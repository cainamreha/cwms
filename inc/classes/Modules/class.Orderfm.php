<?php
namespace Concise;


/**
 * Klasse für Bestellformularerstellung
 *
 */

class Orderfm extends Modules
{
	 
    /**
     * Datenarray mit bestellbaren Artikeln
     *
     * @access public
     * @var    array
     */
    public $queryData = array();

    /** Anzahl an bestellbaren Artikeln
     *
     * @access public
     * @var    int
     */
    public $productCount = 0;

    /** Warenkorbwert
     *
     * @access public
     * @var    string
     */
    public $sumTotal = "";

    /**
     * Bestellformular korrekt ausgefüllt, falls true
     *
     * @access public
     * @var    boolean
     */
    public $checkOrder = false;

    /**
     * Aktueller Schritt beim Bestellformular
     *
     * @access public
     * @var    boolean
     */
    public $orderStep = 1;

    /**
     * Vorheriger Schritt beim Bestellformular
     *
     * @access public
     * @var    boolean
     */
    public $orderPrevStep = 1;

    /**
     * Beschriftung des Zurückbuttons
     *
     * @access public
     * @var    boolean
     */
    public $lftButtonVal = "";

    /**
     * Beschriftung des Weiterbuttons
     *
     * @access public
     * @var    boolean
     */
    public $rgtButtonVal = "";

    /**
     * Icon des Zurückbuttons
     *
     * @access public
     * @var    boolean
     */
    public $lftButtonIco = "";

    /**
     * Icon des Weiterbuttons
     *
     * @access public
     * @var    boolean
     */
    public $rgtButtonIco = "";
  
    /**
     * Besteller Anrede
     *
     * @access public
     * @var    string
     */
    public $formOfAddress = "";
  
    /**
     * Besteller Titel
     *
     * @access public
     * @var    string
     */
    public $title = "";
  
    /**
     * Besteller Name
     *
     * @access public
     * @var    string
     */
    public $name = "";
  
    /**
     * Besteller Vorname
     *
     * @access public
     * @var    string
     */
    public $firstName = "";
  
    /**
     * Besteller Straße
     *
     * @access public
     * @var    string
     */
    public $street = "";
  
    /**
     * Besteller Hausnummer
     *
     * @access public
     * @var    string
     */
    public $number = "";
  
    /**
     * Besteller Postleitzahl
     *
     * @access public
     * @var    string
     */
    public $zipCode = "";
  
    /**
     * Besteller Stadt
     *
     * @access public
     * @var    string
     */
    public $city = "";
  
    /**
     * Besteller Land
     *
     * @access public
     * @var    string
     */
    public $country = "";
  
    /**
     * Besteller Firma
     *
     * @access public
     * @var    string
     */
    public $company = "";
  
    /**
     * Besteller Telefon
     *
     * @access public
     * @var    string
     */
    public $phone = "";
  
    /**
     * Besteller E-Mail
     *
     * @access public
     * @var    string
     */
    public $email = "";
  
    /**
     * Besteller Bemerkung
     *
     * @access public
     * @var    string
     */
    public $comment = "";
  
    /**
     * Bezahlform
     *
     * @access public
     * @var    string
     */
    public $payment = 0;
  
    /**
     * Lieferform
     *
     * @access public
     * @var    string
     */
    public $delivery = 0;
  
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
     * Fehlerarray
     *
     * @access public
     * @var    array
     */
    public $errorOrder = array();

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
    public $errorStreet = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorNumber = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorZipCode = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorCity = "";

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
    public $errorMes = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorCap = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $errorAGBs = "";

    /**
     * Benachrichtigung für den User
     *
     * @access public
     * @var    string
     */
    public $showForm = "";

    /**
     * Bestellformular AGBs-Option
     *
     * @access public
     * @var    boolean
     */
    public $AGBsOpt = false;

    /**
     * Bestellformular AGBs
     *
     * @access public
     * @var    boolean
     */
    public $AGBs = false;

    /**
     * Bestellformular Newsletteroption
     *
     * @access public
     * @var    boolean
     */
    public $newsLetterOpt = false;

    /**
     * Bestellformular Newsletterantrag
     *
     * @access public
     * @var    boolean
     */
    public $newsLetter = false;


	/**
	 * Erstellt ein Bestellformular
	 * 
     * @param	object	$DB			DB object
     * @param	object	$o_lng		lang object
     * @access public
	 */
	public function __construct($DB, $o_lng)
	{
		
		$this->DB		= $DB;
		$this->o_lng	= $o_lng;
		$this->lang		= $this->o_lng->lang;

		
		// Security-Objekt
		$this->o_security	= Security::getInstance();

		// Session-Vars-Array
		$this->g_Session	= Security::getSessionVars();

		// Session-Vars-Array
		$this->g_Post		= $GLOBALS['_POST'];
		
		// Benutzergruppe
		$this->group		= $this->o_security->get('group');
		$this->ownGroups	= $this->o_security->get('ownGroups');
	
	}		


