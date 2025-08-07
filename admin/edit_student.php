<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get student ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid student ID!";
    header("Location: Manage_Students.php");
    exit();
}

$student_id = $_GET['id'];

// Fetch current data including course & semester
$stmt = $conn->prepare("SELECT id, name, email, course, semester FROM users WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['message'] = "Student not found!";
    header("Location: Manage_Students.php");
    exit();
}

$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $semester = trim($_POST['semester']);

    if ($name !== '' && $email !== '' && $course !== '' && $semester !== '') {
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, course = ?, semester = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $name, $email, $course, $semester, $student_id);
        $update_stmt->execute();

        $_SESSION['message'] = "Student updated successfully!";
        header("Location: Manage_Students.php");
        exit();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<?php include '../header.php'; ?>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5>Edit Student</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Student Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Student Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="course" class="form-label">Course</label>
                <select name="course" id="course" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <option value="BCA" <?= ($student['course'] == 'BCA') ? 'selected' : '' ?>>BCA</option>
                    <option value="MCA" <?= ($student['course'] == 'MCA') ? 'selected' : '' ?>>MCA</option>
                    <option value="BBA" <?= ($student['course'] == 'BBA') ? 'selected' : '' ?>>BBA</option>
                    <option value="MBA" <?= ($student['course'] == 'MBA') ? 'selected' : '' ?>>MBA</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select name="semester" id="semester" class="form-select" required>
                    <option value="">-- Select Semester --</option>
                    <option value="1" <?= ($student['semester'] == '1') ? 'selected' : '' ?>>Semester 1</option>
                    <option value="2" <?= ($student['semester'] == '2') ? 'selected' : '' ?>>Semester 2</option>
                    <option value="3" <?= ($student['semester'] == '3') ? 'selected' : '' ?>>Semester 3</option>
                    <option value="4" <?= ($student['semester'] == '4') ? 'selected' : '' ?>>Semester 4</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="Manage_Students.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>