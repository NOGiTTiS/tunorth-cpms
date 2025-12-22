<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <!-- 1. Sidebar -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <!-- 2. Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Project Details</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                
                <!-- Page Title -->
                <div class="mb-8 flex items-center gap-4">
                    <button onclick="history.back()" class="w-10 h-10 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">รายละเอียดโครงงาน</h2>
                        <p class="text-gray-500 text-sm mt-1">ข้อมูลทั่วไปและสมาชิกในทีม</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- ซ้าย: ข้อมูลโครงงาน -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white p-8 rounded-3xl shadow-sm border border-l-8 border-l-pink-600">
                            <div class="flex justify-between items-start mb-6">
                                <span class="bg-pink-50 text-pink-600 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">Project Information</span>
                                <div class="flex flex-col items-end">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Status: 
                                        <span class="text-pink-600"><?php echo htmlspecialchars($data['group']['status'] ?? 'N/A'); ?></span>
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1 text-right italic">
                                        Room: ม.<?php echo htmlspecialchars($data['group']['room'] ?? '-'); ?>
                                    </span>
                                </div>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 leading-tight"><?php echo htmlspecialchars($data['group']['project_name_th']); ?></h3>
                            <p class="text-gray-400 italic mt-2 text-lg font-light"><?php echo htmlspecialchars($data['group']['project_name_en']); ?></p>
                            
                            <div class="mt-8 pt-8 border-t border-slate-50 flex items-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-xl mr-4 shrink-0 shadow-sm border border-white">🎓</div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">ครูที่ปรึกษา</p>
                                    <p class="font-bold text-slate-700"><?php echo htmlspecialchars($data['group']['advisor_name'] ?: 'ยังไม่ได้ระบุ'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ขวา: รายชื่อสมาชิก -->
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 flex flex-col h-full">
                            <div class="flex justify-between items-center mb-8">
                                <h3 class="font-bold text-gray-800 flex items-center">
                                    <span class="mr-2 text-pink-500">👥</span> สมาชิกในกลุ่ม
                                </h3>
                                <span class="bg-slate-900 text-white text-[10px] px-3 py-1 rounded-full font-black"><?php echo count($data['members']); ?> คน</span>
                            </div>

                            <ul class="space-y-4 flex-1">
                                <?php foreach($data['members'] as $m): ?>
                                <li class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-transparent hover:border-pink-100 group transition-all">
                                    <div class="flex items-center min-w-0">
                                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center font-bold text-pink-600 text-xs mr-3 shrink-0 uppercase">
                                            <?php echo htmlspecialchars(mb_substr($m['full_name'], 0, 1, 'UTF-8')); ?>
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-bold text-slate-800 truncate"><?php echo htmlspecialchars($m['full_name']); ?></p>
                                            <p class="text-[10px] text-gray-400 font-medium">ม.<?php echo htmlspecialchars($m['room']); ?> | <?php echo htmlspecialchars($m['student_id']); ?></p>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
