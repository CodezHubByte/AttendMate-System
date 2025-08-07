<?php
session_start();
include '../header.php';
require_once '../db.php';

$user = $_SESSION['name'] ?? 'Admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attendance_date'])) {
    $date = $_POST['attendance_date'];

    // Check if attendance already exists for this date
    $check_existing = mysqli_query($conn, "SELECT * FROM attendance WHERE date = '$date'");
    if (mysqli_num_rows($check_existing) > 0) {
        $_SESSION['error'] = "Attendance has already been marked for this date!";
        header("Location: mark_attendance.php?course={$_GET['course']}&semester={$_GET['semester']}");
        exit;
    }

    if (!isset($_POST['attendance']) || !is_array($_POST['attendance'])) {
        $_SESSION['error'] = "No attendance data submitted.";
        header("Location: mark_attendance.php?course={$_GET['course']}&semester={$_GET['semester']}");
        exit;
    }

    $attendance_data = $_POST['attendance'];

    $stmt = $conn->prepare("INSERT INTO attendance (user_id, date, status) VALUES (?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: mark_attendance.php?course={$_GET['course']}&semester={$_GET['semester']}");
        exit;
    }

    foreach ($attendance_data as $user_id => $status) {
        $status = ($status === 'Present') ? 'Present' : 'Absent';
        $stmt->bind_param("iss", $user_id, $date, $status);
        $stmt->execute();
    }
    $stmt->close();

    $_SESSION['success'] = "Attendance marked successfully!";
    header("Location: mark_attendance.php?course={$_GET['course']}&semester={$_GET['semester']}");
    exit;
}

// GET filters
$selected_course = $_GET['course'] ?? '';
$selected_semester = $_GET['semester'] ?? '';

// Fetch distinct courses and semesters for dropdowns
$courses = mysqli_query($conn, "SELECT DISTINCT course FROM users WHERE role = 'user'");
$semesters = mysqli_query($conn, "SELECT DISTINCT semester FROM users WHERE role = 'user'");

// Fetch filtered students
$students = [];
if ($selected_course && $selected_semester) {
    $stmt = $conn->prepare("SELECT id, name, semester, course FROM users WHERE role = 'user' AND course = ? AND semester = ? ORDER BY name");
    $stmt->bind_param("ss", $selected_course, $selected_semester);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<div class="dashboard-header">
    <h3>Hi, <?php echo htmlspecialchars($user); ?>!</h3>
    <p>Welcome to your dashboard.</p>
</div>

<div class="main-content p-4">
    <h2 class="text-xl font-semibold mb-4">📝 Mark Attendance</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <label for="filter_course">Select Course:</label>
        <select name="course" id="filter_course" class="form-control w-50" required>
            <option value="">-- Select Course --</option>
            <?php while ($row = mysqli_fetch_assoc($courses)): ?>
                <option value="<?= $row['course'] ?>" <?= ($selected_course == $row['course']) ? 'selected' : '' ?>>
                    <?= $row['course'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="filter_semester" class="mt-3">Select Semester:</label>
        <select name="semester" id="filter_semester" class="form-control w-50" required>
            <option value="">-- Select Semester --</option>
            <?php while ($row = mysqli_fetch_assoc($semesters)): ?>
                <option value="<?= $row['semester'] ?>" <?= ($selected_semester == $row['semester']) ? 'selected' : '' ?>>
                    <?= $row['semester'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" class="btn btn-secondary mt-3">Filter</button>
    </form>

    <?php if (!empty($students) && mysqli_num_rows($students) > 0): ?>
        <!-- Attendance Form -->
        <form method="POST" id="attendanceForm">
            <input type="hidden" name="attendance_date" value="<?= date('Y-m-d') ?>">

            <label>Select Date:</label>
            <input type="date" name="attendance_date" class="form-control w-50" required value="<?= date('Y-m-d') ?>">

            <table class="table table-bordered mt-4">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($students)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['semester']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td>
                                <label><input type="radio" name="attendance[<?= (int)$row['id'] ?>]" value="Present" required> Present</label>
                                <label><input type="radio" name="attendance[<?= (int)$row['id'] ?>]" value="Absent" required> Absent</label>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </form>
    <?php elseif ($selected_course && $selected_semester): ?>
        <p>No students found for the selected course and semester.</p>
    <?php else: ?>
        <p>Please select a course and semester to view students.</p>
    <?php endif; ?>
</div>

<script>
    document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
        const radios = document.querySelectorAll('input[type="radio"]:checked');
        const totalRows = document.querySelectorAll('tbody tr').length;
        if (radios.length !== totalRows) {
            e.preventDefault();
            alert("Please mark attendance (Present or Absent) for all students.");
        }
    });
</script>

<?php include '../footer.php'; ?>
