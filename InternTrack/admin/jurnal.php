<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo = db();

$q  = trim($_GET['q'] ?? '');
$fs = (int)($_GET['siswa'] ?? 0);
$dr = $_GET['dari'] ?? '';
$sd = $_GET['sampai'] ?? '';
$w  = ['(j.kegiatan LIKE ? OR j.hasil LIKE ? OR j.kendala LIKE ?)'];
$pr = ["%$q%", "%$q%", "%$q%"];
if ($fs)       { $w[] = 'p.id_siswa = ?'; $pr[] = $fs; }
if ($dr !== '') { $w[] = 'j.tanggal >= ?'; $pr[] = $dr; }
if ($sd !== '') { $w[] = 'j.tanggal <= ?'; $pr[] = $sd; }

$st = $pdo->prepare(
    'SELECT j.*, s.nama, s.nis, pr.nama_perusahaan
     FROM jurnal j
     JOIN penempatan p ON p.id_penempatan = j.id_penempatan
     JOIN siswa s ON s.id_siswa = p.id_siswa
     JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
     WHERE ' . implode(' AND ', $w) . ' ORDER BY j.tanggal DESC, j.id_jurnal DESC'
);
$st->execute($pr);
$rows = $st->fetchAll();
$listSiswa = $pdo->query('SELECT s.id_siswa, s.nama FROM siswa s JOIN penempatan p ON p.id_siswa = s.id_siswa ORDER BY s.nama')->fetchAll();

$judul = 'Aktivitas Siswa (Jurnal Kegiatan)';
include __DIR__ . '/../includes/header.php';
?>
<form class="toolbar" method="get">
    <div class="fg"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari kegiatan..."></div>
    <div class="fg"><select name="siswa"><?= opsi($listSiswa, 'id_siswa', 'nama', $fs, 'Semua siswa') ?></select></div>
    <div class="fg"><input type="date" name="dari" value="<?= e($dr) ?>" title="Dari tanggal"></div>
    <div class="fg"><input type="date" name="sampai" value="<?= e($sd) ?>" title="Sampai tanggal"></div>
    <button class="btn btn-primary" type="submit">Cari / Filter</button>
    <a class="btn" href="<?= url('admin/jurnal.php') ?>">Reset</a>
</form>
<div class="table-wrap"><table class="tbl">
    <tr><th>Tanggal</th><th>Siswa</th><th>Perusahaan</th><th>Kegiatan</th><th>Hasil</th><th>Kendala</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= tgl_id($r['tanggal']) ?></td>
        <td><?= e($r['nama']) ?><br><small class="muted-t"><?= e($r['nis']) ?></small></td>
        <td><?= e($r['nama_perusahaan']) ?></td>
        <td><?= e($r['kegiatan']) ?></td><td><?= e($r['hasil']) ?></td><td><?= e($r['kendala']) ?></td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">Belum ada jurnal.</td></tr><?php endif; ?>
</table></div>
<p class="muted-t">Total: <?= count($rows) ?> jurnal</p>
<?php include __DIR__ . '/../includes/footer.php'; ?>
