<?php
namespace Concise;


require_once PROJECT_DOC_ROOT . "/inc/classes/Contents/class.Contents.php"; // Contents-Klasse einbinden
require_once PROJECT_DOC_ROOT . "/inc/classes/Elements/interface.Elements.php"; // Elements-Interface einbinden

/**
 * Klasse ElementFactory
 * 
 */

class ElementFactory extends Contents
{

	protected $conValue			= "";
	protected $conAttributes	= array();
	protected $conTable			= "";
	protected $conNum			= 0;
	protected $conCount			= 0;
	protected $conType			= "";
	protected $conSubType		= "";
	
	/**
	 * Instanziert ein Element-Objekt
	 * 
     * @param	string	$type			Inhaltselementen-Typ
     * @param	string	$options		Instanzierungsparameter
     * @param	string	$elementKind	Elementart (e.g. core/plugin)
     * @param	object	$DB				DB-Objekt
     * @param	object	$o_lng			Lng-Objekt
     * @param	object	$o_page			Page
	 * @access	public
     * @return  object
	 */
	public static function create($type, $options, $elementKind, $DB, &$o_lng, &$o_page)
	{
	
		$conType		= $type;
		$classNameExt	= 'Element';

		// Klassen-Pfad bestimmen
		switch($elementKind) {
		
			// Falls Plug-in
			case "plugin":
				$classNameExt		= 'Plugin' . $classNameExt;
				$elementClassPath	= PLUGIN_DIR . $type . '/create_' . $type . '.inc.php'; // Pfad zur Plug-in-Klasse
				break;
		
			// Falls Datenmodul
			case "data":
				$type				= $elementKind;
				$elementClassPath	= PROJECT_DOC_ROOT . "/inc/classes/Elements/class.DataElement.php"; // Pfad zur DataElement-Klasse
				break;
		
			// Falls System- oder Core-Inhaltselement
			default:
				$elementClassPath	= PROJECT_DOC_ROOT . "/inc/classes/Elements/class." . ucfirst($type) . "Element.php"; // Pfad zur System-/Core-Element-Klasse
		}
			
		// Falls Datei nicht vorhanden
		if(file_exists($elementClassPath)) {
			
			require_once $elementClassPath; // Element-Klasse einbinden
			
			$className	= 'Concise\\' . ucfirst(str_replace("-", "", $type)) . $classNameExt;
			
			$obj = new $className($options, $DB, $o_lng, $o_page); // Objekt instanzieren und zurückgeben
			
			return $obj;
		}
		else
			throw new \Exception('<p class="notice error">{s_error:unknowncon}: ' . $conType . '.</p>');
	
	}	



	/**
	 * Element-Wrapper mit Attributen (id, class, style) zurückgeben
	 * 
     * @param	array	$conAttributes	Element-Wrapper-Attribute
     * @param	array	$content		Element-Content-String
     * @param	array	$defClass		Default class attributes (default = '')
	 * @access	public
     * @return  string
	 */
	public static function getContentElementWrapper($conAttributes, $content, $defClass = "")
	{
			
		// Ggf. Wrapper div
		if(!empty($conAttributes['div'])) {
			$content =	'<div' . (!empty($conAttributes['divid']) ? ' id="' . (htmlspecialchars($conAttributes['divid'])) . '"' : '') . (!empty($conAttributes['divclass']) ? ' class="' . htmlspecialchars($conAttributes['divclass']) . '"' : '') . '>' . PHP_EOL .
						$content .
						'</div>' . PHP_EOL;
		}

		
		// Element div
		if(!empty($conAttributes['type'])) {
			$defClass	.= ($defClass != "" ? ' ' : '') . 'cc-element cc-con-' . $conAttributes['type'];
			$conAttributes["class"] .=  ($conAttributes["class"] != "" ? ' ' : '') . $defClass;
		}
		
		$output = '<div';
	
		// id
		if($conAttributes["id"] != "")
			$output .= ' id="' . $conAttributes["id"] . '"';
		
		// class
		if($conAttributes["class"] != "")
			$output .= ' class="' . $conAttributes["class"] . '"';
	
		// style
		if($conAttributes["style"] != "")
			$output .= ' style="' . $conAttributes["style"] . '"';
		
		// data-attr
		foreach($conAttributes as $key => $val) {
			if(strpos($key, "data-") === 0
			&& $val != ""
			)
				$output .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
		}
		
		$output .= '>' . PHP_EOL . $content;
		
		$output .= '</div>' . PHP_EOL;
		
		return $output;
	
	}



	/**
	 * Element-Subtype zurückgeben
	 * 
	 * @access	public
     * @return  string
	 */
	public function getContentSubType()
	{
	
		return $this->conSubType;

	}



	/**
	 * Magic method get
	 * 
	 * @access	public
     * @return  string
	 */
	public function __get($name) {
	
		if(isset($name))
			return $this->$name;
		return false;
	
    }

}
