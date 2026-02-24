document.addEventListener('DOMContentLoaded', () => {
    const btnGroupe = document.getElementById('btn-groupe');
    const btnVisiteur = document.getElementById('btn-visiteur');
    const groupInputs = document.querySelector('.group-mode');
    const visitorInputs = document.querySelector('.visitor-mode');
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

            // Explicitly manage display if class toggling isn't sufficient or for consistency
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
    }

    btnGroupe.addEventListener('click', () => setMode(true));
    btnVisiteur.addEventListener('click', () => setMode(false));

    // Initialize
    setMode(true);
});
