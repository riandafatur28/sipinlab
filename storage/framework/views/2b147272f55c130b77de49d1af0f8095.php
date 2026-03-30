<?php $__env->startSection('title', 'Detail User - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <a href="<?php echo e(route('admin.users.index')); ?>" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center gap-2 text-sm font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar User
        </a>
        <h1 class="text-3xl font-bold text-gray-800">👤 Detail User</h1>
        <p class="text-gray-600 mt-1">Informasi lengkap akun pengguna</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <?php echo session('success'); ?>

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

    <!-- User Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">

        <!-- Header with Avatar & Role Badge -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                    <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800"><?php echo e($user->name); ?></h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            <?php if($user->role === 'mahasiswa'): ?> bg-yellow-100 text-yellow-800
                            <?php elseif($user->role === 'dosen'): ?> bg-green-100 text-green-800
                            <?php elseif($user->role === 'ketua_lab'): ?> bg-purple-100 text-purple-800
                            <?php elseif($user->role === 'teknisi'): ?> bg-blue-100 text-blue-800
                            <?php elseif($user->role === 'admin'): ?> bg-red-100 text-red-800
                            <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                            <?php echo e(ucfirst($user->role)); ?>

                        </span>
                        <?php if($user->is_kalab): ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                👔 Kalab
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('admin.users.edit', $user)); ?>"
                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    ✏️ Edit
                </a>
                <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" class="inline"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit"
                            class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium transition-colors border border-red-200">
                        🗑️ Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="p-6">
            <!-- Grid Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Email & Phone -->
                <div class="space-y-4">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">📧 Email</h3>
                        <p class="text-gray-800 font-medium"><?php echo e($user->email); ?></p>
                        <?php if($user->role === 'mahasiswa' && !str_ends_with($user->email, '@student.polije.ac.id')): ?>
                            <p class="text-xs text-red-500 mt-1">⚠️ Email mahasiswa seharusnya @student.polije.ac.id</p>
                        <?php elseif($user->role !== 'mahasiswa' && !str_ends_with($user->email, '@polije.ac.id')): ?>
                            <p class="text-xs text-red-500 mt-1">⚠️ Email staff seharusnya @polije.ac.id</p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">📱 Telepon</h3>
                        <p class="text-gray-800 font-medium"><?php echo e($user->phone ?? '-'); ?></p>
                    </div>
                </div>

                <!-- NIM / NIP -->
                <div class="space-y-4">
                    <?php if($user->role === 'mahasiswa'): ?>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">🎓 NIM</h3>
                        <p class="text-gray-800 font-mono font-medium"><?php echo e($user->nim ?? '-'); ?></p>
                    </div>
                    <?php else: ?>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">👨‍🏫 NIP</h3>
                        <p class="text-gray-800 font-mono font-medium"><?php echo e($user->nip ?? '-'); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Lab Assignment (for Teknisi) -->
                    <?php if($user->role === 'teknisi'): ?>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">🏢 Laboratorium Ditugaskan</h3>
                        <p class="text-gray-800 font-medium"><?php echo e($user->lab_name ?? '-'); ?></p>
                        <?php if(empty($user->lab_name)): ?>
                            <p class="text-xs text-orange-500 mt-1">⚠️ Teknisi harus ditugaskan ke laboratorium</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Account Info -->
                <div class="space-y-4 md:col-span-2">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-xs text-gray-500">ID User</p>
                            <p class="font-mono text-sm text-gray-800">#<?php echo e(str_pad($user->id, 5, '0', STR_PAD_LEFT)); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dibuat Pada</p>
                            <p class="text-sm text-gray-800"><?php echo e($user->created_at?->isoFormat('D MMM Y HH:mm') ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Terakhir Update</p>
                            <p class="text-sm text-gray-800"><?php echo e($user->updated_at?->isoFormat('D MMM Y HH:mm') ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status</p>
                            <p class="text-sm font-medium text-green-600">✅ Aktif</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Additional Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">⚡ Aksi Tambahan</h3>
        <div class="flex flex-wrap gap-3">

            <!-- Reset Password -->
            <form action="<?php echo e(route('admin.users.reset-password', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="px-4 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg text-sm font-medium transition-colors border border-orange-200"
                        onclick="return confirm('Reset password user ini? Password baru akan ditampilkan.')">
                    🔑 Reset Password
                </button>
            </form>

            <!-- Toggle Kalab Status (for Dosen/Ketua Lab) -->
            <?php if(in_array($user->role, ['dosen', 'ketua_lab'])): ?>
            <form action="<?php echo e(route('admin.users.toggle-kalab', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg text-sm font-medium transition-colors border border-indigo-200">
                    <?php echo e($user->is_kalab ? '❌ Cabut Akses Kalab' : '👔 Jadikan Kalab'); ?>

                </button>
            </form>
            <?php endif; ?>

            <!-- ✅ View Bookings (FIXED: menggunakan user_id parameter) -->
            <?php if(in_array($user->role, ['mahasiswa', 'dosen'])): ?>
            <?php
                $userBookingCount = \App\Models\Booking::where('user_id', $user->id)->count();
            ?>
            <?php endif; ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/admin/users/show.blade.php ENDPATH**/ ?>