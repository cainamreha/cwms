<?php
namespace Concise;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Elternklasse für Webseiten-Inhalte für Frontend und Backend
 * 
 */

class ContentsEngine
{

	/**
	 * Beinhaltet ein Datenbankobjekt
	 *
	 * @access protected
     * @var    object
     */
	protected $DB = null;

	/**
	 * Beinhaltet ein Security-Objekt
	 *
	 * @access protected
     * @var    object
     */
	protected $o_security = null;

	/**
	 * Array mit Session-Variablen
	 *
	 * @access protected
     * @var    array
     */
	protected $g_Session = array();

	/**
	 * Array mit Cookies
	 *
	 * @access protected
     * @var    array
     */
	protected $g_Cookie = array();

	/**
	 * Beinhaltet ein EventDispatcher-Objekt
	 *
	 * @access protected
     * @var    object
     */
	protected $o_dispatcher = null;

	/**
	 * Array mit EventListeners
	 *
	 * @access protected
     * @var    array
     */
	protected $eventListeners = array();
	
	/**
	 * Erstellter Formulartoken
	 * 
	 * @access public
	 */
	public static $token = "";

	/**
	 * Enhält Info über Gültigkeit des Formulartokens (bool)
	 * 
	 * @access public
	 */
	public static $tokenOK = false;
	
	/**
	 * Session-Token ist gesetzt
	 *
	 * @access public
     * @var    string
     */
	public static $sessionTokenSet = false;

	/**
	 * Loginstatus
	 * 
	 * @access protected
	 */
	protected $loginStatus = false;
 
    /**
     * Benutzergruppe
     *
     * @access protected
     * @var    string
     */
    protected $group = "public";
 
    /**
     * Eigene Benutzergruppen
     *
     * @access protected
     * @var    array
	 */
    protected $ownGroups = array();

	/**
	 * Admin eingeloggt
	 * 
	 * @access protected
	 */
	protected $adminLog = false;

	/**
	 * Editor oder Admin eingeloggt
	 * 
	 * @access protected
	 */
	protected $editorLog = false;

	/**
	 * Author, Editor oder Admin eingeloggt
	 * 
	 * @access protected
	 */
	protected $backendLog = false;

	/**
	 * Adminseite
	 * 
	 * @access protected
	 */
	protected $adminPage = false;
	
	/**
	 * Beinhaltet ein Sprachobjekt
	 *
	 * @access protected
     * @var    object
     */
	protected $o_lng = null;
	
	/**
	 * Beinhaltet die aktuell eingestellte Sprache
	 *
	 * @access public
     * @var    string
     */
	public $lang = "";
	
	/**
	 * Beinhaltet die aktuelle Sprache für den Adminbereich
	 *
	 * @access public
     * @var    string
     */
	public $adminLang = "";
	
	/**
	 * Beinhaltet die aktuelle Sprache des zu bearbeitenden Inhaltes
	 *
	 * @access public
     * @var    string
     */
	public $editLang = "";
	
	/**
	 * Statische Sprachbausteine
	 *
	 * @access public
     * @var    array
     */
	public static $staText = array();
	
	/**
	 * Seiten-Tabelle.
	 *
	 * @access public
     * @var    string
     */
	public static $tablePages = "pages";
	
	/**
	 * Hauptinhalts-Tabelle.
	 *
	 * @access public
     * @var    string
     */
	public static $tableContents = "contents_main";
	
	/**
	 * Beinhaltet die Tabellen für Seiten- und Templateinhalte
	 *
	 * @access public
     * @var    array
     */
	public static $contentTables = array("contents_main","contents_head","contents_left","contents_right","contents_foot");
	
	/**
	 * Beinhaltet die Tabellen für Templateinhalte
	 *
	 * @access public
     * @var    array
     */
	public static $tablesTplContents = array("contents_head","contents_left","contents_right","contents_foot");
	
	/**
	 * Beinhaltet die Tabellennamen der dbs "contents_xyz" mit Inhalten
	 *
	 * @access public
     * @var    array
     */
	public static $contentAndPreviewTables = array("contents_main", "contents_head", "contents_left", "contents_right", "contents_foot", "contents_main_preview", "contents_head_preview", "contents_left_preview", "contents_right_preview", "contents_foot_preview");
	
	/**
	 * Beinhaltet Inhaltsbereiche
	 *
	 * @access public
     * @var    array
     */
	public static $areasTplContents = array("head", "left", "right", "foot");
	
	/**
	 * Beinhaltet eine Inhaltsbereichs-Tabelle mit Datenbankinhalten
	 *
	 * @access public
     * @var    array
     */
	public $contentsTab = "";
	
	/**
	 * Beinhaltet eine Inhaltsbereichs-Tabelle mit Datenbankinhalten
	 *
	 * @access public
     * @var    array
     */
	public $contentsBaseTab = "";
	
	/**
	 * Objekt mit Details zur aktuellen Seite
	 *
	 * @access protected
     * @var    object
     */
	protected $o_page = null;
	
	/**
	 * Beinhaltet den Query-Details zur aktuellen Seite
	 *
	 * @access public
     * @var    array
     */
	public $queryCurrentPage = array();
	
	/**
	 * Beinhaltet den Alias der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $currentPage = "";
	
	/**
	 * Beinhaltet die page_id der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageId = "";
	
	/**
	 * Beinhaltet den Namen der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageName = "";
	
	/**
	 * Beinhaltet die URL der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public static $currentURL = "";
	
	/**
	 * Beinhaltet den URL-Pfad der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public static $currentURLPath = "";
	
	/**
	 * Beinhaltet den HTML-Titel der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageTitle = "";
		
	/**
	 * Beinhaltet einen Prefix für den HTML-Title Metatag
	 *
	 * @access public
     * @var    string
     */
	public $htmlTitlePrefix = "";
	
	/**
	 * Beinhaltet die Meta-Description der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageDescr = "";
	
	/**
	 * Beinhaltet die Meta-Keywords der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageKeywords = "";
	
	/**
	 * Beinhaltet eine Bild-Url für die aktuelle Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageImage = "";
	
	/**
	 * Beinhaltet die Meta-Robots defs der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageRobots = "";
	
	/**
	 * Beinhaltet eine canonical Url für die aktuelle Seite
	 *
	 * @access public
     * @var    int
     */
	public $canonicalUrl = null;
	
	/**
	 * Html-Body class
	 *
	 * @access public
     * @var    array
     */
	public $bodyClassStrings = array();
	
	/**
	 * Beinhaltet die ID der Root-Elternseite
	 *
	 * @access public
     * @var    string
     */
	public static $rootPageId = "";
	
	/**
	 * Beinhaltet die ID der Elternseite
	 *
	 * @access public
     * @var    string
     */
	public static $parentPageId = "";
	
	/**
	 * Beinhaltet die Aliase der Elternknoten der aktuellen Seite
	 *
	 * @access public
     * @var    array
     */
	public static $parentAliases = array();
	
	/**
	 * Beinhaltet den Templatenamen der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $currentTemplate = "";
	
	/**
     * Namen der include Templates
	 *
	 * @access protected
     * @var    array
     */
	protected $incTemplates = array();
	
	/**
	 * Beinhaltet den Veröffentlichungsstatus der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $pageStatus = "";
	
	/**
	 * Status Code der aktuellen Seite
	 *
	 * @access public
     * @var    string
     */
	public $statusCode = 200;
	
	/**
	 * Beinhaltet das Array der Datenbankquery
	 *
	 * @access protected
     * @var    array
     */
	protected $queryContents = array();
	
	/**
	 * Beinhaltet das Array aus Datenbankinhalten
	 *
	 * @access public
     * @var    array
     */
	public $contents = array();
	
	/**
	 * Beinhaltet das Array aus Datenbankinhalten der Template Areas
	 *
	 * @access public
     * @var    array
     */
	public $contents_tpl = array();
	
	/**
	 * Core Inhalts-Elementtypen
	 *
	 * @access protected
     * @var    array
     */
	protected $systemContentElements 	= array(	"loginpage",
													"errorpage",
													"searchpage",
													"logoutpage",
													"regpage",
													"userpage"
											);
	

	/**
	 * True falls Inhaltsseiten mit noch nicht übernommenen Änderungen existieren
	 *
	 * @access public
     * @var    boolean
     */
	public $isPreview	= false;
	public $preview 	= "";

	/**
	 * Es liegen nicht übernommenen Änderungen an Seiten/Templatebereichen vor
	 *
	 * @access public
     * @var    boolean
     */
	public $diffCon = false;

	/**
	 * Beinhaltet Seiten-Ids von Inhaltsseiten/Templatebereichen mit noch nicht übernommenen Änderungen
	 *
	 * @access public
     * @var    array
     */
	public $diffConIDs = array();

	/**
	 * Beinhaltet Seiten-Aliase von Inhaltsseiten/Templatebereichen mit noch nicht übernommenen Änderungen
	 *
	 * @access public
     * @var    array
     */
	public $diffConAlias = array();

	/**
	 * Beinhaltet das letzte Änderungsdatum von Inhaltsseiten mit noch nicht übernommenen Änderungen
	 *
	 * @access public
     * @var    array
     */
	public $diffConDate = array();

	/**
	 * Beinhaltet den Tabellennamen mit noch nicht übernommenen Änderungen
	 *
	 * @access public
     * @var    array
     */
	public $diffConTables = array();

	/**
	 * FE-Vars
	 *
	 * @access public
     * @var    array
     */
	public $feModeStatus		= "on";
	public static $feMode		= false;
	public static $phMode		= false;
	public static $newElement	= "";
	public static $pasteElement	= "";
	
	/**
	 * Array mit Theme-Setup-Definitionen
	 *
	 * @access public
     * @var    array
     */
	public $themeConf = array();
	
	/**
	 * Array mit Admin-Theme-Setup-Definitionen
	 *
	 * @access protected
     * @var    array
     */
	protected $adminThemeConf = array();
	
	/**
	 * Array mit Theme-Style-Definitionen
	 *
	 * @access public static
     * @var    array
     */
	public static $styleDefs = array();
	
	/**
	 * Array mit Admin-Theme-Style-Definitionen
	 *
	 * @access protected
     * @var    array
     */
	protected $adminStyleDefs = array();
	
	/**
	 * Array mit Icon-Style-Definitionen
	 *
	 * @access public static
     * @var    array
     */
	public static $iconDefs = array();
	
	/**
	 * Array mit Icon-Style-Definitionen
	 *
	 * @access public static
     * @var    array
     */
	public static $device = array();
	
	/**
     * Default Templates
     *
     * @access public
     * @var    array
     */
    public $defaultTemplates = array(	"standard.tpl",
										"standard-leftcol.tpl",
										"standard-rightcol.tpl",
										"twocol-left.tpl",
										"twocol-right.tpl",
										"fullwidth.tpl",
										"fullwidth-leftcol.tpl",
										"fullwidth-rightcol.tpl"
									);
	
	/**
     * Vorhandene Templates im Ordner "templates"
     *
     * @access public
     * @var    array
     */
    public $existTemplates = array();
	
	/**
	 * Beinhaltet ein Templateobjekt
	 *
	 * @access public
     * @var    object
     */
	public static $o_mainTemplate = null;
		
	/**
	 * Beinhaltet ein Loop-Template
	 *
	 * @access public
     * @var    object
     */
	public static $loopTemplate = "contents.tpl";
		
	/**
	 * Beinhaltet ein HTMLobjekt
	 *
	 * @access public
     * @var    object
     */
	public static $o_html = null;
		
	/**
	 * Html5 verwenden
	 *
	 * @access public
     * @var    object
     */
	public $html5 = HTML5;
		
	/**
	 * HeadJS verwenden
	 *
	 * @access public
     * @var    object
     */
	public $headJS = HEADJS;

