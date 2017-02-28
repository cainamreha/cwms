<?php
namespace Concise;


##############################
########  GoogleMap  #########
##############################

/**
 * GmapConfigElement class
 * 
 * content type => gmap
 */
class GmapConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein GmapConfigElement zurück
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
			$this->params[1] = trim($this->a_POST[$this->conPrefix . '_radius']);
			$this->params[2] = trim($this->a_POST[$this->conPrefix . '_width']);
			$this->params[3] = trim($this->a_POST[$this->conPrefix . '_height']);
			$this->params[4] = trim($this->a_POST[$this->conPrefix . '_iframe']);
			$this->params[5] = isset($this->a_POST[$this->conPrefix . '_border']) && $this->a_POST[$this->conPrefix . '_border'] == "on" ? 1 : 0;
			
			if((!is_numeric($this->params[1]) && $this->params[1] != "")) {
				$this->wrongInput[] = $this->conPrefix . '_radius';
				$mapCon1db = "";
			}
			else
				$mapCon1db = $this->DB->escapeString($this->params[1]);
			if($this->params[2] != "" && !is_numeric($this->params[2])) {
				$this->wrongInput[] = $this->conPrefix . '_width';
				$mapCon2db = "";
			}
			else
				$mapCon2db = $this->DB->escapeString($this->params[2]);
			if($this->params[3] != "" && !is_numeric($this->params[3])) {
				$this->wrongInput[] = $this->conPrefix . '_height';
				$mapCon3db = "";
			}
			else
				$mapCon3db = $this->DB->escapeString($this->params[3]);
			
			$mapCon0db = $this->DB->escapeString($this->params[0]);
			$mapCon4db = $this->DB->escapeString($this->params[4]);
			$mapCon5db = $this->DB->escapeString($this->params[5]);
			
			$this->dbUpdateStr = "'" . $mapCon0db . "<>" . $mapCon1db . "<>" . $mapCon2db . "<>" . $mapCon3db . "<>" . $mapCon4db . "<>" . $mapCon5db . "',";
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{


	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params[0]))
			$this->params[0] = "";
		if(!isset($this->params[1]))
			$this->params[1] = "";
		if(!isset($this->params[2]))
			$this->params[2] = "";
		if(!isset($this->params[3]))
			$this->params[3] = "";
		if(!isset($this->params[4]))
			$this->params[4] = "";
		if(!isset($this->params[5]))
			$this->params[5] = 0;

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{
	
		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;

		$output .=	'<fieldset>' . PHP_EOL;
		
		$output	.=	'<div class="leftBox">' . PHP_EOL;
		
		$output	.=	'<label>{s_label:mapnear}';

		if(in_array($this->conPrefix, $this->wrongInput))
			$output	.=	'<span class="notice">{s_error:check}</span>';
			
		$output	.=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '" value="' . htmlspecialchars($this->params[0]) . '" maxlength="512" />' . PHP_EOL . 
					'<label>{s_label:mapradius}';
							
		if(in_array($this->conPrefix . '_radius', $this->wrongInput))
			$output	.=	'<span class="notice">{s_error:nonumber}</span>';
							
		$output	.=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_radius" value="' . htmlspecialchars($this->params[1]) . '" maxlength="512" />' . PHP_EOL . 
					'<br class="clearfloat" />';
		
		$output	.=	'</div>' . PHP_EOL;
		$output	.=	'<div class="rightBox">' . PHP_EOL;

		$output	.=	'<label>{s_label:width}';

		if(in_array($this->conPrefix . '_width', $this->wrongInput))
			$output	.=	'<span class="notice">{s_error:nonumber}</span>';
			
		$output	.=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_width" value="' . htmlspecialchars($this->params[2]) . '" maxlength="4" />' . PHP_EOL . 
					'<label>{s_label:height}';
							
		if(in_array($this->conPrefix . '_height', $this->wrongInput))
			$output	.=	'<span class="notice">{s_error:nonumber}</span>';
							
		$output	.=	'</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_height" value="' . htmlspecialchars($this->params[3]) . '" maxlength="4" />' . PHP_EOL . 
					'<br class="clearfloat" />';
		$output	.=	'</div>' . PHP_EOL .
					'<br class="clearfloat" />';
		
		$output	.=	'<label>{s_label:html}</label>' . PHP_EOL . 
					'<textarea name="' . $this->conPrefix . '_iframe" cols="5" class="noTinyMCE code">' . htmlspecialchars($this->params[4]) . '</textarea>' . PHP_EOL . 
					'<br class="clearfloat" />';

		$output .=	'</fieldset>' . PHP_EOL;
		
		
		// Style
		$output .=	'<fieldset>' . PHP_EOL;
		
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_border" id="' . $this->conPrefix . '_border" ' . ($this->params[5] ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_border">' . PHP_EOL .
					'{s_label:border}</label>' . PHP_EOL;
	
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
