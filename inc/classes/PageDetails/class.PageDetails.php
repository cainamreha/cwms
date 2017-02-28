<?php
namespace Concise;


/**
 * Class PageDetails
 * 
 */

class PageDetails extends ContentsEngine
{
	
	/**
	 * lft-Wert der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $currentLft = "";
	
	/**
	 * rgt-Wert der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $currentRgt = "";
	
	/**
	 * Beinhaltet die zum Zugang der aktuellen Seite berechtigten Benutzergruppen
	 *
	 * @access public
     * @var    array
     */
	public $userGroupAccess = array();
	
	/**
	 * Fehlerseite erzwingen
	 *
	 * @access public
     * @var    boolean
     */
	public $forceErrorPage = false;

	/**
	 * PageDetails Konstruktor
	 * 
	 * @access	public
	 * @param	object	$DB		DB-Objekt
	 * @param	object	$o_lng	Sprachobjekt
	 */
	public function __construct($DB, $o_lng)
	{
		
		// Vars
		// Datenbankobjekt festlegen
		$this->DB			= $DB; // Datenbankobjekt
	
		// Sprache festlegen
		$this->o_lng		= $o_lng; // Sprachobjekt
		$this->lang			= $this->o_lng->lang; // aktuelle Sprache

		// Security-Objekt
		$this->o_security	= Security::getInstance();

		$this->getAccessDetails();
	
	}
	

	/**
	 * Liefert Informationen zur aktuellen Seite
	 * 
	 * @param	num			$page	Seiten-ID (default = '')
	 * @access	protected
     * @return  string
	 */
	protected function getPageDetails($page = "")
	{
		
		// aktuelle Seite
		$currPage = ""; // db-Var


		// Falls eine Seite aufgerufen wurde, bestimmen ob diese vorhanden und Inhalte auslesen
		$this->currentPage = $this->getCurrentPage();

		
		if($this->currentPage == "")
			$this->currentPage = "_index";
		else
			$currPage = $this->DB->escapeString($this->currentPage); // aktuelle Seite (db sicher)

		$addLang = "";
		

		// Falls bei Sprachwechsel Seitenname in vorheriger Sprache
		if(isset($this->o_lng->oldLang) && $this->o_lng->oldLang != "" && isset($GLOBALS['_GET']['lang']))
			$addLang = " OR alias_" . $this->o_lng->oldLang . " = " . "'$currPage'"; 
		
		
		// DB Daten für Seite auslesen
		$query = $this->getPageQuery($currPage, $addLang);


		
		if(!is_array($query)
		|| count($query) < 1
		)
			return $this->gotoErrorPage();

		
		// Überprüfen ob Seite als Duplikat geladen werden soll
		if($query[0]['copy']
		&& $query[0]['canonical'] > 0
		) {
			$this->canonicalUrl		= $query[0]['canonical'];
			$this->pageRobots		= $query[0]['robots'];
			$query	= $this->queryDuplicatePage($this->canonicalUrl);
			$query[0]['canonical']	= $this->canonicalUrl;
			$query[0]['robots']		= $this->pageRobots;
		}
		
		$this->queryCurrentPage		= $query[0];
	
		// Details zuweisen
		$this->assignPageDetails($this->queryCurrentPage);
		
		
		// Falls keine entsprechende page_id gefunden wurde
		if($this->pageId == "")
			return $this->gotoErrorPage();
		
		
		// Falls Seite "mein Bereich"			
		if($this->pageId == -1001 && $this->group == "guest")
			$this->pageName = "{s_link:account}";

		
		$this->currentLft			= $query[0]['lft'];
		$this->currentRgt			= $query[0]['rgt'];

		
		// Datenbanksuche nach Elternknoten des aktuellen Menuepunkts
		$queryParents = $this->DB->query( "SELECT alias_" . $this->lang . " 
												FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
												WHERE lft < '$this->currentLft' AND rgt > '$this->currentRgt'  
												AND lft > 1  
												AND menu_item > 0
												ORDER BY lft"
												);
		
		// Elternseiten-Aliase in Array speichern
		if(is_array($queryParents)
		&& count($queryParents) > 0
		) {
			foreach($queryParents as $parents) {
				
				parent::$parentAliases[] = $parents['alias_' . $this->lang];
			}
		}
		
		
		// Falls die Seite noch nicht veröffentlicht wurde
		if($this->pageStatus == 0) {
			if($this->editorLog)
				$this->adminNotice = $this->replaceStatext('{s_notice:unpublished}');
			else
				return $this->gotoErrorPage(404, "status=0");
		}
		
		// Falls die Seite für die aktuelle Benutzergruppe nicht zugänglich ist, zur Fehlerseite gehen
		elseif(!$this->checkUserAccess())
			return $this->gotoErrorPage(403, "access=0");

			
		// URL der aktuellen Seite
		if($this->adminPage)
			parent::$currentURLPath = "admin";
		else
			parent::$currentURLPath = HTML::getLinkPath($this->pageId);
		
		parent::$currentURL = PROJECT_HTTP_ROOT . '/' . parent::$currentURLPath;
		
		return $this->queryCurrentPage;

	}
	

