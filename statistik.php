<?php
// Memuat file konfigurasi utama
require_once 'config.php';

// Inisialisasi semua variabel untuk mencegah error
$total_laporan = 0;
$laporan_diproses = 0;
$laporan_tuntas = 0;
$stats_kategori = [];
$stats_status = [];
$stats_bulanan = [];

try {
    // 1. Data untuk Kartu Statistik Utama
    $total_laporan = (int) $pdo->query("SELECT COUNT(*) FROM laporan")->fetchColumn();
    $laporan_diproses = (int) $pdo->query("SELECT COUNT(*) FROM laporan WHERE status NOT LIKE 'Selesai%' AND status != 'Tidak Dapat Ditindaklanjuti'")->fetchColumn();
    $laporan_tuntas = (int) $pdo->query("SELECT COUNT(*) FROM laporan WHERE status LIKE 'Selesai%' OR status = 'Tidak Dapat Ditindaklanjuti'")->fetchColumn();

    // 2. Data untuk Tabel Statistik Kategori
    $stats_kategori = $pdo->query("SELECT kategori, COUNT(*) as jumlah FROM laporan GROUP BY kategori ORDER BY jumlah DESC")->fetchAll();

    // 3. Data untuk Tabel Statistik Status
    $stats_status = $pdo->query("SELECT status, COUNT(*) as jumlah FROM laporan GROUP BY status ORDER BY FIELD(status, 'Laporan Diterima', 'Klarifikasi Awal', 'Investigasi', 'Selesai - Terbukti', 'Selesai - Tidak Terbukti', 'Tidak Dapat Ditindaklanjuti')")->fetchAll();

    // 4. Data untuk Tabel Statistik Bulanan
    $stats_bulanan = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as jumlah 
        FROM laporan 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY bulan 
        ORDER BY bulan DESC
    ")->fetchAll();

} catch (PDOException $e) {
    error_log("Error di statistik.php: " . $e->getMessage());
    die("<h1>Terjadi Kesalahan</h1><p>Tidak dapat memuat data statistik. Silakan hubungi administrator.</p>");
}

// Menetapkan judul halaman untuk digunakan di header.php
$page_title = 'Statistik Laporan';

// Memuat header halaman
require_once 'templates/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1>Statistik Publik</h1>
        <p>Data statistik umum dari laporan yang masuk sebagai wujud transparansi.</p>
    </div>
</header>

<main class="page-content">
    <div class="container">

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_laporan; ?></h3>
                <p>Total Laporan Diterima</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $laporan_diproses; ?></h3>
                <p>Dalam Proses</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $laporan_tuntas; ?></h3>
                <p>Laporan Tuntas</p>
            </div>
        </div>

        <hr>

        <div class="stats-table-wrapper">
            <div class="stats-table-container">
                <h3>Berdasarkan Kategori</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Kategori Pelanggaran</th>
                            <th style="text-align: center;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats_kategori)): ?>
                            <tr><td colspan="2" style="text-align: center;">Belum ada data.</td></tr>
                        <?php else: ?>
                            <?php foreach ($stats_kategori as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['kategori']); ?></td>
                                <td style="text-align: center;"><?php echo $item['jumlah']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="stats-table-container">
                <h3>Berdasarkan Status</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Status Laporan</th>
                            <th style="text-align: center;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if (empty($stats_status)): ?>
                            <tr><td colspan="2" style="text-align: center;">Belum ada data.</td></tr>
                        <?php else: ?>
                            <?php foreach ($stats_status as $item): ?>
                            <tr>
                                <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $item['status'])); ?>"><?php echo htmlspecialchars($item['status']); ?></span></td>
                                <td style="text-align: center;"><?php echo $item['jumlah']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="stats-table-container">
                <h3>Laporan per Bulan <small style="font-weight:normal;color:#999;">(12 Bulan Terakhir)</small></h3>
                <table>
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th style="text-align: center;">Jumlah Laporan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats_bulanan)): ?>
                            <tr><td colspan="2" style="text-align: center;">Belum ada data.</td></tr>
                        <?php else: ?>
                            <?php 
                            $bulan_array_indonesia = [
                                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                            ];
                            ?>
                            <?php foreach ($stats_bulanan as $item): ?>
                            <tr>
                                <td>
                                    <?php 
                                        $dateObj = DateTime::createFromFormat('!Y-m', $item['bulan']);
                                        if ($dateObj) {
                                            $bulan_indonesia = $bulan_array_indonesia[$dateObj->format('n')];
                                            $tahun = $dateObj->format('Y');
                                            echo $bulan_indonesia . ' ' . $tahun;
                                        } else {
                                            echo htmlspecialchars($item['bulan']);
                                        }
                                    ?>
                                </td>
                                <td style="text-align: center;"><?php echo $item['jumlah']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>
<br><br>
<?php 
// Memuat footer halaman
require_once 'templates/footer.php'; 
?>