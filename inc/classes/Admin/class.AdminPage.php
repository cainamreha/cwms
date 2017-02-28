<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\Admin\ExtendAdminPageEvent;


// Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEngine.php"; // ContentsEngine einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Admin/class.Admin.php"; // Adminklasse einbinden

// Event-Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Admin/events/event.ExtendAdminPageEvent.php";


/**
 * Systemklasse Concise WMS - AdminPage
 * 
 */

class AdminPage extends Admin
{

	private $adminTask				= "";
	private $forceFullPageContents	= false;
	private $o_adminGlobalEvent		= null;
	private $o_extendAdminPageEvent	= null;

	/*
	 * Methode zum Ausgeben einer Adminseite
	 *
 	 * @param	$DB		object		DB-Objekt
 	 * @param	$o_lng	object		Sprachobjekt
	 */
	public function __construct($DB, $o_lng)
	{
	
		$this->DB			= $DB;
		$this->o_lng		= $o_lng;

	}

	/*
	 * Methode zum Ausgeben einer Adminseite
	 *
	 * @access	public
	 */
	public function initAdminPage()
	{
		
		require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/class.AdminTaskFactory.php"; // AdminTask-Factory einbinden

		// Hauptbereich
		$lang		= $this->o_lng->lang;
		$init		= true;
		$ajax		= false;
		$options	= array();
		
		
		// Seitenaufruf ggf. via Ajax
		if(isset($GLOBALS['_GET']['ajax']) 
		&& $GLOBALS['_GET']['ajax'] == 1
		) {
			$ajax	= true;
			#$init	= false;
		}
		
		// Kompletten Seiteninhalt bei Ajax forcieren
		if(isset($GLOBALS['_GET']['fullpage']) 
		&& $GLOBALS['_GET']['fullpage'] == 1
		) {
			$this->forceFullPageContents	= true;
		}
		
		if(!empty($_GET['task']))
			$this->adminTask = $_GET['task'];
		else
			$this->adminTask = "main";

		

		###################################################
		##############  Templates-Bereich  ################
		###################################################
		if($this->adminTask == "tpl")
			$options	= $this->getTplTaskOptions();
			
		
		
		###################################################
		##################  Änderungen  ###################
		###################################################
		if($this->adminTask == "changes")
			$options	= $this->getChangesTaskOptions();
			
		
		
		###################################################
		###################  Modules  #####################
		###################################################
		if($this->adminTask == "modules")
			$options	= $this->getModulesTaskOptions();
			
		
		
		###################################################
		##################  Kampagnen  ####################
		###################################################
		if($this->adminTask == "campaigns")
			$options	= $this->getCampaignsTaskOptions();



		
		// Load respective task
		try {				
			// Inhaltselement-Instanz
			$admin	= AdminTaskFactory::create($this->adminTask, $options, $this->DB, $this->o_lng, $init);
		}
		// Falls Element-Klasse nicht vorhanden
		catch(\Exception $e) {
			header("HTTP/1.1 406 Not Acceptable");
			echo(ContentsEngine::replaceStaText($e->getMessage()));
			die();
			exit;
		}
		
		
		// Theme-Setup
		$admin->getThemeDefaults("admin");
		
		
		// Adminbereich laden, falls nicht Ajax
		$admin->loadAdminPage(true);
		
		
		// Events listeners registrieren
		$admin->addEventListeners("admin");
		
		
		
		// Adminseite (task) laden, falls Zugriffsberechtigung
		if($admin->getTaskAccess()) {
			$admin->getTaskContents($ajax);
		}
		// andernfalls fehlende Zugriffsberechtigung
		else {
			$admin->adminContent	=	'<h1 class="adminHeader cc-h1">' . $this->adminTask . '</h1>' . "\n" .
										'</div>' . "\n" .
										'<p class="error">{s_error:notaskaccess}</p>' . "\n" .
										$admin->getBackButtons();
			
			$this->adminTask = parent::$task;
		}		

		
		// Inhalte holen
		$this->getAdminContents($admin);		
		
		
		// Admin events
		// ExtendAdminPageEvent
		$this->o_extendAdminPageEvent	= new ExtendAdminPageEvent($this->DB, $this->o_lng, $admin, parent::$task, parent::$type);
		
		// dispatch event get rightbar contents
		$admin->o_dispatcher->dispatch('global.get_rightbar_contents', $this->o_extendAdminPageEvent);
		
		// dispatch event register_head_files
		$admin->o_dispatcher->dispatch('global.register_head_files', $this->o_extendAdminPageEvent);
		
		$admin->mergeHeadCodeArrays($this->o_extendAdminPageEvent);
		$admin->adminRightBarContents[]		= $this->o_extendAdminPageEvent->getOutput();

		
		// Footer setzen
		$admin->footerAction();
		
		
		// Seitentitel ergänzen
		if(!empty(parent::$type))
			$admin->o_page->pageTitle		= $admin->o_page->pageTitle . ContentsEngine::replaceStaText(' - {s_nav:admin' . parent::$type . '}');
		elseif(!empty($this->adminTask) 
		&& $this->adminTask != "main"
		)
			$admin->o_page->pageTitle		= $admin->o_page->pageTitle . ContentsEngine::replaceStaText(' - {s_nav:admin' . $this->adminTask . '}');
		
		
		// Falls Ajax und kein Erzwingen des gesamten Seiteninhaltes
		if($ajax 
		&& !$this->forceFullPageContents
		&& $this->adminTask != "main"
		) {
		
			parent::$o_mainTemplate	= new Template("contents.tpl"); // Template-Objekt erstellen
			parent::$o_html			= new HTML($admin); // HTML-Objekt erstellen
			
			parent::$o_mainTemplate->loadTemplate(true);
			parent::$o_mainTemplate->poolAssign["dbcontents"]	= $admin->adminContent;
			$admin->assignReplaceCommon();
			$admin->assignReplace();
			
			echo parent::$o_mainTemplate->getTemplate(true);
			echo parent::$o_html->getScriptCodeTags($admin->scriptCode);
			
			exit;
			die();
		}
		
		
		// Ausgabe des Adminseiten-MainContents
		$output				= $admin->getNoscriptTag(); // Noscript-Meldung an den Anfang
		$output			   .= $admin->adminHeader; // Header
		$output			   .= $admin->adminContent; // Hauptinhalt
				

		// Ersetzen von Textplatzhaltern
		$admin->assignAdminContents();
		$admin->assembleAdminContents($output);
		$admin->assignReplaceCommon();

		
		// Ggf. Memory usage für Debug-Konsole
		if(DEBUG && $admin->adminLog)
			$this->getMemoryUsage();
		
		// Alle Ersetzungen vornehmen
		$admin->assignReplace();


		// Und das fertige Template in Variable speichern
		$admin->printHtmlContent();

		return $output;
		
	}
	

