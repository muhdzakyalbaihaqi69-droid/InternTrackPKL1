<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo = db();
$n = function (string $sql) use ($pdo): int { return (int)$pdo->query($sql)->fetchColumn(); };

// Semua angka berasal dari database (bukan statis)
$rekap = rekap_kehadiran_semua();
$perhatian = array_values(array_filter($rekap, function ($r) {
    return $r['total'] > 0 && $r['pct'] < 75;   // Perlu Perhatian / Perlu Pembinaan
}));

$stat = [
    'Total Siswa PKL'       => $n('SELECT COUNT(DISTINCT id_siswa) FROM penempatan'),
    'Perusahaan'            => $n('SELECT COUNT(*) FROM perusahaan'),
    'Pembimbing'            => $n('SELECT COUNT(*) FROM pembimbing'),
    'PKL Aktif'             => $n("SELECT COUNT(*) FROM penempatan WHERE status = 'Aktif'"),
    'PKL Selesai'           => $n("SELECT COUNT(*) FROM penempatan WHERE status = 'Selesai'"),
    'Jurnal Hari Ini'       => $n('SELECT COUNT(*) FROM jurnal WHERE tanggal = CURDATE()'),
    'Siswa Perlu Perhatian' => count($perhatian),
];

$terbaru = $pdo->query(
    'SELECT j.tanggal, j.kegiatan, s.nama, pr.nama_perusahaan
     FROM jurnal j
     JOIN penempatan p ON p.id_penempatan = j.id_penempatan
     JOIN siswa s ON s.id_siswa = p.id_siswa
     JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
     ORDER BY j.tanggal DESC, j.id_jurnal DESC LIMIT 6'
)->fetchAll();

$judul = 'Dashboard Admin';
include __DIR__ . '/../includes/header.php';
?>
<div class="grid">
<?php foreach ($stat as $label => $nilai): ?>
    <div class="stat <?= $label === 'Siswa Perlu Perhatian' ? 'warn' : '' ?>"><small><?= e($label) ?></small><b><?= $nilai ?></b></div>
<?php endforeach; ?>
</div>

<div class="card">
    <h3>Siswa yang Perlu Perhatian (kehadiran &lt; 75%)</h3>
    <div class="table-wrap"><table class="tbl">
        <tr><th>Siswa</th><th>Perusahaan</th><th>Kehadiran</th><th>Status</th></tr>
        <?php foreach ($perhatian as $r): ?>
        <tr><td><?= e($r['nama']) ?></td><td><?= e($r['nama_perusahaan']) ?></td><td><?= $r['pct'] ?>%</td><td><?= badge($r['kategori']) ?></td></tr>
        <?php endforeach; if (!$perhatian): ?><tr><td colspan="4" class="empty">Tidak ada siswa yang perlu perhatian.</td></tr><?php endif; ?>
    </table></div>
</div>

<div class="card">
    <h3>Jurnal Terbaru</h3>
    <div class="table-wrap"><table class="tbl">
        <tr><th>Tanggal</th><th>Siswa</th><th>Perusahaan</th><th>Kegiatan</th></tr>
        <?php foreach ($terbaru as $j): ?>
        <tr><td><?= tgl_id($j['tanggal']) ?></td><td><?= e($j['nama']) ?></td><td><?= e($j['nama_perusahaan']) ?></td><td><?= e($j['kegiatan']) ?></td></tr>
        <?php endforeach; if (!$terbaru): ?><tr><td colspan="4" class="empty">Belum ada jurnal.</td></tr><?php endif; ?>
    </table></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
