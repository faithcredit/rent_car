CREATE TABLE IF NOT EXISTS `#__vikrentcar_condtexts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `rules` text DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `lastupd` datetime DEFAULT NULL,
  `debug` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_cars_icals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcar` int(10) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `url` varchar(256) NOT NULL,
  `lastfetched` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `id_ical` int(10) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `idorder_ical` varchar(256) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_locfees` ADD COLUMN `any_oneway` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_places` CHANGE `wopening` `wopening` varchar(1024) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_customers` CHANGE `pin` `pin` int(10) NOT NULL DEFAULT 0;