<?php
// Konfigurasi aplikasi + fungsi bantu (helper) yang dipakai semua halaman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/database.php';

// Deteksi otomatis alamat dasar aplikasi (mis. /InternTrack)
$__root = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
$__dir  = str_replace('\\', '/', dirname(__DIR__));
$__base = ($__root !== '' && strpos($__dir, $__root) === 0) ? substr($__dir, strlen($__root)) : '/InternTrack';
define('BASE_URL', rtrim($__base, '/'));

/* ---------- Umum ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $p = ''): string { return BASE_URL . '/' . ltrim($p, '/'); }
function redirect(string $p): void { header('Location: ' . url($p)); exit; }

function flash($tipe = null, $pesan = null)
{
    if ($tipe !== null) { $_SESSION['flash'] = [$tipe, $pesan]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_cek(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Token keamanan tidak valid. Muat ulang halaman.');
    }
}

/* ---------- Autentikasi ---------- */
function user() { return $_SESSION['user'] ?? null; }

function wajib_login(?string $role = null): void
{
    $u = user();
    if (!$u) { redirect('auth/login.php'); }
    if ($role !== null && $u['role'] !== $role) {
        redirect($u['role'] === 'admin' ? 'admin/dashboard.php' : 'siswa/dashboard.php');
    }
}

function siswa_login()
{
    $st = db()->prepare('SELECT * FROM siswa WHERE id_user = ?');
    $st->execute([user()['id_user']]);
    return $st->fetch() ?: null;
}

function penempatan_saya()
{
    $s = siswa_login();
    if (!$s) { return null; }
    $st = db()->prepare(
        'SELECT p.*, pr.nama_perusahaan, pr.alamat AS alamat_perusahaan, pr.bidang, pr.kontak,
                pb.nama AS pembimbing, pb.nip, pb.no_hp AS hp_pembimbing
         FROM penempatan p
         JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
         JOIN pembimbing pb ON pb.id_pembimbing = p.id_pembimbing
         WHERE p.id_siswa = ?'
    );
    $st->execute([$s['id_siswa']]);
    return $st->fetch() ?: null;
}

// Dipanggil setelah header di halaman siswa bila belum ada penempatan
function tanpa_penempatan(): void
{
    echo '<div class="card"><p>Anda belum ditempatkan di lokasi PKL. Silakan hubungi guru pembimbing.</p></div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

/* ---------- Logika bisnis: status PKL berdasarkan kehadiran ---------- */
function kategori_kehadiran(float $pct, int $total = 1): string
{
    if ($total <= 0) { return 'Belum Ada Data'; }
    if ($pct >= 90) { return 'Sangat Baik'; }
    if ($pct >= 75) { return 'Baik'; }
    if ($pct >= 60) { return 'Perlu Perhatian'; }
    return 'Perlu Pembinaan';
}

function rekap_kehadiran_semua(string $where = '1=1', array $params = []): array
{
    $sql = "SELECT p.id_penempatan, s.id_siswa, s.nis, s.nama, s.kelas, s.jurusan,
                   pr.nama_perusahaan, pb.nama AS pembimbing, p.status,
                   COUNT(a.id_absensi) AS total,
                   COALESCE(SUM(a.status = 'Hadir'), 0) AS hadir,
                   COALESCE(SUM(a.status = 'Izin'), 0)  AS izin,
                   COALESCE(SUM(a.status = 'Sakit'), 0) AS sakit,
                   COALESCE(SUM(a.status = 'Alpa'), 0)  AS alpa
            FROM penempatan p
            JOIN siswa s       ON s.id_siswa = p.id_siswa
            JOIN perusahaan pr ON pr.id_perusahaan = p.id_perusahaan
            JOIN pembimbing pb ON pb.id_pembimbing = p.id_pembimbing
            LEFT JOIN absensi a ON a.id_penempatan = p.id_penempatan
            WHERE $where
            GROUP BY p.id_penempatan, s.id_siswa, s.nis, s.nama, s.kelas, s.jurusan,
                     pr.nama_perusahaan, pb.nama, p.status
            ORDER BY s.nama";
    $st = db()->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();
    foreach ($rows as &$r) {
        $r['pct'] = $r['total'] > 0 ? round($r['hadir'] / $r['total'] * 100, 1) : 0;
        $r['kategori'] = kategori_kehadiran((float)$r['pct'], (int)$r['total']);
    }
    unset($r);
    return $rows;
}

/* ---------- Tampilan ---------- */
function tgl_id(?string $d): string
{
    if (!$d) { return '-'; }
    $bln = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = strtotime($d);
    return date('j', $t) . ' ' . $bln[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function badge(string $teks): string
{
    $map = [
        'Sangat Baik' => 'ok', 'Baik' => 'info', 'Perlu Perhatian' => 'warn', 'Perlu Pembinaan' => 'bad',
        'Aktif' => 'ok', 'Selesai' => 'info', 'Belum Mulai' => 'muted', 'Belum Ada Data' => 'muted',
        'Hadir' => 'ok', 'Izin' => 'info', 'Sakit' => 'warn', 'Alpa' => 'bad',
    ];
    return '<span class="badge b-' . ($map[$teks] ?? 'muted') . '">' . e($teks) . '</span>';
}

// Bar persentase kehadiran berwarna sesuai kategori status PKL
function bar(float $pct, string $kategori): string
{
    $map = ['Sangat Baik' => 'ok', 'Baik' => 'info', 'Perlu Perhatian' => 'warn', 'Perlu Pembinaan' => 'bad'];
    $w = max(0, min(100, $pct));
    return '<div class="bar bar-' . ($map[$kategori] ?? 'muted') . '"><i><u style="width:' . $w . '%"></u></i><b>' . e($pct) . '%</b></div>';
}

function field(string $label, string $name, $val = '', string $type = 'text', bool $req = false, string $extra = ''): string
{
    $r = $req ? 'required' : '';
    $in = $type === 'textarea'
        ? '<textarea id="' . e($name) . '" name="' . e($name) . '" rows="3" ' . $r . ' ' . $extra . '>' . e($val) . '</textarea>'
        : '<input id="' . e($name) . '" type="' . e($type) . '" name="' . e($name) . '" value="' . e($val) . '" ' . $r . ' ' . $extra . '>';
    return '<div class="fg"><label for="' . e($name) . '">' . e($label) . ($req ? ' <b class="req">*</b>' : '') . '</label>' . $in . '</div>';
}

function opsi(array $rows, string $k, $lab, $sel = null, ?string $kosong = null): string
{
    $h = $kosong !== null ? '<option value="">' . e($kosong) . '</option>' : '';
    foreach ($rows as $r) {
        $label = $lab instanceof Closure ? $lab($r) : $r[$lab];
        $h .= '<option value="' . e($r[$k]) . '"' . ((string)$r[$k] === (string)$sel ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    return $h;
}

function opsi_list(array $vals, $sel = null, ?string $kosong = null): string
{
    $h = $kosong !== null ? '<option value="">' . e($kosong) . '</option>' : '';
    foreach ($vals as $v) {
        $h .= '<option value="' . e($v) . '"' . ((string)$v === (string)$sel ? ' selected' : '') . '>' . e($v) . '</option>';
    }
    return $h;
}

function daftar_error(array $err): string
{
    if (!$err) { return ''; }
    return '<div class="alert alert-danger"><ul><li>' . implode('</li><li>', array_map('e', $err)) . '</li></ul></div>';
}
