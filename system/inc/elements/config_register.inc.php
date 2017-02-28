<?php
namespace Concise;


##############################
########  Register  ##########
##############################

/**
 * RegisterConfigElement class
 * 
 * content type => register
 */
class RegisterConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein RegisterConfigElement zurück
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
		$this->params	= $this->conValue;

		
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
	
		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params = $this->a_POST[$this->conPrefix];
		
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString($this->params) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;

		$output	.=	'<label class="cc-group-label">{s_label:register}</label><br />' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL . 
					'<label>{s_label:regtype}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '">' . PHP_EOL . 
					'<option value="newsletter"' . ((isset($this->params) && $this->params == "newsletter") ? " selected=\"selected\"" : "") . '>{s_option:regnewsl}</option>' . PHP_EOL . 
					'<option value="account"' . ((isset($this->params) && $this->params == "account") ? " selected=\"selected\"" : "") . '>{s_option:regaccount}</option>' . PHP_EOL . 
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;

		return $output;
	
	}

}
