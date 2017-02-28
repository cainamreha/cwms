<?php
namespace Concise;



/**
 * LinkElement
 * 
 */

class LinkElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein LinkElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles, Wrap)
	 */
	public function __construct($options, $DB, &$o_lng, &$o_page)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->o_page			= $o_page;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
		$this->conTable			= $options["conTable"];

	}
	

	/**
	 * Element erstellen
	 * 
	 * @access	public
     * @return  string
	 */
	public function getElement()
	{

		##############################
		###########  Link  ###########
		##############################
		
		$linkCon	= explode("<>", $this->conValue);
		$linkClass	= "";
		$icon		= "";
		$iconSep	= "";
		
		if(!isset($linkCon[0]))
			$linkCon[0] = "";
		if(!isset($linkCon[1]))
			$linkCon[1] = "";
		if(!isset($linkCon[2]))
			$linkCon[2] = "";
		if(!isset($linkCon[3]))
			$linkCon[3] = "";
		if(!isset($linkCon[4]))
			$linkCon[4] = "";
		if(!isset($linkCon[5]))
			$linkCon[5] = "l";
		if(!isset($linkCon[6]))
			$linkCon[6] = "";
		if(!isset($linkCon[7]))
			$linkCon[7] = "";

		
		// Link attributes
		$linkAttr	= Modules::getLinkAttr($linkCon[0]);
		
		$output		= '<a href="' . $linkAttr[0] . '"';
		$linkClass	= $linkAttr[1];
		
		
		// If button link
		if($linkCon[3] == "2") {
		
			$linkClass .= " {t_class:btn}";
			
			// If Style
			if($linkCon[6] != "")
				$linkClass .= " {t_class:" . $linkCon[6] . "}";
			else
				$linkClass .= " {t_class:btnpri}";
		
			// If Style2
			if($linkCon[7] != "")
				$linkClass .= " {t_class:" . $linkCon[7] . "}";
		}
		
		// If icon link
		if($linkCon[3] == "3") {
		
			$icoWrapClass	= "";
			$linkClass 		.= " " . parent::getIcon($linkCon[4], "", "", "", "", false);
			
			// If Style
			if($linkCon[6] != "")
				$linkClass .= " {t_class:" . $linkCon[6] . "}";
		
			// If Style2 (effect)
			if($linkCon[7] != "")
				$icoWrapClass = "ci-icon-" . $linkCon[7];
		}
		
		// If add Icon
		elseif($linkCon[4] != "") {
			$icon		= parent::getIcon($linkCon[4], "", "", "");
		
			// If text, icon separator
			if($linkCon[1] != "")
				$iconSep	= "&nbsp;";
		}
		
		if($this->conAttributes['class'] != ""
		&& strpos($this->conAttributes['class'], "popup") !== false
		) {
			$linkClass .=	$this->conAttributes['class'];
			$this->scriptFiles[] 	= JS_DIR . "popup.js";
		}
		
		$output .=	' class="' . $linkClass . '"' .
					($linkCon[2] != "" ? ' title="' . $linkCon[2] . '"' : '') . '>' .
					($linkCon[5] == "l" ? $icon . $iconSep : '') . $linkCon[1] . ($linkCon[5] == "r" ? $iconSep . $icon : '') .
					'</a>' . PHP_EOL;
		
		// If icon link
		if($linkCon[3] == "3")
			$output	= '<div class="' . $icoWrapClass . '">' . $output	. '</div>' . PHP_EOL;
		
		$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		return $output;
	
	}	
	
}
