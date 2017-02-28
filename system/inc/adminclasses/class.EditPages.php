<?php
namespace Concise;


###############################################
################  EditPages  ##################
###############################################

// EditPages

class EditPages extends Admin
{

	private $action				= "";
	private $redirect			= "";
	
	public function __construct($DB, $o_lng)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);

	}
	
	public function conductAction()
	{
	
		// Action
		if(!empty($GLOBALS['_GET']['action']))
			$this->action		= $GLOBALS['_GET']['action'];

		// Edit-Id
		if(isset($_GET['page_id']) && $_GET['page_id'] != "") {
			$this->editID		= $_GET['page_id'];
			$this->editIdDB		= $this->DB->escapeString($this->editID);
		}
		
		// Redirect
		if(isset($GLOBALS['_GET']['red']) && $GLOBALS['_GET']['red'] != "")
			$this->redirect = urldecode($GLOBALS['_GET']['red']);

		
		// Edit entry
		if($this->action == "changetpl")
			return $this->changePageTpl();

		
		// Update check
		if($this->action == "updatecheck") {
			@setcookie("updateCheckDone", true, time()+ parent::updateInterval, "/");
			$upd	= $this->checkForUpdates();
			echo $upd ? "1" : "0";
			exit;
		}

	}
	
	
	// changePageTpl
	public function changePageTpl()
	{
	
		if(empty($GLOBALS['_POST']['template']))
			return false;
		
		$template	= $GLOBALS['_POST']['template'];
		$templateDb	= $this->DB->escapeString($template);
		
		// Templates auflisten
		// Existing Tempates
		$this->existTemplates	= Admin::readTemplateDir();
		
		$tplArr	= array_unique(array_merge(array_intersect($this->defaultTemplates, $this->existTemplates), $this->existTemplates));
		
		if(!in_array($template, $tplArr))
			return false;
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . parent::$tablePages . "`");
	
	
		// db-Update der Pages Tabelle
		$updateSQL1 = $this->DB->query("UPDATE " . DB_TABLE_PREFIX . parent::$tablePages . " 
											SET	`template` = '$templateDb'
												WHERE `page_id` = '$this->editIdDB'
											");
		
		#die(var_dump($updateSQL1));
		
		// db-Sperre aufheben
		$unLock		= $this->DB->query("UNLOCK TABLES");

		$this->redirectPage();
	
	}

	
	// redirectPage
	private function redirectPage()
	{

		// Falls ein Redirect(ziel) per GET-Parameter mitgegeben wurde (FE-Mode)			
		header("location: " . $this->redirect);
		exit;
		return true;
	
	}

} // end class EditPages
