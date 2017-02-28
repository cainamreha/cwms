<?php
namespace Concise;



/**
 * CardsElement
 * 
 */

class CardsElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein CardsElement zurück
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
		###########  Cards  ###########
		##############################

		$output		= "";						
		$cardsCon	= (array)json_decode($this->conValue, true);
		$cardsList	= "";
		$cardItems	= array();
		$cardsDivs	= array();
		$classExt	= "";
		
		if(!count($cardsCon))
			return $this->getContentElementWrapper($this->conAttributes, $output, $classExt);
		
		$cardsCon_h			= isset($cardsCon[0]) ? $cardsCon[0] : array();
		$cardsCon_con		= isset($cardsCon[1]) ? $cardsCon[1] : array();
		$cardsCon_f 		= isset($cardsCon[2]) ? $cardsCon[2] : array();
		$cardsCon_img		= isset($cardsCon[3]) ? $cardsCon[3] : array();
		$cardsCon_img_align	= isset($cardsCon[4]) ? $cardsCon[4] : array();
		$cardsCon_img_link	= isset($cardsCon[5]) ? $cardsCon[5] : array();
		$cardsCon_col		= isset($cardsCon[6]) ? $cardsCon[6] : array();
		$cardStyle			= isset($cardsCon[7]) ? $cardsCon[7] : array();
		$cardFormat			= isset($cardsCon[8]) ? $cardsCon[8] : array();
		$cardsCon_align		= isset($cardsCon[9]) ? $cardsCon[9] : array();
		$cardsCon_id		= isset($cardsCon[10]) ? $cardsCon[10] : array();
		$cardsCon_class		= isset($cardsCon[11]) ? $cardsCon[11] : array();
			
		
		// Cards		
		$classExt	=	'cc-cards cardsWrapper' . ($cardsCon[12] == "deck" ? ' {t_class:carddeckwrap}' : '');
		$output	   .=	'<div class="cc-cards-group' . (!empty($cardsCon[12]) ? ' {t_class:card' . htmlspecialchars($cardsCon[12]) . '}' : '') . '">';
		$i	= 1;
		
		foreach($cardsCon_h as $key => $val) {
		
			$panelClass		= "cc-card-panel {t_class:panel}";
			$conClass		= $cardsCon[15] == "hov" ? ' show-on-hover' : ' show-on-load';
			$cardImg		= "";
			$cardImgPos		= $cardsCon[14] == "ovl" ? 'ovl' : $cardsCon_img_align[$key];
			// Link attributes
			$linkAttr		= Modules::getLinkAttr($cardsCon_img_link[$key]);
						
			// class align
			if($cardsCon_align[$key] == "left")
				$panelClass	.= ' {t_class:alignlft}';
			elseif($cardsCon_align[$key] == "right")
				$panelClass	.= ' {t_class:alignrgt}';
			else
				$panelClass	.= ' {t_class:centertxt}';
			
			// class outline
			if($cardFormat[$key] == "outl") {
				// style
				$styleSfx	= "";
				if(!empty($cardStyle[$key]))
					$styleSfx	= '{t_class:sfx' . htmlspecialchars($cardStyle[$key]) . '}';
				elseif(!empty($cardsCon[13]))
					$panelClass	.= '{t_class:sfx' . htmlspecialchars($cardsCon[13]) . '}';
				$panelClass	.= ' {t_class:cardoutlpfx}' . $styleSfx;
			}
			else {
				// class inverse
				if($cardFormat[$key] == "inv")
					$panelClass	.= ' {t_class:cardinv}';				
				// style
				if(!empty($cardStyle[$key]))
					$panelClass	.= ' {t_class:pfxpanel}{t_class:sfx' . htmlspecialchars($cardStyle[$key]) . '}';
				elseif(!empty($cardsCon[13]))
					$panelClass	.= ' {t_class:pfxpanel}{t_class:sfx' . htmlspecialchars($cardsCon[13]) . '}';
			}
			
			if(!empty($cardsCon_img[$key])) {
				$cardImg		= '<img class="{t_class:cardimg' . ($cardImgPos && $cardImgPos != "mid" && $cardImgPos != "ovl" ? $cardImgPos : '') . '} {t_class:imgnf} {t_class:center}" src="' . PROJECT_HTTP_ROOT . '/' . Modules::getImagePath($cardsCon_img[$key], true) . '" alt="" />';
				if(!empty($linkAttr[0]))
					$cardImg	= '<a href="' . $linkAttr[0] . '">' . $cardImg . '</a>';
				$cardImg	= '<figure>' . $cardImg . '</figure>';
			}
			
			// Card class
			if(!empty($cardsCon_class[$key]))
				$panelClass	.= ' ' . $cardsCon_class[$key];
				
			
			// Card grid
			if(!empty($cardsCon_col[$key]))
				$output	   .=	'<div class="' . $this->getColumnGridClass($cardsCon_col[$key]) . '">';
			
			// Card panel
			$output	   .=	'<div' . (!empty($cardsCon_id[$key]) ? ' id="' . htmlspecialchars($cardsCon_id[$key]) . '"' : '') . ' class="' . $panelClass . '">';
			
			// img top
			if($cardImgPos == "top"
			|| $cardImgPos == "ovl"
			)
				$output	   .=	$cardImg;
			
			// img overlay
			if($cardImgPos == "ovl")
				$output	   .=	'<div class="{t_class:cardimgovl}' . $conClass . '">' . PHP_EOL .
								'<div class="{t_class:ctrcentered}">' . PHP_EOL;
			
			// Cards header
			if($val != "")
				$output	   .=	'<div class="{t_class:panelhead}">' . PHP_EOL .
									$val .
								'</div>' . PHP_EOL;
			
			// img middle
			if($cardImgPos == ""
			|| $cardImgPos == "mid"
			)
				$output	   .=	$cardImg;
			
			$output	   .=	'<div class="{t_class:panelbody}' . ($cardImgPos != "ovl" ? $conClass : '') . '">' . PHP_EOL .
								$cardsCon_con[$key] .
							'</div>' . PHP_EOL;
			
			if(!empty($cardsCon_f[$key]))
				$output	   .=	'<div class="{t_class:panelfoot}">' . PHP_EOL .
									$cardsCon_f[$key] .
								'</div>' . PHP_EOL;
			
			// img bottom
			if($cardImgPos == "bot")
				$output	   .=	$cardImg;
			
			// img overlay
			if($cardImgPos == "ovl")
				$output	   .=	'</div>' . PHP_EOL .
								'</div>' . PHP_EOL;
							
			$output	   .=	'</div>' . PHP_EOL; // close card panel
			
			if(!empty($cardsCon_col[$key]))
				$output	   .=	'</div>' . PHP_EOL; // close grid
							
			$i++;
		
		}
		$output	   .=	'</div>' . PHP_EOL; // close cards panel
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, $classExt);
		
		return $output;
	
	}	
	
}
