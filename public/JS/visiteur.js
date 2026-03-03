document.addEventListener('DOMContentLoaded', function () {
    // ---- DATA IMPORT (Dynamic from Backend) ----
    const groupsData = window.serverGroupsData || [];

    // ---- STATE MANAGEMENT ----
    let favorites = JSON.parse(localStorage.getItem('ites_favorites')) || [];

    function saveFavorites() {
        localStorage.setItem('ites_favorites', JSON.stringify(favorites));
    }

    function toggleFavorite(id) {
        if (favorites.includes(id)) {
            favorites = favorites.filter(favId => favId !== id);
        } else {
            favorites.push(id);
        }
        saveFavorites();
        // Re-render both lists to reflect heart changes
        renderProjectsList();
        renderFavoritesList();
    }

    // Sort globally by most recently modified
    groupsData.sort((a, b) => new Date(b.last_modified) - new Date(a.last_modified));

    // ---- DOM ELEMENTS ----
    const projectsContainer = document.getElementById('projects-list-container');
    const projectsEmptyState = document.getElementById('projects-empty-state');
    const favoritesContainer = document.getElementById('favorites-list-container');
    const favoritesEmptyState = document.getElementById('favorites-empty-state');
    const searchInput = document.getElementById('projectSearch');

    // ---- RENDERING LOGIC ----
    function createBandHTML(group) {
        const isFav = favorites.includes(group.id);
        const heartImg = "/ICON/bookmark-simple-fill.svg";
        const heartClass = isFav ? "band-bookmark-icon active" : "band-bookmark-icon";

        // Logic "Section Vide" for Intro
        let introHtml = '';
        if (group.domains.length === 0 && group.members.length === 0) {
            introHtml = `<div class="section-vide">Section vide</div>`;
        } else {
            let domainsHtml = group.domains.length > 0
                ? group.domains.map(d => `<div>-${d}</div>`).join('')
                : `<div class="section-vide">Aucun domaine</div>`;

            let membersHtml = `
                <table class="visitor-members-table">
                    <thead>
                        <tr>
                            <th>MEMBRES DU GROUPE</th>
                            <th>FILIERES</th>
                            <th>NIVEAUX</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge-chef">CHEF</span> ${group.leader}</td>
                            <td>${group.filiere || '-'}</td>
                            <td>${group.niveau || '-'}</td>
                        </tr>
                        ${group.members.map(m => `
                        <tr>
                            <td><span class="badge-spacer"></span>${m.name}</td>
                            <td>${m.sector || m.filiere || '-'}</td>
                            <td>${m.level || m.niveau || '-'}</td>
                        </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;

            let introTextHtml = '';
            if (group.intro && group.intro.trim() !== '' && group.intro.trim() !== '_ _ _ _') {
                introTextHtml = `<div class="intro-description"><p>${group.intro}</p></div><div class="solid-line"></div>`;
            }

            introHtml = `
                ${introTextHtml}
                <div class="intro-text-block">
                    <p>PROJET DE NIVEAU : ${group.niveau}</p>
                </div>
                <div class="solid-line"></div>
                <div>
                    <strong>DOMAINES QUE COUVRE LE PROJET</strong>
                    <div class="domains-list">${domainsHtml}</div>
                </div>
                <div class="solid-line"></div>
                ${membersHtml}
            `;
        }

        // Logic "Section Vide" for Video
        let videoHtml = group.video
            ? `
               <div style="display:flex; flex-direction:column; align-items:flex-start; width:100%; gap: 1.5rem;">
                   <h2 style="color:white; font-size:1.5rem; font-weight:700; max-width: 100%; line-height: 1.2;">POUR EN SAVOIR PLUS REGARDER CETTE VIDEO.</h2>
                   <div class="custom-video-wrapper" style="width: 100%; max-width: 600px; aspect-ratio: 16/9; min-height: 200px;">
                       <video id="video-project-${group.id}" src="${group.video.match(/^https?:\/\//i) ? group.video : '/' + group.video}" preload="metadata" controls></video>
                       <div class="video-overlay" onclick="document.getElementById('video-project-${group.id}').play(); document.getElementById('video-project-${group.id}').setAttribute('controls', 'controls'); this.style.display='none';">
                           <div class="icon-container">
                               <img src="/ICON/film-strip.svg" alt="Play Video" class="video-play-icon">
                           </div>
                           <span class="video-label">Voir la vidéo</span>
                       </div>
                   </div>
               </div>`
            : `<div class="section-vide" style="color:white;">Section vide</div>`;

        // Logic for Contact Section
        let contactItems = '';
        if (group.whatsapp) contactItems += `<div class="contact-item"><img src="ICON/whatsapp-icon.svg" alt="WA"> <span>${group.whatsapp}</span></div>`;
        if (group.email) contactItems += `<div class="contact-item"><img src="ICON/email-icon.svg" alt="Email"> <span>${group.email}</span></div>`;

        let contactHtml = `
            <div class="contact-tab-container">
                <div class="contact-info-column">
                    ${contactItems || '<div class="section-vide" style="color:white; margin:0;">Aucune coordonnée directe.</div>'}
                </div>
                <div class="contact-form-column">
                    <textarea class="contact-quick-textarea" placeholder="Envoyer un commentaire ou un message..."></textarea>
                    <button class="contact-quick-valider">Valider</button>
                </div>
            </div>
        `;

        // Logic for Reports
        let reportsHtml = '';
        if (group.reports && group.reports.length > 0) {
            const reportsList = group.reports.map(report => {
                const ext = report.file_name.split('.').pop().toLowerCase();
                const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                const videoExts = ['mp4', 'mov', 'webm', 'avi', 'mkv', 'ogg'];

                let iconHtml;
                if (imageExts.includes(ext)) {
                    iconHtml = `<div class="report-icon-box" style="overflow:hidden; border-radius:8px; pointer-events:none;">
                        <img src="${report.file_url}" alt="Image" style="width:100%; height:100%; object-fit:cover;">
                    </div>`;
                } else if (videoExts.includes(ext)) {
                    iconHtml = `<div class="report-icon-box" style="background:#111; border-radius:8px; display:flex; align-items:center; justify-content:center; pointer-events:none;">
                        <img src="/ICON/film-strip.svg" alt="Vidéo" style="width:50%; filter:invert(1);">
                    </div>`;
                } else {
                    iconHtml = `<div class="report-icon-box" style="pointer-events:none;">
                        <img src="/ICON/file-pdf.svg" alt="PDF">
                    </div>`;
                }

                return `
                    <div class="report-card" style="cursor: pointer;" onclick="window.open('${report.file_url}', '_blank')">
                        ${iconHtml}
                        <span class="report-filename" title="${report.file_name}" style="pointer-events: none;">${report.file_name}</span>
                    </div>
                `;
            }).join('');

            reportsHtml = `
                <div class="reports-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 1rem;">
                    ${reportsList}
                </div>
            `;
        } else {
            reportsHtml = `<div class="section-vide" style="color:white;">Aucun rapport publié.</div>`;
        }


        // Fallback images path correction assuming base URL
        const imgSrc = group.image.match(/^https?:\/\//i) ? group.image : '/' + group.image;

        return `
            <div class="project-band" data-id="${group.id}">
                <div class="project-band-header">
                    <span>${group.name.toUpperCase()}</span>
                    <div class="header-right-actions">
                        <img src="${heartImg}" alt="Fav" class="${heartClass}" onclick="event.stopPropagation(); toggleFavorite(${group.id});">
                        <img src="ICON/up-arrow_icon.svg" alt="Expand" class="chevron-icon">
                    </div>
                </div>
                
                <div class="project-band-content">
                    <div class="project-content-left">
                        <div class="project-image-container">
                            <img src="${imgSrc}" alt="${group.name}">
                        </div>
                        <span class="project-group-name">${group.name}</span>
                    </div>

                    <div class="project-content-right">
                        <div class="project-tabs">
                            <button class="project-tab-btn active" data-target="intro-${group.id}">INTRODUCTION</button>
                            <button class="project-tab-btn" data-target="rapports-${group.id}">RAPPORTS</button>
                            <button class="project-tab-btn" data-target="ensavoir-${group.id}">EN SAVOIR PLUS</button>
                            <button class="project-tab-btn" data-target="contact-${group.id}">CONTACT</button>
                        </div>
                        
                        <div class="project-tab-panel active" id="intro-${group.id}">
                            ${introHtml}
                        </div>
                        <div class="project-tab-panel" id="rapports-${group.id}">
                            ${reportsHtml}
                        </div>
                        <div class="project-tab-panel" id="ensavoir-${group.id}">
                            ${videoHtml}
                        </div>
                        <div class="project-tab-panel" id="contact-${group.id}">
                            ${contactHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Attach local toggle logic to window for inline onclick execution
    window.toggleFavorite = toggleFavorite;

    function renderList(container, emptyState, dataToRender) {
        container.innerHTML = '';
        if (dataToRender.length === 0) {
            emptyState.style.display = 'block';
            container.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            container.style.display = 'flex';
            dataToRender.forEach(group => {
                container.innerHTML += createBandHTML(group);
            });
            attachBandEvents(container);
        }
    }

    function attachBandEvents(container) {
        // Accordion toggle
        const headers = container.querySelectorAll('.project-band-header');
        headers.forEach(header => {
            header.addEventListener('click', function () {
                const band = this.closest('.project-band');
                band.classList.toggle('expanded');
            });
        });

        // Tabs within accordions
        const tabBtns = container.querySelectorAll('.project-tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                // Prevent accordion from toggling if clicking tabs
                e.stopPropagation();
                const bandContent = this.closest('.project-band-content');
                const targetId = this.getAttribute('data-target');

                bandContent.querySelectorAll('.project-tab-btn').forEach(b => b.classList.remove('active'));
                bandContent.querySelectorAll('.project-tab-panel').forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                bandContent.querySelector('#' + targetId).classList.add('active');
            });
        });
    }

    function renderProjectsList(searchTerm = '') {
        const lowerTerm = searchTerm.toLowerCase();
        const filteredGroups = groupsData.filter(g => g.name.toLowerCase().includes(lowerTerm));
        renderList(projectsContainer, projectsEmptyState, filteredGroups);
    }

    function renderFavoritesList() {
        const favGroups = groupsData.filter(g => favorites.includes(g.id));
        renderList(favoritesContainer, favoritesEmptyState, favGroups);
    }

    // ---- SEARCH BINDING ----
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            renderProjectsList(e.target.value);
        });
    }

    // ---- MAIN NAVIGATION TABS BINDING ----
    const navLinks = document.querySelectorAll('.visitor-nav-link');
    const tabContents = document.querySelectorAll('.tab-content');
    const bookmarkBtn = document.querySelector('.bookmark-btn');
    const bookmarkIcon = document.querySelector('.bookmark-icon');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');

            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === tabId + '-section') {
                    content.classList.add('active');
                }
            });

            if (tabId !== 'favorites') {
                bookmarkIcon.style.color = 'black';
                bookmarkIcon.src = "/ICON/bookmark-simple-fill.svg"; // Reset visually
            }
        });
    });

    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function () {
            navLinks.forEach(l => l.classList.remove('active'));
            bookmarkIcon.style.color = 'var(--orange)';

            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === 'favorites-section') {
                    content.classList.add('active');
                }
            });

            // Re-render favorites when tab is opened to ensure freshness
            renderFavoritesList();
        });
    }

    // ---- EVENTS LOGIC (Dynamic from Backend) ----
    const eventsData = window.serverEventsData || [];

    const eventsContainer = document.getElementById('events-list-container');
    const eventsEmptyState = document.getElementById('events-empty-state');

    function createEventBandHTML(event) {
        let inlineImageSvg = `<svg width="40" height="40" viewBox="0 0 24 24" fill="none" class="placeholder-icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>`;
        let inlineVideoSvg = `<svg width="50" height="50" viewBox="0 0 24 24" fill="none" class="placeholder-icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>`;
        let imageHtml = event.image ? `<img src="${event.image.match(/^https?:\/\//i) ? event.image : '/' + event.image}" alt="Event Image" class="actual-image">` : inlineImageSvg;

        let videoHtml = event.video ? `
            <div class="custom-video-wrapper">
                <video id="video-event-${event.id}" src="${event.video.match(/^https?:\/\//i) ? event.video : '/' + event.video}" preload="metadata" controls></video>
                <div class="video-overlay" onclick="document.getElementById('video-event-${event.id}').play(); document.getElementById('video-event-${event.id}').setAttribute('controls', 'controls'); this.style.display='none';">
                    <div class="icon-container">
                        <img src="/ICON/film-strip.svg" alt="Play Video" class="video-play-icon">
                    </div>
                    <span class="video-label">Voir la vidéo</span>
                </div>
            </div>
        ` : inlineVideoSvg;

        return `
            <div class="event-band" data-id="${event.id}">
                <div class="event-band-header">
                    <span>${event.title}</span>
                    <img src="ICON/up-arrow_icon.svg" alt="Expand" class="chevron-icon">
                </div>
                
                <div class="event-band-content">
                    <div class="event-content-left">
                        <div class="event-image-placeholder">
                            ${imageHtml}
                        </div>
                    </div>

                    <div class="event-content-right">
                        <div class="event-description">
                            ${event.description.replace(/\n/g, '<br>')}
                        </div>
                        <div class="event-video-placeholder">
                            ${videoHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderEventsList() {
        if (!eventsContainer || !eventsEmptyState) return;
        eventsContainer.innerHTML = '';
        if (eventsData.length === 0) {
            eventsEmptyState.style.display = 'block';
            eventsContainer.style.display = 'none';
        } else {
            eventsEmptyState.style.display = 'none';
            eventsContainer.style.display = 'flex';
            eventsData.forEach(event => {
                eventsContainer.innerHTML += createEventBandHTML(event);
            });
            attachEventBandEvents(eventsContainer);

        }
    }

    function attachEventBandEvents(container) {
        const headers = container.querySelectorAll('.event-band-header');
        headers.forEach(header => {
            header.addEventListener('click', function () {
                const band = this.closest('.event-band');
                band.classList.toggle('expanded');
            });
        });
    }

    // ---- NOTIFICATION LOGIC ----
    const eventsNotifDot = document.getElementById('events-notif-dot');

    function checkNewEvents() {
        if (!eventsNotifDot || eventsData.length === 0) return;

        const lastSeenId = parseInt(localStorage.getItem('ites_last_seen_event_id') || '0');
        const maxEventId = Math.max(...eventsData.map(e => e.id));

        if (maxEventId > lastSeenId) {
            eventsNotifDot.style.display = 'block';
        } else {
            eventsNotifDot.style.display = 'none';
        }
    }

    // Reset notification when clicking on events tab
    navLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (this.getAttribute('data-tab') === 'events' && eventsData.length > 0) {
                const maxEventId = Math.max(...eventsData.map(e => e.id));
                localStorage.setItem('ites_last_seen_event_id', maxEventId.toString());
                if (eventsNotifDot) eventsNotifDot.style.display = 'none';
            }
        });
    });

    // ---- INITIAL RENDER ----
    renderProjectsList();
    renderFavoritesList();
    renderEventsList();
    checkNewEvents();
});
