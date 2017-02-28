<?php
// Crontab

// Falls Aufruf nicht via Crobjob, Skript abbrechen
$sapi = php_sapi_name();
if($sapi != 'cli' && $sapi != 'cgi-fcgi')
	header("Location: error.html?cli=0") . exit;


// common.php einbinden
require_once "../inc/common.php";

// Backup der kompletten Datenbank
$domain		= str_replace("http://", "", PROJECT_HTTP_ROOT);
$domain		= str_replace("https://", "", $domain);
$path		= PROJECT_DOC_ROOT . "/backup/";
$lastweek	= false;

// Falls Montag, wöchentlichen Cron-Job aktivieren (z.B. einmal in der Woche die Tabelle log_bots ausmisten).
if(date("w", time()) == 1) {
	$weeklyCron = true;
}
else {
	$weeklyCron = false;
}


// Auszuschließende Tabellen für Content-Backup
$tables =	"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."badlogin " .
			"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."bannedip " .
			"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."user " .
			"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."log " .
			"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."log_bots " .
			"--ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."stats";

$logTabs	= "";

// Log Tables
for($i = 2010; $i < date("Y", time()); $i++) {
	$logTabs .=	" --ignore-table=".DB_NAME.".".DB_TABLE_PREFIX."log_" . $i;
}

$nameExt	= "_contents";


// Cronjob ausführen (Backup)
// Zuerst Content-Bkp, dann Full Bkp
for($i = 1; $i <= 2; $i++) {

	$latest		= false;
	$former		= false;

	// Alte Datei (past) tempörär umbenennen
	if(file_exists($path."db_bkp_cron".$nameExt."_former.sql")) {
		$former = @rename($path."db_bkp_cron".$nameExt."_former.sql", $path."db_bkp_cron".$nameExt."_former_tmp.sql");
	}
		
	// Alte Datei (latest) tempörär umbenennen
	if(file_exists($path."db_bkp_cron".$nameExt."_latest.sql")) {
		$latest = @rename($path."db_bkp_cron".$nameExt."_latest.sql", $path."db_bkp_cron".$nameExt."_latest_tmp.sql");
	}
	
	$cronJob = Concise\MySQL::makeBackup($tables . $logTabs, $nameExt, true); // Bkp
	
	// Falls erfolgereich, Dateien umbenennen/löschen
	if($cronJob) {
		if($latest && file_exists($path."db_bkp_cron".$nameExt."_latest_tmp.sql")) @rename($path."db_bkp_cron".$nameExt."_latest_tmp.sql", $path."db_bkp_cron".$nameExt."_former.sql");
		if($former && file_exists($path."db_bkp_cron".$nameExt."_former_tmp.sql")) @unlink($path."db_bkp_cron".$nameExt."_former_tmp.sql");
	}
	else {
		if($latest && file_exists($path."db_bkp_cron".$nameExt."_latest_tmp.sql")) @rename($path."db_bkp_cron".$nameExt."_latest_tmp.sql", $path."db_bkp_cron".$nameExt."_latest.sql");
		if($former && file_exists($path."db_bkp_cron".$nameExt."_former_tmp.sql")) @rename($path."db_bkp_cron".$nameExt."_former_tmp.sql", $path."db_bkp_cron".$nameExt."_former.sql");
		
		//Fehlermeldung generieren
		$subject = "Cronjob-Fehler - " . $domain;
		
		$error = "Folgender Fehler ist aufgetreten auf ".$domain.":\r\n";
		$error .= "Das automatische Backup konnte nicht erstellt werden.\r\n\r\n";
		$error .= "Diese Email wurde automatisch generiert.";
	
		@mail(EH_ADMIN_EMAIL, $subject, $error, "From: webmaster@project.de\r\n");
	}
	$tables		= "";
	$nameExt	= "_full";

} // Ende for
 

 
// Neues Log-Objekt
$o_log = new Concise\Log($DB, $_SESSION);

$o_log->cleanupLogTable();


// Weekly cron
// Falls erfolgereich, einmal in der Woche wöchentlichen Stand speichern und die Tabelle log_bots ausmisten
if($cronJob && $weeklyCron) {

	$nameExt	= "_contents";

	// Zuerst Content-Bkp, dann Full Bkp
	for($i = 1; $i <= 2; $i++) {
		
		// Alte Datei (2nd-lastweek) tempörär umbenennen
		if(file_exists($path."db_bkp_cron".$nameExt."_2nd-lastweek.sql")) {
			@rename($path."db_bkp_cron".$nameExt."_2nd-lastweek.sql", $path."db_bkp_cron".$nameExt."_2nd-lastweek_tmp.sql");
		}
		// Alte Datei (lastweek) umbenennen und als copy von latest neu anlegen
		if(file_exists($path."db_bkp_cron".$nameExt."_lastweek.sql")) {
			if(@rename($path."db_bkp_cron".$nameExt."_lastweek.sql", $path."db_bkp_cron".$nameExt."_2nd-lastweek.sql")) {
				if(file_exists($path."db_bkp_cron".$nameExt."_latest.sql")) @copy($path."db_bkp_cron".$nameExt."_latest.sql", $path."db_bkp_cron".$nameExt."_lastweek.sql");
			}
		}
		elseif(file_exists($path."db_bkp_cron".$nameExt."_latest.sql"))
			@copy($path."db_bkp_cron".$nameExt."_latest.sql", $path."db_bkp_cron".$nameExt."_lastweek.sql");
	
		// Temp-Datei löschen
		if(file_exists($path."db_bkp_cron".$nameExt."_2nd-lastweek_tmp.sql")) {
			if(file_exists($path."db_bkp_cron".$nameExt."_2nd-lastweek.sql"))
				@unlink($path."db_bkp_cron".$nameExt."_2nd-lastweek_tmp.sql");
			else
				@rename($path."db_bkp_cron".$nameExt."_2nd-lastweek_tmp.sql", $path."db_bkp_cron".$nameExt."_2nd-lastweek.sql");
		}
		$nameExt	= "_full";

	} // Ende for
	

	// Log bot cleanup
	$o_log->cleanupBotLogs();

} // Ende weekly cron



// Stats-Tabelle updaten
$o_log->updateStatsTable();

// Jahreswechsel manuell durchführen
#$o_log->createLogArchive(2015);

// Falls Jahreswechsel, Tabelle `log` archivieren
if(date("z") === "0") {

	// Ggf. neues Loggin-Jahr starten
	$o_log->changeLogSeason();
}
?>