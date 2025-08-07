<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['id'];
$user = $_SESSION['name'];
$email = $_SESSION['email'];

// Fetch course and semester from the database
$query = "SELECT course, semester FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$course = $data['course'] ?? 'N/A';
$semester = $data['semester'] ?? 'N/A';

mysqli_stmt_close($stmt);
?>

<?php include('../header.php') ?>

<!-- Main Content -->
<div class="col-md-40 p-50">
    <div class="dashboard-header mb-4">
        <h3>Hi, <?php echo htmlspecialchars($user); ?>!</h3>
        <p>This is your profile information.</p>
    </div>

    <div class="profile-card p-4 bg-white rounded shadow">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($course); ?></p>
        <p><strong>Semester:</strong> <?php echo htmlspecialchars($semester); ?></p>
    </div>
</div>

<?php include('../footer.php') ?>
