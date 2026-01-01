# Calendar/Timetable Feature - Implementation Guide

## Overview
We've successfully added a weekly schedule/timetable calendar feature to the attendance system! 🎉

### Features Implemented

#### For Students
- **Weekly Schedule View**: Students can see their class schedule organized by day
- Shows:
  - Subject names
  - Time slots
  - Faculty names teaching each subject
- Automatically displays schedule for their division

#### For Faculty
- **Teaching Schedule View**: Faculty members can see their teaching assignments
- Shows:
  - Subjects they teach
  - Divisions assigned
  - Time slots for each class
- Organized by day of the week

## Database Changes

### New Table: `schedule`
```sql
CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `subject` varchar(100) NOT NULL,
  `division` varchar(10) NOT NULL,
  `time_slot` varchar(50) DEFAULT NULL,
  `faculty_id` int(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_faculty_day` (`faculty_id`, `day_of_week`),
  KEY `idx_division_day` (`division`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## New API Endpoints

### 1. Get Student Schedule
**File:** `api/get_student_schedule.php`
- **Method:** GET
- **Auth:** Required (Bearer token)
- **Response:**
```json
{
  "success": true,
  "division": "A",
  "schedule": {
    "Monday": [
      {
        "subject": "Data Science",
        "time_slot": "9:00 AM - 10:00 AM",
        "faculty_name": "John Doe"
      }
    ],
    "Tuesday": [...],
    ...
  }
}
```

### 2. Get Faculty Schedule
**File:** `api/get_faculty_schedule.php`
- **Method:** GET
- **Auth:** Required (Bearer token)
- **Response:**
```json
{
  "success": true,
  "schedule": {
    "Monday": [
      {
        "subject": "Python Programming",
        "division": "A",
        "time_slot": "10:00 AM - 11:00 AM"
      }
    ],
    "Tuesday": [...],
    ...
  }
}
```

## Frontend Updates

### Student Dashboard
**File:** `frontend/student-dashboard.html`
- Added "Weekly Schedule" section with calendar icon
- Shows schedule organized by day with subject cards

**File:** `frontend/student-dashboard.js`
- Added `loadWeeklySchedule()` function
- Fetches and displays schedule from API
- Shows appropriate message if no schedule exists

### Faculty Dashboard
**File:** `frontend/faculty-dashboard.html`
- Added "My Teaching Schedule" section
- Shows teaching assignments by day and division

**File:** `frontend/faculty-dashboard.js`
- Added `loadTeachingSchedule()` function
- Fetches and displays teaching schedule from API
- Displays subjects organized by day with division badges

## Setup Instructions

### Step 1: Create the Schedule Table
```bash
php create_schedule_table.php
```

### Step 2: Add Sample Data (Optional)
```bash
php create_sample_schedule.php
```

This creates sample schedules for:
- Division A: 15 classes across 5 days
- Division B: 15 classes across 5 days

### Step 3: Access the Feature
1. **Students**: Login → Dashboard → Scroll down to "Weekly Schedule"
2. **Faculty**: Login → Dashboard → Scroll down to "My Teaching Schedule"

## Adding Schedule Data Manually

You can add schedule entries directly via SQL:

```sql
INSERT INTO schedule (day_of_week, subject, division, time_slot, faculty_id) 
VALUES 
  ('Monday', 'Data Science', 'A', '9:00 AM - 10:00 AM', 1),
  ('Monday', 'Python Programming', 'A', '10:00 AM - 11:00 AM', 1),
  ('Tuesday', 'C Programming', 'A', '9:00 AM - 10:00 AM', 2);
```

## Sample Schedule Format

### For Division A (Monday)
- 9:00 AM - 10:00 AM: Data Science
- 10:00 AM - 11:00 AM: Python Programming
- 11:00 AM - 12:00 PM: C++ Basics

### For Division B (Monday)
- 9:00 AM - 10:00 AM: C++ Basics
- 10:00 AM - 11:00 AM: Algorithms
- 11:00 AM - 12:00 PM: Data Structures

## UI Features

### Student View
- Clean card layout with day headers
- Subject name prominently displayed
- Time slot and faculty name in smaller text
- Empty state message if no schedule exists

### Faculty View
- Organized by days of the week
- Each class shown with subject and division
- Border accent for visual separation
- Division badges for quick identification

## Future Enhancements (Optional)

1. **Admin Panel**: Create UI for admins to add/edit schedules
2. **Bulk Import**: CSV/Excel upload for schedule data
3. **Conflict Detection**: Prevent scheduling conflicts
4. **Room Management**: Add classroom/location information
5. **Semester Support**: Different schedules per semester
6. **Calendar Integration**: Export to Google Calendar/iCal
7. **Mobile Notifications**: Remind students of upcoming classes

## Troubleshooting

### Schedule not showing?
1. Check if schedule table exists: `SHOW TABLES LIKE 'schedule';`
2. Verify data exists: `SELECT * FROM schedule;`
3. Check browser console for API errors
4. Verify user is logged in with correct role

### API returns empty schedule?
- Ensure student's division matches schedule division
- Ensure faculty_id matches actual faculty records
- Check if sample data was added successfully

## Files Modified/Created

### Backend
- ✅ `backend/schema_updates.sql` - Added schedule table definition
- ✅ `api/get_student_schedule.php` - Student schedule API
- ✅ `api/get_faculty_schedule.php` - Faculty schedule API
- ✅ `create_schedule_table.php` - Table creation script
- ✅ `create_sample_schedule.php` - Sample data script

### Frontend
- ✅ `frontend/student-dashboard.html` - Added schedule section
- ✅ `frontend/student-dashboard.js` - Added schedule loading
- ✅ `frontend/faculty-dashboard.html` - Added schedule section
- ✅ `frontend/faculty-dashboard.js` - Added schedule loading

## Testing

1. Login as a student from Division A or B
2. Check "Weekly Schedule" section on dashboard
3. Verify subjects appear for each day
4. Login as a faculty member
5. Check "My Teaching Schedule" section
6. Verify your teaching assignments appear

## Notes

- Schedule data is stored independently of attendance
- Faculty must have valid records in the `faculty` table
- Students must have valid division assignments
- Time slots are optional but recommended
- Days without classes are automatically hidden
- Schedule is read-only from student/faculty dashboards

---

**Congratulations! The Calendar/Timetable feature is now live!** 🎊

Students can now see their weekly class schedule and faculty can view their teaching assignments. The feature integrates seamlessly with the existing attendance system.
