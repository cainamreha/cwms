-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 14. Nov 2016 um 15:37
-- Server Version: 5.6.21
-- PHP-Version: 7.0.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `concise-onepage`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_articles`
--

DROP TABLE IF EXISTS `cc_articles`;
CREATE TABLE IF NOT EXISTS `cc_articles` (
`id` int(11) NOT NULL,
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
  `object1` text NOT NULL,
  `object2` text NOT NULL,
  `object3` text NOT NULL,
  `header_de` varchar(300) NOT NULL,
  `teaser_de` text NOT NULL,
  `text_de` mediumtext NOT NULL,
  `tags_de` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_articles_categories`
--

DROP TABLE IF EXISTS `cc_articles_categories`;
CREATE TABLE IF NOT EXISTS `cc_articles_categories` (
`cat_id` int(11) NOT NULL,
  `parent_cat` int(11) NOT NULL DEFAULT '0',
  `sort_id` int(11) NOT NULL,
  `group` varchar(256) NOT NULL DEFAULT 'public',
  `group_edit` text NOT NULL,
  `order_opt` tinyint(1) NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `rating` tinyint(1) NOT NULL DEFAULT '0',
  `image` text NOT NULL,
  `target_page` smallint(6) NOT NULL,
  `category_de` varchar(64) NOT NULL,
  `cat_teaser_de` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_badlogin`
--

DROP TABLE IF EXISTS `cc_badlogin`;
CREATE TABLE IF NOT EXISTS `cc_badlogin` (
`id` int(11) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `triedUsername` varchar(100) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_bannedip`
--

DROP TABLE IF EXISTS `cc_bannedip`;
CREATE TABLE IF NOT EXISTS `cc_bannedip` (
`id` int(11) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `setAt` int(11) NOT NULL,
  `until` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_comments`
--

DROP TABLE IF EXISTS `cc_comments`;
CREATE TABLE IF NOT EXISTS `cc_comments` (
`id` int(11) NOT NULL,
  `table` varchar(32) NOT NULL,
  `entry_id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `author` varchar(200) NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `comment` text NOT NULL,
  `email` varchar(300) NOT NULL,
  `url` varchar(300) NOT NULL,
  `gravatar` tinyint(1) NOT NULL DEFAULT '0',
  `notify` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_foot`
--

DROP TABLE IF EXISTS `cc_contents_foot`;
CREATE TABLE IF NOT EXISTS `cc_contents_foot` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_foot`
--

INSERT INTO `cc_contents_foot` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '', '', ''),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_foot_preview`
--

DROP TABLE IF EXISTS `cc_contents_foot_preview`;
CREATE TABLE IF NOT EXISTS `cc_contents_foot_preview` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_foot_preview`
--

INSERT INTO `cc_contents_foot_preview` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '', '', ''),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_head`
--

DROP TABLE IF EXISTS `cc_contents_head`;
CREATE TABLE IF NOT EXISTS `cc_contents_head` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_head`
--

INSERT INTO `cc_contents_head` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '<>main<>1<><>0<><>1<>0<>0<>1<>0<>0<>0<>0', 'menu', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}'),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_head_preview`
--

DROP TABLE IF EXISTS `cc_contents_head_preview`;
CREATE TABLE IF NOT EXISTS `cc_contents_head_preview` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_head_preview`
--

INSERT INTO `cc_contents_head_preview` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '<>main<>1<><>0<><>1<>0<>0<>1<>0<>0<>0<>0', 'menu', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}'),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_left`
--

DROP TABLE IF EXISTS `cc_contents_left`;
CREATE TABLE IF NOT EXISTS `cc_contents_left` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_left`
--

INSERT INTO `cc_contents_left` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '', '', ''),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_left_preview`
--

DROP TABLE IF EXISTS `cc_contents_left_preview`;
CREATE TABLE IF NOT EXISTS `cc_contents_left_preview` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_left_preview`
--

INSERT INTO `cc_contents_left_preview` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '', '', ''),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_main`
--

