<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        <span id="groupNameValue" class="group-label">Nom de groupe</span>
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
                            <div id="introTextDisplay" class="text-display">
                            </div>
                            <textarea id="introTextArea" style="display: none;" maxlength="1000"
                                placeholder="Saisissez votre introduction..."></textarea>
                            <div class="intro-footer">
                                <span class="word-limit">0/1000</span>
                                <button class="btn-pill-action" id="introToggleBtn">Modifier</button>
                            </div>
                        </div>

                        <div class="info-row-pill">
                            <label>PROJET DE NIVEAU : <span id="projectLevelValue">xxxxxx</span></label>
                            <div class="pill-input-group">
                                <input type="text" placeholder="" id="projectLevelInput">
                                <button class="btn-pill-small" id="projectLevelToggleBtn">Modifier</button>
                            </div>
                        </div>

                        <div class="info-column-group">
                            <label>DOMAINES QUE COUVRE LE PROJET</label>
                            <ul class="domain-list" id="domainList">
                                <li>-xxxxx <img src="{{ asset('ICON/x-circle-fill.svg') }}" class="remove-domain" alt="x"></li>
                                <li>-xxxxx <img src="{{ asset('ICON/x-circle-fill.svg') }}" class="remove-domain" alt="x"></li>
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
                                        <td><span class="badge-chef">CHEF</span> -xxxxx</td>
                                        <td>-xxxxx</td>
                                        <td>-xxxxx</td>
                                        <td></td>
                                    </tr>
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

                    <input type="file" id="reportInput" multiple style="display: none;">
                </section>

                <!-- Section EN SAVOIR PLUS -->
                <section id="multimedia-pane" class="tab-pane">
                    <div class="empty-state-container">
                        <button class="btn-pill-main">Ajouter une vidéo</button>
                    </div>
                </section>

                <!-- Section CONTACT -->
                <section id="contact-pane" class="tab-pane">
                    <div class="contact-form-dashboard">
                        <div class="contact-group">
                            <label>Contact whatsapp</label>
                            <input type="text" class="contact-input-pill">
                        </div>
                        <div class="contact-group">
                            <label>E-MAIL</label>
                            <input type="email" placeholder="xxx@gmail.com" class="contact-input-pill">
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
                    <img src="{{ asset('ICON/group.svg') }}" alt="User">
                </div>
            </div>
            <div class="sidebar-icons">
                <div class="side-icon-box" id="sidebarImageDisplay">
                    <img src="{{ asset('IMG/mascotte_ites.png') }}" alt="Image" id="sidebarImg">
                </div>
                <div class="side-icon-box small" id="triggerUploadBtn">
                    <img src="{{ asset('ICON/camera-rotate-fill.svg') }}" alt="Camera">
                </div>
                <input type="file" id="sidebarImageInput" accept="image/*" style="display: none;">
            </div>
        </aside>
    </div>

    <script src="{{ asset('JS/groupe.js') }}"></script>
</body>

</html>