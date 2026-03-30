<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Peminjaman Ruang Laboratorium - <?php echo e(str_pad($booking->id, 5, '0', STR_PAD_LEFT)); ?></title>
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

        /* Header */
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

        /* Content */
        .intro {
            margin-bottom: 4px;
            font-size: 10pt;
        }

        .form-row {
            margin-bottom: 3px;
        }

        .form-row::after {
            content: "";
            display: block;
            clear: both;
        }

        .form-label {
            float: left;
            width: 150px;
            font-size: 10pt;
        }

        .form-dots {
            margin-left: 155px;
            border-bottom: 1px dotted #000;
            min-height: 12px;
            font-size: 10pt;
        }

        .members-section {
            margin-left: 155px;
            margin-bottom: 6px;
        }

        .members-section div {
            margin-bottom: 2px;
            font-size: 9pt;
        }

        .members-num {
            display: inline-block;
            width: 18px;
        }

        .members-dots {
            display: inline-block;
            width: calc(100% - 22px);
            border-bottom: 1px dotted #000;
        }

        /* Checkbox */
        .checkbox-section {
            margin-bottom: 6px;
        }

        .checkbox-title {
            margin-bottom: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .checkbox-item {
            margin-left: 12px;
            margin-bottom: 2px;
            font-size: 9pt;
        }

        .checkbox-box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            margin-right: 4px;
            vertical-align: middle;
        }

        .checkbox-box.checked {
            background: #000;
        }

        /* Time Section */
        .time-section {
            margin-bottom: 6px;
            font-size: 9pt;
        }

        .time-row {
            margin-bottom: 3px;
        }

        .time-row::after {
            content: "";
            display: block;
            clear: both;
        }

        .time-label {
            float: left;
            width: 120px;
        }

        .time-dots {
            float: left;
            width: 110px;
            border-bottom: 1px dotted #000;
            margin-right: 15px;
        }

        .time-label-right {
            float: left;
            width: 120px;
        }

        .time-dots-right {
            float: left;
            width: 130px;
            border-bottom: 1px dotted #000;
        }

        /* Statement */
        .statement-section {
            margin-bottom: 6px;
        }

        .statement-intro {
            margin-bottom: 3px;
            font-size: 10pt;
        }

        .statement-list {
            margin-left: 15px;
            margin-bottom: 4px;
        }

        .statement-list ol {
            font-size: 8pt;
        }

        .statement-list li {
            margin-bottom: 2px;
            text-align: justify;
            line-height: 1.1;
        }

        .statement-list strong {
            font-weight: bold;
        }

        .statement-note {
            font-size: 8pt;
            font-style: italic;
        }

        /* Signatures */
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

        .clearfix::after {
            content: "";
            display: block;
            clear: both;
        }

        /* Print Button */
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
            body {
                padding: 0;
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
                        <td>: FORM-LAB-<?php echo e(str_pad($booking->id, 5, '0', STR_PAD_LEFT)); ?></td>
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

    <!-- Title -->
    <div class="form-title">FORM PEMINJAMAN RUANG LABORATORIUM</div>

    <!-- Content -->
    <div class="content">
        <div class="intro">Saya yang bertandatangan dibawah ini selaku peminjam :</div>

        <?php if($booking->user->role === 'mahasiswa'): ?>
        <div class="form-row">
            <div class="form-label">Nama / NIM</div>
            <div class="form-dots"><?php echo e($booking->user->name); ?> / <?php echo e($booking->user->nim ?? ''); ?></div>
        </div>
        <?php else: ?>
        <div class="form-row">
            <div class="form-label">Nama Dosen / Tendik</div>
            <div class="form-dots"><?php echo e($booking->user->name); ?></div>
        </div>
        <div class="form-row">
            <div class="form-label">NIP</div>
            <div class="form-dots"><?php echo e($booking->user->nip ?? ''); ?></div>
        </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-label">Prodi / Golongan</div>
            <div class="form-dots"><?php echo e($booking->prodi ?? 'Teknik Informatika'); ?> / <?php echo e($booking->golongan ?? ''); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">No HP</div>
            <div class="form-dots"><?php echo e($booking->phone ?? ''); ?></div>
        </div>

        <div class="form-row">
            <div class="form-label">Anggota Kelompok / Tim</div>
        </div>

        <div class="members-section">
            <?php
            $memberCount = $booking->membersCollection ? $booking->membersCollection->count() : 0;
            ?>

            <?php if($memberCount > 0): ?>
                <?php $__currentLoopData = $booking->membersCollection->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <span class="members-num"><?php echo e($index + 1); ?>.</span>
                    <span class="members-dots"><?php echo e($member->name); ?> (<?php echo e($member->nim ?? $member->nip ?? ''); ?>)</span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php for($i = $memberCount; $i < 5; $i++): ?>
                <div>
                    <span class="members-num"><?php echo e($i + 1); ?>.</span>
                    <span class="members-dots"></span>
                </div>
                <?php endfor; ?>
            <?php else: ?>
                <?php for($i = 1; $i <= 5; $i++): ?>
                <div>
                    <span class="members-num"><?php echo e($i); ?>.</span>
                    <span class="members-dots"></span>
                </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Labs -->
    <div class="checkbox-section">
        <div class="checkbox-title">Mengajukan permohonan peminjaman ruangan laboratorium *(Centang salah satu) :</div>

        <?php
        $labs = [
            'Multimedia Cerdas (MMC)',
            'Komputer dan Sistem Informasi (KSI)',
            'Arsitektur dan Jaringan Komputer (AJK)',
            'Mobile',
            'Rekayasa Perangkat Lunak (RPL)'
        ];
        ?>

        <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="checkbox-item">
            <span class="checkbox-box <?php echo e($booking->lab_name == $lab ? 'checked' : ''); ?>"></span>
            <?php echo e($lab); ?>

        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Activities -->
    <div class="checkbox-section">
        <div class="checkbox-title">Yang akan digunakan dalam kegiatan / acara *(Centang salah satu) :</div>

        <?php
        if($booking->user->role === 'mahasiswa') {
            $acts = [
                'Tugas Akhir Workshop',
                'Penelitian / Pengabdian',
                'Tugas Kuliah',
                'Kegiatan Komunitas',
                'Lomba',
                'Tugas Akhir / Skripsi'
            ];
        } else {
            $acts = [
                'Bimbingan - Tugas Akhir Workshop',
                'Penelitian',
                'Pengabdian Masyarakat',
                'Perkuliahah (Workshop) Prodi Lain',
                'Bimbingan Lainnya'
            ];
        }
        ?>

        <?php $__currentLoopData = $acts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="checkbox-item">
            <span class="checkbox-box <?php echo e(stripos($booking->activity ?? '', $act) !== false ? 'checked' : ''); ?>"></span>
            <?php echo e($act); ?>

        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Time -->
    <div class="time-section">
        <div class="time-row">
            <div class="time-label">Lama Penggunaan</div>
            <div class="time-dots"><?php echo e($booking->duration_days); ?> Hari</div>
            <div class="time-label-right">Tanggal Mulai Pelaksanaan</div>
            <div class="time-dots-right"><?php echo e(\Carbon\Carbon::parse($booking->start_date)->format('d/m/Y')); ?></div>
        </div>
        <div class="time-row">
            <div class="time-label">Selesai Pelaksanaan</div>
            <div class="time-dots"><?php echo e(\Carbon\Carbon::parse($booking->end_date)->format('d/m/Y')); ?></div>
            <div class="time-label-right">Waktu Pelaksanaan</div>
            <div class="time-dots-right"><?php echo e($booking->start_time); ?> s/d <?php echo e($booking->end_time); ?> WIB</div>
        </div>
    </div>

    <!-- Statement -->
    <div class="statement-section">
        <div class="statement-intro">Selanjutnya akan,</div>
        <div class="statement-list">
            <ol>
                <li>
                    <strong>BERTANGGUNG JAWAB DAN MEMATUHI ATURAN</strong> yang ditetapkan pihak kampus terkait dengan penggunaan ruangan.
                </li>
                <li>
                    <strong>BERSEDIA MENJAGA KETERTIBAN, KEBERSIHAN, DAN INVENTARIS</strong> ruangan selama melaksanakan kegiatan di dalam ruangan.
                </li>
                <li>
                    <strong>BERSEDIA DIKENAKAN SANKSI</strong> apabila dalam pelaksanaannya dinilai dan terbukti melanggar poin 1 dan poin 2.
                </li>
            </ol>
        </div>
        <div class="statement-note">
            Demikian permohonan peminjaman ruangan ini disampaikan. Atas perhatian dan bantuan diucapkan terima kasih.
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <?php if($booking->user->role === 'mahasiswa'): ?>
        <!-- Mahasiswa -->
        <div class="sig-row clearfix">
            <div class="sig-box">
                <div class="sig-title">Dosen Pembimbing,</div>
                <div class="sig-line"></div>
                <div class="sig-name">
                    <?php if($booking->approvedByDosen): ?>
                        <?php echo e($booking->approvedByDosen->name); ?>

                    <?php else: ?>
                        ........................
                    <?php endif; ?>
                </div>
                <div class="sig-name">
                    NIP. <?php echo e($booking->approvedByDosen->nip ?? '........................'); ?>

                </div>
            </div>
            <div class="sig-box right">
                <div class="sig-title">Nganjuk, <?php echo e(\Carbon\Carbon::parse($approvalDate)->format('d/m/Y')); ?><br>Peminjam,</div>
                <div class="sig-line"></div>
                <div class="sig-name"><?php echo e($booking->user->name); ?></div>
                <div class="sig-name">NIM. <?php echo e($booking->user->nim ?? ''); ?></div>
            </div>
        </div>

        <div class="sig-row clearfix">
            <div class="sig-box">
                <div class="sig-title">Mengetahui Ketua Laboratorium</div>
                <div class="kalab-name">Raditya Arief Pratama, S.Kom, M.Eng.</div>
                <div class="sig-name">NIP. 199310092024061001</div>
            </div>
            <div class="sig-box right">
                <div class="sig-title">Teknisi Laboratorium</div>
                <div class="sig-line"></div>
                <div class="sig-name">
                    <?php if($booking->approvedByTeknisi): ?>
                        <?php echo e($booking->approvedByTeknisi->name); ?>

                    <?php else: ?>
                        ........................
                    <?php endif; ?>
                </div>
                <div class="sig-name">
                    NIP. <?php echo e($booking->approvedByTeknisi->nip ?? '........................'); ?>

                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Dosen -->
        <div class="sig-row clearfix">
            <div class="sig-box">
                <div class="sig-title">Teknisi Laboratorium</div>
                <div class="sig-line"></div>
                <div class="sig-name">
                    <?php if($booking->approvedByTeknisi): ?>
                        <?php echo e($booking->approvedByTeknisi->name); ?>

                    <?php else: ?>
                        ........................
                    <?php endif; ?>
                </div>
                <div class="sig-name">
                    NIP. <?php echo e($booking->approvedByTeknisi->nip ?? '........................'); ?>

                </div>
            </div>
            <div class="sig-box right">
                <div class="sig-title">Nganjuk, <?php echo e(\Carbon\Carbon::parse($approvalDate)->format('d/m/Y')); ?><br>Peminjam,</div>
                <div class="sig-line"></div>
                <div class="sig-name"><?php echo e($booking->user->name); ?></div>
                <div class="sig-name">NIP. <?php echo e($booking->user->nip ?? ''); ?></div>
            </div>
        </div>

        <div class="sig-row clearfix" style="text-align: center;">
            <div style="margin: 0 auto; width: 50%;">
                <div class="sig-title">Mengetahui Ketua Laboratorium</div>
                <div class="kalab-name">Raditya Arief Pratama, S.Kom, M.Eng.</div>
                <div class="sig-name">NIP. 199310092024061001</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Print Button -->
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Formulir</button>
        <button onclick="window.close()" class="btn-close">✕ Tutup</button>
    </div>
</body>
</html>
<?php /**PATH D:\project\laravel_project\sipinlab\resources\views/booking/print-form.blade.php ENDPATH**/ ?>