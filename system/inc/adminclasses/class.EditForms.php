<?php
namespace Concise;


###################################################
#################  EditForms  #####################
###################################################

// EditForms

class EditForms extends Admin
{

	private $action			= "";
	private $tableForms		= "forms";
	private $tableFormsDefs	= "forms_definitions";

	public function __construct($DB, $o_lng)
	{
	
		$this->DB			= $DB;
		$this->o_lng		= $o_lng;

		$this->getLanguageParams();
		
		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();
		
		if(!empty($GLOBALS['_GET']['action']))
			$this->action	= $GLOBALS['_GET']['action'];
		
		$this->tableForms		= DB_TABLE_PREFIX . $this->tableForms;
		$this->tableFormsDefs	= DB_TABLE_PREFIX . $this->tableFormsDefs;
	
	}
	
	public function conductAction()
	{
	
		// Falls ein Formular gelöscht werden soll
		if($this->action == "delform")
			return $this->deleteForm();
		
		// Formulare löschen (Mehrfachlöschung)
		if($this->action == "delforms")
			return $this->deleteForms();		

		// Formular kopieren
		if($this->action == "copyform")
			return $this->copyForm();
		
		// Formulardaten löschen (Mehrfachlöschung)
		if($this->action == "delformdata")
			return $this->deleteFormData();

		// Falls ein Formularfeld hinzugefügt werden soll
		if($this->action == "newfield")
			return $this->addFormField();

		// Falls Formularfelder neu sortiert werden sollen
		if($this->action == "sortfield" && isset($GLOBALS['_GET']['item']))
			return $this->sortFormFields();		
			
		// Falls ein Formularfeld gelöscht werden soll
		if($this->action == "delfield")
			return $this->deleteFormField();
		
	}
	
	

