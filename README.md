# PHP Attendance System with Wi-Fi-Based Authentication

A robust attendance management system developed in PHP that leverages Wi-Fi-based authentication. The system ensures that students and faculty are connected to the same Wi-Fi network before attendance can be marked, adding an extra layer of reliability and preventing proxy attendance.

---

## Features

### 1. **Wi-Fi-Based Authentication**
- Attendance can only be marked if both faculty and students are on the same Wi-Fi network.
- Validates IP address or SSID of the connected devices.

### 2. **User Roles**
- **Admin**: Can manage the entire system, add users (faculty and students), and view attendance reports.
- **Faculty**: Can initiate attendance sessions and monitor real-time attendance.
- **Student**: Can mark their attendance only when the system validates Wi-Fi connection.

### 3. **Attendance Management**
- Initiate attendance for a specific class/session.
- Automatic attendance log creation.
- Export attendance reports to CSV/Excel.

### 4. **Database Management**
- Secure and efficient storage of user data and attendance logs.
- Role-based access control.

---

## Installation Guide

### Prerequisites
1. A server with PHP 7.4 or later installed (e.g., XAMPP, WAMP, or a live web server).
2. MySQL database.
3. Wi-Fi network for connection validation.

### Steps
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/Hansil-Chapadiya/PHP-Attendance-System.git
   cd php-attendance-system
