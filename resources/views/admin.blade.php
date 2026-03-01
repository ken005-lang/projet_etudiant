<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ITES - Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="admin-dashboard-page">
    <header class="admin-dashboard-header">
        <div class="header-left">
            <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="ITES" class="header-logo">
        </div>
        <div class="header-right">
            <span class="admin-text">Admin</span>
        </div>
    </header>

    <div class="admin-dashboard-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-sticky-wrapper">
                <div class="admin-sidebar-top">
                    <div class="admin-avatar-box">
                        <img src="ICON/user-gear.svg" alt="Admin" class="admin-avatar-icon">
                    </div>
                </div>

                <nav class="admin-nav">
                    <button class="admin-nav-item active" data-tab="access">CODE D'ACCES</button>
                    <div class="admin-nav-sep"></div>
                    <button class="admin-nav-item" data-tab="groups">GROUPES</button>
                    <div class="admin-nav-sep"></div>
                    <button class="admin-nav-item" data-tab="visitors">VISITEURS</button>
                    <div class="admin-nav-sep"></div>
                    <button class="admin-nav-item" data-tab="events">EVENEMENTS</button>
                </nav>

                <button class="admin-logout-btn">
                    <span>Déconnexion</span>
                    <img src="ICON/logout_icon.svg" alt="Logout" class="logout-icon">
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-content-area">

            <!-- Pane 1: CODE D'ACCES -->
            <section class="admin-pane active" id="access-pane">
                <h1 class="pane-title">CODE D'ACCES (id)</h1>
                <p class="pane-subtitle">Groupe sans code d'accès ({{ $accessCodes->count() }})</p>

                <div class="code-creation-box">
                    <input type="text" placeholder="CODE ID" class="code-id-input" id="code-input">
                    <button class="btn-pill-wide" id="create-code-btn">
                        Créer un code id
                        <img src="ICON/key.svg" alt="Key" class="btn-icon-key">
                    </button>
                </div>

                <div class="inactive-codes-section">
                    <h3>Code id créer ( inactif )</h3>
                    <div class="code-list-container" id="code-list">
                        <div class="code-list-header">Code id</div>
                        @foreach ($accessCodes as $code)
                        <div class="code-item-row" data-id="{{ $code->id }}">
                            <span class="code-list-item">{{ $code->code }}</span>
                            <img src="ICON/trash-fill.svg" alt="delete" class="delete-code-icon">
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- Pane 2: GROUPES -->
            <section class="admin-pane" id="groups-pane">
                <h1 class="pane-title">CODE D'ACCES</h1>
                <p class="pane-subtitle">Groupe avec code d'accès ({{ $groupProfiles->count() }})</p>

                <div class="table-actions-bar">
                    <div class="search-pill">
                        <input type="text" placeholder="">
                        <button class="btn-pill-small">Filtrer</button>
                    </div>
                    <button class="btn-pill-small">Rafraîchir</button>
                </div>

                <div class="admin-table-wrapper">
                    <table class="admin-data-table groups-table">
                        <thead>
                            <tr class="main-headers">
                                <th colspan="2">Groupe</th>
                                <th colspan="3">Chef de groupe</th>
                                <th>Compte</th>
                                <th>Session</th>
                            </tr>
                            <tr class="sub-headers">
                                <th>Nom</th>
                                <th>Code id</th>
                                <th>Nom et prénom</th>
                                <th>Niveau</th>
                                <th>Filière</th>
                                <th>Date de création</th>
                                <th>Connexion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groupProfiles as $groupe)
                            <tr>
                                <td>{{ $groupe->project_name }}</td>
                                <td>{{ $groupe->accessCode ? $groupe->accessCode->code : 'N/A' }}</td>
                                <td>{{ $groupe->leader_name }}</td>
                                <td>{{ $groupe->leader_level }}</td>
                                <td>{{ $groupe->leader_sector }}</td>
                                <td>{{ $groupe->created_at->format('d/m/Y') }}</td>
                                <td>Inactif</td>
                                <td><img src="ICON/trash-fill.svg" alt="delete" class="action-icon" data-id="{{ $groupe->id }}"></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" style="text-align: center;">Aucun groupe inscrit pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Pane 3: VISITEURS -->
            <section class="admin-pane" id="visitors-pane">
                <h1 class="pane-title">VISITEURS</h1>
                <p class="pane-subtitle">Visiteurs ({{ $visitorProfiles->count() }})</p>

                <div class="admin-table-wrapper">
                    <table class="admin-data-table visitors-table">
                        <thead>
                            <tr>
                                <th>Nom et prénom</th>
                                <th>Genre</th>
                                <th>E-mail</th>
                                <th>Date de création du compte</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($visitorProfiles as $visitor)
                            <tr>
                                <td>{{ $visitor->user->name ?? 'Utilisateur inconnu' }}</td>
                                <td style="text-transform: capitalize;">{{ $visitor->gender }}</td>
                                <td>{{ $visitor->user->username ?? 'N/A' }}</td>
                                <td>{{ $visitor->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align: center;">Aucun visiteur inscrit pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Pane 4: EVENEMENTS -->
            <section class="admin-pane" id="events-pane">
                <h1 class="pane-title">EVENEMENTS</h1>

                <div class="add-event-box">
                    <input type="text" placeholder="Nom de l'évènement et date" class="event-input"
                        id="admin-event-input">
                    <button class="btn-pill-small" id="add-event-btn">Ajouter</button>
                </div>

                <div class="events-list" id="admin-events-list">
                    @forelse ($events as $event)
                    <div class="event-accordion-item" data-id="{{ $event->id }}">
                        <div class="event-header">
                            <input type="text" value="{{ $event->title }}" class="event-title-edit">
                            <div class="header-buttons">
                                <button class="btn-pill-small white-btn toggle-publish-btn">Valider</button>
                                <img src="ICON/arrow-down_icon.svg" alt="expand" class="expand-arrow">
                                <img src="ICON/trash-fill.svg" alt="delete" class="action-icon">
                            </div>
                        </div>
                        <div class="event-content">
                            <div class="event-grid">
                                <div class="media-upload-placeholder image-place" style="{{ $event->image_path ? 'background-image: url(\''.asset($event->image_path).'\') !important; background-size: contain; background-repeat: no-repeat; background-position: center; border: 2px solid #fff; background-color: transparent;' : '' }}">
                                    @if(!$event->image_path)
                                        <p>upload image</p>
                                    @endif
                                    <input type="file" accept="image/*" class="hidden-file-input image-input" style="display: none;">
                                </div>
                                <div class="event-text-multimedia">
                                    <div class="textarea-container">
                                        <textarea class="event-desc-edit" placeholder="Description de l'évènement" maxlength="1000">{{ $event->description }}</textarea>
                                        <span class="char-count">{{ mb_strlen($event->description ?? '') }}/1000</span>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                                        <div class="media-upload-placeholder video-place">
                                            @if($event->video_path)
                                                <p style="color: #000; font-weight: bold; background: #fff; padding: 2px 10px; border-radius: 10px;">Vidéo sauvegardée</p>
                                            @else
                                                <p>upload video</p>
                                            @endif
                                            <input type="file" accept="video/*" class="hidden-file-input video-input" style="display: none;">
                                        </div>
                                        @if($event->video_path)
                                            <div class="video-name-display" style="color: rgba(255,255,255,0.7); font-size: 0.8rem; margin-top: 8px; text-align: center; word-break: break-all;">
                                                Nom : {{ basename($event->video_path) }}
                                            </div>
                                        @endif
                                        <div class="event-media-progress" style="display: none; width: 100%; text-align: center; margin-top: 5px;">
                                            <div style="width: 100%; background-color: #eee; border-radius: 5px; height: 5px; overflow: hidden;">
                                                <div class="event-media-bar" style="width: 0%; height: 100%; background-color: var(--black); transition: width 0.2s;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--black); padding: 2rem;">Aucun évènement pour le moment.</div>
                    @endforelse
                </div>
            </section>

        </main>
    </div>

    <script src="{{ asset('JS/admin.js') }}?v={{ time() }}"></script>
</body>

</html>