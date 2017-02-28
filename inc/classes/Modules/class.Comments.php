<?php
namespace Concise;



/**
 * Klasse für Kommentare
 *
 */

class Comments extends Modules
{
	 
	/**
	 * Beinhaltet die Anzahl an Kommentaren zu einem Datensatz
	 *
	 * @access public
     * @var    string
     */
	public $totComments = 0;
	
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
	public $errorEmpty = "";
	
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
	public $errorUrl = "";
	
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
	public $errorCap = "";
	
	/**
	 * Zur Bestimmung ob das Formular angezeigt werden soll.
	 *
	 * @access public
     * @var    string
     */
	public $showForm = "";
	
	/**
	 * Formular für Kommentare.
	 *
	 * @access public
     * @var    string
     */
	public $commentForm = "";
	
	/**
	 * Editieren erlaubt.
	 *
	 * @access private
     * @var    boolean
     */
	private $allowEdit = false;
	
	/**
	 * Beinhaltet die aktuelle Domain
	 *
	 * @access public
     * @var    string
     */
	public $domain = "";
	
	/**
	 * Beinhaltet den Pfad zum aktuellen Artikel.
	 *
	 * @access public
     * @var    string
     */
	public $dataPath = "";
	
	/**
	 * Beinhaltet den absoluten Pfad zum aktuellen Artikel.
	 *
	 * @access public
     * @var    string
     */
	public $dataAbsPath = "";
	
	/**
	 * Beinhaltet den Querystring.
	 *
	 * @access public
     * @var    string
     */
	public $adminQS = "";
	
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
	 * Comment author details
	 *
	 * @access public
     * @var    string
     */
	public $authorName = "";
	public $authorMail = "";
	public $authorUrl = "";
	
	/**
	 * Mail subject
	 *
	 * @access public
     * @var    string
     */
	public $mailSubject = "";
	
	/**
	 * Comment recipient name
	 *
	 * @access private
     * @var    string
     */
	private $recipientName = "";
	
	/**
	 * Comment date
	 *
	 * @access private
     * @var    string
     */
	private $commentDate = "";
	
	/**
	 * Comment text
	 *
	 * @access private
     * @var    string
     */
	private $commentText = "";
	

	/**
	 * Erstellt eine Kommentarliste
	 * 
 	 * @param	object	$DB		DB-Objekt
 	 * @param	object	$o_lng	Sprachobjekt
	 * @param	string	$dataTable Datenbanktabelle (default = all)
	 * @param	string	$entryID ID des Datenbankeintrags, auf den sich die Kommentare beziehen (default = '')
	 * @param	string	$dataPath Pfad zum Datenbankeintrag (default = '')
	 * @param	string	$group Benutzergruppe (default = public)
     * @param	array	$readCommentGroups Benutzergruppen, die zum Lesen von Kommentaren berechtigt sind
     * @param	array	$writeCommentGroups Benutzergruppen, die zum Schreiben von Kommentaren berechtigt sind
     * @param	boolean	$checkForm Formular für Kommentare einbinden/überprüfen (default = false)
	 * @access	public
	 */
	public function __construct($DB, $o_lng, $dataTable = "all", $entryID = "", $dataPath = "", $group = "public", $readCommentGroups = array(), $writeCommentGroups = array(), $checkForm = false)
	{
	
		// Datenbankobjekt
		$this->DB		= $DB;
		
		// Sprache
		$this->o_lng	= $o_lng;
		
		// Sprache
		$this->lang		= $this->o_lng->lang;
		
		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();
	
		// Falls Admin oder Editor
		$this->backendLog	= $this->o_security->get('backendLog');
		$this->editorLog	= $this->o_security->get('editorLog');
		$this->adminPage	= $this->o_security->get('adminPage');
		
		// Lese- und Schreibberechtigungen überprüfen
		$this->readCommentsGroups	= $readCommentGroups;
		$this->writeCommentsGroups	= $writeCommentGroups;

		
		// Falls Editor und FE-Mode, TinyMCE einbinden
		if($this->editorLog && parent::$feMode){
			$this->scriptFiles[]	= "extLibs/tinymce/tinymce.min.js";
			#$this->scriptFiles[]	= "extLibs/tinymce/jquery.tinymce.min.js";
			$this->scriptFiles[]	= "system/access/js/myFileBrowser.js";
			$this->scriptFiles[]	= "system/access/js/myTinyMCE.comments.js";
		}
		
		$this->dataTable	= $dataTable;
		
		// Falls Gästebuch-Kommentare
		if($this->dataTable == "gbook") {
				
			// Leseberechtigung überprüfen
			if(in_array("public", $GLOBALS['gbookReadPermission']) || in_array($group, $GLOBALS['gbookReadPermission']) || $this->editorLog)
				$this->readPermission = true;
				
			// Schreibberechtigung überprüfen
			if(in_array("public", $GLOBALS['gbookWritePermission']) || in_array($group, $GLOBALS['gbookWritePermission']) || $this->editorLog)
				$this->writePermission = true;
		}
		
		// Falls Datenmodul-Kommentare (articles, news, planner)
		else {
				
			// Leseberechtigung überprüfen
			if(in_array("public", $this->readCommentsGroups) || in_array($group, $this->readCommentsGroups) || $this->editorLog)
				$this->readPermission = true;
				
			// Schreibberechtigung überprüfen
			if(in_array("public", $this->writeCommentsGroups) || in_array($group, $this->writeCommentsGroups) || $this->editorLog)
				$this->writePermission = true;
			
		}
		$this->dataTables	= $GLOBALS['commentTables'];
		$this->dataPath		= htmlspecialchars(str_replace("//", "/", $dataPath));
		$this->dataAbsPath	= PROJECT_HTTP_ROOT . '/' . $this->dataPath;
		
		// Emoticons einbinden
		if(in_array("comments", $GLOBALS['emoticonForms'])) {
			$this->emoticons = true;
			#require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.Emoticons.php";
			#$this->o_emoticons = new Emoticons();
		}
		
		// Falls Adminbereich, Query String erweitern
		if($this->editorLog && $this->adminPage)
			$this->adminQS = "task=modules&type=comments";
		
		// Falls ein neuer Kommentar abgegeben werden soll
		elseif($checkForm && isset($GLOBALS['_POST']['ct_newentry']) && $this->writePermission) {
			
			if(isset($GLOBALS['_POST']['ct_table']) && in_array($GLOBALS['_POST']['ct_table'], $this->dataTables))
				$this->dataTable = $GLOBALS['_POST']['ct_table'];
			
			$checkForm = $this->checkCommentsForm($this->dataTable, $entryID);
			
			if($checkForm === true && !isset($GLOBALS['_GET']['rep'])) {
				if(!isset($this->g_Session['notice']))
					$this->setSessionVar('notice', "{s_notice:newentry}");
				header("Location: ?rep=newentry&" . $this->adminQS);
				exit;
			}
		}
		
		if(isset($GLOBALS['_GET']['rep']))
			$this->showForm = 'no';
		
		
		// Falls die Benachrichtigung für neue Kommentare eingestellt werden soll
		if(isset($GLOBALS['_GET']['comment']) && $GLOBALS['_GET']['comment'] == "nofollow")
			$this->unsubscribeNotify($this->dataTable);
	
	}
	


