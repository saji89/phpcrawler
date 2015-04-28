--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'A unique id for the entry',
  `link_id` bigint(20) NOT NULL COMMENT 'Unique id of the URL',
  `img_count` int(11) NOT NULL COMMENT 'The count of images in this url',
  `time_taken` float NOT NULL COMMENT 'The time taken to find the number of img tags in this url',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Stores the count of images in a link';

--
-- Table structure for table `links`
--

CREATE TABLE IF NOT EXISTS `links` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'A unique id for the entry',
  `url` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'The unique URL obtained',
  `parent_site` bigint(20) NOT NULL COMMENT 'Id of the parent site',
  `created` date NOT NULL COMMENT 'The time at whioch this entry was made',
  `level` int(11) NOT NULL COMMENT 'The heirarchy level of the link, within the site',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table to store urls';