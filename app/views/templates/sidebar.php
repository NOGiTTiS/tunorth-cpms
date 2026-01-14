<!-- Overlay สำหรับมือถือ (คลิกที่ว่างแล้วปิดเมนู) -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="main-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white transform -translate-x-full transition-transform duration-300 ease-in-out md:relative md:translate-x-0 flex-shrink-0 min-h-screen">

    <!-- ปุ่มปิดสำหรับมือถือ -->
    <div class="flex justify-end p-4 md:hidden">
        <button onclick="toggleSidebar()" class="text-gray-400 hover:text-white text-2xl">✕</button>
    </div>

    <div class="p-6 text-center border-b border-slate-800">
        <?php
        $systemName = 'CPMS TU-North'; // Default
        $systemDesc = 'ระบบจัดการโครงงาน'; // Default

        if (!empty($data['settings'])) {
            $systemName = $data['settings']['system_name'] ?? $systemName;
            $systemDesc = $data['settings']['system_description'] ?? $systemDesc;
        } else if (isset($this) && method_exists($this, 'model')) {
            try {
                $settingsModel = $this->model('Settings_model');
                $dbSystemName = $settingsModel->get('system_name');
                $dbSystemDesc = $settingsModel->get('system_description');
                if ($dbSystemName) $systemName = $dbSystemName;
                if ($dbSystemDesc) $systemDesc = $dbSystemDesc;
            } catch (Exception $e) {
            }
        }
        ?>
        <h1 class="text-xl font-bold text-pink-500"><?php echo htmlspecialchars($systemName); ?></h1>
        <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($systemDesc); ?></p>
    </div>

    <nav class="p-4 space-y-2">
        <a href="<?php echo BASE_URL; ?>/dashboard" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
            <span class="mr-3">📊</span> Dashboard
        </a>

        <?php if ($_SESSION['user_role'] == 'STUDENT'): ?>
            <a href="<?php echo BASE_URL; ?>/project/mygroup" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'project') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">👥</span> จัดการกลุ่ม/สมาชิก
            </a>
            <a href="<?php echo BASE_URL; ?>/submission" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'submission') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">📁</span> ส่งงานเอกสาร
            </a>
            <a href="<?php echo BASE_URL; ?>/presentation" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'presentation') !== false && strpos($_SERVER['REQUEST_URI'], 'manage') === false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">📅</span> จองเวลานำเสนอ
            </a>
        <?php endif; ?>

        <?php if ($_SESSION['user_role'] == 'TEACHER'): ?>
            <a href="<?php echo BASE_URL; ?>/teacher/review" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'teacher/review') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">📝</span> ตรวจงานนักเรียน
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/progress" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'teacher/progress') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">📈</span> ตารางความก้าวหน้า
            </a>
            <a href="<?php echo BASE_URL; ?>/presentation/manage" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'presentation/manage') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">🗓️</span> จัดการรอบนำเสนอ
            </a>
        <?php endif; ?>

        <?php if ($_SESSION['user_role'] == 'ADMIN'): ?>
            <div class="pt-4 pb-2 px-4">
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Admin Menu</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/admin/users" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/users') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">👤</span> จัดการผู้ใช้งาน
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/rooms" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/rooms') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">🏫</span> ห้องเรียน & ครู
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/steps" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/steps') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">⚙️</span> ตั้งค่าขั้นตอนงาน
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/years" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/years') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">🗓️</span> จัดการปีการศึกษา
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/progress" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/progress') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">📈</span> ติดตามความก้าวหน้า
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/announcements" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/announcements') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">📢</span> จัดการประกาศ
            </a>
            <a href="<?php echo BASE_URL; ?>/presentation/manage" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'presentation/manage') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">🗓️</span> จัดการรอบนำเสนอ
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/settings" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/settings') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">🔧</span> ตั้งค่าระบบ
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/logs" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/logs') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">📜</span> บันทึกกิจกรรม
            </a>
        <?php endif; ?>

        <div class="pt-10 space-y-2">
            <a href="<?php echo BASE_URL; ?>/profile" class="flex items-center p-3 hover:bg-slate-800 rounded-lg transition-all <?php echo (strpos($_SERVER['REQUEST_URI'], 'profile') !== false) ? 'bg-pink-600 text-white' : 'text-gray-300'; ?>">
                <span class="mr-3">👤</span> ข้อมูลส่วนตัว
            </a>
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="flex items-center p-3 hover:bg-red-900/50 text-red-400 rounded-lg transition-all">
                <span class="mr-3">🚪</span> ออกจากระบบ
            </a>
        </div>
    </nav>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        if (sidebar.classList.contains('-translate-x-full')) {
            // เปิด
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            // ปิด
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>