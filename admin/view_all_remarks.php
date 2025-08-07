<?php
session_start();
require_once '../db.php';
include '../header.php';

// Check admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$selected_course = $_GET['course'] ?? '';
$selected_semester = $_GET['semester'] ?? '';

// Fetch all distinct courses and semesters for filters
$course_result = mysqli_query($conn, "SELECT DISTINCT course FROM users WHERE role = 'user'");
$semester_result = mysqli_query($conn, "SELECT DISTINCT semester FROM users WHERE role = 'user'");

// Fetch filtered remarks
$remarks = [];

if ($selected_course && $selected_semester) {
    $stmt = $conn->prepare("
        SELECT r.id, r.remark, r.created_at, 
               u.name AS student_name, u.course, u.semester
        FROM remarks r
        JOIN users u ON r.student_id = u.id
        WHERE u.course = ? AND u.semester = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("ss", $selected_course, $selected_semester);
    $stmt->execute();
    $remarks = $stmt->get_result();
    $stmt->close();
}
?>

<div class="main-content p-4">
    <h2 class="text-xl font-semibold mb-4">📋 All Student Remarks (Filtered)</h2>

    <!-- Filter Form -->
    <form method="GET" class="bg-light p-3 rounded shadow-sm mb-4" style="max-width: 600px;">
        <div class="row">
            <div class="col">
                <label class="form-label">Select Course</label>
                <select name="course" class="form-select" required>
                    <option value="">-- Choose Course --</option>
                    <?php while ($row = mysqli_fetch_assoc($course_result)): ?>
                        <option value="<?= $row['course'] ?>" <?= ($selected_course === $row['course']) ? 'selected' : '' ?>>
                            <?= $row['course'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col">
                <label class="form-label">Select Semester</label>
                <select name="semester" class="form-select" required>
                    <option value="">-- Choose Semester --</option>
                    <?php while ($row = mysqli_fetch_assoc($semester_result)): ?>
                        <option value="<?= $row['semester'] ?>" <?= ($selected_semester === $row['semester']) ? 'selected' : '' ?>>
                            <?= $row['semester'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-secondary mt-3">Filter Remarks</button>
    </form>

    <?php if ($selected_course && $selected_semester): ?>
        <?php if ($remarks && mysqli_num_rows($remarks) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Remark</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($remarks)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['course']) ?></td>
                                <td><?= htmlspecialchars($row['semester']) ?></td>
                                <td><?= nl2br(htmlspecialchars($row['remark'])) ?></td>
                                <td><?= date('d M Y h:i A', strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No remarks found for the selected course and semester.</div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">Please select course and semester to view remarks.</div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
