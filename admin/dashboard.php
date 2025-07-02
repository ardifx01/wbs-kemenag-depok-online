<?php
require_once '../config.php';
check_admin_login();

// Logika Paginasi dan Jumlah Baris
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$per_page = in_array($per_page, [10, 20, 50, 100]) ? $per_page : 20;
$offset = ($page - 1) * $per_page;

// Statistik
$total_laporan_keseluruhan = (int) $pdo->query("SELECT COUNT(*) FROM laporan")->fetchColumn();
$laporan_diterima = (int) $pdo->query("SELECT COUNT(*) FROM laporan WHERE status = 'Laporan Diterima'")->fetchColumn();
$laporan_klarifikasi = (int) $pdo->query("SELECT COUNT(*) FROM laporan WHERE status = 'Klarifikasi Awal'")->fetchColumn();
$laporan_investigasi = (int) $pdo->query("SELECT COUNT(*) FROM laporan WHERE status = 'Investigasi'")->fetchColumn();
$laporan_selesai = (int) $pdo->query("SELECT COUNT(*) FROM laporan WHERE status LIKE 'Selesai%'")->fetchColumn();

// Data untuk chart
$stmt_kategori = $pdo->query("SELECT kategori, COUNT(*) as jumlah FROM laporan GROUP BY kategori");
$data_kategori = $stmt_kategori->fetchAll(PDO::FETCH_KEY_PAIR);

// Logika Filter Tanggal
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$where_clauses = [];
$params = [];
if (!empty($start_date)) {
    $where_clauses[] = "created_at >= ?";
    $params[] = $start_date . ' 00:00:00';
}
if (!empty($end_date)) {
    $where_clauses[] = "created_at <= ?";
    $params[] = $end_date . ' 23:59:59';
}
$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(' AND ', $where_clauses);
}

// Query total laporan DENGAN filter
$sql_total_filtered = "SELECT COUNT(*) FROM laporan" . $where_sql;
$stmt_total = $pdo->prepare($sql_total_filtered);
$stmt_total->execute($params);
$total_laporan_filtered = $stmt_total->fetchColumn();
$total_pages = ceil($total_laporan_filtered / $per_page);

// Query utama
$sql_laporan = "SELECT id, tracking_code, kategori, judul_laporan, status, created_at FROM laporan" . $where_sql;
$sql_laporan .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt_laporan = $pdo->prepare($sql_laporan);
foreach ($params as $key => $value) {
    $stmt_laporan->bindValue($key + 1, $value);
}
$stmt_laporan->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt_laporan->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_laporan->execute();
$laporan_list = $stmt_laporan->fetchAll();

