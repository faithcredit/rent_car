CREATE TABLE IF NOT EXISTS `#__vikrentcar_orderhistory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idorder` int(10) NOT NULL,
  `dt` datetime NOT NULL,
  `type` char(2) NOT NULL DEFAULT 'C',
  `descr` text DEFAULT NULL,
  `totpaid` decimal(12,2) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `data` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_critical_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` date DEFAULT NULL,
  `idcar` int(10) NOT NULL DEFAULT 0,
  `subunit` int(10) NOT NULL DEFAULT 0,
  `info` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `reg` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_gpayments` ADD COLUMN `outposition` varchar(16) NOT NULL DEFAULT 'top';
ALTER TABLE `#__vikrentcar_gpayments` ADD COLUMN `logo` varchar(128) DEFAULT NULL;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `paymcount` tinyint(2) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentcar_orders` ADD COLUMN `payable` decimal(12,2) DEFAULT 0.00;
ALTER TABLE `#__vikrentcar_customers` ADD COLUMN `docsfolder` varchar(256) DEFAULT NULL COMMENT 'a unique folder name that will be used for keeping the customer documents' AFTER `docimg`;
ALTER TABLE `#__vikrentcar_customers_orders` ADD COLUMN `drivers_data` text DEFAULT NULL;