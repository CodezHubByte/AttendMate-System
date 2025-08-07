<?php
// Error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../db.php';

// Check login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['name'];
$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");

include '../header.php';
?>

<!-- Main Content -->
<div class="col-md-40 p-50">
    <div class="dashboard-header mb-4">
        <h3>📢 Announcements</h3>
        <p>Latest updates and news from the admin.</p>
    </div>

    <?php if (mysqli_num_rows($announcements) > 0): ?>
        <div class="list-group">
            <?php while ($row = mysqli_fetch_assoc($announcements)): ?>
                <div class="list-group-item mb-3 shadow-sm border rounded bg-light">
                    <h5 class="mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                    <small class="text-muted">Posted on <?= date("d M Y", strtotime($row['created_at'])) ?></small>
                    <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No announcements available at the moment.</div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>