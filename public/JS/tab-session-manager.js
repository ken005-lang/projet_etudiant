document.addEventListener('DOMContentLoaded', function() {
    let isInternalNavigation = false;

    // Détecter les clics sur des liens internes (qui ne s'ouvrent pas dans un nouvel onglet)
    document.addEventListener('click', function(e) {
        let link = e.target.closest('a');
        if (link && !link.hasAttribute('target') && link.href) {
            try {
                const url = new URL(link.href);
                if (url.origin === window.location.origin) {
                    isInternalNavigation = true;
                }
            } catch (err) {}
        }
    });

    // Détecter la soumission des formulaires
    document.addEventListener('submit', function() {
        isInternalNavigation = true;
    });

    // Lorsque l'onglet est fermé ou rafraîchi
    window.addEventListener('pagehide', function(e) {
        // Seulement si ce n'est pas une navigation interne à l'application
        if (!isInternalNavigation) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                const formData = new FormData();
                formData.append('_token', csrfToken);
                
                // Envoi d'une requête discrète asynchrone pour informer de la fermeture imminente
                navigator.sendBeacon('/tab-closing', formData);
            }
        }
    });
});
