<?php
// Dapatkan nama file saat ini untuk menandai menu aktif
$current_page = basename($_SERVER['PHP_SELF'], ".php");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - WBS Kemenag Depok' : 'WBS Kemenag Kota Depok'; ?></title>
    
    <link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>assets/css/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            
            <div class="site-branding">
                <div class="site-logo">
                    <a href="index">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Kementerian_Agama_new_logo.png/150px-Kementerian_Agama_new_logo.png" alt="Logo Kemenag">
                    </a>
                </div>
                <div class="site-title-wrap">
                    <p class="site-title">Whistleblowing System</p>
                    <p class="site-description">Kantor Kemenag Kota Depok</p>
                </div>
            </div>

            <nav class="main-navigation">
                <ul>
                    <li><a href="index" class="<?php echo ($current_page == 'index') ? 'current' : ''; ?>">Beranda</a></li>
                    <li><a href="lacak" class="<?php echo ($current_page == 'lacak') ? 'current' : ''; ?>">Lacak Laporan</a></li>
                    <li><a href="statistik" class="<?php echo ($current_page == 'statistik') ? 'current' : ''; ?>">Statistik</a></li>
                    <li><a href="buat-laporan" class="btn">Buat Laporan</a></li>
                </ul>
            </nav>
            <button class="menu-toggle" id="menu-toggle" aria-label="Buka menu">&#9776;</button>
        </div>
    </header>