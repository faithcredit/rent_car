CREATE TABLE IF NOT EXISTS `#__vikrentcar_cronjobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cron_name` varchar(128) NOT NULL,
  `class_file` varchar(128) NOT NULL,
  `params` text DEFAULT NULL,
  `last_exec` int(11) DEFAULT NULL,
  `logs` text DEFAULT NULL,
  `flag_int` int(11) NOT NULL DEFAULT 0,
  `flag_char` varchar(512) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `#__vikrentcar_seasons` ADD COLUMN `promominlos` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_seasons` ADD COLUMN `promolastmin` int(10) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_seasons` ADD COLUMN `promofinalprice` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `tot_taxes` decimal(12,2) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `car_cost` decimal(12,2) DEFAULT NULL;

INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('cronkey', FLOOR(1000 + (RAND() * 9000)));