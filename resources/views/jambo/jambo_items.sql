-- Jambo PA Production Suite SQL
CREATE TABLE IF NOT EXISTS `jambo_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(80) NOT NULL,
  `external_id` VARCHAR(80) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `content` LONGTEXT NULL,
  `category` VARCHAR(120) DEFAULT NULL,
  `amount` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `meta_kind` VARCHAR(80) DEFAULT NULL,
  `meta_json` LONGTEXT NULL,
  `fingerprint` CHAR(40) NOT NULL,
  `source` VARCHAR(40) NOT NULL DEFAULT 'dashboard',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_jambo_items_fingerprint` (`fingerprint`),
  KEY `idx_jambo_items_module` (`module`),
  KEY `idx_jambo_items_title` (`title`),
  KEY `idx_jambo_items_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jambo_sync_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(80) NOT NULL,
  `payload_count` INT NOT NULL DEFAULT 0,
  `saved_count` INT NOT NULL DEFAULT 0,
  `duplicate_count` INT NOT NULL DEFAULT 0,
  `request_ip` VARCHAR(80) DEFAULT NULL,
  `meta_json` LONGTEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_jambo_sync_logs_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jambo_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(120) NOT NULL,
  `setting_value` LONGTEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_jambo_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
