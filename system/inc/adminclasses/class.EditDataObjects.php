<?php
namespace Concise;


/**
 * Klasse für ModulesDataObjects
 *
 */

class EditDataObjects extends Admin
{
	

    /**
     * Vars
     *
     * @access public
     * @var    string
     */
	public $objectCon				= array();
	public $busyObject				= false;
	protected $isPlugin				= false;
	protected $contentElementKind	= "";
	public $textAreaCount			= 0;
	public $wrongInput				= array();
	
	
	/**
	 * Konstruktor
	 * 
	 * @param	$dataObject	Daten-Objekt Parameter
	 * @param	$o			Zähler für Objekt-Nr
	 * @access	protected
	 * @return	array
	 */
	public function __construct($DB, $o_lng)
	{
	
		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
	
	}	

	
	/**
	 * Methode zum auflisten von Moduldaten-Objekten
	 * 
	 * @param	$dataObject	Daten-Objekt Parameter
	 * @param	$o			Zähler für Objekt-Nr
	 * @access	protected
	 * @return	array
	 */
	public function getObject($dataObject, $o)
	{
	
		// Objekt
		$this->objectCon[$o]	= $dataObject;
		
		$output			= "";
		$objectArr		= array();
		$oType 			= $dataObject["type"];
		$oParams		= $dataObject[$this->editLang];
		$isImage		= false;
		$isGall			= false;
		$isDoc			= false;
		$isAudio		= false;
		$isVideo		= false;
		$isHtml			= false;
		$isObject		= true;
		
		// Objekttyp ermitteln
		switch($oType) {
			
			case "img":
				$isImage	= true;
				break;
				
			case "gallery":
				$isGall		= true;
				break;
				
			case "doc":
				$isDoc		= true;
				break;
				
			case "audio":
				$isAudio	= true;
				break;
				
			case "video":
				$isVideo	= true;
				break;
				
			case "html":
				$isHtml		= true;
				break;
				
			default:
				$isObject	= false;
		}
	
	
		// Falls Objektinhalte
		$this->busyObject	= $isObject ? $oType : false;
		
		// Objekt-Typen
		$objectArr["img"]		= $this->getConfigElement("img", ($isImage ? $oParams : ''), ($isImage ? $GLOBALS['_POST'] : null), $o);
		$objectArr["gallery"]	= $this->getConfigElement("gallery", ($isGall ? $oParams : ''), ($isGall ? $GLOBALS['_POST'] : null), $o);
		$objectArr["doc"]		= $this->getConfigElement("doc", ($isDoc ? $oParams : ''), ($isDoc ? $GLOBALS['_POST'] : null), $o);
		$objectArr["audio"]		= $this->getConfigElement("audio", ($isAudio ? $oParams : ''), ($isAudio ? $GLOBALS['_POST'] : null), $o);
		$objectArr["video"]		= $this->getConfigElement("video", ($isVideo ? $oParams : ''), ($isVideo ? $GLOBALS['_POST'] : null), $o);
		$objectArr["html"]		= $this->getConfigElement("html", ($isHtml ? $oParams : ''), ($isHtml ? $GLOBALS['_POST'] : null), $o);
		
		
		// Falls aktives Objekt, Daten für db übernehmen
		if($this->busyObject) {

			$this->objectCon[$o]["type"]			= $oType;
			$this->objectCon[$o][$this->editLang]	= $this->objectUpdateStrings[$o][$oType];
			
			$this->setLangsObjParams($this->objectUpdateStrings[$o][$oType], $o);
			$this->objUpdateStr						= json_encode($this->objectCon[$o]);
		}
		else {
			$this->objectCon[$o]["type"]			= "";
			$this->objectCon[$o][$this->editLang]	= "";
			$this->objUpdateStr						= "";
		}
		$this->objUpdateStr							= $this->DB->escapeString($this->objUpdateStr);
		
		
		$output .=	'<li id="object-'.$o.'" class="dataObject listItem" data-sortid="'.$o.'" data-sortidold="'.$o.'">' . "\r\n" .
					'<span class="type objectToggle' . ($oType != "" ? ' ' . $oType . ' busy' : '') . '">{s_label:object} - '.$o.'</span>' . "\r\n" .
					'<div class="objects"' . ($oType == "" ? ' style="display:none;"' : '') . '>' . "\r\n";


		// Bildobjekt
		$output .=	'<div class="attach imgObject"' . ($isObject && !$isImage ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_img['.$o.']" id="add_img'.$o.'" class="toggleObjectType"' . ($isImage ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_img'.$o.'" class="dataObjectLabel inline-label">{s_label:image}</label>' . "\r\n" .
					'<div class="dataObjectBox" style="' . (!$isImage ? 'display:none;' : '') . '">' . "\r\n";
		
		$output .=	$objectArr["img"];
	
		$output .=	'</div>' . "\r\n" .
					'</div>' . "\r\n";
					
		
		// Gallerieobjekt
		$output .=	'<div class="attach galleryObject"' . ($isObject && !$isGall ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_gall['.$o.']" id="add_gall'.$o.'" class="toggleObjectType"' . ($isGall ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_gall'.$o.'" class="dataObjectLabel inline-label">{s_label:gallery}</label>' . "\r\n" .
					'<div class="dataObjectBox" style="' . (!$isGall ? ' display:none;' : '') . '">' . "\r\n";
		
		$output .=	$objectArr["gallery"];
	
		$output .=	'</div>' . "\r\n" .
					'</div>' . "\r\n";
		

		// Dokumentobjekt
		$output .=	'<div class="attach docObject"' . ($isObject && !$isDoc ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_doc['.$o.']" id="add_doc'.$o.'" class="toggleObjectType"' . ($isDoc ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_doc'.$o.'" class="dataObjectLabel inline-label">{s_label:doc}</label>' . "\r\n" .
					'<div class="dataObjectBox" style="' . (!$isDoc ? 'display:none;' : '') . '">' . "\r\n";
		
		$output .=	$objectArr["doc"];
	
		$output .=	'</div>' . "\r\n" .
					'</div>' . "\r\n";
					
			
		// Audio-Objekt
		$output .=	'<div class="attach audioObject"' . ($isObject && !$isAudio ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_audio['.$o.']" id="add_audio'.$o.'" class="toggleObjectType"' . ($isAudio ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_audio'.$o.'" class="dataObjectLabel inline-label">{s_label:audio}</label>' . "\r\n" .
					'<div class="dataObjectBox" style="' . (!$isAudio ? 'display:none;' : '') . '">' . "\r\n";
		
		$output .=	$objectArr["audio"];
	
		$output .=	'</div>' . "\r\n" .
					'</div>' . "\r\n";
		
		
		// Video-Objekt
		$output .=	'<div class="attach videoObject"' . ($isObject && !$isVideo ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_video['.$o.']" id="add_video'.$o.'" class="toggleObjectType"' . ($isVideo ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_video'.$o.'" class="dataObjectLabel inline-label">{s_label:video}</label>' . "\r\n" .
					'<div class="dataObjectBox" style="' . (!$isVideo ? 'display:none;' : '') . '">' . "\r\n";
					
		$output .=	$objectArr["video"];
	
		$output .=	'</div>' . "\r\n" .
					'</div>' . "\r\n";

		
		// HTMLobjekt
		$output .=	'<div class="attach htmlObject"' . ($isObject && !$isHtml ? ' style="display:none;"' : '') . '>' . "\r\n" .	
					'<label class="markBox">' . "\r\n" .
					'<input type="checkbox" name="add_html['.$o.']" id="add_html'.$o.'" class="toggleObjectType"' . ($isHtml ? ' checked="checked"' : '') . ' />' . "\r\n" .
					'</label>' . "\r\n" .
					'<label for="add_html'.$o.'" class="dataObjectLabel inline-label">{s_label:html}</label>' . "\r\n" .
					'<div class="htmlBox" style="' . (!$isHtml ? 'display:none;' : '') . '">' . "\r\n";
		
		$output .=	$objectArr["html"];
		
		$output .=	'</div>' . "\r\n" .	
					'</div>' . "\r\n";
		
		$output .=	'</div>' . "\r\n" .	
					'</li>' . "\r\n";
		
		return $output;
	
	}

	
	// Element-Konfiguration Html auslesen
	public function getConfigElement($oType, $oCon, $a_POST, $o)
	{
	
		$output		= "";
		$suffix		= $oType . "-" . $o;
		
		// Falls Extension/Plug-in
		if($this->isPlugin) {
			$this->contentElementKind	= "plugin";
			$pluginDir	= PLUGIN_DIR . $oType . '/';
			$this->setPlugin($oType, $this->editLang); // Sprachbausteine des Plug-ins laden
		}
		else
			$this->contentElementKind	= "core";
		
		require_once(SYSTEM_DOC_ROOT . '/inc/elements/class.ConfigElementFactory.php');
		

		// Element-Options
		$options	= array(	"conType"		=> $oType,
								"conValue"		=> $oCon,
								"conAttributes"	=> "",
								"conNum"		=> $o,
								"conCount"		=> $o,
								"textAreaCount"	=> $this->textAreaCount
							);
		
		// Elementinhalt
		try {				
			// Inhaltselement-Instanz
			$o_element	= ConfigElementFactory::create($oType, $options, $this->contentElementKind, $this->DB, $this->o_lng);
		}
			
		// Falls ConfigElement-Klasse nicht vorhanden
		catch(\Exception $e) {
			$output				   .= $this->backendLog ? $e->getMessage() : "";
			return $output;
		}
		
		// Element-Objekt Eigenschaften
		$o_element->conPrefix		= "dataObj-" . $suffix;
		$o_element->editId			= $this->editId;
		$o_element->editLang		= $this->editLang;
		$o_element->editLangFlag	= $this->editLangFlag;
		$o_element->userGroups		= $this->userGroups;

		
		// Inhaltselement generieren
		$elementConfigArr			= $o_element->getConfigElement($a_POST);

		
		// ConfigElement-Rückgabe zuweisen
		// update return str
		$updateStr					= $elementConfigArr['update'];
		$output						= $elementConfigArr['output'];
		$this->textAreaCount	   += $elementConfigArr['textareas'];

		$updateStr					= rtrim($updateStr, ",");
		$updateStr					= trim($updateStr, "'");
		
		$this->objectUpdateStrings[$o][$oType]	= $updateStr;

		
		if(!empty($elementConfigArr['error']))					$this->wrongInput["obj-" . $o]	= $elementConfigArr['error'];

		
		// Head code zusammenführen
		$this->mergeHeadCodeArrays($o_element);
		
		return $output;
	
	}


	// setLangsObjParams
	public function setLangsObjParams($objP, $o)
	{

		// Änderungen für alle Sprachen übernehmen
		if(isset($GLOBALS['_POST']['all_langs']) && $GLOBALS['_POST']['all_langs'] == "on") { // Falls Objekt für alle Sprachen übernommen werden soll
	
			foreach($this->o_lng->installedLangs as $addLang) {
				if($addLang != $this->editLang) {
					$this->objectCon[$o][$addLang]	= $objP;
				}
									
			}
			
		}
	}
	
} // Ende Klasse
