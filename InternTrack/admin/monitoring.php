<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo  = db();
$aksi = $_GET['aksi'] ?? 'list';           // list | tambah | edit | hapus
$id   = (int)($_GET['id'] ?? 0);
$err  = [];
$data = ['id_penempatan' => '', 'tanggal' => date('Y-m-d'), 'catatan' => '', 'nilai' => '', 'rekomendasi' => ''];
$cur  = null;

if (in_array($aksi, ['edit', 'hapus'], true)) {
    $st = $pdo->prepare('SELECT * FROM monitoring WHERE id_monitoring = ?');
    $st->execute([$id]);
    $cur = $st->fetch();
    if (!$cur) { flash('error', 'Data monitoring tidak ditemukan.'); redirect('admin/monitoring.php'); }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $data = array_intersect_key($cur, $data); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    if ($aksi === 'hapus') {
        $pdo->prepare('DELETE FROM monitoring WHERE id_monitoring = ?')->execute([$id]);
        flash('success', 'Data monitoring berhasil dihapus.');
        redirect('admin/monitoring.php');
    }
    foreach ($data as $k => $_) { $data[$k] = trim($_POST[$k] ?? ''); }
    if ($data['id_penempatan'] === '') { $err[] = 'Siswa wajib dipilih.'; }
    if ($data['tanggal'] === '')       { $err[] = 'Tanggal monitoring wajib diisi.'; }
    if ($data['catatan'] === '')       { $err[] = 'Catatan wajib diisi.'; }
    if ($data['nilai'] === '' || !ctype_digit($data['nilai']) || (int)$data['nilai'] > 100) {
        $err[] = 'Nilai wajib diisi dengan angka 0 sampai 100.';
    }
    if (!$err) {
        $pb = $pdo->prepare('SELECT id_pembimbing FROM penempatan WHERE id_penempatan = ?');
        $pb->execute([(int)$data['id_penempatan']]);
        $idPemb = $pb->fetchColumn() ?: null;
        $v = [(int)$data['id_penempatan'], $idPemb, $data['tanggal'], $data['catatan'], (int)$data['nilai'], $data['rekomendasi']];
        if ($aksi === 'edit') {
            $pdo->prepare('UPDATE monitoring SET id_penempatan=?, id_pembimbing=?, tanggal=?, catatan=?, nilai=?, rekomendasi=? WHERE id_monitoring=?')
                ->execute(array_merge($v, [$id]));
        } else {
            $pdo->prepare('INSERT INTO monitoring (id_penempatan, id_pembimbing, tanggal, catatan, nilai, rekomendasi) VALUES (?,?,?,?,?,?)')->execute($v);
        }
        flash('success', 'Data monitoring berhasil disimpan.');
        redirect('admin/monitoring.php');
    }
}

$judul = 'Monitoring PKL';
include __DIR__ . '/../includes/header.php';
echo daftar_error($err);

if ($aksi === 'tambah' || $aksi === 'edit'):
    $listPen = $pdo->query(
        'SELECT p.id_penempatan, s.nama, pr.nama_perusahaan FROM penempatan p
         JOIN siswa s ON s.id_siswa = p.id_siswa JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan ORDER BY s.nama'
    )->fetchAll();
?>
<div class="card">
    <h3><?= $aksi === 'edit' ? 'Edit Monitoring' : 'Tambah Monitoring' ?></h3>
    <form method="post" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="fg"><label>Nama Siswa <b class="req">*</b></label>
                <select name="id_penempatan" required><?= opsi($listPen, 'id_penempatan', function ($r) { return $r['nama'] . ' — ' . $r['nama_perusahaan']; }, $data['id_penempatan'], '-- Pilih siswa --') ?></select></div>
            <?= field('Tanggal Monitoring', 'tanggal', $data['tanggal'], 'date', true) ?>
            <?= field('Nilai (0-100)', 'nilai', $data['nilai'], 'number', true, 'min="0" max="100"') ?>
        </div>
        <?= field('Catatan', 'catatan', $data['catatan'], 'textarea', true) ?>
        <?= field('Rekomendasi', 'rekomendasi', $data['rekomendasi'], 'textarea') ?>
        <button class="btn btn-primary" type="submit">Simpan</button>
        <a class="btn" href="<?= url('admin/monitoring.php') ?>">Batal</a>
    </form>
</div>
<?php else:
    $q = trim($_GET['q'] ?? '');
    $st = $pdo->prepare(
        'SELECT m.*, s.nama, s.nis, pr.nama_perusahaan FROM monitoring m
         JOIN penempatan p ON p.id_penempatan = m.id_penempatan
         JOIN siswa s ON s.id_siswa = p.id_siswa
         JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
         WHERE s.nama LIKE ? OR s.nis LIKE ? OR m.catatan LIKE ?
         ORDER BY m.tanggal DESC, m.id_monitoring DESC'
    );
    $st->execute(["%$q%", "%$q%", "%$q%"]);
    $rows = $st->fetchAll();
?>
<form class="toolbar" method="get">
    <div class="fg"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama siswa / catatan..."></div>
    <button class="btn btn-primary" type="submit">Cari</button>
    <a class="btn" href="<?= url('admin/monitoring.php') ?>">Reset</a>
    <a class="btn btn-primary toolbar-r" href="<?= url('admin/monitoring.php?aksi=tambah') ?>">+ Tambah Monitoring</a>
</form>
<div class="table-wrap"><table class="tbl">
    <tr><th>Tanggal</th><th>Siswa</th><th>Perusahaan</th><th>Catatan</th><th>Nilai</th><th>Rekomendasi</th><th>Aksi</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= tgl_id($r['tanggal']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['nama_perusahaan']) ?></td>
        <td><?= e($r['catatan']) ?></td><td><b><?= (int)$r['nilai'] ?></b></td><td><?= e($r['rekomendasi']) ?></td>
        <td class="act">
            <a class="btn btn-sm" href="<?= url('admin/monitoring.php?aksi=edit&id=' . $r['id_monitoring']) ?>">Edit</a>
            <form class="inline" method="post" action="<?= url('admin/monitoring.php?aksi=hapus&id=' . $r['id_monitoring']) ?>" data-confirm="Hapus data monitoring ini?">
                <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="empty">Belum ada data monitoring.</td></tr><?php endif; ?>
</table></div>
<?php endif;
include __DIR__ . '/../includes/footer.php';
