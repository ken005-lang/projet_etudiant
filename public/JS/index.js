document.addEventListener('DOMContentLoaded', function () {

    /* ==========================================
       1. MOBILE MENU TOGGLE
       ========================================== */
    var menuToggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.nav');

    if (menuToggle && nav) {
        menuToggle.addEventListener('click', function () {
            nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });
    }

    /* ==========================================
       2. HERO TEXT ALTERNATION
       ========================================== */
    var heroTitle = document.getElementById('hero-title');

    if (heroTitle) {
        var texts = [
            "BIENVENUE SUR LE SITE DES PROJETS ETUDIANTS D'ITES.",
            "INSCRIVEZ-VOUS AFIN D'AVOIR TOUTES LES INFORMATIONS",
            "SUR LE CHEMINEMENT DE PROJETS AMBITIEUX."
        ];
        var currentIndex = 0;

        setInterval(function () {
            // Fade out
            heroTitle.style.opacity = '0';

            setTimeout(function () {
                // Switch text then fade in
                currentIndex = (currentIndex + 1) % texts.length;
                heroTitle.textContent = texts[currentIndex];
                heroTitle.style.opacity = '1';
            }, 500);

        }, 5000);
    }

});
