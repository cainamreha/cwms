<?php
namespace Concise;


##############################
#########  Termine  ##########
##############################


/**
 * PlannerConfigElement class
 * 
 * content type => planner
 */
class PlannerConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $newsCatName		= "";
	public $dbUpdatePlannerCat = "";

	/**
	 * Gibt ein PlannerConfigElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles)
	 * @param	string	$DB			DB-Objekt
	 * @param	string	$o_lng		Sprach-Objekt
	 */
	public function __construct($options, $DB, &$o_lng)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
	
	}

	
	public function getConfigElement($a_POST)
	{

		$this->a_POST	= $a_POST;
		$this->params	= explode("<>", $this->conValue);
		
		
		// Parameter (default) setzen
		$this->setParams();

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
		
		
		// DB-Updatestr generieren
		$this->makeUpdateStr();

		
		// Element-Formular generieren
		$this->output		= $this->getCreateElementHtml();
		
		
		// Ausgabe-Array erstellen und zurückgeben
		$configArr							= $this->makeOutputArray();
		$configArr['dbUpdatePlannerCat']	= $this->dbUpdatePlannerCat;
		
		return $configArr;

	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params[0] = $this->a_POST[$this->conPrefix];
			$this->params[1] = $this->a_POST[$this->conPrefix . '_news'];
			$this->params[2] = $this->a_POST[$this->conPrefix . '_newsTargetID'];
			$this->params[3] = $this->a_POST[$this->conPrefix . '_limit'];
			$this->params[4] = $this->a_POST[$this->conPrefix . '_sort'];
			$this->newsCatName = $this->a_POST[$this->conPrefix . '_catname'];
				
			if(isset($this->a_POST[$this->conPrefix . '_comments']) && $this->a_POST[$this->conPrefix . '_comments'] == "on")
				$this->params[5] = 1;
			else
				$this->params[5] = 0;
			
			if(isset($this->a_POST[$this->conPrefix . '_rating']) && $this->a_POST[$this->conPrefix . '_rating'] == "on")
				$this->params[6] = 1;
			else
				$this->params[6] = 0;
			
			if(isset($this->a_POST[$this->conPrefix . '_ratingGroup']))
				$this->params[7] = $this->a_POST[$this->conPrefix . '_ratingGroup'];
			if(isset($this->a_POST[$this->conPrefix . '_readCommentsGroup']))
				$this->params[8] = $this->a_POST[$this->conPrefix . '_readCommentsGroup'];
			if(isset($this->a_POST[$this->conPrefix . '_writeCommentsGroup']))
				$this->params[9] = $this->a_POST[$this->conPrefix . '_writeCommentsGroup'];
			if(isset($this->a_POST[$this->conPrefix . '_altTpl']))
				$this->params[10] = $this->a_POST[$this->conPrefix . '_altTpl'];
			
									
			if($this->newsCatName != ContentsEngine::replaceStaText("<{s_text:allcats}>") && $this->newsCatName != "") { // Falls ein Kategoriename eingegeben wurde, überprüfen ob dieser bereits existiert
				
				$newsCatNameDb = $this->DB->escapeString($this->newsCatName);
				
				// db-Query nach Anzahl vorhandener Newskategorien
				$existCatName = $this->DB->query( "SELECT *  
														FROM `" . DB_TABLE_PREFIX . "planner_categories` 
														WHERE `category_" . $this->editLang . "` = '$newsCatNameDb'
														", false);
				#var_dump($existCatName);
				
				if(count($existCatName) > 0) {
					$this->newsCatName = $existCatName[0]['category_' . $this->editLang];
					$this->params[0] = $existCatName[0]['cat_id'];
				}
					
			}
			
			elseif($this->newsCatName == "") { // Falls kein Kategoriename eingegeben wurde
				$this->wrongInput[] = $this->conPrefix;
				$this->error =  "{s_error:choosecat}";
			}
			
			elseif($this->newsCatName == "<{s_text:allcats}>") { // Falls kein Kategoriename eingegeben wurde
				
			}
			
			if(isset($existCatName) && count($existCatName) == 0) { // Falls das Katfeld nicht leer ist, auf vorhandene Kat mit diesem Namen prüfen (db-Query starten)
					$this->wrongInput[] = $this->conPrefix;
					$this->error =  "{s_error:newcat}";
			}
		
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$catID			= $this->DB->escapeString(trim($this->params[0]));
		$newsType		= $this->DB->escapeString(trim($this->params[1]));
		$newsTarget		= $this->DB->escapeString((int)trim($this->params[2]));
		$ratingG		= $this->DB->escapeString(implode(",", $this->params[7]));
		$readCommentsG	= $this->DB->escapeString(implode(",", $this->params[8]));
		$writeCommentsG	= $this->DB->escapeString(implode(",", $this->params[9]));
		$altTpl			= $this->DB->escapeString(trim($this->params[10]));
													  
		$this->dbUpdateStr = "'" . $catID . "<>" . $newsType . "<>" . $newsTarget . "<>" . $this->DB->escapeString($this->params[3]) . "<>" . $this->DB->escapeString($this->params[4]) . "<>" . $this->params[5] . "<>" . $this->params[6] . "<>" . $ratingG . "<>" . $readCommentsG . "<>" . $writeCommentsG . "<>" . $altTpl . "',";
		

		// Falls Listentyp, Zielseite für die Kategorie (Tabelle planner_categories) übernehmen
		if($this->params[1] == "list"
		|| $this->params[1] == "teaser"
		|| $this->params[1] == "catteaser"
		) {
		
			if($catID == "<all>")
				$dbFilter = "";
			else
				$dbFilter = " WHERE `cat_id` = $catID OR `parent_cat` = $catID";
			$this->dbUpdatePlannerCat = "`target_page` = '$newsTarget'" . $dbFilter;
		}

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params[0]))
			$this->params[0] = "";
		if(!isset($this->params[1]))
			$this->params[1] = "";
		if(!isset($this->params[2]))
			$this->params[2] = ($this->isTemplateArea ? 1 : $this->editId);
		if(!isset($this->params[3]))
			$this->params[3] = 20;
		if(!isset($this->params[4]))
			$this->params[4] = "dateasc";
		if(!isset($this->params[5]))
			$this->params[5] = 0;
		if(!isset($this->params[6]))
			$this->params[6] = 0;
		if(!isset($this->params[7]))
			$this->params[7] = array("public");
		else
			$this->params[7] = explode(",", $this->params[7]);
		if(!isset($this->params[8]))
			$this->params[8] = array("public");
		else
			$this->params[8] = explode(",", $this->params[8]);
		if(!isset($this->params[9]))
			$this->params[9] = array("public");
		else
			$this->params[9] = explode(",", $this->params[9]);
		if(!isset($this->params[10]))
			$this->params[10] = "";


		if($this->params[0] == "<all>")
			$this->newsCatName = "<{s_text:allcats}>";							

		elseif(!isset($this->a_POST[$this->conPrefix])) { // Falls das Formular NICHT abgeschickt wurde
			
			$catID = $this->DB->escapeString($this->params[0]);
			
			// db-Query nach Anzahl vorhandener Newskategorien
			$existCats = $this->DB->query("SELECT *  
												FROM `" . DB_TABLE_PREFIX . "planner_categories` 
												WHERE `cat_id` = '$catID'
												", false);
			#var_dump(($existCats));
			
			if(count($existCats) > 0)
				$this->newsCatName = $existCats[0]['category_' . $this->editLang];
		}
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;

		// Button goto planner
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=planner&list_cat=',
								"class"		=> "modLink button-icon-only right",
								"text"		=> " &raquo;",
								"title"		=> "{s_title:gotoplanner}",
								"icon"		=> "planner"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);


		// Planner cats MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "planner category",
											"type"		=> "planner",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=planner",
											"value"		=> "{s_button:plannerchoose}",
											"icon"		=> "planner"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<label>{s_label:catname}</label>' . PHP_EOL;
						
		if(in_array($this->conPrefix, $this->wrongInput)) { // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . PHP_EOL . 
						'<a href="' . ADMIN_HTTP_ROOT . '?task=modules&type=planner&cat_name=' . htmlspecialchars($this->newsCatName) . '">{s_link:newcat}</a></span>' . PHP_EOL;

			// Ggf. data_id aus Session löschen
			$this->unsetSessionKey($this->conType . '_id');
		}

		$output	.=	'<input type="text" name="' . $this->conPrefix . '_catname" value="' . htmlspecialchars($this->newsCatName) . '" />' . PHP_EOL . 
					'<input type="hidden" name="' . $this->conPrefix . '" value="' . htmlspecialchars($this->params[0]) . '" />' . PHP_EOL;
		
		$output	.=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="optionsBar">' . PHP_EOL;
		
		$output	.=	'<div class="optionsBar">' . PHP_EOL . 
					'<div class="left"><label>{s_label:plannertype}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_news">' . PHP_EOL . 
					'<option value="list"' . ((isset($this->params[1]) && $this->params[1] == "list") ? " selected=\"selected\"" : "") . '>{s_option:plannerlist}</option>' . PHP_EOL . 
					'<option value="latest"' . ((isset($this->params[1]) && $this->params[1] == "latest") ? " selected=\"selected\"" : "") . '>{s_option:latestplanner}</option>' . PHP_EOL . 
					'<option value="expired"' . ((isset($this->params[1]) && $this->params[1] == "expired") ? " selected=\"selected\"" : "") . '>{s_option:expiredplanner}</option>' . PHP_EOL . 
					'<option value="teaser"' . ((isset($this->params[1]) && $this->params[1] == "teaser") ? " selected=\"selected\"" : "") . '>Teaser</option>' . PHP_EOL . 
					'<option value="catmenu"' . ((isset($this->params[1]) && $this->params[1] == "catmenu") ? " selected=\"selected\"" : "") . '>{s_option:catmenu}</option>' . PHP_EOL . 
					'<option value="archive"' . ((isset($this->params[1]) && $this->params[1] == "archive") ? " selected=\"selected\"" : "") . '>{s_option:plannerarchive}</option>' . PHP_EOL . 
					'<option value="calendar"' . ((isset($this->params[1]) && $this->params[1] == "calendar") ? " selected=\"selected\"" : "") . '>{s_option:calendarplanner}</option>' . PHP_EOL . 
					'<option value="detail"' . ((isset($this->params[1]) && $this->params[1] == "detail") ? " selected=\"selected\"" : "") . '>{s_option:plannerdetail}</option>' . PHP_EOL . 
					'<option value="related"' . ((isset($this->params[1]) && $this->params[1] == "related") ? " selected=\"selected\"" : "") . '>{s_option:relatedplanner}</option>' . PHP_EOL . 
					'<option value="popular"' . ((isset($this->params[1]) && $this->params[1] == "popular") ? " selected=\"selected\"" : "") . '>{s_option:popularplanner}</option>' . PHP_EOL . 
					'<option value="rated"' . ((isset($this->params[1]) && $this->params[1] == "rated") ? " selected=\"selected\"" : "") . '>{s_option:ratinglist}</option>' . PHP_EOL . 
					'<option value="random"' . ((isset($this->params[1]) && $this->params[1] == "random") ? " selected=\"selected\"" : "") . '>{s_option:randomplanner}</option>' . PHP_EOL . 
					'</select></div>' . PHP_EOL .
					'<div class="sortOption small left"><label>{s_label:limit}</label>' . PHP_EOL .
					'<select name="' . $this->conPrefix . '_limit">' . PHP_EOL;
		
		for($lim = 1; $lim < 11; $lim++){
			
			$output	.=	'<option value="'.$lim.'"' . ((isset($this->params[3]) && $this->params[3] == $lim) ? " selected=\"selected\"" : "") . '>'.$lim.'</option>' . PHP_EOL;
		}
		
		$output	.=	'<option value="15"' . ((isset($this->params[3]) && $this->params[3] == 15) ? " selected=\"selected\"" : "") . '>15</option>' . PHP_EOL .
					'<option value="20"' . ((isset($this->params[3]) && $this->params[3] == 20) ? " selected=\"selected\"" : "") . '>20</option>' . PHP_EOL .
					'<option value="50"' . ((isset($this->params[3]) && $this->params[3] == 50) ? " selected=\"selected\"" : "") . '>50</option>' . PHP_EOL .
					'<option value="100"' . ((isset($this->params[3]) && $this->params[3] == 100) ? " selected=\"selected\"" : "") . '>100</option>' . PHP_EOL .
					'</select></div>' . PHP_EOL;
		
		$output	.=	'<div class="sortOption small left"><label>{s_label:sort}</label>' . PHP_EOL .
					'<select name="' . $this->conPrefix . '_sort">' . PHP_EOL .
					'<option value="dateasc"' . ((isset($this->params[4]) && $this->params[4] == "dateasc") ? " selected=\"selected\"" : "") . '>{s_option:dateasc}</option>' . PHP_EOL .
					'<option value="datedsc"' . ((isset($this->params[4]) && $this->params[4] == "datedsc") ? " selected=\"selected\"" : "") . '>{s_option:datedsc}</option>' . PHP_EOL .
					'<option value="nameasc"' . ((isset($this->params[4]) && $this->params[4] == "nameasc") ? " selected=\"selected\"" : "") . '>{s_option:nameasc}</option>' . PHP_EOL .
					'<option value="namedsc"' . ((isset($this->params[4]) && $this->params[4] == "namedsc") ? " selected=\"selected\"" : "") . '>{s_option:namedsc}</option>' . PHP_EOL .
					'<option value="sortid"' . ((isset($this->params[4]) && $this->params[4] == "sortid") ? " selected=\"selected\"" : "") . '>{s_option:sortid}</option>' . PHP_EOL .
					'</select></div>' . PHP_EOL .
					'<div class="filterOption small left"><label for="' . $this->conPrefix . '_comments">{s_label:comments}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_comments" id="' . $this->conPrefix . '_comments"' . ($this->params[5] == "1" ? ' checked="checked"' : '') . ' class="checkComments" /></label></div>' . PHP_EOL .
					'<div class="filterOption small left"><label for="' . $this->conPrefix . '_rating">{s_label:rating}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_rating" id="' . $this->conPrefix . '_rating"' . ($this->params[6] == "1" ? ' checked="checked"' : '') . ' class="checkRating" /></label></div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
		
		$output	.=	'</div>' . PHP_EOL;


		// Kommentar Lese-Berechtigung
		$output	.=	'<div class="selgroupBox comments"' . ($this->params[5] == "0" ? ' style="display:none;"' : '') . '><label>{s_label:readcommentsgroup}</label>' . PHP_EOL .
					'<select multiple="multiple" size="' . count($this->userGroups) . '" name="' . $this->conPrefix . '_readCommentsGroup[]" class="selgroup">' . PHP_EOL;

		// Benutzergruppe auflisten
		foreach($this->userGroups as $group) {
			$output	.=	'<option value="' . $group . '"' . (in_array($group, $this->params[8]) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
		}
			
		$output	.=	'</select></div>' . PHP_EOL;
						 
		// Kommentar Schreib-Berechtigung
		$output	.=	'<div class="selgroupBox comments"' . ($this->params[5] == "0" ? ' style="display:none;"' : '') . '><label>{s_label:writecommentsgroup}</label>' . PHP_EOL .
					'<select multiple="multiple" size="' . count($this->userGroups) . '" name="' . $this->conPrefix . '_writeCommentsGroup[]" class="selgroup">' . PHP_EOL;

		// Benutzergruppe auflisten
		foreach($this->userGroups as $group) {
			$output	.=	'<option value="' . $group . '"' . (in_array($group, $this->params[9]) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
		}
			
		$output	.=	'</select></div>' . PHP_EOL;					


		// Rating Berechtigung
		$output	.=	'<div class="selgroupBox rating"' . ($this->params[6] == "0" ? ' style="display:none;"' : '') . '><label>{s_label:ratinggroup}</label>' . PHP_EOL .
					'<select multiple="multiple" size="' . count($this->userGroups) . '" name="' . $this->conPrefix . '_ratingGroup[]" class="selgroup">' . PHP_EOL;

		// Benutzergruppe auflisten
		foreach($this->userGroups as $group) {
			$output	.=	'<option value="' . $group . '"' . (in_array($group, $this->params[7]) ? ' selected="selected"' : '') . '>' . (in_array($group, $this->systemUserGroups) ? '{s_option:group' . $group . '}' : $group) . '</option>' . PHP_EOL; // Benutzergruppe
		}
			
		$output	.=	'</select></div>' . PHP_EOL;
		
		$output	.=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="targetPage">' . PHP_EOL;						 

		// Zielseite
		$output	.=	'<div class="fieldBox cc-box-info right"><label>{s_label:targetpage}' . PHP_EOL . 
					parent::getIcon("info", "editInfo", 'title="{s_title:targetpage}"') .
					'</label></div>' . PHP_EOL;
		
		// targetPage MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "targetPage",
											"type"		=> "targetPage",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"slbclass"	=> "target",
											"value"		=> "{s_button:targetPage}",
											"icon"		=> "page"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<label>{s_button:targetPage}</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_newsTarget" class="targetPage" value="' . htmlspecialchars(HTML::getLinkPath($this->params[2], "editLang", false)) . '" readonly="readonly" />' . PHP_EOL .
					'<input type="hidden" name="' . $this->conPrefix . '_newsTargetID" class="targetPageID" value="' . htmlspecialchars($this->params[2]) . '" />' . PHP_EOL;


		// Alternatives Template
		$output	.=	'<label>{s_label:alttpl}</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_altTpl" value="' . htmlspecialchars($this->params[10]) . '" />' . PHP_EOL;
		
		$output	.=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
