<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Peminjaman Lab</title>

<style>

@page{
    size:A4;
    margin:15mm;
}

body{
    font-family:"Times New Roman", serif;
    font-size:12px;
}

table{
    width:100%;
    border-collapse:collapse;
}

.header-table td{
    border:1px solid #000;
    padding:6px;
    vertical-align:middle;
}

.logo{
    width:70px;
    text-align:center;
}

.logo img{
    width:60px;
}

.title{
    text-align:center;
    font-weight:bold;
    margin:12px 0;
}

.section{
    margin-top:10px;
}

.row{
    display:flex;
}

.label{
    width:150px;
}

.checkbox{
    width:12px;
    height:12px;
    border:1px solid #000;
    display:inline-block;
    margin-right:5px;
}

.checked{
    background:black;
}

.ttd{
    margin-top:40px;
}

.ttd table td{
    text-align:center;
}

.line{
    border-top:1px solid #000;
    width:200px;
    margin:40px auto 5px auto;
}

</style>
</head>

<body>

<!-- HEADER -->

<table class="header-table">

<tr>

<td class="logo">
<img src="{{ asset('logo.png') }}">
</td>

<td>
<b>POLITEKNIK NEGERI JEMBER</b><br>
JURUSAN TEKNOLOGI INFORMASI
</td>

<td width="220">
No Dokumen : FORM-LAB-{{ $booking->id }}<br>
Tanggal : {{ \Carbon\Carbon::parse($approvalDate)->format('d-m-Y') }}
</td>

</tr>

</table>

<div class="title">
FORM PEMINJAMAN RUANG LABORATORIUM
</div>

<div class="section">
Saya yang bertandatangan dibawah ini selaku peminjam
</div>


<!-- IDENTITAS -->

<div class="row">
<div class="label">Nama / NIM</div>
<div>: {{ $booking->user->name }} / {{ $booking->user->nim }}</div>
</div>

<div class="row">
<div class="label">Prodi / Golongan</div>
<div>: {{ $booking->prodi ?? 'Teknik Informatika' }} / {{ $booking->golongan }}</div>
</div>

<div class="row">
<div class="label">No HP</div>
<div>: {{ $booking->phone }}</div>
</div>


<!-- ANGGOTA -->

<div class="section">
Anggota Kelompok / Tim
</div>

@foreach($booking->membersCollection as $index => $member)

<div class="row">
<div style="width:30px">{{ $index+1 }}.</div>
<div>{{ $member->name }} ({{ $member->nim }})</div>
</div>

@endforeach


<!-- LAB -->

<div class="section">
Mengajukan permohonan peminjaman ruangan laboratorium (Centang salah satu)
</div>

@php
$labs=[
'Multimedia Cerdas (MMC)',
'Komputasi dan Sistem Informasi (KSI)',
'Arsitektur dan Jaringan Komputer (AJK)',
'Mobile',
'Rekayasa Perangkat Lunak (RPL)'
];
@endphp

@foreach($labs as $lab)

<div>
<span class="checkbox {{ $booking->lab_name==$lab?'checked':'' }}"></span>
{{ $lab }}
</div>

@endforeach


<!-- KEGIATAN -->

<div class="section">
Yang akan digunakan dalam kegiatan / acara (Centang salah satu)
</div>

@php
$acts=[
'Tugas Akhir Workshop',
'Penelitian / Pengabdian',
'Tugas Kuliah',
'Kegiatan Komunitas',
'Lomba',
'Tugas Akhir / Skripsi'
];
@endphp

@foreach($acts as $act)

<div>
<span class="checkbox {{ stripos($booking->activity,$act)!==false?'checked':'' }}"></span>
{{ $act }}
</div>

@endforeach


<!-- WAKTU -->

<div class="section">

<div>
Lama Penggunaan : {{ $booking->duration_days }} Hari
</div>

<div>
Selesai Pelaksanaan :
{{ $booking->end_date->format('d-m-Y') }}
</div>

<div>
Tanggal Mulai :
{{ $booking->start_date->format('d-m-Y') }}
</div>

<div>
Waktu :
{{ $booking->session }} WIB
</div>

</div>


<!-- PERNYATAAN -->

<ol>

<li>
BERTANGGUNG JAWAB DAN MEMATUHI ATURAN yang ditetapkan pihak kampus.
</li>

<li>
BERSEDIA MENJAGA KETERATURAN, KEBERSIHAN, DAN INVENTARIS ruangan.
</li>

<li>
BERSEDIA DIKENAKAN SANKSI apabila melanggar aturan.
</li>

</ol>


<div style="margin-top:15px">
Demikian permohonan peminjaman ruangan ini disampaikan.
</div>


<!-- TTD -->

<div class="ttd">

<table>

<tr>

<td width="50%">
Dosen Pembimbing
</td>

<td width="50%">
Nganjuk, {{ \Carbon\Carbon::parse($approvalDate)->format('d-m-Y') }}<br>
Peminjam
</td>

</tr>

<tr>

<td>
<div class="line"></div>
NIP :
</td>

<td>
<div class="line"></div>
{{ $booking->user->name }}<br>
NIM {{ $booking->user->nim }}
</td>

</tr>

<tr>

<td>
Mengetahui Ketua Laboratorium
</td>

<td>
Teknisi Laboratorium
</td>

</tr>

<tr>

<td>
<div class="line"></div>
Raditya Arief Pratama, S.Kom., M.Eng<br>
NIP 199310092024061001
</td>

<td>
<div class="line"></div>
NIP :
</td>

</tr>

</table>

</div>

</body>
</html>
