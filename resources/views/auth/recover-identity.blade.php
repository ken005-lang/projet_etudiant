<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récupération Administrative - ITES</title>
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
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <h2 style="margin-top: 0;">Récupération d'Identité</h2>
            <p style="color: #666; margin-bottom: 25px;">Remplissez ces informations. L'administration vérifiera votre identité et vous renverra votre Code ID.</p>

            <form action="{{ route('identity.recover.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nom du groupe</label>
                    <input type="text" name="group_name" class="form-control" required placeholder="Ex: Groupe Tech 2025" value="{{ old('group_name') }}">
                </div>
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Nom du chef</label>
                        <input type="text" name="chef_nom" class="form-control" required value="{{ old('chef_nom') }}">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Prénom du chef</label>
                        <input type="text" name="chef_prenom" class="form-control" required value="{{ old('chef_prenom') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Filière</label>
                    <input type="text" name="filiere" class="form-control" required value="{{ old('filiere') }}">
                </div>
                <div class="form-group">
                    <label>Niveau</label>
                    <input type="text" name="niveau" class="form-control" required value="{{ old('niveau') }}">
                </div>
                <div class="form-group">
                    <label>Votre email</label>
                    <input type="email" name="email" class="form-control" required placeholder="nom@exemple.com" value="{{ old('email') }}">
                    @error('email') <div style="color: red; font-size: 13px; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-submit">Envoyer la demande à l'admin</button>
            </form>
        </div>
    </div>
    <script src="{{ asset('JS/global-loading.js') }}"></script>
</body>
</html>
