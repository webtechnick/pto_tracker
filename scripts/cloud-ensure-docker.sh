#!/usr/bin/env bash
# Shared Docker bootstrap for Cursor Cloud scripts.
# Source from cloud-install.sh and cloud-start.sh.
#
# The PTO Tracker dev environment runs entirely in Docker via the ./pto CLI
# (docker compose). On Cursor Cloud pods Docker itself is nested inside an
# overlay filesystem, which requires the fuse-overlayfs storage driver and the
# legacy iptables backend to work reliably. This file installs and starts the
# Docker daemon and normalizes those settings so ./pto commands succeed.

# Run a docker command, falling back to sudo when the current user cannot
# reach the daemon socket yet.
docker_cmd() {
    if docker info >/dev/null 2>&1; then
        docker "$@"

        return
    fi

    if sudo docker info >/dev/null 2>&1; then
        sudo docker "$@"

        return
    fi

    echo "Docker is not available (tried docker and sudo docker)." >&2

    return 1
}

_cloud_docker_log() {
    if declare -F log >/dev/null 2>&1; then
        log "$@"
    else
        echo ">>> $*"
    fi
}

_cloud_docker_ok() {
    if declare -F ok >/dev/null 2>&1; then
        ok "$@"
    else
        echo ">>> $*"
    fi
}

ensure_docker_iptables_legacy() {
    # Nested Cursor Cloud pods often have a legacy FORWARD DROP policy while
    # docker.io defaults to the nft backend. Mixed backends break
    # container-to-container traffic (e.g. nginx -> php-fpm timeouts).
    if [[ -x /usr/sbin/iptables-legacy ]]; then
        sudo update-alternatives --set iptables /usr/sbin/iptables-legacy >/dev/null 2>&1 || true
    fi

    if [[ -x /usr/sbin/ip6tables-legacy ]]; then
        sudo update-alternatives --set ip6tables /usr/sbin/ip6tables-legacy >/dev/null 2>&1 || true
    fi
}

ensure_docker_daemon_config() {
    # Nested pods sit on overlay; overlay2-in-overlay fails.
    # fuse-overlayfs is required for nested Cursor Cloud Docker.
    local desired='{"storage-driver":"fuse-overlayfs"}'
    local current=''

    if [[ -f /etc/docker/daemon.json ]]; then
        current="$(tr -d '[:space:]' </etc/docker/daemon.json 2>/dev/null || true)"
    fi

    if [[ "$current" == *'"storage-driver":"fuse-overlayfs"'* ]]; then
        return
    fi

    sudo mkdir -p /etc/docker
    printf '%s\n' "$desired" | sudo tee /etc/docker/daemon.json >/dev/null
}

ensure_docker_installed() {
    if command -v docker >/dev/null 2>&1 && command -v dockerd >/dev/null 2>&1; then
        ensure_docker_daemon_config
        ensure_docker_iptables_legacy

        return
    fi

    _cloud_docker_log "Installing Docker (docker.io + compose plugin + fuse-overlayfs)..."

    export DEBIAN_FRONTEND=noninteractive
    sudo apt-get update -qq
    # iptables ships the legacy backend that fuse-overlayfs networking needs.
    sudo apt-get install -y --no-install-recommends \
        docker.io \
        docker-compose-v2 \
        fuse-overlayfs \
        iptables

    ensure_docker_daemon_config
    ensure_docker_iptables_legacy

    if getent group docker >/dev/null 2>&1; then
        sudo usermod -aG docker "$(id -un)" 2>/dev/null || true
    fi

    if ! command -v docker >/dev/null 2>&1 || ! command -v dockerd >/dev/null 2>&1; then
        echo "Docker packages installed but docker/dockerd still not on PATH." >&2
        exit 1
    fi

    _cloud_docker_ok "Docker packages installed"
}

ensure_docker() {
    ensure_docker_installed

    if docker_cmd info >/dev/null 2>&1; then
        _cloud_docker_ok "Docker daemon is running"

        return
    fi

    _cloud_docker_log "Starting Docker daemon..."

    ensure_docker_daemon_config
    ensure_docker_iptables_legacy

    if ! pgrep -x dockerd >/dev/null 2>&1; then
        sudo dockerd >/tmp/dockerd.log 2>&1 &
    fi

    for _ in $(seq 1 60); do
        if [[ -S /var/run/docker.sock ]] && [[ ! -w /var/run/docker.sock ]]; then
            sudo chmod 666 /var/run/docker.sock 2>/dev/null || true
        fi

        if [[ -d /var/run ]] && [[ ! -x /var/run ]]; then
            sudo chmod 755 /var/run 2>/dev/null || true
        fi

        if docker_cmd info >/dev/null 2>&1; then
            break
        fi
        sleep 1
    done

    if [[ -S /var/run/docker.sock ]] && [[ ! -w /var/run/docker.sock ]]; then
        sudo chmod 666 /var/run/docker.sock 2>/dev/null || true
    fi

    if [[ -d /var/run ]] && [[ ! -x /var/run ]]; then
        sudo chmod 755 /var/run 2>/dev/null || true
    fi

    if ! docker_cmd info >/dev/null 2>&1; then
        echo "Docker failed to start. See /tmp/dockerd.log" >&2
        exit 1
    fi

    _cloud_docker_ok "Docker daemon started"
}
