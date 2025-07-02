<?php 
session_start();
// Muat file konfigurasi jika diperlukan (misal untuk BASE_URL)
require_once 'config.php';
// Atur judul halaman
$page_title = 'WBS Kemenag Kota Depok - Beranda';
// Muat header
require_once 'templates/header.php'; 
?>

<main>
    <section class="hero">
    <div class="container hero-container">
        <div class="hero-text">
            <h1>Wujudkan Kemenag Kota Depok Bersih & Transparan</h1>
            <p>Melihat atau mengetahui adanya indikasi korupsi, kecurangan, atau pelanggaran lainnya? Jangan diam! Laporkan melalui sistem yang aman dan rahasia.</p>
            <a href="buat-laporan.php" class="hero-link">Buat Laporan Sekarang &rarr;</a>
        </div>
        <div class="hero-image">
            <img src="https://www.bprsdinarashri.co.id/uploads/topics/17114267072983.png" alt="Ilustrasi Whistleblowing System">
        </div>
    </div>
    </section>
    <section class="section section-light">
        <div class="container">
            <h2 class="section-title">Layanan Kami</h2>
            <div class="grid grid-4-col">
                <div class="info-card">
                    <div class="icon-wrapper"><i class="fas fa-shield-alt"></i></div>
                    <h4>Kerahasiaan Terjamin</h4>
                    <p>Identitas Anda sebagai pelapor yang beritikad baik akan kami lindungi dan rahasiakan sepenuhnya.</p>
                </div>
                <div class="info-card">
                    <div class="icon-wrapper"><i class="fas fa-user-secret"></i></div>
                    <h4>Pelaporan Anonim</h4>
                    <p>Anda dapat mengirimkan laporan tanpa perlu menyertakan identitas pribadi Anda (anonim).</p>
                </div>
                 <div class="info-card">
                    <div class="icon-wrapper"><i class="fas fa-tasks"></i></div>
                    <h4>Penanganan Profesional</h4>
                    <p>Setiap laporan yang masuk akan ditangani oleh tim pengelola WBS yang berwenang dan profesional.</p>
                </div>
                 <div class="info-card">
                    <div class="icon-wrapper"><i class="fas fa-chart-line"></i></div>
                    <h4>Pemantauan Mudah</h4>
                    <p>Lacak perkembangan status laporan Anda dengan mudah menggunakan nomor tracking yang unik.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="section">
        <div class="container">
            <h2 class="section-title">Cara Membuat Laporan</h2>
            <div class="grid grid-4-col steps-grid">
                <div class="step-card"><span>1</span><h4>Buka Formulir</h4><p>Klik tombol "Buat Laporan" untuk membuka halaman formulir.</p></div>
                <div class="step-card"><span>2</span><h4>Lengkapi Laporan</h4><p>Isi semua detail yang diperlukan selengkap mungkin, sertakan bukti jika ada.</p></div>
                <div class="step-card"><span>3</span><h4>Kirim Laporan</h4><p>Setelah formulir terisi lengkap, kirim laporan Anda dan catat nomor tracking.</p></div>
                <div class="step-card"><span>4</span><h4>Pantau Laporan</h4><p>Gunakan nomor tracking Anda pada halaman "Lacak Laporan".</p></div>
            </div>
        </div>
    </section>

    <section class="section section-light">
        <div class="container" style="max-width: 800px;">
            <h2 class="section-title">Frequently Asked Questions (FAQ)</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">Apa itu Whistleblowing System (WBS)?</div>
                    <div class="faq-answer"><p>WBS adalah sistem untuk memproses pengaduan/pengungkapan informasi mengenai tindakan pelanggaran yang terjadi di lingkungan Kantor Kementerian Agama Kota Depok.</p></div>
                </div>
                 <div class="faq-item">
                    <div class="faq-question">Siapa saja yang bisa melapor?</div>
                    <div class="faq-answer"><p>Siapa saja, baik dari kalangan internal (pegawai) maupun eksternal (masyarakat, mitra kerja) yang memiliki informasi kredibel dapat membuat laporan.</p></div>
                </div>
                 <div class="faq-item">
                    <div class="faq-question">Apakah identitas saya akan aman?</div>
                    <div class="faq-answer"><p>Ya. Kami berkomitmen penuh untuk melindungi kerahasiaan identitas setiap pelapor yang beritikad baik, sesuai dengan pedoman WBS yang berlaku.</p></div>
                </div>
            </div>
        </div>
    </section>

</main>

<?php require_once 'templates/footer.php'; ?>