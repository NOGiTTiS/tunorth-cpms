<?php require_once __DIR__ . '/../templates/header.php'; ?>
<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500">System Settings</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">ตั้งค่าระบบ (System Settings)</h2>
                    <p class="text-gray-500 text-sm">จัดการข้อมูลพื้นฐานของเว็บไซต์และ API เชื่อมต่อต่าง ๆ</p>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-10">
                    <form id="settingsForm" class="space-y-8" enctype="multipart/form-data">
                        
                        <!-- General Settings -->
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                                <span class="bg-pink-100 text-pink-600 rounded-lg p-2 mr-3 text-sm">📂</span> ทั่วไป (General)
                            </h3>
                            <div class="grid grid-cols-1 gap-6 pl-0 md:pl-12">
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Copyright Text</label>
                                    <input type="text" name="site_copyright" value="<?php echo htmlspecialchars($data['settings']['site_copyright'] ?? ''); ?>" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none transition-all placeholder-gray-300" placeholder="© 2025 Your Company">
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Logo / Favicon -->
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                                <span class="bg-blue-100 text-blue-600 rounded-lg p-2 mr-3 text-sm">🖼️</span> รูปภาพ (Images)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pl-0 md:pl-12">
                                <!-- Logo -->
                                <div class="bg-slate-50 p-4 rounded-2xl border border-dashed border-slate-300 hover:border-pink-400 transition-colors">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-3">Site Logo</label>
                                    <div class="flex items-center space-x-4 mb-4">
                                        <div class="w-20 h-20 bg-white rounded-xl flex items-center justify-center p-2 shadow-sm border border-gray-100">
                                            <?php if(!empty($data['settings']['site_logo'])): ?>
                                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($data['settings']['site_logo']); ?>" class="max-w-full max-h-full object-contain">
                                            <?php else: ?>
                                                <span class="text-xs text-gray-300">No Logo</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[10px] text-gray-400 mb-1">Recommended: PNG/SVG transparent</p>
                                        </div>
                                    </div>
                                    <input type="file" name="site_logo" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-white hover:file:bg-pink-600 transition-all cursor-pointer">
                                </div>

                                <!-- Favicon -->
                                <div class="bg-slate-50 p-4 rounded-2xl border border-dashed border-slate-300 hover:border-pink-400 transition-colors">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-3">Favicon</label>
                                    <div class="flex items-center space-x-4 mb-4">
                                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center p-1 shadow-sm border border-gray-100">
                                            <?php if(!empty($data['settings']['site_favicon'])): ?>
                                                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($data['settings']['site_favicon']); ?>" class="max-w-full max-h-full object-contain">
                                            <?php else: ?>
                                                <span class="text-xs text-gray-300">No Icon</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[10px] text-gray-400 mb-1">Recommended: .ico or 32x32 png</p>
                                        </div>
                                    </div>
                                    <input type="file" name="site_favicon" accept="image/x-icon,image/png" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-white hover:file:bg-pink-600 transition-all cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Telegram API -->
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
                                <span class="bg-sky-100 text-sky-600 rounded-lg p-2 mr-3 text-sm">🤖</span> Telegram Notification
                            </h3>
                            <div class="grid grid-cols-1 gap-6 pl-0 md:pl-12">
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Telegram Bot API Token</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">🔑</span>
                                        <input type="text" name="telegram_api_token" value="<?php echo htmlspecialchars($data['settings']['telegram_api_token'] ?? ''); ?>" class="w-full pl-10 p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none font-mono text-sm bg-slate-50 focus:bg-white transition-all" placeholder="123456789:ABCdefGhIJKlmNoPQRstUvwxyz">
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Telegram Chat ID (Admin Group)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">ID</span>
                                        <input type="text" name="telegram_chat_id" value="<?php echo htmlspecialchars($data['settings']['telegram_chat_id'] ?? ''); ?>" class="w-full pl-10 p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none font-mono text-sm bg-slate-50 focus:bg-white transition-all" placeholder="-1001234567890">
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 bg-slate-100 inline-block px-2 py-1 rounded border border-slate-200 flex items-center w-fit">
                                        <span class="mr-1">💡</span> ใช้สำหรับรับการแจ้งเตือนเมื่อนักเรียนส่งงานใหม่
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t flex justify-end">
                            <button type="submit" class="w-full md:w-auto bg-pink-600 text-white px-8 py-3 rounded-2xl font-bold shadow-lg shadow-pink-100 hover:bg-pink-700 transition active:scale-95 flex items-center justify-center">
                                <span class="mr-2">💾</span> บันทึกการตั้งค่า
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Use FormData for file uploads
    const formData = new FormData(e.target);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res = await fetch('<?php echo BASE_URL; ?>/admin/settings_update', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if(result.status === 'success') {
            Swal.fire({
                icon: 'success', 
                title: 'บันทึกสำเร็จ!', 
                text: result.message,
                showConfirmButton: false, 
                timer: 1500 
            }).then(() => location.reload());
        } else {
            Swal.fire('ผิดพลาด', result.message || 'เกิดข้อผิดพลาดในการบันทึก', 'error');
        }
    } catch(err) {
        Swal.fire('Error', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
    }
});
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>
