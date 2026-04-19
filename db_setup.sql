-- ============================================================
-- Lab 5 | Group 6— Database Setup Script
-- ============================================================
-- HOW TO USE:
--   Option A: phpMyAdmin → Import tab → Select this file → Go
--   Option B: MySQL CLI:
--             mysql -u root -p < db_setup.sql
-- ============================================================

-- Create and select database
CREATE DATABASE IF NOT EXISTS lab5;
USE lab5;

-- Drop table if it already exists (clean slate)
DROP TABLE IF EXISTS users;

-- Create users table
-- NOTE: password column is VARCHAR(255) to hold both
--       plaintext (lab demo) and bcrypt hashes (secure app)
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Insert test users (plaintext — required for vulnerable_app)
INSERT INTO users (username, password) VALUES ('user1',  'pass1');
INSERT INTO users (username, password) VALUES ('admin',  'admin123');

-- Verify
SELECT * FROM users;
-- Expected output:
-- +----+----------+-----------+
-- | id | username | password  |
-- +----+----------+-----------+
-- |  1 | user1    | pass1     |
-- |  2 | admin    | admin123  |
-- +----+----------+-----------+
