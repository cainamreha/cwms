<?php
namespace Concise;

use Symfony\Component\EventDispatcher\Event;
use Concise\Events\CreateFormFieldEvent;
use Concise\Events\MakePDFEvent;


// Event-Klassen einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Forms/events/event.CreateFormField.php";
require_once PROJECT_DOC_ROOT."/inc/classes/Forms/events/event.MakePDF.php";

// Klasse Check einbinden
require_once PROJECT_DOC_ROOT."/inc/classes/Forms/class.Check.php";


/**
 * Klasse FormGenerator
 *
 * Erstellt automatisch Formulare aus einer Tabelle der Datenbank
 * 
 * 
 */


class FormGenerator extends ContentsEngine
{

	//Strukturdaten
	private $structureData;
	//Configarray
	private $configArray	= array ();
	//Tabellenname
	private $tablename		= "";
	//Tabellentitel
	public $formTitle		= "";
	//FormMailer Boolean
	public $FormMailer		= false;
	//FormMailer pdf-Dateiname
	public $pdfName			= "";
	//FormMailer pdf Attachment
	public $pdfMailAttach	= "";
	//E-Mailadresse des Empfängers
	public $recipients		= array("user");
	//E-Mailadresse(n) Kopie-Empfänger
	public $recipientsCC	= array();
	//E-Mailadresse(n) Blindkopie-Empfänger
	public $recipientsBCC	= array();
	//E-Mail-Betreff
	public $mailSubject		= "";
	//PDF erstellen
	public $makePDF			= false;
	//PDF Ordner
	public $pdfFolder		= "";
	//PDF an Browser senden
	public $browserPDF		= false;
	//PDF per E-Mail senden
	public $mailPDF			= false;
	//PDF in Ordner _user speichern
	public $userPDF			= false;
	//Formulardaten nicht in DB speichern
	public $noDbStorage		= false;
	//Fehlerarray
	private $errorArray		= array ();
	//Fehlerarray für Formularseiten/Schritte
	private $errorArraySteps	= array ();
	//Array mit Feldern und Werten der Eingaben (Feldbezeichnung aus Konfigurationarray)
	public $formInputArray		= array();
	//Array mit Feldern und Werten der Eingaben (Feldbezeichnung aus DB)
	public $formInputArrayDB	= array();
	//String mit einer tabellarischen Zusammenstellung von Feldern und Werten der Formulareingaben
	public $formData;
	//Konfigurationsparameter für Fremdschlüssel
	private $FKArray		= array ();
	//Maximale Länge von Varchar und Int
	private $maxLength		= false;
	//Seite/Schritt des Formulars Int
	private $step			= 1;
	//Nächste Seite/Schritt des Formulars nach Post Int
	private $stepPost		= 0;
	//Vorherige Seite/Schritt des Formulars nach Post Int
	private $prevStep		= 0;
	//Seiten/Schritte des Formulars hochzählen (bei pagebreak) Int
	private $stepCount		= 1;
	//Anzahl an Seiten/Schritten Int
	private $totalSteps		= 1;
	//Zähler für Anzahl an Eingabefeldern Int
	private $fieldCount		= 0;
	//Feldertyp
	private $fieldType		= "";
	//Feldwert
	private $fieldVal		= "";
	//ausgewählter Feldwert
	private $fieldSel		= "";
	//max. Feldlänge (Zeichenzahl)
	private $fieldMaxLen	= "";
	//Feld title
	private $fieldTitle		= "";
	//Feld class
	private $fieldClass		= "";
	//class fill
	private $classFill		= "";
	//Name des vorherigen Eingabefeldes
	private $prevField		= "";
	//Wert des vorherigen Eingabefeldes
	private $prevValue		= "";
	//Feld verstecken
	private $hide			= true;
	//Date ext
	private $dateExt		= "";
	//Required field
	private $required		= true;
	//Feld ist versteckt
	private $hideField		= false;
	//Feldinhalte aus anderer Tabelle hinzufügen (bei FormMailer)
	private $addFields		= false;
	//Captcha Einbindung Boolean
	private $useCaptcha 	= false;
	//Https-Protokoll verwenden Boolean
	private $useHttps		= false;
	//Captcha überprüfung Boolean
	private $checkCaptcha 	= true;
	//Überprüfung auf File-Felder Boolean
	private $hasFiles		= false;
	//Formulardatenübermittlung
	private $formSubmitted	= false;
	//Erfolgreiche Formulardatenübermittlung
	private $formSuccess	= true;
	//Fehler bei Formulardatenübermittlung
	private $formError		= false;
	//Fehler bei Formular-E-Mailübertragung
	private $mailError		= false;
	//Fehler bei Formular-Pdf-Estellung
	private $pdfError		= false;

	/**
	 * Konstruktor der Klasse
	 * 
	 * @param array		configArray		Array mit Parametern für definierte Felder/Werte.
	 * @param varchar	tablename 		Name der Tabelle für das Formular
	 *
	 * additional attributes
	 * @param varchar	formTitle 		Titel der Tabelle für das Formular (default = '')
	 * @param boolean	formMailer 		Formularversand per E-Mail (default = false)
	 * @param array		recipients		Quellenangabe für die Benutzer E-Mail
	 *									(e.g. user->aus db-Tabelle user oder form->aus Formfeld zu spezifizieren in config; default = array("user"))
	 * @param array		recipientsCC	E-Mail eines Cc-Empfängers für die E-Mail (default = '')
	 * @param array		recipientsBCC	E-Mail eines Bcc-Empfängers für die E-Mail (default = '')
	 * @param varchar	mailSubject 	Betreff für den E-Mail-Versand (default = '')
	 * @param boolean	makePDF			Falls true, wird eine pdf-Datei aus den Formulardaten generiert (default = false)
	 * @param varchar	pdfFolder		Ordner für pdf-Dateien (default = '')
	 * @param boolean	browserPDF		Falls true, wird die pdf-Datei aus den Formulardaten an den Browser geschickt (default = false)
	 * @param boolean	mailPDF			Falls true, wird die pdf-Datei aus den Formulardaten an die E-Mail angehängt (default = false)
	 * @param boolean	userPDF			Falls true, wird die pdf-Datei aus den Formulardaten im Ordner _user gespeichert (default = false)
	 * @param boolean	noDbStorage		Falls true, werden die Formulardaten nicht in der DB gespeichert (default = false)
	 */
	public function __construct($configArray, $tablename)
	{

		$this->configArray		= $configArray; //Array mit definierten Charakteristika		
		$this->tablename		= $tablename; //Name der Tabelle speichern
		
		// Security-Objekt
		$this->o_security		= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session		= Security::getSessionVars();
	
	}

