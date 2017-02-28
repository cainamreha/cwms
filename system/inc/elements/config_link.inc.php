<?php
namespace Concise;


##############################
###########  Link  ###########
##############################

/**
 * LinkConfigElement class
 * 
 * content type => link
 */
class LinkConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein LinkConfigElement zurück
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
			$this->params[1] = $this->a_POST[$this->conPrefix . '_linktext'];
			$this->params[2] = $this->a_POST[$this->conPrefix . '_linktitle'];
			$this->params[3] = $this->a_POST[$this->conPrefix . '_linktype'];
			$this->params[4] = $this->a_POST[$this->conPrefix . '_linkicon'];
			$this->params[5] = $this->a_POST[$this->conPrefix . '_iconpos'];
			$this->params[6] = $this->a_POST[$this->conPrefix . '_linkclass'];
			$this->params[7] = $this->a_POST[$this->conPrefix . '_linkblock'];
		}

		if(empty($this->params[0])) {
			$this->wrongInput[] = $this->conPrefix;
			$this->error		= '{s_text:chooselink} {s_common:or} {s_text:chooselink2}';
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
			$this->params[4] = "";
		if(!isset($this->params[5]))
			$this->params[5] = "l";
		if(!isset($this->params[6]))
			$this->params[6] = "";
		if(!isset($this->params[7]))
			$this->params[7] = "";
	
		#$this->cssFiles["fonticonpickercss"]		= "extLibs/jquery/fontIconPicker/css/jquery.fonticonpicker.min.css";
		#$this->cssFiles["fonticonpickertheme"]		= "extLibs/jquery/fontIconPicker/themes/dark-grey-theme/jquery.fonticonpicker.darkgrey.min.css";
		#$this->scriptFiles["fonticonpicker"]		= "extLibs/jquery/fontIconPicker/jquery.fonticonpicker.min.js";
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$this->params[0] = htmlspecialchars($this->params[0]);
		$this->params[1] = htmlspecialchars($this->params[1]);
		$this->params[2] = htmlspecialchars($this->params[2]);
		
		// Read (fe-)theme styles
		$this->themeStyles		= $this->getThemeStyles(true);
		

		// Button link classes
		$linkClasses	= array("btndef"	=> "btn-default",
								"btnpri"	=> "btn-primary",
								"btnsec"	=> "btn-secondary",
								"btnsuc"	=> "btn-success",
								"btninf"	=> "btn-info",
								"btnwar"	=> "btn-warning",
								"btndan"	=> "btn-danger",
								"btnlink"	=> "btn-link"
								);

		// Button link classes 2
		$linkClasses2	= array("btnblock"	=> "btn-block",
								"btnlg"		=> "btn-lg",
								"btnsm"		=> "btn-sm",
								"btnxs"		=> "btn-xs"
								);

		// Icon link classes
		$icoClasses	= array(	"bgprimary"	=> "primary",
								"bgsuccess"	=> "success",
								"bginfo"	=> "info",
								"bgwarn"	=> "warning",
								"bgdanger"	=> "danger"
								);

		// Icon link classes 2 (effects)
		$icoClasses2	= array();
		
		if(!empty($this->themeStyles['grid']['icoclass'])) {
			$icoEffects	= explode(",", $this->themeStyles['grid']['icoclass']);
			foreach($icoEffects as $val) {
				$icoClasses2[$val]	= $val;
			}
		}

								
		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;

		$output .=	'<fieldset>' . PHP_EOL;
		
		$output	.=	'<div class="fieldBox cc-box-info right"><label>{s_text:chooselink} {s_common:or} {s_text:chooselink2}</label></div>' . PHP_EOL;
		
		// Links MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "links",
											"type"		=> "links",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"value"		=> "Links {s_label:intern}",
											"icon"		=> "page"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		$output .=	'<label>Link</label>' . PHP_EOL;
		
		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . '</span>' . PHP_EOL;
		
		$output .=	'<input type="text" name="' . $this->conPrefix . '" value="' . $this->params[0] . '" />' . PHP_EOL . 
					'<label>Linktext<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_linktext" class="linkText" value="' . $this->params[1] . '" />' . PHP_EOL .
					'<label>Linktitle<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_linktitle" class="linkText" value="' . $this->params[2] . '" />' . PHP_EOL;
				
		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;

		// Link type
		$output .=	'<div class="chooselinkType leftBox">' . PHP_EOL . 
					'<label>{s_link:type}</label>' . PHP_EOL . 
					'<div class="fieldBox clearfix">' . PHP_EOL . 
					'<label>' . PHP_EOL . 
					'<input type="radio" name="' . $this->conPrefix . '_linktype" value="1"' . ($this->params[3] < 2 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
					'{s_link:textlink}</label>' . PHP_EOL .
					'<label>' . PHP_EOL . 
					'<input type="radio" name="' . $this->conPrefix . '_linktype" value="2"' . ($this->params[3] == 2 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
					'{s_link:buttonlink}</label>' . PHP_EOL .
					'<label>' . PHP_EOL . 
					'<input type="radio" name="' . $this->conPrefix . '_linktype" value="3"' . ($this->params[3] == 3 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
					'{s_link:iconlink}</label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;

		// Icon
		$output .=	'<div class="rightBox">' . PHP_EOL . 
					'<div class="left">' . PHP_EOL . 
					'<label>Icon</label>' . PHP_EOL;
		
		$output .=	'<select id="' . $this->conPrefix . '_linkicon" name="' . $this->conPrefix . '_linkicon">' . PHP_EOL .
					'<option data-value="" value="">{s_common:non}</option>' . PHP_EOL;
		
		
		$themeIcons	= $this->themeStyles['icons'];
		asort($themeIcons);
		
		foreach($themeIcons as $key => $icoClass) {
			
			// Falls nicht Icon-Prefixes
			if($key != "ccicons"
			&& $key != "icons"
			&& $key != "icon"
			) {
				$icArr		=	explode(" ", $icoClass);
				$icoClass	=	reset($icArr);
				$output	   .=	'<option data-value="' . $key. '"' . ($this->params[4] == $key ? ' selected="selected"' : '') . '>icon-' . $icoClass . '</option>' . PHP_EOL;
			}
		}
		
		$output .=	'</select>' . PHP_EOL;
		
		/*
		// alternatively get select from json file
		$output .=	'<input type="text" id="' . $this->conPrefix . '_linkicon" name="' . $this->conPrefix . '_linkicon" />
		<span id="' . $this->conPrefix . '_linkicon_button">
			<button autocomplete="off" type="button" class="btn btn-primary">Load from IcoMoon selection.json</button>
		</span>';
		*/

		if(file_exists(PROJECT_DOC_ROOT . '/themes/' . THEME . '/css/icons.css'))
			$output .=	'<link rel="stylesheet" type="text/css" href="' . PROJECT_HTTP_ROOT . '/' . THEME_DIR . 'css/icons.css" />' . PHP_EOL;
		
		$output .=	'</div>' . PHP_EOL;
		
		// Icon pos
		$output .=	'<div class="left">' . PHP_EOL . 
					'<label>Icon Position</label>' . PHP_EOL .
					'<div class="fieldBox right clearfix">' . PHP_EOL . 
					'<label>' . PHP_EOL . 
					'<input type="radio" name="' . $this->conPrefix . '_iconpos" value="l"' . ($this->params[5] == "l" ? ' checked="checked"' : '') . ' />' . PHP_EOL .
					'{s_common:left}</label>' . PHP_EOL .
					'<label>' . PHP_EOL . 
					'<input type="radio" name="' . $this->conPrefix . '_iconpos" value="r"' . ($this->params[5] == "r" ? ' checked="checked"' : '') . ' />' . PHP_EOL .
					'{s_common:right}</label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset class="buttonLinkClass"' . ($this->params[3] != "2" ? ' style="display:none;"' : '') . '>' . PHP_EOL;
		
		
		// Button link style (class)
		$output .=	'<div class="leftBox">' . PHP_EOL . 
					'<label>Button Style 1</label>' . PHP_EOL;
		
		$output .=	'<select id="' . $this->conPrefix . '_linkclass" name="' . $this->conPrefix . '_linkclass">' . PHP_EOL .
					'<option data-value="" value="">{s_common:non}</option>' . PHP_EOL;
		
		foreach($linkClasses as $key => $linkClass) {
			$output .=	'<option value="' . $key. '"' . ($this->params[6] == $key ? ' selected="selected"' : '') . '>' . $linkClass . '</option>' . PHP_EOL;
		}
		
		$output .=	'</select>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		// Button link style 2 (class)
		$output .=	'<div class="rightBox">' . PHP_EOL . 
					'<label>Button Style 2</label>' . PHP_EOL;
		
		$output .=	'<select id="' . $this->conPrefix . '_linkblock" name="' . $this->conPrefix . '_linkblock">' . PHP_EOL .
					'<option data-value="" value="">{s_common:non}</option>' . PHP_EOL;
		
		foreach($linkClasses2 as $key => $linkClass) {
			$output .=	'<option value="' . $key. '"' . ($this->params[7] == $key ? ' selected="selected"' : '') . '>' . $linkClass . '</option>' . PHP_EOL;
		}
		
		$output .=	'</select>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset class="iconLinkClass"' . ($this->params[3] != "3" ? ' style="display:none;"' : '') . '>' . PHP_EOL;
		
		// Icon link style (class)
		$output .=	'<div class="leftBox">' . PHP_EOL . 
					'<label>Icon Style 1</label>' . PHP_EOL;
		
		$output .=	'<select id="' . $this->conPrefix . '_linkclass" name="' . $this->conPrefix . '_linkclass">' . PHP_EOL .
					'<option data-value="" value="">{s_common:non}</option>' . PHP_EOL;
		
		foreach($icoClasses as $key => $linkClass) {
			$output .=	'<option value="' . $key. '"' . ($this->params[6] == $key ? ' selected="selected"' : '') . '>' . $linkClass . '</option>' . PHP_EOL;
		}
		
		$output .=	'</select>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		// Icon link style 2 (class)
		$output .=	'<div class="rightBox">' . PHP_EOL . 
					'<label>Icon Style 2</label>' . PHP_EOL;
		
		$output .=	'<select id="' . $this->conPrefix . '_linkblock" name="' . $this->conPrefix . '_linkblock">' . PHP_EOL .
					'<option data-value="" value="">{s_common:non}</option>' . PHP_EOL;
		
		foreach($icoClasses2 as $key => $linkClass) {
			$output .=	'<option value="' . $key. '"' . ($this->params[7] == $key ? ' selected="selected"' : '') . '>' . $linkClass . '</option>' . PHP_EOL;
		}
		
		$output .=	'</select>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	$this->getScriptTag($this->conPrefix . '_linkicon', str_replace("-", "", $this->conPrefix));
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		return $output;
	
	}
	

	// getScriptTag
	public function getScriptTag($elemID, $prefix)
	{

		return	'<script>' . PHP_EOL .
				'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({fonticonpickercss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/fontIconPicker/css/jquery.fonticonpicker.min.css"});' . PHP_EOL .
				'head.load({fonticonpickertheme: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/fontIconPicker/themes/dark-grey-theme/jquery.fonticonpicker.darkgrey.min.css"});' . PHP_EOL .
				'head.load({fonticonpicker: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/fontIconPicker/jquery.fonticonpicker.min.js"});' . PHP_EOL .
				'head.ready("fonticonpicker", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'cc.myFontIconPicker' . $prefix . ' = $("#' . $elemID . '").fontIconPicker(
							{
								theme: "fip-darkgrey"
							}
						);' . PHP_EOL .
						/*
						'// Add the event on the button
						$("#' . $elemID . '_button button").on("click", function(e) {
							e.preventDefault();
					 
							// Show processing message
							$(this).prop("disabled", true).html(\'<i class="icon-cog demo-animate-spin"></i> Please wait...\');
					 
							// Get the JSON file
							$.ajax({
								url: "' . THEME_DIR . 'css/selection.json",
								type: "GET",
								dataType: "json"
							})
							.done(function(response) {
					 
								// Get the class prefix
								var classPrefix = response.preferences.fontPref.prefix,
									icomoon_json_icons = [],
									icomoon_json_search = [];
					 
								// For each icon
								$.each(response.icons, function(i, v) {
					 
									// Set the source
									icomoon_json_icons.push( classPrefix + v.properties.name );
					 
									// Create and set the search source
									if ( v.icon && v.icon.tags && v.icon.tags.length ) {
										icomoon_json_search.push( v.properties.name + " " + v.icon.tags.join(" ") );
									} else {
										icomoon_json_search.push( v.properties.name );
									}
								});
					 
								// Set new fonts on fontIconPicker
								cc.myFontIconPicker' . $prefix . '.setIcons(icomoon_json_icons, icomoon_json_search);
					 
								// Show success message and disable
								$("#' . $elemID . '_button button").removeClass("btn-primary").addClass("btn-success").text("Successfully loaded icons").prop("disabled", true);
					 
							})
							.fail(function() {
								// Show error message and enable
								$("#' . $elemID . '_button button").removeClass("btn-primary").addClass("btn-danger").text("Error: Try Again?").prop("disabled", false);
							});
							e.stopPropagation();
						});'.
						*/
		
					'// Set value (icon key)
					$("body #' . $elemID . '").closest("form").find(\'[type="submit"]\').bind("click", function(e){
					
						var formEle	= $(this).closest("form");
						var icoSel	= formEle.find("#' . $elemID . '");
						var selName	= icoSel.attr("name");
						var icoEle	= icoSel.children(":selected");
						var icoKey	= icoEle.attr("data-value") || "";
						icoEle.val(icoKey);
						icoEle.html(icoKey);
						formEle.append(\'<input type="hidden" name="\' + selName + \'" value="\' + icoKey + \'" />\');
						
						return true;
					});
					
					// chooselinkType
					$(".chooselinkType input[type=\'radio\']").bind("click", function(e){
						var linkType	= $(this).val();
						var target2		= $(this).closest(".elements").find(".buttonLinkClass");
						var target3		= $(this).closest(".elements").find(".iconLinkClass");

						if(linkType == 2){
							target2.slideDown();
						}else{
							target2.slideUp();
						}						
						if(linkType == 3){
							target3.slideDown();
						}else{
							target3.slideUp();
						}						
					});' .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
