<?php
namespace Concise;


// Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEngine.php"; // ContentsEngine einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/DB/class.Locks.php"; // Lockingklasse einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php"; // Filesklasse


/**
 * Systemklasse Concise WMS - Admin
 * 
 */

class Admin extends ContentsEngine
{
	
	/**
	 * Beinhaltet den Benutzernamen des eingeloggten Benutzers
	 *
	 * @access public
     * @var    string
     */
	protected $loggedUser = "";
	
	/**
	 * Beinhaltet die Benutzer-ID des eingeloggten Benutzers
	 *
	 * @access public
     * @var    string
     */
	protected $loggedUserID = "";
	
	/**
	 * Beinhaltet die Benutzergruppe des eingeloggten Benutzers
	 *
	 * @access public
     * @var    string
     */
	protected $loggedUserGroup = "";
	
	/**
	 * Beinhaltet die eigenen Benutzergruppen des eingeloggten Benutzers
	 *
	 * @access public
     * @var    string
     */
	protected $loggedUserOwnGroups = array();
	
	/**
	 * Beinhaltet mögliche edit Benutzergruppen des eingeloggten Benutzers
	 *
	 * @access public
     * @var    string
     */
	protected $loggedUserEditGroups = array();
	
	/**
	 * Beinhaltet die erlaubten Benutzergruppen
	 *
	 * @access protected
     * @var    array
     */
	protected $userGroups = array();
	
	/**
	 * Beinhaltet eigene Benutzergruppen 
	 *
	 * @access protected
     * @var    array
     */
	protected $ownUserGroups = array();
	
	/**
	 * Beinhaltet die vordefinierten Benutzergruppen
	 *
	 * @access protected
     * @var    array
     */
	protected $systemUserGroups = array();
	
	/**
	 * Beinhaltet die Backend-Benutzergruppen
	 *
	 * @access protected
     * @var    array
     */
	protected $backendUserGroups = array();
	
	/**
	 * Beinhaltet Seiten des Adminbereichs bzw. Tasks (task=) als Array mit den Benutzergruppen, die dafür zugelassen sind
	 *
	 * @access protected
     * @var    array
     */
	protected $adminPages = array();
		
	/**
	 * adminTaskTypes (type=) Submenü-Untertypen
	 *
	 * @access protected
     * @var    array
     */
	protected $adminTaskTypes = array(	"edit",
										"articles",
										"news",
										"planner",
										"gallery",
										"gbook",
										"comments",
										"newsl"
								);
	
	/**
	 * moduleTypes (task=modules&type=) Modultypen
	 *
	 * @access protected
     * @var    array
     */
	protected $moduleTypes = array(	"articles",
									"news",
									"planner",
									"gallery",
									"gbook",
									"comments"
								);
	
	/**
	 * dataModuleTypes Daten-Modultypen
	 *
	 * @access protected
     * @var    array
     */
	protected $dataModuleTypes = array(	"articles",
										"news",
										"planner"
									);
		
	
	/**
	 * campaignTypes (task=campaigns&type=) Kampagnentypen
	 *
	 * @access protected
     * @var    array
     */
	protected $campaignTypes = array(	"newsl"
								);

	/**
	 * Task access
	 *
	 * @access private
     * @var    boolean
     */
	private $taskAccess = false;
	
	/**
	 * Template-Bereich statt Haupseiteninhalt
	 *
	 * @access public
     * @var    boolean
     */
	public $isTemplateArea = false;
	
	/**
	 * Beinhaltet die Inhalte der Adminseite
	 *
	 * @access public
     * @var    string
     */
	public $adminContent = "";
	
	/**
	 * Inhalte der rechten Spalte im Adminbereich
	 *
	 * @access public
     * @var    array
     */
	public $adminRightBarContents = array();

	/**
	 * Beinhaltet den Header der Adminseite
	 *
	 * @access public
     * @var    string
     */
	public $adminHeader = "";

	/**
	 * Array mit Strings für die Vorschaunavigation
	 *
	 * @access public
     * @var    array
     */
	public static $statusNavArray = array("update" => "","preview" => "");

	/**
	 * Beinhaltet PreviewNav-Icons
	 *
	 * @access public
     * @var    string
     */
	public $previewNav = "";

	/**
	 * Beinhaltet Änderungen-Icon
	 *
	 * @access public
     * @var    string
     */
	public $previewNavChanges = "";

	/**
	 * Beinhaltet die aktuelle Adminseite
	 *
	 * @access public
     * @var    string
     */
	public $currAdminPage = "adminMain";

	/**
	 * Wartungsmodus, an = true
	 *
	 * @access protected
     * @var    boolean
     */
	protected $maintenanceMode = false;

	/**
	 * Beinhaltet ein Locking-Objekt
	 *
	 * @access public
     * @var    object
     */
	public $LOCK = null;

	/**
	 * Beinhaltet ein generelles Locking-Objekt
	 *
	 * @access public
     * @var    object
     */
	public $GENLOCK = null;

	/**
	 * Es besteht ein Page- oder Gen-Lock
	 *
	 * @access protected
     * @var    boolean
     */
	protected $isLocked = false;

	/**
	 * Beinhaltet Locking-Informationen
	 *
	 * @access public
     * @var    array
     */
	public $pageLock = array();

	/**
	 * Beinhaltet Locking-Informationen
	 *
	 * @access public
     * @var    array
     */
	public $foreignPageLock = array();

	/**
	 * Beinhaltet generelle Locking-Informationen
	 *
	 * @access public
     * @var    array
     */
	public $genLock = array();

	/**
	 * Beinhaltet den Get-Parameter der anzuzeigenden Unterseite des Adminbereichs
	 *
	 * @access public
     * @var    string
     */
	public static $task = "";

	/**
	 * Beinhaltet den Get-Parameter der anzuzeigenden 2nd level Unterseite des Adminbereichs
	 *
	 * @access public
     * @var    string
     */
	public static $type = "";

	/**
	 * Array mit Info über einzubindende Style und Script Gruppen
	 *
	 * @access public
     * @var    string
     */
	protected $headIncludeFiles = array(	"editor"		=> false,
											"moduleeditor"	=> false,
											"commenteditor"	=> false,
											"newsleditor"	=> false,
											"sortable"		=> false,
											"fileupload"	=> false,
											"filemanager"	=> false,
											"colorpicker"	=> false
										);
		
	/**
	 * Beinhaltet die Sprachkürzel installierter Sprachen
	 *
	 * @access public
     * @var    array
     */
	public $installedLangs = array();
		
	/**
	 * Beinhaltet installierte Plug-Ins mit Integration ins Adminmenü
	 *
	 * @access public
     * @var    array
     */
	public $adminPlugins = array();
		
	/**
	 * Plugin-Adminseite
	 *
	 * @access public
     * @var    boolean
     */
	public $isAdminPlugin = false;
		
	/**
	 * Beinhaltet die page_id der neu anzulegenden Seite
	 *
	 * @access public
     * @var    string
     */
	public $newPageId = "";
		
	/**
	 * Beinhaltet den Namen der neu anzulegenden Seite
	 *
	 * @access public
     * @var    string
     */
	public $newItem = "";
		
	/**
	 * Beinhaltet den Alias der neu anzulegenden Seite
	 *
	 * @access public
     * @var    string
     */
	public $newAlias = "";
		
	/**
	 * Beinhaltet die Id der zu bearbeitenden Seite/Template
	 *
	 * @access public
     * @var    string
     */
	public $editId = "";
		
	/**
	 * Beinhaltet die Area des zu bearbeitenden Templates
	 *
	 * @access public
     * @var    string
     */
	public $editTplArea = "";
	
	/**
	 * Beinhaltet die aktuelle Sprache für den Adminbereich
	 *
	 * @access public
     * @var    string
     */
	public $adminLang = "";
	
	/**
	 * Pfad zu Sprachverzeichnis
	 *
	 * @access public
     * @var    array
     */
	public $langsPath = "";
	
	/**
	 * Beinhaltet vorhandene Sprachkürzel
	 *
	 * @access public
     * @var    array
     */
	public $natLangs = array();
	
	/**
	 * Beinhaltet vorhandene Landesflaggen
	 *
	 * @access public
     * @var    array
     */
	public $flagLangs = array();
	
	/**
	 * Beinhaltet die Pfad zur Flaggendatei der aktuellen Bearbeiten-Sprache
	 *
	 * @access public
     * @var    string
     */
	public $editLangFlag = "";
	
	/**
	 * Sprachauswahl-Menü
	 *
	 * @access public
     * @var    string
     */
	public $langSelector = "";
	
	/**
	 * Skins für den Adminbereich
	 *
	 * @access public
     * @var    array
     */
	public $adminSkins	= array("default",
								"bordeau",
								"blue",
								"mint",
								"brazen",
								"carbon"
								);
	
	/**
	 * Beinhaltet die Anzahl an BELEGTEN Inhaltselementen aus der db "contents" für die jeweilige Inahltsseite
	 *
	 * @access public
     * @var    int
     */
	public $usedFieldNames = 0;
		
	/**
	 * Beinhaltet die Anzahl an gefundenen Mediendaten (Dateien)
	 *
	 * @access public
     * @var    int
     */
	public $mediaCount = "";
	
	/**
	 * Beinhaltet erlaubte Dateinamenerweiterungen für den Datei-Upload
	 *
	 * @access private
     * @var    array
     */
	private $allowedFiles = array();
	
	/**
	 * Beinhaltet die erlaubten MIME-Types für den Datei-Upload
	 *
	 * @access public
     * @var    array
     */
	public $allowedMimeTypes = array("image/jpeg", "image/gif", "image/png", "image/x-png", "application/x-shockwave-flash", "video/x-flv", "video/x-m4v", "video/mp4", "video/ogg", "video/webm", "video/mpeg", "video/quicktime", "application/x-msmetafile", "video/x-msvideo", "audio/x-pn-realaudio", "audio/mpeg", "audio/mpeg3", "audio/ogg", "application/ogg", "application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/zip", "text/plain", "image/bmp", "image/x-windows-bmp", "image/x-icon", "image/svg+xml");

	/**
	 * Check for updates interval
	 *
	 * @access class const
     * @const  int
     */
	const updateInterval = 600;

	/**
	 * Updates verfügbar
	 *
	 * @access private
     * @var    boolean
     */
	protected $updateAvailable = false;

	/**
	 * Beinhaltet einen Hinweis über verfügbare Updates
	 *
	 * @access public static
     * @var    string
     */
	protected static $updateHint = "";
		
	/**
	 * Array mit möglichen Listenlimits
	 *
	 * @access public
     * @var    int
     */
	public $limitOptions = array(10, 25, 50, 100);
		
	/**
	 * Beinhaltet einen Counter
	 *
	 * @access public
     * @var    int
     */
	public $counter = 1;
	

	/**
	 * Admin-Konstruktor
	 * 
	 * @access	public
	 * @param	object	$DB		DB-Objekt
	 * @param	object	$o_lng	Sprachobjekt
	 */
	public function __construct($DB, $o_lng)
	{
	
		// Browser-Caching abschalten
		$this->setBrowserNoCache();
		
		// Benutzerdetails lesen und Benutzer verifizieren
		$this->verifyUser();
		
		// Adminseiten-Definitionen
		$this->getAdminPagesDefinitions();
		
		// Inhaltstypen-Definitionen
		$this->contentTypes = $this->getCoreContentTypes();		

		// ContentsEngine Constructor
		parent::__construct($DB, $o_lng);

		// Check Session regenerate
		$this->checkSessionRegenerate();
		
		// Sprache für Bearbeitung festlegen
		$this->getLanguageParams();

		// Auf Plug-ins mit Integration im AdminMenü überprüfen
		$this->installPlugIns();

		// Aktive Plug-ins anmelden
		$this->registerPlugIns();
		
		// Globale Event Listener anmelden
		$this->addEventListeners("global");

		// Adminseite (task) ermitteln
		$this->getAdminTaskPage();
		
		// Seite ausgeben
		$this->initPage();
		
		// Locking Initialisieren
		$this->initLocking($this->DB, $this->loggedUser);
	
	}	

	
	
