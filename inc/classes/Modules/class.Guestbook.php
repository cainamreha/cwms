<?php
namespace Concise;



/**
 * Klasse für Gästebucherstellung
 *
 */

class Guestbook extends Modules
{

	/**
	 * Beinhaltet eine potentielle Meldung.
	 *
	 * @access public
     * @var    string
     */
	public $report = "";
	
	/**
	 * Beinhaltet eine potentielle Fehlermeldung.
	 *
	 * @access public
     * @var    string
     */
	public $errorMes = "";
	
	/**
	 * Beinhaltet eine potentielle Fehlermeldung.
	 *
	 * @access public
     * @var    string
     */
	public $errorName = "";
	
	/**
	 * Beinhaltet eine potentielle Fehlermeldung.
	 *
	 * @access public
     * @var    string
     */
	public $errorMail = "";
	
	/**
	 * Beinhaltet eine potentielle Fehlermeldung.
	 *
	 * @access public
     * @var    string
     */
	public $errorCap = "";
	
	/**
	 * Zur Bestimmung ob das Formular angezeigt werden soll.
	 *
	 * @access public
     * @var    string
     */
	public $showForm = "";
	
	/**
	 * Editieren erlaubt.
	 *
	 * @access private
     * @var    boolean
     */
	private $allowEdit = false;
	
	/**
	 * Beinhaltet den Querystring.
	 *
	 * @access public
     * @var    string
     */
	public $adminQS = "";
	
	/**
	 * Beinhaltet den Pfad zum Gästebuch.
	 *
	 * @access public
     * @var	   boolean
     */
	public $gbPath = "";
	
	/**
	 * Beinhaltet den Status für Emoticons.
	 *
	 * @access public
     * @var	   boolean
     */
	public $emoticons = false;
	
	/**
	 * Beinhaltet die Objektinstanz für Emoticons.
	 *
	 * @access public
     * @var    object
     */
	public $o_emoticons = null;
		
	/**
	 * Array mit möglichen Listenlimits
	 *
	 * @access public
     * @var    int
     */
	public $limitOptions = array(10, 25, 50, 100);
	

	/**
	 * Erstellt ein Gästebuch
	 * 
 	 * @param	object	$DB		DB-Objekt
	 * @param	string Pfad zur Gästebuchseite
	 * @param	string Benutzergruppe
	 * @access	public
	 */
	public function __construct($DB, $o_lng, $group, $gbPath = "")
	{
	
		// Datenbankobjekt
		$this->DB			= $DB;
		
		// Sprache
		$this->o_lng		= $o_lng;
		
		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();
	
		// Falls Admin oder Editor
		$this->backendLog	= $this->o_security->get('backendLog');
		$this->editorLog	= $this->o_security->get('editorLog');
		$this->adminPage	= $this->o_security->get('adminPage');
		
		if($this->editorLog && $this->adminPage)
			$this->adminQS = "task=modules&type=gbook";

		// Leseberechtigung überprüfen
		if(in_array("public", $GLOBALS['gbookReadPermission']) || in_array($group, $GLOBALS['gbookReadPermission']) || $this->editorLog)
			$this->readPermission = true;
			
		// Schreibberechtigung überprüfen
		if(in_array("public", $GLOBALS['gbookWritePermission']) || in_array($group, $GLOBALS['gbookWritePermission']) || $this->editorLog)
			$this->writePermission = true;

		
		// Falls Editor und FE-Mode, TinyMCE einbinden
		if($this->editorLog && parent::$feMode){
			$this->scriptFiles[]	= "extLibs/tinymce/tinymce.min.js";
			#$this->scriptFiles[]	= "extLibs/tinymce/jquery.tinymce.min.js";
			$this->scriptFiles[]	= "system/access/js/myFileBrowser.js";
			$this->scriptFiles[]	= "system/access/js/myTinyMCE.comments.js";
		}

		// Pfad zur Seite
		$this->gbPath = $gbPath;
		
		// Ggf. Kommentare verwenden
		$this->dataTables = $GLOBALS['commentTables'];
		
		// Falls für Gästebuch Kommentare erlaubt, Klasse Comments einbinden
		if(in_array("gbook", $this->dataTables))
			require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Comments.php";
		
		// Emoticons einbinden
		if(in_array("gbook", $GLOBALS['emoticonForms'])) {
			$this->emoticons = true;
			#require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Emoticons.php";
			#$this->o_emoticons = new Emoticons();
		}
		
		// Benutzermeldung
		if(isset($this->g_Session['notice']) && !strpos($this->g_Session['notice'], "delcon") && !strpos($this->g_Session['notice'], "pastecon")) {
			$this->notice = $this->g_Session['notice'];
			$this->unsetSessionKey('notice');
		}
		
		// Benutzerhinweis
		$this->error	= $this->getSessionNotifications("hint");

		
		// Erfolgsmeldung
		if(isset($GLOBALS['_GET']['rep']) && $GLOBALS['_GET']['rep'] == "newentry")
			$this->notice = "{s_notice:gbentry}".(GBOOK_MODERATE == "mail" && GBOOK_NOTIFY_EMAIL && !$this->adminPage ? ' {s_notice:moderate}' : '');
		
		// Falls neuer Eintrag
		elseif(isset($GLOBALS['_POST']['gb_newentry']) && $this->writePermission) {
		
			// Benutzergruppe ermitteln
			if(isset($GLOBALS['_POST']['gb_group']) && in_array($GLOBALS['_POST']['gb_group'], User::getSystemUserGroups()))
				$group = $GLOBALS['_POST']['gb_group'];
			
			// Fomulareingaben überprüfen
			if($this->checkGuestbookForm($group) == true) {
				header("Location: " . PROJECT_HTTP_ROOT . '/' . $this->gbPath . "?rep=newentry&" . $this->adminQS);
				exit;
			}
			
		}
							
	}
	


