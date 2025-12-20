<?php
require_once __DIR__ . '/Database.php';

class Notification {
    
    public static function sendTelegram($message) {
        $db = (new Database())->getConnection();
        
        // ดึงค่า Token และ Chat ID จาก Database
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('telegram_api_token', 'telegram_chat_id')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $token = $settings['telegram_api_token'] ?? '';
        $chat_id = $settings['telegram_chat_id'] ?? '';

        // ถ้ายังไม่ได้ตั้งค่า ให้หยุดทำงาน
        if (empty($token) || empty($chat_id)) {
            return false;
        }

        $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 3, // รอแค่ 3 วินาที ถ้าเกินให้ข้ามไปเลย
                'ignore_errors' => true 
            ],
        ];
        $context  = stream_context_create($options);
        return @file_get_contents($url, false, $context);
    }
}