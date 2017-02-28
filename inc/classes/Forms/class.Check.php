<?php
namespace Concise;



//Konfigurationsskript für die Tests
require PROJECT_DOC_ROOT."/inc/classes/Forms/config.CheckDataValidity.php";	

class Check
{
	//Datentypvariablen
	private $specificType = null;
	private $generalType = null;
	private $signed = true;
	private $value = null;
	private $typestring = null;
	
	private $setTypesArray;
	private $numericTypesArray;
	private $complexNumericTypesArray;
	private $dateTypesArray;
	private $stringTypesArray;
	private $byteTypesArray;
	
	
	
	/**
	 * Ruft den Check auf.
	 * 
	 * @return boolean Ergebnis der Wertüberprüfung
	 */
	public function checkData($typestring, $value)
	{
	
		$this->setTypesArray = array("ENUM","SET");
		$this->numericTypesArray = array("TINYINT","SMALLINT","MEDIUMINT","BIGINT","INT");
		$this->complexNumericTypesArray = array("FLOAT","DOUBLE","DECIMAL");
		$this->dateTypesArray = array("DATETIME","DATE","TIMESTAMP","TIME","YEAR");
		$this->stringTypesArray = array("VARCHAR","CHAR","TINYTEXT","MEDIUMTEXT","LONGTEXT","TEXT");
		$this->byteTypesArray = array("TINYBLOB","MEDIUMBLOB","LONGBLOB","BLOB","VARBINARY","BINARY");
		
		
		//Typestring speichern
		$this->typestring = $typestring;
		//Den Wert speichern.
		$this->value = $value;
		//Den Typ festlegen:
		$this->parseType();
		
		//Falls value ein Array ist (z.B. bei Mehrfachauswahl), das Array serialisieren
		if(is_array($this->value))
			$this->value = serialize($this->value);
		
		switch ($this->generalType)
		{

			case "Numeric" :
				return $this->checkNumericType();
			case "ComplexNumeric" :
				//Für decimal eine eigene Routine
				if ($this->specificType == "DECIMAL")
				{
					return $this->checkDecimalType();
				}
				else
				{
					return $this->checkComplexNumericType();
				}
			case "Set" :
				return $this->checkSetType();
			case "Date";
			case "date";
				return $this->checkDateType();
			case "String" :
				return $this->checkStringType();
			case "Byte" :
				return $this->checkByteType();
				//Falls keiner der vorigen Fälle...nicht bekannter Typ
			default :
				return false;

		}

	}

	/**
	 * Gibt den spezifischen Datentyp aus.
	 * 
	 * @return varchar Gibt den spezifische Typ des Attributs zurück
	 */
	public function getType()
	{

		return $this->specificType;

	}

	/**
	 * Findet heraus, welchen Typ das Attribut hat.
	 */
	private function parseType()
	{
									
		//Zeigt an, ob schon ein "Match" gefunden wurde.
		$searching = true;

		//Typ des $typestrings in allen Typ-Arrays testen.
		/****NUMERIC TYPES****/
		$searching = $this->parseSpecificArray($this->numericTypesArray, "Numeric");

		/*****SET AND ENUM******/
		//Auf enum Testen
		if ($searching == false)
		{
			$searching = $this->parseSpecificArray($this->setTypesArray, "Set");
		}

		/****COMPLEX NUMERIC TYPES****/
		if ($searching == false)
		{
			$searching = $this->parseSpecificArray($this->complexNumericTypesArray, "ComplexNumeric");
		}
		/****DATE-TYPES****/
		if ($searching == false)
		{
			$searching = $this->parseSpecificArray($this->dateTypesArray, "Date");
		}

		/****STRING-TYPES****/
		if ($searching == false)
		{
			$searching = $this->parseSpecificArray($this->stringTypesArray, "String");
		}

		/****BYTE-TYPES****/
		if ($searching == false)
		{
			$searching = $this->parseSpecificArray($this->byteTypesArray, "Byte");
		}
	}

