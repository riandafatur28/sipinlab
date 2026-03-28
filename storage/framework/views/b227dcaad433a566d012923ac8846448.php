<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Jadwal Laboratorium - Polije TI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-lg sticky top-0 z-40">
        <div class="container mx-auto px-4 py-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                     <div class="flex items-center gap-2 text-white">
                    <img src="<?php echo e(asset('img/polije.png')); ?>"
                     alt="Logo Polije"
                     class="w-14 h-14 object-contain transition duration-300 hover:scale-110">

                </div>
                    <div>
                        <h1 class="text-xl font-bold leading-tight">POLITEKNIK NEGERI JEMBER</h1>
                        <p class="text-blue-100 text-base">JURUSAN TEKNOLOGI INFORMASI</p>
                    </div>
                </div>
                <a href="<?php echo e(route('login')); ?>" class="bg-white text-blue-600 px-6 py-2.5 rounded-lg font-semibold hover:bg-blue-50 transition-colors text-base">
                    🔐 Login
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">

        <!-- Title -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">📅 Jadwal Laboratorium</h2>
            <p class="text-gray-600 text-base">Pantau ketersediaan laboratorium Teknologi Informasi</p>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <form id="filterForm" method="GET" action="<?php echo e(route('public.schedule')); ?>" class="flex flex-wrap gap-4 items-end">

                <!-- Date Picker -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">📅 Tanggal</label>
                    <input type="date"
                           name="date"
                           id="dateFilter"
                           value="<?php echo e($date); ?>"
                           min="<?php echo e(date('Y-m-d')); ?>"
                           max="<?php echo e(date('Y-m-d', strtotime('+30 days'))); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Lab Filter -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Laboratorium</label>
                    <select name="lab"
                            id="labFilter"
                            onchange="autoSubmit()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Lab</option>
                        <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $labName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($labName); ?>" <?php echo e($selectedLab == $labName ? 'selected' : ''); ?>>
                                <?php echo e($labName); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Cari</label>
                    <input type="text"
                           name="search"
                           id="searchFilter"
                           value="<?php echo e($search); ?>"
                           placeholder="Kegiatan, mata kuliah..."
                           oninput="debounceSearch()"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg text-base focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Reset Button -->
                <?php if($search || $selectedLab || $date != date('Y-m-d')): ?>
                <div class="flex items-end">
                    <a href="<?php echo e(route('public.schedule')); ?>"
                       class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg text-base font-medium hover:bg-gray-300 transition-colors">
                        🔄 Reset
                    </a>
                </div>
                <?php endif; ?>
            </form>

            <!-- Loading Text -->
            <div id="loadingText" class="hidden mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200 inline-block">
                <span class="text-lg text-blue-700 font-semibold">
                    ⏳ Memuat jadwal...
                </span>
            </div>
        </div>

        <!-- Date Info -->
        <div class="mb-6 text-center">
            <p class="text-base font-semibold text-gray-700">
                <?php echo e(ucfirst($dayName)); ?>, <?php echo e(\Carbon\Carbon::parse($date)->isoFormat('DD MMMM Y')); ?>

            </p>
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap justify-center gap-6 mb-8 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-green-500"></span>
                <span class="text-gray-700 font-medium">Tersedia</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-yellow-500"></span>
                <span class="text-gray-700 font-medium">Dipinjam</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-red-500"></span>
                <span class="text-gray-700 font-medium">Kuliah</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full bg-gray-400"></span>
                <span class="text-gray-700 font-medium">Istirahat</span>
            </div>
        </div>

        <!-- Schedule Tables -->
        <?php $__currentLoopData = $scheduleData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $labName => $schedules): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <!-- Lab Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600">
                <h3 class="text-xl font-bold text-white"><?php echo e($labName); ?></h3>
            </div>

           <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-base">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">No</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Sesi</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Waktu</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 <?php echo e($item['is_break'] ? 'bg-gray-50' : ''); ?>">
                            <td class="px-6 py-4 text-gray-700 text-base"><?php echo e($item['no']); ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-800 text-base"><?php echo e($item['session']); ?></td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-base">
                                <?php echo e($item['start']); ?> - <?php echo e($item['end']); ?>

                            </td>
                            <td class="px-6 py-4">
                                <?php if($item['is_break']): ?>
                                    <span class="px-3 py-1.5 text-sm rounded-full bg-gray-200 text-gray-700 font-medium">Istirahat</span>
                                <?php else: ?>
                                    <span class="px-3 py-1.5 text-sm rounded-full font-semibold
                                        <?php if($item['status_color'] === 'green'): ?> bg-green-100 text-green-800
                                        <?php elseif($item['status_color'] === 'yellow'): ?> bg-yellow-100 text-yellow-800
                                        <?php elseif($item['status_color'] === 'red'): ?> bg-red-100 text-red-800
                                        <?php else: ?> bg-gray-300 text-gray-700 <?php endif; ?>">
                                        <?php echo e($item['status_label']); ?>

                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-base">
                                <?php if($item['is_break']): ?>
                                    <span class="text-gray-400">-</span>
                                <?php elseif($item['status'] === 'tersedia'): ?>
                                    <span class="text-gray-500 italic text-sm">
                                        Belum ada jadwal kuliah maupun peminjaman di sesi ini
                                    </span>
                                <?php else: ?>
                                    <?php echo e($item['booking_info'] ?? '-'); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-base">
                                Tidak ada data jadwal
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if(empty($scheduleData)): ?>
        <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
            <p class="text-gray-500 text-base mb-3">Tidak ada data jadwal untuk filter yang dipilih.</p>
            <a href="<?php echo e(route('public.schedule')); ?>" class="text-blue-600 hover:underline text-base font-medium mt-2 inline-block">
                🔄 Reset filter
            </a>
        </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-8">
        <div class="container mx-auto px-4 text-center text-base">
            <p class="font-medium">&copy; <?php echo e(date('Y')); ?> Politeknik Negeri Jember - TI</p>
            <p class="text-gray-400 text-sm mt-2">Sistem Peminjaman Laboratorium</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        let searchTimeout;

        /**
         * Auto-submit form saat filter berubah
         */
        function autoSubmit() {
            // Tampilkan loading text
            const loading = document.getElementById('loadingText');
            if (loading) loading.classList.remove('hidden');

            // Submit form
            document.getElementById('filterForm').submit();
        }

        /**
         * Debounce untuk search (tunggu 500ms setelah user selesai mengetik)
         */
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                autoSubmit();
            }, 500);
        }

        // Hide loading text when page loads (after redirect)
        window.addEventListener('load', function() {
            const loading = document.getElementById('loadingText');
            if (loading) loading.classList.add('hidden');
        });
    </script>

</body>
</html>
<?php /**PATH D:\project\laravel_project\sipinlab\resources\views/public/schedule.blade.php ENDPATH**/ ?>