	/**
	 * Formular-Element erstellen
	 *
	 * access	public
	 *
	 */
	public function generateForm()
	{
	
		// Die Tabellenstruktur auslesen:
		$sql = "DESCRIBE `".$this->DB->escapeString(DB_TABLE_PREFIX . $this->tablename) . "`";
		
		// ...und speichern
		$this->structureData = $this->DB->query($sql);
		
		// Falls ein Captcha eingebunden werden soll
		if(isset($this->configArray['cf_captcha']) && $this->configArray['cf_captcha'] == true)
			$this->useCaptcha = true;
		
		// Falls das Https-Protokoll verwendet werden soll
		if(isset($this->configArray['cf_https']) && $this->configArray['cf_https'] == true)
			$this->useHttps = true;
			
		// Wenn Formular abgeschickt wurde, sollen auch die Attribute überprüft werden:
		if (isset($GLOBALS['_POST']['send_'.$this->tablename]) || isset($GLOBALS['_POST']['send_'.$this->tablename.'_back']))
		{
		
			$this->checkAttributes();
			
			// Formularseite/Schritt auslesen
			if(isset($GLOBALS['_POST']['step_'.$this->tablename])) {
				$this->stepPost = $GLOBALS['_POST']['step_'.$this->tablename];
				$this->step = $this->stepPost;
				$this->prevStep = $this->step-1;
			}
			
			// Falls einen Schritt zurückgegangen werden soll
			if(isset($GLOBALS['_POST']['send_'.$this->tablename.'_back'])) {
			 	$this->step -= 2;
				$this->stepPost = $this->step;
				$this->prevStep = $this->step-1;
			}
			
			// Falls ein Captcha überprüft werden soll
			if($this->useCaptcha && (!isset($GLOBALS['_POST']['captcha_confirm']) || !isset($this->g_Session['captcha']) || $GLOBALS['_POST']['captcha_confirm'] != $this->g_Session['captcha']) && isset($GLOBALS['_POST']['step_'.$this->tablename]) && $this->step == $this->totalSteps+1) { 					
				$this->checkCaptcha = false;
				$this->errorArray["captcha"] = 1;
				$this->errorArraySteps[$this->totalSteps] = 1;
			}
		
			// Wenn alle Eingaben in Ordnung sind und der finalSubmit-Button geklickt wurde, wird gespeichert
			if (count($this->errorArray) == 0 && 
				isset($GLOBALS['_POST']['send_'.$this->tablename]) && 
				($this->step == $this->totalSteps + 1 || $this->totalSteps == 1) && 
				(!$this->useCaptcha || $this->checkCaptcha)
			){

				// Falls keine Daten gespeichert werden sollen oder die Formulardaten erfolgreich in der DB gespeichert wurden
				if($this->buildDataArrays($this->noDbStorage) === true) {
				
					// Falls der FormMailer oder PDF-Maker zum Einsatz kommen soll, Daten für E-Mail/PDF aufbereiten
					if($this->formMailer || $this->makePDF) {
						
						// Falls für die Benachrichtigung Daten aus einer anderen Tabelle hinzugefügt werden sollen
						if(array_key_exists("cf_addfields", $this->configArray))
							$this->addFields = $this->configArray['cf_addfields'];
						
						// Tabelle mit Formulardaten generieren
						$this->formData = $this->getTabularFormData();
						
						// Falls eine pdf-Datei generiert werden soll
						if($this->makePDF) {
							
							$pdfReport = $this->getPDF(); // PDF-Maker ausführen
						}
						
						// Falls FormMailer
						if($this->formMailer) {
							// Doppeleinträge entfernen
							array_unique($this->recipients);
							array_unique($this->recipientsCC);
							array_unique($this->recipientsBCC);
							
							$mailReport = $this->runFormMailer(); // FormMailer ausführen
						}
					}
					
					$report = "1";
				}
				else
					$report = "0";				
				
				
				// Falls eine Poll-Stimme abgegeben wurde, Coockie setzen
				if(isset($this->configArray['cf_poll']) && $this->configArray['cf_poll'] == true) {
					setcookie("poll-" . $this->tablename, "locked", (time(date("Y-m-d", time())) + 60*60*24), "/");
					$this->setSessionVar('notice', "{s_notice:pollthanks}");
				
					$urlExt = 'poll=' . $report;
				}
				else				
					$urlExt = 'form=' . $report . (isset($mailReport) ? '&mail=' . $mailReport : '') . (isset($pdfReport) ? '&pdf=' . $pdfReport : '');
				
		
				// Formular-Aktion
				$formActionArr	= explode("&edit_eid=", $this->getFormActionStr());
				$formAction		= reset($formActionArr);
				
				$formAction .= (strpos($formAction, "?") !== false ? "&" : "?");
				$formAction .= $urlExt;
				
				// Falls kein PDF an den Browser gesendet wurde, Seite neu laden
				if(!$this->browserPDF) {
					header("location: " . $formAction);
				}
				exit;

			}

		}
	
	}

	/**
	 * Formular ausgeben
	 *
	 * @param array		configArray		Formular-Konfigurationsdaten
	 * @param varchar	foreignKey		Gibt an ob ein fester Wert für den bestehenden ForeignKey (z.B. Benutzername) verwendet werden soll.
	 *									Falls 'select', wird eine Auswahlliste mit den ForeignKey-Werten erstellt (default = 'fix')
	 */
	public function printForm($configArray, $foreignKey = "fix")
	{
	
		// Formular-Konfigurationsdaten
		$this->configArray	= $configArray;
		
		// Formular erstellen
		$this->generateForm();
		
		$output		= "";
		$loopOutput = "";
		$formType	= "";
		
		//Falls im voherigen Schritt Fehler waren, den Zähler 1x dekrementieren
		if(isset($this->errorArraySteps[$this->prevStep]) && !empty($GLOBALS['_POST'])) {
			$this->step--;
		}
			
		//Falls keine Tabelle gefunden wurde, ist structureData ein String mit der Fehlermeldung. In diesem Fall false zurückgeben
		if(is_string($this->structureData))
			return false;

		//Für jede Zeile des Ergebnisses eine Zeile zusammenbauen:
		foreach ($this->structureData as $field)
		{

			#var_dump($field['Key']);
			//Entscheiden, ob PK angezeigt werden darf
			if ($field['Key'] == "PRI")
			{
				//Wird nicht behandelt.

			}
			//Prüfen, ob Fremdschlüssel  (oder Index)
			else {
				
				//Falls kein bestimmter Foreign-Key-Wert mitgegeben wurde (z.B. Benutzername)
				if ($field['Key'] == "MUL" && $foreignKey != "fix")
				{
					//Fremdschlüssel-Auswahlliste erstellen
					$loopOutput .=	$this->buildForeignKeyInputRow($field);
				}
				else
				{
					//Normale Eingabefeld ausgeben
					$loopOutput .=	$this->buildInputRow($field);

				}
			}

		}
		
		// Formular-Aktion
		$formAction = $this->getFormActionStr();
		
		// Formular-ID
		$formID		= str_replace("_", "-", $this->tablename);

		
		//alle Eingabefelder sind fertig "gebaut"
		//Falls ein File-Feld vorhanden, enctype hinzufügen
		if($this->hasFiles)
			$formType = ' enctype="multipart/form-data"';
			
		//Das Formular ausgeben:
		$output .=	'<div class="cc-autoForm {t_class:autoform}">' . "\r\n" .
					'<div class="top"></div>' . "\r\n" .
					'<div class="center">' . "\r\n" .
					'<form id="' . $formID . '" class="autoForm {t_class:form}' . ($this->stepCount > 1 ? ' cc-form-multistep' : '') . '" action="' . $formAction . '#' . $formID . '" method="post"' . $formType . ' accept-charset="UTF-8" data-ajax="false">' . "\r\n" .
					'<fieldset>' . "\r\n" . 
					'<legend>' . $this->formTitle . '</legend>' . "\r\n";


		$formFoot =	'</fieldset>' . "\r\n" . 
					'</form>' . "\r\n" .
					'</div>' . "\r\n" .
					'<div class="bottom"></div>' . "\r\n" .
					'</div>' . "\r\n";

		// Falls das Formular erfolgreich ausgefüllt worden war
		if(isset($GLOBALS['_GET']['form'])) {
			
			$this->formSubmitted	= true;
				
			// Falls eine Spreicherung der Daten in der DB erfolgt ist
			if($GLOBALS['_GET']['form'] == "1") {
				$output .=	'<div class="notice success {t_class:fmsuccess} {t_class:alert} {t_class:success}">'.(!empty($this->configArray["cf_usernotice"]["success"]) ? $this->configArray["cf_usernotice"]["success"] : '{s_notice:formsuccess}').'</div>' . "\r\n";
			}
				
			// Falls ein Fehler aufgetreten ist
			else {
				$this->formSuccess	= false;
				$this->formError	= true;
				$output .=	'<div class="error {t_class:fmwarning} {t_class:alert} {t_class:error}">'.(!empty($this->configArray["cf_usernotice"]["error"]) ? $this->configArray["cf_usernotice"]["error"] : '{s_error:formerror}').'</div>' . "\r\n";
			}
	
			// Falls der Mailversand mittels FormMailer erfolgreich war
			if(isset($GLOBALS['_GET']['mail'])) {
				
				// Falls eine Spreicherung der Daten in der DB erfolgt ist
				if($GLOBALS['_GET']['mail'] == "1") {
					$output .=	'<div class="notice success {t_class:fmsuccess} {t_class:alert} {t_class:success}">'.(isset($this->configArray["cf_usernotice"]["mailsuccess"]) ? $this->configArray["cf_usernotice"]["mailsuccess"] : '{s_notice:mailsuccess}').'</div>' . "\r\n";
				}
				
				// Falls ein Fehler aufgetreten ist
				else {
					$this->formSuccess	= false;				
					$this->mailError	= true;
					$output .=	'<div class="error {t_class:fmwarning} {t_class:alert} {t_class:error}">'.(isset($this->configArray["cf_usernotice"]["mailerror"]) ? $this->configArray["cf_usernotice"]["mailerror"] : '{s_error:mailerror}').'</div>' . "\r\n";
				}
			}
	
			// Falls eine pdf-Datei erfolgreich erstellt worden war
			if(isset($GLOBALS['_GET']['pdf'])) {
				
				// Falls eine Spreicherung der Daten in der DB erfolgt ist
				if($GLOBALS['_GET']['pdf'] == "1") {
					$output .=	'<div class="notice success {t_class:alert} {t_class:success}">'.(isset($this->configArray["cf_usernotice"]["pdfsuccess"]) ? $this->configArray["cf_usernotice"]["pdfsuccess"] : '{s_notice:pdfsuccess}').'</div>' . "\r\n";
				}
					
				// Falls ein Fehler aufgetreten ist
				else {
					$this->formSuccess	= false;				
					$this->pdfError		= true;
					$output .=	'<div class="error {t_class:fmwarning} {t_class:alert} {t_class:error}">'.(isset($this->configArray["cf_usernotice"]["pdferror"]) ? $this->configArray["cf_usernotice"]["pdferror"] : '{s_error:pdferror}').'</div>' . "\r\n";
				}
			}
			
			$output .=	$formFoot . "\r\n";
			
			return $output;
		}
			
		//Falls ein mehrseitiges Formular generiert werden soll, Flowchart einfügen
		if($this->stepCount > 1) {
		
			$classCue		= ' {t_class:alert} {t_class:info} {t_class:textinfo}';	
			$classActive	= ' active {t_class:alert} {t_class:warning} {t_class:textwarning}';	
			$classDone		= ' done {t_class:alert} {t_class:success} {t_class:textsuccess}';	
			$btnClass		= '{t_class:btn} {t_class:btndef} {t_class:btnsm}';	
			
			$output .=	'<div class="formFlow {t_class:well}">' . "\r\n";
			$output .=	'<ol class="formFlowList {t_class:pagination}">' . "\r\n";
	
			for($i = 1; $i <= $this->stepCount; $i++) {
				
				$output .=	'<li class="formFlowStep ' . ($this->step > $i ? $classDone : $classCue).($this->step == $i ? $classActive : "").'"><button class="' . $btnClass . '" value="' . $i . '" data-formstep="' . $this->tablename . '">{s_text:step} ' . $i . '</button></li>' . "\r\n";
				
				if($i < $this->stepCount)
					$output .=	'<li class="separator' . ($this->stepCount < $i ? $classDone . '"><span>&#x2714;' : $classCue .'"><span>&raquo;') . '</span></li>' . "\r\n";
			}
			$output .=	'</ol>' . "\r\n";
			$output .=	'</div>' . "\r\n";
		}
		
		//Falls das Formular nicht vollständig ausgefüllt wurde
		if(	(isset($this->errorArraySteps[$this->step]) && 
			(isset($GLOBALS['_POST']['send_'.$this->tablename]) && 
			($this->stepPost != $this->step))))
		
			$output .=	'<p class="error {t_class:fmwarning} {t_class:alert} {t_class:warning}">'.(!empty($this->configArray["cf_usernotice"]["fillerror"]) ? $this->configArray["cf_usernotice"]["fillerror"] : '{s_error:checkform}').'</p>' . "\r\n";
		
		
		//Fußnote über erforderliche Felder einfügen, falls nicht Poll
		if(!isset($this->configArray['cf_poll']) || $this->configArray['cf_poll'] == false)
			$output .=	'<p class="footnote topNote">{s_form:req}</p>' . "\r\n";
		
		
		//Formulardaten
		$output .=	$loopOutput; //Felder aus Schleife einfügen
		
		//Captcha generieren, falls gesetzt
		if($this->useCaptcha)
			$output .=	$this->getCaptcha($this->checkCaptcha);
		
		$output .=	'</ul>' . "\r\n";
		
		//Buttons generieren
		$output .=	$this->buildButtons();
		
		//Formularfuß
		$output .=	$formFoot;

		return $output;
	}

