<?php

// common.php einbinden
require_once "../../../../inc/common.php";
require_once SYSTEM_DOC_ROOT . "/inc/checkBackendAccess.inc.php";
require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.Files.php";


$admin	= new Concise\Admin($DB, $o_lng);


// elFinder connector
error_reporting(0); // Set E_ALL for debuging

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';

// Sanitizer
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins/Sanitizer/plugin.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';

/**
 * # Dropbox volume driver need "dropbox-php's Dropbox" and "PHP OAuth extension" or "PEAR's HTTP_OAUTH package"
 * * dropbox-php: http://www.dropbox-php.com/
 * * PHP OAuth extension: http://pecl.php.net/package/oauth
 * * PEAR's HTTP_OAUTH package: http://pear.php.net/package/http_oauth
 *  * HTTP_OAUTH package require HTTP_Request2 and Net_URL2
 */
// Required for Dropbox.com connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDropbox.class.php';

// Dropbox driver need next two settings. You can get at https://www.dropbox.com/developers
// define('ELFINDER_DROPBOX_CONSUMERKEY',    '');
// define('ELFINDER_DROPBOX_CONSUMERSECRET', '');
// define('ELFINDER_DROPBOX_META_CACHE_PATH',''); // optional for `options['metaCachePath']`

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}


// Max file size
$uploadMaxSize	= min(ini_get('upload_max_filesize'), ini_get('post_max_size'));

// Admin-Lang
$locale			= $admin->getAdminLang() == "en" ? 'en_EN.UTF-8' : 'de_DE.UTF-8';

$rootsAvailable	= array("files",
						"images",
						"gallery",
						"docs",
						"audio",
						"video",
						"themes"
						);
$roots			= array();
// root folder(s)
if(!empty($_GET['root'])) {
	$roots		= explode(",", $_GET['root']);
	
	if(in_array("all", $roots))
		$roots	= $rootsAvailable;
	
	if(!$o_security->get('editorLog')) {
		if(($key = array_search("themes", $roots)) !== false) {
			unset($roots[$key]);
		}
	}
}


// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	#'debug' => true,
	'bind' => array(
		// own thumbnails
		'rename duplicate upload rm paste resize crop rotate' => array('updateThumbs'),
		'mkdir.pre' => 'checkFolderLevel',
		'mkdir'		=> 'checkGalleryFolder',
		// Sanitizer
		'mkdir.pre mkfile.pre rename.pre paste_pre copy_pre' => array(
			'Plugin.Sanitizer.cmdPreprocess'
		),
		'upload.presave' => array(
			'Plugin.Sanitizer.onUpLoadPreSave'
		)
	),
	// global configure (optional)
	'plugin' => array(
		'Sanitizer' => array(
			'enable' => true,
			'targets'  => array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß', ' ', '\'', '(', ')', '[', ']', '@', 'é', 'è', 'ë', 'í', 'ì', 'á', 'à', 'â', 'ú', 'ù', 'û', 'c¸', 'ñ'), // target chars
			'replace'  => array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss', '_', '-', '-', '-', '-', '-', '-', 'e', 'e', 'e', 'i', 'i', 'a', 'a', 'a', 'u', 'u', 'u', 'c', 'n')    // replace to this
		)
	),
	'roots' => array()
);


if(!empty($roots)) {
	foreach($roots as $root) {
		if(!in_array($root, $rootsAvailable))
			continue;
		$opts['roots'][]	= getRootOptions($root, $locale, $uploadMaxSize);
	}
}


