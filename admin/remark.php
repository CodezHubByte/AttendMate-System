<?php
session_start();
require_once '../db.php';
include '../header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$success = $error = '';

// Get filters from GET
$selected_course = $_GET['course'] ?? '';
$selected_semester = $_GET['semester'] ?? '';

// Fetch distinct courses and semesters
$course_result = mysqli_query($conn, "SELECT DISTINCT course FROM users WHERE role = 'user'");
$semester_result = mysqli_query($conn, "SELECT DISTINCT semester FROM users WHERE role = 'user'");

// Fetch students based on selected course & semester
$students_result = [];
if ($selected_course && $selected_semester) {
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE role = 'user' AND course = ? AND semester = ? ORDER BY name");
    $stmt->bind_param("ss", $selected_course, $selected_semester);
    $stmt->execute();
    $students_result = $stmt->get_result();
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $course = $_POST['course'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $remark = trim($_POST['remark']);

    if ($student_id && $course && $semester && $remark) {
        $stmt = $conn->prepare("INSERT INTO remarks (student_id, course, semester, remark) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $student_id, $course, $semester, $remark);
        if ($stmt->execute()) {
            $success = "✅ Remark added successfully.";
        } else {
            $error = "❌ Failed to add remark.";
        }
        $stmt->close();
    } else {
        $error = "❗ Please fill all fields.";
    }
}
?>

<?php
if (!empty($success)) {
    echo "<script>alert('{$success}');</script>";
}
if (!empty($error)) {
    echo "<script>alert('{$error}');</script>";
}
?>

<div class="main-content p-4">
    <h2 class="text-xl font-semibold mb-4">📝 Add Remark to Student</h2>

    <!-- Course & Semester Filter Form -->
    <form method="GET" class="bg-light p-3 rounded shadow-sm mb-4" style="max-width: 600px;">
        <div class="row">
            <div class="col">
                <label class="form-label">Select Course</label>
                <select name="course" class="form-select" required>
                    <option value="">-- Choose Course --</option>
                    <?php while ($row = mysqli_fetch_assoc($course_result)): ?>
                        <option value="<?= $row['course'] ?>" <?= ($selected_course == $row['course']) ? 'selected' : '' ?>>
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
                        <option value="<?= $row['semester'] ?>" <?= ($selected_semester == $row['semester']) ? 'selected' : '' ?>>
                            <?= $row['semester'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-secondary mt-3">Filter Students</button>
    </form>

    <?php if (!empty($students_result) && mysqli_num_rows($students_result) > 0): ?>
        <!-- Remark Form -->
        <form method="POST" class="bg-white p-4 rounded shadow-sm" style="max-width: 600px;">
            <div class="mb-3">
                <label class="form-label">Select Student</label>
                <select name="student_id" id="student_id" class="form-select" required>
                    <option value="">-- Choose Student --</option>
                    <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                        <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Course</label>
                <input type="text" name="course" id="course" class="form-control" value="<?= $selected_course ?>" readonly required>
            </div>

            <div class="mb-3">
                <label class="form-label">Semester</label>
                <input type="text" name="semester" id="semester" class="form-control" value="<?= $selected_semester ?>" readonly required>
            </div>

            <div class="mb-3">
                <label class="form-label">Remark</label>
                <textarea name="remark" class="form-control" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Remark</button>
        </form>
    <?php elseif ($selected_course && $selected_semester): ?>
        <p>No students found for the selected course and semester.</p>
    <?php else: ?>
        <p>Please select course and semester to begin.</p>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
