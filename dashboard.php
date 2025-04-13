<?php
$page_title = "Dashboard";
$active_page = "dashboard";
include 'includes/header.php';
include 'config/db_connect.php';

// Get total students
$students_sql = "SELECT COUNT(*) as total_students FROM students";
$students_result = $conn->query($students_sql);
$total_students = $students_result->fetch_assoc()['total_students'];

// Get today's date
$today = date('Y-m-d');

// Get present students today
$present_sql = "SELECT COUNT(*) as present_count FROM attendance WHERE date = '$today' AND status = 'present'";
$present_result = $conn->query($present_sql);
$present_count = $present_result->fetch_assoc()['present_count'];

// Get absent students today
$absent_sql = "SELECT COUNT(*) as absent_count FROM attendance WHERE date = '$today' AND status = 'absent'";
$absent_result = $conn->query($absent_sql);
$absent_count = $absent_result->fetch_assoc()['absent_count'];

// Get recent students
$recent_students_sql = "SELECT * FROM students ORDER BY created_at DESC LIMIT 5";
$recent_students_result = $conn->query($recent_students_sql);

// Get attendance history (last 7 days)
$past_week = [];
$attendance_data = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $past_week[] = date('M d', strtotime("-$i days"));
    
    $day_present_sql = "SELECT COUNT(*) as count FROM attendance WHERE date = '$date' AND status = 'present'";
    $day_present_result = $conn->query($day_present_sql);
    $day_present = $day_present_result->fetch_assoc()['count'];
    
    $day_absent_sql = "SELECT COUNT(*) as count FROM attendance WHERE date = '$date' AND status = 'absent'";
    $day_absent_result = $conn->query($day_absent_sql);
    $day_absent = $day_absent_result->fetch_assoc()['count'];
    
    $attendance_data[] = [
        'date' => date('M d', strtotime("-$i days")),
        'present' => $day_present,
        'absent' => $day_absent
    ];
}

$conn->close();
?>

<!-- Dashboard Content -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card animated-card">
            <div class="card-body stat-card">
                <i class="fas fa-users text-primary"></i>
                <h3 class="stat-number"><?php echo $total_students; ?></h3>
                <p class="stat-title">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card animated-card" style="animation-delay: 0.1s;">
            <div class="card-body stat-card">
                <i class="fas fa-check-circle text-success"></i>
                <h3 class="stat-number"><?php echo $present_count; ?></h3>
                <p class="stat-title">Present Today</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card animated-card" style="animation-delay: 0.2s;">
            <div class="card-body stat-card">
                <i class="fas fa-times-circle text-danger"></i>
                <h3 class="stat-number"><?php echo $absent_count; ?></h3>
                <p class="stat-title">Absent Today</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card animated-card" style="animation-delay: 0.3s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendance History</h5>
                <a href="attendance_summary.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card animated-card" style="animation-delay: 0.4s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Students</h5>
                <a href="students.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php while ($student = $recent_students_result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0"><?php echo $student['name']; ?></h6>
                                <small class="text-muted">Roll No: <?php echo $student['roll_no']; ?></small>
                            </div>
                            <a href="student_profile.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </li>
                    <?php endwhile; ?>
                    <?php if ($recent_students_result->num_rows === 0): ?>
                    <li class="list-group-item text-center py-4">
                        <p class="text-muted mb-0">No students found</p>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card animated-card" style="animation-delay: 0.5s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="add_student.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Add New Student
                    </a>
                    <a href="attendance.php" class="btn btn-success">
                        <i class="fas fa-clipboard-check me-2"></i>Take Attendance
                    </a>
                    <a href="attendance_summary.php" class="btn btn-info text-white">
                        <i class="fas fa-chart-bar me-2"></i>Attendance Reports
                    </a>
                    <a href="students.php" class="btn btn-secondary">
                        <i class="fas fa-list me-2"></i>Student List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart JS for attendance history
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    
    // Prepare data from PHP
    const dates = <?php echo json_encode($past_week); ?>;
    const attendanceData = <?php echo json_encode($attendance_data); ?>;
    
    const presentData = attendanceData.map(item => item.present);
    const absentData = attendanceData.map(item => item.absent);
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Present',
                    data: presentData,
                    backgroundColor: '#2ecc71',
                    borderColor: '#27ae60',
                    borderWidth: 1
                },
                {
                    label: 'Absent',
                    data: absentData,
                    backgroundColor: '#e74c3c',
                    borderColor: '#c0392b',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>