
<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Student Management System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 sidebar p-0">
            <div class="logo text-center py-4">
                <h3 class="text-primary">Student MS</h3>
                <p class="text-muted mb-0">Admin Panel</p>
            </div>
            <div class="nav flex-column nav-pills mt-3" id="nav-tab" role="tablist">
                <a class="nav-link <?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link <?php echo ($active_page == 'students') ? 'active' : ''; ?>" href="students.php">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
                <a class="nav-link <?php echo ($active_page == 'attendance') ? 'active' : ''; ?>" href="attendance.php">
                    <i class="fas fa-clipboard-check"></i> Attendance
                </a>
                <a class="nav-link <?php echo ($active_page == 'attendance_summary') ? 'active' : ''; ?>" href="attendance_summary.php">
                    <i class="fas fa-chart-bar"></i> Attendance Summary
                </a>
                <a class="nav-link <?php echo ($active_page == 'add_student') ? 'active' : ''; ?>" href="add_student.php">
                    <i class="fas fa-user-plus"></i> Add Student
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9 col-md-8 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h2>
                <div class="user-info d-flex align-items-center">
                    <div class="me-3">
                        <strong><?php echo $_SESSION['admin_username']; ?></strong>
                        <div class="text-muted small">Administrator</div>
                    </div>
                    <div class="avatar bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>