<?php
namespace Concise;



/**
 * Klasse für Kontaktformularerstellung
 *
 */

class Contactfm extends Modules
{
	 
    /**
     * Formular-Format
     *
     * @access public
     * @var    string
     */
    public $formFormat = "block";
	 
    /**
     * Style-Klasse Formular
     *
     * @access public
     * @var    string
     */
    public $formClass = "";
	 
    /**
     * Style-Klasse Formularlabels
     *
     * @access public
     * @var    string
     */
    public $labelClass = "";
	 
    /**
     * Formularlabels verstecken
     *
     * @access public
     * @var    boolean
     */
    public $hideLabels = false;
	 
    /**
     * Style-Klasse Formularfelder
     *
     * @access public
     * @var    string
     */
    public $fieldClass = "";
	 
    /**
     * Formularlegende
     *
     * @access public
     * @var    boolean
     */
    public $showLegend = true;
	 
    /**
     * Formularfelder
     *
     * @access public
     * @var    array
     */
    public $formFields = array();
	 
    /**
     * Anrede
     *
     * @access public
     * @var    string
     */
    public $formOfAddress = "";
  
    /**
     * Titel
     *
     * @access public
     * @var    string
     */
    public $title = "";
  
    /**
     * Name
     *
     * @access public
     * @var    string
     */
    public $name = "";
  
    /**
     * Vorname
     *
     * @access public
     * @var    string
     */
    public $firstName = "";
  
    /**
     * Besteller Firma
     *
     * @access public
     * @var    string
     */
    public $company = "";
  
    /**
     * Betreff
	 *
     * @access public
     * @var    string
     */
    public $subject = "";
  
    /**
     * Betreffzeilen
	 *
     * @access public
     * @var    string
     */
    public $subjectItems = "";
  
    /**
     * E-Mail
     *
     * @access public
     * @var    string
     */
    public $email = "";
  
    /**
     * Phone
     *
     * @access public
     * @var    string
     */
    public $phone = "";
  
    /**
     * Bemerkung
     *
     * @access public
     * @var    string
     */
    public $message = "";

	/**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $report = "";
  
    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $error = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorEmpty = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorName = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorFirstName = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorCompany = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorMail = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorPhone = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorMes = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorCap = "";

    /**
     * Formular anzeigen
     *
     * @access public
     * @var    string
     */
    public $showForm = true;


