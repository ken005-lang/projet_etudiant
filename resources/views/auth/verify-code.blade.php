<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du Code - ITES</title>
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
            font-size: 16px;
        }
        .code-input {
            letter-spacing: 5px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
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
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .error-message { color: #f44336; font-size: 13px; margin-top: 5px; }

        /* Countdown Timer */
        .countdown-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 10px;
            background: #fff5eb;
            border: 1px solid #ff660033;
        }
        .countdown-icon {
            font-size: 20px;
        }
        .countdown-text {
            font-size: 14px;
            color: #555;
        }
        .countdown-time {
            font-size: 22px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            color: #ff6600;
            min-width: 50px;
            text-align: center;
        }
        .countdown-expired .countdown-time {
            color: #d32f2f;
        }
        .countdown-expired {
            background: #ffebee;
            border-color: #d32f2f33;
        }
        .countdown-expired .countdown-text {
            color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <h2 style="margin-top: 0; color: #ff6600;">Code de Vérification</h2>
            <p style="color: #666; margin-bottom: 15px;">
                Nous avons envoyé un code de vérification à 6 caractères à l'adresse <strong>{{ $email }}</strong>.<br>
                Veuillez le saisir ci-dessous pour continuer.
            </p>

            <!-- Countdown Timer -->
            <div class="countdown-container" id="countdown-container">
                <span class="countdown-icon">⏱️</span>
                <span class="countdown-text" id="countdown-label">Code valide encore</span>
                <span class="countdown-time" id="countdown-timer">--:--</span>
            </div>

            <form action="{{ route('verify.code.post') }}" method="POST" id="verify-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label>Code de vérification</label>
                    <input type="text" name="code" id="code-input" class="form-control code-input" required minlength="6" maxlength="6" pattern="[A-Za-z0-9]{6}" placeholder="Code à 6 caractères" autocomplete="off" title="Veuillez saisir les 6 caractères de votre code">
                    @error('code') <div class="error-message">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-submit" id="submit-btn">Valider le code</button>
            </form>

            <div style="margin-top: 20px; text-align: center;">
                <a href="{{ route('password.request') }}?mode=visiteur" style="color: #666; text-decoration: underline; font-size: 14px;">Je n'ai pas reçu de code</a>
            </div>
            <div style="margin-top: 10px; text-align: center;">
                <a href="{{ route('login') }}" style="color: #666; text-decoration: underline; font-size: 14px;">Retour à la connexion</a>
            </div>
        </div>
    </div>

    <script>
        (function() {
            let remaining = {{ $remainingSeconds ?? 120 }};
            const timerEl = document.getElementById('countdown-timer');
            const labelEl = document.getElementById('countdown-label');
            const containerEl = document.getElementById('countdown-container');
            const submitBtn = document.getElementById('submit-btn');
            const codeInput = document.getElementById('code-input');

            function formatTime(sec) {
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return m.toString().padStart(2, '0') + ':' + s.toString().padStart(2, '0');
            }

            function updateTimer() {
                if (remaining <= 0) {
                    timerEl.textContent = '00:00';
                    labelEl.textContent = 'Code expiré !';
                    containerEl.classList.add('countdown-expired');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Code expiré — Demandez un nouveau code';
                    codeInput.disabled = true;
                    return;
                }

                timerEl.textContent = formatTime(remaining);

                // Change color when less than 30 seconds
                if (remaining <= 30) {
                    timerEl.style.color = '#d32f2f';
                    timerEl.style.animation = 'pulse 1s infinite';
                }

                remaining--;
                setTimeout(updateTimer, 1000);
            }

            // Start immediately
            updateTimer();
        })();
    </script>

    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</body>
</html>
