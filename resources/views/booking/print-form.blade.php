<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman Ruang Laboratorium - {{ $booking->lab_name }}</title>
    
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
        }
        
        /* Header dengan Logo */
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header-logo {
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }
        
        .header-text {
            flex: 1;
            text-align: center;
        }
        
        .header-institution {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .header-department {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header-program {
            font-size: 10pt;
            margin-bottom: 5px;
        }
        
        .header-address {
            font-size: 9pt;
            font-style: italic;
        }
        
        /* Doc Info Box */
        .doc-info-box {
            border: 1px solid #000;
            margin-bottom: 15px;
        }
        
        .doc-info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .doc-info-box td {
            padding: 5px 10px;
            border-right: 1px solid #000;
            font-size: 10pt;
        }
        
        .doc-info-box td:last-child {
            border-right: none;
        }
        
        .form-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            text-decoration: underline;
        }
        
        /* Content Sections */
        .section {
            margin-bottom: 12px;
        }
        
        .section-label {
            font-size: 11pt;
            margin-bottom: 8px;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 6px;
            align-items: flex-start;
        }
        
        .form-label {
            width: 160px;
            flex-shrink: 0;
            font-size: 11pt;
        }
        
        .form-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 0 3px;
            font-size: 11pt;
        }
        
        .form-value.fill {
            border-bottom: 1px solid #000;
        }
        
        /* Checkbox List */
        .checkbox-section {
            margin-left: 160px;
            margin-top: 5px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .checkbox-box {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 8px;
            flex-shrink: 0;
        }
        
        .checkbox-box.checked {
            background: #000;
        }
        
        .checkbox-label {
            font-size: 11pt;
        }
        
        /* Two Columns for Dates */
        .two-columns {
            display: flex;
            gap: 40px;
            margin-top: 10px;
        }
        
        .column {
            flex: 1;
        }
        
        .column-row {
            display: flex;
            margin-bottom: 6px;
        }
        
        .column-label {
            width: 160px;
            flex-shrink: 0;
            font-size: 11pt;
        }
        
        .column-value {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 0 3px;
            font-size: 11pt;
        }
        
        /* Statements */
        .statements {
            margin: 15px 0;
        }
        
        .statement-item {
            margin-bottom: 8px;
            text-align: justify;
            font-size: 11pt;
        }
        
        .statement-number {
            font-weight: bold;
            display: inline-block;
            width: 20px;
        }
        
        .statement-text {
            display: inline;
        }
        
        /* Signatures */
        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-block {
            text-align: center;
            width: 45%;
        }
        
        .signature-title {
            margin-bottom: 60px;
            font-size: 11pt;
        }
        
        .signature-name {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
            font-size: 11pt;
        }
        
        .signature-nip {
            font-size: 10pt;
            margin-top: 3px;
        }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            font-size: 9pt;
            display: flex;
            justify-content: space-between;
        }
        
        /* Print Buttons */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }
        
        @media print {
            .print-button {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print Button -->
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Cetak Form
    </button>

    <div class="container">
        <!-- Header dengan Logo -->
        <div class="header">
            <!-- Logo Placeholder (ganti dengan logo actual) -->
            <div class="header-logo" style="background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 8pt; text-align: center;">
                LOGO
            </div>
            
            <div class="header-text">
                <div class="header-institution">POLITEKNIK NEGERI JEMBER</div>
                <div class="header-department">JURUSAN TEKNOLOGI INFORMASI</div>
                <div class="header-program">Program Studi Teknik Informatika (Kampus Kali, Ngronggo)</div>
                <div class="header-address">Jl. Mastrip, Krajan Timur, Sumbersari, Kabupaten Jember, Jawa Timur 68121</div>
            </div>
        </div>

        <!-- Doc Info Box -->
        <div class="doc-info-box">
            <table>
                <tr>
                    <td style="width: 50%;">
                        <strong>No. Dokumen:</strong> FORM-LAB-{{ $booking->id }}/{{ $booking->created_at->format('m/Y') }}
                    </td>
                    <td>
                        <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($approvalDate)->isoFormat('D MMMM Y') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Form Title -->
        <div class="form-title">FORM PEMINJAMAN RUANG LABORATORIUM</div>

        <!-- Content Based on User Role -->
        @if($booking->user->role === 'dosen')
            <!-- ============================================ -->
            <!-- FORM DOSEN -->
            <!-- ============================================ -->
            
            <div class="section">
                <div class="section-label">Saya yang bertandatangan di bawah ini selaku peminjam:</div>
                
                <div class="form-row">
                    <div class="form-label">Nama Dosen / Tendik</div>
                    <div class="form-value fill">{{ $booking->user->name }}</div>
                </div>
                
                <div class="form-row">
                    <div class="form-label">NIP</div>
                    <div class="form-value fill">{{ $booking->user->nip ?? '-' }}</div>
                </div>
                
                <div class="form-row">
                    <div class="form-label">No HP</div>
                    <div class="form-value fill">{{ $booking->phone ?? $booking->user->phone ?? '-' }}</div>
                </div>

                @if($booking->is_group && $booking->membersCollection->count() > 0)
                <div class="form-row">
                    <div class="form-label">Anggota Kelompok / Tim</div>
                </div>
                @foreach($booking->membersCollection as $index => $member)
                <div class="form-row" style="margin-left: 160px; margin-bottom: 4px;">
                    <div style="width: 25px;">{{ $index + 1 }}.</div>
                    <div class="form-value fill">{{ $member->name }} ({{ $member->nim ?? '-' }})</div>
                </div>
                @endforeach
                @endif
            </div>

            <!-- Lab Selection -->
            <div class="section">
                <div class="section-label">Mengajukan permohonan peminjaman ruangan laboratorium <em>(Centang salah satu):</em></div>
                
                <div class="checkbox-section">
                    @php
                        $labs = [
                            'Multimedia Cerdas (MMC)',
                            'Komputasi dan Sistem Informasi (KSI)',
                            'Arsitektur dan Jaringan Komputer (AJK)',
                            'Mobile',
                            'Rekayasa Perangkat Lunak (RPL)'
                        ];
                    @endphp
                    
                    @foreach($labs as $lab)
                    <div class="checkbox-item">
                        <div class="checkbox-box {{ $booking->lab_name === $lab ? 'checked' : '' }}"></div>
                        <div class="checkbox-label">{{ $lab }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Activity Type (DOSEN) -->
            <div class="section">
                <div class="section-label">Yang akan digunakan dalam kegiatan / acara <em>(Centang salah satu):</em></div>
                
                <div class="checkbox-section">
                    @php
                        $dosenActivities = [
                            'Bimbingan - Tugas Akhir Workshop',
                            'Penelitian',
                            'Pengabdian Masyarakat',
                            'Perkuliahan (Workshop) Prodi Lain',
                            'Bimbingan Lainnya'
                        ];
                    @endphp
                    
                    @foreach($dosenActivities as $act)
                    <div class="checkbox-item">
                        <div class="checkbox-box {{ stripos($booking->activity, $act) !== false ? 'checked' : '' }}"></div>
                        <div class="checkbox-label">{{ $act }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

        @else
            <!-- ============================================ -->
            <!-- FORM MAHASISWA -->
            <!-- ============================================ -->
            
            <div class="section">
                <div class="section-label">Saya yang bertandatangan di bawah ini selaku peminjam:</div>
                
                <div class="form-row">
                    <div class="form-label">Nama / NIM</div>
                    <div class="form-value fill">{{ $booking->user->name }} / {{ $booking->user->nim ?? '-' }}</div>
                </div>
                
                <div class="form-row">
                    <div class="form-label">Prodi / Golongan</div>
                    <div class="form-value fill">{{ $booking->prodi ?? 'Teknik Informatika' }} / {{ $booking->golongan ?? '-' }}</div>
                </div>
                
                <div class="form-row">
                    <div class="form-label">No HP</div>
                    <div class="form-value fill">{{ $booking->phone ?? $booking->user->phone ?? '-' }}</div>
                </div>

                @if($booking->is_group && $booking->membersCollection->count() > 0)
                <div class="form-row">
                    <div class="form-label">Anggota Kelompok / Tim</div>
                </div>
                @foreach($booking->membersCollection as $index => $member)
                <div class="form-row" style="margin-left: 160px; margin-bottom: 4px;">
                    <div style="width: 25px;">{{ $index + 1 }}.</div>
                    <div class="form-value fill">{{ $member->name }} ({{ $member->nim ?? '-' }})</div>
                </div>
                @endforeach
                @endif
            </div>

            <!-- Lab Selection -->
            <div class="section">
                <div class="section-label">Mengajukan permohonan peminjaman ruangan laboratorium <em>(Centang salah satu):</em></div>
                
                <div class="checkbox-section">
                    @php
                        $labs = [
                            'Multimedia Cerdas (MMC)',
                            'Komputasi dan Sistem Informasi (KSI)',
                            'Arsitektur dan Jaringan Komputer (AJK)',
                            'Mobile',
                            'Rekayasa Perangkat Lunak (RPL)'
                        ];
                    @endphp
                    
                    @foreach($labs as $lab)
                    <div class="checkbox-item">
                        <div class="checkbox-box {{ $booking->lab_name === $lab ? 'checked' : '' }}"></div>
                        <div class="checkbox-label">{{ $lab }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Activity Type (MAHASISWA) -->
            <div class="section">
                <div class="section-label">Yang akan digunakan dalam kegiatan / acara <em>(Centang salah satu):</em></div>
                
                <div class="checkbox-section">
                    @php
                        $mhsActivities = [
                            'Tugas Akhir Workshop',
                            'Penelitian / Pengabdian',
                            'Tugas Kuliah',
                            'Kegiatan Komunitas',
                            'Lomba',
                            'Tugas Akhir / Skripsi'
                        ];
                    @endphp
                    
                    @foreach($mhsActivities as $act)
                    <div class="checkbox-item">
                        <div class="checkbox-box {{ stripos($booking->activity, $act) !== false ? 'checked' : '' }}"></div>
                        <div class="checkbox-label">{{ $act }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Duration & Dates (Common) -->
        <div class="two-columns">
            <div class="column">
                <div class="column-row">
                    <div class="column-label">Lama Penggunaan</div>
                    <div class="column-value">{{ $booking->duration_days }} Hari</div>
                </div>
                
                <div class="column-row">
                    <div class="column-label">Selesai Pelaksanaan</div>
                    <div class="column-value">{{ $booking->end_date->isoFormat('D MMMM Y') }}</div>
                </div>
            </div>
            
            <div class="column">
                <div class="column-row">
                    <div class="column-label">Tanggal Mulai Pelaksanaan</div>
                    <div class="column-value">{{ $booking->start_date->isoFormat('D MMMM Y') }}</div>
                </div>
                
                <div class="column-row">
                    <div class="column-label">Waktu Pelaksanaan</div>
                    <div class="column-value">{{ $booking->session }} WIB</div>
                </div>
            </div>
        </div>

        <!-- Statements -->
        <div class="section statements">
            <div class="section-label">Selanjutnya akan:</div>
            
            <div class="statement-item">
                <span class="statement-number">1.</span>
                <span class="statement-text">
                    <strong>BERTANGGUNG JAWAB DAN MEMATUHI ATURAN</strong> yang ditetapkan pihak kampus terkait dengan penggunaan ruangan.
                </span>
            </div>
            
            <div class="statement-item">
                <span class="statement-number">2.</span>
                <span class="statement-text">
                    <strong>BERSEDIA MENJAGA KETERATURAN, KEBERSIHAN, DAN INVENTARIS</strong> ruangan selama melaksanakan kegiatan di dalam ruangan.
                </span>
            </div>
            
            <div class="statement-item">
                <span class="statement-number">3.</span>
                <span class="statement-text">
                    <strong>BERSEDIA DIKENAKAN SANKSI</strong> apabila dalam pelaksanaannya dinilai dan terbukti melanggar poin 1 dan poin 2.
                </span>
            </div>
        </div>

        <!-- Closing -->
        <div style="margin: 15px 0; font-size: 11pt;">
            Demikian permohonan peminjaman ruangan ini disampaikan. Atas perhatian dan bantuan diucapkan terima kasih.
        </div>

        <!-- Signatures (Different for Dosen vs Mahasiswa) -->
        @if($booking->user->role === 'dosen')
            <!-- Signatures for DOSEN -->
            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-title">Teknisi Laboratorium</div>
                    @if($booking->approvedByTeknisi)
                    <div class="signature-name">{{ $booking->approvedByTeknisi->name }}</div>
                    <div class="signature-nip">NIP. {{ $booking->approvedByTeknisi->nip ?? '-' }}</div>
                    @else
                    <div class="signature-name">(..................................................)</div>
                    <div class="signature-nip">NIP. ...........................................</div>
                    @endif
                </div>
                
                <div class="signature-block">
                    <div class="signature-title">Nganjuk, {{ \Carbon\Carbon::parse($approvalDate)->isoFormat('D MMMM Y') }}</div>
                    <div class="signature-title">Peminjam,</div>
                    <div class="signature-name">{{ $booking->user->name }}</div>
                    <div class="signature-nip">NIP. {{ $booking->user->nip ?? '-' }}</div>
                </div>
            </div>

            <div class="signatures" style="margin-top: 20px;">
                <div class="signature-block" style="width: 100%;">
                    <div class="signature-title">Mengetahui Ketua Laboratorium</div>
                    @if($booking->approvedByKalab)
                    <div class="signature-name">{{ $booking->approvedByKalab->name }}</div>
                    <div class="signature-nip">NIP. {{ $booking->approvedByKalab->nip ?? '-' }}</div>
                    @else
                    <div class="signature-name">Radiana Arief Pratama, S.Kom., M.Eng.</div>
                    <div class="signature-nip">NIP. 199310092024061001</div>
                    @endif
                </div>
            </div>

        @else
            <!-- Signatures for MAHASISWA -->
            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-title">Dosen Pembimbing,</div>
                    @if($booking->supervisor)
                    <div class="signature-name">{{ $booking->supervisor->name }}</div>
                    <div class="signature-nip">NIP. {{ $booking->supervisor->nip ?? '-' }}</div>
                    @else
                    <div class="signature-name">(..................................................)</div>
                    <div class="signature-nip">NIP. ...........................................</div>
                    @endif
                </div>
                
                <div class="signature-block">
                    <div class="signature-title">Nganjuk, {{ \Carbon\Carbon::parse($approvalDate)->isoFormat('D MMMM Y') }}</div>
                    <div class="signature-title">Peminjam,</div>
                    <div class="signature-name">{{ $booking->user->name }}</div>
                    <div class="signature-nip">NIM. {{ $booking->user->nim ?? '-' }}</div>
                </div>
            </div>

            <div class="signatures" style="margin-top: 20px;">
                <div class="signature-block">
                    <div class="signature-title">Teknisi Laboratorium</div>
                    @if($booking->approvedByTeknisi)
                    <div class="signature-name">{{ $booking->approvedByTeknisi->name }}</div>
                    <div class="signature-nip">NIP. {{ $booking->approvedByTeknisi->nip ?? '-' }}</div>
                    @else
                    <div class="signature-name">(..................................................)</div>
                    <div class="signature-nip">NIP. ...........................................</div>
                    @endif
                </div>
                
                <div class="signature-block">
                    <div class="signature-title">Mengetahui Ketua Laboratorium</div>
                    @if($booking->approvedByKalab)
                    <div class="signature-name">{{ $booking->approvedByKalab->name }}</div>
                    <div class="signature-nip">NIP. {{ $booking->approvedByKalab->nip ?? '-' }}</div>
                    @else
                    <div class="signature-name">Radiana Arief Pratama, S.Kom., M.Eng.</div>
                    <div class="signature-nip">NIP. 199310092024061001</div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>localhost:8000/booking/{{ $booking->id }}/print</div>
            <div>1/2</div>
        </div>
    </div>

    <script>
        // Auto print on load (optional - uncomment jika ingin auto print)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>