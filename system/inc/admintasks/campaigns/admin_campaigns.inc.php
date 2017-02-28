<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\ExtendCampaignsEvent;



###################################################
##############  Kampagnen-Bereich  ################
###################################################

// Event-Klassen einbinden
require_once SYSTEM_DOC_ROOT."/inc/admintasks/campaigns/events/event.ExtendCampaigns.php";

// Kampagnen verwalten 

class Admin_Campaigns extends Admin implements AdminTask
{	
	
	public $wrongInput			= array();
	private $overwrite			= false;
	private $scaleImg			= 0;
	private $imgWidth			= 0;
	private $imgHeight			= 0;
	private $useFilesFolder		= USE_FILES_FOLDER;
	private $filesFolder		= "";
	private $sortList			= "datedsc";
	private $filter				= "";
	private $pubFilter			= "";
	public $limit				= 10;
	private $sortRes			= "ORDER BY `mod_date` DESC";
	private $dataNav			= "";
		
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		// Events listeners registrieren
		$this->addEventListeners("admincampaigns");
		
		// ExtendMainEvent
		$this->o_extendCampaignsEvent	= new ExtendCampaignsEvent($this->DB, $this->o_lng);

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:admincampaigns}' . PHP_EOL . 
									$this->closeTag("#headerBox");
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
	
		$this->formAction	 	=	ADMIN_HTTP_ROOT . '?task=campaigns&type=newsl';

		$this->adminContent .= 	'<h2 class="cc-section-heading cc-h2">{s_header:campaigns}</h2>' . PHP_EOL . 
								$this->getAdminMenu(3) . // Menü
								'<p>&nbsp;</p>' . PHP_EOL . 
								'<p>&nbsp;</p>' . PHP_EOL . 
								'<p>&nbsp;</p>' . PHP_EOL;
		


		// Zurückbuttons
		$this->adminContent .=	$this->getBackButtons(parent::$type);
		
		// Script code Toogle Dashboard
		$this->adminContent	.= $this->getScriptCode();		
		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();

		
		return $this->adminContent;

	}

	
	// getBackButtons
	public function getBackButtons($type = "")
	{

		// Zurückbuttons
		$output =	'<p>&nbsp;</p>' . PHP_EOL . 
					'<div class="adminArea">' . PHP_EOL . 
					'<ul><li class="submit back">' . PHP_EOL;
		
		
		// Falls ein Newsletter zum Bearbeiten ausgewählt ist, back buttons anzeigen
		if(!empty($type)) {

			// Button back
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=campaigns',
									"id"		=> "backtomain",
									"class"		=> "left",
									"text"		=> "{s_button:admincampaigns}",
									"icon"		=> "campaigns"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);
		}
		
		// Button back
		$output .=	$this->getButtonLinkBacktomain();
				
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'</li></ul><p>&nbsp;</p>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL . 
					'</div>' . PHP_EOL;
		
		return $output;
	
	}

	
	// getScriptCode
	protected function getScriptCode()
	{
	
		return	'<script>head.ready("ccInitScript", function(){' . PHP_EOL .
				'$(document).ready(function(){' . PHP_EOL .
				'$.addInitFunction({name: "$.toggleDashboard", params: ""});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
