<?php
class Teacher extends Controller
{
    public function __construct()
    {
        $this->middleware();
        // ตรวจสอบว่าเป็นครูจริงไหม
        if ($_SESSION['user_role'] !== 'TEACHER') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    public function review()
    {
        $teacherModel = $this->model('Teacher_model');

        // เปลี่ยนจาก 'mine' เป็น 'all' เพื่อให้เป็นค่าเริ่มต้น
        $mode = $_GET['mode'] ?? 'all';
        $room = $_GET['room'] ?? ''; // Filter by room

        // Default Year Logic
        $yearModel = $this->model('Year_model');
        $currentYearObj = $yearModel->getCurrentYear();
        $defaultYear = $currentYearObj['year'];

        $year = isset($_GET['year']) ? ($_GET['year'] !== '' ? $_GET['year'] : null) : $defaultYear;
        $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

        // Create Room Model Instance
        $roomModel = $this->model('Room_model');
        $assignedRooms = $roomModel->getAssignedRoomsByTeacher($_SESSION['user_id']); // Returns ['6.1', '6.10']

        // Filter Logic
        if (!empty($assignedRooms)) {
            // If user selected a room that is NOT in their assigned list, reset to empty (All Assigned)
            if ($room !== '' && !in_array($room, $assignedRooms)) {
                $room = '';
            }
        }

        // Pass filterRooms to model? OR handle in Controller loop?
        // Ideally, if a teacher has assigned rooms, they CANNOT see others. 
        // So we should enforce it in the Model Query too.

        $limitRooms = !empty($assignedRooms) ? $assignedRooms : null;

        if ($mode === 'mine') {
            $submissions = $teacherModel->getPendingSubmissions($_SESSION['user_id'], null, $room, $year, $group_id);
        } else {
            // Updated Model Call to accept limitRooms
            $submissions = $teacherModel->getPendingSubmissions(null, null, $room, $year, $group_id, $limitRooms);
        }

        $yearModel = $this->model('Year_model');

        $data = [
            'submissions' => $submissions,
            'current_mode' => $mode,
            'selected_room' => $room,
            'selected_year' => $year,
            'years' => $yearModel->getAll(),
            'assigned_rooms' => $assignedRooms // Pass to view to populate dropdown
        ];
        $this->view('teacher/review', $data);
    }

    public function grade()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $teacherModel = $this->model('Teacher_model');
            $success = $teacherModel->updateReview($_POST['id'], $_POST['status'], $_POST['comment'], $_POST['score'] ?? null);

            if ($success) {
                // Log Activity
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'GRADE', "ตรวจงาน: " . $_POST['project_name'] . " (" . $_POST['status'] . ")");

                // ส่ง Telegram แจ้งเตือนนักเรียน (Optional)
                require_once __DIR__ . '/../core/Notification.php';

                $msg = "🔔 <b>ครูตรวจงานแล้ว!</b>\n" .
                    "โครงงาน: " . $_POST['project_name'] . "\n" .
                    "ห้อง: ม." . ($_POST['room'] ?? '-') . "\n" .
                    "งาน: " . ($_POST['step_name'] ?? '-') . "\n" .
                    "ผลการตรวจ: " . $_POST['status'];

                Notification::sendTelegram($msg, 'grading');

                echo json_encode(['status' => 'success']);
            }
        }
    }

    public function export_grades()
    {
        $teacherModel = $this->model('Teacher_model');

        $mode = $_GET['mode'] ?? 'mine';
        $advisor_id = ($mode === 'mine') ? $_SESSION['user_id'] : null;

        $data = $teacherModel->getGradeSheetData($advisor_id);
        $raw = $data['students'];
        $steps = $data['steps'];

        // Pivot Data
        $students = [];
        foreach ($raw as $row) {
            $sid = $row['student_id'];
            if (!isset($students[$sid])) {
                $students[$sid] = [
                    'student_id' => $row['student_id'],
                    'full_name' => $row['full_name'],
                    'room' => $row['room'],
                    'project_name' => $row['project_name_th'],
                    'steps' => []
                ];
            }
            if ($row['step_id']) {
                $students[$sid]['steps'][$row['step_id']] = [
                    'status' => $row['status'],
                    'score' => $row['score']
                ];
            }
        }

        // Export as CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=grade_sheet_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');

        // Add BOM for Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header Row
        $header = ['รหัสนักเรียน', 'ชื่อ-นามสกุล', 'ห้อง', 'ชื่อโครงงาน'];
        foreach ($steps as $step) {
            $header[] = $step['step_name'] . ' (สถานะ)';
            $header[] = $step['step_name'] . ' (คะแนน)';
        }
        fputcsv($output, $header);

        // Data Rows
        foreach ($students as $student) {
            $row = [
                $student['student_id'],
                $student['full_name'],
                $student['room'],
                $student['project_name']
            ];
            foreach ($steps as $step) {
                $stepData = $student['steps'][$step['id']] ?? null;
                $status = $stepData ? $stepData['status'] : '-';
                $score = ($stepData && isset($stepData['score'])) ? $stepData['score'] : '-';

                // Translate Status
                if ($status == 'APPROVED') $status = 'ผ่าน';
                elseif ($status == 'REJECTED') $status = 'ไม่ผ่าน';
                elseif ($status == 'PENDING') $status = 'รอตรวจ';

                $row[] = $status;
                $row[] = $score;
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    public function progress()
    {
        // Reuse Admin_model for Progress Matrix logic to avoid duplication
        $adminModel = $this->model('Admin_model');
        $yearModel = $this->model('Year_model');

        $currentYearObj = $yearModel->getCurrentYear();
        $defaultYear = $currentYearObj['year'];

        $roomModel = $this->model('Room_model');
        $assignedRooms = $roomModel->getAssignedRoomsByTeacher($_SESSION['user_id']);

        if (isset($_GET['year'])) {
            $year = $_GET['year'] !== '' ? $_GET['year'] : null;
        } else {
            $year = $defaultYear;
        }

        // Base Room Logic
        // If user selects a room, check if they are allowed to see it
        // If no room selected, show ONLY their assigned rooms (pass array to model)

        $requestedRoom = isset($_GET['room']) && $_GET['room'] !== '' ? $_GET['room'] : null;
        $filterRoom = null;

        if (!empty($assignedRooms)) {
            if ($requestedRoom) {
                // If specific room requested, check if allowed
                if (in_array($requestedRoom, $assignedRooms)) {
                    $filterRoom = $requestedRoom;
                } else {
                    $filterRoom = $assignedRooms; // Fallback to all assigned if unauthorized
                }
            } else {
                // No specific room -> Show all assigned
                $filterRoom = $assignedRooms;
            }
        } else {
            // No assignments -> Check if they should see nothing or everything?
            // Current policy: If no assignments, maybe default to everything (like Admin) OR nothing?
            // Let's assume strict: If no assignments, they see nothing? Or everything?
            // "Teacher" role without assignments usually implies generic teacher or old system behavior.
            // Let's keep existing behavior (allow all) if empty, or we can restrict.
            // Based on user request "only assigned", let's assume they might not have any.
            // Let's default to $requestedRoom (allow all) if assignments are empty.
            // BUT usually we want to restrict. Let's Pass $requestedRoom as is if no assignments found.
            $filterRoom = $requestedRoom;
        }

        // Pass filterRoom (string|array|null) to model
        $data = $adminModel->getProgressMatrix($filterRoom, $year);

        // Update selection state for view
        $data['selected_room'] = $requestedRoom; // Keep the requested one for UI state if valid
        $data['selected_year'] = $year;
        $data['assigned_rooms'] = $assignedRooms; // Pass to view for dropdown logic

        $data['years'] = $yearModel->getAll();

        // Reuse the Admin view because it's identical
        $this->view('admin/progress', $data);
    }
}
