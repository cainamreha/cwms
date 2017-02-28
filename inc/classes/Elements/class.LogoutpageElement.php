<?php
namespace Concise;



/**
 * LogoutpageElement
 * 
 */

class LogoutpageElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein LogoutpageElement zurück
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
		########  Logoutpage  ########
		##############################
		
		if(empty($this->conValue)) {
		
			$this->conValue	= "<h1>{s_text:logout}</h1>" . PHP_EOL;
		
			$loginLink		= HTML::getLinkPath(-1002, "current", true, true);
		
			// Button link
			$btnDefs	= array(	"href"		=> $loginLink,
									"class"		=> 'link login {t_class:btnpri}',
									"text"		=> "{s_text:relog}",
									"attr"		=> 'rel="nofollow"',
									"icon"		=> "lock"
								);
				
			
			$this->conValue	.=	'<p><br />' . parent::getButtonLink($btnDefs) . '</p>' . PHP_EOL;
		}
		
		$output		=	$this->conValue;

		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
