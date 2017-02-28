<?php
namespace Concise;


/**
 * Klasse für Templatesystem
 *
 */

class Template extends ContentsEngine
{
    /**
     * Der Ordner in dem sich die Template-Dateien befinden.
     *
     * @access protected
     * @var    string
     */
    protected $templateDir = TEMPLATE_DIR;
    
    /**
     * Der Ordner in dem sich die Sprach-Dateien befinden
     *
     * @access protected
     * @var    string
     */
    protected $languageDir = "langs/";
    
    /**
     * Der linke Delimter für einen Standard-Platzhalter
     *
     * @access protected
     * @var    string
     */
    protected $leftDelimiter = '{';
    
    /**
     * Der rechte Delimter für einen Standard-Platzhalter
     *
     * @access protected
     * @var    string
     */
    protected $rightDelimiter = '}';

    /**
     * Der linke Delimter für einen Include
     *
     * @access protected
     * @var    string
     */
    protected $leftDelimiterF = '{i_';
    
    /**
     * Der rechte Delimter für eine Funktion
     *
     * @access protected
     * @var    string
     */
    protected $rightDelimiterF = '}';

    /**
     * Der linke Delimter für ein Kommentar
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $leftDelimiterC = '\{\*';
    
    /**
     * Der rechte Delimter für ein Kommentar
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $rightDelimiterC = '\*\}';
    
    /**
     * Der linke Delimter für einen statischen Textbaustein
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $leftDelimiterS = '\{s_';
    
    /**
     * Der rechte Delimter für einen statischen Textbaustein
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $rightDelimiterS = '\}';
    
    /**
     * Der linke Delimter für einen Theme class style Platzhalter
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $leftDelimiterT = '\{t_';
    
    /**
     * Der rechte Delimter für einen Theme class style Platzhalter
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $rightDelimiterT = '\}';
    
    /**
     * Der linke Delimter für einen Icon Platzhalter
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $leftDelimiterIco = '\{ico\:';
    
    /**
     * Der rechte Delimter für einen Icon Platzhalter
     *
     * Sonderzeichen müssen escaped werden, weil der Delimter in einem RegExp
     * verwendet wird.
     *
     * @access protected
     * @var    string
     */
    protected $rightDelimiterIco = '\}';
    

    /**
     * Der komplette Pfad der Templatedatei.
     *
     * @access protected
     * @var    string
     */
    protected $templateFile = "";
    
    /**
     * Der komplette Pfad der Sprachdatei.
     *
     * @access protected
     * @var    array
     */
    protected $languageFiles = array();
    
    /**
     * Dateiname der Haupttemplate-Datei
     *
     * @access protected
     * @var    string
     */
    protected $mainTemplate = "";

    /**
     * Namen der include Templates
     *
     * @access protected
     * @var    array
     */
    protected $incTemplates = array();

    /**
     * Dateinamen der Templatedateien
     *
     * @access protected
     * @var    array
     */
    protected $systemTemplates = array("admin.tpl", "install.tpl", "contents.tpl", "contents_edit.tpl");

	/**
     * Adminseite
     *
     * @access protected
     * @var    boolean
     */
    private $adminPageTpl = false;

	/**
     * Der Inhalt des Templates
     *
     * @access protected
     * @var    string
     */
    protected $template = "";

	/**
	 * Beinhaltet ein Array aus Platzhalternamen zum Ersetzen von Inhalten.
	 *
	 * @access public
     * @var    array
     */
	public $poolAssign = array();

    
	/**
     * Die Templatedatei bei Instanzierung über den Konstruktor öffnen
     *
     * @access    public
     * @param     string	$file Dateiname des Templates
     * @param     array		$incTemplate Dateinamen der Untertemplates (optional)
     * @param     string	$tplDir alternatives Template-Verzeichnis
     * @return    boolean
     */
    public function __construct($mainTemplate, $incTemplates = array(), $tplDir = "")
    {
	
        // Templatenamen zuweisen
        $this->mainTemplate = $mainTemplate;
        $this->incTemplates = $incTemplates;
		
		// Falls ein alternativer Template-Pfad angegeben ist
		if($tplDir != "" && is_dir(PROJECT_DOC_ROOT . '/' . $tplDir))
			$this->templateDir = $tplDir;

	}

	
	/**
     * Template Eigenschaften setzen
     *
     * @param     boolean	$adminPage Adminseite (default = false)
     * @param     string	$tplDir Templateordner (default = '')
     * @param     string	$langDir Sprachenordner (default = '')
     * @access    public
     * @return    boolean
     */
    public function setTemplate($adminPage = false, $tplDir = "", $langDir = "")
    {

        // Adminseite
        if(is_bool($adminPage))
            $this->adminPageTpl = $adminPage;
		
        // Template Ordner ändern
        if(!empty($tplDir))
            $this->templateDir = $tplDir;

        // Language Ordner ändern
        if(!empty($langDir))
            $this->languageDir = $langDir;
        
        return true;
		
    }


