-- WP SHORTCODES --

CREATE TABLE IF NOT EXISTS `#__vikrentcar_wpshortcodes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `createdon` DATETIME NOT NULL,
  `createdby` int(10) NOT NULL,
  `json` text NOT NULL,
  `type` varchar(48) NOT NULL,
  `title` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `lang` varchar(16) DEFAULT '*',
  `shortcode` varchar(512) NOT NULL,
  `post_id` int(10) unsigned DEFAULT 0,
  `tmp_post_id` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- END WP SHORTCODES --

CREATE TABLE IF NOT EXISTS `#__vikrentcar_busy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcar` int(10) NOT NULL,
  `ritiro` int(11) DEFAULT NULL,
  `consegna` int(11) DEFAULT NULL,
  `realback` int(11) DEFAULT NULL,
  `stop_sales` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_caratteristiche` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `icon` varchar(128) DEFAULT NULL,
  `align` varchar(64) DEFAULT NULL,
  `textimg` varchar(128) DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_cars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `img` varchar(128) DEFAULT NULL,
  `idcat` varchar(128) DEFAULT NULL,
  `idcarat` varchar(128) DEFAULT NULL,
  `idopt` varchar(128) DEFAULT NULL,
  `info` text DEFAULT NULL,
  `idplace` varchar(128) DEFAULT NULL,
  `avail` tinyint(1) NOT NULL DEFAULT 1,
  `units` int(10) NOT NULL DEFAULT 1,
  `idretplace` varchar(128) DEFAULT NULL,
  `moreimgs` varchar(512) DEFAULT NULL,
  `startfrom` decimal(12,2) DEFAULT NULL,
  `short_info` varchar(512) DEFAULT NULL,
  `params` text DEFAULT NULL,
  `alias` varchar(128) NOT NULL,
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

CREATE TABLE IF NOT EXISTS `#__vikrentcar_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT 'cat',
  `descr` text DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `#__vikrentcar_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `param` varchar(128) NOT NULL,
  `setting` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_name` char(64) DEFAULT NULL,
  `country_3_code` char(3) DEFAULT NULL,
  `country_2_code` char(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `percentot` tinyint(1) NOT NULL DEFAULT 1,
  `value` decimal(12,2) DEFAULT NULL,
  `datevalid` varchar(64) DEFAULT NULL,
  `allvehicles` tinyint(1) NOT NULL DEFAULT 1,
  `idcars` varchar(512) DEFAULT NULL,
  `mintotord` decimal(12,2) DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `#__vikrentcar_custfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'text',
  `choose` text DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `ordering` int(10) NOT NULL DEFAULT 1,
  `isemail` tinyint(1) NOT NULL DEFAULT 0,
  `poplink` varchar(256) DEFAULT NULL,
  `isnominative` tinyint(1) NOT NULL DEFAULT 0,
  `isphone` tinyint(1) NOT NULL DEFAULT 0,
  `flag` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `cfields` text DEFAULT NULL,
  `pin` int(10) NOT NULL DEFAULT 0,
  `ujid` int(5) NOT NULL DEFAULT 0,
  `address` varchar(256) DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL,
  `zip` varchar(16) DEFAULT NULL,
  `doctype` varchar(64) DEFAULT NULL,
  `docnum` varchar(128) DEFAULT NULL,
  `docimg` varchar(128) DEFAULT NULL,
  `docsfolder` varchar(256) DEFAULT NULL COMMENT 'a unique folder name that will be used for keeping the customer documents',
  `notes` text DEFAULT NULL,
  `company` varchar(128) DEFAULT NULL,
  `vat` varchar(64) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `bdate` varchar(16) DEFAULT NULL,
  `pbirth` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_customers_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcustomer` int(10) NOT NULL,
  `idorder` int(10) NOT NULL,
  `drivers_data` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `#__vikrentcar_dispcost` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcar` int(10) NOT NULL,
  `days` int(10) NOT NULL,
  `idprice` int(10) NOT NULL,
  `cost` decimal(12,2) DEFAULT NULL,
  `attrdata` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_dispcosthours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcar` int(10) NOT NULL,
  `hours` int(10) NOT NULL,
  `idprice` int(10) NOT NULL,
  `cost` decimal(12,2) DEFAULT NULL,
  `attrdata` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_gpayments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `file` varchar(64) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `charge` decimal(12,2) DEFAULT NULL,
  `setconfirmed` tinyint(1) NOT NULL DEFAULT 0,
  `shownotealw` tinyint(1) NOT NULL DEFAULT 0,
  `val_pcent` tinyint(1) NOT NULL DEFAULT 1,
  `ch_disc` tinyint(1) NOT NULL DEFAULT 1,
  `params` varchar(512) DEFAULT NULL,
  `ordering` int(5) NOT NULL DEFAULT 1,
  `outposition` varchar(16) NOT NULL DEFAULT 'top',
  `logo` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_hourscharges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcar` int(10) NOT NULL,
  `ehours` int(10) NOT NULL,
  `idprice` int(10) NOT NULL,
  `cost` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_iva` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `aliq` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_locfees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(10) NOT NULL,
  `to` int(10) NOT NULL,
  `daily` tinyint(1) NOT NULL DEFAULT 0,
  `cost` decimal(12,2) DEFAULT NULL,
  `idiva` int(10) DEFAULT NULL,
  `invert` tinyint(1) NOT NULL DEFAULT 0,
  `losoverride` varchar(512) DEFAULT NULL,
  `any_oneway` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_optionals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `descr` text,
  `cost` decimal(12,2) DEFAULT NULL,
  `perday` tinyint(1) NOT NULL DEFAULT 0,
  `hmany` tinyint(1) NOT NULL DEFAULT 1,
  `img` varchar(128) DEFAULT NULL,
  `idiva` int(10) DEFAULT NULL,
  `maxprice` decimal(12,2) DEFAULT NULL,
  `forcesel` tinyint(1) NOT NULL DEFAULT 0,
  `forceval` varchar(32) DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  `forceifdays` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idbusy` int(10) DEFAULT NULL,
  `custdata` text,
  `ts` int(11) DEFAULT NULL,
  `status` varchar(128) DEFAULT NULL,
  `idcar` int(10) DEFAULT NULL,
  `days` int(10) DEFAULT NULL,
  `ritiro` int(10) DEFAULT NULL,
  `consegna` int(10) DEFAULT NULL,
  `idtar` int(10) DEFAULT NULL,
  `optionals` varchar(128) DEFAULT NULL,
  `custmail` varchar(128) DEFAULT NULL,
  `sid` varchar(128) DEFAULT NULL,
  `idplace` int(10) DEFAULT NULL,
  `idreturnplace` int(10) DEFAULT NULL,
  `totpaid` decimal(12,2) DEFAULT NULL,
  `idpayment` varchar(128) DEFAULT NULL,
  `ujid` int(10) NOT NULL DEFAULT 0,
  `hourly` tinyint(1) NOT NULL DEFAULT 0,
  `coupon` varchar(128) DEFAULT NULL,
  `order_total` decimal(12,2) DEFAULT NULL,
  `locationvat` decimal(12,2) DEFAULT NULL,
  `paymentlog` text DEFAULT NULL,
  `lang` varchar(16) DEFAULT NULL,
  `country` varchar(5) DEFAULT NULL,
  `carindex` int(5) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `nominative` varchar(64) DEFAULT NULL,
  `adminnotes` text DEFAULT NULL,
  `cust_cost` decimal(12,2) DEFAULT NULL,
  `cust_idiva` int(10) DEFAULT NULL,
  `extracosts` varchar(2048) DEFAULT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT 0,
  `tot_taxes` decimal(12,2) DEFAULT NULL,
  `car_cost` decimal(12,2) DEFAULT NULL,
  `reg` tinyint(1) NOT NULL DEFAULT 0,
  `paymcount` tinyint(2) NOT NULL DEFAULT 0,
  `payable` decimal(12,2) DEFAULT 0.00,
  `id_ical` int(10) DEFAULT NULL,
  `idorder_ical` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

CREATE TABLE IF NOT EXISTS `#__vikrentcar_oohfees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oohname` varchar(128) NOT NULL DEFAULT 'Out of Hours',
  `pickcharge` decimal(12,2) DEFAULT NULL,
  `dropcharge` decimal(12,2) DEFAULT NULL,
  `maxcharge` decimal(12,2) DEFAULT NULL,
  `idcars` varchar(256) DEFAULT NULL,
  `from` int(6) NOT NULL DEFAULT 0,
  `to` int(6) NOT NULL DEFAULT 1,
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `idiva` int(10) DEFAULT NULL,
  `wdays` varchar(32) NOT NULL DEFAULT '-0-,-1-,-2-,-3-,-4-,-5-,-6-',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_oohfees_locxref` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idooh` int(10) DEFAULT NULL,
  `idlocation` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_places` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT 'where',
  `lat` varchar(16) DEFAULT NULL,
  `lng` varchar(16) DEFAULT NULL,
  `descr` varchar(512) DEFAULT NULL,
  `opentime` varchar(16) DEFAULT NULL,
  `closingdays` varchar(1024) DEFAULT NULL,
  `idiva` int(10) DEFAULT NULL,
  `defaulttime` varchar(16) DEFAULT NULL,
  `ordering` int(10) NOT NULL DEFAULT 1,
  `address` varchar(128) DEFAULT NULL,
  `wopening` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_prices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT 'cost',
  `attr` varchar(128) DEFAULT NULL,
  `idiva` int(10) DEFAULT NULL,
  `closingd` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_restrictions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT 'restriction',
  `month` tinyint(2) NOT NULL DEFAULT 7,
  `wday` tinyint(1) DEFAULT NULL,
  `minlos` tinyint(2) NOT NULL DEFAULT 1,
  `multiplyminlos` tinyint(1) NOT NULL DEFAULT 0,
  `maxlos` tinyint(2) NOT NULL DEFAULT 0,
  `dfrom` int(10) DEFAULT NULL,
  `dto` int(10) DEFAULT NULL,
  `wdaytwo` tinyint(1) DEFAULT NULL,
  `wdaycombo` varchar(28) DEFAULT NULL,
  `allcars` tinyint(1) NOT NULL DEFAULT 1,
  `idcars` varchar(512) DEFAULT NULL,
  `ctad` varchar(28) DEFAULT NULL,
  `ctdd` varchar(28) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_seasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT 1,
  `from` int(11) DEFAULT NULL,
  `to` int(11) DEFAULT NULL,
  `diffcost` decimal(12,2) DEFAULT NULL,
  `idcars` varchar(256) DEFAULT NULL,
  `locations` int(10) NOT NULL DEFAULT 0,
  `spname` varchar(64) DEFAULT NULL,
  `wdays` varchar(16) DEFAULT NULL,
  `pickupincl` tinyint(1) NOT NULL DEFAULT 0,
  `val_pcent` tinyint(1) NOT NULL DEFAULT 2,
  `losoverride` varchar(512) DEFAULT NULL,
  `keepfirstdayrate` tinyint(1) NOT NULL DEFAULT 0,
  `roundmode` varchar(32) DEFAULT NULL,
  `year` int(5) DEFAULT NULL,
  `idprices` varchar(256) DEFAULT NULL,
  `promo` tinyint(1) NOT NULL DEFAULT 0,
  `promotxt` text DEFAULT NULL,
  `promodaysadv` int(5) DEFAULT NULL,
  `promominlos` tinyint(1) NOT NULL DEFAULT 0,
  `promolastmin` int(10) NOT NULL DEFAULT 0,
  `promofinalprice` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_texts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `param` varchar(128) NOT NULL,
  `exp` text,
  `setting` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_tmplock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcar` int(10) NOT NULL,
  `ritiro` int(11) NOT NULL,
  `consegna` int(11) NOT NULL,
  `until` int(11) NOT NULL,
  `realback` int(11) DEFAULT NULL,
  `idorder` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_trackings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` datetime DEFAULT NULL,
  `lastdt` datetime DEFAULT NULL,
  `fingerprint` varchar(64) DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `geo` varchar(256) DEFAULT NULL,
  `country` char(3) DEFAULT NULL,
  `idcustomer` int(10) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_tracking_infos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idtracking` int(10) NOT NULL,
  `identifier` int(10) NOT NULL,
  `trackingdt` datetime DEFAULT NULL,
  `device` char(1) DEFAULT NULL,
  `trkdata` varchar(512) DEFAULT NULL,
  `pickup` datetime DEFAULT NULL,
  `dropoff` datetime DEFAULT NULL,
  `idorder` int(10) NOT NULL DEFAULT 0,
  `referrer` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_translations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `lang` varchar(16) NOT NULL,
  `reference_id` int(10) NOT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentcar_usersdata` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ujid` int(10) NOT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('showfooter','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('timeopenstore','');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('hoursmorerentback','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('hoursmorecaravail','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('placesfront','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('allowrent','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('dateformat','%d/%m/%Y');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('showcategories','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('fronttitletag','h3');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('fronttitletagclass','');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('searchbtnclass','button');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('ivainclusa','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('tokenform','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('ccpaypal','');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('paytotal','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('payaccpercent','50');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('minuteslock','20');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('oldorders','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('sendjutility','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('currencyname','EUR');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('currencysymb','&euro;');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('currencycodepp','EUR');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('sitelogo','vikrentcar.png');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('showpartlyreserved','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('numcalendars','3');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('requirelogin','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('loadjquery','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('calendar','jqueryui');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('ehourschbasp','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('enablecoupons','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('theme','default');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('sendpdf','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('numberformat','2:.:,');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('setdropdplus','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('mindaysadvance','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('maxdate','+2y');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('firstwday','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('charatsfilter','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('damageshowtype','3');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('thumbswidth','100');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('cronkey', FLOOR(1000 + (RAND() * 9000)));
INSERT INTO `#__vikrentcar_config` (`param`, `setting`) VALUES('invoiceinum', '0');
INSERT INTO `#__vikrentcar_config` (`param`, `setting`) VALUES('invoicesuffix', '/WEB');
INSERT INTO `#__vikrentcar_config` (`param`, `setting`) VALUES('invcompanyinfo', '');
INSERT INTO `#__vikrentcar_config` (`param`, `setting`) VALUES('icalkey', FLOOR(100 + (RAND() * 900)));
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('multilang','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('timeformat','H:i');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('taxsummary','0');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('todaybookings','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('enablepin','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('emailsendwhen','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('icalendtype','pick');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('typedeposit','pcent');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('trkenabled','1');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('trkcookierfrdur','3');
INSERT INTO `#__vikrentcar_config` (`param`,`setting`) VALUES ('trkcampaigns','');

INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('disabledrentmsg','Disabled Rental Message','');
INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('fronttitle','Page Title','VikRentCar');
INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('searchbtnval','Search Button Text','Search');
INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('intromain','Main Page Introducing Text','');
INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('closingmain','Main Page Closing Text','Powered by VikRentCar');
INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('paymentname','Paypal Transaction Name','Cars Rental');
INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('disclaimer','Disclaimer Text','');
INSERT INTO `#__vikrentcar_texts` (`param`,`exp`,`setting`) VALUES ('footerordmail','Footer Text Order eMail','');

INSERT INTO `#__vikrentcar_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`) VALUES ('Bank Transfer','bank_transfer.php','0','<p>Bank Transfer Info...</p>','0.00','1','1','1','1');
INSERT INTO `#__vikrentcar_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`) VALUES ('PayPal','paypal.php','0','<p></p>','0.00','0','0','1','1');
INSERT INTO `#__vikrentcar_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`) VALUES ('Offline Credit Card','offline_credit_card.php','0','<p></p>','0.00','0','0','1','1');

INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('VRCSEPDRIVERD','separator','','0','1','0','', 0, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_NAME','text','','1','2','0','', 1, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_LNAME','text','','1','3','0','', 1, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_EMAIL','text','','1','4','1','', 0, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_PHONE','text','','0','5','0','', 0, 1);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES ('ORDER_ADDRESS','text','','0','6','0','', 0, 0, 'address');
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES ('ORDER_ZIP','text','','0','7','0','', 0, 0, 'zip');
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES ('ORDER_CITY','text','','0','8','0','', 0, 0, 'city');
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_STATE','country','','1','9','0','', 0, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_DBIRTH','date','','1','10','0','', 0, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_FLIGHTNUM','text','','0','11','0','', 0, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_NOTES','textarea','','0','12','0','', 0, 0);
INSERT INTO `#__vikrentcar_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`) VALUES ('ORDER_TERMSCONDITIONS','checkbox','','1','13','0','', 0, 0);

INSERT INTO `#__vikrentcar_countries` (`country_name`, `country_3_code`, `country_2_code`) VALUES
('Afghanistan', 'AFG', 'AF'),
('Albania', 'ALB', 'AL'),
('Algeria', 'DZA', 'DZ'),
('American Samoa', 'ASM', 'AS'),
('Andorra', 'AND', 'AD'),
('Angola', 'AGO', 'AO'),
('Anguilla', 'AIA', 'AI'),
('Antarctica', 'ATA', 'AQ'),
('Antigua and Barbuda', 'ATG', 'AG'),
('Argentina', 'ARG', 'AR'),
('Armenia', 'ARM', 'AM'),
('Aruba', 'ABW', 'AW'),
('Australia', 'AUS', 'AU'),
('Austria', 'AUT', 'AT'),
('Azerbaijan', 'AZE', 'AZ'),
('Bahamas', 'BHS', 'BS'),
('Bahrain', 'BHR', 'BH'),
('Bangladesh', 'BGD', 'BD'),
('Barbados', 'BRB', 'BB'),
('Belarus', 'BLR', 'BY'),
('Belgium', 'BEL', 'BE'),
('Belize', 'BLZ', 'BZ'),
('Benin', 'BEN', 'BJ'),
('Bermuda', 'BMU', 'BM'),
('Bhutan', 'BTN', 'BT'),
('Bolivia', 'BOL', 'BO'),
('Bosnia and Herzegowina', 'BIH', 'BA'),
('Botswana', 'BWA', 'BW'),
('Bouvet Island', 'BVT', 'BV'),
('Brazil', 'BRA', 'BR'),
('British Indian Ocean Territory', 'IOT', 'IO'),
('Brunei Darussalam', 'BRN', 'BN'),
('Bulgaria', 'BGR', 'BG'),
('Burkina Faso', 'BFA', 'BF'),
('Burundi', 'BDI', 'BI'),
('Cambodia', 'KHM', 'KH'),
('Cameroon', 'CMR', 'CM'),
('Canada', 'CAN', 'CA'),
('Cape Verde', 'CPV', 'CV'),
('Cayman Islands', 'CYM', 'KY'),
('Central African Republic', 'CAF', 'CF'),
('Chad', 'TCD', 'TD'),
('Chile', 'CHL', 'CL'),
('China', 'CHN', 'CN'),
('Christmas Island', 'CXR', 'CX'),
('Cocos (Keeling) Islands', 'CCK', 'CC'),
('Colombia', 'COL', 'CO'),
('Comoros', 'COM', 'KM'),
('Congo', 'COG', 'CG'),
('Cook Islands', 'COK', 'CK'),
('Costa Rica', 'CRI', 'CR'),
('Cote D''Ivoire', 'CIV', 'CI'),
('Croatia', 'HRV', 'HR'),
('Cuba', 'CUB', 'CU'),
('Cyprus', 'CYP', 'CY'),
('Czech Republic', 'CZE', 'CZ'),
('Denmark', 'DNK', 'DK'),
('Djibouti', 'DJI', 'DJ'),
('Dominica', 'DMA', 'DM'),
('Dominican Republic', 'DOM', 'DO'),
('East Timor', 'TMP', 'TP'),
('Ecuador', 'ECU', 'EC'),
('Egypt', 'EGY', 'EG'),
('El Salvador', 'SLV', 'SV'),
('Equatorial Guinea', 'GNQ', 'GQ'),
('Eritrea', 'ERI', 'ER'),
('Estonia', 'EST', 'EE'),
('Ethiopia', 'ETH', 'ET'),
('Falkland Islands (Malvinas)', 'FLK', 'FK'),
('Faroe Islands', 'FRO', 'FO'),
('Fiji', 'FJI', 'FJ'),
('Finland', 'FIN', 'FI'),
('France', 'FRA', 'FR'),
('French Guiana', 'GUF', 'GF'),
('French Polynesia', 'PYF', 'PF'),
('French Southern Territories', 'ATF', 'TF'),
('Gabon', 'GAB', 'GA'),
('Gambia', 'GMB', 'GM'),
('Georgia', 'GEO', 'GE'),
('Germany', 'DEU', 'DE'),
('Ghana', 'GHA', 'GH'),
('Gibraltar', 'GIB', 'GI'),
('Greece', 'GRC', 'GR'),
('Greenland', 'GRL', 'GL'),
('Grenada', 'GRD', 'GD'),
('Guadeloupe', 'GLP', 'GP'),
('Guam', 'GUM', 'GU'),
('Guatemala', 'GTM', 'GT'),
('Guinea', 'GIN', 'GN'),
('Guinea-bissau', 'GNB', 'GW'),
('Guyana', 'GUY', 'GY'),
('Haiti', 'HTI', 'HT'),
('Heard and Mc Donald Islands', 'HMD', 'HM'),
('Honduras', 'HND', 'HN'),
('Hong Kong', 'HKG', 'HK'),
('Hungary', 'HUN', 'HU'),
('Iceland', 'ISL', 'IS'),
('India', 'IND', 'IN'),
('Indonesia', 'IDN', 'ID'),
('Iran (Islamic Republic of)', 'IRN', 'IR'),
('Iraq', 'IRQ', 'IQ'),
('Ireland', 'IRL', 'IE'),
('Israel', 'ISR', 'IL'),
('Italy', 'ITA', 'IT'),
('Jamaica', 'JAM', 'JM'),
('Japan', 'JPN', 'JP'),
('Jordan', 'JOR', 'JO'),
('Kazakhstan', 'KAZ', 'KZ'),
('Kenya', 'KEN', 'KE'),
('Kiribati', 'KIR', 'KI'),
('Korea, Democratic People''s Republic of', 'PRK', 'KP'),
('Korea, Republic of', 'KOR', 'KR'),
('Kuwait', 'KWT', 'KW'),
('Kyrgyzstan', 'KGZ', 'KG'),
('Lao People''s Democratic Republic', 'LAO', 'LA'),
('Latvia', 'LVA', 'LV'),
('Lebanon', 'LBN', 'LB'),
('Lesotho', 'LSO', 'LS'),
('Liberia', 'LBR', 'LR'),
('Libyan Arab Jamahiriya', 'LBY', 'LY'),
('Liechtenstein', 'LIE', 'LI'),
('Lithuania', 'LTU', 'LT'),
('Luxembourg', 'LUX', 'LU'),
('Macau', 'MAC', 'MO'),
('Macedonia, The Former Yugoslav Republic of', 'MKD', 'MK'),
('Madagascar', 'MDG', 'MG'),
('Malawi', 'MWI', 'MW'),
('Malaysia', 'MYS', 'MY'),
('Maldives', 'MDV', 'MV'),
('Mali', 'MLI', 'ML'),
('Malta', 'MLT', 'MT'),
('Marshall Islands', 'MHL', 'MH'),
('Martinique', 'MTQ', 'MQ'),
('Mauritania', 'MRT', 'MR'),
('Mauritius', 'MUS', 'MU'),
('Mayotte', 'MYT', 'YT'),
('Mexico', 'MEX', 'MX'),
('Micronesia, Federated States of', 'FSM', 'FM'),
('Moldova, Republic of', 'MDA', 'MD'),
('Monaco', 'MCO', 'MC'),
('Mongolia', 'MNG', 'MN'),
('Montenegro', 'MNE', 'ME'),
('Montserrat', 'MSR', 'MS'),
('Morocco', 'MAR', 'MA'),
('Mozambique', 'MOZ', 'MZ'),
('Myanmar', 'MMR', 'MM'),
('Namibia', 'NAM', 'NA'),
('Nauru', 'NRU', 'NR'),
('Nepal', 'NPL', 'NP'),
('Netherlands', 'NLD', 'NL'),
('Netherlands Antilles', 'ANT', 'AN'),
('New Caledonia', 'NCL', 'NC'),
('New Zealand', 'NZL', 'NZ'),
('Nicaragua', 'NIC', 'NI'),
('Niger', 'NER', 'NE'),
('Nigeria', 'NGA', 'NG'),
('Niue', 'NIU', 'NU'),
('Norfolk Island', 'NFK', 'NF'),
('Northern Mariana Islands', 'MNP', 'MP'),
('Norway', 'NOR', 'NO'),
('Oman', 'OMN', 'OM'),
('Pakistan', 'PAK', 'PK'),
('Palau', 'PLW', 'PW'),
('Panama', 'PAN', 'PA'),
('Papua New Guinea', 'PNG', 'PG'),
('Paraguay', 'PRY', 'PY'),
('Peru', 'PER', 'PE'),
('Philippines', 'PHL', 'PH'),
('Pitcairn', 'PCN', 'PN'),
('Poland', 'POL', 'PL'),
('Portugal', 'PRT', 'PT'),
('Puerto Rico', 'PRI', 'PR'),
('Qatar', 'QAT', 'QA'),
('Reunion', 'REU', 'RE'),
('Romania', 'ROU', 'RO'),
('Russian Federation', 'RUS', 'RU'),
('Rwanda', 'RWA', 'RW'),
('Saint Kitts and Nevis', 'KNA', 'KN'),
('Saint Lucia', 'LCA', 'LC'),
('Saint Vincent and the Grenadines', 'VCT', 'VC'),
('Samoa', 'WSM', 'WS'),
('San Marino', 'SMR', 'SM'),
('Sao Tome and Principe', 'STP', 'ST'),
('Saudi Arabia', 'SAU', 'SA'),
('Senegal', 'SEN', 'SN'),
('Seychelles', 'SYC', 'SC'),
('Sierra Leone', 'SLE', 'SL'),
('Singapore', 'SGP', 'SG'),
('Slovakia (Slovak Republic)', 'SVK', 'SK'),
('Slovenia', 'SVN', 'SI'),
('Solomon Islands', 'SLB', 'SB'),
('Somalia', 'SOM', 'SO'),
('South Africa', 'ZAF', 'ZA'),
('South Georgia and the South Sandwich Islands', 'SGS', 'GS'),
('Spain', 'ESP', 'ES'),
('Sri Lanka', 'LKA', 'LK'),
('St. Helena', 'SHN', 'SH'),
('St. Pierre and Miquelon', 'SPM', 'PM'),
('Sudan', 'SDN', 'SD'),
('Suriname', 'SUR', 'SR'),
('Svalbard and Jan Mayen Islands', 'SJM', 'SJ'),
('Swaziland', 'SWZ', 'SZ'),
('Sweden', 'SWE', 'SE'),
('Switzerland', 'CHE', 'CH'),
('Syrian Arab Republic', 'SYR', 'SY'),
('Taiwan', 'TWN', 'TW'),
('Tajikistan', 'TJK', 'TJ'),
('Tanzania, United Republic of', 'TZA', 'TZ'),
('Thailand', 'THA', 'TH'),
('Togo', 'TGO', 'TG'),
('Tokelau', 'TKL', 'TK'),
('Tonga', 'TON', 'TO'),
('Trinidad and Tobago', 'TTO', 'TT'),
('Tunisia', 'TUN', 'TN'),
('Turkey', 'TUR', 'TR'),
('Turkmenistan', 'TKM', 'TM'),
('Turks and Caicos Islands', 'TCA', 'TC'),
('Tuvalu', 'TUV', 'TV'),
('Uganda', 'UGA', 'UG'),
('Ukraine', 'UKR', 'UA'),
('United Arab Emirates', 'ARE', 'AE'),
('United Kingdom', 'GBR', 'GB'),
('United States', 'USA', 'US'),
('United States Minor Outlying Islands', 'UMI', 'UM'),
('Uruguay', 'URY', 'UY'),
('Uzbekistan', 'UZB', 'UZ'),
('Vanuatu', 'VUT', 'VU'),
('Vatican City State (Holy See)', 'VAT', 'VA'),
('Venezuela', 'VEN', 'VE'),
('Viet Nam', 'VNM', 'VN'),
('Virgin Islands (British)', 'VGB', 'VG'),
('Virgin Islands (U.S.)', 'VIR', 'VI'),
('Wallis and Futuna Islands', 'WLF', 'WF'),
('Western Sahara', 'ESH', 'EH'),
('Yemen', 'YEM', 'YE'),
('The Democratic Republic of Congo', 'DRC', 'DC'),
('Zambia', 'ZMB', 'ZM'),
('Zimbabwe', 'ZWE', 'ZW'),
('East Timor', 'XET', 'XE'),
('Jersey', 'XJE', 'XJ'),
('St. Barthelemy', 'XSB', 'XB'),
('St. Eustatius', 'XSE', 'XU'),
('Canary Islands', 'XCA', 'XC'),
('Serbia', 'SRB', 'RS'),
('Sint Maarten (French Antilles)', 'MAF', 'MF'),
('Sint Maarten (Netherlands Antilles)', 'SXM', 'SX');