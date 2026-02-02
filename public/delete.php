<?php
// public/delete.php
require_once '../config/db.php';
require_once '../includes/functions.php';
session_start();

if (!isLogged() || !isOrganizer()) {
    redirect('login.php');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Security: Validate CSRF Token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token");
    }

    // 2. Perform Deletion
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);

    redirect('index.php');
} else {
    // If accessed via GET (e.g. typing URL directly), redirect to index safely
    redirect('index.php');
}
?>