-- ============================================
-- SCHEMA BDD FC CHICHE - REFONTE
-- Prefix: pprod_
-- PHP 8.1 / MySQL
-- ============================================

-- Table principale du club
CREATE TABLE IF NOT EXISTS pprod_club (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cl_no INT UNSIGNED UNIQUE NOT NULL,
    affiliation_number INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    colors VARCHAR(100),
    address1 VARCHAR(255),
    address2 VARCHAR(255),
    address3 VARCHAR(255),
    postal_code VARCHAR(10),
    distributor_office VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    logo_url TEXT,
    district_name VARCHAR(255),
    district_cg_no INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cl_no (cl_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Terrains
CREATE TABLE IF NOT EXISTS pprod_terrains (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    te_no BIGINT UNSIGNED UNIQUE NOT NULL,
    club_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    zip_code VARCHAR(10),
    city VARCHAR(255),
    address TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    surface_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES pprod_club(id) ON DELETE CASCADE,
    INDEX idx_te_no (te_no),
    INDEX idx_club_id (club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membres du club
CREATE TABLE IF NOT EXISTS pprod_membres (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    club_id INT UNSIGNED NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    titre VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES pprod_club(id) ON DELETE CASCADE,
    INDEX idx_club_id (club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compétitions
CREATE TABLE IF NOT EXISTS pprod_competitions (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cp_no INT UNSIGNED UNIQUE NOT NULL,
    season INT UNSIGNED NOT NULL,
    type VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    level VARCHAR(10),
    cdg_cg_no INT UNSIGNED,
    cdg_name VARCHAR(255),
    external_updated_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cp_no (cp_no),
    INDEX idx_season (season),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Équipes
CREATE TABLE IF NOT EXISTS pprod_equipes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    club_id INT UNSIGNED NOT NULL,
    category_code VARCHAR(10) NOT NULL,
    number TINYINT UNSIGNED NOT NULL,
    code TINYINT UNSIGNED NOT NULL,
    short_name VARCHAR(255) NOT NULL,
    type VARCHAR(5) NOT NULL,
    season INT UNSIGNED NOT NULL,
    category_label VARCHAR(100),
    category_gender CHAR(1),
    diffusable TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES pprod_club(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team (club_id, category_code, number, season),
    INDEX idx_club_season (club_id, season),
    INDEX idx_category (category_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Engagements des équipes (pivot équipe-compétition)
CREATE TABLE IF NOT EXISTS pprod_engagements (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    equipe_id INT UNSIGNED NOT NULL,
    competition_id INT UNSIGNED NOT NULL,
    terrain_id INT UNSIGNED,
    statut VARCHAR(5),
    forfait_general CHAR(1) DEFAULT 'N',
    tour_no TINYINT UNSIGNED,
    elimine CHAR(1) DEFAULT 'N',
    phase_number TINYINT UNSIGNED,
    poule_stage_number TINYINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipe_id) REFERENCES pprod_equipes(id) ON DELETE CASCADE,
    FOREIGN KEY (competition_id) REFERENCES pprod_competitions(id) ON DELETE CASCADE,
    FOREIGN KEY (terrain_id) REFERENCES pprod_terrains(id) ON DELETE SET NULL,
    UNIQUE KEY unique_engagement (equipe_id, competition_id),
    INDEX idx_equipe (equipe_id),
    INDEX idx_competition (competition_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Matchs (calendrier + résultats)
CREATE TABLE IF NOT EXISTS pprod_matchs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    ma_no BIGINT UNSIGNED UNIQUE NOT NULL,
    competition_id INT UNSIGNED NOT NULL,
    terrain_id INT UNSIGNED,
    season INT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    time VARCHAR(10),
    initial_date DATE,
    
    -- Phase et poule
    phase_number TINYINT UNSIGNED,
    phase_type VARCHAR(10),
    phase_name VARCHAR(255),
    poule_stage_number TINYINT UNSIGNED,
    poule_name VARCHAR(255),
    poule_journee_number TINYINT UNSIGNED,
    
    -- Équipe domicile
    home_club_id INT UNSIGNED,
    home_team_category VARCHAR(10),
    home_team_number TINYINT UNSIGNED,
    home_team_name VARCHAR(255),
    home_score TINYINT UNSIGNED,
    home_is_forfeit CHAR(1) DEFAULT 'N',
    
    -- Équipe extérieur
    away_club_id INT UNSIGNED,
    away_team_category VARCHAR(10),
    away_team_number TINYINT UNSIGNED,
    away_team_name VARCHAR(255),
    away_score TINYINT UNSIGNED,
    away_is_forfeit CHAR(1) DEFAULT 'N',
    
    -- Statut du match
    status VARCHAR(5) NOT NULL,
    status_label VARCHAR(50),
    is_overtime CHAR(1) DEFAULT 'N',
    seems_postponed VARCHAR(10),
    
    -- Type de résultat (calendrier vs résultat)
    is_result TINYINT(1) DEFAULT 0,
    
    external_updated_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (competition_id) REFERENCES pprod_competitions(id) ON DELETE CASCADE,
    FOREIGN KEY (terrain_id) REFERENCES pprod_terrains(id) ON DELETE SET NULL,
    INDEX idx_ma_no (ma_no),
    INDEX idx_date (date),
    INDEX idx_season (season),
    INDEX idx_competition (competition_id),
    INDEX idx_home_club (home_club_id),
    INDEX idx_away_club (away_club_id),
    INDEX idx_is_result (is_result)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logs de synchronisation
CREATE TABLE IF NOT EXISTS pprod_sync_logs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    endpoint VARCHAR(255) NOT NULL,
    status ENUM('success', 'error', 'warning') NOT NULL,
    message TEXT,
    records_processed INT UNSIGNED DEFAULT 0,
    execution_time_ms INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_endpoint (endpoint),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de configuration système
CREATE TABLE IF NOT EXISTS pprod_config (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion configuration initiale
INSERT INTO pprod_config (config_key, config_value) VALUES
('last_sync_club', NULL),
('last_sync_equipes', NULL),
('last_sync_calendrier', NULL),
('last_sync_resultats', NULL),
('current_season', '2025')
ON DUPLICATE KEY UPDATE config_key = config_key;