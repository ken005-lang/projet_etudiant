<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <title>ITES - Espace Visiteur</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}?v={{ time() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/js/app.js'])
</head>

<body class="visitor-page">
    <header class="header visitor-header">
        <div class="header-left">
            <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="logo-img">
        </div>

        <nav class="visitor-nav">
            <div class="nav-links">
                <a href="#" class="visitor-nav-link active" data-tab="projects">Projets</a>
                <span class="nav-divider">|</span>
                <a href="#" class="visitor-nav-link" data-tab="events">Evènements<span class="notif-dot" id="events-notif-dot"></span></a>
            </div>

            <div class="user-profile">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <div class="user-avatar">
                    <img src="{{ asset('ICON/profile_user_avatar_person_icon_192481.svg') }}" alt="User">
                </div>
            </div>

            <div class="header-divider">|</div>

            <div class="header-actions">
                <div class="header-action-btn" id="header-bell-btn">
                    <img src="{{ asset('ICON/chats-fill.svg') }}" alt="Notifications" class="header-icon" title="Messages">
                    <span class="notif-dot" id="messages-notif-dot" style="display: none;"></span>
                </div>
                <div class="header-action-btn bookmark-btn">
                    <img src="{{ asset('ICON/bookmark-simple-fill.svg') }}" alt="Bookmark" class="header-icon" title="Favoris">
                </div>
            </div>

            <div class="header-divider">|</div>

            <a href="#" id="delete-account-btn" class="logout-link" title="Supprimer mon compte">
                <img src="{{ asset('ICON/trash-fill.svg') }}" alt="Delete" class="header-logout-icon header-trash-icon">
            </a>

            <a href="{{ url('/') }}" class="logout-link">
                <!-- Icône de Retour modifiée (flèche pointant vers la gauche) -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="header-logout-icon" title="Retour à l'accueil" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 9l-3 3m0 0l3 3m-3-3h7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
        </nav>
    </header>

    <main class="visitor-main">
        <!-- Section PROJETS -->
        <section id="projects-section" class="tab-content active">
            <div class="section-title-container">
                <h1 class="section-title"># PROJETS</h1>
            </div>

            <div class="visitor-search-container">
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher un projet ou un domaine..." id="projectSearch">
                    <div class="search-icon">
                        <img src="{{ asset('ICON/research_icon.svg') }}" alt="Search">
                    </div>
                </div>
            </div>

            <!-- Container for JS rendered projects -->
            <div id="projects-list-container" class="projects-list-container">
                <!-- Projects will be injected here -->
            </div>

            <div id="projects-empty-state" class="empty-state">
                <div class="empty-icon-container">
                    <img src="{{ asset('ICON/cactus.svg') }}" alt="No result" class="empty-icon">
                </div>
                <p class="empty-text">Aucun resultat.</p>
            </div>
        </section>

        <!-- Section EVENEMENTS -->
        <section id="events-section" class="tab-content">
            <div class="section-title-container">
                <h1 class="section-title"># EVENEMENTS</h1>
            </div>

            <!-- Container for JS rendered events -->
            <div id="events-list-container" class="projects-list-container">
                <!-- Events will be injected here -->
            </div>

            <div id="events-empty-state" class="empty-state">
                <div class="empty-icon-container">
                    <img src="{{ asset('ICON/cactus.svg') }}" alt="No events" class="empty-icon">
                </div>
                <p class="empty-text">Aucun Evènement pour le moment.</p>
            </div>
        </section>

        <!-- Section FAVORIS -->
        <section id="favorites-section" class="tab-content">
            <div class="section-title-container">
                <h1 class="section-title"># FAVORIS</h1>
            </div>

            <!-- Container for JS rendered favorites -->
            <div id="favorites-list-container" class="projects-list-container">
                <!-- Favorite projects will be injected here -->
            </div>

            <div id="favorites-empty-state" class="empty-state">
                <div class="empty-icon-container">
                    <img src="{{ asset('ICON/cactus.svg') }}" alt="No favorites" class="empty-icon">
                </div>
                <p class="empty-text">Aucun favori pour le moment.</p>
            </div>
        </section>
    </main>

    <!-- Modale Messagerie Visiteur -->
    <div id="messages-overlay" class="messages-overlay">
        <div class="messages-panel visitor-theme">
            <div class="messages-header">
                <h2># MESSAGERIE</h2>
                <button class="messages-clear-btn" id="visitor-clear-messages">TOUT SUPPRIMER <span><img src="{{ asset('ICON/trash-fill.svg') }}" alt="del" style="height:14px;"></span></button>
                <button class="messages-close-btn" id="close-messages-btn">&times;</button>
            </div>
            <div class="messages-body" id="messages-list-container">
                <!-- Les messages seront injectés ici par JS -->
                <div class="section-vide">Chargement des messages...</div>
            </div>
        </div>
    </div>

    <script>
        // Inject database data into global window object for JS consumption
        window.serverGroupsData = @json($groupsData ?? []);
        window.serverEventsData = @json($eventsData ?? []);
    </script>
    <script src="{{ asset('JS/visiteur.js') }}?v={{ time() }}"></script>
</body>

</html>