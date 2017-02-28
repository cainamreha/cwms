<?php
namespace Concise;


##############################
########  Warenkorb  #########
##############################

/**
 * CartConfigElement class
 * 
 * content type => cart
 */
class CartConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $redCon			= "";
	private $agbCon			= "";
	private $shippingCon	= "";

	/**
	 * Gibt ein CartConfigElement zurück
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
	
		if(isset($this->a_POST[$this->conPrefix . '_targetPageID'])) { // Falls das Formular abgeschickt wurde
			
			$this->redCon		= trim($this->a_POST[$this->conPrefix . '_targetPageID']);
			$this->agbCon		= trim($this->a_POST[$this->conPrefix . '_agbPageID']);
			$this->shippingCon	= trim($this->a_POST[$this->conPrefix . '_shippingPageID']);
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$this->dbUpdateStr	= "'";
		$this->dbUpdateStr .= $this->DB->escapeString($this->redCon) . "<>";
		$this->dbUpdateStr .= $this->DB->escapeString($this->agbCon) . "<>";
		$this->dbUpdateStr .= $this->DB->escapeString($this->shippingCon);
		$this->dbUpdateStr .= "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		$this->redCon 			= $this->params[0];
		
		if(!isset($this->redCon) || $this->redCon == "")
			$this->redCon 		= ($this->isTemplateArea ? 1 : $this->editId);
		if(!isset($this->params[1]))
			$this->agbCon			= "";
		else
			$this->agbCon			= $this->params[1];
		if(!isset($this->params[2]))
			$this->shippingCon	= "";
		else
			$this->shippingCon	= $this->params[2];

	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{
	
		$this->redCon		= htmlspecialchars($this->redCon);
		$this->agbCon		= htmlspecialchars($this->agbCon);
		$this->shippingCon	= htmlspecialchars($this->shippingCon);

		$output	 = '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;

		// Zielseite (Seite mit Bestellformular)		
		// targetPage MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "targetPage",
											"type"		=> "targetPage",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"value"		=> "{s_button:targetPage}",
											"icon"		=> "articles"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		$output .=	'<label>{s_button:targetPage} - {s_form:ordertit}</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '" class="targetPage" value="' . htmlspecialchars(HTML::getLinkPath($this->redCon, "editLang", false)) . '" readonly="readonly" />' . PHP_EOL .
					'<input type="hidden" name="' . $this->conPrefix . '_targetPageID" class="targetPageID" value="' . htmlspecialchars($this->redCon) . '" />' . PHP_EOL;

		$output	.=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="AGBs">' . PHP_EOL;
		
		// AGB-Seite
		// targetPage MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "targetPage",
											"type"		=> "targetPage",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"value"		=> "{s_button:targetPage} - AGB",
											"icon"		=> "articles"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		$output .=	'<label>{s_button:targetPage} - AGB</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_agb" class="targetPage" value="' . htmlspecialchars(HTML::getLinkPath($this->agbCon, "editLang", false)) . '" readonly="readonly" />' . PHP_EOL .
					'<input type="hidden" name="' . $this->conPrefix . '_agbPageID" class="targetPageID" value="' . htmlspecialchars($this->agbCon) . '" />' . PHP_EOL;

		$output	.=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="Shipping">' . PHP_EOL;
		
		// Shipping-Seite
		// targetPage MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "targetPage",
											"type"		=> "targetPage",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=link",
											"value"		=> "{s_button:targetPage} - Shipping",
											"icon"		=> "articles"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		$output .=	'<label>{s_button:targetPage} - Shipping</label>' . PHP_EOL . 
					'<input type="text" name="' . $this->conPrefix . '_shipping" class="targetPage" value="' . htmlspecialchars(HTML::getLinkPath($this->shippingCon, "editLang", false)) . '" readonly="readonly" />' . PHP_EOL .
					'<input type="hidden" name="' . $this->conPrefix . '_shippingPageID" class="targetPageID" value="' . htmlspecialchars($this->shippingCon) . '" />' . PHP_EOL;

		$output	.=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
