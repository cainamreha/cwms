<?php
namespace Concise;


###############################################
################  SortPages  ##################
###############################################

// SortPages

class SortPages extends Admin
{

	private $sortTable	= "";
	
	public function __construct($DB, $tablePages)
	{

		// DB-Objekt
		$this->DB			= $DB;
		$this->sortTable	= DB_TABLE_PREFIX . $this->DB->escapeString($tablePages);

	}
	
	
	// sortPageUp
	public function sortPageUp($sortId)
	{

		if(!is_numeric($sortId))
			return false;
		
		$sortId			= (int)$sortId;
		$success		= false;
		$updateSQL1		= false;
		$updateSQL2		= false;
		$updateSQL3		= false;
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->sortTable . "`");
		

		// Transaktion starten
		$this->DB->query("SET AUTOCOMMIT=0");
		$this->DB->query("START TRANSACTION");

		
		// Datenbanksuche nach zu verschiebender Seite in Tabelle Pages
		$queryId = $this->DB->query("SELECT lft,rgt,`menu_item`
											FROM `" . $this->sortTable . "` 
											WHERE `page_id` = " . $sortId . "
											");
		
		if(is_array($queryId)
		&& count($queryId) == 1
		) {
		
			$lft		= $queryId[0]['lft'];
			$rgt		= $queryId[0]['rgt'];
			$menuItem	= $queryId[0]['menu_item'];
	
			// Datenbanksuche nach benachbarter Seite
			$queryTarget = $this->DB->query("SELECT lft,rgt
												FROM `" . $this->sortTable . "` 
												WHERE rgt = " . $lft . "-1 
												AND `menu_item` = $menuItem
												");
			
			if(count($queryTarget) == 1) {
	
				$lftTarget = $queryTarget[0]['lft'];
				$rgtTarget = $queryTarget[0]['rgt'];
				$lftDiff = $lft - $lftTarget;
				$rgtDiff = $rgt - $lft+1;
				

				// db-Update der Pages Tabelle
				$updateSQL1 = $this->DB->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft-$lftDiff, 
													rgt = rgt-$lftDiff,
													locked = 1 
													WHERE lft BETWEEN $lft AND $rgt
													AND `menu_item` = $menuItem
													");


				#var_dump($updateSQL1.$lftDiff);
				
				// db-Update der Pages Tabelle
				$updateSQL2 = $this->DB->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft+$rgtDiff, 
													rgt = rgt+$rgtDiff  
													WHERE lft BETWEEN $lftTarget AND $rgtTarget 
													AND `menu_item` = $menuItem
													AND locked != 1 
													");
				
				// db-Update der Pages Tabelle
				$updateSQL3 = $this->DB->query("UPDATE `" . $this->sortTable . "` 
													SET	locked = 0
													WHERE locked = 1 
													");
				
		

			}
		
		}
		
		// Transaktion ausführen/rückgängig
		if(	$updateSQL1 === true && 
			$updateSQL2 === true && 
			$updateSQL3 === true
		) {
			$this->DB->query("COMMIT");
			$success		= true;
		}
		else {
			$this->DB->query("ROLLBACK");
			$dbError = '<script type="text/javascript">jAlert(ln.dberror, ln.alerttitle);</script>';
		}
			
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		return $success;
	
	}
	
	
	// sortPageDown
	public function sortPageDown($sortId)
	{

		if(!is_numeric($sortId))
			return false;
		
		$sortId			= (int)$sortId;
		$success		= false;
		$updateSQL1		= false;
		$updateSQL2		= false;
		$updateSQL3		= false;
		
		// db-Tabelle sperren
		$lock = $this->DB->query("LOCK TABLES `" . $this->sortTable . "`");
		

		// Transaktion starten
		$this->DB->query("SET AUTOCOMMIT=0");
		$this->DB->query("START TRANSACTION");
		
		
		// Datenbanksuche nach zu bearbeitender Seite in Tabelle Pages (Überprüfung ob Seite vorhanden)
		$queryId = $this->DB->query(  "SELECT lft,rgt,`menu_item` 
											FROM `" . $this->sortTable . "` 
											WHERE `page_id` = " . $sortId . "
											");
		
		if(count($queryId) == 1) {
		
			$lft = $queryId[0]['lft'];
			$rgt = $queryId[0]['rgt'];
			$menuItem = $queryId[0]['menu_item'];
	
			// Datenbanksuche nach zu bearbeitender Seite in Tabelle Pages (Überprüfung ob Seite vorhanden)
			$queryTarget = $this->DB->query("SELECT lft,rgt
												FROM `" . $this->sortTable . "` 
												WHERE lft = " . $rgt . "+1 
												AND `menu_item` = $menuItem
												");
			
			if(count($queryTarget) == 1) {
	
				$lftTarget = $queryTarget[0]['lft'];
				$rgtTarget = $queryTarget[0]['rgt'];
				$lftDiff = $rgtTarget - $lftTarget+1;
				$rgtDiff = $rgt - $lft+1;
				

				// db-Update der Pages Tabelle
				$updateSQL1 = $this->DB->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft+$lftDiff, 
													rgt = rgt+$lftDiff,
													locked = 1 
													WHERE lft BETWEEN $lft AND $rgt
													AND `menu_item` = $menuItem
													");


				#var_dump($updateSQL1.$lftDiff);
				
				// db-Update der Pages Tabelle
				$updateSQL2 = $this->DB->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft-$rgtDiff, 
													rgt = rgt-$rgtDiff  
													WHERE lft BETWEEN $lftTarget AND $rgtTarget 
													AND `menu_item` = $menuItem
													AND locked != 1 
													");
				
				// db-Update der Pages Tabelle
				$updateSQL3 = $this->DB->query("UPDATE `" . $this->sortTable . "` 
													SET	locked = 0
													WHERE locked = 1 
													");
				
		

			}

		}
		
		// Transaktion ausführen/rückgängig
		if(	$updateSQL1 === true && 
			$updateSQL2 === true && 
			$updateSQL3 === true
		) {
			$this->DB->query("COMMIT");
			$success		= true;
		}
		else {
			$this->DB->query("ROLLBACK");
			$dbError = '<script type="text/javascript">jAlert(ln.dberror, ln.alerttitle);</script>';
		}
			
		// db-Sperre aufheben
		$unLock = $this->DB->query("UNLOCK TABLES");

		return $success;
	
	}
	
	
	// sortPageTrans
	public function sortPageTrans($sortType, $moveId, $targetId, $menuItemTarget)
	{
		
		$success		= false;
		
		if($sortType != "below"
		&& $sortType != "child") {
			return false;
		}

		if(!is_numeric($moveId)
		|| (!is_numeric($targetId) && $targetId != "new")
		|| !is_numeric($menuItemTarget)
		)
			return false;

		$moveId			= (int)$moveId;
		$rgtTarget		= 1;

		// Datenbanksuche nach zu bearbeitender Seite in Tabelle Pages (Überprüfung ob Seite vorhanden)
		$queryMove = $GLOBALS['DB']->query("SELECT `lft`,`rgt`,`menu_item` 
												FROM `" . $this->sortTable . "` 
											WHERE `page_id` = " . $moveId . "
											");


		if(!is_array($queryMove)
		|| count($queryMove) == 0
		) {
			return false;
		}

		$lft 			= $queryMove[0]['lft'];
		$rgt 			= $queryMove[0]['rgt'];
		$menuItem 		= $queryMove[0]['menu_item'];

		$updateSQL1a 	= false;
		$updateSQL1b	= false;
		$updateSQL1c	= false;
		$updateSQL2a	= false;
		$updateSQL2b	= false;
		$updateSQL3		= false;

		if($targetId != "new") { // Falls nicht in ein leeres Menü verschoben wird, Zielmenü und lft/rgt festlegen
			
			// Datenbanksuche nach zu bearbeitender Seite in Tabelle Pages (Überprüfung ob Seite vorhanden)
			$queryTarget = $GLOBALS['DB']->query  ("SELECT `rgt`,`menu_item` 
														FROM `" . $this->sortTable . "` 
													WHERE `page_id` = " . $targetId . "
													");
			
			if(!is_array($queryTarget)
			|| count($queryTarget) == 0
			) {
				return false;
				
			}
			
			$rgtTarget		= $queryTarget[0]['rgt'];
			$menuItemTarget	= $queryTarget[0]['menu_item'];
		
		}



		// Falls parallel zu einem Menüpunkt verschoben werden soll
		if($sortType == "below") {
						
			// db-Tabelle sperren
			$lock = $GLOBALS['DB']->query("LOCK TABLES `" . $this->sortTable . "`");

			// Transaktion starten
			$GLOBALS['DB']->query("SET AUTOCOMMIT=0");
			$GLOBALS['DB']->query("START TRANSACTION");
			

			$moveDiff = $rgt - $lft+1;
			$movePos = $rgtTarget +1;
			if($movePos > $rgt && $menuItem == $menuItemTarget)
				$movePos = $movePos - $moveDiff;
			$targetDiff = $movePos-$lft; // Adduktor für neuen lft Wert
			


			// db-Update der Pages Tabelle
			// zu verschiebende Punkte sperren und schon die neuen lft- und rgt-Werte setzen
			$updateSQL1a = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	`lft` = lft+$targetDiff,  
														`rgt` = rgt+$targetDiff,
														`menu_item` = $menuItemTarget,
														`locked` = 1 
													WHERE lft BETWEEN $lft AND $rgt 
														AND menu_item = $menuItem
													");

			// rgt-Wert der Eltern der zu verschiebenden Punkte verringern
			$updateSQL1b = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	`rgt` = rgt-$moveDiff
													WHERE  
														`lft` < $lft AND rgt > $rgt 
														AND `menu_item` = $menuItem
														AND `locked` != 1 
													");
			
			// hinter den zu verschiebenden Punkten liegende nach oben verschieben
			$updateSQL1c = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft-$moveDiff, 
														rgt = rgt-$moveDiff 
													WHERE lft >= $rgt
														AND `menu_item` = $menuItem
														AND locked != 1 
													");
			
			// hinter den zu verschiebenden Punkten liegende nach unten verschieben (Lücke generieren)
			$updateSQL2a = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft+$moveDiff, 
														rgt = rgt+$moveDiff 
													WHERE lft >= $movePos
														AND `menu_item` = $menuItemTarget
														AND locked != 1 
													");

			// rgt-Werte von evtl. neuen Elternknoten erhöhen
			$updateSQL2b = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	rgt = rgt+$moveDiff   
													WHERE rgt >= $movePos
														AND lft < $movePos  
														AND `menu_item` = $menuItemTarget
														AND locked != 1 
													");
			
			// Spalte locked zurücksetzen
			$updateSQL3 = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	locked = 0
													WHERE locked = 1 
												");
												
			
			#var_dump($updateSQL1a.$updateSQL1b.$updateSQL1c.$updateSQL2a.$updateSQL2b.$updateSQL3);
					
		}

		
		// Falls als Kind von Seitenknoten
		if($sortType == "child") {
						
			// db-Tabelle sperren
			$lock = $GLOBALS['DB']->query("LOCK TABLES `" . $this->sortTable . "`");

			// Transaktion starten
			$GLOBALS['DB']->query("SET AUTOCOMMIT=0");
			$GLOBALS['DB']->query("START TRANSACTION");

			$moveDiff = $rgt - $lft+1;
			$movePos = $rgtTarget;
			if($movePos > $rgt && $menuItem == $menuItemTarget)
				$movePos = $movePos - $moveDiff;
			$targetDiff = $movePos-$lft; // Adduktor für neuen lft Wert
			


			// db-Update der Pages Tabelle
			// zu verschiebende Punkte sperren und schon die neuen lft- und rgt-Werte setzen
			$updateSQL1a = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft+$targetDiff,  
														rgt = rgt+$targetDiff,
														`menu_item` = $menuItemTarget,
														locked = 1 
													WHERE lft BETWEEN $lft AND $rgt
														AND menu_item = $menuItem
													");

			// rgt-Wert der Eltern der zu verschiebenden Punkte verringern
			$updateSQL1b = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	rgt = rgt-$moveDiff 
													WHERE lft < $rgt AND rgt > $rgt
														AND `menu_item` = $menuItem 
														AND locked != 1
													");
			
			// hinter den zu verschiebenden Punkten liegende nach oben verschieben
			$updateSQL1c = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft-$moveDiff, 
														rgt = rgt-$moveDiff 
													WHERE lft > $rgt 
														AND `menu_item` = $menuItem
														AND locked != 1
													");
			
		#var_dump($updateSQL1.$targetDiff);
			// hinter den zu verschiebenden Punkten liegende nach unten verschieben (Lücke generieren)
			$updateSQL2a = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	lft = lft+$moveDiff, 
														rgt = rgt+$moveDiff
													WHERE lft > $movePos
														AND `menu_item` = $menuItemTarget
														AND locked != 1 
													");
			
			// rgt-Werte von evtl. neuen Elternknoten erhöhen
			$updateSQL2b = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	rgt = rgt+$moveDiff   
													WHERE rgt >= $movePos
														AND lft < $movePos  
														AND `menu_item` = $menuItemTarget
														AND locked != 1 
													");
			
			// Spalte locked zurücksetzen
			$updateSQL3 = $GLOBALS['DB']->query("UPDATE `" . $this->sortTable . "` 
													SET	locked = 0
													WHERE locked = 1 
												");
			
			
			#var_dump($updateSQL1a.$updateSQL1b.$updateSQL1c.$updateSQL2a.$updateSQL2b.$updateSQL3);

			
		}

		
		// Transaktion ausführen/rückgängig
		if(	$updateSQL1a === true && 
			$updateSQL1b === true && 
			$updateSQL1c === true && 
			$updateSQL2a === true && 
			$updateSQL2b === true && 
			$updateSQL3 === true
		) {
			$GLOBALS['DB']->query("COMMIT");
			$success		= true;
		}
		else {
			$GLOBALS['DB']->query("ROLLBACK");
		}

		// db-Sperre aufheben
		$unLock = $GLOBALS['DB']->query("UNLOCK TABLES");

		return $success;

	}

} // end class SortPages
