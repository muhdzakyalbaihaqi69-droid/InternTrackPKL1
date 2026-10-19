<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo  = db();
$aksi = $_GET['aksi'] ?? 'list';           // list | tambah | edit | detail | hapus
$id   = (int)($_GET['id'] ?? 0);
$err  = [];
$data = ['nis' => '', 'nama' => '', 'kelas' => '', 'jurusan' => '', 'no_hp' => '', 'alamat' => ''];
$cur  = null;

if (in_array($aksi, ['edit', 'detail', 'hapus'], true)) {
    $st = $pdo->prepare('SELECT * FROM siswa WHERE id_siswa = ?');
    $st->execute([$id]);
    $cur = $st->fetch();
    if (!$cur) { flash('error', 'Data siswa tidak ditemukan.'); redirect('admin/siswa.php'); }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $data = array_intersect_key($cur, $data); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();

    if ($aksi === 'hapus') {
        // Menghapus akun user otomatis menghapus siswa, penempatan, jurnal, absensi, monitoring (ON DELETE CASCADE)
        $pdo->prepare('DELETE FROM users WHERE id_user = ?')->execute([$cur['id_user']]);
        flash('success', 'Data siswa berhasil dihapus.');
        redirect('admin/siswa.php');
    }

    foreach ($data as $k => $_) { $data[$k] = trim($_POST[$k] ?? ''); }
    $password = $_POST['password'] ?? '';

    // Validasi data siswa
    if ($data['nis'] === '')  { $err[] = 'NIS tidak boleh kosong.'; }
    if ($data['nama'] === '') { $err[] = 'Nama tidak boleh kosong.'; }
    if ($data['nis'] !== '') {
        $c = $pdo->prepare('SELECT COUNT(*) FROM siswa WHERE nis = ? AND id_siswa <> ?');
        $c->execute([$data['nis'], $id]);
        if ($c->fetchColumn() > 0) { $err[] = 'NIS tidak boleh duplikat. NIS tersebut sudah terdaftar.'; }
    }

    if (!$err) {
        try {
            $pdo->beginTransaction();
            if ($aksi === 'edit') {
                $pdo->prepare('UPDATE siswa SET nis=?, nama=?, kelas=?, jurusan=?, no_hp=?, alamat=? WHERE id_siswa=?')
                    ->execute([$data['nis'], $data['nama'], $data['kelas'], $data['jurusan'], $data['no_hp'], $data['alamat'], $id]);
                $pdo->prepare('UPDATE users SET username=?, nama=? WHERE id_user=?')
                    ->execute([$data['nis'], $data['nama'], $cur['id_user']]);
                if ($password !== '') {
                    $pdo->prepare('UPDATE users SET password=? WHERE id_user=?')
                        ->execute([password_hash($password, PASSWORD_DEFAULT), $cur['id_user']]);
                }
            } else {
                // Akun login siswa dibuat otomatis: username = NIS, password awal = NIS (jika kosong)
                $pass = $password !== '' ? $password : $data['nis'];
                $pdo->prepare("INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, 'siswa')")
                    ->execute([$data['nis'], password_hash($pass, PASSWORD_DEFAULT), $data['nama']]);
                $idUser = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO siswa (id_user, nis, nama, kelas, jurusan, no_hp, alamat) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$idUser, $data['nis'], $data['nama'], $data['kelas'], $data['jurusan'], $data['no_hp'], $data['alamat']]);
            }
            $pdo->commit();
            flash('success', 'Data siswa berhasil disimpan.');
            redirect('admin/siswa.php');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $err[] = 'Data gagal disimpan (NIS/username sudah dipakai atau data tidak valid).';
        }
    }
}

$judul = 'Data Siswa';
include __DIR__ . '/../includes/header.php';
echo daftar_error($err);

