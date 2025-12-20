<?php
class Auth extends Controller {
    
    // แสดงหน้า Login
    public function index() {
        // ถ้า Login อยู่แล้วให้ไปหน้า Dashboard (จะสร้างใน step ถัดไป)
        if(isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        // Load Settings
        $settingsModel = $this->model('Settings_model');
        $data['site_logo'] = $settingsModel->get('site_logo');

        $this->view('auth/login', $data);
    }

    // ประมวลผลการ Login
    public function login() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $userModel = $this->model('User_model');
            $user = $userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                // สร้าง Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];

                // Log Activity
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'LOGIN', 'เข้าสู่ระบบสำเร็จ');

                echo json_encode(['status' => 'success', 'message' => 'เข้าสู่ระบบสำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง']);
            }
        }
    }

    // ออกจากระบบ
    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/auth');
    }
}