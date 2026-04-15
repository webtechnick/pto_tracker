<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-token {--show : Display the token instead of writing to .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a secure API_TOKEN and write it to the .env file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $token = Str::random(64);

        if ($this->option('show')) {
            $this->line($token);
            return;
        }

        $this->writeTokenToEnv($token);

        $this->info('API token generated and written to .env file.');
        $this->line('Share this same token with any consuming application.');
    }

    /**
     * Set or replace API_TOKEN in the .env file.
     *
     * @param  string  $token
     * @return void
     */
    protected function writeTokenToEnv($token)
    {
        $path = $this->laravel->environmentFilePath();
        $contents = file_get_contents($path);

        if (preg_match('/^API_TOKEN=.*$/m', $contents)) {
            $contents = preg_replace('/^API_TOKEN=.*$/m', 'API_TOKEN=' . $token, $contents);
        } else {
            $contents .= "\nAPI_TOKEN=" . $token . "\n";
        }

        file_put_contents($path, $contents);
    }
}
