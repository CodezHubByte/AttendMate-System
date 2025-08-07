<?php
require '../db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Attendance_Report.xls");

$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$semester = $_GET['semester'] ?? '';
$course = $_GET['course'] ?? '';

$query = "SELECT u.name, u.semester, u.course,
            COUNT(a.id) AS total_days,
            SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_days,
            SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) AS absent_days
        FROM users u
        LEFT JOIN attendance a ON u.id = a.student_id";

$where = " WHERE u.role = 'user' ";
if (!empty($from_date) && !empty($to_date)) {
    $where .= " AND a.date BETWEEN '$from_date' AND '$to_date'";
}
if (!empty($semester)) {
    $where .= " AND u.semester = '$semester'";
}
if (!empty($course)) {
    $where .= " AND u.course = '$course'";
}

$query .= $where . " GROUP BY u.id ORDER BY u.name ASC";
$result = mysqli_query($conn, $query);

// Output table header
echo "<table border='1'>";
echo "<tr>
        <th>Name</th>
        <th>Semester</th>
        <th>Course</th>
        <th>Total Days</th>
        <th>Present</th>
        <th>Absent</th>
        <th>Attendance %</th>
      </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $percentage = ($row['total_days'] > 0) ? round(($row['present_days'] / $row['total_days']) * 100, 2) : 0;
    echo "<tr>
            <td>{$row['name']}</td>
            <td>{$row['semester']}</td>
            <td>{$row['course']}</td>
            <td>{$row['total_days']}</td>
            <td>{$row['present_days']}</td>
            <td>{$row['absent_days']}</td>
            <td><strong>{$percentage}%</strong></td>
          </tr>";
}
echo "</table>";
