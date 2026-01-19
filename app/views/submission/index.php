<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50">
    <?php include __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">CPMS Submission</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-5xl mx-auto">

                <div class="mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800">ส่งเอกสารโครงงาน</h2>
                    <div class="mt-2 flex items-center text-pink-600">
                        <span class="text-sm font-bold bg-pink-50 px-3 py-1 rounded-lg border border-pink-100 italic">
                            📁 กลุ่ม: <?php echo htmlspecialchars($data['group']['project_name_th']); ?>
                        </span>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse block md:table">
                            <thead class="bg-slate-50 border-b border-gray-100 hidden md:table-header-group">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">ลำดับ & ขั้นตอน</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">สถานะ</th>
                                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 block md:table-row-group">
                                <?php foreach ($data['steps'] as $key => $step): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors block md:table-row border-b border-gray-100 md:border-none p-4 md:p-0 mb-4 md:mb-0 bg-white md:bg-transparent rounded-2xl md:rounded-none shadow-sm md:shadow-none">
                                        <td class="px-0 py-2 md:px-6 md:py-5 align-top block md:table-cell">
                                            <div class="flex items-start">
                                                <!-- ลำดับเลข -->
                                                <div class="w-8 h-8 rounded-lg bg-pink-50 text-pink-500 flex items-center justify-center font-bold text-xs mr-4 shrink-0 mt-1">
                                                    <?php echo $step['step_order']; ?>
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <p class="font-bold text-gray-800 text-sm md:text-base"><?php echo htmlspecialchars($step['step_name']); ?></p>

                                                    <?php if ($step['submitted_at']): ?>
                                                        <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">ส่งเมื่อ: <?php echo date('d/m/Y H:i', strtotime($step['submitted_at'])); ?></p>
                                                    <?php endif; ?>

                                                    <!-- Attachments (Admin Provided) -->
                                                    <?php if (!empty($step['file_form_path']) || !empty($step['file_example_path'])): ?>
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            <?php if (!empty($step['file_form_path'])): ?>
                                                                <?php
                                                                $isUrl = preg_match('/^https?:\/\//', $step['file_form_path']);
                                                                $url = $isUrl ? $step['file_form_path'] : BASE_URL . '/' . $step['file_form_path'];
                                                                $icon = $isUrl ? '🔗' : '📄';
                                                                ?>
                                                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="inline-flex items-center px-2 py-1 bg-pink-50 text-pink-600 rounded-md text-[10px] font-bold border border-pink-100 hover:bg-pink-100 transition">
                                                                    <span class="mr-1"><?php echo $icon; ?></span> แบบฟอร์ม
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($step['file_example_path'])): ?>
                                                                <?php
                                                                $isUrl = preg_match('/^https?:\/\//', $step['file_example_path']);
                                                                $url = $isUrl ? $step['file_example_path'] : BASE_URL . '/' . $step['file_example_path'];
                                                                $icon = $isUrl ? '🔗' : '💡';
                                                                ?>
                                                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-600 rounded-md text-[10px] font-bold border border-amber-100 hover:bg-amber-100 transition">
                                                                    <span class="mr-1"><?php echo $icon; ?></span> ตัวอย่าง
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- แสดงไฟล์ที่ส่ง -->
                                                    <?php if (!empty($step['file_path'])): ?>
                                                        <div class="mt-2">
                                                            <?php
                                                            $isLink = preg_match('/^https?:\/\//', $step['file_path']);
                                                            $targetUrl = $isLink ? $step['file_path'] : BASE_URL . '/' . $step['file_path'];
                                                            $btnText = $isLink ? 'เปิดลิงก์งาน' : 'ดูไฟล์แนบ';
                                                            $btnIcon = $isLink ? '🔗' : '📄';
                                                            ?>
                                                            <a href="<?php echo htmlspecialchars($targetUrl); ?>" target="_blank" class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">
                                                                <span class="mr-1"><?php echo $btnIcon; ?></span> <?php echo $btnText; ?>
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- แสดงคอมเม้นต์จากครูทันที (ถ้ามี) -->
                                                    <?php if ($step['comment']): ?>
                                                        <div class="mt-3 p-3 bg-amber-50 border-l-4 border-amber-400 rounded-r-xl">
                                                            <div class="flex items-center mb-1">
                                                                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">📝 ความเห็นจากครู:</span>
                                                            </div>
                                                            <p class="text-xs text-gray-700 leading-relaxed font-medium">
                                                                <?php echo nl2br(htmlspecialchars($step['comment'])); ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-0 py-2 md:px-6 md:py-5 align-top block md:table-cell pl-[3.5rem] md:pl-6">
                                            <?php
                                            $badgeClass = "bg-gray-100 text-gray-400";
                                            $text = "ยังไม่ส่ง";
                                            if ($step['status'] == 'PENDING') {
                                                $badgeClass = "bg-amber-100 text-amber-600";
                                                $text = "รอตรวจ";
                                            }
                                            if ($step['status'] == 'APPROVED') {
                                                $badgeClass = "bg-green-100 text-green-600";
                                                $text = "ผ่านแล้ว";
                                            }
                                            if ($step['status'] == 'REJECTED') {
                                                $badgeClass = "bg-rose-100 text-rose-600";
                                                $text = "แก้ไข";
                                            }
                                            ?>
                                            <div class="mt-1 flex items-center gap-2 flex-wrap">
                                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $badgeClass; ?>">
                                                    <?php echo $text; ?>
                                                </span>

                                                <?php if (isset($step['score']) && $step['score'] !== null && ($data['show_scores_to_students'] ?? '1') === '1'): ?>
                                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-purple-100 text-purple-600 border border-purple-200">
                                                        ⭐ คะแนน: <?php echo $step['score']; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td class="px-0 py-2 md:px-6 md:py-5 align-top text-center block md:table-cell pl-[3.5rem] md:pl-6 md:text-center text-left">
                                            <div class="flex items-center justify-start md:justify-center">
                                                <!-- ปุ่มอัปโหลด -->
                                                <?php
                                                $canSubmit = true;
                                                if ($data['submission_mode'] === 'sequential') {
                                                    // เช็ค step ก่อนหน้า
                                                    $prevIndex = $key - 1;
                                                    if ($prevIndex >= 0) {
                                                        $prevStep = $data['steps'][$prevIndex];
                                                        // ถ้าก่อนหน้ายังไม่ APPROVED -> ห้ามส่ง
                                                        if ($prevStep['status'] !== 'APPROVED') {
                                                            $canSubmit = false;
                                                        }
                                                    }
                                                }
                                                ?>

                                                <?php if ($canSubmit): ?>
                                                    <button onclick="openUploadModal(<?php echo $step['id']; ?>, '<?php echo htmlspecialchars($step['step_name'], ENT_QUOTES); ?>')"
                                                        class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-pink-100 active:scale-95 whitespace-nowrap w-full md:w-auto text-center justify-center flex">
                                                        <?php echo ($step['status']) ? 'ส่งใหม่' : '🚀 อัปโหลดส่งงาน'; ?>
                                                    </button>
                                                <?php else: ?>
                                                    <button disabled class="px-4 py-2 bg-slate-100 text-slate-400 rounded-xl text-xs font-bold whitespace-nowrap w-full md:w-auto text-center justify-center flex cursor-not-allowed items-center">
                                                        <span class="mr-1">🔒</span> ต้องผ่านงานก่อนหน้า
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

                <div class="mt-8 p-6 bg-blue-50 rounded-3xl border border-blue-100">
                    <h4 class="text-blue-700 font-bold mb-2 flex items-center text-sm">
                        <span class="mr-2">💡</span> คำแนะนำการส่งงาน
                    </h4>
                    <ul class="text-xs text-blue-600 space-y-1 opacity-80">
                        <li>• รองรับไฟล์ PDF, DOC, DOCX, PPT หรือลิงก์ (Google Drive/Canva)</li>
                        <li>• ขนาดไฟล์ไม่ควรเกิน 20MB ต่อการอัปโหลด</li>
                        <li>• หากครูให้แก้ไข สถานะจะเปลี่ยนเป็น "แก้ไข" และคุณสามารถส่งใหม่ได้ทันที</li>
                    </ul>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
    // ฟังก์ชันอัปโหลดไฟล์ (เรียกใช้ SweetAlert2)
    // ฟังก์ชันอัปโหลดไฟล์ (เรียกใช้ SweetAlert2)
    async function openUploadModal(stepId, stepName) {
        const {
            value: formValues
        } = await Swal.fire({
            title: 'ส่งงาน: ' + stepName,
            html: `
            <div class="mb-4 text-left font-prompt">
                <div class="flex justify-center space-x-6 mb-6">
                    <label class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                        <input type="radio" name="sub_type" value="file" checked class="w-4 h-4 text-pink-600 focus:ring-pink-500 border-gray-300" 
                               onclick="document.getElementById('input-file-container').style.display='block';document.getElementById('input-link-container').style.display='none';
                                        const f = document.getElementById('swal-input-file'); f.accept='.pdf,.doc,.docx,.ppt,.pptx'; f.nextElementSibling.innerText='รองรับ PDF, Word, PowerPoint (ไม่เกิน 20MB)';">
                        <span class="text-sm font-bold text-gray-700">📄 อัปโหลดไฟล์</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                        <input type="radio" name="sub_type" value="image" class="w-4 h-4 text-pink-600 focus:ring-pink-500 border-gray-300"
                               onclick="document.getElementById('input-file-container').style.display='block';document.getElementById('input-link-container').style.display='none';
                                        const f = document.getElementById('swal-input-file'); f.accept='.jpg,.jpeg,.png,.gif,.webp'; f.nextElementSibling.innerText='รองรับ JPEG, PNG, GIF (ไม่เกิน 20MB)';">
                        <span class="text-sm font-bold text-gray-700">🖼️ รูปภาพ</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50">
                        <input type="radio" name="sub_type" value="link" class="w-4 h-4 text-pink-600 focus:ring-pink-500 border-gray-300"
                               onclick="document.getElementById('input-file-container').style.display='none';document.getElementById('input-link-container').style.display='block';">
                        <span class="text-sm font-bold text-gray-700">🔗 แนบลิงก์</span>
                    </label>
                </div>

                <div id="input-file-container" class="animate-fade-in">
                    <input type="file" id="swal-input-file" class="swal2-file block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100" accept=".pdf,.doc,.docx,.ppt,.pptx">
                    <p class="text-xs text-gray-400 mt-2 text-center text-file-hint">รองรับ PDF, Word, PowerPoint (ไม่เกิน 20MB)</p>
                </div>

                <div id="input-link-container" style="display:none;" class="animate-fade-in">
                    <input type="url" id="swal-input-link" class="swal2-input" placeholder="https://docs.google.com/..." style="display:block; width:100%; margin: 0 auto;">
                    <p class="text-xs text-gray-400 mt-2 text-center">วางลิงก์งานของคุณที่นี่ (อย่าลืมเปิดสิทธิ์ให้ครูดูได้ด้วยนะครับ)</p>
                </div>
            </div>
        `,
            showCancelButton: true,
            confirmButtonText: '🚀 ส่งงานทันที',
            confirmButtonColor: '#ec4899',
            cancelButtonText: 'ยกเลิก',
            focusConfirm: false,
            customClass: {
                popup: 'rounded-3xl'
            },
            preConfirm: () => {
                const type = document.querySelector('input[name="sub_type"]:checked').value;
                if (type === 'file' || type === 'image') {
                    const fileInput = document.getElementById('swal-input-file');
                    if (!fileInput.files.length) {
                        Swal.showValidationMessage('กรุณาเลือกไฟล์ก่อนส่ง');
                        return false;
                    }
                    return {
                        type: type,
                        file: fileInput.files[0]
                    };
                } else {
                    const linkInput = document.getElementById('swal-input-link').value;
                    if (!linkInput) {
                        Swal.showValidationMessage('กรุณาระบุลิงก์งาน');
                        return false;
                    }
                    if (!linkInput.startsWith('http')) {
                        Swal.showValidationMessage('ลิงก์ต้องขึ้นต้นด้วย http:// หรือ https://');
                        return false;
                    }
                    return {
                        type: 'link',
                        link: linkInput
                    };
                }
            }
        });

        if (formValues) {
            const formData = new FormData();
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('step_id', stepId);
            formData.append('step_name', stepName);
            formData.append('group_id', '<?php echo $data['group']['id']; ?>');
            formData.append('group_name', '<?php echo $data['group']['project_name_th']; ?>');
            formData.append('submission_type', formValues.type);

            if (formValues.type === 'file' || formValues.type === 'image') {
                formData.append('project_file', formValues.file);
            } else {
                formData.append('project_link', formValues.link);
            }

            Swal.fire({
                title: 'กำลังส่งงาน...',
                html: 'กรุณารอสักครู่ ระบบกำลังบันทึกข้อมูล',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await fetch(BASE_URL + '/submission/upload', {
                    method: 'POST',
                    body: formData
                });

                // Safe parse
                const text = await res.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error(text);
                    throw new Error('Invalid JSON Response');
                }

                if (result.status === 'success') {
                    Swal.fire({
                            icon: 'success',
                            title: 'ส่งงานสำเร็จ!',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        .then(() => location.reload());
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', result.message, 'error');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('เชื่อมต่อล้มเหลว', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ โปรดลองใหม่', 'error');
            }
        }
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>