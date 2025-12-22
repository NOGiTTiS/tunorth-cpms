<?php require_once __DIR__ . '/../templates/header.php'; ?>
<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500">Admin Dashboard</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">ภาพรวมระบบ (Summary)</h2>
                    <p class="text-gray-500 text-sm">สถิติการดำเนินงานโครงงานคอมพิวเตอร์ ปีการศึกษา 2568</p>
                </div>

                <!-- 4 Top Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">กลุ่มโครงงาน</p>
                        <p class="text-3xl font-bold text-pink-600 mt-2"><?php echo $data['summary']['total_groups']; ?></p>
                        <p class="text-[10px] text-gray-400 mt-1 italic">กลุ่มที่จดทะเบียนแล้ว</p>
                    </div>
                    <?php foreach($data['summary']['users'] as $u): ?>
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest"><?php echo $u['role']; ?></p>
                        <p class="text-3xl font-bold text-slate-800 mt-2"><?php echo $u['count']; ?></p>
                        <p class="text-[10px] text-gray-400 mt-1 italic">ผู้ใช้งานในระบบ</p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- กราฟความก้าวหน้า (Chart.js) -->
                    <div class="lg:col-span-2 bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-6 flex items-center">
                            <span class="mr-2">📊</span> จำนวนกลุ่มที่ "ผ่านการอนุมัติ" ในแต่ละขั้นตอน
                        </h3>
                        <div class="h-80 w-full">
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>

                    <!-- รายชื่อครูที่ปรึกษาและภาระงาน -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-6">ภาระงานครูที่ปรึกษา</h3>
                        <div class="space-y-4">
                            <?php foreach($data['summary']['advisor_load'] as $load): ?>
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-pink-500 text-white rounded-full flex items-center justify-center text-[10px] font-bold mr-3 uppercase">
                                        <?php echo mb_substr($load['full_name'], 0, 1, 'UTF-8'); ?>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"><?php echo $load['full_name']; ?></span>
                                </div>
                                <span class="bg-white px-3 py-1 rounded-lg text-xs font-bold shadow-sm border border-gray-100">
                                    <?php echo $load['group_count']; ?> กลุ่ม
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-6 text-center italic">* ข้อมูลนี้ช่วยในการกระจายภาระงานครูอย่างเหมาะสม</p>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('progressChart').getContext('2d');
    
    // ไล่เฉดสีให้กราฟดู Modern
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(236, 72, 153, 0.8)'); // Pink-500
    gradient.addColorStop(1, 'rgba(236, 72, 153, 0.1)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($data['chart_labels']); ?>,
            datasets: [{
                label: 'จำนวนกลุ่มที่ผ่านการอนุมัติ',
                data: <?php echo json_encode($data['chart_data']); ?>,
                backgroundColor: gradient,
                borderColor: '#ec4899',
                borderWidth: 2,
                borderRadius: 15,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: { font: { family: 'Prompt' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Prompt', size: 10 } }
                }
            }
        }
    });
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>