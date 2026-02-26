document.addEventListener('DOMContentLoaded', function () {

    // Elements
    var form = document.getElementById('registrationForm');
    var btnGroupe = document.getElementById('reg-btn-groupe');
    var btnVisiteur = document.getElementById('reg-btn-visiteur');
    var groupFields = document.getElementById('group-fields');
    var visitorFields = document.getElementById('visitor-fields');
    var formModeInput = document.getElementById('formMode');
    var isGroupMode = true;

    // Toggle Mode: Groupe / Visiteur
    function setMode(group) {
        isGroupMode = group;

        btnGroupe.classList.toggle('active', group);
        btnVisiteur.classList.toggle('active', !group);

        groupFields.style.display = group ? 'flex' : 'none';
        visitorFields.style.display = group ? 'none' : 'flex';

        if (formModeInput) {
            formModeInput.value = group ? 'groupe' : 'visiteur';
        }
    }

    btnGroupe.addEventListener('click', function () { setMode(true); });
    btnVisiteur.addEventListener('click', function () { setMode(false); });


    // Form Submit Routing
    if (form) {
        form.addEventListener('submit', function (e) {

            var actionUrlGroup = document.getElementById('actionUrlGroup').value;
            var actionUrlVisitor = document.getElementById('actionUrlVisitor').value;

            if (isGroupMode) {
                // Ensure required minimal fields are filled locally before server validation for better UX
                var projectName = document.getElementById('projet_nom').value;
                if (!projectName) {
                    e.preventDefault();
                    return alert("Veuillez entrer un nom de projet");
                }
                form.action = actionUrlGroup;
            } else {
                var email = document.getElementById('email').value;
                if (!email) {
                    e.preventDefault();
                    return alert("Veuillez entrer une adresse e-mail");
                }
                form.action = actionUrlVisitor;
            }
            // Form continues to submit natively to the selected action
        });
    }

    // Reload page when returning via browser back button (bfcache) to refresh CSRF token
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        } else {
            if (form) form.reset();
        }
    });

    // Init
    var defaultMode = true;
    if (formModeInput && formModeInput.value === 'visiteur') {
        defaultMode = false;
    }
    setMode(defaultMode);
});
