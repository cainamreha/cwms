<?php
namespace Concise;



/**
 * CounterElement
 * 
 */

class CounterElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein CounterElement zurück
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
		#########  Counter  ##########
		##############################
		
		$counter	= "";
		
		$countCon	= explode("/", $this->conValue);
		
		// Stats-Objekt
		require_once PROJECT_DOC_ROOT . "/inc/classes/Logging/class.Stats.php";

		$o_stats	= new Stats($this->DB, $this->o_lng->lang);
		
		// Counter generieren
		if(isset($countCon[0]) && $countCon[0])
			$counter .=	$o_stats->usersOnline($this->adminLog, "", true); // Besucher online, Falls Admin
		if(isset($countCon[1]) && $countCon[1])
			$counter .=	$o_stats->getVisitStats("today", "", "", "", false, 10, true); // Besuche heute
		if(isset($countCon[2]) && $countCon[2])
			$counter .=	$o_stats->getVisitStats("yesterday", "", "", "", false, 10, true); // Besuche gestern
		if(isset($countCon[3]) && $countCon[3])
			$counter .=	$o_stats->getVisitStats("visits", strtotime('01-01-2010'), "", "", false, 10, true); // Besuche gesamt
			
		
		// Attribute (Styles) Wrapper-div hinzufügen
		$output =	'<table class="{t_class:table}">' . "\r\n" .
					'<tbody>' . "\r\n";
		
		$output .=	$counter;

		$output .=	'</tbody>' . "\r\n" .
					'</table>' . "\r\n";
		
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'counter');
		
		return $output;
	
	}	
	
}
