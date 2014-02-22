--CREATE TABLE IF NOT EXISTS `prefix_entity_geometry` (
--  `id` int(11) NOT NULL AUTO_INCREMENT,
--  `entity_guid` bigint(20) unsigned NOT NULL,
--  `geom` GEOMETRY NOT NULL,
--  PRIMARY KEY (`id`),
--  UNIQUE KEY `entity_guid` (`entity_guid`),
--  SPATIAL KEY `geometry` (`geometry`)
--) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `prefix_entity_geometry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_guid` bigint(20) unsigned NOT NULL,
  `geometry` GEOMETRY NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity_guid` (`entity_guid`),
  SPATIAL KEY `geometry` (`geometry`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;