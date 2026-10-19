<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('siswa');
$s = siswa_login();
$judul = 'Profil Saya';
include __DIR__ . '/../includes/header.php';
if (!$s) { echo '<div class="card">Data profil tidak ditemukan.</div>'; include __DIR__ . '/../includes/footer.php'; exit; }
?>
<div class="card"><h3>Data Siswa</h3>
<dl class="det">
    <dt>NIS</dt><dd><?= e($s['nis']) ?></dd><dt>Nama</dt><dd><?= e($s['nama']) ?></dd>
    <dt>Kelas</dt><dd><?= e($s['kelas']) ?></dd><dt>Jurusan</dt><dd><?= e($s['jurusan']) ?></dd>
    <dt>No. HP</dt><dd><?= e($s['no_hp']) ?></dd><dt>Alamat</dt><dd><?= e($s['alamat']) ?></dd>
    <dt>Username</dt><dd><?= e(user()['username']) ?></dd>
</dl></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
