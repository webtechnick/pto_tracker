#!/bin/bash

echo -e "###############################################################"
echo -e "Setting up homebrew and dependencies"
echo -e "###############################################################"
if ! [ -x "$(command -v brew 2>/dev/null)" ]; then
  echo -e "Installing brew"
  /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"
fi
if ! brew info mkcert &>/dev/null; then
  echo -e "Installing mkcert"
  brew install mkcert
fi
if ! brew info nss &>/dev/null; then
  echo -e "Installing nss"
  brew install nss
fi
mkcert -install
pushd ./.docker/nginx || exit
mkcert dev.pto-it.alliedhealthmedia.devlocal
popd || exit

echo -e "###############################################################"
echo -e "Setting up environment configuration files"
echo -e "###############################################################"
cp .env.example .env

echo -e "###############################################################"
echo -e "Building docker containers"
echo -e "###############################################################"
docker-compose build

echo -e "###############################################################"
echo -e "Installing composer requirements"
echo -e "###############################################################"
docker-compose run --rm composer install -vvv

echo -e "###############################################################"
echo -e "Installing NPM requirements"
echo -e "###############################################################"
docker-compose run --rm npm npm ci

echo -e "###############################################################"
echo -e "Setting up database"
echo -e "###############################################################"
docker-compose run --rm artisan key:generate
sleep 30 #Sleeping to ensure the DB is ready
docker-compose run --rm artisan migrate

echo -e "###############################################################"
echo -e "Bringing up containers"
echo -e "###############################################################"
docker-compose up -d
docker ps

echo -e "###############################################################"
echo -e "Running npm run dev"
echo -e "###############################################################"
docker-compose run --rm npm gulp

