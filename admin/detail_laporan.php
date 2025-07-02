<?php
require_once '../config.php';
check_admin_login();

// Ambil ID laporan dari URL
$laporan_id = $_GET['id'] ?? 0;
if (!$laporan_id || !filter_var($laporan_id, FILTER_VALIDATE_INT)) {
    // Jika tidak ada ID atau ID tidak valid, kembali ke dashboard
    header("Location: dashboard.php");
    exit;
}

// Daftar status yang valid
$daftar_status = [
    'Laporan Diterima', 'Klarifikasi Awal', 'Investigasi', 
    'Tidak Dapat Ditindaklanjuti', 'Selesai - Terbukti', 'Selesai - Tidak Terbukti'
];

// Proses form saat admin mengirim tindak lanjut atau update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses Update Status
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        if (in_array($new_status, $daftar_status)) {
            $sql_update = "UPDATE laporan SET status = ? WHERE id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$new_status, $laporan_id]);
            $_SESSION['flash_message'] = "Status laporan berhasil diperbarui.";
        }
    }

    // Proses Tambah Komentar
    if (isset($_POST['tambah_komentar'])) {
        $komentar = trim($_POST['komentar']);
        if (!empty($komentar)) {
            $file_tambahan_path = null;
            if (isset($_FILES['file_tambahan']) && $_FILES['file_tambahan']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file_tambahan'];
                $upload_dir = '../uploads/tindak_lanjut/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $new_filename = $laporan_id . '_' . time() . '_' . basename($file['name']);
                $destination = $upload_dir . $new_filename;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $file_tambahan_path = 'uploads/tindak_lanjut/' . $new_filename;
                }
            }
            $sql_insert = "INSERT INTO tindak_lanjut (laporan_id, admin_id, komentar, file_tambahan) VALUES (?, ?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$laporan_id, $_SESSION['admin_id'], $komentar, $file_tambahan_path]);
            $_SESSION['flash_message'] = "Tindak lanjut berhasil ditambahkan.";
        }
    }
    // Redirect untuk mencegah resubmission form
    header("Location: detail_laporan.php?id={$laporan_id}");
    exit;
}

// Ambil data detail laporan
$stmt_laporan = $pdo->prepare("SELECT * FROM laporan WHERE id = ?");
$stmt_laporan->execute([$laporan_id]);
$laporan = $stmt_laporan->fetch();

if (!$laporan) {
    die("Laporan tidak ditemukan.");
}

