<?php
namespace Concise;



/**
 * Script zum Erstellen eines Konfigurations-Arrays für Formulare
 * 
 * @param	array	$queryFormFields Array mit Formularfeldtabellen-Daten
 * @access	public
 * @return	array
 */
 
// Falls foreign_key auf user gesetzt ist, Benutzername als hidden field mitgeben
if($queryFormFields[0]['foreign_key'] == "username") {
	// Benutzernamen auslesen (da hier ForeignKey)
	if(isset($GLOBALS['_SESSION']['username']))
		$username = $GLOBALS['_SESSION']['username'];
	else
		$username = "";
		
	$configArray["username"] = array("type"		=> "hidden",
									 "value"	=> $username
							   );
}

// Generelle Formularoptionen
$configArray["cf_usernotice"] = array(	"success"	=> $queryFormFields[0]['notice_success_' . $lang],
										"error"		=> $queryFormFields[0]['notice_error_' . $lang],
										"errorfield"	=> $queryFormFields[0]['notice_field_' . $lang],
										"errorfill"	=> "{s_error:checkform}"
									 );

if(isset($queryFormFields[0]['add_table']) && $queryFormFields[0]['add_table'] != "")

	$configArray["cf_addfields"] = array(	"table"		=> $queryFormFields[0]['add_table'],
											"fields"	=> $queryFormFields[0]['add_fields'],
											"position"	=> $queryFormFields[0]['add_labels_' . $lang],
											"fields"	=> $queryFormFields[0]['add_position']
										 );

$configArray["cf_id"]			= $queryFormFields[0]["table_id"];
$configArray["cf_timestamp"]	= $queryFormFields[0]['timestamp'];
$configArray["cf_captcha"]		= $queryFormFields[0]['captcha'];
$configArray["cf_https"]		= $queryFormFields[0]['https'];
$configArray["cf_poll"]			= $queryFormFields[0]['poll'];

 
$k = 0;

// Einzelne Feldoptionen
foreach($queryFormFields as $formField) {

	$type	= $formField['type'];
	$field	= $formField['field_name'];
	
	$configArray[$field] = array();
	$configArray[$field]["type"]		= $type;
	$configArray[$field]["required"]	= $formField['required'];
	$configArray[$field]["hidden"]		= $formField['hidden'];
	$configArray[$field]["label"]		= $formField['label_' . $lang];
	$configArray[$field]["value"]		= $formField['value_' . $lang];
	$configArray[$field]["notice"]		= $formField['notice_' . $lang];
	$configArray[$field]["header"]		= $formField['header_' . $lang];
	$configArray[$field]["remark"]		= $formField['remark_' . $lang];
	$configArray[$field]["pagebreak"]	= $formField['pagebreak'];
	
	// Link
	$configArray[$field]["link"] = explode("<>", $formField['link']);
	
	if($configArray[$field]["link"][0])
		$configArray[$field]["link"] = array($configArray[$field]["link"][1], $formField['linkval_' . $lang]);
	else
		unset($configArray[$field]["link"]);
		
	// Falls Eingabefelder, Längenvorgaben einbinden
	if( $type == "default" || 
		$type == "text" || 
		$type == "password" || 
		$type == "int"
	) {
		
		$configArray[$field]["minlen"] = $formField['min_length'];
		$configArray[$field]["maxlen"] = $formField['max_length'];
	}
	
	// Falls Auswahlfelder
	if( $type == "select" || 
		$type == "multiple" || 
		$type == "checkbox" || 
		$type == "radio"
	) {
		
		// Options
		$options	= array_filter(explode("\n", preg_replace("/\r/", "", $formField['options_' . $lang])));
		$configArray[$field]["options"] = $options;
		
		// Falls mehrfache Vorauswahl (value) möglich, aus Valueelementen ein Array machen
		if( $type == "multiple" || 
			$type == "checkbox"
		) {
			$configArray[$field]["value"]	= explode("<>", $formField['value_' . $lang]);
		}
			
	}
	
	// Falls File
	if($type == "file") {
		
		$configArray[$field]["filetypes"]	= explode(",", $formField['filetypes']);
		$configArray[$field]["filesize"]	= ($formField['filesize'] * 1024 * 1024);
		$configArray[$field]["filefolder"]	= $formField['filefolder'];
		$configArray[$field]["fileprefix"]	= $formField['fileprefix'];
		$configArray[$field]["filerename"]	= $formField['filerename'];
		$configArray[$field]["filereplace"]	= $formField['filereplace'];
	}
	
	
	// Falls File
	if($type == "email" && $formField['usemail'])
		$configArray[$field]["usemail"] = true;
		
	// Falls Password
	if($type == "password" && $formField['showpass'])
		$configArray[$field]["showpass"] = true;
	
	// Falls Datum
	if($type == "date" && !$formField['hidden']) {
		if(method_exists($this, 'setDatePicker'))
			$this->setDatePicker($this->themeConf);
		else {
			$this->scriptFiles[]	= "extLibs/jquery/ui/jquery-ui-" . JQUERY_UI_VERSION . ".custom-datepicker.min.js";
			$this->scriptFiles[]	= JS_DIR . "datepicker.js";
			$this->cssFiles[]		= "extLibs/jquery/ui/" . JQUERY_UI_THEME . "/jquery-ui-" . JQUERY_UI_VERSION . ".custom.min.css";
		}
	}
	
	$k++;
	
} // Ende foreach
