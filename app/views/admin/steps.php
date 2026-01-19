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
                            <?php foreach ($data['steps'] as $step): ?>
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

        // Helper to generate file link HTML
        const getFileLink = (path, label) => {
            if (!path) return '';
            const isUrl = path.startsWith('http');
            const url = isUrl ? path : (window.BASE_URL || '') + '/' + path;
            const icon = isUrl ? '🔗' : '📄';
            return `<div class="mt-1 text-xs text-blue-500 overflow-hidden text-ellipsis whitespace-nowrap">
                   <a href="${url}" target="_blank" class="hover:underline">${icon} ดู${label}เดิม</a>
                </div>`;
        };

        const {
            value: formValues
        } = await Swal.fire({
            title: isEdit ? 'แก้ไขขั้นตอน' : 'เพิ่มขั้นตอนงานใหม่',
            width: '700px',
            html: `<div class="text-left space-y-4 px-2">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">ชื่อขั้นตอน (เช่น บทที่ 1)</label>
                    <input id="swal-name" class="w-full p-3 border rounded-xl mt-1 focus:ring-2 focus:ring-pink-500 outline-none" value="${isEdit ? stepData.step_name : ''}">
                </div>
                ${isEdit ? `
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">ลำดับการแสดงผล</label>
                    <input id="swal-order" type="number" class="w-full p-3 border rounded-xl mt-1 focus:ring-2 focus:ring-pink-500 outline-none" value="${stepData.step_order}">
                </div>` : ''}

                <!-- File Inputs -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-dashed border-slate-300">
                    <!-- Form Section -->
                    <div>
                        <label class="text-xs font-bold text-pink-500 uppercase tracking-wider mb-2 block">📄 แบบฟอร์ม (Form)</label>
                        <div class="flex space-x-2 text-xs mb-2">
                             <label class="cursor-pointer"><input type="radio" name="type_form" value="file" checked onclick="toggleInput('form', 'file')"> อัปโหลดไฟล์</label>
                             <label class="cursor-pointer"><input type="radio" name="type_form" value="link" onclick="toggleInput('form', 'link')"> แนบลิงก์</label>
                        </div>
                        
                        <input id="swal-form-file" type="file" class="w-full text-sm text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                        <input id="swal-form-link" type="url" placeholder="https://..." class="w-full p-2 border rounded-lg hidden text-sm" value="">
                        
                        ${isEdit ? getFileLink(stepData.file_form_path, 'แบบฟอร์ม') : ''}
                    </div>

                    <!-- Example Section -->
                    <div>
                        <label class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-2 block">💡 ตัวอย่าง (Example)</label>
                        <div class="flex space-x-2 text-xs mb-2">
                             <label class="cursor-pointer"><input type="radio" name="type_example" value="file" checked onclick="toggleInput('example', 'file')"> อัปโหลดไฟล์</label>
                             <label class="cursor-pointer"><input type="radio" name="type_example" value="link" onclick="toggleInput('example', 'link')"> แนบลิงก์</label>
                        </div>

                        <input id="swal-example-file" type="file" class="w-full text-sm text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        <input id="swal-example-link" type="url" placeholder="https://..." class="w-full p-2 border rounded-lg hidden text-sm" value="">
                        
                        ${isEdit ? getFileLink(stepData.file_example_path, 'ตัวอย่าง') : ''}
                    </div>
                </div>
            </div>`,
            showCancelButton: true,
            confirmButtonText: 'บันทึกข้อมูล',
            confirmButtonColor: '#ec4899',
            didOpen: () => {
                // Helper to toggle inputs
                window.toggleInput = (section, type) => {
                    document.getElementById(`swal-${section}-file`).classList.toggle('hidden', type !== 'file');
                    document.getElementById(`swal-${section}-link`).classList.toggle('hidden', type !== 'link');
                };
            },
            preConfirm: () => {
                const name = document.getElementById('swal-name').value;
                const order = document.getElementById('swal-order') ? document.getElementById('swal-order').value : null;

                // Get types
                const typeForm = document.querySelector('input[name="type_form"]:checked').value;
                const typeExample = document.querySelector('input[name="type_example"]:checked').value;

                const fileForm = document.getElementById('swal-form-file').files[0];
                const linkForm = document.getElementById('swal-form-link').value;

                const fileExample = document.getElementById('swal-example-file').files[0];
                const linkExample = document.getElementById('swal-example-link').value;

                if (!name) {
                    Swal.showValidationMessage('กรุณากรอกชื่อขั้นตอน');
                    return false;
                }

                return {
                    step_name: name,
                    step_order: order,
                    type_form: typeForm,
                    file_form: fileForm,
                    link_form: linkForm,
                    type_example: typeExample,
                    file_example: fileExample,
                    link_example: linkExample
                };
            }
        });

        if (formValues) {
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('step_name', formValues.step_name);
            if (formValues.step_order) formData.append('step_order', formValues.step_order);

            // Form Data
            formData.append('type_form', formValues.type_form);
            if (formValues.type_form === 'file' && formValues.file_form) {
                formData.append('file_form', formValues.file_form);
            } else if (formValues.type_form === 'link') {
                formData.append('link_form', formValues.link_form);
            }

            // Example Data
            formData.append('type_example', formValues.type_example);
            if (formValues.type_example === 'file' && formValues.file_example) {
                formData.append('file_example', formValues.file_example);
            } else if (formValues.type_example === 'link') {
                formData.append('link_example', formValues.link_example);
            }

            let endpoint = '/admin/step_store';

            if (isEdit) {
                formData.append('id', stepData.id);
                endpoint = '/admin/step_update';
            }

            const baseUrl = window.BASE_URL || '';

            // Show loading
            Swal.fire({
                title: 'กำลังบันทึก...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const res = await fetch(`${baseUrl}${endpoint}`, {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.status === 'success') {
                    Swal.close();
                    location.reload();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Connection Failed', 'error');
            }
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
                if (data.status === 'success') {
                    location.reload();
                } else {
                    Swal.fire('ลบไม่สำเร็จ', data.message, 'error');
                }
            }
        });
    }
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>