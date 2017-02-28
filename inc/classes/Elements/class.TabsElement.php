<?php
namespace Concise;



/**
 * TabsElement
 * 
 */

class TabsElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein TabsElement zurück
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
		###########  Tabs  ###########
		##############################

		$output		= "";						
		$tabsCon	= explode("<|>", $this->conValue);
		$tabsList	= "";
		$tabItems	= array();
		$tabsDivs	= array();
		$classExt	= "";
		
		if(!count($tabsCon))
			return $this->getContentElementWrapper($this->conAttributes, $output, $classExt);
		
		$tabsCon_h = (array)json_decode($tabsCon[0], true);
		$tabsCon_con = (array)json_decode($tabsCon[1], true);
			
		$this->cssFiles[] 		= STYLES_DIR . "tabs.css"; // css-Datei einbinden

		// if more than 3 tabs
		if(count($tabsCon_h) > 1) {
			$this->cssFiles[] 				= "access/css/tabdrop.css"; // css-Datei einbinden			
			$this->scriptFiles["tabdrop"] 	= "access/js/bootstrap-tabdrop.js"; // js-Datei einbinden			
			$this->scriptCode[] 			= 'head.ready("tabdrop", function(){
												$("document").ready(function(){
													$(".nav-pills, .nav-tabs").tabdrop({
														text: "<span class=\'sr-only\'>Toggle tabs</span>"
													});
											    });
											  });';
		}
		
		// Accordion
		if($tabsCon[2] == "acc") {
		
			$classExt	=	'cc-accordion accordionWrapper';
			$output	   .=	'<div id="cc-accordion-' . $this->conTable . $this->conNum . '" class="accordion-panel {t_class:panelgroup}" role="tablist" aria-multiselectable="true">';
			$i	= 1;
			
			foreach($tabsCon_h as $key => $val) {
				
				// Tabs generieren, falls nicht leer
				if($val == "")
					continue;
				
				$output	   .=	'<div class="{t_class:panel}' . (!empty($tabsCon[3]) ? ' {t_class:pfxpanel}{t_class:sfx' . htmlspecialchars($tabsCon[3]) . '}' : '') . ' with-nav-tabs">' .
								'<div class="{t_class:panelhead}" role="tab">' .
								'<h3 class="tabHeader {t_class:paneltitle}">' .
								'<a href="#tab-' . $key . '" class="scroll-no' . ($i == "1" ? '' : ' collapsed') . '" role="button" data-toggle="collapse" data-parent="#cc-accordion-' . $this->conTable . $this->conNum . '" data-context="accordion" aria-constrols="tab-' . $key . '"' . ($i == "1" ? ' aria-expanded="true"' : '') . '>' .
								htmlspecialchars($val) .
								'</a>' .
								'</h3>' .
								'</div>' .
								'<div id="tab-' . $key . '" class="tabContent {t_class:panelcollapse}' . ($i == "1" ? ' in' : ' collapse') . '" role="tabpanel" aria-labelledby="tab-' . $key . '">' . "\r\n" .
								'<div class="{t_class:panelbody}">' .
								$tabsCon_con[$key] .
								'</div>' .
								'</div>' .
								'</div>' . PHP_EOL;
								
				$i++;
			
			}
			$output	   .=	'</div>' . PHP_EOL;
		}
		
		// Tabs
		else {
		
			$navType	=	$tabsCon[2] == "tabs" ? 'tabs' : 'pills';
			$classExt	=	'cc-tabs tabsWrapper';
			$i	= 1;
			
			foreach($tabsCon_h as $key => $val) {
				
				// Tabs generieren, falls nicht leer
				if($val == "")
					continue;
					
				$tabItems[$i]	=	'<li class="tab-' . $key . ($i == "1" ? ' active' : '') . '" role="presentation">' .
									'<a href="#tab-' . $this->conTable . $this->conNum . '-' . $key . '" class="scroll-no" role="tab" data-toggle="tab">' .
									'<h3 class="tabHeader {t_class:paneltitle}">' .
									htmlspecialchars($val) .
									'</h3>' .
									'</a>' .
									'</li>';
				
				$tabsDivs[$i]	=	'<div id="tab-' . $this->conTable . $this->conNum . '-' . $key . '" class="tabContent {t_class:tabpane} {t_class:fade}' . ($i == "1" ? ' in active' : '') . '" role="tabpanel" aria-labelledby="tab-' . $key . '">' . "\r\n" .
									$tabsCon_con[$key] .
									'</div>';
				
				$i++;
			}
			
			$tabsList	=	'<ul class="' . $navType . ' {t_class:' . $navType . '}" role="tablist">' . "\r\n";
			$tabsList  .=	implode(PHP_EOL, $tabItems);
			$tabsList  .=	'</ul>';
			
			$tabsList	=	'<div class="{t_class:panelhead}">' .
							$tabsList .
							'</div>';
			
			$output	   .=	'<div class="{t_class:panel}' . (!empty($tabsCon[3]) ? ' {t_class:pfxpanel}{t_class:sfx' . htmlspecialchars($tabsCon[3]) . '}' : '') . ' with-nav-' . $navType . '">' .
							$tabsList .
							'<div class="{t_class:panelbody}">' .
							'<div class="{t_class:tabcontent}">' .
							implode(PHP_EOL, $tabsDivs) .
							'</div>' .
							'</div>' .
							'</div>';
		}
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, $classExt);
		
		return $output;
	
	}	
	
}