	/**
	 * Menues
	 *
	 * @access public
     * @var    string
     */
	public $mainMenu;
	public $subMenu;
	public $parRootMenu;
	public $parSubMenu;
	public $topMenu;
	public $bcNav;
	public $footMenu;
	
	/**
	 * Beinhaltet eine potentielle Benachrichtigung
	 *
	 * @access public
     * @var    string
     */
	public $notice = "";
	
	/**
	 * Beinhaltet eine potentielle Erfolgsmeldung
	 *
	 * @access public
     * @var    string
     */
	public $success = "";
	
	/**
	 * Beinhaltet eine potentielle Fehlermeldung
	 *
	 * @access public
     * @var    string
     */
	public $error = "";
	
	/**
	 * Beinhaltet eine Hinweismeldung
	 *
	 * @access public
     * @var    string
     */
	public $hint = "";
	
	/**
	 * Beinhaltet eine potentielle Meldung für den Admin
	 *
	 * @access public
     * @var    string
     */
	public $adminNotice = "";
	
	/**
	 * Loggin-Objekt
	 *
	 * @access public
     * @var    Boolean
     */
	public static $LOG = null;
 
    /**
     * ID  aktuellen Datensatzes
	 *
     * @access protected
     * @var    string
     */
    protected $dataID = "";
 
    /**
     * ID des aktuellen Datensatzes (per get übermittelt)
	 *
     * @access protected
     * @var    string
     */
    protected $ID = "";
 
    /**
     * Kategorie-ID, die per get übermittelt wurde
     *
     * @access protected
     * @var    string
     */
    protected $catID = "";
 
    /**
     * Kategorie-ID des eingebetteten Moduls
     *
     * @access protected
     * @var    string
     */
    protected $rootCatID = "";
 
    /**
     * Kategorie-IDs aller Kindkategorien einer Elternkategorie
     *
     * @access protected
     * @var    array
     */
    protected $childCatIDs = array();
 
    /**
     * Kommentare
     *
     * @access protected
     * @var    string
     */
    protected $comments = "";
 
    /**
     * Kommentarformularstatus
     *
     * @access protected
     * @var    boolean
     */
    protected $commentForm = false;
 
    /**
     * Rating
     *
     * @access protected
     * @var    string
     */
    protected $rating = "";
 
    /**
     * Zielseite für Module
	 *
     * @access protected
     * @var    string
     */
    protected $targetPage = "";
  
    /**
     * Zielseitenurl für Module
	 *
     * @access public
     * @var    string
     */
    public $targetUrl = "";
  
    /**
     * Url-Pfad für (Eltern-)Kategorien
     *
     * @access public
     * @var    string
     */
    public $urlCatPath = "";
  
    /**
     * Url-Pfad für Elternkategorie
     *
     * @access public
     * @var    string
     */
    public $urlParentCatPath = "";
  
    /**
     * Pfadangaben des aktuellen Artikels (für Breadcrumb-Navi)
     *
     * @access public
     * @var    array
     */
    public static $dataBCPath = array();

    /**
     * GET-Parameter
     *
     * @access public
     * @var    string
     */
    public $queryString = "";
  
	/**
     * Pfadelemente (mit Elternkategorien) des aktuellen Datensatzes
	 *
     * @access public
     * @var    array
     */
    public $activeParents = array();

	/**
     * Anzeigemodus für Datensätze
	 *
     * @access public
     * @var    string
     */
    public $displayMode = "";
  
	/**
     * Erweiterer Select-String
	 *
     * @access public
     * @var    string
     */
    public $selectExt = "";
  
	/**
     * Sortierung der Datensätze
	 *
     * @access public
     * @var    string
     */
    public $order = "";
  
	/**
     * Anzahl an anzuzeigenden Datensätzen
	 *
     * @access public
     * @var    string
     */
    public $limit = "";
  
	/**
	 * Relevante Tabellennamen für Kommentare
	 *
	 * @access public
     * @var    array
     */
	public $dataTables = array();

	/**
	 * Beinhaltet CSS-Dateinamen zur Einbindung im Html-Head-Bereich
	 *
	 * @access public
     * @var    array
     */
	public $cssFiles = array();

	/**
	 * Beinhaltet Javascript-Dateinamen zur Einbindung im Html-Head-Bereich
	 *
	 * @access public
     * @var    array
     */
	public $scriptFiles = array();

	/**
	 * Beinhaltet Javascript-Dateinamen zur Einbindung im Html-Body-Bereich
	 *
	 * @access public
     * @var    array
     */
	public $scriptFilesBody = array();

	/**
	 * Beinhaltet Javascript-Code zur Einbindung im Html-Head-Bereich
	 *
	 * @access public
     * @var    array
     */
	public $scriptCode = array();
	
	/**
	 * Aufruf via Ajax
	 *
	 * @access protected
     * @var    boolean
     */
	protected $ajax = false;
	
	/**
	 * Linkaufruf via Ajax
	 *
	 * @access protected
     * @var    string
     */
	protected $loadViaAjax = "";

	/**
	 * Beinhaltet ein Eltern-Objekt
	 *
	 * @access public
     * @var    object
     */
	public $parentObj = null;

	/**
	 * Formularzielscript
	 *
	 * @access protected
     * @var    string
     */
	protected $formAction;
	
	/**
	 * Beinhaltet Newsfeeds für den Headbereich
	 *
	 * @access public
     * @var    string
     */
	public $feedHeadLinks = "";
		
	/**
	 * Beinhaltet installierte Plug-Ins
	 *
	 * @access public
     * @var    array
     */
	public $installedPlugins = array();
	
	/**
	 * Beinhaltet aktive Plugins
	 *
	 * @access public
     * @var    array
     */
	public $activePlugins = array();

	
	/**
	 * ContentsEngine Konstruktor
	 * 
	 * @access	public
	 * @param	object	$DB		DB-Objekt
	 * @param	object	$o_lng	Sprachobjekt
	 * @param	boolean	$ajax	Aufruf via Ajax (default = false)
	 */
	public function __construct($DB, $o_lng, $ajax = false)
	{
		
		// Vars
		// Datenbankobjekt festlegen
		$this->DB			= $DB; // Datenbankobjekt

		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();
		
		// Cookies-Array
		$this->g_Cookie		= Security::getCookies();

		// EventDispatcher-Objekt
		$this->o_dispatcher	= new EventDispatcher();

		// Token auslesen/erstellen
		self::$tokenOK		= $this->o_security->checkToken();
		self::$token		= $this->o_security->getToken();
	
		// Sprache festlegen
		$this->o_lng		= $o_lng; // Sprachobjekt
		$this->lang			= $this->o_lng->lang; // aktuelle Sprache
		$this->adminLang	= $this->o_lng->getAdminLang(); // aktuelle Sprache
		self::$staText		= $this->o_lng->staText;

		// Ajax-Aufruf
		$this->ajax			= $ajax;
	
	}

	
	/**
	 * Initialisiert aktuelle Seite (Frontend-Inhalte)
	 * 
	 * @param	string	$page	Seitenangabe (default = "")
	 * @access	public
	 */
	public function initPage($page = "")
	{
		
		// Falls Installationsseite
		if($page == "install")
			return $this->getInstallPageDetails();

		
		// Inhaltstabellen
		self::setContentTables();

		
		// Benutzer Berechtigungen holen
		$this->getAccessDetails();

		
		// Details zur Aufgerufenen Seite holen
		$this->readPageDetails();
	
		
		// Zugriffsberechtigung für Seite überprüfen
		$this->checkPageAccess();
	
		
		// Logging-Objekt erstellen
		$this->makeLoggingObject($this->DB, $this->g_Session);
		
		
		// Falls Änderungenvorschau und berechtigter Benutzer
		if($this->backendLog) {

			$this->editLang		= $this->o_lng->editLang; // aktuelle Sprache
			
			if($this->editorLog) {
			
				// Auf vorliegende Änderungen prüfen
				$this->checkPreviewStatus($this->editLang);
				
				if($this->isPreview)
					self::$tableContents	.= $this->preview; // Inhaltstabelle ist Vorschautabelle
			}
		}
		
		
		// Http-Root und Javascript Vars übergeben
		/*
		if(!empty($this->scriptCode["jsvars"]))
			$this->scriptCode["jsvars"] = $this->setScriptVars(true, false) . $this->scriptCode["jsvars"];
		else
			$this->scriptCode["jsvars"] = $this->setScriptVars(true, false);
*/
		
		
		// Template-Objekt erstellen
		$this->getTplObject();

		
		// Ggf.DebugConsole einbinden
		$this->getDebugConsole();
		

		// FE-Access
		if($this->editorLog 
		&& !$this->adminPage 
		&& class_exists("Concise\ContentsEdit")
		) {
			$this->getFrontEndAccess(); // Methode aus Kindklasse "ContentsEdit"
		}

	}
	

	/**
	 * Browser-Caching abschalten
	 * 
	 * @access	protected
     * @return  string
	 */
	protected function setBrowserNoCache()
	{
	
		$date	= gmdate( 'D, d M Y H:i:s', time() - (60*60*24)) . ' GMT';
		header('Expires: ' . $date);
		header('Last-Modified: ' . $date);
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
	
	}
	

	/**
	 * Liefert Benutzergruppe(n) des geloggten Benutzers
	 * 
	 * @access	protected
     * @return  string
	 */
	protected function getUserGroups()
	{	
		
		// Benutzergruppe bestimmen
		$this->group		= $this->o_security->get('group');
		
		// Eigene Benutzergruppen bestimmen
		$this->ownGroups	= $this->o_security->get('ownGroups');
	
	}
	

	/**
	 * Liefert Zugangsberechtigungen des geloggten Benutzers
	 * 
	 * @access	protected
     * @return  string
	 */
	protected function getAccessDetails()
	{	
	
		// Benutzergruppe(n)
		$this->getUserGroups(); // Gruppe(n) holen
		
		// Backend-Log auf true setzen, falls geloggter Admin/Editor/Author
		if($this->o_security->get('loginStatus') === true && $this->group != "") {

			$this->backendLog	= $this->o_security->get('backendLog');
			$this->editorLog	= $this->o_security->get('editorLog');
			$this->adminLog		= $this->o_security->get('adminLog');
			$this->adminPage	= $this->o_security->get('adminPage');
		}

		// Admin-Page defaults setzen, falls Admin- oder Installationsseite
		if($this->adminPage && !$this->ajax) {
			$this->html5			= true;
			$this->headJS			= true;
		}
	
	}
	

	/**
	 * Liefert Informationen zur Installationsseite
	 * 
	 * @access	private
     * @return  string
	 */
	private function getInstallPageDetails()
	{

		$this->getThemeDefaults("admin");
		$this->currentPage			= "_install";
		$this->cssFiles[]			= "access/css/normalize.css";
		$this->cssFiles[]			= "system/themes/" . ADMIN_THEME . "/css/admin.min.css";
		$this->cssFiles[]			= "system/themes/" . ADMIN_THEME . "/css/icons.min.css";
		$this->cssFiles[]	  		= "install/install.css";
		$this->cssFiles[]	  		= "extLibs/jquery/alerts/jquery.alerts.css";
		$this->scriptFiles["headjs"]	= "extLibs/headjs/head.min.js";
		$this->scriptFiles["alerts"]	= "extLibs/jquery/alerts/jquery.alerts.min.js";
		$this->scriptFiles["history"]	= "extLibs/jquery/history/jquery.history.min.js";
		$this->scriptFiles["admin"]		= "system/access/js/admin.min.js";
		$this->scriptCode["jsvars"]		= $this->setScriptVars(true, true, self::$staText['javascript']);
		$this->validateToken();
		$this->setToken();
		return true;
	}
	

