<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <!-- 1. Sidebar -->
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <!-- 2. Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Teacher Review</span>
            <button onclick="toggleSidebar()" class="p-2 bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
                
                <!-- Page Title & Mode Switcher -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 leading-tight">รายการตรวจงาน</h2>
                        <p class="text-gray-500 text-sm mt-1">ตรวจสอบเอกสารและให้ข้อเสนอแนะแก่รายห้องเรียน</p>
                    </div>

                    <!-- 🔘 แท็บสลับโหมดการดูงาน -->
                    <div class="bg-white p-1 rounded-2xl shadow-sm border border-gray-100 flex self-start md:self-auto">
                        <a href="?mode=mine" class="px-5 py-2 rounded-xl text-xs font-bold transition-all <?php echo ($data['current_mode'] != 'all') ? 'bg-pink-600 text-white shadow-lg shadow-pink-100' : 'text-gray-400 hover:text-gray-600'; ?>">
                            📁 งานในที่ปรึกษา
                        </a>
                        <a href="?mode=all" class="px-5 py-2 rounded-xl text-xs font-bold transition-all <?php echo ($data['current_mode'] == 'all') ? 'bg-slate-800 text-white shadow-lg' : 'text-gray-400 hover:text-gray-600'; ?>">
                            🌍 งานทั้งหมดในระบบ
                        </a>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">โครงงาน / ผู้ส่ง</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">ห้อง</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">ครูที่ปรึกษา</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">หัวข้อ</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">สถานะ</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 font-prompt">
                                <?php if(empty($data['submissions'])): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-20 text-center text-gray-400 italic">ยังไม่มีข้อมูลการส่งงานในหมวดนี้</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach($data['submissions'] as $row): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <!-- โครงงาน / ผู้ส่ง -->
                                    <td class="px-6 py-5 min-w-[220px]">
                                        <p class="font-bold text-slate-800 leading-tight mb-1"><?php echo htmlspecialchars($row['project_name_th']); ?></p>
                                        <p class="text-[10px] text-gray-400 font-medium italic">โดย: <?php echo htmlspecialchars($row['submitter_name']); ?></p>
                                    </td>

                                    <!-- ห้อง (New!) -->
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="bg-slate-800 text-white text-[10px] font-black px-2.5 py-1 rounded-lg">
                                            ม.<?php echo htmlspecialchars($row['student_room'] ?: 'N/A'); ?>
                                        </span>
                                    </td>

                                    <!-- ครูที่ปรึกษา (แสดงจากที่นักเรียนพิมพ์มา) -->
                                    <td class="px-6 py-5 min-w-[150px]">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-bold text-slate-500">
                                                👤 <?php echo htmlspecialchars($row['advisor_name'] ?: 'ไม่ระบุ'); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- หัวข้อบทที่ส่ง -->
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <span class="text-[10px] font-black text-pink-600 bg-pink-50 px-3 py-1 rounded-lg border border-pink-100 uppercase">
                                            <?php echo htmlspecialchars($row['step_name']); ?>
                                        </span>
                                    </td>

                                    <!-- สถานะ -->
                                    <td class="px-6 py-5">
                                        <?php 
                                            $badge = ($row['status'] == 'APPROVED') ? 'bg-green-100 text-green-600' : 
                                                    (($row['status'] == 'REJECTED') ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600');
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $badge; ?>">
                                            <?php echo ($row['status'] == 'PENDING') ? 'รอตรวจ' : $row['status']; ?>
                                        </span>
                                    </td>

                                    <!-- จัดการ (ดู/ตรวจ) -->
                                    <td class="px-6 py-5">
                                        <div class="flex justify-center items-center space-x-2">
                                            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($row['file_path']); ?>" target="_blank" 
                                               class="w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all shadow-sm" title="เปิดดูเอกสาร PDF">
                                                👁️
                                            </a>
                                            <button onclick='gradeModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)' 
                                                    class="w-10 h-10 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center hover:bg-pink-600 hover:text-white transition-all shadow-sm" title="ลงคะแนนและคอมเมนต์">
                                                ✏️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Teacher Helper Note -->
                <div class="mt-8 flex items-center p-5 bg-blue-50 rounded-3xl border border-blue-100 text-blue-600 text-xs">
                    <span class="text-xl mr-4">💡</span>
                    <p class="leading-relaxed">
                        <b>คำแนะนำสำหรับคุณครู:</b> คุณสามารถใช้โหมด <b>"งานทั้งหมดในระบบ"</b> เพื่อตรวจสอบความคืบหน้าของนักเรียนข้ามห้องเรียนได้ 
                        ข้อมูล <b>"ห้อง"</b> จะอ้างอิงจากห้องเรียนของผู้ที่กดส่งงานเป็นหลัก
                    </p>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
// ฟังก์ชันสำหรับแสดง Modal ตรวจงาน
async function gradeModal(data) {
    const { value: formValues } = await Swal.fire({
        title: 'ตรวจงาน: ' + data.step_name,
        html:
            `<div class="text-left font-prompt">
                <p class="text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">การตัดสินใจ</p>
                <select id="swal-status" class="w-full p-4 border border-gray-200 rounded-2xl mb-5 focus:ring-2 focus:ring-pink-500 outline-none bg-white font-bold text-slate-700">
                    <option value="APPROVED" ${data.status === 'APPROVED' ? 'selected' : ''}>✅ อนุมัติ (ผ่านขั้นตอน)</option>
                    <option value="REJECTED" ${data.status === 'REJECTED' ? 'selected' : ''}>❌ ตีกลับ (ให้แก้ไขใหม่)</option>
                    <option value="PENDING" ${data.status === 'PENDING' ? 'selected' : ''}>⏳ รอการตรวจสอบ</option>
                </select>
                <p class="text-[10px] font-black text-gray-400 uppercase mb-2 tracking-widest">ข้อเสนอแนะแก่กลุ่มนักเรียน</p>
                <textarea id="swal-comment" class="w-full p-4 border border-gray-200 rounded-2xl h-40 focus:ring-2 focus:ring-pink-500 outline-none transition-all placeholder-gray-300" placeholder="ระบุสิ่งที่นักเรียนต้องปรับปรุง...">${data.comment || ''}</textarea>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'บันทึกผลการตรวจ',
        confirmButtonColor: '#ec4899',
        cancelButtonText: 'ยกเลิก',
        focusConfirm: false,
        customClass: {
            popup: 'rounded-3xl p-6 md:p-10',
            confirmButton: 'rounded-2xl px-8 py-3 font-bold',
            cancelButton: 'rounded-2xl px-8 py-3 font-bold'
        },
        preConfirm: () => {
            return {
                id: data.id,
                status: document.getElementById('swal-status').value,
                comment: document.getElementById('swal-comment').value,
                project_name: data.project_name_th
            }
        }
    });

    if (formValues) {
        Swal.fire({ title: 'กำลังประมวลผล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        Object.keys(formValues).forEach(key => formData.append(key, formValues[key]));
        
        try {
            const res = await fetch(BASE_URL + '/teacher/grade', { method: 'POST', body: formData });
            const result = await res.json();
            
            if(result.status === 'success') {
                Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ!', showConfirmButton: false, timer: 1500 })
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', 'error');
        }
    }
}
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>