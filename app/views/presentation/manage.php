<?php

/** @var array $data */
if (isset($data)) extract($data);

// --- Logic for Week Calculation (Fallback if Controller not updated) ---
if (!isset($week_offset)) $week_offset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
if (!isset($week_dates) || !isset($week_range_str)) {
    $monday = new DateTime();
    $monday->setISODate((int)date('o'), (int)date('W') + $week_offset);
    if ($monday->format('N') != 1) $monday->modify('last monday');

    $friday = clone $monday;
    $friday->modify('+4 days');

    $week_range_str = $monday->format('d M') . ' - ' . $friday->format('d M ' . $monday->format('Y'));

    $week_dates = [];
    $tempDate = clone $monday;
    for ($i = 0; $i < 5; $i++) {
        $week_dates[] = $tempDate->format('Y-m-d');
        $tempDate->modify('+1 day');
    }
}

// Standard Periods
$periods = [
    1 => ['time' => '08:30 - 09:20'],
    2 => ['time' => '09:20 - 10:10'],
    3 => ['time' => '10:10 - 11:00'],
    4 => ['time' => '11:00 - 11:50'],
    'lunch' => ['time' => '11:50 - 12:40'],
    6 => ['time' => '12:40 - 13:30'],
    7 => ['time' => '13:30 - 14:20'],
    8 => ['time' => '14:20 - 15:10'],
    9 => ['time' => '15:10 - 16:00'],
];

