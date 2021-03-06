<?php
namespace Concise;


###################################################
##############  Templates-Bereich  ################
###################################################

// Templates verwalten 


class Admin_Tpl extends Admin implements AdminTask
{

	private $newTplAdded = false;
	private $themeColors = array();
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;

		$this->headIncludeFiles['colorpicker']	= true;
		$this->headIncludeFiles['filemanager']	= true;
		
		$this->scriptCSS['imagepickercss']		= "extLibs/jquery/image-picker/image-picker.css";
		$this->scriptFiles['imagepicker']		= "extLibs/jquery/image-picker/image-picker.min.js";

	}
	
	
	public function getTaskContents($ajax = false)
	{

		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:admintpl}' . PHP_EOL .
									'{s_text:admintpl1}' . PHP_EOL .
									$this->closeTag("#headerBox");
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();
		

		$this->isTemplateArea	= true;
		
		$newTpl			= "";
		$insertSQL		= true;
		$deleteSQL		= true;
		$currTheme		= THEME;
		$newTheme		= "";
		$copyTheme		= "";
		$allowedFiles	= array("jpg","png","gif","jpeg");
		$allowedTypes	= array("image/jpeg", "image/gif", "image/png", "image/x-png");
		$colorArr		= array();


		// Ggf. zu große POST-Requests abfangen
		if($checkPostSize	= $this->checkPostRequestTooLarge())
			$this->error	= $this->getNotificationStr(sprintf(ContentsEngine::replaceStaText("{s_error:postrequest}"), $checkPostSize), "error");

		
		// Falls ein Theme ausgewählt wurde
		if(isset($GLOBALS['_POST']['currTheme']) && $GLOBALS['_POST']['currTheme'] != "") {
			
			$oldTheme	= THEME;
			$currTheme	= $GLOBALS['_POST']['currTheme'];

			if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) die("settings file not found");
			else {
				
				$replace = preg_replace("/'THEME',\"$oldTheme\"/", "'THEME',\"$currTheme\"", $settings);
											
				if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $replace)) {
					@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings);
					die("could not write settings file");
				}
				else {
					
					// Falls noch ein Preeview-Theme ausgewählt war, Cookie löschen
					if(isset($GLOBALS['_COOKIE']['previewTheme']))
						setcookie("previewTheme", "", time()-3600);

					$this->setSessionVar('notice', "{s_notice:themeactive} <strong>" . $currTheme . "</strong>");
					header("Location:" . ADMIN_HTTP_ROOT . "?task=tpl");
					exit;
				}
			}
		}

		// Falls das Theme nicht gefunden wurde,
		if(!is_dir(PROJECT_DOC_ROOT . '/themes/' . $currTheme)) {
			
			// Meldung ausgeben und auf default zurücksetzen
			$notice = "{s_notice:themeactive} <strong>" . $currTheme . "</strong>";

			if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) die("settings file not found");
			else {
				
				$replace = preg_replace("/'THEME',\"$currTheme\"/", "'THEME',\"default\"", $settings);
											
				if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $replace)) {
					@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings);
					die("could not write settings file");
				}
				else {
					$this->setSessionVar('error', "{s_notice:themenotfound} <strong>" . $currTheme . "</strong>");
					header("Location:" . ADMIN_HTTP_ROOT . "?task=tpl");
					exit;
				}
			}
		}


		// Falls ein Template ausgewählt wurde
		if(isset($GLOBALS['_POST']['template']) && $GLOBALS['_POST']['template'] != "")
			$currTpl = $GLOBALS['_POST']['template'];

		elseif(isset($GLOBALS['_POST']['edit_tpl']) && $GLOBALS['_POST']['edit_tpl'] != "")
			$currTpl = $GLOBALS['_POST']['edit_tpl'];

		elseif(isset($GLOBALS['_GET']['edit_tpl']) && $GLOBALS['_GET']['edit_tpl'] != "")
			$currTpl = $GLOBALS['_GET']['edit_tpl'];

		elseif(isset($GLOBALS['_COOKIE']['edit_id']) && strpos($GLOBALS['_COOKIE']['edit_id'], ".tpl") !== false)
			$currTpl = $GLOBALS['_COOKIE']['edit_id'];

		else
			$currTpl = CC_DEFAULT_TEMPLATE;


		// Falls das Template nicht existiert, standard.tpl auswählen
		if(!file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $currTpl))
			$currTpl = CC_DEFAULT_TEMPLATE;

		
		// Aktuelles Template merken
		setcookie('edit_id', $currTpl); // Bewirkt aufklappen des Untermenüs bei der Seitenliste
		setcookie('sort_id', $currTpl);

		
		// Falls ein neues Template angelegt werden soll
		if(isset($GLOBALS['_POST']['newTpl'])) {
		
			$newTpl = trim($GLOBALS['_POST']['newTpl']);
			
			if($newTpl == "")
				$errorName = "{s_error:notplname}";

			elseif(!preg_match("/^[A-Za-z0-9-_]+$/", $newTpl))
				$errorName = "{s_error:wrongname}";

			elseif(strtolower($newTpl) == "admin" || strtolower($newTpl) == "contents" || strtolower($newTpl) == "contents_edit" || strtolower($newTpl) == "install")
				$errorName = "{s_error:wrongname3}";

			elseif(strlen($newTpl) > 60)
				$errorName = "{s_error:longname}";

			elseif(file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $newTpl . '.tpl'))
				$errorName = "{s_error:tplexists}";

			else {
				$currTpl = $newTpl . '.tpl';
				$newTplDb = $this->DB->escapeString($currTpl);
				
				foreach(parent::$tablesTplContents as $table) {
				
					$table		= DB_TABLE_PREFIX . $table;
					
					// Db-Tabellen updaten
					$insertSQL1a = $this->DB->query("INSERT INTO `" . $table . "`  
															SET `page_id` = '$newTplDb'
															");
			
					#var_dump($insertSQL1a);
					
					$insertSQL1b = $this->DB->query("INSERT INTO `" . $table . "_preview` 
															SET `page_id` = '$newTplDb'
															");
			
					#var_dump($insertSQL1b);
					if($insertSQL1a == false || $insertSQL1b == false)
						$insertSQL = false;
				}
				
				if($insertSQL == true) {
					
					if($GLOBALS['_POST']['copy_tpl'] != "" && file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $GLOBALS['_POST']['copy_tpl']))
						$copyTpl = $GLOBALS['_POST']['copy_tpl'];
					else
						$copyTpl = CC_DEFAULT_TEMPLATE;
					
					copy(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $copyTpl, PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $newTpl . '.tpl');
					$notice = "{s_notice:newtpl} <strong>" . $newTpl . "</strong>";
					$this->newTplAdded = true;
				}

			}
		}
			
		// Falls ein Template gelöscht werden soll
		if(!empty($GLOBALS['_POST']['del_tpl'])
		&& !$this->isProtectedTpl($GLOBALS['_POST']['del_tpl'])
		) {
			
			$delTpl		= $GLOBALS['_POST']['del_tpl'];			
			$delTplDb	= $this->DB->escapeString($delTpl);
			
			foreach(parent::$tablesTplContents as $table) {
			
				$table		= DB_TABLE_PREFIX . $table;
				
				// Db-Tabellen updaten
				$deleteSQL1a = $this->DB->query("DELETE FROM `" . $table . "` 
														WHERE `page_id` = '$delTplDb'
														");

				#var_dump($deleteSQL1a);
				
				$deleteSQL1b = $this->DB->query("DELETE FROM `" . $table . "_preview` 
														WHERE `page_id` = '$delTplDb'
														");

				#var_dump($deleteSQL1b);
				
				$updateSQL = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . parent::$tablePages . "` 
													SET template = '" . CC_DEFAULT_TEMPLATE . "' 
													WHERE template = '$delTplDb'
													");

				#var_dump($updateSQL);
				
				if($deleteSQL1a == false || $deleteSQL1b == false)
					$deleteSQL = false;
			}
			
			if($deleteSQL == true) {
				if(file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $delTpl)) {
					unlink(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $delTpl);
					$this->setSessionVar('notice', "{s_notice:deltpl}");
					header("Location:" . ADMIN_HTTP_ROOT . "?task=tpl");
					exit;
				}
			}
		}


		// Falls das Formular zum Bearbeiten von HTML abgeschickt wurde
		if(isset($GLOBALS['_POST']['edit_tplHtml'])) {
			$tplHtml = str_replace("{#","{",$GLOBALS['_POST']['edit_tplHtml']);
			#$tplHtmlOld = DebugConsole::printCode(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $currTpl);
			@file_put_contents(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $currTpl, str_replace("{#","{",$tplHtml));
			$notice = "{s_notice:takechange}";	
		}
		else {
			if(file_exists(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $currTpl))
				$tplHtml = @file_get_contents(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $currTpl);
			else
				$tplHtml = "Template file not found.";
		}

		// HTML-Code formatiert
		#$tplHtmlOld = DebugConsole::printCode(PROJECT_DOC_ROOT . '/' . TEMPLATE_DIR . $currTpl);



		// Falls ein neues Theme angelegt werden soll
		if(isset($GLOBALS['_POST']['newTheme'])) {
			$newTheme = trim($GLOBALS['_POST']['newTheme']);
			
			if($newTheme == "")
				$errorThemeName = "{s_error:nothemename}";

			elseif(!preg_match("/^[A-Za-z0-9-_]+$/", $newTheme))
				$errorThemeName = "{s_error:wrongname}";

			elseif(strtolower($newTheme) == "admin" || strtolower($newTheme) == "contents" || strtolower($newTheme) == "contents_edit" || strtolower($newTheme) == "install")
				$errorThemeName = "{s_error:wrongname3}";

			elseif(strlen($newTheme) > 64)
				$errorThemeName = "{s_error:longname}";

			elseif(is_dir(PROJECT_DOC_ROOT . '/themes/' . $newTheme))
				$errorThemeName = "{s_error:themeexists}";

			else {
				
				if($GLOBALS['_POST']['copyTheme'] != "" && is_dir(PROJECT_DOC_ROOT . '/themes/' . $GLOBALS['_POST']['copyTheme']))
						$copyTheme = $GLOBALS['_POST']['copyTheme'];
					else
						$copyTheme = 'default';
					
				// Themeordner kopieren
				self::copyRecursive(PROJECT_DOC_ROOT  . '/themes/' . $copyTheme, PROJECT_DOC_ROOT . '/themes/' . $newTheme, "cache");
				
				$notice = "{s_notice:newtheme} <strong>" . $newTheme . "</strong>";
				$newTheme = "";

			}
		}
			
			
		// Falls ein Theme gelöscht werden soll
		if (isset($GLOBALS['_POST']['del_theme']) && 
			$GLOBALS['_POST']['del_theme'] != "" && 
			$GLOBALS['_POST']['del_theme'] != "default" && 
			$GLOBALS['_POST']['del_theme'] != THEME
		) {
			
			$delTheme = $GLOBALS['_POST']['del_theme'];
			
			if(is_dir(PROJECT_DOC_ROOT . '/themes/' . $delTheme)) {
				if(self::unlinkRecursive(PROJECT_DOC_ROOT . '/themes/' . $delTheme, true)) {
					$this->setSessionVar('notice', "{s_notice:deltheme}");
					header("Location:" . ADMIN_HTTP_ROOT . "?task=tpl");
					exit;
				}
			}
		}


		// Falls eine Theme-Bilddatei hochgeladen werden soll
		if(isset($GLOBALS['_POST']['uploadThemeImage'])) {

			$error		= array();
			$errorMes	= "";
			$success	= true;
			$emptyArray	= true;
						
			if(isset($GLOBALS['_FILES']['upload']) 
			&& $GLOBALS['_FILES']['upload']['name'][0] != ""
			) {
			
				foreach($GLOBALS['_FILES']["upload"]["name"] as $key => $upload_file) {
						
					if(isset($GLOBALS['_POST']['selFiles']) && preg_match("/".$upload_file."/", $GLOBALS['_POST']['selFiles'])) {
			
						if(in_array($GLOBALS['_FILES']['upload']['type'][$key], $allowedTypes)
						&& preg_match("/image/", $GLOBALS['_FILES']['upload']['type'][$key])
						) {
							
							$fileType	= "theme-image";
							$emptyArray	= false;
								
							$upload_tmpfile = $GLOBALS['_FILES']['upload']['tmp_name'][$key];
							
							// Datei-Upload starten
							$upload = Files::uploadFile($upload_file, $upload_tmpfile, IMAGE_DIR, $fileType, 0, 0, true, "", "", false);
							
							if($upload !== true) {
								$error[] = "<li><strong>" . $upload_file . "</strong><br />" . $upload . "</li>"; // Falls Upload fehlerhaft, Meldung in Array speichern
								$success = false;
							}
						}
						else {
							$error[] = "<li><strong>" . $upload_file . "</strong><br />{s_error:wrongtype1} - ".$GLOBALS['_FILES']['upload']['type'][$key]."</li>"; // Falls Upload fehlerhaft, Meldung in Array speichern
							$success = false;
						}
					}
					
				} // Ende foreach
			
			} // Ende if not empty
			
			if(count($error) > 0)
				$notice2 = '<p class="error">{s_error:file}</p><ul id="errorMes">' . implode("", $error) . '</ul>' . PHP_EOL;
			
			elseif($success == true && $emptyArray == false)
				$notice2 = '<p class="notice success">{s_notice:fileok}</p>' . PHP_EOL;
				
		}


		// Inhalte der Layout-Datei für Theme-Farben einlesen
		$cssPath = PROJECT_DOC_ROOT . '/themes/' . THEME . '/css/';
		$colorsArr	= array();
		$cssContent = "";
		
		if(file_exists($cssPath . 'bootstrap.min.css')) {
			
			$cssContent = @file_get_contents($cssPath . 'bootstrap.min.css');			
			$colorsArr	= $this->getThemeColors($cssContent);
		}
		#else
		#	die("file not found: layout.css");


		// Falls die Theme-Farben geändert werden sollen
		if(!empty($colorsArr)
		&& isset($GLOBALS['_POST']['submitColors'])
		&& count($GLOBALS['_POST']['colors']) > 0
		) {
			
			$colors = $GLOBALS['_POST']['colors'];
			
			$handle = opendir($cssPath);
			
			while($content = readdir($handle)) {
				
				if( $content != ".."
				&& strpos($content, ".") !== 0
				&& !is_dir($cssPath . $content)
				&& $content != "layout_print.css"
				&& $content != "fe-edit.css"
				&& $content != "fe-edit.min.css"
				&& pathinfo($cssPath . $content, PATHINFO_EXTENSION) == "css"
				) {
				
					$c = 0;
					
					// Datei auslesen
					$getCss		= file_get_contents($cssPath . $content);
					$replColor	= $getCss;
					
					foreach($colors as $newColor) {
						
						$newColorHex	= strtoupper($newColor);
						$oldColorHex	= str_replace("#", "", $colorsArr[$c]);
						
						$newColorArr	= str_split($newColorHex, 2);
						$oldColorArr	= str_split($oldColorHex, 2);
						$newColorDecArr	= array();
						$oldColorDecArr	= array();
						
						for($i = 0; $i < 3; $i++) {
							
							$newColorDecArr[$i] = hexdec($newColorArr[$i]);
							$oldColorDecArr[$i] = hexdec($oldColorArr[$i]);
							
						}
						
						$newColorDec	= implode(",", $newColorDecArr);
						$oldColorDec	= implode(",", $oldColorDecArr);
						
						$replColor		= str_ireplace("#" . $oldColorHex, "#" . $newColorHex, $replColor);
						$replColor		= str_replace($oldColorDec, $newColorDec, $replColor);
						
						// 3er code
						if($oldColorArr[0][0] == $oldColorArr[0][1]
						&& $oldColorArr[1][0] == $oldColorArr[1][1]
						&& $oldColorArr[2][0] == $oldColorArr[2][1]
						) {
						
							$oldColorTriplet = $oldColorArr[0][0] . $oldColorArr[1][0] . $oldColorArr[2][0];
							
							if($oldColorTriplet != ""
							&& strlen($oldColorTriplet) === 3
							) {
								$replColor	= str_ireplace("#" . $oldColorTriplet . " ", "#" . $newColorHex . " ", $replColor);
								$replColor	= str_ireplace("#" . $oldColorTriplet . ";", "#" . $newColorHex . ";", $replColor);
								$replColor	= str_ireplace("#" . $oldColorTriplet . ")", "#" . $newColorHex . ")", $replColor);
							}
						}
						
						$c++;
					}

					// Datei schreiben
					$putCss		= file_put_contents($cssPath . $content, $replColor);
				}
			}
			closedir($handle);
			
			$this->setSessionVar('notice', "{s_notice:changecolor}");
			header("Location:" . ADMIN_HTTP_ROOT . "?task=tpl");
			exit;
		}


		// Theme-Selectoren generieren
		$optionsSelTheme	= $this->listThemes($currTheme, "all", "options");
		$optionsCopyTheme	= $this->listThemes($currTheme, "new");
		$optionsDelTheme	= $this->listThemes($currTheme, "del");

		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL;


		// Meldungen
		// Auf Fehlermeldung überprüfen			
		$this->adminContent .=	$this->getSessionNotifications("error", true);

		// Notice
		if($this->notice	= $this->getSessionNotifications("notice") != "")
			$notice = $this->notice;
		

		if(isset($notice))
			$this->adminContent .='<p class="notice success">' . $notice . '</p>' . PHP_EOL;

		if(isset($notice2))
			$this->adminContent .= $notice2;

		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_header:admintpl}</h2>' . PHP_EOL .
								'<div class="controlBar">' . PHP_EOL .
								'<div id="themeSelectionBox" class="choose imagePicker">' . PHP_EOL .
								'<form action="" id="chooseTpl" method="post">' . PHP_EOL .
								'<div class="leftBox">' . PHP_EOL .
								'<div class="iconBox">' .
								parent::getIcon("theme") .
								'</div>' . PHP_EOL . 
								'<label class="label" title="{s_title:choosetheme}">{s_label:activetheme} <span>&#9658;</span> <strong>' . $currTheme . '</strong> </label>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'<div class="selTheme rightBox">' . PHP_EOL .
								'<span class="singleInput-panel">' . PHP_EOL;
		
		$this->adminContent .=	'<select name="currTheme" id="currTheme" class="select themes input-button-right" onfocus="this.blur();" onclick="this.blur();" data-toggle="image_picker_selector">' . PHP_EOL . 
								$optionsSelTheme . PHP_EOL .  
								'</select>' . PHP_EOL;
		
		// Button toggle themes
		$btnDefs	= array(	"type"		=> "button",
								"name"		=> "toggleThemes",
								"class"		=> '{t_class:btndef} button-small',
								"value"		=> htmlspecialchars($newTpl),
								"text"		=> "{s_label:theme}",
								"attr"		=> 'data-toggle="image_picker_selector"',
								"title"		=> '{s_title:choosetheme}',
								"icon"		=> 'theme'
							);
		
		$this->adminContent .=	'<span class="right">' . PHP_EOL;
		
		$this->adminContent .=	parent::getButton($btnDefs, "right");
		
		$this->adminContent .=	'</span>' . PHP_EOL;
		
		$this->adminContent .=	'</span>' . PHP_EOL .
								$this->getScriptTag() .
								'</div>' . PHP_EOL;
		
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL;

		if(isset($editId) && $editId != "")
			$this->adminContent .=	'<input type="hidden" name="edit_id" value="' . $editId . '" />' . PHP_EOL;

		if(isset($GLOBALS['_POST']['edit_area']) && $GLOBALS['_POST']['edit_area'] != "") {
			$this->adminContent .=	'<input type="hidden" name="edit_area" value="' . htmlspecialchars($GLOBALS['_POST']['edit_area']) . '" />' . PHP_EOL .
									'<input type="hidden" name="edit_tpl" value="' . $editId . '" />' . PHP_EOL;
		}

		elseif(isset($GLOBALS['_GET']['edit_area']) && $GLOBALS['_GET']['edit_area'] != "") {
			$this->adminContent .=	'<input type="hidden" name="edit_area" value="' . $GLOBALS['_GET']['edit_area'] . '" />' . PHP_EOL .
									'<input type="hidden" name="edit_tpl" value="' . $editId . '" />' . PHP_EOL;
		}

		$this->adminContent .= 	'</form>' . PHP_EOL .
								'</div></div>' . PHP_EOL;

		// Template auswählen/bearbeiten
		$this->adminContent .=	'<h3 class="cc-h3 switchToggle">{s_header:tpledit}</h3>' . PHP_EOL . 
								'<div class="adminBox">' . PHP_EOL .
								'<ul class="framedItems">' . PHP_EOL .
								'<li>' . PHP_EOL .
								'<span class="rightBox">' . PHP_EOL .
								'<form action="' . ADMIN_HTTP_ROOT . '?task=tpl" class="adminfm1" method="post">' . PHP_EOL . 
								'<label for="newTpl">{s_label:newtpl}</label>' . PHP_EOL;

		if(isset($errorName))
			$this->adminContent .='<p class="notice">' . $errorName . '</p>' . PHP_EOL;
			
		$this->adminContent .=	'<span class="singleInput-panel">' . PHP_EOL;

		$this->adminContent .=	'<input type="text" id="newTpl" class="input-button-right" name="newTpl" value="' . (!$this->newTplAdded ? htmlspecialchars($newTpl) : '') . '" maxlength="60" />' . PHP_EOL . 
								'<span class="editButtons-panel">' . PHP_EOL;
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "new_tpl",
								"class"		=> 'newTpl ajaxSubmit button-icon-only',
								"value"		=> htmlspecialchars($newTpl),
								"text"		=> "",
								"title"		=> '{s_label:newtpl}',
								"icon"		=> "new"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
								
		$this->adminContent .=	'</span>' . PHP_EOL .
								'</span>' . PHP_EOL .
								'<input type="hidden" name="new_tpl" value="' . htmlspecialchars($newTpl) . '" />' . PHP_EOL . 
								'<input type="hidden" name="copy_tpl" value="' . htmlspecialchars($currTpl) . '" />' . PHP_EOL . 
								parent::getTokenInput() . 
								'</form>' . PHP_EOL .
								'</span>' . PHP_EOL;
							
		// Templates auflisten
		// Existing Tempates
		$this->existTemplates	= parent::readTemplateDir();
			
		$this->adminContent .=	'<span class="leftBox">' . PHP_EOL .
								'<label class="tplSelect-label">Template</label>' . PHP_EOL . 
								'<span class="singleInput-panel panel-small">' . PHP_EOL .
								'<form action="' . ADMIN_HTTP_ROOT . '?task=tpl" method="post">' . PHP_EOL .
								parent::listTemplates($currTpl, $this->defaultTemplates, $this->existTemplates, "select", true) .  // Select zum Zuordnen des Templates
								parent::getTokenInput() . 
								'</form>' . PHP_EOL;
			
			
		// Falls kein geschützes Template
		if(!$this->isProtectedTpl($currTpl)) {
			
			$this->adminContent .=	'<form action="' . ADMIN_HTTP_ROOT . '?task=tpl" class="adminfm1" method="post">' . PHP_EOL;
			$this->adminContent .=	'<span class="editButtons-panel">' . PHP_EOL;
			
			// Button delete
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'delTpl button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:deltpl}',
									"attr"		=> 'data-action="deltpl"',
									"icon"		=> "delete"
								);
			
			$this->adminContent .=	parent::getButton($btnDefs);
		
			$this->adminContent .=	'<input type="hidden" name="del_tpl" id="del_tpl" />' . PHP_EOL . 
									parent::getTokenInput() . 
									'</span>' . PHP_EOL .
									'</form>' . PHP_EOL;
		}
		
		$this->adminContent .=	'</span>' . PHP_EOL;
		$this->adminContent .=	'</span>' . PHP_EOL;
		
		// tplSelectionBox
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL . 
								'<div id="tplSelectionBox" class="choose imagePicker">' . PHP_EOL .
								'</div>' . PHP_EOL;
							
		$this->adminContent .=	'<br class="clearfloat" /></li>' . PHP_EOL . 
								'</ul>' . PHP_EOL .
								'</div>' . PHP_EOL;
							
		// Templateinhalte					
		$this->adminContent .=	'<h3 class="cc-h3 switchToggle' . (isset($errorName) || isset($errorThemeName) ? ' hideNext' : '') . '">{s_header:tplcon} - <span class="right">' . $currTpl . '</span></h3>' . PHP_EOL . 
								'<div class="adminBox">' . PHP_EOL .
								'<ul class="editList template">' . PHP_EOL;

		
		$tc = 0;
		
		// Templatebreiche
		foreach(parent::$tablesTplContents as $conTab) {
		
			$tplArea			= parent::$areasTplContents[$tc];
			$changesButtons		= "";
			$areaPH				= strtoupper($tplArea);
			$areaPHexists		= strpos($tplHtml, "{" . $areaPH . "}");
			$noAreaPH			= parent::getIcon('info', 'noTplPH tableCell', 'title="{s_notice:noareaph}"');
			
			#die(print_r($this->diffConTables));
			// Falls Änderungen bestehen, die übernommen werden können, Buttons einbinden
			if(array_key_exists($conTab, $this->diffConTables) && in_array($currTpl, $this->diffConTables[$conTab]) ) {
			
				$changesButtons =	'<span class="changesButtons-panel editButtons-panel panel-inline panel-right" data-id="contextmenu-b' . $tc . '">' . PHP_EOL;
				
				// Button apply
				$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=1&edit_tpl=' . $currTpl . '&edit_area=' . $conTab . '&edit=0',
										"class"		=> "goLive change button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_link:changes}",
										"icon"		=> "apply",
										"attr"		=> 'data-action="applychanges" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_link:changes}" data-id="item-id-' . $tc . '"'
									);
				
				$changesButtons	.=	parent::getButtonLink($btnDefs);
				
				// Button cancel
				$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=changes&affect=0&edit_tpl=' . $currTpl . '&edit_area=' . $conTab . '&edit=0',
										"class"		=> "cancel button-icon-only button-small",
										"text"		=> "",
										"title"		=> "{s_link:nochanges}",
										"icon"		=> "cancel",
										"attr"		=> 'data-action="discardchanges" data-menuitem="true" data-contextmenuitem="true" data-menutitle="{s_link:nochanges}" data-id="item-id-' . $tc . '"'
									);
			
				$changesButtons	.=	parent::getButtonLink($btnDefs);
				
				$changesButtons .=	'</span>' . PHP_EOL;
			
			}
			
			$this->adminContent .=	'<li class="listItem' . (!$areaPHexists ? ' inactive' : '') . '" data-menu="context" data-target="contextmenu-a' . $tc . ',contextmenu-b' . $tc . '">' . PHP_EOL .
									'<span class="listIcon">' . PHP_EOL .
									parent::getIcon('area-' . $tplArea) .
									'</span>' . PHP_EOL .
									'<span class="pageTitle">{s_conareas:' . $conTab . '}</span>' . PHP_EOL .
									'<span class="tplArea-label pageID"> {#' . $areaPH . '}' .
									($areaPHexists === false ? $noAreaPH : '') .
									'</span>' . PHP_EOL .
									$changesButtons .
									'<span class="editButtons-panel" data-id="contextmenu-a' . $tc . '">' . PHP_EOL;
			
			// Button edit
			$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=tpl&type=edit&edit_id=' . $currTpl . '&area=contents_' . $tplArea,
									"class"		=> 'editTpl button-icon-only',
									"text"		=> "",
									"title"		=> '{s_label:edittpl}',
									"attr"		=> 'data-ajax="true" data-menuitem="true" data-id="item-id-' . $tc . '"',
									"icon"		=> "edit"
								);
			
			$this->adminContent .=	parent::getButtonLink($btnDefs);
									
			$this->adminContent .=	'</span>' . PHP_EOL .
									'</li>' . PHP_EOL;
			
			$tc++;
		}					

		$this->adminContent .=	'</ul>' . PHP_EOL;
		

		// Contextmenü-Script
		$this->adminContent .=	$this->getContextMenuScript();


		$this->adminContent .=	'</div>' . PHP_EOL;
		
		
		// Buttons
		$buttons 	=	'<ul>' . PHP_EOL . 
						'<li class="submit change buttonPanel buttonpanel-nofix">' . PHP_EOL;
		
		// Button reset
		$btnDefs	= array(	"type"		=> "reset",
								"name"		=> "reset1",
								"class"		=> "codeMirrorEditor-reset reset right",
								"value"		=> "{s_button:reset}",
								"icon"		=> "reset"
							);
		
		$buttons	.=	parent::getButton($btnDefs);
			
		// Button redo
		$btnDefs	= array(	"name"		=> "history-redo",
								"class"		=> "codeMirrorEditor-history-redo redo button-icon-only right",
								"title"		=> "{s_button:redo} [Ctrl+z]",
								"icon"		=> "redo"
							);
		
		$buttons	.=	parent::getButton($btnDefs);
			
		// Button undo
		$btnDefs	= array(	"name"		=> "history-undo",
								"class"		=> "codeMirrorEditor-history-undo undo button-icon-only right",
								"title"		=> "{s_button:undo} [Ctrl+z]",
								"icon"		=> "undo"
							);
		
		$buttons	.=	parent::getButton($btnDefs);
		
		
		// Button submit (edit)
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "submit1",
								"class"		=> "change",
								"value"		=> "{s_button:savechanges}",
								"icon"		=> "ok"
							);
		
		$buttons	.=	parent::getButton($btnDefs);
			
		$buttons	.=	'<input name="edit_tpl" type="hidden" value="' . $currTpl . '" />' . PHP_EOL . 
						parent::getTokenInput() . 
						'</li></ul>' . PHP_EOL;

		// Filemanager
		$fileManager	=	'<ul class="folderList">' . PHP_EOL .
							'<li class="manageFiles buttonPanel">' . PHP_EOL .
							parent::getIcon('info', 'tooltipHint right', 'title="{s_hint:filemanager}<br />{s_header:admintpl}"');
							
		// Filemanager MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "filemanager",
											"type"		=> "filemanager",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&action=elfinder&root=themes",
											"value"		=> "{s_label:filemanager}",
											"icon"		=> "filemanager"
										);
		
		$fileManager .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$fileManager .=		'</li>' . PHP_EOL .
							'</ul>' . PHP_EOL;

		// Template-HTML
		$this->adminContent .=	'<h3 class="cc-h3 codeMirrorToggle switchToggle hideNext">{s_header:tpl}</h3>' . PHP_EOL . 
								'<div class="adminBox">' . PHP_EOL .
								'<form name="changeTpl" action="' . ADMIN_HTTP_ROOT . '?task=tpl" method="post">' . PHP_EOL . 
								'<ul class="adminBox">' . PHP_EOL .
								'<li>' . PHP_EOL .
								'<h4 class="cc-h4 codeMirrorEditor-targetfile">' . THEME . '/' . $currTpl . '</h4>' . PHP_EOL .
								'<span class="codeMirrorEditor">' . PHP_EOL .
								'<span class="codeMirrorEditor-content">' . PHP_EOL;
							
		$this->adminContent .=	'<div class="codeMirrorEditor-targetfile boxHeader ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">' . THEME . '/' . $currTpl . PHP_EOL;
		
		// Close Button
		$mediaListButtonDef		= array(	"type"		=> "button",
											"class"	 	=> "toggleFullScreen button-icon-only right button-small ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close",
											"value"		=> "{s_title:close}",
											"title"		=> "{s_title:close}",
											"text"		=> "",
											"icon"		=> "close"
										);
		
		$this->adminContent .=	parent::getButton($mediaListButtonDef);
								
		$this->adminContent .=	'</div>' . PHP_EOL .
								'<textarea name="edit_tplHtml" id="editTplCode" class="template customList noTinyMCE" rows="20">' . htmlentities(str_replace("{","{#",$tplHtml)) . '</textarea>' . PHP_EOL .
								'</span>' . PHP_EOL . 
								'<ul>' . PHP_EOL .
								'<li class="toggleFullScreen-panel buttonPanel buttonPanel-last">' . PHP_EOL;
			
			// Button toggleFullScreen
			$btnDefs	= array(	"type"		=> "button",
									"class"		=> "toggleFullScreen right",
									"value"		=> "expand/collapse",
									"icon"		=> "toggle"
								);
			
			$this->adminContent .=	parent::getButton($btnDefs);
	
			$this->adminContent .=	'<br class="clearfloat" /></li></ul>' . PHP_EOL . 
									$buttons .
									#'<div class="code hide" onclick="if(this.style.width==\'1000px\') this.style.width=\'auto\'; else this.style.width=\'1000px\'; this.style.position=\'relative\'">' . $tplHtmlOld . '</div>' . PHP_EOL . 
									'<br class="clearfloat" />' . PHP_EOL .
									'</span>' . PHP_EOL .
									'</li>' . PHP_EOL .
									'</ul>' . PHP_EOL .
									'</form>' . PHP_EOL;
		
		// Filemanager
		$this->adminContent .=	'<ul class="framedItems">' . PHP_EOL .
								'<li class="manageFiles buttonPanel buttonpanel-nofix">' . PHP_EOL .
								parent::getIcon('info', 'tooltipHint right', 'title="{s_hint:filemanager}<br />{s_header:admintpl}"');
							
		// Filemanager MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "filemanager",
											"type"		=> "filemanager",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&action=elfinder&root=themes",
											"value"		=> "{s_label:filemanager}",
											"icon"		=> "filemanager"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$this->adminContent .=	'</li>' . PHP_EOL .
								'</ul>' . PHP_EOL;
		
		$this->adminContent .=	'</div>' . PHP_EOL;

		
		// Themes kopieren/löschen
		$this->adminContent .=	'<h3 class="cc-h3 switchToggle' . (isset($errorThemeName) ? '' : ' hideNext') . '">{s_header:themes}</h3>' . PHP_EOL . 
								'<div class="adminBox">' . PHP_EOL .
								'<form action="' . ADMIN_HTTP_ROOT . '?task=tpl" class="adminfm1" method="post">' . PHP_EOL .
								'<ul class="framedItems">' . PHP_EOL .
								'<li>' . PHP_EOL;
								
		$this->adminContent .=	'<span class="leftBox">' . PHP_EOL .
								'<label>{s_label:newtheme}</label>' . PHP_EOL;
								
								
		if(isset($errorThemeName))
			$this->adminContent .='<p class="notice">' . $errorThemeName . '</p>' . PHP_EOL;

		$this->adminContent .=	'<span class="singleInput-panel panel-small">' . PHP_EOL .
								'<input type="text" class="input-button-right" name="newTheme" value="' . htmlspecialchars($newTheme) . '" maxlength="64" />' . PHP_EOL . 
								'<span class="editButtons-panel">' . PHP_EOL;

		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "new_theme",
								"class"		=> 'newTpl ajaxSubmit button-icon-only',
								"value"		=> htmlspecialchars($newTheme),
								"text"		=> "",
								"title"		=> '{s_label:newtheme}',
								"icon"		=> "new"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
								
		$this->adminContent .=	'</span>' . PHP_EOL .
								'</span>' . PHP_EOL .
								'<input type="hidden" name="new_theme" value="' . htmlspecialchars($newTheme) . '" />' . PHP_EOL . 
								'<input type="hidden" name="copy_theme" value="' . htmlspecialchars($copyTheme) . '" />' . PHP_EOL . 
								parent::getTokenInput() . 
								'</span>' . PHP_EOL;
			
		$this->adminContent .=	'<span class="rightBox">' . PHP_EOL .
								'<label>{s_label:ascopy}</label>' . PHP_EOL;
								
		if(isset($errorThemeName))
			$this->adminContent .='<p class="notice">&nbsp;</p>' . PHP_EOL;
								
		$this->adminContent .=	'<div class="selTheme">' . PHP_EOL .
								'<span class="singleInput-panel">' . PHP_EOL .							
								'<select name="copyTheme" class="template themes">' . PHP_EOL .
								$optionsCopyTheme .  
								'</select>' . PHP_EOL .
								'</span>' . PHP_EOL .
								'</div>' . PHP_EOL .
								'</span>' . PHP_EOL .
								'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL .
								'</ul>' . PHP_EOL .
								'</form>' . PHP_EOL;
		
		// Theme löschen
		if($optionsDelTheme != "") {
			
			$this->adminContent .=	'<ul class="framedItems">' . PHP_EOL .
									'<li>' . PHP_EOL .
									'<form action="' . ADMIN_HTTP_ROOT . '?task=tpl" class="adminfm1" method="post">' . PHP_EOL .
									'<span class="leftBox">' . PHP_EOL .
									'<label>{s_label:deltheme}</label>' . PHP_EOL .
									'<span class="singleInput-panel panel-small">' . PHP_EOL .							
									'<div class="selTheme">' . PHP_EOL .
									'<select name="del_theme" class="template themes">' . PHP_EOL .
									$optionsDelTheme .
									'</select></div>' . PHP_EOL . 
									'<span class="editButtons-panel">' . PHP_EOL;

			// Button delete
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'del_theme button-icon-only',
									"text"		=> "",
									"title"		=> '{s_title:deltheme}',
									"attr"		=> 'data-action="deltheme"',
									"icon"		=> "delete"
								);
			
			$this->adminContent .=	parent::getButton($btnDefs);
									
			$this->adminContent .=	parent::getTokenInput() . 
									'</span>' . PHP_EOL .
									'</span>' . PHP_EOL .
									'</span>' . PHP_EOL .
									'</form>' . PHP_EOL .
									'<br class="clearfloat" /></li>' . PHP_EOL;
									'</ul>' . PHP_EOL;
		}
		
		// Filemanager
		$this->adminContent .=	$fileManager;
		
		$this->adminContent .=	'</div>' . PHP_EOL;
		
		
		// Theme-Graphiken
		$this->adminContent .=	'<h3 class="cc-h3 switchToggle hideNext">{s_header:tplfile}</h3>' . PHP_EOL . 
								'<div class="adminBox">' . PHP_EOL .
								'<form name="uploadfm" action="' . ADMIN_HTTP_ROOT . '?task=tpl" method="post" enctype="multipart/form-data" data-ajax="false">' . PHP_EOL . 
								'<ul class="framedItems">' . PHP_EOL . 
								'<li class="submit buttonPanel buttonpanel-nofix">' . PHP_EOL;
		
		// Button upload
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> "uploadButton right forceShow",
								"value"		=> "{s_button:upload}",
								"icon"		=> "upload"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
		
		$this->adminContent .=	'<input type="hidden" name="uploadThemeImage" value="" />' . PHP_EOL . 
								'<input type="file" id="upload" class="upload-themefile" name="upload[]" multiple="true" maxlength="10" accept="' . implode("|", $allowedFiles) . '" />' . PHP_EOL . 
								'<input type="hidden" name="selFiles" id="selFiles" />' . PHP_EOL . 
								parent::getTokenInput() . 
								parent::getIcon("warning", "inline-icon", 'title="{s_title:overwrite}"') .
								'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL .
								'<ul id="uploadFilesList" class="framedItems">' . PHP_EOL . 
								'<li>{s_text:upload}: ' . (implode(", ", $allowedFiles)) . '</li>' . PHP_EOL . 
								'</ul>' . PHP_EOL .
								'<li>' . PHP_EOL .
								'<div style="position:relative">' . PHP_EOL;
							
		// Images MediaList-Button
		$mediaListButtonDef		= array(	"class"	 	=> "images",
											"type"		=> "images",
											"url"		=> SYSTEM_HTTP_ROOT . "/access/listMedia.php?page=admin&type=theme&action=del",
											"value"		=> "{s_button:themefolder}",
											"icon"		=> "image"
										);
		
		$this->adminContent .=	$this->getButtonMediaList($mediaListButtonDef);
		
		$this->adminContent .=	'</div>' . PHP_EOL .
								'</li>' . PHP_EOL .
								'</ul>' . PHP_EOL .
								'</form>' . PHP_EOL .
								$fileManager;

		$this->adminContent .=	'</div>' . PHP_EOL;


		// Theme-Farben
		$totColors	= count($colorsArr);
		
		$this->adminContent .=	'<h3 class="cc-h3 switchToggle hideNext">{s_header:themecolors}</h3>' . PHP_EOL . 
								'<div class="adminBox">' . PHP_EOL . 
								'<form name="themecolors" id="themecolors" action="' . ADMIN_HTTP_ROOT . '?task=tpl" method="post">' . PHP_EOL . 
								'<ul class="framedItems">' . PHP_EOL . 
								'<li id="totColors" class="totColors-' . $totColors . '">' . PHP_EOL;

		$this->adminContent .=	'<div class="halfBox">' . PHP_EOL;
		
		$c = 1;
		
