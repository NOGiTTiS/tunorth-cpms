<?php
// require_once __DIR__ . '/app/core/Database.php';

// Hack to force 127.0.0.1 if localhost fails in CLI
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
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

$db = (new DatabaseCLI())->getConnection();


$sql = "CREATE TABLE IF NOT EXISTS `presentation_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `scorer_id` int(11) NOT NULL,
  `criteria_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`criteria_data`)),
  `total_score` int(11) NOT NULL,
  `comments` text COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `scorer_id` (`scorer_id`),
  CONSTRAINT `presentation_scores_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `presentation_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presentation_scores_ibfk_2` FOREIGN KEY (`scorer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $db->exec($sql);
    echo "Table 'presentation_scores' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