	/**
	 * Liefert Informationen zur aktuellen Seite
	 * 
	 * @access	private
     * @return  string
	 */
	private function readPageDetails()
	{
		
		require_once PROJECT_DOC_ROOT."/inc/classes/PageDetails/class.PageDetails.php";
		
		$this->o_page			= new PageDetails($this->DB, $this->o_lng);
		$this->queryCurrentPage	= $this->o_page->getPageDetails();
		$this->assignPageDetails($this->o_page);

	}
	
	
	
	/**
	 * Detail-Parameter von aktueller Seite
	 * 
	 * @access	protected
	 * @param	object		$o_page	Seiten-Details-Objekt
	 * @return	string
	 */
	protected function assignPageDetails($o_page)
	{

		$this->pageId				= $o_page->pageId;
		$this->currentPage			= $o_page->currentPage;
		$this->pageName				= $o_page->pageName;
		$this->pageTitle			= $o_page->pageTitle;
		$this->htmlTitlePrefix		= $o_page->htmlTitlePrefix;
		$this->pageDescr			= $o_page->pageDescr;
		$this->pageKeywords			= $o_page->pageKeywords;
		$this->pageRobots			= $o_page->pageRobots;
		$this->pageImage			= $o_page->pageImage;
		$this->canonicalUrl			= $o_page->canonicalUrl;
		$this->currentTemplate		= $o_page->currentTemplate;
		$this->userGroupAccess		= $o_page->userGroupAccess;
		$this->pageStatus			= $o_page->pageStatus;
	
	}


	
	/**
	 * Zugriffsberechtigung für Seitenaufruf checken
	 * 
	 * @access	private
	 * @return	string
	 */
	private function checkPageAccess() 
	{

		// Zugriffsberechtigun überprüfen, falls User-/Adminseite
		if(!$this->backendLog) {
			
			// Falls versucht wird die Benutzerseite (Mein Bereich) aufzurufen und kein Shopbenutzer oder Gast, zur Fehlerseite gehen
			if(!$this->o_security->get('loginStatus') && $this->pageId == -1007)
				$this->gotoErrorPage(403);
			
			// Falls versucht wird die Adminseite aufzurufen, zur Fehlerseite gehen
			if($this->pageId == -1001)
				$this->gotoErrorPage(403);
		}

	}


	
	/**
	 * Zur Fehlerseite gehen
	 * 
	 * @access	protected
	 * @param	int $sc Statuscode (default = 404)
	 * @param	string $ec Fehlercode (default = '')
	 * @return	string
	 */
	protected function gotoErrorPage($sc = 404, $ec = "") 
	{

		require_once PROJECT_DOC_ROOT . "/inc/classes/ErrorPage/class.ErrorPage.php";
		
		// ErrorPageobjekt
		$o_ErrorPage		= new ErrorPage();
		$systemContent		= $o_ErrorPage->setStatusCodeHeader($sc);
		
		header("location: " . PROJECT_HTTP_ROOT . "/error" . PAGE_EXT . "?sc=" . $sc . ($ec != "" ? "&" . $ec : "")); // zur Fehlerseite gehen
		exit;
		return;
	
	}


	
	/**
	 * Fehlerseite anzeigen
	 * 
	 * @access	protected
	 * @param	string $ec Fehlercode (default = '')
	 * @return	string
	 */
	protected function showErrorPage($ec = "") 
	{
	
		require_once PROJECT_DOC_ROOT."/inc/classes/PageDetails/class.PageDetails.php";
		
		$this->o_page					= new PageDetails($this->DB, $this->o_lng);
		$this->o_page->forceErrorPage	= true;
		$this->queryCurrentPage			= $this->o_page->getPageDetails();
		$this->assignPageDetails($this->queryCurrentPage);
	
	}



	/**
	 * Logging-Object erstellen
	 * 
	 * @access	private
	 * @param	object	$DB DB-Objekt
	 * @param	array	$g_Session DB-Objekt
	 * @return	string
	 */
	private function makeLoggingObject($DB, $g_Session) 
	{

		//Logging
		self::$LOG = new Log($DB, $g_Session);

	}
	


	/**
	 * Bestimmen der Inhaltstabellen
	 * 
	 * @access	public
	 * @return	string
	 */
	public function setContentTables() 
	{

		self::$tablePages			= $this->DB->escapeString($GLOBALS["tablePages"]);
		self::$tableContents		= $GLOBALS["tableContents"];
		self::$tablesTplContents	= $GLOBALS["tableTplContents"];
		self::$areasTplContents		= $GLOBALS["areasTplContents"];
		
		
		self::$contentTables		= array(self::$tableContents);
		self::$contentTables		= array_unique(array_filter(array_merge(self::$contentTables, self::$tablesTplContents)));
		
		// Preview-Tabellen
		$adTables = array();
		
		foreach(self::$contentTables as $key => $value) {
			$adTables[]	= $value . "_preview";
		}
		
		self::$contentAndPreviewTables	= array_merge(self::$contentTables, $adTables);

	}
	
	
	
