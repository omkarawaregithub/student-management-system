// Form validation for register form
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            let isValid = true;
            let errorMessages = [];
            
            // Username validation
            if (!/^[a-zA-Z0-9]{4,20}$/.test(username)) {
                errorMessages.push('Username must be 4-20 alphanumeric characters');
                isValid = false;
            }
            
            // Email validation
            if (!/^\S+@\S+\.\S+$/.test(email)) {
                errorMessages.push('Invalid email format');
                isValid = false;
            }
            
            // Password validation
            if (password.length < 8) {
                errorMessages.push('Password must be at least 8 characters');
                isValid = false;
            }
            
            // Confirm password validation
            if (password !== confirmPassword) {
                errorMessages.push('Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n' + errorMessages.join('\n'));
            }
        });
    }
});

// Attendance form validation
document.addEventListener('DOMContentLoaded', function() {
    const attendanceForm = document.getElementById('attendanceForm');
    
    if (attendanceForm) {
        attendanceForm.addEventListener('submit', function(e) {
            const attendanceInputs = document.querySelectorAll('input[name^="attendance"]');
            let validInputs = 0;
            
            attendanceInputs.forEach(input => {
                if (input.checked) {
                    validInputs++;
                }
            });
            
            // Check if at least one student attendance is marked
            if (validInputs === 0) {
                e.preventDefault();
                alert('Please mark attendance for at least one student');
            }
        });
    }
});

// Student form validation
document.addEventListener('DOMContentLoaded', function() {
    const studentForm = document.getElementById('studentForm');
    
    if (studentForm) {
        studentForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const rollNo = document.getElementById('roll_no').value;
            const studentClass = document.getElementById('class').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            
            let isValid = true;
            let errorMessages = [];
            
            // Name validation (only letters and spaces, 2-50 chars)
            if (!/^[A-Za-z\s]{2,50}$/.test(name)) {
                errorMessages.push('Name must be 2-50 characters (letters and spaces only)');
                isValid = false;
            }
            
            // Roll No validation (alphanumeric, 2-20 chars)
            if (!/^[A-Za-z0-9]{2,20}$/.test(rollNo)) {
                errorMessages.push('Roll No must be 2-20 alphanumeric characters');
                isValid = false;
            }
            
            // Class validation (not empty)
            if (!studentClass.trim()) {
                errorMessages.push('Class is required');
                isValid = false;
            }
            
            // Email validation
            if (!/^\S+@\S+\.\S+$/.test(email)) {
                errorMessages.push('Invalid email format');
                isValid = false;
            }
            
            // Phone validation (digits, 10-15 chars)
            if (!/^\d{10,15}$/.test(phone)) {
                errorMessages.push('Phone must be 10-15 digits');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n' + errorMessages.join('\n'));
            }
        });
    }
});

// Date filter validation
document.addEventListener('DOMContentLoaded', function() {
    const dateFilter = document.getElementById('date-filter');
    
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('filter-form').submit();
            }
        });
    }
});