	/**
	 * Erstellt ein Bestellformular
	 * 
     * @param $formType 		public Art des Bestellformulars (z.B. Newsletters/AGBs einfügen; default = 'default')
     * @param $noticeSuccess 	public Zusatzmeldung bei erfolgreicher Bestellung (default = '')
     * @access public
     * @return string
	 */
	public function getOrderForm($formType = "default", $noticeSuccess = "")
	{
	
		// Variablen
		$form				= "";
		$cartValue			= 0;
		$formData			= ""; // Formularfelder mit Daten
		$labelFeed			= array(); // Zeilen-Ausgleich für Fehlermeldung bei benachbarten Feldern
		$countryOptions 	= explode(",", parent::$staText['form']['countries']); // Landesauswahl
		$notice				= "";	
		$success			= false;
		$classCue			= ' {t_class:alert} {t_class:info} {t_class:textinfo}';
		$classActive		= ' active {t_class:alert} {t_class:warning} {t_class:textwarning}';
		$classDone			= ' done {t_class:alert} {t_class:success} {t_class:textsuccess}';
		$btnClass			= '{t_class:btn} {t_class:btndef} {t_class:btnsm}';
		$noticeOpenTag		= '<span class="notice {t_class:texterror}">';
		$noticeCloseTag		= '</span>';
		
		
		// Buttonbeschriftung
		$this->lftButtonVal	= "{s_button:safecart}";
		$this->rgtButtonVal	= "{s_button:ordernext}";
		$this->lftButtonIco	= "ok";
		$this->rgtButtonIco	= "next";
		
		
		// Formulartyp auslesen
		if($formType == "agb" || $formType == "agb-newsl")
			$this->AGBsOpt = true;
		
		if($formType == "newsletter" || $formType == "agb-newsl")
			$this->newsLetterOpt = true;
		
		// Formaction
		$this->formAction	= parent::$currentURL . "#orderForm";
		
	
		// Ggf. Warenkorb löschen
		$this->checkResetCart();

	
		// Artikeldaten einlesen
		$this->getProductDetails();

		
		// Falls die Registrierungsart NICHT auf Shopuser gesetzt ist, nur Meldung ausgeben
		if(REGISTRATION_TYPE != "shopuser" && $this->o_security->get('editorLog'))
			$form .=	'<p>&nbsp;</p><p class="error {t_class:alert} {t_class:info}">{s_notice:noshopuser}&quot;' . REGISTRATION_TYPE . '&quot;</p>';
		
		if(isset($this->g_Session['orderSuccess']) && $this->g_Session['orderSuccess'] === true) {
			$this->unsetSessionKey('orderSuccess');
			$this->showForm = 'no';
			$success	= true;
			$this->orderStep = 5;
			$this->report = '{s_notice:ordersent}';
			
			// Falls eine Zusatzerfolgsmeldung angegeben wurde
			if($noticeSuccess != "")
				$this->report .= '<div class="extraNotice">' . $noticeSuccess . '</div>' . "\r\n";
		}
		
		// Falls das Bestellformular angezeigt wird und gerade ein Artikel in den Warenkorb gelegt wurde, die Warenkorbliste aktualisieren bzw. Seite neu laden
		if(isset($this->g_Post['addToCart'])) {
		
			$form .=	'<form id="orderfm" class="orderfm {t_class:form}" method="post" action="' . $this->formAction . '" data-ajax="false">' . "\r\n" .
						'<fieldset>' . "\r\n" .
						'<input name="order_step1" type="submit" id="submit"  value="{s_button:back} {s_link:tocart}" class="formbutton back alt"/>' . "\r\n" .
						'<input name="order_step2" type="submit" value="{s_button:ordernext}" class="formbutton ok" />' . "\r\n" .
						'</fieldset></form>' . "\r\n";

			return ContentsEngine::replaceStaText($form);
		}
			
		if($this->report != "")
			$notice .=  $this->getNotificationStr($this->report);
		
		if($this->error != "")
			$notice .=	$this->getNotificationStr($this->error, "error");
			

		$form .=	'<div id="orderForm" class="{t_class:col12} {t_class:panel}">' . "\r\n" .
					'<div class="top"></div>' . "\r\n" .
					'<div class="center">' . "\r\n" .
					'<form id="orderfm" class="orderfm {t_class:formhorizon}" method="post" action="' . $this->formAction . '" data-ajax="false">' . "\r\n" .
					'<fieldset id="orderFormHTML">' . "\r\n" . 
					'<legend>{s_form:ordertit}</legend>' . "\r\n";
		
		// Bestellformular-Leitschema
		$formData .=	'<div class="orderFlow {t_class:well}">' . "\r\n" .
						'<ol class="orderFlowList {t_class:pagination}">' . "\r\n" .
						'<li class="orderStep1' . ($this->orderStep == 1 && $this->showForm != 'no' ? $classActive : ($this->orderStep > 1 ? $classDone : $classCue)) . '"><button class="' . $btnClass . '" data-orderstep="1">{s_text:chooseorder}</button></li><li class="separator' . ($this->orderStep > 1 ? $classDone . '"><span>&#x2714;'  : $classCue . '"><span>&raquo;') . '</span></li>' .
						'<li class="orderStep2' . ($this->orderStep == 2 ? $classActive : ($this->orderStep > 2 ? $classDone : $classCue)) . '"><button class="' . $btnClass . '" data-orderstep="2">{s_text:orderdetails}</button></li><li class="separator' . ($this->orderStep > 2 ? $classDone . '"><span>&#x2714;' : $classCue .'"><span>&raquo;') . '</span></li>' .
						'<li class="orderStep3' . ($this->orderStep == 3 ? $classActive : ($this->orderStep > 3 ? $classDone : $classCue)) . '"><button class="' . $btnClass . '" data-orderstep="3">{s_text:orderpayment}</button></li><li class="separator' . ($this->orderStep > 3 ? $classDone . '"><span>&#x2714;' : $classCue .'"><span>&raquo;') . '</span></li>' .
						'<li class="orderstep4' . ($this->orderStep == 4 ? $classActive : ($this->orderStep > 4 ? $classDone : $classCue)) . '"><button class="' . $btnClass . '" data-orderstep="4">{s_text:checkorder}</button></li><li class="separator' . ($this->showForm == 'no' ? $classDone . '"><span>&#x2714;' : $classCue .'"><span>&raquo;') . '</span></li>' .
						'<li class="orderStep5' . ($this->showForm == 'no' ? $classActive : ($this->orderStep > 5 ? $classDone : $classCue)) . '"><button class="' . $btnClass . '" disabled="disabled">{s_text:ordersent}</button></li>' .
						'</ol>' . "\r\n" .
						'</div>' . "\r\n";
						
		if($this->showForm == 'no') { // Falls nur Meldungen ausgegeben werden sollen (z.B. erfolgreicher Versand) Formular nicht anzeigen
			
			$form .= $formData . $notice . '</fieldset></form></div></div>';

			return ContentsEngine::replaceStaText($form);
		}
		
		else { // Andernfalls Formular anzeigen
			
			
			// Meldung einbinden
			$formData .=	$notice;
			
			// Warenkorb einbinden (Schritt 1)
			$formData .=	'<ol' . ($this->orderStep != 1 ? ' style="display:none;"' : '') . '>' . "\r\n" .
							'<h4>{s_text:chooseorder}</h4>' . "\r\n";						
			
			if($this->productCount == 0)
				$formData .=	'<p class="{t_class:alert} {t_class:info}">{s_notice:noproducts}</p>' . "\r\n";
			
			else {
													
				// Artikelauswahl einbinden
				for($j = 1; $j <= MAX_ENTRIES_ORDER_FORM; $j++) {
					
					$formData .='<li class="{t_class:formrow}"' . (MAX_ENTRIES_ORDER_FORM > SHOW_ENTRIES_ORDER_FORM && $j > SHOW_ENTRIES_ORDER_FORM && (!isset($this->g_Post['order'][$j]) || $this->g_Post['order'][$j]["article"] == "none") ? ' style="display:none;"' : '') . '>' . "\r\n" .
								'<label for="order'.$j.'" class="itemLabel {t_class:labelinl} {t_class:col2}">{s_form:orderentry} '.$j.'<em>&nbsp;</em></label>' . "\r\n";
					
					$formData .='<div class="{t_class:col7}">' . "\r\n";
				
					if(isset($this->errorOrder[$j]) && $this->errorOrder[$j] != "") {
						$formData  	  .= $noticeOpenTag . $this->errorOrder[$j] . $noticeCloseTag;
						$labelFeed[$j] = "<span class='notice fill {t_class:formerror} {t_class:block}'>&nbsp;</span>" . "\r\n";
					}
					else
						$labelFeed[$j] = "";
					
					$formData .='<select name="order['.$j.'][article]" id="order'.$j.'" class="shopItems {t_class:field} {t_class:select}">' . "\r\n" . 
								'<option value="none,none" selected="selected">{s_option:choose}</option>' . "\r\n"; 
					
					
					$productGroup	= "";
					$i = 1;
					
					foreach($this->queryData as $product) {
						
						$productGroupOld	= $productGroup;
						$productGroup 		= $product['category_'.$this->lang];
						$productGroupID		= $product['cat_id'];
						$productName		= $product['header_'.$this->lang];
						$productID			= $product['id'];
						$productPrice		= Modules::getPrice($product['price'], $this->lang);
																   
						$inCart = false;
						
						// Bestellsumme ermitteln
						// Falls der Artikel im Warenkorb liegt, den Preis aufsummieren
						if(isset($this->g_Post['order'][$j]) && 
						   $this->g_Post['order'][$j]["catID"] == $productGroupID && 
						   $this->g_Post['order'][$j]["article"] == $productID) {
							
							$inCart = true; // Vorhanden auf true (wird unten gebraucht)
							
							$cartValue += ($this->g_Post['order'][$j]["amount"] * $product['price']);
						}
						
						if($i == 1)
							$formData .='<optgroup label="'.$productGroup . '">' . "\r\n";
							
						elseif($productGroup != $productGroupOld)
							$formData .='</optgroup><optgroup label="'.$productGroup.'">';
						
						$formData .='<option value="' . htmlspecialchars($productGroupID) . "," . htmlspecialchars($productID) . '"' .
									($inCart ? ' selected="selected"' : '') .
									($i % 2 ? '' : ' class="alternate"');
						
						$formData .='>' . htmlspecialchars($productName) . '&nbsp;&nbsp;&nbsp;(' . htmlspecialchars($productPrice) . ' EUR)</option>' . "\r\n";
						
						$i++;
						
					} // Ende foreach
					
					$formData .='</optgroup></select>' . "\r\n" . 
								'</div>' . "\r\n" .
								'<div class="{t_class:col3}">' . "\r\n" .
								$labelFeed[$j] .
								'<label for="amount'.$j.'" class="itemAmountLabel {t_class:labelinl}">{s_form:amount}<em>&nbsp;</em></label>' . "\r\n" . 
								'<span class="itemAmountControls">' .
								'<input name="order['.$j.'][amount]" type="text" id="amount'.$j.'" class="itemAmount numSpinner {t_class:field} {t_class:input}" value="';
								
					isset($this->g_Post['order'][$j]) ? $formData .= htmlspecialchars($this->g_Post['order'][$j]["amount"]) : (isset($this->g_Post['order'][$j]["article"]) && $this->g_Post['order'][$j]["article"] != "none" ? $formData .= '1' : $formData .= '0');
								
					$formData .='" maxlength="3" />' . "\r\n";
			
					// Button setOrderAmount
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "order_step1",
											"class"		=> 'safeChanges {t_class:btnpri} {t_class:btnsm} button-icon-only',
											"value"		=> $j,
											"text"		=> "",
											"title"		=> '{s_title:safechange}',
											"attr"		=> ($this->checkOrder || isset($this->g_Post['order_step2']) ? ' disabled="disabled"' : ''),
											"icon"		=> "ok",
											"icontext"	=> ""
										);
						
					$formData .=	parent::getButton($btnDefs);
			
					// Button deleteItem
					$btnDefs	= array(	"type"		=> "submit",
											"name"		=> "delete_item",
											"class"		=> 'deleteItem {t_class:btndef} {t_class:btnsm} button-icon-only',
											"value"		=> $j,
											"text"		=> "",
											"title"		=> '{s_title:deleteitem}',
											"attr"		=> ($this->checkOrder || isset($this->g_Post['order_step2']) ? ' disabled="disabled"' : ''),
											"icon"		=> "delete",
											"icontext"	=> ""
										);
						
					$formData .=	parent::getButton($btnDefs);
					
					$formData .='</span>' . "\r\n" .
								'</div>' . "\r\n" .
								'</li>' . "\r\n";
								
				} // Ende for j
			
				// Spinner
				$formData .=	$this->getScriptCode();
			
			} // Ende else
			
			$formData .='<br class="clearfloat" />' . "\r\n";
			
			// Button Warenkorb löschen
			$btnDefs	= array(	"href"		=> "?rescart=1",
									"class"		=> 'resetCart {t_class:btndef} {t_class:btnsm} {t_class:right}',
									"text"		=> "{s_title:rescart}&nbsp;",
									"title"		=> '{s_title:rescart}',
									"icon"		=> "delete",
									"icontext"	=> ""
								);
				
			$formData .=	parent::getButtonLink($btnDefs, "right");
			
			// Falls Button Posten hinzufügen angezeigt werden soll
			if(MAX_ENTRIES_ORDER_FORM > SHOW_ENTRIES_ORDER_FORM && $this->productCount > 0) {
			
				// Button addOrderEntry
				$btnDefs	= array(	"type"		=> "button",
										"name"		=> "addOrderEntry",
										"class"		=> 'addOrderEntry {t_class:btninf} {t_class:btnsm}',
										"value"		=> "{s_title:addarticle}",
										"title"		=> '{s_title:addarticle}',
										"icon"		=> "addarticle"
									);
					
				$formData .=	parent::getButton($btnDefs);
			}
			
			// Summe Warenkorb
			$shippingLink	= "{shippingLinkOfm}";
			
			$formData .=	'<br class="clearfloat" />' . "\r\n" .
							'<p class="cartTotal {t_class:alert} {t_class:info}">{s_form:cartvalue} <span class="mwst">({s_text:ustr}, {s_common:plus} ' . $shippingLink . ')</span>: <span class="cartTotal">' . Modules::getPrice($cartValue, $this->lang) . ' EUR</span></p>' . "\r\n";
			
			$formData .=	'</ol>' . "\r\n";
			
			
			// Eingabefelder für Lieferanschrift (Schritt 2)
			$formData .=	'<ul' . ($this->orderStep != 2 ? ' style="display:none;"' : '') . '>' . "\r\n";
			
			$formData .=	'<h4>{s_text:orderdetails}</h4>' . "\r\n";
			
			
			// Falls die Registrierungsart auf Shopuser gesetzt ist und noch kein Benutzer eingewählt ist, Link für Login einbinden
			if(REGISTRATION_TYPE == "shopuser" && !isset($this->g_Session['username']))
				$formData .= '<a class="formbutton button" style="float:right; margin-top:-18px; display:inline;" href="' . PROJECT_HTTP_ROOT . '/login' . PAGE_EXT . '">{s_link:login}</a><p><span class="register">{s_form:regged} <a href="' . PROJECT_HTTP_ROOT . '/login' . PAGE_EXT . '">{s_form:loginnow}</a></span></p><br />' . "\r\n";
						
			
			$formData .=	'<p class="footnote topNote {t_class:alert} {t_class:warning}">{s_form:req}</p>' . "\r\n" .
							'<li class="{t_class:formrow}">' . "\r\n" . 
							'<label for="formofaddress" class="{t_class:labelinl} {t_class:col2}">{s_form:anrede}<em>&#42;</em></label>' . "\r\n" . // Anrede
							'<div class="{t_class:col4}">' . "\r\n" .
							'<select name="formofaddress" id="formofaddress" class="{t_class:field} {t_class:select}" aria-required="true">' . "\r\n" . 
							'<option' . "\r\n";
						
			if($this->formOfAddress === parent::$staText['form']['herr'])
				$formData .= ' selected="selected"';
					
			$formData .=	'>{s_form:herr}</option>' . "\r\n" . 
							'<option';
					
			if($this->formOfAddress === parent::$staText['form']['frau']) 
				$formData .=	' selected="selected"';
				
			$formData .='>{s_form:frau}</option>' . "\r\n" . 
						'</select>' . "\r\n" . 
						'</div>' . "\r\n" .
						'<label class="{t_class:labelinl} {t_class:col2}" for="title">{s_form:grade}<em>&nbsp;</em></label>' . "\r\n" . // Titel
						'<div class="{t_class:col4}">' . "\r\n" .
						'<select name="title" id="title" class="{t_class:field} {t_class:select}">' . "\r\n" . 
						'<option>---</option>' . "\r\n" . 
						'<option';
						
			if($this->title === parent::$staText['form']['dr'])
				$formData .= ' selected="selected"';
				
			$formData .='>{s_form:dr}</option>' . "\r\n" . 
						'<option';
						
			if($this->title === parent::$staText['form']['prof'])
				$formData .= ' selected="selected"';
				
			$formData .='>{s_form:prof}</option>' . "\r\n" . 
						'<option';
						
			if($this->title === parent::$staText['form']['profdr'])
				$formData .= ' selected="selected"';
				
			$formData .='>{s_form:profdr}</option>' . "\r\n" . 
						'</select>' . "\r\n" . 
						'</div>' . "\r\n" .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}">' . "\r\n" .
						'<label class="{t_class:labelinl} {t_class:col2}" for="name">{s_form:name}<em>&#42;</em></label>' . "\r\n"; // Name
			
