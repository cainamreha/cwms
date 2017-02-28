<?php
namespace Concise;


/**
 * Abstraktionsschicht für die Datenbank (nutzt nur MySQL)
 * 
 * Verbindet zur Datenbank und kapselt alle Anfragen an die Datenbank.
 */

class MySQL
{

	/**
	 * Datenbankverbindungsobjekt
	 *
	 * @access public
     * @var    string
     */
	public $MySQLiObj = null;

	/**
	 * Letzte SQL-Abfrage
	 *
	 * @access public
     * @var    string
     */
	public $lastSQLQuery = null;

	/**
	 * Status der letzten Anfrage
	 *
	 * @access public
     * @var    string
     */
	public $lastSQLStatus = null;

	/**
	 * Status der db-Installation
	 *
	 * @access public
     * @var    boolean
     */
	public $installStatus = true;

	/**
	 * Update Version
	 *
	 * @access public
     * @var    boolean
     */
	public $updateVersion = CWMS_VERSION;

	/**
	 * Update Erfolg / Fehler
	 *
	 * @access public
     * @var    boolean
     */
	public $updError = array();
	public $updSuccess = array();

	
	/**
	 * Verbindet zur Datenbank und gibt ggf. eine Fehlermeldung zurück.
	 * 
	 * @param	string	$server db-Server
	 * @param	string	$user db-USer
	 * @param	string	$password db-Passwort
	 * @param	string	$db Datenbankname
	 * @param	string	$port Port (default = 3306)
	 * @access	public
	 */
	public function __construct($server, $user, $password, $db, $port = '3306')
	{

		//Erstellen eines MySQLi-Objektes
		$this->MySQLiObj = new \mysqli($server, $user, $password, $db);
		
		//Prüfen, ob ein Fehler aufgetreten ist.      
		if (mysqli_connect_errno())
		{
			if(isset($GLOBALS['_GET']['page']) && $GLOBALS['_GET']['page'] == "_install") {
				$this->installStatus = false;
				return false;
			}
			else {	
				trigger_error("MySQL-Connection-Error", E_USER_ERROR);
				echo "Keine Verbindung zum MySQL-Server möglich.";
				die();
			}
		}
		
		//Characterset der Verbindung auf UTF-8 setzen:
		$this->query("SET NAMES utf8");
		#$this->query("SET CHARACTER_SET utf8");
	}
	
	/**
	 * Beendet die Verbindung zur Datenbank bei Beenden eines Skriptes.
	 * 
	 * @access	public
	 */
	public function __destruct()
	{
		if (!mysqli_connect_errno())
			$this->MySQLiObj->close();
	}
	
	

	/**
	 * Führt eine SQL-Anfrage durch.
	 * 
	 * Der optionale Parameter bestimmt, ob das Ergebnis als
	 * Array-Struktur zurückgegeben wird oder als normales MySQL-Resultset
	 * 
	 * @param	string	$sqlQuery SQL-Anfrage
	 * @param	boolean	$resultset Parameter, ob ein Resultset oder ein Array zurückgegeben werden soll (default = false)
	 * 
	 * @access	public
	 * @return	array
	 */
	public function query($sqlQuery, $resultset = false)
	{
		
		//Letzte SQL-Abfrage aufzeichnen:
	    $this->lastSQLQuery = $sqlQuery;

		//Hier kann später die Protokoll-Methode doLog() 
		//aktiviert werden
		#$this->doLog($sqlQuery);

		$result = $this->MySQLiObj->query($sqlQuery);

		//Das Ergebnis als MySQL-Result "plain" zurückgeben
		if ($resultset == true)
		{
			//Status setzen
			if ($result == false)
			{
				$this->lastSQLStatus = false;
			}
			else
			{
				$this->lastSQLStatus = true;
			}

			return $result;
		}
		
		
		$return = $this->makeArrayResult($result);

		return $return;
	}



