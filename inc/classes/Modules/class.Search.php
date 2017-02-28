<?php
namespace Concise;


/**
 * Klasse für die Seitensuche
 *
 */

class Search extends Modules
{
	
    /**
     * Suchart
     *
     * @access public
     * @var    string
     */
    public $searchType = "";
	
    /**
     * Suchbegriff
     *
     * @access public
     * @var    string
     */
    public $searchPhrase = "";
    
    /**
     * Suchbegriff escaped
     *
     * @access public
     * @var    string
     */
    public $searchPhraseDb = "";
    
    /**
     * Suchbegriffe für Ajax-Suche
     *
     * @access public
     * @var    array
     */
    public $searchTerms = array();
    
    /**
     * Ajax-Suche
     *
     * @access public
     * @var    boolean
     */
    public $ajaxSearch = false;
    
    /**
     * Suchebegriff speichern
     *
     * @access public
     * @var    boolean
     */
    public $safeSearch = false;
    
	/**
	 * für die Suche generell erlaubte Tabellen
	 *
	 * @access public
     * @var    array
     */
	public $allowedSearchTables = array();
    
	/**
	 * zu durchsuchende Tabellen (ausgewählt in "verfeinerte Suche")
	 *
	 * @access public
     * @var    array
     */
	public $searchTables = array();
	
	/**
	 * Beinhaltet die Feldnamen der SUCHRELEVANTEN Inhaltselemente (z.B. "text") aus der db "contents".
	 *
	 * @access public
     * @var    array
     */
	public $searchFieldNames = array();
	
    /**
     * Suchergebnis
     *
     * @access public
     * @var    array
     */
    public $searchQuery = array();
    
    /**
     * Suchergebnisausgabe
     *
     * @access public
     * @var    string
     */
    public $searchResults = "";
    
    /**
     * Suchfehler
     *
     * @access public
     * @var    boolean
     */
    private $searchError = true;

    /**
     * max. Anzahl der Suchergebnisse
     *
     * @access public
     * @var    string
     */
    public $maxRows = 0;
    
    /**
     * ges. Zahl der Suchergebnisse
     *
     * @access public
     * @var    string
     */
    public $totalRows = 0;
    
    /**
     * Beginn der Pagination
     *
     * @access public
     * @var    string
     */
    public $startRow = 0;
    
    /**
     * Seitenzahl der aktuellen Suchergebnisse
     *
     * @access public
     * @var    string
     */
    public $pageNum = 0;
    
    /**
     * SQL-Anfragebedingung
     *
     * @access public
     * @var    string
     */
    public $restrict1 = "";
    public $restrict2 = "";
    public $restrict3 = "";  
    public $restrict4 = "";
	
	/**
     * Erweiterer Select-String
	 *
     * @access public
     * @var    string
     */
    public $selectExt1 = "";
  
	/**
     * Erweiterer Select-String
	 *
     * @access public
     * @var    string
     */
    public $selectExt2 = "";
  
	/**
     * Sortierung der Datensätze
	 *
     * @access public
     * @var    string
     */
    public $order = "";
  
	/**
     * Sortierung der Datensätze
	 *
     * @access public
     * @var    string
     */
    public $order2 = "";


