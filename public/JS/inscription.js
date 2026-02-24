document.addEventListener('DOMContentLoaded', function () {

    // Elements
    var form = document.getElementById('registrationForm');
    var btnGroupe = document.getElementById('reg-btn-groupe');
    var btnVisiteur = document.getElementById('reg-btn-visiteur');
    var groupFields = document.getElementById('group-fields');
    var visitorFields = document.getElementById('visitor-fields');
    var isGroupMode = true;

    // Toggle Mode: Groupe / Visiteur
    function setMode(group) {
        isGroupMode = group;

        btnGroupe.classList.toggle('active', group);
        btnVisiteur.classList.toggle('active', !group);

        groupFields.style.display = group ? 'flex' : 'none';
        visitorFields.style.display = group ? 'none' : 'flex';
    }

    btnGroupe.addEventListener('click', function () { setMode(true); });
    btnVisiteur.addEventListener('click', function () { setMode(false); });


    // Form Submit
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (isGroupMode) {
                var projectName = document.getElementById('project-name').value;
                if (!projectName) return alert("Veuillez entrer un nom de projet");
            } else {
                var fullname = document.getElementById('visitor-fullname').value;
                if (!fullname) return alert("Veuillez entrer votre nom");
            }

            alert("Inscription envoyée avec succès !");
        });
    }

    // Init
    setMode(true);
});
