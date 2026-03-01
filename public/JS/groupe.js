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

        reportInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                files.forEach(file => {
                    addReportToGrid(file);
                });
                updateReportsVisibility();
                reportInput.value = ''; // Reset for same file selection
            }
        });
    }

    function addReportToGrid(file) {
        if (!reportsGrid) return;

        const reportCard = document.createElement('div');
        reportCard.className = 'report-card';

        // Determine icon based on file type (using available icons in ICON/)
        let iconSrc = 'ICON/research_icon.svg'; // Default icon
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (fileExtension === 'pdf') {
            iconSrc = 'ICON/file-pdf.svg';
        } else if (['jpg', 'jpeg', 'png', 'svg'].includes(fileExtension)) {
            iconSrc = 'ICON/image.svg';
        } else {
            iconSrc = 'ICON/research_icon.svg';
        }

        reportCard.innerHTML = `
            <div class="report-icon-box">
                <img src="${iconSrc}" alt="File">
            </div>
            <span class="report-filename" title="${file.name}">${file.name}</span>
            <button class="report-delete-btn">
                <img src="ICON/x-circle-fill.svg" alt="Delete">
            </button>
        `;

        // Add delete listener
        reportCard.querySelector('.report-delete-btn').addEventListener('click', () => {
            reportCard.remove();
            updateReportsVisibility();
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

    // Initialize visibility
    if (reportsGrid) updateReportsVisibility();
});
