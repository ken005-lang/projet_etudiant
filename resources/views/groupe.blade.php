<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ITES - Espace Groupe</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body class="group-dashboard-page">
    <div class="dashboard-container">
        <!-- Main Dashboard Area -->
        <div class="dashboard-main-area">
            <header class="dashboard-header">
                <div class="header-left">
                    <img src="{{ asset('IMG/ITESLOGO.svg') }}" alt="Group" class="header-logo">
                    <div class="group-info-display">
                        <span id="groupNameValue" class="group-label">{{ $group->project_name }}</span>
                        <input type="text" id="groupNameInput" style="display: none;" class="header-name-input">
                        <button id="groupNameToggleBtn" class="btn-pill-small small-header-btn">Modifier</button>
                        <!--<div class="group-icon-square">
                        </div>-->
                    </div>
                </div>
            </header>

            <nav class="dashboard-tabs">
                <button class="nav-tab active" data-tab="introduction">INTRODUCTION</button>
                <button class="nav-tab" data-tab="rapports">RAPPORTS</button>
                <button class="nav-tab" data-tab="multimedia">EN SAVOIR PLUS</button>
                <button class="nav-tab" data-tab="contact">CONTACT</button>
            </nav>

            <main class="dashboard-content-panes">
                <!-- Section INTRODUCTION -->
                <section id="introduction-pane" class="tab-pane active">
                    <div class="pane-column">
                        <div class="intro-input-card">
                            <div id="introTextDisplay" class="text-display" style="display: flex; align-items: flex-start; justify-content: center; min-height: 150px; text-align: center; width: 100%; flex: 1; padding-top: 1rem;">{{ $group->project_intro ?? '_ _ _ _' }}</div>
                            <textarea id="introTextArea" style="display: none;" maxlength="1000"
                                placeholder="Saisissez votre introduction..."></textarea>
                            <div class="intro-footer">
                                <span class="word-limit">{{ $group->project_intro ? mb_strlen($group->project_intro) : 0 }}/1000</span>
                                <button class="btn-pill-action" id="introToggleBtn">Modifier</button>
                            </div>
                        </div>

                        <div class="info-row-pill">
                            <label>PROJET DE NIVEAU : <span id="projectLevelValue">{{ $group->leader_level }}</span></label>
                            <div class="pill-input-group">
                                <input type="text" placeholder="" id="projectLevelInput">
                                <button class="btn-pill-small" id="projectLevelToggleBtn">Modifier</button>
                            </div>
                        </div>

                        <div class="info-column-group">
                            <label>DOMAINES QUE COUVRE LE PROJET</label>
                            <ul class="domain-list" id="domainList">
                                @if($group->project_domain)
                                    @foreach(explode(',', $group->project_domain) as $domain)
                                        <li>-{{ trim($domain) }} <img src="{{ asset('ICON/x-circle-fill.svg') }}" class="remove-domain" alt="x"></li>
                                    @endforeach
                                @endif
                            </ul>
                            <div class="pill-input-group">
                                <input type="text" placeholder="" id="domainInput">
                                <button class="btn-pill-small" id="addDomainBtn">Ajouter</button>
                            </div>
                        </div>

                        <div class="members-management">
                            <table class="members-dashboard-table">
                                <thead>
                                    <tr>
                                        <th>MEMBRES DU GROUPE</th>
                                        <th>FILIERES</th>
                                        <th>NIVEAUX</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="membersBody">
                                    <tr>
                                        <td><span class="badge-chef">CHEF</span> {{ $group->leader_name }}</td>
                                        <td>{{ $group->leader_sector }}</td>
                                        <td>{{ $group->leader_level }}</td>
                                        <td></td>
                                    </tr>
                                    @foreach($group->members as $member)
                                    <tr data-id="{{ $member->id }}">
                                        <td><span class="badge-spacer"></span>{{ $member->name }}</td>
                                        <td>{{ $member->sector }}</td>
                                        <td>{{ $member->level }}</td>
                                        <td><img src="{{ asset('ICON/trash-fill.svg') }}" class="delete-member" alt="delete"></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="members-management-footer">
                                <button id="showAddMemberBtn" class="btn-pill-action">Ajouter un membre</button>
                                <div class="add-member-form" style="display: none;">
                                    <input type="text" id="memberName" placeholder="Nom et prenom">
                                    <input type="text" id="memberField" placeholder="Filière">
                                    <input type="text" id="memberLevel" placeholder="Niveau">
                                    <button class="btn-pill-small" id="addMemberBtn">Confirmer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section RAPPORTS -->
                <section id="rapports-pane" class="tab-pane">
                    <div class="rapports-header" style="display: none;">
                        <button class="btn-pill-main publish-btn-header">Publier un rapport</button>
                    </div>

                    <div class="empty-state-container">
                        <img src="{{ asset('IMG/mascotte ITES.png') }}" alt="Cactus" class="empty-icon-lg">
                        <p class="empty-text">Aucun rapport ajouté.</p>
                        <button class="btn-pill-main publish-btn-empty">Publier un rapport</button>
                    </div>

                    <div class="reports-grid" id="reportsGrid">
                        <!-- Reports will be added here dynamically -->
                    </div>

                    <input type="file" id="reportInput" multiple accept=".pdf,image/*,video/*" style="display: none;">
                </section>

                <!-- Section EN SAVOIR PLUS -->
                <section id="multimedia-pane" class="tab-pane">
                    <div class="empty-state-container" id="videoEmptyState" style="{{ $group->project_video ? 'display: none;' : '' }}">
                        <button class="btn-pill-main" id="triggerVideoUploadBtn">Ajouter une vidéo</button>
                    </div>

                    <div class="video-container" id="videoContainer" style="display: flex; flex-direction: column; align-items: center; {{ $group->project_video ? '' : 'display: none;' }}">
                        <video id="projectVideoPlayer" src="{{ $group->project_video ? asset($group->project_video) : '' }}" controls style="width: 100%; max-width: 800px; border-radius: 10px; max-height: 400px; background: #000;"></video>
                        <button class="btn-pill-main" id="replaceVideoBtn" style="margin-top: 1rem;">Remplacer la vidéo</button>
                    </div>

                    <div id="videoUploadProgressContainer" style="display: none; width: 100%; max-width: 400px; margin: 1rem auto; text-align: center;">
                        <p style="margin-bottom: 0.5rem; color: var(--white); font-weight: 600;">Téléversement en cours... <span id="videoUploadPercent">0%</span></p>
                        <div style="width: 100%; background-color: #eee; border-radius: 10px; height: 10px; overflow: hidden;">
                            <div id="videoUploadProgressBar" style="width: 0%; height: 100%; background-color: var(--black); transition: width 0.2s;"></div>
                        </div>
                    </div>

                    <input type="file" id="videoInput" accept="video/*" style="display: none;">
                </section>

                <!-- Section CONTACT -->
                <section id="contact-pane" class="tab-pane">
                    <div class="contact-form-dashboard">
                        <div class="contact-group">
                            <label>Contact whatsapp</label>
                            <input type="tel" id="contactWhatsapp" class="contact-input-pill" value="{{ $group->contact_whatsapp }}" placeholder="+225 00 00 00 00 00">
                        </div>
                        <div class="contact-group">
                            <label>E-MAIL</label>
                            <input type="email" id="contactEmail" placeholder="xxx@gmail.com" class="contact-input-pill" value="{{ $group->contact_email }}">
                        </div>
                        <div class="contact-actions">
                            <button class="btn-pill-main" id="submitContact">Valider</button>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="dashboard-bottom-nav">
                <div class="footer-actions">
                    <div id="deleteGroupContainer">
                        <button id="showDeleteGroupBtn" class="btn-pill-action danger">Supprimer le groupe</button>
                        <div id="deleteConfirmationForm" style="display: none;" class="delete-form-pill">
                            <input type="text" id="groupDeleteCode" placeholder="code id">
                            <button id="cancelDeleteBtn" class="btn-pill-small">Annuler</button>
                            <button id="confirmDeleteBtn" class="btn-pill-small danger">Valider</button>
                        </div>
                    </div>
                    <a href="{{ url('/login') }}" class="logout-link">
                        Deconnexion <img src="{{ asset('ICON/logout_icon.svg') }}" alt="Logout">
                    </a>
                </div>
            </footer>
        </div>

        <!-- Right Sidebar Area -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-top">
                <div class="profile-avatar-circle">
                    <img src="{{ $group->project_image ? asset($group->project_image) : asset('ICON/group.svg') }}" alt="User" id="sidebarAvatarImg" style="{{ $group->project_image ? 'width:100%;height:100%;object-fit:cover;filter:none;border-radius:20px;' : 'border-radius:20px;' }}">
                </div>
                <div class="side-icon-box small" id="triggerUploadBtn" style="margin-top: 1rem;">
                    <img src="{{ asset('ICON/camera-rotate-fill.svg') }}" alt="Camera">
                </div>
            </div>
            <div class="sidebar-icons">
                <input type="file" id="sidebarImageInput" accept="image/*" style="display: none;">
            </div>
        </aside>
    </div>

    <script>
        window.serverGroupData = @json($serverGroupData);
    </script>
    <script src="{{ asset('JS/groupe.js') }}?v={{ time() }}"></script>
</body>

</html>