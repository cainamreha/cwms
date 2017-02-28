<?php
namespace Concise;


##############################
#########  Dokument  #########
##############################

/**
 * DocConfigElement class
 * 
 * content type => doc
 */
class DocConfigElement extends ConfigElementFactory implements ConfigElements
{

	private $docPath		= CC_DOC_FOLDER;
	private $docLink		= "";
	private $overwrite		= false;				
	private $useFilesFolder	= USE_FILES_FOLDER;
	private $filesFolder	= "";
	private $docIcon		= "nodoc.png";
	private $folderStr		= "";

	/**
	 * Gibt ein DocConfigElement zurück
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
	
		$this->docPath		   .= '/';
	
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
			$upload_tmpfile	= $GLOBALS['_FILES'][$this->conPrefix]['tmp_name'];
			$docName		= $GLOBALS['_FILES'][$this->conPrefix]['name'];
			$fileFolder		= "";
			
			// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
			if(isset($this->a_POST[$this->conPrefix . '_overwrite']) && $this->a_POST[$this->conPrefix . '_overwrite'] == "on")
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

			$upload = Files::uploadFile($upload_file, $upload_tmpfile, $fileFolder, "doc", 0, 0, $this->overwrite, ""); // File-Upload
			#die($inputName );
			
			if($upload === true) {
				
				$this->params[0] = $this->folderStr . Files::getValidFileName($docName);
				
			}
			else {
				$this->wrongInput[] = $this->conPrefix;
				$this->error = $upload;
			}
			#var_dump($GLOBALS['_FILES'][$this->conPrefix]);
		}

		elseif(isset($this->a_POST[$this->conPrefix . '_existdoc']) && $this->a_POST[$this->conPrefix . '_existdoc'] != "") { // Falls eine vorhandene Datei übernommen werden soll

			$docName = $this->a_POST[$this->conPrefix . '_existdoc'];
			$this->params[0] = $docName;
		}

		// Weitere Post Felder
		if(isset($this->a_POST[$this->conPrefix . '_alt'])) { 

			$this->params[1] = $this->a_POST[$this->conPrefix . '_alt'];
			$this->params[2] = $this->a_POST[$this->conPrefix . '_title'];
			$this->params[3] = $this->a_POST[$this->conPrefix . '_filesize'];
			$this->params[4] = $this->a_POST[$this->conPrefix . '_icon'];
			$this->params[5] = $this->a_POST[$this->conPrefix . '_style'];
			$this->params[6] = $this->a_POST[$this->conPrefix . '_style2'];
			$this->params[7] = $this->a_POST[$this->conPrefix . '_fonticon'];
			
		}
	
	}
	
	
	// makeUpdateStr
	public function makeUpdateStr()
	{

		$docConValue = implode("<>", $this->params);
		$this->dbUpdateStr = "'" . $this->DB->escapeString($docConValue) . "',";

	}
	
	
	// setParams, Parameter (default) setzen
	public function setParams()
	{

		if($this->params[0] == "")
			$this->params[0]	= "{s_label:choosefile}";
		else {
			$fileExt = strtolower(substr($this->params[0], count($this->params[0])-4, 3));
			if($fileExt == "pdf")
				$this->docIcon	= "pdf.png";
			elseif($fileExt == "zip")
				$this->docIcon	= "zip.png";
			elseif($fileExt == "doc" || $fileExt == "docx")
				$this->docIcon	= "doc.png";
			else
				$this->docIcon	= "icon_file.png";
		}
				
		if(!isset($this->params[1]))
			$this->params[1]	= "";
		if(!isset($this->params[2]))
			$this->params[2]	= "";
		if(!isset($this->params[3]))
			$this->params[3]	= 1;
		if(!isset($this->params[4]))
			$this->params[4]	= 1;
		if(!isset($this->params[5]))
			$this->params[5]	= "";
		if(!isset($this->params[6]))
			$this->params[6]	= "";
		if(!isset($this->params[7]))
			$this->params[7]	= 0;
				

		// Pfad zur Dokumentdatei
		$basePath				= $this->docPath;
		
		// Falls files-Ordner, den Pfad ermitteln
		if(strpos($this->params[0], "/") !== false) {
			$filesDoc	= explode("/", $this->params[0]);
			$this->params[0]	= array_pop($filesDoc);
			$basePath			= CC_FILES_FOLDER . "/";
			$this->docPath		= $basePath . implode("/", $filesDoc) . "/";
		}
	
		
		// Dokumentname ggf. verschlüsseln
		require_once(PROJECT_DOC_ROOT."/inc/classes/Media/class.FileOutput.php"); // Klasse FileOutput einbinden

		// Dokumentlinkname
		$this->docLink = PROJECT_HTTP_ROOT . '/' . FileOutput::getFileHash($this->params[0], "doc", $basePath);
	
	}
	
	
	// makeUpdateStr
	public function getCreateElementHtml()
	{

		// Link classes
		$styleClasses	= array("btndef"	=> "btn-default",
								"btnpri"	=> "btn-primary",
								"btnsec"	=> "btn-secondary",
								"btnsuc"	=> "btn-success",
								"btninf"	=> "btn-info",
								"btnwar"	=> "btn-warning",
								"btndan"	=> "btn-danger",
								"btnlink"	=> "btn-link"
								);

		// Link classes 2
		$styleClasses2	= array("btnblock"	=> "btn-block",
								"btnlg"		=> "btn-lg",
								"btnsm"		=> "btn-sm",
								"btnxs"		=> "btn-xs"
								);

		
		$output	 =	'<h4 class="cc-contype-heading cc-h4">{s_option:' . $this->conType . '}</h4>' . PHP_EOL;
				
		if(in_array($this->conPrefix, $this->wrongInput)) // Falls im Fehlerarray vorhanden Meldung ausgeben
			$output	.=	'<span class="notice">' . $this->error . '</span>' . PHP_EOL;
		
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

		$output	.=	'<span><img src="' . SYSTEM_IMAGE_DIR . '/' . $this->docIcon . '" alt="' . $this->docIcon . '" />' . PHP_EOL .
					'<a href="' . $this->docLink . '" target="_blank">' . $this->params[0] . '</a></span>' . PHP_EOL . 
					'</div>' . PHP_EOL;

		// Docs MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "docs",
											"type"		=> "doc",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=doc",
											"path"		=> PROJECT_HTTP_ROOT . '/' . $this->docPath,
											"value"		=> "{s_button:docfolder}",
											"icon"		=> "doc"
										);
		
		$output .=	$this->getButtonMediaList($mediaListButtonDef);
					
		// Doc
		$output .=	'<input type="text" name="' . $this->conPrefix . '_existdoc" class="existingFile" value="' . htmlspecialchars($this->params[0]) . '" readonly="readonly" />' . PHP_EOL .
					'</div>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_label:attributes}">' . PHP_EOL;
					
		// Titel
		$output .=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_label:doctitle}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_alt" value="' . htmlspecialchars($this->params[1]) . '" maxlength="512" class="altText" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Titletag
		$output .=	'<div class="rightBox">' . PHP_EOL .
					'<label>{s_label:titletag}<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
					'<input type="text" name="' . $this->conPrefix . '_title" value="' . htmlspecialchars($this->params[2]) . '" maxlength="512" />' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		$output .=	'</fieldset>' . PHP_EOL;
		
		$output .=	'<fieldset data-tab="{s_label:attributes}">' . PHP_EOL;
						
		// Filesize
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>{s_header:filesize} {s_common:show}</label>' . PHP_EOL .
					'<select class="iconSelect" name="' . $this->conPrefix . '_filesize">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[3] ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;

					
		// Link style (class)
		$output .=	'<div class="buttonLinkClass rightBox">' . PHP_EOL . 
					'<label>Button Style 1</label>' . PHP_EOL;
		
		$output .=	'<select id="' . $this->conPrefix . '-style" name="' . $this->conPrefix . '_style">' . PHP_EOL .
					'<option data-value="" value="">{s_common:non}</option>' . PHP_EOL;
		
		foreach($styleClasses as $key => $linkClass) {
			$output .=	'<option value="' . $key. '"' . ($this->params[5] == $key ? ' selected="selected"' : '') . '>' . $linkClass . '</option>' . PHP_EOL;
		}
		
		$output .=	'</select>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		// Icon
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>Icon</label>' . PHP_EOL .
					'<select class="iconSelect" name="' . $this->conPrefix . '_icon">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[4] ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		// Link style 2 (class)
		$output .=	'<div class="buttonLinkClass rightBox">' . PHP_EOL . 
					'<label>Button Style 2</label>' . PHP_EOL;
		
		$output .=	'<select id="' . $this->conPrefix . '-style2" name="' . $this->conPrefix . '_style2">' . PHP_EOL .
					'<option data-value="" value="">{s_common:non}</option>' . PHP_EOL;
		
		foreach($styleClasses2 as $key => $linkClass) {
			$output .=	'<option value="' . $key. '"' . ($this->params[6] == $key ? ' selected="selected"' : '') . '>' . $linkClass . '</option>' . PHP_EOL;
		}
		
		$output .=	'</select>' . PHP_EOL;
		$output .=	'</div>' . PHP_EOL;
		
		// Font-Icon
		$output	.=	'<div class="leftBox">' . PHP_EOL .
					'<label>Font-Icon</label>' . PHP_EOL .
					'<select class="iconSelect" name="' . $this->conPrefix . '_fonticon">' . PHP_EOL .
					'<option value="0">{s_option:non}</option>' . PHP_EOL .
					'<option value="1"' . ($this->params[7] ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
					'</select>' . PHP_EOL .
					'</div>' . PHP_EOL;
		
		$output .=	'<br class="clearfloat" />' . PHP_EOL;
		
		$output .=	'</fieldset>' . PHP_EOL;

		return $output;
	
	}

}
