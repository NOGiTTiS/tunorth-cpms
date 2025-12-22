<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <!-- ยังคงมี Sidebar เพื่อให้เปลี่ยนเมนูได้ -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0">
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500">Submission</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 flex items-center justify-center p-6">
            <div class="max-w-md w-full text-center bg-white p-10 rounded-3xl shadow-sm border border-gray-100">
                <div class="w-24 h-24 bg-pink-50 text-pink-500 rounded-full flex items-center justify-center text-5xl mx-auto mb-6">
                    📁
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">ยังไม่มีกลุ่มโครงงาน</h2>
                <p class="text-gray-500 mb-8 leading-relaxed">
                    คุณจำเป็นต้องสร้างกลุ่มโครงงาน หรือเข้าร่วมกลุ่มกับเพื่อนก่อน จึงจะสามารถเข้าถึงระบบส่งเอกสารได้
                </p>
                
                <div class="space-y-3">
                    <a href="<?php echo BASE_URL; ?>/project/mygroup" class="block w-full py-4 bg-pink-600 text-white rounded-2xl font-bold hover:bg-pink-700 transition shadow-lg shadow-pink-100">
                        ไปที่หน้าจัดการกลุ่ม
                    </a>
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="block w-full py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold hover:bg-slate-200 transition">
                        กลับหน้าหลัก
                    </a>
                </div>

                <p class="mt-8 text-[10px] text-gray-300 uppercase tracking-widest font-black">
                    TU-North CPMS System
                </p>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>