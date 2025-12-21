<?php
class Profile extends Controller {
    public function __construct() {
        $this->middleware();
    }

    public function index() {
        $userModel = $this->model('User_model');
        $user = $userModel->getUserById($_SESSION['user_id']);

        $this->view('profile/index', ['user' => $user]);
    }

    public function update_password() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = $this->model('User_model');
            $user = $userModel->getUserById($_SESSION['user_id']);

            $old_pass = $_POST['old_password'];
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];

            // 1. Check Old Password
            if (!password_verify($old_pass, $user['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านเดิมไม่ถูกต้อง']);
                return;
            }

            // 2. Check Confirm Password
            if ($new_pass !== $confirm_pass) {
                echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านใหม่ไม่ตรงกัน']);
                return;
            }

            // 3. Update
            if ($userModel->updatePassword($_SESSION['user_id'], $new_pass)) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'CHANGE_PASSWORD', 'เปลี่ยนรหัสผ่านสำเร็จ');
                echo json_encode(['status' => 'success', 'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึก']);
            }
        }
    }
}
