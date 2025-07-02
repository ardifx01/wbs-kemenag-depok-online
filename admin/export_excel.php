<?php
require_once '../config.php';
check_admin_login();

// Logika Filter Tanggal (disalin dari dashboard)
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

// DIUBAH: Menambahkan 'isi_laporan' ke dalam query SELECT
$sql_laporan = "SELECT id, tracking_code, kategori, judul_laporan, isi_laporan, status, is_anonim, nama_pelapor, email_pelapor, nomor_hp, created_at FROM laporan";
if (!empty($where_clauses)) {
    $sql_laporan .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql_laporan .= " ORDER BY created_at DESC";

$stmt_laporan = $pdo->prepare($sql_laporan);
$stmt_laporan->execute($params);
$laporan_list = $stmt_laporan->fetchAll();

// Log aktivitas
log_activity($pdo, $_SESSION['admin_id'], 'Mengekspor data laporan ke Excel.');

// Nama file
$filename = "Laporan_WBS_Kemenag_Depok_" . date('Y-m-d') . ".xls";

// Header untuk memicu download file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Mulai membuat konten Excel
$output = "
<html xmlns:x='urn:schemas-microsoft-com:office:excel'>
<head>
    <meta http-equiv='Content-type' content='text/html;charset=utf-8' />
    <style>
        /* Menambahkan style dasar untuk pembacaan yang lebih baik di Excel */
        th, td { border: 1px solid #ccc; padding: 5px; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h3>DAFTAR LAPORAN WHISTLEBLOWING SYSTEM</h3>
    <p>Kantor Kementerian Agama Kota Depok</p>
    <p>Tanggal Ekspor: ". format_tanggal_indonesia(date('Y-m-d H:i:s')) ."</p>
    <br>
    <table border='1'>
        <thead>
            <tr>
                <th>No. Tracking</th>
                <th>Tanggal Masuk</th>
                <th>Kategori</th>
                <th>Judul Laporan</th>
                <th>Uraian Pelanggaran</th> <th>Status</th>
                <th>Jenis Pelapor</th>
                <th>Nama Pelapor</th>
                <th>Email Pelapor</th>
                <th>No. HP Pelapor</th>
            </tr>
        </thead>
        <tbody>
";

if (empty($laporan_list)) {
    // DIUBAH: colspan disesuaikan menjadi 10
    $output .= "<tr><td colspan='10'>Tidak ada data untuk diekspor.</td></tr>";
} else {
    foreach ($laporan_list as $laporan) {
        $output .= "
            <tr>
                <td>" . htmlspecialchars($laporan['tracking_code']) . "</td>
                <td>" . date('Y-m-d H:i:s', strtotime($laporan['created_at'])) . "</td>
                <td>" . htmlspecialchars($laporan['kategori']) . "</td>
                <td>" . htmlspecialchars($laporan['judul_laporan']) . "</td>
                <td>" . htmlspecialchars($laporan['isi_laporan']) . "</td> <td>" . htmlspecialchars($laporan['status']) . "</td>
                <td>" . ($laporan['is_anonim'] ? 'Anonim' : 'Identitas Diketahui') . "</td>
                <td>" . ($laporan['is_anonim'] ? '-' : htmlspecialchars($laporan['nama_pelapor'])) . "</td>
                <td>" . ($laporan['is_anonim'] ? '-' : htmlspecialchars($laporan['email_pelapor'])) . "</td>
                <td>" . ($laporan['is_anonim'] ? '-' : htmlspecialchars($laporan['nomor_hp'])) . "</td>
            </tr>
        ";
    }
}

$output .= "
        </tbody>
    </table>
</body>
</html>
";

// Mengirim output ke browser
echo $output;
exit;
?>