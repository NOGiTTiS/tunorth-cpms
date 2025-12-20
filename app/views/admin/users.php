<?php require_once __DIR__ . '/../templates/header.php'; ?>
<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500 font-prompt">User Management</span>
            <button onclick="toggleSidebar()" class="p-2 bg-slate-800 rounded-lg">☰</button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 font-prompt">จัดการผู้ใช้งาน</h2>
                        <p class="text-gray-500 text-sm">ดูแลสิทธิ์การเข้าถึงของครูและนักเรียน</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="downloadCSVTemplate()" class="w-full md:w-auto bg-slate-100 text-slate-600 px-4 py-3 rounded-2xl font-bold text-sm hover:bg-slate-200 transition">📥 โหลดตัวอย่าง CSV</button>
                        <button onclick="importCSV()" class="w-full md:w-auto bg-slate-800 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-700 transition shadow-lg">🚀 Import CSV</button>
                        <button onclick="openUserModal()" class="w-full md:w-auto bg-pink-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-pink-700 transition shadow-lg shadow-pink-100">+ เพิ่มรายคน</button>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">ข้อมูลผู้ใช้</th>
                                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">บทบาท</th>
                                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">อีเมล</th>
                                    <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach($data['users'] as $user): ?>
                                <tr class="hover:bg-pink-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                        <div class="text-[10px] text-gray-400 font-mono"><?php echo htmlspecialchars($user['student_id'] ?: 'STAFF ID'); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php 
                                            $roleColor = $user['role'] == 'ADMIN' ? 'bg-purple-100 text-purple-600' : 
                                                        ($user['role'] == 'TEACHER' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600');
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black <?php echo $roleColor; ?>">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center space-x-2">
                                            <button onclick="openUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-pink-600 hover:text-white transition-all">✏️</button>
                                            <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES); ?>')" class="p-2 bg-slate-100 text-red-500 rounded-xl hover:bg-red-600 hover:text-white transition-all">🗑️</button>
                                            <button onclick="resetPassword(<?php echo $user['id']; ?>, '<?php echo $user['full_name']; ?>')"  class="p-2 bg-slate-100 text-amber-500 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm" title="รีเซ็ตรหัสผ่าน">🔄</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// ฟังก์ชันเดียวทำได้ทั้ง Add และ Edit
