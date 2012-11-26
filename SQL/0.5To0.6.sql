--Add all SQL commands in this file

-- Added by Rainer Furtmeier, 14.07.2012
ALTER TABLE `Fhem` ADD `FhemAlias` varchar(30) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemITModel` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemHMModel` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemHMSub` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemHMClass` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemEMModel` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemRoom` varchar(20) NOT NULL;
ALTER TABLE `Einkaufszettel` ADD `EinkaufszettelNameDetails` VARCHAR( 250 ) NOT NULL;

-- Added by Rainer Furtmeier, 21.10.2012
CREATE TABLE IF NOT EXISTS `TinkerforgeData` (
  `TinkerforgeDataID` int(10) NOT NULL,
  `TinkerforgeDataTinkerforgeID` int(10) NOT NULL,
  `TinkerforgeDataBrickID` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `TinkerforgeDataTime` int(15) NOT NULL,
  `TinkerforgeDataValue1` float NOT NULL,
  `TinkerforgeDataValue2` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `Tinkerforge` (
  `TinkerforgeID` int(10) NOT NULL AUTO_INCREMENT,
  `TinkerforgeName` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `TinkerforgeServerIP` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`TinkerforgeID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;