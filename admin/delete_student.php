<?php
session_start();
require_once '../db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "Access denied. You must be an admin to perform this action.";
    header('Location: ../index.php');
    exit();
}


if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = $_GET['id'];


    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id); // 'i' for integer type

    if ($stmt->execute()) {
        // Deletion successful
        $_SESSION['message'] = "User data has been successfully deleted!";
    } else {
        // Deletion failed
        $_SESSION['message'] = "Error deleting user: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: admin_dashboard.php");
    exit();
} else {

    $_SESSION['message'] = "No user ID specified for deletion.";
    header("Location: admin_dashboard.php");
    exit();
}
