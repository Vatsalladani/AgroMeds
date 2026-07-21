<?php
session_start();

// Handle POST request to update theme and language
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['theme'])) {
        $_SESSION['theme'] = $_POST['theme'];
    }
    if (isset($_POST['language'])) {
        $_SESSION['language'] = $_POST['language'];
    }
    // Redirect back to home page
    header('Location: home.php');
    exit;
}

// Handle GET request to fetch the current settings (for `settings.js`)
header('Content-Type: application/json');
echo json_encode([
    'theme' => isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light',
    'language' => isset($_SESSION['language']) ? $_SESSION['language'] : 'en',
]);
