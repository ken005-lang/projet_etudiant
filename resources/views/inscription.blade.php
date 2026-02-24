<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITES - Inscription</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="login-page">
    <header class="header login-header">
        <a href="{{ url('/') }}" class="logo-block">
            <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="logo-img">
        </a>
        <a href="{{ url('/login') }}" class="top-nav-link">Retour à la connexion</a>
    </header>

    <main class="login-main">
        <div class="login-container">

            <div class="login-card registration-card">
                <h1 class="login-title">INSCRIPTION</h1>

                <form class="registration-form" id="registrationForm">

                    <!-- Toggle Groupe / Visiteur -->
                    <div class="toggle-container-inline">
                        <button type="button" class="toggle-btn active" id="reg-btn-groupe">GROUPE</button>
                        <button type="button" class="toggle-btn" id="reg-btn-visiteur">VISITEUR</button>
                    </div>

                    <!-- === GROUPE FIELDS === -->
                    <div id="group-fields" class="input-group-reg">
                        <label for="project-name">Nom du projet</label>
                        <input type="text" id="project-name" name="project-name" placeholder="Ex: Antigravity">
                        <u>
                            <h3>Information du Chef de Groupe</h3>
                        </u>
                        <div class="form-grid">
                            <div>
                                <label for="leader-name">Nom et Prénom</label>
                                <input type="text" id="leader-name" name="leader-name">
                            </div>
                            <div>
                                <label for="leader-niveau">Niveau</label>
                                <select id="leader-niveau" name="leader-niveau">
                                    <option value="" disabled selected>Choisir un niveau</option>
                                    <option value="l1">L 1</option>
                                    <option value="l2">L 2</option>
                                    <option value="l3">L 3</option>
                                    <option value="m1">M 1</option>
                                    <option value="m2">M 2</option>
                                    <option value="m3">M 3</option>
                                </select>
                            </div>
                            <div>
                                <label for="leader-filiere">Filière</label>
                                <select id="leader-filiere" name="leader-filiere">
                                    <option value="" disabled selected>Choisir une filière</option>
                                    <option value="info">INFO</option>
                                    <option value="elt">ELT</option>
                                    <option value="meca">MECA</option>
                                </select>
                            </div>
                        </div>


                        <div style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 1rem;">
                            <img src="{{ asset('ICON/info_icon.svg') }}" alt="Info" class="info-icon"
                                title="Demander le code d'accès à l'admin du site">
                            <label for="group-password">Code id</label>
                            <input type="password" id="group-password" name="group-password">
                        </div>
                    </div>

                    <!-- === VISITEUR FIELDS === -->
                    <div id="visitor-fields" class="input-group-reg" style="display:none;">
                        <label for="visitor-fullname">Nom et Prénom</label>
                        <input type="text" id="visitor-fullname" name="visitor-fullname"
                            placeholder="Votre nom complet">

                        <label for="visitor-email">Email</label>
                        <input type="email" id="visitor-email" name="visitor-email" placeholder="votre@email.com">

                        <u>
                            <h3>Accès &amp; Sécurité</h3>
                        </u>
                        <label for="visitor-password">Créer un mot de passe</label>
                        <input type="password" id="visitor-password" name="visitor-password">

                        <label for="visitor-password-confirm">Confirmer le mot de passe</label>
                        <input type="password" id="visitor-password-confirm" name="visitor-password-confirm">
                    </div>

                    <!-- Submit -->
                    <div class="nav-buttons">
                        <button type="submit" class="btn-nav submit" id="submitBtn">S'inscrire</button>
                    </div>
                </form>
            </div>

            <div class="login-decoration">
                <img src="{{ asset('IMG/PROJET_ETUDIANT.png') }}" alt="PROJET ETUDIANT" class="student-project-img">
            </div>

        </div>

        <div class="login-footer"></div>
    </main>

    <script src="{{ asset('JS/inscription.js') }}"></script>
</body>

</html>