async function openUserModal(userData = null) {
    const isEdit = userData !== null;
    
    // เตรียมตัวเลือกห้อง 6.1 - 6.15
    let roomOptions = '<option value="">-- ไม่ระบุ (สำหรับครู/Admin) --</option>';
    for(let i=1; i<=15; i++) {
        let r = `6.${i}`;
        let selected = (isEdit && userData.room === r) ? 'selected' : '';
        roomOptions += `<option value="${r}" ${selected}>ม.${r}</option>`;
    }

    const { value: formValues } = await Swal.fire({
        title: isEdit ? 'แก้ไขข้อมูลผู้ใช้' : 'เพิ่มผู้ใช้งานใหม่',
        html:
            `<div class="text-left space-y-4 px-2 font-prompt">
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">ชื่อ-นามสกุล</label>
                    <input id="swal-name" class="w-full p-3 border border-gray-200 rounded-xl mt-1 focus:ring-2 focus:ring-pink-500 outline-none transition-all" value="${isEdit ? userData.full_name : ''}" placeholder="ชื่อ-นามสกุล">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">อีเมล</label>
                    <input id="swal-email" type="email" class="w-full p-3 border border-gray-200 rounded-xl mt-1 focus:ring-2 focus:ring-pink-500 outline-none transition-all" value="${isEdit ? userData.email : ''}" placeholder="example@cpms.com">
                </div>
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">รหัสผ่าน ${isEdit ? '(เว้นว่างไว้หากไม่ต้องการเปลี่ยน)' : ''}</label>
                    <input id="swal-pass" type="password" class="w-full p-3 border border-gray-200 rounded-xl mt-1 focus:ring-2 focus:ring-pink-500 outline-none transition-all" placeholder="${isEdit ? '••••••••' : 'ระบุรหัสผ่าน'}">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">บทบาท (Role)</label>
                        <select id="swal-role" class="w-full p-3 border border-gray-200 rounded-xl mt-1 bg-white outline-none focus:ring-2 focus:ring-pink-500 transition-all">
                            <option value="STUDENT" ${isEdit && userData.role === 'STUDENT' ? 'selected' : ''}>STUDENT</option>
                            <option value="TEACHER" ${isEdit && userData.role === 'TEACHER' ? 'selected' : ''}>TEACHER</option>
                            <option value="ADMIN" ${isEdit && userData.role === 'ADMIN' ? 'selected' : ''}>ADMIN</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">ชั้น/ห้อง (เฉพาะนักเรียน)</label>
                        <select id="swal-room" class="w-full p-3 border border-gray-200 rounded-xl mt-1 bg-white outline-none focus:ring-2 focus:ring-pink-500 transition-all">
                            ${roomOptions}
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">รหัสนักเรียน (ถ้ามี)</label>
                    <input id="swal-sid" class="w-full p-3 border border-gray-200 rounded-xl mt-1 outline-none focus:ring-2 focus:ring-pink-500 transition-all" value="${isEdit ? (userData.student_id || '') : ''}" placeholder="เช่น 12345">
                </div>
            </div>`,
        showCancelButton: true,
        confirmButtonText: isEdit ? 'อัปเดตข้อมูล' : 'สร้างผู้ใช้ใหม่',
        confirmButtonColor: '#ec4899',
        cancelButtonText: 'Cancel',
        focusConfirm: false,
        customClass: {
            popup: 'rounded-3xl',
            confirmButton: 'rounded-xl px-8 py-3 font-bold',
            cancelButton: 'rounded-xl px-8 py-3 font-bold'
        },
        preConfirm: () => {
            const data = {
                full_name: document.getElementById('swal-name').value,
                email: document.getElementById('swal-email').value,
                password: document.getElementById('swal-pass').value,
                role: document.getElementById('swal-role').value,
                room: document.getElementById('swal-room').value, // ดึงค่าห้องเพิ่มเข้ามา
                student_id: document.getElementById('swal-sid').value
            };
            if(isEdit) data.id = userData.id;
            
            if(!data.full_name || !data.email || (!isEdit && !data.password)) {
                Swal.showValidationMessage('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                return false;
            }
            return data;
        }
    });

    if (formValues) {
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        Object.keys(formValues).forEach(key => formData.append(key, formValues[key]));
        
        const endpoint = isEdit ? '/admin/user_update' : '/admin/user_store';
        const baseUrl = window.BASE_URL || '';

        Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const res = await fetch(`${baseUrl}${endpoint}`, { method: 'POST', body: formData });
            const result = await res.json();
            
            if(result.status === 'success') {
                Swal.fire({ icon: 'success', title: result.message, showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
            } else {
                Swal.fire('ผิดพลาด', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
        }
    }
}

function deleteUser(id, name) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `คุณกำลังจะลบ ${name} และข้อมูลกลุ่มที่เกี่ยวข้องจะหายไป!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'ใช่, ลบทิ้งเลย',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-3xl' }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const baseUrl = window.BASE_URL || '';
            const res = await fetch(`${baseUrl}/admin/user_delete/` + id);
            const data = await res.json();
            if(data.status === 'success') {
                Swal.fire('ลบแล้ว!', 'ข้อมูลถูกลบออกจากระบบ', 'success').then(() => location.reload());
            } else {
                Swal.fire('ผิดพลาด', data.message, 'error');
            }
        }
    });
}
</script>

<script>
// ฟังก์ชันสำหรับดาวน์โหลด Template (เพื่อให้ Admin กรอกข้อมูลถูก Format)
function downloadCSVTemplate() {
    const csvContent = "Full Name,Email,Password,Role,Student ID,Room\nสมชาย ใจดี,somchai@cpms.com,123456,STUDENT,10001,6.1\nครูสมศรี มีสุข,somsri@cpms.com,123456,TEACHER,,";
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", "template_users.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ฟังก์ชันนำเข้าไฟล์ CSV
async function importCSV() {
    const { value: file } = await Swal.fire({
        title: 'นำเข้าผู้ใช้งานผ่าน CSV',
        input: 'file',
        inputAttributes: { 'accept': '.csv', 'aria-label': 'Upload your CSV file' },
        showCancelButton: true,
        confirmButtonText: 'เริ่มนำเข้าข้อมูล',
        confirmButtonColor: '#1e293b',
        customClass: { popup: 'rounded-3xl' }
    });

    if (file) {
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('csv_file', file);

        Swal.fire({
            title: 'กำลังประมวลผล...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const baseUrl = window.BASE_URL || '';
            const res = await fetch(`${baseUrl}/admin/user_import`, { method: 'POST', body: formData });
            const result = await res.json();
            
            if(result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'นำเข้าเรียบร้อย!',
                    text: result.message,
                    confirmButtonColor: '#ec4899'
                }).then(() => location.reload());
            }
        } catch (error) {
            Swal.fire('Error', 'เกิดข้อผิดพลาดในการอ่านไฟล์', 'error');
        }
    }
}

async function resetPassword(userId, userName) {
    const { value: newPassword } = await Swal.fire({
        title: 'รีเซ็ตรหัสผ่าน',
        html: `
            <div class="text-left font-prompt">
                <p class="text-sm text-gray-500 mb-4">เปลี่ยนรหัสผ่านให้กับ: <b class="text-slate-800">${userName}</b></p>
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">รหัสผ่านใหม่</label>
                <input id="swal-new-pass" type="password" class="w-full p-4 border border-gray-200 rounded-2xl mt-1 outline-none focus:ring-2 focus:ring-pink-500 transition-all" placeholder="ระบุรหัสผ่านใหม่ 6 ตัวขึ้นไป">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'ยืนยันการเปลี่ยน',
        confirmButtonColor: '#f59e0b', // สี Amber
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-3xl p-6 md:p-10' },
        preConfirm: () => {
            const pass = document.getElementById('swal-new-pass').value;
            if (!pass || pass.length < 6) {
                Swal.showValidationMessage('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                return false;
            }
            return pass;
        }
    });

    if (newPassword) {
        Swal.fire({ title: 'กำลังดำเนินการ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const formData = new FormData();
        formData.append('id', userId);
        formData.append('new_password', newPassword);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        try {
            const baseUrl = window.BASE_URL || '';
            const res = await fetch(`${baseUrl}/admin/user_password_reset`, { 
                method: 'POST', 
                body: formData 
            });
            const result = await res.json();
            
            if(result.status === 'success') {
                Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: result.message, showConfirmButton: false, timer: 1500 });
            } else {
                Swal.fire('ผิดพลาด', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
        }
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>