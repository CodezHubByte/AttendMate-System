-- Reset SQL mode and timezone
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create the database
CREATE DATABASE IF NOT EXISTS attendmate DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE attendmate;

-- Drop tables if they already exist
DROP TABLE IF EXISTS remarks;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS users;

  -- Create USERS table
  CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'user',
    `course` VARCHAR(50),
    `semester` VARCHAR(50)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  -- Create ATTENDANCE table
  CREATE TABLE `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('Present', 'Absent') NOT NULL,
    `course` VARCHAR(50),
    `semester` VARCHAR(50),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ANNOUNCEMENTS Table
CREATE TABLE announcements (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  -- Create REMARKS table
  CREATE TABLE `remarks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `remark` TEXT NOT NULL,
    `course` VARCHAR(50),
    `semester` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  -- Insert USERS
  INSERT INTO `users` (`name`, `email`, `password`, `role`, `course`, `semester`) VALUES
  ('Aarav Sharma', 'aarav.0@example.com', 'pass123', 'user', 'MSc', 'Semester 3'),
  ('Ishika Verma', 'ishika.1@example.com', 'pass123', 'user', 'BCA', 'Semester 2'),
  ('Rohan Mehta', 'rohan.2@example.com', 'pass123', 'user', 'MSc', 'Semester 3'),
  ('Diya Reddy', 'diya.3@example.com', 'pass123', 'user', 'BCA', 'Semester 2'),
  ('Aditya Joshi', 'aditya.4@example.com', 'pass123', 'user', 'BCA', 'Semester 3'),
  ('Tanya Kapoor', 'tanya.5@example.com', 'pass123', 'user', 'MSc', 'Semester 4'),
  ('Sahil Singh', 'sahil.6@example.com', 'pass123', 'user', 'MSc', 'Semester 2'),
  ('Neha Jain', 'neha.7@example.com', 'pass123', 'user', 'BSc(IT)', 'Semester 6'),
  ('Kunal Rao', 'kunal.8@example.com', 'pass123', 'user', 'MSc(IT)', 'Semester 1'),
  ('Pooja Patel', 'pooja.9@example.com', 'pass123', 'user', 'MCA', 'Semester 6');

  -- Insert ATTENDANCE
  INSERT INTO `attendance` (`user_id`, `date`, `status`, `course`, `semester`) VALUES
  (2, '2025-07-03', 'Absent', 'BCA', 'Semester 4'),
  (3, '2025-07-04', 'Absent', 'MSc', 'Semester 6'),
  (4, '2025-07-05', 'Absent', 'MSc', 'Semester 5'),
  (5, '2025-07-06', 'Present', 'MCA', 'Semester 3'),
  (6, '2025-07-07', 'Absent', 'BSc', 'Semester 5'),
  (7, '2025-07-08', 'Absent', 'MSc', 'Semester 5'),
  (8, '2025-07-09', 'Absent', 'MSc', 'Semester 2'),
  (9, '2025-07-10', 'Present', 'MSc', 'Semester 5'),
  (10, '2025-07-11', 'Present', 'BSc(IT)', 'Semester 4'),
  (1, '2025-07-12', 'Present', 'BCA', 'Semester 5');

  -- Insert REMARKS
  INSERT INTO `remarks` (`student_id`, `course`, `semester`, `remark`) VALUES
  (2, 'MCA', 'Semester 5', 'Very active in workshops.'),
  (3, 'MCA', 'Semester 5', 'Regular and punctual.'),
  (4, 'BSc', 'Semester 3', 'Should engage more in class discussions.'),
  (5, 'MCA', 'Semester 5', 'Needs improvement in practicals.'),
  (6, 'BSc(IT)', 'Semester 1', 'Positive attitude and eager to learn.'),
  (7, 'MCA', 'Semester 6', 'Frequent absenteeism noticed.'),
  (8, 'BSc', 'Semester 1', 'Frequent absenteeism noticed.'),
  (9, 'MSc', 'Semester 4', 'Must improve project submission timelines.'),
  (10, 'MSc(IT)', 'Semester 6', 'Regular and punctual.'),
  (1, 'MSc', 'Semester 3', 'Shows leadership in team tasks.');

-- Sample announcements
INSERT INTO announcements (title, message) VALUES
('Welcome to AttendMate!', 'We are live. Track your attendance easily.'),
('Holiday Notice', 'College will remain closed on 15th August for Independence Day.'),
('Exam Reminder', 'Mid-term exams start next week. Please check the schedule.'),
('Project Submission', 'Final year students must submit projects by 25th July.'),
('New Course Added', 'Python for Data Science has been added to Semester 3.'),
('Workshop', 'AI Workshop will be conducted on campus on 20th July.'),
('ID Card Update', 'Students must collect new ID cards from the admin office.'),
('Cultural Fest', 'Registrations open for college fest events.'),
('Internal Marks', 'Internal assessment marks will be uploaded soon.'),
('Laptop Policy', 'New rules issued regarding personal laptops in classrooms.');

