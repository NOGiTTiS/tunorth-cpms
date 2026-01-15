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
}
