<?php require_once __DIR__ . '/../templates/header.php'; ?>
<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500">Project Steps</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">จัดการขั้นตอนงาน</h2>
                        <p class="text-gray-500 text-sm">กำหนดรายการเอกสารที่นักเรียนต้องส่งตามลำดับ</p>
                    </div>
                    <button onclick="openStepModal()" class="w-full md:w-auto bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-800 transition shadow-lg">+ เพิ่มขั้นตอน</button>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b">
                            <tr>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest w-24">ลำดับ</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">หัวข้อ / สิ่งที่ต้องส่ง</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach($data['steps'] as $step): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="w-10 h-10 bg-pink-100 text-pink-600 rounded-xl flex items-center justify-center font-black">
                                        <?php echo $step['step_order']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($step['step_name']); ?></p>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-center space-x-2">
                                        <button onclick='openStepModal(<?php echo htmlspecialchars(json_encode($step), ENT_QUOTES, 'UTF-8'); ?>)' class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all">✏️</button>
                                        <button onclick="deleteStep(<?php echo $step['id']; ?>, '<?php echo htmlspecialchars($step['step_name'], ENT_QUOTES); ?>')" class="p-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-600 hover:text-white transition-all">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
async function openStepModal(stepData = null) {
    const isEdit = stepData !== null;
    const { value: formValues } = await Swal.fire({
        title: isEdit ? 'แก้ไขขั้นตอน' : 'เพิ่มขั้นตอนงานใหม่',
        html:
            `<div class="text-left space-y-4 px-2">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">ชื่อขั้นตอน (เช่น บทที่ 1)</label>
                    <input id="swal-name" class="w-full p-3 border rounded-xl mt-1 focus:ring-2 focus:ring-pink-500 outline-none" value="${isEdit ? stepData.step_name : ''}">
                </div>
                ${isEdit ? `
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">ลำดับการแสดงผล</label>
                    <input id="swal-order" type="number" class="w-full p-3 border rounded-xl mt-1 focus:ring-2 focus:ring-pink-500 outline-none" value="${stepData.step_order}">
                </div>` : ''}
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'บันทึกข้อมูล',
        confirmButtonColor: '#ec4899',
        preConfirm: () => {
            const data = {
                step_name: document.getElementById('swal-name').value,
            };
            if(isEdit) {
                data.id = stepData.id;
                data.step_order = document.getElementById('swal-order').value;
            }
            if(!data.step_name) {
                Swal.showValidationMessage('กรุณากรอกชื่อขั้นตอน');
                return false;
            }
            return data;
        }
    });

    if (formValues) {
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        Object.keys(formValues).forEach(key => formData.append(key, formValues[key]));
        const endpoint = isEdit ? '/admin/step_update' : '/admin/step_store';
        const baseUrl = window.BASE_URL || '';
        const res = await fetch(`${baseUrl}${endpoint}`, { method: 'POST', body: formData });
        const result = await res.json();
        if(result.status === 'success') location.reload();
    }
}

function deleteStep(id, name) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `คุณต้องการลบ "${name}" ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'ลบทิ้ง',
        cancelButtonText: 'ยกเลิก'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const baseUrl = window.BASE_URL || '';
            const res = await fetch(`${baseUrl}/admin/step_delete/` + id);
            const data = await res.json();
            if(data.status === 'success') {
                location.reload();
            } else {
                Swal.fire('ลบไม่สำเร็จ', data.message, 'error');
            }
        }
    });
}
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>