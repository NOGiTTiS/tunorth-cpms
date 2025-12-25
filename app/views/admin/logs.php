<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    
    <!-- Sidebar -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">System Logs</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                
                <!-- Filters -->
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 mb-6">
                    <form action="" method="GET" class="flex flex-col lg:flex-row items-center gap-4">
                        
                        <!-- Role Filter -->
                        <div class="w-full lg:w-auto">
                            <select name="role" class="w-full p-3 bg-slate-50 border border-gray-200 rounded-xl text-sm font-bold text-slate-600 outline-none focus:ring-2 focus:ring-pink-500">
                                <option value="">ทุกบทบาท (All Roles)</option>
                                <option value="ADMIN" <?php echo ($data['filters']['role'] == 'ADMIN') ? 'selected' : ''; ?>>ผู้ดูแลระบบ (Admin)</option>
                                <option value="TEACHER" <?php echo ($data['filters']['role'] == 'TEACHER') ? 'selected' : ''; ?>>ครูที่ปรึกษา (Teacher)</option>
                                <option value="STUDENT" <?php echo ($data['filters']['role'] == 'STUDENT') ? 'selected' : ''; ?>>นักเรียน (Student)</option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="w-full lg:w-auto">
                            <input type="date" name="date" value="<?php echo htmlspecialchars($data['filters']['date']); ?>" class="w-full p-3 bg-slate-50 border border-gray-200 rounded-xl text-sm font-bold text-slate-600 outline-none focus:ring-2 focus:ring-pink-500">
                        </div>

                        <!-- Search -->
                        <div class="w-full flex-1">
                            <div class="relative">
                                <span class="absolute left-4 top-3.5 text-gray-400">🔍</span>
                                <input type="text" name="q" value="<?php echo htmlspecialchars($data['filters']['search']); ?>" placeholder="ค้นหาชื่อ, กิจกรรม, หรือรายละเอียด..." class="w-full p-3 pl-10 bg-slate-50 border border-gray-200 rounded-xl text-sm font-bold text-slate-600 outline-none focus:ring-2 focus:ring-pink-500">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2 w-full lg:w-auto">
                            <button type="submit" class="flex-1 lg:flex-none px-6 py-3 bg-pink-600 text-white rounded-xl font-bold shadow-lg shadow-pink-200 hover:bg-pink-700 transition">
                                กรองข้อมูล
                            </button>
                            <?php if(!empty($data['filters']['role']) || !empty($data['filters']['date']) || !empty($data['filters']['search'])): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/logs" class="flex-1 lg:flex-none px-6 py-3 bg-gray-100 text-gray-500 rounded-xl font-bold hover:bg-gray-200 transition text-center">
                                    ล้างค่า
                                </a>
                            <?php endif; ?>
                        </div>

                    </form>
                </div>

                <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">รายการบันทึก (Logs List)</h2>
                    </div>
                    <div class="text-xs text-gray-400 font-bold bg-slate-100 px-3 py-1 rounded-lg">
                        แสดง 100 รายการล่าสุด
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">เวลา (Time)</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ผู้ใช้งาน (User)</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">กิจกรรม (Action)</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">รายละเอียด (Detail)</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">IP Address</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php if(empty($data['logs'])): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">
                                            ยังไม่มีบันทึกกิจกรรมในขณะนี้
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($data['logs'] as $log): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-mono">
                                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($log['user_id']): ?>
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600 mr-2 uppercase">
                                                        <?php echo mb_substr($log['full_name'] ?? '?', 0, 1); ?>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($log['full_name']); ?></p>
                                                        <p class="text-[10px] text-gray-400"><?php echo htmlspecialchars($log['user_role']); ?></p>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic">System / Guest</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                                $actionColor = 'text-slate-600 bg-slate-100';
                                                if(strpos($log['action'], 'LOGIN') !== false) $actionColor = 'text-green-600 bg-green-50';
                                                if(strpos($log['action'], 'DELETE') !== false) $actionColor = 'text-red-600 bg-red-50';
                                                if(strpos($log['action'], 'UPLOAD') !== false) $actionColor = 'text-blue-600 bg-blue-50';
                                                if(strpos($log['action'], 'UPDATE') !== false) $actionColor = 'text-amber-600 bg-amber-50';
                                            ?>
                                            <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wide <?php echo $actionColor; ?>">
                                                <?php echo htmlspecialchars($log['action']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($log['description']); ?>">
                                            <?php echo htmlspecialchars($log['description'] ?? '-'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-mono text-gray-400">
                                            <?php echo htmlspecialchars($log['ip_address']); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
