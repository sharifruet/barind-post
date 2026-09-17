-- Barind Post baseline schema (MySQL 8 / utf8mb4).
-- Applied by app/Database/Migrations/2026-09-18-000000_baseline_schema.php.
-- Extracted from the former hand-maintained dbscript.sql on 2026-09-18; from now on
-- change the schema with NEW migration files, never by editing this file or dbscript.sql
-- (dbscript.sql is regenerated with `php spark schema:dump`).

CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    isSpecial BOOLEAN DEFAULT FALSE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS kickers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    text VARCHAR(255) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#dc3545',
    usage_count INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_kicker_text (text)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    lead_text TEXT,
    reporterRole VARCHAR(100) NULL,
    content TEXT NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    featured BOOLEAN NOT NULL DEFAULT FALSE,
    kicker_id INT UNSIGNED NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    published_at DATETIME NULL,
    image_url VARCHAR(255),
    image_caption TEXT,
    image_alt_text VARCHAR(255),
    slug VARCHAR(255),
    source VARCHAR(255),
    source_url VARCHAR(500) NULL,
    content_hash CHAR(64) NULL,
    suggested_image_url VARCHAR(500) NULL,
    dateline VARCHAR(255),
    word_count INT UNSIGNED,
    language VARCHAR(5) NOT NULL DEFAULT 'bn',
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (kicker_id) REFERENCES kickers(id) ON DELETE SET NULL,
    UNIQUE KEY uq_news_source_url (source_url),
    KEY idx_news_content_hash (content_hash)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news_tags (
    news_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (news_id, tag_id),
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_name VARCHAR(255) NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,
    caption TEXT NULL,
    alt_text VARCHAR(255) NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_created_at (created_at),
    UNIQUE KEY unique_image_path (image_path)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news_views (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    news_id INT UNSIGNED NOT NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    viewer_ip VARCHAR(45),
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    newsletter_subscribed BOOLEAN DEFAULT FALSE,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reporter_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_reporter_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    reporter_role_id INT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_role_id) REFERENCES reporter_roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, reporter_role_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cities (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    latitude DECIMAL(9, 6) NOT NULL,
    longitude DECIMAL(9, 6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prayer_times (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    city_id BIGINT NOT NULL,
    date DATE NOT NULL,
    fajr TIME NOT NULL,
    sunrise TIME NOT NULL,
    dhuhr TIME NOT NULL,
    asr TIME NOT NULL,
    maghrib TIME NOT NULL,
    isha TIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_city_date (city_id, date),
    INDEX idx_city_id (city_id),
    INDEX idx_date (date)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sports_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    custom_url VARCHAR(100) UNIQUE,
    title_bn VARCHAR(255) NOT NULL,
    title_en VARCHAR(255),
    sport_profile VARCHAR(50) DEFAULT 'football',
    description_bn TEXT,
    banner_image VARCHAR(500),
    logo_image VARCHAR(500),
    news_tag_slug VARCHAR(100),
    start_date DATE,
    end_date DATE,
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    show_in_nav BOOLEAN DEFAULT FALSE,
    show_homepage_widget BOOLEAN DEFAULT FALSE,
    config JSON,
    created_at DATETIME,
    updated_at DATETIME
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sports_participants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_bn VARCHAR(150) NOT NULL,
    name_en VARCHAR(150),
    short_code VARCHAR(10),
    flag_url VARCHAR(500),
    type ENUM('team', 'individual') DEFAULT 'team',
    created_at DATETIME,
    updated_at DATETIME
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sports_event_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    participant_id INT UNSIGNED NOT NULL,
    group_name VARCHAR(20),
    seed INT UNSIGNED,
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE KEY unique_event_participant (event_id, participant_id),
    FOREIGN KEY (event_id) REFERENCES sports_events(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES sports_participants(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sports_venues (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_bn VARCHAR(200) NOT NULL,
    name_en VARCHAR(200),
    city VARCHAR(100),
    country VARCHAR(100),
    capacity INT UNSIGNED,
    image_url VARCHAR(500),
    created_at DATETIME,
    updated_at DATETIME
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sports_matches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    participant_a_id INT UNSIGNED NOT NULL,
    participant_b_id INT UNSIGNED NOT NULL,
    venue_id INT UNSIGNED,
    kickoff_at DATETIME,
    stage VARCHAR(50) DEFAULT 'group',
    group_name VARCHAR(20),
    match_slug VARCHAR(200) NOT NULL,
    status VARCHAR(20) DEFAULT 'scheduled',
    summary_bn TEXT,
    news_id INT UNSIGNED,
    sport_data JSON,
    referee VARCHAR(150),
    attendance INT UNSIGNED,
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE KEY unique_event_match_slug (event_id, match_slug),
    FOREIGN KEY (event_id) REFERENCES sports_events(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_a_id) REFERENCES sports_participants(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_b_id) REFERENCES sports_participants(id) ON DELETE CASCADE,
    FOREIGN KEY (venue_id) REFERENCES sports_venues(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sports_match_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id INT UNSIGNED NOT NULL,
    event_minute VARCHAR(20),
    period VARCHAR(20),
    event_type VARCHAR(50) NOT NULL,
    participant_id INT UNSIGNED,
    player_name VARCHAR(150),
    detail VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (match_id) REFERENCES sports_matches(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES sports_participants(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
