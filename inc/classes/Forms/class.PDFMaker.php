<?php
namespace Concise;



// TCPDF-Bibliothek einbinden
require_once(PROJECT_DOC_ROOT.'/extLibs/tcpdf/tcpdf.php');


/**
 * Klasse PDFMaker
 *
 * Erstellt ein PDF-Dokument aus übergebenen Formulardaten
 * 
 * 
 */

class PDFMaker extends \TCPDF
{

	public $headline	= "";
	public $logo		= "";
	public $date		= "";
	public $pdfError	= false;
	

	/**
	 * Setzen der Überschrift
	 * 
	 * @param varchar Überschrift 
	 * @param varchar Untertitel des Logs
	 */
	public function setDocumentHeader($headline, $date)
	{

		$this->headline = $headline;
		$this->date		= $date;

	}

	/**
	 * Erstellen des Logs
	 * 
	 * @param array Formulardaten
	 */
	public function getFormPDF($formData)
	{
		
		$this->AddPage();		
		$this->printFormData($formData);
		
	}

	/**
	 * Header setzen
	 */
	public function Header()
	{
		
		// Image example with resizing
		$logo		= "";
		$logoExt	= "";
		
		if(file_exists(PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/logo-pdf.png')) {
			$logo		= PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/logo-pdf.png';
			$logoExt	= "png";
		}
		elseif(file_exists(PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/logo-pdf.jpg')) {
			$logo		= PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/logo-pdf.jpg';
			$logoExt	= "jpg";
		}
		
		if(!empty($logo)) {
			$logoSize	= $size = getimagesize($logo);
			#$imgWidth	= $logoSize[0] * 2.54 / 72 * 10; // inches
			$imgWidth	= $logoSize[0] * 1 / 72 * 10;
			$xPos		= 200 - $imgWidth;
			$yPos		= 5;
			$this->Image(PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/logo-pdf.' . $logoExt, $xPos, $yPos, $imgWidth, 0, strtoupper($logoExt), '', '', '', true, 72);
		}
		
		if($this->page > 1)
			return;
	
		$this->SetMargins(15, 10, -1, true);
		//Position ca. 1,1 cm vom oberen Rand
		$this->SetY(25);
		//Schrift setzen auf: freeserif 12
		$this->SetFont('', '', 9);
		//Titel ausgeben
		$this->Cell(0, 10, $this->date, 0, 1, 'R', 0);
		//Zeilenumbruch
		$this->Ln(10);
		
		//Schrift setzen auf: Arial bold 15
		$this->SetFont('', 'B', 15);
		$this->SetTextColor(135);
		//Position ca. 1 cm vom oberen Rand
		$this->SetY(12);
		$this->SetDrawColor(220, 220, 220);
		$this->SetLineWidth(1);
		//Title
		$this->Cell(100, 6, $this->headline, 'TB', 1, 'C', 0, '', 1);
		//Line break
		$this->Ln(10);

	}

	/**
	 * Fußteil setzen
	 */
	public function Footer()
	{
	
		//Position ca. 1,5 cm vom unteren Rand
		$this->SetY(-15);
		//Schrift setzen auf: freeserif italic 8
		$this->SetFont('', 'I', 8);
		//Textfarbe: grau
		$this->SetTextColor(128);
		//Seitennummer ausgeben
		$this->Cell(0, 6, $this->headline . ' - Seite '.$this->PageNo(), 0, 0, 'C');
		$this->logo;
		
	}

	/**
	 * Die Tabelle mit den Log-EInträgen erstellen
	 * 
	 * @param array Formulardaten
	 */
	private function printFormData($formData)
	{
	
		$this->SetMargins(15, 30, -1, true);
		//Position ca. 1,1 cm vom oberen Rand
		$this->SetY(40);
		$this->SetCellPadding(1);
		//Farben festlegen
		$this->SetFillColor(220, 220, 220);
		$this->SetTextColor(0);
		$this->SetDrawColor(0, 0, 0);
		$this->SetLineWidth(.3);
		$this->SetFont('', 'B', 9);

		if (count($formData) == 0)
		{
			$string = mb_convert_encoding("Keine Formulardaten vorhanden.", "UTF-8");
			$this->Cell(0, 20, $string, 0, 0, 'C', 0);

		}
		else
		{
			//Spaltenbreite
			$width = array (85, 0);

			#$this->Cell($width[0], 5, Contents::replaceStaText("{s_form:field}"), 1, 0, 'C', 1);
			#$this->Cell($width[1], 5, Contents::replaceStaText("{s_form:input}"), 1, 0, 'C', 1);
			#$this->Ln();
			//Data
			$fill = 1;
			$i = 1;
			$headerCount = 1;
			
			foreach ($formData as $row)
			{
				
				// Falls eine Überschrift eingefügt werden soll
				if($row[0] == "_formheader") {
					
					// Auf zweite Spalte positionieren
					if($headerCount == 2) {
						$this->SetY(85);					
					}
					
					$row[0] = $row[1];
					$row[1] = "";
					$this->setCellHeightRatio(1);
					$this->Ln(2);
					$this->SetTextColor(255);
					$this->SetFont('', 'B', 9);
					$this->SetFillColor(135, 135, 135);
					$this->Write(2, $row[0], '', 1, 'L', 1, 2);
					$fill = 1;
					$headerCount++;
				}								
				// Falls eine Bemerkung eingefügt werden soll
				elseif($row[0] == "_formremark") {
					$fill = 0;
					if($headerCount > 7) {
						$this->SetTextColor(0);
						$this->SetFont('', '', 9);
					}
					else {
						$this->SetTextColor(135, 135, 135);
						$this->SetFont('', '', 8);
					}
					$this->setCellHeightRatio(1.25);
					
					if( $headerCount > 7)
						$this->SetMargins(15, 20, -1, true);
					
					// Falls HTML
					if(strpos($row[1], "<") === 0)
						$this->writeHTML($row[1], 0, 0, 1);
					// Sonst normaler Text
					else
						$this->Write(1, $row[1], '', 0, 'L', 1);
										
					$this->Ln(1);					
					$fill = 1;
				}								
				// Andernfalls Formulardaten ausgeben
				else {
					
					// Feldgröße anpassen für 2-spaltiges Layout
					if($row[0] == "Name")
						$width = array (43, 43);
					
					//Color and font restoration
					$this->SetTextColor(0);
					$this->SetFont('', '', 9);
					$this->setCellHeightRatio(1);
					$this->SetFillColor(235, 235, 235);
					$this->Cell($width[0], $this->getNumLines($row[1], $width[1]) * 4 + 1, $row[0], '', 0, 'L', $fill);
					$this->MultiCell($width[1], $this->getNumLines($row[1], $width[1]) * 4 + 1, $row[1], '', 'L', $fill, 0);
					$this->Ln();
	
					$fill = !$fill;
					
					// Falls Feld Bemerkung, Farbe setzten
				}
				$i++;
			}
			#$this->Cell(0, 0, '', 'T'); // Unterer Rahmen
		}
	}

	/**
	 * Error
	 */
	public function Error($msg) {
	
		// unset all class variables
		$this->_destroy(true);
		// exit program and print error
		$this->pdfError = $msg;
	
	}

}