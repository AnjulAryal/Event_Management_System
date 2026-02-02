<?php
// includes/header.php
require_once __DIR__ . '/../includes/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herald Events</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <meta name="description" content="Herald Events - Manage and discover local events">
</head>
<body>
    <header>
        <nav class="navbar" aria-label="Main navigation">
            <div class="nav-container">
                <a href="index.php" class="logo">Herald Events</a>
                
                <div class="nav-links">
                    <a href="index.php" aria-current="page">Home</a>
                    
                    <?php if (isLogged()): ?>
                        <?php if (isOrganizer()): ?>
                            <a href="manage_attendees.php">Manage Attendees</a>
                            <a href="add.php" class="nav-btn">Create Event</a>
                        <?php endif; ?>
                        <a href="logout.php" class="logout-link">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php" class="nav-btn">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="container">