	/**
	 * Erstellt eine Kommentarliste
	 * 
	 * @param	string	$dataTable Datenbanktabelle (default = all)
	 * @param	string	$entryID ID des Datenbankeintrags, auf den sich die Kommentare beziehen (default = '')
     * @param   int		$maxRows maximale Anzahl an Einträgen (default = COMMENTS_MAX_ROWS)
	 * @access	public
	 * @return	string
	 */
	public function getComments($dataTable = "all", $entryID = "all", $maxRows = COMMENTS_MAX_ROWS)
	{
	
		// Falls keine Leseberechtigung (Gästebuch), Meldung statt Gästebuch-Kommentare ausgeben
		if(!$this->readPermission && !$this->writePermission && !$this->adminPage)
			return "no read/write permission";
			
		$dataTableDB	= $this->DB->escapeString($dataTable);
		$entryID		= $this->DB->escapeString($entryID);
		$restrict		= "";
		$redirect		= "";
		$pubFilter		= "all";
		$filter			= "";
		$dbOrder		= " ORDER BY `date` DESC";
		$formAction		= ADMIN_HTTP_ROOT . "?task=modules&type=comments";
		
		
		if($this->editorLog && $this->adminPage) {
			
			if(isset($GLOBALS['_POST']['ct_table']) && $GLOBALS['_POST']['ct_table'] != "") {
				$dataTableP = $GLOBALS['_POST']['ct_table'];
				
				if($dataTableP == "all") {
					$dataTable = "";
					if(isset($this->g_Session['ct_table']))
						$this->unsetSessionKey('ct_table');
				}
				else {
					$this->setSessionVar('ct_table', $dataTableP);
					$dataTable = $dataTableP;
					$restrict = "WHERE `table` = '" . $dataTableDB . "'";
				}
			}
				
			elseif(isset($this->g_Session['ct_table']) && $this->g_Session['ct_table'] != "" && $GLOBALS['_GET']['type'] == "comments") {
				$dataTable = $this->g_Session['ct_table'];
				$restrict = "WHERE `table` = '" . $dataTableDB . "'";
			}
			
			elseif($GLOBALS['_GET']['type'] != "comments")
				$restrict = "WHERE `table` = '" . $dataTableDB . "' AND `entry_id` = '" . $entryID . "'";


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
					$dbOrder = " ORDER BY `date` ASC";
					break;
					
				case "datedsc":
					$dbOrder = " ORDER BY `date` DESC";
					break;
					
				case "nameasc":
					$dbOrder = " ORDER BY `author` ASC";
					break;
					
				case "namedsc":
					$dbOrder = " ORDER BY `author` DESC";
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
		
			$restrict = "WHERE `table` = '" . $dataTableDB . "' AND `entry_id` = '" . $entryID . "'";
			
			if(!$this->editorLog)
				$restrict .= " AND `published` = 1";
			
			$redirect = $this->dataPath;
		}
		
		#if(isset($GLOBALS['_GET']['newcom']) && $GLOBALS['_GET']['newcom'] != "" && is_numeric($GLOBALS['_GET']['newcom']) && !isset($GLOBALS['_COOKIE']['comment_spam_protection'])) { // Falls der Get-Parameter für die Erstellung eines neuen Eintrags gesetzt ist, Formular für neuen Eintrag anzeigen
		if($this->showForm != "no" && !$this->adminPage) { // Falls der Get-Parameter für die Erstellung eines neuen Eintrags gesetzt ist, Formular für neuen Eintrag anzeigen
		
			// Falls eine Schreibbreichtigung vorliegt
			if($this->writePermission) {
				
				#$entryID = $GLOBALS['_GET']['newcom'];
				
				if(isset($GLOBALS['_GET']['mod']) && in_array($GLOBALS['_GET']['mod'], $this->dataTables)) // Falls der Get-Parameter für die Erstellung eines neuen Eintrags gesetzt ist, Formular für neuen Eintrag anzeigen
					$dataTable = $GLOBALS['_GET']['mod'];

				$this->commentForm = $this->getCommentsForm($dataTable, $entryID);
			}
			else
				return "no write permission";
		}
		
		// Falls keine Leseberechtigung, Meldung statt Kommentare ausgeben
		elseif(!$this->readPermission && !$this->adminPage)
			return "no read permission";
		
		
		// Kommentare anzeigen
		$this->pageNum		= 0;
		$this->maxRows		= $maxRows;
		$comments			= "";
		$admindataTableSel	= "";
		$adminFilter		= "";
		$adminMarkAll		= "";

		// Suche nach Kommentar-Einträgen
		$queryCom = $this->DB->query("SELECT * 
										FROM `" . DB_TABLE_PREFIX . "comments` 
										$restrict 
										");
				
		#var_dump($restrict);
		
		if(!is_array($queryCom))
			return "";
		
		
		$this->totalRows = count($queryCom);
		
		
		// Pagination
		if (isset($GLOBALS['_GET']['pageNumC']))
			$this->pageNum = $GLOBALS['_GET']['pageNumC'];
		
		$this->startRow = $this->pageNum * $this->maxRows;
		$query_limit = " LIMIT " . $this->startRow . "," . $this->maxRows;
  
  
		// Gruppenauswahl falls Adminbereich
		if($this->editorLog && $this->adminPage && $GLOBALS['_GET']['type'] == "comments") {
		
			$this->adminQS = "task=modules&type=comments&sort_param=$sortCat&pub=$pubFilter&limit=$this->maxRows";
			
			$admindataTableSel =	'<div class="adminBox">' . "\r\n" . 
									'<div class="controlBar">' . "\r\n" .
									'<form action="'.$formAction.'" method="post">' . "\r\n" . 
									'<span class="left"><label>{s_label:datatables}</label>' . "\r\n" .
									'<select name="ct_table" class="listCat" data-action="autosubmit">' . "\r\n" . 
									'<option value="all"' . (isset($dataTable) && $dataTable == "<all>" ? ' selected="selected"' : '') . '>{s_option:alldatatables}</option>' . "\r\n";
			
			foreach($this->dataTables as $table) {
				
				$admindataTableSel .=	'<option value="' . $table . '"' . (isset($dataTable) && $dataTable == $table ? ' selected="selected"' : '') . '>{s_option:' . $table . '}</option>' . "\r\n";						
			}
						
			$admindataTableSel .= 	'</select></span>' . "\r\n" .
									'<div class="sortOption small left"><label>{s_label:sort}</label>' . "\r\n" .
									'<select name="sort_param" class="listSort" data-action="autosubmit">' . "\r\n";
			
			$sortOptions = array("dateasc" => "{s_option:dateasc}",
								 "datedsc" => "{s_option:datedsc}",
								 "nameasc" => "{s_option:nameasc}",
								 "namedsc" => "{s_option:namedsc}"
								 );
			
			foreach($sortOptions as $key => $value) { // Sortierungsoptionen
				
				$admindataTableSel .='<option value="' . $key . '"';
				
				if(isset($GLOBALS['_POST']['sort_param']) && $key == $sortCat)
					$admindataTableSel .=' selected="selected"';
					
				$admindataTableSel .= '>' . $value . '</option>' . "\r\n";
			
			}
								
			$admindataTableSel .= 	'</select></div>' . "\r\n" .
									'<div class="sortOption small left"><label>{s_label:limit}</label>';
									
			$admindataTableSel .= 	$this->getLimitSelect($this->limitOptions, $maxRows);
			
			$admindataTableSel .=	'</div>' . "\r\n";
			
			$admindataTableSel .= 	'<div class="filterOption small left"><label for="all">{s_label:all}</label>' . "\r\n" .
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


			$admindataTableSel .=	'</form><br class="clearfloat" /></div>' . "\r\n";

			
			// List filter
			// Falls Gruppenfilter oder Abofilter, Filter löschen Button einfügen
			if((!empty($pubFilter) && $pubFilter != "all")
			|| (!empty($dataTable) && $dataTable != "all" && $dataTable != "<all>")
			) {
			
				$filterStr	= "";
				
				if(!empty($dataTable) && $dataTable != "all")
					$filterStr	.= '<strong>{s_label:cat} &quot;' . $dataTable . '&quot;</strong>';
			
				
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
							
				$adminFilter .=	'<input type="hidden" name="ct_table" value="all">' . "\r\n" .
								'<input type="hidden" name="filter_sent" value="all">' . "\r\n" .
								'</span>' . "\r\n" .
								'</form>' . "\r\n" .
								'</span>' . "\r\n";
			}

			
			// Checkbox zur Mehrfachauswahl im Adminbereich
			$adminMarkAll =				'<ul class="commentList">' . "\r\n" .
										'<form action="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=comments&entryid=array&action=" method="post">' . "\r\n" .
										'<div class="actionBox"><label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" /></label><label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' . "\r\n" .
										'<span class="editButtons-panel">' . "\r\n";
			
			// Button publish
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'pubAll pubComments button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:pubmarked}',
									"icon"		=> "publish"
								);
				
			$adminMarkAll .=	parent::getButton($btnDefs);
			
			// Button unpublish
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'pubAll pubComments unpublish button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:unpubmarked}',
									"icon"		=> "unpublish"
								);
				
			$adminMarkAll .=	parent::getButton($btnDefs);

			// Button delete
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'delAll delComments button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:delmarked}',
									"attr"		=> 'data-action="delmultiple"',
									"icon"		=> "delete"
								);
				
