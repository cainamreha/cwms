<?php
namespace Concise;


##############################
######  Bildergalerie  #######
##############################


/**
 * GalleryConfigElement class
 * 
 * content type => gallery
 */
class GalleryConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein GalleryConfigElement zurück
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
		$this->params	= explode("/", $this->conValue);

		
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

			$this->params[0]	= trim($this->a_POST[$this->conPrefix]);
			$this->params[1]	= trim($this->a_POST[$this->conPrefix . '_gallery']);
			$this->params[2]	= trim($this->a_POST[$this->conPrefix . '_header']);
			$this->params[3]	= isset($this->a_POST[$this->conPrefix . '_showtitle']) ? 1 : '';
			$this->params[4]	= isset($this->a_POST[$this->conPrefix . '_showtext']) ? 1 : '';
			$this->params[5]	= isset($this->a_POST[$this->conPrefix . '_uselink']) ? 1 : '';
			$this->params[6]	= trim($this->a_POST[$this->conPrefix . '_maximgnumber']);
			$this->params[7]	= (int)$this->a_POST[$this->conPrefix . '_auto'];
			$this->params[8]	= (int)$this->a_POST[$this->conPrefix . '_continous'];
			$this->params[9]	= (int)$this->a_POST[$this->conPrefix . '_speed'];
			$this->params[10]	= (int)$this->a_POST[$this->conPrefix . '_pause'];
			$this->params[11]	= (int)$this->a_POST[$this->conPrefix . '_controls'];
			$this->params[12]	= trim($this->a_POST[$this->conPrefix . '_controlstype']);
			
			if($this->params[0] == "" && $this->params[1] != "archive") {
				$this->wrongInput[] = $this->conPrefix;
				$this->error =  "{s_error:choosegall}";
			}
			elseif(!preg_match("/^[A-Za-z0-9_-]+$/", $this->params[0])) {
				$this->wrongInput[] = $this->conPrefix;
				$this->error =  "{s_notice:checkgallname}";
			}
			elseif(strlen($this->params[0]) > 64) {
				$this->wrongInput[] = $this->conPrefix;
				$this->error = "{s_notice:longname}";
			}
			elseif(!is_dir(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $this->params[0])) {
				$this->wrongInput[] = $this->conPrefix;
				$this->error =  "{s_error:newgall}";
			}
			if(strpos($this->params[2], "/") !== false || strlen($this->params[2]) > 256) {
				$this->wrongInput[$this->conPrefix] = "{s_error:check}";
			}
			if($this->params[6] != "" && (!is_numeric($this->params[6]) || strlen($this->params[6]) > 4)) {
				$this->wrongInput[$this->conPrefix . '_maximgnumber'] = "{s_error:check}";
			}
		
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$this->dbUpdateStr = "'" . $this->DB->escapeString(implode("/", $this->params)) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{
		
		// Spinner script code
		$this->scriptCode[]	= $this->getSpinnerScriptCode();

		
		if(!isset($this->params[0]))
			$this->params[0] = "";
		if(!isset($this->params[1]))
			$this->params[1] = "";
		if(!isset($this->params[2]))
			$this->params[2] = "";
		if(!isset($this->params[3]))
			$this->params[3] = 0;
		if(!isset($this->params[4]))
			$this->params[4] = 0;
		if(!isset($this->params[5]))
			$this->params[5] = 0;
		if(!isset($this->params[6]))
			$this->params[6] = "";
		if(!isset($this->params[7]) || $this->params[7] == "")
			$this->params[7] = 1; // auto
		if(!isset($this->params[8]))
			$this->params[8] = 1; // continous
		if(!isset($this->params[9]))
			$this->params[9] = 1200; // speed
		if(!isset($this->params[10]))
			$this->params[10] = 3500; // pause
		if(!isset($this->params[11]))
			$this->params[11] = 1; // controls
		if(!isset($this->params[12]))
			$this->params[12] = "num"; // controls-type

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$this->params[0]	= htmlspecialchars($this->params[0]);
		$this->params[1]	= htmlspecialchars($this->params[1]);
		$this->params[2]	= htmlspecialchars($this->params[2]);
		$this->params[6]	= htmlspecialchars($this->params[6]);
		$this->params[12]	= htmlspecialchars($this->params[12]);

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;
		
		$output	.=	'<div class="openGallElement right">' . PHP_EOL;

		// Button goto gallery
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=gallery&' . (!empty($this->error) ? 'name' : 'edit_gall') . '=' . $this->params[0],
								"class"		=> "button-icon-only right",
								"title"		=> "{s_title:goto} {s_nav:admingallery}",
								"text"		=> (!empty($this->error) ? '{s_link:newgall}' : ' &raquo;'),
								"icon"		=> "gallery"
							);
		
		$output	.=	parent::getButtonLink($btnDefs);

		
		
		// Falls bereits eine Galerie ausgewählt, Button zum Öffnen der listBox einbinden
		if(!empty($this->params[0])) {
			
			$redirect	= urlencode(ADMIN_HTTP_ROOT . '?' . $GLOBALS['_SERVER']['QUERY_STRING'] . '&connr=' . $this->conNum . '#con' . $this->conNum);

			$mediaListButtonDef		= array(	"class"	 	=> "gallery",
												"type"		=> "gallery",
												"url"		=> SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=edit&type=gallery&gal=' . $this->params[0],
												"redirect"	=> $redirect,
												"value"		=> $this->params[0],
												"title"		=> "{s_title:editgall}",
												"icon"		=> "gallery"
											);
			
			$output .=	$this->getButtonMediaList($mediaListButtonDef);
		}
		
		$output .=	'</div>' . PHP_EOL;

		$mediaListButtonDef		= array(	"class"	 	=> "gallery",
											"type"		=> "gallery",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=gallery",
											"value"		=> "{s_button:gallchoose}",
											"title"		=> "{s_button:gallchoose}",
											"icon"		=> "gallery"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<label>{s_label:gallname}</label>' . PHP_EOL;
		
		
		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . PHP_EOL . 
						'<a href="' . ADMIN_HTTP_ROOT . '?task=modules&type=gallery&name=' . $this->params[0] . '">{s_link:newgall}</a></span>' . PHP_EOL;
							
		$output	.=	'<input type="text" name="' . $this->conPrefix . '" value="' . $this->params[0] . '" maxlength="256" />' . PHP_EOL . 
					'<input type="hidden" name="' . $this->conPrefix . '_gallID" value="' . $this->params[0] . '" />' . PHP_EOL .
					'<label>{s_label:heading}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
						
						
		if(array_key_exists($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.= '<span class="notice">' . $this->wrongInput[$this->conPrefix] . '</span>' . PHP_EOL;

		$output	.= 	'<input type="text" name="' . $this->conPrefix . '_header" value="' . $this->params[2] . '" maxlength="256" />' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:galltype}</label>' . PHP_EOL . 
					self::getGalleryTypes($this->conPrefix, $this->params[1]);
						
		$output .=	'</div>' . PHP_EOL;
		$output	.=	'<p class="clearfloat">&nbsp;</p>' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;
		
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_showtitle" id="' . $this->conPrefix . '-showtitle"' . ($this->params[3] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-showtitle" class="inline-label">{s_label:showimgtitle}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_showtext" id="' . $this->conPrefix . '-showtext"' . ($this->params[4] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-showtext" class="inline-label">{s_label:showimgtext}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_uselink" id="' . $this->conPrefix . '-uselink"' . ($this->params[5] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-uselink" class="inline-label">{s_label:useimglink}</label>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;
						
		$output	.=	'<label>{s_label:maximgnumber}</label>' . PHP_EOL;
		
		if(array_key_exists($this->conPrefix . '_maximgnumber', $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.= '<span class="notice">' . $this->wrongInput[$this->conPrefix . '_maximgnumber'] . '</span>' . PHP_EOL;

						
		$output	.=	'<input class="picNumSpinner" type="text" name="' . $this->conPrefix . '_maximgnumber" value="' . $this->params[6] . '" maxlength="4" />' . PHP_EOL;

		// Slider-Options
		$output	.=	'<div class="sliderOptions' . (stripos($this->params[1], "slide") === false ? ' hide' : '') . '">' . PHP_EOL;
						
		// auto
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:auto}</label>' . PHP_EOL .
					'<select class="iconSelect" name="' . $this->conPrefix . '_auto">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[7] ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
						
		// pause
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:pause} [ms]</label>' . PHP_EOL .
					'<input class="mscNumSpinner" type="text" name="' . $this->conPrefix . '_pause" value="' . htmlspecialchars($this->params[10]) . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
						
		// continous
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:continous}</label>' . PHP_EOL .
					'<select class="iconSelect" name="' . $this->conPrefix . '_continous">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[8] ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
						
		// speed
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:speed} [ms]</label>' . PHP_EOL .
					'<input class="mscNumSpinner" type="text" name="' . $this->conPrefix . '_speed" value="' . htmlspecialchars($this->params[9]) . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
					
		// controls
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:slidercontrols}</label>' . PHP_EOL .
					'<select class="iconSelect" name="' . $this->conPrefix . '_controls">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[11] ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
						
		// controls-type
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:slidercontrolstype} [ms]</label>' . PHP_EOL .
					'<select class="select" name="' . $this->conPrefix . '_controlstype">' . PHP_EOL .
					'<option value="num">{s_option:numeric}</option>' . PHP_EOL .
					'<option value="img"' . ($this->params[12] == "img" ? ' selected="selected"' : '') . '>{s_option:imagecontrols}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;

		$output	.=	'<br class="clearfloat" />' . PHP_EOL;
		$output	.=	'</div>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}
	
	
	// getSpinnerScriptCode
	public function getSpinnerScriptCode()
	{
	
		$output	=	'head.ready("ui", function(){' .
						'$(document).ready(function(){' .
							'$( ".picNumSpinner" ).spinner({min:1, max:999});' .
							'$( ".mscNumSpinner" ).spinner({min:0, max:99900, step:100});' .
						'});' .
					'});';
		
		return $output;
	
	}

}
