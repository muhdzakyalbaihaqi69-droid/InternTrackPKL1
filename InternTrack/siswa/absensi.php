<?php
require_once __DIR__ . '/../config/app.php';
wajib_login('siswa');
$pdo = db();
$p = penempatan_saya();
$STATUS = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
$err = [];
$in = ['tanggal' => date('Y-m-d'), 'status' => '', 'keterangan' => ''];

if ($p && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($in as $k => $_) { $in[$k] = trim($_POST[$k] ?? ''); }
    // Validasi absensi: status wajib dipilih
    if (!in_array($in['status'], $STATUS, true)) { $err[] = 'Status wajib dipilih.'; }
    if ($in['tanggal'] === '') { $err[] = 'Tanggal wajib diisi.'; }
    elseif ($in['tanggal'] > date('Y-m-d')) { $err[] = 'Tanggal absensi tidak boleh melebihi hari ini.'; }
    if (!$err) {
        $c = $pdo->prepare('SELECT COUNT(*) FROM absensi WHERE id_penempatan = ? AND tanggal = ?');
        $c->execute([$p['id_penempatan'], $in['tanggal']]);
        if ($c->fetchColumn() > 0) { $err[] = 'Absensi untuk tanggal tersebut sudah diisi.'; }
    }
    if (!$err) {
        $pdo->prepare('INSERT INTO absensi (id_penempatan, tanggal, status, keterangan) VALUES (?,?,?,?)')
            ->execute([$p['id_penempatan'], $in['tanggal'], $in['status'], $in['keterangan']]);
        flash('success', 'Absensi berhasil disimpan.');
        redirect('siswa/absensi.php');
    }
}

$judul = 'Absensi';
include __DIR__ . '/../includes/header.php';
if (!$p) { tanpa_penempatan(); }
echo daftar_error($err);
$rk = rekap_kehadiran_semua('p.id_penempatan = ?', [$p['id_penempatan']])[0];
$st = $pdo->prepare('SELECT * FROM absensi WHERE id_penempatan = ? ORDER BY tanggal DESC');
$st->execute([$p['id_penempatan']]);
$rows = $st->fetchAll();
?>
<div class="card"><h3>Isi Kehadiran</h3>
<form method="post" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="form-grid">
        <?= field('Tanggal', 'tanggal', $in['tanggal'], 'date', true) ?>
        <div class="fg"><label>Status <b class="req">*</b></label><select name="status" required><?= opsi_list($STATUS, $in['status'], '-- Pilih status --') ?></select></div>
        <?= field('Keterangan', 'keterangan', $in['keterangan']) ?>
    </div>
    <button class="btn btn-primary" type="submit">Simpan Absensi</button>
</form></div>
<div class="grid">
    <div class="stat"><small>Hadir</small><b><?= $rk['hadir'] ?></b></div><div class="stat"><small>Izin</small><b><?= $rk['izin'] ?></b></div>
    <div class="stat"><small>Sakit</small><b><?= $rk['sakit'] ?></b></div><div class="stat"><small>Alpa</small><b><?= $rk['alpa'] ?></b></div>
    <div class="stat"><small>Persentase Kehadiran</small><b><?= $rk['pct'] ?>%</b></div>
</div>
<h3>Riwayat Absensi</h3>
<div class="table-wrap"><table class="tbl">
    <tr><th>Tanggal</th><th>Status</th><th>Keterangan</th></tr>
    <?php foreach ($rows as $r): ?><tr><td><?= tgl_id($r['tanggal']) ?></td><td><?= badge($r['status']) ?></td><td><?= e($r['keterangan']) ?></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="3" class="empty">Belum ada absensi.</td></tr><?php endif; ?>
</table></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
