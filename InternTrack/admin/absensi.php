<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('admin');
$pdo = db();
$STATUS = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
$err = [];
$in  = ['id_penempatan' => '', 'tanggal' => date('Y-m-d'), 'status' => '', 'keterangan' => ''];

// Admin dapat menginput/mengoreksi absensi siswa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($in as $k => $_) { $in[$k] = trim($_POST[$k] ?? ''); }
    if ($in['id_penempatan'] === '') { $err[] = 'Siswa wajib dipilih.'; }
    if ($in['status'] === '' || !in_array($in['status'], $STATUS, true)) { $err[] = 'Status wajib dipilih.'; }
    if ($in['tanggal'] === '') { $err[] = 'Tanggal wajib diisi.'; }
    elseif ($in['tanggal'] > date('Y-m-d')) { $err[] = 'Tanggal absensi tidak boleh melebihi hari ini.'; }
    if (!$err) {
        $pdo->prepare(
            'INSERT INTO absensi (id_penempatan, tanggal, status, keterangan) VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), keterangan = VALUES(keterangan)'
        )->execute([(int)$in['id_penempatan'], $in['tanggal'], $in['status'], $in['keterangan']]);
        flash('success', 'Data absensi berhasil disimpan.');
        redirect('admin/absensi.php');
    }
}

$fs = (int)($_GET['siswa'] ?? 0);
$ft = $_GET['status'] ?? '';
$dr = $_GET['dari'] ?? '';
$sd = $_GET['sampai'] ?? '';
$w = ['1=1']; $pr = [];
if ($fs)        { $w[] = 'p.id_siswa = ?'; $pr[] = $fs; }
if ($ft !== '') { $w[] = 'a.status = ?';   $pr[] = $ft; }
if ($dr !== '') { $w[] = 'a.tanggal >= ?'; $pr[] = $dr; }
if ($sd !== '') { $w[] = 'a.tanggal <= ?'; $pr[] = $sd; }

$st = $pdo->prepare(
    'SELECT a.*, s.nama, s.nis FROM absensi a
     JOIN penempatan p ON p.id_penempatan = a.id_penempatan
     JOIN siswa s ON s.id_siswa = p.id_siswa
     WHERE ' . implode(' AND ', $w) . ' ORDER BY a.tanggal DESC, s.nama LIMIT 200'
);
$st->execute($pr);
$rows  = $st->fetchAll();
$rekap = rekap_kehadiran_semua();
$listPen = $pdo->query('SELECT p.id_penempatan, s.id_siswa, s.nis, s.nama FROM penempatan p JOIN siswa s ON s.id_siswa = p.id_siswa ORDER BY s.nama')->fetchAll();

$judul = 'Kehadiran Siswa PKL';
include __DIR__ . '/../includes/header.php';
echo daftar_error($err);
?>
<div class="card">
    <h3>Input / Koreksi Absensi</h3>
    <form method="post" data-validate novalidate>
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="fg"><label>Siswa <b class="req">*</b></label>
                <select name="id_penempatan" required><?= opsi($listPen, 'id_penempatan', function ($r) { return $r['nis'] . ' - ' . $r['nama']; }, $in['id_penempatan'], '-- Pilih siswa --') ?></select></div>
            <?= field('Tanggal', 'tanggal', $in['tanggal'], 'date', true) ?>
            <div class="fg"><label>Status <b class="req">*</b></label>
                <select name="status" required><?= opsi_list($STATUS, $in['status'], '-- Pilih status --') ?></select></div>
            <?= field('Keterangan', 'keterangan', $in['keterangan']) ?>
        </div>
        <button class="btn btn-primary" type="submit">Simpan Absensi</button>
    </form>
</div>

<div class="card">
    <h3>Rekap Kehadiran per Siswa</h3>
    <div class="table-wrap"><table class="tbl">
        <tr><th>Siswa</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Total Hari</th><th>Persentase</th><th>Status</th></tr>
        <?php foreach ($rekap as $r): ?>
        <tr><td><?= e($r['nama']) ?></td><td><?= $r['hadir'] ?></td><td><?= $r['izin'] ?></td><td><?= $r['sakit'] ?></td><td><?= $r['alpa'] ?></td>
            <td><?= $r['total'] ?></td><td><b><?= $r['pct'] ?>%</b></td><td><?= badge($r['kategori']) ?></td></tr>
        <?php endforeach; if (!$rekap): ?><tr><td colspan="8" class="empty">Belum ada data.</td></tr><?php endif; ?>
    </table></div>
</div>

<h3>Riwayat Absensi</h3>
<form class="toolbar" method="get">
    <div class="fg"><select name="siswa"><?= opsi($listPen, 'id_siswa', 'nama', $fs, 'Semua siswa') ?></select></div>
    <div class="fg"><select name="status"><?= opsi_list($STATUS, $ft, 'Semua status') ?></select></div>
    <div class="fg"><input type="date" name="dari" value="<?= e($dr) ?>" title="Dari tanggal"></div>
    <div class="fg"><input type="date" name="sampai" value="<?= e($sd) ?>" title="Sampai tanggal"></div>
    <button class="btn btn-primary" type="submit">Filter</button>
    <a class="btn" href="<?= url('admin/absensi.php') ?>">Reset</a>
</form>
<div class="table-wrap"><table class="tbl">
    <tr><th>Tanggal</th><th>Siswa</th><th>Status</th><th>Keterangan</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr><td><?= tgl_id($r['tanggal']) ?></td><td><?= e($r['nama']) ?> <small class="muted-t">(<?= e($r['nis']) ?>)</small></td><td><?= badge($r['status']) ?></td><td><?= e($r['keterangan']) ?></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="4" class="empty">Tidak ada data absensi.</td></tr><?php endif; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