function getDayName($dateStr)
{
    $days = ['Monday' => 'จันทร์', 'Tuesday' => 'อังคาร', 'Wednesday' => 'พุธ', 'Thursday' => 'พฤหัสบดี', 'Friday' => 'ศุกร์'];
    $d = new DateTime($dateStr);
    return $days[$d->format('l')] ?? $d->format('l');
}
?>
<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <?php require_once __DIR__ . '/../templates/sidebar.php'; ?>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <span class="font-bold text-pink-500 tracking-tight">Presentation Admin</span>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-[1600px] mx-auto"> <!-- Wide container for calendar -->

                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                            <span class="mr-2 text-pink-600">📅</span> จัดการตารางนำเสนอ
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">คลิกที่ช่องว่างเพื่อเพิ่มรอบ หรือคลิกที่ไอคอนแก้ไข/ลบ</p>
                    </div>

                    <div class="flex gap-2">
                        <!-- Batch Generate Button -->
                        <button onclick="openBatchModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 transition-all">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            สร้างหลายรอบ (Batch)
                        </button>
                    </div>
                </div>

                <!-- Calendar Navigation -->
                <div class="flex items-center justify-between mb-4 bg-white p-3 rounded-2xl shadow-sm border border-gray-100">
                    <a href="?week=<?php echo $week_offset - 1; ?>" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div class="text-center">
                        <span class="text-lg font-bold text-gray-800 tracking-tight"><?php echo $week_range_str; ?></span>
                    </div>
                    <a href="?week=<?php echo $week_offset + 1; ?>" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                <!-- Admin Calendar Grid -->
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-20 border-r border-gray-100 bg-slate-100">
                                        คาบ
                                    </th>
                                    <?php foreach ($week_dates as $date): ?>
                                        <th class="px-3 py-3 text-center w-64 border-r border-gray-100 last:border-r-0 min-w-[200px]">
                                            <div class="text-sm font-bold text-gray-900"><?php echo getDayName($date); ?></div>
                                            <div class="text-xs text-gray-400 font-normal"><?php echo date('d/m', strtotime($date)); ?></div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($periods as $pNum => $pInfo): ?>
                                    <?php if ($pNum === 'lunch'): ?>
                                        <tr class="bg-amber-50/50">
                                            <td colspan="6" class="px-3 py-1.5 text-center text-[10px] font-bold text-amber-600 tracking-widest uppercase border-y border-amber-100">
                                                พักเที่ยง (<?php echo $pInfo['time']; ?>)
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <!-- Period Label -->
                                            <td class="px-2 py-4 whitespace-nowrap text-center border-r border-gray-100 bg-gray-50/30 group relative">
                                                <div class="text-lg font-black text-slate-700"><?php echo $pNum; ?></div>
                                                <div class="text-[9px] text-gray-400"><?php echo $pInfo['time']; ?></div>
                                            </td>

                                            <!-- Days Cells -->
                                            <?php foreach ($week_dates as $date): ?>
                                                <?php
                                                $foundSlots = [];
                                                foreach ($slots as $slot) {
                                                    if (date('Y-m-d', strtotime($slot['start_time'])) == $date) {
                                                        $slotStart = date('H:i', strtotime($slot['start_time']));
                                                        $pStart = explode(' - ', $pInfo['time'])[0];
                                                        if (substr($slotStart, 0, 5) == substr($pStart, 0, 5)) {
                                                            $foundSlots[] = $slot;
                                                        }
                                                    }
                                                }

                                                // Prepare pre-filled data for "Add" action
                                                $pStart = explode(' - ', $pInfo['time'])[0];
                                                $pEnd = explode(' - ', $pInfo['time'])[1];
                                                $addParams = "{date: '$date', start: '$pStart', end: '$pEnd'}";
                                                ?>
                                                <td class="p-1 border-r border-gray-100 last:border-r-0 align-top h-28 relative hover:bg-gray-50 transition-colors group/cell">

                                                    <!-- Add Button Overlay (visible on hover) -->
                                                    <div onclick="openSlotModal(<?php echo $addParams; ?>)" class="absolute top-1 right-1 opacity-0 group-hover/cell:opacity-100 transition-opacity cursor-pointer text-gray-300 hover:text-pink-500 z-10" title="เพิ่มรอบเวลานี้">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                    </div>

                                                    <?php if (count($foundSlots) > 0): ?>
                                                        <div class="space-y-1 relative z-20">
                                                            <?php foreach ($foundSlots as $slot):
                                                                $isFull = $slot['booked_count'] >= $slot['max_groups'];
                                                                $statusColor = $isFull ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100';
                                                                if ($slot['booked_count'] > 0 && !$isFull) $statusColor = 'bg-amber-50 text-amber-700 border-amber-100';

                                                                $slotData = htmlspecialchars(json_encode($slot), ENT_QUOTES, 'UTF-8');
                                                            ?>
                                                                <div class="rounded-lg border px-2 py-1.5 <?php echo $statusColor; ?> text-xs group/slot">

                                                                    <!-- Slot Header -->
                                                                    <div class="flex justify-between items-start">
                                                                        <span class="font-bold truncate" title="<?php echo htmlspecialchars($slot['location']); ?>">
                                                                            📍 <?php echo htmlspecialchars($slot['location']); ?>
                                                                        </span>

                                                                        <!-- Tools -->
                                                                        <div class="flex items-center space-x-1">
                                                                            <!-- Edit Btn -->
                                                                            <button onclick='openEditSlot(<?php echo $slotData; ?>)' class="text-xs text-blue-400 hover:text-blue-600">
                                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                                                </svg>
                                                                            </button>
                                                                            <!-- Delete Btn -->
                                                                            <button onclick="deleteSlot(<?php echo $slot['id']; ?>)" class="text-xs text-red-400 hover:text-red-600">
                                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Booked Count -->
                                                                    <div class="mt-1 flex items-center justify-between">
                                                                        <span class="font-mono text-[10px] opacity-75">
                                                                            <?php echo $slot['booked_count']; ?>/<?php echo $slot['max_groups']; ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- Empty State Click Target -->
                                                        <div onclick="openSlotModal(<?php echo $addParams; ?>)" class="w-full h-full flex items-center justify-center cursor-pointer text-gray-100 hover:text-gray-300">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
    // --- SINGLE ADD/EDIT SLOT ---
    async function openSlotModal(prefill = {}, isEdit = false) {
        let title = isEdit ? 'แก้ไขรอบนำเสนอ' : 'เพิ่มรอบนำเสนอ';
        let confirmBtn = isEdit ? 'บันทึกการแก้ไข' : 'บันทึก';

        const {
            value: formValues
        } = await Swal.fire({
            title: title,
            html: '<div class="text-left font-prompt">' +
                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">วันที่</label>' +
                `<input id="swal-date" type="date" value="${prefill.date || ''}" min="<?php echo date('Y-m-d'); ?>" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none">` +

                '<div class="grid grid-cols-2 gap-4 mb-4">' +
                '<div>' +
                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">เวลาเริ่ม</label>' +
                `<input id="swal-start" type="time" value="${prefill.start || ''}" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none">` +
                '</div>' +
                '<div>' +
                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">เวลาสิ้นสุด</label>' +
                `<input id="swal-end" type="time" value="${prefill.end || ''}" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 outline-none">` +
                '</div>' +
                '</div>' +

                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">สถานที่ / Link Online</label>' +
                `<input id="swal-location" type="text" value="${prefill.location || 'Meeting Room'}" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none">` +

                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">จำนวนกลุ่มสูงสุด</label>' +
                `<input id="swal-max" type="number" min="1" value="${prefill.max || 1}" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none">` +
                '</div>',
            showCancelButton: true,
            confirmButtonText: confirmBtn,
            confirmButtonColor: '#ec4899',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                popup: 'rounded-3xl p-8'
            },
            preConfirm: () => {
                const date = document.getElementById('swal-date').value;
                const start = document.getElementById('swal-start').value;
                const end = document.getElementById('swal-end').value;
                const location = document.getElementById('swal-location').value;
                const max = document.getElementById('swal-max').value;

                if (!date || !start || !end || !location || !max) {
                    Swal.showValidationMessage('กรุณากรอกข้อมูลให้ครบถ้วน');
                    return false;
                }

                return {
                    id: prefill.id,
                    date,
                    start,
                    end,
                    location,
                    max
                };
            }
        });

        if (formValues) {
            saveSlot(formValues, isEdit);
        }
    }

    function openEditSlot(slot) {
        // Extract date and time
        const dateObj = new Date(slot.start_time);
        const date = slot.start_time.split(' ')[0];
        const start = slot.start_time.split(' ')[1];
        const end = slot.end_time.split(' ')[1];

        openSlotModal({
            id: slot.id,
            date: date,
            start: start,
            end: end,
            location: slot.location,
            max: slot.max_groups
        }, true);
    }

    // --- BATCH ADD SLOT ---
    async function openBatchModal() {
        const {
            value: formValues
        } = await Swal.fire({
            title: 'สร้างรอบแบบ Batch',
            html: '<div class="text-left font-prompt">' +
                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">เลือกวันที่ (ได้หลายวัน)</label>' +
                '<input id="batch-dates" type="text" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none" placeholder="เลือกวันที่...">' +

                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">เลือกคาบที่ต้องการสร้าง</label>' +
                '<div class="grid grid-cols-4 gap-2 mb-4 max-h-40 overflow-y-auto">' +
                <?php foreach ($periods as $pNum => $pInfo): if ($pNum == 'lunch') continue; ?> `<label class="flex items-center space-x-2 p-2 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" class="batch-period rounded text-pink-600 focus:ring-pink-500" value="<?php echo $pNum; ?>" data-time="<?php echo $pInfo['time']; ?>">
                    <span class="text-sm font-bold text-gray-700">คาบ <?php echo $pNum; ?></span>
                </label>` +
                <?php endforeach; ?> '</div>' +

                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">สถานที่</label>' +
                '<input id="batch-location" type="text" value="Meeting Room" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none">' +

                '<label class="block text-xs font-bold text-gray-400 uppercase mb-1">จำนวนกลุ่มต่อรอบ</label>' +
                '<input id="batch-max" type="number" value="1" min="1" class="w-full p-3 border border-gray-200 rounded-xl mb-4 focus:ring-2 focus:ring-pink-500 outline-none">' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'สร้างรอบ',
            confirmButtonColor: '#ec4899',
            cancelButtonText: 'ยกเลิก',
            customClass: {
                popup: 'rounded-3xl p-8 max-w-2xl'
            },
            didOpen: () => {
                flatpickr("#batch-dates", {
                    mode: "multiple",
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    locale: {
                        firstDayOfWeek: 1
                    }
                });
            },
            preConfirm: () => {
                const datesStr = document.getElementById('batch-dates').value;
                const location = document.getElementById('batch-location').value;
                const max = document.getElementById('batch-max').value;
                const checkboxes = document.querySelectorAll('.batch-period:checked');

                if (!datesStr || checkboxes.length === 0) {
                    Swal.showValidationMessage('กรุณาเลือกวันที่และอย่างน้อย 1 คาบ');
                    return false;
                }

                let periods = [];
                checkboxes.forEach(cb => {
                    periods.push({
                        time: cb.dataset.time
                    });
                });

                return {
                    dates: datesStr.split(', '),
                    location,
                    max,
                    periods
                };
            }
        });

        if (formValues) {
            Swal.fire({
                title: 'กำลังสร้าง...',
                didOpen: () => Swal.showLoading()
            });

            let successCount = 0;
            for (let date of formValues.dates) {
                for (let p of formValues.periods) {
                    const [start, end] = p.time.split(' - ');
                    await $.ajax({
                        url: '<?php echo BASE_URL; ?>/presentation/add_slot',
                        type: 'POST',
                        data: {
                            csrf_token: '<?php echo $_SESSION['csrf_token']; ?>',
                            academic_year: '<?php echo $current_year['year']; ?>',
                            start_time: date + ' ' + start,
                            end_time: date + ' ' + end,
                            location: formValues.location,
                            max_groups: formValues.max
                        }
                    });
                    successCount++;
                }
            }
            Swal.fire('สำเร็จ', `สร้าง ${successCount} รอบเรียบร้อย`, 'success').then(() => location.reload());
        }
    }

    function saveSlot(data, isEdit) {
        const formData = new FormData();
        formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        formData.append('academic_year', '<?php echo $current_year['year']; ?>');
        formData.append('start_time', data.date + ' ' + data.start);
        formData.append('end_time', data.date + ' ' + data.end);
        formData.append('location', data.location);
        formData.append('max_groups', data.max);

        let url = '<?php echo BASE_URL; ?>/presentation/add_slot';
        if (isEdit) {
            url = '<?php echo BASE_URL; ?>/presentation/update_slot';
            formData.append('id', data.id);
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกสำเร็จ',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', res.message, 'error');
                }
            }
        });
    }

    function deleteSlot(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลการจองในรอบนี้จะถูกลบด้วย",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?php echo BASE_URL; ?>/presentation/delete_slot', {
                    id: id,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                }, function(response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ',
                            toast: true,
                            position: 'top-end',
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