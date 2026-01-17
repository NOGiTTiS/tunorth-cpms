<?php
// Standalone script to create criteria table and seed data
class DatabaseCLI
{
    private $host = "127.0.0.1";
    private $db_name = "krusitti_cpms_db";
    private $username = "krusitti_db";
    private $password = "HpXENgteAbC8CzDuXXrQ";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage() . "\n";
        }
        return $this->conn;
    }
}

$db = (new DatabaseCLI())->getConnection();

if (!$db) {
    die("Could not connect to database.\n");
}

// 1. Create Table
$sql = "CREATE TABLE IF NOT EXISTS `presentation_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `max_score` int(11) NOT NULL DEFAULT 10,
  `criteria_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $db->exec($sql);
    echo "Table 'presentation_criteria' created successfully.\n";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage() . "\n");
}

// 2. Seed Data
$default_criteria = [
    1 => 'เนื้อหาของโครงงานมีความน่าสนใจ และเป็นประโยชน์',
    2 => 'มีกระบวนการพัฒนาโครงงานอย่างเป็นระบบ',
    3 => 'มีการเลือกใช้เครื่องมือ โปรแกรม ได้อย่างเหมาะสม',
    4 => 'การประสานงานและสืบเสาะข้อมูลจากแหล่งเรียนรู้ในชุมชน',
    5 => 'ความคิดสร้างสรรค์ และความน่าสนใจของผลงาน',
    6 => 'ความสมบูรณ์ของผลงาน (เนื้อหา,ภาพประกอบ หรือ อื่นๆ)',
    7 => 'เทคนิคในการนำเสนอโครงงาน',
    8 => 'การนำเสนอเสียงดังฟังชัด และออกเสียงอักขระถูกต้อง',
    9 => 'การนำเสนอโครงงานทันตามเวลาที่กำหนด',
    10 => 'การแต่งกายของผู้นำเสนอโครงงานถูกต้องตามระเบียบ'
];

try {
    // Check if empty
    $stmt = $db->query("SELECT COUNT(*) FROM presentation_criteria");
    if ($stmt->fetchColumn() == 0) {
        $insertSql = "INSERT INTO presentation_criteria (label, criteria_order) VALUES (:label, :order)";
        $insertStmt = $db->prepare($insertSql);

        foreach ($default_criteria as $order => $label) {
            $insertStmt->execute([':label' => $label, ':order' => $order]);
        }
        echo "Inserted " . count($default_criteria) . " default criteria.\n";
    } else {
        echo "Table already has data, skipping seed.\n";
    }
} catch (PDOException $e) {
    echo "Error seeding data: " . $e->getMessage() . "\n";
}
