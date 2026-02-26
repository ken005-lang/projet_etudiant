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

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div style="background: rgba(255,255,255,0.1); color: #ffcccc; padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: left;">
                        <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" class="registration-form" id="registrationForm" action="">
                    @csrf
                    
                    <!-- Toggle Groupe / Visiteur -->
                    <div class="toggle-container-inline">
                        <button type="button" class="toggle-btn active" id="reg-btn-groupe">GROUPE</button>
                        <button type="button" class="toggle-btn" id="reg-btn-visiteur">VISITEUR</button>
                    </div>

                    <!-- === GROUPE FIELDS === -->
                    <div id="group-fields" class="input-group-reg">
                        <label for="projet_nom">Nom du projet</label>
                        <input type="text" id="projet_nom" name="projet_nom" placeholder="Ex: Antigravity" value="{{ old('projet_nom') }}">

                        <!-- Since we missed DOMAINE in HTML, adding it quickly below based on backend requirements -->
                        <label for="domaine" style="margin-top: 1rem;">Domaine(s)</label>
                        <input type="text" id="domaine" name="domaine" placeholder="Ex: IA, Web..." value="{{ old('domaine') }}">

                        <u>
                            <h3 style="margin-top: 1rem;">Information du Chef de Groupe</h3>
                        </u>
                        <div class="form-grid">
                            <div>
                                <label for="chef_nom">Nom du Chef</label>
                                <input type="text" id="chef_nom" name="chef_nom" value="{{ old('chef_nom') }}">
                            </div>
                            <div>
                                <label for="chef_prenom">Prénom du Chef</label>
                                <input type="text" id="chef_prenom" name="chef_prenom" value="{{ old('chef_prenom') }}">
                            </div>
                            <div>
                                <label for="niveau">Niveau</label>
                                <select id="niveau" name="niveau">
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
                                <label for="filiere">Filière</label>
                                <select id="filiere" name="filiere">
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
                            <label for="code_acces">Code d'accès (Sera votre identifiant)</label>
                            <input type="password" id="code_acces" name="code_acces">
                        </div>
                    </div>

                    <!-- === VISITEUR FIELDS === -->
                    <div id="visitor-fields" class="input-group-reg" style="display:none;">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Votre nom" value="{{ old('nom') }}">
                        
                        <label for="prenom" style="margin-top: 1rem;">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" value="{{ old('prenom') }}">

                        <div style="margin-top: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; color: white;">Genre</label>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <label style="font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: white;">
                                    <input type="radio" name="genre" value="homme" {{ old('genre') == 'homme' ? 'checked' : '' }}> Homme
                                </label>
                                <label style="font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: white;">
                                    <input type="radio" name="genre" value="femme" {{ old('genre') == 'femme' ? 'checked' : '' }}> Femme
                                </label>
                            </div>
                        </div>

                        <label for="email" style="margin-top: 1rem;">Email</label>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" value="{{ old('email') }}">

                        <u>
                            <h3 style="margin-top: 1rem;">Accès &amp; Sécurité</h3>
                        </u>
                        <label for="password">Créer un mot de passe</label>
                        <input type="password" id="password" name="password">

                        <label for="password_confirmation" style="margin-top: 1rem;">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation">
                    </div>

                    <!-- Hidden field to control Action URL via JS -->
                    <input type="hidden" id="actionUrlGroup" value="{{ route('auth.register.group') }}">
                    <input type="hidden" id="actionUrlVisitor" value="{{ route('auth.register.visitor') }}">

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