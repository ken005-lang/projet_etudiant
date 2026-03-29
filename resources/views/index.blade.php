<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- SEO Primaire -->
  <title>ITES Projets Étudiants - Vitrine de l'Innovation Technologique en Côte d'Ivoire</title>
  <meta name="description" content="Découvrez les projets innovants des étudiants de l'ITES Deux-Plateaux : applications web, mobile, IA, robotique, réseaux et télécoms. Plateforme officielle de valorisation des talents étudiants.">
  <meta name="keywords" content="ITES, projets étudiants, innovation, Côte d'Ivoire, BTS, informatique, application web, mobile, IA, robotique, télécoms, Deux-Plateaux, stage, recrutement">
  <meta name="author" content="ITES Deux-Plateaux">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  <meta name="language" content="fr">
  <meta name="revisit-after" content="7 days">
  <meta name="geo.region" content="CI">
  <meta name="geo.country" content="Côte d'Ivoire">

  <!-- Canonical -->
  <link rel="canonical" href="https://projet-etudiant-1.onrender.com/">

  <!-- Open Graph (Facebook, WhatsApp, LinkedIn) -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://projet-etudiant-1.onrender.com/">
  <meta property="og:title" content="ITES Projets Étudiants - Vitrine de l'Innovation Technologique">
  <meta property="og:description" content="Découvrez les projets innovants des étudiants de l'ITES Deux-Plateaux : applications web, mobile, IA, robotique, réseaux et télécoms.">
  <meta property="og:image" content="https://projet-etudiant-1.onrender.com/IMG/LOGOITES.png">
  <meta property="og:image:alt" content="Logo ITES Projets Étudiants">
  <meta property="og:site_name" content="ITES Projets Étudiants">
  <meta property="og:locale" content="fr_FR">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="ITES Projets Étudiants - Vitrine de l'Innovation">
  <meta name="twitter:description" content="Découvrez les projets innovants des étudiants de l'ITES Deux-Plateaux en Côte d'Ivoire.">
  <meta name="twitter:image" content="https://projet-etudiant-1.onrender.com/IMG/LOGOITES.png">

  <!-- Structured Data JSON-LD -->
  <script type="application/ld+json">
  {
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "ITES Projets Étudiants",
    "url": "https://projet-etudiant-1.onrender.com/",
    "description": "Plateforme officielle de valorisation des projets étudiants de l'Institut des Technologies et des Spécialités (ITES) Deux-Plateaux en Côte d'Ivoire.",
    "inLanguage": "fr",
    "publisher": {
      "@@type": "EducationalOrganization",
      "name": "ITES Deux-Plateaux",
      "url": "https://campusites.net/index.php",
      "address": {
        "@@type": "PostalAddress",
        "addressCountry": "CI",
        "addressLocality": "Abidjan"
      }
    },
    "potentialAction": {
      "@@type": "SearchAction",
      "target": "https://projet-etudiant-1.onrender.com/?s={search_term_string}",
      "query-input": "required name=search_term_string"
    }
  }
  </script>

  <link rel="stylesheet" href="{{ asset('style.css') }}?v={{ time() }}">
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
      @@keyframes modalFadeIn {
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
      <a href="{{ url('/login?mode=visiteur') }}" class="nav-connexion">connexion</a>
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
          <a href="{{ url('/inscription?mode=visiteur') }}" class="btn btn-hero">S'inscrire</a>
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
          <a href="{{ url('/inscription?mode=visiteur') }}" class="btn">S'inscrire</a>
        </div>
        <div class="presentation-video" id="videoWrapper">
          <video id="presentationVideo" src="{{ asset('VIDEO/Video_Presentation.mp4') }}" controls class="video-presentation"></video>
          <div class="video-overlay" id="videoOverlay">
            <img src="{{ asset('ICON/film-strip.svg') }}" alt="Play" class="play-icon">
          </div>
        </div>
      </div>
    </section>

    <section class="content-text">
      <h2>Découvrez "ITES Projet Étudiant" : La vitrine de l'innovation de l'ITES Deux-Plateaux ! <br>

L'Institut des Technologies et des Spécialités (ITES) est fier de présenter sa nouvelle plateforme officielle dédiée à l'hébergement et à la valorisation des projets de ses étudiants. </h2>
      <p>Notre mission
        <br>
Trop de projets remarquables restent invisibles après les soutenances. Avec ITES Projet Étudiant, nous voulons donner une véritable visibilité professionnelle aux réalisations de nos talents, inspirer les nouvelles promotions et renforcer la réputation d'innovation de l'ITES en Côte d'Ivoire.
        <br>
        <br>
 Quels types de projets y trouve-t-on ? <br>
La plateforme regroupe les projets de fin d'études, académiques ou personnels développés par les étudiants. Vous y découvrirez des solutions innovantes dans de multiples domaines : <br> <br>
*   Applications mobiles et web  <br>
*   Réseaux & Télécoms  <br>
*   Robotique  <br>
*   E-santé et agriculture intelligente  <br> <br>
Chaque fiche projet inclut des descriptions détaillées, les technologies utilisées, des démonstrations et les contacts de l'équipe. <br>
        <br>
 Une plateforme pensée pour tous : <br> <br>
*   Pour nos étudiants (du BTS au cycle Ingénieur) : C'est l'opportunité de mettre en valeur votre savoir-faire sur votre CV et LinkedIn pour vous démarquer sur le marché du travail. <br>   _Note importante : les étudiants conservent l'entière propriété intellectuelle et exclusive de leurs projets !. <br>
*   Pour les entreprises et recruteurs : C'est un accès direct aux talents de demain. Venez découvrir leurs compétences et contactez facilement les porteurs de projets. <br>
*   Pour les futurs étudiants : Venez voir concrètement ce que vous serez capables de réaliser en rejoignant l'ITES ! <br>
        <br>
Plongez au cœur de l'innovation étudiante et venez soutenir les créateurs de demain ! </p>
    </section>

    <footer class="footer" id="footer">
      <div class="footer-left">
        <a href="{{ url('/inscription?mode=visiteur') }}" class="btn btn-footer">S'inscrire</a>
        <span class="footer-title">EN SAVOIR PLUS SUR ITES</span>
        <div class="footer-contacts">
          <h5>Contacts</h5>

          <div class="footer-contact-item">
            <img
              src="{{ asset('ICON/whatsapp-logo-fill.svg') }}"
              alt="WhatsApp"
              class="footer-contact-icon"
            >
            <span class="footer-contact-text">+ 225 07 08 28 51 61 (Professeur) / 01 42 79 31 99 (Etudiant)</span>
          </div>

          <div class="footer-contact-item">
            <img
              src="{{ asset('ICON/paperclip-fill.svg') }}"
              alt="Email"
              class="footer-contact-icon"
            >
            <span class="footer-contact-text">nissielcape@gmail.com (Etudiant)</span>
          </div>
        </div>
      </div>
      <div class="footer-social">
        <a href="https://campusites.net/index.php" aria-label="Site web" target="_blank"><img src="{{ asset('ICON/website.svg') }}" alt=""></a>
        <a href="https://www.instagram.com/groupe_ites?igsh=MWwwa3Y1YXh1NnEzYg==" aria-label="Instagram" target="_blank"><img src="{{ asset('ICON/instagram.svg') }}" alt=""></a>
        <a href="https://www.facebook.com/share/1NKePnnVAY/" aria-label="Facebook" target="_blank"><img src="{{ asset('ICON/facebook.svg') }}" alt=""></a>
        <a href="https://www.tiktok.com/@@itesdeuxplateaux?_r=1&_t=ZS-94t7r8fgs9E" aria-label="TikTok" target="_blank"><img src="{{ asset('ICON/tiktok.svg') }}" alt=""></a>
      </div>
      <div class="footer-copyright">
        Copyright © Capé kenania 2026
      </div>
    </footer>
  </main>

  <script src="{{ asset('JS/index.js') }}"></script>
  
  <?php if(session('identity_recovery_success')): ?>
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
  <?php endif; ?>
  <script>
      // Video Overlay Player logic
      document.addEventListener('DOMContentLoaded', function() {
          const videoWrapper = document.getElementById('videoWrapper');
          const video = document.getElementById('presentationVideo');
          const overlay = document.getElementById('videoOverlay');

          if (videoWrapper && video && overlay) {
              const playVideo = () => {
                  video.play();
                  videoWrapper.classList.add('is-playing');
              };

              overlay.addEventListener('click', playVideo);
              videoWrapper.addEventListener('click', function(e) {
                  if (e.target !== video) {
                      playVideo();
                  }
              });

              // Also handle if user uses native controls
              video.addEventListener('play', () => {
                  videoWrapper.classList.add('is-playing');
              });
              
              video.addEventListener('pause', () => {
                  // Optional: show overlay again on pause? 
                  // Usually better to leave it hidden if video is already visible
                  // But if we want to show it again:
                  // videoWrapper.classList.remove('is-playing');
              });
          }
      });
  </script>
</body>

</html>