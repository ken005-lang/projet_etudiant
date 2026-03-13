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
          <video src="{{ asset('VIDEO/NARUTO.mp4') }}" controls muted class="video-presentation"></video>
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
        <a href="#" aria-label="Site web"><img src="{{ asset('ICON/website.svg') }}" alt=""></a>
        <a href="#" aria-label="Instagram"><img src="{{ asset('ICON/instagram.svg') }}" alt=""></a>
        <a href="#" aria-label="Facebook"><img src="{{ asset('ICON/facebook.svg') }}" alt=""></a>
        <a href="#" aria-label="TikTok"><img src="{{ asset('ICON/tiktok.svg') }}" alt=""></a>
      </div>
    </footer>
  </main>

  <script src="{{ asset('JS/index.js') }}"></script>
</body>

</html>