	/**
	 * Seiten-Query der Originalseitendaten bei Seiten-Duplikat (canonical url)
	 * 
	 * @access	private
	 * @param	int		$pageID		Page-ID
     * @return  string
	 */
	private function queryDuplicatePage($pageID)
	{
		
		// Suche nach Einträgen in gesetzter Sprache
		$query = $this->DB->query("SELECT *  
										FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
										WHERE `page_id` = $pageID
										");
		return $query;
	
	}

	
	/**
	 * Zugriffsberechtigung für Seitenaufruf checken
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getCurrentPage() 
	{

		$cp = "";

		if($this->forceErrorPage)
			return "_error";
		
		// Falls der Seitenalias gleichzeitig eine Datenmodul-Kategorie ist
		if(!USE_CAT_NAME 
		&& isset($GLOBALS['_GET']['cn']) 
		&& $GLOBALS['_GET']['cn'] != ""
		)
			$cp	= $GLOBALS['_GET']['cn'];
		
		// Aktuelle Seite (Alias) bestimmen
		elseif(isset($GLOBALS['_GET']['page'])) {
			$pa	= explode("/", $GLOBALS['_GET']['page']);
			$cp	= end($pa);
		}

		return $cp;
	
	}

	
	/**
	 * Seitendetails aus db holen
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getPageQuery($currPage, $addLang) 
	{

		// Falls Fehlerseite forciert wird
		if($this->currentPage == "_error") {
						
			// Suche nach Einträgen in gesetzter Sprache
			$query = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "`  
											WHERE `page_id` = -1003 
											");
			#var_dump($query);		
			return $query;
		}
		
		
		// Falls (per rewrite) die Startseite aufgerufen wird
		if($this->currentPage == "_index") {
						
			// Suche nach Einträgen in gesetzter Sprache
			$query = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "`  
											WHERE `index_page` = 1 
											");
			#var_dump($query);		
			return $query;
		}
		
		// Andernfalls Seite ermitteln
		// Suche nach Einträgen in gesetzter Sprache
		$query = $this->DB->query("SELECT *  
										FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
										WHERE `alias_" . $this->lang . "` = '$currPage'" . $addLang . "												
										");
		#var_dump($query);
		
		
		// Falls Inhaltsseite in aktueller Sprache gefunden
		if(is_array($query)
		&& count($query) > 0
		)
			return $query;
		
		
		// Falls keine Inhaltsseite gefunden, überprüfen of der Alias für eine andere Sprache existiert
		$addLang = "";
		
		foreach($this->o_lng->installedLangs as $otherLang)																		  
			$addLang .= "`alias_" . $otherLang . "` = " . "'$currPage' OR "; 
			
		$addLang = substr($addLang, 0, -4);
		
		// Suche nach Einträgen in anderen Sprachen
		$query = $this->DB->query("SELECT *  
										FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
										WHERE " . $addLang . "												
										");
		#var_dump($query);
		
		if(count($query) > 0) {
			
			if(!isset($GLOBALS['_GET']['lang'])) {
				
				foreach($this->o_lng->installedLangs as $newLang) {
					if($query[0]['alias_' . $newLang] == $currPage)
						$this->lang = $newLang;
				}
			}
			
			$this->setSessionVar('lang', $this->lang);
			header("location: " . PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath($query[0]['page_id'], $this->lang));
			exit;
		}
		
		return $query;
	
	}
	
	
	
	/**
	 * Methode zur Überprüfung der Zugangsberechtigung zur Seite
	 * 
	 * @access	private
	 * @return	string
	 */
	private function checkUserAccess()
	{
		
		// Falls Default-Gruppe
		if(in_array("public", $this->userGroupAccess))
			return true;
		// Falls Default-Gruppe
		if(in_array($this->group, $this->userGroupAccess))
			return true;
		// Eigene Benutzergruppe
		if(count(array_intersect($this->ownGroups, $this->userGroupAccess)) > 0)
			return true;
		// Falls Admin
		if($this->adminLog)
			return true;

		return false;
	}
	
	
	
	/**
	 * Detail-Parameter von aktueller Seite
	 * 
	 * @access	protected
	 * @param	array		$query		Query-Array Seiten-Details
	 * @return	string
	 */
	protected function assignPageDetails($query)
	{
	
		$this->pageId				= $query['page_id'];
		$this->currentPage			= $query['alias_' . $this->lang];
		$this->pageName				= $query['title_' . $this->lang];
		$this->pageTitle			= $query['html_title_' . $this->lang];
		$this->pageDescr			= $query['description_' . $this->lang];
		$this->pageKeywords			= $query['keywords_' . $this->lang];
		$this->pageRobots			= $query['robots'];
		$this->canonicalUrl			= $query['canonical'];
		$this->currentTemplate		= $query['template'];
		$this->userGroupAccess		= explode(",", $query['group']);
		$this->pageStatus			= $query['published'];

		if(empty($this->pageTitle))
			$this->pageTitle		= $this->pageName;
	
	}

}