	/**
	 * Erstellt ein Gästebuch
	 * 
     * @param	string	Benutzergruppe (default = public)
     * @param	int		maximale Anzahl an Einträgen (default = GBOOK_MAX_ROWS)
	 * @access	public
	 * @return	string
	 */
	public function getGuestbook($group = "public", $maxRows = GBOOK_MAX_ROWS)
	{
					
		// Falls keine Leseberechtigung, Meldung statt Gästebuch ausgeben
		if(!$this->readPermission && !$this->writePermission && !$this->adminPage)
			return "no read/write permission";
			
		
		$restrict	= "";
		$pubFilter	= "all";
		$filter		= "";
		$dbOrder	= "ORDER BY gbdate DESC";
		$formAction = ADMIN_HTTP_ROOT . "?task=modules&type=gbook";
		

		if($this->editorLog && $this->adminPage) {
			
			if(isset($GLOBALS['_POST']['gb_group']) && $GLOBALS['_POST']['gb_group'] != "") {
				$groupP = $GLOBALS['_POST']['gb_group'];
				
				if($groupP == "all") {
					$group = "";
					if(isset($this->g_Session['gb_group']))
						$this->unsetSessionKey('gb_group');
				}
				else {
					$this->setSessionVar('gb_group', $groupP);
					$group		= $groupP;
					$groupDB	= $this->DB->escapeString($group);
					$restrict	= "WHERE `group` = '" . $groupDB . "'";
				}
			}
				
			elseif(!empty($GLOBALS['_GET']['gb_group'])) {
				$group		= $GLOBALS['_GET']['gb_group'];
				$groupDB	= $this->DB->escapeString($group);
				$restrict	= "WHERE `group` = '" . $groupDB . "'";
				$this->setSessionVar('gb_group', $group);
			}
			
			elseif(!empty($this->g_Session['gb_group'])) {
				$group		= $this->g_Session['gb_group'];
				$groupDB	= $this->DB->escapeString($group);
				$restrict	= "WHERE `group` = '" . $groupDB . "'";
			}
			
			else {
				$group = "<all>";
				$restrict = "";
			}
			
			
			// Get-Parameter
			if(isset($GLOBALS['_GET']['sort_param']) && $GLOBALS['_GET']['sort_param'] != "")
				$GLOBALS['_POST']['sort_param'] = $GLOBALS['_GET']['sort_param'];
								
			if(isset($GLOBALS['_GET']['pub']) && $GLOBALS['_GET']['pub'] != "")
				$GLOBALS['_POST']['filter_pub'] = $GLOBALS['_GET']['pub'];
			
			
			// Filter für veröffentlichte Kommentare (admin)
			if(isset($GLOBALS['_POST']['filter_pub'])) {
				
				$pubFilter = $GLOBALS['_POST']['filter_pub'];
				
				if($pubFilter != "all")					
					$filter = " `published` = " . ($pubFilter == "pub" ? "1" : "0");
				else
					$filter = " `published` >= 0";
	
			}
			
			if(isset($GLOBALS['_POST']['sort_param']))
				$sortCat = $GLOBALS['_POST']['sort_param'];
			else
				$sortCat = "datedsc";
			
			switch($sortCat) {
				
				case "dateasc":
					$dbOrder = " ORDER BY `gbdate` ASC";
					break;
					
				case "datedsc":
					$dbOrder = " ORDER BY `gbdate` DESC";
					break;
					
				case "nameasc":
					$dbOrder = " ORDER BY `gbname` ASC";
					break;
					
				case "namedsc":
					$dbOrder = " ORDER BY `gbname` DESC";
					break;
			}
			
			// Anzahl angezeigter Einträge pro Seite
			$maxRows = $this->getLimit();
			
			
			if($restrict == "" && $filter != "")
				$restrict = "WHERE" . $filter;
			elseif($restrict != "" && $filter != "")
				$restrict .= " AND" . $filter;
				
				
		}
		else {
			if(!$this->editorLog)
				$restrict = " WHERE `published` = 1";
		}


		// Falls ein neuer gb-Eintrag getätigt werden soll
		if(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] == "newpost" && (!isset($GLOBALS['_COOKIE']['gb_spam_protection']) || $this->editorLog)) { // Falls der Get-Parameter für die Erstellung eines neuen Eintrags gesetzt ist, Formular für neuen Eintrag anzeigen
			// Falls eine Schreibbreichtigung vorliegt
			if($this->writePermission)
				return $this->getGuestbookForm($group); // Gästebuchformular anzeigen
			else
				return "no write permission";
		}
		// Falls ein Kommentar zu einem gb-Eintrag abgegeben werden soll
		elseif(in_array("gbook", $this->dataTables)
		&& isset($GLOBALS['_GET']['newcom'])
		&& $GLOBALS['_GET']['newcom'] != ""
		&& is_numeric($GLOBALS['_GET']['newcom'])
		&& (!isset($GLOBALS['_COOKIE']['gb_spam_protection'])
		 || $this->backendLog)
		) {
		
			// Falls eine Schreibbreichtigung vorliegt
			if($this->writePermission) {
					
				$entryID = $GLOBALS['_GET']['newcom'];
				
				if(isset($GLOBALS['_GET']['mod']) && in_array($GLOBALS['_GET']['mod'], $this->dataTables)) // Falls der Get-Parameter für die Erstellung eines neuen Eintrags gesetzt ist, Formular für neuen Eintrag anzeigen
					$dataTable = $GLOBALS['_GET']['mod'];
					
				$o_comments = new Comments($this->DB, $this->o_lng, "gbook", $entryID, $this->gbPath, $group, $GLOBALS['gbookReadPermission'], $GLOBALS['gbookWritePermission'], true);
				
				$comments	= $o_comments->getCommentsForm("gbook", $entryID); // Kommentarformular anzeigen

				// Ggf. head code Dateien übernehmen
				$this->mergeHeadCodeArrays($o_comments);
				
				return  $comments;
			}
			else
				return "no write permission";
		}
		
		// Falls keine Leseberechtigung, Meldung statt Gästebuch ausgeben
		elseif(!$this->readPermission && !$this->adminPage)
			return "no read permission";
			

		$this->pageNum	= 0;
		$this->maxRows	= $maxRows;
		$gBook			= "";
		$adminGroupSel	= "";
		$adminFilter	= "";
		$adminMarkAll	= "";

		// Pagination
		if (isset($GLOBALS['_GET']['pageNumG']))
			$this->pageNum = $GLOBALS['_GET']['pageNumG'];
		
