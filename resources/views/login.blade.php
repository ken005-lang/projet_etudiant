<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITES - Connexion</title>
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

                <input type="hidden" name="login_mode" id="formMode" value="{{ old('login_mode', 'groupe') }}">

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

                <form method="POST" action="{{ route('auth.login.post') }}" class="login-form" id="loginForm">
                    @csrf
                    <!-- Common Hidden Fields for the actual submission -->
                    <input type="hidden" id="loginUsername" name="login" value="">
                    
                    <!-- Group Mode Inputs (Default) -->
                    <div class="input-group-reg group-mode active">
                        <label for="access-code">Code id</label>
                        <div class="input-wrapper">
                            <input type="text" id="access-code" placeholder="">
                        </div>
                    </div>

                    <!-- Visitor Mode Inputs -->
                    <div class="input-group-reg visitor-mode" style="display: none;">
                        <label for="visitor-name">Adresse e-mail (Visiteur)</label>
                        <input type="email" id="visitor-name">

                        <label for="visitor-pass" style="margin-top: 1rem;">Mot de passe</label>
                        <input type="password" id="visitor-pass" name="password">
                    </div>

                    <button type="submit" class="login-submit-btn" aria-label="Se connecter">
                        <span class="btn-text">VALIDER</span>
                        <span class="arrow-icon">›</span>
                    </button>
                </form>
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