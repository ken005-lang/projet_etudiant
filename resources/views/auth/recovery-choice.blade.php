<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Options de Récupération - ITES</title>
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
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .recovery-title {
            color: #000;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .recovery-text {
            color: #666;
            margin-bottom: 30px;
            font-size: 15px;
        }
        .option-box {
            border: 2px solid #eee;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        .option-box:hover {
            border-color: #ff6600;
            background: #fff9f5;
            transform: translateY(-3px);
        }
        .option-title {
            color: #ff6600;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        .option-desc {
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <h1 class="recovery-title">Récupération de Compte</h1>
            <p class="recovery-text">Choisissez comment vous souhaitez procéder pour votre groupe.</p>

            <a href="{{ route('code.modify') }}" class="option-box">
                <span class="option-title">Modifier mon Code ID</span>
                <span class="option-desc">Si vous connaissez l'ancien Code ID mais souhaitez le changer pour plus de sécurité.</span>
            </a>

            <a href="{{ route('identity.recover') }}" class="option-box">
                <span class="option-title">J'ai oublié mon Code ID</span>
                <span class="option-desc">Si vous avez perdu votre Code ID, remplissez un formulaire pour que l'administration vous aide.</span>
            </a>

            <div style="margin-top: 20px;">
                <a href="{{ route('login') }}" style="color: #666; text-decoration: none; font-size: 14px;">← Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>
