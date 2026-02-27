document.addEventListener('DOMContentLoaded', () => {
    const btnGroupe = document.getElementById('btn-groupe');
    const btnVisiteur = document.getElementById('btn-visiteur');
    const groupInputs = document.querySelector('.group-mode');
    const visitorInputs = document.querySelector('.visitor-mode');
    const formModeInput = document.getElementById('formMode');
    // const loginCard = document.querySelector('.login-card'); // Not strictly needed for logic unless resizing explicitly

    // Initial state
    let isGroupMode = true;

    function setMode(group) {
        isGroupMode = group;
        if (isGroupMode) {
            btnGroupe.classList.add('active');
            btnVisiteur.classList.remove('active');

            // Show group inputs, hide visitor inputs
            groupInputs.classList.add('active');
            visitorInputs.classList.remove('active');

            groupInputs.style.display = 'flex';
            visitorInputs.style.display = 'none';
        } else {
            btnVisiteur.classList.add('active');
            btnGroupe.classList.remove('active');

            visitorInputs.classList.add('active');
            groupInputs.classList.remove('active');

            visitorInputs.style.display = 'flex';
            groupInputs.style.display = 'none';
        }

        if (formModeInput) {
            formModeInput.value = group ? 'groupe' : 'visiteur';
        }
    }

    btnGroupe.addEventListener('click', () => setMode(true));
    btnVisiteur.addEventListener('click', () => setMode(false));

    // Form submission parsing
    const loginForm = document.getElementById('loginForm');
    const loginUsername = document.getElementById('loginUsername');
    const accessCodeInput = document.getElementById('access-code');
    const visitorEmailInput = document.getElementById('visitor-name');
    const visitorPassInput = document.getElementById('visitor-pass');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            if (isGroupMode) {
                // Group logging in
                loginUsername.value = accessCodeInput.value;
                if (!visitorPassInput.value) visitorPassInput.value = accessCodeInput.value;

                // Disable visitor inputs so they aren't submitted
                visitorEmailInput.disabled = true;
            } else {
                loginUsername.value = visitorEmailInput.value;

                // Disable group inputs so they aren't submitted
                accessCodeInput.disabled = true;
            }
        });
    }

    // Reload page when returning via browser back button (bfcache) to refresh CSRF token
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        } else {
            // Only reset if we are not displaying errors (i.e. fresh page load)
            const hasErrors = document.querySelector('.validation-error-message') || document.querySelector('.error-highlight') || (document.querySelector('.login-card').innerHTML.indexOf('ul') !== -1 && document.querySelector('ul').style.color === 'rgb(255, 204, 204)');

            if (!hasErrors) {
                if (accessCodeInput) accessCodeInput.value = '';
                if (visitorEmailInput) visitorEmailInput.value = '';
                if (visitorPassInput) visitorPassInput.value = '';
            }
        }
    });

    let defaultMode = true; // true = groupe, false = visiteur
    if (formModeInput && formModeInput.value === 'visiteur') {
        defaultMode = false;
    }
    setMode(defaultMode);

    // Auto-scroll to the first error message on load if it exists
    const firstError = document.querySelector('.validation-error-message') || document.querySelector('.error-highlight');
    if (firstError) {
        // slightly delay to ensure render is complete
        setTimeout(() => {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
});
