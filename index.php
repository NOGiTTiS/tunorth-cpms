<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// แก้ไข Path ให้เรียกจาก Root โดยตรง (ไม่ต้องมี ../)
require_once 'app/core/App.php';
require_once 'app/core/Controller.php';
require_once 'app/core/Database.php';

// กำหนด Base Path ของโปรเจกต์ (แก้ตรงนี้ถ้าเปลี่ยนโฟลเดอร์)
define('BASE_URL', '/tunorth-cpms');

// เริ่มการทำงานของแอป
$app = new App();
