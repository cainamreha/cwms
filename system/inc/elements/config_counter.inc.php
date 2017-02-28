<?php
namespace Concise;


##############################
#########  Counter  ##########
##############################

/**
 * CounterConfigElement class
 * 
 * content type => counter
 */
class CounterConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein CounterConfigElement zurück
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
		$this->params	= explode("/", $this->conValue);

		
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
									   
			if(isset($this->a_POST[$this->conPrefix . '_online']) && $this->a_POST[$this->conPrefix . '_online'] == "on")
				$this->params[0] = 1;
			else
				$this->params[0] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_today']) && $this->a_POST[$this->conPrefix . '_today'] == "on")
				$this->params[1] = 1;
			else
				$this->params[1] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_yesterday']) && $this->a_POST[$this->conPrefix . '_yesterday'] == "on")
				$this->params[2] = 1;
			else
				$this->params[2] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_total']) && $this->a_POST[$this->conPrefix . '_total'] == "on")
				$this->params[3] = 1;
			else
				$this->params[3] = 0;
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString(trim(implode("/", $this->params))) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params[0]))
			$this->params[0] = 0;
		if(!isset($this->params[1]))
			$this->params[1] = 0;
		if(!isset($this->params[2]))
			$this->params[2] = 0;
		if(!isset($this->params[3]))
			$this->params[3] = 0;
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";
							
		// Online
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_online" id="' . $this->conPrefix . '_online" ' . ($this->params[0] == 1 ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="' . $this->conPrefix . '_online" class="inline-label">{s_label:usersonline}</label>' . "\r\n";

		// Today
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_today" id="' . $this->conPrefix . '_today" ' . ($this->params[1] == 1 ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="' . $this->conPrefix . '_today" class="inline-label">{s_label:visitstoday}</label>' . "\r\n";
		
		// Yesterday
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_yesterday" id="' . $this->conPrefix . '_yesterday" ' . ($this->params[2] == 1 ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="' . $this->conPrefix . '_yesterday" class="inline-label">{s_label:visitsyesterday}</label>' . "\r\n";
		
		// Total
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_total" id="' . $this->conPrefix . '_total" ' . ($this->params[3] == 1 ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="' . $this->conPrefix . '_total" class="inline-label">{s_label:visitstotal}</label>' . "\r\n" .
					'<input type="hidden" name="' . $this->conPrefix . '" value="true" />' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n";

		return $output;
	
	}

}
