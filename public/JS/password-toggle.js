document.addEventListener('DOMContentLoaded', function() {
    function initPasswordToggles() {
        document.querySelectorAll('.password-toggle').forEach(btn => {
            // Remove existing listener to avoid duplicates if re-initialized
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('.toggle-icon');

                if (!input || !icon) return;

                const eyeFill = "/ICON/eye-fill.svg";
                const eyeSlashFill = "/ICON/eye-slash-fill.svg";

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.src = eyeSlashFill;
                } else {
                    input.type = 'password';
                    icon.src = eyeFill;
                }
            });
        });
    }

    initPasswordToggles();

    // Export for dynamic content if needed
    window.initPasswordToggles = initPasswordToggles;
});
