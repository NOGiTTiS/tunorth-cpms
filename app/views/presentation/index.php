<?php

/** @var array $data */
if (isset($data)) extract($data);

// Fallback if controller didn't pass these (e.g. file not updated on server)
if (!isset($week_offset)) $week_offset = 0;
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

// Standard School Periods
$periods = [
    1 => ['time' => '08:30 - 09:20'],
    2 => ['time' => '09:20 - 10:10'],
    3 => ['time' => '10:10 - 11:00'],
    4 => ['time' => '11:00 - 11:50'],
    // Lunch Break
    'lunch' => ['time' => '11:50 - 12:40'],
    6 => ['time' => '12:40 - 13:30'],
    7 => ['time' => '13:30 - 14:20'],
    8 => ['time' => '14:20 - 15:10'],
    9 => ['time' => '15:10 - 16:00'],
];

// Month names in Thai
$thaiMonths = [
    '01' => 'มกราคม',
    '02' => 'กุมภาพันธ์',
    '03' => 'มีนาคม',
    '04' => 'เมษายน',
    '05' => 'พฤษภาคม',
    '06' => 'มิถุนายน',
    '07' => 'กรกฎาคม',
    '08' => 'สิงหาคม',
    '09' => 'กันยายน',
    '10' => 'ตุลาคม',
    '11' => 'พฤศจิกายน',
    '12' => 'ธันวาคม'
];

function thaiDate($dateStr, $thaiMonths)
{
    if (!$dateStr) return '';
    $d = new DateTime($dateStr);
    $year = $d->format('Y') + 543;
    $month = $thaiMonths[$d->format('m')];
    $day = $d->format('j');
    return "$day $month $year";
}

