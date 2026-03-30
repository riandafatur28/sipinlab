<?php $__env->startSection('title', 'Kelola User - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kelola User</h1>
            <p class="text-gray-600">Manajemen pengguna sistem</p>
        </div>
        <a href="<?php echo e(route('admin.users.create')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            + Tambah User
        </a>
    </div>

    <!-- ✅ Search & Filter dengan Reset Button -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form id="userSearchForm" method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="flex gap-4">

            <!-- ✅ Live Search Input -->
            <div class="flex-1 relative">
                <input type="text" name="search" id="searchInput" placeholder="Cari nama, email, NIM, atau NIP..."
                       value="<?php echo e(request('search')); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 pl-10"
                       autocomplete="off">
                <!-- Search Icon -->
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <!-- Loading Spinner (hidden by default) -->
                <div id="searchLoading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                    <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>

            <!-- Role Filter -->
            <select name="role" id="roleFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Role</option>
                <option value="mahasiswa" <?php echo e(request('role') == 'mahasiswa' ? 'selected' : ''); ?>>Mahasiswa</option>
                <option value="dosen" <?php echo e(request('role') == 'dosen' ? 'selected' : ''); ?>>Dosen</option>
                <option value="teknisi" <?php echo e(request('role') == 'teknisi' ? 'selected' : ''); ?>>Teknisi</option>
                <option value="ketua_lab" <?php echo e(request('role') == 'ketua_lab' ? 'selected' : ''); ?>>Ka Lab</option>
            </select>

            <!-- Hidden Submit Button for AJAX -->
            <button type="submit" id="submitBtn" class="hidden"></button>
        </form>

        <!-- ✅ Tombol Reset - DI KIRI, dengan ID untuk JS, hanya muncul saat filter aktif -->
        <?php if(request()->anyFilled(['search', 'role'])): ?>
        <div id="resetButtonContainer" class="mt-3 pt-3 border-t border-gray-200 flex items-center">
            <button type="button" onclick="resetFilters()"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                <span>🔄</span> Reset semua filter
            </button>
        </div>
        <?php endif; ?>

        <p class="text-xs text-gray-400 mt-2 ml-1">💡 Ketik atau pilih filter untuk mencari otomatis...</p>
    </div>

    <!-- User List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIM / NIP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <!-- ✅ ID Table Body: userTableBody -->
                <tbody class="divide-y divide-gray-200" id="userTableBody">
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                    <?php echo e(substr($user->name, 0, 1)); ?>

                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($user->name); ?></div>
                                    <div class="text-sm text-gray-500">ID: <?php echo e($user->id); ?></div>
                                </div>
                            </div>
                        </td>

                        <!-- ✅ Email Column -->
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo e($user->email); ?>

                        </td>

                        <!-- ✅ NIM/NIP Column -->
                        <td class="px-6 py-4 text-sm">
                            <?php if($user->role === 'mahasiswa'): ?>
                                <span class="font-medium text-gray-900"><?php echo e($user->nim ?? '-'); ?></span>
                                <span class="text-xs text-gray-400 block">NIM</span>
                            <?php else: ?>
                                <span class="font-medium text-gray-900"><?php echo e($user->nip ?? '-'); ?></span>
                                <span class="text-xs text-gray-400 block">NIP</span>
                            <?php endif; ?>
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold
                                <?php if($user->role === 'mahasiswa'): ?> bg-blue-100 text-blue-800
                                <?php elseif($user->role === 'dosen'): ?> bg-green-100 text-green-800
                                <?php elseif($user->role === 'teknisi'): ?> bg-yellow-100 text-yellow-800
                                <?php elseif($user->role === 'ketua_lab'): ?> bg-purple-100 text-purple-800
                                <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?>

                            </span>
                        </td>

                        <!-- ✅ AKSI: Satu Baris, Tanpa Icon -->
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-3 whitespace-nowrap">
                                <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </a>
                                <span class="text-gray-300">|</span>
                                <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="text-green-600 hover:text-green-800 font-medium">
                                    Edit
                                </a>
                                <span class="text-gray-300">|</span>
                                <form action="<?php echo e(route('admin.users.reset-password', $user)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-orange-600 hover:text-orange-800 font-medium" onclick="return confirm('Reset password ke NIM/NIP?')">
                                        Reset
                                    </button>
                                </form>
                                <?php if($user->role !== 'admin'): ?>
                                    <span class="text-gray-300">|</span>
                                    <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium" onclick="return confirm('Hapus user ini?')">
                                            Hapus
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada data user ditemukan. <a href="<?php echo e(route('admin.users.create')); ?>" class="text-blue-600 hover:underline">Tambah user pertama</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

            <!-- ✅ Pagination dengan warna putih -->
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            <?php echo e($users->links('vendor.pagination.white')); ?>

        </div>
    </div>

</div>

<!-- ✅ CSS untuk White Pagination -->
<?php $__env->startPush('styles'); ?>
<style>
    .pagination-white-custom nav[role="navigation"] span,
    .pagination-white-custom nav[role="navigation"] a {
        background-color: #ffffff !important;
        color: #374151 !important;
        border: 1px solid #e5e7eb !important;
        margin: 0 2px !important;
        border-radius: 0.375rem !important;
        transition: all 0.2s ease !important;
        font-weight: 500;
    }
    .pagination-white-custom nav[role="navigation"] span:hover:not([aria-current="page"]):not([aria-disabled="true"]),
    .pagination-white-custom nav[role="navigation"] a:hover {
        background-color: #f3f4f6 !important;
        border-color: #d1d5db !important;
        color: #111827 !important;
    }
    .pagination-white-custom nav[role="navigation"] span[aria-current="page"],
    .pagination-white-custom nav[role="navigation"] [aria-current="page"] {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: #ffffff !important;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3) !important;
    }
    .pagination-white-custom nav[role="navigation"] span[aria-disabled="true"],
    .pagination-white-custom nav[role="navigation"] [aria-disabled="true"] {
        color: #9ca3af !important;
        background-color: #f9fafb !important;
        cursor: not-allowed !important;
    }
    .pagination-white-custom nav[role="navigation"] svg {
        width: 1.25rem;
        height: 1.25rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// ✅ Fungsi Reset Filter - FIX LENGKAP: Hide button + reload tabel
function resetFilters() {
    // 1. Clear all input values
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');

    if(searchInput) searchInput.value = '';
    if(roleFilter) roleFilter.value = '';

    // 2. Update URL to base route (remove query params)
    const baseUrl = "<?php echo e(route('admin.users.index')); ?>";
    window.history.replaceState({}, '', baseUrl);

    // 3. ✅ Hide reset button container
    const resetContainer = document.getElementById('resetButtonContainer');
    if(resetContainer) {
        resetContainer.style.display = 'none';
    }

    // 4. ✅ Force reload dengan AJAX params kosong
    performAjaxSearch(true); // true = force reset
}

// ✅ LIVE SEARCH & FILTER dengan Debounce - FIX LENGKAP
function performAjaxSearch(forceReset = false) {
    const loadingIndicator = document.getElementById('searchLoading');
    if(loadingIndicator) loadingIndicator.classList.remove('hidden');

    // Get elements
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const tableBody = document.getElementById('userTableBody');
    const searchForm = document.getElementById('userSearchForm');

    // Collect all filter values (hanya jika ada value)
    const params = new URLSearchParams();
    if(searchInput && searchInput.value.trim()) params.append('search', searchInput.value.trim());
    if(roleFilter && roleFilter.value) params.append('role', roleFilter.value);

    // Update URL dengan params saat ini (kecuali force reset)
    if(!forceReset && params.toString()) {
        const newUrl = "<?php echo e(route('admin.users.index')); ?>" + "?" + params.toString();
        window.history.replaceState({}, '', newUrl);
    }

    params.append('ajax', '1');

    const baseUrl = "<?php echo e(route('admin.users.index')); ?>";
    const url = baseUrl + (params.toString() ? "?" + params.toString() : "");

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html',
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.text();
    })
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Update table body
        const newBody = doc.getElementById('userTableBody');
        if(newBody && tableBody) {
            tableBody.innerHTML = newBody.innerHTML;
        }

        // Update pagination
        const newNav = doc.querySelector('nav[role="navigation"]');
        const oldNav = document.querySelector('.pagination-white-custom');
        if(newNav && oldNav) {
            oldNav.innerHTML = newNav.outerHTML;
        }

        if(loadingIndicator) loadingIndicator.classList.add('hidden');
    })
    .catch(error => {
        console.error('❌ Search error:', error);
        if(loadingIndicator) loadingIndicator.classList.add('hidden');
        // Fallback: submit form normally if AJAX fails
        if(searchForm) searchForm.submit();
    });
}

// ✅ Initialize event listeners on DOM load
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const searchForm = document.getElementById('userSearchForm');

    let debounceTimer;

    // ✅ Trigger search on input with debounce (300ms)
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => performAjaxSearch(false), 300);
        });
    }

    // ✅ Trigger search on role filter change (immediate)
    if(roleFilter) {
        roleFilter.addEventListener('change', () => performAjaxSearch(false));
    }

    // ✅ Prevent default form submit, use AJAX instead
    if(searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performAjaxSearch(false);
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/admin/users/index.blade.php ENDPATH**/ ?>