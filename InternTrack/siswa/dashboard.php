<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('siswa');
$pdo = db();
$s = siswa_login();
$p = penempatan_saya();
$judul = 'Dashboard Siswa';
include __DIR__ . '/../includes/header.php';
?>
<div class="card"><h3>Halo, <?= e($s['nama'] ?? user()['nama']) ?> 👋</h3>
    <p class="muted-t">NIS <?= e($s['nis'] ?? '-') ?> &middot; <?= e($s['kelas'] ?? '') ?> &middot; <?= e($s['jurusan'] ?? '') ?></p></div>
<?php
if (!$p) { tanpa_penempatan(); }
$rk = rekap_kehadiran_semua('p.id_penempatan = ?', [$p['id_penempatan']])[0];
$x = $pdo->prepare('SELECT COUNT(*) FROM jurnal WHERE id_penempatan = ?'); $x->execute([$p['id_penempatan']]); $jj = (int)$x->fetchColumn();
$x = $pdo->prepare('SELECT nilai FROM monitoring WHERE id_penempatan = ? ORDER BY tanggal DESC, id_monitoring DESC LIMIT 1'); $x->execute([$p['id_penempatan']]); $nl = $x->fetchColumn();
?>
<div class="grid">
    <div class="stat"><small>Status PKL</small><b style="font-size:20px"><?= badge($p['status']) ?></b></div>
    <div class="stat"><small>Kehadiran</small><b><?= $rk['pct'] ?>%</b></div>
    <div class="stat"><small>Predikat Kehadiran</small><b style="font-size:20px"><?= badge($rk['kategori']) ?></b></div>
    <div class="stat"><small>Jurnal Terisi</small><b><?= $jj ?></b></div>
    <div class="stat"><small>Nilai Monitoring Terakhir</small><b><?= $nl !== false ? (int)$nl : '-' ?></b></div>
</div>
<div class="card"><h3>Tempat PKL</h3>
    <dl class="det"><dt>Perusahaan</dt><dd><?= e($p['nama_perusahaan']) ?></dd><dt>Pembimbing</dt><dd><?= e($p['pembimbing']) ?></dd>
    <dt>Periode</dt><dd><?= tgl_id($p['tgl_mulai']) ?> s.d. <?= tgl_id($p['tgl_selesai']) ?></dd></dl>
    <p><a class="btn btn-primary" href="<?= url('siswa/jurnal.php') ?>">Isi Jurnal</a> <a class="btn btn-primary" href="<?= url('siswa/absensi.php') ?>">Isi Absensi</a></p></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
