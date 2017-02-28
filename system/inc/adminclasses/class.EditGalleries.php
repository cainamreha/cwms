<?php
namespace Concise;


###################################################
################  EditGalleries  ##################
###################################################

// Galerien editieren	

class EditGalleries extends Admin
{

	private $tableGall		= "galleries";
	private $tableGallImg	= "galleries_images";

	public function __construct($DB, $o_lng)
	{

		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
		
		$this->tableGall		= DB_TABLE_PREFIX . $this->tableGall;
		$this->tableGallImg		= DB_TABLE_PREFIX . $this->tableGallImg;
	}
	
	public function conductAction()
	{
	
		$action = "";
		$type	= "";
		$output	= "";

		if(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] != "")
			$action = $GLOBALS['_GET']['action'];

		if(!empty($GLOBALS['_GET']['type']))
			$type = $GLOBALS['_GET']['type'];
		
		if($type == "gallery") {
		
			if(!empty($GLOBALS['_GET']['gal'])) {
			
				$gal	= $GLOBALS['_GET']['gal'];
			
				if(	$action == "repair")
					$output		= $this->repairGallery($gal);
				
				if(	$action == "galltags")		
					$output		= $this->getGalleryTags($gal);
			}
			if(	$action == "allgalltags")			
				$output		= $this->getAllGalleryTags();
		}
		
