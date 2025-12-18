<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vapid:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for WebPush and save to .env';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Generating VAPID keys...");

        // pakai RSA agar aman di Windows
        $keys = VAPID::createVapidKeys([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        $publicKey  = $keys['publicKey'];
        $privateKey = $keys['privateKey'];

        $this->line("Public Key : " . $publicKey);
        $this->line("Private Key: " . $privateKey);

        // update .env file otomatis
        $path = base_path('.env');
        if (file_exists($path)) {
            $env = file_get_contents($path);

            $env = preg_replace('/^VAPID_PUBLIC_KEY=.*$/m', "VAPID_PUBLIC_KEY={$publicKey}", $env);
            $env = preg_replace('/^VAPID_PRIVATE_KEY=.*$/m', "VAPID_PRIVATE_KEY={$privateKey}", $env);

            if (strpos($env, 'VAPID_PUBLIC_KEY=') === false) {
                $env .= "\nVAPID_PUBLIC_KEY={$publicKey}";
            }
            if (strpos($env, 'VAPID_PRIVATE_KEY=') === false) {
                $env .= "\nVAPID_PRIVATE_KEY={$privateKey}";
            }
            if (strpos($env, 'VAPID_SUBJECT=') === false) {
                $env .= "\nVAPID_SUBJECT=mailto:admin@domain.com";
            }

            file_put_contents($path, $env);
            $this->info(".env file updated with new VAPID keys.");
        } else {
            $this->error(".env file not found! Please add keys manually.");
        }
    }
}
