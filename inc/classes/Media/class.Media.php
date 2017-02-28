<?php
namespace Concise;


//Files-Klasse einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";


/**
 * Klasse Media
 * 
 */

class Media extends ContentsEngine
{

	private $uploadMethod	= "auto";
	private $action			= "";
	private $type			= "";
	private $folder			= "media/images/thumbs";
	private $gallFolder		= "";
	private $existMedia		= array();
	private $mediaTypes		= array();
	private $mediaDates		= array();
	private $isFetchList	= true;
	private $hashFileTypes	= array("doc");
	private $script			= "";
	private $adminObject	= null;
	
 	/**
	 * Media Konstruktor
	 * 
	 * @param	string	$DB 		DB-Objekt
	 * @param	string	$o_lng		Sprach-Objekt
	 * @param	string	$o_admin	Admin-Objekt
	 * @access	public
	 */
	 
	public function __construct($DB, $o_lng, $o_admin)
	{
	
		$this->DB				= $DB;
		$this->o_lng			= $o_lng;
		$this->adminObject		= $o_admin;
		$this->uploadMethod		= Files::getUploadMethod();
		#$this->adminObject->uploadMethod	= $this->uploadMethod;
	
	}

	
 	/**
	 * Media Action
	 * 
	 * @param	string	$action 	DB-Objekt
	 * @param	string	$type		Medien-Listentyp
	 * @param	string	$subFolder	Unterordner (default = '')
	 * @param	string	$index		Index-Counter
	 * @access	public
	 */	 
	public function doAction($action, $type, $subFolder = "", $index = "")
	{

		$output				= "";
		$this->action		= $action;
		$this->type			= $type;
		$this->folder		= $this->getMediaFolder($this->type, $subFolder);		

		
		// Falls eine Galerie bearbeitet werden soll
		if($this->action == "edit") {
			$output			.= $this->listMedia($this->type, $this->folder, $this->action, $this->adminObject->editLang);			
		}
		
		// Falls Galeriebilder sortiert werden sollen
		elseif($this->action == "sort" && $this->type == "gallery")
			$output			.= $this->doActionSortGallery();
		
		// Falls ein Bild gedreht werden soll
		elseif($this->action == "rotate") {
			return $this->doActionRotateImage();
		}
		
		// ListBox zum Löschen von Dateien
		elseif($this->action == "del")
			$output			.= $this->doActionDeleteFile();
		
		// Löschen von Orderinhalten
		elseif($this->action == "delall")
			$output			.= $this->doActionDeleteMediaFolder();
			
		// Falls ein neuer Ordner im Files-Ordner angelegt werden soll
		elseif($this->action == "newfolder" && !empty($GLOBALS['_GET']['foldername']))
			$output			.= $this->createNewFilesSubfolder($GLOBALS['_GET']['foldername']);
		
		// Andernfalls Medien auflisten
		else {
			$output			.= $this->getMediaList($index);
		}
		
		return ContentsEngine::replaceStaText($output);
	
	}

	
 	/**
	 * doActionSortGallery
	 * 
	 * @access	public
	 */	 
	public function doActionSortGallery()
	{

		if(!empty($GLOBALS['_GET']['gal']))
			$gallName	= $GLOBALS['_GET']['gal'];
				
		if(isset($GLOBALS['_GET']['item']) && is_numeric($GLOBALS['_GET']['item']))
			$itemId		= (int)$GLOBALS['_GET']['item'];
				
		if(isset($GLOBALS['_GET']['sortIdOld']) && $GLOBALS['_GET']['sortIdOld'] != "" && is_numeric($GLOBALS['_GET']['sortIdOld']))
			$sortIdOld	= $GLOBALS['_GET']['sortIdOld'];
				
		if(isset($GLOBALS['_GET']['sortIdNew']) && $GLOBALS['_GET']['sortIdNew'] != "" && is_numeric($GLOBALS['_GET']['sortIdNew']))
			$sortIdNew	= $GLOBALS['_GET']['sortIdNew'];
	
		$sortResult		= $this->sortGallery($gallName, $itemId, $sortIdOld, $sortIdNew);
		
		if($sortResult)
			return "1";
		else
			return "0";

	}

	
 	/**
	 * doActionDeleteFile
	 * 
	 * @access	public
	 */	 
	public function doActionDeleteFile()
	{

		// Medienliste
		$output 		=	'<h2 class="listBoxHeader cc-section-heading cc-h2">{s_header:listmedia} &rsaquo; ' . str_replace("/thumbs", "", $this->folder) . '</h2>' . "\r\n";
		$mediaList		=	$this->listMedia($this->type, $this->folder, "del");
		
		$newFolder		=	$this->type == "files" ? true : false;
		$fileUpload		=	$this->type == "theme" || $this->type == "systemimg" || $this->folder == CC_FILES_FOLDER ? false : true;
				
		// Control Bar
		$controlBar		=	$this->adminObject->getControlBar("date", "", "", true, $newFolder, false, $fileUpload, $this->type, $this->folder);
		
		
		$output .=	$controlBar . $mediaList; 
		
		
		if(!empty($GLOBALS['_GET']['target'])) {
			
			$filename	= utf8_decode($GLOBALS['_GET']['target']);
			$output		= $this->deleteMediaFile($this->type, $filename, $this->folder); // Mediendatei löschen
			
		}
		
		return $output;
	
	}

	
 	/**
	 * doActionDeleteMediaFolder
	 * 
	 * @access	public
	 */	 
	public function doActionDeleteMediaFolder()
	{

		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Löschen einer Bildergalerie
		if(!empty($GLOBALS['_GET']['gal'])) {
		
			$delGall = $GLOBALS['_GET']['gal'];
			
			$this->deleteGallery($delGall, $this-folder); // löschen
	
			$this->setSessionVar('notice', "{s_notice:delgall}");
			
			$queryExt	= "task=modules&type=gallery";
		}
		
		// Andernfalls Medienordner(inhalte) löschen
		elseif($this->type != "") {
		
			$this->deleteMediaFolder($this->type, $this->folder); // löschen	
	
			$this->setSessionVar('notice', "{s_notice:delall}");
			
			$queryExt	= "task=file";
		}
		
		header("Location: " . ADMIN_HTTP_ROOT . "?" . $queryExt);
		exit;
	}

	
 	/**
	 * doActionRotateImage
	 * 
	 * @access	public
	 */	 
	public function doActionRotateImage()
	{

		if(!empty($GLOBALS['_GET']['file'])
		&& !empty($GLOBALS['_GET']['folder'])
		) {
			
			$fileName		= $GLOBALS['_GET']['file'];
			$this->folder	= $GLOBALS['_GET']['folder'];
			
			if(isset($GLOBALS['_GET']['dir']) && $GLOBALS['_GET']['dir'] == "left")
				$angle		= 270; // Gegen den Uhrzeigersinn
			else
				$angle		= 90; // Im Uhrzeigersinn
			
			$imgFile = PROJECT_DOC_ROOT . '/' . $this->folder . '/' . $fileName;
			
			if(file_exists($imgFile)) {
				
				//Files-Klasse einbinden
				require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";
				
				$output = Files::rotateImage($fileName, $this->folder, $angle);
			}
			else
				$output = "file not found: " . $imgFile;				
		}
		else
			$output = "no file specified or folder not found.";

		return $output;
	
	}

	
 	/**
	 * Medienordner bestimmen
	 * 
	 * @param	string	$type		Medien-Listentyp
	 * @param	string	$subFolder	Unterordner (default = '')
	 * @access	public
	 */	 
	public function getMediaFolder($type, $subFolder)
	{
		
		switch($type) {
			
			case "doc";
			case "docs";
				$folder = CC_DOC_FOLDER;
				break;
				
			case "systemimg":
				$folder = "system/themes/" . ADMIN_THEME . "/img";
				break;
				
			case "video";
			case "movie";
			case "movies";
			case "flash";
				$folder = CC_VIDEO_FOLDER;
				break;
				
			case "audio":
				$folder = CC_AUDIO_FOLDER;
				break;
				
			case "files":
				$subFolder = strpos($subFolder, CC_FILES_FOLDER) === 1 ? str_replace(CC_FILES_FOLDER, "", $subFolder) : $subFolder;
				$folder = CC_FILES_FOLDER . $subFolder;
				break;
				
			case "gallery":
				$subFolder = strpos($subFolder, CC_GALLERY_FOLDER) === 1 ? str_replace(CC_GALLERY_FOLDER, "", $subFolder) : $subFolder;
				$folder = CC_GALLERY_FOLDER . $subFolder;
				break;
				
			case "theme":
				$folder = substr(IMAGE_DIR, 0, -1);
				break;
				
			default:
				$folder = CC_IMAGE_FOLDER . '/thumbs';
				break;
				
		}
	
		return $folder;
	
	}

	
 	/**
	 * createNewFilesSubfolder
	 * 
	 * @param	string	$folderName		Foldername zu löschender Ornder
	 * @access	public
	 */	 
	public function createNewFilesSubfolder($folderName)
	{

		$parentFolder = PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER . '/';
		
		if(!empty($GLOBALS['_GET']['parentfolder']))
			$parentFolder .= $GLOBALS['_GET']['parentfolder'] . '/';
			
		$newFolder = $parentFolder . $folderName;
		
		// Ordner anlegen
		if(!is_dir($newFolder))
			mkdir($newFolder);
		
		return $newFolder;
	
	}

	
 	/**
	 * deleteMediaFolder
	 * 
	 * @param	string	$type		Medientyp
	 * @param	string	$folder		Foldername zu löschender Ornder
	 * @access	public
	 */	 
	public function deleteMediaFolder($type, $delFolder)
	{
	
		// Bilder mit Thumbs löschen, falls image
		if($type == "image") {		
			$del	= Admin::unlinkRecursive(PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER);
		}
		// Files-Ordner mit Unterordnern löschen
		elseif($type == "files") {
			$del	= Admin::unlinkRecursive(PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER, true);
			mkdir(PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER); // files-Ordner neu anlegen
		}
		else {
			// Ordnerinhalt löschen
			$del	= Admin::unlinkRecursive(PROJECT_DOC_ROOT . '/' . $delFolder);
		}
		
		return $del;
	
	}

	
 	/**
	 * deleteMediaFile
	 * 
	 * @param	string	$type		Medientyp
	 * @param	string	$filename	Filename zu löschende Datei
	 * @param	string	$folder		Foldername
	 * @access	public
	 */	 
	public function deleteMediaFile($type, $filename, $folder)
	{

		$result	= false;
		
		// Falls Verzeichnis, rekursiv löschen
		if(is_dir(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filename))
			$result		= Admin::unlinkRecursive(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filename, true);
		
		// Andernfalls Datei löschen
		else
			$result		= @unlink(PROJECT_DOC_ROOT . '/' . $folder . '/' . $filename);
		
		
		// Falls Filesordner oder Galerie, zusätzlich Thumbnail-Datei löschen
		if($type == "files"
		|| $type == "gallery"
		)
			$result		= $this->deleteImageSrcset($filename, $folder);

			
		// Falls Bilderordner, zusätzlich Hauptbild-Datei löschen
		elseif($folder == CC_IMAGE_FOLDER . '/thumbs')
			$result		= @unlink(PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/' . $filename);
			
			
		// Falls ein Bild aus dem Theme-Bilderordner gelöscht werden soll
		elseif($folder == IMAGE_DIR)
			$result		= @unlink(PROJECT_DOC_ROOT . '/' . IMAGE_DIR . $filename);

			// Falls ein Bild aus einer Gallerie gelöscht werden soll
		if($type == "gallery") {
			
			// Delete potential videos
			$this->deleteGalleryVideos($filename, $folder);
			
			$gallName	= str_replace(CC_GALLERY_FOLDER . '/', "", $folder);
			$gallName	= str_replace('/thumbs', "", $gallName);
			$gallName	= trim($gallName, "/");

			$result		= $this->deleteGalleryImageFromDB($filename, $gallName); // Galeriebild aus DB löschen
			
		}

		return $result;
	
	}

	
 	/**
	 * deleteImageSrcsets
	 * 
	 * @param	string	$filename	Filename zu löschende Datei
	 * @param	string	$folder		Folder
	 * @access	public
	 */	 
	public function deleteImageSrcset($filename, $folder)
	{
	
		$result	= false;

		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/thumbs/' . $filename))
			$result		= @unlink(PROJECT_DOC_ROOT . '/' . $folder . '/thumbs/' . $filename);
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/small/' . $filename))
			$result		= @unlink(PROJECT_DOC_ROOT . '/' . $folder . '/small/' . $filename);
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/medium/' . $filename))
			$result		= @unlink(PROJECT_DOC_ROOT . '/' . $folder . '/medium/' . $filename);
	
		return $result;
	
	}

	
 	/**
	 * deleteGalleryVideos
	 * 
	 * @param	string	$filename	Filename zu löschende Datei
	 * @param	string	$folder		Folder
	 * @access	public
	 */	 
	public function deleteGalleryVideos($filename, $folder)
	{
	
		// If video in gallery folder
		$fileNameBase	= pathinfo($filename, PATHINFO_FILENAME);
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $fileNameBase . '.mp4'))
			@unlink(PROJECT_DOC_ROOT . '/' . $folder . '/' . $fileNameBase . '.mp4');
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $fileNameBase . '.webm'))
			@unlink(PROJECT_DOC_ROOT . '/' . $folder . '/' . $fileNameBase . '.webm');
		if(file_exists(PROJECT_DOC_ROOT . '/' . $folder . '/' . $fileNameBase . '.ogv'))
			@unlink(PROJECT_DOC_ROOT . '/' . $folder . '/' . $fileNameBase . '.ogv');
	
	}

	
 	/**
	 * deleteGalleryImageFromDB
	 * 
	 * @param	string	$filename	Filename zu löschende Datei
	 * @param	string	$folder		Gallery name
	 * @access	public
	 */	 
	public function deleteGalleryImageFromDB($filename, $gallName)
	{

		$filenameDB	= $this->DB->escapeString($filename);
		$gallNameDB	= $this->DB->escapeString($gallName);

		
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "galleries`, `" . DB_TABLE_PREFIX . "galleries_images`");

			
		// Löschen des Galleriebildes
		$deleteSQL = $this->DB->query( "DELETE FROM `" . DB_TABLE_PREFIX . "galleries_images` 
										WHERE `gallery_id` = (SELECT `id` FROM `" . DB_TABLE_PREFIX . "galleries` WHERE `gallery_name` = '$gallNameDB') 
											AND `img_file` = '$filenameDB'
										");

		#var_dump($deleteSQL);
		
		// Variable für Neusortierung setzen
		$updateSQL1 = $this->DB->query("SET @c:=0;");
		
		
		// Neusortierung
		$updateSQL2 = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
											SET `sort_id` = (SELECT @c:=@c+1)
										WHERE `gallery_id` = (SELECT `id` FROM `" . DB_TABLE_PREFIX . "galleries` WHERE `gallery_name` = '$gallNameDB') 
											ORDER BY `sort_id` ASC;
										");

		#var_dump($updateSQL1.$updateSQL2);
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		if( $deleteSQL === true && 
			$updateSQL1 === true && 
			$updateSQL2 === true
		)
			return true;
		else
			return false;

	}

	
 	/**
	 * deleteGallery
	 * 
	 * @param	string	$gallName	Name der zu löschenden Galerie
	 * @param	string	$folder		Gallery name
	 * @access	public
	 */	 
	public function deleteGallery($gallName, $folder)
	{
		
		// Thumbs löschen
		Admin::unlinkRecursive(PROJECT_DOC_ROOT . '/' . $folder, true);
		// Gallerie löschen
		Admin::unlinkRecursive(PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $gallName, true);

		// Galerie aus DB löschen
		$delGall = $this->deleteGalleryFromDB($gallName);
		
	}		

	
 	/**
	 * deleteGallery
	 * 
	 * @param	string	$gallName	Name der zu löschenden Galerie
	 * @access	public
	 */	 
	public function deleteGalleryFromDB($gallName)
	{
		
		$delGallDB = $this->DB->escapeString($gallName);
		
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "galleries`, `" . DB_TABLE_PREFIX . "galleries_images`");

		// Einfügen des neuen Seiteninhaltspunkts
		$deleteSQL1 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "galleries_images` 
											WHERE `gallery_id` = (SELECT `id` FROM `" . DB_TABLE_PREFIX . "galleries` WHERE `gallery_name` = '$delGallDB') 
										");	

										
		$deleteSQL2 = $this->DB->query("DELETE FROM `" . DB_TABLE_PREFIX . "galleries` 
											WHERE `gallery_name` = '$delGallDB'
										");


		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		
		if(	$deleteSQL1 === true && 
			$deleteSQL2 === true
		)
			return true;
		else
			return false;
	}		

	
 	/**
	 * sortGallery
	 * 
	 * @param	string	$gallName	Name der zu löschenden Galerie
	 * @param	int		$itemId		Item-ID
	 * @param	int		$sortIdOld	old sort id
	 * @param	int		$sortIdNew	new sort id
	 * @access	public
	 */	 
	public function sortGallery($gallName, $itemId, $sortIdOld, $sortIdNew)
	{
	
		if( empty($gallName) || 
			empty($itemId) || 
			empty($sortIdOld) || 
			empty($sortIdNew)
		)
			return false;
		
		$return			= array();
		$sortIdOldDB	= $this->DB->escapeString($sortIdOld);
		$sortIdNewDB	= $this->DB->escapeString($sortIdNew);
		$gallNameDB		= $this->DB->escapeString($gallName);
	
	
		// Einträge in DB löschen
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "galleries`, `" . DB_TABLE_PREFIX . "galleries_images`");
	
				
		// Gall-ID bestimmen
		$idQuery	= $this->DB->query("SELECT `id` 
											 FROM `" . DB_TABLE_PREFIX . "galleries` 
											 WHERE `gallery_name` = '$gallNameDB' 
											");
									
		if(!isset($idQuery[0]['id']))
			return false;
		
		
		$gallID	= (int)$idQuery[0]['id'];
		
		
		if($sortIdOld < $sortIdNew)	
			// Neusortierung
			$updateSQL1 = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
													SET `sort_id` = `sort_id`-1
												 WHERE `gallery_id` = $gallID 
													AND `sort_id` > $sortIdOldDB 
													AND `sort_id` <= $sortIdNewDB
												");
		
		if($sortIdOld > $sortIdNew)	
			// Neusortierung
			$updateSQL1 = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
													SET `sort_id` = `sort_id`+1
												 WHERE `gallery_id` = $gallID 
													AND `sort_id` < $sortIdOldDB 
													AND `sort_id` >= $sortIdNewDB
												");
		
		
		// Neusortierung
		$updateSQL2 = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
												SET `sort_id` = $sortIdNewDB
											 WHERE `gallery_id` = $gallID 
												AND `id` = $itemId
											");
	
		// Sortierung neu durchführen
		// Variable für Neusortierung setzen
		$updateSQL3a = $this->DB->query("SET @c:=0;
											");
		
		
		// Neusortierung
		$updateSQL3b = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "galleries_images` 
												SET `sort_id` = (SELECT @c:=@c+1)
											  WHERE `gallery_id` = $gallID 
												ORDER BY `sort_id` ASC
											  ");
	
	
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
	
		if(	$updateSQL1 === true && 
			$updateSQL2 === true && 
			$updateSQL3a === true && 
			$updateSQL3b === true
		)
			return true;
		else
			return false;
	
	}


	/**
	 * Erstellt eine Liste mit Mediendateien aus dem Mediaordner
	 * 
	 * @param	string	$index	Index-Counter (default = '')
	 * @access	public
     * @return  string
	 */	 
	public function getMediaList($index = "")
	{
		
		$newFolder			= false;
		$fileUpload			= true;
		$mediaListType		= "preview";
		
		// Falls Ordner im files-Ordner aufgelistet werden sollen
		if($this->action == "listfolders") {
			$mediaListType	= "listfolders";
			$newFolder		= true;
			$fileUpload		= false;
		}
		// Falls Ordner im files-Ordner aufgelistet werden sollen
		if($this->type == "files") {
			$newFolder		= true;
		}
		
		if($this->folder == CC_FILES_FOLDER)
			$fileUpload		= false;
			
		// Falls das Umschalten zwischen Default- und files-Ordner erlaubt werden soll
		if(($this->type != "gallery" 
		 && $this->type != "theme" 
		 && $this->type != "systemimg") 
		 && ($newFolder == false
		 || $this->action == "list")
		)
			$switchFolder	= true;
		else
			$switchFolder	= false;
		
		
		// ListBox output
		$output 		=	'<h2 class="listBoxHeader cc-section-heading cc-h2">{s_header:listmedia} &rsaquo; ' . str_replace("/thumbs", "", $this->folder) . '</h2>' . "\r\n";
		
		// Medienliste
		$mediaList		=	$this->listMedia($this->type, $this->folder, $mediaListType, $this->adminObject->editLang, $index); 
		
		// Control Bar
		$controlBar		=	$this->adminObject->getControlBar("date", "", "", false, $newFolder, $switchFolder, $fileUpload, $this->type, $this->folder);
		
		$output .=	$controlBar . $mediaList;
		
		return $output;
	
	}


	/**
	 * Generiert Listeninhalte aus einem bestimmten Mediaordner
	 * 
	 * @param	string	$type Medien-Typ (default = 'images')
	 * @param	string	$folder Medienordner (default = media/images/thumbs)
     * @param	string	$action auszuführende Aktion (default = '')
     * @param	string	$lang Sprache (default = '')
     * @param	string	$elementNr Elementnummer zur Unterscheidung von Audioobjekten (default = '')
	 * @access	public
     * @return  string
	 */	 
	public function listMedia($type = "images", $folder = "media/images/thumbs", $action = "", $lang = "", $elementNr = "")
	{
	
		// Dokumentname ggf. verschlüsseln
		require_once(PROJECT_DOC_ROOT."/inc/classes/Media/class.FileOutput.php"); // Klasse FileOutput einbinden
		
		$this->type		= $type;
		
		if($action == "del" 
		|| $action == "edit"
		)
			$this->isFetchList	= false;
		

		if($lang == "")
			$lang = $this->adminObject->editLang;
			
		$markBox		= "";
		$forceSrc		= "?" . time();
		$checkUpdateDB	= false;
		
		$i				= 1; // Zähler für Dateinummern
		
		// Form action
		if(strpos($folder, CC_GALLERY_FOLDER) !== false) {
			$formAction		= ADMIN_HTTP_ROOT . '?task=modules&type=gallery';
			$checkUpdateDB	= true;
		}
		else
			$formAction		= ADMIN_HTTP_ROOT . '?task=file';
			
		
		// Dialog-Formular
		$dialogForm		= 	$this->getRenameDialogForm($formAction, $checkUpdateDB);					
		
		
		// Start Medienliste
		// Button close
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'closeListBox close button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:close}',
								"icon"		=> "close"
							);
		
		$mediaList		=	parent::getButton($btnDefs);
		
		
		// Falls die Gallerieliste zum Bearbeiten der Bildtexte angezeigt werden soll
		if(strpos($folder, CC_GALLERY_FOLDER) === 0) {
		
			$this->gallFolder	= preg_replace("/media\/galleries\/(.*)\/thumbs/", "\\1", $folder);
			
			// Datenbanksuche nach Galleriebilder-Daten der Gallerie
			$this->existMedia	= $this->getGalleryDataFromDB($this->gallFolder);			
		
			// Anzahl an Mediendaten
			$this->adminObject->mediaCount = count($this->existMedia);			
			
			// Falls keine Inhalte vorhanden
			if($this->adminObject->mediaCount == 0) {
				$mediaList	.= $this->getGalleryListHead($folder, $action, $dialogForm);
				$mediaList	.= '<p class="cc-hint notice error">{s_text:nofiles}</p>';
				return parent::replaceStaText($mediaList);
			}			
			
			$mediaList	.= $this->getGalleryListHead($folder, $action, $dialogForm);
			
		}
		
		// Andernfalls Ordnerinhalte einlesen
		else {
		
			// Ordner ins Array einlesen
			// Falls versucht wird einen Unterordner anzuzeigen, der nicht existiert, root anzeigen
			if(strpos($folder, CC_FILES_FOLDER) === 0 
			&& !is_dir(PROJECT_DOC_ROOT . '/' . $folder)
			)
				$folder = CC_FILES_FOLDER;
						
			$mediaList .=	'<div class="listItemBox ' . $this->type . '" data-action="' . $action . '">' . "\r\n";
						
			// Falls kein Ordner
			if(!is_dir(PROJECT_DOC_ROOT . '/' . $folder))
				return $mediaList . '<p class="cc-hint notice error">{s_text:folder} &quot;' . $folder . '&quot; {s_text:notexist}.</p></div>';
			
			
			// Sonst Ordner ins Array einlesen
			$this->readMediaFolder($folder, $action);
		
			// Falls keine Mediendateien vorhanden
			if(count($this->existMedia) == 0) {
			
				// Falls ein Unterordner angezeigt wird, Button zum eine Ebene höher gehen anzeigen
				if(strpos($folder, CC_FILES_FOLDER) === 0 
				&& $folder != CC_FILES_FOLDER
				) {
					
					$subPath		= str_replace(CC_FILES_FOLDER . "/", "", $folder);
					$pathElements	= explode("/", $subPath);
					$current		= array_pop($pathElements);
	
					// Button folderup
					$btnDefs	= array(	"href"		=> implode("/", $pathElements),
											"class"		=> 'folderUp button-icon-only',
											"text"		=> '&nbsp;',
											"title"		=> '{s_title:folderup}' . implode("/", $pathElements),
											"icon"		=> "folderup"
										);
						
					$mediaList .=	parent::getButtonLink($btnDefs);
	
					$mediaList .=	'<span class="currentFolderPath">';
			
					$mediaList .=	parent::getIcon("folderopen");
					
					$mediaList .=	implode(" &rsaquo; ", $pathElements).
									'<span class="currentFolder"> &rsaquo; ' . $current . '</span>' .
									'</span>' . "\r\n" .
									'<br class="clearfloat" />' . "\r\n";
				}
				
				return $mediaList . '<p class="cc-hint notice error">{s_text:folder} &quot;' . $folder . '&quot; {s_text:empty}.</p></div>';
			
			}
			
			// Falls ein Unterordner von files angezeigt wird, Arrays neu sortieren
			elseif(strpos($folder, CC_FILES_FOLDER) === 0 
			&& $folder != CC_FILES_FOLDER
			) {
				array_multisort($this->mediaTypes, SORT_NUMERIC, SORT_DESC, $this->existMedia, $this->mediaDates);
			}

			
			// Anzahl an Mediendaten
			$this->adminObject->mediaCount = count($this->existMedia);
			
			
			// Begin editList
			$mediaList .=	'<ul class="editList">' . "\r\n";
			
			// Dialog-Form zum Ändern von Dateinamen einbinden
			$mediaList .= 	$dialogForm;
			
		} // Ende else Ordnerinhalte
		

		// Schleife zum Auflisten der Medien
		foreach($this->existMedia as $media) {
			
			// Medien-Datei
			$mediaFile		= $media;
			$fileFolder 	= $folder;
			$thumbFolder 	= $folder;
			
			// Falls Bilder oder Galerie, NICHT die Thumbdateigröße auslesen
			if($folder == CC_IMAGE_FOLDER . "/thumbs") {
				$fileFolder = str_replace("/thumbs", "", $folder);
			}
			elseif(strpos($folder, CC_GALLERY_FOLDER) === 0) {
				$mediaFile	= $media['img_file'];
				$fileFolder = preg_replace("/\/thumbs$/", '', $folder);
				$this->mediaDates[$i-1] = $media['mod_date'];
				$this->mediaTypes[$i-1] = false;
			}
			elseif(strpos($folder, CC_FILES_FOLDER . "/") === 0)
				$thumbFolder 	= $folder . '/thumbs';
			
			
			$dataUrlPath	= PROJECT_HTTP_ROOT . '/' . $folder . '/' . $mediaFile;
			$subPath		= $this->type == "files" ? str_replace(CC_FILES_FOLDER . "/", "", $folder) : '';
			
			if($subPath != "")
				$subPath   .= "/";
				
			// Dateidaten
			$fileSize		= Modules::getFileSizeString($mediaFile, $fileFolder);
			$fileExt 		= strtolower(Files::getFileExt($mediaFile));
			$fileCat		= Files::getFileType($mediaFile);
			$fileIcon		= FileOutput::getFileIcon($fileExt);
			$mediaObj		= "";
			$markBox		= "";
			$editButtons	= "";
			$renameButton	= "";
			$thumbsButton	= "";
			$rotateButtons	= "";
			$deleteButton	= "";
			$fetchButton	= "";
			
			// Checkbox zur Mehrfachauswahl einfügen, falls Datei-Liste (löschen)
			if(!$this->isFetchList)
				$markBox =	'<label class="markBox">' . 
							'<input type="checkbox" name="entryNr[' . $i . ']" class="addVal" />' .
							'<input type="hidden" name="entryID[' . $i . ']" value="' . $mediaFile . '" class="getVal" />' .
							'</label>';
			
			// Datei Previewlink
			if(in_array($this->type, $this->hashFileTypes))
				$previewLink	= PROJECT_HTTP_ROOT . '/' . FileOutput::getFileHash($subPath . $mediaFile, $fileCat, CC_FILES_FOLDER . "/");
			else
				$previewLink	= PROJECT_HTTP_ROOT . '/' . $fileFolder . '/' . $mediaFile;

			if($this->type == "audio") {
				require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.AudioContent.php"; // AudioContent-Klasse einbinden
				
				$mediaObj 	= AudioContent::getHTML5Audio(PROJECT_HTTP_ROOT . '/' . $folder . '/' . $mediaFile, "") . "\r\n";
			}
			
			 // Falls Ordner
			if($this->mediaTypes[$i-1])
				$fileIcon = "icon_folder.png";
			
			
			// File icon
			$iconClass	= str_replace("icon_", "", basename($fileIcon, ".png"));
			$fileIcon	= parent::getIcon($iconClass, "noclick fileTypeIcon", 'style="background:url(' . SYSTEM_IMAGE_DIR . '/' . $fileIcon . ') no-repeat center" title="' . $fileExt . '"');
			
			
			// Fetch Button, falls kein Ordner oder Ordnerauswahlliste
			if($this->isFetchList
			&& ($action == "listfolders" 
				|| !is_dir(PROJECT_DOC_ROOT . '/' . $folder . '/' . $mediaFile))
			) {
				
				if(($action == "listfolders" 
				||  $action == "")
				&& strpos(CC_FILES_FOLDER . "/", $subPath) === 0
				)
					$subPath		= str_replace(CC_FILES_FOLDER . "/", "", $subPath);

				$filesFileFolder	= $subPath . $mediaFile;

				// Button fetch
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> $this->type . 'Selection mediaSelection fetch' . ($this->mediaTypes[$i-1] ? ' folder' : '') . ' button-icon-only',
										"value"		=> "",
										"title"		=> $mediaFile . ' {s_common:fetch}',
										"attr"		=> 'data-title="' . $mediaFile . '" data-file="' . $filesFileFolder . '" data-path="' . $dataUrlPath . '" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="' . $mediaFile . ' {s_common:fetch}"',
										"icon"		=> "fetch"
									);
				
				$fetchButton =	parent::getButton($btnDefs);
				
			}

			
			// Button zum Umbenennen der Datei
			// Button rename
			$btnDefs	= array(	"type"		=> "button",
									"id"		=> 'file-' . $mediaFile,
									"class"		=> 'changeFileName dialog dialog-file button-icon-only',
									"value"		=> "",
									"title"		=> '{s_title:changefilename}',
									"attr"		=> 'data-dialog="file" data-dialogname="' . $mediaFile . '" data-dialogid="' . $folder . '" data-title="{s_title:changefilename}" data-id="item-id-' . $i . '" data-menuitem="true" data-menutitle="{s_title:changefilename}"',
									"icon"		=> "rename"
								);
			
			$renameButton 	=	parent::getButton($btnDefs);
			
			
			// Falls Bilddatei und kein Thumb, Icons zum Drehen einfügen
			if($this->mediaTypes[$i-1]
			&& $mediaFile != "thumbs"
			&& $mediaFile != "small"
			&& $mediaFile != "medium"
			) {

				// Button generate thumbs
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'generateThumbs button-icon-only',
										"text"		=> "",
										"title"		=> '{s_title:genthumbs}<br /><br />(thumb-h: ' . THUMB_SIZE . 'px)<br />(small-w: ' . SMALL_IMG_SIZE . 'px)<br />(medium-w: ' . MEDIUM_IMG_SIZE . 'px)',
										"attr"		=> 'data-ajax="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=thumbs&folder=' . $folder . '/' . $mediaFile . '" data-confirm="{s_title:genthumbs}?" data-menuitem="true" data-id="item-id-' . $i . '-2" data-menutitle="{s_title:genthumbs}"',
										"icon"		=> "thumbs"
									);
				
				$thumbsButton	=	parent::getButton($btnDefs);
			}
			
			
			// Falls Bilddatei und kein Thumb, Icons zum Drehen einfügen
			if(in_array($fileExt, Files::get('imgFileExts'))) {

				// Button rotate
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "rotateImage button-icon-only",
										"value"		=> "",
										"title"		=> '{s_title:rotateleft}',
										"attr"		=> 'data-file="' . $mediaFile . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=rotate&dir=left&file=' . $mediaFile . '&folder=' . $fileFolder . '" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:rotateleft}"',
										"icon"		=> "rotateleft"
									);
				
				$rotateButtons	=	parent::getButton($btnDefs);			

				// Button rotate
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> "rotateImage button-icon-only",
										"value"		=> "",
										"title"		=> '{s_title:rotateright}',
										"attr"		=> 'data-file="' . $mediaFile . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=rotate&dir=right&file=' . $mediaFile . '&folder=' . $fileFolder . '" data-menuitem="true" data-id="item-id-' . $i . '" data-menutitle="{s_title:rotateright}"',
										"icon"		=> "rotateright"
									);
				
				$rotateButtons	.=	parent::getButton($btnDefs);
			
			}
			
			// Delete Button
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> $this->type . 'Selection mediaSelection deleteElement button-icon-only',
									"value"		=> "",
									"title"		=> $mediaFile,
									"attr"		=> 'data-menutitle="{s_title:delete}" data-file="' . $mediaFile . '" data-path="' . $dataUrlPath . '" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=del&type=' . $this->type . '&file=' . $mediaFile . '&folder=' . $fileFolder . '" data-menuitem="true" data-id="item-id-' . $i . '"',
									"icon"		=> "delete"
								);
			
			$deleteButton	=	parent::getButton($btnDefs);
			
			
			// Fetch Button
			$editButtons .=	$fetchButton;
			// Button zum Umbenennen der Datei
			$editButtons .=	$renameButton;
			// Button zum Erstellen von Thumbs
			$editButtons .=	$thumbsButton;
			// Ggf. Rotate Buttons
			$editButtons .=	$rotateButtons;
			// Delete Button
			$editButtons .=	$deleteButton;
			
			
			// Falls Galerie-Edit
			if($action == "edit") {

				$imgID		=	$media['imgid'];
				$sortID		=	$media['imgsortid'];
				$showItem	=	$media['show'];
				$itemTitle	=	htmlspecialchars($media['title_' . $lang]);
				$itemLink	=	htmlspecialchars($media['link_' . $lang]);
				$itemTags	=	htmlspecialchars($media['img_tags']);
				$itemText	=	htmlentities($media['text_' . $lang], ENT_QUOTES, "UTF-8");
				
				$itemLink	=	str_replace("{#root}", PROJECT_HTTP_ROOT, $itemLink);
				$itemText	=	str_replace("{#root}", PROJECT_HTTP_ROOT, $itemText);
				
				$imgPath	=	PROJECT_DOC_ROOT . '/' . $folder . '/' . $mediaFile;
				$imgUrl		=	PROJECT_HTTP_ROOT . '/' . $folder . '/' . $mediaFile;
				$imgUrlBig	=	PROJECT_HTTP_ROOT . '/' . $fileFolder . '/' . $mediaFile;
				$imgPlaceh	=	SYSTEM_IMAGE_DIR . '/noimage.png';
				
				$hasVideo	=	false;

				if(!file_exists($imgPath))
					$imgUrl		= $imgPlaceh;
				else
					$imgUrl    .= $forceSrc;
				
				if(file_exists(PROJECT_DOC_ROOT . '/' . $fileFolder . '/' . pathinfo($imgPath, PATHINFO_FILENAME) . ".mp4"))
					$hasVideo	= true;

				$mediaList .=	'<li class="sortItem listItem listEntry' . ($showItem ? '' : ' hiddenImage') . '' . ($hasVideo ? ' hasVideo' : '') . '" data-sortid="' . $sortID . '" data-newsortid="' . $sortID . '" data-id="' . $imgID . '" data-toggleclass="hiddenImage">' . "\r\n" .
								'<div class="gallentry" data-menu="context" data-target="contextmenu-gall-' . $i . '">' . "\r\n";
								
				// Button panel
				$mediaList .=	'<span class="editButtons-panel" data-id="contextmenu-gall-' . $i . '">' . "\r\n";
				
				$mediaList .=	'<span class="switchIcons">' . "\r\n";
			
				// Button unpublish
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'publishItem button-icon-only',
										"value"		=> "",
										"title"		=> '{s_javascript:showpic}',
										"attr"		=> 'data-publish="1" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=hidegallimg&entryid=' . $imgID . '&pub=1" data-menutitle="{s_javascript:showpic}" data-menuitem="true" data-id="item-id-' . $i . '"' . ($showItem ? ' style="display:none;"' : ''),
										"icon"		=> "hide"
									);
				
				$mediaList .=	parent::getButton($btnDefs);
			
				// Button publish
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'publishItem button-icon-only',
										"value"		=> "",
										"title"		=> '{s_javascript:hidepic}',
										"attr"		=> 'data-publish="0" data-url="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=hidegallimg&entryid=' . $imgID . '&pub=0" data-menutitle="{s_javascript:hidepic}" data-menuitem="true" data-id="item-id-' . $i . '"' . (!$showItem ? ' style="display:none;"' : ''),
										"icon"		=> "show"
									);
				
				$mediaList .=	parent::getButton($btnDefs);
			
				$mediaList .=	'</span>' . "\r\n";
				
			
				// Button edit
				$btnDefs	= array(	"type"		=> "button",
										"class"		=> 'editButtons editgall button-icon-only',
										"value"		=> "",
										"title"		=> '{s_header:galldetails}',
										"attr"		=> 'data-menutitle="{s_header:galldetails}" data-menuitem="true" data-id="item-id-' . $i . '"',
										"icon"		=> "editdetails"
									);
				
				$mediaList .=	parent::getButton($btnDefs);
								
				$mediaList .=	$editButtons;
				
				$mediaList .=	'</span>' . "\r\n";
				
				$mediaList .=	'<div class="previewBox">' . "\r\n" .
								'<img src="' . $imgUrl . '" data-img-src="' . $imgUrlBig . '" data-src="' . $imgUrl . '" title="' . $mediaFile . '" class="preview noclick" alt="' . $mediaFile . '" data-file="' . $mediaFile . '" data-path="' . $dataUrlPath . '" />' . "\r\n" .
								'<label for="selImg-' . $i . '" title="{s_javascript:showpic}" class="markImage markBox"><input name="img_gall['.$i.']" type="checkbox" id="selImg-' . $i . '" />' . "\r\n" .
								'</label>' . "\r\n" .
								'</div>' . "\r\n" . 
								'<div class="galleryItemCaptionBox" id="itemDetails-' . $i . '">' . "\r\n" .
								'<div class="boxHeader move ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix" style="display:none;">' . "\r\n" .
								'<span class="move ui-dialog-title">{s_header:galldetails}</span>' . "\r\n" .
								'</div>' . "\r\n" . 
								'<div class="galleryItemCaptionBox-content">' . "\r\n" .
								'<div class="galleryItemCaptionBox-title">' . "\r\n" .
								'<div class="previewBox right" style="display:none;">' . "\r\n" .
								'<img src="' . $imgUrl . '" data-src="' . $imgUrl . '" data-img-src="' . $imgUrlBig . '" alt="' . $mediaFile . '" class="galleryItem-image preview" />' . "\r\n" . 
								'</div>' . "\r\n" . 
								'<input name="img_name[]" type="hidden" value="' . $mediaFile . '" />' . "\r\n" . 
								'<label>{s_label:imageheader}</label><input type="text" name="img_title[]" value="' . $itemTitle . '" />' . "\r\n" . 
								'<label>{s_label:imagelink}</label><input type="text" name="img_link[]" value="' . $itemLink . '" />' . "\r\n" . 
								'<span class="inline-tags">' . "\r\n" .
								'<label>{s_label:tags}</label><input type="text" name="img_tags[]" id="img_tags-' . $i . '" class="cc-image-tags" value="' . $itemTags . '" />' . "\r\n" .
								$this->getTagEditorScriptTag('img_tags-' . $i) .
								'</span>' . "\r\n" . 
								'<br class="clearfloat" />' . "\r\n" . 
								'</div>' . "\r\n" . 
								'<div class="galleryItemCaptionBox-text">' . "\r\n" .
								'<label>{s_label:imagetext}<span class="toggleEditor" data-target="gallImgText-' . $i . '">Editor</span></label>' . "\r\n" .
								'<textarea name="img_text[]" id="gallImgText-' . $i . '" class="cc-editor-add galleryEditor textEditor disableEditor" rows="2">' . $itemText . '</textarea>' . "\r\n" .
								'</div>' . "\r\n" . 
								'</div>' . "\r\n" . 
								'</div>' . "\r\n" . 
								'</div>' . "\r\n" .
								'</li>' . "\r\n";
								
				$i++;
				continue;
			}
	
			
			
			// Falls ein Unterordner von einem files Ordner angezeigt wird, Button zum eine Ebene höher gehen anzeigen
			if(strpos($folder, CC_FILES_FOLDER) === 0
			&& $folder != CC_FILES_FOLDER 
			&& $i == 1
			) {				
				$pathElements	= explode("/", rtrim($subPath, "/"));
				$current		= array_pop($pathElements);
				
				
				// Button folderup
				$btnDefs	= array(	"href"		=> implode("/", $pathElements),
										"class"		=> 'folderUp button-icon-only',
										"title"		=> '{s_title:folderup}' . implode("/", $pathElements),
										"text"		=> '&nbsp;',
										"icon"		=> "folderup"
									);
					
				$mediaList .=	parent::getButtonLink($btnDefs);
				
				$mediaList .=	'<span class="currentFolderPath">';
				
				$mediaList .=	parent::getIcon("folderopen");
				
				$mediaList .=	implode(" &rsaquo; ", $pathElements).
								'<span class="currentFolder"> &rsaquo; ' . $current . '</span>' .
								'</span>' . "\r\n" .
								'<br class="clearfloat" />' . "\r\n";
			}
			
			
			// Medien list item
			$mediaList .=	'<li class="listItem ' . $this->type . 'List ' . (is_numeric($mediaFile[0]) ? '0-9' : strtoupper($mediaFile[0])) . ' date-' . $this->mediaDates[($i - 1)] . '" data-menu="context" data-target="contextmenu-' . $i . '">' . "\r\n";
			
			// Checkbox für Mehrfachauswahl
			$mediaList .=	$markBox;
			
			$mediaList .=	'<span class="editButtons-panel" data-id="contextmenu-' . $i . '">' . "\r\n";
			
			// Edit Buttons
			$mediaList .=	$editButtons;
					
			$mediaList .=	'</span>' . "\r\n";
			
		
			// Falls Bilddatei, Preview einfügen
			if(in_array($fileExt, Files::get('imgFileExts'))) {
				
				$imgUrl		= PROJECT_HTTP_ROOT . '/' . $folder . '/' . $mediaFile;
				$imgPath	= PROJECT_DOC_ROOT . '/' . $folder . '/' . $mediaFile;
				$thumbPath	= PROJECT_DOC_ROOT . '/' . $thumbFolder . '/' . $mediaFile;
				
				// Falls Theme-Bilderordner
				if($this->type == "theme"
				|| !file_exists($thumbPath))
					$imgUrlBig = $imgUrl;
				// Falls Standard-Bilderordner
				else {
					$imgUrl		= PROJECT_HTTP_ROOT . '/' . $thumbFolder . '/' . $mediaFile;
					$imgUrlBig	= PROJECT_HTTP_ROOT . '/' . $fileFolder . '/' . $mediaFile;
				}
					
				if(!file_exists($imgPath))
					$imgUrl		= SYSTEM_IMAGE_DIR . '/noimage.png';
				else
					$imgUrl    .= $forceSrc;
				
				$mediaList .=	'<div class="previewBox"><img src="' . $imgUrl . '?' . time() . '" title="' . $mediaFile . '"';
				
				$mediaList .=	' data-img-src="' . $imgUrlBig . '" src="' . PROJECT_HTTP_ROOT . '/' . $folder . '/' . $mediaFile . '?' . time() . '" title="' . $mediaFile . '" alt="' . $subPath . $mediaFile . '" data-file="' . $subPath . $mediaFile . '" data-path="' . $dataUrlPath . '" class="preview" />' .
				'</div>' . "\r\n";
			}
			
			
			// Falls Ordner	
			if($this->mediaTypes[$i-1])
				$mediaList .=	'<a href="' . str_replace(CC_FILES_FOLDER . "/", "", $folder . '/' . $mediaFile) . '" class="openFolder">';
			// Previewlink
			else
				$mediaList .=	'<a class="fileLink" title="' . $mediaFile . '" href="' . $previewLink . '" target="_blank">' . "\r\n";
		
		
			$mediaList .=	'<span class="listIcon">' . "\r\n" .
							$fileIcon .'</span>' . "\r\n" .
							'<span class="fileName" data-name="' . $mediaFile . '">' . "\r\n" .
							$mediaFile . 
							'</span>' . "\r\n" .
							(!$this->mediaTypes[$i-1] ? $fileSize : '') .
							'</a>' . "\r\n" .
							$mediaObj . // Audio-Objekt
							'<br class="clearfloat" /></li>' . "\r\n";
						
			
			$i++; // Counter-Inkrement
			
		} // Ende foreach
		
		
		// Schließen-Tags Bildergalerie
		if(strpos($folder, CC_GALLERY_FOLDER) === 0) {
			
			if($action == "edit") {
			
				$mediaList .=	'<br class="clearfloat" />' .
								'</ul>' . "\r\n" .
								'</div>' . "\r\n" . // Schließen der listItemBox
								'<ul>' . "\r\n" .
								'<li class="submit change buttonpanel-nofix" style="display:none;">' . "\r\n";
		
				// Links MediaList-Button
				$mediaListButtonDef		= array(	"class"	 	=> "changeGalleryItems-submit change",
													"type"		=> "submit",
													"value"		=> "{s_button:savechanges}",
													"icon"		=> "ok"
												);
				
				$mediaList .=	parent::getButton($mediaListButtonDef);
				
				$mediaList .=	$this->getChangeGalleryItemsScript();
								
				$mediaList .=	'<input name="changeGalleryItems" type="hidden" value="true" />' . PHP_EOL .
								'</li>' . PHP_EOL .
								'</ul>' . PHP_EOL;
			
				$mediaList .=	'<input name="gall_name" class="afd " type="hidden" value="' . $this->gallFolder . '" />' . PHP_EOL;
				
				$mediaList .=	'</form>' . PHP_EOL;
			}
			else
				$mediaList .=	'<br class="clearfloat" />' .
								'</ul>' . "\r\n" .
								'</div>' . "\r\n"; // Schließen der listItemBox
		}
		else
			$mediaList .=	'</ul>' . "\r\n" .
							'</div>' . "\r\n"; // Schließen der listItemBox


		// Contextmenü-Script
		$mediaList .=	$this->getContextMenuScript();

		
		#var_dump($mediaList);
		return parent::replaceStaText($mediaList);


	} // Ende listMedia



	// getUploadBox
	public static function getUploadBox($type, $folder = "", $uploadMethod = "auto", $DB = null, $o_lng = null)
	{

		require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.FileUploaderFactory.php"; // FileUploader-Factory einbinden
		
		$uploadMethod			= $uploadMethod != "uploadify" ? "plupload" : $uploadMethod;
		$allowedFileSizeStr		= Files::getFileSizeStr(Files::getAllowedFileSize());
		$allowedFiles			= Files::getAllowedFiles($type == "gallery" ? "gallery" : "all");
		$options				= array();
		$output					= "";
		
		$options["allowedFiles"]		= $allowedFiles;
		$options["allowedFileSizeStr"]	= $allowedFileSizeStr;
		
		
		if($type == "files"
		&& $folder != ""
		) {
			$options["folder"]			= $folder;
			$options["useFilesFolder"]	= true;
		}

		
		// FileUploader
		try {
			
			// FileUploader-Instanz
			$o_uploader		= FileUploaderFactory::create($uploadMethod, $options, $DB, $o_lng);
			$uploadMask		= $o_uploader->getUploaderMask("listBox", $type);		
			$uploadScript	= $o_uploader->getUploadScript("#myUploadBox-lb", $type);		
			$output			= $uploadMask . $uploadScript;
		}
		// Falls Element-Klasse nicht vorhanden
		catch(\Exception $e) {
			$output = $e->getMessage();
		}
		
		return $output;
	
	}



	/**
	 * Methode zur Erstellung eines Uploadformularteils für die ListBox
	 * 
	 * @param	string	$type			Type (default = '')
	 * @param	string	$newFolder		falls true, Label für "neuen Ordner anlegen" ergänzen (default = false)
	 * @param	string	$wrapForm		falls true, Uploader in ein Formular einbinden (default = false)
	 * @param	string	$folder			Folder (default = '')
	 * @param	string	$uploadMethod	Folder (default = 'auto')
	 * @access	public
	 * @return	string
	 */
	public static function getListBoxUploadMask($type = "", $newFolder = false, $wrapForm = false, $folder = "", $uploadMethod = "auto", $DB = null, $o_lng = null)
	{
		
		$allowedFileSizeStr	= Files::getFileSizeStr(Files::getAllowedFileSize());
		$allowedFiles		= Files::getAllowedFiles();
		$gallName			= "";
		$formAction			= "";
		$output				= "";
		
		if(isset($GLOBALS['_GET']['gal'])) {
			$gallName		= htmlspecialchars($GLOBALS['_GET']['gal']);
			$formAction		= ADMIN_HTTP_ROOT . '?task=modules&type=gallery&gal=' . $gallName;
		}
		
		if($wrapForm)
			$output	.=	'<form action="' . $formAction. '" method="post" name="uploadmethodfm" class="' . $uploadMethod . '-uploader' . ($gallName != "" ? ' gallery-form' : '') . '" data-submit="false" data-type="listbox">' . "\r\n";

		
		$output	.=	'<div class="actionBox">' . "\r\n";
		
		
		// Button filemanager
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> "showListBox keepListBox openFilemanager button-icon-only button-small",
								"title"		=> '{s_label:filemanager}',
								"attr"		=> 'data-url="' . PROJECT_HTTP_ROOT . '/system/access/listMedia.php?page=admin&action=elfinder&root=' . ($type == "gallery" ? 'gallery,images&gall=' . $gallName : 'all') . '" data-type="filemanager"',
								"icon"		=> 'filemanager',
								"iconclass"	=> "openList filemanager"
							);
		
		$output	.=	parent::getButton($btnDefs);
		
		$output	.=	'<label class="iconBox">' . parent::getIcon("upload", "toggleUploadBox", 'title="{s_text:uploadfile}"') . '</label>' . "\r\n" .
					'<label class="toggleUploadBox" for="">{s_text:uploadfile}' . ($newFolder ? ' / {s_label:newfolder}' : '') . '</label>' . "\r\n" .
					'</div>' . "\r\n" .
					'<div class="uploadBox" style="display:none;">' . "\r\n";
		
		
		$output .=	self::getUploadBox($type, $folder, $uploadMethod, $DB, $o_lng);

		
		$output	.=	'<input type="hidden" name="maxFileSize" value="' . str_replace(" ", "", strtolower($allowedFileSizeStr)) . '" />' . "\r\n" .
					'<input type="hidden" name="allowedFileTypes" value="' . implode(",", $allowedFiles) . '" />' . "\r\n";
			
		
		// Falls Galerie, Namen mitgeben
		if($gallName != "")
			$output	.=	'<input type="hidden" name="gall_name" value="' . $gallName . '" />' . "\r\n";

		
		// Falls Liste des files-Ordners erstellt werden soll, Button zum Anlegen eines neuen Ordners einfügen
		if($newFolder)
			$output .=	self::getNewFolderMask();

		
		$output	.=	'</div>' . "\r\n";
		
		if($wrapForm)
			$output	.=	'</form>' . "\r\n";
		
		return $output;
	}



	/**
	 * Methode zur Erstellung eines Formularteils zur Ordnererstellung
	 * 
	 * @access	public
	 * @return	string
	 */
	public static function getNewFolderMask()
	{
	
		// Falls Liste des files-Ordners erstellt werden soll, Button zum Anlegen eines neuen Ordners einfügen
		$output	=	'<div class="newFolderBox">' . "\r\n" .
					'<label>{s_label:newfolder}</label>' . "\r\n" .
					'<span class="singleInput-panel">' . "\r\n" .
					'<input type="text" class="newFolderName input-button-right" name="newFolderName" />' . "\r\n" .
					'<span class="editButtons-panel">' . "\r\n";
		
		// Button new
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'newFolder button-icon-only',
								"value"		=> "",
								"title"		=> '{s_label:newfolder}',
								"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&type=files&action=newfolder"',
								"icon"		=> "folder-plus"
							);
		
		$output .=	parent::getButton($btnDefs);
		
		$output .=	'</span>' . "\r\n" .
					'</span>' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n" .
					'</div>' . "\r\n";
		
		return $output;
		
	}
		



	/**
	 * Methode zur Erstellung einer Dialogbox mit Formular zum Ändern eines Datei/Galerienamens
	 * 
	 * @param	string	$formAction		Formular script
	 * @param	string	$checkUpdateDB	falls true, DB update checkbox obligatorisch an
	 * @access	public
	 * @return	string
	 */
	public function getRenameDialogForm($formAction, $checkUpdateDB)
	{

		$dialogForm = 	'<div id="dialog-form-file" class="file" style="display:none;" title="{s_title:changefilename}">' . "\r\n" .
						'<div class="adminStyle adminArea">' . "\r\n" .
						'<form action="' . $formAction . '" method="post" class="form">' . "\r\n" . 
						'<label for="newname-file">{s_label:filename}</label>' . "\r\n" .
						'<p class="notice validateTips"></p>' . "\r\n" .
						'<input type="text" name="newname-file" id="newname-file" class="dialogName dialogInput text ui-widget-content ui-corner-all" value="" maxlength="64" />' . "\r\n" .
						'<p>&nbsp;</p>' . "\r\n" .
						'<label for="dbUpdate-file">' . "\r\n" .
						parent::getIcon("info", "tooltipHint right", 'title="' . parent::replaceStaText('{s_hint:renamefile}') . '"') .
						'<input type="checkbox" name="dbUpdate-file" id="dbUpdate-file" class="dbUpdate"' . ($checkUpdateDB ? ' checked="checked" disabled="disabled"' : '') . ' /> {s_label:dbupdate}</label>' . "\r\n" .
						'<input type="hidden" name="oldname-file" id="oldname-file" class="dialogName dialogInput" value="" />' . "\r\n" .
						'<input type="hidden" name="foldername-file" id="foldername-file" class="dialogFolder copyID" value="" />' . "\r\n" .
						'<input type="hidden" name="scriptpath-file" id="scriptpath-file" value="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=rename&type=file&folder="  />' . "\r\n" .
						'<input type="hidden" name="phrases-file" id="phrases-file" value="' . parent::replaceStaText("{s_notice:nofilename}<>{s_notice:longname}<>{s_notice:wrongname}<>{s_notice:fileexists}<>{s_notice:error}") . '"  />' . "\r\n" .
						'<input type="hidden" name="buttonLabels-file" id="buttonLabels-file" value="' . parent::replaceStaText("{s_button:takechange}<>{s_button:cancel}") . '"  />' . "\r\n" .
						'</form>' . "\r\n" .
						'</div>' . "\r\n" .
						'</div>' . "\r\n";					
	
		return $dialogForm;

	}	



	/**
	 * Galeriedaten aus DB auslesen
	 * 
	 * @param	string	$gallFolder		Galeriename
	 * @access	public
	 * @return	string
	 */
	public function getGalleryDataFromDB($gallFolder)
	{

		$queryGall = $this->DB->query("SELECT *, img.`id` as imgid, img.`sort_id` as imgsortid
											FROM `" . DB_TABLE_PREFIX . "galleries_images` as img 
											LEFT JOIN `" . DB_TABLE_PREFIX . "galleries` as gall 
												ON gall.`id` = img.`gallery_id` 
											WHERE gall.`gallery_name` = '" . $this->DB->escapeString($gallFolder) . "' 
											ORDER BY img.`sort_id` ASC
											");

		#var_dump($queryGall);
		
		return $queryGall;
	
	}		



	/**
	 * Button zum Reparieren einer Galerie
	 * 
	 * @param	string	$gallFolder		Galeriename
	 * @access	public
	 * @return	string
	 */
	public function getRepairGalleryButton($gallFolder)
	{
	
		$output	=	'<div class="actionBox">' . "\r\n" .
					'<label class="iconBox" title="{s_notice:repairgall}">' . "\r\n" .
					parent::getIcon("warning") .
					'</label>' . "\r\n" .
					'<label class="repair">{s_notice:repairgall}</label>' . "\r\n" .
					'<span class="editButtons-panel">' . "\r\n";
		
		// Button repair
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'repairGallery button-icon-only',
								"value"		=> "",
								"title"		=> '{s_title:repairgall}',
								"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editGalleries.php?page=admin&action=repair&type=gallery&gal=' . $gallFolder . '"',
								"icon"		=> "repair"
							);
		
		$output .=	parent::getButton($btnDefs);
					
		$output .=	'</span>' . "\r\n" .
					'<br class="clearfloat" /></div>' . "\r\n";

		return $output;
	
	}



	/**
	 * Galerie edit form einbinden
	 * 
	 * @param	string	$gallFolder		Galeriename
	 * @param	string	$gallCount		Galerienr.
	 * @param	string	$repairGallery	Button: Galerie reparieren
	 * @access	public
	 * @return	string
	 */
	public function getGalleryEditForm($gallFolder, $gallCount, $repairGallery)
	{
	
		$output	=	'<h2 class="gallCount cc-section-heading cc-h2">{s_label:gallery} &#9658; ' . htmlspecialchars($gallFolder) . '</h2>' . "\r\n" .
					'<form action="' . ADMIN_HTTP_ROOT . '?task=modules&type=gallery&gal=' . $gallFolder . '" method="post" name="galleryfm" class="gallery-form" data-history="false">' . "\r\n" .
					'<div class="controlBar">' . "\r\n";
		
		$output	.=	$repairGallery;
		
		$output	.=	'<div class="actionBox">' . "\r\n" .
					'<label class="iconBox">' . "\r\n" .
					parent::getIcon("togglegrid", "toggleGallView", 'title="{s_title:togglegallview}"') .
					'</label>' . "\r\n" .
					'<label class="gallCount"><strong>' . $gallCount . '</strong> {s_text:gallcount}</label>' . "\r\n" .
					'<br class="clearfloat" /></div>' . "\r\n";
					
		$output	.=	self::getListBoxUploadMask("gallery", false, false, "", $this->uploadMethod, $this->DB, $this->o_lng); // File-Upload-Maske einfügen
		
		$output	.=	'<div class="actionBox">' .
					'<label class="markAll markBox"><input type="checkbox" id="markAllLB" data-select="all" /></label>' .
					'<label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' . "\r\n" .
					'<span class="editButtons-panel">' . "\r\n";
		
		// Button publish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll pubFiles galleryFiles button-icon-only',
								"text"		=> "",
								"title"		=> '{s_javascript:showpic}',
								"icon"		=> "show"
							);
		
		$output .=	parent::getButton($btnDefs);
		
		// Button unpublish
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'pubAll pubFiles galleryFiles unpublish button-icon-only',
								"text"		=> "",
								"title"		=> '{s_javascript:hidepic}',
								"icon"		=> "hide"
							);
		
		$output .=	parent::getButton($btnDefs);
		
		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'delAll delFiles galleryFiles button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:delmarked}',
								"attr"		=> 'data-action="delmultiplefiles"',
								"icon"		=> "delete"
							);
		
		$output .=	parent::getButton($btnDefs);
					
		$output .=	'</div>' . "\r\n";
		
		$output	.=	'</div>' . "\r\n";
		
		if($gallCount)
			$output	.=	'<div class="listItemBox gallery" data-action="edit">' . "\r\n" .
						'<ul id="sortableGallery" class="sortable-container gallViewImage" data-url="' . PROJECT_HTTP_ROOT . '/system/access/listMedia.php?page=admin&action=sort&type=gallery&gal=' . $gallFolder . '">' . "\r\n";
		
		return $output;
	
	}



	/**
	 * Medienordner auslesen
	 * 
	 * @param	string	$folder		Ordnername
	 * @param	string	$action		Art der Medienauflistung
	 * @access	public
	 * @return	string
	 */
	public function readMediaFolder($folder, $action)
	{
	
		$handle = opendir(PROJECT_DOC_ROOT . '/' . $folder);
	
		while($content = readdir($handle)) {
		
			if(	$content != ".." && 
				strpos($content, ".") !== 0 && 
				$content != "smilies" && 
				($action != "listfolders" || is_dir(PROJECT_DOC_ROOT . '/' . $folder . '/' . $content))
			) {
				$filedate = filemtime(PROJECT_DOC_ROOT . '/' . $folder . '/' . $content);
				
				// Falls Unterordner, an den Anfang des Arrays einfügen
				if(is_dir(PROJECT_DOC_ROOT . '/' . $folder . '/' . $content)) {
					array_unshift($this->mediaDates, $filedate);
					array_unshift($this->mediaTypes, is_dir(PROJECT_DOC_ROOT . '/' . $folder . '/' . $content));
					array_unshift($this->existMedia, $content);
				}
				else { // ...sonst am Ende
					$this->mediaDates[] = $filedate;
					$this->mediaTypes[] = is_dir(PROJECT_DOC_ROOT . '/' . $folder . '/' . $content);
					$this->existMedia[] = $content;
				}
			}
		}
		closedir($handle);
		
	}



	/**
	 * Medienordner auslesen
	 * 
	 * @param	string	$folder		Ordnername
	 * @param	string	$action		Art der Medienauflistung
	 * @param	string	$dialogForm	Dialog form
	 * @access	public
	 * @return	string
	 */
	public function getGalleryListHead($folder, $action, $dialogForm)
	{
	
		// Dialog-Formular einbinden
		$output		   		= $dialogForm;	
		$gallCount			= count($this->existMedia);
		$gallScanFolder		= PROJECT_DOC_ROOT . '/' . $folder;
		$actualFileCount	= is_dir($gallScanFolder) ? max(0, count(array_diff(scandir($gallScanFolder), array('..', '.', '.quarantine', '.tmb')))) : 0; // Bildanzahl = Dateianzahl ohne ./, ../, quarantine/, tmb/ ...
		$repairGallery		= "";
		
		// Falls die Zahl an Galeriebildern aus DB und Ordner nicht übereinstimmen, Meldung zum reparieren ausgeben
		if($gallCount != $actualFileCount)				
			$repairGallery	= $this->getRepairGalleryButton($this->gallFolder);
			
		
		// Falls Galerie bearbeitet werden soll
		if($action == "edit") {
		
			// Edit form einbinden
			$output .=	$this->getGalleryEditForm($this->gallFolder, $gallCount, $repairGallery);
			
		}
		elseif($gallCount) {
			$output .=	'<div class="listItemBox ' . $this->type . '" data-action="' . $action . '">' . "\r\n" .
						'<ul class="editList">' . "\r\n";
		}
				
		return $output;
	
	}



	/**
	 * getTagEditorScriptTag
	 * 
	 * @param	string	$tag		Tag id
	 * @access	public
	 * @return	string
	 */
	public function getTagEditorScriptTag($tag)
	{
	
		return	'<script>' . "\r\n" .
				'head.ready(function(){' . "\r\n" .
				'head.load({tagEditorcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.css"});' . "\r\n" .
				'head.load({tagEditorcaret: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.caret.min.js"});' . "\r\n" .
				'head.load({tagEditor: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.js"});' . "\r\n" .
				'head.ready("tagEditor", function(){' . "\r\n" .
					'$("document").ready(function(){' . "\r\n" .
						'$("#' . $tag . '").tagEditor({' . "\r\n" .
							'maxLength: 2048,' . "\r\n" .
							'forceLowercase: false,' . "\r\n" .
							'delimiter: ", ;\n",' . "\r\n" .
							'onChange: function(field, editor, tags){' . "\r\n" .
							'}' . "\r\n" .
						'});' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}



	/**
	 * getChangeGalleryItemsScript
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getChangeGalleryItemsScript()
	{
	
		return	'<script>' . "\r\n" .
				'head.ready(function(){' . "\r\n" .
					'$("document").ready(function(){
						$("body").off("click", ".changeGalleryItems-submit");
						$("body").on("click", ".changeGalleryItems-submit", function(e){
							e.preventDefault();
							e.stopPropagation();
							var parForm		= $(this).closest("form");
							var mediaList	= parForm.closest("div.mediaList");
							var iListBox	= mediaList.find(".innerListBox");
							parForm.attr("action", parForm.attr("action") + "&json=true");
							parForm.closest(".cc-gallery-item-details").fadeOut(200, function(){ $(this).remove(); });
							iListBox.fadeTo(300,.5);
							$.submitViaAjax(parForm, false, "html", false, function(ajax){
								iListBox.html(ajax);
								$.setListView(mediaList);
								$.sortableGallery(mediaList.find("#sortableGallery"));
								iListBox.fadeTo(200,1);
								$.removeWaitBar();
							});
						})' . "\r\n" .
					'});' . "\r\n" .
				'});' . "\r\n" .
				'</script>' . "\r\n";
	
	}

}
