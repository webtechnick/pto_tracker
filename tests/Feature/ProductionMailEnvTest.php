<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * The pto-it and pto-product .env files are still written for Laravel 6.
 * Boot a real application from that exact file (in a separate process so
 * phpunit.xml's forced MAIL_MAILER=array etc. do not leak in) and inspect the
 * Symfony transport Laravel 13 builds from it.
 */
class ProductionMailEnvTest extends TestCase
{
    private const PRODUCTION_ENV = <<<'ENV'
MAIL_DRIVER=smtp # for laravel < 7
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_ENCRYPTION=tls
QUEUE_DRIVER=sync
CACHE_DRIVER=file
SESSION_DRIVER=file
DB_CONNECTION=sqlite
ENV;

    private const PROBE = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->useEnvironmentPath($argv[1]);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$transport = $app['mail.manager']->mailer()->getSymfonyTransport();
$stream = $transport->getStream();
echo json_encode([
    'mail.default' => config('mail.default'),
    'scheme' => config('mail.mailers.smtp.scheme'),
    'queue.default' => config('queue.default'),
    'cache.default' => config('cache.default'),
    'session.driver' => config('session.driver'),
    'database.default' => config('database.default'),
    'transport' => get_class($transport),
    'dsn' => (string) $transport,
    'host' => $stream->getHost(),
    'port' => $stream->getPort(),
    'implicit_tls' => $stream->isTLS(),
    'auto_tls' => $transport->isAutoTls(),
]);
PHP;

    private function bootWithEnv(string $env): array
    {
        $dir = sys_get_temp_dir() . '/pto-env-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents("$dir/.env", $env . "\n");

        // Start from an empty environment: the parent process has phpunit.xml's
        // values and the workspace .env (putenv'd by Laravel) loaded.
        $unset = array_map(fn () => false, array_diff_key(getenv(), ['PATH' => true, 'HOME' => true]));

        try {
            $process = new Process([PHP_BINARY, '-r', self::PROBE, $dir], base_path(), $unset);
            $process->mustRun();
        } finally {
            unlink("$dir/.env");
            rmdir($dir);
        }

        return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    }

    #[Test]
    public function production_env_resolves_to_smtp_with_starttls_on_587()
    {
        $config = $this->bootWithEnv(self::PRODUCTION_ENV);

        $this->assertSame('smtp', $config['mail.default'], 'inline "# for laravel < 7" comment must be stripped');
        $this->assertNull($config['scheme']);
        $this->assertSame('Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport', $config['transport']);
        $this->assertSame('smtp://smtp.sendgrid.net:587', $config['dsn']);
        $this->assertSame('smtp.sendgrid.net', $config['host']);
        $this->assertSame(587, $config['port']);
        $this->assertFalse($config['implicit_tls'], 'port 587 must not use implicit TLS');
        $this->assertTrue($config['auto_tls'], 'STARTTLS must be negotiated when offered');

        $this->assertSame('sync', $config['queue.default']);
        $this->assertSame('file', $config['cache.default']);
        $this->assertSame('file', $config['session.driver']);
        $this->assertSame('sqlite', $config['database.default']);
    }

    #[Test]
    public function ssl_encryption_and_port_465_use_implicit_tls()
    {
        $ssl = $this->bootWithEnv(str_replace('MAIL_ENCRYPTION=tls', 'MAIL_ENCRYPTION=ssl', self::PRODUCTION_ENV));
        $this->assertSame('smtps', $ssl['scheme']);
        $this->assertTrue($ssl['implicit_tls']);

        $port465 = $this->bootWithEnv(str_replace('MAIL_PORT=587', 'MAIL_PORT=465', self::PRODUCTION_ENV));
        $this->assertStringStartsWith('smtps://smtp.sendgrid.net', $port465['dsn']);
        $this->assertSame(465, $port465['port']);
        $this->assertTrue($port465['implicit_tls']);
    }
}
