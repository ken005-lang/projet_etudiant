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
        const heartImg = isFav ? "ICON/bookmark-simple-fill.svg" : "ICON/bookmark-simple.svg";
        const heartClass = isFav ? "band-bookmark-icon active" : "band-bookmark-icon";

        // Logic "Section Vide" for Intro
        let introHtml = '';
        if (group.domains.length === 0 && group.members.length === 0) {
            introHtml = `<div class="section-vide">Section vide</div>`;
        } else {
            let domainsHtml = group.domains.length > 0
                ? group.domains.map(d => `<div>-${d}</div>`).join('')
                : `<div class="section-vide">Aucun domaine</div>`;

            let membersHtml = '';
            if (group.members.length > 0) {
                const membersRows = group.members.map(m => `
                    <div>-${m.name}</div>
                    <div>-${m.filiere}</div>
                    <div>-${m.niveau}</div>
                `).join('');
                membersHtml = `
                    <div class="members-grid">
                        <div class="members-grid-column"><strong>CHEF</strong><div>-${group.leader}</div></div>
                        <div class="members-grid-column"><strong>MEMBRES DU GROUPE</strong>${group.members.map(m => `<div>-${m.name}</div>`).join('')}</div>
                        <div class="members-grid-column"><strong>FILIERES</strong>${group.members.map(m => `<div>-${m.filiere}</div>`).join('')}</div>
                        <div class="members-grid-column"><strong>NIVEAUX</strong>${group.members.map(m => `<div>-${m.niveau}</div>`).join('')}</div>
                    </div>
                `;
            } else {
                membersHtml = `
                    <div class="members-grid">
                        <div class="members-grid-column"><strong>CHEF</strong><div>-${group.leader}</div></div>
                        <div class="members-grid-column"><strong>MEMBRES DU GROUPE</strong><div class="section-vide">Aucun membre</div></div>
                    </div>
                `;
            }

            introHtml = `
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
               <div style="display:flex; justify-content:center; align-items:center; width:100%;">
                 <div class="video-tab-content" style="display:flex; gap: 2rem; align-items:center;">
                   <h2 style="color:white; font-size:1.5rem; max-width:200px;">POUR EN SAVOIR PLUS REGARDER CETTE VIDEO.</h2>
                   <div class="video-player-placeholder">
                     <img src="ICON/video-camera.svg" alt="Play">
                   </div>
                 </div>
               </div>`
            : `<div class="section-vide" style="color:white;">Section vide</div>`;

        // Logic "Section Vide" for Contact
        let contactHtml = '';
        if (!group.whatsapp && !group.email) {
            contactHtml = `<div class="section-vide">Section vide</div>`;
        } else {
            if (group.whatsapp) contactHtml += `<div class="contact-item"><img src="ICON/whatsapp-icon.svg" alt="WA"> ${group.whatsapp}</div>`;
            if (group.email) contactHtml += `<div class="contact-item"><img src="ICON/email-icon.svg" alt="Email"> ${group.email}</div>`;
        }


        // Fallback images path correction assuming base URL
        const imgSrc = group.image.startsWith('HTTP') ? group.image : '/' + group.image;

        return `
            <div class="project-band" data-id="${group.id}">
                <div class="project-band-header">
                    <span>${group.name.toUpperCase()}...</span>
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
                            <div class="section-vide">Section vide</div>
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
        let imageHtml = event.image ? `<img src="${event.image.startsWith('HTTP') ? event.image : '/' + event.image}" alt="Event Image" class="actual-image">` : inlineImageSvg;

        let inlineVideoSvg = `<svg width="50" height="50" viewBox="0 0 24 24" fill="none" class="placeholder-icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>`;
        let videoHtml = event.video ? `<video src="${event.video.startsWith('HTTP') ? event.video : '/' + event.video}" controls></video>` : inlineVideoSvg;

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

    // ---- INITIAL RENDER ----
    renderProjectsList();
    renderFavoritesList();
    renderEventsList();
});
