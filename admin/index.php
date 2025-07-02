<?php
require_once '../config.php';

// Cek jika admin sudah login
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Jika sudah, arahkan ke dashboard
    redirect('dashboard');
} else {
    // Jika belum, arahkan ke halaman login
    redirect('login');
}
?>