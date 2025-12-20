<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <!-- 1. Sidebar -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <!-- 2. Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Group Management</span>
            <button onclick="toggleSidebar()" class="p-2 bg-slate-800 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                
                <!-- Page Title -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">จัดการกลุ่มโครงงาน</h2>
                    <p class="text-gray-500 text-sm mt-1">ตั้งค่าข้อมูลโครงงานและจัดการสมาชิกในทีม</p>
                </div>

                <?php if (!$data['group']): ?>
                    <!-- ✅ CASE 1: ยังไม่มีกลุ่ม (ฟอร์มสร้างกลุ่มใหม่) -->
                    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-slate-900 p-6 text-white text-center">
                            <h3 class="text-xl font-bold italic text-pink-500 tracking-tighter uppercase">Get Started</h3>
                            <p class="text-slate-400 text-xs mt-1">กรุณาระบุข้อมูลโครงงานเพื่อเริ่มต้นระบบ</p>
                        </div>
                        <form id="createGroupForm" class="p-6 md:p-10 space-y-6">
                            <div class="space-y-5">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ชื่อโครงงาน (ภาษาไทย)</label>
                                    <input type="text" name="project_name_th" required class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-pink-500 outline-none transition-all mt-1" placeholder="ระบุชื่อภาษาไทย">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Project Name (English)</label>
                                    <input type="text" name="project_name_en" required class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-pink-500 outline-none transition-all mt-1" placeholder="Project Name in English">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ชื่อครูที่ปรึกษา (พิมพ์เอง)</label>
                                        <input type="text" name="advisor_name" required class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-pink-500 outline-none transition-all mt-1" placeholder="ชื่อ-นามสกุล ครูที่ปรึกษา">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-pink-600">ชั้น/ห้อง (ม.6.x)</label>
                                        <select name="room" required class="w-full px-4 py-3 border border-gray-200 rounded-2xl bg-white mt-1 outline-none focus:ring-2 focus:ring-pink-500 transition-all">
                                            <option value="" disabled selected>-- เลือกห้อง --</option>
                                            <?php for($i=1; $i<=15; $i++): ?>
                                                <option value="6.<?php echo $i; ?>">ม.6.<?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-pink-600 text-white py-4 rounded-2xl font-bold text-lg hover:bg-pink-700 transition shadow-lg shadow-pink-100 active:scale-95">สร้างกลุ่มโครงงาน</button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- ✅ CASE 2: มีกลุ่มแล้ว (แสดงข้อมูลและจัดการสมาชิก) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <!-- ซ้าย: ข้อมูลโครงงาน -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-8 rounded-3xl shadow-sm border border-l-8 border-l-pink-600">
                                <div class="flex justify-between items-start mb-6">
                                    <span class="bg-pink-50 text-pink-600 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">Project Information</span>
                                    <div class="flex flex-col items-end">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Status: <span class="text-pink-600"><?php echo htmlspecialchars($data['group']['status']); ?></span></span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1 text-right italic">Room: ม.<?php echo htmlspecialchars($data['group']['room'] ?? ''); ?></span>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 leading-tight"><?php echo htmlspecialchars($data['group']['project_name_th']); ?></h3>
                                <p class="text-gray-400 italic mt-2 text-lg font-light"><?php echo htmlspecialchars($data['group']['project_name_en']); ?></p>
                                
                                <div class="mt-8 pt-8 border-t border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-xl mr-4 shrink-0 shadow-sm border border-white">🎓</div>
                                        <div>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">ครูที่ปรึกษา</p>
                                            <p class="font-bold text-slate-700"><?php echo htmlspecialchars($data['group']['advisor_name'] ?: 'ยังไม่ได้ระบุ'); ?></p>
                                        </div>
                                    </div>
                                    <button onclick='openEditProjectModal(<?php echo htmlspecialchars(json_encode($data['group']), ENT_QUOTES, 'UTF-8'); ?>)' class="px-5 py-3 bg-slate-100 text-slate-600 rounded-2xl text-xs font-bold hover:bg-slate-900 hover:text-white transition-all">✏️ แก้ไขข้อมูลโครงงาน</button>
                                    
                                </div>
                                <div class="mt-10 pt-10 border-t border-gray-200">
                                    <div class="bg-red-50 p-6 rounded-3xl border border-red-100 flex flex-col md:flex-row items-center justify-between gap-4">
                                        <div>
                                            <h4 class="text-red-600 font-bold text-sm uppercase tracking-widest">Danger Zone</h4>
                                            <p class="text-xs text-red-400 mt-1 italic">หากยุบกลุ่ม ข้อมูลสมาชิกและเอกสารที่ส่งมาทั้งหมดจะถูกลบถาวร</p>
                                        </div>
                                        <button onclick="dissolveGroup()" class="px-6 py-3 bg-red-600 text-white rounded-2xl font-bold text-sm hover:bg-red-700 transition shadow-lg shadow-red-100 active:scale-95">
                                            💥 ยุบกลุ่มโครงงานนี้
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ขวา: จัดการสมาชิก (ไม่จำกัดจำนวน) -->
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
                                        <!-- ลบสมาชิก (เฉพาะเมื่อไม่ใช่ตัวเอง) -->
                                        <?php if($m['user_id'] != $_SESSION['user_id']): ?>
                                            <button onclick="removeMember(<?php echo $m['user_id']; ?>, '<?php echo htmlspecialchars($m['full_name']); ?>')" class="p-2 bg-slate-100 text-slate-400 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors" title="ลบสมาชิก">🗑️</button>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>

                                <button onclick="openInviteModal()" class="w-full mt-8 py-4 border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-sm font-bold hover:border-pink-300 hover:text-pink-600 hover:bg-pink-50 transition-all flex items-center justify-center active:scale-95 group">
                                    <span class="mr-2 text-lg group-hover:scale-125 transition-transform">+</span> เชิญเพื่อนร่วมกลุ่ม
                                </button>
                            </div>
                        </div>

                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</div>

