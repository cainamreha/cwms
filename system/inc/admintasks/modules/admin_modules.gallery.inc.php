<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\ExtendGalleryEvent;


###################################################
##############  Modules-Galleries  ################
###################################################

// Event-Klassen einbinden
require_once SYSTEM_DOC_ROOT."/inc/admintasks/modules/events/event.ExtendGallery.php";

require_once SYSTEM_DOC_ROOT."/inc/admintasks/modules/admin_modules.inc.php"; // AdminModules-Klasse einbinden

// Galerien verwalten 

class Admin_ModulesGallery extends Admin_Modules implements AdminTask
{
	
	private $tableGall			= "galleries";
	private $tableGallImg		= "galleries_images";
	private $existGalls			= array();
	private $installedGalls		= array();
	private $mediaDates			= array();
	private $picCount			= array();
	private $gallTags			= array();
	private $galleriesFolder	= CC_GALLERY_FOLDER;
	private $showGallList		= true;
	private $openGall			= false;
	private $allowedFileSizeStr;
	private $forcedFileCat		= "";
	private $minImgSize			= MIN_IMG_SIZE;
	private $maxImgSize			= MAX_IMG_SIZE;
	private $uploadMethod		= "auto";

	public function __construct($DB, $o_lng, $task, $init = false)
	{
	
		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng, $task, $init);
		
		parent::$task = $task;
		
		// Events listeners registrieren
		$this->addEventListeners("admingallery");
		
		// ExtendGalleryEvent
		$this->o_extendGalleryEvent					= new ExtendGalleryEvent($this->DB, $this->o_lng);
		
		$this->tableGall		= DB_TABLE_PREFIX . $this->tableGall;
		$this->tableGallImg		= DB_TABLE_PREFIX . $this->tableGallImg;
		
		$this->headIncludeFiles['sortable']			= true;
		$this->headIncludeFiles['moduleeditor']		= true;
		$this->headIncludeFiles['fileupload']		= true;

		// Optionen für File-Upload
		$this->uploadMethod							= Files::getUploadMethod();
		$this->allowedFileSizeStr					= Files::getFileSizeStr(Files::getAllowedFileSize());
		$this->allowedFiles							= Files::getAllowedFiles("gallery");
		sort($this->allowedFiles);

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Dateien hochladen/neue Gallerie anlegen

		#$this->allowedFiles		= Files::getAllowedFiles("gallery");
		$this->forcedFileCat	= "image";
		$gallName				= "";
		$this->formAction		= ADMIN_HTTP_ROOT . "?task=modules&type=gallery";
		$fieldPreset			= ""; // Wenn gesetzt wird bereits beim Laden der Seite eine Filterung in der Ansicht (Gallerieliste) vorgenommen
		$this->showGallList		= true;
		$this->openGall			= false;
		$overwrite				= false;
		$imgWidth				= 0;
		$imgHeight				= 0;
		$scaleImg				= false;
		$errors					= array();

