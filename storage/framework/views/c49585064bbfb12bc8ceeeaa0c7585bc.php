<?php $__env->startSection('title', 'Booking Laboratorium - SiPinLab'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $user = Auth::user();
    $isMahasiswa = $user->isMahasiswa();
    $isDosen = $user->isDosen();
    $isKalab = $user->isKalab();
    $isStaff = !$isMahasiswa && !$isDosen && !$isKalab; // Admin, Teknisi, Staff
    $isDosenAndKalab = $isDosen && $isKalab;

    // Tab navigation untuk dual role
    $currentTab = request('tab', $isDosenAndKalab ? 'mybookings' : null);
?>

<div class="max-w-7xl mx-auto">

    <!-- ======================================================================== -->
    <!-- HEADER + ACTION BUTTON -->
    <!-- ======================================================================== -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <?php if($isMahasiswa): ?>
                    📋 Booking Saya
                <?php elseif($isDosenAndKalab): ?>
                    👔 Dashboard Ketua Lab & Dosen
                <?php elseif($isDosen): ?>
                    📋 Booking & Persetujuan Saya
                <?php elseif($isKalab): ?>
                    👔 Dashboard Ketua Laboratorium
                <?php else: ?>
                    📊 Kelola Booking
                <?php endif; ?>
            </h1>
            <p class="text-gray-600">
                <?php if($isMahasiswa): ?>
                    Daftar peminjaman laboratorium Anda
                <?php elseif($isDosenAndKalab): ?>
                    Kelola booking pribadi dan konfirmasi final mahasiswa
                <?php elseif($isDosen): ?>
                    Daftar booking dan persetujuan mahasiswa
                <?php elseif($isKalab): ?>
                    Kelola konfirmasi final peminjaman laboratorium
                <?php else: ?>
                    Dashboard manajemen peminjaman laboratorium
                <?php endif; ?>
            </p>
        </div>

        
    </div>

    <!-- Flash Messages -->
    <?php if(session('success')): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3 animate-pulse">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <?php echo e(session('success')); ?>

        <button onclick="this.closest('.animate-pulse')?.remove()" class="ml-auto text-green-800 hover:text-green-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <?php endif; ?>

    <?php if(session('info')): ?>
    <div class="mb-6 p-4 bg-blue-50 border border-blue-300 rounded-lg text-blue-800 flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <?php echo e(session('info')); ?>

    </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg">
        <ul class="list-disc list-inside text-red-700 text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- ======================================================================== -->
    <!-- ✅ TAB NAVIGATION (Khusus Dosen yang juga Kalab) -->
    <!-- ======================================================================== -->
    <?php if($isDosenAndKalab): ?>
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8 overflow-x-auto" role="tablist">
            <a href="<?php echo e(route('booking.index', array_merge(request()->except('tab'), ['tab' => 'mybookings']))); ?>"
               role="tab"
               class="<?php echo e($currentTab === 'mybookings' || !$currentTab
                   ? 'border-blue-500 text-blue-600'
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?>

                   whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                📋 Booking Saya
            </a>
            <a href="<?php echo e(route('booking.index', array_merge(request()->except('tab'), ['tab' => 'approvals']))); ?>"
               role="tab"
               class="<?php echo e($currentTab === 'approvals'
                   ? 'border-blue-500 text-blue-600'
                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?>

                   whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors">
                ⏳ Persetujuan Mahasiswa
                <?php
                    $pendingCount = \App\Models\Booking::where('status', 'pending')
                        ->whereHas('user', fn($q) => $q->where('role', 'mahasiswa'))
                        ->count();
                ?>
                <?php if($pendingCount > 0): ?>
                    <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-2 py-0.5"><?php echo e($pendingCount); ?></span>
                <?php endif; ?>
            </a>
            
        </nav>
    </div>
    <?php endif; ?>

    <!-- ======================================================================== -->
    <!-- ✅ STATS CARDS (Role-Based) -->
    <!-- ======================================================================== -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <?php if($isMahasiswa || $isDosen): ?>
            <!-- Stats untuk Mahasiswa/Dosen -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Total Booking</p>
                <p class="text-xl md:text-2xl font-bold text-blue-600"><?php echo e($stats['total'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Menunggu</p>
                <p class="text-xl md:text-2xl font-bold text-yellow-600"><?php echo e($stats['pending'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Disetujui</p>
                <p class="text-xl md:text-2xl font-bold text-green-600"><?php echo e($stats['confirmed'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Ditolak</p>
                <p class="text-xl md:text-2xl font-bold text-red-600"><?php echo e($stats['rejected'] ?? 0); ?></p>
            </div>

        <?php elseif($isKalab || $isDosenAndKalab && $currentTab === 'management'): ?>
            <!-- Stats untuk Kalab / Management Mode -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-orange-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">⏳ Menunggu Final</p>
                <p class="text-xl md:text-2xl font-bold text-orange-600"><?php echo e($stats['awaiting_final'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-green-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">✅ Hari Ini</p>
                <p class="text-xl md:text-2xl font-bold text-green-600"><?php echo e($stats['confirmed_today'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-blue-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">📊 Total Dikonfirmasi</p>
                <p class="text-xl md:text-2xl font-bold text-blue-600"><?php echo e($stats['total_confirmed'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-purple-500 opacity-20"></div>
                <p class="text-xs md:text-sm text-gray-500">🖨️ Siap Cetak</p>
                <p class="text-xl md:text-2xl font-bold text-purple-600"><?php echo e($stats['total_confirmed'] ?? 0); ?></p>
            </div>

        <?php else: ?>
            <!-- Stats untuk Staff/Admin/Teknisi -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Total Booking</p>
                <p class="text-xl md:text-2xl font-bold text-blue-600"><?php echo e($stats['total_booking'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Menunggu</p>
                <p class="text-xl md:text-2xl font-bold text-yellow-600"><?php echo e($stats['pending'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Dikonfirmasi</p>
                <p class="text-xl md:text-2xl font-bold text-green-600"><?php echo e($stats['confirmed'] ?? 0); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border border-gray-200 hover:shadow-md transition-shadow">
                <p class="text-xs md:text-sm text-gray-500">Hari Ini</p>
                <p class="text-xl md:text-2xl font-bold text-purple-600"><?php echo e($stats['hari_ini'] ?? 0); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================================== -->
    <!-- ✅ SEARCH & FILTER FORM (Hanya untuk Staff/Kalab Management) -->
    <!-- ======================================================================== -->
    <?php if($isStaff || ($isKalab && (!$isDosen || $currentTab === 'management')) || ($isDosenAndKalab && $currentTab === 'management')): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 md:p-6 mb-6">
        <form action="<?php echo e(route('booking.index')); ?>" method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

            <!-- 🔍 Search Input -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Cari (Nama/NIM/NIP)</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Ketik nama, NIM, atau NIP..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                       onkeyup="if(event.key==='Enter') document.getElementById('filterForm').submit()">
            </div>

            <!-- 🏢 Lab Dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="lab" onchange="document.getElementById('filterForm').submit()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                    <option value="">Semua Lab</option>
                    <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($lab); ?>" <?php echo e(request('lab') == $lab ? 'selected' : ''); ?>><?php echo e($lab); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- ⚠️ Status Dropdown -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" onchange="document.getElementById('filterForm').submit()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>⏳ Menunggu</option>
                    <option value="approved_dosen" <?php echo e(request('status') == 'approved_dosen' ? 'selected' : ''); ?>>✅ Dosen</option>
                    <option value="approved_teknisi" <?php echo e(request('status') == 'approved_teknisi' ? 'selected' : ''); ?>>✅ Teknisi</option>
                    <option value="confirmed" <?php echo e(request('status') == 'confirmed' ? 'selected' : ''); ?>>✅ Dikonfirmasi</option>
                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>❌ Ditolak</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>🗑️ Dibatalkan</option>
                </select>
            </div>

            <!-- 🔘 Submit Button -->
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
            </div>
        </form>

        <?php if(request()->hasAny(['search', 'lab', 'status', 'date_start', 'date_end'])): ?>
        <div class="mt-3 text-right">
            <a href="<?php echo e(route('booking.index', array_merge(request()->all(), ['tab' => $currentTab]))); ?>" class="text-xs text-blue-600 hover:underline">
                🔄 Reset filter
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ======================================================================== -->
    <!-- ✅ SECTION KHUSUS DOSEN: Persetujuan Booking Mahasiswa -->
    <!-- ======================================================================== -->
    <?php if($isDosen && (!$isKalab || $currentTab === 'approvals' || $currentTab === 'mybookings')): ?>
    <?php
        // Ambil booking yang menunggu persetujuan dosen (hanya jika di tab approvals atau default)
        $showApprovalSection = $currentTab === 'approvals' || (!$currentTab && !$isKalab);
        $pendingBookingsForDosen = $showApprovalSection
            ? \App\Models\Booking::where('status', 'pending')
                ->whereHas('user', function($q) { $q->where('role', 'mahasiswa'); })
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();
    ?>

    <?php if($showApprovalSection && $pendingBookingsForDosen->count() > 0): ?>
    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-bold text-yellow-800 mb-4 flex items-center gap-2">
            ⏳ Persetujuan Booking Mahasiswa (<?php echo e($pendingBookingsForDosen->count()); ?>)
        </h3>

        <div class="space-y-3">
            <?php $__currentLoopData = $pendingBookingsForDosen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-lg p-4 border border-yellow-200 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="font-semibold text-gray-900"><?php echo e($booking->user->name); ?></span>
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                <?php echo e($booking->user->nim ?? 'N/A'); ?>

                            </span>
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Lab:</strong> <?php echo e($booking->lab_name); ?></p>
                            <p><strong>Tanggal:</strong> <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('d M Y')); ?> | <strong>Sesi:</strong> <?php echo e($booking->session); ?></p>
                            <p><strong>Keperluan:</strong> <?php echo e(Str::limit($booking->purpose, 100)); ?></p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <!-- Tombol Approve -->
                        <form action="<?php echo e(route('booking.approve-dosen', $booking)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                    onclick="return confirm('✅ Setujui booking dari <?php echo e($booking->user->name); ?>?')"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Setujui
                            </button>
                        </form>

                        <!-- Tombol Reject -->
                        <button type="button"
                                onclick="showRejectModal(<?php echo e($booking->id); ?>, '<?php echo e($booking->user->name); ?>')"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- ======================================================================== -->
    <!-- ✅ SECTION KHUSUS KALAB: Booking yang Menunggu Konfirmasi Final -->
    <!-- ======================================================================== -->
    <?php if(($isKalab || ($isDosenAndKalab && $currentTab === 'management')) && isset($pendingApprovals) && $pendingApprovals->count() > 0): ?>
    <div class="mb-10 bg-white rounded-xl shadow-lg border border-purple-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-purple-100 bg-gradient-to-r from-purple-50 to-white">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-purple-900">📋 Menunggu Konfirmasi Final (Ka Lab)</h2>
                    <p class="text-sm text-purple-600 mt-1 ml-1">Ada <strong><?php echo e($pendingApprovals->total()); ?></strong> booking menunggu persetujuan akhir.</p>
                </div>
                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-semibold border border-purple-200">
                    Prioritas Tinggi
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemohon</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $pendingApprovals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-purple-50/50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center font-bold text-purple-700">
                                    <?php echo e(substr($booking->user->name, 0, 1)); ?>

                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900"><?php echo e($booking->user->name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e(ucfirst($booking->user->role)); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                            <?php echo e($booking->lab_name); ?>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <p class="font-semibold"><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('d M Y')); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($booking->session); ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <p class="font-medium text-gray-700"><?php echo e($booking->activity); ?></p>
                            <p class="text-xs text-gray-500 truncate max-w-[150px]"><?php echo e(Str::limit($booking->purpose, 40)); ?></p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                             <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                Pending Approval
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo e(route('booking.show', $booking)); ?>"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Konfirmasi
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($pendingApprovals->hasPages()): ?>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <?php echo e($pendingApprovals->links()); ?>

        </div>
        <?php endif; ?>
    </div>
    <?php elseif(($isKalab || ($isDosenAndKalab && $currentTab === 'management')) && isset($pendingApprovals) && $pendingApprovals->count() === 0): ?>
    <!-- Empty State untuk Kalab -->
    <div class="mb-10 bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Tidak Ada Booking yang Menunggu</h3>
        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Semua booking yang masuk sudah ditindaklanjuti atau diproses oleh tim terkait.</p>
    </div>
    <?php endif; ?>

    <!-- ======================================================================== -->
    <!-- ✅ SECTION BARU: BOOKING MAHASISWA DENGAN ANDA SEBAGAI PEMBIMBING (DOSEN) -->
    <!-- ======================================================================== -->
    <?php if($isDosen && isset($supervisedBookings)): ?>
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">🎓 Booking dengan Anda sebagai Pembimbing</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Riwayat peminjaman mahasiswa yang mencantumkan Anda sebagai dosen pembimbing
                    </p>
                </div>
                <?php if(isset($supervisedStats['total']) && $supervisedStats['total'] > 0): ?>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">
                    <?php echo e($supervisedStats['total']); ?> Booking
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Mini Cards -->
        <?php if(isset($supervisedStats)): ?>
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 grid grid-cols-3 gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-yellow-600"><?php echo e($supervisedStats['pending'] ?? 0); ?></p>
                <p class="text-xs text-gray-500">Menunggu</p>
            </div>
            <div class="text-center border-l border-gray-200">
                <p class="text-2xl font-bold text-green-600"><?php echo e($supervisedStats['confirmed'] ?? 0); ?></p>
                <p class="text-xs text-gray-500">Dikonfirmasi</p>
            </div>
            <div class="text-center border-l border-gray-200">
                <p class="text-2xl font-bold text-red-600"><?php echo e($supervisedStats['rejected'] ?? 0); ?></p>
                <p class="text-xs text-gray-500">Ditolak</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Sesi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $supervisedBookings ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                    <?php echo e(substr($booking->user->name, 0, 1)); ?>

                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800"><?php echo e($booking->user->name); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($booking->user->nim ?? 'N/A'); ?>

                                        <?php if($booking->is_group): ?>
                                            <span class="ml-1 px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded text-[10px]">
                                                +<?php echo e(count($booking->members ?? [])); ?> anggota
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 font-medium"><?php echo e($booking->lab_name); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <p><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('DD MMM YYYY')); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($booking->session ?? ($booking->start_time . ' - ' . $booking->end_time)); ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <p class="font-medium text-gray-800"><?php echo e($booking->activity); ?></p>
                            <p class="text-xs text-gray-500 truncate max-w-[200px]" title="<?php echo e($booking->purpose); ?>">
                                <?php echo e(Str::limit($booking->purpose, 40)); ?>

                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo e($booking->getStatusBadgeClass()); ?>">
                                <?php echo e($booking->getStatusLabel()); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="<?php echo e(route('booking.show', $booking->id)); ?>"
                               class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium">
                                Detail
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <p class="text-gray-500">Belum ada mahasiswa yang mencantumkan Anda sebagai pembimbing.</p>
                                <p class="text-xs text-gray-400">Mahasiswa dapat memilih Anda saat mengajukan booking.</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(isset($supervisedBookings) && $supervisedBookings->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <?php echo e($supervisedBookings->links()); ?>

        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ======================================================================== -->
    <!-- ✅ BOOKING LIST TABLE (Utama untuk semua role) -->
    <!-- ======================================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center flex-wrap gap-2">
            <h2 class="text-lg md:text-xl font-bold text-gray-800">
                <?php if($isMahasiswa): ?>
                    📋 Riwayat Booking Saya
                <?php elseif($isDosen && !$isKalab): ?>
                    📊 Semua Booking Saya
                <?php elseif($isDosenAndKalab && $currentTab === 'approvals'): ?>
                    ⏳ Persetujuan Mahasiswa
                <?php elseif($isDosenAndKalab && $currentTab === 'management'): ?>
                    📊 Management Booking
                <?php elseif($isKalab): ?>
                    ✅ Booking yang Sudah Dikonfirmasi
                <?php else: ?>
                    📊 Daftar Semua Booking
                <?php endif; ?>
            </h2>

            <?php if($isMahasiswa || $isDosen): ?>
                <a href="<?php echo e($isMahasiswa ? route('booking.create') : route('booking.create-dosen')); ?>"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Peminjaman Baru
                </a>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <?php if($isStaff || $isKalab || ($isDosenAndKalab && $currentTab === 'management')): ?>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                        <?php endif; ?>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden lg:table-cell">Sesi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <?php if($isStaff || $isKalab || ($isDosenAndKalab && $currentTab === 'management')): ?>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($booking->user->name ?? 'Unknown'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($booking->user->nim ?? $booking->user->nip ?? '-'); ?></div>
                            </td>
                        <?php endif; ?>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($booking->lab_name); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell">
                            <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('d M Y')); ?>

                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell"><?php echo e($booking->session); ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php
                                $statusClass = match($booking->status) {
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved_dosen' => 'bg-blue-100 text-blue-800',
                                    'approved_teknisi' => 'bg-indigo-100 text-indigo-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusLabel = match($booking->status) {
                                    'confirmed' => '✅ Dikonfirmasi',
                                    'pending' => '⏳ Menunggu Dosen',
                                    'approved_dosen' => '✅ Disetujui Dosen',
                                    'approved_teknisi' => '✅ Disetujui Teknisi',
                                    'rejected' => '❌ Ditolak',
                                    'cancelled' => '🗑️ Dibatalkan',
                                    default => ucfirst(str_replace('_', ' ', $booking->status)),
                                };
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full font-semibold <?php echo e($statusClass); ?>">
                                <?php echo e($statusLabel); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="<?php echo e(route('booking.show', $booking)); ?>"
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </a>

                                <?php if(($isKalab || $isDosenAndKalab) && $booking->status === 'approved_teknisi'): ?>
                                    <form action="<?php echo e(route('booking.approve-kalab', $booking)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit"
                                                onclick="return confirm('✅ Konfirmasi final booking ini?')"
                                                class="text-purple-600 hover:text-purple-800 font-medium text-xs">
                                            Konfirmasi
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="<?php echo e(($isStaff || $isKalab || ($isDosenAndKalab && $currentTab === 'management')) ? '6' : '5'); ?>" class="px-4 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p>Belum ada booking.</p>
                                <?php if($isMahasiswa || $isDosen): ?>
                                    <a href="<?php echo e($isMahasiswa ? route('booking.create') : route('booking.create-dosen')); ?>"
                                       class="text-blue-600 hover:underline font-medium">
                                        + Buat booking pertama
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($bookings->hasPages()): ?>
        <div class="px-4 md:px-6 py-4 border-t border-gray-200">
            <?php echo e($bookings->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ================= MODAL REJECT (Shared) ============== -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-gray-200">
        <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">❌ Tolak Booking</h3>
                <button type="button" onclick="closeRejectModal()" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-5">
            <p class="text-gray-700 mb-4">Mohon berikan alasan penolakan agar pemohon dapat memperbaruinya.</p>
            <form id="rejectForm" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_method" value="POST">
                <textarea name="rejection_reason" required rows="4" maxlength="500" placeholder="Contoh: Jadwal bentrok dengan kuliah..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"></textarea>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">Batal</button>
                    <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Submit Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let currentRejectUrl = '';

function showRejectModal(bookingId, userName) {
    currentRejectUrl = `/booking/${bookingId}/reject`;
    document.getElementById('rejectForm').action = currentRejectUrl;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectForm').reset();
}

// Close modal jika klik backdrop
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/booking/index.blade.php ENDPATH**/ ?>