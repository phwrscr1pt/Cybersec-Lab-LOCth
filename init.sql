CREATE DATABASE IF NOT EXISTS school_lab;
USE school_lab;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'student', 'teacher') NOT NULL,
    student_sid VARCHAR(10) NULL
);

CREATE TABLE students (
    student_id VARCHAR(10) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    class VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL
);

CREATE TABLE teachers (
    teacher_id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL
);

CREATE TABLE courses (
    course_id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    teacher_id VARCHAR(10),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id)
);

CREATE TABLE flags (
    flag_name VARCHAR(50) PRIMARY KEY,
    flag_value VARCHAR(100) NOT NULL
);

-- Insert admin user
INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'admin');

-- Insert student users
INSERT INTO users (username, password, role, student_sid) VALUES 
('student0001', 'pass1', 'student', '0001'),
('student0002', 'pass2', 'student', '0002'),
('student0003', 'pass3', 'student', '0003'),
('student0004', 'pass4', 'student', '0004'),
('student0005', 'pass5', 'student', '0005');

-- Insert students (0001-0100)
INSERT INTO students VALUES
('0001', 'John', 'Smith', 'CS-101', 'john.smith@school.edu'),
('0002', 'Sarah', 'Johnson', 'CS-101', 'sarah.johnson@school.edu'),
('0003', 'Michael', 'Brown', 'CS-102', 'michael.brown@school.edu'),
('0004', 'Emily', 'Davis', 'CS-102', 'emily.davis@school.edu'),
('0005', 'David', 'Wilson', 'CS-103', 'david.wilson@school.edu'),
('0006', 'Lisa', 'Anderson', 'CS-103', 'lisa.anderson@school.edu'),
('0007', 'James', 'Taylor', 'CS-104', 'james.taylor@school.edu'),
('0008', 'Jennifer', 'Thomas', 'CS-104', 'jennifer.thomas@school.edu'),
('0009', 'Robert', 'Jackson', 'CS-105', 'robert.jackson@school.edu'),
('0010', 'Amanda', 'White', 'CS-105', 'amanda.white@school.edu');

-- Insert more students (abbreviated for space)
INSERT INTO students (student_id, first_name, last_name, class, email) 
SELECT 
    LPAD(11 + n, 4, '0'),
    CONCAT('Student', 11 + n),
    CONCAT('Last', 11 + n),
    CONCAT('CS-', 100 + (n % 10)),
    CONCAT('student', 11 + n, '@school.edu')
FROM (
    SELECT a.N + b.N * 10 + c.N * 100 as n
    FROM 
    (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,
    (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b,
    (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) c
) numbers
WHERE 11 + n <= 100;

-- Insert teachers
INSERT INTO teachers VALUES
('T001', 'Dr. Alan Turing', 'Computer Science'),
('T002', 'Prof. Ada Lovelace', 'Mathematics'),
('T003', 'Dr. Grace Hopper', 'Computer Science'),
('T004', 'Prof. Tim Berners-Lee', 'Web Technologies'),
('T005', 'Dr. Vint Cerf', 'Networking'),
('T006', 'Prof. Barbara Liskov', 'Software Engineering');

-- Insert courses
INSERT INTO courses VALUES
('0001', 'Introduction to Programming', 'T001'),
('0002', 'Data Structures', 'T003'),
('0003', 'Web Development', 'T004'),
('0004', 'Network Security', 'T005'),
('0005', 'Software Engineering', 'T006'),
('0087', 'Advanced Cybersecurity Lab', 'T005'),
('0099', 'Database Systems', 'T002');

-- Insert flags
INSERT INTO flags VALUES
('admin_page', 'FLAG{ADMIN_ACCESS_GRANTED_VIA_OTP}'),
('course_cookie', 'FLAG{HIDDEN_COOKIE_DISCOVERED}'),
('upload_shell', 'FLAG{FILE_UPLOAD_SHELL_EXECUTED}');

-- Grant privileges
GRANT ALL PRIVILEGES ON school_lab.* TO 'labuser'@'%';
FLUSH PRIVILEGES;
