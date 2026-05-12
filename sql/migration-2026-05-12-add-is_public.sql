-- ============================================================================
-- Migration: Add is_public column to content tables for public community forum
-- ============================================================================

USE trinity_db;

ALTER TABLE worlds ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER tags;
ALTER TABLE equipment ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER image;
ALTER TABLE characters ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER tags;
ALTER TABLE stories ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER entry_content;
ALTER TABLE factions ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1 AFTER history;
