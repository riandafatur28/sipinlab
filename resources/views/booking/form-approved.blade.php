<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Booking Dikonfirmasi - {{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
        }

        .header-box {
            border: 1px solid #000;
            padding: 4px;
            margin-bottom: 6px;
        }

        .header-content {
            display: flex;
            align-items: center;
        }

        .header-left {
            flex: 1;
            display: flex;
            align-items: center;
            border-right: 1px solid #000;
            padding-right: 6px;
        }

        .logo-circle {
            width: 32px;
            height: 32px;
            border: 1px solid #000;
            border-radius: 50%;
            margin-right: 6px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .header-text h3 {
            font-size: 9pt;
            margin: 1px 0;
            font-weight: bold;
            line-height: 1.1;
        }

        .header-text p {
            font-size: 7pt;
            margin: 1px 0;
        }

        .header-right {
            width: 180px;
            padding-left: 6px;
            font-size: 7pt;
        }

        .header-right table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-right td {
            padding: 1px 0;
            vertical-align: top;
        }

        .header-right .label {
            width: 70px;
            font-weight: bold;
        }

        .header-bottom {
            border-top: 1px solid #000;
            margin-top: 3px;
            padding-top: 2px;
            font-size: 7pt;
            text-align: center;
        }

        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 6px 0 8px 0;
            text-decoration: underline;
        }

        .status-confirmed {
            text-align: center;
            background: #dcfce7;
            border: 2px solid #166534;
            color: #166534;
            padding: 8px;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 12pt;
        }

        .info-section {
            margin-bottom: 6px;
        }

        .info-section h3 {
            background: #eff6ff;
            padding: 4px 8px;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 4px;
            border-left: 3px solid #2563eb;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .info-table tr {
            border-bottom: 1px solid #e5e7eb;
        }

        .info-table td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
            color: #555;
        }

        .signatures {
            margin-top: 10px;
        }

        .sig-row {
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        .sig-row::after {
            content: "";
            display: block;
            clear: both;
        }

        .sig-box {
            float: left;
            width: 48%;
            text-align: center;
        }

        .sig-box.right {
            float: right;
        }

        .sig-title {
            min-height: 30px;
            margin-bottom: 2px;
            font-size: 9pt;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            width: 140px;
            margin: 25px auto 2px auto;
            height: 12px;
        }

        .sig-name {
            font-size: 8pt;
            margin-top: 2px;
        }

        .kalab-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 20px;
            font-size: 8pt;
        }

        .no-print {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 5px;
        }

        .no-print button {
            padding: 8px 25px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-print {
            background: #2563eb;
            color: white;
        }

        .btn-close {
            background: #6b7280;
            color: white;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-box">
        <div class="header-content">
            <div class="header-left">
                <div class="logo-circle">🎓</div>
                <div class="header-text">
                    <h3>POLITEKNIK NEGERI JEMBER</h3>
                    <h3>JURUSAN TEKNOLOGI INFORMASI</h3>
                    <p>Program Studi Teknik Informatika (Kampus Kali, Nganjuk)</p>
                </div>
            </div>
            <div class="header-right">
                <table>
                    <tr>
                        <td class="label">No. Dokumen</td>
                        <td>: FORM-LAB-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Terbitan</td>
                        <td>: Program Studi Teknik Informatika PSDMU Kab. Nganjuk</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="header-bottom">
            Program Studi Teknik Informatika PSDMU Kab. Nganjuk
        </div>
    </div>

    <!-- Status -->
    <div class="status-confirmed">
        ✅ BOOKING DIKONFIRMASI
    </div>

    <!-- Title -->
    <div class="form-title">FORM PEMINJAMAN RUANG LABORATORIUM</div>

    <!-- Info Sections -->
    <div class="info-section">
        <h3>📋 Informasi Booking</h3>
        <table class="info-table">
            <tr>
                <td>Nomor Booking</td>
                <td>: <strong>#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Pengajuan</td>
                <td>: {{ $booking->created_at->locale('id')->isoFormat('DD MMMM YYYY HH:mm') }}</td>
            </tr>
            <tr>
                <td>Tanggal Konfirmasi</td>
                <td>: {{ $approvalDate->locale('id')->isoFormat('DD MMMM YYYY HH:mm') }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h3>👤 Informasi Pemohon</h3>
        <table class="info-table">
            <tr>
                <td>Nama</td>
                <td>: <strong>{{ $booking->user->name }}</strong></td>
            </tr>
            <tr>
                <td>NIM / NIP</td>
                <td>: {{ $booking->user->nim ?? $booking->user->nip ?? '-' }}</td>
            </tr>
            <tr>
                <td>Role</td>
                <td>: {{ ucfirst($booking->user->role) }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>: {{ $booking->prodi ?? 'Teknik Informatika' }}</td>
            </tr>
            <tr>
                <td>Golongan</td>
                <td>: {{ $booking->golongan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>: {{ $booking->user->email }}</td>
            </tr>
            <tr>
                <td>Telepon</td>
                <td>: {{ $booking->phone ?? $booking->user->phone ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h3>🏢 Informasi Peminjaman</h3>
        <table class="info-table">
            <tr>
                <td>Laboratorium</td>
                <td>: <strong>{{ $booking->lab_name }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($booking->booking_date)->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
            </tr>
            <tr>
                <td>Sesi</td>
                <td>: {{ $booking->session ?? '-' }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: {{ $booking->start_time }} - {{ $booking->end_time }}</td>
            </tr>
            <tr>
                <td>Durasi</td>
                <td>: {{ $booking->duration_days }} Hari</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h3>📚 Informasi Kegiatan</h3>
        <table class="info-table">
            <tr>
                <td>Jenis Kegiatan</td>
                <td>: <strong>{{ $booking->activity }}</strong></td>
            </tr>
            <tr>
                <td>Keperluan</td>
                <td>: {{ $booking->purpose ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-row clearfix">
            <div class="sig-box">
                <div class="sig-title">Teknisi Laboratorium</div>
                <div class="sig-line"></div>
                <div class="sig-name">
                    @if($booking->approvedByTeknisi)
                        {{ $booking->approvedByTeknisi->name }}
                    @endif
                </div>
                <div class="sig-name">
                    NIP. {{ $booking->approvedByTeknisi->nip ?? '........................' }}
                </div>
            </div>
            <div class="sig-box right">
                <div class="sig-title">Ketua Laboratorium</div>
                <div class="sig-line"></div>
                <div class="sig-name">
                    @if($booking->approvedByKalab)
                        {{ $booking->approvedByKalab->name }}
                    @endif
                </div>
                <div class="sig-name">
                    NIP. {{ $booking->approvedByKalab->nip ?? '........................' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Formulir</button>
        <button onclick="window.close()" class="btn-close">✕ Tutup</button>
    </div>
</body>
</html>
