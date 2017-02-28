<?php
namespace Concise;


###################################################
####################  FeEdit  #####################
###################################################

// Speichern von FE-Änderungen an Textelementen in der DB

class FeEdit extends Admin
{

	private $action				= "";

	
	public function __construct($DB, $o_lng, $themeType)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);

		// Theme-Setup
		$this->getThemeDefaults($themeType);
	
	}
	
	public function conductAction()
	{

		// Falls aus FE aufgerufen
		if(isset($GLOBALS['_GET']['fe']) && $GLOBALS['_GET']['fe'] == 1) {
			$this->isFE					= true;
			$this->adminPage			= false;
		}

		if(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] != "")
			$this->action = $GLOBALS['_GET']['action'];

		
		// Fließtext bearbeiten
		if(isset($GLOBALS['_POST']['feEditText'])) {
		
			return $this->editTextelement($GLOBALS['_POST']['feEditText']);
		
		}

		// Datensatz (Artikel) bearbeiten
		if(isset($GLOBALS['_POST']['feEditDataText'])) {
		
			return $this->editDataEntry($GLOBALS['_POST']['feEditDataText']);
		
		}

	}
	
	// editTextelement
	public function editTextelement($content)
	{
	
		if(!$this->editorLog)
			return false;
		

		$pageID		= $this->DB->escapeString($GLOBALS['_POST']['pageID']);
		$conArea	= $this->DB->escapeString($GLOBALS['_POST']['conArea']);
		$conID		= $this->DB->escapeString($GLOBALS['_POST']['conID']);
		$lang		= $this->DB->escapeString($GLOBALS['_POST']['lang']);
		$table		= (strpos($conArea, "contents_") !== 0 ? "contents_" : "") . $conArea . "_preview";

		
		// Pfade durch Platzhalter ersetzen
		$rootPH = "{#root}";
		$rootImgPH = "{#root}/{#root_img}";
		
		$content = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $content);
		$content = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $content);
		
		$content	= $this->DB->escapeString($content); // escape String
	  
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . $table . "`");
		
		
		// db-Update der Inhaltstabelle
		$updateSQL = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . $table . "` 
											SET `con" . $conID . "_" . $lang . "` = '" . $content . "' 
											WHERE `page_id` = '$pageID'
											");
		
		#var_dump($updateSQL);
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		echo($updateSQL);

	}
	
	// editDataEntry
	public function editDataEntry($content)
	{

		$conArea	= $this->DB->escapeString($GLOBALS['_POST']['conArea']);
		$modType	= $this->DB->escapeString($GLOBALS['_POST']['modType']);
		$dataID		= $this->DB->escapeString($GLOBALS['_POST']['dataID']);
		$lang		= $this->DB->escapeString($GLOBALS['_POST']['lang']);
		$queryExt	= "";

		// Falls Author, nur eigene Artikel bearbeiten lassen
		if($GLOBALS['_SESSION']['group'] == "author") {
		
			$authorID	= $this->DB->escapeString((int)$GLOBALS['_SESSION']['userid']);
			$queryExt	= " AND `author` = $authorID";
		}
		
		// Pfade durch Platzhalter ersetzen
		$rootPH = "{#root}";
		$rootImgPH = "{#root}/{#root_img}";
		
		$content = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $content);
		$content = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $content);
		
		$content	= $this->DB->escapeString($content); // escape String
	  
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . $modType . "`");
		
		
		// db-Update der Inhaltstabelle
		$updateSQL = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . $modType . "` 
											SET `" . $conArea . "_" . $lang . "` = '" . $content . "' 
											WHERE `id` = '$dataID'
											");
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		echo($updateSQL);
	
	}

}