	/**
	 * Fehlermeldung der letzten Abfrage
	 * 
	 * @return	varchar Die letzte Fehlermeldung wird zurückgegeben
	 * @access	public
	 */
	public function lastSQLError()
	{
		return $this->MySQLiObj->error;
	}
	
	
	
	/**
     * Maskiert einen Parameter für die Benutzung in einer SQL-Anfrage
     * 
     * @param	varchar $value Attributwert
     * 
     * @return	Gibt den Übergebenen Wert maskiert zurück
	 * @access	public
     */
	public function escapeString($value)
	{

		$value = function_exists("mysql_real_escape_string") ? $this->MySQLiObj->real_escape_string($value) : $this->MySQLiObj->escape_string($value);
		
		return $value;

	}



	/**
	 * Array-Struktur der Anfrage
	 * 
	 * Lässt ein Ergebnis aussehen, wie das von DBX
	 * 
	 * @param	$ResultObj MySQLiObject Das Ergebnisobjekt einer MySQLi-Anfrage 
	 * 
	 * @return	boolean/Array Gibt entweder true, false oder eine Ergebnismenge zurück
	 * @access	private
	 */
	private function makeArrayResult($ResultObj)
	{

		if ($ResultObj === false)
		{
			//Fehler trat auf (z.B. Primärschlüssel schon vorhanden)
			$this->lastSQLStatus = false;
			return false . $this->MySQLiObj->error;

		}
		else
			if ($ResultObj === true)
			{
				//UPDATE- INSERT etc. es wird nur TRUE zurückgegeben.
				$this->lastSQLStatus = true;
				return true;

			}
			else
				if ($ResultObj->num_rows == 0)
				{
					//Kein Ergebnis eines SELECT, SHOW, DESCRIBE oder EXPLAIN-Statements
					$this->lastSQLStatus = true;
					return array ();

				}
				else
				{

					$array = array ();

					while ($line = $ResultObj->fetch_array(MYSQLI_ASSOC))
					{
						//Alle Bezeichner in $line klein schreiben
						array_push($array, $line);
					}

					//Status der Abfrage setzen
					$this->lastSQLStatus = true;
					//Das Array sieht nun genauso aus, wie das Ergebnis von dbx
					return $array;
				}

	}



	/**
	 * Protokolliert alle Datenbankzugriffe  
	 * 
	 * @param	string	$sqlQuery Eine SQL-Anfrage, die "gelogt" werden soll
	 * @access	private
	 */
	private function doLog($sqlQuery)
	{

		//Nur wenn kein SELECT
		$substr = substr($sqlQuery, 0, 6);

		//Eintragen
		if ($substr != "SELECT")
		{

			$sql = "INSERT INTO `" . DB_TABLE_PREFIX . "logging` (sql,datum,name) VALUES ".
                   " ('" . $this->escapeString($sqlQuery) . "',".
                   date("H:i   d.m.Y", time()).",'".
                   $this->escapeString($GLOBALS['_SESSION']['username'])."')";
                   
			//Eintragen
			$this->MySQLiObj->query($sql);
		}

	}



	/**
	 * Überprüft ob eine DB-Tabell existiert  
	 * 
	 * @param	string	$table Tabellenname
	 * @access	public
	 */
	public function tableExists($table)
	{
	
		if($table == "")
			return false;

		//Nur wenn kein SELECT
		$sql = "SHOW TABLES LIKE '".$table."'";
		
		if($this->MySQLiObj->query($sql)->num_rows)
			return true;
		else
			return $this->MySQLiObj->query($sql)->num_rows;
	}
	
	

