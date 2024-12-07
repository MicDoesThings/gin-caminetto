<?php
session_start();
require_once '../config/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Check if ID is provided
if (!isset($_POST['id'])) {
    header('Location: index.php');
    exit();
}

try {
    // Get the gin details first (for image deletion)
    $stmt = $conn->prepare("SELECT image_path FROM gins WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $gin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete the gin from database
    $stmt = $conn->prepare("DELETE FROM gins WHERE id = ?");
    $stmt->execute([$_POST['id']]);

    // If gin had an image, delete it from filesystem
    if ($gin && $gin['image_path']) {
        $image_path = '../' . $gin['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Redirect back to admin page
    header('Location: index.php');
} catch (PDOException $e) {
    // Handle error (you might want to log this)
    header('Location: index.php?error=delete_failed');
}
exit(); 