CREATE DATABASE IF NOT EXISTS university_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE university_system;


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
    profile_image         VARCHAR(255) DEFAULT NULL,
    student_number        VARCHAR(20)  NOT NULL UNIQUE,
    birth_date            DATE,
    password_hash         VARCHAR(255) NOT NULL,
    must_change_password  TINYINT(1)   NOT NULL DEFAULT 1,
    last_login            DATETIME DEFAULT NULL,
    last_activity         DATETIME DEFAULT NULL,
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

-- -----------------------------------------------
-- SEED DATA (kept in repository for portability)
-- -----------------------------------------------

INSERT INTO admin (username, password_hash, role)
VALUES ('admin', 'admin123', 'admin')
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    role = VALUES(role);

INSERT INTO teachers (first_name, last_name, email, subject, password_hash, must_change_password, is_active)
VALUES
    ('Amina', 'Gheffar', 'amina.gheffar@gmail.com', 'Mathematiques', 'gheffaramina123', 0, 1),
    ('Hamza', 'Abdellahoum', 'abdellahoumhamza89@gmail.com', 'Informatique', 'abdellahoumhamza123', 0, 1),
    ('Labde', 'Laachemi', 'labde79@gmail.com', 'Physique', 'laachemilabde123', 0, 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    subject = VALUES(subject),
    password_hash = VALUES(password_hash),
    must_change_password = VALUES(must_change_password),
    is_active = VALUES(is_active);

INSERT INTO students (first_name, last_name, email, student_number, birth_date, password_hash, must_change_password, is_active)
VALUES
    ('Imen', 'Zighed', 'zighedimen921@gmail.com', '232335330411', '2002-01-15', 'zighedimen123', 0, 1),
    ('Dekrah', 'Lakhal', 'dekrah.lakhal@gmail.com', '242431577219', '2002-03-18', 'lakhaldekrah123', 0, 1),
    ('Meriem', 'Ramoul', 'meriem.ramoul@gmail.com', '242431422801', '2002-06-22', 'ramoulmeriem123', 0, 1),
    ('Issam', 'Bearcia', 'issam.bearcia@gmail.com', '2323314125006', '2001-11-09', 'bearciaissam123', 0, 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    student_number = VALUES(student_number),
    birth_date = VALUES(birth_date),
    password_hash = VALUES(password_hash),
    must_change_password = VALUES(must_change_password),
    is_active = VALUES(is_active);