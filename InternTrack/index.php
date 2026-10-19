<?php
require_once __DIR__ . '/config/app.php';
$u = user();
if (!$u) { redirect('auth/login.php'); }
redirect($u['role'] === 'admin' ? 'admin/dashboard.php' : 'siswa/dashboard.php');
