<?php
namespace Concise;



/**
 * Abstraktionsschicht für die Datenbank
 * 
 * Verbindet zur Datenbank und kapselt alle Anfragen an die 
 * Datenbank. Nutzbar mit diversen Datenbanken (MySQL, SQLite, Oracle, etc.) 
 * 
 */

class PDO
{

	//Datenbankverbindungsobjekt 
	public $PDO = null;
	//aktuelles preparedStatement 
	public $preparedStatement = null;

	/**
	 * Verbindet zur Datenbank und gibt ggf. eine
	 * Fehlermeldung zurück.
	 * 
	 */
	public function __construct($dsn, $user, $password)
	{
		try
		{
			//Neues PDO-Objekt
			$this->PDO = new ::PDO($dsn,$user,$password);
			//Fehlermeldungen sollen "geworfen" werden
			$this->PDO->setAttribute(::PDO::ATTR_ERRMODE, ::PDO::ERRMODE_EXCEPTION);	
		}
		catch (PDOException $e)
		{
			//Fehlerbehandlung (bspw. Email an Admin)
			die('<div style="color:red;">'.$e->getMessage().'</div>');	
    	}
	
	}

	/**
	 * Führt eine SQL-Anfrage durch.
	 * 
	 * @param text Die SQL-Anfrage
	 *
	 * @return Array Gibt eine Ergebnismenge zurück
	 */
	public function query($sql)
	{
		try
		{
			//PDO-Anfrage durchführen
			$pdoStmt  = $this->PDO->query($sql);
			//Liegt eine leere Ergebnismenge vor 
		    ($pdoStmt->rowCount()==0)? $return = array():
			//Array mit den Daten
			$return = $pdoStmt->fetchAll();
			//Statement schließen
			$pdoStmt->closeCursor();
			return $return;	
				
		}
		catch(PDOException $e)
		{
			//Fehlerbehandlung (bspw. Email an Admin)
			echo '<div style="color:red;">'.$e->getMessage().'</div>';	
			return false;				
		}
	
	}
	
	/**
	 * Legt ein "prepared Statement" an
	 * 
	 * @param String Statement mit Platzhalter-Parametern 
	 */ 
	public function prepareStatement($statement)
	{
		//Prepared Statement vorbereiten
		$this->preparedStatement = $this->PDO->prepare($statement);
		if($this->preparedStatement===false)
		{
			//Fehlerbehandlung (bspw. Email an Admin)
			echo '<div style="color:red;">Prepared Statement konnte nicht vorbereitet werden.</div>';
		}			
	}
	
	/**
	 * Führt ein zuvor angelegtes preparedStatement aus
	 * 
	 * @param Array Die Parameter für das prepared Statement.
	 * 
	 * @return Array Ergebnis der Anfrage
	 */
	public function execute($params = array())
	{
		//Wenn noch kein Statement angelegt ist, wird hier abgebrochen.
		if($this->preparedStatement==null)return false;
		
		try
		{
			//PDO-Anfrage durchführen
			$this->preparedStatement->execute($params);
			//Wenn keine Daten zurück kamen 
			if($this->preparedStatement->columnCount()==0)return array();
			//Andernfalls die Daten als Array zurückgeben 
			return $this->preparedStatement->fetchAll();
				
		}
		catch(PDOException $e)
		{
			//Fehlerbehandlung (bspw. Email an Admin)
			echo '<div style="color:red;">'.$e->getMessage().'</div>';	
			return false;				
		}	
	}

	
}
