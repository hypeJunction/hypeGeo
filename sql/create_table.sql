CREATE TABLE IF NOT EXISTS `prefix_entity_geometry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_guid` bigint(20) unsigned NOT NULL,
  `geometry` GEOMETRY NOT NULL SRID 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity_guid` (`entity_guid`),
  SPATIAL KEY `geometry` (`geometry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
