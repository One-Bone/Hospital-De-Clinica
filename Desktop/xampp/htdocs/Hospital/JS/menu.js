document.addEventListener('DOMContentLoaded', () => {
    const fabBtn = document.getElementById('fabBtn');
    const fabIcon = document.getElementById('fabIcon');
    const sidePopup = document.getElementById('sidePopup');
    
    const btnOpenPreferences = document.getElementById('btnOpenPreferences');
    const preferencesModal = document.getElementById('preferencesModal');
    
    const dropdownLanguage = document.getElementById('dropdownLanguage');
    const dropdownBtn = document.getElementById('dropdownBtn');
    const dropdownItems = document.querySelectorAll('.dropdown-item');

    // Alternate
    fabBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        sidePopup.classList.toggle('active');
        fabBtn.classList.toggle('open');

        if (sidePopup.classList.contains('active')) {
            fabIcon.classList.remove('fa-bars');
            fabIcon.classList.add('fa-xmark');
        } else {
            fabIcon.classList.remove('fa-xmark');
            fabIcon.classList.add('fa-bars');
        }
    });

    // Preferences
    btnOpenPreferences.addEventListener('click', (e) => {
        e.preventDefault();
        preferencesModal.classList.add('active');
    });

    // Click ofside close preferences
    preferencesModal.addEventListener('click', (e) => {
        if (e.target === preferencesModal) {
            preferencesModal.classList.remove('active');
        }
    });

    // Dropdown Language
    dropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdownLanguage.classList.toggle('open');
    });

    // Lenguaje select
    dropdownItems.forEach(item => {
        item.addEventListener('click', () => {
            dropdownItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            dropdownBtn.innerText = item.dataset.lang;
            dropdownLanguage.classList.remove('open');
        });
    });

    // Dropdown close outside
    document.addEventListener('click', () => {
        dropdownLanguage.classList.remove('open');
    });
});