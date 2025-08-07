<?php
session_start();
require_once '../db.php';

// Check if student is logged in
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user' || !isset($_SESSION['id'])) {
    echo "User ID not found in session. Please log in again.";
    exit();
}

$user = $_SESSION['name'];
$email = $_SESSION['email'];
$userId = $_SESSION['id'];

// Initialize stats
$attendancePercent = 0;
$classesTaken = 0;
$alerts = 0;

// Attendance Percentage
$attendedQuery = "SELECT COUNT(*) AS attended FROM attendance WHERE user_id = '$userId' AND status = 'Present'";
$totalQuery = "SELECT COUNT(*) AS total FROM attendance WHERE user_id = '$userId'";

$attendedResult = mysqli_query($conn, $attendedQuery);
$totalResult = mysqli_query($conn, $totalQuery);

if ($attendedResult && $totalResult) {
    $attended = mysqli_fetch_assoc($attendedResult)['attended'];
    $total = mysqli_fetch_assoc($totalResult)['total'];
    $attendancePercent = ($total > 0) ? round(($attended / $total) * 100) : 0;
}

// Classes Taken
$classQuery = "SELECT COUNT(DISTINCT date) AS total_classes FROM attendance WHERE user_id = '$userId'";
$classResult = mysqli_query($conn, $classQuery);
if ($classResult) {
    $classesTaken = mysqli_fetch_assoc($classResult)['total_classes'];
}

// Combined Alerts (Absent + Remarks)
$absentCount = 0;
$remarkCount = 0;

// Count absents
$absentQuery = "SELECT COUNT(*) AS total FROM attendance WHERE user_id = '$userId' AND status = 'Absent'";
$absentResult = mysqli_query($conn, $absentQuery);
if ($absentResult) {
    $absentCount = mysqli_fetch_assoc($absentResult)['total'];
}

// Count remarks
$remarkQuery = "SELECT COUNT(*) AS total FROM remarks WHERE student_id = '$userId'";
$remarkResult = mysqli_query($conn, $remarkQuery);
if ($remarkResult) {
    $remarkCount = mysqli_fetch_assoc($remarkResult)['total'];
}

$alerts = $absentCount + $remarkCount;

// Recent Classes
$recentQuery = "SELECT status, date FROM attendance WHERE user_id = '$userId' ORDER BY date DESC LIMIT 2";
$recentResult = mysqli_query($conn, $recentQuery);

// Personal Remarks
$remarksQuery = mysqli_query($conn, "
    SELECT remark, created_at 
    FROM remarks 
    WHERE student_id = '$userId' 
    ORDER BY created_at DESC 
    LIMIT 3
");
?>

<?php include('../header.php'); ?>

<!-- Main Content -->
<div class="col-md-40 p-50">
    <div class="dashboard-header mb-10">
        <h3>Hi, <?php echo htmlspecialchars($user); ?>!</h3>
        <p>Here's your attendance overview.</p>
    </div>

    <!-- Stats Cards -->
    <div class="row card-stats mb-4">
        <div class="col-md-3">
            <div class="card card-yellow p-3">
                <h5>Attendance</h5>
                <h2><?= $attendancePercent; ?>%</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-blue p-3">
                <h5>Classes Taken</h5>
                <h2><?= $classesTaken; ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-pink p-3">
                <h5>Alerts</h5>
                <h2><?= $alerts; ?></h2>

            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-purple p-3">
                <h5>Student</h5>
                <h2>1</h2>
            </div>
        </div>
    </div>

    <!-- Recent Classes -->
    <h5>Recent Classes</h5>
    <?php if ($recentResult && mysqli_num_rows($recentResult) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($recentResult)): ?>
            <div class="subject-card mb-2">
                <p>Status: <?= htmlspecialchars($row['status']); ?> |
                    Date: <?= date("d M Y", strtotime($row['date'])); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No recent classes found.</div>
    <?php endif; ?>

    <!-- Personal Remarks -->
    <h5 class="mt-4">🔔 Your Remarks</h5>
    <?php if ($remarksQuery && mysqli_num_rows($remarksQuery) > 0): ?>
        <?php while ($remark = mysqli_fetch_assoc($remarksQuery)): ?>
            <div class="alert alert-warning small mb-2">
                <?= htmlspecialchars($remark['remark']); ?><br>
                <small class="text-muted"><?= date('d M Y h:i A', strtotime($remark['created_at'])) ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-muted">You have no remarks.</div>
    <?php endif; ?>
</div>

<?php include('../footer.php'); ?>