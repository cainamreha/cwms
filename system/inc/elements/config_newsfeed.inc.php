<?php
namespace Concise;


##############################
########  Newsfeed  ##########
##############################

/**
 * NewsfeedConfigElement class
 * 
 * content type => newsfeed
 */
class NewsfeedConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $feedCatName		= "";

	/**
	 * Gibt ein NewsfeedConfigElement zurück
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
		return $this->makeOutputArray();

	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params[0]	= $this->a_POST[$this->conPrefix];
			$this->params[1]	= $this->a_POST[$this->conPrefix . '_type'];
			$this->params[2]	= $this->a_POST[$this->conPrefix . '_feedTargetID'];
			$this->feedCatName	= $this->a_POST[$this->conPrefix . '_feedname'];
				
			
			if($this->feedCatName != ContentsEngine::replaceStaText("<{s_text:allfeeds}>") && $this->feedCatName != "") { // Falls ein Kategoriename eingegeben wurde, überprüfen ob dieser bereits existiert
				
				$feedCatNameDb = $this->DB->escapeString($this->feedCatName);
				
				// db-Query nach Anzahl vorhandener Newsfeedkategorien
				$existCatName = $this->DB->query( "SELECT *  
														FROM `" . DB_TABLE_PREFIX . "news_categories` 
														WHERE `category_" . $this->editLang . "` = '$feedCatNameDb' 
														AND `newsfeed` > 0
														", false);
				#var_dump($existCatName);
				
				if(count($existCatName) > 0) {
					$this->feedCatName = $existCatName[0]['category_' . $this->editLang];
					$this->params[0] = $existCatName[0]['cat_id'];
				}
					
			}
			
			elseif($this->feedCatName == "") { // Falls kein Kategoriename eingegeben wurde
				$this->wrongInput[] = $this->conPrefix;
				$this->error =  "{s_error:choosecat}";
			}
			
			elseif($this->feedCatName == "<{s_text:allfeeds}>") { // Falls kein Kategoriename eingegeben wurde
				
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
		$feedType		= $this->DB->escapeString(trim($this->params[1]));
		$feedTargetID	= $this->DB->escapeString(trim($this->params[2]));

		$this->dbUpdateStr = "'" . $catID . "<>" . $feedType . "<>" . $feedTargetID . "',";

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

		if($this->params[0] == "<all>")
			$this->feedCatName = "<{s_text:allfeeds}>";							

		elseif(!isset($this->a_POST[$this->conPrefix])) { // Falls das Formular NICHT abgeschickt wurde
			
			$catID = $this->DB->escapeString($this->params[0]);
			
			// db-Query nach Anzahl vorhandener Newskategorien
			$existCats = $this->DB->query("SELECT *  
												FROM `" . DB_TABLE_PREFIX . "news_categories` 
												WHERE `cat_id` = '$catID' 
												AND `newsfeed` > 0
												", false);
			#var_dump(($existCats));
			
			if(count($existCats) > 0)
				$this->feedCatName = $existCats[0]['category_' . $this->editLang];
		}
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";

		// Button goto news
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=news&list_cat=',
								"class"		=> "modLink button-icon-only right",
								"text"		=> "",
								"title"		=> "{s_title:gotonews}",
								"icon"		=> "news"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);
		

		// News cats MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "feed",
											"type"		=> "feed",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=feed",
											"value"		=> "{s_button:feedchoose}",
											"icon"		=> "news"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<label>Newsfeed</label>' . "\r\n";
						
		if(in_array($this->conPrefix, $this->wrongInput)) { // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . "\r\n" . 
						'<a href="' . ADMIN_HTTP_ROOT . '?task=modules&type=news&cat_name=' . htmlspecialchars($this->feedCatName) . '">{s_link:newcat}</a></span>' . "\r\n";
			
			// Ggf. data_id aus Session löschen
			$this->unsetSessionKey($this->conType . '_id');
		}
							
		$output	.=	'<input type="text" name="' . $this->conPrefix . '_feedname" value="' . htmlspecialchars($this->feedCatName) . '" />' . "\r\n" . 
					'<input type="hidden" name="' . $this->conPrefix . '" value="' . htmlspecialchars($this->params[0]) . '" />' . "\r\n" . 
					'<label>{s_label:newsfeed}</label>' . "\r\n" . 
					'<select name="' . $this->conPrefix . '_type">' . "\r\n" . 
					'<option value="all"' . ((isset($this->params[1]) && $this->params[1] == "all") ? " selected=\"selected\"" : "") . '>RSS/Atom</option>' . "\r\n" . 
					'<option value="RSS"' . ((isset($this->params[1]) && $this->params[1] == "RSS") ? " selected=\"selected\"" : "") . '>RSS</option>' . "\r\n" . 
					'<option value="Atom"' . ((isset($this->params[1]) && $this->params[1] == "Atom") ? " selected=\"selected\"" : "") . '>Atom</option>' . "\r\n" . 
					'</select><p class="clearfloat">&nbsp;</p><br />' . "\r\n";
					 
		$output	.=	'<div class="fieldBox cc-box-info right"><label>{s_label:targetpage}' . "\r\n" . 
					parent::getIcon("info", "editInfo", 'title="{s_title:targetpage}"') .
					'</label></div>' . "\r\n";
		
		// targetPage MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "targetPage",
											"type"		=> "targetPage",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"slbclass"	=> "target",
											"value"		=> "{s_button:targetPage}",
											"icon"		=> "page"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<label>{s_button:targetPage}</label>' . "\r\n" . 
					'<input type="text" name="' . $this->conPrefix . '_feedTarget" class="targetPage" value="' . htmlspecialchars(HTML::getLinkPath($this->params[2], "editLang", false)) . '" readonly="readonly" />' . "\r\n" .
					'<input type="hidden" name="' . $this->conPrefix . '_feedTargetID" class="targetPageID" value="' . htmlspecialchars($this->params[2]) . '" />' . "\r\n";

		return $output;
	
	}

}
