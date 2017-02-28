<?php
namespace Concise;


/**
 * Klasse für Fileupload
 *
 */

class Files
{
	
	private static $uploadMethods	= array("auto",
											"default",
											"plupload",
											"uploadify"
											);
	 	
	protected static $allowedFileTypes	= array("jpg","png","gif","jpeg","swf","flv","f4v","mp4","m4v","wmf","mpeg","mpg","avi","ra","ram","mov","qt","ogv","webm","mp3","ogg","oga","pdf","odt","doc","docx","zip","txt","bmp","ico","svg","svgz");
	protected static $imgFileExts		= array("jpg", "png", "gif", "jpeg", "bmp", "ico", "svg", "svgz");
	protected static $galleryFileExts	= array("jpg", "png", "gif", "jpeg", "bmp", "ico", "svg", "svgz", "mp4", "webm", "ogv");
	protected static $docFileExts		= array("pdf","odt","doc","docx","zip","txt");
	protected static $audioFileExts		= array("mp3","ogg","oga");
	protected static $videoFileExts		= array("wmf","mpeg","mpg","avi","mp4","m4v","ra","ram","mov","qt","ogv","ogg","webm","swf","flv","f4v");
	protected static $mimeTypes			= array("jpg"	=> "image/jpeg",
												"png"	=> "image/png",
												"gif"	=> "image/gif",
												"jpeg"	=> "image/jpeg",
												"flv"	=> "video/x-flv",
												"f4v"	=> "video/mp4",
												"m4v"	=> "video/x-m4v",
												"mp4"	=> "video/mp4",
												"wmf"	=> "application/x-msmetafile",
												"mpeg"	=> "video/mpeg",
												"mpg"	=> "video/mpeg",
												"avi"	=> "video/x-msvideo",
												"ra"	=> "audio/x-pn-realaudio",
												"ram"	=> "audio/x-pn-realaudio",
												"mov"	=> "video/quicktime",
												"qt"	=> "video/quicktime",
												"ogv"	=> "video/ogg",
												"webm"	=> "video/webm",
												"mp3"	=> "audio/mpeg",
												"ogg"	=> "application/ogg",
												"oga"	=> "audio/ogg",
												"pdf"	=> "application/pdf",
												"odt"	=> "application/vnd.oasis.opendocument.text ",
												"doc"	=> "application/msword",
												"docx"	=> "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
												"zip"	=> "application/x-zip-compressed",
												"txt"	=> "text/plain",
												"bmp"	=> "image/bmp",
												"ico"	=> "image/x-icon",
												"svg"	=> "image/svg+xml",
												"svgz"	=> "image/svg+xml",
												"swf"	=> "application/x-shockwave-flash"
												);


	/**
	 * Getter
	 *
	 * @access	public
     * @return  string
	 */
	public static function get($property)
	{

		if (property_exists(__CLASS__, $property)) {
			return self::$$property;
		}
	}

	
	/**
	 * Gibt mögliche Upload Methoden zurück
	 * 
	 * @access	public
	 * @return	string
	 */
	public static function getPotUploadMethods()
	{
		
		return self::$uploadMethods;
		
	}
	

	/**
	 * Bestimmen des zu verwendenden Upload Moduls
	 * 
	 * @access	public
	 * @return	string
	 */
	public static function getUploadMethod()
	{
		
		// Falls Browser mit multiple-Unterstützung und FILE_UPLOAD_METHOD auf default bzw. auto mit geeignetem Browser
		switch(FILE_UPLOAD_METHOD) {
		
			case "uploadify":
				return "uploadify";
			case "default":
				return "default";
			default:
				return "plupload";
		}
		
	}


	/**
	 * Methode zur Umschreibung von Dateinamen
	 * 
	 * @param	string	Dateiname
	 * @param	boolean	Falls true, werden auch Punkte ersetzt
	 * @access	public
	 * @return	string
	 */
	public static function getValidFileName($fileName, $replaceDot = false)
	{
		
	    $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
	    $fileName = str_replace($special_chars, '', $fileName);
		$search = array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß', ' ', '\'', '(', ')', '[', ']', '@', 'é', 'è', 'ë', 'í', 'ì', 'á', 'à', 'â', 'ú', 'ù', 'û', 'c¸', 'ñ');
		$replace = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss', '-', '-', '-', '-', '-', '-', '-', 'e', 'e', 'e', 'i', 'i', 'a', 'a', 'a', 'u', 'u', 'u', 'c', 'n');
		$fileName = str_replace($search, $replace, $fileName); // Ersetzen der Umlaute und Zeichen
	    $fileName = preg_replace('/[\s-]+/', '-', $fileName);
		$fileName = preg_replace('/[^0-9a-z\.\_\-]/i','',$fileName); // Ersetzen evtl. verbliebender unerlaubter Zeichen
	    $fileName = trim($fileName, '.-_');
		
