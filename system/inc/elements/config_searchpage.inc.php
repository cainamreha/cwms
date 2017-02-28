<?php
namespace Concise;


##############################
########  Searchpage  ########
##############################

/**
 * SearchpageConfigElement class
 * 
 * content type => login
 */
class SearchpageConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein SearchpageConfigElement zurück
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
		$this->params	= (array)json_decode($this->conValue);

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
		
		
		// DB-Updatestr generieren
		$this->makeUpdateStr();
		
		
		// Parameter (default) setzen
		$this->setParams();

		
		// Element-Formular generieren
		$this->output		= $this->getCreateElementHtml();
		
		
		// Ausgabe-Array erstellen und zurückgeben
		return $this->makeOutputArray();
	
	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix])) {
		
			if(isset($this->a_POST[$this->conPrefix . '_pages']) && $this->a_POST[$this->conPrefix . '_pages'] == "on")
				$this->params["s"] = 1;
			else
				$this->params["s"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_articles']) && $this->a_POST[$this->conPrefix . '_articles'] == "on")
				$this->params["a"] = 1;
			else
				$this->params["a"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_news']) && $this->a_POST[$this->conPrefix . '_news'] == "on")
				$this->params["n"] = 1;
			else
				$this->params["n"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_planner']) && $this->a_POST[$this->conPrefix . '_planner'] == "on")
				$this->params["p"] = 1;
			else
				$this->params["p"] = 0;
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString(json_encode($this->params)) . "',";
	
	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{
	
		// Nichtgesetzte Indizes setzen
		if(!isset($this->params["s"])) $this->params["s"] = 1;
		if(!isset($this->params["a"])) $this->params["a"] = 1;
		if(!isset($this->params["n"])) $this->params["n"] = 1;
		if(!isset($this->params["p"])) $this->params["p"] = 1;
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	=	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL .
					'<h5 class="cc-h5">{s_text:refinesearch}</h5><br />' . PHP_EOL .
					'<label class="markAll markBox" data-mark="#searchTabs-' . $this->conPrefix . '"><input type="checkbox" id="' . $this->conPrefix . '-markAll" data-select="all" /></label>' .
					'<label for="' . $this->conPrefix . '-markAll" class="markAllLB inline-label">{s_label:mark}</label>' . PHP_EOL .
					'<span class="separator">&nbsp;</span>' . PHP_EOL .
					'<div id="searchTabs-' . $this->conPrefix . '">' . PHP_EOL .
					'<div class="leftBox clear">' . PHP_EOL;
		
		// Pages
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_pages" id="' . $this->conPrefix . '_pages" ' . ($this->params["s"] == 1 ? ' checked="checked"' : '') . ' /></label><label class="inline-label" for="' . $this->conPrefix . '_pages">' . PHP_EOL .
					'{s_label:pages}</label>' . PHP_EOL;
							
		// Articles
		$output	.= 	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_articles" id="' . $this->conPrefix . '_articles" ' . ($this->params["a"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_articles">' . PHP_EOL .
					'{s_option:articles}</label>' . PHP_EOL;
							
		// News
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_news" id="' . $this->conPrefix . '_news" ' . ($this->params["n"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_news">' . PHP_EOL .
					'{s_option:news}</label>' . PHP_EOL;

		// Planner
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_planner" id="' . $this->conPrefix . '_planner" ' . ($this->params["p"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_planner">' . PHP_EOL .
					'{s_option:planner}</label>' . PHP_EOL;

		$output	.=	'<input type="hidden" name="' . $this->conPrefix . '" value="true" />' . PHP_EOL .
					'<br class="clearfloat">' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;

		return $output;
	
	}

}
