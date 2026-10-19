<?php
require_once __DIR__ . '/../config/app.php';

if (user()) { redirect('index.php'); }

$err = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi login: username & password wajib diisi
    if ($username === '') { $err[] = 'Username wajib diisi.'; }
    if ($password === '') { $err[] = 'Password wajib diisi.'; }

    if (!$err) {
        $st = db()->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([$username]);
        $u = $st->fetch();
        if ($u && password_verify($password, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id_user'  => (int)$u['id_user'],
                'username' => $u['username'],
                'nama'     => $u['nama'],
                'role'     => $u['role'],
            ];
            redirect('index.php');
        }
        $err[] = 'Username atau password salah.';
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — InternTrack</title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body class="login-body">
<section class="login-hero">
    <div class="brand">Intern<span>Track</span></div>
    <h2>Pantau PKL siswa dalam satu tempat</h2>
    <p>Jurnal harian, kehadiran, dan hasil monitoring tercatat rapi, dan bisa dilihat pembimbing kapan saja.</p>
    <small>SMK Hidayah Semarang</small>
</section>
<section class="login-panel">
    <div class="login-box">
        <h1>Masuk ke akun Anda</h1>
        <p>Gunakan username dan password yang diberikan sekolah.</p>
        <?= daftar_error($err) ?>
        <form method="post" data-validate novalidate>
            <?= csrf_field() ?>
            <?= field('Username', 'username', $username, 'text', true, 'autofocus autocomplete="username"') ?>
            <?= field('Password', 'password', '', 'password', true, 'autocomplete="current-password"') ?>
            <button class="btn btn-primary btn-block" type="submit">Login</button>
        </form>
        <p class="hint">Akun demo: <b>admin</b> / <b>password</b><br>Siswa: <b>2526001</b> / <b>password</b></p>
    </div>
</section>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
