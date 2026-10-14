(() => {
    const languageStorageKey = 'siteLanguage';
    const languageNames = { es: 'Español', en: 'English' };
    let translations = null;

    const applyLanguage = (language) => {
        if (!translations || !translations[language]) {
            return;
        }

        document.documentElement.lang = language;
        document.querySelectorAll('[data-i18n]').forEach((element) => {
            const key = element.dataset.i18n;
            if (Object.prototype.hasOwnProperty.call(translations[language], key)) {
                element.textContent = translations[language][key];
            } else {
                console.error(`Missing translation "${key}" for language "${language}".`);
            }
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
            const key = element.dataset.i18nPlaceholder;
            if (Object.prototype.hasOwnProperty.call(translations[language], key)) {
                element.setAttribute('placeholder', translations[language][key]);
            } else {
                console.error(`Missing placeholder translation "${key}" for language "${language}".`);
            }
        });

        document.querySelectorAll('[data-i18n-title]').forEach((element) => {
            const key = element.dataset.i18nTitle;
            if (Object.prototype.hasOwnProperty.call(translations[language], key)) {
                element.setAttribute('title', translations[language][key]);
            } else {
                console.error(`Missing title translation "${key}" for language "${language}".`);
            }
        });

        document.querySelectorAll('[data-i18n-value]').forEach((element) => {
            const key = element.dataset.i18nValue;
            if (Object.prototype.hasOwnProperty.call(translations[language], key)) {
                element.value = translations[language][key];
            } else {
                console.error(`Missing value translation "${key}" for language "${language}".`);
            }
        });

        document.querySelectorAll('[data-i18n-alt]').forEach((element) => {
            const key = element.dataset.i18nAlt;
            if (Object.prototype.hasOwnProperty.call(translations[language], key)) {
                element.setAttribute('alt', translations[language][key]);
            } else {
                console.error(`Missing alt translation "${key}" for language "${language}".`);
            }
        });

        const dropdownButton = document.getElementById('dropdownBtn');
        if (dropdownButton) {
            dropdownButton.textContent = languageNames[language];
        }
        document.querySelectorAll('.dropdown-item[data-lang]').forEach((item) => {
            item.classList.toggle('active', item.dataset.lang === language);
        });
        document.querySelectorAll('[data-language-select]').forEach((select) => {
            select.value = language;
        });
        window.dispatchEvent(new CustomEvent('site-language-changed', { detail: { language } }));
    };

    window.getSiteTranslation = (key) => translations?.[document.documentElement.lang]?.[key];

    window.setSiteLanguage = (language) => {
        if (language !== 'en' && language !== 'es') {
            console.error(`Unsupported language "${language}".`);
            return;
        }
        localStorage.setItem(languageStorageKey, language);
        applyLanguage(language);
    };

    document.addEventListener('DOMContentLoaded', async () => {
        const translationFile = document.documentElement.dataset.i18nFile;
        if (!translationFile) {
            console.error('The page is missing its data-i18n-file attribute.');
            return;
        }

        try {
            const response = await fetch(translationFile);
            if (!response.ok) {
                throw new Error(`Unable to load ${translationFile}: ${response.status}`);
            }
            translations = await response.json();
            if (!translations.es || !translations.en) {
                throw new Error(`${translationFile} must define both "es" and "en" translations.`);
            }

            const savedLanguage = localStorage.getItem(languageStorageKey);
            applyLanguage(savedLanguage === 'en' ? 'en' : 'es');
        } catch (error) {
            console.error('Unable to initialize page translations.', error);
        }
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-language-select]')) {
            window.setSiteLanguage(event.target.value);
        }
    });
})();
