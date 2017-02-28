<?php
namespace Concise;



/**
 * GallerymenuElement
 * 
 */

class GallerymenuElement extends ElementFactory implements Elements
{

	private $linkCon	= array();
	private $linkAlias	= array();
	private $linkNames	= array();
	
	/**
	 * Gibt ein GallerymenuElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles, Wrap)
	 */
	public function __construct($options, $DB, &$o_lng, &$o_page)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->o_page			= $o_page;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
		$this->conTable			= $options["conTable"];

	}
	

	/**
	 * Element erstellen
	 * 
	 * @access	public
     * @return  string
	 */
	public function getElement()
	{

		##############################
		#######  Galerie-Menu  #######
		##############################
					
		$this->linkCon	= (array)json_decode($this->conValue);
		$itemList		= "";
		$output 		= "";
		

		if(!isset($this->linkCon["h"]))
			$this->linkCon["h"] = "";
		if(!isset($this->linkCon["usetags"]))
			$this->linkCon["usetags"] = 0;
		if(!isset($this->linkCon["tags"]))
			$this->linkCon["tags"] = "";
		if(!isset($this->linkCon["link"]))
			$this->linkCon["link"] = "";
		if(!isset($this->linkCon["name"]))
			$this->linkCon["name"] = "";
		if(!isset($this->linkCon["format"]))
			$this->linkCon["format"] = "";
		if(!isset($this->linkCon["style"]))
			$this->linkCon["style"] = "";

		
		if($this->linkCon["usetags"]) {
			$this->getGallmenuItemsByTag();
		}
		else {
			// Link-Aliase
			$this->linkAlias		= array_filter(preg_replace("/\r/", "", explode("\n", $this->linkCon["link"])));
			// Link-Namen
			$this->linkNames		= array_filter(preg_replace("/\r/", "", explode("\n", $this->linkCon["name"])));
		}
		
		$wrapperClass	= "";
		$listClass		= "";
		$itemClass		= "cc-menuitem";		
		
		if($this->linkCon["format"] == "pills") {
			$listClass		= "{t_class:pills} {t_class:navstacked}" . ($this->linkCon["style"] != "" ? ' {t_class:pfxbg}{t_class:sfx' . $this->linkCon["style"] . '}' : '');
		}
		if($this->linkCon["format"] == "panel") {
			$wrapperClass	= "{t_class:panel}" . ($this->linkCon["style"] != "" ? ' {t_class:panel' . $this->linkCon["style"] . '}' : '');
			$listClass		= "{t_class:listgroup}";
			$itemClass	   .= " {t_class:listitem}";
		}
		
		
		// Galerienamen verschlüsseln
		require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.myCrypt.php"); // Klasse myCrypt einbinden
		
		// myCrypt Instanz
		$crypt = new myCrypt();
		
		$l = 0;
		
		foreach($this->linkAlias as $item) {
			
			// Encrypt Name
			$enc		= $crypt->encrypt($item);
			$classExt	= "";
			
			if(!empty($GLOBALS['_GET']['gall'])
			&& $GLOBALS['_GET']['gall'] == $enc)
				$classExt = " active";
			
			$itemList	.= '<li class="' . $itemClass . $classExt . '" role="presentation"><a href="' . self::$currentURL . '?gall=' . $enc . '">' . htmlspecialchars($this->linkNames[$l]) . '</a></li>' . "\r\n";
			
			$l++;
		}
		
		// Überschrift
		if($this->linkCon["h"] != "") {
		
			$output	=	'<h2>' . htmlspecialchars($this->linkCon["h"]) . '</h2>' . "\r\n";
			
			if($this->linkCon["format"] == "panel")
				$output	=	'<div class="{t_class:panelhead}">' . $output . '</div>' . "\r\n";
		}
		
		$output .= '<ul class="' . $listClass . '">' . $itemList . '</ul>' . "\r\n";
		
		// Ggf. Wrapper
		if($wrapperClass != "")
			$output = '<div class="' . $wrapperClass . '">' . $output . '</div>' . "\r\n";
		
		// Falls HTML5
		if($this->html5)
			$output = 	'<nav>' . "\r\n" .
						$output .
						'</nav>' . "\r\n";
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		$output	= $this->getContentElementWrapper($this->conAttributes, $output, 'galleryMenu linkList');
												
		return $output;
	
	}	
	

	/**
	 * getGallmenuItemsByTag
	 * 
	 * @access	public
     * @return  string
	 */
	public function getGallmenuItemsByTag()
	{
	
		$queryExt	= "";		
		$tags		= explode(",", $this->linkCon["tags"]);
		
		foreach($tags as $tag) {
			$queryExt .= " OR FIND_IN_SET('" . $this->DB->escapeString($tag) . "', `tags`)";
		}

		// Datenbanksuche zum Auslesen von Galeriebildern und Bildtexten
		$queryGall = $this->DB->query( "SELECT `gallery_name` 
										FROM `" . DB_TABLE_PREFIX . "galleries` 
										WHERE (`group` = 'public'
											OR `group` = '" . $this->group . "')
										AND (0 " . $queryExt . ") 
										ORDER BY `gallery_name`
										", false);
		
		#var_dump($queryGall);
		
		
		if(is_array($queryGall)
		&& count($queryGall) > 0
		) {
		
			foreach($queryGall as $gall) {
				// Link-Aliase
				$this->linkAlias[]		= $gall['gallery_name'];
				// Link-Namen
				$this->linkNames[]		= $gall['gallery_name'];
			}
		}
	
	}
	
}
