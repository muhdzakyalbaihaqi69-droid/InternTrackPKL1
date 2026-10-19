<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('siswa');
$pdo = db();
$p = penempatan_saya();
$err = [];
$in = ['tanggal' => date('Y-m-d'), 'kegiatan' => '', 'hasil' => '', 'kendala' => ''];

if ($p && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    if (($_GET['aksi'] ?? '') === 'hapus') {
        $pdo->prepare('DELETE FROM jurnal WHERE id_jurnal = ? AND id_penempatan = ?')->execute([(int)($_GET['id'] ?? 0), $p['id_penempatan']]);
        flash('success', 'Jurnal berhasil dihapus.');
        redirect('siswa/jurnal.php');
    }
    foreach ($in as $k => $_) { $in[$k] = trim($_POST[$k] ?? ''); }
    // Validasi jurnal: tanggal & kegiatan wajib diisi
    if ($in['tanggal'] === '')  { $err[] = 'Tanggal wajib diisi.'; }
    if ($in['kegiatan'] === '') { $err[] = 'Kegiatan wajib diisi.'; }
    if ($in['tanggal'] !== '' && $in['tanggal'] > date('Y-m-d')) { $err[] = 'Tanggal jurnal tidak boleh melebihi hari ini.'; }
    if (!$err) {
        $pdo->prepare('INSERT INTO jurnal (id_penempatan, tanggal, kegiatan, hasil, kendala) VALUES (?,?,?,?,?)')
            ->execute([$p['id_penempatan'], $in['tanggal'], $in['kegiatan'], $in['hasil'], $in['kendala']]);
        flash('success', 'Jurnal kegiatan berhasil disimpan.');
        redirect('siswa/jurnal.php');
    }
}

$judul = 'Jurnal Kegiatan';
include __DIR__ . '/../includes/header.php';
if (!$p) { tanpa_penempatan(); }
echo daftar_error($err);
$st = $pdo->prepare('SELECT * FROM jurnal WHERE id_penempatan = ? ORDER BY tanggal DESC, id_jurnal DESC');
$st->execute([$p['id_penempatan']]);
$rows = $st->fetchAll();
?>
<div class="card"><h3>Isi Jurnal Kegiatan</h3>
<form method="post" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="form-grid">
        <?= field('Tanggal', 'tanggal', $in['tanggal'], 'date', true) ?>
        <?= field('Kegiatan', 'kegiatan', $in['kegiatan'], 'text', true) ?>
    </div>
    <?= field('Hasil', 'hasil', $in['hasil'], 'textarea') ?>
    <?= field('Kendala', 'kendala', $in['kendala'], 'textarea') ?>
    <button class="btn btn-primary" type="submit">Simpan Jurnal</button>
</form></div>
<h3>Riwayat Jurnal</h3>
<div class="table-wrap"><table class="tbl">
    <tr><th>Tanggal</th><th>Kegiatan</th><th>Hasil</th><th>Kendala</th><th>Aksi</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr><td><?= tgl_id($r['tanggal']) ?></td><td><?= e($r['kegiatan']) ?></td><td><?= e($r['hasil']) ?></td><td><?= e($r['kendala']) ?></td>
        <td><form class="inline" method="post" action="<?= url('siswa/jurnal.php?aksi=hapus&id=' . $r['id_jurnal']) ?>" data-confirm="Hapus jurnal ini?">
            <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="5" class="empty">Belum ada jurnal.</td></tr><?php endif; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
