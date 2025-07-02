<footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-widget">
                    <h4>Tentang WBS</h4>
                    <p><i>Whistleblowing System</i> adalah sarana untuk melaporkan perbuatan yang terindikasi pelanggaran di lingkungan Kantor Kementerian Agama Kota Depok.</p>
		    <br>
		    <p>depokkota@kemenag.go.id</p>
                </div>
                <div class="footer-widget">
                    <h4>Tautan Cepat</h4>
                    <ul>
                        <li><a href="index">Beranda</a></li>
                        <li><a href="lacak">Lacak Laporan</a></li>
                        <li><a href="statistik">Statistik</a></li>
                        <li><a href="admin">Login Pengelola</a></li>
                    </ul>
                </div>
                <div class="footer-widget">
                    <h4>Alamat</h4>
                    <p>
                        Jalan Boulevard Raya - Kota Kembang Tirtajaya Sukmajaya, Kel. Kalimulya, Kec. Cilodong, Kota Depok, Jawa Barat, Indonesia - 16413
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> Kantor Kementerian Agama Kota Depok. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script>
        // JavaScript untuk menu mobile
        const menuToggle = document.getElementById('menu-toggle');
        const mainNav = document.querySelector('.main-navigation');
        if(menuToggle && mainNav) {
            menuToggle.addEventListener('click', () => {
                mainNav.classList.toggle('active');
            });
        }

        // ## JAVASCRIPT BARU UNTUK FAQ ##
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            question.addEventListener('click', () => {
                // Tutup semua item lain yang mungkin terbuka
                const currentlyActive = document.querySelector('.faq-item.active');
                if (currentlyActive && currentlyActive !== item) {
                    currentlyActive.classList.remove('active');
                }
                // Buka atau tutup item yang diklik
                item.classList.toggle('active');
            });
        });
    </script>
</body>
</html>