<?php $__env->startSection('title', 'Kelola Jadwal - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">

    <!-- Header dengan Info Role -->
    <div class="mb-8 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">📋 Kelola Jadwal Laboratorium</h1>
            <p class="text-gray-600">Pantau dan kelola semua booking</p>
        </div>

        <!-- Role Badge -->
        <?php if(Auth::user()->isKalab() || Auth::user()->isTeknisi()): ?>
            <div class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-lg text-sm font-medium">
                <?php if(Auth::user()->isKalab()): ?>
                    👔 Mode Kalab: <?php echo e(Auth::user()->lab_name ?? 'Semua Lab'); ?>

                <?php else: ?>
                    🔧 Mode Teknisi: <?php echo e(Auth::user()->lab_name ?? 'Semua Lab'); ?>

                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ✅ Filter Form (Live Search + Filters) -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form id="scheduleSearchForm" method="GET" action="<?php echo e(route('admin.schedule.index')); ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">

            <!-- ✅ Live Search Input -->
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" id="searchInput" placeholder="Cari nama user, NIM, NIP..."
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
                        <option value="<?php echo e($name); ?>" <?php echo e(request('lab') == $name ? 'selected' : ''); ?>>
                            <?php echo e($name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Date Range -->
            <div>
                <input type="date" name="start_date" id="startDate" value="<?php echo e(request('start_date')); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <input type="date" name="end_date" id="endDate" value="<?php echo e(request('end_date')); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Status Filter + Hidden Submit -->
            <div class="flex gap-2">
                <select name="status" id="statusFilter" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>⏳ Pending</option>
                    <option value="approved_dosen" <?php echo e(request('status') == 'approved_dosen' ? 'selected' : ''); ?>>✅ Dosen</option>
                    <option value="approved_teknisi" <?php echo e(request('status') == 'approved_teknisi' ? 'selected' : ''); ?>>✅ Teknisi</option>
                    <option value="approved_kalab" <?php echo e(request('status') == 'approved_kalab' ? 'selected' : ''); ?>>✅ Kalab</option>
                    <option value="confirmed" <?php echo e(request('status') == 'confirmed' ? 'selected' : ''); ?>>🎉 Confirmed</option>
                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>❌ Rejected</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>🗑️ Cancelled</option>
                </select>
                <button type="submit" id="submitBtn" class="hidden"></button>
            </div>
        </form>

        <?php if(request()->anyFilled(['search', 'lab', 'start_date', 'end_date', 'status'])): ?>
        <div class="mt-3 pt-3 border-t border-gray-200">
            <a href="<?php echo e(route('admin.schedule.index')); ?>" class="text-sm text-gray-500 hover:text-gray-700">
                🔄 Reset semua filter
            </a>
        </div>
        <?php endif; ?>
        <p class="text-xs text-gray-400 mt-2">💡 Ketik atau pilih filter untuk mencari otomatis...</p>
    </div>

    <!-- Stats Cards -->
    <?php if(isset($stats)): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Total Booking</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo e($stats['total_booking'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Confirmed</p>
            <p class="text-2xl font-bold text-green-600"><?php echo e($stats['confirmed'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600"><?php echo e($stats['pending'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <p class="text-sm text-gray-500">Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e($stats['hari_ini'] ?? 0); ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Booking List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sesi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="bookingTableBody">
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm">
                            <div>
                                <div class="font-medium text-gray-900"><?php echo e($booking->user->name); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php echo e(ucfirst($booking->user->role)); ?>

                                    <?php if($booking->user->role === 'mahasiswa' && $booking->user->nim): ?>
                                        • <?php echo e($booking->user->nim); ?>

                                    <?php elseif($booking->user->role !== 'mahasiswa' && $booking->user->nip): ?>
                                        • <?php echo e($booking->user->nip); ?>

                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($booking->lab_name); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?php echo e(\Carbon\Carbon::parse($booking->booking_date)->isoFormat('DD MMM YYYY')); ?>

                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?php echo e($booking->session); ?><br>
                            <span class="text-xs text-gray-400">
                                <?php echo e($booking->start_time); ?> - <?php echo e($booking->end_time); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold
                                <?php if($booking->status === 'confirmed'): ?> bg-green-100 text-green-800
                                <?php elseif($booking->status === 'pending'): ?> bg-yellow-100 text-yellow-800
                                <?php elseif($booking->status === 'approved_dosen'): ?> bg-blue-100 text-blue-800
                                <?php elseif($booking->status === 'approved_teknisi'): ?> bg-indigo-100 text-indigo-800
                                <?php elseif($booking->status === 'approved_kalab'): ?> bg-purple-100 text-purple-800
                                <?php elseif($booking->status === 'rejected'): ?> bg-red-100 text-red-800
                                <?php elseif($booking->status === 'cancelled'): ?> bg-gray-100 text-gray-800
                                <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $booking->status))); ?>

                            </span>
                        </td>
                        <!-- ✅ AKSI: Satu Baris, Tanpa Icon -->
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center gap-3 whitespace-nowrap">
                                <a href="<?php echo e(route('admin.schedule.show', $booking)); ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </a>
                                <span class="text-gray-300">|</span>
                                <button onclick="openStatusModal(<?php echo e($booking->id); ?>, '<?php echo e($booking->status); ?>')" class="text-green-600 hover:text-green-800 font-medium">
                                    Status
                                </button>
                                <?php if(!Auth::user()->isTeknisi() && !in_array($booking->status, ['cancelled', 'rejected'])): ?>
                                    <span class="text-gray-300">|</span>
                                    <form action="<?php echo e(route('admin.schedule.cancel', $booking)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" onclick="return confirm('Batalkan booking ini?')" class="text-red-600 hover:text-red-800 font-medium">
                                            Batal
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p>Tidak ada data booking yang sesuai filter.</p>
                                <a href="<?php echo e(route('admin.schedule.index')); ?>" class="text-blue-600 hover:underline">
                                    🔄 Reset filter
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

       <!-- ✅ Pagination dengan warna putih -->
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            <?php echo e($bookings->links('vendor.pagination.white')); ?>

        </div>
    </div>

</div>

<!-- ======================================================================== -->
<!-- ✅ MODAL: Update Status Booking -->
<!-- ======================================================================== -->
<div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">✏️ Ubah Status Booking</h3>
            <button type="button" onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="statusForm" method="POST">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                <select name="status" id="statusSelect" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="pending">⏳ Pending</option>
                    <option value="approved_dosen">✅ Disetujui Dosen</option>
                    <?php if(Auth::user()->isTeknisi()): ?>
                        <option value="approved_teknisi">✅ Disetujui Teknisi (Final)</option>
                        <option value="rejected">❌ Rejected</option>
                    <?php else: ?>
                        <option value="approved_teknisi">✅ Disetujui Teknisi</option>
                        <option value="approved_kalab">✅ Disetujui Ka Lab</option>
                        <option value="confirmed">🎉 Confirmed</option>
                        <option value="rejected">❌ Rejected</option>
                        <option value="cancelled">🗑️ Cancelled</option>
                    <?php endif; ?>
                </select>
                <?php if(Auth::user()->isTeknisi()): ?>
                    <p class="text-xs text-indigo-600 mt-1">
                        ℹ️ Teknisi hanya dapat menyetujui sampai tahap "Disetujui Teknisi". Approval final dilakukan oleh Ka Lab.
                    </p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea name="admin_note" rows="2"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                          placeholder="Tambahkan catatan untuk user..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeStatusModal()"
                        class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">Batal</button>
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">✅ Update</button>
            </div>
        </form>
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
// ✅ Modal Functions for Status Update
let currentBookingId = null;

function openStatusModal(bookingId, currentStatus) {
    currentBookingId = bookingId;
    const statusSelect = document.getElementById('statusSelect');
    statusSelect.value = currentStatus;

    const isTeknisi = <?php echo e(Auth::user()->isTeknisi() ? 'true' : 'false'); ?>;
    if (isTeknisi) {
        const forbiddenValues = ['approved_kalab', 'confirmed', 'cancelled'];
        Array.from(statusSelect.options).forEach(option => {
            if (forbiddenValues.includes(option.value)) {
                option.disabled = true;
                option.style.display = 'none';
            }
        });
    }

    document.getElementById('statusForm').action = '/admin/schedule/' + bookingId + '/update-status';
    document.getElementById('statusModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    document.body.style.overflow = '';
    currentBookingId = null;

    const statusSelect = document.getElementById('statusSelect');
    Array.from(statusSelect.options).forEach(option => {
        option.disabled = false;
        option.style.display = '';
    });
}

document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeStatusModal();
});

// ✅ LIVE SEARCH & FILTER dengan Debounce - FIX LENGKAP
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const labFilter = document.getElementById('labFilter');
    const statusFilter = document.getElementById('statusFilter');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const searchForm = document.getElementById('scheduleSearchForm');
    const tableBody = document.getElementById('bookingTableBody');
    const loadingIndicator = document.getElementById('searchLoading');

    let debounceTimer;

    function performAjaxSearch() {
        if(loadingIndicator) loadingIndicator.classList.remove('hidden');

        // Collect all filter values
        const params = new URLSearchParams();
        if(searchInput.value) params.append('search', searchInput.value);
        if(labFilter.value) params.append('lab', labFilter.value);
        if(statusFilter.value) params.append('status', statusFilter.value);
        if(startDate.value) params.append('start_date', startDate.value);
        if(endDate.value) params.append('end_date', endDate.value);
        params.append('ajax', '1'); // Mark as AJAX request

        const url = "<?php echo e(route('admin.schedule.index')); ?>" + "?" + params.toString();

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
            const newBody = doc.getElementById('bookingTableBody');
            if(newBody && tableBody) {
                tableBody.innerHTML = newBody.innerHTML;
            }

            // Update pagination - find nav[role="navigation"] in response
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
            searchForm.submit();
        });
    }

    // ✅ Trigger search on input with debounce (300ms)
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performAjaxSearch, 300);
        });
    }

    // ✅ Trigger search on filter change (immediate)
    if(labFilter) labFilter.addEventListener('change', performAjaxSearch);
    if(statusFilter) statusFilter.addEventListener('change', performAjaxSearch);
    if(startDate) startDate.addEventListener('change', performAjaxSearch);
    if(endDate) endDate.addEventListener('change', performAjaxSearch);

    // ✅ Prevent default form submit, use AJAX instead
    if(searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performAjaxSearch();
        });
    }

    // ✅ Auto-hide forbidden options for Teknisi on page load
    const isTeknisi = <?php echo e(Auth::user()->isTeknisi() ? 'true' : 'false'); ?>;
    if (isTeknisi) {
        const statusSelect = document.getElementById('statusSelect');
        if (statusSelect) {
            const forbiddenValues = ['approved_kalab', 'confirmed', 'cancelled'];
            Array.from(statusSelect.options).forEach(option => {
                if (forbiddenValues.includes(option.value)) {
                    option.disabled = true;
                    option.style.display = 'none';
                }
            });
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\project\laravel_project\sipinlab\resources\views/admin/schedule/index.blade.php ENDPATH**/ ?>