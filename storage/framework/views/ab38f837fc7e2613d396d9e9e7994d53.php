<?php $__env->startSection('title', 'Detail Booking'); ?>

<?php $__env->startSection('content'); ?>
<?php
    // Helper function untuk mendapatkan nama user dari ID
    function getUserNameById($userId) {
        if (!$userId) return '-';
        $user = \App\Models\User::find($userId);
        return $user?->name ?? '-';
    }

    // Helper untuk cek status confirmed (karena method mungkin tidak ada di model)
    function isBookingConfirmed($booking) {
        return $booking->status === 'confirmed';
    }

    // Helper untuk cek apakah user boleh download form
    function canDownloadForm($booking, $user) {
        // Owner booking bisa download jika confirmed
        if ($booking->user_id === $user->id && isBookingConfirmed($booking)) {
            return true;
        }
        // Staff dengan hak akses bisa download jika confirmed
        if (isBookingConfirmed($booking) && (
            $user->isAdmin() ||
            $user->isTeknisi() ||
            $user->isKalab() ||
            $user->role === 'ketua_lab'
        )) {
            return true;
        }
        return false;
    }
?>

<div class="max-w-5xl mx-auto">

    <!-- Header -->
    <div class="mb-8 flex justify-between items-start flex-wrap gap-4">
        <div>
            <a href="<?php echo e(route('booking.index')); ?>" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Booking
            </a>
            <h1 class="text-3xl font-bold text-gray-800">📋 Detail Booking</h1>
            <p class="text-gray-600 mt-1">Informasi lengkap peminjaman laboratorium</p>
        </div>

        <!-- ✅ TOMBOL DOWNLOAD PDF - FIX: Authorization untuk teknisi -->
        <?php if(isBookingConfirmed($booking) && canDownloadForm($booking, Auth::user())): ?>
        <a href="<?php echo e(route('booking.download-approved', $booking)); ?>" target="_blank"
           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Unduh PDF
        </a>
        <?php elseif(isBookingConfirmed($booking)): ?>
        <button disabled class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg flex items-center gap-2 cursor-not-allowed" title="Anda tidak memiliki akses untuk mengunduh">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Unduh PDF
        </button>
        <?php endif; ?>
    </div>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg text-red-700">
        <ul class="list-disc list-inside text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Booking Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <!-- Header dengan Status -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo e($booking->lab_name); ?></h2>
                    <p class="text-sm text-gray-600"><?php echo e($booking->session); ?> • <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('d M Y')); ?></p>
                </div>
            </div>
            <span class="px-4 py-2 text-sm font-semibold rounded-full <?php echo e(\App\Http\Controllers\BookingController::getStatusBadgeClass($booking->status)); ?>">
                <?php echo e(\App\Http\Controllers\BookingController::getStatusLabel($booking->status)); ?>

            </span>
        </div>

        <div class="p-8">
            <!-- Grid Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">

                <!-- Pemohon -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">👤 Data Pemohon</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2">
                        <p class="font-bold text-gray-800"><?php echo e($booking->user->name ?? 'Unknown'); ?></p>
                        <p class="text-sm text-gray-600">
                            <?php echo e(($booking->user->role ?? '') === 'mahasiswa' ? 'NIM: ' . ($booking->user->nim ?? 'N/A') : 'NIP: ' . ($booking->user->nip ?? 'N/A')); ?>

                        </p>
                        <p class="text-sm text-gray-600 break-all"><?php echo e($booking->user->email ?? '-'); ?></p>
                        <p class="text-sm text-gray-600 font-medium flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <?php echo e($booking->phone ?? '-'); ?>

                        </p>
                        <?php if(($booking->user->role ?? '') === 'mahasiswa'): ?>
                        <p class="text-xs text-gray-500 mt-2 border-t pt-2 border-gray-200">Prodi: <?php echo e($booking->prodi ?? 'Teknik Informatika'); ?> - Gol. <?php echo e($booking->golongan ?? '-'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Waktu & Tanggal -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">📅 Jadwal Peminjaman</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-3">
                        <div class="flex justify-between items-center border-b pb-2 border-gray-200">
                            <span class="text-sm text-gray-500">Tanggal Mulai</span>
                            <span class="font-semibold text-gray-800"><?php echo e(\Carbon\Carbon::parse($booking->start_date)->format('d M Y')); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2 border-gray-200">
                            <span class="text-sm text-gray-500">Tanggal Selesai</span>
                            <span class="font-semibold text-gray-800"><?php echo e(\Carbon\Carbon::parse($booking->end_date)->format('d M Y')); ?></span>
                        </div>
                        <div class="flex justify-between items-center border-b pb-2 border-gray-200">
                            <span class="text-sm text-gray-500">Waktu Sesi</span>
                            <span class="font-semibold text-gray-800"><?php echo e($booking->session); ?></span>
                        </div>
                        <div class="pt-1">
                             <span class="text-xs text-gray-500">Durasi</span>
                             <span class="block font-semibold text-blue-600"><?php echo e($booking->duration_days ?? 1); ?> Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Kegiatan -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">📚 Rincian Kegiatan</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <span class="text-xs text-gray-500 block mb-1">Jenis Kegiatan</span>
                        <div class="font-semibold text-gray-800 mb-3"><?php echo e($booking->activity); ?></div>

                        <span class="text-xs text-gray-500 block mb-1">Keperluan</span>
                        <div class="text-sm text-gray-700 italic"><?php echo e($booking->purpose); ?></div>

                        <?php if($booking->notes): ?>
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <span class="text-xs text-gray-500 block mb-1">Kebutuhan Khusus</span>
                            <div class="text-sm text-gray-700"><?php echo e($booking->notes); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Anggota Kelompok -->
                <?php if(($booking->is_group ?? false) && !empty($booking->members) && is_array($booking->members) && count($booking->members) > 0): ?>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">👥 Anggota Kelompok (<?php echo e(count($booking->members)); ?>)</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <ul class="space-y-2 text-sm text-gray-700 pr-2">
                            <?php $__currentLoopData = $booking->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $memberId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $member = \App\Models\User::find($memberId);
                                ?>
                                <?php if($member): ?>
                                <li class="flex items-start gap-2">
                                    <span class="block w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5"></span>
                                    <div>
                                        <span class="font-medium"><?php echo e($member->name); ?></span>
                                        <span class="text-xs text-gray-500 ml-1">(<?php echo e($member->nim ?? '-'); ?>)</span>
                                    </div>
                                </li>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Supervisor -->
                <?php if($booking->supervisor_id): ?>
                    <?php
                        $supervisor = \App\Models\User::find($booking->supervisor_id);
                    ?>
                    <?php if($supervisor): ?>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">👨‍🏫 Dosen Pembimbing</h3>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <p class="font-semibold text-gray-800"><?php echo e($supervisor->name); ?></p>
                            <p class="text-sm text-gray-600 mt-1"><?php echo e($supervisor->email); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Metadata -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">⚙️ Informasi Sistem</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID Booking</span>
                            <span class="font-mono font-semibold text-gray-800">#<?php echo e(str_pad($booking->id, 5, '0', STR_PAD_LEFT)); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Diajukan Oleh</span>
                            <span class="font-semibold text-gray-800"><?php echo e(ucfirst($booking->user->role ?? 'user')); ?></span>
                        </div>
                         <div class="flex justify-between">
                            <span class="text-gray-500">Dibuat Pada</span>
                            <span class="font-medium text-gray-800"><?php echo e(\Carbon\Carbon::parse($booking->created_at)->isoFormat('D MMM YYYY HH:mm')); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Timeline -->
            <div class="border-t border-gray-200 pt-8 pb-8">
                <h3 class="text-sm font-bold text-gray-700 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Timeline Persetujuan
                </h3>

                <div class="relative">
                    <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 -ml-px z-0"></div>

                    <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Step 1: Dosen -->
                        <div class="md:flex items-center md:items-start gap-4">
                            <div class="flex-shrink-0 w-16 h-16 relative flex items-center justify-center">
                                <?php
                                    $isApprovedDosen = in_array($booking->status, ['approved_dosen', 'approved_teknisi', 'confirmed']);
                                ?>
                                <div class="w-16 h-16 rounded-full <?php echo e($isApprovedDosen ? 'bg-green-100' : 'bg-gray-100'); ?> flex items-center justify-center text-xl font-bold
                                    <?php echo e($isApprovedDosen ? 'text-green-600' : 'text-gray-400'); ?> border-4 border-white shadow-sm">
                                    <?php echo e($isApprovedDosen ? '✓' : '1'); ?>

                                </div>
                            </div>
                            <div class="flex-1 pt-2 md:pt-0">
                                <h4 class="text-sm font-bold text-gray-800 mb-1">Persetujuan Dosen</h4>
                                <p class="text-xs text-gray-500">Menunggu persetujuan dari dosen pembimbing atau pengajar lab.</p>
                                <div class="mt-2 text-xs font-mono text-gray-400">
                                    <?php if($booking->approved_at_dosen): ?>
                                        ✅ Disetujui oleh <?php echo e(getUserNameById($booking->approved_by_dosen)); ?> pada <?php echo e(\Carbon\Carbon::parse($booking->approved_at_dosen)->format('d/m/Y H:i')); ?>

                                    <?php else: ?>
                                        Menunggu...
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Teknisi -->
                        <div class="md:flex items-center md:items-start gap-4">
                            <div class="flex-shrink-0 w-16 h-16 relative flex items-center justify-center">
                                <?php
                                    $isApprovedTeknisi = in_array($booking->status, ['approved_teknisi', 'confirmed']);
                                ?>
                                <div class="w-16 h-16 rounded-full <?php echo e($isApprovedTeknisi ? 'bg-green-100' : 'bg-gray-100'); ?> flex items-center justify-center text-xl font-bold
                                    <?php echo e($isApprovedTeknisi ? 'text-green-600' : 'text-gray-400'); ?> border-4 border-white shadow-sm">
                                    <?php echo e($isApprovedTeknisi ? '✓' : '2'); ?>

                                </div>
                            </div>
                            <div class="flex-1 pt-2 md:pt-0">
                                <h4 class="text-sm font-bold text-gray-800 mb-1">Verifikasi Teknisi</h4>
                                <p class="text-xs text-gray-500">Teknisi akan memverifikasi ketersediaan alat dan fasilitas.</p>
                                <div class="mt-2 text-xs font-mono text-gray-400">
                                    <?php if($booking->approved_at_teknisi): ?>
                                        ✅ Disetujui oleh <?php echo e(getUserNameById($booking->approved_by_teknisi)); ?> pada <?php echo e(\Carbon\Carbon::parse($booking->approved_at_teknisi)->format('d/m/Y H:i')); ?>

                                    <?php else: ?>
                                        Menunggu...
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Ka Lab -->
                        <div class="md:flex items-center md:items-start gap-4">
                            <div class="flex-shrink-0 w-16 h-16 relative flex items-center justify-center">
                                <div class="w-16 h-16 rounded-full <?php echo e($booking->status === 'confirmed' ? 'bg-green-100' : 'bg-gray-100'); ?> flex items-center justify-center text-xl font-bold
                                    <?php echo e($booking->status === 'confirmed' ? 'text-green-600' : 'text-gray-400'); ?> border-4 border-white shadow-sm">
                                    <?php echo e($booking->status === 'confirmed' ? '✓' : '3'); ?>

                                </div>
                            </div>
                            <div class="flex-1 pt-2 md:pt-0">
                                <h4 class="text-sm font-bold text-gray-800 mb-1">Konfirmasi Akhir</h4>
                                <p class="text-xs text-gray-500">Kalab melakukan finalisasi jadwal dan konfirmasi akses.</p>
                                <div class="mt-2 text-xs font-mono text-gray-400">
                                    <?php if($booking->status === 'confirmed'): ?>
                                        ✅ KONFIRMASI FINAL oleh <?php echo e(getUserNameById($booking->approved_by_kalab)); ?>

                                    <?php else: ?>
                                        Menunggu...
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⚠️ REJECTION INFO SECTION -->
            <?php if($booking->status === 'rejected'): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <h4 class="text-sm font-bold text-yellow-800">Booking Ditolak</h4>
                        <p class="text-sm text-yellow-700 mt-1"><?php echo e($booking->rejection_reason ?? 'Alasan tidak tersedia.'); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

             <!-- ⚠️ CANCELLED INFO SECTION -->
            <?php if($booking->status === 'cancelled'): ?>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-6">
                 <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-gray-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800">Booking Dibatalkan</h4>
                        <p class="text-sm text-gray-700 mt-1"><?php echo e($booking->rejection_reason ?? 'Booking dibatalkan.'); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ✅ ACTION BUTTONS AREA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- LEFT COLUMN: Actions based on current user role -->
        <div class="space-y-6">

            <!-- 🟢 DOSEN ACTIONS -->
            <?php if(auth()->user()->isDosen() && $booking->status === 'pending'): ?>
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-6">
                <h3 class="text-lg font-bold text-blue-800 mb-2">🔎 Review & Keputusan</h3>
                <p class="text-sm text-blue-600 mb-4">Booking diajukan oleh mahasiswa. Silakan setujui atau tolak sesuai kebutuhan praktikum/bimbingan.</p>
                <div class="flex gap-3">
                    <form action="<?php echo e(route('booking.approve-dosen', $booking)); ?>" method="POST" class="flex-1">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">✅ Setujui</button>
                    </form>
                    <button onclick="openRejectModal()" class="w-1/3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 rounded-lg font-medium transition-colors">Tolak</button>
                </div>
            </div>
            <?php endif; ?>

            <!-- 🟠 TEKNISI ACTIONS -->
            <?php if(Auth::user()->isTeknisi() && in_array($booking->status, ['pending', 'approved_dosen'])): ?>
            <div class="bg-orange-50 rounded-xl border border-orange-200 p-6">
                <h3 class="text-lg font-bold text-orange-800 mb-2">⚠️ Verifikasi Teknis</h3>
                <p class="text-sm text-orange-600 mb-4">Pastikan peralatan tersedia dan kondisi lab layak digunakan untuk sesi ini.</p>
                <div class="flex gap-3">
                    <form action="<?php echo e(route('booking.approve-teknisi', $booking)); ?>" method="POST" class="flex-1">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">✅ Konfirmasi Tersedia</button>
                    </form>
                     <button onclick="openRejectModal()" class="w-1/3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 rounded-lg font-medium transition-colors">Tolak</button>
                </div>
            </div>
            <?php endif; ?>

            <!-- 💜 KALAB/ADMIN ACTIONS -->
            <?php if((Auth::user()->isAdmin() || Auth::user()->isKalab() || Auth::user()->role === 'ketua_lab') && $booking->status === 'approved_teknisi'): ?>
            <div class="bg-purple-50 rounded-xl border border-purple-200 p-6">
                <h3 class="text-lg font-bold text-purple-800 mb-2">📝 Konfirmasi Final</h3>
                <p class="text-sm text-purple-600 mb-4">Tahap akhir. Bukti seluruh proses approval sudah selesai. Konfirmasi dapat dilaksanakan.</p>
                <div class="flex gap-3">
                    <form action="<?php echo e(route('booking.approve-kalab', $booking)); ?>" method="POST" class="flex-1">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors">🔓 Konfirmasi Final</button>
                    </form>
                     <button onclick="openRejectModal()" class="w-1/3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 rounded-lg font-medium transition-colors">Tolak</button>
                </div>
            </div>
            <?php endif; ?>

             <!-- 🛡️ ADMIN/STAFF DELETE ACTION -->
             <?php if(Auth::user()->isAdmin() || Auth::user()->isKalab() || Auth::user()->isTeknisi()): ?>
                <?php if(!in_array($booking->status, ['rejected', 'cancelled'])): ?>
                <div class="bg-red-50 rounded-xl border border-red-200 p-6">
                    <h3 class="text-sm font-bold text-red-800 mb-2">⚠️ Manajemen Booking</h3>
                    <div class="flex gap-3">
                         <form action="<?php echo e(route('booking.destroy', $booking)); ?>" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus booking ini? Tindakan ini tidak dapat dibatalkan.')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="w-full py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg font-medium transition-colors border border-red-200">🗑️ Hapus Booking</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
             <?php endif; ?>

        </div>

        <!-- RIGHT COLUMN: Quick View & Download -->
        <div class="lg:border-l lg:pl-8">
             <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 h-full">
                <h3 class="font-bold text-gray-700 mb-4">Info Tambahan</h3>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        ID Booking unik untuk penelusuran administratif.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Pastikan waktu booking tidak bentrok dengan kegiatan akademik lain.
                    </li>
                    <?php if(isBookingConfirmed($booking)): ?>
                    <li class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                        Formulir resmi siap diunduh untuk tanda tangan basah (jika diperlukan).
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- ✅ Download Section dengan Authorization yang Benar -->
                <?php if(isBookingConfirmed($booking)): ?>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">📄 Dokumen Booking</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php if(canDownloadForm($booking, Auth::user())): ?>
                            <a href="<?php echo e(route('booking.download-approved', $booking)); ?>"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Unduh Form Resmi
                            </a>
                        <?php else: ?>
                            <button disabled class="inline-flex items-center gap-2 px-4 py-2 bg-gray-300 text-gray-500 rounded-lg text-sm font-medium cursor-not-allowed" title="Anda tidak memiliki akses untuk mengunduh">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Tidak Diizinkan
                            </button>
                        <?php endif; ?>

                        <a href="<?php echo e(route('booking.print-form', $booking)); ?>"
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Cetak
                        </a>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        ⚠️ Form hanya dapat diunduh oleh pemohon atau staff berwenang setelah dikonfirmasi.
                    </p>
                </div>
                <?php endif; ?>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-400 text-center">
                        Dibuat melalui SiPinLab System<br>
                        <?php echo e(date('Y')); ?> - Politeknik Negeri Jember
                    </p>
                </div>
             </div>
        </div>

    </div>

</div>

<!-- ================= MODALS ============== -->

<!-- Modal Reject (Shared) -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 transform scale-100 transition-transform">
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
            <form id="rejectForm" action="<?php echo e(route('booking.reject', $booking)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <textarea name="rejection_reason" required rows="4" maxlength="500" placeholder="Contoh: Jadwal bentrok dengan kuliah..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"><?php echo e(old('rejection_reason')); ?></textarea>
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
function openRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/booking/show.blade.php ENDPATH**/ ?>