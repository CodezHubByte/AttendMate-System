<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['name']) || !isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['name'];
$role = $_SESSION['role'];
$activePage = basename($_SERVER['PHP_SELF']); // For active menu highlight
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AttendMate Dashboard</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="../CSS/dashboard.css" rel="stylesheet" />

    <style>
        .sidebar a.active {
            background-color: #e0e0ff;
            font-weight: bold;
            border-left: 4px solid #4a00e0;
        }
        
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="text-center py-4">
                    <h4><i class="fas fa-school me-2 text-primary"></i><strong>AttendMate</strong></h4>
                </div>

                <?php if ($role === 'admin'): ?>
                    <a href="admin_dashboard.php" class="<?= ($activePage == 'admin_dashboard.php') ? 'active' : '' ?>">
                        <i class="fas fa-home me-2"></i> Dashboard</a>

                    <a href="Manage_Students.php" class="<?= ($activePage == 'Manage_Students.php') ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate me-2"></i> Manage Students</a>

                    <a href="Mark_Attendance.php" class="<?= ($activePage == 'Mark_Attendance.php') ? 'active' : '' ?>">
                        <i class="fas fa-edit me-2"></i> Mark Attendance</a>
                        
                    <a href="View_Attendance.php" class="<?= ($activePage == 'View_Attendance.php') ? 'active' : '' ?>">
                        <i class="fas fa-eye me-2"></i> View Attendance</a>

                    <a href="Attendance_Reports.php" class="<?= ($activePage == 'Attendance_Reports.php') ? 'active' : '' ?>">
                        <i class="fas fa-chart-line me-2"></i> Attendance Reports</a>

                    <a href="Announcements.php" class="<?= ($activePage == 'Announcements.php') ? 'active' : '' ?>">
                        <i class="fas fa-bullhorn me-2"></i> Announcements</a>

     <a href="remark.php" class="<?= ($activePage == 'remark.php') ? 'active' : '' ?>">
        <i class="fas fa-sticky-note me-2"></i> Remark</a>

                    <a href="view_all_remarks.php" class="<?= ($activePage == 'view_all_remarks.php') ? 'active' : '' ?>">
    <i class="fas fa-comments me-2"></i> View Remarks
</a>


                    

                <?php elseif ($role === 'user'): ?>
                    <a href="student_dashboard.php" class="<?= ($activePage == 'student_dashboard.php') ? 'active' : '' ?>">
                        <i class="fas fa-home me-2"></i> Dashboard</a>

                    <a href="myattendance.php" class="<?= ($activePage == 'myattendance.php') ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check me-2"></i> My Attendance</a>

                    <a href="profile.php" class="<?= ($activePage == 'profile.php') ? 'active' : '' ?>">
                        <i class="fas fa-user me-2"></i> My Profile</a>

                    <a href="announcements.php" class="<?= ($activePage == 'announcements.php') ? 'active' : '' ?>">
                        <i class="fas fa-bullhorn me-2"></i> Announcements</a>

                    <a href="changepassword.php" class="<?= ($activePage == 'changepassword.php') ? 'active' : '' ?>">
                        <i class="fas fa-key me-2"></i> Change Password</a>
                <?php endif; ?>

                <a href="../logout.php" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">