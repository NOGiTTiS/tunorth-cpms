<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    
    <!-- Sidebar -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">System Logs</span>
            <button onclick="toggleSidebar()" class="p-2 bg-slate-800 rounded-lg text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-3xl font-bold text-slate-800">บันทึกกิจกรรม (Activity Logs)</h2>
                        <p class="text-gray-500 text-sm mt-1">ประวัติการใช้งานและกิจกรรมต่างๆ ภายในระบบ (แสดง 100 รายการล่าสุด)</p>
                    </div>
                    <button onclick="location.reload()" class="p-2 bg-white border border-gray-200 rounded-xl hover:bg-slate-50 transition text-slate-500">
                        🔄 Refresh
                    </button>
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
