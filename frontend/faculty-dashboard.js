// Faculty Dashboard Logic
document.addEventListener('DOMContentLoaded', async () => {
    const auth = checkAuth();
    if (!auth) return;

    const { token } = auth;

    // Load Profile
    await loadFacultyProfile(token);

    // Load Recent Sessions
    await loadRecentSessions(token);

    // Start Session Form
    const startSessionForm = document.getElementById('startSessionForm');
    const startSessionBtn = document.getElementById('startSessionBtn');

    startSessionForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const branch = document.getElementById('branch').value.trim();
        const division = document.getElementById('division').value.trim();

        if (!branch || !division) {
            showAlert('Please enter both branch and division', 'error');
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
            console.log('📤 Request data:', { branch, division });
            
            const result = await apiCall('generate_id.php', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ branch, division })
            });

            console.log('📥 Response:', result);

            if (result.data.status === 'success') {
                showAlert('Session started successfully!', 'success');

                // Show active session card
                displayActiveSession(result.data);

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

// Load Recent Sessions
async function loadRecentSessions(token) {
    const container = document.getElementById('recentSessions');
    
    // For now, show placeholder
    container.innerHTML = `
        <div style="text-align: center; padding: var(--space-8); color: var(--text-secondary);">
            <svg class="icon icon-lg" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto var(--space-4);">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>No recent sessions</p>
            <p style="font-size: var(--font-size-sm); margin-top: var(--space-2);">
                Start a new session to begin tracking attendance
            </p>
        </div>
    `;

    // TODO: Implement API endpoint for fetching faculty's session history
    // This would require a new backend endpoint
}
