<?php
require_once 'app/core/Database.php';

try {
    $db = (new Database())->getConnection();
    $sql = "ALTER TABLE project_groups ADD academic_year VARCHAR(10) NOT NULL DEFAULT '2567'";
    $db->exec($sql);
    echo "Successfully added academic_year column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column academic_year already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
