<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;

class SendFcmTest extends Command
{
    protected $signature = 'fcm:test {token}';
    protected $description = 'Send test push notification via Firebase Cloud Messaging';

    public function handle()
    {
        $deviceToken = $this->argument('token');

        // Load service account
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        // Ambil access token
        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        // Ambil project ID dari JSON
        $projectId = json_decode(file_get_contents(storage_path('app/firebase/credentials.json')), true)['project_id'];

        // FCM endpoint
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        // Kirim notifikasi
        $response = Http::withToken($accessToken)->post($url, [
            "message" => [
                "token" => $deviceToken,
                "notification" => [
                    "title" => "Halo dari Laravel 🎉",
                    "body"  => "Notifikasi test FCM berhasil dikirim!",
                ],
            ],
        ]);

        $this->info("Response: " . $response->body());
    }
}
