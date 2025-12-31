// API Configuration
// Use InfinityFree API (works with database)
const API_BASE_URL = 'https://hcthegreat.ct.ws/api';

// Vercel can't access InfinityFree database, so DON'T use:
// const API_BASE_URL = 'https://your-vercel-url.vercel.app/api';

// Storage
let currentUser = null;
let authToken = null;

// Clean API Response (removes InfinityFree JavaScript injection)
function cleanAPIResponse(responseText) {
    try {
        // Remove all <script> tags and their content
        let cleaned = responseText.replace(/<script[\s\S]*?<\/script>/gi, '');
        
        // Remove all <noscript> tags and their content
        cleaned = cleaned.replace(/<noscript[\s\S]*?<\/noscript>/gi, '');
        
        // Remove HTML tags
        cleaned = cleaned.replace(/<\/?[^>]+(>|$)/g, '');
        
        // Extract JSON from the cleaned string
        const firstBrace = cleaned.indexOf('{');
        const lastBrace = cleaned.lastIndexOf('}');
        
        if (firstBrace !== -1 && lastBrace !== -1) {
            cleaned = cleaned.substring(firstBrace, lastBrace + 1);
        }
        
        // Parse and return JSON
        return JSON.parse(cleaned);
    } catch (error) {
        console.error('Failed to clean API response:', error);
        console.error('Raw response:', responseText);
        throw new Error('Invalid API response format');
    }
}

// Fetch API with cleaning and InfinityFree redirect handling
async function fetchAPI(url, options = {}, retryCount = 0) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });
        
        const text = await response.text();
        console.log('Raw response:', text.substring(0, 200) + '...');
        
        // Check if InfinityFree redirect (contains location.href with ?i=1)
        if (text.includes('location.href') && text.includes('?i=1') && retryCount < 2) {
            console.log('InfinityFree verification detected, retrying...');
            await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1 second
            
            // Retry with ?i=1 parameter
            const newUrl = url.includes('?') ? `${url}&i=1` : `${url}?i=1`;
            return fetchAPI(newUrl, options, retryCount + 1);
        }
        
        const data = cleanAPIResponse(text);
        console.log('Cleaned data:', data);
        
        return {
            ok: response.ok,
            status: response.status,
            data: data,
        };
    } catch (error) {
        console.error('API Error:', error);
        
        // If cleaning failed and we haven't retried with ?i=1, try that
        if (error.message.includes('Invalid API response') && retryCount < 2 && !url.includes('?i=1')) {
            console.log('Retrying with ?i=1 parameter...');
            await new Promise(resolve => setTimeout(resolve, 1000));
            const newUrl = url.includes('?') ? `${url}&i=1` : `${url}?i=1`;
            return fetchAPI(newUrl, options, retryCount + 1);
        }
        
        throw error;
    }
}

// Show/Hide Response
function showResponse(elementId, type, message, data = null) {
    const element = document.getElementById(elementId);
    element.className = `response ${type}`;
    element.innerHTML = `<strong>${message}</strong>`;
    
    if (data) {
        element.innerHTML += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
    }
    
    // Auto hide after 5 seconds for success
    if (type === 'success') {
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }
}

