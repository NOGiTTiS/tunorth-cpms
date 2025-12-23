<?php
// Try to connect using different hosts
$hosts = ['localhost', '127.0.0.1', 'db'];
$dbname = 'krusitti_cpms_db';
$username = 'krusitti_db';
$password = 'HpXENgteAbC8CzDuXXrQ';

$pdo = null;

foreach ($hosts as $host) {
    try {
        echo "Trying host: $host ... ";
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Success!\n";
        break;
    } catch (PDOException $e) {
        echo "Failed: " . $e->getMessage() . "\n";
    }
}

if (!$pdo) {
    die("Could not connect to database.\n");
}

try {
    // 1. Create academic_years table
    $sql = "CREATE TABLE IF NOT EXISTS academic_years (
        id INT AUTO_INCREMENT PRIMARY KEY,
        year VARCHAR(4) NOT NULL,
        term VARCHAR(1) DEFAULT '1',
        is_current TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'academic_years' created successfully.\n";

    // 2. Insert default data if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM academic_years");
    if ($stmt->fetchColumn() == 0) {
        $currentYear = date("Y") + 543;
        if (date("m") < 5) $currentYear--;
        
        // Clear old current flags just in case
        $pdo->exec("UPDATE academic_years SET is_current = 0");
        
        $sqlInsert = "INSERT INTO academic_years (year, term, is_current) VALUES (:year, '1', 1)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([':year' => $currentYear]);
        echo "Inserted default year: $currentYear\n";
        
        // Insert previous year
        $prevYear = $currentYear - 1;
        $pdo->exec("INSERT INTO academic_years (year, term, is_current) VALUES ('$prevYear', '1', 0)");
    } else {
        echo "Table already has data.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
