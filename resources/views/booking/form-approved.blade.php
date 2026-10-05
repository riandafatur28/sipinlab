<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Booking - {{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 11mm 15mm 10mm 15mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.3;
        }

        /* ── KOP SURAT ─────────────────────────────── */
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-logo-cell {
            width: 70px;
            text-align: center;
            vertical-align: middle;
        }
        .kop-logo-cell img { width: 52px; height: 52px; }
        .kop-info-cell {
            text-align: center;
            vertical-align: middle;
            padding: 0 6px;
        }
        .kop-inst  { font-size: 13pt; font-weight: bold; text-transform: uppercase; }
        .kop-dept  { font-size: 11pt; font-weight: bold; text-transform: uppercase; }
        .kop-prodi { font-size: 9pt; }
        .kop-addr  { font-size: 7.5pt; margin-top: 1px; }

        .kop-line-thick { border-top: 3px solid #000; margin-top: 2px; }
        .kop-line-thin  { border-top: 1px solid #000; margin-top: 1px; margin-bottom: 3px; }

        /* ── JUDUL ─────────────────────────────────── */
        .form-title-box { text-align: center; margin: 2px 0 3px 0; }
        .form-title-box h2 {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .form-nomor { font-size: 8pt; margin-top: 1px; }

        /* ── INFO TABLE ────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 9pt;
        }
        .info-table td {
            padding: 1.5px 3px;
            vertical-align: top;
        }
        .info-table td.lbl { width: 36%; }
        .info-table td.sep { width: 3%; }

        /* ── SECTION TITLE ─────────────────────────── */
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 5px 0 2px 0;
        }

        /* ── ANGGOTA TABLE ─────────────────────────── */
        .anggota-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            font-size: 9pt;
        }
        .anggota-table th, .anggota-table td {
            border: 1px solid #000;
            padding: 1.5px 4px;
        }
        .anggota-table th { font-weight: bold; text-align: center; }

        /* ── PERNYATAAN ────────────────────────────── */
        .pern-box {
            border: 1px solid #000;
            padding: 4px 8px;
            margin: 5px 0;
            font-size: 8.5pt;
        }
        .pern-title {
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .pern-intro { margin-bottom: 2px; }
        .pern-list { margin-left: 14px; }
        .pern-list li { margin-bottom: 1.5px; line-height: 1.25; text-align: justify; }
        .pern-note { font-size: 8pt; font-style: italic; margin-top: 2px; }

        /* ── SIGNATURE ─────────────────────────────── */
        .sig-section { margin-top: 16px; }

        .sig-table-top {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-table-top td {
            width: 50%;
            text-align: center;
            padding: 4px 8px 6px 8px;
            vertical-align: top;
        }

        .sig-kalab-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .sig-kalab-table td { border: none; text-align: center; }

        .sig-role {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .sig-date {
            font-size: 8pt;
            margin-bottom: 4px;
            min-height: 12px;
        }
        .stamp-area {
            height: 75px;
            text-align: center;
            padding-top: 8px;
        }
        .stamp-approved {
            display: inline-block;
            border: 2px solid #000;
            font-weight: bold;
            font-size: 8pt;
            padding: 4px 12px;
            letter-spacing: 1.5px;
            transform: rotate(-12deg);
        }
        .stamp-empty {
            display: inline-block;
            height: 68px;
        }
        .sig-name-line {
            border-top: 1px solid #000;
            margin: 4px auto 2px auto;
            width: 80%;
            padding-top: 3px;
            font-size: 8.5pt;
            font-weight: bold;
            min-height: 14px;
        }
        .sig-nip { font-size: 7.5pt; }

        /* ── FOOTER ────────────────────────────────── */
        .footer {
            margin-top: 18px;
            border-top: 1px solid #000;
            padding-top: 3px;
            text-align: center;
            font-size: 7.5pt;
        }
    </style>
</head>
<body>
@php
    $logoPath = public_path('img/polije.png');
    $logoSrc  = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : '';

    $dosenApprover   = $booking->approvedByDosen;
    $teknisiApprover = $booking->approvedByTeknisi;
    $kalabApprover   = $booking->approvedByKalab;
@endphp

<!-- ══════ KOP SURAT ══════ -->
<table class="kop-table">
    <tr>
        <td class="kop-logo-cell">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo Polije">
            @endif
        </td>
        <td class="kop-info-cell">
            <div class="kop-inst">Politeknik Negeri Jember</div>
            <div class="kop-dept">Jurusan Teknologi Informasi</div>
            <div class="kop-prodi">Program Studi Teknik Informatika</div>
            <div class="kop-addr">Kampus Nganjuk — Kabupaten Nganjuk, Jawa Timur</div>
        </td>
    </tr>
</table>
<div class="kop-line-thick"></div>
<div class="kop-line-thin"></div>

<!-- ══════ JUDUL ══════ -->
<div class="form-title-box">
    <h2>Formulir Peminjaman Ruang Laboratorium</h2>
    <div class="form-nomor">No. Dokumen: FORM-LAB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</div>
</div>

<!-- ══════ A. INFORMASI BOOKING ══════ -->
<div class="section-title">A. Informasi Booking</div>
<table class="info-table">
    <tr>
        <td class="lbl">Nomor Booking</td>
        <td class="sep">:</td>
        <td><strong>#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Tanggal Pengajuan</td>
        <td class="sep">:</td>
        <td>{{ \Carbon\Carbon::parse($booking->created_at)->isoFormat('D MMMM YYYY, HH:mm') }} WIB</td>
    </tr>
    <tr>
        <td class="lbl">Tanggal Konfirmasi</td>
        <td class="sep">:</td>
        <td>{{ \Carbon\Carbon::parse($approvalDate)->isoFormat('D MMMM YYYY, HH:mm') }} WIB</td>
    </tr>
</table>

<!-- ══════ B. DATA PEMOHON ══════ -->
<div class="section-title">B. Data Pemohon</div>
<table class="info-table">
    <tr>
        <td class="lbl">Nama Lengkap</td>
        <td class="sep">:</td>
        <td><strong>{{ $booking->user->name }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">{{ ($booking->user->role ?? '') === 'mahasiswa' ? 'NIM' : 'NIP' }}</td>
        <td class="sep">:</td>
        <td>{{ $booking->user->nim ?? $booking->user->nip ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Program Studi</td>
        <td class="sep">:</td>
        <td>{{ $booking->prodi ?? 'Teknik Informatika' }}</td>
    </tr>
    @if($booking->golongan)
    <tr>
        <td class="lbl">Golongan / Kelas</td>
        <td class="sep">:</td>
        <td>{{ $booking->golongan }}</td>
    </tr>
    @endif
    <tr>
        <td class="lbl">Email</td>
        <td class="sep">:</td>
        <td>{{ $booking->user->email }}</td>
    </tr>
    <tr>
        <td class="lbl">No. Telepon</td>
        <td class="sep">:</td>
        <td>{{ $booking->phone ?? $booking->user->phone ?? '-' }}</td>
    </tr>
    @if($booking->supervisor_id && $booking->supervisor)
    <tr>
        <td class="lbl">Dosen Pembimbing</td>
        <td class="sep">:</td>
        <td>{{ $booking->supervisor->name }}</td>
    </tr>
    @endif
</table>

<!-- ══════ C. DATA PEMINJAMAN ══════ -->
<div class="section-title">C. Data Peminjaman</div>
<table class="info-table">
    <tr>
        <td class="lbl">Laboratorium</td>
        <td class="sep">:</td>
        <td><strong>{{ $booking->lab_name }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Tanggal Mulai</td>
        <td class="sep">:</td>
        <td>{{ \Carbon\Carbon::parse($booking->start_date ?? $booking->booking_date)->isoFormat('D MMMM YYYY') }}</td>
    </tr>
    <tr>
        <td class="lbl">Tanggal Selesai</td>
        <td class="sep">:</td>
        <td>{{ \Carbon\Carbon::parse($booking->end_date ?? $booking->booking_date)->isoFormat('D MMMM YYYY') }}</td>
    </tr>
    <tr>
        <td class="lbl">Sesi</td>
        <td class="sep">:</td>
        <td>{{ $booking->session ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Waktu</td>
        <td class="sep">:</td>
        <td>{{ substr($booking->start_time,0,5) }} – {{ substr($booking->end_time,0,5) }} WIB</td>
    </tr>
    <tr>
        <td class="lbl">Durasi</td>
        <td class="sep">:</td>
        <td>{{ $booking->duration_days ?? 1 }} Hari</td>
    </tr>
    <tr>
        <td class="lbl">Jenis Kegiatan</td>
        <td class="sep">:</td>
        <td>{{ $booking->activity ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">Keperluan</td>
        <td class="sep">:</td>
        <td>{{ $booking->purpose ?? '-' }}</td>
    </tr>
</table>

@if($booking->is_group && !empty($booking->members) && is_array($booking->members))
<!-- ══════ D. ANGGOTA KELOMPOK ══════ -->
<div class="section-title">D. Anggota Kelompok</div>
<table class="anggota-table">
    <tr>
        <th style="width:8%;">No</th>
        <th style="width:46%;">Nama</th>
        <th style="width:46%;">NIM</th>
    </tr>
    @foreach($booking->members as $i => $memberId)
        @php $m = \App\Models\User::find($memberId); @endphp
        @if($m)
        <tr>
            <td style="text-align:center;">{{ $i + 1 }}</td>
            <td>{{ $m->name }}</td>
            <td>{{ $m->nim ?? '-' }}</td>
        </tr>
        @endif
    @endforeach
</table>
@endif

<!-- ══════ PERNYATAAN PERSETUJUAN ══════ -->
<div class="pern-box">
    <div class="pern-title">PERNYATAAN PERSETUJUAN PEMINJAM</div>
    <div class="pern-intro">Dengan mengajukan permohonan peminjaman ini, saya menyatakan bersedia:</div>
    <ol class="pern-list">
        <li><strong>Bertanggung jawab dan mematuhi aturan</strong> yang ditetapkan pihak kampus terkait penggunaan ruangan laboratorium.</li>
        <li><strong>Menjaga ketertiban, kebersihan, dan inventaris</strong> ruangan selama melaksanakan kegiatan di dalam laboratorium.</li>
        <li><strong>Tidak memindahtangankan</strong> izin peminjaman ini kepada pihak lain tanpa persetujuan pengelola laboratorium.</li>
        <li><strong>Bersedia dikenakan sanksi</strong> apabila terbukti melanggar ketentuan di atas, termasuk pencabutan hak akses laboratorium.</li>
        <li><strong>Mengembalikan kondisi ruangan</strong> seperti semula setelah kegiatan selesai dan melaporkan kepada teknisi jika terdapat kerusakan.</li>
    </ol>
    <div class="pern-note">Demikian pernyataan ini dibuat dengan sebenar-benarnya. Atas perhatian dan kerjasamanya diucapkan terima kasih.</div>
</div>

<!-- ══════ TANDA TANGAN ══════ -->
@php $sigLetter = $booking->is_group ? 'F' : 'E'; @endphp
<div class="sig-section">
    <div class="section-title">{{ $sigLetter }}. Persetujuan &amp; Tanda Tangan</div>

    <table class="sig-table-top">
        <tr>
            <td>
                <div class="sig-role">Dosen / Pengaju</div>
                <div class="sig-date">
                    @if($booking->approved_at_dosen)
                        Nganjuk, {{ \Carbon\Carbon::parse($booking->approved_at_dosen)->isoFormat('D MMMM YYYY') }}
                    @else &nbsp; @endif
                </div>
                <div class="stamp-area">
                    @if($dosenApprover)<div class="stamp-approved">DISETUJUI</div>
                    @else<div class="stamp-empty"></div>@endif
                </div>
                <div class="sig-name-line">{{ $dosenApprover ? $dosenApprover->name : '&nbsp;' }}</div>
                <div class="sig-nip">NIP. {{ $dosenApprover ? ($dosenApprover->nip ?? '-') : '.................................' }}</div>
            </td>
            <td>
                <div class="sig-role">Teknisi Laboratorium</div>
                <div class="sig-date">
                    @if($booking->approved_at_teknisi)
                        Nganjuk, {{ \Carbon\Carbon::parse($booking->approved_at_teknisi)->isoFormat('D MMMM YYYY') }}
                    @else &nbsp; @endif
                </div>
                <div class="stamp-area">
                    @if($teknisiApprover)<div class="stamp-approved">DISETUJUI</div>
                    @else<div class="stamp-empty"></div>@endif
                </div>
                <div class="sig-name-line">{{ $teknisiApprover ? $teknisiApprover->name : '&nbsp;' }}</div>
                <div class="sig-nip">NIP. {{ $teknisiApprover ? ($teknisiApprover->nip ?? '-') : '.................................' }}</div>
            </td>
        </tr>
    </table>

    <table class="sig-kalab-table">
        <tr>
            <td style="width:27%;"></td>
            <td style="width:46%;text-align:center;padding:4px 8px 6px 8px;vertical-align:top;">
                <div class="sig-role">Ketua Laboratorium</div>
                <div class="sig-date">
                    @if($booking->approved_at_kalab)
                        Nganjuk, {{ \Carbon\Carbon::parse($booking->approved_at_kalab)->isoFormat('D MMMM YYYY') }}
                    @else &nbsp; @endif
                </div>
                <div class="stamp-area">
                    @if($kalabApprover)<div class="stamp-approved">DISETUJUI</div>
                    @else<div class="stamp-empty"></div>@endif
                </div>
                <div class="sig-name-line">{{ $kalabApprover ? $kalabApprover->name : '&nbsp;' }}</div>
                <div class="sig-nip">NIP. {{ $kalabApprover ? ($kalabApprover->nip ?? '-') : '.................................' }}</div>
            </td>
            <td style="width:27%;"></td>
        </tr>
    </table>
</div>

<!-- ══════ FOOTER ══════ -->
<div class="footer">
    Dicetak melalui SiPinLab &mdash; Sistem Informasi Peminjaman Laboratorium &nbsp;|&nbsp; Politeknik Negeri Jember {{ date('Y') }}
</div>

</body>
</html>
