# 📚 Project File Index

Complete reference guide for all files in the project.

---

## 🎯 Quick Links

- [Start Here](#start-here) - First-time setup
- [API Files](#api-files) - Endpoint implementations
- [Backend Files](#backend-files) - Core utilities
- [Documentation](#documentation) - Guides and references
- [Configuration](#configuration) - Setup files

---

## Start Here

**For Quick Setup:**
1. Read [QUICKSTART.md](QUICKSTART.md) - 5-minute setup guide
2. Run `php backend/setup_database.php` - Database initialization
3. Edit `backend/config.php` - Add your credentials
4. Test with [API_TESTING.md](API_TESTING.md) examples

**For Complete Understanding:**
1. [DEPLOYMENT.md](DEPLOYMENT.md) - Full deployment guide
2. [SECURITY.md](SECURITY.md) - Security audit details
3. [SUMMARY.md](SUMMARY.md) - Project overview

---

## API Files

Location: `api/`

### 1. register_user.php
**Purpose:** User registration (students & faculty)  
**Method:** POST  
**Auth:** None (public)  
**Features:**
- Strong password validation
- Input sanitization
- Username uniqueness check
- Transaction support
- Returns JWT token

**Example:**
```bash
POST /api/register_user.php
Body: {
  "username": "student1",
  "password": "Pass123",
  "full_name": "Student Name",
  "role": "student",
  "branch": "CS",
  "division": "A",
  "semester": 5
}
```

---

### 2. stud_login.php
**Purpose:** Student authentication  
**Method:** POST  
**Auth:** None (public)  
**Features:**
- Rate limiting (5 attempts/15min)
- JWT token generation
- Password verification
- Generic error messages

**Example:**
```bash
POST /api/stud_login.php
Body: {
  "username": "student1",
  "password": "Pass123"
}
```

---

### 3. faculty_login.php
**Purpose:** Faculty authentication  
**Method:** POST  
**Auth:** None (public)  
**Features:**
- Rate limiting
- JWT token generation
- Role verification
- Consistent response format

**Example:**
```bash
POST /api/faculty_login.php
Body: {
  "username": "faculty1",
  "password": "Pass123"
}
```

---

### 4. generate_id.php
**Purpose:** Create class attendance session  
**Method:** POST  
**Auth:** Faculty only (JWT required)  
**Features:**
- Faculty role verification
- Branch ownership check
- Session expiry (2 hours)
- IP address capture
- Unique class ID generation

**Example:**
```bash
POST /api/generate_id.php
Headers: Authorization: Bearer <faculty_token>
Body: {
  "branch": "CS",
  "division": "A"
}
```

---

### 5. mark_present.php
**Purpose:** Mark student attendance  
**Method:** POST  
**Auth:** Student only (JWT required)  
**Features:**
- Student authentication via JWT
- Branch/division verification
- Network validation (same subnet)
- Session expiry check
- Duplicate prevention
- No user_id in body (from token)

**Example:**
```bash
POST /api/mark_present.php
Headers: Authorization: Bearer <student_token>
Body: {
  "class_id": "COM-A-1735567890"
}
```

---

### 6. display_profile.php
**Purpose:** View user profile  
**Method:** GET  
**Auth:** Required (JWT)  
**Features:**
- Authentication required
- Students see own profile
- Faculty can view any profile
- Prepared statements

**Example:**
```bash
GET /api/display_profile.php?user_id=123
Headers: Authorization: Bearer <token>
```

---

### 7. show_attendance.php
**Purpose:** View attendance history  
**Method:** GET  
**Auth:** Required (JWT)  
**Features:**
- Authentication required
- Students see own attendance
- Faculty can view any student
- Includes class details
- Ordered by date (newest first)

**Example:**
```bash
GET /api/show_attendance.php
Headers: Authorization: Bearer <token>
```

---

## Backend Files

Location: `backend/`

### 1. config.php ⚠️ (Git-ignored)
**Purpose:** Configuration settings  
**Type:** PHP array return  
**Security:** Excluded from git  

**Contains:**
- Database credentials
- JWT secret key
- Session settings
- Rate limit configuration
- Class session duration

**Setup:**
```bash
cp backend/config.example.php backend/config.php
# Edit with your values
```

---

### 2. config.example.php
**Purpose:** Configuration template  
**Type:** Example file  
**Usage:** Copy to config.php and customize

**Provides:**
- Default values
- Environment variable examples
- Documentation comments

---

### 3. db_connect.php
**Purpose:** Database connection  
**Features:**
- Loads configuration from config.php
- Creates mysqli connection
- Error handling
- UTF-8 charset setting

**Used by:** All API files

---

### 4. helpers.php ⭐
**Purpose:** Core security utilities  
**Size:** ~370 lines  
**Classes:**

#### Auth Class
- `init()` - Start session
- `generateToken()` - Create JWT
- `verifyToken()` - Validate JWT
- `requireAuth()` - Enforce authentication
- `requireRole()` - Enforce role
- `requireOwnership()` - Verify ownership

#### Validator Class
- `validateUsername()` - Username rules
- `validatePassword()` - Strong password
- `validateFullName()` - Name validation
- `validateRole()` - Role whitelist
- `validateBranch()` - Branch validation
- `validateDivision()` - Division validation
- `validateSemester()` - Semester range

#### NetworkHelper Class
- `getClientIP()` - Get real IP
- `isSameSubnet()` - Subnet validation

#### RateLimiter Class
- `checkLimit()` - Check attempts
- `recordAttempt()` - Log attempt

#### CORSHelper Class
- `handleCORS()` - CORS headers

---

### 5. schema_updates.sql
**Purpose:** Database schema updates  
**Type:** SQL file  

**Creates:**
- `rate_limit` table
- Adds `created_at`, `expires_at` to classes
- Adds indexes for performance

**Run:**
```bash
mysql -u user -p database < backend/schema_updates.sql
```

---

### 6. setup_database.php
**Purpose:** Automated database setup  
**Type:** CLI script  

**Features:**
- Reads schema_updates.sql
- Executes queries
- Verifies table creation
- Error reporting

**Run:**
```bash
php backend/setup_database.php
```

---

## Documentation

### 1. QUICKSTART.md ⭐
**Purpose:** 5-minute setup guide  
**Best for:** First-time setup  
**Contents:**
- Database setup steps
- Configuration guide
- Quick tests
- Common issues

**Start here if:** You want to get running quickly

---

### 2. DEPLOYMENT.md 📘
**Purpose:** Complete deployment guide  
**Size:** 450+ lines  
**Best for:** Production deployment  

**Contents:**
- Security improvements list
- Pre-deployment checklist
- API usage guide with examples
- Authentication flow
- Common errors and solutions
- Testing commands

**Start here if:** You're deploying to production

---

### 3. SECURITY.md 🔒
**Purpose:** Security audit report  
**Size:** 600+ lines  
**Best for:** Understanding security fixes  

**Contents:**
- All 15 vulnerabilities detailed
- Before/after comparisons
- OWASP Top 10 compliance
- Security features added
- Testing performed

**Start here if:** You need security documentation

---

### 4. API_TESTING.md 🧪
**Purpose:** API testing examples  
**Size:** 500+ lines  
**Best for:** Testing endpoints  

**Contents:**
- Curl examples for all endpoints
- Postman collection
- Bash test script
- Error response examples
- Troubleshooting tips

**Start here if:** You want to test the API

---

### 5. SUMMARY.md 📊
**Purpose:** Project overview  
**Size:** 400+ lines  
**Best for:** Understanding scope  

**Contents:**
- Files created/modified
- Security features
- Code statistics
- OWASP compliance
- Performance improvements
- Testing coverage

**Start here if:** You want the big picture

---

### 6. CHANGELOG.md 📝
**Purpose:** Version 2.0 changes  
**Size:** 400+ lines  
**Best for:** Migration guide  

**Contents:**
- Breaking changes
- API changes
- Database changes
- Migration steps
- Frontend update guide
- Configuration options

**Start here if:** You're upgrading from v1

---

### 7. CHECKLIST.md ✅
**Purpose:** Production readiness checklist  
**Best for:** Pre-deployment verification  

**Contents:**
- Security fixes checklist
- Code quality metrics
- Documentation status
- Database requirements
- OWASP compliance
- Deployment readiness

**Start here if:** You're verifying production readiness

---

### 8. README.md 📖
**Purpose:** Original project documentation  
**Contents:**
- Project description
- Features overview
- Installation guide (basic)
- Wi-Fi authentication concept

---

## Configuration

### .gitignore
**Purpose:** Exclude sensitive files  
**Excludes:**
- `backend/config.php` - Credentials
- `.env` files - Environment variables
- IDE files - .vscode, .idea
- OS files - .DS_Store, Thumbs.db
- Logs - *.log

---

### vercel.json
**Purpose:** Vercel deployment config  
**Contains:**
- PHP runtime version
- Route mappings
- Function definitions

---

### Dockerfile
**Purpose:** Docker containerization  
**Base:** PHP 7.4 Apache  
**Exposes:** Port 80

---

## Database Tables

### user
**Purpose:** User accounts  
**Columns:**
- user_id (PK)
- username (unique)
- password (hashed)
- full_name
- role (student/faculty)
- branch

---

### students
**Purpose:** Student details  
**Columns:**
- student_id (PK)
- user_id (FK)
- branch
- division
- semester

---

### faculty
**Purpose:** Faculty details  
**Columns:**
- faculty_id (PK)
- user_id (FK)
- branch

---

### classes
**Purpose:** Class sessions  
**Columns:**
- class_id (PK)
- branch
- division
- faculty_in_charge
- faculty_ip
- created_at ⭐ NEW
- expires_at ⭐ NEW

---

### attendance
**Purpose:** Attendance records  
**Columns:**
- attendance_id (PK)
- user_id (FK)
- class_id (FK)
- date
- status
- marked_by
- marked_time

---

### rate_limit ⭐ NEW
**Purpose:** Rate limiting  
**Columns:**
- id (PK)
- identifier (IP + endpoint)
- attempt_time

---

## File Organization

```
PHP-Attendance-System/
│
├── 📁 api/                      # API Endpoints (7 files)
│   ├── register_user.php        # Registration
│   ├── stud_login.php          # Student login
│   ├── faculty_login.php       # Faculty login
│   ├── generate_id.php         # Create class
│   ├── mark_present.php        # Mark attendance
│   ├── display_profile.php     # View profile
│   └── show_attendance.php     # View history
│
├── 📁 backend/                  # Core Backend (6 files)
│   ├── config.php ⚠️           # Configuration (git-ignored)
│   ├── config.example.php      # Config template
│   ├── db_connect.php          # Database connection
│   ├── helpers.php ⭐          # Security utilities
│   ├── schema_updates.sql      # Database schema
│   └── setup_database.php      # Setup automation
│
├── 📄 QUICKSTART.md ⭐         # 5-minute setup
├── 📄 DEPLOYMENT.md            # Production guide
├── 📄 SECURITY.md              # Security audit
├── 📄 API_TESTING.md           # Testing guide
├── 📄 SUMMARY.md               # Overview
├── 📄 CHANGELOG.md             # Version changes
├── 📄 CHECKLIST.md             # Readiness check
├── 📄 README.md                # Original docs
│
├── .gitignore                  # Git exclusions
├── vercel.json                 # Vercel config
├── Dockerfile                  # Docker config
└── index.php                   # Entry point
```

---

## Legend

- ⭐ = Most important files
- 🔒 = Security-related
- ⚠️ = Sensitive (git-ignored)
- 📘 = Documentation
- 🧪 = Testing
- 📊 = Overview

---

## Recommended Reading Order

### For Developers (First Time)
1. [QUICKSTART.md](QUICKSTART.md) - Get it running
2. [API_TESTING.md](API_TESTING.md) - Test endpoints
3. [SECURITY.md](SECURITY.md) - Understand security
4. `backend/helpers.php` - Learn utilities

### For Production Deployment
1. [CHECKLIST.md](CHECKLIST.md) - Verify readiness
2. [DEPLOYMENT.md](DEPLOYMENT.md) - Deploy properly
3. [SECURITY.md](SECURITY.md) - Security review
4. [SUMMARY.md](SUMMARY.md) - Understand scope

### For Maintenance
1. [CHANGELOG.md](CHANGELOG.md) - Version history
2. [API_TESTING.md](API_TESTING.md) - Test after changes
3. `backend/schema_updates.sql` - Database changes
4. [DEPLOYMENT.md](DEPLOYMENT.md) - Reference guide

---

## File Size Summary

| Category | Files | Lines |
|----------|-------|-------|
| API Endpoints | 7 | ~1000 |
| Backend Core | 6 | ~600 |
| Documentation | 8 | ~2500 |
| Configuration | 3 | ~100 |
| **Total** | **24** | **~4200** |

---

**Last Updated:** December 30, 2025  
**Version:** 2.0.0
