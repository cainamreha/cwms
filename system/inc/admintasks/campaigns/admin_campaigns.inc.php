<?php
namespace Concise;


###################################################
##############  Kampagnen-Bereich  ################
###################################################

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

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:admincampaigns}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
	
		$this->formAction	 	=	ADMIN_HTTP_ROOT . '?task=campaigns&type=newsl';

		$this->adminContent .= 	'<h2 class="cc-section-heading cc-h2">{s_header:campaigns}</h2>' . "\r\n" . 
								$this->getAdminMenu(3) . // Menü
								'<p>&nbsp;</p>' . "\r\n" . 
								'<p>&nbsp;</p>' . "\r\n" . 
								'<p>&nbsp;</p>' . "\r\n";
		


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
		$output =	'<p>&nbsp;</p>' . "\r\n" . 
					'<div class="adminArea">' . "\r\n" . 
					'<ul><li class="submit back">' . "\r\n";
		
		
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
				
		$output .=	'<br class="clearfloat" />' . "\r\n" .
					'</li></ul><p>&nbsp;</p>' . "\r\n" . 
					'<p>&nbsp;</p>' . "\r\n" . 
					'</div>' . "\r\n";
		
		return $output;
	
	}

	
	// getScriptCode
	protected function getScriptCode()
	{
	
		return	'<script>head.ready("ccInitScript", function(){' . "\r\n" .
				'$(document).ready(function(){' . "\r\n" .
				'$.addInitFunction({name: "$.toggleDashboard", params: ""});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}

}
