<?php
namespace Concise;


##############################
#######  Audio-Inhalt  #######
##############################


/**
 * AudioConfigElement class
 * 
 * content type => audio
 */
class AudioConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $folder			= CC_AUDIO_FOLDER;
	private $overwrite		= false;				
	private $useFilesFolder	= USE_FILES_FOLDER;
	private $filesFolder	= "";
	private $folderStr		= "";

	/**
	 * Gibt ein AudioConfigElement zurück
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

			$upload_file	= $GLOBALS['_FILES'][$this->conPrefix]['name'];
			$upload_tmpfile = $GLOBALS['_FILES'][$this->conPrefix]['tmp_name'];
			$audioName		= $GLOBALS['_FILES'][$this->conPrefix]['name'];
			$fileFolder		= "";
			
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


			$upload = Files::uploadFile($upload_file, $upload_tmpfile, $fileFolder, "audio", 0, 0, $this->overwrite, ""); // File-Upload

			
			if($upload === true) {
				
				$this->params[0] = $this->folderStr . Files::getValidFileName($audioName);
				
			}
			else {
				$this->wrongInput[] = $this->conPrefix;
				$this->error = $upload;
			}
			#var_dump($GLOBALS['_FILES'][$this->conPrefix]);
		}

		elseif(isset($this->a_POST[$this->conPrefix . '_existaudio']) && $this->a_POST[$this->conPrefix . '_existaudio'] != "") { // Falls eine vorhandene Datei übernommen werden soll

			$audioName = $this->a_POST[$this->conPrefix . '_existaudio'];
			$this->params[0] = $audioName;
		}

		if(isset($this->a_POST[$this->conPrefix . '_alt']))
			$this->params[1] = $this->a_POST[$this->conPrefix . '_alt'];
			
		if(isset($this->a_POST[$this->conPrefix . '_width'])) {
			
			$this->params[2] = $this->a_POST[$this->conPrefix . '_width'];
			
			if(isset($this->a_POST[$this->conPrefix . '_ani']) && $this->a_POST[$this->conPrefix . '_ani'] == "on")
				$this->params[3] = "yes";
			else
				$this->params[3] = "no";
				
			if(isset($this->a_POST[$this->conPrefix . '_link']) && $this->a_POST[$this->conPrefix . '_link'] == "on")
				$this->params[4] = "yes";
			else
				$this->params[4] = "no";
		}	
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$this->dbUpdateStr = "'" . $this->DB->escapeString(implode("<>", $this->params)) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.AudioContent.php"; // AudioContent-Klasse einbinden


		if(!isset($this->params[1]))
			$this->params[1] = "";
		if(!isset($this->params[2]))
			$this->params[2] = "";
		if(!isset($this->params[3]))
			$this->params[3] = "no";
		if(!isset($this->params[4]))
			$this->params[4] = "no";
	
		if($this->params[2] == "" || !is_numeric($this->params[2]))
			$this->params[2] = DEF_PLAYER_WIDTH;			
			
		if($this->params[0] == "")
			$this->params[0] = "{s_label:choosefile}";
		if(strpos($this->params[0], "/") !== false)
			$this->folder			= CC_FILES_FOLDER . "/";
		if(!isset($this->params[1]))
			$this->params[1] = "";
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
				
		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.= '<span class="notice">' . $this->error . '</span>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;

		$output	.=	'<div class="fileSelBox clearfix">' . PHP_EOL;

		// Dateiupload-Box
		$output	.=	'<div class="fileUploadBox rightBox">' . PHP_EOL .
					'<label class="uploadBoxLabel">{s_formfields:file}</label>' . PHP_EOL .
					$this->getUploadMask($this->conPrefix, $this->overwrite) .
					$this->getFilesUploadMask($this->filesFolder, $this->useFilesFolder, $this->conPrefix) .
					'</div>' . PHP_EOL;

		$output	.= 	'<div class="existingFileBox leftBox">' . PHP_EOL .
					'<label class="elementsFileName">' . (!$this->params[0] ? "{s_label:choosefile}" : $this->params[0]) . '</label>' . PHP_EOL .
					'<div class="previewBox ' . $this->conType . '">' . PHP_EOL;


		$fileExt	= strtolower(pathinfo($this->params[0], PATHINFO_EXTENSION));
		
		// Falls Audio-File
		if($fileExt == "audio") {
			// Javascript-File/-Code für Audio-Player einlesen
			AudioContent::getAudioScript(false);
			AudioContent::getAudioScript();
			$output	.= AudioContent::getAudioObject($this->params[0], $this->params[1], "248", "no", "no", $this->conNum);
		}
		// Andernfalls HTML5 Audio
		else
			$output	.=	AudioContent::getHTML5Audio(PROJECT_HTTP_ROOT . '/' . $this->folder . $this->params[0]) . PHP_EOL;
		
		$output	.=	'</div>' . PHP_EOL;
		
		// Audio MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "audio",
											"type"		=> "audio",
											"url"		=> PROJECT_HTTP_ROOT . '/system/access/listMedia.php?page=admin&type=audio&i=' .$this->conNum,
											"path"		=> PROJECT_HTTP_ROOT . '/' . CC_AUDIO_FOLDER . '/',
											"value"		=> "{s_button:audiofolder}",
											"icon"		=> "audio"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);			
		
		$output .=	'<input type="text" name="' . $this->conPrefix . '_existaudio" class="existingFile" value="' . htmlspecialchars($this->params[0]) . '" readonly="readonly" />' . PHP_EOL;

		$output	.=	'</div>' . PHP_EOL;
		$output	.=	'</div>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_label:attributes}">' . PHP_EOL;
		
		$output .=	'<label>Audio-Text<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_alt" value="' . htmlspecialchars($this->params[1]) . '" class="altText" maxlength="512" />' . PHP_EOL .
					'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:playerwidth}</label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_width" value="' . htmlspecialchars($this->params[2]) . '" maxlength="5" class="inputLeft" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="rightBox">' . PHP_EOL .
					'<label>&nbsp;</label>' . PHP_EOL .
					'<div class="fieldBox">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_link">{s_label:download}' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_link" id="' . $this->conPrefix . '_link"' . ($this->params[4] == "yes" ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_ani">{s_label:playerani}' . PHP_EOL .
					'<input type="checkbox" name="' . $this->conPrefix . '_ani" id="' . $this->conPrefix . '_ani"' . ($this->params[3] == "yes" ? ' checked="checked"' : '') . ' /></label>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<br class="clearfloat" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