		return $output;
	
	}

	
	/**
	 * Galerie neu anlegen
	 * 
	 * @param	string		$gallName	Galeriename
	 * @param	boolean		$check		check if exists
	 * @access	public
	 */	 
	public function createGallery($gallName, $check = true)
	{

		$galDB		= $this->DB->escapeString($gallName);
		$insertGall	= false;

		// Falls Galerie bereits vorhanden
		if($check && $this->getGallIdByName($gallName))
			return false;
	
	
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tableGall`, `$this->tableGallImg`");
	
	
		// Galerie neu anlegen
		// Gall-sort-ID bestimmen
		$querySortID	= $this->DB->query("SELECT MAX(`sort_id`) 
												FROM `$this->tableGall` 
											");
												
		$maxSortID		= max($querySortID[0]['MAX(`sort_id`)'] +1, 1);
		
		// Galerie anlegen
		$insertGall	= $this->DB->query("INSERT INTO `$this->tableGall` 
													(`sort_id`,
													`gallery_name`,
													`group`,
													`create_date`,
													`active`)
												VALUES(
													$maxSortID,
													'$galDB',
													'public',
													NOW(),
													1)
								 ");

								 
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		return $insertGall;

	}
	
	/**
	 * Get gallery id by name
	 * 
	 * @param	string		$gallName	Galeriename
	 * @param	boolean		$create		create gallery if not exists
	 * @access	public
	 */	 
	public function getGallIdByName($gallName, $create = false)
	{

		$galDB		= $this->DB->escapeString($gallName);
		
		// Gall-ID bestimmen
		$queryID	= $this->DB->query("SELECT `id` 
											FROM `$this->tableGall` 
										WHERE `gallery_name` = '$galDB' 
										");
		
		// Falls Galerie nicht vorhanden
		if($queryID === false
		|| count($queryID) == 0
		) {
		
			// neu anlegen
			if($create && $this->createGallery($gallName, false) === true)
				return $this->getGallIdByName($gallName, false);
			
			return false;			
		}
		
		return $queryID[0]['id'];

	}


	/**
	 * Galerien reparieren
	 * 
	 * @param	string		$gallName	Galeriename
	 * @access	public
	 */	 
	public function repairGallery($gal)
	{
	
		$galDB			= $this->DB->escapeString($gal);
		$gallPath		= PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $gal;
		$selectSQL		= array();
		$deleteStr		= "";
		$updateStr		= "";
		$resort			= false;
		
		// Falls Ordnername leer
		if(empty($gal))
			return "0";
		
		
		// Überprüfen ob Ordner existiert
		if(!is_dir($gallPath))
			return $this->removeGalleryFromDB($galDB);
		

		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tableGall`, `$this->tableGallImg`");
		
		// Gall-ID bestimmen
		$idQuery	= $this->DB->query("SELECT `id` 
											FROM `$this->tableGall` 
										WHERE `gallery_name` = '$galDB' 
										");
		
		// Falls Galerie nicht vorhanden, neu anlegen
		if(count($idQuery) == 0) {
		
			// Gall-sort-ID bestimmen
			$querySortID	= $this->DB->query("SELECT MAX(`sort_id`) 
													FROM `$this->tableGall` 
												");
													
			$maxSortID		= max($querySortID[0]['MAX(`sort_id`)'], 1) +1;
			
			// Galerie anlegen
			$insertGall	= $this->DB->query("INSERT INTO `$this->tableGall` 
														(`sort_id`,
														`gallery_name`,
														`group`,
														`create_date`,
														`active`)
													VALUES(
														$maxSortID,
														'$galDB',
														'public',
														NOW(),
														1)
												");
			// Gall-ID bestimmen
			$idQuery	= $this->DB->query("SELECT `id` 
												FROM `$this->tableGall` 
											WHERE `gallery_name` = '$galDB' 
											");
		}
		
		$gallID	= (int)$idQuery[0]['id'];
		#var_dump($gallID);
	
		// Suche nach Bildern
		$selectSQL = $this->DB->query("SELECT `gallery_id`, `img_file` 
											FROM `$this->tableGallImg` as img	
											LEFT JOIN `$this->tableGall` as gall 
												ON gall.`id` = img.`gallery_id` 
											WHERE `gallery_id` = $gallID 
											");
		
		
		// Nicht vorhandene Dateiinformationen aus Galerie entfernen und überschüssige Dateien in DB eintragen
		// Galerieordner einlesen
		$handle = scandir($gallPath);		
			
		// Falls Bilder vohanden
		if(count($selectSQL) > 0) {
			$gallID	= $selectSQL[0]['gallery_id'];
			$sortID = count($selectSQL) +1;
		}
		else {
			$sortID = 1;
		}
		
		foreach($handle as $gallFile) {
			
			if( strpos($gallFile, ".") !== 0 
				&& !is_dir($gallPath . '/' . $gallFile)
			) {
				
				// Delete-String
				$deleteStr .= "`img_file` != '" . $gallFile . "' AND ";
				
				
				// Überschüssige Dateien hinzufügen
				$checkFile = parent::arraySearch_recursive($gallFile, $selectSQL);
				
				if(!$checkFile)
					// Update-String
					$updateStr .= "(" . $sortID . "," . $gallID . ",'" . $this->DB->escapeString($gallFile) . "',NOW()),";
				
				$sortID++;
			}
		}
		
		
		
		// Falls Dateien nur noch in db vorhanden
		if($deleteStr != ""
		|| $updateStr == "") {
		
			$resort		= true;
		
			// Datenbankupdate
			$deleteSQL = $this->DB->query( "DELETE FROM `$this->tableGallImg` 
												 WHERE " . $deleteStr . "
												 `gallery_id` = $gallID
												");
		}
			
		
		// Falls Dateien noch nicht in db vorhanden
		if($updateStr != "") {
		
			$updateStr	= substr($updateStr, 0, -1);
			$resort		= true;
			
			// Datenbankupdate
			$updateSQL1 = $this->DB->query( "INSERT INTO `$this->tableGallImg` 
													(`sort_id`,`gallery_id`,`img_file`,`upload_date`)
													VALUES " . $updateStr . "
												");
		}
		
	
		// ggf. Sortierung neu durchführen
		if($resort) {
		
			// Variable für Neusortierung setzen
			$updateSQL2a = $this->DB->query("SET @c:=0;
												");
			
			
			// Neusortierung
			$updateSQL2b = $this->DB->query(  "UPDATE `$this->tableGallImg` 
														SET `sort_id` = (SELECT @c:=@c+1)
													WHERE `gallery_id` = $gallID 
													ORDER BY `sort_id` ASC;
												  ");
		}
	
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");


		if($resort 
		&& $updateSQL2a === true 
		&& $updateSQL2b === true
		)
			return "1";
		else
			return "0";				

	}


	/**
	 * Galerien reparieren Datenbank bereinigen
	 * 
	 * @param	string		$gallName	Galeriename
	 * @access	public
	 */	 
	public function removeGalleryFromDB($galDB)
	{
	
		$deleteSQL1 = true;
		$deleteSQL2 = true;
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tableGall`, `$this->tableGallImg`");
		
		// Gall-ID bestimmen
		$idQuery	= $this->DB->query("SELECT `id` 
											FROM `$this->tableGall` 
										WHERE `gallery_name` = '$galDB' 
										");
		
		if(count($idQuery) > 0) {
		
			$gallID	= (int)$idQuery[0]['id'];
			
			// Datenbankupdate
			$deleteSQL1 = $this->DB->query( "DELETE FROM `$this->tableGallImg` 
												 WHERE `gallery_id` = $gallID
												");
			
			// Datenbankupdate
			$deleteSQL2 = $this->DB->query( "DELETE FROM `$this->tableGall` 
												 WHERE `id` = $gallID
												");
		}
	
	
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		if($deleteSQL1 === true
		&& $deleteSQL2 === true
		)
			return 1;
		else
			return 0;
	
	}



	/**
	 * Galerien reparieren Datenbank bereinigen
	 * 
	 * @param	string		$gallName	Galeriename
	 * @access	public
	 */	 
	public function getGalleryTags($gal)
	{
	
		$galDB	= $this->DB->escapeString($gal);
		$tags	= array();
		$output	= array();
		
		
		// Gall-ID bestimmen
		$tagsQuery	= $this->DB->query("SELECT `tags` 
											FROM `$this->tableGall` 
										WHERE `gallery_name` = '$galDB' 
										");
		
		if(is_array($tagsQuery)
		&& count($tagsQuery) > 0
		) {			
			$tags = explode(",", $tagsQuery[0]['tags']);
		}
		
		$i = 1;
		foreach($tags as $tag) {
			$output[$i]	= $tag;
			$i++;
		}

		echo (json_encode(array("tags" => $tagsQuery[0]['tags'])));
		
		exit;
	
	}



	/**
	 * Galerien reparieren Datenbank bereinigen
	 * 
	 * @param	string		$gallName	Galeriename
	 * @access	public
	 */	 
	public function getAllGalleryTags()
	{
	
		$tags	= array();
		$output	= array();
		
		
		// Gall-ID bestimmen
		$tagsQuery	= $this->DB->query("SELECT `tags` 
											FROM `$this->tableGall` 
										WHERE `tags` != ''
										");
		
		if(is_array($tagsQuery)
		&& count($tagsQuery) > 0
		) {			
			foreach($tagsQuery as $gall) {
				$tags = array_merge($tags, explode(",", $gall['tags']));
			}
		}
		
		$tags = array_unique($tags);
		
		$i = 1;
		foreach($tags as $tag) {
			$output[$i]	= $tag;
			$i++;
		}

		echo (json_encode($output));
		
		exit;
	
	}

} // end class EditGalleries
