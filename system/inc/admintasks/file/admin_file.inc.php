<?php
namespace Concise;



###################################################
################  Datei-Upload  ###################
###################################################

// Dateien hochladen

require_once PROJECT_DOC_ROOT."/inc/classes/Contents/class.Contents.php"; // Contentsklasse einbinden
						

class Admin_File extends Admin implements AdminTask
{
	
	private $allowedFileSizeStr;
	private $forcedFileCat	= "";
	private $minImgSize		= MIN_IMG_SIZE;
	private $maxImgSize		= MAX_IMG_SIZE;
	private $uploadMethod	= "auto";
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;

		$this->headIncludeFiles['fileupload']	= true;
		$this->headIncludeFiles['filemanager']	= true;
		
		$this->formAction						= ADMIN_HTTP_ROOT . "?task=file";
	
	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Header
		$this->adminHeader		=	'{s_text:adminfile}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";
							
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		$this->adminContent		.=	'<div class="adminArea">' . "\r\n";
		

		// Ggf. zu große POST-Requests abfangen
		if($checkPostSize	= $this->checkPostRequestTooLarge())
			$this->notice	= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText("{s_error:postrequest}"), $checkPostSize), "error");

		
		// Optionen für File-Upload
		$this->uploadMethod			= Files::getUploadMethod();
		$this->allowedFileSizeStr	= Files::getFileSizeStr(Files::getAllowedFileSize());
		$this->allowedFiles			= Files::getAllowedFiles();
		sort($this->allowedFiles);
		
		
		// Breite für Bilder bestimmen
		if(isset($GLOBALS['_POST']['scaleimg']) 
		&& $GLOBALS['_POST']['scaleimg'] == "on" 
		&& isset($GLOBALS['_POST']['imgWidth']) 
		&& is_numeric($GLOBALS['_POST']['imgWidth']) 
		&& $GLOBALS['_POST']['imgWidth'] >= $this->minImgSize 
		&& $GLOBALS['_POST']['imgWidth'] <= $this->maxImgSize 
		&& isset($GLOBALS['_POST']['imgHeight']) 
		&& is_numeric($GLOBALS['_POST']['imgHeight']) 
		&& $GLOBALS['_POST']['imgHeight'] >= $this->minImgSize 
		&& $GLOBALS['_POST']['imgHeight'] <= $this->maxImgSize
		) {
			$imgWidth = $GLOBALS['_POST']['imgWidth'];
			$imgHeight = $GLOBALS['_POST']['imgHeight'];
			$scaleImg = true;
		}
		else {
			$imgWidth	= 0;
			$imgHeight	= 0;
			$scaleImg	= false;
		}

		// Überschreiben von Dateien
		$overwrite = false;
		if(isset($GLOBALS['_POST']['overwrite']) && $GLOBALS['_POST']['overwrite'] == "on")
			$overwrite = true;

		// Speichern von Dateien im Files-Ordner statt im Standardordner
		$useFilesFolder		= USE_FILES_FOLDER;
		$filesFolder		= "";
		$recentFilesFolder	= "";

		// Unterordner von Files merken bzw. aus Cookie auslesen
		if(isset($GLOBALS['_COOKIE']['recentFilesFolder']) && is_dir(PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER . '/' . $GLOBALS['_COOKIE']['recentFilesFolder']))
			$recentFilesFolder = htmlspecialchars($GLOBALS['_COOKIE']['recentFilesFolder']);

		if(isset($GLOBALS['_POST']['files_1']) 
		&& $GLOBALS['_POST']['files_1'] == "on"
		)
			$useFilesFolder	= true;
		if(isset($GLOBALS['_POST']['filesFolder_1']) 
		&& is_dir(PROJECT_DOC_ROOT . '/' . CC_FILES_FOLDER . '/' . $GLOBALS['_POST']['filesFolder_1'])
		)
			$filesFolder = htmlspecialchars($GLOBALS['_POST']['filesFolder_1']);
		else {
			$filesFolder = $recentFilesFolder;
			
			if(strpos($filesFolder, "/") !== false)
				$filesFolder = substr($recentFilesFolder, 0, strrpos($recentFilesFolder, "/"));
			else	
				$filesFolder = "";
		}

		// Notifications
		// Notice
		$this->notice	.= $this->getSessionNotifications("notice", true);


		require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.FileUploaderFactory.php"; // FileUploader-Factory einbinden
		
		
		// Element-Options
		$options	= array(	"allowedFiles"			=> $this->allowedFiles,
								"allowedFileSizeStr"	=> $this->allowedFileSizeStr,
								"forcedFileCat"			=> $this->forcedFileCat,
								"overwrite"				=> $overwrite,
								"scaleImg"				=> $scaleImg,
								"imgWidth"				=> $imgWidth,
								"imgHeight"				=> $imgHeight,
								"folder"				=> $filesFolder,
								"useFilesFolder"		=> $useFilesFolder
							);
		
		
		// FileUploader
		try {
			
			// FileUploader-Instanz
			$o_uploader	= FileUploaderFactory::create($this->uploadMethod, $options, $this->DB, $this->o_lng);
			#$o_uploader->assignHeadFiles();
			$this->adminContent .= $o_uploader->getUploadScript("#myUploadBox");
			$this->mergeHeadCodeArrays($o_uploader);
		}
		
		// Falls Element-Klasse nicht vorhanden
		catch(\Exception $e) {
			$this->adminContent = $this->backendLog ? $e->getMessage() : "";
			return $this->adminContent;
		}

		
		// Upload-Methode ändern
		if(isset($GLOBALS['_POST']['setuploadmethod'])) {
		
			$o_uploader->changeDefaultUploadMethod($GLOBALS['_POST']['setuploadmethod'], $this->formAction);
		
		}


		// Upload
		if(isset($GLOBALS['_FILES']['upload'])) {

			// Falls default Upload
			if($this->uploadMethod == "default" 
			&& !empty($GLOBALS['_FILES']['upload']['name'][0])
			&& isset($GLOBALS['_POST']['selFiles'])
			) {
				
				$uploadRes	= $o_uploader->uploadFiles($GLOBALS['_FILES']['upload'], explode(",", $GLOBALS['_POST']['selFiles']));
				
				if(count($uploadRes["error"]) > 0)
					$this->notice .=	'<p class="error">{s_error:file}</p>' . "\r\n" .
										'<ul id="errorMes">' . implode("", $uploadRes["error"]) . '</ul>' . "\r\n";
				
				elseif($uploadRes["success"])
					$this->notice .=	'<p class="notice success">{s_notice:fileok}</p>' . "\r\n";

			}
			
		} // Ende isset Files


		
		// Notification
		if(!empty($this->notice))
			$this->adminContent .= $this->notice;

		
		$this->adminContent .=	'<h2 class="switchToggle cc-section-heading cc-h2">{s_text:uploadfile}</h2>' . "\r\n" . 
								'<div class="adminBox">' . "\r\n" .
								'<ul class="framedItems fileUpload' . ($this->uploadMethod == "uploadify" ? ' fileUpload-uploadify' : '') . '">' . "\r\n" .
								'<li class="uploadDetails">' . "\r\n";
							
		$this->adminContent .=	'<form action="' . $this->formAction . '" method="post" name="uploadfm" id="uploadfm" class="' . $this->uploadMethod . '-uploader" enctype="multipart/form-data"' . ($this->uploadMethod == "default" ? ' data-ajax="false"' : '') . '>' . "\r\n" .
								'<input id="maxUploadSize" name="maxUploadSize" type="hidden" value="' . Files::getAllowedFileSize() .'" />' . "\r\n";


		
		// Upload-Maske
		$this->adminContent .= $o_uploader->getUploaderMask(parent::$task);
		
		
		// Filemanager
		$fileManager	=	'<ul class="editList folderList">' . "\r\n" .
							'<li class="manageFiles buttonPanel">' . "\r\n" .
							parent::getIcon('info', 'tooltipHint right', 'title="{s_hint:filemanager}"');
							
		// Filemanager MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "filemanager",
											"type"		=> "filemanager",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&action=elfinder&root=all",
											"value"		=> "{s_label:filemanager}",
											"icon"		=> "filemanager"
										);
		
		$fileManager .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$fileManager .=		'</li>' . "\r\n" .
							'</ul>' . "\r\n";


		$this->adminContent .=	'</form>' . "\r\n" .
								'</li>' . "\r\n";
		
		$this->adminContent .=	'<ul id="uploadFilesList" class="framedItems"><li>{s_javascript:nofilessel}</li></ul>' . "\r\n" . 
								$fileManager;
								
		// Uploadinfo
		$this->adminContent .=	'<ul class="framedItems">' . "\r\n" .
								'<li>' . "\r\n" .
								'<strong>{s_text:uploadfile}</strong>' . "\r\n";
		
		// File upload method
		$this->adminContent .=	$o_uploader->getFormChangeDefaultUploadMethod($this->formAction);
		
		
		$this->adminContent .=	'<label>' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . $this->allowedFileSizeStr . "\r\n" .
								'<label>{s_text:upload}</label>' . implode(", ", $this->allowedFiles) . '<br />' . "\r\n";
								
		$this->adminContent .=	'</li>' . "\r\n" .
								'</ul>' . "\r\n";
								
		$this->adminContent .=	'</ul>' . "\r\n";
		$this->adminContent .=	'</div>' . "\r\n";
		
		$this->adminContent .= 	'<h2 class="switchToggle cc-section-heading cc-h2 hideNext">{s_text:managefiles}</h2>' . "\r\n" . 
								'<ul>' . "\r\n" .
								$fileManager .
								'<ul class="editList folderList">' . "\r\n" .
								'<li class="listItem" data-menu="context" data-target="contextmenu-fl-0">' . "\r\n";
		
		// Files MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "files",
											"type"		=> "files",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=files&action=del" . ($recentFilesFolder != "" ? '&folder=' . $recentFilesFolder : ''),
											"value"		=> "{s_button:filesfolder}",
											"icon"		=> "files"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
								
		$this->adminContent .=	'<span class="editButtons-panel" data-id="contextmenu-fl-0">' . "\r\n";
		
		
		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delcon delall button-icon-only',
								"title"		=> '{s_title:delall}',
								"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=delall&type=files" data-menuitem="true" data-id="item-id-1"',
								"icon"		=> "delete"
							);
			
		$this->adminContent .=	parent::getButton($btnDefs);
		
		$this->adminContent .=	'</span>' . "\r\n";
		$this->adminContent .=	'</li>' . "\r\n";
		$this->adminContent .=	'<li class="listItem" data-menu="context" data-target="contextmenu-fl-1">' . "\r\n";
		
		// Images MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "images",
											"type"		=> "images",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=images&action=del",
											"value"		=> "{s_button:imgfolder}",
											"icon"		=> "image"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$this->adminContent .=	'<span class="editButtons-panel" data-id="contextmenu-fl-1">' . "\r\n";
		
		// Button generate thumbs
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'generateThumbs button-icon-only',
								"text"		=> "",
								"title"		=> '{s_title:genthumbs}<br /><br />(thumb-h: ' . THUMB_SIZE . 'px)<br />(small-w: ' . SMALL_IMG_SIZE . 'px)<br />(medium-w: ' . MEDIUM_IMG_SIZE . 'px)',
								"attr"		=> 'data-ajax="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=thumbs&folder=' . CC_IMAGE_FOLDER . '" data-confirm="{s_title:genthumbs}?" data-menuitem="true" data-id="item-id-1" data-menutitle="{s_title:genthumbs}"',
								"icon"		=> "thumbs"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
		
		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delcon delall button-icon-only',
								"title"		=> '{s_title:delall}',
								"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=delall&type=image" data-menuitem="true" data-id="item-id-2"',
								"icon"		=> "delete"
							);
			
		$this->adminContent .=	parent::getButton($btnDefs);
		
		$this->adminContent .=	'</span>' . "\r\n";
		$this->adminContent .=	'</li>' . "\r\n";
		$this->adminContent .=	'<li class="listItem" data-menu="context" data-target="contextmenu-fl-2">' . "\r\n";
		
		// Docs MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "docs",
											"type"		=> "doc",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=doc&action=del",
											"value"		=> "{s_button:docfolder}",
											"icon"		=> "doc"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
								
		$this->adminContent .=	'<span class="editButtons-panel" data-id="contextmenu-fl-2">' . "\r\n";
		
		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delcon delall button-icon-only',
								"title"		=> '{s_title:delall}',
								"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=delall&type=doc" data-menuitem="true" data-id="item-id-1"',
								"icon"		=> "delete"
							);
			
		$this->adminContent .=	parent::getButton($btnDefs);
		
		$this->adminContent .=	'</span>' . "\r\n";
		$this->adminContent .=	'</li>' . "\r\n";
		$this->adminContent .=	'<li class="listItem" data-menu="context" data-target="contextmenu-fl-3">' . "\r\n";
		

		// Video MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "video",
											"type"		=> "video",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=video&action=del",
											"value"		=> "{s_button:videofolder}",
											"icon"		=> "video"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
								
		$this->adminContent .=	'<span class="editButtons-panel" data-id="contextmenu-fl-3">' . "\r\n";
		
		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delcon delall button-icon-only',
								"title"		=> '{s_title:delall}',
								"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=delall&type=video" data-menuitem="true" data-id="item-id-1"',
								"icon"		=> "delete"
							);
			
		$this->adminContent .=	parent::getButton($btnDefs);
		
		$this->adminContent .=	'</span>' . "\r\n";
		$this->adminContent .=	'</li>' . "\r\n";
		$this->adminContent .=	'<li class="listItem" data-menu="context" data-target="contextmenu-fl-4">' . "\r\n";

		
		// Audio MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "audio",
											"type"		=> "audio",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=audio&action=del",
											"value"		=> "{s_button:audiofolder}",
											"icon"		=> "audio"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
								
		$this->adminContent .=	'<span class="editButtons-panel" data-id="contextmenu-fl-4">' . "\r\n";
		
		// Button delete
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'delcon delall button-icon-only',
								"title"		=> '{s_title:delall}',
								"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=delall&type=audio" data-menuitem="true" data-id="item-id-1"',
								"icon"		=> "delete"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
		
		
		$this->adminContent .=	'</span>' . "\r\n";
		$this->adminContent .=	'</li></ul>' . "\r\n" . 
								'</ul>' . "\r\n" . 
								'</div>' . "\r\n";

		
		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();

		
		$this->adminContent .=	'<div class="adminArea">' . "\r\n" . 
								'<ul><li class="submit back">' . "\r\n";

		// Button gallery
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=gallery&name=',
								"class"		=> "gallery left",
								"text"		=> "{s_header:newgallery} &raquo;",
								"title"		=> "{s_header:gallerytext}",
								"icon"		=> "gallery"
							);
		
		$this->adminContent	.=	parent::getButtonLink($btnDefs);

		// Button back
		$this->adminContent .=	$this->getButtonLinkBacktomain();
		
		$this->adminContent .=	'<br class="clearfloat" />' . "\r\n" .
								'</li></ul>' . "\r\n" . 
								'</div>' . "\r\n";
		
		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
	
	
		// Admin Tour Script
		$this->adminContent .=	$this->getFileAdminTourScript();
		
		
		return $this->adminContent;

	}
	
	
	// getFileAdminTourScript
	protected function getFileAdminTourScript()
	{
	
		return	'<script>
				head.ready(function(){
					head.load({hopscotch: "extLibs/jquery/hopscotch/js/hopscotch.min.js"}, function(){
						head.load("extLibs/jquery/hopscotch/css/hopscotch.min.css");
						head.load({admintourfile: "system/inc/admintasks/file/js/adminTour.file.min.js"}, function(){
							$("document").ready(function(){
								// Start tour on desktop devices
								if(!cc.isPhone()){
									$.file_AdminTour();
								}
							});
						});
					});
				});
				</script>' . PHP_EOL;
	
	}

}
