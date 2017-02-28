<?php
namespace Concise;


##############################
########  Textinhalt  ########
##############################


/**
 * TextConfigElement class
 * 
 * content type => text
 */
class TextConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $conDef_h		= array();
	private $conDef_levelh	= array();
	private $conDef_text	= "";

	/**
	 * Gibt ein TextConfigElement zurück
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
		$this->textAreaCount	= $options["textAreaCount"];
	
	}

	
	public function getConfigElement($a_POST)
	{

		$this->a_POST	= $a_POST;
		$this->params	= $this->conValue;
		
		$this->conDef_h[1]		= ""; // Überschrift 1
		$this->conDef_h[2]		= ""; // Überschrift 2
		$this->conDef_text		= ""; // Text
		$this->conDef_levelh[1]	= 1; // Überschriften Ebene Überschrift 1
		$this->conDef_levelh[2]	= 2; // Überschriften Ebene Überschrift 1

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
			
		
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
			
			/*
			// Überschriften Text
			$this->conDef_h[1] = trim($this->a_POST[$this->conPrefix . '_h1']);
			$this->conDef_h[2] = trim($this->a_POST[$this->conPrefix . '_h2']);
			
			// Überschriften Ebene
			$this->conDef_levelh[1] = $this->a_POST[$this->conPrefix . '_levelh1'];
			$this->conDef_levelh[2] = $this->a_POST[$this->conPrefix . '_levelh2'];
			*/
			
			// Text
			$this->conDef_text = $this->a_POST[$this->conPrefix];
			
			// Pfade durch Platzhalter ersetzen
			$rootPH		= "{#root}";
			$rootImgPH	= "{#root}/{#root_img}";
			
			$this->conDef_text = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $this->conDef_text);
			$this->conDef_text = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $this->conDef_text);
		
			// DB-Updatestr generieren
			$this->makeUpdateStr();
		
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$this->dbUpdateStr = "'";

		/*
		if($this->conDef_h[1] != "")
			$this->dbUpdateStr .= "<h" . $this->conDef_levelh[1] . ">" . $this->DB->escapeString($this->conDef_h[1]) . "</h" . $this->conDef_levelh[1] . ">";
		else
			$this->conDef_levelh[1] = 1;
		if($this->conDef_h[2] != "")
			$this->dbUpdateStr .= "<h" . $this->conDef_levelh[2] . ">" . $this->DB->escapeString($this->conDef_h[2]) . "</h" . $this->conDef_levelh[2] . ">";
		else
			$this->conDef_levelh[2] = 2;
		*/
		if(trim($this->conDef_text) != "") {
			
			$this->dbUpdateStr .= $this->DB->escapeString($this->conDef_text);
		}
		
		$this->dbUpdateStr .= "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{
	
		/*
		if(preg_match("/^<h[0-9]>([^<^>.]*)<\/h[0-9]><h[0-9]>([^<^>.]*)<\/h[0-9]>.*$/is", $this->conValue)) { // Falls zwei Überschriften vorhanden sind

			$this->conDef_h[1] =	preg_replace("/^<h[0-9]>([^<^>.]*)<\/h[0-9]><h[0-9]>([^<^>.]*)<\/h[0-9]>(.*)$/is", "$1", $this->conValue); // Überschrift 1
			$this->conDef_h[2] =	preg_replace("/^<h[0-9]>([^<^>.]*)<\/h[0-9]><h[0-9]>([^<^>.]*)<\/h[0-9]>(.*)$/is", "$2", $this->conValue); // Überschrift 2
			$this->conDef_text =	preg_replace("/^<h[0-9]>([^<^>.]*)<\/h[0-9]><h[0-9]>([^<^>.]*)<\/h[0-9]>(.*)$/is", "$3", $this->conValue); // Text
			
			// Überschriften Ebene
			$this->conDef_levelh[1] =	preg_replace("/^<h([0-9])>[^<^>.]*<\/h[0-9]><h[0-9]>[^<^>.]*<\/h[0-9]>.*$/is", "$1", $this->conValue); // Überschrift 1
			$this->conDef_levelh[2] =	preg_replace("/^<h[0-9]>[^<^>.]*<\/h[0-9]><h([0-9])>[^<^>.]*<\/h[0-9]>.*$/is", "$1", $this->conValue); // Überschrift 1
		}
		
		elseif(preg_match("/^<h[0-9]>([^<^>.]*)<\/h[0-9]>.*$/is", $this->conValue)) { // Falls nur eine Überschrift vorhanden ist

			$this->conDef_h[1] = preg_replace("/^<h[0-9]>([^<^>.]*)<\/h[0-9]>.*$/is", "$1", $this->conValue); // Überschrift 1
			$this->conDef_h[2] = ""; // Überschrift 2
			$this->conDef_text = preg_replace("/^<h[0-9]>[^<^>.]*<\/h[0-9]>(.*)$/is", "$1", $this->conValue); // Überschrift 1
			
			// Überschriften Ebene
			$this->conDef_levelh[1] = preg_replace("/^<h([0-9])>[^<^>.]*<\/h[0-9]>.*$/is", "$1", $this->conValue); // Überschrift 1
			if($this->conDef_levelh[1] < 5)
				$this->conDef_levelh[2] = $this->conDef_levelh[1] +1; // Überschrift 1
			else
				$this->conDef_levelh[2] = $this->conDef_levelh[1]; // Überschrift 1
		}
		
		else { // Falls keine Überschriften vorhanden sind

			$this->conDef_h[1] = ""; // Überschrift 1
			$this->conDef_h[2] = ""; // Überschrift 2
			
			// Überschriften Ebene
			$this->conDef_levelh[1] = 1; // Überschrift 1
			$this->conDef_levelh[2] = 2; // Überschrift 1
			
			$this->conDef_text = $this->conValue; // Text ohne Überschriften
		}
		*/
		
		$this->conDef_text = $this->conValue; // Text ohne Überschriften
		
		// Pfade durch Platzhalter ersetzen (erfolgt zwar auch durch Javascript in HeadExt, aber nicht schnell genug (Image-Ladeproblem))
		$rootPH		= "{#root}";
		$rootImgPH	= "{#root}/{#root_img}";
		
		$this->conDef_text = str_replace($rootImgPH, PROJECT_HTTP_ROOT . '/' . IMAGE_DIR, $this->conDef_text);
		$this->conDef_text = str_replace($rootPH, PROJECT_HTTP_ROOT, $this->conDef_text);
		
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";

		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . '</span>' . "\r\n";

		/*
		for($h=1; $h<=2; $h++) { // Überschriften anlegen (2x)
			
			$output	.=	'<label>{s_label:heading}' . $h .
						'<span style="right:12.5%;" class="editLangFlag">' . $this->editLangFlag . '</span>' . "\r\n" .
						'</label>' . "\r\n" .
						'<input type="text" name="' . $this->conPrefix . '_h' . $h . '" value="' . htmlspecialchars($this->conDef_h[$h]) . '" class="headerInput" />' . "\r\n" .
						'<select name="' . $this->conPrefix . '_levelh' . $h . '" style="float:right; width:10%; min-width:48px; margin:0;">' . "\r\n";
			
			for($k=1; $k<=5; $k++) {
				
				$output	.= '<option value="'. $k . '"';
				if($this->conDef_levelh[$h] == $k)
					$output	.= ' selected="selected"';
				$output	.= '>h' . $k . '</option>' . "\r\n";
			}
		
			$output	.=	'</select><br class="clearfloat" />' . "\r\n";
		}
		*/
		
		// Textfeld anlegen
		$output	.=	'<label>{s_label:text}<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '" id="textCon' . $this->textAreaCount . '" class="cc-editor-add textEditor code">' . htmlentities($this->conDef_text, ENT_COMPAT, "UTF-8") . '</textarea>' . "\r\n";

		return $output;
	
	}

}
