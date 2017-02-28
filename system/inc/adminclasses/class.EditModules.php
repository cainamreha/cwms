<?php
namespace Concise;


###################################################
#################  EditModules  ###################
###################################################

// Daten-Module editieren	

class EditModules extends Admin_ModulesData
{
	
	private $action				= "";
	private $modType			= "";
	private $catLabel			= "";
	private $isCat				= false;
	private $newSortID			= "";
	private $newObjNr			= "";
	private $newQS				= "";
	private $editRedirect		= "";
	private $isFE				= false;
	private $updExt1a			= "";
	private $updExt1b			= "";
	private $redExt				= "";
	private $renameType			= "";
	private $nameOld			= "";
	private $nameOldDB			= "";
	private $nameNew			= "";
	private $nameNewDB			= "";
	private $rename				= false;
	private $gallTags			= "";
	private $gallTagsDB			= "";
	private $updateSQL			= "";

	public function __construct($DB, $o_lng)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng, "modules");

	}
	
	public function conductAction()
	{
	
		if(!empty($GLOBALS['_GET']['action']))
			$this->action		= $GLOBALS['_GET']['action'];

		if(!empty($GLOBALS['_GET']['mod'])
		|| !empty($GLOBALS['_GET']['type'])
		) {
			$this->modType		= !empty($GLOBALS['_GET']['mod']) ? $GLOBALS['_GET']['mod'] : $GLOBALS['_GET']['type'];
			$this->modType		= $this->DB->escapeString($this->modType);
			$this->setModTables($this->modType);
		}

		if(!empty($GLOBALS['_GET']['cat']) && is_numeric($GLOBALS['_GET']['cat']))
			$this->catID		= $GLOBALS['_GET']['cat'];
			
		if(!empty($GLOBALS['_GET']['sortid']) && is_numeric($GLOBALS['_GET']['sortid']))
			$this->oldSortID	= $GLOBALS['_GET']['sortid'];

		if(!empty($GLOBALS['_GET']['newsortid']) && is_numeric($GLOBALS['_GET']['newsortid']))
			$this->newSortID	= $GLOBALS['_GET']['newsortid'];

		if(!empty($GLOBALS['_GET']['newobj']) && is_numeric($GLOBALS['_GET']['newobj']))
			$this->newObjNr		= (int)$GLOBALS['_GET']['newobj'];

		if(!empty($GLOBALS['_GET']['id'])) {
			$this->editID		= (int)$this->DB->escapeString($GLOBALS['_GET']['id']);
			
			if(!empty($this->catID)) {
				$this->updExt1a		= " AND `cat_id` = $this->catID";
				$this->updExt1b		= " WHERE id = $this->editID";
				$this->redExt		= "&list_cat=$this->catID";
			}
		}
		
		elseif(!empty($this->modType) && !empty($this->catID)) {
			$this->catTable		= $this->modType . "_categories";
			$this->catTableDB	= $this->DB->escapeString(DB_TABLE_PREFIX . $this->catTable);
			$this->isCat		= true;
			$this->updExt1a		= "";
			$this->updExt1b		= " WHERE `cat_id` = " . (int)$this->DB->escapeString($this->catID);
			$this->redExt		= "&list_cats=";
		}
		
		if(!empty($GLOBALS['_GET']['type']))
			$this->renameType = $GLOBALS['_GET']['type'];

		
		// Edit entry
		if($this->action == "editdata")
			return $this->editDataEntry($this->editID);
		

		// Edit entry
		if($this->action == "edit")
			return $this->setEditEntry($this->editID);
		

		// Copy entry
		if($this->action == "copy")
			return $this->copyDataEntry($this->editID, $this->catID, $this->modType);
		

		// Copy newsletter entry
		if($this->action == "copynewsl")
			return $this->copyNewsletter($this->editID);
		

		// Sortieren
		// frei sortieren
		if($this->action == "sort")
			return $this->freeSortData();			
		

		// Sortieren, nach oben oder unten verschieben
		if($this->action == "up" || $this->action == "down")
			return $this->stepSortSortData();
		

		// Neues Datenobjekt einfügen
		if($this->action == "newobj")
			return $this->insertNewObject();		
		

		// Sortieren von Datenobjekten
		if($this->action == "sortobjects")
			return $this->sortObjects();		
		

		// Dateien/Galerien umbenennen
		if($this->action == "rename")
			return $this->rename();		
		

		// Galeriebilder verstecken/veröffentlichen
		if($this->action == "hidegallimg")
			return $this->showHideGalleryImage();		
		

		// Gästebucheinträge/Kommentare/Datensätze veröffentlichen (Mehrfachauswahl)
		if($this->action == "publish")
			return $this->showHideDataEntries();		
		

		// Newsletter löschen (Mehrfachlöschung)
		if($this->action == "delnewsl")
			return $this->deleteNewslEntries();		
		

		// Daten-Kategorien löschen (Mehrfachlöschung)
		if($this->action == "delcat"
		|| $this->action == "delcats"
		) {
			$this->redExt	= "&list_cats=";
			return $this->deleteDataCats();
		}
		

		// Daten (Artikel/News/Termine) löschen (Mehrfachlöschung)
		if($this->action == "deldata")
			return $this->deleteDataEntries();		
		

		// Datensatz (Artikel/News/Termine) löschen
		if($this->action == "del")
			return $this->deleteDataEntry($this->editID, $this->catID, $this->oldSortID);
		

		// Datensatz (e.g., Artikel/News/Termine) veröffentlichen/verstecken
		if($this->action == "pubentry")
			return $this->publishDataEntry($GLOBALS['_GET']['id'], $GLOBALS['_GET']['set'], $this->dataTableDB);		
		

		// Gästebucheinträge löschen (Mehrfachlöschung)
		if($this->action == "delgbook")
			return $this->deleteGBookEntries();		
		

		// Kommentare löschen (Mehrfachlöschung)
		if($this->action == "delcomments")
			return $this->deleteComments();		
		

		// Benutzerbild löschen
		if($this->action == "deluserimage")
			return $this->deleteUserImage();
		

		// Einträge aus Log-Tabellen löschen (IP)
		if($this->action == "delip")
			return $this->deleteIPLogEntries();
		
		
		// Falls eine Tag-Liste generiert werden soll
		if($this->action == "tags")
			return $this->generateTagList();
		

		// Falls Bewertung zurückgesetzt werden sollen (Mehrfachauswahl)
		if($this->action == "resvotes")
			return $this->resetVotes();
		
		
		// Kommentar editieren
		if($this->action == "editcomment")
			return $this->editComment();
		
		
		// Array mit Authoren zurückgeben
		if($this->action == "getauthors")
			return $this->getAuthorSelect();
		
		
		// Author setzen
		if($this->action == "setauthor")
			return $this->setDataAuthor();
		
		
		// Datum setzen
		if($this->action == "setdate")
			return $this->setDataDate();
	
	}	

	
	/**
	 * Modul-Tabellen bestimmen
	 * 
	 * @param	$type		Modultyp
	 * @access	private
	 * @return	array
	 */
	private function setModTables($type)
	{
	
		if(empty($type)
		|| !in_array($type, $this->adminTaskTypes)
		)
			return false;
		
		$restrict = "";
		$queryExt1	= "";
		$queryExt2	= "";
		
		switch($type) {
		
			case "articles":
				$this->dataTable	= "articles";
				$this->catTable		= "articles_categories";
				$this->dataTableDB	= DB_TABLE_PREFIX . "articles";
				$this->catTableDB	= DB_TABLE_PREFIX . "articles_categories";
				$this->catLabel 	= "allgroups";
				break;
				
			case "news":
				$this->dataTable	= "news";
				$this->catTable		= "news_categories";
				$this->dataTableDB	= DB_TABLE_PREFIX . "news";
				$this->catTableDB	= DB_TABLE_PREFIX . "news_categories";
				$this->catLabel		= "allcats";
				break;
				
			case "planner":
				$this->dataTable	= "planner";
				$this->catTable		= "planner_categories";
				$this->dataTableDB	= DB_TABLE_PREFIX . "planner";
				$this->catTableDB	= DB_TABLE_PREFIX . "planner_categories";
				$this->catLabel 	= "allcats";
				break;
			
			case "feed":
				$this->catTable		= "news_categories";
				$this->catTableDB	= DB_TABLE_PREFIX . "news_categories";
				$this->catLabel 	= "allfeeds";
				$restrict			= "WHERE newsfeed > 0 ";
				break;
				
			case "newsl":
				$this->dataTable	= "newsletter";
				$this->dataTableDB	= DB_TABLE_PREFIX . "newsletter";
				break;
			
			case "comments":
				$this->dataTable	= "comments";
				$this->dataTableDB	= DB_TABLE_PREFIX . "comments";
				break;
				
			case "gbook":
				$this->dataTable	= "gbook";
				$this->dataTableDB	= DB_TABLE_PREFIX . "gbook";
				break;
				
		}
	}

	
	/**
	 * Datensatz bearbeiteten
	 * 
	 * @param	$editId		Edit ID
	 * @access	private
	 * @return	array
	 */
	private function editDataEntry($editId)
	{

		// Falls ein Datensatz bearbeitet werden soll
		if(!is_numeric($editId))
			return false;
		
		$dateStart	= "";
		$dateEnd	= "";
		
		if(!empty($GLOBALS['_GET']['dates']))
			$dateStart		= urldecode($GLOBALS['_GET']['dates']);
		if(!empty($GLOBALS['_GET']['datee']))
			$dateEnd		= urldecode($GLOBALS['_GET']['datee']);
			
		if(empty($dateStart)
		|| (empty($dateEnd) && $this->modType == "planner")
		) {
			echo "0";
			exit;
		}
		
		// Falls im Adminbereich kopiert wurde
		require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
		require_once SYSTEM_DOC_ROOT . "/inc/admintasks/modules/admin_modules.data.inc.php"; // Admin-Task einbinden
		
		$adminE					= new Admin_ModulesData($this->DB, $this->o_lng, "modules");
		
		// Theme-Setup
		$adminE->getThemeDefaults("admin");
		$adminE->dataTableDB	= $this->dataTableDB;		
		$adminE->editIDdb		= (int)$this->editID;
		
		if($this->modType == "planner") {
			$start	= explode(" ", $dateStart);
			$end	= explode(" ", $dateEnd);
			$adminE->dbUpdateStr 	=	"`date` = '$start[0]'," .
										"`date_end` = '$end[0]'," .
										"`time` = '$start[1]'," .
										"`time_end` = '$end[1]'";
		}
		else {
			$adminE->dbUpdateStr 	=	"`date` = '$dateStart'";
		}
		
		$result	= $adminE->updateDataEntry(array());
		echo $result;
		
		exit;
		return true;
	
	}

	
	/**
	 * Datensatz, der bearbeitet werden soll, anzeigen
	 * 
	 * @param	$editId		Edit ID
	 * @access	private
	 * @return	array
	 */
	private function setEditEntry($editId)
	{

		// Falls ein Datensatz bearbeitet werden soll
		if(!is_numeric($editId))
			return false;
			
		if($this->modType == "newsl") {
			$this->setSessionVar('newsl_id', $editId);
			$getTask = "campaigns";
		}
		else {
			$this->setSessionVar($this->modType . '_id', $editId);
			$getTask = "modules";
		}
	
		header("Location: " . ADMIN_HTTP_ROOT . "?task=$getTask&type=$this->modType&data_id=$editId");
		exit;
	
	}

	
	/**
	 * Datenmodul-Datensatz kopieren
	 * 
	 * @param	$copyID		Copy ID (ID des zu kopierenden Datensatzes)
	 * @param	$catID		Cat ID
	 * @param	$type		Modultyp
	 * @access	private
	 * @return	none
	 */
	private function copyDataEntry($copyID, $catID, $type)
	{
	
		if(!is_numeric($copyID)
		|| !is_numeric($catID)
		|| empty($type)
		)
			return false;
		
		
		$copyID		= (int)$copyID;
		$catID		= (int)$catID;
		
		$resetValues	= "";
		
		// Daten ändern
		$headerExt	= " (" . ContentsEngine::replaceStaText("{s_common:copy}") . ")";
		
		// Author ID (noch nicht implementiert)
		if(isset($this->g_Session['username']))
			$authorID	= $this->g_Session['userid'];
		else
			$authorID = "`author_id`";
		
		// Typspezifische Daten zurücksetzen
		if($type == "articles")
			$resetValues .= "`orders` = 0,";
		
		if($type != "planner")
			$resetValues .= "`date` = '" . date("Y-m-d H:i:s", time()) . "',";
		
		
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`");

		
		// Tabelle kopieren
		$copySQL1a = $this->DB->query("CREATE TEMPORARY TABLE _temp
											SELECT * 
											FROM `$this->dataTableDB` 
											WHERE id = $copyID;
										 ");
		
		$copySQL1b = $this->DB->query("ALTER TABLE _temp 
											CHANGE id id INT;
										 ");
		
		$copySQL1c = $this->DB->query("UPDATE _temp 
											SET `id` = NULL,
												`sort_id` = ((SELECT MAX(`sort_id`) FROM `$this->dataTableDB` 
																WHERE `cat_id` = $catID) + 1),
												`header_" . $this->editLang . "` = CONCAT(`header_" . $this->editLang . "`, '$headerExt'), 
												$resetValues 
												`published` = 0,
												`calls` = 0
												;
										 ");
		
		$copySQL1d = $this->DB->query("INSERT INTO `$this->dataTableDB` 
											SELECT * 
											FROM _temp;
											");
						
		$copySQL1e = $this->DB->query("DROP TEMPORARY TABLE _temp
										  ");
						

		$copySQL2 = $this->DB->query("SELECT `id` FROM `$this->dataTableDB` 
											WHERE `cat_id` = $catID 
											ORDER BY `sort_id` DESC 
											LIMIT 1;
										 ");
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

	
		// ID des kopierten Artikels in der Session speichern, damit dieser direkt zum Bearbeiten geöffnet wird
		$this->setSessionVar($type . '_id', $copySQL2[0]['id']);
		

		// Meldung in Session speichern
		$this->setSessionVar('notice', "{s_notice:copy".$type."}");
							
		header("Location: " . ADMIN_HTTP_ROOT . '?task=modules&type='.$type);
		exit;
		
	}

	
	/**
	 * Newsletter kopieren
	 * 
	 * @param	$copyID		Copy ID (ID des zu kopierenden Datensatzes)
	 * @access	private
	 * @return	none
	 */
	private function copyNewsletter($copyID)
	{
	
		if(empty($copyID)
		|| !is_numeric($copyID)
		)
			return false;
		
		
		$copyID		= (int)$copyID;
		
		$resetValues	= "";
		
		// Daten ändern
		$headerExt	= " (" . ContentsEngine::replaceStaText("{s_common:copy}") . ")";
		
		// Author ID (noch nicht implementiert)
		if(isset($this->g_Session['username']))
			$authorID	= $this->g_Session['userid'];
		else
			$authorID = "`author_id`";
		
		// Daten zurücksetzen
		$resetValues .= "`sent` = 0,";
		$resetValues .= "`date` = '" . date("Y-m-d H:i:s", time()) . "',";
		$resetValues .= "`sent_date` = ''";
		
		
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`");

		
		// Tabelle kopieren
		$copySQL1a = $this->DB->query("CREATE TEMPORARY TABLE _temp
											SELECT * 
											FROM `$this->dataTableDB` 
											WHERE id = $copyID;
										 ");
		
		$copySQL1b = $this->DB->query("ALTER TABLE _temp 
											CHANGE id id INT;
										 ");
		
		$copySQL1c = $this->DB->query("UPDATE _temp 
											SET `id` = NULL,
												`subject` = CONCAT(`subject`, '$headerExt'), 
												$resetValues;
										 ");
		
		$copySQL1d = $this->DB->query("INSERT INTO `$this->dataTableDB` 
											SELECT * 
											FROM _temp;
											");
						
		$copySQL1e = $this->DB->query("DROP TEMPORARY TABLE _temp;");
						

		$copySQL2 = $this->DB->query("SELECT MAX(`id`) FROM `$this->dataTableDB`;");
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

	
		// ID des kopierten Artikels in der Session speichern, damit dieser direkt zum Bearbeiten geöffnet wird
		$this->setSessionVar('newsl_id', $copySQL2[0]['MAX(`id`)']);
		

		// Meldung in Session speichern
		$this->setSessionVar('notice', "{s_notice:copynews}");
							
		header("Location: " . ADMIN_HTTP_ROOT . '?task=campaigns&type=newsl');
		exit;
		
	}
	
	
	/**
	 * Methode zum auflisten von Moduldaten-Objekten
	 * 
	 * @param	$o			Zähler für Objekt-Nr
	 * @access	private
	 * @return	array
	 */
	private function getNewDataObject($o)
	{
	
		require_once SYSTEM_DOC_ROOT."/inc/adminclasses/class.EditDataObjects.php"; // EditDataObjects-Klasse einbinden

		$o_dataObj	= new EditDataObjects($this->DB, $this->o_lng);

		$o_dataObj->editLang		= $this->editLang;
		$o_dataObj->editId			= $this->editId;
		$o_dataObj->editLangFlag	= $this->editLangFlag;
		$o_dataObj->userGroups		= $this->userGroups;
		$o_dataObj->backendLog		= $this->backendLog;
		
		$dataObject					= array("type"			=> "",
											$this->editLang	=> ""
											);
		
		$output						= $o_dataObj->getObject($dataObject, $o);

		
		// Head code zusammenführen
		$this->mergeHeadCodeArrays($o_dataObj);
		
		return $output;
	
	}
	
	
	// freeSortData
	public function freeSortData()
	{

		$idString	= "";
		$sortTable	= $this->dataTableDB;
		$dbFilter	= "";
		$dbFilter2	= "";
		
		// Falls keine ID mitgegeben, handelt es sich um eine Kategorie
		if($this->isCat) {
			$idString	= "`cat_id` = $this->catID";
			$sortTable	= $this->catTableDB;
		}
		else {
			$dbFilter	= "AND `cat_id` = $this->catID";
			$dbFilter2	= "WHERE `cat_id` = $this->catID ";
			$idString	= "`id` = $this->editID";
		}

		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES " . $sortTable . "");
		
		if($this->newSortID > $this->oldSortID) {
	  
			// Datenbankupdate
			$queryUpdate1a = $this->DB->query( "UPDATE " . $sortTable . " 
														SET `sort_id` = `sort_id`-1 
														WHERE `sort_id` <= $this->newSortID 
														AND `sort_id` > $this->oldSortID 
														$dbFilter
														");		
			#var_dump($queryUpdate1a);
		}
		else {
	  
			// Datenbankupdate
			$queryUpdate1a = $this->DB->query( "UPDATE " . $sortTable . " 
														SET `sort_id` = `sort_id`+1 
														WHERE `sort_id` >= $this->newSortID 
														AND `sort_id` < $this->oldSortID 
														$dbFilter
														");
			#var_dump($queryUpdate1a);
		}
		
		
		// Datenbankupdate
		$queryUpdate1b = $this->DB->query( "UPDATE " . $sortTable . " 
													SET `sort_id` = $this->newSortID
													WHERE $idString
												");
			
		#var_dump($queryUpdate1b);

		// Sortierung neu durchführen
		// Variable für Neusortierung setzen
		$updateSQL2a = $this->DB->query("SET @c:=0;
											");
		
		
		// Neusortierung
		$updateSQL2b = $this->DB->query("UPDATE " . $sortTable . " 
												SET `sort_id` = (SELECT @c:=@c+1)
												$dbFilter2
												ORDER BY `sort_id` ASC;
											  ");


		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		$redirect = "admin?task=modules&type=$this->modType&sort=1".$this->redExt;
		
		echo PROJECT_HTTP_ROOT . "/" . $redirect;
		exit;

	}
	
	
	// stepSortSortData
	public function stepSortSortData()
	{
		
		$idString	= "";
		$sortTable	= $this->dataTableDB;
		$dbFilter	= "";
	
		if($this->action == "up") {
			$this->newSortID = $this->oldSortID-1;
			$sortAddend = +1;
		}
		elseif($this->action == "down") {
			$this->newSortID = $this->oldSortID+1;
			$sortAddend = -1;
		}
		
		// Falls Kategorien
		if($this->isCat) {
			$sortTable	= $this->catTableDB;
		}
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $sortTable . "`");
		
	  
		// Datenbankupdate
		$queryUpdate1a = $this->DB->query( "UPDATE `" . $sortTable . "` 
													SET `sort_id` = `sort_id`+$sortAddend 
													WHERE `sort_id` = $this->newSortID
													$this->updExt1a
													");
		
		// Datenbankupdate
		$queryUpdate1b = $this->DB->query( "UPDATE `" . $sortTable . "` 
													SET `sort_id` = $this->newSortID
													$this->updExt1b
													");
		
		#var_dump($queryUpdate1);
		
		// Sortierung neu durchführen
		// Variable für Neusortierung setzen
		$updateSQL2a = $this->DB->query("SET @c:=0;
											");
		
		
		// Neusortierung
		$updateSQL2b = $this->DB->query("UPDATE `" . $sortTable . "` 
												SET `sort_id` = (SELECT @c:=@c+1)
												ORDER BY `sort_id` ASC;
											  ");


		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		$redirect = "admin?task=modules&type=$this->modType&sort=1".$this->redExt;

		header("location: " . PROJECT_HTTP_ROOT . "/" . $redirect);
		exit;

	}
	
	
	// insertNewObject
	public function insertNewObject()
	{
	
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`");
		
			
		// Tabelle um neues Objekt erweitern
		$alterSQL = $this->DB->query("ALTER TABLE `$this->dataTableDB` ADD `object" . $this->newObjNr . "` TEXT NOT NULL AFTER `object" . ($this->newObjNr-1) . "` 
										   ");
		
		// Tabelle um neues Objekt erweitern
		$updateSQL = $this->DB->query("UPDATE `$this->dataTableDB` SET `object" . $this->newObjNr . "` = ''
										   ");
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		

		// Daten-Objekte holen
		$adminContent = $this->getNewDataObject($this->newObjNr);
		
	
		echo ContentsEngine::replaceStaText($adminContent);
	
	}
	
	
	// sortObjects
	public function sortObjects()
	{

		// Nummer des letzten Datenobjekts
		if(isset($GLOBALS['_GET']['lastobject']) && $GLOBALS['_GET']['lastobject'] != "")
			$lastObjNo = $GLOBALS['_GET']['lastobject'];
			
		$objUpd		= "";
		$moveObj	= "";
		
		// Falls die neue Position größer ist als die alte
		if($this->newSortID > $this->oldSortID) {
			
			$moveObj = $this->newSortID; // Zu verschiebendes Objekt in temp-Spalte speichern
			
			for($i = $this->oldSortID; $i < $this->newSortID; $i++) {
				
				$j = $i+1;
				
				$objUpd .= "`object" . $i . "` = `object" . $j . "`,";
				
			} // Ende for
		}
		
		// Falls die neue Position kleiner ist als die alte
		elseif($this->newSortID < $this->oldSortID) {
			
			$moveObj = $this->newSortID; // Zu verschiebendes Objekt in temp-Spalte speichern
			
			for($i = $this->oldSortID -1; $i >= $this->newSortID; $i--) {
				
				$j = $i+1;
				
				$objUpd .= "`object" . $j . "` = `object" . $i . "`,";
				
			} // Ende for
		}
		
		// Letztes Komma entfernen
		$objUpd = substr($objUpd, 0, -1);
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES " . $this->dataTableDB . "");

		
		// Erstellen einer temporären Spalte
		$addSQL = $this->DB->query("ALTER TABLE `" . $this->dataTableDB . "` 
											ADD `tempobj` VARCHAR(2048) NOT NULL
											");
		
		
		
		// Datenbank-Update temp
		$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->dataTableDB . "` 
												SET `tempobj` = `object" . $this->oldSortID . "`
												WHERE `id` = '$this->editID'
												AND `cat_id` = '$this->catID'
												");

		
		
		// Datenbank-Update Verschieben
		$queryUpdate2 = $this->DB->query( "UPDATE `" . $this->dataTableDB . "` 
												SET $objUpd 
												WHERE `id` = '$this->editID'
												AND `cat_id` = '$this->catID'
												");

		
		// Datenbank-Update Einfügen
		$queryUpdate3 = $this->DB->query( "UPDATE `" . $this->dataTableDB . "` 
												SET `object" . $moveObj . "` = `tempobj`
												WHERE `id` = '$this->editID'
												AND `cat_id` = '$this->catID'
												");

		
		
		// Löschen der temporären Spalte
		$removeSQL = $this->DB->query("ALTER TABLE `" . $this->dataTableDB . "` 
											DROP `tempobj`
											");
		
		
			
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		echo($addSQL . $queryUpdate1 . $queryUpdate2 . $queryUpdate3 . $removeSQL);

	}
	
	
	// rename
	public function rename()
	{

		$this->nameOld		= "";
		$this->nameNew		= "";
		$this->rename		= false;
		$this->updateSQL 	= false;
		
		// Galerie umbenennen
		if($this->renameType == "gallery") {		
			$this->renameGallery();
		}
		
		// Datei umbenennen
		if($this->renameType == "file") {
			$this->renameFile();
		}

	}
	
	
	// renameGallery
	public function renameGallery()
	{
	
		if(isset($GLOBALS['_GET']['editname'])) {
			$this->nameOld		= $GLOBALS['_GET']['editname'];
			$this->nameOldDB	= $this->DB->escapeString($this->nameOld);
		}
		if(isset($GLOBALS['_GET']['newname'])) {
			$this->nameNew		= $GLOBALS['_GET']['newname'];
			$this->nameNewDB	= $this->DB->escapeString($this->nameNew);
		}
		if(isset($GLOBALS['_GET']['tags'])) {
			$this->gallTags		= $GLOBALS['_GET']['tags'];
			$this->gallTagsDB	= $this->DB->escapeString($this->gallTags);
		}
		
		// Überprüfen ob neuer Ordner existiert, wenn ja false zurückgeben
		if($this->nameNew != $this->nameOld
		&& is_dir(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $this->nameNew)
		)
			$this->updateSQL = "-1";
		else {
			
			// Überprüfen ob alter Ordner existiert, wenn ja alten Ordner umbenennen
			if(is_dir(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $this->nameOld))
				if(@rename(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $this->nameOld, PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $this->nameNew))
					$this->rename = true;
					
			if($this->rename) {
						  
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "galleries`");
				
			 
				// Datenbankupdate
				$this->updateSQL = $this->DB->query( "UPDATE `" . DB_TABLE_PREFIX . "galleries` 
														SET `gallery_name` = '$this->nameNew',
															`tags` = '$this->gallTagsDB'
														WHERE `gallery_name` = '$this->nameOld'
													");
						
				
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
			}
		}
		
		echo(json_encode(array(	"result"	=> "$this->updateSQL")));

	}
	
	
	// renameFile
	public function renameFile()
	{
	
		if(isset($GLOBALS['_GET']['newname']))
			$this->nameNew = $GLOBALS['_GET']['newname'];
		
		if(isset($GLOBALS['_GET']['editname']))
			$filenameOld = $GLOBALS['_GET']['editname'];
		
		if(isset($GLOBALS['_GET']['folder']))
			$folder = $GLOBALS['_GET']['folder'];
		
		// Überprüfen ob alter Dateiname existiert, wenn ja Datei umbenennen
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $this->nameNew))
			$this->rename = "-1";
		elseif(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameOld)) {
			
			$isGallFolder	= strpos($folder, CC_GALLERY_FOLDER . "/") === 0;
			
			// Falls eine Bilddatei oder Galeriebilddatei, Dateinamen im Ordner "thumbs" und Elternordner ändern
			if($folder == CC_IMAGE_FOLDER . "/thumbs"
			|| $isGallFolder
			) {
			
				$folder = substr($folder, 0, -7);
				
				$this->renameImageSrcset($this->nameNew, $filenameOld, $folder);
				
				// Falls ein Galeriebild umbenannt wurde, Galeriedatenbank aktualisieren
				if($isGallFolder) {
							  
					$filenameOldDB		= $this->DB->escapeString($filenameOld);
					$filenameNewDB		= $this->DB->escapeString(substr($folder, 16));
					$this->nameNewDB	= $this->DB->escapeString($this->nameNew);
					
					// db-Tabelle sperren
					$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "galleries`, `" . DB_TABLE_PREFIX . "galleries_images`");
					
				 
					// Datenbankupdate
					$this->updateSQL = $this->DB->query( "UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
															SET `img_file` = '$this->nameNewDB' 
														 WHERE `gallery_id` = (SELECT `id` FROM `" . DB_TABLE_PREFIX . "galleries` WHERE `gallery_name` = '$filenameNewDB') 
															AND `img_file` = '$filenameOldDB' 
														");
							
					
					// db-Sperre aufheben
					$unLock = $this->DB->query("UNLOCK TABLES");
					
				}
					
			}
			
			// Rename file
			if(@rename(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameOld, PROJECT_DOC_ROOT . '/' . $folder . '/' . $this->nameNew)) {
				
				$this->rename = "2";
				
				// If video in gallery folder
				if($isGallFolder) {
					$filenameBaseOld	= pathinfo($filenameOld, PATHINFO_FILENAME);
					$filenameBaseNew	= pathinfo($this->nameNew, PATHINFO_FILENAME);
					if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseOld . '.mp4'))
						@rename(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseOld . '.mp4', PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseNew . '.mp4');
					if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseOld . '.webm'))
						@rename(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseOld . '.webm', PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseNew . '.webm');
					if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseOld . '.ogv'))
						@rename(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseOld . '.ogv', PROJECT_DOC_ROOT . '/' . $folder . '/' . $filenameBaseNew . '.ogv');
				}
				
				if(strpos($folder, CC_FILES_FOLDER) === 0)
					$folder = substr($folder, 12) . '/';
				else
					$folder = "";
						  
				// Inhaltstabellen aktualisieren, falls aktiviert und kein Galleriebild
				if(strpos($folder, CC_GALLERY_FOLDER . "/") === false && isset($GLOBALS['_GET']['dbupdate']) && $GLOBALS['_GET']['dbupdate'] == 1) {
				
					$conTables	= array("articles_categories","news_categories","planner_categories","articles","news","planner");
					$conTables	= array_merge(parent::getContentPreviewTables(), $conTables);
					
					$filenameOldDB = $this->DB->escapeString($folder) . $this->DB->escapeString($filenameOld);
					$this->nameNewDB = $this->DB->escapeString($folder) . $this->DB->escapeString($this->nameNew);
					
					foreach($conTables as $conTable) {
					
						$conTableDB		= $this->DB->escapeString(DB_TABLE_PREFIX . $conTable);
						
						// db-Tabelle sperren
						$lock = $this->DB->query("LOCK TABLES `" . $conTableDB . "`");
				
	
						// Suche nach Spaltennamen
						$queryCols = $this->DB->query("SHOW COLUMNS 
															FROM `" . $conTableDB . "`
															");
					
						foreach($queryCols as $queryCol) {
							
							// Datenbankfelder ausschließen
							if(
							   ((strpos($conTable, "categories") !== false) && $queryCol['Field'] == "image") || 
							   (($conTable == "articles" || $conTable == "news" || $conTable == "planner") && strpos($queryCol['Field'], "object") === 0) || 
							   ((strpos($conTable, "content") !== false) && strpos($queryCol['Field'], "con") === 0)
							) {
								
								// Ab Tabelle articles_categories die Slashes in den Strings ersetzen durch >
								if($conTable == "articles_categories") {
									$filenameOldDB = str_replace("/", ">", $this->DB->escapeString($folder)) . $this->DB->escapeString($filenameOld);
									$this->nameNewDB = str_replace("/", ">", $this->DB->escapeString($folder)) . $this->DB->escapeString($this->nameNew);
								}
								
								// Suche nach unterschiedichen Inhaltsspalten
								$replaceSQL = $this->DB->query( "UPDATE `" . $conTableDB . "`
																		SET `" . $queryCol['Field'] . "` = REPLACE(`" . $queryCol['Field'] . "`, '$filenameOldDB', '$this->nameNewDB')
																		");
								
								#var_dump($replaceSQL.$queryCol['Field']);
							}
						}
					}
				}
			}
			else
				$this->rename = "-2";
		}
		else
			$this->rename = "-3";
		
		echo(json_encode(array(	"result"	=> (string)$this->rename)));
	
	}

	
 	/**
	 * renameImageSrcset
	 * 
	 * @param	string	$filenameNew	Filename Dateiname neu
	 * @param	string	$filenameOld	Filename Dateiname alt
	 * @param	string	$folder			Folder
	 * @access	public
	 */	 
	public function renameImageSrcset($filenameNew, $filenameOld, $folder)
	{
	
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/thumbs/' . $filenameOld))
			@rename(PROJECT_DOC_ROOT . '/' . $folder . '/thumbs/' . $filenameOld, PROJECT_DOC_ROOT . '/' . $folder . '/thumbs/' . $filenameNew);
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/small/' . $filenameOld))
			@rename(PROJECT_DOC_ROOT . '/' . $folder . '/small/' . $filenameOld, PROJECT_DOC_ROOT . '/' . $folder . '/small/' . $filenameNew);
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/medium/' . $filenameOld))
			@rename(PROJECT_DOC_ROOT . '/' . $folder . '/medium/' . $filenameOld, PROJECT_DOC_ROOT . '/' . $folder . '/medium/' . $filenameNew);
	
	}
	
	
	// showHideGalleryImage
	public function showHideGalleryImage()
	{
		
		$pubIDs		= array();
		$updateSQL1	= false;
		$notice 	= "{s_notice:puball}";
		$queryExt1	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
			
			$id		= $GLOBALS['_GET']['entryid'];
			
			if(isset($GLOBALS['_GET']['pub']) && $GLOBALS['_GET']['pub'] == 1)
				$pub = 1;
			else
				$pub = 0;
				
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array"
			&& isset($GLOBALS['_POST']['entryID'])
			&& isset($GLOBALS['_POST']['entryNr'])
			) {
				$pubIDs		= array_intersect_key($GLOBALS['_POST']['entryID'], $GLOBALS['_POST']['entryNr']);
			}
			else
				$pubIDs[]	= (int)$id;
			
			if(count($pubIDs) > 0) {
			
				// IDs durchlaufen				
				foreach($pubIDs as $key => $IDvalue) {
					
					$pubID	= (int)$IDvalue;
					$queryExt1 .= " OR `id` = " . $pubID;
				}
				
			}
			else
				$queryExt1 = " OR `id` = " . (int)$id;

			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "galleries_images`");
					
			
			// Query show
			$updateSQL1 = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
													SET `show` = " . $pub . " 
												 WHERE `id` = '' 
												 $queryExt1
												");	
			
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
			echo $updateSQL1;
		}

	}
	
	
	// showHideDataEntries
	public function showHideDataEntries()
	{
		
		$pubIDs		= array();
		$updateSQL1	= false;
		$notice 	= "{s_notice:puball}";
		$queryExt1	= "";
		$listCat	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
			
			$id		= $GLOBALS['_GET']['entryid'];
			
			if(isset($GLOBALS['_GET']['pub']) && $GLOBALS['_GET']['pub'] == 1)
				$pub = 1;
			else
				$pub = 0;
				
			if(isset($GLOBALS['_GET']['list_cat']) && $GLOBALS['_GET']['list_cat'] != "")
				$listCat = $GLOBALS['_GET']['list_cat'];
			
			
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array"
			&& isset($GLOBALS['_POST']['entryID'])
			&& isset($GLOBALS['_POST']['entryNr'])
			) {
				$pubIDs		= array_intersect_key($GLOBALS['_POST']['entryID'], $GLOBALS['_POST']['entryNr']);
			}
			else
				$pubIDs[]	= (int)$id;
			
			if(count($pubIDs) > 0) {
			
				// IDs durchlaufen				
				foreach($pubIDs as $key => $IDvalue) {
					
					$pubID	= (int)$IDvalue;
					$queryExt1 .= " OR `id` = " . $pubID;
				}
			
			
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . $this->dataTableDB . "`");
						
				
				// published setzen
				$updateSQL1 = $this->DB->query("UPDATE `" . $this->dataTableDB . "` 
													 SET `published` = " . $pub . " 
													 WHERE `id` = '' 
													 $queryExt1
													");
					
				
								
					
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
				
				// Ggf. bei Kommentaren notification mails versenden
				if(COMMENTS_MODERATE
				&& $pub == 1
				&& $this->dataTable	== "comments"
				) {
			
					require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";
				
					$o_comments			= new Comments($this->DB, $this->o_lng);
			
					$o_comments->getThemeDefaults();
			
					parent::$staText 			= parse_ini_file(PROJECT_DOC_ROOT . '/langs/' . $this->lang . '/staticText_' . $this->lang . '.ini', true);
					
					// IDs durchlaufen				
					foreach($pubIDs as $key => $IDvalue) {
					
						$this->sendCommentNotifications($IDvalue, $o_comments);
					
					}
				}				
			}
		}
		
		if($updateSQL1 === true)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('hint', "{s_error:error}");
	
		header("location: " . ADMIN_HTTP_ROOT . "?task=modules&type=" . $this->modType . ($listCat != "" ? '&list_cat=' . $listCat : ''));
		exit;

	}
	
	
	// deleteNewslEntries
	public function deleteNewslEntries()
	{
	
		$delIDs		= array();
		$deleteSQL1	= false;
		$notice		= "{s_notice:deldata}";
		$queryExt1	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
			
			$id		= $GLOBALS['_GET']['entryid'];
			
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array" && isset($GLOBALS['_POST']['entryNr'])) {
				$delIDs		= $GLOBALS['_POST']['entryNr'];
			}
			else
				$delIDs[]	= (int)$id;
			
			if(count($delIDs) > 0) {
			
				// Cats durchlaufen				
				foreach($delIDs as $key => $IDvalue) {
					
					$dataID		= (int)$IDvalue;
					$queryExt1 .= " OR `id` = ".$dataID;
				}
				

				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . $this->dataTableDB . "`");
						
				
				// Eintrag löschen
				$deleteSQL1 = $this->DB->query("DELETE FROM `" . $this->dataTableDB . "` 
													 WHERE `id` = '' 
													 $queryExt1
													");
					
							
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
			
			}
			
		}
		
		if($deleteSQL1 === true)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('hint', "{s_error:error}");
		
		#echo($deleteSQL1);
		header("location: " . ADMIN_HTTP_ROOT . "?task=campaigns&type=" . $this->modType);
		exit;

	}
	
	
	// deleteDataCats
	public function deleteDataCats()
	{
	
		$catIDs		= array();
		$deleteSQL1	= false;
		$deleteSQL2	= false;
		$notice		= "{s_notice:del" . $this->modType . "}";
		$queryExt1	= "";
		$queryExt2	= "";
		$queryExt3	= "";
		
		if(isset($GLOBALS['_GET']['catid'])) {
			
			$catID	= $GLOBALS['_GET']['catid'];			
			
			// Falls id=array, mehrere IDs aus Post auslesen
			if($catID == "array" && isset($GLOBALS['_POST']['catIDs'])) {
				$catIDs	= $GLOBALS['_POST']['catIDs'];
			}
			else
				$catIDs[]	= (int)$catID;
			
			if(count($catIDs) > 0) {
			
				// Cats durchlaufen
				foreach($catIDs as $key => $IDvalue) {
					
					$delID		= (int)$IDvalue;
					$queryExt1 .= " OR `cat_id` = ".$delID;
					$queryExt2 .= " OR `parent_cat` = ".$delID;
	
					// Finden zugehöriger Dateneinträge
					$queryData	= $this->DB->query("SELECT id 
															FROM $this->dataTableDB 
															WHERE `cat_id` = $delID 
														");
					
					if(is_array($queryData)
					&& count($queryData) > 0
					) {
						
						// Kommentare durchlaufen
						foreach($queryData as $dataID) {
							
							$dataID		= $this->DB->escapeString($dataID['id']);
							$queryExt3 .= " OR `entry_id` = ".$dataID;
						}
					}
				}
				
			}
			
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES  `$this->catTableDB`, `$this->dataTableDB`, `" . DB_TABLE_PREFIX . "comments`");
			

			// Löschen der Kategorie
			$deleteSQL1 = $this->DB->query("DELETE FROM `" . $this->catTableDB . "` 
												WHERE `cat_id` = '' 
												$queryExt1 
												");
	
	
			// Löschen der zugehörigen Dateneinträge
			$deleteSQL2 = $this->DB->query("DELETE FROM `" . $this->dataTableDB . "` 
												WHERE `cat_id` = '' 
												$queryExt1 
												");

			
			// Löschen zugehöriger Kommentare
			$deleteSQL3 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "comments` 
												WHERE `table` = '" . $this->modType . "' 
												AND `entry_id` = '' 
												$queryExt3
												");


			// Update der parent_cat Einträge bei Unterkategorien
			$updateSQL1 = $this->DB->query("UPDATE `" . $this->catTableDB . "` 
											    SET `parent_cat` = '' 
												WHERE `parent_cat` = '' 
												$queryExt2 
												");


			// Sortierung neu durchführen
			// Variable für Neusortierung setzen
			$updateSQL2a = $this->DB->query("SET @c:=0;
												");			
			
			// Neusortierung
			$updateSQL2b = $this->DB->query("UPDATE " . $this->catTableDB . " 
												SET `sort_id` = (SELECT @c:=@c+1)
												ORDER BY `sort_id` ASC;
												");

			#var_dump($deleteSQL);
				
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
		}
		
		if($deleteSQL1 === true 
		&& $deleteSQL2 === true
		)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('hint', "{s_error:error}");
		
		$this->setSessionVar('list_cats', true);

		// Falls sich von vorher noch eine id zum Bearbeiten von News in der Session befindet, diese löschen
		if(isset($this->g_Session[$this->modType . '_id']))
			$this->unsetSessionKey($this->modType . '_id');
		
		#echo($deleteSQL1.$deleteSQL2);
		header("location: " . ADMIN_HTTP_ROOT . "?task=modules&type=" . $this->modType . $this->redExt);
		exit;

	}
	
	
	// deleteDataEntries
	public function deleteDataEntries()
	{
	
		$delIDs		= array();
		$deleteSQL1	= false;
		$deleteSQL2	= false;
		$notice		= "{s_notice:deldata}";
		$queryExt1	= "";
		$queryExt2	= "";
		$queryExt3	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
			
			$id		= $GLOBALS['_GET']['entryid'];
			
			if(isset($GLOBALS['_GET']['list_cat']) && $GLOBALS['_GET']['list_cat'] != "")
				$listCat = $GLOBALS['_GET']['list_cat'];
				
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array"
			&& isset($GLOBALS['_POST']['entryID'])
			&& isset($GLOBALS['_POST']['entryNr'])
			) {
				$delIDs		= array_intersect_key($GLOBALS['_POST']['entryID'], $GLOBALS['_POST']['entryNr']);
			}
			else
				$delIDs[]	= (int)$id;
			
			if(count($delIDs) > 0) {
			
				// Cats durchlaufen				
				foreach($delIDs as $key => $IDvalue) {
					
					$dataID		= (int)$IDvalue;
					$queryExt1 .= " OR `id` = ".$dataID;
					$queryExt2 .= " OR `entry_id` = ".$dataID;
					$queryExt3 .= " OR `id` = ".$dataID;
				}
				

				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . $this->dataTableDB . "`, `" . DB_TABLE_PREFIX . "comments`, `" . DB_TABLE_PREFIX . "rating`");
				
				
				// Eintrag löschen
				$deleteSQL1 = $this->DB->query("DELETE FROM `" . $this->dataTableDB . "` 
													 WHERE `id` = '' 
													 $queryExt1
													");
					
		
				// Falls Datenmodultyp
				if(in_array($this->modType, $this->dataModuleTypes)) {
				
					// Eintrag löschen
					$deleteSQL2 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "comments` 
														WHERE `table` = '" . $this->dataTable . "' 
														AND `entry_id` = '' 
														$queryExt2
														");
			
					// Löschen der zugehörigen Bewertungen
					$deleteSQL3 = $this->DB->query("DELETE 
														FROM `" . DB_TABLE_PREFIX . "rating` 
														WHERE `module` = '" . $this->modType . "' 
														AND `id` = '' 
														$queryExt3
														");
					#var_dump($deleteSQL1);
				
				}			
					
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
			
			}
			
		}
		
		if($deleteSQL1 === true 
		&& $deleteSQL2 === true
		)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('hint', "{s_error:error}");
		
		#echo($deleteSQL1.$deleteSQL2);
		header("location: " . ADMIN_HTTP_ROOT . "?task=modules&type=" . $this->modType . (!empty($listCat) ? '&list_cat=' . $listCat : ''));
		exit;

	}
	
	
	// deleteDataEntry
	public function deleteDataEntry($dataID, $catID, $sortID = "")
	{

		// Falls ein Eintrag gelöscht werden soll
		$dataID		= $this->DB->escapeString($dataID);
		$sortID		= (int)$sortID;
		
		if(empty($dataID))
			return false;
		
		
		if($catID == "all") {
		
			// bestimmen der catID
			$queryCat = $this->DB->query("SELECT `cat_id` 
												FROM `$this->dataTableDB` 
												WHERE id = $dataID 
												");
			
			$catID	= $queryCat[0]['cat_id'];
		}
		
		$catID		= (int)$catID;
		
		
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`, `" . DB_TABLE_PREFIX . "comments`, `" . DB_TABLE_PREFIX . "rating`");

		// Löschen des Dateneintrags
		$deleteSQL1 = $this->DB->query("DELETE 
											FROM `$this->dataTableDB` 
											WHERE `id` = $dataID 
											");
		#var_dump($deleteSQL1);
		
		// Falls Datenmodultyp
		if(in_array($this->modType, $this->dataModuleTypes)) {
			
		
			// Aktualisieren der sort_ids
			$updateSQL = $this->DB->query("UPDATE `$this->dataTableDB` 
												SET `sort_id` = `sort_id`-1
												WHERE `sort_id` > $sortID 
												AND `cat_id` = $catID
												");
			
			// Löschen der zugehörigen Kommentare
			$deleteSQL2 = $this->DB->query("DELETE 
												FROM `" . DB_TABLE_PREFIX . "comments` 
												WHERE `table` = '$this->dataTable' 
												AND `entry_id` = $dataID 
												");
			
			// Löschen der zugehörigen Bewertungen
			$deleteSQL3 = $this->DB->query("DELETE 
												FROM `" . DB_TABLE_PREFIX . "rating` 
												WHERE `module` = '$this->modType' 
												AND `cat_id` = $catID
												AND `id` = $dataID 
												");
			#var_dump($deleteSQL1);
		
		}
		
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		
		// Falls sich von vorher noch eine id zum Bearbeiten von News in der Session befindet, diese löschen
		if(isset($this->g_Session[$this->modType . '_id']))
			$this->unsetSessionKey($this->modType . '_id');
		
		if(isset($this->g_Session['newsl_id']))
			$this->unsetSessionKey('newsl_id');
		
		
		// Falls vom Front-End aus gelöscht wurde, zur Seite zurückgehen
		if(!empty($GLOBALS['_GET']['red'])) {
			
			$redirect = htmlspecialchars($GLOBALS['_GET']['red']);
			
			$this->setSessionVar('notice', "{s_notice:deldata}");
			
			if(isset($GLOBALS['_GET']['redext']) && $GLOBALS['_GET']['redext'] == "admin") {
				$redirect = ADMIN_HTTP_ROOT . "?task=modules&type=" . $redirect . "&list_cat=" . $catID;
				echo $redirect;
			}
			else {
				header("Location: " . $redirect);
			}
			exit;
		}
	
		return true;
	
	}
	
	
	// publishDataEntry
	public function publishDataEntry($entryID, $publish, $dataTable, $type = "data")
	{
	
		if(empty($entryID))
			return false;
		
		$entryID	= (int)$entryID;
		$publish	= $this->DB->escapeString($publish);	
		$result		= "";
		
		if($type == "newsl")
			$published = "`sent`";
		else
			$published = "`published`";

		// Aktualisieren des Dateneintrags
		$updateSQL = $this->DB->query( "UPDATE `$dataTable` 
											SET $published = $publish 
										WHERE id = $entryID
										");

		
		// Ggf. bei Kommentaren notification mails versenden
		if(COMMENTS_MODERATE
		&& $publish == 1
		&& $this->dataTable	== "comments"
		) {
		
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";
		
			$o_comments					= new Comments($this->DB, $this->o_lng);
			
			$o_comments->getThemeDefaults();
			
			parent::$staText 			= parse_ini_file(PROJECT_DOC_ROOT . '/langs/' . $this->lang . '/staticText_' . $this->lang . '.ini', true);
			
			$this->sendCommentNotifications($entryID, $o_comments);
		
		}				
		
		echo "$updateSQL";
		exit;

	}
	
	
	// deleteGBookEntries
	public function deleteGBookEntries()
	{
		
		$delIDs		= array();
		$deleteSQL1	= false;
		$deleteSQL2	= false;
		$notice = "{s_notice:delgbs}";
		$queryExt1	= "";
		$queryExt2	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
			
			$id		= $GLOBALS['_GET']['entryid'];
			
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array"
			&& isset($GLOBALS['_POST']['entryID'])
			&& isset($GLOBALS['_POST']['entryNr'])
			) {
				$delIDs		= array_intersect_key($GLOBALS['_POST']['entryID'], $GLOBALS['_POST']['entryNr']);
			}
			else
				$delIDs[]	= (int)$id;
			
			if(count($delIDs) > 0) {
			
				// Cats durchlaufen				
				foreach($delIDs as $key => $IDvalue) {
					
					$gbID		= (int)$IDvalue;
					$queryExt1 .= " OR `id` = ".$gbID;
					$queryExt2 .= " OR `entry_id` = ".$gbID;
				}

			
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "gbook`, `" . DB_TABLE_PREFIX . "comments`");
				
				
				// Eintrag löschen
				$deleteSQL1 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "gbook` 
													 WHERE `id` = '' 
													 $queryExt1
													");
					
				
				// Eintrag löschen
				$deleteSQL2 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "comments` 
													 WHERE `table` = 'gbook' 
													 AND `entry_id` = '' 
													 $queryExt2
													");
								
					
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
			
			}
			
		}
		
		if($deleteSQL1 === true 
		&& $deleteSQL2 === true
		)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('hint', "{s_error:error}");
		
		#echo($deleteSQL1.$deleteSQL2);
		header("location: " . ADMIN_HTTP_ROOT . "?task=modules&type=gbook");
		exit;

	}
	
	
	// deleteComments
	public function deleteComments()
	{
		
		$delIDs		= array();
		$deleteSQL	= false;
		$notice		= "{s_notice:delcoms}";
		$queryExt	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
			
			$id		= $GLOBALS['_GET']['entryid'];
			
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array"
			&& isset($GLOBALS['_POST']['entryID'])
			&& isset($GLOBALS['_POST']['entryNr'])
			) {
				$delIDs		= array_intersect_key($GLOBALS['_POST']['entryID'], $GLOBALS['_POST']['entryNr']);
			}
			else
				$delIDs[]	= (int)$id;
			
			if(count($delIDs) > 0) {
			
				// Cats durchlaufen				
				foreach($delIDs as $key => $IDvalue) {
					
					$comID		= (int)$IDvalue;
					$queryExt  .= " OR `id` = ".$comID;
				}
			
			
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "comments`");
						
				
				// Eintrag löschen
				$deleteSQL = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "comments` 
													 WHERE `id` = '' 
													 $queryExt
													");
								
					
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
			}
			
		}
		
		if($deleteSQL === true)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('hint', "{s_error:error}");
		
		header("location: " . ADMIN_HTTP_ROOT . "?task=modules&type=comments");
		exit;

	}
	
	
	// deleteUserImage
	public function deleteUserImage()
	{
	
		$result	= 0;
		
		if(isset($GLOBALS['_GET']['userid'])) {
			
			$userID	= $GLOBALS['_GET']['userid'];
			$result	= User::deleteUserImage($userID);
		}
		
		echo($result);
		exit;

	}
	
	
	// deleteIPLogEntries
	public function deleteIPLogEntries()
	{
	
		$delIPs		= array();
		$deleteSQL1	= false;
		$deleteSQL2	= false;
		$notice 	= "";
		$queryExt1	= "";
		$queryExt2	= "";
		
		if(isset($GLOBALS['_GET']['ip'])) {
			
			$ip		= $this->DB->escapeString($GLOBALS['_GET']['ip']);
			
			// Falls ip=array, mehrere IPs aus Post auslesen
			if($ip == "array"
			&& isset($GLOBALS['_POST']['botNr'])
			&& isset($GLOBALS['_POST']['botIP'])
			) {
				$delIPs		= array_intersect_key($GLOBALS['_POST']['botIP'], $GLOBALS['_POST']['botNr']);
			}
			else
				$delIPs[]	= $ip;
			
			if(count($delIPs) > 0) {
				
				foreach($delIPs as $key => $IPvalue) {
					
					$botIP		= $this->DB->escapeString($IPvalue);
					$queryExt1 .= " OR `realIP` LIKE '".$botIP."-%'";
					$queryExt2 .= " OR `realIP` = '".$botIP."'";
				}
				
		
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "log`, `" . DB_TABLE_PREFIX . "log_bots`");
						
				
				// Falls die Log-Daten entfernt werden sollen, sprich keine echten Besucherdaten sind
				if(!isset($GLOBALS['_GET']['valid']) || $GLOBALS['_GET']['valid'] != 1) {
					
					// log-Eintrag löschen
					$deleteSQL1 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "log` 
														 WHERE `realIP` LIKE '".$ip."-%' 
														 $queryExt1
														");
					
					$notice = "{s_notice:delip}";
				}
				else {
					$deleteSQL1	= true;
					$notice = "{s_notice:delipbot}";
				}
				
				// log-Eintrag löschen
				$deleteSQL2 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "log_bots` 
													 WHERE `realIP` = '".$ip."' 
													 $queryExt2
													");
								
					
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
			}
			
		}
		
		if($deleteSQL1 === true 
		&& $deleteSQL2 === true
		)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('hint', "{s_error:error}");
		
		// Ggf. Sortierung
		if(isset($GLOBALS['_GET']['by']) && isset($GLOBALS['_GET']['sort']))
			$queryStrExt	= "&by=" . htmlspecialchars($GLOBALS['_GET']['by']) . "&sort=" . htmlspecialchars($GLOBALS['_GET']['sort']);
		else
			$queryStrExt	= "";
	
		#echo $deleteSQL1.$deleteSQL2;
		header("location: " . ADMIN_HTTP_ROOT . "?task=stats" . $queryStrExt);
		exit;

	}
	
	
	// generateTagList
	public function generateTagList()
	{
	
		// Zunächst das entsprechende Modul einbinden (Tagcloud-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Tags.php";

		if(empty($this->modType))
			$this->modType = "articles,news,planner";
		
		$excludeTags = array();
		
		if(isset($GLOBALS['_GET']['chosentags'])) {
			$excludeTags = explode(",", $GLOBALS['_GET']['chosentags']);
			$excludeTags = array_map('trim', $excludeTags);
		}
		
		$o_tags		= new Tags($this->DB, $this->editLang, $this->loggedUserGroup, $this->loggedUserOwnGroups);
		$tags		= $o_tags->getTags($this->modType, 50);
		$tagList	= $o_tags->getTagList($excludeTags);
		
		echo $tagList;
		exit;
	
	}
	
	
	// resetVotes
	public function resetVotes()
	{
	
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Rating.php"; // Ratingklasse einbinden
	
		$o_rating	= new Rating($this->DB, true);
		
		$delIDs		= array();
		$deleteSQL1	= false;
		$notice 	= "{s_notice:resvotes}";
		$queryExt1	= "";
		$listCat	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
		
			$id		= $GLOBALS['_GET']['entryid'];
			
			if(isset($GLOBALS['_GET']['list_cat']) && $GLOBALS['_GET']['list_cat'] != "")
				$listCat = $GLOBALS['_GET']['list_cat'];
				
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array"
			&& isset($GLOBALS['_POST']['entryID'])
			&& isset($GLOBALS['_POST']['entryNr'])
			) {
				$delIDs		= array_intersect_key($GLOBALS['_POST']['entryID'], $GLOBALS['_POST']['entryNr']);
			}
			else
				$delIDs[]	= (int)$id;
			
			if(count($delIDs) > 0) {
			
				// IDs durchlaufen				
				foreach($delIDs as $key => $IDvalue) {
					
					$voteID	= (int)$IDvalue;
					$vote	= $o_rating->resetVotes($this->modType, $voteID);
				}
			
				$this->setSessionVar('notice', $notice);
				
			}
		}
		else
			$this->setSessionVar('hint', "{s_error:error}");
		
		header("location: " . ADMIN_HTTP_ROOT . "?task=modules&type=" . $this->modType . ($listCat != "" ? '&list_cat=' . $listCat : ''));
		exit;

	}
	
	
	// editComment
	public function editComment()
	{

		$queryExt	= "";
		$colExt		= "";
		
		// Falls Gästebuch, mindestens Editorlog
		if($this->modType == "gbook") {
			$o_security	= $this->o_security;
			require_once "../inc/checkEditorAccess.inc.php"; // Berechtigung prüfen
			$colExt		= "gb";
		}
		elseif(!$this->editorLog) // Falls Author, Editing auf eigene Kommentare begrenzen
			$queryExt	= " AND `userid` = ";
		
		$commentText	= str_ireplace(array("<br>","<br />"), "\n", $GLOBALS['_POST']['commentEditText']);
		$commentText	= $this->DB->escapeString($commentText);
	  
		// Datenbankupdate
		$queryUpdate = $this->DB->query( "UPDATE `" . $this->dataTableDB . "` 
											SET `" . $colExt . "comment` = '$commentText' 
											WHERE `id` = $this->editID
											$queryExt
										 ");		
	
		if($queryUpdate === true)
			echo '<p class="notice success">' . ContentsEngine::replaceStaText("{s_notice:takechange}") . '</p>' . "\n";
		else
			echo "0";
		exit;

	}
	
	
	// sendCommentNotifications
	public function sendCommentNotifications($entryID, $o_comments)
	{

		$commTarget = $this->DB->query("SELECT `table`, `entry_id`, `author` 
											FROM `$this->dataTableDB` 
										WHERE id = $entryID
										");

		$targetID		= $commTarget[0]["entry_id"];
		$targetTable	= $commTarget[0]["table"];
		$authorName		= $commTarget[0]["author"];
		$domain			= str_replace(array("http://","https://","www."), "", PROJECT_HTTP_ROOT);
		
		if($targetTable == "gbook")
			return false;
		
		
		$targetPage = $this->DB->query("SELECT p.`target_page`, p.`cat_id`, n.`header_" . $this->lang . "` 
											FROM `" . DB_TABLE_PREFIX . $targetTable . "_categories` AS p 
										LEFT JOIN `" . DB_TABLE_PREFIX . $targetTable . "` AS n 
											ON n.`cat_id` = p.`cat_id`
										WHERE n.`id` = $targetID
										");

		
		$o_comments->authorName		= htmlspecialchars($authorName);
		$o_comments->domain			= $domain;
		$o_comments->mailSubject	= htmlspecialchars(parent::replaceStaText("{s_header:newcomment}") . ' ' . $authorName . ' - ' . $domain);
		$o_comments->dataAbsPath	= PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath($targetPage[0]["target_page"], $this->lang, false) . '/' . Modules::getAlias($targetPage[0]["header_" . $this->lang]) . '-' . $targetPage[0]["cat_id"] . $targetTable[0] . $targetID . PAGE_EXT;

		
		$queryRecipients	= $o_comments->getCommentSubscribers($targetTable, $targetID);
		$result				= $o_comments->notifyCommentSubscribers($queryRecipients, $targetTable, $targetID);

	}
	
	
	// getAuthorSelect
	public function getAuthorSelect()
	{

		$o_user	= new User($this->DB, $this->o_lng);
		
		$users	= $o_user->getAuthorNames($this->group);
	
		echo(json_encode($users));

		exit;

	}
	
	
	// setDataAuthor
	public function setDataAuthor()
	{

		$authorID	= $this->DB->escapeString($GLOBALS['_POST']['value']);
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`");
		
	 
		// Datenbankupdate
		$this->updateSQL = $this->DB->query( "UPDATE `$this->dataTableDB` 
												SET `author_id` = '$authorID' 
												WHERE `id` = '$this->editID'
											");
				
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

	}
	
	
	// setDataDate
	public function setDataDate()
	{

		$dateTime	= trim($GLOBALS['_POST']['value']);

		if($this->modType == "planner") {
			$dateTime	= explode(" ", $dateTime);
			$dataDate	= $this->DB->escapeString(reset($dateTime));
			$dataTime	= $this->DB->escapeString(end($dateTime));
			$updStr		= "`date` = '$dataDate', `time` = '$dataTime'";
		}
		else
			$updStr		= "`date` = '" . $this->DB->escapeString($dateTime) . "'";
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`");
		
	 
		// Datenbankupdate
		$this->updateSQL = $this->DB->query( "UPDATE `$this->dataTableDB` 
												SET $updStr
												WHERE `id` = '$this->editID'
											");
				
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

	}

} // end class EditModules