		// Falls Punkte ersetzt werden sollen
		if($replaceDot)
			$fileName = str_replace('.', '-', $fileName); // Ersetzen evtl. vorhandener Punkte
		
		return $fileName;
		
	}


	/**
	 * Methode zur Bestimmung des Dateinamens (ohne Erweiterung)
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	string
	 */
	public static function getFileBasename($fileName)
	{
		
		return substr($fileName, 0, strrpos($fileName,'.'));
		
	}


	/**
	 * Methode zur Bestimmung der Dateinamenerweiterung
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	string
	 */
	public static function getFileExt($fileName)
	{
		
		return substr($fileName, strrpos($fileName,'.')+1, strlen($fileName)-1);
		
	}


	/**
	 * Gibt Mime-Type einer Datei zurück
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	string
	 */
	public static function getMimeType($fileName)
	{
		
		if(function_exists('finfo_file')) {
			$finfo	= finfo_open(FILEINFO_MIME_TYPE);
			$type	= finfo_file($finfo, $fileName);
			return finfo_close($finfo);
		}
		
		$fileExt	= self::getFileExt($fileName);
		if(in_array($fileExt, self::$mimeTypes))
			return self::$mimeTypes[$fileExt];
		
		return 'application/octet-stream';
		
	}


	/**
	 * Gibt den Typ einer Datei zurück
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	string
	 */
	public static function getFileType($fileName)
	{
		
		if(self::isImageFile($fileName))
			return "image";
		if(self::isDocFile($fileName))
			return "doc";
		if(self::isAudioFile($fileName))
			return "audio";
		if(self::isVideoFile($fileName))
			return "video";
		
		return "other";
		
	}


	/**
	 * Überprüfung des Mime-Types
	 *
     * @param	string	$mimeType	mime type
	 * @access	public
     * @return  string
	 */
	public static function getFileCatByMimeType($mimeType)
	{
		
		$key = array_search($mimeType, self::$mimeTypes);
		
		if($key === false)
			return "unknown";
		// Falls Image
		if(in_array($key, self::$imgFileExts))
			return "image";
		// Falls Doc
		if(in_array($key, self::$docFileExts))
			return "doc";
		// Falls Audio
		if(in_array($key, self::$audioFileExts))
			return "audio";
		// Falls Video
		if(in_array($key, self::$videoFileExts))
			return "video";
	
	}


	/**
	 * Methode zur Überprüfung auf (erlaubte) Bilddatei
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	boolean
	 */
	public static function isImageFile($fileName)
	{
		
		$fileExt	= strtolower(self::getFileExt($fileName));
		
		return in_array($fileExt, self::$imgFileExts);
		
	}


	/**
	 * Methode zur Überprüfung auf (erlaubte) Dokumentdatei
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	boolean
	 */
	public static function isDocFile($fileName)
	{
		
		$fileExt	= strtolower(self::getFileExt($fileName));
		
		return in_array($fileExt, self::$docFileExts);
		
	}


	/**
	 * Methode zur Überprüfung auf (erlaubte) Videodatei
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	boolean
	 */
	public static function isVideoFile($fileName)
	{
		
		$fileExt	= strtolower(self::getFileExt($fileName));
		
		return in_array($fileExt, self::$videoFileExts);
		
	}


	/**
	 * Methode zur Überprüfung auf (erlaubte) Audiodatei
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	boolean
	 */
	public static function isAudioFile($fileName)
	{
		
		$fileExt	= strtolower(self::getFileExt($fileName));
		
		return in_array($fileExt, self::$audioFileExts);
		
	}


	/**
	 * Methode zur Überprüfung auf erlaubte Dateitypen
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	boolean
	 */
	public static function isAllowedFile($fileName)
	{
		
		$fileExt	= strtolower(self::getFileExt($fileName));
		
		return in_array($fileExt, self::$allowedFileTypes);
		
	}


	/**
	 * Gibt ein Array mit erlaubten Dateitypen zurück
	 * 
	 * @param	string Dateiname
	 * @access	public
	 * @return	boolean
	 */
	public static function getAllowedFiles($type = "all")
	{
	
		if($type == "image")
			return self::$imgFileExts;
	
		if($type == "gallery")
			return self::$galleryFileExts;
	
		return self::$allowedFileTypes;
		
	}


	/**
	 * Gibt den Typ einer Datei bzw. einen Medientyp zurück
	 * 
	 * @param	string Dateiname/Medientyp
	 * @access	public
	 * @return	string
	 */
	public static function getDefaultFolder($fileName)
	{
		
		if($fileName == "files")
			return CC_FILES_FOLDER;
		if($fileName == "galleries")
			return CC_GALLERY_FOLDER;
		if(self::isImageFile($fileName))
			return CC_IMAGE_FOLDER;
		if(self::isDocFile($fileName))
			return CC_DOC_FOLDER;
		if(self::isAudioFile($fileName))
			return CC_AUDIO_FOLDER;
		if(self::isVideoFile($fileName))
			return CC_VIDEO_FOLDER;
		
		return false;
		
	}


	/**
	 * Überprüfung auf maximal erlaubte Dateigröße
	 * 
	 * @param	string $ownSize Eigene Maximale Dateigröße
	 * @access	public
	 * @return	boolean
	 */
	public static function getAllowedFileSize($ownSize = 104857600)
	{
		
		$maxSize = min((int)ini_get('upload_max_filesize'), (int)ini_get('post_max_size'), (int)$ownSize);

		return ($maxSize * 1048576);
		
	}


	/**
	 * Gibt die Größe einer Datei zurück
	 * 
     * @param	string	Datei
     * @param	string	Ordner
     * @param	boolean falls true, überprüfen auf Vorhandensein der Datei
	 * @access	public
	 * @return	string
	 */
	public static function getFileSizeStr($size)
	{

		$unit		= "kB";
		$fileSize	= (int) max(1, round($size)/1024);
		
		if($fileSize > 1024) {
			$fileSize	= round($fileSize/1024, 1);
			$unit		= "MB";
		}
		
		return $fileSize . " " . $unit;
		
	}


	/**
	 * Gibt ein natives Bild zurück
	 * 
     * @param	string	Datei (mit vollständigem Pfad)
     * @param	string	Dateierweiterung
     * @param	boolean falls true, überprüfen auf Vorhandensein der Datei
	 * @access	public
	 * @return	string
	 */
	public static function getNativeImage($file, $fileExt)
	{

		// automatische Verkleinerung der Bildgröße auf angegebenes Format
		switch(strtolower($fileExt)) { // Je nach fileExt wird der Befehl für die Bildverkleinerung gewählt
		  case "jpg":
		  case "jpeg":
			  $nativeImg = imagecreatefromjpeg($file);
			  break;
		  
		  case "png":
			  $nativeImg = imagecreatefrompng($file);
			  break;
		  
		  case "gif":
			  $nativeImg = imagecreatefromgif($file);
			  break;
		}
		
		return $nativeImg;
		
	}


	/**
	 * Methode für den Datei-Upload inkl. automatischer Thumbnailgenerierung bei Bilddateien
	 * 
	 * @param	string	$upload_file	neue hochzuladende Datei
	 * @param	string 	$upload_upload_tmpfile	Name der temporären Datei
	 * @param	string	$folder			Ordner für den Dateiupload
	 * @param	string 	$fileType		Dateityp der hochzuladenden Datei
	 * @param	int 	$maxWidth		max. Breite bei Bildern
	 * @param	int 	$maxHeight		max. Höhe bei Bildern
	 * @param	boolean	$overwrite		Datei überschreiben (optional)
	 * @param	string	$fixName		Fester Name (Landesflaggen)
	 * @param	string	$delete_name	Name der ggf zu ersetzenden Datei (optional)
	 * @param	boolean	$makeThumb		Thumbnail generieren (default = true)
	 * @param	int		$max_filesize	Maximale Größe der Datei (default = 10485760)
	 * @access	public
	 * @return	boolean
	 */
	public static function uploadFile($upload_file, $upload_tmpfile, $folder = "", $fileType = "image", $maxWidth = 0, $maxHeight = 0, $overwrite = false, $fixName = "", $delete_name = "", $makeThumb = true, $max_filesize = 104857600)
	{
		
		$upload_file		= self::getValidFileName($upload_file);
		$folder				= rtrim($folder, "/");
		$resample			= true;
		$allowed_filetype	= false;
		$errorType			= "";
	
		$max_filesize		= self::getAllowedFileSize($max_filesize);
		$max_filesize_text	= self::getFileSizeStr($max_filesize);
		
		if($maxWidth == 0 && $maxHeight == 0)
			$resample = false;
		if($maxWidth == 0)
			$maxWidth = MAX_IMG_SIZE;
		if($maxHeight == 0)
			$maxHeight = MAX_IMG_SIZE;
		
		// Falls der Medienordner noch nicht existiert, diesen anlegen
		if (!is_dir(PROJECT_DOC_ROOT . '/media'))
			mkdir(PROJECT_DOC_ROOT . '/media');
			
		// Variablen
		$fileExt = self::getFileExt($upload_file); // Bestimmen der Dateinamenerweiterung

		if($fileType == "image") { // Falls eine Bilddatei hochgeladen werden soll
			$upload_path		= PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/';
			$allowed_filetype	= self::isImageFile($upload_file);
			$fileExtStr			= implode(", ", self::$imgFileExts);
			$errorType			= "{s_error:wrongtype2img}<br />{s_text:upload}: " . $fileExtStr;
		}
		
		elseif($fileType == "doc") { // Falls eine Dokumentdatei hochgeladen werden soll
			$upload_path		= PROJECT_DOC_ROOT . '/' . CC_DOC_FOLDER . '/';
			$allowed_filetype	= self::isDocFile($upload_file);
			$fileExtStr			= implode(", ", self::$docFileExts);
			$errorType			= "{s_error:wrongtype2doc}<br />{s_text:upload}: " . $fileExtStr;
		}
		
		elseif($fileType == "audio") { // Falls eine Audiodatei hochgeladen werden soll
			$upload_path		= PROJECT_DOC_ROOT . '/' . CC_AUDIO_FOLDER . '/';
			$allowed_filetype	= self::isAudioFile($upload_file);
			$fileExtStr			= implode(", ", self::$audioFileExts);
			$errorType			= "{s_error:wrongtype2audio}<br />{s_text:upload}: " . $fileExtStr;
		}
		
		elseif($fileType == "video") { // Falls eine Filmdatei hochgeladen werden soll
			$upload_path		= PROJECT_DOC_ROOT . '/' . CC_VIDEO_FOLDER . '/';
			$allowed_filetype	= self::isVideoFile($upload_file);
			$fileExtStr			= implode(", ", self::$videoFileExts);
			$errorType			= "{s_error:wrongtype2mov}<br />{s_text:upload}: " . $fileExtStr;
		}
		
		elseif($fileType == "theme-image") { // Falls eine Theme-Bilddatei hochgeladen werden soll
			$upload_path		= PROJECT_DOC_ROOT . '/'. IMAGE_DIR;
			$allowed_filetype	= self::isImageFile($upload_file);
			$fileExtStr			= implode(", ", self::$imgFileExts);
			$errorType			= "{s_error:wrongtype2img}<br />{s_text:upload}: " . $fileExtStr;
		}

		// Falls ein bestimmter fixer Name mitgegeben wurde, File umbenennen (z.B Länderflaggen)
		if($fixName != "") {
			$allowed_filetype	= self::isAllowedFile($upload_file);
			$upload_file		= $fixName;
		}
		
		// Falls ein Ordnername angegeben ist
		if($folder != "")
			$upload_path = PROJECT_DOC_ROOT . '/' . $folder . '/';
		
		
		// Dateiupload der Bilddatei			
		// Falls der Zielordner noch nicht existiert, diesen anlegen
		if (!is_dir($upload_path))
			mkdir($upload_path);

		// Falls die Datei bereits existiert, Fehlermeldung ausgeben
		if (file_exists($upload_path . $upload_file) 
		&& $overwrite == false
		)
			return sprintf(ContentsEngine::replaceStaText("{s_error:fileexists}"), $upload_file);
		
		// Überprüfung ob Dateityp erlaubt ist, sonst Fehlermeldung ausgeben
		if (!$allowed_filetype)
			return "{s_error:wrongtype1}" . $errorType;
		
		// Überprüfung ob Dateigröße erlaubt ist, sonst Fehlermeldung ausgeben
		if (filesize($upload_tmpfile) > $max_filesize)
			return "{s_error:filesize} " . $max_filesize_text . ".";
		
		// Überprüfung ob auf Uploadordner zugegriffen werden kann
		if (!is_writable($upload_path))
			return "{s_error:filechmod}";
		
		// Ansonsten Datei hochladen...
		if(move_uploaded_file($upload_tmpfile, $upload_path . $upload_file)) {
								  
			chmod($upload_path . $upload_file, 0755); // Rechte setzen
			
			// Falls eine Bilddatei hochgeladen werden soll
			if($fileType == "image") {
				$processImage	= self::processImageFile($upload_file, $upload_path, $fileExt, $resample, $maxWidth, $maxHeight, $makeThumb);
			}					
				
			// true zurückgeben bei erfolgreichem Upload
			return true;					
				
		}
		
		// Andernfalls Fehler zurückgeben
		return "{s_error:uploadfail}";

	} // Ende Methode fileUpload



	/**
	 * Bilddateien: Größenänderung/Thumberstellung nach dem Hochladen
	 * 
	 * @param	string	Dateiname
	 * @param	string	Dateipfad
	 * @param	string	Dateierweiterung
	 * @param	int 	max width
	 * @param	int 	max height
	 * @param	boolean	Thumbnail generieren (default = true)
	 * @param	string	zu ersetzende Datei (default = NULL)
	 * @access	public
	 * @return	boolean
	 */
	public static function processImageFile($upload_file, $upload_path, $fileExt, $resample, $maxWidth, $maxHeight, $makeThumb = true, $delete_name = NULL)
	{
	
		$image		= null;
		$thumb		= null;
		$small		= null;
		$medium		= null;
		$makeSmall	= false;
		$makeMedium	= false;
		
		// Falls Thumbordner nicht existiert, diesen anlegen
		if($makeThumb) {	
			$makeSmall	= true;
			$makeMedium	= true;
			if(!is_dir($upload_path . 'thumbs'))
				mkdir($upload_path . 'thumbs');
			if(!is_dir($upload_path . 'small'))
				mkdir($upload_path . 'small');
			if(!is_dir($upload_path . 'medium'))
				mkdir($upload_path . 'medium');
		}
		
		// Falls kein bitmap
		$lfileExt		= strtolower($fileExt);
		
		if($lfileExt	== "ico"
		|| $lfileExt	== "bmp"
		|| $lfileExt	== "svg"
		|| $lfileExt	== "svgz"
		) {
			if(copy($upload_path . $upload_file, $upload_path . 'thumbs/' . $upload_file))
				chmod($upload_path . 'thumbs/' . $upload_file, 0755); // Rechte setzen
			return false;
		}

		// Andernfalls Bild bearbeiten
			
		// automatische Verkleinerung der Bildgröße auf angegebenes Format
		// Bild einlesen
		$nativeImg		= self::getNativeImage($upload_path . $upload_file, $fileExt);
		$trnprt_indx	= imagecolortransparent($nativeImg);
							
		// Maße für resampletes Bild
		$width		= imagesx($nativeImg);
		$height		= imagesy($nativeImg);
		$imgHeight	= max((($maxWidth/$width)*$height), 1);
		$imgWidth	= max($maxWidth, 1);
		
		// Falls imgHeight die Maximale Größe übersteigt, Bildmaße an maxHeight orientieren
		if($imgHeight > $maxHeight) {
			$imgWidth	= max((($maxHeight/$height)*$width), 1);
			$imgHeight	= max($maxHeight, 1);
		}
		
		// Kein Resamplen, wenn Bild schmaler als imgWidth oder imgHeight
		if($width <= $imgWidth
		|| $height <= $imgHeight
		) {
			$resample	= false;
			$srcWidth	= $width;
			$srcHeight	= $height;
		}
		else {
			$srcWidth	= $imgWidth;
			$srcHeight	= $imgHeight;
		}

		// Maße für Thumbnail
		if($makeThumb) {
			$maxHeightThumb		= THUMB_SIZE;
			$maxWidthThumb		= max((($maxHeightThumb/$height)*$width), 1);
		}
		
		if($makeSmall
		&& $srcWidth >= (SMALL_IMG_SIZE + 100)
		) {
			$smallWidth		= SMALL_IMG_SIZE;
			$smallHeight	= max((($smallWidth/$srcWidth)*$srcHeight), 1);
		}
		else
			$makeSmall		= false;
		
		if($makeMedium
		&& $srcWidth >= (MEDIUM_IMG_SIZE + 200)
		) {
			$mediumWidth	= MEDIUM_IMG_SIZE;
			$mediumHeight	= max((($mediumWidth/$srcWidth)*$srcHeight), 1);
		}
		else
			$makeMedium		= false;
		
		// Bei GD 2.x wird imagecreatetruecolor verwendet, ansonsten imagecreate
		if($resample) {
			if(!@$image		= imagecreatetruecolor($imgWidth,$imgHeight)) $image = imagecreate($imgWidth,$imgHeight);
		}
		
		if($makeThumb) {
			if(!@$thumb		= imagecreatetruecolor($maxWidthThumb,$maxHeightThumb)) $thumb = imagecreate($maxWidthThumb,$maxHeightThumb);
		}
		if($makeSmall) {
			if(!@$small		= imagecreatetruecolor($smallWidth,$smallHeight)) $small = imagecreate($smallWidth,$smallHeight);
		}
		if($makeMedium) {
			if(!@$medium	= imagecreatetruecolor($mediumWidth,$mediumHeight)) $medium = imagecreate($mediumWidth,$mediumHeight);
		}
		
		// Falls PNG, Transparenz für Bild und Thumbnail erhalten
		if ($lfileExt == "png") {
			if($resample) {
				imagealphablending($image, false);
				imagesavealpha($image, true);
			}
			if($makeThumb) {
				imagealphablending($thumb, false);
				imagesavealpha($thumb, true);
			}
			if($makeSmall) {
				imagealphablending($small, false);
				imagesavealpha($small, true);
			}
			if($makeMedium) {
				imagealphablending($medium, false);
				imagesavealpha($medium, true);
			}
		}
		// Andernfalls, falls es eine bestimmte transparente Farbe gibt
		// If we have a specific transparent color
		elseif($trnprt_indx >= 0) {
			
			// Get the original image's transparent color's RGB values
			$trnprt_color = imagecolorsforindex($nativeImg, $trnprt_indx);

			if($resample) {
				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate($image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				imagefill($image, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				imagecolortransparent($image, $trnprt_indx);
			}

			if($makeSmall) {
				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate($small, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				imagefill($small, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				imagecolortransparent($small, $trnprt_indx);
			}

			if($makeMedium) {
				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate($medium, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				imagefill($medium, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				imagecolortransparent($medium, $trnprt_indx);
			}
		}
		
		// Statt imagecopyresized könnte man auch imagecopyresampled verwenden. Die Qualität davon ist wesentlich besser. Es ist allerdings erst in GD 2.x verfügbar und die Geschwindigkeit ist wesentlich langsamer.
		if($resample) {
			imagecopyresampled($image, $nativeImg, 0, 0, 0, 0, $imgWidth, $imgHeight, $width, $height);
		}
		
		if($makeThumb) {
			imagecopyresized($thumb, $nativeImg, 0, 0, 0, 0, $maxWidthThumb, $maxHeightThumb, $width, $height);
		}
		
		if($makeSmall) {
			imagecopyresampled($small, $nativeImg, 0, 0, 0, 0, $smallWidth, $smallHeight, $width, $height);
		}
		
		if($makeMedium) {
			imagecopyresampled($medium, $nativeImg, 0, 0, 0, 0, $mediumWidth, $mediumHeight, $width, $height);
		}
		
		switch($lfileExt) { // Je nach fileExt wird der Befehl für die Bildverkleinerung gewählt
		
			case "jpg":
			case "jpeg":
				if($resample)
					$resizedImage	= imagejpeg($image, $upload_path . $upload_file, 90);
				if($makeThumb)
					$resizedThumb	= imagejpeg($thumb, $upload_path . 'thumbs/' . $upload_file);
				if($makeSmall)
					$resizedSmall	= imagejpeg($small, $upload_path . 'small/' . $upload_file, 90);
				if($makeMedium)
					$resizedMedium	= imagejpeg($medium, $upload_path . 'medium/' . $upload_file, 90);
				break;

			case "png":
				if($resample)
					$resizedImage	= imagepng($image, $upload_path . $upload_file, 9);
				if($makeThumb)
					$resizedThumb	= imagepng($thumb, $upload_path . 'thumbs/' .  $upload_file);
				if($makeSmall)
					$resizedSmall	= imagepng($small, $upload_path . 'small/' . $upload_file, 9);
				if($makeMedium)
					$resizedMedium	= imagepng($medium, $upload_path . 'medium/' . $upload_file, 9);
				break;

			case "gif":
				if($resample)
					$resizedImage	= imagegif($image, $upload_path . $upload_file);
				if($makeThumb)
					$resizedThumb	= imagegif($thumb, $upload_path . 'thumbs/' .  $upload_file);
				if($makeSmall)
					$resizedSmall	= imagegif($small, $upload_path . 'small/' .  $upload_file);
				if($makeMedium)
					$resizedMedium	= imagegif($medium, $upload_path . 'medium/' .  $upload_file);
				break;
		}
					
		if($resample) {
			imagedestroy($image);
			chmod($upload_path . $upload_file, 0755);
		}
		
		if($makeThumb) {
			imagedestroy($thumb);
			chmod($upload_path . 'thumbs/' . $upload_file, 0755); // Rechte setzen
		}
		
		if($makeSmall) {
			imagedestroy($small);
			chmod($upload_path . 'small/' . $upload_file, 0755); // Rechte setzen
		}
		
		if($makeMedium) {
			imagedestroy($medium);
			chmod($upload_path . 'medium/' . $upload_file, 0755); // Rechte setzen
		}
		
				 
		// Falls eine andere Datei ersetzt werden sollte, die alte Imagedatei löschen
		if (isset($delete_name) 
		&& $delete_name != "" 
		&& $delete_name != NULL 
		&& file_exists($upload_path . $delete_name)
		) {
			unlink($upload_path . $delete_name);
			if($makeThumb)
				unlink($upload_path . 'thumbs/' . $delete_name);
		}

		return true;
	
	}
	


	/**
	 * Methode zum Drehen von Bildern
	 * 
	 * @param	string Dateiname
	 * @param	int Drehwinkel im Gegenuhrzeigersinn
	 * @access	public
	 * @return	boolean
	 */
	public static function rotateImage($fileName, $folder, $angle = 90)
	{
		
		$angle = 360 - $angle;
		
		if(!self::isImageFile($fileName))
			return false;
			
			
		$imgFile	= PROJECT_DOC_ROOT . '/' . $folder . '/' . $fileName;
		$thumbFile	= PROJECT_DOC_ROOT . '/' . $folder . '/thumbs/' . $fileName;
		$smallFile	= PROJECT_DOC_ROOT . '/' . $folder . '/small/' . $fileName;
		$mediumFile	= PROJECT_DOC_ROOT . '/' . $folder . '/medium/' . $fileName;
		$fileExt	= self::getFileExt($fileName);

		if(!file_exists($imgFile))
			return false;
		
		if(!file_exists($thumbFile))
			$thumbFile	= false;
		
		if(!file_exists($smallFile))
			$smallFile	= false;
		
		if(!file_exists($mediumFile))
			$mediumFile	= false;
		
		switch(strtolower($fileExt)) { // Je nach fileExt wird der Befehl für die Bildverkleinerung gewählt
		
			case "jpg":
			case "jpeg":
				if($imgSource = imagecreatefromjpeg($imgFile)) {
					if($thumbFile)
						$thumbSource = imagecreatefromjpeg($thumbFile) or die('Error opening file '.$thumbFile);
					if($smallFile)
						$smallSource = imagecreatefromjpeg($smallFile) or null;
					if($mediumFile)
						$mediumSource = imagecreatefromjpeg($mediumFile) or null;
				}
				else
					return false;
				break;
			
			case "png":
				if($imgSource = imagecreatefrompng($imgFile)) {
					if($thumbFile)
						$thumbSource = imagecreatefrompng($thumbFile) or die('Error opening file '.$thumbFile);
					if($smallFile)
						$smallSource = imagecreatefrompng($smallFile) or null;
					if($mediumFile)
						$mediumSource = imagecreatefrompng($mediumFile) or null;
				}
				else
					return false;
				imagealphablending($imgSource, false);
				imagesavealpha($imgSource, true);
				if($thumbFile) {							
					imagealphablending($thumbSource, false);
					imagesavealpha($thumbSource, true);
				}
				if($smallFile && $smallSource) {							
					imagealphablending($smallSource, false);
					imagesavealpha($smallSource, true);
				}
				if($mediumFile && $mediumSource) {							
					imagealphablending($mediumSource, false);
					imagesavealpha($mediumSource, true);
				}
				break;
			
			case "gif":
				if($imgSource = imagecreatefromgif($imgFile)) {
					if($thumbFile)
						$thumbSource = imagecreatefromgif($thumbFile) or die('Error opening file '.$thumbFile);
					if($smallFile)
						$smallSource = imagecreatefromgif($smallFile) or null;
					if($mediumFile)
						$mediumSource = imagecreatefromgif($mediumFile) or null;
				}
				else
					return false;
				break;
		}
					
		if($imgRotate = imagerotate($imgSource, $angle, 0)) {
			
			if(!$thumbFile || $thumbRotate = imagerotate($thumbSource, $angle, 0)) {
			
				$smallRotate	= $smallFile && $smallSource ? imagerotate($smallSource, $angle, 0) : null;
				$mediumRotate	= $mediumFile && $mediumSource ? imagerotate($mediumSource, $angle, 0) : null;

				switch(strtolower($fileExt)) { // Je nach fileExt wird der Befehl für die Bildverkleinerung gewählt
				
					case "jpg":
					case "jpeg":
						header('Content-type: image/jpeg');
						if(imagejpeg($imgRotate, $imgFile, 90)) {
							if($thumbFile) {							
								if(!imagejpeg($thumbRotate, $thumbFile, 80))
									if(!imagejpeg($thumbRotate, $thumbFile, 80))
										return false;
							}
							if($smallFile) {							
								if(!imagejpeg($smallRotate, $smallFile, 90))
									imagejpeg($smallRotate, $smallFile, 90);
							}
							if($mediumFile) {							
								if(!imagejpeg($mediumRotate, $mediumFile, 90))
									imagejpeg($mediumRotate, $mediumFile, 90);
							}
						}
						else
							return false;
						break;
					
					case "png":
						imagealphablending($imgRotate, false);
						imagesavealpha($imgRotate, true);
						imagealphablending($thumbRotate, false);
						imagesavealpha($thumbRotate, true);
						header('Content-type: image/png');
						if(imagepng($imgRotate, $imgFile, 5)) {
							if($thumbFile) {							
								if(!imagepng($thumbRotate, $thumbFile, 5))
									if(!imagepng($thumbRotate, $thumbFile, 5))
										return false;
							}
							if($smallFile) {							
								imagealphablending($smallRotate, false);
								imagesavealpha($smallRotate, true);
								if(!imagepng($smallRotate, $smallFile, 5))
									imagepng($smallRotate, $smallFile, 5);
							}
							if($mediumFile) {							
								imagealphablending($mediumRotate, false);
								imagesavealpha($mediumRotate, true);
								if(!imagepng($mediumRotate, $mediumFile, 5))
									imagepng($mediumRotate, $mediumFile, 5);
							}
						}
						else
							return false;
						break;
					
					case "gif":
						header('Content-type: image/gif');
						if(imagegif($imgRotate, $imgFile)) {
							if($thumbFile) {							
								if(!imagegif($thumbRotate, $thumbFile))
									if(!imagegif($thumbRotate, $thumbFile))
										return false;
							}
							if($smallFile) {							
								if(!imagegif($smallRotate, $smallFile, 5))
									imagegif($smallRotate, $smallFile, 5);
							}
							if($mediumFile) {							
								if(!imagegif($mediumRotate, $mediumFile, 5))
									imagegif($mediumRotate, $mediumFile, 5);
							}
						}
						else
							return false;
						break;
				}
				
				imagedestroy($imgSource);
				imagedestroy($imgRotate);

				if($thumbFile) {
					imagedestroy($thumbSource);
					imagedestroy($thumbRotate);
				}
				if($smallFile) {
					imagedestroy($smallSource);
					imagedestroy($smallRotate);
				}
				if($mediumFile) {
					imagedestroy($mediumSource);
					imagedestroy($mediumRotate);
				}
				
				return true;
			}
		}

		return false;
	}

}
