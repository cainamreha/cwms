<?php
namespace Concise;


##############################
#######  Flashinhalt  ########
##############################


/**
 * FlashConfigElement class
 * 
 * content type => flash
 */
class FlashConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $folder			= CC_VIDEO_FOLDER;
	private $overwrite		= false;				
	private $useFilesFolder	= USE_FILES_FOLDER;
	private $filesFolder	= "";
	private $folderStr		= "";

	/**
	 * Gibt ein FlashConfigElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles)
	 * @param	string	$DB			DB-Objekt
	 * @param	string	$o_lng		Sprach-Objekt
	 */
	public function __construct($options, $DB, &$o_lng)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;

		$this->conType			= $options["conType"];
		$this->conValue			= $options["conValue"];
		$this->conAttributes	= $options["conAttributes"];
		$this->conNum			= $options["conNum"];
	
		$this->folder		   .= '/';
		
	}

	
	public function getConfigElement($a_POST)
	{

		$this->a_POST	= $a_POST;
		$this->params	= explode("<>", $this->conValue);

		
		// Ggf. POST auslesen
		if($this->a_POST != null)
			$this->evalElementPost();
		
		
		// DB-Updatestr generieren
		$this->makeUpdateStr();
		
		
		// Parameter (default) setzen
		$this->setParams();

		
		// Element-Formular generieren
		$this->output		= $this->getCreateElementHtml();
		
		
		// Ausgabe-Array erstellen und zurückgeben
		return $this->makeOutputArray();
	
	}
	
	
	// evalElementPost
	public function evalElementPost()
	{
	
		if(isset($GLOBALS['_FILES'][$this->conPrefix]) && $GLOBALS['_FILES'][$this->conPrefix]['name'] != "") { // Falls eine neue Datei hochgeladen werden soll

			$upload_file				= $GLOBALS['_FILES'][$this->conPrefix]['name'];
			$upload_tmpfile				= $GLOBALS['_FILES'][$this->conPrefix]['tmp_name'];
			$flashName					= $GLOBALS['_FILES'][$this->conPrefix]['name'];
			$fileFolder					= "";
			
			// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
			if(isset($this->a_POST['overwrite_' . $this->conPrefix]) && $this->a_POST['overwrite_' . $this->conPrefix] == "on")
				$this->overwrite		= true;

			// Falls die Datei unterhalb des files-Verzeichnisses gespeichert werden soll
			if(isset($this->a_POST['files_' . $this->conPrefix]) && $this->a_POST['files_' . $this->conPrefix] == "on" && 
			  (isset($this->a_POST['filesFolder_' . $this->conPrefix]) && $this->a_POST['filesFolder_' . $this->conPrefix] != "")) {
				$this->useFilesFolder	= true;
				$fileFolder				= 'media/files/';
				$this->filesFolder 	 	= $this->a_POST['filesFolder_' . $this->conPrefix];
				$fileFolder 	 	   .= $this->filesFolder;
				$this->folderStr		= $this->filesFolder . '/';
			}
			else
				$this->useFilesFolder	= false;

			$upload = Files::uploadFile($upload_file, $upload_tmpfile, $fileFolder, "movie", 0, 0, $this->overwrite, ""); // File-Upload
			#die($inputName );
			
			if($upload === true) {
				
				$this->params[0]		= $this->folderStr . Files::getValidFileName($flashName);
				
			}
			else {
				$this->wrongInput[]		= $this->conPrefix;
				$this->error			= $upload;
			}
		}

		elseif(isset($this->a_POST[$this->conPrefix . '_existFlash']) && $this->a_POST[$this->conPrefix . '_existFlash'] != "") { // Falls eine vorhandene Datei übernommen werden soll

			$flashName = $this->a_POST[$this->conPrefix . '_existFlash'];
			$this->params[0] = $flashName;
		}

		if(isset($this->a_POST[$this->conPrefix . '_alt'])) { 

			$this->params[1] = $this->a_POST[$this->conPrefix . '_alt'];
			$this->params[2] = $this->a_POST[$this->conPrefix . '_width'];
			$this->params[3] = $this->a_POST[$this->conPrefix . '_height'];
			
			if(isset($this->a_POST[$this->conPrefix . '_menu']) && $this->a_POST[$this->conPrefix . '_menu'] == "on")
				$this->params[4] = "true";
			else
				$this->params[4] = "false";
			
			if(isset($this->a_POST[$this->conPrefix . '_play']) && $this->a_POST[$this->conPrefix . '_play'] == "on")
				$this->params[5] = "true";
			else
				$this->params[5] = "false";
			
			if(isset($this->a_POST[$this->conPrefix . '_loop']) && $this->a_POST[$this->conPrefix . '_loop'] == "on")
				$this->params[6] = "true";
			else
				$this->params[6] = "false";
			
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$flashConValue = implode("<>", $this->params);
		$this->dbUpdateStr = "'" . $this->DB->escapeString($flashConValue) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if($this->params[0] == "")
			$this->params[0]	= false;
		if(strpos($this->params[0], "/") !== false)
			$this->folder		= CC_FILES_FOLDER . "/";
		if(!isset($this->params[1]))
			$this->params[1]	= "";
		if(!isset($this->params[2]))
			$this->params[2]	= "";
		if(!isset($this->params[3]))
			$this->params[3]	= "";
		if(!isset($this->params[4]))
			$this->params[4]	= "false";
		if(!isset($this->params[5]))
			$this->params[5]	= "false";
		if(!isset($this->params[6]))
			$this->params[6]	= "false";
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;

				
		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . '</span>' . PHP_EOL;
				
		$output .=	'<fieldset>' . PHP_EOL;
		
		$output	.=	'<div class="fileSelBox clearfix">' . PHP_EOL;

		// Dateiupload-Box
		$output	.=	'<div class="fileUploadBox rightBox">' . PHP_EOL .
					'<label class="uploadBoxLabel">{s_formfields:file}</label>' . PHP_EOL .
					$this->getUploadMask($this->conPrefix, $this->overwrite) .
					$this->getFilesUploadMask($this->filesFolder, $this->useFilesFolder, $this->conPrefix) .
					'</div>' . PHP_EOL;

		$output	.=	'<div class="existingFileBox leftBox">' . PHP_EOL .
					'<label class="elementsFileName">' . (!$this->params[0] ? "{s_label:choosefile}" : $this->params[0]) . '</label>' . PHP_EOL .
					'<div class="previewBox ' . $this->conType . '">' . PHP_EOL;
						

		if($this->params[0] !== false)
			$output	.=	'<object title="' . $this->params[0] . '" class="preview" classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" width="80" height="60" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0">' . PHP_EOL . 
						'<param name="movie" value="' . PROJECT_HTTP_ROOT . '/' . $this->folder . $this->params[0] . '" />' . PHP_EOL . 
						'<param name="quality" value="low" />' . PHP_EOL . 
						'<param name="scale" value="exactfit" />' . PHP_EOL . 
						'<param name="menu" value="false" />' . PHP_EOL . 
						'<param name="PLAY" value="false" />' . PHP_EOL . 
						'<param name="allowscriptaccess" value="always" />' . PHP_EOL .
						'<param name="allowfullscreen" value="true" />' . PHP_EOL .
						'<embed src="' . PROJECT_HTTP_ROOT . '/' . $this->folder . $this->params[0] . '" quality="low" scale="exactfit" menu="false"' .
						'type="application/x-shockwave-flash" width="80" height="60" pluginspage="http://www.adobe.com/go/getflashplayer">' . PHP_EOL . 
						'</embed>' . PHP_EOL . 
						'</object>' . PHP_EOL;
		else
			$output	.=	'<img src="' . SYSTEM_IMAGE_DIR . '/novideo.png" alt="noflash" />' . PHP_EOL;

		$output	.=	'</div>' . PHP_EOL;

		// Flash MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "video",
											"type"		=> "video",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=video",
											"path"		=> PROJECT_HTTP_ROOT . '/' . CC_VIDEO_FOLDER . '/',
											"value"		=> "{s_button:videofolder}",
											"icon"		=> "video"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		$output .=	'<input type="text" name="' . $this->conPrefix . '_existFlash" class="existingFile" value="' . htmlspecialchars($this->params[0]) . '" readonly="readonly" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL .
					'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset>' . PHP_EOL .
					'<label>noflash-Text<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_alt" value="' . htmlspecialchars($this->params[1]) . '" maxlength="512" />' . PHP_EOL .
					'<div class="fieldBox singleCol right" style="margin-top:25px;">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_menu">{s_label:playermenu}' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_menu" id="' . $this->conPrefix . '_menu"' . ($this->params[4] == "true" ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_play">autoplay' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_play" id="' . $this->conPrefix . '_play"' . ($this->params[5] == "true" ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_loop">{s_label:loop}' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_loop" id="' . $this->conPrefix . '_loop"' . ($this->params[6] == "true" ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:width}</label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_width" value="' . htmlspecialchars($this->params[2]) . '" maxlength="5" />' . PHP_EOL .
					'<label>{s_label:height}</label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_height" value="' . htmlspecialchars($this->params[3]) . '" maxlength="5" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
