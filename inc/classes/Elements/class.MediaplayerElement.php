<?php
namespace Concise;



/**
 * MediaplayerElement
 * 
 */

class MediaplayerElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein MediaplayerElement zurück
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
		#######  Media-Player  #######
		##############################
		
		// Zunächst das entsprechende Modul einbinden (Gallery-Klasse)
		require_once PROJECT_DOC_ROOT."/inc/classes/Modules/class.MediaPlayer.php";
		
		$mediaCon = explode("<>", $this->conValue);
		
		if(!isset($mediaCon[0]))
			$mediaCon[0] = "";
		if(!isset($mediaCon[1]))
			$mediaCon[1] = "";
		if(!isset($mediaCon[2]))
			$mediaCon[2] = "";
		if(!isset($mediaCon[3]))
			$mediaCon[3] = "";
		if(!isset($mediaCon[4]))
			$mediaCon[4] = "blue.monday";
		if(!isset($mediaCon[5]))
			$mediaCon[5] = 0;
		if(!isset($mediaCon[6]))
			$mediaCon[6] = 640;
		if(!isset($mediaCon[7]))
			$mediaCon[7] = 360;
		if(!isset($mediaCon[8]))
			$mediaCon[8] = 0;
		if(!isset($mediaCon[9]))
			$mediaCon[9] = 0;
		
		// Medientracks
		$mediaCon[0]	= array_filter(preg_replace("/\r/", "", explode("\n", $mediaCon[0])));
		// Medientitel
		$mediaCon[1]	= array_filter(preg_replace("/\r/", "", explode("\n", $mediaCon[1])));
		// Mediencover
		$mediaCon[2]	= array_filter(preg_replace("/\r/", "", explode("\n", $mediaCon[2])));
		// Medienküstler
		$mediaCon[3]	= array_filter(preg_replace("/\r/", "", explode("\n", $mediaCon[3])));
		
		// Player-ID setzen
		if(isset($playerID))
			$playerID++;
		else
			$playerID = 1;
		
		$o_mediaPlayer	= new MediaPlayer();
		
		$output =	$o_mediaPlayer->getMediaPlayer($mediaCon[0], $mediaCon[1], $mediaCon[2], $mediaCon[3], $mediaCon[4], $mediaCon[5], $mediaCon[6], $mediaCon[7], $mediaCon[8], $mediaCon[9], $playerID);			
		
		$this->mergeHeadCodeArrays($o_mediaPlayer);

		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'mediaplayer');
		
		return $output;
	
	}	
	
}
