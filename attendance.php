<?php
$page_title = "Take Attendance";
$active_page = "attendance";
include 'includes/header.php';
include 'config/db_connect.php';

// Get current date
$attendance_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Check if attendance already taken
$check_sql = "SELECT COUNT(*) as count FROM attendance WHERE date = '$attendance_date'";
$check_result = $conn->query($check_sql);
$attendance_exists = $check_result->fetch_assoc()['count'] > 0;

// Message variables
$success = false;
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_attendance'])) {
    $date = $conn->real_escape_string($_POST['date']);
    
    // Delete existing attendance records for the date
    $delete_sql = "DELETE FROM attendance WHERE date = '$date'";
    $conn->query($delete_sql);
    
    // Insert new attendance records
    $success = true;
    
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 11) === "attendance_") {
            $student_id = substr($key, 11);
            $status = $value;
            
            $insert_sql = "INSERT INTO attendance (student_id, date, status) VALUES ('$student_id', '$date', '$status')";
            
            if (!$conn->query($insert_sql)) {
                $success = false;
                $error = "Error: " . $conn->error;
                break;
            }
        }
    }
    
    if ($success) {
        // Redirect to prevent form resubmission
        header("Location: attendance.php?date=$date&success=1");
        exit();
    }
}

// Success message from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = true;
}

// Fetch class list for filter
$classes_sql = "SELECT DISTINCT class FROM students ORDER BY class";
$classes_result = $conn->query($classes_sql);

// Class filter
$class_filter = isset($_GET['class']) ? $conn->real_escape_string($_GET['class']) : "";
$class_where = !empty($class_filter) ? " WHERE class = '$class_filter'" : "";

// Fetch students
$students_sql = "SELECT * FROM students $class_where ORDER BY name";
$students_result = $conn->query($students_sql);

// Fetch existing attendance records for the date
$attendance_records = [];
$attendance_sql = "SELECT student_id, status FROM attendance WHERE date = '$attendance_date'";
$attendance_result = $conn->query($attendance_sql);

while ($attendance = $attendance_result->fetch_assoc()) {
    $attendance_records[$attendance['student_id']] = $attendance['status'];
}
?>

<!-- Date and Class Filter -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card animated-card">
            <div class="card-body">
                <form action="" method="get" class="row g-3">
                    <div class="col-md-6">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?php echo $attendance_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="class" class="form-label">Class</label>
                        <select name="class" class="form-select" id="class">
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

<!-- Attendance Form -->
<div class="row">
    <div class="col-md-12">
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> Attendance has been recorded successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="card animated-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendance for <?php echo date('F d, Y', strtotime($attendance_date)); ?></h5>
                <?php if ($attendance_exists): ?>
                <span class="badge bg-info">Attendance already recorded</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($students_result->num_rows === 0): ?>
                <div class="alert alert-info" role="alert">
                    No students found. Please add students or adjust the filter.
                </div>
                <?php else: ?>
                <form action="" method="post" id="attendanceForm">
                    <input type="hidden" name="date" value="<?php echo $attendance_date; ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col" width="5%">#</th>
                                    <th scope="col" width="15%">Roll No</th>
                                    <th scope="col" width="25%">Name</th>
                                    <th scope="col" width="15%">Class</th>
                                    <th scope="col" width="40%">Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 1; ?>
                                <?php while ($student = $students_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo $student['roll_no']; ?></td>
                                        <td><?php echo $student['name']; ?></td>
                                        <td><?php echo $student['class']; ?></td>
                                        <td>
                                            <div class="attendance-radio">
                                                <input type="radio" id="present_<?php echo $student['id']; ?>" name="attendance_<?php echo $student['id']; ?>" value="present" <?php echo (isset($attendance_records[$student['id']]) && $attendance_records[$student['id']] == 'present') ? 'checked' : ''; ?>>
                                                <label for="present_<?php echo $student['id']; ?>" class="present">
                                                    <i class="fas fa-check me-1"></i>Present
                                                </label>
                                            </div>
                                            <div class="attendance-radio">
                                                <input type="radio" id="absent_<?php echo $student['id']; ?>" name="attendance_<?php echo $student['id']; ?>" value="absent" <?php echo (isset($attendance_records[$student['id']]) && $attendance_records[$student['id']] == 'absent') ? 'checked' : ''; ?>>
                                                <label for="absent_<?php echo $student['id']; ?>" class="absent">
                                                    <i class="fas fa-times me-1"></i>Absent
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                        <button type="submit" name="submit_attendance" class="btn btn-primary btn-lg">Save Attendance</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>