	/**
	 * Erstellt eine Tabellenspalte, falls diese nicht existiert
	 * 
	 * @param	string	$db die zu überprüfende db-Tabelle
	 * @param	string	$column Spalte auf deren Vorhandensein geprüft werden soll
	 * @param	string	$sql auszuführende Datenbankanfrage
	 * @access	public
	 * @return	boolean|string
	 */
	public function addColumn($db, $column, $sql)
	{

		$columns	= $this->query("SHOW COLUMNS FROM `$db`");
		
		foreach($columns as $existCol) {
		
			if($existCol == $column){
				return "exists";
			}
		}      
		
		// Neue Tabelle anlegen
		$result	= $this->query($sql);
		
		if($result === true)
			return true;
		
		return false;
		
	}
	
	

	/**
	 * Ändert die Kollation einer Datenbank
	 * 
	 * @param	string	$db die zu ändernde Datenbank
	 * @param	string	$charset der neue Zeichensatz (default = "utf8)
	 * @param	string	$collation die neue Kollation (default = "utf8_general_ci)
	 * @access	public
	 */
	public function setDbCollation($db, $charset = "utf8", $collation = "utf8_general_ci")
	{

		$db = $this->escapeString($db);
		$charset = $this->escapeString($charset);
		$collation = $this->escapeString($collation);
		
		return $this->query("ALTER DATABASE `$db` DEFAULT CHARACTER SET $charset COLLATE $collation");
		
	}

	
	/**
	 * Löschen von Views
	 * 
     * @access  public
	 * @param	array	$viewTabs
	 * @return	string
	 */
	public function dropViews($viewTabs)
	{
	
		if(!is_array($viewTabs) || count($viewTabs) == 0)
			return false;
		
		$viewStr	= implode("`,`", $viewTabs);
		
		$query		= $this->MySQLiObj->query("DROP VIEW IF EXISTS `" . $viewStr . "`");

		return $query;
	
	}


	
	/**
	 * Erstellt ein Datenbankbackup
	 * 
	 * @param	string	$tables	Tabellen der zu sichernden db einschließen/ausschließen (default = '')
	 * @param	string	$fileSuffix	Suffix für Backup-Datei (default = '')
	 * @param	boolean	$cron bei Ausführung über Cronjob, Namen ändern (default = false)
	 * @access	public
	 * @return	boolean
	 */
	public function makeBackup($tables = "", $fileSuffix = "", $cron = false)
	{
		
		$fp = false;
		
		// Tabelle "errorlog", "locks" und "sessions" ausschließen
		$tables .=	" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."errorlog" .
					" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."locks" .
					" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."sessions";
		
		// Views ignorieren
		$tables .=	" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."menuviewtable0" .
					" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."menuviewtable1" .
					" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."menuviewtable2" .
					" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."menuviewtable3";
		
		// Falls Cronjob
		if($cron)
			$bkpFileName = "db_bkp_cron" . $fileSuffix . "_latest.sql";
		else
			$bkpFileName = date('Ymd-His') . "_db_bkp" . $fileSuffix . ".sql";
		
		// db-Backup ausführen
		system(BACKUP_ROOT . "/mysqldump --opt --add-locks --create-options --disable-keys --extended-insert --quick -h ".DB_SERVER." -u ".DB_BKP_USER." -p".DB_BKP_PASSWORD." ".DB_NAME." ".$tables." > ".BACKUP_DIR . $bkpFileName, $fp);

		if($fp === 0) {
			chmod(BACKUP_DIR . $bkpFileName, 0755); // Rechte setzen
			return true;
		}
		elseif($fp === false)
			return "nosystem";
		else
			return false;
	

	}
	


