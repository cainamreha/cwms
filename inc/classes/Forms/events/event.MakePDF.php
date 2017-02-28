<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse MakePDFEvent
 *
 * Generiert eine PDF-Dokument
 * 
 * 
 */

class MakePDFEvent extends Event
{

	public $o_pdf			= null;
	public $pdfName			= "";
	public $formTitle		= "";
	public $formInputArray	= array();
	public $userPDF			= "";
	public $g_Session		= array();
	public $pdfFolder		= "";
	public $browserPDF		= "";
	public $mailPDF			= "";
	public $pdfMailAttach	= "";
	public $pdfError		= false;
	public $result			= false;
	
	public function __construct()
    {
		
    }
 
    public function getPdfOutput($type)
    {

		return $this->o_pdf->Output($this->pdfName, $type);
	
	}
 
    public function getPdfName()
    {

		return $this->pdfName;
	
	}
 
    public function getPdfError()
    {

		return $this->pdfError;
	
	}

}