	/**
	 * Erfragt den gesetzten Wert des Attributs
	 * 
	 * @param varchar Name des Attributs
	 * 
	 * @return varchar Gibt den Wert des Attributs aus dem POST-Array zurück (oder Leerstring)
	 */
	private function getValue($attributename)
	{
		//Überprüfen, ob der Wert im Post-Array gesetzt ist.
		if (isset ($GLOBALS['_POST'][$this->tablename."_".$attributename]))
		{
			if(is_string($GLOBALS['_POST'][$this->tablename."_".$attributename]))
				return trim($GLOBALS['_POST'][$this->tablename."_".$attributename]);
			else
				return $GLOBALS['_POST'][$this->tablename."_".$attributename];

		}
		//Andernfalls überprüfen, ob der Wert im Files-Array gesetzt ist.
		elseif (isset ($GLOBALS['_FILES'][$this->tablename."_".$attributename]))
		{
			//Files-Klasse einbinden
			require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";
			//Files-Array für Datei zurückgeben
			return $GLOBALS['_FILES'][$this->tablename."_".$attributename];
		}
		else
		{
			//Leeren Wert zurückgeben
			return "";
		}

	}

	/**
	 * Hier wird ein Eingabefeld dargestellt.
	 * 
	 * @param Array Attributdefinitionen in einer Arraystruktur
	 */
	private function buildInputRow($field)
	{

		$this->field		= $field;
		$this->fieldType	= "default";
		$this->fieldVal		= "";
		$this->fieldSel		= "";
		$this->fieldTitle	= "";
		$this->fieldClass	= "";
		$this->classFill 	= "";
		$this->hide			= true;
		$this->dateExt		= "";
		$this->required		= true;
		$this->hideField	= false;
		$output				= "";
		
		$this->fieldCount++; //Feld zählen
		
		//Vordefinierte Werte auslesen
		if(array_key_exists($field['Field'], $this->configArray)) {
				
			$this->fieldType	= isset($this->configArray[$field['Field']]["type"]) ? $this->configArray[$field['Field']]["type"] : 'default';
			$this->required		= isset($this->configArray[$field['Field']]["required"]) && $this->configArray[$field['Field']]["required"] ? $this->configArray[$field['Field']]["required"] : false;
			$this->hideField	= isset($this->configArray[$field['Field']]["hidden"]) && $this->configArray[$field['Field']]["hidden"] ? $this->configArray[$field['Field']]["hidden"] : false;
			$this->fieldVal		= isset($this->configArray[$field['Field']]["value"]) && !isset($GLOBALS['_POST']['form_submission_'.$this->tablename]) ? $this->configArray[$field['Field']]["value"] : $this->getValue($field['Field']); //Wert des Attributs holen
			$label				= isset($this->configArray[$field['Field']]["label"]) && $this->configArray[$field['Field']]["label"] != "" ? $this->configArray[$field['Field']]["label"] : $field['Field'];
			$notice				= isset($this->configArray[$field['Field']]["notice"]) && $this->configArray[$field['Field']]["notice"] != "" ? '<span class="notice cc-field-notice">' . $this->configArray[$field['Field']]["notice"] . '</span>' : (isset($this->configArray["cf_usernotice"]["errorfield"]) ? '<span class="notice cc-field-notice">' . $this->configArray["cf_usernotice"]["errorfield"] . '</span>' : '');
		
		}
		else { //Andernfalls default-Werte nehmen
			$this->fieldVal		= $this->getValue($field['Field']); //Wert des Attributs holen
			$label				= $field['Field'];
			$notice				= isset($this->configArray["cf_usernotice"]["errorfield"]) ? '<span class="notice cc-field-notice">' . $this->configArray["cf_usernotice"]["errorfield"] . '</span>' : '';
		}
	
		// Überprüfen ob Feld erforderlich
		#if($field['Null'] != "NO")
		#	$this->required = false;
		
		// Überprüfen ob Feld als hidden markiert
		if($this->hideField)
			$this->fieldType = "hidden";
		
		// Überprüfen ob ein Platzhalter für value vorliegt und ggf. ersetzen (z.B {#date})
		if($this->fieldVal == "{#date}")
			$this->fieldVal = date("Y-m-d", time());
		elseif($this->fieldVal == "{#datetime}")
			$this->fieldVal = date("Y-m-d H:i:s", time());
		elseif($this->fieldVal == "{#timestamp}")
			$this->fieldVal = time();
		elseif($this->fieldVal == "{#ip}")
			$this->fieldVal = md5(User::getRealIP() . "concise_hash");
		// Überprüfen ob ein Platzhalter für Benutzerdaten vorliegt und diese ggf. einsetzen (z.B {#user:xyz})
		elseif(!is_array($this->fieldVal) && strpos($this->fieldVal, "{#user:") !== false) {
			
			if(isset($this->g_Session['username'])) {
					
				$defFieldValue	= str_replace("{#user:", "", $this->fieldVal);
				$defFieldValue	= $this->DB->escapeString(str_replace("}", "", $defFieldValue));
				$loggedUser		= $this->g_Session['username'];
				
				$loggedUserDB = $this->DB->escapeString($loggedUser);
				
				
				$userDataQuery = $this->DB->query(	"SELECT $defFieldValue 
															FROM `" . DB_TABLE_PREFIX . "user` 
															WHERE `username` = '$loggedUserDB'
														");
				
				if(count($userDataQuery) > 0)
					$this->fieldVal = $userDataQuery[0][$defFieldValue];
				else
					$this->fieldVal = "";
			}
			else
				$this->fieldVal = "";
		}

		

		//Falls der Feldtyp voreingestellt ist spezifische Merkmale einstellen
		if($this->fieldType == "default" || 
		   $this->fieldType == "date" ||
		   $field['Type'] == "date") {
			
			//Bei Text und Blob auf Textarea einstellen
			if(($field['Type'] == "text" || $field['Type'] == "blob") && $this->fieldType != "default")
				$this->fieldType = "textarea";
				
			//Bei Datum Datepicker einbinden
			elseif($field['Type'] == "date" && !$this->hideField) {
				
				$this->fieldClass .= " datepicker";
		
				$this->dateExt .=	'<input type="hidden" id="daynames" value="{s_date:daynames}" alt="{s_date:daynamesmin}" />' . "\r\n" .
									'<input type="hidden" id="monthnames" value="{s_date:monthnames}" alt="{s_date:monthnamesmin}" />' . "\r\n";
			}
		}		
		
		//Falls DB-ForeinKey-Schlüssel (z.B. username) nicht mitgegeben, Formular abbrechen
		if ($field['Key'] == "MUL" && $this->fieldVal == "")
			return "foreign key missing.";
		
		//Falls ein pagebreak auftaucht
		if(isset($this->configArray[$field['Field']]["pagebreak"]) && $this->configArray[$field['Field']]["pagebreak"]) {
			
			$this->stepCount++; //Step-Zähler Inkrement
			
			//Falls der aktuelle Schritt der Beginn der aktuellen Seite ist, den Formularbereich/Schritt anzeigen
			if($this->step == $this->stepCount)
				$this->hide = false;
		
			
			$output .=	'</ul>' . "\r\n" . //Neuer tbody als Schrittgruppe
						'<ul class="formStep-'.$this->stepCount.'"' . ($this->hide ? ' style="display:none;"' : "") . '>' . "\r\n";
		}

		//Falls das erste Feld erstellt werden soll, überprüfen, ob die erste Formularseite versteckt werden soll
		if($this->fieldCount == 1) {
			$output .=	'<ul class="formStep-1"' . (!isset($this->errorArraySteps[1]) && !empty($GLOBALS['_POST']) && $this->step > 1 ? ' style="display:none;"' : "") . '>' . "\r\n";
		}


		//Falls ein Header eingefügt werden soll
		if(isset($this->configArray[$field['Field']]["header"]) && $this->configArray[$field['Field']]["header"] != "") {
		
			$output .=	'<h4 class="automaticForm-header">' . "\r\n" .
						Modules::safeText($this->configArray[$field['Field']]["header"]) .
						'</h4>' . "\r\n";
		
		}
			
			
		//Eingabefeld darstellen, falls nicht "hidden"
		if($this->fieldType != "hidden" && $this->hideField == false) {
		
			$errTag		= "";
			$errClass	= "";

			//Überprüfen, ob ein Fehler bei der Prüfung stattfand
			if(array_key_exists($field['Field'], $this->errorArray)
			&& isset($GLOBALS['_POST']['step_'.$this->tablename])
			&& $this->step + 1 == $GLOBALS['_POST']['step_'.$this->tablename]
			) {
				$this->classFill	.= " pleaseFill";
				$this->fieldClass	.= " {t_class:fielderror}";
				$errTag				 =	$notice != "" ? $notice : ''; 
				$errClass			 = " {t_class:fielderror}";
			}
			
			//Label hinzufügen (falls radio oder mehrere Checkboxen, das "for"-Attribut hier weglassen; wird bei einzelnen Sublabels angebracht u.)
			$output .=	'<li class="automaticForm-field formField_' . $field['Field'] . ' {t_class:formrow}' . $errClass . '">' . "\r\n" .
						'<label ' . ($this->fieldType != "radio" && ($this->fieldType != "checkbox" || (!isset($this->configArray[$field['Field']]["options"]) || count($this->configArray[$field['Field']]["options"]) == 1)) ? 'for="' . $this->tablename."_".$field['Field'] . '"' : '') . ' class="automaticFormIdentifier' . ($this->required ? ' required' : '') . '">' . "\r\n" .
						$label .
						($this->required ? '<em>*</em>' : '') . '</label>' . "\r\n";
						
			$output .=	$errTag; 
		}
		
		$this->fieldClass .= $this->classFill;
		
		
		// Felder je nach Typ generieren
		// Form field event
		$output .= $this->createFormField($field);
		
		
		//Falls eine Remark eingefügt werden soll
		if(isset($this->configArray[$field['Field']]["remark"]) && $this->configArray[$field['Field']]["remark"] != "") {
			
			$formRemark =	trim($this->configArray[$field['Field']]["remark"]);
			

			$output .=		'<div class="automaticForm-remark {t_class:fmremark}">'.(strpos($formRemark, "<") !== 0 ? '<p>' : ''). "\r\n" .
							$this->configArray[$field['Field']]["remark"] .
							(strpos($formRemark, "<") !== 0 ? '</p>' : '').'</div>' . "\r\n";

		
		}
		
		$output .=	'</li>' . "\r\n";
		
		return $output;
	
	}

	/**
	 * Konfiguriertes Foreign-Key-Feld.
	 * 
	 * @param Array Attributdefinitionen in einer Arraystruktur
	 * 
	 * @return string output Wird zum Abbrechen der Methode benutzt
	 */
	private function buildForeignKeyInputRow($field)
	{
		
		$output				= "";
		$this->fieldClass	= "";
		
		//Attributnamen setzen
		$attributename		= $field['Field'];

		//Die Fremdschlüsselbeziehung aus der Datenbank holen und untersuchen
		$sql = "SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE "."table_schema = '".DB_NAME."' AND "."table_name = '".DB_TABLE_PREFIX.$this->tablename."' AND "." column_name = '".$attributename."';";

		$FKData = $this->DB->query($sql);

		//Ist FKData leer, so ist es nur ein Index und kein Fremdschlüssel
		if (count($FKData) == 0)
		{
			//Doch ein normales Eingabefeld anzeigen
			$output .=	$this->buildInputRow($field);
			//Methode beenden 
			return $output;
		}

		//Fremdschlüsselattribute festlegen
		$FKTable = $FKData[0]['REFERENCED_TABLE_NAME'];
		$FKColumn = $FKData[0]['REFERENCED_COLUMN_NAME'];

		//Überprüfen, ob ein Attribut der Fremdschlüsseltabelle als 
		//anzuzeigen definiert wurde.
		if (isset ($this->FKArray[$attributename]['FKshowName']))
		{
			$FKshowName = $this->FKArray[$attributename]['FKshowName'];
		}
		else
		{
			$FKshowName = $FKColumn;
		}

		//Wert des Attributs holen (wenn bereits ausgewählt)
		$this->fieldVal = $this->getValue($attributename);

		//Wenn kein Constraint angegeben
		if ((isset ($this->FKArray[$attributename]['constraint'])) && ($this->FKArray[$attributename]['constraint'] != ""))
		{
			//Constraint an die Datenbank schicken...
			$sql = $this->FKArray[$attributename]['constraint'];

		}
		else
		{
			$sql = "SELECT * FROM ".$FKTable.";";
		}

		$result = $this->DB->query($sql);

		//Fremdschlüssel-Auswahlliste erstellen
		$output .=	'<li class="automaticForm-field formField_' . $attributename . '">' . "\r\n" .
					'<label for="'.$this->tablename.'_'.$attributename.'" class="automaticFormIdentifier">' .
					$attributename .
					'</label>' . "\r\n";

		//Überprüfen, ob ein Fehler bei der Prüfung stattfand
		if (array_key_exists($attributename, $this->errorArray))
		{
			$this->fieldClass = " pleaseFill";
		}

		$output .=	'<select class="standardField'.$this->fieldClass.'" name="'.$this->tablename.'_'.$attributename.'" id="'.$this->tablename.'_'.$attributename.'">'. "\r\n" .
					'<option value="">-</option>'. "\r\n";

		//Für jeden Eintrag der Fremdschlüsseltabelle eine Option erstellen
		foreach ($result as $entry)
		{
			$optionValue = $entry[$FKColumn];
			//Überprüfen, ob bereits ein Feld gewählt ist...
			if ($this->fieldVal == $optionValue)
			{

				$output .=	'<option selected value="'.$optionValue.'">'. "\r\n";
			}
			else
			{
				$output .=	'<option value="'.$optionValue.'">'. "\r\n";
			}
			$output .=	$entry[$FKshowName] .
						'</option>';
		}

		$output .=	'</select>' . "\r\n" .
					'</li>' . "\r\n" .
					'</ul>' . "\r\n";
					
		return $output;				

	}

	/**
	 * Werte der Attribute überprüfen
	 */
	private function checkAttributes()
	{
		
		$step = 1;
		
		//Jedes Attribut (außer Primärschlüssel) testen:
		foreach ($this->structureData as $attribute)
		{

			//Wenn kein Primärschlüssel, dann testen
			if ($attribute['Key'] != "PRI")
			{

				//Falls ein pagebreak auftaucht, die Schrittzahl erhöhen
				if(isset($this->configArray[$attribute['Field']]["pagebreak"]) && $this->configArray[$attribute['Field']]["pagebreak"]) {
					$step++; //Step-Zähler Inkrement
					$this->totalSteps++;
				}
		
				//Checken der Komponenten:		
				$result = $this->checkInput($attribute['Field'], $attribute['Type'], $attribute['Null']);
				
				if (!$result)
				{
					//Fehler eintragen			
					$this->errorArray[$attribute['Field']] = 1;
					
							
					//Fehler-Array für Formularseiten/Schritte eintragen			
					$this->errorArraySteps[$step][$attribute['Field']] = 1;
					#var_dump($this->errorArraySteps);
				}

			}
		}
	}

	/**
	 * Überprüft den eingegebenen Wert für ein spezifisches Attribut.
	 * 
	 * @param varchar Name des Attributs
	 * @param varchar Typ des Attributs
	 * @param boolean Nullwerte erlaubt oder nicht 
	 * 
	 * @return boolean Gibt das Ergebnis der Wertüberprüfung zurück
	 */
	private function checkInput($attributename, $attributetype, $nullAllowed)
	{
		
		//Ist der Wert gesetzt
		$this->fieldVal			= $this->getValue($attributename);

		
		// Felder überprüfen
		// Form field event
		$o_checkFormFieldEvent	= new CreateFormFieldEvent();
		
		$o_checkFormFieldEvent->attributename	= $attributename;
		$o_checkFormFieldEvent->nullAllowed		= $nullAllowed;
		$o_checkFormFieldEvent->configArray		= $this->configArray;
		$o_checkFormFieldEvent->fieldVal		= $this->fieldVal;
		$o_checkFormFieldEvent->prevField		= $this->prevField;
		$o_checkFormFieldEvent->prevValue		= $this->prevValue;
		$o_checkFormFieldEvent->required		= $this->required;
		
		// dispatch check form field event
		$this->o_dispatcher->dispatch('form.check_form_field', $o_checkFormFieldEvent);
		
		
		$result	= $o_checkFormFieldEvent->getResult();
		
		// if field already checked
		if($result !== ""
		|| $o_checkFormFieldEvent->isPropagationStopped()
		) {
			$this->prevField = $o_checkFormFieldEvent->getPrevField(); //Feldname speichern
			$this->prevValue = $o_checkFormFieldEvent->getPrevVal(); //Wert speichern
			return $result;
		}
		
		
		// Weitere Typ-Unterscheidungen		
		if(isset($this->configArray[$attributename]["type"])) {
		
			$this->fieldType		= $this->configArray[$attributename]["type"];
		
		
			// Felder je nach Typ überprüfen
			// field types event
			$o_checkFieldTypesEvent	= new CreateFormFieldEvent();
			
			$o_checkFieldTypesEvent->attributename	= $attributename;
			$o_checkFieldTypesEvent->nullAllowed	= $nullAllowed;
			$o_checkFieldTypesEvent->configArray	= $this->configArray;
			$o_checkFieldTypesEvent->fieldType		= $this->fieldType;
			$o_checkFieldTypesEvent->fieldVal		= $this->fieldVal;
			$o_checkFieldTypesEvent->prevValue		= $this->prevValue;
			$o_checkFieldTypesEvent->required		= $this->required;
			
			// dispatch check date field event
			$this->o_dispatcher->dispatch('form.check_field_types', $o_checkFieldTypesEvent);
			
		
			$result	= $o_checkFieldTypesEvent->getResult();
			
			// if field already checked
			if($result !== ""
			|| $o_checkFieldTypesEvent->isPropagationStopped()
			) {
			
				$this->fieldVal										= $o_checkFieldTypesEvent->fieldVal;
				if(isset($o_checkFieldTypesEvent->configArray[$attributename]["notice"]))
					$this->configArray[$attributename]["notice"]	= $o_checkFieldTypesEvent->configArray[$attributename]["notice"];
				if(count($o_checkFieldTypesEvent->recipients) > 0)
					$this->recipients								= array_merge($this->recipients, $o_checkFieldTypesEvent->recipients);
				
				$this->prevField = $o_checkFormFieldEvent->getPrevField(); //Feldname speichern
				$this->prevValue = $o_checkFormFieldEvent->getPrevVal(); //Wert speichern
				
				return $result;
			}
			
		}
		
		$this->prevField = $attributename; //Feldname speichern
		$this->prevValue = $this->fieldVal; //Wert speichern
		
		//Checkobject erstellen:
		$DV = new Check();

		//Testergebnis bei Standardfeldern (def by db-field) zurückgeben
		return $DV->checkData($attributetype, $this->fieldVal);
	
	}

	/**	
	 * Die Struktur der Foreign Keys wird gesetzt.
	 * 
	 * @param varchar Name des Attributs
	 * @param Array Fremdschlüsselarray
	 */
	public function setForeignKeyStructure($attributename, $FKArray)
	{
		//FK-Struktur in das assoziative Array speichern
		$this->FKArray[$attributename] = $FKArray;

	}

	/**
	 * Captcha generieren
	 *
	 * @param boolean Überprüfung des Captchas, falls true
	 */
	private function getCaptcha($captchaCheck)
	{

		$output		= "";
		$errTag		= "";
		$errClass	= "";
					
		if(!$this->checkCaptcha) {
			$errTag		= '<span class="notice cc-field-notice">{s_error:captcha}</span>' . "\r\n";
			$errClass	= " {t_class:fielderror}";
		}
		
		$output .= 	'<ul class="{t_class:row}">' . "\r\n" . 
					'<li class="automaticForm-field formField_captcha {t_class:formrow}' . $errClass . '">' . "\r\n" .
					'<span class="fieldLeft {t_class:halfrowsm}">' . "\r\n" .
					'<label for="captcha_confirm">{s_form:captcha}<em>&#42;</em></label>' . "\r\n";
		
		$output .= 	$errTag;
		
		$output .=	'<input name="captcha_confirm" type="text" id="captcha_confirm" class="{t_class:field}" aria-required="true" data-validation="required" />' . "\r\n" .
					'</span>' . "\r\n" .
					'<span class="fieldRight {t_class:halfrowsm}">' . "\r\n" .
					'<span class="captchaBox">' . "\r\n" .
					'<label>&nbsp;</label><br />' . "\r\n" .
					'<img src="' . PROJECT_HTTP_ROOT . '/access/captcha.php" alt="{s_form:capalt}" title="{s_form:captit}" class="captcha" />' . "\r\n";
		
		// Button caprel
		$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/access/captcha.php',
								"text"		=> '',
								"class"		=> "caprel button-icon-only {t_class:btninf} {t_class:btnsm}",
								"title"		=> '{s_form:capreltit}',
								"attr"		=> 'tabindex="2"',
								"icon"		=> "refresh",
								"icontext"	=> ""
							);
		
		$output .=	parent::getButtonLink($btnDefs);
		
		$output .=	'</span>' . "\r\n" . 
					'</span>' . "\r\n" . 
					'<br class="clearfloat" />' . "\r\n" . 
					'</li>' . "\r\n" .
					'</ul>' . "\r\n";
		
		return $output;
	}
	
