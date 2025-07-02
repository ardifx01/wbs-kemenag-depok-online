<?php
// Memuat file konfigurasi dan memulai session
require_once 'config.php';

// Menetapkan judul halaman
$page_title = 'Buat Laporan Pelanggaran';

// Memuat header halaman
require_once 'templates/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1>Formulir Pelaporan Pelanggaran</h1>
        <p>Gunakan formulir di bawah ini untuk melaporkan dugaan pelanggaran secara aman dan rahasia.</p>
    </div>
</header>

<main class="page-content">
    <div class="container" style="max-width: 800px;">
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_laporan.php" method="POST" enctype="multipart/form-data" id="laporanForm">

            <div class="disclaimer">Seluruh pegawai di lingkungan Kantor Kementerian Agama Kota Depok memiliki kewajiban moral untuk melaporkan terjadinya pelanggaran apabila mengetahuinya. Kesadaran perlunya menyampaikan adanya pelanggaran demi kepentingan dan kemaslahatan bersama serta manfaat untuk mencegah dampak yang tidak diinginkan menyebar luas, seperti misalnya kebiasaan penerimaan atau pemberian gratifikasi.</div>
            <br>

            <h3>Detail Laporan</h3>
            <div class="form-group">
                <label for="kategori">Kategori Pelanggaran <span class="required">*</span></label>
                <select id="kategori" name="kategori" required>
                    <option value="">- Pilih Kategori Pelanggaran -</option>
                    <option value="Korupsi">Korupsi</option>
                    <option value="Kecurangan">Kecurangan</option>
                    <option value="Ketidakjujuran">Ketidakjujuran</option>
                    <option value="Perbuatan Melanggar Hukum">Perbuatan Melanggar Hukum</option>
                    <option value="Pelanggaran Ketentuan Perpajakan, atau Peraturan Perundang-Undangan Lainnya">Pelanggaran Ketentuan Perpajakan, atau Peraturan Perundang-Undangan Lainnya</option>
                    <option value="Pelanggaran Pedoman/Kode Etik atau Pelanggaran Norma-Norma Kesopanan pada Umumnya">Pelanggaran Pedoman/Kode Etik atau Pelanggaran Norma-Norma Kesopanan pada Umumnya</option>
                    <option value="Perbuatan yang Membahayakan Keselamatan dan Kesehatan Kerja, atau Membahayakan Keselamatan Satuan Kerja/Pegawai">Perbuatan yang Membahayakan Keselamatan dan Kesehatan Kerja, atau Membahayakan Keselamatan Satuan Kerja/Pegawai</option>
                    <option value="Perbuatan yang dapat Menimbulkan Kerugian Finansial atau Nonfinasial terhadap Satuan Kerja/Negara atau Merugikan Kepentingan Negara">Perbuatan yang dapat Menimbulkan Kerugian Finansial atau Nonfinasial terhadap Satuan Kerja/Negara atau Merugikan Kepentingan Negara</option>
                    <option value="Pelanggaran Standar Operasional Prosedur (SOP) Satuan Kerja, terutama terkait dengan Pengadaan Barang dan Jasa, Pemberian Manfaat dan Remunerasi">Pelanggaran Standar Operasional Prosedur (SOP) Satuan Kerja, terutama terkait dengan Pengadaan Barang dan Jasa, Pemberian Manfaat dan Remunerasi</option>
                </select>
            </div>
            <div class="form-group">
                <label for="judul_laporan">Judul Laporan <span class="required">*</span></label>
                <input type="text" id="judul_laporan" name="judul_laporan" required>
            </div>
            <div class="form-group">
                <label for="isi_laporan">Uraian Pelanggaran <span class="required">*</span></label>
                <textarea id="isi_laporan" name="isi_laporan" rows="10" required></textarea>
                <small>Tips: Jelaskan permasalahan, siapa yang terlibat, kapan, dan di mana terjadinya.</small>
            </div>
            <div class="form-group">
                <label for="bukti_pendukung">Bukti Pendukung</label>
                <input type="file" id="bukti_pendukung" name="bukti_pendukung">
                <small>Format: PDF, JPG, PNG, DOC, DOCX. Maksimal 5MB.</small>
            </div>
            <hr>
            <h3>Identitas Pelapor</h3>
            <div class="form-group-checkbox">
                <input type="checkbox" id="is_anonim" name="is_anonim" value="1" onchange="togglePelaporForm()">
                <label for="is_anonim">Kirim secara anonim</label>
            </div>
            <div id="identitas-pelapor">
                <div class="form-group"><label for="nama_pelapor">Nama Lengkap <span class="required">*</span></label><input type="text" id="nama_pelapor" name="nama_pelapor"></div>
                <div class="form-group"><label for="email_pelapor">Email <span class="required">*</span></label><input type="email" id="email_pelapor" name="email_pelapor"></div>
                <div class="form-group"><label for="nomor_hp">Nomor HP <span class="required">*</span></label><input type="tel" id="nomor_hp" name="nomor_hp"></div>
            </div>
            <hr>
            <h3>Verifikasi</h3>
            <div class="form-group">
                <label for="captcha">Berapa hasil dari <?php $num1 = rand(1, 10); $num2 = rand(1, 10); $_SESSION['captcha'] = $num1 + $num2; echo "$num1 + $num2"; ?>? <span class="required">*</span></label>
                <input type="number" id="captcha" name="captcha" required>
            </div>
            <div class="form-group"><button type="submit" class="btn btn-primary" style="width:100%;">Kirim Laporan</button></div>
            <div class="disclaimer"><strong>Perhatian:</strong> Pelapor yang mengirimkan laporan yang berupa fitnah atau laporan palsu akan memperoleh sanksi dan tidak memperoleh baik jaminan kerahasiaan maupun perlindungan pelapor. Sanksi yang dikenakan sesuai peraturan yang berlaku misalnya KUHP Pasal 310 dan 311 yang terkait dengan perbuatan tidak menyenangkan atau pencemaran nama baik.</div>
        </form>
    </div>
</main>

<script>
    function togglePelaporForm() {
        const isAnonimCheckbox = document.getElementById('is_anonim');
        const identitasPelaporDiv = document.getElementById('identitas-pelapor');
        const requiredInputs = identitasPelaporDiv.querySelectorAll('input');
        if (isAnonimCheckbox.checked) {
            identitasPelaporDiv.style.display = 'none';
            requiredInputs.forEach(input => input.required = false);
        } else {
            identitasPelaporDiv.style.display = 'block';
            requiredInputs.forEach(input => input.required = true);
        }
    }
    document.addEventListener('DOMContentLoaded', togglePelaporForm);
</script>

<?php 
require_once 'templates/footer.php'; 
?>