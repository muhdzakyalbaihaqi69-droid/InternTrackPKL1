<?php
// Konfigurasi koneksi database (sesuaikan dengan server lokal Anda)
const DB_HOST = 'localhost';
const DB_NAME = 'interntrack';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $ex) {
            http_response_code(500);
            exit('Koneksi database gagal. Pastikan database "interntrack" sudah diimpor dan config/database.php benar.');
        }
    }
    return $pdo;
}
