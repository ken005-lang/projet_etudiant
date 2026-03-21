<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mode === 'groupe' ? 'Récupération Code ID' : 'Récupération Mot de passe' }} - ITES</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('IMG/LOGOITES.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .recovery-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }
        .recovery-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .btn-submit {
            background: #ff6600;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover { background: #e55c00; }
        .error-message { color: #f44336; font-size: 13px; margin-top: 5px; }
        .success-message { color: #4CAF50; font-size: 14px; margin-bottom: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <h2 style="margin-top: 0; color: #ff6600;">Récupération de compte</h2>
            <p style="color: #666; margin-bottom: 25px;">
                {{ $mode === 'groupe' 
                    ? "Saisissez l'adresse email de votre groupe. Vous recevrez un lien pour récupérer ou modifier votre Code ID." 
                    : "Saisissez votre adresse email. Vous recevrez un code de vérification à 6 caractères pour définir un nouveau mot de passe." }}
            </p>

            @if(session('status'))
                <div class="success-message">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <input type="hidden" name="mode" value="{{ $mode }}">

                <div class="form-group">
                    <label>Adresse e-mail</label>
                    <input type="email" name="email" class="form-control" required placeholder="nom@exemple.com">
                    @error('email') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-submit">Recevoir le code</button>
            </form>

            <div style="margin-top: 20px; text-align: center;">
                <a href="{{ route('login') }}" style="color: #666; text-decoration: underline; font-size: 14px;">Retour à la connexion</a>
            </div>
        </div>
    </div>
    <script src="{{ asset('JS/global-loading.js') }}"></script>
</body>
</html>
