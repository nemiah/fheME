--Add all SQL commands in this file

-- Added by Rainer Furtmeier, 14.07.2012
ALTER TABLE `Fhem` ADD `FhemAlias` varchar(30) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemITModel` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemHMModel` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemHMSub` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemHMClass` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemEMModel` varchar(20) NOT NULL;
ALTER TABLE `Fhem` ADD `FhemRoom` varchar(20) NOT NULL;