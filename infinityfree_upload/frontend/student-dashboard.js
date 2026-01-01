// Student Dashboard Logic
document.addEventListener('DOMContentLoaded', async () => {
    const auth = checkAuth();
    if (!auth) return;

    const { token } = auth;

    // Load Profile
    await loadProfile(token);

    // Check Wi-Fi Status
    checkWiFiStatus();

    // Load Today's Status
    await loadTodayStatus(token);

    // Load Recent Attendance
    await loadRecentAttendance(token);

    // Load Weekly Schedule
    await loadWeeklySchedule(token);

    // Mark Attendance Button
    document.getElementById('markAttendanceBtn').addEventListener('click', () => {
        window.location.href = 'mark-attendance.html';
    });
});

// Load User Profile
async function loadProfile(token) {
    try {
        const result = await apiCall('display_profile.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (result.data.status === 'success') {
            const profile = result.data.data;
            document.getElementById('studentName').textContent = profile.full_name || profile.username;
            document.getElementById('studentBranch').textContent = profile.branch || '-';
            document.getElementById('studentDiv').textContent = profile.division || '-';
        }
    } catch (error) {
        console.error('Profile load error:', error);
    }
}

// Check Wi-Fi Status (simulated - in production, use actual network API)
function checkWiFiStatus() {
    const statusDot = document.getElementById('wifiStatusDot');
    const statusText = document.getElementById('wifiStatusText');
    const networkName = document.getElementById('wifiNetwork');

    // Simulate Wi-Fi check (in production, use navigator.connection or server-side check)
    const isConnected = navigator.onLine;
    
    if (isConnected) {
        statusDot.className = 'status-dot status-dot-success';
        statusText.textContent = 'Connected to Network';
        networkName.textContent = 'University Wi-Fi';
    } else {
        statusDot.className = 'status-dot status-dot-error';
        statusText.textContent = 'Not Connected';
        networkName.textContent = 'No Connection';
    }

    // Re-check every 10 seconds
    setInterval(() => {
        const online = navigator.onLine;
        if (online !== isConnected) {
            location.reload();
        }
    }, 10000);
}

// Load Today's Status
async function loadTodayStatus(token) {
    const container = document.getElementById('todayStatus');
    
    try {
        const result = await apiCall('show_attendance.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (result.data.status === 'success') {
            const records = result.data.data || [];
            const today = new Date().toISOString().split('T')[0];
            const todayRecords = records.filter(r => r.date === today);

            container.innerHTML = `
                <div style="text-align: center;">
                    <div style="font-size: var(--font-size-3xl); font-weight: 700; color: ${todayRecords.length > 0 ? 'var(--success)' : 'var(--text-secondary)'};">
                        ${todayRecords.length}
                    </div>
                    <p class="text-secondary" style="font-size: var(--font-size-sm); margin-top: var(--space-1);">
                        Classes Today
                    </p>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--primary);">
                        ${records.length}
                    </div>
                    <p class="text-secondary" style="font-size: var(--font-size-sm); margin-top: var(--space-1);">
                        Total This Month
                    </p>
                </div>
            `;
        } else {
            throw new Error('Failed to load');
        }
    } catch (error) {
        console.error('Status load error:', error);
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-secondary);">
                Unable to load status
            </div>
        `;
    }
}

// Load Recent Attendance
async function loadRecentAttendance(token) {
    const container = document.getElementById('recentAttendance');
    
    try {
        const result = await apiCall('show_attendance.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (result.data.status === 'success') {
            const records = result.data.data || [];
            
            if (records.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: var(--space-8); color: var(--text-secondary);">
                        <svg class="icon icon-lg" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto var(--space-4);">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>No attendance records yet</p>
                    </div>
                `;
                return;
            }

            // Show last 5 records
            const recentRecords = records.slice(0, 5);
            
            container.innerHTML = recentRecords.map(record => `
                <div style="display: flex; align-items: center; justify-content: space-between; padding: var(--space-3) 0; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: 500; margin-bottom: var(--space-1);">
                            ${record.branch} - Division ${record.division}
                        </div>
                        <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">
                            ${formatDate(record.date)} at ${formatTime(record.marked_time)}
                        </div>
                    </div>
                    <span class="badge badge-success">
                        <svg class="icon" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Present
                    </span>
                </div>
            `).join('');
        } else {
            throw new Error('Failed to load');
        }
    } catch (error) {
        console.error('Attendance load error:', error);
        container.innerHTML = `
            <div style="text-align: center; padding: var(--space-4); color: var(--text-secondary);">
                Unable to load attendance records
            </div>
        `;
    }
}

// Load Weekly Schedule
async function loadWeeklySchedule(token) {
    const container = document.getElementById('weeklySchedule');
    
    try {
        const result = await apiCall('get_student_schedule.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (result.data.success) {
            const schedule = result.data.schedule;
            const division = result.data.division;
            const semester = result.data.semester;
            
            // Check if schedule is empty
            const hasSchedule = Object.values(schedule).some(day => day.length > 0);
            
            if (!hasSchedule) {
                container.innerHTML = `
                    <div style="text-align: center; padding: var(--space-8); color: var(--text-secondary);">
                        <svg class="icon icon-lg" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto var(--space-4);">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p>No schedule available for Division ${division} - Semester ${semester}</p>
                        <p style="font-size: var(--font-size-sm); margin-top: var(--space-2);">
                            Contact your admin to add schedule
                        </p>
                    </div>
                `;
                return;
            }

            // Build schedule HTML
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            container.innerHTML = `
                <div style="font-size: var(--font-size-sm); color: var(--text-secondary); margin-bottom: var(--space-3);">
                    Division ${division} • Semester ${semester}
                </div>
                ${days.map(day => {
                    const classes = schedule[day] || [];
                    
                    if (classes.length === 0) {
                        return ''; // Skip days with no classes
                    }
                    
                    return `
                        <div style="margin-bottom: var(--space-4); padding-bottom: var(--space-4); border-bottom: 1px solid var(--border);">
                            <div style="font-weight: 600; margin-bottom: var(--space-2); color: var(--primary);">
                                ${day}
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: var(--space-2);">
                                ${classes.map(cls => `
                                    <div style="background: var(--bg-secondary); padding: var(--space-2) var(--space-3); border-radius: var(--radius); font-size: var(--font-size-sm);">
                                        <div style="font-weight: 500; margin-bottom: 2px;">${cls.subject}</div>
                                        ${cls.semester ? `<div style="font-size: var(--font-size-xs); color: var(--success); font-weight: 500;">Semester ${cls.semester}</div>` : ''}
                                        ${cls.time_slot ? `<div style="font-size: var(--font-size-xs); color: var(--text-secondary);">${cls.time_slot}</div>` : ''}
                                        ${cls.faculty_name ? `<div style="font-size: var(--font-size-xs); color: var(--text-secondary);">Prof. ${cls.faculty_name}</div>` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }).join('')}
            `;
        } else {
            throw new Error('Failed to load schedule');
        }
    } catch (error) {
        console.error('Schedule load error:', error);
        container.innerHTML = `
            <div style="text-align: center; padding: var(--space-4); color: var(--text-secondary);">
                Unable to load weekly schedule
            </div>
        `;
    }
}