	// Falls Formularfelder neu sortiert werden sollen
	private function sortFormFields()
	{
		
		if(is_numeric($GLOBALS['_GET']['item']))
			$itemId = $this->DB->escapeString($GLOBALS['_GET']['item']);
				
		if(isset($GLOBALS['_GET']['sortIdOld']) && $GLOBALS['_GET']['sortIdOld'] != "" && is_numeric($GLOBALS['_GET']['sortIdOld']))
			$sortIdOld = $GLOBALS['_GET']['sortIdOld'];
				
		if(isset($GLOBALS['_GET']['sortIdNew']) && $GLOBALS['_GET']['sortIdNew'] != "" && is_numeric($GLOBALS['_GET']['sortIdNew']))
			$sortIdNew = $GLOBALS['_GET']['sortIdNew'];

		$sortIdOldDB	= $this->DB->escapeString($sortIdOld);
		$sortIdNewDB	= $this->DB->escapeString($sortIdNew);
		$formID			= $this->DB->escapeString($GLOBALS['_GET']['formid']);
		
		
		// Einträge in DB aktualisieren
		$lock = $this->DB->query("LOCK TABLES `$this->tableFormsDefs`");

		if($sortIdNew > 1) {
			
			if($sortIdOld > $sortIdNew)
				$idPrevCol = (int)($sortIdNewDB-1);
			else
				$idPrevCol = (int)($sortIdNewDB);
			
			
			// Spaltenname der neuen Position-1
			$queryPrevCol = $this->DB->query("SELECT n.`field_name`, p.`table` 
												FROM `$this->tableFormsDefs` as n 
													LEFT JOIN `$this->tableForms` as p 
													ON n.`table_id` = p.`id` 
												WHERE n.`table_id` = $formID 
													AND n.`sort_id` = $idPrevCol
											 ");
			
			$formTable	= $queryPrevCol[0]['table'];
			$prevCol	= $queryPrevCol[0]['field_name'];
		}
		else {
			// Spaltenname der neuen Position-1
			$queryPrevCol = $this->DB->query("SELECT p.`table`, p.`foreign_key` 
												FROM `$this->tableFormsDefs` as n 
													LEFT JOIN `$this->tableForms` as p 
													ON n.`table_id` = p.`id` 
												WHERE n.`table_id` = $formID 
											 ");
			
			$formTable = $queryPrevCol[0]['table'];
			
			if($queryPrevCol[0]['foreign_key'] == "username" )
				$prevCol = "username";
			else
				$prevCol = "id";
		}
			
		// Spaltenname der neuen Position-1
		$queryfieldName = $this->DB->query("SELECT `field_name` 
												FROM `$this->tableFormsDefs` 
											WHERE `table_id` = $formID 
												AND `id` = $itemId
											");
			
		$fieldName		= $this->DB->escapeString($queryfieldName[0]['field_name']);
		
		
		// Feld-Definitionen holen
		$fieldDefs = $this->DB->query("DESCRIBE `" . DB_TABLE_PREFIX . "form_".$formTable."` `".$fieldName."`
												");
		
		
		$type		= $fieldDefs[0]["Type"];
		$null		= $fieldDefs[0]["Null"] == "NO" ? " NOT NULL" : " NULL";
		$default	= $fieldDefs[0]["Default"] != "" ? " DEFAULT ".$fieldDefs[0]["Default"] : '';



		if($sortIdOld < $sortIdNew)	
			// Neusortierung
			$updateSQL1 = $this->DB->query("UPDATE `$this->tableFormsDefs` 
												SET `sort_id` = `sort_id`-1
											WHERE `table_id` = $formID 
												AND `sort_id` > $sortIdOldDB 
												AND `sort_id` <= $sortIdNewDB
											");
		
		if($sortIdOld > $sortIdNew)	
			// Neusortierung
			$updateSQL1 = $this->DB->query("UPDATE `$this->tableFormsDefs` 
												SET `sort_id` = `sort_id`+1
											WHERE `table_id` = $formID 
												AND `sort_id` < $sortIdOldDB 
												AND `sort_id` >= $sortIdNewDB
											");
		
		// Neusortierung
		$updateSQL2 = $this->DB->query("UPDATE `$this->tableFormsDefs` 
											SET `sort_id` = $sortIdNewDB
										WHERE `table_id` = $formID 
											AND `id` = $itemId
										");

		// Variable für Neusortierung setzen
		$updateSQL3 = $this->DB->query("SET @c:=0;
											");
		
		
		// Neusortierung
		$updateSQL4 = $this->DB->query("UPDATE `$this->tableFormsDefs` 
											SET `sort_id` = (SELECT @c:=@c+1)
										WHERE `table_id` = $formID 
											ORDER BY `sort_id` ASC;
										");

		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "form_".$this->DB->escapeString($formTable)."`");

		// Neusortierung
		$updateSQL3 = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$this->DB->escapeString($formTable)."` 
											MODIFY `".$fieldName."` ".$type.$null.$default." AFTER `".$prevCol."`
										");


		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		echo $updateSQL1.$updateSQL2.$updateSQL3;
	
	}


	// Formular löschen
	private function deleteForm()
	{
		
		$formID		= $this->DB->escapeString($GLOBALS['_GET']['formid']);
		$formTable	= $this->DB->escapeString($GLOBALS['_GET']['tablename']);
		
	
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `$this->tableForms`, `$this->tableFormsDefs`");
		
		// Löschen des Formulars
		$deleteSQL1 = $this->DB->query("DELETE 
											FROM `$this->tableForms` 
											WHERE `id` = $formID
											");
		
		// Löschen der Formulardefinitionen
		$deleteSQL2 = $this->DB->query("DELETE 
											FROM `$this->tableFormsDefs` 
											WHERE `table_id` = $formID
											");
		
		// Löschen der Formulardatentabelle
		$deleteSQL3 = $this->DB->query("DROP TABLE `" . DB_TABLE_PREFIX . "form_".$formTable."` 
											");
		
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");


		$this->setSessionVar('notice', "{s_notice:delform}");
		
		
		// Falls sich von vorher noch eine id zum Bearbeiten von News in der Session befindet, diese löschen
		$this->unsetSessionKey('form_id');
		
		
		header("Location: " . ADMIN_HTTP_ROOT . "?task=forms");
		exit;
		/*			
		// Falls im Adminbereich gelöscht wurde
		require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
		require_once SYSTEM_DOC_ROOT . "/inc/admintasks/forms/admin_forms.inc.php"; // Admin-Task einbinden
		$adminTask		= 'forms';
		$adminE			= new Admin_Forms($this->DB, $this->o_lng, $adminTask);
		// Theme-Setup
		$adminE->getThemeDefaults("admin");
		$ajaxOutput		= $adminE->getTaskContents(true);
		echo parent::replaceStaText($ajaxOutput);					
		
		return true;
		*/
	}
	
	
	// deleteForms (Mehrfachlöschung) 
	public function deleteForms()
	{
	
		$deleteSQL1	= false;
		$deleteSQL2	= false;
		$notice		= "{s_notice:delform}";
		$error		= "{s_error:error}";
		$queryExt1	= "";
		$queryExt2	= "";
		$queryExt3	= "";
		
		if(isset($GLOBALS['_GET']['items'])) {
			
			$id		= $this->DB->escapeString($GLOBALS['_GET']['items']);			
			
			// Falls id=array, mehrere IDs aus Post auslesen
			if($id == "array"
			&& isset($GLOBALS['_POST']['formIDs'])
			&& is_array($GLOBALS['_POST']['formIDs'])
			&& count($GLOBALS['_POST']['formIDs']) > 0
			) {
				
				// Cats durchlaufen
				foreach($GLOBALS['_POST']['formIDs'] as $key => $IDvalue) {
					
					$formDetails	= explode("<>", $IDvalue);
					$formID			= $this->DB->escapeString($formDetails[0]);
					$formTable		= $this->DB->escapeString($formDetails[1]);
					$queryExt1	   .= " OR `id` = ".$formID;
					$queryExt2	   .= " OR `table_id` = ".$formID;
					$queryExt3	   .= "`" . DB_TABLE_PREFIX . "form_".$formTable."`,";	
				}
				$queryExt3	= substr($queryExt3, 0, -1);
				
	
				// Einträge in DB löschen
				$lock = $this->DB->query("LOCK TABLES `$this->tableForms`, `$this->tableFormsDefs`");
				
				// Löschen des Formulars
				$deleteSQL1 = $this->DB->query("DELETE 
													FROM `$this->tableForms` 
													WHERE `id` = ''
													$queryExt1
													");
				
				// Löschen der Formulardefinitionen
				$deleteSQL2 = $this->DB->query("DELETE 
													FROM `$this->tableFormsDefs` 
													WHERE `table_id` = ''
													$queryExt2
													");
				
				// Löschen der Formulardatentabelle
				$deleteSQL3 = $this->DB->query("DROP TABLE $queryExt3");
				
				
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
			
			}				
		}
		
		if($deleteSQL1 === true 
		&& $deleteSQL2 === true
		&& $deleteSQL3 === true
		)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('error', $error);
		
		$this->setSessionVar('list_forms', true);

		#echo($deleteSQL1.$deleteSQL2.$deleteSQL3);
		header("location: " . ADMIN_HTTP_ROOT . "?task=forms");
		exit;

	}

	
	// Formularfeld(er) löschen
	private function deleteFormField()
	{
	
		$formID		= $GLOBALS['_GET']['formid'];
		$formIDdb	= (int)$formID;
		$fieldID	= $GLOBALS['_GET']['fieldid'];
		$fieldIDdb	= (int)$fieldID;
		$tableDb	= $this->DB->escapeString(DB_TABLE_PREFIX . 'form_' . $GLOBALS['_GET']['tablename']);
		$fieldName	= $GLOBALS['_GET']['fieldname'];
		$queryExt1	= "";
		$queryExt2	= "";
		$deleteSQL2 = true;
		$fieldCnt	= 0;
		
	
		// Falls formid=array, mehrere Felder aus Post auslesen
		if( $fieldID == "array" && 
			isset($GLOBALS['_POST']['entryNr']) && 
			count($GLOBALS['_POST']['entryNr']) > 0
		) {
			
			foreach($GLOBALS['_POST']['entryNr'] as $key => $IDvalue) {
				
				$fID		= (int)$GLOBALS['_POST']['entryID'][$key];
				$queryExt1 .= " OR `id` = " . $fID;
				if(!empty($GLOBALS['_POST']['entryName'][$key]))
					$queryExt2 .= "`" . $this->DB->escapeString($GLOBALS['_POST']['entryName'][$key]) . "`,";
				$fieldCnt++;
			}
			$queryExt2 = $queryExt2 != "" ? substr($queryExt2, 0, -1) : "";
		
		}
		// Andernfalls ein Datensatz
		elseif(is_numeric($fieldID)) {
			$queryExt1	= " OR `id` = ".$fieldIDdb;
			
			if(!empty($fieldName)) {
				$fieldName	= $this->DB->escapeString($GLOBALS['_GET']['fieldname']);
				$queryExt2 	= "`" . $fieldName . "`";
			}
		}

		// Überprüfen ob Formulardatentabelle vorhanden
		$fdTabExists	= $this->DB->tableExists($tableDb);
		

		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `$this->tableFormsDefs`" . ($fdTabExists ? ", `".$tableDb."`" : ""));
		
		// Löschen der Formulardefinitionen
		$deleteSQL1 = $this->DB->query("DELETE 
											FROM `$this->tableFormsDefs` 
										WHERE `table_id` = $formIDdb 
											AND (`id` = ''
											$queryExt1)
										");
		
		// Aktualisierung der SortID
		// Variable für Neusortierung setzen
		$updateSQL2a = $this->DB->query("SET @c:=0;
											");
		
		
		// Neusortierung
		$updateSQL2b = $this->DB->query("UPDATE `$this->tableFormsDefs` 
											SET `sort_id` = (SELECT @c:=@c+1)
										 WHERE `table_id` = $formIDdb 
											ORDER BY `sort_id` ASC;
										");


		if($fdTabExists && !empty($queryExt2)) {
		
			// Löschen der Formulardatentabellenspalte
			$deleteSQL2 = $this->DB->query("ALTER TABLE `".$tableDb."` 
												DROP ".$queryExt2."
											");
		}
		
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		$notice	= "";
		$error	= "";
		
		if( $deleteSQL1 === true && 
			$updateSQL2a === true && 
			$updateSQL2b === true && 
			$deleteSQL2 === true
		) {
			$notice		= $fieldID == "array" ? $fieldCnt . " {s_notice:delfields}" : "{s_notice:delfield}";
			#$this->setSessionVar('notice', $notice);
		}
		else {
			$error		= "{s_error:error}";
			#$this->setSessionVar('error', $error);
		}
		
		#$this->setSessionVar('list_fields', $formID);

		#header("Location: " . ADMIN_HTTP_ROOT . "?task=forms");
		#exit;
		##/*
		
		if(isset($GLOBALS['_POST']['edit_fields']))
			unset($GLOBALS['_POST']['edit_fields']);
		
		// Falls im Adminbereich gelöscht wurde
		require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
		require_once SYSTEM_DOC_ROOT . "/inc/admintasks/forms/admin_forms.inc.php"; // Admin-Task einbinden
		$adminTask				= 'forms';
		$adminE					= new Admin_Forms($this->DB, $this->o_lng, $adminTask);
		// Theme-Setup
		$adminE->getThemeDefaults("admin");
		$adminE->notice			= $notice;
		$adminE->error			= $error;
		$adminE->formID			= $formID;
		$adminE->listFields		= true;
		$adminE->evalFieldPost	= false;
		$ajaxOutput				= $adminE->getTaskContents(true);
		echo parent::replaceStaText($ajaxOutput);					
		exit;
		
		return true;
		
	}

	
	// Formularfeld hinzufügen
	private function addFormField()
	{

		// Falls im Adminbereich erstellt wurde
		require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
		require_once SYSTEM_DOC_ROOT . "/inc/admintasks/forms/admin_forms.inc.php"; // Admin-Task einbinden
		
		$adminTask		= 'forms';
		$newField		= $GLOBALS['_GET']['fieldtype'];
		$sortID			= $GLOBALS['_GET']['fieldpos'];
		$formID			= $GLOBALS['_GET']['formid'];
		$adminE			= new Admin_Forms($this->DB, $this->o_lng, $adminTask);
		
		// Theme-Setup
		$adminE->getThemeDefaults("admin");
		$adminE->insertFormField($newField, $formID, $sortID);
		
		$ajaxOutput		= $adminE->getTaskContents(true);
		echo parent::replaceStaText($ajaxOutput);
		
		return true;
	}


	// Formular kopieren
	private function copyForm()
	{
		
		$copySQL1a	= false;
		$copySQL1b	= false;
		$copySQL1c	= false;
		$copySQL1d	= false;
		$copySQL1e	= false;
		$copySQL2a	= false;
		$copySQL2b	= false;
		$copySQL2c	= false;
		$copySQL2d	= false;	
		$copySQL2e	= false;
		$copySQL3a	= false;
		$copySQL3b	= false;
		$notice		= "{s_notice:copyform}";
		$report		= "";
		$queryExt	= "";
		
		if(isset($GLOBALS['_GET']['newname']) && $GLOBALS['_GET']['newname'] != "") {

			//Files-Klasse einbinden
			require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";
			
			$newFormName	= $GLOBALS['_GET']['newname'];
			$newFormName	= Files::getValidFileName(trim($newFormName));
			$newFormTitle	= $newFormName;
			$newFormName	= parent::sanitizeTableName($newFormName);
			$newFormName	= strtolower($newFormName);
			$newFormNameDb	= $this->DB->escapeString($newFormName);
			$newFormTitleDb	= $this->DB->escapeString($newFormTitle);
		
			// Überprüfen ob Formularname bereits vorhanden
			// db-Query nach Formularen
			$existForms = $this->DB->query("SELECT `id` 
												FROM `$this->tableForms` 
												WHERE `table` = '$newFormNameDb'
											");
			#var_dump($existForms);
				
			// Falls Formularname noch nicht vorhanden
			if(count($existForms) == 0) {
					
				if(isset($GLOBALS['_GET']['editname']) && $GLOBALS['_GET']['editname'] != "")
					$formID = $GLOBALS['_GET']['editname'];
					$formIDdb = $this->DB->escapeString($formID);
			
				
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `$this->tableForms`, `$this->tableFormsDefs`");
						
				
				// Tabelle aus forms kopieren
				$copySQL1a = $this->DB->query("CREATE TEMPORARY TABLE _temp
													SELECT * 
													FROM `$this->tableForms` 
													WHERE id = $formIDdb;
												 ");
				
				$copySQL1b = $this->DB->query("ALTER TABLE _temp 
													CHANGE id id INT;
												 ");
				
				$copySQL1c = $this->DB->query("UPDATE _temp 
													SET id = NULL,
													`table` = '$newFormNameDb',
													`title_" . $this->editLang . "` = '$newFormTitleDb',
													`timestamp` = NULL
													;
												 ");
				
				$copySQL1d = $this->DB->query("INSERT INTO `$this->tableForms` 
													SELECT * 
													FROM _temp;
													");
								
				$copySQL1e = $this->DB->query("DROP TEMPORARY TABLE _temp
												  ");
								
					
				
				// Tabelle aus forms_definitions kopieren
				$copySQL2a = $this->DB->query("CREATE TEMPORARY TABLE _temp
													SELECT * 
													FROM `$this->tableFormsDefs` 
													WHERE `table_id` = $formIDdb;
												 ");
				
				$copySQL2b = $this->DB->query("ALTER TABLE _temp 
													CHANGE id id INT;
												 ");
				
				$copySQL2c = $this->DB->query("UPDATE _temp 
													SET `id` = NULL,
													`table_id` = (SELECT `id` 
																		FROM `$this->tableForms` 
																		WHERE `table` = '$newFormNameDb')
													;
												 ");
				
				$copySQL2d = $this->DB->query("INSERT INTO `$this->tableFormsDefs` 
													SELECT * 
													FROM _temp;
													");
								
				$copySQL2e = $this->DB->query("DROP TEMPORARY TABLE _temp
												  ");
								
					
					
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
				
				// db-Query nach Formularen
				$formTable = $this->DB->query("SELECT `table` 
														FROM `$this->tableForms` 
														WHERE `id` = '$formIDdb'
													", false);
				
					
				// Tabelle für Formulardaten kopieren
				$copySQL3a = $this->DB->query("CREATE TABLE `" . DB_TABLE_PREFIX . "form_" . $newFormNameDb . "` 
													ENGINE = InnoDB 
													CHARACTER SET utf8 
													COLLATE utf8_general_ci 
													SELECT * 
													FROM `" . DB_TABLE_PREFIX . "form_" . $formTable[0]['table'] . "`
													LIMIT 1;
												   ");
				
				
				// Tabelle für Formulardaten leeren
				$copySQL3b = $this->DB->query("TRUNCATE TABLE `" . DB_TABLE_PREFIX . "form_" . $newFormNameDb . "`");
				
				
				$copySQL3c = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_" . $newFormNameDb . "` 
													ADD PRIMARY KEY (`id`),
													CHANGE id id INT NOT NULL AUTO_INCREMENT;
												   ");
				
				
				// SQL-Ergebnisse
				$report = (string)$copySQL1a.$copySQL1b.$copySQL1c.$copySQL1d.$copySQL1e.$copySQL2a.$copySQL2b.$copySQL2c.$copySQL2d.$copySQL2e.$copySQL3a.$copySQL3b.$copySQL3c;
			}
			
			// Andernfalls existiert der Name bereits
			else {
				$report = "-1";
			}
			
		}
		
		if($report == "1111111111111") {
			$this->setSessionVar('notice', $notice);
			#header("location: " . ADMIN_HTTP_ROOT . "?task=forms");
			#exit;
		}
		elseif($report != "-1") {
			$this->setSessionVar('error', "{s_error:error}");
		}

		echo(json_encode(array(	"result"	=> "$report")));
		exit;
	
	}


	// Formulardaten löschen (Mehrfachlöschung)
	private function deleteFormData()
	{
		
		$deleteSQL1	= false;
		$deleteSQL2	= false;
		$notice		= "{s_notice:deldata}";
		$error		= "{s_error:error}";
		$queryExt	= "";
		
		if(isset($GLOBALS['_GET']['entryid'])) {
			
			$id		= $this->DB->escapeString($GLOBALS['_GET']['entryid']);
			
			if(isset($GLOBALS['_GET']['formtable']))
				$formTable	= $this->DB->escapeString($GLOBALS['_GET']['formtable']);
				
			// Falls id=array, mehrere IPs aus Post auslesen
			if($id == "array" && isset($GLOBALS['_POST']['entryNr'])) {
				
				// db-Query nach Datei-Feldern
				$queryFileFields = $this->DB->query( "SELECT p.`field_name`, p.`filefolder`, p.`filerename` 
															FROM `$this->tableForms` AS n 
															LEFT JOIN `$this->tableFormsDefs` AS p 
																ON n.`id` = p.`table_id` 
															WHERE n.`table` = '" . $formTable . "' 
																AND p.`type` = 'file'
														  ", false);
						
				#var_dump($queryFileFields);
				
				foreach($GLOBALS['_POST']['entryNr'] as $key => $IDvalue) {
					
					$dataID = $this->DB->escapeString($GLOBALS['_POST']['entryID'][$key]);
					$queryExt .= " OR `id` = ".$dataID;
			
					// Ggf. Dateien löschen, die mit dem Formulareintrag verknüpft sind
					if(count($queryFileFields) > 0) {
						
						foreach($queryFileFields as $fileField) {
							
							$fieldName	= $fileField['field_name'];
							$fileRename	= $fileField['filerename'];
							$folder 	= $fileField['filefolder'];
							
							if($fileRename == 1)
								$folder = PROJECT_DOC_ROOT . '/_user/' . $folder;
							else
								$folder = PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER . '/' . $folder;
							
							// Dateinamen auslesen
							$fileData = $this->DB->query("SELECT `" . $this->DB->escapeString($fieldName) . "` FROM `" . DB_TABLE_PREFIX . "form_" . $formTable . "` 
																 WHERE `id` = $dataID
																");
								
							#var_dump($fileData);
							
							if(count($fileData) == 1) {
								
								$file	= $fileData[0][$fieldName];
							
								if(file_exists($folder . '/' . $file))
									unlink($folder . '/' . $file);							   
							}
							
						}
					}
					
				} // Ende foreach
				
			}
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "form_" . $formTable . "`");
					
			
			// log-Eintrag löschen
			$deleteSQL = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "form_" . $formTable . "` 
												 WHERE `id` = '' 
												 $queryExt
												");
							
				
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
			
		}
		
		if($deleteSQL === true)
			$this->setSessionVar('notice', $notice);
		else
			$this->setSessionVar('error', $error);
		
		// Sortierungsdaten aus dem Query String auslesen
		$queryStrExt = 'list_formdata='.$GLOBALS['_GET']['list_formdata'].'&sort_fieldname='.$GLOBALS['_GET']['sort_fieldname'].'&sort_dir='.$GLOBALS['_GET']['sort_dir'].'&limit='.$GLOBALS['_GET']['limit'];
		
		if(isset($GLOBALS['_GET']['red']) && $GLOBALS['_GET']['red'] != "")
			$redirect = urldecode($GLOBALS['_GET']['red']);
		else
			$redirect = "admin?task=forms&" . $queryStrExt;
		
		header("location: " . PROJECT_HTTP_ROOT . "/" . $redirect);
		exit;
	}

} // end class EditForms
