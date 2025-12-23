<?php
class Dashboard extends Controller {
    public function __construct() {
        $this->middleware(); // ตรวจสอบล็อกอินทุกครั้งที่เข้าคลาสนี้
    }

    public function index() {
        $this->middleware();
        
        // 1. โหลด Model ที่จำเป็น
        $adminModel = $this->model('Admin_model');
        $groupModel = $this->model('Group_model');
        $subModel = $this->model('Submission_model');
        
        // 2. รับค่า Room Filter (สำหรับ Admin)
        $room = isset($_GET['room']) && $_GET['room'] !== '' ? $_GET['room'] : null;
        $year = isset($_GET['year']) && $_GET['year'] !== '' ? $_GET['year'] : null;

        // 3. เตรียมข้อมูลพื้นฐาน (ประกาศต้องมีให้ทุก Role เห็น)
        $data = [
            'title' => 'Dashboard',
            'user_role' => $_SESSION['user_role'],
            'user_name' => $_SESSION['user_name'],
            'current_room' => $room,
            'current_year' => $year,
            'announcements' => $adminModel->getAllAnnouncements() // <-- เพิ่มบรรทัดนี้
        ];

        // 4. Logic แยกตาม Role
        if ($_SESSION['user_role'] == 'ADMIN') {
            $data['summary'] = $adminModel->getSummaryStats($room, $year);
            $data['chart_labels'] = array_column($data['summary']['step_progress'], 'step_name');
            $data['chart_data'] = array_column($data['summary']['step_progress'], 'approved_count');
        } 
        
        elseif ($_SESSION['user_role'] == 'STUDENT') {
            $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
            if ($myGroup) {
                $data['progress'] = $subModel->getCalculateProgress($myGroup['id']);
                $data['project_name'] = $myGroup['project_name_th'];
                $nextTask = $subModel->getNextPendingTask($myGroup['id']);
                $data['next_task'] = $nextTask ? $nextTask['step_name'] : 'ส่งงานครบทุกขั้นตอนแล้ว';
            } else {
                $data['progress'] = 0;
                $data['project_name'] = 'ยังไม่มีกลุ่มโครงงาน';
                $data['next_task'] = 'กรุณาสร้างกลุ่มก่อน';
            }
        }

        elseif ($_SESSION['user_role'] == 'TEACHER') {
            $teacherModel = $this->model('Teacher_model');
            $submissions = $teacherModel->getPendingSubmissions(null, 'PENDING');
            $data['pending_count'] = count($submissions);
        }

        $this->view('dashboard/index', $data);
    }
}