function getRootOptions($root, $locale, $uploadMaxSize) {

	// files
	if($root == "files")
		return array(
			'locale'        => $locale,				// locale
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER . '/',         // path to files (REQUIRED)
			'URL'           => PROJECT_HTTP_ROOT . '/' . CC_FILES_FOLDER . '/', // URL to files (REQUIRED)
			'alias'         => Concise\ContentsEngine::replaceStaText('{s_button:filesfolder}'), // root folder icon
			'icon'          => SYSTEM_IMAGE_DIR . '/icon_files.png', // root folder icon
			'acceptedName'	=> '/^\w[\w\.\%\-]*$/',	// accepted file name								
			'tmbSize'       => THUMB_SIZE,			// thumbnail size								
			'tmbBgColor'    => 'transparent',  		// thumbnail bg color								
			'uploadMaxSize' => $uploadMaxSize,  	// max upload file size								
			'disabled' 		=> array('pixlr'),		// disabled commands
			'accessControl' => 'access',           	// disable and hide dot starting files (OPTIONAL)
			'startPath'		=> isset($GLOBALS['_GET']['ffolder']) ? PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER . '/' . $GLOBALS['_GET']['ffolder'].DIRECTORY_SEPARATOR : '', // not working in multiple roots setup, therefore Option startRoot added
			'startRoot'		=> isset($GLOBALS['_GET']['ffolder']) ? 1 : '' // added for startPath functionality on multiple roots
		);

	// images
	if($root == "images")
		return array(
			'locale'        => $locale,				// locale
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/',         // path to files (REQUIRED)
			'URL'           => PROJECT_HTTP_ROOT . '/' . CC_IMAGE_FOLDER . '/', // URL to files (REQUIRED)
			'alias'         => Concise\ContentsEngine::replaceStaText('{s_button:imgfolder}'), // root folder icon
			'icon'          => SYSTEM_IMAGE_DIR . '/icon_image.png', // root folder icon
			'acceptedName'	=> '/^\w[\w\.\%\-]*$/',	// accepted file name								
			'tmbSize'       => THUMB_SIZE,			// thumbnail size								
			'tmbBgColor'    => 'transparent',  		// thumbnail bg color								
			'uploadAllow'	=> array('image/png', 'image/jpeg', 'image/gif'),		// allow any images
			'uploadDeny'	=> array('all'),		// deny all (others)
			'uploadOrder'	=> 'deny,allow',		// allow any images
			'mimeDetect'	=> 'internal',			// mime type detection
			'uploadMaxSize' => $uploadMaxSize,  	// max upload file size								
			'disabled' 		=> array('mkdir', 'mkfile', 'rmdir', 'pixlr'),		// disabled commands
			'accessControl' => 'access'            // disable and hide dot starting files (OPTIONAL)
		);
	
	// gallery
	if($root == "gallery")
		return array(
			'locale'        => $locale,				// locale
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/',         // path to files (REQUIRED)
			'URL'           => PROJECT_HTTP_ROOT . '/' . CC_GALLERY_FOLDER . '/', // URL to files (REQUIRED)
			'alias'         => Concise\ContentsEngine::replaceStaText('{s_nav:admingallery}'), // root folder icon
			'icon'          => SYSTEM_IMAGE_DIR . '/icon_gallery.png', // root folder icon
			'acceptedName'	=> '/^\w[\w\.\%\-]*$/',	// accepted file name								
			'tmbSize'       => THUMB_SIZE,			// thumbnail size								
			'tmbBgColor'    => 'transparent',  		// thumbnail bg color								
			'uploadAllow'	=> array('image/png', 'image/jpeg', 'image/gif'),		// allow any images
			'uploadDeny'	=> array('all'),		// deny all (others)
			'uploadOrder'	=> 'deny,allow',		// allow any images
			'mimeDetect'	=> 'internal',			// mime type detection
			'uploadMaxSize' => $uploadMaxSize,  	// max upload file size								
			'disabled' 		=> array('mkfile', 'rmdir', 'pixlr'),		// disabled commands
			'accessControl' => 'access',            // disable and hide dot starting files (OPTIONAL)
			'startPath'		=> isset($GLOBALS['_GET']['gall']) ? PROJECT_DOC_ROOT . '/media/galleries/' . $GLOBALS['_GET']['gall'].DIRECTORY_SEPARATOR : '', // not working in multiple roots setup, therefore Option startRoot added
			'startRoot'		=> isset($GLOBALS['_GET']['gall']) ? 1 : '' // added for startPath functionality on multiple roots
		);
	
	// docs
	if($root == "docs")
		return array(
			'locale'        => $locale,				// locale
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => PROJECT_DOC_ROOT . '/' . CC_DOC_FOLDER . '/',         // path to files (REQUIRED)
			'URL'           => PROJECT_HTTP_ROOT . '/' . CC_DOC_FOLDER . '/', // URL to files (REQUIRED)
			'alias'         => Concise\ContentsEngine::replaceStaText('{s_button:docfolder}'), // root folder icon
			'icon'          => SYSTEM_IMAGE_DIR . '/icon_docs.png', // root folder icon
			'acceptedName'	=> '/^\w[\w\.\%\-]*$/',	// accepted file name								
			'uploadAllow'	=> array('application/msword', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),		// allow doc
			'uploadDeny'	=> array('all'),		// deny all (others)
			'uploadOrder'	=> 'deny,allow',		// allow any images
			'mimeDetect'	=> 'internal',			// mime type detection
			'uploadMaxSize' => $uploadMaxSize,  	// max upload file size								
			'disabled' 		=> array('mkdir', 'mkfile', 'rmdir', 'pixlr'),		// disabled commands
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		);
	
	// audio
	if($root == "audio")
		return array(
			'locale'        => $locale,				// locale
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => PROJECT_DOC_ROOT . '/' . CC_AUDIO_FOLDER . '/',         // path to files (REQUIRED)
			'URL'           => PROJECT_HTTP_ROOT . '/' . CC_AUDIO_FOLDER . '/', // URL to files (REQUIRED)
			'alias'         => Concise\ContentsEngine::replaceStaText('{s_button:audiofolder}'), // root folder icon
			'icon'          => SYSTEM_IMAGE_DIR . '/icon_audio.png', // root folder icon
			'acceptedName'	=> '/^\w[\w\.\%\-]*$/',	// accepted file name								
			'uploadAllow'	=> array('audio/mpeg', 'audio/mpeg3', 'audio/ogg', 'application/ogg'),		// allow audio
			'uploadDeny'	=> array('all'),		// deny all (others)
			'uploadOrder'	=> 'deny,allow',		// allow any images
			'mimeDetect'	=> 'internal',			// mime type detection
			'uploadMaxSize' => $uploadMaxSize,  	// max upload file size								
			'disabled' 		=> array('mkdir', 'mkfile', 'rmdir', 'pixlr'),		// disabled commands
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		);
	
	// Video
	if($root == "video")
		return array(
			'locale'        => $locale,				// locale
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => PROJECT_DOC_ROOT . '/' . CC_VIDEO_FOLDER . '/',         // path to files (REQUIRED)
			'URL'           => PROJECT_HTTP_ROOT . '/' . CC_VIDEO_FOLDER . '/', // URL to files (REQUIRED)
			'alias'         => Concise\ContentsEngine::replaceStaText('{s_button:videofolder}'), // root folder icon
			'icon'          => SYSTEM_IMAGE_DIR . '/icon_video.png', // root folder icon
			'acceptedName'	=> '/^\w[\w\.\%\-]*$/',	// accepted file name								
			'uploadAllow'	=> array('audio/x-pn-realaudio', 'video/quicktime', 'application/x-msmetafile', 'video/x-msvideo', 'video/mp4', 'video/x-m4v', 'video/ogg', 'video/webm', 'video/mpeg', 'video/mpg', 'video/x-flv', 'application/x-shockwave-flash'),		// allow videos
			'uploadDeny'	=> array('all'),		// deny all (others)
			'uploadOrder'	=> 'deny,allow',		// allow any images
			'mimeDetect'	=> 'internal',			// mime type detection
			'uploadMaxSize' => $uploadMaxSize,  	// max upload file size								
			'disabled' 		=> array('mkdir', 'mkfile', 'rmdir', 'pixlr'),		// disabled commands
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		);		

	// Themes folder
	if($root == "themes")
		return array(
			'locale'        => $locale,				// locale
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => PROJECT_DOC_ROOT . '/themes/',         // path to files (REQUIRED)
			'URL'           => PROJECT_HTTP_ROOT . '/themes/', // URL to files (REQUIRED)
			'alias'         => Concise\ContentsEngine::replaceStaText('{s_nav:admintpl}'), // root folder icon
			'icon'          => SYSTEM_IMAGE_DIR . '/icon_themes.png', // root folder icon
			'acceptedName'	=> '/^\w[\w\.\%\-]*$/',	// accepted file name								
			'mimeDetect'	=> 'internal',			// mime type detection
			'uploadMaxSize' => $uploadMaxSize,  	// max upload file size								
			'disabled' 		=> array('mkdir', 'mkfile', 'rmdir', 'pixlr'),		// disabled commands
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		);
	
	return "";			

}

	
// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();


	

