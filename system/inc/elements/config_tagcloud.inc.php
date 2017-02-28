<?php
namespace Concise;


##############################
########  Tag cloud  #########
##############################

/**
 * TagcloudConfigElement class
 * 
 * content type => tagcloud
 */
class TagcloudConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $optionsNo	= "";
	private $optionsMin	= "";
	private $optionsMax	= "";

	/**
	 * Gibt ein TagcloudConfigElement zur¸ck
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

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
		
		
		// DB-Updatestr generieren
		$this->makeUpdateStr();
		
		
		// Parameter (default) setzen
		$this->setParams();

		
		// Element-Formular generieren
		$this->output		= $this->getCreateElementHtml();
		
		
		// Ausgabe-Array erstellen und zur¸ckgeben
		return $this->makeOutputArray();
		
	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params[0]	= trim($this->a_POST[$this->conPrefix]);
			$this->params[2]	= trim($this->a_POST[$this->conPrefix . '_min']);
			$this->params[3]	= trim($this->a_POST[$this->conPrefix . '_max']);
			$this->params[1]	= "";
			
			if(isset($this->a_POST[$this->conPrefix . '_articles']) && $this->a_POST[$this->conPrefix . '_articles'] == "on")
				$this->params[1] .= "articles,";
			if(isset($this->a_POST[$this->conPrefix . '_news']) && $this->a_POST[$this->conPrefix . '_news'] == "on")
				$this->params[1] .= "news,";
			if(isset($this->a_POST[$this->conPrefix . '_planner']) && $this->a_POST[$this->conPrefix . '_planner'] == "on")
				$this->params[1] .= "planner,";
				
			if($this->params[1] != "")
				$this->params[1]	= substr($this->params[1], 0, -1);
			else {
				$this->wrongInput[]	= $this->params;
				$this->error			= "{s_error:choosetab}";
			}
			
			if(isset($this->a_POST[$this->conPrefix . '_targetPageID']) && count(explode(",", $this->params[1])) <= 1)
				$this->params[4]	= $this->a_POST[$this->conPrefix . '_targetPageID'];
			else
				$this->params[4] = -1004; // Standardm‰ﬂig Suchseite
			
		}	
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$this->dbUpdateStr  = "'";
		$this->dbUpdateStr .= $this->DB->escapeString(implode("<>", $this->params));
		$this->dbUpdateStr .= "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params[0]) || $this->params[0] == "")
			$this->params[0] = 20;
		if(!isset($this->params[1]))
			$this->params[1] = "";
		if(!isset($this->params[2]) || $this->params[2] == "")
			$this->params[2] = 8;
		if(!isset($this->params[3]) || $this->params[3] == "")
			$this->params[3] = 30;
		if(!isset($this->params[4]))
			$this->params[4] = -1004; // Standardm‰ﬂig Suchseite
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
							
		if(in_array($this->params, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice error">' . $this->error . '</span>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;

		
		// Cloud params
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:tagcloudsize}</label>' . PHP_EOL .
					'<select class="tiny-select" name="' . $this->conPrefix . '">' . PHP_EOL;
					
		for($t = 5; $t <=50; $t++) {
			$this->optionsNo	.=	'<option value="' . $t . '"' . ($this->params[0] == $t ? ' selected="selected"' : '') . '">' . $t . '</option>' . PHP_EOL; 
			$this->optionsMin	.=	'<option value="' . $t . '"' . ($this->params[2] == $t ? ' selected="selected"' : '') . '">' . $t . '</option>' . PHP_EOL; 
			$this->optionsMax	.=	'<option value="' . $t . '"' . ($this->params[3] == $t ? ' selected="selected"' : '') . '">' . $t . '</option>' . PHP_EOL; 
		}
		$optArr = array(75,100,150,200,250,300,500,1000);
		foreach($optArr as $t) {
			$this->optionsNo	.=	'<option value="' . $t . '"' . ($this->params[0] == $t ? ' selected="selected"' : '') . '">' . $t . '</option>' . PHP_EOL; 
		}
		
		$output	.=	$this->optionsNo . '</select><br />' . PHP_EOL;

		$output	.=	'<label>{s_label:tagcloudrange}</label>' . PHP_EOL .
					'<select class="tiny-select" name="' . $this->conPrefix . '_min">' . PHP_EOL .
					$this->optionsMin	. '</select>&nbsp;-&nbsp;' . PHP_EOL;

		$output	.=	'<select class="tiny-select" name="' . $this->conPrefix . '_max">' . PHP_EOL .
					$this->optionsMax	. '</select>&nbsp;px' . PHP_EOL .
					'</div>' . PHP_EOL;
						 

		// Tag-Tabellen
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:tagtables}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_articles" id="' . $this->conPrefix . '_articles"' . (strpos($this->params[1], "articles") !== false ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '_articles" class="inline-label">{s_option:articles}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_news" id="' . $this->conPrefix . '_news"' . (strpos($this->params[1], "news") !== false ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '_news" class="inline-label">{s_option:news}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_planner" id="' . $this->conPrefix . '_planner"' . (strpos($this->params[1], "planner") !== false ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '_planner" class="inline-label">{s_option:planner}</label>' . PHP_EOL .					
					'</div><br class="clearfloat" />' . PHP_EOL;

		$output	.=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_button:targetPage}">' . PHP_EOL;
		
		// Zielseite
		$output	.=	'<div class="leftBox">' . PHP_EOL;
		
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
					'<input type="text" name="' . $this->conPrefix . '_targetPage" class="targetPage" value="' . htmlspecialchars(HTML::getLinkPath($this->params[4], "editLang", false)) . '" readonly="readonly" />' . PHP_EOL .
					'<input type="hidden" name="' . $this->conPrefix . '_targetPageID" class="targetPageID" value="' . htmlspecialchars($this->params[4]) . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<div class="fieldBox cc-box-info clearfix"><label>{s_label:targetPageTags}' . PHP_EOL . 
					parent::getIcon("info", "editInfo", 'title="{s_title:targetpagetags}"') .
					'</label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;

		$output	.=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
