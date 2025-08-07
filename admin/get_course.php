<?php
require_once '../db.php';

if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    $result = mysqli_query($conn, "SELECT course FROM users WHERE id = $student_id LIMIT 1");

    if ($row = mysqli_fetch_assoc($result)) {
        echo $row['course'];
    } else {
        echo '';
    }
}
?>
