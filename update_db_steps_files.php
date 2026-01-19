<?php
// Standalone script to update project_steps table
class DatabaseCLI
{
    private $host = "localhost";
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

// Add columns if they don't exist
$columns = [
    'file_form_path' => "ALTER TABLE `project_steps` ADD `file_form_path` VARCHAR(255) NULL DEFAULT NULL AFTER `step_order`",
    'file_example_path' => "ALTER TABLE `project_steps` ADD `file_example_path` VARCHAR(255) NULL DEFAULT NULL AFTER `file_form_path`"
];

$checkSql = "SHOW COLUMNS FROM `project_steps` LIKE :col";
$stmt = $db->prepare($checkSql);

foreach ($columns as $col => $sql) {
    $stmt->execute([':col' => $col]);
    if ($stmt->fetch()) {
        echo "Column '$col' already exists.\n";
    } else {
        try {
            $db->exec($sql);
            echo "Added column '$col' successfully.\n";
        } catch (PDOException $e) {
            echo "Error adding column '$col': " . $e->getMessage() . "\n";
        }
    }
}
