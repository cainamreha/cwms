<?php
namespace Concise;



/**
 * Klasse für Newsletter
 *
 */

class Newsletter extends Modules
{
	 	

	/**
	 * Newsletter versenden
	 * 
     * @param	string Art des Newsletters
	 * @access	public
	 * @return	string
	 */
	public function getNewsletter($type, $newsCat, $newsType, $userAcces = "public", $targetPage = "")
	{
						
		$ImageLocation ="./test.gif";
		$ImgName = "test.gif";
		$MailFrom="bla";
		$MailFromAdr="mail@yourdomain.de";
		$MailTo ="addy@domain.de";
		$MailToSubject = "Test-Mail mit Bild";
		
		$CID = md5(uniqid (rand(), 1));
		
		$mime_boundary = "" . md5(uniqid(mt_rand(), 1));  
		
		
		$Header= "From:$MailFrom<$MailFromAdr>\n";
		$Header.= "X-Mailer: PHP/" . phpversion(). "\n";  
		$Header.= "MIME-Version: 1.0\n";
		$Header.= "Content-Type: multipart/related; boundary=\"".$mime_boundary."\"; type=\"text/plain\"\n"; 
		
		$MailBody = "--".$mime_boundary."\n";
		$MailBody.= "Content-Type: Text/HTML; charset=iso-8859-1$EOL";  
		$MailBody.= "Content-Transfer-Encoding: quoted-printable\n\n";  
		$MailBody.="<a href='http://lok-soft.de'>testlink</a><br><img src='cid:$CID.$ImgName'>";
		$MailBody.= "\n\n";
		$MailBody.= "--".$mime_boundary."\n";  
		
		
		$fp = fopen ($ImageLocation, "rb");
		$str = fread ($fp, filesize ($ImageLocation));
		$data = chunk_split(base64_encode($str));
		$content.= "Content-Type: image/gif\n";
		$content.= "Content-ID: <$CID.$ImgName>\n";
		$content.= "Content-Transfer-Encoding: base64\n";
		$content.= "Content-Disposition: inline; filename=\"$ImgName\"\n\n";  
		
		
		$content.= $data;
		$MailBody.= $content;
		$MailBody.= "--".$mime_boundary."--\n";  
		
		mail($MailTo, $MailToSubject, $MailBody, $Header); 			
						
		return parent::replaceStaText($newsOutput);

	}	

}
