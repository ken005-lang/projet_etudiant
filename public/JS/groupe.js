document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Helper for profile updates
    async function updateProfile(data) {
        try {
            const response = await fetch('/groupe/update-profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (!result.success) {
                alert('Erreur lors de la sauvegarde : ' + result.message);
            }
            return result;
        } catch (error) {
            console.error('Update error:', error);
            alert('Erreur réseau lors de la sauvegarde.');
        }
    }

    // === Tab Switching Logic ===
    const tabs = document.querySelectorAll('.nav-tab');
    const panes = document.querySelectorAll('.tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(`${target}-pane`).classList.add('active');
        });
    });

    // === Header Logic (Group Name) ===
    const groupNameValue = document.getElementById('groupNameValue');
    const groupNameInput = document.getElementById('groupNameInput');
    const groupNameToggleBtn = document.getElementById('groupNameToggleBtn');

    if (groupNameToggleBtn && groupNameValue && groupNameInput) {
        groupNameToggleBtn.addEventListener('click', async () => {
            if (groupNameToggleBtn.textContent === 'Modifier') {
                groupNameInput.value = groupNameValue.textContent;
                groupNameValue.style.display = 'none';
                groupNameInput.style.display = 'block';
                groupNameInput.focus();
                groupNameToggleBtn.textContent = 'Appliquer';
            } else {
                const newName = groupNameInput.value.trim();
                if (newName) {
                    const res = await updateProfile({ project_name: newName });
                    if (res && res.success) {
                        groupNameValue.textContent = newName;
                        groupNameValue.style.display = 'block';
                        groupNameInput.style.display = 'none';
                        groupNameToggleBtn.textContent = 'Modifier';
                    }
                }
            }
        });
    }

    // === Introduction Tab Logic ===

    const introTextArea = document.getElementById('introTextArea');
    const introTextDisplay = document.getElementById('introTextDisplay');
    const introToggleBtn = document.getElementById('introToggleBtn');
    const wordLimitSpan = document.querySelector('.word-limit');

    if (introToggleBtn && introTextArea && introTextDisplay) {
        introToggleBtn.addEventListener('click', async () => {
            const currentText = introTextDisplay.innerText.trim();
            if (introToggleBtn.textContent === 'Modifier') {
                introTextDisplay.style.display = 'none';
                introTextArea.style.display = 'block';
                introTextArea.value = (currentText === '_ _ _ _') ? '' : currentText;
                introTextArea.focus();
                introToggleBtn.textContent = 'Appliquer';
            } else {
                const newText = introTextArea.value;
                const res = await updateProfile({ project_intro: newText });
                if (res && res.success) {
                    introTextDisplay.innerText = newText.trim() === '' ? '_ _ _ _' : newText;
                    introTextDisplay.style.display = 'block';
                    introTextArea.style.display = 'none';
                    introToggleBtn.textContent = 'Modifier';
                    wordLimitSpan.textContent = `${newText.length}/1000`;
                }
            }
        });

        introTextArea.addEventListener('input', () => {
            const charCount = introTextArea.value.length;
            wordLimitSpan.textContent = `${charCount}/1000`;
            wordLimitSpan.style.color = charCount > 1000 ? 'red' : '';
        });
    }

    // Project Level Toggle
    const projectLevelValue = document.getElementById('projectLevelValue');
    const projectLevelInput = document.getElementById('projectLevelInput');
    const projectLevelToggleBtn = document.getElementById('projectLevelToggleBtn');

    if (projectLevelToggleBtn) {
        projectLevelToggleBtn.addEventListener('click', async () => {
            const group = projectLevelToggleBtn.closest('.pill-input-group');
            if (projectLevelToggleBtn.textContent === 'Modifier') {
                projectLevelInput.value = projectLevelValue.textContent === 'xxxxxx' ? '' : projectLevelValue.textContent;
                group.classList.add('editing');
                projectLevelInput.focus();
                projectLevelToggleBtn.textContent = 'Appliquer';
            } else {
                const newLevel = projectLevelInput.value.trim();
                if (newLevel) {
                    const res = await updateProfile({ leader_level: newLevel });
                    if (res && res.success) {
                        projectLevelValue.textContent = newLevel;
                        group.classList.remove('editing');
                        projectLevelToggleBtn.textContent = 'Modifier';
                    }
                }
            }
        });
    }

    // Domain Management
    const domainInput = document.getElementById('domainInput');
    const addDomainBtn = document.getElementById('addDomainBtn');
    const domainList = document.getElementById('domainList');

    const getDomainsString = () => {
        return Array.from(domainList.querySelectorAll('li'))
            .map(li => li.textContent.replace('-', '').replace('x', '').trim())
            .join(', ');
    };

    const addDomain = async () => {
        const value = domainInput.value.trim();
        if (value) {
            const li = document.createElement('li');
            li.innerHTML = `-${value} <img src="ICON/x-circle-fill.svg" class="remove-domain" alt="x">`;
            domainList.appendChild(li);

            const res = await updateProfile({ project_domain: getDomainsString() });
            if (res && res.success) {
                domainInput.value = '';
                li.querySelector('.remove-domain').addEventListener('click', async () => {
                    li.remove();
                    await updateProfile({ project_domain: getDomainsString() });
                });
            } else {
                li.remove();
            }
        }
    };

    if (addDomainBtn && domainInput && domainList) {
        addDomainBtn.addEventListener('click', addDomain);
        domainInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addDomain();
            }
        });
    }

    document.querySelectorAll('.remove-domain').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const li = e.target.parentElement;
            li.remove();
            await updateProfile({ project_domain: getDomainsString() });
        });
    });

    // Member Management
    const membersBody = document.getElementById('membersBody');
    const addMemberBtn = document.getElementById('addMemberBtn');
    const showAddMemberBtn = document.getElementById('showAddMemberBtn');
    const addMemberForm = document.querySelector('.add-member-form');
    const memberName = document.getElementById('memberName');
    const memberField = document.getElementById('memberField');
    const memberLevel = document.getElementById('memberLevel');

    if (showAddMemberBtn && addMemberForm) {
        showAddMemberBtn.addEventListener('click', () => {
            if (addMemberForm.style.display === 'none') {
                addMemberForm.style.display = 'flex';
                showAddMemberBtn.textContent = 'Annuler';
                showAddMemberBtn.classList.add('active'); // Reuse active style if needed
            } else {
                addMemberForm.style.display = 'none';
                showAddMemberBtn.textContent = 'Ajouter un membre';
                showAddMemberBtn.classList.remove('active');
            }
        });
    }

    if (addMemberBtn) {
        addMemberBtn.addEventListener('click', async () => {
            const name = memberName.value.trim();
            const sector = memberField.value.trim();
            const level = memberLevel.value.trim();

            if (name && sector && level) {
                try {
                    const response = await fetch('/groupe/members', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ name, sector, level })
                    });
                    const result = await response.json();

                    if (result.success) {
                        const tr = document.createElement('tr');
                        tr.setAttribute('data-id', result.member.id);
                        tr.innerHTML = `
                            <td><span class="badge-spacer"></span>${name}</td>
                            <td>${sector}</td>
                            <td>${level}</td>
                            <td><img src="/ICON/trash-fill.svg" class="delete-member" alt="delete"></td>
                        `;
                        membersBody.appendChild(tr);

                        memberName.value = '';
                        memberField.value = '';
                        memberLevel.value = '';
                        addMemberForm.style.display = 'none';
                        showAddMemberBtn.textContent = 'Ajouter un membre';
                        showAddMemberBtn.classList.remove('active');

                        tr.querySelector('.delete-member').addEventListener('click', () => deleteMember(tr, result.member.id));
                    }
                } catch (error) {
                    console.error('Member Add error:', error);
                    alert('Erreur lors de l\'ajout du membre.');
                }
            } else {
                alert('Veuillez remplir tous les champs du membre.');
            }
        });
    }

    async function deleteMember(row, id) {
        if (!confirm('Voulez-vous vraiment supprimer ce membre ?')) return;
        try {
            const response = await fetch(`/groupe/members/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });
            const result = await response.json();
            if (result.success) {
                row.remove();
            }
        } catch (error) {
            console.error('Member Delete error:', error);
        }
    }

    document.querySelectorAll('.delete-member').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            const id = row.getAttribute('data-id');
            if (id) deleteMember(row, id);
        });
    });

    // === Contact Tab Logic ===
    const submitContact = document.getElementById('submitContact');
    const contactWhatsapp = document.getElementById('contactWhatsapp');
    const contactEmail = document.getElementById('contactEmail');

    if (submitContact) {
        submitContact.addEventListener('click', async () => {
            const res = await updateProfile({
                contact_whatsapp: contactWhatsapp.value.trim(),
                contact_email: contactEmail.value.trim()
            });
            if (res && res.success) {
                alert('Informations de contact mises à jour !');
            }
        });
    }
    // === Delete Group Logic ===
    const showDeleteGroupBtn = document.getElementById('showDeleteGroupBtn');
    const deleteConfirmationForm = document.getElementById('deleteConfirmationForm');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const groupDeleteCode = document.getElementById('groupDeleteCode');

    if (showDeleteGroupBtn && deleteConfirmationForm) {
        showDeleteGroupBtn.addEventListener('click', () => {
            showDeleteGroupBtn.style.display = 'none';
            deleteConfirmationForm.style.display = 'flex';
            groupDeleteCode.focus();
        });

        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', () => {
                deleteConfirmationForm.style.display = 'none';
                showDeleteGroupBtn.style.display = 'block';
                groupDeleteCode.value = '';
            });
        }

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                const code = groupDeleteCode.value.trim();
                if (code) {
                    alert(`Groupe supprimé avec succès ! (Code: ${code})`);
                    // Here you would typically redirect or perform cleanup
                } else {
                    alert('Veuillez entrer le code du groupe.');
                }
            });
        }
    }

    // === Sidebar Image Upload Logic ===
    const triggerUploadBtn = document.getElementById('triggerUploadBtn');
    const sidebarImageInput = document.getElementById('sidebarImageInput');
    const sidebarImg = document.getElementById('sidebarImg');

    if (triggerUploadBtn && sidebarImageInput) {
        triggerUploadBtn.addEventListener('click', () => {
            sidebarImageInput.click();
        });

        sidebarImageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Veuillez sélectionner une image.');
                return;
            }

            // Aperçu instantané
            const reader = new FileReader();
            reader.onload = (event) => {
                const updateImg = (img) => {
                    img.src = event.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    img.style.opacity = '1';
                    img.style.borderRadius = '20px';
                    if (img.id === 'sidebarAvatarImg') {
                        img.style.filter = 'none'; // Retirer l'invert de l'icône par défaut
                    }
                };

                if (sidebarImg) updateImg(sidebarImg);
                const sidebarAvatarImg = document.getElementById('sidebarAvatarImg');
                if (sidebarAvatarImg) updateImg(sidebarAvatarImg);
            };
            reader.readAsDataURL(file);

            // Envoi au serveur pour persistance
            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('/groupe/upload-image', {
                method: 'POST',
                body: formData,
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Remplacer l'aperçu par l'URL serveur (plus stable)
                        if (sidebarImg) sidebarImg.src = data.image_url;
                        const sidebarAvatarImg = document.getElementById('sidebarAvatarImg');
                        if (sidebarAvatarImg) sidebarAvatarImg.src = data.image_url;
                    } else {
                        console.error('Erreur upload image :', data.error);
                        alert('Erreur lors de la sauvegarde de l\'image : ' + (data.error || 'Erreur inconnue'));
                    }
                })
                .catch(err => {
                    console.error('Erreur réseau :', err);
                    alert('Erreur réseau lors de l\'envoi de l\'image.');
                });
        });
    }

    // === Video Upload Logic ===
    const triggerVideoUploadBtn = document.getElementById('triggerVideoUploadBtn');
    const replaceVideoBtn = document.getElementById('replaceVideoBtn');
    const videoInput = document.getElementById('videoInput');
    const videoUploadProgressContainer = document.getElementById('videoUploadProgressContainer');
    const videoUploadProgressBar = document.getElementById('videoUploadProgressBar');
    const videoUploadPercent = document.getElementById('videoUploadPercent');
    const videoEmptyState = document.getElementById('videoEmptyState');
    const videoContainer = document.getElementById('videoContainer');
    const projectVideoPlayer = document.getElementById('projectVideoPlayer');

    if (videoInput) {
        const triggerUpload = () => videoInput.click();
        if (triggerVideoUploadBtn) triggerVideoUploadBtn.addEventListener('click', triggerUpload);
        if (replaceVideoBtn) replaceVideoBtn.addEventListener('click', triggerUpload);

        videoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Maximum client-side validation (500MB)
            if (file.size > 512 * 1024 * 1024) {
                alert('La vidéo est trop volumineuse. La taille maximale est de 500 Mo.');
                videoInput.value = '';
                return;
            }

            // Show progress bar, hide buttons
            videoEmptyState.style.display = 'none';
            videoContainer.style.display = 'none';
            videoUploadProgressContainer.style.display = 'block';
            videoUploadProgressBar.style.width = '0%';
            videoUploadPercent.textContent = '0%';

            const formData = new FormData();
            formData.append('video_file', file);
            formData.append('_token', csrfToken);

            // Use XMLHttpRequest for progress tracking
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/groupe/upload-video', true);

            xhr.upload.onprogress = (event) => {
                if (event.lengthComputable) {
                    const percentComplete = Math.round((event.loaded / event.total) * 100);
                    videoUploadProgressBar.style.width = percentComplete + '%';
                    videoUploadPercent.textContent = percentComplete + '%';
                }
            };

            xhr.onload = () => {
                videoUploadProgressContainer.style.display = 'none';

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            projectVideoPlayer.src = response.video_url;
                            videoContainer.style.display = 'flex';
                            alert('Vidéo téléversée avec succès !');
                        } else {
                            throw new Error(response.error || 'Erreur inconnue');
                        }
                    } catch (e) {
                        videoEmptyState.style.display = 'flex';
                        alert('Erreur: ' + e.message);
                    }
                } else {
                    let errorMessage = 'Erreur lors de l\'envoi (' + xhr.status + ')';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) errorMessage = response.message;
                        if (response.error) errorMessage = response.error;
                    } catch (e) { }
                    videoEmptyState.style.display = 'flex';
                    alert(errorMessage);
                }
                videoInput.value = ''; // Reset
            };

            xhr.onerror = () => {
                videoUploadProgressContainer.style.display = 'none';
                videoEmptyState.style.display = 'flex';
                alert('Erreur réseau lors de l\'envoi de la vidéo.');
                videoInput.value = ''; // Reset
            };

            xhr.send(formData);
        });
    }

    // === Reports Management Logic ===
    const reportInput = document.getElementById('reportInput');
    const publishBtnEmpty = document.querySelector('.publish-btn-empty');
    const publishBtnHeader = document.querySelector('.publish-btn-header');
    const reportsGrid = document.getElementById('reportsGrid');
    const reportsPane = document.getElementById('rapports-pane');
    const emptyStateRapports = reportsPane.querySelector('.empty-state-container');
    const rapportsHeader = reportsPane.querySelector('.rapports-header');

    if (reportInput && (publishBtnEmpty || publishBtnHeader)) {
        const triggerUpload = () => reportInput.click();

        if (publishBtnEmpty) publishBtnEmpty.addEventListener('click', triggerUpload);
        if (publishBtnHeader) publishBtnHeader.addEventListener('click', triggerUpload);

        reportInput.addEventListener('change', async (e) => {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                // Prepare FormData for the actual API upload
                const formData = new FormData();
                files.forEach(file => {
                    formData.append('reports[]', file);
                });
                formData.append('_token', csrfToken);

                try {
                    const response = await fetch('/groupe/upload-reports', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success && result.reports) {
                        result.reports.forEach(report => {
                            addReportToGrid(report);
                        });
                        updateReportsVisibility();
                        alert('Rapports publiés avec succès !');
                    } else {
                        alert('Erreur: ' + (result.error || 'Impossible d\'envoyer le(s) fichier(s)'));
                    }
                } catch (err) {
                    console.error('Erreur API Upload Report:', err);
                    alert('Erreur réseau lors de la publication des rapports.');
                }
                reportInput.value = ''; // Reset for same file selection
            }
        });
    }

    function addReportToGrid(reportObj) {
        if (!reportsGrid) return;

        const reportCard = document.createElement('div');
        reportCard.className = 'report-card';

        // Déterminer l'icône selon l'extension du fichier
        const ext = reportObj.file_name.split('.').pop().toLowerCase();
        const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        const videoExts = ['mp4', 'mov', 'webm', 'avi', 'mkv', 'ogg'];

        let iconHtml;
        if (imageExts.includes(ext)) {
            // Pour les images, afficher directement la miniature
            iconHtml = `<div class="report-icon-box" style="overflow:hidden; border-radius:8px;">
                <img src="${reportObj.file_url}" alt="Image" style="width:100%; height:100%; object-fit:cover;">
            </div>`;
        } else if (videoExts.includes(ext)) {
            // Pour les vidéos, icône film-strip
            iconHtml = `<div class="report-icon-box" style="background:#111; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                <img src="/ICON/film-strip.svg" alt="Video" style="width:50%; filter:invert(1);">
            </div>`;
        } else {
            // PDF par défaut
            iconHtml = `<div class="report-icon-box">
                <img src="/ICON/file-pdf.svg" alt="PDF">
            </div>`;
        }

        reportCard.innerHTML = `
            <a href="${reportObj.file_url}" target="_blank" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; align-items:center; width:100%;">
                ${iconHtml}
                <span class="report-filename" title="${reportObj.file_name}">${reportObj.file_name}</span>
            </a>
            <button class="report-delete-btn" style="position: absolute; top: -5px; right: -5px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); padding: 2px;">
                <img src="/ICON/x-circle-fill.svg" alt="Delete" style="display:block;">
            </button>
        `;

        // Ajout d'une propriété relative au parent pour que le bouton absolu se place bien
        reportCard.style.position = 'relative';

        // Add delete listener
        const deleteBtn = reportCard.querySelector('.report-delete-btn');
        deleteBtn.addEventListener('click', async (e) => {
            e.stopPropagation(); // Évite le déclenchement éventuel d'autres clics
            if (!confirm('Voulez-vous vraiment supprimer ce rapport ? Cela libèrera également la mémoire.')) return;

            try {
                const response = await fetch(`/groupe/reports/${reportObj.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const result = await response.json();

                if (result.success) {
                    reportCard.remove();
                    updateReportsVisibility();
                } else {
                    alert('Erreur: ' + (result.error || 'Impossible de supprimer ce rapport'));
                }
            } catch (err) {
                console.error('Erreur API Delete Report:', err);
                alert('Erreur réseau lors de la suppression du rapport.');
            }
        });

        reportsGrid.appendChild(reportCard);
    }

    function updateReportsVisibility() {
        if (!reportsGrid || !emptyStateRapports || !rapportsHeader) return;
        const hasReports = reportsGrid.children.length > 0;
        if (hasReports) {
            emptyStateRapports.style.display = 'none';
            rapportsHeader.style.display = 'flex';
            reportsGrid.style.display = 'grid';
        } else {
            emptyStateRapports.style.display = 'flex';
            rapportsHeader.style.display = 'none';
            reportsGrid.style.display = 'none';
        }
    }

    // Initialize initial reports on load
    if (window.serverGroupData && window.serverGroupData.reports && Array.isArray(window.serverGroupData.reports)) {
        window.serverGroupData.reports.forEach(report => {
            addReportToGrid({
                id: report.id,
                file_name: report.file_name,
                file_url: report.file_url
            });
        });
    }

    // Initialize visibility
    if (reportsGrid) updateReportsVisibility();

    // =============================================
    // === MODULE EVENEMENTS (identique visiteur) ===
    // =============================================
    const eventsData = window.serverEventsData || [];
    const eventsPanel = document.getElementById('groupEventsPanel');
    const eventsListContainer = document.getElementById('group-events-list-container');
    const eventsEmptyState = document.getElementById('group-events-empty-state');
    const eventsBtn = document.getElementById('groupEventsBtn');
    const bellBtn = document.getElementById('groupBellBtn');
    const closePanelBtn = document.getElementById('closeEventsPanel');
    const eventsNotifDot = document.getElementById('group-events-notif-dot');
    const bellNotifDot = document.getElementById('group-bell-notif-dot');

    // Build event band HTML (same logic as visiteur.js)
    function createGroupEventBandHTML(event) {
        const inlineImageSvg = `<svg width="40" height="40" viewBox="0 0 24 24" fill="none" class="placeholder-icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>`;
        const inlineVideoSvg = `<svg width="50" height="50" viewBox="0 0 24 24" fill="none" class="placeholder-icon" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>`;

        const imageHtml = event.image
            ? `<img src="${event.image}" alt="Event Image" class="actual-image">`
            : inlineImageSvg;

        const videoHtml = event.video
            ? `<div class="custom-video-wrapper">
                <video id="video-gevent-${event.id}" src="${event.video}" preload="metadata" controls></video>
                <div class="video-overlay" onclick="document.getElementById('video-gevent-${event.id}').play(); document.getElementById('video-gevent-${event.id}').setAttribute('controls','controls'); this.style.display='none';">
                    <div class="icon-container"><img src="/ICON/film-strip.svg" alt="Play" class="video-play-icon"></div>
                    <span class="video-label">Voir la vidéo</span>
                </div>
              </div>`
            : inlineVideoSvg;

        return `
            <div class="event-band" data-id="${event.id}">
                <div class="event-band-header">
                    <span>${event.title}</span>
                    <img src="/ICON/up-arrow_icon.svg" alt="Expand" class="chevron-icon">
                </div>
                <div class="event-band-content">
                    <div class="event-content-left">
                        <div class="event-image-placeholder">${imageHtml}</div>
                    </div>
                    <div class="event-content-right">
                        <div class="event-description">${event.description.replace(/\n/g, '<br>')}</div>
                        <div class="event-video-placeholder">${videoHtml}</div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderGroupEventsList() {
        if (!eventsListContainer || !eventsEmptyState) return;
        eventsListContainer.innerHTML = '';
        if (eventsData.length === 0) {
            eventsEmptyState.style.display = 'block';
            eventsListContainer.style.display = 'none';
        } else {
            eventsEmptyState.style.display = 'none';
            eventsListContainer.style.display = 'flex';
            eventsData.forEach(event => {
                eventsListContainer.innerHTML += createGroupEventBandHTML(event);
            });
            // Accordion toggle for event bands
            eventsListContainer.querySelectorAll('.event-band-header').forEach(header => {
                header.addEventListener('click', function () {
                    this.closest('.event-band').classList.toggle('expanded');
                });
            });
        }
    }

    // Notification dot logic (same as visitor)
    function checkGroupNewEvents() {
        if (eventsData.length === 0) return;
        const lastSeenId = parseInt(localStorage.getItem('ites_last_seen_event_id') || '0');
        const maxEventId = Math.max(...eventsData.map(e => e.id));
        const hasNew = maxEventId > lastSeenId;
        if (eventsNotifDot) eventsNotifDot.style.display = hasNew ? 'block' : 'none';
        if (bellNotifDot) bellNotifDot.style.display = hasNew ? 'block' : 'none';
    }

    function markEventsAsSeen() {
        if (eventsData.length === 0) return;
        const maxEventId = Math.max(...eventsData.map(e => e.id));
        localStorage.setItem('ites_last_seen_event_id', maxEventId.toString());
        if (eventsNotifDot) eventsNotifDot.style.display = 'none';
        if (bellNotifDot) bellNotifDot.style.display = 'none';
    }

    function openEventsPanel() {
        if (!eventsPanel) return;
        eventsPanel.style.display = 'flex';
        renderGroupEventsList();
        markEventsAsSeen();
    }

    function closeEventsPanel() {
        if (eventsPanel) eventsPanel.style.display = 'none';
    }

    if (eventsBtn) eventsBtn.addEventListener('click', openEventsPanel);
    if (closePanelBtn) closePanelBtn.addEventListener('click', closeEventsPanel);

    // Close panel clicking outside
    if (eventsPanel) {
        eventsPanel.addEventListener('click', function (e) {
            if (e.target === this) closeEventsPanel();
        });
    }

    // Initial notification check
    checkGroupNewEvents();

    // =============================================
    // MESSAGERIE GROUPE
    // =============================================
    const msgsOverlay = document.getElementById('messages-overlay');
    const closeMsgsBtn = document.getElementById('close-messages-btn');
    const msgList = document.getElementById('messages-list-container');
    const msgNotifDot = document.getElementById('group-messages-notif-dot');
    const clearBtn = document.getElementById('group-clear-messages');

    // Check for unread messages
    async function checkUnreadMsgs() {
        try {
            const res = await fetch('/groupe/messages/unread');
            const data = await res.json();
            if (data.success && data.unread_count > 0) {
                if (msgNotifDot) msgNotifDot.style.display = 'block';
            } else {
                if (msgNotifDot) msgNotifDot.style.display = 'none';
            }
        } catch (e) { console.error('Error checking unread', e); }
    }

    // Load messages
    async function loadMessages() {
        msgList.innerHTML = '<div class="section-vide" style="color:black;">Chargement...</div>';
        try {
            const res = await fetch('/groupe/messages', { headers: { 'Accept': 'application/json' } });
            const textResponse = await res.text();

            if (!res.ok) {
                console.error('[Messagerie Groupe] Erreur HTTP', res.status, textResponse.substring(0, 500));
                msgList.innerHTML = `<div class="section-vide" style="color:black;">Erreur ${res.status} - rechargez la page.</div>`;
                return;
            }

            let data;
            try {
                data = JSON.parse(textResponse);
            } catch (jsonErr) {
                console.error('[Messagerie Groupe] JSON Error:', jsonErr, 'Response:', textResponse.substring(0, 500));
                // Affiche les 100 premiers caractères de la réponse brute pour diagnostiquer l'HTML
                msgList.innerHTML = `<div style="color:red; font-size: 0.8rem; padding:10px; word-break: break-all;">Erreur Serveur (HTML reçu au lieu de JSON) : <br><br>${textResponse.substring(0, 150).replace(/</g, '&lt;')}</div>`;
                return;
            }

            if (data.success) {
                renderMessages(data.messages);
                // Mark as read after loading
                await fetch('/groupe/messages/read', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' }
                });
                if (msgNotifDot) msgNotifDot.style.display = 'none';
            }
        } catch (e) {
            console.error('[Messagerie Groupe] Exception interceptée:', e);
            msgList.innerHTML = `<div class="section-vide" style="color:black;">Erreur critique JS: ${e.message}</div>`;
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
            block.className = 'message-block visitor-msg'; // Visitor orange block

            const visitorName = msg.visitor ? msg.visitor.name : 'Visiteur Inconnu';
            const hasReply = msg.group_reply ? true : false;

            // Visitor original message (in orange styling context)
            let html = `
                <div class="msg-header">
                    <div class="msg-user-info">
                        <img src="/ICON/profile_user_avatar_person_icon_192481.svg" class="msg-user-icon" style="filter: brightness(0) invert(1);" alt="user">
                        <span>${visitorName}</span>
                    </div>
                    ${!hasReply ? `<button class="msg-reply-btn" data-msg-id="${msg.id}"><img src="/ICON/reply-icon.svg" onerror="this.style.display='none'"> RÉPONDRE</button>` : ''}
                </div>
                <p class="msg-content">${msg.visitor_message}</p>
                ${!msg.is_read_by_group ? '<div class="msg-status-dot" style="position:absolute; bottom:10px; right:10px;" title="Nouveau message"></div>' : ''}
            `;

            // If group replied, append the white block
            if (hasReply) {
                const _gd = window.serverGroupData || {};
                const groupName = _gd.project_name || _gd.name || 'Groupe';
                const groupImg = _gd.project_image ? `/${_gd.project_image}` : '/ICON/group.svg';

                html += `
                <div class="message-block group-msg" style="margin-top: 10px;">
                    <div class="msg-header">
                        <div class="msg-user-info">
                            <img src="${groupImg}" class="msg-user-icon" style="border-radius:50%; object-fit:cover;" onerror="this.src='/ICON/group.svg'" alt="group">
                            <span>Moi (${groupName})</span>
                        </div>
                        ${!msg.is_read_by_visitor ? '<div class="msg-status-dot" style="background-color: var(--orange); box-shadow: none;" title="Non lu par le visiteur"></div>' : ''}
                    </div>
                    <p class="msg-content">${msg.group_reply}</p>
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
        bellBtn.addEventListener('click', (e) => {
            // Note: bellBtn usually opens events. We changed it so Bell opens Messages, and Calendar icon opens Events.
            // Wait, currently Bell opens Events. Let's redirect Bell to Messages, but the code above uses eventsBtn and bellBtn both for Events Panel:
            // "if (bellBtn) bellBtn.addEventListener('click', openEventsPanel);"
            // I need to stop the bell button from opening the events panel.
            msgsOverlay.classList.add('open');
            loadMessages();
        });
    }

    // Close Modale
    if (closeMsgsBtn) {
        closeMsgsBtn.addEventListener('click', () => {
            msgsOverlay.classList.remove('open');
            checkUnreadMsgs();
        });
    }

    // Clear messages
    if (clearBtn) {
        clearBtn.addEventListener('click', async () => {
            if (confirm('Supprimer tous les messages de la messagerie ?')) {
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    await fetch('/groupe/messages/clear', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
                    loadMessages();
                } catch (e) { console.error('Clear error', e); }
            }
        });
    }

    // Delegated reply button click
    msgList.addEventListener('click', (e) => {
        const replyBtn = e.target.closest('.msg-reply-btn');
        if (replyBtn) {
            const msgId = replyBtn.dataset.msgId;
            const parentBlock = replyBtn.closest('.visitor-msg');

            document.querySelectorAll('.reply-editor-container').forEach(el => el.remove());

            const editor = document.createElement('div');
            editor.className = 'reply-editor-container';
            editor.innerHTML = `
                <textarea class="reply-textarea" placeholder="Écrire une réponse..." style="resize:vertical;"></textarea>
                <button class="send-reply-btn" data-msg-id="${msgId}">Envoyer</button>
            `;
            parentBlock.appendChild(editor);
            setTimeout(() => editor.querySelector('textarea').focus(), 50);
        }

        const sendBtn = e.target.closest('.send-reply-btn');
        if (sendBtn) {
            const msgId = sendBtn.dataset.msgId;
            const text = sendBtn.previousElementSibling.value.trim();
            if (text) {
                sendGroupReply(msgId, text, sendBtn.closest('.reply-editor-container'));
            }
        }
    });

    async function sendGroupReply(msgId, text, editorRef) {
        if (editorRef) editorRef.innerHTML = '<span style="color:white;">Envoi...</span>';
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(`/groupe/messages/${msgId}/reply`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ reply: text })
            });
            const data = await res.json();
            if (data.success) {
                loadMessages();
            } else {
                alert('Erreur: ' + (data.message || 'Impossible de répondre.'));
                if (editorRef) editorRef.remove();
            }
        } catch (e) {
            console.error(e);
            alert('Problème réseau.');
            if (editorRef) editorRef.remove();
        }
    }

    checkUnreadMsgs();

    // --- TEMPS REEL avec Laravel Echo ---
    const groupIdMeta = document.querySelector('meta[name="user-id"]');
    const groupUserId = groupIdMeta ? parseInt(groupIdMeta.content) : null;

    if (groupUserId) {
        function initGroupEcho(retries) {
            if (window.Echo) {
                console.log('[Echo] Groupe: connexion au canal group.messages.' + groupUserId);
                window.Echo.private(`group.messages.${groupUserId}`)
                    .listen('.message.received', (data) => {
                        console.log('[Echo] Nouveau message visiteur reçu en temps réel', data);
                        checkUnreadMsgs();
                        if (document.getElementById('messages-overlay')?.classList.contains('open')) {
                            loadMessages();
                        }
                    })
                    .error((err) => {
                        console.error('[Echo] Erreur canal groupe:', err);
                    });
            } else if (retries > 0) {
                setTimeout(() => initGroupEcho(retries - 1), 200);
            } else {
                console.warn('[Echo] Non disponible pour le groupe, fallback polling 10s');
                setInterval(checkUnreadMsgs, 10000);
            }
        }
        initGroupEcho(50);
    } else {
        setInterval(checkUnreadMsgs, 10000);
    }

});
