-- SQL script to manually add missing columns to admin_access_logs table
-- Run this in your MySQL database (phpMyAdmin, MySQL Workbench, or command line)

-- First, check if columns exist before adding them
-- You can skip any ALTER statements that return "Duplicate column name" error

-- Add status column (for tracking success/failed logins)
ALTER TABLE `admin_access_logs` 
ADD COLUMN `status` VARCHAR(255) DEFAULT 'success' AFTER `role`;

-- Add username_attempted column (for tracking failed login attempts)
ALTER TABLE `admin_access_logs` 
ADD COLUMN `username_attempted` VARCHAR(255) NULL AFTER `status`;

-- Add latitude column
ALTER TABLE `admin_access_logs` 
ADD COLUMN `latitude` DECIMAL(10,8) NULL AFTER `ip_address`;

-- Add longitude column
ALTER TABLE `admin_access_logs` 
ADD COLUMN `longitude` DECIMAL(11,8) NULL AFTER `latitude`;

-- Add location_details column
ALTER TABLE `admin_access_logs` 
ADD COLUMN `location_details` TEXT NULL AFTER `longitude`;

