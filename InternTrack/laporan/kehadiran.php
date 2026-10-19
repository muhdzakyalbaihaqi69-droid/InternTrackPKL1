<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo = db();
$fk = $_GET['kelas'] ?? ''; $fp = (int)($_GET['perusahaan'] ?? 0);
$w = ['1=1']; $pr = [];
if ($fk !== '') { $w[] = 's.kelas = ?'; $pr[] = $fk; }
if ($fp)        { $w[] = 'p.id_perusahaan = ?'; $pr[] = $fp; }
$rows = rekap_kehadiran_semua(implode(' AND ', $w), $pr);
$listKelas = $pdo->query('SELECT DISTINCT kelas FROM siswa WHERE kelas <> "" ORDER BY kelas')->fetchAll(PDO::FETCH_COLUMN);
$listPer = $pdo->query('SELECT id_perusahaan, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan')->fetchAll();
$judul = 'Laporan Kehadiran';
include __DIR__ . '/../includes/header.php';
?>
<form class="toolbar" method="get">
    <div class="fg"><select name="kelas"><?= opsi_list($listKelas, $fk, 'Semua kelas') ?></select></div>
    <div class="fg"><select name="perusahaan"><?= opsi($listPer, 'id_perusahaan', 'nama_perusahaan', $fp, 'Semua perusahaan') ?></select></div>
    <button class="btn btn-primary" type="submit">Tampilkan</button>
    <button class="btn toolbar-r" type="button" onclick="window.print()">Cetak</button>
</form>
<div class="print-head"><h2>Laporan Kehadiran Siswa PKL — SMK Hidayah Semarang</h2><small>Dicetak: <?= tgl_id(date('Y-m-d')) ?></small></div>
<div class="table-wrap"><table class="tbl">
    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Perusahaan</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Total</th><th>%</th><th>Status</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td><td><?= e($r['nama_perusahaan']) ?></td>
        <td><?= $r['hadir'] ?></td><td><?= $r['izin'] ?></td><td><?= $r['sakit'] ?></td><td><?= $r['alpa'] ?></td><td><?= $r['total'] ?></td>
        <td><b><?= $r['pct'] ?>%</b></td><td><?= badge($r['kategori']) ?></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="12" class="empty">Tidak ada data.</td></tr><?php endif; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
