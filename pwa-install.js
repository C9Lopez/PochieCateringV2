let deferredPrompt;
const installBtn = document.getElementById('installAppBtn');

if (installBtn) {
    // Check if already in standalone mode (app installed and running)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                         window.navigator.standalone === true;
    
    if (isStandalone) {
        // Already running as installed app, hide button
        installBtn.style.setProperty('display', 'none', 'important');
        console.log('App is running in standalone mode');
    } else {
        // Show button - user can install
        installBtn.style.setProperty('display', 'inline-flex', 'important');
        console.log('PWA install button shown');
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt = e;
        
        // Show the button immediately since we have the prompt ready
        if (installBtn) {
            installBtn.style.setProperty('display', 'inline-flex', 'important');
            // Optional: Auto-trigger prompt after first user interaction if you want it very fast
            console.log('Native PWA prompt is ready to be triggered');
        }
    });

    installBtn.addEventListener('click', async (e) => {
        if (deferredPrompt) {
            // Show the native install prompt
            deferredPrompt.prompt();
            
            // Wait for the user to respond to the prompt
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response to install prompt: ${outcome}`);
            
            if (outcome === 'accepted') {
                installBtn.style.setProperty('display', 'none', 'important');
            }
            deferredPrompt = null;
        } else {
            // Fallback if still not ready (usually due to HTTPS/Not Secure issue)
            if (window.location.protocol === 'https:' && window.location.hostname === 'localhost') {
                alert('Mabilis na Install Error: Naka-HTTPS ka sa localhost pero "Not Secure".\n\nSolution: Gamitin ang http://localhost/catering (walang "s") para lumabas ang mabilis na install prompt.');
            } else {
                alert('Para i-install:\n1. Click ang 3 dots (⋮)\n2. Click "Install Pochie Catering Services..."');
            }
        }
    });

    window.addEventListener('appinstalled', (evt) => {
        console.log('PWA was installed.');
        installBtn.style.setProperty('display', 'none', 'important');
    });
}
