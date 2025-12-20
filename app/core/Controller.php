<?php
class Controller {
    // ฟังก์ชันสำหรับเรียกใช้ Model
    public function model($model) {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model();
    }

    // ฟังก์ชันสำหรับเรียกใช้ View (หน้า HTML)
    public function view($view, $data = []) {
        require_once __DIR__ . '/../views/' . $view . '.php';
    }

    // ฟังก์ชันตรวจสอบ Session
    public function middleware() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/auth');
            exit;
        }
    }

    // --- CSRF PROTECTION ---

    // 1. สร้าง Token และเก็บใน Session
    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // 2. ตรวจสอบ Token ที่ส่งมาจาก Request
    public function verifyCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ตรวจสอบทั้งจาก $_POST และจาก HTTP Header (สำหรับ AJAX)
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if (empty($token) || $token !== $_SESSION['csrf_token']) {
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid. (ความปลอดภัยไม่อนุญาตให้ดำเนินการ)']);
                exit;
            }
        }
    }
}