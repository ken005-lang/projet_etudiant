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

    // === Access Code Logic ===
    const codeInput = document.getElementById('code-input');
    const createCodeBtn = document.getElementById('create-code-btn');
    const codeList = document.getElementById('code-list');

    if (createCodeBtn && codeInput && codeList) {
        createCodeBtn.addEventListener('click', () => {
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

            // Create new code row
            const newRow = document.createElement('div');
            newRow.className = 'code-item-row';
            newRow.innerHTML = `
                <span class="code-list-item">${codeValue}</span>
                <img src="ICON/trash-fill.svg" alt="delete" class="delete-code-icon">
            `;

            // Append to list
            codeList.appendChild(newRow);

            // Clear input
            codeInput.value = "";
        });

        // Event delegation for deleting codes
        codeList.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-code-icon')) {
                const row = e.target.closest('.code-item-row');
                if (row && confirm('Voulez-vous supprimer ce code id ?')) {
                    row.remove();
                }
            }
        });
    }

    // === Groups Table Logic ===
    const groupsTableBody = document.querySelector('.groups-table tbody');
    if (groupsTableBody) {
        groupsTableBody.addEventListener('click', (e) => {
            if (e.target.classList.contains('action-icon')) {
                const row = e.target.closest('tr');
                if (!row) return;

                if (confirm('Voulez-vous supprimer ces informations ?')) {
                    row.remove();
                }
            }
        });
    }

    // === Events Logic (Creation & Delegation) ===
    const eventInput = document.getElementById('admin-event-input');
    const addEventBtn = document.getElementById('add-event-btn');
    const eventsList = document.getElementById('admin-events-list');

    if (addEventBtn && eventInput && eventsList) {
        // --- Create Event ---
        addEventBtn.addEventListener('click', () => {
            const val = eventInput.value.trim();
            if (val === "") {
                alert("Veuillez saisir le nom et la date de l'évènement.");
                return;
            }

            // Create basic structure for new event (collapsed by default)
            const newItem = document.createElement('div');
            newItem.className = 'event-accordion-item';
            newItem.innerHTML = `
                <div class="event-header">
                    <input type="text" value="${val}" class="event-title-edit">
                    <div class="header-buttons">
                        <button class="btn-pill-small white-btn toggle-publish-btn">Valider</button>
                        <img src="ICON/arrow-down_icon.svg" alt="expand" class="expand-arrow">
                        <img src="ICON/trash-fill.svg" alt="delete" class="action-icon">
                    </div>
                </div>
                <div class="event-content">
                    <div class="event-grid">
                        <div class="media-upload-placeholder image-place">
                            <img src="ICON/image_icon.svg" alt="upload image">
                            <input type="file" accept="image/*" class="hidden-file-input image-input" style="display: none;">
                        </div>
                        <div class="event-text-multimedia">
                            <div class="textarea-container">
                                <textarea placeholder="Description de l'évènement" maxlength="1000"></textarea>
                                <span class="char-count">0/1000</span>
                            </div>
                            <div class="media-upload-placeholder video-place">
                                <img src="ICON/video_icon.svg" alt="upload video">
                                <input type="file" accept="video/*" class="hidden-file-input video-input" style="display: none;">
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Prepend or Append
            eventsList.appendChild(newItem);
            eventInput.value = "";
        });

        // --- Delegation (Toggling & Deletion) ---
        eventsList.addEventListener('click', (e) => {
            const header = e.target.closest('.event-header');
            const trash = e.target.closest('.action-icon');

            // 1. Delete Logic
            if (trash) {
                const item = trash.closest('.event-accordion-item');
                if (item && confirm('Voulez-vous supprimer cet événement ?')) {
                    item.remove();
                }
                return;
            }

            // 2. Button Toggle Logic (Valider -> Publier)
            if (e.target.classList.contains('toggle-publish-btn')) {
                if (e.target.textContent === 'Valider') {
                    e.target.textContent = 'Publier';
                }
                return;
            }

            // 2. Toggle Logic
            if (header) {
                // If we click an input or button inside the header, don't toggle
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT') {
                    return;
                }

                const item = header.closest('.event-accordion-item');
                const isExpanded = item.classList.contains('expanded');

                // Toggle expansion
                item.classList.toggle('expanded');

                // Update arrow icon and reset button to "Valider" if opening
                const arrow = header.querySelector('.expand-arrow');
                const publishBtn = header.querySelector('.toggle-publish-btn');

                if (item.classList.contains('expanded')) {
                    if (arrow) arrow.src = 'ICON/up-arrow_icon.svg';
                    if (publishBtn) publishBtn.textContent = 'Valider';
                } else {
                    if (arrow) arrow.src = 'ICON/arrow-down_icon.svg';
                }
            }
        });

        // 4. Character Count Logic (using input event for real-time update)
        eventsList.addEventListener('input', (e) => {
            if (e.target.tagName === 'TEXTAREA') {
                const countSpan = e.target.parentElement.querySelector('.char-count');
                if (countSpan) {
                    countSpan.textContent = `${e.target.value.length}/1000`;
                }
            }
        });

        // 5. Media Upload Trigger Logic (moved back to click for clarity/consistency)
        eventsList.addEventListener('click', (e) => {
            const placeholder = e.target.closest('.media-upload-placeholder');
            if (placeholder) {
                // If we click the file input directly, let it propagate
                if (e.target.tagName === 'INPUT' && e.target.type === 'file') {
                    return;
                }
                const fileInput = placeholder.querySelector('.hidden-file-input');
                if (fileInput) {
                    fileInput.click();
                }
            }
        });

        // Prevention of event bubbling for file inputs if needed
        eventsList.addEventListener('change', (e) => {
            if (e.target.classList.contains('hidden-file-input')) {
                const file = e.target.files[0];
                if (file) {
                    const placeholder = e.target.closest('.media-upload-placeholder');
                    const p = placeholder.querySelector('p');
                    if (p) {
                        p.textContent = `Fichier: ${file.name}`;
                    } else {
                        // For items with images (dynamic template)
                        placeholder.style.border = "2px solid #fff";
                    }
                }
            }
        });
    }
});
