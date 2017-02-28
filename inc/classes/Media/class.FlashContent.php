<?php
namespace Concise;



/**
 * Klasse FlashContent
 * 
 */

class FlashContent extends ContentsEngine
{

	/**
	 * Erstellt ein Flashobjekt
	 *
     * @param	string	$title Objekttitel
	 * @param	string	$alt Alternativtext
     * @param	string	$width Breite des Flashobjekts
     * @param	string	$height Höhe des Flashobjekts
     * @param	string	$menu Flashmenu (default = false)
     * @param	string	$play automatisches Abspielen (default = false)
     * @param	string	$loop Abspielen mit Schleife (default = false)
     * @param	string	$quality Qualität des Flashobjekts (default = high)
	 * @access	public
	 * @return	string
	 */
	public function getFlashObject($title, $alt, $width, $height, $menu = "false", $play = "false", $loop = "false", $quality = "high")
	{
	
		$folder		= (strpos($title, "/") !== false ? CC_FILES_FOLDER . '/' : CC_VIDEO_FOLDER . '/');
		$baseName	= pathinfo($title, PATHINFO_FILENAME);
		
		return	'<script language="JavaScript" type="text/javascript">' . "\r\n" .
						'AC_FL_RunContent(' . "\r\n" .
						'"codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0",' . "\r\n" .
						'"width", "' . $width . '",' . "\r\n" .
						'"height", "' . $height . '",' . "\r\n" .
						'"src", "' . PROJECT_HTTP_ROOT . '/' . $folder . $baseName . '",' . "\r\n" .
						'"quality", "' . $quality . '",' . "\r\n" .
						'"pluginspage", "http://www.adobe.com/go/getflashplayer",' . "\r\n" .
						'"align", "middle",' . "\r\n" .
						'"play", "' . $play . '",' . "\r\n" .
						'"loop", "' . $loop . '",' . "\r\n" .
						'"scale", "showall",' . "\r\n" .
						'"wmode", "window",' . "\r\n" .
						'"devicefont", "false",' . "\r\n" .
						'"id", "' . $baseName . '",' . "\r\n" .
						#'"bgcolor", "#000000",' . "\r\n" .
						'"name", "' . $title . '",' . "\r\n" .
						'"menu", "' . $menu . '",' . "\r\n" .
						'"allowFullScreen", "true",' . "\r\n" .
						'"allowScriptAccess","sameDomain",' . "\r\n" .
						'"movie", "' . PROJECT_HTTP_ROOT . '/' . $folder . $baseName . '",' . "\r\n" .
						'"salign", ""' . "\r\n" .
						'); //end AC code' . "\r\n" .
				'</script>' . "\r\n" .
				'<noscript>' . "\r\n" .
				'<object type="application/x-shockwave-flash" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" data="' . PROJECT_HTTP_ROOT . '/flash/' . $title . '" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="' . $width . '" height="' . $height . '">' . "\r\n" .
				'<param name="movie" value="' . PROJECT_HTTP_ROOT . '/' . $folder . $title . '" />' . "\r\n" .
				'<param name="quality" value="' . $quality . '" />' . "\r\n" .
				'<param name="allowscriptaccess" value="always">' . "\r\n" .
				'<param name="allowfullscreen" value="true">' . "\r\n" .
				'<param name="scale" value="exactfit" />' . "\r\n" .
				'<param name="menu" value="' . $menu . '" />' . "\r\n" .
				'<param name="play" value="' . $play . '" />' . "\r\n" .
				'<param name="loop" value="' . $loop . '" />' . "\r\n" .
				'<embed src="' . PROJECT_HTTP_ROOT . '/' . $folder . $title . '" width="' . $width . '" height="' . $height . '" quality="' . $quality . '" scale="exactfit" menu="' . $menu . '" play="' . $play . '" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer">' . "\r\n" .
				'</embed>' . "\r\n" .
				'<p class="noflash">' . $alt . '</p>' . "\r\n" .
				'</object>' . "\r\n" .
				'</noscript>' . "\r\n";				
	}
}
