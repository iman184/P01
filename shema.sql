CREATE DATABASE IF NOT EXISTS school_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE school_system;


-- -----------------------------------------------
-- TEACHERS
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS teachers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    last_name             VARCHAR(80)  NOT NULL,
    first_name            VARCHAR(80)  NOT NULL,
    email                 VARCHAR(120) NOT NULL UNIQUE,
    subject     VARCHAR(100),
    password_hash         VARCHAR(255) NOT NULL,
    must_change_password  TINYINT(1)   NOT NULL DEFAULT 1,
    is_active             TINYINT(1)   NOT NULL DEFAULT 1,
    created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------
-- MODULES
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS modules (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code          VARCHAR(20) NOT NULL UNIQUE,
    title       VARCHAR(150) NOT NULL,
    coefficient   INT NOT NULL DEFAULT 1,
    teacher_id  INT NOT NULL,        -- UNIQUE enforces one-to-one
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
        ON DELETE CASCADE
);
-- -----------------------------------------------
-- STUDENTS
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    last_name             VARCHAR(80)  NOT NULL,
    first_name            VARCHAR(80)  NOT NULL,
    email                 VARCHAR(120) NOT NULL UNIQUE,
    student_number        VARCHAR(20)  NOT NULL UNIQUE,
    birth_date            DATE,
    password_hash         VARCHAR(255) NOT NULL,
    must_change_password  TINYINT(1)   NOT NULL DEFAULT 1,
    is_active             TINYINT(1)   NOT NULL DEFAULT 1,
    created_at            TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ── Enrollments (student ↔ module) ─────────────────────
CREATE TABLE IF NOT EXISTS enrollments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    module_id       INT NOT NULL,
    academic_year   VARCHAR(10) NOT NULL DEFAULT '2025/2026',
    UNIQUE KEY uq_enrollment (student_id, module_id, academic_year),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id)   REFERENCES modules(id) ON DELETE CASCADE
);
-- -----------------------------------------------
-- NOTES  (junction table: student + module + grade)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS notes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  INT NOT NULL,
    module_id   INT NOT NULL,
    grade       DECIMAL(4,2) NOT NULL CHECK (grade >= 0 AND grade <= 20),
    academic_year VARCHAR(10) NOT NULL DEFAULT '2025/2026',
UNIQUE KEY uq_note (student_id, module_id, academic_year),
    FOREIGN KEY (student_id) REFERENCES students(id)
        ON DELETE CASCADE,
    FOREIGN KEY (module_id)  REFERENCES modules(id)
        ON DELETE CASCADE
);

-- -----------------------------------------------
-- ADMIN
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(80)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(50)  DEFAULT 'admin'
);