/**
 * Generate own thumbnails
 *
 * @param  string   $cmd       command name
 * @param  array    $result    command result
 * @param  array    $args      command arguments from client
 * @param  object   $elfinder  elFinder instance
 * @return void|true
 **/
function updateThumbs($cmd, $result, $args, $elfinder) {
	
	// Thumbnails generieren, falls Bild
	$imgTypes		= array('image/png', 'image/jpeg', 'image/gif');
	$isGallery		= false;
	$galleryName	= "";
	$key			= 0;
	
	foreach ($result as $key => $value) {
        if (empty($value)) {
            continue;
        }
        $data = array();
		
        if (!in_array($key, array('error', 'warning'))) {
		
            if (is_array($value)) { // changes made to files
			
				foreach ($value as $file) {
					
                    $filePath	= (isset($file['realpath']) ? $file['realpath'] : $elfinder->realpath($file['hash']));
					$filePath	= stripFilePath($filePath);

					
					// Falls kein files- oder images-Ordner, keine Thumbnails generieren
					if(	strpos($filePath, "media/files") !== 0 && 
						strpos($filePath, "media/images") !== 0 &&
						strpos($filePath, "media\files") !== 0 && 
						strpos($filePath, "media\images") !== 0 && 
						strpos($filePath, "media/galleries") !== 0 && 
						strpos($filePath, "media\galleries") !== 0
					)
						return false;
					
				
					// Falls Galerie
					if( strpos($filePath, "media/galleries") === 0 || 
						strpos($filePath, "media\galleries") === 0
					) {
						$isGallery		= true;
						$galleryName	= str_replace(array($file['name'], "media/galleries", "media\galleries"), "", $filePath);
						$galleryName	= substr($galleryName, 1, -1);
						$galleryName	= $galleryName == "" ? $file['name'] : $galleryName;
						$galleryName	= str_replace(array("/thumbs","\thumbs"), "", $galleryName);
					}
				
                    if(!isset($file['mime']) || !in_array($file['mime'], $imgTypes))
						continue;
					
					// Path
                    $filePath		= PROJECT_DOC_ROOT . '/' . $filePath;
					$fileName		= $file['name'];					
					$thumbFolder	= str_replace($fileName, "thumbs", $filePath);
					$smallFolder	= str_replace($fileName, "small", $filePath);
					$mediumFolder	= str_replace($fileName, "medium", $filePath);
					$thumbPath		= $thumbFolder . "/" . $fileName;
					$smallPath		= $smallFolder . "/" . $fileName;
					$mediumPath		= $mediumFolder . "/" . $fileName;
					
					// If within thumbs folder
					if(	strpos($thumbPath, "/thumbs/thumbs/") !== false ||
						strpos($thumbPath, "/thumbs\thumbs/") !== false || 
						strpos($thumbPath, "\thumbs/thumbs/") !== false ||
						strpos($thumbPath, "\thumbs\thumbs/") !== false ||
						strpos($smallPath, "/small/small/") !== false ||
						strpos($smallPath, "/small\small/") !== false || 
						strpos($smallPath, "\small/small/") !== false ||
						strpos($smallPath, "\small\small/") !== false ||
						strpos($mediumPath, "/medium/medium/") !== false ||
						strpos($mediumPath, "/medium\medium/") !== false || 
						strpos($mediumPath, "\medium/medium/") !== false ||
						strpos($mediumPath, "\medium\medium/") !== false
					)
						continue;
					
					
					// If removed
					if($key == "removed") {
						
						if($fileName != "thumbs"
						&& $fileName != "small"
						&& $fileName != "medium"
						) {
							if(file_exists($thumbPath))
								unlink($thumbPath);
							if(file_exists($smallPath))
								unlink($smallPath);
							if(file_exists($mediumPath))
								unlink($mediumPath);
						}
						continue;
					}
					
					// Upload path
					$upload_path	= str_replace($fileName, "", $filePath);
					
					// Fileextension
					$fileExt		= Concise\Files::getFileExt($fileName);

					// Generate Thumbs
					Concise\Files::processImageFile($fileName, $upload_path, $fileExt, false, MAX_IMG_SIZE, MAX_IMG_SIZE);
				
                }
			}
        }
		
		// Falls Galerie
		if($isGallery && $galleryName != "") {
			sleep(2); // Pause, da sonst Konflikt beim Löschen von Galerieordnern (repairGallery)
			$galleryUpdate	= updateGallery($galleryName, $key);
		}
		#doLogging($cmd, $result, $args, $elfinder, "GN: ".$galleryName);
	}	
	
	return true;
	
}
	

