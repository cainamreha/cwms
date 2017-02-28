<?php
namespace Concise;


##############################
#########  Redirect  #########
##############################

/**
 * RedirectConfigElement class
 * 
 * content type => redirect
 */
class RedirectConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein RedirectConfigElement zurück
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
		$this->params	= explode("|", $this->conValue);
		
		
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
			
			$this->params[0] = trim($this->a_POST[$this->conPrefix]);
			
			if(isset($this->a_POST[$this->conPrefix . '_permanent']) && $this->a_POST[$this->conPrefix . '_permanent'] == "on")
				$this->params[1] = 1;
			else
				$this->params[1] = 0;
			
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$this->dbUpdateStr = "'" . $this->DB->escapeString($this->params[0]) . "|" . $this->params[1] . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params[0]))
			$this->params[0] = "";
		if(!isset($this->params[1]))
			$this->params[1] = 0;

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";
			
		// Redirect-Ziel
			$output	.=	'<span class="fieldBox cc-box-info right"><label>{s_text:chooselink}</label></span>' . "\r\n";
		
		// Links MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "links",
											"type"		=> "links",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"value"		=> "Links {s_label:intern}",
											"icon"		=> "link"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=		'<label>Link</label>' . "\r\n" . 
						'<input type="text" name="' . $this->conPrefix . '" value="' . htmlspecialchars($this->params[0]) . '" />' . "\r\n"; 

		// Permanenter Redirect
		$output	.=	'<label for="' . $this->conPrefix . '_permanent">Permanent redirect (301)</label>' . "\r\n" .
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="' . $this->conPrefix . '_permanent" id="' . $this->conPrefix . '_permanent" ' . ($this->params[1] == 1 ? ' checked="checked"' : '') . ' style="margin:2px 10px 0 0; float:left;" />' . "\r\n" .
					'</label>' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n";

		return $output;
	
	}

}
