<?php
namespace Concise\Events\Form;

use Symfony\Component\EventDispatcher\Event;
use Concise\PDFMaker;
use Concise\Files;



##############################
###  EventListener-Klasse  ###
##############################

// PdfCoreEventsListener

class PdfCoreEventsListener
{	
	
	// onMakePdf
	public function onMakePdf(Event $event)
	{
		
		// PDFMaker-Klasse einbinden
		require_once PROJECT_DOC_ROOT."/inc/classes/Forms/class.PDFMaker.php";
	
		$event->o_pdf = new PDFMaker();
		
		$event->o_pdf->setLanguageArray("en","de");
		$event->o_pdf->setDocumentHeader($event->formTitle, date("d.m.Y", time()));
		#$event->o_pdf->SetHeaderData(PROJECT_HTTP_ROOT . '/' . CC_IMAGE_FOLDER . '/logo.png', 0, '', $event->formTitle);			
		$event->o_pdf->setHeaderTemplateAutoreset(1);
		$event->o_pdf->setTopMargin(50);
		$event->o_pdf->getFormPDF($event->formInputArray);
		
		//Files-Klasse einbinden
		require_once PROJECT_DOC_ROOT."/inc/classes/Media/class.Files.php";
		
		// Falls es sich um eine benutzerspezifische Datei handelt
		if($event->userPDF && isset($event->g_Session['username']) && isset($event->g_Session['userid'])) {
			$nameExt 	= $event->g_Session['userid'];
			$rootFolder = '/_user/';
		}
		else {
			$nameExt 	= 'pdf-' . time();
			$rootFolder = '/' . CC_FILES_FOLDER . '/';
		}
			
		$event->pdfName	= $event->formTitle . '_' . $nameExt;
		$event->pdfName	= Files::getValidFileName($event->pdfName, true) . '.pdf';
		
		// Ausgabe der pdf-Datei, falls folder oder Browser
		if($event->pdfFolder != ""
		|| $event->browserPDF
		) {
			
			$outputType	= "";
			$folder		= "";
		
			// Falls ein Speicherort (Ordner) angegeben ist, pdf-Output als Datei speichern
			if($event->pdfFolder != "") {
			
				$folder		= PROJECT_DOC_ROOT . $rootFolder . $event->pdfFolder . '/';
				
				// Falls der angegebene Ordner noch nicht existiert, diesen anlegen
				if(!is_dir($folder))
					mkdir($folder);

				$outputType .= "F";
			}
			// Falls kein Speicherort angegeben ist, pdf-Output an Browser senden
			if($event->browserPDF) {
			
				$outputType .= "I"; // "D" für Erzwingen eines "save as"-Dialogs, "I" für direktes Öffnen im Browser
			}
			
			$event->result = $event->o_pdf->Output($folder . $event->pdfName, $outputType);

		}
		
		// pdfError
		$event->pdfError	= $event->o_pdf->pdfError;
		
		return true;
	
	}
	
} // Ende class
