<?php
namespace Concise;


########################
########  Tabs  ########
########################

/**
 * TabsConfigElement class
 * 
 * content type => tabs
 */
class TabsConfigElement extends ConfigElementFactory implements ConfigElements
{

	private	$tabContent_h	= array(1 => "tab-1");
	private	$tabContent_con	= array(1 => "");
	private	$tabFormat		= "";
	private	$tabStyle		= "";

	/**
	 * Gibt ein TabsConfigElement zurück
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
	
		$this->scriptTag		= $this->getSortScript('sortableTabs-' . $this->conPrefix);

		$this->a_POST	= $a_POST;
		$this->params	= explode("<|>", $this->conValue);

		
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
	
		if(isset($this->a_POST[$this->conPrefix . '_h'])) { // Falls das Formular abgeschickt wurde

			$tc	= 1;
			
			// Reiterbeschriftungen / Inhalte
			foreach($this->a_POST[$this->conPrefix . '_h'] as $key => $val) {
			
				if($val == "")
					continue;

				// Label
				$this->tabContent_h[$tc] 	= trim($val);
			
				// Tab-Inhalt
				$this->tabContent_con[$tc]	= $this->a_POST[$this->conPrefix . '_con'][$key];
				
				// Pfade durch Platzhalter ersetzen
				$rootPH		= "{#root}";
				$rootImgPH	= "{#root}/{#root_img}";
				
				$this->tabContent_con[$tc] = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."\/".str_replace("/", "\/", IMAGE_DIR)."~isU", $rootImgPH, $this->tabContent_con[$tc]);
				$this->tabContent_con[$tc] = preg_replace("~".str_replace("/", "\/", PROJECT_HTTP_ROOT)."~isU", $rootPH, $this->tabContent_con[$tc]);
			
				$tc++;
			}
			
			$this->tabFormat	= $this->a_POST[$this->conPrefix . '_tabFormat'];
			$this->tabStyle		= $this->a_POST[$this->conPrefix . '_tabStyle'];
		
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		// db-Updatestring
		$this->dbUpdateStr  = "'";
		$this->dbUpdateStr .= $this->DB->escapeString(json_encode($this->tabContent_h, JSON_UNESCAPED_UNICODE) . '<|>' . json_encode($this->tabContent_con, JSON_UNESCAPED_UNICODE) . '<|>' . $this->tabFormat . '<|>' . $this->tabStyle);
		$this->dbUpdateStr .= "',";

	}
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		$this->scriptFiles["contabs"] = "system/access/js/tabs.min.js";

		if(count($this->params) > 1) {
			
			$this->tabContent_h	= (array)json_decode($this->params[0], true);
			$this->tabContent_con = (array)json_decode($this->params[1], true);
				
			// Pfade durch Platzhalter ersetzen
			$rootPH		= "{#root}";
			$rootImgPH	= "{#root}/{#root_img}";
			
			foreach($this->tabContent_con as $key => $tabCon) {
				
				$this->tabContent_con[$key] = str_replace($rootImgPH, PROJECT_HTTP_ROOT . '/' . IMAGE_DIR, $tabCon);
				$this->tabContent_con[$key] = str_replace($rootPH, PROJECT_HTTP_ROOT, $this->tabContent_con[$key]);
			}
		}
		
		if(!isset($this->params[2]))
			$this->tabFormat	= "";
		else
			$this->tabFormat	= $this->params[2];
		if(!isset($this->params[3]))
			$this->tabStyle		= "";
		else
			$this->tabStyle		= $this->params[3];

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{
	
		$output	 = '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;

		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.= '<span class="notice">' . $this->error . '</span>' . PHP_EOL;


		$output	.= '<ul id="sortableTabs-' . $this->conPrefix . '" class="setupTabs sortableTabs sortable">' . PHP_EOL;

		
		foreach($this->tabContent_h as $key => $val) {
			
			if($val == ""){
				continue;
			}
			
			$this->textAreaCount++; // TinyMCE-Zähler erhöhen
			
			$output	.= '<li class="tabEntry cc-groupitem-entry">' . PHP_EOL;
			
			// Registerüberschriften
			$output	.=	'<span class="listEntryHeader actionHeader move">{s_label:tab} ' . $key . PHP_EOL .
						'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button remove tab
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'removeTab button-icon-only',
									"title"		=> '{s_title:delete}',
									"icon"		=> "delete"
								);
				
			$output .=	parent::getButton($btnDefs);
						
			$output .=	'</span>' . PHP_EOL .
						'</span>' . PHP_EOL .
						'<label class="tabHeader-label cc-groupitem-header-label">{s_label:tabheader} ' . $key . '<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<input class="tabHeader" type="text" name="' . $this->conPrefix . '_h[]" value="' . htmlspecialchars($val) . '" />' . PHP_EOL;

			// Registerinhalte
			$output	.=	'<label class="tabContent-label cc-groupitem-content-label toggleEditor toggle' . ($this->tabContent_con[$key] != "" ? ' busy' : '') . '" data-target="' . $this->conPrefix . '-tabCon' . $this->textAreaCount . '">{s_label:tabcon} ' . $key . '<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<textarea name="' . $this->conPrefix . '_con[]" id="' . $this->conPrefix . '-tabCon' . $this->textAreaCount . '" class="tabContent disableEditor forceSave hide cc-editor-add cc-always-hide" data-index="' . $this->textAreaCount . '">' . $this->tabContent_con[$key] . '</textarea>' . PHP_EOL;

			$output	.= '</li>' . PHP_EOL;
		
		}

		$output	.= '</ul>' . PHP_EOL;

		// Button für weitere Tabs
		$output	.=	'<div class="addTabs cc-groupitem-add buttonPanel">' . PHP_EOL;
		
		// Button new tab
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'newTab button-icon-only button-small',
								"title"		=> '{s_title:addtab}',
								"icon"		=> "new"
							);
			
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_form:format}">' . PHP_EOL;
		
		// Format
		$output .=	'<div class="leftBox">' . PHP_EOL . 
					'<label>{s_form:format}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_tabFormat" id="' . $this->conPrefix . '_listFormat" class="selListFormat">' .
					'<option value="tabs">Tabs</option>' . PHP_EOL .
					'<option value="pills"' . ($this->tabFormat == "pills" ? ' selected="selected"' : '') . '>Pills</option>' . PHP_EOL .
					'<option value="acc"' . ($this->tabFormat == "acc" ? ' selected="selected"' : '') . '>Accordion</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Style
		$output .=	'<div class="rightBox">' . PHP_EOL . 
					'<label>{s_common:style}</label>' . PHP_EOL . 
					'<select name="' . $this->conPrefix . '_tabStyle" id="' . $this->conPrefix . '_listStyle" class="selListStyle">' .
					'<option value="">{s_common:non}</option>' . PHP_EOL .
					'<option value="def"' . ($this->tabStyle == "def" ? ' selected="selected"' : '') . '>Default</option>' . PHP_EOL .
					'<option value="pri"' . ($this->tabStyle == "pri" ? ' selected="selected"' : '') . '>Primary</option>' . PHP_EOL .
					'<option value="suc"' . ($this->tabStyle == "suc" ? ' selected="selected"' : '') . '>Success</option>' . PHP_EOL .
					'<option value="inf"' . ($this->tabStyle == "inf" ? ' selected="selected"' : '') . '>Info</option>' . PHP_EOL .
					'<option value="war"' . ($this->tabStyle == "war" ? ' selected="selected"' : '') . '>Warning</option>' . PHP_EOL .
					'<option value="dan"' . ($this->tabStyle == "dan" ? ' selected="selected"' : '') . '>Danger</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

	
	// getSortScript
	protected function getSortScript($tabsID)
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready("ui", function(){' . PHP_EOL .
				'head.load({sort:"' . SYSTEM_HTTP_ROOT . '/access/js/adminSort.min.js"}, function(){' . PHP_EOL .
				'$(document).ready(function(){' . PHP_EOL .
					'$.sortableTabs("#' . $tabsID . '");' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