DROP TABLE IF EXISTS `cc_contents_main`;
CREATE TABLE IF NOT EXISTS `cc_contents_main` (
  `page_id` int(11) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL DEFAULT 'text',
  `styles-con1` text NOT NULL,
  `con2_de` mediumtext NOT NULL,
  `type-con2` varchar(50) NOT NULL DEFAULT 'text',
  `styles-con2` text NOT NULL,
  `con3_de` mediumtext NOT NULL,
  `type-con3` varchar(50) NOT NULL,
  `styles-con3` text NOT NULL,
  `con4_de` mediumtext NOT NULL,
  `type-con4` varchar(50) NOT NULL,
  `styles-con4` text NOT NULL,
  `con5_de` mediumtext NOT NULL,
  `type-con5` varchar(50) NOT NULL,
  `styles-con5` text NOT NULL,
  `con6_de` mediumtext NOT NULL,
  `type-con6` varchar(50) NOT NULL,
  `styles-con6` text NOT NULL,
  `con7_de` mediumtext NOT NULL,
  `type-con7` varchar(50) NOT NULL,
  `styles-con7` text NOT NULL,
  `con8_de` mediumtext NOT NULL,
  `type-con8` varchar(50) NOT NULL,
  `styles-con8` text NOT NULL,
  `con9_de` mediumtext NOT NULL,
  `type-con9` varchar(50) NOT NULL,
  `styles-con9` text NOT NULL,
  `con10_de` mediumtext NOT NULL,
  `type-con10` varchar(50) NOT NULL,
  `styles-con10` text NOT NULL,
  `con11_de` mediumtext NOT NULL,
  `type-con11` varchar(50) NOT NULL,
  `styles-con11` text NOT NULL,
  `con12_de` mediumtext NOT NULL,
  `type-con12` varchar(50) NOT NULL,
  `styles-con12` text NOT NULL,
  `con13_de` mediumtext NOT NULL,
  `type-con13` varchar(50) NOT NULL,
  `styles-con13` text NOT NULL,
  `con14_de` mediumtext NOT NULL,
  `type-con14` varchar(50) NOT NULL,
  `styles-con14` text NOT NULL,
  `con15_de` mediumtext NOT NULL,
  `type-con15` varchar(50) NOT NULL,
  `styles-con15` text NOT NULL,
  `con16_de` mediumtext NOT NULL,
  `type-con16` varchar(50) NOT NULL,
  `styles-con16` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_main`
--

INSERT INTO `cc_contents_main` (`page_id`, `con1_de`, `type-con1`, `styles-con1`, `con2_de`, `type-con2`, `styles-con2`, `con3_de`, `type-con3`, `styles-con3`, `con4_de`, `type-con4`, `styles-con4`, `con5_de`, `type-con5`, `styles-con5`, `con6_de`, `type-con6`, `styles-con6`, `con7_de`, `type-con7`, `styles-con7`, `con8_de`, `type-con8`, `styles-con8`, `con9_de`, `type-con9`, `styles-con9`, `con10_de`, `type-con10`, `styles-con10`, `con11_de`, `type-con11`, `styles-con11`, `con12_de`, `type-con12`, `styles-con12`, `con13_de`, `type-con13`, `styles-con13`, `con14_de`, `type-con14`, `styles-con14`, `con15_de`, `type-con15`, `styles-con15`, `con16_de`, `type-con16`, `styles-con16`) VALUES
(1, '<>#section-home\r\n#section-service\r\n#section-impressionen\r\n#section-contact\r\n#section-footer<>Home\r\nService\r\nImpressionen\r\nKontakt\r\nÖffnungszeiten<>nav<>1<>1<>0<>1<>2<>0<>0<>1', 'listmenu', '{"cols":"full","hide":0,"id":"","class":"navbar-large navbar-scrollspy no-gutter","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"3","ctr":"0","row":"0","div":"0","secid":"section-home","ctrid":"","rowid":"","divid":"","secclass":"container-full-width section-fullscreen bg-fixed no-gutter","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Homegallery-fullsize/fader//1/1/1//1/1/1200/3500/1/num', 'gallery', '{"cols":"","hide":0,"id":"","class":"no-gutter","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h2>Unsere Services</h2>', 'text', '{"id":"","hide":0,"class":"text-center","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":"","colssm":"","colsxs":"","pt":"","pb":"","pl":"","pr":"","sec":"1","div":"0","secid":"section-service","ctrid":"","rowid":"","divid":"","secclass":"cs-style-triangle-top cs-style-triangle-bottom","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"0":{"1":"<h3>Service-1<\\/h3>","2":"<h3>Service-2<\\/h3>","3":"<h3>Service-3<\\/h3>"},"1":{"1":"<span class=\\"cc-iconcontainer ci-icon-wrap ci-icon-effect-2a\\"><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><span class=\\"icons icon-pie-chart\\" role=\\"icon\\"><!-- cc-icon --><\\/span><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><\\/span>","2":"<span class=\\"cc-iconcontainer ci-icon-wrap ci-icon-effect-2a\\"><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><span class=\\"icons icon-star\\" role=\\"icon\\"><!-- cc-icon --><\\/span><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><\\/span>","3":"<span class=\\"cc-iconcontainer ci-icon-wrap ci-icon-effect-2a\\"><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><span class=\\"icons icon-gift\\" role=\\"icon\\"><!-- cc-icon --><\\/span><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><\\/span>"},"2":{"1":"","2":"","3":""},"3":{"1":"","2":"","3":""},"4":{"1":"top","2":"top","3":"top"},"5":{"1":"","2":"","3":""},"6":{"1":"third","2":"third","3":"third"},"7":{"1":"","2":"","3":""},"8":{"1":"","2":"","3":""},"9":{"1":"center","2":"center","3":"center"},"10":{"1":"","2":"","3":""},"11":{"1":"wow fadeInLeftBig","2":"wow fadeInUpBig","3":"wow fadeInRightBig"},"12":"","13":"","wow":{"wowAni":"bounceIn","wowAniOut":"bounceOutDown","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""},"14":"","15":""}', 'cards', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"60","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h2>Impressionen</h2>', 'text', '{"cols":"12","hide":0,"id":"","class":"text-center","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"1","div":"0","secid":"section-impressionen","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Portfolio/portfolio3//1/1/1/3/1/1/1200/3500/1/num/{"wowAni":"","wowAniOut":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'gallery', '{"cols":"full","hide":0,"id":"","class":"cc-skin-primary","style":"","colssm":"","colsxs":"","mt":"","mb":"90","ml":"","mr":"","pt":"0","pb":"0","pl":"","pr":"","sec":"0","ctr":"0","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"grid-nogap","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<p><span class="lead text-uppercase">Unser Slogan ist unser Motto</span></p>', 'text', '{"cols":"full","hide":0,"id":"","class":"cc-hero","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"1","div":"0","secid":"slogan","ctrid":"","rowid":"","divid":"","secclass":"bg-fixed section-halfscreen section-brand-default bg-blend-soft-light cs-style-triangle-inset-top","ctrclass":"container-centered","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h3>Unsere Anschrift</h3>\r\n<p>Firmenname <br /> Firmengasse 1 <br /> 12345 Firmenhausen</p><p>Tel.: 098 123456</p>\r\n<p>&nbsp;<span class="cc-iconcontainer" style="font-size: 128px;"><span class="icon-selection-fill">&nbsp;</span><span class="icons icon-group" role="icon"><!-- cc-icon --></span><span class="icon-selection-fill">&nbsp;</span></span>&nbsp;</p>', 'text', '{"cols":"half","hide":0,"id":"","class":"text-center text-light","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"50","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"1","div":"0","secid":"section-contact","ctrid":"","rowid":"","divid":"","secclass":"bg-primary section-brand-alternative cs-style-doublediagonal","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"foa":0,"title":0,"name":1,"fname":1,"com":0,"mail":1,"phone":0,"subj":0,"subji":"","mes":1,"copy":0,"cap":0,"form":"block","lab":1,"leg":0}', 'cform', '{"cols":"half","hide":0,"id":"","class":"bg-primary-fade","style":"","colssm":"","colsxs":"","mt":"0","mb":"0","ml":"","mr":"","pt":"90","pb":"90","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Baden-Baden<><><><><>0', 'gmap', '{"cols":"8","hide":0,"id":"","class":"no-gutter","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"container-fluid container-full-width","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h3><span class="cc-iconcontainer" style="font-size: 128px;"><span class="icon-selection-fill">&nbsp;</span><span class="icons icon-map-marker" role="icon"><!-- cc-icon --></span><span class="icon-selection-fill">&nbsp;</span></span>&nbsp;&nbsp;</h3>\r\n<h3>Anreise&nbsp;</h3>\r\n<p>Sie finden uns im Herzen von Baden-Baden.</p>\r\n<p>&nbsp;</p>', 'text', '{"cols":"4","hide":0,"id":"","class":"text-center","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"30","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"footer","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"fb":1,"tw":1,"go":0,"li":0,"pi":0,"ml":1,"ph":1,"fbl":"http:\\/\\/www.hermani-web.de","twl":"http:\\/\\/www.hermani-web.de","gol":"","lil":"","pil":"","mll":"mail@hermani-web.de","phl":"+49 (0)98-123456","sort":["fb","tw","go","li","pi","ml","ph"],"fix":0,"btn":"def","aln":"h"}', 'social-links', '{"cols":"third","hide":0,"id":"","class":"margin-top-md text-center center-block","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h3>Öffnungszeiten</h3>\r\n<table class="table table-responsive table-bordered">\r\n<tbody>\r\n<tr>\r\n<td>Mo. - Fr.</td>\r\n<td>9:00 - 17:00 Uhr</td>\r\n</tr>\r\n<tr>\r\n<td>Sa.</td>\r\n<td><span>9:00 - 12:00 Uhr</span></td>\r\n</tr>\r\n<tr>\r\n<td>So.</td>\r\n<td>geschlossen</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>Termine vereinbaren wir gerne auf Anfrage</p>', 'text', '{"cols":"third","hide":0,"id":"","class":"flex-col","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"4","ctr":"1","row":"1","div":"0","secid":"section-footer","ctrid":"footer","rowid":"","divid":"","secclass":"bg-dark","ctrclass":"","rowclass":"row-flex","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"fb":1,"tw":1,"go":1,"li":0,"pi":0,"fix":0,"btn":"pri","aln":"h","alt":""}', 'share', '{"cols":"third","hide":0,"id":"","class":"flex-col flex-center-vertical","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<>foot<>1<><>0<><>2<><>0<>0<><>0<>1<>0', 'menu', '{"cols":"third","hide":0,"id":"","class":"flex-col flex-center-vertical","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Copyright © 2016 Mein Unternehmen', 'text', '{"cols":"full","hide":0,"id":"","class":"well well-lg text-center text-dark","style":"","colssm":"","colsxs":"","mt":"0","mb":"0","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"container-full-width","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}'),
(-1007, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', 'userpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1006, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '{"regsubject":"Registrierung bei {#domain}","regthank":"herzlich willkommen bei {#domain}.","regmessage":"<p>Sie erhalten diese E-Mail zur im Zusammenhang mit Ihrer Registrierung auf {#domain}.<\\/p>","regtextshop":"<p>Die Registrierung erleichtert Ihnen den Bestellvorgang und Sie werden ggf. über unseren Newsletter auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regmail":"<p>Sie werden automatisch über Neuerungen informiert.<\\/p>","reguser":"<p>Vielen Dank für Ihr Interesse!<br \\/><br \\/>In Kürze sollten Sie eine automatische E-Mail mit einem Link zur Bestätigung über die kostenfreie Registrierung erhalten.<br \\/>Bitte schließen Sie die Registrierung durch&nbsp;Klick auf den entsprechenden Link in der Bestätigungsmail ab.<\\/p>","regnewsl":"<p>Vielen Dank für die Anmeldung zum Newsletter! Sie werden fortan über Neuerungen auf dem Laufenden gehalten.<\\/p>","unregnewsl":"<p>Die Abmeldung vom Newsletter ist erfolgt.<br \\/><br \\/>Wir bedauern, dass Ihre Entscheidung, den Newsletter nicht mehr erhalten zu wollen. Um Spam zu vermeiden, wird der Newsletter nur intern verwendet und in maßvollem Rhythmus verschickt.<br \\/>Natürlich besteht weiterhin die Möglichkeit, über unsere Website auf dem Laufenden zu bleiben.<br \\/>Vielen Dank für Ihr Interesse und Ihre Unterstützung!<\\/p>","regtextnewsl":"<p>Sie werden fortan über Neuerungen oder anstehende Termine auf dem Laufenden gehalten.<br \\/>Sollten Sie den Newsletter nicht mehr erhalten wollen, können Sie ihn jederzeit über den Link unten wieder abbestellen.<br \\/><br \\/>Vielen Dank für Ihr Interesse an unserem Newsletter.<\\/p>","regtextguest":"<p>Die Registrierung erlaubt Ihnen den Zugang zu erweiterten Premium-Inhalten. Über unseren Newsletter werden Sie, sofern erwünscht, auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regtextoptin":"<p>Für die Aktivierung Ihres Benutzerkontos benötigen wir jetzt noch Ihre Bestätigung über den folgenden Link:<\\/p>\\r\\n<p><a href=\\"{#reglink}\\">Registrierung bestätigen und abschließen<\\/a>.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Sollte Ihnen der Inhalt dieser E-Mail nichts sagen bzw. ein entsprechender Eintrag nicht von Ihnen selbst vorgenommen worden sein, ignorieren Sie bitte diese E-Mail. <br \\/> In diesem Fall erfolgt keine Aktivierung Ihres Benutzerkontos.<\\/p>\\r\\n<p>Die Registrierung ist kostenfrei und kann durch Klick auf den entsprechenden Link in der jeweils letzten E-Mail jederzeit wieder rückgängig gemacht werden.<\\/p>\\r\\n<p>Weitere Informationen unter:<\\/p>\\r\\n<p>&nbsp;<\\/p>"}', 'regpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1005, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '<div id="logoutForm" class="form col-md-12 margin-top-md margin-bottom-md">\r\n<div class="top">&nbsp;</div>\r\n<div class="center"><form><fieldset><legend> {#s_header:userpage} </legend>\r\n<h2 class="logout">Logout<span class="logout icons icon-logout"><br /></span></h2>\r\n<p class="alert alert-success notice success">{#s_text:logout}</p>\r\n<p><a class="{#t_class:btn} {#t_class:btnpri} formbutton ok right" href="{#root}/login.html"><span class="{#t_icons:icons} {#t_icons:icon}{#t_icons:signin} icon-left"></span> {#s_text:relog}</a></p>\r\n</fieldset></form></div>\r\n<div class="bottom">&nbsp;</div>\r\n</div>', 'logoutpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"half","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1004, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', 'searchpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1003, '', 'text', '{"cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":"","colssm":"","colsxs":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":""}', '{"er":"<p>{#s_text:error}<\\/p>","nf":"<p>{#s_header:notfound}<\\/p>","fb":"<p>{#s_text:errorforbidden}<\\/p>","sv":"<p>{#s_text:errorserver}<\\/p>","st":"<p>{#s_text:errorstatus}<\\/p>","ac":"<p>{#s_text:erroraccess}<\\/p>","nl":"<p>{#s_text:erroraccess2}<\\/p>","to":"<p>{#s_notice:errortimeout}<\\/p>","nn":"<p>{#s_notice:nofeed}<\\/p>"}', 'errorpage', '{"cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":"","colssm":"","colsxs":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1002, '<h1>Benutzerseite</h1>', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', 'loginpage', '{"row":"1","cols":"half","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(2, '<h1>Impressum</h1>', 'text', '{"id":"","hide":0,"class":"","style":"","cols":"full","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<div class="panel panel-default">\r\n<div class="panel-body">\r\n<p><strong>Firma</strong></p>\r\n<p>Straße / Hausnummer <br /> PLZ Ort</p>\r\n<p>Tel.: +49 <br /> E-Mail:&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>Verantwortlich:</p>\r\n<p>Vorname Name</p>\r\n<p>&nbsp;</p>\r\n<p>Finanzamt / Registergericht: <br /> Steuernr.: <br /> USt-IDNr.:&nbsp;</p>\r\n</div>\r\n</div>', 'text', '{"id":"","hide":0,"class":"","style":"","cols":"full","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<p>&nbsp;</p>\r\n<h3>Webdesign und Umsetzung</h3>\r\n<p><a class="extLink" href="http://www.hermani-webrealisierung.de"><span class="bold">hermani webrealisierung</span> Baden-Baden</a></p>\r\n<hr />\r\n<h3>Bildnachweis</h3>\r\n<p>Bilderquellen:&nbsp;</p>\r\n<hr />\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', 'text', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h1>Disclaimer</h1>\r\n<div class="panel panel-default">\r\n<div class="panel-body">\r\n<h2>1. Haftungsbeschränkung</h2>\r\n<p>Verantwortlich für dieses Informationsangebot ist der Herausgeber. Die Informationen auf dieser Website wurden mit großer Sorgfalt zusammengestellt. Für die Richtigkeit und Vollständigkeit kann gleichwohl keine Gewähr übernommen werden. Aus diesem Grund ist jegliche Haftung für eventuelle Schäden im Zusammenhang mit der Nutzung des Informationsangebots ausgeschlossen. Durch die bloße Nutzung dieser Website kommt keinerlei Vertragsverhältnis zwischen Nutzer und Anbieter/Herausgeber zustande.</p>\r\n<h2>2. Hinweis zum Urheberrecht</h2>\r\n<p>Der gesamte Inhalt dieser Website unterliegt dem Urheberrecht. Unerlaubte Verwendung, Reproduktion oder Wiedergabe des Inhalts oder von Teilen des Inhalts ist untersagt. Wegen einer Erlaubnis zur Nutzung des Inhalts wenden Sie sich bitte an den Herausgeber.</p>\r\n<h2>3. Hinweis zu externen Links</h2>\r\n<p>Soweit von dieser Website auf andere Websites Links gelegt sind, wird darauf hingewiesen, dass keinerlei Einfluss auf die Gestaltung und die Inhalte der gelinkten Seiten besteht und sich deren Inhalt nicht zu eigen gemacht wird. Dies gilt für alle auf dieser Seite ausgebrachten, externen Links und für alle Inhalte der Seiten, zu denen Werbemittel (z.B. Banner, Textanzeigen, Videoanzeigen) führen. Für verlinkte Seiten gilt, dass rechtswidrige Inhalte zum Zeitpunkt der Verlinkung nicht erkennbar waren. Die Links werden regelmäßig auf rechtswidrige Inhalte überprüft und bei Rechtsverletzungen unverzüglich entfernt.</p>\r\n<h2>4. Datenschutz</h2>\r\n<h3>a) Google Adsense</h3>\r\n<p>Soweit Google Adsense, einen Webanzeigendienst der Google Inc., USA ("Google"), auf dieser Website Werbung (Textanzeigen, Banner etc.) schaltet, speichert Ihr Browser eventuell ein von Google Inc. oder Dritten gesendetes Cookie. Die in dem Cookie gespeicherten Informationen können durch Google Inc. oder auch Dritte aufgezeichnet, gesammelt und ausgewertet werden. Darüber hinaus verwendet Google Adsense zur Sammlung von Informationen auch sog. "WebBacons"(kleine unsichtbare Grafiken), durch deren Verwendung einfache Aktionen wie der Besucherverkehr auf der Webseite aufgezeichnet, gesammelt und ausgewertet werden können. Die durch den Cookie und/oder Web Bacon erzeugten Informationen über Ihre Nutzung dieser Website werden an einen Server von Google in den USA übertragen und dort gespeichert. Google benutzt die so erhaltenen Informationen, um eine Auswertung Ihres Nutzungsverhaltens im Hinblick auf die AdSense-Anzeigen durchzuführen. Google wird diese Informationen gegebenenfalls auch an Dritte übertragen, sofern dies gesetzlich vorgeschrieben ist oder soweit Dritte diese Daten im Auftrag von Google verarbeiten. Ihre IP-Adresse wird von Google nicht mit anderen von Google gespeicherten Daten in Verbindung gebracht. Sie können das Speichern von Cookies auf Ihrer Festplatte und die Anzeige von Web Bacons verhindern. Dazu müssen Sie in Ihren Browser-Einstellungen "keine Cookies akzeptieren" wählen (Im Internet-Explorer unter "Extras / Internetoptionen / Datenschutz / Einstellung", bei Firefox unter "Extras / Einstellungen / Datenschutz / Cookies").</p>\r\n<h3>b) Google Analytics</h3>\r\n<p>Soweit diese Website Google Analytics, einen Webanalysedienst der Google Inc. ("Google") benutzt, speichert Ihr Browser eventuell ein von Google Inc. oder Dritten gesendetes Cookie, dass eine Analyse Ihrer Nutzung dieser Website ermöglicht. Die so gesammelten Informationen (einschließlich Ihrer IP-Adresse) werden an einen Server von Google in den USA übertragen und dort gespeichert. Google nutzt diese Informationen, um Ihre Nutzung dieser Website auszuwerten, um Reports über die Websiteaktivitäten für die Websitebetreiber zusammenzustellen und um weitere mit der Websitenutzung und der Internetnutzung verbundene Dienstleistungen zu erbringen. Google wird diese Informationen gegebenenfalls auch an Dritte übertragen, sofern dies gesetzlich vorgeschrieben oder soweit Dritte diese Daten im Auftrag von Google verarbeiten. Google wird in keinem Fall Ihre IP-Adresse mit anderen von Google gespeicherten Daten in Verbindung bringen. Die Installation der Cookies können Sie verhindern. Dazu müssen Sie in Ihren Browser-Einstellungen "keine Cookies akzeptieren" wählen (Im Internet-Explorer unter "Extras / Internetoptionen / Datenschutz / Einstellung", bei Firefox unter "Extras / Einstellungen / Datenschutz / Cookies"). Es wird darauf hingewiesen, dass Sie in diesem Fall gegebenenfalls nicht sämtliche Funktionen dieser Website voll umfänglich nutzen können. Durch die Nutzung dieser Website erklären Sie sich mit der Bearbeitung der über Sie erhobenen Daten durch Google in der zuvor beschriebenen Art und Weise und zu dem zuvor benannten Zweck einverstanden.</p>\r\n<h3>c) Sonstige Produkte und Dienste</h3>\r\n<p>Soweit diese Website Anzeigen schaltet, die unter Punkt 4a) nicht näher bezeichnete sind, nutzen die Anzeigen schaltenden Unternehmen möglicherweise Informationen (dies schließt nicht Ihren Namen, Ihre Adresse, E-Mail-Adresse oder Telefonnummer ein) zu Ihren Besuchen dieser und anderer Websites, damit Anzeigen zu Produkten und Diensten geschaltet werden können, die Sie interessieren.</p>\r\n<h3>d) Sonstiges</h3>\r\n<p>Es wird darauf hingewiesen, dass hinsichtlich der Datenübertragung über das Internet (z.B. bei der Kommunikation per E-Mail) keine sichere Übertragung gewährleistet ist. Empfindliche Daten sollten daher entweder gar nicht oder nur über eine sichere Verbindung (SSL) übertragen werden.</p>\r\n</div>\r\n</div>', 'text', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_main_preview`
--

DROP TABLE IF EXISTS `cc_contents_main_preview`;
CREATE TABLE IF NOT EXISTS `cc_contents_main_preview` (
  `page_id` int(11) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL DEFAULT 'text',
  `styles-con1` text NOT NULL,
  `con2_de` mediumtext NOT NULL,
  `type-con2` varchar(50) NOT NULL DEFAULT 'text',
  `styles-con2` text NOT NULL,
  `con3_de` mediumtext NOT NULL,
  `type-con3` varchar(50) NOT NULL,
  `styles-con3` text NOT NULL,
  `con4_de` mediumtext NOT NULL,
  `type-con4` varchar(50) NOT NULL,
  `styles-con4` text NOT NULL,
  `con5_de` mediumtext NOT NULL,
  `type-con5` varchar(50) NOT NULL,
  `styles-con5` text NOT NULL,
  `con6_de` mediumtext NOT NULL,
  `type-con6` varchar(50) NOT NULL,
  `styles-con6` text NOT NULL,
  `con7_de` mediumtext NOT NULL,
  `type-con7` varchar(50) NOT NULL,
  `styles-con7` text NOT NULL,
  `con8_de` mediumtext NOT NULL,
  `type-con8` varchar(50) NOT NULL,
  `styles-con8` text NOT NULL,
  `con9_de` mediumtext NOT NULL,
  `type-con9` varchar(50) NOT NULL,
  `styles-con9` text NOT NULL,
  `con10_de` mediumtext NOT NULL,
  `type-con10` varchar(50) NOT NULL,
  `styles-con10` text NOT NULL,
  `con11_de` mediumtext NOT NULL,
  `type-con11` varchar(50) NOT NULL,
  `styles-con11` text NOT NULL,
  `con12_de` mediumtext NOT NULL,
  `type-con12` varchar(50) NOT NULL,
  `styles-con12` text NOT NULL,
  `con13_de` mediumtext NOT NULL,
  `type-con13` varchar(50) NOT NULL,
  `styles-con13` text NOT NULL,
  `con14_de` mediumtext NOT NULL,
  `type-con14` varchar(50) NOT NULL,
  `styles-con14` text NOT NULL,
  `con15_de` mediumtext NOT NULL,
  `type-con15` varchar(50) NOT NULL,
  `styles-con15` text NOT NULL,
  `con16_de` mediumtext NOT NULL,
  `type-con16` varchar(50) NOT NULL,
  `styles-con16` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_main_preview`
--

INSERT INTO `cc_contents_main_preview` (`page_id`, `con1_de`, `type-con1`, `styles-con1`, `con2_de`, `type-con2`, `styles-con2`, `con3_de`, `type-con3`, `styles-con3`, `con4_de`, `type-con4`, `styles-con4`, `con5_de`, `type-con5`, `styles-con5`, `con6_de`, `type-con6`, `styles-con6`, `con7_de`, `type-con7`, `styles-con7`, `con8_de`, `type-con8`, `styles-con8`, `con9_de`, `type-con9`, `styles-con9`, `con10_de`, `type-con10`, `styles-con10`, `con11_de`, `type-con11`, `styles-con11`, `con12_de`, `type-con12`, `styles-con12`, `con13_de`, `type-con13`, `styles-con13`, `con14_de`, `type-con14`, `styles-con14`, `con15_de`, `type-con15`, `styles-con15`, `con16_de`, `type-con16`, `styles-con16`) VALUES
(1, '<>#section-home\r\n#section-service\r\n#section-impressionen\r\n#section-contact\r\n#section-footer<>Home\r\nService\r\nImpressionen\r\nKontakt\r\nÖffnungszeiten<>nav<>1<>1<>0<>1<>2<>0<>0<>1', 'listmenu', '{"cols":"full","hide":0,"id":"","class":"navbar-large navbar-scrollspy no-gutter","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"3","ctr":"0","row":"0","div":"0","secid":"section-home","ctrid":"","rowid":"","divid":"","secclass":"container-full-width section-fullscreen bg-fixed no-gutter","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Homegallery-fullsize/fader//1/1/1//1/1/1200/3500/1/num', 'gallery', '{"cols":"","hide":0,"id":"","class":"no-gutter","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h2>Unsere Services</h2>', 'text', '{"id":"","hide":0,"class":"text-center","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":"","colssm":"","colsxs":"","pt":"","pb":"","pl":"","pr":"","sec":"1","div":"0","secid":"section-service","ctrid":"","rowid":"","divid":"","secclass":"cs-style-triangle-top cs-style-triangle-bottom","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"0":{"1":"<h3>Service-1<\\/h3>","2":"<h3>Service-2<\\/h3>","3":"<h3>Service-3<\\/h3>"},"1":{"1":"<span class=\\"cc-iconcontainer ci-icon-wrap ci-icon-effect-2a\\"><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><span class=\\"icons icon-pie-chart\\" role=\\"icon\\"><!-- cc-icon --><\\/span><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><\\/span>","2":"<span class=\\"cc-iconcontainer ci-icon-wrap ci-icon-effect-2a\\"><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><span class=\\"icons icon-star\\" role=\\"icon\\"><!-- cc-icon --><\\/span><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><\\/span>","3":"<span class=\\"cc-iconcontainer ci-icon-wrap ci-icon-effect-2a\\"><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><span class=\\"icons icon-gift\\" role=\\"icon\\"><!-- cc-icon --><\\/span><span class=\\"icon-selection-fill\\">&nbsp;<\\/span><\\/span>"},"2":{"1":"","2":"","3":""},"3":{"1":"","2":"","3":""},"4":{"1":"top","2":"top","3":"top"},"5":{"1":"","2":"","3":""},"6":{"1":"third","2":"third","3":"third"},"7":{"1":"","2":"","3":""},"8":{"1":"","2":"","3":""},"9":{"1":"center","2":"center","3":"center"},"10":{"1":"","2":"","3":""},"11":{"1":"wow fadeInLeftBig","2":"wow fadeInUpBig","3":"wow fadeInRightBig"},"12":"","13":"","wow":{"wowAni":"bounceIn","wowAniOut":"bounceOutDown","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""},"14":"","15":""}', 'cards', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"60","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h2>Impressionen</h2>', 'text', '{"cols":"12","hide":0,"id":"","class":"text-center","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"1","div":"0","secid":"section-impressionen","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Portfolio/portfolio3//1/1/1/3/1/1/1200/3500/1/num/{"wowAni":"","wowAniOut":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'gallery', '{"cols":"full","hide":0,"id":"","class":"cc-skin-primary","style":"","colssm":"","colsxs":"","mt":"","mb":"90","ml":"","mr":"","pt":"0","pb":"0","pl":"","pr":"","sec":"0","ctr":"0","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"grid-nogap","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<p><span class="lead text-uppercase">Unser Slogan ist unser Motto</span></p>', 'text', '{"cols":"full","hide":0,"id":"","class":"cc-hero","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"1","div":"0","secid":"slogan","ctrid":"","rowid":"","divid":"","secclass":"bg-fixed section-halfscreen section-brand-default bg-blend-soft-light cs-style-triangle-inset-top","ctrclass":"container-centered","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h3>Unsere Anschrift</h3>\r\n<p>Firmenname <br /> Firmengasse 1 <br /> 12345 Firmenhausen</p><p>Tel.: 098 123456</p>\r\n<p>&nbsp;<span class="cc-iconcontainer" style="font-size: 128px;"><span class="icon-selection-fill">&nbsp;</span><span class="icons icon-group" role="icon"><!-- cc-icon --></span><span class="icon-selection-fill">&nbsp;</span></span>&nbsp;</p>', 'text', '{"cols":"half","hide":0,"id":"","class":"text-center text-light","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"50","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"1","div":"0","secid":"section-contact","ctrid":"","rowid":"","divid":"","secclass":"bg-primary section-brand-alternative cs-style-doublediagonal","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"foa":0,"title":0,"name":1,"fname":1,"com":0,"mail":1,"phone":0,"subj":0,"subji":"","mes":1,"copy":0,"cap":0,"form":"block","lab":1,"leg":0}', 'cform', '{"cols":"half","hide":0,"id":"","class":"bg-primary-fade","style":"","colssm":"","colsxs":"","mt":"0","mb":"0","ml":"","mr":"","pt":"90","pb":"90","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Baden-Baden<><><><><>0', 'gmap', '{"cols":"8","hide":0,"id":"","class":"no-gutter","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"1","ctr":"1","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"container-fluid container-full-width","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h3><span class="cc-iconcontainer" style="font-size: 128px;"><span class="icon-selection-fill">&nbsp;</span><span class="icons icon-map-marker" role="icon"><!-- cc-icon --></span><span class="icon-selection-fill">&nbsp;</span></span>&nbsp;&nbsp;</h3>\r\n<h3>Anreise&nbsp;</h3>\r\n<p>Sie finden uns im Herzen von Baden-Baden.</p>\r\n<p>&nbsp;</p>', 'text', '{"cols":"4","hide":0,"id":"","class":"text-center","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"30","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"footer","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"fb":1,"tw":1,"go":0,"li":0,"pi":0,"ml":1,"ph":1,"fbl":"http:\\/\\/www.hermani-web.de","twl":"http:\\/\\/www.hermani-web.de","gol":"","lil":"","pil":"","mll":"mail@hermani-web.de","phl":"+49 (0)98-123456","sort":["fb","tw","go","li","pi","ml","ph"],"fix":0,"btn":"def","aln":"h"}', 'social-links', '{"cols":"third","hide":0,"id":"","class":"margin-top-md text-center center-block","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h3>Öffnungszeiten</h3>\r\n<table class="table table-responsive table-bordered">\r\n<tbody>\r\n<tr>\r\n<td>Mo. - Fr.</td>\r\n<td>9:00 - 17:00 Uhr</td>\r\n</tr>\r\n<tr>\r\n<td>Sa.</td>\r\n<td><span>9:00 - 12:00 Uhr</span></td>\r\n</tr>\r\n<tr>\r\n<td>So.</td>\r\n<td>geschlossen</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>Termine vereinbaren wir gerne auf Anfrage</p>', 'text', '{"cols":"third","hide":0,"id":"","class":"flex-col","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"4","ctr":"1","row":"1","div":"0","secid":"section-footer","ctrid":"footer","rowid":"","divid":"","secclass":"bg-dark","ctrclass":"","rowclass":"row-flex","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '{"fb":1,"tw":1,"go":1,"li":0,"pi":0,"fix":0,"btn":"pri","aln":"h","alt":""}', 'share', '{"cols":"third","hide":0,"id":"","class":"flex-col flex-center-vertical","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<>foot<>1<><>0<><>2<><>0<>0<><>0<>1<>0', 'menu', '{"cols":"third","hide":0,"id":"","class":"flex-col flex-center-vertical","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', 'Copyright © 2016 Mein Unternehmen', 'text', '{"cols":"full","hide":0,"id":"","class":"well well-lg text-center text-dark","style":"","colssm":"","colsxs":"","mt":"0","mb":"0","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"container-full-width","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}'),
(-1007, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', 'userpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1006, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '{"regsubject":"Registrierung bei {#domain}","regthank":"herzlich willkommen bei {#domain}.","regmessage":"<p>Sie erhalten diese E-Mail zur im Zusammenhang mit Ihrer Registrierung auf {#domain}.<\\/p>","regtextshop":"<p>Die Registrierung erleichtert Ihnen den Bestellvorgang und Sie werden ggf. über unseren Newsletter auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regmail":"<p>Sie werden automatisch über Neuerungen informiert.<\\/p>","reguser":"<p>Vielen Dank für Ihr Interesse!<br \\/><br \\/>In Kürze sollten Sie eine automatische E-Mail mit einem Link zur Bestätigung über die kostenfreie Registrierung erhalten.<br \\/>Bitte schließen Sie die Registrierung durch&nbsp;Klick auf den entsprechenden Link in der Bestätigungsmail ab.<\\/p>","regnewsl":"<p>Vielen Dank für die Anmeldung zum Newsletter! Sie werden fortan über Neuerungen auf dem Laufenden gehalten.<\\/p>","unregnewsl":"<p>Die Abmeldung vom Newsletter ist erfolgt.<br \\/><br \\/>Wir bedauern, dass Ihre Entscheidung, den Newsletter nicht mehr erhalten zu wollen. Um Spam zu vermeiden, wird der Newsletter nur intern verwendet und in maßvollem Rhythmus verschickt.<br \\/>Natürlich besteht weiterhin die Möglichkeit, über unsere Website auf dem Laufenden zu bleiben.<br \\/>Vielen Dank für Ihr Interesse und Ihre Unterstützung!<\\/p>","regtextnewsl":"<p>Sie werden fortan über Neuerungen oder anstehende Termine auf dem Laufenden gehalten.<br \\/>Sollten Sie den Newsletter nicht mehr erhalten wollen, können Sie ihn jederzeit über den Link unten wieder abbestellen.<br \\/><br \\/>Vielen Dank für Ihr Interesse an unserem Newsletter.<\\/p>","regtextguest":"<p>Die Registrierung erlaubt Ihnen den Zugang zu erweiterten Premium-Inhalten. Über unseren Newsletter werden Sie, sofern erwünscht, auf dem Laufenden gehalten.<br \\/><br \\/>Die Speicherung Ihrer Daten ist ausschließlich zu diesem Zweck bestimmt. Es erfolgt keine Weitergabe der Daten an Dritte.<br \\/>Sollten Sie beabsichtigen Ihre Daten dauerhaft zu löschen, so können Sie dies über den entsprechenden Link in der jeweils aktuellen E-Mail vornehmen.<br \\/><br \\/>Ihre Daten können Sie nach dem Einloggen jederzeit einsehen und bearbeiten.<\\/p>","regtextoptin":"<p>Für die Aktivierung Ihres Benutzerkontos benötigen wir jetzt noch Ihre Bestätigung über den folgenden Link:<\\/p>\\r\\n<p><a href=\\"{#reglink}\\">Registrierung bestätigen und abschließen<\\/a>.<\\/p>\\r\\n<p>&nbsp;<\\/p>\\r\\n<p>Sollte Ihnen der Inhalt dieser E-Mail nichts sagen bzw. ein entsprechender Eintrag nicht von Ihnen selbst vorgenommen worden sein, ignorieren Sie bitte diese E-Mail. <br \\/> In diesem Fall erfolgt keine Aktivierung Ihres Benutzerkontos.<\\/p>\\r\\n<p>Die Registrierung ist kostenfrei und kann durch Klick auf den entsprechenden Link in der jeweils letzten E-Mail jederzeit wieder rückgängig gemacht werden.<\\/p>\\r\\n<p>Weitere Informationen unter:<\\/p>\\r\\n<p>&nbsp;<\\/p>"}', 'regpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1005, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '<div id="logoutForm" class="form col-md-12 margin-top-md margin-bottom-md">\r\n<div class="top">&nbsp;</div>\r\n<div class="center"><form><fieldset><legend> {#s_header:userpage} </legend>\r\n<h2 class="logout">Logout<span class="logout icons icon-logout"><br /></span></h2>\r\n<p class="alert alert-success notice success">{#s_text:logout}</p>\r\n<p><a class="{#t_class:btn} {#t_class:btnpri} formbutton ok right" href="{#root}/login.html"><span class="{#t_icons:icons} {#t_icons:icon}{#t_icons:signin} icon-left"></span> {#s_text:relog}</a></p>\r\n</fieldset></form></div>\r\n<div class="bottom">&nbsp;</div>\r\n</div>', 'logoutpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"half","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1004, '', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', 'searchpage', '{"id":"","hide":0,"class":"","style":"","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1003, '', 'text', '{"cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":"","colssm":"","colsxs":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":""}', '{"er":"<p>{#s_text:error}<\\/p>","nf":"<p>{#s_header:notfound}<\\/p>","fb":"<p>{#s_text:errorforbidden}<\\/p>","sv":"<p>{#s_text:errorserver}<\\/p>","st":"<p>{#s_text:errorstatus}<\\/p>","ac":"<p>{#s_text:erroraccess}<\\/p>","nl":"<p>{#s_text:erroraccess2}<\\/p>","to":"<p>{#s_notice:errortimeout}<\\/p>","nn":"<p>{#s_notice:nofeed}<\\/p>"}', 'errorpage', '{"cols":"full","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":"","colssm":"","colsxs":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(-1002, '<h1>Benutzerseite</h1>', 'text', '{"id":"","hide":0,"class":"","style":"","ctr":"1","row":"1","cols":"full","mt":"","mb":"","ml":"","mr":""}', '', 'loginpage', '{"row":"1","cols":"half","hide":0,"id":"","class":"","style":"","mt":"","mb":"","ml":"","mr":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
(2, '<h1>Impressum</h1>', 'text', '{"id":"","hide":0,"class":"","style":"","cols":"full","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"1","row":"1","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<div class="panel panel-default">\r\n<div class="panel-body">\r\n<p><strong>Firma</strong></p>\r\n<p>Straße / Hausnummer <br /> PLZ Ort</p>\r\n<p>Tel.: +49 <br /> E-Mail:&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>Verantwortlich:</p>\r\n<p>Vorname Name</p>\r\n<p>&nbsp;</p>\r\n<p>Finanzamt / Registergericht: <br /> Steuernr.: <br /> USt-IDNr.:&nbsp;</p>\r\n</div>\r\n</div>', 'text', '{"id":"","hide":0,"class":"","style":"","cols":"full","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<p>&nbsp;</p>\r\n<h3>Webdesign und Umsetzung</h3>\r\n<p><a class="extLink" href="http://www.hermani-webrealisierung.de"><span class="bold">hermani webrealisierung</span> Baden-Baden</a></p>\r\n<hr />\r\n<h3>Bildnachweis</h3>\r\n<p>Bilderquellen:&nbsp;</p>\r\n<hr />\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', 'text', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '<h1>Disclaimer</h1>\r\n<div class="panel panel-default">\r\n<div class="panel-body">\r\n<h2>1. Haftungsbeschränkung</h2>\r\n<p>Verantwortlich für dieses Informationsangebot ist der Herausgeber. Die Informationen auf dieser Website wurden mit großer Sorgfalt zusammengestellt. Für die Richtigkeit und Vollständigkeit kann gleichwohl keine Gewähr übernommen werden. Aus diesem Grund ist jegliche Haftung für eventuelle Schäden im Zusammenhang mit der Nutzung des Informationsangebots ausgeschlossen. Durch die bloße Nutzung dieser Website kommt keinerlei Vertragsverhältnis zwischen Nutzer und Anbieter/Herausgeber zustande.</p>\r\n<h2>2. Hinweis zum Urheberrecht</h2>\r\n<p>Der gesamte Inhalt dieser Website unterliegt dem Urheberrecht. Unerlaubte Verwendung, Reproduktion oder Wiedergabe des Inhalts oder von Teilen des Inhalts ist untersagt. Wegen einer Erlaubnis zur Nutzung des Inhalts wenden Sie sich bitte an den Herausgeber.</p>\r\n<h2>3. Hinweis zu externen Links</h2>\r\n<p>Soweit von dieser Website auf andere Websites Links gelegt sind, wird darauf hingewiesen, dass keinerlei Einfluss auf die Gestaltung und die Inhalte der gelinkten Seiten besteht und sich deren Inhalt nicht zu eigen gemacht wird. Dies gilt für alle auf dieser Seite ausgebrachten, externen Links und für alle Inhalte der Seiten, zu denen Werbemittel (z.B. Banner, Textanzeigen, Videoanzeigen) führen. Für verlinkte Seiten gilt, dass rechtswidrige Inhalte zum Zeitpunkt der Verlinkung nicht erkennbar waren. Die Links werden regelmäßig auf rechtswidrige Inhalte überprüft und bei Rechtsverletzungen unverzüglich entfernt.</p>\r\n<h2>4. Datenschutz</h2>\r\n<h3>a) Google Adsense</h3>\r\n<p>Soweit Google Adsense, einen Webanzeigendienst der Google Inc., USA ("Google"), auf dieser Website Werbung (Textanzeigen, Banner etc.) schaltet, speichert Ihr Browser eventuell ein von Google Inc. oder Dritten gesendetes Cookie. Die in dem Cookie gespeicherten Informationen können durch Google Inc. oder auch Dritte aufgezeichnet, gesammelt und ausgewertet werden. Darüber hinaus verwendet Google Adsense zur Sammlung von Informationen auch sog. "WebBacons"(kleine unsichtbare Grafiken), durch deren Verwendung einfache Aktionen wie der Besucherverkehr auf der Webseite aufgezeichnet, gesammelt und ausgewertet werden können. Die durch den Cookie und/oder Web Bacon erzeugten Informationen über Ihre Nutzung dieser Website werden an einen Server von Google in den USA übertragen und dort gespeichert. Google benutzt die so erhaltenen Informationen, um eine Auswertung Ihres Nutzungsverhaltens im Hinblick auf die AdSense-Anzeigen durchzuführen. Google wird diese Informationen gegebenenfalls auch an Dritte übertragen, sofern dies gesetzlich vorgeschrieben ist oder soweit Dritte diese Daten im Auftrag von Google verarbeiten. Ihre IP-Adresse wird von Google nicht mit anderen von Google gespeicherten Daten in Verbindung gebracht. Sie können das Speichern von Cookies auf Ihrer Festplatte und die Anzeige von Web Bacons verhindern. Dazu müssen Sie in Ihren Browser-Einstellungen "keine Cookies akzeptieren" wählen (Im Internet-Explorer unter "Extras / Internetoptionen / Datenschutz / Einstellung", bei Firefox unter "Extras / Einstellungen / Datenschutz / Cookies").</p>\r\n<h3>b) Google Analytics</h3>\r\n<p>Soweit diese Website Google Analytics, einen Webanalysedienst der Google Inc. ("Google") benutzt, speichert Ihr Browser eventuell ein von Google Inc. oder Dritten gesendetes Cookie, dass eine Analyse Ihrer Nutzung dieser Website ermöglicht. Die so gesammelten Informationen (einschließlich Ihrer IP-Adresse) werden an einen Server von Google in den USA übertragen und dort gespeichert. Google nutzt diese Informationen, um Ihre Nutzung dieser Website auszuwerten, um Reports über die Websiteaktivitäten für die Websitebetreiber zusammenzustellen und um weitere mit der Websitenutzung und der Internetnutzung verbundene Dienstleistungen zu erbringen. Google wird diese Informationen gegebenenfalls auch an Dritte übertragen, sofern dies gesetzlich vorgeschrieben oder soweit Dritte diese Daten im Auftrag von Google verarbeiten. Google wird in keinem Fall Ihre IP-Adresse mit anderen von Google gespeicherten Daten in Verbindung bringen. Die Installation der Cookies können Sie verhindern. Dazu müssen Sie in Ihren Browser-Einstellungen "keine Cookies akzeptieren" wählen (Im Internet-Explorer unter "Extras / Internetoptionen / Datenschutz / Einstellung", bei Firefox unter "Extras / Einstellungen / Datenschutz / Cookies"). Es wird darauf hingewiesen, dass Sie in diesem Fall gegebenenfalls nicht sämtliche Funktionen dieser Website voll umfänglich nutzen können. Durch die Nutzung dieser Website erklären Sie sich mit der Bearbeitung der über Sie erhobenen Daten durch Google in der zuvor beschriebenen Art und Weise und zu dem zuvor benannten Zweck einverstanden.</p>\r\n<h3>c) Sonstige Produkte und Dienste</h3>\r\n<p>Soweit diese Website Anzeigen schaltet, die unter Punkt 4a) nicht näher bezeichnete sind, nutzen die Anzeigen schaltenden Unternehmen möglicherweise Informationen (dies schließt nicht Ihren Namen, Ihre Adresse, E-Mail-Adresse oder Telefonnummer ein) zu Ihren Besuchen dieser und anderer Websites, damit Anzeigen zu Produkten und Diensten geschaltet werden können, die Sie interessieren.</p>\r\n<h3>d) Sonstiges</h3>\r\n<p>Es wird darauf hingewiesen, dass hinsichtlich der Datenübertragung über das Internet (z.B. bei der Kommunikation per E-Mail) keine sichere Übertragung gewährleistet ist. Empfindliche Daten sollten daher entweder gar nicht oder nur über eine sichere Verbindung (SSL) übertragen werden.</p>\r\n</div>\r\n</div>', 'text', '{"cols":"full","hide":0,"id":"","class":"","style":"","colssm":"","colsxs":"","mt":"","mb":"","ml":"","mr":"","pt":"","pb":"","pl":"","pr":"","sec":"0","ctr":"0","row":"0","div":"0","secid":"","ctrid":"","rowid":"","divid":"","secclass":"","ctrclass":"","rowclass":"","divclass":"","secbgcol":"","secbgimg":"","wowAni":"","data-wow-duration":"","data-wow-delay":"","data-wow-offset":"","data-wow-iteration":""}', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_right`
--

DROP TABLE IF EXISTS `cc_contents_right`;
CREATE TABLE IF NOT EXISTS `cc_contents_right` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_right`
--

INSERT INTO `cc_contents_right` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '', '', ''),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_contents_right_preview`
--

DROP TABLE IF EXISTS `cc_contents_right_preview`;
CREATE TABLE IF NOT EXISTS `cc_contents_right_preview` (
  `page_id` varchar(64) NOT NULL,
  `con1_de` mediumtext NOT NULL,
  `type-con1` varchar(50) NOT NULL,
  `styles-con1` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_contents_right_preview`
--

INSERT INTO `cc_contents_right_preview` (`page_id`, `con1_de`, `type-con1`, `styles-con1`) VALUES
('standard.tpl', '', '', ''),
('standard-leftcol.tpl', '', '', ''),
('standard-rightcol.tpl', '', '', ''),
('twocol-left.tpl', '', '', ''),
('twocol-right.tpl', '', '', ''),
('fullwidth.tpl', '', '', ''),
('fullwidth-leftcol.tpl', '', '', ''),
('fullwidth-rightcol.tpl', '', '', ''),
('one-page.tpl', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_download`
--

DROP TABLE IF EXISTS `cc_download`;
CREATE TABLE IF NOT EXISTS `cc_download` (
  `filename` varchar(300) NOT NULL,
  `downloads` int(11) NOT NULL,
  `last_access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_errorlog`
--

DROP TABLE IF EXISTS `cc_errorlog`;
CREATE TABLE IF NOT EXISTS `cc_errorlog` (
`id` int(11) NOT NULL,
  `error` varchar(500) NOT NULL,
  `script` varchar(200) NOT NULL,
  `line` int(11) NOT NULL,
  `timestamp` varchar(13) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_forms`
--

DROP TABLE IF EXISTS `cc_forms`;
CREATE TABLE IF NOT EXISTS `cc_forms` (
`id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `table` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `title_de` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `foreign_key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notice_success_de` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `notice_error_de` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `notice_field_de` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `captcha` tinyint(1) NOT NULL,
  `https` tinyint(1) NOT NULL,
  `poll` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `add_table` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `add_fields` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `add_labels_de` varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  `add_position` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_forms_definitions`
--

DROP TABLE IF EXISTS `cc_forms_definitions`;
CREATE TABLE IF NOT EXISTS `cc_forms_definitions` (
`id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `field_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '1',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `label_de` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value_de` text COLLATE utf8_unicode_ci,
  `min_length` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `max_length` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options_de` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notice_de` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `header_de` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remark_de` text COLLATE utf8_unicode_ci,
  `pagebreak` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_galleries`
--

DROP TABLE IF EXISTS `cc_galleries`;
CREATE TABLE IF NOT EXISTS `cc_galleries` (
`id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `gallery_name` varchar(64) NOT NULL,
  `group` varchar(256) NOT NULL DEFAULT 'public',
  `group_edit` text NOT NULL,
  `create_date` datetime NOT NULL,
  `mod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `tags` text NOT NULL,
  `name_de` varchar(300) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_galleries`
--

INSERT INTO `cc_galleries` (`id`, `sort_id`, `gallery_name`, `group`, `group_edit`, `create_date`, `mod_date`, `active`, `tags`, `name_de`) VALUES
(1, 1, 'Homegallery-halfsize', 'public', '', '2016-09-05 17:25:47', '2016-11-11 17:01:15', 1, '', ''),
(2, 2, 'Portfolio', 'public', '', '2016-09-06 14:22:27', '2016-09-06 12:22:27', 1, '', ''),
(3, 3, 'Homegallery-fullsize', 'public', '', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_galleries_images`
--

DROP TABLE IF EXISTS `cc_galleries_images`;
CREATE TABLE IF NOT EXISTS `cc_galleries_images` (
`id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `gallery_id` int(11) NOT NULL,
  `img_file` varchar(300) NOT NULL,
  `upload_date` datetime NOT NULL,
  `mod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `show` tinyint(1) NOT NULL DEFAULT '1',
  `img_tags` text NOT NULL,
  `title_de` varchar(300) NOT NULL,
  `link_de` varchar(300) NOT NULL,
  `text_de` mediumtext NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_galleries_images`
--

INSERT INTO `cc_galleries_images` (`id`, `sort_id`, `gallery_id`, `img_file`, `upload_date`, `mod_date`, `show`, `img_tags`, `title_de`, `link_de`, `text_de`) VALUES
(1, 2, 1, 'architecture-22039_1920.jpg', '2016-09-05 17:25:47', '2016-11-11 16:57:25', 1, '', 'Urban Business', '', 'Nähe und Vertrauen'),
(2, 1, 1, 'lotus-614493_1920.jpg', '2016-09-05 17:25:47', '2016-11-11 16:57:25', 1, '', 'Wellness', '', 'für Ihre Seele'),
(3, 3, 1, 'lamborghini-618356.jpg', '2016-09-05 17:25:47', '2016-11-11 16:57:25', 1, '', 'Kfz & Leidenschaft', '', 'Mehr als nur Auto'),
(4, 4, 1, 'flower-729514.jpg', '2016-09-05 17:25:47', '2016-11-11 16:57:25', 1, '', '', '', ''),
(5, 5, 1, 'wheel-1017023_1920.jpg', '2016-09-05 17:25:47', '2016-11-11 16:59:10', 1, '', '', '', ''),
(6, 6, 1, 'building-491294_1920.jpg', '2016-09-05 17:25:47', '2016-11-11 16:57:25', 1, '', '', '', ''),
(7, 6, 2, 'keyboard-621831_1920.jpg', '2016-09-06 14:22:27', '2016-11-11 17:10:53', 1, 'Office', '', '', ''),
(8, 4, 2, 'lamborghini-618356.jpg', '2016-09-06 14:22:27', '2016-11-11 17:10:53', 1, 'Cars', '', '', ''),
(9, 2, 2, 'drip-1048722_1920.jpg', '2016-09-06 14:22:27', '2016-11-11 17:10:53', 1, '', '', '', ''),
(10, 1, 2, 'marguerite-729510.jpg', '2016-09-06 14:22:27', '2016-11-11 17:10:53', 1, 'Flowers', 'Flower 1', '{#root}/Startseite.html', '<p><span class="cc-iconcontainer"><span class="icons icon-beer" role="icon"></span> </span> Very beautiful<span class="cc-iconcontainer"><span class="icons icon-star" role="icon"></span> </span> indeed</p>\r\n<p><span class="cc-iconcontainer" style="font-size: 36px;"><span class="icons icon-star" role="icon"><!-- cc-icon --></span> </span></p>'),
(11, 3, 2, 'flower-729514.jpg', '2016-09-06 14:22:27', '2016-11-11 17:10:53', 1, 'Flowers', 'Flower 2', '', ''),
(12, 5, 2, 'building-491294_1920.jpg', '2016-09-06 14:22:27', '2016-11-11 17:10:53', 1, 'Office,Buildings', '', '', ''),
(13, 7, 1, '1-horse-shoe-lucky-western-hoof-horseshoe.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(14, 8, 1, '1-roses-bouquet-congratulations-arrangement-flowers.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(15, 9, 1, '90f1cea6f304ed1ffc338b37fb31c5a4.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(16, 10, 1, 'art-wall-brush-painting.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(17, 11, 1, 'brown-vintage-background-18641-19113-hd-wallpapers.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(18, 12, 1, 'buick-1158293_1920.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(19, 13, 1, 'computer-1149148_1920.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(20, 14, 1, 'entrepreneur-979861_1920.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(21, 15, 1, 'food-healthy-soup-leek.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(22, 16, 1, 'food-restaurant-fruits-orange.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(23, 17, 1, 'food-vegetables-meal-kitchen.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(24, 18, 1, 'kaboompics.com_Closeup-of-Beautiful-Pink-Flowers.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(25, 19, 1, 'leaves-108969_1920.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(26, 20, 1, 'lotus-614494_1920.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(27, 21, 1, 'medical-563427_1920.jpg', '2016-11-11 17:57:25', '2016-11-11 16:57:25', 1, '', '', '', ''),
(28, 22, 1, 'nature-blue-park-green.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(29, 23, 1, 'night-car-mustang-ford.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(30, 24, 1, 'painting-black-paint-roller.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(31, 25, 1, 'photo-1423593586350-a634d0b09416.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(32, 26, 1, 'photo-1424971260748-37cf4b4a3c07.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(33, 27, 1, 'photo-1431250620804-78b175d2fada.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(34, 28, 1, 'photo-1432888498266-38ffec3eaf0a.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(35, 29, 1, 'photo-1438755582627-221038b62986.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(36, 30, 1, 'picjumbo.com_HNCK1748.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(37, 31, 1, 'picjumbo.com_HNCK2390.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(38, 32, 1, 'picjumbo.com_HNCK3562.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(39, 33, 1, 'picjumbo.com_HNCK4560.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(40, 34, 1, 'picjumbo.com_HNCK5532.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(41, 35, 1, 'picjumbo.com_HNCK5665.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(42, 36, 1, 'picjumbo.com_HNCK9550.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(43, 37, 1, 'picjumbo.com_IMG_7751.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(44, 38, 1, 'picjumbo.com_IMG_7779.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(45, 39, 1, 'picjumbo.com_IMG_7961.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(46, 40, 1, 'picjumbo.com_IMG_9944.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(47, 41, 1, 'pizza-pizza-service-italian-eat-pizza-topping.jpg', '2016-11-11 17:58:22', '2016-11-11 16:58:22', 1, '', '', '', ''),
(48, 42, 1, 'puzzle-654957_1920.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(49, 43, 1, 'sunset-poppy-backlight.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(50, 44, 1, 'tire-114259_1920.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(51, 45, 1, 'traffic-car-vehicle-black.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(52, 46, 1, 'vegetables-italian-pizza-restaurant-1.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(53, 47, 1, 'vintage-1950s-887272_1920.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(54, 48, 1, 'wineglass-553467_1920.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(55, 49, 1, 'winter-260831_1920.jpg', '2016-11-11 17:59:10', '2016-11-11 16:59:10', 1, '', '', '', ''),
(56, 1, 3, 'medical-563427_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(57, 2, 3, '1-horse-shoe-lucky-western-hoof-horseshoe.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(58, 3, 3, '1-roses-bouquet-congratulations-arrangement-flowers.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(59, 4, 3, '90f1cea6f304ed1ffc338b37fb31c5a4.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(60, 5, 3, 'architecture-22039_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(61, 6, 3, 'art-wall-brush-painting.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(62, 7, 3, 'brown-vintage-background-18641-19113-hd-wallpapers.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(63, 8, 3, 'buick-1158293_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(64, 9, 3, 'building-491294_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(65, 10, 3, 'computer-1149148_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(66, 11, 3, 'entrepreneur-979861_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(67, 12, 3, 'flower-729514.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(68, 13, 3, 'food-healthy-soup-leek.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(69, 14, 3, 'food-restaurant-fruits-orange.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(70, 15, 3, 'food-vegetables-meal-kitchen.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(71, 16, 3, 'kaboompics.com_Closeup-of-Beautiful-Pink-Flowers.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(72, 17, 3, 'lamborghini-618356.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(73, 18, 3, 'leaves-108969_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(74, 19, 3, 'lotus-614493_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(75, 20, 3, 'lotus-614494_1920.jpg', '2016-11-11 18:02:05', '2016-11-11 17:02:05', 1, '', '', '', ''),
(76, 21, 3, 'nature-blue-park-green.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(77, 22, 3, 'night-car-mustang-ford.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(78, 23, 3, 'painting-black-paint-roller.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(79, 24, 3, 'photo-1423593586350-a634d0b09416.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(80, 25, 3, 'photo-1424971260748-37cf4b4a3c07.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(81, 26, 3, 'photo-1431250620804-78b175d2fada.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(82, 27, 3, 'photo-1432888498266-38ffec3eaf0a.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(83, 28, 3, 'photo-1438755582627-221038b62986.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(84, 29, 3, 'picjumbo.com_HNCK1748.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(85, 30, 3, 'picjumbo.com_HNCK2390.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(86, 31, 3, 'picjumbo.com_HNCK3562.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(87, 32, 3, 'picjumbo.com_HNCK4560.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(88, 33, 3, 'picjumbo.com_HNCK5532.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(89, 34, 3, 'picjumbo.com_HNCK5665.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(90, 35, 3, 'picjumbo.com_HNCK9550.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(91, 36, 3, 'picjumbo.com_IMG_7751.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(92, 37, 3, 'picjumbo.com_IMG_7779.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(93, 38, 3, 'picjumbo.com_IMG_7961.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(94, 39, 3, 'picjumbo.com_IMG_9944.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(95, 40, 3, 'pizza-pizza-service-italian-eat-pizza-topping.jpg', '2016-11-11 18:02:32', '2016-11-11 17:02:32', 1, '', '', '', ''),
(96, 41, 3, 'puzzle-654957_1920.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(97, 42, 3, 'sunset-poppy-backlight.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(98, 43, 3, 'tire-114259_1920.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(99, 44, 3, 'traffic-car-vehicle-black.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(100, 45, 3, 'vegetables-italian-pizza-restaurant.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(101, 46, 3, 'vintage-1950s-887272_1920.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(102, 47, 3, 'wheel-1017023_1920.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(103, 48, 3, 'wineglass-553467_1920.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', ''),
(104, 49, 3, 'winter-260831_1920.jpg', '2016-11-11 18:02:48', '2016-11-11 17:02:48', 1, '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_gbook`
--

DROP TABLE IF EXISTS `cc_gbook`;
CREATE TABLE IF NOT EXISTS `cc_gbook` (
`id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `gbname` varchar(200) NOT NULL,
  `group` varchar(256) NOT NULL DEFAULT 'public',
  `gbdate` datetime NOT NULL,
  `gbcomment` text NOT NULL,
  `gbmail` varchar(300) NOT NULL,
  `gravatar` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_lang`
--

DROP TABLE IF EXISTS `cc_lang`;
CREATE TABLE IF NOT EXISTS `cc_lang` (
`id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `nat_code` varchar(3) NOT NULL,
  `nationality` varchar(100) NOT NULL,
  `flag_file` varchar(300) NOT NULL,
  `def_lang` int(1) NOT NULL DEFAULT '0',
  `inst_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_lang`
--

INSERT INTO `cc_lang` (`id`, `sort_id`, `nat_code`, `nationality`, `flag_file`, `def_lang`, `inst_date`) VALUES
(1, 1, 'de', 'Deutsch', 'flag_de.png', 1, '2016-08-31 10:55:18');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_locks`
--

DROP TABLE IF EXISTS `cc_locks`;
CREATE TABLE IF NOT EXISTS `cc_locks` (
  `rowID` varchar(100) NOT NULL,
  `tablename` varchar(100) NOT NULL,
  `lockedBy` varchar(256) NOT NULL,
  `lockedUntil` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_log`
--

DROP TABLE IF EXISTS `cc_log`;
CREATE TABLE IF NOT EXISTS `cc_log` (
`id` int(11) NOT NULL,
  `realIP` varchar(32) NOT NULL,
  `sessionID` varchar(32) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `page_id` int(9) NOT NULL,
  `lang` varchar(3) NOT NULL,
  `referer` varchar(512) NOT NULL,
  `browser` varchar(200) NOT NULL,
  `version` varchar(6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_log_bots`
--

DROP TABLE IF EXISTS `cc_log_bots`;
CREATE TABLE IF NOT EXISTS `cc_log_bots` (
  `userAgent` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `realIP` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `cc_menuviewtable0`
--
DROP VIEW IF EXISTS `cc_menuviewtable0`;
CREATE TABLE IF NOT EXISTS `cc_menuviewtable0` (
`id` int(11)
,`page_id` int(11)
,`create_date` datetime
,`mod_date` timestamp
,`author_id` int(11)
,`group` varchar(256)
,`group_edit` text
,`published` tinyint(1)
,`protected` tinyint(1)
,`menu_item` tinyint(1)
,`lft` int(11)
,`rgt` int(11)
,`locked` tinyint(1)
,`template` varchar(200)
,`nosearch` tinyint(1)
,`robots` tinyint(1)
,`canonical` int(11)
,`copy` tinyint(1)
,`index_page` tinyint(1)
,`title_de` varchar(100)
,`alias_de` varchar(100)
,`html_title_de` varchar(100)
,`description_de` varchar(200)
,`keywords_de` varchar(300)
);
-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `cc_menuviewtable1`
--
DROP VIEW IF EXISTS `cc_menuviewtable1`;
CREATE TABLE IF NOT EXISTS `cc_menuviewtable1` (
`id` int(11)
,`page_id` int(11)
,`create_date` datetime
,`mod_date` timestamp
,`author_id` int(11)
,`group` varchar(256)
,`group_edit` text
,`published` tinyint(1)
,`protected` tinyint(1)
,`menu_item` tinyint(1)
,`lft` int(11)
,`rgt` int(11)
,`locked` tinyint(1)
,`template` varchar(200)
,`nosearch` tinyint(1)
,`robots` tinyint(1)
,`canonical` int(11)
,`copy` tinyint(1)
,`index_page` tinyint(1)
,`title_de` varchar(100)
,`alias_de` varchar(100)
,`html_title_de` varchar(100)
,`description_de` varchar(200)
,`keywords_de` varchar(300)
);
-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `cc_menuviewtable2`
--
DROP VIEW IF EXISTS `cc_menuviewtable2`;
CREATE TABLE IF NOT EXISTS `cc_menuviewtable2` (
`id` int(11)
,`page_id` int(11)
,`create_date` datetime
,`mod_date` timestamp
,`author_id` int(11)
,`group` varchar(256)
,`group_edit` text
,`published` tinyint(1)
,`protected` tinyint(1)
,`menu_item` tinyint(1)
,`lft` int(11)
,`rgt` int(11)
,`locked` tinyint(1)
,`template` varchar(200)
,`nosearch` tinyint(1)
,`robots` tinyint(1)
,`canonical` int(11)
,`copy` tinyint(1)
,`index_page` tinyint(1)
,`title_de` varchar(100)
,`alias_de` varchar(100)
,`html_title_de` varchar(100)
,`description_de` varchar(200)
,`keywords_de` varchar(300)
);
-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `cc_menuviewtable3`
--
DROP VIEW IF EXISTS `cc_menuviewtable3`;
CREATE TABLE IF NOT EXISTS `cc_menuviewtable3` (
`id` int(11)
,`page_id` int(11)
,`create_date` datetime
,`mod_date` timestamp
,`author_id` int(11)
,`group` varchar(256)
,`group_edit` text
,`published` tinyint(1)
,`protected` tinyint(1)
,`menu_item` tinyint(1)
,`lft` int(11)
,`rgt` int(11)
,`locked` tinyint(1)
,`template` varchar(200)
,`nosearch` tinyint(1)
,`robots` tinyint(1)
,`canonical` int(11)
,`copy` tinyint(1)
,`index_page` tinyint(1)
,`title_de` varchar(100)
,`alias_de` varchar(100)
,`html_title_de` varchar(100)
,`description_de` varchar(200)
,`keywords_de` varchar(300)
);
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_news`
--

DROP TABLE IF EXISTS `cc_news`;
CREATE TABLE IF NOT EXISTS `cc_news` (
`id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `sort_id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` int(11) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `calls` int(11) NOT NULL,
  `object1` text NOT NULL,
  `object2` text NOT NULL,
  `object3` text NOT NULL,
  `header_de` varchar(300) NOT NULL,
  `teaser_de` text NOT NULL,
  `text_de` mediumtext NOT NULL,
  `tags_de` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_newsletter`
--

DROP TABLE IF EXISTS `cc_newsletter`;
CREATE TABLE IF NOT EXISTS `cc_newsletter` (
`id` int(11) NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sent_date` datetime NOT NULL,
  `group` varchar(256) NOT NULL DEFAULT '<all>',
  `only_subscribers` tinyint(1) NOT NULL DEFAULT '1',
  `extra_emails` text NOT NULL,
  `file` varchar(300) NOT NULL,
  `subject` varchar(300) NOT NULL,
  `text` text NOT NULL,
  `format` varchar(5) NOT NULL DEFAULT 'html'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_news_categories`
--

DROP TABLE IF EXISTS `cc_news_categories`;
CREATE TABLE IF NOT EXISTS `cc_news_categories` (
`cat_id` int(11) NOT NULL,
  `parent_cat` int(11) NOT NULL DEFAULT '0',
  `sort_id` int(11) NOT NULL,
  `group` varchar(256) NOT NULL DEFAULT 'public',
  `group_edit` text NOT NULL,
  `newsfeed` tinyint(1) NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `rating` tinyint(1) NOT NULL DEFAULT '0',
  `image` text NOT NULL,
  `target_page` smallint(6) NOT NULL,
  `category_de` varchar(64) NOT NULL,
  `cat_teaser_de` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_pages`
--

DROP TABLE IF EXISTS `cc_pages`;
CREATE TABLE IF NOT EXISTS `cc_pages` (
`id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `mod_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `author_id` int(11) NOT NULL DEFAULT '1',
  `group` varchar(256) NOT NULL DEFAULT 'public',
  `group_edit` text NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `menu_item` tinyint(1) NOT NULL DEFAULT '1',
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `template` varchar(200) NOT NULL DEFAULT 'standard.tpl',
  `nosearch` tinyint(1) NOT NULL DEFAULT '0',
  `robots` tinyint(1) NOT NULL DEFAULT '3',
  `canonical` int(11) NOT NULL,
  `copy` tinyint(1) NOT NULL DEFAULT '0',
  `index_page` tinyint(1) NOT NULL DEFAULT '0',
  `title_de` varchar(100) NOT NULL,
  `alias_de` varchar(100) NOT NULL,
  `html_title_de` varchar(100) NOT NULL,
  `description_de` varchar(200) NOT NULL,
  `keywords_de` varchar(300) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_pages`
--

INSERT INTO `cc_pages` (`id`, `page_id`, `create_date`, `mod_date`, `author_id`, `group`, `group_edit`, `published`, `protected`, `menu_item`, `lft`, `rgt`, `locked`, `template`, `nosearch`, `robots`, `canonical`, `copy`, `index_page`, `title_de`, `alias_de`, `html_title_de`, `description_de`, `keywords_de`) VALUES
(1, -1001, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'admin,editor,author', '', 1, 1, -1, 0, 0, 0, 'admin.tpl', 0, 0, 0, 0, 0, 'Admin', 'admin', 'Concise WMS - Adminbereich', '', ''),
(2, -1002, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Login', 'login', '', '', ''),
(3, -1003, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Fehlerseite', 'error', '', '', ''),
(4, -1004, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 2, 0, 0, 0, 'Suchergebnisse', 'sitesearch', '', '', ''),
(5, -1005, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Logout', 'logout', '', '', ''),
(6, -1006, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Registrierung', 'registration', '', '', ''),
(7, -1007, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'guest', '', 1, 1, -1, 0, 0, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'Mein Bereich', 'account', '', '', ''),
(8, 0, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, 1, 1, 4, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_main', '', '', '', ''),
(9, -1, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, 2, 1, 2, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_top', '', '', '', ''),
(10, -2, '2016-08-31 10:55:18', '2016-11-09 10:47:34', 1, 'public', '', 1, 1, 3, 1, 4, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_foot', '', '', '', ''),
(11, -3, '2016-08-31 10:55:18', '2016-08-31 10:55:18', 1, 'public', '', 1, 1, 0, 1, 2, 0, 'standard.tpl', 0, 0, 0, 0, 0, 'root_nonmenu', '', '', '', ''),
(12, 1, '2016-08-31 10:55:18', '2016-09-05 13:48:27', 1, 'public', '', 1, 0, 1, 2, 3, 0, 'one-page.tpl', 0, 3, 0, 0, 1, 'Startseite', 'startseite', 'Startseite', '', ''),
(13, 2, '2016-11-09 11:47:34', '2016-11-12 15:54:39', 1, 'public', '', 1, 0, 3, 2, 3, 0, 'standard.tpl', 0, 2, 0, 0, 0, 'Impressum', 'impressum', 'Impressum', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_planner`
--

DROP TABLE IF EXISTS `cc_planner`;
CREATE TABLE IF NOT EXISTS `cc_planner` (
`id` int(11) NOT NULL,
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
  `object1` text NOT NULL,
  `object2` text NOT NULL,
  `object3` text NOT NULL,
  `header_de` varchar(300) NOT NULL,
  `teaser_de` text NOT NULL,
  `text_de` mediumtext NOT NULL,
  `tags_de` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_planner_categories`
--

DROP TABLE IF EXISTS `cc_planner_categories`;
CREATE TABLE IF NOT EXISTS `cc_planner_categories` (
`cat_id` int(11) NOT NULL,
  `parent_cat` int(11) NOT NULL DEFAULT '0',
  `sort_id` int(11) NOT NULL,
  `group` varchar(256) NOT NULL DEFAULT 'public',
  `group_edit` text NOT NULL,
  `comments` tinyint(1) NOT NULL DEFAULT '0',
  `rating` tinyint(1) NOT NULL DEFAULT '0',
  `image` text NOT NULL,
  `target_page` smallint(6) NOT NULL,
  `category_de` varchar(64) NOT NULL,
  `cat_teaser_de` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_plugins`
--

DROP TABLE IF EXISTS `cc_plugins`;
CREATE TABLE IF NOT EXISTS `cc_plugins` (
`id` int(11) NOT NULL,
  `pl_name` varchar(64) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_plugins`
--

INSERT INTO `cc_plugins` (`id`, `pl_name`, `date`, `active`) VALUES
(1, 'test', '2016-09-01 11:05:31', 0),
(3, 'share', '2016-10-18 14:58:20', 1),
(4, 'wow', '2016-10-18 14:58:20', 1),
(2, 'social-links', '2016-10-18 14:55:13', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_rating`
--

DROP TABLE IF EXISTS `cc_rating`;
CREATE TABLE IF NOT EXISTS `cc_rating` (
  `module` varchar(16) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `votes` int(11) NOT NULL,
  `rate` float NOT NULL,
  `last_vote` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_search`
--

DROP TABLE IF EXISTS `cc_search`;
CREATE TABLE IF NOT EXISTS `cc_search` (
  `page_id` int(11) NOT NULL,
  `con_de` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_search`
--

INSERT INTO `cc_search` (`page_id`, `con_de`) VALUES
(1, 'Unsere Services\nImpressionen\nUnser Slogan ist unser Motto\nUnsere Anschrift\nFirmenname \n Firmengasse 1 \n 12345 Firmenhausen\nAnreise\nSie finden uns im Herzen von Baden-Baden.\nÖffnungszeiten\nMo. - Fr.\n9:00 - 17:00 Uhr\nSa.\n9:00 - 12:00 Uhr\nSo.\ngeschlossen\nTermine vereinbaren wir gerne auf AnfrageCopyright © 2016 Mein Unternehmen'),
(2, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_search_strings`
--

DROP TABLE IF EXISTS `cc_search_strings`;
CREATE TABLE IF NOT EXISTS `cc_search_strings` (
`id` int(11) NOT NULL,
  `search_string` varchar(256) NOT NULL,
  `results` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_sessions`
--

DROP TABLE IF EXISTS `cc_sessions`;
CREATE TABLE IF NOT EXISTS `cc_sessions` (
  `id` varchar(32) NOT NULL,
  `lastUpdated` int(11) NOT NULL DEFAULT '0',
  `start` int(11) NOT NULL DEFAULT '0',
  `value` mediumtext NOT NULL,
  `locked` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_stats`
--

DROP TABLE IF EXISTS `cc_stats`;
CREATE TABLE IF NOT EXISTS `cc_stats` (
  `page_id` int(11) NOT NULL,
  `visits_total` int(11) NOT NULL,
  `visits_lastmon` int(11) NOT NULL,
  `visits_lastyear` int(11) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cc_user`
--

DROP TABLE IF EXISTS `cc_user`;
CREATE TABLE IF NOT EXISTS `cc_user` (
`userid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` char(64) NOT NULL,
  `salt` varchar(9) NOT NULL,
  `group` varchar(64) NOT NULL DEFAULT 'guest',
  `own_groups` varchar(256) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `alias` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gender` varchar(1) NOT NULL,
  `title` varchar(20) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `street` varchar(100) NOT NULL,
  `zip_code` varchar(5) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `company` varchar(100) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_log` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `lang` varchar(3) NOT NULL,
  `at_skin` varchar(20) NOT NULL,
  `logID` varchar(200) NOT NULL,
  `newsletter` int(1) NOT NULL DEFAULT '0',
  `auth_code` varchar(200) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cc_user`
--

INSERT INTO `cc_user` (`userid`, `username`, `password`, `salt`, `group`, `own_groups`, `author_name`, `alias`, `email`, `gender`, `title`, `last_name`, `first_name`, `street`, `zip_code`, `city`, `country`, `phone`, `company`, `reg_date`, `last_log`, `active`, `lang`, `at_skin`, `logID`, `newsletter`, `auth_code`) VALUES
(1, 'admin', '10478b70488befcd369e4769e552b434f0aa9081fe1af42c695a2759e72fb893', 'ccSalt#01', 'admin', '', '', '', 'mail@hermani-web.de', 'm', '', '', '', '', '', '', '', '', '', '2016-08-31 10:55:18', '2016-11-10 18:14:53', 1, 'de', '', '9d137a0c64608db4fe35398b1b0f8157', 0, '');

-- --------------------------------------------------------

--
-- Struktur des Views `cc_menuviewtable0`
--
DROP TABLE IF EXISTS `cc_menuviewtable0`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ahermani`@`localhost` SQL SECURITY DEFINER VIEW `cc_menuviewtable0` AS select `cc_pages`.`id` AS `id`,`cc_pages`.`page_id` AS `page_id`,`cc_pages`.`create_date` AS `create_date`,`cc_pages`.`mod_date` AS `mod_date`,`cc_pages`.`author_id` AS `author_id`,`cc_pages`.`group` AS `group`,`cc_pages`.`group_edit` AS `group_edit`,`cc_pages`.`published` AS `published`,`cc_pages`.`protected` AS `protected`,`cc_pages`.`menu_item` AS `menu_item`,`cc_pages`.`lft` AS `lft`,`cc_pages`.`rgt` AS `rgt`,`cc_pages`.`locked` AS `locked`,`cc_pages`.`template` AS `template`,`cc_pages`.`nosearch` AS `nosearch`,`cc_pages`.`robots` AS `robots`,`cc_pages`.`canonical` AS `canonical`,`cc_pages`.`copy` AS `copy`,`cc_pages`.`index_page` AS `index_page`,`cc_pages`.`title_de` AS `title_de`,`cc_pages`.`alias_de` AS `alias_de`,`cc_pages`.`html_title_de` AS `html_title_de`,`cc_pages`.`description_de` AS `description_de`,`cc_pages`.`keywords_de` AS `keywords_de` from `cc_pages` where (`cc_pages`.`menu_item` = '0');

-- --------------------------------------------------------

--
-- Struktur des Views `cc_menuviewtable1`
--
DROP TABLE IF EXISTS `cc_menuviewtable1`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ahermani`@`localhost` SQL SECURITY DEFINER VIEW `cc_menuviewtable1` AS select `cc_pages`.`id` AS `id`,`cc_pages`.`page_id` AS `page_id`,`cc_pages`.`create_date` AS `create_date`,`cc_pages`.`mod_date` AS `mod_date`,`cc_pages`.`author_id` AS `author_id`,`cc_pages`.`group` AS `group`,`cc_pages`.`group_edit` AS `group_edit`,`cc_pages`.`published` AS `published`,`cc_pages`.`protected` AS `protected`,`cc_pages`.`menu_item` AS `menu_item`,`cc_pages`.`lft` AS `lft`,`cc_pages`.`rgt` AS `rgt`,`cc_pages`.`locked` AS `locked`,`cc_pages`.`template` AS `template`,`cc_pages`.`nosearch` AS `nosearch`,`cc_pages`.`robots` AS `robots`,`cc_pages`.`canonical` AS `canonical`,`cc_pages`.`copy` AS `copy`,`cc_pages`.`index_page` AS `index_page`,`cc_pages`.`title_de` AS `title_de`,`cc_pages`.`alias_de` AS `alias_de`,`cc_pages`.`html_title_de` AS `html_title_de`,`cc_pages`.`description_de` AS `description_de`,`cc_pages`.`keywords_de` AS `keywords_de` from `cc_pages` where (`cc_pages`.`menu_item` = '1');

-- --------------------------------------------------------

--
-- Struktur des Views `cc_menuviewtable2`
--
DROP TABLE IF EXISTS `cc_menuviewtable2`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ahermani`@`localhost` SQL SECURITY DEFINER VIEW `cc_menuviewtable2` AS select `cc_pages`.`id` AS `id`,`cc_pages`.`page_id` AS `page_id`,`cc_pages`.`create_date` AS `create_date`,`cc_pages`.`mod_date` AS `mod_date`,`cc_pages`.`author_id` AS `author_id`,`cc_pages`.`group` AS `group`,`cc_pages`.`group_edit` AS `group_edit`,`cc_pages`.`published` AS `published`,`cc_pages`.`protected` AS `protected`,`cc_pages`.`menu_item` AS `menu_item`,`cc_pages`.`lft` AS `lft`,`cc_pages`.`rgt` AS `rgt`,`cc_pages`.`locked` AS `locked`,`cc_pages`.`template` AS `template`,`cc_pages`.`nosearch` AS `nosearch`,`cc_pages`.`robots` AS `robots`,`cc_pages`.`canonical` AS `canonical`,`cc_pages`.`copy` AS `copy`,`cc_pages`.`index_page` AS `index_page`,`cc_pages`.`title_de` AS `title_de`,`cc_pages`.`alias_de` AS `alias_de`,`cc_pages`.`html_title_de` AS `html_title_de`,`cc_pages`.`description_de` AS `description_de`,`cc_pages`.`keywords_de` AS `keywords_de` from `cc_pages` where (`cc_pages`.`menu_item` = '2');

-- --------------------------------------------------------

--
-- Struktur des Views `cc_menuviewtable3`
--
DROP TABLE IF EXISTS `cc_menuviewtable3`;

CREATE ALGORITHM=UNDEFINED DEFINER=`ahermani`@`localhost` SQL SECURITY DEFINER VIEW `cc_menuviewtable3` AS select `cc_pages`.`id` AS `id`,`cc_pages`.`page_id` AS `page_id`,`cc_pages`.`create_date` AS `create_date`,`cc_pages`.`mod_date` AS `mod_date`,`cc_pages`.`author_id` AS `author_id`,`cc_pages`.`group` AS `group`,`cc_pages`.`group_edit` AS `group_edit`,`cc_pages`.`published` AS `published`,`cc_pages`.`protected` AS `protected`,`cc_pages`.`menu_item` AS `menu_item`,`cc_pages`.`lft` AS `lft`,`cc_pages`.`rgt` AS `rgt`,`cc_pages`.`locked` AS `locked`,`cc_pages`.`template` AS `template`,`cc_pages`.`nosearch` AS `nosearch`,`cc_pages`.`robots` AS `robots`,`cc_pages`.`canonical` AS `canonical`,`cc_pages`.`copy` AS `copy`,`cc_pages`.`index_page` AS `index_page`,`cc_pages`.`title_de` AS `title_de`,`cc_pages`.`alias_de` AS `alias_de`,`cc_pages`.`html_title_de` AS `html_title_de`,`cc_pages`.`description_de` AS `description_de`,`cc_pages`.`keywords_de` AS `keywords_de` from `cc_pages` where (`cc_pages`.`menu_item` = '3');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `cc_articles`
--
ALTER TABLE `cc_articles`
 ADD PRIMARY KEY (`id`), ADD FULLTEXT KEY `index_de` (`header_de`,`teaser_de`,`text_de`), ADD FULLTEXT KEY `tags_de` (`tags_de`);

--
-- Indizes für die Tabelle `cc_articles_categories`
--
ALTER TABLE `cc_articles_categories`
 ADD PRIMARY KEY (`cat_id`), ADD FULLTEXT KEY `cat_teaser_de` (`cat_teaser_de`);

--
-- Indizes für die Tabelle `cc_badlogin`
--
ALTER TABLE `cc_badlogin`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_bannedip`
--
ALTER TABLE `cc_bannedip`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_comments`
--
ALTER TABLE `cc_comments`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_contents_foot`
--
ALTER TABLE `cc_contents_foot`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_foot_preview`
--
ALTER TABLE `cc_contents_foot_preview`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_head`
--
ALTER TABLE `cc_contents_head`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_head_preview`
--
ALTER TABLE `cc_contents_head_preview`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_left`
--
ALTER TABLE `cc_contents_left`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_left_preview`
--
ALTER TABLE `cc_contents_left_preview`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_main`
--
ALTER TABLE `cc_contents_main`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_main_preview`
--
ALTER TABLE `cc_contents_main_preview`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_right`
--
ALTER TABLE `cc_contents_right`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_contents_right_preview`
--
ALTER TABLE `cc_contents_right_preview`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_download`
--
ALTER TABLE `cc_download`
 ADD PRIMARY KEY (`filename`);

--
-- Indizes für die Tabelle `cc_errorlog`
--
ALTER TABLE `cc_errorlog`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_forms`
--
ALTER TABLE `cc_forms`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `table` (`table`);

--
-- Indizes für die Tabelle `cc_forms_definitions`
--
ALTER TABLE `cc_forms_definitions`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_galleries`
--
ALTER TABLE `cc_galleries`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_galleries_images`
--
ALTER TABLE `cc_galleries_images`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_gbook`
--
ALTER TABLE `cc_gbook`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_lang`
--
ALTER TABLE `cc_lang`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_locks`
--
ALTER TABLE `cc_locks`
 ADD PRIMARY KEY (`rowID`,`tablename`);

--
-- Indizes für die Tabelle `cc_log`
--
ALTER TABLE `cc_log`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_news`
--
ALTER TABLE `cc_news`
 ADD PRIMARY KEY (`id`), ADD FULLTEXT KEY `index_de` (`header_de`,`teaser_de`,`text_de`), ADD FULLTEXT KEY `tags_de` (`tags_de`);

--
-- Indizes für die Tabelle `cc_newsletter`
--
ALTER TABLE `cc_newsletter`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_news_categories`
--
ALTER TABLE `cc_news_categories`
 ADD PRIMARY KEY (`cat_id`), ADD FULLTEXT KEY `cat_teaser_de` (`cat_teaser_de`);

--
-- Indizes für die Tabelle `cc_pages`
--
ALTER TABLE `cc_pages`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `page_id` (`page_id`);

--
-- Indizes für die Tabelle `cc_planner`
--
ALTER TABLE `cc_planner`
 ADD PRIMARY KEY (`id`), ADD FULLTEXT KEY `index_de` (`header_de`,`teaser_de`,`text_de`), ADD FULLTEXT KEY `tags_de` (`tags_de`);

--
-- Indizes für die Tabelle `cc_planner_categories`
--
ALTER TABLE `cc_planner_categories`
 ADD PRIMARY KEY (`cat_id`), ADD FULLTEXT KEY `cat_teaser_de` (`cat_teaser_de`);

--
-- Indizes für die Tabelle `cc_plugins`
--
ALTER TABLE `cc_plugins`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_search`
--
ALTER TABLE `cc_search`
 ADD PRIMARY KEY (`page_id`), ADD FULLTEXT KEY `con_de` (`con_de`);

--
-- Indizes für die Tabelle `cc_search_strings`
--
ALTER TABLE `cc_search_strings`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_sessions`
--
ALTER TABLE `cc_sessions`
 ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cc_stats`
--
ALTER TABLE `cc_stats`
 ADD PRIMARY KEY (`page_id`);

--
-- Indizes für die Tabelle `cc_user`
--
ALTER TABLE `cc_user`
 ADD PRIMARY KEY (`userid`), ADD UNIQUE KEY `username` (`username`), ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `cc_articles`
--
ALTER TABLE `cc_articles`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_articles_categories`
--
ALTER TABLE `cc_articles_categories`
MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_badlogin`
--
ALTER TABLE `cc_badlogin`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT für Tabelle `cc_bannedip`
--
ALTER TABLE `cc_bannedip`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_comments`
--
ALTER TABLE `cc_comments`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_errorlog`
--
ALTER TABLE `cc_errorlog`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_forms`
--
ALTER TABLE `cc_forms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_forms_definitions`
--
ALTER TABLE `cc_forms_definitions`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_galleries`
--
ALTER TABLE `cc_galleries`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT für Tabelle `cc_galleries_images`
--
ALTER TABLE `cc_galleries_images`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=105;
--
-- AUTO_INCREMENT für Tabelle `cc_gbook`
--
ALTER TABLE `cc_gbook`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_lang`
--
ALTER TABLE `cc_lang`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `cc_log`
--
ALTER TABLE `cc_log`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_news`
--
ALTER TABLE `cc_news`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_newsletter`
--
ALTER TABLE `cc_newsletter`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_news_categories`
--
ALTER TABLE `cc_news_categories`
MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_pages`
--
ALTER TABLE `cc_pages`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT für Tabelle `cc_planner`
--
ALTER TABLE `cc_planner`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_planner_categories`
--
ALTER TABLE `cc_planner_categories`
MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_plugins`
--
ALTER TABLE `cc_plugins`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `cc_search_strings`
--
ALTER TABLE `cc_search_strings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `cc_user`
--
ALTER TABLE `cc_user`
MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
