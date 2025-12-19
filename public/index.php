<?php
session_start();
// ดึง Core ยันต์กันผีมาใช้
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';

// เริ่มการทำงานของแอป
$app = new App();