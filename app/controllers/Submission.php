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
            $user_id = $_SESSION['user_id']; // ดึงจาก Session

            $file = $_FILES['project_file'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = "group_" . $group_id . "_step_" . $step_id . "_" . time() . "." . $ext;
            $targetPath = "uploads/" . $fileName;

            // --- ส่วนที่เพิ่มใหม่: SECURITY CHECK ---
        
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

            // 3. ตรวจสอบ MIME Type ด้วย finfo (อ่านเนื้อไฟล์จริง)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = [
                'application/pdf',                                                        // .pdf
                'application/msword',                                                     // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                'application/vnd.ms-powerpoint',                                          // .ppt
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'// .pptx
            ];

            if (!in_array($mime, $allowedMimeTypes)) {
                echo json_encode(['status' => 'error', 'message' => 'อนุญาตเฉพาะไฟล์ PDF, Word และ PowerPoint เท่านั้น']);
                return;
            }

            // 4. ตั้งชื่อไฟล์ใหม่แบบสุ่ม (Sanitize Filename)
            // ป้องกันแฮกเกอร์ตั้งชื่อไฟล์เป็น ../../index.php เพื่อเขียนทับไฟล์ระบบ
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeFileName = "group_" . $group_id . "_step_" . $step_id . "_" . bin2hex(random_bytes(8)) . "." . $ext;
            
            // ใช้ Absolute Path สำหรับย้ายไฟล์ (Disk Operation)
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
            $targetPath = $uploadDir . $safeFileName;
            
            // ใช้ Relative Path สำหรับเก็บลง DB และเรียกใช้ผ่าน Web (Web URL)
            // เก็บเป็น public/uploads/... เพื่อให้ BASE_URL + /public/uploads/... ทำงานได้ถูกต้อง
            $dbPath = 'public/uploads/' . $safeFileName;

            // --- จบส่วนความปลอดภัย ---

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $subModel = $this->model('Submission_model');
                // ส่ง $user_id ไปด้วย
                $subModel->submitWork($group_id, $step_id, $dbPath, $user_id);
                
                // Log Activity
                $this->model('Log_model')->log($user_id, $_SESSION['user_role'], 'UPLOAD', "ส่งงานเอกสาร Step ID: $step_id");
                
                require_once __DIR__ . '/../core/Notification.php';
                Notification::sendTelegram("📁 <b>มีการส่งงานใหม่!</b>\nกลุ่ม: " . $_POST['group_name'] . "\nงาน: " . $_POST['step_name']);
                
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
            }
        }
    }
}