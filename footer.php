    <!-- FOOTER -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="/project-urfarm/index.php" class="nav-logo">Ur<span>Farm</span></a>
                <p class="footer-desc">Platform donasi lingkungan untuk mendorong penghijauan dan pelestarian alam
                    Indonesia.</p>
                <div class="footer-contact">
                    📧 hello@urfarm.id<br>
                    📱 +62 851-3456-7890
                </div>
            </div>
            <div>
                <div class="footer-heading">Navigasi</div>
                <div class="footer-links">
                    <a href="/project-urfarm/index.php">Home</a>
                    <a href="/project-urfarm/pages/program.php">Program</a>
                    <a href="/project-urfarm/pages/partner.php">Partner</a>
                    <a href="/project-urfarm/pages/publikasi.php">Publikasi</a>
                </div>
            </div>
            <div>
                <div class="footer-heading">Bantuan</div>
                <div class="footer-links">
                    <a href="/project-urfarm/pages/about/faq.php">FAQ</a>
                    <a href="/project-urfarm/pages/about/contact.php">Hubungi Kami</a>
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat Penggunaan</a>
                </div>
            </div>
            <div>
                <div class="footer-heading">Ikuti Kami</div>
                <div class="social-links">
                    <a href="#" class="social-btn" title="Facebook">f</a>
                    <a href="#" class="social-btn" title="Instagram">ig</a>
                    <a href="#" class="social-btn" title="Twitter">tw</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 UrFarm. Hak Cipta Dilindungi.</span>
            <div style="display:flex;gap:20px;">
                <a href="#">Privasi</a>
                <a href="#">Ketentuan</a>
            </div>
        </div>
    </footer>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
        });

        // Mobile menu
        document.getElementById('menuToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('mobile-open');
        });

        // Tab switching
        function switchTab(n) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab' + n).classList.add('active');
            document.querySelectorAll('.tab-btn')[n - 1].classList.add('active');
        }



        // Counter animation
        function animateCounter(id, target, prefix, suffix, duration) {
            const el = document.getElementById(id);
            if (!el) return;
            const start = performance.now();
            const update = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const val = Math.floor(progress * target);
                el.textContent = prefix + val.toLocaleString('id') + suffix;
                if (progress < 1) requestAnimationFrame(update);
            };
            requestAnimationFrame(update);
        }

        window.addEventListener('load', () => {
            if (document.getElementById('stat1')) {
                animateCounter('stat1', 42, '', '', 1500);
                animateCounter('stat2', 450, 'Rp ', 'Jt+', 2000);
                animateCounter('stat3', 120, '', '+', 1500);
            }
        });

        // Auto-hide notification
        <?php if (isset($message) && $message): ?>
            setTimeout(() => {
                const n = document.getElementById('notif');
                if (n) n.remove();
            }, 5000);
        <?php endif; ?>
    </script>

</body>

</html>
