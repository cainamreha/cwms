<?php
namespace Concise;


/**
 * Klasse zum Sperren von Datenbankeinträgen
 *
 */

class Locks
{

    /**
     * Datenbankobjekt
     *
     * @access	private
     * @var   	string
     */
	private $DB;
	
    /**
     * Dauer der Sperre in Sekunden
     *
     * @access	private
     * @var   	string
     */
	private $lockDuration;
	
    /**
     * DB table locks
     *
     * @access	private
     * @var   	string
     */
	private $locksTable = "locks";

	/**
	 * Konstruktor der Klasse
	 * 
     * @access	public
	 * @param	$lockDuration	int 	Dauer der Sperre
	 * @param	$DB				object	DB-Objekt
	 */
	public function __construct($lockDuration, $DB)
	{
		
		// Setzen der Zeitspanne einer Sperre
		$this->lockDuration = $lockDuration;

		// Referenz auf das globale Datenbankobjekt
		$this->DB = $DB;

		// DB table locks
		$this->locksTable = DB_TABLE_PREFIX . $this->locksTable;
	
	}

	/**
	 * Sperrt eine Zeile in einer Tabelle.
	 *
	 * Im Erfolgsfall gibt die Methode true zurück, sonst false. 
	 * 
	 * @param varchar Primärschlüsselwert der zu sperrenden Zeile
	 * @param varchar Name der Tabelle
	 * @param int Benutzernummer
     * @access	public
	 * @return Array Gibt eine Statusmeldung (true/false) und eine Ergebnismenge in einem Array zurück
	 */
	public function setLock($rowID, $tablename, $userID)
	{

		$resultArray = array ();

		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->locksTable`");
		
		//Veraltete Einträge löschen
		$this->deleteOldLocks();
		
		$sql = "INSERT INTO `$this->locksTable` (`rowID`,`tablename`,`lockedBy`,`lockedUntil`) VALUES ('".$this->DB->escapeString($rowID)."','".$this->DB->escapeString($tablename)."','".$this->DB->escapeString($userID)."',". (time() + $this->lockDuration).")";

		$result = $this->DB->query($sql);
		
		//Abfrage, ob ein Datensatz eingetragen wurde:
		if ($this->DB->MySQLiObj->affected_rows == 1)
		{
			array_push($resultArray, true);
		}
		else
		{
			//Affected_rows ist -1, da der Eintrag schon vorhanden ist.
			array_push($resultArray, false);

			$sql = "SELECT * FROM `$this->locksTable` WHERE "." `rowID` = '".$this->DB->escapeString($rowID)."' AND `tablename` = '".$this->DB->escapeString($tablename)."'";

			$result = $this->DB->query($sql);
			array_push($resultArray, $result[0]);

		}
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		return $resultArray;

	}

	/**
	 * Liest ggf. eine Sperre auf eine Zeile in einer Tabelle aus
	 * 
	 * @param varchar Primärschlüsselwert der zu entsperrenden Zeile (bei Einzelseitencheck ein Benutzername)
	 * @param varchar Name der Tabelle (editpages bzw. langs bei Genlock, sonst der Tabellenname)
	 * @param boolean Wenn true, werden Benutzerlocks des mitgegebenen Benutzers (rowID) ermittelt
     * @access	public
     * @return	array
	 */
	public function readLock($rowID, $tablename, $own = false)
	{

		$resultArray = array ();
		
		// Falls own true ist, Sperre des Benutzers (rowID) auf Einzeldatensatz (Edit/Tpl) feststellen
		if($own === true)
			$sql = "SELECT * FROM `$this->locksTable` WHERE `lockedBy` = '".$this->DB->escapeString($rowID)."' AND `tablename` LIKE '".$this->DB->escapeString($tablename)."%'";
			
		// Falls "contents" mitgegeben wird, nach contents* NICHT des Benutzers in Locks suchen, um Sperre auf Einzeldatensatz (Edit/Tpl) festzustellen
		elseif($tablename == "contents")
			$sql = "SELECT * FROM `$this->locksTable` WHERE `lockedBy` != '".$this->DB->escapeString($rowID)."' AND `tablename` LIKE '".$this->DB->escapeString($tablename)."%'";
			
		// Sonst Genlock bzw. Pageslock auslesen
		else
			$sql = "SELECT * FROM `$this->locksTable` WHERE `rowID` = '".$this->DB->escapeString($rowID)."' AND `tablename` = '".$this->DB->escapeString($tablename)."'";

		$result = $this->DB->query($sql);

		if(count($result) > 0) {
		
			array_push($resultArray, true);
			array_push($resultArray, $result[0]);
		}
		else
		
			array_push($resultArray, false);

		return $resultArray;
		
	}

	/**
	 * Löscht die Sperre auf eine Zeile in einer Tabelle
	 * 
	 * @param varchar Name der Tabelle
	 * @param varchar Primärschlüsselwert der zu entsperrenden Zeile
     * @access	public
	 */
	public function deleteLock($tablename, $rowID)
	{
	
		$sql = "DELETE FROM `$this->locksTable` WHERE "." rowID = '".$this->DB->escapeString($rowID)."' AND  "." tablename = '".$this->DB->escapeString($tablename)."'";

		$this->DB->query($sql);

	}

	/**
	 * Löscht alle Sperren eines Users in einer Tabelle
	 * Damit können alle Sperren auf einmal entfernt werden.
	 * 
	 * @param int Benutzernummer
	 * @param varchar Name der Tabelle (optional)
     * @access	public
	 */
	public function deleteAllUserLocks($userID, $tablename = "")
	{
		
		$sqlExt = "";
		
		if($tablename != "")
			$sqlExt = " AND  "." tablename = '".$this->DB->escapeString($tablename)."'";
			
		$sql = "DELETE FROM `$this->locksTable` WHERE `lockedBy` = '".$this->DB->escapeString($userID)."'".$sqlExt;

		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->locksTable`");
		
		$this->DB->query($sql);
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

	}

	/**
	* Löscht alle alten Sperren in der LOCK-Tabelle.
	* 
     * @access	public
	*/
	public function deleteOldLocks()
	{

		$sql = "DELETE FROM `$this->locksTable` WHERE `lockedUntil` < ".time();

		$this->DB->query($sql);

	}

}
