<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Progress Matrix</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-full mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">ตารางความก้าวหน้าทุกกลุ่ม (Progress Matrix)</h2>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">REAL-TIME STATUS TRACKING</p>
                    </div>

                    <!-- Room Filter -->
                    <div class="flex items-center bg-slate-50 border border-gray-200 rounded-2xl p-2 px-4 shadow-inner">
                        <span class="text-xs font-bold text-gray-500 mr-3 hidden md:inline">ตัวกรองห้องเรียน:</span>
                        <select onchange="window.location.href='?room='+this.value" class="bg-transparent text-sm font-bold text-slate-700 outline-none cursor-pointer min-w-[150px]">
                            <option value="">🏫 ทุกห้องเรียน (ม.6)</option>
                            <?php for($i=1; $i<=15; $i++): $r = "6.$i"; ?>
                                <option value="<?php echo $r; ?>" <?php echo ($data['selected_room'] === $r) ? 'selected' : ''; ?>>
                                    ม.<?php echo $r; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Matrix Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col relative h-[calc(100vh-250px)]">
                    <div class="overflow-auto custom-scrollbar flex-1">
                        <table class="w-full text-left border-collapse min-w-[1200px]">
                            <thead class="bg-slate-50 text-slate-500 sticky top-0 z-20 shadow-sm">
                                <tr>
                                    <!-- Sticky Column: Project Info -->
                                    <th class="sticky left-0 bg-slate-50 z-30 px-6 py-4 border-b border-r border-gray-100 min-w-[300px] shadow-[4px_0_10px_-4px_rgba(0,0,0,0.05)] text-xs font-black uppercase tracking-widest text-gray-400">
                                        ข้อมูลโครงงาน
                                    </th>
                                    <?php foreach($data['steps'] as $step): ?>
                                        <th class="px-4 py-4 border-b border-gray-100 text-center min-w-[140px]">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 block"><?php echo htmlspecialchars($step['step_name']); ?></span>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                <?php if(empty($data['groups'])): ?>
                                    <tr><td colspan="100%" class="px-6 py-20 text-center text-gray-400 italic">ไม่พบข้อมูลกลุ่มโครงงานในห้องที่เลือก</td></tr>
                                <?php endif; ?>

                                <?php foreach($data['groups'] as $group): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <!-- Sticky Column Data -->
                                    <td class="sticky left-0 bg-white group-hover:bg-slate-50/50 z-10 px-6 py-4 border-r border-gray-100 shadow-[4px_0_10px_-4px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-shrink-0 w-10 h-10 bg-slate-100 text-slate-500 rounded-lg flex items-center justify-center font-black text-xs">
                                                <?php echo htmlspecialchars($group['room'] ?? '-'); ?>
                                            </div>
                                            <div>
                                                <a href="<?php echo BASE_URL . '/project/details/' . $group['id']; ?>" class="block group/link">
                                                    <p class="font-bold text-slate-800 line-clamp-2 leading-tight group-hover/link:text-pink-600 transition-colors underline decoration-dotted decoration-gray-300 underline-offset-4">
                                                        <?php echo htmlspecialchars($group['project_name_th']); ?>
                                                    </p>
                                                </a>
                                                <p class="text-[10px] text-gray-400 mt-1">ที่ปรึกษา: <?php echo htmlspecialchars($group['advisor_name'] ?: '-'); ?></p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status Columns -->
                                    <?php foreach($data['steps'] as $step): 
                                        $status = $data['matrix'][$group['id']][$step['id']] ?? null;
                                    ?>
                                        <td class="px-4 py-4 text-center border-gray-50">
                                            <div class="flex justify-center">
                                                <?php if($status === 'APPROVED'): ?>
                                                    <div class="w-8 h-8 rounded-lg bg-green-500 shadow-lg shadow-green-200 flex items-center justify-center text-white" title="ผ่านเรียบร้อย">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                <?php elseif($status === 'PENDING'): ?>
                                                    <div class="w-8 h-8 rounded-lg bg-amber-400 shadow-lg shadow-amber-100 flex items-center justify-center text-white animate-pulse" title="รอตรวจ">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </div>
                                                <?php elseif($status === 'REJECTED'): ?>
                                                    <div class="w-8 h-8 rounded-lg bg-rose-500 shadow-lg shadow-rose-200 flex items-center justify-center text-white" title="ต้องแก้ไข">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200" title="ยังไม่ส่ง"></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Legend Footer -->
                    <div class="px-6 py-4 bg-white border-t border-gray-100 flex flex-wrap gap-6 text-xs text-gray-500 justify-center md:justify-start sticky bottom-0 z-30 shadow-[0_-4px_10px_-4px_rgba(0,0,0,0.05)]">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-green-500 shadow-sm"></span> ผ่านอนุมัติ</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-amber-400 shadow-sm"></span> รอการตรวจสอบ</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-rose-500 shadow-sm"></span> ให้แก้ไข</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-slate-100 border border-slate-200"></span> ยังไม่ส่งงาน</div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<style>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