	/**
	 * getTplTaskOptions
	 * 
	 * @access public
	 * @return string
	 */
	public function getTplTaskOptions()
	{		

		$options	= array();
	
		// Fall Templateinhalte bearbeitet werden
		if(!empty($GLOBALS['_POST']['edit_area'])
		&& (!empty($GLOBALS['_POST']['edit_tpl']) 
			|| (isset($GLOBALS['_POST']['edit_tpl_change']) 
				&& !empty($GLOBALS['_POST']['template']) 
				)
			)
		) { // edit_tpl auslesen, falls in Post gesetzt
			
			$options['admintype']		= "edit";
			$options['editId']			= isset($GLOBALS['_POST']['edit_tpl']) ? $GLOBALS['_POST']['edit_tpl'] : $GLOBALS['_POST']['template'];
			$options['isTemplateArea']	= true;
			return $options;
		}
		
		if(!empty($GLOBALS['_GET']['edit_id']) 
		&& !is_numeric($GLOBALS['_GET']['edit_id']) 
		&& !empty($GLOBALS['_GET']['area']) 
		) { // edit_tpl auslesen, falls in Get gesetzt
			
			$options['admintype']		= "edit";
			$options['editId']			= $GLOBALS['_GET']['edit_id'];
			$options['isTemplateArea']	= true;
			return $options;
		}
		
		if(!empty($GLOBALS['_GET']['edit_tpl']) 
		&& !empty($GLOBALS['_GET']['edit_area']) 
		) { // edit_tpl auslesen, falls in Get gesetzt
			
			$options['admintype']		= "edit";
			$options['editId']			= $GLOBALS['_GET']['edit_tpl'];
			$options['isTemplateArea']	= true;
			return $options;
		}
		
		return $options;
	
	}
	

	/**
	 * getChangesTaskOptions
	 * 
	 * @access public
	 * @return string
	 */
	public function getChangesTaskOptions()
	{

		$options	= array();
	
		$options['admintype']		= "changes";
		
		return $options;
	
	}
	

	/**
	 * getModulesTaskOptions
	 * 
	 * @access public
	 * @return string
	 */
	public function getModulesTaskOptions()
	{

		$options	= array();
			
		if(!empty($GLOBALS['_GET']['type']) 
		&& in_array($GLOBALS['_GET']['type'], $this->moduleTypes)
		) { // mod type auslesen, falls in Get gesetzt
			
			$options['admintype']		= $GLOBALS['_GET']['type'];
			
			if($options['admintype'] == "articles"
			|| $options['admintype'] == "news"
			|| $options['admintype'] == "planner"
			)
				$options['admintype']	= "data";
		}
		
		return $options;
	
	}
	

	/**
	 * getCampaignsTaskOptions
	 * 
	 * @access public
	 * @return string
	 */
	public function getCampaignsTaskOptions()
	{		

		$options	= array();
			
		if(!empty($GLOBALS['_GET']['type']) 
		&& in_array($GLOBALS['_GET']['type'], $this->campaignTypes)
		) { // campaign type auslesen, falls in Get gesetzt
			
			$options['admintype']		= $GLOBALS['_GET']['type'];

		}
		
		return $options;
	
	}


	/**
	 * Generiert den Admin-Inhaltsbereich
	 * 
	 * @access public
	 * @return string
	 */
	public function getAdminContents($admin)
	{		
	
		// Falls keine Task ausegewählt wurde, Haupbereich anzeigen
		################  Hauptbereich  ####################
		if(empty($this->adminTask))
			return $admin->adminContent;

		
		################  Locking  #########################
		if($admin->checkGenPageLocks())
			return $admin->adminContent;


		################  Änderungen  ######################
		if($this->adminTask == "changes")
			return $admin->conductChanges();
		
		
		################  Admin-Plugin-Bereich  ############
		if(in_array($this->adminTask, $admin->adminPlugins))
			$admin->isAdminPlugin = true;			
		
		
		// Admin-Content zurückgeben
		return $admin->adminContent;
	
	}
	
}
