document.addEventListener('DOMContentLoaded', function () {
    // Tab switching logic
    const navLinks = document.querySelectorAll('.visitor-nav-link');
    const tabContents = document.querySelectorAll('.tab-content');
    const bookmarkBtn = document.querySelector('.bookmark-btn');
    const bookmarkIcon = document.querySelector('.bookmark-icon');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const tabId = this.getAttribute('data-tab');

            // Update nav links
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            // Update tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === tabId + '-section') {
                    content.classList.add('active');
                }
            });

            // If switching to events or others, reset bookmark highlight if needed
            if (tabId !== 'favorites') {
                bookmarkIcon.style.color = 'black';
            }
        });
    });

    // Bookmark / Favorites "Tab"
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function () {
            // Treat bookmark as a special tab
            navLinks.forEach(l => l.classList.remove('active'));
            bookmarkIcon.style.color = 'var(--orange)';

            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === 'favorites-section') {
                    content.classList.add('active');
                }
            });
        });
    }

    // Search functionalitiy placeholder
    const searchInput = document.getElementById('projectSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            console.log('Searching for:', e.target.value);
            // Here you would typically filter a list of projects
        });
    }

    // User dropdown (simple log for now)
    const userProfile = document.querySelector('.user-profile');
    if (userProfile) {
        userProfile.addEventListener('click', function () {
            console.log('User profile clicked');
            // Toggle a real dropdown menu here if needed
        });
    }
});
