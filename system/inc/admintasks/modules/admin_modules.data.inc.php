<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\ExtendDataEvent;


###################################################
#########  Artikel/Nachrichten/Termine  ###########
###################################################

// Event-Klassen einbinden
require_once SYSTEM_DOC_ROOT."/inc/admintasks/modules/events/event.ExtendData.php";

require_once SYSTEM_DOC_ROOT."/inc/admintasks/modules/admin_modules.inc.php"; // AdminModules-Klasse einbinden

// Daten-Module verwalten

class Admin_ModulesData extends Admin_Modules implements AdminTask
{

	// Vars 
	protected $o_extendDataEvent		= null;
	protected $catID					= "";
	protected $dataCat					= "";
	protected $dataCatDb				= "";
	protected $dataCatNew				= "";
	protected $dataCatOld				= "";
	protected $dataCatName				= "";
	protected $dataCatAlias				= "";
	protected $dataParentCat			= "";
	protected $dataParentCatDb			= "";
	protected $oldParentCat				= "";
	protected $dataCatTeaser			= "";
	protected $dataCatTeaserDb			= "";
	protected $listCat					= "";
	protected $listCatName				= "";
	protected $dataCatGroup				= array("public");
	protected $existCats				= array();
	protected $existCatsData			= array();
	protected $catExists				= false;
	protected $catIsLocked				= false;
	protected $showCats					= false;
	protected $showExistingCats			= false;
	protected $hideNewCat				= false;
	protected $dataTable				= "";
	protected $catTable					= "";
	protected $dataTableDB				= "";
	protected $catTableDB				= "";
	protected $useCatImage				= false;
	protected $dataCatImage				= "";
	protected $editCat					= false;
	protected $editAccess				= false;
	protected $newData					= false;
	protected $editData					= false;
	protected $dataIsLocked				= false;
	protected $editDataID				= "";
	protected $editEntry				= array();
	protected $dataID					= "";
	protected $dataIDdb					= "";
	protected $editID					= "";
	protected $editIDdb					= "";
	protected $sortID					= "";
	protected $sortIDOld				= "";
	protected $oldSortID				= "";
	protected $authorID					= "";
	protected $authorName				= "";
	protected $dataAlias				= "";
	protected $dataDate					= "";
	protected $dataHeader				= "";
	protected $dataTeaser				= "";
	protected $dataText					= "";
	protected $dataTextDB				= "";
	protected $dataTags					= "";
	protected $dataFeatured				= 0;
	protected $targetPage				= "";
	protected $currentDataUrl			= "";
	protected $dataPubState				= 0;
	protected $dataCalls				= "";
	protected $dataRating				= "";
	protected $dataComments				= "";
	protected $objectNumber				= 0;
	protected $objectOutput				= "";
	protected $objectUpdateStrings		= array();
	protected $objectCon				= array();
	protected $objectConAlt				= array(1 => "");
	protected $catImg					= array();
	protected $catImgDb					= "";
	protected $authorFilterCat 			= "";
	protected $authorFilter				= "";
	protected $dataGroupRead			= array("public");
	protected $dataGroupWrite			= array();
	protected $dataGroupReadDb			= "";
	protected $dataGroupWriteDb			= "";
	protected $dataGroupReadStr			= "";
	protected $dataGroupWriteStr		= "";
	protected $dataStatus				= "";
	protected $listData					= true;
	protected $dataEntriesArr			= array();
	protected $noChange					= false;
	protected $catLevelArray			= array();
	protected $showCalendar				= false;
	protected $sortable					= false;
	protected $sortParam				= "";
	protected $pubFilter				= "all";
	protected $dataNav					= "";
	protected $totalRows				= "";
	protected $startRow					= "";
	protected $pageNum					= "";
	protected $textAreaCount			= 0;
	protected $dbInsertStr1				= "";
	protected $dbInsertStr2				= "";
	protected $dbUpdateStr				= "";
	protected $objUpdateStr				= "";
	protected $wrongInput				= array();
	protected $noticeSuccess			= "";
	protected $successChange			= false;

	public function __construct($DB, $o_lng, $task, $init = false)
	{
	
		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng, $task, $init);
		
		parent::$task = $task;
		
		// Events listeners registrieren
		$this->addEventListeners("admindata");
		$this->addEventListeners("admin" . parent::$type);
		
		// ExtendDataEvent
		$this->o_extendDataEvent					= new ExtendDataEvent($this->DB, $this->o_lng, parent::$type);
		$this->o_extendDataEvent->editorLog			= $this->editorLog;
		
		$this->headIncludeFiles['sortable']			= true;
		$this->headIncludeFiles['moduleeditor']		= true;

