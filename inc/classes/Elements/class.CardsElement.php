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
		
		$cardsCon_h			= isset($cardsCon["head"]) ? $cardsCon["head"] : array();
		$cardsCon_con		= isset($cardsCon["con"]) ? $cardsCon["con"] : array();
		$cardsCon_f 		= isset($cardsCon["foot"]) ? $cardsCon["foot"] : array();
		$cardsCon_img		= isset($cardsCon["img"]) ? $cardsCon["img"] : array();
		$cardsCon_img_align	= isset($cardsCon["ia"]) ? $cardsCon["ia"] : array();
		$cardsCon_img_link	= isset($cardsCon["li"]) ? $cardsCon["li"] : array();
		$cardsCon_col		= isset($cardsCon["col"]) ? $cardsCon["col"] : array();
		$cardStyle			= isset($cardsCon["cs"]) ? $cardsCon["cs"] : array();
		$cardFormat			= isset($cardsCon["cf"]) ? $cardsCon["cf"] : array();
		$cardsCon_align		= isset($cardsCon["al"]) ? $cardsCon["al"] : array();
		$cardsCon_id		= isset($cardsCon["id"]) ? $cardsCon["id"] : array();
		$cardsCon_class		= isset($cardsCon["cl"]) ? $cardsCon["cl"] : array();
		$cardsCon_tt		= isset($cardsCon["tt"]) ? $cardsCon["tt"] : array();
			
		if(!isset($cardsCon["fa"]))		$cardsCon["fa"]		= "";
		if(!isset($cardsCon["sa"]))		$cardsCon["sa"]		= "";
		if(!isset($cardsCon["ovl"]))	$cardsCon["ovl"]	= "";
		if(!isset($cardsCon["hov"]))	$cardsCon["hov"]	= "";

		
		// if flip card
		if($cardsCon["ovl"] == "2") {
			#$this->cssFiles[]	= 'access/css/flipcard.css'; // load via resource loader
			$this->scriptCode[]	= $this->getFlipcardScript();
		}
		
		
		// Cards		
		$classExt	=	'cc-cards cardsWrapper' . ($cardsCon["fa"] == "deck" ? ' {t_class:carddeckwrap}' : '');
		$output	   .=	'<div class="cc-cards-group' . (!empty($cardsCon["fa"]) ? ' {t_class:card' . htmlspecialchars($cardsCon["fa"]) . '}' : '') . '">';
		$i	= 1;
		
		foreach($cardsCon_h as $key => $val) {
		
			$panelClass		= 'cc-card-panel' . ($cardsCon["ovl"] == "2" ? ' cc-flipcard-panel' : '') . ' {t_class:panel}';
			$panelTooltip	= "";
			$conClass		= $cardsCon["hov"] == "1" ? ' show-on-hover' : ($cardsCon["ovl"] == "2" ? '' : ' show-on-load');
			$conClass	   .= $cardsCon["ovl"] == "2" ? ' cc-flipcard-back' : '';
			$cardImg		= "";
			$cardImgPos		= $cardsCon["ovl"] == "1" || $cardsCon["ovl"] == "2" ? 'ovl' : $cardsCon_img_align[$key];
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
				elseif(!empty($cardsCon["sa"]))
					$panelClass	.= '{t_class:sfx' . htmlspecialchars($cardsCon["sa"]) . '}';
				$panelClass	.= ' {t_class:cardoutlpfx}' . $styleSfx;
			}
			else {
				// class inverse
				if($cardFormat[$key] == "inv")
					$panelClass	.= ' {t_class:cardinv}';				
				// style
				if(!empty($cardStyle[$key]))
					$panelClass	.= ' {t_class:pfxpanel}{t_class:sfx' . htmlspecialchars($cardStyle[$key]) . '}';
				elseif(!empty($cardsCon["sa"]))
					$panelClass	.= ' {t_class:pfxpanel}{t_class:sfx' . htmlspecialchars($cardsCon["sa"]) . '}';
			}
			
			if(!empty($cardsCon_img[$key])) {
				$figClass		= 'cc-card-figure' . ($cardsCon["ovl"] == "2" ? ' cc-flipcard-front' : '');
				$cardImgAlt		= pathinfo($cardsCon_img[$key], PATHINFO_FILENAME);
				$cardImg		= '<img class="{t_class:cardimg' . ($cardImgPos && $cardImgPos != "mid" && $cardImgPos != "ovl" ? $cardImgPos : '') . '} {t_class:imgnf} {t_class:center}" src="' . PROJECT_HTTP_ROOT . '/' . Modules::getImagePath($cardsCon_img[$key], true) . '" alt="' . $cardImgAlt . '" />';
				if(!empty($linkAttr[0]))
					$cardImg	= '<a href="' . $linkAttr[0] . '">' . $cardImg . '</a>';
				$cardImg	= '<figure class="' . $figClass . '">' . $cardImg . '</figure>';
			}
			
			// Card class
			if(!empty($cardsCon_class[$key]))
				$panelClass	.= ' ' . $cardsCon_class[$key];
			
			// Card tooltip
			if(!empty($cardsCon_tt[$key]))
				$panelTooltip	= ' title="' . $cardsCon_tt[$key] . '"';
				
			
			// Card grid
			if(!empty($cardsCon_col[$key]))
				$output	   .=	'<div class="' . $this->getColumnGridClass($cardsCon_col[$key]) . '">';
			
			// Card panel
			$output	   .=	'<div' . (!empty($cardsCon_id[$key]) ? ' id="' . htmlspecialchars($cardsCon_id[$key]) . '"' : '') . ' class="' . $panelClass . '"' . $panelTooltip . '>';
			
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
	

	/**
	 * getFlipcardScript
	 * 
	 * @access	public
     * @return  string
	 */
	public function getFlipcardScript()
	{
	
		return 'head.load("' . PROJECT_HTTP_ROOT . '/access/css/flipcard.css");
				$(document).ready(function(){
					$(".cc-card-panel").hover(function(){
						$(this).addClass("cc-flipcard-flip");
					},function(){
						$(this).removeClass("cc-flipcard-flip");
					});
				});' . PHP_EOL;
	
	}

}
