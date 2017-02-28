<?php
namespace Concise;


###################################################
################  Edit Elements  ##################
###################################################

// Elemente editieren

class EditElements extends Admin
{

	private $action				= "";
	private $editID				= "";
	private $newQS				= "";
	private $editRedirect		= "";
	private $editContentsTab		= "";
	private $editContentsTabPrev	= "";
	private $editContentsTabDB		= "";
	private $editContentsTabPrevDB	= "";
	private $isFE				= false;
	private $notify				= false;
	private $con				= "";
	private $sortParams			= array();
	private $copyCon			= array();
	private $pasteConNr			= "";
	private $maxConNr			= "";
	private $currConNr			= "";
	private $newConType			= "";
	private $editConType		= "";
	private $redirect			= "";
	
	
	public function __construct($DB, $o_lng, $themeType)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		// Theme-Setup
		$this->getThemeDefaults($themeType);
	
	}
	
	public function conductAction()
	{
		
		$this->editContentsTab 			= parent::$tableContents;
		$this->editContentsTabDB		= DB_TABLE_PREFIX . $this->editContentsTab;

		// Falls aus FE aufgerufen
		if(isset($GLOBALS['_GET']['fe']) && $GLOBALS['_GET']['fe'] == 1) {
			$this->isFE					= true;
			$this->adminPage			= false;
		}

		if(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] != "")
			$this->action = $GLOBALS['_GET']['action'];


		if(!empty($GLOBALS['_GET']['area'])
		&& ($GLOBALS['_GET']['area'] == "all" || in_array($GLOBALS['_GET']['area'], parent::$contentTables))
		) {
			$this->editContentsTab			= $GLOBALS['_GET']['area'];
			$this->editContentsTabPrev		= $this->editContentsTab . "_preview";
			$this->editContentsTabDB		= DB_TABLE_PREFIX . $this->editContentsTab;
			$this->editContentsTabPrevDB	= DB_TABLE_PREFIX . $this->editContentsTabPrev;
		}
		
		if(isset($GLOBALS['_GET']['con']) && $GLOBALS['_GET']['con'] != "" && is_numeric($GLOBALS['_GET']['con'])) {
			$this->con = $GLOBALS['_GET']['con'];
			
			// Falls sortiert werden soll
			if(isset($GLOBALS['_GET']['pastecon']) && $GLOBALS['_GET']['pastecon'] != "" && is_numeric($GLOBALS['_GET']['pastecon']))
				$this->pasteConNr = $GLOBALS['_GET']['pastecon'];
			else
				$this->pasteConNr = $this->con+1;
		}
		if(isset($GLOBALS['_GET']['conmax']) && $GLOBALS['_GET']['conmax'] != "" && is_numeric($GLOBALS['_GET']['conmax']))
			$this->maxConNr = $GLOBALS['_GET']['conmax'];
			
		if(isset($GLOBALS['_GET']['connr']) && $GLOBALS['_GET']['connr'] != "" && is_numeric($GLOBALS['_GET']['connr']))
			$this->currConNr = $GLOBALS['_GET']['connr'];

		if(isset($GLOBALS['_GET']['id']) && $GLOBALS['_GET']['id'] != "")
			$this->editID = $this->DB->escapeString($GLOBALS['_GET']['id']);

		if(isset($GLOBALS['_GET']['type']) && $GLOBALS['_GET']['type'] != "") {
			$this->editConType	= $this->DB->escapeString($GLOBALS['_GET']['type']);
			$this->newConType	= $this->editConType;
		}

		
		// Locking checken
		if($this->checkEditLocking())
			return false;
		
		
		// Falls kein Lock, Action ausführen
		if($this->action == "cache")
			$this->refreshHtmlCache();

		if($this->action == "fe-changes")
			$this->setFeChanges();
		
		if($this->action == "fe-edit")
			$this->feEdit();
		
		if($this->action == "fe-editcon")
			$this->feEditElement();
		
		if($this->action == "fe-resize")
			$this->feResize();
		
		if($this->action == "up" || $this->action == "down") // Andernfalls, wenn sortiert werden soll
			$this->sortUpDown();
		
		// Falls die Liste zum Anlegen eines neuen Inhaltselements angezeigt werden soll
		if($this->action == "listelements")
			$this->listElements();
		
		// Element veröffentlichen/verstecken
		if($this->action == "publish")
			$this->publishElements();
		
		// Löschen
		if($this->action == "del")
			$this->deleteElements();

		// Kopieren
		if($this->action == "copy" || $this->action == "cut")
			$this->copyCutElements();
		
		// Sortieren
		if($this->action == "sort") // (über sortable)
			$this->sortElements();

		// Einfügen
		if($this->action == "new" || $this->action == "sort" || $this->action == "paste")
			$this->insertElements();

		// Einfügen abbrechen
		if($this->action == "cancelpaste") {
			$this->cancelPaste();
			return true;
		}

		// Grid classes
		if($this->action == "gridclasses")
			$this->getGridClasses();

		// History restore
		if($this->action == "restore")
			$this->restoreHistory();

		// History clear
		if($this->action == "clearhistory")
			$this->clearHistory();


		if(isset($this->g_Session['copycon']) && $this->action != "copy" && $this->action != "cut") {
			$this->unsetSessionKey('copycon');
		}
		

		// Falls ein Redirect(ziel) per GET-Parameter mitgegeben wurde (FE-Mode) und kein neues Element angelegt werden soll (außer Text), Seite neu laden
		if(!$this->isFE || $this->action != "new")
			$this->redirectPage();
		
		
		return true;
	
	} // Ende Methode conductAction

	
	// Falls der HTML-Cache neu angelegt werden soll
	private function refreshHtmlCache()
	{
		
		$affect = "all";
		
		if(isset($GLOBALS['_GET']['id'])) {
			
			if(is_numeric($GLOBALS['_GET']['id']))
				$affect = (int)$GLOBALS['_GET']['id'];
			elseif(strpos($GLOBALS['_GET']['id'], ".tpl") !== false)
				$affect = $GLOBALS['_GET']['id'];
		}
		
		$result = $this->refreshCache($affect);
		
		$this->outputJSON($result);
		exit;

	}

	


	// Falls vom Frontend aus Änderungen übernommen bzw. verworfen werden sollen
	private function setFeChanges()
	{
		
		if(empty($GLOBALS['_GET']['param']))
			return false;
		

		$param = $GLOBALS['_GET']['param'];
		
		if($param == "apply") {
			$param = "1";
			$notice	= ContentsEngine::replaceStaText('{s_notice:takechange}');		
		}
		else {
			$param = "0";
			$notice	= ContentsEngine::replaceStaText('{s_notice:cancelcon}');		
		}
		
		// Änderungen an Seiten- bzw. Templateinhalten übernehmen/veröffentlichen
		#$preview	= $admin->isPreview ? "_preview" : ""; // überprüfen, ob Änderungen vorliegen -> Tabellenzusatz "_preview"
		$result		= $this->applyConChanges($param, $this->editID, $this->editContentsTab);
		
		// Erfolgsmeldung im FE ausgeben
		if($result !== false)
			$this->setSessionVar('fe-notice', $notice);

		echo $result; // Ergebnis an js		
		exit;
		
	}


	// Falls vom Frontend aus editiert werden soll (z.B. Bilderaustausch)
	private function feEdit()
	{
		
		$type			= "";
		$src			= "";
		$imgWidth 		= "";
		$imgHeight		= "";
		$alt 			= "";
		$title			= "";
		$caption		= "";
		$link			= "";
		$imgclass		= "";
		$imgclassold	= "";
		$imgextra		= "";
		$gall			= "";
		$gallType		= "slimbox";
		$newCon 		= "";
		$allLangs		= false;
			
		
		if(isset($GLOBALS['_GET']['type']))
			$type = $this->DB->escapeString($GLOBALS['_GET']['type']);

		if(isset($GLOBALS['_GET']['src']))
			$src = $this->DB->escapeString($GLOBALS['_GET']['src']);

		if(isset($GLOBALS['_GET']['width']))
			$imgWidth = $this->DB->escapeString($GLOBALS['_GET']['width']);

		if(isset($GLOBALS['_GET']['height']))
			$imgHeight = $this->DB->escapeString($GLOBALS['_GET']['height']);

		if(isset($GLOBALS['_GET']['alt']))
			$alt = $this->DB->escapeString($GLOBALS['_GET']['alt']);

		if(isset($GLOBALS['_GET']['tit']))
			$title = $this->DB->escapeString($GLOBALS['_GET']['tit']);

		if(isset($GLOBALS['_GET']['caption']))
			$caption = $this->DB->escapeString(urldecode($GLOBALS['_GET']['caption']));

		if(isset($GLOBALS['_GET']['link']))
			$link = $this->DB->escapeString(str_replace(PROJECT_HTTP_ROOT, "{#root}", $GLOBALS['_GET']['link']));

		if(isset($GLOBALS['_GET']['imgclass']))
			$imgclass = $this->DB->escapeString($GLOBALS['_GET']['imgclass']);

		if(isset($GLOBALS['_GET']['imgclassold']))
			$imgclassold = $this->DB->escapeString($GLOBALS['_GET']['imgclassold']);

		if(isset($GLOBALS['_GET']['imgextra']))
			$imgextra = $this->DB->escapeString($GLOBALS['_GET']['imgextra']);

		if(isset($GLOBALS['_GET']['gall']))
			$gall = $this->DB->escapeString($GLOBALS['_GET']['gall']);

		if(isset($GLOBALS['_GET']['oldgall']))
			$oldGall = $this->DB->escapeString($GLOBALS['_GET']['oldgall']);

		if(isset($GLOBALS['_GET']['galltype']))
			$gallType = $this->DB->escapeString($GLOBALS['_GET']['galltype']);

		if(isset($GLOBALS['_GET']['oldgalltype']))
			$oldGallType = $this->DB->escapeString($GLOBALS['_GET']['oldgalltype']);

		if(isset($GLOBALS['_GET']['langs']) && $GLOBALS['_GET']['langs'] == "true")
			$allLangs = true;
		
		$targetCon		= $this->DB->escapeString("con" . $this->currConNr . "_" . $this->editLang);
		$targetStyle	= $this->DB->escapeString("styles-con" . $this->currConNr);
		$styleString	= "";
		$styleStringOld	= "";
		
		
		// Bildmaße als Styleattr
		if($imgWidth != "")
			$styleString	.= "width:" . (int)$imgWidth . "px;";
		if($imgHeight != "")
			$styleString	.= "height:" . (int)$imgHeight . "px;";
		
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->editContentsTabPrevDB . "`");


		// Falls Bildelement
		if($type == "img") {


			// Falls keine neue Bilderquelle angegeben wurde, den alten Bildnamen auslesen
			if($src == "") {
			
				// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
				$querySrc = $this->DB->query(  "SELECT `" . $targetCon . "` 
													FROM `" . $this->editContentsTabPrevDB . "` 
												WHERE `page_id` = '$this->editID'
												");
				
				$qArr	= explode("<>", $querySrc[0][$targetCon]);
				$src	= reset($qArr);
			}

			// Falls Bildmaße angegeben sind, alten Stylestr auslesen
			// Datenbanksuche
			$queryStyle = $this->DB->query("SELECT `" . $targetStyle . "` 
												FROM " . $this->editContentsTabPrevDB . " 
											WHERE `page_id` = '$this->editID'
											");
			
			$styleStringOld	= $queryStyle[0][$targetStyle];
			
			$styleStringOld	= preg_replace("/width\:\s?[0-9]+px;/", "", $styleStringOld);
			$styleStringOld	= preg_replace("/height\:\s?[0-9]+px;/", "", $styleStringOld);

			
			$stylesArr		= (array)json_decode($styleStringOld);
			
			if(isset($stylesArr['style']))
				$stylesArr['style'] .= $styleString;
			else
				$stylesArr['style']  = $styleString;

			$styleString	= json_encode($stylesArr);
				
			
			// Falls die Änderung für alle Sprachen übernommen werden soll
			if($allLangs) {
				
				foreach($this->o_lng->installedLangs as $eachlang) {
					
					$targetCon	= $this->DB->escapeString("con" . $this->currConNr . "_" . $eachlang);
					$newCon 	.= "`" . $targetCon . "` = '" . $src . "<>" . $alt . "<>" . $title . "<>" . $caption . "<>" . $link . "<>" . $imgclass . "<>" . $imgextra . "',";								
				}
				
				$newCon = substr($newCon, 0, -1);
			}
			else
				$newCon 	= "`" . $targetCon . "` = '" . $src . "<>" . $alt . "<>" . $title . "<>" . $caption . "<>" . $link . "<>" . $imgclass . "<>" . $imgextra . "'";
		
			// Style
			if($styleString != "")
				$newStyle	= ", `" . $targetStyle . "` = '" . $this->DB->escapeString($styleString) . "'";
			else
				$newStyle	= "";
			
			// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
			$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
													SET " . $newCon .
													$newStyle . " 
													WHERE `page_id` = '$this->editID'
													");
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
			echo(
				json_encode(
							array(	"result"		=> $queryUpdate1 === true ? "1" : "0",
									"imgclass"		=> !empty(parent::$styleDefs['img' . $imgclass]) ? parent::$styleDefs['img' . $imgclass] : "",
									"imgclassshort"	=> $imgclass,
									"imgclassold"	=> !empty(parent::$styleDefs['img' . $imgclassold]) ? parent::$styleDefs['img' . $imgclassold] : ""
							)
				)
			);
			
			
			exit;
			return true;
		
		}
		
		
		// Falls Galerieelement
		if($type == "gall") {
			
			// Falls die Änderung für alle Sprachen übernommen werden soll
			if($allLangs || $oldGall == "") {
				
				foreach($this->o_lng->installedLangs as $eachlang) {
					
					$targetCon	= $this->DB->escapeString("con" . $this->currConNr . "_" . $eachlang);
					
					// Falls noch kein Galerieinhalt bestand
					if($oldGall == "")
						$newCon 	.= "`" . $targetCon . "` = '" . $gall . "/" . $gallType . "//1/1/1/',";								
					else
						$newCon 	.= "`" . $targetCon . "` = REPLACE(`" . $targetCon . "`, '" . $oldGall . "/" . $oldGallType . "/', '" . $gall . "/" . $gallType . "/'),";
				}
				
				$newCon = substr($newCon, 0, -1);
			}
			else
				$newCon 	= "`" . $targetCon . "` = REPLACE(`" . $targetCon . "`, '" . $oldGall . "/" . $oldGallType . "/', '" . $gall . "/" . $gallType . "/')";

				
			// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
			$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
													SET " . $newCon . " 
													WHERE `page_id` = '$this->editID'
													");
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");

			echo "$queryUpdate1";
			
			exit;
			return true;
			
		}
		
		// Falls HTML-Element
		if($type == "html") {
			
			if(isset($GLOBALS['_POST']['htmlContent'])) {
			
				$htmlCon = $this->DB->escapeString($GLOBALS['_POST']['htmlContent']);
		
				// Falls die Änderung für alle Sprachen übernommen werden soll
				if($allLangs) {
					
					foreach($this->o_lng->installedLangs as $eachlang) {
						
						$targetCon	= $this->DB->escapeString("con" . $this->currConNr . "_" . $eachlang);
						$newCon 	.= "`" . $targetCon . "` = '" . $htmlCon . "',";								
					}
					
					$newCon = substr($newCon, 0, -1);
				}
				else
					$newCon 	= "`" . $targetCon . "` = '" . $htmlCon . "'";
		
					
				// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
				$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
														SET " . $newCon . " 
														WHERE `page_id` = '$this->editID'
														");
				
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
				
				#echo "$queryUpdate1";
		
			unset($GLOBALS['_GET']['id']); // ID zurücksetzen falls Daten-Element vorhanden
			$GLOBALS['_GET']['page'] = str_replace(PAGE_EXT, "", $this->getRedirectPage());
		
			
			require_once(PROJECT_DOC_ROOT . "/inc/classes/Contents/class.ContentsEdit.php"); // Klasse einbinden
			$o_contents				= new ContentsEdit($this->DB, $this->o_lng, true);
			$contents				= $o_contents->getContents();
			$output					= $o_contents::$o_mainTemplate->getTemplate(true);
			
			echo(
				json_encode(
							array(	"content"	=> $output
							)
				)
			);
				
				exit;
				return true;
			
			}
			
			return false;
		
		}
		
	} // Ende if fe-edit


	// Falls vom Frontend aus editiert werden soll (andere Inhaltselemente)
	private function feEditElement()
	{
	
		// Falls im Adminbereich gelöscht wurde
		require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
		require_once SYSTEM_DOC_ROOT . "/inc/admintasks/edit/admin_edit.inc.php"; // Admin-Task einbinden
		
		$isTpl					= strpos($this->editContentsTab, "main") !== false ? false : true;
		$adminTask				= $isTpl ? 'tpl' : 'edit';
		$adminE					= new Admin_Edit($this->DB, $this->o_lng, $adminTask);
		$editOutput				= "";
		$ajaxOutput				= "";

		// Theme-Setup
		$adminE->getThemeDefaults("admin");
		$adminE::$tableContents		= $this->editContentsTab;
		$adminE->editElementNumber	= (int)$this->con;
		$adminE->editId				= $this->editID;
		$adminE->isTemplateArea		= $isTpl;
		
		// Head-Definitionen (headExt)
		$adminE->getAdminHeadIncludes(true);
		
		// Head-Dateien zusammenführen
		$adminE->setHeadIncludes();

		if($this->notify)
			$adminE->notice			= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText($notice)), "success");
		
		
		// Redirect
		if(isset($GLOBALS['_GET']['red']) && $GLOBALS['_GET']['red'] != "")
			$this->redirect = urldecode($GLOBALS['_GET']['red']);

		// show fieldset
		if(!empty($GLOBALS['_GET']['tabactive'])
		&& $GLOBALS['_GET']['tabactive'] == "styles"
		)
			$adminE->activeTab		= 1;
		
		// show fieldset
		if(!empty($GLOBALS['_GET']['fieldactive']))
			$adminE->showFieldset	= $GLOBALS['_GET']['fieldactive'];

		
		$editOutput	.= '<div id="mediaList-' . time() . '" class="cc-fe-medialist-default cc-fe-box cc-contype-' . $this->editConType . ' cc-row fullBox mediaList" data-type="' . $this->editConType . '" data-connum="' . $this->con . '" data-fe="true">' . PHP_EOL;
		/*
		$editOutput	.=	'<ul class="cc-tabs-tabheader" role="tablist">' . PHP_EOL .
						'<li class="cc-tab-details" role="tab"><a href="#cc-elementedit-tabcon-1">{s_text:settings}</a></li>' . PHP_EOL .
						'<li class="cc-tab-styles" role="tab"><a href="#cc-stylesbox-' . $this->con . '">{s_header:styles}</a></li>' . PHP_EOL .
						'</ul>' . PHP_EOL;
		*/
		$editOutput	.= '<button class="closeDetailsBox btnDetailsBox cc-button close button-icon-only" title="{s_title:close}"><span class="cc-admin-icons cc-icons cc-icon-cancel-circle"></span></button>' . PHP_EOL;
		
		$editOutput	.= '<form action="' . SYSTEM_HTTP_ROOT . '/access/editElements.php?page=admin&action=fe-editcon&type=' . $this->editConType . '&con=' . $this->con . '&id=' . $this->editID . '&area=' . $this->editContentsTab . '&red=' . urlencode($this->redirect) . '&edit_id=' . $this->editID . '" id="editElementDetails-form" method="post" enctype="multipart/form-data" accept-charset="UTF-8"' . (!empty($this->redirect) ? ' data-ajax="false"' : '') . ' data-history="false">' . PHP_EOL;
		
		// Edit content
		$editOutput	.= '<div class="cc-edit-element-box">' . PHP_EOL;
		
		$adminE->getTaskContents(true);
		$editOutput	.= $adminE->getContentElement($this->con, false);
		
		$editOutput	.= '</div>' . PHP_EOL;

		$editOutput	.= $adminE->getElementsSubmitButton(true);

		$editOutput	.= '</form>' . PHP_EOL;
		
		// Tabs script tag
		$editOutput .= $adminE->getTabsScriptTag($adminE->activeTab);
		$editOutput	.= $adminE->getScriptTags();
		$editOutput	.= $adminE->getScriptCode();
		$editOutput	.= '<script>' ."\n" . implode("\n\n", $adminE->scriptCode) . '</script>' . "\n";

		$editOutput	.= '</div>' . PHP_EOL;
		
		$ajaxOutput	= parent::replaceStaText($editOutput);
		$ajaxOutput	= parent::replaceStyleDefs($ajaxOutput);
		
		
		// If form has been submitted without errors
		if(empty($GLOBALS['_GET']['getform'])
		&& !count($adminE->wrongInput)
		){
			
			$GLOBALS['_GET']['page']	= str_replace(PAGE_EXT, "", urldecode($GLOBALS['_GET']['red']));

			require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.ContentsEdit.php"; // FE-Klasse einbinden
			
			$o_contents		= new ContentsEdit($this->DB, $this->o_lng, true); // Inhalte der aktuellen Seite (Edit-Modus)
			$contents		= $o_contents->getContents();
		
			// HTML-Objekt erstellen
			parent::$o_html	= new HTML($o_contents);
			
			// HTML
			#self::$o_html->printHead($this->lang, $this->pageTitle);
			#self::$o_html->printBody($bodyID, $bodyClass, $this->preview, $this->feModeStatus);
			
			$bodyContent	= parent::$o_mainTemplate->getTemplate(true);
			parent::$o_html->triggerGlobalAddHeadCodeEvent();
			
			// Template ausgeben
			$content= $bodyContent;

			/*
			// alternative via stream
			$opts = array(	'http'=>array(	'method'=>"GET",
											'header'=>"Accept-language: en\r\n" .
											'Cookie: '.$_SERVER['HTTP_COOKIE']."\r\n"
									)
					);

			$context = stream_context_create($opts);
			$content = file_get_contents( PROJECT_HTTP_ROOT . "/" . $this->getRedirectPage(), false, $context);
			*/
			
			echo json_encode(
							array(	"success"		=> "1",
									"content"		=> $content,
									"cssFiles"		=> $o_contents->cssFiles,
									"scriptFiles"	=> $o_contents->scriptFiles,
									"scriptCode"	=> parent::$o_html->getScriptCodeTags(parent::$o_html->globalScriptCode)
							)
				);
			exit;			
		}
		
		
		// If form output
		echo(
			json_encode(
						array(	"html"		=> $ajaxOutput,
								"elemid"	=> '#pageID-' . $this->editID . '-area-' . str_replace("contents_", "", $this->editContentsTab) . '-conID-' . $this->con,
								"errors"	=> $adminE->wrongInput,
								"scripts"	=> $adminE->scriptFiles,
								"scriptcode"=> $adminE->scriptCode,
								"css"		=> $adminE->cssFiles
						)
			)
		);
		exit;

		return false;
	
	} // Ende if fe-editcon
	

	// Falls vom Frontend aus resized werden soll (columns)
	private function feResize()
	{
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.Contents.php"; // Contents einbinden
		
		$cols		= 12;
		$colsOld	= "";
		
		if(isset($GLOBALS['_GET']['cols']))
			$cols	= (int)$GLOBALS['_GET']['cols'];

		if($cols < 1)
			$cols	= 1;

		if($cols > 18)
			$cols	= 18;
		
		$targetStyle	= $this->DB->escapeString("styles-con" . $this->currConNr);
		$styleString	= "";
		$styleStringOld	= "";
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->editContentsTabPrevDB . "`");


		// Falls Bildmaße angegeben sind, alten Stylestr auslesen
		// Datenbanksuche
		$queryStyle = $this->DB->query( "SELECT `" . $targetStyle . "` FROM `" . $this->editContentsTabPrevDB . "` 
												WHERE `page_id` = '$this->editID'
												");
		
		$styleStringOld		= $queryStyle[0][$targetStyle];
		
		$stylesArr			= (array)json_decode($styleStringOld);
		
		$colsOld			= $stylesArr["cols"];
		$stylesArr["cols"]  = $cols;

		$styleString		= json_encode($stylesArr);
		
		
		// Style
		$newStyle	= "`" . $targetStyle . "` = '" . $this->DB->escapeString($styleString) . "'";

		
		// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
		$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
												SET " . $newStyle . " 
												WHERE `page_id` = '$this->editID'
												");
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");


		echo(
			json_encode(
						array(	"result"	=> $queryUpdate1 === true ? "1" : "0",
								"colcnt"	=> $cols,
								"colsnew"	=> parent::$styleDefs['col' . $cols],
								"colsold"	=> Contents::getColumnGridClass($colsOld)
						)
			)
		);
		
		exit;
		return true;
		
	} // Ende if fe-resize

	
	
	// Sortieren von Inhalts-Elementetn
	private function sortUpDown()
	{
		
		#var_dump($this->action.$this->con);
			
		// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
		$query = $this->DB->query("SELECT *
										FROM `" . $this->editContentsTabPrevDB . "` 
										WHERE `page_id` = '$this->editID' 
										");
		
		
		#var_dump($query);
		
		$contentNumber = count($query[0])/count($this->o_lng->installedLangs)-$this->con;
		#var_dump($contentNumber);
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->editContentsTabPrevDB . "`");
		
		foreach($this->o_lng->installedLangs as $lang) { // Inhalte nach Sprache auslesen und updaten
			
			$i = $this->con;
			if($this->action == "up")
				$j = $this->con-1;
			elseif($this->action == "down")
				$j = $this->con+1;
		
		
			$moveConKey			= "con" . $i . "_" . $lang;
			$targetConKey		= "con" . $j . "_" . $lang;
			$moveTypeKey		= "type-con" . $i;
			$targetTypeKey		= "type-con" . $j;
			$moveStylesKey		= "styles-con" . $i;
			$targetStylesKey	= "styles-con" . $j;

			$moveCon			= $this->DB->escapeString($query[0]["con" . $i . "_" . $lang]);
			$targetCon			= $this->DB->escapeString($query[0]["con" . $j . "_" . $lang]);
			$moveType			= $this->DB->escapeString($query[0]["type-con" . $i]);
			$targetType			= $this->DB->escapeString($query[0]["type-con" . $j]);
			$moveStyles			= $this->DB->escapeString($query[0]["styles-con" . $i]);
			$targetStyles		= $this->DB->escapeString($query[0]["styles-con" . $j]);


			// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
			$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
													SET `" . $targetConKey . "` = '$moveCon', 
														`" . $moveConKey . "` = '$targetCon', 
														`" . $targetStylesKey . "` = '$moveStyles', 
														`" . $moveStylesKey . "` = '$targetStyles', 
														`" . $targetTypeKey . "` = '$moveType', 
														`" . $moveTypeKey . "` = '$targetType' 
													WHERE `page_id` = '$this->editID'
													");
			
			#var_dump($queryUpdate1);
			
		}
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
	}
		
		
	// Falls die Liste zum Anlegen eines neuen Inhaltselements angezeigt werden soll
	private function listElements()
	{

		// Liste generieren
		$adminContent =	$this->listContentTypes(true);
		$adminContent =	ContentsEngine::replaceStaText($adminContent);
		$adminContent =	ContentsEngine::replaceStyleDefs($adminContent);
		echo $adminContent;
		exit;
		die();

	}

	
	// Element veröffentlichen/verstecken
	private function publishElements()
	{
		
		$pubItems	= array($this->con);
		$sqlStr		= "";
		$status		= 1;
		$isArray	= false;
		
		if(isset($GLOBALS['_GET']['array']) && isset($GLOBALS['_GET']['items'])) {
			$pubItems	= explode(",", $GLOBALS['_GET']['items']);
			$isArray	= true;
		}
		
		if(count($pubItems) == 0) {
			echo "0";
			exit;
		}
		
		if(isset($GLOBALS['_GET']['status']) && $GLOBALS['_GET']['status'] == 0)
			$status		= 0;
		
		$statusOld	= $status ? 0 : 1;
		
		foreach($pubItems as $pubItem) {
		
			$pubItem	= $this->DB->escapeString($pubItem);
			
			$sqlStr .= "`styles-con" . $pubItem . "` = REPLACE(`styles-con" . $pubItem . "`, '<hide>', ''),"; // (= alte Auszeichnung)
			$sqlStr .= "`styles-con" . $pubItem . "` = REPLACE(`styles-con" . $pubItem . "`, '\"hide\":" . $statusOld . "', '\"hide\":" . $status . "'),";
		}
		
		$sqlStr = substr($sqlStr, 0, -1);
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->editContentsTabPrevDB . "`");

		
		// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
		$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
												SET " . $sqlStr . " 
												WHERE `page_id` = '$this->editID'
												");
		#die(var_dump($queryUpdate1.$GLOBALS['_GET']['status']));
		
			
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		if($isArray) {
			if(isset($GLOBALS['_GET']['connr'])) unset($GLOBALS['_GET']['connr']);
			$this->returnEditContents();
		}
		else
			echo "$queryUpdate1";
		
		exit;
	
	}


	// Löschen
	private function deleteElements()
	{
		
		#var_dump($this->action.$this->con);
		$conNrArr	= array();
		$notice		= "{s_notice:delcon}";

		// Falls multi-delete
		if(isset($GLOBALS['_GET']['array'])) {
			if(isset($GLOBALS['_POST']['markConNr']))
				$conNrArr	= array_keys($GLOBALS['_POST']['markConNr'], true);
		}
		else
			$conNrArr	= array($this->con);
		
		// Falls zu löschende Elemente angegeben
		if(count($conNrArr) > 0) {
		
			// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
			$query = $this->DB->query("SELECT *
											FROM `" . $this->editContentsTabPrevDB . "` 
											WHERE `page_id` = '$this->editID'
											");
			#var_dump($query);
			
			$count = 0;
			
			foreach($query[0] as $key => $row) {
			
				if(preg_match("/^con[0-9]+_(.*)$/", $key))
					$count++;									 
			}
			
			$contentNumber = $count/count($this->o_lng->installedLangs);
			#var_dump($contentNumber);
			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . $this->editContentsTabPrevDB . "`");

			$conCount = 0;
			
			// Falls mehrere Elemente gelöscht werden sollen
			foreach($conNrArr as $conElem) {
			
				#$conElem -= $conCount;
				
				foreach($this->o_lng->installedLangs as $lang) { // Inhalte nach Sprache auslesen und updaten
					
					$i = $conElem - $conCount;
					$j = $conElem+1;
					
					for($i; $i <= $contentNumber; $i++) {
						
						$keyCon = "con" . $i . "_" . $lang;
						if(!isset($query[0]["con" . $j . "_" . $lang]))
							$valueCon = "";
						else
							$valueCon = $this->DB->escapeString($query[0]["con" . $j . "_" . $lang]);
						
						#var_dump(print_r($query[0]).$keyCon.$valueCon);
						
						// Datenbank-Update: Inhalte hinter zu löschendem Inhalt nach "links" verschieben
						$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
																SET `" . $keyCon . "` = '$valueCon' 
																WHERE `page_id` = '$this->editID'
																");
						
						
						$j++;
						
						#var_dump($queryUpdate1);
								
					}
					
				}
					
					
				$i = $conElem - $conCount;
				$j = $conElem+1;
				
				for($i; $i <= $contentNumber; $i++) { // Inhaltstyp aktualisieren
					
					$keyType = "type-con" . $i;
					if(!isset($query[0]["type-con" . $j]))
						$valueType = "";
					else
						$valueType = $this->DB->escapeString($query[0]["type-con" . $j]);
					
					$keyStyles = "styles-con" . $i;
					if(!isset($query[0]["styles-con" . $j]))
						$valueStyles = "";
					else
						$valueStyles = $this->DB->escapeString($query[0]["styles-con" . $j]);
					
					
					// Datenbanksuche zur Best. der Maximalen Anzahl an Inhaltselementen
					$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
															SET `" . $keyType . "` = '$valueType',
																`" . $keyStyles . "` = '$valueStyles' 
															WHERE `page_id` = '$this->editID'
															");
					
					
					$j++;
				
					#var_dump($queryUpdate1);
				
				}
				
				$conCount++;
			}
			
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
			
			#die(var_dump("<br>".$conNrArr));
			
			// Benachrichtigung, falls nicht im FE
			if(!isset($GLOBALS['_GET']['red']))
				$this->notify = true;
		
		} // Falls zu löschende Elemente

		if(!$this->isFE) {
		
			// Falls im Adminbereich gelöscht wurde
			require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
			require_once SYSTEM_DOC_ROOT . "/inc/admintasks/edit/admin_edit.inc.php"; // Admin-Task einbinden
			
			$isTpl					= strpos($this->editContentsTab, "main") !== false ? false : true;
			$adminTask				= $isTpl ? 'tpl' : 'edit';
			$adminE					= new Admin_Edit($this->DB, $this->o_lng, $adminTask);

			// Theme-Setup
			$adminE->getThemeDefaults("admin");
			$adminE::$tableContents	= $this->editContentsTab;
			$adminE->editId			= $this->editID;
			$adminE->isTemplateArea	= $isTpl;

			if($this->notify)
				$adminE->notice		= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText($notice)), "success");
	
			$ajaxOutput	= $adminE->getTaskContents(true);
			echo parent::replaceStaText($ajaxOutput);
							
			exit;
		}
		else {
			// Falls FE
		
			unset($GLOBALS['_GET']['id']); // ID zurücksetzen falls Daten-Element vorhanden

			require_once(PROJECT_DOC_ROOT . "/inc/classes/Contents/class.ContentsEdit.php"); // Klasse einbinden
			$o_contents				= new ContentsEdit($this->DB, $this->o_lng, true);
			$contents				= $o_contents->getContents();
			echo($o_contents::$o_mainTemplate->getTemplate(true) . '<cctarget>' . $this->editRedirect);
			
			exit;
		}
	}

	
	
	// Kopieren
	private function copyCutElements()
	{
	
		$this->copyCon[]	= array("conTab"	=> $this->editContentsTab,
									"con"		=> $this->con,
									"eid"		=> $this->editID,
									"cut"		=> ($this->action == "cut" ? true : false)
									);
		
		$this->setSessionVar('copycon', $this->copyCon);
		$this->g_Session['copycon']	= $this->copyCon;
		
		// Falls Sortierung
		if(isset($GLOBALS['_GET']['targetcon']) && $GLOBALS['_GET']['targetcon'] != "") {
			
			$targetCon = (int)$GLOBALS['_GET']['targetcon'] - 1;

			if(isset($GLOBALS['_GET']['conmax']) && $GLOBALS['_GET']['conmax'] != "")
				$conmax = $GLOBALS['_GET']['conmax'];
			if(isset($GLOBALS['_GET']['connr']) && $GLOBALS['_GET']['connr'] != "")
				$connr = $GLOBALS['_GET']['connr'];
			if(isset($GLOBALS['_GET']['targetid']) && $GLOBALS['_GET']['targetid'] != "")
				$targetID = $GLOBALS['_GET']['targetid'];
			if(isset($GLOBALS['_GET']['targetarea']) && $GLOBALS['_GET']['targetarea'] != "")
				$targetArea = $GLOBALS['_GET']['targetarea'];
			if(isset($GLOBALS['_GET']['red']) && $GLOBALS['_GET']['red'] != "")
				$this->editRedirect = urldecode($GLOBALS['_GET']['red']);
			if((isset($GLOBALS['_GET']['last']) && $GLOBALS['_GET']['last'] == "true") || $targetCon == -1) {
				$targetCon ++;
			}

			
			// Falls Sortierung im Frontend
			if($this->isFE) {
		
				$this->action		= "paste";
				$this->con			= $targetCon;
				$this->maxConNr		= $conmax;
				$this->currConNr	= $connr;
				$this->editID		= $targetID;
				$this->pasteConNr 	= $this->con+1;
				$this->editContentsTab			= $targetArea;
				$this->editContentsTabPrev		= $this->editContentsTab . "_preview";
				$this->editContentsTabDB		= DB_TABLE_PREFIX . $this->editContentsTab;
				$this->editContentsTabPrevDB	= DB_TABLE_PREFIX . $this->editContentsTabPrev;
				
				$this->insertElements();
			}
			else {
				echo(PROJECT_HTTP_ROOT . "/system/access/editElements.php?page=admin&action=paste&con=$targetCon&conmax=$conmax&connr=$connr&id=$targetID&area=$targetArea&red=" . urlencode($this->editRedirect));
			}
			exit;
			die();		
		}
	
		// Falls copy/cut aus dem Frontend
		if($this->isFE) {
		
			unset($GLOBALS['_GET']['id']); // ID zurücksetzen falls Daten-Element vorhanden
			
			require_once(PROJECT_DOC_ROOT . "/inc/classes/Contents/class.ContentsEdit.php"); // Klasse einbinden
			$o_contents				= new ContentsEdit($this->DB, $this->o_lng, true, "pastecon");
			$contents				= $o_contents->getContents();
			echo($o_contents::$o_mainTemplate->getTemplate(true));
			exit;
		}
		else
			// Falls Backend
			$this->returnEditContents();
	
	}


	// Sortieren
	private function sortElements()
	{
	
		$this->sortParams = array(	"conTab"	=> $this->editContentsTab,
									"con"		=> $this->con,
									"eid"		=> $this->editID,
									"cut"		=> true
							);
	
	}

	
	
	// Element Einfügen
	private function insertElements()
	{

		// Falls die Connr des einzufügenden Elements größer als die vorhandene con-Spaltenanzahl ist, eine neue Inhaltsspalte einfügen
		if($this->currConNr >= $this->maxConNr) {
		
			if(!$this->addContentColumns())
				return false;
		}
		
		$notice	= "{s_notice:pastecon}";

		
		if($this->action == "new") {
			$copyCon			= array();
			$copyCon["conTab"]	= $this->editContentsTab . "_preview";
			$copyCon["con"]		= $this->pasteConNr;
			$copyCon["eid"]		= $this->editID;
			$this->newQS	= "&new=$this->pasteConNr#content-$this->pasteConNr";
			
			// Falls FE-Editing, Redirect anfügen
			if($this->isFE) {
			#if($this->newConType == "text" || $this->newConType == "img" || $this->newConType == "gallery" || $this->newConType == "html")
				$this->editRedirect	= "?pageid=" . $this->editID . "&conid=" . $this->pasteConNr . "&area=" . $this->editContentsTab . "#pageID-" . $this->editID . "-area-" . str_replace("contents_", "", $this->editContentsTab) . "-conID-" . $this->pasteConNr;
			}
		}
		
		if($this->action == "paste") {
			$copyCon			= (!empty($this->copyCon[0]) ? $this->copyCon[0] : $this->g_Session['copycon'][0]);
			$delConTab			= $copyCon["conTab"];
			$copyCon["conTab"]	= $copyCon["conTab"] . "_preview";
		}
		
		if($this->action == "sort") {
			$copyCon			= $this->sortParams;
			$delConTab			= $copyCon["conTab"];
			$copyCon["conTab"]	= $copyCon["conTab"] . "_preview";
		}


		// paste element
		$copyCon	= $this->pasteElement($copyCon);


		
		// Falls ein Inhaltselement ausgeschnitten oder sortiert wurde, das Element nach dem Kopieren löschen
		if(($this->action == "sort" || $this->action == "paste") && $copyCon["cut"] == "true") {
					
			$delConNr	= $copyCon["con"];
			$delConID	= $copyCon["eid"];
		
			
			$conFieldUpd	= "";
			$conTypeUpd		= "";
			$conStylesUpd	= "";
			$conFieldPaste	= "";
			$conTypePaste	= "";
			$conStylesPaste = "";
			
			$conNrElem = parent::getConNumber(DB_TABLE_PREFIX . $delConTab, $delConID); // Anzahl an Inhalts-Elementen (nach einfügen)
			
			$delConTabDB	= DB_TABLE_PREFIX . $delConTab . "_preview";
				
			// Falls die selbe Tabelle, ein Element abziehen (da eingefügt)
			#if($delConTab == $this->editContentsTab) {
				#$delConNr--;
			#}
			
			for($i = $delConNr; $i <= $conNrElem; $i++) {
				
				$j = $i+1;
				
				if($i < $conNrElem) {
					$conTypeUpd .= "`type-con" . $i . "` = `type-con" . $j . "`, ";
					$conStylesUpd .= "`styles-con" . $i . "` = `styles-con" . $j . "`, ";
			
					foreach($this->o_lng->installedLangs as $existLang) {
						$conFieldUpd .= "`con" . $i . "_" . $existLang . "` = `con" . $j . "_" . $existLang . "`, ";
					}
				}
				if($i == $conNrElem) {
										
					$conTypePaste .= "`type-con" . $i . "` = '', ";
					$conStylesPaste .= "`styles-con" . $i . "` = ''";
					foreach($this->o_lng->installedLangs as $existLang) {
						$conFieldPaste .= "`con" . $i . "_" . $existLang . "` = '', ";
					}
				}
				
			} // Ende for
			
			$moveUpd = $conFieldUpd . $conTypeUpd . $conStylesUpd . $conFieldPaste . $conTypePaste . $conStylesPaste;
			
		#echo($conNrElem.' '.$delConNr.' '.$this->pasteConNr);
		#die(var_dump($moveUpd));
		
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . $delConTabDB . "`");
		
		
			// Datenbank-Update in _preview (paste)
			$queryUpdate1 = $this->DB->query( "UPDATE `" . $delConTabDB . "` 
													SET $moveUpd
													WHERE `page_id` = '$delConID'
													");
		
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
		
		} // Ende if sort/paste
		
		
			
		// Session mit zu kopierendem Element löschen, falls paste
		if(isset($this->g_Session['copycon']) && $this->action == "paste")
			$this->unsetSessionKey('copycon');
		
		
		// Falls Backend, Inhalte neu einlesen (ajax)
		if(!$this->isFE && ($this->action == "new" || $this->action == "sort" || $this->action == "paste")) {
		
			// Falls im Adminbereich sortiert wurde
			require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
			require_once SYSTEM_DOC_ROOT . "/inc/admintasks/edit/admin_edit.inc.php"; // Admin-Task einbinden
			
			$isTpl					= strpos($this->editContentsTab, "main") !== false ? false : true;
			$adminTask				= $isTpl ? 'tpl' : 'edit';
			$adminE					= new Admin_Edit($this->DB, $this->o_lng, $adminTask);
			
			// Theme-Setup
			$adminE->getThemeDefaults("admin");
			$adminE::$tableContents	= $this->editContentsTab;
			$adminE->editId			= $this->editID;
			$adminE->isTemplateArea	= $isTpl;
			$adminE->notice			= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText($notice)), "success");
			
			$adminE->elementStartNumber		= (int)floor($this->pasteConNr / Admin_Edit::MAX_CONELEM_NO -0.1) * Admin_Edit::MAX_CONELEM_NO +1;
			$adminE->elementStartNumber		= max(1, $adminE->elementStartNumber);
			
			if($this->action == "new") {
				$adminE->showElements[] = $this->pasteConNr;
			}
			unset($GLOBALS['_GET']['connr']);
	
			$ajaxOutput	= $adminE->getTaskContents(true);
			$ajaxOutput	= parent::replaceStaText($ajaxOutput);
			$ajaxOutput	= parent::replaceStyleDefs($ajaxOutput);
			echo $ajaxOutput;
			
			#return true;
			exit;
			die();
		}
		
		
		// Falls im Frontend sortiert bzw. ein neues Element angelegt wurde
		if($this->isFE && ($this->action == "paste" || $this->action == "new")) {
		
			unset($GLOBALS['_GET']['id']); // ID zurücksetzen falls Daten-Element vorhanden
	
			require_once(PROJECT_DOC_ROOT . "/inc/classes/Contents/class.ContentsEdit.php"); // Klasse einbinden
			
			$o_contents				= new ContentsEdit($this->DB, $this->o_lng, true);
			$contents				= $o_contents->getContents();
			
			echo($o_contents::$o_mainTemplate->getTemplate(true) . '<cctarget>' . $this->editRedirect);
			exit;
			die();
		}
		
		// Benachrichtigung in Session speichern, falls nicht im FE
		if(!isset($GLOBALS['_GET']['red']))
			$this->setSessionVar('notice', $notice);


	} // Ende if new, sort, paste

	
	
	// Paste element
	private function pasteElement($copyCon)
	{
	
		$conFieldUpd	= "";
		$conTypeUpd		= "";
		$conStylesUpd	= "";
		$conFieldPaste	= "";
		$conTypePaste	= "";
		$conStylesPaste = "";
		
		
		// Element einfügen
		for($i = (int)$this->maxConNr; $i >= $this->pasteConNr; $i--) {
			
			$j			= $i-1;
			
			$stylesStr	= $this->DB->escapeString('{"cols":"full","hide":0}');

			
			if($i > $this->pasteConNr) {
				$conTypeUpd .= "`type-con" . $i . "` = `type-con" . $j . "`, ";
				$conStylesUpd .= "`styles-con" . $i . "` = `styles-con" . $j . "`, ";
		
				foreach($this->o_lng->installedLangs as $existLang) {
					$conFieldUpd .= "`con" . $i . "_" . $existLang . "` = `con" . $j . "_" . $existLang . "`, ";
				}
			}
			if($i == $this->pasteConNr) {
				
				// Falls innerhalb eines Inhaltsbereichs
				if($this->editContentsTabPrev == $copyCon["conTab"] && $this->editID == $copyCon["eid"]) {
					
					if($this->pasteConNr < $copyCon["con"]) // Falls der zu kopierende bereits um ein nach hinten verschoben wurde, den Wert zum Auslesen der Inhalte um 1 erhöhen
						$copyCon["con"]++;
					
					$conTypePaste .= "`type-con" . $i . "` = " . ($this->newConType != "" ? "'".$this->newConType."', " : "`type-con" . $copyCon["con"]. "`, ");
					$conStylesPaste .= "`styles-con" . $i . "` = " . ($this->newConType != "" ? "'" . $stylesStr . "'" : "`styles-con" . $copyCon["con"]. "`");
					foreach($this->o_lng->installedLangs as $existLang) {
						$conFieldPaste .= "`con" . $i . "_" . $existLang . "` = " . ($this->newConType != "" ? "'', " : "`con" . $copyCon["con"]. "_" . $existLang . "`, ");
					}
				}
				// Falls unterschiedliche Inhaltsbereiche
				else {
					$conTypePaste .= "`type-con" . $i . "` = (SELECT `type-con" . $copyCon["con"]. "` FROM (SELECT * FROM `" . DB_TABLE_PREFIX . $copyCon["conTab"] . "`) AS t$i WHERE t$i.`page_id` = '" . $copyCon["eid"] . "'), ";
					$conStylesPaste .= "`styles-con" . $i . "` = (SELECT `styles-con" . $copyCon["con"]. "` FROM (SELECT * FROM `" . DB_TABLE_PREFIX . $copyCon["conTab"] . "`) AS s$i WHERE s$i.`page_id` = '" . $copyCon["eid"] . "')";
			
					foreach($this->o_lng->installedLangs as $existLang) {
						$conFieldPaste .= "`con" . $i . "_" . $existLang . "` = (SELECT `con" . $copyCon["con"]. "_" . $existLang . "` FROM (SELECT * FROM `" . DB_TABLE_PREFIX . $copyCon["conTab"] . "`) AS c$i WHERE c$i.`page_id` = '" . $copyCon["eid"] . "'), ";
					}
				}
			}
		} // Ende for
		
		$pasteUpd = $conFieldUpd . $conTypeUpd . $conStylesUpd . $conFieldPaste . $conTypePaste . $conStylesPaste;
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->editContentsTabPrevDB . "`");


		// Datenbank-Update in _preview (paste)
		$queryUpdate1 = $this->DB->query( "UPDATE `" . $this->editContentsTabPrevDB . "` 
												SET $pasteUpd
												WHERE `page_id` = '$this->editID'
												");
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
	
		return $copyCon;
	
	}	

	
	
	// Einfügen neuer Inhaltstabellenspalten
	private function addContentColumns()
	{

		$this->maxConNr++;
		$newConNr	= $this->maxConNr;
		
		$conField	= "";
		$fieldType	= "MEDIUMTEXT NOT NULL";
		
		$conType	= "ADD `type-con" . $newConNr . "` VARCHAR(50) NOT NULL";
		$conStyles	= "ADD `styles-con" . $newConNr . "` TEXT NOT NULL";
		
		foreach($this->o_lng->installedLangs as $existLang) {
			$conField .= "ADD `con" . $newConNr . "_" . $existLang . "` " . $fieldType . ",";
			
		}
		$conField	.= $conType;
		$conField	.= "," . $conStyles;
		
		$sql		= "ALTER TABLE `" . $this->editContentsTabPrevDB . "` " . $conField . "";
		$sqlBase	= "ALTER TABLE `" . $this->editContentsTabDB . "` " . $conField . "";
		

		// db-Tabelle sperren
		$lock		= $this->DB->query("LOCK TABLES `" . $this->editContentsTabDB . "`, `" . $this->editContentsTabPrevDB . "`");
	
		// Der Tabelle contents_xyz_preview Spalte hinzufügen
		$query		= $this->DB->query($sql);

		// Ggf. auch in der contents_xyz Spalte hinzufügen
		$addCol		= $this->DB->addColumn($this->editContentsTabDB, "`type-con" . $newConNr . "`", $sqlBase);
		
		// db-Sperre aufheben
		$unLock		= $this->DB->query("UNLOCK TABLES");

		
		if($addCol === false) {
			$output	= array("alert" => ContentsEngine::replaceStaText("{s_error:maxconcols}"),
							"type"	=> $this->action
							);
			$this->outputJSON($output);
			exit;
			return false;
		}
		if($query === false 
		|| $addCol === "exists"
		) {
			$output	= array("alert" => ContentsEngine::replaceStaText("{s_error:newfail}"),
							"type"	=> $this->action,"res"=>$addCol
							);
			$this->outputJSON($output);
			exit;
			return false;
		}
		
		return true;
	
	}
	
	
	// Einfügen abbrechen
	private function cancelPaste()
	{
	
		// Ggf. copycon aus Session löschen
		$this->unsetSessionKey('copycon');
	
	}



	// getGridClasses	 
	public function getGridClasses()
	{
	
		$output		= array();
		$tagSrc		= array();
		$tagClasses	= array();
		$conType	= "";
		
		// If specific tag/section type
		if(!empty($GLOBALS['_GET']['tagtype'])) {
		
			if(!empty($GLOBALS['_GET']['contype']))
				$conType	= trim($GLOBALS['_GET']['contype']);
			
			$tags		= trim($GLOBALS['_GET']['tagtype']);
			$tagSrc[]	= $tags . 'class';
			$tagSrc[]	= 'marginclass';
			
			// if section
			if($tags == "sec") {
				$tagSrc[]	= 'blendclass';
			}
			
			// if wrapper div
			if($tags == "div") {
				if($conType == "img")
					$tagSrc[]	= 'hovclass';
			}
			
			// if wrapper ele
			if($tags == "ele") {
				if($conType == "menu" || $conType == "listmenu")
					$tagSrc[]	= 'navclass';
				if($conType == "cards")
					$tagSrc[]	= 'panelclass';
			}
		}
		// else all grid classes
		else{			
			$tags		= 'all';
			if(!empty($this->themeConf["grid"]))
				$tagSrc	= array_keys($this->themeConf["grid"]);
		}
		
		if(!empty($tagSrc)
		&& is_array($tagSrc)
		) {
			foreach($tagSrc as $tag) {
				if(!empty($this->themeConf["grid"][$tag]))
					$tagClasses = array_merge($tagClasses, explode(",", $this->themeConf["grid"][$tag]));
			}
		}
		
		$tagClasses = array_unique(array_filter($tagClasses));
		
		$i = 1;
		foreach($tagClasses as $tag) {
			$tag	= trim($tag);
			$cat	= "";
			if(!in_array($tag, $output)) {
				if(strpos($tag, "cs-style-") !== false)
					$cat	= "Section layouts";
				elseif(strpos($tag, "-blend") !== false)
					$cat	= "Blend modes";
				elseif(strpos($tag, "margin-") !== false)
					$cat	= "Margins";
				elseif(strpos($tag, "navbar-") !== false)
					$cat	= "Navbar";
				elseif(strpos($tag, "panel-") !== false)
					$cat	= "Panels";
				elseif(strpos($tag, "ch-effect") !== false)
					$cat	= "Hover effects";
				$output[$i]	= array("label" => $tag, "category" => $cat);
				$i++;
			}
		}

		echo (json_encode($output));
		
		exit;
	
	}
	
	
	// Restore History
	private function restoreHistory()
	{
	
		if(empty($GLOBALS['_GET']['version']))
			return false;
		
		$updateStr		= "";
		$colNo			= 0;
		$tableHistory	= $this->editContentsTabDB . '_history';
		$tablePreview	= $this->editContentsTabDB . '_preview';
		$versionID		= $this->DB->escapeString($GLOBALS['_GET']['version']);
		
		if(!$this->DB->tableExists($tableHistory))
			return false;
		
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $tableHistory . "`, `" . $tablePreview . "`");

		
		$selectSQL	= $this->DB->query("SELECT * FROM `" . $tableHistory . "` WHERE `id` = '" . $versionID . "'");
		
		
		if(!is_array($selectSQL)
		|| empty($selectSQL)
		) {		
			// db-Sperre aufheben
			$unLock		= $this->DB->query("UNLOCK TABLES");
			return false;
		}
		
		
		$i	= 1;
		
		while($i < count($selectSQL[0])) {
			
			if(empty($selectSQL[0]['type-con' . $i]))
				break;
			
			// Inhaltsspalte nach Sprache
			foreach($this->o_lng->installedLangs as $lang) {
				$updateStr .= "`con" . $i . "_" . $lang . "` = '" . $this->DB->escapeString($selectSQL[0]['con' . $i . '_' . $lang]) . "',";
			}
			$updateStr .= "`type-con" . $i . "` = '" . $this->DB->escapeString($selectSQL[0]['type-con' . $i]) . "',";
			$updateStr .= "`styles-con" . $i . "` = '" . $this->DB->escapeString($selectSQL[0]['styles-con' . $i]) . "',";
			
			$i++;
			$colNo++;
		}
		
		
		// Ggf. Tabelle contents_xyz(_preview) um Felder erweitern
		$result		= $this->extendContentTable($tablePreview, $colNo, 1);
		$result		= $this->extendContentTable($this->editContentsTabDB, $colNo, 1);
		
		$columns	= $result["columns"];

		
		// Ggf. überzählige Felder leeren
		while($colNo < $columns) {
			
			$colNo++;
			
			// Inhaltsspalte nach Sprache
			foreach($this->o_lng->installedLangs as $lang) {
				$updateStr .= "`con" . $colNo . "_" . $lang . "` = '',";
			}
			$updateStr .= "`type-con" . $colNo . "` = '',";
			$updateStr .= "`styles-con" . $colNo . "` = '',";

		}
		
		$updateStr	= substr($updateStr, 0, -1);		
		
		
		// In Temp-Tabelle Daten zwischenspeichern
		$historySQL = $this->DB->query("UPDATE `" . $tablePreview . "` 
											SET " . $updateStr ." 
											WHERE `page_id` = '" . $this->editID . "'
											");
		#die(var_dump($historySQL));
		
		// db-Sperre aufheben
		$unLock		= $this->DB->query("UNLOCK TABLES");

		if($historySQL === true)
			$this->setSessionVar("notice", '{s_notice:reshistory}');
		else
			$this->setSessionVar("error", '{s_error:errordb}');
			
		return $historySQL;
	
	}
	
	
	// Clear History
	private function clearHistory()
	{
	
		if(empty($this->editContentsTab))
			return false;
		
		// if clear history of a page
		if(!empty($this->editID))
			return $this->clearPageTplHistory($this->editID);
		
		$tablesHistory	= "";
		
		// if clear complete history
		if($this->editContentsTab == "all") {
			foreach(parent::$contentTables as $conTab) {
				$tablesHistory	.= "`" . $this->DB->escapeString(DB_TABLE_PREFIX . $conTab . '_history') . "`,";
			}
			$tablesHistory	= substr($tablesHistory, 0, -1);
		}
		// else clear history of a content area
		else
			$tablesHistory	= "`" . $this->editContentsTabDB . '_history' . "`";		
		
		$dropSQL	= $this->DB->query("DROP TABLE IF EXISTS " . $tablesHistory);
		
		if($dropSQL === true)
			$this->setSessionVar("notice", '{s_notice:clearhistory}');
		else
			$this->setSessionVar("error", '{s_error:errordb}');
		
		return $dropSQL;
	
	}
	
	
	// Clear page/template History
	private function clearPageTplHistory($editID)
	{
		
		if(empty($editID))
			return false;
	
		if(empty($this->editContentsTabDB))
			return false;
		
		$tableHistory	= $this->editContentsTabDB . '_history';
		#$versionID		= $this->DB->escapeString($GLOBALS['_GET']['version']);
		
		if(!$this->DB->tableExists($tableHistory))
			return false;
		
		
		$deleteSQL	= $this->DB->query( "DELETE FROM `" . $tableHistory . "` 
											WHERE `page_id` = '" . $editID . "'
										");
		
		if($deleteSQL === true)
			$this->setSessionVar("notice", '{s_notice:delhistory}');
		else
			$this->setSessionVar("error", '{s_error:errordb}');
		
		return $deleteSQL;
	
	}

	
	// Inhalte Admin-Edit
	private function returnEditContents()
	{
	
		// Falls im Adminbereich kopiert wurde
		require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
		require_once SYSTEM_DOC_ROOT . "/inc/admintasks/edit/admin_edit.inc.php"; // Admin-Task einbinden
		
		$isTpl					= strpos($this->editContentsTab, "main") !== false ? false : true;
		$adminTask				= $isTpl ? 'tpl' : 'edit';
		$adminE					= new Admin_Edit($this->DB, $this->o_lng, $adminTask);
		
		// Theme-Setup
		$adminE->getThemeDefaults("admin");
		$adminE::$tableContents	= $this->editContentsTab;
		$adminE->editId			= $this->editID;
		$adminE->isTemplateArea	= $isTpl;
		
		// Ggf. copycon setzen
		if(!empty($this->g_Session['copycon'])) {
			$adminE->g_Session['copycon']	= $this->copyCon;
		}
		$adminE->elementStartNumber		= (int)floor($this->con / Admin_Edit::MAX_CONELEM_NO -0.1) * Admin_Edit::MAX_CONELEM_NO +1;
		$adminE->elementStartNumber		= max(1, $adminE->elementStartNumber);
		
		$ajaxOutput	= $adminE->getTaskContents(true);
		echo parent::replaceStaText($ajaxOutput);
		
		exit;
		return true;
	
	}

	
	// checkEditLocking
	protected function checkEditLocking()
	{
	
		$readLock	= $this->LOCK->readLock($this->editID, $this->editContentsTab);
		$pageLock	= $this->LOCK->readLock("all", "editpages");
		$genLock	= $this->GENLOCK->readLock("all", "langs");

		if(($readLock[0] == true && $readLock[1]['lockedBy'] != $this->g_Session['username']) || $pageLock[0] == true || $genLock[0] == true) {
		
			// Falls ListBox zum Anlegen eines neuen Elements angezeigt werden soll oder Publish geklickt wurde, Meldung ausgeben
			if($this->action == "listelements" 
			|| $this->action == "publish"
			) {
				$output	= array("alert" => ContentsEngine::replaceStaText("{s_error:felock}"),
								"type"	=> $this->action
								);
				$this->outputJSON($output);
				exit;
				return true;
			}
			// Falls ein Redirect(ziel) per GET-Parameter mitgegeben wurde (FE-Mode) und kein neues Element angelegt werden soll (außer Text), Seite neu laden
			if(!$this->isFE || ($this->action != "new")) {
				$this->setSessionVar('error', ContentsEngine::replaceStaText("{s_error:felock}")); // Benachrichtigung in Session speichern
				$this->redirectPage();
				return true;
			}
		}
	
		return false;
	
	}

	
	// redirectPage -> Falls ein Redirect(ziel) per GET-Parameter mitgegeben wurde (FE-Mode) und kein neues Element angelegt werden soll (außer Text), Seite neu laden
	private function redirectPage()
	{

		// Falls ein Redirect(ziel) per GET-Parameter mitgegeben wurde (FE-Mode) und kein neues Element angelegt werden soll (außer Text), Seite neu laden
		$redPage	= $this->getRedirectPage();
			
		header("location: " . PROJECT_HTTP_ROOT . "/" . $redPage);
		exit;
		return true;
	
	}

	
	// getRedirectPage
	private function getRedirectPage()
	{

		// Falls ein Redirect(ziel) per GET-Parameter mitgegeben wurde (FE-Mode) und kein neues Element angelegt werden soll (außer Text), Seite neu laden
		// zum Frontend gehen
		if(!empty($GLOBALS['_GET']['red']) && ($this->action != "new" || $this->editRedirect != "")) {
			
			$redPage = urldecode($GLOBALS['_GET']['red']) . $this->editRedirect;
			
			if(is_numeric($redPage))
				$redPage = HTML::getLinkPath($redPage, $this->editLang);
		}
		// bzw. Adminseite neu laden
		else
			$redPage = "admin?task=" . (strpos($this->editContentsTab, "main") !== false ? 'edit' : 'tpl&type=edit') . "&edit_id=$this->editID&area=$this->editContentsTab".$this->newQS;
			
		return $redPage;
	
	}

} // end class EditElements
