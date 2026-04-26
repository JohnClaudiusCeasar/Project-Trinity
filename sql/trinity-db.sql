-- ============================================================================
-- Trinity Archives Database Schema
-- Complete schema with proper foreign key data type alignment
-- ============================================================================

SELECT database();

-- Create the database
CREATE DATABASE IF NOT EXISTS trinity_db;

USE trinity_db;
-- ============================================================================
-- INDEPENDENT TABLES (no foreign key dependencies)
-- ============================================================================

-- Recreate with INT UNSIGNED
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(200) NOT NULL UNIQUE,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(200) NOT NULL,
    creator_id VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Optional: Create an index on creator_id for faster lookups
CREATE INDEX IF NOT EXISTS idx_creator_id ON users(creator_id);

-- Create world_types lookup table
CREATE TABLE IF NOT EXISTS world_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Create equipment_types lookup table
CREATE TABLE IF NOT EXISTS equipment_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Create character_types lookup table
CREATE TABLE IF NOT EXISTS character_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================================
-- TIER 2: FIRST-LEVEL DEPENDENT TABLES (depend on users + type tables)
-- ============================================================================

-- Create worlds table
CREATE TABLE IF NOT EXISTS worlds (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type_id TINYINT UNSIGNED NULL,
    description TEXT NULL,
    location VARCHAR(100) NULL,
    era VARCHAR(100) NULL,
    government VARCHAR(100) NULL,
    population INT UNSIGNED NULL,
    language VARCHAR(100) NULL,
    religion VARCHAR(100) NULL,
    currency VARCHAR(100) NULL,
    image VARCHAR(255) NULL,
    tags VARCHAR(255) NULL,
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
    age VARCHAR(50) NULL,
    description TEXT NULL,
    status ENUM('active','inactive','unused','destroyed') DEFAULT 'unused',
    appearance TEXT NULL,
    features VARCHAR(255) NULL,
    abilities TEXT NULL,
    image VARCHAR(255) NULL,
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
    type_id TINYINT UNSIGNED NULL,
    nickname VARCHAR(100) NULL,
    age VARCHAR(50) NULL,
    gender VARCHAR(50) NULL,
    faction VARCHAR(100) NULL,
    appearance TEXT NULL,
    abilities TEXT NULL,
    bio TEXT NULL,
    image VARCHAR(255) NULL,
    tags VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (type_id) REFERENCES character_types (id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================================
-- ADD FACTIONS LOOKUP TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS factions (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

-- Create equipment_world junction table
CREATE TABLE IF NOT EXISTS equipment_world (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    equipment_id INT UNSIGNED NOT NULL,
    world_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_equip_world (equipment_id, world_id),
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE,
    FOREIGN KEY (world_id) REFERENCES worlds (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create equipment_character junction table (current/previous owners)
CREATE TABLE IF NOT EXISTS equipment_character (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    equipment_id INT UNSIGNED NOT NULL,
    character_id INT UNSIGNED NOT NULL,
    ownership_type ENUM('current','previous') NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_equip_char (equipment_id, character_id, ownership_type),
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create equipment_story junction table (origins)
CREATE TABLE IF NOT EXISTS equipment_story (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    equipment_id INT UNSIGNED NOT NULL,
    story_id INT UNSIGNED NOT NULL,
    role VARCHAR(100) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_equip_story (equipment_id, story_id),
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE,
    FOREIGN KEY (story_id) REFERENCES stories (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create story_equipment junction table
CREATE TABLE IF NOT EXISTS story_equipment (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    story_id INT UNSIGNED NOT NULL,
    equipment_id INT UNSIGNED NOT NULL,
    role VARCHAR(100) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_story_equipment (story_id, equipment_id),
    FOREIGN KEY (story_id) REFERENCES stories (id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- SEED DATA: Insert default types
-- ============================================================================

-- Insert default world types (Complete replacement)
INSERT IGNORE INTO world_types (name) VALUES
('Multiverse'),
('Universe'),
('Galaxy'),
('Star System'),
('Planet'),
('Continent'),
('Metropolis'),
('City'),
('Town'),
('Village/Tribe');

-- Insert default equipment types
INSERT IGNORE INTO equipment_types (name) VALUES
('Weapon'),
('Armor'),
('Accessory'),
('Tool'),
('Consumable'),
('Artifact'),
('Vehicle'),
('Other');

-- Insert default character types
INSERT IGNORE INTO character_types (name) VALUES
('Protagonist'),
('Deuteragonist'),
('Antagonist'),
('Tritagonist'),
('Contagonist'),
('Others');

-- Insert default story types
INSERT IGNORE INTO story_types (name) VALUES
('Chapter/Episode'),
('Origin Story'),
('Filler');

-- Insert Factions
INSERT IGNORE INTO factions (name, description) VALUES
('The Veil Accord', 'A secretive organization that maintains balance between realms'),
('Order of the Ashen Mark', 'Ancient warriors dedicated to protecting the forgotten'),
('The Hollow Collective', 'A network of information brokers and shadow operatives'),
('Freelance / Independent', 'Operates without formal allegiance'),
('Unaffiliated', 'No known faction ties');

-- ============================================================================
-- STORIES TABLE
-- ============================================================================

-- Create story_types lookup table
CREATE TABLE IF NOT EXISTS story_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    type_id TINYINT UNSIGNED NULL,
    genre VARCHAR(100) NULL,
    synopsis TEXT NULL,
    status ENUM('wip','finished','cancelled') DEFAULT 'wip',
    entry_content LONGTEXT NULL,
    author VARCHAR(100) NULL,
    word_count INT UNSIGNED DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (type_id) REFERENCES story_types (id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Story relations junction tables
CREATE TABLE IF NOT EXISTS story_character (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    story_id INT UNSIGNED NOT NULL,
    character_id INT UNSIGNED NOT NULL,
    role VARCHAR(100) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_story_character (story_id, character_id),
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS story_world (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    story_id INT UNSIGNED NOT NULL,
    world_id INT UNSIGNED NOT NULL,
    role VARCHAR(100) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_story_world (story_id, world_id),
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (world_id) REFERENCES worlds(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================