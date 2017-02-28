<?php
namespace Concise;


###################################################
##################  ListPages  ####################
###################################################

// Listen erstellen

class ListPages extends Admin
{

	private $action				= "";
	private $editID				= "";
	private $editIdDB			= "";
	private $area				= "";

	
	public function __construct($DB, $o_lng, $themeType)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);

		// Theme-Setup
		$this->getThemeDefaults($themeType);
	
	}
	
	public function conductAction()
	{

		// Edit-Id
		if(isset($_GET['edit_id']) && $_GET['edit_id'] != "") {
			$this->editID		= $_GET['edit_id'];
			$this->editIdDB		= $this->DB->escapeString($this->editID);
		}

		// Edit-TplArea
		if(isset($_GET['area']) && $_GET['area'] != "") {
			$this->area			= $_GET['area'];
			$aArr				= explode("_", $this->area);
			$this->editTplArea	= end($aArr);
		}

			
		// Falls Seiten online/offline geschaltet werden sollen
		if(isset($_GET['online']) && $_GET['online'] != "") {
		
			return $this->setPageStatus($_GET['online']);
		
		}


		// Falls Inhalte von einer anderen Seite übernommen werden sollen, Inhalte kopieren (überschreiben)
		if(isset($_GET['fetchid']) && $_GET['fetchid'] != "") {
		
			return $this->fetchPageContents($_GET['fetchid']);
		
		}

		
		// Seiten auflisten (z.B. zum Übernehmen von Inhalten einer anderen Seite)
		if(isset($_GET['type']) && $_GET['type'] != "") {
		
			return $this->generatePageList($_GET['type']);
		
		}

	}
	
	
	// setPageStatus
	public function setPageStatus($online)
	{
		
		$sqlStr		= "";
		$isArray	= false;
		
		if(isset($GLOBALS['_GET']['array']) && isset($GLOBALS['_GET']['items'])) {
			$pubItems	= explode(",", $GLOBALS['_GET']['items']);
			$isArray	= true;
		}
		else
			$pubItems	= array($this->editID);
		
		if(count($pubItems) == 0) {
			echo "0";
			exit;
		}		
		
		$online 		= (int)$online;
		$tablePages		= $GLOBALS['DB']->escapeString(DB_TABLE_PREFIX . $GLOBALS['tablePages']);
		
		if($online)
			$online = 1;
		else
			$online = 0;
		
		
		foreach($pubItems as $pubItem) {
		
			$pubItem	= $GLOBALS['DB']->escapeString($pubItem);
			$sqlStr .= "`page_id` = '" . $pubItem . "' OR ";
		}
		
		$sqlStr = substr($sqlStr, 0, -4);
			
		// Aktualisieren des Dateneintrags
		$updateSQL = $GLOBALS['DB']->query("UPDATE $tablePages 
											SET `published` = $online 
											WHERE $sqlStr
											");

		
		// Falls mehrere Seiten
		if($isArray) {
			require_once PROJECT_DOC_ROOT . "/inc/classes/Admin/interface.AdminTask.php"; // AdminTask-Interface einbinden
			require_once SYSTEM_DOC_ROOT . "/inc/admintasks/edit/admin_edit.inc.php"; // Admin-Task einbinden
			$adminTask				= 'edit';
			$adminE					= new Admin_Edit($GLOBALS['DB'], $GLOBALS['o_lng'], $adminTask);
			// Theme-Setup
			$adminE->getThemeDefaults("admin");
			$adminE->notice = '{s_notice:' . ($online ? '' : 'un') . 'pubpages}';
			$ajaxOutput	= $adminE->getTaskContents(true);
			echo parent::replaceStaText($ajaxOutput);					
			exit;
		}
		
		return $updateSQL;
	
	}
	
	
	// fetchPageContents
	public function fetchPageContents($fetchId)
	{

		if(empty($fetchId))
			return false;
		
		
		$tableContents			= parent::$tableContents;
		$tableContentsTarget	= $tableContents;
		
		// Falls Templateinhalte übernommen werden sollen
		if(isset($_GET['tpl']) && $_GET['tpl'] == 1) {
		
			$fetchTpl		= explode("_FID_", $fetchId);
			$tableContents	= "contents_" . $fetchTpl[1];
			$fetchId		= $fetchTpl[0];
		}
		
		$fetchIdDB	= $this->DB->escapeString($fetchId);
		
		
		// Redirect festlegen
		if(is_numeric($this->editID))
			$redExt					= "edit_id=$this->editID"; // Seite
		else {
			$tableContentsTarget	= $this->area;
			$redExt					= "edit_tpl=$this->editID&edit_area=$this->area"; // Template
		}
		
		$baseTable					= $tableContents;
		$baseTableTarget			= $tableContentsTarget;
		$tableContents				= $this->DB->escapeString(DB_TABLE_PREFIX . $baseTable);
		$tableContentsPreview		= $this->DB->escapeString(DB_TABLE_PREFIX . $baseTable . "_preview");
		$tableContentsTarget		= $this->DB->escapeString(DB_TABLE_PREFIX . $baseTableTarget);
		$tableContentsTargetPreview	= $this->DB->escapeString(DB_TABLE_PREFIX . $baseTableTarget . "_preview");
		
		
		// Datensatz kopieren
		// db-Tabelle sperren
		$lock = $GLOBALS['DB']->query("LOCK TABLES  `" . $tableContents . "`,
													`" . $tableContentsPreview . "`,
													`" . $tableContentsTarget . "`
									");


		// Kopieren des Datensatzes
		$createSQL = $GLOBALS['DB']->query("DROP TABLE IF EXISTS `" . DB_TABLE_PREFIX . "con_temp`");
		
		$createSQL = $GLOBALS['DB']->query("CREATE TABLE `" . DB_TABLE_PREFIX . "con_temp` ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci 
											 SELECT *
											 FROM `" . $tableContentsPreview . "` 
											 WHERE `page_id` = '$fetchIdDB'
											 ");

		$updateSQL1a = $GLOBALS['DB']->query("SHOW COLUMNS FROM `" . DB_TABLE_PREFIX . "con_temp`");	
		$updateSQL1b = $GLOBALS['DB']->query("SHOW COLUMNS FROM `" . $tableContentsTargetPreview . "`");
		
		// Spaltenanzahl der Inhaltstabelle (db) ermitteln
		$colCount 		= $this->getConNumber($tableContentsPreview);
		$colCountTarget	= $this->getConNumber($tableContentsTargetPreview);
		
		$extendTab = "";
		
		// Falls Spaltenzahl der Zieltabelle größer als zu kopierende
		if($colCount < $colCountTarget) {
			
			$colDiff = $colCountTarget - $colCount;
			
			for($i = 1; $i <= $colDiff; $i++) {
				
				foreach($this->o_lng->installedLangs as $conLang) {
					$extendTab .= "ADD `con" . ($colCount + $i) . "_" . $conLang . "` MEDIUMTEXT NOT NULL,";
				}
				$extendTab .= "ADD `type-con" . ($colCount + $i) . "` VARCHAR(50) NOT NULL,";
				$extendTab .= "ADD `styles-con" . ($colCount + $i) . "` TEXT NOT NULL,";
			}
			$extendTab = substr($extendTab, 0, -1);
			$updateSQL2 = $GLOBALS['DB']->query("ALTER TABLE `" . DB_TABLE_PREFIX . "con_temp` $extendTab");
		}
		
		// Falls Spaltenzahl der Zieltabelle größer als zu kopierende
		if($colCount > $colCountTarget) {
			
			$colDiff = $colCount - $colCountTarget;
			
			for($i = 1; $i <= $colDiff; $i++) {
				
				foreach($this->o_lng->installedLangs as $conLang) {
					$extendTab .= "ADD `con" . ($colCountTarget + $i) . "_" . $conLang . "` MEDIUMTEXT NOT NULL,";
				}
				$extendTab .= "ADD `type-con" . ($colCountTarget + $i) . "` VARCHAR(50) NOT NULL,";
				$extendTab .= "ADD `styles-con" . ($colCountTarget + $i) . "` TEXT NOT NULL,";
			}
			$extendTab = substr($extendTab, 0, -1);
			
			$updateSQL2a = $GLOBALS['DB']->query("ALTER TABLE `" . $tableContentsTarget . "` $extendTab");
			$updateSQL2b = $GLOBALS['DB']->query("ALTER TABLE `" . $tableContentsTargetPreview . "` $extendTab");
		}
		
		// Feld page_id kompatibel für varchar machen
		$updateSQL3a = $GLOBALS['DB']->query("ALTER TABLE `" . DB_TABLE_PREFIX . "con_temp` 
												CHANGE `page_id` `page_id` VARCHAR(50) NOT NULL
											 ");
		
		$updateSQL3b = $GLOBALS['DB']->query("UPDATE `" . DB_TABLE_PREFIX . "con_temp` 
												 SET `page_id` = '$this->editIdDB'
											 ");
		#die($updateSQL3a);

		$updateSQL4 = $GLOBALS['DB']->query("REPLACE INTO `" . $tableContentsTargetPreview . "` 
											 SELECT *
											 FROM `" . DB_TABLE_PREFIX . "con_temp` 
											 WHERE `page_id` = '$this->editIdDB'
											 ");
		
		$updateSQL5 = $GLOBALS['DB']->query("DROP TABLE `" . DB_TABLE_PREFIX . "con_temp`
											");
		
		// db-Sperre aufheben
		$unLock = $GLOBALS['DB']->query("UNLOCK TABLES");
		
		
		// Meldung in Session speichern
		if($updateSQL3a === true
		&& $updateSQL3b === true
		&& $updateSQL4 === true
		&& $updateSQL5 === true
		)
			$GLOBALS['_SESSION']['notice']	= "{s_notice:fetchcon}";
		else
			$GLOBALS['_SESSION']['error']	= "{s_error:error}";
		
		header("location: " . ADMIN_HTTP_ROOT . "?task=" . (strpos($baseTableTarget, "main") !== false ? 'edit' : 'tpl') . "&" . $redExt);
		exit;
		
	}
	
	
	// generatePageList
	public function generatePageList($type)
	{

		if(empty($type))
			return false;
		

		$controlBarType = "date";
		
		if($type == "articles"
		|| $type == "news"
		|| $type == "planner"
		)
			$controlBarType = "id";
		if($type == "fetchcon"
		|| $type == "link"
		)
			$controlBarType = "none";
			
		// Button close
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'closeListBox close button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:close}',
								"icon"		=> "close"
							);
		
		$adminContent  =	ContentsEngine::getButton($btnDefs);
		
		$adminContent .=	'<h2 class="listBoxHeader cc-section-heading cc-h2">{s_header:list'.$type.'}</h2>' . "\r\n";
		$adminContent .=	$this->getControlBar($controlBarType);
		
		$adminContent .=	'<div class="listItemBox ' . $type . '">' . "\r\n";
		
		
		// Seiten für Linkauswahl auflisten
		if($type == "link")	{
			$adminContent .=	$this->listPages("links") . "\r\n";	
			$adminContent .=	$this->listPages("links", 2) . "\r\n";
			$adminContent .=	$this->listPages("links", 3) . "\r\n";
			$adminContent .=	$this->listPages("links", 0) . "\r\n";	
		}
		
		// Seiten für Inhaltsübernahme auflisten
		if($type == "fetchcon")	 {
			
			if(!isset($_GET['tpl'])) { // Falls kein Template
				$adminContent .=	$this->listPages("fetchcon") . "\r\n";	
				$adminContent .=	$this->listPages("fetchcon", 2) . "\r\n";
				$adminContent .=	$this->listPages("fetchcon", 3) . "\r\n";
				$adminContent .=	$this->listPages("fetchcon", 0) . "\r\n";
			}
			else {
				
				// Templates des aktuellen Themes einlesen
				$tplPath = PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR;
				
				$handle = opendir($tplPath);
				
				while($content = readdir($handle)) {
					
					if( $content != ".." && 
						strpos($content, ".") !== 0 && 
						$content != "index.tpl" && 
						!is_dir($tplPath . $content)
					) {
						$adminContent .=	$this->listPages("fetchcon", $content) . "\r\n";	
					}
				}
				closedir($handle);
			}
		}
		
		// Galerien auflisten
		if($type == "gallery")	 {
			
			$existGall	= array();
			$gallDates	= array();
			$gallPreview	= array();
			$noGall		= "";
			
			require_once(PROJECT_DOC_ROOT . "/inc/classes/Modules/class.Gallery.php"); // Galleryklasse einbinden
		
			// Gallerieordner ins Array einlesen
			if(is_dir(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER)) {
			
				$handle = opendir(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER);
			
				while($content = readdir($handle)) {
					if( $content != ".." && 
						strpos($content, ".") !== 0 && 
						is_dir(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $content)
					) {
						$filedate		= filemtime(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $content);
						$o_gallery		= new Gallery($this->DB, $this->o_lng->lang, $content, "slimbox", "", 0, 0, 0, 5);
						$existGall[]	= $content;
						$gallDates[]	= $filedate;
						$gallPreview[]	= $o_gallery->getGallery();
					}
				}
				closedir($handle);
				
				if(count($existGall) == 0)
					$noGall = "{s_text:folder} &quot;galleries&quot; {s_text:empty}.";
			}
			else
				$noGall = "{s_text:folder} &quot;galleries&quot; {s_text:notexist}.";
			
			$j = 0;
			
			if(count($existGall) > 0) {

				$adminContent .=		'<ul class="editList">' . "\r\n";
					
				foreach($existGall as $gallery) {
					
					$adminContent .= 	'<li class="gallList ' . (is_numeric($gallery[0]) ? '0-9' : strtoupper($gallery[0])) . ' date-' . $gallDates[$j] . '">' . "\r\n";
					$adminContent .= 	'<span class="editButtons-panel">' . "\r\n";
				
					// Button fetch
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'link button-icon-only',
											"value"		=> $gallery,
											"text"		=> "",
											"title"		=> $gallery . ' -> {s_common:fetch}',
											"icon"		=> "fetch"
										);
					
					$adminContent .=	ContentsEngine::getButton($btnDefs);
										
					$adminContent .=	'</span>' . "\r\n";
					
					$adminContent .=	'<span class="galleryName">' . "\r\n" . 
										$gallery . 
										'</span>' . "\r\n" .
										$gallPreview[$j] .
										'</li>' . "\r\n";
					
					$j++;
				}
			
				$adminContent .=	'</ul>' . "\r\n";
			}
			else
				$adminContent .=	'<p class="{t_class:alert} {t_class:error}">' . $noGall . '</p>';
			
			$adminContent .=	'</div>' . "\r\n"; // listItemBox schließen
		}
		
		
		
		// Module bearbeiten
		if($type == "articles"
		|| $type == "news"
		|| $type == "planner"
		|| $type == "feed"
		|| $type == "newsl"
		|| $type == "comments"
		|| $type == "gbook"
		) {
			
			$restrict = "";
			$queryExt1	= "";
			$queryExt2	= "";
			
			switch($type) {
			
				case "articles":
					$dataTable	= DB_TABLE_PREFIX . "articles";
					$catTable	= DB_TABLE_PREFIX . "articles_categories";
					$catLabel	= "allgroups";
					break;
					
				case "news":
					$dataTable	= DB_TABLE_PREFIX . "news";
					$catTable	= DB_TABLE_PREFIX . "news_categories";
					$catLabel	= "allcats";
					break;
					
				case "planner":
					$dataTable	= DB_TABLE_PREFIX . "planner";
					$catTable	= DB_TABLE_PREFIX . "planner_categories";
					$catLabel	= "allcats";
					break;
				
				case "feed":
					$catTable	= DB_TABLE_PREFIX . "news_categories";
					$catLabel	= "allfeeds";
					$restrict	= "WHERE newsfeed > 0 ";
					break;
					
				case "newsl":
					$dataTable	= DB_TABLE_PREFIX . "newsletter";
					break;
				
				case "comments":
					$dataTable	= DB_TABLE_PREFIX . "comments";
					break;
					
				case "gbook":
					$dataTable	= DB_TABLE_PREFIX . "gbook";
					break;
					
			}

			// Newskategorien für Übername im Edit-Bereich auflisten
			// db-Query nach Newskategorien
			$existCats = $GLOBALS['DB']->query("SELECT `cat_id`, `parent_cat`, `category_" . $this->editLang . "` 
												FROM `$catTable` 
												$restrict
												ORDER BY `sort_id` ASC
												", false);
			#var_dump($existCats);
			
			$adminContent .=		'<ul class="editList">' . "\r\n" .
									'<li class="listEntry A date-0">' . "\r\n" .
									'<span class="listNr tableCell">[*]</span>' . "\r\n" .
									'<span class="pageTitle tableCell">&lt;{s_text:'.$catLabel.'}&gt;</span>' . "\r\n" . 
									'<span class="editButtons-panel">' . "\r\n";
				
			// Button fetch
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'fetchCat button-icon-only',
									"value"		=> '<all>',
									"title"		=> '{s_text:'.$catLabel.'} -> {s_title:fetchcat}',
									"text"		=> '',
									"attr"		=> 'data-catname="&lt;{s_text:'.$catLabel.'}&gt;"',
									"icon"		=> "fetch"
								);
			
			$adminContent .=	ContentsEngine::getButton($btnDefs);
							
			$adminContent .=		'</span>' . "\r\n" .
									'</li>' . "\r\n";
			
			$j = 0;
			
			foreach($existCats as $cat) {
				
				$j++;
				
				$childTag = "";
				$parCat = $cat['parent_cat'];
				
				while($parCat > 0) { // Elternkat. auslesen zur Markierung der Kindlevels
					$childTag .= '&#746; ';
				
					$pCatID = (int)$GLOBALS['DB']->escapeString($parCat);
				
					// db-Query nach allen data
					$queryParentCat = $GLOBALS['DB']->query("SELECT * 
																FROM `$catTable` 
																WHERE 
																`cat_id` = $pCatID 
																AND `parent_cat` > 0
																", false);
				
					#var_dump($queryParentCat);
					if(count($queryParentCat) > 0)
						$parCat = $queryParentCat[0]['parent_cat'];
					else
						$parCat = 0;
				}
				
				$adminContent .= 	'<li class="listEntry ' . ($childTag == "" ? 'parentCat ' : '') . (is_numeric($cat['category_' . $this->editLang][0]) ? '0-9' : strtoupper($cat['category_' . $this->editLang][0])) . ' date-' . $cat['cat_id'] . '">' . "\r\n" .
									'<span class="listNr tableCell">[#' . $cat['cat_id'] . ']</span>' . "\r\n" .
									'<span class="pageTitle tableCell">' . $childTag . $cat['category_' . $this->editLang] . '</span>' . "\r\n";
				
				$adminContent .= 	'<span class="editButtons-panel">' . "\r\n";
				
				// Button fetch
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'fetchCat button-icon-only',
										"value"		=> $cat['cat_id'],
										"title"		=> '{s_title:fetchcat} -> ' . $cat['category_' . $this->editLang],
										"text"		=> '',
										"attr"		=> 'data-catname="' . $cat['category_' . $this->editLang] . '"',
										"icon"		=> "fetch"
									);
				
				$adminContent .=	ContentsEngine::getButton($btnDefs);
									
				$adminContent .= 	'</span>' . ($childTag != "" ? '<span class="pageID"><i>({s_text:childof} #' . $cat['parent_cat'] . ')</i>' : '') . '</span></li>' . "\r\n";
									
			} // Ende foreach
			
			$adminContent .=		'</ul>' . "\r\n";
			
			
		} // Ende Module bearbeiten
		
		#var_dump($adminContent);
		$adminContent = ContentsEngine::replaceStaText($adminContent);
		$adminContent = ContentsEngine::replaceStyleDefs($adminContent);
		
		// Inhalte ausgeben (als ajax content)
		echo "$adminContent";

		$scriptExt		= "";
		$loadHead		= "";
		$loadHeadClose	= "";
		
		if($this->headJS) {
			$loadHead		= "head.ready(function(){\r\n";
			$loadHeadClose	= "});";
		}
			
		?>	
		<script type="text/javascript">	
		<?php
			echo $loadHead;
		?>
		
			$(document).ready(function() {

		<?php
			echo $scriptExt;	
		?>
		
			// Seitenbaum einklappen	
			$('.pageList ul').hide();
		
			}); // Ende ready function
		
		<?php
			echo $loadHeadClose; // Ende head ready function
		?>
		</script>
		<?php
		exit;
	
	} // Ende elseif type

}
