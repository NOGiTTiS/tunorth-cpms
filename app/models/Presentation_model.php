<?php
class Presentation_model
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // ดึงข้อมูลรอบนำเสนอทั้งหมด
    public function getAllSlots($year = null)
    {
        $sql = "SELECT s.*, 
                (SELECT COUNT(*) FROM presentation_bookings b WHERE b.slot_id = s.id) as booked_count 
                FROM presentation_slots s 
                WHERE 1=1";

        $params = [];
        if ($year) {
            $sql .= " AND s.academic_year = :year";
            $params[':year'] = $year;
        }

        $sql .= " ORDER BY s.start_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงข้อมูลการจองทั้งหมดในปีการศึกษา (สำหรับหน้า Manage)
    public function getAllBookings($year = null)
    {
        $sql = "SELECT b.*, g.project_name_th, g.project_name_en, g.room, u.full_name as advisor_name
                FROM presentation_bookings b
                JOIN presentation_slots s ON b.slot_id = s.id
                JOIN project_groups g ON b.group_id = g.id
                LEFT JOIN users u ON g.advisor_id = u.id
                WHERE 1=1";

        $params = [];
        if ($year) {
            $sql .= " AND s.academic_year = :year";
            $params[':year'] = $year;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงรายชื่อกลุ่มที่จองในรอบนี้
    public function getBookingsBySlot($slot_id)
    {
        $sql = "SELECT b.*, g.project_name_th, g.project_name_en, u.full_name as advisor_name
                FROM presentation_bookings b
                JOIN project_groups g ON b.group_id = g.id
                LEFT JOIN users u ON g.advisor_id = u.id
                WHERE b.slot_id = :slot_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slot_id' => $slot_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // สร้างรอบนำเสนอ
    public function createSlot($data)
    {
        $sql = "INSERT INTO presentation_slots (academic_year, start_time, end_time, location, max_groups) 
                VALUES (:year, :start, :end, :location, :max)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':year' => $data['academic_year'],
            ':start' => $data['start_time'],
            ':end' => $data['end_time'],
            ':location' => $data['location'],
            ':max' => $data['max_groups'] ?? 1
        ]);
    }

    // อัปเดตรอบนำเสนอ
    public function updateSlot($data)
    {
        $sql = "UPDATE presentation_slots 
                SET start_time = :start, end_time = :end, location = :location, max_groups = :max 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $data['id'],
            ':start' => $data['start_time'],
            ':end' => $data['end_time'],
            ':location' => $data['location'],
            ':max' => $data['max_groups']
        ]);
    }

    // ลบรอบนำเสนอ (ต้องไม่มีคนจอง)
    public function deleteSlot($id)
    {
        // เช็คก่อนว่ามีคนจองไหม
        $check = $this->db->prepare("SELECT COUNT(*) FROM presentation_bookings WHERE slot_id = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            return false; // ห้ามลบถ้ามีคนจอง
        }

        $stmt = $this->db->prepare("DELETE FROM presentation_slots WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // เช็คว่ากลุ่มนี้จองไปหรือยัง
    public function getBookingByGroup($group_id)
    {
        $sql = "SELECT b.*, s.start_time, s.end_time, s.location 
                FROM presentation_bookings b
                JOIN presentation_slots s ON b.slot_id = s.id
                WHERE b.group_id = :group_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':group_id' => $group_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // จองรอบ
    public function bookSlot($slot_id, $group_id)
    {
        // 1. เช็คว่ากลุ่มนี้จองหรือยัง
        if ($this->getBookingByGroup($group_id)) {
            return ['success' => false, 'message' => 'กลุ่มของคุณได้จองเวลานำเสนอไปแล้ว'];
        }

        // 2. เช็คว่ารอบนี้ว่างไหม
        $sqlSlot = "SELECT s.*, 
                    (SELECT COUNT(*) FROM presentation_bookings b WHERE b.slot_id = s.id) as booked_count 
                    FROM presentation_slots s WHERE s.id = :id";
        $stmtSlot = $this->db->prepare($sqlSlot);
        $stmtSlot->execute([':id' => $slot_id]);
        $slot = $stmtSlot->fetch(PDO::FETCH_ASSOC);

        if (!$slot) {
            return ['success' => false, 'message' => 'ไม่พบรอบนำเสนอ'];
        }

        if ($slot['booked_count'] >= $slot['max_groups']) {
            return ['success' => false, 'message' => 'รอบนี้เต็มแล้ว'];
        }

        // 3. ทำการจอง
        $sqlBook = "INSERT INTO presentation_bookings (slot_id, group_id) VALUES (:sid, :gid)";
        $stmtBook = $this->db->prepare($sqlBook);
        if ($stmtBook->execute([':sid' => $slot_id, ':gid' => $group_id])) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'];
        }
    }

    // ยกเลิกการจอง
    public function cancelBooking($booking_id, $group_id)
    {
        // ต้องเช็คว่าเป็นของกลุ่มตัวเองจริงๆ (หรือถ้าเป็น Admin/Teacher ก็ต้อง bypass logic นี้ใน Controller)
        $sql = "DELETE FROM presentation_bookings WHERE id = :bid AND group_id = :gid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':bid' => $booking_id, ':gid' => $group_id]);
    }

    public function deleteBookingAdmin($booking_id)
    {
        $sql = "DELETE FROM presentation_bookings WHERE id = :bid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':bid' => $booking_id]);
    }

    // --- ส่วนของการให้คะแนน (Grading) ---

    // บันทึกคะแนน
    public function saveScore($data)
    {
        // เช็คว่าเคยให้คะแนนไปหรือยัง ถ้าเคยแล้วให้ Update
        $existing = $this->getScoreByScorer($data['booking_id'], $data['scorer_id']);

        if ($existing) {
            $sql = "UPDATE presentation_scores 
                    SET criteria_data = :criteria, total_score = :total, comments = :comments 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $existing['id'],
                ':criteria' => $data['criteria_data'], // JSON string
                ':total' => $data['total_score'],
                ':comments' => $data['comments']
            ]);
        } else {
            $sql = "INSERT INTO presentation_scores (booking_id, scorer_id, criteria_data, total_score, comments) 
                    VALUES (:bid, :sid, :criteria, :total, :comments)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':bid' => $data['booking_id'],
                ':sid' => $data['scorer_id'],
                ':criteria' => $data['criteria_data'],
                ':total' => $data['total_score'],
                ':comments' => $data['comments']
            ]);
        }
    }

    // ดึงคะแนนทั้งหมดของการจองนี้
    public function getScoresByBooking($booking_id)
    {
        $sql = "SELECT ps.*, u.full_name as scorer_name 
                FROM presentation_scores ps
                JOIN users u ON ps.scorer_id = u.id
                WHERE ps.booking_id = :bid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':bid' => $booking_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงคะแนนของกรรมการคนนี้ สำหรับการจองนี้
    public function getScoreByScorer($booking_id, $scorer_id)
    {
        $sql = "SELECT * FROM presentation_scores WHERE booking_id = :bid AND scorer_id = :sid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':bid' => $booking_id, ':sid' => $scorer_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ดึงข้อมูลการจองรายรายการ (สำหรับหน้า Grading)
    public function getBookingById($booking_id)
    {
        $sql = "SELECT b.*, g.project_name_th, g.project_name_en, g.room, u.full_name as advisor_name
                FROM presentation_bookings b
                JOIN project_groups g ON b.group_id = g.id
                LEFT JOIN users u ON g.advisor_id = u.id
                WHERE b.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- Criteria Management (CRUD) ---

    public function fetchCriteria()
    {
        $sql = "SELECT * FROM presentation_criteria WHERE is_active = 1 ORDER BY criteria_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCriterion($data)
    {
        $sql = "INSERT INTO presentation_criteria (label, max_score, criteria_order) VALUES (:label, :max, :order)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':label' => $data['label'],
            ':max' => $data['max_score'] ?? 10,
            ':order' => $data['criteria_order'] ?? 0
        ]);
    }

    public function updateCriterion($data)
    {
        $sql = "UPDATE presentation_criteria SET label = :label, max_score = :max, criteria_order = :order WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $data['id'],
            ':label' => $data['label'],
            ':max' => $data['max_score'],
            ':order' => $data['criteria_order']
        ]);
    }

    public function deleteCriterion($id)
    {
        // Check if used? Maybe soft delete? For now hard delete.
        // Or better, set is_active = 0 to preserve history.
        $sql = "UPDATE presentation_criteria SET is_active = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
