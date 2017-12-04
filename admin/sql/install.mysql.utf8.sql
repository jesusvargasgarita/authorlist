
CREATE TABLE IF NOT EXISTS `#__authorlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `alias` VARCHAR(50) NOT NULL,
  `display_alias` VARCHAR(50),
  `description` mediumtext,
  `image` varchar(250) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `featuted` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `access` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` int(11) NOT NULL DEFAULT '0',
  `params` TEXT NOT NULL,
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`userid`)
)