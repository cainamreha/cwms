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
		$this->adminHeader		=	'{s_text:adminmodules}' . PHP_EOL . 
									$this->closeTag("#headerBox");

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
		$output	= 	'<h2 class="cc-section-heading cc-h2">{s_header:modules}</h2>' . PHP_EOL . 
					$this->getAdminMenu(2) . // Menü
					'<p>&nbsp;</p>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL;

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
		$output		=	'<p>&nbsp;</p>' . PHP_EOL . 
						'<div class="adminArea">' . PHP_EOL . 
						'<ul><li class="submit back">' . PHP_EOL;
							
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
				
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'</li>' . PHP_EOL . 
					'</ul>' . PHP_EOL . 
					'<p>&nbsp;</p>' . PHP_EOL . 
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
