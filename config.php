<?php
session_start();

// PASTIKAN BLOK INI ADA DAN BENAR
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$folder = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_URL', $protocol . $domainName . $folder . '/');
// ------------------------------------

// --- PENGATURAN KONEKSI DATABASE ---
// Sesuaikan dengan pengaturan database Anda
define('DB_HOST', 'localhost');
define('DB_NAME', 'zakiymyi_kemenag_wbs'); // Nama database yang Anda buat
define('DB_USER', 'zakiymyi_kemenag_wbs');       // Username database Anda
define('DB_PASS', ')-B]H%JI,81O+WNO');           // Password database Anda

// Atur zona waktu default
date_default_timezone_set('Asia/Jakarta');

// Buat koneksi PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Atur mode error PDO ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Atur mode fetch default ke associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Jika koneksi gagal, hentikan skrip dan tampilkan pesan error
    die("Koneksi ke database gagal: " . $e->getMessage());
}

// --- FUNGSI BANTUAN (HELPER FUNCTIONS) ---

/**
 * Fungsi untuk memeriksa apakah admin sudah login.
 * Jika belum, akan dialihkan ke halaman login.
 */
function check_admin_login() {
    if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
        redirect('login'); // Menggunakan URL bersih
        exit;
    }
}

/**
 * Fungsi untuk melakukan pengalihan halaman (redirect).
 * @param string $url URL tujuan (tanpa .php)
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Fungsi untuk mencatat log aktivitas admin
 * @param PDO $pdo Koneksi PDO
 * @param int $admin_id ID admin yang melakukan aksi
 * @param string $aktivitas Deskripsi aktivitas
 */
function log_activity($pdo, $admin_id, $aktivitas) {
    $sql = "INSERT INTO log_aktivitas (admin_id, aktivitas) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_id, $aktivitas]);
}

// --- FUNGSI BANTUAN (HELPER FUNCTIONS) ---
// ... (fungsi check_admin_login, redirect, log_activity yang sudah ada) ...


/**
 * Fungsi untuk mengubah format tanggal ke format Indonesia lengkap.
 * Contoh output: Senin, 30 Juni 2025 13:48 WIB
 * @param string $timestamp Timestamp dari database (misal: '2025-06-30 13:48:41')
 * @return string Tanggal yang sudah diformat
 */
function format_tanggal_indonesia($timestamp) {
    if (empty($timestamp)) {
        return '';
    }

    $hari_array = [
        1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'
    ];

    $bulan_array = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    // Ubah string timestamp menjadi objek DateTime
    $date = new DateTime($timestamp);

    $hari = $hari_array[$date->format('N')];
    $tanggal = $date->format('j');
    $bulan = $bulan_array[$date->format('n')];
    $tahun = $date->format('Y');
    $waktu = $date->format('H:i');

    return "{$hari}, {$tanggal} {$bulan} {$tahun} {$waktu} WIB";
}

?>