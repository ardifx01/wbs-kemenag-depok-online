<?php
require_once '../config.php';

// Jika sudah login, alihkan ke dashboard
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong.';
    } else {
        $sql = "SELECT id, nama, username, password FROM admin WHERE username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Password benar, mulai session
            $_SESSION['admin_loggedin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];

            // Log aktivitas login
            log_activity($pdo, $admin['id'], 'Pengelola WBS berhasil login.');
            
            header("Location: dashboard.php");
            exit;
        } else {
            // Password atau username salah
            $error_message = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Pengelola WBS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* CSS Lengkap untuk Halaman Login Dimasukkan Langsung */
        :root {
            --primary-color: #D32F2F;
            --secondary-color: #005662;
            --border-color: #E2E8F0;
            --text-color: #2D3748;
            --text-color-light: #718096;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body.login-page-body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f7f6;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 20px;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-logo img {
            height: 60px;
            margin-bottom: 20px;
        }
        .login-container h1 {
            font-size: 1.8em;
            margin-bottom: 5px;
            color: var(--secondary-color);
        }
        .login-container .login-subtitle {
            color: var(--text-color-light);
            margin-bottom: 25px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid transparent;
            text-align: left;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1em;
            background-color: #f8f9fa;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(0, 86, 98, 0.2);
        }
        .form-group button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: var(--secondary-color);
            color: white;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        .form-group button:hover {
            background-color: #00434d;
        }
    </style>
</head>
<body class="login-page-body">

    <div class="login-container">
        <div class="login-logo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Kementerian_Agama_new_logo.png/150px-Kementerian_Agama_new_logo.png" alt="Logo Kemenag">
        </div>

        <h1>Login Pengelola WBS</h1>
        <p class="login-subtitle">Kantor Kementerian Agama Kota Depok</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>
    </div>

</body>
</html>