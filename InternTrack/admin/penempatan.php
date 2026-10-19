<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo  = db();
$aksi = $_GET['aksi'] ?? 'list';           // list | tambah | edit | hapus | status
$id   = (int)($_GET['id'] ?? 0);
$err  = [];
$STATUS = ['Belum Mulai', 'Aktif', 'Selesai'];
$data = ['id_siswa' => '', 'id_perusahaan' => '', 'id_pembimbing' => '', 'tgl_mulai' => '', 'tgl_selesai' => '', 'status' => 'Belum Mulai'];
$cur  = null;

if (in_array($aksi, ['edit', 'hapus', 'status'], true)) {
    $st = $pdo->prepare('SELECT * FROM penempatan WHERE id_penempatan = ?');
    $st->execute([$id]);
    $cur = $st->fetch();
    if (!$cur) { flash('error', 'Data penempatan tidak ditemukan.'); redirect('admin/penempatan.php'); }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $data = array_intersect_key($cur, $data); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();

    if ($aksi === 'hapus') {
        $pdo->prepare('DELETE FROM penempatan WHERE id_penempatan = ?')->execute([$id]);
        flash('success', 'Data penempatan berhasil dihapus.');
        redirect('admin/penempatan.php');
    }
    if ($aksi === 'status') {                       // ubah status PKL cepat dari daftar
        $s = $_POST['status'] ?? '';
        if (in_array($s, $STATUS, true)) {
            $pdo->prepare('UPDATE penempatan SET status = ? WHERE id_penempatan = ?')->execute([$s, $id]);
            flash('success', 'Status PKL berhasil diubah menjadi ' . $s . '.');
        }
        redirect('admin/penempatan.php');
    }

    foreach ($data as $k => $_) { $data[$k] = trim($_POST[$k] ?? ''); }
    if ($data['id_siswa'] === '')      { $err[] = 'Siswa wajib dipilih.'; }
    if ($data['id_perusahaan'] === '') { $err[] = 'Perusahaan wajib dipilih.'; }
    if ($data['id_pembimbing'] === '') { $err[] = 'Pembimbing wajib dipilih.'; }
    if ($data['tgl_mulai'] === '')     { $err[] = 'Tanggal mulai wajib diisi.'; }
    if ($data['tgl_selesai'] === '')   { $err[] = 'Tanggal selesai wajib diisi.'; }
    if ($data['tgl_mulai'] !== '' && $data['tgl_selesai'] !== '' && $data['tgl_selesai'] < $data['tgl_mulai']) {
        $err[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
    }
    if (!in_array($data['status'], $STATUS, true)) { $err[] = 'Status PKL tidak valid.'; }
    if ($data['id_siswa'] !== '') {
        $c = $pdo->prepare('SELECT COUNT(*) FROM penempatan WHERE id_siswa = ? AND id_penempatan <> ?');
        $c->execute([$data['id_siswa'], $id]);
        if ($c->fetchColumn() > 0) { $err[] = 'Siswa tersebut sudah memiliki penempatan PKL.'; }
    }
    if (!$err) {
        try {
            $v = [(int)$data['id_siswa'], (int)$data['id_perusahaan'], (int)$data['id_pembimbing'], $data['tgl_mulai'], $data['tgl_selesai'], $data['status']];
            if ($aksi === 'edit') {
                $pdo->prepare('UPDATE penempatan SET id_siswa=?, id_perusahaan=?, id_pembimbing=?, tgl_mulai=?, tgl_selesai=?, status=? WHERE id_penempatan=?')
                    ->execute(array_merge($v, [$id]));
            } else {
                $pdo->prepare('INSERT INTO penempatan (id_siswa, id_perusahaan, id_pembimbing, tgl_mulai, tgl_selesai, status) VALUES (?,?,?,?,?,?)')->execute($v);
            }
            flash('success', 'Data penempatan berhasil disimpan.');
            redirect('admin/penempatan.php');
        } catch (PDOException $ex) {
            $err[] = 'Data gagal disimpan. Periksa kembali pilihan siswa, perusahaan, dan pembimbing.';
        }
    }
}

$judul = 'Penempatan PKL';
include __DIR__ . '/../includes/header.php';
echo daftar_error($err);

if ($aksi === 'tambah' || $aksi === 'edit'):
    $sp = $pdo->prepare('SELECT id_siswa, nis, nama FROM siswa WHERE id_siswa NOT IN (SELECT id_siswa FROM penempatan) OR id_siswa = ? ORDER BY nama');
    $sp->execute([(int)$data['id_siswa']]);
    $listSiswa = $sp->fetchAll();
    $listPer = $pdo->query('SELECT id_perusahaan, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan')->fetchAll();
    $listPem = $pdo->query('SELECT id_pembimbing, nama FROM pembimbing ORDER BY nama')->fetchAll();
?>
<div class="card">
    <h3><?= $aksi === 'edit' ? 'Edit Penempatan' : 'Tambah Penempatan' ?></h3>
    <p class="muted-t">Alur: Siswa &rarr; Perusahaan &rarr; Pembimbing</p>
    <form method="post" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="fg"><label>Siswa <b class="req">*</b></label>
                <select name="id_siswa" required><?= opsi($listSiswa, 'id_siswa', function ($r) { return $r['nis'] . ' - ' . $r['nama']; }, $data['id_siswa'], '-- Pilih siswa --') ?></select></div>
            <div class="fg"><label>Perusahaan <b class="req">*</b></label>
                <select name="id_perusahaan" required><?= opsi($listPer, 'id_perusahaan', 'nama_perusahaan', $data['id_perusahaan'], '-- Pilih perusahaan --') ?></select></div>
            <div class="fg"><label>Pembimbing <b class="req">*</b></label>
                <select name="id_pembimbing" required><?= opsi($listPem, 'id_pembimbing', 'nama', $data['id_pembimbing'], '-- Pilih pembimbing --') ?></select></div>
            <?= field('Tanggal Mulai', 'tgl_mulai', $data['tgl_mulai'], 'date', true) ?>
            <?= field('Tanggal Selesai', 'tgl_selesai', $data['tgl_selesai'], 'date', true) ?>
            <div class="fg"><label>Status PKL</label><select name="status"><?= opsi_list($STATUS, $data['status']) ?></select></div>
        </div>
        <button class="btn btn-primary" type="submit">Simpan</button>
        <a class="btn" href="<?= url('admin/penempatan.php') ?>">Batal</a>
    </form>
</div>
<?php else:
    $q = trim($_GET['q'] ?? ''); $fs = $_GET['status'] ?? '';
    $w = ['(s.nama LIKE ? OR s.nis LIKE ? OR pr.nama_perusahaan LIKE ?)']; $pr = ["%$q%", "%$q%", "%$q%"];
    if ($fs !== '') { $w[] = 'p.status = ?'; $pr[] = $fs; }
    $st = $pdo->prepare(
        'SELECT p.*, s.nis, s.nama, pr.nama_perusahaan, pb.nama AS pembimbing
         FROM penempatan p
         JOIN siswa s ON s.id_siswa = p.id_siswa
         JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
         JOIN pembimbing pb ON pb.id_pembimbing = p.id_pembimbing
         WHERE ' . implode(' AND ', $w) . ' ORDER BY s.nama'
    );
    $st->execute($pr);
    $rows = $st->fetchAll();
?>
<form class="toolbar" method="get">
    <div class="fg"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari siswa / perusahaan..."></div>
    <div class="fg"><select name="status"><?= opsi_list($STATUS, $fs, 'Semua status') ?></select></div>
    <button class="btn btn-primary" type="submit">Cari / Filter</button>
    <a class="btn" href="<?= url('admin/penempatan.php') ?>">Reset</a>
    <a class="btn btn-primary toolbar-r" href="<?= url('admin/penempatan.php?aksi=tambah') ?>">+ Tambah Penempatan</a>
</form>
<div class="table-wrap"><table class="tbl">
    <tr><th>No</th><th>Siswa</th><th>Perusahaan</th><th>Pembimbing</th><th>Periode</th><th>Status</th><th>Ubah Status</th><th>Aksi</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($r['nama']) ?><br><small class="muted-t"><?= e($r['nis']) ?></small></td>
        <td><?= e($r['nama_perusahaan']) ?></td><td><?= e($r['pembimbing']) ?></td>
        <td><?= tgl_id($r['tgl_mulai']) ?><br><small class="muted-t">s.d. <?= tgl_id($r['tgl_selesai']) ?></small></td>
        <td><?= badge($r['status']) ?></td>
        <td>
            <form class="inline" method="post" action="<?= url('admin/penempatan.php?aksi=status&id=' . $r['id_penempatan']) ?>">
                <?= csrf_field() ?>
                <select name="status" style="width:auto"><?= opsi_list($STATUS, $r['status']) ?></select>
                <button class="btn btn-sm" type="submit">Ubah</button>
            </form>
        </td>
        <td class="act">
            <a class="btn btn-sm" href="<?= url('admin/penempatan.php?aksi=edit&id=' . $r['id_penempatan']) ?>">Edit</a>
            <form class="inline" method="post" action="<?= url('admin/penempatan.php?aksi=hapus&id=' . $r['id_penempatan']) ?>" data-confirm="Hapus penempatan <?= e($r['nama']) ?>? Jurnal, absensi, dan monitoring ikut terhapus.">
                <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="empty">Data tidak ditemukan.</td></tr><?php endif; ?>
</table></div>
<?php endif;
include __DIR__ . '/../includes/footer.php';