/* ======================= FORM TAMBAH / EDIT ======================= */
if ($aksi === 'tambah' || $aksi === 'edit'): ?>
<div class="card">
    <h3><?= $aksi === 'edit' ? 'Edit Siswa' : 'Tambah Siswa' ?></h3>
    <form method="post" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <?= field('NIS', 'nis', $data['nis'], 'text', true) ?>
            <?= field('Nama', 'nama', $data['nama'], 'text', true) ?>
            <?= field('Kelas', 'kelas', $data['kelas']) ?>
            <?= field('Jurusan', 'jurusan', $data['jurusan']) ?>
            <?= field('No. HP', 'no_hp', $data['no_hp']) ?>
            <?= field($aksi === 'edit' ? 'Password baru (kosongkan jika tidak diubah)' : 'Password (kosong = sama dengan NIS)', 'password', '', 'password') ?>
        </div>
        <?= field('Alamat', 'alamat', $data['alamat'], 'textarea') ?>
        <button class="btn btn-primary" type="submit">Simpan</button>
        <a class="btn" href="<?= url('admin/siswa.php') ?>">Batal</a>
    </form>
</div>

<?php /* ======================= DETAIL ======================= */
elseif ($aksi === 'detail'):
    $st = $pdo->prepare(
        'SELECT p.*, pr.nama_perusahaan, pb.nama AS pembimbing FROM penempatan p
         JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
         JOIN pembimbing pb ON pb.id_pembimbing = p.id_pembimbing WHERE p.id_siswa = ?'
    );
    $st->execute([$id]);
    $pen = $st->fetch();
    $rk  = $pen ? rekap_kehadiran_semua('p.id_penempatan = ?', [$pen['id_penempatan']])[0] : null;
    $jj  = 0; $nl = null;
    if ($pen) {
        $x = $pdo->prepare('SELECT COUNT(*) FROM jurnal WHERE id_penempatan = ?'); $x->execute([$pen['id_penempatan']]); $jj = (int)$x->fetchColumn();
        $x = $pdo->prepare('SELECT ROUND(AVG(nilai),1) FROM monitoring WHERE id_penempatan = ?'); $x->execute([$pen['id_penempatan']]); $nl = $x->fetchColumn();
    }
?>
<div class="card">
    <h3>Detail Siswa</h3>
    <dl class="det">
        <dt>NIS</dt><dd><?= e($cur['nis']) ?></dd>
        <dt>Nama</dt><dd><?= e($cur['nama']) ?></dd>
        <dt>Kelas</dt><dd><?= e($cur['kelas']) ?></dd>
        <dt>Jurusan</dt><dd><?= e($cur['jurusan']) ?></dd>
        <dt>No. HP</dt><dd><?= e($cur['no_hp']) ?></dd>
        <dt>Alamat</dt><dd><?= e($cur['alamat']) ?></dd>
    </dl>
</div>
<div class="card">
    <h3>Informasi PKL</h3>
    <?php if ($pen): ?>
    <dl class="det">
        <dt>Perusahaan</dt><dd><?= e($pen['nama_perusahaan']) ?></dd>
        <dt>Pembimbing</dt><dd><?= e($pen['pembimbing']) ?></dd>
        <dt>Periode</dt><dd><?= tgl_id($pen['tgl_mulai']) ?> s.d. <?= tgl_id($pen['tgl_selesai']) ?></dd>
        <dt>Status PKL</dt><dd><?= badge($pen['status']) ?></dd>
        <dt>Kehadiran</dt><dd><?= $rk['hadir'] ?> dari <?= $rk['total'] ?> hari (<?= $rk['pct'] ?>%) <?= badge($rk['kategori']) ?></dd>
        <dt>Jumlah Jurnal</dt><dd><?= $jj ?></dd>
        <dt>Rata-rata Nilai</dt><dd><?= $nl !== null && $nl !== false ? e($nl) : '-' ?></dd>
    </dl>
    <?php else: ?><p class="muted-t">Siswa belum ditempatkan PKL.</p><?php endif; ?>
</div>
<a class="btn" href="<?= url('admin/siswa.php') ?>">&larr; Kembali</a>

