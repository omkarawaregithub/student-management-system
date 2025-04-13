<?php
$page_title = "Student List";
$active_page = "students";
include 'includes/header.php';
include 'config/db_connect.php';

// Initialize variables
$search = "";
$class_filter = "";
$where_clause = "";

// Search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clause .= " WHERE (name LIKE '%$search%' OR roll_no LIKE '%$search%' OR email LIKE '%$search%')";
}

// Class filter
if (isset($_GET['class']) && !empty($_GET['class'])) {
    $class_filter = $conn->real_escape_string($_GET['class']);
    
    if (empty($where_clause)) {
        $where_clause .= " WHERE class = '$class_filter'";
    } else {
        $where_clause .= " AND class = '$class_filter'";
    }
}

// Fetch all classes for the filter dropdown
$classes_sql = "SELECT DISTINCT class FROM students ORDER BY class";
$classes_result = $conn->query($classes_sql);

// Pagination
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $results_per_page;

// Count total records
$count_sql = "SELECT COUNT(*) as total FROM students" . $where_clause;
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $results_per_page);

// Fetch students with pagination
$students_sql = "SELECT * FROM students" . $where_clause . " ORDER BY name LIMIT $offset, $results_per_page";
$students_result = $conn->query($students_sql);
?>

<!-- Search and Filter Bar -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card animated-card">
            <div class="card-body">
                <form action="" method="get" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search by name, roll no, or email" value="<?php echo $search; ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="class" class="form-select" onchange="this.form.submit()">
                            <option value="">All Classes</option>
                            <?php while ($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['class']; ?>" <?php echo ($class_filter == $class['class']) ? 'selected' : ''; ?>>
                                    <?php echo $class['class']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="add_student.php" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Students List -->
<div class="row">
    <div class="col-md-12">
        <div class="card animated-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Students</h5>
                <span class="badge bg-primary"><?php echo $total_records; ?> students</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Roll No</th>
                                <th scope="col">Name</th>
                                <th scope="col">Class</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students_result->num_rows > 0): ?>
                                <?php while ($student = $students_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $student['roll_no']; ?></td>
                                        <td><?php echo $student['name']; ?></td>
                                        <td><?php echo $student['class']; ?></td>
                                        <td><?php echo $student['email']; ?></td>
                                        <td><?php echo $student['phone']; ?></td>
                                        <td class="text-center">
                                            <a href="student_profile.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info text-white me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning text-white me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No students found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&class=<?php echo $class_filter; ?>">Previous</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&class=<?php echo $class_filter; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&class=<?php echo $class_filter; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>