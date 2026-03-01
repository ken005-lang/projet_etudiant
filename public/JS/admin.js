document.addEventListener('DOMContentLoaded', () => {
    // === Tab Switching Logic ===
    const tabs = document.querySelectorAll('.admin-nav-item');
    const panes = document.querySelectorAll('.admin-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');

            // Remove active classes
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            // Add active class to clicked tab and its pane
            tab.classList.add('active');
            document.getElementById(`${target}-pane`).classList.add('active');
        });
    });

    // === Event Accordion Logic (Moved to consolidated delegator) ===

    // === Logout (Placeholder) ===
    const logoutBtn = document.querySelector('.admin-logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            if (confirm('Voulez-vous vous déconnecter ?')) {
                window.location.href = 'admin_login.html';
            }
        });
    }

    // Helper function to get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // === Access Code Logic ===
    const codeInput = document.getElementById('code-input');
    const createCodeBtn = document.getElementById('create-code-btn');
    const codeList = document.getElementById('code-list');

    if (createCodeBtn && codeInput && codeList) {
        createCodeBtn.addEventListener('click', async () => {
            const codeValue = codeInput.value.trim();

            if (codeValue === "") {
                alert("Veuillez saisir un code id.");
                return;
            }

            // Check for duplicates
            const existingCodes = Array.from(codeList.querySelectorAll('.code-list-item'))
                .map(item => item.textContent.trim());

            if (existingCodes.includes(codeValue)) {
                alert("Ce code existe déjà dans la liste.");
                return;
            }

            try {
                // Send to backend
                const response = await fetch('/admin/codes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: codeValue })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    alert("Erreur lors de la création : " + (errorData.message || "Une erreur est survenue."));
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    // Create new code row
                    const newRow = document.createElement('div');
                    newRow.className = 'code-item-row';
                    newRow.setAttribute('data-id', data.code.id);
                    newRow.innerHTML = `
                        <span class="code-list-item">${data.code.code}</span>
                        <img src="ICON/trash-fill.svg" alt="delete" class="delete-code-icon">
                    `;

                    // Append to list
                    codeList.appendChild(newRow);

                    // Clear input
                    codeInput.value = "";
                }
            } catch (error) {
                console.error("Erreur serveur:", error);
                alert("Erreur de connexion au serveur.");
            }
        });

        // Event delegation for deleting codes
        codeList.addEventListener('click', async (e) => {
            if (e.target.classList.contains('delete-code-icon')) {
                const row = e.target.closest('.code-item-row');
                if (row && confirm('Voulez-vous supprimer ce code id ?')) {
                    const codeId = row.getAttribute('data-id');

                    if (codeId) {
                        try {
                            const response = await fetch(`/admin/codes/${codeId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json'
                                }
                            });

                            if (!response.ok) {
                                alert("Erreur lors de la suppression.");
                                return;
                            }

                            row.remove();
                        } catch (error) {
                            console.error("Erreur de suppression:", error);
                            alert("Erreur réseau lors de la suppression.");
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
    if (groupsTableBody) {
        groupsTableBody.addEventListener('click', async (e) => {
            if (e.target.classList.contains('action-icon')) {
                const row = e.target.closest('tr');
                if (!row) return;

                const groupId = e.target.getAttribute('data-id');

                if (confirm("Voulez-vous vraiment supprimer ce groupe et libérer son code d'accès ?")) {
                    try {
                        const response = await fetch(`/admin/groups/${groupId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            alert("Erreur lors de la suppression du groupe.");
                            return;
                        }

                        // Remove row visually
                        row.remove();
                        // Optional: Reload page to refresh counts and access code list
                        window.location.reload();
                    } catch (error) {
                        console.error("Erreur de suppression:", error);
                        alert("Erreur réseau lors de la suppression.");
                    }
                }
            }
        });
    }

    // === Events Logic (Creation, Update, Media & Deletion) ===
    const eventInput = document.getElementById('admin-event-input');
    const addEventBtn = document.getElementById('add-event-btn');
    const eventsList = document.getElementById('admin-events-list');

    if (addEventBtn && eventInput && eventsList) {

        // Helper function: Update Event Text (Title & Description)
        const updateEventText = async (eventId, title, description, buttonToReset = null) => {
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
                        description: description
                    })
                });

                if (!response.ok) {
                    alert("Erreur lors de la mise à jour de l'événement.");
                } else if (buttonToReset) {
                    buttonToReset.textContent = 'Valider'; // Reset button text on success
                }
            } catch (error) {
                console.error("Erreur de mise à jour:", error);
                alert("Erreur réseau.");
            }
        };

        // --- Create Event ---
        addEventBtn.addEventListener('click', async () => {
            const val = eventInput.value.trim();
            if (val === "") {
                alert("Veuillez saisir le nom et la date de l'évènement.");
                return;
            }

            try {
                const response = await fetch('/admin/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ title: val })
                });

                if (!response.ok) {
                    alert("Erreur lors de la création de l'événement.");
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
                            <input type="text" value="${event.title}" class="event-title-edit">
                            <div class="header-buttons">
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
                    eventInput.value = "";
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

            // 1. Delete Logic
            if (trash && item) {
                if (confirm('Voulez-vous supprimer cet événement définitivement ?')) {
                    const eventId = item.getAttribute('data-id');
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
                        } else {
                            alert("Erreur lors de la suppression.");
                        }
                    } catch (error) {
                        alert("Erreur réseau.");
                    }
                }
                return;
            }

            // 2. Button Save Logic (Valider -> Publier)
            if (publishBtn && item) {
                const eventId = item.getAttribute('data-id');
                const title = item.querySelector('.event-title-edit').value;
                const description = item.querySelector('.event-desc-edit').value;

                publishBtn.textContent = 'Enregistrement...';
                await updateEventText(eventId, title, description, publishBtn);

                // Keep it on 'Publier' visually if user wants (optional). Let's reset to Valider automatically for feedback.
                return;
            }

            // 3. Toggle Accordion Logic
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
                                    placeholder.style.backgroundSize = 'cover';
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
                                }
                            } else {
                                alert('Erreur: ' + response.message);
                                if (pTag) pTag.textContent = isImage ? 'upload image' : 'upload video';
                            }
                        } catch (err) {
                            alert('Erreur parsing JSON.');
                        }
                    } else {
                        alert('Erreur serveur lors de l\'upload.');
                        if (pTag) pTag.textContent = isImage ? 'upload image' : 'upload video';
                    }
                    input.value = ''; // Reset
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
});
