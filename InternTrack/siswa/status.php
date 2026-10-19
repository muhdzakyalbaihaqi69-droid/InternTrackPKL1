<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('siswa');
$p = penempatan_saya();
$judul = 'Status PKL';
include __DIR__ . '/../includes/header.php';
if (!$p) { tanpa_penempatan(); }
$rk = rekap_kehadiran_semua('p.id_penempatan = ?', [$p['id_penempatan']])[0];
?>
<div class="card"><h3>Status Penempatan</h3>
<dl class="det"><dt>Status PKL</dt><dd><?= badge($p['status']) ?></dd>
<dt>Perusahaan</dt><dd><?= e($p['nama_perusahaan']) ?></dd>
<dt>Periode</dt><dd><?= tgl_id($p['tgl_mulai']) ?> s.d. <?= tgl_id($p['tgl_selesai']) ?></dd></dl></div>
<div class="card"><h3>Perkembangan Kehadiran</h3>
<p>Total hari tercatat: <b><?= $rk['total'] ?></b> &middot; Hadir: <b><?= $rk['hadir'] ?></b></p>
<p>Persentase: <?= $rk['hadir'] ?> / <?= $rk['total'] ?> &times; 100 = <b><?= $rk['pct'] ?>%</b> &rarr; <?= badge($rk['kategori']) ?></p>
<table class="tbl" style="max-width:420px"><tr><th>Persentase Kehadiran</th><th>Status</th></tr>
<tr><td>&ge; 90%</td><td>Sangat Baik</td></tr><tr><td>75% – 89%</td><td>Baik</td></tr>
<tr><td>60% – 74%</td><td>Perlu Perhatian</td></tr><tr><td>&lt; 60%</td><td>Perlu Pembinaan</td></tr></table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
