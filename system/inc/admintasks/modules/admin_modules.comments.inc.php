<?php
namespace Concise;



###################################################
##############  Modules-Comments  #################
###################################################

require_once SYSTEM_DOC_ROOT."/inc/admintasks/modules/admin_modules.inc.php"; // AdminModules-Klasse einbinden

// Kommentare verwalten 

class Admin_ModulesComments extends Admin_Modules implements AdminTask
{

	public function __construct($DB, $o_lng, $task, $init = false)
	{
	
		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng, $task, $init);
		
		parent::$task = $task;
		
		$this->headIncludeFiles['commenteditor']	= true;

	}
	
	
	public function getTaskContents($ajax = false)
	{

		if(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] == "del") {
			
			$delId = $GLOBALS['_GET']['id'];
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "comments`");

			// Löschen des Kommentars
			$deleteSQL = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "comments` 
												WHERE id = " . $delId . " 
												");
			
			#var_dump($query);
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");

			if($deleteSQL == true) {
				$this->notice = ContentsEngine::replaceStaText("{s_notice:delcom}");
				$this->setSessionVar('notice', $this->notice);
			}
			else {		
				$this->error = ContentsEngine::replaceStaText("{s_error:delcom}");
				$this->setSessionVar('hint', $this->error);
			}
			
			
			// Falls vom Frontend aus gelöscht wurde, zur Seite zurückgehen
			if(isset($GLOBALS['_GET']['red'])) {

				$redirect = urldecode($GLOBALS['_GET']['red']);
				
				if($redirect == "")
					$redirect = "admin?task=modules&type=comments";
				
				if(isset($GLOBALS['_GET']['totalRows']) && isset($GLOBALS['_GET']['pageNum'])) {
				
					$totRows = intval($GLOBALS['_GET']['totalRows'])-1;
					$pageNum = intval($GLOBALS['_GET']['pageNum']);
					$limit = $totRows/COMMENTS_MAX_ROWS;
					
					if($pageNum > 0 && $limit <= 1)
						$pageNum--;
				}
				
				header("Location: " . PROJECT_HTTP_ROOT . "/" . $redirect . (isset($totRows) ? '?totalRows=' . $totRows : '') . (isset($pageNum) ? '&pageNum=' . $pageNum : ''));
				exit;
			}

		}


		$this->adminHeader		=	'{s_text:admincomments}' . PHP_EOL . 
									$this->closeTag("#headerBox");
							
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		
		$this->adminContent    .=	'<div class="adminArea comments">' . PHP_EOL;
		
		
		if(isset($this->notice) && $this->notice != "")
			$this->adminContent .='<p class="notice success">' . $this->notice . '</p>' . PHP_EOL;
			
		$this->adminContent .=	'<h2 class="toggle cc-section-heading cc-h2">{s_option:comments}</h2>' . PHP_EOL;

			
		// Zunächst das entsprechende Modul einbinden (Search-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";

		if(isset($GLOBALS['_POST']['ct_table']))
			$comTable = $GLOBALS['_POST']['ct_table'];
		elseif(isset($this->g_Session['ct_table']))
			$comTable = $this->g_Session['ct_table'];
		else
			$comTable = "all";
			
		$comments = new Comments($this->DB, $this->o_lng, $comTable, "", $this->g_Session['group']);

		$this->adminContent .=	$comments->getComments($comTable, "all", 10) .
								'</div>' . PHP_EOL;

								
		$this->adminContent	.= $this->getBackButtons(parent::$type);

		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		return $this->adminContent;
	
	}

}