	/**
	 * Erstellt ein Kontaktformular
	 * 
     * @param	array	$formFields
     * @param	array	$staText
     * @access	public
	 */
	public function __construct($formFields, $g_Session)
	{
		
		$this->formFields		= $formFields; // Config Array
		$this->g_Session		= $g_Session;
		
		if(isset($this->formFields["form"]) && $this->formFields["form"] == "inline") {
			$this->formFormat	= "inline";
			$this->formClass	= "contactfm {t_class:formhorizon}";
			$this->labelClass	= "{t_class:labelinl} {t_class:col2}";
			$this->fieldClass	= "{t_class:fieldinl}";
		}
		else {
			$this->formFormat	= "block";
			$this->formClass	= "contactfm {t_class:form}";
			$this->labelClass	= "{t_class:label}";
			$this->fieldClass	= "{t_class:field}";
		}
		
		if(isset($this->formFields["lab"]) && $this->formFields["lab"]) {
			$this->hideLabels	= true;
			$this->labelClass  .= ' ' . "{t_class:hidelabel}";
		}
		else {
			$this->hideLabels	= false;
		}
		
		if(isset($this->formFields["leg"]) && !$this->formFields["leg"])
			$this->showLegend	= false;
	
	}
	
	
	/**
	 * Erstellt ein Kontaktformular
	 * 
     * @access public
     * @return string
	 */
	public function getContactForm()
	{
			
		// Formular action
		$formAction		= parent::$currentURL;
		$noticeOpenTag	= '<span class="notice {t_class:texterror}">';
		$noticeCloseTag	= '</span>';
		
		
		// Falls Formular abgeschickt wurde, Auswertung starten und bei Fehlerfreiheit E-Mail abschicken
		if(isset($GLOBALS['_POST']['contactfm'])) {
			
			if($this->checkContactForm() == true) {
				header("Location:" . $formAction . "?mail=sent");
				exit;
			}

		}

		// Falls ein Eintrag vorgenommen wurde
		if(isset($GLOBALS['_GET']['mail']) && $GLOBALS['_GET']['mail'] == "sent") {
			$this->report = '{s_notice:mailsent}';
			$this->showForm = false;
		}
		
		// Falls der Cookie gegen Spam gesetzt wurde, Nachricht ausgeben und Formular nicht anzeigen
		elseif (isset($GLOBALS['_COOKIE']['contact_spam_protection']) && $GLOBALS['_COOKIE']['contact_spam_protection'] == "contact_spam_protection") {
			$this->error .= '{s_error:spam}';
			$this->showForm = false;
		}

		// Falls ein Eintrag vorgenommen wurde
		if(empty($this->formFields)
		|| count($this->formFields) == 1
		) {
			$this->error	= '<a href="' . ADMIN_HTTP_ROOT . '?task=edit&edit_id=" onclick="var href = $(this).parents(\'.innerEditDiv\').find(\'*[data-action=&quot;editcon&quot;][data-actiontype=&quot;edit&quot;]\').attr(\'data-url\'); if(typeof(href) == \'undefined\'){href = $(this).attr(\'href\') + $(\'body\').attr(\'id\').split(\'page-\')[1];}$(this).attr(\'href\', href);">{s_javascript:newelement} ' . parent::getIcon("edit") . '</a>' . "\n";
			$this->showForm = false;
		}
				
		$form =		'<div id="contactForm" class="form {t_class:form} cc-cform' . ($this->hideLabels ? ' cc-form-labelless' : '') . '">' . "\r\n" .
					'<div class="top"></div>' . "\r\n" .
					'<div class="center">' . "\r\n" .
					'<form id="contactfm" class="' . $this->formClass . '" method="post" action="' . $formAction . '#contactfm">' . "\r\n" .
					'<fieldset>' . "\r\n";
		
		// Legend
		if($this->showLegend)
			$form .=	'<legend>' . parent::getIcon("mail") . '{s_form:contacttit}</legend>' . "\r\n";
		
					
		if($this->report != "")
			$form .=	$this->getNotificationStr($this->report);
		
		elseif($this->error != "")
			$form .=	$this->getNotificationStr($this->error, "error");
		
		if($this->showForm == false) { // Falls nur Meldungen ausgegeben werden sollen (z.B. erfolgreicher Versand) Formular nicht anzeigen
		
			$form .='</fieldset>' . 
					'</form>' . 
					'</div>' . 
					'<div class="bottom"></div>' . "\r\n" .
					'</div>' . "\r\n";

			return ContentsEngine::replaceStaText($form);
		}
	
	
		// Andernfalls Formular anzeigen		
		// Error box
		$form .=	'<div class="formErrorBox"></div>' . "\r\n";
		
		if(!$this->hideLabels)
			$form .=	'<p class="footnote topNote {t_class:alert} {t_class:info}">{s_form:req}</p>' . "\r\n";
		
		$form .=	'<ul>' . "\r\n";
		
		$formRow	= "";
			
		// Falls inline
		if($this->formFormat == "inline") {
			$labelSpanLft	= "";
			$fieldSpanLft	= '<span class="fieldLeft {t_class:col5}">' . "\r\n";
			$labelSpanRgt	= "";
			$fieldSpanRgt	= '<span class="fieldRight {t_class:col5}">' . "\r\n";
		}
		else {
			$labelSpanLft	= '<span class="fieldLeft {t_class:halfrowsm} {t_class:alpha}">' . "\r\n";
			$fieldSpanLft	= "";
			$labelSpanRgt	= '<span class="fieldRight {t_class:halfrowsm} {t_class:omega}">' . "\r\n";
			$fieldSpanRgt	= "";
		}
		
		// Anrede
		if($this->formFields["foa"]) {
		
			// Falls mit Titel
			if($this->formFields["title"] && $this->formFormat == "inline") {
				$secLabel	= ' / <label for="title" class="secondLabel">{s_form:grade}</label>';
			}
			else {
				$secLabel = "";
			}

			$formRow .=	$labelSpanLft .
						'<label class="' . $this->labelClass . '" for="formOfAddress">{s_form:anrede}<em>&#42;</em>' . $secLabel . '</label>' . "\r\n" .
						$fieldSpanLft .
						'<select name="formOfAddress" id="formOfAddress" class="{t_class:select} ' . $this->fieldClass . '" aria-required="true">' . "\r\n" . 
						'<option' . "\r\n";
						
			if(isset($GLOBALS['_POST']['formOfAddress']) && $GLOBALS['_POST']['formOfAddress'] == parent::$staText['form']['herr'])
				$formRow .= ' selected="selected"';
					
			$formRow .=	'>{s_form:herr}</option>' . "\r\n" . 
						'<option';
					
			if(isset($GLOBALS['_POST']['formOfAddress']) && $GLOBALS['_POST']['formOfAddress'] == parent::$staText['form']['frau']) 
				$formRow .=	' selected="selected"';
				
			$formRow .=	'>{s_form:frau}</option>' . "\r\n" . 
						'</select>' . "\r\n" . 
						'</span>' . "\r\n";
		}
		
		// Titel
		if($this->formFields["title"]) {
		
			$formRow .=	$labelSpanRgt .
						($secLabel == "" ? '<label class="secondLabel ' . $this->labelClass . '" for="title">{s_form:grade}<em></em></label>' . "\r\n" : '') . 
						$fieldSpanRgt .
						'<select name="title" id="title" class="' . $this->fieldClass . '">' . "\r\n" . 
						'<option>---</option>' . "\r\n" . 
						'<option';
						
			if(isset($GLOBALS['_POST']['title']) && $GLOBALS['_POST']['title'] == parent::$staText['form']['dr'])
				$formRow .= ' selected="selected"';
				
			$formRow .=	'>{s_form:dr}</option>' . "\r\n" . 
						'<option';
						
			if(isset($GLOBALS['_POST']['title']) && $GLOBALS['_POST']['title'] == parent::$staText['form']['prof'])
				$formRow .= ' selected="selected"';
				
			$formRow .=	'>{s_form:prof}</option>' . "\r\n" . 
						'<option';
						
			if(isset($GLOBALS['_POST']['title']) && $GLOBALS['_POST']['title'] == parent::$staText['form']['profdr'])
				$formRow .= ' selected="selected"';
				
			$formRow .=	'>{s_form:profdr}</option>' . "\r\n" . 
						'</select>' . "\r\n" . 
						'</span>' . "\r\n";
		}
		
		if($formRow != "")
			$form .=	'<li class="{t_class:formrow}' . ($this->formFormat == "inline" || !$this->formFields["fname"] ? '' : ' {t_class:row}') . '">' . "\r\n" .
						$formRow .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		
		
		$formRow	= "";
		
		// Name
		if($this->formFields["name"]) {
		
			$placeH	= "";
			
			if($this->hideLabels)
				$placeH	= ' placeholder="{s_form:name}"';
			
			$labelSpanLftClose	= '</span>' . "\r\n";
			
			// Falls mit Vorname
			if($this->formFields["fname"]) {
				
				if($this->formFormat == "inline") {
					$secLabel		= ' / <label for="firstname" class="secondLabel">{s_form:firstname}</label>';
					$labelSpanLft	= "";
					$fieldSpanLft	= '<span class="fieldLeft {t_class:col5} {t_class:alpha}">' . "\r\n";
					$labelSpanRgt	= "";
					$fieldSpanRgt	= '<span class="fieldRight {t_class:col5} {t_class:omega}">' . "\r\n";
				}
				else {
					$secLabel 		= "";
					$labelSpanLft	= '<span class="fieldLeft {t_class:halfrowsm} {t_class:alpha}">' . "\r\n";
					$fieldSpanLft	= "";
					$labelSpanRgt	= '<span class="fieldRight {t_class:halfrowsm} {t_class:omega}">' . "\r\n";
					$fieldSpanRgt	= "";
				}
			}
			else {
				$secLabel		= "";
				$labelSpanLft	= "";
				$fieldSpanLft	= "";
				$labelSpanLftClose	= "";
			}
		
			$formRow .=	$labelSpanLft .
						'<label class="' . $this->labelClass . '" for="name">{s_form:name}<em>&#42;</em>' . $secLabel . '</label>' . "\r\n";
			
			$formRow .=	$fieldSpanLft;
			
			if($this->errorName != "")
				$formRow .= $noticeOpenTag . $this->errorName . $noticeCloseTag . "\r\n";
	
			$formRow .=	'<span class="{t_class:inputgroup}">' . "\n" .
						'<span class="{t_class:inputaddon}">' . parent::getIcon("user", "", "", "") . '</span>' . "\n" .
						'<input name="name"' . $placeH . ' type="text" id="name" class="{t_class:input} ' . $this->fieldClass . '" aria-required="true" value="';
						
			isset($GLOBALS['_POST']['name']) ? $formRow .= htmlspecialchars($GLOBALS['_POST']['name']) : '""';
						
			$formRow .=	'" maxlength="50" data-validation="required" data-validation-length="max50" />' . "\r\n" . 
						$labelSpanLftClose .
						'</span>' . "\r\n";
		}
		
		// Vorname
		if($this->formFields["fname"]) {
		
			$placeH	= "";
			
			if($this->hideLabels)
				$placeH	= ' placeholder="{s_form:firstname}"';
		
			$formRow .=	$labelSpanRgt .
						($secLabel == "" ? '<label class="secondLabel ' . $this->labelClass . '" for="firstname">{s_form:firstname}</label>' . "\r\n" : '');
			
			$formRow .=	$fieldSpanRgt;
			
			if($this->errorFirstName != "")
				$formRow .= $noticeOpenTag . $this->errorFirstName . $noticeCloseTag . "\r\n";
	
			$formRow .=	'<input name="firstname"' . $placeH . ' type="text" id="firstname" class="' . $this->fieldClass . '" value="';
						
			isset($GLOBALS['_POST']['firstname']) ? $formRow .= htmlspecialchars($GLOBALS['_POST']['firstname']) : '""';
						
			$formRow .=	'" maxlength="50" />' . "\r\n" . 
						'</span>' . "\r\n";
		}
		
		if($formRow != "") {
			$form .=	'<li class="{t_class:formrow}' . ($this->formFormat == "inline" || !$this->formFields["fname"] ? '' : ' {t_class:row}') . ($this->errorName != "" || $this->errorFirstName != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						$formRow .
						($this->formFormat == "inline" || !$this->formFields["fname"] ? '' : '<br class="clearfloat" />' . "\r\n") .
						'</li>' . "\r\n";
		}
		
	
		// Falls inline form
		if($this->formFormat == "inline") {
			$fieldSpanRgt		= '<span class="fieldRight {t_class:col10} {t_class:omega}">' . "\r\n";
		}
		else {
			$fieldSpanRgt		= '<span class="">' . "\r\n";
		}
		
		// Firma
		if($this->formFields["com"]) {
		
			$placeH	= "";
			
			if($this->hideLabels)
				$placeH	= ' placeholder="{s_form:company}"';
		
			$form .=	'<li class="{t_class:formrow}' . ($this->errorCompany != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<label class="' . $this->labelClass . '" for="company">{s_form:company}</label>' . "\r\n";
			
			$form .=	$fieldSpanRgt;
			
			if($this->errorCompany != "")
				$form .= $noticeOpenTag . $this->errorCompany . $noticeCloseTag . "\r\n";
	
			$form .=	'<span class="{t_class:inputgroup}">' . "\n" .
						'<span class="{t_class:inputaddon}">' . parent::getIcon("building", "", "", "") . '</span>' . "\n" .
						'<input name="company"' . $placeH . ' type="text" id="company" class="{t_class:input} ' . $this->fieldClass . '" value="';
						
			isset($GLOBALS['_POST']['company']) ? $form .= htmlspecialchars($GLOBALS['_POST']['company']) : '""';
						
			$form .=	'" maxlength="50" />' . "\r\n" . 
						'</span>' . "\r\n" .
						'</span>' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		// E-Mail
		if($this->formFields["mail"]) {
		
			$placeH	= "";
			
			if($this->hideLabels)
				$placeH	= ' placeholder="{s_form:email}"';
		
			$form .=	'<li class="{t_class:formrow}' . ($this->errorMail != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<label class="' . $this->labelClass . '" for="email">{s_form:email}<em>&#42;</em></label>' . "\r\n";
			
			$form .=	$fieldSpanRgt;
						
			if($this->errorMail != "")
				$form .= $noticeOpenTag . $this->errorMail . $noticeCloseTag . "\r\n";
						
			$form .=	'<span class="{t_class:inputgroup}">' . "\n" .
						'<span class="{t_class:inputaddon}">' . parent::getIcon("mail", "", "", "") . '</span>' . "\n" .
						'<input name="email"' . $placeH . ' type="' . ($this->html5 ? 'email' : 'text') . '" id="email" class="{t_class:email} {t_class:input} ' . $this->fieldClass . '" aria-required="true" value="';
						
			isset($GLOBALS['_POST']['email']) ? $form .= htmlspecialchars($GLOBALS['_POST']['email']) : '""';
			
			$form .=	'" maxlength="254" data-validation="email" />' . "\r\n" . 
						'</span>' . "\r\n" .
						'</span>' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		// Phone
		if($this->formFields["phone"]) {
		
			$placeH	= "";
			
			if($this->hideLabels)
				$placeH	= ' placeholder="{s_form:phone}"';
		
			$form .=	'<li class="{t_class:formrow}' . ($this->errorPhone != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<label class="' . $this->labelClass . '" for="phone">{s_form:phone}<em>&#42;</em></label>' . "\r\n";
			
			$form .=	$fieldSpanRgt;
						
			if($this->errorPhone != "")
				$form .= $noticeOpenTag . $this->errorPhone . $noticeCloseTag . "\r\n";
						
			$form .=	'<span class="{t_class:inputgroup}">' . "\n" .
						'<span class="{t_class:inputaddon}">' . parent::getIcon("phone", "", "", "") . '</span>' . "\n" .
						'<input name="phone"' . $placeH . ' type="text" id="phone" class="{t_class:input} ' . $this->fieldClass . '" aria-required="true" value="';
						
			isset($GLOBALS['_POST']['phone']) ? $form .= htmlspecialchars($GLOBALS['_POST']['phone']) : '""';
			
			$form .=	'" maxlength="32" data-validation="required" data-validation-length="max32" />' . "\r\n" . 
						'</span>' . "\r\n" .
						'</span>' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		// Betreff
		if($this->formFields["subj"]
		&& !empty($this->formFields["subji"])
		) {
			
			$this->subjectItems	= explode("\r\n", $this->formFields["subji"]);
		
			$form .=	'<li class="{t_class:formrow}">' . "\r\n" . 
						'<label class="' . $this->labelClass . '" for="subject">{s_form:subject}<em></em></label>' . "\r\n";

			$form .=	$fieldSpanRgt;
						
			$form .=	'<select name="subject" id="subject" class="{t_class:select} {t_class:input} ' . $this->fieldClass . '">' . "\r\n" . 
						'<option value="-1">- {s_form:other} -</option>' . "\r\n"; 
			
			foreach($this->subjectItems as $key => $val) {
				
				$form .='<option value="' . $key . '"';
				
				if(isset($GLOBALS['_POST']['subject'])
				&& $GLOBALS['_POST']['subject'] == $key
				)
					$form .= ' selected="selected"';
				
				$form .='>' . $val . '</option>' . "\r\n";
			}
			
			$form .=	'</select>' . "\r\n" . 
						'</span>' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		// Nachricht
		if($this->formFields["mes"]) {
		
			$placeH	= "";
			
			if($this->hideLabels)
				$placeH	= ' placeholder="{s_form:message}"';
		
			$form .=	'<li class="{t_class:formrow}' . ($this->errorMes != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" .
						'<label class="' . $this->labelClass . '" for="message">{s_form:message}<em>&#42;</em></label>' . "\r\n";
						
			$form .=	$fieldSpanRgt;
			
			if($this->errorMes != "")
				$form .= $noticeOpenTag . $this->errorMes . $noticeCloseTag . "\r\n";
						
			$form .=	'<textarea name="message"' . $placeH . ' id="message" class="{t_class:text} {t_class:input} ' . $this->fieldClass . '" aria-required="true" rows="5" cols="30" accept-charset="UTF-8" data-validation="required" data-validation-length="max1800">';
						
			isset($GLOBALS['_POST']['message']) ?  $form .= htmlentities($GLOBALS['_POST']['message'], ENT_QUOTES, 'UTF-8') : '""';
			
			$form .=	'</textarea>' . "\r\n" . 
						'</span>' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		// Kopie an Absender
		if($this->formFields["copy"]) {
			
			if($this->formFormat == "inline") {
				$labelClass			= '{t_class:col10}';
				$fieldSpan			= '<span class="{t_class:col10} {t_class:push2}">' . "\r\n";
			}
			else {
				$labelClass			= "{t_class:label} {t_class:checkbox}";
				$fieldSpan			= '<span class="">' . "\r\n";
			}
		
			$form .=	'<li class="{t_class:formrow}">' . "\r\n";

			$form .=	$fieldSpan;
			
			$form .=	'<div class="{t_class:checkbox}">' . "\r\n";
			
			$form .=	'<label for="copy" class="' . $labelClass . '">' . "\r\n";
						
			$form .=	'<input type="checkbox" name="copy" id="copy" class="{t_class:fieldcheck}" ';
						
			isset($GLOBALS['_POST']['copy']) && $GLOBALS['_POST']['copy'] == "on" ?  $form .= ' checked="checked"' : '""';
			
			$form .=	'/>{s_form:copy}</label>' . "\r\n" . 
						'</div>' . "\r\n" .
						'</span>' . "\r\n" .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		// Captcha
		if($this->formFields["cap"]) {
		
			$placeH	= "";
			
			if($this->hideLabels)
				$placeH	= ' placeholder="{s_form:captcha}"';
		
			if($this->formFormat == "inline") {
				$labelSpanLft	= "";
				$fieldSpanLft	= '<span class="fieldLeft {t_class:col5} {t_class:alpha}">' . "\r\n";
				$labelSpanRgt	= "";
				$fieldSpanRgt	= '<span class="fieldCaptcha fieldRight {t_class:col5} {t_class:omega}">' . "\r\n";
			}
			else {
				$labelSpanLft	= '<span class="fieldLeft {t_class:halfrowsm} {t_class:alpha}">' . "\r\n";
				$fieldSpanLft	= "";
				$labelSpanRgt	= '<span class="fieldCaptcha fieldRight {t_class:halfrowsm} {t_class:omega}">' . "\r\n" .
								  '<label class="' . $this->labelClass . '">&nbsp;</label>' . ($this->hideLabels ? '' : '<br />') . "\r\n";
				
				$fieldSpanRgt	= "";
			}
		
			$form .=	'<li class="{t_class:formrow}' . ($this->formFormat == "inline" ? '' : ' {t_class:row}') . ($this->errorCap != "" ? ' {t_class:fielderror}' : '') . '">' . "\r\n" . 
						$labelSpanLft .
						'<label class="' . $this->labelClass . '" for="captcha-confirm">{s_form:captcha}<em>&#42;</em></label>' . "\r\n";
		
			$form .=	$fieldSpanLft;
			
			if($this->errorCap != "")
				$form .= $noticeOpenTag . $this->errorCap . $noticeCloseTag . "\r\n";
						
			$form .=	'<input name="captcha_confirm"' . $placeH . ' type="text" id="captcha-confirm" class="{t_class:input} ' . $this->fieldClass . '" aria-required="true" data-validation="required" />' . "\r\n" .
						'</span>' . "\r\n" .
						$labelSpanRgt .
						$fieldSpanRgt .
						'<img src="' . PROJECT_HTTP_ROOT . '/access/captcha.php" alt="{s_form:capalt}" title="{s_form:captit}" class="captcha" />' . "\r\n";
	
			// Button caprel
			$btnDefs	= array(	"href"		=> PROJECT_HTTP_ROOT . '/access/captcha.php',
									"text"		=> '',
									"class"		=> 'caprel button-icon-only {t_class:btninf} {t_class:btnsm}',
									"title"		=> '{s_form:capreltit}',
									"attr"		=> 'tabindex="2"',
									"icon"		=> "refresh",
									"icontext"	=> ""
								);
			
			$form .=	parent::getButtonLink($btnDefs);
			
			$form .=	'</span>' . "\r\n" . 
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n";
		}
		
		
		// Falls inline
		if($this->formFormat == "inline") {
			$labelClass			= '{t_class:col10}';
			$fieldSpan			= '<span class="{t_class:col10} {t_class:push2}">' . "\r\n";
		}
		else {
			$labelClass			= $this->labelClass;
			$fieldSpan			= '<span class="">' . "\r\n";
		}
		
		$form .=	'<li class="{t_class:formrow}">' . "\r\n" .
					'<input type="text" name="m-mail" id="m-mail" class="emptyfield" value="" />' . "\r\n";

		$form .=	$fieldSpan;
					
		// Button submit
		$btnDefs	= array(	"type"		=> "submit",
								"name"		=> "contactfm",
								"id"		=> "submit-contactfm",
								"class"		=> '{t_class:btnpri} formbutton ok',
								"value"		=> "{s_button:submit}",
								"icon"		=> "ok"
							);
			
		$form .=	parent::getButton($btnDefs);
		
		$form .=	'<input name="contactfm" type="hidden" value="{s_button:submit}" />' . "\r\n" .
					#'<input name="reset" type="button" id="reset" onClick="fieldRes();" value="{s_button:reset}" class="formbutton reset right" />' . "\r\n" .
					'<input type="hidden" name="token" value="' . parent::$token . '" />' . "\r\n" .
					'<br class="clearfloat" />' . "\r\n" .
					'</span>' . "\r\n" . 
					'</li>' . "\r\n" . 
					'</ul>' . "\r\n" . 
					'</fieldset>' . "\r\n" . 
					'</form>' . "\r\n" .
					'</div>' . "\r\n" .
					'<div class="bottom"></div>' . "\r\n" .
					'</div>' . "\r\n";
		
			
		return ContentsEngine::replaceStaText($form);
	
	}
	
	

	/**
	 * Überprüft die Eingaben des Kontaktformulars
	 * 
     * @access public
     * @return boolean
	 */
	public function checkContactForm()
	{
		
		$checkOK		= true;
		
		// Post-Variablen auslesen
		$this->formOfAddress	= $this->formFields["foa"] ? self::safeText($GLOBALS['_POST']['formOfAddress']) : '';
		$this->title			= $this->formFields["title"] ? self::safeText($GLOBALS['_POST']['title']) : '';
		if($this->title == "---")
			$this->title = "";
		$this->name				= $this->formFields["name"] ? self::safeText($GLOBALS['_POST']['name']) : '';
		$this->firstName		= $this->formFields["fname"] ? self::safeText($GLOBALS['_POST']['firstname']) : '';
		$this->company			= $this->formFields["com"] ? self::safeText($GLOBALS['_POST']['company']) : '';
		$this->email			= $this->formFields["mail"] ? self::safeText($GLOBALS['_POST']['email']) : '';
		$this->phone			= $this->formFields["phone"] ? self::safeText($GLOBALS['_POST']['phone']) : '';
		$this->subject			= $this->formFields["subj"] ? self::safeText($GLOBALS['_POST']['subject']) : '';
		$this->message			= $this->formFields["mes"] ? self::safeText($GLOBALS['_POST']['message']) : '';
		
		$messlg = strlen($this->message); // Nachrichtenlänge auslesen
		
		// Formular auswerten
		// Falls der Testcookie beim Aufruf der Seite nicht gesetzt werden konnte, weil Cookies nicht aktiviert sind...
		if(empty($this->g_Session['captcha']) && (!isset($GLOBALS['_COOKIE']['cookies_on']) || $GLOBALS['_COOKIE']['cookies_on'] != "cookies_on")) {
			// ...zusätzliche Meldung ausgeben
			$this->error	= '{s_error:sessmes}';
			$testCookie		= "alert";
			$checkOK		= false;
		}			

		// ...andernfalls, falls der Cookie gegen Spam gesetzt wurde, Nachricht ausgeben
		if (isset($GLOBALS['_COOKIE']['contact_spam_protection']) && $GLOBALS['_COOKIE']['contact_spam_protection'] == "contact_spam_protection") {
			$this->error   .= '{s_error:spam}';
			$this->showForm = false;
			$checkOK		= false;
		}
			
		// Falls keins der Felder ausgefüllt ist...
		if (empty($this->name) && empty($this->email) && empty($this->message) && empty($GLOBALS['_POST']['captcha_confirm'])) {
			$this->errorEmpty .= '{s_error:fillreq}';
			$checkOK		= false;
		}

		// Falls Name leer ist...
		if ($this->formFields["name"] && empty($this->name)) {
			// ...Meldung ausgeben
			$this->errorName = '{s_error:name}';
			$checkOK		= false;
		}

		// Falls Name zu lang ist...
		elseif ($this->formFields["name"] && strlen($this->name) > 50) {
			// ...Meldung ausgeben
			$this->errorName = '{s_error:nametoolong}';
			$checkOK		= false;
		}
		
		// Falls Vorname zu lang ist...
		if ($this->formFields["fname"] && strlen($this->firstName) > 50) {
			// ...Meldung ausgeben
			$this->errorFirstName = '{s_error:nametoolong}';
			$checkOK		= false;
		}
		
		// Falls Firma zu lang ist...
		if ($this->formFields["com"] && strlen($this->company) > 50) {
			// ...Meldung ausgeben
			$this->errorFirstName = '{s_error:nametoolong}';
			$checkOK		= false;
		}
		
		// ...Falls eine E-Mail Adresse eingegeben wurde, aber leer
		if ($this->formFields["mail"] && $this->email == "") {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorMail = '{s_error:mail1}';
			$checkOK		= false;
		}
		
		// ...Falls eine E-Mail Adresse eingegeben wurde, aber das Format falsch ist...
		elseif ($this->formFields["mail"] && (!filter_var($this->email, FILTER_VALIDATE_EMAIL) ||
			strlen($this->email) > 254)) {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorMail = '{s_error:mail2}';
			$checkOK		= false;
		}
		
		// ...Falls eine Telefonnr. eingegeben wurde, aber leer
		if ($this->formFields["phone"] && $this->phone == "") {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorPhone = '{s_error:phone}';
			$checkOK		= false;
		}
		
		// ...Falls eine Telefonnr. eingegeben wurde, aber fehlerhaft
		elseif ($this->formFields["phone"] && (strlen($this->phone) < 4 || strlen($this->phone) > 32 || !preg_match("/^[0-9 \/+-]{4,32}$/", $this->phone))) {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorPhone = '{s_error:phone}';
			$checkOK		= false;
		}
		
		// ...Falls das Mock-Feld m-mail nicht leer ist...
		if ($GLOBALS['_POST']['m-mail'] != "") {
			// ...dann eine Fehlermeldung ausgeben!
			$this->error = '{s_error:checkform}';
			$checkOK		= false;
		}
		
		// ...Falls keine Nachricht eingegeben wurde...
		if ($this->formFields["mes"] && $this->message == "") {
			// ...dann eine Fehlermeldung ausgeben!
			$this->errorMes = '{s_error:nomes}';
			$checkOK		= false;
		}
		
		// Falls Nachricht zu lang (>1800 Zeichen) ist...
		if ($this->formFields["mes"] && $messlg > 1800) {
			// ...Meldung ausgeben
			// mit Angabe der aktuellen Zeichenanzahl
			$messlgstr		= parent::$staText['error']['messlg'];
			$this->errorMes = str_replace('%zuviel%', $messlg, $messlgstr);
			$checkOK		= false;
		}

		// Falls der Captcha nicht stimmt...
		if($this->formFields["cap"] && (empty($GLOBALS['_POST']['captcha_confirm']) || (trim($GLOBALS['_POST']['captcha_confirm']) == "") || strlen($GLOBALS['_POST']['captcha_confirm']) != 5 || (!empty($this->g_Session['captcha']) && $GLOBALS['_POST']['captcha_confirm'] != $this->g_Session['captcha']))) {
			$this->errorCap = '{s_error:captcha}';
			$checkOK		= false;
		}
		
		
		// Wenn alle Felder ausgefuellt wurden...
		if($checkOK === true) {
			
			if($this->sendForm()) // ...wird die Email abgeschickt
				return "sent";
			else
				return "not sent";
		}
		else {
			
			if($this->error == "")
				$this->error = '{s_error:checkform}';
			
			return false;
		}
				
	}
	
	

	/**
	 * Versendet das Kontaktformular
	 * 
     * @access public
     * @return boolean
	 */
	public function sendForm()
	{
		
		if($this->email == "") $emailLink = "-";
		else $emailLink = "<a href=\"mailto:$this->email\">$this->email</a>";
		
		$mailStatus		= false;
		$mailStatusCopy	= false;
		$mailError		= "";
		$mailError2		= "";
		
		$domain			= str_replace("http://", "", PROJECT_HTTP_ROOT);
		$domain			= str_replace("https://", "", $domain);
		$domain			= str_replace("www.", "", $domain);
		
		$this->subject	= $this->subject == "-1" ? ContentsEngine::replaceStaText("{s_form:other}") : $this->subject;
		
		$subjectPrefix	= htmlspecialchars(ContentsEngine::replaceStaText("{s_form:newcontact}"));
		$mailSubject	= $subjectPrefix . ($this->subject != "" ? ' - ' . $this->subject : '') . ' - ' . $domain;
		$mailSubject	= '=?utf-8?B?'.base64_encode($mailSubject).'?=';
		
		$submitDate		= date("d.m.Y", time());
		$submitTime		= date("H:i", time());
		
		#$IP			= getenv("REMOTE_ADDR");
		
		// Nachricht
		$htmlMail = "
					<html>
						<head>
							<title>{s_form:newcontact} {s_form:concerning} &quot;$this->subject&quot;.</title>
							<style type='text/css'>
								table { border:1px solid #D3D3D3; padding:5px; border-collapse:collapse; }
								tr { vertical-align:top; padding:10px; }
								td { padding: 5px 20px; 5px 10px}
								tr td:first-child { background:#D3D3D3; }
								td.border { border-bottom:1px solid #D3D3D3; }
								td.borderL { border-bottom:1px solid #FFF; }
							</style>
						</head>
						<body>
							<p>{s_form:newcontact} - $domain</p>
							<p>$submitDate {s_text:attime} $submitTime {s_text:clock}</p>
							<hr>
							<table>
							<tr>
							<td>{s_form:author}: </td><td>$this->formOfAddress $this->title $this->firstName <strong>$this->name</strong></td>
							</tr>";
							
		if($this->formFields["com"])
			$htmlMail .= 	"<tr>
							<td>{s_form:company}: </td><td>$this->company</td>
							</tr>";
							
		if($this->formFields["mail"])
			$htmlMail .= 	"<tr>
							<td>{s_form:email}: </td><td>$emailLink</td>
							</tr>";
							
		if($this->formFields["phone"])
			$htmlMail .= 	"<tr>
							<td>{s_form:phone}: </td><td>$this->phone</td>
							</tr>";
							
		if($this->formFields["mes"])
			$htmlMail .= 	"<tr>
							<td>{s_form:message}: </td><td>$this->message</td>
							</tr>";
							
		$htmlMail .= 		"</table>
						</body>
					</html>
					";
		
		// Statische Sprachbausteine ersetzen
		$htmlMail	= ContentsEngine::replaceStaText($htmlMail) . "\n";
		

		// Klasse phpMailer einbinden
		require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
		
		// Instanz von PHPMailer bilden
		$mail = new \PHPMailer();
		
		
		// E-Mail-Parameter für SMTP
		$mail->setMailParameters(SMTP_MAIL, $this->name, CONTACT_EMAIL, $mailSubject, $htmlMail, true, "", "smtp");
		
		// E-Mail senden per phpMailer (SMTP)
		$mailStatus = $mail->Send();
		
		// Falls Versand per SMTP erfolglos, per Sendmail probieren
		if($mailStatus !== true) {
			
			// E-Mail-Parameter für php Sendmail
			$mail->setMailParameters(AUTO_MAIL_EMAIL, $this->name, CONTACT_EMAIL, $mailSubject, $htmlMail, true, "", "sendmail");
			
			// Absenderadresse der Email auf FROM: setzen
			#$mail->Sender = $this->email;		
			
			// E-Mail senden per phpMailer (Sendmail)
			$mailStatus = $mail->Send();
		}
		
		// Falls Versand per Sendmail erfolglos, per mail() probieren
		if($mailStatus !== true) {
			
			// E-Mail-Parameter für php mail()
			$mail->setMailParameters(AUTO_MAIL_EMAIL, $this->name, CONTACT_EMAIL, $mailSubject, $htmlMail, true);
			
			// E-Mail senden per phpMailer (mail())
			$mailStatus = $mail->Send();
		}
		
		// Falls Mailversand erfolgreich
		if($mailStatus === true) {
			
			// Falls gewünscht, Kopie an Absender
			if($this->formFields["copy"] && isset($GLOBALS['_POST']['copy']) && $GLOBALS['_POST']['copy'] == "on") {
		
				// Instanz von PHPMailer bilden
				$mail = new \PHPMailer();
		
				// E-Mail-Parameter für SMTP
				$mail->setMailParameters(SMTP_MAIL, $this->name, $this->email, $mailSubject, $htmlMail, true, "", "smtp");
				
				// E-Mail senden per phpMailer (SMTP)
				$mailStatusCopy = $mail->Send();
				
				// Falls Versand per SMTP erfolglos, per Sendmail probieren
				if($mailStatusCopy !== true) {
					
					// E-Mail-Parameter für php Sendmail
					$mail->setMailParameters(AUTO_MAIL_EMAIL, $this->name, $this->email, $mailSubject, $htmlMail, true, "", "sendmail");
					
					// E-Mail senden per phpMailer (Sendmail)
					$mailStatusCopy = $mail->Send();
				}
				
				// Falls Versand per Sendmail erfolglos, per mail() probieren
				if($mailStatusCopy !== true) {
					
					// E-Mail-Parameter für php mail()
					$mail->setMailParameters(AUTO_MAIL_EMAIL, $this->name, $this->email, $mailSubject, $htmlMail, true);
					
					// E-Mail senden per phpMailer (mail())
					$mailStatusCopy = $mail->Send();
				}
			}
			
			// Falls kein Fehler vorliegt ein Cookie setzen sowie die Variable $mes auf sent gesetzt
			setcookie("contact_spam_protection", "contact_spam_protection", time()+300, '/');
			
			return true;
		}
		else {
			$this->error = '{s_error:mailfail}<br /><br />' . $mail->ErrorInfo;

			return false;
		}
			
	}


}
