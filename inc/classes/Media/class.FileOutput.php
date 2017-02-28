<?php
namespace Concise;



/**
 * Klasse FileOutput
 * 
 */

class FileOutput
{

	private static $encryptFiles	= array("doc",
											"audio",
											"video"
									  );
	
 	/**
	 * FileOutput
	 * 
	 * @param	string	$file 		File name
	 * @param	string	$type		Medien-Typ
	 * @param	string	$folder		File folder (default = '')
	 * @param	string	$page		Seiten-URL-Pfad (default = '')
	 * @access	public
	 */	 
	public static function getFileHash($file, $type, $folder = "", $page = "")
	{
	
		if($type != "user"
		&& !in_array($type, self::$encryptFiles)
		)
			return $folder . $file;
		
		require_once(PROJECT_DOC_ROOT."/inc/classes/Modules/class.myCrypt.php"); // Klasse myCrypt einbinden
		
		// myCrypt Instanz
		$crypt = new myCrypt();
		
		// Encrypt Name
		$file = $crypt->encrypt($file);

		switch($type) {
		
			case "user":
				$prefix	= "_user/";
				break;
		
			case "doc":
				$prefix	= "_doc-";
				break;
		
			default:
				$prefix	= "_file-";
				break;
			
		}
		
		$file	= ($page != "" ? $page . '/' : '') . $prefix . $file;
		
		return $file;
	
	}


	
	/**
	 * Gibt den Icon key einer Datei zurück
	 * 
	 * @param	string Fileextension
	 * @access	public
	 * @return	string
	 */
	public static function getFileIconKey($type)
	{
	
		$icon	= "";
		
		switch($type) {
		
			case "pdf";
				$icon = "filepdf";
				break;
			
			case "odt";
			case "doc";
			case "docx";
				$icon = "fileword";
				break;
			
			case "jpg";
			case "jpeg";
			case "png";
			case "gif";
			case "bmp";
			case "ico";
				$icon = "fileimg";
				break;
			
			case "swf";
			case "flv";
			case "f4v";
				$icon = "fileflash";
				break;
			
			case "mp4";
			case "m4v";
			case "ogv";
			case "webm";
			case "mov";
			case "mpeg";
			case "avi";
				$icon = "filevideo";
				break;
			
			case "mp3";
			case "ogg";
			case "oga";
				$icon = "fileaudio";
				break;
			
			case "zip";
				$icon = "filezip";
				break;
			
			default:
				$icon = "filedoc";
				break;
			
		}		
		return $icon;
		
	}


	
	/**
	 * Gibt ein Datei-Icon zurück
	 * 
	 * @param	string Fileextension
	 * @param	string Icon folder
	 * @access	public
	 * @return	string
	 */
	public static function getFileIcon($type, $iconFolder = "")
	{

		if(empty($iconFolder))
			$iconFolder	= SYSTEM_IMAGE_DIR;
		
		if(file_exists($iconFolder . '/icon_' . $type . '.png'))
			return 'icon_' . $type . '.png';
				
		switch($type) {
		
			case "pdf";
				$icon = "icon_pdf.png";
				break;
			
			case "odt";
			case "doc";
			case "docx";
				$icon = "icon_doc.png";
				break;
			
			case "jpg";
			case "jpeg";
			case "png";
			case "gif";
			case "bmp";
			case "ico";
				$icon = "icon_image.png";
				break;
			
			case "swf";
			case "flv";
			case "f4v";
				$icon = "icon_flash.png";
				break;
			
			case "mp4";
			case "m4v";
			case "webm";
			case "ogv";
			case "mov";
			case "mpeg";
			case "avi";
				$icon = "icon_video.png";
				break;
			
			case "mp3";
				$icon = "icon_mp3.png";
				break;
			
			case "ogg";
			case "oga";
				$icon = "icon_ogg.png";
				break;
			
			case "zip";
				$icon = "icon_zip.png";
				break;
			
			default:
				$icon = "icon_file.png";
				break;
			
		}		
		return $icon;
	
	}

}
