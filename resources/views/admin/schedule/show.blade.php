@extends('layouts.app')

@section('title', 'Detail Booking')

@section('content')
@php
    $authUser = Auth::user();

    $hasApproveAction =
        ($authUser->isDosen() && $booking->status === 'pending') ||
        ($authUser->isTeknisi() && in_array($booking->status, ['pending', 'approved_dosen'])) ||
        (($authUser->isAdmin() || $authUser->isKalab() || $authUser->role === 'ketua_lab') && $booking->status === 'approved_teknisi');

    $hasDeleteAction =
        ($authUser->isAdmin() || $authUser->isKalab()) &&
        !in_array($booking->status, ['rejected', 'cancelled']);

    $hasAnyAction = $hasApproveAction || $hasDeleteAction;

    $canDownload = $booking->status === 'confirmed';

    $isDosenBooking = ($booking->user->role ?? '') === 'dosen';
@endphp

<div class="max-w-5xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.schedule.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center gap-2 text-sm font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar Booking
        </a>
        <h1 class="text-3xl font-bold text-gray-800">📋 Detail Booking</h1>
        <p class="text-gray-600 mt-1">Informasi lengkap peminjaman laboratorium</p>
    </div>

    <!-- Notifikasi -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-300 rounded-lg text-green-800 flex items-center gap-3">
        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-300 rounded-lg text-red-700">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <!-- ================================================================ -->
    <!-- CARD UTAMA: Info Booking                                          -->
    <!-- ================================================================ -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">

        <!-- Header card: nama lab + status -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $booking->lab_name }}</h2>
                    <p class="text-sm text-gray-500">{{ $booking->session }} &bull; {{ \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMMM Y') }}</p>
                </div>
            </div>
            <span class="px-4 py-1.5 text-sm font-semibold rounded-full {{ \App\Http\Controllers\BookingController::getStatusBadgeClass($booking->status) }}">
                {{ \App\Http\Controllers\BookingController::getStatusLabel($booking->status) }}
            </span>
        </div>

        <div class="p-6">

            <!-- Grid Info Pemohon / Jadwal / Kegiatan -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">

                <!-- Data Pemohon -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">👤 Data Pemohon</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-1.5">
                        <p class="font-bold text-gray-800">{{ $booking->user->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-600">
                            {{ ($booking->user->role ?? '') === 'mahasiswa' ? 'NIM: ' . ($booking->user->nim ?? 'N/A') : 'NIP: ' . ($booking->user->nip ?? 'N/A') }}
                        </p>
                        <p class="text-sm text-gray-500 break-all">{{ $booking->user->email ?? '-' }}</p>
                        <p class="text-sm text-gray-600">📞 {{ $booking->phone ?? '-' }}</p>
                        @if(($booking->user->role ?? '') === 'mahasiswa')
                            <p class="text-xs text-gray-400 pt-1 border-t border-gray-200">
                                {{ $booking->prodi ?? 'Teknik Informatika' }} — Gol. {{ $booking->golongan ?? '-' }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Jadwal -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">📅 Jadwal Peminjaman</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal Mulai</span>
                            <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->start_date ?? $booking->booking_date)->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 border-gray-100">
                            <span class="text-gray-500">Tanggal Selesai</span>
                            <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->end_date ?? $booking->booking_date)->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 border-gray-100">
                            <span class="text-gray-500">Waktu</span>
                            <span class="font-semibold text-gray-800">{{ substr($booking->start_time,0,5) }} – {{ substr($booking->end_time,0,5) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 border-gray-100">
                            <span class="text-gray-500">Sesi</span>
                            <span class="font-semibold text-gray-800">{{ $booking->session ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 border-gray-100">
                            <span class="text-gray-500">Durasi</span>
                            <span class="font-semibold text-blue-600">{{ $booking->duration_days ?? 1 }} Hari</span>
                        </div>
                    </div>
                </div>

                <!-- Kegiatan -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">📚 Rincian Kegiatan</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-2">
                        <div>
                            <span class="text-xs text-gray-400">Jenis Kegiatan</span>
                            <p class="font-semibold text-gray-800 text-sm">{{ $booking->activity ?? '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-100">
                            <span class="text-xs text-gray-400">Keperluan</span>
                            <p class="text-sm text-gray-700 italic">{{ $booking->purpose ?? '-' }}</p>
                        </div>
                        @if($booking->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <span class="text-xs text-gray-400">Kebutuhan Khusus</span>
                            <p class="text-sm text-gray-700">{{ $booking->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Anggota Kelompok -->
                @if($booking->is_group && !empty($booking->members) && is_array($booking->members))
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">👥 Anggota Kelompok ({{ count($booking->members) }})</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <ul class="space-y-1.5 text-sm text-gray-700">
                            @foreach($booking->members as $memberId)
                                @php $member = \App\Models\User::find($memberId); @endphp
                                @if($member)
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                                    <span class="font-medium">{{ $member->name }}</span>
                                    <span class="text-xs text-gray-400">({{ $member->nim ?? '-' }})</span>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Dosen Pembimbing -->
                @if($booking->supervisor_id && $booking->supervisor)
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">👨‍🏫 Dosen Pembimbing</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <p class="font-semibold text-gray-800">{{ $booking->supervisor->name }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $booking->supervisor->email }}</p>
                    </div>
                </div>
                @endif

                <!-- Info Sistem -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">⚙️ Informasi Sistem</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ID Booking</span>
                            <span class="font-mono font-bold text-gray-800">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-1.5 border-gray-100">
                            <span class="text-gray-500">Diajukan oleh</span>
                            <span class="font-semibold text-gray-800">{{ ucfirst($booking->user->role ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-1.5 border-gray-100">
                            <span class="text-gray-500">Dibuat pada</span>
                            <span class="text-gray-700">{{ \Carbon\Carbon::parse($booking->created_at)->isoFormat('D MMM YYYY, HH:mm') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TIMELINE PERSETUJUAN                                          -->
            <!-- ============================================================ -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Timeline Persetujuan
                </h3>

                @php
                    $dosenApprover   = $booking->approvedByDosen;
                    $teknisiApprover = $booking->approvedByTeknisi;
                    $kalabApprover   = $booking->approvedByKalab;
                    $rejectedBy      = $booking->rejectedBy;

                    $stepDosen   = in_array($booking->status, ['approved_dosen','approved_teknisi','confirmed']);
                    $stepTeknisi = in_array($booking->status, ['approved_teknisi','confirmed']);
                    $stepKalab   = $booking->status === 'confirmed';
                    $isRejected  = $booking->status === 'rejected';
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    {{-- ── STEP 1: DOSEN ── --}}
                    <div class="rounded-xl border-2 p-4
                        {{ $stepDosen ? 'border-green-200 bg-green-50'
                            : ($isRejected && !$dosenApprover ? 'border-red-200 bg-red-50'
                            : 'border-gray-200 bg-gray-50') }}">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold
                                {{ $stepDosen ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $stepDosen ? '✓' : '1' }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 leading-tight">Persetujuan Dosen</p>
                                @if($stepDosen)
                                    <span class="text-xs font-semibold text-green-600">
                                        {{ $isDosenBooking ? 'Auto-disetujui' : 'Disetujui' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Menunggu</span>
                                @endif
                            </div>
                        </div>
                        @if($dosenApprover)
                            <div class="bg-white rounded-lg p-3 border border-green-200 space-y-0.5">
                                <p class="text-xs text-gray-400">
                                    {{ $isDosenBooking ? 'Diajukan & disetujui oleh' : 'Disetujui oleh' }}
                                </p>
                                <p class="text-sm font-bold text-gray-800">{{ $dosenApprover->name }}</p>
                                @if($booking->approved_at_dosen)
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($booking->approved_at_dosen)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Menunggu persetujuan dosen pembimbing.</p>
                        @endif
                    </div>

                    {{-- ── STEP 2: TEKNISI ── --}}
                    <div class="rounded-xl border-2 p-4
                        {{ $stepTeknisi ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold
                                {{ $stepTeknisi ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $stepTeknisi ? '✓' : '2' }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 leading-tight">Verifikasi Teknisi</p>
                                @if($stepTeknisi)
                                    <span class="text-xs font-semibold text-green-600">Diverifikasi</span>
                                @else
                                    <span class="text-xs text-gray-400">Menunggu</span>
                                @endif
                            </div>
                        </div>
                        @if($teknisiApprover)
                            <div class="bg-white rounded-lg p-3 border border-green-200 space-y-0.5">
                                <p class="text-xs text-gray-400">Diverifikasi oleh</p>
                                <p class="text-sm font-bold text-gray-800">{{ $teknisiApprover->name }}</p>
                                @if($booking->approved_at_teknisi)
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($booking->approved_at_teknisi)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Menunggu verifikasi ketersediaan alat dan fasilitas lab.</p>
                        @endif
                    </div>

                    {{-- ── STEP 3: KA LAB ── --}}
                    <div class="rounded-xl border-2 p-4
                        {{ $stepKalab ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold
                                {{ $stepKalab ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $stepKalab ? '✓' : '3' }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 leading-tight">Konfirmasi Ka Lab</p>
                                @if($stepKalab)
                                    <span class="text-xs font-semibold text-green-600">Dikonfirmasi</span>
                                @else
                                    <span class="text-xs text-gray-400">Menunggu</span>
                                @endif
                            </div>
                        </div>
                        @if($kalabApprover)
                            <div class="bg-white rounded-lg p-3 border border-green-200 space-y-0.5">
                                <p class="text-xs text-gray-400">Dikonfirmasi oleh</p>
                                <p class="text-sm font-bold text-gray-800">{{ $kalabApprover->name }}</p>
                                @if($booking->approved_at_kalab)
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($booking->approved_at_kalab)->isoFormat('D MMM YYYY, HH:mm') }}</p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Menunggu konfirmasi final dari Ketua Laboratorium.</p>
                        @endif
                    </div>

                </div>{{-- end grid timeline --}}
            </div>

            <!-- Rejected / Cancelled notice -->
            @if($booking->status === 'rejected')
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <p class="text-sm font-bold text-red-800">Booking Ditolak
                        @if($rejectedBy) — oleh {{ $rejectedBy->name }}@endif
                    </p>
                    <p class="text-sm text-red-700 mt-0.5">{{ $booking->rejection_reason ?? 'Alasan tidak tersedia.' }}</p>
                </div>
            </div>
            @endif
            @if($booking->status === 'cancelled')
            <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="text-sm font-bold text-gray-800">Booking Dibatalkan</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $booking->rejection_reason ?? 'Booking dibatalkan.' }}</p>
                </div>
            </div>
            @endif

        </div>{{-- end card body --}}
    </div>{{-- end card utama --}}

    <!-- ================================================================ -->
    <!-- BAGIAN BAWAH: Aksi (kiri) + Ringkasan (kanan)                    -->
    <!-- ================================================================ -->
    <div class="grid grid-cols-1 {{ $hasAnyAction ? 'lg:grid-cols-2' : '' }} gap-6">

        {{-- KIRI: Tombol Aksi (hanya tampil jika ada aksi) --}}
        @if($hasAnyAction)
        <div class="space-y-4">

            {{-- ── DOSEN ACTION ── --}}
            @if($authUser->isDosen() && $booking->status === 'pending')
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-5">
                <h3 class="text-base font-bold text-blue-800 mb-1">🔎 Review & Keputusan</h3>
                <p class="text-sm text-blue-600 mb-4">Booking diajukan oleh mahasiswa. Setujui atau tolak sesuai kebutuhan.</p>
                <div class="flex gap-3">
                    <form id="form-approve-dosen" action="{{ route('booking.approve-dosen', $booking) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="button"
                            onclick="openApproveModal('form-approve-dosen','Setujui Booking','Yakin menyetujui booking ini? Akan diteruskan ke Teknisi untuk verifikasi.')"
                            class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm transition-colors">
                            ✅ Setujui
                        </button>
                    </form>
                    <button onclick="openRejectModal()" class="w-1/3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 rounded-lg font-medium text-sm transition-colors">Tolak</button>
                </div>
            </div>
            @endif

            {{-- ── TEKNISI ACTION ── --}}
            @if($authUser->isTeknisi() && in_array($booking->status, ['pending','approved_dosen']))
            <div class="bg-orange-50 rounded-xl border border-orange-200 p-5">
                <h3 class="text-base font-bold text-orange-800 mb-1">⚠️ Verifikasi Teknis</h3>
                <p class="text-sm text-orange-600 mb-4">Pastikan peralatan tersedia dan kondisi lab layak digunakan.</p>
                <div class="flex gap-3">
                    <form id="form-approve-teknisi" action="{{ route('booking.approve-teknisi', $booking) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="button"
                            onclick="openApproveModal('form-approve-teknisi','Konfirmasi Ketersediaan Lab','Yakin peralatan dan fasilitas tersedia? Booking akan diteruskan ke Ka Lab.')"
                            class="w-full py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium text-sm transition-colors">
                            ✅ Konfirmasi Tersedia
                        </button>
                    </form>
                    <button onclick="openRejectModal()" class="w-1/3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 rounded-lg font-medium text-sm transition-colors">Tolak</button>
                </div>
            </div>
            @endif

            {{-- ── KA LAB ACTION ── --}}
            @if(($authUser->isAdmin() || $authUser->isKalab() || $authUser->role === 'ketua_lab') && $booking->status === 'approved_teknisi')
            <div class="bg-purple-50 rounded-xl border border-purple-200 p-5">
                <h3 class="text-base font-bold text-purple-800 mb-1">📝 Konfirmasi Final</h3>
                <p class="text-sm text-purple-600 mb-4">Tahap akhir. Setelah dikonfirmasi, peminjam mendapat notifikasi.</p>
                <div class="flex gap-3">
                    <form id="form-approve-kalab" action="{{ route('booking.approve-kalab', $booking) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="button"
                            onclick="openApproveModal('form-approve-kalab','Konfirmasi Final (Ka Lab)','Ini tahap akhir. Booking akan berstatus DIKONFIRMASI. Lanjutkan?')"
                            class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium text-sm transition-colors">
                            🔓 Konfirmasi Final
                        </button>
                    </form>
                    <button onclick="openRejectModal()" class="w-1/3 py-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 rounded-lg font-medium text-sm transition-colors">Tolak</button>
                </div>
            </div>
            @endif

            {{-- ── CANCEL/DELETE ACTION (Admin & Kalab only) ── --}}
            @if($hasDeleteAction)
            <div class="bg-red-50 rounded-xl border border-red-200 p-5">
                <h3 class="text-sm font-bold text-red-800 mb-3">⚠️ Manajemen Booking</h3>
                <form action="{{ route('admin.schedule.cancel', $booking) }}" method="POST"
                      onsubmit="return confirm('Batalkan booking ini? Tindakan tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit" class="w-full py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg font-medium text-sm border border-red-200 transition-colors">
                        🗑️ Batalkan Booking
                    </button>
                </form>
            </div>
            @endif

        </div>{{-- end kolom kiri --}}
        @endif

        <!-- KANAN: Ringkasan + Download -->
        <div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">

                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">📋 Ringkasan Booking</h3>

                <!-- Baris ringkasan -->
                <div class="space-y-1.5 text-sm">
                    @php
                        $rows = [
                            ['ID Booking',  '#'.str_pad($booking->id,5,'0',STR_PAD_LEFT)],
                            ['Laboratorium', $booking->lab_name],
                            ['Tanggal',      \Carbon\Carbon::parse($booking->booking_date)->isoFormat('D MMM YYYY')],
                            ['Waktu',        substr($booking->start_time,0,5).' – '.substr($booking->end_time,0,5)],
                            ['Durasi',       ($booking->duration_days ?? 1).' Hari'],
                            ['Diajukan',     \Carbon\Carbon::parse($booking->created_at)->isoFormat('D MMM YYYY, HH:mm')],
                        ];
                    @endphp
                    @foreach($rows as [$label, $value])
                    <div class="flex justify-between items-start py-1.5 border-b border-gray-100 last:border-0">
                        <span class="text-gray-500 flex-shrink-0">{{ $label }}</span>
                        <span class="font-semibold text-gray-800 text-right ml-4 break-words max-w-[60%]">{{ $value }}</span>
                    </div>
                    @endforeach

                    {{-- Status --}}
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                        <span class="text-gray-500">Status</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ \App\Http\Controllers\BookingController::getStatusBadgeClass($booking->status) }}">
                            {{ \App\Http\Controllers\BookingController::getStatusLabel($booking->status) }}
                        </span>
                    </div>
                </div>

                {{-- Progress persetujuan --}}
                <div class="pt-2 border-t border-gray-100">
                    @php $progress = $booking->getApprovalProgress(); @endphp
                    <p class="text-xs text-gray-500 mb-1.5">Progress Persetujuan</p>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all {{ $progress === 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                             style="width: {{ $progress }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $progress }}% — {{ $booking->getCurrentApprovalStep() }}</p>
                </div>

                {{-- Tombol unduh (hanya jika confirmed) --}}
                @if($booking->status === 'confirmed')
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-xs font-bold text-gray-600 mb-2">📄 Dokumen Resmi</p>
                    @if($canDownload)
                        <a href="{{ route('booking.download-approved', $booking) }}" target="_blank"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Unduh Formulir PDF
                        </a>
                        <p class="text-xs text-gray-400 text-center mt-1.5">Formulir resmi untuk tanda tangan basah</p>
                    @endif
                </div>
                @endif

                <p class="text-xs text-gray-400 text-center pt-2 border-t border-gray-100">
                    SiPinLab — Politeknik Negeri Jember {{ date('Y') }}
                </p>
            </div>
        </div>

    </div>{{-- end grid bawah --}}

</div>{{-- end max-w --}}

<!-- ================================================================ -->
<!-- MODALS                                                            -->
<!-- ================================================================ -->

<!-- Modal Approve -->
<div id="approveModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h3 id="approveModalTitle" class="text-lg font-bold text-white">Konfirmasi Persetujuan</h3>
            <button type="button" onclick="closeApproveModal()" class="text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            <p id="approveModalBody" class="text-gray-700 mb-5 text-sm leading-relaxed"></p>
            <div class="flex gap-3">
                <button type="button" onclick="closeApproveModal()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">Batal</button>
                <button type="button" id="approveConfirmBtn" class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h3 class="text-lg font-bold text-white">❌ Tolak Booking</h3>
            <button type="button" onclick="closeRejectModal()" class="text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="px-6 py-5">
            <p class="text-gray-700 mb-3 text-sm">Berikan alasan penolakan agar pemohon dapat memperbaruinya.</p>
            <form action="{{ route('booking.reject', $booking) }}" method="POST">
                @csrf
                <textarea name="rejection_reason" required rows="4" maxlength="500"
                    placeholder="Contoh: Jadwal bentrok dengan kuliah..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">{{ old('rejection_reason') }}</textarea>
                <div class="flex gap-3 mt-4">
                    <button type="button" onclick="closeRejectModal()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">Batal</button>
                    <button type="submit" class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Submit Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openRejectModal() { document.getElementById('rejectModal').classList.remove('hidden'); }
function closeRejectModal() { document.getElementById('rejectModal').classList.add('hidden'); }
document.getElementById('rejectModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeRejectModal(); });

let _approveFormId = null;
function openApproveModal(formId, title, body) {
    _approveFormId = formId;
    document.getElementById('approveModalTitle').textContent = title;
    document.getElementById('approveModalBody').textContent = body;
    document.getElementById('approveModal').classList.remove('hidden');
}
function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    _approveFormId = null;
}
document.getElementById('approveConfirmBtn').addEventListener('click', () => {
    if (_approveFormId) document.getElementById(_approveFormId).submit();
});
document.getElementById('approveModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeApproveModal(); });
</script>
@endpush

@endsection