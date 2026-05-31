# Demo:
[p01-production.up.railway.app 
](https://p01-production.up.railway.app/)# School Management System - Setup & Testing Guide

## 🔧 Initial Setup

## 📦 Teacher ZIP Setup (Recommended)

If you send this project as a `.zip`, your teacher must do these exact steps:

1. Extract the ZIP into `htdocs` (example: `C:/xampp/htdocs/P01imen`).
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import `shema.sql`.
4. **Install PHP dependencies** (see section below ⚠️).
5. Open `http://localhost/P01imen/`.

---

## ⚠️ Required: Install PHP Libraries (Composer)

If you see this error when opening the project:

```
Warning: require(...thecodingmachine/safe/lib/special_cases.php): Failed to open stream
Fatal error: Uncaught Error: Failed opening required ...
```

It means the PHP libraries are missing. You must run **Composer** once to install them.

### Step 1 — Install Composer (only once on your PC)

1. Go to: **https://getcomposer.org/download/**
2. Download **Composer-Setup.exe**
3. Run the installer → click Next until Finish
4. Restart your CMD / Terminal

### Step 2 — Install the project libraries

Open **CMD** (Command Prompt) and run:

```bash
cd C:\xampp\htdocs\P01imen
composer install
```

> ⏳ Wait until it finishes (it will download the missing libraries into the `vendor/` folder).

### Step 3 — Refresh the page

Go back to your browser and reload:
```
http://localhost/P01imen/
```

The error should be gone. ✅

> 💡 **You only need to do this once.** After `composer install`, the libraries stay in the `vendor/` folder permanently.

---

Important:
- The data (admin, teachers, modules, full student class list) is included in `shema.sql`.
- Passwords are already stored as bcrypt hashes in the SQL seed.
- No extra script is required after importing `shema.sql`.

### 1. Database Setup
- Run the SQL schema file to create the database:
  ```
  mysql -u root < shema.sql
  ```
   This will create the `university_system` database with all required tables.

### 2. Create Test Users
- Your `shema.sql` already inserts admin, teachers, modules, and the full class students automatically.
- Optional only: open `/create_user.php` if you want to overwrite/reset only the small demo accounts:
  ```
  http://localhost/P01imen/create_user.php
  ```
   This script is not needed for teacher evaluation setup.

### 3. Database Configuration
- The database configuration is in `/config/db.php`
- Default credentials: 
  - Host: `localhost`
   - Database: `university_system`
  - User: `root`
  - Password: (empty)

## 👥 Test Credentials

### Admin Login
**URL:** `http://localhost/P01imen/auth/login_admin.php`
- **Username:** `admin`
- **Password:** `admin123`

### Teacher Login
**URL:** `http://localhost/P01imen/auth/login_teacher.php`

| Name | Email | Password |
|------|-------|----------|
| Amina Gheffar | amina.gheffar@gmail.com | gheffaramina123 |
| Hamza Abdellahoum | abdellahoumhamza89@gmail.com | abdellahoumhamza123 |
| Labde Laachemi | labde79@gmail.com | laachemilabde123 |
| Samira Khellaf | samira.khellaf@edusync.local | khellaf123 |
| Karim Bouzid | karim.bouzid@edusync.local | bouzid123 |

### Student Login
**URL:** `http://localhost/P01imen/auth/login_student.php`

You can login with either **Email** or **Student Number**



| Name | Email | Student Number | Password |
|------|-------|-----------------|----------|
| Imen Zighed | zighedimen921@gmail.com | 232335330411 | 232335330411 |
| Dekrah Lakhal | dekrah.lakhal@gmail.com | 242431577219 | 242431577219 |
| Meriem Ramoul | meriem.ramoul@gmail.com | 242431422801 | 242431422801 |

## 🌐 Main Login Pages

1. **Role Selection (Landing Page)**
   - URL: `http://localhost/P01imen/auth/login.php`
   - Shows three options to choose your role

2. **Admin Login**
   - URL: `http://localhost/P01imen/auth/login_admin.php`
   - Username-based login

3. **Teacher Login**
   - URL: `http://localhost/P01imen/auth/login_teacher.php`
   - Email-based login

4. **Student Login**
   - URL: `http://localhost/P01imen/auth/login_student.php`
   - Email or Student Number login

## 📋 Database Schema

### Tables Structure

**admin**
- id (INT, PK)
- username (VARCHAR)
- password_hash (VARCHAR)
- role (VARCHAR)

**teachers**
- id (INT, PK)
- first_name (VARCHAR)
- last_name (VARCHAR)
- email (VARCHAR, UNIQUE)
- subject (VARCHAR)
- password_hash (VARCHAR)
- must_change_password (TINYINT)
- is_active (TINYINT)
- created_at (TIMESTAMP)

**students**
- id (INT, PK)
- first_name (VARCHAR)
- last_name (VARCHAR)
- email (VARCHAR, UNIQUE)
- student_number (VARCHAR, UNIQUE)
- birth_date (DATE)
- password_hash (VARCHAR)
- must_change_password (TINYINT)
- is_active (TINYINT)
- created_at (TIMESTAMP)

**modules**
- id (INT, PK)
- code (VARCHAR)
- title (VARCHAR)
- coefficient (INT)
- teacher_id (INT, FK)

**enrollments**
- id (INT, PK)
- student_id (INT, FK)
- module_id (INT, FK)
- academic_year (VARCHAR)

**notes**
- id (INT, PK)
- student_id (INT, FK)
- module_id (INT, FK)
- grade (DECIMAL)
- academic_year (VARCHAR)

## 🔑 Key Features

### Session Management
- All users have session-based authentication
- Sessions store: `user_id`, `user_name`, `user_role`, `must_change_password`

### Role-Based Access
- **Admin**: Full system access
- **Teachers**: Can manage their modules and student grades
- **Students**: Can view their grades and modules

### Password Security
- All passwords are hashed using PHP's `password_hash()` with BCRYPT
- Password verification uses `password_verify()`

### Active Status
- Teachers and students can be deactivated by admin
- Inactive accounts cannot login

## 🚀 Testing Workflow

## 📥 Import Your Real Class CSV

Everything is already included in `shema.sql`:
- full class students
- teachers
- modules with teacher assignment
- hashed passwords

For delivery and teacher evaluation, import `shema.sql` only.

### Test 1: Admin Login
1. Go to `http://localhost/P01imen/auth/login_admin.php`
2. Enter username: `admin`
3. Enter password: `admin123`
4. You should be redirected to `/admin/dashboard.php`

### Test 2: Teacher Login
1. Go to `http://localhost/P01imen/auth/login_teacher.php`
2. Enter email: `amina.gheffar@gmail.com`
3. Enter password: `gheffaramina123`
4. You should be redirected to `/teacher/dashboard.php`

### Test 3: Student Login
1. Go to `http://localhost/P01imen/auth/login_student.php`
2. Enter email or student number: `zighedimen921@gmail.com` (or `232335330411`)
3. Enter password: `zighedimen123`
4. You should be redirected to `/student/dashboard.php`

### Test 4: Role Selection
1. Go to `http://localhost/P01imen/auth/login.php`
2. Click on any role card
3. You should be taken to the appropriate login page

## 🔐 Security Features

✅ Password hashing with BCRYPT
✅ Session regeneration on login
✅ CSRF protection through form tokens (if implemented)
✅ Input validation and sanitization
✅ SQL injection prevention with prepared statements
✅ XSS prevention with htmlspecialchars()
✅ Role-based access control (RBAC)

## 📝 Files Modified/Created

### New Files
- `/auth/login_admin.php` - Admin login page
- `/auth/login_teacher.php` - Teacher login page
- `/auth/login_student.php` - Student login page

### Modified Files
- `/shema.sql` - Updated to use consistent `password_hash` column
- `/create_user.php` - Updated to use `password_hash` column
- `/auth/login.php` - Converted to role selection landing page
- `/auth/session.php` - Enhanced session validation
- `/index.php` - Improved role-based redirects
- `/includes/auth.php` - Fixed password column references
- `/admin/add_teacher.php` - Fixed to use `password_hash` column
- `/teacher/change_password.php` - Fixed to use `password_hash` column

## ⚠️ Important Notes

1. **Database Must Be Created First**: Run the schema file before testing
2. **Teacher Setup Uses Only shema.sql**: Import `shema.sql` to get all required data
3. **Password Hashing**: All new passwords must be hashed before insertion
4. **Column Names**: Ensure all tables use `password_hash` consistently
5. **Session Storage**: Sessions use PHP default (file-based)

## 🆘 Troubleshooting

### "Teacher sees no students"
- Cause: the logged-in teacher has no module assigned.
- Fix: re-import `shema.sql` (module assignment is already included).

### "Database connection failed"
- Check `/config/db.php` configuration
- Verify MySQL is running
- Ensure database exists

### "Email or password incorrect" on login
- Verify database has been populated with test users
- Check that you're using the correct credentials
- Ensure password hashing is consistent

### "Access Denied" to dashboard
- Check that session is properly set
- Verify user role is stored in session
- Clear browser cookies and try again

### "Cannot modify header information"
- Check for output before header() calls
- Verify no whitespace before opening PHP tags

## 📧 Support

For more information about the system architecture or database structure, refer to the relevant source files in the project directory.
