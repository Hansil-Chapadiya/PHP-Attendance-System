# Frontend Integration Guide

## Problem
InfinityFree injects JavaScript tracking code into API responses, wrapping your clean JSON with HTML/JavaScript.

## Solution
Clean the response in your frontend before parsing JSON.

---

## JavaScript/React/React Native/Next.js

Use the `frontend_api_helper.js` file:

```javascript
import { fetchAPI, cleanAPIResponse } from './frontend_api_helper';

// Example 1: Login
const handleLogin = async () => {
  try {
    const result = await fetchAPI('https://hcthegreat.ct.ws/api/faculty_login.php', {
      method: 'POST',
      body: JSON.stringify({
        username: 'Marina',
        password: 'SecurePassword123'
      })
    });
    
    if (result.data.status === 'success') {
      const token = result.data.token;
      localStorage.setItem('token', token);
      console.log('Login successful!');
    }
  } catch (error) {
    console.error('Login error:', error);
  }
};

// Example 2: Register Student
const handleRegister = async () => {
  const userData = {
    username: 'john_doe',
    password: 'Password123',
    full_name: 'John Doe',
    role: 'student',
    branch: 'Computer Science',
    division: 'A',
    semester: 5
  };
  
  const result = await fetchAPI('https://hcthegreat.ct.ws/api/register_user.php', {
    method: 'POST',
    body: JSON.stringify(userData)
  });
  
  console.log(result.data);
};

// Example 3: Get Profile (with token)
const getProfile = async () => {
  const token = localStorage.getItem('token');
  
  const result = await fetchAPI('https://hcthegreat.ct.ws/api/display_profile.php', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  console.log('Profile:', result.data);
};
```

---

## Python/Flutter (using requests)

Use the `frontend_api_helper.py` file:

```python
from frontend_api_helper import fetch_api

# Example 1: Login
def login():
    result = fetch_api(
        'https://hcthegreat.ct.ws/api/faculty_login.php',
        method='POST',
        data={
            'username': 'Marina',
            'password': 'SecurePassword123'
        }
    )
    
    if result['data']['status'] == 'success':
        token = result['data']['token']
        print('Login successful!')
        return token
    return None

# Example 2: Register
def register_student():
    user_data = {
        'username': 'john_doe',
        'password': 'Password123',
        'full_name': 'John Doe',
        'role': 'student',
        'branch': 'Computer Science',
        'division': 'A',
        'semester': 5
    }
    
    result = fetch_api(
        'https://hcthegreat.ct.ws/api/register_user.php',
        method='POST',
        data=user_data
    )
    
    print(result['data'])

# Example 3: Get Profile
def get_profile(token):
    result = fetch_api(
        'https://hcthegreat.ct.ws/api/display_profile.php',
        method='GET',
        token=token
    )
    
    print('Profile:', result['data'])
```

---

## Android (Kotlin/Java)

```kotlin
import org.json.JSONObject
import okhttp3.*

fun cleanAPIResponse(responseText: String): JSONObject {
    var cleaned = responseText
    
    // Remove script tags
    cleaned = cleaned.replace(Regex("<script[\\s\\S]*?</script>", RegexOption.IGNORE_CASE), "")
    
    // Remove noscript tags
    cleaned = cleaned.replace(Regex("<noscript[\\s\\S]*?</noscript>", RegexOption.IGNORE_CASE), "")
    
    // Remove HTML tags
    cleaned = cleaned.replace(Regex("</?[^>]+(>|$)"), "")
    
    // Extract JSON
    val firstBrace = cleaned.indexOf('{')
    val lastBrace = cleaned.lastIndexOf('}')
    
    if (firstBrace != -1 && lastBrace != -1) {
        cleaned = cleaned.substring(firstBrace, lastBrace + 1)
    }
    
    return JSONObject(cleaned)
}

// Usage
val client = OkHttpClient()
val url = "https://hcthegreat.ct.ws/api/faculty_login.php"

val json = JSONObject()
json.put("username", "Marina")
json.put("password", "SecurePassword123")

val body = RequestBody.create(
    MediaType.parse("application/json"),
    json.toString()
)

val request = Request.Builder()
    .url(url)
    .post(body)
    .build()

client.newCall(request).execute().use { response ->
    val responseText = response.body()?.string()
    val cleanedData = cleanAPIResponse(responseText ?: "")
    println(cleanedData.toString())
}
```

---

## iOS (Swift)

```swift
import Foundation

func cleanAPIResponse(_ responseText: String) -> [String: Any]? {
    var cleaned = responseText
    
    // Remove script tags
    cleaned = cleaned.replacingOccurrences(
        of: "<script[\\s\\S]*?</script>",
        with: "",
        options: .regularExpression
    )
    
    // Remove noscript tags
    cleaned = cleaned.replacingOccurrences(
        of: "<noscript[\\s\\S]*?</noscript>",
        with: "",
        options: .regularExpression
    )
    
    // Remove HTML tags
    cleaned = cleaned.replacingOccurrences(
        of: "</?[^>]+(>|$)",
        with: "",
        options: .regularExpression
    )
    
    // Extract JSON
    if let firstBrace = cleaned.firstIndex(of: "{"),
       let lastBrace = cleaned.lastIndex(of: "}") {
        cleaned = String(cleaned[firstBrace...lastBrace])
    }
    
    // Parse JSON
    guard let data = cleaned.data(using: .utf8),
          let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any] else {
        return nil
    }
    
    return json
}

// Usage
let url = URL(string: "https://hcthegreat.ct.ws/api/faculty_login.php")!
var request = URLRequest(url: url)
request.httpMethod = "POST"
request.setValue("application/json", forHTTPHeaderField: "Content-Type")

let body = ["username": "Marina", "password": "SecurePassword123"]
request.httpBody = try? JSONSerialization.data(withJSONObject: body)

let task = URLSession.shared.dataTask(with: request) { data, response, error in
    guard let data = data,
          let responseText = String(data: data, encoding: .utf8),
          let cleanedJSON = cleanAPIResponse(responseText) else {
        return
    }
    
    print(cleanedJSON)
}

task.resume()
```

---

## Complete API Endpoint List

**Base URL:** `https://hcthegreat.ct.ws/api/`

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/register_user.php` | POST | No | Register student/faculty |
| `/stud_login.php` | POST | No | Student login |
| `/faculty_login.php` | POST | No | Faculty login |
| `/generate_id.php` | POST | Yes (Faculty) | Generate class ID |
| `/mark_present.php` | POST | Yes (Student) | Mark attendance |
| `/display_profile.php` | GET | Yes | View profile |
| `/show_attendance.php` | GET | Yes | View attendance history |

---

## Summary

✅ **Database works fine** on InfinityFree  
✅ **API works fine** on InfinityFree  
✅ **Frontend cleans the response** before parsing  
✅ **No need to change hosting!**

Just use the helper functions in your frontend and everything will work perfectly!
