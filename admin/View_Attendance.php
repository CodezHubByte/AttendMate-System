<?php
session_start();
include '../header.php';
require_once '../db.php';

// Optional: Get logged-in admin's name
$user = $_SESSION['user_name'] ?? 'Admin';

// Get selected filter values
$selected_course = mysqli_real_escape_string($conn, $_GET['course'] ?? '');
$user_id         = mysqli_real_escape_string($conn, $_GET['student_id'] ?? '');
$from_date       = mysqli_real_escape_string($conn, $_GET['from_date'] ?? '');
$to_date         = mysqli_real_escape_string($conn, $_GET['to_date'] ?? '');

// Build student query based on course
$student_query = "SELECT id, name FROM users WHERE role = 'user'";
if (!empty($selected_course)) {
    $student_query .= " AND course = '$selected_course'";
}
$student_query .= " ORDER BY name";
$students = mysqli_query($conn, $student_query);

// Build attendance query
$sql = "SELECT a.id, u.name, u.semester, u.course, a.date, a.status 
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        WHERE 1";

if (!empty($selected_course)) {
    $sql .= " AND u.course = '$selected_course'";
}
if (!empty($user_id)) {
    $sql .= " AND u.id = '$user_id'";
}
if (!empty($from_date) && !empty($to_date)) {
    $sql .= " AND a.date BETWEEN '$from_date' AND '$to_date'";
}
$sql .= " ORDER BY a.date DESC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    echo "<div class='alert alert-danger'>Error loading data: " . mysqli_error($conn) . "</div>";
    include '../footer.php';
    exit;
}
?>

<div class="dashboard-header">
    <h3>Hi, <?= htmlspecialchars($user) ?>!</h3>
    <p>Welcome to your dashboard.</p>
</div>

<div class="main-content p-4">
    <h2 class="text-xl font-semibold mb-4">📅 View Attendance Records</h2>

    <!-- Filter Form -->
    <form method="GET" class="mb-4 row g-3">
        <!-- Course Filter -->
        <div class="col-md-3">
            <label class="form-label">Filter by Course</label>
            <select name="course" class="form-select" onchange="this.form.submit()">
                <option value="">-- All Courses --</option>
                <option value="BCA" <?= $selected_course === 'BCA' ? 'selected' : '' ?>>BCA</option>
                <option value="MCA" <?= $selected_course === 'MCA' ? 'selected' : '' ?>>MCA</option>
                <option value="BBA" <?= $selected_course === 'BBA' ? 'selected' : '' ?>>BBA</option>
                <option value="MBA" <?= $selected_course === 'MBA' ? 'selected' : '' ?>>MBA</option>
            </select>
        </div>

        <!-- Student Dropdown (Filtered by Course) -->
        <div class="col-md-3">
            <label class="form-label">Filter by Student</label>
            <select name="student_id" class="form-select">
                <option value="">-- All Students --</option>
                <?php while ($student = mysqli_fetch_assoc($students)): ?>
                    <option value="<?= $student['id'] ?>" <?= ($student['id'] == $user_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($student['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
        </div>

        <div class="col-md-2">
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Attendance Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Semester</th>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td>
                                <?php if ($row['status'] === 'Present'): ?>
                                    <span class="badge bg-success">Present</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Absent</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-danger">No attendance records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>
