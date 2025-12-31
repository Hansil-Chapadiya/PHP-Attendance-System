// Mark Attendance Logic
document.addEventListener('DOMContentLoaded', async () => {
    const auth = checkAuth();
    if (!auth) return;

    const { token } = auth;

    // Step elements
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const wifiStatus = document.getElementById('wifiStatus');
    const classIdInput = document.getElementById('classId');
    const submitBtn = document.getElementById('submitBtn');

    // Step 1: Check Wi-Fi
    setTimeout(async () => {
        const isConnected = navigator.onLine;
        
        if (isConnected) {
            wifiStatus.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24" style="color: var(--success);">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="color: var(--success); font-weight: 500;">Connected to Network</span>
            `;

            // Update step 1 icon
            const step1Icon = step1.querySelector('div[style*="background"]');
            step1Icon.style.background = 'var(--success)';

            // Enable step 2
            setTimeout(() => {
                step2.style.opacity = '1';
                const step2Icon = step2.querySelector('div[style*="background"]');
                step2Icon.style.background = 'var(--primary)';
                classIdInput.disabled = false;
                classIdInput.focus();
            }, 500);
        } else {
            wifiStatus.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24" style="color: var(--error);">
                    <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="color: var(--error); font-weight: 500;">Not connected to network</span>
            `;

            showAlert('Please connect to university Wi-Fi to mark attendance', 'error');
        }
    }, 1500);

    // Step 2: Class ID input validation
    classIdInput.addEventListener('input', (e) => {
        const value = e.target.value.trim();
        
        if (value.length > 0) {
            // Enable step 3
            step3.style.opacity = '1';
            const step3Icon = step3.querySelector('div[style*="background"]');
            step3Icon.style.background = 'var(--primary)';
            submitBtn.disabled = false;

            // Update step 2 icon
            const step2Icon = step2.querySelector('div[style*="background"]');
            step2Icon.style.background = 'var(--success)';
        } else {
            step3.style.opacity = '0.5';
            const step3Icon = step3.querySelector('div[style*="background"]');
            step3Icon.style.background = 'var(--bg-secondary)';
            submitBtn.disabled = true;
        }
    });

    // Step 3: Submit attendance
    submitBtn.addEventListener('click', async () => {
        const classId = classIdInput.value.trim();

        if (!classId) {
            showAlert('Please enter a valid Class ID', 'error');
            return;
        }

        // Show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <div class="spinner"></div>
            Submitting...
        `;

        try {
            const result = await apiCall('mark_present.php', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ class_id: classId })
            });

            if (result.data.status === 'success') {
                // Success!
                showAlert(result.data.message || 'Attendance marked successfully!', 'success');

                // Update all steps to success
                document.querySelectorAll('[style*="background"]').forEach(icon => {
                    icon.style.background = 'var(--success)';
                });

                // Update submit button
                submitBtn.innerHTML = `
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Attendance Marked!
                `;
                submitBtn.style.background = 'var(--success)';

                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = 'student-dashboard.html';
                }, 2000);
            } else {
                // Error
                throw new Error(result.data.message || 'Failed to mark attendance');
            }
        } catch (error) {
            console.error('Attendance error:', error);
            showAlert(error.message || 'Failed to mark attendance. Please try again.', 'error');
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Submit Attendance
            `;
        }
    });
});
