<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo = db();
$dr = $_GET['dari'] ?? ''; $sd = $_GET['sampai'] ?? '';
$w = ['1=1']; $pr = [];
if ($dr !== '') { $w[] = 'm.tanggal >= ?'; $pr[] = $dr; }
if ($sd !== '') { $w[] = 'm.tanggal <= ?'; $pr[] = $sd; }
$st = $pdo->prepare(
    'SELECT m.*, s.nis, s.nama, s.kelas, pr.nama_perusahaan, pb.nama AS pembimbing
     FROM monitoring m
     JOIN penempatan p ON p.id_penempatan = m.id_penempatan
     JOIN siswa s ON s.id_siswa = p.id_siswa
     JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
     LEFT JOIN pembimbing pb ON pb.id_pembimbing = m.id_pembimbing
     WHERE ' . implode(' AND ', $w) . ' ORDER BY m.tanggal DESC, s.nama'
);
$st->execute($pr);
$rows = $st->fetchAll();
$rata = $rows ? round(array_sum(array_column($rows, 'nilai')) / count($rows), 1) : 0;
$judul = 'Laporan Monitoring';
include __DIR__ . '/../includes/header.php';
?>
<form class="toolbar" method="get">
    <div class="fg"><input type="date" name="dari" value="<?= e($dr) ?>" title="Dari tanggal"></div>
    <div class="fg"><input type="date" name="sampai" value="<?= e($sd) ?>" title="Sampai tanggal"></div>
    <button class="btn btn-primary" type="submit">Tampilkan</button>
    <button class="btn toolbar-r" type="button" onclick="window.print()">Cetak</button>
</form>
<div class="print-head"><h2>Laporan Monitoring PKL — SMK Hidayah Semarang</h2><small>Dicetak: <?= tgl_id(date('Y-m-d')) ?></small></div>
<div class="table-wrap"><table class="tbl">
    <tr><th>No</th><th>Tanggal</th><th>Siswa</th><th>Perusahaan</th><th>Pembimbing</th><th>Catatan</th><th>Nilai</th><th>Rekomendasi</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= tgl_id($r['tanggal']) ?></td><td><?= e($r['nama']) ?><br><small class="muted-t"><?= e($r['kelas']) ?></small></td>
        <td><?= e($r['nama_perusahaan']) ?></td><td><?= e($r['pembimbing'] ?? '-') ?></td><td><?= e($r['catatan']) ?></td><td><b><?= (int)$r['nilai'] ?></b></td><td><?= e($r['rekomendasi']) ?></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="empty">Tidak ada data.</td></tr><?php endif; ?>
</table></div>
<p class="muted-t">Jumlah monitoring: <?= count($rows) ?> &middot; Rata-rata nilai: <b><?= $rata ?></b></p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
