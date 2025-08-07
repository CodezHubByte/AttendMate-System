<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit();
}

$studentName = $_SESSION['name'];
$userId = $_SESSION['id'] ?? null;

if ($userId === null) {
    die("User ID not found in session. Please log in again.");
}

// Fetch attendance records with course and semester from users table
$query = "SELECT a.date, u.course, u.semester, a.status
          FROM attendance a
          JOIN users u ON a.user_id = u.id
          WHERE a.user_id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die("SQL Prepare Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Count stats
$total = $present = $absent = 0;
$records = [];

while ($row = mysqli_fetch_assoc($result)) {
    $records[] = $row;
    $total++;
    if (strtolower($row['status']) === 'present') $present++;
    else $absent++;
}

$attendancePercent = $total > 0 ? round(($present / $total) * 100) : 0;

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<?php include('../header.php'); ?>

<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3>Welcome, <?php echo htmlspecialchars($studentName); ?> 👩‍🎓</h3>
        <p class="mb-3 text-muted">Your attendance summary:</p>

        <!-- Attendance Summary -->
        <div class="summary mb-4">
            <div class="row text-center">
                <div class="col"><strong>✅ Present:</strong> <?php echo $present; ?></div>
                <div class="col"><strong>❌ Absent:</strong> <?php echo $absent; ?></div>
                <div class="col"><strong>📊 Percentage:</strong> <?php echo $attendancePercent; ?>%</div>
            </div>
            <div class="progress mt-3">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $attendancePercent; ?>%">
                    <?php echo $attendancePercent; ?>%
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($records) > 0): ?>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['course']); ?></td>
                                <td><?php echo htmlspecialchars($row['semester']); ?></td>
                                <td>
                                    <?php if (strtolower($row['status']) === 'present'): ?>
                                        <span class="badge bg-success">Present</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Absent</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('../footer.php'); ?>
                        