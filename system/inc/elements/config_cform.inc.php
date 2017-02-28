<?php
namespace Concise;


##############################
#####  Kontaktformular  ######
##############################


/**
 * CformConfigElement class
 * 
 * content type => cform
 */
class CformConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein CformConfigElement zurück
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
	
		if(isset($this->a_POST[$this->conPrefix])) {
		
			if(isset($this->a_POST[$this->conPrefix . '_formofadress']) && $this->a_POST[$this->conPrefix . '_formofadress'] == "on")
				$this->params["foa"] = 1;
			else
				$this->params["foa"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_title']) && $this->a_POST[$this->conPrefix . '_title'] == "on")
				$this->params["title"] = 1;
			else
				$this->params["title"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_name']) && $this->a_POST[$this->conPrefix . '_name'] == "on")
				$this->params["name"] = 1;
			else
				$this->params["name"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_firstname']) && $this->a_POST[$this->conPrefix . '_firstname'] == "on")
				$this->params["fname"] = 1;
			else
				$this->params["fname"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_company']) && $this->a_POST[$this->conPrefix . '_company'] == "on")
				$this->params["com"] = 1;
			else
				$this->params["com"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_email']) && $this->a_POST[$this->conPrefix . '_email'] == "on")
				$this->params["mail"] = 1;
			else
				$this->params["mail"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_phone']) && $this->a_POST[$this->conPrefix . '_phone'] == "on")
				$this->params["phone"] = 1;
			else
				$this->params["phone"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_subject']) && $this->a_POST[$this->conPrefix . '_subject'] == "on")
				$this->params["subj"] = 1;
			else
				$this->params["subj"] = 0;
			if(!empty($this->a_POST[$this->conPrefix . '_subjectitems']))
				$this->params["subji"] = $this->a_POST[$this->conPrefix . '_subjectitems'];
			else
				$this->params["subji"] = "";
			if(isset($this->a_POST[$this->conPrefix . '_message']) && $this->a_POST[$this->conPrefix . '_message'] == "on")
				$this->params["mes"] = 1;
			else
				$this->params["mes"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_copy']) && $this->a_POST[$this->conPrefix . '_copy'] == "on")
				$this->params["copy"] = 1;
			else
				$this->params["copy"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_captcha']) && $this->a_POST[$this->conPrefix . '_captcha'] == "on")
				$this->params["cap"] = 1;
			else
				$this->params["cap"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_format']) && $this->a_POST[$this->conPrefix . '_format'] != "")
				$this->params["form"] = trim($this->a_POST[$this->conPrefix . '_format']);
			else
				$this->params["form"] = "block";
			if(isset($this->a_POST[$this->conPrefix . '_labels']) && $this->a_POST[$this->conPrefix . '_labels'] == "on")
				$this->params["lab"] = 1;
			else
				$this->params["lab"] = 0;
			if(isset($this->a_POST[$this->conPrefix . '_legend']) && $this->a_POST[$this->conPrefix . '_legend'] == "on")
				$this->params["leg"] = 1;
			else
				$this->params["leg"] = 0;
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// Updatestring
		$this->dbUpdateStr = "'" . $this->DB->escapeString(json_encode($this->params)) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		// Nichtgesetzte Indizes setzen
		if(empty($this->params["foa"])) $this->params["foa"] = 0;
		if(empty($this->params["title"])) $this->params["title"] = 0;
		if(!isset($this->params["name"])) $this->params["name"] = 1;
		if(empty($this->params["fname"])) $this->params["fname"] = 0;
		if(!isset($this->params["mail"])) $this->params["mail"] = 1;
		if(empty($this->params["phone"])) $this->params["phone"] = 0;
		if(empty($this->params["com"])) $this->params["com"] = 0;
		if(empty($this->params["subj"])) $this->params["subj"] = 0;
		if(empty($this->params["subji"])) $this->params["subji"] = "";
		if(!isset($this->params["mes"])) $this->params["mes"] = 1;
		if(empty($this->params["copy"])) $this->params["copy"] = 0;
		if(!isset($this->params["cap"])) $this->params["cap"] = 1;
		if(empty($this->params["form"])) $this->params["form"] = "block";
		if(empty($this->params["lab"])) $this->params["lab"] = 0;
		if(!isset($this->params["leg"])) $this->params["leg"] = 1;
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;
		
		$output .=	'<legend>{s_header:formfields}</legend>' . PHP_EOL .
					'<label class="markAll markBox" data-mark="#formFields-' . $this->conPrefix . '"><input type="checkbox" id="' . $this->conPrefix . '-markAll" data-select="all" /></label>' .
					'<label for="' . $this->conPrefix . '-markAll" class="markAllLB inline-label">{s_label:mark}</label>' . PHP_EOL .
					'<span class="separator">&nbsp;</span>' . PHP_EOL .
					'<div id="formFields-' . $this->conPrefix . '">' . PHP_EOL .
					'<div class="leftBox clear">' . PHP_EOL;
		
		// Anrede
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_formofadress" id="' . $this->conPrefix . '_formofadress" ' . ($this->params["foa"] == 1 ? ' checked="checked"' : '') . ' /></label><label class="inline-label" for="' . $this->conPrefix . '_formofadress">' . PHP_EOL .
					'{s_form:anrede}</label>' . PHP_EOL;
							
		// Titel
		$output	.= 	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_title" id="' . $this->conPrefix . '_title" ' . ($this->params["title"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_title">' . PHP_EOL .
					'{s_form:grade}</label>' . PHP_EOL;
							
		// Name
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_name" id="' . $this->conPrefix . '_name" ' . ($this->params["name"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_name">' . PHP_EOL .
					'{s_form:name}</label>' . PHP_EOL;

		// Vorname
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_firstname" id="' . $this->conPrefix . '_firstname" ' . ($this->params["fname"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_firstname">' . PHP_EOL .
					'{s_label:userFN}</label>' . PHP_EOL;

		// Firma
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_company" id="' . $this->conPrefix . '_company" ' . ($this->params["com"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_company">' . PHP_EOL .
					'{s_form:company}</label>' . PHP_EOL;

		// E-Mail
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_email" id="' . $this->conPrefix . '_email" ' . ($this->params["mail"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_email">' . PHP_EOL .
					'{s_form:email}</label>' . PHP_EOL;

		// Phone
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_phone" id="' . $this->conPrefix . '_phone" ' . ($this->params["phone"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_phone">' . PHP_EOL .
					'{s_form:phone}</label>' . PHP_EOL;

		$output	.=	'</div>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL;

		// Betreff
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_subject" id="' . $this->conPrefix . '_subject" ' . ($this->params["subj"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_subject">' . PHP_EOL .
					'{s_form:subject}</label>' . PHP_EOL;
		
		// Betreffzeilen
		$output	.=	'<label class="subjectItems">{s_form:subject} {s_label:fieldoptions}' . PHP_EOL . 
					'<textarea name="' . $this->conPrefix . '_subjectitems" id="' . $this->conPrefix . '_subjectitems" class="subjectItemsList noTinyMCE">' . $this->params["subji"] . (strrpos($this->params["subji"], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea></label>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;

		// Nachricht
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_message" id="' . $this->conPrefix . '_message" ' . ($this->params["mes"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_message">' . PHP_EOL .
					'{s_form:message}</label>' . PHP_EOL;

		// Kopie
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_copy" id="' . $this->conPrefix . '_copy" ' . ($this->params["copy"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_copy">' . PHP_EOL .
					'{s_form:copy}</label>' . PHP_EOL;

		// Captcha
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_captcha" id="' . $this->conPrefix . '_captcha" ' . ($this->params["cap"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_captcha">' . PHP_EOL .
					'{s_form:captcha}</label>' . PHP_EOL;

		$output	.=	'</div>' . PHP_EOL;
		$output	.=	'</div>' . PHP_EOL;

		// Format
		$output	.=	'<br class="clearfloat">' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_form:format}">' . PHP_EOL;
		$output .=	'<legend>{s_form:format}</legend>' . PHP_EOL .
					'<div class="leftBox"><br />' . PHP_EOL .
					'<select name="' . $this->conPrefix . '_format" id="' . $this->conPrefix . '_format">' . PHP_EOL .
					'<option value="block">{s_form:block}</option>' . PHP_EOL .
					'<option value="inline"' . ($this->params["form"] == "inline" ? ' selected="selected"' : '') . '>{s_form:inline}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;

		// Labels
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<br /><label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_labels" id="' . $this->conPrefix . '_labels" ' . ($this->params["lab"] == 1 ? ' checked="checked"' : '') . ' /></label><label class="inline-label" for="' . $this->conPrefix . '_labels">' . PHP_EOL .
					'{s_form:hidelabels}</label>' . PHP_EOL .
					'</div>' . PHP_EOL;

		// Legend
		$output	.=	'<br class="clearfloat"><br />' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_legend" id="' . $this->conPrefix . '_legend" ' . ($this->params["leg"] == 1 ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_legend">' . PHP_EOL .
					'{s_label:formtitle}/{s_form:legend}</label>' . PHP_EOL;

		$output	.=	'<input type="hidden" name="' . $this->conPrefix . '" value="true" />' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		// Script
		$output	.= $this->getScriptTag();

		return $output;
	
	}
	

	// getScriptTag
	public function getScriptTag()
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
				'head.load({tagEditorcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.css"});' . PHP_EOL .
				'head.load({tagEditorcaret: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.caret.min.js"});' . PHP_EOL .
				'head.load({tagEditor: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.js"});' . PHP_EOL .
				'head.ready("tagEditor", function(){' . PHP_EOL .
				'$("document").ready(function(){' . PHP_EOL .
				'$("#' . $this->conPrefix . '_subjectitems").tagEditor({' . PHP_EOL .
				'maxLength: 2048,' . PHP_EOL .
				'forceLowercase: false,' . PHP_EOL .
				'delimiter: "\n",' . PHP_EOL .
				'onChange: function(field, editor, tags){' . PHP_EOL .
					'editor.next(".deleteAllTags-panel").remove();' . PHP_EOL .
					'if(tags.length > 0 && !editor.next(".deleteAllTags-panel").length){ editor.after(\'<span class="deleteAllTags-panel buttonPanel"><button class="deleteAllTags cc-button button button-small button-icon-only btn right" type="button" role="button" title="{s_javascript:removeall}"><span class="cc-admin-icons cc-icons cc-icon-cancel-circle">&nbsp;</span></button><br class="clearfloat" /></span>\'); }' . PHP_EOL .
					'editor.next(".deleteAllTags-panel").children(".deleteAllTags").click(function(){' . PHP_EOL .
						'for (i = 0; i < tags.length; i++) { field.tagEditor("removeTag", tags[i]); }' . PHP_EOL .
					'});' . PHP_EOL .
				'}' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
