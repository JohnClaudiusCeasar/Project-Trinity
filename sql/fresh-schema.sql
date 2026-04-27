-- ============================================================================
-- Fresh Schema Refresh Script
-- Purpose: Drop and recreate tables with correct schema
-- Preserves: users table only
-- ============================================================================

USE trinity_db;

-- ============================================================================
-- STEP 1: Drop junction tables first (due to foreign key dependencies)
-- ============================================================================

DROP TABLE IF EXISTS story_character;
DROP TABLE IF EXISTS story_world;
DROP TABLE IF EXISTS story_equipment;
DROP TABLE IF EXISTS character_world;
DROP TABLE IF EXISTS equipment_story;
DROP TABLE IF EXISTS equipment_character;
DROP TABLE IF EXISTS equipment_world;
DROP TABLE IF EXISTS character_equipment;
DROP TABLE IF EXISTS faction_type;
DROP TABLE IF EXISTS faction_world;
DROP TABLE IF EXISTS faction_founder;
DROP TABLE IF EXISTS faction_character;
DROP TABLE IF EXISTS faction_equipment;

-- ============================================================================
-- STEP 2: Drop main data tables
-- ============================================================================

DROP TABLE IF EXISTS stories;
DROP TABLE IF EXISTS characters;
DROP TABLE IF EXISTS worlds;
DROP TABLE IF EXISTS equipment;

-- ============================================================================
-- STEP 3: Truncate and reset lookup tables (preserve seeds)
-- ============================================================================

TRUNCATE TABLE factions;
DROP TABLE IF EXISTS factions;
DROP TABLE IF EXISTS world_types;
DROP TABLE IF EXISTS equipment_types;
DROP TABLE IF EXISTS character_types;
DROP TABLE IF EXISTS story_types;

-- ============================================================================
-- STEP 4: Recreate worlds table (with type_id)
-- ============================================================================

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

-- ============================================================================
-- STEP 5: Recreate equipment table
-- ============================================================================

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
-- STEP 6: Recreate characters table
-- ============================================================================

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
-- STEP 7: Recreate lookup tables
-- ============================================================================

CREATE TABLE IF NOT EXISTS world_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipment_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS character_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS story_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS faction_types (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS factions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    economic_status VARCHAR(100) NULL,
    social_status VARCHAR(100) NULL,
    history TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Faction Types (Many-to-Many, max 2 per faction enforced in app)
CREATE TABLE IF NOT EXISTS faction_type (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faction_id INT UNSIGNED NOT NULL,
    type_id TINYINT UNSIGNED NOT NULL,
    UNIQUE KEY unique_faction_type (faction_id, type_id),
    FOREIGN KEY (faction_id) REFERENCES factions (id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES faction_types (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Faction Locations (Many-to-Many)
CREATE TABLE IF NOT EXISTS faction_world (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faction_id INT UNSIGNED NOT NULL,
    world_id INT UNSIGNED NOT NULL,
    UNIQUE KEY unique_faction_world (faction_id, world_id),
    FOREIGN KEY (faction_id) REFERENCES factions (id) ON DELETE CASCADE,
    FOREIGN KEY (world_id) REFERENCES worlds (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Faction Founders (Many-to-Many)
CREATE TABLE IF NOT EXISTS faction_founder (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faction_id INT UNSIGNED NOT NULL,
    character_id INT UNSIGNED NOT NULL,
    UNIQUE KEY unique_faction_founder (faction_id, character_id),
    FOREIGN KEY (faction_id) REFERENCES factions (id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Faction Characters (Leaders + Members combined, with role enum)
CREATE TABLE IF NOT EXISTS faction_character (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faction_id INT UNSIGNED NOT NULL,
    character_id INT UNSIGNED NOT NULL,
    role ENUM('primary_leader', 'secondary_leader', 'member') DEFAULT 'member',
    FOREIGN KEY (faction_id) REFERENCES factions (id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Faction Equipment/Treasures
CREATE TABLE IF NOT EXISTS faction_equipment (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faction_id INT UNSIGNED NOT NULL,
    equipment_id INT UNSIGNED NOT NULL,
    treasure_type ENUM('sacred', 'secret', 'other') DEFAULT 'other',
    FOREIGN KEY (faction_id) REFERENCES factions (id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- STEP 8: Recreate stories table
-- ============================================================================

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

-- ============================================================================
-- STEP 9: Recreate junction tables
-- ============================================================================

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

CREATE TABLE IF NOT EXISTS character_equipment (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_id INT UNSIGNED NOT NULL,
    equipment_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_char_equipment (character_id, equipment_id),
    FOREIGN KEY (character_id) REFERENCES characters (id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipment_world (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    equipment_id INT UNSIGNED NOT NULL,
    world_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_equip_world (equipment_id, world_id),
    FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE,
    FOREIGN KEY (world_id) REFERENCES worlds (id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
-- STEP 11: Insert lookup seed data (for dropdowns)
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

-- Insert faction types
INSERT IGNORE INTO faction_types (name) VALUES
('Political'),
('Adventurer'),
('Academic'),
('Freedom'),
('Mercenary'),
('Religious/Cult'),
('Secret/Spy'),
('Corporations'),
('Mafia'),
('Knight/Templar'),
('Industrial'),
('Black'),
('Magical Council'),
('Entertainment');

INSERT IGNORE INTO factions (name, description) VALUES
('The Veil Accord', 'A secretive organization that maintains balance between realms'),
('Order of the Ashen Mark', 'Ancient warriors dedicated to protecting the forgotten'),
('The Hollow Collective', 'A network of information brokers and shadow operatives'),
('Freelance / Independent', 'Operates without formal allegiance'),
('Unaffiliated', 'No known faction ties');

-- ============================================================================
-- Refresh complete
-- ============================================================================