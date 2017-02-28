<?php
namespace Concise;


##############################
##########  Menu  ############
##############################

/**
 * MenuConfigElement class
 * 
 * content type => menu
 */
class MenuConfigElement extends ConfigElementFactory implements ConfigElements
{

	/**
	 * Gibt ein MenuConfigElement zurück
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
			$this->params[1] = trim($this->a_POST[$this->conPrefix . '_menutype']);
			$this->params[2] = isset($this->a_POST[$this->conPrefix . '_menuactive']) ? trim($this->a_POST[$this->conPrefix . '_menuactive']) : "";
			$this->params[3] = isset($this->a_POST[$this->conPrefix . '_menuseparator']) ? trim($this->a_POST[$this->conPrefix . '_menuseparator']) : "";
			$this->params[4] = isset($this->a_POST[$this->conPrefix . '_menubase']) ? 1 : 0;
			$this->params[5] = isset($this->a_POST[$this->conPrefix . '_menubaseext']) ? $this->a_POST[$this->conPrefix . '_menubaseext'] : "";
			$this->params[6] = trim($this->a_POST[$this->conPrefix . '_menustyle']);
			$this->params[7] = isset($this->a_POST[$this->conPrefix . '_menufixed']) ? trim($this->a_POST[$this->conPrefix . '_menufixed']) : "";
			$this->params[8] = isset($this->a_POST[$this->conPrefix . '_searchbar']) ? 1 : 0;
			$this->params[9] = isset($this->a_POST[$this->conPrefix . '_menulogo']) ? 1 : 0;
			$this->params[10] = isset($this->a_POST[$this->conPrefix . '_menualign']) ? trim($this->a_POST[$this->conPrefix . '_menualign']) : "";
			$this->params[11] = isset($this->a_POST[$this->conPrefix . '_langmenu']) ? 1 : 0;
			$this->params[12] = isset($this->a_POST[$this->conPrefix . '_menuitemalign']) ? trim($this->a_POST[$this->conPrefix . '_menuitemalign']) : "";
			$this->params[13] = isset($this->a_POST[$this->conPrefix . '_collapsible']) ? trim($this->a_POST[$this->conPrefix . '_collapsible']) : "";
			
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
			$this->params[1] = "main";
		if(!isset($this->params[2]))
			$this->params[2] = 1;
		if(!isset($this->params[3]))
			$this->params[3] = "";
		if(!isset($this->params[4]))
			$this->params[4] = 0;
		if(!isset($this->params[5]))
			$this->params[5] = "";
		if(!isset($this->params[6]))
			$this->params[6] = 1;
		if(!isset($this->params[7]))
			$this->params[7] = 0;
		if(!isset($this->params[8]))
			$this->params[8] = 0;
		if(!isset($this->params[9]))
			$this->params[9] = 0;
		if(!isset($this->params[10]))
			$this->params[10] = 0;
		if(!isset($this->params[11]))
			$this->params[11] = 0;
		if(!isset($this->params[12]))
			$this->params[12] = 0;
		if(!isset($this->params[13]))
			$this->params[13] = 1;
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$this->params[0] = htmlspecialchars($this->params[0]);
		$this->params[1] = htmlspecialchars($this->params[1]);
		$this->params[2] = htmlspecialchars($this->params[2]);
		$this->params[3] = htmlspecialchars($this->params[3]);
		$this->params[5] = htmlspecialchars($this->params[5]);
		$this->params[6] = htmlspecialchars($this->params[6]);
		
		// Menüauswahl
		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL; 
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;
		
		$output .=	'<label>{s_label:heading}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '" value="' . $this->params[0] . '" /><br />' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:menu}</label>' . PHP_EOL .
					'<select name="' . $this->conPrefix . '_menutype">' . PHP_EOL .
					'<option value="main"' . ($this->params[1] == "main" ? ' selected="selected"' : '') . '>{s_option:mainmenu}</option>' . PHP_EOL .
					'<option value="top"' . ($this->params[1] == "top" ? ' selected="selected"' : '') . '>{s_option:topmenu}</option>' . PHP_EOL .
					'<option value="foot"' . ($this->params[1] == "foot" ? ' selected="selected"' : '') . '>{s_option:footmenu}</option>' . PHP_EOL .
					'<option value="bc"' . ($this->params[1] == "bc" ? ' selected="selected"' : '') . '>{s_option:bcnav}</option>' . PHP_EOL .
					'<option value="sub"' . ($this->params[1] == "sub" ? ' selected="selected"' : '') . '>{s_option:submenu}</option>' . PHP_EOL .
					'<option value="parroot"' . ($this->params[1] == "parroot" ? ' selected="selected"' : '') . '>{s_option:parrootmenu}</option>' . PHP_EOL .
					'<option value="parsub"' . ($this->params[1] == "parsub" ? ' selected="selected"' : '') . '>{s_option:parsubmenu}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Menüstyle
		$output	 .=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:menustyle}</label>' . PHP_EOL .
					'<select name="' . $this->conPrefix . '_menustyle">' . PHP_EOL .
					'<option value="0"' . ($this->params[6] == 0 ? ' selected="selected"' : '') . '>{s_common:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[6] == 1 ? ' selected="selected"' : '') . '>{s_option:default}</option>' . PHP_EOL .
					'<option value="2"' . ($this->params[6] == 2 ? ' selected="selected"' : '') . '>{s_option:alternative}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
		
		if($this->params[1] == "main"
		|| $this->params[1] == "top"
		) {			
		
			// Menualign
			$output	 .=	'<div class="leftBox">' . PHP_EOL .
						'<label>{s_common:alignment}</label>' . PHP_EOL .
						'<select name="' . $this->conPrefix . '_menualign">' . PHP_EOL .
						'<option value="0">auto</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[10] == 1 ? ' selected="selected"' : '') . '>{s_common:left}</option>' . PHP_EOL .
						'<option value="2"' . ($this->params[10] == 2 ? ' selected="selected"' : '') . '>{s_common:right}</option>' . PHP_EOL .
						'<option value="3"' . ($this->params[10] == 3 ? ' selected="selected"' : '') . '>{s_common:centered}</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
			
			// Menu position
			$output	 .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_label:menufixed}</label>' . PHP_EOL .
						'<select class="iconSelect" name="' . $this->conPrefix . '_menufixed">' . PHP_EOL .
						'<option value="0"' . ($this->params[7] == 0 ? ' selected="selected"' : '') . '>{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[7] == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'<option value="2"' . ($this->params[7] == 2 ? ' selected="selected"' : '') . '>Affix on scroll</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
			
			$output	 .=	'<br class="clearfloat" />' . PHP_EOL;
		}
		
		if($this->params[1] != "bc") {
		
			// Menu list type
			$output	 .=	'<div class="leftBox">' . PHP_EOL .
						'<label>{s_common:alignment} {s_label:menuitems}</label>' . PHP_EOL .
						'<select class="noTrueFalse" name="' . $this->conPrefix . '_menuitemalign">' . PHP_EOL .
						'<option value="0"' . ($this->params[12] == 0 ? ' selected="selected"' : '') . '>{s_common:horizontal}</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[12] == 1 ? ' selected="selected"' : '') . '>{s_common:vertical}</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
			
			// Menu collapsible
			$output	 .=	'<div class="rightBox">' . PHP_EOL .
						'<label>{s_label:menucollapsible}</label>' . PHP_EOL .
						'<select class="iconSelect" name="' . $this->conPrefix . '_collapsible">' . PHP_EOL .
						'<option value="0"' . ($this->params[13] == 0 ? ' selected="selected"' : '') . '>{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->params[13] == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'<option value="2"' . ($this->params[13] == 2 ? ' selected="selected"' : '') . '>{s_label:alwayscollapse}</option>' . PHP_EOL .
						'</select>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
			$output	 .=	'<br class="clearfloat" />' . PHP_EOL;
		}
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_form:format}">' . PHP_EOL;
		
		// Eigenschaften
		// Linkart
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:menuactive}</label>' . PHP_EOL .
					'<div class="fieldBox left clearfix">' . PHP_EOL .
					'<label for="menuActive-' . $this->conPrefix . '-0">Link' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_menuactive" value="1" id="menuActive-' . $this->conPrefix . '-0" ' . ($this->params[2] == 1 ? 'checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="menuActive-' . $this->conPrefix . '-1">Text' . PHP_EOL .
					'<input type="radio" name="' . $this->conPrefix . '_menuactive" value="0" id="menuActive-' . $this->conPrefix . '-1" ' . ($this->params[2] == 0 ? 'checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Separator
		$output	.=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:menuseparator}</label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_menuseparator" value="' . $this->params[3] . '" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" /><br />' . PHP_EOL;
		
		if($this->params[1] != "bc") {

			// Basegroup
			$output	.=	'<div class="leftBox">' . PHP_EOL .
						'<label for="' . $this->conPrefix . '-menubase">{s_label:menubase}</label>' . PHP_EOL .
						'<label class="markBox">' . PHP_EOL .
						'<input type="checkbox" name="' . $this->conPrefix . '_menubase" id="' . $this->conPrefix . '-menubase"' . ($this->params[4] == 1 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<div class="rightBox">' . PHP_EOL .
						'<label>{s_label:menubaseext}</label>' . PHP_EOL .
						'<input type="text" name="' . $this->conPrefix . '_menubaseext" value="' . $this->params[5] . '" />' . PHP_EOL .
						'</div>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL;
		}
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		if($this->params[1] == "main"
		|| $this->params[1] == "top"
		|| $this->params[1] == "foot"
		) {
		
			$output .=	'<fieldset data-tab="{s_label:menulogo}">' . PHP_EOL;
			
			if(defined('CC_SITE_LOGO')
			&& CC_SITE_LOGO != ""
			&& file_exists(PROJECT_DOC_ROOT . '/' . CC_SITE_LOGO)
			) {
			
				// Logo
				$output	.=	'<div class="leftBox">' . PHP_EOL .
							'<label for="' . $this->conPrefix . '-menulogo">{s_label:menulogo}</label>' . PHP_EOL .
							'<label class="markBox">' . PHP_EOL .
							'<input type="checkbox" name="' . $this->conPrefix . '_menulogo" id="' . $this->conPrefix . '-menulogo"' . ($this->params[9] == 1 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
							'</label>' . PHP_EOL .
							'<img src="' . PROJECT_HTTP_ROOT . '/' . CC_SITE_LOGO . '" alt="website-logo ' . $_SERVER['SERVER_NAME'] . '" />' . PHP_EOL .
							'</div>' . PHP_EOL;
			}
		
			// Search bar
			$output	.=	'<div class="rightBox">' . PHP_EOL .
						'<label for="' . $this->conPrefix . '-searchbar">{s_label:searchbar}</label>' . PHP_EOL .
						'<label class="markBox">' . PHP_EOL .
						'<input type="checkbox" name="' . $this->conPrefix . '_searchbar" id="' . $this->conPrefix . '-searchbar"' . ($this->params[8] == 1 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
						'</label>' . PHP_EOL .
						'</div>' . PHP_EOL;
			
			if(count($this->o_lng->installedLangs) > 1) {
			
				// Lang menu
				$output	.=	'<div class="rightBox">' . PHP_EOL .
							'<label for="' . $this->conPrefix . '-langmenu">{s_option:lang}</label>' . PHP_EOL .
							'<label class="markBox">' . PHP_EOL .
							'<input type="checkbox" name="' . $this->conPrefix . '_langmenu" id="' . $this->conPrefix . '-langmenu"' . ($this->params[11] == 1 ? ' checked="checked"' : '') . ' />' . PHP_EOL .
							'</label>' . PHP_EOL .
							'</div>' . PHP_EOL;
			}
			
			$output	.=	'<br class="clearfloat" />' . PHP_EOL;
		
			$output .=	'</fieldset>' . PHP_EOL;
		}

		return $output;
	
	}

}
