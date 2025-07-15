<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $filePath = $_FILES['image']['tmp_name'];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.ocr.space/parse/image",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'apikey' => 'helloworld', // مفتاح تجريبي
            'file' => new CURLFile($filePath),
            'language' => 'eng',
            'isOverlayRequired' => false
        ]
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response, true);
    $text = $result['ParsedResults'][0]['ParsedText'] ?? '';

    // استخراج رقم التحويل (أول رقم من 6 خانات أو أكثر)
    preg_match('/\b\d{6,}\b/', $text, $matches);
    echo $matches[0] ?? '';
}
?>