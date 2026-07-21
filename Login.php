<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card animate__animated animate__fadeInDown">
            <div class="login-image"></div>
            <div class="login-form">
                <h1 data-i18n="login_heading">Login</h1>
                <form id="loginForm">
    <input type="email" id="email" class="form-control" placeholder="Email" required>
    <input type="password" id="password" class="form-control" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

                <div class="links">
                    <a href="Register.php" data-i18n="not_registered">Not Registered?</a>
                    <a href="Forget_password.php" data-i18n="forgot_password">Forgot Password?</a>
                </div>
                <div class="language-select">
                    <label for="language" data-i18n="change_language">Change Language:</label>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Select Language
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="#" onclick="changeLanguage('en')">English</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeLanguage('hi')">Hindi</a></li>
                            <li><a class="dropdown-item" href="#" onclick="changeLanguage('gu')">Gujarati</a></li>
                        </ul>
                 </div>
            </div>
        </div>
    </div>

    <!-- External Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/i18next/21.9.1/i18next.min.js"></script>
    <script>
         const resources = {
            en: {
                translation: {
                    login_heading: "Login",
                    email_placeholder: "Email",
                    password_placeholder: "Password",
                    login_button: "Login",
                    not_registered: "Not Registered?",
                    forgot_password: "Forgot Password?",
                    change_language: "Change Language:"
                }
            },
            hi: {
                translation: {
                    login_heading: "लॉग इन करें",
                    email_placeholder: "ईमेल",
                    password_placeholder: "पासवर्ड",
                    login_button: "लॉग इन करें",
                    not_registered: "पंजीकृत नहीं है?",
                    forgot_password: "पासवर्ड भूल गए?",
                    change_language: "भाषा बदलें:"
                }
            },
            gu: {
                translation: {
                    login_heading: "લૉગિન",
                    email_placeholder: "ઇમેઇલ",
                    password_placeholder: "પાસવર્ડ",
                    login_button: "લૉગિન",
                    not_registered: "નોંધણી કરેલ નથી?",
                    forgot_password: "પાસવર્ડ ભૂલી ગયા?",
                    change_language: "ભાષા બદલો:"
                }
            }
        };

        // Initialize i18next
        i18next.init({
            lng: localStorage.getItem('selectedLanguage') || 'en',
            debug: true,
            resources
        }, (err, t) => {
            if (err) console.error(err);
            updateContent();
        });

        // Update content dynamically
        function updateContent() {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                el.textContent = i18next.t(el.getAttribute('data-i18n'));
            });
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                el.setAttribute('placeholder', i18next.t(el.getAttribute('data-i18n-placeholder')));
            });
        }

        // Change language
        function changeLanguage(language) {
            i18next.changeLanguage(language, () => {
                localStorage.setItem('selectedLanguage', language);
                updateContent();
                document.getElementById('languageDropdown').textContent = language === 'en' ? 'English' : language === 'hi' ? 'Hindi' : 'Gujarati';
            });
        }

        // Set language on page load
        document.addEventListener('DOMContentLoaded', () => {
            const savedLanguage = localStorage.getItem('selectedLanguage') || 'en';
            changeLanguage(savedLanguage);
        });
    </script>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
      <script>
    document.getElementById('loginForm').addEventListener('submit', async function (event) {
    // Prevent the default form submission
    event.preventDefault();

    // Get input values
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        // Send login data to the server
        const response = await fetch('validateLogin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Check Content-Type to determine how to handle the response
        const contentType = response.headers.get('Content-Type');
        if (contentType && contentType.includes('application/json')) {
            // Parse as JSON
            const data = await response.json();

            if (data.status === 'success') {
                Swal.fire({
                    title: 'Login Successful!',
                    text: 'Redirecting to your dashboard...',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false,
                });

                // Redirect after a delay
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 3000);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Invalid login credentials!',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false,
                });
            }
        } else {
            // Handle as HTML or plain text
            const htmlText = await response.text();
            console.error('Received HTML response:', htmlText);

            Swal.fire({
                title: 'Error!',
                text: 'Unexpected server response. Please try again later.',
                icon: 'error',
                timer: 3000,
                showConfirmButton: false,
            });
        }
    } catch (error) {
        console.error('Error:', error);

        Swal.fire({
            title: 'Error!',
            text: 'An error occurred while logging in. Please try again.',
            icon: 'error',
            timer: 3000,
            showConfirmButton: false,
        });
    }
});




</script>

</body>
</html>
