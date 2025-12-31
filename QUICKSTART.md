# 🚀 Quick Start Guide

Get your production-ready attendance system running in 5 minutes!

---

## Step 1: Database Setup (2 minutes)

### Create Database
```sql
CREATE DATABASE attendance_system;
USE attendance_system;
```

### Run Setup Script
```bash
# Option A: Automated setup
php backend/setup_database.php

# Option B: Manual setup
mysql -u your_user -p attendance_system < backend/schema_updates.sql
```

---

## Step 2: Configuration (1 minute)

### Create Config File
```bash
cp backend/config.example.php backend/config.php
```

### Edit Configuration
Edit `backend/config.php`:

```php
return [
    'database' => [
        'host' => 'localhost',
        'name' => 'attendance_system',
        'user' => 'your_username',
        'password' => 'your_password',
        'port' => 3306
    ],
    'jwt' => [
        // Generate a random secret:
        // openssl rand -base64 32
        'secret_key' => 'PASTE_YOUR_RANDOM_SECRET_HERE',
        'expiry' => 86400 // 24 hours
    ],
    'session' => [
        'secure' => false, // Set true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ],
    'class' => [
        'session_duration' => 7200 // 2 hours
    ],
    'rate_limit' => [
        'max_attempts' => 5,
        'lockout_time' => 900 // 15 minutes
    ]
];
```

### Generate JWT Secret
```bash
# Linux/Mac
openssl rand -base64 32

# Windows PowerShell
[Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Maximum 256 }))
```

---

## Step 3: Test Installation (2 minutes)

### Test 1: Register a Student
```bash
curl -X POST http://localhost/api/register_user.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "student1",
    "password": "Student123",
    "full_name": "Test Student",
    "role": "student",
    "branch": "Computer Science",
    "division": "A",
    "semester": 5
  }'
```

**Expected:** `"status": "success"` with a token

### Test 2: Register a Faculty
```bash
curl -X POST http://localhost/api/register_user.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "faculty1",
    "password": "Faculty123",
    "full_name": "Test Faculty",
    "role": "faculty",
    "branch": "Computer Science"
  }'
```

### Test 3: Login
```bash
curl -X POST http://localhost/api/stud_login.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "student1",
    "password": "Student123"
  }'
```

**Save the token from the response!**

### Test 4: Access Protected Endpoint
```bash
# Replace YOUR_TOKEN with the token from previous step
curl -X GET "http://localhost/api/display_profile.php" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected:** Your profile information

---

## Complete Workflow Test

### 1. Faculty Creates Class Session
```bash
# Login as faculty first
FACULTY_TOKEN=$(curl -s -X POST http://localhost/api/faculty_login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"faculty1","password":"Faculty123"}' | jq -r '.token')

# Generate class ID
curl -X POST http://localhost/api/generate_id.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $FACULTY_TOKEN" \
  -d '{
    "branch": "Computer Science",
    "division": "A"
  }'
```

**Note the class_id from response!**

### 2. Student Marks Attendance
```bash
# Login as student
STUDENT_TOKEN=$(curl -s -X POST http://localhost/api/stud_login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"student1","password":"Student123"}' | jq -r '.token')

# Mark attendance (replace CLASS_ID)
curl -X POST http://localhost/api/mark_present.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -d '{
    "class_id": "COM-A-1735567890"
  }'
```

### 3. View Attendance
```bash
curl -X GET "http://localhost/api/show_attendance.php" \
  -H "Authorization: Bearer $STUDENT_TOKEN"
```

---

## ✅ Installation Complete!

Your attendance system is now:
- ✅ Fully secured
- ✅ Production ready
- ✅ OWASP compliant
- ✅ Ready for students and faculty

---

## Next Steps

### For Development
1. Read [DEPLOYMENT.md](DEPLOYMENT.md) for detailed API documentation
2. Check [API_TESTING.md](API_TESTING.md) for testing examples
3. Review [SECURITY.md](SECURITY.md) for security details

### For Production
1. Enable HTTPS on your server
2. Set `'secure' => true` in config.php
3. Restrict CORS to your domain (edit `backend/helpers.php`)
4. Set up regular database backups
5. Configure error logging
6. Monitor rate_limit table growth

### Build Frontend
The API is ready! Build your frontend using:
- React / Vue / Angular
- Mobile app (React Native / Flutter)
- Desktop app (Electron)

---

## Common Issues

### "Database connection failed"
✅ Check database credentials in `backend/config.php`  
✅ Verify MySQL is running  
✅ Check database name exists

### "Unauthorized" on all requests
✅ Check JWT secret is set in config.php  
✅ Verify token is included: `Authorization: Bearer <token>`  
✅ Check token hasn't expired (24 hours)

### "Password must contain..." error
✅ Passwords need: min 8 chars, uppercase, lowercase, number  
✅ Example valid password: `MyPass123`

### "Not on same network" error
✅ Faculty and student must be on same subnet  
✅ Both should have IPs like 192.168.1.x  
✅ Check with: `ipconfig` (Windows) or `ifconfig` (Linux/Mac)

---

## File Structure

```
PHP-Attendance-System/
├── api/                      # API endpoints
│   ├── stud_login.php       # Student login
│   ├── faculty_login.php    # Faculty login
│   ├── register_user.php    # Registration
│   ├── generate_id.php      # Create class session
│   ├── mark_present.php     # Mark attendance
│   ├── display_profile.php  # View profile
│   └── show_attendance.php  # View attendance history
├── backend/
│   ├── config.php           # Configuration (create this)
│   ├── config.example.php   # Configuration template
│   ├── db_connect.php       # Database connection
│   ├── helpers.php          # Security utilities
│   ├── schema_updates.sql   # Database schema
│   └── setup_database.php   # Setup script
├── DEPLOYMENT.md            # Deployment guide
├── SECURITY.md              # Security audit
├── API_TESTING.md           # API examples
├── SUMMARY.md               # Project summary
└── README.md                # Original docs
```

---

## Support

Need help?
1. Check documentation in DEPLOYMENT.md
2. Review security info in SECURITY.md
3. Test with examples in API_TESTING.md
4. Check error logs in PHP error log

---

## 🎉 You're All Set!

Your attendance system is ready for:
- ✅ Student registration and login
- ✅ Faculty class management
- ✅ Secure attendance marking
- ✅ Attendance tracking
- ✅ Wi-Fi-based validation

**Happy coding!** 🚀
