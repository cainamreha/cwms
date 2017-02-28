<?php
namespace Concise;


##############################
########  Errorpage  #########
##############################

/**
 * ErrorpageConfigElement class
 * 
 * content type => login
 */
class ErrorpageConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein ErrorpageConfigElement zurück
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
	
		if(isset($this->a_POST[$this->conPrefix . '_er']))
			$this->params["er"] = $this->a_POST[$this->conPrefix . '_er'];
		if(isset($this->a_POST[$this->conPrefix . '_nf']))
			$this->params["nf"] = $this->a_POST[$this->conPrefix . '_nf'];
		if(isset($this->a_POST[$this->conPrefix . '_fb']))
			$this->params["fb"] = $this->a_POST[$this->conPrefix . '_fb'];
		if(isset($this->a_POST[$this->conPrefix . '_sv']))
			$this->params["sv"] = $this->a_POST[$this->conPrefix . '_sv'];
		if(isset($this->a_POST[$this->conPrefix . '_st']))
			$this->params["st"] = $this->a_POST[$this->conPrefix . '_st'];
		if(isset($this->a_POST[$this->conPrefix . '_ac']))
			$this->params["ac"] = $this->a_POST[$this->conPrefix . '_ac'];
		if(isset($this->a_POST[$this->conPrefix . '_nl']))
			$this->params["nl"] = $this->a_POST[$this->conPrefix . '_nl'];
		if(isset($this->a_POST[$this->conPrefix . '_to']))
			$this->params["to"] = $this->a_POST[$this->conPrefix . '_to'];
		if(isset($this->a_POST[$this->conPrefix . '_nn']))
			$this->params["nn"] = $this->a_POST[$this->conPrefix . '_nn'];

	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{
	
		// Pfade durch Platzhalter ersetzen
		$params		= json_encode($this->params);
		
		$rootPH		= "{#root}";
		$rootImgPH	= "{#root}/{#root_img}";
		
		$params 	= str_replace($rootImgPH, PROJECT_HTTP_ROOT . '/' . IMAGE_DIR, $params);
		$params 	= str_replace($rootPH, PROJECT_HTTP_ROOT, $params);
		
		// Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString($params) . "',";
	
	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(empty($this->params["er"]))	$this->params["er"]		= "";
		if(empty($this->params["nf"]))	$this->params["nf"]		= "";
		if(empty($this->params["fb"]))	$this->params["fb"]		= "";
		if(empty($this->params["sv"]))	$this->params["sv"]		= "";
		if(empty($this->params["st"]))	$this->params["st"]		= "";
		if(empty($this->params["ac"]))	$this->params["ac"]		= "";
		if(empty($this->params["nl"]))	$this->params["nl"]		= "";
		if(empty($this->params["to"]))	$this->params["to"]		= "";
		if(empty($this->params["nn"]))	$this->params["nn"]		= "";
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	= '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";

		// Text error
		$output	.=	'<label>{s_label:text}: error<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_er">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_er" id="textCon' . $this->textAreaCount . '_er" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["er"] . '</textarea>' . "\r\n";

		// Text not found (404)
		$output	.=	'<label>{s_label:text}: not found (404)<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_nf">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_nf" id="textCon' . $this->textAreaCount . '_nf" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["nf"] . '</textarea>' . "\r\n";

		// Text forbidden
		$output	.=	'<label>{s_label:text}: forbidden<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_fb">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_fb" id="textCon' . $this->textAreaCount . '_fb" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["fb"] . '</textarea>' . "\r\n";

		// Text server
		$output	.=	'<label>{s_label:text}: server<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_sv">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_sv" id="textCon' . $this->textAreaCount . '_sv" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["sv"] . '</textarea>' . "\r\n";

		// Text status
		$output	.=	'<label>{s_label:text}: status<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_st">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_st" id="textCon' . $this->textAreaCount . '_st" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["st"] . '</textarea>' . "\r\n";

		// Text access
		$output	.=	'<label>{s_label:text}: access<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_ac">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_ac" id="textCon' . $this->textAreaCount . '_ac" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["ac"] . '</textarea>' . "\r\n";

		// Text nologin
		$output	.=	'<label>{s_label:text}: nologin<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_nl">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_nl" id="textCon' . $this->textAreaCount . '_nl" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["nl"] . '</textarea>' . "\r\n";

		// Text timeout
		$output	.=	'<label>{s_label:text}: timeout<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_to">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_to" id="textCon' . $this->textAreaCount . '_to" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["to"] . '</textarea>' . "\r\n";

		// Text nofeed
		$output	.=	'<label>{s_label:text}: nofeed<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_nn">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_nn" id="textCon' . $this->textAreaCount . '_nn" class="cc-editor-add textEditor cc-editor-small teaser">' . $this->params["nn"] . '</textarea>' . "\r\n";

		return $output;
	
	}

}