    /**
     * Template-Datei Inhalte laden
     *
     * @access    public
     * @param     boolean	$adminPage Adminseite (default = false)
     * @return    boolean
     */
    public function loadTemplate($adminPage = false)
    {
		
		// Falls true, Admin-Tpl
		$this->adminPageTpl	= $adminPage;
	
		// Template-Pfad bestimmen
		if(in_array($this->mainTemplate, $this->systemTemplates) || $this->adminPageTpl)
	    	$this->templateFile = SYSTEM_DOC_ROOT . '/themes/' . ADMIN_THEME . '/templates/' . $this->mainTemplate;
		else
	        $this->templateFile = PROJECT_DOC_ROOT . '/' . $this->templateDir . $this->mainTemplate;

        // Wenn ein Dateiname übergeben wurde, versuchen, die Datei zu öffnen
        if(!empty($this->templateFile)) {
            if($readTpl = file_get_contents($this->templateFile)) { 
				$this->template = $readTpl;
            } else {
                echo "Theme folder or template file not found: " . $this->templateFile;
            }
        }

        // Die methode replaceFuntions() aufrufen
        $this->replaceFunctions($this->incTemplates);
        
        return true;	
	
	}
	

    /**
     * Die Funktionen ersetzen
     *
     * @access    protected
     * @param     array $incTemplate Dateinamen der Untertemplates (optional)
     * @return    boolean
     */
    protected function replaceFunctions($incTemplates)
    {
		
        // Includes ersetzen ( {include file="..."} )
		if(count($incTemplates) > 0) {
						
			$replacement = "";
			
			foreach($incTemplates as $placeHolder => $incTemplate) {
				
				if($incTemplate != "") {
					
					if(in_array($incTemplate, $this->systemTemplates))
					   $tplPath = "system/themes/" . ADMIN_THEME . "/templates/";
					else
					   $tplPath = $this->templateDir;
					
					if(!$replacement = @file_get_contents(PROJECT_DOC_ROOT . '/' . $tplPath . $incTemplate)) {
						echo('<p class="alert alert-warning">Template not found: ' . $incTemplate . '.</p>');
						if(!$replacement = @file_get_contents(PROJECT_DOC_ROOT . '/' . $tplPath . 'standard.tpl'))
							die("Template not found: " . $incTemplate . ".");
					}
					
					$this->template = preg_replace("/".$this->leftDelimiterF . $placeHolder . $this->rightDelimiterF."/", $replacement, $this->template);
				}
			}
			
		}
    
        // Kommentare löschen
        $this->template = preg_replace("/".$this->leftDelimiterC."(.*)".$this->rightDelimiterC."/ism", "", $this->template);

        // Die methode replaceTplStaText() aufrufen
        $this->replaceTplStaText();
        
		return true;
		
    }  
      

    /**
     * Die Standard-Platzhalter ersetzen
     *
     * @access    public
     * @param     string $replace      Zu ersetzender Platzhalter
     * @param     string $replacement  Text, durch den der Platzhalter ersetzt werden soll
     * @return    boolean
     */
    public function assign($replace, $replacement)
    {
	
        $this->template = str_replace($this->leftDelimiter.$replace.$this->rightDelimiter, $replacement, $this->template);
        
		return true;
		
    }

        
	/**
	 * Die statischen Text-/Sprachvariablen ersetzen
	 *
	 * @access    public
	 * @return    boolean
	 */
    public function replaceTplStaText()
    {
		
		if(!parent::$phMode) { // Falls Platzhalterersetzung nicht ausgeschaltet ist
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."([A-Za-z0-9_-]+)\:([A-Za-z0-9_-]+)".$this->rightDelimiterS."/ism", array($this, 'staTextCallback'), $this->template);
		}
		else {
			// Falls ph-Modus angeschaltet ist, Platzhalter nur für Systeminterne Sprachbausteine ersetzen
			$this->template = preg_replace_callback("/".$this->leftDelimiter."(e_[A-Za-z0-9_-]+)\:([A-Za-z0-9_-]+)".$this->rightDelimiterS."/ism", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(title)\:(paste|new|edit[A-Za-z0-9]*|goto|copy[A-Za-z0-9]*|cut|moveup|movedown|move|status|hideelement|del[A-Za-z0-9]*|directedit|safechange|cancelchange|[A-Za-z0-9]{0,2}publish[A-Za-z0-9]+)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(nav)\:(admin[A-Za-z0-9_-]*)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(link)\:(newgall|editpage)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(common)\:(after)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(label)\:(alttag|titletag|takechange2)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(button)\:(admin[A-Za-z0-9_-]*|imgfolder|takechange|gallchoose)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(option|optgroup)\:([A-Za-z0-9_-]*)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."(conareas)\:([A-Za-z0-9_-]+)".$this->rightDelimiterS."/isum", array($this, 'staTextCallback'), $this->template);
			// Alle anderen Platzhalter markieren
			$this->template = preg_replace_callback("/".$this->leftDelimiterS."([A-Za-z0-9_-]+)\:([A-Za-z0-9_-]+)".$this->rightDelimiterS."/ism", array($this, 'placeholderCallback'), $this->template);
		}
		
