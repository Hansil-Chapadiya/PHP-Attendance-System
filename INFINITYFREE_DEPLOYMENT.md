# InfinityFree Deployment Guide

## Files to Upload

Upload these folders and files to your InfinityFree `htdocs` folder:

```
htdocs/
├── api/
│   ├── display_profile.php
│   ├── faculty_login.php
│   ├── generate_id.php
│   ├── mark_present.php
│   ├── register_user.php
│   ├── show_attendance.php
│   └── stud_login.php
├── backend/
│   ├── config.production.php (rename to config.php)
│   ├── db_connect.php
│   └── helpers.php
└── index.php (optional - landing page)
```

## Step-by-Step Deployment

### 1. Access File Manager
- Go to InfinityFree Control Panel
- Click on **File Manager**
- Navigate to `htdocs` folder

### 2. Upload Files

**Option A: Via File Manager**
- Click **Upload** button
- Upload folders: `api/` and `backend/`
- Upload `index.php` if needed

**Option B: Via FTP** (Recommended for bulk uploads)
- Use FileZilla or any FTP client
- FTP Details from InfinityFree:
  - Host: `ftpupload.net`
  - Username: Your FTP username (from control panel)
  - Password: Your FTP password
  - Port: 21

### 3. Rename Production Config
After uploading:
- Go to `htdocs/backend/`
- Rename `config.production.php` to `config.php`
- Or delete the local `config.php` if it was uploaded and rename production one

### 4. Set Permissions (if needed)
- `api/` folder: 755
- `backend/` folder: 755
- All `.php` files: 644

### 5. Test Your API

**Your API Base URL:**
```
https://yoursite.infinityfreeapp.com/api/
```

Replace `yoursite` with your actual subdomain.

**Test Endpoints:**

```bash
# 1. Test Student Registration
curl -X POST https://yoursite.infinityfreeapp.com/api/register_user.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "test_student",
    "password": "TestPass123",
    "full_name": "Test Student",
    "role": "student",
    "branch": "Computer Science",
    "division": "A",
    "semester": 5
  }'

# 2. Test Student Login
curl -X POST https://yoursite.infinityfreeapp.com/api/stud_login.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "test_student",
    "password": "TestPass123"
  }'
```

### 6. Update Your Frontend/Mobile App

Update API base URL in your frontend:
```
http://localhost/api/  →  https://yoursite.infinityfreeapp.com/api/
```

## Important Notes

1. **HTTPS is enabled** by default on InfinityFree
2. **Database is accessible** only from InfinityFree servers (no remote access)
3. **PHP version**: Check your site's PHP version (should be 7.4+)
4. **Error reporting**: Set to production mode in PHP settings
5. **File upload limit**: 10MB on free plan

## Troubleshooting

### 500 Internal Server Error
- Check file permissions (644 for files, 755 for folders)
- Check error logs in control panel
- Verify `config.php` exists in `backend/` folder

### Database Connection Failed
- Verify database credentials in `config.php`
- Make sure database is created in phpMyAdmin
- Check that tables exist

### CORS Issues
If accessing from a different domain, add to the top of each API file:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

## Security Checklist

- [ ] Changed JWT secret key
- [ ] Verified HTTPS is working
- [ ] Database password is not in any public repository
- [ ] Rate limiting is enabled
- [ ] Error display is off in production
- [ ] Test all endpoints with Postman

## Your API Endpoints

Base: `https://yoursite.infinityfreeapp.com/api/`

- POST `/register_user.php` - Register student/faculty
- POST `/stud_login.php` - Student login
- POST `/faculty_login.php` - Faculty login
- POST `/generate_id.php` - Generate class ID (faculty)
- POST `/mark_present.php` - Mark attendance (student)
- GET `/display_profile.php` - View profile
- GET `/show_attendance.php` - View attendance history
