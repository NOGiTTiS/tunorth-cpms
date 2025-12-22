<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <!-- 1. Sidebar -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <!-- 2. Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header: แสดงเฉพาะหน้าจอมือถือ -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-pink-600 rounded flex items-center justify-center font-bold text-xs text-white">CP</div>
                <span class="font-bold text-pink-500 tracking-tight">CPMS Dashboard</span>
            </div>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                
                <!-- Section: Welcome Message -->
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-800">สวัสดี, <?php echo htmlspecialchars($data['user_name']); ?></h2>
                        <div class="flex items-center mt-2">
                            <span class="bg-pink-100 text-pink-600 px-3 py-1 rounded-full text-[10px] font-black mr-2 uppercase tracking-widest">
                                <?php echo htmlspecialchars($data['user_role']); ?>
                            </span>
                            <p class="text-gray-400 text-sm italic">ยินดีต้อนรับเข้าสู่ระบบจัดการโครงงานคอมพิวเตอร์</p>
                        </div>
                    </div>
                    <div class="bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100 text-sm font-medium text-slate-600 self-start md:self-auto flex items-center">
                        <span class="mr-2">📅</span> <?php echo date('d M Y'); ?>
                    </div>
                </div>

                <!-- ==========================================
                     CASE 1: ADMIN DASHBOARD (With Room Filter)
                     ========================================== -->
                <?php if($_SESSION['user_role'] == 'ADMIN'): ?>
                    
                    <!-- 🔍 Room Filter Bar -->
                    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-gray-100 shadow-sm border-l-8 border-l-slate-800">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-lg">🔍</div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">ตัวกรองข้อมูลรายห้อง</p>
                                <select onchange="window.location.href='?room='+this.value" class="bg-transparent border-none p-0 pr-8 text-lg font-bold text-slate-800 focus:ring-0 outline-none cursor-pointer">
                                    <option value="">ชั้น ม.6 ทุกห้อง (6.1 - 6.15)</option>
                                    <?php for($i=1; $i<=15; $i++): $r = "6.$i"; ?>
                                        <option value="<?php echo $r; ?>" <?php echo ($data['current_room'] === $r) ? 'selected' : ''; ?>>
                                            ชั้น ม.<?php echo $r; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <?php if($data['current_room']): ?>
                            <a href="<?php echo BASE_URL; ?>/dashboard" class="px-4 py-2 bg-pink-50 text-pink-600 rounded-xl text-xs font-bold hover:bg-pink-100 transition-colors">
                                ❌ ล้างตัวกรอง (แสดงทั้งหมด)
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Admin Statistics Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 border-b-4 border-b-pink-500 transition-transform hover:scale-105">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">กลุ่มโครงงาน (<?php echo htmlspecialchars($data['current_room'] ?: 'ทั้งหมด'); ?>)</p>
                            <p class="text-4xl font-black text-slate-800 mt-2"><?php echo $data['summary']['total_groups']; ?></p>
                        </div>
                        <?php foreach($data['summary']['users'] as $u): ?>
                        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 transition-transform hover:scale-105">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">สมาชิก: <?php echo htmlspecialchars($u['role']); ?></p>
                            <p class="text-4xl font-black text-slate-700 mt-2"><?php echo $u['count']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Admin Graphs -->
                    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="font-bold text-gray-800 text-lg flex items-center">
                                <span class="mr-2 text-pink-500">📊</span> ความก้าวหน้าห้อง <?php echo htmlspecialchars($data['current_room'] ?: 'ม.6 (รวม)'); ?>
                            </h3>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">อนุมัติแล้ว</span>
                        </div>
                        <div class="h-80 w-full">
                            <canvas id="adminProgressChart"></canvas>
                        </div>
                    </div>

                <!-- ==========================================
                     CASE 2: TEACHER DASHBOARD
                     ========================================== -->
                <?php elseif($_SESSION['user_role'] == 'TEACHER'): ?>
                    
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 text-center flex flex-col items-center justify-center transition-transform hover:scale-105">
                            <div class="w-24 h-24 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center text-5xl mb-6 animate-bounce shadow-sm">📝</div>
                            <h3 class="text-6xl font-black text-slate-800 tracking-tighter"><?php echo $data['pending_count']; ?></h3>
                            <p class="text-gray-500 font-bold mt-2 uppercase tracking-tight">งานที่รอคุณตรวจ (รวมทุกห้อง)</p>
                            <a href="<?php echo BASE_URL; ?>/teacher/review" class="mt-8 px-10 py-4 bg-pink-600 text-white rounded-2xl font-bold text-lg hover:bg-pink-700 transition-all shadow-xl shadow-pink-200 ring-4 ring-pink-50">
                                🚀 เริ่มตรวจงานเดี๋ยวนี้
                            </a>
                        </div>
                    </div>

                <!-- ==========================================
                     CASE 3: STUDENT DASHBOARD
                     ========================================== -->
                <?php elseif($_SESSION['user_role'] == 'STUDENT'): ?>
                    
                    <!-- ส่วนของความก้าวหน้า -->
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 border-l-8 border-l-pink-600">
                        <h3 class="font-bold text-slate-800 mb-6 flex items-center text-lg">
                            <span class="mr-2 text-pink-500">🚀</span> ความก้าวหน้าโครงงานของคุณ
                        </h3>
                        <p class="text-xs text-gray-400 mb-2"><?php echo htmlspecialchars($data['project_name']); ?></p> <!-- แสดงชื่อกลุ่มแทน (รวม) -->
                        
                        <div class="relative pt-1">
                            <div class="flex mb-4 items-center justify-between">
                                <span class="text-xs font-black inline-block py-1 px-3 uppercase rounded-full text-pink-600 bg-pink-50 tracking-widest text-[10px]">Overall Progress</span>
                                <span class="text-2xl font-black text-pink-600"><?php echo $data['progress']; ?>%</span>
                            </div>
                            <div class="overflow-hidden h-6 mb-4 text-xs flex rounded-2xl bg-slate-100 p-1">
                                <!-- ปรับความกว้างตามค่า $data['progress'] -->
                                <div style="width:<?php echo $data['progress']; ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-pink-500 to-rose-500 rounded-xl transition-all duration-1000"></div>
                            </div>
                        </div>
                    </div>

                    <!-- ส่วนของประกาศ / สิ่งที่ต้องทำ ใน dashboard/index.php -->
                    <div class="bg-slate-900 mt-8 p-8 rounded-3xl shadow-xl text-white">
                        <h3 class="font-bold mb-6 text-pink-500 uppercase text-xs tracking-widest">ประกาศ / สิ่งที่ต้องทำ</h3>
                        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                            
                            <!-- 1. แสดงงานที่ต้องทำถัดไป (Logic เดิมที่เราทำไว้) -->
                            <div class="p-5 bg-slate-800 rounded-2xl border border-pink-600/30 flex items-start gap-4">
                                <div class="w-10 h-10 bg-pink-600/20 rounded-xl flex items-center justify-center text-pink-500 shrink-0 border border-pink-600/30">📅</div>
                                <div>
                                    <p class="font-bold text-sm">งานถัดไปของคุณ:</p>
                                    <p class="text-xs text-pink-400 mt-1 font-bold"><?php echo htmlspecialchars($data['next_task']); ?></p>
                                </div>
                            </div>

                            <!-- 2. แสดงประกาศจาก Admin (Dynamic) -->
                            <?php foreach($data['announcements'] as $announce): ?>
                                <?php 
                                    $colorClass = $announce['type'] == 'DANGER' ? 'border-red-500/50 bg-red-500/10' : ($announce['type'] == 'WARNING' ? 'border-amber-500/50 bg-amber-500/10' : 'border-slate-700 bg-slate-800');
                                    $icon = $announce['type'] == 'DANGER' ? '🚨' : ($announce['type'] == 'WARNING' ? '🔔' : '📢');
                                ?>
                                <div class="p-5 rounded-2xl border <?php echo $colorClass; ?> flex items-start gap-4 transition-transform hover:scale-[1.02]">
                                    <div class="text-xl shrink-0"><?php echo $icon; ?></div>
                                    <div>
                                        <p class="font-bold text-sm text-white"><?php echo htmlspecialchars($announce['title']); ?></p>
                                        <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">
                                            <?php echo nl2br(htmlspecialchars($announce['content'])); ?>
                                        </p>
                                        <p class="text-[8px] text-slate-500 mt-2 uppercase tracking-tighter">
                                            โพสต์เมื่อ: <?php echo date('d M H:i', strtotime($announce['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if(empty($data['announcements'])): ?>
                                <p class="text-center text-slate-500 text-xs py-10 italic">ไม่มีประกาศใหม่ในขณะนี้</p>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </main>
    </div>
</div>

<!-- Scripts: Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    <?php if($_SESSION['user_role'] == 'ADMIN'): ?>
    const adminCtx = document.getElementById('adminProgressChart').getContext('2d');
    new Chart(adminCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($data['chart_labels']); ?>,
            datasets: [{
                label: 'จำนวนกลุ่มที่ผ่าน',
                data: <?php echo json_encode($data['chart_data']); ?>,
                backgroundColor: '#ec4899',
                borderRadius: 12,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { family: 'Prompt' } } },
                x: { grid: { display: false }, ticks: { font: { family: 'Prompt', size: 10 } } }
            }
        }
    });
    <?php endif; ?>
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>