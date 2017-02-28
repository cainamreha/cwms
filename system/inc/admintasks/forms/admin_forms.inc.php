<?php
namespace Concise;



#################################################
###############  Forms-Bereich  #################
#################################################

// Formulare verwalten 

class Admin_Forms extends Admin implements AdminTask
{

	public $formID				= "";
	public $formIDdb			= "";
	private $tableForms			= "forms";
	private $tableFormsDefs		= "forms_definitions";
	private $existForms			= array(); 
	public $queryFormDetails	= array();
	public $queryFormFields		= array();
	public $queryFormData		= array();
	public $newForm				= false;
	public $editForm			= false;
	public $listFields			= false;
	public $editFields			= false;
	public $listData			= false;
	public $editData			= false;
	public $noChange			= false;
	public $dataLock			= false;
	private $form				= "";
	private $formTitle			= "";
	private $formForeignKey		= "";
	private $formPoll			= 0;
	private $formActive			= 1;
	private $formEndDate		= "";
	private $formSuccess		= "";
	private $formError			= "";
	private $formFieldNotice	= "";
	private $formCaptcha		= 1;
	private $formHttps			= 0;
	private $formAddTable		= "";
	private $formAddFields		= "";
	private $formAddLabels		= "";
	private $formAddPosition	= "";
	private $fieldTypes			= array("default", "textarea", "select", "multiple", "checkbox", "radio", "email", "url", "date", "password", "file", "int", "float", "hidden");
	private $existFormsArray	= false;
	private $existFieldNames	= array();
	private $fieldType		= array();
	private $fieldName		= array();
	private $fieldNameOld	= array();
	private $fieldNamePrev	= "";
	private $fieldRequired	= array();
	private $fieldRequiredOld	= array();
	private $fieldLabel		= array();
	private $fieldValue		= array();
	private $fieldOptions	= array();
	private $fieldNotice	= array();
	private $dbFieldLen		= array();
	private $fileTypes		= array();
	private $fileSize		= array();
	private $fileFolder		= array();
	private $filePrefix		= array();
	private $fileRename		= array();
	private $fileReplace	= array();
	private $usemail		= array();
	private $showpass		= array();
	private $fieldLink		= array();
	private $fieldLinkField	= array();
	private $fieldLinkValue	= array();
	private $fieldHeader	= array();
	private $fieldRemark	= array();
	private $pagebreak		= array();
	private $wrongInput		= array();
	private $insertSQL		= false;
	private $updateSQL		= false;
	private $newFieldNr		= "";
	private $successChange	= false;
	public $evalFieldPost	= true;
	private $dbUpdateStr	= "";								
	public $limitOptions	= array(10, 25, 50, 100, 250, 500);
	
