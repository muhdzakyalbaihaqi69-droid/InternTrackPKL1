<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo  = db();
$aksi = $_GET['aksi'] ?? 'list';           // list | tambah | edit | detail | hapus
$id   = (int)($_GET['id'] ?? 0);
$err  = [];
$data = ['nama_perusahaan' => '', 'alamat' => '', 'bidang' => '', 'kontak' => ''];
$cur  = null;

if (in_array($aksi, ['edit', 'detail', 'hapus'], true)) {
    $st = $pdo->prepare('SELECT * FROM perusahaan WHERE id_perusahaan = ?');
    $st->execute([$id]);
    $cur = $st->fetch();
    if (!$cur) { flash('error', 'Data perusahaan tidak ditemukan.'); redirect('admin/perusahaan.php'); }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $data = array_intersect_key($cur, $data); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    if ($aksi === 'hapus') {
        try {
            $pdo->prepare('DELETE FROM perusahaan WHERE id_perusahaan = ?')->execute([$id]);
            flash('success', 'Data perusahaan berhasil dihapus.');
        } catch (PDOException $ex) {
            flash('error', 'Perusahaan tidak dapat dihapus karena masih dipakai pada data penempatan PKL.');
        }
        redirect('admin/perusahaan.php');
    }
    foreach ($data as $k => $_) { $data[$k] = trim($_POST[$k] ?? ''); }
    if ($data['nama_perusahaan'] === '') { $err[] = 'Nama perusahaan tidak boleh kosong.'; }
    if ($data['alamat'] === '')          { $err[] = 'Alamat tidak boleh kosong.'; }
    if (!$err) {
        if ($aksi === 'edit') {
            $pdo->prepare('UPDATE perusahaan SET nama_perusahaan=?, alamat=?, bidang=?, kontak=? WHERE id_perusahaan=?')
                ->execute([$data['nama_perusahaan'], $data['alamat'], $data['bidang'], $data['kontak'], $id]);
        } else {
            $pdo->prepare('INSERT INTO perusahaan (nama_perusahaan, alamat, bidang, kontak) VALUES (?,?,?,?)')
                ->execute(array_values($data));
        }
        flash('success', 'Data perusahaan berhasil disimpan.');
        redirect('admin/perusahaan.php');
    }
}

$judul = 'Data Perusahaan';
include __DIR__ . '/../includes/header.php';
echo daftar_error($err);

if ($aksi === 'tambah' || $aksi === 'edit'): ?>
<div class="card">
    <h3><?= $aksi === 'edit' ? 'Edit Perusahaan' : 'Tambah Perusahaan' ?></h3>
    <form method="post" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <?= field('Nama Perusahaan', 'nama_perusahaan', $data['nama_perusahaan'], 'text', true) ?>
            <?= field('Bidang', 'bidang', $data['bidang']) ?>
            <?= field('Kontak', 'kontak', $data['kontak']) ?>
        </div>
        <?= field('Alamat', 'alamat', $data['alamat'], 'textarea', true) ?>
        <button class="btn btn-primary" type="submit">Simpan</button>
        <a class="btn" href="<?= url('admin/perusahaan.php') ?>">Batal</a>
    </form>
</div>

<?php elseif ($aksi === 'detail'):
    $st = $pdo->prepare('SELECT s.nis, s.nama, s.kelas, p.status FROM penempatan p JOIN siswa s ON s.id_siswa = p.id_siswa WHERE p.id_perusahaan = ? ORDER BY s.nama');
    $st->execute([$id]);
    $peserta = $st->fetchAll();
?>
<div class="card">
    <h3>Detail Perusahaan</h3>
    <dl class="det">
        <dt>Nama Perusahaan</dt><dd><?= e($cur['nama_perusahaan']) ?></dd>
        <dt>Alamat</dt><dd><?= e($cur['alamat']) ?></dd>
        <dt>Bidang</dt><dd><?= e($cur['bidang']) ?></dd>
        <dt>Kontak</dt><dd><?= e($cur['kontak']) ?></dd>
    </dl>
</div>
<div class="card">
    <h3>Siswa PKL di Perusahaan Ini (<?= count($peserta) ?>)</h3>
    <div class="table-wrap"><table class="tbl">
        <tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Status</th></tr>
        <?php foreach ($peserta as $p): ?>
        <tr><td><?= e($p['nis']) ?></td><td><?= e($p['nama']) ?></td><td><?= e($p['kelas']) ?></td><td><?= badge($p['status']) ?></td></tr>
        <?php endforeach; if (!$peserta): ?><tr><td colspan="4" class="empty">Belum ada siswa.</td></tr><?php endif; ?>
    </table></div>
</div>
<a class="btn" href="<?= url('admin/perusahaan.php') ?>">&larr; Kembali</a>

<?php else:
    $q = trim($_GET['q'] ?? '');
    $st = $pdo->prepare('SELECT * FROM perusahaan WHERE nama_perusahaan LIKE ? OR alamat LIKE ? OR bidang LIKE ? ORDER BY nama_perusahaan');
    $st->execute(["%$q%", "%$q%", "%$q%"]);
    $rows = $st->fetchAll();
?>
<form class="toolbar" method="get">
    <div class="fg"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari perusahaan / bidang / alamat..."></div>
    <button class="btn btn-primary" type="submit">Cari</button>
    <a class="btn" href="<?= url('admin/perusahaan.php') ?>">Reset</a>
    <a class="btn btn-primary toolbar-r" href="<?= url('admin/perusahaan.php?aksi=tambah') ?>">+ Tambah Perusahaan</a>
</form>
<div class="table-wrap"><table class="tbl">
    <tr><th>No</th><th>Nama Perusahaan</th><th>Alamat</th><th>Bidang</th><th>Kontak</th><th>Aksi</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nama_perusahaan']) ?></td><td><?= e($r['alamat']) ?></td><td><?= e($r['bidang']) ?></td><td><?= e($r['kontak']) ?></td>
        <td class="act">
            <a class="btn btn-sm" href="<?= url('admin/perusahaan.php?aksi=detail&id=' . $r['id_perusahaan']) ?>">Detail</a>
            <a class="btn btn-sm" href="<?= url('admin/perusahaan.php?aksi=edit&id=' . $r['id_perusahaan']) ?>">Edit</a>
            <form class="inline" method="post" action="<?= url('admin/perusahaan.php?aksi=hapus&id=' . $r['id_perusahaan']) ?>" data-confirm="Hapus perusahaan <?= e($r['nama_perusahaan']) ?>?">
                <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="empty">Data tidak ditemukan.</td></tr><?php endif; ?>
</table></div>
<?php endif;
include __DIR__ . '/../includes/footer.php';
