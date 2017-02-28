<?php
namespace Concise;



/**
 * DocElement
 * 
 */

class DocElement extends ElementFactory implements Elements
{
	
	/**
	 * Gibt ein DocElement zurück
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
		#########  Dokument  #########
		##############################
	
		$docCon = explode("<>", $this->conValue);
		
		if(!isset($docCon[0])) // Doc file
			$docCon[0] = "";
		if(!isset($docCon[1])) // Text
			$docCon[1] = "";
		if(!isset($docCon[2])) // Title
			$docCon[2]	= "";
		if(!isset($docCon[3])) // Filesize
			$docCon[3]	= 1;
		if(!isset($docCon[4])) // Icon
			$docCon[4]	= 1;
		if(!isset($docCon[5])) // Styles
			$docCon[5]	= "";
		if(!isset($docCon[6])) // Style 2
			$docCon[6]	= "";
		if(!isset($docCon[7])) // Font icon
			$docCon[7]	= 0;
			
		$docName	= $docCon[0];
		$fileSize	= "";
		$fileIcon	= "";
		$docClass	= "link";
		
		$fileExt	= substr($docName, strrpos($docName,'.')+1, strlen($docName)-1);
				
		
		// Pfad zur Dokumentdatei
		// Falls files-Ordner, den Pfad ermitteln
		if(strpos($docCon[0], "/") !== false) {
			$filesDoc	= explode("/", $docCon[0]);
			$docName	= array_pop($filesDoc);					
			$basePath	= CC_FILES_FOLDER . "/";
			$docPath	= $basePath . implode("/", $filesDoc) . "/";
		}
		else {
			$basePath	= CC_DOC_FOLDER . "/";
			$docPath	= $basePath;
		}
		
		// Dokumentname ggf. verschlüsseln
		require_once(PROJECT_DOC_ROOT."/inc/classes/Media/class.FileOutput.php"); // Klasse FileOutput einbinden

		// Dokumentlinkname
		$docLink = FileOutput::getFileHash($docCon[0], "doc", $basePath, $this->currentPage);
		
		// Dokumentgröße
		if($docCon[3])
			$fileSize = Modules::getFileSizeString($docName, $docPath, true);
		
		// Icon
		if($docCon[4]) {
		
			// Font icon
			if($docCon[7])
				$fileIcon	= ContentsEngine::getIcon(FileOutput::getFileIconKey($fileExt));
			// Image icon
			else {
				$docIcon	= FileOutput::getFileIcon($fileExt, PROJECT_HTTP_ROOT . '/' . IMAGE_DIR);
				if(file_exists(PROJECT_DOC_ROOT . '/' . IMAGE_DIR . $docIcon))
					$iconSrc	= PROJECT_HTTP_ROOT . '/' . IMAGE_DIR . $docIcon;
				else
					$iconSrc	= SYSTEM_IMAGE_DIR . '/' . $docIcon;
				$fileIcon	= '<img src="' . $iconSrc . '" alt="' . $docIcon . '" />' . "\r\n";
			}
		}
		
		// Href
		$href	= PROJECT_HTTP_ROOT . '/' . $docLink;
		
		// Style
		if($docCon[5] != "") {
			$docClass	= '{t_class:btn} {t_class:' . $docCon[5] . '}';
			if($docCon[6] != "")
				$docClass	.= ' {t_class:' . $docCon[6] . '}';
		}
		
		$output = 	'<p>' . "\r\n" .
					'<a class="' . $docClass . '" href="' . $href . '"' . ($docCon[2] != "" ? ' title="' . $docCon[2] . '"' : '') . ' target="_blank">' .
					$fileIcon .
					($docCon[1] != "" ? $docCon[1] : $docCon[0]) .
					'</a>' .
					$fileSize .
					'</p>';
		
		// Ggf. Attribute (Styles) Wrapper-div hinzufügen
		if($this->conAttributes['id'] != "" || $this->conAttributes['class'] != "" || $this->conAttributes['style'] != "")
			$output	= $this->getContentElementWrapper($this->conAttributes, $output);

		return $output;
	
	}	
	
}
