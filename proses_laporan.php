<?php
require_once 'config.php';

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index');
}

// 1. Validasi CAPTCHA
if (!isset($_POST['captcha']) || (int)$_POST['captcha'] !== $_SESSION['captcha']) {
    $_SESSION['error_message'] = "Verifikasi CAPTCHA salah. Silakan coba lagi.";
    redirect('index');
}
// Hapus session captcha setelah digunakan
unset($_SESSION['captcha']);


// 2. Ambil data dari form dan lakukan sanitasi dasar
$kategori = trim($_POST['kategori'] ?? '');
$judul_laporan = trim($_POST['judul_laporan'] ?? '');
$isi_laporan = trim($_POST['isi_laporan'] ?? '');
$is_anonim = isset($_POST['is_anonim']) ? 1 : 0;
$nama_pelapor = $is_anonim ? null : trim($_POST['nama_pelapor'] ?? '');
$email_pelapor = $is_anonim ? null : trim($_POST['email_pelapor'] ?? '');
$nomor_hp = $is_anonim ? null : trim($_POST['nomor_hp'] ?? '');


// 3. Validasi input wajib
if (empty($kategori) || empty($judul_laporan) || empty($isi_laporan)) {
    $_SESSION['error_message'] = "Kategori, Judul, dan Uraian Pelanggaran wajib diisi.";
    redirect('index');
}
if (!$is_anonim && (empty($nama_pelapor) || empty($email_pelapor) || empty($nomor_hp))) {
     $_SESSION['error_message'] = "Nama, Email, dan Nomor HP wajib diisi jika laporan tidak anonim.";
    redirect('index');
}
// Validasi email jika tidak anonim
if (!$is_anonim && !filter_var($email_pelapor, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Format email tidak valid.";
    redirect('index');
}


// 4. Proses Upload File Bukti
$bukti_pendukung_path = null;
$tracking_code = 'WB' . time() . rand(100, 999); // Generate tracking code unik

if (isset($_FILES['bukti_pendukung']) && $_FILES['bukti_pendukung']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['bukti_pendukung'];
    $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error_message'] = "Format file bukti tidak diizinkan. Gunakan: " . implode(', ', $allowed_extensions);
        redirect('index');
    }

    if ($file['size'] > $max_file_size) {
        $_SESSION['error_message'] = "Ukuran file bukti tidak boleh lebih dari 5MB.";
        redirect('index');
    }
    
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $new_filename = $tracking_code . '.' . $file_extension;
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Simpan path relatif yang akan digunakan di web
        $bukti_pendukung_path = $destination;
    } else {
        $_SESSION['error_message'] = "Gagal mengunggah file bukti. Pastikan folder 'uploads' dapat ditulis (writable).";
        redirect('index');
    }
}


// 5. Simpan ke Database
try {
    $sql = "INSERT INTO laporan (
                tracking_code, kategori, judul_laporan, isi_laporan, 
                bukti_pendukung, is_anonim, nama_pelapor, email_pelapor, 
                nomor_hp, status
            ) VALUES (
                :tracking_code, :kategori, :judul_laporan, :isi_laporan, 
                :bukti_pendukung, :is_anonim, :nama_pelapor, :email_pelapor, 
                :nomor_hp, 'Laporan-Diterima'
            )";
    
    $stmt = $pdo->prepare($sql);
    
    // Binding semua parameter ke statement SQL
    $stmt->bindParam(':tracking_code', $tracking_code, PDO::PARAM_STR);
    $stmt->bindParam(':kategori', $kategori, PDO::PARAM_STR);
    $stmt->bindParam(':judul_laporan', $judul_laporan, PDO::PARAM_STR);
    $stmt->bindParam(':isi_laporan', $isi_laporan, PDO::PARAM_STR);
    $stmt->bindParam(':bukti_pendukung', $bukti_pendukung_path, PDO::PARAM_STR);
    $stmt->bindParam(':is_anonim', $is_anonim, PDO::PARAM_INT);
    $stmt->bindParam(':nama_pelapor', $nama_pelapor, PDO::PARAM_STR);
    $stmt->bindParam(':email_pelapor', $email_pelapor, PDO::PARAM_STR);
    $stmt->bindParam(':nomor_hp', $nomor_hp, PDO::PARAM_STR);

    $stmt->execute();
    
    // 6. Beri notifikasi sukses ke pengguna
    $_SESSION['success_message'] = "Laporan berhasil dikirim. Nomor tracking Anda: <strong>" . htmlspecialchars($tracking_code) . "</strong>. Harap simpan nomor ini untuk melacak status laporan Anda.";
    redirect('index');

} catch (PDOException $e) {
    // Tangani error jika gagal menyimpan ke database
    
    // Hapus file yang sudah terlanjur di-upload jika query gagal, untuk mencegah file sampah
    if ($bukti_pendukung_path && file_exists($bukti_pendukung_path)) {
        unlink($bukti_pendukung_path);
    }
    
    // Catat error sebenarnya di log server untuk debugging oleh admin/developer
    error_log("Gagal menyimpan laporan WBS: " . $e->getMessage());
    
    // Tampilkan pesan error yang ramah ke pengguna
    $_SESSION['error_message'] = "Terjadi kesalahan pada sistem saat menyimpan laporan. Silakan coba beberapa saat lagi.";
    redirect('index');
}
?>