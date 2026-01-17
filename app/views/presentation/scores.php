<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php require_once __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Project Scores</span>
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
                            <span class="mr-2 text-pink-500">📊</span> สรุปคะแนนโครงงาน (Score Summary)
                        </h2>
                        <a href="<?php echo BASE_URL; ?>/presentation/manage" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-gray-300 hover:text-white rounded-xl text-sm font-bold transition-all border border-slate-700">
                            ← กลับ
                        </a>
                    </div>

                    <div class="p-6 md:p-8">
                        <!-- Project Info -->
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 mb-8 relative overflow-hidden">
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
                                </div>
                            </div>
                        </div>

                        <!-- Score Table -->
                        <?php
                        $totalSum = 0;
                        $scorerCount = count($data['scores']);
                        ?>

                        <?php if ($scorerCount > 0): ?>
                            <div class="overflow-x-auto rounded-xl border border-gray-100 mb-6">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">กรรมการ (Scorer)</th>
                                            <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider">วันที่ประเมิน</th>
                                            <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider">คะแนนรวม</th>
                                            <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">ข้อเสนอแนะ</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($data['scores'] as $score):
                                            $totalSum += $score['total_score'];
                                        ?>
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 text-sm font-bold text-gray-700">
                                                    <?php echo htmlspecialchars($score['scorer_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 text-center text-sm text-gray-500">
                                                    <?php echo date('d/m/Y H:i', strtotime($score['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                        <?php echo $score['total_score']; ?>
                                                    </span>
                                                    <span class="text-xs text-gray-400 font-medium">/ <?php echo $data['max_possible_score']; ?></span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600 italic">
                                                    "<?php echo htmlspecialchars($score['comments'] ?? '-'); ?>"
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Calculation -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                                    <h4 class="text-sm font-bold text-gray-500 uppercase mb-4">การคำนวณ (Calculation)</h4>
                                    <div class="space-y-2 text-sm text-gray-600">
                                        <div class="flex justify-between">
                                            <span>จำนวนกรรมการ:</span>
                                            <span class="font-bold"><?php echo $scorerCount; ?> ท่าน</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>คะแนนรวมทั้งหมด:</span>
                                            <span class="font-bold"><?php echo $totalSum; ?> คะแนน</span>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                                            <span>คะแนนเฉลี่ย (Average):</span>
                                            <span class="font-bold text-gray-800">
                                                <?php
                                                $average = $totalSum / $scorerCount;
                                                echo number_format($average, 2);
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden flex flex-col justify-center items-center text-center">
                                    <div class="absolute top-0 right-0 p-4 opacity-5">
                                        <i class="fas fa-trophy text-9xl"></i>
                                    </div>
                                    <h4 class="text-blue-300 text-xs font-bold uppercase tracking-widest mb-2">คะแนนสุทธิ (Final Score 20%)</h4>

                                    <?php
                                    // Formula: (Average / MaxPossible) * 20
                                    $finalScore = ($average / $data['max_possible_score']) * 20;
                                    ?>

                                    <div class="text-5xl font-black mb-1 bg-clip-text text-transparent bg-gradient-to-r from-pink-400 to-rose-400">
                                        <?php echo number_format($finalScore, 2); ?>
                                    </div>
                                    <div class="text-gray-400 text-sm font-medium">คะแนนเต็ม 20 คะแนน</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="text-lg font-medium">ยังไม่มีการประเมินคะแนน</p>
                                <p class="text-sm opacity-75">รอคณะกรรมการให้คะแนน</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>