-- MySQL dump 10.13  Distrib 5.1.41, for Win32 (ia32)


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cc_articles`
--

DROP TABLE IF EXISTS `cc_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` int(11) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `calls` int(11) NOT NULL,
  `orders` int(11) NOT NULL DEFAULT '0',
  `price` float(8,2) NOT NULL,
  `object1` text COLLATE utf8_general_ci NOT NULL,
  `object2` text COLLATE utf8_general_ci NOT NULL,
  `object3` text COLLATE utf8_general_ci NOT NULL,
  `header_de` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `teaser_de` text COLLATE utf8_general_ci NOT NULL,
  `text_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `tags_de` text COLLATE utf8_general_ci NOT NULL,
  `header_en` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `teaser_en` text COLLATE utf8_general_ci NOT NULL,
  `text_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `tags_en` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `index_de` (`header_de`,`teaser_de`,`text_de`),
  FULLTEXT KEY `index_en` (`header_en`,`teaser_en`,`text_en`),
  FULLTEXT KEY `tags_de` (`tags_de`),
  FULLTEXT KEY `tags_en` (`tags_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_articles_categories`
--

DROP TABLE IF EXISTS `cc_articles_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_articles_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_cat` int(11) NOT NULL DEFAULT '0',
  `sort_id` int(11) NOT NULL,
  `group` varchar(256) COLLATE utf8_general_ci NOT NULL DEFAULT 'public',
  `group_edit` text COLLATE utf8_general_ci NOT NULL,
  `order_opt` tinyint(1) NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `rating` tinyint(1) NOT NULL DEFAULT '0',
  `image` text COLLATE utf8_general_ci NOT NULL,
  `target_page` smallint(6) NOT NULL,
  `category_de` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `cat_teaser_de` text COLLATE utf8_general_ci NOT NULL,
  `category_en` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `cat_teaser_en` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`cat_id`),
  FULLTEXT KEY `cat_teaser_de` (`cat_teaser_de`),
  FULLTEXT KEY `cat_teaser_en` (`cat_teaser_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_badlogin`
--

DROP TABLE IF EXISTS `cc_badlogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_badlogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) COLLATE utf8_general_ci NOT NULL,
  `timestamp` int(11) NOT NULL,
  `triedUsername` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_bannedip`
--

DROP TABLE IF EXISTS `cc_bannedip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_bannedip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(16) COLLATE utf8_general_ci NOT NULL,
  `setAt` int(11) NOT NULL,
  `until` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_comments`
--

DROP TABLE IF EXISTS `cc_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(32) COLLATE utf8_general_ci NOT NULL,
  `entry_id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `author` varchar(200) COLLATE utf8_general_ci NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `comment` text COLLATE utf8_general_ci NOT NULL,
  `email` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `url` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `gravatar` tinyint(1) NOT NULL DEFAULT '0',
  `notify` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_contents_foot`
--

DROP TABLE IF EXISTS `cc_contents_foot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_foot` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_foot`
--

LOCK TABLES `cc_contents_foot` WRITE;
/*!40000 ALTER TABLE `cc_contents_foot` DISABLE KEYS */;
INSERT INTO `cc_contents_foot` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_foot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_foot_preview`
--

DROP TABLE IF EXISTS `cc_contents_foot_preview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_foot_preview` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_foot_preview`
--

LOCK TABLES `cc_contents_foot_preview` WRITE;
/*!40000 ALTER TABLE `cc_contents_foot_preview` DISABLE KEYS */;
INSERT INTO `cc_contents_foot_preview` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_foot_preview` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_head`
--

DROP TABLE IF EXISTS `cc_contents_head`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_head` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_head`
--

LOCK TABLES `cc_contents_head` WRITE;
/*!40000 ALTER TABLE `cc_contents_head` DISABLE KEYS */;
INSERT INTO `cc_contents_head` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_head` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_head_preview`
--

DROP TABLE IF EXISTS `cc_contents_head_preview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_head_preview` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_head_preview`
--

LOCK TABLES `cc_contents_head_preview` WRITE;
/*!40000 ALTER TABLE `cc_contents_head_preview` DISABLE KEYS */;
INSERT INTO `cc_contents_head_preview` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_head_preview` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_left`
--

DROP TABLE IF EXISTS `cc_contents_left`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_left` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_left`
--

LOCK TABLES `cc_contents_left` WRITE;
/*!40000 ALTER TABLE `cc_contents_left` DISABLE KEYS */;
INSERT INTO `cc_contents_left` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_left` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_left_preview`
--