$cols="";
$colsArrHex=array();

		foreach($colorsArr as $color) {
			
			if(strlen($color) >= 6) {
				
				$color			= str_replace(array(PHP_EOL, "\n", "<br />", "<br>"), "", $color);
				$hexVal			= str_replace("#", "", $color);
				
				$description	= substr($color, strpos($color, "(")+1, ($c == $totColors ? -1 : -1));
				$description	= explode(",", $description);
				$description	= implode("<br />", $description);
				
				$description	= "";
			
#$cols.="col$c\t\t= &quot;" . $color . "&quot;\n";
#$colsArrHex[] = $hexVal;
			
				// Häufigkeit
				$occurance	=	$this->themeColors["cnt"][$c-1];
				
				$this->adminContent .=	'<div id="col-' . $c . '" class="colorTab tableRow">' . PHP_EOL .
										'<label class="color tableCell">{s_label:color} ' . $c . ' <span> (<i>' . $occurance . ' x</i>)</span></label>' . PHP_EOL .
										'<div class="tableCell colorSample-box" title="{s_title:rescolor}">' . PHP_EOL .
										'<span style="background-color:#' . $hexVal . ';" class="colorSample">&nbsp;</span>' . PHP_EOL .
										'</div>' . PHP_EOL .
										'<div class="color tableCell">' . PHP_EOL .
										'#<input type="text" name="colors[]" class="color" value="' . $hexVal . '" />' . PHP_EOL .
										'</div>' . PHP_EOL .
										'<div class="colorAffect tableCell">' . $description . '</div>' . PHP_EOL .
										'<br class="clearfloat" />' . PHP_EOL .
										'</div>' . PHP_EOL .
										($c == ceil(count($colorsArr)/2) ? '</div>' . PHP_EOL . '<div class="halfBox">' . PHP_EOL : '');
										
				$c++;
			}
		}
	
