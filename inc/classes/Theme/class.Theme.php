<?php
namespace Concise;


/**
 * Klasse für Theme-Konfiguration
 *
 */

class Theme extends ContentsEngine
{

	private $themeType		= "";
	private $configFileName	= "theme_config.ini";
	private $stylesFileName	= "theme_styles.ini";
	private $frameworkDef	= "default";
	private $themeStyles	= array();
	private $o_mobileDetect	= null;
	
	
	public function __construct($themeType = "fe")
	{

		// Theme type
		$this->themeType	= $themeType;
	
		// Mobile detect
		$this->initMobileDetect();
		
	}
	
	public function getThemeConfig()
	{
		
		// Theme / FE-Framework
		// Theme-Pfad
		if($this->themeType == "admin")
			$themePath	= SYSTEM_DOC_ROOT."/themes/" . ADMIN_THEME . "/";
		else
			$themePath	= PROJECT_DOC_ROOT."/".THEME_DIR;

		// Theme-Config einbinden
		if(file_exists($themePath . $this->configFileName)) {
			$this->themeConf		= parse_ini_file($themePath . $this->configFileName, true);
			if(isset($this->themeConf["framework"]["styledefs"]))
				$this->frameworkDef = strtolower($this->themeConf["framework"]["styledefs"]);
		}

		// Styledefinitionen einbinden (globale Konstanten)
		/*
		if(file_exists(PROJECT_DOC_ROOT . "/inc/styleDefs_" . $this->frameworkDef . ".inc.php"))
			require_once PROJECT_DOC_ROOT . "/inc/styleDefs_" . $this->frameworkDef . ".inc.php";
		else
			die("Style-Definitions not found.");
		*/
		if(file_exists($themePath . $this->stylesFileName))
			$this->themeStyles		= parse_ini_file($themePath . $this->stylesFileName, true);
		elseif(file_exists(PROJECT_DOC_ROOT . "/themes/theme_styles_" . $this->frameworkDef . ".ini"))
			$this->themeStyles		= parse_ini_file(PROJECT_DOC_ROOT."/themes/theme_styles_" . $this->frameworkDef . ".ini", true);
		
		// Theme-Arrays zusammenführen
		$this->themeConf	= array_merge($this->themeConf, $this->themeStyles);
		
		return $this->themeConf;
	
	}
	
	
	public function initMobileDetect()
	{
		
		require_once PROJECT_DOC_ROOT . "/inc/classes/Devices/class.Mobile_Detect.php";
		
		$this->o_mobileDetect	= new Mobile_Detect();
	
	}
	
	
	public function checkMobileDevice($check = "mobile")
	{
		
		if($check == "mobile")
			return $this->o_mobileDetect->isMobile();
		
		if($check == "tablet")
			return $this->o_mobileDetect->isTablet();
		
		if($check == "phone")
			return $this->o_mobileDetect->mobileGrade();

		return $this->o_mobileDetect->is($check);
	
	}
}
