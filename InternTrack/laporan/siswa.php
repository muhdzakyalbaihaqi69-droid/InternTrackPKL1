<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo = db();
$fk = $_GET['kelas'] ?? ''; $fs = $_GET['status'] ?? ''; $fp = (int)($_GET['perusahaan'] ?? 0);
$w = ['1=1']; $pr = [];
if ($fk !== '') { $w[] = 's.kelas = ?'; $pr[] = $fk; }
if ($fs !== '') { $w[] = 'p.status = ?'; $pr[] = $fs; }
if ($fp)        { $w[] = 'p.id_perusahaan = ?'; $pr[] = $fp; }
$st = $pdo->prepare(
    'SELECT s.nis, s.nama, s.kelas, s.jurusan, pr.nama_perusahaan, pb.nama AS pembimbing, p.tgl_mulai, p.tgl_selesai, p.status
     FROM penempatan p
     JOIN siswa s ON s.id_siswa = p.id_siswa
     JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
     JOIN pembimbing pb ON pb.id_pembimbing = p.id_pembimbing
     WHERE ' . implode(' AND ', $w) . ' ORDER BY s.kelas, s.nama'
);
$st->execute($pr);
$rows = $st->fetchAll();
$listKelas = $pdo->query('SELECT DISTINCT kelas FROM siswa WHERE kelas <> "" ORDER BY kelas')->fetchAll(PDO::FETCH_COLUMN);
$listPer = $pdo->query('SELECT id_perusahaan, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan')->fetchAll();
$judul = 'Laporan Data Siswa PKL';
include __DIR__ . '/../includes/header.php';
?>
<form class="toolbar" method="get">
    <div class="fg"><select name="kelas"><?= opsi_list($listKelas, $fk, 'Semua kelas') ?></select></div>
    <div class="fg"><select name="perusahaan"><?= opsi($listPer, 'id_perusahaan', 'nama_perusahaan', $fp, 'Semua perusahaan') ?></select></div>
    <div class="fg"><select name="status"><?= opsi_list(['Belum Mulai', 'Aktif', 'Selesai'], $fs, 'Semua status') ?></select></div>
    <button class="btn btn-primary" type="submit">Tampilkan</button>
    <button class="btn toolbar-r" type="button" onclick="window.print()">Cetak</button>
</form>
<div class="print-head"><h2>Laporan Data Siswa PKL — SMK Hidayah Semarang</h2><small>Dicetak: <?= tgl_id(date('Y-m-d')) ?></small></div>
<div class="table-wrap"><table class="tbl">
    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Perusahaan</th><th>Pembimbing</th><th>Periode</th><th>Status</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td><td><?= e($r['nama_perusahaan']) ?></td>
        <td><?= e($r['pembimbing']) ?></td><td><?= tgl_id($r['tgl_mulai']) ?> – <?= tgl_id($r['tgl_selesai']) ?></td><td><?= badge($r['status']) ?></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="empty">Tidak ada data.</td></tr><?php endif; ?>
</table></div>
<p class="muted-t">Total: <?= count($rows) ?> siswa</p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