#self::sortColorsByColor($colsArrHex);
#echo("<p><br><br></p>");				
#echo("<pre>$cols</pre>");				
	
		$this->adminContent .=	'</div>' . PHP_EOL;
		
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL .
								'<li class="submit change">' . PHP_EOL;
		
		// Button submit (edit colors)
		$btnDefs	= array(	"type"		=> "submit",
								"id"		=> "submitColors",
								"name"		=> "submitColors",
								"class"		=> "change",
								"value"		=> "{s_button:takechange}",
								"icon"		=> "ok"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
								
		$this->adminContent .=	'<input name="submitColors" type="hidden" value="1" />' . PHP_EOL;
		
		// Button submit (reset colors)
		$btnDefs	= array(	"id"		=> "resetColors",
								"name"		=> "resetColors",
								"class"		=> "reset right",
								"value"		=> "{s_button:resetcolors}",
								"icon"		=> "reset"
							);
		
		$this->adminContent .=	parent::getButton($btnDefs);
								
		$this->adminContent .=	'<input name="edit_tpl" type="hidden" value="' . $currTpl . '" />' . PHP_EOL . 
								parent::getTokenInput() . 
								'</li></ul>' . PHP_EOL . 
								'</form>' . PHP_EOL . 
								'</div>' . PHP_EOL . 
								'</div>' . PHP_EOL;

		$this->adminContent .=	'<div class="adminArea">' . PHP_EOL . 
								'<ul>' . PHP_EOL .
								'<li class="submit back buttonpanel-nofix">' . PHP_EOL;
		
		// Button back
		$this->adminContent .=	$this->getButtonLinkBacktomain();
				
		$this->adminContent .=	'<br class="clearfloat" />' . PHP_EOL .
								'</li>' . PHP_EOL . 
								'</ul>' . PHP_EOL . 
								'</div>' . PHP_EOL;
							
		
		// #adminContent close
		$this->adminContent	.=	$this->closeAdminContent();
	
	
		// Admin Tour Script
		$this->adminContent .=	$this->getTplAdminTourScript();
		
		
		return $this->adminContent;

	}
	

	// isProtectedTpl
	public function isProtectedTpl($tpl)
	{
	
		// Falls geschützes Template
		if($tpl == "index.tpl"
		|| $tpl == "admin.tpl"
		|| $tpl == "contents.tpl"
		|| $tpl == "contents_edit.tpl"
		|| in_array($tpl, $this->defaultTemplates)
		)
			return true;
		
		return false;
	
	}
	

	// getThemeColors
	public function getThemeColors($cssContent)
	{
	
		#$colorsStr	= preg_replace("/([[:alnum:]\@\/\r\n\s\"'*().,:; _-]*colors:)(([[:alnum:]\r\n\s#,:.\/(){}> _\-][^*]*)(?<!colors))(.*)/iums", '$2', $cssContent); // Farbinformationen aus layout.css-Kopf auslesen
		#$colorsArr	= array_unique(array_filter(explode("#", str_replace(array("\r","\n"), "", $colorsStr))));

		if(empty($cssContent))
			return array();
		
		
		$token		= strtok($cssContent, "{}");
		$css_parts	= array();
		
		while ($token !== false) {
			$css_parts[]	= trim($token);
			$token			= strtok("{}");
		}

		$flag		= false;
		$properties = "";
		
		foreach($css_parts as $part) {
			if($flag) {
				$properties .= " ".trim($part);
			}
			$flag = !$flag;
		}
		$propertiesHex	= strtoupper(str_replace(array(":",",",";","(",")"), " ", $properties));
		$propertiesRgb	= strtolower(str_replace(array(":",";"), " ", $properties));

		$colorsArr	= array();
		$colorsHex	= array();
		$colFullHex	= array();
		$colorsRgb	= array();
		
		preg_match_all('/(?!\b)(#[abcdef0-9]+\b)/i', $propertiesHex, $colorsHex);
		preg_match_all('/rgb[a]?\(([0-9]{1,3},[0-9]{1,3},[0-9]{1,3}),/i', $propertiesRgb, $colorsRgb);
		
		$colorsArr	= $colorsHex[0];
		$colorsRgb	= $colorsRgb[1];
		
		foreach($colorsRgb as $col) {
			$decArr			= explode(",", $col);
			$colHex			= "#" . str_pad(dechex($decArr[0]),2,"0",STR_PAD_LEFT) . str_pad(dechex($decArr[1]),2,"0",STR_PAD_LEFT) . str_pad(dechex($decArr[2]),2,"0",STR_PAD_LEFT);
			$colorsArr[]	= $colHex;
		}
		
		foreach($colorsArr as $col) {
			$colFullHex[]	= "#" . strtoupper($this->getColorHexVal(str_replace("#", "", $col)));
		}
		
		$countHex	= array_count_values ($colFullHex);
		arsort($countHex);
		$colorsArr	= array_keys($countHex);
		
		$this->themeColors	=	array(	"col" => array_keys($countHex),
										"cnt" => array_values($countHex)
								);
		return $colorsArr;
	
	}
	

	// getColorHexVal
	public function getColorHexVal($col)
	{
	
		if(strlen($col) == 3)
			return $col[0].$col[0].$col[1].$col[1].$col[2].$col[2];
	
		return $col;
	
	}
	

	// rgbToHsl
	public function rgbToHsl($r, $g, $b)
	{
	
    $r /= 255; 
    $g /= 255; 
    $b /= 255;
    $max = max($r, $g, $b);
        $min = min($r, $g, $b);
    $h = 0;
    $s = 0;
    $l = ($max + $min) / 2;
 
    if($max == $min){
        $h = $s = 0; // achromatic
    }else{
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        switch($max){
            case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
            case $g: $h = ($b - $r) / $d + 2; break;
            case $b: $h = ($r - $g) / $d + 4; break;
        }
        $h /= 6;
    }
 
    return array('h'=>$h, 's'=>$s, 'l'=>$l);
	}
	

	// sortColorsByColor
	public function sortColorsByColor($rgblist)
	{
	
		$sort = array();
		
		foreach($rgblist as $rgb) {
			$hsl = self::rgbToHsl(hexdec(substr($rgb, 0, 2)), hexdec(substr($rgb, 2, 2)), hexdec(substr($rgb, 4, 2)));
			$sort[] = $hsl['h'];
		}
		
		array_multisort($sort, SORT_ASC, $rgblist);
		
		return $rgblist;
	
	}
	

	// getScriptTag
	public function getScriptTag()
	{

		return	'<script>' . PHP_EOL .
				'head.ready("jquery", function(){' . PHP_EOL .
				'head.load({imagepickercss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.css"});' . PHP_EOL .
				'head.load({imagepicker: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/image-picker/image-picker.min.js"});' . PHP_EOL .
				'head.ready("imagepicker", function(){' . PHP_EOL .
					'$(document).ready(function(){' . PHP_EOL .
						'$("#currTheme").imagepicker({
							target_box: $("#themeSelectionBox"),
							hide_select: true,
							show_label: true,
							show_title: true,
							limit: undefined
						});' . PHP_EOL .
						'$("select.tplSelect").imagepicker({
							target_box: $("#tplSelectionBox"),
							hide_select: false,
							show_label: false,
							limit: undefined,
							initialized: function(){
								$("#tplSelectionBox, #tplSelectionBox ul").show();
								$("#tplSelectionBox ul li").each(function(i,e){
									var title	= $("select.tplSelect").children(":nth-child(" + (i+1) + ")").attr("data-title");
									$(this).attr("title", title);
									$(this).append(\'<span class="label">\' + title + \'</span>\');
								});
							}
						});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}
	
	
	// getTplAdminTourScript
	protected function getTplAdminTourScript()
	{
	
		return	'<script>
				head.ready(function(){
					head.load({hopscotch: "extLibs/jquery/hopscotch/js/hopscotch.min.js"}, function(){
						head.load("extLibs/jquery/hopscotch/css/hopscotch.min.css");
						head.load({admintourtpl: "system/inc/admintasks/tpl/js/adminTour.tpl.min.js"}, function(){
							$("document").ready(function(){
								// Start tour on desktop devices
								if(!cc.isPhone()){
									$.tpl_AdminTour();
								}
							});
						});
					});
				});
				</script>' . PHP_EOL;
	
	}

}
