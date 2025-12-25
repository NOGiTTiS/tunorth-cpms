<?php
require_once __DIR__ . '/Database.php';

class Notification {
    
    public static function sendTelegram($message, $type = 'general') {
        $db = (new Database())->getConnection();
        
        // ดึงค่า Token, Chat ID และการตั้งค่าเปิด-ปิดแจ้งเตือน
        $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('telegram_api_token', 'telegram_chat_id', 'telegram_chat_id_student', 'enable_submission_notify', 'enable_grading_notify')");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $token = $settings['telegram_api_token'] ?? '';
        $chat_id_admin = $settings['telegram_chat_id'] ?? ''; // กลุ่มครู/แอดมิน (รับงาน)
        $chat_id_student = $settings['telegram_chat_id_student'] ?? ''; // กลุ่มนักเรียน (รับผลตรวจ)
        
        $submission_notify = $settings['enable_submission_notify'] ?? '1';
        $grading_notify = $settings['enable_grading_notify'] ?? '1';

        // 1. ตรวจสอบการตั้งค่าเปิด-ปิด
        if ($type === 'submission' && $submission_notify === '0') return false;
        if ($type === 'grading' && $grading_notify === '0') return false;

        // 2. เลือก Chat ID ตามประเภท
        $target_chat_id = $chat_id_admin; // Default

        if ($type === 'submission') {
            $target_chat_id = $chat_id_admin; // นร. ส่งงาน -> แจ้งเข้ากลุ่มครู
        } elseif ($type === 'grading') {
            $target_chat_id = $chat_id_student; // ครูตรวจงาน -> แจ้งเข้ากลุ่มนักเรียน
        }

        // 3. Validation
        if (empty($token) || empty($target_chat_id)) {
            return false;
        }

        $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
        $data = [
            'chat_id' => $target_chat_id,
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