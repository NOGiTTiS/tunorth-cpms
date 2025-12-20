<?php
class Admin_model {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getAllUsers($limit = null, $offset = 0, $search = '') {
        $sql = "SELECT * FROM users WHERE 1=1";
        
        if (!empty($search)) {
            $sql .= " AND (full_name LIKE :search OR student_id LIKE :search OR email LIKE :search)";
        }
        
        $sql .= " ORDER BY role ASC, full_name ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }

        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAllUsers($search = '') {
        $sql = "SELECT COUNT(*) FROM users WHERE 1=1";
        if (!empty($search)) {
            $sql .= " AND (full_name LIKE :search OR student_id LIKE :search OR email LIKE :search)";
        }
        $stmt = $this->db->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        $query = "INSERT INTO users (full_name, email, password, role, student_id, room) 
                  VALUES (:name, :email, :pass, :role, :sid, :room)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $data['full_name'],
            ':email' => $data['email'],
            ':pass' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'],
            ':sid'  => (!empty($data['student_id'])) ? $data['student_id'] : null,
            ':room' => (!empty($data['room'])) ? $data['room'] : null
        ]);
    }

    public function updateUser($data) {
        $sql = "UPDATE users SET full_name = :name, email = :email, role = :role, 
                student_id = :sid, room = :room";
        
        $params = [
            ':name'  => $data['full_name'],
            ':email' => $data['email'],
            ':role'  => $data['role'],
            ':sid'   => (!empty($data['student_id'])) ? $data['student_id'] : null,
            ':room'  => (!empty($data['room'])) ? $data['room'] : null,
            ':id'    => $data['id']
        ];

        if (!empty($data['password'])) {
            $sql .= ", password = :pass";
            $params[':pass'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteUser($id) {
        if ($id == $_SESSION['user_id']) return false;
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ฟังก์ชันดึงสถิติ (ใช้ใน Dashboard)
    public function getSummaryStats($room = null) {
        $stats = [];
        
        // 1. นับจำนวนกลุ่มโครงงาน (กรองตามห้องของสมาชิกในกลุ่ม)
        $sqlGroups = "SELECT COUNT(DISTINCT g.id) as total FROM project_groups g";
        if ($room) {
            $sqlGroups .= " JOIN group_members gm ON g.id = gm.group_id 
                            JOIN users u ON gm.user_id = u.id 
                            WHERE u.room = :room";
        }
        $stmt = $this->db->prepare($sqlGroups);
        if ($room) $stmt->bindParam(':room', $room);
        $stmt->execute();
        $stats['total_groups'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // 2. นับจำนวนผู้ใช้งานแยกตาม Role (ถ้าเลือกห้อง จะนับเฉพาะนักเรียนในห้องนั้น)
        $sqlUsers = "SELECT role, COUNT(*) as count FROM users WHERE 1=1";
        if ($room) {
            // ถ้าเลือกห้อง ให้นับเฉพาะนักเรียนห้องนั้น ส่วนครูและ Admin ให้แสดงยอดเดิม (หรือ 0)
            $sqlUsers .= " AND (room = :room OR role != 'STUDENT')";
        }
        $sqlUsers .= " GROUP BY role";
        $stmt = $this->db->prepare($sqlUsers);
        if ($room) $stmt->bindParam(':room', $room);
        $stmt->execute();
        $stats['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. สถิติความก้าวหน้า (กราฟ) - กรองตามห้อง
        $sqlSteps = "SELECT ps.step_name, COUNT(DISTINCT s.id) as approved_count 
                     FROM project_steps ps 
                     LEFT JOIN submissions s ON ps.id = s.step_id AND s.status = 'APPROVED'
                     LEFT JOIN group_members gm ON s.group_id = gm.group_id
                     LEFT JOIN users u ON gm.user_id = u.id";
        if ($room) {
            $sqlSteps .= " WHERE u.room = :room";
        }
        $sqlSteps .= " GROUP BY ps.id ORDER BY ps.step_order";
        
        $stmt = $this->db->prepare($sqlSteps);
        if ($room) $stmt->bindParam(':room', $room);
        $stmt->execute();
        $stats['step_progress'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. ภาระงานครูที่ปรึกษา (ดูเฉพาะกลุ่มที่มีเด็กห้องที่เลือก)
        $sqlLoad = "SELECT u_adv.full_name, COUNT(DISTINCT g.id) as group_count 
                    FROM users u_adv 
                    LEFT JOIN project_groups g ON u_adv.id = g.advisor_id 
                    LEFT JOIN group_members gm ON g.id = gm.group_id
                    LEFT JOIN users u_std ON gm.user_id = u_std.id";
        if ($room) {
            $sqlLoad .= " WHERE u_std.room = :room";
        } else {
            $sqlLoad .= " WHERE u_adv.role = 'TEACHER'";
        }
        $sqlLoad .= " GROUP BY u_adv.id";
        
        $stmt = $this->db->prepare($sqlLoad);
        if ($room) $stmt->bindParam(':room', $room);
        $stmt->execute();
        $stats['advisor_load'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    // --- Steps Management ---
    public function getAllSteps() {
        $stmt = $this->db->query("SELECT * FROM project_steps ORDER BY step_order ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStepOrder($id, $new_order) {
        $stmt = $this->db->prepare("UPDATE project_steps SET step_order = :ord WHERE id = :id");
        return $stmt->execute([':ord' => $new_order, ':id' => $id]);
    }

    public function createStep($data) {
        // หาค่าลำดับสูงสุดปัจจุบันเพื่อตั้งค่าลำดับถัดไป (Auto-increment order)
        $stmt_max = $this->db->query("SELECT MAX(step_order) as max_order FROM project_steps");
        $max = $stmt_max->fetch(PDO::FETCH_ASSOC);
        $next_order = ($max['max_order'] ?? 0) + 1;

        $query = "INSERT INTO project_steps (step_name, step_order) VALUES (:name, :ord)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $data['step_name'],
            ':ord' => $next_order
        ]);
    }

    public function updateStep($data) {
        $query = "UPDATE project_steps SET step_name = :name, step_order = :ord WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name' => $data['step_name'],
            ':ord' => $data['step_order'],
            ':id' => $data['id']
        ]);
    }

    public function deleteStep($id) {
        // ตรวจสอบก่อนว่ามีนักเรียนส่งงานในขั้นตอนนี้หรือยัง (Data Integrity)
        $check = $this->db->prepare("SELECT COUNT(*) FROM submissions WHERE step_id = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            return false; // ไม่ให้ลบหากมีงานค้างอยู่
        }

        $stmt = $this->db->prepare("DELETE FROM project_steps WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ตรวจสอบว่ามี Email นี้ในระบบหรือยัง
    public function checkEmailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ? true : false;
    }

    // ฟังก์ชันนำเข้าข้อมูล (ใช้ใน Loop ของ Controller)
    public function importUser($data) {
        $query = "INSERT INTO users (full_name, email, password, role, student_id, room) 
                VALUES (:name, :email, :pass, :role, :sid, :room)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':name'  => $data['full_name'],
            ':email' => $data['email'],
            ':pass'  => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role'  => $data['role'],
            ':sid'   => !empty($data['student_id']) ? $data['student_id'] : null,
            ':room'  => !empty($data['room']) ? $data['room'] : null
        ]);
    }

    public function getAllAnnouncements() {
        $stmt = $this->db->query("SELECT * FROM announcements ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAnnouncement($data) {
        $query = "INSERT INTO announcements (title, content, type) VALUES (:title, :content, :type)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':title'   => $data['title'],
            ':content' => $data['content'],
            ':type'    => $data['type']
        ]);
    }

    public function deleteAnnouncement($id) {
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function resetUserPassword($id, $new_password) {
        $query = "UPDATE users SET password = :pass WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        // เข้ารหัสผ่านใหม่ก่อนบันทึกเสมอ
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        return $stmt->execute([
            ':pass' => $hashed_password,
            ':id'   => $id
        ]);
    }
    public function getProgressMatrix($room = null) {
        // 1. Get Groups (and filtering)
        // Group by project to avoid duplicates, use MIN(room) as representative room
        $sql = "SELECT g.id, g.project_name_th, g.advisor_name, MIN(u.room) as room
                FROM project_groups g
                JOIN group_members gm ON g.id = gm.group_id
                JOIN users u ON gm.user_id = u.id
                WHERE u.role = 'STUDENT'";
        
        if ($room) {
            $sql .= " AND u.room = :room";
        }
        
        $sql .= " GROUP BY g.id ORDER BY room ASC, g.id ASC";
        
        $stmt = $this->db->prepare($sql);
        if ($room) $stmt->bindValue(':room', $room);
        $stmt->execute();
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Get All Steps
        $stmtSteps = $this->db->query("SELECT id, step_name FROM project_steps ORDER BY step_order ASC");
        $steps = $stmtSteps->fetchAll(PDO::FETCH_ASSOC);

        // 3. Get Submission Statuses
        $sqlSub = "SELECT group_id, step_id, status FROM submissions"; 
        $stmtSub = $this->db->query($sqlSub);
        $rawSubs = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

        // Map submissions: [group_id][step_id] = status
        $matrix = [];
        foreach($rawSubs as $s) {
            $matrix[$s['group_id']][$s['step_id']] = $s['status'];
        }

        return [
            'groups' => $groups, 
            'steps' => $steps, 
            'matrix' => $matrix
        ];
    }
}