	/**
	 * Ruft die jeweilige Testfunktion anhand des Datentyps auf.
	 * 
	 * @param Array Spezifisches Datentyp-Array aus der Konfigurationsdatei
	 * @param varchar Typgruppe des Datentyps 
	 * 
	 * @return boolean Wird zum Abbrechen der Schleife benutzt
	 */
	private function parseSpecificArray($typeArray, $generalType)
	{

		//Reseten des Zeigers im NumericArray
		reset($typeArray);

		//Solange durchlaufen, wie Elemente im Array sind 
		//und noch nichts gefunden wurde.
		while ($type = current($typeArray))
		{
			//Den typestring prüfen...
			if (stripos(strtolower($this->typestring), strtolower($type)) !== false)
			{
				$this->specificType = $type;
				$this->generalType = $generalType;
				return true;
			}

			//Zeiger weiterrücken.
			next($typeArray);

		}

	}

	/**
	 * Überprüfung der numerischen Typen
	 * 
	 * @return boolean Gibt den Wert der Wertüberprüfung zurück
	 */
	private function checkNumericType()
	{

		//Erlaubte Anzahl an Stellen ermitteln
		$maxLength = $this->getSetOptions(true);

		//Falls Int zu viele Stellen
		if($maxLength && strlen($this->value) > $maxLength)
		{

			return false;

		}
		
		//Hier noch prüfen, ob der Typ "unsigned" ist
		if (stripos(strtolower($this->typestring), "unsigned") !== false)
		{
			$this->signed = false;
		}

		//Minimum-Konstante
		$minConstant = $this->specificType."_";
		if (!$this->signed)
		{
			$minConstant .= "UNSIGNED_";
		}
		$minConstant .= "MIN";

		//Maximum-Konstante
		$maxConstant = $this->specificType."_";
		if (!$this->signed)
		{
			$maxConstant .= "UNSIGNED_";
		}
		$maxConstant .= "MAX";

		//Der Wert des numerischen Typen muss nun numerisch sein und zwischen
		//den beiden Grenzen liegen.
		if (is_numeric($this->value))
		{
			if (($this->value >= constant($minConstant)) && ($this->value <= constant($maxConstant)))
			{
				//Gültig
				return true;
			}
			else
			{
				//Ist zu groß oder zu klein
				return false;
			}
		}
		else
		{
			//Ist keine Zahl
			return false;
		}
	}

	/**
	* Überprüfung der komplexen numerischen Typen
	* FLOAT und DOUBLE. Für DECIMAL wird weitergeleitet.
	* 
    * @return boolean Gibt den Wert der Wertüberprüfung zurück
	*/
	private function checkComplexNumericType()
	{

		
		//Float mit Punkt erzwingen
		$this->value = self::getFloat($this->value);
		
		//Minimum-Konstante
		$minConstant = $this->specificType."_MIN";

		//Maximum-Konstante
		$maxConstant = $this->specificType."_MAX";

		//Wenn UNSIGNED, dann dürfen keine negativen Werte gespeichert werden, 
		//aber die obere Grenze verschiebt sich nicht.

		//Der Wert des numerischen Typen muss nun numerisch sein und zwischen
		//den beiden Grenzen liegen.
		if (is_numeric($this->value))
		{

			//Negative Werte erlaubt: 
			if ($this->signed)
			{
				//Dann zwischen oberer und unterer Grenze   	
				if (($this->value >= constant($minConstant)) && ($this->value <= constant($maxConstant)))
				{
					//Gültig
					return true;
				}
				else
				{
					//Ist zu groß oder zu klein
					return false;
				}
			}
			else
			{
				//UNSIGNED also keine negativen Werte
				if (($this->value >= 0) && ($this->value <= constant($maxConstant)))
				{
					//Ist gültig
					return true;
				}
				else
				{
					//ist zu große oder zu klein
					return false;
				}
			}
		}
		else
		{
			//Ist keine Zahl
			return false;
		}
	}

