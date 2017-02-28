<?php
namespace Concise;


################################################
################  List Media  ##################
################################################

// List media

class ListMedia extends Admin
{

	private $action				= "";	
	private $redirect			= "";	
	
	public function __construct($DB, $o_lng, $themeType)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		// Theme-Setup
		$this->getThemeDefaults($themeType);
	
		if(isset($GLOBALS['_GET']['action']) && $GLOBALS['_GET']['action'] != "")
			$this->action = $GLOBALS['_GET']['action'];

	}
	
	public function conductAction()
	{		
	
		// If generate thumbs
		if($this->action == "thumbs")
			return $this->generateThumbs();
		
		// Falls elfinder
		if($this->action == "elfinder")
			return $this->initFilemanager();
		
		// Andernfalls Media
		$this->getMediaList();		
		
		return true;
	
	} // Ende Methode conductAction
	
	
	
 	/**
	 * generateThumbs
	 * 
	 * @access	public
	 */	 
	public function generateThumbs()
	{

		if(!empty($GLOBALS['_GET']['red']))
			$this->redirect = urldecode($GLOBALS['_GET']['red']);
		
		if(empty($GLOBALS['_GET']['folder'])
		|| !is_dir(PROJECT_DOC_ROOT . '/' . $GLOBALS['_GET']['folder'])
		) {
			$errorMes	= ContentsEngine::replaceStaText("{s_text:folder} {s_text:notexist}");
			
			if(!empty($this->redirect)) {
				$GLOBALS['_SESSION']['error']	= $errorMes;
				header('location: ' . $this->redirect);
				exit;
				die();
			}
			echo (json_encode(array("result" => "0",
									"alert" => $errorMes
							)
				 )
			);
			exit;
			die();
		}
		
		//Files-Klasse einbinden
		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";
			
		$imgFolder	= PROJECT_DOC_ROOT . '/' . $GLOBALS['_GET']['folder'] . '/';
				
		// Galeriebilder aus Ordner lesen
		$resArr	= array();
		
		$handle		= opendir($imgFolder);
		
		while($content = readdir($handle)) {
			if($content != ".."
			&& strpos($content, ".") !== 0
			&& !is_dir($imgFolder . $content)
			) {
			
				$fileExt	= Files::getFileExt($content);
				
				// Generate thumbs
				if(Files::isImageFile($content))
					$resArr[]	= Files::processImageFile($content, $imgFolder, $fileExt, false, MAX_IMG_SIZE, MAX_IMG_SIZE);
			}
		}
		closedir($handle);
		
		if(in_array(false, $resArr)) {
			$result		= "0";
			$message	= ContentsEngine::replaceStaText("{s_error:thumbsgen}");
		}
		else {
			$result		= "1";
			$message	= ContentsEngine::replaceStaText("{s_notice:thumbsgen}");
		}
		
		if(!empty($this->redirect)) {
			if($result)
				$GLOBALS['_SESSION']['notice']	= $message;
			else
				$GLOBALS['_SESSION']['error']	= $message;
			header('location: ' . $this->redirect);
			exit;
			die();
		}
		
		echo (json_encode(array("result" => $result,
								"alert" => $message
						)
			 )
		);
		exit;
		die();
	
	}

	
	// Falls filemanager
	private function initFilemanager()
	{
	
		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Filemanager.php"; // Filemanager einbinden
		
		$root		= "";
		$queryExt	= "";
		
		if(!empty($GLOBALS['_GET']['root']))
			$root	= $GLOBALS['_GET']['root'];
		
		if(!empty($GLOBALS['_GET']['ffolder']))
			$queryExt	= '&ffolder=' . $GLOBALS['_GET']['ffolder'];
		
		if(!empty($GLOBALS['_GET']['gall']))
			$queryExt	= '&gall=' . $GLOBALS['_GET']['gall'];
		
		$o_fileMan	= new Filemanager($this->DB, $this->o_lng);
		echo $o_fileMan->getFilemanager($root, $this->getAdminLang(), $queryExt);
		
		exit;
		die();

	}

	
	// List Media
	private function getMediaList()
	{
	
		$type			= "";
		$i				= "";

		// Andernfalls Medien
		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Media.php"; // Media einbinden
				
		if(isset($GLOBALS['_GET']['type']))	
			$type = $GLOBALS['_GET']['type'];

		if(isset($GLOBALS['_GET']['i']) && is_numeric($GLOBALS['_GET']['i']))
			$i = $GLOBALS['_GET']['i'];

		if(!empty($GLOBALS['_GET']['folder']))
			$subFolder	= "/" . $GLOBALS['_GET']['folder'];
		elseif(!empty($GLOBALS['_GET']['gal']))
			$subFolder	= "/" . $GLOBALS['_GET']['gal'] . "/thumbs";
		else
			$subFolder	= "";

		parent::$task="modules";
		parent::$type="gallery";
		$this->headIncludeFiles['moduleeditor'] = true;
		// Head-Definitionen (headExt)
		$this->getAdminHeadIncludes();
		
		// Head-Dateien zusammenführen
		$this->setHeadIncludes();
		
		$o_media	= new Media($this->DB, $this->o_lng, $this);
		$output		= $o_media->doAction($this->action, $type, $subFolder, $i);
		
		
		// if gallery edit add scipts
		if($this->action == "edit") {
		
			$output	.= '<script>' . "\r\n";
			
			foreach($this->scriptFiles as $key => $val) {
				$output	.= 'head.load({' . $key . ': "' . PROJECT_HTTP_ROOT . '/' . $val . '"});' . "\r\n";			
			}
			
			$output	.= implode(PHP_EOL, $this->scriptCode);
			$output	.= 'head.ready("editor", function(){ $.myTinyMCEModules(); });' . PHP_EOL;
			
			$output	.= '</script>' . "\r\n";
		}
		
		echo $output;
		
		exit;
		die();
	
	}

} // end class EditElements
