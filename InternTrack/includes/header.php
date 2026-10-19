<?php
// Layout atas: sidebar + judul halaman. Variabel yang dipakai: $judul
$u    = user();
$role = $u['role'] ?? '';
if ($role === 'admin') {
    $menu = [
        ['Dashboard', 'admin/dashboard.php'],
        ['Data Siswa', 'admin/siswa.php'],
        ['Data Perusahaan', 'admin/perusahaan.php'],
        ['Data Pembimbing', 'admin/pembimbing.php'],
        ['Penempatan PKL', 'admin/penempatan.php'],
        ['Aktivitas Siswa', 'admin/jurnal.php'],
        ['Kehadiran', 'admin/absensi.php'],
        ['Monitoring', 'admin/monitoring.php'],
        ['Laporan'],
        ['Laporan Data Siswa', 'laporan/siswa.php'],
        ['Laporan Kehadiran', 'laporan/kehadiran.php'],
        ['Laporan Monitoring', 'laporan/monitoring.php'],
    ];
} else {
    $menu = [
        ['Dashboard', 'siswa/dashboard.php'],
        ['Profil Saya', 'siswa/profil.php'],
        ['Tempat PKL', 'siswa/tempat_pkl.php'],
        ['Jurnal Kegiatan', 'siswa/jurnal.php'],
        ['Absensi', 'siswa/absensi.php'],
        ['Status PKL', 'siswa/status.php'],
        ['Hasil Monitoring', 'siswa/monitoring.php'],
    ];
}
$self = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$f = flash();
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'InternTrack') ?> — InternTrack</title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<aside class="side">
    <div class="brand">Intern<span>Track</span></div>
    <nav>
    <?php foreach ($menu as $m): ?>
        <?php if (count($m) === 1): ?>
            <div class="nav-h"><?= e($m[0]) ?></div>
        <?php else: $on = substr($self, -strlen('/' . $m[1])) === '/' . $m[1]; ?>
            <a class="<?= $on ? 'on' : '' ?>" href="<?= url($m[1]) ?>"><?= e($m[0]) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
    </nav>
    <div class="side-foot">
        <b><?= e($u['nama'] ?? '') ?></b>
        <small><?= $role === 'admin' ? 'Admin / Pembimbing' : 'Siswa' ?></small>
        <a class="btn btn-sm btn-light" href="<?= url('auth/logout.php') ?>">Keluar</a>
    </div>
</aside>
<main>
    <header class="top"><h1><?= e($judul ?? '') ?></h1><span class="tgl"><?= tgl_id(date('Y-m-d')) ?></span></header>
    <?php if ($f): ?><div class="alert alert-<?= e($f[0]) ?>"><?= e($f[1]) ?></div><?php endif; ?>
    <section class="content">
