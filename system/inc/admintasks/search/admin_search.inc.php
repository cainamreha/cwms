<?php
namespace Concise;



###################################################
##################  db-Backup  ####################
###################################################

// Datenbank updates verwalten

class Admin_Search extends Admin implements AdminTask
{

	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		require_once PROJECT_DOC_ROOT . "/inc/classes/Modules/class.Search.php";

		parent::$task = $task;

	}
	
	
	public function getTaskContents($ajax = false)
	{
	
		$this->scriptFiles[] = "access/js/ajaxSearch.js"; // js-Datei einbinden
		

		// Enthält Headerbox
		$this->adminHeader	=	'{s_text:adminsearch}' . PHP_EOL . 
								$this->closeTag("#headerBox");
							
		// #adminContent
		$this->adminContent =	$this->openAdminContent();

		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL .
								'<h2 class="cc-section-heading cc-h2">{s_label:search}</h2>' . PHP_EOL;
								
		
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();
		
		
		$this->adminContent .=	'<h3 class="cc-h3">{s_header:search}</h3>' . PHP_EOL .
								'<div class="dataList search adminBox">' . PHP_EOL;
				
		$search = new Search($this->DB, $this->o_lng, "LIKE");
		$this->adminContent .=	str_replace("<>", "", $search->getSearch("big", true));

		$this->adminContent .=	'</div>' . PHP_EOL;
							
		$this->adminContent .=	'</div>' . PHP_EOL;


		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL .
								'<p>&nbsp;</p>' . PHP_EOL .
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
