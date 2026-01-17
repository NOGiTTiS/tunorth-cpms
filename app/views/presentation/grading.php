<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php require_once __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Grading</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

                    <!-- Header -->
                    <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <span class="mr-2 text-pink-500">⭐</span> ประเมินโครงงาน (Project Grading)
                        </h2>
                        <a href="<?php echo BASE_URL; ?>/presentation/manage" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-gray-300 hover:text-white rounded-xl text-sm font-bold transition-all border border-slate-700">
                            ← กลับ
                        </a>
                    </div>

                    <div class="p-6 md:p-8">
                        <!-- Project Info -->
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 mb-8 relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="fas fa-project-diagram text-8xl text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-bold text-blue-900 mb-2">ข้อมูลโครงงาน</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm relative z-10">
                                <div>
                                    <span class="block text-blue-400 text-xs font-bold uppercase mb-1">ชื่อโครงงาน</span>
                                    <div class="font-bold text-gray-800 text-lg">
                                        <?php echo htmlspecialchars($data['booking']['project_name_th'] ?? '-'); ?>
                                    </div>
                                    <div class="text-gray-500">
                                        <?php echo htmlspecialchars($data['booking']['project_name_en'] ?? ''); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <span class="block text-blue-400 text-xs font-bold uppercase mb-1">ที่ปรึกษา (Advisor)</span>
                                        <div class="font-medium text-gray-700">
                                            <?php echo htmlspecialchars($data['booking']['advisor_name'] ?? '-'); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="block text-blue-400 text-xs font-bold uppercase mb-1">ห้องสอบ (Room)</span>
                                        <div class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($data['booking']['room'] ?? '-'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grading Form -->
                        <form id="gradingForm" class="space-y-6">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="booking_id" value="<?php echo $data['booking']['id']; ?>">

                            <div class="overflow-x-auto rounded-xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-16">#</th>
                                            <th class="px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">รายการประเมิน</th>
                                            <th class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-24">เต็ม</th>
                                            <th class="px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-32">คะแนน</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php
                                        // Use dynamic criteria list passed from controller
                                        $criteria_list = !empty($data['criteria_list']) ? $data['criteria_list'] : [
                                            // Fallback if empty
                                            ['id' => 1, 'label' => 'เนื้อหาของโครงงานมีความน่าสนใจ และเป็นประโยชน์', 'max_score' => 10, 'criteria_order' => 1],
                                            // ... (truncated for brevity, but actually we expect DB data)
                                        ];

                                        $existing_criteria = (!empty($data['existing_score']) && !empty($data['existing_score']['criteria_data']))
                                            ? json_decode($data['existing_score']['criteria_data'], true)
                                            : [];

                                        $existing_comment = (!empty($data['existing_score'])) ? $data['existing_score']['comments'] : '';

                                        $totalMaxScore = 0;
                                        ?>

                                        <?php if (empty($criteria_list)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center p-4">ไม่มีหัวข้อการประเมิน</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($criteria_list as $index => $item):
                                                // Handle potential difference in structure if using default fallback vs DB array
                                                // DB: id, label, max_score, criteria_order
                                                // Existing logic used keys 1..10. Now we use IDs.

                                                // If using fallback (associative array), we might need normalization? 
                                                // Let's assume controller always passes array of objects/assoc arrays.

                                                $key = $item['id']; // Use DB ID as key
                                                $label = $item['label'];
                                                $max = $item['max_score'];
                                                $totalMaxScore += $max;

                                                // Fallback for old saved scores that might use 1-10 keys if we migrated?
                                                // If saved data uses 1-10 but DB uses AI IDs (11, 12...), this might break.
                                                // However, since we just created the table, IDs will likely start from 1.
                                                // But if we deleted and re-added...
                                                // Ideally, we should migrate old data or just accept that old scores might not map perfectly if IDs changed.
                                                // For now, simple mapping.
                                            ?>
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 text-center text-sm font-medium text-gray-400"><?php echo $item['criteria_order'] ?? ($index + 1); ?></td>
                                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium"><?php echo htmlspecialchars($label); ?></td>
                                                    <td class="px-6 py-4 text-center text-sm font-bold text-gray-400"><?php echo $max; ?></td>
                                                    <td class="px-6 py-4">
                                                        <input type="number" name="criteria[<?php echo $key; ?>]"
                                                            class="score-input block w-full text-center rounded-xl border-gray-200 shadow-sm focus:border-pink-500 focus:ring-pink-500 sm:text-sm py-2 font-bold text-gray-800 bg-gray-50 focus:bg-white transition-colors"
                                                            min="0" max="<?php echo $max; ?>" required placeholder="0"
                                                            value="<?php echo isset($existing_criteria[$key]) ? $existing_criteria[$key] : ''; ?>">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <tr class="bg-slate-50">
                                            <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-slate-700 uppercase tracking-wider">รวมคะแนน (Total)</td>
                                            <td class="px-6 py-4 text-center">
                                                <span id="totalScore" class="text-2xl font-black text-pink-600">0</span>
                                                <span class="text-xs text-gray-400 font-bold">/ <?php echo $totalMaxScore; ?></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Comment Section -->
                            <div>
                                <label for="comments" class="block text-sm font-bold text-gray-700 mb-2">
                                    ความคิดเห็นเพิ่มเติม (Comments)
                                </label>
                                <textarea id="comments" name="comments" rows="3"
                                    class="shadow-sm focus:ring-pink-500 focus:border-pink-500 block w-full sm:text-sm border-gray-200 rounded-xl p-4 bg-gray-50 focus:bg-white transition-colors"
                                    placeholder="ระบุข้อเสนอแนะเพิ่มเติม..."><?php echo htmlspecialchars($existing_comment ?? ''); ?></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end border-t border-gray-100 pt-6">
                                <button type="submit" class="inline-flex justify-center py-3 px-8 border border-transparent shadow-lg text-sm font-bold rounded-xl text-white bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transform transition-all hover:-translate-y-0.5">
                                    <i class="fas fa-save mr-2"></i> บันทึกผลการประเมิน
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.score-input');
        const totalDisplay = document.getElementById('totalScore');

        function calculateTotal() {
            let total = 0;
            inputs.forEach(input => {
                let val = parseInt(input.value) || 0;
                let max = parseInt(input.getAttribute('max')) || 10;
                if (val > max) val = max;
                if (val < 0) val = 0;
                total += val;
            });
            totalDisplay.textContent = total;
        }

        inputs.forEach(input => {
            input.addEventListener('input', calculateTotal);
            input.addEventListener('blur', function() {
                let val = parseInt(this.value);
                let max = parseInt(this.getAttribute('max')) || 10;
                if (isNaN(val)) this.value = '';
                else if (val > max) this.value = max;
                else if (val < 0) this.value = 0;
                calculateTotal();
            });
        });

        calculateTotal();

        document.getElementById('gradingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            Swal.fire({
                title: 'กำลังบันทึก...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('<?php echo BASE_URL; ?>/presentation/submit_score', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ!',
                            text: 'คะแนนการนำเสนอถูกบันทึกเรียบร้อยแล้ว',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#ec4899',
                            timer: 2000
                        }).then(() => {
                            window.location.href = '<?php echo BASE_URL; ?>/presentation/manage';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: data.message || 'ไม่สามารถบันทึกข้อมูลได้',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        confirmButtonColor: '#ef4444'
                    });
                });
        });
    });
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>