<?php

// common.php einbinden
require_once "../../../inc/common.php";
require_once SYSTEM_DOC_ROOT . "/inc/checkBackendAccess.inc.php"; // Berechtigung prüfen

require_once(PROJECT_DOC_ROOT . "/inc/classes/Media/class.Files.php");
require_once(PROJECT_DOC_ROOT . "/extLibs/jquery/plupload/PluploadHandler.php");


/**
 * Klasse Plupload
 *
 */

class Plupload extends \Concise\Files
{
	
	public function upload()
	{
		
		PluploadHandler::no_cache_headers();
		PluploadHandler::cors_headers();

		if (!PluploadHandler::handle(array(
			'target_dir'		=> self::getTargetDir(),
			'allow_extensions'	=> self::getAllowedFileTypes(),
			'cb_sanitize_file_name' => array('Concise\Files', 'getValidFileName')
			))
		) {
			die(json_encode(array(
				'OK' => 0, 
				'error' => array(
					'code'		=> PluploadHandler::get_error_code(),
					'message'	=> PluploadHandler::get_error_message()
				)
			)));
		} else {
			die(json_encode(array(
				'OK' => 1, 
				'originalname'	=> PluploadHandler::get_original_file_name(),
				'truename'		=> PluploadHandler::get_last_file_name(),
				'duplicate'		=> PluploadHandler::is_duplicate_file(),
				'folder'		=> self::getTargetDir(),
				'fileTypes'		=> self::getAllowedFileTypes()
				)
			));
		}
	}
	
	private function getTargetDir()
	{
	
		$path	= PROJECT_DOC_ROOT . DIRECTORY_SEPARATOR;
		
		// Falls files folder
		if(!empty($_REQUEST['useFilesFolder']) 
		&& isset($_REQUEST['filesFolder']) 
		&& $_REQUEST['filesFolder'] != ""
		&& is_dir($path . CC_FILES_FOLDER . DIRECTORY_SEPARATOR . $_REQUEST['filesFolder'])
		)
			return $path . CC_FILES_FOLDER . DIRECTORY_SEPARATOR . $_REQUEST['filesFolder'];
		
		// Falls galleries folder
		if(!empty($_REQUEST['gallName']) 
		&& $_REQUEST['gallName'] != ""
		) {
			self::$allowedFileTypes	= Concise\Files::$galleryFileExts;
			return $path . CC_GALLERY_FOLDER . DIRECTORY_SEPARATOR . $_REQUEST['gallName'];
		}
		
		// Falls bekannter Dateityp, Standardordner bestimmen
		if(isset($_REQUEST['name'])
		&& $_REQUEST['name'] != "" 
		) {
			if($defFolder = Concise\Files::getDefaultFolder($_REQUEST['name'])) 
				return $path . $defFolder;
		}
		
		// Falls unbekannt, files ordner wählen
		return $path . CC_FILES_FOLDER . DIRECTORY_SEPARATOR . 'unknown';
	
	}
	
	private function getAllowedFileTypes()
	{
		
		if(count(self::$allowedFileTypes) == 0)
			self::$allowedFileTypes = Concise\Files::$allowedFileTypes;

		return implode(",", self::$allowedFileTypes);
	
	}
}

$o_plupload	= new Plupload;
$o_plupload->upload();
