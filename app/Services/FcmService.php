<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class FcmServices
{
    private string $accessToken;
    private string $projectId;
    public function __construct()
    {
        $configPath =base_path(env('FCM_CREDENTIALS'));
        $jsonKey = json_decode(file_get_contents($configPath), true);
        $this->projectId   = $jsonKey['project_id'];
        $this->accessToken = $this->getAccessToken($jsonKey);
    }
    private function getAccessToken(array $jsonKey): string
    {
        $header = rtrim(strtr(base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ])), '+/', '-_'), '=');
        $now = time();
        $claim = rtrim(strtr(base64_encode(json_encode([
            'iss'   => $jsonKey['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => $jsonKey['token_uri'],
            'iat'   => $now,
            'exp'   => $now + 3600,
        ])), '+/', '-_'), '=');
        openssl_sign("$header.$claim", $signature, $jsonKey['private_key'], 'sha256');
        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $jwt = "$header.$claim.$signature";
        $response = Http::asForm()->post($jsonKey['token_uri'], [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);
        if (!$response->successful()) {
            Log::error('FCM auth error: '.$response->body());
            throw new \RuntimeException('Failed to get FCM access token');
        }
        return $response->json()['access_token'];
    }
    public function sendToUser(User $user, string $titleKey, string $bodyKey, array $data = []): bool
{
    if (!$user->fcm_token) {
        Log::warning("User {$user->id} has no fcm_token");
        return false;
    }
    app()->setLocale($user->language ?? 'ar');
    $title = __('messages.' . $titleKey, $data);
    $body  = __('messages.' . $bodyKey, $data);
    $sent = $this->sendRaw($user->fcm_token, $title, $body, $data);
    $user->notifications()->create([
        'type' => 'system',
        'data' => [
            'title'   => $title,
            'body'    => $body,
            'payload' => $data,
        ],
    ]);
    return $sent;
}
    public function sendRaw(string $token, string $title, string $body, array $data = []): bool
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $payload = [
            'message' => [
                'token'        => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $data,
            ],
        ];
        $response = Http::withToken($this->accessToken)->post($url, $payload);
        Log::info('FCM response: '.$response->body());
        return $response->successful();
    }
}