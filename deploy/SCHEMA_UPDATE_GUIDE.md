# 🚀 InfinityFree Deployment Guide - Schema Update

## ⚠️ IMPORTANT: Run This First Before Uploading Files!

### Step 1: Update Database Schema

1. **Login to InfinityFree Control Panel**
   - Go to your InfinityFree dashboard
   - Click on "MySQL Databases"
   - Click "phpMyAdmin"

2. **Select Your Database**
   - Click on your database name (e.g., `epiz_xxxxx_attendance`)

3. **Run Schema Update**
   - Click on "SQL" tab at the top
   - Open the file: `deploy/infinityfree_schema_update.sql`
   - Copy ALL the SQL code from the file
   - Paste it into the SQL query box
   - Click "Go" to execute

4. **Verify Updates**
   - Scroll down in the same SQL tab
   - Copy and paste the verification queries (at the bottom of the SQL file)
   - Click "Go" to check if everything is updated correctly

### Step 2: What Gets Updated?

✅ **classes** table:
   - Adds `subject` column (VARCHAR 100)
   - Adds `created_at` and `expires_at` timestamps

✅ **students** table:
   - Adds `semester` column (INT)

✅ **schedule** table:
   - Creates new table for weekly timetable
   - Stores faculty teaching schedules

✅ **Indexes**:
   - Adds performance indexes for faster queries

✅ **rate_limit** table:
   - Creates table for API rate limiting

### Step 3: After Schema Update

Once the schema is updated, you can upload your files:

1. Upload ALL files from the `deploy/` folder to your `htdocs/` on InfinityFree
2. Make sure `backend/config.php` has correct database credentials
3. Test the dashboards!

### 🔍 Verification Checklist

After running the schema update, verify:

- [ ] `classes` table has `subject` column
- [ ] `students` table has `semester` column  
- [ ] `schedule` table exists
- [ ] No error messages in phpMyAdmin
- [ ] Faculty dashboard shows subjects correctly
- [ ] Student dashboard shows semester numbers

### 🆘 Troubleshooting

**If you see "Column already exists" errors:**
- ✅ This is NORMAL! The SQL uses `IF NOT EXISTS` to avoid errors
- The script will skip columns that already exist

**If you see "Table already exists":**
- ✅ This is also NORMAL! The script won't recreate existing tables

**If you see "Unknown column 'subject'" after upload:**
- ❌ Schema update didn't run properly
- Re-run the SQL script in phpMyAdmin
- Check that you selected the correct database

### 📋 Required Files in Deploy Folder

Make sure these updated files are in your `deploy/` folder:

```
deploy/
├── api/
│   ├── show_attendance.php (✅ Updated - includes subject & semester)
│   ├── get_faculty_attendance.php (✅ Updated - fixed grouping)
│   └── ... (other API files)
├── frontend/
│   ├── faculty-dashboard.html (✅ Updated - scrollable with filters)
│   ├── faculty-dashboard.js (✅ Updated - enhanced display)
│   ├── student-dashboard.html (✅ Updated - filters & scrollable)
│   ├── student-dashboard.js (✅ Updated - attendance records)
│   └── ... (other frontend files)
├── backend/
│   └── config.php (⚠️ Update database credentials!)
└── infinityfree_schema_update.sql (🔥 RUN THIS FIRST!)
```

### 🎉 Success!

If everything works, you should see:
- Faculty can filter by Division, Subject, and Date
- Student can filter by Subject and Date
- Subjects display correctly for all attendance records
- Semester numbers show up in student records
- Scrollable attendance lists with beautiful UI

---

**Need Help?** Check the main README.md for more deployment instructions.
