<?php require_once __DIR__ . '/../templates/header.php'; ?>
<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md">
            <span class="font-bold text-pink-500 font-prompt">Room Assignment</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-6xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 font-prompt">จัดการห้องเรียนและครูที่ปรึกษา</h2>
                    <p class="text-gray-500 text-sm">กำหนดสิทธิ์การตรวจงานของครูในแต่ละห้องเรียน</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Assignment Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sticky top-8">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                                <span class="bg-pink-100 text-pink-600 rounded-lg p-2 mr-3 text-lg">👨‍🏫</span>
                                มอบหมายห้องเรียน
                            </h3>

                            <form id="assignForm" class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">เลือกครู (Teacher)</label>
                                    <select name="teacher_id" class="w-full p-3 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-pink-500 transition cursor-pointer bg-slate-50 hover:bg-white">
                                        <?php foreach ($data['teachers'] as $t): ?>
                                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">เลือกห้อง (Classrooms)</label>
                                    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-4 rounded-xl border border-gray-200">
                                        <?php foreach ($data['rooms'] as $room): ?>
                                            <label class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-white hover:shadow-sm transition">
                                                <input type="checkbox" name="rooms[]" value="<?php echo $room; ?>" class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500 border-gray-300">
                                                <span class="text-sm font-bold text-gray-600">ม.<?php echo $room; ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-2 text-right">* สามารถเลือกได้หลายห้อง</p>
                                </div>

                                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl shadow-lg shadow-slate-200 hover:bg-slate-800 transition active:scale-95">
                                    บันทึกการมอบหมาย
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Assignments List -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-slate-50">
                                <h3 class="font-bold text-gray-800">รายการที่มอบหมายแล้ว</h3>
                                <div class="text-xs font-bold text-slate-500 bg-white px-3 py-1 rounded-lg border border-gray-200">
                                    ทั้งหมด: <?php echo count($data['assignments']); ?> รายการ
                                </div>
                            </div>

                            <?php if (empty($data['assignments'])): ?>
                                <div class="p-10 text-center text-gray-400">
                                    <p class="text-4xl mb-2">🍃</p>
                                    <p>ยังไม่มีการมอบหมายครูประจำห้อง</p>
                                </div>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-slate-50/50 border-b border-gray-100">
                                            <tr>
                                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">ห้องเรียน</th>
                                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">ครูผู้รับผิดชอบ</th>
                                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            <?php foreach ($data['assignments'] as $assign): ?>
                                                <tr class="hover:bg-pink-50/20 transition">
                                                    <td class="px-6 py-4">
                                                        <span class="bg-pink-100 text-pink-600 px-3 py-1 rounded-lg font-bold text-sm">
                                                            ม.<?php echo $assign['room']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 font-bold text-gray-700">
                                                        <?php echo htmlspecialchars($assign['teacher_name']); ?>
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <button onclick="removeAssignment(<?php echo $assign['teacher_id']; ?>, '<?php echo $assign['room']; ?>')"
                                                            class="text-red-400 hover:text-red-600 p-2 hover:bg-red-50 rounded-lg transition" title="ลบ">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.getElementById('assignForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        Swal.fire({
            title: 'กำลังบันทึก...',
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch('<?php echo BASE_URL; ?>/admin/assign_teacher', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            if (result.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    showConfirmButton: false,
                    timer: 1000
                }).then(() => location.reload());
            } else {
                Swal.fire('ผิดพลาด', result.message, 'error');
            }
        } catch (err) {
            Swal.fire('Error', 'Connection failed', 'error');
        }
    });

    function removeAssignment(tid, room) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: `ต้องการยกเลิกการมอบหมายห้อง ม.${room} หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('teacher_id', tid);
                formData.append('room', room);

                const res = await fetch('<?php echo BASE_URL; ?>/admin/unassign_teacher', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.status === 'success') location.reload();
            }
        });
    }
</script>
<?php require_once __DIR__ . '/../templates/footer.php'; ?>