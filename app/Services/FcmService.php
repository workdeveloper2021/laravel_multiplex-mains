<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->projectId = config('fcm.project_id');
        $this->credentialsPath = config('fcm.credentials_path');
    }

    /**
     * Get FCM access token using service account
     */
    private function getAccessToken(): string
    {
        $client = new Client();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        
        $token = $client->fetchAccessTokenWithAssertion();
        
        if (isset($token['error'])) {
            throw new \Exception('FCM Auth Error: ' . $token['error']);
        }

        return $token['access_token'];
    }

    /**
     * Send notification to multiple tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, ?string $image = null): array
    {
        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'errors' => []];
        }

        $accessToken = $this->getAccessToken();
        $results = ['success' => 0, 'failure' => 0, 'errors' => []];
        
        // Split tokens into batches of 500
        $batches = array_chunk($tokens, config('fcm.batch_size', 500));
        
        foreach ($batches as $batch) {
            $batchResult = $this->sendBatch($batch, $title, $body, $image, $accessToken);
            $results['success'] += $batchResult['success'];
            $results['failure'] += $batchResult['failure'];
            $results['errors'] = array_merge($results['errors'], $batchResult['errors']);
        }

        return $results;
    }

    /**
     * Send batch of notifications
     */
    private function sendBatch(array $tokens, string $title, string $body, ?string $image, string $accessToken): array
    {
        $notification = [
            'title' => $title,
            'body' => $body,
        ];

        if ($image) {
            $notification['image'] = $image;
        }

        $payload = [
            'message' => [
                'notification' => $notification,
                'android' => [
                    'notification' => [
                        'icon' => config('fcm.defaults.icon'),
                        'sound' => config('fcm.defaults.sound'),
                        'click_action' => config('fcm.defaults.click_action'),
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => config('fcm.defaults.sound'),
                        ]
                    ]
                ]
            ]
        ];

        $results = ['success' => 0, 'failure' => 0, 'errors' => []];

        foreach ($tokens as $token) {
            $payload['message']['token'] = $token;

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $payload);

                if ($response->successful()) {
                    $results['success']++;
                } else {
                    $results['failure']++;
                    $results['errors'][] = [
                        'token' => $token,
                        'error' => $response->json()['error']['message'] ?? 'Unknown error'
                    ];
                }
            } catch (\Exception $e) {
                $results['failure']++;
                $results['errors'][] = [
                    'token' => $token,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