	/**
	 * Prüft den Decimal-WERT
	 * 
	 * @return boolean Gibt den Wert der Wertüberprüfung zurück
	 */
	private function checkDecimalType()
	{
		
		//Float mit Punkt erzwingen
		$this->value = self::getFloat($this->value);
		
		//Überprüfen, ob ein Minus vorhanden ist.
		//Muss an erster Stelle stehen.
		if ($this->value[0] == "-")
		{
			//Das Minus entfernen, da es für die Gültigkeit 
			//keinen Unterschied macht.
			$this->value = substr($this->value, 1, strlen($this->value));
		}

		$result = array ();
		//Regulärer Ausdruck
		preg_match_all("/\((\d*),(\d*)\)/", $this->typestring, $result);

		//Gesamtstellen: decimal(20,2)...
		$digits = $result[1][0];
		//Nachkommastellen
		$digitsRightFromPoint = $result[2][0];
		$digitsLeftFromPoint = $digits - $digitsRightFromPoint;

		//Der Wert muss einen Punkt enthalten.
		$point = stripos($this->value, ".");

		//Wenn kein Punkt vorhanden, gibt es nur einen linken Teil
		//und der Punkt wird hier als Strnglänge gesetzt.
		if ($point === false)
		{
			$point = strlen($this->value);
		}

		//Die Teile vor und hinter des Punktes
		$left = substr($this->value, 0, $point);
		$right = substr($this->value, $point +1, (strlen($this->value) - $point));

		//Linker und rechter Prüfwert als regulärer Ausdruck
		$leftRegexp = "/^[0-9]{0,".$digitsLeftFromPoint."}$/";
		$rightRegexp = "/^[0-9]{0,".$digitsRightFromPoint."}$/";

		if ((preg_match($leftRegexp, $left)) && (preg_match($rightRegexp, $right)))
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Überprüfung der Datums-Typen
	 * 
	 * @return boolean Gibt den Wert der Wertüberprüfung zurück 
	 */
	private function checkDateType()
	{
	
		//Wenn Datentyp DATE:
		if(strtoupper($this->specificType)=="DATE"){
		  	return self::getDateString($this->value, true); // Datumstring überprüfen
		}

		//Konstantennamen zusammenbauen und holen
		$regexpString = constant(strtoupper($this->specificType)."_REGEXP");

		//Wert mit regulärem Ausdruck prüfen
		 
        return preg_match($regexpString, $this->value);

	}

	/**
	 * Überprüfung der String-Typen
	 * 
	 * @return boolean Gibt den Wert der Wertüberprüfung zurück
	 */
	private function checkStringType()
	{

		//Erlaubte Anzahl an Stellen ermitteln
		$maxLength = $this->getSetOptions(true);

		//Falls Varchar zu viele Stellen
		if($maxLength && strlen($this->value) > $maxLength)
		{

			return false;

		}
		
		$maxChars = constant(strtoupper($this->specificType)."_MAXCHARS");

		//Liegt die Characteranzahl des Strings 
		//unter der MAXCHARACTER-Anzahl
		if (strlen($this->value) <= $maxChars)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Überprüfung der Byte-Typen
	 * 
	 * @return boolean Gibt den Wert der Wertüberprüfung zurück
	 */
	private function checkByteType()
	{

		$maxBytes = constant(strtoupper($this->specificType)."_MAXBYTES");

		//Liegt die Byteanzahl der Datei unter der MAXBYTES-Anzahl?
		if ($this->value <= $maxBytes)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Überprüfung des SET-Typs
	 * 
	 * @return boolean Gibt den Wert der Wertüberprüfung zurück
	 */
	private function checkSetType()
	{
		//Array mit den möglichen Optionen des "Sets"
		$availableOptions = $this->getSetOptions();

		//Testroutine für ENUM
		if ($this->specificType == "ENUM")
		{

			//Der Wert muss einem aus dem Array entsprechen:
			return in_array($this->value, $availableOptions);

		}
		else
			if ($this->specificType == "SET")
			{
				//Es muss ein Array übergeben worden sein.
				//Alle Werte des Arrays müssen auch in den availableOptions stehen

				//Es dürfen in $this->value keine Werte enthalten sein, die nicht auch
				//in $availableOptions drin sind. Also muss das Array leer sein.
				$result = array_diff($this->value, $availableOptions);

				if (count($result) == 0)
				{
					return true;
				}
				else
				{
					return false;
				}

			}

	}

	/**
	 * Extrahiert die SET-Optionen aus dem Datentyp-String
	 * 
	 * @param boolean true bei Rückgabe nur eines Wertes (default = false)
	 * @return Array Liste mit möglichen Set-Optionen
	 */
	private function getSetOptions($singleValue = false)
	{

		if(!stripos($this->typestring, "("))
			return false;
			
													 
		//Position des ersten Klammer
		$firstBracket = stripos($this->typestring, "(");
		//Position des zweiten Klammer
		$secondBracket = strripos($this->typestring, ")");

		//alles dazwischen holen: set('value1','value2') 
		//dann haben wir die Liste 'value1', 'value2'
		$valueList = substr($this->typestring, ($firstBracket +1), ($secondBracket - $firstBracket -1));


		if($singleValue)
		
			return $valueList;
		
		
		//beim Komma trennen:
		$values = explode(",", $valueList);

		for ($i = 0; $i < count($values); $i ++)
		{

			$values[$i] = substr($values[$i], 1, strlen($values[$i]) - 2);
		}

		return $values;
	
	}

	/**
	 * Gibt eine DB-taugliche formatierte Float-Zahl zurück (Dezimalzeichen "," wird durch "." ersetzt)
	 * 
     * @param	string Float
	 * @access	public
	 * @return	string
	 */
	public static function getFloat($float)
	{
		
		$chkFloat = substr_count($float, ",");
		
		if($chkFloat == 1) {

			$float = (float) str_replace(",", ".", $float);
		
		}
		
		return $float;
	}

	/**
	 * Gibt einen Zehnstelliges Datum im Format (yyyy-mm-dd) (zurück
	 * 
     * @param	string	Datum
     * @param	Boolean check nur auf Datum überprüfen
	 * @return boolean Gibt den Wert der Wertüberprüfung zurück 
	 */
	public static function getDateString($date, $check = false)
	{
		
		//String überprüfen
		if(strlen($date) != 10 || !preg_match("/[0-9]{2}[\.-][0-9]{2}[\.-][0-9]{4}/", $date)) {
			return false;
		}
		//Nach deutschem Format trennen
		elseif(substr_count($date, ".") == 2) {
			
			//Jahr,Monat und Tag aufspalten
			$parts = explode(".",$date);
			
			if(!is_numeric($parts[0]) && !is_numeric($parts[1]) && !is_numeric($parts[2]))
				return false;
			
			if($check)
				return checkdate($parts[1],$parts[0],$parts[2]);
			else
				return $parts[2]."-".$parts[1]."-".$parts[0];
		}
		//Nach englischem Format trennen
		elseif(substr_count($date, "-") == 2) {
			
			//Jahr,Monat und Tag aufspalten
			$parts = explode("-",$date);
			
			if(!is_numeric($parts[0]) && !is_numeric($parts[1]) && !is_numeric($parts[2]))
				return false;
			
			if(strlen($parts[0]) == 4) { // Falls der erste Parameter das Jahr enthält
				$year	= $parts[0];
				$mon	= $parts[1];
				$day	= $parts[2];
			}
			else {
				$year	= $parts[2];
				$mon	= $parts[0];
				$day	= $parts[1];
			}
			
			if($check)
				return checkdate($mon,$day,$year);
			else
				return $year."-".$mon."-".$day;
		}
		else
		  return false;
	}

}
