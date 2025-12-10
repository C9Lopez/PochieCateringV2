    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">🍲 <?= $settings['site_name'] ?? 'Filipino Catering' ?></h5>
                    <p class="text-white-50">Authentic Filipino catering services for all your special occasions. From intimate gatherings to grand celebrations.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= url('index.php') ?>" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?= url('menu.php') ?>" class="text-white-50 text-decoration-none">Menu</a></li>
                        <li class="mb-2"><a href="<?= url('packages.php') ?>" class="text-white-50 text-decoration-none">Packages</a></li>
                        <li class="mb-2"><a href="<?= url('book.php') ?>" class="text-white-50 text-decoration-none">Book Now</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Contact Us</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= $settings['site_address'] ?? 'Manila, Philippines' ?></li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i><?= $settings['site_phone'] ?? '09123456789' ?></li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i><?= $settings['site_email'] ?? 'info@filipinocatering.com' ?></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h6 class="mb-3">Follow Us</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50 fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white-50 fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white-50 fs-4"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white-50 fs-4"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="text-center text-white-50">
                <small>&copy; <?= date('Y') ?> <?= $settings['site_name'] ?? 'Filipino Catering' ?>. All rights reserved.</small>
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
    </script>
</body>
</html>
