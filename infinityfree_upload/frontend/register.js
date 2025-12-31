// Register Page Logic
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');
    
    // Toggle student-specific fields
    window.toggleRoleFields = function() {
        const role = document.querySelector('input[name="role"]:checked').value;
        const studentFields = document.getElementById('studentFields');
        const division = document.getElementById('division');
        const semester = document.getElementById('semester');
        
        if (role === 'student') {
            studentFields.style.display = 'block';
            division.required = true;
            semester.required = true;
        } else {
            studentFields.style.display = 'none';
            division.required = false;
            semester.required = false;
            division.value = '';
            semester.value = '';
        }
    };
    
    // Initialize form
    toggleRoleFields();
    
    // Form submission
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(registerForm);
        const role = formData.get('role');
        const username = formData.get('username').trim();
        const fullName = formData.get('fullName').trim();
        const password = formData.get('password');
        const confirmPassword = formData.get('confirmPassword');
        const branch = formData.get('branch');
        
        // Validate passwords match
        if (password !== confirmPassword) {
            showAlert('Passwords do not match!', 'error');
            return;
        }
        
        // Validate password strength
        if (!isValidPassword(password)) {
            showAlert('Password must contain at least one uppercase letter, one lowercase letter, and one number', 'error');
            return;
        }
        
        // Prepare registration data
        const registerData = {
            username: username,
            password: password,
            full_name: fullName,
            role: role,
            branch: branch
        };
        
        // Add student-specific fields
        if (role === 'student') {
            const division = formData.get('division');
            const semester = formData.get('semester');
            
            if (!division || !semester) {
                showAlert('Please select division and semester', 'error');
                return;
            }
            
            registerData.division = division;
            registerData.semester = parseInt(semester);
        }
        
        // Show loading state
        registerBtn.disabled = true;
        registerBtn.textContent = 'Creating Account...';
        
        try {
            const response = await apiCall('register_user.php', {
                method: 'POST',
                body: JSON.stringify(registerData)
            });
            
            if (response.data.status === 'success') {
                showAlert('Account created successfully! Redirecting to login...', 'success');
                
                // Redirect to login after 2 seconds
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                showAlert(response.data.message || 'Registration failed', 'error');
                registerBtn.disabled = false;
                registerBtn.textContent = 'Create Account';
            }
        } catch (error) {
            console.error('Registration error:', error);
            showAlert('Registration failed: ' + error.message, 'error');
            registerBtn.disabled = false;
            registerBtn.textContent = 'Create Account';
        }
    });
});

// Password validation helper
function isValidPassword(password) {
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const isLongEnough = password.length >= 8;
    
    return hasUpperCase && hasLowerCase && hasNumbers && isLongEnough;
}
