<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\Modules\OutputExtendDataEvent;


require_once PROJECT_DOC_ROOT . "/inc/classes/Contents/class.Contents.php"; // Contents-Klasse einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.Contents.php"; // Contents einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Modules.php"; // Modules einbinden

// Event-Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Modules/events/event.OutputExtendData.php";

/**
 * Klasse für Daten-Module
 *
 */

class ModulesData extends Contents
{
	
	protected $o_outputExtendDataEvent	= null;
	protected $singleData				= false;
	protected $useModDate				= false;
	protected $parentCats				= array();
	protected $dataObjects				= array();
	protected $objOutput				= array();
	protected $dataObjectKind			= "";
	protected $objectNumber				= 0;
	protected $teaserImg				= "";
	protected $teaserImgSrc				= "";
	protected $targetPageID				= "";
	protected $dataLink					= "";
	protected $published				= false;
	protected $dataFeatured				= false;
	protected $dbFilterNext				= "";
	protected $dbOrderNext				= "";
	protected $dbFilterPrev				= "";
	protected $dbOrderPrev				= "";
	protected $linkBackCat				= "";
	protected $queryData				= array();
	protected $queryNextData			= array();
	protected $queryPrevData			= array();
	protected $isPluginObject			= array();
	protected $dataEditAccess			= false;

	public function __construct($DB, $o_lng, $o_dispatcher, $eventListeners, $o_page)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->o_dispatcher		= $o_dispatcher;
		$this->eventListeners	= $eventListeners;
		$this->lang				= $this->o_lng->lang;
		$this->o_page			= $o_page;
		
		// Security-Objekt
		$this->o_security		= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session		= Security::getSessionVars();

		// Berechtigung
		$this->getAccessDetails();