		$this->startRow = $this->pageNum * $this->maxRows;
		$query_limit = " LIMIT " . $this->startRow . "," . $this->maxRows;
		
  
		// Suche nach Einträgen im Gästebuch
		$queryCount = $this->DB->query("SELECT COUNT(*) 
											FROM `" . DB_TABLE_PREFIX . "gbook` 
											$restrict 
											");
			
		#var_dump($this->maxRows);
		
		// Gruppenauswahl falls Adminbereich
		if($this->editorLog && $this->adminPage) {
			
			// List filter
			// Falls Gruppenfilter oder Abofilter, Filter löschen Button einfügen
			if((!empty($pubFilter) && $pubFilter != "all")
			|| (!empty($group) && $group != "all" && $group != "<all>")
			) {
			
				$filterStr	= "";
				
				if(!empty($group) && strpos($group, "all") === false)
					$filterStr	.= '<strong>{s_label:group} &quot;' . $group . '&quot;</strong>';
			
				
				if(!empty($pubFilter) && $pubFilter != "all") {
					$filterStr  .= ($filterStr != "" ? ' | ' : '');
					$filterStr	.= '<strong>{s_label:' . ($pubFilter == "pub" ? '' : 'un') . 'published}</strong>';
				}
			

				$adminFilter	=	'<span class="showHiddenListEntries actionBox cc-hint">' . "\r\n";
			
				// Filter icon
				$adminFilter .=	'<span class="listIcon">' . "\r\n" .
								parent::getIcon("filter", "inline-icon") .
								'</span>' . "\n";

				$adminFilter .=	'{s_label:filter}: ' . $filterStr;
				
				$adminFilter .=	'<form action="'.$formAction.'" method="post">' . "\r\n";
				
				$adminFilter .=	'<span class="editButtons-panel">' . "\r\n";

				// Button remove filter
				$btnDefs	= array(	"type"		=> "submit",
										"class"		=> 'removefilter ajaxSubmit button-icon-only',
										"title"		=> '{s_title:removefilter}',
										"icon"		=> "close"
									);
					
				$adminFilter .=	parent::getButton($btnDefs);
							
				$adminFilter .=	'<input type="hidden" name="gb_group" value="all">' . "\r\n" .
								'<input type="hidden" name="filter_sent" value="all">' . "\r\n" .
								'</span>' . "\r\n" .
								'</form>' . "\r\n" .
								'</span>' . "\r\n";
			}

			
			$redPath = "";
			$this->adminQS = "task=modules&type=gbook&sort_param=$sortCat&pub=$pubFilter&limit=$this->maxRows";
			
			$adminGroupSel =		'<h3 class="cc-h3 toggle">{s_header:gbentries}</h3>' . "\r\n" . 
									'<div class="adminBox">' . "\r\n" . 
									'<div class="controlBar">' . "\r\n" . 
									'<form action="' . $formAction . '" method="post">' . "\r\n" . 
									'<div class="left"><label>{s_label:usergroup}</label>' . "\r\n" .
									'<select name="gb_group" class="listCat" data-action="autosubmit">' . "\r\n" . 
									'<option value="all"' . (isset($group) && $group == "<all>" ? ' selected="selected"' : '') . '>{s_option:allgroup}</option>' . "\r\n";
			
			foreach(User::getSystemUserGroups() as $userGroup) {
				
				$adminGroupSel .=	'<option value="' . $userGroup . '"' . (isset($group) && $group == $userGroup ? ' selected="selected"' : '') . '>' . $userGroup . '</option>' . "\r\n";						
			}
			
			$adminGroupSel .= 		'</select></div>' . "\r\n" .
									'<div class="sortOption small left"><label>{s_label:sort}</label>' . "\r\n" .
									'<select name="sort_param" class="listSort" data-action="autosubmit">' . "\r\n";
			
			$sortOptions = array("dateasc" => "{s_option:dateasc}",
								 "datedsc" => "{s_option:datedsc}",
								 "nameasc" => "{s_option:nameasc}",
								 "namedsc" => "{s_option:namedsc}"
								 );
			
			foreach($sortOptions as $key => $value) { // Sortierungsoptionen
				
				$adminGroupSel .='<option value="' . $key . '"';
				
				if(isset($GLOBALS['_POST']['sort_param']) && $key == $sortCat)
					$adminGroupSel .=' selected="selected"';
					
				$adminGroupSel .= '>' . $value . '</option>' . "\r\n";
			
			}
								
			$adminGroupSel .= 		'</select></div>' . "\r\n" .
									'<div class="sortOption small left"><label>{s_label:limit}</label>' . "\r\n";
			
			$adminGroupSel .= 		$this->getLimitSelect($this->limitOptions, $maxRows);
				
			$adminGroupSel .= 		'</div>' . "\r\n";
			
			$adminGroupSel .= 		'<div class="filterOption small left"><label for="all">{s_label:all}</label>' . "\r\n" .
									'<label class="radioBox markBox">' . "\r\n" .
									'<input type="radio" name="filter_pub" value="all" id="all"' . ($pubFilter == "all" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . "\r\n" .
									'</label>' . "\r\n" .
									'</div>' . "\r\n" .
									'<div class="filterOption small left"><label for="pub">{s_label:published}</label>' . "\r\n" .
									'<label class="radioBox markBox">' . "\r\n" .
									'<input type="radio" name="filter_pub" value="pub" id="pub"' . ($pubFilter == "pub" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . "\r\n" .
									'</label>' . "\r\n" .
									'</div>' . "\r\n" .
									'<div class="filterOption small left"><label for="unpub">{s_label:unpublished}</label>' . "\r\n" .
									'<label class="radioBox markBox">' . "\r\n" .
									'<input type="radio" name="filter_pub" value="unpub" id="unpub"' . ($pubFilter == "unpub" ? ' checked="checked"' : '') . ' data-action="filterlist" />' . "\r\n" .
									'</label>' . "\r\n" .
									'</div>' . "\r\n";

			$adminGroupSel .=		'</form><br class="clearfloat" /></div>' . "\r\n";
			
			
			// Checkbox zur Mehrfachauswahl im Adminbereich
			$adminMarkAll =			'<ul class="commentList">' . "\r\n" .
									'<form action="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=gbook&entryid=array&action=" method="post">' . "\r\n" .
									'<div class="actionBox"><label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" /></label><label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' . "\r\n" .
									'<span class="editButtons-panel">' . "\r\n";
			
			// Button publish
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'pubAll pubGBook button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:pubmarked}',
									"icon"		=> "publish"
								);
				
			$adminMarkAll .=	parent::getButton($btnDefs);
			
			// Button unpublish
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'pubAll pubGBook unpublish button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:unpubmarked}',
									"icon"		=> "unpublish"
								);
				
			$adminMarkAll .=	parent::getButton($btnDefs);
									
			// Button delete
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'delAll delGBook button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:delmarked}',
									"attr"		=> 'data-action="delmultiple"',
									"icon"		=> "delete"
								);
				
			$adminMarkAll .=	parent::getButton($btnDefs);
									
			$adminMarkAll .=	'</span>' . "\r\n" .
								'</div>' . "\r\n";
							
		}
		
