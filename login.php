<?php
session_start();
require_once 'db.php'; // one folder up if this file is inside /user or /admin

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/admin_dashboard.php");
            } elseif ($user['role'] === 'user') {
                header("Location: user/student_dashboard.php");
            } else {
                $_SESSION['login_error'] = "Unknown role.";
                $_SESSION['active_form'] = 'login';
                header("Location: index.php");
            }
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