<script>
// --- ส่วนการสร้างกลุ่ม ---
document.getElementById('createGroupForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    const res = await fetch(BASE_URL + '/project/create', { method: 'POST', body: formData });
    const result = await res.json();
    if(result.status === 'success') {
        Swal.fire('สำเร็จ', 'สร้างกลุ่มโครงงานเรียบร้อยแล้ว', 'success').then(() => location.reload());
    }
});

// --- ส่วนการแก้ไขข้อมูลโครงงาน (New Modal) ---
async function openEditProjectModal(projectData) {
    const { value: formValues } = await Swal.fire({
        title: 'แก้ไขข้อมูลโครงงาน',
        html:
            `<div class="text-left font-prompt space-y-4 px-2">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ชื่อโครงงาน (ภาษาไทย)</label>
                    <input id="edit-name-th" class="w-full p-4 border border-gray-200 rounded-2xl mt-1 outline-none focus:ring-2 focus:ring-pink-500 transition-all" value="${projectData.project_name_th}">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Project Name (English)</label>
                    <input id="edit-name-en" class="w-full p-4 border border-gray-200 rounded-2xl mt-1 outline-none focus:ring-2 focus:ring-pink-500 transition-all" value="${projectData.project_name_en}">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">ชื่อครูที่ปรึกษา</label>
                        <input id="edit-advisor" class="w-full p-4 border border-gray-200 rounded-2xl mt-1 outline-none focus:ring-2 focus:ring-pink-500 transition-all" value="${projectData.advisor_name || ''}">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest text-pink-600">ชั้น/ห้อง (ม.6.x)</label>
                        <select id="edit-room" class="w-full p-4 border border-gray-200 rounded-2xl bg-white mt-1 outline-none focus:ring-2 focus:ring-pink-500 transition-all">
                            ${Array.from({length: 15}, (_, i) => `6.${i+1}`).map(r => 
                                `<option value="${r}" ${projectData.room === r ? 'selected' : ''}>ม.${r}</option>`
                            ).join('')}
                        </select>
                    </div>
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'บันทึกการแก้ไข',
        confirmButtonColor: '#ec4899',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-3xl p-6 md:p-10', confirmButton: 'rounded-xl px-8 py-3', cancelButton: 'rounded-xl px-8 py-3' },
        preConfirm: () => {
            return {
                project_name_th: document.getElementById('edit-name-th').value,
                project_name_en: document.getElementById('edit-name-en').value,
                advisor_name: document.getElementById('edit-advisor').value,
                room: document.getElementById('edit-room').value
            }
        }
    });

    if (formValues) {
        Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const fd = new FormData();
        fd.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        Object.keys(formValues).forEach(key => fd.append(key, formValues[key]));
        const res = await fetch(BASE_URL + '/project/update', { method: 'POST', body: fd });
        const result = await res.json();
        if(result.status === 'success') {
            Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: result.message, showConfirmButton: false, timer: 1500 }).then(() => location.reload());
        }
    }
}

// --- ส่วนการเชิญเพื่อนแบบเลือกห้อง (Dropdown) ---
// --- ส่วนการเชิญเพื่อนแบบ Multiple Select ---
async function openInviteModal() {
    // 1. เลือกห้อง
    const { value: selectedRoom } = await Swal.fire({
        title: 'เลือกห้องของเพื่อน',
        input: 'select',
        inputOptions: {
            '6.1': 'ม.6.1', '6.2': 'ม.6.2', '6.3': 'ม.6.3', '6.4': 'ม.6.4', '6.5': 'ม.6.5',
            '6.6': 'ม.6.6', '6.7': 'ม.6.7', '6.8': 'ม.6.8', '6.9': 'ม.6.9', '6.10': 'ม.6.10',
            '6.11': 'ม.6.11', '6.12': 'ม.6.12', '6.13': 'ม.6.13', '6.14': 'ม.6.14', '6.15': 'ม.6.15'
        },
        inputPlaceholder: '-- เลือกห้อง --',
        showCancelButton: true,
        confirmButtonText: 'ถัดไป',
        confirmButtonColor: '#ec4899',
        customClass: { popup: 'rounded-3xl' }
    });

    if (selectedRoom) {
        Swal.showLoading();
        const res = await fetch(`${BASE_URL}/project/get_available_by_room?room=${selectedRoom}`);
        const students = await res.json();

        if (students.length === 0) {
            return Swal.fire('ไม่พบนักเรียน', `นักเรียนในห้อง ${selectedRoom} มีกลุ่มกันหมดแล้ว`, 'info');
        }

        // 2. สร้าง HTML Checkbox List
        const checkboxHtml = students.map(s => `
            <label class="flex items-center p-3 hover:bg-pink-50 rounded-xl cursor-pointer transition-colors border border-transparent hover:border-pink-100 mb-2 text-left">
                <input type="checkbox" name="selected_students" value="${s.id}" class="w-5 h-5 text-pink-600 border-gray-300 rounded focus:ring-pink-500 mr-3">
                <div>
                    <span class="font-bold text-gray-800 text-sm block">${s.full_name}</span>
                    <span class="text-xs text-slate-400 font-mono">${s.student_id}</span>
                </div>
            </label>
        `).join('');

        const { value: isConfirmed } = await Swal.fire({
            title: `เลือกเพื่อน (ห้อง ${selectedRoom})`,
            html: `
                <div class="text-xs text-gray-500 mb-4 text-left">เลือกได้หลายคน</div>
                <div class="max-h-[300px] overflow-y-auto custom-scrollbar p-1 border border-gray-100 rounded-2xl bg-white">
                    ${checkboxHtml}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'เชิญเข้ากลุ่ม',
            confirmButtonColor: '#ec4899',
            customClass: { popup: 'rounded-3xl p-6' },
            preConfirm: () => {
                const checked = document.querySelectorAll('input[name="selected_students"]:checked');
                if (checked.length === 0) {
                    Swal.showValidationMessage('กรุณาเลือกเพื่อนอย่างน้อย 1 คน');
                    return false;
                }
                return Array.from(checked).map(c => c.value);
            }
        });

        // 3. Loop เพิ่มทีละคน
        if (isConfirmed) {
            Swal.fire({ title: 'กำลังเชิญสมาชิก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            
            let successCount = 0;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            for (const uid of isConfirmed) {
                const fd = new FormData();
                fd.append('csrf_token', csrfToken);
                fd.append('user_id', uid);
                
                try {
                    const resAdd = await fetch(BASE_URL + '/project/add_member', { method: 'POST', body: fd });
                    const result = await resAdd.json();
                    if(result.status === 'success') successCount++;
                } catch(e) { console.error(e); }
            }
            
            Swal.fire({
                icon: 'success', 
                title: 'เสร็จสิ้น!', 
                text: `เชิญสมาชิกสำเร็จ ${successCount} จาก ${isConfirmed.length} คน`,
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        }
    }
}

// --- ส่วนการลบสมาชิก ---
function removeMember(id, name) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `ต้องการลบ ${name} ออกจากกลุ่มใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'ใช่, ลบออก',
    }).then(async (result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            fd.append('user_id', id);
            const res = await fetch(BASE_URL + '/project/remove_member', { method: 'POST', body: fd });
            const result = await res.json();
            if(result.status === 'success') location.reload();
        }
    });
}

async function dissolveGroup() {
    // การยืนยันชั้นที่ 1
    const { isConfirmed } = await Swal.fire({
        title: 'ยืนยันการยุบกลุ่ม?',
        text: "ข้อมูลสมาชิกและการส่งงานทั้งหมดจะถูกลบถาวร ไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'ใช่, ฉันต้องการยุบกลุ่ม',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-3xl' }
    });

    if (isConfirmed) {
        // การยืนยันชั้นที่ 2 (Hard Confirmation)
        const { value: confirmText } = await Swal.fire({
            title: 'กรุณาพิมพ์ชื่อโครงงาน',
            text: 'เพื่อยืนยันการลบ กรุณาพิมพ์ชื่อกลุ่มของคุณให้ถูกต้อง',
            input: 'text',
            inputPlaceholder: 'ชื่อโครงงานของคุณ...',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันยุบกลุ่มถาวร',
            confirmButtonColor: '#dc2626',
            customClass: { popup: 'rounded-3xl' }
        });

        // ตรวจสอบว่าพิมพ์ชื่อตรงไหม (Optional: หรือแค่กดตกลงเฉยๆ ก็ได้)
        if (confirmText) {
            Swal.fire({ title: 'กำลังยุบกลุ่ม...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            try {
                const res = await fetch(BASE_URL + '/project/delete', { method: 'POST', body: formData });
                const result = await res.json();
                
                if (result.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'ยุบกลุ่มแล้ว', text: 'คุณสามารถไปสร้างกลุ่มใหม่หรือเข้ากลุ่มเพื่อนได้ทันที', confirmButtonColor: '#ec4899' })
                        .then(() => window.location.href = BASE_URL + '/project/mygroup');
                } else {
                    Swal.fire('ผิดพลาด', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
            }
        }
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>