<?php
namespace Concise;


/**
 * Klasse FileUploader
 *
 */

class FileUploader extends FileUploaderFactory implements FileUploaderInterface
{
	
	private static $uploadMethod	= "default";
	private $allowedFiles			= array();
	private $allowedFileSizeStr		= "";
	private $forcedFileCat			= "";
	private $overwrite				= false;
	private $scaleImg				= false;
	private $imgWidth				= 0;
	private $imgHeight				= 0;
	private $folder					= "";
	private $useFilesFolder			= false;
	
	/**
	 * Gibt ein FormdataConfigElement zurück
	 * 
	 * @access	public
	 * @param	string	$options	Parameter-Array (Wert, Styles)
	 * @param	string	$DB			DB-Objekt
	 * @param	string	$o_lng		Sprach-Objekt
	 */
	public function __construct($options, $DB, &$o_lng)
	{
	
		$this->DB					= $DB;
		$this->o_lng				= $o_lng;

		$this->allowedFiles			= isset($options["allowedFiles"])		? $options["allowedFiles"] : array();
		$this->allowedFileSizeStr	= isset($options["allowedFileSizeStr"])	? $options["allowedFileSizeStr"] : "";
		$this->forcedFileCat		= isset($options["forcedFileCat"])		? $options["forcedFileCat"] : "";
		$this->overwrite			= isset($options["overwrite"])			? $options["overwrite"] : false;
		$this->scaleImg				= isset($options["scaleImg"])			? $options["scaleImg"] : false;
		$this->imgWidth				= isset($options["imgWidth"])			? $options["imgWidth"] : 0;
		$this->imgHeight			= isset($options["imgHeight"])			? $options["imgHeight"] : 0;
		$this->folder				= isset($options["folder"])				? $options["folder"] : "";
		$this->useFilesFolder		= isset($options["useFilesFolder"])		? $options["useFilesFolder"] : false;
	
	}


	/**
	 * Gibt Upload-Methode zurück
	 *
	 * @access	public
     * @return  string
	 */
	public function getUploadMethod()
	{
		
		return self::$uploadMethod;
	
	}


	/**
	 * Weist Upload Head Files zu
	 *
     * @param	string	$type	Variante des Upload-Sripts (default = '')
	 * @access	public
     * @return  string
	 */
	public function assignHeadFiles($type = "")
	{
		
		return true;
	
	}


