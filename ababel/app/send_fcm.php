<?php
require 'vendor/autoload.php';

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

// 🔐 المسار إلى ملف حساب الخدمة
$serviceAccountFile = __DIR__ . '/firebase-service-account.json';
$projectId = 'ababel-daf87'; // من بيانات JSON

// 🎟️ الحصول على access token باستخدام الحساب
$scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
$credentials = new ServiceAccountCredentials($scopes, $serviceAccountFile);
$authToken = $credentials->fetchAuthToken();
$accessToken = $authToken['access_token'];

// 📨 بيانات الإشعار (إلى topic أو token)
$message = [
    'message' => [
        'topic' => 'client_123', // أو استخدم 'token' => 'DEVICE_FCM_TOKEN'
        'notification' => [
            'title' => 'إشعار جديد',
            'body' => 'مرحبًا! هذا إشعار من Ababel.',
        ],
    ]
];

// 🌐 إرسال الطلب إلى FCM API
$client = new Client();
$response = $client->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
    'headers' => [
        'Authorization' => "Bearer $accessToken",
        'Content-Type' => 'application/json',
    ],
    'json' => $message,
]);

echo "✅ تم الإرسال بنجاح:\n";
echo $response->getBody();
