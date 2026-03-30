<?php $__env->startSection('title', 'Profil Saya - SiPinLab'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">

    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">👤 Profil Saya</h1>
        <p class="text-gray-600 mt-1">Kelola informasi akun dan preferensi Anda</p>
    </div>

    <!-- Profile Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Header Banner -->
        <div class="h-24 bg-gradient-to-r from-blue-500 to-blue-600"></div>

        <div class="px-6 pb-6">
            <!-- Avatar & Basic Info -->
            <div class="flex flex-col md:flex-row md:items-end gap-4 -mt-12 mb-6">
                <div class="relative">
                    <div class="w-24 h-24 rounded-full bg-blue-500 border-4 border-white flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                    </div>
                    <?php if($user->isKalab()): ?>
                        <span class="absolute bottom-1 right-1 badge-kalab text-xs">👔</span>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="badge-role badge-role-<?php echo e($user->role); ?>">
                            <?php echo e(ucfirst($user->role)); ?>

                        </span>
                        <?php if($user->isKalab()): ?>
                            <span class="badge-kalab">Kepala Laboratorium</span>
                        <?php endif; ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($user->account_status['class']); ?>">
                            <span><?php echo e($user->account_status['dot']); ?></span>
                            <?php echo e($user->account_status['label']); ?>

                        </span>
                    </div>
                </div>
                <a href="<?php echo e(route('profile.edit')); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Profil
                </a>
            </div>

            <!-- Profile Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Informasi Pribadi -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b pb-2">
                        📋 Informasi Pribadi
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Nama Lengkap</span>
                            <span class="text-gray-900 font-medium text-sm"><?php echo e($user->name); ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Email</span>
                            <span class="text-gray-900 font-medium text-sm"><?php echo e($user->email); ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm"><?php echo e($user->nim_nip_label); ?></span>
                            <span class="text-gray-900 font-medium text-sm">
                                <?php echo e($user->nim_nip ?? '<span class="text-gray-400 italic">Belum diisi</span>'); ?>

                            </span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Program Studi</span>
                            <span class="text-gray-900 font-medium text-sm"><?php echo e($user->prodi ?? '-'); ?></span>
                        </div>
                        <?php if($user->phone): ?>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">No. Telepon</span>
                            <span class="text-gray-900 font-medium text-sm"><?php echo e($user->phone); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informasi Akun -->
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b pb-2">
                        🔐 Informasi Akun
                    </h3>

                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Role</span>
                            <span class="text-gray-900 font-medium text-sm capitalize"><?php echo e($user->role); ?></span>
                        </div>

                        <!-- Status Akun (SEMUA ROLE) -->
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Status Akun</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($user->account_status['class']); ?>">
                                <span><?php echo e($user->account_status['dot']); ?></span>
                                <?php echo e($user->account_status['label']); ?>

                            </span>
                        </div>

                        <!-- Status Kalab (HANYA DOSEN/KALAB/ADMIN) -->
                        <?php if($user->canSeeKalabStatus()): ?>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500 text-sm">Status Kalab</span>
                                <?php if($user->kalab_status): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($user->kalab_status['class']); ?>">
                                        <?php echo e($user->kalab_status['label']); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">-</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500 text-sm">Terdaftar Sejak</span>
                            <span class="text-gray-900 font-medium text-sm">
                                <?php echo e($user->created_at?->format('d M Y') ?? '-'); ?>

                            </span>
                        </div>
                        
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-4 border-t border-gray-200 flex flex-wrap gap-3">
                <a href="<?php echo e(route('profile.edit')); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Perbarui Profil
                </a>
                <button type="button" onclick="confirmPasswordChange()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Ubah Password
                </button>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="password-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">🔐 Ubah Password</h3>
                <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="<?php echo e(route('profile.update-password')); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closePasswordModal()"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
function confirmPasswordChange() {
    document.getElementById('password-modal').classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('password-modal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('password-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closePasswordModal();
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/profile/show.blade.php ENDPATH**/ ?>