	/**
	 * Knöpfe für das Formular werden ausgegegeben
	 *
	 * @param boolean Anzeige eines Resetbuttons, falls true (default = false)
	 */
	private function buildButtons($showResetBtn = false)
	{

		$output			= "";
		$finalSubmit	= false;
		
		// Falls Final Submit bzw. letzt Seite des Formulars
		if(($this->step == $this->stepCount && !empty($GLOBALS['_POST'])) || $this->stepCount == 1)
			$finalSubmit = true;
		
		$submitVal	= $finalSubmit ? "{s_button:submitautoform}" : "{s_button:next}";
		$submitIco	= $finalSubmit ? "ok" : '';
		
		//Eingabefeld darstellen
		$output .=	'<ul class="automaticForm">' . "\r\n" .
					'<li class="{t_class:formrow} submitPanel' . ($this->adminPage ? ' change submit' : '') . '">' . "\r\n";
		
		// Button submit
		$btnDefs	= array(	"type"		=> "submit",
								"class"		=> 'standardSubmit submit-next formbutton {t_class:btn} {t_class:btnpri}'. ($finalSubmit ? " finalSubmit" : ""),
								"name"		=> 'send_'.$this->tablename,
								"value"		=> "true",
								"text"		=> $submitVal,
								"attr"		=> 'role="submit" onclick="var sbm = $(\'<input type=&quot;hidden&quot; name=&quot;\' + $(this).attr(\'name\') + \'&quot; value=&quot;true&quot; />\'); sbm.insertAfter($(this));" onfocus="this.blur();"',
								"icon"		=> $submitIco
							);
		
		$output .=	parent::getButton($btnDefs);
		
		
		// Button reset
		if($showResetBtn) {
			$btnDefs	= array(	"type"		=> "reset",
									"class"		=> 'standardSubmit submit-reset formbutton alt {t_class:btn} {t_class:btnsec}',
									"value"		=> "{s_button:reset}",
									"text"		=> "{s_button:reset}",
									"attr"		=> 'onfocus="blur();"',
									"icon"		=> $submitIco
								);
			
			$output .=	parent::getButton($btnDefs);
		}
		
		
		// Button back
		if($this->step > 1 && $this->totalSteps > 1) { // Zurückbutton bei mehrseitigen Formularen, falls nicht Step 1
			$btnDefs	= array(	"type"		=> "submit",
									"class"		=> 'standardSubmit submit-back formbutton alt {t_class:btn} {t_class:btnsec}',
									"name"		=> 'send_'.$this->tablename.'_back',
									"value"		=> "{s_button:back}",
									"text"		=> "{s_button:back}",
									"attr"		=> 'role="submit" onfocus="blur();"',
									"icon"		=> "back"
								);
			
			$output .=	parent::getButton($btnDefs);
		}
		$output .=	(isset($this->configArray['cf_poll']) && $this->configArray['cf_poll'] == true ? '<input type="hidden" name="send_'.$this->tablename.'" value="true" />' . "\r\n" : "") .
					'<input type="text" name="m-mail" id="m-mail" class="emptyfield" value="" />' . "\r\n" . // Mockfield
					(isset($this->configArray['cf_id']) ? '<input type="hidden" name="pollID" class="pollID" value="' . $this->configArray['cf_id'] . '" />' . "\r\n" : '') . // Poll-ID
					'<input type="hidden" class="hidden" name="form_submission_'.$this->tablename.'" value="true" />' . "\r\n" .
					'<input type="hidden" class="hidden" name="step_'.$this->tablename.'" value="'.($this->step + 1).'" />' . "\r\n" .
					'<input type="hidden" class="hidden" name="former_step_'.$this->tablename.'" value="'.(isset($GLOBALS['_POST']["former_step_".$this->tablename]) ? $GLOBALS['_POST']["former_step_".$this->tablename] : "1").'" />' . "\r\n";
					#'<input type="hidden" class="hidden" name="former_step_'.$this->tablename.'" value="'.(isset($GLOBALS['_POST']["step_".$this->tablename]) ? $GLOBALS['_POST']["step_".$this->tablename] : '').'" />' . "\r\n" .
		
		if(isset($this->configArray['cf_poll']) && $this->configArray['cf_poll'] == true)
			$output .=	'<input type="hidden" name="pollDate" class="pollDate" value="' . strtotime($this->configArray['cf_timestamp']) . '" />' . "\r\n"; // Polldatum
		
		// Token
		if($finalSubmit)
			$output .=	parent::getTokenInput();
		
		$output .=	'</li>' . "\r\n" .
					'</ul>' . "\r\n";
					
		return $output;

	}

