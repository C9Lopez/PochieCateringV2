let deferredPrompt;
const installBtn = document.getElementById('installAppBtn');

if (installBtn) {
    // Hide the button initially
    installBtn.style.setProperty('display', 'none', 'important');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt = e;
        // Update UI to notify the user they can add to home screen
        installBtn.style.setProperty('display', 'inline-flex', 'important');
        
        console.log('PWA install prompt is ready');
    });

    installBtn.addEventListener('click', (e) => {
        if (!deferredPrompt) {
            // Fallback message if prompt not available
            alert('Para i-install ang app:\n\n1. Click ang 3 dots (⋮) sa Chrome\n2. Click "Install Pochie Catering Services..."');
            return;
        }
        
        // Hide our user interface that shows our A2HS button
        installBtn.style.setProperty('display', 'none', 'important');
        // Show the prompt
        deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
            } else {
                console.log('User dismissed the A2HS prompt');
                // Show the button again if they dismissed it
                installBtn.style.setProperty('display', 'inline-flex', 'important');
            }
            deferredPrompt = null;
        });
    });

    window.addEventListener('appinstalled', (evt) => {
        console.log('PWA was installed.');
        installBtn.style.setProperty('display', 'none', 'important');
    });

    // Check if already running as installed app
    if (window.matchMedia('(display-mode: standalone)').matches) {
        installBtn.style.setProperty('display', 'none', 'important');
    }
}
