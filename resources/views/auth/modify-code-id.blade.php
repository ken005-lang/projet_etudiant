<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Code ID - ITES</title>
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
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper .form-control { padding-right: 40px; }
        .password-toggle { 
            position: absolute; 
            right: 12px; 
            top: 50%; 
            transform: translateY(-50%); 
            background: none; 
            border: none; 
            cursor: pointer; 
            padding: 0; 
            display: flex; 
            align-items: center; 
        }
        .password-toggle img { width: 20px; height: 20px; opacity: 0.6; transition: opacity 0.3s; }
        .password-toggle:hover img { opacity: 1; }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <h2 style="margin-top: 0; color: #ff6600;">Modifier le Code ID</h2>
            <p style="color: #666; margin-bottom: 25px;">Utilisez votre ancien Code ID pour le remplacer par un nouveau.</p>

            @if(session('error'))
                <div class="error-message" style="margin-bottom: 20px; font-size: 15px; font-weight: bold;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('code.modify.post') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Ancien Code ID</label>
                    <input type="text" name="old_code_id" class="form-control" required value="{{ old('old_code_id') }}">
                    @error('old_code_id') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Nouveau Code ID</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" id="new_password" class="form-control" required>
                        <button type="button" class="password-toggle" data-target="new_password" title="Afficher/Masquer le code">
                            <img src="{{ asset('ICON/eye-fill.svg') }}" class="toggle-icon" alt="Toggle">
                        </button>
                    </div>
                    <div id="password-policy" style="margin-top: 0.5rem; color: #666; transition: color 0.3s; font-size: 0.85rem;">
                        <p style="margin-bottom: 0.2rem; font-weight: 600;">Le Code ID doit contenir :</p>
                        <ul style="list-style-type: none; padding-left: 0; margin-top: 0;">
                            <li id="req-length" class="policy-req">Au moins 6 caractères</li>
                            <li id="req-lower" class="policy-req">Une lettre minuscule</li>
                            <li id="req-upper" class="policy-req">Une lettre majuscule</li>
                            <li id="req-number" class="policy-req">Un chiffre</li>
                        </ul>
                    </div>
                    @error('password') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Confirmer le nouveau Code ID</label>
                    <div class="input-wrapper">
                        <input type="password" name="password_confirmation" id="confirm_password" class="form-control" required>
                        <button type="button" class="password-toggle" data-target="confirm_password" title="Afficher/Masquer le code">
                            <img src="{{ asset('ICON/eye-fill.svg') }}" class="toggle-icon" alt="Toggle">
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Valider le changement</button>
            </form>

            <div style="margin-top: 20px; text-align: center;">
                <a href="{{ route('recovery.choice') }}" style="color: #666; text-decoration: underline; font-size: 14px;">Retour aux options</a>
            </div>
        </div>
    </div>

    <script src="{{ asset('JS/password-toggle.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            const reqLength = document.getElementById('req-length');
            const reqLower = document.getElementById('req-lower');
            const reqUpper = document.getElementById('req-upper');
            const reqNumber = document.getElementById('req-number');
            const policyItems = document.querySelectorAll('.policy-req');

            if (passwordInput) {
                // Initialize default colors
                policyItems.forEach(item => item.style.color = '#666');

                passwordInput.addEventListener('focus', function() { validatePasswordState(true); });
                passwordInput.addEventListener('input', function() { validatePasswordState(true); });
                passwordInput.addEventListener('blur', function() {
                    if (passwordInput.value.length === 0) {
                        policyItems.forEach(item => item.style.color = '#666');
                    }
                });

                function validatePasswordState(isFocusedOrTyping) {
                    const val = passwordInput.value;
                    updateRequirement(reqLength, val.length >= 6, isFocusedOrTyping);
                    updateRequirement(reqLower, /[a-z]/.test(val), isFocusedOrTyping);
                    updateRequirement(reqUpper, /[A-Z]/.test(val), isFocusedOrTyping);
                    updateRequirement(reqNumber, /[0-9]/.test(val), isFocusedOrTyping);
                }

                function updateRequirement(element, isValid, isFocusedOrTyping) {
                    if (!element) return;
                    if (isValid) {
                        element.style.color = '#4CAF50'; // Green success
                    } else if (isFocusedOrTyping) {
                        element.style.color = '#f44336'; // Red for error
                    } else {
                        element.style.color = '#666'; // Default grey
                    }
                }
            }
        });

        });
    </script>
</body>
</html>
