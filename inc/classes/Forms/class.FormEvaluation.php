<?php
namespace Concise;



/**
 * Klasse FormEvaluation
 *
 * Listet Formulardaten aus einer Tabelle der Datenbank auf
 * 
 * 
 */

class FormEvaluation extends Modules
{

	//Ausgabedaten
	private $output;
	//Tabellenname
	private $formTable;
	//Tabellenname
	private $formID;
	//Tabellentitel
	private $formTitle;
	//Tabellentitel
	private $excludedFields = array();
	//Tabellentitel
	private $fileAsName = false;
	//Benutzergruppen mit Editierberechtigung
	private $editingGroups;
	//Bearbeitungsrechte
	private $editAccess = false;
	//Zu bearbeitender Eintrag
	private $editID;
	//Poll
	public $poll = false;
	// Limit options
	public $limitOptions = array(10, 25, 50, 100, 250, 500);

	/**
	 * Konstruktor der Klasse
	 * 
	 * @param varchar	tablename 		Name der Tabelle für das Formular
	 * @param varchar	formTitle 		Titel der Tabelle für das Formular (default = '')
	 */
	public function __construct($formTable, $formTitle = "", $excludedFields = array(), $fileAsName = false, $editingGroups = array())
	{

		$this->formTable		= $formTable;
		$this->formTitle		= $formTitle;
		$this->excludedFields	= $excludedFields;
		$this->fileAsName		= $fileAsName;
		$this->editingGroups	= $editingGroups;
		
		// Security-Objekt
		$this->o_security		= Security::getInstance();

		$this->group			= $this->o_security->get('group');
		
		// Editing-Access
		if(!empty($this->group) && (in_array($this->group, $this->editingGroups) || in_array("public", $this->editingGroups)))
			$this->editAccess	= true;
		
	}



	/**
	 * Methode zum Ausgeben von Formulardaten
	 * 
	 * @param varchar	tablename 		Name der Tabelle für das Formular
	 * @param varchar	formTitle 		Titel der Tabelle für das Formular (default = '')
	 */
	public function getFormData()
	{

		$result	= true;
		
		// Falls ein Formulareintrag bearbeitet werden soll und die Berechtigung zum Editieren vorliegt
 		if(!empty($GLOBALS['_GET']['edit_eid']) && $this->editAccess) {
			
			$this->editID	= $GLOBALS['_GET']['edit_eid'];
			
			$result	= $this->editFormData();
			
		}
		// Andernfalls, Formulardaten auflisten
		if($result)
			$this->listFormData();
			
		return $this->output;
		
	}



