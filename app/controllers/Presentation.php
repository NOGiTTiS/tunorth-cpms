<?php
class Presentation extends Controller
{
    public function __construct()
    {
        $this->middleware();
    }

    // หน้าหลักสำหรับแสดงรายชื่อรอบนำเสนอ (สำหรับทุกคน)
    public function index()
    {
        $presentationModel = $this->model('Presentation_model');
        $groupModel = $this->model('Group_model');
        $yearModel = $this->model('Year_model');

        $currentYear = $yearModel->getCurrentYear();

        // ถ้าเป็น Student เช็คว่าตัวเองจองไปหรือยัง
        $myBooking = null;
        $myGroup = null;
        if ($_SESSION['user_role'] == 'STUDENT') {
            $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
            if ($myGroup) {
                $myBooking = $presentationModel->getBookingByGroup($myGroup['id']);
            }
        }

        // Calculate Week Range
        $weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
        $monday = new DateTime();
        $monday->setISODate((int)date('o'), (int)date('W') + $weekOffset);

        // Ensure we start at Monday
        if ($monday->format('N') != 1) {
            $monday->modify('last monday');
        }

        $friday = clone $monday;
        $friday->modify('+4 days');

        // Fetch slots only for this week
        // Note: getAllSlots might need update to support filtering, but for now we can filter in PHP or assume we fetch all
        // Ideally, we should add a method `getSlotsByDateRange` to Model, but let's filter in view for MVP or update model if needed.
        // Let's stick to getAllSlots for now and filter/organize in Controller or View.
        // Actually, fetching everything is bad if we have lots of data. Let's start by modifying the View to use this range.

        $weekDates = [];
        $tempDate = clone $monday;
        for ($i = 0; $i < 5; $i++) {
            $weekDates[] = $tempDate->format('Y-m-d');
            $tempDate->modify('+1 day');
        }

        $data = [
            'slots' => $presentationModel->getAllSlots($currentYear['year']), // We will filter this in view
            'current_year' => $currentYear,
            'my_booking' => $myBooking,
            'my_group' => $myGroup,
            'week_offset' => $weekOffset,
            'week_dates' => $weekDates,
            'week_range_str' => $monday->format('d M') . ' - ' . $friday->format('d M ' . $monday->format('Y'))
        ];

        $this->view('presentation/index', $data);
    }

    // หน้าจัดการสำหรับ Admin/Teacher
    public function manage()
    {
        // เช็คสิทธิ์
        if (!in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            header('Location: ' . BASE_URL . '/presentation');
            exit;
        }

        $presentationModel = $this->model('Presentation_model');
        $yearModel = $this->model('Year_model');

        $slots = $presentationModel->getAllSlots($yearModel->getCurrentYear()['year']);
        $bookings = $presentationModel->getAllBookings($yearModel->getCurrentYear()['year']);

        // Map bookings to slots
        $bookingsBySlot = [];
        foreach ($bookings as $booking) {
            $bookingsBySlot[$booking['slot_id']][] = $booking;
        }

        foreach ($slots as &$slot) {
            $slot['bookings'] = isset($bookingsBySlot[$slot['id']]) ? $bookingsBySlot[$slot['id']] : [];
        }

        $data = [
            'slots' => $slots,
            'years' => $yearModel->getAll(),
            'current_year' => $yearModel->getCurrentYear()
        ];

        // ถ้าจะให้ดีควรดึงรายชื่อคนที่จองมาแสดงด้วย (ทำ Ajax หรือดึงไปเลยก็ได้)
        // เดี๋ยวทำ View แล้วค่อยดูอีกที

        $this->view('presentation/manage', $data);
    }