	/**
	 * Erstellt Datenarrays aus den Formulardaten und trägt diese ggf. in DB ein
	 *
	 * @return	Boolean
	 */
	private function buildDataArrays()
	{

		//Alle Daten wurden gecheckt und für "korrekt" befunden.
		//Einzig Datentypen wie timestamp und date müssen erzeugt werden.

		$sqlFields		= "";
		$sqlValues		= "";
		$sqlFieldsArr	= array();
		$sqlValuesArr	= array();
		
		//Attributnamen schreiben:
		foreach ($this->structureData as $attribute)
		{
			if ($attribute['Key'] != "PRI")
			{
				//Attribut in Insert-String/Update-Array einfügen
				$sqlFields .= "`".$attribute['Field']."`,";
				$sqlFieldsArr[] = "`".$attribute['Field']."`";
				
				//Wert des Feldes
				$this->fieldVal = $this->getValue($attribute['Field']);

				//Art des Feldes
				$this->fieldType = $this->getValue($attribute['Type']);
				

				//Falls value ein Array ist (z.B. bei Mehrfachauswahl), aber kein Files-Array(!) das Array serialisieren
				if(is_array($this->fieldVal) && (!isset($this->configArray[$attribute['Field']]["type"]) || $this->configArray[$attribute['Field']]["type"] != "file")) {
				
					#$this->fieldVal = serialize($this->fieldVal);
					#$this->fieldVal = $this->getInputString($attribute['Field'], $this->fieldVal);
					
					// Falls Checkbox, Key als Werte nehmen
					if($this->configArray[$attribute['Field']]["type"] == "checkbox")
						$this->fieldVal = json_encode(array_keys($this->fieldVal));
					// Andernfalls Array Values
					else
						$this->fieldVal = json_encode($this->fieldVal);

				}
				
				//Falls value ein Float oder decimal sein soll, evtl. Komma ersetzen und float generieren
				if(stripos($attribute['Type'], "float") !== false || stripos($attribute['Type'], "decimal") !== false) {
					$inputValue = $this->fieldVal; // Eingegebenes Zahlenformat
					$this->fieldVal = Check::getFloat($this->fieldVal);
				}
				
				//Falls value ein Datum ist, Datum im Format yyyy-mm-dd generieren
				if($attribute['Type'] == "date") {
					$inputValue = $this->fieldVal; // Eingegebenes Zahlenformat
					$this->fieldVal = Check::getDateString($this->fieldVal);
				}
				
				//Falls bestimmte Typen definiert sind
				if(array_key_exists($attribute['Field'], $this->configArray) && isset($this->configArray[$attribute['Field']]["type"])) {
					#var_dump($this->configArray[$attribute['Field']]);
					$this->fieldType = $this->configArray[$attribute['Field']]["type"];
					
					//Falls value ein Passwort ist, verschlüsseltes Passwort generieren
					if($this->fieldType == "password") {
						$uncryptPass		= $this->fieldVal;
						if($this->fieldVal != "")
							$this->fieldVal	= Security::hashPassword($this->fieldVal, CC_SALT);
					}
					
					//Falls value ein File ist, Datei hochladen und Namen generieren
					if($this->fieldType == "file") {
					
						// Falls ein Eintrag bearbeitet werden soll und das Feld vom Typ File ist
						// und keine neue Datei hochgeladen werden soll
						if(array_key_exists("cf_editformdata", $this->configArray) && $this->fieldVal['name'] == "")
							$this->fieldVal = $GLOBALS['_POST'][$attribute['Field'] . "_oldFile"];
							
						else {
							
							$prefix		= isset($this->configArray[$attribute['Field']]["fileprefix"]) ? $this->configArray[$attribute['Field']]["fileprefix"] : '';
							$baseName	= Files::getFileBasename($this->fieldVal['name']);
							$fileExt	= Files::getFileExt($this->fieldVal['name']);
							
							// Falls die Datei umbenannt werden soll (Präfix + UserID) bei benutzerspezifischen Dateien
							if(isset($this->configArray[$attribute['Field']]["filerename"]) && $this->configArray[$attribute['Field']]["filerename"] == 1) {
								if(isset($this->g_Session['username']) && isset($this->g_Session['userid']))
									$prefix		.= $this->g_Session['userid'];
								else
									$prefix		.= "NOUSERID";
									
								$fileName	= Files::getValidFileName($prefix, true);
								$folder		= '_user';
							}
							else {
								$fileName	= Files::getValidFileName($prefix . $baseName, true);
								$folder		= CC_FILES_FOLDER;
							}
								
							$folder		   .= isset($this->configArray[$attribute['Field']]["filefolder"]) && $this->configArray[$attribute['Field']]["filefolder"] != "" ? '/'.$this->configArray[$attribute['Field']]["filefolder"] : '';
							$maxFileSize	= isset($this->configArray[$attribute['Field']]["filesize"]) ? $this->configArray[$attribute['Field']]["filesize"] : 5242880;
							$fileUpload		= Files::uploadFile($this->fieldVal['name'], $this->fieldVal['tmp_name'], $folder, "", 0, 0, true, $fileName, "", false, $maxFileSize);
							$this->fieldVal	= Files::getValidFileName($fileName, true) . "." . $fileExt;
						}	
					}
				}
				
				
				//SQL-String
				//Attribut-Wert in Insert-String/Update-Array einfügen
				$sqlValues .= "'".$this->DB->escapeString($this->fieldVal)."',";
				$sqlValuesArr[] = "'".$this->DB->escapeString($this->fieldVal)."'";
				
				
				//Falls value ein Datum, eine Fließkomma- oder Dezimal-Zahl ist, für die Ausgabe das Eingabeformat wieder herstellen
				if($attribute['Type'] == "date" || stripos($attribute['Type'], "float") !== false || stripos($attribute['Type'], "decimal") !== false) {
					
					$this->fieldVal = $inputValue;
				}
				
				//Falls value ein (verschlüsseltes) Passwort ist, das unverschlüsselte Passwort oder einen Leerstring für E-Mail speichern
				if($this->fieldType == "password") {
					
					if(isset($this->configArray[$attribute['Field']]["showpass"]) && $this->configArray[$attribute['Field']]["showpass"] === true)
						$this->fieldVal = $uncryptPass;
					else
						$this->fieldVal = "******";
				}
				
				//Falls value ein Array, String erstellen
				if($this->fieldType == "checkbox"
				|| $this->fieldType == "multiple"
				){
					
					$this->fieldVal = implode(", ", (array)json_decode($this->fieldVal));
				}
				
		
				// Eingaben zusätzlich in Array speichern (für spätere Verwendung z.B. in E-Mail)
				// Data array event
				$o_buildDataArrayEvent	= new CreateFormFieldEvent();
				
				$o_buildDataArrayEvent->attribute		= $attribute;
				$o_buildDataArrayEvent->fieldVal		= $this->fieldVal;
				$o_buildDataArrayEvent->configArray		= $this->configArray;

				
				// dispatch check date field event
				$this->o_dispatcher->dispatch('form.build_data_array', $o_buildDataArrayEvent);
				
				
				// if field already not to be saved
				if($o_buildDataArrayEvent->isPropagationStopped())
					continue;
				
				$this->formInputArrayDB	= array_merge($this->formInputArrayDB, $o_buildDataArrayEvent->formInputArrayDB);
				$this->formInputArray	= array_merge($this->formInputArray, $o_buildDataArrayEvent->formInputArray);
			
			}

		} // Ende foreach
		
		
		// Weitere Eingaben zusätzlich in Array speichern
		// Extra data event
		$o_addExtraDataEvent	= new CreateFormFieldEvent();
		
		// dispatch check date field event
		$this->o_dispatcher->dispatch('form.get_extra_data', $o_addExtraDataEvent);
		
		
		$result	= $o_addExtraDataEvent->getResult();
		
		// if field already not to be saved
		if(!empty($result))
			$this->formInputArray[]	= array("_formremark", $result);
		

		// Daten in DB speichern, falls nicht anders angegeben
		if(!$this->noDbStorage)
			$data = $this->saveNewRecord($sqlFields, $sqlValues, $sqlFieldsArr, $sqlValuesArr);
		else
			$data = true;
		
		return $data;
	}
	
	
	/**
	 * Speichert einen neuen Datensatz
	 *
	 * @param String sqlFields Input-String
	 * @param String sqlValues Input-String
	 * @param Array sqlFieldsArr Input-Array
	 * @param Array sqlValuesArr Input-Array
	 * @return Boolean DB-Result
	 */
	private function saveNewRecord($sqlFields, $sqlValues, $sqlFieldsArr, $sqlValuesArr)
	{		
		
		$sql			= "";
		$sqlData		= "";
		
		//SQL-Daten einfügen/updaten
		//Falls ein Datensatz  bearbeitet werden soll
		if(array_key_exists("cf_editformdata", $this->configArray)) {
			
			$editIDdb	= $this->DB->escapeString($this->configArray["cf_editid"]);
			
			
			foreach($sqlFieldsArr as $key => $field) {
				
				$sqlData .= $field . " = " . $sqlValuesArr[$key] . ",";
			}
				
			//Letztes Komma abtrennen
			$sqlData = substr($sqlData, 0, -1);
			
			//SQL-Update-Statement
			$sql .= "UPDATE `" . DB_TABLE_PREFIX . $this->tablename . "` SET ";
			$sql .= $sqlData;
			$sql .= " WHERE `id` = " . $editIDdb;
		}
		
		else { //Andernfalls neuen Datensatz einfügen
			
			//Letztes Komma abtrennen
			$sqlFields = substr($sqlFields, 0, -1);
			$sqlValues = substr($sqlValues, 0, -1);
			
			//SQL-Insert-Statement
			$sql .= "INSERT INTO `" . DB_TABLE_PREFIX . $this->tablename . "` (";
			$sql .= $sqlFields;
			$sql .= ") VALUES (";
			$sql .= $sqlValues;
			$sql .= ")";
		}
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . $this->tablename . "`");
				
