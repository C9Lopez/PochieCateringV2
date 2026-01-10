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
                      <button type="button" class="btn btn-primary" onclick="saveCookieSettings()">
                          <i class="bi bi-check2 me-1"></i>I-save ang Settings
                      </button>
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
                      <p>Ang cookies ay maliliit na text files na iniimbak sa iyong device (computer, tablet, o mobile phone) kapag bumibisita ka sa aming website. Ang mga ito ay malawakang ginagamit upang gumana nang maayos ang mga website at upang magbigay ng impormasyon sa mga may-ari ng website.</p>
                      
                      <h6 class="fw-bold mt-4">Paano Namin Ginagamit ang Cookies?</h6>
                      <p>Gumagamit kami ng cookies para sa mga sumusunod na layunin:</p>
                      <ul>
                          <li><strong>Kinakailangang Cookies:</strong> Ang mga ito ay mahalaga para gumana ang aming website nang maayos. Kasama dito ang cookies na nagpapaalala sa iyong session at mga preference.</li>
                          <li><strong>Analytics Cookies:</strong> Ang mga ito ay nagbibigay-daan sa amin na suriin kung paano ginagamit ng mga bisita ang aming website, na tumutulong sa amin na mapabuti ito.</li>
                          <li><strong>Marketing Cookies:</strong> Ang mga ito ay ginagamit upang ipakita ang mga advertisement na mas relevant sa iyo at sa iyong mga interes.</li>
                      </ul>
                      
                      <h6 class="fw-bold mt-4">Paano Ko Makokontrol ang Cookies?</h6>
                      <p>Maaari mong i-manage ang iyong cookie preferences sa pamamagitan ng pag-click sa "Cookie Settings" button sa aming cookie banner. Maaari mo ring i-configure ang iyong browser na tanggihan ang lahat ng cookies o alertuhan ka kapag may cookie na inilalagay.</p>
                      
                      <h6 class="fw-bold mt-4">Alinsunod sa Data Privacy Act of 2012 (RA 10173)</h6>
                      <p>Alinsunod sa Republic Act No. 10173 o ang Data Privacy Act of 2012, iginagalang namin ang iyong karapatan sa privacy. Ang iyong personal na impormasyon na nakolekta sa pamamagitan ng cookies ay mapoprotektahan at gagamitin lamang para sa mga layuning nabanggit sa itaas.</p>
                      
                      <div class="alert alert-warning mt-4" style="border-radius: 12px;">
                          <i class="bi bi-info-circle me-2"></i>
                          <strong>Paalala:</strong> Kung patuloy mong gamitin ang aming website nang hindi binabago ang iyong cookie settings, itinuturing naming tinatanggap mo ang paggamit ng cookies.
                      </div>
                      
                      <h6 class="fw-bold mt-4">Makipag-ugnayan</h6>
                      <p>Kung mayroon kang mga katanungan tungkol sa aming paggamit ng cookies, mangyaring makipag-ugnayan sa amin sa pamamagitan ng aming contact page.</p>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Isara</button>
                  </div>
              </div>
          </div>
      </div>
      
      <style>
          .cookie-banner {
              position: fixed;
              bottom: 0;
              left: 0;
              right: 0;
              background: #1e293b;
              color: #e2e8f0;
              z-index: 9999;
              box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
              display: none;
          }
          .cookie-banner.show {
              display: block;
              animation: slideUp 0.3s ease-out;
          }
          @keyframes slideUp {
              from { transform: translateY(100%); }
              to { transform: translateY(0); }
          }
          .cookie-content {
              display: flex;
              align-items: center;
              justify-content: space-between;
              padding: 16px 50px 16px 30px;
              max-width: 1600px;
              margin: 0 auto;
              gap: 30px;
          }
          .cookie-text {
              flex: 1;
          }
          .cookie-text strong {
              font-size: 15px;
              font-weight: 600;
              display: block;
              margin-bottom: 4px;
          }
          .cookie-text p {
              font-size: 12px;
              line-height: 1.5;
              margin: 0;
              color: #94a3b8;
          }
          .cookie-link {
              color: #f97316;
              text-decoration: underline;
          }
          .cookie-link:hover {
              color: #fb923c;
          }
          .cookie-buttons {
              display: flex;
              gap: 12px;
              flex-shrink: 0;
          }
          .cookie-btn {
              padding: 10px 20px;
              border-radius: 8px;
              font-size: 13px;
              font-weight: 500;
              cursor: pointer;
              transition: all 0.2s ease;
              white-space: nowrap;
          }
          .cookie-settings {
              background: transparent;
              border: 1px solid #64748b;
              color: #e2e8f0;
          }
          .cookie-settings:hover {
              background: rgba(255, 255, 255, 0.1);
              border-color: #f97316;
              color: #f97316;
          }
          .cookie-reject {
              background: #475569;
              border: none;
              color: white;
          }
          .cookie-reject:hover {
              background: #64748b;
          }
          .cookie-accept {
              background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
              border: none;
              color: white;
          }
          .cookie-accept:hover {
              background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
              box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
          }
          .cookie-close {
              position: absolute;
              top: 50%;
              right: 15px;
              transform: translateY(-50%);
              background: none;
              border: none;
              color: #94a3b8;
              font-size: 24px;
              cursor: pointer;
              padding: 5px;
              line-height: 1;
          }
          .cookie-close:hover {
              color: #f97316;
          }
          @media (max-width: 991px) {
              .cookie-content {
                  flex-direction: column;
                  text-align: center;
                  padding: 20px 40px 20px 20px;
                  gap: 15px;
              }
              .cookie-buttons {
                  flex-wrap: wrap;
                  justify-content: center;
              }
          }
          @media (max-width: 576px) {
              .cookie-buttons {
                  flex-direction: column;
                  width: 100%;
              }
              .cookie-btn {
                  width: 100%;
              }
          }
          
          .form-check-input:checked {
              background-color: #f97316;
              border-color: #f97316;
          }
          .form-check-input:focus {
              box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.2);
              border-color: #f97316;
          }
      </style>
      
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
                  const formatter = new Intl.DateTimeFormat('en-PH', options);
                  const display = document.getElementById('ph-time-display');
                  if (display) {
                      display.textContent = formatter.format(now) + ' (PHT)';
                  }
              } catch (e) {
                  console.error('Time display error:', e);
              }
          }
  
          // Cookie Consent Functions
          function checkCookieConsent() {
              const consent = localStorage.getItem('cookieConsent');
              if (!consent) {
                  const banner = document.getElementById('cookieConsent');
                  if (banner) banner.classList.add('show');
              } else {
                  loadCookiePreferences();
              }
          }
          
          function acceptCookies() {
              const preferences = {
                  necessary: true,
                  analytics: true,
                  marketing: true,
                  timestamp: new Date().toISOString()
              };
              localStorage.setItem('cookieConsent', 'accepted');
              localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
              const banner = document.getElementById('cookieConsent');
              if (banner) banner.classList.remove('show');
          }
          
          function rejectCookies() {
              const preferences = {
                  necessary: true,
                  analytics: false,
                  marketing: false,
                  timestamp: new Date().toISOString()
              };
              localStorage.setItem('cookieConsent', 'rejected');
              localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
              const banner = document.getElementById('cookieConsent');
              if (banner) banner.classList.remove('show');
          }
          
          function closeCookieBanner() {
              const banner = document.getElementById('cookieConsent');
              if (banner) banner.classList.remove('show');
          }
          
          function saveCookieSettings() {
              const analytics = document.getElementById('analyticsCookies').checked;
              const marketing = document.getElementById('marketingCookies').checked;
              
              const preferences = {
                  necessary: true,
                  analytics: analytics,
                  marketing: marketing,
                  timestamp: new Date().toISOString()
              };
              
              localStorage.setItem('cookieConsent', 'custom');
              localStorage.setItem('cookiePreferences', JSON.stringify(preferences));
              const banner = document.getElementById('cookieConsent');
              if (banner) banner.classList.remove('show');
              
              const modalElem = document.getElementById('cookieSettingsModal');
              if (modalElem) {
                  const modal = bootstrap.Modal.getInstance(modalElem);
                  if (modal) modal.hide();
              }
              
              showToast('Matagumpay na na-save ang iyong cookie preferences!');
          }
          
          function loadCookiePreferences() {
              const prefs = localStorage.getItem('cookiePreferences');
              if (prefs) {
                  const preferences = JSON.parse(prefs);
                  const analyticsInput = document.getElementById('analyticsCookies');
                  const marketingInput = document.getElementById('marketingCookies');
                  if (analyticsInput) analyticsInput.checked = preferences.analytics || false;
                  if (marketingInput) marketingInput.checked = preferences.marketing || false;
              }
          }
          
          function showToast(message) {
              const toast = document.createElement('div');
              toast.className = 'position-fixed bottom-0 end-0 p-3';
              toast.style.zIndex = '10000';
              toast.innerHTML = `
                  <div class="toast show" role="alert" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; border-radius: 12px;">
                      <div class="toast-body d-flex align-items-center gap-2">
                          <i class="bi bi-check-circle-fill"></i>
                          ${message}
                      </div>
                  </div>
              `;
              document.body.appendChild(toast);
              setTimeout(() => toast.remove(), 3000);
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
              checkCookieConsent();
              loadCookiePreferences();
          });
      </script>
      <script src="<?= url('pwa-install.js') ?>"></script>
  </body>
  </html>
