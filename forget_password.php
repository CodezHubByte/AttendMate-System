<?php
session_start();
require_once 'db.php'; // Database connection

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $message = '<div class="text-red-500">Please enter your email.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="text-red-500">Invalid email format.</div>';
    } else {
        // Check if the email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id);
            $stmt->fetch();

            // Generate token and expiry
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // Store token and expiry
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expire = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $token, $expires, $user_id);
            if ($update_stmt->execute()) {
                $reset_link = "http://localhost/resetpassword.php?token=" . urlencode($token);
                $message = '<div class="text-green-600">Reset link generated!</div>';
                $message .= '<div class="mt-2">Click to reset: <a href="' . $reset_link . '" class="text-blue-600 underline">Reset Password</a></div>';
            } else {
                $message = '<div class="text-red-500">Failed to generate reset link. Please try again.</div>';
            }
            $update_stmt->close();
        } else {
            $message = '<div class="text-red-500">Email not found. Please check again.</div>';
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-500 via-blue-400 to-indigo-500 text-gray-800">
    <div class="bg-white bg-opacity-80 backdrop-blur-md p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Forgot Password</h2>
        <?php echo $message; ?>
        <form method="POST" action="">
            <label class="block mb-4">
                <span class="text-gray-700">Enter your registered email:</span>
                <input type="email" name="email" class="mt-1 block w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            </label>
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">Send Reset Link</button>
        </form>
        <div class="text-sm text-center mt-4">
            <a href="index.php" class="text-blue-700 hover:underline">Back to Login</a>
        </div>
    </div>
</body>
</html>
