<?php
##############################
###	  Grundeinstellungen   ###
##############################


#-------------------#
#--- CONCISE WMS ---#
#-------------------#
	
define('CWMS_VERSION',"2.8.3");						// Concise WMS-Version
define('CWMS_ACCESS',true);							// Überprüfung auf Direktaufruf von Skripten


// Error reporting ausschalten, wenn nicht Admin oder in Entwicklungsumgebung
if((isset($GLOBALS['_SESSION']['group']) && $GLOBALS['_SESSION']['group'] == "admin") || (isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1"))) {
	ini_set('display_errors','On');
	error_reporting(E_ALL);
	$EHnotify = true;
}
else {
	ini_set('display_errors','Off');
	$EHnotify = false;
}	

// php.ini settings
// hide php info
@ini_set('expose_php','Off');
// pcre.backtrack_limit hochsetzen (def 100k vor PHP 5.3.7 seitdem 1000k) = Limit für Anzahl an preg_xyz
#@ini_set('memory_limit','128M');
#@ini_set('post_max_size','128M');
#@ini_set('max_input_nesting_level','64');
#@ini_set('max_execution_time','60');
#@ini_set('pcre.backtrack_limit',1000000);
#@ini_set('pcre.recursion_limit',1000000);


#------------------------------#
#--- SPRACHEN/LAND/ZEITZONE ---#
#------------------------------#

date_default_timezone_set("Europe/Berlin");			// seit PHP 5.3 sollte die Zeitzone gesetzt werden
setlocale(LC_ALL, "de_DE.UTF-8", "de_DE", "de", "ger_ger", "german");

// UTF-8
ini_set('default_charset','UTF-8');
mb_internal_encoding('UTF-8');

// Dateien kompriemieren
#ob_start("ob_gzhandler");
					
define('LANG_URL_TYPE',"default");					// Url-Struktur für Sprachen über subdirectroy oder subdomain (default => /lang/, subdomain => http://lang.)
define('DEF_LANG',"de");							// Default-Sprache
define('FALLBACK_LANG',"en");						// Fallback-Sprache
define('DEF_ADMIN_LANG',"de");						// Default-Admin-Sprache
$adminLangs = array	("de" => "Deutsch",
					 "en" => "English"
					);								// Admin-Sprachen


#---------------------------#
#--- SYSTEMEINSTELLUNGEN ---#
#---------------------------#

define('ADMIN_THEME',"icomoon");					// Theme für Adminbereich
define('ADMIN_SKIN',"");							// Theme Skin für Adminbereich
define('ADMIN_HTTPS_PROTOCOL',false);				// Https-Protokol für Systemseiten
define('DEBUG',true);								// DEBUG-MODUS (wenn true, wird die Debug-Konsole angezeigt)
define('CC_CRYPT_KEY',"cc-newmcrypt-key");			// Schlüssel für die Verschlüsselung mit Klasse "myCrypt"
define('CC_SALT',"Y29uY2lzZSB3bXM=");				// Salt für Passwort-Verschlüsselung
define('CC_UPDATE_CHECK',true);						// Update check aktivieren (true)


#---------------------------#
#--- SEITENEINSTELLUNGEN ---#
#---------------------------#

define('HTTPS_PROTOCOL',!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!="off");	// Protokoll der Verbindung (HTTP oder HTTPS, falls true)
define('WEBSITE_LIVE',true);						// Website im production mode, falls true
define('PAGE_EXT',".html");							// Webpage-Extension
define('CC_USE_FULL_PAGEURL',false);				// Volle oder reduzierte Page Urls (path depth)
define('HTML_TITLE',"");							// HTML-TITEL-Ergänzung
define('SITE_AUTHOR',"concise wms");				// Author der Website
define('SITE_DESIGNER',"hermani webrealisierung");	// Designer der Website
define('CACHE',false);								// HTML-CACHE (wenn true, werden Seiten als HTML gecached)
define('CACHE_METHOD',"curl");						// Caching-Methode für das auslesen von HTML-Seiten ("curl" oder "fgc")
define('HTML5',true);								// HTML5 (wenn true, werden Seiten als HTML5-Domument angelegt)


#---------------#
#--- LOGGING ---#
#---------------#

define('CONCISE_LOG',true);							// Seitenaufrufe intern loggen (Statistik)
define('ANONYMIZE_IP',true);						// IP-Adressen anonymisieren (z.B. letztes Oktett = 0)
define('ANONYMIZE_IP_BYTES',4);						// IP-Adressen anonymisieren Anzahl Bytes (z.B. letzten zwei Oktetts = 4)
define('ANALYTICS',false);							// Analytics-code einbinden (Datei: "/access/js/analytics.js")
define('ANALYTICS_POSITION',"head");				// Position im Code für einbinden von Analytics-Code (head/body)


#-------------#
#--- PFADE ---#
#-------------#

// Dokumentpfad
// vor PHP 5.3: define('PROJECT_DOC_ROOT',dirname(__FILE__));
#define('PROJECT_DOC_ROOT',dirname(__FILE__)); // Dokumentpfad
$docPath	= str_replace(array("\inc","/inc"), "", __DIR__);

define('PROJECT_DOC_ROOT',$docPath);
define('SYSTEM_DOC_ROOT',PROJECT_DOC_ROOT . '/system'); // Systempfad

// Projektname
$project = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace("\\", "/",$docPath));

