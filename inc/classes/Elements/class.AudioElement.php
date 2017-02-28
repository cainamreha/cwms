<?php
namespace Concise;



/**
 * AudioElement
 * 
 */

class AudioElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein AudioElement zurück
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
		$this->conCount			= $options["conCount"];

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
		########  audio-Inhalt  ########
		##############################
		
		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.AudioContent.php"; // AudioContent-Klasse einbinden
		
		$audioCon = explode("<>", $this->conValue);
		
		if(!isset($audioCon[0]))
			$audioCon[0] = "";
		if(!isset($audioCon[1]))
			$audioCon[1] = "";
		if(!isset($audioCon[2]))
			$audioCon[2] = DEF_PLAYER_WIDTH;
		if(!isset($audioCon[3]))
			$audioCon[3] = "no";
		if(!isset($audioCon[4]))
			$audioCon[4] = "no";

		$uniquePlayerId = $this->conCount . rand(100,999);
		$fileExt		= strtolower(pathinfo($audioCon[0], PATHINFO_EXTENSION));
		
		// Falls audio-File
		if($fileExt == "audio") {
			$output =	AudioContent::getAudioObject($audioCon[0], $audioCon[1], $audioCon[2], $audioCon[3], $audioCon[4], $uniquePlayerId, $this->conAttributes['class']);
			// Javascript-File/-Code für Audio-Player einlesen
			AudioContent::getAudioScript();
		}
		// Andernfalls HTML5 Audio
		else {
			$folder = CC_AUDIO_FOLDER . "/";
			$output	= AudioContent::getHTML5Audio(PROJECT_HTTP_ROOT . '/' . $folder . $audioCon[0]) . "\r\n";
		}

		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);
		
		return $output;
	
	}	
	
}