		#$this->allowedFileSizeStr	= Files::getFileSizeStr(Files::getAllowedFileSize());
		#$this->uploadMethod			= Files::getUploadMethod();

		
		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminmodules}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";

		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		
		// Notifications
		$this->notice 	= $this->getSessionNotifications("notice");
		$this->hint		= $this->getSessionNotifications("hint");


		// Ggf. zu große POST-Requests abfangen
		if($checkPostSize	= $this->checkPostRequestTooLarge())
			$this->notice  .= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText("{s_error:postrequest}"), $checkPostSize), "error");


		
		// Gallerieordner ggf. anlegen
		if(!is_dir(PROJECT_DOC_ROOT . '/' . $this->galleriesFolder))
			@mkdir(PROJECT_DOC_ROOT . '/' . $this->galleriesFolder);

				
		if( isset($GLOBALS['_POST']['scaleimg']) 
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
			$imgWidth		= $GLOBALS['_POST']['imgWidth'];
			$imgHeight		= $GLOBALS['_POST']['imgHeight'];
			$scaleImg		= true;
		}


		// Falls ein Galleriename aus dem Editbereich per Url mitgegeben wurde
		if(isset($GLOBALS['_GET']['name'])) {
			$gallName = trim($GLOBALS['_GET']['name']);
			$gallName = str_replace(" ", "_", $gallName);
			$this->showGallList	= false;
		}

		// Falls eine bestimmte Gallerie bearbeitet werden soll (z.B. link aus FE)
		if(isset($GLOBALS['_GET']['edit_gall']) 
		&& $GLOBALS['_GET']['edit_gall'] != "" 
		&& is_dir(PROJECT_DOC_ROOT . '/' . $this->galleriesFolder . '/' . $GLOBALS['_GET']['edit_gall'])
		) {
			$this->openGall		= trim($GLOBALS['_GET']['edit_gall']);
			$editGall		= str_replace(" ", "_", $this->openGall);
			$gallName		= $editGall;
			$fieldPreset	= "search";
			$this->showGallList	= true;
		}


		require_once PROJECT_DOC_ROOT . "/inc/classes/Media/class.FileUploaderFactory.php"; // FileUploader-Factory einbinden


		// Element-Options
		$options	= array(	"allowedFiles"			=> $this->allowedFiles,
								"allowedFileSizeStr"	=> $this->allowedFileSizeStr,
								"forcedFileCat"			=> $this->forcedFileCat,
								"overwrite"				=> $overwrite,
								"scaleImg"				=> $scaleImg,
								"imgWidth"				=> $imgWidth,
								"imgHeight"				=> $imgHeight,
								"folder"				=> $this->galleriesFolder . '/' . $gallName,
								"useFilesFolder"		=> false
							);


		// FileUploader
		try {
			
			// FileUploader-Instanz
			$o_uploader	= FileUploaderFactory::create($this->uploadMethod, $options, $this->DB, $this->o_lng);
			#$o_uploader->assignHeadFiles("gallery");
			$this->adminContent .= $o_uploader->getUploadScript("#myUploadBox", "gallery");
			$this->mergeHeadCodeArrays($o_uploader);
		}

		// Falls Element-Klasse nicht vorhanden
		catch(\Exception $e) {
			$this->adminContent = $this->backendLog ? $e->getMessage() : "";
			return $this->adminContent;
		}



		// Falls das Formular abgeschickt wurde
		if(isset($GLOBALS['_POST']['gallName'])) {

			require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.EditGalleries.php"; // EditGalleries-Klasse	

			$gallName			= trim($GLOBALS['_POST']['gallName']);
			$gallName			= str_replace(" ", "_", $gallName);
			$gallNameDB			= $this->DB->escapeString($gallName);
			$dbUpdateStr		= "";
			$dbUpdateModDate	= array();
			
			// Ermitteln der Gall-ID
			// EditGalleries-Objekt
			$o_editGalleries	= new EditGalleries($this->DB, $this->o_lng);
			$gallID				= $o_editGalleries->getGallIdByName($gallName, true); // Gallery ID ermitteln und ggf. neu anlegen (table galleries)
			
			
			// Galerienamen überprüfen	
			if($gallName == "")
				$errors['name'] = "{s_notice:nogallname}";

			elseif(!preg_match("/^[A-Za-z0-9_-]+$/", $gallName))
				$errors['name'] = "{s_notice:wrongname}";

			elseif(strlen($gallName) > 64)
				$errors['name'] = "{s_notice:longname}";

				
			if(isset($errors['name']))
				$this->showGallList	= false;
			
			
			// Ermitteln der Max-SortId
			$queryMaxSortId = $this->DB->query("SELECT MAX(img.`sort_id`) AS maxsort 
													FROM `$this->tableGall` as gall 
													LEFT JOIN `$this->tableGallImg` as img 
														ON gall.`id` = img.`gallery_id` 
												WHERE gall.`gallery_name` = '" . $gallNameDB . "'
												");

			
			$sortId = !empty($queryMaxSortId[0]['maxsort']) ?$queryMaxSortId[0]['maxsort'] : 0;
					
			if($sortId > 0)
				$sortId++;
			else
				$sortId = 1;
			
			
			// Falls die Checkbox zum Überschreiben von Dateien gecheckt ist
			if(isset($GLOBALS['_POST']['overwrite']) 
			&& $GLOBALS['_POST']['overwrite'] == "on"
			)
				$overwrite = true;		
			

			// Upload-Ordner setzen
			$o_uploader->folder		= $this->galleriesFolder . '/' . $gallName;
			$o_uploader->overwrite	= $overwrite;
			$o_uploader->scaleImg	= $scaleImg;
			$o_uploader->imgWidth	= $imgWidth;
			$o_uploader->imgHeight	= $imgHeight;

			
			// Upload
			// Falls default Upload
			if($this->uploadMethod == "default" 
			&& !isset($errors['name']) 
			&& !empty($GLOBALS['_FILES']['upload']['name'][0])
			&& isset($GLOBALS['_POST']['selFiles'])
			) {

				
				$uploadRes		= $o_uploader->uploadFiles($GLOBALS['_FILES']['upload'], explode(",", $GLOBALS['_POST']['selFiles']));
				
				if(count($uploadRes["error"]) > 0)
					$notice = '<p class="error">{s_error:file}</p><ul id="errorMes">' . implode("", $uploadRes["error"]) . '</ul>' . "\r\n";
				
				elseif($uploadRes["success"])
					$notice = '<p class="notice success">{s_notice:fileok}</p>' . "\r\n";

				// Neue Dateien
				foreach ($uploadRes["newFiles"] as $newFile) {
					
					// If no img file, don't save
					if(!Files::isImageFile($newFile))
						continue;
					
					$dbUpdateStr .= "(" . $sortId . "," . $gallID . ",'" . $this->DB->escapeString($newFile) . "','" . date("Y.m.d H:i:s") . "'),"; // db-Updatestring generieren
					
					$sortId++; // SortId erhöhen
				}
				
				// Ersetzte Dateien (mod_date)
				foreach ($uploadRes["replacedFiles"] as $repFile) {
					
					// If no img file, don't save
					if(!Files::isImageFile($newFile))
						continue;
				
					$dbUpdateModDate[] = array($repFile, $gallName); // Array mit ersetzten Dateien (sprich nur Update von ModDate)
				
				}

			} // Ende if default upload
			
			
			// Falls Upload via Uploader erfolgt ist, DB updaten
			elseif(isset($GLOBALS['_POST']['uppedFiles'])) {
				
				$uppedFiles		= array_unique(array_filter(explode(",", $GLOBALS['_POST']['uppedFiles'])));
				$duplicateFiles	= array_unique(array_filter(explode(",", $GLOBALS['_POST']['duplicateFiles'])));
				
				foreach($uppedFiles as $uppedFile) {
					
					$validFileName = Files::getValidFileName($uppedFile);
					
					if($uppedFile != "") {
					
						// If no img file, don't save
						if(!Files::isImageFile($uppedFile))
							continue;
						
						if(!in_array($uppedFile, $duplicateFiles)) {
							$dbUpdateStr .= "(" . $sortId . "," . $gallID . ",'" . $this->DB->escapeString($validFileName) . "','" . date("Y.m.d H:i:s") . "'),"; // db-Updatestring generieren
									
							$sortId++; // SortId erhöhen
						}
							
						else
							$dbUpdateModDate[] = array($validFileName, $gallName); // Array mit ersetzten Dateien (sprich nur Update von ModDate)
					}
				}
				$notice = '<p class="notice success">{s_notice:fileok}</p>' . "\r\n";
			}
			
			if($dbUpdateStr != "" || count($dbUpdateModDate) > 0) {
				
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `$this->tableGall`, `$this->tableGallImg`");

					
				// Falls Dateien erfolgreich hochgeladen wurden, diese in DB aufnehmen
				if($dbUpdateStr != "") {
					
					$dbUpdateStr = substr($dbUpdateStr, 0, -1); // Letztes Komma entfernen
					

					// Einfügen der Galleriedaten
					$insertSQL = $this->DB->query("INSERT INTO `$this->tableGallImg` 
														(`sort_id`,`gallery_id`,`img_file`,`upload_date`) 
														VALUES " . $dbUpdateStr . "
														");

					#var_dump($insertSQL);

				}
				
				// Falls Dateien ersetzt wurden, diese in DB aktualisieren (modDate)
				if(count($dbUpdateModDate) > 0) {
					
					foreach($dbUpdateModDate as $modFile) {
						
						// Einfügen der Galleriedaten
						$updateSQL = $this->DB->query("UPDATE `$this->tableGallImg` 
																SET `mod_date` = CURRENT_TIMESTAMP 
															WHERE `img_file` = '$modFile[0]'
																AND `gallery_id` = $gallID
															");

						#var_dump($updateSQL);
					}
				}
				
				// Sortierung neu durchführen
				// Variable für Neusortierung setzen
				$updateSQL2a = $this->DB->query("SET @c:=0;
													");
				
				
				// Neusortierung
				$updateSQL2b = $this->DB->query("UPDATE `$this->tableGallImg` 
														SET `sort_id` = (SELECT @c:=@c+1)
													  WHERE `gallery_id` = $gallID 
														ORDER BY `sort_id` ASC;
													  ");
			
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");

			}
			
			// Falls redirect
			if(!empty($GLOBALS['_GET']['redirect'])) {
				header("Location: " . urldecode($GLOBALS['_GET']['redirect']));
				exit;
			}

		} // Ende if post name

		

		// Falls das Formular zur Bearbeitung von Galleriebild-Texten abgeschickt wurde
		elseif(isset($GLOBALS['_POST']['changeGalleryItems'])) {
						
			require_once SYSTEM_DOC_ROOT . "/inc/adminclasses/class.EditGalleries.php"; // EditGalleries-Klasse	

			$gallName			= $GLOBALS['_POST']['gall_name'];
			$gallNameDB			= $this->DB->escapeString($gallName);
			$imgNames			= $GLOBALS['_POST']['img_name'];
			$this->showGallList		= true;
			
			// Ermitteln der Gall-ID
			// EditGalleries-Objekt
			$o_editGalleries	= new EditGalleries($this->DB, $this->o_lng);
			$gallID				= $o_editGalleries->getGallIdByName($gallName, true);

			
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `$this->tableGallImg`");


			for($i = 0; $i < count($imgNames); $i++) {
			
			
				$imgName	= $this->DB->escapeString($GLOBALS['_POST']['img_name'][$i]);
				$imgTitle	= $this->DB->escapeString(trim($GLOBALS['_POST']['img_title'][$i]));
				$imgLink	= $this->DB->escapeString(trim($GLOBALS['_POST']['img_link'][$i]));
				$imgTags	= $this->DB->escapeString(trim($GLOBALS['_POST']['img_tags'][$i]));
				$imgText	= $this->DB->escapeString(trim($GLOBALS['_POST']['img_text'][$i]));
				
				$imgLink	= str_replace(PROJECT_HTTP_ROOT, "{#root}", $imgLink);
				$imgText	= str_replace(PROJECT_HTTP_ROOT, "{#root}", $imgText);

				
				// DB-Update der Bildtexte
				$updateSQL = $this->DB->query("UPDATE `$this->tableGallImg` 
													SET `title_" . $this->editLang . "` = '$imgTitle',
														`link_" . $this->editLang . "` = '$imgLink',
														`img_tags` = '$imgTags',
														`text_" . $this->editLang . "` = '$imgText' 
													WHERE `img_file` = '$imgName' 
													AND `gallery_id` = $gallID
													");

				#var_dump($updateSQL);

			} // Ende for

			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");

			$notice		= '<p class="notice success">{s_notice:takechange}</p>' . "\r\n";
			
			$this->openGall	= $gallName;
			
			if(!empty($GLOBALS['_GET']['json'])) {

				// Andernfalls Medien
				require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Media.php"; // Media einbinden

				$type		= "gallery";
				$action		= "edit";
				$i			= 1;
				$subFolder	= "/" . $gallName . "/thumbs";

				$o_media	= new Media($this->DB, $this->o_lng, $this);
				
				echo $o_media->doAction($action, $type, $subFolder, $i);
				exit;
			}			

		}

		// Upload-Methode ändern
		elseif(isset($GLOBALS['_POST']['setuploadmethod'])) {

			$o_uploader->changeDefaultUploadMethod($GLOBALS['_POST']['setuploadmethod'], $this->formAction . '&uplm=1');
			$this->showGallList	= false;
		
		}
		
		// Falls Upload-Methode geändert wurde
		if(!empty($GLOBALS['_GET']['uplm'])) {

			$this->showGallList	= false;
		
		}

		
		// Gallerien auflisten
		// Gallerieordner ins Array einlesen
		$this->getGalleriesByDir();

		// Gallerien aus DB auslesen
		$this->installedGalls	= $this->getGalleriesFromDB();

		// Gallerie Parameter zuweisen
		$this->assignGallerieTags();

		
		if(count($this->existGalls) == 0)
			$this->showGallList	= false;

		
		
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();

		

		$this->adminHeader		=	'{s_text:admingallery}' . "\r\n" . 
									'</div><!-- Ende headerBox -->' . "\r\n";
		
		$this->adminContent .=		'<div class="adminArea">' . "\r\n";

		if(isset($notice) && $notice != "")
			$this->adminContent .= $notice;
		elseif(isset($this->notice) && $this->notice != "")
			$this->adminContent .= '<p class="notice success">' . $this->notice . '</p>' . "\r\n";
			
					
		$this->adminContent .=	'<h2 class="switchToggle cc-section-heading cc-h2' . ($this->showGallList || $this->openGall ? ' hideNext' : '') . '">{s_header:newgallery}</h2>' . "\r\n" . 
								'<ul class="fileUpload framedItems' . ($this->uploadMethod == "uploadify" ? ' fileUpload-uploadifyGallery' : '') . '"><li class="uploadDetails">' . "\r\n" .
								'<form action="' . $this->formAction . '" method="post" name="uploadfm" id="uploadfm" class="' . $this->uploadMethod . '-uploader gallery-form" enctype="multipart/form-data" accept-charset="UTF-8"' . ($this->uploadMethod == "default" ? ' data-ajax="false"' : '') . '>' . "\r\n" . 
								'<label>{s_label:gallname}</label>' . "\r\n";

		if(isset($errors['name']))
			$this->adminContent .=	'<p class="notice">' . $errors['name'] . '</p>' . "\r\n";


		$this->adminContent .=	'<input type="text" name="gallName" id="gallName" value="' . (isset($gallName) && $gallName != '' ? htmlspecialchars($gallName) : '') . '" maxlength="64" />' . "\r\n";
		
		// Gallery MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "gallery",
											"type"		=> "gallery",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listPages.php?page=admin&type=gallery",
											"value"		=> "{s_button:gallchoose}",
											"title"		=> "{s_title:extendgall}",
											"icon"		=> "gallery"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);

		
		// Upload-Maske
		$this->adminContent .= $o_uploader->getUploaderMask(parent::$type);
		

		$this->adminContent .=	'<input type="hidden" name="selFiles" id="selFiles" />' . "\r\n" . 
								'<input type="hidden" name="token" value="' . parent::$token . '" />' . "\r\n" . 
								'</form><br class="clearfloat" /></li>' . "\r\n" . 
								'<ul id="uploadFilesList" class="framedItems"><li>{s_javascript:nofilessel}</li></ul>' . "\r\n";
					
		// Uploadinfo
		$this->adminContent .=	'<ul class="framedItems">' . "\r\n" .
								'<li>' . "\r\n" .
								'<strong>{s_text:uploadfile}</strong>' . "\r\n";
					

		// File upload method
		$this->adminContent .=	$o_uploader->getFormChangeDefaultUploadMethod($this->formAction);

					
		$this->adminContent .=	'<label>' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . Files::getFileSizeStr(Files::getAllowedFileSize()) . "\r\n" .
								'<label>{s_text:upload}</label>' . implode(", ", $this->allowedFiles) . '<br />' . "\r\n" . 
								'</li>' . "\r\n" .
								'</ul></ul>' . "\r\n";
							
		$this->adminContent .= 	'<h2 class="switchToggle cc-section-heading cc-h2' . (!$this->showGallList && !$this->openGall ? ' hideNext' : '') . '">{s_header:gallerytext}</h2>' . "\r\n" .
								'<ul>' . "\r\n" .
								'<h3 class="cc-h3 switchToggle">{s_header:editgall}</h3>' . "\r\n" .
								'<div class="adminBox">' . "\r\n";


		$this->adminContent .= 	'<ul class="editList galleryList">' . "\r\n";

		// Dialog-Form zum Ändern des Gallerienamens, falls Gallerien vorhanden
		if(count($this->existGalls) > 0) {
			
			// Anzahl an Mediendaten
			$this->mediaCount	= count($this->existGalls);
			$this->adminContent .= 	$this->getGalleryEditDialog();
			
		}

		// Control Bar zum Filtern der Anzeige
		$controlBar			=	$this->getControlBar("date", $fieldPreset, $gallName);

		$showSLstEntries	=	'<span class="showHiddenListEntries actionBox cc-hint toggle"' . ($this->openGall ? '' : ' style="display:none;"') . ' onclick="$(\'.showHiddenListEntries\').each(function(){ $(this).hide(); $(this).siblings(\'.controlBar\').find(\'input.listSearch\').val(\'\').focus().trigger({ type :\'keyup\', which : 8 }).val(\'\'); });">';
		

		// Filter icon
		$showSLstEntries  .=	'<span class="listIcon">' . "\r\n" .
								parent::getIcon("filter", "inline-icon") .
								'</span>' . "\n";
						
		$showSLstEntries  .=	'{s_label:showlistentries}' .
								'<span class="editButtons-panel">' . "\r\n";
		
		// Button list
		$btnDefs	= array(	"type"		=> "button",
								"class"		=> 'removeFilter button-icon-only',
								"text"		=> "",
								"title"		=> '{s_label:showlistentries}',
								"icon"		=> "list"
							);
		
		$showSLstEntries .=	parent::getButton($btnDefs);
		
		$showSLstEntries .=		'</span>' . "\r\n" .
								'</span>' . "\r\n";

		$gallListEdit		= "";
		$gallListDel		= "";
		$i					= 1;
		
		// Vorhandene Galerien auflisten
		foreach($this->existGalls as $key => $gallery) {
			
			$gallPicCount	=	'<span class="fileCount" title="' . $gallery . '" data-content="count"><strong>' . $this->picCount[$key] . '</strong> {s_text:gallcount}</span>' . "\r\n";
			$gallCreateDate =	'<span class="fileDate" title="' . $gallery . '">' . parent::getDateString($this->mediaDates[$key], $this->adminLang, false) . '</span>' . "\r\n";
			$gallTags		=	'<span class="gallTags-box" title="Tags: ' . $this->gallTags[$gallery] . '">' . parent::getIcon("tags") .
								'<span class="gallTags" data-content="tags">' . $this->gallTags[$gallery] . '</span>' . "\r\n" .
								'</span>' . "\r\n";
			
			// Edit-List
			$gallListEdit .='<li id="editlist-' . $gallery . '" class="listItem gallListItem ' . (is_numeric($gallery[0]) ? '0-9' : strtoupper($gallery[0])) . ' date-' . $this->mediaDates[$key] . '" data-menu="context" data-target="contextmenu-el-' . $i . '">' . "\r\n";
		
			// Gallery MediaList-Button
			$mediaListButtonDef		= array(	"class"	 	=> "gallery",
												"type"		=> "gallery",
												"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&action=edit&type=gallery&gal=" . $gallery,
												"slbclass"	=> ($this->openGall && $this->openGall == $gallery ? ' showOnLoad' : ''),
												"value"		=> $gallery,
												"title"		=> "{s_title:editgall}",
												"icon"		=> "gallery"
											);
			
			$gallListEdit .=	$this->getButtonMediaList($mediaListButtonDef);
			
			$gallListEdit .=$gallPicCount .
							$gallCreateDate .
							$gallTags .
							'<span class="editButtons-panel" data-id="contextmenu-el-' . $i . '">' . "\r\n";
		
			// Button edit
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'changeGallName dialog dialog-gallery button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:gallparams}',
									"attr"		=> 'data-dialog="gallery" data-dialogname="' . $gallery . '" data-dialogid="" data-menuitem="true" data-id="item-id-' . $i . '-1"',
									"icon"		=> "edit"
								);
			
			$btnEditGall	=	parent::getButton($btnDefs);
			
			$gallListEdit  .=	$btnEditGall;
		
			// Button generate thumbs
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'generateThumbs button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:genthumbs}<br /><br />(thumb-h: ' . THUMB_SIZE . 'px)<br />(small-w: ' . SMALL_IMG_SIZE . 'px)<br />(medium-w: ' . MEDIUM_IMG_SIZE . 'px)',
									"attr"		=> 'data-ajax="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=thumbs&folder=' . CC_GALLERY_FOLDER . '/' . $gallery . '" data-confirm="{s_title:genthumbs}?" data-menuitem="true" data-id="item-id-' . $i . '-2" data-menutitle="{s_title:genthumbs}"',
									"icon"		=> "thumbs"
								);
			
			$btnGenThumbs	=	parent::getButton($btnDefs);
			
			$gallListEdit  .=	$btnGenThumbs;

			// Button delete
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> 'delcon delgal button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:delgall}',
									"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/listMedia.php?page=admin&action=delall&type=gallery&gal=' . $gallery . '" data-menuitem="true" data-id="item-id-' . $i . '-3"',
									"icon"		=> "delete"
								);
			
			$btnDelGall		=	parent::getButton($btnDefs);
			
			$gallListEdit  .=	$btnDelGall;
			
			$gallListEdit .=	'</span>' . "\r\n" .
								'</li>' . "\r\n";
			
			// Del-List
			$gallListDel .=	'<li id="dellist-' . $gallery . '" class="listItem gallListItem ' . (is_numeric($gallery[0]) ? '0-9' : strtoupper($gallery[0])) . ' date-' . $this->mediaDates[$key] . '" data-menu="context" data-target="contextmenu-dl-' . $i . '">' . "\r\n";
		
			// Gallery MediaList-Button
			$mediaListButtonDef		= array(	"class"	 	=> "images gallery",
												"type"		=> "gallery",
												"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&action=del&type=gallery&gal=" . $gallery,
												"value"		=> $gallery,
												"title"		=> "{s_title:delgallpic}",
												"icon"		=> "gallery"
											);
			
			$gallListDel .=	$this->getButtonMediaList($mediaListButtonDef);
			
			$gallListDel .= $gallPicCount .
							$gallCreateDate .
							$gallTags .
							'<span class="editButtons-panel" data-id="contextmenu-dl-' . $i . '">' . "\r\n";
			
			// Button edit
			$gallListDel .=	$btnEditGall;
			
			// Button generate thumbs
			$gallListDel .=	$btnGenThumbs;

			// Button delete
			$gallListDel .=	$btnDelGall;
			
			
			$gallListDel .=	'</span>' . "\r\n" .
							'</li>' . "\r\n";

			$i++;
			
		}


		$this->adminContent .=	$controlBar;
		$this->adminContent .=	$showSLstEntries;
			
		$this->adminContent .= 	$gallListEdit . '</ul>' . "\r\n"; // Edit-List	
		$this->adminContent .= 	'</div>' . "\r\n";
			
		$this->adminContent .= 	'<h3 class="cc-h3 switchToggle hideNext">{s_header:delgallery}</h3>' . "\r\n" . 
								'<ul class="editList folderList galleryList">' . "\r\n";
							
		// Control Bar zum Filtern der Anzeige
		$this->adminContent .=	$controlBar;
		$this->adminContent .=	$showSLstEntries;
			
		$this->adminContent .= 	$gallListDel . '</ul>' . "\r\n"; // Del-List
		$this->adminContent .= 	'</ul>' . "\r\n";

		
		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();
		
		
		$this->adminContent .= 	'</div>' . "\r\n";
		
		$this->adminContent	.= $this->getBackButtons(parent::$type);
		
		$this->adminContent	.= $this->closeAdminContent();
		
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= $this->getGalleryRightBarContents();
	
	
		// Admin Tour Script
		$this->adminContent .=	$this->getGalleryAdminTourScript();

		
		return $this->adminContent;
	
	}



	/**
	 * getGalleriesByDir
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getGalleriesByDir()
	{
	
		if(is_dir(PROJECT_DOC_ROOT . '/' . $this->galleriesFolder)) {

			$handle = opendir(PROJECT_DOC_ROOT . '/' . $this->galleriesFolder);

			while($content = readdir($handle)) {
				$gallDir			= PROJECT_DOC_ROOT . '/' . $this->galleriesFolder . '/' . $content;
				if( strpos($content, ".") !== 0 && 
					is_dir($gallDir)
				) {
					$filedate			= filemtime($gallDir);
					$this->existGalls[]	= $content;
					$this->mediaDates[]	= $filedate;
					$this->picCount[]	= max(0, count(array_diff(scandir($gallDir), array('..', '.', 'thumbs', 'small', 'medium', '.quarantine', '.tmb')))); // Bildanzahl = Dateianzahl ohne ./, ../, thumbs/, small/, medium/ ...
				}
			}
			closedir($handle);
			
			natsort($this->existGalls);
		}
	}



	/**
	 * getGalleriesFromDB
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getGalleriesFromDB()
	{

		// Datenbanksuche zum Auslesen von Galeriebildern und Bildtexten
		$queryGall = $this->DB->query( "SELECT * 
										FROM `" . DB_TABLE_PREFIX . "galleries` 
										WHERE (`group` = 'public'
											OR `group` = '" . $this->loggedUserGroup . "') 
										ORDER BY `sort_id` DESC,`gallery_name`
										", false);
		
		#var_dump($queryGall);
		return $queryGall;
	
	}



	/**
	 * assignGallerieTags
	 * 
	 * @access	public
	 * @return	string
	 */
	public function assignGallerieTags()
	{

		if(empty($this->installedGalls))
			return false;
		
		foreach($this->installedGalls as $gall){			
			$this->gallTags[$gall['gallery_name']]	= $gall['tags'];
		}
		
		return $this->gallTags;
	
	}
	


	/**
	 * Gallery edit dialog
	 * 
	 * @access	public
	 * @return	string
	 */
	public function getGalleryEditDialog()
	{
		
		$output	= 	'<div id="dialog-form-gallery" style="display:none;" title="{s_title:gallparams}">' . "\r\n" .
					'<div class="adminStyle adminArea">' . "\r\n" .
					'<form action="' . $this->formAction . '" method="post" class="form">' . "\r\n" . 
					'<label for="newname-gallery">{s_label:gallname}</label>' . "\r\n" .
					'<p class="notice validateTips"></p>' . "\r\n" .
					'<input type="text" name="newname-gallery" id="newname-gallery" class="dialogName dialogInput text ui-widget-content ui-corner-all" value="" maxlength="64" />' . "\r\n" .
					'<span class="inline-tags">' . "\r\n" .
					'<label>{s_label:tags}</label><input type="text" name="gall_tags" id="gall_tags" class="cc-gallery-tags" value="" />' . "\r\n" .
					$this->getTagEditorScripts() .
					'</span>' . "\r\n" . 
					'<input type="hidden" name="oldname-gallery" id="oldname-gallery" class="dialogName dialogInput" value="" />' . "\r\n" .
					'<input type="hidden" name="scriptpath-gallery" id="scriptpath-gallery" value="' . SYSTEM_HTTP_ROOT . '/access/editModules.php?page=admin&action=rename&type=gallery"  />' . "\r\n" .
					'<input type="hidden" name="phrases-gallery" id="phrases-gallery" value="' . ContentsEngine::replaceStaText("{s_notice:nogallname}<>{s_notice:longname}<>{s_notice:wrongname}<>{s_notice:gallexists}") . '"  />' . "\r\n" .
					'<input type="hidden" name="buttonLabels-gallery" id="buttonLabels-gallery" value="' . ContentsEngine::replaceStaText("{s_button:takechange}<>{s_button:cancel}") . '"  />' . "\r\n" .
					'</form>' . "\r\n" .
					'</div>' . "\r\n" .
					'</div>' . "\r\n";

		return $output;
	
	}

	
	// getGalleryRightBarContents
	private function getGalleryRightBarContents()
	{
	
		// Panel for rightbar
		$output	= "";
		
		// New gallery
		$output .=	'<div class="controlBar">' . PHP_EOL;
		
		if(!$this->showGallList) {
			// Button showGallList
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=gallery',
									"class"		=> "{t_class:btnpri} {t_class:btnblock}",
									"text"		=> "{s_nav:admingallery}",
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "list"
								);
		
		}
		else {
			// Button add gallery
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=modules&type=gallery&name=',
									"class"		=> "{t_class:btnpri} {t_class:btnblock}",
									"text"		=> "{s_link:newgall}",
									"attr"		=> 'data-ajax="true"',
									"icon"		=> "new"
								);
		
		}
		
		$output		.=	parent::getButtonLink($btnDefs);
		
		$output .=	'</div>' . PHP_EOL;
		
		return $output;
		
	}
	
	
	// getGalleryAdminTourScript
	protected function getGalleryAdminTourScript()
	{
	
		return	'<script>
				head.ready(function(){
					head.load({hopscotch: "extLibs/jquery/hopscotch/js/hopscotch.min.js"}, function(){
						head.load("extLibs/jquery/hopscotch/css/hopscotch.min.css");
						head.load({admintourgallery: "system/inc/admintasks/modules/js/adminTour.gallery.min.js"}, function(){
							$("document").ready(function(){
								// Start tour on desktop devices
								if(!cc.isPhone()){
									$.gallery_AdminTour();
								}
							});
						});
					});
				});
				</script>' . PHP_EOL;
	
	}

}
