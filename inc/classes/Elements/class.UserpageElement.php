<?php
namespace Concise;



/**
 * UserpageElement
 * 
 */

class UserpageElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein UserpageElement zurück
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
		########  Userpage  #########
		##############################
	
		if(!(
			 (  REGISTRATION_TYPE == "account"
			 || REGISTRATION_TYPE == "shopuser"
			 )
			&& isset($this->g_Session['userid'])
			&& isset($this->g_Session['username'])
			&& isset($this->g_Session['group'])
			&& ($this->g_Session['group'] == "guest"
			 || $this->backendLog)
			)
		)
			$this->gotoErrorPage();
		
		// Neues User-Objekt
		$o_user			= new User($this->DB, $this->o_lng);

		$output			= $o_user->getPersonalPage($this->g_Session['userid'], $this->g_Session['username']);
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
