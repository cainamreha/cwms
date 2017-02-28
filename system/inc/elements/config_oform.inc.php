<?php
namespace Concise;



##############################
#####  Bestellformular  ######
##############################

/**
 * OformConfigElement class
 * 
 * content type => oform
 */
class OformConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $addOpt	= "";

	/**
	 * Gibt ein OformConfigElement zurück
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
	
		if(!empty($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params[0] = $this->a_POST[$this->conPrefix];
			$this->params[1] = $this->a_POST[$this->conPrefix . '_notice'];
		
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString(implode("<>", $this->params)) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(empty($this->params[0])) {
			$this->wrongInput[]	= $this->conPrefix;
			$this->error		= "{s_error:ordertype}";
			$this->addOpt		= '<option value="" selected="selected">{s_option:choose}</option>' . PHP_EOL;
		}
		if(!isset($this->params[1]))
			$this->params[1]	= '';
		if(strpos($this->params[1], "<>") !== false) {
			$this->wrongInput[]	= $this->conPrefix;
			$this->error		= "{s_error:check}";
		}
	
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;

		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice error">' . $this->error . '</span>' . PHP_EOL;

		$output	.=	'<label>{s_label:formtype}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '">' . PHP_EOL . 
					$this->addOpt .
					'<option value="default"' . ((isset($this->params[0]) && $this->params[0] == "default") ? " selected=\"selected\"" : "") . '>{s_option:default}</option>' . PHP_EOL . 
					'<option value="agb"' . ((isset($this->params[0]) && $this->params[0] == "agb") ? " selected=\"selected\"" : "") . '>{s_option:regagb}</option>' . PHP_EOL . 
					'<option value="newsletter"' . ((isset($this->params[0]) && $this->params[0] == "newsletter") ? " selected=\"selected\"" : "") . '>{s_option:regnewsl}</option>' . PHP_EOL . 
					'<option value="agb-newsl"' . ((isset($this->params[0]) && $this->params[0] == "agb-newsl") ? " selected=\"selected\"" : "") . '>{s_option:regagbnewsl}</option>' . PHP_EOL . 
					'</select><br class="clearfloat" />' . PHP_EOL;
						
		$output	.=	'<label>{s_label:formsuccess}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL . 
					'<textarea name="' . $this->conPrefix . '_notice" class="noTinyMCE" rows="3" style="min-height:30px;" />' . htmlspecialchars($this->params[1]) . '</textarea>' . PHP_EOL; // Erfolgsmeldung

		return $output;
	
	}

}