		// Optionen für File-Upload
		$this->uploadMethod							= Files::getUploadMethod();
		$this->allowedFileSizeStr					= Files::getFileSizeStr(Files::getAllowedFileSize());
		$this->allowedFiles							= Files::getAllowedFiles();
		sort($this->allowedFiles);

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:admin'.parent::$type.'}' . PHP_EOL . 
									$this->closeTag("#headerBox");

		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		
		
		$this->formAction		= ADMIN_HTTP_ROOT . "?task=modules&type=" . parent::$type;
		
		
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();

		
		// Notifications
		$this->notice 	= $this->getSessionNotifications("notice");
		$this->hint		= $this->getSessionNotifications("hint");


		// Ggf. zu große POST-Requests abfangen
		if($checkPostSize	= $this->checkPostRequestTooLarge())
			$this->notice  .= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText("{s_error:postrequest}"), $checkPostSize), "error");

		
		// ModulesData
		$this->getModulesData();
		
		$this->adminContent	.= $this->getBackButtons(parent::$type);
		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		return $this->adminContent;

	}
	
	
	/**
	 * Methode zur Bearbeitung von Moduldaten (Artikel, News, Termine)
	 * 
	 * @access	protected
	 * @return	array
	 */
	protected function getModulesData()
	{

		// Daten-Tabellen festlegen
		$this->setDataTables();

		
		// Kategorie-Bild-Array
		$this->catImg["type"]					= "";
		$this->catImg[$this->editLang]			= "";
		
		// Daten-Objekt-Array
		$this->objectCon[1]["type"]				= "";
		$this->objectCon[1][$this->editLang]	= "";

		
		// Anzahl an Einträgen pro Seite
		$this->limit = $this->getLimit();

		
		// Request auswerten
		$this->evalRequest();
		
		
		// db-Query nach Datensatzkategorien
		$this->getExistingCatData();
		
		

		##############  Kategorie anlegen/bearbeiten  ###############
		//
		// Falls das Formular zum Anlegen oder Bearbeiten einer Kategorie abgeschickt wurde
		if(isset($GLOBALS['_POST']['dataCatName'])
		|| isset($GLOBALS['_POST']['edit_cat'])
		) {
		
			$this->processDataCat();
		
		}
		else {
		
			// Kategoriebild holen (beinhaltet updateStr)
			$this->getDataCatImg();

		}


		##############  Neue(r) Datensatz  ###############
		//
		// Falls das Formular zum Anlegen eines neuen Datensatzes abgeschickt wurde
		if($this->newData) {
			
			if(isset($GLOBALS['_POST']['mod_submit'])) {
			
				$this->processNewDataEntry();
			}
		
		}		

		##############  Datensatz bearbeiten  ###############
		//
		// Falls eine einzelner Datensatz zum Bearbeiten in der Session gespeichert ist
		elseif($this->editData) {
			
			$this->editID		= $this->editDataID;
			$this->editIDdb		= $this->DB->escapeString($this->editID);			
		
			$this->processEditDataEntry();
		
		}
		
		##############  Datensatzliste  ###############
		//
		// Andernfalls Nachrichtenliste auslesen
		else {
		
			$this->readDataEntries();

		}

		// Falls Kategorie oder Datensatz blockiert ist
		if($this->catIsLocked
		|| $this->dataIsLocked) {
			
			return $this->adminContent;
		}
		
		
		// Datenmodul-Bereich
		// Falls kein Lock gesetzt, Formulare anzeigen
		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL;


		// Meldungen ausgeben
		$this->getResultNotices();


		// Auf Listenanzeigen überprüfen
		$this->setListToggles();
		

		
		// Bereich: Datenmodule
		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_nav:admin'.parent::$type.'}</h2>' . PHP_EOL;
		

		
		// Falls Calendaransicht
		if($this->showCalendar) {
		
			$this->o_extendDataEvent->showCalendar 	= $this->showCalendar;
		
			$this->adminContent .= 	'<div id="calendar"></div>' . PHP_EOL;
			$this->adminContent .= 	'</div>' . PHP_EOL; // close AdminArea
			$this->adminContent .=	self::getFullcalendarScriptTag();
			$this->adminContent .= 	'<div class="adminArea">' . PHP_EOL;
			$this->adminContent .=	self::getDataNewSection("short");
			#$this->adminContent .=	self::getEditEntryForm("short");
			$this->adminContent .= 	'</div>' . PHP_EOL; // close AdminArea
			
			return $this->adminContent;
		
		}
		
		// Section: Kategorie, falls nicht Author
		if($this->editorLog)
			$this->adminContent .=	$this->getDataCategoriesSection();
		
		
		// Falls noch keine Kategorien angelegt sind, Ausgabe schließen
		if(count($this->existCats) == 0) {
		
			$this->adminContent .= 	'</div>' . PHP_EOL; // close AdminArea
			$this->adminContent .=	self::getScriptTag();
			$this->adminContent .=	$this->getDataAdminTourScript();
			
			return $this->adminContent;
		
		}

		
		// Section: Datensätze bearbeiten
		$this->adminContent .=	$this->getDataEditSection($this->g_Session['userid']);
	
		
		// Ggf. Section: Neuer Datensatz
		if($this->newData)
			$this->adminContent .=	$this->getDataNewSection();
		
		
		
		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();

		
		$this->adminContent .= 	'</div>' . PHP_EOL; // close AdminArea
		
		
		// Skripts (Editor/Sortable)
		$this->adminContent .=	self::getScriptTag();
	
	
		// Admin Tour Script
		$this->adminContent .=	$this->getDataAdminTourScript();

		
		return $this->adminContent;

	}
	
	
	
	/**
	 * Datentabellen bestimmen (Artikel, News, Termine)
	 * 
	 * @access	protected
	 * @return	void
	 */
	protected function setDataTables()
	{

		// Daten-Tabellen festlegen
		switch(parent::$type) {

			case "articles":
				$this->dataTable	= "articles";
				$this->catTable		= "articles_categories";
				$this->dataTableDB	= DB_TABLE_PREFIX . "articles";
				$this->catTableDB	= DB_TABLE_PREFIX . "articles_categories";
				break;
			
			case "news":
				$this->dataTable	= "news";
				$this->catTable		= "news_categories";
				$this->dataTableDB	= DB_TABLE_PREFIX . "news";
				$this->catTableDB	= DB_TABLE_PREFIX . "news_categories";
				break;
			
			case "planner":
				$this->dataTable	= "planner";
				$this->catTable		= "planner_categories";
				$this->dataTableDB	= DB_TABLE_PREFIX . "planner";
				$this->catTableDB	= DB_TABLE_PREFIX . "planner_categories";
				break;
			
		}
	}
	
	
	
	/**
	 * Data Request auswerten
	 * 
	 * @access	protected
	 * @return	void
	 */
	protected function evalRequest()
	{
	
		// Request auswerten		
		// Daten-ID
		if(!empty($GLOBALS['_GET']['data_id'])) {
			$this->editDataID	= (int)$GLOBALS['_GET']['data_id'];
			$this->editData		= true;
		}
		elseif(!empty($this->g_Session[parent::$type . '_id'])) {
			$this->editDataID	= (int)$this->g_Session[parent::$type . '_id'];
			$this->editData		= true;
		}

		// Ggf. News-ID aus Session löschen
		if((isset($GLOBALS['_POST']['add_new'])
		||  isset($GLOBALS['_POST']['list_cat'])
		||  isset($GLOBALS['_POST']['go_edit_cat'])
		||  isset($GLOBALS['_GET']['list_cat']))
			&& isset($this->g_Session[parent::$type . '_id'])
		) {
			$this->unsetSessionKey(parent::$type . '_id');
			$this->editData		= false;
			$this->editDataID	= "";
		}
		
		// Sort
		if(isset($GLOBALS['_GET']['sort_param']) && $GLOBALS['_GET']['sort_param'] != "")
			$this->sortParam	= $GLOBALS['_GET']['sort_param'];
		
		if(isset($GLOBALS['_GET']['sort']) && $GLOBALS['_GET']['sort'] == 1) // Falls gerade Sortiert wurde, eigene Sortierung anzeigen
			$this->sortParam	= "sortid";
		
		elseif(isset($GLOBALS['_POST']['sort_param']))
			$this->sortParam	= $GLOBALS['_POST']['sort_param'];
		
		elseif(!empty($this->g_Session['orderby']))
			$this->sortParam	= $this->g_Session['orderby'];
			
		// Filter
		if(isset($GLOBALS['_GET']['pub']) && $GLOBALS['_GET']['pub'] != "")
			$this->pubFilter	= $GLOBALS['_GET']['pub'];
		
		elseif(isset($GLOBALS['_POST']['filter_pub']))				
			$this->pubFilter	= $GLOBALS['_POST']['filter_pub'];
		

		if(!empty($GLOBALS['_GET']['list_cat']))
			$this->listCat		= $GLOBALS['_GET']['list_cat'];
		
		if(!empty($GLOBALS['_POST']['list_cat']))
			$this->listCat		= $GLOBALS['_POST']['list_cat'];

		if($this->listCat != "")
			$this->listData		= true;
	
		// Falls neuer Datensatz
		if(isset($GLOBALS['_POST']['add_new'])
		|| isset($GLOBALS['_POST']['mod_submit'])
		) {
			$this->newData		= true;
			$this->editData		= false;
		}
	
		// Falls Calendar
		if(!empty($_REQUEST['calendar'])) {
			$this->showCalendar	= true;
			$this->listData		= true;
			$this->newData		= false;
			$this->editData		= false;
		
			// Falls Calendar datum
			if(!empty($_REQUEST['date'])) {
				$this->dataDate		= urldecode($_REQUEST['date']);
			}
		}

	}
	
	
	
	/**
	 * Daten von Kategorien auslesen
	 * 
	 * @access	protected
	 * @return	void
	 */
	protected function getExistingCatData()
	{
	
		// Falls ein Author geloggt ist, Daten von anderen ausblenden
		if($this->loggedUserGroup ==  "author") {
		
			$this->authorFilterCat	= " WHERE dct.`group_edit` = ''" .
											$this->getAuthorPermissionQueryExt();

			$this->authorFilter		= " LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
											ON dt.`author_id` = user.`userid` 
										WHERE dt.`author_id` = '" . (int)$this->loggedUserID . "'";
		}

		// Falls ein Editor geloggt ist, Daten von anderen ausblenden
		elseif($this->loggedUserGroup ==  "editor") {
		
			$this->authorFilterCat	= " WHERE dct.`group_edit` = ''" .
											$this->getAuthorPermissionQueryExt();

			$this->authorFilter		= " LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
											ON dt.`author_id` = user.`userid` 
										WHERE dct.`group_edit` != 'admin' 
											AND (`author_id` = '" . (int)$this->loggedUserID . "' 
												OR dct.`group_edit` = ''" .
												$this->getAuthorPermissionQueryExt() .
												")";
		}
		
		else {													 
			$this->authorFilter		= " LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
											ON dt.`author_id` = user.`userid` 
										WHERE dt.`author_id` != ''";
		}
		
		// db-Query nach Datenkategorien		
		$this->existCats = $this->DB->query("SELECT * 
											FROM `$this->catTableDB` AS dct" .
												$this->authorFilterCat .
											" ORDER BY `sort_id` ASC
											", false);
		#var_dump($this->existCats);


		// db-Query nach Daten entries
		$this->existCatsData = $this->DB->query("SELECT *, dct.`cat_id`, COUNT(*) AS datasets 
												FROM `$this->catTableDB` AS dct 
												LEFT JOIN `$this->dataTableDB` AS dt 
													ON dct.`cat_id` = dt.`cat_id` 
												$this->authorFilterCat 
												GROUP BY dct.`cat_id`
												ORDER BY dct.`sort_id` ASC 
												", false);
		#die(var_dump($this->existCatsData));
	
	}
	

	
	/**
	 * getAuthorPermissionQueryExt
	 * 
	 * @param	$alias		db query alias
	 * @access	protected
	 * @return	string
	 */
	protected function getAuthorPermissionQueryExt($alias = "dct")
	{
	
		$output	= "";
		
		foreach($this->loggedUserOwnGroups as $group) {
			$output	.= " OR FIND_IN_SET('" . $this->DB->escapeString($group) . "', " . $alias . ".`group_edit`)";
		}
		return $output;
		
	}
	

	
	/**
	 * Section: Kategorie
	 * 
	 * @access	protected
	 * @return	string
	 */
	protected function getDataCategoriesSection()
	{
	
		$output	= "";
		$hide	= "";
	
		if (count($this->existCats) > 0
		&& !$this->showCats
		)
			$hide	= ' hideNext';
			
		// Bereich: Kategorie anlegen bzw. bearbeiten
		$output		 .=	'<h3 class="cc-h3 switchToggle' . $hide . '">{s_header:'.parent::$type.'cat}</h3>' . PHP_EOL;
		
		$output		 .=	'<div class="editDataCategories adminBox"' . (!empty($hide) ? ' style="display:none;"' : '') . '>' . PHP_EOL;
		
		
		$hideStyle	= !$this->showExistingCats ? ' style="display:none;"' : '';
		
		
		// Bestehende Kategorien
		$output		 .=	'<h4 class="cc-h4 toggle">{s_label:catexist}</h4>' . PHP_EOL .
						'<div class="existingCatsList adminBox"' . $hideStyle . '>' . PHP_EOL;
		
		
		// Kategorien auflisten (falls vorhanden)
		if(count($this->existCats) > 0) {
			
			$output		 .=	$this->listDataCategories();
		
		}
		else
			$output		 .=	'<p class="notice error">{s_notice:nocats}</p>' . PHP_EOL;

		
		// Button neue Cat
		$output		 .=	'<span class="newCatButton-panel buttonPanel">' . PHP_EOL .
						$this->getAddCatButton("right") .
						'<br class="clearfloat" />' . PHP_EOL .
						'</span>' . PHP_EOL;

		$output		 .=	'</div>' . PHP_EOL; // close .existingCatsList

		
		// Kategorie neu/bearbeiten
		if(!$this->hideNewCat) {
		
			$output		 .=	$this->getSectionCatDetails();
		
		}		
		
		$output		 .=	'</div>' . PHP_EOL; // close .editDataCategories
		
		return $output;
	
	}
	

	
	/**
	 * Kategorien auflisten
	 * 
	 * @access	protected
	 * @return	string
	 */
	protected function listDataCategories()
	{

		$output	= "";
		
		// Falls Einträge von nur einer Kategorie angezeigt werden, Sortierung auf true setzen
		if(isset($this->sortParam) && $this->sortParam == "sortid" && $this->listCat != "all")
			$this->sortable	= true;
		
		$j = 0;
		
		// Actionbox
		$output		 .=	$this->getDataCatActionBox();
		
		$output		 .=	'<ul class="editList dataCatList list list-condensed sortableData sortable-container" id="sortableDataCat" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=sort&mod=' . parent::$type . '">' . PHP_EOL;

		foreach($this->existCats as $cat) {
			
			$j++;
			
			$childTag	= "";
			$parCat		= $cat['parent_cat'];
			
			while($parCat > 0) { // Elternkat. auslesen zur Markierung der Kindlevels
				$childTag .= '&#746; ';
			
				$pCatID = (int)$this->DB->escapeString($parCat);
			
				// db-Query nach allen data
				$queryParentCat = $this->DB->query("SELECT * 
															FROM `$this->catTableDB` 
															WHERE  
															`cat_id` = $pCatID 
															AND `parent_cat` > 0
															", false);
			
				#var_dump($queryParentCat);
				
				if(count($queryParentCat) > 0)
					$parCat = $queryParentCat[0]['parent_cat'];
				else
					$parCat = 0;
			}
			
			$accessDenied	= !parent::arraySearch_recursive($cat['cat_id'], $this->existCatsData);
			
			$catNameList	= $cat['category_' . $this->editLang];
			
			$markBox	=	'<label class="markBox' . ($accessDenied ? ' disabled' : '') . '">' . PHP_EOL .
							'<input type="checkbox" class="addVal" name="catIDs[]" value="' . $cat['cat_id'] . '"' . ($accessDenied ? ' disabled="disabled"' : '') . ' />' . PHP_EOL .
							'</label>' . PHP_EOL;				
			
			$output		 .=	'<li id="datacatid-' . $cat['cat_id'] . '" class="';
			
			if($childTag == "")								$output		 .= 'parentCat level_0';
			elseif($childTag == "&#746; ")					$output		 .= 'parentCat level_1';
			elseif($childTag == "&#746; &#746; ")			$output		 .= 'parentCat level_2';
			elseif($childTag == "&#746; &#746; &#746; ")	$output		 .= 'parentCat level_3';
			else											$output		 .= 'childCat';
			
			$this->catLevelArray[] = $childTag;
			
			if($accessDenied)								$output		 .= ' disabled';
			
			$output		 .=	' listItem listEntry sortid-' . $cat['sort_id'] . '" data-catid="' . $cat['cat_id'] . '" data-sortid="' . $cat['sort_id'] . '" data-sortidold="' . $cat['sort_id'] . '" data-menu="context" data-target="contextmenu-cats-' . $j . '">' . PHP_EOL;
			
			$output		 .=	$markBox;

			$output		 .=	'<span class="listNr">[#' . $cat['cat_id'] . ']</span>' . PHP_EOL .
							'<span class="listName">' . ($childTag != "" ? $childTag . $catNameList . '</span>' . PHP_EOL .
							'<span class="childof">({s_text:childof} #' . $cat['parent_cat'] . ')' : $catNameList) . '</span>' . PHP_EOL;
			
			// EditButtons
			$output		 .=	'<span class="editButtons-panel" data-id="contextmenu-cats-' . $j . '">' . PHP_EOL;

			// Button new data
			$editButtons	=	$this->getAddDataToCatButton($cat['cat_id'], "image", $j);

			// Button list data
			$editButtons	.= '<form action="' . $this->formAction . '#cfm" method="post" accept-charset="UTF-8">' . PHP_EOL;
			
			// Button list
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "list_cat",
									"class"		=> 'button-icon-only',
									"value"		=> $cat['cat_id'],
									"text"		=> "",
									"title"		=> '{s_title:list'.parent::$type.'}',
									"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "list"
								);
			
			$editButtons   .=	parent::getButton($btnDefs);
			
			$editButtons   .=	'<input type="hidden" name="list_cat" value="' . $cat['cat_id'] . '" />' . PHP_EOL .
								'</form>' . PHP_EOL;

			// Falls eine bestimmte Kat angezeigt wird und mehrere Artikel vorhanden sind, nach unten Button einfügen
			if(count($this->existCats) > 1 && $j < count($this->existCats)) {
			
				// Button sortdown
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'sortcon button-icon-only',
										"title"		=> '{s_title:movedown}',
										"attr"		=> 'data-ajax="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=down&mod=' . parent::$type . '&cat=' . $cat['cat_id'] . '&sortid=' . $cat['sort_id'] . '" data-menuitem="true" data-id="item-id-' . $j . '"',
										"icon"		=> "sortdown"
									);
				
				$editButtons	.=	parent::getButton($btnDefs);
			}

			
			// Falls eine bestimmte Kat angezeigt wird und mehrere Artikel vorhanden sind, nach oben Button einfügen
			if(count($this->existCats) > 1 && $j > 1) {
			
				// Button sortup
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'sortcon button-icon-only',
										"title"		=> '{s_title:moveup}',
										"attr"		=> 'data-ajax="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=up&mod=' . parent::$type . '&cat=' . $cat['cat_id'] . '&sortid=' . $cat['sort_id'] . '" data-menuitem="true" data-id="item-id-' . $j . '"',
										"icon"		=> "sortup"
									);
				
				$editButtons	.=	parent::getButton($btnDefs);
			}
			
			$editButtons	 .=	'<form action="' . $this->formAction . '" id="editcatfm" method="post" accept-charset="UTF-8">' . PHP_EOL;
			
			// Button edit
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "edit_cat",
									"class"		=> 'button-icon-only',
									"value"		=> $cat['cat_id'],
									"text"		=> '',
									"title"		=> '{s_title:edit'.parent::$type.'cat}',
									"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "edit"
								);
			
			$editButtons	.=	parent::getButton($btnDefs);
		
		
			$this->o_extendDataEvent->catData		= $cat;
			
			// dispatch event get_goeditcat_fields
			$this->o_dispatcher->dispatch('cat.get_goeditcat_fields', $this->o_extendDataEvent);
			
			$editButtons    .=	$this->o_extendDataEvent->getOutput(true);
			
			
			$editButtons .=	'</form>' . PHP_EOL;
			
			
			// Button delete
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'delcon delcat button-icon-only',
									"title"		=> '{s_title:del'.parent::$type.'cat} -> ' . $cat['category_' . $this->editLang],
									"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=delcat&mod=' . parent::$type . '&catid=' . $cat['cat_id'] . '&sortid=' . $cat['sort_id'] . '" data-title="' . $cat['category_' . $this->editLang] . '" data-menutitle="{s_title:del'.parent::$type.'cat}" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "delete"
								);
			
			$editButtons	.=	parent::getButton($btnDefs);
			
			
			if(!$accessDenied)
				$output.= $editButtons;
			else
				$output.= parent::getIcon("adminuser", "noAccess inline-icon", 'title="{s_text:noaccess}"');
			
			
			$output		.=	'</span>' . PHP_EOL;
			
			$output		.=	'</li>' . PHP_EOL;
		}

		$output		 .=	'</ul>' . PHP_EOL;
		
		return $output;
	
	}
	

	
	/**
	 * Kategorie neu bzw. bearbeiten
	 * 
	 * @access	protected
	 * @return	void
	 */
	protected function processDataCat()
	{
	
		// Falls Edit
		if(isset($GLOBALS['_POST']['edit_cat'])) {
			
			$this->editCat			= true;
			$this->catID			= (int)$GLOBALS['_POST']['edit_cat'];
			$this->noticeSuccess	= "{s_notice:takechange}";
			

			if(isset($GLOBALS['_POST']['nochange'])
			&& $GLOBALS['_POST']['nochange'] == "1"
			) {
				$this->noChange		= true;
			}
			
			
			// Locking checken
			if($this->checkDataCatLock())
				
				return $this->adminContent;
		
		}
		
		// Falls New
		else {
			$this->noticeSuccess	= "{s_notice:newcat}";
		}

		
		// Kategorie Post auswerten
		$this->evalDataCatPost($GLOBALS['_POST']);
	
	}


	
	/**
	 * Kategorie Post auswerten
	 * 
	 * @param	array		$a_Post		POST-Array
	 * @access	protected
	 * @return	void
	 */
	protected function evalDataCatPost($a_Post)
	{

		$this->dataCatName			= trim($a_Post['dataCatName']);
		$this->dataParentCat		= (int)$a_Post['dataParentCat'];
		$this->oldParentCat			= (int)$a_Post['oldParentCat'];
		$this->oldSortID			= (int)$a_Post['sort_id'];
		$this->dataCatTeaser		= $a_Post['catTeaser'];
		$this->dataGroupRead		= $a_Post['newsGroupRead'];
		$this->dataGroupWrite		= array_filter($a_Post['newsGroupWrite']);
		
		if(in_array('public', $this->dataGroupRead)) {
			$this->dataGroupRead 	= array('public');
			$this->dataGroupReadStr = 'public';
		}
		else
			$this->dataGroupReadStr = implode(",", $this->dataGroupRead);
		
		if(!empty($this->dataGroupWrite)) {
			$this->dataGroupWrite 	= $this->getPageEditGroups($this->dataGroupWrite);
			$this->dataGroupWriteStr = implode(",", $this->dataGroupWrite);
		}
		
		$this->dataComments			= (int)$a_Post['newsComments'];
		$this->dataRating			= (int)$a_Post['newsRating'];
		
		// Target page
		$this->targetPage			= (int)$a_Post['dataTargetPageID'];
		
		// Cat Event
		$this->o_extendDataEvent->a_Post	= $a_Post;
		
		// dispatch event eval_cat_post 
		$this->o_dispatcher->dispatch('cat.eval_cat_post', $this->o_extendDataEvent);
		
		// get event errors
		$this->wrongInput	= array_merge($this->wrongInput, $this->o_extendDataEvent->wrongInput);
		

		
		// Cat image			
		// Kategorie-Bildinfo (bestehende cat)
		if(!empty($a_Post['catImg'])) {
			
			$this->useCatImage	= true;
			$p_catImg			= $a_Post['catImg'];
		
			$this->catImg		= $this->getCatImageParams($p_catImg);
		}
		
		// Typ auf Bild festlegen
		$this->catImg["type"]	= "img";
		

		// Kategoriebild holen (beinhaltet updateStr)
		$o_dataCatObj			= $this->getDataCatImg();

		
		// Falls Kategorie zum Ändern geladen werden soll
		if($this->noChange) {

			// Falls sich von vorher noch eine id zum Bearbeiten von News in der Session befindet, diese löschen
			$this->unsetSessionKey(parent::$type . '_id');
			
			return false;
		
		}
		
		
		// Eingaben überprüfen (Kategorie)
		// Überprüfen ob Kategorie vorhanden
		$this->catExists	= $this->checkCatNameExists($this->dataCatName);

		
		// Kategoriename überprüfen
		if($this->dataCatName == "")
			$this->wrongInput['catName'] = "{s_notice:no".parent::$type."cat}";

		elseif(is_numeric($this->dataCatName))
			$this->wrongInput['catName'] = "{s_notice:wrongcat}";

		elseif(!$this->validateTitle($this->dataCatName, false, true))
			$this->wrongInput['catName'] = "{s_notice:wrongname}";

		elseif(strlen($this->dataCatName) > 128)
			$this->wrongInput['catName'] = "{s_notice:longname}";
		
		elseif($this->catExists == true)
			$this->wrongInput['catName'] = "{s_notice:catexist}";
		
		
		// Falls cat image
		if(isset($a_Post['add_catimg'])
		&& $a_Post['add_catimg'] == "on"
		) {
			
			// Info für weitere Sprachen auslesen
			if(isset($a_Post['old_catimg'])) {
				$this->catImg				= (array)json_decode($a_Post['old_catimg']);
				$this->catImg["type"]		= "img";
			}
			$this->useCatImage				= true;
			$this->catImg[$this->editLang]	= $o_dataCatObj->objectUpdateStrings["cat"]["img"];
			$this->catImgDb					= json_encode($this->catImg);
			$this->catImgDb					= $this->DB->escapeString($this->catImgDb);
		}

		
		// Falls korrekter Kategoriename
		if(!isset($this->wrongInput['catName'])) {

			$this->saveCatToDb();
		
		}
		
		// Falls sich von vorher noch eine id zum Bearbeiten von News in der Session befindet, diese löschen
		$this->unsetSessionKey(parent::$type . '_id');
	
	}
	

	
	/**
	 * Kategorie speichern
	 * 
	 * @access	protected
	 * @return	boolean
	 */
	protected function saveCatToDb()
	{

		// Safestrings
		$this->dataCatDb		= $this->DB->escapeString($this->dataCatName);
		$this->dataParentCatDb	= $this->DB->escapeString($this->dataParentCat);
		$this->dataCatTeaserDb	= $this->DB->escapeString($this->dataCatTeaser);
		$this->dataGroupReadDb	= $this->DB->escapeString($this->dataGroupReadStr);
		$this->dataGroupWriteDb	= $this->DB->escapeString($this->dataGroupWriteStr);
		$this->dataComments		= (int)$this->dataComments;
		$this->dataRating		= (int)$this->dataRating;
		$this->dbInsertStr1		= "";
		$this->dbInsertStr2		= "";
		$this->dbUpdateStr		= "";
		
		
		// sortID bestimmen
		$this->getCatSortID($GLOBALS['_POST']);
		
		
		// targetPage bestimmen
		$this->getTargetPage();
		
		
		// DB Strings holen
		$this->getCatDbStrings();
		
		
		// Eintrag in DB (Kategorie)
		$this->writeCatDb();
	
	}	
	

	
	/**
	 * DB Strings holen (Kategorie)
	 * 
	 * @access	protected
	 * @return	boolean
	 */
	protected function getCatDbStrings()
	{
		
		$this->dbInsertStr1 .=	"`parent_cat`," .
								"`sort_id`," .
								"`group`," .
								"`group_edit`," .
								"`comments`," .
								"`rating`," .
								"`image`," .
								"`target_page`,";
		
		$this->dbInsertStr2 .=	$this->dataParentCatDb . "," .
								$this->sortID . ",'" .
								$this->dataGroupReadDb . "','" .
								$this->dataGroupWriteDb . "'," .
								$this->dataComments . "," .
								$this->dataRating . ",'" .
								$this->catImgDb . "'," .
								$this->targetPage . ",";
		
		$this->dbUpdateStr .=	"`parent_cat` = $this->dataParentCatDb," .
								"`sort_id` = $this->sortID," .
								"`group` = '" . $this->dataGroupReadDb . "'," .
								"`group_edit` = '" . $this->dataGroupWriteDb . "'," .
								"`comments` = $this->dataComments," .
								"`rating` = $this->dataRating," .
								"`image` = '" . $this->catImgDb . "'," .
								"`target_page` = $this->targetPage,";
		
		
		// Cat Event
		// dispatch event make_cat_dbstring 
		$this->o_dispatcher->dispatch('cat.make_cat_dbstring', $this->o_extendDataEvent);
		
		// DB strings
		$this->dbInsertStr1 .= $this->o_extendDataEvent->dbInsertStr1;
		$this->dbInsertStr2 .= $this->o_extendDataEvent->dbInsertStr2;
		
		$this->dbUpdateStr  .= $this->o_extendDataEvent->dbUpdateStr;
		
		
		// Falls insert, für alle Sprachen übernehmen
		foreach($this->o_lng->installedLangs as $lang) {
			
			$this->dbInsertStr1 .= '`category_' . $lang . '`,`cat_teaser_' . $lang . '`,';
			$this->dbInsertStr2 .= "'" . $this->dataCatDb . "','" . $this->dataCatTeaserDb . "',";
			
		}		
		
		$this->dbUpdateStr .=	"`category_" . $this->editLang . "` = '" . $this->dataCatDb . "'," .
								"`cat_teaser_" . $this->editLang . "` = '" . $this->dataCatTeaserDb . "',";
		
		$this->dbInsertStr1	= substr($this->dbInsertStr1, 0, -1);
		$this->dbInsertStr2	= substr($this->dbInsertStr2, 0, -1);
		
		$this->dbUpdateStr	= substr($this->dbUpdateStr, 0, -1);
	
	}
	

	
	/**
	 * Kategorie speichern
	 * 
	 * @access	protected
	 * @return	boolean
	 */
	protected function writeCatDb()
	{		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->catTableDB`");


		if(!empty($this->catID)) {
			
			// Aktualisierung der Sort-IDs
			if($this->sortID > $this->oldSortID)
				
				$updateSQL = $this->DB->query("UPDATE $this->catTableDB 
													SET `sort_id` = `sort_id`-1 
													WHERE `sort_id` > $this->oldSortID AND `sort_id` <= $this->sortID
													");
		
			elseif($this->sortID < $this->oldSortID)
				
				$updateSQL = $this->DB->query("UPDATE $this->catTableDB 
													SET `sort_id` = `sort_id`+1 
													WHERE `sort_id` < $this->oldSortID AND `sort_id` >= $this->sortID
													");
		
			// Aktualisierung der Kategorie
			$insertSQL = $this->DB->query("UPDATE $this->catTableDB 
												SET " . $this->dbUpdateStr . " 
												WHERE `cat_id` = $this->catID
												");
			#var_dump($updateSQL);
		}
		else {
			
			// Aktualisierung der Sort-IDs
			$updateSQL = $this->DB->query("UPDATE $this->catTableDB 
												SET `sort_id` = `sort_id`+1 
												WHERE `sort_id` >= $this->sortID
												");
				
			// Einfügen der neuen Kategorie
			$insertSQL = $this->DB->query("INSERT INTO `$this->catTableDB` 
												($this->dbInsertStr1) 
												VALUES ($this->dbInsertStr2)
												");

			#die(var_dump($insertSQL));			
		}
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		if($insertSQL === true) {
		
			$this->notice				= $this->noticeSuccess;
			$this->dataCat				= "";						
			$this->catImg["type"]		= "";
			$this->catImg[$this->editLang]	= "";
			$this->dataCatName			= "";
			$this->dataParentCat		= "";
			$this->dataGroupRead		= array("public");
			$this->dataGroupWrite		= array();
			$this->dataCatTeaser		= "";
			$this->dataComments			= "";
			$this->dataRating			= "";
			$this->dataFeed				= "";
			$this->targetPage			= "";
			
			unset($this->catID);
		
			// Cat Event
			// dispatch event reset_cat_attributes 
			$this->o_dispatcher->dispatch('cat.reset_cat_attributes', $this->o_extendDataEvent);

		}
		
		// Neue db-Query nach (aktualisierten) Newskategorien
		$this->getExistingCatData();
		
	}
	

	
	/**
	 * Überprüfen ob Kategoriename bereits vorhanden
	 * 
	 * @access	protected
	 * @return	int
	 */
	protected function checkCatNameExists($catName)
	{
	
		// Überprüfen ob Kategorie vorhanden
		if(count($this->existCats) > 0) {
		
			for($j = 0; $j < count($this->existCats); $j++) {
				
				if(in_array($catName, $this->existCats[$j])
				&& $this->existCats[$j]["category_" . $this->editLang] == $catName
				) {
					if(!isset($this->catID)
					|| $this->existCats[$j]["cat_id"] != $this->catID
					)
						return true;
				}
			}
		}
		return false;
	
	}
	

	
	/**
	 * Überprüfen ob eine Kategorie vorhanden ist
	 * 
	 * @param	string		$cat	cat name
	 * @access	protected
	 * @return	boolean
	 */
	protected function checkCatExists($cat)
	{
	
		// Überprüfen ob Kategorie vorhanden
		if(count($this->existCats) > 0) {
	
			foreach($this->existCats as $existCat) {
				if(in_array($cat, $existCat)) {
					$this->catExists = true;
					return true;
				}
			}
		}
		return false;
	
	}
	

	
	/**
	 * sortID für Kategorie bestimmen
	 * 
	 * @param	array		$a_Post		POST-Array
	 * @access	protected
	 * @return	int
	 */
	protected function getCatSortID($a_Post)
	{
	
		// sortID bestimmen
		if(isset($a_Post['edit_cat'])
		&& $this->dataParentCat == $this->oldParentCat
		)
			$this->sortID		= $this->oldSortID;
			
		else {
			if($this->dataParentCat == 0)
				$maxSortId	= $this->DB->query("SELECT MAX(`sort_id`) AS sortid 
													FROM `$this->catTableDB`
													", false);
			else
				$maxSortId	= $this->DB->query("SELECT MAX(n.`sort_id`) AS sortid, p.`sort_id` AS catsid 
													FROM `$this->catTableDB` AS n,
														 `$this->catTableDB` AS p 
													WHERE n.`parent_cat` = $this->dataParentCatDb 
													AND (p.`cat_id` = $this->dataParentCatDb AND p.`cat_id` != 0) 
													", false);
				
			
			$this->sortID	= (int)$maxSortId[0]['sortid'];
			
			if($this->dataParentCat != 0 && ($this->sortID == NULL || $this->sortID == false))
				$this->sortID = (int)$maxSortId[0]['catsid'];
			if($this->sortID < $this->oldSortID || $this->oldSortID == "")
				$this->sortID++;
		}
		
		return $this->sortID;
	
	}
	

	
	/**
	 * sortID für Datensatz bestimmen
	 * 
	 * @param	array		$a_Post		POST-Array
	 * @access	protected
	 * @return	int
	 */
	protected function getDataSortID($a_Post)
	{

		// sortID bestimmen
		if($a_Post['news_cat'] != $this->dataCatOld) {
			
			$this->dataCatNew = $this->DB->escapeString($a_Post['news_cat']);
			
			$maxSortId = $this->DB->query("SELECT MAX(`sort_id`) 
												FROM `$this->dataTableDB` 
												WHERE `cat_id` = " . $this->dataCatNew . "
												", false);
			
			$this->sortID	= $maxSortId[0]['MAX(`sort_id`)'];
			
			if($this->sortID == NULL || $this->sortID == false)
				$this->sortID = 1;
			else
				$this->sortID++;
							
		}
		else
			$this->sortID	= $this->sortIDOld;
		
		return $this->sortID;

	}
	

	
	/**
	 * Zielseite für Datenanzeige bestimmen
	 * 
	 * @access	protected
	 * @return	int
	 */
	protected function getTargetPage()
	{	
	
		if(!empty($this->targetPage))
			return (int)$this->targetPage;
		
		
		// Zielseite (ggf. von Elternkat.) übernehmen
		$queryTP = $this->DB->query("SELECT `target_page` 
										 FROM `$this->catTableDB` 
										 WHERE `cat_id` = $this->dataParentCatDb
										 ", false);
		
		if(count($queryTP) > 0 && $queryTP[0]['target_page'] != "")
			$this->targetPage = $queryTP[0]['target_page'];
			
		elseif(!empty($this->catID)) {

			$queryTP2 = $this->DB->query("SELECT `target_page` 
												 FROM `$this->catTableDB` 
												 WHERE `cat_id` = $this->catID
												 ", false);


			if(count($queryTP2) > 0)
				$this->targetPage = $queryTP2[0]['target_page'];
			else
				$this->targetPage = "";
		}
		
		else
			$this->targetPage = "";
		
		$this->targetPage = (int)$this->targetPage;
		
		return $this->targetPage;
	
	}
	

	
	/**
	 * Locking für Kategorie checken
	 * 
	 * @access	protected
	 * @return	boolean
	 */
	protected function checkDataCatLock()
	{
		
		// Locking checken
		if($this->checkLocking($this->catID, $this->catTable, $this->g_Session['username'], '{s_error:lockededitentry}')) {
			
			// Falls sich von vorher noch eine id zum Bearbeiten von News in der Session befindet, diese löschen
			if(!isset($GLOBALS['_GET']['data_id']) && !isset($GLOBALS['_GET']['refresh']))
				$this->unsetSessionKey(parent::$type . '_id');
			
			$this->catIsLocked	= true;
			
			return true;
			
		}
		return false;
	
	}
	

	
	/**
	 * Locking für Datensatz checken
	 * 
	 * @access	protected
	 * @return	boolean
	 */
	protected function checkDataLock()
	{

		// Locking checken
		if($this->checkLocking($this->editID, $this->dataTable, $this->g_Session['username'], '{s_error:lockededitentry}')) {
			
			// Falls sich von vorher noch eine id zum Bearbeiten von News in der Session befindet, diese löschen
			if(!isset($GLOBALS['_GET']['data_id']) && !isset($GLOBALS['_GET']['refresh']))
				$this->unsetSessionKey(parent::$type . '_id');
			
			$this->adminContent .=	self::getBackToListButtons("all");

			$this->dataIsLocked	= true;
			
			return true;
		}
		return false;
	
	}
	

	
	/**
	 * Gibt Kategoriebild Parameter zurück
	 * 
	 * @param	$p_catImg	Kategoriebild db str
	 * @access	protected
	 * @return	array
	 */
	protected function getCatImageParams($p_catImg)
	{
	
		// legacy object syntax
		if(substr_count($p_catImg, "|") >= 6) {
			$p_catImg						= explode("|", $p_catImg);
			$p_catImg						= implode("<>", $p_catImg);
			$p_catImg[$this->editLang]		= $p_catImg;
		}
		else{
			$p_catImg						= (array)json_decode($p_catImg);
			if(!isset($p_catImg[$this->editLang]))
				$p_catImg[$this->editLang]	= "";
		}
		
		return $p_catImg;
	
	}
	

	
	/**
	 * Kategoriebild-Objekt auslesen
	 * 
	 * @access	protected
	 * @return	object
	 */
	protected function getDataCatImg()
	{
	
		// Kategoriebild holen (beinhaltet updateStr)
		require_once SYSTEM_DOC_ROOT."/inc/adminclasses/class.EditDataObjects.php"; // EditDataObjects-Klasse einbinden

		$o_dataCatObj			= new EditDataObjects($this->DB, $this->o_lng);
		
		$this->dataCatImage		= $o_dataCatObj->getConfigElement("img", $this->catImg[$this->editLang], $GLOBALS['_POST'], "cat");
	
		return $o_dataCatObj;
	
	}
	

	
	/**
	 * Section: Kategorie neu/bearbeiten
	 * 
	 * @access	protected
	 * @return	string
	 */
	protected function getSectionCatDetails()
	{

		$output	= "";
		
		// Kategorie neu/bearbeiten
		$output		 .=	'<h4 class="cc-h4 toggle">';
		
		if($this->editCat)
			$output	 .=	'{s_label:editcat} #' . $this->catID;
		else
			$output	 .=	'{s_label:newcat}';

		
		$hide	= 	$this->hideNewCat ? ' style="display:none;"' : '';

		$output		 .=	'</h4>' . PHP_EOL;
		
		$output		 .=	'<div class="adminBox"' . $hide . '>' . PHP_EOL;
		$output		 .=	'<form action="' . $this->formAction . '#cfm" method="post" enctype="multipart/form-data" id="adminfm1" accept-charset="UTF-8">' . PHP_EOL . 
						'<ul class="framedItems">' . PHP_EOL;
		
		$output		 .=	'<li>' . PHP_EOL .
						'<div class="leftBox"><label>{s_label:catname}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;

		if(isset($this->wrongInput['catName']))
			$output		 .=	'<p class="notice">' . $this->wrongInput['catName'] . '</p>' . PHP_EOL;


		$output		 .=	'<input type="text" class="dataCat" name="dataCatName" value="' . (isset($this->dataCatName) && $this->dataCatName != '' && !$this->editData ? htmlspecialchars($this->dataCatName) : '') . '" maxlength="128" />' . PHP_EOL .
						'</div>' . PHP_EOL;
								
		$output		 .=	'<div class="rightBox"><label>{s_label:parentcat}</label>' . PHP_EOL .
						'<select class="dataParentCat iconSelect" name="dataParentCat">' . PHP_EOL .
						'<option value="0">-</option>' . PHP_EOL;

					
		$c = 0;
		foreach($this->existCats as $eCat) {
			if(!isset($this->catID) || $this->catID != $eCat['cat_id'])
				$output		 .='<option ' . ($eCat['parent_cat'] == 0 ? 'class="parentCat"' : '') . ' value="' . $eCat['cat_id'] . '"' . (isset($this->dataParentCat) && $this->dataParentCat == $eCat['cat_id'] ? ' selected="selected"' : '') . '>' . $this->catLevelArray[$c] . $eCat['category_'.$this->editLang] . '</option>' . PHP_EOL; // Benutzergruppe
			
			$c++;
		}
			
		$output		 .=	'</select>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<br class="clearfloat">' . PHP_EOL .
						'</li>' . PHP_EOL;
		
		$output		 .=	'<li class="fullBox">' . PHP_EOL .
						'<input type="hidden" name="oldParentCat" value="' . (isset($this->oldParentCat) ? $this->oldParentCat : '') . '" />' . PHP_EOL .
						'<input type="hidden" name="sort_id" value="' . (isset($this->catID) ? $this->oldSortID : '') . '" />' . PHP_EOL .
						'<ul class="rowlist">' . PHP_EOL .
						'<label>{s_label:catTeaser}<span class="editLangFlag">' . $this->editLangFlag . '</span><span class="toggleEditor" data-target="catTeaser">Editor</span></label>' . PHP_EOL .
						'<textarea name="catTeaser" id="catTeaser" rows="2" class="teaser cc-editor-add disableEditor">' . (isset($this->dataCatTeaser) ? htmlspecialchars($this->dataCatTeaser) : '') . '</textarea>' . PHP_EOL .
						'</ul>' . PHP_EOL .
						'</li>' . PHP_EOL;
		
		// Groups
		$output		 .=	'<li>' . PHP_EOL .
						'<ul class="rowlist clearfix">' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<div class="leftBox">' . PHP_EOL .
						'<label>{s_label:'.parent::$type.'group} / {s_common:rightsread}</label>' . PHP_EOL .
						'<select multiple="multiple" size="' . count($this->userGroups) . '" name="newsGroupRead[]" class="selgroup">' . PHP_EOL;
					
		foreach($this->userGroups as $group) {
			$output		 .='<option value="' . $group . '"' . (isset($this->dataGroupRead) && in_array($group, $this->dataGroupRead) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
		}
		
		$output		 .=	'</select>' .
						'</div>' . PHP_EOL;
						
		$output		 .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_common:rightswrite}</label>' . PHP_EOL .
						'<select multiple="multiple" size="' . (count($this->loggedUserEditGroups) +1) . '" name="newsGroupWrite[]" class="selgroup">' . PHP_EOL;
		
		$output		 .='<option value=""' . (empty($this->dataGroupWrite) ? ' selected="selected"' : '') . '>{s_title:defaultrigths}</option>' . PHP_EOL; // Benutzergruppe
					
		foreach($this->loggedUserEditGroups as $group) {
			$output		 .='<option value="' . $group . '"' . (!empty($this->dataGroupWrite) && in_array($group, $this->dataGroupWrite) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
		}
			
		$output		 .=	'</select>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'</li>' . PHP_EOL;

		
		// Cat Event
		$this->o_extendDataEvent->dataComments	= $this->dataComments;
		$this->o_extendDataEvent->dataRating	= $this->dataRating;
		$this->o_extendDataEvent->targetPage	= $this->targetPage;
		$this->o_extendDataEvent->useCatImage	= $this->useCatImage;
		$this->o_extendDataEvent->dataCatImage	= $this->dataCatImage;
		$this->o_extendDataEvent->catImg		= $this->catImg;
		
		// dispatch event get_cat_fields
		// Pre/Mid/Post
		$this->o_dispatcher->dispatch('cat.get_cat_fields', $this->o_extendDataEvent);
		
		$output		 .=	$this->o_extendDataEvent->getOutput(true);
		

		$output		 .=	'</ul>' . PHP_EOL;


		// Falls neue Kategorie
		if(!$this->editCat) {
			$output		 .=	'<ul>' . PHP_EOL . 
							'<li class="submit change">' . PHP_EOL;
			
			// Button submit (new)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "new_cat",
									"class"		=> "change",
									"value"		=> "{s_button:newcat}",
									"icon"		=> "ok"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="new_cat" value="{s_button:newcat}" />' . PHP_EOL . 
							'<input type="hidden" name="oldParentCat" value="" />' . PHP_EOL .
							parent::getTokenInput() . 
							'</li>' . PHP_EOL . 
							'</ul>' . PHP_EOL . 
							'</form>' . PHP_EOL;
		}
		
		// Andernfalls Kategorie bearbeiten
		else {
			$output		 .=	'<ul>' . PHP_EOL . 
							'<li class="submit change">' . PHP_EOL;
			
			// Button submit (new)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "submit_edit",
									"class"		=> "change",
									"value"		=> "{s_button:takechange}",
									"icon"		=> "ok"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="submit_edit" value="{s_button:takechange}" />' . PHP_EOL . 
							'<input type="hidden" name="edit_cat" value="' . $this->catID . '" />' . PHP_EOL .
							parent::getTokenInput() . 
							'</li>' . PHP_EOL .
							'</ul>' . PHP_EOL .
							'</form>' . PHP_EOL .
							'<ul>' . PHP_EOL . 
							'<li class="submit back">' . PHP_EOL .
							$this->getAddCatButton("left") . 
							'<br class="clearfloat" />' . PHP_EOL .
							'</li>' . PHP_EOL .
							'</ul>' . PHP_EOL;
		}
		
		$output		 .=		'</div>' . PHP_EOL;
		
		return $output;
	
	}
	

	
	/**
	 * Section: EditData
	 * 
	 * @param	array		$userID		user ID
	 * @access	protected
	 * @return	object
	 */
	protected function getDataEditSection($userID)
	{

		$output		= "";
		
		// Falls eine einzelner Datensatz zum Bearbeiten in der Session gespeichert ist
		// Bearbeitungsberechtigung prüfen
		if(	$this->editData
		&& ($userID == $this->authorID
		   || $this->editorLog == true)
		)
			$this->editAccess	= true;			


		// Section: Datensätze bearbeiten/auflisten
		$output		 .= 	'<h3' . (!isset($this->successChange) ? ' id="cfm"' : '') . ' class="cc-h3 switchToggle actionHeader';
		
		if($this->showCats
		|| $this->newData
		)
			$output		 .=' hideNext';
			
		$output		 .=	'">{s_header:edit'.parent::$type.'} ' . (isset($this->editID) && $this->editID != "" ? '(#' . $this->editID . ')' : '') . PHP_EOL;
		
		// Backtolist Button
		if($this->editAccess) {
			
			$output		 .=	'<form action="' . $this->formAction . '" method="post" class="left" data-getcontent="fullpage">' . PHP_EOL;
			
			$output		 .=	'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button backtolist
			$btnDefs	= array(	"type"		=> "submit",
									"value"		=> '{s_button:'.parent::$type.'list}',
									"class"		=> 'button-icon-only',
									"text"		=> '',
									"title"		=> '{s_button:'.parent::$type.'list}',
									"attr"		=> 'data-ajaxform="true" data-check="changes"',
									"icon"		=> "backtolist"
								);
			
			$output		 .=	parent::getButton($btnDefs);
			
			$output		 .=	'<input name="list_cat" type="hidden" value="'.$this->dataCat.'" />' . PHP_EOL .
							'</span>' . PHP_EOL .
							'</form>' . PHP_EOL;
		}

		$output		 .=		'</h3>' . PHP_EOL .
							'<div class="editDataEntrySection adminBox">' . PHP_EOL;
		
		
		// Falls Bearbeitungsberechtigung für einen Datensatz besteht
		if($this->editAccess) {
			
			$output		 .=	$this->getEditEntryForm();
			$output		 .=	'</div>' . PHP_EOL;
			return $output;

		}		
		
		
		// Datensätze auflisten
		// ControlBar
		$output		   .= $this->getDataListControlBar();
	
		
		// Falls noch keine Kategorie zum Auflisten von Datensätzen ausgewählt wurde
		if(!$this->listData)
			$output	   .= '<p class="notice error">{s_text:no'.parent::$type.'}' . (isset($this->editID) && $this->editID != "" ? ' {s_common:or} {s_text:noaccess}' : '') . '.</p>' . PHP_EOL;
		
		
		// Daten auflisten
		if($this->listData) {
			
			$output		   .= $this->getDataList($userID);
			
		}
		
		$output .= 	'</div>' . PHP_EOL; // close editDataEntrySection
		
		return $output;
	
	}
	

	
	/**
	 * Formular: Dateneintrag bearbeiten
	 * 
	 * @access	protected
	 * @return	string
	 */
	protected function getEditEntryForm($formType = "")
	{
		
		$output		 = "";
		
		// DataHeader
		if($formType != "short")
			$output	 .=	$this->getEditEntryActionBar();
		
						
		// Edit form
		$output		 .=	'<form action="' . $this->formAction . '" method="post" id="adminfm3" enctype="multipart/form-data" accept-charset="UTF-8">' . PHP_EOL .
						'<ul class="framedItems">' . PHP_EOL;

		
		// Header
		$output		 .= 	'<li>' . PHP_EOL .
							'<label>{s_label:'.parent::$type.'header}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
							
		if(isset($this->wrongInput['header']))
			$output		 .=	'<p class="notice">' . $this->wrongInput['header'] . '</p>' . PHP_EOL;
							
		$output		 .=	'<input type="text" name="news_header" value="' . (isset($this->dataHeader) && $this->dataHeader != '' ? htmlspecialchars($this->dataHeader) : '') . '" maxlength="300" />' . PHP_EOL;
		
		$output		 .=	'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL;

						
		// Newskategorien Auswahl
		$output		 .=	'<li>' . PHP_EOL .
						'<div class="catSelection leftBox">' . PHP_EOL .
						'<label>{s_label:'.parent::$type.'cat}</label>' . PHP_EOL;
						
		if(isset($this->wrongInput['cat']))
			$output		 .=	'<p class="notice">' . $this->wrongInput['cat'] . '</p>' . PHP_EOL;
							
		$output		 .=	'<select name="news_cat">' . PHP_EOL;
		
		
		$c = 0;
		
		foreach($this->existCats as $cat) {
			
			$output		 .=	'<option' . ($cat['parent_cat'] == 0 ? ' class="parentCat"' : '') . ' value="' . $cat['cat_id'] . '"';
			
			if(isset($this->dataCat) && $cat['cat_id'] == $this->dataCat)
				$output		 .= ' selected="selected"';
				
			$output		 .= '>' . (!empty($this->catLevelArray[$c]) ? $this->catLevelArray[$c] : '') . $cat['category_' . $this->editLang] . '</option>' . PHP_EOL;
			
			$c++;
		}
							
		$output		 .= 	'</select>' . PHP_EOL;
		$output		 .= 	'</div>' . PHP_EOL;

		
		// Tags
		$output		 .=	'<div class="tagSelection rightBox">' . PHP_EOL .
						'<label>Tags<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<input type="text" name="news_tags" value="' . (isset($this->dataTags) && $this->dataTags != '' ? htmlspecialchars($this->dataTags) : '') . '" class="dataTags tags-' . parent::$type . '" data-type="' . parent::$type . '" autocomplete="off" maxlength="512" />' . PHP_EOL . 
						'</div>' . PHP_EOL;
		
		$output		 .=	'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL;

		
		
		// Data Event
		// dispatch event get_data_fields 
		$this->o_dispatcher->dispatch('data.get_data_fields', $this->o_extendDataEvent);
		
		$output		 .= $this->o_extendDataEvent->getOutput(true);
		
		
		$output		 .=	'</ul>' . PHP_EOL;
		
		
		// if short form type
		if($formType === "short") {
			$output		.=	self::getEditDataSubmit(1, false);
			$output		.= '</form>' . PHP_EOL;
			return $output;
		}
		
		
		$output		 .=	'<h4 class="cc-h4 marginTop toggle">{s_option:' . parent::$type . '} - {s_label:objects}</h4>' . PHP_EOL;
		
		// Datenobjekte als sortierbare Liste einbinden
		$output		 .=	'<ul id="sortableObjects" class="dataObjectList subList sortable-container framedItems" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=sortobjects&mod=' . parent::$type . '&cat=' . $this->dataCat. '&id=' . $this->editID . '&lastobject=' . $this->objectNumber . '">' . PHP_EOL;
		
		
		// Daten-Objekte einbinden
		$output		 .=	$this->objectOutput;

		
		// Button für weitere Objekte
		$output		 .=	'<li class="newobj cc-groupitem-add buttonPanel">' . "\n";

		// Button new
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'newobj button-icon-only button-small',
								"value"		=> "",
								"title"		=> "{s_title:newobject}",
								"attr"		=> 'data-action="adddataobject" data-url="'.SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=newobj&mod=' . parent::$type . '&cat=' . $this->dataCat . '&id=' . $this->editID . '&newobj='.($this->objectNumber +1).'"',
								"icon"		=> "new",
								"iconclass"	=> "namePlaceholder-icon mceIcon"
							);
			
		$output		 .=	parent::getButton($btnDefs);
		
		$output		 .=	'</li>' . PHP_EOL;

		$output		 .=	'</ul>' . PHP_EOL;

		
		// Submit Buttons
		$output		 .=	$this->getEditDataSubmit($this->dataStatus);
		
		$output		 .=	'</form>' . PHP_EOL;
		
		$output		 .=	self::getBackToListButtons($this->dataCat);

		return $output;
	
	}	
	

	
	/**
	 * Formular: Edit action bar
	 * 
	 * @access	protected
	 * @return	string
	 */
	protected function getEditEntryActionBar()
	{
		
		// DataHeader
		$output		 =	'<div class="borderBox">' . PHP_EOL .
						'<div class="listEntryHeader actionHeader">' . PHP_EOL .
						'<span class="editButtons-panel panel-left">' . PHP_EOL .
						'<span class="switchIcons">' . PHP_EOL;
		
		// Button publish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:nopublish'.parent::$type.'}',
								"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=' . parent::$type . '&action=pubentry&set=0&id=' . $this->editID . '" data-publish="0"' . ($this->dataStatus == 0 ? ' style="display:none;"' : ''),
								"icon"		=> "publish"
							);
			
		$output .=	parent::getButton($btnDefs);

		// Button unpublish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:publish'.parent::$type.'}',
								"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=' . parent::$type . '&action=pubentry&set=1&id=' . $this->editID . '" data-publish="1"' . ($this->dataStatus == 1 ? ' style="display:none;"' : ''),
								"icon"		=> "unpublish"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output		 .=	'</span>' . PHP_EOL .
						'</span>' . PHP_EOL;


						
		
		// Header fields (e.g. date)
		// dispatch event get_dataheader_fields
		$this->o_dispatcher->dispatch('data.get_dataheader_fields', $this->o_extendDataEvent);
		
		$output		 .=	$this->o_extendDataEvent->getOutput(true);


		
		// Author
		$output		 .=	'<span class="dataAuthor" title="{s_option:author}">' . $this->getEditableAuthor($this->authorName, $this->authorID, $this->editID) . '</span>' . PHP_EOL;
		
		// EditButtons
		$output		 .=	'<span class="dataCalls editButtons-panel panel-left" title="' . $this->dataCalls . ' {s_title:calls}">' . PHP_EOL .
						parent::getIcon("preview", "left-icon") .
						'<span class="{t_class:badge}">&nbsp;' . $this->dataCalls . '</span>' . PHP_EOL .
						'</span>' . PHP_EOL;
		
		$editButtonsPanel		= '<span class="editButtons-panel">' . PHP_EOL;
		
		if($this->dataRating == 1) {
			
			require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.Rating.php"); // Klasse einbinden
		
			$o_rating	= new Rating($this->DB);
			$this->mergeHeadCodeArrays($o_rating);
			
			$output		 .=	'<span class="editButtons-panel panel-left">' . PHP_EOL;
			$output		 .=	$o_rating->getStarRater(parent::$type, $this->dataCat, $this->editID, false, false, false) . PHP_EOL;
			$output		 .=	'</span>' . PHP_EOL;
			
			$output		 .=	$editButtonsPanel;

			// Button reset
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'delcon resetVotes button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:resvotes}',
									"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/rating.php?page=admin&action=res&mod=' . parent::$type . '&cat=' . $this->dataCat . '&id=' . $this->editID . '"',
									"icon"		=> "reset"
								);
				
			$output .=	parent::getButton($btnDefs);
			
		}
		else
			$output		 .=	$editButtonsPanel;

		// Button copy
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'copydata editcon button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:copy'.parent::$type.'}',
								"attr"		=> 'data-action="editcon" data-actiontype="copydata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=copy&mod=' . parent::$type . '&cat=' . $this->dataCat . '&id=' . $this->editID . '"',
								"icon"		=> "copy"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'deldata button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:del'.parent::$type.'}',
								"attr"		=> 'data-action="deldata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=del&mod=' . parent::$type . '&cat=' . $this->dataCat . '&id=' . $this->editID . '&sortid=' . $this->sortIDOld . '&red=' . parent::$type . '&redext=admin"',
								"icon"		=> "delete"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output		 .=	'</span>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL .
						'</div>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		return $output;
	
	}	

	
	/**
	 * Edit data submit button
	 * 
	 * @param	int		$dataStatus
	 * @access	protected
	 * @return	string
	 */
	protected function getEditDataSubmit($dataStatus, $fix = true)
	{
	
		// Submit Buttons
		$output		 =	'<ul>' . PHP_EOL .
						'<li class="submit change' . (!$fix ? ' buttonpanel-nofix' : '') . '">' . PHP_EOL;
		
		// Button submit
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "edit_news",
								"class"		=> "change left",
								"value"		=> "{s_button:takechange}",
								"icon"		=> "ok"
							);
		
		$output		.=	parent::getButton($btnDefs);

		
		if($dataStatus == 0) {
		
			// Button submit (publish)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "publish_news",
									"class"		=> "publish right",
									"value"		=> '{s_button:publish'.parent::$type.'}',
									"icon"		=> "warning"
								);
			
			$output		 .=	parent::getButton($btnDefs);
		}
		else
			$output		 .=	'<input type="hidden" name="edit_news" value="{s_button:takechange}" />' . PHP_EOL;


		#$output		 .=	parent::getTokenInput();
		
		
		$output		 .=	'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL .
						'</ul>' . PHP_EOL;
		
		return $output;
	
	}	

	
	/**
	 * ControlBar für Auflistung von Datensätzen
	 * 
	 * @access	protected
	 * @return	string
	 */
	protected function getDataListControlBar()
	{

		$output		 =	'<div class="controlBar">' . PHP_EOL .
						'<form action="' . $this->formAction . '" method="post">' . PHP_EOL . 
						'<div class="dataCatSelection left"><label>{s_label:'.parent::$type.'cat}</label>' . PHP_EOL .
						'<select name="list_cat" class="listCat" data-action="autosubmit">' . PHP_EOL;
					
		if(!$this->listData || count($this->dataEntriesArr) == 0)
			$output		 .=	'<option value="" disabled="disabled" selected="selected">{s_option:choose}</option>';
			
		$output		 .=	'<option value="all"';
						
		if($this->listData && $this->listCat == "all")
			$output		 .=	' selected="selected"';
				
		$output		 .= 	'>{s_option:all'.parent::$type.'}</option>';

	
		// Kategorien auflisten
		foreach($this->existCatsData as $cat) {
			
			$output		 .=	'<option value="' . $cat['cat_id'] . '"';
			
			if($this->listData && $cat['cat_id'] == $this->listCat) {
				
				$this->listCatName	= $cat['category_' . $this->editLang];
				
				$output	 .=	' selected="selected"';
			}
				
			$output		 .= '>' . $cat['category_' . $this->editLang] . ' (' . ($cat['id'] == NULL ? 0 : $cat['datasets']) . ')</option>' . PHP_EOL;
		
		}
		
		$output		 .= 	'</select></div>' . PHP_EOL;
		
		
		// Sortierungsoptionen einblenden
		if($this->listData) {
		
			$output		 .= 	'<div class="sortOption small left"><label>{s_label:sort}</label>' . PHP_EOL .
								'<select name="sort_param" class="listCat" data-action="autosubmit">' . PHP_EOL;
			
			$sortOptions = array("sortid" => "{s_option:sortid}",
								 "dateasc" => "{s_option:dateasc}",
								 "datedsc" => "{s_option:datedsc}",
								 "nameasc" => "{s_option:nameasc}",
								 "namedsc" => "{s_option:namedsc}",
								 "callsasc" => "{s_option:callsasc}",
								 "callsdsc" => "{s_option:callsdsc}"
								 );
			
			
			// Sortierungsoptionen auflisten
			foreach($sortOptions as $key => $value) {
				
				$output		 .=	'<option value="' . $key . '"';
				
				if($key == $this->sortParam)
					$output		 .=	' selected="selected"';
					
				$output		 .= '>' . $value . '</option>' . PHP_EOL;
			
			}
								
			$output		 .= 	'</select></div>' . PHP_EOL;
			
			// Limit
			$output		 .= 	'<div class="sortOption small left"><label>{s_label:limit}</label>' . PHP_EOL;
		
			$output		 .=		$this->getLimitSelect($this->limitOptions, $this->limit);
			
			$output		 .= 	'</div>' . PHP_EOL;
			
			// Filter Optionen
			$output		 .= 	'<div class="filterOptions cc-table-cell">' . PHP_EOL .
								'<div class="filterOption left"><label for="all">{s_label:all}</label>' . PHP_EOL .
								'<label class="radioBox markBox">' . PHP_EOL .
								'<input type="radio" name="filter_pub" id="all" value="all"' . ($this->pubFilter == "all" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
								'</label>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'<div class="filterOption left"><label for="pub">{s_label:published}</label>' . PHP_EOL .
								'<label class="radioBox markBox">' . PHP_EOL .
								'<input type="radio" name="filter_pub" id="pub" value="pub"' . ($this->pubFilter == "pub" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
								'</label>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'<div class="filterOption left"><label for="unpub">{s_label:unpublished}</label>' . PHP_EOL .
								'<label class="radioBox markBox">' . PHP_EOL .
								'<input type="radio" name="filter_pub" id="unpub" value="unpub"' . ($this->pubFilter == "unpub" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . PHP_EOL .
								'</label>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'</div>' . PHP_EOL;
		
			// Button panel
			$output		.= 	'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button search
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'toggleDataSearch button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:search}',
									"attr"		=> 'data-toggle="ccDataSearch"',
									"icon"		=> "search"
								);
				
			$output .=	parent::getButton($btnDefs);
			
			// Button calendar
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=' . parent::$type . '&calendar=1',
									"class"		=> "showCalendar button-icon-only",
									"text"		=> "",
									"title"		=> '{s_option:calendar' . parent::$type . '} &raquo;',
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "calendar"
								);
				
			$output .=	parent::getButtonLink($btnDefs);
			
			// Button list
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'toggleDataList button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:toggledatalist}',
									"icon"		=> "list"
								);
				
			$output .=	parent::getButton($btnDefs);
		
			$output		 .=	'</span>' . PHP_EOL;
		}		
		
		$output		.=	'</form>' . PHP_EOL;
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Search.php";
		
		$o_search	 = new Search($this->DB, $this->o_lng, SEARCH_TYPE, array(parent::$type));
		$output		.= '<div id="ccDataSearch" class="dataSearch popup-panel-right hide-init ui-tooltip ui-widget ui-corner-all ui-widget-content">' . PHP_EOL;
		$output		.= $o_search->getSearchForm("small");
		$output		.= '</div>' . PHP_EOL;

		$output		.= '</div>' . PHP_EOL;

	
	
		// Falls Gruppenfilter oder Abofilter, Filter löschen Button einfügen
		if((!empty($this->listCat) && $this->listCat != "all")
		|| (!empty($this->pubFilter) && $this->pubFilter != "all")
		) {
	
			$filter	= "";
			
			if(!empty($this->listCat) && $this->listCat != "all")
				$filter	.= '<strong>{s_label:' . parent::$type . 'cat} &quot;' . ($this->listCatName != "" ? $this->listCatName : '#' . $this->listCat) . '&quot;</strong>';
		
			
			if(!empty($this->pubFilter) && $this->pubFilter != "all") {
				$filter .= ($filter != "" ? ' | ' : '');
				$filter	.= '<strong>{s_label:' . ($this->pubFilter == "pub" ? '' : 'un') . 'published}' . '</strong>';
			}
			
			
			$output .=	'<span class="showHiddenListEntries actionBox cc-hint">' . PHP_EOL;

			$output .=	'<form action="'.$this->formAction.'" method="post">' . PHP_EOL;
			
			// Filter icon
			$output .=	'<span class="listIcon">' . PHP_EOL .
						parent::getIcon("filter", "inline-icon") .
						'</span>' . "\n";
						
			$output .=	'{s_label:filter}: ' . $filter;
			
			$output .=	'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button remove filter
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'removeFilter ajaxSubmit button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:removefilter}',
									"icon"		=> "close"
								);
				
			$output .=	parent::getButton($btnDefs);
						
			$output .=	'<input type="hidden" value="all" name="filter_pub">' . PHP_EOL .
						'<input type="hidden" value="all" name="list_cat">' . PHP_EOL .
						'</span>' . PHP_EOL .
						'</form>' . PHP_EOL .
						'</span>' . PHP_EOL;
		}
	
		return $output;
	
	}
	

	
	/**
	 * Dateneinträge auflisten
	 * 
	 * @param	array		$userID		user ID
	 * @access	protected
	 * @return	string
	 */
	protected function getDataList($userID)
	{
	
		$output	= "";
		
		// Falls keine Daten vorhanden sind
		if(count($this->dataEntriesArr) == 0) {
			$output		 .=	'<p class="notice error">{s_notice:noentries}</p>' . PHP_EOL;
			$output		 .=	'<ul><li class="submit back">' . PHP_EOL;
		
			if(is_numeric($this->listCat))			
				$output		 .=	$this->getAddDataToCatButton($this->listCat, "button");
			else
				$output		 .=	$this->getAddDataButton("right");
				
			$output		 .=	'<br class="clearfloat" />' . PHP_EOL;
			$output		 .=	'</li></ul>' . PHP_EOL;
			return $output;
		}
	
		// Pagination Nav
		$this->dataNav = Modules::getPageNav($this->limit, $this->totalRows, $this->startRow, $this->pageNum, $this->queryString, "", false, parent::getLimitForm($this->limitOptions, $this->limit));
		
		if($this->limit > 25)
			$output		 .= 	$this->dataNav;
		
		
		// Daten auflisten
		// Checkbox zur Mehrfachauswahl zum Löschen und Publizieren
		$output		 .=	'<form action="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=' . parent::$type . '&entryid=array&list_cat=' . $this->listCat . '&action=" method="post">' . PHP_EOL;
		
	
		// ActionBox
		$output		 .=	$this->getDataActionBox();
	
	
		// Dateneinträge
		$output		 .=	'<div class="dataEntries">' . PHP_EOL;
		
		
		require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.Rating.php"); // Klasse einbinden
		
		$o_rating	= new Rating($this->DB);
		$this->mergeHeadCodeArrays($o_rating);
		
		$k = 1;
		$o = 1;
	
		// sortable list
		$output		 .=	'<ul class="dataList sortableData' . (!$this->sortable ? ' disabled' : '') . (isset($GLOBALS['_COOKIE']['dataList']) && $GLOBALS['_COOKIE']['dataList'] == 2 ? ' collapsed' : '') . '"';			
		
		// Sortierungsoption durch sortableData-Objekt einbinden,
		// falls nicht alle Kategorien aufgelistet werden und Sortierung = "eigene"
		if($this->sortable)
			$output		 .=	' id="sortableData" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=sort&mod=' . parent::$type . '&cat=' . $this->listCat . '" class="dataList sortable-container' . (isset($GLOBALS['_COOKIE']['dataList']) && $GLOBALS['_COOKIE']['dataList'] == 2 ? ' collapsed' : '') . '">' . PHP_EOL;			
		else
			$output		 .=	'>' . PHP_EOL;
		
		
		// Einträge auflisten
		foreach($this->dataEntriesArr as $dataEntry) {
			
			if($userID == $dataEntry['author_id'] || $this->editorLog == true) {
				
				$this->objectCon[$o]	= $this->getObjectArray($dataEntry['object1']);
				$objParams				= $this->getObjectParams($this->objectCon[$o]);
				$this->authorName		= $dataEntry['author_name'] != "" ? $dataEntry['author_name'] : '{s_common:unknown}';
				$this->authorID			= $dataEntry['userid'] != "" ? $dataEntry['userid'] : 0;
				
				// Pfad zur Bilddatei
				// Falls files-Ordner, den Pfad ermitteln
				if(strpos($objParams[0], "/") !== false) {
					$filesImgArr		= explode("/", $objParams[0]);
					$objParams[0]		= array_pop($filesImgArr);					
					$imgPath			= CC_FILES_FOLDER . '/' . implode("/", $filesImgArr) . '/';
					$docPath			= $imgPath;
					$audioPath			= $imgPath;
				}
				elseif(strpos($objParams[0], ">") !== false) {
					$filesImgArr		= explode(">", $objParams[0]);
					$objParams[0]		= array_pop($filesImgArr);					
					$imgPath			= CC_FILES_FOLDER . "/" . implode("/", $filesImgArr) . "/";
					$docPath			= $imgPath;
					$audioPath			= $imgPath;
				}
				else {
					$imgPath	= CC_IMAGE_FOLDER . "/";
					$docPath	= CC_DOC_FOLDER . "/";
					$audioPath	= CC_AUDIO_FOLDER . "/";
				}
				
				
				// Sprachenfilter
				if(!isset($objParams[1]))
					$objParams[1] = "";
				elseif(strpos($objParams[1], "{") === 0) {
					$objectConLang	= (array)json_decode($objParams[1]);						
					$objParams[1]	= isset($objectConLang[$this->lang]) ? $objectConLang[$this->lang] : '';
				}
				
				$output		 .=	'<div id="dataid-' . $dataEntry['id'] . '" class="listEntry' . ($k % 2 ? '' : ' alternate') . '" data-id="' . $dataEntry['id'] . '" data-sortid="' . $dataEntry['sortid'] . '" data-sortidold="' . $dataEntry['sortid'] . '" data-menu="context" data-target="contextmenu-b-' . $k . ',contextmenu-a-' . $k . '">' . PHP_EOL .
								'<div class="listEntryHeader">' . PHP_EOL;
						
				$output		 .=	'<label class="markBox">' . 
								'<input type="checkbox" name="entryNr[' . $k . ']" class="addVal" />' .
								'<input type="hidden" name="entryID[' . $k . ']" value="' . $dataEntry['id'] . '" class="getVal" />' .
								'</label>';
								
				$output		 .=	'<span class="editButtons-panel panel-left">' . PHP_EOL;
				
				$output		 .=	'<span class="switchIcons" data-id="contextmenu-b-' . $k . '">' . PHP_EOL;
		
				// Button publish
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'button-icon-only',
										"value"		=> "",
										"title"		=> '{s_title:nopublish'.parent::$type.'}',
										"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=' . parent::$type . '&action=pubentry&set=0&id=' . $dataEntry['id'] . '" data-publish="0" data-menuitem="true" data-id="item-id-' . $k . '"' . ($dataEntry['published'] == 0 ? ' style="display:none;"' : ''),
										"icon"		=> "publish"
									);
					
				$output .=	parent::getButton($btnDefs);

				// Button unpublish
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:publish'.parent::$type.'}',
										"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=' . parent::$type . '&action=pubentry&set=1&id=' . $dataEntry['id'] . '" data-publish="1" data-menuitem="true" data-id="item-id-' . $k . '" data-menutitle="{s_title:publish'.parent::$type.'}"' . ($dataEntry['published'] == 1 ? ' style="display:none;"' : ''),
										"icon"		=> "unpublish"
									);
					
				$output .=	parent::getButton($btnDefs);
				
				$output		 .=	'</span>' . PHP_EOL .
								'</span>' . PHP_EOL;
				
				
				
				// Header fields (e.g. date)
				$this->o_extendDataEvent->dataEntry	= $dataEntry;
				
				// dispatch event get_dataheader_listfields
				$this->o_dispatcher->dispatch('data.get_dataheader_listfields', $this->o_extendDataEvent);
				
				$output		 .=	$this->o_extendDataEvent->getOutput(true);


				
				// Author
				$output		 .=	'<span class="dataAuthor" title="{s_option:author}">' . $this->getEditableAuthor($this->authorName, $this->authorID, $dataEntry['id']) . '</span>' . PHP_EOL;
					
				// EditButtons
				$output		 .=	'<span class="dataCalls editButtons-panel panel-left" title="' . $dataEntry['calls'] . ' {s_title:calls}">' . PHP_EOL .
								parent::getIcon("preview", "left-icon") .
								'<span class="{t_class:badge}">&nbsp;' . $dataEntry['calls'] . '</span>' . PHP_EOL .
								'</span>' . PHP_EOL;
				
				
				$editButtonsPanel		= '<span class="editButtons-panel" data-id="contextmenu-a-' . $k . '">' . PHP_EOL;
				

				if($dataEntry['rating'] == 1) {
				
					$output		 .=	'<span class="editButtons-panel panel-left">' . PHP_EOL;
					$output		 .=	$o_rating->getStarRater(parent::$type, $dataEntry['cat_id'], $dataEntry['id'], false, false, false) . PHP_EOL;
					$output		 .=	'</span>' . PHP_EOL;
					
					$output		 .=	$editButtonsPanel;
			
					// Button reset
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'delcon resetVotes button-icon-only',
											"text"		=> "",
											"title"		=> '{s_title:resvotes}',
											"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/rating.php?page=admin&action=res&mod=' . parent::$type . '&cat=' . $this->listCat . '&id=' . $dataEntry['id'] . '" data-menuitem="true" data-id="item-id-' . $k . '"',
											"icon"		=> "reset"
										);
						
					$output .=	parent::getButton($btnDefs);
				
				}
				else
					$output		 .=	$editButtonsPanel;									
			
				// Falls eine bestimmte Kat angezeigt wird und mehrere Artikel vorhanden sind, nach oben Button einfügen
				if(count($this->dataEntriesArr) > 1 && $k > 1 && $this->listCat != "all" && $this->sortParam == "sortid") {
			
					// Button sortup
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'sortcon button-icon-only',
											"title"		=> '{s_title:moveup}',
											"attr"		=> 'data-ajax="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=up&mod=' . parent::$type . '&cat=' . $this->listCat . '&id=' . $dataEntry['id'] . '&sortid=' . $dataEntry['sortid'] . '" data-menuitem="true" data-id="item-id-' . $k . '"',
											"icon"		=> "sortup"
										);
					
					$output		.=	parent::getButton($btnDefs);
				}
								
				// Falls eine bestimmte Kat angezeigt wird und mehrere Artikel vorhanden sind, nach unten Button einfügen
				if(count($this->dataEntriesArr) > 1 && $k < count($this->dataEntriesArr) && $this->listCat != "all" && $this->sortParam == "sortid") {
					
					// Button sortdown
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'sortcon button-icon-only',
											"title"		=> '{s_title:movedown}',
											"attr"		=> 'data-ajax="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=down&mod=' . parent::$type . '&cat=' . $this->listCat . '&id=' . $dataEntry['id'] . '&sortid=' . $dataEntry['sortid'] . '" data-menuitem="true" data-id="item-id-' . $k . '"',
											"icon"		=> "sortdown"
										);
					
					$output		.=	parent::getButton($btnDefs);
			
				}

				// Button edit
				$btnDefs	= array(	"type"		=> "submit",
										"class"		=> 'editcon button-icon-only',
										"text"		=> '',
										"title"		=> '{s_title:edit'.parent::$type.'}',
										"attr"		=> 'data-action="editcon" data-actiontype="editcon" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=edit&mod=' . parent::$type . '&id=' . $dataEntry['id'] . '" data-menuitem="true" data-id="item-id-' . $k . '"',
										"icon"		=> "edit"
									);
				
				$output		.=	parent::getButton($btnDefs);
				
				// Button copy
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'copydata editcon button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:copy'.parent::$type.'}',
										"attr"		=> 'data-action="editcon" data-actiontype="copydata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=copy&mod=' . parent::$type . '&cat=' . $dataEntry['cat_id'] . '&id=' . $dataEntry['id'] . '" data-menuitem="true" data-id="item-id-' . $k . '"',
										"icon"		=> "copy"
									);
					
				$output .=	parent::getButton($btnDefs);
		
				// Button delete
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'deldata button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:del'.parent::$type.'}',
										"attr"		=> 'data-action="deldata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=del&mod=' . parent::$type . '&cat=' . $this->listCat . '&id=' . $dataEntry['id'] . '&sortid=' . $dataEntry['sortid'] . '" data-menuitem="true" data-id="item-id-' . $k . '"',
										"icon"		=> "delete"
									);
					
				$output .=	parent::getButton($btnDefs);
				
				$output		 .=	'</span>' . PHP_EOL .
								'</div>' . PHP_EOL;
				
				
				// Icon attachment
				$oIcons			= "";
				$iconAttach		=	"";
				$oLink			= SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=edit&mod=' . parent::$type . '&id=' . $dataEntry['id'] . '#object-';
				
				if(!empty($this->objectCon[$o]["type"])) {
				
					$oIcons		.=  '<a href="' . $oLink . $o . '">' . parent::getIcon($this->objectCon[$o]["type"], "inline-icon") . '</a>';
					$iconAttach	=	parent::getIcon("attachment", "inline-icon", 'title="{s_label:objects}"') . ' | ';
				}
				
				// Icon featured
				$iconFeatured	=	"";
				
				if(!empty($dataEntry['featured'])) {
				
					$iconFeatured	=	parent::getIcon("pushpin", "inline-icon", 'title="{s_label:featured}"') . ' | ';
				}
				
				// Abgelaufene Termine markieren
				$pastDate		=	"";
				
				if(parent::$type == "planner" && Modules::getTimestamp($dataEntry['date_end'], 0, 0, 0, "-", $dataEntry['time_end']) < time())
					$pastDate	= '<span class="past">{s_text:past}</span>' . PHP_EOL;
				
				// listEntryHeader
				$output		 .=	'<h4 class="cc-h4 dataListHeader toggle"><strong title="{s_label:' . parent::$type . 'header}">' . $dataEntry['header_' . $this->editLang] . '&nbsp;</strong>' .
								'<span class="right">' .
								$pastDate .
								$iconFeatured .
								$iconAttach .
								($this->listCat == "all" ? ' <i title="{s_label:' . parent::$type . 'cat}">('.$dataEntry['category_' . $this->editLang].')</i> | ' : '') .
								'#' . $dataEntry['id'] . PHP_EOL .
								'</span>' .
								'</h4>' . PHP_EOL;
				
				// listEntryContent
				$output		 .=	'<div class="listEntryContent">' . PHP_EOL;

				// list object				
				$output		 .=	'<div class="attachment">' . PHP_EOL .
								'<p class="type">{s_label:object}-1</p>' . PHP_EOL;
				
				$output		 .=	'<div class="listObject">' . PHP_EOL;
				
				// Make object type icon list
				$oKey	= 2;
				
				while(!empty($dataEntry['object' . $oKey])) {
					
					$this->objectCon[$oKey]	= $this->getObjectArray($dataEntry['object' . $oKey]);
					$oIcons	.= '<a href="' . $oLink . $oKey . '">' . parent::getIcon($this->objectCon[$oKey]["type"], "inline-icon") . '</a>';
					$oKey++;
				}
				
				// Image
				if($this->objectCon[$o]["type"] == "img"
				|| $this->objectCon[$o][$this->editLang] == ""
				) {
				
					if(isset($objParams[0]) && $objParams[0] != ""
					&& file_exists(PROJECT_DOC_ROOT . '/' . $imgPath . 'thumbs/' . htmlspecialchars($objParams[0]))
					) {
						$thumbSrc	= $imgPath . 'thumbs/' . htmlspecialchars($objParams[0]);
						$imgSrc		= $imgPath . htmlspecialchars($objParams[0]);
					}
					else {
						$thumbSrc	= 'system/themes/' . ADMIN_THEME . '/img/noimage.png';
						$imgSrc		= $thumbSrc;
					}
					
					$output		 .=	'<div class="previewBox img">' . PHP_EOL .
									'<img src="' . PROJECT_HTTP_ROOT . '/' . $thumbSrc . '" alt="' . (isset($objParams[1]) && $objParams[1] != "" ? htmlspecialchars($objParams[1]) : '') . '" title="' . (isset($objParams[1]) && $objParams[1] != "" ? htmlspecialchars($objParams[1]) : 'no image') . '" class="preview" data-img-src="' . PROJECT_HTTP_ROOT . '/' . $imgSrc . '?afd" />' . PHP_EOL .
									'</div>' . PHP_EOL;
				}
				
				// Gallery
				elseif($this->objectCon[$o]["type"] == "gallery")
					$output		 .=	'<div class="listObjectDetails">{s_text:gallery}<br />' . htmlspecialchars($objParams[0]) . '<br />(' . htmlspecialchars($objParams[1]) . ')</div>' . PHP_EOL;
								
				// Doc
				elseif($this->objectCon[$o]["type"] == "doc")
					$output		 .=	'<div class="listObjectDetails">{s_text:doc}<br />' . htmlspecialchars($objParams[0]) . '<br />(' . htmlspecialchars($objParams[1]) . ')</div>' . PHP_EOL;
								
				// Audio
				elseif($this->objectCon[$o]["type"] == "audio") {
					
					require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.AudioContent.php"; // AudioContent-Klasse einbinden
					
					if(strpos($objParams[0], "/") !== false)
						$aFolder	= CC_FILES_FOLDER . "/";
					else
						$aFolder	= CC_AUDIO_FOLDER . "/";
					
					$output		 .=	'<div class="listObjectDetails">{s_label:audio}<br />' . htmlspecialchars($objParams[0]) . '<br />' . PHP_EOL .
									AudioContent::getHTML5Audio(PROJECT_HTTP_ROOT . '/' . $aFolder . '/' . $objParams[0], "audio-object-".$k) .
									'</div>' . PHP_EOL;
				}
				
				// Video
				elseif($this->objectCon[$o]["type"] == "video") {
					$output		 .=	'<div class="listObjectDetails">{s_label:video}<br />' . htmlspecialchars($objParams[0]) . '<br />(' . htmlspecialchars($objParams[1]) . ')</div>' . PHP_EOL;
				}
				
				// Html
				elseif($this->objectCon[$o]["type"] == "html")
					$output		 .=	'<div class="listObjectDetails"><br />&lt;HTML&gt;</div>' . PHP_EOL;
				
				$output		 .=	'</div>' . PHP_EOL;
				$output		 .=	$oIcons;
				$output		 .=	'</div>' . PHP_EOL;

				
				$output		 .=	'<div class="dataDetails">' . PHP_EOL .
								'<p class="dataListTeaser">' . (isset($dataEntry['teaser_' . $this->editLang]) && $dataEntry['teaser_' . $this->editLang] != "" ? htmlspecialchars(substr(strip_tags($dataEntry['teaser_' . $this->editLang]), 0, 130)) . '...' : (parent::$type != "articles" ? htmlspecialchars(strip_tags(substr($dataEntry['text_' . $this->editLang], 0, 130))) : '')) . '</p>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL;
				
		
				// Data Event
				$this->o_extendDataEvent->dataEntry	= $dataEntry;
				
				// dispatch event get_data_listattribute 
				$this->o_dispatcher->dispatch('data.get_data_listattribute', $this->o_extendDataEvent);
				
				// Weitere Listenattibute (e.g. Preis bei bestellbaren Artikeln)
				$output		 .=	$this->o_extendDataEvent->getOutput(true);
				
				
				
				// Ggf. Tags einbinden
				if($dataEntry['tags_' . $this->editLang] != "") {
					$entryTags		= explode(",", $dataEntry['tags_' . $this->editLang]);
					$output		 .=	'<div class="{t_class:panelfoot}">' . PHP_EOL;				
					$output		 .=	parent::getIcon('tags');				
					$output		 .=	'<span class="{t_class:label}">' . implode('</span> | <span class="{t_class:label}">', $entryTags) . '</span>' . PHP_EOL;
					$output		 .=	'</div>' . PHP_EOL;				

				}
				
				
				
				// Ggf. Kommentare einbinden
				if(in_array(parent::$type, $GLOBALS['commentTables']) && $dataEntry['comments'] == 1) {
					
					// Comments-Klasse einbinden
					require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";
					
					$comGroup = $this->loggedUserGroup;
						
					$comments = new Comments($this->DB, $this->o_lng, parent::$type, "", ADMIN_HTTP_ROOT . '?task=modules&type=' . parent::$type, $comGroup);
		
					$output		 .=	'<div class="commentBox {t_class:panelfoot}">' . PHP_EOL;				
					$output		 .=	$comments->getComments(parent::$type, $dataEntry['id'], COMMENTS_MAX_ROWS);
					$output		 .=	'</div>' . PHP_EOL;				

				}
				
				$output		 .=	parent::getTokenInput();				
				$output		 .=	'</div></div>' . PHP_EOL;				
				$output		 .=	'</div>' . PHP_EOL;				
				
			} // Ende if user oder editorLog
			
			$k++;
			
		} // Ende foreach
		
		
		$output		 .=	'</ul>' . PHP_EOL;
		$output		 .=	'</div></form>' . PHP_EOL;
			
			
		$output		 .=	$this->dataNav .			
						'<ul><li class="submit back">' . PHP_EOL .
						$this->getAddDataButton("right") .
						'<br class="clearfloat" />' . PHP_EOL .
						'</li></ul>' . PHP_EOL;

		return $output;
	
	}
	

	
	/**
	 * Formular: Neuer Dateneintrag
	 * 
	 * @param	string		$formType (default = '')
	 * @access	protected
	 * @return	string
	 */
	protected function getDataNewSection($formType = "")
	{
	
		// Section: Datensatz neu anlegen
		// Neuer Datensatz
		$output		  =		'<h3 class="cc-h3 switchToggle">{s_header:'.parent::$type.'con}</h3>' . PHP_EOL;
		
		$output		 .= 	'<div class="adminBox">' . PHP_EOL;
		
		$output		 .= 	'<form action="' . $this->formAction . '" method="post" id="dialog-form-data" enctype="multipart/form-data" accept-charset="UTF-8">' . PHP_EOL . 
							'<ul class="framedItems">' . PHP_EOL;
		
		
		// Header
		$output		 .= 	'<li>' . PHP_EOL .
							'<label>{s_label:'.parent::$type.'header}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
						
		if(isset($this->wrongInput['header']))
			$output		 .=	'<p class="notice">' . $this->wrongInput['header'] . '</p>' . PHP_EOL;
							
		$output		 .=		'<input type="text" name="news_header" value="' . (isset($this->dataHeader) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($this->dataHeader) : '') . '" maxlength="300" />' . PHP_EOL;
		
		$output		 .=		'<br class="clearfloat" />' . PHP_EOL .
							'</li>' . PHP_EOL;

								
		// Newskategorien Auswahl
		$output		 .= 	'<li>' . PHP_EOL .
							'<div class="catSelection leftBox">' . PHP_EOL .
							'<label>{s_label:'.parent::$type.'cat}</label>' . PHP_EOL;
							
		if(isset($this->wrongInput['cat']))
			$output		 .=	'<p class="notice">' . $this->wrongInput['cat'] . '</p>' . PHP_EOL;
							
		$output		 .=	'<select name="news_cat">' . PHP_EOL;
		$output		 .=	'<option disabled="disabled" selected="selected" value="">{s_option:choose}</option>';
		
		$c = 0;
		foreach($this->existCats as $cat) { // Newskategorien Optionen auflisten
		
			$output		 .=	'<option' . ($cat['parent_cat'] == 0 ? ' class="parentCat"' : '') . ' value="' . $cat['cat_id'] . '"';
			
			if(isset($GLOBALS['_POST']['news_cat']) && $cat['cat_id'] == $this->dataCat && isset($GLOBALS['_POST']['mod_submit']))
				$output		 .= ' selected="selected"';
				
			$output		 .= '>' . (!empty($this->catLevelArray[$c]) ? $this->catLevelArray[$c] : '') . $cat['category_' . $this->editLang] . '</option>' . PHP_EOL;
		
			$c++;
		}
							
		$output		 .= '</select>' . PHP_EOL;
		$output		 .= '</div>' . PHP_EOL;

		// Tags
		$output		 .= '<div class="tagSelection rightBox">' . PHP_EOL .
						'<label>Tags<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<input type="text" name="news_tags" value="' . (isset($this->dataTags) && isset($GLOBALS['_POST']['mod_submit']) ? htmlspecialchars($this->dataTags) : '') . '" class="dataTags tags-' . parent::$type . '" data-type="' . parent::$type . '" autocomplete="off" maxlength="512" />' . PHP_EOL . 
						'</div>' . PHP_EOL;

		$output		 .=	'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL;


	

		// Data Event
		// dispatch event get_newdata_fields 
		$this->o_dispatcher->dispatch('data.get_newdata_fields', $this->o_extendDataEvent);
		
		// Weitere Listenattibute (e.g. Preis bei bestellbaren Artikeln)
		$output		 .=	$this->o_extendDataEvent->getOutput(true);


	
		$output		.=	'</ul>' . PHP_EOL;
		
		
		// if short form type
		if($formType === "short") {
			$output		.=  self::getNewDataSubmit(false);
			$output		.= '<input type="hidden" name="all_langs" value="on" />';
			$output		.= '</form>' . PHP_EOL;
			$output		.= '</div>' . PHP_EOL;
			return $output;
		}

		// Data objects
		$output		.=	'<h4 class="cc-h4 marginTop toggle">{s_option:' . parent::$type . '} - {s_label:objects}</h4>' . PHP_EOL;
		
		// Datenobjekte
		$output		.=	'<ul class="dataObjectList subList framedItems" title="{s_title:addobjects}">' . PHP_EOL;
		
		
		// Daten-Objekte einbinden
		$output		.=	empty($this->objectOutput) ? $this->getDataObject($this->objectCon[1], 1) : $this->objectOutput;
		
		
		$output		.=	'</ul>' . PHP_EOL;

		$output		.=	self::getNewDataSubmit();
		
		$output		.=	'<ul>' . PHP_EOL .
						'<li class="submit change">' . PHP_EOL;

		// Button submit (new)
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "mod_submit",
								"class"		=> "change",
								"value"		=> '{s_button:add'.parent::$type.'}',
								"icon"		=> "ok"
							);
		
		$output		.=	parent::getButton($btnDefs);
						
		$output		.=	'</li>' . PHP_EOL .
						'<input type="hidden" name="mod_submit" value="{s_button:add'.parent::$type.'}" />' . PHP_EOL .
						parent::getTokenInput() .
						'</ul>' . PHP_EOL; 
	
		$output		.= '</form>' . PHP_EOL;

		$output		.=	self::getBackToListButtons("all");

		$output	 	.= '</div>' . PHP_EOL; // close AdminBox
		
		return $output;
	
	}
	

	
	/**
	 * Neuer Dateneintrag verarbeiten
	 * 
	 * @param	boolean		fix		allow button panel position fixation
	 * @access	protected
	 * @return	object
	 */
	protected function getNewDataSubmit($fix = true)
	{

		$output		=	'<ul>' . PHP_EOL .
						'<li class="submit change' . (!$fix ? ' buttonpanel-nofix' : '') . '">' . PHP_EOL;

		// Button submit (new)
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "mod_submit",
								"class"		=> "change",
								"value"		=> '{s_button:add'.parent::$type.'}',
								"icon"		=> "ok"
							);
		
		$output		.=	parent::getButton($btnDefs);
						
		$output		.=	'</li>' . PHP_EOL .
						'<input type="hidden" name="mod_submit" value="{s_button:add'.parent::$type.'}" />' . PHP_EOL .
						parent::getTokenInput() .
						'</ul>' . PHP_EOL; 
			
		return $output;
	
	}	

	
	/**
	 * Neuer Dateneintrag verarbeiten
	 * 
	 * @access	protected
	 * @return	object
	 */
	protected function processNewDataEntry()
	{

		$this->wrongInput		= array();
		
		
		// Daten Post auswerten
		$this->evalNewDataPost($GLOBALS['_POST']);
		
		
		// Daten Post auswerten
		$this->saveNewDataEntry();
	
	}		
	

	
	/**
	 * Bestehenden Dateneintrag verarbeiten
	 * 
	 * @access	protected
	 * @return	object
	 */
	protected function processEditDataEntry()
	{
	
		// Locking checken
		if($this->checkDataLock())
			
			return $this->adminContent;
		

		
		if($this->noChange)
			
			return false;
		
			
		// Dateneintrag einlesen, falls vorhanden
		$this->getEditEntry();
		
		
		// Datensatz auslesen
		$this->readEditEntry();
		


		// Falls das Formular zum Ändern oder Publizieren einer Nachricht abgeschickt wurde
		if(isset($GLOBALS['_POST']['edit_news']) 
		|| isset($GLOBALS['_POST']['publish_news'])
		) {
		
			// Daten Post auswerten
			$this->evalEditDataPost($GLOBALS['_POST']);
			
		
			// Überprüfen ob Kategorie vorhanden
			$this->catExists	= $this->checkCatExists($this->dataCat);

			
			// Kategorie überprüfen
			if($this->catExists == false)
				$this->wrongInput['cat'] = "{s_notice:catnotexist}";
			else
				$this->dbUpdateStr .=  "`cat_id` = '" . $this->DB->escapeString($this->dataCat) . "',";

			$this->dbUpdateStr .=  "`sort_id` = " . $this->DB->escapeString($this->sortID) . ",";
			
			// Header überprüfen
			if($this->dataHeader == "")
				$this->wrongInput['header'] = "{s_notice:no".parent::$type."head}";

			elseif(strlen($this->dataHeader) > 300)
				$this->wrongInput['header'] = "{s_notice:longhead}";
			
			else
				$this->dbUpdateStr .=  "`header_" . $this->editLang . "` = '" . $this->DB->escapeString($this->dataHeader) . "',";

			// Teaser
			$this->dbUpdateStr .=  "`teaser_" . $this->editLang . "` = '" . $this->DB->escapeString($this->dataTeaser) . "',";

			// Nachrichtentext
			$this->dataTextDB	= "";
			
			if($this->dataText != "") {
				
				// Pfade durch Platzhalter ersetzen
				$rootPH		= "{#root}";
				$rootImgPH	= "{#root}/{#root_img}";
				
				$this->dataTextDB	= preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $this->dataText);
				$this->dataTextDB	= preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $this->dataTextDB);
				
			}
			$this->dbUpdateStr .=  "`text_" . $this->editLang . "` = '" . $this->DB->escapeString($this->dataTextDB) . "',";
			
			
			// Tags
			$this->dbUpdateStr .=  "`tags_" . $this->editLang . "` = '" . $this->DB->escapeString($this->dataTags) . "',";
			
			
			// Featured
			$this->dbUpdateStr .=  "`featured` = " . $this->dataFeatured . ",";


			// Fall keine Fehler
			if(count($this->wrongInput) == 0) {
				
			
				// Änderungen für alle Sprachen übernehmen
				$this->getLangsUpdateStr();

				
				if(isset($GLOBALS['_POST']['publish_news'])) // Falls die Nachricht veröffentlicht werden soll
					$this->dbUpdateStr .=  "published = 1,";
			
			
				// Letztes Komma entfernen
				$this->dbUpdateStr = substr($this->dbUpdateStr, 0, -1);

			
				// Neuer Daten-Eintrag in DB
				$this->updateDataEntry($GLOBALS['_POST']);

				
				// Seite neu laden
				header("Location: " . ADMIN_HTTP_ROOT . "?task=modules&type=" . parent::$type);
				exit;
				
			}

		} // Ende if change or publish
		
		else {
			// Daten-Objekte holen
			for($o = 1; $o <= $this->objectNumber; $o++) {					
			
				$this->objectCon[$o] 	 = $this->getObjectArray($this->editEntry[0]['object'.$o]);
				$this->objectOutput		.= $this->getDataObject($this->objectCon[$o], $o);
			}
		}

	}

	
	
	/**
	 * Neuer Dateneintrag auswerten
	 * 
	 * @param	array		$a_Post		POST-Array
	 * @access	protected
	 * @return	void
	 */
	protected function evalNewDataPost($a_Post)
	{
		
		if(isset($a_Post['news_cat'])
		&& $a_Post['news_cat'] != ""
		)
			$this->dataCat			= $a_Post['news_cat'];
		else {
			$this->dataCat			= "";
			$this->wrongInput['cat']	= "{s_error:choosecat}";
		}
		$this->dataHeader			= isset($a_Post['news_header']) ? $a_Post['news_header'] : '';
		$this->dataTeaser			= isset($a_Post['news_teaser']) ? $a_Post['news_teaser'] : '';
		$this->dataText				= isset($a_Post['news_text']) ? $a_Post['news_text'] : '';
		$this->authorID				= $this->g_Session['userid'];
		$this->authorName			= $this->g_Session['author_name'];
		$this->dataTags				= isset($a_Post['news_tags']) ? $a_Post['news_tags'] : '';
		$this->dataFeatured			= !empty($a_Post['featured']) ? 1 : 0;

		
		// Falls Author leer ist, unbekannt ausgeben
		if($this->authorName == "")
			$this->authorName = "{s_common:unknown}";


		// Data Event
		$this->o_extendDataEvent->a_Post		= $a_Post;
		$this->o_extendDataEvent->dataTeaser	= $this->dataTeaser;
		$this->o_extendDataEvent->dataText		= $this->dataText;
		$this->o_extendDataEvent->dataFeatured	= $this->dataFeatured;
		$this->o_extendDataEvent->editLangFlag 	= $this->editLangFlag;
		
		
		// dispatch event eval_newdata_post 
		$this->o_dispatcher->dispatch('data.eval_newdata_post', $this->o_extendDataEvent);
		
		// get event errors
		$this->wrongInput	= array_merge($this->wrongInput, $this->o_extendDataEvent->wrongInput);
		
		
		// DB strings
		$this->dbInsertStr1 .= $this->o_extendDataEvent->dbInsertStr1;
		$this->dbInsertStr2 .= $this->o_extendDataEvent->dbInsertStr2;

		
		
		// Falls eine Newskategorie gewählt worden war
		if(!empty($this->dataCat)
		&& is_numeric($this->dataCat)
		) {
			
			$maxSortId = $this->DB->query("SELECT MAX(`sort_id`) 
												FROM `$this->dataTableDB` 
												WHERE `cat_id` = " . $this->dataCat . "
												", false);
			
			$this->sortID = $maxSortId[0]['MAX(`sort_id`)'];
			if($this->sortID == NULL || $this->sortID == false)
				$this->sortID = 1;
			else
				$this->sortID++;
			
			if(DATA_PUBLISH_DELAY == false)
				$this->dataPubState = 1;
			else
				$this->dataPubState = 0;
			
			$this->dbInsertStr1 .=	"`author_id`," .
									"`sort_id`," .
									"`published`," .
									"`tags_" . $this->editLang . "`,";
			
			$this->dbInsertStr2 .=	"'" . $this->DB->escapeString($this->authorID) . "'," .
									$this->sortID . "," .
									$this->dataPubState . "," .
									"'" . $this->DB->escapeString($this->dataTags) . "',";
		}
		
		
		// Falls Medien-Dateiobjekt
		if((isset($a_Post['add_img'][1]) && $a_Post['add_img'][1] == "on") ||
		   (isset($a_Post['add_doc'][1]) && $a_Post['add_doc'][1] == "on") ||
		   (isset($a_Post['add_audio'][1]) && $a_Post['add_audio'][1] == "on") ||
		   (isset($a_Post['add_video'][1]) && $a_Post['add_video'][1] == "on")
		  ) {
			
			if(isset($a_Post['add_img'][1]))
				$this->objectCon[1]["type"] = "img";
			elseif(isset($a_Post['add_doc'][1]))
				$this->objectCon[1]["type"] = "doc";
			elseif(isset($a_Post['add_audio'][1]))
				$this->objectCon[1]["type"] = "audio";
			elseif(isset($a_Post['add_video'][1]))
				$this->objectCon[1]["type"] = "video";
			
		} // Ende if add_img
		
		
		// Falls Galerie-Objekt
		elseif(isset($a_Post['add_gall'][1]) && $a_Post['add_gall'][1] == "on") {
								   
			$this->objectCon[1]["type"]	= "gallery";
			
		} // Ende if add_gall
		
		
		// Falls HTML-Objekt
		elseif(isset($a_Post['add_html'][1]) && $a_Post['add_html'][1] == "on") {
								   
			$this->objectCon[1]["type"] = "html";
				
		} // Ende if add_html

	}		
	

	
	/**
	 * Neuer Dateneintrag in DB
	 * 
	 * @access	protected
	 * @return	void
	 */
	protected function saveNewDataEntry()
	{		
	
		// insert str
		$this->dbInsertStr1 .=	"`object1`," .
								"`object2`," .
								"`object3`,";


		// Daten-Objekt(1) holen (beinhaltet updateStr)
		$this->objectOutput	=	$this->getDataObject($this->objectCon[1], 1);

		// db Strings
		$this->dbInsertStr2	.= "'" . $this->objUpdateStr . "',"; // Object 1
		$this->dbInsertStr2 .= "'','',"; // Objects 2 + 3
		
		
		// Überprüfen ob Kategorie vorhanden
		$this->catExists	= $this->checkCatExists($this->dataCat);

		
		// Kategorie überprüfen
		if($this->catExists == false)
			$this->wrongInput['cat'] = "{s_notice:catnotexist}";
		else {
			$this->dbInsertStr1 .=	"`cat_id`,";
			$this->dbInsertStr2 .=  "'" . $this->DB->escapeString($this->dataCat) . "',";
		}

		// Header überprüfen
		if($this->dataHeader == "")
			$this->wrongInput['header'] = "{s_notice:no".parent::$type."head}";

		elseif(strlen($this->dataHeader) > 300)
			$this->wrongInput['header'] = "{s_notice:longhead}";
		
		else {
			$this->dbInsertStr1 .=	"`header_" . $this->editLang . "`,";
			$this->dbInsertStr2 .=  "'" . $this->DB->escapeString($this->dataHeader) . "',";
		}

		// Teaser
		$this->dbInsertStr1 .=	"`teaser_" . $this->editLang . "`,";
		$this->dbInsertStr2 .=  "'" . $this->DB->escapeString($this->dataTeaser) . "',";

		// Nachrichtentext
		$this->dataTextDB	= "";
		
		if($this->dataText != "") {
				
			// Pfade durch Platzhalter ersetzen
			$rootPH		= "{#root}";
			$rootImgPH	= "{#root}/{#root_img}";
			
			$this->dataTextDB	= preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $this->dataText);
			$this->dataTextDB	= preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $this->dataTextDB);
		}
		$this->dbInsertStr1 .=	"`text_" . $this->editLang . "`,";
		$this->dbInsertStr2 .=  "'" . $this->DB->escapeString($this->dataTextDB) . "',";

		
		// Falls keine Fehler
		if(count($this->wrongInput) == 0) {
			
			// Änderungen für alle Sprachen übernehmen
			$this->getLangsInsertStr();

			
			// Letztes Komma entfernen
			$this->dbInsertStr1 = substr($this->dbInsertStr1, 0, -1);
			$this->dbInsertStr2 = substr($this->dbInsertStr2, 0, -1);

			
			// Neuer Daten-Eintrag in DB
			$this->insertDataEntry();


			// Seite neu laden (Aufruf des neu angelegten Datensatzes via Session)
			header("Location: " . ADMIN_HTTP_ROOT . "?task=modules&type=" . parent::$type);		
			exit;
		}
	
	}
	

	
	/**
	 * Zu bearbeitenden Datensatz aus db auslesen
	 * 
	 * @access	protected
	 * @return	array
	 */
	protected function getEditEntry()
	{

		// db-Query nach zu bearbeitendem Datensatz
		$this->editEntry = $this->DB->query("SELECT n.*, p.*, n.`sort_id` as sortidold, `author_name`, user.`userid` 
												FROM `$this->dataTableDB` AS n 
												LEFT JOIN `$this->catTableDB` AS p 
													ON n.`cat_id` = p.`cat_id` 
												LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
													ON n.`author_id` = user.`userid` 
												WHERE n.id = $this->editIDdb 
											", false);
		
		#var_dump($this->editEntry);
		
		if(!is_array($this->editEntry)
		|| count($this->editEntry) == 0
		) {

			// Ggf. data_id aus Session löschen
			$this->unsetSessionKey(parent::$type . '_id');
			
			header("Location: " . ADMIN_HTTP_ROOT . "?task=modules&type=parent::$type");
			exit;
		}
	
		return $this->editEntry;
	
	}
	

	
	/**
	 * Zu bearbeitenden Datensatz Parameter zuweisen
	 * 
	 * @access	protected
	 * @return	void
	 */
	protected function readEditEntry()
	{
		
		$this->dataCat		= $this->editEntry[0]['cat_id'];
		$this->dataCatName	= $this->editEntry[0]['category_' . $this->editLang];
		$this->dataCatGroup	= explode(",", $this->editEntry[0]['group']);
		$this->targetPage	= $this->editEntry[0]['target_page'];
		$this->dataID		= $this->editEntry[0]['id'];
		$this->dataHeader	= $this->editEntry[0]['header_' . $this->editLang];
		$this->dataTeaser	= $this->editEntry[0]['teaser_' . $this->editLang];
		$this->dataText		= $this->editEntry[0]['text_' . $this->editLang];
		$this->authorID		= $this->editEntry[0]['author_id'];
		$this->authorName	= $this->editEntry[0]['author_name'];
		$this->dataTags		= $this->editEntry[0]['tags_' . $this->editLang];
		$this->dataFeatured	= $this->editEntry[0]['featured'];
		$this->dataStatus	= $this->editEntry[0]['published'];
		$this->dataCalls	= $this->editEntry[0]['calls'];
		$this->dataRating	= $this->editEntry[0]['rating'];
		$this->sortIDOld	= $this->editEntry[0]['sortidold'];
		$this->dataCatOld	= $this->dataCat;
		$this->dataCatAlias	= Modules::getAlias($this->dataCatName);
		$this->dataAlias	= Modules::getAlias($this->dataHeader);
		$this->objectCon	= array();
		
		$this->currentDataUrl	= $this->getCurrentDataUrl();
		

		// Vorschaulink für Datensatz im Kopfbereich einbinden
		self::$statusNavArray['preview'] .= $this->getPreviewNavItem();
		
		
		
		// Falls Author leer ist, unbekannt ausgeben
		if($this->authorName == "") {
			$this->authorName = "{s_common:unknown}";
			if($this->editorLog)
				$this->authorID	= $this->g_Session['userid']; // Falls Admin oder Editor, Authorschaft übernehmen
		}
		
		// Anzahl an Datenobjekten ermitteln
		for($i = 1; $i <= MAX_DATA_OBJECTS_NUMBER; $i++) {
			if(!isset($this->editEntry[0]['object'.$i])) {
				$this->objectNumber		= $i-1;
				break;
			}
			// Falls noch keine Objektdaten angelegt sind (e.g. bei mehr als drei Objekten), Leerstring
			elseif($this->editEntry[0]['object'.$i] === NULL)
				$this->editEntry[0]['object'.$i] = "";
		}
		
		// Object-Daten auslesen
		for($o = 1; $o <= $this->objectNumber; $o++) {
			
			$this->objectCon[$o] 		= $this->getObjectArray($this->editEntry[0]['object'.$o]);
			
		}
		
		
		// Data Event
		$this->o_extendDataEvent->editEntry			= $this->editEntry;
		$this->o_extendDataEvent->editData			= $this->editData;
		$this->o_extendDataEvent->dataTeaser		= $this->dataTeaser;
		$this->o_extendDataEvent->dataText			= $this->dataText;
		$this->o_extendDataEvent->dataFeatured		= $this->dataFeatured;
		$this->o_extendDataEvent->editLangFlag 		= $this->editLangFlag;
		$this->o_extendDataEvent->currentDataUrl 	= $this->currentDataUrl;
		
		// dispatch event get_data_attributes 
		$this->o_dispatcher->dispatch('data.get_data_attributes', $this->o_extendDataEvent);

		$this->hint	= $this->o_extendDataEvent->hint;
		
	}
	

	
	/**
	 * Daten Edit Post auswerten
	 * 
	 * @param	array		$a_Post		POST-Array
	 * @access	protected
	 * @return	void
	 */
	protected function evalEditDataPost($a_Post)
	{
		
		if(empty($a_Post))
			return false;
		

		// Daten Post auswerten
		// sortID bestimmen
		$this->getDataSortID($a_Post);
		

		$this->dataCat		= $a_Post['news_cat'];
		$this->dataHeader	= $a_Post['news_header'];
		$this->dataTeaser	= $a_Post['news_teaser'];
		$this->dataText		= $a_Post['news_text'];
		$this->dataTags		= $a_Post['news_tags'];
		$this->dataFeatured	= !empty($a_Post['featured']) ? 1 : 0;
		$this->dbUpdateStr	= "";								
		
		
		// Data Event
		$this->o_extendDataEvent->a_Post		= $a_Post;
		$this->o_extendDataEvent->editData		= $this->editData;
		$this->o_extendDataEvent->dataTeaser	= $this->dataTeaser;
		$this->o_extendDataEvent->dataText		= $this->dataText;
		$this->o_extendDataEvent->dataFeatured	= $this->dataFeatured;
		$this->o_extendDataEvent->editLangFlag 	= $this->editLangFlag;
		
		
		// dispatch event eval_data_post
		$this->o_dispatcher->dispatch('data.eval_data_post', $this->o_extendDataEvent);
		
		// get event errors
		$this->wrongInput	= array_merge($this->wrongInput, $this->o_extendDataEvent->wrongInput);
		
		// DB update string
		$this->dbUpdateStr  .= $this->o_extendDataEvent->dbUpdateStr;

		$this->hint	= $this->o_extendDataEvent->hint;


		
		// Data objects
		for($o = 1; $o <= $this->objectNumber; $o++) {
			
			
			// Daten-Objekt-Array
			$this->objectCon[$o]["type"]			= "";
			$this->objectCon[$o][$this->editLang]	= "";

			
			// Medien-Dateiobjekte
			if((isset($a_Post['add_img'][$o]) && $a_Post['add_img'][$o] == "on") ||
			   (isset($a_Post['add_doc'][$o]) && $a_Post['add_doc'][$o] == "on") ||
			   (isset($a_Post['add_audio'][$o]) && $a_Post['add_audio'][$o] == "on") ||
			   (isset($a_Post['add_video'][$o]) && $a_Post['add_video'][$o] == "on")
			) {
			
				if(isset($a_Post['add_img'][$o]))
					$this->objectCon[$o]["type"] = "img";
				elseif(isset($a_Post['add_doc'][$o]))
					$this->objectCon[$o]["type"] = "doc";
				elseif(isset($a_Post['add_audio'][$o]))
					$this->objectCon[$o]["type"] = "audio";
				elseif(isset($a_Post['add_video'][$o]))
					$this->objectCon[$o]["type"] = "video";
				
			} // Ende if add_img
			
			
			// Galerie-Objekte
			elseif(isset($a_Post['add_gall'][$o]) && $a_Post['add_gall'][$o] == "on") {
									   
				$this->objectCon[$o]["type"]	= "gallery";
	
			} // Ende if add_gall
			
			
			// HTML-Objekte
			elseif(isset($a_Post['add_html'][$o]) && $a_Post['add_html'][$o] == "on") {
									   
				$this->objectCon[$o]["type"]	= "html";
								
			} // Ende if add_html


	
			// Daten-Objekte holen
			$this->objectOutput		.=	$this->getDataObject($this->objectCon[$o], $o);


			// db Strings
			$this->dbUpdateStr		.= "`object" . $o . "` = '" . $this->objUpdateStr . "',";


		} // Ende for

	}
	

	
	/**
	 * Methode zum auflisten von Moduldaten-Objekten
	 * 
	 * @param	$dataObject	Daten-Objekt Parameter
	 * @param	$o			Zähler für Objekt-Nr
	 * @access	protected
	 * @return	array
	 */
	protected function getDataObject($dataObject, $o)
	{
	
		$oType 		= $dataObject["type"];
		
		require_once SYSTEM_DOC_ROOT."/inc/adminclasses/class.EditDataObjects.php"; // EditDataObjects-Klasse einbinden

		$o_dataObj	= new EditDataObjects($this->DB, $this->o_lng);

		$o_dataObj->editLang		= $this->editLang;
		$o_dataObj->editId			= $this->editId;
		$o_dataObj->editLangFlag	= $this->editLangFlag;
		$o_dataObj->userGroups		= $this->userGroups;
		$o_dataObj->backendLog		= $this->backendLog;
		
		$output						= $o_dataObj->getObject($dataObject, $o);

		$this->objectCon[$o]		= array_merge($this->objectCon[$o], $o_dataObj->objectCon[$o]);
		
		$this->textAreaCount		= $o_dataObj->textAreaCount;
		$this->objUpdateStr			= $o_dataObj->objUpdateStr;

		if($o_dataObj->busyObject)
			$this->objectUpdateStrings[$o][$oType]	= $o_dataObj->objectUpdateStrings[$o][$oType];

		$this->wrongInput			= array_merge($this->wrongInput, $o_dataObj->wrongInput);
		
		// Head code zusammenführen
		$this->mergeHeadCodeArrays($o_dataObj);
		
		return $output;
	
	}

	
	// getObjectArray
	protected function getObjectArray($objData)
	{

		// legacy object syntax
		if(substr_count($objData, "|") >= 7) {
			$objArr							= explode("|", $objData);
			$type							= array_pop($objArr);
			if($type == "gallery")
				$objArr[$this->editLang]	= implode("/", $objArr);
			else
				$objArr[$this->editLang]	= implode("<>", $objArr);
			$objArr["type"]					= $type;
		}
		else{
			$objArr							= (array)json_decode($objData);
			if(!isset($objArr["type"]))
				$objArr["type"]				= "";
			if(!isset($objArr[$this->editLang]))
				$objArr[$this->editLang]	= "";
		}
	
		return $objArr;
	}

	
	// getObjectParams
	protected function getObjectParams($objData)
	{
	
		if(empty($objData["type"]))
			return array("","");

		// legacy object syntax
		if($objData["type"] == "gallery") {
			$objArr	= explode("/", $objData[$this->editLang]);
		}
		elseif($objData["type"] == "html") {
			$objArr	= array($objData[$this->editLang]);
		}
		else{
			$objArr	= explode("<>", $objData[$this->editLang]);
		}
	
		return $objArr;
	
	}
	

	// getLangsInsertStr
	public function getLangsInsertStr()
	{

		// Änderungen für alle Sprachen übernehmen
		if(isset($GLOBALS['_POST']['all_langs']) && $GLOBALS['_POST']['all_langs'] == "on") { // Falls die Nachricht für alle Sprachen übernommen werden soll
		
			foreach($this->o_lng->installedLangs as $addLang) {
				if($addLang != $this->editLang) {
					$this->dbInsertStr1 .= 	"`header_" . $addLang . "`,`teaser_" . $addLang . "`,`text_" . $addLang . "`,";
					$this->dbInsertStr2 .= 	"'" . $this->DB->escapeString($this->dataHeader) . "'," .
											"'" . $this->DB->escapeString($this->dataTeaser) . "'," .
											"'" . $this->DB->escapeString($this->dataTextDB) . "',";
				}

			}
			
		}
	}


	// getLangsUpdateStr
	public function getLangsUpdateStr()
	{

		// Änderungen für alle Sprachen übernehmen
		if(isset($GLOBALS['_POST']['all_langs']) && $GLOBALS['_POST']['all_langs'] == "on") { // Falls die Nachricht für alle Sprachen übernommen werden soll
		
			foreach($this->o_lng->installedLangs as $addLang) {
				if($addLang != $this->editLang) {
					$this->dbUpdateStr .= 	"`header_" . $addLang . "` = '" . $this->DB->escapeString($this->dataHeader) . "'," .
											"`teaser_" . $addLang . "` = '" . $this->DB->escapeString($this->dataTeaser) . "'," .
											"`text_" . $addLang . "` = '" . $this->DB->escapeString($this->dataTextDB) . "'," .
											"`tags_" . $addLang . "` = '" . $this->DB->escapeString($this->dataTags) . "',";
				}
									
			}
			
		}
	}


	// insertDataEntry
	public function insertDataEntry()
	{
		
		// Neuer Daten-Eintrag in DB
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`");

		
		// Einfügen des neuen Seiteninhaltspunkts
		$insertSQL = $this->DB->query("INSERT INTO `$this->dataTableDB` 
											($this->dbInsertStr1) 
											VALUES ($this->dbInsertStr2)
											");

		#var_dump($insertSQL);

		// db-Query nach gerade angelegter news
		$recentNews = $this->DB->query("SELECT id 
											FROM `$this->dataTableDB` 
											WHERE id = (SELECT MAX(id) FROM `$this->dataTableDB`) 
											", false);
		
		#var_dump($recentNews);
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");



		// Meldungen in Session speichern
		if(DATA_PUBLISH_DELAY == false) { // Falls die Nachricht direkt veröffentlicht werden soll
			$this->setSessionVar('notice', "{s_notice:addpub".parent::$type."}");
		}
		else {
			$this->setSessionVar('notice', "{s_notice:add".parent::$type."}");
			$this->setSessionVar('hint', "<br />{s_notice:mod".parent::$type."}");
			$this->setSessionVar(parent::$type . '_id', $recentNews[0]['id']);
		}
		
	}


	/* updateDataEntry
	 * @param	array		$a_Post		POST-Array
	 */
	public function updateDataEntry($a_Post)
	{
	
		// Neuer Daten-Eintrag in DB

		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->dataTableDB`");

			
		// Einfügen des neuen Seiteninhaltspunkts
		$updateSQL1 = $this->DB->query("UPDATE `$this->dataTableDB` 
											SET $this->dbUpdateStr 
											WHERE id = $this->editIDdb
										");

		#var_dump($updateSQL1);
			
		if(isset($a_Post['news_cat']) && $a_Post['news_cat'] != $this->dataCatOld) {
			
			// sort_id aktualisieren
			$updateSQL2 = $this->DB->query("UPDATE `$this->dataTableDB` 
												SET `sort_id` = `sort_id`-1 
												WHERE `cat_id` = " . $this->dataCatOld . " 
												AND `sort_id` > $this->sortIDOld 
											");
			#var_dump($updateSQL2);
		}

		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		
		// Falls die News veröffentlicht werden soll
		if(isset($a_Post['publish_news'])) {
			
			$this->setSessionVar('notice', "{s_notice:publish".parent::$type."}");
			
			$this->unsetSessionKey('hint');
			$this->unsetSessionKey(parent::$type . '_id');
			
			// ggf. die Datei sitemap.xml aktualisieren, falls öffentlicher Datensatz
			if(in_array("public", $this->dataCatGroup))
				$this->updateSitemapXML($this->getCurrentDataUrl($this->o_lng->defLang));
		}
		
		// Falls die News nur geändert werden soll
		elseif(isset($a_Post['edit_news'])) {
			$this->setSessionVar('notice',	"{s_notice:edit".parent::$type."}");
			$this->setSessionVar(parent::$type . '_id',	$this->editID);
			
			if($this->dataStatus == 0)
				$this->setSessionVar('hint', "<br />{s_notice:mod".parent::$type."}");
				
			elseif(isset($this->g_Session['hint'])) {
				$this->unsetSessionKey('hint');
				$this->unsetSessionKey(parent::$type . '_id');
			}
		}
		
		return $updateSQL1;
	
	}


	// readDataEntries
	public function readDataEntries()
	{
	
		// Falls nur eine bestimmte Kategorie
		if($this->listCat != "" && $this->listCat != "all") {
			
			$this->authorFilter	.= " AND dt.`cat_id` = " . $this->DB->escapeString($this->listCat) . " ";
		}
		// Andernfalls alle Kategorien
		else {
			
			$this->listCat	= "all";
		}
		
		
		// Falls nur versteckte/veröffentliche Daten
		if($this->pubFilter != "" 
		&& $this->pubFilter != "all"
		) {
			$this->authorFilter	.= " AND dt.`published` = " . ($this->pubFilter == "pub" ? "1" : "0");
		}
		
		// Sortierung				
		if($this->sortParam == "") {
			
			switch(parent::$type) {
				
				case "news":
					$this->sortParam = "datedsc";
					break;
					
				case "planner":
					$this->sortParam = "dateasc";
					break;
					
				default:
					$this->sortParam = "sortid";
					break;
			}
		}
		
		switch($this->sortParam) {
			
			case "dateasc":
				$dbOrder = " ORDER BY `date` ASC";
				break;
				
			case "datedsc":
				$dbOrder = " ORDER BY `date` DESC";
				break;
				
			case "nameasc":
				$dbOrder = " ORDER BY `header_" . $this->editLang . "` ASC";
				break;
				
			case "namedsc":
				$dbOrder = " ORDER BY `header_" . $this->editLang . "` DESC";
				break;
				
			case "callsasc":
				$dbOrder = " ORDER BY `calls` ASC";
				break;
				
			case "callsdsc":
				$dbOrder = " ORDER BY `calls` DESC";
				break;
				
			default:
				$dbOrder = " ORDER BY dt.`sort_id` ASC";
				break;
		}
		
		// Sortierungsparameter merken
		$this->setSessionVar('orderby', $this->sortParam);
		
		
		// db-Query nach News
		$this->dataEntriesArr = $this->DB->query("SELECT * 
													FROM `$this->dataTableDB` AS dt 
													LEFT JOIN `$this->catTableDB` AS dct 
														ON dt.`cat_id` = dct.`cat_id`
													$this->authorFilter	
													$dbOrder 
													", false);
		#die(var_dump($this->dataEntriesArr));
		

		if(!$this->showCalendar
		&& is_array($this->dataEntriesArr)
		&& count($this->dataEntriesArr) > 0
		) {
		
			$this->totalRows	= count($this->dataEntriesArr);
			$this->startRow		= "";
			$this->pageNum		= 0;
		
			// Pagination
			if (isset($GLOBALS['_GET']['pageNum']))
				$this->pageNum = $GLOBALS['_GET']['pageNum'];
			
				
					$this->startRow		= $this->pageNum * $this->limit;
					$queryLimit			= " LIMIT " . $this->startRow . "," . $this->limit;
					$this->queryString	= "task=modules&type=".parent::$type."&list_cat=".$this->listCat."&sort_param=".$this->sortParam."&pub=".$this->pubFilter."&limit=$this->limit";
					

			// db-Query nach News
			$this->dataEntriesArr = $this->DB->query(  "SELECT dt.*, dct.*, dt.`sort_id` AS sortid, `author_name`, user.`userid` 
															FROM `$this->dataTableDB` AS dt 
															LEFT JOIN `$this->catTableDB` AS dct 
																ON dt.`cat_id` = dct.`cat_id`
															$this->authorFilter 
															$dbOrder 
															$queryLimit
															", false);
			#die(var_dump($this->dataEntriesArr));
		}
	}
	

	// getResultNotices
	public function getResultNotices()
	{

		// Meldungen ausgeben
		if (!empty($this->notice)) {
			$this->adminContent .= '<p class="notice success">' . $this->notice . '</p>' . PHP_EOL;
			$this->successChange = true;
		}
		
		if(!empty($this->hint))
			$this->adminContent .= '<p class="error">' . $this->hint . '</p>' . PHP_EOL;
		
		
		if(isset($this->wrongInput) && count($this->wrongInput) > 0)
			$this->adminContent .= '<p class="error">{s_error:failchange}</p>' . PHP_EOL;
	
	}
	

	// setListToggles
	public function setListToggles()
	{

		// Meldungen ausgeben
		if(isset($GLOBALS['_GET']['list_cats'])) {
			$this->showCats			= true;
			$this->showExistingCats	= true;
			$this->hideNewCat		= true;
		}
		elseif(isset($this->g_Session['list_cats'])) {
			$this->showCats			= true;
			$this->showExistingCats	= true;
			$this->unsetSessionKey('list_cats');
		}	
		
		// Falls eine neu anzulegende Kategoriebez. per get übermittelt wurde
		if(isset($GLOBALS['_GET']['cat_name'])) {
			$this->dataCatName	= $GLOBALS['_GET']['cat_name'];
			$this->showCats			= true;
			$this->showExistingCats	= false;
			$this->hideNewCat		= false;
		}

		if((isset($GLOBALS['_POST']['new_cat']) || isset($GLOBALS['_POST']['edit_cat']))) {
			$this->showCats			= true;
			$this->showExistingCats	= $this->successChange;
			$this->hideNewCat		= $this->successChange;
		}
		
		if(!isset($GLOBALS['_POST']['new_cat'])
		&& !isset($GLOBALS['_POST']['edit_cat'])
		&& !isset($GLOBALS['_GET']['cat_name'])
		&& !isset($GLOBALS['_GET']['list_cats'])
		) {
			$this->showCats			= false;
			$this->showExistingCats	= true;
			$this->hideNewCat		= true;
		}

	}
	
	

	/**
	 * getDataCatActionBox
	 * @access protected
	 */
	protected function getDataCatActionBox()
	{

		// Checkbox zur Mehrfachauswahl zum Löschen
		$output =		'<div class="actionBox">' . PHP_EOL .
						'<form action="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=' . parent::$type . '&catid=array&list_cat=' . $this->listCat . '&action=" method="post" data-history="false">' . PHP_EOL .
						'<label class="markAll markBox" data-mark="#sortableDataCat">' . PHP_EOL .
						'<input type="checkbox" id="markAllLB-cat" data-select="all" /></label>' . PHP_EOL .
						'<label for="markAllLB-cat" class="markAllLB"> {s_label:mark}</label>' . PHP_EOL .
						'<span class="editButtons-panel">' . PHP_EOL;
		
		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'delAll delDataCats delSelectedListItems button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:delmarked}',
								"attr"		=> 'data-action="delmultiple"',
								"icon"		=> "delete"
							);
			
		$output .=		parent::getButton($btnDefs);
						
		$output .=		'</span>' . PHP_EOL .
						'</form>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		return $output;
	
	}
	
	

	/**
	 * getDataActionBox
	 * @access protected
	 */
	protected function getDataActionBox()
	{

		$actionBox	=	'<div class="actionBox">' . PHP_EOL .
						'<label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" /></label>' . PHP_EOL .
						'<label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' . PHP_EOL .
						'<span class="editButtons-panel">' . PHP_EOL;
		
		// Falls Rating für eine Kategorie aktiviert ist, resVotes-Button in ActionBar anzeigen
		foreach($this->dataEntriesArr as $dataGroup) {
		
			if(isset($dataGroup['rating']) && $dataGroup['rating'] == 1) {
			
				// Button reset
				$btnDefs	= array(	"type"		=> "submit",
										"class"		=> 'delAll resetVotes button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:resvotes}',
										"attr"		=> 'data-action="delmultiple"',
										"icon"		=> "reset"
									);
					
				$actionBox .=	parent::getButton($btnDefs);
				
				break;
			}
		}
		
		// Button publish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll pubData button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:pubmarked}',
								"attr"		=> 'data-action="pubmultiple"',
								"icon"		=> "publish"
							);
			
		$actionBox .=	parent::getButton($btnDefs);
		
		// Button unpublish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll pubData unpublish button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:unpubmarked}',
								"attr"		=> 'data-action="pubmultiple"',
								"icon"		=> "unpublish"
							);
			
		$actionBox .=	parent::getButton($btnDefs);
		
		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'delAll delData button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:delmarked}',
								"attr"		=> 'data-action="delmultiple"',
								"icon"		=> "delete"
							);
		
		$actionBox .=	parent::getButton($btnDefs);
		
		$actionBox .=	'</span>' . PHP_EOL .
						'</div>' . PHP_EOL;
	
		return $actionBox;
	
	}

	
	// getEditableAuthor
	protected function getEditableAuthor($author, $authorID, $id)
	{
	
		if(!$this->editorLog)
			return $author;
		
		$output	 = "";
		
		$output	.= '<a href="#" id="authorname-' . $id . '" data-type="select" data-pk="' . $id . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=setauthor&mod=' . parent::$type . '&id=' . $id . '" data-value="' . $authorID . '" data-title="{s_option:author}">' . $author . '</a>' . "\n";
		
		$output	.= $this->getEditableScriptTag('"#authorname-' . $id . '"');
		
		return	$output;
	
	}

	
	// getEditableDate
	protected function getEditableDate($date, $id, $lang)
	{
	
		$output	 = "";
		$dateStr = Modules::getLocalDateString($date, $lang, true);
		$dateFmt = Modules::getComboDateFormat($lang, true);
		
		$output	.=	'<a href="#" id="datadate-' . $id . '" class="editable editable-click editable-empty" data-type="combodate" data-template="DD MM YYYY  HH:mm" data-format="YYYY-MM-DD HH:mm" data-viewformat="DD.MM.YYYY hh:mm" data-pk="' . $id . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=setdate&mod=' . parent::$type . '&id=' . $id . '" title="{s_date:created}" data-title="{s_date:created}" data-value="' . $date . '" data-original-title="' . $dateStr . '">' . $dateStr . '</a>' . PHP_EOL;
		
		$output	.= $this->getCombodateScriptTag('"#datadate-' . $id . '"');
		
		return	$output;
	
	}
	


	// getAddCatButton
	public function getAddCatButton($align = "left")
	{
		
		$output		=	'<form action="' . $this->formAction . '" method="post" data-ajaxform="true" data-getcontent="fullpage">' . PHP_EOL;
		
		// Button neue Kategorie anlegen
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> "add buttonpanel-nofix " . $align,
								"value"		=> '{s_button:newcat}',
								"icon"		=> "new"
							);
		
		$output		.=	parent::getButton($btnDefs);
		
		$output		.=	'<input name="new_cat" type="hidden" />' . PHP_EOL .
						'</form>' . PHP_EOL;
			
		return $output;
	
	}
	


	// getAddDataButton
	public function getAddDataButton($align = "left")
	{
	
		$output		=	'<form action="' . $this->formAction . '" method="post" data-ajaxform="true" data-getcontent="fullpage">' . PHP_EOL;
		
		// Button neuen Artikel anlegen
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> "add buttonpanel-nofix " . $align,
								"value"		=> '{s_button:add'.parent::$type.'}',
								"icon"		=> "new"
							);
		
		$output		.=	parent::getButton($btnDefs);
		
		$output		.=	'<input name="add_new" type="hidden" />' . PHP_EOL .
						'</form>' . PHP_EOL;
			
		return $output;
	
	}
	


	// getAddDataToCatButton
	public function getAddDataToCatButton($catID, $type = "image", $j = 1)
	{
	
		if($type == "button") {
			
			// Button neuen Artikel anlegen
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> "add buttonpanel-nofix left",
									"value"		=> '{s_button:add'.parent::$type.'}',
									"icon"		=> "new"
								);
			
			$input		=	parent::getButton($btnDefs);
		}
		else {
		
			// Button new
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "mod_submit",
									"class"		=> 'button-icon-only',
									"value"		=> "new",
									"text"		=> "",
									"title"		=> '{s_button:add'.parent::$type.'}',
									"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "new"
								);
				
			$input 		=	parent::getButton($btnDefs);
		}
			
		$output		=	'<form action="' . $this->formAction . '" method="post" accept-charset="UTF-8">' . PHP_EOL .
						$input .
						'<input type="hidden" name="mod_submit" value="' . $catID . '" />' . PHP_EOL .
						'<input type="hidden" name="news_cat" value="' . $catID . '" />' . PHP_EOL .
						'</form>' . PHP_EOL;
		
		return $output;
	
	}
	


	// getBackToListButtons
	public function getBackToListButtons($dataCat)
	{
	
		$output		=	'<ul><li class="submit back">' . PHP_EOL .
						'<form action="' . $this->formAction . '" method="post">' . PHP_EOL; // Formular mit Buttons zum Zurückgehen
		
		// Button back
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> "back left",
								"value"		=> '{s_button:'.parent::$type.'list}',
								"icon"		=> "backtolist"
							);
		
		$output		.=	parent::getButton($btnDefs);
		
		$output		.=	'<input name="list_cat" type="hidden" value="'.$dataCat.'" />' . PHP_EOL .
						'</form>' . PHP_EOL .
						$this->getAddDataButton("right") . 
						'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL .
						'</ul>' . PHP_EOL;
			
		return $output;

	}
	


	// getPreviewNavItem
	public function getPreviewNavItem()
	{
	
		// Vorschaulink für Datensatz im Kopfbereich einbinden
		// Button preview
		$btnDefs	= array(	"href"		=> $this->currentDataUrl,
								"class"		=> 'pagePreview gotoPreviewPage button-icon-only button-small',
								"text"		=> "",
								"title"		=> '{s_notice:preview} {s_title:dataentry} &quot;' . $this->dataHeader . '&quot; {s_title:pagepreview1b}',
								"icon"		=> "preview"
							);
			
		$output		=	parent::getButtonLink($btnDefs);
		
		return $output;

	}
	


	// getCurrentDataUrl
	public function getCurrentDataUrl($lang = "editLang")
	{
	
		// Vorschaulink für Datensatz im Kopfbereich einbinden
		$output		=	HTML::getLinkPath($this->targetPage, $lang, false, true) . '/' . (USE_CAT_NAME ? $this->dataCatAlias . '/' : '') . $this->dataAlias . '-' . $this->dataCat . parent::$type[0] . $this->dataID . PAGE_EXT . ($lang == "editLang" ? '?lang=' . $this->editLang : '');
		return $output;

	}

	
	// getScriptTag
	protected function getScriptTag()
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready("ccInitScript", function(){' . PHP_EOL .
				'$.addInitFunction({name: "$.myTinyMCEModules", params: ""});' . PHP_EOL .
				'$.addInitFunction({name: "$.sortableData", params: ""});' . PHP_EOL .
				'$.addInitFunction({name: "$.sortableObjects", params: ""});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}
	

	// getEditableScriptTag
	public function getEditableScriptTag($tag)
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
				'head.load({xeditablecss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/x-editable/css/jqueryui-editable.css"});' . PHP_EOL .
				'head.load({xeditable: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/x-editable/js/jqueryui-editable.min.js"});' . PHP_EOL .
				'head.ready("xeditable", function(){' . PHP_EOL .
					'$("document").ready(function(){' . PHP_EOL .
						'$(' . $tag . ').editable({' . PHP_EOL .
						'source: "' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=getauthors&mod=' . parent::$type . '",' . PHP_EOL .
						'sourceCache: true,' . PHP_EOL .
						'showbuttons: false' . PHP_EOL .
						'});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}
	

	// getCombodateScriptTag
	public function getCombodateScriptTag($tag)
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
				'head.load({momentjs: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/moment/moment.min.js"});' . PHP_EOL .
				'head.load({xeditablecss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/x-editable/css/jqueryui-editable.css"});' . PHP_EOL .
				'head.load({xeditable: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/x-editable/js/jqueryui-editable.min.js"});' . PHP_EOL .
				'head.ready("momentjs", function(){' . PHP_EOL .
				'head.ready("xeditable", function(){' . PHP_EOL .
					'$("document").ready(function(){
						var date	= new Date();
						$(' . $tag . ').editable({
							type: "combodate",
							combodate: {
								minYear: 2010,
								maxYear: date.getFullYear() +1,
								minuteStep: 5
							}
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

	
	// getFullcalendarScriptTag
	protected function getFullcalendarScriptTag()
	{
	
		$output	=	'<script>' . PHP_EOL .
					'head.ready("ui", function(){' . PHP_EOL .
					'head.load({datadialog: "' . SYSTEM_HTTP_ROOT . '/access/js/myFullCalendar.min.js"});' . PHP_EOL .
					'head.load({fullcalendarcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/fullcalendar/fullcalendar.min.css"});' . PHP_EOL .
					'head.load({momentjs: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/fullcalendar/lib/moment.min.js"});' . PHP_EOL .
					'});' . PHP_EOL .
					'head.ready("momentjs", function(){' . PHP_EOL .
					'head.load({fullcalendar: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/fullcalendar/fullcalendar.min.js"});' . PHP_EOL .
					'head.ready("fullcalendar", function(){' . PHP_EOL;
		if($this->adminLang != "en"){
			$output	.=	'head.load({fullcalendarln' . $this->adminLang . ': "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/fullcalendar/lang/' . $this->adminLang . '.js"});' . PHP_EOL;
		}
		$output	.=	'head.ready("fullcalendar' . ($this->adminLang != "en" ? "ln" . $this->adminLang : "") . '", function(){' . PHP_EOL .
						'$("document").ready(function(){
							cc.dataType	= "' . parent::$type . '";
							cc.dataEvent = {"catid":"","cat":""};
							cc.grabPageColors();
							$("#calendar").fullCalendar({
								theme: "concise",
								//firstDay: 1 // affected by lang
								lang: "' . $this->adminLang . '",' .
								(!empty($this->dataDate) ? 'defaultDate: "' . $this->dataDate . '",' : '') . '
								header: {
									left: "prevYear,prev today next,nextYear",
									center: "title",
									right: "month,agendaWeek,agendaDay"
								},
								droppable: true,
								dropAccept: ".fc-external-event",
								eventReceive : function(event, ui) {
									var calendar = $("#calendar").fullCalendar("getCalendar");
									var newMoment = calendar.moment(cc.dataEvent.date);
									var newDate = calendar.moment(cc.dataEvent.date).format("X");
									var hasTime	= newMoment.hasTime();
									var timeStart	= cc.dataEvent.date;
									var timeEnd	= (hasTime ? null : moment.unix(parseInt(newDate) + 22*3600).format("YYYY-MM-DD HH:mm:ss"));
									cc.dataEvent.catid = event.catid;
									cc.dataEvent.cat = event.cat;
									
									$("#calendar").fullCalendar("removeEvents", event.id);
									//calendar.select(calendar.moment(cc.dataEvent.date), calendar.moment(cc.dataEvent.date));
									calendar.select(timeStart, timeEnd);
								},
								drop: function(date, jsEvent, ui) {
									cc.dataEvent.date = date;
								},
								editable : true,
								eventDurationEditable : cc.dataType == "planner" ? true : false,
								eventRender: function(view, element) {
									$("#external-events .toggleExternalEvents").each(function(){
										var catid = $(this).attr("data-eventcat");
										if(!$(this).is(":checked")
										&& element.hasClass("fc-event-cat-" + catid)){
											element.addClass("hide");
										}
									});
								},
								selectable: true,
								selectHelper: true,
								select: function(start, end) {
									var title,
										eventData,
										dateFormat = "' . ($this->adminLang != "en" ? "DD.MM.YYYY" : "MM/DD/YYYY") . '",
										timeFormat = "HH:mm",
										calendar = $("#calendar").fullCalendar("getCalendar"),
										newMoment = calendar.moment(start);
										hasTime	= newMoment.hasTime();
										dateStart = calendar.moment(start).format(dateFormat),
										dateEnd = calendar.moment((hasTime ? end : end-1)).format(dateFormat);
										timeStart = calendar.moment(start).format(timeFormat),
										timeEnd = calendar.moment((hasTime ? end : end-900*1000)).format(timeFormat);
									$.createDataDialog("data", "' . '{s_header:'.parent::$type.'con}' . '", dateStart, dateEnd, timeStart, timeEnd, cc.dataEvent.catid, cc.dataEvent.cat);
									$("#calendar").fullCalendar("unselect");
								},
								eventMouseover: function(calEvent, jsEvent, view) {
									// title tag
									var calendar = $("#calendar").fullCalendar("getCalendar");
									var date = "";
									var dateFormat = "DD. MMMM YYYY";
									var timeFormat = "HH:mm";
									var dateStart = calendar.moment(calEvent.start).format(dateFormat);
									var dateEnd = "";
									var timeStart = calendar.moment(calEvent.start).format(timeFormat);
									var timeEnd = "";
									date = dateStart;
									if(calEvent.end){
										dateEnd = calendar.moment(calEvent.end).format(dateFormat);
										if(dateEnd != dateStart){
											date += "<br />-<br />" + dateEnd;
										}else{
											timeEnd		= calendar.moment(calEvent.end).format(timeFormat);
											date += "<br />" + timeStart + "-" + timeEnd;
										}
									}else{
										date += "<br />" + timeStart;
									}
									$(this).attr("title", "<strong>" + calEvent.title + "</strong> <span class=\'small\'>[#" + calEvent.id + "]</span><br /><br />" +  date);
								},
								eventClick: function(calEvent, jsEvent, view) {
									// change the border color
									$(this).css("border-color", "#93D835");
								},
								eventDrop: function(event, delta, revertFunc) {
									cc.changeDataFcEvent(event, delta, revertFunc);
								},
								eventResize: function(event, delta, revertFunc) {
									cc.changeDataFcEvent(event, delta, revertFunc);
								},
								eventLimit: true, // allow "more" link when too many events
								events: [' . PHP_EOL;
		
		// Einträge auflisten
		foreach($this->dataEntriesArr as $dataEntry) {
			
			if($this->loggedUserID == $dataEntry['author_id'] || $this->editorLog == true) {
				
				$dataID			= $dataEntry['id'];
				$dataCatID		= $dataEntry['cat_id'];
				$authorName		= $dataEntry['author_name'] != "" ? $dataEntry['author_name'] : '{s_common:unknown}';
				$authorID		= $dataEntry['userid'] != "" ? $dataEntry['userid'] : 0;
				$dataTitle		= $dataEntry['header_' . $this->editLang];
				$dataStatus		= $dataEntry['published'];
				$eventClass		= "fc-event-cat-" . $dataCatID;
				$dateStart		= "";
				$dateEnd		= "";
				
				if($dataStatus)
					$eventClass	.= " published";
				else
					$eventClass	.= " unpublished";
				
				if(parent::$type == "planner")
					$dateStart		= $dataEntry['date'] . 'T' . $dataEntry['time'];
				else
					$dateStart		= str_replace(" ", "T", $dataEntry['date']);
				
				if(isset($dataEntry['date_end'])
				&& isset($dataEntry['time_end'])
				) {
					$isAllDay	= $dataEntry['time'] == "00:00:00" && $dataEntry['time_end'] == "23:45:00" ? true : false;
					$dateEnd	=		'
										end : "' . $dataEntry['date_end'] .
										'T' . $dataEntry['time_end'] . '",
										allDay : false, // will make the time show' . PHP_EOL;
				}
				
				$output	.=			'{
										id     : "' . $dataID. '",
										title  : \'' . $dataTitle . '\',
										start  : "' . $dateStart . '",' . PHP_EOL .
										$dateEnd .
										'url   : \'' . ADMIN_HTTP_ROOT . '?task=modules&type=' . parent::$type . '&data_id=' . $dataID . '\',' . PHP_EOL .
										'className   : \'' . $eventClass . '\',' . PHP_EOL .
										'textColor   : cc.eventColors[' . substr($dataEntry['cat_id'], -1) . '],' . PHP_EOL .
										'backgroundColor   : cc.eventBGColors[' . substr($dataEntry['cat_id'], -1) . ']
									},';
			}
		}
			
		$output	.=			']' . PHP_EOL;
		
		$output	.=			'})
						});' . PHP_EOL .
					'});' . PHP_EOL .
					'});' . PHP_EOL .
					'});' . PHP_EOL .
					'</script>' . PHP_EOL;
	
		return $output;
	
	}
	
	
	// getDataAdminTourScript
	protected function getDataAdminTourScript()
	{
	
		return	'<script>
				head.ready(function(){
					head.load({hopscotch: "extLibs/jquery/hopscotch/js/hopscotch.min.js"}, function(){
						head.load("extLibs/jquery/hopscotch/css/hopscotch.min.css");
						head.load({admintourdata: "system/inc/admintasks/modules/js/adminTour.data.min.js"}, function(){
							$("document").ready(function(){
								// Start tour on desktop devices
								if(!cc.isPhone()){
									$.data_AdminTour();
								}
							});
						});
					});
				});
				</script>' . PHP_EOL;
	
	}

}
