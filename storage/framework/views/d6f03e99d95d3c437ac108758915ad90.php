<?php $__env->startSection('title', 'Detail Booking'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <a href="<?php echo e(route('admin.schedule.index')); ?>" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-flex items-center gap-1">
                ← Kembali ke Daftar Booking
            </a>
            <h1 class="text-3xl font-bold text-gray-800">📋 Detail Booking</h1>
            <p class="text-gray-600">Informasi lengkap peminjaman laboratorium</p>
        </div>
        <a href="<?php echo e(route('booking.download-pdf', $booking)); ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Unduh PDF
        </a>
    </div>

    <!-- ✅ Main Booking Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">

        <!-- Card Header: Lab Info & Status -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?php echo e($booking->lab_name); ?></h2>
                    <p class="text-sm text-gray-600">
                        <?php if($booking->session): ?>
                            <?php echo e($booking->session); ?>

                        <?php else: ?>
                            <?php echo e($booking->start_time); ?> - <?php echo e($booking->end_time); ?>

                        <?php endif; ?>
                        • <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('DD MMMM YYYY')); ?>

                    </p>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="px-4 py-2 rounded-full text-sm font-semibold
                <?php if($booking->status === 'confirmed'): ?> bg-green-100 text-green-800
                <?php elseif($booking->status === 'pending'): ?> bg-yellow-100 text-yellow-800
                <?php elseif($booking->status === 'rejected'): ?> bg-red-100 text-red-800
                <?php elseif($booking->status === 'cancelled'): ?> bg-gray-100 text-gray-800
                <?php else: ?> bg-blue-100 text-blue-800 <?php endif; ?>">
                <?php if($booking->status === 'confirmed'): ?>
                    ✓ Dikonfirmasi
                <?php elseif($booking->status === 'pending'): ?>
                    ⏳ Menunggu
                <?php elseif($booking->status === 'rejected'): ?>
                    ✗ Ditolak
                <?php elseif($booking->status === 'cancelled'): ?>
                    ✗ Dibatalkan
                <?php else: ?>
                    <?php echo e(ucfirst($booking->status)); ?>

                <?php endif; ?>
            </div>
        </div>

        <!-- Card Body: 3 Column Layout -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- ✅ Column 1: Data Pemohon -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    DATA PEMOHON
                </h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <p class="font-semibold text-gray-900"><?php echo e($booking->user->name ?? 'N/A'); ?></p>
                        <?php if($booking->user->nip): ?>
                            <p class="text-gray-600">NIP: <?php echo e($booking->user->nip); ?></p>
                        <?php elseif($booking->user->nim): ?>
                            <p class="text-gray-600">NIM: <?php echo e($booking->user->nim); ?></p>
                        <?php endif; ?>
                        <p class="text-gray-600"><?php echo e($booking->user->email ?? '-'); ?></p>
                        <?php if($booking->user->phone): ?>
                            <p class="text-gray-600">📞 <?php echo e($booking->user->phone); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ✅ Column 2: Jadwal Peminjaman -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    JADWAL PEMINJAMAN
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Mulai</span>
                        <span class="font-semibold text-gray-900"><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('DD MMM YYYY')); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Selesai</span>
                        <span class="font-semibold text-gray-900"><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('DD MMM YYYY')); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Waktu Sesi</span>
                        <span class="font-semibold text-gray-900">
                            <?php if($booking->session): ?>
                                <?php echo e($booking->session); ?>

                            <?php else: ?>
                                <?php echo e($booking->start_time); ?> - <?php echo e($booking->end_time); ?>

                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Durasi</span>
                        <span class="font-semibold text-blue-600">1 Hari</span>
                    </div>
                </div>
            </div>

            <!-- ✅ Column 3: Rincian Kegiatan -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    RINCIAN KEGIATAN
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-600 block mb-1">Jenis Kegiatan</span>
                        <p class="font-semibold text-gray-900"><?php echo e($booking->activity_type ?? 'Praktikum Mata Kuliah'); ?></p>
                    </div>
                    <div>
                        <span class="text-gray-600 block mb-1">Keperluan</span>
                        <p class="text-gray-900"><?php echo e($booking->purpose ?? $booking->notes ?? '-'); ?></p>
                    </div>
                    <div>
                        <span class="text-gray-600 block mb-1">Kebutuhan Khusus</span>
                        <p class="text-gray-900"><?php echo e($booking->special_needs ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">ID Booking</span>
                    <p class="font-semibold text-gray-900">#<?php echo e(str_pad($booking->id, 5, '0', STR_PAD_LEFT)); ?></p>
                </div>
                <div>
                    <span class="text-gray-600">Diajukan Oleh</span>
                    <p class="font-semibold text-gray-900"><?php echo e(ucfirst($booking->user->role ?? 'user')); ?></p>
                </div>
                <div>
                    <span class="text-gray-600">Dibuat Pada</span>
                    <p class="font-semibold text-gray-900"><?php echo e($booking->created_at->locale('id')->isoFormat('DD MMM YYYY HH:mm')); ?></p>
                </div>
            </div>
        </div>

        <!-- ✅ Approval Timeline -->
        <div class="px-6 py-6 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Timeline Persetujuan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Approval 1: Dosen -->
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            <?php if($booking->approved_at_dosen): ?> bg-green-100 text-green-600
                            <?php else: ?> bg-gray-100 text-gray-400 <?php endif; ?>">
                            <?php if($booking->approved_at_dosen): ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            <?php else: ?>
                                <span class="text-sm font-bold">1</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Persetujuan Dosen</p>
                            <p class="text-xs text-gray-500">Menunggu persetujuan dari dosen pembimbing atau pengajar lab.</p>
                        </div>
                    </div>
                    <?php if($booking->approved_at_dosen): ?>
                        <div class="ml-13 text-xs text-green-600 bg-green-50 p-2 rounded">
                            ✓ Disetujui oleh <?php echo e($booking->approverDosen->name ?? 'Dosen'); ?> pada <?php echo e($booking->approved_at_dosen->locale('id')->isoFormat('DD/MM/YYYY HH:mm')); ?>

                        </div>
                    <?php endif; ?>
                </div>

                <!-- Approval 2: Teknisi -->
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            <?php if($booking->approved_at_teknisi): ?> bg-green-100 text-green-600
                            <?php else: ?> bg-gray-100 text-gray-400 <?php endif; ?>">
                            <?php if($booking->approved_at_teknisi): ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            <?php else: ?>
                                <span class="text-sm font-bold">2</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Verifikasi Teknisi</p>
                            <p class="text-xs text-gray-500">Teknisi akan memverifikasi ketersediaan alat dan fasilitas.</p>
                        </div>
                    </div>
                    <?php if($booking->approved_at_teknisi): ?>
                        <div class="ml-13 text-xs text-green-600 bg-green-50 p-2 rounded">
                            ✓ Disetujui oleh <?php echo e($booking->approverTeknisi->name ?? 'Teknisi'); ?> pada <?php echo e($booking->approved_at_teknisi->locale('id')->isoFormat('DD/MM/YYYY HH:mm')); ?>

                        </div>
                    <?php endif; ?>
                </div>

                <!-- Approval 3: Kalab -->
                <div class="relative">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            <?php if($booking->approved_at_kalab || $booking->status === 'confirmed'): ?> bg-green-100 text-green-600
                            <?php else: ?> bg-gray-100 text-gray-400 <?php endif; ?>">
                            <?php if($booking->approved_at_kalab || $booking->status === 'confirmed'): ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            <?php else: ?>
                                <span class="text-sm font-bold">3</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Konfirmasi Akhir</p>
                            <p class="text-xs text-gray-500">Kalab melakukan finalisasi jadwal dan konfirmasi akses.</p>
                        </div>
                    </div>
                    <?php if($booking->approved_at_kalab): ?>
                        <div class="ml-13 text-xs text-green-600 bg-green-50 p-2 rounded">
                            ✓ KONFIRMASI FINAL oleh <?php echo e($booking->approverKalab->name ?? 'Kalab'); ?> pada <?php echo e($booking->approved_at_kalab->locale('id')->isoFormat('DD/MM/YYYY HH:mm')); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Bottom Section: Management & Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Management Booking -->
        <div class="md:col-span-1">
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-red-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Manajemen Booking
                </h3>

                <div class="space-y-2">
                    <?php if($booking->status !== 'cancelled' && $booking->status !== 'rejected'): ?>
                        <form action="<?php echo e(route('admin.schedule.cancel', $booking)); ?>" method="POST" class="inline w-full">
                            <?php echo csrf_field(); ?>
                            <button type="submit" onclick="return confirm('Yakin ingin membatalkan booking ini?')"
                                    class="w-full px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Hapus Booking
                            </button>
                        </form>
                    <?php else: ?>
                        <button disabled class="w-full px-4 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed font-medium text-sm">
                            Booking Sudah Dibatalkan
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info Tambahan -->
        <div class="md:col-span-2">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Info Tambahan</h3>

                <ul class="space-y-2 text-sm text-gray-600 mb-4">
                    <li class="flex items-start gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mt-1.5"></span>
                        <span>ID Booking unik untuk penelusuran administratif.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full mt-1.5"></span>
                        <span>Pastikan waktu booking tidak bentrok dengan kegiatan akademik lain.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="w-2 h-2 bg-purple-500 rounded-full mt-1.5"></span>
                        <span>Formulir resmi siap diunduh untuk tanda tangan basah (jika diperlukan).</span>
                    </li>
                </ul>

                <!-- Dokumen Booking -->
                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-xs font-semibold text-gray-500 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Dokumen Booking
                    </h4>

                    <div class="flex gap-3">
                        <a href="<?php echo e(route('booking.download-approved', $booking)); ?>"
                           class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium text-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Unduh Form Resmi
                        </a>
                        <button onclick="window.print()"
                                class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Cetak
                        </button>
                    </div>

                    <p class="text-xs text-orange-600 mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Form hanya dapat diunduh oleh pemohon atau staff berwenang setelah dikonfirmasi.
                    </p>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 pt-4 mt-4 text-xs text-gray-400 text-right">
                    <p>Dibuat melalui SiPinLab System</p>
                    <p><?php echo e(date('Y')); ?> - Politeknik Negeri Jember</p>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/admin/schedule/show.blade.php ENDPATH**/ ?>