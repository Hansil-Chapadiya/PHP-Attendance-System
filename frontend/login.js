// Login Page Logic
document.addEventListener('DOMContentLoaded', () => {
    let selectedRole = 'student';

    // Role Selection
    const roleButtons = document.querySelectorAll('.role-btn');
    roleButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove active from all
            roleButtons.forEach(b => b.classList.remove('active'));
            
            // Add active to clicked
            btn.classList.add('active');
            selectedRole = btn.dataset.role;
        });
    });

    // Login Form
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        // Validation
        if (!username || !password) {
            showAlert('Please enter both username and password', 'error');
            return;
        }

        // Show loading
        loginBtn.disabled = true;
        loginBtn.innerHTML = `
            <div class="spinner"></div>
            Signing in...
        `;

        try {
            // Choose endpoint based on role
            let endpoint = 'stud_login.php';
            if (selectedRole === 'faculty') {
                endpoint = 'faculty_login.php';
            } else if (selectedRole === 'admin') {
                endpoint = 'stud_login.php'; // Admin uses same endpoint
            }

            const result = await apiCall(endpoint, {
                method: 'POST',
                body: JSON.stringify({ username, password })
            });

            if (result.data.status === 'success') {
                // Save auth data
                saveToStorage(STORAGE_KEYS.TOKEN, result.data.token);
                saveToStorage(STORAGE_KEYS.USER, result.data);
                saveToStorage(STORAGE_KEYS.ROLE, selectedRole);

                // Show success
                showAlert('Login successful! Redirecting...', 'success');

                // Redirect based on role
                setTimeout(() => {
                    switch (selectedRole) {
                        case 'student':
                            window.location.href = 'student-dashboard.html';
                            break;
                        case 'faculty':
                            window.location.href = 'faculty-dashboard.html';
                            break;
                        case 'admin':
                            window.location.href = 'admin-dashboard.html';
                            break;
                    }
                }, 1000);
            } else {
                showAlert(result.data.message || 'Login failed', 'error');
                loginBtn.disabled = false;
                loginBtn.innerHTML = 'Sign In';
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('Connection error. Please try again.', 'error');
            loginBtn.disabled = false;
            loginBtn.innerHTML = 'Sign In';
        }
    });

    // Auto-focus username
    document.getElementById('username').focus();
});