//Projekt-URL (für die Verwendung im Web)
define('PROJECT_HTTP_ROOT',(HTTPS_PROTOCOL ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$project);

// Admin- und System-Pfade
if(ADMIN_HTTPS_PROTOCOL)
	$conciseHttpRoot = str_replace("http://", "https://", PROJECT_HTTP_ROOT);
else
	$conciseHttpRoot = PROJECT_HTTP_ROOT;

define('ADMIN_HTTP_ROOT',$conciseHttpRoot . '/admin'); // Admin-URL
define('SYSTEM_HTTP_ROOT',$conciseHttpRoot . '/system'); // System-URL

// Pfad für MySQL-Backup
define('BACKUP_ROOT', "/usr/bin");
#define('BACKUP_ROOT', "/usr/syno/mysql/bin");
#define('BACKUP_ROOT', "d:/www/xampp/mysql/bin");

define('BACKUP_DIR', PROJECT_DOC_ROOT . '/backup/'); 			// Pfad für Backupverzeichnis
define('SYSTEM_IMAGE_DIR',SYSTEM_HTTP_ROOT.'/themes/'.ADMIN_THEME.'/img'); // Theme-Bilderordner für Adminbereich
define('SYSTEM_TEMPLATE_DIR',SYSTEM_HTTP_ROOT.'/themes/'.ADMIN_THEME.'/templates'); // Theme-Templateordner für Adminbereich
define('HTML_CACHE_DIR', PROJECT_DOC_ROOT . '/inc/html/'); 		// Pfad für HTML-Cache
define('FONTS_DIR', PROJECT_DOC_ROOT . '/inc/fonts/'); 			// Pfad für Fonts
define('CC_PLUGIN_FOLDER', 'plugins'); 							// Verzeichnis für Plug-ins
define('PLUGIN_DIR', PROJECT_DOC_ROOT . '/plugins/'); 			// Pfad für Plug-ins
define('PLUGIN_URL', PROJECT_HTTP_ROOT . '/plugins'); 			// URL für Plug-ins
define('TEMP_DIR', PROJECT_DOC_ROOT . '/_temp/'); 				// Pfad für temporäres Verzeichnis

define('CC_USER_FOLDER', '_user');								// Verzeichnis für Benutzerdateien
define('CC_MEDIA_FOLDER', 'media');								// Verzeichnis für Mediendateien
define('CC_FILES_FOLDER', CC_MEDIA_FOLDER . '/files');			// Verzeichnis für eigene Dateien
define('CC_GALLERY_FOLDER', CC_MEDIA_FOLDER . '/galleries');	// Verzeichnis für Bildergalerien
define('CC_IMAGE_FOLDER', CC_MEDIA_FOLDER . '/images');			// Verzeichnis für Bilddateien
define('CC_DOC_FOLDER', CC_MEDIA_FOLDER . '/docs');				// Verzeichnis für Dokumentdateien
define('CC_VIDEO_FOLDER', CC_MEDIA_FOLDER . '/video');			// Verzeichnis für Videodateien
define('CC_AUDIO_FOLDER', CC_MEDIA_FOLDER . '/audio');			// Verzeichnis für Audiodateien



#----------------------#
#--- ERROR HANDLING ---#
#----------------------#
	
define('EH_SCREEN_NOTIFICATION',$EHnotify);			//Aus-/Anschalten der Bildschirm-Benachrichtigung bei Fehlern
$screenErrors = array (	E_USER_ERROR,
						E_USER_WARNING,
						E_WARNING,
						E_NOTICE,
						E_USER_NOTICE);				//Definieren der Fehler, die am Bildschirm angezeigt werden sollen.

define('EH_EMAIL_NOTIFICATION',false);				//Aus-/Anschalten der Email-Benachrichtigung bei Fehlern
$emailErrors = array (	E_USER_ERROR,
					  	E_USER_WARNING,
						E_WARNING);					//Definieren der Fehler für Email-Benachrichtigung

define('EH_ADMIN_EMAIL',"mail@hermani-web.de");		//Admin-Email-Adresse für Fehler
define('EH_LOG',true);								//Aus-/Anschalten der Protokollfunktion
define('EH_LOGFILE_PATH',PROJECT_DOC_ROOT."/inc/log/error.log");	//Pfad zur Protokolldatei
$logErrors = array (	E_USER_ERROR,
						E_USER_WARNING,
						E_WARNING,
						E_NOTICE,
						E_USER_NOTICE);				//Definieren der Fehler, die protokolliert werden sollen


#----------------------------------#
#--- DATENBANKVERBINDUNGS-DATEN ---#
#----------------------------------#

// allgemein
define('DB_SERVER',"dbhost");
define('DB_PORT',"");
define('DB_NAME',"dbname");
define('DB_USER',"dbuser");
define('DB_PASSWORD',"dbpassword");
// Backup-User
define('DB_BKP_USER',"dbuser");
define('DB_BKP_PASSWORD',"dbpassword");


#--------------------------------------#
#--- BENUTZER-/SESSIONEINSTELLUNGEN ---#
#--------------------------------------#

define('PASSWORD_MIN_LENGTH',6);					// Mindestlänge für Passwörter
define('PASSWORD_MAX_LENGTH',15);					// Maximale Länge für Passwörter
define('PASSWORD_AUTO_LENGTH',8);					// Länge für automatisch generierte Passwörter
define('REGISTRATION_TYPE',"newsletter");			// Art der Standardregistrierung (none, newsletter, account)
define('LOGFORM_TYPE',"forgotPass");				// Art des Loginformulars (default, forgotPass)
define('REMEMBER_USER',true);						// An Benutzer erinnern (Option bei Login und Überprüfung bei checkLogin) anzeigen
define('MAX_ALLOWED_BAD_LOGINS',5);					// Maximal erlaubte Anzahl an "ungültigen" Anmeldeversuchen
define('LOGIN_BAN_TIME',1800);						// Dauer der Sperrung des Anmeldeskriptes in Sekunden
define('SESSION_UPTIME',"-30 minutes");				// Dauer der Aufrechterhaltung einer Session ohne Benutzeraktion
define('VISIT_COUNT_INTERVAL',21600);				// Dauer der Sperrung des Besucherzählers (visits) in Sekunden

$ownUserGroups = array();							// Selbstdefinierte Benutzergruppen


#----------------------------------------------#
#--- E-MAIL ADRESSEN/AUTHOREN/EINSTELLUNGEN ---#
#----------------------------------------------#

define('CONTACT_EMAIL',"");							// E-Mail für Kontaktformular
define('ORDER_EMAIL',"");							// E-Mail für Bestellformular
define('AUTO_MAIL_EMAIL',"");						// E-Mail des Autors für automatische E-Mails
define('NEWSLETTER_EMAIL',"");						// E-Mail des Newsletterauthors
define('GBOOK_NOTIFY_EMAIL',"");					// E-Mail für Benachrichtigung über neuen GB-Eintrag
define('COMMENTS_NOTIFY_EMAIL',"");					// E-Mail für Benachrichtigung über neuen Kommentar

define('AUTO_MAIL_AUTHOR',"");						// Name des Autors für automatische E-Mails
define('NEWSLETTER_AUTHOR',"");						// Name des Newsletterautors

// SMTP-Mail
define('SMTP_HOST',"");								// SMTP-Server
define('SMTP_PORT',25);								// SMTP-Port
define('SMTP_MAIL',"");								// E-Mail für SMTP-Versand
define('SMTP_USER',"");								// SMTP-Benutzername
define('SMTP_PASS',"");								// SMTP-Passwort


#----------------------------#
#--- MODULE-EINSTELLUNGEN ---#
#----------------------------#

define('BUILD_MENUS',false);						// Menüs standardmäßig generieren (Menütypen in theme_config.ini festlegen)

define('SEARCH_TYPE',"LIKE");						// Suchart (none, LIKE, MATCH)
define('SEARCH_MAX_ROWS',10);						// Maximale Anzahl an Suchergebnissen pro Tabelle

$searchTables = array("pages","articles","news","planner");	// MYSQL db-Tabellen, für die eine Suche erlaubt ist (pages, articles, news, planner, gbook)

define('DATA_PUBLISH_DELAY',true);					// Artikel/Daten werden nicht direkt veröffentlicht
define('MAX_DATA_OBJECTS_NUMBER',50);				// max. Anzahl an Artikel-/Datenobjekten
define('GBOOK_MAX_ROWS',5);							// Anzahl an Gästebucheinträgen pro Seite
define('GBOOK_MODERATE',true);						// Gästebucheinträge moderieren (true, false, mail)
define('COMMENTS_MAX_ROWS',10);						// Anzahl an Kommentaren pro Seite
define('COMMENTS_MODERATE',true);					// Kommentare moderieren (true, false, mail)
define('MAX_COMMENTS',50);							// Maximale Anzahl an Kommentaren

$gbookReadPermission = array("public");				// Benutzergruppen, für die das Lesen von Gästebucheinträgen erlaubt ist
$gbookWritePermission = array("public");			// Benutzergruppen, für die  das Verfassen von Gästebucheinträgen erlaubt ist
$commentTables = array("gbook","articles","news","planner"); // MYSQL db-Tabellen, für die Kommentare erlaubt sind

define('MAX_ENTRIES_ORDER_FORM',20);				// max. Anzahl an Posten im Bestellformular
define('SHOW_ENTRIES_ORDER_FORM',3);				// Anzahl an unmittelbar angezeigten Posten im Bestellformular
define('SHIPPING_CHARGES',"5,90");					// Versandkosten (Bestellformular)
define('SHIPPING_CHARGES_LIMIT',"20,00");			// Versandkostengrenze (Bestellformular)

define('FEED_HEAD',true);							// Newsfeed-Headeinträge

define('USE_CAT_NAME',false);						// Daten-Kategorienamen in Url verwenden

define('MOD_MENU_PREFIX',"");						// Prefix für das Artikelmenue

define('FILE_UPLOAD_METHOD',"auto");				// File-Upload Methode (e.g., plupload)
define('USE_FILES_FOLDER',false);					// Dateien Standardmäßig im Files Ordner speichern aktivieren

define('TEASER_IMG_TYPE',"link");					// Teaserbild bei Datenlisten als Bild- oder Linkansicht (view, link)

define('IMG_WIDTH',1280);							// Bildbreite
define('IMG_HEIGHT',768);							// Bildhöhe
define('MIN_IMG_SIZE',10);							// min. Bildbreite/-höhe
define('MAX_IMG_SIZE',1920);						// max. Bildbreite/-höhe
define('THUMB_SIZE',270);							// Höhe von Thumbnails
define('SMALL_IMG_SIZE',768);						// Breite von small images
define('MEDIUM_IMG_SIZE',1280);						// Breite von medium images
define('LARGE_IMG_SIZE',1400);						// Breite von large images

define('DEF_PLAYER_WIDTH',240);						// Audio-Playerbreite

define('SOCIAL_BAR',"default");						// Social bar

$emoticonForms = array("gbook");					// Array mit Formularen bei denen Emoticons verwendet werden sollen


#------------------------------------#
#--- THEME/TEMPLATE-EINSTELLUNGEN ---#
#------------------------------------#

define('THEME',"default");							// aktives Layout-Theme
define('HTML_HEAD_EXT',"");							// Erweiterung des HTML-Head-Codes

define('FE_THEME_SELECTION',true);					// Layout-Theme-Auswahl im Frontend einbinden

// Falls Theme-Preview, Preview-Theme einsetzen
if(	FE_THEME_SELECTION
	&& (!isset($_GET['page']) || $_GET['page'] != "admin")
	#&& isset($_SESSION['group']) && ($_SESSION['group'] == "admin" || $_SESSION['group'] == "editor")
	&& isset($_COOKIE['previewTheme'])
	&& is_dir(PROJECT_DOC_ROOT . '/themes/' . $_COOKIE['previewTheme'])
)
	$currentTheme = $_COOKIE['previewTheme'];
else
	$currentTheme = THEME;

define('JQUERY_VERSION',"1.11.1");					// Version der jQuery-Core-Datei (wird in class.HTML eingebunden)
define('JQUERY_VERSION_ADMIN',"2.1.4");				// Version der jQuery-Core-Datei (wird in class.HTML eingebunden) für Adminbereich
define('JQUERY_UI_VERSION',"1.11.4");				// Version der jQuery-UI-(Core-)Dateien
define('JQUERY_UI_VERSION_ADMIN',"1.11.1");			// Version der jQuery-UI-(Core-)Dateien für Adminbereich
define('JQUERY_UI_THEME',"redmond");				// jQuery-UI-Theme
define('JQUERY_UI_THEME_ADMIN',"concise");			// jQuery-UI-Theme für Adminbereich

define('WYSIWYG_EDITOR',"tinyMCE");					// Html-Editor (tinyMCE)
define('EDITOR_VERSION',4);							// Version Html-Editor
define('EDITOR_SKIN',"concise");					// Skin für den Html-Editor (tinyMCE)


define('THEME_DIR',"themes/".$currentTheme."/");	// Theme dir
define('TEMPLATE_DIR',THEME_DIR."templates/");		// Template dir
define('STYLES_DIR',THEME_DIR."css/");				// Styles dir
define('IMAGE_DIR',THEME_DIR."img/");				// Image dir
define('JS_DIR',THEME_DIR."js/");					// JS dir
define('CACHE_DIR',THEME_DIR."cache/");				// Cache dir
define('COMBINE_CSS_FILES',true);					// CSS combine files
define('MINIFY_CSS',true);							// CSS minify
define('GZIP_CSS',true);							// CSS gzip


define('HEADJS',true);								// head.js js-Dateien dynamisch einbinden
define('MODERNIZR',false);							// modernizr
define('HTML5SHIV',true);							// Html5Shiv


define('CC_MAIN_TEMPLATE',"index.tpl");				// Haupt-Template
define('CC_DEFAULT_TEMPLATE',"standard.tpl");		// Standard-Template

define('CC_SITE_LOGO',IMAGE_DIR."Ihr-Logo.png");	// Website-Logo
define('CC_ADMIN_LOGO',SYSTEM_IMAGE_DIR."/company_logo.png");	// Logo Adminbereich
define('APPLE_TOUCH_ICON',"apple-touch-icon-precomposed.png");	// Apple touch icon


#-------------------#
#--- DB-TABELLEN ---#
#-------------------#

define('DB_TABLE_PREFIX',"cc_");					// MYSQL db-Tabellen-Präfix

$tablePages 		= "pages";						// MYSQL db-Tabelle mit HTML-Seiteninformationen
$tableContents		= "contents_main";				// MYSQL db-Tabelle mit Hauptinhalten
$tableTplContents	= array("contents_head",
							"contents_left",
							"contents_right",
							"contents_foot"
							);						// Tabellen für Nebeninhaltsbereiche (Template-Areas)
$areasTplContents	= array("head",
							"left",
							"right",
							"foot"
							);						// Nebeninhlatsbereiche (Template-Areas)


#-------------------------#
#--- GLOBALE VARIABLEN ---#
#-------------------------#

$staText			= array();						// Variable für statische Sprachinhalte
