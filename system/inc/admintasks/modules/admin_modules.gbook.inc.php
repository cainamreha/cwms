<?php
namespace Concise;



###################################################
##############  Modules-Guestbook  ################
###################################################

require_once SYSTEM_DOC_ROOT."/inc/admintasks/modules/admin_modules.inc.php"; // AdminModules-Klasse einbinden

// Gästebuch verwalten 

class Admin_ModulesGbook extends Admin_Modules implements AdminTask
{

	private $tableGbook		= "gbook";
	private $tableComments	= "comments";

	public function __construct($DB, $o_lng, $task, $init = false)
	{
	
		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng, $task, $init);
		
		parent::$task = $task;
		
		$this->tableGbook		= DB_TABLE_PREFIX . $this->tableGbook;
		$this->tableComments	= DB_TABLE_PREFIX . $this->tableComments;
		
		$this->headIncludeFiles['commenteditor']	= true;

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Löschen von Einträgen
		if(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] == "del") {
			
			$delId = $this->DB->escapeString($GLOBALS['_GET']['id']);
			
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `$this->tableGbook`, `$this->tableComments`");

			// Löschen des Eintrags
			$deleteSQL1 = $this->DB->query("DELETE FROM `$this->tableGbook`  
												WHERE id = " . $delId . " 
												");
			
			// Löschen des Eintrags
			$deleteSQL2 = $this->DB->query("DELETE FROM `$this->tableComments`  
												WHERE `table` = 'gbook' 
												AND `entry_id` = " . $delId . " 
												");
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");

			if($deleteSQL1 == true) {
				$this->notice = ContentsEngine::replaceStaText("{s_notice:delgb}");
				$this->setSessionVar('notice', $this->notice);
			}
			else {		
				$this->error = ContentsEngine::replaceStaText("{s_error:delgb}");
				$this->setSessionVar('hint', $this->error);
			}
			
			// Falls vom Frontend aus gelöscht wurde, zur Seite zurückgehen
			if(isset($GLOBALS['_GET']['red'])) {

				$redirect = urldecode($GLOBALS['_GET']['red']);
				
				if($redirect == "")
					$redirect = "admin?task=modules&type=gbook";
				
				if(isset($GLOBALS['_GET']['totalRows']) && isset($GLOBALS['_GET']['pageNum'])) {
				
					$totRows = intval($GLOBALS['_GET']['totalRows'])-1;
					$pageNum = intval($GLOBALS['_GET']['pageNum']);
					$limit = $totRows/GBOOK_MAX_ROWS;
					
					if($pageNum > 0 && $limit <= 1)
						$pageNum--;
				}
				
				header("Location: " . PROJECT_HTTP_ROOT . "/" . $redirect . (isset($totRows) ? '?totalRows=' . $totRows : '') . (isset($pageNum) ? '&pageNum=' . $pageNum : ''));
				exit;
			}
		}


		$this->adminHeader		=	'{s_text:admingbook}' . PHP_EOL . 
									$this->closeTag("#headerBox");
							
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		$this->adminContent    .=	'<div class="adminArea gbook">' . PHP_EOL;


		if(isset($this->notice) && $this->notice != "")
			$this->adminContent .='<p class="notice success">' . $this->notice . '</p>' . PHP_EOL;
			
			
		// Zunächst das entsprechende Modul einbinden (Search-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Guestbook.php";

		$guestbook = new Guestbook($this->DB, $this->o_lng, $this->loggedUserGroup, "admin");

		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_header:gbentry}</h2>' . PHP_EOL . 
								'<div class="form">' . PHP_EOL .
								$guestbook->getGuestbook($this->loggedUserGroup, 10) .
								'</div></div>' . PHP_EOL;
	

		$this->adminContent	.= $this->getBackButtons(parent::$type);
		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();

		return $this->adminContent;
	
	}

}
