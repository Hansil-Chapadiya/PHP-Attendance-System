// Faculty Dashboard Logic
let allAttendanceRecords = []; // Store all records for filtering
let teachingScheduleData = {}; // Store teaching schedule

document.addEventListener('DOMContentLoaded', async () => {
    const auth = checkAuth();
    if (!auth) return;

    const { token } = auth;

    // Load Profile
    await loadFacultyProfile(token);

    // Load Attendance Records
    await loadAttendanceRecords(token);

    // Load Teaching Schedule
    await loadTeachingSchedule(token);

    // Auto-refresh attendance records every 30 seconds
    setInterval(() => {
        loadAttendanceRecords(token);
    }, 30000);

    // Manual refresh button
    const refreshBtn = document.getElementById('refreshAttendanceBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<div class="spinner" style="width: 16px; height: 16px;"></div> Refreshing...';
            await loadAttendanceRecords(token);
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            `;
        });
    }

    // Attendance filter event listeners
    const filterDivision = document.getElementById('filterDivision');
    const filterSubject = document.getElementById('filterSubject');
    const filterDate = document.getElementById('filterDate');
    const clearFiltersBtn = document.getElementById('clearFilters');
    
    if (filterDivision) {
        filterDivision.addEventListener('change', () => displayAttendanceRecords(allAttendanceRecords));
    }
    if (filterSubject) {
        filterSubject.addEventListener('change', () => displayAttendanceRecords(allAttendanceRecords));
    }
    if (filterDate) {
        filterDate.addEventListener('change', () => displayAttendanceRecords(allAttendanceRecords));
    }
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            if (filterDivision) filterDivision.value = '';
            if (filterSubject) filterSubject.value = '';
            if (filterDate) filterDate.value = '';
            displayAttendanceRecords(allAttendanceRecords);
        });
    }

    // Start Session Form
    const startSessionForm = document.getElementById('startSessionForm');
    const startSessionBtn = document.getElementById('startSessionBtn');

    startSessionForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const branch = document.getElementById('branch').value.trim();
        const division = document.getElementById('division').value.trim();
        const subject = document.getElementById('subject').value.trim();

        if (!branch || !division || !subject) {
            showAlert('Please enter branch, division, and subject', 'error');
            return;
        }

        // Show loading
        startSessionBtn.disabled = true;
        startSessionBtn.innerHTML = `
            <div class="spinner"></div>
            Starting Session...
        `;

        try {
            console.log('🔐 Sending request with token:', token ? 'Token exists' : 'NO TOKEN!');
            console.log('📤 Request data:', { branch, division, subject });
            
            // Prepare request data with token fallback for InfinityFree
            const requestData = { 
                branch, 
                division,
                subject,
                _token: token  // Fallback: Send token in body if header doesn't work
            };
            
            const result = await apiCall('generate_id.php', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(requestData)
            });

            console.log('📥 Response:', result);

            if (result.data.status === 'success') {
                showAlert('Session started successfully!', 'success');

                // Show active session card
                displayActiveSession(result.data);

                // Reload attendance records to show updated data
                await loadAttendanceRecords(token);

                // Reset form
                startSessionForm.reset();
                startSessionBtn.disabled = false;
                startSessionBtn.innerHTML = `
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4"/>
                    </svg>
                    Start Session
                `;
            } else {
                throw new Error(result.data.message || 'Failed to start session');
            }
        } catch (error) {
            console.error('Session error:', error);
            showAlert(error.message || 'Failed to start session', 'error');

            startSessionBtn.disabled = false;
            startSessionBtn.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4"/>
                </svg>
                Start Session
            `;
        }
    });

    // Copy Class ID
    const copyIdBtn = document.getElementById('copyIdBtn');
    if (copyIdBtn) {
        copyIdBtn.addEventListener('click', async () => {
            const classId = document.getElementById('classIdDisplay').textContent.trim();
            
            try {
                await navigator.clipboard.writeText(classId);
                
                copyIdBtn.innerHTML = `
                    <svg class="icon" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Copied!
                `;
                
                setTimeout(() => {
                    copyIdBtn.innerHTML = `
                        <svg class="icon" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                            <path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Copy ID
                    `;
                }, 2000);
            } catch (error) {
                showAlert('Failed to copy. Please copy manually.', 'error');
            }
        });
    }
});

// Load Faculty Profile
async function loadFacultyProfile(token) {
    try {
        const result = await apiCall('display_profile.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (result.data.status === 'success') {
            const profile = result.data.data;
            document.getElementById('facultyName').textContent = profile.full_name || profile.username;
        }
    } catch (error) {
        console.error('Profile load error:', error);
    }
}

