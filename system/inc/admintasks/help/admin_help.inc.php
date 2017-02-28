<?php
namespace Concise;



###################################################
#################  Hilfe-Seite  ###################
###################################################

// Hilfe zu Concise WMS

class Admin_Help extends Admin implements AdminTask
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
		$this->adminHeader		=	'{s_text:adminhelp}' . PHP_EOL . 
									$this->closeTag("#headerBox");
							
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		$this->adminContent		.=	'<div class="adminArea">' . PHP_EOL .
									'<h2 class="cc-section-heading cc-h2">{s_header:adminhelp}</h2>'. PHP_EOL;



		$helpList	= "";
		$helpDetail	= "";

		
		foreach($this->adminPages as $adminCat => $array) {
			
			if($adminCat != "changes" && $adminCat != "help" && in_array($this->g_Session['group'], $this->adminPages[$adminCat]['access']) && !in_array($adminCat, $this->adminPlugins)) {
				
				// Gruppenüberschrift einbinden
				if($adminCat == "new") 
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminpages}</h3>'. PHP_EOL;
					
				if($adminCat == "tpl")
					$helpList	.=	'<h3 class="cc-h3">{s_header:admintpl}</h3>'. PHP_EOL;
					
				if($adminCat == "modules")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminmod}</h3>'. PHP_EOL;
					
				if($adminCat == "forms")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminforms}</h3>'. PHP_EOL;
					
				if($adminCat == "file")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminfile}</h3>'. PHP_EOL;
					
				if($adminCat == "campaigns")
					$helpList	.=	'<h3 class="cc-h3">{s_header:admincampaigns}</h3>'. PHP_EOL;
					
				if($adminCat == "langs")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminlangs}</h3>'. PHP_EOL;
					
				if($adminCat == "user")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminuser}</h3>'. PHP_EOL;
					
				if($adminCat == "settings")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminsettings}</h3>'. PHP_EOL;
					
				// Hilfeseiten auflisten
				$helpList	.=	'<li>' .
								'<a href="#'.$adminCat.'">' .
								parent::getIcon($adminCat, 'menuicon-' . $adminCat . ' inline-icon', 'style="background-image:url(' . SYSTEM_IMAGE_DIR . '/' . $adminCat . '_symbol.png)"') .
								'{s_nav:admin'.$adminCat.'}' .
								'</a>' .
								'</li>' . PHP_EOL;
				
				// Hilfeseiten Details
				$helpDetail	.=	'<span id="'.$adminCat.'" class="ancor">&nbsp;</span>' .
								'<h2 class="cc-section-heading cc-h2">' .
								parent::getIcon($adminCat, 'menuicon-' . $adminCat . ' inline-icon-big right', 'style="background-image:url(' . SYSTEM_IMAGE_DIR . '/' . $adminCat . '_symbol.png)"') .
								'{s_nav:admin'.$adminCat.'}' .
								'</h2>' .
								'<li>'. PHP_EOL .
								($adminCat == "edit" || $adminCat == "tpl" ? '{s_text:admin' . $adminCat . '}<i>Step 1</i>' : '') . '{s_text:admin'.$adminCat.($adminCat == "edit" || $adminCat == "tpl" ? '1}<i>Step 2</i>{s_text:adminedit2}' : '}') . '</li>{up}' . PHP_EOL;
								
			}
			
		}

		$this->adminContent .=	'<ul class="helpList framedItems">'.$helpList.'</ul>' . PHP_EOL;

		$this->adminContent .=	'<ul class="helpDetails">'.$helpDetail.'</ul>' . PHP_EOL;

		$this->adminContent .=	'</div>' . PHP_EOL;


		$this->adminContent .=	'<p>&nbsp;</p>' . PHP_EOL . 
								'<div class="adminArea">' . PHP_EOL . 
								'<ul>' . PHP_EOL .
								'<li class="submit back">' . PHP_EOL;
		
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
