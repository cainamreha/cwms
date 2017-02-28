<?php
namespace Concise;



/**
 * Klasse für Emoticons
 *
 */

class Emoticons extends Modules
{
	 
	/**
	 * Beinhaltet den Pfad zu den Emoticons.
	 *
	 * @access public
     * @var    string
     */
	public $smileyPath = "";
	
	/**
	 * Beinhaltet die Platzhalter für die Emoticons.
	 *
	 * @access public
     * @var    array
     */
	public $placeHolders = array(	':)',
									';)',
									':D',
									':P',
									':(',
									';p',
									'=(',
									'(00)'
								);
	
	/**
	 * Beinhaltet die Dateinamen der Emoticons.
	 *
	 * @access public
     * @var    array
     */
	public $emoFiles = array(
								array('smile.gif',15,15),
								array('prick.gif',15,15),
								array('grins.gif',15,15),
								array('tongue.gif',15,15),
								array('frown.gif',15,15),
								array('wink.gif',25,15),
								array('freu.gif',25,27),
								array('cool.gif',15,15)
							);
		

	/**
	 * Definiert Emoticons
	 * 
	 * @access public
	 */
	public function __construct()
	{
					
		$this->smileyPath	= PROJECT_HTTP_ROOT . '/' . IMAGE_DIR . 'smilies/';
		
		// Eigene Emoticons
		if(file_exists(PROJECT_DOC_ROOT . '/inc/defEmoticons.inc.php'))
			require PROJECT_DOC_ROOT . '/inc/defEmoticons.inc.php';
	
	}
	
	

	/**
	 * Ersetzt Platzhalter mit Emoticons
	 * 
	 * @access	public
     * @param	varchar Quellstring
     * @return string
	 */
	public function getEmoticons($source)
	{
		
		$i = 0;
		
		foreach($this->placeHolders as $placeH) {
			
			$repl = '<img src="' . $this->smileyPath . $this->emoFiles[$i][0] . '" alt="'.$this->emoFiles[$i][0].'" width="'.$this->emoFiles[$i][1].'" height="'.$this->emoFiles[$i][2].'" class="smiley" />';
			$source = str_replace($placeH, $repl, $source); // Ersetzen der Abkürzungen
		
			$i++;
		}
		
		return $source;

	}
	
	

	/**
	 * Listet Emoticons zum Anklicken auf
	 * 
	 * @access	public
     * @return string
	 */
	public function listEmoticons()
	{
					
		$smileys =	'<p class="smileys {t_class:well} {t_class:wellsm}">' . "\r\n";
		$i = 0;
		
		foreach($this->placeHolders as $placeH) {		
			$smileys .=	'<img src="' . $this->smileyPath . $this->emoFiles[$i][0] . '" alt="'.$this->emoFiles[$i][0].'" title="'.$placeH.'" width="'.$this->emoFiles[$i][1].'" height="'.$this->emoFiles[$i][2].'" class="smiley clickable" />';
			$i++;
		}
		
        $smileys .= '</p>' . "\r\n";
	
	
		// Smileys ersetzen
		$smileys .= '<script>' . "\n" .
					'head.ready(function(){' . "\n" .
					'$(document).ready(function(){' . "\n" .
					'$("img.smiley").click(function(){' . "\n" .
					'var shortcut = $(this).attr("title") == "" ? titleVal : $(this).attr("title");' . "\n" .
					'var message = $("textarea[id=\'message\']").val();' . "\n" .
					'$("textarea[id=\'message\']").val(message + shortcut);' . "\n" .
					'return false;' . "\n" .
					'});' . "\n" .
					'});' . "\n" .
					'});' . "\n" .
					'</script>' . "\n";
		
		return $smileys;


	}
	
}
