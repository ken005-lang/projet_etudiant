<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ITES - Projets Étudiants</title>
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  <link rel="icon" type="image/png" href="{{ asset('IMG/LOGOITES.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
      /* Success Modal Styles */
      .modal-overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.7);
          display: none;
          justify-content: center;
          align-items: center;
          z-index: 1000;
          backdrop-filter: blur(5px);
      }
      .success-modal {
          background: white;
          padding: 2.5rem;
          border-radius: 20px;
          max-width: 500px;
          width: 90%;
          text-align: center;
          box-shadow: 0 20px 40px rgba(0,0,0,0.2);
          animation: modalFadeIn 0.3s ease-out;
      }
      @keyframes modalFadeIn {
          from { opacity: 0; transform: translateY(-20px); }
          to { opacity: 1; transform: translateY(0); }
      }
      .modal-icon {
          width: 80px;
          height: 80px;
          background: #4CAF50;
          color: white;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 40px;
          margin: 0 auto 1.5rem;
      }
      .success-modal h2 {
          color: #333;
          margin-bottom: 1rem;
          font-size: 1.5rem;
      }
      .success-modal p {
          color: #666;
          line-height: 1.6;
          margin-bottom: 2rem;
      }
      .btn-close-modal {
          background: #ff6600;
          color: white;
          border: none;
          padding: 0.8rem 2rem;
          border-radius: 8px;
          font-weight: 600;
          cursor: pointer;
          transition: 0.3s;
      }
      .btn-close-modal:hover {
          background: #e55c00;
          transform: scale(1.05);
      }
  </style>
</head>

<body>
  <header class="header">
    <a href="#" class="logo-block">
      <img src="{{ asset('IMG/ITESLOGO1.svg') }}" alt="ITES" class="logo-img" style="border-radius: 5px;">
    </a>
    <nav class="nav">
      <a href="#presentation">PRESENTATION</a>
      <a href="#footer">ITES ?</a>
      <span class="nav-sep"></span>
      <a href="{{ url('/login') }}" class="nav-connexion">connexion</a>
    </nav>
    <button class="menu-toggle" aria-label="Menu" type="button">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </header>

  <main>
    <section class="hero">
      <div class="hero-orange">
        <div class="hero-content">
          <h1 id="hero-title">BIENVENUE SUR LE SITE DES PROJETS ETUDIANTS D'ITES.</h1>
          <a href="{{ url('/inscription') }}" class="btn btn-hero">S'inscrire</a>
        </div>
      </div>
      <div class="hero-video">
        <img src="{{ asset('GIF/tech.gif') }}" alt="Play Video" class="video-icon">
      </div>
    </section>

    <section class="presentation" id="presentation">
      <h2># PRESENTATION</h2>
      <div class="presentation-grid">
        <div class="presentation-text">
          <p>APPRENEZ-EN PLUS EN REGARDANT CETTE VIDEO.</p>
          <a href="{{ url('/inscription') }}" class="btn">S'inscrire</a>
        </div>
        <div class="presentation-video">
          <video src="{{ asset('VIDEO/NARUTO.mp4') }}" controls class="video-presentation"></video>
        </div>
      </div>
    </section>

    <section class="content-text">
      <h2>XXXXXXXXX</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore
        magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
        pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est
        laborum.</p>
      <br>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
        magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
        consequat.</p>
    </section>

    <footer class="footer" id="footer">
      <div class="footer-left">
        <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="footer-logo">
        <a href="{{ url('/inscription') }}" class="btn btn-footer">S'inscrire</a>
        <span class="footer-title">EN SAVOIR PLUS SUR ITES</span>
      </div>
      <div class="footer-social">
        <a href="https://campusites.net/index.php" aria-label="Site web" target="_blank"><img src="{{ asset('ICON/website.svg') }}" alt=""></a>
        <a href="https://www.instagram.com/groupe_ites?igsh=MWwwa3Y1YXh1NnEzYg==" aria-label="Instagram" target="_blank"><img src="{{ asset('ICON/instagram.svg') }}" alt=""></a>
        <a href="https://www.facebook.com/share/1NKePnnVAY/" aria-label="Facebook" target="_blank"><img src="{{ asset('ICON/facebook.svg') }}" alt=""></a>
        <a href="https://www.tiktok.com/@itesdeuxplateaux?_r=1&_t=ZS-94t7r8fgs9E" aria-label="TikTok" target="_blank"><img src="{{ asset('ICON/tiktok.svg') }}" alt=""></a>
      </div>
      <div class="footer-copyright">
        Copyright © Capé kenania 2026
      </div>
    </footer>
  </main>

  <script src="{{ asset('JS/index.js') }}"></script>
  
  @if(session('identity_recovery_success'))
  <!-- Success Modal -->
  <div class="modal-overlay" id="successModal">
      <div class="success-modal">
          <div class="modal-icon">✓</div>
          <h2>Demande Envoyée</h2>
          <p>{{ session('identity_recovery_success') }}</p>
          <button class="btn-close-modal" onclick="closeModal()">D'ACCORD</button>
      </div>
  </div>

  <script>
      document.addEventListener('DOMContentLoaded', function() {
          const modal = document.getElementById('successModal');
          if (modal) {
              modal.style.display = 'flex';
          }
      });

      function closeModal() {
          const modal = document.getElementById('successModal');
          if (modal) {
              modal.style.opacity = '0';
              setTimeout(() => {
                  modal.style.display = 'none';
              }, 300);
          }
      }
  </script>
  @endif
</body>

</html>