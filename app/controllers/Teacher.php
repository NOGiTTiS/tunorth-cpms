<?php
class Teacher extends Controller {
    public function __construct() {
        $this->middleware();
        // ตรวจสอบว่าเป็นครูจริงไหม
        if ($_SESSION['user_role'] !== 'TEACHER') {
            header('Location: /dashboard');
            exit;
        }
    }

    public function review() {
        $teacherModel = $this->model('Teacher_model');
        
        // เปลี่ยนจาก 'mine' เป็น 'all' เพื่อให้เป็นค่าเริ่มต้น
        $mode = $_GET['mode'] ?? 'all'; 
        
        if ($mode === 'mine') {
            // ดึงเฉพาะงานที่ครูคนนี้ดูแล
            $submissions = $teacherModel->getPendingSubmissions($_SESSION['user_id']);
        } else {
            // ดึงงานทั้งหมด (default)
            $submissions = $teacherModel->getPendingSubmissions(null);
        }

        $data = [
            'submissions' => $submissions,
            'current_mode' => $mode
        ];
        $this->view('teacher/review', $data);
    }

    public function grade() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $teacherModel = $this->model('Teacher_model');
            $success = $teacherModel->updateReview($_POST['id'], $_POST['status'], $_POST['comment']);

            if ($success) {
                // ส่ง Telegram แจ้งเตือนนักเรียน (Optional)
                require_once '../app/core/Notification.php';
                Notification::sendTelegram("🔔 <b>ครูตรวจงานแล้ว!</b>\nโครงงาน: " . $_POST['project_name'] . "\nผลการตรวจ: " . $_POST['status']);
                
                echo json_encode(['status' => 'success']);
            }
        }
    }
}