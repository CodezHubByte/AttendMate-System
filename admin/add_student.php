<?php
session_start();
require_once '../db.php'; // Adjusted path
include '../header.php';

$name = $email = $password = $course = $semester = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $course = $_POST['course'];
    $semester = $_POST['semester'];

    if ($name && $email && $password && $course && $semester) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, course, semester, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->bind_param("sssss", $name, $email, $hashed, $course, $semester);

        if ($stmt->execute()) {
            $_SESSION['message'] = "<div class='alert alert-success'>Student added successfully!</div>";
            header("Location: Manage_Students.php");
            exit();
        } else {
            $message = "Failed to add student.";
        }
    } else {
        $message = "Please fill all fields.";
    }
}
?>

<div class="card col-md-8 mx-auto mt-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Add New Student</h5>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email ID</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Course</label>
                <select name="course" class="form-select" required>
                    <option value="">Select Course</option>
                    <option value="BCA" <?= $course == 'BCA' ? 'selected' : '' ?>>BCA</option>
                    <option value="MCA" <?= $course == 'MCA' ? 'selected' : '' ?>>MCA</option>
                    <option value="BBA" <?= $course == 'BBA' ? 'selected' : '' ?>>BBA</option>
                    <option value="MBA" <?= $course == 'MBA' ? 'selected' : '' ?>>MBA</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select" required>
                    <option value="">Select Semester</option>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?= $i ?>" <?= $semester == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Add Student</button>
            <a href="Manage_Students.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>
