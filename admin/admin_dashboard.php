<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['name'])) {
    header("Location: index.php");
    exit();
}
$user = $_SESSION['name'];

// Total Students
$studentResult = mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role = 'user'");
$studentCount = mysqli_fetch_row($studentResult)[0];

// Classes Taken
$classQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT date) FROM attendance");
$classCount = mysqli_fetch_row($classQuery)[0];

// Attendance Percent
$attendanceQuery = mysqli_query($conn, "SELECT COUNT(*) AS total, SUM(status = 'Present') AS present FROM attendance");
$data = mysqli_fetch_assoc($attendanceQuery);
$totalAttendance = $data['total'] ?? 0;
$present = $data['present'] ?? 0;
$attendancePercent = ($totalAttendance > 0) ? round(($present / $totalAttendance) * 100) : 0;

// Alerts = Announcements + Remarks
$alertResult = mysqli_query($conn, "
    SELECT 
        (SELECT COUNT(*) FROM announcements) + 
        (SELECT COUNT(*) FROM remarks) AS total_alerts
");
$alerts = mysqli_fetch_row($alertResult)[0];

// Recent Classes
$recentClasses = mysqli_query($conn, "
    SELECT  date, 
    ROUND(SUM(status='Present')/COUNT(*)*100) AS marked 
    FROM attendance 
    GROUP BY  date 
    ORDER BY date DESC 
    LIMIT 2
");

// Recent Remarks
$remarksQuery = mysqli_query($conn, "
    SELECT r.remark, u.name, r.created_at 
    FROM remarks r
    JOIN users u ON r.student_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 3
");
?>

<?php include('../header.php'); ?>

<!-- DASHBOARD HEADER -->
<div class="dashboard-header mb-4">
    <h3>Hi, <?php echo htmlspecialchars($user); ?>!</h3>
    <p>Here's today's summary and insights.</p>
</div>

<!-- STATS CARDS -->
<div class="row card-stats mb-4">
    <div class="col-md-3">
        <div class="card card-yellow p-3">
            <h5>Attendance</h5>
            <h2><?php echo $attendancePercent; ?>%</h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-blue p-3">
            <h5>Classes Taken</h5>
            <h2><?php echo $classCount; ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-pink p-3">
            <h5>Alerts</h5>
            <h2><?php echo $alerts; ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-purple p-3">
            <h5>Students</h5>
            <h2><?php echo $studentCount; ?></h2>
        </div>
    </div>
</div>

<!-- RECENT CLASSES -->
<h5>Recent Classes</h5>
<?php if ($recentClasses && mysqli_num_rows($recentClasses) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($recentClasses)): ?>
        <div class="subject-card mb-2">
            <p>Marked: <?= $row['marked']; ?>% | Date: <?= date('d M Y', strtotime($row['date'])); ?></p>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="alert alert-info">No recent classes found.</div>
<?php endif; ?>

<!-- RECENT REMARKS -->
<h5 class="mt-4">Recent Remarks</h5>
<?php if ($remarksQuery && mysqli_num_rows($remarksQuery) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($remarksQuery)): ?>
        <div class="alert alert-warning small mb-2">
            <strong><?= htmlspecialchars($row['name']) ?>:</strong>
            <?= htmlspecialchars($row['remark']) ?> <br>
            <small class="text-muted"><?= date('d M Y h:i A', strtotime($row['created_at'])) ?></small>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted">No recent remarks found.</p>
<?php endif; ?>

<?php include('../footer.php'); ?>
