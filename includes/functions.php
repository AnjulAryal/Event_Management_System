<?php
// includes/functions.php


// Sanitize input to prevent XSS attacks (removes special HTML characters)
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Redirect the user to a specific page
function redirect($path) {
    header("Location: $path");
    exit();
}

// Check if a user is currently logged in
function isLogged() {
    return isset($_SESSION['user_id']);
}

// Check if the logged-in user is an admin/organizer
function isOrganizer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'organizer';
}

// Generate a unique security token to prevent Cross-Site Request Forgery (CSRF)
// This should be called when rendering a form
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify that the token submitted with the form matches the one we stored
// This prevents malicious sites from submitting forms on behalf of the user
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
