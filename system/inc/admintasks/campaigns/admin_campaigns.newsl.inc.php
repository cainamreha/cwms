<?php
namespace Concise;


###################################################
##############  Newsletter-Bereich  ###############
###################################################

require_once SYSTEM_DOC_ROOT."/inc/admintasks/campaigns/admin_campaigns.inc.php"; // AdminCampaigns-Klasse einbinden

// Newsletter verwalten 

class Admin_CampaignsNewsl extends Admin_Campaigns implements AdminTask
{	

	private $tableNewsl			= "newsletter";
	private $newslID			= "";
	private $newslArchive		= array();
	private $newslGroup			= "";
	private $newslGroupStr		= "";
	private $onlySubscribers	= 1;
	private $extraUsers			= array();
	private $extraEmails		= "";
	private $extraEmailsArray	= array();
	private $saveExtraEmails	= false;
	private $addedExtraEmails	= array();
	private $newslFormat		= "html";
	private $newslSubject		= "";
	private $newslText			= "";
	private $newslDate			= "";
	private $sentDate			= "";
	private $authorName			= "";
	private $authorID			= "";
	private $newslStatus		= "";
	private $attachCon			= "";
	private $objectCon			= array();
	private $fileCon			= array();
	private $fileConValue		= "";
	private $docIcon			= "";
	private $addNewsl			= false;
	private $editNewsl			= false;
	private $listNewsl			= true;
	private $noChange			= false;
	private $isSent				= false;
	private $okSent				= false;
	private $failSend			= false;
	private $resendNewsl		= false;
	private $resendMail			= array();
	private $notSentMail		= array();
	private $showImgObj			= false;
	private $showFileObj		= false;
	private $attachmentKeyWords	= array();
	public $wrongInput			= array();
	private $overwrite			= false;
	private $scaleImg			= 0;
	private $imgWidth			= 0;
	private $imgHeight			= 0;
	private $useFilesFolder		= USE_FILES_FOLDER;
	private $filesFolder		= "";
	private $sortList			= "datedsc";
	private $dbFilter			= "";
	private $pubFilter			= "all";
	public $limit				= 10;
	private $sortRes			= "ORDER BY `mod_date` DESC";
	private $dataNav			= "";
	
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng, $task, $init);
		
		parent::$task = $task;
		
		$this->tableNewsl		= DB_TABLE_PREFIX . $this->tableNewsl;
		
		$this->dbFilter			= $this->getDbFilterStr();

		$this->headIncludeFiles['newsleditor']	= true;
		
		$this->allowedTypes		= array("jpg", "png", "gif", "jpeg", "pdf", "doc", "docx", "rtf", "txt", "zip");
		$this->objectCon[0]		= "";
		$this->objectCon[1]		= "";
		$this->fileCon[0]		= "";
		$this->fileCon[1]		= "";
		$this->docIcon			= "nodoc.png";

		$this->attachmentKeyWords = explode(",", parent::replaceStaText("{s_form:attachkeywords}"));
	
	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminnewsl}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";
											
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
	
		$this->formAction	 	=	ADMIN_HTTP_ROOT . '?task=campaigns&type=newsl';

		// Notifications
		$this->notice			= $this->getSessionNotifications("notice");
		$this->hint				= $this->getSessionNotifications("hint");


		if((isset($GLOBALS['_POST']['list_newsl']) || isset($GLOBALS['_POST']['add_new'])) && isset($this->g_Session['newsl_id']))
			$this->unsetSessionKey('newsl_id');

		
		if(!empty($GLOBALS['_POST']['add_new'])) {
			$this->addNewsl		= true;
			$this->listNewsl	= false;
		}

		
		
		#######################################
		############  Newsletter  #############
		#######################################
		
		##############  Neuer Newsletter  ###############
		//
		// Falls das Formular zum Anlegen eines neuen Newsletters abgeschickt wurde
		if(isset($GLOBALS['_POST']['submit_newsl'])) {
			
			$this->addNewsl		= true;
			$this->listNewsl	= false;
			$this->verifyNewsletterPost($GLOBALS['_POST']);
		
		} // Ende if submit Newsletter
		
		
		
		##############  Newsletter bearbeiten  ###############
		//
		// Falls ein einzelner Newsletter zum bearbeiten in der Session gespeichert ist
		if(!empty($GLOBALS['_GET']['newsl_id']))
			$this->newslID	= $GLOBALS['_GET']['edit_id'];
		
		elseif(!empty($this->g_Session['newsl_id']))
			$this->newslID	= $this->g_Session['newsl_id'];
			
		
		// Newsletter bearbeiten
		$this->editNewsletter($this->newslID);

		
		// Falls kein zu bearbeitender Newsletter, Newsl auflisten
		if(!is_numeric($this->newslID)) {
			
			// Newsletter bearbeiten
			$this->queryNewsletters();
		
		}
		else {
			$this->editNewsl	= true;
			$this->listNewsl	= false;
		}
			
		
		
		// Falls ein Lock besteht
		if($this->noChange) {
			if(isset($this->g_Session['newsl_id']))
				$this->unsetSessionKey('newsl_id');
			
			$this->adminContent .=	$this->getBackButtons(parent::$type);
			
			// #adminContent close
			$this->adminContent	.= $this->closeAdminContent();
			
			return $this->adminContent;
		}
		
		
		// Newsletter-Bereich
		$this->adminContent .=	'<div class="adminArea">' . "\r\n";
		
		
		if(!empty($this->notice))
			$this->adminContent .=	$this->getNotificationStr($this->notice, "success");
		
		
		// Hinweismeldung ausgeben
		if(!empty($this->hint)) {
			// Falls E-Mailadressen aus erfolglosem Versand angezeigt werden sollen
			if(preg_match("/unsentMail/Se", $this->hint)) {
				$splitHint				= explode(")", $this->hint);
				$this->adminContent    .= $this->getNotificationStr($splitHint[0] . ')<br /><br />{s_notice:resendnewsl}', "error");
				$this->adminContent    .= $splitHint[1];
				$this->resendNewsl		= true;
			}
			else
				$this->adminContent .= $this->getNotificationStr($this->hint, "error");
		}
		
		// Mindestens ein Fehler
		if(isset($this->wrongInput) && count($this->wrongInput) > 0)
			$this->adminContent .= $this->getNotificationStr("{s_error:failchange}", "error");

			
		// Newsletter bearbeiten
		$this->adminContent .= 	'<h2 class="cc-section-heading cc-h2">{s_nav:adminnewsl}</h2>';
		
		
		// Newsletter bearbeiten
		$this->adminContent .= 	'<h3 class="cc-h3 toggle actionHeader';
		

		if($this->addNewsl)
			$this->adminContent .=	' hideNext';
			
		$this->adminContent .=	'">{s_header:editnewsl} ' . (!empty($this->newslID) ? '(#' . $this->newslID . ')' : '');
		
		
		// Ggf. Button zurück zur Liste einfügen
		if($this->editNewsl) {
			
			$this->adminContent .=	$this->getBacktoListButton();

		}
		
		$this->adminContent .=	'</h3>' . "\r\n" .
								'<div class="adminBox">' . "\r\n";
		
		
		// Falls ein einzelner Newsletter zum Bearbeiten in der Session gespeichert ist
		if(	$this->editNewsl &&
			count($this->newslQuery) > 0 && 
			($this->backendLog
			|| (int)$this->g_Session['userid'] == $this->authorID)
		) {
			
			// Newsletter-Formular
			$this->adminContent .= 	$this->getNewsletterForm();
		
		} // Ende falls einzelner newsl
		
		// Andernfalls Newsletter auflisten
		else {
			
			$this->adminContent .= 	$this->listNewsletters();
		
		}
		
		$this->adminContent .= 	'</div>' . "\r\n";


		// Ggf Formular neuen Newsletter anlegen
		if($this->addNewsl)	{
		
			// Neuen Newsletter anlegen
			$this->adminContent .= 	'<h3 class="cc-h3 actionHeader toggle">{s_header:newnewsl}';
			

			$this->adminContent .=	$this->getBacktoListButton();

			
			$this->adminContent .=	'</h3>' . "\r\n" .
									'<div class="adminBox">' . "\r\n";
									
			// Newsletter-Formular
			$this->adminContent .= 	$this->getNewsletterForm();
			
			$this->adminContent .= 	'</div>' . "\r\n";
		
		}
		
		
		$this->adminContent .= 	'</div>' . "\r\n";



		// Script
		$this->adminContent .=	$this->getScriptTag();

		// Select-Script
		$this->adminContent	.= $this->getSelectScriptTag();


		// Zurückbuttons
		$this->adminContent .=	$this->getBackButtons(parent::$type);

		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		return $this->adminContent;

	}
	
	
	
	/**
	 * Überprüft Eingaben für einen neuen Newsletter
	 * 
	 * @param	array	$a_Post	POST-Array
	 * @access	public
	 */
	public function verifyNewsletterPost($a_Post)
	{
	
		$this->newslGroup		= $a_Post['newsl_group'];
		$this->newslGroupStr	= in_array('<all>', $this->newslGroup) ? '<all>' : implode(",", $this->newslGroup);
		$this->newslSubject		= $a_Post['newsl_subject'];
		$this->newslText		= $a_Post['newsl_text'];
		$this->newslDate		= date("Y-m-d H:i:s");
		$this->authorName		= $this->g_Session['username'];
		$this->authorID			= $this->g_Session['userid'];
		$this->wrongInput		= array();
		
		// Festlegen ob der Newsletter nur an Newsletterempfänger gehen soll
		if(isset($a_Post['onlysubscribers'])) {
			$this->onlySubscribers = 1;
		}
		else {
			$this->onlySubscribers = 0;
		}
	
		// Newsletterformat
		isset($a_Post['newsl_format']) ? $this->newslFormat = "html" : $this->newslFormat = "plain";
		
		
		// Falls kein Html, Tags entfernen
		if($this->newslFormat == "plain")
			$this->newslText = strip_tags(str_replace(array("<br />","</p><p>"), "\r\n", $this->newslText));
		else
			$this->newslText = str_replace(array("// <![CDATA[", "// ]]>", "<style type=\"text/css\"><!--", "--></style>"), array("", "", "<style type=\"text/css\">", "</style>"), $this->newslText);
		

		// Extra-E-Mails
		$this->checkExtraEmails();
		
		
		
		// db-update Strings
		$dbUpdateStr1 =	"`author_id`,`date`,`group`,`only_subscribers`,`extra_emails`,`format`,`file`,`subject`,`text`,";
		$dbUpdateStr2 =	"" . (int)$this->authorID . ",'" . $this->DB->escapeString($this->newslDate) . "','" . $this->DB->escapeString($this->newslGroupStr) . "'," . (int)$this->onlySubscribers . ",'" . $this->DB->escapeString($this->extraEmails) . "','" . $this->newslFormat . "',";
		
		
		if(isset($a_Post['add_img'])
		&& $a_Post['add_img'] == "on"
		) {
								   
			$this->objectCon[2] = "img";


			// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
			if(isset($a_Post['overwrite_newsl_img']) && $a_Post['overwrite_newsl_img'] == "on")
				$this->overwrite = true;				

			// Falls die Checkbox zum Skalieren von Bildern gecheckt ist
			if(isset($a_Post['scaleimg_newsl_img']) && $a_Post['scaleimg_newsl_img'] == "on") {
				$this->scaleImg = 1;				
			
				// Bildbreite
				if(isset($a_Post['imgWidth_newsl_img']) && is_numeric($a_Post['imgWidth_newsl_img']))
					$this->imgWidth = htmlspecialchars($a_Post['imgWidth_newsl_img']);
				
				// Bildhöhe
				if(isset($a_Post['imgHeight_newsl_img']) && is_numeric($a_Post['imgHeight_newsl_img']))
					$this->imgHeight = htmlspecialchars($a_Post['imgHeight_newsl_img']);
			}
						
			
			if(isset($GLOBALS['_FILES']['newsl_img']) && $GLOBALS['_FILES']['newsl_img']['name'] != "") { // Falls eine neue Datei hochgeladen werden soll
			
				$upload_file		= $GLOBALS['_FILES']['newsl_img']['name'];
				$upload_tmpfile		= $GLOBALS['_FILES']['newsl_img']['tmp_name'];
				$imageName			= $GLOBALS['_FILES']['newsl_img']['name'];
	
				
				// Falls die Datei unterhalb des files-Verzeichnisses gespeichert werden soll
				if(isset($a_Post['files_newsl_img']) && $a_Post['files_newsl_img'] == "on" && 
				  (isset($a_Post['filesFolder_newsl_img']) && $a_Post['filesFolder_newsl_img'] != "")) {
					$this->useFilesFolder	= true;
					$this->filesFolder		= htmlspecialchars($a_Post['filesFolder_newsl_img']);
				}
				else
					$this->useFilesFolder	= false;
				
				$upload = Files::uploadFile($upload_file, $upload_tmpfile, $this->filesFolder, "image", $this->imgWidth, $this->imgHeight, $this->overwrite, ""); // File-Upload
				
				if($upload === true) {
					
					$this->objectCon[0] = ($this->filesFolder != "" ? $this->filesFolder . '/' : '') . Files::getValidFileName($imageName);
					
				}
				else {
					$this->showImgObj			= true;
					$this->wrongInput['img']	= $imageName;
					$error				= $upload;
					$errorImg			= $error;
					$this->hint			= "{s_error:uploadfail}";
				}
				#var_dump($GLOBALS['_FILES']['news_img']);
			}

			elseif(isset($a_Post['existImg']) && $a_Post['existImg'] != "") { // Falls eine vorhandene Datei übernommen werden soll
			
				$imageName = $a_Post['existImg'];
				$this->objectCon[0] = $imageName;
			}
			
			if(isset($a_Post['alttext']) && $a_Post['alttext'] != "") { 
			
				$this->objectCon[1] = $a_Post['alttext'];
				
			}
			
			$this->fileConValue = implode("<>", $this->objectCon);
			
		} // Ende if add_img
		
		elseif(isset($a_Post['add_file']) && $a_Post['add_file'] == "on") {
								   
			$this->fileCon[2] = "file";


			// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
			if(isset($a_Post['overwrite_newsl_file']) && $a_Post['overwrite_newsl_file'] == "on")
				$this->overwrite = true;				
		
		
			if(isset($GLOBALS['_FILES']['newsl_file']) && $GLOBALS['_FILES']['newsl_file']['name'] != "") { // Falls eine neue Datei hochgeladen werden soll
			
				$upload_file		= $GLOBALS['_FILES']['newsl_file']['name'];
				$upload_tmpfile		= $GLOBALS['_FILES']['newsl_file']['tmp_name'];
				$fileName			= $GLOBALS['_FILES']['newsl_file']['name'];

				
				// Falls die Datei unterhalb des files-Verzeichnisses gespeichert werden soll
				if(isset($a_Post['files_newsl_file']) && $a_Post['files_newsl_file'] == "on" && 
				  (isset($a_Post['filesFolder_newsl_file']) && $a_Post['filesFolder_newsl_file'] != "")) {
					$this->useFilesFolder	= true;
					$this->filesFolder		= htmlspecialchars($a_Post['filesFolder_newsl_file']);
				}
				else
					$this->useFilesFolder	= false;
				
				$upload = Files::uploadFile($upload_file, $upload_tmpfile, $this->filesFolder, "doc", 0, 0, $this->overwrite, ""); // File-Upload
				
				if($upload === true) {
					
					$this->fileCon[0] = ($this->filesFolder != "" ? $this->filesFolder . '/' : '') . Files::getValidFileName($fileName);
					
				}
				else {
					$this->showFileObj		= true;
					$this->wrongInput['img']	= $fileName;
					$error				= $upload;
					$errorImg			= $error;
					$this->hint			= "{s_error:uploadfail}";
				}
				#var_dump($GLOBALS['_FILES']['news_img']);
			}

			elseif(isset($a_Post['existFile']) && $a_Post['existFile'] != "") { // Falls eine vorhandene Datei übernommen werden soll
			
				$fileName	= $a_Post['existFile'];
				$this->fileCon[0] = $fileName;
				$this->fileCon[1] = "";
			}
			
			$this->fileConValue = implode("<>", $this->fileCon);


			if($this->fileCon[0] != "") {
				
				$fileExt = strtolower(substr($this->fileCon[0], count($this->fileCon[0])-4, 3));
				if($fileExt == "pdf")
					$this->docIcon = "pdf.png";
				elseif($fileExt == "zip")
					$this->docIcon = "zip.png";
				else
					$this->docIcon = "doc.png";
			}
					

		} // Ende if add_file
		

		$dbUpdateStr2 .= "'" . $this->DB->escapeString($this->fileConValue) . "',";
	
		
		
		// Subject überprüfen
		if($this->newslSubject == "")
			$this->wrongInput['subject'] = "{s_notice:nonewslhead}";

		elseif(strlen($this->newslSubject) > 300)
			$this->wrongInput['subject'] = "{s_notice:longsubject}";
		
		else				
			$dbUpdateStr2 .=  "'" . $this->DB->escapeString($this->newslSubject) . "',";

		// Newslettertext überprüfen
		if($this->newslText == "")
			$this->wrongInput['text'] = "{s_notice:nonewsltext}";

		else
			$dbUpdateStr2 .=  "'" . $this->DB->escapeString($this->newslText) . "',";
		

		
		//  Falls keine Fehler
		if(count($this->wrongInput) == 0) {
			

			$dbUpdateStr1 = substr($dbUpdateStr1, 0, -1);
			$dbUpdateStr2 = substr($dbUpdateStr2, 0, -1);


			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `$this->tableNewsl`");

				
			// Einfügen des neuen Seiteninhaltspunkts
			$insertSQL = $this->DB->query("INSERT INTO `$this->tableNewsl` 
												($dbUpdateStr1) 
												VALUES ($dbUpdateStr2)
												");

			#var_dump($insertSQL);

			// db-Query nach gerade angelegtem Newsletter
			$recentNewsl = $this->DB->query("SELECT `id` 
												FROM `$this->tableNewsl` AS n 
												LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
													ON n.`author_id` = user.`userid` 
												WHERE n.`date` = (SELECT MAX(`date`) FROM `$this->tableNewsl`) 
												AND n.id = (SELECT MAX(id) FROM `$this->tableNewsl`) 
												AND $this->dbFilter
												", false);
			
			#var_dump($recentNewsl);
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");


			$this->setSessionVar('notice',		"{s_notice:addnewsl}" . $this->notice);
			$this->setSessionVar('hint',		"<br />{s_notice:modnewsl}");
			$this->setSessionVar('newsl_id',	$recentNewsl[0]['id']);
			
			
			header("Location: " . $this->formAction);
			exit;
			
		}

	}
	
	
	
	/**
	 * Überprüft Eingaben für einen Newsletter
	 * 
	 * @param	int		$newslID	Newsletter ID
	 * @access	public
	 */
	public function editNewsletter($newslID)
	{
		
		// Newsletter bearbeiten
		if(!is_numeric($newslID))
			return false;			
		
		
		$newslIDdb	= $this->DB->escapeString($newslID);
		
		
		// Locking checken
		if($this->checkLocking($newslID, "newsletter", $this->g_Session['username'])) {
			$this->noChange	= true;
			return false;
		}
		
		
		// db-Query nach zu bearbeitendem Newsletter
		$this->newslQuery = $this->DB->query( "SELECT n.*, user.`userid`, user.`author_name` 
												FROM `$this->tableNewsl` AS n 
												LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
													ON n.`author_id` = user.`userid` 
												WHERE 
												$this->dbFilter 
												AND `id` = $newslIDdb 
											", false);
		
		#var_dump($this->newslQuery);
		
		
		if(!is_array($this->newslQuery)
		|| count($this->newslQuery) == 0
		) {
			$this->unsetSessionKey('newsl_id');
			header("Location: " . $this->formAction);
			exit;
		}
		
		$this->newslGroupStr	= $this->newslQuery[0]['group'];
		$this->newslGroup		= explode(",", $this->newslGroupStr);
		$this->newslSubject		= $this->newslQuery[0]['subject'];
		$this->newslText		= $this->newslQuery[0]['text'];
		$this->onlySubscribers	= $this->newslQuery[0]['only_subscribers'];
		$this->extraEmails		= $this->newslQuery[0]['extra_emails'];
		$this->newslFormat		= $this->newslQuery[0]['format'];
		$this->newslDate		= $this->newslQuery[0]['date'];
		$this->sentDate			= $this->newslQuery[0]['sent_date'];
		$this->authorName		= $this->newslQuery[0]['author_name'];
		$this->authorID			= $this->newslQuery[0]['userid'];
		$this->attachCon		= $this->newslQuery[0]['file'];
		$this->newslStatus		= $this->newslQuery[0]['sent'];
		
		$attArr					= explode("<>", $this->attachCon);
		$attType				= end($attArr);
		
		if($attType == "img")
			$this->objectCon = $attArr;
		
		elseif($attType == "file") {
			
			$this->fileCon = $attArr;
			
			if($this->fileCon[0] != "") {
			
				$fileExt = strtolower(substr($this->fileCon[0], count($this->fileCon[0])-4, 3));
				if($fileExt == "pdf")
					$this->docIcon = "pdf.png";
				elseif($fileExt == "zip")
					$this->docIcon = "zip.png";
				else
					$this->docIcon = "doc.png";
			}
		}

		
		// Extra-E-Mails
		$this->checkExtraEmails();

		
		// Falls das Formular zum Ändern oder senden eines Newsletters abgeschickt wurde
		if(isset($GLOBALS['_POST']['edit_newsl'])
		|| isset($GLOBALS['_POST']['send_newsl'])
		) {
			
			$this->newslGroup		= $GLOBALS['_POST']['newsl_group'];
			$this->newslGroupStr	= in_array('<all>', $this->newslGroup) ? '<all>' : implode(",", $this->newslGroup);
			$this->newslSubject		= $GLOBALS['_POST']['newsl_subject'];
			$this->newslText		= $GLOBALS['_POST']['newsl_text'];
			
			
			// Festlegen ob der Newsletter nur an Newsletterempfänger gehen soll
			if(isset($GLOBALS['_POST']['onlysubscribers'])) {
				$this->onlySubscribers = 1;
			}
			else {
				$this->onlySubscribers = 0;
			}
			
			// Format
			isset($GLOBALS['_POST']['newsl_format']) ? $this->newslFormat = "html" : $this->newslFormat = "plain";
		
		
			// Falls kein Html, Tags entfernen
			if($this->newslFormat == "plain")
				$this->newslText = strip_tags(str_replace(array("<br />","</p><p>"), "\r\n", $this->newslText));
			else
				$this->newslText = str_replace(array("// <![CDATA[", "// ]]>", "<style type=\"text/css\"><!--", "--></style>"), array("", "", "<style type=\"text/css\">", "</style>"), $this->newslText);
				
			
			
			// db update String
			$dbUpdateStr = "`group` = '" . $this->DB->escapeString($this->newslGroupStr) . "',`only_subscribers` = " . (int)$this->onlySubscribers . ",`extra_emails` = '" . $this->DB->escapeString($this->extraEmails) . "',`format` = '" . $this->newslFormat . "',";
			
			if(isset($GLOBALS['_POST']['add_img']) && $GLOBALS['_POST']['add_img'] == "on") {
									   
				$this->objectCon[2] = "img";


				// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
				if(isset($GLOBALS['_POST']['overwrite_newsl_img']) && $GLOBALS['_POST']['overwrite_newsl_img'] == "on")
					$this->overwrite = true;				

				// Falls die Checkbox zum Skalieren von Bildern gecheckt ist
				if(isset($GLOBALS['_POST']['scaleimg_newsl_img']) && $GLOBALS['_POST']['scaleimg_newsl_img'] == "on") {
					$this->scaleImg = 1;				
				
					// Bildbreite
					if(isset($GLOBALS['_POST']['imgWidth_newsl_img']) && is_numeric($GLOBALS['_POST']['imgWidth_newsl_img']))
						$this->imgWidth = htmlspecialchars($GLOBALS['_POST']['imgWidth_newsl_img']);
					
					// Bildhöhe
					if(isset($GLOBALS['_POST']['imgHeight_newsl_img']) && is_numeric($GLOBALS['_POST']['imgHeight_newsl_img']))
						$this->imgHeight = htmlspecialchars($GLOBALS['_POST']['imgHeight_newsl_img']);
				}
				
			
				if(isset($GLOBALS['_FILES']['newsl_img']) && $GLOBALS['_FILES']['newsl_img']['name'] != "") { // Falls eine neue Datei hochgeladen werden soll
				
					$upload_file = $GLOBALS['_FILES']['newsl_img']['name'];
					$upload_tmpfile = $GLOBALS['_FILES']['newsl_img']['tmp_name'];
					$imageName = $GLOBALS['_FILES']['newsl_img']['name'];
		
					
					// Falls die Datei unterhalb des files-Verzeichnisses gespeichert werden soll
					if(isset($GLOBALS['_POST']['files_newsl_img']) && $GLOBALS['_POST']['files_newsl_img'] == "on" && 
					  (isset($GLOBALS['_POST']['filesFolder_newsl_img']) && $GLOBALS['_POST']['filesFolder_newsl_img'] != "")) {
						$this->useFilesFolder	= true;
						$this->filesFolder		= htmlspecialchars($GLOBALS['_POST']['filesFolder_newsl_img']);
					}
					else
						$this->useFilesFolder	= false;
					
					$upload = Files::uploadFile($upload_file, $upload_tmpfile, $this->filesFolder, "image", $this->imgWidth, $this->imgHeight, $this->overwrite, ""); // File-Upload
					
					if($upload === true) {
						
						$this->objectCon[0] = ($this->filesFolder != "" ? $this->filesFolder . '/' : '') . Files::getValidFileName($imageName);
												
					}
					else {
						$this->showImgObj 			= true;
						$this->wrongInput['img']	= $imageName;
						$error						= $upload;
						$errorImg					= $error;
						$this->hint					= "{s_error:uploadfail}";
					}
					#var_dump($GLOBALS['_FILES']['news_img']);
				}
	
				elseif(isset($GLOBALS['_POST']['existImg']) && $GLOBALS['_POST']['existImg'] != "") { // Falls eine vorhandene Datei übernommen werden soll
				
					$imageName		= $GLOBALS['_POST']['existImg'];
					$this->objectCon[0]	= $imageName;
				}
				
				if(isset($GLOBALS['_POST']['alttext']) && $GLOBALS['_POST']['alttext'] != "") { 
				
					$this->objectCon[1] = $GLOBALS['_POST']['alttext'];
					
				}
				
				$this->fileConValue = implode("<>", $this->objectCon);
				
			} // Ende if add_img
			
			elseif(isset($GLOBALS['_POST']['add_file']) && $GLOBALS['_POST']['add_file'] == "on") {
									   
				$this->fileCon[2] = "file";
				
				
				// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
				if(isset($GLOBALS['_POST']['overwrite_newsl_file']) && $GLOBALS['_POST']['overwrite_newsl_file'] == "on")
					$this->overwrite = true;				

					
				if(isset($GLOBALS['_FILES']['newsl_file']) && $GLOBALS['_FILES']['newsl_file']['name'] != "") { // Falls eine neue Datei hochgeladen werden soll
				
					$upload_file	= $GLOBALS['_FILES']['newsl_file']['name'];
					$upload_tmpfile = $GLOBALS['_FILES']['newsl_file']['tmp_name'];
					$fileName		= $GLOBALS['_FILES']['newsl_file']['name'];


					// Falls die Datei unterhalb des files-Verzeichnisses gespeichert werden soll
					if(isset($GLOBALS['_POST']['files_newsl_file']) && $GLOBALS['_POST']['files_newsl_file'] == "on" && 
					  (isset($GLOBALS['_POST']['filesFolder_newsl_file']) && $GLOBALS['_POST']['filesFolder_newsl_file'] != "")) {
						$this->useFilesFolder	= true;
						$this->filesFolder		= htmlspecialchars($GLOBALS['_POST']['filesFolder_newsl_file']);
					}
					else
						$this->useFilesFolder	= false;
					
					$upload = Files::uploadFile($upload_file, $upload_tmpfile, $this->filesFolder, "doc", 0, 0, $this->overwrite, ""); // File-Upload
					
					if($upload === true) {
						
						$this->fileCon[0] = ($this->filesFolder != "" ? $this->filesFolder . '/' : '') . Files::getValidFileName($fileName);
												
					}
					else {
						$this->showFileObj		= true;
						$this->wrongInput['img']	= $fileName;
						$error				= $upload;
						$errorImg			= $error;
						$this->hint			= "{s_error:uploadfail}";
					}
					#var_dump($GLOBALS['_FILES']['news_img']);
				}
	
				elseif(isset($GLOBALS['_POST']['existFile']) && $GLOBALS['_POST']['existFile'] != "") { // Falls eine vorhandene Datei übernommen werden soll
				
					$fileName = $GLOBALS['_POST']['existFile'];
					$this->fileCon[0] = $fileName;
					$this->fileCon[1] = "";
				}
				
				$this->fileConValue = implode("<>", $this->fileCon);
				

			} // Ende if add_file
			

			$dbUpdateStr .= "`file` = '" . $this->DB->escapeString($this->fileConValue) . "',";
		
			

			// Subject überprüfen
			if($this->newslSubject == "")
				$this->wrongInput['subject'] = "{s_notice:nonewslhead}";

			elseif(strlen($this->newslSubject) > 300)
				$this->wrongInput['subject'] = "{s_notice:longsubject}";
			
			else				
				$dbUpdateStr .=  "`subject` = '" . $this->DB->escapeString($this->newslSubject) . "',";

			// Newslettertext überprüfen
			if($this->newslText == "")
				$this->wrongInput['text'] = "{s_notice:nonewsltext}";

			else				
				$dbUpdateStr .=  "`text` = '" . $this->DB->escapeString($this->newslText) . "',";

			
			
			// Falls keine Fehler
			if(count($this->wrongInput) == 0) {
				

				if(isset($GLOBALS['_POST']['send_newsl'])) { // Falls der Newsletter verschickt werden soll
				
					// User object
					$o_user		= new User($this->DB, $this->o_lng);
				
					// Klasse phpMailer einbinden
					require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
					
					// Instanz von PHPMailer bilden
					$mail = new \PHPMailer();
					
					
					// Name des Abenders setzen
					$mailFromName = NEWSLETTER_AUTHOR != '' ? NEWSLETTER_AUTHOR : $this->g_Session['username'];
					
					// Falls Newsletter im HTML-Format
					if($this->newslFormat == "html")
						$mailIsHTML = true; // Versand im HTML-Format festlegen
					else
						$mailIsHTML = false; // Versand im HTML-Format festlegen
					
					
					// Falls der Newsletter mit Datei-Anhang verschickt werden soll
					if($this->fileConValue != "" || strpos($this->newslText, "<img src=") !== false) {
						
						$fileAttach = explode("<>", $this->fileConValue);
						
						// Pfad zur Datei
						if(isset($fileAttach[2]) && $fileAttach[2] == "img")
							$fileAttachFolder = CC_IMAGE_FOLDER;
						elseif(isset($fileAttach[2]) && $fileAttach[2] == "file")
							$fileAttachFolder = CC_DOC_FOLDER;
							
						// Falls files-Ordner, den Pfad ermitteln
						if(isset($fileAttach[0]) && strpos($fileAttach[0], "/") !== false) {
							$filesImgArr		= explode("/", $fileAttach[0]);
							$fileAttach[0]		= array_pop($filesImgArr);					
							$fileAttachFolder	= CC_FILES_FOLDER . "/" . implode("/", $filesImgArr);
						}
										
						// Falls ein Bilddatei-Anhang in die E-Mail eingebettet werden soll
						while(strpos($this->newslText, ' src="' . PROJECT_HTTP_ROOT) !== false) {
							$inlineImg = PROJECT_HTTP_ROOT . preg_replace("/(.* src=\"" . str_replace("/", "\/", PROJECT_HTTP_ROOT) . ")([A-Za-z0-9\.\:\/_-]*)(\" alt=\")([A-Za-z0-9\. _-]*)(\" .*)/ism", "\\2", $this->newslText, 1);
							$inlineImgName = basename($inlineImg);
							$inlineImgBaseName = substr($inlineImgName, 0, strrpos($inlineImgName, "."));
							$this->newslText = preg_replace("/(.* src=\")(" . str_replace("/", "\/", PROJECT_HTTP_ROOT) . ")([A-Za-z0-9\.\:\/_-]*)(\" alt=\")([A-Za-z0-9\. _-]*)(\" .*)/ism", "\\1" . "cid:" . $inlineImgBaseName . "\\4" . $inlineImgName . "\\6", $this->newslText, 1);
							
							$mail->AddEmbeddedImage(str_replace(PROJECT_HTTP_ROOT, PROJECT_DOC_ROOT, $inlineImg), $inlineImgBaseName, $inlineImgName);
							$mail->AddAttachment(str_replace(PROJECT_HTTP_ROOT, PROJECT_DOC_ROOT, $inlineImg), $inlineImgBaseName);
						}
						
						//Eine Datei vom Server als Attachment anhängen
						if($this->fileConValue != "")
							$mail->AddAttachment(PROJECT_DOC_ROOT . "/".$fileAttachFolder."/" . $fileAttach[0], ($fileAttach[1] != "" ? $fileAttach[1] : $fileAttach[0]));
						  
					}
					
					// Platzhalter (StatText) ersetzen
					$this->newslText = ContentsEngine::replaceStaText($this->newslText);

					// Erneuter Versuch bei Fehler
					// Falls an bestimmte E-Mails erneut gesendet werden soll
					if(isset($GLOBALS['_POST']['resendMails'])) {
						
						$this->resendMail = $GLOBALS['_POST']['resendMails'];
					}
					
					// E-Mailadressen der zu adressierenden Benutzergruppe holen
					$userEmail = $o_user->getUserEmail($this->newslGroup, $this->onlySubscribers, true, $this->resendMail);
					
					
					// Ggf. extra E-Mailadressen hinzufügen
					if(count($this->extraEmailsArray) > 0) {
					
						// Falls keine registrierten Benutzer im Email-Array
						if($userEmail === false)
							$userEmail = array();
						
						// Benutzerdaten generieren
						foreach($this->extraEmailsArray as $eM) {
							$userEmail[]	= array("username" => $eM,
													"email" => $eM,
													"auth_code" => (isset($this->extraUsers[$eM]) ? $this->extraUsers[$eM] : '')
													);
						}
					}
					
					
					// Wenn Empfänger vorhanden
					if($userEmail !== false) {
						
						$errorMail		= 0;
						$mailStatus		= false;
						$individualMail = false;
						
						// Falls eine individuelle Ansprache der Empfänger erfolgen soll (sprich ein Platzhalter für den Namen vorhanden ist),
						// einzelne E-Mails versenden
						if(strpos($this->newslText, "{%name%}") !== false
						|| strpos($this->newslText, "{%username%}") !== false
						)
							$individualMail = true;
						
						// Empfänger-Array auslesen
						foreach($userEmail as $recipient) {
							
							// Falls eine individuelle Ansprache der Empfänger erfolgen soll
							if($individualMail) {
									
								// Empfänger-Adresse der vorehrigen E-Mail löschen
								$mail->ClearAddresses();
								
								$name = User::getMailLocalPart($recipient['username']); // Adressat
								
								// Platzhalter ersetzen
								$mailSubject 	= str_replace("{%name%}", $name, $this->newslSubject);// Adressat im Betreff
								$mailSubject	= '=?utf-8?B?'.base64_encode($mailSubject).'?=';
								$mailBody		= str_replace("{%name%}", $name, $this->newslText);// Adressat
								$mailBody		= str_replace("{%username%}", $recipient['username'], $mailBody);// Benutzername
								$mailBody		= str_replace("{%subject%}", $this->newslSubject, $mailBody);// Betreff
								$mailBody		= str_replace("{%auth_code%}", $recipient['auth_code'], $mailBody);// Auth-Code
								$mailBody		= str_replace("{%root%}", PROJECT_HTTP_ROOT, $mailBody);// newslText
								$mailBody		= str_replace("{%year%}", date("Y"), $mailBody);// newslText
								
								// Falls Newsletter im HTML-Format
								if($this->newslFormat == "html")
									// Bei Altbody die Tags entfernen
									$mail->AltBody = strip_tags($mail->Body); // mit strip_tags() werden die HTML-Tags entfernt
								
								// E-Mail senden
								// E-Mail-Parameter für SMTP
								$mail->setMailParameters(NEWSLETTER_EMAIL, $mailFromName, $recipient['email'], $mailSubject, $mailBody, $mailIsHTML, "", "smtp");
								// E-Mail senden per phpMailer (SMTP)
								$mailStatus = $mail->Send();
								
								// Falls Versand per SMTP erfolglos, per Sendmail probieren
								if($mailStatus !== true) {
									// E-Mail-Parameter für php Sendmail
									$mail->setMailParameters(NEWSLETTER_EMAIL, $mailFromName, $recipient['email'], $mailSubject, $mailBody, $mailIsHTML, "", "sendmail");
									// E-Mail senden per phpMailer (Sendmail)
									$mailStatus = $mail->Send();
								}
								// Falls Versand per Sendmail erfolglos, per mail() probieren
								if($mailStatus !== true) {
									// E-Mail-Parameter für php mail()
									$mail->setMailParameters(NEWSLETTER_EMAIL, $mailFromName, $recipient['email'], $mailSubject, $mailBody, $mailIsHTML);
									// E-Mail senden per phpMailer (mail())
									$mailStatus = $mail->Send();
								}
								// Falls der Versand mit keiner der Versandarten erfolgreich war, E-Mail in Array notSentMail speichern und Fehler zählen
								if($mailStatus !== true) {
									$this->notSentMail[] = $recipient['email'];
									$errorMail++;
								}
							}
							else
								// Andernfalls Empfängeradresse für BCC hinzufügen
								$mail->AddBCC($recipient['email']);
						}
						
						// Falls keine individuelle Ansprache der Empfänger erfolgen soll, jetzt die E-Mail als Blindkopie an alle Empfänger schicken
						if(!$individualMail) {
						
							// UTF8 subject
							$mailSubject		= '=?utf-8?B?'.base64_encode(htmlspecialchars($this->newslSubject)).'?=';
							
							// E-Mail senden
							// E-Mail-Parameter für SMTP
							$mail->setMailParameters(NEWSLETTER_EMAIL, $mailFromName, NEWSLETTER_EMAIL, $mailSubject, $this->newslText, $mailIsHTML, "", "smtp");
							// E-Mail senden per phpMailer (SMTP)
							$mailStatus = $mail->Send();
							// Falls Versand per SMTP erfolglos, per Sendmail probieren
							if($mailStatus !== true) {
								// E-Mail-Parameter für php Sendmail
								$mail->setMailParameters(NEWSLETTER_EMAIL, $mailFromName, NEWSLETTER_EMAIL, $mailSubject, $this->newslText, $mailIsHTML, "", "sendmail");
								// E-Mail senden per phpMailer (Sendmail)
								$mailStatus = $mail->Send();
							}
							// Falls Versand per Sendmail erfolglos, per mail() probieren
							if($mailStatus !== true) {
								// E-Mail-Parameter für php mail()
								$mail->setMailParameters(NEWSLETTER_EMAIL, $mailFromName, NEWSLETTER_EMAIL, $mailSubject, $this->newslText, $mailIsHTML);
								// E-Mail senden per phpMailer (mail())
								$mailStatus = $mail->Send();
							}
							if($mailStatus !== true)
								$errorMail++;
						}

						// Wenn keine E-Mail versandt wurde
						if($errorMail == count($userEmail) || (!$individualMail && $errorMail == 1)) {
							$this->hint		= "{s_notice:notsent} (0/" . count($userEmail) . ")";
							$this->failSend = true;
						}
						// Wenn die E-Mails teilweise versandt wurden
						elseif($errorMail > 0) {
							$this->isSent 	= true;
							$this->notice	= "{s_notice:sent}";					
							$this->hint		= "{s_notice:partlysent} (" . (count($userEmail) - $errorMail) . "/" . count($userEmail) . ")";					
							$this->hint    .= '<div class="unsentMail"><h3 class="cc-h3 toggle">{s_notice:failmails}</h3><ul><li>' . implode("<br />", $this->notSentMail) . '</li></ul></div>' . "\r\n";
							$this->failSend = true;
						}
						// Wenn alle E-Mails versandt wurden
						elseif($errorMail == 0) {
							$this->isSent 	= true;
							$this->notice	= "{s_notice:sent} (" . count($userEmail) . "/" . count($userEmail) . ")";					
						}
						
					}
					else {
						$this->hint = "{s_notice:nomails}";
					}
				}
				
				// Newsl ganz, partiell oder nicht gesendet
				$dbUpdateStr .=  "`sent` = " . ($this->isSent ? 1 : 0) . ",";
				
				$dbUpdateStr = substr($dbUpdateStr, 0, -1);


				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `$this->tableNewsl`");

					
				// Einfügen des neuen Seiteninhaltspunkts
				$updateSQL = $this->DB->query("UPDATE `$this->tableNewsl` 
													SET $dbUpdateStr 
													WHERE id = $newslIDdb
													");

				#var_dump($updateSQL);

				
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");

				
				// Falls der Newsletter veröffentlicht werden soll
				if(isset($GLOBALS['_POST']['send_newsl'])) {
					
					if(!empty($this->notice)) {
						$this->setSessionVar('notice', $this->notice);
					}
					if(!empty($this->hint)) {
						$this->setSessionVar('hint', $this->hint);
					}
					// Falls Versand erfolgreich, newsl_id aus der Session löschen
					if(!$this->failSend)
						$this->unsetSessionKey('newsl_id');
					else {
						$this->unsetSessionKey('notice');
						$this->unsetSessionKey('hint');
					}
				}
				
				// Falls der Newsletter nur geändert werden soll
				elseif(isset($GLOBALS['_POST']['edit_newsl'])) {
					$this->setSessionVar('notice', "{s_notice:editnewsl}" . $this->notice);
					$this->setSessionVar('newsl_id', $newslID);
					
					if($this->newslStatus == 0)
						$this->setSessionVar('hint', "<br />{s_notice:modnewsl}");
						
					elseif(isset($this->g_Session['hint'])) {
						$this->unsetSessionKey('hint');
						$this->unsetSessionKey('newsl_id');
					}
				}
				
				// Falls Versand erfolgreich, Seite neu laden
				if(!$this->failSend) {
					header("Location: " . $this->formAction);
					exit;
				}
				
			}

		} // Ende if change or publish
	
	}
	
	
	
	/**
	 * Newsletter DB-Query
	 * 
	 * @access public
	 */
	public function getDbFilterStr()
	{

		// Restriktion der Benutzergruppen
		$this->dbFilter = "user.`username` != ''";
		
		if($this->loggedUserGroup == "editor") {
			
			$this->dbFilter .= " AND (user.`group` != 'admin' AND (user.`group` != 'editor' OR user.`username` = '" . $this->DB->escapeString($this->loggedUser) . "'))";
		}
		elseif($this->loggedUserGroup == "author") { // Admin- und Editor-Gruppe entfernen
			
			$this->dbFilter .= " AND user.`username` = '" . $this->DB->escapeString($this->loggedUser) . "'";
		}
		return $this->dbFilter;
	
	}
	
	
	
	/**
	 * Newsletter DB-Query
	 * 
	 * @access public
	 */
	public function queryNewsletters()
	{
	
		// Newsletter auflisten
		// Parameter auslesen
		// List Limit
		$this->limit = self::getLimit();
		
		// Weitere Listenparameter
		$this->evalListParams();
		

		// db-Query nach News
		$this->newslArchive = $this->DB->query( "SELECT n.*, user.`userid` , user.`author_name` 
													FROM `$this->tableNewsl` AS n 
													LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
														ON n.`author_id` = user.`userid` 
													WHERE 
													$this->dbFilter
													$this->sortRes
												", false);
		#var_dump($this->newslArchive);
		

		if(is_array($this->newslArchive)
		&& count($this->newslArchive) > 0
		) {
		
			$this->totalRows	= count($this->newslArchive);
			$this->startRow		= "";
			$this->pageNum		= 0;
		
			// Pagination
			if (isset($GLOBALS['_GET']['pageNum']))
				$this->pageNum = $GLOBALS['_GET']['pageNum'];
			
				
					$this->startRow		= $this->pageNum * $this->limit;
					$queryLimit			= " LIMIT " . $this->startRow . "," . $this->limit;
					$this->queryString	= "task=campaigns&type=newsl&list_newsl=$this->sortList&sent=$this->pubFilter&limit=$this->limit";
					
	
			// db-Query nach News
			$this->newslArchive = $this->DB->query( "SELECT n.*, user.`userid`, user.`author_name` 
														FROM `$this->tableNewsl` AS n 
														LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
															ON n.`author_id` = user.`userid` 
														WHERE 
														$this->dbFilter 
														$this->sortRes
														$queryLimit
													", false);
			#var_dump($this->newslArchive);
		}

	}
	
	
	
	/**
	 * Erstellt ein Newsletter-Formular
	 * 
	 * @access public
	 */
	public function getNewsletterForm()
	{
	
		$output =	'<div class="adminBox">' . "\r\n" .
					'<form action="' . $this->formAction . '" method="post" name="adminfm3" enctype="multipart/form-data" accept-charset="UTF-8">' . "\r\n" .
					'<ul class="newsletter framedItems">' . "\r\n" .
					'<li>' . "\r\n" .
					'<div class="leftBox"><label>{s_label:plannergroup}</label>' . "\r\n" .
					'<select name="newsl_group[]" multiple="multiple" id="newsl_group" class="block-select" size="' . count($this->userGroups) . '">' . "\r\n" .
					'<option value="<all>"' . (empty($this->newslGroup) || (!empty($this->newslGroup) && in_array("<all>", $this->newslGroup)) ? ' selected="selected"' : '') . '>{s_option:allgroup}</option>' . "\r\n";
							
		foreach($this->userGroups as $group) {
			if($group != "public")
				$output .='<option value="' . $group . '"' . (!empty($this->newslGroup) && in_array($group, $this->newslGroup) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . "\r\n"; // Benutzergruppe
		}
		
		$output .=	'</select></div>' . "\r\n" .
					'<div class="rightBox">' . "\r\n" . 
					'<label for="newsl_extraemails">{s_label:newslextramails} (' . count($this->extraEmailsArray) . ')</label>' . "\r\n";


		if(isset($this->wrongInput['newsl_extraemails']))
			$output .='<p class="notice">' . $this->wrongInput['newsl_extraemails'] . '</p>' . "\r\n";
							
		$output .=	'<textarea name="newsl_extraemails" id="newsl_extraemails" class="customList" rows="9">' . (isset($this->extraEmails) ? htmlspecialchars($this->extraEmails) : '') . '</textarea>' . "\r\n" . 
					'</div>' . "\r\n" . 
					'<br class="clearfloat" /><br />' . "\r\n" .
					'<div class="leftBox">' . "\r\n" .
					'<div class="onlySubscribers leftBox">' . "\r\n" .
					'<label class="markBox clearleft"><input type="checkbox" name="onlysubscribers" id="onlysubscribers"' . ($this->onlySubscribers ? ' checked="checked"' : '') . ' /></label>' . "\r\n" . 
					'<label for="onlysubscribers">{s_label:onlysubscribers}</label>' . "\r\n" .
					'</div>' . "\r\n" .
					'<div class="htmlFormatBox rightBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="newsl_format" id="newsl_format"' . ($this->newslFormat == "html" ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="newsl_format">{s_label:htmlformat}</label>' . "\r\n" . 
					'</div>' . "\r\n" .
					'</div>' . "\r\n" .
					'<div class="rightBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="newsl_saveextraemails" id="newsl_saveextraemails"' . ($this->saveExtraEmails ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="newsl_saveextraemails">{s_label:saveextramails}</label>' . "\r\n" . 
					'</div>' . "\r\n" .
					'<br class="clearfloat" /><br />' . "\r\n" .
					'</li>' . "\r\n";

		// Subject
		$output .=	'<li>' . "\r\n" .
					'<label for="newsl_subject">{s_label:subject}</label>' . "\r\n";
							
		if(isset($this->wrongInput['subject']))
			$output .='<p class="notice">' . $this->wrongInput['subject'] . '</p>' . "\r\n";
							
		$output .=	'<input type="text" name="newsl_subject" id="newsl_subject" value="' . htmlspecialchars($this->newslSubject) . '" maxlength="300" />' . "\r\n" . 
					'</li>' . "\r\n";

		// Text
		$output .=	'<li>' . "\r\n" .
					'<label for="newsl_text">{s_label:newsltext}</label>' . "\r\n";
							
		if(isset($this->wrongInput['text']))
			$output .=	'<p class="notice">' . $this->wrongInput['text'] . '</p>' . "\r\n";
							
		$output .=	'<span class="namePlaceholder ' . EDITOR_SKIN . 'Skin">' . "\r\n";
		
		// Button preview
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'namePlaceholder-button mceButton mceButtonEnabled button-icon-only',
								"value"		=> "",
								"title"		=> "{s_title:newslrec}",
								"icon"		=> "user",
								"iconclass"	=> "namePlaceholder-icon mceIcon"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'</span>' . "\r\n";
		
		$newslText	= str_replace("{%root%}", PROJECT_HTTP_ROOT, $this->newslText);// newslText

		$output .=	'<textarea name="newsl_text" id="newsl_text"' . ($this->newslFormat == "html" ? '' : ' class="cc-editor-add disableEditor"') . ' rows="5" data-attachkeywords="' . implode(",", $this->attachmentKeyWords) . '">' . htmlspecialchars($this->newslText) . '</textarea>' . "\r\n" .
					'</li>' . "\r\n";
		
		
		// Pfad zur Bilddatei
		// Falls files-Ordner, den Pfad ermitteln
		if(isset($this->objectCon[0]) && strpos($this->objectCon[0], "/") !== false) {
			$filesImgArr			= explode("/", $this->objectCon[0]);
			$this->objectCon[0]		= array_pop($filesImgArr);					
			$imgPath				= CC_FILES_FOLDER . "/" . implode("/", $filesImgArr) . "/";
			$docPath				= $imgPath;
		}
		else {
			$imgPath = CC_IMAGE_FOLDER . "/";
			$docPath = CC_DOC_FOLDER . "/";
		}
		
		// Anhang
		$output .=	'<ul class="dataObjectList subList framedItems">' . "\r\n" .
					'<li class="dataObject listItem"><span class="type objectToggle toggleAttachment">{s_label:attachm}</span>' . "\r\n" .
					'<div class="objects"' . ($this->fileCon[0] == "" && $this->objectCon[0] == "" && isset($this->wrongInput['img']) ? ' style="display:none;"' : '') . '>' . "\r\n";


		// Fehlermeldung
		if(isset($this->wrongInput['img']))
			$output .=	'<p class="notice">' . $errorImg . '</p>' . "\r\n";
		
		
		// Bildanhang
		$output .=	'<div class="attach imgObject"' . ($this->fileCon[0] != '' ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_img" id="add_img" class="toggleObjectType"' . ($this->objectCon[0] != '' ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_img" class="dataObjectLabel inline-label">{s_label:image}</label>' . "\r\n" .
					'<div class="dataObjectBox" style="' . ($this->objectCon[0] == '' ? ' display:none;' : '') . '">' . "\r\n" .
					'<div class="fileSelBox">' . "\r\n" .
					'<div class="existingFileBox leftBox">' . "\r\n" . // Dateiupload-Box
					'<label class="elementsFileName">' . (empty($this->objectCon[0]) ? "{s_label:choosefile}" : htmlspecialchars($this->objectCon[0])) . '</label>' . "\r\n" .									
					'<div class="previewBox img">' . "\r\n" .
					'<img src="' . $imgPath . 'thumbs/' . (!empty($this->objectCon[0]) ? htmlspecialchars($this->objectCon[0]) : '../../../system/themes/' . ADMIN_THEME . '/img/noimage.png') . '" alt="' . (isset($this->objectCon[1]) ? htmlspecialchars($this->objectCon[1]) : '') . '" title="' . (isset($this->objectCon[1]) && $this->objectCon[0] != "" ? htmlspecialchars($this->objectCon[1]) : 'no image') . '" class="preview" data-img-src="' . (isset($this->objectCon[0]) && $this->objectCon[0] != "" ? htmlspecialchars($imgPath . $this->objectCon[0]) : SYSTEM_IMAGE_DIR . '/noimage.png') . '" />' . "\r\n" . 
					'</div>' . "\r\n";

		$mediaListButtonDef		= array(	"class" 	=> "images",
											"type"		=> "images",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=images",
											"path"		=> $imgPath . "thumbs/",
											"value"		=> "{s_button:imgfolder}",
											"icon"		=> "image"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<input type="text" name="existImg" class="existingFile" value="" />' . "\r\n" .
					'</div>' . "\r\n" .
					'<div class="fileUploadBox rightBox">' . "\r\n" . // Dateiupload-Box
					'<label class="uploadBoxLabel">{s_formfields:file}</label>' . "\r\n" .
					$this->getUploadMask('newsl_img', $this->overwrite, 'img', $this->scaleImg, $this->imgWidth, $this->imgHeight) .
					$this->getFilesUploadMask($this->filesFolder, $this->useFilesFolder, 'newsl_img') .
					'</div>' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n" .
					'<label>alt-Text</label>' . "\r\n" .
					'<input type="text" name="alttext" value="' . (isset($this->objectCon[1]) ? htmlspecialchars($this->objectCon[1]) : '') . '" maxlength="512" class="altText" />' . "\r\n" .
					'</div>' . "\r\n" .	
					'</div>' . "\r\n" .	
					'</div>' . "\r\n";
							
		// Dokumentenanhang
		$output .=	'<div class="attach docObject"' . ($this->objectCon[0] != '' ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_file" id="add_file" class="toggleObjectType"' . ($this->fileCon[0] != '' ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_file" class="dataObjectLabel inline-label">{s_label:doc}</label>' . "\r\n" .
					'<div class="dataObjectBox" style="' . ($this->fileCon[0] == '' ? ' display:none;' : '') . '">' . "\r\n" .
					'<div class="fileSelBox">' . "\r\n" .
					'<div class="existingFileBox leftBox">' . "\r\n" . // Dateiupload-Box
					'<label class="elementsFileName">' . (empty($this->fileCon[0]) ? "{s_label:choosefile}" : htmlspecialchars($this->fileCon[0])) . '</label>' . "\r\n" .									
					'<div class="previewBox doc">' . "\r\n" .
					'<span style="display:block"><img src="' . SYSTEM_IMAGE_DIR . '/' . $this->docIcon . '" alt="' . $this->docIcon . '" />' . "\r\n" .
					'<a href="' . $docPath . ($this->fileCon[0] != '' ? $this->fileCon[0] : '') . '" target="_blank">' . ($this->fileCon[0] != '' ? $this->fileCon[0] : '') . '</a></span>' . "\r\n" . 
					'</div>' . "\r\n";
					
		$mediaListButtonDef		= array(	"class" => "docs",
											"type"	=> "doc",
											"url"	=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=doc",
											"path"	=> $docPath,
											"value"	=> "{s_button:docfolder}",
											"icon"	=> "doc"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<input type="text" name="existFile" class="existingFile" value="" />' . "\r\n" .
					'</div>' . "\r\n" .	
					'<div class="fileUploadBox rightBox">' . "\r\n" . // Dateiupload-Box
					'<label class="uploadBoxLabel">{s_formfields:file}</label>' . "\r\n" .
					$this->getUploadMask('newsl_file', $this->overwrite, 'file') .
					'<br class="clearfloat" />' . "\r\n" .
					$this->getFilesUploadMask($this->filesFolder, $this->useFilesFolder, 'newsl_file') .
					'</div>' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n" .
					'<label>{s_label:doctitle}</label>' . "\r\n" .
					'<input type="text" name="filename_alt" value="' . htmlspecialchars($this->fileCon[1]) . '" maxlength="512" class="altText" />' . "\r\n" .
					'</div>' . "\r\n" .	
					'</div>' . "\r\n" .	
					'</div>' . "\r\n" .	
					'</div>' . "\r\n" .	
					'</li>' . "\r\n" .
					'</ul>' . "\r\n";
		
		
		
		// Previewbutton
		$output .=	'<ul>' . "\r\n" .
					'<li class="buttonPanel buttonPanel-last' . ($this->newslFormat == "html" ? ' hide' : '') . '">' . "\r\n";

		// Button preview
		$btnDefs	= array(	"type"		=> "button",
								"name"		=> "preview_newsl",
								"id"		=> "preview_newsl",
								"class"		=> 'preview right',
								"value"		=> "{s_button:preview}",
								"icon"		=> "preview"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'<br class="clearfloat" />' . "\r\n" .
					'</li>' . "\r\n";
	
		// Submitbuttons
		$output .=	'<li class="submit change">' . "\r\n";
		
		// Submitbutton neu
		if(!$this->editNewsl) {
		
			// Button submit (new)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "submit_newsl",
									"class"		=> "change",
									"value"		=> "{s_button:addnewsl}",
									"icon"		=> "ok"
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			$output .=	'<input type="hidden" name="submit_newsl" value="{s_button:addnewsl}" />' . "\r\n";
		}
		
		// Submitbutton edit
		else {
		
			// Button submit (edit)
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "edit_newsl",
									"class"		=> "change",
									"value"		=> "{s_button:takechange}",
									"icon"		=> "ok"
								);
			
			$output	.=	parent::getButton($btnDefs);
			
			// Falls der Versendenbutton hinzugefügt werden soll
			if($this->newslStatus == 0) {
		
				// Button submit (edit)
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "send_newsl",
										"id"		=> "send_newsl",
										"class"		=> "publish right",
										"value"		=> ($this->failSend ? '{s_button:resendnewsl}' : '{s_button:sendnewsl}'),
										"icon"		=> "warning"
									);
				
				$output	.=	parent::getButton($btnDefs);
			}
			else
				$output .=	'<input type="hidden" name="edit_newsl" value="{s_button:takechange}" />' . "\r\n";
		}
		

		// Falls nach teilweise erfolglosem Versand ein 2. Versuch an bestimmte Adressen erfolgen soll
		if($this->failSend && count($this->notSentMail) > 0) {
			
			foreach($this->notSentMail as $retryReceip) {
				$output .=	'<input type="hidden" name="resendMails[]" value="' . $retryReceip . '" />' . "\r\n";
			}
			
		}
		
		$output .=	'<input type="hidden" name="token" value="' . parent::$token . '" />' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n" .
					'</li>' . "\r\n" .
					'</ul>' . "\r\n" .
					'</ul>' . "\r\n" .
					'</form>' . "\r\n" .
					'</div>' . "\r\n";
			
		$output .=	'<ul>' . "\n" .
					'<li class="submit back">' . "\r\n" . 
					'<form action="' . $this->formAction . '" method="post">' . "\r\n"; // Formular mit Buttons zum Zurückgehen
		
		// Button backtolist
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "list_newsl",
								"class"		=> "back left",
								"value"		=> "{s_button:newsllist}",
								"icon"		=> "backtolist"
							);
		
		$output	.=	parent::getButton($btnDefs);
			
		$output	.=	'<input name="list_newsl" type="hidden" value="date" />' . "\r\n" .
					'</form>' . "\r\n";
			
		$output .=	$this->getAddNewslButton("right");
		
		$output .=	'<br class="clearfloat" />' . "\r\n";
		$output .=	'</li>' . "\r\n";
		$output .=	'</ul>' . "\r\n";
		
		return $output;
		
	}
	
	
	
	/**
	 * Liest Listen Sortierungs- und Filterparameter aus
	 * 
	 * @access public
	 */
	public function evalListParams()
	{
		
		if(!empty($GLOBALS['_GET']['list_newsl']))
		
			$this->sortList = $GLOBALS['_GET']['list_newsl'];

		
		
		if(!empty($GLOBALS['_POST']['list_newsl'])) {
			
			$this->sortList = $GLOBALS['_POST']['list_newsl'];
		
		}
		
		
		switch($this->sortList) {
			
			case "dateasc":
				$this->sortRes = " ORDER BY `mod_date` ASC";
				break;
				
			case "datedsc":
				$this->sortRes = " ORDER BY `mod_date` DESC";
				break;
				
			case "subjectasc":
				$this->sortRes = " ORDER BY `subject` ASC";
				break;
				
			case "subjectdsc":
				$this->sortRes = " ORDER BY `subject` DESC";
				break;
		}

			
		if(!empty($GLOBALS['_POST']['filter_sent'])) {
			
			$this->pubFilter = $GLOBALS['_POST']['filter_sent'];
			
		}		
		elseif(!empty($GLOBALS['_GET']['sent'])) {
		
			$this->pubFilter = $GLOBALS['_GET']['sent'];
		
		}
		elseif(!empty($this->g_Session['filter_sent'])) {
			
			$this->pubFilter = $this->g_Session['filter_sent'];
		
		}

		$this->setSessionVar('filter_sent', $this->pubFilter);
		
		
		if($this->pubFilter != "all") {
			
			$this->dbFilter .= ($this->dbFilter != "" ? ' AND ' : '') . "`sent` = " . ($this->pubFilter == "sent" ? "1" : "0");
		
		}
	
	}
	
	
	
	/**
	 * Erstellt eine Newsletter-Liste
	 * 
	 * @access public
	 */
	public function listNewsletters()
	{

		$output	= "";
		
		// ControlBar
		$output		 .=	$this->getNewslControlBar();
		
		
		// Falls Newsl vorhanden
		if(count($this->newslArchive) > 0) {

			// ActionBox
			$output		 .=	$this->getNewslActionBox();
			
		
			// Newsletterliste
			$output		 .= 	'<div id="newsletterList" class="dataList framedItems' . (isset($GLOBALS['_COOKIE']['dataList']) && $GLOBALS['_COOKIE']['dataList'] == 2 ? ' collapsed' : '') . '">' . "\r\n";
				
			// Pagination Nav
			$this->dataNav = Modules::getPageNav($this->limit, $this->totalRows, $this->startRow, $this->pageNum, $this->queryString, "", false, parent::getLimitForm($this->limitOptions, $this->limit));
			
			if($this->limit > 25)
				$output		 .= 	$this->dataNav;
			
			$i = 0;
			
			// Einträge durchlaufen
			foreach($this->newslArchive as $newslEntry) {
			
				if((int)$this->g_Session['userid'] == $newslEntry['userid'] ||
				   $this->g_Session['group'] == "admin" || 
				   $this->g_Session['group'] == "editor")
				{
												
					if($newslEntry['file'] != "")
						$attachment = explode("<>", $newslEntry['file']);
					else {
						$attachment[0] = "";
						$attachment[1] = "";
						$attachment[2] = "";
					}
								
					$markBox	= '<label class="markBox">' . "\r\n" .
								  '<input type="checkbox" class="addVal" name="entryNr[]" value="' . $newslEntry['id'] . '" />' . "\r\n" .
								  '</label>' . "\r\n";				

					// List entry
					$output		 .=	'<div class="listEntry' . ($i%2 ? ' alternate' : '') . '" data-menu="context" data-target="contextmenu-' . $i . '">' . "\r\n";
					
					// Date
					$lastDate	= $newslEntry['sent'] == 1 ? $newslEntry['sent_date'] : $newslEntry['mod_date'];
					
					// Header
					$output		 .=	'<div class="listEntryHeader">' . "\r\n" .
									$markBox;
					
					// Date
					$modDate	= parent::getDateString(strtotime($lastDate), $this->adminLang, false);					
					$output		 .=	'<span class="newslDate tableCell"><span title="{s_text:lastmodified} ' . $modDate . '">' . $modDate . '</span> {s_text:attime} <span>' . parent::getTimeString(strtotime($lastDate), $this->adminLang) . '</span> {s_text:time}' . ($newslEntry['sent'] == 1 ? '<span class="newsl-sent">' . parent::getIcon("check", "inline-icon") . '{s_text:sent}</span>' : '') . '</span>' . "\r\n";
									
					// Author
					$output		 .=	'<span class="newslAuthor tableCell">{s_option:author} &raquo; <span title="{s_option:author}">' . $this->getEditableAuthor($newslEntry['author_name'], $newslEntry['userid'], $newslEntry['id']) . '</span></span>'."\r\n";
					
					// Edit Buttons
					$output		 .=	'<span class="editButtons-panel" data-id="contextmenu-' . $i . '">' . "\r\n";

					// Button edit
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'editcon button-icon-only',
											"title"		=> '{s_title:editnewsl}',
											"attr"		=> 'data-action="editcon" data-actiontype="edit" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=newsl&id=' . $newslEntry['id'] . '&action=edit" data-ajaxlaod="fullpage" data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "edit"
										);
						
					$output .=	parent::getButton($btnDefs);

					// Button copy
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'copydata editcon button-icon-only',
											"title"		=> '{s_title:copynewsl}',
											"attr"		=> 'data-action="editcon" data-actiontype="copydata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=copynewsl&mod=newsl&id=' . $newslEntry['id'] . '" data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "copy"
										);
						
					$output .=	parent::getButton($btnDefs);

					// Button delete
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'delnewsl button-icon-only',
											"title"		=> '{s_title:delnewsl}',
											"attr"		=> 'data-action="deldata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=newsl&entryid=' . $newslEntry['id'] . '&action=delnewsl" data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "delete"
										);
						
					$output .=	parent::getButton($btnDefs);
					
					$output .=		'</span>' . "\r\n" .
									'</div>' . "\r\n"; // close listEntryHeader
									
					
					// Attachment
					$newslAttach	= "";
					$iconAttach		= "";
					
					if(!empty($attachment[2])) {
					
						// Icon attachment
						$iconAttach	=	parent::getIcon("attachment", "inline-icon", 'title="{s_label:attachm}"') . ' | ';
						
						// Attachment
						$newslAttach	=	'<div class="attachment">' . "\r\n" .
											($attachment[2] != "" ? '<p class="type">{s_label:attachm}</p>' : '') . "\r\n" .
											'<div class="listObject">' . "\r\n";
			
						// Pfad zur Bilddatei
						// Falls files-Ordner, den Pfad ermitteln
						if(isset($attachment[0]) && strpos($attachment[0], "/") !== false) {
							$filesImgArr		= explode("/", $attachment[0]);
							$attachment[0]		= array_pop($filesImgArr);					
							$imgPath			= CC_FILES_FOLDER . "/" . implode("/", $filesImgArr) . "/";
							$docPath			= $imgPath;
						}
						else {
							$imgPath = CC_IMAGE_FOLDER . "/";
							$docPath = CC_DOC_FOLDER . "/";
						}
				
						// Falls Bildanhang
						if($attachment[2] == "img")
							$newslAttach .=	'<div class="previewBox img">' . "\r\n" .
											'<img src="' . $imgPath . 'thumbs/' . (isset($attachment[0]) && $attachment[0] != "" ? htmlspecialchars($attachment[0]) : '../../../system/themes/' . ADMIN_THEME . '/img/noimage.png') . '" alt="' . (isset($attachment[1]) && $attachment[1] != "" ? htmlspecialchars($attachment[1]) : '') . '" title="' . (isset($attachment[1]) && $attachment[1] != "" ? htmlspecialchars($attachment[1]) : 'no image') . '" class="preview" data-img-src="' . $imgPath . (isset($attachment[0]) && $attachment[0] != "" ? htmlspecialchars($attachment[0]) : '../../system/themes/' . ADMIN_THEME . '/img/noimage.png') . '" />' . "\r\n" .
											'</div>' . "\r\n";
						
						// Falls Dokumentenanhang
						elseif($attachment[2] == "file")
							$newslAttach .=	'<div><br /><br />{s_text:doc}<br />' .
											htmlspecialchars($attachment[0]) .
											'<br />(' . htmlspecialchars($attachment[1]) . ')</div>' . "\r\n";
					
						$newslAttach	 .=	'</div></div>' . "\r\n";
					
					}
				
					// Newsl Header / Subject
					$output		 .=	'<h4 class="cc-h4 newslSubject dataListHeader toggle">{s_form:subject}: <strong>' . $newslEntry['subject'] . '</strong>';
					$output		 .=	'<span class="right">' . $iconAttach . '#' . $newslEntry['id'] . '</span>' . "\n";
					$output		 .=	'</h4>' . "\r\n";
					
					
					// listEntryContent
					$output		 .=	'<div class="listEntryContent">' . "\r\n";

					$output		 .=	$newslAttach;
					
					$output		 .= '<p class="newslText">' . $newslEntry['text'] . '</p>' . "\r\n" .
									'<br class="clearfloat"></div>' . "\r\n"; // close listEntryContent
					
					$output		 .=	'</div>' . "\r\n"; // close listEntry
					
					 $i++;
									
				} // Ende if
				
			} // Ende foreach
	
	
			$output		 .= 	'</div>' . "\r\n";


			// Datanav
			$output		 .= 	$this->dataNav;
		
		
			// Contextmenü-Script
			$output		 .=	$this->getContextMenuScript();
	
		}
		
		else {
			$output		 .=	'<p class="notice error">{s_text:nonewsl}</p>' . "\r\n";
		}

		// Add newsl button
		$output		 .=		'<span class="newNewslButton-panel buttonPanel">' . "\r\n" .
							$this->getAddNewslButton("right") .
							'<br class="clearfloat" />' . "\r\n" .
							'</span>' . "\r\n";
		
	
		return $output;
	
	}
	
	
	
	/**
	 * Erstellt ein Array mit E-Mails aus Texteingabe
	 * 
     * @param   string	$extraMailsStr	String mit E-Mailadressen aus Textfeld
	 * @access public
	 */
	public function getExtraEmails($extraMailsStr)
	{
	
		$mailStr	= trim($extraMailsStr);
		$mailStr	= str_replace(array("<", ">"), "", $mailStr);
		$mailStr	= str_replace(" ", ",", $mailStr);
		
		return array_unique(array_filter(explode(",", $mailStr)));
		
	}
	
	
	
	/**
	 * Überprüft Array mit E-Mails aus Texteingabe
	 * 
     * @param   array	$extraMails	Array mit E-Mailadressen
     * @param   string	$extraMails	String mit Feldnamen
     * @param   string	$extraMails	String mit Fehlermeldung
	 * @access	public
	 */
	public function validateEmails($extraMails, $fieldName, $errorMsg)
	{
	
		$valid	= true;
		
		foreach($extraMails as $eM) {
			if(!filter_var($eM, FILTER_VALIDATE_EMAIL)) {
				$valid = false;
				$this->wrongInput[$fieldName] = $errorMsg;
			}
		}
		
		return $valid;
	}
	
	
	
	/**
	 * Fügt neue Benutzer (E-Mails aus Texteingabe) als Newsletterempfänger zur Tabelle `user` hinzu
	 * 
     * @param   string	$userEmails	String mit E-Mailadressen aus Textfeld
	 * @access	private
	 */
	private function checkExtraEmails()
	{

		// Ggf. extra E-Mailadressen überprüfen
		if(isset($GLOBALS['_POST']['newsl_extraemails'])) {
		
			$this->extraEmails	= trim($GLOBALS['_POST']['newsl_extraemails']);
		
		}
		
		// Email-Array erstellen
		$this->extraEmailsArray	= self::getExtraEmails($this->extraEmails);
		
		if($this->extraEmails == "")
			return true;

		
		// Extra-E-Mails überprüfen
		$mailsValid		= self::validateEmails($this->extraEmailsArray, 'newsl_extraemails', "{s_error:mail2}");
		
		// Emails verifizieren, ggf. Fehlermeldung
		if($mailsValid) {
			
			$this->extraEmails = implode(",", $this->extraEmailsArray);
		
			// Falls die extra E-Mails in der Tabelle `user` gespeichert werden sollen
			if(isset($GLOBALS['_POST']['newsl_saveextraemails'])) {
		
				$this->saveExtraEmails	= true;
				
				$this->addedExtraEmails	= $this->addExtraEmailsToUser($this->extraEmailsArray);
		
				if($this->addedExtraEmails === true)
					$this->notice .= "<br />" . count($this->extraUsers) . " {s_notice:extramails}";
			}
		}
		
		return $mailsValid;
		
	}
	
	
	
	/**
	 * Fügt neue Benutzer (E-Mails aus Texteingabe) als Newsletterempfänger zur Tabelle `user` hinzu
	 * 
     * @param   string	$userEmails	String mit E-Mailadressen aus Textfeld
	 * @access	private
	 */
	private function addExtraEmailsToUser($userEmails)
	{

		// Falls leeres Array
		if(empty($userEmails)
		|| !is_array($userEmails)
		)
			return false;
		
		
		// Sonst user anlegen
		$o_user		= new User($this->DB, $this->o_lng);
		$newUsers	= array();
		
		foreach($userEmails as $userEmail) {
		
			if(!$o_user->checkUserExists($userEmail))
				$newUsers[]	= $userEmail;
		}
	
		if(count($newUsers) == 0)
			return false;
		
		$values = "";
		
		foreach($newUsers as $userEmail) {
		
			if(!$o_user->checkUserExists($userEmail)) {
				$newUsers[]		= $userEmail;
				$userName		= $this->DB->escapeString($userEmail);
				$userLang		= $this->DB->escapeString($this->editLang);
				$authCode		= md5(uniqid(time()));
				
				$this->extraUsers[$userEmail]	= $authCode; // Auth-Code speichern
			
				$values			.=	"('".$userName."','subscriber','".$userName."','".$userLang."',1,'".$authCode."'),"; // db-String
			}
		}
		
		$values = substr($values, 0, -1);
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "user`");
	
	
		// Einfügen der neuen Sprachfelder
		$insertSQL = $this->DB->query("INSERT INTO `" . DB_TABLE_PREFIX . "user` 
											(`username`, `group`, `email`, `lang`, `newsletter`, `auth_code`) 
											VALUES $values
											");

		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		return $insertSQL;
	}
	

	// checkMissingAttachment
	public function checkMissingAttachment($newslText)
	{
		foreach($this->attachmentKeyWords as $keyw) {
			if(preg_match("/" . $keyw . "/ium", $newslText))
				return true;
		}

		return false;
	
	}
	
	

	/**
	 * getNewslControlBar
	 * @access protected
	 */
	protected function getNewslControlBar()
	{
	
		$output		 =	'<div class="controlBar">' . "\r\n" .
						'<form name="sort" action="' . $this->formAction . '" method="post">' . "\r\n" . 
						'<div class="sortOption small left"><label>{s_label:sort}</label>' . "\r\n" .
						'<select name="list_newsl" class="listCat" data-action="autosubmit">' . "\r\n";
					
		$sortOptions = array("datedsc" => "{s_option:datedsc}",
							 "dateasc" => "{s_option:dateasc}",
							 "subjectasc" => "{s_option:subjectasc}",
							 "subjectdsc" => "{s_option:subjectdsc}"
							 );
		
		foreach($sortOptions as $key => $value) { // Sortierungsoptionen auflisten
			
			$output		 .=	'<option value="' . $key . '"';
			
			if($key == $this->sortList)
				$output		 .=' selected="selected"';
				
			$output		 .= '>' . $value . '</option>' . "\r\n";
		
		}
							
		$output		 .= 	'</select></div>' . "\r\n";
		
		$output		 .= 	'<div class="sortOption small left"><label>{s_label:limit}</label>' . "\r\n";
		
		$output		 .=		$this->getLimitSelect($this->limitOptions, $this->limit);
							
		$output		 .=		'</div>' . "\r\n";
		
		// Filter Optionen
		$output		 .=	'<div class="filterOptions cc-table-cell">' . PHP_EOL .
						'<div class="filterOption left"><label for="all">{s_label:all}</label>' . "\r\n" .
						'<label class="radioBox markBox">' . "\r\n" .
						'<input type="radio" name="filter_sent" id="all" value="all"' . ($this->pubFilter == "all" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . "\r\n" .
						'</label>' . "\r\n" .
						'</div>' . "\r\n" .
						'<div class="filterOption left"><label for="sent">{s_label:sent}</label>' . "\r\n" .
						'<label class="radioBox markBox">' . "\r\n" .
						'<input type="radio" name="filter_sent" id="sent" value="sent"' . ($this->pubFilter == "sent" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . "\r\n" .
						'</label>' . "\r\n" .
						'</div>' . "\r\n" .
						'<div class="filterOption left"><label for="unsent">{s_label:unsent}</label>' . "\r\n" .
						'<label class="radioBox markBox">' . "\r\n" .
						'<input type="radio" name="filter_sent" id="unsent" value="unsent"' . ($this->pubFilter == "unsent" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . "\r\n" .
						'</label>' . "\r\n" .
						'</div>' . "\r\n" .
						'</div>' . "\r\n";
					
		$output		 .=	'<input type="hidden" name="token" value="' . parent::$token . '" />' . "\r\n" .
						'</form>' . "\r\n";

	
		// list view toggle
		$output		 .= 	'<span class="editButtons-panel">' . "\r\n";
		
		// Button list
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'toggleDataList button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:toggledatalist}',
								"icon"		=> "list"
							);
			
		$output .=	parent::getButton($btnDefs);
	
		$output		 .=	'</span>' . "\r\n";
			
		$output		 .=	'</div>' . "\r\n"; // close controlBar

		
		
		// Falls Gruppenfilter oder Abofilter, Filter löschen Button einfügen
		if((!empty($this->pubFilter) && $this->pubFilter != "all")) {
			
			$filter		= '<strong>{s_label:' . ($this->pubFilter == "sent" ? '' : 'un') . 'sent}' . '</strong>';			
			
			$output .=	'<span class="showHiddenListEntries actionBox cc-hint">' . "\r\n";
			
			// Filter icon
			$output .=	'<span class="listIcon">' . "\r\n" .
						parent::getIcon("filter", "inline-icon") .
						'</span>' . "\n";

			$output .=	'{s_label:filter}: ' . $filter;
			
			$output .=	'<form action="'.$this->formAction.'" method="post">' . "\r\n";
			
			$output .=	'<span class="editButtons-panel">' . "\r\n";

			// Button remove filter
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'removefilter ajaxSubmit button-icon-only',
									"title"		=> '{s_title:removefilter}',
									"icon"		=> "close"
								);
				
			$output .=	parent::getButton($btnDefs);
						
			$output .=	'<input type="hidden" value="all" name="filter_sent">' . "\r\n" .
						'</span>' . "\r\n" .
						'</form>' . "\r\n" .
						'</span>' . "\r\n";
		}

		return $output;
	
	}
	
	

	/**
	 * getNewslActionBox
	 * @access protected
	 */
	protected function getNewslActionBox()
	{

		// Checkbox zur Mehrfachauswahl zum Löschen und Publizieren
		$output =		'<div class="actionBox">' . "\r\n" .
						'<form action="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=newsl&entryid=array&action=" method="post" data-history="false">' . "\r\n" .
						'<label class="markAll markBox" data-mark="#newsletterList">' . "\r\n" .
						'<input type="checkbox" id="markAllLB-form" data-select="all" /></label>' . "\r\n" .
						'<label for="markAllLB-form" class="markAllLB"> {s_label:mark}</label>' . "\r\n" .
						'<span class="editButtons-panel">' . "\r\n";

		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'delAll delNewsl delSelectedListItems button-icon-only',
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

	
	// getEditableAuthor
	protected function getEditableAuthor($author, $authorID, $id = 1)
	{
	
		if(!$this->editorLog)
			return $author;
		
		$output	= "";
		
		$output	.= '<a href="#" id="authorname-' . $id . '" data-type="select" data-pk="1" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=setauthor&mod=' . parent::$type . '&id=' . $id . '" data-value="' . $authorID . '" data-title="{s_option:author}">' . $author . '</a>' . "\n";
		
		$output	.= $this->getEditableScriptTag('"#authorname-' . $id . '"');
		
		return	$output;
	
	}
	

	// getScriptTag
	public function getScriptTag()
	{
	
		return	'<script>' . "\r\n" .
				'head.ready(function(){' . "\r\n" .
				'head.load({tagEditorcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.css"});' . "\r\n" .
				'head.load({tagEditorcaret: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.caret.min.js"});' . "\r\n" .
				'head.load({tagEditor: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.js"});' . "\r\n" .
					'$("document").ready(function(){' . "\r\n" .
				'head.ready("tagEditor", function(){' . "\r\n" .
						'$("#newsl_extraemails").tagEditor({' . "\r\n" .
							'maxLength: 2048,' . "\r\n" .
							'delimiter: ", ;\n",' . "\r\n" .
							'onChange: function(field, editor, tags){' . "\r\n" .
								'var mailItems = editor.find(".tag-editor-tag");
								mailItems.each(function(i,e){
								if(isValidEmailAddress($(e).text())){
									$(e).removeClass("invalid");
								}else{$(e).addClass("invalid");}
								});
								var extraLab = $(\'label[for="newsl_extraemails"]\');' . "\r\n" .
								'extraLab.html(extraLab.html().replace(/\([0-9]+\)/, "(" + tags.length + ")"));' . "\r\n" .
								'editor.next(".deleteAllTags-panel").remove();' . "\r\n" .
								'if(tags.length > 0 && !editor.next(".deleteAllTags-panel").length){ editor.after(\'<span class="deleteAllTags-panel buttonPanel"><button class="deleteAllTags cc-button button button-small button-icon-only btn right" type="button" role="button" title="{s_javascript:removeall}"><span class="cc-admin-icons icons cc-icon-cancel-circle">&nbsp;</span></button><br class="clearfloat" /></span>\'); }' . "\r\n" .
								'editor.next(".deleteAllTags-panel").children(".deleteAllTags").click(function(){' . "\r\n" .
									'for (i = 0; i < tags.length; i++) { field.tagEditor("removeTag", tags[i]); }' . "\r\n" .
								'});' . "\r\n" .
							'}' . "\r\n" .
						'});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}
	

	// getEditableScriptTag
	public function getEditableScriptTag($tag)
	{
	
		return	'<script>' . "\r\n" .
				'head.ready(function(){' . "\r\n" .
				'head.load({xeditablecss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/x-editable/css/jqueryui-editable.css"});' . "\r\n" .
				'head.load({xeditable: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/x-editable/js/jqueryui-editable.min.js"});' . "\r\n" .
				'head.ready("xeditable", function(){' . "\r\n" .
					'$("document").ready(function(){' . "\r\n" .
						'$(' . $tag . ').editable({' . "\r\n" .
							'source: "' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=getauthors&mod=' . parent::$type . '",' . "\r\n" .
							'sourceCache: true,' . "\r\n" .
							'showbuttons: false' . "\r\n" .
						'});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}
	

	// getSelectScriptTag
	public function getSelectScriptTag()
	{

		$output	=	'<script>' . "\r\n" .
					'head.ready("jquery", function(){' . "\r\n" .
					'head.load({pqselectcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/pqSelect/pqselect.min.css"});' . "\r\n" .
					'head.load({pqselect: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/pqSelect/pqselect.min.js"});' . "\r\n" .
					'});' . "\r\n" .
					'</script>' . "\r\n";
		
		$output	.=	'<script>
					head.ready("pqselect", function(){
					$(document).ready(function(){';
		
		//initialize the pqSelect widget.
		$opts	= '{
						multiplePlaceholder: "' . ContentsEngine::replaceStaText('{s_option:choose}') . '",
						selectallText: "' . ContentsEngine::replaceStaText('{s_label:chooseall}') . '",
						displayText: "%i1 ' . ContentsEngine::replaceStaText('{s_common:of}') . ' %i2 ' . ContentsEngine::replaceStaText('{s_common:selected}') . '",
						width: "calc(100% - 0px)", //adds width to options    
						minWidth: "275", //width    
						flexWidth: true, //flexWidth    
						checkbox: true, //adds checkbox to options    
						maxDisplay: 10,
						maxSelect: 0,
						selectallText: ""
		}';
		
		$output	.=	'$("#newsl_group").pqSelect( ' . $opts . ').pqSelect( "open" );'."\n";
		$output	.=	'$("#newsl_group").pqSelect( "refreshData" )'."\n";
		
	
		$output	.=	'});
					});
					</script>' . "\r\n";
		
		return $output;
	
	}
	

	// getBacktoListButton
	public function getBacktoListButton()
	{
		
		$output =	'<form action="' . $this->formAction . '" method="post">' . "\r\n"; // Formular mit Buttons zum Zurückgehen

		$output	.=	'<span class="editButtons-panel">' . "\r\n";
		
		// Button backtolist
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'button-icon-only',
								"value"		=> "{s_button:newsllist}",
								"text"		=> "",
								"title"		=> '{s_button:newsllist}',
								"attr"		=> 'data-ajaxform="true"',
								"icon"		=> "backtolist"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		
		$output .=	'<input type="hidden" value="date" name="list_newsl">' . "\r\n" .
					'</span>' . "\r\n" .
					'</form>' . "\r\n";
	
		return $output;
	
	}
	

	// getAddFormButton
	public function getAddNewslButton($align = "left")
	{
		
		$output	=	'<form action="' . $this->formAction . '" method="post">' . "\r\n";
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "add_new",
								"class"		=> $align . " add",
								"value"		=> "{s_button:addnewsl}",
								"icon"		=> "new"
							);
		
		$output	.=	parent::getButton($btnDefs);
		
		$output	.=	'<input name="add_new" type="hidden" value="1" />' . "\r\n" .
					'</form>' . "\r\n";

		return $output;
	
	}

}
