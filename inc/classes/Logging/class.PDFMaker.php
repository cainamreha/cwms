<?php
namespace Concise;



// TCPDF-Bibliothek einbinden
require_once(PROJECT_DOC_ROOT.'/extLibs/tcpdf/tcpdf.php');


/**
 * Klasse PDFMaker
 *
 * Versendet Formulardaten per E-Mail
 * 
 * 
 */

class PDFMaker extends \TCPDF
{

	public $headline = "";
	public $logo = "";
	public $subtitle = "";

	/**
	 * Setzen der Überschrift
	 * 
	 * @param varchar Überschrift 
	 * @param varchar Untertitel des Logs
	 */
	public function setDocumentHeader($headline, $subtitle)
	{

		$this->headline = $headline;
		$this->subtitle = $subtitle;

	}

	/**
	 * Erstellen des Logs
	 * 
	 * @param int Monat des Jahres
	 * @param int Jahreszahl 
	 */
	public function PrintLog($month, $year)
	{
		
		$this->AddPage();		
		$this->printLogTable($month, $year);
		
	}

	/**
	 * Header setzen
	 */
	public function Header()
	{
		

		//Position ca. 1,1 cm vom oberen Rand
		$this->SetY(11);
		//Schrift setzen auf: helvetica 12
		$this->SetFont('helvetica', '', 12);
		//Titel ausgeben
		$this->Cell(0, 5, $this->subtitle, 0, 1, 'R', 0);
		//Zeilenumbruch
		$this->Ln(20);
		
		// Image example with resizing
		$this->Image(PROJECT_DOC_ROOT . '/' . CC_IMAGE_FOLDER . '/logo.png', 10, 9.5, 25, 0, 'PNG', '', '', '', true, 300);
		
		//Schrift setzen auf: Arial bold 15
		$this->SetFont('helvetica', 'B', 15);
		//Position ca. 1 cm vom oberen Rand
		$this->SetY(10);
		$this->SetDrawColor(220, 220, 220);
		$this->SetLineWidth(1);
		//Title
		$this->Cell(190, 6, $this->headline, 'TB', 1, 'C');
		//Line break
		$this->Ln(20);

	}

	/**
	 * Fußteil setzen
	 */
	public function Footer()
	{
		//Position ca. 1,5 cm vom unteren Rand
		$this->SetY(-15);
		//Schrift setzen auf: helvetica italic 8
		$this->SetFont('helvetica', 'I', 8);
		//Textfarbe: grau
		$this->SetTextColor(128);
		//Seitennummer ausgeben
		$this->Cell(0, 6, 'Seite '.$this->PageNo(), 0, 0, 'C');
		$this->logo;
		
	}

	/**
	 * Die Tabelle mit den Log-EInträgen erstellen
	 * 
	 * @param int Monat
	 * @param int Jahr
	 */
	private function printLogTable($month, $year)
	{
				
		//Timestamp berechnen:
		//Anfangszeitraum: 01. um 0:00 des besagten Monats.
		$beginTime = mktime(0, 0, 0, $month, 1, $year);
		//Endzeitraum (1 Monat später; - 1 Tag)
		$endTime = mktime(0, 0, 0, $month +1, 0, $year);
		//Daten aus der Tabelle holen
		$sql =	"SELECT n.*, p.`title_".$GLOBALS['o_lng']->lang."` 
					FROM `" . DB_TABLE_PREFIX . "log as n 
						LEFT JOIN `" . DB_TABLE_PREFIX . "pages` as p 
						ON n.`page_id` = p.`page_id`
					WHERE timestamp BETWEEN "."'".$beginTime."' AND '".$endTime."'
				";
		//Direkt auf das globale Objekt zugreifen
		$result = $GLOBALS['DB']->query($sql);
		
		#var_dump($result);

		//Farben festlegen
		//Position ca. 1,1 cm vom oberen Rand
		$this->SetY(25);
		$this->SetMargins(10, 25, -1, true);
		$this->SetFillColor(220, 220, 220);
		$this->SetTextColor(0);
		$this->SetDrawColor(0, 0, 0);
		$this->SetLineWidth(.3);
		$this->SetFont('', 'B', 8);

		if (count($result) == 0)
		{
			$string = mb_convert_encoding("Es liegen keine Daten für diesen Zeitraum vor.", "UTF-8");
			$this->Cell(0, 20, $string, 0, 0, 'C', 0);

		}
		else
		{
			//Spaltenbreite
			$width = array (15, 30, 100, 20, 0);

			$this->Cell($width[0], 5, "page_id", 1, 0, 'R', 1);
			$this->Cell($width[1], 5, "Seitenname", 1, 0, 'C', 1);
			$this->Cell($width[2], 5, "Referer", 1, 0, 'C', 1);
			$this->Cell($width[3], 5, "Browser", 1, 0, 'C', 1);
			$this->Cell($width[4], 5, "Zeitpunkt", 1, 0, 'C', 1);
			$this->Ln();
			//Color and font restoration
			$this->SetFillColor(230, 230, 230);
			$this->SetTextColor(0);
			$this->SetFont('');
			//Data
			$fill = 0;
			
			foreach ($result as $row)
			{

				$this->Cell($width[0], 4, $row['page_id'], 'LR', 0, 'R', $fill);
				$this->Cell($width[1], 4, $row['title_'.$GLOBALS['o_lng']->lang], 'LR', 0, 'L', $fill);
				$this->Cell($width[2], 4, $row['referer'], 'LR', 0, 'L', $fill);
				$this->Cell($width[3], 4, $row['browser'], 'LR', 0, 'C', $fill);
				$this->Cell($width[4], 4, date("d.m.Y   H:i", $row['timestamp']), 'LR', 0, 'R', $fill);
				$this->Ln();

				$fill = !$fill;
			}
			$this->Cell(0, 0, '', 'T');
		}
	}

}
