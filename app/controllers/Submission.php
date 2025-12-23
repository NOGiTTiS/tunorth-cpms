<?php
class Submission extends Controller {
    public function __construct() {
        $this->middleware();
    }

    public function index() {
        $groupModel = $this->model('Group_model');
        $subModel = $this->model('Submission_model');
        
        $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
        
        // หากยังไม่มีกลุ่ม ให้ไปที่หน้าแจ้งเตือน (แทนการใช้ die)
        if (!$myGroup) {
            $data['title'] = 'ยังไม่พบกลุ่ม';
            $this->view('submission/no_group', $data);
            return; // หยุดการทำงานตรงนี้
        }

        $data = [
            'steps' => $subModel->getStepsWithStatus($myGroup['id']),
            'group' => $myGroup
        ];

        $this->view('submission/index', $data);
    }

    public function upload() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $step_id = $_POST['step_id'];
            $group_id = $_POST['group_id'];
            $user_id = $_SESSION['user_id'];
            $submission_type = $_POST['submission_type'] ?? 'file'; // work_type: 'file' or 'link'

            $dbPath = '';

            if ($submission_type === 'link') {
                // --- กรณีส่งเป็นลิงก์ ---
                $link = trim($_POST['project_link']);
                
                // Validate URL
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    echo json_encode(['status' => 'error', 'message' => 'รูปแบบลิงก์ไม่ถูกต้อง (ต้องขึ้นต้นด้วย http:// หรือ https://)']);
                    return;
                }

                $dbPath = $link;

            } else {
                // --- กรณีส่งเป็นไฟล์ (Logic เดิม) ---
                if (!isset($_FILES['project_file']) || $_FILES['project_file']['error'] == UPLOAD_ERR_NO_FILE) {
                     echo json_encode(['status' => 'error', 'message' => 'กรุณาเลือกไฟล์หรือระบุลิงก์งาน']);
                     return;
                }

                $file = $_FILES['project_file'];

                // 1. ตรวจสอบ Error ของการอัปโหลด
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์']);
                    return;
                }

                // 2. ตรวจสอบขนาดไฟล์ (จำกัดที่ 20MB)
                if ($file['size'] > 20 * 1024 * 1024) {
                    echo json_encode(['status' => 'error', 'message' => 'ไฟล์ต้องมีขนาดไม่เกิน 20MB']);
                    return;
                }

                // 3. ตรวจสอบ MIME Type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimeTypes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
                ];

                if (!in_array($mime, $allowedMimeTypes)) {
                    echo json_encode(['status' => 'error', 'message' => 'อนุญาตเฉพาะไฟล์ PDF, Word และ PowerPoint เท่านั้น']);
                    return;
                }

                // 4. ตั้งชื่อไฟล์ใหม่
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safeFileName = "group_" . $group_id . "_step_" . $step_id . "_" . bin2hex(random_bytes(8)) . "." . $ext;
                
                // Updated: Uploads are now at root
                $uploadDir = __DIR__ . '/../../uploads/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                $targetPath = $uploadDir . $safeFileName;
                
                $dbPath = 'uploads/' . $safeFileName;

                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
                    return;
                }
            }

            // บันทึกลงฐานข้อมูล (ใช้ทั้ง File Path และ Link Path)
            $subModel = $this->model('Submission_model');
            $subModel->submitWork($group_id, $step_id, $dbPath, $user_id);
            
            // Log & Notification
            $this->model('Log_model')->log($user_id, $_SESSION['user_role'], 'UPLOAD', "ส่งงาน ($submission_type) Step ID: $step_id");
            
            require_once __DIR__ . '/../core/Notification.php';
            Notification::sendTelegram("📁 <b>มีการส่งงานใหม่! (" . strtoupper($submission_type) . ")</b>\nกลุ่ม: " . $_POST['group_name'] . "\nงาน: " . $_POST['step_name']);
            
            echo json_encode(['status' => 'success']);
        }
    }
}