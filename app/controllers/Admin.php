<?php
class Admin extends Controller {
    public function __construct() {
        $this->middleware();
        if ($_SESSION['user_role'] !== 'ADMIN') {
            header('Location: /dashboard');
            exit;
        }
    }

    public function index() {
        $adminModel = $this->model('Admin_model');
        $data['summary'] = $adminModel->getSummaryStats();
        
        // จัดรูปแบบ Data สำหรับ Chart.js
        $data['chart_labels'] = array_column($data['summary']['step_progress'], 'step_name');
        $data['chart_data'] = array_column($data['summary']['step_progress'], 'approved_count');
        
        $this->view('admin/dashboard', $data);
    }

    // หน้าจัดการผู้ใช้งาน
    public function users() {
        $adminModel = $this->model('Admin_model');
        $data['users'] = $adminModel->getAllUsers();
        $this->view('admin/users', $data);
    }

    public function user_store() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $adminModel = $this->model('Admin_model');
            // ส่งค่า $_POST ทั้งหมดไป (รวมถึง room และ student_id)
            if ($adminModel->createUser($_POST)) {
                echo json_encode(['status' => 'success', 'message' => 'เพิ่มผู้ใช้สำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
            }
        }
    }

    public function user_update() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $adminModel = $this->model('Admin_model');
            if ($adminModel->updateUser($_POST)) {
                echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลสำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถอัปเดตข้อมูลได้']);
            }
        }
    }

    public function user_edit($id) {
        $adminModel = $this->model('Admin_model');
        $user = $adminModel->getUserById($id);
        echo json_encode($user);
    }

    public function user_delete($id) {
        $adminModel = $this->model('Admin_model');
        if ($adminModel->deleteUser($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบตัวเองได้']);
        }
    }

    // API สำหรับสร้าง User
    public function user_create() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $adminModel = $this->model('Admin_model');
            if ($adminModel->createUser($_POST)) {
                echo json_encode(['status' => 'success']);
            }
        }
    }

    // หน้าจัดการขั้นตอนส่งงาน
    public function steps() {
        $adminModel = $this->model('Admin_model');
        $data['steps'] = $adminModel->getAllSteps();
        $this->view('admin/steps', $data);
    }

    public function step_store() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $adminModel = $this->model('Admin_model');
            if ($adminModel->createStep($_POST)) {
                echo json_encode(['status' => 'success', 'message' => 'เพิ่มขั้นตอนงานเรียบร้อย']);
            }
        }
    }

    public function step_update() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $adminModel = $this->model('Admin_model');
            if ($adminModel->updateStep($_POST)) {
                echo json_encode(['status' => 'success', 'message' => 'อัปเดตขั้นตอนงานเรียบร้อย']);
            }
        }
    }

    public function step_delete($id) {
        $adminModel = $this->model('Admin_model');
        if ($adminModel->deleteStep($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบได้ เนื่องจากมีนักเรียนส่งงานในหัวข้อนี้แล้ว']);
        }
    }

    public function user_import() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, "r");
            
            $adminModel = $this->model('Admin_model');
            $successCount = 0;
            $skipCount = 0;
            $rowCount = 0;

            // ข้ามบรรทัดแรก (Header)
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rowCount++;
                // โครงสร้าง CSV: 0:Name, 1:Email, 2:Password, 3:Role, 4:StudentID, 5:Room
                $email = trim($data[1]);

                if (!$adminModel->checkEmailExists($email)) {
                    $userData = [
                        'full_name'  => trim($data[0]),
                        'email'      => $email,
                        'password'   => trim($data[2]),
                        'role'       => trim($data[3]), // STUDENT, TEACHER
                        'student_id' => trim($data[4]),
                        'room'       => trim($data[5])
                    ];
                    $adminModel->importUser($userData);
                    $successCount++;
                } else {
                    $skipCount++;
                }
            }
            fclose($handle);

            echo json_encode([
                'status' => 'success', 
                'message' => "นำเข้าสำเร็จ $successCount รายการ, ข้ามข้อมูลซ้ำ $skipCount รายการ"
            ]);
        }
    }

    public function announcements() {
        $adminModel = $this->model('Admin_model');
        $data['announcements'] = $adminModel->getAllAnnouncements();
        $this->view('admin/announcements', $data);
    }

    public function announcement_store() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $adminModel = $this->model('Admin_model');
            if ($adminModel->createAnnouncement($_POST)) {
                echo json_encode(['status' => 'success']);
            }
        }
    }

    public function announcement_delete($id) {
        $adminModel = $this->model('Admin_model');
        if ($adminModel->deleteAnnouncement($id)) {
            echo json_encode(['status' => 'success']);
        }
    }

    public function user_password_reset() {
        // 1. ตรวจสอบความปลอดภัย
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $new_pass = $_POST['new_password'];

            // ตรวจสอบความยาวรหัสผ่านเบื้องต้น
            if (strlen($new_pass) < 6) {
                echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร']);
                return;
            }

            $adminModel = $this->model('Admin_model');
            if ($adminModel->resetUserPassword($id, $new_pass)) {
                echo json_encode(['status' => 'success', 'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเปลี่ยนรหัสผ่านได้']);
            }
        }
    }
}