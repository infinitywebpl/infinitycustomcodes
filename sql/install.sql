CREATE TABLE IF NOT EXISTS `PREFIX_infinitycustomcodes_code` (
  `id_infinitycustomcodes_code` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `widget_key` VARCHAR(64) NULL DEFAULT NULL,
  `code` LONGTEXT NOT NULL,
  `placement` VARCHAR(32) NOT NULL,
  `pages` VARCHAR(255) NOT NULL DEFAULT 'all',
  `once_per_order` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `position` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_infinitycustomcodes_code`),
  UNIQUE KEY `widget_key` (`widget_key`),
  KEY `active_placement_position` (`active`, `placement`, `position`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `PREFIX_infinitycustomcodes_fired` (
  `id_infinitycustomcodes_code` INT(10) UNSIGNED NOT NULL,
  `reference` VARCHAR(16) NOT NULL,
  `date_add` DATETIME NOT NULL,
  PRIMARY KEY (`id_infinitycustomcodes_code`, `reference`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8mb4;
