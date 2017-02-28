<?php
namespace Concise;


/** 
 * Klasse zum Überschreiben des Error-Handlers von PHP 
 *
 */

class ErrorHandling
{

	public function __construct()
	{

		//Überschreiben des ursprünglichen Error-Handlers mit dem eigenen Erro-Handler
		$old_error_handler = set_error_handler(array ($this, "userErrorHandler"));

	}

	/**
	 * Methode zur Fehlerbehandlung
	 *
	 * In dieser Methode wird der Fehler ausgewertet und je nach Schwere auch in die Log-Daten eingetragen.
	 * Außerdem wird der Fehler ausgegeben. 
	 *
	 * @param integer Fehlernummer
	 * @param string Fehlermeldung
	 * @param string Dateiname
	 * @param integer Zeilennummer
	 * @param array Kontext
	 */
	public function userErrorHandler($errno, $errmsg, $filename, $linenum)
	{
	
		//Falls error reporting abgeschaltet wurde
		if(error_reporting() === 0)
			return false;
		
		
		//Hier wird ein assoziatives Array mit den Fehlerarten erstellt.
		$errortypes = array(E_ERROR => "Error",
							E_WARNING => "Warning",
							E_PARSE => "Parsing Error",
							E_NOTICE => "Notice",
							E_CORE_ERROR => "Core Error",
							E_CORE_WARNING => "Core Warning",
							E_COMPILE_ERROR => "Compile Error",
							E_COMPILE_WARNING => "Compile Warning",
							E_USER_ERROR => "User Error",
							E_USER_WARNING => "User Warning",
							E_USER_NOTICE => "User Notice",
							E_STRICT => "Runtime Notice");

		//Nur ausgeben, wenn der Fehler auch angezeigt werden soll
		if ((in_array($errno, $GLOBALS['screenErrors'])) && (EH_SCREEN_NOTIFICATION === true))
		{
			//Fehlermeldung generieren
			echo "<p>&nbsp;</p>\r\n";
			echo "<p>&nbsp;</p>\r\n";
			echo "<fieldset style='border:1px solid red;padding:2px;margin:2px;'>";
			echo "<legend style='color:red;font-family:Verdana;'>";
			echo $errortypes[$errno]."</legend>";
			echo $errmsg."<br />\r\n";
			echo "<b>Skript:</b>&nbsp;".$filename."&nbsp;\r\n";
			echo "<b>Line:</b>&nbsp;<span style='color:orange;font-weight:bold;'>".$linenum."</span><br />\r\n";
			echo "</fieldset>";
		}

		//Fehler, die in der Datenbank bzw. der Log-Datei gespeichert werden sollen.
		if ((in_array($errno, $logErrors = $GLOBALS['logErrors'])) && (EH_LOG === true))
		{

			//Routine zum Speichern des Fehlers in Datenbank (bzw. der Log-Datei)
			$this->doLog($errortypes[$errno], $errmsg, $filename, $linenum);

		}

		// Fehler, die per Email-Benachrichtigung an den Admin geschickt werden.
		if ((in_array($errno, $GLOBALS['emailErrors'])) && (EH_EMAIL_NOTIFICATION === true))
		{

			//Userklasse
			require_once PROJECT_DOC_ROOT."/inc/classes/User/class.User.php";

			$domain = str_replace("http://", "", PROJECT_HTTP_ROOT);
			$domain = str_replace("https://", "", $domain);
								  
			//Fehlermeldung generieren
			$subject = "Fehlermeldung - " . $domain;
			
			$error = "Folgender Fehler ist aufgetreten auf ".$domain.":\r\n";
			$error .= $errortypes[$errno]."\r\n";
			$error .= $errmsg."\r\n";
			$error .= "In Skript: ".$filename."\r\n";
			$error .= "Zeile: ".$linenum."\r\n\r\n";
			$error .= "Seite: ".$GLOBALS['_SERVER']['REQUEST_URI']."\r\n\r\n";
			$error .= "Referer: ".$GLOBALS['_SERVER']['HTTP_REFERER']."\r\n\r\n";
			$error .= "IP: ".User::getRealIP()."\r\n\r\n";
			$error .= "Diese Email wurde automatisch generiert.";

			@mail(EH_ADMIN_EMAIL, $subject, $error, "From: webmaster@project.de\r\n");

		}

	}

	/**
	 * Eintragen des aufgetretenen Fehlers in die Log-Datei
	 * 
	 * @param integer Fehlernummer
	 * @param string Fehlermeldung
	 * @param string Dateiname
	 * @param integer Zeilennummer
	 */
	private function doLog($errortype, $errormsg, $filename, $linenum)
	{

		if (isset ($GLOBALS['DB']))
		{

			$DB = $GLOBALS['DB'];

			$error = $errortype.":".$errormsg;

			$sql = "INSERT INTO `" . DB_TABLE_PREFIX . "errorlog` (error,script,line,timestamp)"." VALUES ('".$DB->escapeString($error)."', "."'".$DB->escapeString($filename)."',"."'".$linenum."','".time()."')";
			$DB->query($sql);

		}
		else
		{

			$logPath	= EH_LOGFILE_PATH;
			$logFile	= explode("/", $logPath);
			$logFile	= array_pop($logFile);
			$logFolder	= str_replace("/".$logFile, "", $logPath);
			
			if(!is_dir($logFolder))
				mkdir($logFolder);
			
			$handle = @fopen(EH_LOGFILE_PATH, "a");
			//Fehlermedlung eintragen
			$error = date("d.m.Y h:i:s", time())."|".$errormsg."|".$filename." in Zeile: ".$linenum."\r\n";

			fwrite($handle, $error);

			fclose($handle);
		}
	}

}

//Überschreiben des "normalen" ErrorHandlings durch 
//erstellen einer Instanz dieser Klasse
new ErrorHandling();
