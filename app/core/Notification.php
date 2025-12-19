<?php
class Notification {
    private static $token = "7997326343:AAHhG63Os0vaCxSeNv0qaeCWq7XNsMWi3MM"; // ใส่ Token ที่ได้จาก BotFather
    private static $chat_id = "7606578887";

    public static function sendTelegram($message) {
        $url = "https://api.telegram.org/bot" . self::$token . "/sendMessage";
        $data = [
            'chat_id' => self::$chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context  = stream_context_create($options);
        return @file_get_contents($url, false, $context);
    }
}