<?php
class Project extends Controller {
    public function __construct() {
        $this->middleware();
    }

    public function mygroup() {
        $groupModel = $this->model('Group_model');
        $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
        
        $data = [
            'group' => $myGroup,
            'teachers' => $groupModel->getTeachers()
        ];

        if ($myGroup) {
            $data['members'] = $groupModel->getMembers($myGroup['id']);
        }

        $this->view('project/mygroup', $data);
    }

    public function create() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $groupModel = $this->model('Group_model');
            $result = $groupModel->createGroup($_POST, $_SESSION['user_id']);

            if ($result) {
                // ส่งแจ้งเตือน Telegram
                // Log Activity
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'CREATE_GROUP', "สร้างกลุ่ม: " . $_POST['project_name_th']);

                require_once __DIR__ . '/../core/Notification.php';
                Notification::sendTelegram("🚀 <b>กลุ่มใหม่ถูกสร้าง:</b>\n" . $_POST['project_name_th']);
                
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถสร้างกลุ่มได้']);
            }
        }
    }

    public function update() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $groupModel = $this->model('Group_model');
            // ตรวจสอบว่าผู้ใช้มีกลุ่มจริงๆ หรือไม่
            $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
            
            if ($myGroup) {
                $result = $groupModel->updateGroup($myGroup['id'], $_POST);
                if ($result) {
                    $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'UPDATE_GROUP', "แก้ไขข้อมูลกลุ่ม ID: " . $myGroup['id']);
                    echo json_encode(['status' => 'success', 'message' => 'แก้ไขข้อมูลโครงงานเรียบร้อยแล้ว']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกข้อมูลได้']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลกลุ่มของคุณ']);
            }
        }
    }

    public function delete() {
        // 1. ตรวจสอบความปลอดภัย CSRF
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $groupModel = $this->model('Group_model');
            
            // 2. ตรวจสอบว่าผู้ใช้มีกลุ่มจริงๆ หรือไม่ (ป้องกันแฮกเกอร์ส่ง ID กลุ่มอื่นมาลบ)
            $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
            
            if ($myGroup) {
                $filesToDelete = $groupModel->deleteGroup($myGroup['id']);

                if ($filesToDelete !== false) {
                    // 3. ลบไฟล์จริงออกจากโฟลเดอร์ uploads (Disk Cleanup)
                    foreach ($filesToDelete as $f) {
                        $fullPath = "public/" . $f['file_path'];
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                    $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'DELETE_GROUP', "ยุบกลุ่ม ID: " . $myGroup['id']);
                    echo json_encode(['status' => 'success', 'message' => 'ยุบกลุ่มโครงงานเรียบร้อยแล้ว']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบกลุ่มที่คุณต้องการลบ']);
            }
        }
    }

    // API สำหรับค้นหาเพื่อน
    public function search_students() {
        $search = $_GET['q'] ?? '';
        $groupModel = $this->model('Group_model');
        $students = $groupModel->getAvailableStudents($search, $_SESSION['user_id']);
        echo json_encode($students);
    }

    // API สำหรับกดเพิ่มเพื่อน
    public function add_member() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $groupModel = $this->model('Group_model');
            $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
            
            if ($myGroup && $groupModel->addMember($myGroup['id'], $_POST['user_id'])) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'ADD_MEMBER', "เพิ่มสมาชิก UserID: " . $_POST['user_id']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเพิ่มสมาชิกได้ (เพื่อนอาจมีกลุ่มแล้ว)']);
            }
        }
    }

    // API สำหรับลบเพื่อน
    public function remove_member() {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $groupModel = $this->model('Group_model');
            $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
            
            // ป้องกันลบตัวเองออก (ต้องยุบกลุ่มแทน)
            if ($_POST['user_id'] == $_SESSION['user_id']) {
                echo json_encode(['status' => 'error', 'message' => 'คุณไม่สามารถลบตัวเองได้']);
                return;
            }

            if ($myGroup && $groupModel->removeMember($myGroup['id'], $_POST['user_id'])) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'REMOVE_MEMBER', "ลบสมาชิก UserID: " . $_POST['user_id']);
                echo json_encode(['status' => 'success']);
            }
        }
    }

    public function get_available_by_room() {
        $room = $_GET['room'] ?? '';
        if (empty($room)) {
            echo json_encode([]);
            return;
        }
        
        $groupModel = $this->model('Group_model');
        $students = $groupModel->getAvailableStudentsByRoom($room);
        echo json_encode($students);
    }
}