DROP TABLE IF EXISTS `cc_contents_left_preview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_left_preview` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_left_preview`
--

LOCK TABLES `cc_contents_left_preview` WRITE;
/*!40000 ALTER TABLE `cc_contents_left_preview` DISABLE KEYS */;
INSERT INTO `cc_contents_left_preview` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_left_preview` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_main`
--

DROP TABLE IF EXISTS `cc_contents_main`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_main` (
  `page_id` int(11) NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL DEFAULT 'text',
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  `con2_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con2_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con2` varchar(50) COLLATE utf8_general_ci NOT NULL DEFAULT 'text',
  `styles-con2` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_main`
--

LOCK TABLES `cc_contents_main` WRITE;
/*!40000 ALTER TABLE `cc_contents_main` DISABLE KEYS */;
INSERT INTO `cc_contents_main` VALUES 
(1,'<h1>Herzlich willkommen im concise-wms</h1>','<h1>Welcome to concise-wms</h1>','text','{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}','','','',''),
(-1007, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', 'userpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}'),
(-1006, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '{"regsubject":"Registrierung bei {#domain}","regthank":"herzlich willkommen bei {#domain}.","regmessage":"<p>Sie erhalten diese E-Mail zur im Zusammenhang mit Ihrer Registrierung auf {#domain}.<\\/p>","regtextshop":"<p>Die Registrierung erleichtert Ihnen den Bestellvorgang und Sie werden ggf. über unseren Newsletter auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regmail":"<p>Sie werden automatisch über Neuerungen informiert.<\\/p>","reguser":"<p>Vielen Dank für Ihr Interesse!<br \\/><br \\/>In Kürze sollten Sie eine automatische E-Mail mit einem Link zur Bestätigung über die kostenfreie Registrierung erhalten.<br \\/>Bitte schließen Sie die Registrierung durch&nbsp;Klick auf den entsprechenden Link in der Bestätigungsmail ab.<\\/p>","regnewsl":"<p>Vielen Dank für die Anmeldung zum Newsletter! Sie werden fortan über Neuerungen auf dem Laufenden gehalten.<\\/p>","unregnewsl":"<p>Die Abmeldung vom Newsletter ist erfolgt.<br \\/><br \\/>Wir bedauern, dass Ihre Entscheidung, den Newsletter nicht mehr erhalten zu wollen. Um Spam zu vermeiden, wird der Newsletter nur intern verwendet und in maßvollem Rhythmus verschickt.<br \\/>Natürlich besteht weiterhin die Möglichkeit, über unsere Website auf dem Laufenden zu bleiben.<br \\/>Vielen Dank für Ihr Interesse und Ihre Unterstützung!<\\/p>","regtextnewsl":"<p>Sie werden fortan über Neuerungen oder anstehende Termine auf dem Laufenden gehalten.<br \\/>Sollten Sie den Newsletter nicht mehr erhalten wollen, können Sie ihn jederzeit über den Link unten wieder abbestellen.<br \\/><br \\/>Vielen Dank für Ihr Interesse an unserem Newsletter.<\\/p>","regtextguest":"<p>Die Registrierung erlaubt Ihnen den Zugang zu erweiterten Premium-Inhalten. Über unseren Newsletter werden Sie, sofern erwünscht, auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regtextoptin":"<p>Für die Aktivierung Ihres Benutzerkontos benötigen wir jetzt noch Ihre Bestätigung über den folgenden Link:<\\/p>\\r\\n<p><a href=\\"{#reglink}\\">Registrierung bestätigen und abschließen<\\/a>.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Sollte Ihnen der Inhalt dieser E-Mail nichts sagen bzw. ein entsprechender Eintrag nicht von Ihnen selbst vorgenommen worden sein, ignorieren Sie bitte diese E-Mail. <br \\/> In diesem Fall erfolgt keine Aktivierung Ihres Benutzerkontos.<\\/p>\\r\\n<p>Die Registrierung ist kostenfrei und kann durch Klick auf den entsprechenden Link in der jeweils letzten E-Mail jederzeit wieder rückgängig gemacht werden.<\\/p>\\r\\n<p>Weitere Informationen unter:<\\/p>\\r\\n<p>&nbsp;<\\/p>"}', '{"regsubject":"Registrierung bei {#domain}","regthank":"herzlich willkommen bei {#domain}.","regmessage":"<p>Sie erhalten diese E-Mail zur im Zusammenhang mit Ihrer Registrierung auf {#domain}.<\\/p>","regtextshop":"<p>Die Registrierung erleichtert Ihnen den Bestellvorgang und Sie werden ggf. über unseren Newsletter auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regmail":"<p>Sie werden automatisch über Neuerungen informiert.<\\/p>","reguser":"<p>Vielen Dank für Ihr Interesse!<br \\/><br \\/>In Kürze sollten Sie eine automatische E-Mail mit einem Link zur Bestätigung über die kostenfreie Registrierung erhalten.<br \\/>Bitte schließen Sie die Registrierung durch&nbsp;Klick auf den entsprechenden Link in der Bestätigungsmail ab.<\\/p>","regnewsl":"<p>Vielen Dank für die Anmeldung zum Newsletter! Sie werden fortan über Neuerungen auf dem Laufenden gehalten.<\\/p>","unregnewsl":"<p>Die Abmeldung vom Newsletter ist erfolgt.<br \\/><br \\/>Wir bedauern, dass Ihre Entscheidung, den Newsletter nicht mehr erhalten zu wollen. Um Spam zu vermeiden, wird der Newsletter nur intern verwendet und in maßvollem Rhythmus verschickt.<br \\/>Natürlich besteht weiterhin die Möglichkeit, über unsere Website auf dem Laufenden zu bleiben.<br \\/>Vielen Dank für Ihr Interesse und Ihre Unterstützung!<\\/p>","regtextnewsl":"<p>Sie werden fortan über Neuerungen oder anstehende Termine auf dem Laufenden gehalten.<br \\/>Sollten Sie den Newsletter nicht mehr erhalten wollen, können Sie ihn jederzeit über den Link unten wieder abbestellen.<br \\/><br \\/>Vielen Dank für Ihr Interesse an unserem Newsletter.<\\/p>","regtextguest":"<p>Die Registrierung erlaubt Ihnen den Zugang zu erweiterten Premium-Inhalten. Über unseren Newsletter werden Sie, sofern erwünscht, auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regtextoptin":"<p>Für die Aktivierung Ihres Benutzerkontos benötigen wir jetzt noch Ihre Bestätigung über den folgenden Link:<\\/p>\\r\\n<p><a href=\\"{#reglink}\\">Registrierung bestätigen und abschließen<\\/a>.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Sollte Ihnen der Inhalt dieser E-Mail nichts sagen bzw. ein entsprechender Eintrag nicht von Ihnen selbst vorgenommen worden sein, ignorieren Sie bitte diese E-Mail. <br \\/> In diesem Fall erfolgt keine Aktivierung Ihres Benutzerkontos.<\\/p>\\r\\n<p>Die Registrierung ist kostenfrei und kann durch Klick auf den entsprechenden Link in der jeweils letzten E-Mail jederzeit wieder rückgängig gemacht werden.<\\/p>\\r\\n<p>Weitere Informationen unter:<\\/p>\\r\\n<p>&nbsp;<\\/p>"}', 'regpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}'),
(-1005, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '<div id="logoutForm" class="form col-md-12 margin-top-md margin-bottom-md">\r\n<div class="top">&nbsp;</div>\r\n<div class="center"><form><fieldset><legend> {#s_header:userpage} </legend>\r\n<h2 class="logout">Logout<span class="logout icons icon-logout"><br /></span></h2>\r\n<p class="alert alert-success notice success">{#s_text:logout}</p>\r\n<p><a class="{#t_class:btn} {#t_class:btnpri} formbutton ok right" href="{#root}/login.html"><span class="{#t_icons:icons} {#t_icons:icon}{#t_icons:signin} icon-left"></span> {#s_text:relog}</a></p>\r\n</fieldset></form></div>\r\n<div class="bottom">&nbsp;</div>\r\n</div>', '<div id="logoutForm" class="form col-md-12 margin-top-md margin-bottom-md">\r\n<div class="top">&nbsp;</div>\r\n<div class="center"><form><fieldset><legend> {#s_header:userpage} </legend>\r\n<h2 class="logout">Logout<span class="logout icons icon-logout"><br /></span></h2>\r\n<p class="alert alert-success notice success">{#s_text:logout}</p>\r\n<p><a class="{#t_class:btn} {#t_class:btnpri} formbutton ok right" href="{#root}/login.html"><span class="{#t_icons:icons} {#t_icons:icon}{#t_icons:signin} icon-left"></span> {#s_text:relog}</a></p>\r\n</fieldset></form></div>\r\n<div class="bottom">&nbsp;</div>\r\n</div>', 'logoutpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"half","mt":"","mb":"","ml":"","mr":""}'),
(-1004, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', 'searchpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}'),
(-1003, '', '', 'text', '{"ctr":"1","row":"1","cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}', '{"er":"<p>{#s_text:error}<\\/p>","nf":"<p>{#s_header:notfound}<\\/p>","fb":"<p>{#s_text:errorforbidden}<\\/p>","sv":"<p>{#s_text:errorserver}<\\/p>","st":"<p>{#s_text:errorstatus}<\\/p>","ac":"<p>{#s_text:erroraccess}<\\/p>","nl":"<p>{#s_text:erroraccess2}<\\/p>","to":"<p>{#s_notice:errortimeout}<\\/p>","nn":"<p>{#s_notice:nofeed}<\\/p>"}', '{"er":"<p>{#s_text:error}<\\/p>","nf":"<p>{#s_header:notfound}<\\/p>","fb":"<p>{#s_text:errorforbidden}<\\/p>","sv":"<p>{#s_text:errorserver}<\\/p>","st":"<p>{#s_text:errorstatus}<\\/p>","ac":"<p>{#s_text:erroraccess}<\\/p>","nl":"<p>{#s_text:erroraccess2}<\\/p>","to":"<p>{#s_notice:errortimeout}<\\/p>","nn":"<p>{#s_notice:nofeed}<\\/p>"}', 'errorpage', '{"cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}'),
(-1002, '<h1>Benutzerseite</h1>', '<h1>Benutzerseite</h1>', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', 'loginpage', '{"row":"1","cols":"half","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}');
/*!40000 ALTER TABLE `cc_contents_main` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_main_preview`
--

DROP TABLE IF EXISTS `cc_contents_main_preview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_main_preview` (
  `page_id` int(11) NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL DEFAULT 'text',
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  `con2_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con2_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con2` varchar(50) COLLATE utf8_general_ci NOT NULL DEFAULT 'text',
  `styles-con2` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_main_preview`
--

LOCK TABLES `cc_contents_main_preview` WRITE;
/*!40000 ALTER TABLE `cc_contents_main_preview` DISABLE KEYS */;
INSERT INTO `cc_contents_main_preview` VALUES 
(1,'<h1>Herzlich willkommen im concise-wms</h1>','<h1>Welcome to concise-wms</h1>','text','{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}','','','',''),
(-1007, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', 'userpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}'),
(-1006, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '{"regsubject":"Registrierung bei {#domain}","regthank":"herzlich willkommen bei {#domain}.","regmessage":"<p>Sie erhalten diese E-Mail zur im Zusammenhang mit Ihrer Registrierung auf {#domain}.<\\/p>","regtextshop":"<p>Die Registrierung erleichtert Ihnen den Bestellvorgang und Sie werden ggf. über unseren Newsletter auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regmail":"<p>Sie werden automatisch über Neuerungen informiert.<\\/p>","reguser":"<p>Vielen Dank für Ihr Interesse!<br \\/><br \\/>In Kürze sollten Sie eine automatische E-Mail mit einem Link zur Bestätigung über die kostenfreie Registrierung erhalten.<br \\/>Bitte schließen Sie die Registrierung durch&nbsp;Klick auf den entsprechenden Link in der Bestätigungsmail ab.<\\/p>","regnewsl":"<p>Vielen Dank für die Anmeldung zum Newsletter! Sie werden fortan über Neuerungen auf dem Laufenden gehalten.<\\/p>","unregnewsl":"<p>Die Abmeldung vom Newsletter ist erfolgt.<br \\/><br \\/>Wir bedauern, dass Ihre Entscheidung, den Newsletter nicht mehr erhalten zu wollen. Um Spam zu vermeiden, wird der Newsletter nur intern verwendet und in maßvollem Rhythmus verschickt.<br \\/>Natürlich besteht weiterhin die Möglichkeit, über unsere Website auf dem Laufenden zu bleiben.<br \\/>Vielen Dank für Ihr Interesse und Ihre Unterstützung!<\\/p>","regtextnewsl":"<p>Sie werden fortan über Neuerungen oder anstehende Termine auf dem Laufenden gehalten.<br \\/>Sollten Sie den Newsletter nicht mehr erhalten wollen, können Sie ihn jederzeit über den Link unten wieder abbestellen.<br \\/><br \\/>Vielen Dank für Ihr Interesse an unserem Newsletter.<\\/p>","regtextguest":"<p>Die Registrierung erlaubt Ihnen den Zugang zu erweiterten Premium-Inhalten. Über unseren Newsletter werden Sie, sofern erwünscht, auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regtextoptin":"<p>Für die Aktivierung Ihres Benutzerkontos benötigen wir jetzt noch Ihre Bestätigung über den folgenden Link:<\\/p>\\r\\n<p><a href=\\"{#reglink}\\">Registrierung bestätigen und abschließen<\\/a>.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Sollte Ihnen der Inhalt dieser E-Mail nichts sagen bzw. ein entsprechender Eintrag nicht von Ihnen selbst vorgenommen worden sein, ignorieren Sie bitte diese E-Mail. <br \\/> In diesem Fall erfolgt keine Aktivierung Ihres Benutzerkontos.<\\/p>\\r\\n<p>Die Registrierung ist kostenfrei und kann durch Klick auf den entsprechenden Link in der jeweils letzten E-Mail jederzeit wieder rückgängig gemacht werden.<\\/p>\\r\\n<p>Weitere Informationen unter:<\\/p>\\r\\n<p>&nbsp;<\\/p>"}', '{"regsubject":"Registrierung bei {#domain}","regthank":"herzlich willkommen bei {#domain}.","regmessage":"<p>Sie erhalten diese E-Mail zur im Zusammenhang mit Ihrer Registrierung auf {#domain}.<\\/p>","regtextshop":"<p>Die Registrierung erleichtert Ihnen den Bestellvorgang und Sie werden ggf. über unseren Newsletter auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regmail":"<p>Sie werden automatisch über Neuerungen informiert.<\\/p>","reguser":"<p>Vielen Dank für Ihr Interesse!<br \\/><br \\/>In Kürze sollten Sie eine automatische E-Mail mit einem Link zur Bestätigung über die kostenfreie Registrierung erhalten.<br \\/>Bitte schließen Sie die Registrierung durch&nbsp;Klick auf den entsprechenden Link in der Bestätigungsmail ab.<\\/p>","regnewsl":"<p>Vielen Dank für die Anmeldung zum Newsletter! Sie werden fortan über Neuerungen auf dem Laufenden gehalten.<\\/p>","unregnewsl":"<p>Die Abmeldung vom Newsletter ist erfolgt.<br \\/><br \\/>Wir bedauern, dass Ihre Entscheidung, den Newsletter nicht mehr erhalten zu wollen. Um Spam zu vermeiden, wird der Newsletter nur intern verwendet und in maßvollem Rhythmus verschickt.<br \\/>Natürlich besteht weiterhin die Möglichkeit, über unsere Website auf dem Laufenden zu bleiben.<br \\/>Vielen Dank für Ihr Interesse und Ihre Unterstützung!<\\/p>","regtextnewsl":"<p>Sie werden fortan über Neuerungen oder anstehende Termine auf dem Laufenden gehalten.<br \\/>Sollten Sie den Newsletter nicht mehr erhalten wollen, können Sie ihn jederzeit über den Link unten wieder abbestellen.<br \\/><br \\/>Vielen Dank für Ihr Interesse an unserem Newsletter.<\\/p>","regtextguest":"<p>Die Registrierung erlaubt Ihnen den Zugang zu erweiterten Premium-Inhalten. Über unseren Newsletter werden Sie, sofern erwünscht, auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regtextoptin":"<p>Für die Aktivierung Ihres Benutzerkontos benötigen wir jetzt noch Ihre Bestätigung über den folgenden Link:<\\/p>\\r\\n<p><a href=\\"{#reglink}\\">Registrierung bestätigen und abschließen<\\/a>.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Sollte Ihnen der Inhalt dieser E-Mail nichts sagen bzw. ein entsprechender Eintrag nicht von Ihnen selbst vorgenommen worden sein, ignorieren Sie bitte diese E-Mail. <br \\/> In diesem Fall erfolgt keine Aktivierung Ihres Benutzerkontos.<\\/p>\\r\\n<p>Die Registrierung ist kostenfrei und kann durch Klick auf den entsprechenden Link in der jeweils letzten E-Mail jederzeit wieder rückgängig gemacht werden.<\\/p>\\r\\n<p>Weitere Informationen unter:<\\/p>\\r\\n<p>&nbsp;<\\/p>"}', 'regpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}'),
(-1005, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '<div id="logoutForm" class="form col-md-12 margin-top-md margin-bottom-md">\r\n<div class="top">&nbsp;</div>\r\n<div class="center"><form><fieldset><legend> {#s_header:userpage} </legend>\r\n<h2 class="logout">Logout<span class="logout icons icon-logout"><br /></span></h2>\r\n<p class="alert alert-success notice success">{#s_text:logout}</p>\r\n<p><a class="{#t_class:btn} {#t_class:btnpri} formbutton ok right" href="{#root}/login.html"><span class="{#t_icons:icons} {#t_icons:icon}{#t_icons:signin} icon-left"></span> {#s_text:relog}</a></p>\r\n</fieldset></form></div>\r\n<div class="bottom">&nbsp;</div>\r\n</div>', '<div id="logoutForm" class="form col-md-12 margin-top-md margin-bottom-md">\r\n<div class="top">&nbsp;</div>\r\n<div class="center"><form><fieldset><legend> {#s_header:userpage} </legend>\r\n<h2 class="logout">Logout<span class="logout icons icon-logout"><br /></span></h2>\r\n<p class="alert alert-success notice success">{#s_text:logout}</p>\r\n<p><a class="{#t_class:btn} {#t_class:btnpri} formbutton ok right" href="{#root}/login.html"><span class="{#t_icons:icons} {#t_icons:icon}{#t_icons:signin} icon-left"></span> {#s_text:relog}</a></p>\r\n</fieldset></form></div>\r\n<div class="bottom">&nbsp;</div>\r\n</div>', 'logoutpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"half","mt":"","mb":"","ml":"","mr":""}'),
(-1004, '', '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', 'searchpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}'),
(-1003, '', '', 'text', '{"ctr":"1","row":"1","cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}', '{"er":"<p>{#s_text:error}<\\/p>","nf":"<p>{#s_header:notfound}<\\/p>","fb":"<p>{#s_text:errorforbidden}<\\/p>","sv":"<p>{#s_text:errorserver}<\\/p>","st":"<p>{#s_text:errorstatus}<\\/p>","ac":"<p>{#s_text:erroraccess}<\\/p>","nl":"<p>{#s_text:erroraccess2}<\\/p>","to":"<p>{#s_notice:errortimeout}<\\/p>","nn":"<p>{#s_notice:nofeed}<\\/p>"}', '{"er":"<p>{#s_text:error}<\\/p>","nf":"<p>{#s_header:notfound}<\\/p>","fb":"<p>{#s_text:errorforbidden}<\\/p>","sv":"<p>{#s_text:errorserver}<\\/p>","st":"<p>{#s_text:errorstatus}<\\/p>","ac":"<p>{#s_text:erroraccess}<\\/p>","nl":"<p>{#s_text:erroraccess2}<\\/p>","to":"<p>{#s_notice:errortimeout}<\\/p>","nn":"<p>{#s_notice:nofeed}<\\/p>"}', 'errorpage', '{"cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}'),
(-1002, '<h1>Benutzerseite</h1>', '<h1>Benutzerseite</h1>', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', 'loginpage', '{"row":"1","cols":"half","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}');
/*!40000 ALTER TABLE `cc_contents_main_preview` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_right`
--

DROP TABLE IF EXISTS `cc_contents_right`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_right` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_right`
--

LOCK TABLES `cc_contents_right` WRITE;
/*!40000 ALTER TABLE `cc_contents_right` DISABLE KEYS */;
INSERT INTO `cc_contents_right` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_right` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_contents_right_preview`
--

DROP TABLE IF EXISTS `cc_contents_right_preview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_contents_right_preview` (
  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `con1_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `con1_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `type-con1` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `styles-con1` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_contents_right_preview`
--

LOCK TABLES `cc_contents_right_preview` WRITE;
/*!40000 ALTER TABLE `cc_contents_right_preview` DISABLE KEYS */;
INSERT INTO `cc_contents_right_preview` VALUES
('standard.tpl','','','',''),
('standard-leftcol.tpl','','','',''),
('standard-rightcol.tpl','','','',''),
('twocol-left.tpl','','','',''),
('twocol-right.tpl','','','',''),
('fullwidth.tpl','','','',''),
('fullwidth-leftcol.tpl','','','',''),
('fullwidth-rightcol.tpl','','','',''),
('one-page.tpl','','','','');
/*!40000 ALTER TABLE `cc_contents_right_preview` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_download`
--

DROP TABLE IF EXISTS `cc_download`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_download` (
  `filename` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `downloads` int(11) NOT NULL,
  `last_access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`filename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_errorlog`
--

DROP TABLE IF EXISTS `cc_errorlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_errorlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error` varchar(500) COLLATE utf8_general_ci NOT NULL,
  `script` varchar(200) COLLATE utf8_general_ci NOT NULL,
  `line` int(11) NOT NULL,
  `timestamp` varchar(13) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_forms`
--

DROP TABLE IF EXISTS `cc_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_id` int(11) NOT NULL,
  `table` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `title_de` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `title_en` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `foreign_key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notice_success_de` text COLLATE utf8_unicode_ci NULL,
  `notice_success_en` text COLLATE utf8_unicode_ci NULL,
  `notice_error_de` text COLLATE utf8_unicode_ci NULL,
  `notice_error_en` text COLLATE utf8_unicode_ci NULL,
  `notice_field_de` text COLLATE utf8_unicode_ci NULL,
  `notice_field_en` text COLLATE utf8_unicode_ci NULL,
  `captcha` tinyint(1) NOT NULL,
  `https` tinyint(1) NOT NULL,
  `poll` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `end_date` datetime NULL DEFAULT NULL,
  `add_table` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `add_fields` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `add_labels_de` text COLLATE utf8_unicode_ci NULL,
  `add_labels_en` text COLLATE utf8_unicode_ci NULL,
  `add_position` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `table` (`table`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_forms_definitions`
--

DROP TABLE IF EXISTS `cc_forms_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_forms_definitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `field_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '1',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `label_de` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label_en` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value_de` text COLLATE utf8_unicode_ci,
  `value_en` text COLLATE utf8_unicode_ci,
  `min_length` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_length` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options_de` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options_en` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notice_de` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notice_en` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filetypes` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filesize` int(11) NOT NULL DEFAULT '5',
  `filefolder` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fileprefix` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filerename` tinyint(1) NOT NULL,
  `filereplace` tinyint(1) NOT NULL,
  `usemail` tinyint(1) NOT NULL,
  `showpass` tinyint(1) NOT NULL,
  `link` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linkval_de` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linkval_en` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `header_de` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `header_en` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remark_de` text COLLATE utf8_unicode_ci,
  `remark_en` text COLLATE utf8_unicode_ci,
  `pagebreak` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_galleries`
--

DROP TABLE IF EXISTS `cc_galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_galleries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_id` int(11) NOT NULL,
  `gallery_name` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `group` varchar(256) COLLATE utf8_general_ci NOT NULL DEFAULT 'public',
  `group_edit` text COLLATE utf8_general_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `mod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `tags` text COLLATE utf8_general_ci NOT NULL,
  `name_de` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `name_en` varchar(300) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_galleries_images`
--

DROP TABLE IF EXISTS `cc_galleries_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_galleries_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_id` int(11) NOT NULL,
  `gallery_id` int(11) NOT NULL,
  `img_file` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `upload_date` datetime NOT NULL,
  `mod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `show` tinyint(1) NOT NULL DEFAULT '1',
  `img_tags` text COLLATE utf8_general_ci NOT NULL,
  `title_de` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `link_de` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `text_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `title_en` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `link_en` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `text_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_gbook`
--

DROP TABLE IF EXISTS `cc_gbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_gbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `gbname` varchar(200) COLLATE utf8_general_ci NOT NULL,
  `group` varchar(256) COLLATE utf8_general_ci NOT NULL DEFAULT 'public',
  `gbdate` datetime NOT NULL,
  `gbcomment` text COLLATE utf8_general_ci NOT NULL,
  `gbmail` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `gravatar` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_lang`
--

DROP TABLE IF EXISTS `cc_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_id` int(11) NOT NULL,
  `nat_code` varchar(3) COLLATE utf8_general_ci NOT NULL,
  `nationality` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `flag_file` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `def_lang` int(1) NOT NULL DEFAULT '0',
  `inst_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_lang`
--

LOCK TABLES `cc_lang` WRITE;
/*!40000 ALTER TABLE `cc_lang` DISABLE KEYS */;
INSERT INTO `cc_lang` VALUES (1,1,'de','Deutsch','flag_de.png',1,CURRENT_TIMESTAMP),(2,2,'en','English','flag_en.png',0,CURRENT_TIMESTAMP);
/*!40000 ALTER TABLE `cc_lang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_locks`
--

DROP TABLE IF EXISTS `cc_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_locks` (
  `rowID` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `tablename` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `lockedBy` varchar(256) COLLATE utf8_general_ci NOT NULL,
  `lockedUntil` int(11) NOT NULL,
  PRIMARY KEY (`rowID`,`tablename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_log`
--

DROP TABLE IF EXISTS `cc_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `realIP` varchar(32) COLLATE utf8_general_ci NOT NULL,
  `sessionID` varchar(32) COLLATE utf8_general_ci NOT NULL,
  `timestamp` int(11) NOT NULL,
  `page_id` int(9) NOT NULL,
  `lang` varchar(3) COLLATE utf8_general_ci NOT NULL,
  `referer` varchar(512) COLLATE utf8_general_ci NOT NULL,
  `browser` varchar(200) COLLATE utf8_general_ci NOT NULL,
  `version` varchar(6) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_log_bots`
--

DROP TABLE IF EXISTS `cc_log_bots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_log_bots` (
  `userAgent` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `realIP` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_news`
--

DROP TABLE IF EXISTS `cc_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` int(11) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `calls` int(11) NOT NULL,
  `object1` text COLLATE utf8_general_ci NOT NULL,
  `object2` text COLLATE utf8_general_ci NOT NULL,
  `object3` text COLLATE utf8_general_ci NOT NULL,
  `header_de` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `teaser_de` text COLLATE utf8_general_ci NOT NULL,
  `text_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `tags_de` text COLLATE utf8_general_ci NOT NULL,
  `header_en` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `teaser_en` text COLLATE utf8_general_ci NOT NULL,
  `text_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `tags_en` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `index_de` (`header_de`,`teaser_de`,`text_de`),
  FULLTEXT KEY `index_en` (`header_en`,`teaser_en`,`text_en`),
  FULLTEXT KEY `tags_de` (`tags_de`),
  FULLTEXT KEY `tags_en` (`tags_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_news_categories`
--

DROP TABLE IF EXISTS `cc_news_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_news_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_cat` int(11) NOT NULL DEFAULT '0',
  `sort_id` int(11) NOT NULL,
  `group` varchar(256) COLLATE utf8_general_ci NOT NULL DEFAULT 'public',
  `group_edit` text COLLATE utf8_general_ci NOT NULL,
  `newsfeed` tinyint(1) NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `rating` tinyint(1) NOT NULL DEFAULT '0',
  `image` text COLLATE utf8_general_ci NOT NULL,
  `target_page` smallint(6) NOT NULL,
  `category_de` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `cat_teaser_de` text COLLATE utf8_general_ci NOT NULL,
  `category_en` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `cat_teaser_en` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`cat_id`),
  FULLTEXT KEY `cat_teaser_de` (`cat_teaser_de`),
  FULLTEXT KEY `cat_teaser_en` (`cat_teaser_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_newsletter`
--

DROP TABLE IF EXISTS `cc_newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sent_date` datetime NOT NULL,
  `group` varchar(256) COLLATE utf8_general_ci NOT NULL DEFAULT '<all>',
  `only_subscribers` tinyint(1) NOT NULL DEFAULT '1',
  `extra_emails` text COLLATE utf8_general_ci NOT NULL,
  `file` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `subject` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `text` text COLLATE utf8_general_ci NOT NULL,
  `format` varchar(5) COLLATE utf8_general_ci NOT NULL DEFAULT 'html',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_pages`
--

DROP TABLE IF EXISTS `cc_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `author_id` int(11) NOT NULL DEFAULT '1',
  `group` varchar(256) COLLATE utf8_general_ci NOT NULL DEFAULT 'public',
  `group_edit` text COLLATE utf8_general_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `menu_item` tinyint(1) NOT NULL DEFAULT '1',
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `template` varchar(200) COLLATE utf8_general_ci NOT NULL DEFAULT 'standard.tpl',
  `nosearch` tinyint(1) NOT NULL DEFAULT '0',
  `robots` tinyint(1) NOT NULL DEFAULT '3',
  `canonical` int(11) NOT NULL,
  `copy` tinyint(1) NOT NULL DEFAULT '0',
  `index_page` tinyint(1) NOT NULL DEFAULT '0',
  `title_de` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `alias_de` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `html_title_de` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `description_de` varchar(200) COLLATE utf8_general_ci NOT NULL,
  `keywords_de` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `title_en` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `alias_en` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `html_title_en` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `description_en` varchar(200) COLLATE utf8_general_ci NOT NULL,
  `keywords_en` varchar(300) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_pages`
--

LOCK TABLES `cc_pages` WRITE;
/*!40000 ALTER TABLE `cc_pages` DISABLE KEYS */;
INSERT INTO `cc_pages` VALUES
(1, -1001, NOW(), CURRENT_TIMESTAMP, '1', 'admin,editor,author', '', 1, 1, -1, 0, 0, 0, 'admin.tpl', 0, 0, 0, 0, 0, 'Admin', 'admin', 'Concise WMS - Adminbereich', '', '', 'Admin', 'admin', 'Concise WMS - Admin', '', ''),
(2, -1002, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Login', 'login', '', '', '', 'Login', 'login', '', '', ''),
(3, -1003, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Fehlerseite', 'error', '', '', '', 'Error', 'error', '', '', ''),
(4, -1004, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 2, 0, 0, 0, 'Suchergebnisse', 'sitesearch', '', '', '', 'Site search', 'sitesearch', '', '', ''),
(5, -1005, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Logout', 'logout', '', '', '', 'Logout', 'logout', '', '', ''),
(6, -1006, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Registrierung', 'registration', '', '', '', 'Registration', 'registration', '', '', ''),
(7, -1007, NOW(), CURRENT_TIMESTAMP, '1', 'guest', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Mein Bereich', 'account', '', '', '', 'My account', 'account', '', '', ''),
(8, 0, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, 1, 1, 4, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_main', '', '', '', '', 'root_main-en', '', '', '', ''),
(9, -1, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, 2, 1, 2, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_top', '', '', '', '', 'root_top-en', '', '', '', ''),
(10, -2, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, 3, 1, 2, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_foot', '', '', '', '', 'root_foot-en', '', '', '', ''),
(11, -3, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 1, 0, 1, 2, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_nonmenu', '', '', '', '', 'root_nonmenu-en', '', '', '', ''),
(12, 1, NOW(), CURRENT_TIMESTAMP, '1', 'public', '', 1, 0, 1, 2, 3, 0, 'standard.tpl', 0, 3, 0, 0, 1, 'Startseite', 'startseite', 'Startseite', '', '', 'Home', 'home', 'Home', '', '');
/*!40000 ALTER TABLE `cc_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_planner`
--

DROP TABLE IF EXISTS `cc_planner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_planner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` int(11) NOT NULL DEFAULT '1',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `date_end` date NOT NULL,
  `time_end` time NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `calls` int(11) NOT NULL,
  `object1` text COLLATE utf8_general_ci NOT NULL,
  `object2` text COLLATE utf8_general_ci NOT NULL,
  `object3` text COLLATE utf8_general_ci NOT NULL,
  `header_de` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `teaser_de` text COLLATE utf8_general_ci NOT NULL,
  `text_de` mediumtext COLLATE utf8_general_ci NOT NULL,
  `tags_de` text COLLATE utf8_general_ci NOT NULL,
  `header_en` varchar(300) COLLATE utf8_general_ci NOT NULL,
  `teaser_en` text COLLATE utf8_general_ci NOT NULL,
  `text_en` mediumtext COLLATE utf8_general_ci NOT NULL,
  `tags_en` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `index_de` (`header_de`,`teaser_de`,`text_de`),
  FULLTEXT KEY `index_en` (`header_en`,`teaser_en`,`text_en`),
  FULLTEXT KEY `tags_de` (`tags_de`),
  FULLTEXT KEY `tags_en` (`tags_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_planner_categories`
--

DROP TABLE IF EXISTS `cc_planner_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_planner_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_cat` int(11) NOT NULL DEFAULT '0',
  `sort_id` int(11) NOT NULL,
  `group` varchar(256) COLLATE utf8_general_ci NOT NULL DEFAULT 'public',
  `group_edit` text COLLATE utf8_general_ci NOT NULL,
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `rating` tinyint(1) NOT NULL DEFAULT '0',
  `image` text COLLATE utf8_general_ci NOT NULL,
  `target_page` smallint(6) NOT NULL,
  `category_de` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `cat_teaser_de` text COLLATE utf8_general_ci NOT NULL,
  `category_en` varchar(64) COLLATE utf8_general_ci NOT NULL,
  `cat_teaser_en` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`cat_id`),
  FULLTEXT KEY `cat_teaser_de` (`cat_teaser_de`),
  FULLTEXT KEY `cat_teaser_en` (`cat_teaser_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `cc_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pl_name` varchar(64) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_rating`
--

DROP TABLE IF EXISTS `cc_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_rating` (
  `module` varchar(16) COLLATE utf8_general_ci NOT NULL,
  `cat_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `votes` int(11) NOT NULL,
  `rate` float NOT NULL,
  `last_vote` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_search`
--

DROP TABLE IF EXISTS `cc_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_search` (
  `page_id` int(11) NOT NULL,
  `con_de` longtext COLLATE utf8_general_ci NOT NULL,
  `con_en` longtext COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`page_id`),
  FULLTEXT KEY `con_de` (`con_de`),
  FULLTEXT KEY `con_en` (`con_en`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_search`
--

LOCK TABLES `cc_search` WRITE;
/*!40000 ALTER TABLE `cc_search` DISABLE KEYS */;
INSERT INTO `cc_search` VALUES (1,'Herzlich willkommen im concise-wms','Welcome to concise-wms');
/*!40000 ALTER TABLE `cc_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cc_search_strings`
--

DROP TABLE IF EXISTS `cc_search_strings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_search_strings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search_string` varchar(256) COLLATE utf8_general_ci NOT NULL,
  `results` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_sessions`
--

DROP TABLE IF EXISTS `cc_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_sessions` (
  `id` varchar(32) COLLATE utf8_general_ci NOT NULL,
  `lastUpdated` int(11) NOT NULL DEFAULT '0',
  `start` int(11) NOT NULL DEFAULT '0',
  `value` varchar(65000) COLLATE utf8_general_ci NOT NULL,
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_stats`
--

DROP TABLE IF EXISTS `cc_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_stats` (
  `page_id` int(11) NOT NULL,
  `visits_total` int(11) NOT NULL,
  `visits_lastmon` int(11) NOT NULL,
  `visits_lastyear` int(11) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cc_user`
--

DROP TABLE IF EXISTS `cc_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_user` (
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `password` char(64) COLLATE utf8_general_ci NOT NULL,
  `salt` varchar(9) COLLATE utf8_general_ci NOT NULL,
  `group` varchar(64) COLLATE utf8_general_ci NOT NULL DEFAULT 'guest',
  `own_groups` varchar(256) COLLATE utf8_general_ci NOT NULL,
  `author_name` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `alias` varchar(32) COLLATE utf8_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `gender` VARCHAR(1) COLLATE utf8_general_ci NOT NULL,
  `title` VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
  `last_name` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `first_name` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `street` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `zip_code` VARCHAR(5) COLLATE utf8_general_ci NOT NULL,
  `city` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `country` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `phone` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `company` VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_log` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `lang` varchar(3) COLLATE utf8_general_ci NOT NULL,
  `at_skin` varchar(20) COLLATE utf8_general_ci NOT NULL,
  `logID` varchar(200) COLLATE utf8_general_ci NOT NULL,
  `newsletter` int(1) NOT NULL DEFAULT '0',
  `auth_code` varchar(200) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=2;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cc_user`
--

LOCK TABLES `cc_user` WRITE;
/*!40000 ALTER TABLE `cc_user` DISABLE KEYS */;
INSERT INTO `cc_user` VALUES (1,'admin','10478b70488befcd369e4769e552b434f0aa9081fe1af42c695a2759e72fb893','ccSalt#01','admin','','','','admin@concise-wms.cw','m','','','','','','','','','',NOW(),'0000-00-00 00:00:00',1,'de','','',0,'');
/*!40000 ALTER TABLE `cc_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
