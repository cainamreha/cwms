<?php
namespace Concise;


require_once SYSTEM_DOC_ROOT . "/inc/elements/interface.ConfigElements.php"; // ConfigElements-Interface einbinden

/**
 * Klasse ConfigElementFactory
 * 
 */

class ConfigElementFactory extends Admin
{

	protected $a_POST			= array();
	protected $conType			= "";
	protected $conValue			= "";
	protected $conAttributes	= array();
	protected $conNum			= 0;
	protected $conCount			= 0;
	protected $output			= "";
	protected $dbUpdateStr		= "";
	protected $wrongInput		= array();
	protected $textAreaCount	= 0;	
	protected $scriptTag		= "";
	public $isNewElement		= false;
	
	
	/**
	 * Instanziert ein Element-Objekt
	 * 
     * @param	string	$type			Inhaltselementen-Typ
     * @param	string	$options		Instanzierungsparameter
     * @param	string	$elementKind	Elementart (e.g. core/plugin)
	 * @access	public
     * @return  string
	 */
	public static function create($type, $options, $elementKind, $DB, &$o_lng)
	{
	
		$conType		= $type;
		$classNameExt	= 'ConfigElement';

		// Klassen-Pfad bestimmen
		switch($elementKind) {
		
			// Falls Plug-in
			case "plugin":
				$classNameExt		= 'Plugin' . $classNameExt;
				$elementClassPath	= PLUGIN_DIR . $type . '/config_' . $type . '.inc.php'; // Pfad zur Plug-in-Klasse
				break;
		
			// Falls Core-Inhaltselement
			default:
				$elementClassPath	= SYSTEM_DOC_ROOT . "/inc/elements/config_" . $type . ".inc.php"; // Pfad zur DataElement-Klasse
		}
			
		// Falls Datei nicht vorhanden
		if(file_exists($elementClassPath)) {
			
			require_once $elementClassPath; // Element-Klasse einbinden
			
			$className	= 'Concise\\' . ucfirst(str_replace("-", "", $type)) . $classNameExt;
			
			$obj = new $className($options, $DB, $o_lng); // Objekt instanzieren und zurückgeben
				return $obj;
		}
		else
			throw new \Exception('<h4 class="cc-contype-heading cc-h4">' . $conType . '</h4>' . "\r\n" . '<p class="error">{s_error:unknowncon}: <strong>' . $conType . '</strong></p>' . "\r\n");
	
	}	


	/**
	 * Rückgabe-Array eines ConfigElements
	 * 
	 * @access	public
     * @return  string
	 */
	public function makeOutputArray()
	{

		$outputArr	= array(	"output"	=> $this->output,
								"update"	=> $this->dbUpdateStr,
								"error"		=> $this->wrongInput,
								"textareas"	=> $this->textAreaCount,
								"script"	=> $this->scriptTag
							);
	
		return $outputArr;
		
	}


	/**
	 * Element-Wrapper mit Attributen (id, class, style) zurückgeben
	 * 
     * @param	array	$conAttributes	Element-Wrapper-Attribute
	 * @access	public
     * @return  string
	 */
	public static function getContentElementPost($conAttributes, $content, $defClass = "")
	{
		
		return $output;
	
	}

}
