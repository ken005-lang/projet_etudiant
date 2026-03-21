document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                const span = btn.querySelector('.btn-text') || btn.querySelector('span');
                if (span) {
                    if (!span.dataset.originalText) span.dataset.originalText = span.textContent;
                    span.textContent = 'Chargement...';
                } else {
                    if (!btn.dataset.originalText) btn.dataset.originalText = btn.textContent;
                    btn.textContent = 'Chargement...';
                }
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.7';
            }
        });
    });
});