	/**
	 * Spielt ein Datenbankbackup zurück
	 * 
	 * @param	string	$bkpFile die zu sichernde db
	 * @access	public
	 * @return	boolean
	 */
	public function restoreDB($bkpFile)
	{
		
		$fp 		= false;
		$errorMes	= "";
		$bfArr		= explode(".sql", $bkpFile);
		$bkpFile 	= reset($bfArr) . ".sql";
		
		// db-Backup ausführen
		#system(BACKUP_ROOT . "/mysql -h ".DB_SERVER." -u ".DB_BKP_USER." -p".DB_BKP_PASSWORD." ".DB_NAME." < ".BACKUP_DIR . $bkpFile, $fp1, $fp);
		
		// Temporary variable, used to store current query
		$templine		= "";
		
		// Read in entire file
		$resource		= file(BACKUP_DIR . $bkpFile);
		
		// Loop through each line
		foreach ($resource as $line) {
		
			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '')
				continue;

			// Add this line to the current segment
			$templine .= $line;
			
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';')
			{
				// Perform the query
				$result		= $this->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . $this->lastSQLError() . '<br /><br />');
				
				// register potential error
				if($result !== true)
					$errorMes	.= 'Error performing query &quot;<strong>' . $templine . '&quot;: ' . $this->lastSQLError() . '</strong><br />';
				
