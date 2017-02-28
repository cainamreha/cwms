<?php
namespace Concise;


##############################
########  Logoutpage  ########
##############################

/**
 * LogoutpageConfigElement class
 * 
 * content type => login
 */
class LogoutpageConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein LogoutpageConfigElement zurück
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

		$this->conDef_text		= ""; // Text
		
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

			// Text
			$this->conDef_text = $this->a_POST[$this->conPrefix];	
			
			// Pfade durch Platzhalter ersetzen
			$rootPH		= "{#root}";
			$rootImgPH	= "{#root}/{#root_img}";
			
			$this->conDef_text = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $this->conDef_text);
			$this->conDef_text = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $this->conDef_text);
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString($this->conDef_text) . "',";
	
	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		$this->conDef_text = $this->conValue; // Text
		
		// Pfade durch Platzhalter ersetzen (erfolgt zwar auch durch Javascript in HeadExt, aber nicht schnell genug (Image-Ladeproblem))
		$rootPH		= "{#root}";
		$rootImgPH	= "{#root}/{#root_img}";
		
		$this->conDef_text = str_replace($rootImgPH, PROJECT_HTTP_ROOT . '/' . IMAGE_DIR, $this->conDef_text);
		$this->conDef_text = str_replace($rootPH, PROJECT_HTTP_ROOT, $this->conDef_text);
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	= '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";
		
		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . '</span>' . "\r\n";

		// Textfeld anlegen
		$output	.=	'<label>{s_label:text}<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '" id="textCon' . $this->textAreaCount . '" class="cc-editor-add textEditor code">' . $this->conDef_text . '</textarea>' . "\r\n";

		return $output;
	
	}

}
