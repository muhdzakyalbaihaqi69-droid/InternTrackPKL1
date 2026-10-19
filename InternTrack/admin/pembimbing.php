<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo  = db();
$aksi = $_GET['aksi'] ?? 'list';           // list | tambah | edit | hapus
$id   = (int)($_GET['id'] ?? 0);
$err  = [];
$data = ['nip' => '', 'nama' => '', 'no_hp' => '', 'bidang' => ''];
$cur  = null;

if (in_array($aksi, ['edit', 'hapus'], true)) {
    $st = $pdo->prepare('SELECT * FROM pembimbing WHERE id_pembimbing = ?');
    $st->execute([$id]);
    $cur = $st->fetch();
    if (!$cur) { flash('error', 'Data pembimbing tidak ditemukan.'); redirect('admin/pembimbing.php'); }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $data = array_intersect_key($cur, $data); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    if ($aksi === 'hapus') {
        try {
            $pdo->prepare('DELETE FROM pembimbing WHERE id_pembimbing = ?')->execute([$id]);
            flash('success', 'Data pembimbing berhasil dihapus.');
        } catch (PDOException $ex) {
            flash('error', 'Pembimbing tidak dapat dihapus karena masih dipakai pada data penempatan PKL.');
        }
        redirect('admin/pembimbing.php');
    }
    foreach ($data as $k => $_) { $data[$k] = trim($_POST[$k] ?? ''); }
    if ($data['nama'] === '') { $err[] = 'Nama pembimbing tidak boleh kosong.'; }
    if (!$err) {
        if ($aksi === 'edit') {
            $pdo->prepare('UPDATE pembimbing SET nip=?, nama=?, no_hp=?, bidang=? WHERE id_pembimbing=?')
                ->execute([$data['nip'], $data['nama'], $data['no_hp'], $data['bidang'], $id]);
        } else {
            $pdo->prepare('INSERT INTO pembimbing (nip, nama, no_hp, bidang) VALUES (?,?,?,?)')->execute(array_values($data));
        }
        flash('success', 'Data pembimbing berhasil disimpan.');
        redirect('admin/pembimbing.php');
    }
}

$judul = 'Data Guru Pembimbing';
include __DIR__ . '/../includes/header.php';
echo daftar_error($err);

if ($aksi === 'tambah' || $aksi === 'edit'): ?>
<div class="card">
    <h3><?= $aksi === 'edit' ? 'Edit Pembimbing' : 'Tambah Pembimbing' ?></h3>
    <form method="post" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <?= field('NIP', 'nip', $data['nip']) ?>
            <?= field('Nama', 'nama', $data['nama'], 'text', true) ?>
            <?= field('No. HP', 'no_hp', $data['no_hp']) ?>
            <?= field('Bidang / Mapel', 'bidang', $data['bidang']) ?>
        </div>
        <button class="btn btn-primary" type="submit">Simpan</button>
        <a class="btn" href="<?= url('admin/pembimbing.php') ?>">Batal</a>
    </form>
</div>
<?php else:
    $q = trim($_GET['q'] ?? '');
    $st = $pdo->prepare(
        'SELECT pb.*, COUNT(p.id_penempatan) AS jml_siswa FROM pembimbing pb
         LEFT JOIN penempatan p ON p.id_pembimbing = pb.id_pembimbing
         WHERE pb.nama LIKE ? OR pb.nip LIKE ? OR pb.bidang LIKE ?
         GROUP BY pb.id_pembimbing, pb.nip, pb.nama, pb.no_hp, pb.bidang ORDER BY pb.nama'
    );
    $st->execute(["%$q%", "%$q%", "%$q%"]);
    $rows = $st->fetchAll();
?>
<form class="toolbar" method="get">
    <div class="fg"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama / NIP / bidang..."></div>
    <button class="btn btn-primary" type="submit">Cari</button>
    <a class="btn" href="<?= url('admin/pembimbing.php') ?>">Reset</a>
    <a class="btn btn-primary toolbar-r" href="<?= url('admin/pembimbing.php?aksi=tambah') ?>">+ Tambah Pembimbing</a>
</form>
<div class="table-wrap"><table class="tbl">
    <tr><th>No</th><th>NIP</th><th>Nama</th><th>No. HP</th><th>Bidang</th><th>Siswa Dibimbing</th><th>Aksi</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nip']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['no_hp']) ?></td><td><?= e($r['bidang']) ?></td><td><?= (int)$r['jml_siswa'] ?></td>
        <td class="act">
            <a class="btn btn-sm" href="<?= url('admin/pembimbing.php?aksi=edit&id=' . $r['id_pembimbing']) ?>">Edit</a>
            <form class="inline" method="post" action="<?= url('admin/pembimbing.php?aksi=hapus&id=' . $r['id_pembimbing']) ?>" data-confirm="Hapus pembimbing <?= e($r['nama']) ?>?">
                <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="empty">Data tidak ditemukan.</td></tr><?php endif; ?>
</table></div>
<?php endif;
include __DIR__ . '/../includes/footer.php';
