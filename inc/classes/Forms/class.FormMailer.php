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
		
		//E-Mailauthor
		$from			= AUTO_MAIL_AUTHOR;
		$replyTo		= AUTO_MAIL_EMAIL;
		
		//E-Mailempfänger-Adressen extrahieren
		$recipients		= htmlspecialchars(implode(',', $this->recipients));
		$recipientsCC	= htmlspecialchars(implode(',', $this->recipientsCC));
		$recipientsBCC	= htmlspecialchars(implode(',', $this->recipientsBCC));
		
		$subject		= $this->subject; //Subject
		$htmlMail		= $this->getMailHTML($formTitle); //Message
		$htmlMailCC		= $this->mailHTMLCC; //Message Cc
		$htmlMailBCC	= $this->mailHTMLBCC; //Message Bcc


		// Falls die pdf-Datei per E-Mail versandt werden soll
		if($mailPDF) {
									
			// Header-Daten setzen
			$boundary = md5(uniqid(time()));

			$header	=	"MIME-Version: 1.0\n" .
						'X-Mailer: PHP/' . phpversion() . "\r\n" .
						'From: ' . $from . ' <' . $replyTo . '>' . "\r\n" .
						'Reply-To:' . $replyTo . "\r\n" .
						'Cc: ' . $recipientsCC . "\r\n" .
						'Bcc: ' . $recipientsBCC . "\r\n" .
						"Content-Type: multipart/mixed; boundary=$boundary\n\n".
						"This is a multi-part message in MIME format\n".
						"--$boundary\n".
						'Content-type: text/html; charset=utf-8' . "\r\n" .
						'Content-Transfer-Encoding: 8bit' . "\r\n\n" .
						$htmlMail .
						"\n--$boundary".
						"\nContent-Type: application/octetstream; name=" . $pdfName . "\n".
						"Content-Transfer-Encoding: base64\n".
						"Content-Disposition: attachment; filename=" . $pdfName . "\n\n".
						$pdfMailAttach.
						"\n--$boundary--";
						
			$htmlMail = ""; // Inhalte entfernen, da schon in Header
		}


		// Falls noch kein Header für pdf-Mail gesetzt wurde, Header setzen
		if(!isset($header)) {
				
			$header		 = 'MIME-Version: 1.0' . "\n";
			$header		.= 'X-Mailer: PHP/' . phpversion() . "\n";
			$header		.= 'Content-type: text/html; charset=utf-8' . "\n";
			$header		.= 'Content-Transfer-Encoding: 8bit' . "\n";
			
			// zusätzlicher Header
			$header		.= 'From: ' . $from . ' <' . $replyTo . '>' . "\n";
			$header		.= 'Reply-To:' . $replyTo . "\n";
			$header		.= 'Cc: ' . $recipientsCC . "\n";
			$header		.= 'Bcc: ' . $recipientsBCC . "\n";
		}
		
		// E-Mail senden
		$mail		= @mail($recipients, $subject, $htmlMail, $header);
		
		
		// Falls kein Fehler vorliegt ein Cookie setzen sowie die Variable $mes auf sent gesetzt
		if ($mail === true) {
			
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