	public function __construct($DB, $o_lng, $task, $init = false)
	{

		// Admin-Elternklasse aufrufen
		parent::__construct($DB, $o_lng);
		
		parent::$task = $task;
		
		// Events listeners registrieren
		$this->addEventListeners("form");
		
		$this->headIncludeFiles['sortable']		= true;
		$this->headIncludeFiles['moduleeditor']	= true;
		
		$this->tableForms		= DB_TABLE_PREFIX . $this->tableForms;
		$this->tableFormsDefs	= DB_TABLE_PREFIX . $this->tableFormsDefs;

	}
	
	
	public function getTaskContents($ajax = false)
	{
	
		// Enthält Headerbox
		$this->adminHeader		=	'{s_text:adminforms}' . PHP_EOL . 
									'</div><!-- Ende headerBox -->' . PHP_EOL;
		
		// #adminContent
		$this->adminContent 	=	$this->openAdminContent();

		$this->adminContent 	.=	'<div class="adminArea">' . PHP_EOL;


		$this->formAction		= ADMIN_HTTP_ROOT . "?task=forms";		
		$this->ajax				= $ajax;
		
		
		// Globale Request vars auslesen
		$this->evalUserRequests();
		
		
		// Notifications
		$this->notice 	.= $this->getSessionNotifications("notice");
		$this->error	.= $this->getSessionNotifications("error");

		
		// db-Query nach Formularen
		$this->existForms = $this->getExistingForms();

		
		// db-Query nach Formulardetails
		if($this->formID != "") {
			$this->formIDdb		= (int)$this->formID;
			$this->initEditForm();
		}
		
		
		
		##########################################	
		// Formular anlegen/bearbeiten
		##########################################	
		
		// Falls ein Formular neu angelegt oder bearbeitet werden soll
		if($this->newForm
		|| $this->editForm
		) {
			
			// Falls Formular (edit)
			if($this->editForm) {			
				$this->success	= "{s_notice:takechange}";
			}			
			
			// Andernfalls Formular (neu)
			else {
				$this->success	= "{s_notice:newform}";
			}


			// Falls das Formular zum Anlegen oder Bearbeiten eines Formulars (mit Daten) abgeschickt wurde
			if(!empty($GLOBALS['_POST'])
			&&(!isset($GLOBALS['_POST']['new_form'])
			|| !empty($GLOBALS['_POST']['new_form']))
			) {
				
				$this->readFormPost();
			}
			
			
			// Falls das Formular mit Daten zum Ändern oder Anlegen abgeschickt wurde
			if($this->noChange == false && empty($GLOBALS['_POST']['go_edit_form'])) {
				
				$this->conductFormChanges();
			
			} // Ende nochange = false

			// Falls sich von vorher noch eine id zum Bearbeiten von Formularen in der Session befindet, diese löschen
			if(isset($this->g_Session['form_id']))
				$this->unsetSessionKey('form_id');
									   
		} // Ende if Form




		##########################################	
		//  Formularfelder bearbeiten
		##########################################	
		//
		// Falls ein einzelnes Formular zum Bearbeiten in der Session gespeichert ist
		// und falls Änderungen nicht blockiert sind
		if(!empty($this->formID)
		&& $this->noChange == false
		) {

			// db-Query nach Feldern des zu bearbeitenden Formulars
			$this->queryFormFields = $this->getFormFieldDetails($this->formIDdb);
		
			
			// Andernfalls Formularfelder anzeigen/auslesen
			$this->readFormFields($this->queryFormFields);


			// Seite nach erfolgreicher Feldbearbeitung neu laden und Meldung ausgeben
			if($this->editFields && count($this->wrongInput) == 0) {
				
				$this->setSessionVar('notice', "{s_notice:editform}");
				$this->setSessionVar('list_fields', $this->formID);
				
				header("Location: " . ADMIN_HTTP_ROOT . "?task=forms");
				exit;
			}
			
		}



		// Falls kein Lock gesetzt, Formulare anzeigen
		if($this->dataLock == false) {
			

		// Meldungen einbinden
		$this->adminContent .=	$this->getFormNotifications();
		
		
		// Bei mehreren Sprachen Sprachauswahl einbinden
		$this->getLangSelection();
		

		// Formulare
		$this->adminContent .=	'<h2 class="cc-section-heading cc-h2">{s_nav:adminforms}</h2>' . PHP_EOL;
		


		##########################################	
		// Formular anlegen bzw. bearbeiten (HTML)
		##########################################
		$this->adminContent .=	'<h3 class="cc-h3 switchToggle';

		if($this->listFields
		|| $this->listData
		|| $this->editData
		)
			$this->adminContent .=	' hideNext';

		$this->adminContent .=	'">{s_header:form}</h3>' . PHP_EOL . 
								'<div class="adminBox">' . PHP_EOL;


		// Bestehende Formulare
		$this->adminContent .=	$this->listExistingForms();
		
		
		
		##########################################	
		// Formular neu bzw. bearbeiten (HTML)
		##########################################	
		
		// Formular neu bzw. bearbeiten
		if(!$this->successChange &&
			($this->newForm || $this->editForm || isset($GLOBALS['_POST']['go_edit_form']))
		)
			$this->adminContent .=	$this->getFormDetailsForm();

			
		
		$this->adminContent .=		'</div>' . PHP_EOL;
		
		
		
		#################################################################	
		// Formularfelder anlegen/bearbeiten (aus Auswahlliste auswählen)
		#################################################################
		if(count($this->existForms) > 0) {
			
			$this->adminContent .= 	'<h3' . (!$this->successChange ? ' id="cfm"' : '') . ' class="cc-h3 switchToggle';
			
			if(!$this->listFields
			|| $this->editData
			)
			
				$this->adminContent .=' hideNext';
				
			$this->adminContent .=	'">{s_header:formfields} ' . (!empty($this->formID) ? '(#' . $this->formID . ')' : '') . '</h3>' . PHP_EOL;			
			
			$this->adminContent .=	'<div class="adminBox">' . PHP_EOL;

			
			// Falls ein einzelnes Formular zum Bearbeiten in der Session gespeichert ist
			if(!empty($this->formID)
			&& !empty($this->queryFormDetails)
			) {
			
				$this->adminContent .=	'<ul id="formFieldEntries" class="formFieldList">' . PHP_EOL;
				
				$this->adminContent .=	'<h4 class="cc-h4 formFields" title="{s_title:togglelist}">'.$this->queryFormDetails[0]['table'] . ' (' . $this->queryFormDetails[0]['title_' . $this->editLang].')<span class="right">{s_label:form} #' . $this->formID . '</span></h4>' . PHP_EOL;
				
				$this->adminContent .=	'<form action="' . $this->formAction . '&list_fields=' . $this->formID . '" method="post" accept-charset="UTF-8" data-getcontent="fullpage" data-history="false">' . PHP_EOL;

				
				$fieldsLoop	= "";
				$i = 1;
				
				// Ggf. Feldnummer aus url auslesen
				if(isset($GLOBALS['_GET']['field']))
					$this->newFieldNr = $GLOBALS['_GET']['field'];
				
				
				// Falls Felder vorhanden
				if(count($this->queryFormFields) > 0) {
				
					$this->adminContent .=	'<div class="actionBox">' . PHP_EOL .
											'<label class="markAll markBox" data-mark=".markFormFieldEntry"><input type="checkbox" id="markAllLB" data-select="all" /></label>' .
											'<label for="markAllLB" class="markAllLB"> {s_label:mark}</label>' .
											'<span class="editButtons-panel">' . PHP_EOL;
		
					// Button delete
					$btnDefs	= array(	"type"		=> "button",
											"class"		=> 'delAll delFormFields button-icon-only',
											"title"		=> '{s_title:delmarked}',
											"attr"		=> 'data-url="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&action=delfield&formid=' . $this->formID . '&fieldid=array&fieldname=array&tablename=' . $this->queryFormDetails[0]['table'] . '" data-action="delmultiple"',
											"icon"		=> "delete"
										);
						
					$this->adminContent .=	parent::getButton($btnDefs);
		
					$this->adminContent .=	'</span>' . PHP_EOL .
											'</div>';
					
					
					// Formularfelder auflisten
					foreach($this->queryFormFields as $formField) {
					
						// Falls kein Feldname angegeben
						if(empty($this->fieldName[$i]))
							$this->wrongInput[$i]['fieldName'] = "{s_error:name}";
						
					
						$fieldsLoop .=	'<li id="field-'.$i.'" class="formField sortItem' .
										(!isset($this->wrongInput[$i]) && $this->newFieldNr != $i && (!$this->queryFormDetails[0]['poll'] || $i > 1) ? ' collapse' : '') .
										(!empty($this->pagebreak[$i]) ? ' form-pagebreak-before' : '') .
										'" data-sortid="'.$i.'" data-newsortid="'.$i.'" data-id="'.$formField['id'].'"' . PHP_EOL .
										'>' . PHP_EOL;
										
						$fieldsLoop .=		'<span class="listEntryHeader">' . PHP_EOL .
											'<label class="markFormFieldEntry markBox">' . PHP_EOL .
											'<input type="checkbox" class="addVal" name="entryNr[' . $i . ']" />' . PHP_EOL .
											'<input type="hidden" class="getVal" name="entryID[' . $i . ']" value="' . $formField['id'] . '" />' . PHP_EOL .
											'<input type="hidden" class="getVal" name="entryName[' . $i . ']" value="' . $formField['field_name']  . '" />' . PHP_EOL .
											'</label>' . PHP_EOL .
											'<span class="formFieldHeader toggle" title="{s_title:toggledetails}">' . PHP_EOL .
											'<span class="fieldNumber">{s_header:field} #' . $i . '</span>' . PHP_EOL .
											'<span class="editButtons-panel">' . PHP_EOL;

						
						$fieldsLoop	.=		'<span class="left" title="{s_label:newfield}">' . PHP_EOL;
					
						// Feldtypauswahl für "neues Feld hinzufügen"
						$fieldsLoop .=	$this->getAddFormFieldSelect($i, false);
						
						$fieldsLoop	.=		'</span>' . PHP_EOL;
		
		
						// Button delete field
						$btnDefs	= array(	"type"		=> "button",
												"class"		=> 'delcon delfield button-icon-only',
												"title"		=> '{s_title:delfield}',
												"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&action=delfield&formid=' . $this->formID . '&fieldid=' . $formField['id'] . '&fieldname=' . $formField['field_name'] . '&tablename=' . $this->queryFormDetails[0]['table'] . '" value="' . $this->queryFormDetails[0]['table'] . '"',
												"icon"		=> "delete"
											);
							
						$fieldsLoop .=	parent::getButton($btnDefs);
			
						$fieldsLoop .=		'</span>' . PHP_EOL .
											'</span>' . PHP_EOL .
											'</span>' . PHP_EOL;
										
						// Panel body
						$fieldsLoop .=		'<div class="cc-formfield-panel-body">' . PHP_EOL;
						
						// Feldtyp
						$fieldsLoop .=		'<div class="left">' . PHP_EOL .
											'<label>{s_label:fieldtype}</label>' . PHP_EOL .
											'<span class="fieldType ' . $this->fieldType[$i] . ' type' . (isset($this->fieldRequired[$i]) && $this->fieldRequired[$i] == 1 ? ' requiredField" title="{s_label:fieldrequired}"' : ' optionalField" title="{s_label:fieldoptional}"') . '>'.parent::$staText['formfields'][$this->fieldType[$i]].'</span>' . PHP_EOL .
											'<input type="hidden" name="fieldType['.$i.']" value="'.$this->fieldType[$i].'" />' . PHP_EOL .
											#'</select>' . PHP_EOL .
											'</div>' . PHP_EOL;
						
						// Feldname
						$fieldsLoop .=		'<div class="rightBox">' . PHP_EOL .
											'<label>{s_label:fieldname}</label>' . PHP_EOL;
						
						if(isset($this->wrongInput[$i]['fieldName']))
							$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldName'] . '</p>' . PHP_EOL;
											
						$fieldsLoop .=		'<input type="text" name="fieldName['.$i.']" value="' . (isset($this->fieldName[$i]) && $this->fieldName[$i] != '' ? htmlspecialchars($this->fieldName[$i]) : '') . '" maxlength="50" />' . PHP_EOL . 
											'</div>' . PHP_EOL;
												
						$fieldsLoop .= 		'<p class="clearfloat">&nbsp;</p>' . PHP_EOL;
						
						// Feldrequired
						$fieldsLoop .= 		'<div class="left">' . PHP_EOL .
											'<label for="fieldReq-'.$i.'">{s_label:fieldrequired}</label>' . PHP_EOL .
											'<label class="markBox"><input type="checkbox" id="fieldReq-'.$i.'" name="fieldRequired['.$i.']"' . (isset($this->fieldRequired[$i]) && $this->fieldRequired[$i] == 1 ? ' checked="checked"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
											'</label></div>' . PHP_EOL;
						
						// Feldlabel
						$fieldsLoop .= 		'<div class="rightBox">' . PHP_EOL .
											'<label>{s_label:fieldlabel} <span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
											
						if(isset($this->wrongInput[$i]['fieldLabel']))
							$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldLabel'] . '</p>' . PHP_EOL;
											
						$fieldsLoop .=	'<input type="text" name="fieldLabel['.$i.']" value="' . (isset($this->fieldLabel[$i]) && $this->fieldLabel[$i] != '' ? htmlspecialchars($this->fieldLabel[$i]) : '') . '" maxlength="300" />' . PHP_EOL .
											'</div>' . PHP_EOL;
						
						$fieldsLoop .= 	'<p class="clearfloat">&nbsp;</p>' . PHP_EOL;
						
						// Falls Auswahlfelder
						if( $this->fieldType[$i] == "select" || 
							$this->fieldType[$i] == "multiple" || 
							$this->fieldType[$i] == "checkbox" || 
							$this->fieldType[$i] == "radio"
						) {
							
							// Feldoptions
							$fieldsLoop .= 	'<label>{s_label:fieldoptions} <span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
											'<textarea name="fieldOptions['.$i.']" id="fieldOptions-'.$i.'" class="fieldOptions noTinyMCE" rows="5">' . (isset($this->fieldOptions[$i]) && $this->fieldOptions[$i] != '' ? htmlspecialchars($this->fieldOptions[$i]) : '') . '</textarea>' . PHP_EOL;
							
							// Tag Script
							$fieldsLoop .= 	$this->getScriptTag('fieldOptions-'.$i);
						}
							
						// Begin Field-Details
						$fieldsLoop .= 	'<div class="formFieldDetails"';
						
						// Falls nicht neues Feld, Details ausblenden
						if(
						   !isset($this->wrongInput[$i]['fieldValue']) && 
						   !isset($this->wrongInput[$i]['fieldnotice']) && 
						   !isset($this->wrongInput[$i]['fieldMinLen']) && 
						   !isset($this->wrongInput[$i]['fieldMaxLen']) && 
						   !isset($this->wrongInput[$i]['fileTypes']) && 
						   !isset($this->wrongInput[$i]['fileSize']) && 
						   !isset($this->wrongInput[$i]['fileFolder']) && 
						   !isset($this->wrongInput[$i]['filePrefix']) && 
						   !isset($this->wrongInput[$i]['fieldLinkField']) && 
						   !isset($this->wrongInput[$i]['fieldLinkValue']) && 
						   !isset($this->wrongInput[$i]['fieldHeader'])
						  )
							$fieldsLoop .= ' style="display:none;"';
						
						#var_dump($this->wrongInput);
						
						$fieldsLoop .= 	'>' . PHP_EOL;
						
						// Falls nicht Dateifeld
						if( $this->fieldType[$i] != "file") {
							
							// Feldvalue
							$fieldsLoop .= 	'<label>{s_label:fieldvalue} ' . ($this->fieldType[$i] == "multiple" || $this->fieldType[$i] == "checkbox" ? '({s_label:fieldvalues})' : '') . '<span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
												
							if(isset($this->wrongInput[$i]['fieldValue']))
								$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldValue'] . '</p>' . PHP_EOL;
												
							$fieldsLoop .=	'<input type="text" name="fieldValue['.$i.']" value="' . (isset($this->fieldValue[$i]) && $this->fieldValue[$i] != '' ? htmlspecialchars($this->fieldValue[$i]) : '') . '" maxlength="300" />' . PHP_EOL;
						}
						
						// Falls Eingabefelder, Längenvorgaben einbinden
						if( $this->fieldType[$i] == "default" || 
							$this->fieldType[$i] == "textarea" || 
							$this->fieldType[$i] == "password" || 
							$this->fieldType[$i] == "int"
							) {
							
							// Mindestlänge
							$fieldsLoop .= 	'<div class="fullBox">' . PHP_EOL .
											'<div class="halfBox">' . PHP_EOL .
											'<label>{s_label:fieldminlen}</label>' . PHP_EOL;
												
							if(isset($this->wrongInput[$i]['fieldMinLen']))
								$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldMinLen'] . '</p>' . PHP_EOL;
												
							$fieldsLoop .=	'<input type="text" name="fieldMinLen['.$i.']" value="' . (isset($this->fieldMinLen[$i]) && $this->fieldMinLen[$i] != '' ? htmlspecialchars($this->fieldMinLen[$i]) : '') . '" maxlength="4" />' . PHP_EOL;
							
							// Maximallänge
							$fieldsLoop .= 	'</div><div class="halfBox">' . PHP_EOL .
											'<label>{s_label:fieldmaxlen}</label>' . PHP_EOL;
												
							if(isset($this->wrongInput[$i]['fieldMaxLen']))
								$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldMaxLen'] . '</p>' . PHP_EOL;
												
							$fieldsLoop .=	'<input type="text" name="fieldMaxLen['.$i.']" value="' . (isset($this->fieldMaxLen[$i]) && $this->fieldMaxLen[$i] != '' ? htmlspecialchars($this->fieldMaxLen[$i]) : '') . '" maxlength="4" />' . PHP_EOL;
							
							$fieldsLoop .= 	'</div></div><br class="clearfloat" />' . PHP_EOL;
						}
					
						// Feldnotice
						$fieldsLoop .= 	'<label>{s_label:fieldnotice}<span class="toggleEditor" data-target="fieldNotice-'.$i.'">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
											
						if(isset($this->wrongInput[$i]['fieldNotice']))
							$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldNotice'] . '</p>' . PHP_EOL;
										
						$fieldsLoop .=	'<textarea name="fieldNotice['.$i.']" rows="2" id="fieldNotice-'.$i.'" class="fieldNotice cc-editor-add disableEditor">' . (isset($this->fieldNotice[$i]) && $this->fieldNotice[$i] != '' ? htmlspecialchars($this->fieldNotice[$i]) : '') . '</textarea>' . PHP_EOL;
						
						// Falls Dateifeld
						if($this->fieldType[$i] == "file") {
								
							// Feldfiletypes
							$fieldsLoop .= 	'<label>{s_label:filetypes}</label>' . PHP_EOL;
												
							if(isset($this->wrongInput[$i]['fileTypes']))
								$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fileTypes'] . '</p>' . PHP_EOL;
												
							$fieldsLoop .=	'<input type="text" name="fieldValue['.$i.']" value="' . (isset($this->fileTypes[$i]) && $this->fileTypes[$i] != '' ? htmlspecialchars($this->fileTypes[$i]) : '') . '" maxlength="300" />' . PHP_EOL;
							
							// Feldfilesize
							$fieldsLoop .= 	'<label>' . sprintf(ContentsEngine::replaceStaText('{s_label:filesize}'), MIN_IMG_SIZE, MAX_IMG_SIZE) . '</label>' . PHP_EOL;
												
							if(isset($this->wrongInput[$i]['fileSize']))
								$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fileSize'] . '</p>' . PHP_EOL;
												
							$fieldsLoop .=	'<input type="text" name="fileSize['.$i.']" value="' . (isset($this->fileSize[$i]) && $this->fileSize[$i] != '' ? htmlspecialchars($this->fileSize[$i]) : '') . '" maxlength="4" />' . PHP_EOL;
							
							// Feldfilefolder
							$fieldsLoop .= 	'<label>{s_label:filefolder}</label>' . PHP_EOL;
												
							if(isset($this->wrongInput[$i]['fileFolder']))
								$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fileFolder'] . '</p>' . PHP_EOL;
												
							$fieldsLoop .=	'<input type="text" name="fileFolder['.$i.']" value="' . (isset($this->fileFolder[$i]) && $this->fileFolder[$i] != '' ? htmlspecialchars($this->fileFolder[$i]) : '') . '" maxlength="64" />' . PHP_EOL;
							
							// Feldfileprefix
							$fieldsLoop .= 	'<label>{s_label:fileprefix}</label>' . PHP_EOL;
												
							if(isset($this->wrongInput[$i]['filePrefix']))
								$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['filePrefix'] . '</p>' . PHP_EOL;
												
							$fieldsLoop .=	'<input type="text" name="filePrefix['.$i.']" value="' . (isset($this->filePrefix[$i]) && $this->filePrefix[$i] != '' ? htmlspecialchars($this->filePrefix[$i]) : '') . '" maxlength="100" />' . PHP_EOL;
							
							// Feldfilerename
							$fieldsLoop .= 	'<span class="form-fieldrow">' . PHP_EOL .
											'<label class="markBox"><input type="checkbox" id="input-fileRename-'.$i.'" name="fileRename['.$i.']"' . (isset($this->fileRename[$i]) && $this->fileRename[$i] == 1 ? ' checked="checked"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
											'<label for="input-fileRename-'.$i.'" class="inline-label">{s_label:filerename}</label>' . PHP_EOL .
											'</span>' . PHP_EOL;
							
							// Feldfilereplace
							$fieldsLoop .= 	'<span class="form-fieldrow">' . PHP_EOL .
											'<label class="markBox"><input type="checkbox" id="input-fileReplace-'.$i.'" name="fileReplace['.$i.']"' . (isset($this->fileReplace[$i]) && $this->fileReplace[$i] == 1 ? ' checked="checked"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
											'<label for="input-fileReplace-'.$i.'" class="inline-label">{s_label:filereplace}</label>' . PHP_EOL .
											'</span>' . PHP_EOL;
						}
							
						// Falls E-Mail
						if( $this->fieldType[$i] == "email")
								
							// Feldusemail
							$fieldsLoop .= 	'<span class="form-fieldrow">' . PHP_EOL .
											'<label class="markBox"><input type="checkbox" id="input-usemail-'.$i.'" name="usemail['.$i.']"' . (isset($this->usemail[$i]) && $this->usemail[$i] == 1 ? ' checked="checked"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
											'<label for="input-usemail-'.$i.'" class="inline-label">{s_label:usemail}</label>' . PHP_EOL .
											'</span>' . PHP_EOL;
						
						// Falls Passwort
						if( $this->fieldType[$i] == "password")
							
						// Feldshowpass
						$fieldsLoop .= 	'<span class="form-fieldrow">' . PHP_EOL .
										'<label class="markBox"><input type="checkbox" id="input-showpass-'.$i.'" name="showpass['.$i.']"' . (isset($this->showpass[$i]) && $this->showpass[$i] == 1 ? ' checked="checked"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
										'<label for="input-showpass-'.$i.'" class="inline-label">{s_label:showpass}</label>' . PHP_EOL .
										'</span>' . PHP_EOL;
					
						// Feldhidden
						$fieldsLoop .= 	'<span class="form-fieldrow">' . PHP_EOL .
										'<label class="markBox"><input type="checkbox" id="input-fieldHidden-'.$i.'" name="fieldHidden['.$i.']"' . ($this->fieldType[$i] == "hidden" || (isset($this->fieldHidden[$i]) && $this->fieldHidden[$i] == 1) ? ' checked="checked"' : '') . ($this->fieldType[$i] == "hidden" ? ' disabled="disabled"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
										'<label for="input-fieldHidden-'.$i.'" class="inline-label">{s_label:hidden}</label>' . PHP_EOL .
										'</span>' . PHP_EOL;
						
						// Falls hidden, verstecktes Feld mit hidden = 1
						if($this->fieldType[$i] == "hidden")
							$fieldsLoop .= '<input type="hidden" name="fieldHidden['.$i.']" value="on" /></label>' . PHP_EOL;
									   
						// Feldlink
						$fieldsLoop .= 	'<span class="form-fieldrow">' . PHP_EOL .
										'<label class="markBox"><input type="checkbox" id="input-fieldLink-'.$i.'" name="fieldLink['.$i.']"' . (isset($this->fieldLink[$i]) && $this->fieldLink[$i] == 1 ? ' checked="checked"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
										'<label for="input-fieldLink-'.$i.'" class="inline-label">{s_label:fieldlink}</label>' . PHP_EOL .
										'</span>' . PHP_EOL;
						
						// Feldfieldlinkfield
						$fieldsLoop .= 	'<label>{s_label:fieldlinkfield}</label>' . PHP_EOL;
											
						if(isset($this->wrongInput[$i]['fieldLinkField']))
							$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldLinkField'] . '</p>' . PHP_EOL;
											
						$fieldsLoop .=	'<input type="text" name="fieldLinkField['.$i.']" value="' . (isset($this->fieldLinkField[$i]) && $this->fieldLinkField[$i] != '' ? htmlspecialchars($this->fieldLinkField[$i]) : '') . '" maxlength="50" />' . PHP_EOL;
						
						// Feldfieldlinkvalue
						$fieldsLoop .= 	'<label>{s_label:fieldlinkvalue} <span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
											
						if(isset($this->wrongInput[$i]['fieldLinkValue']))
							$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldLinkValue'] . '</p>' . PHP_EOL;
											
						$fieldsLoop .=	'<input type="text" name="fieldLinkValue['.$i.']" value="' . (isset($this->fieldLinkValue[$i]) && $this->fieldLinkValue[$i] != '' ? htmlspecialchars($this->fieldLinkValue[$i]) : '') . '" maxlength="256" />' . PHP_EOL;
						
						// Feldheader
						$fieldsLoop .= 	'<label>{s_label:fieldheader} <span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;
						
						if(isset($this->wrongInput[$i]['fieldHeader']))
							$fieldsLoop .=	'<p class="notice">' . $this->wrongInput[$i]['fieldHeader'] . '</p>' . PHP_EOL;
											
						$fieldsLoop .=	'<input type="text" name="fieldHeader['.$i.']" value="' . (isset($this->fieldHeader[$i]) && $this->fieldHeader[$i] != '' ? htmlspecialchars($this->fieldHeader[$i]) : '') . '" maxlength="300" />' . PHP_EOL;
						
						// Feldremark
						$fieldsLoop .= 	'<label>{s_label:fieldremark}<span class="toggleEditor" data-target="fieldRemark-'.$i.'">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
										'<textarea name="fieldRemark['.$i.']" rows="2" id="fieldRemark-'.$i.'" class="formRemark cc-editor-add disableEditor">' . (isset($this->fieldRemark[$i]) && $this->fieldRemark[$i] != '' ? htmlspecialchars($this->fieldRemark[$i]) : '') . '</textarea>' . PHP_EOL;
						
						// Feldpagebreak
						$fieldsLoop .= 	'<span class="form-fieldrow">' . PHP_EOL .
										'<label class="markBox"><input type="checkbox" id="input-pagebreak-'.$i.'" name="pagebreak['.$i.']"' . (isset($this->pagebreak[$i]) && $this->pagebreak[$i] == 1 ? ' checked="checked"' : '') . ' class="floatingCheck" /></label>' . PHP_EOL .
										'<label for="input-pagebreak-'.$i.'" class="inline-label">{s_label:pagebreak}</label>' . PHP_EOL .
										'</span>' . PHP_EOL;
						
						
						// Ende Field-Details
						$fieldsLoop .= 	'</div>' . PHP_EOL;
						
						// Ende Panel body
						$fieldsLoop .= 	'</div>' . PHP_EOL;
						
						$fieldsLoop .=	'</li>' . PHP_EOL;
						
						$i++;
						
					} // Ende foreach
								
				} // count formFields
				
				
				#die(var_dump($this->wrongInput));
				// Sortable Funktionsaufruf, falls kein neuer Eintrag erstellt wurde und kein Fehler beim Feldnamen besteht
				if( !$this->arrayKeySearch_recursive('fieldName', $this->wrongInput) && 
					!isset($GLOBALS['_GET']['field']) && 
					count($this->queryFormFields) > 1
				) {
					$this->adminContent .=	'<script type="text/javascript">head.ready(\'ccInitScript\', function(){ $.addInitFunction({name: "$.sortableForm", params: "#sortableForm"}, true); });</script>'."\r\n";
					$this->adminContent .=	'<ul id="sortableForm" class="sortable-container ';
				}
				else
					$this->adminContent .=	'<ul class="';
					
				// Sortable-Url
				$this->adminContent .=	'formFields" data-url="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&action=sortfield&formid=' . $this->formID . '">' . PHP_EOL;
				
				// Felder einfügen
				$this->adminContent .=	$fieldsLoop;
				
				// Buttons
				$this->adminContent .=	'</ul>' . PHP_EOL .
										'<li class="submit change">' . PHP_EOL;

				
				$this->adminContent .=	'<div class="right" title="{s_label:newfield}">' . PHP_EOL;
				
				// Feldtypauswahl für "neues Feld hinzufügen"
				$this->adminContent .=	$this->getAddFormFieldSelect($i-1);
				
				$this->adminContent .=	'</div>' . PHP_EOL;
				
				
				// Button "Änderungen übernehmen"
				$btnDefs	= array(	"type"		=> "submit",
										"name"		=> "edit_fields",
										"class"		=> "change",
										"value"		=> "{s_button:takechange}",
										"icon"		=> "ok"
									);
				
				$this->adminContent	.=	parent::getButton($btnDefs);
			
				$this->adminContent .=	'<input type="hidden" name="edit_fields" value="{s_button:takechange}" />' . PHP_EOL;


				$this->adminContent .=	'<input type="hidden" name="form_id" value="' . $this->formID . '" />' . PHP_EOL .
										'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL .
										'<br class="clearfloat" />' . PHP_EOL .
										'</li>' . PHP_EOL .
										'</form>' . PHP_EOL;
				
				if(!$this->listData) {
					
					// Formular mit Buttons zum Zurückgehen
					$this->adminContent .=	$this->getFormBackButtons("fields");
				}
			
				$this->adminContent .=	'</ul>' . PHP_EOL;
			
			
				// Selectmenu Script
				$this->adminContent .=	$this->getSelectmenuScript();
			
			
			} // Ende falls einzelnes Formular
			
			else {
				
				$noDataNote	= "";
				
				 // Formular-Auswahlliste (edit)
				 $this->adminContent .=	'<div class="controlBar">' . PHP_EOL .
										'<form action="' . $this->formAction . '" method="post" accept-charset="UTF-8" data-getcontent="fullpage">' . PHP_EOL . 
										'<div class="leftBox"><label>{s_label:forms}</label>' . PHP_EOL .
										'<select name="list_fields" class="selectForm autoSubmit">' . PHP_EOL;
									
				$this->adminContent .=	'<option value="" disabled="disabled" selected="selected">{s_option:choose}</option>';
					
				
				// Formulare auflisten
				foreach($this->existForms as $formData) {
					
					$this->adminContent .='<option value="' . $formData['id'] . '"';
					
					if($formData['id'] == $this->formID)
						$this->adminContent .=' selected="selected"';
						
					$this->adminContent .= '>' . $formData['table'] . ' ('.$formData['title_' . $this->editLang].')</option>' . PHP_EOL;
				
				}
				
				$this->adminContent .= 	'</select></div><br class="clearfloat" />' . PHP_EOL;
				
				
				if(!isset($GLOBALS['_POST']['list_fields']) || $GLOBALS['_POST']['list_fields'] == "")
					$noDataNote = 		'<p class="notice error">{s_text:nodatasel}</p>' . PHP_EOL;
				
				$this->adminContent .= 	'</form></div>' . PHP_EOL;
				
				$this->adminContent .=	$noDataNote;
					
			} // Ende else nicht einzelnes Formular
			
			$this->adminContent .=		'</div>' . PHP_EOL;
			
			
			
			#############################################################
			// Formulardaten aus DB anzeigen (aus Auswahlliste auswählen)
			#############################################################
					
			// Sortierung
			if(isset($GLOBALS['_POST']['sort_fieldname']) &&
			 (!isset($GLOBALS['_POST']['formtable_old']) || $GLOBALS['_POST']['formtable_old'] == $this->formID)
			)
				$sortField = $GLOBALS['_POST']['sort_fieldname'];
			elseif(isset($GLOBALS['_GET']['sort_fieldname']))
				$sortField = $GLOBALS['_GET']['sort_fieldname'];
			else
				$sortField = "id";
			
			if(isset($GLOBALS['_POST']['sort_dir']))
				$sortDirection = $GLOBALS['_POST']['sort_dir'];
			elseif(isset($GLOBALS['_GET']['sort_dir']))
				$sortDirection = $GLOBALS['_GET']['sort_dir'];
			else
				$sortDirection = "desc";
			
			$dbOrder = " ORDER BY `" . $this->DB->escapeString($sortField) . "` " . $this->DB->escapeString(strtoupper($sortDirection));
			
			$this->adminContent .= 	'<h3 class="cc-h3 switchToggle';
			
			if(!$this->listData
			&& !$this->editData
			)
			
				$this->adminContent .=' hideNext';
				
			$this->adminContent .=	'">{s_header:formdata} ' . (!empty($this->formID) ? '(#' . $this->formID . ')' : '') . '</h3>' . PHP_EOL;			
			
			$this->adminContent .=	'<div class="adminBox">' . PHP_EOL;
				
			// Formular-Auswahlliste (formdata)
			$this->adminContent .=	'<div class="controlBar">' . PHP_EOL .
									'<form action="' . $this->formAction . '" method="post" accept-charset="UTF-8" data-getcontent="fullpage">' . PHP_EOL . 
									'<div class="leftBox"><label>{s_label:forms}</label>' . PHP_EOL .
									'<select name="list_formdata" class="selectForm autoSubmit">' . PHP_EOL;
									
			$this->adminContent .=	'<option value="" disabled="disabled" selected="selected">{s_option:choose}</option>';
					
				
			// Formulare auflisten
			foreach($this->existForms as $formData) {
				
				$this->adminContent .='<option value="' . $formData['id'] . '"';
				
				if($formData['id'] == $this->formID)
					$this->adminContent .=' selected="selected"';
					
				$this->adminContent .= '>' . $formData['table'] . ' ('.$formData['title_' . $this->editLang].')</option>' . PHP_EOL;
			
			}
			
			$this->adminContent .= 	'</select></div>' . PHP_EOL;
			
			// Hidden Field für alte FormData Tabelle
			$this->adminContent .=	'<input type="hidden" name="formtable_old" value="' . $this->formID . '" />';
			
			
			// Falls ein einzelnes Formular zum Auswerten in der Session gespeichert ist
			if(!empty($this->formID)
			&& !empty($this->queryFormDetails)
			) {
				
				$formTable = $this->queryFormDetails[0]['table'];
				
				// db-Query nach Daten (Benutzereingaben) für das ausgewählte Formular
				$this->queryFormData = $this->getFormData($formTable, $dbOrder);
				
				
				$totalRows	= count($this->queryFormData);
				$startRow	= "";
				$pageNum	= 0;
				$limit		= 25;
				
				// Anzahl an anzuzeigenden Datensätzen
				$limit		= $this->getLimit($limit);
				
				
				if($totalRows > 0 && count($this->queryFormFields) > 0) {
				
					// Pagination
					if(isset($GLOBALS['_GET']['pageNum']))
						$pageNum = $GLOBALS['_GET']['pageNum'];
					
				
					$startRow = $pageNum * $limit;
					$queryLimit = " LIMIT " . $startRow . "," . $limit;
					$queryString = "admin?task=forms&list_fields=$this->formID&sort_fieldname=$sortField&sort_dir=$sortDirection&limit=$limit";
					

					// db-Query nach News
					$this->queryFormData = $this->getFormData($formTable, $dbOrder . $queryLimit);
					
					// Sortierungsoptionen Feldname
					$this->adminContent .= 	'<div class="rightBox">' . PHP_EOL .
											'<div class="fullBox"><label>{s_label:sort}</label>' . PHP_EOL .
											'<select name="sort_fieldname" class="selectFormFields" data-action="autosubmit">' . PHP_EOL;
				
					$formFieldOpt	= "";
					$ff				= -1;
				
					$foreignKey		= false;
						
					if($this->queryFormFields[0]['foreign_key'] != "") {
						$foreignKey = true;
					}
					
					// Feldnamen auslesen und als Optionen für Sortierungsauswahl nehmen
					foreach($this->queryFormData[0] as $fieldName => $fieldVal) {
						
						$fieldLabel = !empty($this->queryFormFields[$ff]['label_' . $this->editLang]) ? $this->queryFormFields[$ff]['label_' . $this->editLang] : $fieldName;
					
						// Beim ersten Datensatz Feldnamen als Überschriften einfügen
						$formFieldOpt .=	'<option' . ($sortField == $fieldName ? ' selected="selected"' : '') . ' value="' . ($ff == -1 ? 'id' : ($ff == 0 && $foreignKey == true ? $this->queryFormFields[$ff]['foreign_key'] : $fieldName)) . '">' . ($ff == -1 ? 'id' : ($ff == 0 && $foreignKey == true ? $this->queryFormFields[1]['foreign_key'] : $fieldLabel)) . '</option>';
							
						// Falls kein Fremdschlüsselwert eingefügt wurde, Zähler hochzählen
						if($ff == 0 && $foreignKey == true)
							$foreignKey	= false;
						else
							$ff++;
					}
						
					$this->adminContent .= 	$formFieldOpt . '</select></div></div>' . PHP_EOL;

					
					// Suche
					$this->adminContent .=	'<div class="leftBox"><label>{s_label:search}</label>' . PHP_EOL .
											'<input class="cc-input-table-search" type="text" name="search_formdata" value="" data-target="#formDataList table.formData" />' . PHP_EOL .
											'</div>' . PHP_EOL;
					
					
					// Sortierungsoptionen (asc/dsc)
					$this->adminContent .= 	'<div class="rightBox">' . PHP_EOL .
											'<div class="halfBox"><label>{s_label:sort}</label>' . PHP_EOL .
											'<select name="sort_dir" class="listCat" data-action="autosubmit">' . PHP_EOL;
					
					$sortOptions = array("asc" => "{s_option:asc}",
										 "desc" => "{s_option:dsc}"
										 );
					
					foreach($sortOptions as $key => $value) { // Sortierungsoptionen auflisten
						
						$this->adminContent .='<option value="' . $key . '"';
						
						if($key == $sortDirection)
							$this->adminContent .=' selected="selected"';
							
						$this->adminContent .= '>' . $value . '</option>' . PHP_EOL;
					
					}
										
					$this->adminContent .= 	'</select></div>' . PHP_EOL;
					
					// Anzahl an Datensätzen begrenzen
					$this->adminContent .= 	'<div class="halfBox"><label>{s_label:limit}</label>' . PHP_EOL;
					
					$this->adminContent .=	$this->getLimitSelect($this->limitOptions, $limit);
										
					$this->adminContent .= 	'</div>' . PHP_EOL;
					$this->adminContent .= 	'</div>' . PHP_EOL;
					
				} // Ende if $totalRows > 0
				
				
			} // formID != ""
			
			$this->adminContent .= 	'<br class="clearfloat" />' . PHP_EOL;			
			$this->adminContent .= 	'</form></div>' . PHP_EOL;
				

			if(!empty($this->formID)
			&& !empty($this->queryFormDetails)
			) {
			
				$this->adminContent .=	'<h4 class="cc-h4 listFormData toggle" data-toggle="expand" data-target="formDataList">'.$this->queryFormDetails[0]['table'] . ' (' . $this->queryFormDetails[0]['title_' . $this->editLang].')<span class="right">{s_label:form} #' . $this->formID . '</span></h4>' . PHP_EOL;
			
				
					
				####################################
				// Formulardatenauswertung einbinden
				####################################	
				if(count($this->queryFormData) > 0) {
				
					require_once(PROJECT_DOC_ROOT . '/inc/classes/Forms/class.FormEvaluation.php');
					$o_form = new FormEvaluation($formTable, "", array(), false, array("admin", "editor"));
		
					$o_form->DB			= $this->DB;
					$o_form->lang		= $this->lang;
					$o_form->adminLang	= $this->adminLang;
					$o_form->editLang	= $this->editLang;
					$o_form->adminPage	= $this->adminPage;
					$o_form->themeConf	= $this->themeConf;
		
					// EventDispatcher
					$o_form->o_dispatcher	= $this->o_dispatcher;


					// Formulardaten (list oder edit)
					$formData			= $o_form->getFormData();
					
					
					if($this->editData){
						$this->adminContent .=	'<div class="adminSection">' . PHP_EOL .
												$formData .
												'</div>' . PHP_EOL;
					}
					else {
						$this->adminContent .= $formData;

						// Formdata script
						#$this->adminContent .=	$this->getFormdataToPDFScript(); // not working yet
					}
					
					
					// Pagination Nav
					#$this->adminContent .= Modules::getPageNav($limit, $totalRows, $startRow, $pageNum, $queryString, "", true, parent::getLimitForm($this->limitOptions, $limit));
					

					// Formular mit Buttons zum Zurückgehen
					$this->adminContent .=	$this->getFormBackButtons("data");
					
				
				}
				else {
					
					$this->adminContent .=	'<p class="notice error">{s_text:nodata}</p>' . PHP_EOL;

				}
			
			}
			
			############################################
			// Andernfalls Formularauswahlliste anzeigen
			############################################
			else {
				
				$noDataNote = 		'<p class="notice error">{s_text:nodatasel}</p>' . PHP_EOL;
				
				$this->adminContent .=	$noDataNote;
				
			} // Ende else Auswahlliste

			$this->adminContent .= 	'</div>' . PHP_EOL;
		
		
		} // Ende if existForms
	

		// Backbuttons
		$this->adminContent .= 	'</div>' . PHP_EOL;

		$this->adminContent .=	$this->getBackButtons("main");

		} // Ende if no lock

		else {
			if(isset($this->g_Session['form_id']))
				$this->unsetSessionKey('form_id');
		}
		
		
		// #adminContent close
		$this->adminContent	.= $this->closeAdminContent();
		
		
		// Panel for rightbar
		$this->adminRightBarContents[]	= $this->getFormRightBarContents($this->formID);
		
		
		// Falls Ajax
		if(($this->ajax || isset($GLOBALS['_GET']['isajax']))
		&& empty($GLOBALS['_GET']['fullpage'])
		) {
			$this->footerAction(); // Footer setzen
			echo parent::replaceStaText($this->adminContent);
			exit;
		}
		
		return $this->adminContent;

	}

	
	// evalUserRequests
	public function evalUserRequests()
	{
		
		// Globale Request vars auslesen
		if(isset($this->g_Session['form_id']) && $this->g_Session['form_id'] != "") {
			$this->listFields	= true;
			$this->formID		= $this->g_Session['form_id'];
		}

		if(isset($this->g_Session['list_fields'])) {
			$this->listFields	= true;
			$this->formID		= $this->g_Session['list_fields'];
			$this->unsetSessionKey('list_fields');
		}

		if(isset($this->g_Session['edit_fields'])) {
			$this->listFields	= true;
			$this->formID		= $this->g_Session['edit_fields'];
			$this->unsetSessionKey('edit_fields');
		}

		if(isset($GLOBALS['_POST']['edit_form'])) {
			$this->editForm		= true;
			$this->listFields	= false;
			$this->formID		= $GLOBALS['_POST']['edit_form'];
		}

		if(isset($GLOBALS['_POST']['list_fields'])) {
			$this->listFields	= true;
			$this->formID		= $GLOBALS['_POST']['list_fields'];
		}

		if(isset($GLOBALS['_POST']['edit_fields']) && isset($GLOBALS['_POST']['form_id'])) {
			$this->editFields	= true;
			$this->listFields	= true;
			$this->formID		= $GLOBALS['_POST']['form_id'];
		}
				
		if(isset($GLOBALS['_POST']['add_new']) && isset($this->g_Session['form_id'])) {
			$this->listFields	= true;
			$this->formID		= $this->g_Session['form_id'];
			$this->unsetSessionKey('form_id');
		}
		
		if(isset($GLOBALS['_POST']['list_formdata'])) {
			$this->listData		= true;
			$this->formID		= $GLOBALS['_POST']['list_formdata'];
		}
		
		if(!empty($GLOBALS['_GET']['list_fields'])) {
			$this->listFields	= true;
			$this->formID		= $GLOBALS['_GET']['list_fields'];
		}
		
		if(!empty($GLOBALS['_GET']['list_formdata'])) {
			$this->listData		= true;
			$this->formID		= $GLOBALS['_GET']['list_formdata'];
		}
		
		if(!empty($GLOBALS['_GET']['list_formdata']) && !empty($GLOBALS['_GET']['edit_eid'])) {
			$this->editData		= true;
			$this->formID		= $GLOBALS['_GET']['list_formdata'];
		}
		
		if(empty($GLOBALS['_POST']) && isset($GLOBALS['_GET']['formname'])) {
			$this->newForm		= true;
		}
		
		if(isset($GLOBALS['_POST']['new_form'])) {
			$this->newForm		= true;
		}
		
		elseif(isset($GLOBALS['_POST']['new_form'])) {
			$this->editForm		= true;
		}
		
		if(!is_numeric($this->formID)) {
			$this->formID		= "";
		}
	
	}

	
	// initEditForm
	public function initEditForm()
	{
		
		if(isset($GLOBALS['_POST']['nochange']) && $GLOBALS['_POST']['nochange'] == "1") {
			$this->noChange = true;
		}
	
		// Locking checken
		if($this->checkLocking($this->formID, "forms", $this->g_Session['username'])) {
			$this->dataLock	= true;
			$this->noChange	= true;
			$this->adminContent .=	$this->getBackButtons("main");
			return $this->adminContent;
		}

		// Falls kein Lock
		// db-Query nach Feldern des zu bearbeitenden Formulars
		$this->queryFormDetails = $this->getFormDetails($this->formIDdb, $this->editLang);
		
	}

	
	// readFormPost
	public function readFormPost()
	{

		$this->form				= Files::getValidFileName(trim($GLOBALS['_POST']['formName']));
		$this->form				= parent::sanitizeTableName($this->form);
		$this->form				= strtolower($this->form);
		$this->formTitle		= empty($GLOBALS['_POST']['formTitle']) ? $this->form : $GLOBALS['_POST']['formTitle'];
		$this->formForeignKey	= $GLOBALS['_POST']['formForeignKey'];
		$this->formPoll			= $GLOBALS['_POST']['formPoll'];
		$this->formActive		= $GLOBALS['_POST']['formActive'];
		$this->formEndDate		= trim($GLOBALS['_POST']['formEndDate']);
		$this->formSuccess		= trim($GLOBALS['_POST']['formSuccess']);
		$this->formError		= trim($GLOBALS['_POST']['formError']);
		$this->formFieldNotice	= trim($GLOBALS['_POST']['formFieldNotice']);
		$this->formCaptcha		= (int)$GLOBALS['_POST']['formCaptcha'];
		$this->formHttps		= (int)$GLOBALS['_POST']['formHttps'];
		$this->formAddTable		= trim($GLOBALS['_POST']['formAddTable']);
		$this->formAddFields	= trim($GLOBALS['_POST']['formAddFields']);
		$this->formAddLabels	= trim($GLOBALS['_POST']['formAddLabels']);
		$this->formAddPosition	= trim($GLOBALS['_POST']['formAddPosition']);
	}

	
	// conductFormChanges
	public function conductFormChanges()
	{

		// Überprüfen ob Formularname bereits vorhanden
		$this->checkFormExists();
		
		// Prüft ob Tabelle vorhanden ist
		if($this->DB->tableExists(DB_TABLE_PREFIX . 'form_' . $this->DB->escapeString($this->form)))
			$this->existFormsArray = true;

		
		if($this->form == "")
			$this->wrongInput['formname'] = "{s_notice:noform}";

		elseif(!parent::validateDbTableName($this->form))
			$this->wrongInput['formname'] = "{s_notice:wrongname}";

		elseif(strlen($this->form) > 50)
			$this->wrongInput['formname'] = "{s_notice:longname}";
		
		elseif($this->existFormsArray == true && $this->form != $this->queryFormDetails[0]['table'])
			$this->wrongInput['formname'] = "{s_notice:formexist}";
		
		else
			// Formular ändern/neu speichern
			$this->saveForm();
	
	}

	
	// getExistingForms
	public function getExistingForms()
	{

		$existForms = $this->DB->query("SELECT * 
										FROM `$this->tableForms` 
										ORDER BY `table` ASC;
										");
		#var_dump($existForms);
		
		return $existForms;
	
	}

	
	// checkFormExists
	public function checkFormExists()
	{

		// Überprüfen ob Formularname bereits vorhanden
		if(count($this->existForms) > 0) {
		
			for($j = 0; $j < count($this->existForms); $j++) {
				if(in_array($this->form, $this->existForms[$j]) && $this->existForms[$j]["table"] == $this->form) {
					if(empty($this->formID) || $this->existForms[$j]["id"] != $this->formID)
						$this->existFormsArray = true;
				}
			}
		}
		return $this->existFormsArray;
	}

	
	// saveForm
	public function saveForm()
	{
	
		// Formular ändern/neu speichern
		$formDb				= $this->DB->escapeString($this->form); // Safestring
		$formTitleDb		= $this->DB->escapeString($this->formTitle);
		$formForeignKeyDb	= $this->DB->escapeString($this->formForeignKey);
		$formPollDb			= $this->DB->escapeString($this->formPoll);
		$formActiveDb		= $this->DB->escapeString($this->formActive);
		$formEndDateDb		= $this->formEndDate == "" || $this->formEndDate == "NULL" ? "NULL" : "'".$this->DB->escapeString(implode("-", array_reverse(explode(".", $this->formEndDate))) . " 00:00:00")."'";
		$formSuccessDb		= $this->DB->escapeString($this->formSuccess);
		$formErrorDb		= $this->DB->escapeString($this->formError);
		$formFieldNoticeDb	= $this->DB->escapeString($this->formFieldNotice);
		$formCaptchaDb		= $this->DB->escapeString($this->formCaptcha);
		$formHttpsDb		= $this->DB->escapeString($this->formHttps);
		$formAddTableDb		= $this->DB->escapeString($this->formAddTable);
		$formAddFieldsDb	= $this->DB->escapeString($this->formAddFields);
		$formAddLabelsDb	= $this->DB->escapeString($this->formAddLabels);
		$formAddPositionDb	= $this->DB->escapeString($this->formAddPosition);
		$dbInsertStr1		= "";
		$dbInsertStr2		= "";
		$dbUpdateStr		= "";
		$foreignKeyInsert	= "";
		
		
		$dbInsertStr1 .= "`table`,`title_" . $this->editLang . "`,`foreign_key`,`notice_success_" . $this->editLang . "`,`notice_error_" . $this->editLang . "`,`notice_field_" . $this->editLang . "`,`captcha`,`https`,`poll`,`active`,`end_date`,`add_table`,`add_fields`,`add_labels_" . $this->editLang . "`,`add_position`,";
		$dbInsertStr2 .= "'" . $formDb . "','" . $formTitleDb . "','" . $formForeignKeyDb . "','" . $formSuccessDb . "','" . $formErrorDb . "','" . $formFieldNoticeDb . "'," . $formCaptchaDb . "," . $formHttpsDb . "," . $formPollDb . "," . $formActiveDb . "," . $formEndDateDb . ",'" . $formAddTableDb . "','" . $formAddFieldsDb . "','" . $formAddLabelsDb . "','" . $formAddPositionDb . "',";
			
		$dbUpdateStr .= "`title_" . $this->editLang . "` = '".$formTitleDb."',`foreign_key` = '" . $formForeignKeyDb . "',`notice_success_" . $this->editLang . "` = '" . $formSuccessDb . "',`notice_error_" . $this->editLang . "` = '".$formErrorDb."',`notice_field_" . $this->editLang . "` = '".$formFieldNoticeDb."',`captcha` = " . $formCaptchaDb . ",`https` = " . $formHttpsDb . ",`poll` = " . $formPollDb . ",`active` = " . $formActiveDb . ",`end_date` = " . $formEndDateDb . ",`add_table` = '".$formAddTableDb."',`add_fields` = '".$formAddFieldsDb."',`add_labels_" . $this->editLang . "` = '".$formAddLabelsDb."',`add_position` = '".$formAddPositionDb."',";
			
			
		$dbInsertStr1 = substr($dbInsertStr1, 0, -1);
		$dbInsertStr2 = substr($dbInsertStr2, 0, -1);
		
		$dbUpdateStr = substr($dbUpdateStr, 0, -1);
		
		
		// db-Tabelle sperren		
		$lock = $this->DB->query("LOCK TABLES `$this->tableForms`");
		
		
		// Falls geändert wird
		if($this->editForm) {
			
			
			// Aktualisierung der Sort-IDs
			$this->updateSQL = $this->DB->query("UPDATE `$this->tableForms` 
												SET $dbUpdateStr 
												WHERE `id` = $this->formIDdb;
												");
			
		
			$colSQL = $this->DB->query("SHOW COLUMNS FROM `" . DB_TABLE_PREFIX . "form_".$formDb."`;");
			#var_dump($this->updateSQL);
			
			
			// Forein Key auslesen
			if($colSQL) {
				
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "form_".$formDb."`");


				// Falls keine Spalte username vorhanden, diese anlegen
				if($this->formForeignKey == "username" && (!isset($colSQL[1]['Field']) || $colSQL[1]['Field'] != "username")) {
					
					// Aktualisierung der Sort-IDs
					$alterSQL = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$formDb."` 
														 ADD `username` VARCHAR( 64 ) NOT NULL AFTER `id`,
														 ADD INDEX ( `username` )
														");
					
					// FK-Check abstellen
					$fkSQL = $this->DB->query("SET foreign_key_checks = 0");
					
					
					// Aktualisierung der Sort-IDs
					$alterSQL = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$formDb."` 
														 ADD FOREIGN KEY (`username`) REFERENCES `user` (`username`)
														");
					
					// FK-Check anstellen
					$fkSQL = $this->DB->query("SET foreign_key_checks = 1");
					
					#var_dump($alterSQL);
				}
					
				// Falls keine Spalte username vorhanden, diese anlegen
				elseif($this->formForeignKey != "username" && isset($colSQL[1]['Field']) && $colSQL[1]['Field'] == "username") {
						
					
					// Aktualisierung der Sort-IDs
					$alterSQL = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$formDb."` 
														 DROP FOREIGN KEY `form_".$formDb."_ibfk_1`
														");
					// Aktualisierung der Sort-IDs
					$alterSQL = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$formDb."` 
														 DROP `username`
														");
					#var_dump($alterSQL);
				}
			}
		}
		
		// Falls neu
		else {
			
			// Forein Key auslesen
			if($this->formForeignKey == "username") {
				
				$foreignKeyInsert = ",
									 `username` VARCHAR( 64 ) NOT NULL,
									 INDEX ( `username` ),
									 FOREIGN KEY (`username`) REFERENCES `user` (`username`)";
									 
			}
			
			// Einfügen des neuen Formulars
			$this->insertSQL = $this->DB->query("INSERT INTO `$this->tableForms` 
												($dbInsertStr1) 
												VALUES ($dbInsertStr2)
												");
			
			
			// Formulardaten-Tabelle anlegen
			$createSQL = $this->DB->query("CREATE TABLE `" . DB_TABLE_PREFIX . "form_".$formDb."` 
												(`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY"
												 .$foreignKeyInsert."
												) 
												ENGINE = InnoDB 
												CHARACTER SET utf8 
												COLLATE utf8_general_ci
												", true);
			
			#var_dump($this->insertSQL);
		}

	
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		
		
		// Falls Neuanlegen bzw. Bearbeiten erfolgreich
		if(($this->insertSQL == true || $this->updateSQL == true) && !isset($GLOBALS['_POST']['go_edit_form'])) {
		

			// Falls Poll, verstecktes Formularfeld für Timestamp und IP-hash einfügen
			if($this->formPoll) {
				
				$presentFields = array();
				
				// Falls neues Formular, FormID ermitteln
				if($this->insertSQL == true) {
				
					$formIDQuery = $this->DB->query("SELECT `id` 
															FROM `$this->tableForms` 
															WHERE `table` = '$formDb';
														");
					$this->formID = $formIDQuery[0]['id'];
				
				}
				else { // Falls Update, überprüfen ob noch keine Felder angelegt sind. Nur dann automatisch 2 Pollfelder anlegen
				
					$presentFields = $this->DB->query("SELECT * 
														FROM `$this->tableFormsDefs` 
														WHERE `table_id` = $this->formIDdb;
														");
					#var_dump($presentFields);
				}
				
				// Falls noch keine Felder angelgt wurden
				if(count($presentFields) == 0) {
				
					$labStr	= "";
					$valStr	= "";
					$optStr	= "";
					$labVal	= array(0 => "", 1 => "", 2 => "");
					$valVal	= array(0 => "", 1 => "", 2 => "");
					$optVal	= array(0 => "", 1 => "", 2 => "");
					
					foreach($this->installedLangs as $eLang) {
					
						$labStr		.= "`label_" . $eLang . "`,";
						$valStr		.= "`value_" . $eLang . "`,";
						$optStr		.= "`options_" . $eLang . "`,";
						$labVal[0]	.= "'Auswahloptionen',";
						$valVal[0]	.= "'',";
						$optVal[0]	.= "'ja\r\nnein\r\nvielleicht',";
						$labVal[1]	.= "'Date',";
						$valVal[1]	.= "'{#datetime}',";
						$optVal[1]	.= "'',";
						$labVal[2]	.= "'IP-hash',";
						$valVal[2]	.= "'{#ip}',";
						$optVal[2]	.= "'',";
					}
					// Komma entfernen
					$labStr	= substr($labStr, 0, -1);
					$valStr	= substr($valStr, 0, -1);
					$optStr	= substr($optStr, 0, -1);
					$labVal[0]	= substr($labVal[0], 0, -1);
					$valVal[0]	= substr($valVal[0], 0, -1);
					$optVal[0]	= substr($optVal[0], 0, -1);
					$labVal[1]	= substr($labVal[1], 0, -1);
					$valVal[1]	= substr($valVal[1], 0, -1);
					$optVal[1]	= substr($optVal[1], 0, -1);
					$labVal[2]	= substr($labVal[2], 0, -1);
					$valVal[2]	= substr($valVal[2], 0, -1);
					$optVal[2]	= substr($optVal[2], 0, -1);
				
					// db-Tabelle sperren
					$lock = $this->DB->query("LOCK TABLES `$this->tableForms`");
					
					
					// Neuen Feldtyp einfügen
					$this->insertSQL = $this->DB->query("INSERT INTO `$this->tableFormsDefs` 
														(`table_id`, `sort_id`, `type`, `field_name`, " . $labStr . ", " . $valStr . ", " . $optStr . ")
														VALUES 
														($this->formIDdb, 1, 'radio', 'options', " . $labVal[0] . ", " . $valVal[0] . ", " . $optVal[0] . "),
														($this->formIDdb, 2, 'hidden', 'date', " . $labVal[1] . ", " . $valVal[1] . ", " . $optVal[1] . "),
														($this->formIDdb, 3, 'hidden', 'ip-hash', " . $labVal[2] . ", " . $valVal[2] . ", " . $optVal[2] . ")
														", false);
					
					#die(var_dump($this->insertSQL));
					
					// db-Sperre aufheben
					$unLock = $this->DB->query("UNLOCK TABLES");
				}				
			}

		
			// Meldung
			$this->setSessionVar('notice', $this->success);
			
			if($this->insertSQL == true) {
				
				// db-Query nach letzter ID
				$ID = $this->DB->query("SELECT MAX(`id`)  
											FROM `$this->tableForms`;
										", false);
				
				
				#var_dump($ID);
				
				if(is_numeric($ID[0]['MAX(`id`)']))
					$this->setSessionVar('list_fields', $ID[0]['MAX(`id`)']);
			}
								
			header("Location: " . ADMIN_HTTP_ROOT . "?task=forms");
			exit;
		}
		
		// db-Query nach bestehenden Formularen (aktualisieren)
		$this->existForms = $this->getExistingForms();

	}
	

	// getFormDetails
	public function getFormDetails($formID, $lang)
	{

		if(empty($formID))
			return array();
		
		// db-Query nach Feldern des zu bearbeitenden Formulars
		$queryFormDetails = $this->DB->query( "SELECT `table`,`title_" . $lang . "`, `poll` 
												FROM `$this->tableForms` 
												WHERE `id` = $formID;
												");
		#var_dump($queryFormDetails);
		
		return $queryFormDetails;
	}

	
	// getFormFieldDetails
	public function getFormFieldDetails($formID)
	{

		// db-Query nach Feldern des zu bearbeitenden Formulars
		$queryFormFields = $this->DB->query( "SELECT p.*, n.`foreign_key`  
												FROM `$this->tableForms` AS n 
												LEFT JOIN `$this->tableFormsDefs` AS p 
												ON n.`id` = p.`table_id` 
												WHERE p.`table_id` = $formID 
												ORDER BY `sort_id` ASC;
												");
		
		return $queryFormFields;
	
	}

	
	// Formularfelder anzeigen/auslesen
	// readFormFields
	public function readFormFields($queryFormFields)
	{
		
		$i = 0;
		
		// Formularfelder anzeigen/auslesen
		foreach($queryFormFields as $formField) {
		

			$j = $i + 1;
			
			$this->fieldID[$j]			= $formField['id'];
			$this->fieldSortID[$j]		= $formField['sort_id'];
			$this->fieldType[$j]		= $formField['type'];
			$this->fieldName[$j]		= $formField['field_name'];
			$this->fieldNameOld[$j]		= $formField['field_name'];
			$this->fieldRequired[$j]	= $formField['required'];
			$this->fieldRequiredOld[$j]	= $formField['required'];
			$this->fieldHidden[$j]		= $formField['hidden'];
			$this->fieldLabel[$j]		= $formField['label_' . $this->editLang];
			$this->fieldValue[$j]		= $formField['value_' . $this->editLang];
			$this->fieldMinLen[$j]		= $formField['min_length'];
			$this->fieldMaxLen[$j]		= $formField['max_length'];
			$this->fieldMaxLenOld[$j]	= $formField['max_length'];
			$this->fieldOptions[$j]		= $formField['options_' . $this->editLang];
			$this->fieldNotice[$j]		= $formField['notice_' . $this->editLang];
			$this->fileTypes[$j]		= $formField['filetypes'];
			$this->fileSize[$j]			= $formField['filesize'];
			$this->fileFolder[$j]		= $formField['filefolder'];
			$this->filePrefix[$j]		= $formField['fileprefix'];
			$this->fileRename[$j]		= $formField['filerename'];
			$this->fileReplace[$j]		= $formField['filereplace'];
			$this->usemail[$j]			= $formField['usemail'];
			$this->showpass[$j]			= $formField['showpass'];
			$this->fieldHeader[$j]		= $formField['header_' . $this->editLang];
			$this->fieldRemark[$j]		= $formField['remark_' . $this->editLang];
			$this->pagebreak[$j]		= $formField['pagebreak'];
			
			$this->fieldLinkArr			= explode("<>", $formField['link']);
			$this->fieldLink[$j]		= isset($this->fieldLinkArr[0]) ? $this->fieldLinkArr[0] : NULL;
			$this->fieldLinkField[$j]	= isset($this->fieldLinkArr[1]) ? $this->fieldLinkArr[1] : '';
			$this->fieldLinkValue[$j]	= $formField['linkval_' . $this->editLang];;
			
			$this->dbUpdateStr			= "";								
			
			
			
			// Falls das Formular zum Ändern der Formularfelder abgeschickt wurde
			if($this->editFields && $this->evalFieldPost) {
			
			
				$this->fieldType[$j]		= isset($GLOBALS['_POST']['fieldType'][$j]) ? $GLOBALS['_POST']['fieldType'][$j] : $this->fieldType[$j];
				$this->fieldName[$j]		= isset($GLOBALS['_POST']['fieldName'][$j]) ? $GLOBALS['_POST']['fieldName'][$j] : $this->fieldName[$j];
				$this->fieldRequired[$j]	= isset($GLOBALS['_POST']['fieldRequired'][$j]) ? 1 : 0;
				$this->fieldHidden[$j]		= isset($GLOBALS['_POST']['fieldHidden'][$j]) ? 1 : 0;
				$this->fieldLabel[$j]		= isset($GLOBALS['_POST']['fieldLabel'][$j]) ? $GLOBALS['_POST']['fieldLabel'][$j] : $this->fieldLabel[$j];
				$this->fieldValue[$j]		= isset($GLOBALS['_POST']['fieldValue'][$j]) ? $GLOBALS['_POST']['fieldValue'][$j] : $this->fieldValue[$j];
				$this->fieldMinLen[$j]		= isset($GLOBALS['_POST']['fieldMinLen'][$j]) ? $GLOBALS['_POST']['fieldMinLen'][$j] : $this->fieldMinLen[$j];
				$this->fieldMaxLen[$j]		= isset($GLOBALS['_POST']['fieldMaxLen'][$j]) ? $GLOBALS['_POST']['fieldMaxLen'][$j] : $this->fieldMaxLen[$j];
				$this->fieldOptions[$j]		= isset($GLOBALS['_POST']['fieldOptions'][$j]) ? $GLOBALS['_POST']['fieldOptions'][$j] : $this->fieldOptions[$j];
				$this->fieldNotice[$j]		= isset($GLOBALS['_POST']['fieldNotice'][$j]) ? $GLOBALS['_POST']['fieldNotice'][$j] : $this->fieldNotice[$j];
				$this->fileTypes[$j]		= isset($GLOBALS['_POST']['fileTypes'][$j]) ? $GLOBALS['_POST']['fileTypes'][$j] : $this->fileTypes[$j];
				$this->fileSize[$j]			= isset($GLOBALS['_POST']['fileSize'][$j]) ? $GLOBALS['_POST']['fileSize'][$j] : $this->fileSize[$j];
				$this->fileFolder[$j]		= isset($GLOBALS['_POST']['fileFolder'][$j]) ? $GLOBALS['_POST']['fileFolder'][$j] : $this->fileFolder[$j];
				$this->filePrefix[$j]		= isset($GLOBALS['_POST']['filePrefix'][$j]) ? $GLOBALS['_POST']['filePrefix'][$j] : $this->filePrefix[$j];
				$this->fileRename[$j]		= isset($GLOBALS['_POST']['fileRename'][$j]) ? 1 : 0;
				$this->fileReplace[$j]		= isset($GLOBALS['_POST']['fileReplace'][$j]) ? 1 : 0;
				$this->usemail[$j]			= isset($GLOBALS['_POST']['usemail'][$j]) ? 1 : 0;
				$this->showpass[$j]			= isset($GLOBALS['_POST']['showpass'][$j]) ? 1 : 0;
				$this->fieldHeader[$j]		= isset($GLOBALS['_POST']['fieldHeader'][$j]) ? $GLOBALS['_POST']['fieldHeader'][$j] : $this->fieldHeader[$j];
				$this->fieldRemark[$j]		= isset($GLOBALS['_POST']['fieldRemark'][$j]) ? $GLOBALS['_POST']['fieldRemark'][$j] : $this->fieldRemark[$j];
				$this->pagebreak[$j]		= isset($GLOBALS['_POST']['pagebreak'][$j]) ? 1 : 0;
				
				$this->fieldLink[$j]		= isset($GLOBALS['_POST']['fieldLink'][$j]) ? 1 : 0;
				$this->fieldLinkField[$j]	= isset($GLOBALS['_POST']['fieldLinkField'][$j]) ? $GLOBALS['_POST']['fieldLinkField'][$j] : $this->fieldLinkField[$j];
				$this->fieldLinkValue[$j]	= isset($GLOBALS['_POST']['fieldLinkValue'][$j]) ? $GLOBALS['_POST']['fieldLinkValue'][$j] : $this->fieldLinkValue[$j];
				$this->fieldLinkArr			= $this->fieldLink[$j]."<>".$this->fieldLinkField[$j]."<>".$this->fieldLinkValue[$j];
				
				
				// Check und Update-String
				// Feldtyp
				$this->dbUpdateStr .=  "`type` = '" . $this->DB->escapeString($this->fieldType[$j]) . "',";
				
				// Feldnamen überprüfen
				if(!preg_match("/^[a-zA-Z0-9_-]+$/", $this->fieldName[$j]))
					$this->wrongInput[$j]['fieldName'] = "{s_notice:wrongname}";
		
				elseif(strlen($this->fieldName[$j]) > 50)
					$this->wrongInput[$j]['fieldName'] = "{s_notice:longname}";
				
				elseif(in_array($this->fieldName[$j], $this->existFieldNames) || $this->fieldName[$j] == "id" || $this->fieldName[$j] == "username")
					$this->wrongInput[$j]['fieldName'] = "{s_notice:fieldexists}";
				
				else {
					$this->existFieldNames[] = $this->fieldName[$j];
					$this->dbUpdateStr .=  "`field_name` = '" . $this->DB->escapeString($this->fieldName[$j]) . "',";
				}
				
				// Required
				$this->dbUpdateStr .=  "`required` = '" . $this->DB->escapeString($this->fieldRequired[$j]) . "',";
		
				// Label überprüfen
				if(strlen($this->fieldLabel[$j]) > 300)
					$this->wrongInput[$j]['fieldLabel'] = "{s_notice:longname}";
				
				else				
					$this->dbUpdateStr .=  "`label_" . $this->editLang . "` = '" . $this->DB->escapeString($this->fieldLabel[$j]) . "',";
				
				// Value
				$this->dbUpdateStr .=  "`value_" . $this->editLang . "` = '" . $this->DB->escapeString($this->fieldValue[$j]) . "',";
				
				// Minlen
				if(strlen($this->fieldMinLen[$j]) > 4)
					$this->wrongInput[$j]['fieldMinLen'] = "{s_notice:longname}";
				elseif(!empty($this->fieldMinLen[$j]) && !is_numeric($this->fieldMinLen[$j]))
					$this->wrongInput[$j]['fieldMinLen'] = "{s_error:check}";
				elseif($this->fieldMinLen[$j] < PASSWORD_MIN_LENGTH && $this->fieldType[$j] == "password")
					$this->wrongInput[$j]['fieldMinLen'] = "{s_error:check}";
				else				
					$this->dbUpdateStr .=  "`min_length` = '" . $this->DB->escapeString($this->fieldMinLen[$j]) . "',";
				
				// Maxlen
				if(strlen($this->fieldMaxLen[$j]) > 4)
					$this->wrongInput[$j]['fieldMaxLen'] = "{s_notice:longname}";
				elseif(!empty($this->fieldMaxLen[$j]) && !is_numeric($this->fieldMaxLen[$j]))
					$this->wrongInput[$j]['fieldMaxLen'] = "{s_error:check}";
				elseif($this->fieldMaxLen[$j] > PASSWORD_MAX_LENGTH && $this->fieldType[$j] == "password")
					$this->wrongInput[$j]['fieldMaxLen'] = "{s_error:check}";
				else				
					$this->dbUpdateStr .=  "`max_length` = '" . $this->DB->escapeString($this->fieldMaxLen[$j]) . "',";
		
				// Options überprüfen
				if(strlen($this->fieldOptions[$j]) > 2048)
					$this->wrongInput[$j]['fieldOptions'] = "{s_notice:longname}";
				
				else				
					$this->dbUpdateStr .=  "`options_" . $this->editLang . "` = '" . $this->DB->escapeString($this->fieldOptions[$j]) . "',";
		
				// Notice überprüfen
				if(strlen($this->fieldNotice[$j]) > 300)
					$this->wrongInput[$j]['fieldNotice'] = "{s_notice:longname}";
				
				else				
					$this->dbUpdateStr .=  "`notice_" . $this->editLang . "` = '" . $this->DB->escapeString($this->fieldNotice[$j]) . "',";
		
				// Filetypes überprüfen
				if(strlen($this->fileTypes[$j]) > 300)
					$this->wrongInput[$j]['fileTypes'] = "{s_notice:longname}";
				
				else				
					$this->dbUpdateStr .=  "`filetypes` = '" . $this->DB->escapeString($this->fileTypes[$j]) . "',";
				
				// Filesize
				if(!empty($this->fileSize[$j]) && !is_numeric($this->fileSize[$j]))
					$this->wrongInput[$j]['fileSize'] = "{s_error:check}";
				elseif(!empty($this->fileSize[$j]) && $this->fileSize[$j] == 0)
					$this->wrongInput[$j]['fileSize'] = "{s_error:check}";
				else
					$this->dbUpdateStr .=  "`filesize` = '" . $this->DB->escapeString($this->fileSize[$j]) . "',";
		
				// Filefolder überprüfen
				if(strlen($this->fileFolder[$j]) > 64)
					$this->wrongInput[$j]['fileFolder'] = "{s_notice:longname}";
				elseif(!empty($this->fileFolder[$j]) && !preg_match("/^[a-zA-Z0-9-_]+$/", $this->fileFolder[$j]))
					$this->wrongInput[$j]['fileFolder'] = "{s_notice:wrongname}";
				
				else				
					$this->dbUpdateStr .=  "`filefolder` = '" . $this->DB->escapeString(trim($this->fileFolder[$j])) . "',";
		
				// Fileprefix überprüfen
				if(strlen($this->filePrefix[$j]) > 100)
					$this->wrongInput[$j]['filePrefix'] = "{s_notice:longname}";
				
				else				
					$this->dbUpdateStr .=  "`fileprefix` = '" . $this->DB->escapeString($this->filePrefix[$j]) . "',";
				
				// Filerename
				$this->dbUpdateStr .=  "`filerename` = '" . $this->DB->escapeString($this->fileRename[$j]) . "',";
				
				// Filereplace
				$this->dbUpdateStr .=  "`filereplace` = '" . $this->DB->escapeString($this->fileReplace[$j]) . "',";
				
				// Filereplace
				$this->dbUpdateStr .=  "`usemail` = '" . $this->DB->escapeString($this->usemail[$j]) . "',";
				
				// Filereplace
				$this->dbUpdateStr .=  "`showpass` = '" . $this->DB->escapeString($this->showpass[$j]) . "',";
		
				// Hidden
				$this->dbUpdateStr .=  "`hidden` = " . $this->DB->escapeString($this->fieldHidden[$j]) . ",";
				
				// Linkfeld überprüfen
				$link1	= $this->DB->escapeString($this->fieldLink[$j]);
				$link2	= "";
				$link3	= "";
				if(strlen($this->fieldLinkField[$j]) > 50)
					$this->wrongInput[$j]['fieldLinkField'] = "{s_notice:longname}";
				
				else				
					$link2 = $this->DB->escapeString($this->fieldLinkField[$j]);
		
				// Linkvalue überprüfen
				if(strlen($this->fieldLinkValue[$j]) > 256)
					$this->wrongInput[$j]['fieldLinkValue'] = "{s_notice:longname}";
				
				else				
					$link3 =  $this->DB->escapeString($this->fieldLinkValue[$j]);
				
				$this->dbUpdateStr .=  "`link` = '" . $link1 . "<>" . $link2 . "',";
				$this->dbUpdateStr .=  "`linkval_" . $this->editLang . "` = '" . $link3. "',";
		
				// Header überprüfen
				if(strlen($this->fieldHeader[$j]) > 300)
					$this->wrongInput[$j]['fieldHeader'] = "{s_notice:longname}";
				
				else				
					$this->dbUpdateStr .=  "`header_" . $this->editLang . "` = '" . $this->DB->escapeString($this->fieldHeader[$j]) . "',";

				// Remark überprüfen
				$this->dbUpdateStr .=  "`remark_" . $this->editLang . "` = '" . $this->DB->escapeString($this->fieldRemark[$j]) . "',";

				// Filereplace
				$this->dbUpdateStr .=  "`pagebreak` = '" . $this->DB->escapeString($this->pagebreak[$j]) . "',";



				// Falls keine Fehler db-Update
				if(count($this->wrongInput) == 0) {
					
				
					$this->dbUpdateStr = substr($this->dbUpdateStr, 0, -1);
					
					// Längenangaben für db-Felder ermitteln
					$this->dbFieldLen[$j] = (2 * $this->fieldMaxLen[$j]);
					
					// Falls Multiple oder Checkbox, Optionslänge*2 als Längenvorgabe
					if( $this->fieldType[$j] == "multiple" || 
						$this->fieldType[$j] == "checkbox"
						)
						$this->dbFieldLen[$j] = max(32, (mb_strlen($this->fieldOptions[$j], "UTF-8") * 3));
						
			
					// db-Tabelle sperren
					$lock = $this->DB->query("LOCK TABLES `$this->tableFormsDefs`");
		
						
					// Einfügen des neuen Formularfelds
					$this->updateSQL = $this->DB->query("UPDATE `$this->tableFormsDefs` 
															SET $this->dbUpdateStr 
															WHERE `id` = " . $this->fieldID[$j] . "
														");
		
		
					// Auf Vorhandensein in Tabelle form_xyz prüfen
					if(!empty($this->fieldNameOld[$j]))
						$checkSQL = $this->DB->query("SHOW COLUMNS 
													  FROM `" . DB_TABLE_PREFIX . "form_".$this->queryFormDetails[0]['table']."` 
													  LIKE '" . $this->DB->escapeString($this->fieldNameOld[$j]) . "' 
													 ");
					else
						$checkSQL = array();
					
					
					// Falls nicht vorhanden, Feld in form_xyz Tabelle einfügen						
					if(count($checkSQL) == 0)
						$alterSQL = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$this->queryFormDetails[0]['table']."` 
															ADD ".parent::getFieldDBStr($this->fieldType[$j], $this->fieldName[$j], $this->fieldRequired[$j], $this->dbFieldLen[$j]).(!empty($this->fieldNamePrev) ? " AFTER `" . $this->fieldNamePrev . "`" : "") . "
															");
					
					// Falls der Feldname geändert wurde
					if($this->fieldName[$j] != $this->fieldNameOld[$j] && $this->fieldNameOld[$j] != "")
						
						// Feld in form_xyz Tabelle ändern
						$alterSQL = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$this->queryFormDetails[0]['table']."` 
															CHANGE `".$this->fieldNameOld[$j]."` ".parent::getFieldDBStr($this->fieldType[$j], $this->fieldName[$j], $this->fieldRequired[$j], $this->dbFieldLen[$j])."
															");


					// Falls andere Parameter geändert wurden
					if($this->fieldRequired[$j] != $this->fieldRequiredOld[$j] || 
					   $this->fieldMaxLen[$j] != $this->fieldMaxLenOld[$j]
					)
						// Feld in form_xyz Tabelle ändern
						$alterSQL = $this->DB->query("ALTER TABLE `" . DB_TABLE_PREFIX . "form_".$this->queryFormDetails[0]['table']."` 
															CHANGE `".$this->fieldName[$j]."` ".parent::getFieldDBStr($this->fieldType[$j], $this->fieldName[$j], $this->fieldRequired[$j], $this->dbFieldLen[$j])."
															");
					
					#die(var_dump($this->updateSQL . $alterSQL));
		
					
					// db-Sperre aufheben
					$unLock = $this->DB->query("UNLOCK TABLES");
		
				}
				else
					$this->error = "{s_error:failchange}";
				
			} // Ende if submit edit
			
			
			// Namen des vorherigen Feldes speichern
			$this->fieldNamePrev	= $this->fieldName[$j];
			$i++;
		
		} // Ende foreach

	}

	
	// getFormData
	public function getFormData($formTab, $queryExt)
	{

		// db-Query nach Daten (Benutzereingaben) für das ausgewählte Formular
		$queryFormData = $this->DB->query( "SELECT * 
												FROM `" . DB_TABLE_PREFIX . "form_" . $formTab . "` 
												$queryExt
											");
		#var_dump($queryFormData);
		
		return $queryFormData;
	}
	
	
	// insertFormField
	public function insertFormField($newFieldType, $formID, $sortID)
	{

		$newFieldTypeDB	= $this->DB->escapeString($newFieldType);
		$formIDdb		= (int)$formID;
		$sortIDdb		= (int)$sortID;
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `$this->tableFormsDefs`");
		
		/*
		$maxSortId = $this->DB->query("SELECT MAX(`sort_id`) 
											FROM `$this->tableFormsDefs` 
											WHERE `table_id` = $formIDdb
											", false);
		
		$sortIDdb = $maxSortId[0]['MAX(`sort_id`)'];
		*/
		
		if(empty($sortIDdb))
			$sortIDdb = 1;
		else
			$sortIDdb++;
		
		
		
		// Felder verschieben
		$this->updateSQL = $this->DB->query("UPDATE `$this->tableFormsDefs` 
												SET `sort_id` = `sort_id`+1
											 WHERE `table_id` = $formIDdb 
												AND `sort_id` >= $sortIDdb
											", false);
		
		#var_dump($this->insertSQL);
		
		// Neuen Feldtyp einfügen
		$this->insertSQL = $this->DB->query("INSERT INTO `$this->tableFormsDefs` 
												(`table_id`, `sort_id`, `type`)
												VALUES ($formIDdb, $sortIDdb, '$newFieldTypeDB')
											", false);
		
		#var_dump($this->insertSQL);
		
		// Aktualisierung der SortID
		// Variable für Neusortierung setzen
		$updateSQL2a = $this->DB->query("SET @c:=0;
											");
		
		
		// Neusortierung
		$updateSQL2b = $this->DB->query("UPDATE `$this->tableFormsDefs` 
											SET `sort_id` = (SELECT @c:=@c+1)
										 WHERE `table_id` = $formIDdb 
											ORDER BY `sort_id` ASC;
										");
		
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		
		$this->setSessionVar('list_fields', $formID);
		$this->setSessionVar('notice', "{s_notice:newfield}");
		

		header("Location: " . ADMIN_HTTP_ROOT . "?task=forms&field=".$sortIDdb."#field-".$sortIDdb);
		exit;
	
	}
	
	

	/**
	 * getFormNotifications
	 * @access protected
	 */
	protected function getFormNotifications()
	{
		
		$output	= "";
		
		if(!empty($this->notice)) {
			$output .= $this->getNotificationStr($this->notice, "success");
			$this->successChange = true;
		}

		if(!empty($this->error))
			$output .= $this->getNotificationStr($this->error, "error");
		
		return $output;
		
	}	
	

	/**
	 * getFormActionBox
	 * @access protected
	 */
	protected function getFormActionBox()
	{

		// Checkbox zur Mehrfachauswahl zum Löschen und Publizieren
		$output =		'<div class="actionBox">' . PHP_EOL .
						'<form action="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&items=array&list_forms=' . $this->editForm . '&action=" method="post" data-getcontent="fullpage" data-history="false">' . PHP_EOL .
						'<label class="markAll markBox" data-mark="#selectableForms">' . PHP_EOL .
						'<input type="checkbox" id="markAllLB-form" data-select="all" /></label>' . PHP_EOL .
						'<label for="markAllLB-form" class="markAllLB"> {s_label:mark}</label>' . PHP_EOL .
						'<span class="editButtons-panel">' . PHP_EOL;
			
		// Button delete
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> "delAll delForms delSelectedListItems button-icon-only",
								"title"		=> "{s_title:delmarked}",
								"attr"		=> 'data-action="delmultiple"',
								"icon"		=> "delete"
							);
		
		$output		.=	parent::getButton($btnDefs);
		
		$output		.=	'</span>' . PHP_EOL .
						'</form>' . PHP_EOL .
						'</div>' . PHP_EOL;
		
		return $output;
	
	}
	

	// listExistingForms
	public function listExistingForms()
	{
	
		$output		= "";
		$hide		= (isset($GLOBALS['_POST']['new_form']) || isset($GLOBALS['_POST']['edit_form']) || isset($GLOBALS['_GET']['formname']) || isset($GLOBALS['_POST']['add_new']) ? ' hideNext' : '');
		$formsExist	= count($this->existForms) > 0;
		
		// Formulare auflisten (falls vorhanden)
		$output		 .=	'<h4 class="cc-h4 toggle' . $hide . '">{s_label:formexist}</h4>' . PHP_EOL;
	
		$output		 .=	'<div class="adminSubBox"' . ($hide ? ' style="display:none;"' : '') . '>' . PHP_EOL;
		

		// Actionbox
		if($formsExist) {
			
			$output		 .=	$this->getFormActionBox();
		
			$output		 .=	'<ul id="selectableForms" class="selectableForms selectableItems editList list list-condensed">' . PHP_EOL;
		
			// Formularliste
			$output	 	.=	$this->generateFormList();
			
			$output		 .=	'</ul>' . PHP_EOL;
		
		
			// Contextmenü-Script
			$output		 .=	$this->getContextMenuScript();

		}
		else
			$output	 .=	'<p class="notice error">{s_text:noforms}</p>' . PHP_EOL;
			

	
		// Add form button
		$output		 .=	'<span class="newFormButton-panel buttonPanel">' . PHP_EOL .
						$this->getAddFormButton("right") .
						'<br class="clearfloat" />' . PHP_EOL .
						'</span>' . PHP_EOL;

		
		// Dialog-Form zum Angeben des Formularnamens für die Formularkopie
		$output		 .=	'<div id="dialog-form-forms" class="copy" style="display:none;" title="{s_title:copyform}">' . PHP_EOL .
						'<div class="adminStyle adminArea">' . PHP_EOL .
						'<form action="' . $this->formAction . '" method="post" class="form" accept-charset="UTF-8">' . PHP_EOL . 
						'<label for="newname-forms">{s_label:tablename}</label>' . PHP_EOL .
						'<p class="notice validateTips"></p>' . PHP_EOL .
						'<input type="text" name="newname-forms" id="newname-forms" class="dialogName dialogInput text ui-widget-content ui-corner-all" value="" maxlength="64" />' . PHP_EOL .
						'<input type="hidden" name="oldname-forms" id="oldname-forms" class="dialogName copyID" value="" />' . PHP_EOL .
						'<input type="hidden" name="scriptpath-forms" id="scriptpath-forms" value="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&action=copyform"  />' . PHP_EOL .
						'<input type="hidden" name="phrases-forms" id="phrases-forms" value="' . ContentsEngine::replaceStaText("{s_error:name}<>{s_notice:longname}<>{s_notice:wrongname}<>{s_notice:formexist}") . '"  />' . PHP_EOL .
						'<input type="hidden" name="buttonLabels-forms" id="buttonLabels-forms" value="' . ContentsEngine::replaceStaText("{s_button:takechange}<>{s_button:cancel}") . '"  />' . PHP_EOL .
						'</form>' . PHP_EOL .
						'</div>' . PHP_EOL .
						'</div>' . PHP_EOL;

		
		$output		 .=	'</div>' . PHP_EOL;
		
		return $output;
	
	}
	
	

	// generateFormList
	public function generateFormList()
	{

		$output	= "";
		$j		= 0;
		
		foreach($this->existForms as $formData) {
			
			$j++;
			$delVal		= $formData['id'] . "<>" . $formData['table'];
			
			$markBox	= '<label class="markBox">' . PHP_EOL .
						  '<input type="checkbox" class="addVal" name="formIDs[]" value="' . htmlspecialchars($delVal) . '" />' . PHP_EOL .
						  '</label>' . PHP_EOL;				
			
			$output		 .=	'<li class="listItem" data-menu="context" data-target="contextmenu-' . $j . '">' . PHP_EOL .
							$markBox .
							'<span class="listNr" title="Id">[#' . $formData['id'] . ']</span><span class="listName" title="{s_label:formtitle}">' . $formData['title_' . $this->editLang] . ' </span><span class="formTable" title="{s_label:tablename}">(' . $formData['table'] . ')</span>' . PHP_EOL;
			
			$output		 .=	'<span class="editButtons-panel" data-id="contextmenu-' . $j . '">' . PHP_EOL;
			
			$output		 .=	'<form action="' . $this->formAction . '" method="post" data-getcontent="fullpage">' . PHP_EOL;
	
			// Button: Formularfelder anzeigen
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "list_fields",
									"class"		=> "listFields button-icon-only",
									"value"		=> $formData['id'],
									"text"		=> "",
									"title"		=> "{s_header:formfields}",
									"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "fields"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			$output		 .=	'<input type="hidden" name="list_fields" value="' . $formData['id'] . '" />' . PHP_EOL .
							'</form>' . PHP_EOL;
							
			$output		 .=	'<form action="' . $this->formAction . '" method="post" data-getcontent="fullpage">' . PHP_EOL;
			
			// Button: Formularauswertung anzeigen
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "list_formdata",
									"class"		=> "showFormResults button-icon-only",
									"value"		=> $formData['id'],
									"text"		=> "",
									"title"		=> "{s_header:formdata}",
									"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '" data-menutitle="{s_header:formdata}"',
									"icon"		=> "stats"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			$output		 .=	'<input type="hidden" name="list_formdata" value="' . $formData['id'] . '" />' . PHP_EOL .
							'</form>' . PHP_EOL;
	
			$output		 .=	'<form action="' . $this->formAction . '" method="post" accept-charset="UTF-8" data-getcontent="fullpage">' . PHP_EOL;
			
			// Button: Formular bearbeiten
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "go_edit_form",
									"class"		=> "goEditForm button-icon-only",
									"value"		=> $formData['id'],
									"text"		=> "",
									"title"		=> "{s_title:editform}",
									"attr"		=> 'data-ajaxform="true" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "edit"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			$output		 .=	'<input type="hidden" name="go_edit_form" value="' . $formData['id'] . '" />' . PHP_EOL .
							'<input type="hidden" name="edit_form" value="' . $formData['id'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formName" value="' . $formData['table'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formTitle" value="' . $formData['title_' . $this->editLang] . '" />' . PHP_EOL .
							'<input type="hidden" name="formForeignKey" value="' . $formData['foreign_key'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formPoll" value="' . $formData['poll'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formActive" value="' . $formData['active'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formEndDate" value="' . ($formData['end_date'] == "NULL" ? '' : implode(".", array_reverse(explode("-", substr($formData['end_date'], 0, 10))))) . '" />' . PHP_EOL .
							'<input type="hidden" name="formSuccess" value="' . $formData['notice_success_' . $this->editLang] . '" />' . PHP_EOL .
							'<input type="hidden" name="formError" value="' . $formData['notice_error_' . $this->editLang] . '" />' . PHP_EOL .
							'<input type="hidden" name="formFieldNotice" value="' . $formData['notice_field_' . $this->editLang] . '" />' . PHP_EOL .
							'<input type="hidden" name="formCaptcha" value="' . $formData['captcha'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formHttps" value="' . $formData['https'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formAddTable" value="' . $formData['add_table'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formAddFields" value="' . $formData['add_fields'] . '" />' . PHP_EOL .
							'<input type="hidden" name="formAddLabels" value="' . $formData['add_labels_' . $this->editLang] . '" />' . PHP_EOL .
							'<input type="hidden" name="formAddPosition" value="' . $formData['add_position'] . '" />' . PHP_EOL .
							'</form>' . PHP_EOL;
							
			// Button: Formular kopieren
			$btnDefs	= array(	"type"		=> "button",
									"id"		=> 'copyID-' . $formData['id'],
									"class"		=> "copyForm dialog dialog-forms button-icon-only",
									"title"		=> "{s_title:copyform}",
									"attr"		=> 'data-dialog="forms" data-dialogname="' . $formData['table'] . '_{s_common:copy}" data-dialogid="' . $formData['id'] . '" data-menuitem="true" data-id="item-id-' . $j . '"',
									"icon"		=> "copy"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			// Button: Formular löschen
			$btnDefs	= array(	"type"		=> "button",
									"id"		=> 'copyID-' . $formData['id'],
									"class"		=> "delcon delform button-icon-only",
									"title"		=> $formData['table'] . ' -> {s_title:delform}',
									"attr"		=> 'data-action="delete" data-url="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&action=delform&formid=' . $formData['id'] . '&tablename=' . $formData['table'] . '" data-title="' . $formData['table'] . '" data-menuitem="true" data-id="item-id-' . $j . '" data-menutitle="{s_title:delform}"',
									"icon"		=> "delete"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			$output		 .=	'</span>' . PHP_EOL;
				
			$output		 .=	'</li>' . PHP_EOL;			
		}
		
		return $output;
	
	}
	
	

	// getFormDetailsForm
	public function getFormDetailsForm()
	{
	
		$output	= "";
		
		if($this->editForm)
			$output		 .=	'<h4 class="cc-h4 toggle">{s_label:editform} #' . $this->formID . '</h4>' . PHP_EOL;
		else
			$output		 .=	'<h4 class="cc-h4 toggle">{s_label:newform}</h4>' . PHP_EOL;
			
		$output		 .=	'<div class="adminSubBox"' . ($this->successChange ? ' style="display:none;"' : '') . '>' . PHP_EOL;
		
		$output		 .=	'<form action="' . $this->formAction . '#cfm" method="post" name="adminfm1" accept-charset="UTF-8">' . PHP_EOL . 
						'<ul class="framedItems">' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<label>{s_label:tablename}</label>' . PHP_EOL;

		if(isset($this->wrongInput['formname']))
			$output		 .=	'<p class="notice">' . $this->wrongInput['formname'] . '</p>' . PHP_EOL;


		// Formularname (db)
		if(!empty($this->form) && 
		   !isset($this->g_Session['form_id'])
		)
			$formName = htmlspecialchars($this->form);
		elseif(isset($GLOBALS['_GET']['formname']))
			$formName = htmlspecialchars($GLOBALS['_GET']['formname']);
		else
			$formName = "";
		
		$output		 .=	'<input type="text" name="formName" value="' . $formName . '" maxlength="50"' . ($this->editForm ? ' readonly="readonly" class="readonly"' : '') . ' />' . PHP_EOL .
						'<label>{s_label:formtitle} <span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL;

		if(isset($errorTitle))
			$output		 .=	'<p class="notice">' . $errorTitle . '</p>' . PHP_EOL;


		$output		 .=	'<input type="text" name="formTitle" value="' . (!empty($this->formTitle) && !isset($this->g_Session['form_id']) ? htmlspecialchars($this->formTitle) : '') . '" maxlength="200" />' . PHP_EOL;
		
		$output		 .=	'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL;
		
		
		// Details
		$output		 .=	'<li>' . PHP_EOL .
						'<label class="markBox"><input type="checkbox" class="toggleDetails" name="toggleDetails" id="toggleDetails" data-toggle="formDetails"></label>' . PHP_EOL .
						'<label class="toggleDetails inline-label" for="toggleDetails">{s_title:toggledetails}</label>' . PHP_EOL .
						'<ul id="formDetails" class="detailBox" style="display:none;">' . PHP_EOL;


		// Option für Foreign-Key hinzufügen
		$output		 .=	'<li><label>Foreign key</label>' . PHP_EOL .
						'<select name="formForeignKey" class="formForeignKey">' . PHP_EOL .
						'<option value="">{s_option:non}</option>' . PHP_EOL .
						'<option value="username"' . ($this->formForeignKey == "username" ? ' selected="selected"' : '') . '>{s_label:username}</option>' . PHP_EOL .
						'</select><br class="clearfloat" />' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL .
						'</li>' . PHP_EOL;
					
		// Meldungen
		$output		 .=	'<li>' . PHP_EOL .
						'<ul class="rowlist">' . PHP_EOL .
						'<label>{s_label:formsuccess}<span class="toggleEditor" data-target="formSuccess">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<textarea name="formSuccess" rows="2" id="formSuccess" class="formMessage cc-editor-add disableEditor">' . htmlspecialchars($this->formSuccess) . '</textarea>' . PHP_EOL .
						'<label>{s_label:formerror}<span class="toggleEditor" data-target="formError">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<textarea name="formError" rows="2" id="formError" class="formMessage cc-editor-add disableEditor">' . htmlspecialchars($this->formError) . '</textarea>' . PHP_EOL .
						'<label>{s_label:formfieldnotice}<span class="toggleEditor" data-target="formFieldNotice">Editor</span><span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<textarea name="formFieldNotice" rows="2" id="formFieldNotice" class="formMessage cc-editor-add disableEditor">' . htmlspecialchars($this->formFieldNotice) . '</textarea>' . PHP_EOL .
						'</ul>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL .								
						'</li>' . PHP_EOL;

		// Option für Captcha hinzufügen
		$output		 .=	'<li>' . PHP_EOL .
						'<ul class="rowlist">' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<label>Captcha</label>' . PHP_EOL .
						'<select name="formCaptcha">' . PHP_EOL .
						'<option value="0">{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->formCaptcha == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'</select></li>' . PHP_EOL .
						'</ul>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL .
						'<br />' . PHP_EOL .								
						'</li>' . PHP_EOL;

		// Option für Https hinzufügen
		$output		 .=	'<li>' . PHP_EOL .
						'<ul class="rowlist">' . PHP_EOL .
						'<li><label>{s_label:formhttps}</label>' . PHP_EOL .
						'<select name="formHttps">' . PHP_EOL .
						'<option value="0">{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->formHttps == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'</select></li>' . PHP_EOL .
						'</ul>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL .
						'<br />' . PHP_EOL .								
						'</li>' . PHP_EOL;
					
		// Eingabefelder für zusätzliche Felder für Formmailer hinzufügen
		$output		 .=	'<li>' . PHP_EOL .
						'<ul class="rowlist">' . PHP_EOL .
						'<label>{s_label:formaddtable}</label>' . PHP_EOL .
						'<input type="text" name="formAddTable" value="' . htmlspecialchars($this->formAddTable) . '" />' . PHP_EOL .
						'<label>{s_label:formaddfields}</label>' . PHP_EOL .
						'<textarea name="formAddFields" rows="2" class="formMessage customList noTinyMCE">' . htmlspecialchars($this->formAddFields) . '</textarea>' . PHP_EOL .
						'<label>{s_label:formaddlabels} <span class="editLangFlag">' . $this->editLangFlag . '</span></label>' . PHP_EOL .
						'<textarea name="formAddLabels" rows="2" class="formMessage customList noTinyMCE">' . htmlspecialchars($this->formAddLabels) . '</textarea>' . PHP_EOL .
						'</ul>' . PHP_EOL .
						'</li>' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<label>{s_label:formaddposition}</label>' . PHP_EOL .
						'<input type="text" name="formAddPosition" value="' . htmlspecialchars($this->formAddPosition) . '" placeholder="top, bottom {s_common:or} {s_label:fieldname}" />' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL .
						'<br />' . PHP_EOL .								
						'</li>' . PHP_EOL;

		// Poll
		$output		 .=	'<li>' . PHP_EOL .
						'<ul class="rowlist">' . PHP_EOL .
						'<li>' . PHP_EOL .
						'<div class="left"><label>Poll</label>' . PHP_EOL .
						'<select name="formPoll" class="formPoll">' . PHP_EOL .
						'<option value="0">{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->formPoll == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'</select></div>' . PHP_EOL .
						'<div class="left" style="margin-left:25px;"><label>Aktiv</label>' . PHP_EOL .
						'<select name="formActive" class="formActive">' . PHP_EOL .
						'<option value="0">{s_option:non}</option>' . PHP_EOL .
						'<option value="1"' . ($this->formActive == 1 ? ' selected="selected"' : '') . '>{s_option:yesn}</option>' . PHP_EOL .
						'</select></div>' . PHP_EOL .
						'<div class="right" style="width:175px; margin-left:75px;"><label>Enddatum</label>' . PHP_EOL .
						'<input type="hidden" id="daynames" value="{s_date:daynames}" alt="{s_date:daynamesmin}" />' . PHP_EOL .
						'<input type="hidden" id="monthnames" value="{s_date:monthnames}" alt="{s_date:monthnamesmin}" />' . PHP_EOL .
						'<input type="text" name="formEndDate" class="formEndDate datepicker" style="width:135px; float:left;" value="' . (!empty($this->formEndDate) && $this->formEndDate != NULL ? $this->formEndDate : '') . '" maxlength="10" />' . PHP_EOL .
						'</div>' . PHP_EOL .
						'</li>' . PHP_EOL .
						'<br class="clearfloat" />' . PHP_EOL .
						'</ul>' . PHP_EOL .
						'</li>' . PHP_EOL .
						'<br class="clearfloat" /><br />' . PHP_EOL;
								
		// Ende Details
		$output		 .=	'</ul>' . PHP_EOL .
						'</li>' . PHP_EOL .
						'</ul>' . PHP_EOL;

		
		// Buttons, falls ein neues Formular angelegt werden soll
		if(!isset($GLOBALS['_POST']['edit_form'])) {
			
			$output		 .=	'<ul>'. PHP_EOL .
							'<li class="submit change">'. PHP_EOL;
			
			// Button ok
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "new_form",
									"class"		=> "change",
									"value"		=> "{s_button:newform}",
									"icon"		=> "ok"
								);
			
			$output		.=	parent::getButton($btnDefs);
			
			$output		.=	'<input type="hidden" name="new_form" value="{s_button:newform}" />' . PHP_EOL .
							'<br class="clearfloat" />' . PHP_EOL .
							'</li>' . PHP_EOL .
							'<input type="hidden" name="oldParentCat" value="" />' . PHP_EOL .
							'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL .
							'</ul>' . PHP_EOL .
							'</form>' . PHP_EOL;
		}
		
		// Buttons, falls ein Formular bearbeitet werden soll
		else {
			$output		 .=	'<ul>'. PHP_EOL .
							'<li class="submit change">' . PHP_EOL;
				
			// Button "Änderungen übernehmen"
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "submit_edit",
									"class"		=> "change",
									"value"		=> "{s_button:takechange}",
									"icon"		=> "ok"
								);
			
			$output		.=	parent::getButton($btnDefs);
							
			$output		.=	'<input type="hidden" name="submit_edit" value="{s_button:takechange}" />' . PHP_EOL . 
							'<input type="hidden" name="edit_form" value="' . $this->formID . '" />' . PHP_EOL .
							'<input type="hidden" name="token" class="token" value="' . parent::$token . '" />' . PHP_EOL . 
							'</li></ul>' . PHP_EOL .
							'</form>' . PHP_EOL .
							'<li class="submit back">' . PHP_EOL .
							$this->getAddFormButton("left") .
							$this->getEditFormFieldsButton($this->formID) .
							'<br class="clearfloat" />' . PHP_EOL .
							'</li></ul>' . PHP_EOL;
		}
		
		$output		 .=		'</div>' . PHP_EOL;
		
		return $output;
	
	}
	

	// getAddFormFieldSelect
	public function getAddFormFieldSelect($fc, $label = true)
	{

		// Feldtypauswahl für "neues Feld hinzufügen"
		$output		=	($label ? '<label class="left">{s_common:or} {s_label:newfield}' . "\n" : '') .
						parent::getIcon("new", "right inline-icon") .
						($label ? '</label>' . "\n" : '') .
						'<select name="insertFormField" class="insertFormField" data-autosubmit="true" data-url="' . SYSTEM_HTTP_ROOT . '/access/editForms.php?page=admin&action=newfield&formid=' . $this->formID . '&fieldpos=' . $fc . '&fieldtype=">' . PHP_EOL .
						'<option value="-">---</option>' . PHP_EOL;
		
		// Feldtypen auflisten
		foreach($this->fieldTypes as $type) {
			
			$output	.='<option value="' . $type . '" class="' . $type . ' fieldType" data-class="newFieldEntry fieldType ' . $type . '"data-fieldtype="' . $type . '">' . parent::$staText['formfields'][$type] . '</option>' . PHP_EOL;
		}
							
		$output		.= 	'</select>' . PHP_EOL;
		
		return $output;
	
	}
		


	// getAddFormButton
	public function getAddFormButton($align = "left")
	{
		
		$output		=	'<form action="' . $this->formAction . '" method="post" class="' . $align . '">' . PHP_EOL;
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "new_form",
								"class"		=> "add " . $align,
								"value"		=> "{s_button:newform}",
								"icon"		=> "new"
							);
		
		$output		.=	parent::getButton($btnDefs);
		
		$output		.=	'<input name="new_form" type="hidden" />' . PHP_EOL .
						'</form>' . PHP_EOL;

		return $output;
	
	}
	

	// getEditFormFieldsButton
	public function getEditFormFieldsButton($formID, $align = "right")
	{
	
		$output		=	'<form action="' . $this->formAction . '" method="post">' . PHP_EOL;

		// Button: Formularfelder anzeigen
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "list_fields",
								"class"		=> "listFields " . $align,
								"value"		=> $formID,
								"text"		=> "{s_header:formfields}",
								"icon"		=> "fields"
							);
		
		$output		.=	parent::getButton($btnDefs);
		
		$output		 .=	'<input type="hidden" name="list_fields" value="' . $formID . '" />' . PHP_EOL .
						'</form>' . PHP_EOL;

		return $output;
	
	}
	
	
	// getFormBackButtons
	public function getFormBackButtons($type)
	{
	
		$output				=	'<li class="submit back">' . PHP_EOL;
		
		$output			   .=	'<form action="' . $this->formAction . '" method="post">' . PHP_EOL;
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "new_form",
								"class"		=> "add right",
								"value"		=> "{s_button:newform}",
								"icon"		=> "new"
							);
		
		$output			   .=	parent::getButton($btnDefs);
		
		$output			   .=	'<input name="new_form" type="hidden" />' . PHP_EOL .
								'</form>' . PHP_EOL;
		
		
		// Button new
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> "back",
								"value"		=> "{s_button:formlist}",
								"icon"		=> "backtolist"
							);
		
				
		switch($type) {
		
			case "fields":
				$output		.=	'<form action="' . $this->formAction . '" method="post">' . PHP_EOL .
								parent::getButton($btnDefs) .
								'<input name="list_fields" type="hidden" value="" />' . PHP_EOL .
								'</form>' . PHP_EOL;
				break;
			
			case "data":
				$output		.=	'<form action="' . $this->formAction . '" method="post">' . PHP_EOL .
								parent::getButton($btnDefs) .
								'<input name="list_formdata" type="hidden" value="' . $this->formID . '" />' . PHP_EOL .
								'</form>' . PHP_EOL;
				break;
			
		}
		
		$output				.=	'</li>' . PHP_EOL;
	
		return $output;
	
	}

	
	// getFormRightBarContents
	private function getFormRightBarContents($formID)
	{
	
		// Panel for rightbar
		$output	= "";
		
		// New gallery
		$output .=	'<div class="controlBar">' . PHP_EOL;
		
		// Button add gallery
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=forms',
								"class"		=> "{t_class:btnpri} {t_class:btnblock}",
								"text"		=> "{s_header:form}",
								"icon"		=> "edit",
								"attr"		=> 'data-ajax="true"'
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		// Button add gallery
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=forms&list_fields=' . ($formID ? $formID : 'true'),
								"class"		=> "{t_class:btnpri} {t_class:btnblock} marginTop",
								"text"		=> "{s_header:formfields}",
								"icon"		=> "list",
								"attr"		=> 'data-ajax="true"'
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		// Button add gallery
		$btnDefs	= array(	"href"		=> ADMIN_HTTP_ROOT . '?task=forms&list_formdata=' . ($formID ? $formID : 'true'),
								"class"		=> "{t_class:btnpri} {t_class:btnblock} marginTop",
								"text"		=> "{s_header:formdata}",
								"icon"		=> "stats",
								"attr"		=> 'data-ajax="true"'
							);
	
		$output		.=	parent::getButtonLink($btnDefs);
		
		$output .=	'</div>' . PHP_EOL;
		
		return $output;
		
	}
	

	// getScriptTag
	public function getScriptTag($target)
	{
	
		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
				'head.load({tagEditorcss: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.css"});' . PHP_EOL .
				'head.load({tagEditorcaret: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.caret.min.js"});' . PHP_EOL .
				'head.load({tagEditor: "' . PROJECT_HTTP_ROOT . '/extLibs/jquery/tagEditor/jquery.tag-editor.min.js"});' . PHP_EOL .
				'head.ready("tagEditor", function(){' . PHP_EOL .
				'$("document").ready(function(){' . PHP_EOL .
				'$("#' . $target . '" ).tagEditor({' . PHP_EOL .
				'maxLength: 2048,' . PHP_EOL .
				'delimiter: "\n",' . PHP_EOL .
				'forceLowercase: false,' . PHP_EOL .
				'onChange: function(field, editor, tags){' . PHP_EOL .
					'editor.next(".deleteAllTags-panel").remove();' . PHP_EOL .
					'if(tags.length > 0 && !editor.next(".deleteAllTags-panel").length){ editor.after(\'<span class="deleteAllTags-panel buttonPanel"><button class="deleteAllTags cc-button button button-small button-icon-only btn right" type="button" role="button" title="{s_javascript:removeall}"><span class="cc-admin-icons icons cc-icon-cancel-circle">&nbsp;</span></button><br class="clearfloat" /></span>\'); }' . PHP_EOL .
					'editor.next(".deleteAllTags-panel").children(".deleteAllTags").click(function(){' . PHP_EOL .
						'for (i = 0; i < tags.length; i++) { field.tagEditor("removeTag", tags[i]); }' . PHP_EOL .
					'});' . PHP_EOL .
				'}' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

	
	// getSelectmenuScript
	protected function getSelectmenuScript()
	{

		return	'<script>' . PHP_EOL .
				'head.ready(function(){' . PHP_EOL .
				'head.ready("ui", function(){' . PHP_EOL .
				'head.load({formsselectmenu: "' . SYSTEM_HTTP_ROOT . '/inc/admintasks/forms/js/forms.js"})' . PHP_EOL .
				'head.ready("formsselectmenu", function(){' . PHP_EOL .
				'$(document).ready(function(){' . PHP_EOL .
				'$("select.insertFormField").iconselectmenu({' .
				'open: function(e){ e.preventDefault(); e.stopImmediatePropagation(); },' .
				'close: function(e){ e.preventDefault(); e.stopImmediatePropagation(); },' .
				'select: function( event, ui ){' .
				'var elem = $(this);' .
				'var ft = elem.children("option:selected").attr("data-fieldtype");' .
				'if(typeof(ft) == "undefined"){ ft = ""; }' .
				'else{' .
				'if(cc.conciseChanges){' .
					'jConfirm(ln.savefirst, ln.confirmtitle, function(result){' .
						'if(result === true){' .								
							'elem.val(ft).trigger("change");' .
						'}' .
						'return true;' .
					'});' .
				'}else{' .
					'elem.val(ft).trigger("change");' .
				'}' .
				'}} }).iconselectmenu( "menuWidget" ).addClass( "formFieldSelect" );' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

	
	// getFormdataToPDFScript
	protected function getFormdataToPDFScript()
	{
	
		return	'<script>head.ready(function(){' . PHP_EOL .
				'$(document).ready(function(){' . PHP_EOL .				
					'head.load(	{jspdf: "' . PROJECT_HTTP_ROOT . '/extLibs/jsPDF/jspdf.min.js"},
								//{jspdftable: "' . PROJECT_HTTP_ROOT . '/extLibs/jsPDF/jspdf.plugin.table.js"},
								{formdatatopdf: "' . SYSTEM_HTTP_ROOT . '/inc/admintasks/forms/js/formdata-topdf.js"}' . PHP_EOL .
					');' . PHP_EOL .
					'head.ready("jspdf", function(){' . PHP_EOL .
						'head.ready("formdatatopdf", function(){' . PHP_EOL .
							'cc.formdataToPDF();' . PHP_EOL .
						'});' . PHP_EOL .
					'});' . PHP_EOL .
				'});' . PHP_EOL .
				'});' . PHP_EOL .
				'</script>' . PHP_EOL;
	
	}

}