// Query string untuk link paginasi
$query_params = http_build_query([ 'start_date' => $start_date, 'end_date' => $end_date, 'per_page' => $per_page ]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengelola WBS</title>
    <style>
        /* --- CSS Admin Dimasukkan Langsung --- */
        :root {
            --primary-color: #D32F2F;
            --secondary-color: #005662;
            --border-color: #e0e0e0;
        }
        body { background-color: #f0f2f5; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #333; margin: 0; padding: 0;}
        .container-admin { max-width: 1400px; margin: 20px auto; padding: 20px; }
        .header-admin { display: flex; justify-content: space-between; align-items: center; background-color: #fff; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .header-admin h1 { margin: 0; font-size: 1.8em; }
        .header-admin a { color: var(--primary-color); text-decoration: none; }
        hr { border: 0; height: 1px; background-color: #e9ecef; margin: 20px 0; }

        .stats-grid, .filter-controls, .chart-container-admin, table, .table-controls, .table-actions {
            background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;
        }

        .chart-container-admin { height: 400px; }
        .chart-container-admin h3 { margin-top:0; text-align: center; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; }
        .stat-card { text-align: left; padding: 20px; border: 1px solid #e9ecef; border-radius: 8px; }
        .stat-card h3 { font-size: 2.2em; }
        .stat-card p { font-size: 1em; color: #666; }
        .stat-card.status-diterima { border-top: 4px solid #ffc107; }
        .stat-card.status-klarifikasi { border-top: 4px solid #17a2b8; }
        .stat-card.status-investigasi { border-top: 4px solid #007bff; }
        .stat-card.status-selesai { border-top: 4px solid #28a745; }

        .filter-controls form { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .button-filter { background-color: var(--secondary-color); color: #fff; border:0; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .button-reset { background-color: #6c757d; color: #fff; text-decoration: none; padding: 8px 15px; border-radius: 5px; }

        .table-actions { padding: 0; background: none; box-shadow: none; }
        .button-export { background-color: #1D6F42; color: #fff; text-decoration: none; padding: 10px 15px; border-radius: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 0; padding: 0; box-shadow: none; }
        th, td { padding: 12px; border-bottom: 1px solid #e9ecef; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        tbody tr:hover { background-color: #f8f9fa; }
        td a.action-link { color: var(--primary-color); font-weight: bold; text-decoration: none; }

        .status-badge { padding: 5px 10px; border-radius: 15px; color: white; font-size: 0.8em; font-weight: bold; display: inline-block; }
        .status-laporan-diterima { background-color: #ffc107; color: #333;}
        .status-klarifikasi-awal { background-color: #17a2b8;}
        .status-investigasi { background-color: #007bff;}
        .status-selesai---terbukti, .status-selesai---tidak-terbukti { background-color: #28a745;}
        .status-tidak-dapat-ditindaklanjuti { background-color: #6c757d; }

        .table-controls { display: flex; justify-content: space-between; align-items: center; }
        .pagination { display: flex; list-style: none; padding: 0; margin: 0; }
        .pagination li a { color: var(--secondary-color); padding: 8px 12px; text-decoration: none; border: 1px solid #ddd; margin: 0 2px; border-radius: 4px; }
        .pagination li.active a { background-color: var(--secondary-color); color: white; border-color: var(--secondary-color); }
        .pagination li.disabled a { color: #ccc; pointer-events: none; }
    </style>
</head>
<body>
    <div class="container-admin">
        <div class="header-admin">
            <h1>Dashboard Pengelola WBS</h1>
            <div>
                Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['admin_nama']); ?></strong> | <a href="logout">Logout</a>
            </div>
        </div>
        
        <h3>Statistik Laporan</h3>
        <div class="stats-grid">
            <div class="stat-card"><h3><?php echo $total_laporan_keseluruhan; ?></h3><p>Total Laporan</p></div>
            <div class="stat-card status-diterima"><h3><?php echo $laporan_diterima; ?></h3><p>Laporan Diterima</p></div>
            <div class="stat-card status-klarifikasi"><h3><?php echo $laporan_klarifikasi; ?></h3><p>Klarifikasi Awal</p></div>
            <div class="stat-card status-investigasi"><h3><?php echo $laporan_investigasi; ?></h3><p>Investigasi</p></div>
            <div class="stat-card status-selesai"><h3><?php echo $laporan_selesai; ?></h3><p>Selesai</p></div>
        </div>

        <div class="chart-container-admin">
            <h3>Distribusi Laporan Berdasarkan Kategori</h3>
            <canvas id="kategoriChart"></canvas>
        </div>
        
        <h3>Daftar Laporan</h3>
        <div class="filter-controls">
            <form method="GET" action="dashboard.php">
                <label for="start_date">Dari Tanggal:</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                <label for="end_date">Sampai Tanggal:</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                <button type="submit" class="button-filter">Filter</button>
                <a href="dashboard.php" class="button-reset">Reset</a>
            </form>
        </div>

        <div class="table-actions">
            <a href="export_excel.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="button-export">Ekspor ke Excel</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No. Tracking</th>
                    <th>Kategori</th>
                    <th>Judul Laporan</th>
                    <th>Status</th>
                    <th>Tanggal Masuk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan_list)): ?>
                    <tr><td colspan="6" style="text-align: center;">Tidak ada laporan yang ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($laporan_list as $laporan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($laporan['tracking_code']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['kategori']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['judul_laporan']); ?></td>
                        <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $laporan['status'])); ?>"><?php echo htmlspecialchars($laporan['status']); ?></span></td>
                        <td><?php echo format_tanggal_indonesia($laporan['created_at']); ?></td>
                        <td><a href="detail_laporan.php?id=<?php echo $laporan['id']; ?>" class="action-link">Lihat Detail</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="table-controls">
            <div class="rows-per-page-form">
                <form method="GET" action="dashboard.php">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    <label for="per_page">Baris per halaman:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()">
                        <option value="10" <?php if ($per_page == 10) echo 'selected'; ?>>10</option>
                        <option value="20" <?php if ($per_page == 20) echo 'selected'; ?>>20</option>
                        <option value="50" <?php if ($per_page == 50) echo 'selected'; ?>>50</option>
                        <option value="100" <?php if ($per_page == 100) echo 'selected'; ?>>100</option>
                    </select>
                </form>
            </div>
            <?php if ($total_pages > 1): ?>
            <ul class="pagination">
                <li class="<?php echo ($page <= 1) ? 'disabled' : ''; ?>"><a href="?page=<?php echo $page - 1; ?>&<?php echo $query_params; ?>">« Prev</a></li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="<?php echo ($page == $i) ? 'active' : ''; ?>"><a href="?page=<?php echo $i; ?>&<?php echo $query_params; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <li class="<?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"><a href="?page=<?php echo $page + 1; ?>&<?php echo $query_params; ?>">Next »</a></li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dataKategori = <?php echo json_encode($data_kategori); ?>;
        const ctxKategori = document.getElementById('kategoriChart');
        if (ctxKategori && Object.keys(dataKategori).length > 0) {
            new Chart(ctxKategori, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(dataKategori),
                    datasets: [{
                        label: 'Jumlah Laporan',
                        data: Object.values(dataKategori),
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    </script>
</body>
</html>