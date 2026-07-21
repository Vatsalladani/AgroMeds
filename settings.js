document.addEventListener('DOMContentLoaded', () => {
    // Fetch current theme and language settings from the server
    fetch('settings.php')
        .then(response => response.json())
        .then(data => {
            const language = data.language || 'en';

            // Fetch translations based on the selected language
            fetch('translation_s.json')
                .then(response => response.json())
                .then(translations => {
                    const translation = translations[language];

                    // Replace all text with the translated content
                    document.querySelectorAll('[data-lang-key]').forEach(element => {
                        const key = element.getAttribute('data-lang-key');
                        if (translation[key]) {
                            element.textContent = translation[key];
                        }
                    });
                });

            // Apply the theme
            const theme = data.theme || 'light';
            document.body.setAttribute('data-theme', theme);

            // Set theme styles
            switch (theme) {
                case 'dark':
                    document.body.style.backgroundColor = '#121212';
                    document.body.style.color = '#e0e0e0';
                    break;
                case 'blue-gradient':
                    document.body.style.background = 'linear-gradient(45deg, #1e3c72, #2a5298)';
                    document.body.style.color = '#ffffff';
                    break;
                case 'pink-gradient':
                    document.body.style.background = 'linear-gradient(45deg, #ff9a9e, #fad0c4)';
                    document.body.style.color = '#000000';
                    break;
                case 'green-gradient':
                    document.body.style.background = 'linear-gradient(45deg, #56ab2f, #a8e063)';
                    document.body.style.color = '#000000';
                    break;
                default:
                    document.body.style.backgroundColor = '#ffffff';
                    document.body.style.color = '#000000';
            }
        });
});
