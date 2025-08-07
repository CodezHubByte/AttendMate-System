<?php
$host = 'localhost';
$db = 'attendmate';
$usr = 'root';
$pwd = '';
$conn = new mysqli($host, $usr, $pwd, $db);
if ($conn->connect_error) die("DB Error: " . $conn->connect_error);