			$adminMarkAll .=	parent::getButton($btnDefs);
									
			$adminMarkAll .=		'</span>' . "\r\n" .
									'</div>' . "\r\n";
							
							
		}


		// Suche nach Kommentar-Einträgen
		$queryCom = $this->DB->query("SELECT `comments`.*, `user`.`userid` 
											FROM `" . DB_TABLE_PREFIX . "comments` AS comments 
												LEFT JOIN `" . DB_TABLE_PREFIX . "user` AS user 
												ON `comments`.`userid` = `user`.`userid` 
											$restrict 
											$dbOrder 
											$query_limit
											");
				
		#var_dump($queryCom);
			

		if(isset($this->notice) && $this->notice != "")
			$comments .=		'<p class="notice success {t_class:alert} {t_class:success}">' . $this->notice . '</p>' . "\r\n";
		
		$comments .=			$admindataTableSel;	// Im Adminbereich Gruppenauswahl einbinden
		
		$i = 0;
		$comCount	= count($queryCom);
		$comBoxID	= "comments-" . $entryID;
		
		if(!isset($GLOBALS['_GET']['type'])
		|| $GLOBALS['_GET']['type'] != "comments"
		)
			$comments .=	'<div class="commentBoxHeader {t_class:fullrow}">' .
							parent::getIcon('comments', 'inline-icon {t_class:left}') .
							'<span class="comments" id="comment-' . $entryID . '">' .
							'<span class="commentsLabel {t_class:badge}">' .
							($comCount > 0 ? '<span class="toggle {t_class:link}" data-toggle="' . $comBoxID .'">' . $this->totalRows . ' {s_text:comments}</span>' : $comCount . ' {s_text:comments}') .
							'</span>' .
							'</span>';
		
		if(!$this->adminPage
		&& $this->totalRows < MAX_COMMENTS
		) {
			$icon	   = parent::getIcon("pencil");
			$comments .=	'<span class="newComment comments"><a href="' . parent::$currentURL . '?newcom='.$entryID.($this->adminQS != "" ? '&amp;' : '') . $this->adminQS . '#commentForm" rel="nofollow" class="{t_class:btn} {t_class:btnlink}">' . $icon . '{s_link:comments}</a></span>' . "\r\n";
		
			// Formular für neuen Kommentar einfügen
			if($this->showForm != "no")
				$comments .= $this->commentForm;
		}
		
	
		if($comCount > 0) { // Falls Kommentare vorhanden sind
			
			$comments  .= 	$adminFilter . $adminMarkAll; // Alles Markieren einfügen, falls Adminbereich
			
			if(!isset($GLOBALS['_GET']['type']) || $GLOBALS['_GET']['type'] != "comments")
				$comments .=	'</div>' .
								'<div id="' . $comBoxID . '" class="comments {t_class:fullrow}" style="display:' . (isset($GLOBALS['_GET']['pageNumC']) ? 'block' : 'none') . '">' . "\r\n";
			else
				$comments .=	'<div id="' . $comBoxID . '" class="commentBox">' . "\r\n";
			
			for($j = 0; $j < $comCount; $j++) {
				
				$commentID	= htmlspecialchars($queryCom[$j]["id"]);
				$catTable	= htmlspecialchars($queryCom[$j]["table"]);
				$dataID		= htmlspecialchars($queryCom[$j]["entry_id"]);
				$userID		= str_pad($queryCom[$j]['userid'], 9, '0', STR_PAD_LEFT);
				$author		= htmlspecialchars($queryCom[$j]["author"]);
				$date		= htmlspecialchars(date("d.m.Y", strtotime($queryCom[$j]["date"])));
				$time		= htmlspecialchars(date($this->lang == "en" ? "g.ia" : "H:i", strtotime($queryCom[$j]["date"])));
				$comment	= $queryCom[$j]["comment"];
				$email		= htmlspecialchars($queryCom[$j]["email"]);
				$gravatar	= htmlspecialchars($queryCom[$j]["gravatar"]);
				$url		= htmlspecialchars($queryCom[$j]["url"]);
				
				// Falls eine Url angegeben war, diese als Link um den Author einbinden
				if($url != "")
					$authorTag = '<a href="' . $url . '" rel="nofollow">' . $author . '</a>';
				else
					$authorTag = $author;


				// Pfad zum Avatarplatzhalter
				if($this->adminPage)
					$imgSrc		= SYSTEM_IMAGE_DIR . '/empty_avatar.png';
				else
					$imgSrc		= PROJECT_HTTP_ROOT . '/' . IMAGE_DIR . 'empty_avatar.png';
				
				
				// Benutzerbild, falls vorhanden
				if($gravatar == 1 && $email != "") // ...Gravatar
					$imgSrc		= 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . "?r=pg&d=" . urlencode($imgSrc); // Gravatar Hash generieren
				elseif($userID > 0) // geloggter Benutzer
					$imgSrc		= User::getUserImageSrc($userID);
				
				
				#if($this->emoticons === true)
				#	$comment = $this->o_emoticons->getEmoticons($comment); // Emoticons einbinden
				
				
				$comment = nl2br($comment);
				$comment = str_replace(array("\r","\n"), "", $comment);
				#$comment = preg_replace('~\S{100}~', ' ... ', $comment); // "Begriffe" mit mehr als 100 zusammenhängenden Zeichen, die kein Leerzeichen enthalten, löschen
				
				$comments .='<div class="comment listEntry {t_class:panel}' . ($j % 2 ? " alternate" : "") . '" data-menu="context" data-target="contextmenu-com-' . $j . '">' . "\r\n";
				
				
				$editButtons	= "";
				$markBox		= "";
							
				// Falls Editor/Admin
				if($this->editorLog) {
				
					// Überprüfen ob Editieren erlaubt
					if($this->adminPage || parent::$feMode)
						$this->allowEdit	= $this->checkAccess($userID);
					
					
					// Falls alle Kommentare angezeigt werden (oder jeweils beim 1. Kommentar), Tabelle/Rubrik anzeigen
					if($dataTable == "all" || $dataTable == "" || $j == 0)
						$comments .=	'<div class="commentCat {t_class:panelhead}">{s_nav:admin' . $catTable . '} - {s_label:comments}</div>' . "\r\n";
					
					// Checkboxen zur Mehrfachauswahl im Adminbereich
					if($this->adminPage && isset($GLOBALS['_GET']['type']) && $GLOBALS['_GET']['type'] == "comments")
						$markBox	 .=	'<label class="markBox">' . 
										'<input type="checkbox" name="entryNr[' . $j . ']" class="addVal" />' .
										'<input type="hidden" name="entryID[' . $j . ']" value="' . $commentID . '" class="getVal" />' .
										'</label>';
					
					// Edit Buttons
					$editButtons	=	'<span class="commentIcons commentEditButtons editButtons-panel" data-id="contextmenu-com-' . $j . '">' . "\r\n";
					
				
					// Falls Adminbereich
					if($this->adminPage) {
									
						// Button link
						$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=' . $catTable . '&data_id=' . $dataID,
												"class"		=> 'commentData button-icon-only',
												"value"		=> "",
												"title"		=> '{s_title:goto} {s_nav:admin' . $catTable . '}',
												"attr"		=> 'data-menuitem="true" data-id="item-id-' . $j . '"',
												"icon"		=> $catTable
											);
							
						$editButtons .=	parent::getButtonLink($btnDefs);
					}
					else {
					
						// Button link
						$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=comments&data_id=' . $commentID,
												"class"		=> 'commentData button-icon-only',
												"value"		=> "",
												"title"		=> '{s_title:goto} {s_nav:admincomments}',
												"attr"		=> 'data-menuitem="true" data-id="item-id-' . $j . '"',
												"icon"		=> "comments"
											);
							
						$editButtons .=	parent::getButtonLink($btnDefs);
					}
					
					$editButtons .=	'<span class="switchIcons">' . "\r\n";
			
					// Button publish
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pubComment publish-state-hidden button-icon-only',
											"value"		=> "",
											"title"		=> '{s_title:publishcomment}',
											"attr"		=> 'data-action="pubdata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=comments&action=pubentry&set=1&id=' . $commentID . '" data-menuitem="true" data-id="item-id-' . $j . '" data-publish="1"' . ($queryCom[$j]['published'] == 1 ? ' style="display:none;"' : ''),
											"icon"		=> "unpublish"
										);
						
					$editButtons .=	parent::getButton($btnDefs);
			
					// Button unpublish
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'pubComment publish-state-visible button-icon-only',
											"value"		=> "",
											"title"		=> '{s_title:nopublishcomment}',
											"attr"		=> 'data-action="pubdata" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&mod=comments&action=pubentry&set=0&id=' . $commentID . '" data-menuitem="true" data-id="item-id-' . $j . '" data-publish="0"' . ($queryCom[$j]['published'] == 0 ? ' style="display:none;"' : ''),
											"icon"		=> "publish"
										);
						
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .=	'</span>' . "\r\n";
			
					// Button delete
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'delComment button-icon-only',
											"value"		=> "",
											"title"		=> '{s_title:delcom}',
											"attr"		=> 'data-action="delete" data-url="' . ADMIN_HTTP_ROOT . '?task=modules&amp;type=comments&amp;action=del&amp;id=' . $commentID . '&amp;red=' . $redirect . '" data-menuitem="true" data-id="item-id-' . $j . '"',
											"icon"		=> "delete"
										);
						
					$editButtons .=	parent::getButton($btnDefs);
					
					$editButtons .=	'</span>' . "\r\n";	
				}
				
				$comments .='<div class="commentHeader listEntryHeader {t_class:panelhead}">' . "\r\n" .
							$markBox .
							'<span class="dataAuthor {t_class:paneltitle}">' . $authorTag . '</span>' . "\r\n" .
							'<span class="dataDate"> {s_text:commented} </span>' . $date . '<span> {s_text:attime} </span>' . $time . '<span> {s_text:time}</span><span class="entryNo {t_class:badge} {t_class:right}">#' . ($comCount - $j) . '</span>' . "\r\n" .
							$editButtons .
							'</div>' . "\r\n" .
							'<div class="{t_class:panelbody} {t_class:media}">' . "\r\n" .
							'<div class="avatar {t_class:medialft}"><img src="' . $imgSrc . '" alt="avatar" class="avatar {t_class:mediaobj}" /></div>' . "\r\n" .
							'<div class="{t_class:mediabody}">' . "\r\n" .
							'<div class="commentText {t_class:mediabody}' . ($this->allowEdit ? ' editableComment" title="{s_title:edittext}"' : '"') . '>' . $comment .
							'</div>' . "\r\n" .
							'</div>' . "\r\n";
							
				// Entry-ID
				if($this->allowEdit)
					$comments .=	'<input type="hidden" name="commentEntryEditUrl[' . $j . ']" value="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=editcomment&mod=comments&id=' . $commentID . '" class="commentEntryEditUrl commentEditUrl" />' . "\r\n";

				$comments .=	'<br class="clearfloat">' . "\r\n";
				$comments .=	'</div>' . "\r\n";	
				$comments .=	'</div>' . "\r\n";
			}
			
			
			// Formtag schließen (Checkboxen im Adminbereich)
			if($this->editorLog && $this->adminPage && isset($GLOBALS['_GET']['type']) && $GLOBALS['_GET']['type'] == "comments")
				$comments .=	'</form>' . "\r\n";
			
			
			$comments .= Modules::getPageNav($this->maxRows, $this->totalRows, $this->startRow, $this->pageNum, ($this->adminQS != "" ? $this->adminQS : '::'.$this->dataAbsPath), "C", ($this->adminPage == false ? true : false), Modules::getLimitForm($this->limitOptions, $this->maxRows));
			
			$comments .=		'</div>' . "\r\n";
		
		
			// Contextmenü-Script
			$comments .=		$this->getContextMenuScript();
		
		
		}

		elseif($this->adminPage && $GLOBALS['_GET']['type'] == "comments") {
  
			$this->notice = "{s_notice:nocomments}";
			
			$comments .=	$adminFilter;
			
			$comments .=	'<p class="notice error empty {t_class:alert} {t_class:warning}">' . $this->notice . '</p>' . "\r\n";
							
		}
		
		else
			$comments .=	'</div>' . "\r\n";

		
		if($this->adminPage
		&& !empty($admindataTableSel)
		)
			$comments .=	'</div>' . "\r\n";
		
		$comments = ContentsEngine::replaceStaText($comments);
		
		return $comments;


	}
	
	

	/**
	 * Gibt die Anzahl an Kommentaren zurück
	 * 
	 * @param	string	$dataTable Datenbanktabelle (default = all)
	 * @param	string	$entryID ID des Datenbankeintrags, auf den sich die Kommentare beziehen (default = '')
	 * @access	public
	 * @return	string
	 */
	public function getCommentNumber($dataTable = "all", $entryID = "all")
	{
						
		$dataTableDB = $this->DB->escapeString($dataTable);
		$entryID = $this->DB->escapeString($entryID);
		
		$restrict = "WHERE `table` = '" . $dataTableDB . "' AND `entry_id` = '" . $entryID . "'";
		
		if(!$this->editorLog)
			$restrict .= " AND `published` = 1";
				

		// Suche nach Kommentar-Einträgen
		$queryCom = $this->DB->query("SELECT COUNT(*) 
										FROM `" . DB_TABLE_PREFIX . "comments` 
										$restrict 
										", false);
				
		#var_dump($queryCom);
		
        $this->totComments = $queryCom[0]['COUNT(*)'];
        
		$comments =	'<span class="commentNum {t_class:halfrow}">' . $this->totComments . ' {s_text:comments}</span>';
		
		return $comments;

	}
	
	

	/**
	 * Erstellt ein Formular für einen neuen Kommentar
	 * 
	 * @param	string	$dataTable Datenbanktabelle (default = all)
	 * @param	string	$entryID ID des Datenbankeintrags, auf den sich die Kommentare beziehen (default = '')
	 * @access	public
	 * @return	string
	 */
	public function getCommentsForm($dataTable = "all", $entryID = "")
	{
	
		// Falls keine Leseberechtigung (Gästebuch), Meldung statt Gästebuch-Kommentare ausgeben
		if(!$this->writePermission)
			return "no write permission";
		
		$queryStr		= "";
		$formAction		= '?newcom=' . $entryID . '#commentForm';
		$form			= "";
		$noticeOpenTag	= '<span class="notice {t_class:texterror}">';
		$noticeCloseTag	= '</span>';
		
		
		if($this->editorLog && $this->adminPage) {
			$queryStr = "?task=modules&type=comments";
			$formAction = ADMIN_HTTP_ROOT . '?task=modules&type=comments';
		}		
		
		// Textarea editor
		if(!parent::$feMode){
			$this->scriptFiles[]	= "extLibs/tinymce/tinymce.min.js";
			$this->scriptFiles[]	= "system/access/js/myTinyMCE.comments-simple.js";
		}
		
		// Falls das Formular für einen neuen Kommentar auf einer eigenen Seite angezeigt wird, Buttons einfügen
		if(isset($GLOBALS['_GET']['newcom'])) {
		
			$btnIcon	= $dataTable != "gbook" ? "book" : "back";
			
			// Button link
			$btnDefs	= array(	"href"		=> "javascript:history.back();",
									"class"		=> 'link backtoLast {t_class:btndef}',
									"text"		=> '{s_link:backtolast'.$dataTable.'}',
									"icon"		=> "prev"
								);
				
			$btnLast =	parent::getButtonLink($btnDefs);
		
			// Button link
			$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/' . $GLOBALS['_GET']['page'] . PAGE_EXT . $queryStr,
									"class"		=> 'link backtoList {t_class:btndef}',
									"text"		=> '{s_link:backto'.$dataTable.'}',
									"icon"		=> $btnIcon
								);
				
			$btnList =	parent::getButtonLink($btnDefs);
		
			$form .=	($dataTable != "gbook" ? $btnLast : '') .
						$btnList;
		}
		
		$form .=	'<div id="commentForm" class="form cc-comments cc-module {t_class:margintm}"' . (!isset($GLOBALS['_POST']['ct_newentry']) && !isset($GLOBALS['_GET']['newcom']) ? ' style="display:none;"' : '') . '>' . "\r\n" .
					'<div class="top"></div>' . "\r\n" .
					'<div class="center">' . "\r\n" .
					'<form id="ct_newentry" class="ct_newentry {t_class:form}" action="' . $formAction  . '" method="post">' . "\r\n" .
					'<fieldset class="{t_class:panel}">' . "\r\n" .
					'<legend class="{t_class:panelhead}">{s_form:commentstit}</legend>' . "\r\n" .
					'<div class="{t_class:panelbody}">' . "\r\n";
		
		if($this->report != "")
			$form .=	'<p class="notice {t_class:alert} {t_class:success}">' . $this->report . '</p>' . "\r\n";
		
		elseif($this->error != "")
			$form .=	'<p class="notice error {t_class:alert} {t_class:error}">' . $this->error . '</p>' . "\r\n";
		
		 // Falls nur Meldungen ausgegeben werden sollen (z.B. max. Anzahl an Comments erreicht) Formular nicht anzeige
		if($this->showForm == 'no') {
		
			$form .=	'</div>' . "\r\n" .
						'</fieldset>' . "\r\n" .
						'</form>' . "\r\n" .
						'</div>' . "\r\n" .
						'<div class="bottom"></div>' . "\r\n" .
						'</div>' . "\r\n";

			return ContentsEngine::replaceStaText($form);
		}
		
		
		// Andernfalls Formular anzeigen
		// Ggf. Benutzerdaten auslesen
		$this->authorName	= "";
		$this->authorMail	= "";
		
		if(isset($this->g_Session['author_name'])) {
			$this->authorName = $this->g_Session['author_name'];
		}
		elseif(isset($this->g_Session['username'])) {
			$this->authorName = User::getMailLocalPart($this->g_Session['username']);
		}
		if(isset($this->g_Session['usermail'])) {
			$this->authorMail = $this->g_Session['usermail'];
		}
		
		$form .=	'<p class="footnote topNote {t_class:alert} {t_class:info}">{s_form:req}</p>' . "\r\n" .
					'<ul>' . "\r\n" .
					'<li class="{t_class:formrow}">' . "\r\n" .
					'<label class="{t_class:label}" for="name">{s_form:name}<em>&#42;</em></label>' . "\r\n";
					
		if($this->errorName != "")
			$form .= $noticeOpenTag . $this->errorName . $noticeCloseTag . "\r\n";
					
					
		if($this->editorLog && $this->adminPage) {
		
			$form .='<label class="{t_class:label}" for="dataTable">{s_label:userG}<em>&#42;</em></label>' . "\r\n" . 
					'<select name="ct_table" class="{t_class:select} {t_class:field}">' . "\r\n";
			
			foreach($this->dataTables as $table) {
				
				$form .=	'<option value="' . $table . '"' . (isset($GLOBALS['_POST']['ct_table']) && $GLOBALS['_POST']['ct_table'] == $table ? ' selected="selected"' : '') . '>' . $table . '</option>' . "\r\n";						
			}
			
			$form .=	'</select>' . "\r\n";
		}

		$form .=	'<input name="name" type="text" id="name" class="{t_class:field}" aria-required="true" value="';
					
		$form .= 	isset($GLOBALS['_POST']['name']) ? htmlspecialchars($GLOBALS['_POST']['name']) : $this->authorName;
					
		$form .=	'" maxlength="50" data-validation="required" data-validation-length="max50" />' . "\r\n" . 
					'</li>' . "\r\n" . 
					'<li class="{t_class:formrow}">' . "\r\n" .
					'<label class="{t_class:label}" for="email">E-Mail<em>&#42;</em></label>' . "\r\n";
					
		if($this->errorMail != "")
			$form .= $noticeOpenTag . $this->errorMail . $noticeCloseTag . "\r\n";
					
		$form .=	'<input name="email" type="text" id="email" class="{t_class:field}" aria-required="true" value="';
					
		$form .=	isset($GLOBALS['_POST']['email']) ? htmlspecialchars($GLOBALS['_POST']['email']) : $this->authorMail;
		
		$form .=	'" maxlength="128" data-validation="email" data-validation-length="max128" />' . "\r\n" . 
					'<input type="text" name="m-mail" id="m-mail" class="emptyfield" value="" />' . "\r\n" . 
					'</li>' . "\r\n";
					
		$form .=	'<li class="{t_class:rowcheckbox}">' . "\r\n" .
					'<label class="{t_class:label}" for="gravatar">' . "\r\n" .
					'<input type="checkbox" name="gravatar" id="gravatar" class="{t_class:checkbox}"' . (isset($GLOBALS['_POST']['gravatar']) ? ' checked="checked"' : '') . ' />' . "\r\n" . 
					'{s_form:gravatar}</label>' . "\r\n" .
					'</li>' . "\r\n";

		// Notify check, if not gbook
		if($dataTable != "gbook") {
			$form .=	'<li class="{t_class:rowcheckbox}">' . "\r\n" .
						'<label class="{t_class:label}" for="notify">' . "\r\n" .
						'<input type="checkbox" name="notify" id="notify" class="{t_class:checkbox}"' . (isset($GLOBALS['_POST']['notify']) ? ' checked="checked"' : '') . ' />' . "\r\n" . 
						'{s_form:notify}</label>' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		$form .=	'<li class="{t_class:formrow}">' . "\r\n" .
					'<label class="{t_class:label}" for="url">Website</label>' . "\r\n";
					
		if($this->errorUrl != "")
			$form .= $noticeOpenTag . $this->errorUrl . $noticeCloseTag . "\r\n";
					
		$form .=	'<input name="url" type="text" id="url" class="{t_class:input} {t_class:field}" value="';
					
		isset($GLOBALS['_POST']['url']) ? $form .= htmlspecialchars($GLOBALS['_POST']['url']) : '';
		
		$form .=	'" placeholder="http://" maxlength="256" data-validation="url" data-validation-optional="true" />' . "\r\n" . 
					'</li>' . "\r\n" .
					'<li class="{t_class:formrow}">' . "\r\n" .
					'<label class="{t_class:label}" for="message">{s_form:message}<em>&#42;</em></label>' . "\r\n";
					
		if($this->errorMes != "")
			$form .= $noticeOpenTag . $this->errorMes . $noticeCloseTag . "\r\n";
					
		$form .=	'<textarea name="message" id="message" class="{t_class:text} {t_class:field}" aria-required="true" data-validation="required" data-validation-length="max1800" rows="5" cols="30" accept-charset="UTF-8">';
					
		isset($GLOBALS['_POST']['message']) ?  $form .= htmlentities($GLOBALS['_POST']['message'], ENT_QUOTES, 'UTF-8') : '';
		
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
			
			$form .='<li class="{t_class:formrow}">' . "\r\n" .
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
		
		$form .=	'<li class="{t_class:formrow}">' . "\r\n" . 
					(COMMENTS_MODERATE ? '<p class="footnote {t_class:alert} {t_class:info}">{s_text:moderate}</p>' . "\r\n" : '');
		
		// Button submit
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "ct_newentry",
								"id"		=> "submit",
								"class"		=> '{t_class:btnpri} formbutton ok',
								"value"		=> "{s_button:submit}",
								"text"		=> "{s_button:submit}",
								"title"		=> '{s_title:delcom}',
								"icon"		=> "ok"
							);
			
		$form .=	parent::getButton($btnDefs);
		
		$form .=	'<input name="ct_newentry" type="hidden" value="{s_button:submit}" />' . "\r\n" .
					#'<input name="reset" type="button" id="reset" onClick="fieldRes();" value="{s_button:reset}" class="formbutton reset right" />' . "\r\n" .
					'<input name="postdate" type="hidden" id="hidden_date" value="' . date("Y-m-d H:i:s") . '" />' . "\r\n" .
					parent::getTokenInput() . 
					'</li>' . "\r\n" . 
					'</ul>' . "\r\n" . 
					'</div>' . "\r\n" .
					'</fieldset>' . "\r\n" . 
					'</form>' . "\r\n" .
					'</div><div class="bottom"></div>' . "\r\n" .
					'</div>' . "\r\n";
		
		
		// Form validator script
		$form	.= $this->getScriptTag();

		
		return ContentsEngine::replaceStaText($form);

	}
	
	

	/**
	 * Überprüft die Eingaben eines Kommentareintrags
	 * 
	 * @param	string	$dataTable Datenbanktabelle
	 * @param	string	$entryID ID des Datenbankeintrags, auf den sich die Kommentare beziehen
	 * @access	public
	 * @return	boolean
	 */
	public function checkCommentsForm($dataTable, $entryID)
	{
			
		// Formular auswerten
		$checkForm	= true;
		
		$messlg = strlen($GLOBALS['_POST']['message']); // Nachrichtenlänge auslesen
		
		// Falls der Testcookie beim Aufruf der Seite nicht gesetzt werden konnte, weil Cookies nicht aktiviert sind...
		if(empty($this->g_Session['captcha']) && (!isset($GLOBALS['_COOKIE']['cookies_on']) || $GLOBALS['_COOKIE']['cookies_on'] != "cookies_on")) {
			// ...zusätzliche Meldung ausgeben
			$this->error = '{s_error:sessmes}';
			$testCookie = "alert";
			$checkForm	= false;
		}			

		// ...andernfalls, falls der Cookie gegen Spam gesetzt wurde, Nachricht ausgeben
		if(!$this->backendLog
		 && ((isset($GLOBALS['_COOKIE']['comment_spam_protection'])
		  && $GLOBALS['_COOKIE']['comment_spam_protection'] == "comment_spam_protection")
		  || !empty($GLOBALS['_POST']['m-mail']))
		) {
			$this->error .= '{s_error:spam}';
			$this->showForm = 'no';
			$checkForm	= false;
		}
			
		// Falls keins der Felder ausgefüllt ist...
		if (empty($GLOBALS['_POST']['name']) && empty($GLOBALS['_POST']['email']) && empty($GLOBALS['_POST']['message']) && empty($GLOBALS['_POST']['captcha_confirm'])) {
			$this->errorEmpty .= '{s_error:fillreq}';
			$checkForm	= false;
		}

		// Falls Name leer ist...
		if (empty($GLOBALS['_POST']['name'])) {
			// ...Meldung ausgeben
			$this->errorName = '{s_error:name}';
			$checkForm	= false;
		}

		// Falls E-Mail leer ist...
		if (empty($GLOBALS['_POST']['email'])) {
			// ...Meldung ausgeben
			$this->errorMail = '{s_error:mail1}';
			$checkForm	= false;
		}

		// ...Falls keine E-Mail Adresse eingegeben wurde...
		if ($GLOBALS['_POST']['email'] == "" && isset($GLOBALS['_POST']['gravatar'])) {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorMail = '{s_error:mail1}';
			$checkForm	= false;
		}

		// ...Falls keine E-Mail Adresse eingegeben wurde...
		if ($GLOBALS['_POST']['email'] == "" && isset($GLOBALS['_POST']['notify'])) {
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
		
		// ...Falls eine Url eingegeben wurde, aber das Format falsch ist...
		if (isset($GLOBALS['_POST']['url']) && $GLOBALS['_POST']['url'] != "" && !filter_var($GLOBALS['_POST']['url'], FILTER_VALIDATE_URL)) {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorUrl = '{s_error:checkfield}';
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
		if((empty($GLOBALS['_POST']['captcha_confirm']) || 
			(trim($GLOBALS['_POST']['captcha_confirm']) == "") || strlen($GLOBALS['_POST']['captcha_confirm']) != 5 || 
			(!empty($this->g_Session['captcha']) && $GLOBALS['_POST']['captcha_confirm'] != $this->g_Session['captcha'])) && 
			$this->editorLog == false)
		{ 					
			$this->errorCap = '{s_error:captcha}';
			$checkForm	= false;
		}
		
		
		if($checkForm === true)
			return $this->safeComment($dataTable, $entryID);
			
		else {
			
			if($this->error == "")
				$this->error = '{s_error:checkform}';
			
			return false;
		}		
				
	}

	

	/**
	 * Überprüft Editierrechte
	 * 
	 * @param	string	$userID Benutzer-ID
	 * @access	private
	 * @return	boolean
	 */
	private function checkAccess($userID)
	{
	
		if($this->editorLog)
			return true;
		
		if($this->authorLog && $userID == $this->g_Session['userid'])	
			return true;
		
		return false;
	
	}
	
	

	/**
	 * Speichert einen Kommentar in der DB
	 * 
	 * @param	string	$dataTable Datenbanktabelle
	 * @param	string	$entryID ID des Datenbankeintrags, auf den sich die Kommentare beziehen
	 * @access	public
	 * @return	boolean
	 */
	public function safeComment($dataTable, $entryID)
	{
			
		// Kommentar speichern
		
		$dataTableDB	= $this->DB->escapeString($dataTable);
		$entryID		= (int)$entryID;
		$noticeExt		= "";
	
		// Felder auslesen
		$this->authorName       = $this->DB->escapeString(trim($GLOBALS['_POST']['name']));
		$this->authorMail		= $this->DB->escapeString(trim($GLOBALS['_POST']['email']));
		$this->authorUrl		= $this->DB->escapeString(trim($GLOBALS['_POST']['url']));
		$this->commentText 		= $this->DB->escapeString(trim($GLOBALS['_POST']['message']));
		$this->commentDate		= $this->DB->escapeString(trim($GLOBALS['_POST']['postdate']));

		// Ggf. UserID ermitteln (falls geloggter Benutzer)
		if(isset($this->g_Session['userid']))
			$userID = (int)$this->g_Session['userid'];
		else
			$userID = 0;

		if(isset($GLOBALS['_POST']['gravatar']))
			$gravatar = 1;
		else
			$gravatar = 0;

		if(isset($GLOBALS['_POST']['notify'])
		&& $dataTable != "gbook"
		)
			$notify = 1;
		else
			$notify = 0;

		if(COMMENTS_MODERATE)
			$publish = 0;
		else
			$publish = 1;


		$this->domain		= str_replace(array("http://","https://","www."), "", PROJECT_HTTP_ROOT);


		// db-Update der Comments Tabelle
		$insertSQL = $this->DB->query("INSERT INTO `" . DB_TABLE_PREFIX . "comments` 
												   (`table`,
													`entry_id`,
													`published`,
													`author`,
													`userid`,
													`date`, 
													`comment`,
													`email`,
													`gravatar`,
													`notify`,
													`url`
													) 
											VALUES ('$dataTableDB',
													'$entryID',
													$publish,
													'$this->authorName',
													$userID,
													'$this->commentDate', 
													'$this->commentText',
													'$this->authorMail',
													$gravatar,
													$notify,
													'$this->authorUrl'
													)
											");
		
		#var_dump($insertSQL);
		
		if($insertSQL === true) {
		
		
			// Nachricht an Betreiber
			if(COMMENTS_MODERATE == "mail"
			&& COMMENTS_NOTIFY_EMAIL
			) {
					
				$mailStatus	= false;
				
				// ggf. Info-Mail senden
				$this->recipientName	= htmlspecialchars(User::getMailLocalPart(COMMENTS_NOTIFY_EMAIL));
				$this->authorName		= htmlspecialchars($GLOBALS['_POST']['name']);
				$this->commentDate		= htmlspecialchars(Modules::getFormattedDateString(strtotime($GLOBALS['_POST']['postdate'])));
				$this->commentText		= nl2br(htmlspecialchars(substr($GLOBALS['_POST']['message'], 0, 512))) . (strlen($GLOBALS['_POST']['message']) > 512 ? " ..." : "");
				$this->mailSubject		= '=?utf-8?B?'.base64_encode(ContentsEngine::replaceStaText("{s_header:newcomment}") . ' ' . $this->authorName . ' - ' . $this->domain." ".$name).'?=';			
				$email		= htmlspecialchars($this->authorMail);
				$url		= htmlspecialchars($this->authorUrl);

				
				// Nachricht
				$htmlMail = ContentsEngine::replaceStaText("
							<html>
								<head>
									<title>{s_header:newcomment}</title>
									<style type='text/css'>
									</style>
								</head>
								<body>
									<p>{s_text:hello} $this->recipientName,</p>
									<p>{s_header:newcomment} <strong>".$this->authorName."</strong> ({s_text:commented} ".$this->commentDate." {s_text:time}).</p>
									<p>{s_form:email}: ".$email."</p>
									<p>Website: ".$url."</p>
									<p><i>".$this->commentText."</i></p>
									<p>&nbsp;</p>
									<hr>
									<p>{s_text:gomoderate}</p>
									<p><a href='" . $this->dataAbsPath . "?pageNumC=0#comment-" . $entryID . "'>{s_link:gotocomment}</a></p>
									<p><a href='" . PROJECT_HTTP_ROOT . "/login" . PAGE_EXT . "'>{s_link:gotologin}</a></p>
								</body>
							</html>
							") . "\n";
		
				
				// Klasse phpMailer einbinden
				require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
				
				// Instanz von PHPMailer bilden
				$mail = new \PHPMailer();
						
				// E-Mail-Parameter für SMTP
				$mail->setMailParameters(SMTP_MAIL, AUTO_MAIL_AUTHOR, COMMENTS_NOTIFY_EMAIL, $this->mailSubject, $htmlMail, true, "", "smtp");
				
				// E-Mail senden per phpMailer (SMTP)
				$mailStatus = $mail->send();
				
				// Falls Versand per SMTP erfolglos, per Sendmail probieren
				if($mailStatus !== true) {
					
					// E-Mail-Parameter für php Sendmail
					$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, COMMENTS_NOTIFY_EMAIL, $this->mailSubject, $htmlMail, true, "", "sendmail");
					
					// Absenderadresse der Email auf FROM: setzen
					#$mail->Sender = $email;		
					
					// E-Mail senden per phpMailer (Sendmail)
					$mailStatus = $mail->send();
				}
				// Falls Versand per Sendmail erfolglos, per mail() probieren
				if($mailStatus !== true) {
					
					// E-Mail-Parameter für php mail()
					$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, COMMENTS_NOTIFY_EMAIL, $this->mailSubject, $htmlMail, true);
					
					// E-Mail senden per phpMailer (mail())
					$mailStatus = $mail->send();
				}					
								
				$noticeExt = " {s_notice:moderate}";
			}
			
			
			// Falls kein notify gewünscht ggf. bei alten Beiträgen abstellen
			if(!$notify)
				$this->deleteCommentNotifications($entryID, $this->authorMail);
			
			
			// Ggf. direkt Nachricht an Diskussionsteilnehmer
			if(!COMMENTS_MODERATE) {
				
				$queryRecipients	= $this->getCommentSubscribers($dataTable, $entryID, $this->authorMail);
				$this->notifyCommentSubscribers($queryRecipients, $dataTable, $entryID);
			}
			
			
			$this->setSessionVar('notice', "{s_notice:gbentry}".$noticeExt);
			setcookie('comment_spam_protection', 'comment_spam_protection', time()+300, '/');
			
			return true;
		}
		else {
			$this->error = '{s_error:ctfail}';
			return false;
		}
			
	}
	
	

	/**
	 * Stellt die Registrierung für Benachrichtigungen ggf. bei alten Beiträgen ab
	 * 
	 * @param	string	$entryID	comment ID
	 * @param	string	$email		email
	 * @access	public
	 * @return	boolean
	 */
	public function deleteCommentNotifications($entryID, $email)
	{

		// Update Diskussionsteilnehmer
		$updateCom = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "comments` 
											SET `notify` = 0 
											WHERE `entry_id` = " . (int)$entryID . ",
											AND `notify` = 1 
											AND `email` = '" . $this->DB->escapeString($email) . "' 
											");
				
		#die(var_dump($updateCom));
		
		return $updateCom;
	
	}
	
	

	/**
	 * Subscriber für Comment-Benachrichtigungen auslesen
	 * 
	 * @param	string	$dataTable	data db table
	 * @param	string	$entryID	comment ID
	 * @param	string	$email		email des Verfassers
	 * @access	public
	 * @return	boolean
	 */
	public function getCommentSubscribers($dataTable, $entryID, $email = "")
	{
	
		// Suche nach Diskussionsteilnehmern
		$queryRecipients = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . "comments` 
											WHERE `entry_id` = " . (int)$entryID . " 
											AND `notify` = 1 
											AND `table` = '" . $this->DB->escapeString($dataTable) . "' " .
											($email != "" ? "AND `email` != '" . $this->DB->escapeString($email) . "' " : "") . "
											GROUP BY `email` 
											ORDER BY `date` DESC
											");
				
		#die(var_dump($queryRecipients));
	
		return $queryRecipients;
	
	}
	
	

	/**
	 * New Comment-Benachrichtigungen für comment subscriber
	 * 
	 * @param	array	$queryRecipients	query commment subscribers
	 * @param	string	$dataTable			data db table
	 * @param	string	$entryID			entry ID
	 * @access	public
	 * @return	boolean
	 */
	public function notifyCommentSubscribers($queryRecipients, $dataTable, $entryID)
	{
	
		if(!is_array($queryRecipients)
		|| empty($queryRecipients)
		)
			return false;
		
		$mailStatus	= false;
		
		foreach($queryRecipients as $recip) {
		
			$mailStatus	= false;
		
			// ggf. Info-Mail senden
			$recipientEmail	= htmlspecialchars($recip['email']);
			$recipientName	= htmlspecialchars($recip['author']);
			$commentID		= htmlspecialchars($recip['id']);
			$lastComment	= htmlspecialchars(strtotime($recip['date']));
			$articleTitle	= htmlspecialchars(Modules::getDataHeader($this->DB, $entryID, $dataTable, $this->lang));

			// Nachricht
			$htmlMail = "
						<html>
							<head>
								<title>{s_header:newcomment}</title>
								<style type='text/css'>
									.footerText { font-size:small }
								</style>
							</head>
							<body>
								<p>{s_text:hello} $recipientName,</p>
								<p>{s_text:commentnotify} <a href='".PROJECT_HTTP_ROOT."'>" . $this->domain . "</a>:<br />&quot;" . $articleTitle . "&quot;.</p>
								<p>{s_header:newcomment} <strong>".$this->authorName."</strong>.</p>
								<p>&nbsp;</p>
								<p><a href='" . $this->dataAbsPath . "?pageNumC=0#comment-" . $entryID . "'>{s_link:gotocomment}</a></p>
								<p>&nbsp;</p>
								<hr>
								<p class='footerText'>{s_text:commentnofollow}" . $this->domain . "? <a href='" . $this->dataAbsPath . "?comment=nofollow&ci=" . $commentID . "&cd=" . $lastComment . "'>{s_link:clickhere}</a>.</p>
							</body>
						</html>
						\n";
	
			$htmlMail = parent::replaceStaText($htmlMail);
			
			// Klasse phpMailer einbinden
			require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
			require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.smtp.php');
			
			// Instanz von PHPMailer bilden
			$mail = new \PHPMailer();
					
			// E-Mail-Parameter für SMTP
			$mail->setMailParameters(SMTP_MAIL, AUTO_MAIL_AUTHOR, $recipientEmail, $this->mailSubject, $htmlMail, true, "", "smtp");
			
			// E-Mail senden per phpMailer (SMTP)
			$mailStatus = $mail->send();
			
			// Falls Versand per SMTP erfolglos, per Sendmail probieren
			if($mailStatus !== true) {
				
				// E-Mail-Parameter für php Sendmail
				$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $recipientEmail, $this->mailSubject, $htmlMail, true, "", "sendmail");
				
				// Absenderadresse der Email auf FROM: setzen
				#$mail->Sender = $recipientEmail;		
				
				// E-Mail senden per phpMailer (Sendmail)
				$mailStatus = $mail->send();
			}
			// Falls Versand per Sendmail erfolglos, per mail() probieren
			if($mailStatus !== true) {
				
				// E-Mail-Parameter für php mail()
				$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $recipientEmail, $this->mailSubject, $htmlMail, true);
				
				// E-Mail senden per phpMailer (mail())
				$mailStatus = $mail->send();
			}					
			
		}					
		
		return true;

	}
	
	

	/**
	 * Hebt die Registrierung für Benachrichtigungen über neue Kommentare auf
	 * 
	 * @param	string	$dataTable Datenbanktabelle
	 * @access	public
	 * @return	boolean
	 */
	public function unsubscribeNotify($dataTable)
	{
			
		// Kommentar speichern
		
		$dataTable	= $this->DB->escapeString($dataTable);
		$success	= false;
		$notice		= "";
	
		// User ermitteln
		if(isset($GLOBALS['_GET']['ci']))
			$commentID = (int)$GLOBALS['_GET']['ci'];

		if(isset($GLOBALS['_GET']['cd']))
			$date = date("Y-m-d H:i:s", $GLOBALS['_GET']['cd']);
			
				
		// E-Mail des Diskussionsteilnehmers ermitteln
		$queryMail = $this->DB->query("SELECT `email` 
											FROM `" . DB_TABLE_PREFIX . "comments` 
											WHERE `table` = '$dataTable' 
											AND `id` = $commentID 
											AND `date` = '" . $this->DB->escapeString($date) . "' 
											", false);
		#die(var_dump($queryMail));
			
		if(count($queryMail) > 0) {
		
			$email = $this->DB->escapeString($queryMail[0]['email']);
			
			// Update Diskussionsteilnehmer
			$success = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "comments` 
											SET `notify` = 0 
											WHERE `email` = '$email' 
											");
					
			#die(var_dump($success));
			
			if($success	=== true) {
				$this->setSessionVar('notice', "{s_notice:unregnotify}");
				header("Location: ?");
				exit;
			}
		}
		
		return $success;
	}
	

	// getScriptTag
	public function getScriptTag()
	{

		return	'<script>' . "\r\n" .
				'head.ready("jquery", function(){' . "\r\n" .
				'head.load({formvalidator: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/form-validator/jquery.form-validator.min.js"});' . "\r\n" .
				'head.ready("formvalidator", function(){' . "\r\n" .
					'$(document).ready(function(){' . "\r\n" .
						'$.validate({
							form : "#ct_newentry",
							lang : "' . $this->lang . '",
							validateOnBlur : false,
							scrollToTopOnError : false,
							onSuccess : function($form) {
								$form.find(\'button[type="submit"]\').not(".disabled").addClass("disabled").append(\'&nbsp;&nbsp;<span class="icons icon-refresh icon-spin"></span>\');
							}
						});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}

}