    // API: เพิ่มรอบ
    public function add_slot()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            $model = $this->model('Presentation_model');
            if ($model->createSlot($_POST)) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'ADD_SLOT', "เพิ่มรอบนำเสนอ: " . $_POST['start_time']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเพิ่มข้อมูลได้']);
            }
        }
    }

    // API: แก้ไขรอบ
    public function update_slot()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            $model = $this->model('Presentation_model');
            if ($model->updateSlot($_POST)) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'UPDATE_SLOT', "แก้ไขรอบนำเสนอ ID: " . $_POST['id']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถแก้ไขข้อมูลได้']);
            }
        }
    }

    // API: ลบรอบ
    public function delete_slot()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            $model = $this->model('Presentation_model');
            // เช็คก่อนว่ามีคนจองไหม
            if ($model->deleteSlot($_POST['id'])) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'DELETE_SLOT', "ลบรอบนำเสนอ ID: " . $_POST['id']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบได้ (อาจมีผู้จองแล้ว)']);
            }
        }
    }

    // API: จองรอบ (Student)
    public function book()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['user_role'] == 'STUDENT') {
            $groupModel = $this->model('Group_model');
            $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);

            if (!$myGroup) {
                echo json_encode(['status' => 'error', 'message' => 'คุณยังไม่มีกลุ่มโครงงาน']);
                return;
            }

            $model = $this->model('Presentation_model');
            $result = $model->bookSlot($_POST['slot_id'], $myGroup['id']);

            if ($result['success']) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'BOOK_SLOT', "จองรอบนำเสนอ ID: " . $_POST['slot_id']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $result['message']]);
            }
        }
    }

    // API: ยกเลิกการจอง
    public function cancel()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = $this->model('Presentation_model');
            $groupModel = $this->model('Group_model');

            if ($_SESSION['user_role'] == 'STUDENT') {
                $myGroup = $groupModel->getGroupByUser($_SESSION['user_id']);
                if ($myGroup && $model->cancelBooking($_POST['booking_id'], $myGroup['id'])) {
                    $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'CANCEL_BOOKING', "ยกเลิกจอง ID: " . $_POST['booking_id']);
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถยกเลิกได้']);
                }
            }
        }
    }

    // --- Grading System ---

    public function grading($booking_id)
    {
        if (!in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            header('Location: ' . BASE_URL . '/presentation');
            exit;
        }

        $presentationModel = $this->model('Presentation_model');
        $booking = $presentationModel->getBookingById($booking_id);

        if (!$booking) {
            // Handle Not Found
            echo "Booking not found";
            return;
        }

        $existingScore = $presentationModel->getScoreByScorer($booking_id, $_SESSION['user_id']);

        // Fetch criteria from DB
        $criteria = $presentationModel->fetchCriteria();

        // Fallback if DB is empty (use hardcoded for safety during migration)
        if (empty($criteria)) {
            $criteria = []; // View will handle or we can populate default here
        }

        $data = [
            'booking' => $booking,
            'existing_score' => $existingScore,
            'criteria_list' => $criteria
        ];

        $this->view('presentation/grading', $data);
    }

    public function submit_score()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            $model = $this->model('Presentation_model');

            $criteria = $_POST['criteria'];
            $total = 0;
            foreach ($criteria as $score) {
                $total += (int)$score;
            }

            $data = [
                'booking_id' => $_POST['booking_id'],
                'scorer_id' => $_SESSION['user_id'],
                'criteria_data' => json_encode($criteria),
                'total_score' => $total,
                'comments' => $_POST['comments']
            ];

            if ($model->saveScore($data)) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'GRADE_PRESENTATION', "ให้คะแนนการนำเสนอ Booking ID: " . $_POST['booking_id']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'บันทึกคะแนนไม่สำเร็จ']);
            }
        }
    }

    // --- Criteria Management System ---

    public function criteria_manage()
    {
        if (!in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            header('Location: ' . BASE_URL . '/presentation');
            exit;
        }

        $model = $this->model('Presentation_model');

        // DEBUG: Check methods
        // echo "<pre>"; print_r(get_class_methods($model)); echo "</pre>"; die();

        $data = [
            'criteria' => $model->fetchCriteria()
        ];

        $this->view('presentation/criteria_manage', $data);
    }

    public function save_criteria()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            $model = $this->model('Presentation_model');

            $data = [
                'label' => $_POST['label'],
                'max_score' => $_POST['max_score'],
                'criteria_order' => $_POST['criteria_order']
            ];

            if (!empty($_POST['id'])) {
                $data['id'] = $_POST['id'];
                $result = $model->updateCriterion($data);
                $action = 'UPDATE_CRITERIA';
            } else {
                $result = $model->addCriterion($data);
                $action = 'ADD_CRITERIA';
            }

            if ($result) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], $action, "จัดการเกณฑ์การประเมิน: " . $_POST['label']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
            }
        }
    }

    public function delete_criteria()
    {
        $this->verifyCsrfToken();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            $model = $this->model('Presentation_model');
            if ($model->deleteCriterion($_POST['id'])) {
                $this->model('Log_model')->log($_SESSION['user_id'], $_SESSION['user_role'], 'DELETE_CRITERIA', "ลบเกณฑ์การประเมิน ID: " . $_POST['id']);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถลบได้']);
            }
        }
    }

    // --- Score Summary ---

    public function scores($booking_id)
    {
        if (!in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            header('Location: ' . BASE_URL . '/presentation');
            exit;
        }

        $model = $this->model('Presentation_model');
        $booking = $model->getBookingById($booking_id);

        if (!$booking) {
            echo "Booking not found";
            return;
        }

        $scores = $model->getScoresByBooking($booking_id);
        $criteria = $model->fetchCriteria();

        // Calculate Max Score (Sum of all active criteria max_score)
        $maxPossibleScore = 0;
        foreach ($criteria as $c) {
            $maxPossibleScore += $c['max_score'];
        }
        if ($maxPossibleScore == 0) $maxPossibleScore = 100; // Prevent division by zero

        $data = [
            'booking' => $booking,
            'scores' => $scores,
            'criteria' => $criteria,
            'max_possible_score' => $maxPossibleScore
        ];

        $this->view('presentation/scores', $data);
    }
    public function export_scores()
    {
        if (!in_array($_SESSION['user_role'], ['ADMIN', 'TEACHER'])) {
            header('Location: ' . BASE_URL . '/presentation');
            exit;
        }

        $model = $this->model('Presentation_model');
        $bookings = $model->getAllBookings(); // Fetch all bookings
        $criteria = $model->fetchCriteria();

        // Calculate Max Possible Score
        $maxPossibleScore = 0;
        foreach ($criteria as $c) {
            $maxPossibleScore += $c['max_score'];
        }
        if ($maxPossibleScore == 0) $maxPossibleScore = 100;

        // Headers for CSV
        $filename = "project_scores_" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // Byte Order Mark (BOM) for UTF-8 in Excel

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Project Name (TH)', 'Project Name (EN)', 'Advisor', 'Room', 'Scores (Teacher:Score)', 'Average Score', 'Final Score (20%)']);

        foreach ($bookings as $booking) {
            $scores = $model->getScoresByBooking($booking['id']);

            $totalSum = 0;
            $scorerCount = count($scores);
            $scoreDetails = [];

            foreach ($scores as $s) {
                $totalSum += $s['total_score'];
                $scoreDetails[] = $s['scorer_name'] . ':' . $s['total_score'];
            }

            $average = $scorerCount > 0 ? ($totalSum / $scorerCount) : 0;
            $finalScore = ($average / $maxPossibleScore) * 20;

            fputcsv($output, [
                $booking['project_name_th'],
                $booking['project_name_en'],
                $booking['advisor_name'] ?? '-',
                $booking['room'],
                implode(', ', $scoreDetails),
                number_format($average, 2),
                number_format($finalScore, 2)
            ]);
        }
        fclose($output);
        exit;
    }
}
