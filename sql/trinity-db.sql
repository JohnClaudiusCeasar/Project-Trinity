-- ============================================================================
-- Trinity Archives Database Schema
-- Complete schema with proper foreign key data type alignment
-- ============================================================================

select database();

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

-- ============================================================================
-- SEED USER: Insert default user (John-Trinity)
-- ============================================================================

-- Insert a test user (John-Trinity)
INSERT INTO users (username, email, password, creator_id) VALUES
('John-Trinity', 'john@trinity.com', '$2y$10$dummyhashfordevelopment123', 'JT001');

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

-- ============================================================================
-- SEED DATA: Insert default types
-- ============================================================================

-- Insert default world types
INSERT IGNORE INTO world_types (name) VALUES
('Fantasy'),
('Sci-Fi'),
('Modern'),
('Historical'),
('Post-Apocalyptic'),
('Cyberpunk'),
('Steampunk'),
('Horror'),
('Mystery'),
('Other');

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

-- Insert Factions
INSERT IGNORE INTO factions (name, description) VALUES
('The Veil Accord', 'A secretive organization that maintains balance between realms'),
('Order of the Ashen Mark', 'Ancient warriors dedicated to protecting the forgotten'),
('The Hollow Collective', 'A network of information brokers and shadow operatives'),
('Freelance / Independent', 'Operates without formal allegiance'),
('Unaffiliated', 'No known faction ties');

-- ============================================================================
-- SEED SAMPLE WORLDS 
-- ============================================================================
 
-- Note: created_by uses the existing user ID. Adjust if your user has a different ID.
 
INSERT INTO worlds (name, type_id, description, created_by) VALUES
('Aethermoor', 1, 'A floating archipelago where islands drift through perpetual twilight, connected by shimmering bridges of solidified starlight.', 1),
('Nexus Prime', 2, 'A megacity sprawling across three moons, where corporate towers pierce the atmosphere and neon lights never fade.', 1),
('The Fractured Realm', 1, 'A world shattered by an ancient cataclysm, its fragments held together by mysterious threads of magic.', 1),
('New Tokyo 2087', 6, 'A neon-soaked metropolis where augmented humans navigate corporate warfare and underground resistance.', 1),
('The Ashen Wastes', 5, 'Endless dunes of gray sand beneath a dying sun, dotted with the ruins of a civilization that forgot its name.', 1);

-- ============================================================================
-- SEED SAMPLE EQUIPMENT 
-- ============================================================================
 
INSERT INTO equipment (name, type_id, description, created_by) VALUES
('Voidblade', 1, 'A sword forged from crystallized shadow, capable of cutting through reality itself.', 1),
('Chrono Gauntlet', 3, 'A wrist-mounted device that allows brief manipulation of local time flow.', 1),
('Wanderer\'s Cloak', 2, 'An enchanted cloak that shifts its appearance to match any environment.', 1),
('Memory Shard', 6, 'A glowing crystal that stores and replays fragments of the past.', 1),
('Neural Interface Implant', 3, 'A cybernetic enhancement allowing direct connection to digital networks.', 1),
('Phoenix Elixir', 5, 'A rare potion that can revive the user from mortal wounds once.', 1),
('Grappling Hook Gun', 4, 'A compact launcher with retractable cables for urban traversal.', 1),
('The Codex of Whispers', 6, 'An ancient tome that writes itself, revealing secrets to those who listen.', 1);

-- ============================================================================
-- STORIES TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS stories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    genre VARCHAR(100) NULL,
    synopsis TEXT NULL,
    status ENUM('wip','finished','cancelled') DEFAULT 'wip',
    entry_content LONGTEXT NULL,
    author VARCHAR(100) NULL,
    word_count INT UNSIGNED DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
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

-- TODO: story_equipment relation (equipment/artifacts picker for stories)
-- Will be added when equipment picker supports story relations

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
