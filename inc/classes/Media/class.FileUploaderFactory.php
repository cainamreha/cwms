<?php
namespace Concise;


require_once PROJECT_DOC_ROOT . "/inc/classes/Media/interface.FileUploaderInterface.php"; // FileUpload-Interface einbinden

/**
 * Klasse FileUploaderFactory
 * 
 */

class FileUploaderFactory extends Admin
{

	/**
	 * Instanziert ein Element-Objekt
	 * 
     * @param	string	$type			Inhaltselementen-Typ
     * @param	string	$options		Instanzierungsparameter
     * @param	string	$elementKind	Elementart (e.g. core/plugin)
	 * @access	public
     * @return  string
	 */
	public static function create($uploadMethod, $options, $DB, &$o_lng)
	{
	
		// Klassen-Pfad bestimmen
		switch($uploadMethod) {
		
			// Falls Plug-in
			case "default":
				$classPath	= PROJECT_DOC_ROOT . "/inc/classes/Media/class.FileUploader.php"; // Pfad zur FileUploader-Klasse
				$uploadMethod	= "FileUploader";
				break;
		
			// Falls Core-Inhaltselement
			default:
				$classPath	= PROJECT_DOC_ROOT . '/extLibs/jquery/' . $uploadMethod . '/class.' . ucfirst($uploadMethod) . '.php'; // Pfad zur FileUploader-Klasse
		}
			
		// Falls Datei nicht vorhanden
		if(file_exists($classPath)) {
			
			require_once $classPath; // FileUploader-Klasse einbinden
			
			$className	= 'Concise\\' . ucfirst(str_replace("-", "", $uploadMethod));

			$obj = new $className($options, $DB, $o_lng); // Objekt instanzieren und zurückgeben
				return $obj;
		}
		else
			throw new \Exception('<p class="error">File uploader {s_common:unknown}: <strong>' . $uploadMethod . '</strong><br /><br />{s_label:setfileupload}: <a href="' . ADMIN_HTTP_ROOT . '?task=settings#setmod" class="link">{s_text:settings} -> {s_header:setmod}</a></p>' . "\r\n");
	
	}	


	/**
	 * Rückgabe-Array eines ConfigElements
	 * 
	 * @access	public
     * @return  string
	 */
	public function makeOutputArray()
	{

		$outputArr	= array(	"output"	=> $this->output,
								"update"	=> $this->dbUpdateStr,
								"error"		=> $this->wrongInput,
								"textareas"	=> $this->textAreaCount
							);
	
		return $outputArr;
		
	}
	
	
	/**
	 * Upload-Methode ändern
	 * 
     * @param	string	$uploadMethod	Upload-Methode
	 * @access	public
     * @return  string
	 */
	public function changeDefaultUploadMethod($uploadMethod, $redirect)
	{
	
		if(!in_array($uploadMethod, Files::getPotUploadMethods()))
			return false;
	
		
		// Inhalte der Settings-Datei einlesen
		if(!$settings = @file_get_contents(PROJECT_DOC_ROOT . '/inc/settings.php')) {
			die("settings file not found");
			return false;
		}
		
		// Sicherungskopie anlegen
		copy(PROJECT_DOC_ROOT . '/inc/settings.php', PROJECT_DOC_ROOT . '/inc/settings.php.old');
		
		$settings = preg_replace("/'FILE_UPLOAD_METHOD',\"".FILE_UPLOAD_METHOD."\"/", "'FILE_UPLOAD_METHOD',\"$uploadMethod\"", $settings);

		if(!@file_put_contents(PROJECT_DOC_ROOT . '/inc/settings.php', $settings)) {
			$GLOBALS['_SESSION']['notice']	= "{s_javascript:settingserror}";
			die("could not write settings file");
		}
		else {
			$GLOBALS['_SESSION']['notice']	= "{s_notice:takechange}";
			header("location:" . $redirect);
			exit;
		}
	
	}
	
	
	/**
	 * Formular Upload-Methode ändern
	 * 
     * @param	string	$uploadMethod	Upload-Methode
	 * @access	public
     * @return  string
	 */
	public function getFormChangeDefaultUploadMethod($formAction)
	{
	
		$uploadMethods	= Files::getPotUploadMethods();
	
		// File upload method
		$output	=	'<form action="' . $formAction . '" method="post" name="uploadMethod" id="uploadMethod" class="">' . "\r\n" .
					'<label>{s_label:setfileupload}</label>' . "\r\n" .
					'<select name="setuploadmethod" id="setuploadmethod" class="selectUploadMethod autoSubmit">' . "\r\n";
							
		foreach($uploadMethods as $uploadM) {
			
			$output .='<option value="'.$uploadM.'"' . (FILE_UPLOAD_METHOD == $uploadM ? ' selected="selected"' : '') . '>'.$uploadM.'</option>' . "\r\n";
		}
		
		$output	.=	'</select>' . "\r\n" .
					'</form>' . "\r\n";
		
		return $output;
	
	}

}
