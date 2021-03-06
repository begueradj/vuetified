<?php

namespace Vuetified\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Console\ConfirmableTrait;

class GenerateLaravelWebsocketsAppID extends Command
{
    use ConfirmableTrait;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the Laravel Websocket App ID';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websockets:id
                        {--show : Display the key instead of modifying files}
                        {--force : Force the operation to run when in production}';

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
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            return $this->line('<comment>'.$key.'</comment>');
        }
        if (!$this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->laravel['config']['websockets.apps.0.id'] = $key;

        $this->info("Laravel Websocket App ID: [$key] set successfully.");
    }

    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey($this->laravel['config']['app.cipher'])
        );
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern()
    {
        $escaped = preg_quote('='.$this->laravel['config']['websockets.apps.0.id'], '/');

        return "/^PUSHER_APP_ID{$escaped}/m";
    }

    /**
     * @param $key
     */
    protected function setKeyInEnvironmentFile($key)
    {
        $currentKey = $this->laravel['config']['websockets.apps.0.id'];

        if (strlen($currentKey) !== 0 && (!$this->confirmToProceed())) {
            return false;
        }

        $this->writeNewEnvironmentFileWith($key);

        return true;
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param  string $key
     * @return void
     */
    protected function writeNewEnvironmentFileWith($key)
    {
        file_put_contents($this->laravel->environmentFilePath(), preg_replace(
            $this->keyReplacementPattern(),
            'PUSHER_APP_ID='.$key,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }
}