		else
			$redPath = htmlspecialchars($GLOBALS['_GET']['page']) . PAGE_EXT;
			


		if(isset($this->notice) && $this->notice != "" && !$this->adminPage)
			$gBook .=	'<p class="notice success {t_class:alert} {t_class:success}">' . $this->notice . '</p>' . "\r\n";
		
		elseif(isset($this->hint) && $this->hint != "")
			$gBook .=	'<p class="notice error {t_class:alert} {t_class:error}">' . $this->hint . '</p>' . "\r\n";
		
		if($this->backendLog || !isset($GLOBALS['_COOKIE']['gb_spam_protection'])) {
		
			// Button link
			$btnDefs	= array(	"href"		=> '?action=newpost' . ($this->adminQS != "" ? '&amp;' . $this->adminQS : '') . '#gbfm',
									"class"		=> 'newpost {t_class:btnpri} {t_class:marginbm}',
									"text"		=> "{s_link:gbentry}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "pencil"
								);
				
			$gBook .=	parent::getButtonLink($btnDefs);
		}
		
		elseif($this->adminPage) {
		
			$gBook .=	'<ul><li>' . "\r\n";
			
			// Button link
			$btnDefs	= array(	"href"		=> '?action=newpost' . ($this->adminQS != "" ? '&amp;' . $this->adminQS : ''),
									"class"		=> 'newpost {t_class:btnpri}',
									"text"		=> "{s_link:gbentry}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "pencil"
								);
				
			$gBook .=	parent::getButtonLink($btnDefs);
			
			$gBook .=	'</li></ul>' . "\r\n";
		}
		
		$gBook .=		$adminGroupSel;	// Im Adminbereich Gruppenauswahl einbinden
		
		
		
