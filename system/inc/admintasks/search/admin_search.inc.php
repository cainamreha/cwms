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
		$this->adminHeader	=	'{s_text:adminsearch}' . "\r\n" . 
								'</div><!-- Ende headerBox -->' . "\r\n";
							
		// #adminContent
		$this->adminContent =	$this->openAdminContent();

		$this->adminContent .=	'<div class="adminArea">' . "\r\n" .
								'<h2 class="cc-section-heading cc-h2">{s_label:search}</h2>' . "\r\n";
								
		
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();
		
		
		$this->adminContent .=	'<h3 class="cc-h3">{s_header:search}</h3>' . "\r\n" .
								'<div class="dataList search adminBox">' . "\r\n";
				
		$search = new Search($this->DB, $this->o_lng, "LIKE");
		$this->adminContent .=	str_replace("<>", "", $search->getSearch("big", true));

		$this->adminContent .=	'</div>' . "\r\n";
							
		$this->adminContent .=	'</div>' . "\r\n";


		$this->adminContent .=	'<div class="adminArea">' . "\r\n" .
								'<p>&nbsp;</p>' . "\r\n" .
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
