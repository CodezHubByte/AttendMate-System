<?php
session_start();
include '../header.php';
require_once '../db.php';

// Handle new announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announce'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $date = date('Y-m-d');

    if (!empty($title) && !empty($message)) {
        $insert = "INSERT INTO announcements (title, message, created_at) VALUES ('$title', '$message', '$date')";
        if (mysqli_query($conn, $insert)) {
            $_SESSION['success'] = "Announcement posted successfully.";
        } else {
            $_SESSION['error'] = "Failed to post announcement.";
        }
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
    }
    header("Location: Announcements.php");
    exit;
}

// Fetch existing announcements
$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
?>

<div class="main-content p-4">
    <h2 class="text-xl font-semibold mb-4">📅 Announcements</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'];
                                            unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'];
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" name="announce" class="btn btn-primary">Post Announcement</button>
    </form>

    <hr>

    <h4 class="mt-4">Previous Announcements</h4>
    <ul class="list-group mt-3">
        <?php if (mysqli_num_rows($announcements) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($announcements)): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($row['title']) ?></strong>
                    <div><small class="text-muted">Posted on <?= $row['created_at'] ?></small></div>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li class="list-group-item text-muted">No announcements yet.</li>
        <?php endif; ?>
    </ul>
</div>

<?php include '../footer.php'; ?>