	/**
	 * Methode zum Bearbeiten von Formulardaten
	 * 
	 * @param varchar	tablename 		Name der Tabelle für das Formular
	 * @param varchar	formTitle 		Titel der Tabelle für das Formular (default = '')
	 */
	public function editFormData()
	{
		
 		if(empty($this->editID)
		|| !$this->editAccess
		)
			return false;
		
		
		// Events listeners registrieren
		$this->addEventListeners("form");
			

		// ID Formulareintrag
		$editIDdb		= $this->DB->escapeString($this->editID);
		$formTableDB	= $this->DB->escapeString($this->formTable);
			
		// db-Query nach News
		$queryFormID = $this->DB->query( "SELECT `id` 
													FROM `" . DB_TABLE_PREFIX . "forms` 
													WHERE `table` = '" . $formTableDB . "'
												  ", false);
		#var_dump($queryFormID);
		
		$this->formID	= $queryFormID[0]['id'];
		$formIDdb		= $this->DB->escapeString($this->formID);
		
		
		// db-Query nach Daten (Benutzereingaben) für das ausgewählte Formular
		$queryFormData = $this->DB->query( "SELECT * 
												FROM `" . DB_TABLE_PREFIX . "form_" . $formTableDB . "` 
												WHERE `id` = $editIDdb
											", false);
		#var_dump($queryFormData);
		
		if(empty($queryFormData)
		|| !is_array($queryFormData)
		) {
			$this->output = "no data found.";
			return false;
		}
		
			
		// FormGenerator-Klasse einbinden
		require_once PROJECT_DOC_ROOT."/inc/classes/Forms/class.FormGenerator.php";

		
		// db-Query nach Feldern des zu bearbeitenden Formulars
		$queryFormFields = $this->DB->query( "SELECT * 
												FROM `" . DB_TABLE_PREFIX . "forms` AS n 
												LEFT JOIN `" . DB_TABLE_PREFIX . "forms_definitions` AS p 
													ON n.`id` = p.`table_id` 
												WHERE n.`table` = '$formTableDB' 
												ORDER BY p.`sort_id` ASC
											  ", false);
						
		#var_dump($queryFormFields);
		
		
		$formTable					= 'form_' . $this->formTable; // Formulartabelle
		$formEditLang				= $this->adminPage ? $this->editLang : $this->lang;
		
		// Formular-Objekt
		$o_editForm	= new FormGenerator(array(), $formTable);
		
		$o_editForm->DB				= $this->DB;
		$o_editForm->lang			= $formEditLang;
		$o_editForm->adminPage		= $this->adminPage;
	
		// EventDispatcher
		$o_editForm->o_dispatcher	= $this->o_dispatcher;

		// Form parameter
		$o_editForm->formTitle		= $this->formTitle;
		
		
		// Falls Formular im Backend angelegt, Configarray erstellen
		if(count($queryFormFields) > 0) {
			
			// Methode zum Erstellen eines Arrays mit Konfigurationsdaten für das Formular
			$configArray = $o_editForm->makeFormConfigArray($queryFormFields, $formEditLang);
		}
		
		// Falls das Formular zum Bearbeiten noch nicht abgeschickt wurde
		if(!isset($GLOBALS['_POST'])
		||  count($GLOBALS['_POST']) == 0
		) {

			// Dem Konfigurations-Array Werte des zu bearbeitenden Datensatzes für die Vorbelegung der Felder mitgeben
			foreach($queryFormData[0] as $field => $data) {
				if(isset($configArray[$field]['type'])) {
					$this->getRequiredHeadFiles($configArray[$field]['type']);
					$configArray[$field]["value"] = $this->getFormattedField($configArray[$field]['type'], $data, $formEditLang);
				}
				else
					$configArray[$field]["value"] = $data;
			}
		}
		
		$configArray["cf_editformdata"]	= true; // Signal mitgeben, das Formulardaten bearbeitet werden sollen
		$configArray["cf_editid"]		= $this->editID; // Querystring mitgeben
		$configArray["cf_querystring"]	= "edit_eid=" . $this->editID; // Querystring mitgeben
		$configArray["cf_captcha"]		= false; // Captcha unterdrücken

		if($this->adminPage)
			$configArray["cf_querystring"]	= 'task=forms&list_formdata=' . $this->formID . '&' . $configArray["cf_querystring"];
		

		$this->output = $o_editForm->printForm($configArray); // Formular generieren
		
		return $o_editForm->getFormSubmissionStatus() && $o_editForm->getFormSubmissionResult();
			
	}
	
	
	
	/**
	 * Methode zum Auflisten von Formulardaten
	 * 
	 */
	public function listFormData()
	{
		
		$formAction		= ADMIN_HTTP_ROOT . "?task=forms";
		$formControls	= "";
		$formHeader		= "";
		$formEntry		= "";
		$formTpl		= "mod_tpls/formdata.tpl";
		$loopTplRow		= "mod_tpls/formdata_loop_row.tpl";
		$tplPath		= "";
		$pageNav		= "";
		$pollResults	= "";
	
		// Template Path festlegen
		if($this->adminPage)
			$tplPath	= 'system/themes/' . ADMIN_THEME . '/templates/';
		
		$tpl_row		= new Template($loopTplRow, array(), $tplPath);
		$tpl_row->loadTemplate();
		
		// Meldung
		if(isset($GLOBALS['_GET']['form'])) {
			
			if($GLOBALS['_GET']['form'] == 1)
				$this->output = '<p class="notice {t_class:alert} {t_class:success}">{s_notice:formsuccess}</p>';
			else
				$this->output = '<p class="notice {t_class:alert} {t_class:error}">{s_error:formerror}</p>';
		}
		
		// Sortierung
		if(isset($GLOBALS['_POST']['sort_fieldname']))
			$sortField = $GLOBALS['_POST']['sort_fieldname'];
		elseif(isset($GLOBALS['_GET']['sort_fieldname']))
			$sortField = $GLOBALS['_GET']['sort_fieldname'];
		else
			$sortField = "id";
		
		if(isset($GLOBALS['_POST']['sort_dir']))
			$sortDirection = $GLOBALS['_POST']['sort_dir'];
		elseif(isset($GLOBALS['_GET']['sort_dir']))
			$sortDirection = $GLOBALS['_GET']['sort_dir'];
		else
			$sortDirection = "desc";
		
		$dbOrder = " ORDER BY `" . $this->DB->escapeString($sortField) . "` " . $this->DB->escapeString(strtoupper($sortDirection));
		
		
		// Falls ein Titel angegeben ist
		if(!empty($this->formTitle))
			$formHeader = 	'<h2 class="formTitle">' . $this->formTitle . '</h2>' . "\r\n";			
		
		
		// Falls kein Formular angegeben ist
		if(empty($this->formTable)) {
			
			$noDataNote	= "";
			
			if(!isset($GLOBALS['_POST']['list_formdata']) || $GLOBALS['_POST']['list_formdata'] == "")
				$noDataNote = 		'<p class="error">{s_text:nodatasel}</p>' . "\r\n";
			
			$this->output .=		$noDataNote;
			
			return $this->output;
			
		} // Ende else Auswahlliste
		
		
		// Falls ein Formular angegeben ist
		$formTableDB = $this->DB->escapeString($this->formTable);
		
		// db-Query nach Daten (Benutzereingaben) für das ausgewählte Formular
		$queryFormData = $this->DB->query( "SELECT * 
													FROM `" . DB_TABLE_PREFIX . "form_" . $formTableDB . "` 
													$dbOrder
												  ");
		
		#var_dump($queryFormData);
		
		$totalRows		= count($queryFormData);
		$startRow		= "";
		$pageNum		= 0;
		$limit			= 25;
		$queryString	= "";
		
		// Anzahl an anzuzeigenden Datensätzen
		$limit			= $this->getLimit($limit);
		
		
		if($totalRows > 0 || $this->poll) {
		
			// Pagination
			if(isset($GLOBALS['_GET']['pageNum']))
				$pageNum = $GLOBALS['_GET']['pageNum'];
			
		
			$startRow = $pageNum * $limit;
			$queryLimit = " LIMIT " . $startRow . "," . $limit;
			
			if($this->adminPage) {

		
				// db-Query nach Daten Formular-ID
				$formID			= $this->DB->query( "SELECT `id` 
														FROM `" . DB_TABLE_PREFIX . "forms` 
														WHERE `table` = '" . $formTableDB . "'
													", false);
				
				if(isset($formID[0]['id']))
					$queryString .= "task=forms&list_formdata=" . $formID[0]['id'] . "&";
			}
			
			$queryString .= "sort_fieldname=$sortField&sort_dir=".$sortDirection."&limit=$limit";
			

			// db-Query nach Daten Formular-ID
			$queryFormID = $this->DB->query( "SELECT `id`, `poll` 
														FROM `" . DB_TABLE_PREFIX . "forms` 
														WHERE `table` = '" . $formTableDB . "'
													  ", false);
			#var_dump($queryFormID);

			if(count($queryFormID) == 0)
			
				return $this->output .= '<p class="error">{s_label:form} &quot;' . ($this->formTitle != "" ? $this->formTitle : $this->formTable) . '&quot;: {s_text:nodata}</p>';
			

			$this->formID	= $queryFormID[0]['id'];
			$formIDdb		= $this->DB->escapeString($this->formID);
			$this->poll		= $queryFormID[0]['poll'];
			
			
			// Falls Poll-Auswertung (und nicht Adminbereich)
			if($this->poll && $this->adminPage !== true)
			
				return $this->getPollData($formTableDB, $formIDdb); // Pollauswertung zurückgeben
			
			
			
			// db-Query nach Formular-Daten
			$queryFormData = $this->DB->query( "SELECT * 
														FROM `" . DB_TABLE_PREFIX . "form_" . $formTableDB . "` 
														$dbOrder
														$queryLimit
													  ", false);
			#var_dump($queryFormData);
			
		
			// db-Query nach Feldern des zu bearbeitenden Formulars
			$queryFormFields = $this->DB->query( "SELECT p.*, n.`foreign_key` 
														FROM `" . DB_TABLE_PREFIX . "forms` AS n 
														LEFT JOIN `" . DB_TABLE_PREFIX . "forms_definitions` AS p 
														ON n.`id` = p.`table_id` 
														WHERE p.`table_id` = $formIDdb 
														ORDER BY p.`sort_id` ASC
													  ", false);
					
			#var_dump($queryFormFields);
			
			
			// Sortierungsoptionen Feldname, falls nicht Adminbereich
			if(!$this->adminPage) {
			
				$formControls .= 	'<div class="controlBox clearfix">' . "\r\n" .
									'<form class="{t_class:form} {t_class:forminl}" method="post" action="" data-history="false">' . "\r\n" .
									'<div class="sortBox">' . "\r\n" .
									'<div class="sortField {t_class:formrow}">' . "\r\n" .
									'<label class="{t_class:labelinl}">{s_label:sortby}</label>' . "\r\n" .
									'<select name="sort_fieldname" class="listFieldName {t_class:fieldinl} {t_class:select}" data-action="autosubmit">' . "\r\n";
			
				$formFieldOpt	= "";
				$f				= -1;
				$foreignKey		= false;
					
				if($queryFormFields[0]['foreign_key'] != "") {
					$foreignKey = true;
				}
				
				// Feldnamen auslesen und als Optionen für Sortierungsauswahl nehmen
				foreach($queryFormData[0] as $fieldName => $fieldVal) {
					
					// Falls die Inhalte des Feldes nicht ignoriert werden sollen
					if(!in_array($fieldName, $this->excludedFields)) {
				
						$fieldLabel = !empty($queryFormFields[$f]['label_' . $this->lang]) ? $queryFormFields[$f]['label_' . $this->lang] : $fieldName;
						
						// Beim ersten Datensatz Feldnamen als Überschriften einfügen
						$formFieldOpt .=	'<option' . ($sortField == $fieldName ? ' selected="selected"' : '') . ' value="' . ($f == -1 ? 'id' : ($f == 0 && $foreignKey == true ? $queryFormFields[$f]['foreign_key'] : $fieldName)) . '">' . ($f == -1 ? 'id' : ($f == 0 && $foreignKey == true ? $queryFormFields[1]['foreign_key'] : $fieldLabel)) . '</option>';
					}
					
					// Falls kein Fremdschlüsselwert eingefügt wurde, Zähler hochzählen
					if($f == 0 && $foreignKey == true)
						$foreignKey	= false;
					else
						$f++;
				}
					
				$formControls .= 	$formFieldOpt . '</select></div>' . "\r\n";
						
				// Sortierungsoptionen (asc/dsc)
				$formControls .= 	'<div class="sortDirection {t_class:formrow}">' . "\r\n" .
									'<label class="{t_class:labelinl}">{s_label:order}</label>' . "\r\n" .
									'<select name="sort_dir" class="listSort {t_class:fieldinl} {t_class:select}" data-action="autosubmit">' . "\r\n";
				
				$sortOptions = array("asc" => "{s_option:asc}",
									 "desc" => "{s_option:dsc}"
									 );
				
				foreach($sortOptions as $key => $value) { // Sortierungsoptionen auflisten
					
					$formControls .='<option value="' . $key . '"';
					
					if($key == $sortDirection)
						$formControls .=' selected="selected"';
						
					$formControls .= '>' . $value . '</option>' . "\r\n";
				
				}
									
				$formControls .= 	'</select></div></div>' . "\r\n";
				
				// Anzahl an Datensätzen begrenzen
				$formControls .= 	'<div class="limitBox {t_class:formrow}">' . "\r\n" .
									'<label class="{t_class:labelinl}">{s_label:limit}</label>' . "\r\n" .
									'<select name="limit" class="listLimit {t_class:fieldinl} {t_class:select}" data-action="autosubmit">' . "\r\n";
				
				
				// Ergebnisse pro Seite
				foreach($this->limitOptions as $value) {
					
					$formControls .='<option value="' . $value . '"';
					
					if($limit == $value)
						$formControls .=' selected="selected"';
						
					$formControls .= '>' . $value . '</option>' . "\r\n";
				
				}
									
				$formControls .= 	'</select></div>' . "\r\n";
				
				$formControls .= 	'</form></div>' . "\r\n";
				
				$formControls .= 	$this->getFormControlSriptTag();
			
			} // Ende if not adminPage
		
		} // Ende if $totalRows > 0
	
		

		if(count($queryFormData) > 0 && count($queryFormFields) > 0) {
			
			$loopTplFieldHeader	= "mod_tpls/formdata_loop_fieldheader.tpl";
			$tpl_data_header	= new Template($loopTplFieldHeader, array(), $tplPath);
			$tpl_data_header->loadTemplate();
			
			$loopTplField		= "mod_tpls/formdata_loop_field.tpl";
			$tpl_data			= new Template($loopTplField, array(), $tplPath);
			$tpl_data->loadTemplate();
			
			$loopRow			= "";
			$rowCount			= -1;
			$formTag			= "";
			$formTagClose		= "";
			$loopHeader			= "";
			$markAll			= "";
			$sortFilter			= "";
			$editRow			= "";
			$foreignKey			= false;
			$tokenField			= "";
			
			if($queryFormFields[0]['foreign_key'] != "") {
				$foreignKey = true;
			}
			
			
			if($this->adminPage)
				$redirect	=	ADMIN_HTTP_ROOT . '?task=forms&';
			else
				$redirect	=	parent::$currentURL . '?';
			
			
			for($rowCount = 0; $rowCount < count($queryFormData); $rowCount++) {
				
				$dataRow		= $rowCount;
				$loopData		= "";
				$fieldCount		= 0;
				$editButton		= "";
				$rowClass		= "";
				$markEach		= "";
				$editButton		= "";
				$delAllButton	= "";
				$newSortDir		= "asc";
				
				if($sortDirection == "asc")
					$newSortDir	= "desc";
				
				
				foreach($queryFormData[$dataRow] as $fieldName => $fieldVal) {
					
					$fieldData		= "";
					$fieldClass		= "";
					$sortIcon		= "";
					
					if($sortField == $fieldName) {
						$sortFilter	= 'sort_fieldname=' . $fieldName . '&sort_dir=' . $newSortDir;
						$sortIcon	= '<span class="cc-table-sort-dir">' . parent::getIcon('sort' . ($newSortDir == "asc" ? 'dsc' : 'asc')) . '</span>';
					}
					
					
					// Falls erste Reihe und Spalte und editieren erlaubt, Checkbox "alle markieren" einfügen
					if($fieldCount == 0 && $this->editAccess) {
						
						$fieldClass		= "mark";
						
						// Falls erste Reihe und Spalte und editieren erlaubt, Checkbox "alle markieren" einfügen
						if($rowCount == 0) {

							// Checkbox zur Mehrfachauswahl im Header einfügen, falls löschberechtigt
							$fieldData	=	'<label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" />' .
											'</label>';
							
							$delAllButton	=	'<span class="editButtons-panel">';
	
							// Button delete
							$btnDefs	= array(	"type"		=> "submit",
													"class"		=> "delAll delFormData button-icon-only",
													"value"		=> "",
													"title"		=> "{s_title:delmarked}",
													"attr"		=> 'data-action="delmultiple"',
													"icon"		=> "delete"
												);
							
							$delAllButton .=	parent::getButton($btnDefs);
							
							$delAllButton .=	'</span>';
						
							// Zuweisung der Inhalte für Templatesystem (Loop-Template)
							$loop_tpl_field		= clone $tpl_data_header;
							// Platzhalterersetzungen für Felder
							$loop_tpl_field->assign("fieldData", $fieldData);								
							$loop_tpl_field->assign("fieldClass", $fieldClass);
							$loopHeader .= $loop_tpl_field->getTemplate(); // Template ausgeben
						}
							
						// Checkbox zur Mehrfachauswahl vor Tabellenzeile einfügen
						$fieldData =	'<label class="markBox">' .
										'<input type="checkbox" name="entryNr[' . $dataRow . ']" class="addVal" />' .
										'<input type="hidden" name="entryID[' . $dataRow. ']" value="' . $fieldVal . '" class="getVal" /></label>';
										
						// Daten-ID auslesen
						$dataID = $fieldVal;
								
						// In jeder Datenreihe einen Edit-Button einfügen
						$editButton =	'<span class="editButtons-panel">';
	
						// Button edit
						$btnDefs	= array(	"href"		=> $redirect . 'list_formdata=' . $this->formID . '&edit_eid=' . $dataID,
												"class"		=> "editFormData button-icon-only",
												"value"		=> "",
												"title"		=> "{s_title:editdata}",
												"attr"		=> 'data-action="editformdata"',
												"icon"		=> "edit"
											);
						
						$editButton .=	parent::getButtonLink($btnDefs);
						
						$editButton .=	'</span>';
						
							
						// Zuweisung der Inhalte für Templatesystem (Loop-Template)
						$loop_tpl_field		= clone $tpl_data;
						// Platzhalterersetzungen für Felder
						$loop_tpl_field->assign("fieldData", $fieldData);								
						$loop_tpl_field->assign("fieldClass", $fieldClass);
						$loopData .= $loop_tpl_field->getTemplate(); // Template ausgeben
					
						$fieldClass		= "";
					}
					
					// Falls die Inhalte des Feldes nicht ignoriert werden sollen
					if(!in_array($fieldName, $this->excludedFields)) {
					
						// Falls erste Datenreihe, zunächst Feldnamen als Überschriften einfügen
						if($rowCount == 0) {
							
							if($fieldName == "")
								$fieldName = $queryFormFields[$fieldCount-1]['field_name'];
							
							$fieldLabel = !empty($queryFormFields[$fieldCount-1]['label_' . $this->lang]) ? $queryFormFields[$fieldCount-1]['label_' . $this->lang] : $fieldName;
							
							$fieldData	=	$fieldCount == 0 ? "id" : ($fieldCount == 1 && $foreignKey == true ? $queryFormFields[1]['foreign_key'] : $fieldLabel);
							
							$fieldData	=	'<a href="' . $redirect . 'list_formdata=' . $this->formID . '&sort_fieldname=' . $fieldName . '&sort_dir=' . $newSortDir . '" data-ajax="true" data-history="false">' .
											$fieldData .
											$sortIcon .
											'</a>';
							
							// Zuweisung der Inhalte für Templatesystem (Loop-Template)
							$loop_tpl_field		= clone $tpl_data_header;
							// Platzhalterersetzungen für Felder
							$loop_tpl_field->assign("fieldData", $fieldData);								
							$loopHeader .= $loop_tpl_field->getTemplate(); // Template ausgeben
						}
							
						
						// Wert des Feldes ausgeben
						// Falls Passwort
						if($fieldCount > 0 && $queryFormFields[$fieldCount-1]['type'] == "password")
							$fieldData = "&bull;&bull;&bull;&bull;&bull;&bull;";
							
						// Falls File nicht als Dateiname ausgegeben werden soll (z.B. Bild)
						elseif($fieldCount > 0 && $queryFormFields[$fieldCount-1]['type'] == "file" && !$this->fileAsName && $fieldVal != "")								
							$fieldData	= $this->getPresentFile($fieldVal, $queryFormFields[$fieldCount-1]['filefolder'], $queryFormFields[$fieldCount-1]['filerename']);
											
						else
							$fieldData = htmlspecialchars($fieldVal);
						
				
						// Zuweisung der Inhalte für Templatesystem (Loop-Template)
						$loop_tpl_field		= clone $tpl_data;
						// Platzhalterersetzungen für Felder
						$loop_tpl_field->assign("fieldData", $fieldData);
						$loop_tpl_field->assign("fieldClass", $fieldClass);
						$loopData .= $loop_tpl_field->getTemplate(); // Template ausgeben
					}						
							
					// Falls ein Foreign Key-Wert zuvor ausgelesen wurde, den RowCount um 1 zurücksetzen
					if($fieldCount == 1 && $foreignKey == true)
						$foreignKey = false;
					else {
						if($fieldCount == 0 && $queryFormFields[0]['foreign_key'] != "")
							$foreignKey = true;
						$fieldCount++;
					}				
					
				} // Ende foreach
				
				
				// Alternierende Reihe
				if($dataRow % 2)
					$rowClass = "alternate";
				
				
				// Als letzte Header-Spalte einen DeleteAll-Button einfügen
				if($rowCount == 0) {
					
					// Zuweisung der Inhalte für Templatesystem (Loop-Template)
					$loop_tpl_field		= clone $tpl_data_header;
					// Platzhalterersetzungen für Felder
					$loop_tpl_field->assign("fieldClass", "editButtons-cell");
					$loop_tpl_field->assign("fieldData", $delAllButton);								
					$loopHeader .= $loop_tpl_field->getTemplate(); // Template ausgeben
				}
				
				// Als letzte Spalte einen Edit-Button einfügen
				if($this->editAccess) {
					
					// Zuweisung der Inhalte für Templatesystem (Loop-Template)
					$loop_tpl_field		= clone $tpl_data;
					// Platzhalterersetzungen für Edit-Buttons
					$loop_tpl_field->assign("fieldClass", "editButtons-cell");
					$loop_tpl_field->assign("fieldData", $editButton);
					$loopData .= $loop_tpl_field->getTemplate(); // Template ausgeben
				}

				
				// Zuweisung der Inhalte für Templatesystem (Loop-Template)
				$loop_tpl_row		= clone $tpl_row;
				// Platzhalterersetzungen für Zeilen
				$loop_tpl_row->assign("loop_data", $loopData);
				$loop_tpl_row->assign("rowClass", $rowClass);
				$loopRow .= $loop_tpl_row->getTemplate(); // Template ausgeben
				

			} // Ende for
			
			// Falls editieren erlaubt, Formular einfügen
			if($this->editAccess) {
				
				if($this->adminPage)
					$redirect	=	urlencode('admin?task=forms&list_formdata=' . $this->formID . '&' . $sortFilter);
				else
					$redirect	=	urlencode(parent::$currentURLPath);
						
				$formTag		=	'<form action="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&action=delformdata&formtable=' . $this->formTable . '&list_formdata='.$this->formID.'&sort_fieldname='.$sortField.'&sort_dir='.$sortDirection.'&limit='.$limit.'&red='.$redirect.'&entryid=array" method="post" data-history="false">';
				$formTagClose	=	'</form>';
				#$tokenField		=	'<input type="hidden" name="token" value="' . parent::$token . '" />' . "\r\n"; // Token
			}
			
			// Hidden page nav fields
			$hiddenFields	= array ();
			$hiddenFields[]	= array(	"name"	=> "list_formdata",
										"val"	=> $this->formID
									);
			
			// Pagination Nav
			$pageNav = Modules::getPageNav($limit, $totalRows, $startRow, $pageNum, $queryString, "", false, Modules::getLimitForm($this->limitOptions, $limit, "", $hiddenFields));
			
			
			// Ggf. Poll results
			if($this->poll && $this->adminPage)
				$pollResults	= $this->getPollData($formTableDB, $formIDdb); // Pollauswertung
			
			
			// Ausgabe über Templatesystem
			$tpl_data		= new Template($formTpl, array(), $tplPath);
			$tpl_data->loadTemplate();
			
			// Platzhalterersetzungen
			$tpl_data->assign("formID", $this->formID);
			$tpl_data->assign("formControls", $formControls);
			$tpl_data->assign("formTag", $formTag);
			$tpl_data->assign("formHeader", $formHeader);
			$tpl_data->assign("loop_header", $loopHeader);
			$tpl_data->assign("loop_row", $loopRow);
			#$tpl_data->assign("token", $tokenField);
			$tpl_data->assign("formTagClose", $formTagClose);
			$tpl_data->assign("pageNav", $pageNav); // PageNav
			$tpl_data->assign("pollResults", $pollResults); // pollResults
			$tpl_data->assign("fieldClass", "");
			
			$this->output .= $tpl_data->getTemplate(); // Template ausgeben
			
		}
		
		return $this->output;
	
	}



	/**
	 * Hinzufügen von Dateiinformationen von bereits vorhandenen Dateien (im Edit-Modus)
	 * 
	 * @param varchar	presentFile	Name der vorhandenen Datei
	 * @param varchar	fileFolder	Name des Ordners der vorhandenen Datei
	 * @param boolean	fileRename	Info ob Datei benutzerspezifisch ist (wirkt sich auf Root-Ordner aus)
	 * @return String 	Ergebniseintrag
	 */
	public function getPresentFile($presentFile, $fileFolder, $fileRename)
	{	

		// Files-Klasse einbinden
		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";
		
		// Datei(root)ordner bestimmen (falls benutzerspezifisch = '_user')
		if($fileRename)
			$rootFolder = '/_user/';
		else
			$rootFolder = '/' . CC_FILES_FOLDER . '/';
		
		$filePath	= PROJECT_HTTP_ROOT . $rootFolder . $fileFolder . '/' . $presentFile;
		
		if(Files::isImageFile($presentFile))
			$fieldVal	= '<a href="' . $filePath .'" class="extLink" target="_blank"><img src="' . $filePath . '" alt="' . $presentFile . '" class="previewImage" /></a>';
		else
			$fieldVal	= '<a href="' . $filePath .'" class="extLink" target="_blank">' . $presentFile . '</a>';
		
		return $fieldVal;
		
	}



	/**
	 * Erzeugt eine Ausgabe von Poll-Daten
	 * 
	 * @param varchar	table	Name der DB-Tabelle
	 * @param varchar	formID	ID der DB-Tabelle
	 * @return String 	Poll-Daten
	 */
	public function getPollData($table, $formID)
	{
	
		$output	= "";
		
		// db-Query nach Formular-Daten
		$queryPollCol = $this->DB->query( "SHOW COLUMNS 
												FROM `" . DB_TABLE_PREFIX . "form_" . $table . "` 
										  ", false);
		#var_dump($queryPollCol);
	
		if(count($queryPollCol) < 1)
			return "no poll data";
		
		
		// Feld mit Polloptionen
		$pollField		= $queryPollCol[1]['Field'];
		$countStr		= "*";
		
		// Falls Datum und IP-Hash in DB vorhanden
		// Falls 2. Feld
		if(isset($queryPollCol[2])) {
			
			if(strtolower($queryPollCol[2]['Field']) == "date" || strtolower($queryPollCol[2]['Field']) == "datetime" || strtolower($queryPollCol[2]['Field']) == "timestamp")
				$pollDate	= $queryPollCol[2]['Field'];
			if(strtolower($queryPollCol[2]['Field']) == "ip-hash" || strtolower($queryPollCol[2]['Field']) == "ip")
				$pollIP		= $queryPollCol[2]['Field'];
			
			
			// Falls 3. Feld
			if(isset($queryPollCol[3])) {
				if(strtolower($queryPollCol[3]['Field']) == "date" || strtolower($queryPollCol[3]['Field']) == "datetime" || strtolower($queryPollCol[3]['Field']) == "timestamp")
					$pollDate	= $queryPollCol[3]['Field'];
				if(strtolower($queryPollCol[3]['Field']) == "ip-hash" || strtolower($queryPollCol[3]['Field']) == "ip")
					$pollIP		= $queryPollCol[3]['Field'];
			}
			
			// Gruppierung für die Zählung nach Datum und IP eingrenzen
			if(empty($pollDate) || empty($pollIP))
				return "poll not configured.";

			$countStr = "DISTINCT DATE(`" . $pollDate . "`), `" . $pollIP . "`";
		}
		
		
		// db-Query nach Feldern des zu bearbeitenden Formulars
		$queryFormFields = $this->DB->query( "SELECT p.`label_" . $this->lang . "`, p.`options_" . $this->lang . "`
													FROM `" . DB_TABLE_PREFIX . "forms` AS n 
													LEFT JOIN `" . DB_TABLE_PREFIX . "forms_definitions` AS p 
													ON n.`id` = p.`table_id` 
													WHERE p.`table_id` = " . $formID . "
													AND `field_name` = '" . $pollField . "'
													ORDER BY p.`sort_id` ASC
												  ", false);
				
		#var_dump($queryFormFields);
		
		$pollLabel		= $queryFormFields[0]['label_' . $this->lang];
		$pollOptions	= $queryFormFields[0]['options_' . $this->lang];
		$pollOptions	= explode("\r\n", $pollOptions);
		#var_dump($pollOptions);
		
		// db-Query nach Formular-Daten
		$queryFormData = $this->DB->query( "SELECT `" . $pollField . "`, MAX(" . $pollDate . ") AS lastvote, COUNT(" . $countStr . ") AS cnt 
													FROM `" . DB_TABLE_PREFIX . "form_" . $table . "` 
													GROUP BY `" . $pollField . "`
													ORDER BY `" . $pollField . "` ASC
												  ", false);
		#die(var_dump($queryFormData));
		
		$loop = "";
		$i = 0;
		$total = 0;
		$values = array();
		$lastVote = 0;
		
		
		foreach($pollOptions as $opt) {
			$count = 0;
			foreach($queryFormData as $pollData) {
				if($pollData[$pollField] == $i+1) {
					$count = isset($pollData['cnt']) ? $pollData['cnt']['cnt'] : 0;
					$lastVote = isset($pollData['cnt']['lastvote']) && $pollData['cnt']['lastvote'] > $lastVote ? $pollData['cnt']['lastvote'] : $lastVote;
				#die(var_dump($count));
				// Falls HTML5, Meter-Tag verwenden
				#if($this->html5)
				#	$loop .=	'<tr><td>' . $opt . '{max-' . $i . '}</td><td style="text-align:right;">' . $count . ' <span class="pollPercentage">({' . $i . '}%)</span></td><td class="chartField"><meter class="chartBar" min="0" max="100" value="{' . $i . '}">{' . $i . '}%</meter></td></tr>';
				
				#else
				}
			}
			$values[] = $count;
			$total += $count;
			$loop .=	'<tr><td>' . $opt . '</td><td>{max-' . $i . '}</td><td class="resultField">' . $count . ' <span class="pollPercentage">({' . $i . '}%)</span></td><td class="chartField"><span class="chartBar"><span class="pollMeter" style="width:{' . $i . '}%;">&nbsp;</span></span></td></tr>';
		
			$i++;
		}
		
		// Maximaler Wert des Polls
		$maxVal = array_keys($values, max($values));
		
		foreach($values as $key => $value) {
			
			$percentage = $total == 0 ? 0 : round($value / $total * 100, 0);
			$maxIcon	= '<img src="' . PROJECT_HTTP_ROOT . '/' . IMAGE_DIR . '/ok.png" alt="Poll Leader" class="maxPollIcon" />';
			
			$loop = str_replace("{" . $key . "}", $percentage, $loop);
			
			// Vor höchster Poll-Option Icon ausgeben
			if(in_array($key, $maxVal) && $value > 0)
				$loop = str_replace("{max-" . $key . "}", $maxIcon, $loop);
			else
				$loop = str_replace("{max-" . $key . "}", "&nbsp;", $loop);
			
		}
		
		$output .=		'<div class="pollData">' .
						'<h2>' . $this->formTitle . '</h2>' .
						'<h3>' . $pollLabel . '</h3>' .
						'<table>' .
						'<tfoot>' .
						'<tr><td>Total votes</td><td>&nbsp;</td><td class="resultField">' . $total . ' <span class="pollPercentage">(100%)</span></td><td class="chartField"><span class="chartBar"><span class="pollMeter" style="width:100%;">&nbsp;</span></span></td></tr>';
		
		// Falls Adminbereich, letztes Vote-Datum zufügen
		if($this->adminPage)
			$output .=	'<tr><td>Last vote</td><td>&nbsp;</td><td class="resultField">' . Admin::getDateString(strtotime($lastVote)) . '</td><td class="chartField"><span class="chartBar"><span>&nbsp;</span></span></td></tr>';
						
		$output .=	'</tfoot>' .
					'<tbody>' .
					$loop .
					'</tbody>' .
					'</table>' .
					'</div>';

		return $output;

	}	
	
	
	/**
	 * Methode zum Erstellen eines Konfigurations-Arrays für Formulare
	 * 
	 * @param	array	$type	Feldtyp
	 * @access	public
	 * @return	array
	 */
	public function getFormattedField($type, $value, $lang)
	{
		
		if(empty($type))
			return $value;
		
		
		switch($type) {
	
			case "date";
				$value	= Modules::getLocalDateString($value, $lang);
		}
		
		return $value;
	}
	
	
	/**
	 * Methode zum Erstellen eines Konfigurations-Arrays für Formulare
	 * 
	 * @param	array	$type	Feldtyp
	 * @access	public
	 * @return	array
	 */
	public function getRequiredHeadFiles($type)
	{
		
		if(empty($type))
			return false;
		
		
		switch($type) {
	
			case "date";
				// Datepicker head files setzen
				$this->setDatePicker($this->themeConf);
		}
	
	}
	
	
	/**
	 * Methode zum Erstellen eines Konfigurations-Arrays für Formulare
	 * 
	 * @access	public
	 * @return	array
	 */
	public function getFormControlSriptTag()
	{
		
		return '<script>
					head.ready("jquery", function(){
						$(document).ready(function(){
							$("select[data-action=\'autosubmit\']").bind("change", function(){
								$(this).parents(\'form\').submit();
							});
						});
					});
				</script>' . PHP_EOL;
	
	}

}
