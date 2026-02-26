<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;

class FcmService
{
    protected static function getAccessToken()
    {
        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            storage_path('app/firebase-service-account.json')
        );

        $token = $credentials->fetchAuthToken();
        return $token['access_token'];
    }

    public static function sendToToken($token, $title, $body)
    {
        $accessToken = self::getAccessToken();

        $projectId = env('FIREBASE_PROJECT_ID');

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ]
            ]
        ];

        $headers = [
            "Authorization: Bearer {$accessToken}",
            "Content-Type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    
}