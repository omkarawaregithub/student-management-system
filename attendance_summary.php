
<?php
$page_title = "Attendance Summary";
$active_page = "attendance_summary";
include 'includes/header.php';
include 'config/db_connect.php';

// Get date for filter, default to today
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Class filter
$class_filter = isset($_GET['class']) ? $conn->real_escape_string($_GET['class']) : "";
$class_where = !empty($class_filter) ? " AND s.class = '$class_filter'" : "";

// Fetch classes for filter
$classes_sql = "SELECT DISTINCT class FROM students ORDER BY class";
$classes_result = $conn->query($classes_sql);

// Get summary data
$summary_sql = "
    SELECT 
        COUNT(*) as total_students,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id AND a.date = '$filter_date'
    WHERE 1=1 $class_where
";
$summary_result = $conn->query($summary_sql);
$summary = $summary_result->fetch_assoc();

// Get detailed attendance data
$details_sql = "
    SELECT 
        s.id, s.name, s.roll_no, s.class, s.email, s.phone,
        a.status
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id AND a.date = '$filter_date'
    WHERE 1=1 $class_where
    ORDER BY s.class, s.name
";
$details_result = $conn->query($details_sql);

// Get attendance history (last 7 days)
$history_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_formatted = date('M d', strtotime("-$i days"));
    
    $history_sql = "
        SELECT 
            '$date_formatted' as date,
            COUNT(*) as total_students,
            SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count
        FROM students s
        LEFT JOIN attendance a ON s.id = a.student_id AND a.date = '$date'
        WHERE 1=1 $class_where
    ";
    $history_result = $conn->query($history_sql);
    $history_row = $history_result->fetch_assoc();
    
    $history_data[] = $history_row;
}
?>

<!-- Filter Form -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card animated-card">
            <div class="card-body">
                <form action="" method="get" class="row g-3" id="filter-form">
                    <div class="col-md-6">
                        <label for="date-filter" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date-filter" name="date" value="<?php echo $filter_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="class-filter" class="form-label">Class</label>
                        <select name="class" class="form-select" id="class-filter">
                            <option value="">All Classes</option>
                            <?php while ($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['class']; ?>" <?php echo ($class_filter == $class['class']) ? 'selected' : ''; ?>>
                                    <?php echo $class['class']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card animated-card">
            <div class="card-body stat-card">
                <i class="fas fa-users text-primary"></i>
                <h3 class="stat-number"><?php echo $summary['total_students']; ?></h3>
                <p class="stat-title">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card animated-card" style="animation-delay: 0.1s;">
            <div class="card-body stat-card">
                <i class="fas fa-check-circle text-success"></i>
                <h3 class="stat-number"><?php echo $summary['present_count'] ?: 0; ?></h3>
                <p class="stat-title">Present</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card animated-card" style="animation-delay: 0.2s;">
            <div class="card-body stat-card">
                <i class="fas fa-times-circle text-danger"></i>
                <h3 class="stat-number"><?php echo $summary['absent_count'] ?: 0; ?></h3>
                <p class="stat-title">Absent</p>
            </div>
        </div>
    </div>
</div>

<!-- Attendance History Chart -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card animated-card" style="animation-delay: 0.3s;">
            <div class="card-header">
                <h5 class="mb-0">Attendance History (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="attendanceHistory" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Attendance Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card animated-card" style="animation-delay: 0.4s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detailed Attendance for <?php echo date('F d, Y', strtotime($filter_date)); ?></h5>
                <a href="attendance.php?date=<?php echo $filter_date; ?>&class=<?php echo $class_filter; ?>" class="btn btn-sm btn-primary">
                    Take/Edit Attendance
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Roll No</th>
                                <th scope="col">Name</th>
                                <th scope="col">Class</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($details_result->num_rows > 0): ?>
                                <?php while ($student = $details_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $student['roll_no']; ?></td>
                                        <td><?php echo $student['name']; ?></td>
                                        <td><?php echo $student['class']; ?></td>
                                        <td>
                                            <?php if ($student['status'] === 'present'): ?>
                                                <span class="badge bg-success">Present</span>
                                            <?php elseif ($student['status'] === 'absent'): ?>
                                                <span class="badge bg-danger">Absent</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Marked</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No students found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart JS for attendance history
    const ctx = document.getElementById('attendanceHistory').getContext('2d');
    
    // Prepare data from PHP
    const historyData = <?php echo json_encode($history_data); ?>;
    
    const dates = historyData.map(item => item.date);
    const presentData = historyData.map(item => parseInt(item.present_count) || 0);
    const absentData = historyData.map(item => parseInt(item.absent_count) || 0);
    
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Present',
                    data: presentData,
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    tension: 0.1
                },
                {
                    label: 'Absent',
                    data: absentData,
                    backgroundColor: 'rgba(231, 76, 60, 0.2)',
                    borderColor: '#e74c3c',
                    borderWidth: 2,
                    tension: 0.1
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
    
    // Auto-submit date filter when changed
    document.getElementById('date-filter').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    // Auto-submit class filter when changed
    document.getElementById('class-filter').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>