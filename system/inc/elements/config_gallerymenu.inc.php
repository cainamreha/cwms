<?php
namespace Concise;


##############################
#######  Galerie-Menü  #######
##############################

/**
 * GallerymenuConfigElement class
 * 
 * content type => gallerymenu
 */
class GallerymenuConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein GallerymenuConfigElement zurück
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

		if(isset($this->a_POST[$this->conPrefix])) { // Falls das Formular abgeschickt wurde
			
			$this->params["h"]		= trim($this->a_POST[$this->conPrefix]);
			$this->params["usetags"]= !empty($this->a_POST[$this->conPrefix . '_usetags']) ? 1 : 0;
			$this->params["tags"]	= trim($this->a_POST[$this->conPrefix . '_tags']);
			$this->params["link"]	= trim($this->a_POST[$this->conPrefix . '_linkAlias']);
			$this->params["name"]	= trim($this->a_POST[$this->conPrefix . '_linkNames']);
			$this->params["format"]	= trim($this->a_POST[$this->conPrefix . '_listFormat']);
			$this->params["style"]	= trim($this->a_POST[$this->conPrefix . '_listStyle']);
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

		if(!isset($this->params["h"]))
			$this->params["h"] = "";
		if(!isset($this->params["usetags"]))
			$this->params["usetags"] = 0;
		if(!isset($this->params["tags"]))
			$this->params["tags"] = "";
		if(!isset($this->params["link"]))
			$this->params["link"] = "";
		if(!isset($this->params["name"]))
			$this->params["name"] = "";
		if(!isset($this->params["format"]))
			$this->params["format"] = "";
		if(!isset($this->params["style"]))
			$this->params["style"] = "";

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$this->params["h"]		= htmlspecialchars($this->params["h"]);
		$this->params["tags"]	= htmlspecialchars($this->params["tags"]);
		$this->params["name"]	= htmlspecialchars($this->params["name"]);
		$this->params["name"]	= htmlspecialchars($this->params["name"]);
		
		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;
			
		$output	.=	'<label>{s_label:heading}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '" value="' . $this->params["h"] . '" />' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		
		$output	.=	'<label class="markBox">' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_usetags" id="' . $this->conPrefix . '-usetags" class="toggleDetails" ' . ($this->params["usetags"] ? ' checked="checked"' : '') . ' data-toggle="' . $this->conPrefix . '-togglebox" />' . PHP_EOL .
					'</label>' . PHP_EOL .
					'<label class="inline-label" for="' . $this->conPrefix . '-usetags">{s_label:gallbytags}</label>' . PHP_EOL;
					
		$output .=	'<div class="cc-togglebox ' . $this->conPrefix . '-togglebox"' . ($this->params["usetags"] ? '' : ' style="display:none;"') . '>' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL . 
					'<span class="inline-tags">' . PHP_EOL .
					'<label>{s_label:tags}</label><input type="text" name="' . $this->conPrefix . '_tags" id="' . $this->conPrefix . '-tags" class="cc-gallery-tags" value="' . $this->params["tags"] . '" />' . PHP_EOL .
					$this->getTagEditorScripts() .
					'</span>' . PHP_EOL;
		
		$output .=	'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL .
					'</div>' . PHP_EOL;
					
		$output .=	'<div class="cc-togglebox ' . $this->conPrefix . '-togglebox"' . (!$this->params["usetags"] ? '' : ' style="display:none;"') . '>' . PHP_EOL;
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
					
		// Gallery MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "gallery",
											"type"		=> "gallery",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=gallery",
											"slbclass"	=> "multiple",
											"value"		=> "{s_nav:admingallery}",
											"icon"		=> "gallery"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		// Menu
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'<div class="leftBox">' . PHP_EOL . 
					'<label class="customListLft">{s_label:gallery}</label>' . PHP_EOL . 
					'<textarea name="' . $this->conPrefix . '_linkAlias" id="' . $this->conPrefix . '_linkAlias" class="linkList customList noTinyMCE">' . $this->params["link"] . (strrpos($this->params["link"], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'<div class="rightBox">' . PHP_EOL . 
					'<label class="customListRgt"><span class="editLangFlag">' . $this->editLangFlag . '</span>{s_label:menutitle} {s_label:title2}</label>' . PHP_EOL .
					'<textarea name="' . $this->conPrefix . '_linkNames" id="' . $this->conPrefix . '_linkNames" class="linkNames customList noTinyMCE">' . $this->params["name"] . (strrpos($this->params["name"], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'<br class="clearfloat" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_label:coverpic}">' . PHP_EOL;
		
		// Format
		$output .=	'<div class="leftBox">' . PHP_EOL . 
					'<label>{s_form:format}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_listFormat" id="' . $this->conPrefix . '_listFormat" class="selListFormat">' .
					'<option value="">{s_common:non}</option>' . PHP_EOL .
					'<option value="pills"' . ($this->params["format"] == "pills" ? ' selected="selected"' : '') . '>Pills</option>' . PHP_EOL .
					'<option value="panel"' . ($this->params["format"] == "panel" ? ' selected="selected"' : '') . '>Panel</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Buttonstyle
		$output	 .=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_common:style}</label>' . PHP_EOL .
					'<select name="' . $this->conPrefix . '_listStyle" id="' . $this->conPrefix . '_listStyle" class="selListStyle">' . PHP_EOL .
					'<option value="">{s_option:default}</option>' . PHP_EOL .
					'<option value="def"' . ($this->params["style"] == "def" ? ' selected="selected"' : '') . '>{s_common:style}: default</option>' . PHP_EOL .
					'<option value="pri"' . ($this->params["style"] == "pri" ? ' selected="selected"' : '') . '>{s_common:style}: primary</option>' . PHP_EOL .
					'<option value="suc"' . ($this->params["style"] == "suc" ? ' selected="selected"' : '') . '>{s_common:style}: success</option>' . PHP_EOL .
					'<option value="inf"' . ($this->params["style"] == "inf" ? ' selected="selected"' : '') . '>{s_common:style}: info</option>' . PHP_EOL .
					'<option value="war"' . ($this->params["style"] == "war" ? ' selected="selected"' : '') . '>{s_common:style}: warning</option>' . PHP_EOL .
					'<option value="dan"' . ($this->params["style"] == "dan" ? ' selected="selected"' : '') . '>{s_common:style}: danger</option>' . PHP_EOL .
					'</select>' . PHP_EOL;
		
		$output	.=	'</div>' . PHP_EOL;

		$output	.=	'<br class="clearfloat" />' . PHP_EOL;
		
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
				// Galleries by tags
					// Gallery tags
					'$("#' . $this->conPrefix . '-tags").tagEditor({
						maxLength: 2048,
						forceLowercase: false,
						delimiter: ",;\n",
						autocomplete: {
							position: { collision: "flip" }, // automatic menu position up/down
							source: cc.httpRoot + "/system/access/editGalleries.php?action=allgalltags&type=gallery&gal=all",
							delay: 0,
							minLength: 0
						}
					});' . PHP_EOL .
				
					// Individual galleries
					'$("#' . $this->conPrefix . '_linkAlias, #' . $this->conPrefix . '_linkNames").tagEditor({' . PHP_EOL .
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
					// Mediendatei auswählen (Adminbereich) über ListBox
					'cc.execMultiselectCallbacks = cc.execMultiselectCallbacks || [];' . PHP_EOL .
					'cc.execMultiselectCallbacks.push(function(target, title){' . PHP_EOL .
						'target.tagEditor("addTag", title);' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