<?php /* ======================= DAFTAR ======================= */
else:
    $q = trim($_GET['q'] ?? ''); $fk = $_GET['kelas'] ?? ''; $fs = $_GET['status'] ?? '';
    $fp = (int)($_GET['perusahaan'] ?? 0); $fb = (int)($_GET['pembimbing'] ?? 0);
    $w = ['1=1']; $pr = [];
    if ($q !== '')  { $w[] = '(s.nama LIKE ? OR s.nis LIKE ?)'; $pr[] = "%$q%"; $pr[] = "%$q%"; }
    if ($fk !== '') { $w[] = 's.kelas = ?';        $pr[] = $fk; }
    if ($fs !== '') { $w[] = 'p.status = ?';       $pr[] = $fs; }
    if ($fp)        { $w[] = 'p.id_perusahaan = ?'; $pr[] = $fp; }
    if ($fb)        { $w[] = 'p.id_pembimbing = ?'; $pr[] = $fb; }
    $st = $pdo->prepare(
        'SELECT s.*, p.status, pr.nama_perusahaan, pb.nama AS pembimbing
         FROM siswa s
         LEFT JOIN penempatan p  ON p.id_siswa = s.id_siswa
         LEFT JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
         LEFT JOIN pembimbing pb ON pb.id_pembimbing = p.id_pembimbing
         WHERE ' . implode(' AND ', $w) . ' ORDER BY s.nama'
    );
    $st->execute($pr);
    $rows = $st->fetchAll();
    $listKelas = $pdo->query('SELECT DISTINCT kelas FROM siswa WHERE kelas <> "" ORDER BY kelas')->fetchAll(PDO::FETCH_COLUMN);
    $listPer = $pdo->query('SELECT id_perusahaan, nama_perusahaan FROM perusahaan ORDER BY nama_perusahaan')->fetchAll();
    $listPem = $pdo->query('SELECT id_pembimbing, nama FROM pembimbing ORDER BY nama')->fetchAll();
?>
<form class="toolbar" method="get">
    <div class="fg"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama / NIS..."></div>
    <div class="fg"><select name="kelas"><?= opsi_list($listKelas, $fk, 'Semua kelas') ?></select></div>
    <div class="fg"><select name="perusahaan"><?= opsi($listPer, 'id_perusahaan', 'nama_perusahaan', $fp, 'Semua perusahaan') ?></select></div>
    <div class="fg"><select name="status"><?= opsi_list(['Belum Mulai', 'Aktif', 'Selesai'], $fs, 'Semua status PKL') ?></select></div>
    <div class="fg"><select name="pembimbing"><?= opsi($listPem, 'id_pembimbing', 'nama', $fb, 'Semua pembimbing') ?></select></div>
    <button class="btn btn-primary" type="submit">Cari / Filter</button>
    <a class="btn" href="<?= url('admin/siswa.php') ?>">Reset</a>
    <a class="btn btn-primary toolbar-r" href="<?= url('admin/siswa.php?aksi=tambah') ?>">+ Tambah Siswa</a>
</form>
<div class="table-wrap"><table class="tbl">
    <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jurusan</th><th>No. HP</th><th>Perusahaan</th><th>Status PKL</th><th>Aksi</th></tr>
    <?php foreach ($rows as $i => $r): ?>
    <tr>
        <td><?= $i + 1 ?></td><td><?= e($r['nis']) ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kelas']) ?></td>
        <td><?= e($r['jurusan']) ?></td><td><?= e($r['no_hp']) ?></td>
        <td><?= e($r['nama_perusahaan'] ?? '-') ?></td><td><?= $r['status'] ? badge($r['status']) : '-' ?></td>
        <td class="act">
            <a class="btn btn-sm" href="<?= url('admin/siswa.php?aksi=detail&id=' . $r['id_siswa']) ?>">Detail</a>
            <a class="btn btn-sm" href="<?= url('admin/siswa.php?aksi=edit&id=' . $r['id_siswa']) ?>">Edit</a>
            <form class="inline" method="post" action="<?= url('admin/siswa.php?aksi=hapus&id=' . $r['id_siswa']) ?>" data-confirm="Hapus siswa <?= e($r['nama']) ?> beserta seluruh data PKL-nya?">
                <?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="9" class="empty">Data tidak ditemukan.</td></tr><?php endif; ?>
</table></div>
<?php endif;
include __DIR__ . '/../includes/footer.php';
