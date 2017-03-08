DROP TABLE IF EXISTS `contact_us_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_us_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_subject` varchar(255) NOT NULL,
  `contact_message` tinytext NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_us_data`
--

LOCK TABLES `contact_us_data` WRITE;
/*!40000 ALTER TABLE `contact_us_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_us_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registration`
--

DROP TABLE IF EXISTS `registration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plan` varchar(255) DEFAULT NULL,
  `title` varchar(5) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cname` varchar(255) NOT NULL,
  `ccode` varchar(5) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `state` varchar(45) NOT NULL,
  `city` varchar(45) NOT NULL,
  `zip` varchar(7) NOT NULL,
  `address` varchar(255) NOT NULL,
  `country` varchar(50) NOT NULL,
  `comments` tinytext NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration`
--

LOCK TABLES `registration` WRITE;
/*!40000 ALTER TABLE `registration` DISABLE KEYS */;
/*!40000 ALTER TABLE `registration` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `cportal_sessions`;
CREATE TABLE `cportal_sessions` (
  `id` varchar(100) NOT NULL,
  `ip_address`  varchar(16) NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `support_form`;
CREATE TABLE `support_form` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `contact_name` varchar(255) NOT NULL,
   `contact_email` varchar(255) NOT NULL,
   `contact_tenant` varchar(255) NOT NULL,
   `contact_message` tinytext NOT NULL,
   `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
