<?php $__env->startSection('title', 'Kelola Laboratorium - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">

    <!-- Header dengan Info Role -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🏢 Kelola Laboratorium</h1>
            <p class="text-gray-600">Manajemen data laboratorium</p>
        </div>

        <?php if(Auth::user()->isKalab()): ?>
            <div class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-lg text-sm font-medium mr-4">
                👔 Mode Kalab: <?php echo e(Auth::user()->lab_name ?? 'Semua Lab'); ?>

            </div>
        <?php endif; ?>

        <?php if(Auth::user()->isAdmin()): ?>
            <a href="<?php echo e(route('admin.labs.create')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                + Tambah Lab
            </a>
        <?php endif; ?>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Laboratorium</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo e($labs->total()); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Aktif</p>
            <p class="text-2xl font-bold text-green-600"><?php echo e($labs->filter(fn($l) => $l->status === 'active')->count()); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Non-Aktif</p>
            <p class="text-2xl font-bold text-gray-600"><?php echo e($labs->filter(fn($l) => $l->status === 'inactive')->count()); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Kapasitas</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e($labs->sum('capacity')); ?></p>
        </div>
    </div>

    <!-- ✅ Search & Filter dengan Reset Button -->
    <?php if(Auth::user()->isAdmin()): ?>
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form id="labSearchForm" method="GET" action="<?php echo e(route('admin.labs.index')); ?>" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px] relative">
                <input type="text" name="search" id="searchInput" placeholder="Cari nama/kode lab..."
                       value="<?php echo e(request('search')); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 pl-10"
                       autocomplete="off">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <div id="searchLoading" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                    <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>

            <select name="status" id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Aktif</option>
                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Non-Aktif</option>
            </select>

            <!-- Hidden Submit Button for AJAX -->
            <button type="submit" id="submitBtn" class="hidden"></button>
        </form>

        <!-- ✅ Tombol Reset - DI KIRI, dengan ID untuk JS, hanya muncul saat filter aktif -->
        <?php if(request()->anyFilled(['search', 'status'])): ?>
        <div id="resetButtonContainer" class="mt-3 pt-3 border-t border-gray-200 flex items-center">
            <button type="button" onclick="resetFilters()"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                <span>🔄</span> Reset semua filter
            </button>
        </div>
        <?php endif; ?>

        <p class="text-xs text-gray-400 mt-2 ml-1">💡 Ketik atau pilih filter untuk mencari otomatis...</p>
    </div>
    <?php endif; ?>

    <!-- Lab List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Laboratorium</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kapasitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="labTableBody">
                    <?php $__empty_1 = true; $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                                <?php echo e($lab->code); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($lab->name); ?></div>
                            <?php if($lab->description): ?>
                                <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo e(Str::limit($lab->description, 50)); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo e($lab->location ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo e($lab->capacity); ?> orang</td>
                        <td class="px-6 py-4">
                            <?php if($lab->status === 'active'): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">✓ Aktif</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">✗ Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <!-- ✅ AKSI: Satu Baris, Tanpa Icon -->
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-3 whitespace-nowrap">
                                <button type="button"
                                        onclick="openLabDetailModal(<?php echo e(json_encode($lab)); ?>)"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </button>
                                <span class="text-gray-300">|</span>
                                <a href="<?php echo e(route('admin.labs.edit', $lab)); ?>"
                                   class="text-green-600 hover:text-green-800 font-medium">
                                    Edit
                                </a>
                                <?php if(Auth::user()->isAdmin()): ?>
                                    <span class="text-gray-300">|</span>
                                    <form action="<?php echo e(route('admin.labs.destroy', $lab)); ?>" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus laboratorium <?php echo e($lab->name); ?>?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <p>Belum ada data laboratorium.</p>
                                <?php if(Auth::user()->isAdmin()): ?>
                                    <a href="<?php echo e(route('admin.labs.create')); ?>" class="text-blue-600 hover:underline font-medium">
                                        + Tambahkan laboratorium pertama
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ✅ Pagination dengan White Theme -->
        <?php if($labs->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-200 bg-white pagination-white-custom">
            <?php echo e($labs->appends(request()->query())->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ✅ SIMPLE MODAL DETAIL LABORATORIUM -->
<div id="labDetailModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeLabDetailModal()"></div>

    <!-- Modal Panel -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">

                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white" id="modal-title">🏢 Detail Laboratorium</h3>
                    <button type="button" onclick="closeLabDetailModal()" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-5 space-y-4">
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Kode Lab</span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold" id="modal-code"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Nama Laboratorium</span>
                        <span class="text-sm font-semibold text-gray-900" id="modal-name"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Lokasi</span>
                        <span class="text-sm text-gray-900" id="modal-location"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Kapasitas</span>
                        <span class="text-sm font-medium text-gray-900" id="modal-capacity"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Status</span>
                        <span id="modal-status"></span>
                    </div>
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500 block mb-2">Deskripsi</span>
                        <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg" id="modal-description"></p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" onclick="closeLabDetailModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Tutup
                    </button>
                    <?php if(Auth::user()->isAdmin() || Auth::user()->isKalab()): ?>
                    <a href="#" id="modal-edit-link" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Edit Lab
                    </a>
                    <?php endif; ?>
                </div>
            </div>
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
// ✅ Simple Modal Functions
function openLabDetailModal(lab) {
    document.getElementById('modal-code').textContent = lab.code;
    document.getElementById('modal-name').textContent = lab.name;
    document.getElementById('modal-location').textContent = lab.location || '-';
    document.getElementById('modal-capacity').textContent = lab.capacity + ' orang';

    const statusEl = document.getElementById('modal-status');
    if (lab.status === 'active') {
        statusEl.innerHTML = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">✓ Aktif</span>';
    } else {
        statusEl.innerHTML = '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">✗ Non-Aktif</span>';
    }

    const descEl = document.getElementById('modal-description');
    if (lab.description && lab.description.trim()) {
        descEl.textContent = lab.description;
        descEl.classList.remove('text-gray-400', 'italic');
    } else {
        descEl.textContent = 'Tidak ada deskripsi.';
        descEl.classList.add('text-gray-400', 'italic');
    }

    document.getElementById('modal-edit-link').href = `/admin/labs/${lab.id}/edit`;
    document.getElementById('labDetailModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeLabDetailModal() {
    document.getElementById('labDetailModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLabDetailModal();
    }
});

// ✅ Fungsi Reset Filter - FIX LENGKAP: Hide button + reload tabel
function resetFilters() {
    // 1. Clear all input values
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    if(searchInput) searchInput.value = '';
    if(statusFilter) statusFilter.value = '';

    // 2. Update URL to base route (remove query params)
    const baseUrl = "<?php echo e(route('admin.labs.index')); ?>";
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
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('labTableBody');
    const searchForm = document.getElementById('labSearchForm');

    // Collect all filter values (hanya jika ada value)
    const params = new URLSearchParams();
    if(searchInput && searchInput.value.trim()) params.append('search', searchInput.value.trim());
    if(statusFilter && statusFilter.value) params.append('status', statusFilter.value);

    // Update URL dengan params saat ini (kecuali force reset)
    if(!forceReset && params.toString()) {
        const newUrl = "<?php echo e(route('admin.labs.index')); ?>" + "?" + params.toString();
        window.history.replaceState({}, '', newUrl);
    }

    params.append('ajax', '1');

    const baseUrl = "<?php echo e(route('admin.labs.index')); ?>";
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
        const newBody = doc.getElementById('labTableBody');
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
    const statusFilter = document.getElementById('statusFilter');
    const searchForm = document.getElementById('labSearchForm');

    let debounceTimer;

    // ✅ Trigger search on input with debounce (300ms)
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => performAjaxSearch(false), 300);
        });
    }

    // ✅ Trigger search on status filter change (immediate)
    if(statusFilter) {
        statusFilter.addEventListener('change', () => performAjaxSearch(false));
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/admin/labs/index.blade.php ENDPATH**/ ?>