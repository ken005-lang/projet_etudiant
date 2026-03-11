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
                ? group.domains.map(d => `<div><span class="clickable-domain" onclick="executeDomainSearch('${d.replace(/'/g, "\\'")}')">-${d}</span><img src="/ICON/research_icon.svg" class="external-search-icon" onclick="openDomainInfo('${d.replace(/'/g, "\\'")}')" alt="chercher"></div>`).join('')
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
        if (group.whatsapp) contactItems += `<div class="contact-item"><img src="/ICON/whatsapp-logo-fill.svg" alt="WA"> <span>${group.whatsapp}</span></div>`;
        if (group.email) contactItems += `<div class="contact-item"><img src="/ICON/paperclip-fill.svg" alt="Email"> <span>${group.email}</span></div>`;

        let contactHtml = `
            <div class="contact-tab-container">
                <div class="contact-info-column">
                    ${contactItems || '<div class="section-vide" style="color:white; margin:0;">Aucune coordonnée directe.</div>'}
                </div>
                <div class="contact-form-column">
                    <textarea class="contact-quick-textarea" placeholder="Envoyer un commentaire ou un message..."></textarea>
                    <button class="contact-quick-valider" data-group-id="${group.user_id}" data-group-name="${group.name}">Valider</button>
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
        const isDefaultImg = imgSrc.includes('group.svg');
        const imgClass = isDefaultImg ? 'default-project-img' : 'actual-image';
        const containerClass = isDefaultImg ? 'project-image-container default-bg' : 'project-image-container';

        return `
            <div class="project-band" data-id="${group.id}">
                <div class="project-band-header">
                    <span>${group.name.toUpperCase()}</span>
                    <div class="header-right-actions">
                        <img src="${heartImg}" alt="Fav" class="${heartClass}" onclick="event.stopPropagation(); toggleFavorite(${group.id});">
                        <img src="/ICON/up-arrow_icon.svg" alt="Expand" class="chevron-icon">
                    </div>
                </div>
                
                <div class="project-band-content">
                    <div class="project-content-left">
                        <div class="${containerClass}">
                            <img src="${imgSrc}" alt="${group.name}" class="${imgClass}">
                        </div>
                        <span class="project-group-name">${group.name}</span>
                    </div>

                    <div class="project-content-right">
                        <div class="project-tabs">
                             <button class="project-tab-btn active" data-target="presentation-${group.id}">PRESENTATION</button>
                            <button class="project-tab-btn" data-target="rapports-${group.id}">RAPPORTS</button>
                            <button class="project-tab-btn" data-target="ensavoir-${group.id}">EN SAVOIR PLUS</button>
                            <button class="project-tab-btn" data-target="contact-${group.id}">CONTACT</button>
                        </div>
                        
                        <div class="project-tab-panel active" id="presentation-${group.id}">
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

    // Attach logic to window for inline onclick execution
    window.toggleFavorite = toggleFavorite;
    window.executeDomainSearch = function(domain) {
        const searchInput = document.getElementById('projectSearch');
        if (searchInput) {
            searchInput.value = domain;
            renderProjectsList(domain);
            
            // Collapse all expanded bands
            const allBands = projectsContainer.querySelectorAll('.project-band');
            allBands.forEach(band => band.classList.remove('expanded'));
            
            // Scroll to the top of the projects section
            document.getElementById('projects-section').scrollIntoView({ behavior: 'smooth' });
        }
    };
    
    window.openDomainInfo = function(domain) {
        window.open('https://www.google.com/search?q=' + encodeURIComponent(domain), '_blank');
    };

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
        const filteredGroups = groupsData.filter(g => {
            const matchName = g.name.toLowerCase().includes(lowerTerm);
            const matchDomain = g.domains && g.domains.some(d => d.toLowerCase().includes(lowerTerm));
            return matchName || matchDomain;
        });
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
    let eventsData = window.serverEventsData || [];

    const eventsContainer = document.getElementById('events-list-container');
    const eventsEmptyState = document.getElementById('events-empty-state');

    function createEventBandHTML(event) {
        let inlineImageSvg = `<svg width="40" height="40" viewBox="0 0 24 24" fill="none" class="placeholder-icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>`;
        let inlineVideoSvg = `<svg width="50" height="50" viewBox="0 0 24 24" fill="none" class="placeholder-icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>`;
        let imageHtml = event.image ? `<img src="${event.image.match(/^https?:\/\//i) ? event.image : '/' + event.image}" alt="Event Image" class="actual-image">` : inlineImageSvg;

        let videoHtml = event.video ? `
            <div class="event-video-placeholder">
                <div class="custom-video-wrapper">
                    <video id="video-event-${event.id}" src="${event.video.match(/^https?:\/\//i) ? event.video : '/' + event.video}" preload="metadata" controls></video>
                    <div class="video-overlay" onclick="document.getElementById('video-event-${event.id}').play(); document.getElementById('video-event-${event.id}').setAttribute('controls', 'controls'); this.style.display='none';">
                        <div class="icon-container">
                            <img src="/ICON/film-strip.svg" alt="Play Video" class="video-play-icon">
                        </div>
                        <span class="video-label">Voir la vidéo</span>
                    </div>
                </div>
            </div>
        ` : '';

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
                        ${videoHtml}
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

    // =============================================
    // MESSAGERIE VISITEUR
    // =============================================
    const bellBtn = document.getElementById('header-bell-btn');
    const msgsOverlay = document.getElementById('messages-overlay');
    const closeMsgsBtn = document.getElementById('close-messages-btn');
    const msgList = document.getElementById('messages-list-container');
    const notifDot = document.getElementById('messages-notif-dot');
    const clearBtn = document.getElementById('visitor-clear-messages');

    // Check for unread messages
    async function checkUnread() {
        try {
            const res = await fetch('/visiteur/messages/unread');
            const data = await res.json();
            if (data.success && data.unread_count > 0) {
                notifDot.style.display = 'block';
            } else {
                notifDot.style.display = 'none';
            }
        } catch (e) { console.error('Error checking unread', e); }
    }

    // Load messages
    async function loadMessages() {
        msgList.innerHTML = '<div class="section-vide">Chargement...</div>';
        try {
            const res = await fetch('/visiteur/messages', { headers: { 'Accept': 'application/json' } });
            const textResponse = await res.text();

            if (!res.ok) {
                console.error('[Messagerie Visiteur] Erreur HTTP', res.status, textResponse.substring(0, 500));
                msgList.innerHTML = `<div class="section-vide">Erreur ${res.status} - rechargez la page.</div>`;
                return;
            }

            let data;
            try {
                data = JSON.parse(textResponse);
            } catch (jsonErr) {
                console.error('[Messagerie Visiteur] JSON Error:', jsonErr, 'Response:', textResponse.substring(0, 500));
                msgList.innerHTML = `<div style="color:red; font-size: 0.8rem; padding:10px; word-break: break-all;">Erreur Serveur (HTML reçu au lieu de JSON) : <br><br>${textResponse.substring(0, 150).replace(/</g, '&lt;')}</div>`;
                return;
            }

            if (data.success) {
                renderMessages(data.messages);
                // Mark as read after loading
                await fetch('/visiteur/messages/read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
                });
                notifDot.style.display = 'none';
            }
        } catch (e) {
            console.error('[Messagerie Visiteur] Exception interceptée:', e);
            msgList.innerHTML = `<div class="section-vide">Erreur critique JS: ${e.message}</div>`;
        }
    }

    function renderMessages(msgs) {
        if (!msgs || msgs.length === 0) {
            msgList.innerHTML = '<div class="section-vide" style="color:black;">Aucun message.</div>';
            return;
        }

        const fragment = document.createDocumentFragment();
        msgs.forEach(msg => {
            const block = document.createElement('div');
            block.className = 'message-block visitor-msg';

            const hasReply = msg.group_reply ? true : false;

            // Visitor original message
            let html = `
                <div class="msg-header">
                    <div class="msg-user-info">
                        <img src="/ICON/profile_user_avatar_person_icon_192481.svg" class="msg-user-icon" alt="user">
                        <span>Moi (Visiteur)</span>
                    </div>
                    ${!msg.is_read_by_group ? '<div class="msg-status-dot" style="background-color: var(--orange); box-shadow: none;" title="Non lu par le groupe"></div>' : ''}
                </div>
                <p class="msg-content">${msg.visitor_message}</p>
                <p class="msg-recipient" style="font-size:0.75rem; color:#888; margin: 4px 0 0 0; text-align:right; font-style:italic;">à ${msg.group ? (msg.group.project_name || msg.group.name || 'Groupe') : 'Groupe'}</p>
            `;

            // If group replied, append the black block
            if (hasReply) {
                html += `
                <div class="message-block group-msg" style="margin-top: 10px;">
                    <div class="msg-header">
                        <div class="msg-user-info">
                            <img src="${msg.group.project_image ? '/' + msg.group.project_image : '/ICON/group.svg'}" onerror="this.src='/ICON/profile_user_avatar_person_icon_192481.svg'" class="msg-user-icon" alt="group">
                            <span>${msg.group.project_name || msg.group.name || 'Groupe'}</span>
                        </div>
                        <button class="msg-reply-btn" data-group-id="${msg.group.id}"><img src="/ICON/reply-icon.svg" onerror="this.style.display='none'"> RÉPONDRE</button>
                    </div>
                    <p class="msg-content">${msg.group_reply}</p>
                    <div class="msg-footer">
                        ${!msg.is_read_by_visitor ? '<div class="msg-status-dot" title="Nouveau message"></div>' : ''}
                    </div>
                </div>
                `;
            }

            block.innerHTML = html;
            fragment.appendChild(block);
        });

        msgList.innerHTML = '';
        msgList.appendChild(fragment);
    }

    // Open Modale
    if (bellBtn) {
        bellBtn.addEventListener('click', () => {
            msgsOverlay.classList.add('open');
            loadMessages();
        });
    }

    // Close Modale
    if (closeMsgsBtn) {
        closeMsgsBtn.addEventListener('click', () => {
            msgsOverlay.classList.remove('open');
            checkUnread(); // recalculate indicator on close
        });
    }

    // Clear messages
    if (clearBtn) {
        clearBtn.addEventListener('click', async () => {
            if (confirm('Supprimer tous vos messages de la messagerie ?')) {
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    await fetch('/visiteur/messages/clear', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
                    loadMessages();
                } catch (e) { console.error('Clear error', e); }
            }
        });
    }

    // Delegated reply button click
    msgList.addEventListener('click', (e) => {
        const replyBtn = e.target.closest('.msg-reply-btn');
        if (replyBtn) {
            const groupId = replyBtn.dataset.groupId;
            const parentBlock = replyBtn.closest('.group-msg');

            // Remove existing editors
            document.querySelectorAll('.reply-editor-container').forEach(el => el.remove());

            const editor = document.createElement('div');
            editor.className = 'reply-editor-container';
            editor.innerHTML = `
                <textarea class="reply-textarea" placeholder="Écrire une nouvelle réponse..."></textarea>
                <button class="send-reply-btn" data-group-id="${groupId}">Envoyer</button>
            `;
            parentBlock.appendChild(editor);
            setTimeout(() => editor.querySelector('textarea').focus(), 50);
        }

        const sendBtn = e.target.closest('.send-reply-btn');
        if (sendBtn) {
            const groupId = sendBtn.dataset.groupId;
            const text = sendBtn.previousElementSibling.value.trim();
            if (text) {
                sendNewMessage(groupId, text, sendBtn.closest('.reply-editor-container'));
            }
        }
    });

    async function sendNewMessage(groupId, text, editorRef) {
        if (editorRef) editorRef.innerHTML = 'Envoi...';
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch('/visiteur/messages/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ group_id: groupId, message: text })
            });
            const data = await res.json();
            if (data.success) {
                loadMessages(); // reload to show the new message at top
            } else {
                alert("Erreur d'envoi");
                if (editorRef) editorRef.remove();
            }
        } catch (e) {
            console.error(e);
            if (editorRef) editorRef.remove();
        }
    }

    // Attach rapid contact form event handler via delegation since bands are dynamic
    document.body.addEventListener('click', async (e) => {
        if (e.target.classList.contains('contact-quick-valider')) {
            const btn = e.target;
            const groupId = btn.dataset.groupId;
            const textarea = btn.previousElementSibling;
            const text = textarea ? textarea.value.trim() : '';

            if (!groupId) {
                console.error('group_id introuvable sur le bouton Valider');
                return;
            }

            if (!text) {
                alert('Veuillez écrire un message avant de valider.');
                textarea.focus();
                return;
            }

            // Disable button to prevent double-send
            btn.disabled = true;
            btn.textContent = '...';

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const res = await fetch('/visiteur/messages/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({ group_id: parseInt(groupId), message: text })
                });

                const data = await res.json();
                if (data.success) {
                    textarea.value = '';
                    btn.textContent = '✓ Envoyé!';
                    btn.style.backgroundColor = '#28a745';
                    setTimeout(() => {
                        btn.textContent = 'Valider';
                        btn.style.backgroundColor = '';
                        btn.disabled = false;
                    }, 2500);
                    // Update notification dot
                    checkUnread();
                } else {
                    alert('Erreur: ' + (data.message || 'Impossible d\'envoyer le message.'));
                    btn.textContent = 'Valider';
                    btn.disabled = false;
                }
            } catch (err) {
                console.error('Erreur réseau:', err);
                alert('Problème réseau. Veuillez réessayer.');
                btn.textContent = 'Valider';
                btn.disabled = false;
            }
        }
    });

    // Init notifications checker
    checkUnread();

    // --- TEMPS REEL avec Laravel Echo ---
    const visitorIdMeta = document.querySelector('meta[name="user-id"]');
    const visitorId = visitorIdMeta ? parseInt(visitorIdMeta.content) : null;

    if (visitorId) {
        // On attend que Echo soit disponible (peut prendre un instant après le chargement du module Vite)
        function initVisitorEcho(retries) {
            if (window.Echo) {
                console.log('[Echo] Visiteur: connexion au canal visitor.messages.' + visitorId);
                window.Echo.private(`visitor.messages.${visitorId}`)
                    .listen('.reply.received', (data) => {
                        console.log('[Echo] Nouvelle réponse reçue en temps réel', data);
                        if (notifDot) notifDot.style.display = 'block'; // Alerte instantanée visuelle
                        checkUnread();
                        if (document.getElementById('messages-overlay')?.classList.contains('open')) {
                            loadMessages();
                        }
                    })
                    .error((err) => {
                        console.error('[Echo] Erreur canal visiteur:', err);
                    });

                // Écoute des mises à jour globales (Canal public)
                console.log('[Echo] Visiteur: connexion au canal public.updates');
                window.Echo.channel('public.updates')
                    .listen('.group.deleted', (data) => {
                        console.log('[Echo] Suppression de groupe reçue:', data);
                        if (data && data.groupId) {
                            // Supprimer du tableau global
                            const index = window.serverGroupsData.findIndex(g => g.id === parseInt(data.groupId));
                            if (index !== -1) {
                                window.serverGroupsData.splice(index, 1);
                                console.log('[Echo] Groupe retiré de la mémoire locale.');

                                // Rafraîchir la liste si l'onglet projet est actif
                                if (document.getElementById('projects-section').classList.contains('active')) {
                                    renderProjectsList(document.getElementById('projectSearch')?.value || '');
                                }

                                // Fermer le panneau latéral s'il était ouvert pour ce groupe supprimé
                                const panel = document.getElementById('project-side-panel');
                                if (panel && panel.classList.contains('open')) {
                                    const submitBtn = document.getElementById('contact-quick-submit');
                                    // S'assurer qu'on ferme bien le panneau du groupe supprimé
                                    if (submitBtn && parseInt(submitBtn.dataset.groupId) === parseInt(data.groupId)) {
                                        closeProjectPanel();
                                    }
                                }
                            }
                        }
                    })
                    .listen('.event.published', (data) => {
                        console.log('[Echo] Mise à jour événement reçue:', data);
                        if (data && data.event && data.action) {
                            if (data.action === 'updated') {
                                const index = eventsData.findIndex(e => e.id === data.event.id);
                                if (index !== -1) {
                                    // L'événement existe déjà : mise à jour simple
                                    eventsData[index] = data.event;
                                } else {
                                    // L'événement n'existe pas encore : première publication via "Valider"
                                    eventsData.unshift(data.event);
                                    const dot = document.getElementById('events-notif-dot');
                                    if (dot) dot.style.display = 'inline-block';
                                }
                            } else if (data.action === 'created') {
                                // Cas rarissime (garde pour compatibilité)
                                eventsData.unshift(data.event);
                                const dot = document.getElementById('events-notif-dot');
                                if (dot) dot.style.display = 'inline-block';
                            } else if (data.action === 'deleted') {
                                eventsData = eventsData.filter(e => e.id !== data.event.id);
                            }

                            // Rafraîchir les listes
                            console.log('[Echo] Mise à jour du rendu des événements (total: ' + eventsData.length + ')');
                            renderEventsList();

                            // Mettre à jour ou fermer la modale évènement si ouverte
                            const modal = document.getElementById('event-modal-fullscreen');
                            if (modal && modal.classList.contains('active')) {
                                // Find event details in DOM (hacky, let's just close/reopen or close if deleted)
                                if (data.action === 'deleted') {
                                    closeEventModal();
                                } else {
                                    // It's harder to know WHICH event is open, so we just blindly update if the title matches or close
                                    const modalTitle = document.getElementById('modal-event-title').textContent;
                                    const oldEvent = eventsData.find(e => e.title === modalTitle || e.id === data.event.id);
                                    if (oldEvent && oldEvent.id === data.event.id) {
                                        openEventModalFullscreen(data.event);
                                    }
                                }
                            }
                        }
                    });
            } else if (retries > 0) {
                setTimeout(() => initVisitorEcho(retries - 1), 200);
            } else {
                console.warn('[Echo] Non disponible, fallback polling toutes les 10s');
                setInterval(checkUnread, 10000);
            }
        }
        initVisitorEcho(50); // Essaie pendant 10 secondes (50 x 200ms)
    } else {
        setInterval(checkUnread, 10000);
    }
    // --- SUPPRESSION DE COMPTE ---
    const deleteAccountBtn = document.getElementById('delete-account-btn');
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            if (confirm('Êtes-vous sûr de vouloir supprimer votre compte visiteur ? Cette action est irréversible.')) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const response = await fetch('/visiteur/account', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert('Votre compte a été supprimé avec succès.');
                        window.location.href = '/';
                    } else {
                        alert(data.error || 'Une erreur est survenue.');
                    }
                } catch (error) {
                    console.error('Error deleting account:', error);
                    alert('Erreur réseau lors de la suppression.');
                }
            }
        });
    }
});