function getDayName($dateStr)
{
    $days = ['Monday' => 'จันทร์', 'Tuesday' => 'อังคาร', 'Wednesday' => 'พุธ', 'Thursday' => 'พฤหัสบดี', 'Friday' => 'ศุกร์'];
    $d = new DateTime($dateStr);
    return $days[$d->format('l')] ?? $d->format('l');
}
?>
<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="flex min-h-screen bg-gray-50 font-prompt">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../templates/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Mobile Header -->
        <header class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-pink-500 tracking-tight">Presentation Booking</span>
            </div>
            <button onclick="toggleSidebar()" class="p-2.5 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors shadow-sm border border-slate-700/50">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </header>

        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">

                <!-- Helper: No group? -->
                <?php if (!$my_group): ?>
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r shadow-sm mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-amber-700">
                                    คุณยังไม่มีกลุ่มโครงงาน กรุณาสร้างหรือเข้าร่วมกลุ่มก่อนทำการจอง
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($my_booking): ?>
                    <div class="bg-white overflow-hidden shadow-lg rounded-2xl mb-8 border border-green-100">
                        <div class="bg-green-500 px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-bold text-white flex items-center justify-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                กลุ่มของคุณจองเวลาเรียบร้อยแล้ว
                            </h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6 text-center">
                            <div class="text-3xl font-bold text-gray-800 mb-2">
                                <?php echo thaiDate($my_booking['start_time'], $thaiMonths); ?>
                            </div>
                            <div class="text-xl text-pink-600 font-bold mb-4">
                                <?php echo date('H:i', strtotime($my_booking['start_time'])) . ' - ' . date('H:i', strtotime($my_booking['end_time'])); ?>
                            </div>
                            <p class="text-gray-500 mb-6 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <?php echo htmlspecialchars($my_booking['location']); ?>
                            </p>
                            <button onclick="cancelBooking(<?php echo $my_booking['id']; ?>)" class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                ยกเลิกการจอง
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Calendar Navigation -->
                <div class="flex items-center justify-between mb-6">
                    <a href="?week=<?php echo $week_offset - 1; ?>" class="flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 text-gray-700 font-bold">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        สัปดาห์ก่อนหน้า
                    </a>
                    <div class="text-center">
                        <h2 class="text-xl font-bold text-gray-800">ตารางการนำเสนอ</h2>
                        <p class="text-sm text-pink-600 font-medium"><?php echo $week_range_str; ?></p>
                    </div>
                    <a href="?week=<?php echo $week_offset + 1; ?>" class="flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 text-gray-700 font-bold">
                        สัปดาห์ถัดไป
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                <!-- Calendar Grid -->
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-24 border-r border-gray-100 bg-slate-100">
                                        คาบ / เวลา
                                    </th>
                                    <?php foreach ($week_dates as $date): ?>
                                        <th class="px-3 py-3 text-center w-48 border-r border-gray-100 last:border-r-0">
                                            <div class="text-sm font-bold text-gray-900"><?php echo getDayName($date); ?></div>
                                            <div class="text-xs text-gray-500 font-normal"><?php echo date('d/m', strtotime($date)); ?></div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php foreach ($periods as $pNum => $pInfo): ?>
                                    <?php if ($pNum === 'lunch'): ?>
                                        <tr class="bg-amber-50">
                                            <td colspan="6" class="px-3 py-2 text-center text-xs font-bold text-amber-600 tracking-widest uppercase">
                                                พักเที่ยง (<?php echo $pInfo['time']; ?>)
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <!-- Period Info -->
                                            <td class="px-2 py-4 whitespace-nowrap text-center border-r border-gray-100 bg-gray-50/50">
                                                <div class="text-lg font-black text-slate-700"><?php echo $pNum; ?></div>
                                                <div class="text-[10px] text-gray-400 font-medium"><?php echo $pInfo['time']; ?></div>
                                            </td>

                                            <!-- Days -->
                                            <?php foreach ($week_dates as $date): ?>
                                                <?php
                                                // Find overlap slot
                                                $foundSlot = null;
                                                foreach ($slots as $slot) {
                                                    // Check Date
                                                    if (date('Y-m-d', strtotime($slot['start_time'])) == $date) {
                                                        // Check Time Overlap
                                                        // A slot overlaps if it starts exactly at period start time (simplification for MVP as per user requirement)
                                                        // The period starts at e.g. 08:30. Let's extract H:i
                                                        $slotStart = date('H:i', strtotime($slot['start_time']));
                                                        $pStart = explode(' - ', $pInfo['time'])[0];

                                                        // Relaxed matching: match first 5 chars
                                                        if (substr($slotStart, 0, 5) == substr($pStart, 0, 5)) {
                                                            $foundSlot = $slot;
                                                            break;
                                                        }
                                                    }
                                                }
                                                ?>
                                                <td class="p-2 border-r border-gray-100 last:border-r-0 h-24 align-top transition-all hover:bg-gray-50">
                                                    <?php if ($foundSlot): ?>
                                                        <?php
                                                        $isFull = $foundSlot['booked_count'] >= $foundSlot['max_groups'];
                                                        $isBookedByMe = $my_booking && $my_booking['slot_id'] == $foundSlot['id'];
                                                        ?>
                                                        <div class="h-full flex flex-col justify-between">
                                                            <div>
                                                                <div class="text-[10px] font-bold text-gray-400 mb-1">
                                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($foundSlot['location']); ?>
                                                                </div>

                                                                <?php if ($isBookedByMe): ?>
                                                                    <div class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-lg font-bold border border-green-200 mb-1">
                                                                        กลุ่มของคุณ
                                                                    </div>
                                                                <?php endif; ?>

                                                                <div class="text-xs text-slate-600 font-medium">
                                                                    <?php echo $foundSlot['booked_count']; ?>/<?php echo $foundSlot['max_groups']; ?>
                                                                </div>
                                                            </div>

                                                            <div class="mt-2">
                                                                <?php if ($isBookedByMe): ?>
                                                                    <button disabled class="w-full py-1 bg-gray-100 text-gray-400 text-xs rounded cursor-not-allowed">จองแล้ว</button>
                                                                <?php elseif ($my_booking): ?>
                                                                    <button disabled class="w-full py-1 bg-gray-50 text-gray-300 text-xs rounded cursor-not-allowed">เลือกแล้ว</button>
                                                                <?php elseif ($isFull): ?>
                                                                    <button disabled class="w-full py-1 bg-red-50 text-red-300 text-xs rounded cursor-not-allowed">เต็ม</button>
                                                                <?php else: ?>
                                                                    <button onclick="bookSlot(<?php echo $foundSlot['id']; ?>)" class="w-full py-1 bg-pink-600 text-white text-xs rounded hover:bg-pink-700 shadow-sm shadow-pink-200">
                                                                        จอง
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- Empty Slot -->
                                                        <div class="h-full flex items-center justify-center text-gray-300 text-xs">
                                                            -
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
    function bookSlot(slotId) {
        <?php if (!$my_group): ?>
            Swal.fire('แจ้งเตือน', 'คุณต้องมีกลุ่มก่อนจึงจะจองได้', 'warning');
            return;
        <?php endif; ?>

        Swal.fire({
            title: 'ยืนยันการจอง?',
            text: "คุณต้องการจองรอบเวลานี้ใช่หรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#ec4899',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?php echo BASE_URL; ?>/presentation/book', {
                    slot_id: slotId,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                }, function(response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire('จองสำเร็จ', '', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ขออภัย', res.message, 'error');
                    }
                });
            }
        });
    }

    function cancelBooking(bookingId) {
        Swal.fire({
            title: 'ยืนยันการยกเลิก?',
            text: "คุณต้องการยกเลิกการจองนี้ใช่หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยกเลิกการจอง',
            cancelButtonText: 'ไม่',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?php echo BASE_URL; ?>/presentation/cancel', {
                    booking_id: bookingId,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                }, function(response) {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire('ยกเลิกแล้ว', '', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>