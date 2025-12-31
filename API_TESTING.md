# API Testing Guide

Quick reference for testing all endpoints with curl or Postman.

## Base URL
```
http://localhost/api/
```

For Vercel deployment, use your vercel URL.

---

## 1. Register Student

**Endpoint:** `POST /student/register`

```bash
curl -X POST http://localhost/api/register_user.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_student",
    "password": "SecurePass123",
    "full_name": "John Student",
    "role": "student",
    "branch": "Computer Science",
    "division": "A",
    "semester": 5
  }'
```

**Expected Response (201):**
```json
{
  "status": "success",
  "message": "Registration successful",
  "user_id": 1,
  "username": "john_student",
  "role": "student",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

---

## 2. Register Faculty

**Endpoint:** `POST /student/register` (same endpoint, different role)

```bash
curl -X POST http://localhost/api/register_user.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "prof_smith",
    "password": "ProfPass123",
    "full_name": "Professor Smith",
    "role": "faculty",
    "branch": "Computer Science"
  }'
```

Note: Faculty doesn't need division or semester.

---

## 3. Student Login

**Endpoint:** `POST /student/login`

```bash
curl -X POST http://localhost/api/stud_login.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_student",
    "password": "SecurePass123"
  }'
```

**Response (200):**
```json
{
  "status": "success",
  "message": "Login successful",
  "user_id": 1,
  "username": "john_student",
  "full_name": "John Student",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

**Save the token for subsequent requests!**

---

## 4. Faculty Login

**Endpoint:** `POST /faculty/login`

```bash
curl -X POST http://localhost/api/faculty_login.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "prof_smith",
    "password": "ProfPass123"
  }'
```

---

## 5. Generate Class ID (Faculty Only)

**Endpoint:** `POST /faculty/generate_id`  
**Requires:** Faculty token

```bash
curl -X POST http://localhost/api/generate_id.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_FACULTY_TOKEN_HERE" \
  -d '{
    "branch": "Computer Science",
    "division": "A"
  }'
```

**Response (201):**
```json
{
  "status": "success",
  "message": "Class session created successfully",
  "class_id": "COM-A-1735567890",
  "faculty_ip": "192.168.1.100",
  "expires_at": "2025-12-30 14:30:00",
  "valid_for_minutes": 120
}
```

**Share the class_id with students!**

---

## 6. Mark Attendance (Student Only)

**Endpoint:** `POST /student/mark`  
**Requires:** Student token, same network as faculty

```bash
curl -X POST http://localhost/api/mark_present.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_STUDENT_TOKEN_HERE" \
  -d '{
    "class_id": "COM-A-1735567890"
  }'
```

**Response (201):**
```json
{
  "status": "success",
  "message": "Attendance marked successfully",
  "marked_time": "2025-12-30 12:45:30",
  "class_id": "COM-A-1735567890"
}
```

---

## 7. View Profile

**Endpoint:** `GET /student/info`  
**Requires:** Token

```bash
curl -X GET "http://localhost/api/display_profile.php" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "username": "john_student",
    "full_name": "John Student",
    "role": "student",
    "branch": "Computer Science",
    "division": "A",
    "semester": 5
  }
}
```

---

## 8. View Attendance History

**Endpoint:** `GET /student/attendance`  
**Requires:** Token

```bash
curl -X GET "http://localhost/api/show_attendance.php" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "attendance_id": 1,
      "class_id": "COM-A-1735567890",
      "date": "2025-12-30",
      "status": "present",
      "marked_time": "2025-12-30 12:45:30",
      "branch": "Computer Science",
      "division": "A"
    }
  ],
  "count": 1
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "status": "error",
  "message": "Username must be between 3 and 50 characters"
}
```

### 401 Unauthorized
```json
{
  "status": "error",
  "message": "Unauthorized. Please login."
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "You are not enrolled in this class"
}
```

### 409 Conflict
```json
{
  "status": "info",
  "message": "Attendance already marked for this class today"
}
```

### 410 Gone
```json
{
  "status": "error",
  "message": "Class session has expired"
}
```

### 429 Too Many Requests
```json
{
  "status": "error",
  "message": "Too many attempts. Please try again later."
}
```

---

## Complete Test Workflow

### Bash Script
```bash
#!/bin/bash

BASE_URL="http://localhost/api"

# 1. Register Faculty
echo "1. Registering faculty..."
FACULTY_RESPONSE=$(curl -s -X POST "$BASE_URL/register_user.php" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "prof_test",
    "password": "TestPass123",
    "full_name": "Test Professor",
    "role": "faculty",
    "branch": "CS"
  }')
echo $FACULTY_RESPONSE | jq .
FACULTY_TOKEN=$(echo $FACULTY_RESPONSE | jq -r '.token')

# 2. Register Student
echo -e "\n2. Registering student..."
STUDENT_RESPONSE=$(curl -s -X POST "$BASE_URL/register_user.php" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "student_test",
    "password": "TestPass123",
    "full_name": "Test Student",
    "role": "student",
    "branch": "CS",
    "division": "A",
    "semester": 5
  }')
echo $STUDENT_RESPONSE | jq .
STUDENT_TOKEN=$(echo $STUDENT_RESPONSE | jq -r '.token')

# 3. Faculty generates class ID
echo -e "\n3. Generating class ID..."
CLASS_RESPONSE=$(curl -s -X POST "$BASE_URL/generate_id.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $FACULTY_TOKEN" \
  -d '{
    "branch": "CS",
    "division": "A"
  }')
echo $CLASS_RESPONSE | jq .
CLASS_ID=$(echo $CLASS_RESPONSE | jq -r '.class_id')

# 4. Student marks attendance
echo -e "\n4. Marking attendance..."
ATTENDANCE_RESPONSE=$(curl -s -X POST "$BASE_URL/mark_present.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -d "{\"class_id\": \"$CLASS_ID\"}")
echo $ATTENDANCE_RESPONSE | jq .

# 5. View attendance
echo -e "\n5. Viewing attendance history..."
curl -s -X GET "$BASE_URL/show_attendance.php" \
  -H "Authorization: Bearer $STUDENT_TOKEN" | jq .

echo -e "\n✅ Test workflow complete!"
```

Save as `test_api.sh` and run:
```bash
chmod +x test_api.sh
./test_api.sh
```

---

## Postman Collection

Import this JSON into Postman:

```json
{
  "info": {
    "name": "PHP Attendance System API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Register Student",
      "request": {
        "method": "POST",
        "header": [{"key": "Content-Type", "value": "application/json"}],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"username\": \"test_student\",\n  \"password\": \"TestPass123\",\n  \"full_name\": \"Test Student\",\n  \"role\": \"student\",\n  \"branch\": \"CS\",\n  \"division\": \"A\",\n  \"semester\": 5\n}"
        },
        "url": {
          "raw": "{{base_url}}/register_user.php",
          "host": ["{{base_url}}"],
          "path": ["register_user.php"]
        }
      }
    },
    {
      "name": "Student Login",
      "request": {
        "method": "POST",
        "header": [{"key": "Content-Type", "value": "application/json"}],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"username\": \"test_student\",\n  \"password\": \"TestPass123\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/stud_login.php",
          "host": ["{{base_url}}"],
          "path": ["stud_login.php"]
        }
      }
    },
    {
      "name": "Mark Attendance",
      "request": {
        "method": "POST",
        "header": [
          {"key": "Content-Type", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{student_token}}"}
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"class_id\": \"{{class_id}}\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/mark_present.php",
          "host": ["{{base_url}}"],
          "path": ["mark_present.php"]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost/api"
    },
    {
      "key": "student_token",
      "value": "your_token_here"
    },
    {
      "key": "faculty_token",
      "value": "your_token_here"
    },
    {
      "key": "class_id",
      "value": "your_class_id_here"
    }
  ]
}
```

---

## Tips

1. **Save tokens** after login - you'll need them for all protected endpoints
2. **Network validation** - Student and faculty must be on same subnet (check IP addresses)
3. **Token expiry** - Tokens expire after 24 hours by default
4. **Class expiry** - Class sessions expire after 2 hours
5. **Rate limiting** - Max 5 failed login attempts per 15 minutes

---

## Troubleshooting

### "Unauthorized" error
- Check token is included in header
- Verify token hasn't expired (login again)
- Ensure format is `Bearer <token>`

### "Not on same network"
- Check faculty and student IP addresses
- Both must be on same subnet (e.g., 192.168.1.x)

### "Class session has expired"
- Faculty must generate new class_id
- Sessions last 2 hours by default

### "Too many attempts"
- Wait 15 minutes before trying to login again
- Clear rate_limit table if testing: `DELETE FROM rate_limit;`
