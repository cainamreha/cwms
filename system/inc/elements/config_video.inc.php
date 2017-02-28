<?php
namespace Concise;


##############################
#######  Videoinhalt  ########
##############################

/**
 * VideoConfigElement class
 * 
 * content type => video
 */
class VideoConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $folder			= CC_VIDEO_FOLDER;
	private $overwrite		= false;				
	private $useFilesFolder	= USE_FILES_FOLDER;
	private $filesFolder	= "";
	private $docIcon		= "novideo.png";
	private $folderStr		= "";
	private $errorImg		= "";

	/**
	 * Gibt ein VideoConfigElement zurück
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

		if(strpos($this->conValue, "[") === 0)
			$this->conValue	= str_replace('\"', '"', $this->conValue);
		
		$this->params	= json_decode($this->conValue, true);

		
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
			$upload_tmpfile	= $GLOBALS['_FILES'][$this->conPrefix]['tmp_name'];
			$videoName		= $GLOBALS['_FILES'][$this->conPrefix]['name'];
			$fileFolder		= "";
			
			// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
			if(isset($this->a_POST['overwrite_' . $this->conPrefix]) && $this->a_POST['overwrite_' . $this->conPrefix] == "on")
				$this->overwrite = true;				

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

			$upload = Files::uploadFile($upload_file, $upload_tmpfile, $fileFolder, "video", 0, 0, $this->overwrite, ""); // File-Upload
			#die($inputName );
			
			if($upload === true) {
				
				$this->params[0] = $this->folderStr . Files::getValidFileName($videoName);
				
			}
			else {
				$this->wrongInput[] = $this->conPrefix;
				$this->error = $upload;
			}
			#var_dump($GLOBALS['_FILES'][$this->conPrefix]);
		}

		elseif(isset($this->a_POST[$this->conPrefix . '_existVideo']) && $this->a_POST[$this->conPrefix . '_existVideo'] != "") { // Falls eine vorhandene Datei übernommen werden soll

			$videoName = $this->a_POST[$this->conPrefix . '_existVideo'];
			$this->params[0] = $videoName;
		}

		if(isset($this->a_POST[$this->conPrefix . '_alt'])) { 

			$this->params[1]	= $this->a_POST[$this->conPrefix . '_alt'];
			$this->params[2] 	= isset($this->a_POST[$this->conPrefix . '_preload']) ? 1 : '';
			$this->params[3] 	= isset($this->a_POST[$this->conPrefix . '_controls']) ? 1 : '';
			$this->params[4] 	= isset($this->a_POST[$this->conPrefix . '_muted']) ? 1 : '';
			$this->params[5] 	= isset($this->a_POST[$this->conPrefix . '_autoplay']) ? 1 : '';
			$this->params[6] 	= isset($this->a_POST[$this->conPrefix . '_loop']) ? 1 : '';
			$this->params[7] 	= isset($this->a_POST[$this->conPrefix . '_border']) ? 1 : '';
			$this->params[8] 	= trim($this->a_POST[$this->conPrefix . '_width']);
			$this->params[9] 	= trim($this->a_POST[$this->conPrefix . '_height']);
			$this->params[10] 	= trim($this->a_POST[$this->conPrefix . '_poster']);
			$this->params[11] 	= !empty($this->a_POST[$this->conPrefix . '_modal']) ? 1 : 0;
			$this->params[12] 	= $this->a_POST[$this->conPrefix . '_modalbtn'];
			
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$videoConValue = json_encode($this->params, JSON_UNESCAPED_UNICODE);
		$this->dbUpdateStr = "'" . $this->DB->escapeString($videoConValue) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(empty($this->params[0]))
			$this->params[0] = "{s_label:choosefile}";
		else {
			$this->docIcon = "icon_video.png";
		}
				
		if(strpos($this->params[0], "/") !== false)
			$this->folder	= CC_FILES_FOLDER . "/";
		if(!isset($this->params[1]))
			$this->params[1] = ""; // title
		if(!isset($this->params[2]))
			$this->params[2] = 1; // preload
		if(!isset($this->params[3]))
			$this->params[3] = 1; // controls
		if(!isset($this->params[4]))
			$this->params[4] = 1; // muted
		if(!isset($this->params[5]))
			$this->params[5] = 0; // autoplay
		if(!isset($this->params[6]))
			$this->params[6] = 0; // loop
		if(!isset($this->params[7]))
			$this->params[7] = 0; // border
		if(!isset($this->params[8]))
			$this->params[8] = 640; // width
		if(!isset($this->params[9]))
			$this->params[9] = 360; // height
		if(!isset($this->params[10]))
			$this->params[10] = ""; // poster
		if(!isset($this->params[11]))
			$this->params[11] = 0; // modal
		if(!isset($this->params[12]))
			$this->params[12] = ""; // modal btn

		// Pfad zur Bilddatei
		$this->imgFile			= Modules::getImagePath($this->params[10]);
		$basename				= basename($this->imgFile);

		$this->img_Src			= PROJECT_HTTP_ROOT . '/' . $this->imgFile;
		$this->imgSrc			= str_replace($basename, 'thumbs/' . $basename, $this->img_Src);
		$this->imgPath			= PROJECT_HTTP_ROOT . '/' . CC_IMAGE_FOLDER . '/';

		// Falls noch kein Bild ausgewählt
		if($this->params[10] == "") {
			$this->imgSrc		= SYSTEM_IMAGE_DIR . '/noimage.png';
			$this->img_Src		= $this->imgSrc;
		}

		// Falls Bild nicht vorhanden
		elseif(!file_exists(PROJECT_DOC_ROOT . '/' . $this->imgFile)){
			$this->imgSrc		= SYSTEM_IMAGE_DIR . '/noimage.png';
			$this->img_Src		= $this->imgSrc;
			
			$this->wrongInput[] = $this->conPrefix;
			$this->errorImg		= "{s_javascript:confirmreplace1}" . $this->imgFile . "&quot; {s_text:notexist}.";
		}
		else
			$this->imgPath		= pathinfo($this->img_Src, PATHINFO_DIRNAME);
	
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

		$output	.=	'<div class="existingFileBox leftBox">' . PHP_EOL .
					'<label class="elementsFileName">' . (!$this->params[0] ? "{s_label:choosefile}" : $this->params[0]) . '</label>' . PHP_EOL .
					'<div class="previewBox ' . $this->conType . '">' . PHP_EOL;

		$output	.=	'<span><img src="' . SYSTEM_IMAGE_DIR . '/' . $this->docIcon. '" alt="' . $this->docIcon . '" />' . PHP_EOL .
					'<a href="' . PROJECT_HTTP_ROOT . '/' . CC_VIDEO_FOLDER . '/' . $this->params[0] . '" target="_blank">' . $this->params[0] . '</a></span>' . PHP_EOL . 
					'</div>' . PHP_EOL;
		
		// Video MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "video",
											"type"		=> "video",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=video",
											"path"		=> PROJECT_HTTP_ROOT . '/' . CC_VIDEO_FOLDER . '/',
											"value"		=> "{s_button:videofolder}",
											"icon"		=> "video"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$output .=	'<input type="text" name="' . $this->conPrefix . '_existVideo" class="existingFile" value="' . htmlspecialchars($this->params[0]) . '" readonly="readonly" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;
		
		// Video title
		$output .=	'<label>{s_label:videotitle}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_alt" value="' . htmlspecialchars($this->params[1]) . '" maxlength="512" class="altText" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		$output .=	'<fieldset>' . PHP_EOL;
		
		// Video attr
		$output .=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_option:video} {s_text:settings}</label>' . PHP_EOL;
		
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_preload" id="' . $this->conPrefix . '-preload"' . ($this->params[2] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-preload" class="inline-label">preload</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_controls" id="' . $this->conPrefix . '-controls"' . ($this->params[3] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-controls" class="inline-label">{s_label:playermenu}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_muted" id="' . $this->conPrefix . '-muted"' . ($this->params[4] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-muted" class="inline-label">{s_label:mute}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_autoplay" id="' . $this->conPrefix . '-autoplay"' . ($this->params[5] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-autoplay" class="inline-label">{s_label:autoplay}</label>' . PHP_EOL .
					'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_loop" id="' . $this->conPrefix . '-loop"' . ($this->params[6] ? ' checked="checked"' : '') . ' /></label>' .
					'<label for="' . $this->conPrefix . '-loop" class="inline-label">{s_label:loop}</label>' . PHP_EOL;

		// Style
		$output	.=	'<label class="markBox"><input type="checkbox" name="' . $this->conPrefix . '_border" id="' . $this->conPrefix . '_border" ' . ($this->params[7] ? ' checked="checked"' : '') . ' /></label><label class=" inline-label" for="' . $this->conPrefix . '_border">' . PHP_EOL .
					'{s_label:border}</label>' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		
		// Size
		$output	.=	'<div>' . PHP_EOL .
					'<div class="halfBox">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_width">{s_label:width}</label>' . PHP_EOL .
					'<input class="numSpinner" type="text" name="' . $this->conPrefix . '_width" value="' . htmlspecialchars($this->params[8]) . '" id="' . $this->conPrefix . '_width" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'<div class="halfBox">' . PHP_EOL .
					'<label for="' . $this->conPrefix . '_height">{s_label:height}</label>' . PHP_EOL .
					'<input class="numSpinner" type="text" name="' . $this->conPrefix . '_height" value="' . htmlspecialchars($this->params[9]) . '" id="' . $this->conPrefix . '_height" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'</div>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		// Poster
		$output .=	'<div class="rightBox">' . PHP_EOL;
						
		$output	.=	'<div class="fileSelBox clearfix">' . PHP_EOL;
		$output .=	'<div class="existingFileBox">' . PHP_EOL .
					'<label>{s_label:poster}</label>' . PHP_EOL;
					
		if(!empty($this->errorImg))
			$output	.=	'<span class="notice error">' . $this->errorImg . '</span>' . PHP_EOL;
		
		
		// Btn Reset bg
		$mediaListButtonDef		= array(	"type"		=> "button",
											"class"		=> "button-icon-only button-small right",
											"text"		=> "",
											"value"		=> "{s_plugin-daslider:removebg}",
											"title"		=> "{s_plugin-daslider:removebg}",
											"attr"		=> 'onclick="$(this).closest(\'.existingFileBox\').find(\'.existingFile\').val(\'\');$(this).closest(\'.existingFileBox\').find(\'.preview\').attr(\'src\',\'' . SYSTEM_IMAGE_DIR . '/noimage.png\').attr(\'data-img-src\',\'' . SYSTEM_IMAGE_DIR . '/noimage.png\').attr(\'title\',\'no poster\'); $(this).siblings(\'.videoFile\').html(\'{s_label:choosefile}\');"',
											"icon"		=> "delete"
										);
		
		$btnResetImg 	=	$this->getButton($mediaListButtonDef);

		
		$output .=	'<label class="elementsFileName"><span class="videoFile">' . (!$this->params[10] ? "{s_label:choosefile}" : htmlspecialchars($this->params[10])) . '</span>' . $btnResetImg . '</label>' . PHP_EOL .
					'<div class="previewBox img">' . PHP_EOL .
					'<img src="' . $this->imgSrc . '" data-img-src="' . $this->img_Src . '" class="preview" alt="' . htmlspecialchars($this->params[10]) . '" title="' . htmlspecialchars($this->params[10]) . '" />' . PHP_EOL . 
					'</div>' . PHP_EOL;
		
		// Images MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "images",
											"type"		=> "images",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=images",
											"path"		=> $this->imgPath . 'thumbs/',
											"value"		=> "{s_button:imgfolder}",
											"icon"		=> "images"
										);
		
		$output 	.=	$this->getButtonMediaList($mediaListButtonDef);

		$output 	.=	'<input type="text" name="' . $this->conPrefix . '_poster" class="existingFile" value="' . htmlspecialchars($this->params[10]) . '" readonly="readonly" />' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		$output .=	'</div>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
			
		$output .=	'<fieldset>' . PHP_EOL;

		// Modal
		$output	 .=	'<div class="leftBox">' . PHP_EOL .
					'<label>Modal pop-up</label>' . PHP_EOL .
					'<select class="iconSelect" name="' . $this->conPrefix . '_modal">' . PHP_EOL .
					'<option value="0"' . ($this->params[11] == 0 ? ' selected="selected"' : '') . '>{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[11] == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;

		// Modal btn
		$output	 .=	'<div class="rightBox">' . PHP_EOL .
					'<label>Modal button text<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_modalbtn" value="' . $this->params[12] . '" />' . PHP_EOL .
					'</div>' . PHP_EOL;
	
		$output	 .=	'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
