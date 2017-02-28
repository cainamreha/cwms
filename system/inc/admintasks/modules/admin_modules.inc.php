<?php
namespace Concise;



###################################################
###############  Modules-Bereich  #################
###################################################

// Module verwalten 

class Admin_Modules extends Admin implements AdminTask
{

	private $allowedFileSizeStr;
	private $forcedFileCat	= "";
	private $minImgSize		= MIN_IMG_SIZE;
	private $maxImgSize		= MAX_IMG_SIZE;
	private $uploadMethod	= "auto";

	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
	
	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminmodules}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";

		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		
		// Notifications
		$this->notice 	= $this->getSessionNotifications("notice");
		$this->hint		= $this->getSessionNotifications("hint");

		// Modules overview
		$this->adminContent	.= $this->getModulesMainCon();
		$this->adminContent	.= $this->getBackButtons(parent::$type);
		$this->adminContent	.= $this->getScriptCode();		
		
		$this->adminContent	.= $this->closeAdminContent();

		return $this->adminContent;

	}
	


	/**
	 * Modulübersicht ausgeben
	 * 
	 * @access protected
	 * @return string
	 */
	protected function getModulesMainCon()
	{
		// Andernfalls Modulübersicht
		$output	= 	'<h2 class="cc-section-heading cc-h2">{s_header:modules}</h2>' . "\r\n" . 
					$this->getAdminMenu(2) . // Menü
					'<p>&nbsp;</p>' . "\r\n" . 
					'<p>&nbsp;</p>' . "\r\n" . 
					'<p>&nbsp;</p>' . "\r\n";

		return $output;
	
	}
	


	/**
	 * getBackButtons
	 * 
	 * @access public
	 * @return string
	 */
	public function getBackButtons($type = false)
	{

		// Zurückbuttons
		$output		=	'<p>&nbsp;</p>' . "\r\n" . 
						'<div class="adminArea">' . "\r\n" . 
						'<ul><li class="submit back">' . "\r\n";
							
		if(!empty($type)) { // Falls eine Modulunterseite angezeigt wird, zurückbutton anzeigen
		
			// Button back
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules',
									"class"		=> "modules left",
									"text"		=> "{s_button:adminmod}",
									"icon"		=> "modules"
								);
			
			$output	.=	parent::getButtonLink($btnDefs);
			
		}
		
		// Button back
		$output .=	$this->getButtonLinkBacktomain();
				
		$output .=	'<br class="clearfloat" />' . "\r\n" .
					'</li>' . "\r\n" . 
					'</ul>' . "\r\n" . 
					'<p>&nbsp;</p>' . "\r\n" . 
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