			$formData .='<div class="{t_class:col4}">' . "\r\n";
			
			if($this->errorName != "")
				$formData .= $noticeOpenTag . $this->errorName . $noticeCloseTag;
	
			$formData .='<input name="name" type="text" id="name" class="{t_class:field} {t_class:input}" aria-required="true" value="' . $this->name . '" maxlength="50" />' . "\r\n" . 
						'</div>' . "\r\n" .
						($this->errorName != "" ? '<span class="notice fill {t_class:block}">&nbsp;</span>' . "\r\n" : '') .
						'<label class="{t_class:labelinl} {t_class:col2}" for="firstname">{s_form:firstname}<em>&nbsp;</em></label>' . "\r\n"; // Vorname
			
			$formData .='<div class="{t_class:col4}">' . "\r\n";
			
			if($this->errorFirstName != "")
				$formData .= $noticeOpenTag . $this->errorFirstName . $noticeCloseTag;
	
			$formData .='<input name="firstname" type="text" id="firstname" class="{t_class:field} {t_class:input}" value="' . $this->firstName . '" maxlength="50" />' . "\r\n" . 
						'</div>' . "\r\n" .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}">' . "\r\n" .
						($this->errorNumber != "" && $this->errorStreet == "" ? '<span class="notice fill {t_class:block}">&nbsp;</span>' . "\r\n" : '') .
						'<label class="{t_class:labelinl} {t_class:col2}" for="street">{s_form:street}<em>&#42;</em></label>' . "\r\n"; // Straße
			
			$formData .='<div class="{t_class:col4}">' . "\r\n";
			
			if($this->errorStreet != "")
				$formData .= $noticeOpenTag . $this->errorStreet . $noticeCloseTag;
	
			$formData .='<input name="street" type="text" id="street" class="{t_class:field} {t_class:input}" aria-required="true" value="' . $this->street . '" maxlength="100" />' . "\r\n" . 
						'</div>' . "\r\n" .
						($this->errorStreet != "" && $this->errorNumber == "" ? '<span class="notice fill {t_class:block}">&nbsp;</span>' . "\r\n" : '') .
						'<label class="{t_class:labelinl} {t_class:col2}" for="number">{s_form:number}<em>&#42;</em></label>' . "\r\n"; // Hausnummer
			
			$formData .='<div class="{t_class:col4}">' . "\r\n";
			
			if($this->errorNumber != "")
				$formData .= $noticeOpenTag . $this->errorNumber . $noticeCloseTag;
	
			$formData .='<input name="number" type="text" id="number" class="{t_class:field} {t_class:input}" aria-required="true" value="' . $this->number . '" maxlength="10" />' . "\r\n" . 
						'</div>' . "\r\n" .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}">' . "\r\n" .
						($this->errorCity != "" && $this->errorZipCode == "" ? '<span class="notice fill {t_class:block}">&nbsp;</span>' . "\r\n" : '') .
						'<label class="{t_class:labelinl} {t_class:col2}" for="zipCode">{s_form:zipcode}<em>&#42;</em></label>' . "\r\n"; // Plz
			
			$formData .='<div class="{t_class:col4}">' . "\r\n";
			
			if($this->errorZipCode != "")
				$formData .= $noticeOpenTag . $this->errorZipCode . $noticeCloseTag;
	
			$formData .='<input name="zipCode" type="text" id="zipCode" class="{t_class:field} {t_class:input}" aria-required="true" value="' . $this->zipCode . '" maxlength="5" />' . "\r\n" . 
						'</div>' . "\r\n" .
						($this->errorZipCode != "" && $this->errorCity == "" ? '<span class="notice fill {t_class:block}">&nbsp;</span>' . "\r\n" : '') .
						'<label class="{t_class:labelinl} {t_class:col2}" for="city">{s_form:city}<em>&#42;</em></label>' . "\r\n"; // Ort
			
			$formData .='<div class="{t_class:col4}">' . "\r\n";
			
			if($this->errorCity != "")
				$formData .= $noticeOpenTag . $this->errorCity . $noticeCloseTag;
	
			$formData .='<input name="city" type="text" id="city" class="{t_class:field} {t_class:input}" aria-required="true" value="' . $this->city . '" maxlength="100" />' . "\r\n" . 
						'</div>' . "\r\n" .
						'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}">' . "\r\n" .
						'<label class="{t_class:labelinl} {t_class:col2}" for="country">{s_form:country}<em>&#42;</em></label>' . "\r\n" . // Landesauswahl
						'<div class="{t_class:col10}">' . "\r\n" .
						'<select name="country" id="country" class="{t_class:field} {t_class:select}" aria-required="true">' . "\r\n";
						
			foreach($countryOptions as $countryOpt) {
				$formData .='<option value="' . $countryOpt . '"' . "\r\n";
						
			if($this->country == $countryOpt)
				$formData .= ' selected="selected"';
					
			$formData .=	'>' . $countryOpt . '</option>' . "\r\n";
			}
			
			$formData .='</select>' . "\r\n" . 
						'</div>' . "\r\n" .
						'</li>' . "\r\n" .
						'<li class="{t_class:formrow}">' . "\r\n" .
						'<label class="{t_class:labelinl} {t_class:col2}" for="company">{s_form:company}</label>' . "\r\n"; // Firma
			
			$formData .='<div class="{t_class:col10}">' . "\r\n";
			
			if($this->errorCompany != "")
				$formData .= $noticeOpenTag . $this->errorCompany . $noticeCloseTag;
	
			$formData .='<input name="company" type="text" id="company" class="{t_class:field} {t_class:input}" value="' . $this->company . '" maxlength="50" />' . "\r\n" . 
						'</div>' . "\r\n" . 
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}">' . "\r\n" .
						'<label class="{t_class:labelinl} {t_class:col2}" for="phone">{s_form:phone2}</label>' . "\r\n"; // Telefon
			
			$formData .='<div class="{t_class:col10}">' . "\r\n";
			
			if($this->errorPhone != "")
				$formData .= $noticeOpenTag . $this->errorPhone . $noticeCloseTag;
	
			$formData .='<input name="phone" type="text" id="phone" class="{t_class:field} {t_class:input}" value="' . $this->phone . '" maxlength="50" />' . "\r\n" . 
						'</div>' . "\r\n" . 
						'</li>' . "\r\n" . 
						'<li class="{t_class:formrow}">' . "\r\n" .
						'<label class="{t_class:labelinl} {t_class:col2}" for="email">E-Mail<em>&#42;</em></label>' . "\r\n"; // E-Mail
			
			$formData .='<div class="{t_class:col10}">' . "\r\n";
			
			if($this->errorMail != "")
				$formData .= $noticeOpenTag . $this->errorMail . $noticeCloseTag;
						
			$formData .='<input name="email" type="text" id="email" class="{t_class:field} {t_class:input}" aria-required="true" value="' . $this->email . '" maxlength="254" />' . "\r\n" . 
						'<input type="text" name="m-mail" id="m-mail" class="emptyfield" value="" />' . "\r\n" . // Mock field
						'</div>' . "\r\n";
						'</li>' . "\r\n";
			
			$formData .='<li class="{t_class:formrow}">' . "\r\n" .
						'<label class="{t_class:labelinl} {t_class:col2}" for="message">{s_form:comment}</label>' . "\r\n"; // Bemerkung
			
			$formData .='<div class="{t_class:col10}">' . "\r\n";
						
			if($this->errorMes != "")
				$formData .= $noticeOpenTag . $this->errorMes . $noticeCloseTag;
						
			$formData .='<textarea name="message" id="message" class="{t_class:field} {t_class:text}" rows="1" cols="30" accept-charset="UTF-8">' . $this->comment . '</textarea>' . "\r\n" . 
						'</div>' . "\r\n" . 
						'</li>' . "\r\n" . 
						'<br class="clearfloat" />' . "\r\n" .
						'</ul>' . "\r\n";
			
			#$form .=	$formData;
						
			
			// Eingabefelder für Bezahlart und Lieferform einbinden (Schritt 3)
			$formData .='<ul' . ($this->orderStep != 3 ? ' style="display:none;"' : '') . '>' . "\r\n";
			
			// Bezahlart
			$formData .='<h4>{s_text:orderpayment}</h4>' . "\r\n" .						
						'<p class="footnote topNote">{s_form:req}</p>' . "\r\n" .
						'<li class="{t_class:formrow}">' . "\r\n" . 
						'<label class="{t_class:labelinl} {t_class:col2}">{s_form:payment}<em>&#42;</em></label>' . "\r\n" . // Anrede
						'<ul class="subList {t_class:col10}">' . "\r\n" . 
						'<li class="{t_class:rowradio} {t_class:block}">' .
						'<label for="payment1"><input type="radio" name="payment" id="payment1" class="{t_class:radio}" aria-required="true" value="{s_form:payment1}"' . ($this->payment == 0 || $this->payment === parent::$staText['form']['payment1'] ? ' checked="checked"' : '') . ' />{s_form:payment1} {s_form:payment1b}</label></li>' . "\r\n" .
						'<li class="{t_class:rowradio} {t_class:block}">' .
						'<label for="payment2"><input type="radio" name="payment" id="payment2" class="{t_class:radio}" aria-required="true" value="{s_form:payment2}"' . ($this->country != $countryOptions[0] ? ' disabled="disabled"' : ($this->payment === parent::$staText['form']['payment2'] ? ' checked="checked"' : '')) . ' />' .
						'{s_form:payment2} {s_form:payment2b}</label></li>' . "\r\n" .
						'</ul></li><p>&nbsp;</p>' . "\r\n";
			
			// Lieferform
			$formData .='<li class="{t_class:formrow}">' . "\r\n" . 
						'<label class="{t_class:labelinl} {t_class:col2}">{s_form:delivery}<em>&#42;</em></label>' . "\r\n" . // Anrede
						'<ul class="subList {t_class:col10}">' . "\r\n" . 
						'<li class="{t_class:rowradio} {t_class:block}">' .
						'<label for="delivery1" class="delivery1"><input type="radio" name="delivery" id="delivery1" class="{t_class:radio}" aria-required="true" value="{s_form:delivery1}"' . ($this->delivery == 0 || $this->delivery === parent::$staText['form']['delivery1'] ? ' checked="checked"' : '') . ' />' .
						'{s_form:delivery1}</label></li>' . "\r\n" .
						'<li class="{t_class:rowradio} {t_class:block}">' .
						'<label for="delivery2" class="delivery2"><input type="radio" name="delivery" id="delivery2" class="{t_class:radio}" aria-required="true" value="{s_form:delivery2}"' . ($this->delivery === parent::$staText['form']['delivery2'] ? ' checked="checked"' : '') . ' />' .
						'{s_form:delivery2}</label></li>' . "\r\n" .
						'<li class="{t_class:rowradio} {t_class:block}">' .
						'<label for="delivery3" class="delivery3"><input type="radio" name="delivery" id="delivery3" class="{t_class:radio}" aria-required="true" value="{s_form:delivery3}"' . ($this->delivery === parent::$staText['form']['delivery3'] ? ' checked="checked"' : '') . ' />' .
						'{s_form:delivery3}</label></li>' . "\r\n";
									
			$formData .='</ul>' . "\r\n" . 
						'</li>' . "\r\n" . 
						'<br class="clearfloat" />' . "\r\n" .
						'</ul>' . "\r\n";
			
			$form .=	$formData;
			
			
			// Falls die Daten überprüft oder bestätigt werden sollen (Schritt 4)
			if($this->orderStep == 4) {
				
				$form .=	'<h4>{s_text:checkorder}</h4>' . "\r\n" .
							'<div class="orderHint {t_class:alert} {t_class:warning}">{s_form:orderhint}</div>' . "\r\n" .
							'<div class="orderDetails">' . "\r\n" .
							$this->getOrderDetails("form") .
							'</div>' . "\r\n";
				
				$form .=	'<p class="footnote">{s_form:req}</p>' . "\r\n" .
							'<ul>' . "\r\n";
				
				if($this->AGBsOpt) {
					$form .='<li class="{t_class:rowcheckbox}">' .
							'<label for="agb" class="{t_class:label} {t_class:3thirds}">' . "\n";
								
					if($this->errorAGBs != "")
						$form .= $noticeOpenTag . $this->errorAGBs . $noticeCloseTag;
									
					$form .='<input name="agb" id="agb" class="agb {t_class:checkbox}" type="checkbox"' . ($this->AGBs ? ' checked="checked"' : '') . ' />{s_label:agb}<em>*</em><br />{s_text:agb} {agbsLinkOfm} | {shippingLinkOfm}<br /><br /></label>' . "\n" .
							'</li>' . "\r\n";
				}
				
				if($this->newsLetterOpt)
					$form .='<li class="{t_class:rowcheckbox}"><label for="newsl" class="{t_class:label} {t_class:3thirds}">' . "\n" . 
							'<input name="newsletter" id="newsl" class="newsletter {t_class:checkbox}" type="checkbox"' . ($this->newsLetter ? ' checked="checked"' : '') . ' />{s_label:newsl}<br /><br /></label>' . "\n" .
							'</li>' . "\r\n";
							
				$form .=	'<li class="{t_class:formrow}">' . "\r\n" . 
							'<label class="{t_class:labelinl} {t_class:col2}" for="captcha_confirm">{s_form:captcha}<em>&#42;</em></label>' . "\r\n";
				
				$form .=	'<div class="{t_class:col4}">' . "\r\n";
				
				if($this->errorCap != "" && $this->orderStep == $this->orderPrevStep)
					$form .= $noticeOpenTag . $this->errorCap . $noticeCloseTag;
							
				$form .=	'<input name="captcha_confirm" type="text" id="captcha_confirm" class="{t_class:input} {t_class:field}" aria-required="true" />' . "\r\n" . 
							'</div>' . "\r\n" . 
							'<span class="captchaBox {t_class:col6}">' . "\r\n" .
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
				
				$form .=	parent::getButtonLink($btnDefs);
				
				$form .=	'</span>' . "\r\n" . 
							'</li>' . "\r\n";
							
				$form .=	'<br class="clearfloat" />' . 
							'<p class="orderHint alt {t_class:alert} {t_class:info}">{s_form:orderhint2}</p>' . "\r\n";
					
			}
		
			// Buttons einbinden
			$form .=	'<br />' . 
						'<ul><li class="submitPanel">' . "\r\n";
			
			// Button back
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> 'order_step' . ($this->orderStep > 1 ? ($this->orderStep -1) : 1),
									"class"		=> 'formBack {t_class:btnsec}',
									"value"		=> $this->lftButtonVal,
									"icon"		=> $this->lftButtonIco
								);
			
			$form .=	parent::getButton($btnDefs);
			
			
			$this->rgtButtonVal = $this->rgtButtonIco == "next" ? $this->rgtButtonVal . "&nbsp;" : "&nbsp;" . $this->rgtButtonVal;
			
			// Button submit
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> 'order_step' . ($this->orderStep == 4 ? $this->orderStep : ($this->orderStep +1)),
									"class"		=> ($this->orderStep == 4 ? ' finalSubmit ' : '') . ' {t_class:btnpri} {t_class:right}',
									"value"		=> $this->rgtButtonVal,
									"icon"		=> $this->rgtButtonIco,
									"icontext"	=> $this->rgtButtonIco == "next" ? "" : "&nbsp;"
								);
				
			$form .=	parent::getButton($btnDefs, ($this->rgtButtonIco == "next" ? "right" : "left"));
			
			$form .=	'<input type="hidden" name="newsletter" value="' . ($this->newsLetter ? 'on' : '') . '" />' . "\r\n";

			// Versteckte Felder und Fuß
			$form .=	'<input type="hidden" name="orderPrevStep" value="' . ($this->orderStep) . '" />' . "\r\n" . 
						'<input type="hidden" name="orderFormSubmit" value="true" />' . "\r\n";

			// Token einfügen bei letztem Schritt
			if($this->orderStep == 4)
				$form .=	parent::getTokenInput();

			$form .=	'<br class="clearfloat" />' . "\r\n" .
						'</li>' . "\r\n" . 
						'</ul>' . "\r\n" . 
						'</fieldset>' . "\r\n" . 
						'</form>' . "\r\n";
						
			$form .=	'</div>' . "\r\n" .
						'<div class="bottom"></div>' . "\r\n" .
						'</div>' . "\r\n";
			

			// Links zu Versandkosten und AGBs
			$form .=	'<ul class="shopInfo {t_class:fullrow}">' . "\r\n" .
						'<li>{agbsLinkOfm}</li>' . "\r\n" .
						'<li>{shippingLinkOfm}</li>' . "\r\n" .
						'</ul>' . "\r\n";
						
			return ContentsEngine::replaceStaText($form);
			
		} // Ende else: Formular anzeigen

	}
	
	
	/**
	 * Löscht falls gewünscht den Warenkorb
	 * 
     * @access public
     * @return string
	 */
	public function checkResetCart()
	{
	
		// Falls der Warenkorb gelöscht werden soll
		if(isset($GLOBALS['_GET']['rescart']) && $GLOBALS['_GET']['rescart'] == "1") {
			$this->unsetSessionKey('cart');
			header("Location:" . parent::$currentURL);
			exit;
		}
	
	}		
	
	
	/**
	 * Löscht falls gewünscht den Warenkorb
	 * 
     * @access public
     * @return string
	 */
	public function getProductDetails()
	{

		// db-Query nach Artikeldaten
		$this->queryData = $this->DB->query("SELECT * 
											FROM `" . DB_TABLE_PREFIX . "articles` AS dt 
											LEFT JOIN `" . DB_TABLE_PREFIX . "articles_categories` AS dct 
											ON dt.`cat_id` = dct.`cat_id` 
											WHERE 
											published = 1 
											AND (`group` = 'public'
											OR FIND_IN_SET('" . $this->DB->escapeString($this->group) . "', `group`)" . ContentsEngine::getOwnGroupsQueryStr($this->ownGroups) . ") 
											AND `order_opt` = 1 
											ORDER BY `header_" . $this->DB->escapeString($this->lang) . "`
											", false);
					
		#var_dump($this->queryData);
		
		if(!is_array($this->queryData)
		|| count($this->queryData) == 0
		)
			return false;
		
		
		$this->productCount = count($this->queryData);


		// Falls die Registrierungsart auf Shopuser gesetzt ist und ein Benutzer eingewählt ist, Benutzer-/Adressdaten auslesen
		if(REGISTRATION_TYPE == "shopuser" && isset($this->g_Session['username'])) {
			
			// Benutzername
			$loggedUserDb = $this->DB->escapeString($this->g_Session['username']);
			
			// Benutzerdaten auslesen
			$loggedUserQuery = $this->DB->query("SELECT * 
														FROM `" . DB_TABLE_PREFIX . "user` 
														WHERE `username` = '$loggedUserDb' 
														");
			#var_dump($loggedUserQuery);
			
			if(is_array($loggedUserQuery)
			&& count($loggedUserQuery) == 1
			) {
				
				// Benutzerdaten des geloggten Benutzers
				$this->formOfAddress	= Modules::safeText($loggedUserQuery[0]['gender']);
				if($this->formOfAddress == "f")
					$this->formOfAddress = parent::$staText['form']['frau'];
				else
					$this->formOfAddress = parent::$staText['form']['herr'];
				$this->title			= Modules::safeText($loggedUserQuery[0]['title']);
				$this->name				= Modules::safeText($loggedUserQuery[0]['last_name']);
				$this->firstName		= Modules::safeText($loggedUserQuery[0]['first_name']);
				$this->streetNr			= explode(" ", $loggedUserQuery[0]['street']);
				$this->number			= Modules::safeText(array_pop($this->streetNr));
				$this->street			= Modules::safeText(implode(" ", $this->streetNr));
				$this->zipCode			= Modules::safeText($loggedUserQuery[0]['zip_code']);
				$this->city				= Modules::safeText($loggedUserQuery[0]['city']);
				$this->country			= Modules::safeText($loggedUserQuery[0]['country']);
				$this->company			= Modules::safeText($loggedUserQuery[0]['company']);
				$this->email			= Modules::safeText($loggedUserQuery[0]['email']);
				$this->phone			= Modules::safeText($loggedUserQuery[0]['phone']);
						
			}			
		}		
		

		// Falls das Bestellformular abgeschickt wurde, die Warenkorbliste aktualisieren und leere Felder löschen (Index neu sortieren)
		if(isset($this->g_Post['order'])) {
			
			$tempArray				= array();
			$tempArray[0]			= array("catID" => "none", "article" => "none", "amount" => 0);
			$totAmount				= 0;
			$this->orderPrevStep	= $this->g_Post['orderPrevStep'];
			
			
			// Post-Parameter auslesen
			$this->formOfAddress	= self::safeText($this->g_Post['formofaddress']);
			$this->title			= self::safeText($this->g_Post['title']);
			if($this->title == "---")
				$this->title = "";
			$this->name				= self::safeText($this->g_Post['name']);
			$this->firstName		= self::safeText($this->g_Post['firstname']);
			$this->street			= self::safeText($this->g_Post['street']);
			$this->number			= self::safeText($this->g_Post['number']);
			$this->zipCode			= self::safeText($this->g_Post['zipCode']);
			$this->city				= self::safeText($this->g_Post['city']);
			$this->country			= self::safeText($this->g_Post['country']);
			$this->company			= self::safeText($this->g_Post['company']);
			$this->phone			= self::safeText($this->g_Post['phone']);
			$this->email			= self::safeText($this->g_Post['email']);
			$this->comment			= htmlentities($this->g_Post['message'], ENT_QUOTES, "UTF-8");
			$this->payment			= self::safeText($this->g_Post['payment']);
			$this->delivery			= self::safeText($this->g_Post['delivery']);
			
			
			$this->setSessionVar('cart', array()); // Session-Warenkorb neu anlegen
			
			
			// Bestellposten zählen
			$entryCount = 0;
			
			// Bestellartikel auslesen
			foreach($this->g_Post['order'] as $key => $orderList) {
				
				$articleData	= explode(",", $orderList['article']);
				$catID			= $articleData[0];
				$itemID			= $articleData[1];
			
				// Falls ein Artikel aus dem Warenkorb gelöscht werden soll, die itemID auf "none" setzen und nicht in array schreiben
				if(isset($this->g_Post['delete_item']) && $this->g_Post['delete_item'] == $key) {
					$itemID = "none";
 					$this->g_Post['order_step1'] = true;
				}
				
				if($itemID != "none") {
				
					$amount = floor($orderList['amount']);
					$tempArray[] = array("catID" => $catID, "article" => $itemID, "amount" => $amount);
					$totAmount += $amount;
					
					if(!isset($this->g_Session['cart'][$catID])) {
						$this->g_Session['cart'][$catID] = array(	'ID' => array($itemID),
																	'amount' => array($amount)
																);						
						$entryCount++; // Bestellposten zählen
					}
					else {
						if(in_array($itemID, $this->g_Session['cart'][$catID]['ID'])) {
							
							$cartID = array_keys($this->g_Session['cart'][$catID]['ID'], $itemID);
							$this->g_Session['cart'][$catID]['amount'][$cartID[0]] += $amount;
						}
						elseif($entryCount < MAX_ENTRIES_ORDER_FORM) {
							$this->g_Session['cart'][$catID]['ID'][]		= $itemID;
							$this->g_Session['cart'][$catID]['amount'][]	= $amount;
							$entryCount++; // Bestellposten zählen
						}
					}
				}
				
			}
			
			$this->g_Session['cart']['totArticles'] = $totAmount;
			
			$countOrder = count($tempArray);
			
			$this->g_Post['order'] = $tempArray;
		
			// Add cart to session
			$this->setSessionVar("cart", $this->g_Session['cart']);
		
			for($f = $countOrder; $f <= MAX_ENTRIES_ORDER_FORM; $f++) {
				$this->g_Post['order'][$f] = array("catID" => "none", "article" => "none", "amount" => 0);
			}					
		}
		
		// Andernfalls evtl. Sessiondaten (Warenkorb) für das Bestellformular auslesen
		elseif(isset($this->g_Session['cart'])) {
			
			$tempArray = array();
			$tempArray[0] = array("catID" => "none", "article" => "none", "amount" => 0);
			$countOrder = 1;
			
			foreach($this->g_Session['cart'] as $catID => $orderData) {
				
				if($catID != "totArticles") {
					
					$countOrder = count($orderData['ID']) +1;
					
					foreach($orderData['ID'] as $key => $itemID) {
						$tempArray[] = array("catID" => $catID, "article" => $itemID, "amount" => floor($orderData['amount'][$key]));
					}
					
				}
				
			}
			
			// Post-Array mit Bestellposten
			$this->g_Post['order'] = $tempArray;
			
			// Leere Bestellposten auffüllen
			for($f = ($countOrder); $f <= MAX_ENTRIES_ORDER_FORM; $f++) {
				$this->g_Post['order'][$f] = array("catID" => "none", "article" => "none", "amount" => 0);
			}
			
			$this->checkOrderForm(1);
		}
		
				
		// Falls Bezahlform gewählt ist
		if(isset($this->g_Post['payment']))
			$this->payment = $this->g_Post['payment'];
			
				
		// Falls Versandart gewählt ist
		if(isset($this->g_Post['delivery']))
			$this->delivery = $this->g_Post['delivery'];
		
		
		// Falls AGB-Checkbox gecheckt ist
		if(isset($this->g_Post['agb']) && $this->g_Post['agb'] == "on")
			$this->AGBs = true;
			
				
		// Falls Newsletteroption gecheckt ist
		if(isset($this->g_Post['newsletter']) && $this->g_Post['newsletter'] == "on")
			$this->newsLetter = true;
			
				
		// Falls Formular abgeschickt wurde, Auswertung starten (Schritt 1)
		if(isset($this->g_Post['orderFormSubmit'])) {
		
			$this->orderStep = 1;
			
			// Bestellposten überprüfen
			if(!$this->checkOrderForm(1)) {
				$this->error = '{s_error:checkform}';
				return false;
			}
		
		}
		// Falls Formular abgeschickt wurde, Auswertung starten (Schritt 2)
		if(isset($this->g_Post['order_step2'])) {
			
			$this->orderStep = 2;

			$this->lftButtonVal	= "{s_button:modorder}";
			$this->lftButtonIco	= "prev";
			$this->rgtButtonIco	= "next";
				
			if(!$this->checkOrderForm(1)) { // Bestellposten überprüfen. Falls Fehler, zu Schritt 1
				$this->orderStep	= 1;
				$this->lftButtonVal	= "{s_button:safecart}";
				unset($this->g_Post['order_step2']);
				$this->g_Post['order_step1'] = "true";
				$this->error = '{s_error:checkform}';
			}
			
			elseif($this->g_Post['order_step2'] != parent::$staText['button']['ordernext'] && $this->g_Post['order_step2'] != parent::$staText['button']['finishorder'] && $this->g_Post['order_step2'] != 'edit') {
				
				if($this->checkOrderForm(2) == true && !isset($this->g_Post['modify'])) { // Falls die Bestellung korrekt ist und nicht mehr geändert werden soll
					$this->checkOrder = true;
				}
				else {
					$this->error = '{s_error:checkform}';
				}

			}
				
		}
		// Falls Formular abgeschickt wurde, Auswertung starten (Schritt 3)
		if(isset($this->g_Post['order_step3'])) {
			
			$this->orderStep = 3;

			$this->lftButtonVal	= "{s_button:back}";
			$this->rgtButtonVal	= "{s_button:safecheck}";
			$this->lftButtonIco	= "prev";
			$this->rgtButtonIco	= "next";
			
			if(!$this->checkOrderForm(2)) { // Bestellposten überprüfen. Falls Fehler, zu Schritt 2
				$this->orderStep = 2;
				$this->lftButtonVal	= "{s_button:modorder}";
				$this->rgtButtonVal	= "{s_button:ordernext}";
				$this->lftButtonIco	= "prev";
				$this->rgtButtonIco	= "next";
				unset($this->g_Post['order_step3']);
				$this->g_Post['order_step2'] = "true";
				$this->error = '{s_error:checkform}';
			}			
			
			elseif($this->g_Post['order_step3'] != parent::$staText['button']['ordernext'] && $this->g_Post['order_step3'] != parent::$staText['button']['finishorder'] && $this->g_Post['order_step3'] != 'edit') {
				
				if($this->checkOrderForm(3) == true && !isset($this->g_Post['modify'])) { // Falls die Bestellung korrekt ist und nicht mehr geändert werden soll
					$this->checkOrder = true;
				}
				else {
					$this->error = '{s_error:checkform}';
				}

			}
		}
		// Falls die Bestellung bestätigt wurde, E-Mails abschicken (Schritt 4)
		if(isset($this->g_Post['order_step4'])) {
			
			$this->orderStep = 4;
				
			$this->lftButtonVal	= "{s_button:back}";
			$this->rgtButtonVal	= "{s_button:submitorder}";
			$this->lftButtonIco	= "prev";
			$this->rgtButtonIco	= "ok";

			if($this->checkOrderForm(4) == "sent") {
				
				$this->unsetSessionKey('cart');
				$this->setSessionVar('orderSuccess', true);
				header("Location:" . parent::$currentURL);
				exit;
			}
			else {
				$this->checkOrder = true;
				
				if($this->error == "" && $this->orderPrevStep == $this->orderStep)
					$this->error = '{s_error:checkform}';
			}
		
		}

	}
	
	
	/**
	 * Überprüft die Eingaben des Bestellformulars
	 * 
	 * @param integer $step = Schritt für die Auswertung (1 = ausfüllen, 2 = Bestätigung/abschicken)
     * @access public
     * @return boolean
	 */
	public function checkOrderForm($step)
	{
		
		$checkOK		= true;
		$orderEntries	= 0;
	
		// ...wenn ein Fehler aufgetreten ist und keine Nachricht versendet wurde, Meldung ausgeben
		// Falls der Testcookie beim Aufruf der Seite nicht gesetzt werden konnte, weil Cookies nicht aktiviert sind...
		if(empty($this->g_Session['captcha']) && (!isset($GLOBALS['_COOKIE']['cookies_on']) || $GLOBALS['_COOKIE']['cookies_on'] != "cookies_on")) {
			// ...zusätzliche Meldung ausgeben
			$this->error = '{s_error:sessmes}';
			$testCookie = "alert";
			$checkOK = false;
		}			
		
		for($j = 1; $j <= MAX_ENTRIES_ORDER_FORM; $j++) {
			
			if(isset($this->g_Post['order']) && $this->g_Post['order'][$j]["article"] != "none") {
				
				$orderEntries++;
				
				if(trim($this->g_Post['order'][$j]["amount"]) <= 0
				|| trim($this->g_Post['order'][$j]["amount"]) == ""
				|| !is_numeric(trim($this->g_Post['order'][$j]["amount"]))
				|| strlen(trim($this->g_Post['order'][$j]["amount"])) > 3
				|| trim($this->g_Post['order'][$j]["amount"]) > 999
				) {
					$this->errorOrder[$j] = "{s_error:amount}";
					$checkOK = false;
				}

			}
									
		} // Ende for j
		// Falls keins der Felder ausgefüllt ist...
		if ($orderEntries == 0) {
			$this->errorOrder[1] = '{s_error:chooseproduct}';
			$checkOK = false;
		}
		
		
		// Fehler bei step 1
		if(!$checkOK) {
			$this->orderStep = 1;
			return false;
		}

		
		// Bestellerdaten-Überprüfung
		if($step > 1) {
			
			
			$messlg = strlen($this->comment); // Nachrichtenlänge auslesen
			
			// Falls Name leer ist...
			if (empty($this->name)) {
				// ...Meldung ausgeben
				$this->errorName = '{s_error:name}';
				$checkOK = false;
			}
	
			// Falls Name zu lang ist...
			elseif (strlen($this->name) > 50) {
				// ...Meldung ausgeben
				$this->errorName = '{s_error:nametoolong}';
				$checkOK = false;
			}
			
			// Falls Vorname zu lang ist...
			if (strlen($this->firstName) > 50) {
				// ...Meldung ausgeben
				$this->errorFirstName = '{s_error:nametoolong}';
				$checkOK = false;
			}
			
			// Falls Straße leer ist...
			if (empty($this->street)) {
				// ...Meldung ausgeben
				$this->errorStreet = '{s_error:street}';
				$checkOK = false;
			}
	
			// Falls Straße zu lang ist...
			elseif (strlen($this->street) > 100) {
				// ...Meldung ausgeben
				$this->errorStreet = '{s_error:nametoolong}';
				$checkOK = false;
			}
			
			// Falls Hausnummer leer ist...
			if (strlen($this->number) > 10 || !preg_match("/^[0-9a-z \-\+]{1,10}$/i", $this->number)) {
				// ...Meldung ausgeben
				$this->errorNumber = '{s_error:number}';
				$checkOK = false;
			}
			
			// Falls Postleitzahl zu lang ist...
			if (!preg_match("/^[0-9]{4,5}$/", $this->zipCode)) {
				// ...Meldung ausgeben
				$this->errorZipCode = '{s_error:zipcode}';
				$checkOK = false;
			}
			
			// Falls Stadt leer ist...
			if (empty($this->city)) {
				// ...Meldung ausgeben
				$this->errorCity = '{s_error:city}';
				$checkOK = false;
			}
	
			// Falls Stadt zu lang ist...
			elseif (strlen($this->city) > 100) {
				// ...Meldung ausgeben
				$this->errorCity = '{s_error:nametoolong}';
				$checkOK = false;
			}
			
			// Falls Firma zu lang ist...
			if (strlen($this->company) > 50) {
				// ...Meldung ausgeben
				$this->errorFirstName = '{s_error:nametoolong}';
				$checkOK = false;
			}
			
			// Falls Telefon falsch ist...
			if ($this->phone != "" && !preg_match("/^[0-9 \-\+\/()]+$/", $this->phone)) {
				// ...Meldung ausgeben
				$this->errorPhone = '{s_error:phone}';
				$checkOK = false;
			}
			
			// ...Falls keine E-Mail Adresse eingegeben wurde...
			if ($this->email == "") {
				// ...dann eine Fehlermeldung ausgeben!
				$this->errorMail = '{s_error:mail1}';
				$checkOK = false;
			}
			
			// ...Falls eine E-Mail Adresse eingegeben wurde, aber das Format falsch ist...
			elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL) ||
				strlen($this->email) > 254) {
				// ...dann eine Fehlermeldung ausgeben!
				$this->errorMail = '{s_error:mail2}';
				$checkOK = false;
			}
			
			// ...Falls das Mock-Feld m-mail nicht leer ist...
			if ($this->g_Post['m-mail'] != "") {
				// ...dann eine Fehlermeldung ausgeben!
				$this->error = '{s_error:checkform}';
				$checkOK = false;
			}
			
			// Falls Nachricht zu lang (>1800 Zeichen) ist...
			if ($messlg > 1800) {
				// ...Meldung ausgeben
				// mit Angabe der aktuellen Zeichenanzahl
				$messlgstr = parent::$staText['error']['messlg'];
				$this->errorMes = str_replace('%zuviel%', $messlg, $messlgstr);
				$checkOK = false;
			}
		
			// Fehler bei step 1
			if(!$checkOK) {
				$this->orderStep = 2;
				return false;
			}
			
			// Falls AGBs vorhanden und nicht gecheckt...
			if($step == 4 && $this->AGBsOpt == true && (!isset($this->g_Post['agb']) || $this->g_Post['agb'] != "on")) {
				$this->errorAGBs = '{s_error:agb}';
				$checkOK = false;
			}
			
			// Falls der Captcha nicht stimmt...
			if($step == 4 && (empty($this->g_Post['captcha_confirm']) || (trim($this->g_Post['captcha_confirm']) == "") || strlen($this->g_Post['captcha_confirm']) != 5 || (!empty($this->g_Session['captcha']) && $this->g_Post['captcha_confirm'] != $this->g_Session['captcha']))) {
				$this->errorCap = '{s_error:captcha}';
				$checkOK = false;
			}
		
		} // Ende if Step > 1
		
		// Wenn alle Felder ausgefuellt wurden...
		if($checkOK === true) {
			
			if($step == 4) {
				if($this->sendOrderForm()) // ...wird die Email abgeschickt, falls Schritt drei
					return "sent";
				else
					return "not sent";
			}
			else
				return true; // ...sonst true
		}
		else
			return false;

	}
	
	

	/**
	 * Überprüft die Eingaben des Bestellformulars
	 * 
     * @access public
     * @return boolean
	 */
	public function getOrderDetails()
	{
	
		// POST-Parameter auslesen
		$orderList		= "";
		$sum			= 0;
		$btnEditStep1	= "";
		$btnEditStep2	= "";
		$btnEditStep3	= "";
		
		// bestellte Artikel
		for($j = 1; $j <= MAX_ENTRIES_ORDER_FORM; $j++) {
			
			$catID		= $this->g_Post['order'][$j]["catID"];
			$itemID		= $this->g_Post['order'][$j]["article"];
			$amount		= (int)$this->g_Post['order'][$j]["amount"];
			
			if($itemID != "none") {
				
				foreach($this->queryData as $products) {
					
					$productCatID = $products['cat_id'];
					$productID = $products['id'];
					
					if($productCatID == $catID && $productID == $itemID) {
						$productName = $products['header_'.$this->lang];
						$price = (float)$products['price'];
						$price = round(($price * $amount), 2);
						$sum += $price;
						$price = Modules::getPrice($price, $this->lang);
					}
					
															   					
					
				} // Ende foreach
				
				$orderList .=	'<tr' . ($j%2 ? '' : ' class="alternate"') . '>' . "\r\n" .
								'<td class="orderList">{s_text:product} ' . $j . '</td><td class="amount">' . self::safeText($amount) . ' x </td><td>' . self::safeText($productName) . '</td><td class="price">' . self::safeText($price) . ' EUR</td>' . "\r\n" .
								'</tr>' . "\r\n";
			
			}
			
		} // Ende for j
		
		// Zeischensumme
		$subTotal				= Modules::getPrice($sum, $this->lang) . " EUR";
		
		// Versandkosten (unter Freigrenze)
		$shipping				= trim((float)str_replace(",", ".", SHIPPING_CHARGES));
		$shippingLimit			= trim((float)str_replace(",", ".", SHIPPING_CHARGES_LIMIT));
		
		
		// Falls die Versandkosten aus der Datei für statische Sprachbausteine entnommen werden sollen
		// Auskommentieren, falls der Preis für 
		$shippingFeesDHL	= explode(",", parent::$staText['form']['shippingdhl']); // Versandkosten DHL
		$shippingFeesUPS	= explode(",", parent::$staText['form']['shippingups']); // Versandkosten UPS
		$shippingFeesHermes	= explode(",", parent::$staText['form']['shippinghermes']); // Versandkosten Hermes
		$countryOptions 	= explode(",", parent::$staText['form']['countries']); // Länder für die Landesauswahl

		for($i = 0; $i < count($countryOptions); $i++) {
			
			// Versandkosten für das aktuelle Land bestimmen
			if(trim($countryOptions[$i]) == $this->country) {
			
				if(parent::$staText['form']['delivery1'] === $this->delivery)
					$shipping	= trim((float)$shippingFeesDHL[$i]); // Versandkosten DHL
				if(parent::$staText['form']['delivery2'] === $this->delivery)
					$shipping	= trim((float)$shippingFeesUPS[$i]); // Versandkosten UPS
				if(parent::$staText['form']['delivery3'] === $this->delivery)
					$shipping	= trim((float)$shippingFeesHermes[$i]);	// Versandkosten Hermes
			}
		}
		
		// Falls eine Versandkostengrenze besteht und diese überschritten wird und
		// das Land aus der Anschrift das Hauptland (Deutschland/[0]) ist, Versandkosten auf 0 sezten
		if(trim(SHIPPING_CHARGES_LIMIT) != "" && $sum >= $shippingLimit && trim($countryOptions[0]) == $this->country) {
			$shipping			= Modules::getPrice(0.00, $this->lang) . " EUR";
		}
		// Andernfalls Versandkosten hinzurechnen
		else {
			$sum				+= $shipping;
			$shipping			= Modules::getPrice($shipping, $this->lang) . " EUR";
		}
		
		$sum					= Modules::getPrice($sum, $this->lang) . " EUR";
		$this->sumTotal			= $sum;

		
		if($this->email == "") $emailLink = "-";
		else $emailLink = "<a href=\"mailto:$this->email\">$this->email</a>";

		
		// Buttons (nur bei "form")
		if($this->checkOrder) {
		
			// Button edit 1
			$btnDefs	= array(	"type"		=> "button",
									"id"		=> 'order_step1',
									"class"		=> 'edit cart {t_class:btninf} {t_class:btnsm} button-icon-only {t_class:right}',
									"title"		=> '{s_button:change}',
									"attr"		=> 'data-orderstep="1"',
									"icon"		=> "pencil",
									"icontext"	=> ""
								);
				
			$btnEditStep1	=	parent::getButton($btnDefs);
			
			// Button edit 2
			$btnDefs	= array(	"type"		=> "button",
									"id"		=> 'order_step2',
									"class"		=> 'edit cart {t_class:btninf} {t_class:btnsm} button-icon-only {t_class:right}',
									"title"		=> '{s_button:change}',
									"attr"		=> 'data-orderstep="2"',
									"icon"		=> "pencil",
									"icontext"	=> ""
								);
				
			$btnEditStep2	=	parent::getButton($btnDefs);
			
			// Button edit 3
			$btnDefs	= array(	"type"		=> "button",
									"id"		=> 'order_step3',
									"class"		=> 'edit cart {t_class:btninf} {t_class:btnsm} button-icon-only {t_class:right}',
									"title"		=> '{s_button:change}',
									"attr"		=> 'data-orderstep="3"',
									"icon"		=> "pencil",
									"icontext"	=> ""
								);
				
			$btnEditStep3	=	parent::getButton($btnDefs);
		}
		
		
		// Bestelldetails
		$details = "	<table class='orderDetails {t_class:table} {t_class:tablestr}'>
							<thead>
								<tr>
									<th colspan='4'>{s_text:purchaser}" . $btnEditStep2 . "</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>{s_form:address}</td><td>&nbsp;</td><td colspan='3'>$this->formOfAddress $this->title $this->firstName $this->name</td>
								</tr>
								<tr>
									<td>&nbsp;</td><td>&nbsp;</td><td colspan='3'>$this->street $this->number<br />$this->zipCode $this->city<br />$this->country</td>
								</tr>
								<tr>
									<td>{s_form:company}</td><td>&nbsp;</td><td colspan='3'>$this->company</td>
								</tr>
								<tr>
									<td>{s_form:phone}</td><td>&nbsp;</td><td colspan='3'>$this->phone</td>
								</tr>
								<tr>
									<td>{s_form:email}</td><td>&nbsp;</td><td colspan='3'>$emailLink</td>
								</tr>
								<tr>
									<td>{s_form:comment}</td><td>&nbsp;</td><td colspan='3'>" . nl2br($this->comment) . "</td>
								</tr>
								</tbody>
							<thead>
								<tr>
									<th colspan='4'>{s_text:order}" . $btnEditStep1 . "</th>
								</tr>
							</thead>
							<tbody>
								$orderList
								<tr class='subTotal'>
									<td>{s_form:subtotal}</td><td class='subTotal' colspan='3'>$subTotal</td>
								</tr>
								<tr class='shipping'>
									<td>{s_form:shipping}</td><td class='shipping' colspan='3'>$shipping</td>
								</tr>
								<tr class='sum {t_class:lead} {t_class:info}'>
									<td class='{t_class:bginf}'>{s_form:sum}</td><td class='sum {t_class:bginf}' colspan='3'>$sum</td>
								</tr>
							</tbody>
							<thead>
								<tr>
									<th colspan='4'>{s_text:orderpayment}" . $btnEditStep3 . "</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>{s_form:payment}</td><td>&nbsp;</td><td colspan='3'>$this->payment</td>
								</tr>
								<tr>
									<td>{s_form:delivery}</td><td>&nbsp;</td><td colspan='3'>$this->delivery</td>
								</tr>
							</tbody>
						</table>
						";

		return $details;
		
	}
	
	

	/**
	 * Versendet die Bestellung
	 * 
     * @access public
     * @return boolean
	 */
	public function sendOrderForm()
	{
		
		// Bestellzusammenfassung
		$orderDetails = $this->getOrderDetails("mail");
		
		$mailStatus	= false;
		$mailStatus_2	= false;
				
		// Name der aktuellen Domain
		$domain		= str_replace("http://", "", PROJECT_HTTP_ROOT);
		$domain		= str_replace("www.", "", $domain);
		
		// Betreffs
		$subject_1	= ContentsEngine::replaceStaText("{s_text:neworder} ") . $domain;
		$subject_2	= ContentsEngine::replaceStaText("{s_text:ordersubject} ") . $domain;
		
		// Bestelldatum und -uhrzeit
		$orderDate	= date("d.m.Y", time());
		$orderTime	= date("H:i", time());
		
		// Bestellnummer (unix timestring)
		$orderNumber	= time();
		
		$orderText	= "<p>{s_text:ordertext}</p>";
		
		// Bestelltextzusatz - Kontoinformationen falls Vorkasse (Überweisung)
		if($this->payment === parent::$staText['form']['payment1'])
			$orderText	.= "<p>{s_text:paymenttext} " . $orderNumber . "</p>";
		
		
		#$IP			= getenv("REMOTE_ADDR");
		
		// Bestellnachricht für Betreiber
		$htmlMail_1 = "
					<html>
						<head>
							<title>Order &gt; &quot;$subject_1&quot;.</title>
							<style type='text/css'>
								table { border:1px solid #D3D3D3; padding:5px; border-collapse:collapse; }
								tr { vertical-align:top; padding:10px; }
								th { color:#fff; text-align:left; padding:2px 5px; background-color:#AEA4AE; }
								td { padding: 5px 20px; 5px 10px}
								tr td:first-child { background:#D3D3D3; }
								td.border { border-bottom:1px solid #D3D3D3; }
								td.borderL { border-bottom:1px solid #FFF; }
								td.orderList { max-width:50px; }
								td.price { text-align:right; }
								td.shipping, td.subTotal, td.sum { text-align:right; }
								td.sum { font-weight:bold; }
							</style>
						</head>
						<body>
							<p>{s_text:neworder} $domain.</p>
							<p>{s_text:orderdate}: <strong>$orderDate</strong> {s_text:attime} $orderTime {s_text:clock}</p>
							<p>{s_form:ordernumber}: <strong>$orderNumber</strong></p>
							</tr>
							<hr>
							$orderDetails
						</body>
					</html>
					";
					
		// Bestellnachricht für Besteller
		$htmlMail_2 = "
					<html>
						<head>
							<title>Order &gt; &quot;$subject_2&quot;.</title>
							<style type='text/css'>
								table { border:1px solid #D3D3D3; padding:5px; border-collapse:collapse; }
								tr { vertical-align:top; padding:10px; }
								th { color:#fff; text-align:left; padding:2px 5px; background-color:#AEA4AE; }
								td { padding: 5px 20px; 5px 10px}
								tr td:first-child { background:#D3D3D3; }
								td.border { border-bottom:1px solid #D3D3D3; }
								td.borderL { border-bottom:1px solid #FFF; }
								td.orderList { max-width:50px; }
								td.shipping, td.subTotal, td.sum { text-align:right; }
								td.sum { font-weight:bold; }
							</style>
						</head>
						<body>
							<p>{s_text:formal} $this->formOfAddress $this->title $this->name,</p>
							<p>&nbsp;</p>
							$orderText
							<p>&nbsp;</p>
							<p>{s_text:orderinfo}</p>
							<hr>
							<p>{s_text:orderdate}: $orderDate {s_text:attime} $orderTime {s_text:clock}</p>
							<p>{s_form:ordernumber}: $orderNumber</p>
							<hr>
							$orderDetails
							<p>&nbsp;</p>
							<p class='footText'>{s_text:orderfoot}</p>
						</body>
					</html>
					";
		
		
		// Stat. Sprachbausteinen ersetzen
		$htmlMail_1 = ContentsEngine::replaceStaText($htmlMail_1) . "\n";
		$htmlMail_2 = ContentsEngine::replaceStaText($htmlMail_2) . "\n";
		
		// UTF8 Subject
		$subject_1	= '=?utf-8?B?'.base64_encode($subject_1).'?=';
		$subject_2	= '=?utf-8?B?'.base64_encode($subject_2).'?=';

		
		// Klasse phpMailer einbinden
		require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
		require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.smtp.php');
		
		// Instanz von PHPMailer bilden
		$mail = new \PHPMailer();
		
		
		// E-Mail-Parameter für SMTP
		$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, ORDER_EMAIL, $subject_1, $htmlMail_1, true, "", "smtp");
		
		// E-Mail senden per phpMailer (SMTP)
		$mailStatus = $mail->send();
		
		// Falls Versand per SMTP erfolglos, per mail() probieren
		if($mailStatus !== true) {
			
			// E-Mail-Parameter für php mail()
			$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, ORDER_EMAIL, $subject_1, $htmlMail_1, true, "", "sendmail");
			
			// E-Mail senden per phpMailer (mail())
			$mailStatus = $mail->send();
		}
		
		// Falls Versand per SMTP erfolglos, per mail() probieren
		if($mailStatus !== true) {
			
			// E-Mail-Parameter für php mail()
			$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, ORDER_EMAIL, $subject_1, $htmlMail_1, true);
			
			// E-Mail senden per phpMailer (mail())
			$mailStatus = $mail->send();
		}
		
		// E-Mail senden per phpMailer und falls sie versandt wurde, eine 2. E-Mail an Besteller senden
		if($mailStatus === true) {
		
			// Empfänger-Adresse der 1. E-Mail löschen
			$mail->ClearAddresses();
			
			// Absenderadresse der Email auf FROM: setzen
			$mail->Sender = AUTO_MAIL_EMAIL;		
			
			// E-Mail-Parameter für SMTP
			$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $this->email, $subject_2, $htmlMail_2, true, "", "smtp");
			
			// E-Mail senden per phpMailer (SMTP)
			$mailStatus_2 = $mail->send();
			
			// Falls Versand per SMTP erfolglos, per mail() probieren
			if($mailStatus_2 !== true) {
				
				// E-Mail-Parameter für php mail()
				$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $this->email, $subject_2, $htmlMail_2, true, "", "sendmail");
				
				// E-Mail senden per phpMailer (mail())
				$mailStatus_2 = $mail->send();
			}
			
			// Falls Versand per SMTP erfolglos, per mail() probieren
			if($mailStatus_2 !== true) {
				
				// E-Mail-Parameter für php mail()
				$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $this->email, $subject_2, $htmlMail_2, true);
				
				// E-Mail senden per phpMailer (mail())
				$mailStatus_2 = $mail->send();
			}
			
			
			// Bestellung in DB zählen
			// db-Tabelle sperren
			$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "articles`");
			
			
			// bestellte Artikel
			foreach($this->g_Post['order'] as $order) {
				
				$itemID		= $order["article"];
				
				if($itemID != "none") {
						
					$catID		= $this->DB->escapeString($order["catID"]);
					$itemID		= $this->DB->escapeString($itemID);
					$amount		= $this->DB->escapeString((int)$order["amount"]);
	
					$updateSQL = $this->DB->query(	"UPDATE `" . DB_TABLE_PREFIX . "articles`
														 SET `orders` = `orders` + $amount
														 WHERE `cat_id` = $catID
														 AND `id` = $itemID
														");
		
					#var_dump($updateSQL);
				
				}
				
			} // ende foreach
			
			// db-Sperre aufheben
			$unLock = $this->DB->query("UNLOCK TABLES");
				
			
			// Falls Newsletteroption gecheckt ist, Subscriber eintragen
			if($this->newsLetterOpt && $this->newsLetter) {
			
				// db-Tabelle sperren
				$lock = $this->DB->query("LOCK TABLES `" . DB_TABLE_PREFIX . "user`");
					
				$emailDb	= $this->DB->escapeString($this->email);
				$authCode	= md5(uniqid(time()));
	
				// Überprüfung auf E-Mail
				$checkUser = $this->DB->query("SELECT `username` FROM `" . DB_TABLE_PREFIX . "user` 
													WHERE `email` = '$emailDb'
													");
				
				if(count($checkUser) > 0) {
					
					// Eintrag in DB
					$addSubscr = $this->DB->query("UPDATE `" . DB_TABLE_PREFIX . "user` 
														SET `newsletter` = 1
														WHERE `email` = '$emailDb'
														");
					
					#var_dump($addSubscr);
				}
				else {
						
					// Eintrag in DB
					$addSubscr = $this->DB->query("INSERT INTO `" . DB_TABLE_PREFIX . "user` 
															(`username`,
															`group`,
															`email`,
															`newsletter`,
															`auth_code`
															)
														VALUES 
															('$emailDb',
															 'subscriber',
															 '$emailDb',
															 1,
															 '$authCode',
															)
														ON DUPLICATE KEY UPDATE `newsletter` = 1
														");
					#var_dump($addSubscr);
				}
				
				// db-Sperre aufheben
				$unLock = $this->DB->query("UNLOCK TABLES");
					
			} // ende if newsletter
			
			// Falls nur die E-Mail an den Benutzer nicht erfolgreich versandt wurde, Meldung ausgeben
			if($mailStatus_2 !== true)
				$this->error = '{s_error:confmailfail}<br /><br />' . $mail->ErrorInfo;
			
			return $mailStatus;
		}
		else {
			$this->error = '{s_error:orderfail}<br /><br />' . $mail->ErrorInfo;

			return false;
		}
			
	}
	
	

	/**
	 * Erstellt eine Warenkorbanzeige
	 * 
     * @param string	$targetPage Zielseite mit Bestellformular
     * @param string	$type Darstellungsart (default = "default")
     * @param boolean	$count Legt fest ob Artikelpost gezählt werden soll (default = true)
	 * @access public
     * @return boolean
	 */
	public function getCart($targetPage, $type = "default", $count = true)
	{
		
		// Falls ein Artikekl in den Warenkorb gelegt werden soll
		if(isset($this->g_Post['addToCart']) && $count === true) {
		
			$this->addToCart($this->g_Post); // Artikel zum Warenkorb hinzufügen
		}
		
		$totArticles = 0;
		
		if(isset($this->g_Session['cart']['totArticles']))
			$totArticles = $this->g_Session['cart']['totArticles'];
		
		// Btn cart text
		$btnText =		'<span class="toCart" title="{s_title:cartno} ' . $totArticles . '">{s_link:tocart}</span>' . "\r\n" .
						'<span class="{t_class:badge}" title="{s_title:cartno} ' . $totArticles . '">' . $totArticles . '</span>' . "\r\n";
						
		
		// Button link
		$btnDefs	= array(	"href"		=> $targetPage,
								"class"		=> 'siteLink toCart {t_class:btnsec} {t_class:btnblock}',
								"text"		=> $btnText,
								"title"		=> "{s_title:tocart}",
								"attr"		=> 'rel="nofollow"',
								"icon"		=> "cart"
							);
			
		$btnCart	=	parent::getButtonLink($btnDefs);
		
		$output =		'<div class="cart {t_class:row}">' . "\r\n" .
						'<form id="orderfm" class="orderfm {t_class:form}" action="' . $targetPage . '" method="post" data-ajax="false">' . "\r\n" .
						'<div class="currentCart {t_class:btngroupv} {t_class:fullrow}" role="group">' . "\r\n" .
						$btnCart;
						
		
		// Falls der Button zum Abschließen der Bestellung angezeigt werden soll und sich Artikel im Warenkorb befinden
		if($type == "default" && $totArticles > 0) {
		
			
			// Button finishorder
			$btnDefs	= array(	"type"		=> "submit",
									"name"		=> "order_step2",
									"class"		=> 'cart {t_class:btnpri} {t_class:btnlg} {t_class:btnblock}',
									"value"		=> "{s_button:finishorder}",
									"text"		=> "{s_button:finishorder}",
									"title"		=> '{s_button:finishorder}',
									"icon"		=> "ok"
								);
				
			$btnFinishOrder =	parent::getButton($btnDefs);
			
			$output .=	$btnFinishOrder;
		}

		
		$output .=		'</div>' . "\r\n" .
						'</form>' . "\r\n" .
						'</div>' . "\r\n";
		
		
		return $output ;
	
	}
	
	

	/**
	 * Legt einen Artikel in den Warenkorb
	 * 
	 * @access public
     * @return boolean
	 */
	public function addToCart($a_Post)
	{
	
		if(empty($a_Post))
			return false;
		
		
		
		// Warenkorb
		$addArticleCat	= $a_Post['cat_id'];
		$addArticleID	= $a_Post['data_id'];
		$amount			= $a_Post['amount'];
		$a_cart			= array();
		
		// Falls Cart noch nicht vorhanden, Cart anlegen
		if(!isset($this->g_Session['cart']))
			$a_cart		= array("totArticles" => 0);
		else {
		
			$a_cart		= $this->g_Session['cart'];
			
			// Bestellposten zählen
			$entryCount = 0;
			
			foreach($a_cart as $key => $cartItem) {
				if($key != "totArticles")
					$entryCount += count($cartItem["ID"]);
			}
		}
		// Warenkorb (in Artikelgruppen-Arrays)
		if(isset($a_cart[$addArticleCat])) {
				
			if(in_array($addArticleID, $a_cart[$addArticleCat]['ID'])) {
				
				$cartID = array_keys($a_cart[$addArticleCat]['ID'], $addArticleID);
				$a_cart[$addArticleCat]['amount'][$cartID[0]] += $amount;
			}
			elseif($entryCount < MAX_ENTRIES_ORDER_FORM) {
				$a_cart[$addArticleCat]['ID'][]		= $addArticleID;
				$a_cart[$addArticleCat]['amount'][]	= $amount;
			}
			else { // Falls die maximale Anzahl an Bestellposten ausgeschöpft ist, Meldung ausgeben
				$orderFull = true;
			}
		}
		else {
			
			$a_cart[$addArticleCat]['ID'][]		= $addArticleID;
			$a_cart[$addArticleCat]['amount'][]	= $amount;
		}
		if(isset($orderFull))
			$this->notice = "{s_notice:cartfull}";
		else {
			$a_cart['totArticles'] += $amount; // Gesamtzahl an Artikeln im Warenkorb
			$this->notice = "{s_notice:addtocart}";
		}
		
		self::$o_mainTemplate->poolAssign["notice"] = $this->getNotificationStr($this->notice); // Dem Hauptinhalt Meldung hinzufügen
		
		// Add cart to session
		$this->setSessionVar("cart", $a_cart);
	
	}
	
	
	// getScriptCode
	protected function getScriptCode()
	{
	
		// Number spinner
		$output	= $this->getNumSpinner(".numSpinner", 0, 999, $this->themeConf);
		
		return $output;
	
	}

}
