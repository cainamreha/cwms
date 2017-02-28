<?php
namespace Concise;


##############################
#########  Regpage  ##########
##############################

/**
 * RegpageConfigElement class
 * 
 * content type => login
 */
class RegpageConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein RegpageConfigElement zurück
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
	
		if(isset($this->a_POST[$this->conPrefix . '_regsubject']))
			$this->params["regsubject"] = $this->a_POST[$this->conPrefix . '_regsubject'];
		if(isset($this->a_POST[$this->conPrefix . '_regthank']))
			$this->params["regthank"] = $this->a_POST[$this->conPrefix . '_regthank'];
		if(isset($this->a_POST[$this->conPrefix . '_regmessage']))
			$this->params["regmessage"] = $this->a_POST[$this->conPrefix . '_regmessage'];
		if(isset($this->a_POST[$this->conPrefix . '_regtextnewsl']))
			$this->params["regtextnewsl"] = $this->a_POST[$this->conPrefix . '_regtextnewsl'];
		if(isset($this->a_POST[$this->conPrefix . '_regtextguest']))
			$this->params["regtextguest"] = $this->a_POST[$this->conPrefix . '_regtextguest'];
		if(isset($this->a_POST[$this->conPrefix . '_regtextshop']))
			$this->params["regtextshop"] = $this->a_POST[$this->conPrefix . '_regtextshop'];
		if(isset($this->a_POST[$this->conPrefix . '_reguser']))
			$this->params["reguser"] = $this->a_POST[$this->conPrefix . '_reguser'];
		if(isset($this->a_POST[$this->conPrefix . '_regnewsl']))
			$this->params["regnewsl"] = $this->a_POST[$this->conPrefix . '_regnewsl'];
		if(isset($this->a_POST[$this->conPrefix . '_unregnewsl']))
			$this->params["unregnewsl"] = $this->a_POST[$this->conPrefix . '_unregnewsl'];
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{
	
		// Pfade durch Platzhalter ersetzen (erfolgt zwar auch durch Javascript in HeadExt, aber nicht schnell genug (Image-Ladeproblem))
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

		if(empty($this->params["regsubject"])) $this->params["regsubject"]		= "";
		if(empty($this->params["regthank"])) $this->params["regthank"]			= "";
		if(empty($this->params["regmessage"])) $this->params["regmessage"]		= "";
		if(empty($this->params["regtextnewsl"])) $this->params["regtextnewsl"]	= "";
		if(empty($this->params["regtextguest"])) $this->params["regtextguest"]	= "";
		if(empty($this->params["regtextshop"])) $this->params["regtextshop"]	= "";
		if(empty($this->params["reguser"])) $this->params["reguser"]			= "";
		if(empty($this->params["regnewsl"])) $this->params["regnewsl"]			= "";
		if(empty($this->params["unregnewsl"])) $this->params["unregnewsl"]		= "";
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	= '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . "\r\n";

		$output	.= '<h5 class="cc-h5">{s_label:formmail}</h5>' . "\r\n";

		// Text regsubject
		$output	.=	'<label>{s_label:subject}: E-Mail<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<input type="text" name="' . $this->conPrefix . '_regsubject" id="' . $this->conPrefix . '_regsubject" value="' . $this->params["regsubject"] . '" />' . "\r\n";

		// Text regthank
		$output	.=	'<label>{s_label:text}: regthank<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<input type="text" name="' . $this->conPrefix . '_regthank" id="' . $this->conPrefix . '_regthank" value="' . $this->params["regthank"] . '" />' . "\r\n";

		// Text regmessage
		$output	.=	'<label>{s_label:text}: regmessage<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_regmessage">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_regmessage" id="textCon' . $this->textAreaCount . '_regmessage" class="cc-editor-add textEditor smallEditor teaser">' . $this->params["regmessage"] . '</textarea>' . "\r\n";

		// Text regtextnewsl
		$output	.=	'<label>{s_label:text}: regtextnewsl<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_regtextnewsl">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_regtextnewsl" id="textCon' . $this->textAreaCount . '_regtextnewsl" class="cc-editor-add textEditor smallEditor teaser">' . $this->params["regtextnewsl"] . '</textarea>' . "\r\n";

		// Text regtextguest
		$output	.=	'<label>{s_label:text}: regtextguest<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_regtextguest">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_regtextguest" id="textCon' . $this->textAreaCount . '_regtextguest" class="cc-editor-add textEditor smallEditor teaser">' . $this->params["regtextguest"] . '</textarea>' . "\r\n";

		// Text regtextshop
		$output	.=	'<label>{s_label:text}: regtextshop<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_regtextshop">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_regtextshop" id="textCon' . $this->textAreaCount . '_regtextshop" class="cc-editor-add textEditor smallEditor teaser">' . $this->params["regtextshop"] . '</textarea>' . "\r\n";


		$output	.= '<br /><br />' . "\r\n";
		$output	.= '<h5 class="cc-h5">{s_javascript:alerttitle}</h5>' . "\r\n";
		
		// Text reguser
		$output	.=	'<label>{s_label:text}: reguser<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_reguser">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_reguser" id="textCon' . $this->textAreaCount . '_reguser" class="cc-editor-add textEditor smallEditor teaser">' . $this->params["reguser"] . '</textarea>' . "\r\n";

		// Text regnewsl
		$output	.=	'<label>{s_label:text}: regnewsl<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_regnewsl">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_regnewsl" id="textCon' . $this->textAreaCount . '_regnewsl" class="cc-editor-add textEditor smallEditor teaser">' . $this->params["regnewsl"] . '</textarea>' . "\r\n";

		// Text unregnewsl
		$output	.=	'<label>{s_label:text}: unregnewsl<span class="toggleEditor" data-target="textCon' . $this->textAreaCount . '_unregnewsl">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . "\r\n" .
					'<textarea name="' . $this->conPrefix . '_unregnewsl" id="textCon' . $this->textAreaCount . '_unregnewsl" class="cc-editor-add textEditor smallEditor teaser">' . $this->params["unregnewsl"] . '</textarea>' . "\r\n";

		return $output;
	
	}

}