		// Daten speichern
		$return = $this->DB->query($sql);

		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");
		
		return $return;
	}


	/**
	 * Wandelt den Inputtyp Array in einen String zur Speicherung in DB
	 * 
	 * @param Array inputArray Input-Array
	 * @return String Input als String
	 */
	private function getInputString($field, $inputArray)
	{
		
		//Falls kein Array, nichts machen
		if(!is_array($inputArray))
			return $inputArray;
			
		//Falls der Inputtyp Checkbox ist, die Schlüsselnamen nehmen, da die Schlüsselwerte nur "on" enthalten, die Schlüsselnamen enthalten aber den Optionswert
		if(isset($this->configArray[$field]["type"]) && $this->configArray[$field]["type"] == "checkbox")
			$inputArray = array_keys($inputArray);
			
		$string = "";
		
		//Array-Elemente serialisieren, wenn nicht leer
		foreach($inputArray as $input) {

			if($input != "")
				$string .= $input . ',';
		}
		
		//Letztes Komma entfernen
		if(strrpos($string, ",") !== false)
			$string = substr($string, 0 , -1);
		
		return $string;
		
	}

	/**
	 * Zeigt eine einfache Liste der Einträge der Tabelle an
	 */
	public function printList()
	{
	
		$output = "";
		
		//Alle Einträge der Tabelle holen
		$sql = "SELECT * FROM `" . DB_TABLE_PREFIX . $this->tablename . "`";
		$result = $this->DB->query($sql);

		$output .=	'<table class="autoFormResultList {t_class:table}">';
		
		//Jede Zeile in eigener Tabellenzeile anzeigen
		foreach ($result as $line)
		{
			$output .=	'<tr>';
			foreach ($line as $attrib)
			{
				$output .=	'<td>'.$attrib.'</td>';
			}
			$output .=	'</tr>';
		}
		$output .=	'</table>';

	}

	/**
	 * Ermittelt Längenbegrenzungen für Datentyp-Varchar/Int
	 * 
	 * @param String $typeString	Datentyp
	 * @param String $field			falls angegeben, werden Längendefinitionen aus dem ConfigArray überprüft (default = '')
	 * @return Boolean				Versanderfolg
	 */
	private function getLengthOptions($typestring, $field = "")
	{

		//Falls der Inputtyp Checkbox ist, die Schlüsselnamen nehmen, da die Schlüsselwerte nur "on" enthalten, die Schlüsselnamen enthalten aber den Optionswert
		if($field != "" && isset($this->configArray[$field]["maxlen"]) && $this->configArray[$field]["maxlen"] != "") {
			
			$maxLen = $this->configArray[$field]["maxlen"];
		}
		else {
		
			//Position des ersten Klammer
			$firstBracket = stripos($typestring, "(");
			//Position des zweiten Klammer
			$secondBracket = strripos($typestring, ")");
	
			//alles dazwischen holen: set('value1','value2') 
			//dann haben wir die Liste 'value1', 'value2'
	
			$maxLen = substr($typestring, ($firstBracket +1), ($secondBracket - $firstBracket -1));
		}
		
		return $maxLen;
		
	}
	
	/**
	 * Generiert eine Tabelle mit Formulardaten
	 * 
	 * @return String Ergebnistabelle
	 */
	public function getTabularFormData()
	{
		
		$formData = "";
		$loopData = "";
		$addFieldContents = false;

		// Falls, zusätzliche Felder mit Benutzerdaten hinzugefügt werden sollen
		if($this->addFields !== false)
			$addFieldContents = $this->getAddFieldContents();
		
		$i = 0;
		
		// Datenarray auslesen und Tabellenzeilen generieren
		foreach($this->formInputArray as $data) {
			
			if($data[0] == "_formheader") {
				$loopData .=	'<tr>' . "\r\n" .
								'<th colspan="2"><h2>' . Modules::safeText($data[1]) . '</h2></th>' . "\r\n" .
								'</tr>' . "\r\n";
			}								
			elseif($data[0] == "_formremark") {
				$loopData .=	'<tr>' . "\r\n" .
								'<td colspan="2"><p>' . Modules::safeText($data[1]) . '</p></td>' . "\r\n" .
								'</tr>' . "\r\n";
			}								
			else {
				$loopData .=	'<tr>' . "\r\n" .
								'<td>' . Modules::safeText($data[0]) . '</td><td>' . Modules::safeText($data[1]) . '</td>' . "\r\n" .
								'</tr>' . "\r\n" .
								($addFieldContents !== false && isset($addFields["position"]) && $addFields["position"] == $this->formInputArrayDB[$i] ? $addFieldContents : '');
			}
								
			$i++;
			
		}
		
		// Datentabelle
		$formData .= '
						<table>
							<tr>
							<td colspan="2"><h1>'.$this->formTitle.'</h1></td>
							</tr>' .
							($addFieldContents !== false && (!isset($addFields["position"]) || $addFields["position"] == "top") ? $addFieldContents : '') .
							$loopData .
							($addFieldContents !== false && isset($addFields["position"]) && $addFields["position"] == "bottom" ? $addFieldContents : '') .
						'</table>
					 ';
		
		return $formData;
	}

	/**
	 * Hinzufügen von Feldinhalten aus anderer Tabelle, die mit Benutzerdaten verknüpft sind
	 * 
	 * @return String 	Ergebnistabelle
	 */
	public function getAddFieldContents()
	{
		
		// Datenarray holen
		$addDB		= $this->addFields["table"];
		$addDBDB	= $this->DB->escapeString(DB_TABLE_PREFIX . $addDB);
		$fieldsDB	= "";
		$labelsDB	= "";
		$userData	= "";
		
		// Haupt-E-Mailadresse
		$fields		= $this->addFields["fields"];
		
		foreach($fields as $field) {
			$fieldsDB	.= "`" . $this->DB->escapeString($field) . "`,";
		}
		$fieldsDB = substr($fieldsDB, 0, -1);
		
		// Haupt-E-Mailadresse
		$labels		= $this->addFields["labels"];


		//Falls die Formularinhalte an einen eingeloggten Benutzer gehen sollen
		if(isset($this->g_Session['username'])) {
			
			$userDB = $this->DB->escapeString($this->g_Session['username']);
			
			
			$userDataQuery = $this->DB->query(	"SELECT $fieldsDB 
													FROM `$addDBDB` 
													WHERE `username` = '$userDB'
													");
			
			#var_dump($userDataQuery);
			
			$i = 0;
			
			// Datenarray auslesen und Tabellenzeilen generieren
			foreach($userDataQuery[0] as $data) {
				
				$userData .=	'<tr>' . "\r\n" .
								'<td>' . $labels[$i] . '</td><td>' . $data . '</td>' . "\r\n" .
								'</tr>' . "\r\n";
								
				$i++;
			}
			
			return $userData;
		}
		
		else
			return "";
		
	}	

	/**
	 * Erstellen einer pdf-Datei mit Formulardaten
	 * 
	 * @return String 	Ergebnistabelle
	 */
	public function getPDF()
	{
	
		// Form pdfMaker event
		$o_makePDFEvent	= new MakePDFEvent();
		
		$o_makePDFEvent->formTitle		= $this->formTitle;
		$o_makePDFEvent->formInputArray	= $this->formInputArray;
		$o_makePDFEvent->userPDF		= $this->userPDF;
		$o_makePDFEvent->g_Session		= $this->g_Session;
		$o_makePDFEvent->pdfFolder		= $this->pdfFolder;
		$o_makePDFEvent->browserPDF		= $this->browserPDF;
		$o_makePDFEvent->mailPDF		= $this->mailPDF;
		$o_makePDFEvent->pdfMailAttach	= $this->pdfMailAttach;
		$o_makePDFEvent->pdfName		= $this->pdfName;
		
		// dispatch pdf field event
		$this->o_dispatcher->dispatch('pdf.make_pdf', $o_makePDFEvent);
		
		
		// Falls die pdf-Datei per E-Mail versandt werden soll
		if($this->mailPDF) {
			
			// E-Mail-Attachment (pdf)
			$this->pdfName			= $o_makePDFEvent->getPdfName();
			$this->pdfMailAttach	= $o_makePDFEvent->getPdfOutput("S");
		}

		
		// Falls ein Fehler aufgetreten ist
		if(!empty($o_makePDFEvent->pdfError))
			return "0";
		else
			return "1";

	}
	
	/**
	 * Versendet Formulardaten per E-Mail
	 * 
	 * @return int		Ergebnisstatus
	 */
	public function runFormMailer()
	{
		
		//Checkobject erstellen:
		$FM = new FormMailer($this->DB, $this->formData, $this->recipients, $this->recipientsCC, $this->recipientsBCC, $this->mailSubject);

		//Testergebnis zurückgeben
		$result = $FM->sendForm($this->formTitle, $this->mailPDF, $this->pdfName, $this->pdfMailAttach);
		
		if($result)
			return "1";
		else
			return "0";
		
	}
	
	
	
	/**
	 * Methode zum Erstellen eines Konfigurations-Arrays für Formulare
	 * 
	 * @param	array	$queryFormFields Array mit Formularfeldtabellen-Daten
	 * @param	array	$lang	Sprache
	 * @access	public
	 * @return	array
	 */
	public function makeFormConfigArray($queryFormFields, $lang)
	{
		
		$configArray = array();

		// Funktion zum erstellen eines Formulardatenarrays einbinden
		require_once PROJECT_DOC_ROOT."/inc/classes/Forms/config.formData.php";
		
		return $configArray; // Array mit Konfigurationsdaten zurückgeben
		
	}
	
	
	
	/**
	 * Gibt ein form action Attribut zurück
	 * 
	 * @access	public
	 * @return	array
	 */
	public function getFormActionStr()
	{
		
		// Formular-Aktion
		$formAction = parent::$currentURL;
		
		// Falls Https-Protokoll verwendet werden soll
		if($this->useHttps)			
			$formAction = str_replace("http://", "https://", $formAction);
			
		if(array_key_exists("cf_querystring", $this->configArray))
			$formAction .= '?' . $this->configArray["cf_querystring"];
		
		return $formAction;
	
	}
	
	
	
	/**
	 * Gibt ein form field zurück
	 * 
	 * @access	public
	 * @return	array
	 */
    public function createFormField($field)
    {

		$fieldOutput		= "";
		$this->fieldMaxLen	=	$this->getLengthOptions($field['Type'], $field['Field']);
		
		// Felder je nach Typ generieren
		// Form field event
		$o_createFormFieldEvent	= new CreateFormFieldEvent();
		
		$o_createFormFieldEvent->field			= $field;
		$o_createFormFieldEvent->tablename		= $this->tablename;
		$o_createFormFieldEvent->configArray	= $this->configArray;
		$o_createFormFieldEvent->required		= $this->required;
		$o_createFormFieldEvent->fieldType		= $this->fieldType;
		$o_createFormFieldEvent->fieldVal		= $this->fieldVal;
		$o_createFormFieldEvent->fieldSel		= $this->fieldSel;
		$o_createFormFieldEvent->dateExt		= $this->dateExt;
		$o_createFormFieldEvent->fieldMaxLen	= $this->fieldMaxLen;
		$o_createFormFieldEvent->fieldTitle		= $this->fieldTitle;
		$o_createFormFieldEvent->fieldClass		= $this->fieldClass;
		$o_createFormFieldEvent->classFill		= $this->classFill;
		
		// dispatch form field event
		$this->o_dispatcher->dispatch('form.create_form_field', $o_createFormFieldEvent);
		
		
		// if field already created
		$fieldOutput	.= $o_createFormFieldEvent->getFieldOutput();
		
		return $fieldOutput;
	
	}
	
	
	
	/**
	 * getFormSubmissionStatus
	 * 
	 * @access	public
	 * @return	array
	 */
    public function getFormSubmissionStatus()
    {

		return $this->formSubmitted;
	
	}
	
	
	
	/**
	 * getFormSubmissionResult
	 * 
	 * @access	public
	 * @return	array
	 */
    public function getFormSubmissionResult()
    {

		return $this->formSuccess;
	
	}

}
