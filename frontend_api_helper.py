"""
API Response Helper for Python/Flutter
Cleans InfinityFree JavaScript injection from API responses
"""

import json
import re
import requests

def clean_api_response(response_text):
    """
    Clean InfinityFree JavaScript injection from API response
    
    Args:
        response_text (str): Raw response text from API
        
    Returns:
        dict: Parsed JSON data
    """
    try:
        # Remove all <script> tags and content
        cleaned = re.sub(r'<script[\s\S]*?</script>', '', response_text, flags=re.IGNORECASE)
        
        # Remove all <noscript> tags and content
        cleaned = re.sub(r'<noscript[\s\S]*?</noscript>', '', cleaned, flags=re.IGNORECASE)
        
        # Remove HTML tags
        cleaned = re.sub(r'</?[^>]+(>|$)', '', cleaned)
        
        # Extract JSON (find first { and last })
        first_brace = cleaned.find('{')
        last_brace = cleaned.rfind('}')
        
        if first_brace != -1 and last_brace != -1:
            cleaned = cleaned[first_brace:last_brace + 1]
        
        # Parse and return JSON
        return json.loads(cleaned)
    except Exception as e:
        print(f"Failed to clean API response: {e}")
        raise ValueError("Invalid API response format")

def fetch_api(url, method='GET', data=None, headers=None, token=None):
    """
    Make API request and clean response
    
    Args:
        url (str): API endpoint URL
        method (str): HTTP method (GET, POST, etc.)
        data (dict): Request body data
        headers (dict): Additional headers
        token (str): Authorization token
        
    Returns:
        dict: Cleaned JSON response
    """
    if headers is None:
        headers = {}
    
    headers['Content-Type'] = 'application/json'
    
    if token:
        headers['Authorization'] = f'Bearer {token}'
    
    if method == 'POST':
        response = requests.post(url, json=data, headers=headers)
    else:
        response = requests.get(url, headers=headers)
    
    cleaned_data = clean_api_response(response.text)
    
    return {
        'ok': response.ok,
        'status': response.status_code,
        'data': cleaned_data
    }

# Example Usage:
"""
# Login Example
def login_user(username, password):
    result = fetch_api(
        'https://hcthegreat.ct.ws/api/faculty_login.php',
        method='POST',
        data={'username': username, 'password': password}
    )
    
    if result['data']['status'] == 'success':
        print('Login successful:', result['data'])
        return result['data']
    else:
        print('Login failed:', result['data']['message'])
        return None

# Register Example
def register_student(user_data):
    result = fetch_api(
        'https://hcthegreat.ct.ws/api/register_user.php',
        method='POST',
        data=user_data
    )
    return result['data']

# Get Profile with Token
def get_profile(token):
    result = fetch_api(
        'https://hcthegreat.ct.ws/api/display_profile.php',
        method='GET',
        token=token
    )
    return result['data']
"""
