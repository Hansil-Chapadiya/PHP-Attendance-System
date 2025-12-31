// Main App Configuration
const API_BASE_URL = 'https://hcthegreat.ct.ws/api';

// Storage Keys
const STORAGE_KEYS = {
    TOKEN: 'auth_token',
    USER: 'user_data',
    ROLE: 'user_role'
};

// Clean InfinityFree response
function cleanAPIResponse(responseText) {
    try {
        let cleaned = responseText.replace(/<script[\s\S]*?<\/script>/gi, '');
        cleaned = cleaned.replace(/<noscript[\s\S]*?<\/noscript>/gi, '');
        cleaned = cleaned.replace(/<\/?[^>]+(>|$)/g, '');
        
        const firstBrace = cleaned.indexOf('{');
        const lastBrace = cleaned.lastIndexOf('}');
        
        if (firstBrace !== -1 && lastBrace !== -1) {
            cleaned = cleaned.substring(firstBrace, lastBrace + 1);
        }
        
        return JSON.parse(cleaned);
    } catch (error) {
        console.error('Failed to clean API response:', error);
        throw new Error('Invalid API response format');
    }
}

// API Call Function with InfinityFree handling
async function apiCall(endpoint, options = {}, retryCount = 0) {
    const url = `${API_BASE_URL}/${endpoint}`;
    
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });
        
        const text = await response.text();
        
        // Check for InfinityFree redirect
        if (text.includes('location.href') && text.includes('?i=1') && retryCount < 2) {
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
        if (error.message.includes('Invalid API response') && retryCount < 2 && !url.includes('?i=1')) {
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
