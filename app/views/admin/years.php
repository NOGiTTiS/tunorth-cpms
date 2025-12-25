<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Academic Years</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-5xl mx-auto">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 leading-tight">จัดการปีการศึกษา</h2>
                        <p class="text-gray-500 text-sm mt-1">กำหนดปีการศึกษาปัจจุบันและประวัติย้อนหลัง</p>
                    </div>
                    <button onclick="openAddYearModal()" class="w-full md:w-auto bg-pink-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-pink-200 hover:bg-pink-700 transition-all flex items-center justify-center">
                        <span class="text-xl mr-2">+</span> เพิ่มปีการศึกษา
                    </button>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">ปีพุทธศักราช</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">เทอม</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">สถานะ</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach($data['years'] as $year): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-5 text-center font-black text-xl text-slate-700">
                                        <?php echo htmlspecialchars($year['year']); ?>
                                    </td>
                                    <td class="px-6 py-5 text-center font-bold text-slate-500">
                                        <?php echo htmlspecialchars($year['term']); ?>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <?php if($year['is_current']): ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-green-100 text-green-600">
                                                <span class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></span> ปัจจุบัน
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-400">
                                                ประวัติ/ล่วงหน้า
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex justify-center items-center space-x-2">
                                            <button onclick='openEditYearModal(<?php echo json_encode($year); ?>)' class="w-8 h-8 bg-amber-50 text-amber-500 rounded-lg flex items-center justify-center hover:bg-amber-100 transition-colors" title="แก้ไข">
                                                ✏️
                                            </button>
                                            <?php if(!$year['is_current']): ?>
                                                <button onclick="setAsCurrent(<?php echo $year['id']; ?>, '<?php echo $year['year']; ?>')" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors" title="ตั้งเป็นปีปัจจุบัน">
                                                    ตั้งเป็นปัจจุบัน
                                                </button>
                                                <button onclick="deleteYear(<?php echo $year['id']; ?>, '<?php echo $year['year']; ?>')" class="w-8 h-8 bg-rose-50 text-rose-500 rounded-lg flex items-center justify-center hover:bg-rose-100 transition-colors" title="ลบ">
                                                    🗑️
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Helper Text -->
                <div class="mt-6 flex items-center p-4 bg-yellow-50 rounded-2xl border border-yellow-100 text-yellow-700 text-xs">
                    <span class="text-lg mr-3">⚠️</span>
                    <p>การเปลี่ยน "ปีการศึกษาปัจจุบัน" จะมีผลต่อค่าเริ่มต้นในการสร้างกลุ่มใหม่ แต่จะไม่กระทบข้อมูลเก่าที่บันทึกไปแล้ว</p>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
async function openAddYearModal() {
    const { value: formValues } = await Swal.fire({
        title: 'เพิ่มปีการศึกษาใหม่',
        html:
            '<div class="text-left font-prompt">' +
            '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">ปีพุทธศักราช (เช่น 2568)</label>' +
            '<input id="swal-year" type="number" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none" value="<?php echo date("Y")+543+1; ?>">' +
            '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">ภาคเรียน</label>' +
            '<select id="swal-term" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none bg-white">' +
            '<option value="1">ภาคเรียนที่ 1</option>' +
            '<option value="2">ภาคเรียนที่ 2</option>' +
            '<option value="3">ภาคฤดูร้อน</option>' +
            '</select>' +
            '<div class="flex items-center gap-2">' +
            '<input type="checkbox" id="swal-current" class="w-5 h-5 text-pink-600 rounded focus:ring-pink-500 border-gray-300">' +
            '<label for="swal-current" class="text-sm font-bold text-gray-700">ตั้งเป็นปีปัจจุบันทันที</label>' +
            '</div>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: 'บันทึกข้อมูล',
        confirmButtonColor: '#ec4899',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-3xl p-8' },
        preConfirm: () => {
            return {
                year: document.getElementById('swal-year').value,
                term: document.getElementById('swal-term').value,
                is_current: document.getElementById('swal-current').checked ? 1 : 0
            }
        }
    });

    if (formValues) {
        if(!formValues.year) return Swal.fire('Error', 'กรุณาระบุปีการศึกษา', 'error');
        
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        formData.append('year', formValues.year);
        formData.append('term', formValues.term);
        formData.append('is_current', formValues.is_current);

        submitData('<?php echo BASE_URL; ?>/admin/year_store', formData);
    }
}

async function openEditYearModal(yearData) {
    const { value: formValues } = await Swal.fire({
        title: 'แก้ไขปีการศึกษา ' + yearData.year,
        html:
            '<div class="text-left font-prompt">' +
            '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">ปีพุทธศักราช</label>' +
            `<input id="swal-edit-year" type="number" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none" value="${yearData.year}">` +
            '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">ภาคเรียน</label>' +
            `<select id="swal-edit-term" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none bg-white">` +
            `<option value="1" ${yearData.term == '1' ? 'selected' : ''}>ภาคเรียนที่ 1</option>` +
            `<option value="2" ${yearData.term == '2' ? 'selected' : ''}>ภาคเรียนที่ 2</option>` +
            `<option value="3" ${yearData.term == '3' ? 'selected' : ''}>ภาคฤดูร้อน</option>` +
            '</select>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: 'บันทึกการแก้ไข',
        confirmButtonColor: '#f59e0b',
        cancelButtonText: 'ยกเลิก',
        customClass: { popup: 'rounded-3xl p-8' },
        preConfirm: () => {
            return {
                id: yearData.id,
                year: document.getElementById('swal-edit-year').value,
                term: document.getElementById('swal-edit-term').value
            }
        }
    });

    if (formValues) {
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        formData.append('id', formValues.id);
        formData.append('year', formValues.year);
        formData.append('term', formValues.term);
        
        // Preserve current/active status implicitly or update if needed
        // For simplicity, we just update basic info here
        
        submitData('<?php echo BASE_URL; ?>/admin/year_update', formData);
    }
}

function setAsCurrent(id, year) {
    Swal.fire({
        title: 'ยืนยันการเปลี่ยนปีปัจจุบัน?',
        text: `คุณต้องการตั้งปี ${year} เป็นปีการศึกษาปัจจุบันใช่หรือไม่?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ec4899',
        confirmButtonText: 'ใช่, ดำเนินการ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
            formData.append('id', id);
            submitData('<?php echo BASE_URL; ?>/admin/year_set_current', formData);
        }
    });
}

function deleteYear(id, year) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: `ต้องการลบข้อมูลปี ${year} ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        confirmButtonText: 'ลบข้อมูล',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?php echo BASE_URL; ?>/admin/year_delete/' + id)
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire('Deleted!', 'ลบข้อมูลเรียบร้อย', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

async function submitData(url, formData) {
    Swal.fire({ title: 'Processing...', didOpen: () => Swal.showLoading() });
    try {
        const res = await fetch(url, { method: 'POST', body: formData });
        const data = await res.json();
        if(data.status === 'success') {
            Swal.fire('Success', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Cannot connect to server', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
