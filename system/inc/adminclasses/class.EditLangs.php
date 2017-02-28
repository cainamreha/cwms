<?php
namespace Concise;


###############################################
##############  Edit EditLangs  ###############
###############################################

// Sprachen bearbeiten

class EditLangs extends Admin
{

	private $tableLang		= "lang";

	public function __construct($DB, $o_lng)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		$this->tableLang		= DB_TABLE_PREFIX . $this->tableLang;

	}
	
	public function conductAction()
	{
	
		// Locking checken
		if($this->checkLocking("all", "langs", $this->g_Session['username']))
			return "locked";
		

		// Falls die Default-Sprache geändert werden soll
		if(!empty($GLOBALS['_GET']['defln'])) {
		
			$newDefLang		= $GLOBALS['_GET']['defln'];
			
			return $this->changeDefaultLang($newDefLang);
		}
		
		// Falls die Reihenfolge der Sprachen neu sortiert werden soll
		if(isset($GLOBALS['_GET']['action']) 
			&& $GLOBALS['_GET']['action'] == "sort"
			&& !empty($GLOBALS['_GET']['sortlang']) 
			&& !empty($GLOBALS['_GET']['oldsortid']) 
			&& !empty($GLOBALS['_GET']['newsortid']) 
		) {
			$sortLang		= $GLOBALS['_GET']['sortlang'];
			$oldSortID		= $GLOBALS['_GET']['oldsortid'];
			$newSortID		= $GLOBALS['_GET']['newsortid'];
		
			return $this->sortLanguages($sortLang, $oldSortID, $newSortID);
		}

	}

	
	#############  Default-Sprache ändern  ###############
	/**
	 * Ändern der Default-Sprache
	 * 
	 * @access	public
	 */	 
	private function changeDefaultLang($newDefLang)
	{		
		
		$newDefLangDB	= $this->DB->escapeString($newDefLang);
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tableLang`");
		

		$updateSQL1 = $this->DB->query("UPDATE `$this->tableLang`
											SET	`def_lang` = 0 
											WHERE `def_lang` = 1
											");

		$updateSQL2 = $this->DB->query("UPDATE `$this->tableLang`
											SET	`def_lang` = 1 
											WHERE `nat_code` = '$newDefLangDB'
											");


		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		if(	$updateSQL1 !== true 
		 || $updateSQL2 !== true 
		)
			return false;
		
		// Settings.inc update
		if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php'))
			return "settings file not found. Please contact administrator for manual setting of default lang.";
		
		$settings = preg_replace("/'DEF_LANG',\"[a-zA-Z]{2,3}\"/", "'DEF_LANG',\"".$newDefLang."\"", $settings);
		
		if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings))
			return "could not write settings file";
		
		return "1";

	}


	#############  Sprachen sortieren  ###############
 	/**
	 * Sortieren von Sprachen
	 * 
	 * @access	public
	 */	 
	private function sortLanguages($sortLang, $oldSortID, $newSortID)
	{
	
		$sortLangdb		= $this->DB->escapeString($sortLang);
		$oldSortIDdb	= $this->DB->escapeString($oldSortID);
		$newSortIDdb	= $this->DB->escapeString($newSortID);


		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tableLang`");
		
		if($oldSortID < $newSortID) {
			
			$updateSQL1 = $this->DB->query("UPDATE `$this->tableLang`
												SET	`sort_id` = `sort_id` -1 
												WHERE `sort_id` > $oldSortIDdb 
												AND `sort_id` <= $newSortIDdb 
												");
		}

		else {
			
			$updateSQL1 = $this->DB->query("UPDATE `$this->tableLang`
												SET	`sort_id` = `sort_id` +1 
												WHERE `sort_id` >= $newSortIDdb 
												AND `sort_id` < $oldSortIDdb 
												");
		}
		
		$updateSQL2 = $this->DB->query("UPDATE `$this->tableLang`
											SET	`sort_id` = $newSortIDdb
											WHERE `nat_code` = '$sortLangdb'
											");


		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");


		if($updateSQL1 === true 
		&& $updateSQL2 === true
		)
			return "1";
		else
			return ADMIN_HTTP_ROOT . "?task=langs";
		
	}

} // end class EditLangs