// Display Active Session
function displayActiveSession(sessionData) {
    const activeSessionCard = document.getElementById('activeSessionCard');
    
    document.getElementById('classIdDisplay').textContent = sessionData.class_id;
    document.getElementById('sessionBranch').textContent = sessionData.branch || '-';
    document.getElementById('sessionDivision').textContent = sessionData.division || '-';
    document.getElementById('sessionExpiry').textContent = formatTime(sessionData.expires_at);
    document.getElementById('studentCount').textContent = '0'; // Will update with real-time data
    
    activeSessionCard.style.display = 'block';

    // Scroll to active session
    activeSessionCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Load Attendance Records
async function loadAttendanceRecords(token) {
    const container = document.getElementById('attendanceRecords');
    
    try {
        const result = await apiCall('get_faculty_attendance.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        console.log('📊 Attendance Records Response:', result);

        // Handle both direct response and wrapped response
        const responseData = result.data || result;
        
        if (responseData.success && responseData.records && responseData.records.length > 0) {
            console.log('✅ Displaying', responseData.records.length, 'attendance records');
            
            // Store records globally for filtering
            allAttendanceRecords = responseData.records;
            
            // Populate filter dropdowns from both attendance and schedule
            populateFilters();
            
            // Display records
            displayAttendanceRecords(allAttendanceRecords);
        } else {
            // No records but still populate filters from schedule
            allAttendanceRecords = [];
            populateFilters();
            
            // No records
            container.innerHTML = `
                <div style="text-align: center; padding: var(--space-8); color: var(--text-secondary);">
                    <svg class="icon icon-lg" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto var(--space-4);">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No attendance records yet</p>
                    <p style="font-size: var(--font-size-sm); margin-top: var(--space-2);">
                        Records will appear here when students mark their attendance
                    </p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Attendance records load error:', error);
        container.innerHTML = `
            <div style="text-align: center; padding: var(--space-8); color: var(--error);">
                <p>Failed to load attendance records</p>
                <p style="font-size: var(--font-size-sm); margin-top: var(--space-2);">
                    ${error.message || 'Please try again later'}
                </p>
            </div>
        `;
    }
}

// Populate filter dropdowns
function populateFilters() {
    const divisions = new Set();
    const subjects = new Set();
    
    console.log('📋 Populating filters from:', {
        attendanceRecords: allAttendanceRecords.length,
        hasSchedule: !!teachingScheduleData.schedule
    });
    
    // Get divisions and subjects from attendance records
    allAttendanceRecords.forEach(record => {
        record.divisions.forEach(div => {
            divisions.add(div.division);
            if (div.subject) {
                subjects.add(div.subject);
                console.log('  ✓ Added subject from attendance:', div.subject);
            }
        });
    });
    
    // Also get divisions and subjects from teaching schedule
    if (teachingScheduleData && teachingScheduleData.schedule) {
        Object.values(teachingScheduleData.schedule).forEach(daySchedule => {
            daySchedule.forEach(classItem => {
                if (classItem.division) divisions.add(classItem.division);
                if (classItem.subject) {
                    subjects.add(classItem.subject);
                    console.log('  ✓ Added subject from schedule:', classItem.subject);
                }
            });
        });
    }
    
    console.log('📊 Filter options:', {
        divisions: Array.from(divisions),
        subjects: Array.from(subjects)
    });
    
    // Populate division filter
    const divisionSelect = document.getElementById('filterDivision');
    if (divisionSelect) {
        divisionSelect.innerHTML = '<option value="">All Divisions</option>';
        Array.from(divisions).sort().forEach(div => {
            divisionSelect.innerHTML += `<option value="${div}">Division ${div}</option>`;
        });
    }
    
    // Populate subject filter
    const subjectSelect = document.getElementById('filterSubject');
    if (subjectSelect) {
        subjectSelect.innerHTML = '<option value="">All Subjects</option>';
        Array.from(subjects).sort().forEach(subject => {
            subjectSelect.innerHTML += `<option value="${subject}">${subject}</option>`;
        });
    }
}

// Display attendance records with optional filtering
function displayAttendanceRecords(records) {
    const container = document.getElementById('attendanceRecords');
    const filterDivision = document.getElementById('filterDivision')?.value || '';
    const filterSubject = document.getElementById('filterSubject')?.value || '';
    const filterDate = document.getElementById('filterDate')?.value || '';
    
    console.log('🎯 Displaying attendance records:', {
        totalRecords: records.length,
        filters: { division: filterDivision, subject: filterSubject, date: filterDate },
        records: records
    });
    
    let filteredRecords = records;
    
    // Apply date filter
    if (filterDate) {
        filteredRecords = filteredRecords.filter(record => record.date === filterDate);
    }
    
    let html = '';
    let hasVisibleRecords = false;
    let totalStudents = 0;
    let totalDivisions = new Set();
    let totalSubjects = new Set();
    
    filteredRecords.forEach(record => {
        // Date header
        let dateHtml = `
            <div style="margin-bottom: var(--space-6); border-left: 4px solid var(--primary); padding-left: var(--space-4); padding-top: var(--space-2);">
                <div style="margin-bottom: var(--space-3);">
                    <h4 style="font-weight: 700; font-size: var(--font-size-lg); margin-bottom: var(--space-1); color: var(--primary);">
                        ${record.day_of_week}
                    </h4>
                    <p style="color: var(--text-secondary); font-size: var(--font-size-sm);">
                        ${formatDate(record.date)}
                    </p>
                </div>
        `;
        
        let divisionsHtml = '';
        
        // Filter divisions
        const visibleDivisions = record.divisions.filter(div => {
            const divisionMatch = !filterDivision || div.division === filterDivision;
            const subjectMatch = !filterSubject || div.subject === filterSubject;
            
            if (filterDivision || filterSubject) {
                console.log('  🔍 Filter check:', {
                    division: div.division,
                    subject: div.subject,
                    divisionMatch,
                    subjectMatch,
                    filters: { filterDivision, filterSubject }
                });
            }
            
            return divisionMatch && subjectMatch;
        });
        
        if (visibleDivisions.length > 0) {
            hasVisibleRecords = true;
            
            visibleDivisions.forEach(div => {
                // Track statistics
                totalStudents += div.students.length;
                totalDivisions.add(div.division);
                if (div.subject) totalSubjects.add(div.subject);
                
                divisionsHtml += `
                    <div style="background: var(--bg-secondary); border-radius: var(--radius-md); padding: var(--space-4); margin-bottom: var(--space-3); border-left: 4px solid var(--primary);">
                        <div style="margin-bottom: var(--space-3);">
                            <div style="font-weight: 600; font-size: var(--font-size-md); margin-bottom: var(--space-2); color: var(--text-primary);">
                                ${div.subject || 'No Subject'}
                            </div>
                            <div style="display: flex; gap: var(--space-2); flex-wrap: wrap;">
                                <span class="badge badge-primary">Division ${div.division}</span>
                                <span class="badge" style="background: var(--success-light); color: var(--success);">
                                    ${div.students.length} ${div.students.length === 1 ? 'Student' : 'Students'}
                                </span>
                                ${div.branch ? `<span class="badge" style="background: var(--gray-200); color: var(--gray-700);">${div.branch}</span>` : ''}
                            </div>
                        </div>
                        
                        <div style="display: grid; gap: var(--space-2);">
                `;
                
                // Student list
                div.students.forEach(student => {
                    divisionsHtml += `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-2); background: white; border-radius: var(--radius-sm);">
                            <div style="display: flex; align-items: center; gap: var(--space-3);">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                                    ${student.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div style="font-weight: 500;">${student.name}</div>
                                    <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">
                                        Sem ${student.semester} • ${student.username}
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right; font-size: var(--font-size-sm); color: var(--text-secondary);">
                                ${formatTime(student.marked_time)}
                            </div>
                        </div>
                    `;
                });
                
                divisionsHtml += `
                        </div>
                    </div>
                `;
            });
            
            html += dateHtml + divisionsHtml + `</div>`;
        }
    });
    
    if (!hasVisibleRecords) {
        container.innerHTML = `
            <div style="text-align: center; padding: var(--space-8); color: var(--text-secondary);">
                <svg class="icon icon-lg" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto var(--space-4);">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p style="font-weight: 600; font-size: var(--font-size-lg);">No records match the selected filters</p>
                <p style="font-size: var(--font-size-sm); margin-top: var(--space-2);">
                    Try adjusting your filter criteria or clearing all filters
                </p>
                ${(filterDivision || filterSubject || filterDate) ? `
                <div style="margin-top: var(--space-4);">
                    <button onclick="document.getElementById('clearFilters').click()" class="btn btn-primary">
                        Clear All Filters
                    </button>
                </div>
                ` : ''}
            </div>
        `;
    } else {
        // Add active filters indicator
        let activeFiltersHtml = '';
        const activeFilters = [];
        if (filterDivision) activeFilters.push(`Division: ${filterDivision}`);
        if (filterSubject) activeFilters.push(`Subject: ${filterSubject}`);
        if (filterDate) activeFilters.push(`Date: ${formatDate(filterDate)}`);
        
        if (activeFilters.length > 0) {
            activeFiltersHtml = `
                <div style="background: var(--info-light); border-left: 4px solid var(--info); padding: var(--space-3); border-radius: var(--radius-md); margin-bottom: var(--space-4);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="font-weight: 600; margin-right: var(--space-2);">🔍 Active Filters:</span>
                            ${activeFilters.map(f => `<span class="badge" style="background: var(--info); color: white; margin-right: var(--space-2);">${f}</span>`).join('')}
                        </div>
                        <button onclick="document.getElementById('clearFilters').click()" class="btn btn-sm" style="padding: var(--space-1) var(--space-3); font-size: var(--font-size-sm);">
                            Clear Filters
                        </button>
                    </div>
                </div>
            `;
        }
        
        // Add summary banner
        const summaryHtml = `
            <div style="background: linear-gradient(135deg, var(--primary-light), var(--primary)); color: white; padding: var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-6);">
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-4); text-align: center;">
                    <div>
                        <div style="font-size: var(--font-size-2xl); font-weight: 700;">${totalStudents}</div>
                        <div style="font-size: var(--font-size-sm); opacity: 0.9;">Total Students</div>
                    </div>
                    <div>
                        <div style="font-size: var(--font-size-2xl); font-weight: 700;">${totalDivisions.size}</div>
                        <div style="font-size: var(--font-size-sm); opacity: 0.9;">Divisions</div>
                    </div>
                    <div>
                        <div style="font-size: var(--font-size-2xl); font-weight: 700;">${totalSubjects.size}</div>
                        <div style="font-size: var(--font-size-sm); opacity: 0.9;">Subjects</div>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = activeFiltersHtml + summaryHtml + html;
    }
}

// Old rendering function - now replaced by displayAttendanceRecords
function renderAttendanceRecords_OLD(records) {
    const container = document.getElementById('attendanceRecords');
    let html = '';
}

// Load Teaching Schedule
async function loadTeachingSchedule(token) {
    const container = document.getElementById('teachingSchedule');
    
    try {
        const result = await apiCall('get_faculty_schedule.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (result.data.success) {
            const schedule = result.data.schedule;
            
            // Store schedule globally for filter population
            teachingScheduleData = result.data;
            
            // Update filters with schedule data
            populateFilters();
            
            // Check if schedule is empty
            const hasSchedule = Object.values(schedule).some(day => day.length > 0);
            
            if (!hasSchedule) {
                container.innerHTML = `
                    <div style="text-align: center; padding: var(--space-8); color: var(--text-secondary);">
                        <svg class="icon icon-lg" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto var(--space-4);">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p>No teaching schedule assigned</p>
                        <p style="font-size: var(--font-size-sm); margin-top: var(--space-2);">
                            Contact your admin to add your teaching schedule
                        </p>
                    </div>
                `;
                return;
            }

            // Build schedule HTML
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            
            container.innerHTML = days.map(day => {
                const classes = schedule[day] || [];
                
                if (classes.length === 0) {
                    return ''; // Skip days with no classes
                }
                
                return `
                    <div style="margin-bottom: var(--space-4); padding-bottom: var(--space-4); border-bottom: 1px solid var(--border);">
                        <div style="font-weight: 600; margin-bottom: var(--space-3); color: var(--primary); font-size: var(--font-size-md);">
                            ${day}
                        </div>
                        <div style="display: grid; gap: var(--space-3);">
                            ${classes.map(cls => `
                                <div style="background: var(--bg-secondary); padding: var(--space-3); border-radius: var(--radius); border-left: 3px solid var(--primary);">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 500; margin-bottom: 4px;">${cls.subject}</div>
                                            <div style="font-size: var(--font-size-sm); color: var(--text-secondary);">
                                                <span class="badge badge-info" style="display: inline-block; margin-right: 8px;">Sem ${cls.semester}</span>
                                                Division ${cls.division}
                                                ${cls.time_slot ? ` • ${cls.time_slot}` : ''}
                                            </div>
                                        </div>
                                        <span class="badge badge-primary" style="font-size: var(--font-size-xs);">
                                            ${cls.division}
                                        </span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            throw new Error('Failed to load schedule');
        }
    } catch (error) {
        console.error('Schedule load error:', error);
        container.innerHTML = `
            <div style="text-align: center; padding: var(--space-4); color: var(--text-secondary);">
                Unable to load teaching schedule
            </div>
        `;
    }
}

// Helper: Format date to readable format
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { month: 'short', day: 'numeric', year: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Helper: Format time from datetime string
function formatTime(datetimeString) {
    const date = new Date(datetimeString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}
