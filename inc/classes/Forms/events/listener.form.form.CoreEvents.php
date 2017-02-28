<?php
namespace Concise\Events\Form;

use Symfony\Component\EventDispatcher\Event;
use Concise\Security;



##############################
###  EventListener-Klasse  ###
##############################

// FormCoreEventsListener

class FormCoreEventsListener
{
	
	// onCreateFormfield
	public function onCreateFormfield(Event $event)
	{
	
		$fieldOutput		= "";
		$dataRequired		= $event->required ? ' data-validation="required"' : '';
		
		//Felder je nach Typ generieren
		switch($event->fieldType) {
		
			//Hidden
			case "hidden":
			
				$event->fieldClass 	.=	" hidden";
				$fieldOutput 		.=	'<li class="automaticForm formField_' . $event->field['Field'] . '" style="display:none;">' . PHP_EOL .
										'<input type="hidden" class="standardField'.$event->fieldClass.'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" value="'.htmlentities($event->fieldVal,ENT_QUOTES,"UTF-8").'"' .
										$dataRequired .
										' />' . PHP_EOL;
				break;
			
			//Textarea
			case "textarea":
			
				$event->fieldClass 	.=	" textarea {t_class:field}";
				$fieldOutput 		.=	'<textarea class="standardField'.$event->fieldClass.'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" rows="" cols=""' .
				$dataRequired .
				'>'.htmlentities($event->fieldVal,ENT_QUOTES,"UTF-8").'</textarea>' . PHP_EOL;
				break;
			
			//Auswahlliste
			case "select":
			
				$event->fieldClass 	.=	" select {t_class:field}";
				$blankField = ($event->field['Null'] != "NO" ? '<option value="">{s_option:choose}</option>' . PHP_EOL : "");
				$fieldOutput 		.=	'<select class="standardField'.$event->fieldClass.'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'"' .
				$dataRequired .
				'>'. PHP_EOL;
				$fieldOutput		.=	$blankField;
				
				$optCount = 0;
				
				foreach($event->configArray[$event->field['Field']]["options"] as $option) {
					
					if($option == "[]") // Falls Optgroup-Schließen-Tag
						$fieldOutput	.=	'</optgroup>' . PHP_EOL;
					elseif(strpos($option, "[") === 0) // Falls Optgroup-Beginn
						$fieldOutput 	.=	'<optgroup label="' .  $option . '">' . PHP_EOL;
					else { // Andernfalls Option
						$fieldOutput 	.=	'<option'.($event->fieldVal == $option ? ' selected="selected"' : "").'>'.  $option . '</option>' . PHP_EOL;
						$optCount++;
					}
				}
				$fieldOutput	.=	'</select>' . PHP_EOL;
				break;
			
			//Mehrfachauswahl
			case "multiple":
			
				if(!is_array($event->fieldVal))
					$event->fieldVal		 = (array)json_decode($event->fieldVal);
				
				$event->fieldClass 	.=	" multiple {t_class:field}";
				$blankField = ($event->field['Null'] != "NO" ? '<option value="">{s_option:choose}</option>' . PHP_EOL : "");
				$fieldOutput 		.=	'<select multiple="multiple" class="standardField'.$event->fieldClass.'" name="'.$event->tablename.'_'.$event->field['Field'].'[]" id="'.$event->tablename.'_'.$event->field['Field'].'" size="'.(count($event->configArray[$event->field['Field']]["options"]) + min(strlen($blankField), 1)).'"' .
				$dataRequired .
				'>'. PHP_EOL;
				
				// Optionen
				$fieldOutput		.=	$blankField;

				foreach($event->configArray[$event->field['Field']]["options"] as $option) {
					$fieldOutput 	.=	'<option'.(!empty($event->fieldVal) && (is_array($event->fieldVal) && (in_array($option, $event->fieldVal) || array_key_exists($option, $event->fieldVal)) || (is_string($event->fieldVal) && $event->fieldVal == $option)) ? ' selected="selected"' : "").'>'.  $option . '</option>' . PHP_EOL;
				}
				$fieldOutput 		.=	'</select>' . PHP_EOL;
				break;
			
			//Checkbox
			case "checkbox":
				
				if(!is_array($event->fieldVal))
					$event->fieldVal		 = (array)json_decode($event->fieldVal);
				
				$event->fieldClass 	.=	" checkbox";
				$fieldOutput		.=	'<ul class="inputFrame'.$event->classFill.' subList">' . PHP_EOL;
				
				//Falls keine Optionen angegeben, kann es sich nur um eine Checkbox handeln mit Wert "yes", falls gesetzt
				if(!isset($event->configArray[$event->field['Field']]["options"]))
					$fieldOutput 	.=	'<li>' .
										'<input type="checkbox" class="standardField'.$event->fieldClass.'"'.(is_array($event->fieldVal) && (in_array($option, $event->fieldVal) || array_key_exists($option, $event->fieldVal)) ? ' checked="checked"' : "").' name="'.$event->tablename.'_'.$event->field['Field'].'['.$event->fieldVal[0].']" id="'.$event->tablename.'_'.$event->field['Field'].'_'.$event->tablename.'_'.$event->field['Field'].'"' .
										$dataRequired . 
										' />' . PHP_EOL .
										'</li>';
				
				//Andernfalls Optionen auslesen
				else {
					
					foreach($event->configArray[$event->field['Field']]["options"] as $option) {
						$fieldOutput .=	'<li>' .
										'<label for="' . $event->tablename."_".$event->field['Field'] . "_" . $option . '" class="subLabel {t_class:checkboxinl}">' .
										'<input type="checkbox" class="standardField'.$event->fieldClass.'"'.((is_array($event->fieldVal) && (in_array($option, $event->fieldVal) || array_key_exists($option, $event->fieldVal)) || (is_string($event->fieldVal) && $event->fieldVal == $option)) ? ' checked="checked"' : "").' name="'.$event->tablename.'_'.$event->field['Field'].'['.$option.']" id="'.$event->tablename.'_'.$event->field['Field'].'_'.$option.'"' .
										$dataRequired .
										#($event->required ? ' data-validation="checkbox_group" data-validation-qty="min1"' : '') .
										' />' .
										$option .
										'</label>' . PHP_EOL .
										'</li>' . PHP_EOL;
					}
					$fieldOutput 	.= '<br class="clearfloat"></ul>' . PHP_EOL;
				}
				break;
			
			//Radiobutton
			case "radio":
			
				$event->fieldClass 	.=	" radio";
				$fieldOutput		.=	'<ul class="inputFrame'.$event->classFill.' subList">' . PHP_EOL;
				$optNr = 1; // Falls Poll, Option-Nr. anstelle von Wert in DB eintragen

				foreach($event->configArray[$event->field['Field']]["options"] as $option) {
					$fieldOutput	.=	'<li>' .
										'<label for="' . $event->tablename."_".$event->field['Field'] . "_" . $option . '" class="subLabel {t_class:radioinl}">' .
										'<input type="radio" class="standardField'.$event->fieldClass.'"'.($event->fieldVal == $option ? ' checked="checked"' : "").' name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'_'.$option.'" value="'.(isset($event->configArray["cf_poll"]) && $event->configArray["cf_poll"] ? $optNr : $option).'"' .
										$dataRequired .
										' />' . 
										$option .
										'</label>' . PHP_EOL .
										'</li>' . PHP_EOL;
					
					$optNr++;
				}
				$fieldOutput		.=	'<br class="clearfloat"></ul>' . PHP_EOL;
				break;
			
			//Password
			case "password":
			
				$event->fieldClass 	.=	" password {t_class:field}";
				$event->fieldMaxLen > PASSWORD_MAX_LENGTH ? $event->fieldMaxLen = PASSWORD_MAX_LENGTH : '';
				$fieldOutput 	.=	'<input type="password" class="standardField'.$event->fieldClass.'" value="'.htmlentities($event->fieldVal,ENT_QUOTES,"UTF-8").'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" maxlength="' . $event->fieldMaxLen . '"' .
				$dataRequired .
				' />' . PHP_EOL;
				break;
			
			//Email
			case "email":
			
				$event->fieldClass 	.=	" default {t_class:field}";
				$fieldOutput 		.=	'<input type="email" class="standardField'.$event->fieldClass.'" value="'.htmlentities($event->fieldVal,ENT_QUOTES,"UTF-8").'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" maxlength="' . $event->fieldMaxLen . '"' .
				$dataRequired . ' data-validation="email"' . ($event->required ? '' : ' data-validation-optional="true"') .
				' />' . PHP_EOL;
				break;
			
			//Url
			case "url":
			
				$event->fieldClass 	.=	" default {t_class:field}";
				$fieldOutput 		.=	'<input type="url" class="standardField'.$event->fieldClass.'" value="'.htmlentities($event->fieldVal,ENT_QUOTES,"UTF-8").'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" maxlength="' . $event->fieldMaxLen . '"' .
				$dataRequired . ' data-validation="url"' . ($event->required ? '' : ' data-validation-optional="true"') .
				' />' . PHP_EOL;
				break;
			
			//Date
			case "date":
				
				//Falls value ein Datum ist, Datum im Format yyyy-mm-dd generieren
				$inputValue		 	 =	$event->fieldVal;
				$event->fieldClass 	.=	" date {t_class:field}";
				$fieldOutput 		.=	'<input type="text" class="standardField'.$event->fieldClass.'" value="'.htmlentities($inputValue,ENT_QUOTES,"UTF-8").'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" maxlength="' . $event->fieldMaxLen . '"' .
				$dataRequired .
				' />' . PHP_EOL .
				$event->dateExt;
				break;
			
			//File
			case "file":
			
				$outputExt = "";
				
				// Falls ein Datensatz bearbeitet wird, bestehende Datei vor File-Feld anzeigen
				if(array_key_exists("cf_editformdata", $event->configArray)) {
					
					if($event->configArray[$event->field['Field']]["value"] != "" || (isset($GLOBALS['_POST'][$event->field['Field'] . "_oldFile"]) && $GLOBALS['_POST'][$event->field['Field'] . "_oldFile"] != "")) {
					
						if(!isset($GLOBALS['_POST'][$event->field['Field'] . "_oldFile"]))
							$oldFile = $event->configArray[$event->field['Field']]["value"];
						else
							$oldFile = $GLOBALS['_POST'][$event->field['Field'] . "_oldFile"];
						
						$fieldOutput 	.=	'<div class="presentFileBox">';
						
						$fieldOutput 	.=	FormEvaluation::getPresentFile($oldFile, $event->configArray[$event->field['Field']]["filefolder"], $event->configArray[$event->field['Field']]["filerename"]);
						$outputExt = '</div><br />';
					}
					
					$fieldOutput 		.=	'<input type="hidden" name="'.$event->field['Field'].'_oldFile" value="' . (!isset($GLOBALS['_POST'][$event->field['Field'] . '_oldFile']) ? $event->configArray[$event->field['Field']]['value'] : $GLOBALS['_POST'][$event->field['Field'] . '_oldFile']) . '" />' . PHP_EOL .
					$outputExt;
				}
				
				// Standard-Dateifeld
				$fieldOutput 	.=	'<input type="file" class="standardField'.$event->fieldClass.'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" />' . PHP_EOL .
				(array_key_exists("filetypes", $event->configArray[$event->field['Field']]) && count($event->configArray[$event->field['Field']]["filetypes"]) > 0 ? '<span class="allowedFileTypes">' . implode(", ", $event->configArray[$event->field['Field']]["filetypes"]) . '</span>' . PHP_EOL : '') .
				(array_key_exists("filesize", $event->configArray[$event->field['Field']]) && is_numeric($event->configArray[$event->field['Field']]["filesize"]) ? '<span class="maxFileSize"> (max. ' . floor($event->configArray[$event->field['Field']]["filesize"] / 1000000) . 'MB)</span>' . PHP_EOL : '');
				$event->hasFiles = true; //Erlaubt das Einbinden des enctype-Attributs beim Form-Tag
				break;
			
			//Default
			default:
			
				$event->fieldClass 	.=	" default {t_class:field}";
				$fieldOutput 		.=	'<input type="text" class="standardField'.$event->fieldClass.'" value="'.htmlentities($event->fieldVal,ENT_QUOTES,"UTF-8").'" name="'.$event->tablename.'_'.$event->field['Field'].'" id="'.$event->tablename.'_'.$event->field['Field'].'" maxlength="' . $event->fieldMaxLen . '"' .
				$dataRequired .
				' />' . PHP_EOL;
				break;
		}
		
		// Prevent execution of subsequent event listeners functions
		#$event->stopPropagation();
		
		$event->fieldOutput	= $fieldOutput;
		
		return $fieldOutput;

	}
	
	
	// onCheckFormfield
	public function onCheckFormfield(Event $event)
	{
		
		// Falls kein Array, Leerzeichen entfernen
		if(!is_array($event->fieldVal))
			$event->fieldVal	= trim($event->fieldVal);
		
		//Ist null verboten und trotzdem null?
		if (($event->nullAllowed == "NO") && ($event->fieldVal == ""))
		{
			$event->prevField	= $event->attributename; //Feldname speichern
			$event->prevValue	= $event->fieldVal; //Wert speichern
			$event->result		= false;
			return false;
		}
		//Andernfalls, falls null erlaubt ist, das Feld leer ist, aber in Abhängigkeit vom einem nicht leeren vorherigen Wert zwingend wird, hier false zurückgeben
		if ($event->nullAllowed == "YES" && ($event->fieldVal == "" || $event->fieldVal == array())
		&& isset($event->configArray[$event->attributename]["link"][0])
		&& $event->configArray[$event->attributename]["link"][0] == $event->prevField
		&& $event->configArray[$event->attributename]["link"][1] == "*"
		&& $event->prevValue != "" && $event->prevValue != array()
		&& ($event->fieldVal == "" || $event->fieldVal == array())
		) {
			$event->prevField	= $event->attributename; //Feldname speichern
			$event->prevValue	= $event->fieldVal; //Wert speichern
			$event->result		= false;
			return false;
		}
		//Andernfalls, falls der Wert mit dem vorherigen Wert übereinstimmen muss, dies aber nicht tut, hier false zurückgeben
		if (isset($event->configArray[$event->attributename]["link"][0])
		&& $event->configArray[$event->attributename]["link"][0] == $event->prevField
		&& $event->configArray[$event->attributename]["link"][1] == "="
		&& $event->fieldVal != $event->prevValue
		) {
			$event->prevField	= $event->attributename; //Feldname speichern
			$event->prevValue	= $event->fieldVal; //Wert speichern
			$event->result		= false;
			return false;
		}
		//Andernfalls, falls der Wert mit dem vorherigen Wert NICHT übereinstimmen darf, dies aber tut, hier false zurückgeben
		if (isset($event->configArray[$event->attributename]["link"][0])
		&& $event->configArray[$event->attributename]["link"][0] == $event->prevField
		&& $event->configArray[$event->attributename]["link"][1] == "<>"
		&& $event->fieldVal == $event->prevValue
		) {
			$event->prevField	= $event->attributename; //Feldname speichern
			$event->prevValue	= $event->fieldVal; //Wert speichern
			$event->result		= false;
			return false;
		}
		//Andernfalls, falls der Wert abhängig von einem bestimmten vorherigen Wert ist, und nicht gesetzt ist, hier false zurückgeben
		if (isset($event->configArray[$event->attributename]["link"][0])
		&& $event->configArray[$event->attributename]["link"][0] == $event->prevField
		&& $event->configArray[$event->attributename]["link"][1] != ""
		&& $event->prevValue == $event->configArray[$event->attributename]["link"][1]
		&& $event->fieldVal == "" || $event->fieldVal == array()
		) {
			$event->prevField	= $event->attributename; //Feldname speichern
			$event->prevValue	= $event->fieldVal; //Wert speichern
			$event->result		= false;
			return false;
		}
		//Ist null erlaubt und null?
		if (($event->nullAllowed == "YES") && ($event->fieldVal == ""))
		{
			$event->prevField	= $event->attributename; //Feldname speichern
			$event->prevValue	= $event->fieldVal; //Wert speichern
			$event->result		= true;
			return true;
		}
		$event->prevField	= $event->attributename; //Feldname speichern
		$event->prevValue	= $event->fieldVal; //Wert speichern
	
		return "";
	
	}
	
	
	// onCheckFieldTypes
	public function onCheckFieldTypes(Event $event)
	{

		switch($event->fieldType) {
			
			// Falls Eingabefelder, Längenvorgaben einbinden
			case "default";
			case "text";
			case "password";
			case "int";
				
				// Falls ein Eintrag bearbeitet werden soll und das Feld vom Typ Password ist
				// und das Passwort nicht geändert wurde (sprich die Länge von 64 (Sha256) hat), wahr zurückgeben
				if(array_key_exists("cf_editformdata", $event->configArray) && $event->fieldType == "password" && strlen($event->fieldVal) == 64)
					return $event->setResult(true);

				if(($event->nullAllowed == "NO") && $event->fieldVal == "")
					return $event->setResult(false);
				
				$minLength	= $event->configArray[$event->attributename]["minlen"];
				$maxLength	= $event->configArray[$event->attributename]["maxlen"];
				
				if($minLength != "" && $event->fieldVal != "" && mb_strlen($event->fieldVal, "UTF-8") < $minLength) {
					$event->configArray[$event->attributename]["notice"] = "{s_error:inputlen2a} " . (int)$minLength . " {s_error:inputlen2b}";
					return $event->setResult(false);
				}
				elseif($maxLength != "" && mb_strlen($event->fieldVal, "UTF-8") > $maxLength) {
					$event->configArray[$event->attributename]["notice"] = "{s_error:inputlen1a} " . (int)$maxLength . " {s_error:inputlen1b}";
					return $event->setResult(false);
				}
				else {
					
					//Falls Feld vom Typ Password ist
					if($event->fieldType == "password") {
						
						$checkPass = Security::verifyPassword($event->fieldVal);
						
						if($checkPass !== true) {
							$event->configArray[$event->attributename]["notice"] = $checkPass;
							return $event->setResult(false);
						}
						else
							return $event->setResult(true);
						break;
					}
					else
						return $event->setResult(true);
				}
				break;
				
			
			//Falls Feld vom Typ checkbox ist
			case "checkbox";
				if(!empty($event->fieldVal) && count(array_intersect($event->configArray[$event->attributename]["options"], array_keys($event->fieldVal))) == 0) {
					$event->result	= false;
				}
				else {
					$event->result	= true;
				}
				return $event->result;
				break;
				
			
			//Falls Feld vom Typ multiple ist
			case "multiple";
				
				if(!empty($event->fieldVal) && count(array_intersect($event->configArray[$event->attributename]["options"], $event->fieldVal)) == 0) {
					$event->result	= false;
				}
				else {
					$event->result	= true;
				}
				return $event->result;
				break;
				
			
			//Falls Feld vom Typ E-Mail ist
			case "email":
				
				if($event->fieldVal != "" && (!filter_var($event->fieldVal, FILTER_VALIDATE_EMAIL) || strlen($event->fieldVal) > 254))
					return $event->setResult(false);
				else {
					
					//Falls diese Formular-E-mail als Empfängeradresse verwendet werden soll, die Adresse aus dem Feld hinzufügen
					if(isset($event->configArray[$event->attributename]["usemail"]) && isset($event->recipients[0]) && $event->recipients[0] == "form")
						$event->recipients[] = $event->fieldVal;
					
					return $event->setResult(true);
				}
				break;
			
			//Falls Feld vom Typ Url ist
			case "url":
				
				if($event->fieldVal != "" && (!filter_var($event->fieldVal, FILTER_VALIDATE_URL) || strlen($event->fieldVal) > 254))
					return $event->setResult(false);
				else
					return $event->setResult(true);
				break;
			
			//Falls Feld vom Typ File ist
			case "file":
				
				// Falls ein Eintrag bearbeitet werden soll und das Feld vom Typ File ist
				// und keine neue Datei hochgeladen werden soll
				if(array_key_exists("cf_editformdata", $event->configArray) && $event->fieldVal['name'] == "" && $event->nullAllowed != "NO")
					return $event->setResult(true);

				if(($event->nullAllowed == "NO") && ($event->fieldVal['name'] == "") && !array_key_exists("cf_editformdata", $event->configArray)) {
					$event->configArray[$event->attributename]["notice"] = "{s_error:choosefile}";
					return $event->setResult(false);
				}
				
				//Files-Klasse einbinden
				require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";
				
				$fileExt	= Files::getFileExt($event->fieldVal['name']);
				$prefix		= isset($event->configArray[$event->attributename]["fileprefix"]) ? $event->configArray[$event->attributename]["fileprefix"] : '';
				$folder		= PROJECT_DOC_ROOT . '/';
				
				// Falls die Datei umbenannt werden soll (Präfix + Username) bei benutzerspezifischen Dateien
				if(isset($event->configArray[$event->attributename]["filerename"]) && $event->configArray[$event->attributename]["filerename"] == 1) {
					if(isset($event->g_Session['username']) && isset($event->g_Session['userid']))
						$prefix		.= $event->g_Session['userid'];
					else
						$prefix		.= "NOUSERID";
						
					$fileName	= $prefix . "." . $fileExt;
					$folder	   .= '_user';
				}
				else {
					$fileName	= $prefix . $event->fieldVal;
					$folder	   .= CC_FILES_FOLDER;
				}
					
				$folder		   .= isset($event->configArray[$event->attributename]["filefolder"]) && $event->configArray[$event->attributename]["filefolder"] != "" ? '/'.$event->configArray[$event->attributename]["filefolder"] : '';
				$fileTypes		= array_key_exists("filetypes", $event->configArray[$event->attributename]) ? $event->configArray[$event->attributename]["filetypes"] : array();
				$maxFileSize	= isset($event->configArray[$event->attributename]["filesize"]) ? $event->configArray[$event->attributename]["filesize"] : 5242880;
				$overwrite		= isset($event->configArray[$event->attributename]["filereplace"]) ? $event->configArray[$event->attributename]["filereplace"] : false;
				
				if(!Files::getValidFileName($fileName, true)) {
					$event->configArray[$event->attributename]["notice"] = "{s_error:uploadfail}";
					return $event->setResult(false);
				}
				elseif(count($fileTypes) > 0 && $fileTypes[0] != "" && !in_array($fileExt, $fileTypes)) {
					$event->configArray[$event->attributename]["notice"] = "{s_error:filetype}";
					return $event->setResult(false);
				}
				elseif(filesize($event->fieldVal['tmp_name']) > $maxFileSize) {
					$event->configArray[$event->attributename]["notice"] = "{s_error:filesize} " . (floor((float)$maxFileSize / 1000000)) . "MB";
					return $event->setResult(false);
				}
				elseif(!is_dir($folder)) {
					return $event->setResult(true);
				}
				elseif($overwrite == false && file_exists($folder . '/' . $fileName)) {
					$event->configArray[$event->attributename]["notice"] = sprintf(ContentsEngine::replaceStaText("{s_error:fileexists}"), $fileName);
					return $event->setResult(false);
				}
				else
					return $event->setResult(true);
				break;
		}
		
		return "";

	}
	
	
	// onBuildDataArrayPre
	public function onBuildDataArrayPre(Event $event)
	{
		
		$preRemark	= "";
		
		#$event->result	= $preRemark;
		
		// ArrayElement überspringen
		#$event->stopPropagation();
		
		return $preRemark;
	
	}
	
	
	// onBuildDataArrayMid
	public function onBuildDataArrayMid(Event $event)
	{
	
		//Eingaben zusätzlich in Array speichern (für spätere Verwendung z.B. in E-Mail)
		$event->formInputArrayDB[]	= array($event->attribute['Field'], $event->fieldVal);
		
		
		// Falls eine Überschrift eingefügt werden soll
		if(isset($event->configArray[$event->attribute['Field']]["header"]) && $event->configArray[$event->attribute['Field']]["header"] != "")
			$event->formInputArray[]	= array("_formheader", $event->configArray[$event->attribute['Field']]["header"]); //Array mit ersetzten Labels
			
		$event->formInputArray[]	= array(isset($event->configArray[$event->attribute['Field']]["label"]) ? $event->configArray[$event->attribute['Field']]["label"] : $event->attribute['Field'], $event->fieldVal); // Array mit ersetzten Labels
			
		// Falls eine Bemerkung eingefügt werden soll
		if(isset($event->configArray[$event->attribute['Field']]["remark"]) && $event->configArray[$event->attribute['Field']]["remark"] != "") {
		
			$result	= $event->getResult();
			
			// Falls remark event
			if(!empty($result))
				$event->formInputArray[]	= $result;
			else
				$event->formInputArray[]	= array("_formremark", $event->configArray[$event->attribute['Field']]["remark"]); //Array mit ersetzten Labels
		}

		// onBuildDataArrayPost-Action überspringen
		#$event->stopPropagation();
		
		return true;
	
	}
	
	
	// onBuildDataArrayPost
	public function onBuildDataArrayPost(Event $event)
	{
	
		$finalRemark	= "";
		
		#$event->result	= $finalRemark;
		
		return $finalRemark;
	
	}
	
	
	// onAddExtraData
	public function onAddExtraData(Event $event)
	{
	
		$finalRemark	= "";
		
		#$event->result	= $finalRemark;
		
		return $finalRemark;
	
	}
	
} // Ende class
