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
		$this->adminHeader		=	'{s_text:adminhelp}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";
							
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		$this->adminContent		.=	'<div class="adminArea">' . "\r\n" .
									'<h2 class="cc-section-heading cc-h2">{s_header:adminhelp}</h2>'. "\r\n";



		$helpList	= "";
		$helpDetail	= "";

		
		foreach($this->adminPages as $adminCat => $array) {
			
			if($adminCat != "changes" && $adminCat != "help" && in_array($this->g_Session['group'], $this->adminPages[$adminCat]['access']) && !in_array($adminCat, $this->adminPlugins)) {
				
				// Gruppenüberschrift einbinden
				if($adminCat == "new") 
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminpages}</h3>'. "\r\n";
					
				if($adminCat == "tpl")
					$helpList	.=	'<h3 class="cc-h3">{s_header:admintpl}</h3>'. "\r\n";
					
				if($adminCat == "modules")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminmod}</h3>'. "\r\n";
					
				if($adminCat == "forms")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminforms}</h3>'. "\r\n";
					
				if($adminCat == "file")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminfile}</h3>'. "\r\n";
					
				if($adminCat == "campaigns")
					$helpList	.=	'<h3 class="cc-h3">{s_header:admincampaigns}</h3>'. "\r\n";
					
				if($adminCat == "langs")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminlangs}</h3>'. "\r\n";
					
				if($adminCat == "user")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminuser}</h3>'. "\r\n";
					
				if($adminCat == "settings")
					$helpList	.=	'<h3 class="cc-h3">{s_header:adminsettings}</h3>'. "\r\n";
					
				// Hilfeseiten auflisten
				$helpList	.=	'<li>' .
								'<a href="#'.$adminCat.'">' .
								parent::getIcon($adminCat, 'menuicon-' . $adminCat . ' inline-icon', 'style="background-image:url(' . SYSTEM_IMAGE_DIR . '/' . $adminCat . '_symbol.png)"') .
								'{s_nav:admin'.$adminCat.'}' .
								'</a>' .
								'</li>' . "\r\n";
				
				// Hilfeseiten Details
				$helpDetail	.=	'<span id="'.$adminCat.'" class="ancor">&nbsp;</span>' .
								'<h2 class="cc-section-heading cc-h2">' .
								parent::getIcon($adminCat, 'menuicon-' . $adminCat . ' inline-icon-big right', 'style="background-image:url(' . SYSTEM_IMAGE_DIR . '/' . $adminCat . '_symbol.png)"') .
								'{s_nav:admin'.$adminCat.'}' .
								'</h2>' .
								'<li>'. "\r\n" .
								($adminCat == "edit" || $adminCat == "tpl" ? '{s_text:admin' . $adminCat . '}<i>Step 1</i>' : '') . '{s_text:admin'.$adminCat.($adminCat == "edit" || $adminCat == "tpl" ? '1}<i>Step 2</i>{s_text:adminedit2}' : '}') . '</li>{up}' . "\r\n";
								
			}
			
		}

		$this->adminContent .=	'<ul class="helpList framedItems">'.$helpList.'</ul>' . "\r\n";

		$this->adminContent .=	'<ul class="helpDetails">'.$helpDetail.'</ul>' . "\r\n";

		$this->adminContent .=	'</div>' . "\r\n";


		$this->adminContent .=	'<p>&nbsp;</p>' . "\r\n" . 
								'<div class="adminArea">' . "\r\n" . 
								'<ul>' . "\r\n" .
								'<li class="submit back">' . "\r\n";
		
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