	/**
	 * Methode zur Überprüfung auf Änderungen an Inhaltselementen
	 * 
	 * @param	string $lang Sprache
	 * @access	public
	 * @return	string
	 */
	public function checkPreviewStatus($lang) 
	{
	
		foreach(self::$contentTables AS $conTable) {
			
			$conTableDB	= $this->DB->escapeString(DB_TABLE_PREFIX . $conTable);
			
			// Suche nach Spaltennamen
			$queryCols = $this->DB->query("SHOW COLUMNS 
												FROM `$conTableDB`
												");
		
			$diffConTab			= array();
				
			foreach($queryCols as $queryCol) {
				
				// Suche nach unterschiedichen Inhaltsspalten
				$queryConDiff = $this->DB->query( "SELECT *
													  	FROM `" . $conTableDB . "` AS c 
														LEFT JOIN `" . $conTableDB . "_preview` AS cp  
														ON c.`page_id` = cp.`page_id` 
														WHERE BINARY c.`" . $queryCol['Field'] . "` != BINARY cp.`" . $queryCol['Field'] . "`
														");
				
				#var_dump($queryConDiff);
				
				if(count($queryConDiff) > 0) {
					
					$this->diffCon = true;
					
					foreach($queryConDiff as $conDiff) {
						
						// Suche nach Seitenalias Inhaltsspalten
						$queryDiffPage = $this->DB->query( "SELECT `alias_" . $lang . "`, UNIX_TIMESTAMP(`mod_date`) as md 
																FROM `" . DB_TABLE_PREFIX . self::$tablePages . "`
																WHERE `page_id` = '" . $conDiff['page_id'] . "'
																");
						
						#var_dump(print_r($queryDiffPage)."<br><br>");
						
						if(in_array($conDiff['page_id'], $diffConTab))
							continue;
						
						
						$diffConTab[]			= $conDiff['page_id'];
						$this->diffConIDs[]		= $conDiff['page_id'];
						
						// Falls Template
						if(!is_numeric($conDiff['page_id'])) {
							$conTabExpl				= explode("_", $conTable);
							$this->diffConAlias[]	= $conDiff['page_id'] . " (" . end($conTabExpl) . ")";
							$this->diffConDate[]	= 0;
						}
						// Andernfalls Seite
						else {
							if(isset($queryDiffPage[0]['alias_' . $lang])) {
								$this->diffConAlias[]	= $queryDiffPage[0]['alias_' . $lang];
								$this->diffConDate[]	= $queryDiffPage[0]['md'];
							}
						}
					}
				}
			}
	
			$this->diffConTables["$conTable"] = $diffConTab;
			
		}
		
		$this->diffConAlias = array_unique($this->diffConAlias);
		
		// Aktuelle Seite (falls Startseite, nach index = 1 suchen, sonst nach alias)
		if(!isset($GLOBALS['_GET']['page'])
		|| $GLOBALS['_GET']['page'] == "_index"
		)
			$currPageDb = "`index_page` = 1";
		else {
			$pagePathExpl	= explode("/", $GLOBALS['_GET']['page']);
			$currPageDb = "`alias_" . $lang . "` = '" . $this->DB->escapeString(end($pagePathExpl)) . "'";
		}
		
		// Suche nach Einträgen in gesetzter Sprache
		$queryPageId = $this->DB->query(  "SELECT `page_id`, `template` 
												FROM `" . DB_TABLE_PREFIX . self::$tablePages . "` 
												WHERE " . $currPageDb . "
												");
				
		#var_dump($queryPageId);
		
		// Falls ein Eintrag zur aktuellen Seite gefunden wurde
		if(count($queryPageId) > 0) {
			// Falls die Seite oder das Template im Änderungsarray vorhanden ist, die Previewinhalte anzeigen
			if(in_array($queryPageId[0]['page_id'], $this->diffConIDs) || in_array($queryPageId[0]['template'], $this->diffConIDs)) {
				$this->isPreview	= true;
				$this->preview		= "_preview"; // Falls Änderungen vorliegen -> Tabellenzusatz "_preview"
			}
		}

		return $this->isPreview;
		
	}


	/**
	 * Theme-Defaults einlesen
	 *
	 * @param	$themeType			Theme-Typ (default = 'fe')
	 * @param	$setThemeDefaults	set theme defaults (default = true)
	 * @access	public
     * @return  string
	 */
	public function getThemeDefaults($themeType = "fe", $setThemeDefaults = true)
	{
	
		// Theme-Defaults laden
		require_once PROJECT_DOC_ROOT."/inc/classes/Theme/class.Theme.php";
		
		$o_theme	= new Theme($themeType);
		$tDefs		= $o_theme->getThemeConfig();
		
		// Mobile detect
		self::$device["isMobile"]	= $o_theme->checkMobileDevice("mobile");
		self::$device["isTablet"]	= $o_theme->checkMobileDevice("tablet");
		self::$device["isPhone"]	= self::$device["isMobile"] && !self::$device["isTablet"] ? true : false;
		
		if($setThemeDefaults) {
			$this->themeConf	= $tDefs;
			$this->setThemeDefaults();
		}
		
		return $tDefs;

	}


	/**
	 * Theme-Defaults setzten
	 *
	 * @access	public
     * @return  string
	 */
	public function setThemeDefaults()
	{

		if(isset($this->themeConf["defaults"]["html5"])) $this->html5 = $this->themeConf["defaults"]["html5"];
		if(isset($this->themeConf["defaults"]["headjs"])) $this->headJS = $this->themeConf["defaults"]["headjs"];
	
		if(isset($this->themeConf["class"])) self::$styleDefs	= $this->themeConf["class"];
		if(isset($this->themeConf["icons"])) self::$iconDefs	= $this->themeConf["icons"];
	
	}


	/**
	 * Theme-Styles einlesen
	 *
	 * @param	boolean 	$fe	Frontend-Theme neu einlesen
	 * @access	public
     * @return  string
	 */
	public function getThemeStyles($fe = false)
	{

		if($fe)
			return @parse_ini_file(PROJECT_DOC_ROOT.'/themes/' . THEME . '/theme_styles.ini', true);
		else
			return $this->themeConf;
	
	}


	/**
	 * Theme-Config parsen
	 *
	 * @param	boolean 	$fe	Frontend-Theme neu einlesen
	 * @access	public
     * @return  string
	 */
	public function parseThemeDefaults()
	{

		return @parse_ini_file(PROJECT_DOC_ROOT.'/themes/' . THEME . '/theme_config.ini', true);
	
	}


	/**
	 * Button generieren
	 * 
	 * @param	array 	$def 		Button-Definitionen
	 * @param	string 	$iconPos 	Icon-Position (default = 'left')
	 * @access	public static
     * @return  boolean
	 */
	public static function getButton($def, $iconPos = "left")
	{
		
		$btnType	= !empty($def["type"]) ? $def["type"] : 'button';
		$btnName	= !empty($def["name"]) ? ' name="' . $def["name"] . '"' : '';
		$btnVal		= !empty($def["value"]) ? $def["value"] : '';
		$btnText	= isset($def["text"]) ? $def["text"] : $btnVal;
		$btnID		= !empty($def["id"]) ? ' id="' . $def["id"] . '"' : '';
		$btnClass	= !empty(self::$styleDefs['ccbutton']) ? self::$styleDefs['ccbutton'] . ' ' : '';
		$btnClass  .= !empty(self::$styleDefs['btn']) ? self::$styleDefs['btn'] : 'btn';
		$btnClass  .= !empty($def["class"]) ? ' ' . $def["class"] : '';
		$btnTitle	= !empty($def["title"]) ? ' title="' . $def["title"] . '"' : '';
		$btnAttr	= !empty($def["attr"]) ? ' ' . $def["attr"] : '';
		$icon		= "";
		
		if(!empty($def["icon"])) {
			$iconType	= $def["icon"];
			$iconClass	= !empty($def["iconclass"]) ? $def["iconclass"] : '';
			$iconAttr	= !empty($def["iconattr"]) ? $def["iconattr"] : '';
			$iconText	= isset($def["icontext"]) ? $def["icontext"] : '&nbsp;';
			$icon		= self::getIcon($iconType, $iconClass, $iconAttr, $iconText);
		}
		
		$output =	'<button type="' . $btnType . '"' . $btnID . ' class="' . $btnClass . '"' . $btnName . ' value="' . $btnVal . '"' . $btnTitle . $btnAttr . '>' .
					($iconPos == "left" ? $icon : '') .
					$btnText .
					($iconPos == "right" ? $icon : '') .
					'</button>';
		
		return $output;
	
	}


	/**
	 * Button-Link generieren
	 * 
	 * @param	array 	$def 		Button-Definitionen
	 * @param	string 	$iconPos 	Icon-Position
	 * @access	public static
     * @return  boolean
	 */
	public static function getButtonLink($def, $iconPos = "left")
	{
	
		$href		= !empty($def['href']) ? $def['href'] : '#';
		$text		= !empty($def['text']) ? $def['text'] : '';
		$btnID		= !empty($def["id"]) ? ' id="' . $def["id"] . '"' : '';
		$btnClass	= !empty(self::$styleDefs['ccbutton']) ? self::$styleDefs['ccbutton'] . ' ' : '';
		$btnClass  .= !empty(self::$styleDefs['btn']) ? self::$styleDefs['btn'] : 'btn';
		$btnClass  .= !empty($def["class"]) ? ' ' . $def["class"] : '';
		$btnTitle	= !empty($def["title"]) ? ' title="' . $def["title"] . '"' : '';
		$btnAttr	= !empty($def["attr"]) ? ' ' . $def["attr"] : '';
		$icon		= "";
		
		if(!empty($def["icon"])) {
			$iconType	= $def["icon"];
			$iconClass	= !empty($def["iconclass"]) ? $def["iconclass"] : '';
			$iconAttr	= !empty($def["iconattr"]) ? $def["iconattr"] : '';
			$iconText	= isset($def["icontext"]) ? $def["icontext"] : '&nbsp;';
			$icon		= self::getIcon($iconType, $iconClass, $iconAttr, $iconText);
		}
	
		$output	=	'<a href="' . $href . '"' . $btnID . ' class="' . $btnClass . '"' . $btnTitle . $btnAttr . '>' .
					($iconPos == "left" ? $icon : '') .
					$text .
					($iconPos == "right" ? $icon : '') .
					'</a>';
		
		return $output;
	
	}	


	/**
	 * Icon-Tag generieren
	 * 
	 * @param	string 	$iType		Icon-Typ (Class-Suffix)
	 * @param	string 	$iClass		Icon-Class (default = '')
	 * @param	string 	$iAttr	 	Icon-Attribute (default = '')
	 * @param	string 	$iText	 	Icon-Text (default = '&nbsp;')
	 * @param	string 	$iSpan	 	Icon-Span (default = true)
	 * @access	public static
     * @return  boolean
	 */
	public static function getIcon($iType, $iClass = "", $iAttr = "", $iText = "&nbsp;", $iSpan = true)
	{
	
		$classArr	= array();
		$classStr	= "";
		
		if(isset(self::$iconDefs['ccicons']))
			$classArr[]	= self::$iconDefs['ccicons'];
		if(isset(self::$iconDefs['icons']))
			$classArr[]	= self::$iconDefs['icons'];
		else
			$classArr[]	= 'icons';
		$classArr[]	=   (isset(self::$iconDefs['icon']) ? self::$iconDefs['icon'] : 'icon-') .
						(isset(self::$iconDefs[$iType]) ? self::$iconDefs[$iType] : $iType);
		if(!empty($iClass))
			$classArr[]	= $iClass;
		
		$classArr	= array_unique(array_filter($classArr));
		
		$classStr	= implode(" ", $classArr);
		
		if(!$iSpan)
			return $classStr;
		
		$icoAttr	= "";
		
		if(!empty($iAttr)) {
			$icoAttr	= ' ' . $iAttr;
		}
		
		$output = '<span class="' . $classStr . '"' . $icoAttr . ' aria-hidden="true">' . $iText . '</span>';
		
		return $output;
	
	}
	
	
	/**
	 * Generiert Arrays mit Theme-Dateien
	 *
	 * @access	public
     * @param	string	$themeConf Theme-Setup-Definitionen
     * @return  string
	 */
	public function makeThemeFilesArr($themeConf)
	{
	
		$this->includeScriptLibs($themeConf);
		
		// Falls leeres Array
		if(!is_array($themeConf) || count($themeConf) == 0)
			return false;
		
		$cssFiles		= array();
		$scriptFiles	= array();
		$uiversion		= "";
		
		if(!$this->adminPage && self::$feMode)
			$uiversion	= isset($this->adminThemeConf["jsframework"]["uiversion"]) ? $this->adminThemeConf["jsframework"]["uiversion"] : JQUERY_UI_VERSION_ADMIN;
		elseif($this->adminPage)
			$uiversion	= isset($this->themeConf["jsframework"]["uiversion"]) ? $this->themeConf["jsframework"]["uiversion"] : JQUERY_UI_VERSION_ADMIN;
		else
			$uiversion	= isset($this->themeConf["jsframework"]["uiversion"]) ? $this->themeConf["jsframework"]["uiversion"] : JQUERY_UI_VERSION;
		
		// CSS-Files
		if(array_key_exists("cssfiles", $themeConf)) {
			
			foreach($themeConf['cssfiles'] as $key => $cssFile) {
				$cssFile	= str_replace("{root}", PROJECT_HTTP_ROOT, $cssFile);
				$cssFile	= str_replace("{theme}", STYLES_DIR, $cssFile);
				$cssFile	= str_replace("{admintheme}", 'system/themes/' . ADMIN_THEME . '/css/', $cssFile);
				$cssFile	= str_replace("{uiversion}", $uiversion, $cssFile);
				$cssFiles[$key] = $cssFile;
			}
		}
		
		// JS-Files
		if(array_key_exists("jsfiles", $themeConf)) {
			
			foreach($themeConf['jsfiles'] as $key => $scriptFile) {
				$scriptFile		= str_replace("{root}", PROJECT_HTTP_ROOT, $scriptFile);
				$scriptFile		= str_replace("{theme}", JS_DIR, $scriptFile);
				$scriptFile		= str_replace("{admintheme}", 'system/themes/' . ADMIN_THEME . '/js/', $scriptFile);
				$scriptFile		= str_replace("{uiversion}", $uiversion, $scriptFile);
				$scriptFiles[$key] 	= $scriptFile;
			}
		}
		
		$this->cssFiles		= array_merge($cssFiles, $this->cssFiles);
		$this->scriptFiles	= array_merge($scriptFiles, $this->scriptFiles);
		
		return true;
	}
	
	
	/**
	 * Bindet ggf. Skript-Bibliotheken ein
	 *
	 * @access	public
     * @param	string	$themeConf Theme-Setup-Definitionen
     * @return  string
	 */
	public function includeScriptLibs($themeConf)
	{
	
		// Ggf Modernizr einbinden
		if (MODERNIZR
		|| (isset($themeConf['defaults']['modernizr']) && $themeConf['defaults']['modernizr'] === true)
		) {
			$this->scriptFiles["modernizr"]	= "extLibs/modernizr/modernizr.min.js";
			return true;
		}
		else
			return false;
	}


	/**
	 * Übergibt Script-Code für den HTML-Headbereich
	 * 
	 * @access	public
     * @return  boolean
	 */
	public function getScriptVars()
	{
	
		// Javascript-Variablen hinzufügen
		$langVars		= isset(self::$staText['javascript']) ? self::$staText['javascript'] : array();
		$scriptVars		= isset($this->scriptCode["jsvars"]) ? $this->scriptCode["jsvars"] : "";

		$this->scriptCode["jsvars"] = $this->setScriptVars(true, true, $langVars) . $scriptVars;
	
	}

    
    /**
     * (Statische Text-/Sprach-) Variablen an JavaScript übermitteln
	 *
     * @param     $globalVars	falls false, werden die Variable mit dem conciseCMS-Object und Http-Roots nicht mitgegeben (default = true)
     * @param     $feVars		falls false, werden weitere Variablen nicht mitgegeben (default = true)
     * @param     $jsStaText	Statische Sprachbausteine (default = array())
	 * @access    public
     * @return    boolean
     */
    public function setScriptVars($globalVars = true, $feVars = true, $jsStaText = array())
    {
		
		$return =	"/* Concise JS vars */\r\n";
		
		if($globalVars) {
			
			// Globale Variablen
			// namespace "conciseCMS"
			$return .=	'var conciseCMS = conciseCMS || {regSess: false,'."\n";			
			// Variable mit HTTP_ROOT mitgeben
			$return .=	'httpRoot: "'.PROJECT_HTTP_ROOT.'",'."\n";
			// Theme Vars
			$return .=	'html5: "'.(string)HTML5.'",'."\n"; // HTML5
			$return .=	'activeTheme: "'.THEME.'",'."\n";
			$return .=	'adminTheme: "'.ADMIN_THEME.'",'."\n";
			$return .=	'imageDir: "'.IMAGE_DIR.'"};'."\n";
			$return .=	'conciseCMS.adminLang = "'.$this->adminLang.'";'."\n";
			$return .=	'conciseCMS.editLang = "'.$this->editLang.'";'."\n";
		}

		// Falls feVars und backendLog
		if($feVars && $this->backendLog) {

			// Weitere Globale Variablen
			$return .=	'conciseCMS.feMode = "'.self::$feMode.'";'."\n";
			$return .=	'conciseCMS.feLang = "'.$this->lang.'";'."\n";
			$return .=	'conciseCMS.feTheme = {};'."\n";
			
			$return .=	'conciseCMS.conciseChanges = false;'."\n";
			$return .=	'conciseCMS.tinyMCESettings = null;'."\n"; // Texteditor
			$return .=	'conciseCMS.openEditors = 0;'."\n";
			
			// Falls feMode
			if(self::$feMode) {
				
				// Fontdefs übergeben
				$return .= $this->getThemeJSVars($this->themeConf);
			}
		}
		
		// JS Variablen definieren
		if(!empty($jsStaText)) {
			
			$return .= $this->getScriptLangObj($jsStaText);
		}
		
		return $return;
		
    }

    
    /**
     * Statische Sprachbausteine var an JavaScript übermitteln
	 *
     * @param     $jsStaText	Statische Sprachbausteine (default = array())
	 * @access    public
     * @return    boolean
     */
    public function getScriptLangObj($jsStaText)
    {
		
		$return =	"";
		
		// JS Variablen definieren
		if(!empty($jsStaText) > 0) {
			
			$return .= 'conciseCMS.ln = {'."\n";
			
			foreach($jsStaText as $key => $jsText) {
				$return .= $key.':"'.$jsText.'",';
			}
			
			$return = substr($return, 0, -1);
			$return .= '};'."\n";
		}
		
		return $return;
	
	}
	
	
	/**
	 * Returns valid JS Key (e.g., w/w/o quotes)
	 *
     * @param   $themeConf	Theme config array
     * @param   $ccObjName	ccObjName (default = 'conciseCMS')
	 * @access	public
     * @return  string
	 */
	public function getThemeJSVars($themeConf, $ccObjName = "conciseCMS")
	{
	
		$return	= "";
		
		// Fontdefs übergeben
		if(!empty($themeConf["fonts"])) {
			$return .= $ccObjName . '.feTheme.fonts = {'."\n";
		
			foreach($themeConf["fonts"] as $key => $font) {
				$return .= $this->getValidJSKey($key) . ':"'.$font.'",';
			}
			
			$return = substr($return, 0, -1);
			$return .= '};'."\n";
		}
		
		// Styledefs (class) übergeben
		if(!empty($themeConf["class"])) {
			$return .= $ccObjName . '.feTheme.styles = {'."\n";
		
			foreach($themeConf["class"] as $key => $class) {
				$return .= $this->getValidJSKey($key) . ':"'.$class.'",';
			}
			
			$return = substr($return, 0, -1);
			$return .= '};'."\n";
		}
		
		// Styledefs (grid) übergeben
		if(!empty($themeConf["grid"])) {
			$return .= $ccObjName . '.feTheme.grid = {'."\n";
		
			foreach($themeConf["grid"] as $key => $grid) {
				$return .= $this->getValidJSKey($key) . ':"'.$grid.'",';
			}
			
			$return = substr($return, 0, -1);
			$return .= '};'."\n";
		}
		
		// Colors übergeben
		if(!empty($themeConf["colors"])) {
			$return .= $ccObjName . '.feTheme.colors = {'."\n";
			
			foreach($themeConf["colors"] as $key => $color) {
				$return .= $this->getValidJSKey($key) . ':"'.$color.'",';
			}
			
			$return = substr($return, 0, -1);
			$return .= '};'."\n";
		}
		
		// Icondefs übergeben
		if(!empty($themeConf["icons"])) {
			$return .= $ccObjName . '.feTheme.icons = {'."\n";
		
			foreach($themeConf["icons"] as $key => $icoClass) {
				$return .= $this->getValidJSKey($key) . ':"'.$icoClass.'",';
			}
			
			$return = substr($return, 0, -1);
			$return .= '};'."\n";
		}

		return $return;
	
	}
	
	
	/**
	 * Returns valid JS Key (e.g., w/w/o quotes)
	 *
	 * @access	public
     * @return  string
	 */
	public function getValidJSKey($key)
	{
	
		return (!ctype_alpha($key) ? '"' : '') . $key. (!ctype_alpha($key) ? '"' : '');
		
	}
	
	
	/**
	 * Erstellt ein Haupttemplateobjekt
	 *
	 * @access	public
     * @return  string
	 */
	public function getTplObject()
	{
		
		// Eine neue Instanz der Template Klasse erzeugen
		// Contents Include-Template
		$incTemplate				= array("CONTENTS" => $this->currentTemplate);
		$incTemplates				= array_merge($incTemplate, $this->incTemplates);
		
		// Template Objekt
		self::$o_mainTemplate		= new Template(CC_MAIN_TEMPLATE, $incTemplates, "");
		self::$o_mainTemplate->loadTemplate($this->adminPage);

	}

	

	/**
	 * Ersetzt Inhaltsplatzhalter mit Inhalten
	 *
	 * @access	public
     * @return  string
	 */
	public function assignReplace()
	{

		// Ggf. weitere individuelle Plazthalter-Definitionen
		if(file_exists(PROJECT_DOC_ROOT."/inc/placeholders.inc.php"))
			require_once(PROJECT_DOC_ROOT."/inc/placeholders.inc.php");


		// Ersetzen von variablen Standardplatzhaltern
		foreach(self::$o_mainTemplate->poolAssign as $key => $value) {
			self::$o_mainTemplate->assign($key, $value);
		}
	
	}




	/**
	 * Ersetzt Inhaltsplatzhalter mit Inhalten
	 *
	 * @access	public
     * @return  string
	 */
	public function assignReplaceCommon()
	{

		// Pfade
		self::$o_mainTemplate->poolAssign["root"]			= PROJECT_HTTP_ROOT;
		self::$o_mainTemplate->poolAssign["root_img"]		= IMAGE_DIR;
		self::$o_mainTemplate->poolAssign["admin_root"]		= ADMIN_HTTP_ROOT;
		self::$o_mainTemplate->poolAssign["system_root"]	= SYSTEM_HTTP_ROOT;
		self::$o_mainTemplate->poolAssign["admin_theme"]	= ADMIN_THEME;
		self::$o_mainTemplate->poolAssign["fe_theme"]		= THEME;

		// Aktuelle Sprache
		self::$o_mainTemplate->poolAssign["currlang"]		= $this->lang;


		// Allgemeine Links
		// zurück Link
		self::$o_mainTemplate->poolAssign["back"]			= '<p class="back"><a href="javascript:history.back();">{s_link:pageback}</a></p>' . "\r\n";
		// Drucken Link
		self::$o_mainTemplate->poolAssign["print"]			= '<p class="print"><a href="javascript:window.print();">{s_link:print}<span class="print sprite">&nbsp;</span></a></p>' . "\r\n";
		// Nach oben Link
		self::$o_mainTemplate->poolAssign["up"]				= '<p class="up"><a href="#top">{s_link:up}</a></p>' . "\r\n";
		// Aktuelles Jahr
		self::$o_mainTemplate->poolAssign["curryear"]		= date("Y", time());

	}



	/**
	 * mergeHeadCodeArrays
	 *
	 * @access	public
	 * @param	object	$o_element	Element-Objekt
     * @return  string
	 */
	public function mergeHeadCodeArrays($o_element)
	{
	
		// Script files
		if(!empty($o_element->scriptFiles))
			$this->scriptFiles	= array_unique(array_merge($this->scriptFiles, $o_element->scriptFiles));
		
		// Script code
		if(!empty($o_element->scriptCode)) {
		
			// JS vars
			if(!empty($o_element->scriptCode["jsvars"])) {
				if(!empty($this->scriptCode["jsvars"]))
					$this->scriptCode["jsvars"] .= $o_element->scriptCode["jsvars"];
				else
					$this->scriptCode["jsvars"] = $o_element->scriptCode["jsvars"];
				
				unset($o_element->scriptCode["jsvars"]); // prevent replacement of jsvars
			}
			
			$this->scriptCode	= array_merge($this->scriptCode, $o_element->scriptCode);
		}
		
		// CSS files
		if(!empty($o_element->cssFiles))
			$this->cssFiles	= array_unique(array_merge($this->cssFiles, $o_element->cssFiles));

	}



	/**
	 * Setzt Includes für den HTML-Head-Bereich
	 *
	 * @access	public
     * @return  string
	 */
	public function setHeadIncludes()
	{
	
		
		// Arrays mit Theme-Dateien erstellen
		$this->makeThemeFilesArr($this->themeConf);
		
		
		// Headbereich-Dateien zusammenführen
		// Script-Daten für Html-Headbereich aus den verschiedenen Contentbereichen zusammenfassen
		$this->cssFiles		= array_unique(array_filter($this->cssFiles));

		// Script-Daten für Html-Headbereich aus den verschiedenen Contentbereichen zusammenfassen
		$this->scriptFiles	= array_unique(array_filter($this->scriptFiles));

		// Script-Daten für Html-Headbereich aus den verschiedenen Contentbereichen zusammenfassen
		$this->scriptCode	= array_unique(array_filter($this->scriptCode));

	}

	

	/**
	 * Gibt die Body-Style-Klasse zurück
	 *
	 * @access	public
     * @return  string
	 */
	public function getBodyClass()
	{
	
		$bodyClass	 = $this->lang . " ";
		$bodyClass	.= $this->currentTemplate != "" ? str_replace(".", "-", $this->currentTemplate) . " " : "";
		$bodyClass	.= isset($GLOBALS['_GET']['fetchcache']) ? "{#browser}" : Log::$browser;
		$bodyClass	.= $this->adminPage ? " admin" : (!empty($this->themeConf["theme"]["skin"]) ? " cc-skin-" . $this->themeConf["theme"]["skin"] : "");
		$bodyClass	.= self::$feMode ? " feMode" : "";
		
		if($this->adminPage || self::$feMode) {
			$skin		 = !empty($this->g_Session['at_skin']) ? $this->g_Session['at_skin'] : (ADMIN_SKIN != "" ? ADMIN_SKIN : "default");
			$bodyClass	.= " cc-admin-skin-" . $skin;
		}
		
		if(!empty($this->bodyClassStrings)) {
			$bodyClass	.= " " . implode(" ", $this->bodyClassStrings);
		}
		
		$bodyClass	= htmlspecialchars($bodyClass);
	
		return $bodyClass;
	}	
	


    /**
     * Die statischen Text-/Sprachvariablen ersetzen
     *
	 * @param	string	$target Zielstring
     * @param	string	$leftDel linker Begrenzer
     * @param	string	$rightDel rechter Begrenzer
	 * @access	public
     * @return  string
     */
    public static function replaceStaText($target, $leftDel = "{s_", $rightDel = "}")
    {
		
		// Callback function für replaceStaText (pre_replace_callback)
		if(!function_exists('Concise\staTextCallback')) {
			function staTextCallback($e)
			{
			
				if(isset(ContentsEngine::$staText["$e[1]"]["$e[2]"]))
					return ContentsEngine::$staText["$e[1]"]["$e[2]"];
				if(ContentsEngine::$phMode)
					return "i18n: unknown";
				return "";
			
			}
		}
        
		if(!self::$phMode) { // Falls Platzhalterersetzung nicht ausgeschaltet ist
    	    #$target = Language::force_utf8(preg_replace_callback("/".$leftDel."([A-Za-z0-9_-]+)\:([A-Za-z0-9_-]+)".$rightDel."/isum", 'staTextCallback', $target));
    	    $target = preg_replace_callback("/".$leftDel."([A-Za-z0-9_-]+)\:([A-Za-z0-9_-]+)".$rightDel."/ism", 'Concise\staTextCallback', $target);
		}
		else {
			// Falls ph-Modus angeschaltet ist, Platzhalter nur für Systeminterne Sprachbausteine ersetzen
			$target = preg_replace_callback("/".$leftDel."(button)\:(turno[A-Za-z0-9_-]+)".$rightDel."/ism", 'Concise\staTextCallback', $target);
			$target = preg_replace_callback("/".$leftDel."(label)\:([a-z]{0,6}theme)".$rightDel."/ism", 'Concise\staTextCallback', $target);
			$target = preg_replace_callback("/".$leftDel."(title)\:([A-Za-z0-9_-]{2}toggle)".$rightDel."/ism", 'Concise\staTextCallback', $target);
			$target = preg_replace_callback("/".$leftDel."(notice)\:(preview[A-Za-z0-9_-]*|themeactive)".$rightDel."/ism", 'Concise\staTextCallback', $target);
		}
		
		return $target;
    }	
	


    /**
     * Die Platzhalter für Style-Definitionen ersetzen
     *
	 * @param	string	$target Zielstring
     * @param	string	$leftDel linker Begrenzer
     * @param	string	$rightDel rechter Begrenzer
	 * @access	public
     * @return  string
     */
    public static function replaceStyleDefs($target, $leftDel = "{t_", $rightDel = "}")
    {
		
		// Callback function für replaceStyleDefs (pre_replace_callback)
		if(!function_exists('Concise\themePhCallback')) {
			function themePhCallback($e)
			{
			
				if($e[1] === "class" && isset(ContentsEngine::$styleDefs["$e[2]"]))
					return ContentsEngine::$styleDefs["$e[2]"];
				if($e[1] === "icons" && isset(ContentsEngine::$iconDefs["$e[2]"]))
					return ContentsEngine::$iconDefs["$e[2]"];
				return "";
			
			}
		}
		
		// Callback function für themeIconCallback (preg_replace_callback)
		if(!function_exists('Concise\themeIconCallback')) {
			function themeIconCallback($e)
			{
			
				if(isset(ContentsEngine::$iconDefs["$e[1]"]))
					return ContentsEngine::getIcon("$e[1]", "", "", "");
				return "";
			
			}
		}
		
   	    $target = preg_replace_callback("/".$leftDel."([A-Za-z0-9_-]+)\:([A-Za-z0-9_-]+)".$rightDel."/ism", 'Concise\themePhCallback', $target);
		
		// Icon Platzhalter
		if(strpos($target, "{ico:"))
			$target = preg_replace_callback("/\{ico\:([A-Za-z0-9_-]+)".$rightDel."/ism", 'Concise\themeIconCallback', $target);
		
		return $target;
	
    }	

	

	/**
	 * Gibt den HTML-Seiten-Content aus
	 *
	 * @access	public
     * @return  string
	 */
	public function printHtmlContent()
	{

		// Seitendetails zuweisen
		$this->assignPageDetails($this->o_page);
		
		
		$bodyClass		= $this->getBodyClass();
		$bodyID			= "page-" . $this->pageId;
		
		
		// HTML-Objekt erstellen
		self::$o_html	= new HTML($this);
		
		// HTML
		self::$o_html->printHead($this->lang, $this->pageTitle);
		self::$o_html->printBody($bodyID, $bodyClass, $this->preview, $this->feModeStatus);
		
		$bodyContent	= self::$o_mainTemplate->getTemplate(true);
		
		// Template ausgeben
		echo $bodyContent;

		// HTML foot ausgeben:
		self::$o_html->printFoot($this->scriptFilesBody);
		
		return true;
	
	}



	/**
	 * Validiert den Formulartoken und lädt bei fehlerhaftem Token die Seite neu
	 *
	 * @access	public
     * @return  string
	 */
	public function validateToken()
	{
	
		// Token auswerten
		if(isset($GLOBALS['_POST']['token']) && self::$tokenOK === false) {
			header("Location: ?");
			exit;
		}
	
	}

	

	/**
	 * Session Token setzen
	 * 
	 * @access public
	 * @return string
	 */
	public function setToken()
	{

		// Token zur Session hinzufügen
		$this->o_security->setToken(self::$token);
		self::$sessionTokenSet	= true;

	}
	

	
	/**
	 * Methode zur Erstellung eines Querystrings eigener Benutzergruppen
	 * 
	 * @param	array	$ownGroups Array mit eigenen Benutzergruppen
	 * @param	string	$tabPrefix DB-Tabellen-Prefix (default = '')
	 * @access	private
	 * @return	string
	 */
	protected function getOwnGroupsQueryStr($ownGroups, $tabPrefix = "")
	{
		
		$return = "";
		
		// Eigene Benutzergruppe
		if(count($ownGroups) == 0)
			return $return;
		
		foreach($ownGroups as $ownGroup) {
			$return .= " OR FIND_IN_SET('" . $this->DB->escapeString($ownGroup) . "', ".$tabPrefix."`group`)";
		}

		return $return;
	}
	
	
	
	/**
	 * Methode zur Erstellung des Benutzerkontenmenüs
	 * 
	 * @param	boolean	$logForm Falls true, wird Benutzerformular hinzugefügt
	 * @access	public
	 * @return	string
	 */
	public function getAccountMenu($settings = false, $logForm = true)
	{
		
		$log			= $this->o_security->get('loginStatus');
		$accountMenu	= "";
		$settingsMenu	= "";
		$welcome		= "";
		$logFormSmall	= "";
		
		$accountMenu	= '<div id="account"' . (isset($this->g_Session['group']) && $log ? ' class="'.$this->g_Session['group'].'"' : '') . '>' . "\r\n" .
						  #'<div id="accountMenu" class="{t_class:btngroup} {t_class:btngroupxs}' . ($log ? ' loggedIn' : '') . '" role="group">' . "\r\n";
						  '<div id="accountMenu"' . ($log ? ' class="loggedIn"' : '') . '>' . "\r\n" .
						  '<div class="{t_class:btngroup} {t_class:btngroupxs}" role="group">' . "\r\n";
		
		
		// Falls das Login-Formular angefügt werden soll
		if($logForm) {
			
			// Neues Login-Objekt
			$o_LoginSmall = new Login($this->DB, $this->o_lng, $this->o_dispatcher);
			$this->addEventListeners("user"); // User event listeners
			
			$logFormSmall = '<div class="logFormSmall {t_class:quaterrow}">' . "\r\n" .
							$o_LoginSmall->printLoginForm("small") . // Loginformular ausgeben
							'</div>' . "\r\n";
		}
		
		if($log) {
			
			// Falls Admin
			if($this->adminLog) {
				$accountType	= "{s_link:account}";
				$logoutTit		= "{s_title:logoutadmin}";
			}
			else {
				$accountType	= "{s_link:account}";
				$logoutTit		= "{s_title:logout}";
			}
			
			// Falls Admin, Editor oder Author
			if($this->backendLog) {
				
				if($this->adminPage)
					$this->loadViaAjax	= ' data-ajax="true"';
				
				
				// Adminlink einbinden
				// Button link admin
				$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT,
										"text"		=> $accountType,
										"class"		=> "account admin gotoAdminPage {t_class:btndef}",
										"title"		=> "{s_title:admin}",
										"attr"		=> $this->loadViaAjax,
										"icon"		=> "user"
									);
				
				$accountMenu .=	self::getButtonLink($btnDefs);
				
				
				// Falls Adminbereich und Admin, Settingslink einbinden
				if($settings) {
				
					// Button link settings
					$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=settings',
											"text"		=> '{s_text:settings}',
											"class"		=> "account gotoSettingsPage {t_class:btndef}",
											"title"		=> "{s_title:adminset}",
											"attr"		=> $this->loadViaAjax,
											"icon"		=> "settings"
										);
					
					$settingsMenu .=	self::getButtonLink($btnDefs);
				}
				
				
				// Falls eine Nicht-Adminseite angezeigt wird, Editlink einbinden
				if($this->editorLog && !$this->adminPage)
					$accountMenu .=	$this->getFeEditPageMenu();

			}
			
			// Falls Guest, Shopuser oder eigene Benutzergruppe
			if(isset($this->g_Session['group']) && ($this->g_Session['group'] == "guest" || in_array($this->g_Session['group'], $GLOBALS['ownUserGroups'])) && (REGISTRATION_TYPE == "account" || REGISTRATION_TYPE == "shopuser")) {
			
				// Button link user
				$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(-1007),
										"text"		=> $accountType,
										"class"		=> "account admin gotoUserPage {t_class:btndef}",
										"title"		=> "{s_title:admin}",
										"icon"		=> "user"
									);
				
				$welcome .=	self::getButtonLink($btnDefs);
			
			}
			
			$accountMenu .=		$welcome;
			
			// Button link logout
			$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '?logout=true',
									"text"		=> '{s_link:logout}',
									"class"		=> "account logout gotoLogoutPage {t_class:btndef}",
									"title"		=> $logoutTit,
									"icon"		=> "logout"
								);
			
			$accountMenu .=	self::getButtonLink($btnDefs);			
			
			$accountMenu .=	'</div>' . "\r\n";

			$accountMenu .=	'</div>' . "\r\n";
			
			if($settingsMenu != "")
				$accountMenu .=	'<div id="settingsMenu" class="account settings">' .
								$settingsMenu .
								'</div>' . "\r\n";

			// logFormSmall (admin)
			$accountMenu .=	$logFormSmall;
		
		}
		else {

			// Falls Guest, Shopuser oder eigene Benutzergruppe
			if(REGISTRATION_TYPE == "account" || REGISTRATION_TYPE == "shopuser") {
			
				// Button register
				$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(-1006),
										"text"		=> '{s_link:register}',
										"class"		=> "admin account {t_class:btndef}",
										"title"		=> '{s_header:register}',
										"icon"		=> "user"
									);
				
				$accountMenu .=	self::getButtonLink($btnDefs);
			}
			
		
			// Button login
			$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . HTML::getLinkPath(-1002),
									"text"		=> 'Login',
									"class"		=> "loginButton login {t_class:btndef}",
									"title"		=> '{s_title:login}',
									"attr"		=> 'rel="nofollow" role="button"',
									"icon"		=> "lock"
								);
			
