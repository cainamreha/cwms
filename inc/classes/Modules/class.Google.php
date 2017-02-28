<?php
namespace Concise;



/**
 * Klasse für Google-Services
 *
 */

class Google extends Modules
{

	/**
	 * Bindet ein Googlemaps-Modul ein
	 * 
     * @param	string Breite des Map-Objekts  (default = 450)
     * @param	string Höhe des Map-Objekts  (default = 324)
     * @param	string q (default = '')
     * @param	string near (default = '')
     * @param	string radius (default = '')
     * @param	string hq (default = '')
     * @param	string size Größe des Map-Objekts (default = small)
	 * @access	public
	 * @return	string
	 */
	public static function getMap($code = "", $width = "450", $height = "324", $q = "", $near = "", $radius = "", $hq = "", $size = "small")
	{

		$googleMap 		=	'<div class="{t_class:flexiblediv}"' . (!empty($height) ? ' style="padding-bottom:' . str_replace(",", ".", ($height /16)) . 'rem;"' : '') . '>' . "\r\n";
		$googleMapClose =	'</div>' . "\r\n";
		
		
		if($code != "")
		
			return $googleMap . $code . $googleMapClose;
		
		
		$qArr = explode(" ", $q);
		$q = "";
		
		foreach($qArr as $qS) {
			$q .= urlencode($qS) . "+";
		}
		
		$q = str_replace("%2C", ",", $q);
		$hq = $hq == "" ? $q : $hq;
		
		if($near == "")
			$near = $q;			
			
		$googleMap .=	'<div class="registerCenter">' . "\r\n" .
						'<iframe style="display:block;" width="' . $width . '" height="' . $height . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http' . (HTTPS_PROTOCOL ? 's' : '') . '://maps.google.de/maps?f=q&amp;source=s_q&amp;hl=' . $GLOBALS['o_lng']->lang . '&amp;geocode=&amp;q=' . $q . '&amp;aq=&amp;ie=UTF8&amp;hq=' . $hq . '&amp;hnear=' . $near . '&amp;radius=' . $radius . '&amp;t=m&amp;output=embed" allowfullscreen></iframe>' . "\r\n" . 
						'</div>' . "\r\n";
				
		return $googleMap . $googleMapClose;
	
	}

}