	/**
	 * Liest den Suchbegriff aus
	 * 
     * @param   object	DB-Objekt
     * @param   object	Sprachobjekt
     * @param   string	Suchart
     * @param   array	Suchtabellen (default = array())
	 * @access	public
	 */
	public function __construct($DB, $o_lng, $searchType, $searchTables = array())
	{
	
		// Datenbankobjekt
		$this->DB		= $DB;
		
		// Sprache
		$this->lang		= $o_lng->lang;
		
		// Suchart
		$this->searchType = $searchType;
		
		// Generell erlaubte Tabellen
		$this->allowedSearchTables = $GLOBALS['searchTables'];		
		
		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();

		// Benutzergruppe
		$this->group		= $this->o_security->get('group');
		$this->ownGroups	= $this->o_security->get('ownGroups');
		$this->adminPage	= $this->o_security->get('adminPage');
		
		
		// Falls Suche im Adminbereich
		if($this->adminPage) {
		
			// Sprache ist editLang
			$this->lang		= $o_lng->editLang;
			
			// Falls ein Author im Backend gelogged ist, Seitensuche ausnehmen und Daten auf selbst verfasste beschränken
			$this->allowedSearchTables = array("pages","articles","news","planner");
		
			// Falls ein Author im Backend gelogged ist, Seitensuch ausnehmen und Daten auf selbst verfasste beschränken
			if($this->group == "author" && $GLOBALS['_GET']['page'] == "admin")
				array_shift($this->allowedSearchTables);
		}
		

		// Zu durchsuchende Tabllen
		if(!empty($searchTables)) {
			foreach($searchTables as $sTab) {
				if(in_array($sTab, $this->allowedSearchTables))
					$this->searchTables[] = $sTab;
			}
		}
		else
			$this->searchTables = $this->allowedSearchTables;
			
		// Auslesen des Suchstrings
		if(isset($GLOBALS['_POST']['searchPhrase'])) {
			$this->searchPhrase = trim($GLOBALS['_POST']['searchPhrase']);
		}

		elseif(isset($GLOBALS['_GET']['search'])) {
			$this->searchPhrase = trim($GLOBALS['_GET']['search']);
			$this->searchType = "LIKE";
		}
		
		// Falls Ajax-Suche
		if(isset($GLOBALS['_GET']['ajaxsearch'])) {
			$this->ajaxSearch	= true;
			$this->safeSearch	= false;
			$this->searchPhrase = rawurldecode($this->searchPhrase);
			parent::$staText	= $o_lng->staText; // Sprachbausteine nachladen
		}
		// Andernfalls, falls nicht Aufruf aus Tagcloud, Suchbegriff speichern (unten)
		elseif(!isset($GLOBALS['_GET']['src']))
			$this->safeSearch	= true;


			// Auslesen der verfeinerten Suche
		if(isset($GLOBALS['_POST']['searchTables'])) {
			
			$tabSel = $GLOBALS['_POST']['searchTables'];
			$this->searchTables = array();
			
			if(!empty($tabSel["t"]) && in_array("pages", $this->allowedSearchTables))
				$this->searchTables[] = "pages";
			if(!empty($tabSel["a"]) && in_array("articles", $this->allowedSearchTables))
				$this->searchTables[] = "articles";
			if(!empty($tabSel["n"]) && in_array("news", $this->allowedSearchTables))
				$this->searchTables[] = "news";
			if(!empty($tabSel["p"]) && in_array("planner", $this->allowedSearchTables))
				$this->searchTables[] = "planner";
			
		}
		
		// Falls Parameter zur Einschränkung der zu durchsuchenden Tabellen mitgegeben wurden (z.B. aus Tag cloud)
		elseif(isset($GLOBALS['_GET']['src'])) {
				
			$tabSel = $GLOBALS['_GET']['src'];
			$this->searchTables = array();
			
			if(strpos($tabSel, "t") !== false && in_array("pages", $this->allowedSearchTables))
				$this->searchTables[] = "pages";
			if(strpos($tabSel, "a") !== false && in_array("articles", $this->allowedSearchTables))
				$this->searchTables[] = "articles";
			if(strpos($tabSel, "n") !== false && in_array("news", $this->allowedSearchTables))
				$this->searchTables[] = "news";
			if(strpos($tabSel, "p") !== false && in_array("planner", $this->allowedSearchTables))
				$this->searchTables[] = "planner";
		}
		
		// Suchbegriff überprüfen
		if($this->searchPhrase == "")
			$this->notice = "{s_notice:searchterm}";
		
		elseif(strlen($this->searchPhrase) < 4 && $this->searchType == "MATCH")
			$this->notice = "{s_error:shortsearch1}";
		
		elseif(strlen($this->searchPhrase) < 3)
			$this->notice = "{s_error:shortsearch2}";
		
		elseif(strlen($this->searchPhrase) > 256)
			$this->notice = "{s_error:longsearch}";
		
		elseif(!preg_match("/^[0-9\pL &\.\+\-]+$/u", $this->searchPhrase)) // !!!WICHTIG: \pL statt \w, da bei Linux sonst keine Umlaute erlaubt sind!
			$this->notice = "{s_error:wrongsearch}";
		
		else {

			$this->searchPhraseDb	= $this->DB->escapeString($this->searchPhrase);
			$this->searchError		= false;

			$groupDB 				= $this->DB->escapeString($this->group);
			
			// Gruppenbeschränkung
			if($this->group != "admin" && $this->group != "editor") {
				
				$this->restrict1 = 	" AND (pt.`group` = 'public' OR FIND_IN_SET('" . $groupDB . "', pt.`group`)" . ContentsEngine::getOwnGroupsQueryStr($this->ownGroups, "pt.") . ") ";
				$this->restrict2 = 	" AND (dct.`group` = 'public' OR FIND_IN_SET('" . $groupDB . "', dct.`group`)" . ContentsEngine::getOwnGroupsQueryStr($this->ownGroups, "dct.") . ") ";
				$this->restrict3 = 	" AND (`group` = 'public' OR FIND_IN_SET('" . $groupDB . "', `group`)" . ContentsEngine::getOwnGroupsQueryStr($this->ownGroups) . ") ";
				
				// Falls Author
				if($this->group == "author" && $GLOBALS['_GET']['page'] == "admin")
					$this->restrict2 = " AND dct.`group` = 'public' OR dt.`author_id` = '" . $this->g_Session['userid'] . "'";
			}

			// Suche durchführen, falls erlaubte Tabellen in Array vorhanden
			if(count($this->allowedSearchTables) > 0 && $this->allowedSearchTables[0] != "" && !empty($this->searchTables)) {
            
            	if($this->ajaxSearch)
  					$this->doSearch(100);
              	else
					$this->doSearch();
					
				// Suchbegriff speichern, falls nicht über Ajax und nicht Backend
				if($this->safeSearch && !$this->adminPage)
					$this->safeSearchStr();
            }
		}
	}
		
		
	
	/**
	 * Führt die db-Suche nach dem Suchbegriff aus
	 * 
     * @param   int		maximale Anzahl Suchergebnisse (default = SEARCH_MAX_ROWS)
	 * @access	public
	 * @return	boolean
	 */
	public function doSearch($maxRows = SEARCH_MAX_ROWS)
	{
		
		// Suche starten
		$columns		= array(); // relevante Spalten
		$columnNr		= 0; // Zahl der Spalten
		$targetCols		= "";
		$targetCols		.= "ct.`con_" . $this->lang . "`";
		$this->pageNum	= 0;
		$this->maxRows	= $maxRows;
		
		
		if($this->searchType == "LIKE") { // Falls eine Suche mit LIKE ausgeführt werden soll (Standard)
			
			// Suchstring für contents_main
			$dbQueryString1 = "ct.con_" . $this->lang . " LIKE " . "'%$this->searchPhraseDb%'";
			
			// Suchstring für Daten
			$dbQueryString2 = "(dt.published = 1 AND 
							   (dt.header_" . $this->lang . " LIKE " . "'%$this->searchPhraseDb%'" . " OR 
								dt.tags_" . $this->lang . " LIKE " . "'%$this->searchPhraseDb%'" . " OR 
								dt.teaser_" . $this->lang . " LIKE " . "'%$this->searchPhraseDb%'" . " OR 
								dt.text_" . $this->lang . " LIKE " . "'%$this->searchPhraseDb%'" . " 
								)
								OR dct.`cat_teaser_" . $this->lang . "` LIKE " . "'%$this->searchPhraseDb%'" . "
								)";
			
			// Suchstring für Gästebuch
			$dbQueryString3 = "(gbcomment LIKE " . "'%$this->searchPhraseDb%'" . ")";
			
			#var_dump("<br><br>".$dbQueryString2);
			
		}
		
		else { // Andernfalls MATCH AGAINST Suche durchführen
			
			$dbQueryString1 = " MATCH (" . $targetCols . ") AGAINST ('$this->searchPhraseDb')";
			
			$dbQueryString2 = "(dt.published = 1 AND 
								(
								(MATCH (dct.`cat_teaser_" . $this->lang . "`) AGAINST ('$this->searchPhraseDb')) OR  
								(MATCH (dt.`header_" . $this->lang . "`, dt.`teaser_" . $this->lang . "`, dt.`text_" . $this->lang . "`) AGAINST ('$this->searchPhraseDb')) OR 
								(MATCH (dt.`tags_" . $this->lang . "`) AGAINST ('$this->searchPhraseDb' IN BOOLEAN MODE))
								) 
								)";
						
