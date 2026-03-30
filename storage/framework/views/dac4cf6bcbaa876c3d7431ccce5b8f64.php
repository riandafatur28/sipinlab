<?php $__env->startSection('title', 'Jadwal Kuliah Lab - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Jadwal Kuliah Laboratorium</h1>
            <p class="text-gray-600">Kelola jadwal kuliah regular yang menggunakan lab</p>
        </div>
        <a href="<?php echo e(route('admin.class-schedules.create')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            + Tambah Jadwal
        </a>
    </div>

    <!-- ✅ Filter Form (Live Search + Filters) -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form id="scheduleSearchForm" method="GET" action="<?php echo e(route('admin.class-schedules.index')); ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <!-- ✅ Live Search Input -->
            <div class="relative">
                <input type="text" name="search" id="searchInput" placeholder="Cari mata kuliah..."
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

            <!-- Lab Filter -->
            <div>
                <select name="lab" id="labFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Lab</option>
                    <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($name); ?>" <?php echo e(request('lab') == $name ? 'selected' : ''); ?>><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Golongan Filter -->
            <div>
                <select name="golongan" id="golonganFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Golongan</option>
                    <?php $__currentLoopData = $golongans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($gol); ?>" <?php echo e(request('golongan') == $gol ? 'selected' : ''); ?>>Golongan <?php echo e($gol); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Day Filter -->
            <div>
                <select name="day" id="dayFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Hari</option>
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($day); ?>" <?php echo e(request('day') == $day ? 'selected' : ''); ?>><?php echo e($day); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Status Filter + Hidden Submit -->
            <div class="flex gap-2">
                <select name="status" id="statusFilter" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
                <!-- Hidden Submit Button for AJAX -->
                <button type="submit" id="submitBtn" class="hidden"></button>
            </div>
        </form>

        <!-- ✅ Tombol Reset - DI KIRI, dengan ID untuk JS, hanya muncul saat filter aktif -->
        <?php if(request()->anyFilled(['search', 'lab', 'golongan', 'day', 'status'])): ?>
        <div id="resetButtonContainer" class="mt-3 pt-3 border-t border-gray-200 flex items-center">
            <button type="button" onclick="resetFilters()"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                <span>🔄</span> Reset semua filter
            </button>
        </div>
        <?php endif; ?>

        <p class="text-xs text-gray-400 mt-2">💡 Ketik atau pilih filter untuk mencari otomatis...</p>
    </div>

    <!-- ✅ Stats Cards -->
    <?php if(isset($stats)): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Jadwal</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo e($stats['total_jadwal'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Mahasiswa Unik</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e($stats['total_mahasiswa_unik'] ?? 0); ?></p>
            <small class="text-xs text-gray-400">Berdasarkan Semester & Golongan</small>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Lab Terpakai</p>
            <p class="text-2xl font-bold text-indigo-600"><?php echo e($stats['lab_terpakai'] ?? 0); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Schedule List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gol</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hari</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosen</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="scheduleTableBody">
                    <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium text-gray-900"><?php echo e($schedule->course_name); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($schedule->course_code); ?> | Kelas <?php echo e($schedule->class_name); ?></div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($schedule->lab_name); ?></td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800 font-semibold">
                                <?php echo e($schedule->semester); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold
                                <?php if($schedule->golongan === 'A'): ?> bg-blue-100 text-blue-800
                                <?php elseif($schedule->golongan === 'B'): ?> bg-green-100 text-green-800
                                <?php elseif($schedule->golongan === 'C'): ?> bg-purple-100 text-purple-800
                                <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                <?php echo e($schedule->golongan); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($schedule->day); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($schedule->start_time); ?> - <?php echo e($schedule->end_time); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($schedule->lecturer->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php if($schedule->status === 'active'): ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">✓ Aktif</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">✗ Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <!-- ✅ AKSI: Satu Baris, Tanpa Icon -->
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-3 whitespace-nowrap">
                                <button type="button"
                                        onclick="openDetailModal(<?php echo e(json_encode($schedule)); ?>)"
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </button>
                                <span class="text-gray-300">|</span>
                                <a href="<?php echo e(route('admin.class-schedules.edit', $schedule)); ?>"
                                   class="text-green-600 hover:text-green-800 font-medium">
                                    Edit
                                </a>
                                <span class="text-gray-300">|</span>
                                <form action="<?php echo e(route('admin.class-schedules.destroy', $schedule)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" onclick="return confirm('Hapus jadwal ini?')"
                                            class="text-red-600 hover:text-red-800 font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            Belum ada jadwal kuliah. <a href="<?php echo e(route('admin.class-schedules.create')); ?>" class="text-blue-600 hover:underline">Tambahkan pertama</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ✅ Pagination dengan White Theme -->
       <!-- ✅ Pagination dengan warna putih -->
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            <?php echo e($schedules->links('vendor.pagination.white')); ?>

        </div>
    </div>

</div>

<!-- ✅ MODAL DETAIL JADWAL -->
<div id="detailModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white" id="modal-title">📋 Detail Jadwal</h3>
                    <button type="button" onclick="closeDetailModal()" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Mata Kuliah</span>
                        <span class="text-sm font-semibold text-gray-900 text-right" id="modal-course"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Kode / Kelas</span>
                        <span class="text-sm font-medium text-gray-900" id="modal-code-class"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Laboratorium</span>
                        <span class="text-sm font-medium text-gray-900" id="modal-lab"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Golongan</span>
                        <span class="text-sm" id="modal-golongan"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Semester</span>
                        <span class="text-sm font-medium text-gray-900" id="modal-semester"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Hari & Jam</span>
                        <span class="text-sm font-medium text-gray-900 text-right" id="modal-schedule"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Dosen Pengampu</span>
                        <span class="text-sm font-medium text-gray-900" id="modal-lecturer"></span>
                    </div>
                    <div class="flex justify-between items-start pb-3 border-b border-gray-100">
                        <span class="text-sm text-gray-500">Jumlah Mahasiswa</span>
                        <span class="text-sm font-medium text-gray-900" id="modal-students"></span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-sm text-gray-500">Status</span>
                        <span id="modal-status"></span>
                    </div>
                    <div id="modal-notes-container" class="hidden pt-2">
                        <span class="text-sm text-gray-500 block mb-1">Catatan</span>
                        <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg" id="modal-notes"></p>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Tutup
                    </button>
                    <a href="#" id="modal-edit-link" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Edit Jadwal
                    </a>
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
// ✅ Modal Functions
function openDetailModal(schedule) {
    document.getElementById('modal-course').textContent = schedule.course_name;
    document.getElementById('modal-code-class').textContent = `${schedule.course_code} | Kelas ${schedule.class_name}`;
    document.getElementById('modal-lab').textContent = schedule.lab_name;

    const golonganEl = document.getElementById('modal-golongan');
    const golongan = schedule.golongan;
    let golonganClass = 'bg-gray-100 text-gray-800';
    if (golongan === 'A') golonganClass = 'bg-blue-100 text-blue-800';
    else if (golongan === 'B') golonganClass = 'bg-green-100 text-green-800';
    else if (golongan === 'C') golonganClass = 'bg-purple-100 text-purple-800';

    golonganEl.innerHTML = `<span class="px-2 py-1 text-xs rounded-full font-semibold ${golonganClass}">${golongan}</span>`;

    document.getElementById('modal-semester').textContent = schedule.semester;
    document.getElementById('modal-schedule').textContent = `${schedule.day}, ${schedule.start_time} - ${schedule.end_time}`;
    document.getElementById('modal-lecturer').textContent = schedule.lecturer?.name || '-';
    document.getElementById('modal-students').textContent = schedule.students_count;

    const statusEl = document.getElementById('modal-status');
    if (schedule.status === 'active') {
        statusEl.innerHTML = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">✓ Aktif</span>';
    } else {
        statusEl.innerHTML = '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">✗ Non-Aktif</span>';
    }

    const notesContainer = document.getElementById('modal-notes-container');
    const notesEl = document.getElementById('modal-notes');
    if (schedule.notes && schedule.notes.trim()) {
        notesContainer.classList.remove('hidden');
        notesEl.textContent = schedule.notes;
    } else {
        notesContainer.classList.add('hidden');
    }

    document.getElementById('modal-edit-link').href = `/admin/class-schedules/${schedule.id}/edit`;
    document.getElementById('detailModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeDetailModal();
});

// ✅ Fungsi Reset Filter - FIX LENGKAP: Hide button + reload tabel
function resetFilters() {
    // 1. Clear all input values
    const searchInput = document.getElementById('searchInput');
    const labFilter = document.getElementById('labFilter');
    const golonganFilter = document.getElementById('golonganFilter');
    const dayFilter = document.getElementById('dayFilter');
    const statusFilter = document.getElementById('statusFilter');

    if(searchInput) searchInput.value = '';
    if(labFilter) labFilter.value = '';
    if(golonganFilter) golonganFilter.value = '';
    if(dayFilter) dayFilter.value = '';
    if(statusFilter) statusFilter.value = '';

    // 2. Update URL to base route (remove query params)
    const baseUrl = "<?php echo e(route('admin.class-schedules.index')); ?>";
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
    const labFilter = document.getElementById('labFilter');
    const golonganFilter = document.getElementById('golonganFilter');
    const dayFilter = document.getElementById('dayFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('scheduleTableBody');
    const searchForm = document.getElementById('scheduleSearchForm');

    // Collect all filter values (hanya jika ada value)
    const params = new URLSearchParams();
    if(searchInput && searchInput.value.trim()) params.append('search', searchInput.value.trim());
    if(labFilter && labFilter.value) params.append('lab', labFilter.value);
    if(golonganFilter && golonganFilter.value) params.append('golongan', golonganFilter.value);
    if(dayFilter && dayFilter.value) params.append('day', dayFilter.value);
    if(statusFilter && statusFilter.value) params.append('status', statusFilter.value);

    // Update URL dengan params saat ini (kecuali force reset)
    if(!forceReset && params.toString()) {
        const newUrl = "<?php echo e(route('admin.class-schedules.index')); ?>" + "?" + params.toString();
        window.history.replaceState({}, '', newUrl);
    }

    params.append('ajax', '1');

    const baseUrl = "<?php echo e(route('admin.class-schedules.index')); ?>";
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
        const newBody = doc.getElementById('scheduleTableBody');
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
    const labFilter = document.getElementById('labFilter');
    const golonganFilter = document.getElementById('golonganFilter');
    const dayFilter = document.getElementById('dayFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchForm = document.getElementById('scheduleSearchForm');

    let debounceTimer;

    // ✅ Trigger search on input with debounce (300ms)
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => performAjaxSearch(false), 300);
        });
    }

    // ✅ Trigger search on filter change (immediate)
    if(labFilter) labFilter.addEventListener('change', () => performAjaxSearch(false));
    if(golonganFilter) golonganFilter.addEventListener('change', () => performAjaxSearch(false));
    if(dayFilter) dayFilter.addEventListener('change', () => performAjaxSearch(false));
    if(statusFilter) statusFilter.addEventListener('change', () => performAjaxSearch(false));

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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/admin/class-schedules/index.blade.php ENDPATH**/ ?>