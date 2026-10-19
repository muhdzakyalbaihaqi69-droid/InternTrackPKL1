<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('siswa');
$p = penempatan_saya();
$judul = 'Tempat PKL';
include __DIR__ . '/../includes/header.php';
if (!$p) { tanpa_penempatan(); }
?>
<div class="card"><h3>Perusahaan / Instansi</h3>
<dl class="det">
    <dt>Nama Perusahaan</dt><dd><?= e($p['nama_perusahaan']) ?></dd><dt>Alamat</dt><dd><?= e($p['alamat_perusahaan']) ?></dd>
    <dt>Bidang</dt><dd><?= e($p['bidang']) ?></dd><dt>Kontak</dt><dd><?= e($p['kontak']) ?></dd>
</dl></div>
<div class="card"><h3>Guru Pembimbing &amp; Periode</h3>
<dl class="det">
    <dt>Pembimbing</dt><dd><?= e($p['pembimbing']) ?></dd><dt>No. HP Pembimbing</dt><dd><?= e($p['hp_pembimbing']) ?></dd>
    <dt>Tanggal Mulai</dt><dd><?= tgl_id($p['tgl_mulai']) ?></dd><dt>Tanggal Selesai</dt><dd><?= tgl_id($p['tgl_selesai']) ?></dd>
    <dt>Status PKL</dt><dd><?= badge($p['status']) ?></dd>
</dl></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