// Ambil data riwayat tindak lanjut
$stmt_tindak_lanjut = $pdo->prepare("SELECT tl.*, a.nama as nama_admin FROM tindak_lanjut tl JOIN admin a ON tl.admin_id = a.id WHERE laporan_id = ? ORDER BY timestamp DESC");
$stmt_tindak_lanjut->execute([$laporan_id]);
$tindak_lanjut_list = $stmt_tindak_lanjut->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Laporan - <?php echo htmlspecialchars($laporan['tracking_code']); ?></title>
    <style>
        /* CSS Lengkap Dimasukkan Langsung di Sini */
        :root {
            --primary-color: #D32F2F;
            --secondary-color: #005662;
            --border-color: #e0e0e0;
        }
        body { background-color: #f0f2f5; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #333; margin: 0; padding: 0; line-height: 1.6; }
        .container-admin { max-width: 1400px; margin: 20px auto; padding: 20px; }
        .header-admin { display: flex; justify-content: space-between; align-items: center; background-color: #fff; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .header-admin h1 { margin: 0; font-size: 1.8em; }
        .header-admin a { color: var(--primary-color); text-decoration: none; font-weight: bold;}
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; border: 1px solid transparent; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        
        /* Tata Letak Detail Grid */
        .detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .detail-card { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .detail-card h3 { margin-top: 0; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }

        /* Tabel Detail */
        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table th, .detail-table td { padding: 12px 5px; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: top;}
        .detail-table tr:last-child th, .detail-table tr:last-child td { border-bottom: none; }
        .detail-table th { font-weight: bold; width: 180px; color: #555; }
        .detail-table td a { color: var(--primary-color); font-weight: bold; }

        /* Form */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group textarea, .form-group select, .form-group input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .button-small, button[type="submit"] { background-color: var(--secondary-color); color: #fff; border: 0; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button[type="submit"] { width: 100%; padding: 12px; }
        button[type="submit"]:hover { background-color: #00434d; }

        /* Timeline Riwayat */
        .timeline-admin { margin-top: 20px; }
        .timeline-item { position: relative; padding-left: 25px; border-left: 2px solid #e9ecef; margin-bottom: 20px; }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-dot { position: absolute; left: -9px; top: 5px; width: 15px; height: 15px; background-color: var(--secondary-color); border-radius: 50%; border: 2px solid #fff; }
        .timeline-content { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
        .timeline-content p { margin: 0; }
        .timeline-author { display: block; font-size: 0.9em; color: #333; margin-bottom: 5px; }
        .timeline-date { font-size: 0.8em; color: #6c757d; }

        @media (max-width: 992px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="admin-body">
<div class="container-admin">
    <div class="header-admin">
        <h1>Detail Laporan</h1>
        <a href="dashboard.php">&larr; Kembali ke Dashboard</a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></div>
    <?php endif; ?>

    <div class="detail-grid">
        <div class="detail-card">
            <h3>Informasi Laporan</h3>
            <table class="detail-table">
                <tr><th>No. Tracking</th><td><?php echo htmlspecialchars($laporan['tracking_code']); ?></td></tr>
                <tr><th>Tanggal Masuk</th><td><?php echo format_tanggal_indonesia($laporan['created_at']); ?></td></tr>
                <tr><th>Kategori</th><td><?php echo htmlspecialchars($laporan['kategori']); ?></td></tr>
                <tr><th>Status</th>
                    <td>
                        <form method="POST" action="detail_laporan.php?id=<?php echo $laporan_id; ?>" style="display:flex; gap:10px;">
                             <select name="status">
                                <?php foreach ($daftar_status as $status_item): ?>
                                    <option value="<?php echo $status_item; ?>" <?php if ($laporan['status'] == $status_item) echo 'selected'; ?>>
                                        <?php echo $status_item; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="button-small">Update</button>
                        </form>
                    </td>
                </tr>
                <tr><th>Judul Laporan</th><td><?php echo htmlspecialchars($laporan['judul_laporan']); ?></td></tr>
                <tr><th>Uraian Pelanggaran</th><td><?php echo nl2br(htmlspecialchars($laporan['isi_laporan'])); ?></td></tr>
                <tr><th>Bukti Pendukung</th>
                    <td>
                        <?php if ($laporan['bukti_pendukung'] && file_exists('../' . $laporan['bukti_pendukung'])): ?>
                            <a href="../<?php echo htmlspecialchars($laporan['bukti_pendukung']); ?>" target="_blank">Lihat Bukti</a>
                        <?php else: ?>
                            - Tidak ada -
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <h3 style="margin-top:30px;">Informasi Pelapor</h3>
             <table class="detail-table">
                <tr><th>Jenis Laporan</th><td><?php echo $laporan['is_anonim'] ? 'Anonim' : 'Identitas Diketahui'; ?></td></tr>
                <?php if (!$laporan['is_anonim']): ?>
                <tr><th>Nama Pelapor</th><td><?php echo htmlspecialchars($laporan['nama_pelapor']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($laporan['email_pelapor']); ?></td></tr>
                <tr><th>Nomor HP</th><td><?php echo htmlspecialchars($laporan['nomor_hp']); ?></td></tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="detail-card">
            <h3>Proses Penanganan Laporan</h3>
            <div class="form-tindak-lanjut">
                <form method="POST" action="detail_laporan.php?id=<?php echo $laporan_id; ?>" enctype="multipart/form-data">
                    <div class="form-group"><label for="komentar">Tambah Komentar / Hasil Penanganan</label><textarea name="komentar" id="komentar" rows="5" required></textarea></div>
                    <div class="form-group"><label for="file_tambahan">Upload Berita Acara / File Lainnya (Opsional)</label><input type="file" name="file_tambahan" id="file_tambahan"></div>
                    <button type="submit" name="tambah_komentar">Simpan Tindak Lanjut</button>
                </form>
            </div>

            <h3 style="margin-top: 30px;">Riwayat Penanganan</h3>
             <div class="timeline-admin">
                <?php if (empty($tindak_lanjut_list)): ?>
                    <p>Belum ada tindak lanjut yang dicatat.</p>
                <?php else: ?>
                    <?php foreach ($tindak_lanjut_list as $tindak_lanjut): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <span class="timeline-author">Oleh: <strong><?php echo htmlspecialchars($tindak_lanjut['nama_admin']); ?></strong></span>
                            <span class="timeline-date"><?php echo format_tanggal_indonesia($tindak_lanjut['timestamp']); ?></span>
                            <p><?php echo nl2br(htmlspecialchars($tindak_lanjut['komentar'])); ?></p>
                            <?php if ($tindak_lanjut['file_tambahan'] && file_exists('../' . $tindak_lanjut['file_tambahan'])): ?>
                                <p style="margin-top:10px;"><small><a href="../<?php echo htmlspecialchars($tindak_lanjut['file_tambahan']); ?>" target="_blank">Lihat Lampiran</a></small></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>