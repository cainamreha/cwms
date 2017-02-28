<?php
namespace Concise;


###################################################
##################  db-Backup  ####################
###################################################

// Datenbank updates verwalten

class Admin_Bkp extends Admin implements AdminTask
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
		$this->adminHeader		=	'{s_text:adminbkp}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		
		$this->formAction		= ADMIN_HTTP_ROOT . '?task=bkp';

		// Backup anlegen
		if(isset($GLOBALS['_POST']['new_bkp']) && $GLOBALS['_POST']['new_bkp'] == "new_bkp") {
			
			$this->makeDbBackup(isset($GLOBALS['_GET']['bkp']) ? $GLOBALS['_GET']['bkp'] : null);

		}
		
		// Backup wieder herstellen
		elseif(!empty($GLOBALS['_POST']['restore_bkp']) && $this->g_Session['group'] == "admin") {

			$this->restoreDbBackup($GLOBALS['_POST']['restore_bkp']);
		
		}
		
		// Backup löschen
		elseif(!empty($GLOBALS['_POST']['del_bkp']) && $this->g_Session['group'] == "admin") {
			
			$this->deleteDbBackups($GLOBALS['_POST']['del_bkp']);

		}


		// Backupseite ausgeben
		$this->adminContent .=	'<div class="adminArea">' . "\r\n";

		if(!empty($this->error))
			$this->adminContent .= '<p class="notice error">' . $this->error . '</p>' . "\r\n";
			
		if(!empty($this->notice))
			$this->adminContent .= '<p class="notice success">' . $this->notice . '</p>' . "\r\n";
		

		// Neues Backup
		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_label:newbkp}</h2>' . "\r\n" . 
								'<ul class="editList dbBackup cc-list cc-list-large">' . "\r\n" . 
								'<li class="listItem">' . "\r\n" .
								'<span class="listName">{s_label:newbkpfull}</span>' . "\r\n";
		
		$this->adminContent .=	'<span class="editButtons-panel">' . "\r\n";
		
		$this->adminContent .=	'<form action="' . $this->formAction . '" method="post" name="adminfm">' . "\r\n";
		
		// Button backup-global
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> 'new_bkp',
								"class"		=> 'ajaxSubmit button-icon-only',
								"value"		=> "new_bkp",
								"title"		=> '{s_title:newbkp}:<br /><strong>{s_label:newbkpfull}</strong>',
								"text"		=> '',
								"icon"		=> "backupglob"
							);
		
		$this->adminContent .=	ContentsEngine::getButton($btnDefs);
		
		$this->adminContent .=	'<input type="hidden" name="new_bkp" value="new_bkp" />' . "\r\n" .
								'<input type="hidden" name="token" value="' . parent::$token . '" />' . "\r\n" .
								'</form>' . "\r\n" .
								'</span>' . "\r\n" .
								'</li>' . "\r\n";
								
		$this->adminContent .=	'<li class="listItem">' . "\r\n" .
								'<span class="listName">{s_label:newbkpcontents}</span>' . "\r\n";
								
		$this->adminContent .=	'<span class="editButtons-panel">' . "\r\n";
		
		$this->adminContent .=	'<form action="' . $this->formAction . '&bkp=contents" method="post" name="adminfm2">' . "\r\n";
		
		// Button backup-contents
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> 'new_bkp',
								"class"		=> 'ajaxSubmit button-icon-only',
								"value"		=> "new_bkp",
								"title"		=> '{s_title:newbkp}:<br /><strong>{s_label:newbkpcontents}</strong>',
								"text"		=> '',
								"icon"		=> "backupnew"
							);
		
		$this->adminContent .=	ContentsEngine::getButton($btnDefs);
		
		$this->adminContent .=	'<input type="hidden" name="new_bkp" value="new_bkp" />' . "\r\n" .
								'<input type="hidden" name="token" value="' . parent::$token . '" />' . "\r\n" .
								'</form>' . "\r\n" .
								'</span>' . "\r\n";
		
		$this->adminContent .=	'</li>' . "\r\n" .
								'</ul>' . "\r\n";

		// Liste vorhandener Backups
		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_label:existbkp}</h2>' . "\r\n" .
								$this->getBkpActionBox() .
								MySQL::getBackups(DB_NAME, parent::$token, $this->adminLog) . 
								'<p>&nbsp;</p>' . "\r\n";
		
		
		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();

		
		$this->adminContent .=	'</div>' . "\r\n";


		// Backbutton
		$this->adminContent .=	'<div class="adminArea">' . "\r\n" .
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
	
	

	/**
	 * makeDbBackup
	 *
	 * @param string	$g_bkp	GET bkp
	 * @access protected
	 */
	protected function makeDbBackup($g_bkp = null)
	{
	
		$tables		= "";
		$logTabs	= "";
		
		// Log Tables
		for($i = 2010; $i < date("Y", time()); $i++) {
			$logTabs .=	" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."log_" . $i;
		}
		
		if($g_bkp == "contents") {
			$tables =	"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."badlogin " .
						"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."bannedip " .
						"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."user " .
						"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."log " .
						"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."log_bots " .
						"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."stats";
			
			$noteExt	= "{s_label:newbkpcontents}";
			$fileSuffix	= "_contents";
		}
		else {
			$noteExt = "{s_label:newbkpfull}";
			$fileSuffix	= "_full";
		}

		
		// Datenbankupdate durchführen
		$newBkp = $this->DB->makeBackup($tables . $logTabs, $fileSuffix);

		if($newBkp === true) {
			$this->notice = "{s_notice:bkpsuccess} (" . $noteExt . ").";
			return true;
		}
		if($newBkp == "nosystem")
			$this->error = "{s_error:bkperror}<br /><br /><strong>Check server settings to allow for <i>system()</i> command.</strong>";
		else
			$this->error = "{s_error:bkperror}";
		
		return false;

	}
	
	

	/**
	 * restoreDbBackup
	 *
	 * @param string	$restoreBkp	POST restore_bkp
	 * @access protected
	 */
	protected function restoreDbBackup($restoreBkp)
	{
		
		// Datenbankupdate durchführen
		$restoreDB = $this->DB->restoreDB($restoreBkp);

		// Falls erfolgreich
		if($restoreDB === true) {
		
			$this->notice = "{s_notice:bkprestore}<br /><br /><strong>$restoreBkp</strong><br /><br />";
			
			// Meldung von Update-Script
			if(count($this->DB->updSuccess) > 0)
				$this->notice .= '</p><p class="notice success">' . implode("<br />", $this->DB->updSuccess) . '</p>';
			if(count($this->DB->updError) > 0)
				$this->error	= implode("<br />", $this->DB->updError);
			
			// Ggf. edit_lang aus Session löschen
			$this->unsetSessionKey('edit_lang');
			
			return true;
		}
		if($restoreDB == "nosystem")
			$this->error = "{s_error:bkperror}<br /><br /><strong>Check server settings to allow for <i>system()</i> command.</strong>";
		else
			$this->error = "{s_error:bkprestore}<br /><br />" . $restoreDB;

		return false;

	}
	
	

	/**
	 * deleteDbBackups
	 *
	 * @param string	$delBkp	POST del_bkp
	 * @access protected
	 */
	protected function deleteDbBackups($delBkp)
	{
	
		$success	= true;
		
		// Datenbankupdate durchführen
		if(is_array($delBkp)) {
			foreach($delBkp as $bkpFile) {
				$delete		= $this->deleteDbBackupFile($bkpFile);
				if(!$delete)
					$success	= false;
			}
		}
		else
			$success		= $this->deleteDbBackupFile($delBkp);
		
		if($success) {
			$this->notice	= "{s_notice:bkpdel}";
			return true;
		}

		$this->error = "{s_error:bkpdel}";
		
		return false;
	
	}
	
	

	/**
	 * deleteDbBackupFile
	 *
	 * @param string	$bkpFile	Backup-Datei
	 * @access protected
	 */
	protected function deleteDbBackupFile($bkpFile)
	{
		
		// Datenbankupdate durchführen
		if(MySQL::deleteBkp($bkpFile)) {
			$this->notice = "{s_notice:bkpdel}";
			return true;
		}
		
		$this->error = "{s_error:bkpdel}";
		return false;
	
	}
	
	

	/**
	 * getBkpActionBox
	 * @access protected
	 */
	protected function getBkpActionBox()
	{

		// Checkbox zur Mehrfachauswahl zum Löschen
		$output =		'<div class="actionBox clearfix">' . "\r\n" .
						'<form action="' . $this->formAction . '&bkpfiles=array&action=" method="post" data-history="false">' . "\r\n" .
						'<label class="markAll markBox" data-mark="#dbBackupList">' . "\r\n" .
						'<input type="checkbox" id="markAllLB-form" data-select="all" /></label>' . "\r\n" .
						'<label for="markAllLB-form" class="markAllLB"> {s_label:mark}</label>' . "\r\n" .
						'<span class="editButtons-panel">' . "\r\n";
		
		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'delAll delBackups delSelectedListItems button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:delmarked}',
								"attr"		=> 'data-action="delmultiple"',
								"icon"		=> "delete"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=		'</span>' . "\r\n" .
						'</form>' . "\r\n" .
						'</div>' . "\r\n";
		
		return $output;
	
	}

}