			$dbQueryString3 = "MATCH (gbcomment) AGAINST ('$this->searchPhraseDb')";
			
		}
		
		$this->selectExt1 	= ", MATCH (" . $targetCols . ") AGAINST ('$this->searchPhraseDb') AS score";
		$this->selectExt2 	= ", MATCH (dt.`header_" . $this->lang . "`, dt.`teaser_" . $this->lang . "`, dt.`text_" . $this->lang . "`) AGAINST ('$this->searchPhraseDb') AS score";
		$this->selectExt2 	.= ", MATCH (dt.`tags_" . $this->lang . "`) AGAINST ('$this->searchPhraseDb' IN BOOLEAN MODE) AS score2";
		$this->order		= " ORDER BY score DESC";
		$this->order2		= " ORDER BY score + score2 DESC";
				
		$query				= array();
		$query_limit		= " LIMIT 0," . $this->maxRows;
		
		
		// Falls Ajax
		if($this->ajaxSearch) {
			$this->searchRes($dbQueryString1, $dbQueryString2, $dbQueryString3, $query_limit);
			return false;
		}
		
		
		// Falls nicht Ajax-Suche schauen ob überhaupt Ergebnisse vorhanden
		
		// Pagination
		if (isset($GLOBALS['_GET']['pageNum']))
			$this->pageNum = $GLOBALS['_GET']['pageNum'];
		
		$this->startRow = $this->pageNum * $this->maxRows;
		$query_limit = " LIMIT " . $this->startRow . "," . $this->maxRows;
		
		// db-Suche
		if(in_array("pages", $this->searchTables))
			$query[] = $this->DB->query("SELECT *" . $this->selectExt1 . " 
											FROM `" . DB_TABLE_PREFIX . "search` AS ct 
												LEFT JOIN `" . DB_TABLE_PREFIX . parent::$tablePages . "` AS pt
												ON ct.page_id = pt.page_id 
											WHERE (" . $dbQueryString1 . ") 
												AND ct.`page_id` > 0 
												AND pt.`nosearch` != 1
												$this->restrict1
											");
			
		if(in_array("articles", $this->searchTables))
			$query[] = $this->DB->query("SELECT *" . $this->selectExt2 . " 
											FROM `" . DB_TABLE_PREFIX . "articles_categories` AS dct
												LEFT JOIN `" . DB_TABLE_PREFIX . "articles` AS dt
												ON dt.cat_id = dct.cat_id 
											WHERE (" . $dbQueryString2 . ") 
												$this->restrict2 
											");
			
		if(in_array("news", $this->searchTables))
			$query[] = $this->DB->query("SELECT *" . $this->selectExt2 . " 
											FROM `" . DB_TABLE_PREFIX . "news_categories` AS dct 
												LEFT JOIN `" . DB_TABLE_PREFIX . "news` AS dt 
												ON dt.cat_id = dct.cat_id 
											WHERE (" . $dbQueryString2 . ") 
												$this->restrict2 
											");
			
		if(in_array("planner", $this->searchTables))
			$query[] = $this->DB->query("SELECT *" . $this->selectExt2 . " 
											FROM `" . DB_TABLE_PREFIX . "planner_categories` AS dct 
												LEFT JOIN `" . DB_TABLE_PREFIX . "planner` AS dt 
												ON dt.cat_id = dct.cat_id 
											WHERE (" . $dbQueryString2 . ") 
												$this->restrict2
											");
			/*
		if(in_array("gbook", $this->searchTables))
			$query[] = $this->DB->query("SELECT *" . $this->selectExt3 . " 
											FROM `" . DB_TABLE_PREFIX . "gbook` 
												WHERE (" . $dbQueryString3 . ") 
												$this->restrict3
											");
	  
			*/
		#var_dump($query);
		
		$this->totalRows = count(max($query)); // Anzahl an Suchergebnissen auf dieser Seite
		
		
		$resCount = 0;
		
		foreach($query as $res) {
			$resCount += count($res);
		}
		if($resCount > 0) { // Falls die Suche erfolgreich war Methode zur Auflistung der Suchergebnisse aufrufen
	
			$resStr		= sprintf(ContentsEngine::replaceStaText('{s_notice:searchres}'), '&quot;<i>' . htmlspecialchars($this->searchPhrase) . '</i>&quot;', $resCount);
			#$this->totalRows = $resCount;

			// Button newsearch
			$btnDefs	= array(	"href"		=> $GLOBALS['_GET']['page'] . ($GLOBALS['_GET']['page'] == "admin" ? '?task=search' : PAGE_EXT . '?search=#siteSearch'),
									"class"		=> 'submitSearch {t_class:btnlink}',
									"text"		=> "{s_link:newsearch}",
									"title"		=> '{s_common:start}',
									"icon"		=> "search",
									"iconclass"	=> "newSearch"
								);
				
			$resStr    .=	parent::getButtonLink($btnDefs);

			$this->notice = $resStr;
			
			$this->searchRes($dbQueryString1, $dbQueryString2, $dbQueryString3, $query_limit);
			
			return true;
		}
		else {
  
			$this->notice = sprintf(ContentsEngine::replaceStaText('{s_notice:searchres}'), '&quot;<i>' . htmlspecialchars($this->searchPhrase) . '</i>&quot;', '{s_common:nopl}');
			
		}
  
		return false;

	}
	


	/**
	 * Gibt die Suchseiteninhalte aus
	 * 
     * @param   string	Art der Suchmaske (default = small)
     * @param   boolean	Ajax-Behandlung unterdrücken (default = false)
	 * @access	public
	 * @return	string
	 */
	public function getSearch($type = "small", $noAjax = false)
	{
		
		// Falls die Suche nicht erfolgreich war, Suchmaske mit Meldung anzeigen
		if ($this->searchResults == "" 
		&& (!$this->ajaxSearch
		|| $noAjax)
		)		
			return $this->getSearchForm($type);
		
		
		// Andernfalls Suchergebnisse anzeigen
		if($this->ajaxSearch) { // Falls Ajax-Suche
		
			if($this->searchError) { // Falls die Suche nicht erfolgreich war, Suchmaske mit Meldung anzeigen
				if($type == "big")
					return '<><div id="searchResults" class="emptySearchRes"><p class="searchNotice notice error {t_class:alert} {t_class:warning}">' . ContentsEngine::replaceStaText($this->notice) . '</p></div>' . "\r\n";
				else
					return "";
			}
			else {
				// Falls die kleine Seitensuche, nur die Suchbegriffe widergeben
				if($type == "small"
				|| $type == "navbar"
				)
					return $this->searchTerms . '<>';
				else
					return $this->searchTerms . '<><div id="searchResults">' . ContentsEngine::replaceStaText($this->searchResults) . '</div>' . "\r\n";
			}
		}
		else
		
			return $this->searchResults;

	}
	

	/**
	 * Liefert eine einfache Suchmaske
	 * 
     * @param	string Art der Suchmaske (default = small)
     * @param	string Standardinputwert (default = '')
	 * @access	public
	 * @return	string
	 */
	public function getSearchForm($type = "small", $defValue = "")
	{
		
		$notice		= "";
		$refSearch	= "";
		$formAction	= "";
		
		if($this->adminPage) {
			$formAction	= ADMIN_HTTP_ROOT . '?task=search';
		}
		else {
			$formAction	= HTML::getLinkPath(-1004, "current", true, true);
		}
		
		if(!empty($this->notice))
			$notice = $this->notice; // Meldung falls gesetzt übergeben
				 
		$hiddenSearchTables =	'<input type="hidden" class="searchTablesT" name="searchTables[t]" value="' . (in_array("pages", $this->searchTables) ? 't' : '') . '" />' . "\r\n" .
								'<input type="hidden" class="searchTablesA" name="searchTables[a]" value="' . (in_array("articles", $this->searchTables) ? 'a' : '') . '" />' . "\r\n" .
								'<input type="hidden" class="searchTablesN" name="searchTables[n]" value="' . (in_array("news", $this->searchTables) ? 'n' : '') . '" />' . "\r\n" .
								'<input type="hidden" class="searchTablesP" name="searchTables[p]" value="' . (in_array("planner", $this->searchTables) ? 'p' : '') . '" />' . "\r\n";

		if($type == "small") {
		
			$uniqueID		= uniqid(time());
			
			$searchForm =	'<div class="searchDiv">' . "\r\n" .
							'<form class="{t_class:forminl}" action="' . $formAction . '" method="post" accept-charset="UTF-8">' . "\r\n" .
							'<fieldset>' . "\r\n" .
							'<span class="{t_class:formrow}">' . "\r\n" .
							'<label for="searchPhrase-' . $uniqueID . '" class="search {t_class:labelinl}">' . ($defValue == "" ? '{s_common:search}' : '') . '</label>' . "\r\n" .
							'<span class="searchPhrase">' . "\r\n" .
							'<input type="text" name="searchPhrase" id="searchPhrase-' . $uniqueID . '" class="searchPhrase small {t_class:input} {t_class:fieldinl}" autocomplete="off" maxlength="100" placeholder="{s_common:search}" title="{s_common:searchtitle}" value="'.$defValue.'" data-ajaxsearch="true" />' . "\r\n" .
							'</span>' . "\r\n";
			
			$searchForm .=	$hiddenSearchTables;

			// Button search
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> 'submitSearch',
									"class"		=> 'submitSearch {t_class:btnpri} {t_class:btnnarrow} button-icon-only',
									"text"		=> "",
									"value"		=> "{s_common:start}",
									"title"		=> '{s_common:start}',
									"icon"		=> "search",
									"icontext"	=> ""
								);
				
			$searchForm .=	parent::getButton($btnDefs);
			
			$searchForm .=	'</span>' . "\r\n" .
							'</fieldset>' . "\r\n" .
							'</form>' . "\r\n" .
							'</div><!-- end #search -->' . "\r\n";
		}
		elseif($type == "navbar") {
		
			$uniqueID		= uniqid(time());
			
			$searchForm =	'<form class="{t_class:forminl} navbar-form navbar-right" role="search" action="' . $formAction . '#siteSearch" method="post" accept-charset="UTF-8" data-ajax="false">' . "\r\n" .
							'<div class="input-group">' . "\r\n" .
							'<input type="text" name="searchPhrase" id="searchPhrase-' . $uniqueID . '" class="searchPhrase navbarSearch {t_class:input} {t_class:fieldinl}" autocomplete="off" maxlength="100" placeholder="{s_common:search}" title="{s_common:searchtitle}" value="'.$defValue.'" data-ajaxsearch="true" />' . "\r\n" .
							'<span class="searchPhrase input-group-btn {t_class:formgroupbtn}">' . "\r\n" .
							$hiddenSearchTables;

			// Button search
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> 'submitSearch',
									"class"		=> 'submitSearch {t_class:btndef} {t_class:btnnarrow} button-icon-only',
									"value"		=> "{s_common:start}",
									"text"		=> "",
									"title"		=> '{s_common:start}',
									"icon"		=> "search",
									"icontext"	=> ""
								);
				
			$searchForm .=	parent::getButton($btnDefs);
			
			$searchForm .=	'</span>' . "\r\n" .
							'</div>' . "\r\n" .
							'</form><!-- end #search -->' . "\r\n";
		}
		else {
			
			// Falls mehrere Tabellen für die Suche erlaubt sind, Option "Suche verfeinern" einbinden
			if(count($this->allowedSearchTables) > 1
			|| $this->adminPage
			) {
				
				$refSearch =	'<div id="refineSearch" class="searchDiv">' . "\r\n";
				
				if(!$this->adminPage)
					$refSearch .=	'<button type="button" class="link refineSearch {t_class:btn} {t_class:btnlink}">{s_button:refinesearch}</button>' . "\r\n" .
									'<div id="refinedSearch" class="{t_class:panel}"' . (isset($GLOBALS['_POST']['refSearch']) && $GLOBALS['_POST']['refSearch'] == "true" ? ' style="display:block;"' : '') . '>' . "\r\n";
								
				$refSearch .=	'<p class="labelSearchTabs {t_class:panelhead}">{s_text:refinesearch}</p>' . "\r\n";
				
				$refSearch .=	'<div class="{t_class:panelbody}">' . "\r\n" .
								'<input type="hidden" name="searchTables[true]" value="" />' . "\r\n" .
								'<label class="markAll markBox"><input type="checkbox" id="markAllSearch" data-select="all" /></label>' . "\r\n" .
								'<label for="markAllSearch" class="markAllLB inline-label">{s_label:mark}</label>' . "\r\n" .
								'<br class="clearfloat" /><br />' . "\r\n";
								
				if(in_array("pages", $this->allowedSearchTables))
					$refSearch .=	'<span class="{t_class:formrow}">' . "\r\n" .
									'<label class="markBox"><input type="checkbox" name="searchTables[t]" id="searchTables-t" class="searchTable"' . (in_array("pages", $this->searchTables) ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
									'<label for="searchTables-t" class="inline-label">{s_label:pages}</label>' . "\r\n" .
									'</span>' . "\r\n";
									
				if(in_array("articles", $this->allowedSearchTables))
					$refSearch .=	'<span class="{t_class:formrow}">' . "\r\n" .
									'<label class="markBox"><input type="checkbox" name="searchTables[a]" id="searchTables-a" class="searchTable"' . (in_array("articles", $this->searchTables) ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
									'<label for="searchTables-a" class="inline-label">{s_' . ($this->adminPage ? 'option' : 'label') . ':articles}</label>' . "\r\n" .
									'</span>' . "\r\n";
									
				if(in_array("news", $this->allowedSearchTables))
					$refSearch .=	'<span class="{t_class:formrow}">' . "\r\n" .
									'<label class="markBox"><input type="checkbox" name="searchTables[n]" id="searchTables-n" class="searchTable"' . (in_array("news", $this->searchTables) ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
									'<label for="searchTables-n" class="inline-label">{s_' . ($this->adminPage ? 'option' : 'label') . ':news}</label>' . "\r\n" .
									'</span>' . "\r\n";
									
				if(in_array("planner", $this->allowedSearchTables))
					$refSearch .=	'<span class="{t_class:formrow}">' . "\r\n" .
									'<label class="markBox"><input type="checkbox" name="searchTables[p]" id="searchTables-p" class="searchTable"' . (in_array("planner", $this->searchTables) ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
									'<label for="searchTables-p" class="inline-label">{s_' . ($this->adminPage ? 'option' : 'label') . ':planner}</label>' . "\r\n" .
									'</span>' . "\r\n";
				
				$refSearch .=	'</div>' . "\r\n" .
								'<br class="clearfloat" />' . "\r\n" .
								'</div>' . "\r\n";
								
				if(!$this->adminPage)
					$refSearch .=	'</div>' . "\r\n";
			}
			else
				$refSearch .=	$hiddenSearchTables;

			
			if(!$this->adminPage && !$this->ajaxSearch)
		        $searchForm =	'<div id="siteSearch" class="{t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\r\n" .
								'<h1>{s_header:search}</h1>' . "\r\n";
			else
				$searchForm = 	'<div id="siteSearch" class="adminSection">' . "\r\n";
			
			$uniqueID		= uniqid(time());
			
	        $searchForm .=	'<div id="newSearch" class="{t_class:margintm} {t_class:marginbm}">' . "\r\n" .
							'<p class="searchNote notice error {t_class:alert} {t_class:hint}">' . $notice . '</p>' . "\r\n" .
							'<form class="{t_class:row} {t_class:forminl}" action="' . $formAction . '#siteSearch" data-ajax="false" method="post" accept-charset="UTF-8">' . "\r\n" .
							'<fieldset class="{t_class:halfrow}">' . "\r\n" .
							'<span class="{t_class:formrow}">' . "\r\n" .
							#'<label class="{t_class:labelinl}" for="searchPhrase-' . $uniqueID . '">{s_common:newsearch} </label>' . "\r\n" .
							#'<span class="{t_class:col-8} {t_class:col}">' . "\r\n" .
							'<span class="{t_class:labelinl}">' . "\r\n" .
							'<input type="text" name="searchPhrase" id="searchPhrase-' . $uniqueID . '" class="searchPhrase big {t_class:input} {t_class:field}" autocomplete="off" maxlength="100" placeholder="{s_common:search}" data-ajaxsearch="true" />' . "\r\n" .
							'</span>' . "\r\n";
							#'<span class="{t_class:col-2} {t_class:col}">' . "\r\n" .

			// Button search
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> 'submitNewSearch',
									"id"		=> 'submitNewSearch',
									"class"		=> 'submitNewSearch formbutton {t_class:btnpri} {t_class:btnnarrow}',
									"value"		=> "{s_common:start}",
									"title"		=> '{s_common:start}',
									"attr"		=> 'data-alert="{s_notice:nosearchtabs}"',
									"icon"		=> "search"
								);
				
			$searchForm .=	parent::getButton($btnDefs);
			
			$searchForm .=	'</span>' . "\r\n" .
							'<input type="hidden" name="submitNewSearch" />' . "\r\n" .
							'<input type="hidden" name="refSearch" class="refSearch"' . (isset($GLOBALS['_POST']['refSearch']) && $GLOBALS['_POST']['refSearch'] == "true" ? ' value="true"' : '') . ' />' . "\r\n" .
							'</fieldset>' . "\r\n" .
							'<fieldset class="{t_class:halfrow}">' . "\r\n" .
							$refSearch .
							'</fieldset>' . "\r\n" .
							'</form>' . "\r\n" .
							'</div><!-- end #newSearch -->' . "\r\n" .
							'<script type="text/javascript">' . "\r\n" .
							($this->headJS ? 'head.ready("jquery",function(){' : '') .
							'$(document).ready(function(){$("#searchPhrase-' . $uniqueID . '").focus();' . "\r\n" .
							'}); // Ende ready function' . "\r\n" .
							($this->headJS ? '});' : '') .
							'</script>' . "\r\n";
							
	        $searchForm .=	'</div>' . "\r\n";
		
		}
		
		return $searchForm;

	}
	
	
	/**
	 * Liefert eine erweiterte Suchmaske bzw. Suchergebnisse
	 * 
     * @param   string	Querystring1
     * @param   string	Querystring2
     * @param   string	Querystring3
     * @param   int		maximale Anzahl Suchergebnisse (default = '')
	 * @access	public
	 * @return	string
	 */
	public function searchRes($dbQueryString1, $dbQueryString2, $dbQueryString3, $query_limit = "")
	{	

		$query			= array();
		$queryStringExt	= "";
		
		
		// Meta-Tags (HTML-Titel etc.) um Suchbegriff erweitern, falls nicht Suche via Ajax
		if(!$this->ajaxSearch) {
			$this->currentPage	= htmlspecialchars($this->searchPhrase) . " - " . $this->currentPage;
			$this->pageKeywords	= htmlspecialchars($this->searchPhrase) . ", " . $this->pageKeywords;
			$this->pageDescr	= ContentsEngine::replaceStaText('{s_header:search} {s_common:for}') . ' ' . htmlspecialchars($this->searchPhrase) . ($this->pageDescr != "" ? " - " . $this->pageDescr : '');
		}


		// db-Suche
		if(in_array("pages", $this->searchTables))
			$query["pages"] = $this->DB->query("SELECT *" . $this->selectExt1 . "
											FROM `" . DB_TABLE_PREFIX . "search` AS ct 
												LEFT JOIN `" . DB_TABLE_PREFIX . parent::$tablePages . "` AS pt
												ON ct.page_id = pt.page_id 
											WHERE (" . $dbQueryString1 . ") 
												AND pt.`page_id` > 0 
												AND pt.`nosearch` != 1
												$this->restrict1 
												$this->order
												$query_limit
											");
			
		if(in_array("articles", $this->searchTables))
			$query["articles"] = $this->DB->query("SELECT *, dct.`cat_id` AS catid" . $this->selectExt2 . " 
											FROM `" . DB_TABLE_PREFIX . "articles_categories` AS dct 
												LEFT JOIN `" . DB_TABLE_PREFIX . "articles` AS dt 
												ON dt.cat_id = dct.cat_id 
											WHERE (" . $dbQueryString2 . ") 
												$this->restrict2 
												$this->order2
												$query_limit
											");
			
		if(in_array("news", $this->searchTables))
			$query["news"] = $this->DB->query("SELECT *, dct.`cat_id` AS catid" . $this->selectExt2 . "
											FROM `" . DB_TABLE_PREFIX . "news_categories` AS dct 
												LEFT JOIN `" . DB_TABLE_PREFIX . "news` AS dt 
												ON dt.cat_id = dct.cat_id 
											WHERE (" . $dbQueryString2 . ") 
												$this->restrict2 
												$this->order2
												$query_limit
											");
			
		if(in_array("planner", $this->searchTables))
			$query["planner"] = $this->DB->query("SELECT *, dct.`cat_id` AS catid" . $this->selectExt2 . "
											FROM `" . DB_TABLE_PREFIX . "planner_categories` AS dct 
												LEFT JOIN `" . DB_TABLE_PREFIX . "planner` AS dt 
												ON dt.cat_id = dct.cat_id 
											WHERE (" . $dbQueryString2 . ") 
												$this->restrict2 
												$this->order2
												$query_limit
											");
		/*
		if(in_array("gbook", $this->searchTables))
			$query["gbook"] = $this->DB->query("SELECT *
											FROM `" . DB_TABLE_PREFIX . "gbook` 
												WHERE (" . $dbQueryString3 . ") 
												$this->restrict3
												$query_limit
											");
		*/
		#var_dump($query);
		

		$searchResHeader	= "";
		$searchResHeaderTag	= $this->adminPage ? "h4" : "h1";
		
		if(!$this->ajaxSearch)
			$searchResHeader	=	'<div id="siteSearch" class="{t_class:fullrow} {t_class:margintm} {t_class:marginbm}">' . "\r\n" .
									'<' . $searchResHeaderTag . ' class="cc-' . $searchResHeaderTag . '">{s_header:search} {s_common:for} &raquo;' . htmlspecialchars($this->searchPhrase) . '&laquo;</' . $searchResHeaderTag . '>' . "\r\n";
		
		
		if($this->adminPage) {
			$searchResList =	(!empty($this->notice) ? '<span class="notice success searchNote">' . $this->notice . '</span>' : '') . "\r\n";
			$searchResList .=	$searchResHeader;
		}
		else {
			$searchResList =	$searchResHeader;
			$searchResList .=	($this->notice != "" ? '<p class="searchNote {t_class:alert} {t_class:info}">' . $this->notice . '</p>' . "\r\n" : '');
		}

		foreach($query as $sTable => $searchCon) {
				
			if(is_array($searchCon)
			&& count($searchCon) > 0
			) {
			foreach($searchCon as $conPage) {
				
				$searchContent	= "";
				$dataCat		= false;
				$i = 0;
				
				foreach($conPage as $key => $field) {
					
				#var_dump(print_r($key.": ".$field));
				
					if(stripos($field, $this->searchPhrase) !== false) { // Arrayeintrag mit Suchbegriff suchen
						
						if(strpos($key, "tags_" . $this->lang) === 0) {
							$tagArr = explode(",", $field);
							foreach($tagArr as $tag) {
								if(stripos($tag, $this->searchPhrase) !== false)
									$this->searchTerms[] = preg_replace("/(.*)($this->searchPhrase)(.*)/iums", htmlspecialchars("$1") . "<strong>" . htmlspecialchars("$2") . "</strong>" . htmlspecialchars("$3"), trim($tag), 1);
							}
						}
						if(strpos($key, "header_" . $this->lang) === 0)
							$searchContent .= $field." ";
							
						if(strpos($key, "teaser_" . $this->lang) === 0)
							$searchContent .= $field." ";
							
						if(strpos($key, "text_" . $this->lang) === 0)
							$searchContent .= $field." ";
							
						if(strpos($key, "cat_teaser_" . $this->lang) === 0)
							$searchContent .= $field." ";
							
						if(strpos($key, "con_" . $this->lang) === 0 && !strpos($key, "/title_/") && !strpos($key, "/alias_/")) // Title und Alias für Ergebnistext ausschließen
							$searchContent .= $field." ";
							
						if(strpos($key, "gbcomment") === 0)
							$searchContent .= $field." ";
							
					} // Ende if preg_match
				} // Ende foreach
				
				
				// Speicherung der Suchergebnisse im Listenformat in der Variable "$searchResList" zur späteren Ausgabe
				$searchFields = strip_tags(str_replace("\s", " ", str_replace("><", "> <", str_replace("\n", " ", str_replace("\r", "", str_replace("\t", " ", $searchContent)))))); // Gesamttext der Inhalte getrennt durch Zeilenumbruch
				$iconClass	= $sTable;
				$iconKey	= $this->adminPage ? $sTable : 'searchres';
				$tabClass	= 'searchTable {t_class:badge} {t_class:info}';
				
				
				// Platzhalter entfernen (bei nächstem replaceStaText)
				$searchFields = str_replace("{#", "{__", $searchFields);

				
				// Ajax-Suchbegriffe
				// Die möglichen Suchbegriffe für Ajax-Suche auflisten
				if($this->ajaxSearch) {
					$searchContentTerms = "\n" . $searchFields;
					$break = 0;
					
					// Suchvorschläge in Array einlesen
					// !!!WICHTIG: \pL statt \w, da bei Linux sonst keine Umlaute erlaubt sind!
					while(stripos($searchContentTerms, $this->searchPhrase) !== false && count($this->searchTerms) < 25 && $break < 1000) {
						$this->searchTerms[] = preg_replace("/(.*?)([\s\<[:punct:]]+)([\pL\-]*)?(" . $this->searchPhrase . ")([\pL\-]*?)([\b\s\>[:punct:]]+)(.*)/iums", htmlspecialchars("$3") . "<strong>" . htmlspecialchars("$4") . "</strong>" . htmlspecialchars("$5"), $searchContentTerms, 1);
						$searchContentTerms = preg_replace("/(.*?)([\s\<[:punct:]]+)([\pL\-]*?" . $this->searchPhrase . "[\pL\-]*?)([\b\s\>[:punct:]]+)(.*)/iums", "$1$2 $4$5", $searchContentTerms, 1);
						$break++;
					}
					$this->searchTerms = array_unique(array_filter($this->searchTerms));
				}
				
				// Falls Adminseite, statt Targetpage, Datenmodul
				if($this->adminPage && $sTable != "pages")
					$dataCat	= '{s_option:' . $sTable . '}';
				
				
				// Falls Seite
				if($sTable == "pages") {
					$lft			= $conPage['lft'];
					$rgt			= $conPage['rgt'];
					$menuItem		= $conPage['menu_item'];
					$parentAlias 	= CC_USE_FULL_PAGEURL ? $this->getParentAlias($lft, $rgt, $menuItem, $this->lang) : "";
					$link			= $parentAlias . $conPage['alias_' . $this->lang] . PAGE_EXT;
					$title			= '<span class="' . $tabClass . '">{s_common:webpage}</span>' . $conPage['title_' . $this->lang];
					$iconClass		= 'webpage';
					$iconKey		= $this->adminPage ? 'page' : 'searchres';
					if($this->adminPage)
						$link		= 'admin?task=edit&edit_id=' . $conPage['page_id'];
				}
				else {
					if($sTable != "gbook")
						$catAlias	= USE_CAT_NAME ? '/' . Modules::getAlias($conPage['category_' . $this->lang]) : '';
					else
						$catAlias	= "";
				}
				
				// Falls Artikel
				if($sTable == "articles") {
					$targetPage = HTML::getLinkPath($conPage['target_page'], "editLang", false);
					if($conPage['header_' . $this->lang] == "") { // Falls kein Artikel, Cat_Teaser nehmen
						$link = $targetPage . $catAlias . '-' . $conPage['catid'] . 'a' . PAGE_EXT;
						$title = '<span class="' . $tabClass . '">'.($dataCat ? $dataCat : $targetPage).'</span>' . $conPage['category_' . $this->lang];
					}else{
						$link = $targetPage . $catAlias . '/' . Modules::getAlias($conPage['header_' . $this->lang]) . '-' . $conPage['cat_id'] . 'a' . $conPage['id'] . PAGE_EXT;
						$title = '<span class="' . $tabClass . '">'.($dataCat ? $dataCat : $targetPage).'</span>' . $conPage['header_' . $this->lang];
					}
					if($this->adminPage)
						$link	= 'system/access/editModules.php?page=admin&action=edit&mod=articles&id=' . $conPage['id'];
				}
					
				// Falls News
				elseif($sTable == "news") {
					$targetPage = HTML::getLinkPath($conPage['target_page'], "editLang", false);
					if($conPage['header_' . $this->lang] == "") { // Falls keine News, Cat_Teaser nehmen
						$link = $targetPage . $catAlias . '-' . $conPage['catid'] . 'n' . PAGE_EXT;
						$title = '<span class="' . $tabClass . '">'.($dataCat ? $dataCat : $targetPage).'</span>' . $conPage['category_' . $this->lang];
					}else{
						$link = $targetPage . $catAlias . '/' . Modules::getAlias($conPage['header_' . $this->lang]) . '-'. $conPage['cat_id'] .'n'. $conPage['id'] . PAGE_EXT;
						$title = '<span class="' . $tabClass . '">'.($dataCat ? $dataCat : $targetPage).'</span>' . $conPage['header_' . $this->lang];
					}
					if($this->adminPage)
						$link	= 'system/access/editModules.php?page=admin&action=edit&mod=news&id=' . $conPage['id'];
				}
				
				// Falls Termin
				elseif($sTable == "planner") {
					$targetPage = HTML::getLinkPath($conPage['target_page'], "editLang", false);
					if($conPage['header_' . $this->lang] == "") { // Falls kein Termin, Cat_Teaser nehmen
						$link = $targetPage . $catAlias . '-' . $conPage['catid'] . 'p' . PAGE_EXT;
						$title = '<span class="' . $tabClass . '">'.($dataCat ? $dataCat : $targetPage).'</span>' . $conPage['category_' . $this->lang];
					}else{
						$link = $targetPage . $catAlias . '/' . Modules::getAlias($conPage['header_' . $this->lang]) . '-' . $conPage['cat_id'] . 'p' . $conPage['id'] . PAGE_EXT;
						$title = '<span class="' . $tabClass . '">'.($dataCat ? $dataCat : $targetPage).'</span>' . $conPage['header_' . $this->lang];
					}
					if($this->adminPage)
						$link	= 'system/access/editModules.php?page=admin&action=edit&mod=planner&id=' . $conPage['id'];
				}
				/*
				elseif($sTable == "gbook") {
					$link = "?mod=gbook&id=" . $conPage['id'];
					$title = '{s_header:comment} '.$conPage['gbname'] . ' <span class="' . $tabClass . '">({s_header:gbook})</span>';
				}
				*/	
				$resHeader	= '<h2 class="searchResHeader">' . $title . '</h2>'; // Überschrift des jeweiligen Inhalts inklusive Link definieren
				$resLink	= '<a href="' . PROJECT_HTTP_ROOT . '/' . $link . '">';
				
				$searchPos = strpos(strtolower($searchFields), strtolower($this->searchPhrase)); // Bestimmung der Position des Suchbegriffs
				if($searchPos < 40) { // Falls vor dem Suchbegriff weniger als 40 Zeichen sind...
					$startPos = 0; // Startposition für Ausgabe auf 0 setzen
					$searchCut = substr($searchFields, $startPos, 120); // Anzeigetext mit einer Länge von 120 Zeichen von Beginn des Inhaltstextes anzeigen
					$searchCut = preg_replace("/^(.*)\s.{0,20}$/ism", "$1 ...", $searchCut, 1); // letztes Leerzeichen erkennen, dahinter abschneiden und "..." anhängen
				}
				else {
					$startPos = $searchPos - 40; // Andernfalls 40 Zeichen vor dem Suchbegriff mit dem Inhaltstext beginnen...
					$searchCut = substr($searchFields, $startPos, 120); // ...und von da aus 120 Zeichen anzeigen
					$searchCut = preg_replace("/^.{0,20}?\s(.*)\s.{0,20}$/ism", "... $1 ...", $searchCut, 1); // erstes und letztes Leerzeichen erkennen, davor bzw. dahinter abschneiden und "..." anhängen
				}
				
				// Falls HTML5, mark-tag verwenden, sonst span
				if($this->html5)
					$markTag = "mark";
				else
					$markTag = "span";
				
				$searchCut		= preg_replace("/($this->searchPhrase)/i", "<" . $markTag . " class='highlight'>$1</" . $markTag . ">", $searchCut); // Suchbegriff hervorheben
				$searchCut		= "<p>" . str_replace("<br />", " ", $searchCut) . "</p>"; // Absatze im Vorschautext entfernen
				$icon			= parent::getIcon($iconKey, $iconClass . ' searchResIcon inline-icon {t_class:left}');
				$searchResList .= "<li class='searchItem'>" . $resLink . $icon . $resHeader . $searchCut . "</a></li>";
				
			} // Ende count > 0
		
			} // Ende foreach
			
		} // Ende foreach
		
		
		if($searchResList != "") {
			$searchResList =	'<ol class="searchRes">' . "\r\n" .
								$searchResList .
								'</ol>' . "\r\n";
		
			if(!$this->adminPage) {
				$searchResList =	'<div id="searchResults">' . "\r\n" .
									$searchResList .
									'</div>' . "\r\n";
			}
		}
		
		
		if($this->adminPage)
			$queryStringExt .= "task=search"; // Falls Adminbereich
		
		
		
		// Url-Querystring
		$queryStringExt .= "&amp;search=" . htmlspecialchars($this->searchPhrase);
		
		
		$this->searchResults .= $searchResList;
		
		
		#var_dump(count($query). $this->maxRows);
		if($this->totalRows > $this->maxRows && !$this->ajaxSearch) // Falls mehr als die max. Zeilenanzahl erreich ist, Pagination einfügen
			$this->searchResults .= Modules::getPageNav($this->maxRows, $this->totalRows, $this->startRow, $this->pageNum, $queryStringExt, "", false, false);
		
		
		#$this->searchResults = ContentsEngine::replaceStaText($this->searchResults);

		
		if($this->ajaxSearch) {
			
			// Funktion zum Sortieren von Suchvorschlägen nach Länge des Strings
			function sortStrLen($a, $b) {
				
				if(strlen($a) == strlen($b)) {
					return 0;
					echo "0<br>";
				}
				return strlen($a) > strlen($b) ? 1 : -1;
			}			
			
			array_unique(array_filter($this->searchTerms));
			usort($this->searchTerms, "Concise\sortStrLen"); // Sortieren von Suchvorschlägen nach Länge des Strings
			
			// Sortieren von Suchvorschlägen nach Vorkommen am Anfang
			$sortArr = array();
			
			foreach($this->searchTerms as $searchT) {
				if(stripos(strip_tags($searchT), $this->searchPhrase) === 0)
					$sortArr[] = $searchT;
			}
			foreach($this->searchTerms as $searchT) {
				if(stripos(strip_tags($searchT), $this->searchPhrase) > 0)
					$sortArr[] = $searchT;
			}
			$this->searchTerms = $sortArr;
			$output = "";
			if(count($this->searchTerms) > 0) {
				
				if($this->adminPage) {
					$searchUrl	= ADMIN_HTTP_ROOT . '?task=search&';
				}
				else {
					$searchUrl	= HTML::getLinkPath(-1004, "current", true, true) . "?";
				}
				$searchUrl	   .= 'search=';
				
				foreach($this->searchTerms as $term) {
				
			
					if(strlen(strip_tags($term)) <= 100)
						$output .= '<li><a href="' . $searchUrl . htmlspecialchars(strip_tags($term)) . '" class="searchPhraseLink">' . $term . '</a></li>';
				}
				$this->searchTerms = '<ul id="searchTerms" class="{t_class:panel}">' . $output . '</ul>';
			}
			else {
				$this->searchTerms = "";
			}
			return $this->searchTerms;
		}
		else
			return $this->searchResults;
	
	}




	/**
	 * Datenbanksuche nach Elternseiten
	 * 
     * @param	string	lft-Wert der Kindseite
     * @param   string	rgt-Wert der Kindseite
     * @param   string	Nummer des Menüs (menu_item)
     * @param   string	Sprache
     * @param   string	Tabelle (default = pages)
	 * @access	public
	 * @return	string
	 */
	public function getParentAlias($childLft, $childRgt, $menuItem, $lang, $table = "pages")
	{	
		
		$parentAliases = "";
		
		// Datenbanksuche nach Elternknoten des aktuellen Menuepunkts
		$queryParents = $this->DB->query( "SELECT id, alias_" . $lang . " 
												FROM `" . DB_TABLE_PREFIX . $table . "` 
												WHERE lft < " . "'$childLft'" . "AND rgt > " . "'$childRgt'" . " 
												AND lft > 1 
												AND `menu_item` = '$menuItem' 
												ORDER BY lft"
												);
		
		if(count($queryParents) > 0) {
			foreach($queryParents as $parentAlias) {
				$parentAliases .= $parentAlias['alias_' . $lang] . "/";
			}
		}
		
		return $parentAliases;
	}




	/**
	 * Speichert einen Suchebegriff in der DB
	 * 
     * @param	boolean	$remember Falls true, letzten Suchbegriff in Session speichern
	 * @access	public
	 * @return	string
	 */
	public function safeSearchStr($remember = true)
	{	
		
		// Suchbegriff speichern
		$safeSearchStr = $this->DB->query( "INSERT INTO `" . DB_TABLE_PREFIX . "search_strings` 
												SET `search_string` = '$this->searchPhraseDb',
												`results` = " . (int)$this->totalRows
												);
		
		if($remember)
			setcookie('lastSearch', $this->searchPhrase, time() + 3600 * 24 * 7, '/');
		
		return $safeSearchStr;
	}


}
