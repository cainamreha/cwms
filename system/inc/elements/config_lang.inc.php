<?php
namespace Concise;


##############################
######  Sprachauswahl  #######
##############################

/**
 * LangConfigElement class
 * 
 * content type => lang
 */
class LangConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein LangConfigElement zurück
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
		
		
		// Ausgabe-Array erstellen und zurückgeben
		return $this->makeOutputArray();
		
	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params[0] = trim($this->a_POST[$this->conPrefix]);
			$this->params[1] = $this->a_POST[$this->conPrefix . '_langseparator'];
			$this->params[2] = isset($this->a_POST[$this->conPrefix . '_hideactive']) && $this->a_POST[$this->conPrefix . '_hideactive'] == "on" ? 1 : 0;
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$this->dbUpdateStr = "'" . $this->DB->escapeString(implode("<>", $this->params)) . "',";
	
	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params[0]))
			$this->params[0] = "";

		if(!isset($this->params[1]))
			$this->params[1] = "";

		if(!isset($this->params[2]))
			$this->params[2] = 0;

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$this->params[0] = htmlspecialchars($this->params[0]);

		$output	=	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n" . 
					'<div class="leftBox">' . "\r\n" .
					'<label>{s_label:menu}</label>' . "\r\n" .
					'<select name="' . $this->conPrefix . '">' . "\r\n" .
					'<option value="flag"' . ($this->params[0] == "flag" ? ' selected="selected"' : '') . '>{s_option:langmenuflag}</option>' . "\r\n" .
					'<option value="text"' . ($this->params[0] == "text" ? ' selected="selected"' : '') . '>{s_option:langmenutext}</option>' . "\r\n" .
					'</select>' . "\r\n" .
					'</div>' . "\r\n";
		
		// Separator
		$output	.=	'<div class="rightBox">' . "\r\n" .
					'<label>{s_label:menuseparator}</label>' . "\r\n" .
					'<input type="text" name="' . $this->conPrefix . '_langseparator" value="' . $this->params[1] . '" />' . "\r\n" .
					'</div>' . "\r\n" .
					'<br class="clearfloat" /><br />' . "\r\n";

		// Show active lang
		$output	.=	'<label for="' . $this->conPrefix . '_hideactive">{s_label:langactive}</label>' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_hideactive" id="' . $this->conPrefix . '_hideactive"' . ($this->params[2] ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<br class="clearfloat" /><br />' . "\r\n";

		return $output;
	
	}

}
