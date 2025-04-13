
<?php
$page_title = "Student Profile";
$active_page = "students";
include 'includes/header.php';
include 'config/db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$student_id = (int)$_GET['id'];

// Fetch student data
$student_sql = "SELECT * FROM students WHERE id = $student_id";
$student_result = $conn->query($student_sql);

// Check if student exists
if ($student_result->num_rows === 0) {
    header("Location: students.php");
    exit();
}

$student = $student_result->fetch_assoc();

// Get attendance history
$attendance_sql = "
    SELECT a.date, a.status
    FROM attendance a
    WHERE a.student_id = $student_id
    ORDER BY a.date DESC
    LIMIT 10
";
$attendance_result = $conn->query($attendance_sql);

// Calculate attendance statistics
$stats_sql = "
    SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
    FROM attendance
    WHERE student_id = $student_id
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$attendance_rate = 0;
if ($stats['total_days'] > 0) {
    $attendance_rate = round(($stats['present_days'] / $stats['total_days']) * 100);
}
?>

<!-- Student Profile -->
<div class="row">
    <div class="col-lg-4 col-md-5 mb-4">
        <div class="card animated-card">
            <div class="card-body text-center">
                <div class="avatar bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                </div>
                <h4 class="card-title"><?php echo $student['name']; ?></h4>
                <p class="text-muted mb-1">Roll No: <?php echo $student['roll_no']; ?></p>
                <p class="text-muted mb-3">Class: <?php echo $student['class']; ?></p>
                
                <div class="d-flex justify-content-center mb-2">
                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">
                        <i class="fas fa-trash me-1"></i> Delete
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card animated-card mt-4" style="animation-delay: 0.1s;">
            <div class="card-header">
                <h5 class="mb-0">Contact Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-envelope me-2"></i> Email</span>
                        <span><?php echo $student['email']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-phone me-2"></i> Phone</span>
                        <span><?php echo $student['phone']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar me-2"></i> Registered</span>
                        <span><?php echo date('M d, Y', strtotime($student['created_at'])); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8 col-md-7">
        <div class="card animated-card" style="animation-delay: 0.2s;">
            <div class="card-header">
                <h5 class="mb-0">Attendance Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <h4 class="text-success"><?php echo $stats['present_days'] ?: 0; ?></h4>
                        <p class="text-muted mb-0">Present Days</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h4 class="text-danger"><?php echo $stats['absent_days'] ?: 0; ?></h4>
                        <p class="text-muted mb-0">Absent Days</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h4 class="text-primary"><?php echo $attendance_rate; ?>%</h4>
                        <p class="text-muted mb-0">Attendance Rate</p>
                    </div>
                </div>
                
                <div class="progress mt-3">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $attendance_rate; ?>%;" aria-valuenow="<?php echo $attendance_rate; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $attendance_rate; ?>%
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card animated-card mt-4" style="animation-delay: 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Attendance</h5>
                <a href="attendance.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($attendance_result->num_rows > 0): ?>
                                <?php while ($attendance = $attendance_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y (D)', strtotime($attendance['date'])); ?></td>
                                        <td>
                                            <?php if ($attendance['status'] === 'present'): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4">No attendance records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>