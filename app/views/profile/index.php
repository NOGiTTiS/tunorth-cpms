<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Header Mobile -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">My Profile</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-3xl mx-auto">
                
                <h2 class="text-3xl font-bold text-gray-800 mb-8">ข้อมูลส่วนตัว (My Profile)</h2>

                <!-- User Info Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
                    <div class="bg-slate-900 p-8 text-center">
                        <div class="w-24 h-24 bg-white rounded-full mx-auto flex items-center justify-center text-4xl mb-4 shadow-lg border-4 border-slate-800">
                             <!-- Initials -->
                             <span class="font-bold text-pink-600"><?php echo mb_substr($data['user']['full_name'], 0, 1); ?></span>
                        </div>
                        <h3 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($data['user']['full_name']); ?></h3>
                        <span class="inline-block bg-pink-600 text-white text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-widest mt-2">
                            <?php echo $data['user']['role']; ?>
                        </span>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email</label>
                                <p class="text-slate-800 font-medium text-lg border-b border-gray-100 pb-2"><?php echo htmlspecialchars($data['user']['email']); ?></p>
                            </div>
                            <?php if($data['user']['role'] == 'STUDENT'): ?>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Student ID</label>
                                <p class="text-slate-800 font-medium text-lg border-b border-gray-100 pb-2"><?php echo htmlspecialchars($data['user']['student_id']); ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Room</label>
                                <p class="text-slate-800 font-medium text-lg border-b border-gray-100 pb-2">ม.<?php echo htmlspecialchars($data['user']['room']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Change Password Form -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <span class="mr-3 bg-pink-100 text-pink-600 w-8 h-8 rounded-lg flex items-center justify-center text-sm">🔑</span>
                        เปลี่ยนรหัสผ่าน
                    </h3>
                    
                    <form id="changePassForm" class="space-y-5">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">รหัสผ่านเดิม</label>
                            <input type="password" name="old_password" required class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-pink-500 outline-none transition-all mt-1">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-pink-600">รหัสผ่านใหม่</label>
                                <input type="password" name="new_password" required class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-pink-500 outline-none transition-all mt-1">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-pink-600">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" required class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-pink-500 outline-none transition-all mt-1">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold hover:bg-slate-800 transition shadow-lg shadow-slate-200">บันทึกรหัสผ่านใหม่</button>
                    </form>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
document.getElementById('changePassForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Check match
    const newP = e.target.new_password.value;
    const confP = e.target.confirm_password.value;
    
    if(newP !== confP) {
        Swal.fire('Error', 'รหัสผ่านใหม่ไม่ตรงกัน', 'error');
        return;
    }

    const formData = new FormData(e.target);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res = await fetch(BASE_URL + '/profile/update_password', { method: 'POST', body: formData });
        const result = await res.json();
        
        if(result.status === 'success') {
            Swal.fire('สำเร็จ', result.message, 'success').then(() => e.target.reset());
        } else {
            Swal.fire('ผิดพลาด', result.message, 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
    }
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