			$accountMenu .=	self::getButtonLink($btnDefs);
			
			$accountMenu .=	'</div>' . "\r\n";
			
			$accountMenu .=	'</div>' . "\r\n";

			// logFormSmall
			$accountMenu .=	$logFormSmall;
		}
		
		$accountMenu	.= '</div>' . "\r\n";
		

		return $accountMenu;

	}
	
	

	/**
	 * Auswahlmenue/-liste für das Anlegen neuer Inhaltselemente
	 * 
	 * @param	$listOutput boolean Ausgabe der Inhaltstypen-Liste. Falls false, Button zum Öffnen der Liste ausgeben (default = false)
	 * @param	$select boolean Ausgabe mit Select (default = false)
	 * @access	public
	 * @return	string
	 */
	public function listContentTypes($listOutput = false, $select = false)
	{
	
		// Falls der Button zum Öffnen der Liste ausgegeben werden soll
		if(!$listOutput) {
					
			$mediaListButtonDef		= array(	"class" 	=> "insertElement",
												"type"		=> "insertElement",
												"url"		=> SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=listelements',
												"value"		=> "{s_option:choosetype}",
												"icon"		=> "media",
												"btnclass"	=> "add-newcon-btn feEditButton",
												"fe"		=> ($this->adminPage ? false : true)
											);
			
			return Admin::getMediaListButton($mediaListButtonDef);
		}
		
		
		// Andernfalls (Auswahl-)Liste ausgeben
		if($select) { // Falls select
			$output		=	'<select class="new_con" name="new_con">' . "\r\n" .
							'<option selected="selected" value="" disabled="disabled">{s_option:choosetype}</option>' . "\r\n";
						
			// Inhaltsgruppe
			$groupTag	=	'<optgroup label="{s_optgroup:#key#}">' . "\r\n";
			// Element
			$listTag	=	'<option value="#type#">{s_option:#type#}</option>' . "\r\n";
			// Icon-Pfad
			$iconPath	=	"";
			// Schließen-Tag
			$closeTag	=	'optgroup';
		}
		
		// Falls ListBox
		else {
			
			// Button close
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'closeListBox close button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:close}',
									"icon"		=> "close"
								);
			
			$output		=	self::getButton($btnDefs);
		
			$output		.=	'<h2 class="cc-section-heading cc-h2">{s_option:choosetype}</h2>' . "\r\n" .
							'<div class="listItemBox {t_class:row}">' . "\r\n";
			

			// Inhaltsgruppe
			$groupTag	=	'<li class="contentListItem">' . "\r\n" .
							'<h4 class="cc-h2">' . PHP_EOL .
							'<a href="#tabcon-#key#" class="scroll-no">' . PHP_EOL .
							'{s_optgroup:#key#}' .
							'</a>' .
							'</h4>' . PHP_EOL .
							'</li>' . PHP_EOL;
			
			// Element
			$listTag	=	'<li>' . "\n";
			
			// Button insert element
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'contentType button button-icon-only',
									"value"		=> "#type#",
									"text"		=> "",
									"title"		=> "#type#",
									"icon"		=> "#type#",
									"iconclass"	=> "{t_icons:icon}{t_icons:#icon#} contype-#key# conicon-#type# conicon-#icon#",
									"iconattr"	=> 'style="background:url(\'#iconpath#/conicon_#type#.png\') no-repeat center center;"'
								);
			
			$listTag .=	self::getButton($btnDefs);
			
			$listTag .=		'<span class="contentListItemName">{s_option:#type#}</span>' .
							'</li>';
			
			// Icon-Pfad
			$iconPath	=	SYSTEM_IMAGE_DIR;
			
			// Schließen-Tag
			$closeTag	=	'ul';
		}
		
		
		// Plugins auslesen
		// Installierte Plugins
		$this->installedPlugins	= $this->getInstalledPlugins(true);
		// Aktive Plugins
		$this->activePlugins	= $this->getActivePlugins();

		
		// Ggf. Plugins zu Inhaltstypen hinzufügen
		foreach($this->activePlugins as $plugin) {
			if(!in_array($plugin, $this->contentTypes["plugins"])
			&& !empty($this->installedPlugins[$plugin]["features"]["contentelement"])
			)
				$this->contentTypes["plugins"][] = $plugin;
		}

		
		
		$output		.=	'<ul class="tabHeader contentTypeGroup {t_class:col} {t_class:panelhead}">' . "\r\n";
		$listOutput	= "";
		
		// Inhaltsgruppen auslesen
		
		foreach($this->contentTypes as $key => $conType) {
			
			if(count($conType) > 0) {
			
				$output .=	str_replace("#key#", $key, $groupTag);
				$catList	= "";
				
				// Inhaltstypen auslesen
				foreach($conType as $type) {
					
					if($key == "plugins") {
						$iconPath	= PROJECT_HTTP_ROOT . '/plugins/' . $type . '/img';
					}
					if(array_key_exists($type, $this->installedPlugins) && !empty($this->installedPlugins[$type]["features"]["conicon"]))
						$iconKey	= $this->installedPlugins[$type]["features"]["conicon"];
					else
						$iconKey	= $type;
					
					$catList .=	str_replace("#key#", $key, str_replace("#type#", $type, str_replace("#iconpath#", $iconPath, str_replace("#icon#", $iconKey, $listTag))));
				}
				
				if($select)
					$output .=	'</' . $closeTag . '>' . "\r\n";
				else
					$listOutput	.=	'<ul id="tabcon-' . $key . '" class="{t_class:panelbody}">' .
									$catList .
									'<br class="clearfloat" />' .
									'</ul>';
			}
		}
		if($select)
			$output .=	'</select>' . "\r\n";
		else {
			$output .=	'</' . $closeTag . '>' . "\r\n";
			$output .=	$listOutput;
			$output .=	'</div>' . "\r\n";
			$output .=	'<script>' . PHP_EOL .
						'head.ready(function(){' . PHP_EOL .
						'$("document").ready(function(){' . PHP_EOL .
						'$(".listItemBox").tabs({
							beforeLoad: function(event, ui) { ui.panel.html(\'<p class="framedParagraph"><span class="inline-image"><img src="\' + ccWaitImg.src + \'" alt="loading" /><span> loading...</p>\'); }
						});' . PHP_EOL .
						'});' . PHP_EOL .
						'});' . PHP_EOL .
						'</script>' . PHP_EOL;
		}		
		
		return $output;

	}
	
	
	
	/**
	 * Methode zur Bestimmung der Anzahl an Inhaltselementent
	 * 
	 * @param	string	$table Tabellenname
	 * @param	string	$id Seiten-ID der Inhaltsseite (page_id) (default = '')
	 * @param	int		$colCorrection colCorrection columns to subtract (default = 1)
	 * @access	public
	 * @return	string
	 */
	public function getConNumber($table, $id = "", $colCorrection = 1)
	{
		
		$table		= $this->DB->escapeString($table);
		$columns	= 0;
		
		if($id == "") {
			
			// db-Suche zur Bestimmung der Spaltenzahl mit Inhaltselementen (pro Sprache)
			if($queryCols = $this->DB->query("SHOW COLUMNS 
												   FROM `" . $table . "`")) {
		
	
				$totColumns		= count($queryCols);
				$conColumns		= $totColumns - $colCorrection; // Spalte page_id abziehen
				$installedLangs	= count($this->o_lng->installedLangs);
				$columns 		= $conColumns/($installedLangs +2); // Anzahl an Inhaltselementen (Spalten/Sprache) = alle Spalten minus page_id durch Anzahl an Sprachen plus 2 (type-/styles-con)
			}
		}
		else {
			
			// db-Suche zur Bestimmung der Spaltenzahl mit Inhaltselementen (pro Sprache)
			if($queryCols = $this->DB->query("SELECT * 
												   FROM `" . $table . "_preview` 
												   WHERE `page_id` = '$id' 
												   ")) {
				
				$maxConNr = self::getConNumber($table);
				
				if($maxConNr < 1)
					return 0;
				
				if($queryCols[0]["type-con".$maxConNr] != "")
					$columns = $maxConNr;
				else {	
					for($i = 1; $i <= $maxConNr; $i++) {
					
						if($queryCols[0]["type-con".$i] == "") {
							$columns = $i-1;
							break;
						}
					}
				}
	
			}
		}

		return $columns;
		
	}
	


	/**
	 * Gibt Meldungen aus der Session zurück
	 * 
	 * @param string	$type	Typ der Meldung (default = "all")
	 * @param boolean	$tag	Meldung als Html-Tag zurückgeben (default = false)
	 * @param boolean	$unset	Session-Meldung löschen (default = true)
	 * @access public
	 * @return string
	 */
	public function getSessionNotifications($type = "all", $tag = false, $unset = true)
	{
		
		$notice		= "";
		
		// Meldungen aus Session auslesen
		// Fehler
		if(($type = "all" || $type = "error") 
		&& isset($this->g_Session['error'])) {
			$notice .= $tag ? $this->getNotificationStr($this->g_Session['error'], "error") : $this->g_Session['error']; 
			if($unset) $this->unsetSessionKey('error');
		}
		// Benachrichtigung
		if(($type = "all" || $type = "notice") 
		&& isset($this->g_Session['notice'])) {
			$notice .= $tag ? $this->getNotificationStr($this->g_Session['notice'], "success") : $this->g_Session['notice']; 
			if($unset) $this->unsetSessionKey('notice');
		}
		// Hinweis
		if(($type = "all" || $type = "hint") 
		&& isset($this->g_Session['hint'])) {
			$notice .= $tag ? $this->getNotificationStr($this->g_Session['hint'], "success") : $this->g_Session['hint']; 
			if($unset) $this->unsetSessionKey('hint');
		}
		
		return $notice;
		
	}
	


	/**
	 * Gibt einen HTML-Tag mit einer Meldungen zurück
	 * 
	 * @param string	$str		Meldung
	 * @param string	$type		Typ der Meldung (default = "success")
	 * @param string	$addClass	Style class ext (default = "")
	 * @access public
	 * @return string
	 */
	public function getNotificationStr($str, $type = "success", $addClass = "")
	{
		
		return '<p class="notice ' . $type . ($addClass != "" ? ' ' . $addClass : '') . ' {t_class:alert} {t_class:' . $type . '}" role="alert">' . $str . '</p>' . "\r\n";
		
	}
	
	
	
	/**
	 * Setzt eine Session-Variable
	 * 
	 * @param string var key
	 * @param string var value
	 * @access protected
	 */
	protected function setSessionVar($key, $val)
	{
	
		$this->g_Session[$key]	= $val;
		$this->o_security->setSessionVar($key, $val);
	
	}


	
	/**
	 * Löscht eine Session-Variable
	 * 
	 * @param	string $key Session-Variable
	 * @access	public
     * @return  boolean
	 */
	public function unsetSessionKey($key)
	{
	
		if(isset($this->g_Session[$key]))
			unset($this->g_Session[$key]);
		
		return $this->o_security->unsetSessionKey($key);
	
	}
	


	/**
	 * EventDispatcherInterface weiterreichen
	 *
	 * @parem	string	$o_dispatcher EventDispatcherInterface
	 * @access	protected
     * @return  array
	 */
	public function setEventDispatcher(EventDispatcherInterface $o_dispatcher)
    {
	
        $this->o_dispatcher = $o_dispatcher;
	
    }
	


	/**
	 * EventListenerArray weiterreichen
	 *
	 * @parem	string	$eventListeners EventListenerArray
	 * @access	protected
     * @return  array
	 */
	public function setEventListeners($eventListeners)
    {
	
        $this->eventListeners = $eventListeners;
	
    }
	


	/**
	 * Liest installierte Plugins aus der DB in ein Array
	 *
	 * @param	$setPlugins boolean Plugin-Sprachbausteine laden (default = false)
	 * @access	public
     * @return  array
	 */
	public function getInstalledPlugins($setPlugins = false)
	{
	
		$instPlugins	= array();
		
		// Suche nach aktiven Plugins
		$queryPlugins = $this->DB->query(  "SELECT *
												FROM `" . DB_TABLE_PREFIX . "plugins` 
											WHERE 1"
										);

		if(count($queryPlugins) > 0) {
			
			foreach($queryPlugins as $plugin) {
			
				$plName	= $plugin['pl_name'];
				
				// Falls Plugin-Ordner vorhanden
				if(is_dir(PLUGIN_DIR . $plName)) {

					// Read config
					if(file_exists(PLUGIN_DIR . $plName . '/config.ini'))
						if($plConf = parse_ini_file(PLUGIN_DIR . $plName . '/config.ini', true))
							$plugin	= array_merge($plugin, $plConf);
					
					$instPlugins[$plName]	= $plugin;
						
					
					// Ggf. Sprachbausteine des Plug-ins laden
					if($setPlugins
					|| !empty($plConf['features']['setlang'])
					)
						$this->setPlugin($plName, $this->editLang);
				
				}
			}
		}
		return $instPlugins;
	
	}	
	


	/**
	 * Liest aktive Plugins in ein Array
	 *
	 * @param	$fromDB boolean aktive Plugins aus DB auslesen (default = false)
	 * @access	public
     * @return  array
	 */
	public function getActivePlugins($fromDB = false)
	{

		$activePlugins	= array();
		
		// Falls aus Datenbank
		if($fromDB) {
		
			// Suche nach aktiven Plugins
			$queryActivePlugins = $this->DB->query	("SELECT `pl_name`
														FROM `" . DB_TABLE_PREFIX . "plugins` 
													  WHERE `active` = 1"
													);
			
			if(is_array($queryActivePlugins)
			&& count($queryActivePlugins) > 0
			) {
			
				foreach($queryActivePlugins as $pl) {
					$activePlugins[]	= $pl['pl_name'];
				}
			}
			return $activePlugins;
		}
		
		// Aus dem Array installierter Plugins bestimmen
		foreach($this->installedPlugins as $plugin => $pluginDetails) {
			if($pluginDetails['active'])
				$activePlugins[]	= $plugin;
		}
		return $activePlugins;
	
	}	
	


	/**
	 * Liest Event subscriptions aktiver Plugins aus (event listening)
	 *
	 * @access	public
     * @return  array
	 */
	public function registerPlugIns()
	{

		if(!is_array($this->activePlugins)
		|| count($this->activePlugins) == 0
		)
			return false;
		
		
		// Callback function für replaceStaText (pre_replace_callback)
		if(!function_exists('Concise\eventNameCallback')) {
			function eventNameCallback($e)
			{
			
				return strtoupper("$e[1]");
			
			}
		}
		
		// Eventlistener aktiver Plugins speichern
		foreach($this->activePlugins as $plugin) {
		
			$listenerName	= ucfirst(preg_replace_callback("/-([a-z])/", 'Concise\eventNameCallback', $plugin));
			$className		= $listenerName . "Events";
			$eventClassFile	= PLUGIN_DIR . $plugin . '/events/events.' . $listenerName . '.php';
			
			if(!is_file($eventClassFile))
				continue;

			
			$classIdentifier	= '\Concise\Events\\' . $className;
			
			require_once $eventClassFile;
			
			// Plugin-Eventlistener merken
			$this->eventListeners[]	= array("name" 		=> $plugin,
											"events"	=> $classIdentifier::$events,
											"listener"	=> $listenerName,
											"path"		=> CC_PLUGIN_FOLDER . '/' . $plugin . '/events'
											);

		}		
		
		return true;
	
	}	
	


	/**
	 * Liest Core Events aus (event listening)
	 *
	 * @access	public
     * @return  array
	 */
	public function registerCoreEvents($scope)
	{
	
		if(empty($scope))
			return false;
		
		// CoreEventlistener registrieren
		$listenerName	= 'CoreEvents';
		$scopeNamespace	= $scope != "global" ? ucfirst($scope) . '\\' : ''; // Namespace mit Scope, falls nicht global
		$scopeClassName	= ucfirst($scope) . $listenerName;
		$eventClassFile	= PROJECT_DOC_ROOT . '/inc/classes/Events/events.' . $scope . '.CoreEvents.php';
		
		if(!is_file($eventClassFile))
			return false;
		
		$classIdentifier	= '\Concise\Events\\' . $scopeNamespace . $scopeClassName;
		
		require_once $eventClassFile;
		
		// Core-Eventlistener merken
		$this->eventListeners[]	= array("name" 		=> $scope,
										"events"	=> $classIdentifier::$events,
										"listener"	=> $listenerName,
										"path"		=> $classIdentifier::$path
										);
		
		return true;
	
	}	
	


	/**
	 * Event subscriptions aktiver Plugins für einen Geltungsbereich anmelden (event listening)
	 *
	 * @parem	string	$scope Geltungsbereich
	 * @access	protected
     * @return  array
	 */
	protected function addEventListeners($scope)
	{

		if(empty($scope))
			return false;

		$added	= false;

		// register Core Events
		$this->registerCoreEvents($scope);
		
		// Falls keine Event Listener vorhanden
		if(count($this->eventListeners) == 0)
			return false;

		
		// Eventlistener aktiver Plugins registrieren
		foreach($this->eventListeners as $listener) {
			
			$events				= $listener['events'];
			
			if(empty($events[$scope])
			|| !is_array($events[$scope])
			|| count($events[$scope]) == 0
			)
				continue;
			

			$pathStrPre			= PROJECT_DOC_ROOT . '/' . $listener['path'] . '/listener.' . $scope . '.';
			$pathStrPost		= '.' . $listener['listener'] . '.php';
			
			$listenerFileStr	= $pathStrPre . '*' . $pathStrPost;
			
			// Listener files
			$listenerFiles		= glob($listenerFileStr);
			
		
			if(empty($listenerFiles))
				continue;
			
			foreach($listenerFiles as  $eventListenerFile) {

				$eventNamePrefix	= str_replace(array($pathStrPre, $pathStrPost), "", $eventListenerFile); // Eventname Prefix
				$scopeNamespace		= $scope != "global" ? ucfirst($scope) . '\\' : ''; // Namespace mit Scope, falls nicht global
				$className			= ucfirst($eventNamePrefix) . $listener['listener'] . "Listener";
				$classIdentifier	= '\Concise\Events\\' . $scopeNamespace . $className;
				
				require_once $eventListenerFile;
				
				// Plugin-EventListener-Objekt
				$o_eventClass	= new $classIdentifier();
				
				
				// Event scope nach listeners zum jeweiligen Event durchsuchen
				foreach($events[$scope] as $eventName => $eventParams) {
				
					if(strpos($eventName, $eventNamePrefix . '.') !== 0)
						continue;

					// add event listener for respective event
					$this->addEventListener($o_eventClass, $eventName, $eventParams);
					
					$added	= true;
				}
			}
		}
	
		return $added;
	
	}	
	


	/**
	 * EventDispatcherInterface weiterreichen
	 *
	 * @parem	string	$o_dispatcher EventDispatcherInterface
	 * @access	protected
     * @return  array
	 */
	public function addEventListener($o_eventClass, $eventName, $eventParams)
    {

		$callback	= $eventParams[0];
		
		if(is_array($callback)) {
			foreach($eventParams as $ev => $params) {
				$this->addEventListener($o_eventClass, $eventName, $params);
			}
			return false;
		}
		
		$priority	= $eventParams[1];
		
		// add event listener for respective event
		$this->o_dispatcher->addListener($eventName, array($o_eventClass, $callback), $priority);
		
		return true;

    }
	


	/**
	 * Plugin-Verzeichnis einlesen
	 * 
	 * @param	$setPlugins boolean Plugin-Sprachbausteine laden (default = false)
	 * @access	public
	 * @return	array/boolean
	 */
	public function readPlugInsDir($setPlugins = false)
	{
	
		if(!is_dir(PLUGIN_DIR))
			return false;
		
		$plugins	= array();
		
		$handle = opendir(PLUGIN_DIR);
		
		while($content = readdir($handle)) {
			if(strpos($content, ".") !== 0
			&& is_dir(PLUGIN_DIR . $content)
			) {
				$plugins[] = $content;
				if($setPlugins)
					$this->setPlugin($content, $this->editLang); // Sprachbausteine des Plug-ins laden
			}
		}
		closedir($handle);
		
		return $plugins;
	
	}
	


	/**
	 * Integriert relevante Dateien von Plug-ins
	 * 
     * @param	string $plugin	Plug-in Name
     * @param	string $lang	Plug-in Sprache
	 * @access	public
	 */
	public function setPlugin($plugin, $lang)
	{

		$path = PLUGIN_DIR . $plugin . '/lang/' . $plugin . '_';
		$file = $path . $lang . '.ini';
		
		if(file_exists($file))
			$extLang = parse_ini_file($file, true);
		elseif(!empty($this) && is_object($this->o_lng) && file_exists($path . $this->o_lng->defLang . '.ini'))
			$extLang = parse_ini_file($path . $this->o_lng->defLang . '.ini', true);
		elseif(file_exists($path . DEF_LANG . '.ini'))
			$extLang = parse_ini_file($path . DEF_LANG . '.ini', true);
		elseif(file_exists($path . FALLBACK_LANG . '.ini'))
			$extLang = parse_ini_file($path . FALLBACK_LANG . '.ini', true);
		else
			return false;
		
		self::$staText = array_merge_recursive_simple(self::$staText, $extLang);
		
		return $extLang;
	
	}
	


	/**
	 * Debug-Console
	 *
	 * @access	public
     * @return  string
	 */
	private function getDebugConsole()
	{
		// Ggf.DebugConsole einbinden
		if(DEBUG === true && $this->adminLog) { // Falls in settings.php eingestellt
	
			require_once PROJECT_DOC_ROOT."/inc/classes/Debugging/class.Logging.php";
			require_once PROJECT_DOC_ROOT."/inc/classes/Debugging/class.DebugConsole.php";

			// Scripte einbinden
			$this->cssFiles[]			= "system/themes/" . ADMIN_THEME . "/css/tabs.min.css";
			$this->scriptFiles["tabs"]	= "extLibs/jquery/ui/jquery-ui-" . JQUERY_UI_VERSION_ADMIN . ".custom-tabs.min.js";
			$this->scriptFiles["debug"]	= "system/access/js/debug.js";
			self::$o_mainTemplate->poolAssign["debug"] = DebugConsole::displayConsole();
		}
	
	}			
	


	/**
	 * ContextMenu-Script
	 *
	 * @access	public
     * @return  string
	 */
	protected function getContextMenuScript()
	{
	
		$output =	'<script>
						head.ready("ccInitScript", function(){
						head.load(
						cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.contextMenu.css",
						{contextmenu: cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.contextMenu.min.js"},
						{contextmenuui: cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.ui.position.js"},
						{contextmenujs: cc.httpRoot + "/system/access/js/contextMenu.min.js"}
						);
						});
					</script>' . PHP_EOL;

		return $output;
	
	}			
	


	/**
	 * Liest den Querystring aus
	 *
	 * @access	public
     * @return  string
	 */
	public function getQueryStr()
	{
		
		// Javascript-File zuweisen
		$qs = $GLOBALS['_SERVER']['QUERY_STRING'];
		$qs = preg_replace("/page=[.]*[^&]*&?([.&?]*)/", "\\2", $qs);
		$qs = preg_replace("/lang=[.]*[^&]*&?([.&?]*)/", "\\2", $qs);
		$qs = preg_replace("/fetchcache=1&ts=[.]*[^&]*&?([.&?]*)/", "\\2", $qs);
		
		return $qs;
	}	
	
	
	
	/**
	 * Methode zur Trimmen von Array-Elementen
	 * 
	 * @param	array	Array
	 * @access	public
	 * @return	array
	 */
	public function trimArrayElements($array)
	{
		
		$trimmed = array();
		
		foreach($array as $elem) {			
			$trimmed[] = trim($elem);
		}
		return $trimmed;
				
	}
	
	
	
	/**
	 * Methode Ändern von Array-Keys bei Beibehaltung der Reihenfolge der Array-Elemente
	 * 
	 * @param	array	Array
	 * @access	public
	 * @return	array
	 */
	public function changeArrayKey( $array, $old_key, $new_key) {

		if( ! array_key_exists( $old_key, $array ) )
			return $array;

		$keys = array_keys( $array );
		$keys[ array_search( $old_key, $keys ) ] = $new_key;

		return array_combine( $keys, $array );
	
	}
	
	
	
	/**
	 * Methode Hinzufügen von Key/Value an den Anfang assoziativer Arrays
	 * 
	 * @param	array	Array
	 * @param	string	key
	 * @param	mixed	val
	 * @access	public
	 * @return	array
	 */
	public function array_unshift_assoc(&$arr, $key, $val)
	{
	
		$arr		= array_reverse($arr, true);
		$arr[$key]	= $val;
		$arr		= array_reverse($arr, true);
		
		return $arr;	
	
	}
	
	
	
	/**
	 * Methode zur Ersetzen von Umlauten
	 * 
	 * @param	string	Zeichenkette
	 * @access	public
	 * @return	string
	 */
	public function replaceSpecialChars($str)
	{
		
		$search = array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß');
		$replace = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss');
		
		return str_replace($search, $replace, utf8_decode($str)); // Ersetzen der Umlaute und Zeichen
		
	}
	
	
	
	/**
	 * Funktion zur Erkennung von iPad, iPhone und iPod
	 * 
	 * @access	public
	 * @return	string/boolean
	 */
	public function isAppleDevice()
	{
		
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			
			$ua = $GLOBALS['_SERVER']['HTTP_USER_AGENT'];
			
			if(stripos("iPhone", $ua) !== false)
				return "iPhone";
			elseif(stripos("iPad", $ua) !== false)
				return "iPad";
			elseif(stripos("iPod", $ua) !== false)
				return "iPod";
		}
		
		return false;
		
	}


	/**
	 * Zu große POST Requests abfangen
	 *
	 * @access	public
     * @return  string
	 */
	public function checkPostRequestTooLarge()
	{

		if($GLOBALS['_SERVER']['REQUEST_METHOD'] == 'POST' 
		&& empty($GLOBALS['_POST']) 
		&& empty($GLOBALS['_FILES']) 
		&& $GLOBALS['_SERVER']['CONTENT_LENGTH'] > 0)
		{
		
			$displayMaxSize = ini_get('post_max_size');

			return $GLOBALS['_SERVER']['CONTENT_LENGTH'];
		}
		
		return false;
	
	}


	/**
	 * Ersetzt Inhaltsplatzhalter mit Inhalten
	 *
	 * @access	public
     * @return  string
	 */
	public function getMemoryUsage()
	{

		self::$o_mainTemplate->poolAssign["memory_usage_allo"]		= Logging::getMemoryUsage();
		self::$o_mainTemplate->poolAssign["memory_usage_allo_peak"]	= Logging::getMemoryUsage(true);
		self::$o_mainTemplate->poolAssign["memory_usage_real"]		= Logging::getMemoryUsage(false, true);
		self::$o_mainTemplate->poolAssign["memory_usage_real_peak"]	= Logging::getMemoryUsage(true, true);
	
	}	
	
}