	/**
	 * Erstellt ein Array mit Definitionen zu den Adminseiten
	 * 
	 * @access	private
	 */
	private function getAdminPagesDefinitions()
	{
	
		$this->adminPages = array	("new"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 1,
															"menu"		=> array(1,0,0,0,1)
														),
									"edit"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 1,
															"menu"		=> array(2,1,0,0,2)
														),			
									"sort"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 1,
															"menu"		=> array(3,0,0,0,3)
														),			
									"changes"	=> array(	"access"	=> array("admin", "editor"),
															"level"		=> false,
															"submenu"	=> false,
															"menu"		=> array(false, false, false, false, false)
														),			
									"stats"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 1,
															"menu"		=> array(4,2,0,0,4)
														),
									"tpl"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 2,
															"menu"		=> array(5,3,0,0,5)
														),
									"modules"	=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 0,
															"submenu"	=> 2,
															"menu"		=> array(6,0,0,0,6)
														),
									"articles"	=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 1,
															"submenu"	=> 2,
															"menu"		=> array(0,4,1,0,7)
														),
									"news"		=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 1,
															"submenu"	=> 2,
															"menu"		=> array(0,5,2,0,8)
														),
									"planner"	=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 1,
															"submenu"	=> 2,
															"menu"		=> array(0,6,3,0,9)
														),
									"gallery"	=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 1,
															"submenu"	=> 2,
															"menu"		=> array(0,7,4,0,10)
														),
									"gbook"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 1,
															"submenu"	=> 2,
															"menu"		=> array(0,0,5,0,11)
														),
									"comments"	=> array(	"access"	=> array("admin", "editor", "hide"),
															"level"		=> 1,
															"submenu"	=> 2,
															"menu"		=> array(0,0,6,0,12)
														),
									"forms"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 2,
															"menu"		=> array(15,0,0,0,14)
														),
									"file"		=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 0,
															"submenu"	=> 2,
															"menu"		=> array(7,8,0,0,15)
														),
									"plugins"	=> array(	"access"	=> array("admin"),
															"level"		=> 0,
															"submenu"	=> 3,
															"menu"		=> array(16,0,0,0,13)
														),
									"campaigns"	=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 0,
															"submenu"	=> 4,
															"menu"		=> array(8,0,0,0,false)
														),
									"newsl"		=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 1,
															"submenu"	=> 4,
															"menu"		=> array(0,9,0,1,16)
														),
									"langs"		=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 5,
															"menu"		=> array(9,0,0,0,17)
														),			
									"user"		=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 0,
															"submenu"	=> 5,
															"menu"		=> array(10,0,0,0,18)
														),
									"settings"	=> array(	"access"	=> array("admin", "editor"),
															"level"		=> 0,
															"submenu"	=> 6,
															"menu"		=> array(11,0,false,false,false)
														),
									"bkp"		=> array(	"access"	=> array("admin"),
															"level"		=> 0,
															"submenu"	=> 6,
															"menu"		=> array(12,0,0,0,20)
														),
									"search"	=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 0,
															"submenu"	=> 6,
															"menu"		=> array(13,0,0,0,21)
														),
									"help"		=> array(	"access"	=> array("admin", "editor", "author"),
															"level"		=> 0,
															"submenu"	=> 6,
															"menu"		=> array(14,0,0,0,22)
														),
									"update"	=> array(	"access"	=> array("admin"),
															"level"		=> false,
															"submenu"	=> false,
															"menu"		=> array(false,false,false,false,false)
														)
								);
	
		return $this->adminPages;
	}
	
	

	/**
	 * Gibt den Adminbereich aus
	 * 
	 * @param	$init	boolean	falls true, wird AdminHeader mit ausgegeben (default = false)
	 * @access	public
	 */
	public function loadAdminPage($init = false)
	{
	
		// Falls Seite neu geladen
		if($init) {
		
			// Auf Updates überprüfen, falls Admin / Editor
			if(CC_UPDATE_CHECK && $this->editorLog && empty($GLOBALS['_POST'])) {
				$this->checkForUpdates(true); // only from session, true check is done via ajax for performance reasons
				$this->getUpdateHint();
			}
			
			// Head-Definitionen (headExt)
			$this->getAdminHeadIncludes();
			
			// Head-Dateien zusammenführen
			$this->setHeadIncludes();
			
			// Token checken
			$this->checkAdminToken();

		}
		
		// Links für Vorschau und Änderungen
		$this->getPreviewNav();
		
		// Berechtigung checken
		$this->checkTaskAccess();
		
	}	
	
	
	/**
	 * Überprüfung auf verifizierten Benutzer / Benutzerdaten auslesen
	 * 
	 * @access public
	 * @return string
	 */
	public function verifyUser()
	{
	
		// Benutzergruppen holen
		$this->systemUserGroups		= User::getSystemUserGroups();
		$this->backendUserGroups	= User::getBackendUserGroups();
		$this->ownUserGroups		= $GLOBALS['ownUserGroups'];
		
		// Selbstdefinierte Benutzergruppen zum UserGroup-Array hinzufügen
		$this->userGroups			= array_merge($this->systemUserGroups, $this->ownUserGroups);

		// Security-Objekt
		$this->o_security			= Security::getInstance();
		
		// Benutzername
		$this->loggedUser			= $this->o_security->get('loggedUser');
		
		// Falls kein legitimer Benutzer zur Fehlerseite gehen
		if(empty($this->loggedUser))
			return $this->gotoErrorPage(403, "fc=403");
		
		// Benutzer-ID
		$this->loggedUserID			= (int)$this->o_security->get('loggedUserID');
		
		// Benutzer Berechtigungen holen
		$this->getAccessDetails();
		
		// Benutzergruppe bestimmen
		$this->loggedUserGroup		= $this->o_security->get('group');
		// Eigene Benutzergruppen bestimmen
		$this->loggedUserOwnGroups	= $this->o_security->get('ownGroups');
		
		// Falls kein legitimer Benutzer zur Fehlerseite gehen
		if($this->loggedUserGroup == "" || !in_array($this->loggedUserGroup, $this->backendUserGroups))
			return $this->gotoErrorPage(403);
	
		// Edit Benutzergruppen festlegen
		if($this->loggedUserGroup === "admin")
			$this->loggedUserEditGroups	= array_merge(array("admin", "editor"), $this->ownUserGroups);
		if($this->loggedUserGroup === "editor")
			$this->loggedUserEditGroups	= array_merge(array("editor"), $this->loggedUserOwnGroups);
	
	}
	

	/**
	 * Edit groups bestimmen
	 *
	 * @param	array			$groups	Edit groups
	 * @access	protected
     * @return  array
	 */
	protected function getPageEditGroups($groups)
	{
	
		$groupsWrite = array_intersect($groups, $this->loggedUserEditGroups);
		
		if(in_array("admin", $groupsWrite))
			$groupsWrite = array("admin");
		elseif(in_array("editor", $groupsWrite)) {
			$eKey	= array_keys($groupsWrite,"editor");
			unset($groupsWrite[$eKey[0]]);
		}
		
		return $groupsWrite;
	
	}
	

	/**
	 * Write permission check
	 *
	 * @param	array			$editGroups	Edit groups
	 * @access	protected
     * @return  array
	 */
	protected function getWritePermission($editGroups)
	{
	
		if ($this->loggedUserGroup === "admin"
		|| ($this->loggedUserGroup === "editor"
			&& (empty($editGroups) || count(array_intersect($editGroups, $this->loggedUserEditGroups)))
			)
		)
			return true;
		
		return false;
	
	}
	

	/**
	 * Sprache für die Bearbeitung auslesen
	 * 
	 * @access public
	 * @return string
	 */
	public function getLanguageParams()
	{
			
		$this->adminLang		= $this->o_lng->adminLang; // vorhandene Sprachen für Seiten
		$this->installedLangs	= $this->o_lng->installedLangs; // vorhandene Sprachen für Seiten
		$this->flagLangs		= $this->o_lng->existFlag; // vorhandene Sprachenflaggen für Seiten
		$this->natLangs			= $this->o_lng->existNation; // vorhandene Sprache (Nationalität) für Seiten
		$this->langsPath		= PROJECT_HTTP_ROOT . '/langs';
		
		// Sprache für die Bearbeitung von Seiten im Adminbereich auslesen
		$this->editLang			= $this->o_lng->editLang;  // Andernfalls Sprache auf default setzen
		$langKey				= array_search ($this->editLang, $this->installedLangs);
		$this->editLangFlag		= '<img src="' . $this->langsPath . '/' . $this->editLang . '/' . $this->flagLangs[$langKey] . '" alt="'.$this->editLang.'" title="'.$this->natLangs[$langKey].'" class="flag" />';
	
	}	
	

	/**
	 * Sprache für die Bearbeitung auslesen
	 * 
	 * @access public
	 * @return string
	 */
	public function getAdminLang()
	{
	
		return $this->adminLang;
	
	}	
	

	/**
	 * Locking initialisieren
	 * 
	 * @access	protected
	 * @param	$DB	object	DB-Objekt
	 * @return	string
	 */
	protected function initLocking($DB, $loggedUser)
	{
	
		// Locking
		$this->LOCK				= new Locks(600, $DB);
		$this->pageLock			= $this->LOCK->readLock("all", "editpages");
		$this->foreignPageLock	= $this->LOCK->readLock($loggedUser, "contents");
		$this->GENLOCK			= new Locks(1800, $DB);
		$this->genLock			= $this->GENLOCK->readLock("all", "langs");
		
		// Falls Pages-Lock durch User besteht, aber keine Seitenbaumverwaltung mehr stattfindet, Sperre löschen
		if($this->pageLock[0] == true 
		&& $this->pageLock[1]['lockedBy'] == $loggedUser 
		&& (!isset(self::$task) 
			|| self::$task != "new" 
			&& self::$task != "sort")
		)
			$this->LOCK->deleteAllUserLocks($loggedUser, "editpages");
		
		// Falls singlePage-Lock durch User besteht, aber keine Seiten-/Template-Editierung mehr stattfindet, Sperre löschen
		if(!isset($GLOBALS['_POST']['del_id'])
		&& (!isset(self::$task) 
			|| self::$task != "edit" 
			&& self::$task != "tpl") 
		) {
		
			$ownPageLock		= $this->LOCK->readLock($loggedUser, "contents", true);
			
			if($ownPageLock[0] == true 
			&& $ownPageLock[1]['lockedBy'] == $loggedUser 
			)
				$this->LOCK->deleteAllUserLocks($loggedUser);
		}
		
		// Falls Lang-Lock durch User besteht, aber keine Sprachverwaltung mehr stattfindet, Sperre löschen
		if($this->genLock[0] == true 
		&& $this->genLock[1]['lockedBy'] == $loggedUser 
		&& (!isset(self::$task) 
			|| self::$task != "langs")
		)
			$this->GENLOCK->deleteAllUserLocks($loggedUser, "langs");
		
				
		// Veraltete Einträge löschen
		$this->LOCK->deleteOldLocks();
		$this->GENLOCK->deleteOldLocks();
	
	}	
	

	/**
	 * Auf Locking prüfen
	 * 
	 * @access public
	 * @return string
	 */
	public function checkGenPageLocks()
	{
	
		// Locking (generell)
		if($this->checkGenLock())
			return true;		
		
		// Locking (Seitenstruktur)
		if($this->checkPageLock())
			return true;

		return false;
	
	}
	

	/**
	 * Auf generelles Locking prüfen
	 * 
	 * @access public
	 * @return string
	 */
	public function checkGenLock()
	{
	
		if($this->genLock[0] == true 
		&& $this->genLock[1]['lockedBy'] != $this->loggedUser 
		&& self::$task != "stats" 
		&& self::$task != "file" 
		&& self::$task != "user" 
		&& self::$task != "help" 
		&& self::$task != "bkp" 
		&& self::$task != "settings"
		) {
			
			$this->isLocked		= true;

			// #adminContent
			$this->adminContent =	$this->openAdminContent();
			
			$lockStr	= sprintf(ContentsEngine::replaceStaText('{s_error:lockdata}'), '<strong>' . $this->genLock[1]['lockedBy'] . '</strong>', '<strong>' . date("H:i:s", $this->genLock[1]['lockedUntil']) . '</strong>');
			
			$this->adminContent .=	'<p class="notice error">{s_error:lockall}</p>' . "\r\n" .
									'<p>&nbsp;</p>' . "\r\n" .
									'<p class="lockedBy framedParagraph">' .
									parent::getIcon("user", "inline-icon") .
									$lockStr .
									self::getRefreshPageButton() .
									'</p>' . "\r\n";
			
			$this->adminContent .=	$this->getBackButtons("main");
			
			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();

			return true;
		}
		
		return false;
		
	}	
	

	/**
	 * Auf gelockte Seiten prüfen
	 * 
	 * @access public
	 * @return string
	 */
	public function checkPageLock()
	{
	
		if($this->pageLock[0] == true 
		&& $this->pageLock[1]['lockedBy'] != $this->loggedUser 
		&& (	self::$task == "new" 
			||  self::$task == "edit" 
			||  self::$task == "sort" 
			||  self::$task == "langs" 
			|| (self::$task == "tpl" && self::$type == "edit")
			)
		) {		
			
			$this->isLocked		= true;
			
			// #adminContent
			$this->adminContent =	$this->openAdminContent();
			
			$lockStr	= sprintf(ContentsEngine::replaceStaText('{s_error:lockdata}'), '<strong>' . $this->pageLock[1]['lockedBy'] . '</strong>', '<strong>' . date("H:i:s", $this->pageLock[1]['lockedUntil']) . '</strong>');

			$this->adminContent .=	'<p class="notice error">{s_error:lockpages}</p>' . "\r\n" .
									'<p>&nbsp;</p>' . "\r\n" .
									'<p class="lockedBy framedParagraph">' .
									parent::getIcon("user", "inline-icon") .
									$lockStr .
									self::getRefreshPageButton() .
									'</p>' . "\r\n";
			
			$this->adminContent .=	$this->getBackButtons("main");

			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();

			return true;
		}
		
		return false;
		
	}	
	
	

	/**
	 * Auf gelockten Inhalt prüfen
	 * 
	 * @access protected
	 * @param	$entry	string	Entry ID
	 * @param	$table	string	DB table
	 * @param	$user	string	User name
	 * @param	$notice	string	Notice ext (default = '')
	 * @return string
	 */
	protected function checkLocking($entry, $table, $user, $notice = "{s_error:lockededitentry}")
	{

		// Locking checken
		$readLock	= $this->LOCK->readLock($entry, $table);
		
		$setLock	= array(true);
		
		if($readLock[0] == true && $readLock[1]['lockedBy'] != $user)
			$setLock[0] = false;
		else {
			// bestehende Einträge des Benutzers löschen
			$this->LOCK->deleteAllUserLocks($user);
			// Lock setzen, falls nicht bereits ein anderer Lock besteht
			if(!$this->isLocked)
				$setLock = $this->LOCK->setLock($entry, $table, $user);
		}
			
		if($setLock[0] == false) {
			
			$this->adminContent .=	$this->getLockMessage($readLock[1]['lockedBy'], $readLock[1]['lockedUntil'], $notice);
			
			return true;
		}
		
		return false;
	
	}

	
	/**
	 * Locking message
	 * 
	 * @access private
	 * @param	$user	string	User name
	 * @param	$time	string	Locked until
	 * @param	$notice	string	Notice ext
	 * @return string
	 */
	private function getLockMessage($user, $time, $notice)
	{

		$lockStr	= sprintf(ContentsEngine::replaceStaText('{s_error:lockdata}'), '<strong>' . $user . '</strong>', '<strong>' . date("H:i:s", $time)) . '</strong>';
		
		$lockInfo	=	'<p class="notice error">' . $notice . '</p>' . "\r\n" .
						'<p>&nbsp;</p>' . "\r\n" .
						'<p class="lockedBy framedParagraph">' .
						parent::getIcon("user", "inline-icon") .
						$lockStr .
						self::getRefreshPageButton() .
						'</p>' . "\r\n";
		
		return $lockInfo;
	}


	/**
	 * Auf Berechtigung zum Aufrufen des Inhaltsbereichs (task) prüfen
	 * 
	 * @access protected
	 * @return string
	 */
	protected function checkTaskAccess()
	{
			
		// Task-Check
		if(self::$task != ""
		&& self::$task != "main"
		&& ( !array_key_exists(self::$task, $this->adminPages)
		  || !in_array($this->loggedUserGroup, $this->adminPages[self::$task]['access']))
		) {
			$this->taskAccess	= false;
			return false;
		}
		
		// Falls keine Berechtigung, Hauptbereich
		$this->taskAccess		= true;
		return true;
		
	}	


	/**
	 * getTaskAccess
	 * 
	 * @access protected
	 * @return string
	 */
	protected function getTaskAccess()
	{
	
		return $this->taskAccess;
	
	}
	

	/**
	 * Adminseiten bestimmen
	 * 
	 * @access public
	 * @return string
	 */
	public function getAdminTaskPage()
	{
	
		if(isset($GLOBALS['_GET']['task']) && array_key_exists($GLOBALS['_GET']['task'], $this->adminPages))
		{
	
			self::$task = $GLOBALS['_GET']['task'];
			
			
			// Falls eine 2nd-Level Unterseite
			if(isset($GLOBALS['_GET']['type']) && in_array($GLOBALS['_GET']['type'], $this->adminTaskTypes)) {
	
				self::$type = $GLOBALS['_GET']['type'];
				// Prefix für das Hintergrundbild der Headerbox
				$this->currAdminPage = self::$type;
			}
			else {
				// Prefix für das Hintergrundbild der Headerbox
				$this->currAdminPage = self::$task;
			}
		}
	}


	/**
	 * Head-Definitionen, Skript- und Style-Dateien für die Adminseite festlegen
	 * 
	 * @param	string	$fromFE		Triggered from front end (default = false)
	 * @access public
	 * @return string
	 */
	public function getAdminHeadIncludes($fromFE = false)
	{
		
		// Script- und css-Includes für den HTML-Headbereich (headIncludes)
		#if(EDITOR_VERSION == 4)
		require_once PROJECT_DOC_ROOT."/inc/classes/Admin/class.HeadIncludes.php"; // TinyMCE 4
		
		$o_headInc	= new HeadIncludes($this->headIncludeFiles, $this->adminLang, $fromFE);
		$o_headInc->getHeadIncludes(self::$task, self::$type, "textarea.cc-editor-add");
		
		$this->mergeHeadCodeArrays($o_headInc);
	
	}
		


	/**
	 * Medienordner auslesen
	 * 
	 * @param	string	$tag		Tag id
	 * @access	public
	 * @return	string
	 */
	public function getTagEditorScripts()
	{
	
		return	'<script>' . "\r\n" .
				'head.ready(function(){' . "\r\n" .
					'head.load({tagEditorcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.css"});' . "\r\n" .
					'head.load({tagEditorcaret: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.caret.min.js"});' . "\r\n" .
					'head.load({tagEditor: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.js"});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
		
	}


	/**
	 * Footer-Action: Token neu setzen, Vorschauicons und HeaderBox-Zusatz
	 * 
	 * @access public
	 * @return string
	 */
	public function footerAction()
	{
	
		// Script-Code für Headbereich
		$this->getScriptVars();

		// Token zur Session hinzufügen
		$this->setToken();
		
		// Vorschauicons
		$this->setAdminMenuDisplay();
		
		// Vorschauicons
		$this->previewNav 	=	$this->getAdminTopPanel();
		
		// Headerbox (open Tag)
		parent::$o_mainTemplate->poolAssign["open_headerbox"] = $this->getHeaderBox();
	
	}
	
	
	/**
	 * Admin menu display
	 * 
	 * @access public
	 * @return string
	 */
	public function setAdminMenuDisplay()
	{
	
		$mainConClass	= array();
		
		// sidebar toggle if mobile
		if(!empty(parent::$device["isPhone"])) {
			parent::$o_mainTemplate->poolAssign["leftbar_class"] = "collapsed";
			parent::$o_mainTemplate->poolAssign["rightbar_class"] = "collapsed";
			return true;
		}
		
		// sidebar toggle by cookie
		if(isset($GLOBALS['_COOKIE']['cc_sidebars'])) {

			$sidebars		= (array)json_decode($GLOBALS['_COOKIE']['cc_sidebars']);

			if(!empty($sidebars["left"])) {
				parent::$o_mainTemplate->poolAssign["leftbar_class"] = "collapsed";
				$mainConClass[]	= "expanded-left";
			}
			if(!empty($sidebars["right"])) {
				parent::$o_mainTemplate->poolAssign["rightbar_class"] = "collapsed";
				$mainConClass[]	= "expanded-right";
			}
			parent::$o_mainTemplate->poolAssign["maincon_class"] = implode(" ", $mainConClass);
		}
		return true;
	
	}
	
	
	/**
	 * AdminTopPanel (Vorschauicons)
	 * 
	 * @access public
	 * @return string
	 */
	public function getAdminTopPanel()
	{
		
		// Vorschauicons
		return	'<div id="iconPanelTop">' . "\r\n" .
				implode("", self::$statusNavArray) .
				'</span>' . "\r\n" .
				$this->previewNavChanges . 
				'</div>' . "\r\n" .
				'</div>';
	
	}
	
	
	/**
	 * AdminTask Background (Headerbox)
	 * 
	 * @access public
	 * @return string
	 */
	public function getHeaderBox()
	{
		
		$headerBox	=	'<!-- begin #headerBox -->' . PHP_EOL .
						'<div id="headerBox" class="headerBox';
		
		if($this->currAdminPage == "adminMain")
			return $headerBox . '">' . PHP_EOL;
		
		if($this->isAdminPlugin) {
			$icon		= parent::getIcon(self::$task, "cc-taskicon");
		}
		else {
			$icon		= parent::getIcon($this->currAdminPage, "cc-taskicon");
		}
		
		// Hintergrund-Headerbox
		$headerBox	.=	' withBG header-'.$this->currAdminPage.'">' . PHP_EOL .
						$icon;
		
		return	$headerBox;
	
	}


	/**
	 * Footer-Action: Token neu setzen, Vorschauicons und HeaderBox-Zusatz
	 * 
	 * @access public
	 * @return string
	 */
	public function setContentReplacements()
	{
		
		// RIGHT bar
		parent::$o_mainTemplate->poolAssign["lang_menu"]		= $this->langSelector;
		parent::$o_mainTemplate->poolAssign["RIGHT"]			= implode("", $this->adminRightBarContents);
		
		// Top bar / menus
		parent::$o_mainTemplate->poolAssign["account"]			= $this->getAdminAccountMenu();
		parent::$o_mainTemplate->poolAssign["admin_menu"]		= $this->getAdminMenu(0);
		parent::$o_mainTemplate->poolAssign["admin_menu_top"]	= $this->getAdminMenu(4);
		parent::$o_mainTemplate->poolAssign["admin_page"]		= $this->currAdminPage;
		parent::$o_mainTemplate->poolAssign["admin_task"]		= self::$task;
		parent::$o_mainTemplate->poolAssign["admin_type"]		= self::$type;
		parent::$o_mainTemplate->poolAssign["loggeduser"]		= $this->loggedUser;
		parent::$o_mainTemplate->poolAssign["preview"] 			= $this->previewNav;

	}
	
	
	/**
	 * Token überprüfen
	 * 
	 * @access public
	 * @return string
	 */
	public function checkAdminToken()
	{

		// Token überprüfen (ggf. außer bei Seiten mit Ajax, da sonst Session-Token neu)
		if(isset($GLOBALS['_POST']['token']) && parent::$tokenOK === false) {

			header("Location: " . ADMIN_HTTP_ROOT . "?task=" . self::$task . (self::$type != "" ? "&type=" . self::$type : '')); // Falls Token nicht übereinstimmt, Seite neu laden
			exit;
		}
		
	}
	

	/**
	 * Auf Updates überprüfen
	 * 
	 * @param boolean	$sessionOnly (default = false)
	 * @access protected
	 * @return string
	 */
	protected function checkForUpdates($sessionOnly = false)
	{
	
		if($sessionOnly)
			return $this->updateAvailable = !empty($this->g_Session['updateAvailable']);
		
		// Falls Updates verfügbar, Hinweisbox generieren
		if($this->updateAvailable = $this->checkUpdateAvailable())
			$this->getUpdateHint();
		
		return $this->updateAvailable;
	
	}
	

	/**
	 * Gibt zurück ob Updates verfügbar
	 * 
	 * @access private
	 * @return boolean
	 */
	private function checkUpdateAvailable()
	{
	
		// Falls bereits gecheckt, Info aus Session
		if(isset($this->g_Session['updateAvailable']))
			return $this->g_Session['updateAvailable'];
	
		// Falls Zeitintervall für Überprüfung abgelaufen oder Hauptbereich
		if(isset($GLOBALS['_COOKIE']['updateCheckDone']))
			return false;
		
		// Andernfalls auf Updates auf Server prüfen
		require_once PROJECT_DOC_ROOT . '/inc/classes/Update/class.LiveUpdate.php';
		
		// Plugins wegen Performance nur im Hauptbereich prüfen
		$checkPlugins	= empty(self::$task) || self::$task == "main" ? true : false;
		
		$o_update		= new LiveUpdate($this->DB, $this->o_lng, $this->installedPlugins);
		$o_update->initLiveUpdater(true, $checkPlugins);
		$isUpdate		= $o_update->updateAvailable;
		
		if($isUpdate)
			return true;
		else {
			@setcookie("updateCheckDone", true, time()+ self::updateInterval, "/");
			return false;
		}
	
	}
	

	/**
	 * Hinweis zu verfügbaren Updates
	 * 
	 * @access private
	 * @return string
	 */
	private function getUpdateHint()
	{
	
		// Button changes
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=update',
								"class"		=> "updateHint gotoUpdatePage button-icon-only button-small",
								"text"		=> "",
								"title"		=> "{s_hint:newupdates}",
								"icon"		=> "update"
							);
		
		self::$updateHint = parent::getButtonLink($btnDefs);

		return self::$updateHint;
		
	}
	

	/**
	 * Links für Vorschau und Änderungen
	 * 
	 * @access public
	 * @return string
	 */
	public function getPreviewNav()
	{

		// Icons für Vorschau und Änderungen		
		// Include hidden updates button (check is done via ajax to prevent hanging due to stream timeouts)
		if(CC_UPDATE_CHECK && $this->editorLog && self::$updateHint != "")
			self::$statusNavArray['update']	=	'<div id="updateNav" class="iconPanel-top' . (!$this->updateAvailable ? ' hide' : '') . '">' . self::$updateHint . '</div>' . "\r\n";
		
		self::$statusNavArray['preview']	=	'<div id="previewNav" class="iconPanel-top">' . "\r\n" .
												'<span class="previewStatusBox">' . "\r\n";
			
		// Website Live-Status, falls Editor mit Link
		if($this->adminLog) {
			$liveOff	= 'a href="' . SYSTEM_HTTP_ROOT . '/access/editPages.php?page=admin&sitemode=0"';
			$liveOn		= 'a href="' . SYSTEM_HTTP_ROOT . '/access/editPages.php?page=admin&sitemode=1"';
			$closeTag	= "a";
		}
		else {
			$liveOff	= "span";
			$liveOn		= "span";
			$closeTag	= "span";
		}
		
		self::$statusNavArray['preview']	.=	'<span class="siteStatusBox">' . "\r\n";
		self::$statusNavArray['preview']	.=	'<' . $liveOff . ' class="goLive live-on' . (WEBSITE_LIVE ? '' : ' hide') . '">' . "\r\n";
		self::$statusNavArray['preview']	.=	parent::getIcon('go-stage', 'icon-live icon-live-on', 'title="{s_title:websitelive}' . ($this->adminLog ? '<br />{s_title:gostage}' : '') . '"') .
												'</' . $closeTag . '>';
		self::$statusNavArray['preview']	.=	'<' . $liveOn . ' class="goLive live-off' . (WEBSITE_LIVE ? ' hide' : '') . '">' . "\r\n";
		self::$statusNavArray['preview']	.=	parent::getIcon('go-live', 'icon-live icon-live-off', 'title="{s_title:websitestage}' . ($this->adminLog ? '<br />{s_title:golive}' : '') . '"') .
												'</' . $closeTag . '>';
		self::$statusNavArray['preview']	.= '</span>' . "\r\n";
		
		
		// Falls noch nicht übernommene Änderungen vorliegen oder der HTML-Cache aktiviert ist, Hinweiszeichen im Accountmenu anzeigen
		$this->previewNavChanges	.= '<span class="adminChanges-box">';
		
		// Nicht übernommene Änderungen
		if(count($this->diffConIDs) > 0) {
		
			// Button changes
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT,
									"class"		=> "adminChanges ok button-icon-only button-small",
									"text"		=> "",
									"title"		=> "{s_notice:changes}",
									"icon"		=> "warning"
								);
			
			$this->previewNavChanges .=	parent::getButtonLink($btnDefs);
		}
		
		// Cache
		if(CACHE) {
		
			// Button cache
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cache&id=all',
									"class"		=> "refreshCache button-icon-only button-small",
									"text"		=> "",
									"title"		=> "{s_notice:cache}",
									"attr"		=> 'data-action="refreshcache"',
									"icon"		=> "cache"
								);
			
			$this->previewNavChanges .=	parent::getButtonLink($btnDefs);
		}
		
		$this->previewNavChanges .= '</span>';
		
	}


	/**
	 * Überprüft auf Vorhandensein (eigener) Benutzergruppen
	 * 
	 * @param string	$group Benutzergruppe
	 * @access public
	 * @return string
	 */
	public function checkUserGroupExists($group)
	{
		
		$group = explode(",", $group);
		
		foreach($group as $ownGroup) {
			if(!in_array($ownGroup, $this->userGroups)	&& !in_array($ownGroup, $this->ownUserGroups))
				return false;
		}
		return true;
		
	}
	
	

	/**
	 * Sprachauswahlmenü für right bar, falls mehrere Sprachen installiert
	 * 
	 * @param string	$editId editId (default = "")
	 * @access public
	 * @return string
	 */
	public function getLangSelection()
	{
		
		// Bei mehreren Sprachen Sprachauswahl oben von right bar
		if(count($this->installedLangs) > 1) {
		
			$this->langSelector	=	'<div class="controlBar">' . "\r\n" .
									$this->chooseLang() .
									'</div>' . "\r\n";
		}
	}
	
	

	/**
	 * Erstellt ein Auswahlmenü für Sprachen
	 * 
	 * @param string	$editId editId (default = "")
	 * @access public
	 * @return string
	 */
	public function chooseLang($editId = "")
	{
		
		$options = "";
		$return = "";
		$i = 0;
		
		foreach($this->installedLangs as $addLang) {
						
			$options .= '<option value="' . $addLang . '"' . "\r\n";
			
			if($addLang == $this->editLang) {
				$options   .= ' selected="selected"' . "\r\n";
				$flag		= $this->flagLangs[$i];
				$nation		= $this->natLangs[$i];
			}
			
			$options .= ' style="background:url(' . $this->langsPath . '/' . $addLang . '/' . $this->flagLangs[$i] . ') no-repeat 40px center;"' . "\r\n";
			
			$options .= '>' . $addLang . '</option>' . "\r\n";
			
			$i++;
		}
		
		$return = 			'<div class="choose">' . "\r\n" . 
							'<form action="" id="chooseLang" method="post" data-getcontent="fullpage">' . "\r\n" . 
							'<span class="iconBox"><img src="' . $this->langsPath . '/' . $this->editLang . '/' . $flag . '" alt="' . $nation . '" title="' . $nation . '" /></span>' . "\r\n" . 
							'<select name="editLang" id="editLang" class="select rightSelect autoSubmit">' . "\r\n" . 
							$options . "\r\n" .  
							'</select>' . "\r\n" . 
							'<label class="label" title="{s_title:chooselang}"><span>&#9658;</span></label>' . "\r\n";
		
		if(isset($editId) && $editId != "")
			$return .= 		'<input type="hidden" name="edit_id" value="' . $editId . '" />' . "\r\n";
		
		if($this->isTemplateArea) {
			$return .= 		'<input type="hidden" name="edit_area" value="' . parent::$tableContents . '" />' . "\r\n" .
							'<input type="hidden" name="edit_tpl" value="' . $editId . '" />' . "\r\n";
		}
		
		$return .= 			'</form>' . "\r\n" .
							'<br class="clearfloat" /></div>' . "\r\n";
							
							
		return $return;
	}


	/**
	 * Inhaltsbereiche für Systemseiten zusammensetzen
	 *
	 * @access	public
     * @return  string
	 */
	public function assembleAdminContents($adminContent)
	{
	
		// Hauptinhalte zuweisen, falls nicht schon geschehen
		parent::$o_mainTemplate->assign("MAIN", $adminContent);

	}
	
	
	
	/**
	 * Erstellt Liste von Seitennamen/-links
	 * 
	 * @param string	$type Listentyp (default = edit)
	 * @param int		$items menu_item (default = 1)
	 * @param int		$protected geschützte Seiten (default = 0)
	 * @access public
	 * @return string
	 */
	public function listPages($type = "edit", $items = 1, $protected = 0)
	{
		
		$extCond	= "";
		$query		= array();
		
		if($items == -1) {// Falls geschützte Seiten angezeigt werden sollen (mit Adminrechten)
			$orderBy = "n.`page_id` DESC";
			$groupBy = "n.`page_id`";
			$extCond = " AND n.`page_id` < -1001";
		}
				
		else { // Falls Menüelemente angezeigt werden sollen
			$groupBy = "n.`lft`";
			$orderBy = "n.`lft`";
		}
		
		if(is_numeric($items)) {
			
			// db-Query nach Menu/nicht-Menu-Seiten
			$query = $this->DB->query("SELECT n.*,
											COUNT(*)-1 AS menulevel  
											FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` AS n, 
											`" . DB_TABLE_PREFIX . parent::$tablePages . "` AS p
											WHERE n.lft BETWEEN p.lft AND p.rgt 
											AND n.menu_item = $items
											AND p.menu_item = $items
											AND n.protected = $protected 
											$extCond 
											GROUP BY " . $groupBy . "
											ORDER BY " . $orderBy . "" 
											);
			#var_dump($query);
		}
		else {
		
			// Templates des aktuellen Themes einlesen
			foreach(parent::$areasTplContents as $area) {
				
					$query[] = $area;
			}
			
		}
		
		$rgt = 1;
		$closeList = "";
		$menuLevel = 1; // Menuepunktlevel
		$pagesList = "";
		
		
		if(count($query) > 0) { // Falls Menupunkte gefunden wurden
			
			// Falls eine Linkliste für TinyMCE erstellt werden soll, die Query zurückgeben
			if($type == "linklist")
			
				return $query;
			
			
			$pagesList = '<ul class="editList pageList list list-condensed">' . "\r\n";  // Ausgabe der Menue-Liste
			
			// Falls neu, Icon zum Einfügen an erster Position
			if($type == "new") {
				
				$pagesList .= 	'<li class="newFirstEntry listItem pageListItem">' . "\r\n" . 
								'<span class="editButtons-panel">' . "\r\n";
				
				// Button new
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'newPage button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:newpage}',
										"attr"		=> 'data-action="newpage" data-type="new_first" data-value="' . $items . '"',
										"icon"		=> "new"
									);
				
				$pagesList .=	parent::getButton($btnDefs);
								
				$pagesList .=	'</span>' . "\r\n" . 
								'</li>' . "\r\n";
	
			}
			
			// Falls sort, Icon zum Einfügen an erster Position
			if($type == "sort") { // Wenn eine Sortierliste angezeigt werden soll und keine Einträge für das Menü gefunden wurden...
			
				$pagesList .= 	'<li class="sortFirstEntry listItem pageListItem" style="display:none;">' . "\r\n" . 
								'<span class="editButtons-panel">' . "\r\n";
				
				// Button pastebelow
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'pasteBelow newItem button-icon-only',
										"text"		=> "",
										"title"		=> '{s_javascript:addpagebelow}',
										"attr"		=> 'data-newpos="' . $items . '" data-type="pasteBelow" data-pageid="new"',
										"icon"		=> "pastebelow"
									);
				
				$pagesList .=	parent::getButton($btnDefs);
				
				$pagesList .=	'</span>' . "\r\n" . 
								'<br class="clearfloat"></li>' . "\r\n";
			}
			
			$i = 1;
			
			foreach($query as $row) { // Schleife zum Ausgeben der Menupunktliste
			
				// Falls Seitenauflistung (kein Template)
				if(is_numeric($items)) {
	
			
				$i = $rgt; // speichert letzten rgt-Wert
				
				$lft		= $row['lft']; // lft-Wert
				$rgt		= $row['rgt']; // rgt-Wert
				$level		= $lft; // Level des Knotenpunkts
				$pageId		= $row['page_id']; // Menuepunktid
				$group		= explode(",", $row['group']); // Benutzergruppen (read)
				$groupWrite	= array_filter(explode(",", $row['group_edit'])); // Benutzergruppen (write)
				$online		= $row['published']; // Seitenstatus (1 = online)
				$index		= $row['index_page']; // Seitenstatus (1 = index page)
				$title		= $row['title_' . $this->editLang]; // Menuepunkttitel
				$alias		= $row['alias_' . $this->editLang]; // Menuepunktalias
				$noAccess	= false;
				$editAccess	= false;
				$menuLevel	= 1; // Menuepunktlevel
				
				if($pageId > -1000
				&& isset($row['menulevel'])
				&& $row['menulevel'] != ""
				)
					$menuLevel = $row['menulevel']; // Menuepunktlevel
				
				// Falls die Seite nur für Admin zugänglich ist, Noaccess-Icon einbinden
				if(!in_array("public", $group) && !in_array($this->loggedUserGroup, $group) && $this->loggedUserGroup != "admin")
					$noAccess	= true;
				// Edit access
				if($this->getWritePermission($groupWrite))
					$editAccess	= true;
				
				$index		= $index ? parent::getIcon("home", "indexPage", 'title="{s_title:indexpage}"') : ''; // Icon für Startseite
				
				$markBox	= '<label class="markBox' . (!$editAccess ? ' disabled' : '') . '">' . "\r\n" .
							  '<input type="checkbox" class="addVal' . (!$editAccess ? ' disabled' : '') . '" name="pageIDs[]" value="' . $pageId . '"' . (!$editAccess ? ' disabled="disabled"' : '') . ' />' . "\r\n" .
							  '</label>' . "\r\n";				
				
				
				while($level >= $i+2) { // Solange der aktuelle lft-Wert größer als oder gleich ist wie der alte rgt-Wert +2...
		
					if(substr($pagesList, strlen($pagesList)-7, 5) == "</li>") { // Falls schon ein li-Tag am Ende des Menues ist,...
						$closeList = '</ul>'."\r\n"; // ...Liste schließen-Tag einfügen
					}
					else					
						$closeList = '</li></ul>'."\r\n"; // Sonst Listenpunkt und Liste schließen
						
					$pagesList .= $closeList; // Schließen-Tags anhängen
					$level--; // Dekrement
				}
				
				$pagesList .= 		'<li id="sortid' . $pageId . '" class="listItem pageListItem';
				
				$pagesList .= $menuLevel % 2 ? '' : ' alternateList';
				$pagesList .= $row['group'] == "admin" ? ' admin' : ''; // Falls Seite nur für Admin erlaubt
				$validTitle = parent::replaceSpecialChars($title); // Sonderzeichen im Titel entfernen
				$validTitle = $validTitle == "" ? array(0 => "") : $validTitle; // Falls kein Seitenname angegeben ist, den Alias verwenden
				$pagesList .= ' ' . (is_numeric($validTitle[0]) ? '0-9' : strtoupper($validTitle[0])); // Anfangsbuchstaben des Titels hinzufügen, Zwecks Sortierung
				
				$pagesList .= 		'" data-menu="context" data-target="contextmenu-' . $items . '-a' . $i . ',contextmenu-' . $items . '-b' . $i . ',contextmenu-' . $items . '-c' . $i . '" data-itemcount="' . $i . '">' . "\r\n";
				
				// Mark-Box
				if($type == "edit")
					$pagesList .= $markBox;
	
				$pagesList .= 	'<span class="buttons-pagecontrol editButtons-panel panel-inline panel-left" data-id="contextmenu-' . $items . '-a' . $i . '">' . "\r\n";
				
				
				// Seitenstatus (online)
				// Falls nur Icon
				if($editAccess) {
					$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/listPages.php?page=admin&edit_id=' . $pageId . '&online=' . ($online ? 0 : 1),
											"class"		=> "pageStatus button-icon-only button-small",
											"title"		=> ($online ? 'online' : 'offline'),
											"icon"		=> ($online ? 'online' : 'offline'),
											"attr"		=> 'data-alttitle="' . ($online ? 'offline' : 'online') . '" data-status="' . ($online ? 1 : 0) . '" data-menuitem="true" data-id="item-id-' . $i . '"'
										);
					
					$pagesList .=	parent::getButtonLink($btnDefs);
				}
				else {
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> "pageStatus button-icon-only button-small",
											"title"		=> ($online ? 'online' : 'offline'),
											"icon"		=> ($online ? 'online' : 'offline'),
											"attr"		=> 'disabled="disabled" data-contextmenuitem="false"'
										);
					
					$pagesList .=	parent::getButton($btnDefs);
				}
				
				// Gruppenzugehörigkeit
				$previewHref	= HTML::getLinkPath($pageId, "editLang", true, true);
				
				$btnDefs	= array(	"href"		=> $previewHref,
										"class"		=> "pageAccess userGroupIcon button-icon-only button-small",
										"title"		=> '{s_title:' . (in_array("public", $group) ? 'publicpage}' : 'grouppage}' . implode(", ", $group)),
										"icon"		=> (in_array("public", $group) ? '' : 'admin') . 'user',
										"attr"		=> 'data-contextmenuitem="false"'
									);
				
				$pagesList .=	parent::getButtonLink($btnDefs);
				
				// Seitenvorschau
				$btnDefs	= array(	"href"		=> $previewHref,
										"class"		=> "pagePreview button-icon-only button-small",
										"title"		=> '{s_title:pagepreview}',
										"icon"		=> 'preview' . ($noAccess == false ? '' : 'no'),
										"attr"		=> 'target="_blank" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:pagepreview}"'
									);
				
				$pagesList .=	parent::getButtonLink($btnDefs);
				
				// Cache aktualisieren
				if(CACHE && $type == "edit" && $pageId > 0) {
				
					$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=cache&id=' . $pageId,
											"class"		=> "refreshCache button-icon-only button-small",
											"title"		=> '{s_notice:cache}',
											"icon"		=> 'cache',
											"attr"		=> 'data-action="refreshcache" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_notice:cache}"'
										);
					
					$pagesList .=	parent::getButtonLink($btnDefs);
				}
				
				elseif(CACHE && $type == "edit") {
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> "iconCacheNo button-icon-only button-small",
											"title"		=> '{s_title:nocache}',
											"icon"		=> 'cacheno',
											"attr"		=> 'disabled="disabled" data-contextmenuitem="false"'
										);
					
					$pagesList .=	parent::getButton($btnDefs);
				}
				$pagesList .= 	'</span>' . "\r\n";
				
				// Seitentitel
				$pagesList .= 		'<span class="pageTitle" title="{s_label:title} {s_label:title2}">' . $title . $index . '</span>' . "\r\n";
				
				// Toggle submenu, falls vorhanden, anzeigen
				if($lft < $rgt-1) {
					
					$pagesList .= 	'<span class="toggle-panel editButtons-panel panel-inline panel-left">';
					
					// Button toggle
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> "toggleSubList button-icon-only",
											"title"		=> '{s_title:toggle}',
											"icon"		=> 'toggle'
										);
					
					$pagesList .=	parent::getButton($btnDefs);
					
					$pagesList .= 	'</span>' . "\r\n";
				}
				
				// page-ID einfügen
				$pagesList .= 		'<span class="pageID" title="page ID #' . $pageId . '">#' . $pageId . '</span>' . "\r\n";

				
				// Button panel
				$editButtons	= 	'<span class="editButtons-panel" data-id="contextmenu-' . $items . '-b' . $i . '">' . "\r\n";

				
				// Wenn eine Liste zum Anlegen neuer Seiten angezeigt werden soll
				if($type == "new") {
				
					// Button pastebelow
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pasteBelow newItem button-icon-only',
											"title"		=> '{s_title:new1a} &quot;' . $title . '&quot; {s_title:new1b}',
											"attr"		=> 'data-action="newpage" data-value="' . $pageId . '" data-type="new_below" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:new1a}"',
											"icon"		=> "pastebelow"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					// Button pastechild
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pasteChild newItem button-icon-only',
											"text"		=> "",
											"title"		=> '{s_title:new2a} &quot;' . $title . '&quot; {s_title:new1b}',
											"attr"		=> 'data-action="newpage" data-value="' . $pageId . '" data-type="new_child" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:new2a}"',
											"icon"		=> "pastechild"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .= 	'</span>' . "\r\n";
				}

				
				// Wenn eine Sortierliste angezeigt werden soll
				elseif($type == "sort") {
				
					// Button cut
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'cutPageBranch button-icon-only',
											"text"		=> "",
											"title"		=> '{s_title:sort1a} &quot;' . $title . '&quot; {s_title:sort3a}',
											"attr"		=> 'data-action="cutpagebranch" data-pageid="' . $pageId . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/sortPages_trans.php?page=admin" data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "cut"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					// Button pastebelow
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pasteBelow button-icon-only',
											"text"		=> "",
											"title"		=> '{s_javascript:addpagebelow} &quot;' . $title . '&quot; {s_title:sort3a}',
											"attr"		=> 'data-pageid="' . $pageId . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/sortPages_trans.php?page=admin" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_javascript:addpagebelow}" style="display:none;"',
											"icon"		=> "pastebelow"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					// Button pastechild
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pasteChild button-icon-only',
											"text"		=> "",
											"title"		=> '{s_javascript:addpageaschild} &quot;' . $title . '&quot; {s_title:sort3a}',
											"attr"		=> 'data-pageid="' . $pageId . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/sortPages_trans.php?page=admin" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_javascript:addpageaschild}" style="display:none;"',
											"icon"		=> "pastechild"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .= 	'<form action="' . ADMIN_HTTP_ROOT . '?task=sort" method="post">' . "\r\n";
					
					// Button sortdown
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "sortdown_id",
											"class"		=> 'sortDown oneSort button-icon-only',
											"value"		=> $pageId,
											"text"		=> "",
											"title"		=> '{s_title:sort1a} &quot;' . $title . '&quot;<br />{s_title:sort2a}',
											"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:sort2a}"',
											"icon"		=> "sortdown"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .= 	'<input type="hidden" name="sortdown_id" value="' . $pageId . '" />' . "\r\n" . 
										'</form>' . "\r\n";
					
					$editButtons .= 	'<form action="' . ADMIN_HTTP_ROOT . '?task=sort" class="adminfm1" method="post">' . "\r\n";
					
					// Button sortup
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "sortdown_id",
											"class"		=> 'sortUp oneSort button-icon-only',
											"value"		=> $pageId,
											"text"		=> "",
											"title"		=> '{s_title:sort1a} &quot;' . $title . '&quot;<br />{s_title:sort1b}',
											"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:sort1b}"',
											"icon"		=> "sortup"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .= 	'<input type="hidden" name="sortup_id" value="' . $pageId . '" />' . "\r\n" . 
										'</form>' . "\r\n";
										
					if($items > -1) {
					
						$editButtons .= '<form action="' . ADMIN_HTTP_ROOT . '?task=edit" method="post">' . "\r\n";
						
						// Button delete
						$btnDefs	= array(	"type"		=> "submit",
												"name"		=> "del_id",
												"class"		=> 'delPage button-icon-only',
												"value"		=> $pageId,
												"text"		=> "",
												"title"		=> '{s_title:sort1a} &quot;' . $title . '&quot; {s_title:del}',
												"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '"',
												"icon"		=> "delete"
											);
						
						$editButtons .=	parent::getButton($btnDefs);
						
						$editButtons .=	'<input type="hidden" name="del_id" value="' . $pageId . '" />' . "\r\n";				
						
						$editButtons .= '</form>' . "\r\n";
					}
					
					$editButtons .= 	'</span>' . "\r\n";
				}
	
	
				// Wenn eine Linkliste mit internen Links angezeigt werden soll
				elseif($type == "links") {
				
					$alias = HTML::getLinkPath($pageId, "editLang", false); // Link mit Pfad holen
				
					// Button link
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'link button-icon-only',
											"value"		=> '{#root}/' . $alias,
											"text"		=> "",
											"title"		=> $title . ' -> {s_title:link}',
											"icon"		=> "link"
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .= 	'<input type="hidden" value="' . $pageId . '" />' . "\r\n" . 
										'</span>' . "\r\n";
				}
	
	
				// Wenn eine Seitenliste vorhandener Inhaltsseiten angezeigt werden soll (zur Übernahme der Inhalte)
				elseif($type == "fetchcon") {
					
					$iconType	= "";
					$btnClass	= "";
					$btnAttr	= "";
					$btnTitle	= "";
					
					// Falls die Seite die aktuelle Seite ist, Nofetch-Icon einbinden
					if($pageId == $this->editId) {
						
						$iconType	= 'fetchno';
						$btnClass	= 'noAccess';
					}
					// Falls die Seite nicht nur für Admin zugänglich ist, Noaccess-Icon einbinden
					elseif($row['group'] == "admin" && $this->loggedUserGroup != "admin") {
						
						$iconType	= 'noaccess';
						$btnClass	= 'noAccess';
						$btnTitle	= "{s_title:grouppage}admin";
					}
					// Andernfalls Editieren und Löschen verbieten
					else {
							
						$iconType	= 'fetch';
						$btnClass	= 'fetchcon';
						$btnAttr	= 'data-action="fetchcon"';
						$btnTitle	= sprintf(ContentsEngine::replaceStaText('{s_title:fetch}'), '&quot;' . $title . '&quot;');
					}
					
					// Button fetchcon / noAccess
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> $btnClass . ' button-icon-only',
											"value"		=> $pageId,
											"text"		=> "",
											"title"		=> $btnTitle,
											"attr"		=> $btnAttr,
											"icon"		=> $iconType
										);
					
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .= 	'</span>' . "\r\n";
				}
	
	
				// Andernfalls Editliste anzeigen
				else {
					
					$sortButtons	= 		'<form action="' . ADMIN_HTTP_ROOT . '?task=edit" class="adminfm2" method="post">' . "\r\n";
					
					// Button sortdown
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "sortdown_id",
											"class"		=> 'sortDown oneSort button-icon-only',
											"value"		=> $pageId,
											"text"		=> "",
											"title"		=> '{s_title:sort1a} &quot;' . $title . '&quot;<br />{s_title:sort2a}',
											"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:sort2a}"',
											"icon"		=> "sortdown"
										);
					
					$sortButtons	.=	parent::getButton($btnDefs);
				
					$sortButtons	.= 		'<input type="hidden" name="sortdown_id" value="' . $pageId . '" />' . "\r\n" . 
											'</form>' . "\r\n";
					
					$sortButtons	.= 	'<form action="' . ADMIN_HTTP_ROOT . '?task=edit" class="adminfm1" method="post">' . "\r\n";
					
					// Button sortup
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "sortdown_id",
											"class"		=> 'sortUp oneSort button-icon-only',
											"value"		=> $pageId,
											"text"		=> "",
											"title"		=> '{s_title:sort1a} &quot;' . $title . '&quot;<br />{s_title:sort1b}',
											"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:sort1b}"',
											"icon"		=> "sortup"
										);
					
					$sortButtons	.=	parent::getButton($btnDefs);
											
					$sortButtons	.=		'<input type="hidden" name="sortup_id" value="' . $pageId . '" />' . "\r\n" . 
											'</form>' . "\r\n";
											
					
					// Falls die Seite nur für bestimmte Benutzergruppen bzw. Admin zugänglich ist, Noaccess-Icon einbinden
					if($editAccess == false) {
						
						// Button noAccess
						$btnDefs	= array(	"type"		=> "button",
												"class"		=> 'noAccess button-icon-only',
												"value"		=> $pageId,
												"text"		=> "",
												"title"		=> '{s_title:grouppage}admin',
												"icon"		=> 'noaccess'
											);
						
						$noAccessIcon =	parent::getButton($btnDefs);
						
						$editButtons .= 	$noAccessIcon . $sortButtons . $noAccessIcon;	
					}
					
					// Andernfalls Editieren und Löschen verbieten
					else {
						
						// Button edit
						$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=edit&edit_id=' . $pageId,
												"class"		=> 'noAccess button-icon-only',
												"text"		=> "",
												"title"		=> '{s_title:editpage} &quot;' . $title . '&quot;',
												"attr"		=> 'data-ajax="true" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:edit}"',
												"icon"		=> 'edit'
											);
						
						$editButtons .=	parent::getButtonLink($btnDefs);

						
						if($items > -1) {
						
							$editButtons .= $sortButtons;
							
							$editButtons .=	'<form action="" class="adminfm2" method="post">' . "\r\n";
							
							// Button delete
							$btnDefs	= array(	"type"		=> "submit",
													"name"		=> "del_id",
													"class"		=> 'delPage button-icon-only',
													"value"		=> $pageId,
													"text"		=> "",
													"title"		=> '{s_title:sort1a} &quot;' . $title . '&quot; {s_title:del}',
													"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:del}"',
													"icon"		=> "delete"
												);
							
							$editButtons .=	parent::getButton($btnDefs);
							$editButtons .=	'<input type="hidden" name="del_id" value="' . $pageId . '" />' . "\r\n" .
											'</form>' . "\r\n";
						}
					}
					
					$editButtons .= 		'</span>' . "\r\n";
					
				}


				// Falls Änderungen bestehen, die übernommen werden können, Buttons einbinden
				if(in_array($alias, $this->diffConAlias) && $type == "edit" && !$noAccess) {
				
					$changesButtons =	'<span class="changesButtons-panel editButtons-panel panel-inline panel-right" data-id="contextmenu-' . $items . '-c' . $i . '">' . "\r\n";
					
					// Button apply
					$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=1&edit_id=' . $pageId . '&edit=0',
											"class"		=> "goLive change button-icon-only button-small",
											"text"		=> "",
											"title"		=> "{s_link:changes}",
											"icon"		=> "apply",
											"attr"		=> 'data-action="applychanges" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_link:changes}" data-id="item-id-' . $i . '"'
										);
					
					$changesButtons	.=	parent::getButtonLink($btnDefs);
					
					// Button cancel
					$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=0&edit_id=' . $pageId . '&edit=0',
											"class"		=> "cancel button-icon-only button-small",
											"text"		=> "",
											"title"		=> "{s_link:nochanges}",
											"icon"		=> "cancel",
											"attr"		=> 'data-action="discardchanges" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_link:nochanges}" data-id="item-id-' . $i . '"'
										);
				
					$changesButtons	.=	parent::getButtonLink($btnDefs);
					
					$changesButtons .=	'</span>' . "\r\n";
					
					$pagesList .= $changesButtons;
				}
				
				
				// EditButtons einfügen
				$pagesList .= $editButtons;
				
				
				$pagesList .=		'<br class="clearfloat">'."\r\n";
				
				if($lft < $rgt-1)
					$pagesList .=	'<ul style="display:none;">'."\r\n"; // Wenn der lft-Wert kleiner ist als der rgt-Wert -1, beginnt eine neue Liste
				else
					$pagesList .=	'</li>'."\r\n";
			
				} // Ende is_numeric($items)			
				
				
				// Falls Templateauflistung
				else {
					
					$pageId = $items;
					$lft	= 1;
					$rgt	= 0;
					
					// Falls das Template nicht das aktuelle Template ist, Template auflisten
					if($pageId != $this->editId || $row != $this->editTplArea) {
						
						$pagesList .= 		'<li id="sortid' . $i . '" title="page ID #' . $pageId . '" class="listItem pageListItem ' . (is_numeric($pageId[0]) ? '0-9' : strtoupper($pageId[0])) . '">' . "\r\n" .
											'<span class="listIcon">' . "\r\n" .
											parent::getIcon('area-' . $row, "listIcon-tpl") .
											'</span>' . "\r\n" .
											'<span class="pageTitle">' . $pageId . '</span>' . "\r\n";
						
						$pagesList .= 		'<span class="editButtons-panel">' . "\r\n";
						
						// Button fetchcon
						$btnDefs	= array(	"type"		=> "button",
												"class"		=> 'fetchcon button-icon-only',
												"value"		=> $pageId . '_FID_' . $row,
												"text"		=> "",
												"title"		=> sprintf(ContentsEngine::replaceStaText('{s_title:fetch}'), '&quot;' . $pageId. ' - ' . $row . '&quot;'),
												"attr"		=> 'data-action="fetchcon"',
												"icon"		=> 'fetch'
											);
						
						$pagesList .=	parent::getButton($btnDefs);
						
						$pagesList .= 		'</span>' . "\r\n" .
											'<span class="pageID" title="page ID #' . $pageId . '">{#' . $row . '}</span>' . "\r\n";
						
						$pagesList .= 		'<br class="clearfloat">'."\r\n";
					}
				}
			
				$i++;
				
			} // Ende foreach
			
			while($menuLevel > 1) { // Solange der Menulevel noch nicht auf 1 ist...
		
				if(substr($pagesList, strlen($pagesList)-7, 5) == "</li>") // Falls schon ein li-Tag am Ende des Menues ist,...
					$pagesList .= '</ul>'."\r\n"; // ...Liste schließen-Tag einfügen
				else					
					$pagesList .= '</li></ul>'."\r\n"; // Sonst Listenpunkt und Liste schließen
					
				$menuLevel--;
				
			}
	
			$pagesList .= '</ul>' . "\r\n";
			
		} // Ende if query == true
		
		else {
			
			// Falls neu und leere Liste
			if($type == "new") {
				$pagesList .= 	'<ul class="editList pageList list list-condensed">' . "\r\n";  // ... eine leere Liste mit Option zum Einfügen anzeigen
				$pagesList .= 	'<li class="newFirstEntry listItem pageListItem">' . "\r\n" . 
								'<span class="editButtons-panel">' . "\r\n";
				
				// Button new
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'newPage button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:newpage}',
										"attr"		=> 'data-action="newpage" data-type="new_first" data-value="' . $items . '"',
										"icon"		=> "new"
									);
				
				$pagesList .=	parent::getButton($btnDefs);
								
				$pagesList .=	'</span>' . "\r\n" . 
								'<br class="clearfloat"></li>' . "\r\n" . 
								'</ul>' . "\r\n";
	
			}
			
			// Falls sort und leere Liste
			if($type == "sort") { // Wenn eine Sortierliste angezeigt werden soll und keine Einträge für das Menü gefunden wurden...
				$pagesList .= 	'<ul class="editList pageList list list-condensed">' . "\r\n";  // ... eine leere Liste mit Option zum Einfügen anzeigen
				$pagesList .= 	'<li class="sortFirstEntry listItem pageListItem">' . "\r\n" . 
								'<span class="editButtons-panel" style="display:none;">' . "\r\n";
					
				// Button new
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'pasteBelow newItem button-icon-only',
										"text"		=> "",
										"title"		=> '{s_javascript:addpagebelow}',
										"attr"		=> 'data-newpos="' . $items . '" data-pageid="new" data-type="pasteBelow"',
										"icon"		=> "pastebelow"
									);
				
				$pagesList .=	parent::getButton($btnDefs);
								
				$pagesList .=	'</span>' . "\r\n" . 
								'<br class="clearfloat"></li>' . "\r\n" . 
								'</ul>' . "\r\n";
								
			}
		}
	

		return $pagesList;
	
	} // Ende listPages
	


	/**
	 * Methode zum Generieren einer Box mit Sortier-, Auswahl- und Suchfunktion für Mediadaten
	 * 
	 * @param	string $sortType Art der Sortierungsoptionen (default = 'date')
	 * @param	string $fieldPreset Feld, das vorbelegt werden soll (default = '')
	 * @param	string $value Wert für die Vorbelegung eines Feldes (default = '')
	 * @param	boolean $markBox falls true, Checkbox zum Markieren hinzufügen (default = false)
	 * @param	boolean $newFolder falls true, Inputfeld zum Anlegen eines neuen Ordners hinzufügen (default = false)
	 * @param	boolean $switchFolder falls true, Button zum Umschalten zwischen Default- und files-Ordner hinzufügen (default = false)
	 * @param	boolean $fileUpload	falls true, Button für File-Upload hinzufügen (default = false)
	 * @param	string $type Typ (default = '')
	 * @param	string $folder Ordner (default = '')
	 * @access	public
	 * @return	string
	 */
	public function getControlBar($sortType = "date", $fieldPreset = "", $value = "", $markBox = false, $newFolder = false, $switchFolder = false, $fileUpload = false, $type = "all", $folder = "")
	{
	
		$listOrder	= "";
		$output		= "";
			
		$output .=	'<div class="controlBar">' . "\r\n" .
					'<div class="fullBox">' . "\r\n" .
					'<div class="labelBox left"><label>{s_common:show}</label>' . "\r\n" .
					'<select name="listFilter" class="listFilter">' . "\r\n" .
					'<option value="all">{s_common:all}' . ($this->mediaCount != "" ? ' (' . $this->mediaCount . ')' : '') . '</option>' . "\r\n" .
					'<option>0-9</option>' . "\r\n";
					
		$alphabet = range('A', 'Z');
		
		foreach($alphabet as $letter) {
			$output .=	'<option>' . $letter . '</option>' . "\r\n";
		}
		
		$output .=	'</select></div>' . "\r\n";
		
		// Falls angegeben, Sortierungsoptionen einbinden
		if($sortType != "none") {
				
			$output .=	'<div class="labelBox left"><label>{s_label:orderby}</label>' . 	
						'<select name="listOrder" class="listOrder">' . "\r\n" . 
						'<option value="nameasc"' . ($listOrder == "nameasc" ? ' selected="selected"' : '') . '>{s_option:nameasc}</option>' . "\r\n" . 
						'<option value="namedsc"' . ($listOrder == "namedsc" ? ' selected="selected"' : '') . '>{s_option:namedsc}</option>' . "\r\n";
			
			if($sortType == "date")
				$output .=	'<option value="dateasc"' . ($listOrder == "dateasc" ? ' selected="selected"' : '') . '>{s_option:dateasc}</option>' . "\r\n" . 
							'<option value="datedsc"' . ($listOrder == "datedsc" ? ' selected="selected"' : '') . '>{s_option:datedsc}</option>' . "\r\n";
			elseif($sortType == "id")
				$output .=	'<option value="idasc"' . ($listOrder == "idasc" ? ' selected="selected"' : '') . '>{s_option:idasc}</option>' . "\r\n" . 
							'<option value="iddsc"' . ($listOrder == "iddsc" ? ' selected="selected"' : '') . '>{s_option:iddsc}</option>' . "\r\n";
				
			$output .=	'</select></div>' . "\r\n";
		}
		
		// Suchfunktion
		$output .=	'<div class="labelBox left"><label>{s_label:searchfor}</label>' .
					'<span class="singleInput-panel">' . PHP_EOL .
					'<input type="text" name="listSearch" class="listSearch input-button-right" value="' . ($fieldPreset == "search" ? htmlspecialchars($value) . '"' : '"') . ' />' . "\r\n" .
					'<span class="editButtons-panel">' . "\r\n";

		// Button new
		$btnDefs	= array(	"type"		=> "button",
								"name"		=> "reset_search_val",
								"class"		=> 'resetSearchVal button-icon-only',
								"value"		=> "true",
								"text"		=> "",
								"title"		=> '{s_label:reset}',
								"icon"		=> "close"
							);
		
		$output .=	parent::getButton($btnDefs);
								
		$output .=	'</span>' . "\r\n" .
					'</span>' . "\r\n" .
					'</div>' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n" .
					'</div>' . "\r\n";
		
		// Checkbox zur Mehrfachauswahl im Adminbereich
		if($markBox) {
		
			$output .=	'<form action="" method="post">' .
						'<div class="actionBox">' .
						'<label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" /></label>' .
						'<label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' .
						'<span class="editButtons-panel">' . "\r\n";
			
			// Button delete
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'delAll delFiles button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:delmarked}',
									"attr"		=> 'data-action="delmultiplefiles"',
									"icon"		=> "delete"
								);
			
			$output .=	parent::getButton($btnDefs);
			
			$output .=	'</span>' . "\r\n" .
						'</div>' .
						'</form>';
		}
		
		// Bei default-Foldern (Bilder, Dokumente, etc.) einen Button zum Umschalten zwischen Default- und files-Ordner einfügen
		if($switchFolder) {
		
			$output .=	'<div class="openFolderIcons">' . "\r\n";
			
			// Button switchfolder
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=list&type=files&folder=',
									"class"		=> "changeDefaultFolder button-icon-only",
									"text"		=> "",
									"title"		=> "{s_title:switchfolder}",
									"attr"		=> 'data-type="files"',
									"icon"		=> "switchdir"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);
					
			// Buttons für weitere Medienordner
			
			// Button image
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=list&type=images&folder=',
									"class"		=> "openDefaultFolder changeDefaultFolder button-icon-only",
									"text"		=> "",
									"title"		=> "{s_button:imgfolder}",
									"attr"		=> 'data-type="images"',
									"icon"		=> "image"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);
			
			// Button docs
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=list&type=doc&folder=',
									"class"		=> "openDefaultFolder changeDefaultFolder button-icon-only",
									"text"		=> "",
									"title"		=> "{s_button:docfolder}",
									"attr"		=> 'data-type="doc"',
									"icon"		=> "doc"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);
			
			// Button video
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=list&type=video&folder=',
									"class"		=> "openDefaultFolder changeDefaultFolder button-icon-only",
									"text"		=> "",
									"title"		=> "{s_button:videofolder}",
									"attr"		=> 'data-type="video"',
									"icon"		=> "video"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);
			
			// Button flash
			/*
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=list&type=flash&folder=',
									"class"		=> "openDefaultFolder changeDefaultFolder button-icon-only",
									"text"		=> "",
									"title"		=> "{s_button:flashfolder}",
									"attr"		=> 'data-type="flash"',
									"icon"		=> "flash"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);
			*/
			
			// Button audio
			$btnDefs	= array(	"href"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=list&type=audio&folder=',
									"class"		=> "openDefaultFolder changeDefaultFolder button-icon-only",
									"text"		=> "",
									"title"		=> "{s_button:audiofolder}",
									"attr"		=> 'data-type="audio"',
									"icon"		=> "audio"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);

			$output .=	'</div>' . "\r\n";
		}
						
		// Ggf. File-Upload-Maske einfügen
		if($fileUpload) {
			require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Media.php"; // Media einbinden
			$output .=	Media::getListBoxUploadMask($type, $newFolder, true, $folder, Files::getUploadMethod(), $this->DB, $this->o_lng);
		}
		// Button zum Anlegen eines neuen Ordners
		elseif($newFolder) {
			require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Media.php"; // Media einbinden
			$output .=	Media::getNewFolderMask();
		}
		
		$output .=	'<script type="text/javascript">' . "\n" .
					'head.ready("jquery",function(){'."\r\n".
						'$(document).ready(function(){' .
							'if(!head.mobile){$(".listSearch").focus();}' .
							'$(".resetSearchVal").bind("click", function(){
								$(this).closest(".singleInput-panel").children(".listSearch").val("").focus().trigger("keyup").blur();
							});' .
						'});' . "\n" .
					'});' . "\n" .
					'</script>' . "\n";
			
		$output .=	'</div>' . "\r\n";
		
		return $output;
		
	}
	


	/**
	 * Öffnet den #adminContent div
	 * 
	 * @access	public
	 * @return	string
	 */
	public function openAdminContent()
	{
	
		return  '<div id="adminContent">' . PHP_EOL . (!$this->ajax ? '<!-- begin #adminContent -->' . PHP_EOL : '');
	
	}
	


	/**
	 * Schließt den #adminContent div
	 * 
	 * @access	public
	 * @return	string
	 */
	public function closeAdminContent()
	{
	
		return (!$this->ajax ? '<!-- end #adminContent -->' . PHP_EOL : '') . '</div>' . PHP_EOL;
	
	}
	


	/**
	 * Close a tag
	 * 
	 * @access	public
	 * @param	string $id tag id (default = "")
	 * @param	string $id tag type (default = "div")
	 * @return	string
	 */
	public function closeTag($id = "", $tag = "div")
	{
	
		return '</' . htmlspecialchars($tag) . '>' . PHP_EOL . '<!-- end ' . htmlspecialchars($id) . ' -->' . PHP_EOL;
	
	}
	


	/**
	 * Methode zur Überprüfung auf gültige Seitentiteln
	 * 
	 * @param	string $newTitle zu überprüfender Seitentitel (title)
	 * @param	boolean $slash true, falls Slash erlaubt sein soll (default = false)
	 * @param	boolean $punctMarks true, falls Satzzeichen erlaubt sein sollen (default = false)
	 * @access	public
	 * @return	boolean
	 */
	public function validateTitle($newTitle, $slash = false, $punctMarks = false)
	{
		
		$slashPattern	= "";
		$punctPattern	= "";
		
		// Slash
		if($slash)
			$slashPattern = "\/";
		
		// Satzzeichen
		if($punctMarks)
			$punctPattern = "\.\,;\!\?()";
		
		if($newTitle != "" && preg_match("/^[0-9\pL]+[" . $slashPattern . $punctPattern . "0-9 \pL'&+_-]+$/u", $newTitle) && !preg_match("/\\\/", $newTitle) && !preg_match("/^[anpi][0-9]+$/", $newTitle))
			return true;
		
		else // Falls ein falscher Begriff eingegeben wurde		
			return false;
		
	}
	


	/**
	 * Methode zur Überprüfung auf gültige db-Tabellennamen
	 * 
	 * @param	string $newTitle zu überprüfender Seitentitel (title)
	 * @access	public
	 * @return	boolean
	 */
	public function validateDbTableName($tabName)
	{
		
		// Gültiger Tabellenname
		if($tabName != "" && preg_match("/^[a-zA-Z0-9_-]+$/", $tabName))
			return true;		
		else
			return false;
		
	}



	/**
	 * Methode zur Überprüfung, Umschreibung und Ausgabe von Aliasen
	 * 
	 * @param	string	$newItem aktuelle Seite (alias)
	 * @param	string	$newPageId aktuelle Seite (page_id)
	 * @param	string	$otherLang aktuelle Sprache
	 * @param	boolean	$check Überprüfung des Alias auf Duplicate (default = true)
	 * @access	public
	 * @return	string
	 */
	public function getAlias($newItem, $newPageId, $otherLang, $check = true)
	{
		
		$search = array('ä', 'ö', 'ü', 'ß', 'é', 'è', 'à', 'â', 'ô', 'ç', ' & ', '&', '+', ' ', '\'', '/', '(', ')', '–');
		$replace = array('ae', 'oe', 'ue', 'ss', 'e', 'e', 'a', 'a', 'o', 'c', '-', '-', '-', '-', '-', '-', '-', '-', '-');
		$newItem = str_ireplace($search, $replace, $newItem); // Ersetzen der Umlaute
		$query = "";
		
		if($newItem != "" && (!preg_match("/^[a-zA-Z0-9_-]+$/", $newItem) || preg_match("/^[anpi][0-9]+$/", $newItem) || strpos($newItem, "_") === 0)) // Falls ein falscher Begriff eingegeben wurde				
			
			return false;
		
		
		$this->newItem = $newItem;
				
		if($check == true) {

			$newItem = $this->DB->escapeString($newItem);
		
			// Datenbanksuche nach evtl. bereits vorhandenen titles
			$query = $this->DB->query("SELECT `id` 
											FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
											WHERE alias_" .  $otherLang . " = " . "'$newItem'". " 
											AND page_id != " . $newPageId . " 
											");
		
		
			#var_dump($query);
			
			if($query == true) {
				$newItem = $newItem . "-";
				self::checkAlias($newItem, $newPageId, $otherLang);
			}
			
		#var_dump(print_r($query) . "new:" . $this->newItem);
		}

			
		return $this->newItem;
		
	}



	/**
	 * Methode zur Überprüfung auf Vorhandensein von Aliasen
	 * 
	 * @param	string $newItem aktuelle Seite (alias)
	 * @param	string $newPageId aktuelle Seite (page_id)
	 * @param	string $otherLang Sprache die überprüft werden soll
	 * @access	public
	 * @return	string
	 */
	public function checkAlias($newItem, $newPageId, $otherLang)
	{
			
		$this->newAlias = $newItem . str_pad($this->counter, 2 ,'0', STR_PAD_LEFT); // ggf. Zahl hinzufügen bei Duplikat
		
		// Datenbanksuche nach evtl. bereits vorhandenen titles
		$query = $this->DB->query("SELECT *
										FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
										WHERE `alias_" .  $otherLang . "` = '$this->newAlias' 
										AND `page_id` != " . $newPageId . " 
										");
		
		if($query == true) {
			#var_dump($newPageId);
			$this->counter++;
			$return = self::checkAlias($newItem, $newPageId, $otherLang);
		}

		else {
			$this->counter = 1;

			$this->newItem = $this->newAlias;
			$return = $this->newItem;
			
		}
		
		return $return ;
		
	}


	
	/**
	 * Bestimmen der Inhaltstabellen
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getContentPreviewTables() 
	{

		$previewTables	= array();
		
		foreach (self::$contentTables as $key => $tab){
		
			$previewTables[] = $tab . "_preview";
		}
		
		return $previewTables;
	
	}
	
	
	
	/**
	 * Methode zur Übernahme von Änderungen an Inhaltselementen
	 * 
	 * @param	$affect 		string Ändern/verwerfen: wenn 'all', alle Änderungen, wenn 1 Änderungen für Seite/Template übernehmen (default = none)
	 * @param	$changesTarget	string page_id des Inhaltselements oder Templatename (default = '')
	 * @param	$changesTPL		string content_area des Templates (default = '')
	 * @access	public
	 * @return	boolean/string
	 */
	public function applyConChanges($affect = "none", $changesTarget = "", $changesTPL = "")
	{
	
		foreach($this->diffConTables as $tableName => $conTable) {
		
			// Falls Änderungen übernommen werden sollen
			if($affect == "all" || $affect == "1") {
				$tableOld = $tableName;
				$tableNew = $tableName . "_preview";
			}
			// Andernfalls Änderungen verwerfen
			else {
				$tableOld = $tableName . "_preview";
				$tableNew = $tableName;
			}
			
			$tableOld		= DB_TABLE_PREFIX . $tableOld;
			$tableNew		= DB_TABLE_PREFIX . $tableNew;
			$tableHistory	= DB_TABLE_PREFIX . $tableName . "_history";
			$tableSearch	= DB_TABLE_PREFIX . "search";
			
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . $tableOld . "`, `" . $tableNew . "`, `" . $tableSearch . "`");
	
	
			foreach($conTable as $content) {
			
				// Falls alle Seiten/Templates oder nur ein bestimmtes betroffen ist/sind
				if($changesTarget == "" 
				||($changesTarget == $content 
				&&($changesTPL == "" || $changesTPL == $tableName))
				) {
						
					// Spaltenzahl anpassen (überflüssige Spalten löschen)
					$contentNumber = parent::getConNumber(DB_TABLE_PREFIX . $tableName);
					
					
					// Ggf. History Tabelle anlegen/Spalten
					$this->setupHistoryTable($tableHistory, $contentNumber);
					
					// Ggf. History Tabelle insert
					$this->insertHistory($tableHistory, $tableOld, $content, $contentNumber);
					
	
					// Temp-Tabelle anlegen
					$createSQL = $this->DB->query("CREATE TABLE `" . $tableOld . "_temp` ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
														");
							
					// In Temp-Tabelle Daten zwischenspeichern
					$insertSQL1 = $this->DB->query("INSERT INTO `" . $tableOld . "_temp` 
														SELECT * FROM `" . $tableOld . "` 
														WHERE `page_id` = '$content'
														");
							
					// Daten aus contents_xyz löschen
					$deleteSQL1 = $this->DB->query("DELETE FROM `" . $tableOld . "` 
														WHERE `page_id` = '$content'
														");
							
					// Daten aus contents_xyz_preview einfügen
					$insertSQL2 = $this->DB->query("INSERT INTO `" . $tableOld . "` 
														SELECT * FROM `" . $tableNew . "` 
														WHERE `page_id` = '$content'
														");
					
					// Falls Insert aus _preview scheitert, Daten aus _temp wieder einfügen
					if($insertSQL2 !== true)
						$insertSQL1a = $this->DB->query("INSERT INTO `" . $tableOld . "` 
															SELECT * FROM `" . $tableOld . "_temp` 
															WHERE `page_id` = '$content'
															");
						
					// _temp löschen
					$deleteSQL2 = $this->DB->query("DROP TABLE `" . $tableOld . "_temp` 
														");
					
					
					// Datenbanksuche zur Best. der Maximalen Anzahl an Spalten
					$queryMaxConOld = $this->DB->query("SELECT `type-con" . $contentNumber . "`  
														  FROM `" . $tableOld . "` 
														  WHERE `type-con" . $contentNumber . "` != NULL OR `type-con" . $contentNumber . "` != '' 
														  ");
	
					// Datenbanksuche zur Best. der Maximalen Anzahl an Spalten
					$queryMaxConNew = $this->DB->query("SELECT `type-con" . $contentNumber . "`  
														  FROM `" . $tableNew . "` 
														  WHERE `type-con" . $contentNumber . "` != NULL OR `type-con" . $contentNumber . "` != '' 
														  ");
					
					
					// Falls keine Inhalte mehr unter der Spalte (sowohl in Tabelle als auch in Tabelle_preview) zu finden sind, die Spalte löschen
					if(count($queryMaxConOld) == 0
					&& count($queryMaxConNew) == 0
					) {
						
						$dropCol = "";
						
						foreach($this->o_lng->installedLangs as $lang) { // Inhalte nach Sprache auslesen und updaten
				
							$dropCol .= "DROP `con" . $contentNumber . "_" . $lang . "`,";
						}
					
						$dropCol .= "DROP `type-con" . $contentNumber . "`,";
						$dropCol .= "DROP `styles-con" . $contentNumber . "` ";
					
						// Spalte löschen
						$alterSQL1 = $this->DB->query("ALTER TABLE `" . $tableOld . "` " . $dropCol . "
														");
					
						// Spalte löschen
						$alterSQL2 = $this->DB->query("ALTER TABLE `" . $tableNew . "` " . $dropCol . "
														");
					
						#var_dump($alterSQL1);
						
						$contentNumber--;
					}
													
					// Falls gerade die contents_main-Tabelle durchlaufen wird, den Suchindex aktualisieren
					if($tableName == $GLOBALS['tableContents'] && is_numeric($content) && $content > 0) {
						
						$searchIndex = array();
						
						foreach($this->o_lng->installedLangs as $lang) { // Indexvar für Sprachen
							$searchIndex[$lang] = "";
						}
						
						// Datenbanksuche zur Best. der Maximalen Anzahl an Spalten
						$querySearchIndex = $this->DB->query("SELECT * 
																	  FROM `" . $tableNew . "` 
																	  WHERE `page_id` = '$content'
																	  ");
						#var_dump($contentNumber);
						
						for($i = 1; $i <= $contentNumber; $i++) {
							
							if(isset($querySearchIndex[0]['type-con' . $i]) && $querySearchIndex[0]['type-con' . $i] == "text") {
						
								foreach($this->o_lng->installedLangs as $lang) { // Inhalte nach Sprache auslesen
									$searchIndex[$lang] .= $querySearchIndex[0]['con' . $i . '_' . $lang];
								}
							}
						}
						
						foreach($this->o_lng->installedLangs as $lang) { // Inhalte nach Sprache auslesen
						
							$searchIndex[$lang] = str_replace("><", ">\n<", $searchIndex[$lang]);
							$searchIndex[$lang] = str_replace(array("&nbsp;", "<br />", "<br>"), "\n", $searchIndex[$lang]);
							$searchIndex[$lang] = strip_tags(html_entity_decode($searchIndex[$lang], ENT_QUOTES, 'UTF-8'));
							$searchIndex[$lang] = str_replace("\t", "\s", $searchIndex[$lang]);
							$searchIndex[$lang] = str_replace("\r\n", "\n", $searchIndex[$lang]);
							while(strpos($searchIndex[$lang], "\s\s") !== false) {
								$searchIndex[$lang] = str_replace("\s\s", "\s", $searchIndex[$lang]);
							}
							$searchIndex[$lang] = str_replace("\n\s\n", "\n", $searchIndex[$lang]);
							while(strpos($searchIndex[$lang], "\n\n") !== false) {
								$searchIndex[$lang] = str_replace("\n\n", "\n", $searchIndex[$lang]);
							}
							if(strpos($searchIndex[$lang], "\n") === 0) {
								$searchIndex[$lang] = substr($searchIndex[$lang], 1);
							}
							
							// Daten aus contents_xyz_preview einfügen
							$insertSQL3 = $this->DB->query("INSERT INTO `" . $tableSearch . "` 
																 SET `page_id` = '$content',
																	 `con_" . $lang . "` = '" . $searchIndex[$lang] . "' 
																 ON DUPLICATE KEY UPDATE 
																 `con_" . $lang . "` = '" . $searchIndex[$lang] . "'
																 ");
						}
						
						// Cache aktualisieren, falls Änderungen übernommen wurden
						if(CACHE && ($affect == "all" || $affect == "1")) {
							$cacheId = is_numeric($content) ? $content : "all";
							$this->refreshCache($content);
						}

				
					} // Ende if contentsMain					
					
				} // Ende if changesTarget
				
			} // Ende foreach conTable

			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
			
		} // Ende foreach diffConTables

		
		if(!isset($insertSQL2) || $insertSQL2 !== true)
			return false;
		else
			return $affect;
		
	}
	
	
	
	/**
	 * Methode zum Anlegen einer History DB-Tabelle
	 * 
	 * @param	$table		string table Table name
	 * @param	$conNo		int conNo Table column num
	 * @access	public
	 * @return	boolean/string
	 */
	public function setupHistoryTable($table, $conNo = 2)
	{
	
		$alterSQL	= false;
		$fields		= "";
		$tableDB	= $this->DB->escapeString($table);
		
		for($i = 1; $i <= $conNo; $i++) {
			// Inhaltsspalte nach Sprache
			foreach($this->o_lng->installedLangs as $lang) {
				$fields .= '`con' . $i . '_' . $lang . '` mediumtext COLLATE utf8_general_ci NOT NULL,';
			}
			$fields .= '`type-con' . $i . '` varchar(50) COLLATE utf8_general_ci NOT NULL,';
			$fields .= '`styles-con' . $i . '` text COLLATE utf8_general_ci NOT NULL,';
		}
		
		// Temp-Tabelle anlegen
		$createSQL = $this->DB->query("CREATE TABLE IF NOT EXISTS `" . $tableDB . "` 
											(
											  `id` int(11) AUTO_INCREMENT NOT NULL,
											  `v_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
											  `page_id` varchar(64) COLLATE utf8_general_ci NOT NULL,
											  $fields
											  PRIMARY KEY (`id`)
											) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
										");
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $tableDB . "`");
		
		
		// Ggf. Tabelle um Felder erweitern
		$result	= $this->extendContentTable($table, $conNo, 3);
		
		$alterSQL	= $result["result"];
		
		return $alterSQL;
								
	}
	
	
	
	/**
	 * Methode zum Erweitern einer content DB-Tabelle
	 * 
	 * @param	$table			string table Table name
	 * @param	$conNo			int conNo Table column num
	 * @param	$colCorrection	int colCorrection columns to subtract
	 * @access	public
	 * @return	boolean/string
	 */
	public function extendContentTable($table, $conNo, $colCorrection)
	{
	
		// Ggf. Tabelle um Felder erweitern
		$tableDB	= $this->DB->escapeString($table);
		$alterSQL	= false;
		
		// Column num
		$queryCols		= $this->DB->query("SHOW COLUMNS FROM `" . $tableDB . "`");
		
		$totColumns		= count($queryCols);
		$conColumns		= $totColumns - $colCorrection; // Spalte (z.B. id, v_timestamp und page_id) abziehen
		$installedLangs	= count($this->o_lng->installedLangs);
		$columns 		= $conColumns/($installedLangs +2); // Anzahl an Inhaltselementen (Spalten/Sprache) = alle Spalten minus page_id durch Anzahl an Sprachen plus 2 (type-/styles-con)
		
		$diffColNo		= $conNo - $columns;
		
		// Ggf. zusätzliche Spalten einfügen
		if($diffColNo > 0) {
		
			$alterStr	= "";
			
			for($i = $columns +1; $i <= $conNo; $i++) {
				// Inhaltsspalte nach Sprache
				foreach($this->o_lng->installedLangs as $lang) {
					$alterStr .= ' ADD `con' . $i . '_' . $lang . '` mediumtext COLLATE utf8_general_ci NOT NULL,';
				}
				$alterStr .= ' ADD `type-con' . $i . '` varchar(50) COLLATE utf8_general_ci NOT NULL,';
				$alterStr .= ' ADD `styles-con' . $i . '` text COLLATE utf8_general_ci NOT NULL,';
			}
			
			$alterStr	= substr($alterStr, 0, -1);
			
			$alterSQL	= $this->DB->query("ALTER TABLE `" . $tableDB . "` " . $alterStr . "");									
		
		}
		
		return array(	"columns" => $columns,
						"result" => $alterSQL
					);
		
	}
	
	
	
	/**
	 * Methode zum Anlegen einer History DB-Tabelle
	 * 
	 * @param	$table			string table Table name
	 * @param	$tableOld		string tableOld Table name
	 * @param	$pageID			string table pageID
	 * @param	$conNo			int conNo Table column num
	 * @access	public
	 * @return	boolean/string
	 */
	public function insertHistory($table, $tableOld, $pageID, $conNo)
	{
	
		$fieldStr	= "`page_id`,";
		$valueStr	= "'" . $pageID . "',";
		$tableDB	= $this->DB->escapeString($table);
		

		for($i = 1; $i <= $conNo; $i++) {
			// Inhaltsspalte nach Sprache
			foreach($this->o_lng->installedLangs as $lang) {
				$fieldStr .= '`con' . $i . '_' . $lang . '`,';
			}
			$fieldStr .= '`type-con' . $i . '`,';
			$fieldStr .= '`styles-con' . $i . '`,';
		}
		
		$fieldStr	= substr($fieldStr, 0, -1);
		
		$selectSQL	= $this->DB->query("SELECT " . $fieldStr . " FROM `" . $tableOld . "` WHERE `page_id` = '$pageID'");
		

		for($i = 1; $i <= $conNo; $i++) {
			// Inhaltsspalte nach Sprache
			foreach($this->o_lng->installedLangs as $lang) {
				$valueStr .= "'" . $this->DB->escapeString($selectSQL[0]['con' . $i . '_' . $lang]) . "',";
			}
			$valueStr .= "'" . $this->DB->escapeString($selectSQL[0]['type-con' . $i]) . "',";
			$valueStr .= "'" . $this->DB->escapeString($selectSQL[0]['styles-con' . $i]) . "',";
		}
		
		$valueStr	= substr($valueStr, 0, -1);

		
		// In Temp-Tabelle Daten zwischenspeichern
		$historySQL = $this->DB->query("INSERT INTO `" . $tableDB . "` (" . $fieldStr .") VALUES (" . $valueStr . ")");

	}
	
	
	
	/**
	 * Methode zum Anlegen/Aktualisieren eines Seiten-Caches
	 * 
	 * @param	$affect		string page_id der Seite (default = all)
	 * @access	public
	 * @return	boolean/string
	 */
	public function refreshCache($affect = "all")
	{
		
		$pageID = $affect;
		
		// Falls alle Änderungen übernommen werden sollen
		if(!is_numeric($pageID)) {
			
			$queryExt = "";
			
			if(strpos($pageID, ".tpl") !== false)
				$queryExt = " AND `template` = '" . $pageID . "'";
				
			// Datenbanksuche zur Best. der Maximalen Anzahl an Spalten
			$queryCachePages = $this->DB->query("SELECT `page_id` 
													  FROM `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
													  WHERE `page_id` > 0
													  AND `group` = 'public'" .
													  $queryExt . "
													  ");
			
		}
		elseif(is_numeric($pageID))
			$queryCachePages = array(array('page_id' => $pageID));
		else
			return false;
		
		
		if(count($queryCachePages) > 0) {
			
			set_time_limit(300);
		  	$cachePath = HTML_CACHE_DIR;
		  	if(!is_dir($cachePath)) mkdir($cachePath);
			
			$writeErrors	= array();
			$noCache		= array();
			
			foreach($this->o_lng->installedLangs as $lang) {
				
			  	// Falls Verzeichnisse nicht vorhanden, diese anlegen	  
			  	if(!is_dir($cachePath . $lang)) mkdir($cachePath . $lang);
				
				foreach($queryCachePages as $cachePage) {
				
					###########################
				  	// HTML-Cache aktualisieren
				  	###########################
			  
					$pageID		= $cachePage['page_id'];
					$cacheUrl	= HTML::getLinkPath($pageID, $lang, true, true) . '?lang=' . $lang . "&fetchcache=1&ts=" . time();
				
					// Ausleseverfahren
				  	if(CACHE_METHOD == "fgc")
						$htmlPage	= file_get_contents($cacheUrl);
					else
						$htmlPage	= self::getcURL($cacheUrl);
					
					// Falls keine Formulare / dynamische Inhalte (Gästebuch, News, etc.) vorhanden sind, Seite cachen
					if(strpos($htmlPage, '<') === 0
					#&& strpos($htmlPage, 'cc-con-register') === false
					#&& strpos($htmlPage, 'cc-con-form') === false
					#&& strpos($htmlPage, 'cc-con-cform") === false
					#&& strpos($htmlPage, 'cc-con-oform') === false
					&& strpos($htmlPage, 'cc-con-counter') === false
					) {
						
						// Kommentar hinzufügen						
						$htmlPage		= explode(">", $htmlPage, 2);
						$htmlPage[0]	.= ">\n<!-- HTML page #" . $pageID . " from cache (lang = " . $lang . ") -->";
						$htmlPage		= implode("", $htmlPage);
						$htmlPage		= self::removeBOM($htmlPage);
						
						if(!file_put_contents(HTML_CACHE_DIR . $lang . '/' . $pageID . '.html', $htmlPage)) {
							$writeErrors[] = $pageID;
						}
					}
					else { // Falls kein Caching möglich ist (z.B. wegen Formularelement)
						
						// Cache, falls für diese Seite vohanden, löschen
						if(file_exists($cachePath . $lang . '/' . $pageID . '.html'))
							unlink($cachePath . $lang . '/' . $pageID . '.html');
						
						$noCache[] = $pageID;
					}
										
				}
				
			} // Ende foreach
			
			if(count($writeErrors) > 0)
				return array('result' => false, 'title' => implode(",\n", array_unique($writeErrors)));
			elseif(count($noCache) > 0 && $affect != "all" && is_numeric($affect))
				return array('result' => 'nocache', 'title' => (string)parent::replaceStaText("{s_title:nocache}"));
			else
				return array('result' => true, 'title' => (string)parent::replaceStaText("{s_title:cacheuptodate}"));
		}
		else
			return false;
					
	}
	
	
	/**
	 * Liest den Inhalt einer HTML-Seite via cURL aus
	 * 
	 * @param	$url		string Url der auszulesenden Seite
	 * @access	public
	 * @return	boolean/string
	 */
	public static function getcURL($url)
	{

		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}	
	
	
	/**
	 * Schalten der Website in den Live- bzw. Staging-Betrieb
	 * 
	 * @param	$mode		boolean Live Modus an
	 * @access	public
	 * @return	boolean/string
	 */
	public static function setWebsiteLiveMode($mode)
	{
	
		if($mode !== true && $mode !== false)
			return false;
		
		
		// Inhalte der Settings-Datei einlesen
		if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) {
			die("settings file not found");
			return false;
		}
			
		// Sicherungskopie von settings.php anlegen
		copy(PROJECT_DOC_ROOT . '/inc/settings.php', PROJECT_DOC_ROOT . '/inc/settings.php.old');
		
		$settings = preg_replace("/'WEBSITE_LIVE',".self::boolToStr(WEBSITE_LIVE)."/", "'WEBSITE_LIVE',".self::boolToStr($mode), $settings);
									
		// settings.php speichern
		if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings)) {
			die("could not write settings file");
			return false;
		}
		return true;

	}
	
	
	
	/**
	 * Methode zur Erstellung des Accountmenüs
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getAdminAccountMenu()
	{
	
		$settings	= in_array($this->loggedUserGroup, $this->adminPages['settings']['access']);
		return $this->getAccountMenu($settings);
	
	}



	/**
	 * Methode zur Erstellung des Adminmenüs
	 * 
	 * @param	string $type Menüart (0=Menu, 1=Main, 2=Modules, 3=Actions, 4=Top)
	 * @access	public
	 * @return	string
	 */
	public function getAdminMenu($type)
	{
		
		$adminMenu		= '<ol id="admin'.(!$type ? 'Menu' : ($type == 4 ? 'Top' : 'Main')).'" class="adminMenu">' . "\r\n";
		$submenuLevel	= 0;
		$openListTag	= '<ul class="subMenu">' . "\r\n";
		$closeListTag	= '</ul>' . "\r\n";
		$loadViaAjax	= ' data-ajax="true"';
		foreach($this->adminPages as $name => $adminPage) {
			
			if(in_array($this->loggedUserGroup, $adminPage['access']) && $adminPage['menu'][$type] !== false) {
				
				$menuItemName	= '{s_nav:admin'.$name.'}';
				$icon			= !empty($adminPage['icon']) ? $adminPage['icon'] : $name;
				$iconTag		= parent::getIcon($icon, 'menuicon-' . $name . ' menuItem-icon');
					
				
				// Adminmenü oder adminTop (mit Untermenüs bzw. Gruppierung)
				if(($type == 0 || $type == 4) && $adminPage['menu'][0] > 0) {
					
					// Hauptpunkt
					if($name == self::$task)
						$active = " active";
					else
						$active = "";
					
					if($adminPage['submenu'] > $submenuLevel) {
						
						if($submenuLevel > 0)
							$adminMenu .= $closeListTag;
							
						$adminMenu .= $openListTag;
						$submenuLevel = $adminPage['submenu'];
					}
				
					$subMenuItems	= "";
					$prevLevel		= 0;
					
					
					// Ggf. Untermenu Level-2 einfügen
					foreach($this->adminPages as $subMenuItem => $itemDetails) {
					
						if($subMenuItem == $name) {
							$prevLevel = 1;
							continue;
						}
						
						if($prevLevel == 1 && $itemDetails['submenu'] == $submenuLevel && $itemDetails['level'] == 1) {
						
							// Falls Berechtigung
							if(in_array($this->loggedUserGroup, $itemDetails['access']) && $adminPage['menu'][$type] !== false) {
							
								$subMenuItemName	= '{s_nav:admin'.$subMenuItem.'}';
								
								if($subMenuItem == self::$type)
									$activeSub = " active";
								else
									$activeSub = "";
							
								$subMenuItems .=	'<li id="' . ($type == 4 ? 'top' : 'main') . 'MenuItem-' . $name . '-' . $subMenuItem . '" class="subMenuItem' . $activeSub . '">' . "\r\n" .
													'<a href="' . ADMIN_HTTP_ROOT . '?task='.$name.'&type='.$subMenuItem.'"' . $loadViaAjax . '>' . "\r\n" .
													parent::getIcon((!empty($itemDetails['icon']) ? $itemDetails['icon'] : $subMenuItem), 'menuicon-' . $subMenuItem . ' menuItem-icon') .
													'<span class="menuItem-text"> ' . $subMenuItemName . '</span>' . "\r\n" .
													'</a>' . "\r\n" .
													'</li>' . "\r\n";
							}
						}
						else
							$prevLevel = 0;
					}
					
					if($subMenuItems != "") {
						$subMenuItems = "\r\n" . '<ul class="subMenu level-2">' . "\r\n" .
										$subMenuItems . '</ul>' . "\r\n";
						$active .= " hasChild";
					}
					
					// Menüpunkt (ggf. mit Untermenü) ausgeben
					$adminMenu .=	'<li id="' . ($type == 4 ? 'top' : 'main') . 'MenuItem-' . $name . '" class="' . ($type == 4 ? 'top' : 'main') . 'MenuItem' . $active . '">' . "\r\n" .
									'<a href="' . ADMIN_HTTP_ROOT . '?task='.$name.'"' . $loadViaAjax . '>' . "\r\n" .
									parent::getIcon($icon, 'menuicon-' . $name . ' menuItem-icon') .
									'<span class="menuItem-text"> ' . $menuItemName . '</span>' . "\r\n" .
									'</a>' . "\r\n" .
									$subMenuItems .
									'</li>' . "\r\n";
				}
				
				// Main
				elseif($type == 1 && ($adminPage['menu'][1] > 0 || $adminPage['menu'][0] > 0) && $adminPage['submenu'] <= 6) {
				
					if($adminPage['menu'][3] > 0)
						$adminMenu .=	'<li class="' . ($adminPage['menu'][1] > 0 ? ' shortMenu' . ($adminPage['menu'][0] == 0 ? ' hide' : '') : '') . '">' . "\r\n" .
										'<a href="' . ADMIN_HTTP_ROOT . '?task=campaigns&type='.$name.'"' . $loadViaAjax . '>' . "\r\n" .
										$iconTag .
										'<span class="menuItem-text"> ' . $menuItemName . '</span>' . "\r\n" .
										'</a>' . "\r\n" .
										'</li>' . "\r\n";
						
					elseif($adminPage['menu'][2] > 0)
						$adminMenu .=	'<li class="' . ($adminPage['menu'][1] > 0 ? ' shortMenu' . ($adminPage['menu'][0] == 0 ? ' hide' : '') : '') . '">' . "\r\n" .
										'<a href="' . ADMIN_HTTP_ROOT . '?task=modules&type='.$name.'"' . $loadViaAjax . '>' . "\r\n" .
										$iconTag .
										'<span class="menuItem-text"> ' . $menuItemName . '</span>' . "\r\n" .
										'</a>' . "\r\n" .
										'</li>' . "\r\n";
						
					elseif($adminPage['menu'][0] > 0)
						$adminMenu .=	'<li class="' . ($adminPage['menu'][1] > 0 ? ' shortMenu' . ($adminPage['menu'][0] == 0 ? ' hide' : '') : '') . '">' . "\r\n" .
										'<a href="' . ADMIN_HTTP_ROOT . '?task='.$name.'"' . $loadViaAjax . '>' . "\r\n" .
										$iconTag .
										'<span class="menuItem-text"> ' . $menuItemName . '</span>' . "\r\n" .
										'</a>' . "\r\n" .
										'</li>' . "\r\n";
						
				}
				
				// Modules
				elseif($type == 2 && $adminPage['menu'][2] > 0 && $adminPage['submenu'] <= 6)
					$adminMenu .=	'<li class="shortMenu">' . "\r\n" .
									'<a href="' . ADMIN_HTTP_ROOT . '?task=modules&type='.$name.'"' . $loadViaAjax . '>' . "\r\n" .
									parent::getIcon($icon, 'menuicon-' . $name . ' menuItem-icon') .
									'<span class="menuItem-text"> ' . $menuItemName . '</span>' . "\r\n" .
									'</a>' . "\r\n" .
									'</li>' . "\r\n";
					
				// Actions
				elseif($type == 3 && $adminPage['menu'][3] > 0 && $adminPage['submenu'] <= 6)
					$adminMenu .=	'<li class="shortMenu">' . "\r\n" .
									'<a href="' . ADMIN_HTTP_ROOT . '?task=campaigns&type='.$name.'"' . $loadViaAjax . '>' . "\r\n" .
									parent::getIcon($icon, 'menuicon-' . $name . ' menuItem-icon') .
									'<span class="menuItem-text"> ' . $menuItemName . '</span>' . "\r\n" .
									'</a>' . "\r\n" .
									'</li>' . "\r\n";
				
				// Top
				elseif($type == 4 && $adminPage['submenu'] <= 6)
					$adminMenu .=	'<li>' . "\r\n" .
									'<a href="' . ADMIN_HTTP_ROOT . '?task=' . ($adminPage['menu'][2] > 0 ? 'modules&type=' : ($adminPage['menu'][3] > 0 ? 'campaigns&type=' : '')) . $name . '" title="' . $menuItemName . '"' . $loadViaAjax . '>' . "\r\n" .
									parent::getIcon($icon, 'menuicon-' . $name . ' menuItem-icon') .
									'</a>' . "\r\n" .
									'</li>' . "\r\n";
			}
		}
								
		if($submenuLevel > 0)
			$adminMenu .=		'</ul>' . "\r\n";
		
		$adminMenu .=		'</ol>' . "\r\n";		
		
		return $adminMenu;

	}



	/**
	 * Ersetzt Inhaltsplatzhalter mit Inhalten (Adminbereich)
	 *
	 * @access	public
     * @return  string
	 */
	public function assignAdminContents()
	{
		
		$this->setContentReplacements();
		
		// Menu event listeners
		$this->addEventListeners("menu");

		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Menu.php"; // Menu-Klasse einbinden
		
		$o_menu				= new Menu($this->DB, $this->o_lng, $this->o_dispatcher, $this->o_page, $this->html5);
		
		parent::$o_mainTemplate->poolAssign["admin_logo"]		= $this->getAdminLogo();
		parent::$o_mainTemplate->poolAssign["preview_menu"]		= $o_menu->getMenu($this->pageId, 1, "main", "span", "", false);
		parent::$o_mainTemplate->poolAssign["top_menu"]			= $o_menu->getMenu($this->pageId, 2, "main", "span", "", false);
		parent::$o_mainTemplate->poolAssign["foot_menu"]		= $o_menu->getMenu($this->pageId, 3, "main", "span", "", false);
		parent::$o_mainTemplate->poolAssign["non_menu"]			= $o_menu->getMenu($this->pageId, 0, "main", "span", "", false);
		parent::$o_mainTemplate->poolAssign["cwms_version"]		= CWMS_VERSION;

	}
	
	

	/**
	 * Inhaltselement-Typen (core) auslesen
	 * 
	 * @access	public
	 * @return	array
	 */
	public function getCoreContentTypes()
	{
	
		$conTypes = array(	"media"		=> array("text",
												 "img",
												 "gallery",
												 "cards",
												 "doc",										   
												 "video",
												 "audio",
												 "mediaplayer",
												 "flash"
												),
							"links"		=> array("menu",
												 "listmenu",
												 "lang",
												 "gallerymenu",
												 "link",
												 "tabs",
												 "redirect"
												),
							"modules"	=> array("articles",
												 "news",
												 "planner",
												 "gallery",
												 "gbook",
												 "tagcloud",
												 "newsfeed",
												 "gmap",
												 "cart"
												),
							"forms"		=> array("search",
												 "login",
												 "register",
												 "form",
												 "cform",
												 "oform",
												 "formdata"
												),
							"site"		=> array("sitemap",
												 "counter"											   
												),
							"code"		=> array("html",
												 "script"											   
												),
							"plugins"	=> array()
							);
		
		return $conTypes;
	
	}

	

	/**
	 * Plugins für Adminbereich installieren
	 * 
	 * @param	$listOutput boolean Ausgabe der Inhaltstypen-Liste. Falls false, Button zum Öffnen der Liste ausgeben (default = false)
	 * @param	$select boolean Ausgabe mit Select (default = false)
	 * @access	public
	 * @return	string
	 */
	public function installPlugIns($listOutput = false, $select = false)
	{		
	
		// Ggf. Plugins auslesen
		if(!is_dir(PLUGIN_DIR))
			return false;
		
		$menuCount		= count($this->adminPages) +1; // letzte Menüpuktzahl
		$pluginNode		= 3; // Plugin Menüknoten
		$splitArr		= $this->adminPages; // Tasks ab Kampagnen
		$menuArr		= array();
		$menuArr[6]		= array_splice($splitArr,20); // Tasksmenu 6
		$menuArr[5]		= array_splice($splitArr,18); // Tasksmenu 5
		$menuArr[4]		= array_splice($splitArr,16); // Tasksmenu 4
		$menuArr[3]		= array_splice($splitArr,15); // Tasksmenu 3 (Plugins)
		$menuArr[2]		= array_splice($splitArr,5); // Tasksmenu 2
		$menuArr[1]		= array_splice($splitArr,0); // Tasksmenu 1
		
		
		// Installierte Plugins
		$this->installedPlugins	= $this->getInstalledPlugins();
		// Aktive Plugins
		$this->activePlugins	= $this->getActivePlugins();

		
		// Installierte Plug-Ins Loop
		foreach($this->installedPlugins as $plugin => $pluginDetails) {
		
			// Falls Plugin-Ordner nicht vorhanden, continue
			if(!is_dir(PLUGIN_DIR . $plugin))
				continue;
							
			// Falls Plugin nicht aktiv, continue
			if(!in_array($plugin, $this->activePlugins))
				continue;
			
			if(!empty($pluginDetails["features"]["contentelement"]))
				$this->contentTypes["plugins"][]	= $plugin;
			
			if(!empty($pluginDetails["features"]["contenttype"]))
				$this->contentTypes[$pluginDetails["features"]["contenttype"]][]	= $plugin;
			
			
			// Nur Plug-Ins mit der Datei admin_pluginname.inc.php im Adminbereich installieren
			if(file_exists(PLUGIN_DIR . $plugin . '/admin_' . $plugin . '.inc.php')) {
			
				$this->adminPlugins[] = $plugin;

				$this->setPlugin($plugin, $this->adminLang); // Sprachbausteine des Plug-ins laden
				
				// Access
				if(!empty($pluginDetails["features"]["access"])) {
					$access	= explode(",", $pluginDetails["features"]["access"]);
				}
				else {
					$access	= array("admin","editor");
				}
				
				// Submenu
				if(!empty($pluginDetails["features"]["menunode"]))
					$menuNode	= (int)$pluginDetails["features"]["menunode"];
				else
					$menuNode	= $pluginNode;
				
				// Icon
				if(!empty($pluginDetails["features"]["conicon"]))
					$menuIcon	= $pluginDetails["features"]["conicon"];
				else
					$menuIcon	= "";
				
				
				// Plugins Submenu
				$menuArr[$menuNode][$plugin] = 	array(	"access"	=> $access,
														"level"		=> 0,
														"submenu"	=> $menuNode,
														"menu"		=> array($menuCount, false, false, false, false),
														"icon"		=> $menuIcon
											);
				
				
				$menuCount++;
			}
		}
		$this->adminPages = array_merge($menuArr[1], $menuArr[2], $menuArr[3], $menuArr[4], $menuArr[5], $menuArr[6]);
		
	}
	


	/**
	 * Installiert ein Plugin in die DB
	 *
	 * @access	public
     * @return  array
	 */
	public function installPlugin($plugin)
	{

		// Suche nach aktiven Plugins
		$instPlugins = $this->DB->query(   "INSERT INTO `" . DB_TABLE_PREFIX . "plugins`
												(`pl_name`)
											VALUES('$plugin')"
										);
		return $instPlugins;
	
	}	
	


	/**
	 * checkSessionRegenerate
	 *
	 * @access	public
     * @return  array
	 */
	public function checkSessionRegenerate()
	{

		// Suche nach aktiven Plugins
		if($this->o_security->get('loginRefresh')) {
			$this->scriptCode[]	= 'conciseCMS.regSess	= true;';
			return true;
		}
		
		return false;
	
	}	



	/**
	 * Button generieren
	 * 
	 * @access	public
     * @return  boolean
	 */
	public function getAdminLogo()
	{
		
		$defLogo	= "concise-wms_logo.svg";
		$path		= SYSTEM_IMAGE_DIR . '/';
		
		if(file_exists(str_replace(PROJECT_HTTP_ROOT, PROJECT_DOC_ROOT, CC_ADMIN_LOGO)))
			return CC_ADMIN_LOGO;
		
		return $path . $defLogo;
		
	}
	


	/**
	 * Button generieren
	 * 
	 * @access	public
     * @return  boolean
	 */
	public static function getRefreshPageButton()
	{
		
		// Button refresh page
		$btnDefs	= array(	"href"		=> '#',
								"class"		=> "refreshPage inline-button button-icon-only button-small",
								"text"		=> "",
								"title"		=> "{s_notice:cache}",
								"attr"		=> 'onclick="$(this).attr(\'href\', document.location.href.replace(\'&refresh=1\', \'\') + \'&refresh=1\'); $.doAjaxAction($(this).attr(\'href\'), true);"',
								"icon"		=> "refresh"
							);
		
		$output	= parent::getButtonLink($btnDefs);

		return $output;	

	}
	


	/**
	 * Button generieren
	 * 
	 * @param	string 	$pos 	Button-Position
	 * @access	public
     * @return  boolean
	 */
	public function getButtonBacktomain($pos = "right")
	{		

		// Button back
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "backtomain",
								"id"		=> "backtomain",
								"class"		=> $pos,
								"value"		=> "{s_button:adminmain}",
								"icon"		=> "main"
							);
		
		$output	=	parent::getButton($btnDefs);
		
		return $output;
	
	}



	/**
	 * MediaList-Button zum Öffnen der ListBox generieren
	 * 
	 * @param	array 	$def 	Button-Definitionen
	 * @access	public
     * @return  boolean
	 */
	public function getButtonMediaList($def)
	{
		
		
		return self::getMediaListButton($def);
	
	}



	/**
	 * MediaList-Button zum Öffnen der ListBox generieren
	 * 
	 * @param	array 	$def 	Button-Definitionen
	 * @access	public
     * @return  boolean
	 */
	public static function getMediaListButton($def)
	{
		
		$output =	'<div class="mediaList ' . $def["class"] . '" data-type="' . $def["type"] . '"' . (!empty($def["fe"]) ? ' data-fe="true"' : '') . (!empty($def["redirect"]) ? ' data-redirect="' . $def["redirect"] . '"' : '') . '>' . "\r\n" .
					'<span class="showListBox' . (!empty($def["slbclass"]) ? ' ' . $def["slbclass"] : '') . '" data-url="' . $def["url"] . '"' . (!empty($def["path"]) ? ' data-path="' . $def["path"] . '"' : '') . ' data-type="' . $def["type"] . '">' . "\r\n";
		
		$btnClass	= !empty(self::$styleDefs['ccbutton']) ? self::$styleDefs['ccbutton'] . ' ' : 'cc-button ';
		$btnClass  .= !empty(self::$styleDefs['btn']) ? self::$styleDefs['btn'] : 'btn';
		$btnClass  .= !empty($def["btnclass"]) ? ' ' . $def["btnclass"] : '';
		
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> "openList " . $btnClass,
								"value"		=> $def["value"],
								"title"		=> (!empty($def["title"]) ? $def["title"] : ''),
								"icon"		=> $def["icon"],
								"iconclass"	=> (!empty($def["iconclass"]) ? $def["iconclass"] : '')
							);
		
		$output	.=	parent::getButton($btnDefs);
					
		$output	.=	'</span>' . "\r\n" .
					'</div>' . "\r\n";
		
		return $output;
	
	}



	/**
	 * Button-Link generieren
	 * 
	 * @param	string 	$pos 	Button-Position
	 * @access	public
     * @return  boolean
	 */
	public function getButtonLinkBacktomain($pos = "right")
	{		

		// Button back
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT,
								"id"		=> "backtomain",
								"class"		=> $pos,
								"text"		=> "{s_button:adminmain}",
								"icon"		=> "main"
							);
		
		$output	=	parent::getButtonLink($btnDefs);
		
		return $output;
	
	}
	


	/**
	 * Button-Panel mit back buttons
	 * 
	 * @param	string $type Art der Buttons
	 * @access	public
     * @return  boolean
	 */
	public function getBackButtons($type = "main")
	{
		
		$output =	'<p>&nbsp;</p>' . "\r\n" .
					'<div class="adminArea">' . "\r\n" .
					'<ul>' . "\r\n";
		
		$backToMain	= self::getButtonLinkBacktomain();
		
		
		// Button "zurück zur Hauptauswahl" einfügen
		// Main
		if($type == "main")
	
			$output .=	'<li class="submit back">' . "\r\n" .
						$backToMain .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		
		// Button "edit" einfügen
		// Edit
		if($type == "edit") {
	
			$backToEdit	= '<a href="' . ADMIN_HTTP_ROOT . '?task=edit" class="cc-button button button-icon-left left" id="back">{s_button:adminedit}</a>' . "\r\n";
			
			$output .=	'<li class="submit back">' . "\r\n" .
						$backToEdit .
						$backToMain .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		$output .=	'</ul>' . "\r\n" .
					'</div>' . "\r\n";
		
		return $output;
		
	}



	/**
	 * Gibt einen Noscript-Tag aus
	 * 
	 * @access	public
     * @return  boolean
	 */
	public function getNoscriptTag()
	{
	
		// Noscript-Tag
		return '<noscript><p class="noScriptWarning error">{s_notice:noscript}</p></noscript>';
	
	}



    /**
     * Eine Theme-Liste erstellen
     *
 	 * @param	string $currTheme aktuelles Theme (default = default)
 	 * @param	string $type Art der Auswahl all|new|del (default = all)
 	 * @param	string $type Art der Auswahl options|gallery (default = options)
	 * @access	public
	 * @return	string
     */
    public function listThemes($currTheme = "default", $source = "all", $type = "options")
    {
		
		return self::getThemeSelection($currTheme, $source, $type);
	
    }



    /**
     * Eine Theme-Liste erstellen
     *
 	 * @param	string $currTheme aktuelles Theme (default = default)
 	 * @param	string $type Art der Auswahl all|new|del (default = all)
 	 * @param	string $type Art der Auswahl options|gallery (default = options)
	 * @access	public
	 * @return	string
     */
    public static function getThemeSelection($currTheme = "default", $source = "all", $type = "options")
    {
	
		// Theme wählen
		$instThemes = array();
		$output		= "";
		$i 			= 0;
		
		$handle = opendir(PROJECT_DOC_ROOT . '/themes');
		
		while($content = readdir($handle)) {
			if( $content != ".." && 
				strpos($content, ".") !== 0 && 
				is_dir(PROJECT_DOC_ROOT . '/themes/' . $content)) { // Falls index.tpl mit aufgelistet werden soll
				$instThemes[] = $content;
			}
		}
		closedir($handle);
		
		natsort($instThemes); // sortieren

		// Option-Felder generieren
		if($type == "options") {
		
			foreach($instThemes as $theme) {
				
				$themePreview = "";
				
				// Options für Themes
				if(($theme != "default" && 
					$theme != $currTheme) || 
					$source != "del"
				) { // Falls Options für Liste löschbarer Themes
					$output .= '<option value="' . $theme . '" class="selTheme' . (count($instThemes) > 10 ? ' longList' : '');
					$themePreview  = '" style="background-image:url(' . PROJECT_HTTP_ROOT . '/themes/' . $theme . '/img/theme-preview-thumb.jpg);"';
					$themePreview .= ' data-img-src="' . PROJECT_HTTP_ROOT . '/themes/' . $theme . '/img/theme-preview-thumb.jpg"';
					$themePreview .= ' data-img-label="' . ucfirst($theme) . '"';
				
					if(	($theme == $currTheme && $source == "all") || 
						(!isset($GLOBALS['_POST']['new_theme']) && $theme == $currTheme) || 
						(isset($GLOBALS['_POST']['new_theme']) && $theme == $GLOBALS['_POST']['new_theme']) && 
						$source == "new"
					) {
						$output .= ' currentTheme" selected="selected" data-img-title="{s_label:activetheme}';
					}				
					
					$output .= $themePreview . '>' . $theme . '</option>' . "\r\n";
				}
				
				$i++;
			}
		
			return $output;
		}

		if($type == "gallery") {
		
			$gallery =			'<div id="themeCarousel" class="themeCarousel">' . "\r\n" .
								'<div class="viewport">' . "\r\n" .
								'<div id="themeCarouselSlider" class="owl-carousel owl-theme">' . "\r\n";

			
   			$adjustVaules = array('top', 'bottom');
			
			// Falls ein Preview-Theme gewählt war (Cookie)
			if(isset($GLOBALS['_COOKIE']['previewTheme']) && is_dir(PROJECT_DOC_ROOT . '/themes/' . $GLOBALS['_COOKIE']['previewTheme']))
				$previewTheme = $GLOBALS['_COOKIE']['previewTheme'];
			else
				$previewTheme = false;
			
		
			foreach($instThemes as $theme) {
				
				$imgFile	= htmlspecialchars(PROJECT_HTTP_ROOT . '/themes/' . $theme . '/img/theme-preview.jpg');
				$imgFile   .= '?' . time();
				$title		= "";
				
				$gallery .=		'<div id="theme-' . $theme . '" class="item selectTheme ' . ($previewTheme === $theme ? 'preview ' : '') . ($theme == $currTheme ? 'currentTheme" title="' . parent::replaceStaText('{s_label:activetheme}') . '"' : '" title="' . parent::replaceStaText('{s_label:theme}') . ' &#9658; ' . $theme . '"') . '>' . "\r\n";
			
				$gallery .=		'<img class="lazyOwl" data-src="' . $imgFile . '" alt="' . $theme . '" />' . "\r\n";
				$gallery .=		'<span class="themeName">' . $theme . '</span>' . "\r\n";
				$gallery .=		'</div>' . "\r\n";
								
			}
			
			$gallery .=			'</div>' . "\r\n" .
								'</div>' . "\r\n" .
								'<span class="owl-close buttons themeSelection-close">&#xf102;</span>' . "\r\n" .
								'</div>' . "\r\n";
			
			$output = $gallery;
		}
		
		return $output;
    }



    /**
     * Eine Template-Liste erstellen
     *
 	 * @param	string $currentTpl aktuelles Template
 	 * @param	array  $defaultTpls default Templates
 	 * @param	array  $existTpls existing (custom) Templates
 	 * @param	string $choose Art der Auswahl (default = select)
 	 * @param	string $autoSubmit Automatischer Submit (default = false)
	 * @access	public
	 * @return	string
     */
    public static function listTemplates($currentTpl, $defaultTpls, $existTpls, $choose = "select", $autoSubmit = false)
    {
	
		if($choose == "select") {
			
			$select =		'<select name="template" class="tplSelect' . "\r\n";
							
			if($autoSubmit)
				$select .= 	' autoSubmit';
				
			$select .=		'">' . "\r\n";

			$tplArr	= array_unique(array_merge(array_intersect($defaultTpls, $existTpls), $existTpls));
			#rsort($tplArr);
			
			foreach($tplArr as $template) {
			
				$tplImg	 =	str_replace(".tpl", "-tpl", $template) . '.png';
				
				if(!file_exists(SYSTEM_DOC_ROOT.'/themes/'.ADMIN_THEME.'/img/' . $tplImg))
					$tplImg	 =	'custom-tpl.png';
					
				$select .=	'<option value="' . $template . '"';
				
				$select .=	' data-img-src="' . SYSTEM_IMAGE_DIR . '/' . $tplImg . '"';
				$select .=	' data-img-label="' . str_replace(".tpl", "", $template) . '"';
				$select .=	' data-title="' . str_replace(".tpl", "", $template) . '"';
				
				if($template == $currentTpl)
					$select .=	' selected="selected"';

				$select .=	'>' . $template .  '</option>' . "\r\n";
			}

			$select .=		'</select>' . "\r\n";
			
			return $select;
		
		}
	
    }



    /**
     * Eine Template-Liste erstellen
     *
	 * @access	public
	 * @return	string
     */
    public static function readTemplateDir()
    {
	
		$existTpls	= array();
		
		// Sprachordner ins Array einlesen
		$handle = opendir(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR);
		
		while($content = readdir($handle)) {
			if( $content != ".." && 
				strpos($content, ".") !== 0 && 
				$content != "mod_tpls" && 
				$content != "index.tpl" && 
				$content != "contents.tpl" && 
				$content != "contents_edit.tpl" && 
				$content != "admin.tpl" && 
				$content != "install.tpl") {
				$existTpls[] = $content;
			}
		}
		closedir($handle);

		return $existTpls;

	}



    /**
     * Get grid column select
     *
 	 * @param	string $selName
 	 * @param	string $currVal
	 * @access	protected
	 * @return	string
     */
    protected function getGridColumnSelect($selName, $currVal)
    {

		$output	=	'<select name="' . $selName . '">' . 
					'<option value="">{s_option:choose}</option>' . 
					'<optgroup label="{s_label:width}">' . "\n" .
					'<option value="full"' . ($currVal == "full" ? ' selected="selected"' : '') . '>{s_option:fullrow}</option>' . 
					'<option value="half"' . ($currVal == "half" ? ' selected="selected"' : '') . '>{s_option:halfrow}</option>' . 
					'<option value="third"' . ($currVal == "third" ? ' selected="selected"' : '') . '>{s_option:thirdrow}</option>' . 
					'<option value="quater"' . ($currVal == "quater" ? ' selected="selected"' : '') . '>{s_option:quaterrow}</option>' .
					'<option value="2thirds"' . ($currVal == "2thirds" ? ' selected="selected"' : '') . '>{s_option:2thirdsrow}</option>' .
					'<option value="3quaters"' . ($currVal == "3quaters" ? ' selected="selected"' : '') . '>{s_option:3quatersrow}</option>' .
					'</optgroup>' .
					'<optgroup label="{s_label:colno}">';

		
		$maxCols	= empty(parent::$styleDefs['fullrowcnt']) ? 12 : parent::$styleDefs['fullrowcnt'];
		
		for($colCount = 1; $colCount <= $maxCols; $colCount++) {
			$output .=	'<option value="' . $colCount . '"' . (is_numeric($currVal) && $currVal == $colCount ? ' selected="selected"' : '') . '>' . $colCount . ' {s_option:columns}</option>';
		}
							
		$output .=	'</optgroup>' .
					'</select>' . PHP_EOL;
		
		return $output;
	
	}
	


    /**
     * Aktualisierung der Datei sitemap.xml
     *
 	 * @param	string $url Url, die in die Sitemap-Datei aufgenommen werden soll
	 * @access	public
	 * @return	string
     */
    public function updateSitemapXML($url, $mapFile = 'sitemap.xml')
    {
		
		if(!$sitemap = @file_get_contents(PROJECT_DOC_ROOT . '/' . $mapFile))
			
			return("File &quot;sitemap.xml&quot; not found");
		
		// Url-String generieren, falls noch nicht vorhanden
		if(strpos($sitemap, '<loc>' . $url . '</loc>') === false) {

			$urlStr =	'<url>' . "\r\n" .
						'  <loc>' . $url . '</loc>' . "\r\n" .
						'  <lastmod>' . date(DATE_ATOM, time()) . '</lastmod>' . "\r\n" .
						'  <changefreq>monthly</changefreq>' . "\r\n" .
						'</url>' . "\r\n" .
						'</urlset>';
			
			$replace = str_replace("</urlset>", $urlStr, $sitemap);
										
			if(!@file_put_contents(PROJECT_DOC_ROOT . '/sitemap.xml', $replace))
				return("could not write sitemap.xml file");
			else {
				@chmod(PROJECT_DOC_ROOT . '/sitemap.xml', 0755);
				return true;
			}
		
		}
		return false;
	
    }
	


	/**
	 * Methode zur Erstellung eines Uploadformularteils
	 * 
	 * @param	string	$name Präfix für Attribut name
	 * @param	string	$overwrite Falls vorhandene Datei überschrieben werden soll
	 * @param	int		$id	ID zur eindeutigen Zuweisung der FilesBox (default = 1)
	 * @param	boolean	$scaleImg	falls true werden Felder zum Skalieren von Bildern ausgegeben (default = false)
	 * @param	int		$imgWidth	Bildbreite (default = 0)
	 * @param	int		$imgHeight	Bildhöhe (default = 0)
	 * @access	public
	 * @return	string
	 */
	public function getUploadMask($name, $overwrite, $id = 1, $scaleImg = false, $imgWidth = 0, $imgHeight = 0)
	{
		
		$return = 	'<div class="uploadMask">' . "\r\n" .
					'<input type="file" class="newUploadFile" name="' . $name . '" id="uploader-input-' . $id. '" />' . "\r\n" .
					'<span class="overwriteLabel">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="overwrite_' . $name . '" id="overwrite-' . $id . '"' . ($overwrite === true ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="overwrite-' . $id . '" class="inline-label">{s_label:overwrite}</label>' . "\r\n" .
					'</span>' . "\r\n" .
					'</div>' . "\r\n";
					
		// Falls Bildskalierung
		if($scaleImg !== false)
			$return .=	'<div class="scaleImgLabelBox">' . "\r\n" . 
						'<label class="markBox"><input type="checkbox" name="scaleimg_' . $name . '" class="scaleimg" id="scaleimg-' . $id . '"' . ($scaleImg ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
						'<label for="scaleimg-' . $id . '" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
						'<div class="scaleImgDiv" id="scaleImgDiv-' . $id . '" style="clear:left;' . (!$scaleImg ? ' display:none;' : '') . '">' . "\r\n" .
						'<input type="text" name="imgWidth_' . $name . '" id="imgWidth-' . $id . '" class="imgWidth" value="' . ($imgWidth == 0 ? IMG_WIDTH : $imgWidth) . '" />' . "\r\n" . 
						'<span class="imgSize"> x </span>' . "\r\n" . 
						'<input type="text" name="imgHeight_' . $name . '" id="imgHeight-' . $id . '" class="imgHeight" value="' . ($imgHeight == 0 ? IMG_HEIGHT : $imgHeight) . '" />' . "\r\n" . 
						'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" . 
						'<br class="clearfloat" /></div></div>' . "\r\n" .
						'<br class="clearfloat" />' . "\r\n";

				
		return $return;
	}



	/**
	 *  Methode zur Erstellung eines Uploadformularteils für den Filesordner
	 * 
	 * @param	string	$filesFolder Ornername
	 * @param	string	$checked Status der Checkbox zur Aktivierung
	 * @param	int		$id	ID zur eindeutigen Zuweisung der FilesBox (default = 1)
	 * @access	public
	 * @return	string
	 */
	public function getFilesUploadMask($filesFolder, $checked, $id = 1)
	{
		
		$return =	'<span class="inline-box">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="files_' . $id . '" id="files-' . $id . '" class="useFilesFolder"' . ($checked === true ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
					'<label for="files-' . $id . '" class="filesLabel inline-label">{s_label:filesFolder}</label>' . "\r\n" . 
					'<div class="filesDiv" style="' . ($checked === false ? ' display:none;' : '') . '">' . "\r\n";

		$mediaListButtonDef		= array(	"class" => "files",
											"type"	=> "files",
											"url"	=> PROJECT_HTTP_ROOT . '/system/access/listMedia.php?page=admin&type=files&action=listfolders&folder=' . $filesFolder,
											"value"	=> "{s_button:filesfolder}",
											"icon"	=> "files"
										);
		
		$return .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$return .=	'<label for="filesFolder-' . $id . '">{s_label:filesfoldername}</label>' . "\r\n" . 
					'<input type="text" name="filesFolder_' . $id . '" id="filesFolder-' . $id . '" class="filesFolder" value="' . $filesFolder . '" />' . "\r\n" . 
					'</div>' . "\r\n" .
					'</span>' . "\r\n";

		
		return $return;
	}



	/**
	 * Methode zur Auflistung von Gallerietypen
	 * 
	 * @param	string	$name Präfix füe Attribut name (default = '')
	 * @param	string	$select ausgewählter Gallerietyp (default = '')
	 * @param	boolean/int	$array Arrayelement (default = false)
	 * @access	public
	 * @return	string
	 */
	public static function getGalleryTypes($name = "", $select = "", $array = false)
	{
		
		$gallTypes = 	array	("fader" => "Simple Fader {s_text:gallery}",
								 "thumbs" => "Thumbnail {s_text:gallery} 1",
								 "thumbs2" => "Thumbnail {s_text:gallery} 2",
								 "slimbox" => "Thumbnail {s_text:gallery} 3",
								 "slider" => "Slider 1",
								 "slider2" => "Slider 2",
								 "portfolio" => "Portfolio {s_text:gallery} 1",
								 "portfolio2" => "Portfolio {s_text:gallery} 2",
								 "portfolio3" => "Portfolio {s_text:gallery} 3",
								 "archive" => "{s_option:gallarchive}",
								 "random" => "{s_option:gallrandom}"
								);
		
		$return =	'<select name="' . $name . '_gallery' . ($array !== false ? '['.$array.']' : '') . '" class="' . $name . 'Gallery selectGalleryType">' . "\r\n";
		
		foreach($gallTypes as $key => $value) {
			$return .=	'<option value="'.$key.'"' . ($select == $key ? " selected=\"selected\"" : "") . '>'.$value.'</option>' . "\r\n";
		}
		$return .=	'</select><br class="clearfloat" />' . "\r\n";
		
		return $return;
	}



	/**
	 * Gibt Anzahl der Begrenzung von Auflistungen zurück
	 * 
	 * @param	string $limit Standard-Limit (default = 10)
	 * @access	public
	 * @return	string
	 */
	public function getLimit($limit = 10)
	{

		if(isset($this->g_Session['limitPageNo']))
			$limit = (int)$this->g_Session['limitPageNo'];
		
		if(isset($GLOBALS['_GET']['limit']))
			$limit = (int)$GLOBALS['_GET']['limit'];
		
		if(isset($GLOBALS['_POST']['limit']))
			$limit = (int)$GLOBALS['_POST']['limit'];
		
		$this->o_security->setSessionVar('limitPageNo', $limit);

		return (int)$limit;
	}



	/**
	 * getLimitForm
	 * 
     * @param   array	$limitOptions
     * @param   int		$maxRows
     * @param   string	$formAction
     * @param   array	$hiddenFields
	 * @access	public
     * @return	string
	 */
	public static function getLimitForm($limitOptions, $maxRows, $formAction = "", $hiddenFields = array())
	{
	
		$output		 =	'<span class="cc-list-limit-select {t_class:formrow}">' . PHP_EOL .
						'<form class="{t_class:form} {t_class:forminl}" action="' . htmlspecialchars($formAction) . '" method="post">' . PHP_EOL;
		
		$output		 .=	self::getLimitSelect($limitOptions, $maxRows);
	
		if(!empty($hiddenFields)) {
			foreach($hiddenFields as $hf) {
				$output		 .=	'<input type="hidden" name="' . $hf["name"] . '" value="' . $hf["val"] . '" />' . PHP_EOL;
			}
		}
		
		$output		 .=	'</form>' . PHP_EOL;
		$output		 .=	'</span>' . PHP_EOL;
		
		return $output;
	
	}



	/**
	 * Gibt Select zur Begrenzung von Auflistungen zurück
	 * 
	 * @param	array $limitOptions
	 * @param	int $limit Current limit
	 * @access	public
	 * @return	string
	 */
	public static function getLimitSelect($limitOptions, $limit)
	{

		$output	=	'<select name="limit" class="listLimit" data-action="autosubmit">' . PHP_EOL;		
		
		// Ergebnisse pro Seite
		foreach($limitOptions as $value) {
		
			$output	.=	'<option value="' . $value . '"';
			
			if($limit == $value)
				$output	.=' selected="selected"';
				
			$output	.= '>' . $value . '</option>' . PHP_EOL;
		
		}
							
		$output	.= '</select>' . PHP_EOL;

		return $output;
	
	}



	/**
	 * Setzt eine angegebene DB-Engine für eine DB-Tabelle
	 * 
	 * @param	string $table	DB-Tabelle
	 * @param	string $engine	DB-Engine (default = 'InnoDB')
	 * @access	protected
	 * @return	obj
	 */
	protected function setDbEngine($table, $engine = "InnoDB")
	{

		// Datenbanksuche nach zu löschender Seite
		$query = $this->DB->query("ALTER TABLE `" . $this->DB->escapeString($table) . "` ENGINE = " . $this->DB->escapeString($engine));
		return $query;
	
	}



	/**
	 * Gibt einen validen Namen für eine DB Tabelle zurück
	 * 
	 * @param	string $tableName	DB-Tabellenname
	 * @access	protected
	 * @return	string
	 */
	protected static function sanitizeTableName($tableName)
	{

		// tableName
		$tableName = preg_replace("/[^a-zA-Z0-9_\$]/", "_", $tableName);
		return $tableName;
	}



	/**
	 * Gibt einen getrimmten single line String zurück
	 * 
	 * @param	string $str	String
	 * @access	protected
	 * @return	string
	 */
	protected static function sanitizeSLStr($str)
	{

		// str
		$str	= str_replace(array("\r","\n","\t"), "", $str);
		$str	= trim($str);
		return $str;
	}



	/**
	 * Methode zum Erstellen eines Strings für Datenbankfelder
	 * 
	 * @param	string $type Formular-Feldtyp
	 * @param	string $fieldName db-Feldname
	 * @param	string $required erforderliches feld (0/1)
	 * @param	string $maxLength maximale Anzahl an Eingabebuchstaben (default = '')
	 * @access	public
     * @return  boolean
	 */
	public function getFieldDBStr($type, $fieldName, $required, $maxLength = "")
	{
	
		if($required == 1)
			$null = " NOT NULL";
		else
			$null = " NULL";
			
		if($maxLength == "")
			$maxLength = 1024;
		
		$maxLength = "(" . $this->DB->escapeString($maxLength) . ")";
		
		$return = "`" . $this->DB->escapeString($fieldName) . "`";
		
		//Felder je nach Typ generieren
		switch($type) {
			
			//Textarea
			case "textarea":
				$return	.= " TEXT".$null;
				break;
			
			//Auswahlliste
			case "select":
				$return	.= " VARCHAR(100)".$null;
				break;
			
			//Mehrfachauswahl
			case "multiple":
				$return	.= " VARCHAR".$maxLength.$null;
				break;
			
			//Checkbox
			case "checkbox":
				$return	.= " VARCHAR".$maxLength.$null;
				break;
			
			//Radiobutton
			case "radio":
				$return	.= " VARCHAR(300)".$null;
				break;
			
			//Password
			case "password":
				$return	.= " VARCHAR(200)".$null;
				break;
			
			//File
			case "file":
				$return	.= " VARCHAR(300)".$null;
				break;
			
			//Date
			case "date":
				$return	.= " DATE".$null;
				break;
			
			//Int
			case "int":
				$return	.= " INT".$maxLength.$null;
				break;
			
			//Flaot
			case "float":
				$return	.= " FLOAT".$null;
				break;
			
			//Default
			default:
				$return	.= " VARCHAR".$maxLength.$null;
				break;
		}
		
		return $return;
	
	}



	/**
	 * Methode zum Ausgeben eines formatierten Datums
	 * 
	 * @param	string	$timestamp Zeitstempel
	 * @param	string	$lang Sprache (default = de)
	 * @param	boolean	$time falls, true wird die Uhrzeit mit zurückgegeben (default = true)
	 * @access	public
     * @return  boolean
	 */
	public static function getDateString($timestamp = "", $lang = "de", $time = true)
	{
	
		// Falls kein Zeitstempel mitgegeben wurde, aktuellen Timestamp verwenden
		if($timestamp == "")
			$timestamp = time();
		
		$timeStr = "";
		
		setlocale(LC_TIME, $lang . "_" . strtoupper($lang) . ".UTF-8", $lang . '_' . strtoupper($lang), strtolower($lang));
		
		// Falls Deutsch
		if($lang == "de") {
			$dateStr	= "%d. %B %Y";
			$timeStr	= " %H:%M";
		}
		// Sonst englisches Format
		else {
			$dateStr	= "%B %d %Y";
			$timeStr	= " %I:%M %p";
		}
		
		if($time)
			$dateStr .= $timeStr;
		
		return Language::force_utf8(strftime($dateStr, $timestamp));
	  
	}



	/**
	 * Methode zum Ausgeben einer formatierten Zeitangabe
	 * 
	 * @param	string	$timestamp Zeitstempel
	 * @param	string	$lang Sprache (default = de)
	 * @access	public
     * @return  boolean
	 */
	public static function getTimeString($timestamp = "", $lang = "de")
	{
	
		// Falls kein Zeitstempel mitgegeben wurde, aktuellen Timestamp verwenden
		if($timestamp == "")
			$timestamp = time();
			
		setlocale(LC_TIME, $lang . "_" . strtoupper($lang) . ".UTF-8", $lang . '_' . strtoupper($lang), strtolower($lang));
		
		// Falls Deutsch
		if($lang == "de") {
			$time		= " %H:%M";
		}
		// Sonst englisches Format
		else {
			$time		= " %I:%M %p";
		}
				
		return Language::force_utf8(strftime($time, $timestamp));
	  
	}



	/**
	 * Methode zum Ausgeben eines Strings im JSON-Format
	 * 
	 * @param	string|array	$result	auszugebender String/Array
	 * @access	public
     * @return  boolean
	 */
	public function outputJSON($result)
	{

		header('Content-type: application/json');
		echo json_encode($result);
	
	}



	/**
	 * String in boolean-Wert umwandeln
	 * 
	 * @param	string $value String
	 * @access	public
     * @return  boolean
	 */
	public static function boolToStr($value)
	{
		return $value ? 'true' : 'false';
	}

	

	/**
	 * BOM (Byte Order Mark) aus Datei bzw. String entfernen
	 * 
	 * @param	string $str String
	 * @access	public
     * @return  boolean
	 */
	public static function removeBOM($str)
	{
	
		$bom = pack("CCC", 0xef, 0xbb, 0xbf);
		
		if(0 == strncmp($str, $bom, 3))
			$str = substr($str, 3);
			
		return $str;
	
	}



	/**
	 * Methode zum rekursiven Durchsuchen von Arrays
	 * 
	 * @param	string $search String
	 * @param	array $array zu durchlaufendes Array (default = pages)
	 * @access	public
     * @return  boolean
	 */
	public static function arraySearch_recursive($search, $array)
	{
		
	  foreach($array as $key => $values)
	  {
		if(in_array($search, $values))
		{
		  return true;
		}
		if(is_array($key))
		{
		  	self::arraySearch_recursive($search, $key);
		}
	  }
	  
	  return false;
	  
	}



	/**
	 * Methode zum rekursiven Suchen nach Array-Schlüssel
	 * 
	 * @param	string $search String
	 * @param	array $array zu durchlaufendes Array (default = pages)
	 * @access	public
     * @return  boolean
	 */
	public static function arrayKeySearch_recursive($search, $array)
	{
		
	  $i = 0;
	
	  foreach($array as $key => $values)
	  {
		if($key === $search)
		{
		  return true;
		}
		if(is_array($values))
		{
			if(array_key_exists($search, $values))
				return true;
			
		  	self::arrayKeySearch_recursive($search, $values);
		}
		$i++;
	  }
	  
	  return false;
	  
	}



	/**
	 * Methode zum Kopieren von Ordnern und Unterordnern
	 *
	 * @param	string $src Quell-Ordnername
	 * @param	string $dst Ziel-Ordnername
	 * @param	string $excludeDir Ordnername eines auszuschließenden Ordners
	 * @access	protected
	 */
	protected static function copyRecursive($src, $dst, $excludeDir = "")
	{
		
		$dir = opendir($src);
		
		@mkdir($dst);
		
		while(($file = readdir($dir)) !== false) {
			
			if($file != '.' && $file != '..' && $file != $excludeDir) {
				if(is_dir($src . '/' . $file) ) {
					self::copyRecursive($src . '/' . $file, $dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir); 
		
	}



	/**
	 * Methode zum rekursive Löschen von Ordnern
	 *
	 * @param	string $dir Directory name
	 * @param	boolean $deleteFolders Verzeichnisse löschen, falls true (default = false)
	 * @access	public
	 */
	public static function unlinkRecursive($dir, $deleteFolders = false)
	{
	
		if(!$dh = @opendir($dir)) {
			return false;
		}
		
		while(false !== ($obj = readdir($dh))) {
			if($obj == '.' || $obj == '..') {
				continue;
			}
	
			if(is_dir($dir . DIRECTORY_SEPARATOR . $obj)) {
				self::unlinkRecursive($dir . DIRECTORY_SEPARATOR . $obj, $deleteFolders);
			}
			else{
				chmod($dir . DIRECTORY_SEPARATOR . $obj, 0777);
				@unlink($dir . DIRECTORY_SEPARATOR . $obj);
			}
		}
	
		closedir($dh);
		
		if($deleteFolders) {
			chmod($dir, 0777);
			@rmdir($dir);
		}
	   	   
		return true;
	
	} 
	
	
	
	/**
	 * makeTempDir
	 * @return boolean
	 */
	protected static function makeTempDir()
	{
	
		if(!is_dir(TEMP_DIR))
			return mkdir(TEMP_DIR, 0755);
		
		return false;		
		
	}
	
	
	
	/**
	 * deleteTempDir
	 * @return boolean
	 */
	protected static function deleteTempDir()
	{
	
		if(is_dir(TEMP_DIR))
			return Admin::unlinkRecursive(TEMP_DIR, true);
		
		return false;
		
	}
	
	
	
	/**
	 * activateMaintenanceMode
	 * @access protected
	 */
	protected function activateMaintenanceMode()
	{
		
		// Wartungsmodus einschalten
		// Temp-Verzeichnis
		self::makeTempDir();
		
		// Datei für Wartungsmodus erstellen
		$mtFile		= TEMP_DIR . 'maintenance.ini';
		
		if(@file_put_contents($mtFile, "1")) {
			if(chmod($mtFile, 0644)) {
				$this->maintenanceMode = true;
				setcookie('cwms_maintenanceLog', true, time() +120, "/"); // Cookie setzten, um Zugang bei Fehler zu gewährleisten
				return true;
			}
		}
		die("false");
		return false;
	
	}
	
	
	
	/**
	 * inactivateMaintenanceMode
	 * @access protected
	 */
	protected function inactivateMaintenanceMode()
	{
		
		// Wartungsmodus ausschalten
		// Datei für Wartungsmodus löschen
		$mtFile		= TEMP_DIR . 'maintenance.ini';
		
		if(file_exists($mtFile)) {
			if(unlink($mtFile)) {
				$this->maintenanceMode = false;
				return true;
			}
			else
				return array('{s_error:mtmodeoff}');
		}
		return false;
	
	}

}
