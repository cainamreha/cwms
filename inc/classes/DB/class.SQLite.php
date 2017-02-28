<?php
namespace Concise;


#namespace System\Database;

class SQLite
{
	
	//SQLite-Objekt
	private $dbObj = false;

	/**
	 * Konstruktor
	 * 
	 * Öffnet die übergebene Datei als SQLite Datenbank
	 * 
	 * @param Dateiname der SQLite-Datenbankdatei
	 */
	public function __construct($filename)
	{
	    //Öffnen der Datenbank (ggf. wird diese angelegt)
		if(!$this->dbObj = sqlite_open($filename))die();		
	}
	
	
	/**
	 * Schließt die Datenbankverbindung
	 */
	public function __destruct()
	{
		//Datenbankverbindung schliessen (wenn geöffnet)
		if($this->dbObj)sqlite_close($this->dbObj);
	}
	
	/**
	 * Anfrage an die Datenbank stellen
	 * 
	 * @param String SQL-Anfrage an die Datenbank
	 * @return Array Ergebnismenge
	 */
	 public function query($sql)
	 {
	    //Query result als Array zurückgeben(zur direkten Weiterverarbeitung)
	    $data = sqlite_array_query($sql,$this->dbObj);
	   	 
	    if($data === false) 
	    {
	    	echo '<span style="color:red;"><strong>Bei der Ausführung des folgenden Statements<br />';
	    	echo '<em>'.$sql.'</em><br />';
	    	echo 'trat folgender Fehler auf:</strong> '.$this->lastSQLError().'</span><br />';
	    }
	    
	    return $data;
	    
	 }
	
	 /**
	  * Gibt den zuletzt aufgetretenen Fehler aus
	  * 
	  * @return String Fehlermeldung
	  */
	 public function lastSQLError()
	 {
	 	return sqlite_error_string(sqlite_last_error($this->dbObj));
	 }	
	
	
		
}