// Tab Navigation
function showTab(tabName) {
    // Hide all cards
    document.querySelectorAll('.card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Remove active from all tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected card
    document.getElementById(tabName).classList.add('active');
    
    // Activate button
    event.target.classList.add('active');
}

// Toggle student/faculty fields
function toggleRoleFields() {
    const role = document.getElementById('reg-role').value;
    const studentFields = document.getElementById('student-fields');
    
    if (role === 'student') {
        studentFields.style.display = 'grid';
        document.getElementById('reg-division').required = true;
        document.getElementById('reg-semester').required = true;
    } else {
        studentFields.style.display = 'none';
        document.getElementById('reg-division').required = false;
        document.getElementById('reg-semester').required = false;
    }
}

// Student Login
async function studentLogin(event) {
    event.preventDefault();
    
    const username = document.getElementById('stud-username').value;
    const password = document.getElementById('stud-password').value;
    
    try {
        const result = await fetchAPI(`${API_BASE_URL}/stud_login.php`, {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });
        
        if (result.data.status === 'success') {
            authToken = result.data.token;
            currentUser = { ...result.data, role: 'student' };
            
            // Show student tabs
            document.getElementById('profileTab').style.display = 'block';
            document.getElementById('attendanceTab').style.display = 'block';
            document.getElementById('markTab').style.display = 'block';
            
            showResponse('stud-login-response', 'success', 'Login successful!', result.data);
            
            setTimeout(() => {
                showTab('profile');
                loadProfile();
            }, 1500);
        } else {
            showResponse('stud-login-response', 'error', result.data.message, result.data);
        }
    } catch (error) {
        showResponse('stud-login-response', 'error', 'Login failed: ' + error.message);
    }
}

// Faculty Login
async function facultyLogin(event) {
    event.preventDefault();
    
    const username = document.getElementById('fac-username').value;
    const password = document.getElementById('fac-password').value;
    
    try {
        const result = await fetchAPI(`${API_BASE_URL}/faculty_login.php`, {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });
        
        if (result.data.status === 'success') {
            authToken = result.data.token;
            currentUser = { ...result.data, role: 'faculty' };
            
            // Show faculty tabs
            document.getElementById('profileTab').style.display = 'block';
            document.getElementById('generateTab').style.display = 'block';
            
            showResponse('fac-login-response', 'success', 'Login successful!', result.data);
            
            setTimeout(() => {
                showTab('profile');
                loadProfile();
            }, 1500);
        } else {
            showResponse('fac-login-response', 'error', result.data.message, result.data);
        }
    } catch (error) {
        showResponse('fac-login-response', 'error', 'Login failed: ' + error.message);
    }
}

// Register User
async function registerUser(event) {
    event.preventDefault();
    
    const role = document.getElementById('reg-role').value;
    const userData = {
        username: document.getElementById('reg-username').value,
        password: document.getElementById('reg-password').value,
        full_name: document.getElementById('reg-fullname').value,
        role: role,
        branch: document.getElementById('reg-branch').value
    };
    
    if (role === 'student') {
        userData.division = document.getElementById('reg-division').value;
        userData.semester = parseInt(document.getElementById('reg-semester').value);
    }
    
    try {
        const result = await fetchAPI(`${API_BASE_URL}/register_user.php`, {
            method: 'POST',
            body: JSON.stringify(userData)
        });
        
        if (result.data.status === 'success') {
            showResponse('register-response', 'success', 'Registration successful! You can now login.', result.data);
            event.target.reset();
        } else {
            showResponse('register-response', 'error', result.data.message, result.data);
        }
    } catch (error) {
        showResponse('register-response', 'error', 'Registration failed: ' + error.message);
    }
}

// Load Profile
async function loadProfile() {
    if (!authToken) {
        document.getElementById('profile-content').innerHTML = '<p>Please login first</p>';
        return;
    }
    
    try {
        const result = await fetchAPI(`${API_BASE_URL}/display_profile.php`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        
        if (result.data.status === 'success') {
            const profile = result.data.data;
            let html = '<div class="profile-info">';
            
            for (const [key, value] of Object.entries(profile)) {
                if (value) {
                    html += `
                        <div class="profile-item">
                            <label>${key.replace('_', ' ').toUpperCase()}</label>
                            <span>${value}</span>
                        </div>
                    `;
                }
            }
            
            html += '</div>';
            document.getElementById('profile-content').innerHTML = html;
        } else {
            showResponse('profile-response', 'error', result.data.message);
        }
    } catch (error) {
        showResponse('profile-response', 'error', 'Failed to load profile: ' + error.message);
    }
}

// Load Attendance
async function loadAttendance() {
    if (!authToken) {
        document.getElementById('attendance-content').innerHTML = '<p>Please login first</p>';
        return;
    }
    
    try {
        const result = await fetchAPI(`${API_BASE_URL}/show_attendance.php`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        
        if (result.data.status === 'success') {
            const records = result.data.data;
            
            if (records.length === 0) {
                document.getElementById('attendance-content').innerHTML = '<p>No attendance records found.</p>';
                return;
            }
            
            let html = `
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class ID</th>
                            <th>Branch</th>
                            <th>Division</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            records.forEach(record => {
                html += `
                    <tr>
                        <td>${record.date}</td>
                        <td>${record.class_id}</td>
                        <td>${record.branch}</td>
                        <td>${record.division}</td>
                        <td>${record.marked_time}</td>
                        <td><span class="status-badge ${record.status}">${record.status}</span></td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            document.getElementById('attendance-content').innerHTML = html;
        } else {
            showResponse('attendance-response', 'error', result.data.message);
        }
    } catch (error) {
        showResponse('attendance-response', 'error', 'Failed to load attendance: ' + error.message);
    }
}

// Generate Class ID
async function generateClassId(event) {
    event.preventDefault();
    
    if (!authToken) {
        showResponse('generate-response', 'error', 'Please login as faculty first');
        return;
    }
    
    const branch = document.getElementById('gen-branch').value;
    const division = document.getElementById('gen-division').value;
    
    try {
        const result = await fetchAPI(`${API_BASE_URL}/generate_id.php`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({ branch, division })
        });
        
        if (result.data.status === 'success') {
            const classId = result.data.class_id;
            const expiresAt = result.data.expires_at;
            
            const html = `
                <div class="class-id-display">
                    <h3>✅ Class Session Created!</h3>
                    <div class="id">${classId}</div>
                    <p>Share this ID with students</p>
                    <p><small>Expires: ${expiresAt}</small></p>
                </div>
            `;
            
            document.getElementById('class-id-display').innerHTML = html;
            showResponse('generate-response', 'success', 'Class session created successfully!', result.data);
        } else {
            showResponse('generate-response', 'error', result.data.message, result.data);
        }
    } catch (error) {
        showResponse('generate-response', 'error', 'Failed to generate class ID: ' + error.message);
    }
}

// Mark Attendance
async function markAttendance(event) {
    event.preventDefault();
    
    if (!authToken) {
        showResponse('mark-response', 'error', 'Please login as student first');
        return;
    }
    
    const classId = document.getElementById('mark-classid').value;
    
    try {
        const result = await fetchAPI(`${API_BASE_URL}/mark_present.php`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`
            },
            body: JSON.stringify({ class_id: classId })
        });
        
        if (result.data.status === 'success') {
            showResponse('mark-response', 'success', 'Attendance marked successfully!', result.data);
            event.target.reset();
        } else {
            showResponse('mark-response', 'error', result.data.message, result.data);
        }
    } catch (error) {
        showResponse('mark-response', 'error', 'Failed to mark attendance: ' + error.message);
    }
}

// Logout
function logout() {
    authToken = null;
    currentUser = null;
    
    // Hide protected tabs
    document.getElementById('profileTab').style.display = 'none';
    document.getElementById('attendanceTab').style.display = 'none';
    document.getElementById('generateTab').style.display = 'none';
    document.getElementById('markTab').style.display = 'none';
    
    // Show login tab
    showTab('student-login');
    
    // Clear forms
    document.querySelectorAll('form').forEach(form => form.reset());
    document.querySelectorAll('.response').forEach(res => res.style.display = 'none');
}

// Auto-load attendance when tab is shown
document.addEventListener('click', (e) => {
    if (e.target.textContent === 'Attendance' && authToken) {
        loadAttendance();
    }
});
