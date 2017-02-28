<?php
namespace Concise;



###################################################
##############  Pluginverwaltung  #################
###################################################

// Plug-Ins verwalten

class Admin_Update extends Admin implements AdminTask
{

	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminupdate}' . PHP_EOL . 
									$this->closeTag("#headerBox");

		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		
		$this->adminContent 	.=	'<div class="adminArea">' . PHP_EOL;
		

		$formAction		= ADMIN_HTTP_ROOT . '?task=update';
		$showBackButton = true;
		$setLock 		= array(true);		
		$showBackButton = false;
		

		$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:coreupdates}</h2>' . PHP_EOL .
								'<div class="updateList">' . PHP_EOL;
		
		// Update
		if(CC_UPDATE_CHECK) {
		
			require_once PROJECT_DOC_ROOT."/inc/classes/Update/class.LiveUpdate.php"; // Klasse LiveUpdate einbinden
			
			$o_update		= new LiveUpdate($this->DB, $this->o_lng, $this->installedPlugins);
			$o_update->initLiveUpdater(true, false);
			
			// Update output
			$this->adminContent .=	$o_update->getUpdate(true, false);		
		
			// Plugin updates
			if(count($this->installedPlugins) > 0) {
			
				$this->adminContent .=	'</div>' . PHP_EOL;
				$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:pluginupdates}</h2>' . PHP_EOL .
										'<div class="updateList">' . PHP_EOL;
				
				$this->adminContent .=	$o_update->getUpdate(false, true);
			}
		}
		else {
			$this->adminContent	.=	'<p class="framedParagraph">' .
									parent::getIcon("concise", "inline-icon") .
									'<span class="versionHint padding-left"><strong>{s_hint:currentversion}: ' . CWMS_VERSION . '</strong></span></p>' . "\n";
		
		}
		
		$this->adminContent .=	'</div>' . PHP_EOL;
		$this->adminContent .=	'</div>' . PHP_EOL;



		// Zurückbuttons
		$this->adminContent .=	'<p>&nbsp;</p>' . PHP_EOL . 
								'<div class="adminArea">' . PHP_EOL . 
								'<ul>' . PHP_EOL .
								'<li class="submit back">' . PHP_EOL;

		if($showBackButton) {
			
			// Button back
			$btnDefs	= array(	"href"		=> $formAction,
									"class"		=> "left",
									"text"		=> "{s_button:back}",
									"icon"		=> "backtolist"
								);
			
			$this->adminContent .=	parent::getButtonLink($btnDefs);
		}
		
		// Button back
		$this->adminContent .=	$this->getButtonLinkBacktomain();
				
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL .
								'</ul>' . PHP_EOL .
								'</div>' . PHP_EOL;
	
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		return $this->adminContent;

	}
}
