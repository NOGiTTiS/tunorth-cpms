<?php require_once '../app/views/templates/header.php'; ?>
<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php include '../app/views/templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">จัดการประกาศ</h2>
                    <button onclick="openAnnounceModal()" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold">+ สร้างประกาศใหม่</button>
                </div>

                <div class="space-y-4">
                    <?php foreach($data['announcements'] as $row): ?>
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex justify-between items-center">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-1 rounded mb-2 inline-block <?php echo $row['type'] == 'DANGER' ? 'bg-red-100 text-red-600' : ($row['type'] == 'WARNING' ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'); ?>">
                                <?php echo $row['type']; ?>
                            </span>
                            <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($row['title']); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($row['content']); ?></p>
                        </div>
                        <button onclick="deleteAnnounce(<?php echo $row['id']; ?>)" class="text-red-400 hover:text-red-600">🗑️</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
async function openAnnounceModal() {
    const { value: formValues } = await Swal.fire({
        title: 'สร้างประกาศใหม่',
        html:
            `<div class="text-left font-prompt">
                <input id="swal-title" class="w-full p-3 border rounded-xl mb-4" placeholder="หัวข้อประกาศ">
                <textarea id="swal-content" class="w-full p-3 border rounded-xl mb-4 h-32" placeholder="รายละเอียด..."></textarea>
                <select id="swal-type" class="w-full p-3 border rounded-xl bg-white">
                    <option value="INFO">สีฟ้า (ทั่วไป)</option>
                    <option value="WARNING">สีส้ม (แจ้งเตือน)</option>
                    <option value="DANGER">สีแดง (เร่งด่วน)</option>
                </select>
            </div>`,
        confirmButtonText: 'บันทึก',
        confirmButtonColor: '#ec4899',
        preConfirm: () => {
            return {
                title: document.getElementById('swal-title').value,
                content: document.getElementById('swal-content').value,
                type: document.getElementById('swal-type').value
            }
        }
    });

    if (formValues) {
        const fd = new FormData();
        fd.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        Object.keys(formValues).forEach(key => fd.append(key, formValues[key]));
        const res = await fetch('/admin/announcement_store', { method: 'POST', body: fd });
        if((await res.json()).status === 'success') location.reload();
    }
}

async function deleteAnnounce(id) {
    if ((await Swal.fire({ title: 'ลบประกาศ?', showCancelButton: true })).isConfirmed) {
        const res = await fetch('/admin/announcement_delete/' + id);
        if((await res.json()).status === 'success') location.reload();
    }
}
</script>