				// Reset temp variable to empty
				$templine = '';
			}
		}
		if($errorMes != "")
			return $errorMes;
		else
			$fp	= 0;
		
		if($fp == 0) {
		
			// Update-Script ausführen
			$this->runUpdateScript();
			return true;
		}
		elseif($fp === false)
			return "nosystem";
		else
			return false;

	}
	
	

	/**
	 * Löscht ein Datenbankbackup
	 * 
	 * @param	string	$bkpFile zu löschendes db-file
	 * @access	public
	 * @return	boolean
	 */
	public static function deleteBkp($bkpFile)
	{
		
		$bfArr		= explode(".sql", $bkpFile);
		$bkpFile	= reset($bfArr) . ".sql"; // Dateigrößeninfo entfernen
		
		// db-Backup löschen
		if(file_exists(BACKUP_DIR . $bkpFile)) {
		
			if(unlink(BACKUP_DIR . $bkpFile))
				return true;
			else
				return false;
		}
		else
			return false;
	
	}
	
	

	/**
	 * Listet vorhandene Datenbankbackups auf
	 * 
	 * @param	string	$db die zu sichernde db
	 * @param	string	$token Formular-Token
	 * @access	public
	 * @return	string
	 */
	public static function getBackups($db, $token, $admin = false)
	{
		
		$existBkp = array();
		
		$bkpPath = BACKUP_DIR;
		
		// Falls der Backupordner nicht existiert, diesen neu anlegen
		if(!is_dir($bkpPath))
			mkdir($bkpPath);
		
		// Datenbankbackups ins Array einlesen
		$handle = opendir($bkpPath);
		
		while($content = readdir($handle)) {
			if( $content != ".." && 
				strpos($content, ".") !== 0
			) {
				$fileSize = (int) round(filesize($bkpPath . $content)/1024);
				$fileDate = date("d.m.Y H:i", filemtime($bkpPath . $content));
				$existBkp[] = array("file" => $content,
									"size" => $fileSize,
									"date" => $fileDate
									);
			}
		}
		closedir($handle);

		// Backup-Dateien auflisten
		$bkpList =	'<ul id="dbBackupList" class="editList dbBackup list list-condensed">' . "\r\n";

		// Falls Backup-Dateien vorhanden
		if(count($existBkp) > 0) {
		
			$bkpList .=	'<h3 class="cc-h3 toggle">Crontab-Backups</h3><ul>';
		
			arsort($existBkp); // umgekehrt sortieren (cron-Bkps zuerst)
			
			$i = 1;
			
			foreach($existBkp as $bkp) {
				
				$bkpList .=		(strpos($bkp["file"], "cron") === false && $i == 1 ? '</ul>' .
								'<h3 class="cc-h3 toggle">{s_header:manual} Backups</h3>' .
								'<ul class="editList dbBackup list list-condensed">' : '') .
								'<li class="listItem" data-menu="context" data-target="contextmenu-' . $i . '">' . "\r\n";
				
				// MarkBox
				$bkpList .=		'<label class="markBox">' . "\r\n" .
								'<input type="checkbox" class="addVal" name="del_bkp[]" value="' . htmlspecialchars($bkp["file"]) . '" />' . "\r\n" .
								'</label>' . "\r\n";				
							
				$bkpList .=		'<span class="bkpFile"><a href="' . PROJECT_HTTP_ROOT . '/_backup/' . $bkp["file"] . '" target="_blank">' . $bkp["file"] . '</a></span><span class="fileSize">(' . $bkp["size"] . ' kB)</span><span class="fileDate">' . $bkp["date"] . '</span>' . "\r\n";
				
				
				// Falls Admin
				if($admin) {
				
					$bkpList .=	'<span class="editButtons-panel" data-id="contextmenu-' . $i . '">' . "\r\n";
					
					$bkpList .=	'<form action="' . ADMIN_HTTP_ROOT . '?task=bkp" method="post" name="adminfm" data-ajax="false">' . "\r\n";
		
					// Button restore
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "restore_bkp",
											"class"		=> "button-icon-only",
											"value"		=> "",
											"title"		=> "{s_title:restorebkp}",
											"attr"		=> 'data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "restoredb"
										);
					
					$bkpList .=	ContentsEngine::getButton($btnDefs);
					
					$bkpList .=	'<input type="hidden" name="restore_bkp" value="' . $bkp["file"] . '" />' . "\r\n"  . 
								'<input type="hidden" name="token" value="' . $token . '" />' . "\r\n" .
								'</form>' . "\r\n";
								
					$bkpList .=	'<form action="' . ADMIN_HTTP_ROOT . '?task=bkp" method="post" name="adminfm">' . "\r\n";
		
					// Button delete
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "del_bkp",
											"class"		=> "button-icon-only",
											"value"		=> "",
											"title"		=> "{s_title:delbkp}",
											"attr"		=> 'data-menuitem="true" data-id="item-id-' . $i . '"',
											"icon"		=> "delete"
										);
					
					$bkpList .=	ContentsEngine::getButton($btnDefs);
					
					$bkpList .=	'<input type="hidden" name="del_bkp" value="' . $bkp["file"] . '" />' . "\r\n" .
								'<input type="hidden" name="token" value="' . $token . '" />' . "\r\n" .
								'</form>' . "\r\n";
					
					$bkpList .=	'</span>' . "\r\n";
				}
				
				$bkpList .=		'</li>'."\r\n";
				
				if(strpos($bkp["file"], "cron") !== false)
					$i = 1;
				else $i++;
			}
			
			$bkpList .= '</ul>' . "\r\n";
		}
		else
			$bkpList =	'<h3 class="cc-h3 toggle">Crontab-Backups</h3>' . "\r\n" .
						'<h3 class="cc-h3 toggle">{s_header:manual} Backups</h3>' . "\r\n";
		
		$bkpList .= '</ul>' . "\r\n";
		
		return $bkpList;
			
	}
	
	
	
	/**
	 * runUpdateScript
	 * @return boolean
	 */
	private function runUpdateScript()
	{
	
		$updateScript = SYSTEM_DOC_ROOT . '/update/updateCore.inc.php';
		 
		// Falls ein Update-Script vorliegt, dieses ausführen
		if(!file_exists($updateScript))
			return false;
		
		$update	= "db";
		
		require_once $updateScript; // Update-Script einbinden		

		// ConciseCoreUpdater instance
		$o_CoreUpdater = new ConciseCoreUpdater($this, $this->updateVersion);
		$o_CoreUpdater->runCWMSUpdater($update);
		
		// Objektinstanz aus updateScript
		$this->updError		= array_merge($this->updError, $o_CoreUpdater->errorUpdScript);
		$this->updSuccess	= array_merge($this->updSuccess, $o_CoreUpdater->successUpdScript);
		
		if(count($o_CoreUpdater->errorUpdScript) > 0)
			return false;
		else
			return true;
	}
	
}
