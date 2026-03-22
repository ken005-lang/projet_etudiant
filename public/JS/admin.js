document.addEventListener('DOMContentLoaded', () => {
    // === Tab Switching Logic (with localStorage persistence) ===
    const tabs = document.querySelectorAll('.admin-nav-item');
    const panes = document.querySelectorAll('.admin-pane');

    function activateTab(tabName) {
        tabs.forEach(t => t.classList.remove('active'));
        panes.forEach(p => p.classList.remove('active'));
        const targetTab = document.querySelector(`.admin-nav-item[data-tab="${tabName}"]`);
        const targetPane = document.getElementById(`${tabName}-pane`);
        if (targetTab && targetPane) {
            targetTab.classList.add('active');
            targetPane.classList.add('active');
        }
    }

    // Restore saved tab on page load
    const savedTab = localStorage.getItem('admin_active_tab');
    if (savedTab) {
        activateTab(savedTab);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');
            activateTab(target);
            localStorage.setItem('admin_active_tab', target);
        });
    });

    // === Event Accordion Logic (Moved to consolidated delegator) ===

    // === Logout (Placeholder) ===
    const logoutBtn = document.querySelector('.admin-logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            if (confirm('Voulez-vous vous déconnecter ?')) {
                window.location.href = '/logout';
            }
        });
    }

    // Helper function to get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // === Access Code Logic ===
    const createCodeBtn = document.getElementById('create-code-btn');
    const codeList = document.getElementById('code-list');

    if (createCodeBtn && codeList) {
        createCodeBtn.addEventListener('click', async () => {
            const originalHTML = createCodeBtn.innerHTML;
            if (window.setBtnLoading) window.setBtnLoading(createCodeBtn, true);
            else createCodeBtn.classList.add('loading-btn');

            try {
                // Send to backend (no payload needed)
                const response = await fetch('/admin/codes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({}) // Paramètres vides
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.success) {
                        // Create new row
                        const newRow = document.createElement('div');
                        newRow.className = 'code-item-row';
                        newRow.setAttribute('data-id', data.code.id);
                        
                        // We use innerHTML for simplicity
                        newRow.innerHTML = `
                            <span class="code-list-item">${data.code.code}</span>
                            <img src="/ICON/trash-fill.svg" alt="delete" class="delete-code-icon">
                        `;

                        // Add to list (at the top, under header)
                        const header = codeList.querySelector('.code-list-header');
                        if (header && header.nextSibling) {
                            codeList.insertBefore(newRow, header.nextSibling);
                        } else if (header) {
                            codeList.appendChild(newRow);
                        } else {
                            codeList.appendChild(newRow);
                        }
                    } else {
                        throw new Error(data.message || 'Error creating code.');
                    }
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Server error.');
                }
            } catch (error) {
                console.error("Erreur serveur:", error);
                alert("Erreur de connexion au serveur : " + error.message);
            } finally {
                if (window.setBtnLoading) window.setBtnLoading(createCodeBtn, false);
                else createCodeBtn.classList.remove('loading-btn');
            }
        });

        // Event delegation for deleting codes
        codeList.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-code-icon')) {
                const row = e.target.closest('.code-item-row');
                if (row && confirm('Voulez-vous supprimer ce code id ?')) {
                    const codeId = row.getAttribute('data-id');

                    if (codeId) {
                        const originalIcon = e.target;
                        const spinner = document.createElement('div');
                        spinner.className = 'delete-spinner dark';
                        
                        // Remplacer l'icône par le spinner
                        originalIcon.style.display = 'none';
                        originalIcon.parentNode.insertBefore(spinner, originalIcon);

                        try {
                            const response = await fetch(`/admin/codes/${codeId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json'
                                }
                            });

                            if (!response.ok) {
                                let errMsg = `Erreur lors de la suppression. Statut : ${response.status} ${response.statusText}`;
                                try {
                                    const errData = await response.json();
                                    if (errData.message) errMsg += `\nDétail : ${errData.message}`;
                                    if (errData.error) errMsg += `\nErreur : ${errData.error}`;
                                    console.error("Full error data:", errData);
                                } catch (e) {
                                    console.error("Could not parse JSON error response.");
                                }
                                alert(errMsg);
                            } else {
                                row.remove();
                            }
                        } catch (error) {
                            console.error("Erreur de suppression:", error);
                            alert("Erreur réseau lors de la suppression.");
                        } finally {
                            if (spinner.parentNode) {
                                spinner.remove();
                                originalIcon.style.display = 'inline-block';
                            }
                        }
                    } else {
                        row.remove();
                    }
                }
            }
        });
    }

    // === Groups Table Logic ===
    const groupsTableBody = document.querySelector('.groups-table tbody');
    const groupsSearchInput = document.getElementById('groups-search-input');
    const groupsFilterBtn = document.getElementById('groups-filter-btn');
    const groupsRefreshBtn = document.getElementById('groups-refresh-btn');

    // Filtering logic
    function filterGroupsTable() {
        if (!groupsTableBody || !groupsSearchInput) return;
        const searchTerm = groupsSearchInput.value.toLowerCase().trim();
        const rows = groupsTableBody.querySelectorAll('tr');

        rows.forEach(row => {
            // S'il s'agit de la ligne "Aucun groupe inscrit", on la laisse si le champ est vide
            if (row.querySelector('td[colspan]')) return;

            const textContent = row.textContent.toLowerCase();
            if (textContent.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (groupsSearchInput) {
        // Filter as the user types
        groupsSearchInput.addEventListener('input', filterGroupsTable);
        
        // Handle "Enter" key
        groupsSearchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterGroupsTable();
            }
        });
    }

    if (groupsFilterBtn) {
        groupsFilterBtn.addEventListener('click', filterGroupsTable);
    }

    if (groupsRefreshBtn) {
        groupsRefreshBtn.addEventListener('click', () => {
            if (groupsSearchInput) groupsSearchInput.value = '';
            window.location.reload();
        });
    }

    if (groupsTableBody) {
        groupsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('action-icon')) {
                const row = e.target.closest('tr');
                if (!row) return;

                const groupId = e.target.getAttribute('data-id');

                if (confirm("Voulez-vous vraiment supprimer ce groupe et libérer son code d'accès ?")) {
                    const originalIcon = e.target;
                    const spinner = document.createElement('div');
                    spinner.className = 'delete-spinner dark';
                    
                    originalIcon.style.display = 'none';
                    originalIcon.parentNode.insertBefore(spinner, originalIcon);

                    try {
                        const response = await fetch(`/admin/groups/${groupId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            alert("Erreur lors la suppression du groupe.");
                            spinner.remove();
                            originalIcon.style.display = 'inline-block';
                            return;
                        }

                        // Remove row visually
                        row.remove();
                        // Reload page to refresh counts and access code list
                        window.location.reload();
                    } catch (error) {
                        console.error("Erreur de suppression:", error);
                        alert("Erreur réseau lors de la suppression.");
                        spinner.remove();
                        originalIcon.style.display = 'inline-block';
                    }
                }
            }
        });
    }

    // === Visitors Table Logic ===
    const visitorsTableBody = document.querySelector('.visitors-table tbody');
    if (visitorsTableBody) {
        visitorsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-visitor-btn')) {
                const row = e.target.closest('tr');
                if (!row) return;

                const visitorId = e.target.getAttribute('data-id');

                if (confirm("Voulez-vous vraiment supprimer ce visiteur ?")) {
                    const originalIcon = e.target;
                    const spinner = document.createElement('div');
                    spinner.className = 'delete-spinner dark';
                    
                    originalIcon.style.display = 'none';
                    originalIcon.parentNode.insertBefore(spinner, originalIcon);

                    try {
                        const response = await fetch(`/admin/visitors/${visitorId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            alert("Erreur lors de la suppression du visiteur.");
                            spinner.remove();
                            originalIcon.style.display = 'inline-block';
                            return;
                        }

                        // Remove row visually
                        row.remove();
                        // Reload page to refresh counts
                        window.location.reload();
                    } catch (error) {
                        console.error("Erreur de suppression:", error);
                        alert("Erreur réseau lors de la suppression.");
                        spinner.remove();
                        originalIcon.style.display = 'inline-block';
                    }
                }
            }
        });
    }

    // === Events Logic (Creation, Update, Media & Deletion) ===
    const eventNameInput = document.getElementById('admin-event-name-input');
    const eventDateInput = document.getElementById('admin-event-date-input');
    const addEventBtn = document.getElementById('add-event-btn');
    const eventsList = document.getElementById('admin-events-list');

    if (addEventBtn && eventNameInput && eventsList) {

        // Helper function: Update Event Text (Title & Description) & handles Publication
        const updateEventText = async (eventId, title, description, buttonToReset = null, publish = false) => {
            try {
                const response = await fetch(`/admin/events/${eventId}`, {
                    method: 'POST', // Using POST instead of PUT/PATCH for simpler FormData matching or direct JSON
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        title: title,
                        description: description,
                        publish: publish
                    })
                });

                if (!response.ok) {
                    alert("Erreur lors de la mise à jour de l'événement.");
                } else if (buttonToReset) {
                    buttonToReset.textContent = publish ? 'Publié' : 'Valider';
                    if (publish) {
                        buttonToReset.style.backgroundColor = '#4CAF50';
                        buttonToReset.style.color = '#fff';
                        setTimeout(() => {
                            buttonToReset.textContent = 'Valider';
                            buttonToReset.style.backgroundColor = '';
                            buttonToReset.style.color = '';
                        }, 2000);
                    }
                }
            } catch (error) {
                console.error("Erreur de mise à jour:", error);
                alert("Erreur réseau.");
            }
        };

        // --- Create Event ---
        addEventBtn.addEventListener('click', async () => {
            const name = eventNameInput.value.trim();
            const date = eventDateInput ? eventDateInput.value.trim() : '';

            if (name === "") {
                alert("Veuillez saisir le nom de l'évènement.");
                return;
            }

            // Combine name and date with " | " separator
            const title = date ? `${name} | ${date}` : name;

            if (window.setBtnLoading) window.setBtnLoading(addEventBtn, true);
            else addEventBtn.classList.add('loading-btn');

            try {
                const response = await fetch('/admin/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ title: title })
                });

                if (!response.ok) {
                    if (response.status === 419) {
                        alert("Votre session a expiré. La page va se recharger, veuillez réessayer.");
                        window.location.reload();
                        return;
                    }
                    let errMsg = "Erreur lors de la création de l'événement.";
                    try {
                        const errData = await response.json();
                        if (errData.message) errMsg += " (" + errData.message + ")";
                    } catch (_) { }
                    alert(errMsg);
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    const event = data.event;

                    // Remove empty state message if it exists
                    const emptyMsg = eventsList.querySelector('div[style*="text-align: center"]');
                    if (emptyMsg) emptyMsg.remove();

                    // Create basic structure for new event
                    const newItem = document.createElement('div');
                    newItem.className = 'event-accordion-item';
                    newItem.setAttribute('data-id', event.id);
                    newItem.innerHTML = `
                        <div class="event-header">
                            <input type="text" value="${event.title}" class="event-title-edit" readonly>
                            <div class="header-buttons">
                                <button class="btn-pill-small white-btn rewrite-btn">Réécrire</button>
                                <button class="btn-pill-small white-btn toggle-publish-btn">Valider</button>
                                <img src="ICON/arrow-down_icon.svg" alt="expand" class="expand-arrow">
                                <img src="ICON/trash-fill.svg" alt="delete" class="action-icon">
                            </div>
                        </div>
                        <div class="event-content">
                            <div class="event-grid">
                                <div class="media-upload-placeholder image-place">
                                    <p>upload image</p>
                                    <input type="file" accept="image/*" class="hidden-file-input image-input" style="display: none;">
                                </div>
                                <div class="event-text-multimedia">
                                    <div class="textarea-container">
                                        <textarea class="event-desc-edit" placeholder="Description de l'évènement" maxlength="1000"></textarea>
                                        <span class="char-count">0/1000</span>
                                    </div>
                                    <div class="media-upload-placeholder video-place">
                                        <p>upload video</p>
                                        <input type="file" accept="video/*" class="hidden-file-input video-input" style="display: none;">
                                    </div>
                                    <div class="event-media-progress" style="display: none; width: 100%; text-align: center; margin-top: 5px;">
                                        <div style="width: 100%; background-color: #eee; border-radius: 5px; height: 5px; overflow: hidden;">
                                            <div class="event-media-bar" style="width: 0%; height: 100%; background-color: var(--black); transition: width 0.2s;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    eventsList.prepend(newItem); // Add at the top
                    eventNameInput.value = "";
                    if (eventDateInput) eventDateInput.value = "";
                }
            } catch (error) {
                console.error("Erreur serveur:", error);
                alert("Erreur de connexion au serveur.");
            }
        });

        // --- Delegation (Toggling, Deletion & Updates) ---
        eventsList.addEventListener('click', async (e) => {
            const header = e.target.closest('.event-header');
            const trash = e.target.closest('.action-icon');
            const publishBtn = e.target.closest('.toggle-publish-btn');
            const item = e.target.closest('.event-accordion-item');
            if (header && !trash && !publishBtn) {
                item.classList.toggle('active');
                return;
            }

            if (trash && item) {
                if (confirm('Voulez-vous supprimer cet événement définitivement ?')) {
                    const eventId = item.getAttribute('data-id');
                    
                    const originalIcon = trash;
                    const spinner = document.createElement('div');
                    spinner.className = 'delete-spinner dark';
                    
                    originalIcon.style.display = 'none';
                    originalIcon.parentNode.insertBefore(spinner, originalIcon);

                    try {
                        const response = await fetch(`/admin/events/${eventId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            item.remove();
                        } else if (response.status === 419) {
                            alert("Votre session a expiré. La page va se recharger, veuillez réessayer.");
                            window.location.reload();
                        } else {
                            let errMsg = "Erreur lors de la suppression.";
                            try {
                                const errData = await response.json();
                                if (errData.message) errMsg += " (" + errData.message + ")";
                            } catch (_) { }
                            alert(errMsg);
                            spinner.remove();
                            originalIcon.style.display = 'inline-block';
                        }
                    } catch (error) {
                        alert("Erreur réseau.");
                        spinner.remove();
                        originalIcon.style.display = 'inline-block';
                    }
                }
            }

            // 2. Réécrire button logic
            const rewriteBtn = e.target.closest('.rewrite-btn');
            if (rewriteBtn && item) {
                const titleInput = item.querySelector('.event-title-edit');
                const descTextarea = item.querySelector('.event-desc-edit');
                const isReadonly = titleInput.hasAttribute('readonly');

                if (isReadonly) {
                    // Enable editing
                    titleInput.removeAttribute('readonly');
                    titleInput.focus();
                    titleInput.style.border = '2px solid #ff6600';
                    titleInput.style.backgroundColor = '#fff';
                    if (descTextarea) {
                        descTextarea.style.border = '2px solid #ff6600';
                    }
                    rewriteBtn.textContent = 'Terminer';
                    rewriteBtn.style.backgroundColor = '#ff6600';
                    rewriteBtn.style.color = '#fff';

                    // Expand the accordion so user can also edit description
                    if (!item.classList.contains('expanded')) {
                        item.classList.add('expanded');
                        const arrow = item.querySelector('.expand-arrow');
                        if (arrow) arrow.src = 'ICON/up-arrow_icon.svg';
                    }
                } else {
                    // Save and disable editing
                    titleInput.setAttribute('readonly', true);
                    titleInput.style.border = '';
                    titleInput.style.backgroundColor = '';
                    if (descTextarea) {
                        descTextarea.style.border = '';
                    }
                    rewriteBtn.textContent = 'Réécrire';
                    rewriteBtn.style.backgroundColor = '';
                    rewriteBtn.style.color = '';

                    // Auto-save changes
                    const eventId = item.getAttribute('data-id');
                    const title = titleInput.value;
                    const description = descTextarea ? descTextarea.value : '';
                    updateEventText(eventId, title, description);
                }
                return;
            }

            // 3. Button Save Logic (Valider -> Publier)
            if (publishBtn && item) {
                const eventId = item.getAttribute('data-id');
                const title = item.querySelector('.event-title-edit').value;
                const description = item.querySelector('.event-desc-edit').value;

                publishBtn.textContent = 'Chargement...';
                publishBtn.disabled = true;
                await updateEventText(eventId, title, description, publishBtn, true);

                publishBtn.disabled = false;
                // Keep it on 'Publier' visually if user wants (optional). Let's reset to Valider automatically for feedback.
                return;
            }

            // 4. Toggle Accordion Logic
            if (header) {
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') {
                    return;
                }

                const isExpanded = item.classList.contains('expanded');
                item.classList.toggle('expanded');

                const arrow = header.querySelector('.expand-arrow');
                if (item.classList.contains('expanded')) {
                    if (arrow) arrow.src = 'ICON/up-arrow_icon.svg';
                } else {
                    if (arrow) arrow.src = 'ICON/arrow-down_icon.svg';
                }
            }
        });

        // 4. Character Count Logic
        eventsList.addEventListener('input', (e) => {
            if (e.target.classList.contains('event-desc-edit')) {
                const countSpan = e.target.parentElement.querySelector('.char-count');
                if (countSpan) {
                    countSpan.textContent = `${e.target.value.length}/1000`;
                }
                // Optionnel : auto-save sur input. Vaut mieux utiliser change (blur) ou le bouton Valider.
            }
        });

        // 5. Auto-save on blur for title and description
        eventsList.addEventListener('change', (e) => {
            if (e.target.classList.contains('event-title-edit') || e.target.classList.contains('event-desc-edit')) {
                const item = e.target.closest('.event-accordion-item');
                const eventId = item.getAttribute('data-id');
                const title = item.querySelector('.event-title-edit').value;
                const description = item.querySelector('.event-desc-edit').value;
                updateEventText(eventId, title, description); // Silent background save
            }
        });

        // 6. Media Upload Trigger Logic
        eventsList.addEventListener('click', (e) => {
            const placeholder = e.target.closest('.media-upload-placeholder');
            if (placeholder) {
                if (e.target.tagName === 'INPUT' && e.target.type === 'file') {
                    return; // Let native browser input handle it
                }
                const fileInput = placeholder.querySelector('.hidden-file-input');
                if (fileInput) fileInput.click();
            }
        });

        // 7. Media Upload Handling (XMLHttpRequest for progress)
        eventsList.addEventListener('change', (e) => {
            if (e.target.classList.contains('hidden-file-input')) {
                const file = e.target.files[0];
                if (!file) return;

                const input = e.target;
                const isImage = input.classList.contains('image-input');
                const mediaTypeStr = isImage ? 'image' : 'video';

                // Size validation
                const maxSize = isImage ? 20 * 1024 * 1024 : 512 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert(`Fichier trop volumineux. Max: ${isImage ? '20 Mo' : '500 Mo'}`);
                    input.value = '';
                    return;
                }

                const item = input.closest('.event-accordion-item');
                const eventId = item.getAttribute('data-id');
                const placeholder = input.closest('.media-upload-placeholder');
                const pTag = placeholder.querySelector('p');

                // Prepare progress bar
                const progressContainer = item.querySelector('.event-media-progress');
                const progressBar = item.querySelector('.event-media-bar');
                if (progressContainer) {
                    progressContainer.style.display = 'block';
                    progressBar.style.width = '0%';
                }
                if (pTag) pTag.textContent = 'Upload en cours...';

                const formData = new FormData();
                formData.append(mediaTypeStr, file);
                formData.append('_token', getCsrfToken());

                const xhr = new XMLHttpRequest();
                xhr.open('POST', `/admin/events/${eventId}/media`, true);

                xhr.upload.onprogress = (event) => {
                    if (event.lengthComputable && progressBar) {
                        const percentComplete = Math.round((event.loaded / event.total) * 100);
                        progressBar.style.width = percentComplete + '%';
                        if (pTag) pTag.textContent = `Upload: ${percentComplete}%`;
                    }
                };

                xhr.onload = () => {
                    if (progressContainer) progressContainer.style.display = 'none';

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                if (isImage) {
                                    placeholder.style.backgroundImage = `url('${response.url}')`;
                                    placeholder.style.backgroundSize = 'contain';
                                    placeholder.style.backgroundRepeat = 'no-repeat';
                                    placeholder.style.backgroundPosition = 'center';
                                    placeholder.style.border = '2px solid #fff';
                                    if (pTag) pTag.remove(); // Hide text when image is shown
                                } else {
                                    if (pTag) {
                                        pTag.textContent = 'Vidéo sauvegardée';
                                        pTag.style.color = '#000';
                                        pTag.style.background = '#fff';
                                        pTag.style.padding = '2px 10px';
                                        pTag.style.borderRadius = '10px';
                                        pTag.style.fontWeight = 'bold';
                                    }

                                    // Update or create the target div for the video name
                                    let nameDiv = placeholder.parentElement.querySelector('.video-name-display');
                                    if (!nameDiv) {
                                        nameDiv = document.createElement('div');
                                        nameDiv.className = 'video-name-display';
                                        nameDiv.style.color = 'rgba(255,255,255,0.7)';
                                        nameDiv.style.fontSize = '0.8rem';
                                        nameDiv.style.marginTop = '8px';
                                        nameDiv.style.textAlign = 'center';
                                        nameDiv.style.wordBreak = 'break-all';
                                        // Insert it right after the placeholder
                                        placeholder.insertAdjacentElement('afterend', nameDiv);
                                    }
                                    nameDiv.textContent = 'Nom : ' + file.name;
                                }
                            } else {
                                alert('Erreur: ' + response.message);
                                if (pTag) pTag.textContent = isImage ? 'upload image' : 'upload video';
                            }
                        } catch (err) {
                            alert('Erreur parsing JSON.');
                        }
                    } else if (xhr.status === 419) {
                        alert("Votre session a expiré. La page va se recharger.");
                        window.location.reload();
                    } else {
                        let errMsg = "Erreur serveur lors de l'upload.";
                        try {
                            const errData = JSON.parse(xhr.responseText);
                            if (errData.message) errMsg += " (" + errData.message + ")";
                        } catch (_) { }
                        alert(errMsg);
                        if (pTag) pTag.textContent = isImage ? 'upload image' : 'upload video';
                    }
                    input.value = ''; // Reset to allow re-upload of same file
                };

                xhr.onerror = () => {
                    if (progressContainer) progressContainer.style.display = 'none';
                    alert('Erreur réseau.');
                    if (pTag) pTag.textContent = isImage ? 'upload image' : 'upload video';
                    input.value = '';
                };

                xhr.send(formData);
            }
        });
    }

    // --- TEMPS REEL (Laravel Echo / Reverb) ---
    // Écoute des nouvelles inscriptions (Visiteurs et Groupes)
    if (window.Echo) {
        console.log('[Echo] Admin: connexion au canal admin.notifications');
        window.Echo.private('admin.notifications')
            .listen('.user.registered', (data) => {
                console.log('[Echo] Nouvel utilisateur inscrit:', data);
                if (data && data.user) {
                    if (data.user.type_role === 'visiteur') {
                        // Incrémenter la stat Visiteur (cercle jaune)
                        const statNum = document.querySelector('.icon-circle-group.bg-yellow')?.nextElementSibling?.querySelector('.stat-number');
                        if (statNum) {
                            statNum.textContent = parseInt(statNum.textContent || '0') + 1;
                        }

                        // Ajouter dans le tableau
                        const visitorTableBody = document.querySelector('.visitors-table tbody');
                        if (visitorTableBody && data.profile_data) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${data.user.name}</td>
                                <td style="text-transform: capitalize;">${data.profile_data.gender}</td>
                                <td>${data.user.username}</td>
                                <td>À l'instant</td>
                                <td class="action-cell">
                                    <img src="/ICON/trash-fill.svg" alt="delete" class="action-icon delete-visitor-btn" data-id="${data.profile_data.id}">
                                </td>
                            `;
                            visitorTableBody.prepend(tr);
                        }
                    } else if (data.user.type_role === 'groupe') {
                        // Incrémenter la stat Groupes (cercle violet)
                        const statNum = document.querySelector('.icon-circle-group.bg-purple')?.nextElementSibling?.querySelector('.stat-number');
                        if (statNum) {
                            statNum.textContent = parseInt(statNum.textContent || '0') + 1;
                        }

                        // Ajouter dans le tableau
                        const groupTableBody = document.querySelector('.groups-table tbody');
                        if (groupTableBody && data.profile_data) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${data.profile_data.project_name}</td>
                                <td>${data.user.username}</td>
                                <td>${data.profile_data.leader_name}</td>
                                <td>${data.profile_data.leader_level}</td>
                                <td>${data.profile_data.leader_sector}</td>
                                <td>À l'instant</td>
                                <td class="actions-cell">
                                    <img src="/ICON/trash-fill.svg" alt="delete" class="action-icon" data-id="${data.profile_data.id}">
                                </td>
                            `;
                            groupTableBody.prepend(tr);
                        }

                        // Incrémenter les stats des projets au passage
                        const projectStatNum = document.querySelector('.icon-circle-group.bg-orange')?.nextElementSibling?.querySelector('.stat-number');
                        if (projectStatNum) {
                            projectStatNum.textContent = parseInt(projectStatNum.textContent || '0') + 1;
                        }
                    }
                }
            })
            .listen('.codeid.changed', (data) => {
                console.log('[Echo] Code ID modifié:', data);
                if (data && data.group_profile_id && data.new_code_id) {
                    // Find the matching row in the groups table
                    const row = document.querySelector(`.groups-table tbody tr[data-group-id="${data.group_profile_id}"]`);
                    if (row) {
                        // The Code ID is in the 2nd cell (index 1)
                        const codeCell = row.querySelectorAll('td')[1];
                        if (codeCell) {
                            codeCell.textContent = data.new_code_id;
                            // Visual flash to highlight the change
                            codeCell.style.transition = 'background-color 0.3s ease';
                            codeCell.style.backgroundColor = '#ff6600';
                            codeCell.style.color = '#fff';
                            codeCell.style.fontWeight = 'bold';
                            setTimeout(() => {
                                codeCell.style.backgroundColor = '';
                                codeCell.style.color = '';
                                codeCell.style.fontWeight = '';
                            }, 3000);
                        }
                    }
                }
            })
            .error((err) => {
                console.error('[Echo] Erreur connexion admin:', err);
            });

        // Écoute des suppressions globales (Ex: un groupe qui s'auto-supprime)
        window.Echo.channel('public.updates')
            .listen('.group.deleted', (data) => {
                console.log('[Echo] Admin: Suppression de groupe reçue:', data);
                if (data && data.groupId) {
                    // Trouver la corbeille correspondant à l'ID
                    const trashIcons = document.querySelectorAll('.groups-table tbody .action-icon');
                    trashIcons.forEach(icon => {
                        if (parseInt(icon.getAttribute('data-id')) === parseInt(data.groupId)) {
                            const row = icon.closest('tr');
                            if (row) {
                                row.remove();

                                // Décrémenter la stat Groupes (cercle violet)
                                const statNumGroup = document.querySelector('.icon-circle-group.bg-purple')?.nextElementSibling?.querySelector('.stat-number');
                                if (statNumGroup) {
                                    let currentVal = parseInt(statNumGroup.textContent || '0');
                                    statNumGroup.textContent = Math.max(0, currentVal - 1);
                                }

                                // Décrémenter la stat Projets (cercle orange)
                                const statNumProj = document.querySelector('.icon-circle-group.bg-orange')?.nextElementSibling?.querySelector('.stat-number');
                                if (statNumProj) {
                                    let currentVal = parseInt(statNumProj.textContent || '0');
                                    statNumProj.textContent = Math.max(0, currentVal - 1);
                                }
                            }
                        }
                    });
                }
            })
            .listen('.visitor.deleted', (data) => {
                console.log('[Echo] Admin: Suppression de visiteur reçue:', data);
                if (data && data.visitorId) {
                    // Trouver la corbeille correspondant à l'ID dans le tableau des visiteurs
                    const trashIcons = document.querySelectorAll('.visitors-table tbody .action-icon');
                    trashIcons.forEach(icon => {
                        if (parseInt(icon.getAttribute('data-id')) === parseInt(data.visitorId)) {
                            const row = icon.closest('tr');
                            if (row) {
                                row.remove();

                                // Décrémenter la stat Visiteurs (cercle jaune)
                                const statNumVis = document.querySelector('.icon-circle-group.bg-yellow')?.nextElementSibling?.querySelector('.stat-number');
                                if (statNumVis) {
                                    let currentVal = parseInt(statNumVis.textContent || '0');
                                    statNumVis.textContent = Math.max(0, currentVal - 1);
                                }
                            }
                        }
                    });
                }
            });
    }

});
