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
		$this->adminHeader		=	'{s_text:adminupdate}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";

		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		
		$this->adminContent 	.=	'<div class="adminArea">' . "\r\n";
		

		$formAction		= ADMIN_HTTP_ROOT . '?task=update';
		$showBackButton = true;
		$setLock 		= array(true);		
		$showBackButton = false;
		

		$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:coreupdates}</h2>' . "\r\n" .
								'<div class="updateList">' . "\r\n";
		
		// Update
		if(CC_UPDATE_CHECK) {
		
			require_once PROJECT_DOC_ROOT."/inc/classes/Update/class.LiveUpdate.php"; // Klasse LiveUpdate einbinden
			
			$o_update		= new LiveUpdate($this->DB, $this->installedPlugins);
			$o_update->initLiveUpdater(true, false);
			
			// Update output
			$this->adminContent .=	$o_update->getUpdate(true, false);		
		
			// Plugin updates
			if(count($this->installedPlugins) > 0) {
			
				$this->adminContent .=	'</div>' . "\r\n";
				$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_header:pluginupdates}</h2>' . "\r\n" .
										'<div class="updateList">' . "\r\n";
				
				$this->adminContent .=	$o_update->getUpdate(false, true);
			}
		}
		else {
			$this->adminContent	.=	'<p class="framedParagraph">' .
									parent::getIcon("concise", "inline-icon") .
									'<span class="versionHint padding-left"><strong>{s_hint:currentversion}: ' . CWMS_VERSION . '</strong></span></p>' . "\n";
		
		}
		
		$this->adminContent .=	'</div>' . "\r\n";
		$this->adminContent .=	'</div>' . "\r\n";



		// Zurückbuttons
		$this->adminContent .=	'<p>&nbsp;</p>' . "\r\n" . 
								'<div class="adminArea">' . "\r\n" . 
								'<ul>' . "\r\n" .
								'<li class="submit back">' . "\r\n";

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
				
		$this->adminContent .=	'<br class="clearfloat" />' . "\r\n" .
								'</li>' . "\r\n" . 
								'</ul>' . "\r\n" . 
								'</div>' . "\r\n";
	
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		return $this->adminContent;

	}
}
