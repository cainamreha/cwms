<?php
namespace Concise;


##############################
########  Bildinhalt  ########
##############################

/**
 * ImgConfigElement class
 * 
 * content type => img
 */
class ImgConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $imgSrc			= "";
	private $img_Src		= "";
	private $imgPath		= CC_IMAGE_FOLDER;
	private $thumbPath		= "";
	private $imgFile		= "";
	private $overwrite		= false;				
	private $scaleImg		= 0;
	private $imgWidth		= 0;
	private $imgHeight		= 0;
	private $useFilesFolder	= USE_FILES_FOLDER;
	private $filesFolder	= "";
	private $folderStr		= "";

	/**
	 * Gibt ein ImgConfigElement zurück
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
	
		$this->imgPath		   .= "/";
		$this->thumbPath		= $this->imgPath . 'thumbs/';
	
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
			$imageName		= $GLOBALS['_FILES'][$this->conPrefix]['name'];
			$fileFolder		= "";
			
			// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
			if(isset($this->a_POST['overwrite_' . $this->conPrefix]) && $this->a_POST['overwrite_' . $this->conPrefix] == "on")
				$this->overwrite = true;				
			
			// Falls die Checkbox zum Skalieren von Bildern gecheckt ist
			if(isset($this->a_POST['scaleimg_' . $this->conPrefix]) && $this->a_POST['scaleimg_' . $this->conPrefix] == "on") {
				$this->scaleImg = 1;				
				
				// Bildbreite
				if(isset($this->a_POST['imgWidth_' . $this->conPrefix]) && is_numeric($this->a_POST['imgWidth_' . $this->conPrefix]))
					$this->imgWidth = htmlspecialchars($this->a_POST['imgWidth_' . $this->conPrefix]);
				
				// Bildhöhe
				if(isset($this->a_POST['imgHeight_' . $this->conPrefix]) && is_numeric($this->a_POST['imgHeight_' . $this->conPrefix]))
					$this->imgHeight = htmlspecialchars($this->a_POST['imgHeight_' . $this->conPrefix]);
			}
			
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

			$upload = Files::uploadFile($upload_file, $upload_tmpfile, $fileFolder, "image", $this->imgWidth, $this->imgHeight, $this->overwrite, ""); // File-Upload
			#die($inputName );
			
			if($upload === true) {
				
				$this->params[0] = $this->folderStr . Files::getValidFileName($imageName);
				
			}
			else {
				$this->wrongInput[] = $this->conPrefix;
				$this->error = $upload;
			}
			
		}

		elseif(isset($this->a_POST[$this->conPrefix . '_existImg']) && $this->a_POST[$this->conPrefix . '_existImg'] != "") // Falls eine vorhandene Datei übernommen werden soll
			$this->params[0]	= $this->a_POST[$this->conPrefix . '_existImg'];
		

		if(isset($this->a_POST[$this->conPrefix . '_alt']))
			$this->params[1]	= $this->a_POST[$this->conPrefix . '_alt'];
			
		if(isset($this->a_POST[$this->conPrefix . '_title']))
			$this->params[2]	= $this->a_POST[$this->conPrefix . '_title'];
			
		if(isset($this->a_POST[$this->conPrefix . '_caption']))
			$this->params[3]	= $this->a_POST[$this->conPrefix . '_caption'];
			
		if(isset($this->a_POST[$this->conPrefix . '_link']))
			$this->params[4]	= $this->a_POST[$this->conPrefix . '_link'];
			
		if(isset($this->a_POST[$this->conPrefix . '_imgclass']))
			$this->params[5]	= $this->a_POST[$this->conPrefix . '_imgclass'];
			
		if(isset($this->a_POST[$this->conPrefix . '_extra']))
			$this->params[6]	= $this->a_POST[$this->conPrefix . '_extra'];
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$imageConValue		= implode("<>", $this->params);		
		$this->dbUpdateStr	= "'" . $this->DB->escapeString($imageConValue) . "',";			

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if(!isset($this->params[1]))
			$this->params[1] = "";
	
		if(!isset($this->params[2]))
			$this->params[2] = "";
	
		if(!isset($this->params[3]))
			$this->params[3] = "";
	
		if(!isset($this->params[4]))
			$this->params[4] = "";
	
		if(!isset($this->params[5]))
			$this->params[5] = "";
	
		if(!isset($this->params[6]))
			$this->params[6] = 0;

		
		// Pfad zur Bilddatei
		// Falls files-Ordner, den Pfad ermitteln
		if(strpos($this->params[0], "/") !== false) {
			$filesImg			= explode("/", $this->params[0]);
			$this->params[0]	= array_pop($filesImg);					
			$this->imgPath		= CC_FILES_FOLDER . "/" . implode("/", $filesImg) . "/";
			$this->thumbPath	= $this->imgPath . 'thumbs/';
			$this->imgFile		= $this->params[0];
		}
		elseif(strpos($this->params[0], "{#img_root}") === 0) {
			$this->imgPath		= IMAGE_DIR;
			$this->thumbPath	= $this->imgPath;
			$this->imgFile		= str_replace("{#img_root}", "", $this->params[0]);					
		}
		else
			$this->imgFile		= $this->params[0];
			

		$this->imgSrc			= PROJECT_HTTP_ROOT . '/' . $this->thumbPath . $this->imgFile;
		$this->img_Src			= PROJECT_HTTP_ROOT . '/' . $this->imgPath . $this->imgFile;

		// Falls noch kein Bild ausgewählt
		if($this->params[0] == "") {
			$this->imgSrc		= PROJECT_HTTP_ROOT . '/system/themes/' . ADMIN_THEME . '/img/noimage.png';
			$this->img_Src		= $this->imgSrc;
		}

		// Falls Bild nicht vorhanden
		elseif(!file_exists(PROJECT_DOC_ROOT . '/' . $this->imgPath . $this->imgFile)){
			$this->imgSrc		= PROJECT_HTTP_ROOT . '/system/themes/' . ADMIN_THEME . '/img/noimage.png';
			$this->img_Src		= $this->imgSrc;
			
			$this->wrongInput[] = $this->conPrefix;
			$this->error		= "{s_javascript:confirmreplace1}" . $this->params[0] . "&quot; {s_text:notexist}.";
		}
	
	}
	
	
	// getCreateElementHtml
	public function getCreateElementHtml()
	{

		$output		 = '<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;

		// Falls im Fehlerarray vorhanden Meldung ausgeben
		if(in_array($this->conPrefix, $this->wrongInput))
			$output	.= '<span class="notice error">' . $this->error . '</span>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_option:' . $this->conType . '}">' . PHP_EOL;
			
		$output		.= '<div class="fileSelBox clearfix">' . PHP_EOL;

		// Dateiupload-Box
		$output		.=	'<div class="fileUploadBox rightBox">' . PHP_EOL .
						'<label class="uploadBoxLabel">{s_formfields:file}</label>' . PHP_EOL .
						$this->getUploadMask($this->conPrefix, $this->overwrite, $this->conPrefix, $this->scaleImg, $this->imgWidth, $this->imgHeight) .
						$this->getFilesUploadMask($this->filesFolder, $this->useFilesFolder, $this->conPrefix) .
						'</div>' . PHP_EOL;

		$output		.=	'<div class="existingFileBox leftBox">' . PHP_EOL .
						'<label class="elementsFileName">' . (!$this->params[0] ? "{s_label:choosefile}" : htmlspecialchars($this->params[0])) . '</label>' . PHP_EOL;

		$output 	.=	'<div class="previewBox ' . $this->conType . '">' . PHP_EOL .
						'<img src="' . $this->imgSrc . '" data-img-src="' . $this->img_Src . '" class="preview" alt="' . htmlspecialchars($this->params[1]) . '" title="' . htmlspecialchars($this->params[1]) . '" />' . PHP_EOL . 
						'</div>' . PHP_EOL;
		
		// Images MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "images",
											"type"		=> "images",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=images",
											"path"		=> PROJECT_HTTP_ROOT . '/' . $this->imgPath . 'thumbs/',
											"value"		=> "{s_button:imgfolder}",
											"icon"		=> "images"
										);
		
		$output 	.=	$this->getButtonMediaList($mediaListButtonDef);
					
		$output 	.=	'<input type="text" name="' . $this->conPrefix . '_existImg" class="existingFile" value="' . htmlspecialchars($this->params[0]) . '" readonly="readonly" />' . PHP_EOL .
						'</div>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		$output .=		'</fieldset>' . PHP_EOL;
		
		$output .=		'<fieldset data-tab="{s_label:attributes}">' . PHP_EOL;
		
		$output .=		'<label>alt-Text<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<input type="text" name="' . $this->conPrefix . '_alt" value="' . htmlspecialchars($this->params[1]) . '" maxlength="512" class="altText" />' . PHP_EOL .
						'<label>title-Text<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<input type="text" name="' . $this->conPrefix . '_title" value="' . htmlspecialchars($this->params[2]) . '" maxlength="512" class="altText" />' . PHP_EOL .
						'<label>{s_label:caption}<span class="toggleEditor" data-target="' . $this->conPrefix . '_caption">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<textarea id="' . $this->conPrefix . '_caption" class="disableEditor forceSave cc-editor-add textEditor cc-editor-small teaser" name="' . $this->conPrefix . '_caption" maxlength="1024">' . htmlentities($this->params[3], ENT_COMPAT, "UTF-8") . '</textarea>' . PHP_EOL;
		
		$output .=		'</fieldset>' . PHP_EOL;
		
		$output .=		'<fieldset data-tab="{s_label:attributes}">' . PHP_EOL;
		
		$output .=		'<label>Link</label>' . PHP_EOL .
						'<input type="text" name="' . $this->conPrefix . '_link" value="' . htmlspecialchars($this->params[4]) . '" maxlength="512" />' . PHP_EOL;
		
		$output .=		'</fieldset>' . PHP_EOL;
		
		$output .=		'<fieldset data-tab="{s_form:format}">' . PHP_EOL;
		
		// Image class
		$output		.=	'<div class="leftBox">' . PHP_EOL .
						'<label>{s_label:imgclass}</label>' . PHP_EOL .
						'<select name="' . $this->conPrefix . '_imgclass">' . PHP_EOL;
		
		$imgClasses	= array(	"None"			=> array("","{s_option:inactive}"),
								"imgF"			=> array("f","Image with frame"),
								"imgNF"			=> array("nf","Image without frame"),
								"imgR"			=> array("r","Rounded image"),
								"imgRF"			=> array("rf","Rounded image with frame"),
								"imgC"			=> array("c","Circular image"),
								"imgCF"			=> array("cf","Circular image with frame")
							);

		foreach($imgClasses as $key => $val){
			$output		.=	'<option value="' . $val[0] . '"' . ($val[0] == $this->params[5] ? ' selected="selected"' : '') . '>' . $val[1] . '</option>' . PHP_EOL;
		}
		
		$output		.=	'</select>' . PHP_EOL;
		$output		.=	'</div>' . PHP_EOL;
		
		$output		.=	'<div class="rightBox">' . PHP_EOL .
						'<label>Extra</label>' . PHP_EOL .
						'<div class="fieldBox clearfix">' .
						'<label for="' . $this->conPrefix . '_default">' . PHP_EOL .
						'<input type="radio" name="' . $this->conPrefix . '_extra" value="0" id="' . $this->conPrefix . '_default"' . (empty($this->params[6]) ? ' checked="checked"' : '') . ' />{s_common:non}</label>' .
						'<label for="' . $this->conPrefix . '_enlarge">' . PHP_EOL .
						'<input type="radio" name="' . $this->conPrefix . '_extra" value="1" id="' . $this->conPrefix . '_enlarge"' . ($this->params[6] == 1 ? ' checked="checked"' : '') . ' />Enlargable</label>' .
						'<label for="' . $this->conPrefix . '_zoom">' . PHP_EOL .
						'<input type="radio" name="' . $this->conPrefix . '_extra" value="2" id="' . $this->conPrefix . '_zoom"' . ($this->params[6] == 2 ? ' checked="checked"' : '') . ' />Zoom</label>' .
						'</div>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		$output		.=	'<br class="clearfloat" />' . PHP_EOL;
		
		$output		.=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
