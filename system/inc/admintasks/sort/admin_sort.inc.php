<?php
namespace Concise;



###################################################
################  Sort-Bereich  ###################
###################################################

// Step 1

class Admin_Sort extends Admin implements AdminTask
{

	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		// Datenbank-Engine auf InnoDB setzen
		$this->setDbEngine(DB_TABLE_PREFIX . parent::$tablePages, "InnoDB");

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminsort}' . PHP_EOL . 
									$this->closeTag("#headerBox");

		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		

		// Locking checken
		if($this->checkLocking("all", "editpages", $this->g_Session['username'], '{s_error:lockpages}')) {
			$this->adminContent .=	$this->getBackButtons("main");
			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();
			return $this->adminContent;
		}
			
		
		// Falls kein Lock
		// Sort up
		if(!empty($GLOBALS['_POST']['sortup_id'])
		&& is_numeric($GLOBALS['_POST']['sortup_id'])
		) {
			
			$sortId	= $GLOBALS['_POST']['sortup_id'];
			setcookie('sort_id', $sortId);
			
			$this->sortPageUp($sortId);
		
		}

		// Sort down
		elseif(!empty($GLOBALS['_POST']['sortdown_id'])
		&& is_numeric($GLOBALS['_POST']['sortdown_id'])
		) {
			
			$sortId = $GLOBALS['_POST']['sortdown_id'];
			setcookie('sort_id', $sortId);
			
			$this->sortPageDown($sortId);
		
		}


		$this->adminContent .=	'<div class="adminArea sortPages">' . PHP_EOL .					
								'<h2 class="cc-section-heading cc-h2">{s_nav:adminsort}</h2>' . PHP_EOL;
		
		
		
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();


		
		$this->adminContent .=	'<h3 class="cc-h3">{s_header:mainmenu}</h3>' . PHP_EOL .
							$this->listPages("sort");
		
		$this->adminContent .=	'<h3 class="cc-h3">{s_header:topmenu}</h3>' . PHP_EOL .
							$this->listPages("sort", 2);
		
		$this->adminContent .=	'<h3 class="cc-h3">{s_header:footmenu}</h3>' . PHP_EOL .
							$this->listPages("sort", 3);
							
		$this->adminContent .=	'<h3 class="cc-h3">{s_header:nonmenu}</h3>' . PHP_EOL .
							$this->listPages("sort", 0);
		
		$this->adminContent .=	'</div>' . PHP_EOL;
		
		// Zurückbuttons
		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL . 
								'<p>&nbsp;</p>' . PHP_EOL . 
								'<p>&nbsp;</p>' . PHP_EOL . 
								'<ul>' . PHP_EOL .
								'<li class="submit back">' . PHP_EOL;
		// Button back (new)
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=new',
								"text"		=> "{s_button:adminnew}",
								"icon"		=> "new"
							);
		
		$this->adminContent	.=	parent::getButtonLink($btnDefs);
		
		// Button back (edit)
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=edit',
								"class"		=> "right",
								"text"		=> "{s_nav:adminedit}",
								"icon"		=> "edit"
							);
		
		$this->adminContent	.=	parent::getButtonLink($btnDefs);
								
		$this->adminContent	.=	'</li>' . PHP_EOL . 
								'<li class="submit back">' . PHP_EOL;
		
		// Button back
		$this->adminContent .=	$this->getButtonLinkBacktomain();
				
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL . 
								'</ul>' . PHP_EOL . 
								'<p>&nbsp;</p>' . PHP_EOL . 
								'<p>&nbsp;</p>' . PHP_EOL . 
								'</div>' . PHP_EOL;
	
				
		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();

				
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= $this->getSortRightBarContents();
		
		
		return $this->adminContent;

	}
	
	
	// sortPageUp
	public function sortPageUp($sortId)
	{

		if(!is_numeric($sortId))
			return false;
		
		require_once SYSTEM_DOC_ROOT."/inc/adminclasses/class.SortPages.php"; // SortPages-Klasse
		
		$o_sortPages	= new SortPages($this->DB, parent::$tablePages);
		
		return $o_sortPages->sortPageUp($sortId);
	
	}
	
	
	// sortPageDown
	public function sortPageDown($sortId)
	{

		if(!is_numeric($sortId))
			return false;
		
		require_once SYSTEM_DOC_ROOT."/inc/adminclasses/class.SortPages.php"; // SortPages-Klasse
		
		$o_sortPages	= new SortPages($this->DB, parent::$tablePages);
		
		return $o_sortPages->sortPageDown($sortId);

	}

	
	// getSortRightBarContents
	private function getSortRightBarContents()
	{
	
		// Panel for rightbar
		$output	= "";
		
		// Back to list
		$output .=	'<div class="controlBar">' . PHP_EOL;
		
		// Button new page
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=new',
								"class"		=> "{t_class:btnpri} {t_class:btnblock} {t_class:marginbs}",
								"text"		=> "{s_button:adminnew}",
								"attr"		=> 'data-ajax="true"',
								"icon"		=> "new"
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		// Button edit pages
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=edit',
								"class"		=> "{t_class:btnpri} {t_class:btnblock}",
								"text"		=> "{s_header:adminpages} & {s_header:contents}",
								"attr"		=> 'data-ajax="true"',
								"icon"		=> "edit"
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		$output .=	'</div>' . PHP_EOL;
		
		return $output;
		
	}

}
