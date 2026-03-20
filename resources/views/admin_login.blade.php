<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITES - Admin Login</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('IMG/LOGOITES.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="admin-login-body">
    <header class="admin-header">
        <div class="header-left">
            <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="header-logo" style="border-radius: 5px;">
        </div>
        <div class="header-right">
            <span class="admin-text">Admin</span>
        </div>
    </header>

    <main class="admin-login-main">
        <div class="admin-login-card">
            <form class="admin-login-form" method="POST" action="{{ route('admin.login.post') }}">
                @csrf
                @if ($errors->any())
                    <div style="background-color: #ffcccc; color: #cc0000; padding: 10px; border-radius: 4px; font-weight: 600; text-align: center;">
                        @foreach ($errors->all() as $error)
                            <p style="margin: 0; font-size: 0.95rem;">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="admin-input-group">
                    <label for="username">Utilisateur</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}">
                </div>
                <div class="admin-input-group">
                    <label for="password">Mot de passe</label>
                    <div class="admin-password-wrapper">
                        <input type="password" id="password" name="password">
                        <button type="button" class="admin-password-toggle" id="togglePassword">
                            <img src="{{ asset('ICON/eye-fill.svg') }}" alt="Voir" id="toggleIcon">
                        </button>
                    </div>
                </div>
                <div class="admin-form-footer">
                    <button type="submit" class="admin-submit-btn">Connexion</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.src = "{{ asset('ICON/eye-slash-fill.svg') }}";
            } else {
                passwordInput.type = 'password';
                toggleIcon.src = "{{ asset('ICON/eye-fill.svg') }}";
            }
        });
    </script>
</body>

</html>