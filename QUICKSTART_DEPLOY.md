# 🚀 Quick Start Guide - Production Deployment

## ⚡ 60-Second Deploy

### Step 1: Run Deploy Script (Windows)
```bash
deploy.bat
```

### Step 2: Upload to InfinityFree
1. Login to https://infinityfree.com/panel
2. Open **File Manager**
3. Go to `/htdocs/` folder
4. **Delete all files** (backup first!)
5. **Upload** from `deploy/` folder:
   - `frontend/` (entire folder)
   - `api/` (entire folder)
   - `backend/` (entire folder)
   - `.htaccess` file

### Step 3: Test
Visit: **https://hcthegreat.ct.ws/login.html**

---

## 🧪 Quick Test

### Local Testing
```bash
cd frontend
php -S localhost:8000
```
Open: http://localhost:8000/login.html

### Production Testing
Open: https://hcthegreat.ct.ws/production-tester.html

---

## 📋 Test Workflow

### Create Student Account
1. Open login page
2. Click "Register New Account" (if you add this link)
3. Or use production-tester.html → Register User
4. Username: `test123`, Password: `test123`, Role: `student`

### Create Faculty Account
1. Use production-tester.html → Register User
2. Username: `faculty123`, Password: `faculty123`, Role: `faculty`

### Student Flow
1. Login as student
2. View dashboard
3. Click "Mark Attendance"
4. Enter Class ID from faculty
5. Submit

### Faculty Flow
1. Login as faculty
2. Enter Branch: `CSE`, Division: `A`
3. Click "Start Session"
4. Copy Class ID
5. Share with students

---

## 🔧 Configuration

### API Endpoint (if needed)
**File:** `frontend/app.js`
```javascript
// Production
const API_BASE_URL = 'https://hcthegreat.ct.ws/api/';

// Local testing
const API_BASE_URL = 'http://localhost/PHP-Attendance-System/api/';
```

### Database Credentials
**File:** `backend/config.php`
```php
define('DB_HOST', 'sql207.infinityfree.com');
define('DB_NAME', 'if0_40793832_attendance');
define('DB_USER', 'if0_40793832');
define('DB_PASS', '1LadPbIbHs5ZU');
```

---

## 📁 What You're Deploying

```
deploy/
├── frontend/
│   ├── login.html
│   ├── login.js
│   ├── student-dashboard.html
│   ├── student-dashboard.js
│   ├── mark-attendance.html
│   ├── mark-attendance.js
│   ├── faculty-dashboard.html
│   ├── faculty-dashboard.js
│   ├── app.js (core utilities)
│   └── styles.css (design system)
├── api/
│   ├── register_user.php
│   ├── stud_login.php
│   ├── faculty_login.php
│   ├── generate_id.php
│   ├── mark_present.php
│   ├── show_attendance.php
│   └── display_profile.php
├── backend/
│   ├── config.php
│   ├── db_connect.php
│   └── helpers.php
└── .htaccess
```

**Total Size:** ~50KB (incredibly lightweight!)

---

## ✅ Features Included

### Security
- ✅ JWT authentication
- ✅ Password hashing (bcrypt)
- ✅ Rate limiting (5/15min)
- ✅ SQL injection prevention
- ✅ XSS protection
- ✅ CORS headers

### UI/UX
- ✅ Mobile-first responsive design
- ✅ System fonts (no external fonts)
- ✅ Loading states
- ✅ Error handling
- ✅ Success feedback
- ✅ Touch-friendly buttons
- ✅ Fast page loads (<1s)

### Functionality
- ✅ User registration
- ✅ Role-based login (Student/Faculty)
- ✅ JWT token management
- ✅ Wi-Fi detection
- ✅ Generate Class ID
- ✅ Mark attendance
- ✅ View attendance history
- ✅ Display profile
- ✅ Session management
- ✅ Auto-logout on token expiry

### InfinityFree Compatibility
- ✅ JavaScript injection cleaning
- ✅ `?i=1` redirect handling
- ✅ Retry logic for failed requests
- ✅ Response sanitization

---

## 🐛 Common Issues

### "Login Failed"
- **Check:** Backend credentials in `config.php`
- **Test:** Open `https://hcthegreat.ct.ws/check_schema.php`
- **Fix:** Verify database connection

### "CORS Error"
- **Check:** CORS headers in PHP files
- **Fix:** Ensure all API files have `header('Access-Control-Allow-Origin: *');`

### "Token Expired"
- **Normal:** Tokens expire after 2 hours
- **Fix:** Login again

### "Class ID Invalid"
- **Check:** Class ID from faculty is recent (expires after 1 hour)
- **Fix:** Generate new Class ID

---

## 📞 Production URLs

| Resource | URL |
|----------|-----|
| **Login Page** | https://hcthegreat.ct.ws/login.html |
| **API Base** | https://hcthegreat.ct.ws/api/ |
| **Test Page** | https://hcthegreat.ct.ws/production-tester.html |
| **Database** | sql207.infinityfree.com |
| **Control Panel** | https://infinityfree.com/panel |

---

## 🎯 Success Metrics

After deployment, you should see:

- ✅ Login page loads in <1 second
- ✅ API responses in <500ms
- ✅ No console errors
- ✅ Mobile responsive (test on phone)
- ✅ All features working end-to-end

---

## 📚 Full Documentation

- **[INTEGRATION.md](frontend/INTEGRATION.md)** - Complete integration guide
- **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Full deployment checklist
- **[README.md](frontend/README.md)** - Frontend documentation
- **[SECURITY.md](SECURITY.md)** - Security measures

---

## 🚀 You're Ready!

Your attendance system is **production-ready**.

**Just upload and test!** 🎉

---

**Need Help?**
- Test locally first: `cd frontend && php -S localhost:8000`
- Use production-tester.html to debug API issues
- Check browser console for JavaScript errors
- Verify database connection via check_schema.php
