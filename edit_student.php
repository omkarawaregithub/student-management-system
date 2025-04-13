<?php
$page_title = "Edit Student";
$active_page = "students";
include 'includes/header.php';
include 'config/db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$student_id = (int)$_GET['id'];

// Initialize variables
$success = false;
$error = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $roll_no = $conn->real_escape_string($_POST['roll_no']);
    $class = $conn->real_escape_string($_POST['class']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    // Validate form data
    if (empty($name) || empty($roll_no) || empty($class) || empty($email) || empty($phone)) {
        $error = "All fields are required";
    } else {
        // Check if roll number already exists for other students
        $check_sql = "SELECT id FROM students WHERE roll_no = '$roll_no' AND id != $student_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error = "Roll No already exists for another student";
        } else {
            // Update student data
            $sql = "UPDATE students SET name = '$name', roll_no = '$roll_no', class = '$class', email = '$email', phone = '$phone' WHERE id = $student_id";
            
            if ($conn->query($sql) === TRUE) {
                $success = true;
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}

// Fetch student data
$student_sql = "SELECT * FROM students WHERE id = $student_id";
$student_result = $conn->query($student_sql);

// Check if student exists
if ($student_result->num_rows === 0) {
    header("Location: students.php");
    exit();
}

$student = $student_result->fetch_assoc();
?>

<!-- Edit Student Form -->
<div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> Student information has been updated successfully.
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
                <h5 class="mb-0">Edit Student Information</h5>
                <a href="student_profile.php?id=<?php echo $student_id; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Profile
                </a>
            </div>
            <div class="card-body">
                <form action="" method="post" id="studentForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $student['name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="roll_no" class="form-label">Roll No</label>
                        <input type="text" class="form-control" id="roll_no" name="roll_no" value="<?php echo $student['roll_no']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="class" class="form-label">Class</label>
                        <select class="form-select" id="class" name="class" required>
                            <option value="">Select Class</option>
                            <option value="Class 1" <?php echo ($student['class'] == 'Class 1') ? 'selected' : ''; ?>>Class 1</option>
                            <option value="Class 2" <?php echo ($student['class'] == 'Class 2') ? 'selected' : ''; ?>>Class 2</option>
                            <option value="Class 3" <?php echo ($student['class'] == 'Class 3') ? 'selected' : ''; ?>>Class 3</option>
                            <option value="Class 4" <?php echo ($student['class'] == 'Class 4') ? 'selected' : ''; ?>>Class 4</option>
                            <option value="Class 5" <?php echo ($student['class'] == 'Class 5') ? 'selected' : ''; ?>>Class 5</option>
                            <option value="Class 6" <?php echo ($student['class'] == 'Class 6') ? 'selected' : ''; ?>>Class 6</option>
                            <option value="Class 7" <?php echo ($student['class'] == 'Class 7') ? 'selected' : ''; ?>>Class 7</option>
                            <option value="Class 8" <?php echo ($student['class'] == 'Class 8') ? 'selected' : ''; ?>>Class 8</option>
                            <option value="Class 9" <?php echo ($student['class'] == 'Class 9') ? 'selected' : ''; ?>>Class 9</option>
                            <option value="Class 10" <?php echo ($student['class'] == 'Class 10') ? 'selected' : ''; ?>>Class 10</option>
                            <option value="Class 11" <?php echo ($student['class'] == 'Class 11') ? 'selected' : ''; ?>>Class 11</option>
                            <option value="Class 12" <?php echo ($student['class'] == 'Class 12') ? 'selected' : ''; ?>>Class 12</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $student['email']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $student['phone']; ?>" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Student</button>
                        <a href="student_profile.php?id=<?php echo $student_id; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>