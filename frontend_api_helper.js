/**
 * API Response Helper
 * Cleans InfinityFree JavaScript injection from API responses
 */

// For JavaScript/React/React Native/Next.js Frontend
export const cleanAPIResponse = (responseText) => {
  try {
    // Remove all <script> tags and their content
    let cleaned = responseText.replace(/<script[\s\S]*?<\/script>/gi, '');
    
    // Remove all <noscript> tags and their content
    cleaned = cleaned.replace(/<noscript[\s\S]*?<\/noscript>/gi, '');
    
    // Remove HTML tags
    cleaned = cleaned.replace(/<\/?[^>]+(>|$)/g, '');
    
    // Extract JSON from the cleaned string
    // Find first { and last }
    const firstBrace = cleaned.indexOf('{');
    const lastBrace = cleaned.lastIndexOf('}');
    
    if (firstBrace !== -1 && lastBrace !== -1) {
      cleaned = cleaned.substring(firstBrace, lastBrace + 1);
    }
    
    // Parse and return JSON
    return JSON.parse(cleaned);
  } catch (error) {
    console.error('Failed to clean API response:', error);
    throw new Error('Invalid API response format');
  }
};

// Usage with fetch:
export const fetchAPI = async (url, options = {}) => {
  const response = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options.headers,
    },
  });
  
  const text = await response.text();
  const data = cleanAPIResponse(text);
  
  return {
    ok: response.ok,
    status: response.status,
    data: data,
  };
};

// Example Usage:
/*
// Login Example
const loginUser = async (username, password) => {
  try {
    const result = await fetchAPI('https://hcthegreat.ct.ws/api/faculty_login.php', {
      method: 'POST',
      body: JSON.stringify({ username, password }),
    });
    
    if (result.data.status === 'success') {
      console.log('Login successful:', result.data);
      return result.data;
    } else {
      console.error('Login failed:', result.data.message);
    }
  } catch (error) {
    console.error('API Error:', error);
  }
};

// Register Example
const registerStudent = async (userData) => {
  const result = await fetchAPI('https://hcthegreat.ct.ws/api/register_user.php', {
    method: 'POST',
    body: JSON.stringify(userData),
  });
  return result.data;
};

// Authenticated Request Example
const getProfile = async (token) => {
  const result = await fetchAPI('https://hcthegreat.ct.ws/api/display_profile.php', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  return result.data;
};
*/
