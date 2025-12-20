    <footer class="mt-10 pb-5 text-center text-gray-400 text-sm">
        <?php
            // ดึงข้อมูล Copyright จาก Database โดยตรง
            $copyright = '&copy; ' . date('Y') . ' โรงเรียนเตรียมอุดมศึกษา ภาคเหนือ. All rights reserved.';
            if (class_exists('Database')) {
                try {
                    $db = (new Database())->getConnection();
                    $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'site_copyright'");
                    $stmt->execute();
                    $row = $stmt->fetch();
                    if ($row && !empty($row['setting_value'])) {
                        $copyright = $row['setting_value'];
                    }
                } catch (Exception $e) {}
            }
            echo $copyright;
        ?>
    </footer>


</body>
</html>