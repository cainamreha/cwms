<?php
namespace Concise;



###########################
###  ConciseCoreUpdater ###
###########################

// common.php einbinden
require_once __DIR__ . "/../../inc/common.php";
require_once PROJECT_DOC_ROOT."/inc/classes/Update/class.LiveUpdate.php"; // Klasse LiveUpdate einbinden

/**
 * Klasse ConciseCoreUpdater
 *
 *
 *
 */
class ConciseCoreUpdater extends LiveUpdate
{

	private $updateVersion		= "";
	private $updateDir			= "";
	private $langDir			= "";
	public $logResults			= true;
	private $logDir				= "";
	private $existingLangs		= array();
	public $successUpdScript	= array();
	public $errorUpdScript		= array();
	public $errorLang			= array();

	
	/**
	 * Constructor
	 */
	public function __construct($DB, $o_lng, $version)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->updateVersion	= $version;
		$this->updateDir		= SYSTEM_DOC_ROOT . '/update';
		$this->langDir 			= $this->updateDir . '/lang';
		$this->logDir 			= $this->updateDir . '/log';

		if(empty($this->DB))
			$this->DB			= $GLOBALS['DB'];

		if($this->updateVersion == "")
			$this->updateVersion	= "unknown";
		
		$this->existingLangs	= Language::getExistingLangs();
	
	}


	/**
	 * Datenbank Update
	 */
	public function runCWMSUpdater($update = "all")
	{
	
		if($update == "all"
		|| $update == "db"
		)
			$this->runDBUpdate();
		
		if($update == "all"
		|| $update == "lang"
		)
			$this->runLangUpdate();
		
		if($update == "all"
		|| $update == "settings"
		)
			$this->runSettingsUpdate();
		
		// update log
		$this->logUpdate($update);

	}
	

	/**
	 * Datenbank Update
	 */
	public function runDBUpdate()
	{
	
		if(empty($this->DB)) {
			$this->errorUpdScript[]		= "Fehler DB: keine Datanbankverbindung";
			return false;
		}
		
		// Database changes
		$resArr		= array();
		
		#$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "mytable` ADD `myfield` text NOT NULL");
		#$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "mytable` CHANGE `myfield` text NOT NULL");
		#$resArr[]	= $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "mytable` SET `myfiled` = 'myvalue'");
		
		
		// version 2.7.1
		if(version_compare(CWMS_VERSION, "2.7.1", '<')) {
			// alter table user
			$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "user` ADD `at_skin` varchar(20) NOT NULL AFTER `lang`");
			// alter table log_bots
			$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "log_bots` ADD `referer` varchar(512) NOT NULL AFTER `realIP`");
		}
		
		// version 2.8.3
		if(version_compare(CWMS_VERSION, "2.8.3", '<')) {
			// alter table forms
			foreach($this->o_lng->installedLangs as $eLang) {
				$eLang		= $this->DB->escapeString($eLang);
				$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms` CHANGE `notice_success_" . $eLang . "` `notice_success_" . $eLang . "` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
				$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms` CHANGE `notice_error_" . $eLang . "` `notice_error_" . $eLang . "` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
				$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms` CHANGE `notice_field_" . $eLang . "` `notice_field_" . $eLang . "` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
				$resArr[] 	= $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "forms` CHANGE `add_labels_" . $eLang . "` `add_labels_" . $eLang . "` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;");
			}
		}
		
		if(empty($resArr))
			return false;
		
		
		// Check results
		foreach($resArr as $key => $res) {
			// Check for true result values
			if($res === true)
				unset($resArr[$key]);
			
			// If field already existed on alter table (=duplicate) don't throw an error but notice
			elseif(stripos($res, "duplicate") !== false) {
				$this->successUpdScript[]	= "Hinweis DB: Feld bereits vorhanden (" . $res . ")";
				unset($resArr[$key]);
			}
		}
		
		if(empty($resArr)) {
			$this->successUpdScript[]	= "Die Datenbank wurde aktualisiert.";
			return true;
		}
		
		$this->errorUpdScript[]		= "Fehler DB: " . implode("<br />", $resArr);
		return false;
	
	}


	/**
	 * Sprachdateien Update (Statische Sprachbausteine, staticText_ln.ini)
	 */
	public function runLangUpdate()
	{	
	
		$langUpdCnt		= 0;
		
		// Lang changes
		foreach($this->existingLangs as $eLang) {
		
			$langUpdFile = $this->langDir . '/staticText_' . $eLang . '.ini';
			
			if(!file_exists($langUpdFile))
				continue;
			
			
			if($langUpd = @parse_ini_file($langUpdFile, true)) {
				
				$langFile = PROJECT_DOC_ROOT . '/langs/' . $eLang . '/staticText_' . $eLang . '.ini';
				
				// Neue Sprachbausteine hinzufügen
				if($langFileContent = @parse_ini_file($langFile, true)) {
					$newLangContent	= implode("", array_slice(@file($langFile), 0 ,1));
					
					foreach($langUpd as $key => $val) {
					
						if(array_key_exists($key, $langFileContent))
							$langFileContent[$key]	= array_merge($langFileContent[$key], $langUpd[$key]);
						else
							$langFileContent[$key]	= $val;
					}
					
					$newLangContent	.= $this->arr2ini($langFileContent);
				
					if(!$updLangFile = @file_put_contents($langFile, $newLangContent))
						$this->errorLang[] = $eLang;
					else
						$langUpdCnt++;
				}
				else
					$this->errorLang[] = $eLang;
			}
			elseif(is_array($langUpd)
			&& count($langUpd) == 0
			)
				continue;
			else
				$this->errorLang[] = $eLang;
		}
		
		// Falls Fehler
		if(count($this->errorLang) > 0) {
			$this->errorUpdScript[]		= "{s_common:error} {s_header:stattext}: " . implode(", ", $this->errorLang);
			return false;
		}
		elseif($langUpdCnt)
			$this->successUpdScript[]	= "{s_header:stattext} {s_common:updated}.";
		
		return true;
	}
	
	
	// Array in ini-File umwandeln
	private function arr2ini($a, $parent = array())
	{
		$out = '';
		foreach ($a as $k => $v)
		{
			if (is_array($v))
			{
				//subsection case
				//merge all the sections into one array...
				$sec = array_merge((array) $parent, (array) $k);
				//add section information to the output
				$out .= PHP_EOL . '[' . join('.', $sec) . ']' . PHP_EOL;
				//recursively traverse deeper
				$out .= self::arr2ini($v, $sec);
			}
			else
			{
				$keyLen		= strlen($k);
				$tabs		= "\t";
				
				while($keyLen < 12) {
					$tabs	.= "\t";
					$keyLen += 4;
				}

				//plain key->value case
				$out .= "$k$tabs= \"$v\"" . PHP_EOL;
			}
		}
		return $out;
	}
	
	
	
	/**
	 * runSettingsUpdate
	 * @return boolean
	 */
	private function runSettingsUpdate()
	{
	
		// Inhalte der Settings-Datei einlesen
		if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) {
			$this->errorUpdScript[]	= "settings file not found";
			return false;
		}
			
		// Sicherungskopie von settings.php anlegen
		copy(PROJECT_DOC_ROOT . '/inc/settings.php', PROJECT_DOC_ROOT . '/inc/settings-update.php.bkp');
		
		
		//**************
		// version 2.7.0
		//**************
		if(strlen(CC_USE_FULL_PAGEURL) < 16)
			$settings = preg_replace("/'CC_CRYPT_KEY',\"".CC_CRYPT_KEY."\"/", "'CC_CRYPT_KEY',\"cc-newmcrypt-key\"", $settings);
		
		if(!defined('CC_USE_FULL_PAGEURL'))
			$settings = preg_replace("/define\('HTML_TITLE'/", "define('CC_USE_FULL_PAGEURL',true);				// Volle oder reduzierte Page Urls (path depth)\ndefine('HTML_TITLE'", $settings);
		
		
		//**************
		// version 2.7.3
		//**************
		if(!defined('SMALL_IMG_SIZE'))
			$settings = preg_replace("/define\('DEF_PLAYER_WIDTH'/", "define('SMALL_IMG_SIZE',768);						// Breite von small images\ndefine('DEF_PLAYER_WIDTH'", $settings);
		if(!defined('MEDIUM_IMG_SIZE'))
			$settings = preg_replace("/define\('DEF_PLAYER_WIDTH'/", "define('MEDIUM_IMG_SIZE',1280);						// Breite von medium images\n\ndefine('DEF_PLAYER_WIDTH'", $settings);
		
		
		//**************
		// version 2.8.0
		//**************
		if(!defined('CC_VIDEO_FOLDER'))
			$settings = preg_replace("/CC_MOVIE_FOLDER/", "CC_VIDEO_FOLDER", $settings);
		if(strpos($settings, '/mp3') !== false)
			$settings = str_replace("/mp3", "/audio", $settings);
		if(defined('CC_FLASH_FOLDER'))
			$settings = str_replace("define('CC_FLASH_FOLDER', CC_MEDIA_FOLDER . '/flash');			// Verzeichnis für Flashdateien", "", $settings);
		if(is_dir(PROJECT_DOC_ROOT . '/' . CC_MEDIA_FOLDER . '/movies'))
			rename(PROJECT_DOC_ROOT . '/' . CC_MEDIA_FOLDER . '/movies', PROJECT_DOC_ROOT . '/' . CC_MEDIA_FOLDER . '/video');
		if(is_dir(PROJECT_DOC_ROOT . '/' . CC_MEDIA_FOLDER . '/mp3'))
			rename(PROJECT_DOC_ROOT . '/' . CC_MEDIA_FOLDER . '/mp3', PROJECT_DOC_ROOT . '/' . CC_MEDIA_FOLDER . '/audio');
		
		
		//**************
		// version 2.8.1
		//**************
		if(!defined('APPLE_TOUCH_ICON'))
			$settings = preg_replace("/define\('CC_SITE_LOGO'/", "define('APPLE_TOUCH_ICON',\"apple-touch-icon-precomposed.png\");	// Apple touch icon\ndefine('CC_SITE_LOGO'", $settings);
		
		
		//**************
		// version 2.8.3
		//**************
		if(!defined('HTTPS_PROTOCOL'))
			$settings = preg_replace("/define\('WEBSITE_LIVE'/", 'define(\'HTTPS_PROTOCOL\',!empty($_SERVER[\'HTTPS\']) && $_SERVER[\'HTTPS\']!="off");	// Protokoll der Verbindung (HTTP oder HTTPS, falls true)' . "\ndefine('WEBSITE_LIVE'", $settings);
		
		if(!defined('LARGE_IMG_SIZE'))
			$settings = preg_replace("/define\('DEF_PLAYER_WIDTH'/", "define('LARGE_IMG_SIZE',1400);						// Breite von large images\n\ndefine('DEF_PLAYER_WIDTH'", $settings);
		
		
		// settings.php speichern
		if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings)) {
			$this->errorUpdScript[]	= "could not write settings file";
			return false;
		}
		
		$this->successUpdScript[]	= "File &quot;settings.php&quot; {s_common:updated}.";
		
		return true;
	}

	
	
	// Log-File Updatevorgang
	private function logUpdate($update)
	{
	
		if(!is_dir($this->logDir))
			mkdir($this->logDir);
		
		$logFile	= $this->logDir . '/updateLog.txt';
		
		$handle = @fopen($logFile, "a");
		
		// Updatevorgang eintragen
		$error = date("d.m.Y h:i:s", time()) . " => version " . $this->updateVersion . ' (' . $update . '): ' . ContentsEngine::replaceStaText(str_replace(array("<br>","<br />"), " ", implode(" | ", $this->errorUpdScript))) . PHP_EOL;

		fwrite($handle, $error);

		fclose($handle);
	}
}

/*
if(isset($this->DB))
	$DB	= $this->DB;
else
	$DB	= $GLOBALS['DB'];

if(isset($this->updateVersion))
	$updateVersion	= $this->updateVersion;
else
	$updateVersion	= 'unknown';

if(!isset($update))
	$update			= 'all';

$o_CoreUpdater = new ConciseCoreUpdater($DB, $o_lng, $updateVersion);
$o_CoreUpdater->runCWMSUpdater($update);
*/