<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The pto-it / pto-product instances only differ by .env, and those files
 * still use the Laravel 6 variable names. Make sure they keep resolving.
 */
class LegacyEnvConfigTest extends TestCase
{
    private const KEYS = [
        'MAIL_MAILER', 'MAIL_DRIVER', 'MAIL_SCHEME', 'MAIL_ENCRYPTION', 'MAIL_PORT',
        'QUEUE_CONNECTION', 'QUEUE_DRIVER',
        'CACHE_STORE', 'CACHE_DRIVER',
        'BROADCAST_CONNECTION', 'BROADCAST_DRIVER',
        'LOG_LEVEL', 'APP_LOG_LEVEL',
        'SESSION_DOMAIN', 'DB_DATABASE',
    ];

    private array $saved = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::KEYS as $key) {
            $this->saved[$key] = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];
            $this->setEnv($key, null);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->saved as $key => [$env, $envArray, $server]) {
            putenv($env === false ? $key : "$key=$env");
            unset($_ENV[$key], $_SERVER[$key]);

            if ($envArray !== null) {
                $_ENV[$key] = $envArray;
            }
            if ($server !== null) {
                $_SERVER[$key] = $server;
            }
        }

        parent::tearDown();
    }

    private function setEnv(string $key, ?string $value): void
    {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private function config(string $file, array $env = []): array
    {
        foreach ($env as $key => $value) {
            $this->setEnv($key, $value);
        }

        return require base_path("config/$file.php");
    }

    #[Test]
    public function legacy_driver_names_are_honoured()
    {
        $this->assertSame('log', $this->config('mail', ['MAIL_DRIVER' => 'log'])['default']);
        $this->assertSame('database', $this->config('queue', ['QUEUE_DRIVER' => 'database'])['default']);
        $this->assertSame('array', $this->config('cache', ['CACHE_DRIVER' => 'array'])['default']);
        $this->assertSame('log', $this->config('broadcasting', ['BROADCAST_DRIVER' => 'log'])['default']);
        $this->assertSame('warning', $this->config('logging', ['APP_LOG_LEVEL' => 'warning'])['channels']['single']['level']);
    }

    #[Test]
    public function new_driver_names_win_over_legacy_ones()
    {
        $this->assertSame('array', $this->config('mail', ['MAIL_DRIVER' => 'smtp', 'MAIL_MAILER' => 'array'])['default']);
        $this->assertSame('sync', $this->config('queue', ['QUEUE_DRIVER' => 'database', 'QUEUE_CONNECTION' => 'sync'])['default']);
        $this->assertSame('file', $this->config('cache', ['CACHE_DRIVER' => 'array', 'CACHE_STORE' => 'file'])['default']);
    }

    #[Test]
    public function defaults_match_laravel_6_behaviour()
    {
        $this->assertSame('smtp', $this->config('mail')['default']);
        $this->assertSame('sync', $this->config('queue')['default']);
        $this->assertSame('file', $this->config('cache')['default']);
        $this->assertSame('null', $this->config('broadcasting')['default']);
        $this->assertSame('laravel_session', $this->config('session')['cookie']);
        $this->assertSame('password_resets', $this->config('auth')['passwords']['users']['table']);
        $this->assertFalse($this->config('database')['connections']['sqlite']['foreign_key_constraints']);
    }

    #[Test]
    public function mail_encryption_maps_onto_symfony_smtp_schemes()
    {
        $this->assertNull($this->config('mail', ['MAIL_ENCRYPTION' => 'tls'])['mailers']['smtp']['scheme']);
        $this->assertSame('smtps', $this->config('mail', ['MAIL_ENCRYPTION' => 'ssl'])['mailers']['smtp']['scheme']);
        $this->assertSame('smtp', $this->config('mail', ['MAIL_ENCRYPTION' => 'ssl', 'MAIL_SCHEME' => 'smtp'])['mailers']['smtp']['scheme']);
    }

    #[Test]
    public function session_domain_and_sqlite_path_come_straight_from_env()
    {
        $this->assertSame('localhost', $this->config('session', ['SESSION_DOMAIN' => 'localhost'])['domain']);
        $this->assertSame(
            '/var/www/database/database.sqlite',
            $this->config('database', ['DB_DATABASE' => '/var/www/database/database.sqlite'])['connections']['sqlite']['database']
        );
    }
}
