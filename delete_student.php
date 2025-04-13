<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include 'config/db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$student_id = (int)$_GET['id'];

// Check if student exists
$check_sql = "SELECT id FROM students WHERE id = $student_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows === 0) {
    header("Location: students.php");
    exit();
}

// Delete student and related attendance
$delete_attendance_sql = "DELETE FROM attendance WHERE student_id = $student_id";
$conn->query($delete_attendance_sql);

$delete_student_sql = "DELETE FROM students WHERE id = $student_id";
$conn->query($delete_student_sql);

// Redirect back to students page
header("Location: students.php?deleted=1");
exit();
?>