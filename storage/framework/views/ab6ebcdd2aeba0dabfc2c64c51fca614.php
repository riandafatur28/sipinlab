<?php $__env->startSection('title', 'Admin Dashboard - Polije'); ?>

<?php
use Carbon\Carbon;
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">

    <!-- ======================================================================== -->
    <!-- ✅ REAL-TIME CLOCK HEADER -->
    <!-- ======================================================================== -->
    <div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-medium text-blue-100 mb-1">🕐 Waktu Sekarang</h2>
                <div class="text-3xl md:text-4xl font-bold" id="realtime-clock">00:00:00</div>
                <div class="text-blue-200 mt-1">
                    <?php echo e($realtimeDayName ?? Carbon::now()->isoFormat('dddd')); ?>, <?php echo e(($currentTime ?? Carbon::now())->isoFormat('D MMMM Y')); ?>

                </div>
            </div>
            <div class="hidden md:block">
                <svg class="w-20 h-20 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ STATS CARDS: Users + Labs + Bookings Real-time -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 mb-8">
        <!-- Users Stats -->
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
            <p class="text-xs text-gray-500">Total Users</p>
            <p class="text-lg font-bold text-gray-800"><?php echo e($stats['total_users'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
            <p class="text-xs text-gray-500">Mahasiswa</p>
            <p class="text-lg font-bold text-blue-600"><?php echo e($stats['mahasiswa'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
            <p class="text-xs text-gray-500">Dosen</p>
            <p class="text-lg font-bold text-green-600"><?php echo e($stats['dosen'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
            <p class="text-xs text-gray-500">Staff</p>
            <p class="text-lg font-bold text-purple-600"><?php echo e(($stats['ketua_lab'] ?? 0) + ($stats['teknisi'] ?? 0) + ($stats['admin'] ?? 0)); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
            <p class="text-xs text-gray-500">🏢 Total Lab</p>
            <p class="text-lg font-bold text-indigo-600"><?php echo e($stats['total_labs'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow">
            <p class="text-xs text-gray-500">📚 Mata Kuliah</p>
            <p class="text-lg font-bold text-teal-600"><?php echo e($stats['active_courses'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute right-0 top-0 h-full w-1 bg-green-500 opacity-20"></div>
            <p class="text-xs text-gray-500">📅 Booking Hari Ini</p>
            <p class="text-lg font-bold text-green-600" id="bookings-today"><?php echo e($stats['bookings_today'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="absolute right-0 top-0 h-full w-1 bg-blue-500 opacity-20"></div>
            <p class="text-xs text-gray-500">📊 Booking Bulan Ini</p>
            <p class="text-lg font-bold text-blue-600" id="bookings-month"><?php echo e($stats['bookings_this_month'] ?? 0); ?></p>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ SECTION GRAFIK ANALYTICS (4 Charts) -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- 📊 Chart 1: Lab Paling Sering Dipinjam -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">🏢</span>
                Lab Paling Sering Dipinjam
                <span class="text-xs text-gray-500 font-normal ml-2">(30 hari terakhir)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartLabUsage"></canvas>
            </div>
        </div>

        <!-- 📊 Chart 2: Hari Paling Banyak Dipilih -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">📅</span>
                Hari Paling Banyak Dipilih
                <span class="text-xs text-gray-500 font-normal ml-2">(Semua booking)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartDayDistribution"></canvas>
            </div>
        </div>

        <!-- 📊 Chart 3: Jenis Kegiatan Peminjaman -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">📋</span>
                Jenis Kegiatan Peminjaman
                <span class="text-xs text-gray-500 font-normal ml-2">(3 bulan terakhir)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartActivityType"></canvas>
            </div>
        </div>

        <!-- 📊 Chart 4: Top Peminjam -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">👤</span>
                Top Peminjam
                <span class="text-xs text-gray-500 font-normal ml-2">(3 bulan terakhir)</span>
            </h3>
            <div class="h-64">
                <canvas id="chartTopBorrowers"></canvas>
            </div>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ FILTER: Calendar + Day + Lab (Opsional, untuk ganti tanggal) -->
    <!-- ======================================================================== -->
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="flex flex-wrap items-center gap-4" id="filterForm">

            <!-- Calendar Date Picker -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">📅 Lihat Tanggal:</label>
                <input type="date"
                       id="datePicker"
                       name="date"
                       value="<?php echo e($scheduleDate ?? date('Y-m-d')); ?>"
                       min="<?php echo e(date('Y-m-d', strtotime('-30 days'))); ?>"
                       max="<?php echo e(date('Y-m-d', strtotime('+90 days'))); ?>"
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium text-sm"
                       onchange="onDateChange(this.value)">
            </div>

            <!-- Day Selector (Auto-sync) -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Hari:</label>
                <select name="day" id="daySelect" onchange="onDayChange(this.value)"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium">
                    <?php $__currentLoopData = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($day); ?>" <?php echo e(($scheduleDayName ?? '') == $day ? 'selected' : ''); ?>><?php echo e($day); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Lab Selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">🏢 Filter Lab:</label>
                <select name="lab" onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white font-medium">
                    <option value="">Semua Lab</option>
                    <?php $__currentLoopData = ($labs ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $labName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($labName); ?>" <?php echo e(request('lab') == $labName ? 'selected' : ''); ?>>
                            <?php echo e($labName); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Info: Auto-load today -->
            <span class="text-xs text-gray-500 ml-2 flex items-center gap-1">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                Menampilkan: <strong class="text-blue-600"><?php echo e(request('lab') ?: 'Semua Lab'); ?></strong>
                • <strong class="text-green-600">Hari Ini (Auto-load)</strong>
            </span>

            <!-- Reset Filter -->
            <?php if(request('date') || request('day') || request('lab')): ?>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-sm text-gray-500 hover:text-gray-700 underline">
                🔄 Reset
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ LEGEND / KETERANGAN STATUS -->
    <!-- ======================================================================== -->
    <div class="mb-6 flex flex-wrap gap-2">
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-xs text-gray-700">Tersedia</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
            <span class="text-xs text-gray-700">Proses</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <span class="text-xs text-gray-700">Terisi</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="w-3 h-3 rounded-full bg-gray-400"></div>
            <span class="text-xs text-gray-700">Selesai</span>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-lg shadow-sm border border-orange-200">
            <div class="w-3 h-3 rounded-full bg-orange-500"></div>
            <span class="text-xs text-gray-700">Pending</span>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ LOADING INDICATOR -->
    <!-- ======================================================================== -->
    <div id="loadingIndicator" class="hidden mb-6 text-center py-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memuat jadwal...</span>
        </div>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ TABLES PER LAB (Schedule Grid) - AUTO-LOAD HARI INI -->
    <!-- ======================================================================== -->
    <?php
        $displayLabs = request('lab') ? [request('lab')] : ($labs ?? []);
    ?>

    <div id="scheduleContainer">
        <?php $__currentLoopData = $displayLabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(in_array($lab, ($labs ?? []))): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6" data-lab="<?php echo e($lab); ?>">
                <!-- Header Lab -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo e($lab); ?></h2>
                    <span class="text-lg font-extrabold text-white bg-blue-600 px-4 py-1.5 rounded-lg shadow-sm">
                        <?php echo e($scheduleDayName ?? Carbon::now()->isoFormat('dddd')); ?>

                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sesi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="scheduleBody-<?php echo e(Str::slug($lab)); ?>">
                            <?php $__currentLoopData = ($scheduleData[$lab] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition-colors <?php echo e(($item['is_break'] ?? false) ? 'bg-gray-100' : ''); ?>">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?php echo e($item['no'] ?? $loop->iteration); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo e($item['session'] ?? '-'); ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-mono">
                                    <?php echo e($item['start'] ?? '-'); ?> - <?php echo e($item['end'] ?? '-'); ?>

                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if(($item['is_break'] ?? false)): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-300 text-gray-700">Istirahat</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full cursor-pointer transition-transform hover:scale-105
                                            <?php if(($item['status_color'] ?? '') === 'green'): ?> bg-green-100 text-green-800 border border-green-300
                                            <?php elseif(($item['status_color'] ?? '') === 'yellow'): ?> bg-yellow-100 text-yellow-800 border border-yellow-300
                                            <?php elseif(($item['status_color'] ?? '') === 'red'): ?> bg-red-100 text-red-800 border border-red-300
                                            <?php elseif(($item['status_color'] ?? '') === 'orange'): ?> bg-orange-100 text-orange-800 border border-orange-300
                                            <?php elseif(($item['status_color'] ?? '') === 'blue'): ?> bg-blue-100 text-blue-800 border border-blue-300
                                            <?php elseif(($item['status_color'] ?? '') === 'indigo'): ?> bg-indigo-100 text-indigo-800 border border-indigo-300
                                            <?php else: ?> bg-gray-400 text-gray-100 <?php endif; ?>"
                                            onclick="showStatusInfo('<?php echo e($lab); ?>', '<?php echo e($item['session'] ?? ''); ?>', '<?php echo e($item['status_label'] ?? ''); ?>', '<?php echo e($item['status_color'] ?? ''); ?>', '<?php echo e(addslashes($item['booking_info'] ?? '')); ?>')">
                                            <?php echo e($item['status_label'] ?? '-'); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <?php if(!($item['is_break'] ?? false)): ?>
                                        <?php if(($item['status'] ?? '') === 'tersedia'): ?>
                                            <button onclick="openBookingModal('<?php echo e($lab); ?>', '<?php echo e($item['session'] ?? ''); ?>', '<?php echo e($item['start'] ?? ''); ?>', '<?php echo e($item['end'] ?? ''); ?>', '<?php echo e($scheduleDate ?? date('Y-m-d')); ?>')"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm">
                                                📅 Booking
                                            </button>
                                        <?php elseif(in_array(($item['status'] ?? ''), ['pending', 'approved_dosen', 'approved_teknisi'])): ?>
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs text-gray-500"><?php echo e($item['booking_info'] ?? ''); ?></span>
                                                <span class="text-xs text-gray-400">Menunggu approval...</span>
                                            </div>
                                        <?php elseif(($item['status'] ?? '') === 'proses'): ?>
                                            <span class="text-xs text-yellow-600 font-medium flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"></path>
                                                </svg>
                                                Proses
                                            </span>
                                        <?php elseif(($item['status'] ?? '') === 'terisi'): ?>
                                            <span class="text-xs text-red-600 font-medium flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"></path>
                                                </svg>
                                                Terisi
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">Selesai</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if(empty($displayLabs) || (request('lab') && !in_array(request('lab'), ($labs ?? [])))): ?>
        <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-200">
            <p class="text-gray-500">Laboratorium tidak ditemukan.</p>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:underline mt-2 inline-block">Reset filter</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ QUICK ACTIONS -->
    <!-- ======================================================================== -->
    

</div>

<!-- ======================================================================== -->
<!-- ✅ MODALS (Status & Booking) -->
<!-- ======================================================================== -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">📋 Informasi Status</h3>
            <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="space-y-3">
            <div><p class="text-sm text-gray-500">Laboratorium</p><p class="font-semibold text-gray-800" id="modalLab">-</p></div>
            <div><p class="text-sm text-gray-500">Sesi</p><p class="font-semibold text-gray-800" id="modalSession">-</p></div>
            <div><p class="text-sm text-gray-500">Status</p><p class="font-semibold" id="modalStatus">-</p></div>
            <div id="modalInfo" class="text-sm text-gray-600 italic hidden"></div>
            <div id="modalMessage" class="mt-4 p-3 rounded-lg bg-blue-50 text-sm text-blue-800"></div>
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeStatusModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">Tutup</button>
        </div>
    </div>
</div>

<div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">📅 Booking Laboratorium</h3>
            <button onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="bookingForm" onsubmit="submitBooking(event)">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="bookingLab" name="lab_name">
            <input type="hidden" id="bookingSession" name="session">
            <input type="hidden" id="bookingStartTime" name="start_time">
            <input type="hidden" id="bookingEndTime" name="end_time">
            <div class="space-y-4">
                <div><p class="text-sm text-gray-500">Laboratorium</p><p class="font-semibold text-gray-800" id="formLab">-</p></div>
                <div><p class="text-sm text-gray-500">Sesi</p><p class="font-semibold text-gray-800" id="formSession">-</p></div>
                <div><p class="text-sm text-gray-500">Waktu</p><p class="font-semibold text-gray-800" id="formTime">-</p></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="booking_date" id="bookingDate" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keperluan</label>
                    <textarea name="purpose" rows="3" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="Jelaskan keperluan penggunaan lab..."></textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeBookingModal()" class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">✅ Konfirmasi Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================== -->
<!-- ✅ JAVASCRIPT: Charts + Real-time Updates + Auto-load Schedule -->
<!-- ======================================================================== -->
<?php $__env->startPush('scripts'); ?>
<!-- ✅ Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>

<script>
// ========================================================================
// COLOR PALETTE FOR CHARTS
// ========================================================================
const colors = {
    blue: '#3b82f6', indigo: '#6366f1', purple: '#8b5cf6',
    green: '#22c55e', yellow: '#eab308', orange: '#f97316',
    red: '#ef4444', gray: '#6b7280', teal: '#14b8a6'
};

// ========================================================================
// 📊 CHART 1: Lab Usage (Bar Chart)
// ========================================================================
const ctxLab = document.getElementById('chartLabUsage');
if (ctxLab) {
    new Chart(ctxLab, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabLabels ?? [], 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: <?php echo json_encode($chartLabData ?? [], 15, 512) ?>,
                backgroundColor: [colors.blue, colors.indigo, colors.purple, colors.green, colors.orange],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => `${ctx.parsed} booking` } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
}

// ========================================================================
// 📊 CHART 2: Day Distribution (Pie Chart)
// ========================================================================
const ctxDay = document.getElementById('chartDayDistribution');
if (ctxDay) {
    new Chart(ctxDay, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($chartDayLabels ?? [], 15, 512) ?>,
            datasets: [{
                data: <?php echo json_encode($chartDayData ?? [], 15, 512) ?>,
                backgroundColor: [colors.blue, colors.green, colors.yellow, colors.orange, colors.red, colors.purple, colors.indigo],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed} booking` } }
            }
        }
    });
}

// ========================================================================
// 📊 CHART 3: Activity Type (Doughnut Chart)
// ========================================================================
const ctxActivity = document.getElementById('chartActivityType');
if (ctxActivity) {
    new Chart(ctxActivity, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($chartActivityLabels ?? [], 15, 512) ?>,
            datasets: [{
                data: <?php echo json_encode($chartActivityData ?? [], 15, 512) ?>,
                backgroundColor: [colors.blue, colors.green, colors.purple, colors.orange, colors.indigo, colors.yellow],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.parsed} booking` } }
            }
        }
    });
}

// ========================================================================
// 📊 CHART 4: Top Borrowers (Horizontal Bar)
// ========================================================================
const ctxBorrowers = document.getElementById('chartTopBorrowers');
if (ctxBorrowers) {
    new Chart(ctxBorrowers, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartBorrowerLabels ?? [], 15, 512) ?>,
            datasets: [{
                label: 'Jumlah Booking',
                data: <?php echo json_encode($chartBorrowerData ?? [], 15, 512) ?>,
                backgroundColor: <?php echo json_encode($chartBorrowerRoles ?? [], 15, 512) ?>.map(role =>
                    role === 'mahasiswa' ? colors.blue :
                    role === 'dosen' ? colors.green :
                    role === 'ketua_lab' ? colors.purple :
                    role === 'teknisi' ? colors.orange : colors.gray
                ),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { grid: { display: false } }
            }
        }
    });
}

// ========================================================================
// ⏰ REAL-TIME CLOCK
// ========================================================================
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateClock, 1000);
updateClock();

// ========================================================================
// ✅ AUTO-REFRESH BOOKING STATS (Real-time per 30 detik)
// ========================================================================
async function fetchBookingStats() {
    try {
        const response = await fetch("<?php echo e(route('admin.dashboard')); ?>?stats_only=1", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (response.ok) {
            const data = await response.json();
            if (data.bookings_today !== undefined) {
                document.getElementById('bookings-today').textContent = data.bookings_today;
                document.getElementById('bookings-month').textContent = data.bookings_this_month;
            }
        }
    } catch (e) {
        // Silent fail - tidak ganggu UX
    }
}
// Refresh stats setiap 30 detik
setInterval(fetchBookingStats, 30000);

// ========================================================================
// 🗓️ DAY MAPPING FOR JAVASCRIPT
// ========================================================================
const jsDayIndexMap = {
    'Minggu': 0, 'Senin': 1, 'Selasa': 2, 'Rabu': 3,
    'Kamis': 4, 'Jumat': 5, 'Sabtu': 6
};
const jSDayNamesArray = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// ========================================================================
// 🔄 DATE/DAY SYNC FUNCTIONS
// ========================================================================
function onDateChange(dateValue) {
    if (!dateValue) return;
    const date = new Date(dateValue + 'T00:00:00');
    const jsIndex = date.getDay();
    const dayName = jSDayNamesArray[jsIndex];
    document.getElementById('daySelect').value = dayName;
    document.getElementById('filterForm').submit();
}

function onDayChange(dayName) {
    const today = new Date();
    const targetDayNum = jsDayIndexMap[dayName];
    if (targetDayNum === undefined) return;
    const todayDayNum = today.getDay();
    let diff = targetDayNum - todayDayNum;
    if (diff < 0) diff += 7;
    const targetDate = new Date(today);
    targetDate.setDate(today.getDate() + diff);
    const yyyy = targetDate.getFullYear();
    const mm = String(targetDate.getMonth() + 1).padStart(2, '0');
    const dd = String(targetDate.getDate()).padStart(2, '0');
    document.getElementById('datePicker').value = `${yyyy}-${mm}-${dd}`;
    document.getElementById('filterForm').submit();
}

// ========================================================================
// 💬 MODAL FUNCTIONS
// ========================================================================
function showStatusInfo(lab, session, status, color, bookingInfo = '') {
    document.getElementById('modalLab').textContent = lab;
    document.getElementById('modalSession').textContent = session;
    document.getElementById('modalStatus').textContent = status;
    const infoEl = document.getElementById('modalInfo');
    const messageEl = document.getElementById('modalMessage');
    const statusEl = document.getElementById('modalStatus');
    if (bookingInfo && bookingInfo.trim() !== '') {
        infoEl.textContent = '📋 ' + bookingInfo;
        infoEl.classList.remove('hidden');
    } else {
        infoEl.classList.add('hidden');
    }
    let message = '';
    if (color === 'green') { message = '✅ Laboratorium tersedia untuk booking.'; statusEl.className = 'font-semibold text-green-600'; }
    else if (color === 'yellow') { message = '⏳ Sesi ini sedang berlangsung.'; statusEl.className = 'font-semibold text-yellow-600'; }
    else if (color === 'red') { message = '❌ Laboratorium sudah terisi pada sesi ini.'; statusEl.className = 'font-semibold text-red-600'; }
    else if (color === 'orange') { message = '⏳ Menunggu approval dosen.'; statusEl.className = 'font-semibold text-orange-600'; }
    else if (color === 'blue') { message = '✅ Disetujui dosen, menunggu teknisi.'; statusEl.className = 'font-semibold text-blue-600'; }
    else if (color === 'indigo') { message = '✅ Disetujui teknisi, menunggu approval Kalab.'; statusEl.className = 'font-semibold text-indigo-600'; }
    else { message = '⏹️ Sesi ini sudah selesai.'; statusEl.className = 'font-semibold text-gray-600'; }
    messageEl.textContent = message;
    document.getElementById('statusModal').classList.remove('hidden');
}
function closeStatusModal() { document.getElementById('statusModal').classList.add('hidden'); }

function openBookingModal(lab, session, start, end, date) {
    document.getElementById('bookingLab').value = lab;
    document.getElementById('bookingSession').value = session;
    document.getElementById('bookingStartTime').value = start;
    document.getElementById('bookingEndTime').value = end;
    document.getElementById('formLab').textContent = lab;
    document.getElementById('formSession').textContent = session;
    document.getElementById('formTime').textContent = start + ' - ' + end;
    document.getElementById('bookingDate').value = date || new Date().toISOString().split('T')[0];
    document.getElementById('bookingModal').classList.remove('hidden');
}
function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('bookingForm').reset();
}

// ========================================================================
// 📤 BOOKING SUBMISSION (AJAX)
// ========================================================================
async function submitBooking(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    try {
        const response = await fetch('<?php echo e(route("booking.store")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        const result = await response.json();
        if (result.success) {
            showToast('✅ ' + result.message, 'success');
            closeBookingModal();
            setTimeout(() => {
                window.location.reload();
                fetchBookingStats(); // Update stats setelah booking sukses
            }, 1000);
        } else {
            showToast('❌ ' + (result.message || 'Terjadi kesalahan'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('❌ Terjadi kesalahan saat melakukan booking', 'error');
    }
}

// ========================================================================
// 🔔 TOAST NOTIFICATION
// ========================================================================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white text-sm z-50 animate-fade-in ${
        type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ========================================================================
// 🎯 EVENT LISTENERS
// ========================================================================
document.getElementById('statusModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
document.getElementById('bookingModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeBookingModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStatusModal();
        closeBookingModal();
    }
});

// ========================================================================
// ✅ AUTO-LOAD: Jadwal hari ini langsung muncul tanpa user pilih tanggal
// ========================================================================
document.addEventListener('DOMContentLoaded', function() {
    // Jika tidak ada parameter date, set default ke hari ini
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('date')) {
        urlParams.set('date', new Date().toISOString().split('T')[0]);
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.replaceState({}, document.title, newUrl);
    }

    // Auto-refresh stats on load
    fetchBookingStats();

    // Refresh jika ada parameter refresh
    if (urlParams.has('refresh')) {
        urlParams.delete('refresh');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, document.title, newUrl);
        setTimeout(() => window.location.reload(), 500);
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>