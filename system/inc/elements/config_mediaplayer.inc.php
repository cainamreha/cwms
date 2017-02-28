<?php
namespace Concise;


##############################
#######  Media-Player  #######
##############################


/**
 * MediaplayerConfigElement class
 * 
 * content type => mediaplayer
 */
class MediaplayerConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein MediaplayerConfigElement zurück
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
	
		if(isset($this->a_POST[$this->conPrefix . '_files']))
			$this->params[0] = $this->a_POST[$this->conPrefix . '_files'];
			
		if(isset($this->a_POST[$this->conPrefix . '_titles']))						
			$this->params[1] = $this->a_POST[$this->conPrefix . '_titles'];
			
		if(isset($this->a_POST[$this->conPrefix . '_poster']))						
			$this->params[2] = $this->a_POST[$this->conPrefix . '_poster'];
			
		if(isset($this->a_POST[$this->conPrefix . '_artist']))						
			$this->params[3] = $this->a_POST[$this->conPrefix . '_artist'];
			
		if(isset($this->a_POST[$this->conPrefix . '_skin'])) {						
			$this->params[4] = $this->a_POST[$this->conPrefix . '_skin'];
			
			if(isset($this->a_POST[$this->conPrefix . '_type']) && $this->a_POST[$this->conPrefix . '_type'] == 1)
				$this->params[5] = 1;
			else
				$this->params[5] = 0;
				
			if(isset($this->a_POST[$this->conPrefix . '_width']) && is_numeric($this->a_POST[$this->conPrefix . '_width']))
				$this->params[6] = (int)$this->a_POST[$this->conPrefix . '_width'];
			else
				$this->params[6] = 640;
					
			if(isset($this->a_POST[$this->conPrefix . '_height']) && is_numeric($this->a_POST[$this->conPrefix . '_height']))
				$this->params[7] = (int)$this->a_POST[$this->conPrefix . '_height'];
			else
				$this->params[7] = 360;
				
			if(isset($this->a_POST[$this->conPrefix . '_play']) && $this->a_POST[$this->conPrefix . '_play'] == "on")
				$this->params[8] = 1;
			else
				$this->params[8] = 0;
					
			if(isset($this->a_POST[$this->conPrefix . '_link']) && $this->a_POST[$this->conPrefix . '_link'] == "on")
				$this->params[9] = 1;
			else
				$this->params[9] = 0;
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$audioConValue = implode("<>", $this->params);
		$this->dbUpdateStr = "'" . $this->DB->escapeString($audioConValue) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.Contents.php"; // Contentsklasse einbinden
		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.AudioContent.php"; // AudioContent-Klasse einbinden

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
		if(!isset($this->params[6]))
			$this->params[6] = 640;
		if(!isset($this->params[7]))
			$this->params[7] = 360;
		if(!isset($this->params[8]))
			$this->params[8] = 0;
		if(!isset($this->params[9]))
			$this->params[9] = 0;
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;
		
		// Audio MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "audio",
											"type"		=> "audio",
											"url"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&type=audio&i=' .$this->conNum,
											"path"		=> PROJECT_HTTP_ROOT . '/' . CC_AUDIO_FOLDER . '/',
											"slbclass"	=> "multiple",
											"value"		=> "{s_button:audiofolder}",
											"icon"		=> "audio"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);		
		
		// Video MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "video",
											"type"		=> "video",
											"url"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&type=video&i=' .$this->conNum,
											"path"		=> PROJECT_HTTP_ROOT . '/' . CC_VIDEO_FOLDER . '/',
											"slbclass"	=> "multiple",
											"value"		=> "{s_button:videofolder}",
											"icon"		=> "video"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'<div class="cc-filelist-box leftBox">' . PHP_EOL .
					'<label class="customListLft">{s_label:mediafiles}</label>' . PHP_EOL .
					'<textarea name="' . $this->conPrefix . '_files" id="' . $this->conPrefix . '_files" class="fileList customList noTinyMCE">' . htmlspecialchars($this->params[0]) . (strrpos($this->params[0], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'<div class="cc-filetitles-box rightBox">' . PHP_EOL .
					'<label class="customListRgt"><span class="editLangFlag">' . $this->editLangFlag . '</span>{s_label:mediatitles}</label>' . PHP_EOL .
					'<textarea name="' . $this->conPrefix . '_titles" id="' . $this->conPrefix . '_titles" class="fileTitles customList noTinyMCE">' . htmlspecialchars($this->params[1]) . (strrpos($this->params[1], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_label:coverpic}">' . PHP_EOL;
		
		// Images MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "images",
											"type"		=> "images",
											"url"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&type=images&i=' .$this->conNum,
											"path"		=> PROJECT_HTTP_ROOT . '/' . CC_IMAGE_FOLDER . '/',
											"slbclass"	=> "multiple",
											"value"		=> "{s_button:imgfolder}",
											"icon"		=> "image"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'<div class="cc-imglist-box leftBox">' . PHP_EOL .
					'<label class="customListLft">{s_label:coverpic}</label>' . PHP_EOL .
					'<textarea name="' . $this->conPrefix . '_poster" id="' . $this->conPrefix . '_poster" class="coverPics customList noTinyMCE">' . htmlspecialchars($this->params[2]) . (strrpos($this->params[2], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'<div class="rightBox">' . PHP_EOL .
					'<label class="customListRgt">{s_label:artist}</label>' . PHP_EOL .
					'<textarea name="' . $this->conPrefix . '_artist" id="' . $this->conPrefix . '_artist" class="artists customList noTinyMCE">' . htmlspecialchars($this->params[3]) . (strrpos($this->params[3], PHP_EOL, -4) ? PHP_EOL : '') . '</textarea>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'<br class="clearfloat" />' . PHP_EOL;
	
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_label:format}">' . PHP_EOL;
					
		$output .=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:playerskin}</label>' . PHP_EOL .
					'<select name="' . $this->conPrefix . '_skin">' . PHP_EOL;
		
		// Skinordner ins Array einlesen
		$folder = PROJECT_DOC_ROOT . '/extLibs/jquery/jplayer/skin';
		
		if(is_dir($folder)) {
		
			$handle = opendir($folder);
		
			while($content = readdir($handle)) {
				if( $content != ".." && 
					strpos($content, ".") !== 0
				) {
					$output	.=	'<option value="' . $content . '"' . ($this->params[4] == $content ? ' selected="selected"' : '') . '>' . $content . '</option>';
				}
			}
			closedir($handle);
		}
		
		$output	.=	'</select>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'<div class="rightBox">' . PHP_EOL .
					'<label>{s_form:format}</label>' . PHP_EOL .
					'<div class="fieldBox clearfix">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_type1">' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_type" id="' . $this->conPrefix . '_type1" value="0"' . ($this->params[5] == 0 ? ' checked="checked"' : '') . ' /> Single Player</label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_type2">' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_type" id="' . $this->conPrefix . '_type2" value="1"' . ($this->params[5] == 1 ? ' checked="checked"' : '') . ' /> Playlist</label>' . PHP_EOL .
					'</div>' . PHP_EOL . 
					'</div><br class="clearfloat" />' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL .
					'<div class="halfBox">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_width">{s_label:width}</label>' . PHP_EOL .
					'<input class="numSpinner" type="text" name="' . $this->conPrefix . '_width" value="' . htmlspecialchars($this->params[6]) . '" id="' . $this->conPrefix . '_width" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="halfBox">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_height">{s_label:height}</label>' . PHP_EOL .
					'<input class="numSpinner" type="text" name="' . $this->conPrefix . '_height" value="' . htmlspecialchars($this->params[7]) . '" id="' . $this->conPrefix . '_height" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div><label>&nbsp;</label>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL .
					'<div class="fieldBox clearfix">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_play">Autoplay' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_play" id="' . $this->conPrefix . '_play"' . ($this->params[8] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_link">{s_label:download}' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_link" id="' . $this->conPrefix . '_link"' . ($this->params[9] == 1 ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL .
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
				'$( "#' . $this->conPrefix . '_files,' .
					'#' . $this->conPrefix . '_titles,' .
					'#' . $this->conPrefix . '_poster,' .
					'#' . $this->conPrefix . '_artist"' .
				').tagEditor({' . PHP_EOL .
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