	/**
	 * Gibt Upload-Script zurück
	 *
     * @param	string	$targetElem	Zielelement (dom)
     * @param	string	$type		Medien-Typ (default = 'default')
     * @param	string	$noCache	falls true, hängt timestamp an src Attribut (default = true)
	 * @access	public
     * @return  string
	 */
	public function getUploadScript($targetElem, $type = "default", $noCache = true)
	{
	
		$uploadScript	=	"";
		
		return $uploadScript;
	
	}
	
	
	/**
	 * Gibt eine Upload Maske eines bestimmten Typs zurück
	 *
     * @param	string	$uploadKind	Upload-Typ
     * @param	string	$type		Mediengruppen-Typ
     * @param	string	$index		Index
	 * @access	public
     * @return  string
	 */
	public function getUploaderMask($uploadKind, $type = "", $index = "")
	{
		
		switch($uploadKind) {
			
			case "file":
			return	'<div class="leftBox">' . "\r\n" . 
					'<input type="file" id="upload" name="upload[]" multiple="true" maxlength="20" accept="' . implode("|", $this->allowedFiles) . '" />' . "\r\n" . 
					'</div>' . "\r\n" . 
					'<input type="hidden" name="selFiles" id="selFiles" />' . "\r\n" . 
					'<button type="submit" id="fileupload" class="cc-button button button-upload right" value="{s_button:upload}">' . "\r\n" .
					ContentsEngine::getIcon("upload") .
					'{s_button:upload}' .
					'</button>' . "\r\n" . 
					'<p class="clearfloat">&nbsp;</p>' . "\r\n" .
					'<span class="inline-box">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="overwrite" id="overwrite" class="overwrite"' . ($this->overwrite === true ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="overwrite" class="inline-label">{s_label:overwrite}</label>' . "\r\n" . 
					'</span>' . "\r\n" .
					'<span class="inline-box">' . "\r\n" .
					'<div class="scaleImgBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg"' . ($this->scaleImg === true ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
					'<label for="scaleimg" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
					'<div class="scaleImgDiv" id="scaleImgDiv" style="' . ($this->scaleImg === false ? ' display:none;' : '') . '">' . "\r\n" .
					'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . ($this->imgWidth == 0 ? IMG_WIDTH : $this->imgWidth) . '" />' . "\r\n" . 
					'<span class="imgSize"> x </span>' . "\r\n" . 
					'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . ($this->imgHeight == 0 ? IMG_HEIGHT : $this->imgHeight) . '" />' . "\r\n" . 
					'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize2}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" . 
					'<br class="clearfloat" /></div></div>' . "\r\n" .
					'</span>' . "\r\n" .
					$this->getFilesUploadMask($this->folder, $this->useFilesFolder) .
					parent::getTokenInput();
			break;
			
			case "gallery":
			return	'<div class="leftBox">' . "\r\n" . 
					'<input type="file" multiple="true" id="upload" name="upload[]" accept="' . implode("|", $this->allowedFiles) . '" maxlength="20" />' . "\r\n" .
					'</div>' . "\r\n" . 
					'<button type="submit" id="uploadGallFiles" class="cc-button button button-upload right" value="{s_button:upload}">' .
					ContentsEngine::getIcon("upload") .
					'{s_button:upload}' .
					'</button>' . "\r\n" . 
					'<input type="hidden" value="{s_button:upload}" />' . "\r\n" .
					'<p class="clearfloat">&nbsp;</p>' . "\r\n" .
					'<p>&nbsp;</p>' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="overwrite" id="overwrite" class="overwrite"' . ($this->overwrite === true ? ' checked="checked"' : '') . ' /></label>' . "\r\n" .
					'<label for="overwrite" class="inline-label">{s_label:overwrite}</label>' . "\r\n" . 
					'<div class="scaleImgBox">' . "\r\n" .
					'<label class="markBox"><input type="checkbox" name="scaleimg" class="scaleimg" id="scaleimg"' . ($this->scaleImg === true ? ' checked="checked"' : '') . '/></label>' . "\r\n" . 
					'<label for="scaleimg" class="inline-label">{s_label:scaleimg}</label>' . "\r\n" . 
					'<div class="scaleImgDiv" id="scaleImgDiv" style="' . ($this->scaleImg === false ? ' display:none;' : '') . '">' . "\r\n" .
					'<input type="text" name="imgWidth" id="imgWidth" class="imgWidth" value="' . ($this->imgWidth == 0 ? IMG_WIDTH : $this->imgWidth) . '" />' . "\r\n" . 
					'<span class="imgSize"> x </span>' . "\r\n" .
					'<input type="text" name="imgHeight" id="imgHeight" class="imgHeight" value="' . ($this->imgHeight == 0 ? IMG_HEIGHT : $this->imgHeight) . '" />' . "\r\n" . 
					'<label class="imgSizeLabel inline-label">' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize3}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . "\r\n" . 
					parent::getTokenInput() .
					'<br class="clearfloat" /></div></div>' . "\r\n";
			break;
		}

	}

	
	/**
	 * File upload von Dateien im FILES-Array
	 *
     * @param	array	$a_Files	_FILES-Array
     * @param	array	$selFiles	Array mit ausgewählten Dateien
	 * @access	public
     * @return  array
	 */
	public function uploadFiles($a_Files, $selFiles)
	{
	
		$success		= true;
		$error			= array();
		$newFiles		= array();
		$replacedFiles	= array();
		
		// Falls leere File Arrays
		if(!is_array($a_Files) 
		|| count($a_Files) == 0
		|| !is_array($selFiles) 
		|| count($selFiles) == 0
		) {
			$error[] = "<li>{s_javascript:nofilessel}</li>"; // Meldung in Array speichern
			$success = false;
			return array(	"success" 	=> $success,
							"error"		=> $error
						);
		}
		
		// Files-Array auslesen und Dateien hochladen
		foreach($a_Files["name"] as $key => $upload_file) {
			
			if(in_array($upload_file, $selFiles)) {
	
				$upload_file	= Files::getValidFileName($upload_file);
				$fileType		= $this->forcedFileCat == "" ? Files::getFileCatByMimeType($a_Files['type'][$key]) : $this->forcedFileCat;
				$fileExt		= strtolower(Files::getFileExt($upload_file));
				
				// Falls Dateityp nicht erlaubt
				if(!in_array($fileExt, $this->allowedFiles)) {
					$error[] = '<li class="listItem"><strong>' . $upload_file . '</strong><br />{s_error:wrongtype1} - '.$a_Files['type'][$key].'</li>';
					$success = false;
					continue;
				}
					
				// Falls der Typ nicht erkannt wird, handelt es sich evtl. um ein audio-File o.ä.
				if($fileType == "unknown"
				&& $a_Files['type'][$key] == 'application/octect-stream'
				) {
					
					$fileType	= Files::getFileType($upload_file);
				}

				// Falls Typ eingeordnet werden kann
				if($fileType == "other") {
					// Falls Upload fehlerhaft, Meldung in Array speichern
					$error[] = '<li class="listItem"><strong>' . $upload_file . '</strong><br />{s_error:wrongtype1} - '.$a_Files['type'][$key].'</li>';
					$success = false;
					continue;
				}
				
				$checkFileExist	= file_exists(PROJECT_DOC_ROOT . '/' . $this->folder . '/' . $upload_file);
				
				// Datei-Upload starten
				$upload_tmpfile = $a_Files['tmp_name'][$key];
				
				$upload		= Files::uploadFile($upload_file, $upload_tmpfile, $this->folder, $fileType, $this->imgWidth, $this->imgHeight, $this->overwrite, "");
				
				if($upload !== true)	{
					// Falls Upload fehlerhaft, Meldung in Array speichern
					$error[] = '<li class="listItem"><strong>' . $upload_file . '</strong><br />' . $upload . '</li>';
					$success = false;
				}
				else {
					
					// Falls die Datei noch nicht existiert bzw. nicht ersetzt werden soll (verhindert doppelten Eintrag in db)
					if($this->overwrite !== true 
					|| $checkFileExist !== true) {
						
						$newFiles[]			= $upload_file; // neu hochgeladene (noch nicht vorhandene) Dateien
					}
					else
						$replacedFiles[]	= $upload_file; // Array mit ersetzten Dateien
				}
			}
			
		} // Ende foreach

		return array(	"success" 		=> $success,
						"error"			=> $error,
						"newFiles" 		=> $newFiles,
						"replacedFiles"	=> $replacedFiles
					);

	}


	/**
	 * Getter
	 *
	 * @access	public
     * @return  string
	 */
	public function __get($property)
	{

		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}


	/**
	 * Setter
	 *
	 * @access	public
     * @return  string
	 */
	public function __set($property, $value)
	{
	
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
		return $this;
	}	

}
