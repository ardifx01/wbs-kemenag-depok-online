<?php
require_once '../config.php';

if (isset($_SESSION['admin_id'])) {
    // Log aktivitas sebelum session dihancurkan
    log_activity($pdo, $_SESSION['admin_id'], 'Admin berhasil logout.');
}

// Hancurkan semua data session
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Alihkan ke halaman login
redirect('login');
?>