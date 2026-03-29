-- ============================================================================
-- Trinity Archives Database Schema
-- Complete schema with proper foreign key data type alignment
-- ============================================================================

select database();

-- Create the database
CREATE DATABASE IF NOT EXISTS trinity_db;

USE trinity_db;

SHOW TABLE STATUS WHERE Name IN ('users', 'world_types', 'equipment_types');

-- ============================================================================
-- TIER 1: INDEPENDENT TABLES (no foreign key dependencies)
-- ============================================================================

-- Drop the users table (this will delete any data!)
DROP TABLE IF EXISTS users;

-- Recreate with INT UNSIGNED
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(200) NOT NULL UNIQUE,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(200) NOT NULL,
    creator_id VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: Create an index on creator_id for faster lookups
CREATE INDEX IF NOT EXISTS idx_creator_id ON users(creator_id);

-- Create world_types lookup table
CREATE TABLE IF NOT EXISTS world_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Create equipment_types lookup table
CREATE TABLE IF NOT EXISTS equipment_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- ============================================================================
-- TIER 2: FIRST-LEVEL DEPENDENT TABLES (depend on users + type tables)
-- ============================================================================

-- Create worlds table
CREATE TABLE IF NOT EXISTS worlds (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type_id TINYINT UNSIGNED NULL,
    description TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (type_id) REFERENCES world_types (id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Create equipment table
CREATE TABLE IF NOT EXISTS equipment (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type_id TINYINT UNSIGNED NULL,
    description TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (type_id) REFERENCES equipment_types (id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================================
-- TIER 3: SECOND-LEVEL DEPENDENT TABLES (depend on users)
-- ============================================================================

-- Create characters table
CREATE TABLE IF NOT EXISTS characters (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    nickname VARCHAR(100) NULL,
    age VARCHAR(50) NULL,
    gender VARCHAR(50) NULL,
    faction VARCHAR(100) NULL,
    appearance TEXT NULL,
    abilities TEXT NULL,
    bio TEXT NULL,
    tags VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================================
-- TIER 4: JOIN TABLES (many-to-many relationships)
-- ============================================================================

-- Create character_world junction table (with relation metadata)
CREATE TABLE IF NOT EXISTS character_world (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_id INT UNSIGNED NOT NULL,
    world_id INT UNSIGNED NOT NULL,
    role VARCHAR(100) NULL,
    connection TEXT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_char_world (character_id, world_id),
    FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE,
    FOREIGN KEY (world_id) REFERENCES worlds (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create character_equipment junction table
CREATE TABLE IF NOT EXISTS character_equipment (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_id INT UNSIGNED NOT NULL,
    equipment_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_char_equipment (character_id, equipment_id),
    FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- SEED DATA: Insert default types
-- ============================================================================

-- Insert default world types
INSERT IGNORE INTO world_types (name) VALUES
('Fantasy'),
('Sci-Fi'),
('Modern'),
('Historical'),
('Other');

-- Insert default equipment types
INSERT IGNORE INTO equipment_types (name) VALUES
('Weapon'),
('Armor'),
('Accessory'),
('Tool'),
('Consumable'),
('Other');

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
