<?php
namespace Concise;


/**
 * Eine Konsole, die das Debugging von Skripten erleichtert
 * 
 * 
 */

class DebugConsole extends ContentsEngine
{
	/**
	 * Aktionen, die per GET-Parameter gesetzt wurden, ausführen.
	 *  
	 * @author Gunnar Thies
	 * 
	 */
	private static function handleAction()
	{
		
		//Wenn die Session gelöscht werden soll
		if(isset($_GET['destroySession']))
		{
			//Cookies löschen:
			self::deleteCookies();
			
			//SessionDaten löschen:
			session_destroy();
			
			header("location:".PROJECT_HTTP_ROOT);
			exit;
		}
		
		//Wenn die Log-Datei gelöscht werden soll
		if(isset($_GET['deleteLogfile']))
		{
			//Logdatei löschen:
			Logging::deleteLogfile(isset($_GET['all']));
			
			header("location:".PROJECT_HTTP_ROOT."/admin");
			exit;
		}
		
		//Wenn Cookies gelöscht werden sollen
		if(isset($_GET['deleteCookies']))
		{
			//Cookies löschen:
			self::deleteCookies();
			
			header("location:".PROJECT_HTTP_ROOT."/admin");
			exit;
		}
	}
	
	/**
	 * Hiermit wird die Konsole ausgegeben
	 *  
	 * @author Gunnar Thies
	 * 
	 */
	public static function displayConsole()
	{
		//eventuell gesetzte Get-Parameter überprüfen
		self::handleAction();
		
		$output = "";				

		//Äußeres DIV
		$output .= '<div id="debugDiv">';
		//Inhalt
		$output .= '<div id="debugContent">';
		
		$output .= '<ul>';
	
		if(count($_GET)>0)$output .= '<li><a href="#tabs-1"><span>GET-Parameter</span></a></li>';
		if(count($_POST)>0)$output .= '<li><a href="#tabs-2"><span>POST-Parameter</span></a></li>';
		if(count($_SESSION)>0)$output .= '<li><a href="#tabs-3"><span>SESSION-Parameter</span></a></li>';
		if(count($_FILES)>0)$output .= '<li><a href="#tabs-4"><span>FILES-Parameter</span></a></li>';
		if(count($_COOKIE)>0)$output .= '<li><a href="#tabs-5"><span>COOKIE-Parameter</span></a></li>';
		$output .= '<li><a href="'.SYSTEM_HTTP_ROOT.'/access/getSrcCode.php?filename='.$GLOBALS['_SERVER']['SCRIPT_FILENAME'].'"><span>Quellcode</span></a></li>';
		$output .= '<li><a href="#tabs-6"><span>Memory usage</span></a></li>';
		$output .= '<li><a href="'.SYSTEM_HTTP_ROOT.'/access/getLog.php"><span>Log</span></a></li>';
		
		$output .= '</ul>';
		
		//GET Parameter
		if(count($_GET)>0)
		{
		$output .= '<div class="tabs" id="tabs-1">';
		$output .= HTML::printArray($_GET);
		$output .= '</div>';
		}
		
		//POST Parameter
		if(count($_POST)>0)
		{
		$output .= '<div class="tabs" id="tabs-2">';
		$output .= HTML::printArray($_POST);
		$output .= '</div>';
		}
		
		//SESSION Parameter
		if(count($_SESSION)>0)
		{
		$output .= '<div class="tabs" id="tabs-3">';
		$output .= '<div>';
		$output .= '<a class="standardSubmit" href="?destroySession">Sessiondaten löschen</a></div>';
		$output .= '<div class="fragment">';
		$output .= HTML::printArray($_SESSION);
		$output .= '</div>';
		$output .= '</div>';
		}
		
		//FILES Parameter
		if(count($_FILES)>0)
		{
		$output .= '<div class="tabs" id="tabs-4">';
		$output .= HTML::printArray($_FILES);
		$output .= '</div>';
		}
		
		//COOKIE Parameter
		if(count($_COOKIE)>0)
		{
		$output .= '<div class="tabs" id="tabs-5">';
		$output .= '<div>';
		$output .= '<a class="standardSubmit" href="?deleteCookies">Cookies löschen</a></div>';
		$output .= '<div class="fragment">';
		$output .= HTML::printArray($_COOKIE);
		$output .= '</div>';
		$output .= '</div>';
		}
		
		//Memory usage Parameter
		$output .= '<div class="tabs" id="tabs-6">';
		$output .= '<table class="memoryTable adminTable">';
		$output .= '<tbody>';
		$output .= '<tr><th>&nbsp;</th><th>emalloc</th><th>real</th></tr>';
		$output .= '<tr><td>Memory usage: </td><td> {memory_usage_allo} MB</td><td> {memory_usage_real} MB</td></tr>';
		$output .= '<tr><td>Memory peak usage: </td><td> {memory_usage_allo_peak} MB</td><td> {memory_usage_real_peak} MB</td></tr>';
		$output .= '</tbody>';
		$output .= '</table>';
		$output .= '</div>';
		
		
	    //DIV schließen	
		$output .= '</div>';
		//Opener
		$output .= '<div id="debugOpener"></div>';
		//Äußeres DIV schliessen
		$output .= '</div>';
		
		return $output;
	}
	
	
	/**
	 * Dieses Skript gibt den Sourcecode einer PHP-Datei 
	 * aus (mit Zeilennummern am Anfang)
	 * 
	 *  
	 * @author Gunnar Thies, teilweise von: vanessaschissato@gmail.com
	 * 
	 * @param String Dateiname
	 */	
	public static function printCode($filename)
    {
       	
    	if(!file_exists($filename)){echo 'Kann Datei <em>'.$filename.'</em> nicht finden.';return false;}
    	
    	$sourceCode = file_get_contents($filename);
    	//Dateinamen anzeigen
    	$output = '<div style="background:#efefef;color:black;padding:2px;">Datei:<strong>'.$filename.'</strong></div>';
    	              
        //Zeilenumbrüche beachten und zeilenweise ins Array speichern 
        $sourceCode = explode("\n", str_replace(array("\r\n", "\r"), "\n", $sourceCode));
        $lineCount = 1;
		
		$formattedCode = "";

		//Jede Zeile bearbeiten
        foreach ($sourceCode as $codeLine)
        {
        	//Tabellenzeile erstellen
            $formattedCode .= '<tr><td style="text-align:right;background:#dedede;">'.$lineCount.'</td>';
            $lineCount++;
          
            //Ersetzungen vornehmen
            if (preg_match('/<\?(php)?[^[:graph:]]/', $codeLine))
                #$formattedCode .= '<td>'.highlight_string($codeLine, true).'</td></tr>';
                $formattedCode .= '<td>'.highlight_string(preg_replace("/[{]([^\s])/","PH: /*$1",preg_replace("/[}]/","*/",$codeLine)), true).'</td></tr>';
            else
                #$formattedCode .= '<td>'.str_replace('&lt;?php&nbsp;','',highlight_string('<?php'.$codeLine, true)).'</td></tr>';
                $formattedCode .= '<td>'.str_replace('&lt;?php&nbsp;','',highlight_string(preg_replace("/[{]([^\s])/","PH: /*$1",preg_replace("/[}]/","*/",'<?php
 '.$codeLine)), true)).'</td></tr>';
        	
        }
        
        //Ausgabestring zusammensetzen
        $output .= '<table style="border-collapse:collapse;font: 1em Consolas, \'andale mono\', \'monotype.com\', \'lucida console\', monospace;">'.$formattedCode.'</table>';
    	// ... und zurückgeben.
    
    	 
    	return $output;
    }
	
	
	/**
	 * Cookies löschen
	 * 
	 */	
	public static function deleteCookies()
    {
		//Cookies löschen:
		foreach($GLOBALS['_COOKIE'] as $key => $value) {
			setcookie($key, "", time()-3600);
			setcookie($key, "", time()-3600, "/");
		}
	}
	
}
