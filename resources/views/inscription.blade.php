<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITES - Inscription</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('IMG/LOGOITES.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="login-page">
    <header class="header login-header">
        <a href="{{ url('/') }}" class="logo-block">
            <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="logo-img" style="border-radius: 5px;">
        </a>
        <a href="{{ url('/login') }}" class="top-nav-link">connexion</a>
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

                <form method="POST" class="registration-form" id="registrationForm" action="{{ route('auth.register.group') }}">
                    @csrf
                    
                    <!-- Toggle Groupe / Visiteur -->
                    <div class="toggle-container-inline">
                        <button type="button" class="toggle-btn active" id="reg-btn-groupe">GROUPE</button>
                        <button type="button" class="toggle-btn" id="reg-btn-visiteur">VISITEUR</button>
                    </div>

                    <!-- Hidden field to remember the active tab after validation failure -->
                    <input type="hidden" name="registration_mode" id="formMode" value="{{ old('registration_mode', 'groupe') }}">

                    <!-- === GROUPE FIELDS === -->
                    <div id="group-fields" class="input-group-reg">
                        <label for="projet_nom">Nom du projet</label>
                        <input type="text" id="projet_nom" name="projet_nom" placeholder="Ex: Solaris" value="{{ old('projet_nom') }}" class="@error('projet_nom') error-highlight @enderror" style="@error('projet_nom') border: 1px solid #ffcccc; @enderror">
                        @error('projet_nom')
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror

                        <!-- Since we missed DOMAINE in HTML, adding it quickly below based on backend requirements -->
                        <label for="domaine" style="margin-top: 1rem;">Domaine(s)</label>
                        <input type="text" id="domaine" name="domaine" placeholder="Ex: IA, Web..." value="{{ old('domaine') }}" class="@error('domaine') error-highlight @enderror" style="@error('domaine') border: 1px solid #ffcccc; @enderror">
                        @error('domaine')
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror

                        <u style="color: white;">
                            <h3 style="margin-top: 1rem;">Information du Chef de Groupe</h3>
                        </u>
                        <div class="form-grid">
                            <div>
                                <label for="chef_nom">Nom du Chef</label>
                                <input type="text" id="chef_nom" placeholder="Ex: Diallo" name="chef_nom" value="{{ old('chef_nom') }}" class="@error('chef_nom') error-highlight @enderror" style="@error('chef_nom') border: 1px solid #ffcccc; @enderror; width: 100%;">
                                @error('chef_nom')
                                    <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="chef_prenom">Prénom du Chef</label>
                                <input type="text" id="chef_prenom" placeholder="Ex: Tierno" name="chef_prenom" value="{{ old('chef_prenom') }}" class="@error('chef_prenom') error-highlight @enderror" style="@error('chef_prenom') border: 1px solid #ffcccc; @enderror; width: 100%;">
                                @error('chef_prenom')
                                    <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="niveau">Niveau</label>
                                <select id="niveau" name="niveau" class="@error('niveau') error-highlight @enderror" style="@error('niveau') border: 1px solid #ffcccc; @enderror; width: 100%;">
                                    <option value="" disabled {{ old('niveau') ? '' : 'selected' }}>Choisir un niveau</option>
                                    <option value="licence 1" {{ old('niveau') == 'licence 1' ? 'selected' : '' }}>licence 1</option>
                                    <option value="licence 2" {{ old('niveau') == 'licence 2' ? 'selected' : '' }}>licence 2</option>
                                    <option value="licence 3" {{ old('niveau') == 'licence 3' ? 'selected' : '' }}>licence 3</option>
                                    <option value="master 1" {{ old('niveau') == 'master 1' ? 'selected' : '' }}>master 1</option>
                                    <option value="master 2" {{ old('niveau') == 'master 2' ? 'selected' : '' }}>master 2</option>
                                    <option value="master 3" {{ old('niveau') == 'master 3' ? 'selected' : '' }}>master 3</option>
                                </select>
                                @error('niveau')
                                    <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="filiere">Filière</label>
                                <select id="filiere" name="filiere" class="@error('filiere') error-highlight @enderror" style="@error('filiere') border: 1px solid #ffcccc; @enderror; width: 100%;">
                                    <option value="" disabled {{ old('filiere') ? '' : 'selected' }}>Choisir une filière</option>
                                    <option value="info" {{ old('filiere') == 'info' ? 'selected' : '' }}>INFO</option>
                                    <option value="elt" {{ old('filiere') == 'elt' ? 'selected' : '' }}>ELT</option>
                                    <option value="meca" {{ old('filiere') == 'meca' ? 'selected' : '' }}>MECA</option>
                                </select>
                                @error('filiere')
                                    <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 1rem;">
                            <img src="{{ asset('ICON/info_icon.svg') }}" alt="Info" class="info-icon"
                                title="Demander le code d'accès à l'admin du site">
                            <label for="code_acces">Code d'accès (Sera votre identifiant)</label>
                            <input type="password" id="code_acces" name="code_acces" class="@error('code_acces') error-highlight @enderror" style="@error('code_acces') border: 1px solid #ffcccc; @enderror">
                            @error('code_acces')
                                <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- === VISITEUR FIELDS === -->
                    <div id="visitor-fields" class="input-group-reg" style="display:none;">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Votre nom" value="{{ old('nom') }}" class="@error('nom') error-highlight @enderror" style="@error('nom') border: 1px solid #ffcccc; @enderror">
                        @error('nom')
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror

                        <label for="prenom" style="margin-top: 1rem;">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" value="{{ old('prenom') }}" class="@error('prenom') error-highlight @enderror" style="@error('prenom') border: 1px solid #ffcccc; @enderror">
                        @error('prenom')
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror

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
                            @error('genre')
                                <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <label for="email" style="margin-top: 1rem;">Email</label>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" value="{{ old('email') }}" class="@error('email') error-highlight @enderror" style="@error('email') border: 1px solid #ffcccc; @enderror">
                        @error('email')
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror

                        <u style="color: white;">
                            <h3 style="margin-top: 1rem;">Accès &amp; Sécurité</h3>
                        </u>
                        <label for="password">Créer un mot de passe</label>
                        <input type="password" id="password" name="password" class="@error('password') error-highlight @enderror" style="@error('password') border: 1px solid #ffcccc; @enderror">
                        
                        <!-- Dynamic Password Policy Checklist -->
                        <div id="password-policy" style="margin-top: 0.5rem; color: white; transition: color 0.3s; font-size: 0.85rem;">
                            <p style="margin-bottom: 0.2rem; font-weight: 600;">Le mot de passe doit contenir :</p>
                            <ul style="list-style-type: none; padding-left: 0; margin-top: 0;">
                                <li id="req-length" class="policy-req">Au moins 6 caractères</li>
                                <li id="req-lower" class="policy-req">Une lettre minuscule</li>
                                <li id="req-upper" class="policy-req">Une lettre majuscule</li>
                                <li id="req-number" class="policy-req">Un chiffre</li>
                            </ul>
                        </div>

                        @error('password')
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scrolling directly to the error message or field
            @if ($errors->any())
                // Target the specific inline error message if it exists
                let errorTarget = document.querySelector('.validation-error-message');
                
                // If no specific inline error, fallback to the first field with an error class or the form itself
                if (!errorTarget) {
                    errorTarget = document.querySelector('.error-highlight') || document.getElementById('registrationForm');
                }

                if (errorTarget) {
                    setTimeout(() => {
                        // Scroll slightly above the element so it's not glued to the top edge
                        const y = errorTarget.getBoundingClientRect().top + window.scrollY - 150;
                        window.scrollTo({ top: y, behavior: 'smooth' });
                        
                        // Highlight effect to draw attention
                        if(errorTarget.classList.contains('validation-error-message')) {
                            // Flash the error message twice
                            let opacity = 1;
                            let count = 0;
                            const interval = setInterval(() => {
                                opacity = opacity === 1 ? 0.4 : 1;
                                errorTarget.style.opacity = opacity;
                                count++;
                                if(count >= 4) {
                                    clearInterval(interval);
                                    errorTarget.style.opacity = 1;
                                }
                            }, 200);
                        }
                    }, 100);
                }
            @endif

            // Dynamic Password Policy Validation
            const passwordInput = document.getElementById('password');
            const reqLength = document.getElementById('req-length');
            const reqLower = document.getElementById('req-lower');
            const reqUpper = document.getElementById('req-upper');
            const reqNumber = document.getElementById('req-number');
            const policyItems = document.querySelectorAll('.policy-req');

            if (passwordInput) {
                // When user clicks/focuses on the password field, unfulfilled conditions turn red
                passwordInput.addEventListener('focus', function() {
                    validatePasswordState(true);
                });

                // Validate in real-time as they type
                passwordInput.addEventListener('input', function() {
                    validatePasswordState(true);
                });

                // (Optional) Revert completely empty field to white when losing focus to prevent aggressive red
                passwordInput.addEventListener('blur', function() {
                    if (passwordInput.value.length === 0) {
                        policyItems.forEach(item => {
                            item.style.color = 'white';
                        });
                    }
                });

                function validatePasswordState(isFocusedOrTyping) {
                    const val = passwordInput.value;

                    // Length >= 6
                    updateRequirement(reqLength, val.length >= 6, isFocusedOrTyping);
                    
                    // Lowercase
                    updateRequirement(reqLower, /[a-z]/.test(val), isFocusedOrTyping);
                    
                    // Uppercase
                    updateRequirement(reqUpper, /[A-Z]/.test(val), isFocusedOrTyping);
                    
                    // Number
                    updateRequirement(reqNumber, /[0-9]/.test(val), isFocusedOrTyping);
                }

                function updateRequirement(element, isValid, isFocusedOrTyping) {
                    if (isValid) {
                        element.style.color = '#4ade80'; // Green success
                    } else if (isFocusedOrTyping) {
                        element.style.color = '#8b0000'; // Dark red for better contrast
                    } else {
                        element.style.color = 'white'; // Default
                    }
                }
            }
        });
    </script>
</body>

</html>