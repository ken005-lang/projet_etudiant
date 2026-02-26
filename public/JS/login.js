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
                // Since groups use 'password' as the name dynamically in controller, we need to map the code_acces to password if we want the simplest Auth::attempt
                // But wait, the controller checks 'username' => $request->login, 'password' => $request->password. 
                // For groups, what is the password? The user requested: login with just code id for groups. So code id IS the password actually?
                // Let's set password to the same code so Auth::attempt works if both are identical, or just don't bother for groups if we set it up that way.
                // Wait! In registerGroup, we generated a Hash::make($password_groupe). There IS a password. But the design only shows one input "Code id" for group login.
                // Let's assign accessCode as BOTH login and password if there's no password field for groups, or maybe there IS a password for groups.
                // Looking at design: "Code id". If no password input exists for groups in the UI, we must submit something. We'll set a default dummy password for groups in our logic, OR we just use the code as password.
                // For now, let's just make the hidden mapped fields.
                loginUsername.value = accessCodeInput.value;

                // If there's no password input visible for groups, we inject a dummy one so the form doesn't fail HTML validation
                if (!visitorPassInput.value) visitorPassInput.value = accessCodeInput.value;
            } else {
                loginUsername.value = visitorEmailInput.value;
            }
        });
    }

    setMode(true);
});