		// Theme style Platzhalter
		$this->template = preg_replace_callback("/".$this->leftDelimiterT."([A-Za-z0-9_-]+)\:([A-Za-z0-9_-]+)".$this->rightDelimiterT."/ism", array($this, 'themePhCallback'), $this->template);
		
		return true;
		
    }
    
    
    /**
     * Das fertige Template ausgeben
     *
     * @access    public
     * @param     boolean $replace	Ersetzen von verbliebenen Platzhaltern (default = false)
	 * @return    boolean
     */
    public function getTemplate($replace = false)
    {
		
		// zunächst evtl. noch vorhandene Platzhalter entfernen...
		// Ausnahme ist "sitelink", da es in geschweiften Klammern in der db für interne Links verwendet wird
		if($replace) {
			$this->replaceTplStaText();
		}
		
		if($replace && !parent::$phMode) // Falls Ersetzung erfolgen soll und Platzhalterersetzung nicht ausgeschaltet ist
	        $this->template = preg_replace("/".$this->leftDelimiter."[^}^\$^#^\r^%][A-Za-z0-9_-]*(?<!sitelink|root)".$this->rightDelimiter."/ism", "", $this->template);
		else {
			// Falls ph-Modus angeschaltet ist, Platzhalter nur für Systeminterne Sprachbausteine ersetzen
	        $this->template = preg_replace("/".$this->leftDelimiter."e_[^}^\$^#^\r^%][A-Za-z0-9_-]*(?<!sitelink|root)".$this->rightDelimiter."/ism", "", $this->template);
		}
	
		// Icon Platzhalter ersetzen
		if(strpos($this->template, "{ico:"))
			$this->template = preg_replace_callback("/".$this->leftDelimiterIco."([A-Za-z0-9_-]+)".$this->rightDelimiterIco."/ism", array($this, 'themeIconCallback'), $this->template);
		
		// ... dann Template ausgeben
		return $this->template;
		
    }
    
    
    /**
     * Gibt Template file zurück
     *
     * @access    public
	 * @return    boolean
     */
    public function getTemplateFile()
    {
		
		return $this->templateFile;
		
    }
    
    
    /**
     * Überprüfung auf Vorschau-Theme
     *
     * @access    public
	 * @return    boolean
     */
    public static function checkThemePreview()
    {
		
		$previewTheme = Security::getCookie('previewTheme');
		
		// Überprüfen ob Cookie für Theme-Vorschau gesetzt ist
		if(!empty($previewTheme)
		&& is_dir(PROJECT_DOC_ROOT . '/themes/' . $previewTheme)
		&& $previewTheme != THEME
		)
		
			return true;
		
		else
		
			return false;
		
    }
    
    
    /**
     * Callback function für themePhCallback (preg_replace_callback)
     *
     * @access    private
	 * @return    string
     */
	private function themePhCallback($e)
	{
	
		if($e[1] === "class" && isset(parent::$styleDefs["$e[2]"]))
			return parent::$styleDefs["$e[2]"];
		if($e[1] === "icons" && isset(parent::$iconDefs["$e[2]"]))
			return parent::$iconDefs["$e[2]"];
		return "";
	
	}
    
    
    /**
     * Callback function für themeIconCallback (preg_replace_callback)
     *
     * @access    private
	 * @return    string
     */
	private function themeIconCallback($e)
	{

		if(isset(parent::$iconDefs["$e[1]"]))
			return parent::getIcon("$e[1]", "", "", "");
		return "";
	
	}
    
    
    /**
     * Callback function für replaceStaText (preg_replace_callback)
     *
     * @access    private
	 * @return    string
     */
	private function staTextCallback($e)
	{
	
		if(isset(parent::$staText["$e[1]"]["$e[2]"]))
			return parent::$staText["$e[1]"]["$e[2]"];
		if(parent::$phMode)
			return "i18n: unknown";
		return "";
	
	}
    
    
    /**
     * Callback function für placeholder (preg_replace_callback)
     *
     * @access    private
	 * @return    string
     */
	private function placeholderCallback($e)
	{
	
		return "<span class='PH-Keys' title='Keygroup:\t<strong>".$e[1]."</strong><br />Key:\t\t\t<strong>".$e[2]."</strong>'>&#123;s_".$e[1].":".$e[2]."&#125;</span>";
	
	}
}
