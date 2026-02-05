let deferredPrompt = null;
const installBtn = document.getElementById('installAppBtn');

// Listen for beforeinstallprompt IMMEDIATELY when script loads
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('beforeinstallprompt event fired!');
    e.preventDefault();
    deferredPrompt = e;
    
    // Show install button
    if (installBtn) {
        installBtn.style.setProperty('display', 'inline-flex', 'important');
        installBtn.classList.add('pwa-ready');
    }
});

if (installBtn) {
    // Check if already in standalone mode
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                         window.navigator.standalone === true;
    
    if (isStandalone) {
        installBtn.style.setProperty('display', 'none', 'important');
        console.log('PWA: Running in standalone mode');
    } else {
        installBtn.style.setProperty('display', 'inline-flex', 'important');
    }

    installBtn.addEventListener('click', async () => {
        if (deferredPrompt) {
            // Native install prompt available
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                installBtn.style.setProperty('display', 'none', 'important');
            }
            deferredPrompt = null;
        } else {
            // Check for specific issues
            const issues = [];
            
            // Check service worker
            if (!('serviceWorker' in navigator)) {
                issues.push('Service Worker not supported');
            }
            
            // Check if manifest exists
            const manifestLink = document.querySelector('link[rel="manifest"]');
            if (!manifestLink) {
                issues.push('Manifest link not found');
            }
            
            if (issues.length > 0) {
                console.error('PWA Issues:', issues);
            }
            
            // Use browser's native install if available (Chrome 76+)
            if ('getInstalledRelatedApps' in navigator) {
                const relatedApps = await navigator.getInstalledRelatedApps();
                if (relatedApps.length > 0) {
                    alert('Naka-install na ang app!');
                    installBtn.style.setProperty('display', 'none', 'important');
                    return;
                }
            }
            
            // Final fallback - manual instructions
            alert('Para i-install ang app:\n\n' +
                  'Desktop Chrome: Click ang icon sa address bar (tabi ng star) o 3 dots menu > "Install Pochie Catering Services"\n\n' +
                  'Mobile: Tap Share > "Add to Home Screen"');
        }
    });

    window.addEventListener('appinstalled', () => {
        console.log('PWA installed successfully');
        installBtn.style.setProperty('display', 'none', 'important');
        deferredPrompt = null;
    });
}
