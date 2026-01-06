<?php
class Room_model
{
    private $db;
    private $table = 'teacher_assignments';

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->initializeTable();
    }

    private function initializeTable()
    {
        // Table for assignments
        $sql = "CREATE TABLE IF NOT EXISTS teacher_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teacher_id INT NOT NULL,
            room VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_assignment (teacher_id, room),
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            // Ignore if exists
        }
    }

    public function getAssignments()
    {
        $sql = "SELECT ta.id, ta.teacher_id, ta.room, u.full_name as teacher_name 
                FROM teacher_assignments ta
                JOIN users u ON ta.teacher_id = u.id
                ORDER BY ta.room ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignedRoomsByTeacher($teacherId)
    {
        $sql = "SELECT room FROM teacher_assignments WHERE teacher_id = :tid ORDER BY room ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tid', $teacherId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Returns array of strings e.g., ['6.1', '6.2']
    }

    public function assign($teacherId, $room)
    {
        $sql = "INSERT INTO teacher_assignments (teacher_id, room) VALUES (:tid, :room)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute([':tid' => $teacherId, ':room' => $room]);
        } catch (PDOException $e) {
            return false; // Likely duplicate
        }
    }

    public function remove($teacherId, $room)
    {
        $sql = "DELETE FROM teacher_assignments WHERE teacher_id = :tid AND room = :room";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':tid' => $teacherId, ':room' => $room]);
    }

    // Helper to get all classrooms (distinct from users or hardcoded list)
    // For now, we can query distinct rooms from users to suggest, 
    // or just return a generated list 6.1 - 6.15 as requested commonly.
    public function getAvailableRooms()
    {
        $rooms = [];
        for ($i = 1; $i <= 15; $i++) {
            $rooms[] = "6.$i";
        }
        return $rooms;
    }
}
