/**
 * Service Worker Registration
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register(cfiData.pluginUrl + 'assets/js/sw.js')
            .then(function(registration) {
                console.log('CFI ServiceWorker registered:', registration.scope);
            })
            .catch(function(error) {
                console.log('CFI ServiceWorker registration failed:', error);
            });
    });
}
