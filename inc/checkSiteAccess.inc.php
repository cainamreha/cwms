<?php
namespace Concise;


// Falls gerade ein LiveUpdate durchgeführt wird, Live-Betrieb aussetzen
checkMaintenanceLock();
checkBannedIPs(); // IPs auschließen


// Falls gerade ein LiveUpdate durchgeführt wird, Live-Betrieb aussetzen
function checkMaintenanceLock()
{

	// Auf Datei maintenance.ini als Zeichen für Wartungsmodus prüfen
	$mtFile		= __DIR__ . '/../_temp/maintenance.ini';
	
	// Falls kein Wartungsmodus
	if(!file_exists($mtFile))
		return false;

	$expTime	= time() - 120;
	
	// Falls aktuelle Wartungsdatei, Wartungshinweiseite aufrufen
	if(date(filemtime($mtFile)) > $expTime) {
	
		// Falls Wartungsadmin
		if(isset($_COOKIE['cwms_maintenanceLog']))
			return false;
		
		if(isset($_GET['page']) && $_GET['page'] == "login")
			header("Location: /_login.html") . exit;
		else
			header("Location: /_index.html?paused=1", true, 302) . exit;
		return true;
	}
	
	// Falls alte Datei (z.B. Wartung fehlgeschlagen), diese löschen
	unlink($mtFile);
	return false;
}


// Exclude banned IPs
function checkBannedIPs()
{

	// Falls banned IP, Wartungshinweiseite aufrufen
	$ipFile		= __DIR__ . '/../inc/bannedIPs.txt';
	// Falls keine IP Datei
	if(!file_exists($ipFile))
		return false;
	
	$bIPs = file($ipFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	
	if(empty($bIPs))
		return false;

	$realip	= "";	
		
	if (isset ($_SERVER["HTTP_CLIENT_IP"]))
	{
		$realip = $_SERVER["HTTP_CLIENT_IP"];
	}
	elseif (isset ($_SERVER["HTTP_X_FORWARDED_FOR"]))
	{
		$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif (isset ($_SERVER["REMOTE_ADDR"]))
	{
		$realip = $_SERVER["REMOTE_ADDR"];
	}
	
	if(empty($realip))
		return false;
	
	$realip	= long2ip(ip2long($realip) & 0xFFFFFF00);
	
	if(in_array($realip, $bIPs)) {
		header("Location: /_index.html?paused=1&ban=1", true, 302);
		exit;
	}
	return false;
}
