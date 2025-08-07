<?php
session_start();
require_once '../db.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get admin email from session
$adminEmail = $_SESSION['email'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Get current password from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hashed)) {
        $error = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE email = ?");
        $update->bind_param("ssss", $name, $email, $newHash, $adminEmail);

        if ($update->execute()) {
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            $success = "Settings updated successfully.";
            $adminEmail = $email; // Update for later use
        } else {
            $error = "Failed to update settings.";
        }

        $update->close();
    }
}

// Fetch admin data after update or for display
$result = mysqli_query($conn, "SELECT name, email FROM users WHERE email = '$adminEmail'");
if ($result && mysqli_num_rows($result) > 0) {
    $adminData = mysqli_fetch_assoc($result);
} else {
    $adminData = ['name' => '', 'email' => ''];
    $error = "Admin not found in the database.";
}

include '../header.php';
?>

<div class="main-content p-4">
    <h2 class="text-xl font-semibold mb-4">⚙️ Admin Settings</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 600px;">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($adminData['name']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($adminData['email']) ?>" class="form-control" required>
        </div>

        <hr>

        <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
    </form>
</div>

<?php include '../footer.php'; ?>
