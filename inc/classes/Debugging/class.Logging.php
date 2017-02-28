<?php
namespace Concise;


/**
 * Logging-Möglichkeit für das Basissystem
 * 
 * 
 */

class Logging
{
	//Pfad zur Logging-Datei (absolut oder relativ)
	private static $logfile = null;
	
	//Loglevel ist entweder INFO oder WARN
	private static $logLevel = 'INFO';
	
	//Filehandle
	private static $fileHandle = false;
		
	/**
	 * Konstruktor
	 * 
	 * Erstellt die globale Funktion debug(), die dann unter dem
	 * Befehl ::debug aufrufbar ist.
	 */	
	public function __construct()
	{
		if(!is_dir(PROJECT_DOC_ROOT.'/inc/log'))
			mkdir(PROJECT_DOC_ROOT.'/inc/log');
		
		//Log-Datei für den aktuellen Tag
		self::$logfile = PROJECT_DOC_ROOT.'/inc/log/'.date('Y_m_d',time()).'_log.txt';
		
	}
	
	/**
	 * Seit PHP 5.3 dürfen Objekte ausgeführt werden 
	 * 
	 * Diese Methode wird automatisch aufgerufen, wenn eine Klasseninstanz als
	 * ausführbare Methode behandelt wird.
	 * $log = new Logging();
	 * $log(); -> führt die __invoke-Methode aus 
	 */
	public function __invoke ($message = "",$level = "INFO") {
				    	
    	//Wenn die Datei noch nicht geöffnet ist, öffnen!
		if(!self::$fileHandle)self::$fileHandle = @fopen(self::$logfile,'a+');
		
		//Entscheiden, ob beim aktuellen Log-Level protokolliert werden darf
		if((self::$logLevel == $level) OR ($level == 'WARN'))
		{
			//INFO oder WARNING protokollieren
			$string  = strtoupper($level).'::'.date("d.m.Y H:i:s",time()).' - '.$message.' - '.$GLOBALS['_SERVER']["SCRIPT_FILENAME"]."\r\n";	
			//In die Datei schreiben
			fwrite(self::$fileHandle,$string);
		}
	
    }
	
	
	public function __destruct()
	{
		if(self::$fileHandle)fclose(self::$fileHandle);			
	}
	
		
	
	public static function getLog($count = 20, $level = "INFO")
	{
		//Wenn die Datei noch nicht geöffnet ist, öffnen!
		if(!self::$fileHandle)self::$fileHandle = @fopen(self::$logfile,'a+');
	    //Alle Log-Einträge aus der Datei in ein Array einlesen
		$entries = file(self::$logfile);	
		//Anzahl an bereits gezeigten Meldungen
		$displayedMessages = 0;
		
		//Die letzten $count Meldungen ausgeben
		//die neuste Meldung oben
		for($i = count($entries); $i > 0;$i--)	
		{
				//Abbrechen, wenn die Anzahl an zu zeigender Nachrichten erreicht ist.
				if($displayedMessages >= $count) return true;
				//Gibt es weitere Nachrichten im Log?
				if(!isset($entries[$i-1]))return true;
				
				//Alle Infos ausgeben
				if($level == "INFO") 
				{
					echo $entries[$i-1].'<br />';
					$displayedMessages++;
				}
				else if(substr($entries[$i-1],0,4)=="WARN")
				{	
					$displayedMessages++;
					echo $entries[$i-1].'<br />';	
				}
		}
		
	}
	
	public static function deleteLogfile($all = false)
	{
		//Wenn die Datei geöffnet ist, schliessen.
		if(self::$fileHandle)fclose(self::$fileHandle);
		//Datei mit aktuellem Datum löschen
		if(!$all)
			$del = unlink(self::$logfile);
		//Oder alle Log-Files löschen
		else {
			require_once PROJECT_DOC_ROOT."/inc/classes/Admin/class.Admin.php";
			$del = Admin::unlinkRecursive(PROJECT_DOC_ROOT.'/inc/log');
		}
		if($del)
			$GLOBALS['_SESSION']['notice'] = "Log files deleted.";
	}

	
	// Speichernutzung
	public static function getMemoryUsage($peak = false, $real = false, $decimals = 2)
	{
		$mem	= $peak ? memory_get_peak_usage($real) : memory_get_usage($real);
		$mem	= number_format(intval($mem)/1024/1024, $decimals, '.', '');
		
		return $mem;
	}
	
}
	
// Falls Debug-Konsole an, Logging aktivieren
if(DEBUG) {
	
	$debugLog = new Logging();
	$debugLog(); //führt die __invoke-Methode aus
}
