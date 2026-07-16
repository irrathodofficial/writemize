CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS businesses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    name VARCHAR(190) NOT NULL,
    website_url VARCHAR(2048) NOT NULL,
    publish_time TIME NULL,
    daily_posting_enabled TINYINT(1) NOT NULL DEFAULT 1,
    last_daily_run_date DATE NULL,
    scout_context JSON NULL,
    niche VARCHAR(190) NULL,
    tone VARCHAR(190) NULL,
    audience VARCHAR(255) NULL,
    content_strategy TEXT NULL,
    last_scouted_url VARCHAR(2048) NULL,
    last_scouted_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_runs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id INT UNSIGNED NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'queued',
    topic VARCHAR(255) NULL,
    focus_keyword VARCHAR(190) NULL,
    seo_score INT UNSIGNED NULL,
    image_url TEXT NULL,
    publish_url VARCHAR(2048) NULL,
    logs JSON NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX (business_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_id INT UNSIGNED NULL,
    business_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    meta_description VARCHAR(320) NOT NULL,
    focus_keyword VARCHAR(190) NOT NULL,
    html MEDIUMTEXT NOT NULL,
    image_url TEXT NULL,
    seo_score INT UNSIGNED NOT NULL DEFAULT 0,
    word_count INT UNSIGNED NOT NULL DEFAULT 0,
    reading_time VARCHAR(40) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    publish_url VARCHAR(2048) NULL,
    scheduled_for DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug (slug),
    INDEX (run_id),
    INDEX (business_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
