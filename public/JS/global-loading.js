/**
 * Global Loading UI Handler
 * Applies a loading state to submit buttons on form submission.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.tagName === 'FORM') {
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            
            submitButtons.forEach(btn => {
                btn.classList.add('loading-btn');
                
                if (btn.tagName === 'INPUT') {
                    if (!btn.hasAttribute('data-original-value')) {
                        btn.setAttribute('data-original-value', btn.value);
                    }
                    btn.value = 'Chargement...';
                } else if (btn.tagName === 'BUTTON') {
                    if (!btn.hasAttribute('data-original-html')) {
                        btn.setAttribute('data-original-html', btn.innerHTML);
                    }
                    // Fixer la largeur pour éviter que le bouton ne saute
                    if (!btn.style.width) {
                        btn.style.width = btn.offsetWidth + 'px';
                    }
                    btn.innerHTML = 'Chargement...';
                }
            });
        }
    });

    window.setBtnLoading = function(btn, isLoading = true) {
        if (!btn) return;
        
        if (isLoading) {
            btn.classList.add('loading-btn');
            if (btn.tagName === 'INPUT') {
                if (!btn.hasAttribute('data-original-value')) {
                    btn.setAttribute('data-original-value', btn.value);
                }
                btn.value = 'Chargement...';
            } else if (btn.tagName === 'BUTTON') {
                if (!btn.hasAttribute('data-original-html')) {
                    btn.setAttribute('data-original-html', btn.innerHTML);
                }
                if (!btn.style.width) {
                    btn.style.width = btn.offsetWidth + 'px';
                }
                btn.innerHTML = 'Chargement...';
            }
        } else {
            btn.classList.remove('loading-btn');
            if (btn.tagName === 'INPUT' && btn.hasAttribute('data-original-value')) {
                btn.value = btn.getAttribute('data-original-value');
            } else if (btn.tagName === 'BUTTON' && btn.hasAttribute('data-original-html')) {
                btn.innerHTML = btn.getAttribute('data-original-html');
                btn.style.width = ''; // Reset width
            }
        }
    };
});