		if($queryCount[0]['COUNT(*)'] > 0) { // Falls die Suche erfolgreich war Gästebucheinträge anzeigen
		
			$gBook	   .=	$adminFilter . $adminMarkAll; // Alles Markieren einfügen, falls Adminbereich

			$this->totalRows = $queryCount[0]["COUNT(*)"];
			
			
			// Suche nach Einträgen im Gästebuch
			$query = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . "gbook` 
											$restrict 
											$dbOrder
											$query_limit
											");
				
			#var_dump($query);
		

			$i = 0;
			$alternate = ' class="alternate"';
			
				
			foreach($query as $gBookEntry) {
				
				$gBook	   .=	'<div class="gb_entry listEntry {t_class:panel}' . ($i % 2 ? " alternate" : "") . '" data-menu="context" data-target="contextmenu-gb-' . $i . '">' . "\r\n";
				
				$entryID	= $gBookEntry["id"];
				$gbName		= htmlspecialchars($gBookEntry["gbname"]);
				$gbDate		= htmlspecialchars(date("d.m.Y", strtotime($gBookEntry["gbdate"])));
				$gbTime		= htmlspecialchars(date("H:i", strtotime($gBookEntry["gbdate"])));
				$comment	= $gBookEntry["gbcomment"];
				$email		= htmlspecialchars($gBookEntry["gbmail"]);
				$gravatar	= htmlspecialchars($gBookEntry["gravatar"]);
				
				// Pfad zum Avatarplatzhalter
				if($this->adminPage)
					$imgSrc		= SYSTEM_IMAGE_DIR . '/empty_avatar.png';
				else
					$imgSrc		= PROJECT_HTTP_ROOT . '/' . IMAGE_DIR . 'empty_avatar.png';
				
				if($gravatar == 1 && $email != "")
					$imgSrc	= 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . "?d=" . urlencode($imgSrc); // Gravatar Hash generieren
				
				
				#if($this->emoticons === true)
				#	$comment = $this->o_emoticons->getEmoticons($comment); // Emoticons einbinden
				
				$comment = nl2br($comment);
				$comment = str_replace(array("\r","\n"), "", $comment);
				#$comment = preg_replace('~\S{100}~', ' ... ', $comment); // "Begriffe" mit mehr als 100 zusammenhängenden Zeichen, die kein Leerzeichen enthalten, löschen

				$editButtons	= "";
				$markBox		= "";
				
				if($this->editorLog) {
					
					// Editieren erlaubt
					if($this->adminPage || parent::$feMode)
						$this->allowEdit	= true;
					
					
					// Checkboxen zur Mehrfachauswahl im Adminbereich
					if($this->adminPage)
						$markBox .=		'<label class="markBox">' . 
										'<input type="checkbox" name="entryNr[' . $i . ']" class="addVal" />' .
										'<input type="hidden" name="entryID[' . $i . ']" value="' . $entryID . '" class="getVal" />' .
										'</label>' . "\r\n";
					
					// Entry-ID
					$editButtons .=	'<input type="hidden" name="gbookEntryEditUrl[' . $i . ']" value="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=editcomment&mod=gbook&id=' . $entryID . '" class="gbookEntryEditUrl commentEditUrl" />' . "\r\n";

					// Icons zum Veröffentlichen/Löschen
					$editButtons .=	'<span class="gbookIcons commentEditButtons editButtons-panel" data-id="contextmenu-gb-' . $i . '">' . "\r\n";
					
					$editButtons .=	'<span class="switchIcons">' . "\r\n";
			
					// Button publish
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pubComment publish-state-hidden button-icon-only',
											"value"		=> "",
											"title"		=> '{s_title:publishcomment}',
											"attr"		=> 'data-action="pubdata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=gbook&action=pubentry&set=1&id=' . $entryID . '" data-menuitem="true" data-id="item-id-' . $i . '" data-publish="1"' . ($gBookEntry['published'] == 1 ? ' style="display:none;"' : ''),
											"icon"		=> "unpublish"
										);
						
					$editButtons .=	parent::getButton($btnDefs);
			
					// Button unpublish
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pubComment publish-state-visible button-icon-only',
											"value"		=> "",
											"title"		=> '{s_title:nopublishcomment}',
											"attr"		=> 'data-action="pubdata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=gbook&action=pubentry&set=0&id=' . $entryID . '" data-menuitem="true" data-id="item-id-' . $i . '" data-publish="0"' . ($gBookEntry['published'] == 0 ? ' style="display:none;"' : ''),
											"icon"		=> "publish"
										);
						
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .=	'</span>' . "\r\n";	
			
					// Button delete
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'delComment button-icon-only',
											"value"		=> "",
											"title"		=> '{s_title:delcon}',
											"attr"		=> 'data-action="delete" data-url="' . ADMIN_HTTP_ROOT . '?task=modules&amp;type=gbook&amp;action=del&amp;id=' . $entryID . '&amp;red=' . $redPath . '&amp;totalRows=' . (isset($GLOBALS['_GET']['totalRowsG']) ? $GLOBALS['_GET']['totalRowsG'] : '') . (isset($GLOBALS['_GET']['pageNumG']) ? '&amp;pageNum=' . $GLOBALS['_GET']['pageNumG'] : '') . '" data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "delete"
										);
						
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .=	'</span>' . "\r\n";	
				}
				
				// Head
				$gBook .=	'<div class="listEntryHeader {t_class:panelhead}">' .
							$markBox .
							'<span class="dataAuthor {t_class:paneltitle}"> ' .$gbName . '</span>' .
							($this->editorLog ? ' (<i style="font-weight:normal">' . $gBookEntry["group"] . '</i>) ' : '') .
							'<span class="dataDate">' . PHP_EOL .
							'<span> {s_text:commented} </span>' . $gbDate . '<span> {s_text:attime} </span>' . $gbTime . '<span> {s_text:time}</span>' . PHP_EOL .
							'</span>' . "\r\n";
				
				// No
				$gBook .=	'<span class="entryNo {t_class:badge} {t_class:right}">#' . ($this->totalRows - $i - ($this->pageNum * $this->maxRows)) . '</span>' . "\r\n" .
							$editButtons .
							'</div>' . "\r\n";
							
				// Body
				$gBook .=	'<div class="{t_class:panelbody}">' . "\n" .
							'<div class="{t_class:media}">' . "\n" .
							'<div class="avatar {t_class:medialft}">' . "\n" .
							'<img src="' . $imgSrc . '" alt="avatar" class="avatar {t_class:mediaobj}" />' . "\n" .
							'</div>' . "\r\n" .
							'<div class="gbComment comment {t_class:mediabody}' . ($this->allowEdit ? ' editableComment" title="{s_title:edittext}"' : '"') . '>' . $comment . '</div>' . "\r\n" .
							'</div>' . "\r\n" .
							'</div>' . "\r\n";

				// Ggf. Kommentare einbinden
				if(in_array("gbook", $this->dataTables)) {
					
					if(isset($this->g_Session['group']))
						$comGroup = $this->g_Session['group'];
					else
						$comGroup = "public";
						
					$o_comments = new Comments($this->DB, $this->o_lng, "gbook", "", $redPath, $comGroup, $GLOBALS['gbookReadPermission'], $GLOBALS['gbookWritePermission']);
					
					$o_comments->showForm = 'no';
					
					// Foot
					$gBook .=	'<div class="commentBox {t_class:panelfoot}">' . "\r\n";
					$gBook .=	'<div class="{t_class:row}">' . "\r\n";
					$gBook .=	$o_comments->getComments("gbook", $entryID, COMMENTS_MAX_ROWS);
					$gBook .=	!$this->adminPage ? '<br class="clearfloat" />' : '';
					$gBook .=	'</div>' . "\r\n";
					$gBook .=	'</div>' . "\r\n";

					// Ggf. head code Dateien übernehmen
					$this->mergeHeadCodeArrays($o_comments);

				}
					
				$gBook .=		'</div>' . "\r\n";
				
				$i++;
				
			} // Ende foreach
			
			
			// Formtag schließen (Checkboxen im Adminbereich)
			if($this->editorLog && $this->adminPage)
				$gBook .=		'</form>' . "\r\n";
				
			
			$gBook .= Modules::getPageNav($this->maxRows, $this->totalRows, $this->startRow, $this->pageNum, $this->adminQS, "G", false, Modules::getLimitForm($this->limitOptions, $this->maxRows));
							
			
		}
		else {
  
			$this->hint .= "{s_notice:nogbook}";

			$gBook .=	$adminFilter;
			
			$gBook .=	'<p class="notice error empty {t_class:alert} {t_class:warning}">' . $this->hint . '</p>' . "\r\n";
			
		}

		
		if($this->editorLog && $this->adminPage)
			$gBook .=	'</div>' . "\r\n";
		
		
		// Contextmenü-Script
		$gBook		 .=	$this->getContextMenuScript();

			
		$gBook = ContentsEngine::replaceStaText($gBook);
		
		return $gBook;


	}
	
	

	/**
	 * Erstellt ein Formular für einen neuen Gästebucheintrag
	 * 
     * @param	string	Benutzergruppe (default = public)
	 * @access	public
	 * @return	string
	 */
	public function getGuestbookForm($group = "public")
	{
			
		// Berechtigung 
		if(!in_array($group, $GLOBALS['gbookWritePermission']) && !$this->editorLog)
			return false;
		
		$queryStr		= "";
		$formAction		= "";
		$noticeOpenTag	= '<span class="notice {t_class:texterror}">';
		$noticeCloseTag	= '</span>';


		if($this->editorLog && $this->adminPage) {
			$queryStr = "?task=modules&type=gbook";
			$formAction = ADMIN_HTTP_ROOT . '?task=modules&type=gbook&action=newpost';
		}
		
		// Textarea editor
		if(!parent::$feMode){
			$this->scriptFiles[]	= "extLibs/tinymce/tinymce.min.js";
			$this->scriptFiles[]	= "system/access/js/myTinyMCE.comments-simple.js";
		}
		
		// Button link
		$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . $GLOBALS['_GET']['page'] . PAGE_EXT . $queryStr,
								"class"		=> 'backtoList {t_class:btndef} {t_class:marginbm}',
								"text"		=> "{s_link:backtogbook}",
								"icon"		=> "book"
							);
			
		$form =	parent::getButtonLink($btnDefs);
		
		$form .=	'<div id="gbfm" class="form cc-gbook cc-module">' . "\r\n" .
					'<div class="top"></div>' . "\r\n" .
					'<div class="center">' . "\r\n" .
					'<form id="gb_newentry" class="gb_newentry {t_class:form}" action="' . $formAction  . '#gbfm" method="post">' . "\r\n" . 
					'<fieldset>' . "\r\n" . 
					'<legend>{s_form:guestbooktit}</legend>' . "\r\n";
		
		// Error box
		$form .=	'<div class="formErrorBox">' . "\r\n";
		
		if($this->report != "")
			$form .=	'<p class="notice {t_class:alert} {t_class:success}">' . $this->report . '</p>' . "\r\n";
		
		elseif($this->error != "")
			$form .=	'<p class="notice error {t_class:alert} {t_class:error}">' . $this->error . '</p>' . "\r\n";
		
		if($this->showForm == 'no') { // Falls nur Meldungen ausgegeben werden sollen (z.B. erfolgreicher Versand) Formular nicht anzeigen
		
			$form .=	'</div>' .
						'</fieldset>' .
						'</form>' .
						'</div>' . "\r\n" .
						'<div class="bottom"></div>' . "\r\n" .
						'</div>' . "\r\n";

			return ContentsEngine::replaceStaText($form);
		}
		else { // Andernfalls Formular anzeigen
		
			$form .=	'</div>' . "\r\n"; // close errorBox
			$form .=	'<p class="footnote topNote {t_class:alert} {t_class:info}">{s_form:req}</p>' . "\r\n";
			$form .=	'<ul class="commentList' . ($this->adminPage ? ' framedItems' : '') . '">' . "\r\n";
						
			if($this->editorLog && $this->adminPage) {
			
				$form .=	'<li class="{t_class:formrow}">' . "\r\n" .
							'<label class="{t_class:label}" for="group">{s_label:userG}<em>&#42;</em></label>' . "\r\n" . 
							'<select class="{t_class:select} {t_class:field}" name="gb_group">' . "\r\n";
				
				foreach(User::getSystemUserGroups() as $userGroup) {
					
					$form .=	'<option value="' . $userGroup . '"' . (isset($GLOBALS['_POST']['gb_group']) && $GLOBALS['_POST']['gb_group'] == $userGroup ? ' selected="selected"' : ($userGroup == "public" ? ' selected="selected"' :'')) . '>' . $userGroup . '</option>' . "\r\n";						
				}
				
				$form .=	'</select>' . "\r\n" .
							'<p class="clearfloat">&nbsp;</p></li>' . "\r\n";
			}


			$form .=	'<li class="{t_class:formrow}' . ($this->errorName != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<label class="{t_class:label}" for="name">{s_form:name}<em>&#42;</em></label>' . "\r\n";
						
			if($this->errorName != "")
				$form .= $noticeOpenTag . $this->errorName . $noticeCloseTag . "\r\n";

			$form .=	'<input name="name" type="text" id="name" class="{t_class:input} {t_class:field}" aria-required="true" value="';
						
			isset($GLOBALS['_POST']['name']) ? $form .= htmlspecialchars($GLOBALS['_POST']['name']) : '""';
						
			$form .=	'" maxlength="50" data-validation="required" data-validation-length="max50" />' . "\r\n" . 
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}' . ($this->errorMail != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<label class="{t_class:label}" for="email">E-Mail</label>' . "\r\n";
						
			if($this->errorMail != "")
				$form .= $noticeOpenTag . $this->errorMail . $noticeCloseTag . "\r\n";
						
			$form .=	'<input name="email" type="text" id="email" class="{t_class:email} {t_class:field}" value="';
						
			isset($GLOBALS['_POST']['email']) ? $form .= htmlspecialchars($GLOBALS['_POST']['email']) : '""';
			
			$form .=	'" maxlength="128" data-validation-if-checked="gravatar" data-validation="email" data-validation-length="max128" />' . "\r\n" . 
						'<input type="text" name="m-mail" id="m-mail" class="emptyfield" value="" />' . "\r\n" . 
						'</li>' . "\r\n" . 
						'<li class="{t_class:rowcheckbox}">' . "\r\n" .
						'<label class="{t_class:label}" for="gravatar">' . "\r\n" .
						'<input type="checkbox" name="gravatar" id="gravatar" class="{t_class:checkbox}"' . (isset($GLOBALS['_POST']['gravatar']) ? ' checked="checked"' : '') . ' />' . "\r\n" . 
						'{s_form:gravatar}</label>' . "\r\n" .
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}' . ($this->errorMes != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<label class="{t_class:label}" for="message">{s_form:message}<em>&#42;</em></label>' . "\r\n";
						
			if($this->errorMes != "")
				$form .= $noticeOpenTag . $this->errorMes . $noticeCloseTag . "\r\n";
						
			$form .=	'<textarea name="message" id="message" class="{t_class:text} {t_class:field}" aria-required="true" data-validation="required" data-validation-length="max1800" rows="3" cols="30" accept-charset="UTF-8">';
						
			isset($GLOBALS['_POST']['message']) ?  $form .= htmlentities($GLOBALS['_POST']['message'], ENT_QUOTES, 'UTF-8') : '""';
			
			$form .=	'</textarea>' . "\r\n";
						
			
			// Emoticons einfügen			
			if($this->emoticons === true) {
				#$form .=	$this->o_emoticons->listEmoticons();
				$form .=	'<input type="hidden" id="emoticonForms" value="true" />' . "\r\n";
			}
			$form .=	'<input type="hidden" id="commentEditorSkin" value="decent" />' . "\r\n";
			$form .=	'</li>' . "\r\n";
						
			
			// Falls kein Admin/Editor, Captcha anzeigen
			if(!$this->editorLog) {
				
				$form .='<li class="{t_class:formrow}' . ($this->errorCap != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<ul class="{t_class:row}">' . "\r\n" .
						'<li class="fieldLeft {t_class:halfrowsm} {t_class:alpha}">' . "\r\n" .
						'<label class="{t_class:label}" for="captcha_confirm">{s_form:captcha}<em>&#42;</em></label>' . "\r\n";
							
				if($this->errorCap != "")
					$form .= $noticeOpenTag . $this->errorCap . $noticeCloseTag . "\r\n";
							
				$form .='<input name="captcha_confirm" type="text" id="captcha_confirm" class="{t_class:input} {t_class:field}" aria-required="true" data-validation="required" />' . "\r\n" . 
						'</li>' . "\r\n" . 
						'<li class="fieldRight {t_class:halfrowsm} {t_class:omega}">' . "\r\n" .
						'<span class="captchaBox">' . "\r\n" .
						'<label>&nbsp;</label><br />' . "\r\n" .
						'<img src="' . PROJECT_HTTP_ROOT . '/access/captcha.php" alt="{s_form:capalt}" title="{s_form:captit}" class="captcha" />' . "\r\n";
		
				// Button caprel
				$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/access/captcha.php',
										"text"		=> '',
										"class"		=> "caprel button-icon-only {t_class:btninf} {t_class:btnsm}",
										"title"		=> '{s_form:capreltit}',
										"attr"		=> 'tabindex="2"',
										"icon"		=> "refresh",
										"icontext"	=> ""
									);
				
				$form .=	parent::getButtonLink($btnDefs);
				
				$form .='</span>' . "\r\n" . 
						'</li>' . "\r\n" . 
						'</ul>' . "\r\n" . 
						'</li>' . "\r\n";
			}
			
			if($this->adminPage)
				$form .='<li><p class="footnote {t_class:alert} {t_class:info}">{s_form:req}</p></li>' . "\r\n";
				
			$form .=	(GBOOK_MODERATE && !$this->adminPage ? '<p class="footnote {t_class:alert} {t_class:info}">{s_text:moderate}</p>' . "\r\n" : '') .
						'<li class="{t_class:formrow} submitPanel' . ($this->adminPage ? ' change submit' : '') . '">' . "\r\n";
			
			// Button submit
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "gb_newentry",
									"id"		=> "submit",
									"class"		=> '{t_class:btnpri} formbutton ok',
									"value"		=> "{s_button:submit}",
									"text"		=> "{s_button:submit}",
									"title"		=> '{s_title:delcom}',
									"icon"		=> "ok"
								);
				
			$form .=	parent::getButton($btnDefs);
			
			$form .=	'<input name="gb_newentry" type="hidden" value="{s_button:submit}" />' . "\r\n" .
						#'<input name="reset" type="button" id="reset" onClick="fieldRes();" value="{s_button:reset}" class="formbutton button reset right" />' . "\r\n" .
						'<input name="postdate" type="hidden" id="hidden_date" value="' . date("Y-m-d H:i:s") . '" />' . "\r\n" .
						parent::getTokenInput() . 
						'</li>' . "\r\n" . 
						'</ul>' . "\r\n" . 
						'</fieldset>' . "\r\n" . 
						'</form>' . "\r\n" .
						'</div>' . "\r\n" .
						'<div class="bottom"></div>' . "\r\n" .
						'</div>' . "\r\n";
						
						
			return ContentsEngine::replaceStaText($form);
			
		} // Ende else: Formular anzeigen

	}
	
	

	/**
	 * Überprüft die Eingaben des neuen Gästebucheintrags
	 * 
     * @param	string	Benutzergruppe
	 * @access	public
	 * @return	boolean
	 */
	public function checkGuestbookForm($group)
	{
	
		// Formular auswerten
		$checkForm	= true;
		
		$messlg = strlen($GLOBALS['_POST']['message']); // Nachrichtenlänge auslesen
		
		// ...wenn ein Fehler aufgetreten ist und keine Nachricht versendet wurde, Meldung ausgeben
		// Falls der Testcookie beim Aufruf der Seite nicht gesetzt werden konnte, weil Cookies nicht aktiviert sind...
		if(empty($this->g_Session['captcha']) && (!isset($GLOBALS['_COOKIE']['cookies_on']) || $GLOBALS['_COOKIE']['cookies_on'] != "cookies_on")) {
			// ...zusätzliche Meldung ausgeben
			$this->error = '{s_error:sessmes}';
			$testCookie = "alert";
			$checkForm	= false;
		}			

		// ...andernfalls, falls der Cookie gegen Spam gesetzt wurde, Nachricht ausgeben
		if ((isset($GLOBALS['_COOKIE']['gb_spam_protection']) && $GLOBALS['_COOKIE']['gb_spam_protection'] == "gb_spam_protection") || 
			!empty($GLOBALS['_POST']['m-mail'])
		) {
			$this->error .= '{s_error:spam}';
			$this->showForm = 'no';
			$checkForm	= false;
		}
			
		// Falls keins der Felder ausgefüllt ist...
		if (empty($GLOBALS['_POST']['name']) && empty($GLOBALS['_POST']['email']) && empty($GLOBALS['_POST']['message']) && empty($GLOBALS['_POST']['captcha_confirm'])) {
			$this->error .= '{s_error:fillreq}';
			$checkForm	= false;
		}

		// Falls Name leer ist...
		if (empty($GLOBALS['_POST']['name'])) {
			// ...Meldung ausgeben
			$this->errorName = '{s_error:name}';
			$checkForm	= false;
		}

		// ...Falls keine E-Mail Adresse eingegeben wurde...
		if (isset($GLOBALS['_POST']['gravatar']) && empty($GLOBALS['_POST']['email'])) {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorMail = '{s_error:mail1}';
			$checkForm	= false;
		}
		
		// ...Falls eine E-Mail Adresse eingegeben wurde, aber das Format falsch ist...
		if (isset($GLOBALS['_POST']['email']) && $GLOBALS['_POST']['email'] != "" && !filter_var($GLOBALS['_POST']['email'], FILTER_VALIDATE_EMAIL)) {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorMail = '{s_error:mail2}';
			$checkForm	= false;
		}
		
		// ...Falls keine Nachricht eigetragen wurde...
		if ($GLOBALS['_POST']['message'] == "") {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorMes = '{s_error:nomes}';
			$checkForm	= false;
		}
		
		// Falls Nachricht zu lang (>1800 Zeichen) ist...
		if ($messlg > 1800) {
			// ...Meldung ausgeben
			// mit Angabe der aktuellen Zeichenanzahl
			$messlgstr = parent::$staText['error']['messlg'];
			$this->errorMes = str_replace('%zuviel%', $messlg, $messlgstr);
			$checkForm	= false;
		}
		
		// Falls der Captcha nicht stimmt...
		if(	(empty($GLOBALS['_POST']['captcha_confirm']) || 
			(trim($GLOBALS['_POST']['captcha_confirm']) == "") || 
			 strlen($GLOBALS['_POST']['captcha_confirm']) != 5 || 
			(!empty($this->g_Session['captcha']) && 
			$GLOBALS['_POST']['captcha_confirm'] != $this->g_Session['captcha'])) && 
			$this->editorLog == false
		) {
			$this->errorCap = '{s_error:captcha}';
			$checkForm	= false;
		}
		
		if($checkForm === true)
			return $this->safeGuestbookEntry($group);
			
		else {
			
			if($this->error == "")
				$this->error = '{s_error:check' . (!$this->adminPage ? 'form' : '') . '}';
			
			return false;
		}
		
	}
	
	

	/**
	 * Speichert den neuen Gästebucheintrag
	 * 
     * @param	string	Benutzergruppe
	 * @access	public
	 * @return	boolean
	 */
	public function safeGuestbookEntry($group)
	{
				
		// Eintrag speichern
		
		// Felder auslesen
		$name       = $this->DB->escapeString(trim($GLOBALS['_POST']['name']));
		$userGroup  = $this->DB->escapeString($group);
		$nachricht  = $this->DB->escapeString(trim($GLOBALS['_POST']['message']));
		$datum		= $this->DB->escapeString($GLOBALS['_POST']['postdate']);
		$email      = preg_replace( "/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)]/im", "", $GLOBALS['_POST']['email'] );
		$email      = $this->DB->escapeString($email);
		$noticeExt	= "";

		$this->domain	= str_replace(array("http://","https://","www."), "", PROJECT_HTTP_ROOT);

		if(isset($GLOBALS['_POST']['gravatar']))
			$gravatar = 1;
		else
			$gravatar = 0;
		
		if(GBOOK_MODERATE)
			$publish = 0;
		else
			$publish = 1;


		// db-Update der Pages Tabelle
		$updateSQL1 = $this->DB->query("INSERT INTO `" . DB_TABLE_PREFIX . "gbook` 
												   (gbname,
													`group`,
													gbdate, 
													gbmail,
													gravatar,
													gbcomment,
													published
													) 
											VALUES ('$name',
													'$userGroup',
													'$datum', 
													'$email',
													$gravatar,
													'$nachricht',
													$publish
													)
											");
		
		#var_dump($updateSQL1);
		
		if($updateSQL1 === true) {
			
			if(GBOOK_MODERATE == "mail" && GBOOK_NOTIFY_EMAIL && !$this->adminPage) {
							
				$mailStatus	= false;
				
				// ggf. Info-Mail senden
				$anrede		= htmlspecialchars(User::getMailLocalPart(GBOOK_NOTIFY_EMAIL));
				$name		= htmlspecialchars($GLOBALS['_POST']['name']);
				$datum		= htmlspecialchars($GLOBALS['_POST']['postdate']);
				$nachricht	= nl2br(htmlspecialchars(substr($GLOBALS['_POST']['message'], 0, 512))) . (strlen($GLOBALS['_POST']['message']) > 512 ? " ..." : "");
				$betreff	= '=?utf-8?B?'.base64_encode(ContentsEngine::replaceStaText("{s_header:newgbcomment}")." ".$name . ' - ' . $this->domain).'?=';			
				$email		= htmlspecialchars($email);
				
				// Nachricht
				$htmlMail = ContentsEngine::replaceStaText("
							<html>
								<head>
									<title>{s_header:newgbentry}</title>
									<style type='text/css'>
									</style>
								</head>
								<body>
									<p>{s_text:hello} $anrede,</p>
									<p>{s_header:newgbentry}</p>
									<p>{s_header:newgbcomment} <strong>".$name."</strong> (".$datum.").</p>
									<p>{s_form:email}: ".$email."</p>
									<p>&nbsp;</p>
									<p><i>".$nachricht."</i></p>
									<p>&nbsp;</p>
									<hr>
									<p>{s_text:gomoderate}</p>
									<p><a href='" . PROJECT_HTTP_ROOT . "/" . $this->gbPath . "'>{s_link:gotogb}</a></p>
									<p><a href='" . PROJECT_HTTP_ROOT . "/login" . PAGE_EXT . "'>{s_link:gotologin}</a></p>
								</body>
							</html>
							") . "\n";
		
				
				// Klasse phpMailer einbinden
				require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
				require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.smtp.php');
				
				// Instanz von PHPMailer bilden
				$mail = new \PHPMailer();
						
				// E-Mail-Parameter für SMTP
				$mail->setMailParameters(SMTP_MAIL, AUTO_MAIL_AUTHOR, GBOOK_NOTIFY_EMAIL, $betreff, $htmlMail, true, "", "smtp");
				
				// E-Mail senden per phpMailer (SMTP)
				$mailStatus = $mail->send();
				
				// Falls Versand per SMTP erfolglos, per Sendmail probieren
				if($mailStatus !== true) {
					
					// E-Mail-Parameter für php Sendmail
					$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, GBOOK_NOTIFY_EMAIL, $betreff, $htmlMail, true, "", "sendmail");
					
					// Absenderadresse der Email auf FROM: setzen
					#$mail->Sender = $email;		
					
					// E-Mail senden per phpMailer (Sendmail)
					$mailStatus = $mail->send();
				}
				// Falls Versand per Sendmail erfolglos, per mail() probieren
				if($mailStatus !== true) {
					
					// E-Mail-Parameter für php mail()
					$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, GBOOK_NOTIFY_EMAIL, $betreff, $htmlMail, true);
					
					// E-Mail senden per phpMailer (mail())
					$mailStatus = $mail->send();
				}
				
				$noticeExt = " {s_notice:moderate}";
				
			} // Ende if moderate
				
			$this->setSessionVar('notice', "{s_notice:gbentry}".$noticeExt);
			setcookie('gb_spam_protection', 'gb_spam_protection', time()+300, '/');
					
			return true;
			
		} // Ende if update = true
		else {
			$this->error = '{s_error:gbfail}';
			return false;
		}

				
	}


}
