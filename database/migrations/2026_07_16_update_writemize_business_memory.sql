CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER $$

CREATE PROCEDURE writemize_add_column_if_missing(
    IN table_name_in VARCHAR(64),
    IN column_name_in VARCHAR(64),
    IN column_definition_in TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = table_name_in
          AND COLUMN_NAME = column_name_in
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', table_name_in, '` ADD COLUMN `', column_name_in, '` ', column_definition_in);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

CALL writemize_add_column_if_missing('businesses', 'user_id', 'INT UNSIGNED NULL');
CALL writemize_add_column_if_missing('businesses', 'daily_posting_enabled', 'TINYINT(1) NOT NULL DEFAULT 1');
CALL writemize_add_column_if_missing('businesses', 'last_daily_run_date', 'DATE NULL');
CALL writemize_add_column_if_missing('businesses', 'scout_context', 'JSON NULL');
CALL writemize_add_column_if_missing('businesses', 'niche', 'VARCHAR(190) NULL');
CALL writemize_add_column_if_missing('businesses', 'tone', 'VARCHAR(190) NULL');
CALL writemize_add_column_if_missing('businesses', 'audience', 'VARCHAR(255) NULL');
CALL writemize_add_column_if_missing('businesses', 'content_strategy', 'TEXT NULL');
CALL writemize_add_column_if_missing('businesses', 'last_scouted_url', 'VARCHAR(2048) NULL');
CALL writemize_add_column_if_missing('businesses', 'last_scouted_at', 'DATETIME NULL');
CALL writemize_add_column_if_missing('blog_runs', 'logs', 'JSON NULL');
CALL writemize_add_column_if_missing('blog_posts', 'scheduled_for', 'DATETIME NULL');

DROP PROCEDURE writemize_add_column_if_missing;
