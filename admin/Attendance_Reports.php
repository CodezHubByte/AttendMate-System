<?php
session_start();
include '../header.php';
require_once '../db.php';

// Get filters
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$filter_semester = $_GET['semester'] ?? '';
$filter_course = $_GET['course'] ?? '';

// Dropdowns
$semesters = mysqli_query($conn, "SELECT DISTINCT semester FROM users WHERE role='user'");
$courses = mysqli_query($conn, "SELECT DISTINCT course FROM users WHERE role='user'");

// Build SQL Query
$query = "SELECT u.id, u.name, u.semester, u.course,
            COUNT(a.id) AS total_days,
            SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_days,
            SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_days
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id";

$where = " WHERE u.role = 'user' ";
if (!empty($from_date) && !empty($to_date)) {
    $where .= " AND a.date BETWEEN '$from_date' AND '$to_date'";
}
if (!empty($filter_semester)) {
    $where .= " AND u.semester = '$filter_semester'";
}
if (!empty($filter_course)) {
    $where .= " AND u.course = '$filter_course'";
}

$query .= $where . " GROUP BY u.id ORDER BY u.name ASC";
$result = mysqli_query($conn, $query);
?>
<div class="dashboard-header">
    <h3>Hi, <?php echo htmlspecialchars($user); ?>!</h3>
    <p>Welcome to your dashboard.</p>
</div>

<div class="main-content p-4">
    <h2 class="text-xl font-semibold mb-4">📊 Attendance Report</h2>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>From Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= $from_date ?>">
        </div>
        <div class="col-md-3">
            <label>To Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= $to_date ?>">
        </div>
        <div class="col-md-2">
            <label>Semester</label>
            <select name="semester" class="form-select">
                <option value="">All Semesters</option>
                <?php while ($s = mysqli_fetch_assoc($semesters)): ?>
                    <option value="<?= $s['semester'] ?>" <?= ($s['semester'] == $filter_semester) ? 'selected' : '' ?>>
                        <?= $s['semester'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Course</label>
            <select name="course" class="form-select">
                <option value="">All Courses</option>
                <?php while ($c = mysqli_fetch_assoc($courses)): ?>
                    <option value="<?= $c['course'] ?>" <?= ($c['course'] == $filter_course) ? 'selected' : '' ?>>
                        <?= $c['course'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Generate</button>
        </div>
    </form>


    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Semester</th>
                    <th>Course</th>
                    <th>Total</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                        $percentage = ($row['total_days'] > 0)
                            ? round(($row['present_days'] / $row['total_days']) * 100, 2)
                            : 0;
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['semester'] ?></td>
                            <td><?= $row['course'] ?></td>
                            <td><?= $row['total_days'] ?></td>
                            <td><?= $row['present_days'] ?></td>
                            <td><?= $row['absent_days'] ?></td>
                            <td><strong><?= $percentage ?>%</strong></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-danger">No data found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="GET" action="export_attendance_excel.php" target="_blank">
            <input type="hidden" name="from_date" value="<?= $from_date ?>">
            <input type="hidden" name="to_date" value="<?= $to_date ?>">
            <input type="hidden" name="semester" value="<?= $filter_semester ?>">
            <input type="hidden" name="course" value="<?= $filter_course ?>">
            <button type="submit" class="btn btn-success mt-3">Download Report as Excel</button>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>