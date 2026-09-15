<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    private function accessToken()
    {
        if (class_exists(\Google\Auth\Credentials\ServiceAccountCredentials::class)) {
            $credentialsPath = env('FIREBASE_CREDENTIALS');
            if ($credentialsPath && file_exists(base_path($credentialsPath))) {
                $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                    ['https://www.googleapis.com/auth/firebase.messaging'],
                    json_decode(file_get_contents(base_path($credentialsPath)), true)
                );
                $credentials->fetchAuthToken();
                return $credentials->getLastReceivedToken()['access_token'] ?? null;
            }
        }
        return null;
    }

    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        if (!$projectId) {
            Log::warning('Firebase Project ID is not configured.');
            return ['status' => false, 'message' => 'Firebase Project ID missing'];
        }

        $accessToken = $this->accessToken();
        if (!$accessToken) {
            Log::warning('Firebase Service Account Credentials missing or google/auth package not loaded.');
            return ['status' => false, 'message' => 'Firebase credentials or access token unavailable'];
        }

        $payload = [
            'message' => [
                'token'        => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
            ],
        ];

        if (!empty($data)) {
            $payload['message']['data'] = array_map('strval', $data);
        }

        try {
            $response = Http::withToken($accessToken)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                    $payload
                );

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Firebase Notification Error: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
