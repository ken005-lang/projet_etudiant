<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITES - Connexion</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('IMG/LOGOITES.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="login-page">
    <header class="header login-header">
        <a href="{{ url('/') }}" class="logo-block">
            <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="logo-img" style="border-radius: 5px;">
        </a>
        <a href="{{ url('/inscription') }}" class="top-nav-link">INSCRIPTION</a>
    </header>

    <main class="login-main">
        <div class="login-container">

            <div class="login-card">
                <h1 class="login-title">CONNEXION</h1>

                <!-- Toggle inside card for consistency with registration -->
                <div class="toggle-container-inline">
                    <button type="button" class="toggle-btn active" id="btn-groupe">GROUPE</button>
                    <!-- Separator removed for modern look -->
                    <button type="button" class="toggle-btn" id="btn-visiteur">VISITEUR</button>
                </div>

                <!-- Validation Errors (Global list removed for cleaner UI, inline errors below are used instead) -->

                <form method="POST" action="{{ route('auth.login.post') }}" class="login-form" id="loginForm">
                    @csrf
                    <!-- Hidden field to track active tab (must be inside form to submit) -->
                    <input type="hidden" name="login_mode" id="formMode" value="{{ old('login_mode', 'groupe') }}">
                    
                    <!-- Common Hidden Fields for the actual submission -->
                    <input type="hidden" id="loginUsername" name="login" value="{{ old('login') }}">
                    
                    <!-- Group Mode Inputs (Default) -->
                    <div class="input-group-reg group-mode active">
                        <label for="access-code">Code id</label>
                        <div class="input-wrapper" style="position: relative; display: flex; align-items: center;">
                            <input type="password" id="access-code" value="{{ old('login_mode') === 'groupe' || !old('login_mode') ? old('login') : '' }}" class="@if((old('login_mode') === 'groupe' || !old('login_mode')) && $errors->has('login')) error-highlight @endif" style="@if((old('login_mode') === 'groupe' || !old('login_mode')) && $errors->has('login')) border: 1px solid #ffcccc; @endif">
                            <button type="button" class="password-toggle" data-target="access-code" title="Afficher/Masquer le code">
                                <img src="{{ asset('ICON/eye-fill.svg') }}" alt="Toggle Visibility" class="toggle-icon">
                            </button>
                        </div>
                        @if((old('login_mode') === 'groupe' || !old('login_mode')) && $errors->has('login'))
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $errors->first('login') }}</div>
                        @endif
                    </div>

                    <!-- Visitor Mode Inputs -->
                    <div class="input-group-reg visitor-mode" style="display: none;">
                        <label for="visitor-name">Adresse e-mail</label>
                        <input type="email" id="visitor-name" name="visitor_email" value="{{ old('login_mode') === 'visiteur' ? old('login') : '' }}" class="@if(old('login_mode') === 'visiteur' && $errors->has('login') && $errors->first('login') !== 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.') error-highlight @endif" style="@if(old('login_mode') === 'visiteur' && $errors->has('login') && $errors->first('login') !== 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.') border: 1px solid #ffcccc; @endif">
                        @if(old('login_mode') === 'visiteur' && $errors->has('login') && $errors->first('login') !== 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.')
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $errors->first('login') }}</div>
                        @endif

                        <label for="visitor-pass" style="margin-top: 1rem;">Mot de passe</label>
                        <div class="input-wrapper" style="position: relative; display: flex; align-items: center;">
                            <input type="password" id="visitor-pass" name="password" class="@if(old('login_mode') === 'visiteur' && ($errors->has('password') || $errors->has('login'))) error-highlight @endif" style="@if(old('login_mode') === 'visiteur' && ($errors->has('password') || $errors->has('login'))) border: 1px solid #ffcccc; @endif">
                            <button type="button" class="password-toggle" data-target="visitor-pass" title="Afficher/Masquer le mot de passe">
                                <img src="{{ asset('ICON/eye-fill.svg') }}" alt="Toggle Visibility" class="toggle-icon">
                            </button>
                        </div>
                        @if(old('login_mode') === 'visiteur' && $errors->has('password'))
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $errors->first('password') }}</div>
                        @elseif(old('login_mode') === 'visiteur' && $errors->has('login') && $errors->first('login') === 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.')
                            <!-- Global auth mismatch error (displayed under password for visitors as requested) -->
                            <div class="validation-error-message" style="color: #ffcccc; font-size: 0.85rem; margin-top: 0.25rem;">{{ $errors->first('login') }}</div>
                        @endif
                    </div>

                    <button type="submit" class="login-submit-btn" aria-label="Se connecter">
                        <span class="btn-text">VALIDER</span>
                        <span class="arrow-icon">›</span>
                    </button>
                </form>

                <div class="forgot-container">
        <a href="{{ route('recovery.choice') }}" class="forgot-link group-mode active" id="forgot-group">Code id oublié.</a>
        <a href="{{ route('password.request', ['mode' => 'visiteur']) }}" class="forgot-link visitor-mode" id="forgot-visitor" style="display: none;">Mot de passe oublié.</a>
    </div>
            </div>

            <div class="login-decoration">
                <img src="{{ asset('IMG/PROJET_ETUDIANT.png') }}" alt="PROJET ETUDIANT" class="student-project-img">
            </div>

        </div>

        <div class="login-footer"></div>
    </main>

    <script src="{{ asset('JS/login.js') }}"></script>
</body>


</html>