<?php
// Re-fetch settings if not available (to ensure consistency across all pages)
if (!isset($settings)) {
    $settings = getSettings($conn);
}
$siteName = $settings['site_name'] ?? 'Pochie Catering Services';
$siteEmail = $settings['site_email'] ?? 'info@filipinocatering.com';
$sitePhone = $settings['site_phone'] ?? '+63 912 345 6789';
$siteAddress = $settings['site_address'] ?? 'Metro Manila, Philippines';
?>
<footer style="background: #0f172a; color: #ffffff; padding: 60px 0 30px; font-family: 'Poppins', sans-serif;">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & About -->
            <div class="col-lg-4 col-md-6">
                <h5 style="font-family: 'Playfair Display', serif; font-weight: 600; margin-bottom: 20px; color: #f97316;">
                    🍲 <?= htmlspecialchars($siteName) ?>
                </h5>
                <p style="font-size: 14px; opacity: 0.8; line-height: 1.6; margin-bottom: 20px;">
                    Experience the authentic taste of Filipino cuisine. We provide professional catering services for all types of events, ensuring a memorable dining experience for you and your guests.
                </p>
                <p style="font-size: 13px; opacity: 0.7;">
                    <strong>Registered Owner:</strong><br>
                    Pochollo Glen Gutierrez
                </p>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 style="font-family: 'Playfair Display', serif; font-weight: 600; margin-bottom: 20px;">Quick Links</h5>
                <ul class="list-unstyled" style="font-size: 14px;">
                    <li class="mb-2"><a href="<?= url('index.php') ?>" style="color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s;">Home</a></li>
                    <li class="mb-2"><a href="<?= url('menu.php') ?>" style="color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s;">Our Menu</a></li>
                    <li class="mb-2"><a href="<?= url('packages.php') ?>" style="color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s;">Packages</a></li>
                    <li class="mb-2"><a href="<?= url('book.php') ?>" style="color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s;">Book Now</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6">
                <h5 style="font-family: 'Playfair Display', serif; font-weight: 600; margin-bottom: 20px;">Contact Us</h5>
                <ul class="list-unstyled" style="font-size: 14px; opacity: 0.8;">
                    <li class="mb-3 d-flex align-items-start">
                        <i class="bi bi-geo-alt me-2 text-primary" style="color: #f97316 !important;"></i>
                        <?= htmlspecialchars($siteAddress) ?>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-telephone me-2 text-primary" style="color: #f97316 !important;"></i>
                        <?= htmlspecialchars($sitePhone) ?>
                    </li>
                    <li class="mb-3 d-flex align-items-center">
                        <i class="bi bi-envelope me-2 text-primary" style="color: #f97316 !important;"></i>
                        <?= htmlspecialchars($siteEmail) ?>
                    </li>
                </ul>
            </div>

            <!-- Social Media -->
            <div class="col-lg-3 col-md-6">
                <h5 style="font-family: 'Playfair Display', serif; font-weight: 600; margin-bottom: 20px;">Follow Us</h5>
                <div class="d-flex gap-3">
                    <a href="#" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: 0.3s;">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: 0.3s;">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" style="width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: 0.3s;">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 30px; margin-top: 40px;">
            <div class="row align-items-center">
                <div class="col-md-12 text-center">
                    <p class="mb-0" style="font-size: 14px; opacity: 0.9;">
                        &copy; 2026 <strong>Pochie Catering Services</strong>. All rights reserved.
                        <span class="mx-2" style="opacity: 0.3;">|</span>
                        <a href="<?= url('terms.php') ?>" style="color: rgba(255,255,255,0.8); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.2);">Terms of Use</a>
                        <span class="mx-2" style="opacity: 0.3;">|</span>
                        <a href="<?= url('privacy.php') ?>" style="color: rgba(255,255,255,0.8); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.2);">Privacy Policy</a>
                    </p>
                    <div class="mt-2 d-flex justify-content-center align-items-center gap-2" style="font-size: 13px;">
                        <a href="<?= url('privacy.php#choices') ?>" style="color: rgba(255,255,255,0.7); text-decoration: none;">Your privacy choices</a>
                        <a href="<?= url('privacy.php#choices') ?>">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/California_Privacy_Options_Icon.svg/1024px-California_Privacy_Options_Icon.svg.png" 
                                 alt="Privacy Choices" style="height: 14px; cursor: pointer; opacity: 0.8;">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<div id="cookieConsent" class="cookie-banner">
    <div class="cookie-content">
        <div class="cookie-text">
            <strong>Gumagamit ang website na ito ng cookies</strong>
            <p>Sa pag-click ng "Tanggapin lahat", sumasang-ayon ka sa pag-iimbak ng cookies sa iyong device upang mapabuti ang pag-navigate sa site, suriin ang paggamit ng site, at tumulong sa aming mga pagsisikap sa marketing. <a href="#" class="cookie-link" data-bs-toggle="modal" data-bs-target="#cookieNoticeModal">Cookie Notice</a></p>
        </div>
        <div class="cookie-buttons">
            <button type="button" class="cookie-btn cookie-settings" data-bs-toggle="modal" data-bs-target="#cookieSettingsModal">Cookie Settings</button>
            <button type="button" class="cookie-btn cookie-reject" onclick="rejectCookies()">Tanggihan lahat</button>
            <button type="button" class="cookie-btn cookie-accept" onclick="acceptCookies()">Tanggapin lahat</button>
        </div>
        <button type="button" class="cookie-close" onclick="closeCookieBanner()">&times;</button>
    </div>