		// Events listeners registrieren
		$this->addEventListeners("data");

	}
	
	/**
	 * Methode zur Ausgabe von Moduldaten (Artikel, News, Termine)
	 * 
     * @param	string	$modType Modultyp
     * @param	string	$catID Kategorie-ID
     * @param	string	$dataID Daten-ID (wenn nicht numerisch, =Darstellungsform der Datensätze)
     * @param	int		$targetPageID Zielseiten ID (default ='')
     * @param	string	$limit Datensatzlimit (default ='')
     * @param	string	$order Sortierung (default ='')
     * @param	string	$comments Kommentare (default ='')
     * @param	string	$rating Bewertungssystem (default ='')
     * @param	array	$ratingGroups Benutzergruppen, die zum Rating berechtigt sind
     * @param	array	$readCommentGroups Benutzergruppen, die zum Lesen von Kommentaren berechtigt sind
     * @param	array	$writeCommentGroups Benutzergruppen, die zum Schreiben von Kommentaren berechtigt sind
     * @param	string	$dataMonth Monat, falls Monatsarchiv per GET übermittelt (default ='')
     * @param	string	$altTpl Alternativer Template Dateiname ohne Erweiterung (default ='')
	 * @access	public
	 * @return	string	Datenausgabe
	 */
	public function getModule($modType, $catID, $dataID, $targetPageID = "", $limit = "", $order = "", $comments = "", $rating = "", $ratingGroups = array(), $readCommentGroups = array(), $writeCommentGroups = array(), $altTpl = "", $dataMonth = "")
	{
		
		// Falls keine Daten mitgegeben wurden, Meldung ausgeben
		if($catID == ""
		&& $dataID == ""
		)
			return $this->getNotificationStr('{s_option:' . $modType . '} &#x25BA; {s_notice:nodata}', "hint", "permanent-notice");
	
		
		$dataOutput					= "";
		$this->singleData			= false;
		$this->modType				= $modType;
		$this->catID				= $catID;
		$this->dataID				= $dataID;
		$this->targetPageID			= $targetPageID;
		$this->dbFilter				= "";
		$this->order 				= $order;
		$this->dbOrder				= "ORDER BY ";
		$this->limit 				= $limit;
		$this->dataTables[]			= $GLOBALS['commentTables'];
		$this->readCommentsGroups	= $readCommentGroups;
		$this->writeCommentsGroups	= $writeCommentGroups;
		$this->comments				= $comments;
		$this->rating				= $rating;
		$this->ratingGroups 		= $ratingGroups;
		$this->useTpl				= $altTpl;
		
		// Templatename bestimmen
		if($this->useTpl == "")
			$this->useTpl	= $this->modType;					
		elseif(is_numeric($dataID) && !file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . 'mod_tpls/single_' . $this->useTpl . '.tpl'))
			return "alternative template file &quot;single_" . $this->useTpl . ".tpl&quot; not found.";
		elseif(!is_numeric($dataID) && !file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . 'mod_tpls/' . $dataID . '_' . $this->useTpl . '.tpl'))
			return "alternative template file &quot;" . $dataID . "_" . $this->useTpl . ".tpl&quot; not found.";
		elseif(!is_numeric($dataID) && !file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . 'mod_tpls/' . $dataID . '_' . $this->useTpl . '_loop.tpl'))
			return "alternative template file &quot;" . $dataID . "_" . $this->useTpl . "_loop.tpl&quot; not found.";

		// Falls keine Zielseite angegeben wurde, zur aktuellen Seite verlinken
		if($this->targetPageID == "")
			$this->targetUrl	= parent::$currentURL;
		else
		// TargetUrl/-page
			$this->targetUrl	= PROJECT_HTTP_ROOT . '/' . htmlspecialchars(HTML::getLinkPath($this->targetPageID, "current", false));
		
		$this->targetPage	= pathinfo($this->targetUrl, PATHINFO_BASENAME);

		
		// Kategorienamen (Alias) auslesen
		if(isset($GLOBALS['_GET']['cn'])) {
			$this->urlCatPath	= $GLOBALS['_GET']['cn'] . '/';
		}
		
		$currDataAlias = "";
		// Kategorienamen (Alias) auslesen
		if(isset($GLOBALS['_GET']['dn']) && $GLOBALS['_GET']['dn'] != "") {
		
			// Falls Kategoriename nicht explizit in Url erscheinen soll
			if(!USE_CAT_NAME)
				$this->urlCatPath = "";
			
			$currDataAlias = htmlspecialchars($GLOBALS['_GET']['dn']);
		}
		else
			$currDataAlias = "comments";

			
		// Ggf. Kommentarformular einbinden
		if((in_array($this->modType, $this->dataTables) || $this->comments == 1) && isset($GLOBALS['_GET']['newcom']) && $GLOBALS['_GET']['newcom'] != "" && is_numeric($GLOBALS['_GET']['newcom']) && !isset($GLOBALS['_COOKIE']['spam_protection']) && $this->isMainContent) {
		
			$entryID = $GLOBALS['_GET']['newcom'];
			
			if(isset($GLOBALS['_GET']['mod']) && in_array($GLOBALS['_GET']['mod'], $this->dataTables)) // Falls der Get-Parameter für die Erstellung eines neuen Eintrags gesetzt ist, Formular für neuen Eintrag anzeigen
				$this->dataTable = $GLOBALS['_GET']['mod'];
			
			if(!$this->commentForm) {
		
				// Formvalidator
				$this->scriptFiles["formvalidator"]	= "extLibs/jquery/form-validator/jquery.form-validator.min.js";		
			
				require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";
				$o_comments = new Comments($this->DB, $this->o_lng, $this->modType, $entryID, $this->targetPage.'/'.$this->urlCatPath.$currDataAlias.'-'.$this->catID.$this->modType[0].$this->ID.PAGE_EXT, $this->group, $this->readCommentsGroups, $this->writeCommentsGroups, true);
				$this->commentForm = true;
				
				return $o_comments->getCommentsForm($this->modType, $entryID); // Kommentarformular anzeigen
			}
			else
				return "";
		}
		
		// Falls ein neuer Eintrag (Kommentare) erfolgt ist
		if(isset($this->g_Session['notice'])
		&& $this->isMainContent
		&& !strpos($this->g_Session['notice'], "delcon")
		&& !strpos($this->g_Session['notice'], "pastecon")
		&& $this->displayMode != "related"
		) {
			$this->notice	= $this->g_Session['notice'];
			$this->unsetSessionKey('notice');
		}
		
		// Aktuelles Datum
		$currDate			= self::getCurrentDate();
		$currTime			= self::getCurrentTime();
		$currDateStamp		= self::getTimestamp($currDate);
		$currDateTimeStamp	= self::getTimestamp($currDate, 0, 0, 0, "-", $currTime);
		
		// Auslesen des Modultyps
		switch($this->modType) {
		
			case "articles":
				$this->dataTable	= "articles";
				$this->dataTableDB	= DB_TABLE_PREFIX . "articles";
				$this->catTableDB	= DB_TABLE_PREFIX . "articles_categories";
				$this->dbFilterDate	= " AND UNIX_TIMESTAMP(dt.`date`) <" . $currDateTimeStamp;
				break;
			
			case "news":
				$this->dataTable	= "news";
				$this->dataTableDB	= DB_TABLE_PREFIX . "news";
				$this->catTableDB	= DB_TABLE_PREFIX . "news_categories";
				$this->dbFilterDate	= " AND UNIX_TIMESTAMP(dt.`date`) <" . $currDateTimeStamp;
				break;
			
			case "planner":
				$this->dataTable	= "planner";
				$this->dataTableDB	= DB_TABLE_PREFIX . "planner";
				$this->catTableDB	= DB_TABLE_PREFIX . "planner_categories";
				$this->dbFilterDate	= " AND UNIX_TIMESTAMP(dt.`date`) " . ($this->displayMode == "expired" ? "<" : ">= ") . $currDateStamp;
				break;
				
			case "newsfeed":
				$this->dataTable	= "news";
				$this->dataTableDB	= DB_TABLE_PREFIX . "news";
				$this->catTableDB	= DB_TABLE_PREFIX . "news_categories";
				$this->dbFilterDate	= " AND UNIX_TIMESTAMP(dt.`date`) <" . $currDateTimeStamp;
				break;
			
			
		}
		
		
		// Sortierung der Daten
		switch($this->order) {
			
			case "dateasc":
				$this->dbOrder .= "`date` ASC";
				break;
			case "datedsc":
				$this->dbOrder .= "`date` DESC";
				break;
			case "nameasc":
				$this->dbOrder .= "`header_" . $this->lang . "` ASC";
				break;
			case "namedsc":
				$this->dbOrder .= "`header_" . $this->lang . "` DESC";
				break;
			default:
				$this->dbOrder .= "dt.`sort_id` ASC";
				break;
		}
		

		// catIdDb
		$catIdDb = $this->DB->escapeString($this->catID);

		
		// Falls editorLog, auch unveröffentlichte Daten anzeigen
		if($this->editorLog)
			$this->published = "`published` >= 0";
		// Sonst nur veröffentlichte Daten
		else
			$this->published = "`published` = 1";

		
		// Benutzergruppe
		// Benutzerberechtigung
		$userGroupDb	= $this->DB->escapeString($this->group);
		$authorFilter	= "";
		
		// Falls nicht Admin oder Editor
		if(!$this->editorLog) {
		
			// Falls Author, eigene Daten einschließen
			if($this->group == "author")
				$authorFilter = " OR dt.`author_id` = '" . $this->DB->escapeString($this->g_Session['userid']) . "'";
				
			$this->dbFilterGroup = "(dct.`group` = 'public' OR FIND_IN_SET('" . $this->DB->escapeString($userGroupDb) . "', dct.`group`)" . ContentsEngine::getOwnGroupsQueryStr($this->ownGroups, "dct.") . ")";
		}
		else
			$this->dbFilterGroup = "dct.`group` != ''";



		// Datenfilter
		// Falls Newsfeed und alle Datenkategorien angezeigt werden sollen
		if($this->modType == "newsfeed"
		&& ($this->catID == "<all>" || $this->catID == "")
		)
			$this->dbFilter .= "AND `cat_id` != '' AND `newsfeed` > 0";
		// Falls Newsfeed
		elseif($this->modType == "newsfeed")
			$this->dbFilter .= "AND `cat_id` = $catIdDb AND `newsfeed` > 0";
		// Falls alle Datenkategorien angezeigt werden sollen
		elseif($this->catID == "<all>")
			$this->dbFilter .= "AND dt.`cat_id` != ''";
		// Falls eine Datenkategorie angegeben ist und kein unspezifischer Anzeigetyp gewählt ist
		elseif($this->catID != ""
		&& !is_numeric($this->dataID)
		&& $this->dataID != "latest"
		&& $this->dataID != "random"
		&& $this->dataID != "related"
		&& $this->dataID != "popular"
		&& $this->dataID != "rated"
		&& !isset($GLOBALS['_GET']['tag'])
		)
			$this->dbFilter .= "AND dt.`cat_id` = $catIdDb";
		// Falls eine Datenkategorie angegeben ist und kein unspezifischer Anzeigetyp gewählt ist
		elseif(!empty($this->catID)
		&& !empty($this->rootCatID) 
		&& is_numeric($this->dataID)
		)
			$this->dbFilter .= "AND dt.`cat_id` = $catIdDb";
		
		
		// Falls ein Monatsarchiv per GET übermittelt wurde
		if($dataMonth != "" && !preg_match("/\//", $dataMonth)) { // Monatsarchiv (kein Kalender)
			$dataMonthDb	= $this->DB->escapeString($dataMonth);
			$this->dbFilter .= "AND MONTH(dt.`" . ($this->modType != "planner" && $this->useModDate ? 'mod_date' : 'date') . "`) = $dataMonthDb";
		}
		// Ggf. Authorenfilter
		$this->dbFilter .= $authorFilter;
		
		
	
		// Falls dataID nicht numerisch ist, muss es sich um einen Ansichtsmodus (z.B. latest) handeln
		if($this->dataID != "" && !is_numeric($this->dataID)) {
			$this->displayMode = $this->dataID;
		}
		elseif(is_numeric($this->dataID) && $this->isMainContent) {
		
			if($this->displayMode != "related"
			&& $this->displayMode != "expired"
			) {
				
				$this->displayMode	= "single";
				$this->singleData	= true;
			}
		}
		else { // Falls kein Ansichtsmodus angegeben, Listenansicht wählen
			$this->displayMode = "list";
		}
		
		// Falls nur Datensätze mit bestimmtem Tag angezeigt werden sollen
		if(isset($GLOBALS['_GET']['tag']) && 
		   $GLOBALS['_GET']['tag'] != "" && 
		   $this->isMainContent && 
		   $this->displayMode != "related" && 
		   $this->modType != "newsfeed"
		) {
			$this->dbFilter .= " AND (MATCH (dt.`tags_" . $this->lang . "`) AGAINST ('+" .  $this->DB->escapeString($GLOBALS['_GET']['tag']) . "' IN BOOLEAN MODE) OR dt.`tags_" . $this->lang . "` LIKE '%" . $this->DB->escapeString($GLOBALS['_GET']['tag']) . "%') ";
			$this->displayMode	= "list";
			$this->queryString = "tag=" . urlencode(htmlspecialchars($GLOBALS['_GET']['tag']));
		}
			
			
		// Falls Kindartikel aus allen Kindkategorien berücksichtigt werden sollen
		if(($this->displayMode == "latest" || 
			$this->displayMode == "expired" || 
			$this->displayMode == "random" || 
			$this->displayMode == "related" || 
			$this->displayMode == "popular" || 
			$this->displayMode == "rated") && 
			$this->catID != "<all>" && $this->catID != ""
		) {
		
			ModulesData::getAllChildCats($this->rootCatID);
			$this->dbFilter .= " AND (dt.`cat_id` = $catIdDb";
								
			foreach($this->childCatIDs as $childCat) {
				$this->dbFilter .= " OR dt.`cat_id` = $childCat";
			}
			
			$this->dbFilter .= ")";
		}

		
		// POST-Parameter von Modulen auslesen
		// Datepicker
		if(isset($GLOBALS['_POST']['datepicker_pickedDate']) && $GLOBALS['_POST']['datepicker_pickedDate'] != "" && $this->isMainContent && preg_match("/\//", $dataMonth)) {
			
			$dataDate = explode("/", $dataMonth);
			
			$dataDate = $dataDate[2]."-".$dataDate[0]."-".$dataDate[1];

			$this->dbFilter .= " AND DATE(dt.`date`) = '$dataDate'";
		}
		
	
		// Inhalte generieren
		// Einzelner Datensatz
		if(($this->singleData == true || ($this->displayMode == "related" && $this->ID != "")) && $this->isMainContent) {

			$dataOutput = ModulesData::getSingleData($this->dataID);
		
		} // Ende if einzelner Datensatz



		// Falls Datensätze in einer angegebenen Form ausgegeben werden sollen
		elseif($this->displayMode == "list" || 
			   $this->displayMode == "latest" || 
			   $this->displayMode == "expired" || 
			   $this->displayMode == "teaser" || 
			   $this->displayMode == "detail" || 
			   $this->displayMode == "popular" || 
			   $this->displayMode == "rated" || 
			   $this->displayMode == "random"
			   ) {
			
			$dataOutput = ModulesData::getModuleData();
					
		} // Ende if displayMode

		

		// Falls ein Archiv/Kalender ausgegeben werden soll
		elseif($this->displayMode == "archive" ||
			   $this->displayMode == "calendar"
			   ) {
			
			$dataOutput = ModulesData::getArchiveData($dataMonth);
			
			
		} // Ende if archive


		
		// Falls ein Archiv oder ein Kategorieteaser ausgegeben werden soll
		elseif($this->displayMode == "articlemenu" ||
			   $this->displayMode == "catmenu" ||
			   $this->displayMode == "catteaser"
			   ) {

			$dataOutput = ModulesData::getDataMenu();
		
		} // Ende if menu/teaser
						
		
		
		// Falls eine Feedliste erstellt werden soll
		elseif($this->modType == "newsfeed" &&
			   ($this->displayMode == "all" ||
			   $this->displayMode == "RSS" ||
			   $this->displayMode == "Atom")
			   ) {
			
			$dataOutput = ModulesData::getFeedList();
		
		} // Ende if displayMode

		#var_dump($this->displayMode);		

		$this->dataOutput = parent::replaceStaText($dataOutput);
		
		return $this->dataOutput;


	}

	
	
	#################################################
	#########   Methoden zur Datenausgabe   #########
	#################################################


	/**
	 * Methode zur Ausgabe von einzelnen Moduldaten (Artikel, News, Termine)
	 * 
     * @param	string Daten-ID
	 * @access	public
	 * @return	string
	 */
	public function getSingleData($dataID)
	{		
	
		// Templatename
		$dataTpl		= "mod_tpls/single_".$this->useTpl.".tpl";
		$tpl_data		= new Template($dataTpl);
		$tpl_data->loadTemplate();
	
		
		// OutputExtendDataEvent
		$this->o_outputExtendDataEvent			= new OutputExtendDataEvent($this->DB, $this->o_lng, $tpl_data, $this->modType);
		

		// Data-ID
		$dataIdDb		= (int)$dataID;
		$dataOutput		= "";
		$commentOutput	= "";
		$ratingOutput	= "";
		$socialBar		= "";
		

		if(!empty($this->notice)
		&& $this->displayMode != "related"
		) {
			$dataOutput .=	$this->getNotificationStr($this->notice, "success");
			$this->notice = "";
		}
		

		// db-Query Einzeldatensatz
		$this->queryData = $this->getDataEntryFromDB($dataID);


		
		if(!is_array($this->queryData)
		|| count($this->queryData) == 0
		)
			return $dataOutput;
	
		
		// Falls verwandte Daten angezeigt werden sollen
		if($this->displayMode == "related" && !empty($this->ID)) {

			$dataOutput .= $this->getRelatedData($dataID);
			return $dataOutput;
		
		}
		
		
		// Anzahl an Datenobjekten ermitteln
		$this->objectNumber	= self::getDataObjectNumber($this->queryData[0]);
		
		// Falls Daten-Objekte vorhanden
		if($this->objectNumber > 0)
			$this->objOutput	= $this->getDataObjects($this->queryData[0]);
		

		// db-Query Prev/Next data
		$this->queryPrevAndNextData();
		
		
		// Page image src
		if(!empty($this->teaserImgSrc))
			$this->o_page->pageImage		= $this->teaserImgSrc;


		// Event
		$this->o_outputExtendDataEvent->queryData		= $this->queryData;
		$this->o_outputExtendDataEvent->queryNextData	= $this->queryNextData;
		$this->o_outputExtendDataEvent->queryPrevData	= $this->queryPrevData;
		$this->o_outputExtendDataEvent->backendLog		= $this->backendLog;
		$this->o_outputExtendDataEvent->targetUrl		= $this->targetUrl;		
		$this->o_outputExtendDataEvent->dataID			= $this->dataID;		
		$this->o_outputExtendDataEvent->catID			= $this->catID;		
		$this->o_outputExtendDataEvent->urlCatPath		= $this->urlCatPath;		
		$this->o_outputExtendDataEvent->queryString		= $this->queryString;		
		$this->o_outputExtendDataEvent->objOutput		= $this->objOutput;
		$this->o_outputExtendDataEvent->dataObjects		= $this->dataObjects;
		$this->o_outputExtendDataEvent->objectNumber	= $this->objectNumber;
		$this->o_outputExtendDataEvent->pageKeywords	= $this->o_page->pageKeywords;		
		$this->o_outputExtendDataEvent->pageDescr		= $this->o_page->pageDescr;		
		$this->o_outputExtendDataEvent->pageImage		= $this->o_page->pageImage;		
		$this->o_outputExtendDataEvent->tokenInput		= parent::getTokenInput();
		
		// dispatch event get_data_details
		$this->o_dispatcher->dispatch('detail.get_data_details', $this->o_outputExtendDataEvent);
		
		$this->scriptFiles	= array_merge($this->scriptFiles, $this->o_outputExtendDataEvent->scriptFiles);
		$this->cssFiles		= array_merge($this->cssFiles, $this->o_outputExtendDataEvent->cssFiles);
		$this->scriptCode	= array_merge($this->scriptCode, $this->o_outputExtendDataEvent->scriptCode);

		
		// HTML-Titel um Datentitel erweitern
		if($this->o_outputExtendDataEvent->dataHeader != "" && isset($GLOBALS['_GET']['id']))
			$this->o_page->pageTitle	= $this->o_outputExtendDataEvent->dataHeader . " - " . $this->o_page->pageTitle;
		
		// Keywords
		$this->o_page->pageKeywords		= $this->o_outputExtendDataEvent->pageKeywords;
		
		// Description
		$this->o_page->pageDescr		= $this->o_outputExtendDataEvent->pageDescr;
		
		// Page image src
		$this->o_page->pageImage		= $this->o_outputExtendDataEvent->pageImage;

		
		// Canonical url, falls veraltete Url
		if(!empty($this->o_outputExtendDataEvent->dataPath)
		&& ($this->o_outputExtendDataEvent->dataPath != PROJECT_HTTP_ROOT . $GLOBALS['_SERVER']['REQUEST_URI']
			|| strpos($GLOBALS['_SERVER']['REQUEST_URI'], "?") !== false)
		)
			$this->o_page->canonicalUrl	= $this->o_outputExtendDataEvent->dataPath;


		// Pfad an Breadcrumb-Navi anhängen
		parent::$dataBCPath[] = '<li><a href="' . $this->o_outputExtendDataEvent->dataUrl . '">' . $this->o_outputExtendDataEvent->dataHeader . '</a></li>';				

		
		
		// Ggf. Kommentare einbinden
		if(in_array($this->dataTable, $this->dataTables)
		|| $this->comments == 1
		&& (in_array("public", $this->readCommentsGroups) || in_array($this->group, $this->readCommentsGroups))
		) {
		
			// Falls Editor und FE-Mode, TinyMCE einbinden
			if($this->editorLog && parent::$feMode){
				$this->scriptFiles[]	= "extLibs/tinymce/tinymce.min.js";
				$this->scriptFiles[]	= "system/access/js/myFileBrowser.js";
				$this->scriptFiles[]	= "system/access/js/myTinyMCE.comments.js";
			}
		
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";
			
			$o_comments		= new Comments($this->DB, $this->o_lng, $this->modType, $dataID, $this->o_outputExtendDataEvent->dataPath, $this->group, $this->readCommentsGroups, $this->writeCommentsGroups, true);
			$commentOutput	= $o_comments->getComments($this->dataTable, $dataID);
			$o_comments->getCommentNumber($this->dataTable, $dataID);
			$totComments	= $o_comments->totComments;
		}
		
		// Ggf. Rating einbinden
		if($this->o_outputExtendDataEvent->dataRating == 1
		&& $this->rating != 0
		&& (in_array("public", $this->ratingGroups) || in_array($this->group, $this->ratingGroups))
		) {
			self::installRater();
			$o_rating		= new Rating($this->DB);
			$this->mergeHeadCodeArrays($o_rating);
			$ratingOutput	= $o_rating->getStarRater($this->modType, $this->catID, $dataID);
		}
		

		// Social sharing bar
		$socialBar	=	'<div class="socialBar">' . "\r\n" .
						$this->getSocialBar(SOCIAL_BAR, $this->o_outputExtendDataEvent->dataUrl, $this->o_page->pageTitle, $this->o_page->pageDescr, $this->o_page->pageKeywords, $this->o_page->pageImage) . // Social bar
						'</div>' . "\r\n";

		
		// Comments/Rating/Social
		$this->o_outputExtendDataEvent->commentOutput		= $commentOutput;		
		$this->o_outputExtendDataEvent->ratingOutput		= $ratingOutput;		
		$this->o_outputExtendDataEvent->socialBar			= $socialBar;		
		

		// Falls backendLog, EditButtons einbinden
		if($this->backendLog && $this->hasDataEditAccess($this->o_outputExtendDataEvent->queryData[0]))	{		
			$this->o_outputExtendDataEvent->adminIcons		= $this->getDataEditButtonPanel($this->o_outputExtendDataEvent->queryData[0]);
			$this->dataEditAccess							= true;
		}
		else
			$this->dataEditAccess							= false;

		
		// Event
		// dispatch event assign_data_details
		$this->o_dispatcher->dispatch('detail.assign_data_details', $this->o_outputExtendDataEvent);
		// dispatch event assign_data_objects
		$this->o_dispatcher->dispatch('detail.assign_data_objects', $this->o_outputExtendDataEvent);

		
		// Artikelaufruf zählen, falls kein Bot oder Author
		$this->logDataCall($dataIdDb);


		// Template ausgeben
		$dataOutput .= $tpl_data->getTemplate();
		
		
		return $dataOutput;
		
	}
	
	
			
	/**
	 * Methode zur Ausgabe von Moduldaten (Artikel, News, Termine)
	 * 
     * @param	string Zielseite
	 * @access	boolean Seitennavigation (Pagination) einfügen, falls true
	 * @access	boolean Zurückbuttons zur Kategorieteaser-Seite einfügen, falls true
	 * @return	array
	 */
	public function getModuleData($pageNav = true, $backToCat = false)
	{
		
		$tplPrefix = $this->displayMode;
		

		switch($this->displayMode) {
		
			case "list":
				$this->dbOrder	= str_replace("ORDER BY", "ORDER BY dt.`featured` DESC,", $this->dbOrder);
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "10");
				$maxRows	= $this->limit != "" ? $this->limit : "10";
				$classExt	= "List";
				break;
				
			case "latest":
				$this->dbOrder	= str_replace("ORDER BY", "ORDER BY dt.`featured` DESC,", $this->dbOrder);
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "1");
				$maxRows	= $this->limit != "" ? $this->limit : "1";
				$classExt	= "Latest";
				break;
				
			case "expired":
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "10");
				$maxRows	= $this->limit != "" ? $this->limit : "10";
				$classExt	= "Past";
				$tplPrefix	= "past";
				break;
									
			case "teaser":
				$this->dbOrder	= str_replace("ORDER BY", "ORDER BY dt.`featured` DESC,", $this->dbOrder);
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "5");
				$maxRows	= $this->limit != "" ? $this->limit : "5";
				$classExt	= "Teaser";
				break;
									
			case "detail":
				$this->dbOrder	= str_replace("ORDER BY", "ORDER BY dt.`featured` DESC,", $this->dbOrder);
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "5");
				$maxRows	= $this->limit != "" ? $this->limit : "5";
				$classExt	= "Detail";
				break;
									
			case "related":
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "5");
				$maxRows	= $this->limit != "" ? $this->limit : "5";
				$classExt	= "Related";
				break;
									
			case "popular":
				$this->dbOrder	= str_replace("ORDER BY", "ORDER BY dt.calls DESC,", $this->dbOrder);
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "5");
				$maxRows	= $this->limit != "" ? $this->limit : "5";
				$classExt	= "Popular";
				$tplPrefix	= "latest";
				break;
									
			case "rated":
				$this->dbOrder	= str_replace("ORDER BY", "ORDER BY rating.`rate` DESC, rating.`votes` DESC,", $this->dbOrder);
				$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "5");
				$maxRows	= $this->limit != "" ? $this->limit : "5";
				$classExt	= "List";
				$tplPrefix	= "rated";
				$this->selectExt .= ",rating.`votes`,rating.`rate`";
				break;
									
			case "random":
				$this->dbOrder = "ORDER BY RAND()";
				$queryLimit = "LIMIT ".($this->limit != "" ? $this->limit : "1");
				$maxRows = $this->limit != "" ? $this->limit : "1";
				$classExt = "Random";
				break;
									
		}
		
		
		// Clear before list
		$classExt .= " {t_class:clear}";
		
		
		// db-Query nach allen data
		$this->queryData = $this->DB->query("SELECT *" . $this->selectExt . ",UNIX_TIMESTAMP(dt.`date`)
											FROM `$this->dataTableDB` AS dt 
												LEFT JOIN `$this->catTableDB` AS dct 
												ON dt.`cat_id` = dct.`cat_id` 
												LEFT JOIN `" . DB_TABLE_PREFIX . "rating` AS rating 
												ON (rating.`module` = '$this->dataTable' AND dt.`id` = rating.`id` AND dt.`id` = rating.`id`) 
											WHERE 
											$this->published 
											AND $this->dbFilterGroup 
											$this->dbFilter 
											$this->dbFilterDate
											", false);
		
		#var_dump($this->queryData);
		
		
		$dataOutput			= "";
		$totalRows			= "";
		$startRow			= "";
		$pageNum			= 0;
		$uniqueID			= "";
		$frameClass	 		= "main";
		$parentCatTeaser	= "";
		$loopData			= "";
		$linkList			= "";
		$linkBack			= "";
		$dataCatLink		= "";
		$this->dataObjects	= array();
		$rewrite			= false;
        $queryString		= "";
		$filterData			= "";
        
			
		// Templatename
		$dataTpl		= "mod_tpls/".$tplPrefix."_".$this->useTpl.".tpl";
		$tpl_data		= new Template($dataTpl);
		$tpl_data->loadTemplate();
		
		
		if($tplPrefix != "single") {
			$dataLoopTpl	= "mod_tpls/".$tplPrefix."_".$this->useTpl."_loop.tpl";
			$tpl_loop		= new Template($dataLoopTpl);
			$tpl_loop->loadTemplate();
		}


		// Falls keine Daten vorhanden sind
		if(!is_array($this->queryData)
		|| count($this->queryData) == 0
		) {
		
			$hint	= $this->getNotificationStr('{s_notice:no' . $this->modType . '}', "hint", "permanent-notice");
			
			$tpl_data->assign("loop_data", $hint);
			$tpl_data->assign("class", $classExt . ($this->dataEditAccess ? " cc-edit-access" : ''));
			
			$dataOutput .= $tpl_data->getTemplate(); // Template ausgeben

			return $dataOutput;
		
		}
		

		// Falls Daten vorhanden sind
		$totalRows		= count($this->queryData);

		// Pagination
		$uniqueParam	= '-' . $this->modType[0] . $this->displayMode . $this->contentTable;
		
		if(isset($GLOBALS['_GET']['pageNum' . $uniqueParam])) {
			$pageNum = $GLOBALS['_GET']['pageNum' . $uniqueParam];
		}
		
		$startRow	= $pageNum * $maxRows;
		$queryLimit = " LIMIT " . $startRow . "," . $maxRows;

		// db-Query nach allen data
		$this->queryData = $this->DB->query("SELECT *, dt.`cat_id`, dt.`id`" . $this->selectExt . ", user.`author_name`
												FROM `$this->dataTableDB` AS dt 
													LEFT JOIN `$this->catTableDB` AS dct 
													ON dt.`cat_id` = dct.`cat_id` 
													LEFT JOIN `" . DB_TABLE_PREFIX . "rating` AS rating 
													ON (rating.`module` = '$this->dataTable' AND dt.`cat_id` = rating.`cat_id` AND dt.`id` = rating.`id`) 
													LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
													ON dt.`author_id` = user.`userid` 
												WHERE 
												$this->published  
												AND $this->dbFilterGroup 
												$this->dbFilter 
												$this->dbFilterDate 
												$this->dbOrder 
												$queryLimit
												", false);
					
		#var_dump($this->queryData);
		
		if(isset($this->notice) && $this->notice != "" && ($this->displayMode == "detail" || $this->displayMode == "list"))
			$dataOutput .=	$this->getNotificationStr($this->notice, "success");
		
		$this->notice = "";
		

		// Falls nur Datensätze mit bestimmtem Tag angezeigt werden Überschrift hinzufügen
		if(isset($GLOBALS['_GET']['tag']) && $GLOBALS['_GET']['tag'] != "" && $this->isMainContent && $this->displayMode == "list") {
			$tagString	=	htmlspecialchars(html_entity_decode(urldecode($GLOBALS['_GET']['tag'])));
			$filterData =	'<div class="filterTags {t_class:row}">' . "\r\n" .
							'<h2 class="tags {t_class:3quaters}">{s_header:'.$this->modType.'tags} &quot;' . $tagString . '&quot; <span>(' . htmlspecialchars($totalRows) . '&nbsp;{s_label:'.$this->modType.'})</span></h2>' . "\r\n" .
							'<div class="removeFilter {t_class:quaterrow} {t_class:alignrgt}">' . PHP_EOL;
			
			// Button remove filter
			$btnDefs	= array(	"href"		=> $this->targetUrl . PAGE_EXT,
									"class"		=> '{t_class:btndef} {t_class:btnsm}',
									"text"		=> '{s_text:remfilter}: ' . $tagString . '&nbsp;',
									"icon"		=> "close",
									"icontext"	=> ""
								);
				
			$filterData .=	parent::getButtonLink($btnDefs, "right");
							
			$filterData .=	'</div>' . "\r\n" .
							'</div>' . "\r\n";
		
			// Tag für HTML-Titel übernehmen, falls kein einzelner Artikel
			$this->o_page->htmlTitlePrefix			= $tagString . " - ";
			$this->o_page->pageDescr				= $tagString . " - " . $this->o_page->pageDescr;
			$this->o_page->pageKeywords				= $tagString . ", " . $this->o_page->pageKeywords;

		}
		
		
		// Falls nur Datensätze einer bestimmtem Kategorie angezeigt werden, Überchrift hinzufügen
		elseif(	isset($GLOBALS['_GET']['cid']) && $GLOBALS['_GET']['cid'] != ""  && 
				!isset($GLOBALS['_GET']['id']) && 
				$this->isMainContent && 
				$this->displayMode == "list" && 
				$this->catID != $this->rootCatID
		) {
			$filterData =	'<div class="filterTags {t_class:row}">' . "\r\n" .
							'<h2 class="tags {t_class:3quaters}">{s_text:category} &quot;' . htmlspecialchars($this->queryData[0]['category_' . $this->lang]) . '&quot; <span>(' . htmlspecialchars($totalRows) . '&nbsp;{s_label:'.$this->modType.'})</span></h2>' . "\r\n" .
							'<div class="removeFilter {t_class:quaterrow} {t_class:alignrgt}">' . PHP_EOL;
			
			// Button remove filter
			$btnDefs	= array(	"href"		=> $this->targetUrl . PAGE_EXT,
									"class"		=> '{t_class:btndef} {t_class:btnsm}',
									"text"		=> $this->queryData[0]['category_' . $this->lang] . '&nbsp;',
									"icon"		=> "close",
									"icontext"	=> ""
								);
				
			$filterData .=	parent::getButtonLink($btnDefs, "right");
							
			$filterData .=	'</div>' . "\r\n" .
							'</div>' . "\r\n";
		}


		$k = 1;
		
		foreach($this->queryData as $dataEntry) {
			
			$dataID				= htmlspecialchars($dataEntry['id']);
			$catID				= htmlspecialchars($dataEntry['cat_id']);
			$dataSortID			= htmlspecialchars($dataEntry['sort_id']);
			$parentCatID		= htmlspecialchars($dataEntry['parent_cat']);
			$dataCat			= htmlspecialchars($dataEntry['category_' . $this->lang]);
			$dataAuthor			= htmlspecialchars($dataEntry['author_name']);
			$dataFeatured		= htmlspecialchars($dataEntry['featured']);
			$date				= htmlspecialchars(date("d.m.y", strtotime($dataEntry['date'])));
			$datetime			= date("Y-m-dTH:i:s", strtotime(htmlspecialchars($dataEntry['date'])));
			$dataDay			= htmlspecialchars(date("d.", strtotime($dataEntry['date'])));
			$dataYear			= htmlspecialchars(date("Y", strtotime($dataEntry['date'])));
			$dataMon			= "{s_date:" . htmlspecialchars(strtolower(date("M", strtotime($dataEntry['date'])))) . "}";
			#$dataMon			= htmlspecialchars(ucfirst(date("M", strtotime($dataEntry['date']))));
			$dataDate			= self::getDateString($date, $dataDay, $dataYear, $dataMon); // Überprüfung ob heute, sonst Datumsausgabe
			if($this->modType != "planner")
				$dataModDate 	= htmlspecialchars($dataEntry['mod_date']);
			else
				$dataTime		= htmlspecialchars($dataEntry['time']);
			$catImg				= $this->getCatImageParams($dataEntry['image']);
			$dataCatImg			= "";
			$dataHeader			= htmlspecialchars($dataEntry['header_' . $this->lang]);
			$dataAlias			= htmlspecialchars(self::getAlias($dataHeader));
			$dataCatAlias		= htmlspecialchars(self::getAlias($dataCat));
			$targetPageID		= htmlspecialchars($dataEntry['target_page']);
			$this->targetUrl	= PROJECT_HTTP_ROOT . '/' . htmlspecialchars(HTML::getLinkPath($targetPageID, "current", false));
			$parentCatTeaser	= $dataEntry['cat_teaser_' . $this->lang];
			$dataTeaser			= $dataEntry['teaser_' . $this->lang];
			$dataTeaserList		= "";
			$dataText			= $dataEntry['text_' . $this->lang];
			
			// Falls kein Teaser, erste Textzeile nehmen
			if($dataTeaser == "") {
			
				$dataTeaserList = preg_replace('/\r|\n/uim', '', $dataText);
				$dataTeaserList = preg_replace('/<p>[\s]*<\/p>/uim', '', $dataTeaserList);
				$dataTeaserList = preg_replace('/^(.*(?!p>))(<p>(.*(?!\/p>))<\/p>).*$/muisU', "$3", $dataTeaserList);
				#$dataTeaserList = substr($dataTeaserList, strpos($dataTeaserList, '<p>', 0), strpos($dataTeaserList, '</p>', strpos($dataTeaserList, '<p>')));
				#$dataTeaserList = str_replace('<br />', '', $dataTeaserList);
				if(strpos($dataTeaserList, '<br') !== false)
					$dataTeaserList = substr($dataTeaserList, 0, strpos($dataTeaserList, '<br'));
				$dataTeaserList = strip_tags($dataTeaserList, '<p>,<div>,<strong>,<em>,<i>,<b>,<img>');
				if(strlen($dataTeaserList) > 100) {
					$dataTeaserList = substr($dataTeaserList, 0, 100) . substr($dataTeaserList, 100, max(101, strpos($dataTeaserList, '.')));
					$dataTeaserList = strpos($dataTeaserList, ".") !== strlen($dataTeaserList) ? substr($dataTeaserList, 0, strrpos($dataTeaserList, ' ')) : $dataTeaserList;
				}
				if(strrpos($dataTeaserList, "</") < strrpos($dataTeaserList, "<"))
					$dataTeaserList .= '</' . substr($dataTeaserList, strrpos($dataTeaserList, "<") +1, strrpos($dataTeaserList, ">") - strrpos($dataTeaserList, "<"));
				$dataTeaserList .= ' (...)</p>' . "\r\n";
			}
			
			// Falls der Teaser kein Html enthält, den Inhalt in einen p-tag packen
			elseif(strpos($dataTeaser, "<") !== 0) {
				$dataTeaser	= strip_tags($dataTeaser, '<p>,<div>,<strong>,<em>,<i>,<b>');
				$dataTeaser = '<p class="dataTeaser">' . nl2br($dataTeaser) . '</p>' . "\r\n";
				$dataTeaserList	= $dataTeaser;
			}
			else
				$dataTeaserList	= strip_tags($dataTeaser, '<p>,<div>,<strong>,<em>,<i>,<b>');
			
			// Platzhalter entfernen (bei nächstem replaceStaText)
			$dataTeaserList = str_replace("{#", "{__", $dataTeaserList);
			
			
			// Falls Author leer ist, unbekannt ausgeben
			if($dataAuthor == "")
				$dataAuthor = "{s_common:unknown}";
			
			if($this->modType == "articles") {
				$orderOpt	= $dataEntry['order_opt'];
				$dataPrice	= parent::getPrice($dataEntry['price'], $this->lang);
			}
			else
				$dataPrice	= "";
			
			$dataOrder		= "";
			$dataRating		= $dataEntry['rating'];
			$commentOutput	= "";
			$totComments	= "";
			$ratingOutput	= "";
			$this->dataObjects		= array();
			$this->objOutput		= array();
			$this->teaserImg		= "";
			$linkList		= "";
			$nextData		= "";
			$prevData		= "";
			$plannerPast	= "";
			$parentCatAlias = $this->getParentCat($parentCatID, "alias");
			$adminIcons		= "";
			$more			= "{s_link:more}";
			
			// Platzhalterschutz (#) entfernen
			$dataText = str_replace("{#", "{", $dataText);
			
			
			// Falls FE-Editing aktiviert ist, Buttons einbinden
			if(parent::$feMode && $this->hasDataEditAccess($dataEntry))	{
				$adminIcons				=	self::getDataEditButtonPanel($dataEntry);
				$this->dataEditAccess	= true;
			}
			else
				$this->dataEditAccess	= false;

			
			if(USE_CAT_NAME && $this->urlCatPath == "") {
			
				if($dataCatAlias !== false)
					$this->urlCatPath = $dataCatAlias . '/';
				elseif($parentCatAlias !== false)
					$this->urlCatPath = $parentCatAlias . '/';
			}

			
			// Data path
			$dataPath			= $this->targetUrl . '/' . $this->urlCatPath . $dataAlias . '-' . $catID . $this->modType[0] . $dataID . PAGE_EXT;
			
			
			// Kategorienamen für HTML-Titel übernehmen, falls kein einzelner Artikel und falls Unterkategorie
			if(isset($GLOBALS['_GET']['cid']) && !isset($GLOBALS['_GET']['id']) && $parentCatID > 0 && $this->isMainContent && $k == 1)
				$this->o_page->htmlTitlePrefix .= $dataCat . " - ";
			
			
			// Preisangabe bei Artikeln
			if($this->modType == "articles" && $orderOpt == 1) {

				// Button addToCart
				$btnDefs	= array(	"type"		=> "submit",
										"class"		=> 'addToCart button-icon-only button-small {t_class:btnsuc} {t_class:right}',
										"text"		=> "",
										"title"		=> '{s_title:addtocart}',
										"icon"		=> "cart"
									);
				
				$addToCartBtn	=	parent::getButton($btnDefs);
				
				$dataOrder	=	'<div class="addToCart">' . "\r\n" .
								'<form method="post" action="" class="{t_class:forminl}">' . "\r\n" .
								'<fieldset>' . "\r\n" .
								'<span class="{t_class:fieldrow}">' . "\r\n" .
								'<input type="hidden" name="cat_id" value="' . $catID . '" />' . "\r\n" .
								'<input type="hidden" name="data_id" value="' . $dataID . '" />' . "\r\n" .
								'<input type="hidden" name="addToCart" value="true" />' . "\r\n" .
								parent::getTokenInput() .
								$addToCartBtn . 
								'<input type="text" name="amount" value="1" class="inputAmount {t_class:input} {t_class:fieldinl}" />' . "\r\n" .
								'<label>' . $dataPrice . ' &euro;</label>' .
								'</span>' . "\r\n" .
								'</fieldset>' . "\r\n" .
								'</form>' . "\r\n" .
								'</div>' . "\r\n";
			}
			
			
			// Ggf. Kommentare einbinden
			if(in_array($this->dataTable, $this->dataTables) || $this->comments == 1 && (in_array("public", $this->readCommentsGroups) || in_array($this->group, $this->readCommentsGroups))) {
			
				require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";
				$o_comments		= new Comments($this->DB, $this->o_lng, $this->modType, $dataID, $dataPath, $this->group, $this->readCommentsGroups, $this->writeCommentsGroups);
				$commentOutput	= $o_comments->getCommentNumber($this->dataTable, $dataID);
				$totComments	= $o_comments->totComments;
			}
			
			
			// Ggf. Rating einbinden
			if($dataRating == 1
			&& $this->rating != 0
			&& (in_array("public", $this->ratingGroups) || in_array($this->group, $this->ratingGroups))
			) {
				self::installRater();
				$o_rating		= new Rating($this->DB);
				$this->mergeHeadCodeArrays($o_rating);

				if($this->displayMode == "detail")
					$enable	= true;
				else
					$enable	= false;
				
				$ratingOutput	= $o_rating->getStarRater($this->modType, $catID, $dataID, $enable);
			}
			
				
			// Pfad zur Bilddatei
			if($catImg[0] != "") {
			
				$srcStr			= $this->getSourcePath($catImg[0]);
			
				// Kategoriebild
				$dataCatImg		= '<img src="' . $srcStr . '" alt="' . $catImg[1] . '" title="' . $catImg[2] . '" class="catImage" />' . "\r\n";
			}
			
			
			// Link zur Detailansicht
			$this->dataLink		= $this->targetUrl . '/' . $this->urlCatPath;
			$this->dataLink		= $this->dataLink . $dataAlias . '-' . $catID . $this->modType[0] . $dataID . PAGE_EXT;
			$this->dataLink		= '<a href="' . $this->dataLink . ($this->queryString != "" ? '?' . $this->queryString : '') . '" class="dataLink">' . "\r\n";
			$linkList			= $this->targetUrl . PAGE_EXT . ($this->queryString != "" ? '?' . $this->queryString : '');
		

			// Zurücklink
			$linkBack		= $this->getCatBackButton(parent::$currentURL . ($this->queryString != "" ? '?' . $this->queryString : ''));
			
			
			
			// Anzahl an Datenobjekten ermitteln
			$this->objectNumber	= self::getDataObjectNumber($dataEntry);

			
			// Falls Daten-Objekte vorhanden
			if($this->objectNumber > 0)
				$this->objOutput	= $this->getDataObjects($dataEntry);
			
			
			// Falls kein Bild aus Objekten festgelegt, versuchen aus Text zu extrahieren
			if($this->teaserImg == "" && strpos($dataText, "<div class=\"imgObj") === 0) {
				$this->teaserImg = substr($dataText, 0, strpos($dataText, "<p class=\"caption")) . '</div>';
				$this->teaserImg = str_replace("512", "128", $this->teaserImg);
				$this->teaserImg = str_replace("256", "128", $this->teaserImg);
				$this->teaserImg = str_replace("width=\"128", "width=\"auto", $this->teaserImg);
				$this->teaserImg = strip_tags($this->teaserImg, '<div>,<img>');
				$this->teaserImg = $this->dataLink . $this->teaserImg . '</a>';
			}
			elseif($this->teaserImg != "") {
				$this->teaserImg = '<div class="imgObj teaserImg dataObjects">' . $this->teaserImg . '</div>';
			}				
			

			// classExt
			if($this->dataEditAccess)
				$classExt .=  " cc-edit-access";
			
			// entryClassExt
			$entryClassExt		= $k % 2 ? "" : "alternate";
			
			if($dataFeatured)
				$entryClassExt .=  " cc-data-featured";
			
			
			
			if($tplPrefix != "single") {

				$loop_tpl	= clone $tpl_loop;
				
				// Platzhalterersetzungen
				$loop_tpl->assign("author", $dataAuthor);
				$loop_tpl->assign("dataHeader", $dataHeader . $adminIcons);
				$loop_tpl->assign("dataDate", $dataDate);
				$loop_tpl->assign("datetime", $datetime);
				$loop_tpl->assign("dataDay", $dataDay);
				$loop_tpl->assign("dataMonth", $dataMon);
				$loop_tpl->assign("dataYear", $dataYear);
				$loop_tpl->assign("dataTeaser", $dataTeaser);
				$loop_tpl->assign("dataTeaserList", $dataTeaserList);
				$loop_tpl->assign("dataText", $dataText);
				$loop_tpl->assign("teaserImg", $this->teaserImg);
				$loop_tpl->assign("dataLink", $this->dataLink);
				$loop_tpl->assign("more", $more);
				$loop_tpl->assign("class", $classExt);
				$loop_tpl->assign("plannerPast", $plannerPast);
				$loop_tpl->assign("object_all", implode("", $this->objOutput));
				$loop_tpl->assign("objOutput", implode("", $this->objOutput));
				$loop_tpl->assign("dataOrder", $dataOrder);
				$loop_tpl->assign("rating", $ratingOutput);
				$loop_tpl->assign("comments", $commentOutput);
				$loop_tpl->assign("totComments", $totComments);
				$loop_tpl->assign("dataCatImg", $dataCatImg);
				$loop_tpl->assign("altClass", $entryClassExt);
				
				for($o = 1; $o <= $this->objectNumber; $o++) {
					$loop_tpl->assign("object_".$o, $this->objOutput[$o]);
					$loop_tpl->assign("objOutput_".$o, $this->objOutput[$o]);
				}

				$loopData .= $loop_tpl->getTemplate() . "\r\n"; // Template ausgeben
			}
			
			$k++;
			
		} // Ende foreach
			
		
		// Elternkategorien
		if(!empty($parentCatID)) {
		
			$this->getParentCats($this->catID, $this->rootCatID);
			
			// DataCatLink
			if(!empty($this->linkBackCat)) {
				$dataCatLink	= $this->linkBackCat;
			}
			elseif(!empty(parent::$parentAliases))
				$dataCatLink	= $this->getCatBackButton(HTML::getLinkPath($this->getParentCat($parentCatID, "targetPage"), "current", true, true));
			else
				$dataCatLink	= $linkBack;
		}
		
	
		// Pagination generieren
		if($pageNav === true && $maxRows < $totalRows) {
						
			// Falls Kategoriefilter
			if(isset($GLOBALS['_GET']['cid'])) {
				$rewrite = true;
				$queryString = $this->targetUrl . '/' . str_replace("/", "", $this->urlCatPath) . '-' . $catID . $this->modType[0] . PAGE_EXT;
			}
			// Falls ein Tag zur Eingrenzung
			elseif(isset($GLOBALS['_GET']['tag'])) {
				$queryString = 'tag=' . urlencode(htmlspecialchars($GLOBALS['_GET']['tag'])) . (isset($GLOBALS['_GET']['src']) ? '&src=' . htmlspecialchars($GLOBALS['_GET']['src']) : '');
			}
			$pageNav = self::getPageNav($maxRows, $totalRows, $startRow, $pageNum, $queryString, $uniqueParam, $rewrite) . "\r\n";
			$tpl_data->assign("pageNav", $pageNav);
		}


		if($this->displayMode == "popular" || $this->displayMode == "random")
			$modHeader = "{s_header:".$this->displayMode.$this->modType."}";
		elseif(isset($dataCat))
			$modHeader = $dataCat;
		else
			$modHeader = "";


		
		// Werbeanzeigen
		#$adBlock		= self::getAdBlock();
		
		
		$tpl_data->assign("class", $classExt . ($this->dataEditAccess ? " cc-edit-access" : ''));
		$tpl_data->assign("reldata", "{s_header:rel".$this->modType."}");
		$tpl_data->assign("filterData", $filterData);
		$tpl_data->assign("modHeader", $modHeader);
		$tpl_data->assign("catName", $dataCat);
		$tpl_data->assign("parentCatTeaser", $parentCatTeaser != "" && !isset($GLOBALS['_GET']['tag'])? '<div class="parentCatTeaser">' . $parentCatTeaser . '</div>' . "\r\n" : '');
		$tpl_data->assign("linkList", $linkList);
		$tpl_data->assign("linkBack", $linkBack);
		$tpl_data->assign("dataCatLink", $dataCatLink);
		$tpl_data->assign("tplpath", JS_DIR);
		#$tpl_data->assign("adBlock", $adBlock);
		$tpl_data->assign("loop_data", $loopData);
		$tpl_data->assign("#root", PROJECT_HTTP_ROOT);
		$tpl_data->assign("root", PROJECT_HTTP_ROOT);
		$tpl_data->assign("#root_img", IMAGE_DIR);
		$tpl_data->assign("root_img", IMAGE_DIR);
		$tpl_data->replaceTplStaText();
		
		if(count($this->dataObjects) > 0) {
			
			if($this->dataObjects[1]["type"] == "img") {
				$this->objOutput[1] = "";
			}
			else {
				$tpl_data->assign("object_img", "");
				$tpl_data->assign("objOutput_img", "");
			}
		}

		$dataOutput .= $tpl_data->getTemplate(); // Template ausgeben

		return $dataOutput;
	
	}
	


			
	/**
	 * Gibt ein Artikelmenu bzw. Kategorieteaser zurück
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getDataMenu()
	{
	
		$dataOutput		= "";
		$currOutput		= "";
		$parentOutput 	= "";
		$menuPrefix 	= "";
		$classExt		= "data";
		$differQS		= "";
		$dbCatFilter	= "`parent_cat` = " . $this->DB->escapeString($this->catID);
		$this->dbOrder	= str_replace("dt.`sort_id`", "dct.`sort_id`", $this->dbOrder);
		$more			= "{s_link:more}";
		
		
		// Zurücklink
		$this->linkBackCat		= $this->getCatBackButton(parent::$currentURL);
		

		if(($this->displayMode == "articlemenu" || $this->displayMode == "catmenu") && $this->rootCatID != "<all>") {
			
			$queryLimit	= "LIMIT ".($this->limit != "" ? $this->limit : "100");
			$classExt	= "Menu";
			$maxRows	= $this->limit != "" ? $this->limit : 100;
			$menuPrefix = MOD_MENU_PREFIX;
			$differQS 	= "Cat";
			
			// Elternkategorien
			$this->getParentCats($this->catID, $this->rootCatID);
		
		}
		
		elseif($this->displayMode != "catteaser" && $this->rootCatID == "<all>") {
			
			$dbCatFilter	= "`cat_id`!= '' AND `parent_cat` = 0";
			$queryLimit		= "LIMIT ".($this->limit != "" ? $this->limit : "20");
			$classExt		= "catMenu";
			$maxRows		= $this->limit != "" ? $this->limit : 20;
		
		}
		
		elseif($this->displayMode == "catteaser") {
			
			// Elternkategorien
			if($this->rootCatID != "<all>")
				$this->getParentCats($this->catID, $this->rootCatID);
			
			$queryLimit			= "LIMIT ".($this->limit != "" ? $this->limit : "20");
			$classExt			= "catTeaser";
			$maxRows			= $this->limit != "" ? $this->limit : 20;
			$parentCat			= $this->getParentCat($this->catID);
			$parentCatID		= $parentCat[0]['parent_cat'];
			$parentCatName		= $parentCat[0]['category_' . $this->lang];
			#$parentCatAlias	= $this->getParentCat($this->catID, "alias");
			$parentCatTeaser	= $parentCat[0]['cat_teaser_' . $this->lang];
			$parentCatImage		= $this->getCatImageParams($parentCat[0]['image']);
			
			if($parentCatImage[0] != "") {
			
				// Pfad zur Bilddatei
				$srcStr			= $this->getSourcePath($parentCatImage[0]);
				
				$parentCatImage =	'<div class="imgObj {t_class:halfrow}">' .
									($this->html5 ? '<figure>' : '') .
									'<img class="dataCatImage" src="' . $srcStr . '" alt="' . $parentCatImage[1] . '" title="' . $parentCatImage[2] . '" />' . "\r\n" .
									'<' . ($this->html5 ? 'figcaption' : 'p') . ' class="caption">' . $parentCatImage[3] . '</' . ($this->html5 ? 'figcaption' : 'p') . '>' .
									($this->html5 ? '</figure>' : '') .
									'</div>' . "\r\n";
			}
			else
				$parentCatImage	= "";
			
			$dataTpl			= "mod_tpls/catteaser_".$this->useTpl.".tpl";
			$tpl_data			= new Template($dataTpl);
			$tpl_data->loadTemplate();
			$dataLoopTpl		= "mod_tpls/catteaser_".$this->useTpl."_loop.tpl";
			$tpl_loop			= new Template($dataLoopTpl);
			$tpl_loop->loadTemplate();
			$loopData			= "";
		}
		
		
		// db-Query nach Kindkategorien
		$queryCatArchiveAll = $this->DB->query( "SELECT * 
														FROM `$this->catTableDB` AS dct 
														WHERE $dbCatFilter
														AND $this->dbFilterGroup 
														", false);
		#var_dump($queryCatArchiveAll);
		
		if(count($queryCatArchiveAll) > 0) {
			
			$totalRows = count($queryCatArchiveAll);
			$startRow = "";
			$pageNum = 0;

			
			if(strpos($this->dbOrder, "date"))
				$this->dbOrder = " ORDER BY dct.`sort_id` ASC";

			// Pagination
			if(isset($GLOBALS['_GET']['pageNum']) && $this->displayMode != "articlemenu")
				$pageNum = $GLOBALS['_GET']['pageNum'];
			
			elseif(isset($GLOBALS['_GET']['pageNumCat']) && $this->displayMode == "articlemenu")
				$pageNum = $GLOBALS['_GET']['pageNumCat'];
			
			$this->dbOrder	= str_replace("header_", "category_", $this->dbOrder);
			$startRow 	= $pageNum * $maxRows;
			$queryLimit	= " LIMIT " . $startRow . "," . $maxRows;
		
			$queryChildCats = self::getChildCats($this->catID, $queryLimit);
			
			$k = 0;
			
			foreach($queryChildCats as $childCat) {
	
				$catID			= htmlspecialchars($childCat['cat_id']);
				$dataCat		= $childCat['category_' . $this->lang];
				$dataCatAlias	= self::getAlias($dataCat);
				$dataCat		= htmlspecialchars($dataCat);
				$chParentCatID	= htmlspecialchars($childCat['parent_cat']);
				$dataCatTeaser	= $childCat['cat_teaser_' . $this->lang];
				$catImg			= $this->getCatImageParams($childCat['image']);
				$dataCatImg		= "";
				$chTargetPageID	= $childCat['target_page'];
				
				if($chTargetPageID != $this->targetPageID)
					$catLink	= '<a href="' . HTML::getLinkPath($chTargetPageID, "current", true, true) . '" class="dataLink catLink">' .
								$menuPrefix;
				else
					$catLink	= '<a href="' . $this->targetUrl . '/' . $this->urlCatPath . $dataCatAlias . '-' . $catID . $this->modType[0] . PAGE_EXT . '" class="dataLink catLink">' .
								$menuPrefix;
								
				// Falls der CatTeaser kein Html enthält, den Inhalt in einen p-tag packen
				if(strpos($dataCatTeaser, "<") !== 0)
					$dataCatTeaser = '<p class="catTeaser">' . nl2br(htmlspecialchars($dataCatTeaser)) . '</p>' . "\r\n";

				// Pfad zur Bilddatei
				if($catImg[0] != "") {
				
					$srcStr			= $this->getSourcePath($catImg[0]);
				
					// Kategoriebild
					$dataCatImg		=	'<div class="imgObj teaserImg dataObjects">' . PHP_EOL .
										$catLink .
										'<img src="' . $srcStr . '" alt="' . $catImg[1] . '" title="' . $catImg[2] . '" class="catImage" />' . PHP_EOL .
										'</a>' .
										'</div>' . PHP_EOL;
				}
				
				if($this->displayMode == "catteaser") {
					
					$altClass = "item-" . ($k +1);
					
					// Style-Klasse alternate
					if($k % 2)
						$altClass .= " alternate";
					
					// Style-Klasse alpha
					if($k == 0 || is_int(($k - 3) / 3))
						$altClass .= " {t_class:alpha}";
					
					$loop_tpl	= clone $tpl_loop;
					
					// Platzhalterersetzungen
					$loop_tpl->assign("dataLink", $catLink);
					$loop_tpl->assign("dataCatImg", $dataCatImg);
					$loop_tpl->assign("catName", $dataCat);
					$loop_tpl->assign("catTeaser", $dataCatTeaser);
					$loop_tpl->assign("altClass", $altClass);
					$loop_tpl->assign("more", $more); // Platzhalter für "mehr"
					
					$loopData .= $loop_tpl->getTemplate(); // Template ausgeben
					
				}
				
				else			
					$currOutput .=	'<li>' . $catLink .
									$menuPrefix .
									$dataCat . '</a>' . "\r\n" .
									'</li>' . "\r\n";
				
				$k++;
				
			} // Ende foreach
			
			
			
			
			if($this->displayMode == "catteaser") {
            
				if(empty($parentCatID))
					$this->linkBackCat	= "";
				
				elseif($this->catID == $this->rootCatID
				&& !empty(parent::$parentAliases)
				)
					$this->linkBackCat	= $this->getCatBackButton(HTML::getLinkPath($this->getParentCat($parentCatID, "targetPage"), "current", true, true));
				
				
				$tpl_data->assign("listClass", $classExt . ($this->dataEditAccess ? " cc-edit-access" : ''));
				$tpl_data->assign("catName", $dataCat);
				$tpl_data->assign("parentCatName", $parentCatName);
				$tpl_data->assign("parentCatTeaser", $parentCatTeaser != "" ? '<div class="parentCatTeaser">{parentCatImage}' . $parentCatTeaser . '</div>' . "\r\n" : '');
				$tpl_data->assign("parentCatImage", $parentCatImage);
				#$tpl_data->assign("linkBackCat", $parentCatID <= 3 ? '' : $this->linkBackCat);
				$tpl_data->assign("linkBackCat", $this->linkBackCat);
				$tpl_data->assign("loop_data", $loopData);
				$tpl_data->replaceTplStaText();
                
			
				$dataOutput = $tpl_data->getTemplate(); // Template ausgeben
					
			}
			else {
								
				if(count($this->parentCats) > 0)
					$dataOutput .=	self::getCatMenu($this->parentCats, $parentCatID, $currOutput, $queryLimit);
				else {
					$dataOutput	 = '<ul class="dataMenu">' . PHP_EOL;
					$dataOutput .=	$currOutput;
					$dataOutput .=	'</ul>' . PHP_EOL;
				}
			}
			
			// Falls alle Data angezeigt werden sollen, Pagination generieren
			if(count($queryCatArchiveAll) > $this->limit && $this->displayMode != "catmenu") {
				
				$queryString = $this->targetUrl . (isset($GLOBALS['_GET']['cid']) ? '/' . str_replace("/", "", $this->urlCatPath) . '-' . $parentCatID . $this->modType[0] : '') . PAGE_EXT;
				$dataOutput .= self::getPageNav($maxRows, $totalRows, $startRow, $pageNum, $queryString, $differQS, true);
			}
			
		}
		
		elseif($this->displayMode != "catmenu") {
						
			// db-Query nach Artikeldaten
			$queryCatDataAll = $this->DB->query( "SELECT * 
														FROM `$this->dataTableDB` AS dt 
															LEFT JOIN `$this->catTableDB` AS dct 
															ON dt.`cat_id` = dct.`cat_id` 
														WHERE dct.`cat_id` = '$this->catID' 
														AND $this->dbFilterGroup 
														AND $this->published 
														", false);
			#var_dump($queryCatDataAll);
			
			$totalRows = count($queryCatDataAll);
			$startRow = "";
			$pageNum = 0;
			

			if($totalRows > 0) {
				
				// Sortierung auch nach Artikel.sort_id
				$this->dbOrder	= str_replace("dct.`sort_id`", "dct.`sort_id`, dt.`sort_id`", $this->dbOrder);
			
				// Pagination
				if(isset($GLOBALS['_GET']['pageNum']) && $this->displayMode != "articlemenu")
					$pageNum = $GLOBALS['_GET']['pageNum'];
				
				elseif(isset($GLOBALS['_GET']['pageNumCat']) && $this->displayMode == "articlemenu")
					$pageNum = $GLOBALS['_GET']['pageNumCat'];
				
				$startRow = $pageNum * $maxRows;
				$queryLimit = " LIMIT " . $startRow . "," . $maxRows;
			
				// db-Query nach Artikeldaten
				$queryCatData = $this->DB->query( "SELECT * 
														FROM `$this->dataTableDB` AS dt 
															LEFT JOIN `$this->catTableDB` AS dct 
															ON dt.`cat_id` = dct.`cat_id` 
														WHERE dct.`cat_id` = $this->catID 
														AND $this->dbFilterGroup 
														AND $this->published  
														$this->dbOrder 
														$queryLimit
														", false);
				#var_dump($queryCatData);
				
				if(count($queryCatData) > 0 && $this->displayMode == "catteaser") {
						
						$this->dbOrder		= str_replace("dct.`sort_id`", "dt.`sort_id`", $this->dbOrder);
						$this->displayMode	= "list";
						#$this->dbFilter		.= " AND `cat_id` = " . $queryCatData[0]['cat_id'];
						$dataOutput = self::getModuleData();
				}
				
				elseif(count($queryCatData) > 0 && $this->displayMode == "articlemenu") {
					
					foreach($queryCatData as $data) {
						
						$dataID = $data['id'];
						$header = $data['header_'.$this->lang];
						$alias	= self::getAlias($header);
						$header = htmlspecialchars($data['header_'.$this->lang]);
						
						$currOutput .=	'<li' . ($this->ID == $dataID ? ' class="active"' : '') . '>' . 
										MOD_MENU_PREFIX . 
                                        '<a href="' . $this->targetUrl . '/' . $this->urlCatPath . $alias . '-' . $this->catID . $this->modType[0] . $data['id'] . PAGE_EXT . '">' . $header . '</a></li>';
										#kein Link zu Artikel, nur zu Kategorien!!!
										#'<span>' . $header . '</span></li>';
										
						if($this->ID == $dataID)
							$this->activeParents[] = $currOutput;

					}
					
					$dataOutput	 = '<ul class="dataMenu">' . PHP_EOL;
					
					if(count($this->parentCats) > 0) {
						
						if(strpos($this->dbOrder, "date"))
							$this->dbOrder = " ORDER BY dct.`sort_id` ASC";
						elseif(strpos($this->dbOrder, "category"))
							$this->dbOrder	= str_replace("header_", "category_", $this->dbOrder);
						else
							$this->dbOrder = " ORDER BY dt.`sort_id` ASC";
						
						// Kategoriemenu erstellen
						$dataOutput .=	self::getCatMenu($this->parentCats, $this->catID, $currOutput, $queryLimit);
					}
					else
						$dataOutput .=	$currOutput;
					
					$dataOutput .=	'</ul>' . "\r\n";
					
				}
				
				// Falls alle Data angezeigt werden sollen, Pagination generieren
				if(count($queryCatDataAll) > $this->limit) {
					
					$queryString = $this->targetUrl . '/' . str_replace("/", "", $this->urlCatPath) . '-' . $this->catID  . $this->modType[0]. PAGE_EXT;
					
					// Pagination generieren
					$dataOutput .= self::getPageNav($maxRows, $totalRows, $startRow, $pageNum, $queryString, "", true);
				}
			}
			
			else {
				if(count($this->parentCats) > 0)
					$this->dbOrder	= str_replace("header_", "category_", $this->dbOrder);
			
				if(empty($parentCatID))
					$this->linkBackCat	= "";
				
				elseif($this->catID == $this->rootCatID
				&& !empty(parent::$parentAliases)
				)
					$this->linkBackCat	= $this->getCatBackButton(HTML::getLinkPath($this->getParentCat($parentCatID, "targetPage"), "current", true, true));
			
				// Kategoriemenu erstellen
				$dataOutput .= self::getCatMenu($this->parentCats, $this->catID, $currOutput, $queryLimit);
					
				$nodata	= $this->getNotificationStr('{s_notice:no' . $this->modType . '}', "hint", "permanent-notice");
				$dataOutput .=	($this->isMainContent ? $nodata . $this->linkBackCat : '');
			}
			
		}
		
		// Falls HTML5
		if($this->html5 && ($this->displayMode == "articlemenu" || $this->displayMode == "catmenu"))
			$dataOutput = '<nav>' . "\r\n" . $dataOutput . '</nav>' . "\r\n";
			
		return $dataOutput;
		
	}
	
	
	
	
	/**
	 * Gibt ein Kategoriearchiv/Kalender zurück
	 * 
     * @param	string Monatsanzeige
	 * @access	public
	 * @return	string
	 */
	public function getArchiveData($dataMonth = "")
	{
		
		$selExt			= "";
		$selExtYear		= "";
		$dataOutput		= "";
		$busyDates		= array();
		$busyDatesStr	= "";
		

		if(isset($this->notice) && $this->notice != "" && $this->displayMode != "related") {
			$dataOutput .=	$this->getNotificationStr($this->notice, "success");
			$this->notice = "";
		}

		// Falls Datepicker abgeschickt wurde
		if(isset($GLOBALS['_POST']['datepicker_pickedDate'])) {
			$dataDate = explode("/", $GLOBALS['_POST']['datepicker_pickedDate']);
			$pickedDate = $dataDate[2].",".$dataDate[0].",".$dataDate[1];
			$dataDate = $dataDate[2]."-".$dataDate[0]."-".$dataDate[1];
			$selDate = $dataDate;
		}
		else {
			$selDate = date("Y-m-d");
			$pickedDate = date("Y,m,d");
		}
		
		
		// Falls ein Kalender generiert werden soll
		if($this->displayMode == "calendar") {
		
			// Datepicker head files setzen
			$this->setDatePicker($this->themeConf);
		
			$dataOutput .=	'<div class="dataArchive">' . "\r\n" .
							'<form action="' . $this->targetUrl . PAGE_EXT . '" method="post" id="chooseDate">' . "\r\n" .
							'<fieldset>' . "\r\n" .
							'<input name="datepicker_pickedDate" type="hidden" value="'.$selDate.'" />' . "\r\n" .
							'<input name="datepicker_mod" type="hidden" value="'.$this->modType.'" />' . "\r\n" .
							'<input name="datepicker_cat" type="hidden" value="'.$this->catID.'" />' . "\r\n" .
							'<input type="hidden" id="daynames" value="{s_date:daynames}" alt="{s_date:daynamesmin}" />' . "\r\n" .
							'<input type="hidden" id="monthnames" value="{s_date:monthnames}" alt="{s_date:monthnamesmin}" />' . "\r\n";
							
							
			if($this->modType == "planner")
				$selExt		= "DATE(dt.`date_end`),";
			
			// db-Query nach Dataarchiven (Jahr)
			$queryCatArchive = $this->DB->query( "SELECT DATE(dt.`date`),".$selExt." dt.`cat_id`, dt.id 
														FROM `$this->dataTableDB` AS dt 
															LEFT JOIN `$this->catTableDB` AS dct 
															ON dt.`cat_id` = dct.`cat_id` 
														WHERE 
														$this->published 
														$this->dbFilter 
														AND $this->dbFilterGroup 
														ORDER BY DATE(date) DESC 
														", false);
			#var_dump($queryCatArchive);
			
			// Gefundene Daten in String für input-Feld speichern
			foreach($queryCatArchive as $busyDate) {
				
				$startDate	= $busyDate["DATE(dt.`date`)"];
				$busyDates[] = $startDate;
				
				if($this->modType == "planner") {
					
					$endDate = $busyDate["DATE(dt.`date_end`)"];
					
					if($endDate != $startDate) {
						
						$timestampStart = self::getTimestamp($startDate);
						$timestampEnd	= self::getTimestamp($endDate);
						
						$i = $timestampStart;
						
						while($i < $timestampEnd) {
						
							$i+= 3600 * 24;
							$busyDates[] = date("Y-m-d", $i);
							
						}
					}
				}
			}
			
			$busyDates = implode(",", array_unique($busyDates));
			
			$dataOutput .=	'<input type="hidden" id="busydates" value="'.$busyDates.'" alt="{s_text:currmonth}" />' . "\r\n" .
							'<input type="hidden" id="currDate" value="'.(isset($pickedDate) ? $pickedDate : '').'" />' . "\r\n" .
							'<div id="datepicker" class="'.$this->modType.'_calendar">' . "\r\n" .
							'</div>' . "\r\n" .
							'</fieldset>' . "\r\n" .
							'</form></div>' . "\r\n";

			
		}
		
		
		// Andernfalls Jahres-/Monatsarchiv
		else {
		
			if (isset($GLOBALS['_GET']['day']) && $GLOBALS['_GET']['day'] != "" && is_numeric($GLOBALS['_GET']['day'])) // Newsarchiv (Jahr)
				$year	= $GLOBALS['_GET']['day'];
			else
				$year	= date("Y", time());
			
			$yearDb		= $this->DB->escapeString($year);
			
			if (isset($GLOBALS['_GET']['dam']) && $GLOBALS['_GET']['dam'] != "" && is_numeric($GLOBALS['_GET']['dam'])) { // Newsarchiv (Monat)
				$month		= $GLOBALS['_GET']['dam'];
				$monthDb	= $this->DB->escapeString($month);
			}
			
			if($this->modType != "planner" && $this->useModDate) {
				$dateType		= "mod_date";
				$selExt			= ", YEAR(mod_date) ";
				$selExtYear		= ", MONTH(mod_date) ";
				$groupByMonth	= "GROUP BY (MONTH(mod_date))";
				$groupByYear	= "GROUP BY (YEAR(mod_date))";
				$orderByMonth	= "ORDER BY (MONTH(mod_date)) ASC";
				$orderByYear	= "ORDER BY (YEAR(mod_date)) DESC";
			}
			else {
				$dateType		= "date";
				$selExt			= ", YEAR(date) ";
				$selExtYear		= ", MONTH(date) ";
				$groupByMonth	= "GROUP BY (MONTH(date))";
				$groupByYear	= "GROUP BY (YEAR(date))";
				$orderByMonth	= "ORDER BY (MONTH(date)) ASC";
				$orderByYear	= "ORDER BY (YEAR(date)) DESC";
			}
			
			// Tags festlegen
			$headerTag		= 'h3';
			$navTagOpen		= "";
			$navTagClose	= "";
			$asideTagOpen	= "";
			$asideTagClose	= "";
			
			// Falls HTML5
			if($this->html5) {
				$headerTag		= 'h1';
				$navTagOpen		= '<nav>' . "\r\n";
				$navTagClose	= '</nav>' . "\r\n";
				$asideTagOpen	= '<aside>' . "\r\n";
				$asideTagClose	= '</aside>' . "\r\n";
			}
			
			
			if(isset($year) && $year != "") {
			
				// db-Query nach Dataarchiven (Monat)
				$queryMonArchive = $this->DB->query(  "SELECT MONTHNAME(".$dateType."), MONTH(".$dateType.")" . $selExtYear . " 
															FROM `$this->dataTableDB` AS dt 
																LEFT JOIN `$this->catTableDB` AS dct 
																ON dt.`cat_id` = dct.`cat_id` 
															WHERE 
															$this->published 
															AND $this->dbFilterGroup 
															$this->dbFilter 
															AND (YEAR($dateType)) = '$year' 
															$groupByMonth 
															$orderByMonth
															", false);
						
				#var_dump($queryMonArchive);
			}
			
			// db-Query nach Dataarchiven (Jahr)
			$queryYearArchive = $this->DB->query( "SELECT YEAR($dateType)" . $selExt . " 
														FROM `$this->dataTableDB` AS dt 
															LEFT JOIN `$this->catTableDB` AS dct 
															ON dt.`cat_id` = dct.`cat_id` 
														WHERE 
														$this->published  
														AND $this->dbFilterGroup 
														$this->dbFilter 
														$groupByYear 
														$orderByYear
														", false);
			
			#var_dump($queryYearArchive);
			
			// db-Query nach data Kategorien
			$queryCatArchive = $this->DB->query(  "SELECT dt.`cat_id`, dt.`category_" . $this->lang . "` 
														FROM `$this->catTableDB` AS dt
														WHERE 
														$this->dbFilterGroup 
														$this->dbFilter 
														ORDER BY dt.`category_" . $this->lang . "` ASC 
														", false);
			
			#var_dump($queryCatArchive);
			
							
			$dataOutput .=	'<div class="dataArchive">' . "\r\n";
			
			// Falls HTML5, aside-Tag
			$dataOutput .=	$asideTagOpen;
			
			
			// Jahr
			if($queryYearArchive > 0) {
			
				$dataOutput .=	'<' . $headerTag . '>{s_header:newsarchive1a}</' . $headerTag . '>' . "\r\n";
				
				// Falls HTML5
				$dataOutput .=	$navTagOpen;
				
				$dataOutput .=	'<ol>' . "\r\n";
					
				
				foreach($queryYearArchive as $archive) {
				
					$dataDate = htmlspecialchars($archive['YEAR('.$dateType.')']);
					
					if($this->modType != "planner") {
						
						$dataModDate = htmlspecialchars($archive['YEAR('.$dateType.')']);
					
						if($dataModDate > $dataDate)
							$dataDate = $dataModDate;
					}
						
					$dataOutput .=	'<li';
					
					if(isset($year) && $year == $dataDate)
						$dataOutput .=	' class="active"><span>' . $dataDate . '</span>';
					else
						$dataOutput .=	'><a href="' . $this->targetUrl . PAGE_EXT . '?mod=' . $this->modType . '&amp;day=' . $dataDate . '">' . $dataDate . '</a>';
						
					$dataOutput .=	'</li>' . "\r\n";
									
				} // Ende foreach									
									
			$dataOutput .=			'</ol>' . "\r\n";
		
			} // Ende count query
			
			
			// Monat
			if(isset($queryMonArchive) && $queryMonArchive > 0) {
			
				
				$dataOutput .=	'<' . $headerTag . '>{s_header:newsarchive1b}</' . $headerTag . '>' . "\r\n" .
								'<ol>' . "\r\n";
				
				
				foreach($queryMonArchive as $archive) {
				
					$dataDate = htmlspecialchars($archive['MONTH('.$dateType.')']);
					
					if($this->modType != "planner") {
						
						$dataModDate = htmlspecialchars($archive['MONTH('.$dateType.')']);
					
						if($dataModDate > $dataDate)
							$dataDate = $dataModDate;
					}
						
					$dataOutput .=	'<li';
					 
					if(isset($month) && $month == $dataDate)
						$dataOutput .=	' class="active"><span>{s_date:' . strtolower(date("M", strtotime(htmlspecialchars($archive['MONTHNAME('.$dateType.')'])))) . '}</span>';
					else
						$dataOutput .=	'><a href="' . $this->targetUrl . PAGE_EXT . '?mod=' . $this->modType . '&amp;day=' . $year . '&dam=' . $dataDate . '">{s_date:' . strtolower(date("M", strtotime(htmlspecialchars($archive['MONTHNAME('.$dateType.')'])))) . '}</a>';
									
					$dataOutput .=	'</li>' . "\r\n";
					
				} // Ende foreach									
									
			$dataOutput .=			'</ol>' . "\r\n";
				
			// Falls HTML5, nav-Tag schließen
			$dataOutput .=	$navTagClose;
	
			} // Ende count query
			
			
			// Kategorie
			if($queryCatArchive > 0) {
			
				$dataOutput .=	'<div class="data'.'Archive">' . "\r\n" .
								'<' . $headerTag . '>{s_header:newsarchive2}</' . $headerTag . '>' . "\r\n";
				
				// Falls HTML5
				$dataOutput .=	$navTagOpen;
				
				$dataOutput .=	'<ol>' . "\r\n";
					
				
				foreach($queryCatArchive as $archive) {
				
					$dataCat	= htmlspecialchars($archive['category_' . $this->lang]);
					$dataCatID	= htmlspecialchars($archive['cat_id']);
											
					$dataOutput .=	'<li';
					 
					if(isset($GLOBALS['_GET']['cid']) && $GLOBALS['_GET']['cid'] == $dataCatID)
						$dataOutput .=	' class="active"><span>' . $dataCat . '</span>';
					else
						$dataOutput .=	'><a href="' . $this->targetUrl . '/' . $this->urlCatPath . self::getAlias($dataCat) . '-' . $dataCatID . $this->modType[0] . PAGE_EXT . '">' . $dataCat . '</a>';
									
					$dataOutput .=	'</li>' . "\r\n";
								
				} // Ende foreach									
									
				$dataOutput .=		'</ol>' . "\r\n";
				
				// Falls HTML5, nav-Tag schließen
				$dataOutput .=	$navTagClose;
		
			} // Ende count query
			
			
			// Falls HTML5, aside-Tag schließen
			$dataOutput .=	$asideTagClose;

			
			$dataOutput .=			'</div>' . "\r\n";
			
			
		}// Ende else
		
		return $dataOutput;

	}
	
	
	
	/**
	 * Gibt DB Query nach Einzeldatensatz zurück
	 * 
     * @param	int		$dataID	data ID
	 * @access	public
	 * @return	string
	 */
	public function getDataEntryFromDB($dataID)
	{
	
		$dataID = (int)$dataID;
		
		// db-Query nach allen data
		$queryData = $this->DB->query("SELECT *, dt.`sort_id` AS sortid, user.`author_name` AS author 
											FROM `$this->dataTableDB` AS dt 
												LEFT JOIN `$this->catTableDB` AS dct 
												ON dt.`cat_id` = dct.`cat_id` 
												LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
												ON dt.`author_id` = user.`userid` 
											WHERE dt.id = $dataID 
											$this->dbFilter 
											AND $this->dbFilterGroup 
											AND $this->published
											", false);
		#var_dump($queryData);
	
		return $queryData;
	
	}
	
	
	
	/**
	 * Verwandte Datensätze auflisten
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getRelatedData($dataID)
	{
	
		$dataID		= (int)$dataID;
		$output		= "";
		$tags		= "";
		$dataTags	= $this->queryData[0]['tags_'.$this->lang];
		
		if(trim($dataTags) != "") {
		
			$dataTags	= explode(",", $dataTags);
			
			$i = 0;
			
			foreach($dataTags as $tag) {
				
				$tags .= " >";
				$tags .= trim($tag);
			}
			
			$this->selectExt = ", MATCH (dt.`tags_".$this->lang."`) AGAINST ('".$tags."' IN BOOLEAN MODE) AS score";
			$this->dbFilter .= " AND (MATCH (dt.`tags_".$this->lang."`) AGAINST ('".$tags."' IN BOOLEAN MODE)) AND dt.`id` != $dataID";
			$this->dbOrder	 = "ORDER BY score DESC, `date` DESC, `header_".$this->lang."` ASC";
			
			// related data
			$output	= self::getModuleData();
		
		}
	
		return $output;

	}
	
	
	
	/**
	 * DB Query nach vorherigem und nächstem Datensatz
	 * 
	 * @access	public
	 * @return	string
	 */
	public function queryPrevAndNextData()
	{

		// Einzeldatensatz-Details
		$nextSortID = $this->queryData[0]['sortid']+1;
		$prevSortID = $this->queryData[0]['sortid']-1;
		
		switch($this->order) {
			
			case "dateasc":
				$this->dbFilterNext	= $this->dbFilter." AND `date` > '" . $this->queryData[0]['date'] . "'";
				$this->dbFilterPrev = $this->dbFilter." AND `date` < '" . $this->queryData[0]['date'] . "'";
				$this->dbOrderNext	= $this->dbOrder;
				$this->dbOrderPrev	= " ORDER BY `date` DESC";
				break;
			case "datedsc":
				$this->dbFilterNext = $this->dbFilter." AND `date` < '" . $this->queryData[0]['date'] . "'";
				$this->dbFilterPrev = $this->dbFilter." AND `date` > '" . $this->queryData[0]['date'] . "'";
				$this->dbOrderNext	= $this->dbOrder;
				$this->dbOrderPrev	= " ORDER BY `date` ASC";
				break;
			case "nameasc":
				$this->dbFilterNext = $this->dbFilter." AND `header_".$this->lang."` > '" . $this->queryData[0]['header_'.$this->lang]."'";
				$this->dbFilterPrev = $this->dbFilter." AND `header_".$this->lang."` < '" . $this->queryData[0]['header_'.$this->lang]."'";
				$this->dbOrderNext	= $this->dbOrder;
				$this->dbOrderPrev	= " ORDER BY `header_".$this->lang."` DESC";
				break;
			case "namedsc":
				$this->dbFilterNext = $this->dbFilter." AND `header_".$this->lang."` < '" . $this->queryData[0]['header_'.$this->lang]."'";
				$this->dbFilterPrev = $this->dbFilter." AND `header_".$this->lang."` > '" . $this->queryData[0]['header_'.$this->lang]."'";
				$this->dbOrderNext	= $this->dbOrder;
				$this->dbOrderPrev	= " ORDER BY `header_".$this->lang."` ASC";
				break;
			default:
				$this->dbFilterNext = $this->dbFilter." AND dt.`sort_id` = $nextSortID";
				$this->dbFilterPrev = $this->dbFilter." AND dt.`sort_id` = $prevSortID";
				$this->dbOrderNext	= $this->dbOrder;
				$this->dbOrderPrev	= $this->dbOrder;
				break;
		}
		

		// db-Query nach neuerem Datensatz
		$this->queryNextData = $this->DB->query("SELECT * 
													FROM `$this->dataTableDB` AS dt 
													LEFT JOIN `$this->catTableDB` AS dct 
														ON dt.`cat_id` = dct.`cat_id` 
													WHERE 
														$this->published 
													AND $this->dbFilterGroup 
														$this->dbFilterNext 
														$this->dbOrderNext
													LIMIT 1
													", false);
		
		#var_dump($this->queryNextData);
		
		// db-Query nach älterem Datensatz
		$this->queryPrevData = $this->DB->query("SELECT * 
													FROM `$this->dataTableDB` AS dt 
													LEFT JOIN `$this->catTableDB` AS dct 
														ON dt.`cat_id` = dct.`cat_id` 
													WHERE 
														$this->published 
													AND $this->dbFilterGroup 
														$this->dbFilterPrev 
														$this->dbOrderPrev 
													LIMIT 1
													", false);
		
		#var_dump($this->queryPrevData);
	
	}
	
	
	
	/**
	 * Gibt die direkten Kindkategorien einer Elternkategorie zurück
	 * 
     * @param	string Kategorie-ID der Elternkategorie
     * @param	string Limitierung der Datensätze (default = '')
	 * @access	public
	 * @return	string
	 */
	public function getChildCats($catID, $queryLimit = "")
	{
		
		$catID		= (int)$this->DB->escapeString($catID);
		
		if(strpos($this->dbOrder, "date") !== false)
			$dbOrder	= "ORDER BY dct.`sort_id`";
		else
			$dbOrder	= str_replace("dt.`sort_id`", "dct.`sort_id`", $this->dbOrder);
		
		// db-Query nach Kindkategorien
		$queryChildCats = $this->DB->query( "SELECT * 
													FROM `$this->catTableDB` AS dct 
													WHERE `parent_cat` = $catID
													AND $this->dbFilterGroup 
													$dbOrder 
													$queryLimit
													", false);
		#var_dump($queryChildCats);

		return $queryChildCats;

	}
	


	/**
	 * Gibt Kategorie-IDs aller Kindkategorien einer Elternkategorie zurück
	 * 
     * @param	string Kategorie-ID der Elternkategorie
	 * @access	public
	 */
	public function getAllChildCats($parentCatID)
	{
		
		$childCats = self::getChildCats($parentCatID);
		
		if(count($childCats) > 0) {
			
			foreach($childCats as $parentCat) {
				
				$this->childCatIDs[] = $parentCat['cat_id'];
				self::getAllChildCats($parentCat['cat_id']);
			}
		}
	}
	
	
	
	
	/**
	 * Liest IDs aller Elternkategorien aus
	 * 
     * @param	string currentCatID
     * @param	string parentRootCatID
	 * @access	public
	 * @return	boolean/string
	 */
	public function getParentCats($currCatID, $parentRootCatID)
	{
	
		// Elternkategorien
		$z =0;
		
		while($currCatID != $parentRootCatID) {
			
			$parents	= $this->getParentCat($currCatID);

			if(count($parents) > 0) {
				
				$currCatParent = $parents[0]['parent_cat'];
				$parents	= $this->getParentCat($currCatParent);
				foreach($parents as $parent) {
					if($parent['cat_id'] == $currCatParent) {
						$this->parentCats[] = $currCatParent;
						$currCatID			= $currCatParent;
						// Zurücklink
						$this->linkBackCat	= $this->getCatBackButton(HTML::getLinkPath($parent['target_page'], "current", true, true));
					}
				}
			}
			
			$z++;
			
			// Absicherung der while-Schleife
			if($z == 1000)
				break;
		}
		#$this->parentCats = array_reverse($this->parentCats);
		#var_dump($this->parentCats);
	
	}	
	
	
	
	/**
	 * Gibt Daten einer Elternkategorie zurück
	 * 
     * @param	string Kategorie-ID
     * @param	string wenn nicht leer wird url-Alias zurückgegeben (default = '')
     * @param	string Datensortierung (default = '')
     * @param	string Datenanzahl (default = '')
	 * @access	public
	 * @return	boolean/string
	 */
	public function getParentCat($catID, $data = "", $dbOrder = "", $queryLimit = "")
	{
		
		$catID = (int)$this->DB->escapeString($catID);
		
		// db-Query nach allen data
		$queryParentCat = $this->DB->query("SELECT * 
												FROM `$this->catTableDB` AS dct 
											WHERE 
												`cat_id` = $catID 
											AND $this->dbFilterGroup 
												$dbOrder 
												$queryLimit
											", false);
	
		#var_dump($queryParentCat);
		
		if(count($queryParentCat) > 0) {
			if($data == "alias") {
		
				return self::getAlias($queryParentCat[0]['category_'.$this->lang]);
			}
			if($data == "targetPage") {
		
				return $queryParentCat[0]['target_page'];
			}
		}
		if($data == "alias") {
	
			return "";
		}
		
		return $queryParentCat;

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
			$imgParams	= explode("|", $p_catImg);
		}
		else{
			$imgArr		= (array)json_decode($p_catImg);
			
			if(!isset($imgArr[$this->lang]))
				$imgArr[$this->lang]	= "";
			
			$imgParams	= explode("<>", $imgArr[$this->lang]);
		}
		
		return $imgParams;
	
	}


	
	/**
	 * Gibt Daten-Objekt-Array zurück
	 * 
	 * @param	$objData			Objekt db str
	 * @access	protected
	 * @return	array
	 */
	protected function getObjectArray($objData)
	{

		// legacy object syntax
		if(substr_count($objData, "|") >= 7) {
		
			// Options-Array erstellen
			$legacyObjArr				= explode("|", $objData);
			$type						= array_pop($legacyObjArr);
			$objArr[$this->lang]		= $this->makeObjectOptionsArray($type, $legacyObjArr);
			$objArr["type"]				= $type;
		}
		else{
			$objArr						= (array)json_decode($objData);
			if(!isset($objArr["type"]))
				$objArr["type"]			= "";
			if(!isset($objArr[$this->lang]))
				$objArr[$this->lang]	= "";
		}
	
		return $objArr;
	}
	

	
	/**
	 * Gibt den Dateipfad eines Dateiobjekts zurück
	 * 
	 * @param	$src		Datei-Parameter
	 * @access	protected
	 * @return	array
	 */
	protected function getSourcePath($src)
	{

		// Pfad zur Datei
		// Falls files-Ordner, den Pfad ermitteln
		if(strpos($src, "/") !== false) {
			$srcPath		= CC_FILES_FOLDER . '/' . $src;
		}
		elseif(strpos($src, ">") !== false) {
			$filesImgArr	= explode(">", $src);
			$fileName		= array_pop($filesImgArr);					
			$srcPath		= CC_FILES_FOLDER . '/' . implode("/", $filesImgArr) . "/" . $fileName;
		}
		else {
			$srcPath		= CC_IMAGE_FOLDER . '/' . $src;
		}
		
		$srcPath			= PROJECT_HTTP_ROOT . '/' . $srcPath;
		
		return $srcPath;
	
	}
	


	/**
	 * Gibt Daten einer Elternkategorie zurück
	 * 
     * @param	array		Array mit Elternkategorie-IDs
     * @param	int			Kategorie-ID
     * @param	string		Derzeitiger Output-String
     * @param	int			Datenanzahl (Limit)
	 * @access	public
	 * @return	string
	 */
	public function getCatMenu($parentCats, $parentCatID, $currOutput, $queryLimit)
	{
		
		$dataOutput		= "";
		$parentOutput	= "";
		$level			= 0;
		$menuLevel		= count($parentCats);
							
		while($level < count($parentCats)) {
			
			$parentCatMenu = self::getChildCats($parentCats[$level], $queryLimit);
			
			foreach($parentCatMenu as $parentMenuItem) {
				
				$dataOutput =	'<li' . ($parentMenuItem['cat_id'] == $parentCatID ? ' class="active"' : '') . '><a href="' . $this->targetUrl . '/' . self::getAlias($parentMenuItem['category_'.$this->lang]) . '-' . $parentMenuItem['cat_id'] . $this->modType[0] . PAGE_EXT . '">';
				$dataOutput .=	MOD_MENU_PREFIX;
				$dataOutput .=	$parentMenuItem['category_'.$this->lang] . '</a>' . "\r\n";
				
				if($parentMenuItem['cat_id'] == $parentCatID)
					$this->activeParents[] = $dataOutput . '</li>';
					
				if($parentMenuItem['cat_id'] == $parentCatID && $currOutput != "")
					$dataOutput .=	'<ul class="level_' . $menuLevel . '">' . $currOutput . '</ul>' . "\r\n";
				$dataOutput .=	'</li>' . "\r\n";
				
				$parentOutput .= $dataOutput;				
			}
			
			$parentCatID = $parentMenuItem['parent_cat'];
			$currOutput = $parentOutput;
			$parentOutput = "";
			$level++;
			$menuLevel--;
			
		}
		
		$this->activeParents = array_reverse($this->activeParents);
		
		// Pfad an Breadcrumb-Navi anhängen
		parent::$dataBCPath = $this->activeParents;
		
		return $currOutput;
		
	}
	
	
	
	
	/**
	 * Anzahl an Datenobjekten ermitteln
	 * 
     * @param	array	$dataEntry	Array mit Datensätzen
	 * @access	public
	 * @return	string
	 */
	public function getDataObjectNumber($dataEntry)
	{
	
		$objectNumber	= 0;
		
		for($i = 1; $i <= MAX_DATA_OBJECTS_NUMBER; $i++) {
			if(!isset($dataEntry['object'.$i])) {
				$objectNumber	= $i-1;
				break;
			}
		}
		return $objectNumber;
	}
	
	
	
	
	/**
	 * Datenobjekte auslesen
	 * 
     * @param	array	$dataQuery	Array mit Dateneintrag-Details
	 * @access	public
	 * @return	string
	 */
	public function getDataObjects($dataQuery)
	{

		require_once PROJECT_DOC_ROOT . "/inc/classes/Elements/class.ElementFactory.php"; // Element-Factory einbinden

		$this->objOutput	= array();

	
		// Daten-Objekte auslesen
		for($o = 1; $o <= $this->objectNumber; $o++) {
			
			$this->dataObjects[$o]	= $this->getObjectArray($dataQuery['object'.$o]);
			
			// Daten-Objekt auslesen
			$this->objOutput[$o]	= $this->getDataObjectContent($this->dataObjects[$o], $o);

		}
		
		return $this->objOutput;

	}
	
	
	
	/**
	 * Datenobjektinhalt auslesen
	 * 
     * @param	array	$dataObj	Array mit Datenobjekt-Options
	 * @access	public
	 * @return	string
	 */
	public function getDataObjectContent($dataObj, $num)
	{		
	
		$conType		= $dataObj["type"];
		$conValue		= $dataObj[$this->lang];
		
		if(empty($conType))
			return "";
		
		
		// Objekttyp Namen anpassen
		if(	$conType == "image")
			$conType	= "img";
		

		// Objekt-Inhaltselementenart
		$this->dataObjectKind	= $this->getContentElementKind($conType);
		
		
		// Falls unbekanntes Element
		if($this->dataObjectKind == "unknown")
			return $this->backendLog ? $this->getNotificationStr('{s_error:unknowncon}: ' . $conType . '.', "error") : '';
		
		
		// Falls Plug-in
		if($this->dataObjectKind == "plugin") {			
			$this->isPluginObject[$num]	= true;
			$this->setPlugin($conType, $this->lang); // Sprachbausteine des Plug-ins laden
		}
		
		
		// Options-Array erstellen
		$conAttributes	= $this->makeObjectAttributesArray($conType, $num);
		$table			= $this->contentTable;
		$conNum			= "objectNum-" . $num;

		// Inhaltselementenart
		$this->contentElementKind	= $this->getContentElementKind($conType);
	
		// Element-Options
		$options	= array(	"conType"		=> $conType,
								"conValue"		=> $conValue,
								"conAttributes"	=> $conAttributes,
								"conTable"		=> $table,
								"conNum"		=> $conNum,
								"conCount"		=> $num
							);
		
		// Elementinhalt
		try {		
			// Inhaltselement-Instanz
			$o_element	= ElementFactory::create($conType, $options, $this->contentElementKind, $this->DB, $this->o_lng, $this->o_page);
		}
		
		// Falls Element-Klasse nicht vorhanden
		catch(Exception $e) {
			return $this->backendLog ? $e->getMessage() : "";
		}
		
		// Element-Objekt Attribute
		$o_element->lang					= $this->lang;
		$o_element->group					= $this->group;
		$o_element->pageId					= $this->pageId;
		$o_element->html5					= $this->html5;
		$o_element->isMainContent			= $this->isMainContent;
		
		
		// Ggf. Bild als Galerie
		if(!$this->singleData && $conType == "img") {

			$o_element->showCaption			= false;
			
			if(TEASER_IMG_TYPE == "view")
				$o_element->enlarge			= true;
			else {
				$o_element->useTeaserLink	= true;
				$o_element->teaserLink		= $this->dataLink;
			}
		}
		
		
		// Inhaltselement generieren
		$contentElement						= $o_element->getElement();
		
		// Ggf. Teaser-Image setzen
		if($conType == "img"
		&& $contentElement != ""
		&& $this->teaserImg == ""
		) {
			$this->teaserImg	= $contentElement;
			$this->teaserImgSrc	= $o_element->imgSrc;
		}

			
		// Head code
		$this->mergeHeadCodeArrays($o_element);
		
		return $contentElement;
	
	}
	
	
	
	/**
	 * Options-Array für die Inhaltselement-Generierung erstellen
	 * 
     * @param	array	$conType	Datenobjekt-Typ
     * @param	array	$conValue	Array mit Datenobjekt-Options
	 * @access	public
	 * @return	string
	 */
	public function makeObjectOptionsArray($conType, $conValue)
	{	
	
		switch($conType) {
		
			case "img";
			case "doc";
			case "audio";
				$conValue[0]		= str_replace(">", "/", $conValue[0]);
				$conValue[1]		= $this->getFieldValLocation($conValue[1]);
				$conValue			= implode("<>", $conValue);
				return $conValue;
		
			case "gallery":
				$conValue[2]		= $this->getFieldValLocation($conValue[2]);
				// erweiterte Galerieoptionen
				if(strpos($conValue[6], "{") !== 0
				&& strpos($conValue[6], "[") !== 0
				) {
					$conValue[6]	= $conValue[6];
					$conValue[7]	= 1;
					$conValue[8]	= 1;
					$conValue[9]	= 60;
					$conValue[10]	= 3500;
					$conValue[11]	= 1;
					$conValue[12]	= "num";
				}
				else {
					$conValue[6]	= implode("/", (array)json_decode($conValue[6]));
				}
				$conValue			= implode("/", $conValue);
				return $conValue;
		
			default:
				$conValue			= implode("<>", $conValue);
				return $conValue;
		}
	
	}
	
	
	
	/**
	 * Options-Array erstellen
	 * 
     * @param	array	$conType	Datenobjekt-Typ
     * @param	array	$num		Datenobjekt-Nummer
	 * @access	public
	 * @return	string
	 */
	public function makeObjectAttributesArray($conType, $num)
	{
	
		$attrArr	= array('id' 	=> "",
							'class'	=> $conType . 'Obj cc-dataobject-' . $conType . ' object-' . $num,
							'style'	=> ""
							);
		
		switch($conType) {
		
			case "img":
				$attrArr['class']  .= ' ' . ($num % 2 ? '{t_class:omega} {t_class:right}' : '{t_class:alpha} {t_class:left}');
				break;
		
			case "gallery":
				$attrArr['class']  .= ' cc-gallery cc-module';
				break;
		}
		
		return $attrArr;
	
	}	
	
	
	
	/**
	 * Options-Array erstellen
	 * 
     * @param	array	$val	Feldwert
	 * @access	public
	 * @return	string
	 */
	public function getFieldValLocation($val)
	{

		// Sprachenfilter
		if(strpos($val, "{") !== 0 && strpos($val, "[") !== 0)
			return $val;
		
		$valLang	= (array)json_decode($val);						
		$val	 	= isset($valLang[$this->lang]) ? $valLang[$this->lang] : '';
		
		return $val;
	
	}
	
	
	/**
	 * Liefert eine Liste von Newsfeeds
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getFeedList()
	{	
		
		$this->modType	= "feed";
		$this->dbOrder	= "ORDER BY `category_".$this->lang."` ASC";
		$classExt		= "List";
		$loopData		= "";
		$dataOutput		= "";
		$htmlHeadFeed	= "";
		$feedUrl		= PROJECT_HTTP_ROOT . '/_feed' . PAGE_EXT;
		
		// Falls keine Kategorie(n) ausgewählt wurden, Meldung zurückgeben
		if($this->catID == "")
			return '<p' . ($this->editorLog ? ' class="error {t_class:alert} {t_class:error}">Newsfeed - {s_error:choosecat}' : '>&nbsp;') . '</p>';
		

		// db-Query nach allen Feeds
		$queryData = $this->DB->query("SELECT * 
											FROM `$this->catTableDB` AS dct  
											WHERE 
											$this->dbFilterGroup 
											$this->dbFilter 
											$this->dbOrder 
											$this->limit
											", false);
		
				
		if(count($queryData) > 0) {
		
			$dataOutput .=		'<' . ($this->html5 ? 'h1' : 'h2') . '>{s_header:feed}</' . ($this->html5 ? 'h1' : 'h2') . '>' . "\r\n" .
								'<div class="'.$this->modType.$classExt.'">' . "\r\n";
			
			if($this->displayMode == "all" || $this->displayMode == "RSS") {
									
				$dataOutput .=	'<div><' . ($this->html5 ? 'h2' : 'h3') . ' class="'.$this->modType.'">' .
								'{s_header:rss}</' . ($this->html5 ? 'h2' : 'h3') . '>' . "\r\n" .
								'<ul>' . "\r\n";
				
				foreach($queryData as $dataEntry) {
					
					$dataCat = htmlspecialchars($dataEntry['category_' . $this->lang]);
					$dataCatId = htmlspecialchars($dataEntry['cat_id']);
					$dataTargetPage = htmlspecialchars($dataEntry['target_page']);
					
					if($dataTargetPage == "" || $dataTargetPage == 0)
						$dataTargetPage = $this->targetUrl;

					
					// Gesamtseitenfeed in HTML-Headbereich
					$htmlHeadFeed .= '<link href="' . $feedUrl . '?ff=rss&amp;id=' . $dataCatId . '&amp;tp=' . $dataTargetPage . '" type="application/rss+xml" rel="alternate" title="' . $dataCat . ' (RSS 2.0)" />' . "\r\n";

					$dataOutput .=	'<li>' . "\r\n" .
									'<a href="' . $feedUrl . '?ff=rss&amp;id=' . $dataCatId . '&amp;tp=' . $dataTargetPage . '" class="link">' .
									parent::getIcon("rss") .
									$dataCat .
									'</a>' . "\r\n" .
									'</li>' . "\r\n";
					
					
				} // Ende foreach
				
				$dataOutput .=		'</ul></div>' . "\r\n";
				
			}
													
			if($this->displayMode == "all" || $this->displayMode == "Atom") {
				
				$dataOutput .=	 	'<div><' . ($this->html5 ? 'h2' : 'h3') . ' class="'.$this->modType.'">' .
									'{s_header:atom}</' . ($this->html5 ? 'h2' : 'h3') . '>' . "\r\n" .
									'<ul>' . "\r\n";
				
				foreach($queryData as $dataEntry) {
					
					$dataCat = htmlspecialchars($dataEntry['category_' . $this->lang]);
					$dataCatId = htmlspecialchars($dataEntry['cat_id']);
					$dataTargetPage = htmlspecialchars($dataEntry['target_page']);
					
					$htmlHeadFeed .= '<link href="' . $feedUrl . '?ff=atom&amp;id=' . $dataCatId . '&amp;tp=' . $dataTargetPage . '" type="application/atom+xml" rel="alternate" title="' . $dataCat . ' (Atom 1.0)" />' . "\r\n";
					
					$dataOutput .=	'<li>' . "\r\n" .
									'<a href="' . $feedUrl . '?ff=atom&amp;id=' . $dataCatId . '&amp;tp=' . $dataTargetPage . '" class="link">' .
									parent::getIcon("rss") .
									$dataCat .
									'</a>' . "\r\n" .
									'</li>' . "\r\n";
					
					
				} // Ende foreach
				
				$dataOutput .=		'</ul></div>' . "\r\n";
			}
													
			$dataOutput .=		'<div class="close" title="{s_title:close}">{s_link:close}</div>' . "\r\n";
			
			$dataOutput .=		'</div>' . "\r\n";
	
		} // Ende count query
		
		
		// Seitenweite Feeds übergeben
		$this->feedHeadLinks	= $htmlHeadFeed;
		

		return $dataOutput;
		
	}
	


	/**
	 * Zählt den Aufruf des Datensatzes, falls nicht Backenduser
	 * 
     * @param	string $dataID	Datensatz-ID
	 * @access	private
	 */
	private function logDataCall($dataID)
	{

		if(!$this->backendLog
		&& !isset($GLOBALS['_COOKIE']['conciseLogging_off'])
		&& Log::checkBot() !== true
		)
																	
			$countView = $this->DB->query("UPDATE `$this->dataTableDB` 
												SET calls = calls+1 
												WHERE id = $dataID 
												", false);
			
	}
	


	/**
	 * Data edit access check
	 * 
	 * @param	array	$dataEntry	Array mit Datensatzdetails
	 * @access	public
     * @return	string
	 */
	public function hasDataEditAccess($dataEntry)
	{

		if ($this->adminLog
		|| ($this->editorLog
			&& (empty($dataEntry['group_edit'])
			|| $this->o_security->get('loggedUserID') == $dataEntry['author_id']
			|| count(array_intersect(array_filter(explode(",", $dataEntry['group_edit'])), $this->o_security->get('ownGroups')))))
		)
			return true;
		
		return false;
	
	}
	


	/**
	 * Button back to parent cat (page)
	 * 
	 * @param	array	$dataEntry	Array mit Datensatzdetails
	 * @access	public
     * @return	string
	 */
	public function getCatBackButton($url)
	{
	
		return '<p class="clearfix"><a href="' . htmlspecialchars($url) . '" class="{t_class:btn} {t_class:btndef} back">{s_link:back}</a></p>' . PHP_EOL;
	
	}	


	/**
	 * Erstellt ein Buttonpanel zum Bearbeiten des Dateneintrags
	 * 
	 * @param	array	$dataEntry	Array mit Datensatzdetails
	 * @access	public
     * @return	string
	 */
	public function getDataEditButtonPanel($dataEntry)
	{
	
		$adminIcons =	'<span class="dataEditButtons editButtons-panel">' . "\r\n";
		
		$adminIcons .=	'<span class="switchIcons">' . "\r\n";

		// Button publish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'pubcon publish publish-state-visible button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:nopublish}'.$this->modType,
								"attr"		=> 'data-action="pubdata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod='.$this->modType.'&action=pubentry&set=0&id=' . $dataEntry['id'] . '"' . ($dataEntry['published'] == 0 ? ' style="display:none;"' : ''),
								"icon"		=> "publish"
							);
			
		$adminIcons .=	parent::getButton($btnDefs);

		// Button unpublish
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'pubcon publish publish-state-hidden button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:publishcomment}',
								"attr"		=> 'data-action="pubdata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod='.$this->modType.'&action=pubentry&set=1&id=' . $dataEntry['id'] . '"' . ($dataEntry['published'] == 1 ? ' style="display:none;"' : ''),
								"icon"		=> "unpublish"
							);
			
		$adminIcons .=	parent::getButton($btnDefs);
		
		$adminIcons .=	'</span>' . "\r\n";

		
		// Falls FE-Mode an
		if(parent::$feMode && $this->editorLog) {
			
			// Button edit
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'editcon button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:edit'.$this->modType.'}',
									"attr"		=> 'data-action="editcon" data-actiontype="edit" data-type="' . $this->modType . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=edit&mod=' . $this->modType . '&id=' . $dataEntry['id'] . '"',
									"icon"		=> "edit"
								);
				
			$adminIcons .=	parent::getButton($btnDefs);
			
			// Button copy
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'editcon copydata button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:copy'.$this->modType.'}',
									"attr"		=> 'data-action="editcon" data-actiontype="copydata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=copy&mod=' . $this->modType . '&cat=' . $dataEntry['cat_id'] . '&id=' . $dataEntry['id'] . '"',
									"icon"		=> "copy"
								);
				
			$adminIcons .=	parent::getButton($btnDefs);
			
		}

		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delcon button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:del'.$this->modType.'}',
								"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&task=modules&mod='.$this->modType.'&action=del&cat=' . $dataEntry['cat_id'] . '&id=' . $dataEntry['id'] . '&sortid=' . $dataEntry['sort_id'] . '&red=' . urlencode($this->targetUrl . PAGE_EXT) . '&totalRows=' . (isset($GLOBALS['_GET']['totalRows']) ? $GLOBALS['_GET']['totalRows'] : '') . (isset($GLOBALS['_GET']['pageNum']) ? '&pageNum=' . $GLOBALS['_GET']['pageNum'] : '') . '"',
								"icon"		=> "delete"
							);
			
		$adminIcons .=	parent::getButton($btnDefs);

		$adminIcons .=	'</span>' . "\r\n";
		
		return $adminIcons;
	}
	
} // Ende Klasse
