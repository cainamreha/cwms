<?php
namespace Concise;


##############################
##########  1D-Menü  #########
##############################

/**
 * ListmenuConfigElement class
 * 
 * content type => listmenu
 */
class ListmenuConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein ListmenuConfigElement zurück
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
			$this->params[1] = trim($this->a_POST[$this->conPrefix . '_linkAlias']);
			$this->params[2] = trim($this->a_POST[$this->conPrefix . '_linkNames']);
			$this->params[3] = trim($this->a_POST[$this->conPrefix . '_listStyle']);
			$this->params[4] = isset($this->a_POST[$this->conPrefix . '_menustyle']) ? trim($this->a_POST[$this->conPrefix . '_menustyle']) : '';
			$this->params[5] = isset($this->a_POST[$this->conPrefix . '_menufixed']) ? trim($this->a_POST[$this->conPrefix . '_menufixed']) : '';
			$this->params[6] = isset($this->a_POST[$this->conPrefix . '_searchbar']) ? 1 : 0;
			$this->params[7] = isset($this->a_POST[$this->conPrefix . '_menulogo']) ? 1 : 0;
			$this->params[8] = isset($this->a_POST[$this->conPrefix . '_menualign']) ? trim($this->a_POST[$this->conPrefix . '_menualign']) : '';
			$this->params[9] = isset($this->a_POST[$this->conPrefix . '_langmenu']) ? 1 : 0;
			$this->params[10] = isset($this->a_POST[$this->conPrefix . '_menuitemalign']) ? trim($this->a_POST[$this->conPrefix . '_menuitemalign']) : '';
			$this->params[11] = isset($this->a_POST[$this->conPrefix . '_collapsible']) ? trim($this->a_POST[$this->conPrefix . '_collapsible']) : '';
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

		if(!isset($this->params[0]))
			$this->params[0] = "";
		if(!isset($this->params[1]))
			$this->params[1] = "";
		if(!isset($this->params[2]))
			$this->params[2] = "";
		if(!isset($this->params[3]))
			$this->params[3] = "";
		if(!isset($this->params[4]))
			$this->params[4] = 1;
		if(!isset($this->params[5]))
			$this->params[5] = 0;
		if(!isset($this->params[6]))
			$this->params[6] = 0;
		if(!isset($this->params[7]))
			$this->params[7] = 0;
		if(!isset($this->params[8]))
			$this->params[8] = 0;
		if(!isset($this->params[9]))
			$this->params[9] = 0;
		if(!isset($this->params[10]))
			$this->params[10] = 0;
		if(!isset($this->params[11]))
			$this->params[11] = 1;
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{
	
		$this->params[0] = htmlspecialchars($this->params[0]);
		$this->params[1] = htmlspecialchars($this->params[1]);
		$this->params[2] = htmlspecialchars($this->params[2]);

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;

		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$this->output	.= '<span class="notice">' . $this->error . '</span>' . PHP_EOL;
			
		$output	.=	'<label>{s_label:heading}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '" value="' . $this->params[0] . '" />' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
		
		// Links MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "links",
											"type"		=> "links",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"slbclass"	=> "multiple",
											"value"		=> "Links {s_label:intern}",
											"icon"		=> "page"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		// Menu
		$output .=	'<span class="fieldBox cc-box-info right"><label>{s_text:chooselink} {s_common:or} {s_text:chooselink2}</label></span>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;

		// Link
		$output	 .=	'<div class="leftBox">' . PHP_EOL .
					'<label class="">Link' . PHP_EOL . 
					'<textarea name="' . $this->conPrefix . '_linkAlias" id="' . $this->conPrefix . '_linkAlias" class="linkList noTinyMCE">' . $this->params[1] . (strrpos($this->params[1], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea></label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL .
					'<label class=""><span class="editLangFlag">' . $this->editLangFlag . '</span>{s_label:menutitle} {s_label:title2}' . PHP_EOL .
					'<textarea name="' . $this->conPrefix . '_linkNames" id="' . $this->conPrefix . '_linkNames" class="linkNames noTinyMCE">' . $this->params[2] . (strrpos($this->params[2], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea></label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_form:format}">' . PHP_EOL;
		
		// Format
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_form:format}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_listStyle" id="' . $this->conPrefix . '_listStyle" class="selListStyle">' .
					'<option value="">{s_common:non}</option>' . PHP_EOL .
					'<option value="nav"' . ($this->params[3] == "nav" ? ' selected="selected"' : '') . '>Nav</option>' . PHP_EOL .
					'<option value="pills"' . ($this->params[3] == "pills" ? ' selected="selected"' : '') . '>Pills</option>' . PHP_EOL .
					'<option value="panel"' . ($this->params[3] == "panel" ? ' selected="selected"' : '') . '>Panel</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		
		if($this->params[3] == "nav") {
		
			// Menüstyle
			$output	 .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_label:menustyle}</label>' . PHP_EOL .
						'<select name="' . $this->conPrefix . '_menustyle">' . PHP_EOL .
						'<option value="0"' . ($this->params[4] == 0 ? ' selected="selected"' : '') . '>{s_common:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[4] == 1 ? ' selected="selected"' : '') . '>{s_option:default}</option>' . PHP_EOL .
						'<option value="2"' . ($this->params[4] == 2 ? ' selected="selected"' : '') . '>{s_option:alternative}</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
			$output	 .=	'<br class="clearfloat" />' . PHP_EOL;
		
			// Menüalign
			$output	 .=	'<div class="leftBox">' . PHP_EOL .
						'<label>{s_common:alignment}</label>' . PHP_EOL .
						'<select name="' . $this->conPrefix . '_menualign">' . PHP_EOL .
						'<option value="0">auto</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[8] == 1 ? ' selected="selected"' : '') . '>{s_common:left}</option>' . PHP_EOL .
						'<option value="2"' . ($this->params[8] == 2 ? ' selected="selected"' : '') . '>{s_common:right}</option>' . PHP_EOL .
						'<option value="3"' . ($this->params[8] == 3 ? ' selected="selected"' : '') . '>{s_common:centered}</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
			
			// Menü position
			$output	 .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_label:menufixed}</label>' . PHP_EOL .
						'<select class="iconSelect" name="' . $this->conPrefix . '_menufixed">' . PHP_EOL .
						'<option value="0"' . ($this->params[5] == 0 ? ' selected="selected"' : '') . '>{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[5] == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'<option value="2"' . ($this->params[5] == 2 ? ' selected="selected"' : '') . '>Affix on scroll</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL;
			
			// Menu list type
			$output	 .=	'<div class="leftBox">' . PHP_EOL .
						'<label>{s_common:alignment} {s_label:menuitems}</label>' . PHP_EOL .
						'<select class="noTrueFalse" name="' . $this->conPrefix . '_menuitemalign">' . PHP_EOL .
						'<option value="0"' . ($this->params[10] == 0 ? ' selected="selected"' : '') . '>{s_common:horizontal}</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[10] == 1 ? ' selected="selected"' : '') . '>{s_common:vertical}</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
			
			// Menu collapsible
			$output	 .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_label:menucollapsible}</label>' . PHP_EOL .
						'<select class="iconSelect" name="' . $this->conPrefix . '_collapsible">' . PHP_EOL .
						'<option value="0"' . ($this->params[11] == 0 ? ' selected="selected"' : '') . '>{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[11] == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'<option value="2"' . ($this->params[11] == 2 ? ' selected="selected"' : '') . '>{s_label:alwayscollapse}</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
			$output	 .=	'<br class="clearfloat" />' . PHP_EOL;
		
			$output .=	'</fieldset>' . PHP_EOL;
			
			$output .=	'<fieldset data-tab="{s_form:format}">' . PHP_EOL;
			
			if(defined('CC_SITE_LOGO')
			&& CC_SITE_LOGO != ""
			&& file_exists(PROJECT_DOC_ROOT . '/' . CC_SITE_LOGO)
			) {
			
				// Logo
				$output	.=	'<div class="leftBox">' . PHP_EOL .
							'<label for="' . $this->conPrefix . '-menulogo">{s_label:menulogo}</label>' . PHP_EOL .
							'<label class="markBox">' . PHP_EOL .
							'<input type="checkbox" name="' . $this->conPrefix . '_menulogo" id="' . $this->conPrefix . '-menulogo"' . ($this->params[7] == 1 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
							'</label>' . PHP_EOL .
							'<img src="' . PROJECT_HTTP_ROOT . '/' . CC_SITE_LOGO . '" alt="website-logo ' . $_SERVER['SERVER_NAME'] . '" />' . PHP_EOL .
							'</div>' . PHP_EOL;
			}
		
			// Search bar
			$output	.=	'<div class="rightBox">' . PHP_EOL .
						'<label for="' . $this->conPrefix . '-searchbar">{s_label:searchbar}</label>' . PHP_EOL .
						'<label class="markBox">' . PHP_EOL .
						'<input type="checkbox" name="' . $this->conPrefix . '_searchbar" id="' . $this->conPrefix . '-searchbar"' . ($this->params[6] == 1 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'</div>' . PHP_EOL;
			
			if(count($this->o_lng->installedLangs) > 1) {
			
				// Lang menu
				$output	.=	'<div class="rightBox">' . PHP_EOL .
							'<label for="' . $this->conPrefix . '-langmenu">{s_option:lang}</label>' . PHP_EOL .
							'<label class="markBox">' . PHP_EOL .
							'<input type="checkbox" name="' . $this->conPrefix . '_langmenu" id="' . $this->conPrefix . '-langmenu"' . ($this->params[9] == 1 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
							'</label>' . PHP_EOL .
							'</div>' . PHP_EOL;
			}
			
		}
		
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
