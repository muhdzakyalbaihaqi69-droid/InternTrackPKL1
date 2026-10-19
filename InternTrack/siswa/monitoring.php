<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('siswa');
$pdo = db();
$p = penempatan_saya();
$judul = 'Hasil Monitoring';
include __DIR__ . '/../includes/header.php';
if (!$p) { tanpa_penempatan(); }
$st = $pdo->prepare('SELECT m.*, pb.nama AS pembimbing FROM monitoring m LEFT JOIN pembimbing pb ON pb.id_pembimbing = m.id_pembimbing WHERE m.id_penempatan = ? ORDER BY m.tanggal DESC, m.id_monitoring DESC');
$st->execute([$p['id_penempatan']]);
$rows = $st->fetchAll();
?>
<div class="table-wrap"><table class="tbl">
    <tr><th>Tanggal</th><th>Pembimbing</th><th>Catatan</th><th>Nilai</th><th>Rekomendasi</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr><td><?= tgl_id($r['tanggal']) ?></td><td><?= e($r['pembimbing'] ?? '-') ?></td><td><?= e($r['catatan']) ?></td><td><b><?= (int)$r['nilai'] ?></b></td><td><?= e($r['rekomendasi']) ?></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="5" class="empty">Belum ada hasil monitoring.</td></tr><?php endif; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
