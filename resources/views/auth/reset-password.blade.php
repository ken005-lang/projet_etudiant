<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mode === 'groupe' ? 'Modifier Code ID' : 'Nouveau Mot de Passe' }} - ITES</title>
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
            background: #000;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover { opacity: 0.8; }
        .error-message { color: #f44336; font-size: 13px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <h2 style="margin-top: 0;">{{ $mode === 'groupe' ? 'Modification du Code ID' : 'Nouveau Mot de Passe' }}</h2>
            <p style="color: #666; margin-bottom: 25px;">
                {{ $mode === 'groupe' 
                    ? 'Définissez un nouveau Code ID pour votre groupe.' 
                    : 'Définissez un nouveau mot de passe sécurisé.' }}
            </p>

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label>Email du compte</label>
                    <div style="padding: 12px; background: #f9f9f9; border-radius: 8px; font-size: 14px; color: #555;">
                        {{ $email }}
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ $mode === 'groupe' ? 'Nouveau Code ID' : 'Nouveau Mot de Passe' }}</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" name="password" id="password" class="form-control" required style="padding-right: 45px;">
                        <button type="button" class="password-toggle" data-target="password" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; display: flex; align-items: center; opacity: 0.6;">
                            <img src="{{ asset('ICON/eye-fill.svg') }}" alt="Voir" class="toggle-icon" style="width: 20px;">
                        </button>
                    </div>
                    @error('password') <div class="error-message">{{ $message }}</div> @enderror
                    
                    <!-- Dynamic Password Policy Checklist -->
                    <div id="password-policy" style="margin-top: 5px; color: #666; transition: color 0.3s; font-size: 0.85rem;">
                        <p style="margin-bottom: 2px; font-weight: 600;">Le mot de passe doit contenir :</p>
                        <ul style="list-style-type: none; padding-left: 0; margin-top: 0;">
                            <li id="req-length" class="policy-req">Au moins 6 caractères</li>
                            <li id="req-lower" class="policy-req">Une lettre minuscule</li>
                            <li id="req-upper" class="policy-req">Une lettre majuscule</li>
                            <li id="req-number" class="policy-req">Un chiffre</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>Confirmation</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required style="padding-right: 45px;">
                        <button type="button" class="password-toggle" data-target="password_confirmation" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; display: flex; align-items: center; opacity: 0.6;">
                            <img src="{{ asset('ICON/eye-fill.svg') }}" alt="Voir" class="toggle-icon" style="width: 20px;">
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="margin-top: 10px;">Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('.toggle-icon');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.src = "{{ asset('ICON/eye-slash-fill.svg') }}";
                } else {
                    input.type = 'password';
                    icon.src = "{{ asset('ICON/eye-fill.svg') }}";
                }
            });
        });

        // Dynamic Password Policy Validation
        const passwordInput = document.getElementById('password');
        const reqLength = document.getElementById('req-length');
        const reqLower = document.getElementById('req-lower');
        const reqUpper = document.getElementById('req-upper');
        const reqNumber = document.getElementById('req-number');
        const policyItems = document.querySelectorAll('.policy-req');

        if (passwordInput) {
            passwordInput.addEventListener('focus', function() {
                validatePasswordState(true);
            });

            passwordInput.addEventListener('input', function() {
                validatePasswordState(true);
            });

            passwordInput.addEventListener('blur', function() {
                if (passwordInput.value.length === 0) {
                    policyItems.forEach(item => {
                        item.style.color = '#666';
                    });
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
                if (isValid) {
                    element.style.color = '#2e7d32'; // Green success
                } else if (isFocusedOrTyping) {
                    element.style.color = '#d32f2f'; // Dark red error
                } else {
                    element.style.color = '#666'; // Default
                }
            }
        }
    </script>
</body>
</html>