/**
 * Update Gallery db
 *
 * @param  string   $gal      Galeriename
 * @return void|true
 **/
function updateGallery($gal, $key) {

	$galDB			= $GLOBALS['DB']->escapeString($gal);
	$gallPath		= PROJECT_DOC_ROOT . '/' . CC_GALLERY_FOLDER . '/' . $gal;

	require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.EditGalleries.php"; // EditGalleries-Klasse			

	// Object instanzieren
	$o_editGalleries	= new Concise\EditGalleries($GLOBALS['DB'], $GLOBALS['o_lng']);
	
	if(!is_dir($gallPath) 
	|| count(scandir($gallPath)) == 2
	) {
		return $o_editGalleries->removeGalleryFromDB($galDB);
	}
	
	// Überprüfen ob Ordner existiert
	if(is_dir($gallPath)) {
		return $o_editGalleries->repairGallery($gal);
	}
	
	return false;

}

	

/**
 * checkFolderLevel
 *
 * @param  string   $cmd       command name
 * @param  array    $result    command result
 * @param  array    $args      command arguments from client
 * @param  object   $elfinder  elFinder instance
 * @return void|true
 **/
function checkFolderLevel($cmd, $result, $args, $elfinder) {

    foreach ($result as $key => $value) {
        if (empty($value)) {
            continue;
        }
        $data = array();
		
        if (!in_array($key, array('error', 'warning'))) {
		
			if (!is_array($value)) {
				
				$fileTarget	= $elfinder->realpath($value);
				$filePath	= stripFilePath($fileTarget);
				
				// Falls Galerie
				if(strpos($filePath, "media/galleries") === 0 || 
					strpos($filePath, "media\galleries") === 0
				) {
					// Falls unerlaubte Foldertiefe
					$subDir	= str_replace(array("media/galleries", "media\galleries"), "", $filePath);
					$subDir	= substr($subDir, 1);
					if($subDir != "") {
						$elfinder->disabled[] = 'mkdir';
					}
				}
            }
        }
    }
}

	

