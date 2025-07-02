<?php
// Memuat file konfigurasi utama
require_once 'config.php';

// Inisialisasi variabel
$laporan = null;
$tindak_lanjut_list = [];
$error_message = '';
$tracking_code_input = '';

// Memproses jika ada permintaan pelacakan
if (isset($_GET['tracking_code']) && !empty($_GET['tracking_code'])) {
    $tracking_code_input = trim($_GET['tracking_code']);
    
    // Ambil data laporan dari database
    $sql_laporan = "SELECT * FROM laporan WHERE tracking_code = ?";
    $stmt_laporan = $pdo->prepare($sql_laporan);
    $stmt_laporan->execute([$tracking_code_input]);
    $laporan = $stmt_laporan->fetch();

    if ($laporan) {
        // Jika laporan ditemukan, ambil riwayat tindak lanjutnya
        $sql_tindak_lanjut = "SELECT tl.*, a.nama as nama_admin 
                             FROM tindak_lanjut tl
                             JOIN admin a ON tl.admin_id = a.id
                             WHERE tl.laporan_id = ? 
                             ORDER BY tl.timestamp ASC";
        $stmt_tindak_lanjut = $pdo->prepare($sql_tindak_lanjut);
        $stmt_tindak_lanjut->execute([$laporan['id']]);
        $tindak_lanjut_list = $stmt_tindak_lanjut->fetchAll();
    } else {
        $error_message = "Nomor tracking tidak ditemukan. Pastikan Anda memasukkan nomor yang benar.";
    }
}

// Menetapkan judul halaman untuk digunakan di header.php
$page_title = 'Lacak Status Laporan';
// Memuat header halaman (Jika Anda menggunakan struktur template)
// Jika Anda menggunakan metode All-in-One di halaman lain, Anda bisa meniru struktur tersebut di sini.
// Untuk sekarang, kita asumsikan Anda menggunakan header.php dan footer.php
require_once 'templates/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1>Lacak Status Laporan</h1>
        <p>Masukkan nomor tracking yang Anda dapatkan saat mengirim laporan untuk melihat progresnya.</p>
    </div>
</header>

<main class="page-content">
    <div class="container" style="max-width: 800px;">
        
        <form method="GET" action="lacak" class="lacak-form">
            <div class="form-group" style="flex-grow: 1;">
                <label for="tracking_code" class="sr-only">Nomor Tracking</label>
                <input type="text" id="tracking_code" name="tracking_code" placeholder="Masukkan nomor tracking Anda di sini..." value="<?php echo htmlspecialchars($tracking_code_input); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Lacak</button>
        </form>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" style="margin-top: 20px;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($laporan): ?>
        <div class="laporan-detail" style="margin-top: 40px; border-top: 1px solid var(--border-color); padding-top: 40px;">
            
            <h3>Detail Laporan Anda</h3>
            <table class="detail-table">
                <tr>
                    <th>Nomor Tracking</th>
                    <td><?php echo htmlspecialchars($laporan['tracking_code']); ?></td>
                </tr>
                <tr>
                    <th>Judul Laporan</th>
                    <td><?php echo htmlspecialchars($laporan['judul_laporan']); ?></td>
                </tr>
                <tr>
                    <th>Tanggal Masuk</th>
                    <td><?php echo format_tanggal_indonesia($laporan['created_at']); ?></td>
                </tr>
                <tr>
                    <th>Status Terkini</th>
                    <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $laporan['status'])); ?>"><?php echo htmlspecialchars($laporan['status']); ?></span></td>
                </tr>
            </table>

            <h4 style="margin-top: 40px; margin-bottom: 20px;">Riwayat Penanganan Laporan</h4>
            <?php if (empty($tindak_lanjut_list)): ?>
                <p>Belum ada riwayat penanganan atau komentar dari Pengelola WBS.</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($tindak_lanjut_list as $tindak_lanjut): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <span class="timeline-date"><?php echo format_tanggal_indonesia($tindak_lanjut['timestamp']); ?></span>
                            <p><?php echo nl2br(htmlspecialchars($tindak_lanjut['komentar'])); ?></p>
                            <?php if ($tindak_lanjut['file_tambahan']): ?>
                                <p class="attachment-link"><a href="<?php echo htmlspecialchars($tindak_lanjut['file_tambahan']); ?>" target="_blank">Lihat Lampiran dari Pengelola</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</main>
<br>
<?php 
require_once 'templates/footer.php'; 
?>