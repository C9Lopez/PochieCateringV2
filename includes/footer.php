    <footer style="background: #1e293b; color: #ffffff; padding: 60px 0 40px; font-family: 'Poppins', sans-serif;">
        <div class="container">
            <div class="row">
                <div class="col-12 text-start">
                    <p class="mb-2" style="font-size: 14px; font-weight: 500; opacity: 0.9;">
                        &copy; 2026 Pochie Catering Services. All rights reserved.
                    </p>
                    <p class="mb-4" style="font-size: 14px; opacity: 0.7; line-height: 1.8; max-width: 900px;">
                        Pochie Catering Services provides access to professional catering solutions and delicious Filipino cuisine. 
                        Pochie Catering Services is a registered business under the ownership of Pochollo Glen Gutierrez. Use of our products and services is governed by our 
                        <a href="<?= url('terms.php') ?>" style="color: #ffffff; text-decoration: underline; opacity: 0.8;">Terms of Use</a> and 
                        <a href="<?= url('privacy.php') ?>" style="color: #ffffff; text-decoration: underline; opacity: 0.8;">Privacy Policy</a>.
                    </p>
                    <div class="d-flex align-items-center mt-3">
                        <a href="<?= url('privacy.php#choices') ?>" style="color: #ffffff; text-decoration: none; font-size: 14px; border-bottom: 1px solid rgba(255,255,255,0.3); padding-bottom: 2px; opacity: 0.9;">Your privacy choices</a>
                        <a href="<?= url('privacy.php#choices') ?>">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/California_Privacy_Options_Icon.svg/1024px-California_Privacy_Options_Icon.svg.png" 
                                 alt="Privacy Choices" style="height: 14px; margin-left: 10px; opacity: 0.9; cursor: pointer;">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showAlert(icon, title, text) {
            Swal.fire({ icon, title, text, confirmButtonColor: '#f97316' });
        }
        
        function confirmAction(title, text, callback) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) callback();
            });
        }

        function updateDisplayTime() {
            const now = new Date();
            const options = { 
                timeZone: 'Asia/Manila',
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            
            try {
                const formatter = new Intl.DateTimeFormat('en-US', options);
                const display = document.getElementById('ph-time-display');
                if (display) {
                    display.textContent = formatter.format(now);
                }
            } catch (e) {
                console.error('Time display error:', e);
            }
        }

        // Service Worker Registration for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                const swPath = "<?= url('sw.js') ?>";
                navigator.serviceWorker.register(swPath)
                    .then(reg => console.log('Service Worker registered:', reg.scope))
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateDisplayTime();
            setInterval(updateDisplayTime, 1000);
        });
    </script>
    <script src="<?= url('pwa-install.js') ?>"></script>
</body>
</html>
