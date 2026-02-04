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
        // Make sure button is visible
        installBtn.style.setProperty('display', 'inline-flex', 'important');
        console.log('PWA install prompt is ready');
    });

    installBtn.addEventListener('click', (e) => {
        if (deferredPrompt) {
            // Use the native prompt if available
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the A2HS prompt');
                    installBtn.style.setProperty('display', 'none', 'important');
                } else {
                    console.log('User dismissed the A2HS prompt');
                }
                deferredPrompt = null;
            });
        } else {
            // Fallback: show instructions for manual install
            alert('Para i-install ang app:\n\n• Desktop: Click ang 3 dots (⋮) sa Chrome > "Install Pochie Catering Services..."\n\n• Mobile: Tap ang Share button > "Add to Home Screen"');
        }
    });

    window.addEventListener('appinstalled', (evt) => {
        console.log('PWA was installed.');
        installBtn.style.setProperty('display', 'none', 'important');
    });
}
