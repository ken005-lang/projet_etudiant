<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITES - Admin Login</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="admin-login-body">
    <header class="admin-header">
        <div class="header-left">
            <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="header-logo">
        </div>
        <div class="header-right">
            <span class="admin-text">Admin</span>
        </div>
    </header>

    <main class="admin-login-main">
        <div class="admin-login-card">
            <form class="admin-login-form">
                <div class="admin-input-group">
                    <label for="username">Utilisateur</label>
                    <input type="text" id="username" name="username">
                </div>
                <div class="admin-input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="admin-form-footer">
                    <button type="submit" class="admin-submit-btn">Connexion</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>