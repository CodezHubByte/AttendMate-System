<?php
session_start();
require_once '../db.php';
include '../header.php';

$query = "SELECT id, name, email, course, semester FROM users WHERE role = 'user' ORDER BY id ASC";
$result = $conn->query($query);

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<div class="dashboard-header">
    <h3>Hi, Admin!</h3>
    <p>Welcome to your dashboard.</p>
</div>

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Manage Students</h5>
        <a href="add_student.php" class="btn btn-light btn-sm">+ Add Student</a>
    </div>
    <div class="card-body">
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['course']); ?></td>
                            <td><?= htmlspecialchars($row['semester']); ?></td>
                            <td>
                                <a href="edit_student.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_student.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this student?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No students found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>
