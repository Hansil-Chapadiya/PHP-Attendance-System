# 🚀 Production Deployment Checklist

## Pre-Deployment Verification

### ✅ Backend Configuration
- [ ] **Database credentials** verified in `backend/config.php`
  - Host: `sql207.infinityfree.com`
  - Database: `if0_40793832_attendance`
  - User: `if0_40793832`
  - Password: `1LadPbIbHs5ZU`

- [ ] **JWT Secret** is production-ready (64+ character random string)
- [ ] **Error reporting** is OFF in production (`display_errors = Off`)
- [ ] **CORS headers** are present in all API endpoints
- [ ] **InfinityFree handlers** implemented in all endpoints

### ✅ Frontend Configuration
- [ ] **API_BASE_URL** in `app.js` points to production:
  ```javascript
  const API_BASE_URL = 'https://hcthegreat.ct.ws/api/';
  ```
- [ ] All JavaScript files are minified (optional for now)
- [ ] All CSS is optimized and production-ready
- [ ] Images are compressed (if any)

### ✅ Security Checks
- [ ] No sensitive data in frontend code
- [ ] No console.log() statements exposing secrets
- [ ] SQL injection prevention active (prepared statements)
- [ ] XSS protection enabled
- [ ] Rate limiting configured (5 attempts per 15 minutes)
- [ ] Password hashing using bcrypt
- [ ] JWT tokens with expiration (2 hours)

### ✅ Testing Checklist
- [ ] **Student Login** works with test credentials
- [ ] **Faculty Login** works with test credentials
- [ ] **Registration** creates new users successfully
- [ ] **Mark Attendance** accepts valid Class ID
- [ ] **Generate Class ID** creates unique IDs
- [ ] **Show Attendance** displays records correctly
- [ ] **Display Profile** shows user information
- [ ] **Wi-Fi Detection** indicates connection status
- [ ] **Session Management** handles logout properly
- [ ] **Error Handling** shows user-friendly messages

## Deployment Steps

### Step 1: Prepare Deployment Package
```bash
# On Windows
deploy.bat

# On Linux/Mac
bash deploy.sh
```

This creates a `deploy/` folder with production-ready files.

### Step 2: Upload to InfinityFree

#### Option A: File Manager (Recommended)
1. **Login** to InfinityFree Control Panel
2. Go to **File Manager** → htdocs/
3. **Delete** all existing files (backup first!)
4. **Upload** files from `deploy/` folder:
   - Upload `frontend/` folder
   - Upload `api/` folder
   - Upload `backend/` folder
   - Upload `.htaccess`

#### Option B: FTP
```
Host: ftpupload.net
Port: 21
Username: if0_40793832
Password: [Your FTP password]
Directory: /htdocs/
```

Upload all files from `deploy/` to `/htdocs/`

### Step 3: Verify Database Connection
1. Visit: `https://hcthegreat.ct.ws/check_schema.php`
2. Should show: "Database connection successful"
3. If error, check `backend/config.php` credentials

### Step 4: Test API Endpoints
```bash
# Test health check
curl https://hcthegreat.ct.ws/api/

# Test registration
curl -X POST https://hcthegreat.ct.ws/api/register_user.php \
  -H "Content-Type: application/json" \
  -d '{"username":"test123","password":"test123","role":"student"}'

# Test login
curl -X POST https://hcthegreat.ct.ws/api/stud_login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"test123","password":"test123"}'
```

### Step 5: Test Frontend

1. **Open**: `https://hcthegreat.ct.ws/login.html`
2. **Test Student Login**:
   - Username: test123
   - Password: test123
   - Should redirect to student dashboard

3. **Test Faculty Login**:
   - Create faculty account via registration
   - Login and generate Class ID
   - Verify ID is displayed

4. **Test Attendance Flow**:
   - Login as student
   - Go to "Mark Attendance"
   - Enter faculty's Class ID
   - Verify success message

### Step 6: Monitor Performance
- [ ] Check page load times (should be < 2s)
- [ ] Verify API response times (should be < 500ms)
- [ ] Test on mobile devices
- [ ] Test on different browsers

## Post-Deployment

### ✅ Monitoring Setup
- [ ] Setup error logging to file
- [ ] Monitor database size and performance
- [ ] Track API usage and patterns
- [ ] Setup uptime monitoring (e.g., UptimeRobot)

### ✅ User Training
- [ ] Provide login credentials to test users
- [ ] Document the attendance marking process
- [ ] Create quick start guide for faculty
- [ ] Setup support channel

### ✅ Backup Strategy
- [ ] Backup database daily (InfinityFree limits apply)
- [ ] Download code backup weekly
- [ ] Keep local development copy synced

## Rollback Plan

If deployment fails:

1. **Restore from backup**:
   - Re-upload previous working version
   - Restore database from backup

2. **Check error logs**:
   ```
   /htdocs/error_log (if available)
   ```

3. **Common issues**:
   - **500 Error**: Check PHP syntax, file permissions
   - **Database Error**: Verify credentials in config.php
   - **CORS Error**: Check CORS headers in API files
   - **404 Error**: Verify file paths and .htaccess

## Performance Optimization

### After Initial Deployment:

1. **Enable Browser Caching** (add to .htaccess):
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

2. **Compress Assets**:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
```

3. **Minify CSS/JS** (use online tools):
   - https://cssminifier.com/
   - https://javascript-minifier.com/

## Maintenance Schedule

- **Daily**: Monitor error logs and API errors
- **Weekly**: Check database size and performance
- **Monthly**: Review and clean old attendance records
- **Quarterly**: Update dependencies and security patches

## Support Contacts

- **InfinityFree Support**: https://forum.infinityfree.com/
- **Database Issues**: Check phpMyAdmin at InfinityFree panel
- **Code Issues**: Check error_log in htdocs/

## Success Metrics

Track these after deployment:

- [ ] Total registered users
- [ ] Daily active users
- [ ] Attendance records per day
- [ ] Average session duration
- [ ] Error rate (should be < 1%)
- [ ] Page load time (should be < 2s)
- [ ] API response time (should be < 500ms)

---

## Quick Reference

**Production URL**: https://hcthegreat.ct.ws/
**API Base**: https://hcthegreat.ct.ws/api/
**Database**: sql207.infinityfree.com
**Control Panel**: https://infinityfree.com/panel

---

✅ **Deployment Complete!** Your attendance system is now live.
