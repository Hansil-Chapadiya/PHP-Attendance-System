// Main App Configuration
// Auto-detect environment: use production URL if on production domain, otherwise use local
const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

// For local development, use the current port if running from file system,
// otherwise detect from window.location
let localPort = window.location.port || '80';
const API_BASE_URL = isLocalhost 
    ? `http://localhost:${localPort}/Hansil/PHP-Attendance-System/api`
    : 'https://hcthegreat.ct.ws/deploy/api';  // ✅ Fixed: Added /deploy

console.log('API Base URL:', API_BASE_URL);


// Storage Keys
const STORAGE_KEYS = {
    TOKEN: 'auth_token',
    USER: 'user_data',
    ROLE: 'user_role'
};

// Clean InfinityFree response
function cleanAPIResponse(responseText) {
    try {
        // Log FULL response for debugging
        console.log('📥 Raw API response (full):', responseText);
        
        // Check if it's a 404 or error page
        if (responseText.includes('404') || responseText.includes('Not Found') || responseText.includes('errors.infinityfree.net')) {
            console.error('❌ 404 Error - API endpoint not found!');
            throw new Error('API endpoint not found (404). Check if files are uploaded correctly.');
        }
        
        // Remove BOM if present
        let cleaned = responseText.replace(/^\uFEFF/, '');
        
        // Trim whitespace
        cleaned = cleaned.trim();
        
        // Remove HTML tags from InfinityFree
        cleaned = cleaned.replace(/<script[\s\S]*?<\/script>/gi, '');
        cleaned = cleaned.replace(/<noscript[\s\S]*?<\/noscript>/gi, '');
        cleaned = cleaned.replace(/<\/?[^>]+(>|$)/g, '');
        
        // Extract JSON from response
        const firstBrace = cleaned.indexOf('{');
        const lastBrace = cleaned.lastIndexOf('}');
        
        if (firstBrace !== -1 && lastBrace !== -1) {
            cleaned = cleaned.substring(firstBrace, lastBrace + 1);
        } else {
            console.error('❌ No JSON found in response!');
            console.error('Cleaned text:', cleaned);
            throw new Error('No JSON data found in response. Got: ' + cleaned.substring(0, 100));
        }
        
        // Trim again after extraction
        cleaned = cleaned.trim();
        
        console.log('✅ Cleaned JSON:', cleaned.substring(0, 200));
        
        return JSON.parse(cleaned);
    } catch (error) {
        console.error('❌ Failed to clean API response:', error);
        console.error('📄 Response text (first 1000 chars):', responseText.substring(0, 1000));
        throw new Error('Invalid API response: ' + error.message);
    }
}

// API Call Function with InfinityFree handling
async function apiCall(endpoint, options = {}, retryCount = 0) {
    const url = `${API_BASE_URL}/${endpoint}`;
    
    console.log(`🌐 API Call: ${url} (Attempt ${retryCount + 1})`);
    
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });
        
        console.log(`📡 Response status: ${response.status} ${response.statusText}`);
        
        const text = await response.text();
        
        // Check for 404 error
        if (response.status === 404) {
            console.error('❌ 404 Not Found:', url);
            throw new Error(`API endpoint not found: ${endpoint}. Check if files are uploaded to InfinityFree.`);
        }
        
        // Check for InfinityFree redirect
        if (text.includes('location.href') && text.includes('?i=1') && retryCount < 2) {
            console.log('🔄 InfinityFree redirect detected, retrying...');
            await new Promise(resolve => setTimeout(resolve, 1000));
            const newUrl = url.includes('?') ? `${url}&i=1` : `${url}?i=1`;
            return apiCall(endpoint + (url.includes('?') ? '&i=1' : '?i=1'), options, retryCount + 1);
        }
        
        const data = cleanAPIResponse(text);
        
        return {
            ok: response.ok,
            status: response.status,
            data: data,
        };
    } catch (error) {
        console.error('❌ API Call failed:', error.message);
        
        if (error.message.includes('Invalid API response') && retryCount < 2 && !url.includes('?i=1')) {
            console.log('🔄 Retrying with ?i=1 parameter...');
            await new Promise(resolve => setTimeout(resolve, 1000));
            return apiCall(endpoint + '?i=1', options, retryCount + 1);
        }
        throw error;
    }
}

// Storage Functions
function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.error('Storage error:', e);
    }
}

function getFromStorage(key) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    } catch (e) {
        console.error('Storage error:', e);
        return null;
    }
}

function removeFromStorage(key) {
    try {
        localStorage.removeItem(key);
    } catch (e) {
        console.error('Storage error:', e);
    }
}

// Show Alert
function showAlert(message, type = 'error') {
    const alertEl = document.getElementById('alertMessage');
    if (!alertEl) return;
    
    alertEl.className = `alert alert-${type}`;
    alertEl.textContent = message;
    alertEl.style.display = 'block';
    
    // Auto-hide success messages
    if (type === 'success') {
        setTimeout(() => {
            alertEl.style.display = 'none';
        }, 5000);
    }
}

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Format Time
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

// Check if user is logged in
function checkAuth() {
    const token = getFromStorage(STORAGE_KEYS.TOKEN);
    const role = getFromStorage(STORAGE_KEYS.ROLE);
    
    if (!token) {
        window.location.href = 'login.html';
        return false;
    }
    
    return { token, role };
}

// Logout
function logout() {
    removeFromStorage(STORAGE_KEYS.TOKEN);
    removeFromStorage(STORAGE_KEYS.USER);
    removeFromStorage(STORAGE_KEYS.ROLE);
    window.location.href = 'login.html';
}

// Add logout button listener if exists
document.addEventListener('DOMContentLoaded', () => {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
});