/**
 * checkGalleryFolder
 *
 * @param  string   $cmd       command name
 * @param  array    $result    command result
 * @param  array    $args      command arguments from client
 * @param  object   $elfinder  elFinder instance
 * @return void|true
 **/
function checkGalleryFolder($cmd, $result, $args, $elfinder) {

	// Loggin function
    foreach ($result as $key => $value) {
	
        if (empty($value)) {
            continue;
        }
		
        $data = array();
		
        if (!in_array($key, array('error', 'warning'))) {

            if (is_array($value)) {
				
				$fileTarget	= $elfinder->realpath($value[0]['hash']);
				$filePath	= stripFilePath($fileTarget);
				
				// Falls Galerie
				if(strpos($filePath, "media/galleries") === 0 || 
					strpos($filePath, "media\galleries") === 0
				) {
					// Falls unerlaubte Foldertiefe
					$subDir	= str_replace(array("media/galleries", "media\galleries"), "", $filePath);
					$subDir	= substr($subDir, 1);
					
					if($subDir != "" 
					&& strpos($subDir, "/") === false
					&& strpos($subDir, "\\") === false
					) {
						$subDir	= str_replace(" ", "_", $subDir);
						createGallery($subDir);
					}
				}
            }
        }
    }
}

// createGallery function
function createGallery($gallName) {

	require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.EditGalleries.php"; // EditGalleries-Klasse
	
	// EditGalleries-Objekt
	$o_editGalleries	= new Concise\EditGalleries($GLOBALS['DB'], $GLOBALS['o_lng']);
	$createGall			= $o_editGalleries->createGallery($gallName, true);
}

// stripFilePath function
function stripFilePath($filePath) {

	$filePath	= str_replace("../", "", $filePath);
	$filePath	= str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $filePath);
	$filePath	= str_replace(PROJECT_DOC_ROOT . DIRECTORY_SEPARATOR, "", $filePath);

	return $filePath;
}

// Loggin function
function doLogging($cmd, $result, $args, $elfinder, $logExt = "") {

	$log = sprintf('[%s] %s:', date('r'), strtoupper($cmd));
	
    foreach ($result as $key => $value) {
        if (empty($value)) {
            continue;
        }
        $data = array();
        if (in_array($key, array('error', 'warning'))) {
            array_push($data, implode(' ', $value));
        } else {
            if (is_array($value)) { // changes made to files
                foreach ($value as $file) {
                    $filePath	= (isset($file['realpath']) ? $file['realpath'] : $elfinder->realpath($file['hash']));
                    #$filePath	= str_replace("../", "", $filePath);
                    array_push($data, "path: " . $filePath);
                    array_push($data, implode(PHP_EOL, "file: " . $file));
                }
            } else { // other value (ex. header)
                array_push($data, $value);
            }
        }
        $log .= sprintf(' %s(%s)', $key, implode(', ', $data)) . PHP_EOL;
    }
    $log .= $logExt . PHP_EOL;

    $logfile = PROJECT_DOC_ROOT . '/extLibs/jquery/elfinder/log/log.txt';
    $dir = dirname($logfile);
    if (!is_dir($dir) && !mkdir($dir)) {
        return;
    }
    if (($fp = fopen($logfile, 'a'))) {
        fwrite($fp, $log);
        fclose($fp);
    }
}