</div>

<!-- Cookie Modals & Styles (Same as before for functionality) -->
<div class="modal fade" id="cookieSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%); color: white;">
                <h5 class="modal-title"><i class="bi bi-gear me-2"></i>Cookie Settings</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size: 14px;">
                <p class="text-muted mb-4">I-manage ang iyong cookie preferences. Ang ilang cookies ay kinakailangan para gumana nang maayos ang website.</p>
                <div class="cookie-setting-item mb-3 p-3" style="background: #f8fafc; border-radius: 12px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="bi bi-shield-check text-success me-2"></i>Kinakailangang Cookies</strong>
                            <p class="mb-0 text-muted" style="font-size: 13px;">Mga essential cookies para sa pag-function ng website.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" checked disabled style="width: 50px; height: 26px;">
                        </div>
                    </div>
                </div>
                <div class="cookie-setting-item mb-3 p-3" style="background: #f8fafc; border-radius: 12px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="bi bi-bar-chart text-primary me-2"></i>Analytics Cookies</strong>
                            <p class="mb-0 text-muted" style="font-size: 13px;">Tumutulong sa pag-intindi kung paano ginagamit ng mga bisita ang website.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input cookie-analytics" type="checkbox" id="analyticsCookies" style="width: 50px; height: 26px;">
                        </div>
                    </div>
                </div>
                <div class="cookie-setting-item mb-3 p-3" style="background: #f8fafc; border-radius: 12px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="bi bi-megaphone text-warning me-2"></i>Marketing Cookies</strong>
                            <p class="mb-0 text-muted" style="font-size: 13px;">Ginagamit para magpakita ng mga relevant na advertisement.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input cookie-marketing" type="checkbox" id="marketingCookies" style="width: 50px; height: 26px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Isara</button>
                <button type="button" class="btn btn-primary" onclick="saveCookieSettings()">I-save ang Settings</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cookieNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%); color: white;">
                <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Cookie Notice / Abiso sa Cookies</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size: 14px; line-height: 1.8;">
                <h6 class="fw-bold text-uppercase" style="color: #f97316;">Ano ang Cookies?</h6>
                <p>Ang cookies ay maliliit na text files na iniimbak sa iyong device kapag bumibisita ka sa aming website.</p>
                <h6 class="fw-bold mt-4">Paano Namin Ginagamit ang Cookies?</h6>
                <ul>
                    <li><strong>Kinakailangang Cookies:</strong> Mahalaga para gumana ang website.</li>
                    <li><strong>Analytics Cookies:</strong> Para mapabuti ang serbisyo.</li>
                    <li><strong>Marketing Cookies:</strong> Para sa relevant na ads.</li>
                </ul>
                <h6 class="fw-bold mt-4">Data Privacy Act of 2012</h6>
                <p>Alinsunod sa RA 10173, pinoprotektahan namin ang iyong data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Isara</button>
            </div>
        </div>
    </div>
</div>

