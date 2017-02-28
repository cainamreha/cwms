<?php
namespace Concise;



/**
 * Klasse AudioContent
 * 
 */

class AudioContent extends ContentsEngine
{

	// Javascript-File zuweisen
	public static $scriptFile = "extLibs/audio-player/audio-player.js";
	
	/**
	 * Stellt Javascript-Code für die Einbindung im Html-Head-Bereich bereit
	 *
	 * @param	init	falls false, wird ein Ergebnis-Array mit Script-Datei und -Code zurückgegeben
	 * @access	public
	 */
	public function getAudioScript($init = true)
	{
		
		// Javascript-Code zuweisen
		$scriptCode = 	'AudioPlayer.setup("' . PROJECT_HTTP_ROOT . '/extLibs/audio-player/player.swf", {' . "\r\n";
		
		if(!isset($GLOBALS['_GET']['page']) || $GLOBALS['_GET']['page'] != "admin")
			$scriptCode .= 	'bg: "1C2721",' .
							'left: "aaaaaa",' .
							'leftbg: "1C1F21",' .
							'rightbg: "1C1F21",' .
							'rightbghover: "1C1F21",' .
							'lefticon: "aaaaaa",' .
							'righticon: "aaaaaa",' .
							'righticonhover: "88ee55",' .
							'loader: "aaaaaa",' .
							'voltrack: "aaaaaa",' .
							'volslider: "88ee55",';
		
		else
			$scriptCode .= 	'lefticon: "666666",' .
							'righticon: "666666",' .
							'track: "405B8E",' .
							'text: "FFFFFF",' .
							'tracker: "708BBE",' .
							'loader: "666666",';
		
		$scriptCode .= 	'initialvolume: 100,' .
						'transparentpagebg: "yes"' .
						'});' . "\r\n";

		if(!$init)
			return array(self::$scriptFile, $scriptCode);
			
		$this->scriptFiles[] =	self::$scriptFile; 
		$this->scriptCode[] = 	$scriptCode;
	
	}
	


	/**
	 * Erstellt ein Audioobjekt (audio)
	 *
	 * @param	string	$name audio-Objektname(n)
     * @param	string 	$title audio-Objekttitel(n)
     * @param	string	$width Breite des Players (default = DEF_PLAYER_WIDTH)
     * @param	string	$ani Animation des Players (default = no)
     * @param	string	$link Link zum audio-File (default = no)
     * @param	int		$uniqueID ID als Zusatz zur tag-id (default = 1)
     * @param	string	$class Klassenname des Player-divs (default = main)
	 * @access	public
	 * @return	string
	 */
	public function getAudioObject($name, $title, $width = "", $ani = "no", $link = "no", $uniqueID = 1, $class = "main")
	{
		
		if($width == "")
			$width = DEF_PLAYER_WIDTH;
		
		$name = explode(",", $name);
		$title = explode(",", $title);
		
		$tracks = "";
		$titles = "";
		$i = 0;
		
		foreach($name as $file) {
		
			if(strpos($file, "/") !== false)
				$folder = CC_FILES_FOLDER . "/";
			else
				$folder = CC_AUDIO_FOLDER . "/";
			
			$tracks .= PROJECT_HTTP_ROOT . '/' . $folder . trim($file) . ',';
			
			if($title[$i] != "")
				$titles .= trim($title[$i]) . ',';
			else
				$titles .= trim($file) . ',';
			
			$i++;
		}
		
		$tracks = substr($tracks, 0, -1);
		$titles = substr($titles, 0, -1);
		 
		$audio =	'<div class="audio-player ' . $class . '">' . "\r\n" .
					'<span id="audioplayer_'.$uniqueID.'"><a href="http://get.adobe.com/flashplayer/">{s_text:altflash}<img src="' . PROJECT_HTTP_ROOT . '/'.IMAGE_DIR.'noflash_icon.png" alt="getFlash" title="download flash player" class="getFlash" /></a></span>' . "\r\n" .
					'<script type="text/javascript">' . "\r\n" .
					($this->headJS ? 'head.ready("jquery",function(){' : '') .
					'$(document).ready("' . PROJECT_HTTP_ROOT . '/' . self::$scriptFile . '", function(){'."\r\n".
					'AudioPlayer.embed("audioplayer_'.$uniqueID.'", {' . "\r\n" .
					'soundFile: "' . $tracks . '",' . "\r\n" .
					'titles: "' . $titles . '",' . "\r\n" .
					'width: "'.$width.'",' . "\r\n" .
					'animation: "'.$ani.'"' . "\r\n" .
					'});' . "\r\n" .
					'});' . "\r\n" .
					($this->headJS ? '});' : '') .
					'</script>' . "\r\n";

		if($link == "yes" && count($name) == 1)
			$audio .=	'<span class="download"><a href="' . PROJECT_HTTP_ROOT . '/' . CC_AUDIO_FOLDER . '/' . trim($name[0]) . '">' . trim($title[0]) . ' (' . (float)round(filesize(PROJECT_DOC_ROOT . '/' . CC_AUDIO_FOLDER . '/' . trim($name[0]))/1024000, 2) . 'MB)</a></span>' . "\r\n";							
		
		$audio .=	'</div>' . "\r\n";
		
		return $audio;
	}
	


	/**
	 * Erstellt ein HTML5-Audioobjekt
	 *
	 * @param	string	$src Audiodateipfad
     * @param	int		$id ID-Attribut (default = '')
     * @param	string	$class Klassenattribut (default = '')
	 * @access	public
	 * @return	string
	 */
	public static function getHTML5Audio($src, $id = "", $class = "")
	{
		
		if(!file_exists(str_replace(PROJECT_HTTP_ROOT, PROJECT_DOC_ROOT, $src)))
			return '<img src="' . SYSTEM_IMAGE_DIR . '/noaudio.png" />' . "\n";
		
		$fileType	= strtolower(pathinfo($src, PATHINFO_EXTENSION));
		$mimeType	= "audio/mpeg";
		
		if($fileType == "ogg"
		|| $fileType == "oga"
		)
			$mimeType	= "audio/ogg";
				 
		$output		=	'<audio controls="">' . "\r\n" .
						'<source src="' . $src . '" type="' . $mimeType . '" />' . "\r\n" .
						'</audio>' . "\r\n";

		if($id != ""
		|| $class != ""
		)
			$output	=	'<div' . ($id != "" ? ' id="' . htmlspecialchars($id) . '"' : '') . ($class != "" ? ' class="' . htmlspecialchars($class) . '"' : '') . '>' . "\r\n" .
						$output .
						'</div>' . "\r\n";
		
		return $output;
	}

}
