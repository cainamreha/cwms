<?php
namespace Concise;



/**
 * Klasse FormMailer
 *
 * Versendet Formulardaten per E-Mail
 * 
 * 
 */

class FormMailer
{
	
	//Datenbank-Objekt
	private $DB = null;

	//String mit tabellarischen Formulardaten
	private $formData;

	//Haupt-E-Mailadressen
	private $recipients = array();

	//E-Mailadressen von Cc-Empfängern
	private $recipientsCC = array();

	//E-Mailadressen von Bcc-Empfängern
	private $recipientsBCC = array();

	//E-Mailadressen von Cc-Empfängern
	private $mailHTMLCC;

	//E-Mailadressen von Bcc-Empfängern
	private $mailHTMLBCC;

	//E-Mail-Betreff
	private $subject;

	//pdf
	private $pdf = false;

	//Fehlermeldung
	private $error = false;


	/**
	 * Konstruktor der Klasse
	 * 
	 * @param array		formData		String mit tabellarischen Daten (Felder/Werte) aus einem Formular
	 * @param array		recipients		Quellenangabe für die Benutzer E-Mail
	 *									(e.g. user->aus db-Tabelle user oder form->aus Formfeld zu spezifizieren in config; default = array("user"))
	 * @param array		recipientsCC	E-Mail eines Cc-Empfängers für die E-Mail (default = '')
	 * @param array		recipientsBCC	E-Mail eines Bcc-Empfängers für die E-Mail (default = '')
	 * @param varchar	mailSubject		E-Mail eines cc-Empfängers für die E-Mail (default = '')
	 * @param varchar	mailSubject		Betreff für den E-Mail-Versand (default = '')
	 * @param boolen	pdf				Legt fest ob von den Formulardaten eine pdf-Datei generiert werden soll (default = false)
	 */
	public function __construct($DB, $formData, $recipients, $recipientsCC, $recipientsBCC, $mailSubject, $pdf = false)
	{
		
		// Datenbank-Objekt
		$this->DB		= $DB;
		
		// Datenstring mit Formulardaten holen
		$this->formData = $formData;
		
		// Haupt-E-Mailadresse
		$this->recipients = $recipients;
		
		// E-Mailadresse von cc-Empfänger
		$this->recipientsCC = $recipientsCC;
		
		// E-Mailadresse von cc-Empfänger
		$this->recipientsBCC = $recipientsBCC;
		
		// E-Mail-Betreff
		$this->subject	= Modules::safeText(ContentsEngine::replaceStaText($mailSubject));
		
		// Erstellen von Inhalten als pdf
		$this->pdf		= $pdf;
			
	}	
	
	
	/**
	 * assembleRecipients
	 * 
	 * @param string		user		username (default = '')
	 * @return boolean		Ergebnis der Wertüberprüfung
	 */
	public function assembleRecipients($user = "")
	{	
		
		//Falls die Formularinhalte an einen eingeloggten Benutzer gehen sollen
		if($this->recipients[0] == "user" && $user != "") {
			
			$userDB	= $this->DB->escapeString($user);
			
			$userQuery = $this->DB->query(	"SELECT `email` 
											   		FROM `" . DB_TABLE_PREFIX . "user` 
													WHERE `username` = '$userDB'
													");
			
			#var_dump($userQuery);
			
			//Benutzer-E-Mail aus DB
			if(count($userQuery) == 1)				
				$this->recipients[0] = $userQuery[0]['email'];
				
		}
		//Andernfalls, falls die Formularinhalte an E-Mail(s) aus dem Formular gehen sollen
		elseif($this->recipients[0] == "form" && isset($this->recipients[1]))
			$mailSource	= array_shift($this->recipients);

		
		//Falls keine gültige E-Mail, Fehlermeldung aktivieren
		if(!filter_var($this->recipients[0], FILTER_VALIDATE_EMAIL))
			$this->error = true;
	
	}
	
	
	/**
	 * Ruft den Check auf.
	 * 
	 * @param varchar		formTitle		Formulartitel
	 * @param boolean		mailPDF			Falls true, wird die pdf-Datei aus den Formulardaten an die E-Mail angehängt (default = false)
	 * @param string		pdfName			Beinhaltet den Namen für die pdf-Datei
	 * @param string		pdfMailAttach	Beinhaltet die pdf-Datei aus den Formulardaten
	 * @return boolean		Ergebnis der Wertüberprüfung
	 */
	public function sendForm($formTitle, $mailPDF, $pdfName, $pdfMailAttach)
	{
		
		//Falls Fehler im Constructor gesetzt wurde
		if($this->error === true)
			return false;
		
		
		// Klasse phpMailer einbinden
		require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.phpMailer.php');
		require_once(PROJECT_DOC_ROOT . '/inc/classes/phpMailer/class.smtp.php');
		
		// Instanz von PHPMailer bilden
		$mail = new \PHPMailer();
		
		$htmlMail		= $this->getMailHTML($formTitle); //Message
		$htmlMailCC		= $this->mailHTMLCC; //Message Cc
		$htmlMailBCC	= $this->mailHTMLBCC; //Message Bcc

		if(strpos($this->subject, "{domain}") !== false) {
			$domain			= str_replace(array("http://", "https://", "www."), "", PROJECT_HTTP_ROOT);
			$this->subject	= str_replace("{domain}", $domain, $this->subject);
		}
		
		
		//E-Mail-Empfänger-Adressen
		if(!empty($this->recipientsCC)) {
			foreach($this->recipientsCC as $ccAdd) {
				$mail->addCC($ccAdd);
			}
		}
		if(!empty($this->recipientsBCC)) {
			foreach($this->recipientsBCC as $bccAdd) {
				$mail->addBCC($bccAdd);
			}
		}

		// Falls die pdf-Datei per E-Mail versandt werden soll
		if($mailPDF) {
		
			$mail->addStringAttachment($pdfMailAttach, $pdfName);
			
		}

		
		// E-Mail-Parameter für SMTP
		$mail->setMailParameters(SMTP_MAIL, AUTO_MAIL_AUTHOR, $this->recipients, $this->subject, $htmlMail, true, "", "smtp");
		
		// E-Mail senden per phpMailer (SMTP)
		$mailStatus = $mail->send();
		
		// Falls Versand per SMTP erfolglos, per Sendmail probieren
		if($mailStatus !== true) {
			
			// E-Mail-Parameter für php Sendmail
			$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $this->recipients, $this->subject, $htmlMail, true, "", "sendmail");
			
			// Absenderadresse der Email auf FROM: setzen
			#$mail->Sender = $email;		
			
			// E-Mail senden per phpMailer (Sendmail)
			$mailStatus = $mail->send();
		}
		// Falls Versand per Sendmail erfolglos, per mail() probieren
		if($mailStatus !== true) {
			
			// E-Mail-Parameter für php mail()
			$mail->setMailParameters(AUTO_MAIL_EMAIL, AUTO_MAIL_AUTHOR, $this->recipients, $this->subject, $htmlMail, true);
			
			// E-Mail senden per phpMailer (mail())
			$mailStatus = $mail->send();
		}
		
		// Falls kein Fehler vorliegt ein Cookie setzen sowie die Variable $mes auf sent gesetzt
		if ($mailStatus === true) {
			
			@setcookie("spam_protection", "spam_protection", time()+300);
			return true;
		}
		else {
			$this->error = true;
			return false;
		}
		
	}	

	
	/**
	 * Generiert den HTML-Code für die E-Mail
	 * 
	 * @param varchar	formTitle	Formulartitel
	 * @return boolean	Ergebnis der Wertüberprüfung
	 * @return String 	Ergebnistabelle
	 */
	public function getMailHTML($formTitle)
	{
		
		//Datentabelle erstellen
		$formTitle =  Modules::safeText($formTitle);
		
		//Datentabelle erstellen
		$formData	= $this->formData;

		$domain		= str_replace("http://", "", PROJECT_HTTP_ROOT);
		$domain		= str_replace("www.", "", $domain);
		
		$submitDate	= date("d.m.Y", time());
		$submitTime	= date("H:i", time());
		
		#$IP			= getenv("REMOTE_ADDR");
		
		// Nachricht
		$htmlMail = "
					<html>
						<head>
							<title>".$this->subject." - ".$formTitle."</title>
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
							<p>{s_form:formsubmission} - ".$domain."</p>
							<p>".$submitDate." {s_text:attime} ".$submitTime." {s_text:clock}</p>
							<hr>
							<table>
							<tr>
							<td>{s_form:author}: </td><td><strong>".implode(',', $this->recipients)."</strong></td>
							</tr>
							<tr>
							<td>{s_form:message}: </td><td>&nbsp;</td>
							</tr>
							</table>
							<hr>" .
							$formData . "
						</body>
					</html>
					";
					
		// Statische Sprachbausteine ersetzen
		$htmlMail	= ContentsEngine::replaceStaText($htmlMail) . "\n";
		
		$this->mailHTMLCC	= $htmlMail;
		$this->mailHTMLBCC	= $htmlMail;
		
		return $htmlMail;
		
	}

}