<style>
    .cookie-banner { position: fixed; bottom: 0; left: 0; right: 0; background: #1e293b; color: #e2e8f0; z-index: 9999; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3); display: none; }
    .cookie-banner.show { display: block; animation: slideUp 0.3s ease-out; }
    @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    .cookie-content { display: flex; align-items: center; justify-content: space-between; padding: 16px 50px 16px 30px; max-width: 1600px; margin: 0 auto; gap: 30px; }
    .cookie-text { flex: 1; }
    .cookie-text strong { font-size: 15px; font-weight: 600; display: block; margin-bottom: 4px; }
    .cookie-text p { font-size: 12px; line-height: 1.5; margin: 0; color: #94a3b8; }
    .cookie-link { color: #f97316; text-decoration: underline; }
    .cookie-buttons { display: flex; gap: 12px; flex-shrink: 0; }
    .cookie-btn { padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; white-space: nowrap; }
    .cookie-settings { background: transparent; border: 1px solid #64748b; color: #e2e8f0; }
    .cookie-reject { background: #475569; border: none; color: white; }
    .cookie-accept { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border: none; color: white; }
    .cookie-close { position: absolute; top: 50%; right: 15px; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 24px; cursor: pointer; }
    @media (max-width: 991px) { .cookie-content { flex-direction: column; text-align: center; padding: 20px 40px 20px 20px; gap: 15px; } .cookie-buttons { flex-wrap: wrap; justify-content: center; } }
    @media (max-width: 576px) { .cookie-buttons { flex-direction: column; width: 100%; } .cookie-btn { width: 100%; } }
    .form-check-input:checked { background-color: #f97316; border-color: #f97316; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showAlert(icon, title, text) { Swal.fire({ icon, title, text, confirmButtonColor: '#f97316' }); }
    function confirmAction(title, text, callback) { Swal.fire({ title, text, icon: 'warning', showCancelButton: true, confirmButtonColor: '#f97316', cancelButtonColor: '#6c757d', confirmButtonText: 'Yes' }).then((result) => { if (result.isConfirmed) callback(); }); }
    function updateDisplayTime() {
        const now = new Date();
        const options = { timeZone: 'Asia/Manila', weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        try {
            const formatter = new Intl.DateTimeFormat('en-PH', options);
            const display = document.getElementById('ph-time-display');
            if (display) display.textContent = formatter.format(now) + ' (PHT)';
        } catch (e) { console.error('Time display error:', e); }
    }
    function checkCookieConsent() { const consent = localStorage.getItem('cookieConsent'); if (!consent) { const banner = document.getElementById('cookieConsent'); if (banner) banner.classList.add('show'); } else { loadCookiePreferences(); } }
    function acceptCookies() { localStorage.setItem('cookieConsent', 'accepted'); localStorage.setItem('cookiePreferences', JSON.stringify({ necessary: true, analytics: true, marketing: true, timestamp: new Date().toISOString() })); const banner = document.getElementById('cookieConsent'); if (banner) banner.classList.remove('show'); }
    function rejectCookies() { localStorage.setItem('cookieConsent', 'rejected'); localStorage.setItem('cookiePreferences', JSON.stringify({ necessary: true, analytics: false, marketing: false, timestamp: new Date().toISOString() })); const banner = document.getElementById('cookieConsent'); if (banner) banner.classList.remove('show'); }
    function closeCookieBanner() { const banner = document.getElementById('cookieConsent'); if (banner) banner.classList.remove('show'); }
    function saveCookieSettings() {
        const analytics = document.getElementById('analyticsCookies').checked;
        const marketing = document.getElementById('marketingCookies').checked;
        localStorage.setItem('cookieConsent', 'custom');
        localStorage.setItem('cookiePreferences', JSON.stringify({ necessary: true, analytics, marketing, timestamp: new Date().toISOString() }));
        const banner = document.getElementById('cookieConsent');
        if (banner) banner.classList.remove('show');
        const modal = bootstrap.Modal.getInstance(document.getElementById('cookieSettingsModal'));
        if (modal) modal.hide();
        showToast('Nai-save na ang iyong preference!');
    }
    function loadCookiePreferences() {
        const prefs = localStorage.getItem('cookiePreferences');
        if (prefs) {
            const p = JSON.parse(prefs);
            const a = document.getElementById('analyticsCookies');
            const m = document.getElementById('marketingCookies');
            if (a) a.checked = p.analytics; if (m) m.checked = p.marketing;
        }
    }
    function showToast(m) {
        const t = document.createElement('div');
        t.className = 'position-fixed bottom-0 end-0 p-3';
        t.style.zIndex = '10000';
        t.innerHTML = `<div class="toast show" style="background: #22c55e; color: white; border-radius: 12px;"><div class="toast-body"><i class="bi bi-check-circle-fill me-2"></i>${m}</div></div>`;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
    if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register("<?= url('sw.js') ?>").catch(err => console.log('SW failed:', err)); }); }
    document.addEventListener('DOMContentLoaded', () => { updateDisplayTime(); setInterval(updateDisplayTime, 1000); checkCookieConsent(); loadCookiePreferences(); });
</script>
<script src="<?= url('pwa-install.js') ?>"></script>
