<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php require_once __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Presentation Criteria</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-5xl mx-auto">

                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                            <span class="mr-2 text-pink-600">📋</span> จัดการเกณฑ์การประเมิน
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">กำหนดหัวข้อและคะแนนสำหรับการประเมินโครงงาน</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="<?php echo BASE_URL; ?>/presentation/manage" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 transition-all">
                            ← กลับหน้าตาราง
                        </a>
                        <button onclick="openForm()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 transition-all transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            เพิ่มหัวข้อประเมิน
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-16">ลำดับ</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">หัวข้อการประเมิน</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-32">คะแนนเต็ม</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-32">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($data['criteria'])): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                <p>ยังไม่มีเกณฑ์การประเมิน</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data['criteria'] as $item): ?>
                                        <tr class="hover:bg-gray-50 transition-colors group">
                                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-500">
                                                <?php echo $item['criteria_order']; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($item['label']); ?>
                                            </td>
                                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-600 bg-gray-50/50 rounded-lg mx-2">
                                                <?php echo $item['max_score']; ?>
                                            </td>
                                            <td class="px-6 py-4 text-center text-sm font-medium">
                                                <div class="flex items-center justify-center space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button onclick='openForm(<?php echo json_encode($item); ?>)' class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-50 rounded-lg transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    <button onclick="deleteItem(<?php echo $item['id']; ?>)" class="text-red-600 hover:text-red-900 p-1 hover:bg-red-50 rounded-lg transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    async function openForm(item = null) {
        const isEdit = !!item;
        const title = isEdit ? 'แก้ไขเกณฑ์การประเมิน' : 'เพิ่มเกณฑ์การประเมิน';

        const {
            value: formValues
        } = await Swal.fire({
            title: title,
            html: '<div class="text-left font-prompt space-y-4">' +
                '<div>' +
                '<label class="block text-xs font-bold text-gray-500 uppercase mb-1">หัวข้อการประเมิน</label>' +
                `<input id="swal-label" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none transition-all" value="${item ? item.label : ''}" placeholder="ระบุหัวข้อ...">` +
                '</div>' +
                '<div class="grid grid-cols-2 gap-4">' +
                '<div>' +
                '<label class="block text-xs font-bold text-gray-500 uppercase mb-1">ลำดับ (Order)</label>' +
                `<input id="swal-order" type="number" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none transition-all" value="${item ? item.criteria_order : ''}" placeholder="0">` +
                '</div>' +
                '<div>' +
                '<label class="block text-xs font-bold text-gray-500 uppercase mb-1">คะแนนเต็ม</label>' +
                `<input id="swal-max" type="number" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none transition-all" value="${item ? item.max_score : '10'}" min="1">` +
                '</div>' +
                '</div>' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#ec4899',
            customClass: {
                popup: 'rounded-3xl p-8'
            },
            preConfirm: () => {
                return {
                    id: item ? item.id : null,
                    label: document.getElementById('swal-label').value,
                    criteria_order: document.getElementById('swal-order').value,
                    max_score: document.getElementById('swal-max').value
                }
            }
        });

        if (formValues) {
            saveItem(formValues);
        }
    }

    function saveItem(data) {
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        formData.append('label', data.label);
        formData.append('criteria_order', data.criteria_order);
        formData.append('max_score', data.max_score);
        if (data.id) formData.append('id', data.id);

        fetch('<?php echo BASE_URL; ?>/presentation/save_criteria', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
    }

    function deleteItem(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลนี้จะถูกลบออกจากระบบ",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
                formData.append('id', id);

                fetch('<?php echo BASE_URL; ?>/presentation/delete_criteria', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'ลบสำเร็จ',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>