<?php
class Admin extends Controller
{
    public function __construct()
    {
        $this->middleware();
        if ($_SESSION['user_role'] !== 'ADMIN') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public function index()
    {
        $adminModel = $this->model('Admin_model');
        $data['summary'] = $adminModel->getSummaryStats();

        // จัดรูปแบบ Data สำหรับ Chart.js
        $data['chart_labels'] = array_column($data['summary']['step_progress'], 'step_name');
        $data['chart_data'] = array_column($data['summary']['step_progress'], 'approved_count');

        // Widget: Recent Activities
        $logModel = $this->model('Log_model');
        $data['recent_activities'] = $logModel->getLogs(5); // 5 รายการล่าสุด

        $this->view('admin/dashboard', $data);
    }

    // หน้าจัดการผู้ใช้งาน
    public function users()
    {
        $adminModel = $this->model('Admin_model');

        // Pagination logic
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $search = isset($_GET['q']) ? trim($_GET['q']) : ''; // Search Query
        $offset = ($page - 1) * $limit;

        $data['users'] = $adminModel->getAllUsers($limit, $offset, $search);
        $totalUsers = $adminModel->countAllUsers($search);

        $data['pagination'] = [
            'current_page' => $page,
            'limit' => $limit,
            'total_users' => $totalUsers,
            'total_pages' => ceil($totalUsers / $limit),
            'search' => $search
        ];

        $this->view('admin/users', $data);
    }

    public function user_store()
    {
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

    public function user_update()
    {
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

    public function user_edit($id)
    {
        $adminModel = $this->model('Admin_model');
        $user = $adminModel->getUserById($id);
        echo json_encode($user);
    }

    public function user_delete($id)
    {
        $adminModel = $this->model('Admin_model');
        if ($adminModel->deleteUser($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบตัวเองได้']);
        }
    }



    // หน้าจัดการขั้นตอนส่งงาน
    public function steps()
    {
        $adminModel = $this->model('Admin_model');
        $data['steps'] = $adminModel->getAllSteps();
        $this->view('admin/steps', $data);
    }

    private function uploadStepFile($fileKey)
    {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../uploads/steps/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $file = $_FILES[$fileKey];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $fileKey . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            return 'uploads/steps/' . $fileName;
        }
        return null;
    }

    public function step_store()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $adminModel = $this->model('Admin_model');

                $data = $_POST;

                // Handle Form (File or Link)
                if (isset($_POST['type_form']) && $_POST['type_form'] === 'link') {
                    $data['file_form_path'] = trim($_POST['link_form']);
                } else {
                    $data['file_form_path'] = $this->uploadStepFile('file_form');
                }

                // Handle Example (File or Link)
                if (isset($_POST['type_example']) && $_POST['type_example'] === 'link') {
                    $data['file_example_path'] = trim($_POST['link_example']);
                } else {
                    $data['file_example_path'] = $this->uploadStepFile('file_example');
                }

                if ($adminModel->createStep($data)) {
                    echo json_encode(['status' => 'success', 'message' => 'เพิ่มขั้นตอนงานเรียบร้อย']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function step_update()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $adminModel = $this->model('Admin_model');

                $data = $_POST;

                // Handle Form Logic
                if (isset($_POST['type_form']) && $_POST['type_form'] === 'link') {
                    // If link provided, use it
                    if (!empty($_POST['link_form'])) {
                        $data['file_form_path'] = trim($_POST['link_form']);
                    }
                } else {
                    // If file uploaded, use it
                    $newForm = $this->uploadStepFile('file_form');
                    if ($newForm) $data['file_form_path'] = $newForm;
                }

                // Handle Example Logic
                if (isset($_POST['type_example']) && $_POST['type_example'] === 'link') {
                    if (!empty($_POST['link_example'])) {
                        $data['file_example_path'] = trim($_POST['link_example']);
                    }
                } else {
                    $newExample = $this->uploadStepFile('file_example');
                    if ($newExample) $data['file_example_path'] = $newExample;
                }

                if ($adminModel->updateStep($data)) {
                    echo json_encode(['status' => 'success', 'message' => 'อัปเดตขั้นตอนงานเรียบร้อย']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function step_delete($id)
    {
        $adminModel = $this->model('Admin_model');
        if ($adminModel->deleteStep($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบได้ เนื่องจากมีนักเรียนส่งงานในหัวข้อนี้แล้ว']);
        }
    }

    public function user_import()
    {
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

    public function announcements()
    {
        $adminModel = $this->model('Admin_model');
        $data['announcements'] = $adminModel->getAllAnnouncements();
        $this->view('admin/announcements', $data);
    }

    public function announcement_store()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $adminModel = $this->model('Admin_model');
            if ($adminModel->createAnnouncement($_POST)) {
                echo json_encode(['status' => 'success']);
            }
        }
    }

    public function announcement_delete($id)
    {
        $adminModel = $this->model('Admin_model');
        if ($adminModel->deleteAnnouncement($id)) {
            echo json_encode(['status' => 'success']);
        }
    }

    public function user_password_reset()
    {
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

    // --- Admin Settings ---
    public function settings()
    {
        $settingsModel = $this->model('Settings_model');
        $data['settings'] = $settingsModel->getAll();
        $this->view('admin/settings', $data);
    }

    public function settings_update()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $settingsModel = $this->model('Settings_model');

            // Text Settings
            foreach ($_POST as $key => $value) {
                if ($key !== 'csrf_token') {
                    $settingsModel->set($key, trim($value));
                }
            }

            // File Settings (Logo / Favicon)
            // Updated: Uploads are now at root
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/x-icon', 'image/vnd.microsoft.icon'];

            foreach (['site_logo', 'site_favicon'] as $fileKey) {
                if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES[$fileKey]['tmp_name'];
                    $fileType = $_FILES[$fileKey]['type'];

                    if (in_array($fileType, $allowedTypes)) {
                        $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
                        $fileName = $fileKey . '_' . time() . '.' . $ext;

                        if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
                            // Save path relative to project root (accessible via browser)
                            // Updated: No 'public/' prefix needed
                            $settingsModel->set($fileKey, 'uploads/' . $fileName);
                        }
                    }
                }
            }

            echo json_encode(['status' => 'success', 'message' => 'บันทึกการตั้งค่าเรียบร้อยแล้ว']);
        }
    }
    public function logs()
    {
        $logModel = $this->model('Log_model');

        // Capture filters
        $filters = [
            'role' => isset($_GET['role']) ? $_GET['role'] : '',
            'date' => isset($_GET['date']) ? $_GET['date'] : '',
            'search' => isset($_GET['q']) ? trim($_GET['q']) : ''
        ];

        // Pass filters to model
        $data['logs'] = $logModel->getLogs(100, $filters);
        $data['filters'] = $filters; // Send back to view to maintain state

        $this->view('admin/logs', $data);
    }

    public function progress()
    {
        $adminModel = $this->model('Admin_model');
        $yearModel = $this->model('Year_model');

        $currentYearObj = $yearModel->getCurrentYear();
        $defaultYear = $currentYearObj['year'];

        $room = isset($_GET['room']) && $_GET['room'] !== '' ? $_GET['room'] : null;
        if (isset($_GET['year'])) {
            $year = $_GET['year'] !== '' ? $_GET['year'] : null;
        } else {
            $year = $defaultYear;
        }

        $data = $adminModel->getProgressMatrix($room, $year);
        $data['selected_room'] = $room;
        $data['selected_year'] = $year;
        $data['years'] = $yearModel->getAll(); // Load years

        $this->view('admin/progress', $data);
    }


    // --- Academic Year Management ---
    public function years()
    {
        $yearModel = $this->model('Year_model');
        $data['years'] = $yearModel->getAll();
        $this->view('admin/years', $data);
    }

    public function year_store()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $yearModel = $this->model('Year_model');
            if ($yearModel->create($_POST)) {
                echo json_encode(['status' => 'success', 'message' => 'เพิ่มปีการศึกษาสำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด']);
            }
        }
    }

    public function year_update()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $yearModel = $this->model('Year_model');
            if ($yearModel->update($_POST)) {
                echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลสำเร็จ']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด']);
            }
        }
    }

    public function year_set_current()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $yearModel = $this->model('Year_model');
            if ($yearModel->setCurrent($_POST['id'])) {
                echo json_encode(['status' => 'success', 'message' => 'ตั้งค่าปีปัจจุบันเรียบร้อย']);
            }
        }
    }

    public function year_delete($id)
    {
        $yearModel = $this->model('Year_model');
        if ($yearModel->delete($id)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบปีปัจจุบันได้']);
        }
    }

    // --- Room & Assignment Management ---
    public function rooms()
    {
        $roomModel = $this->model('Room_model');
        $adminModel = $this->model('Admin_model'); // Need users list

        $data['rooms'] = $roomModel->getAvailableRooms(); // 6.1 - 6.15
        $data['assignments'] = $roomModel->getAssignments();

        // Get all Teachers
        $data['teachers'] = $adminModel->getUsersByRole('TEACHER');

        $this->view('admin/rooms', $data);
    }

    public function assign_teacher()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $roomModel = $this->model('Room_model');
            $teacherId = $_POST['teacher_id'];
            $rooms = $_POST['rooms'] ?? [];

            if (empty($rooms)) {
                echo json_encode(['status' => 'error', 'message' => 'กรุณาเลือกอย่างน้อย 1 ห้อง']);
                return;
            }

            $count = 0;
            if (!is_array($rooms)) $rooms = [$rooms];

            foreach ($rooms as $room) {
                if ($roomModel->assign($teacherId, $room)) {
                    $count++;
                }
            }

            if ($count > 0) {
                echo json_encode(['status' => 'success', 'message' => "มอบหมายสำเร็จ $count ห้อง"]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่มีการเปลี่ยนแปลง (อาจมอบหมายไปแล้ว)']);
            }
        }
    }

    public function unassign_teacher()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $roomModel = $this->model('Room_model');
            if ($roomModel->remove($_POST['teacher_id'], $_POST['room'])) {
                echo json_encode(['status' => 'success']);
            }
        }
    }
}
