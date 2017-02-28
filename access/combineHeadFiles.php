<?php 
namespace Concise;


    /************************************************************************ 
     * CSS and Javascript Combinator 0.5
     * Copyright 2006 by Niels Leenheer
	 *
     * Adapted by Alexander Hermani 2015
     * 
     * Permission is hereby granted, free of charge, to any person obtaining 
     * a copy of this software and associated documentation files (the 
     * "Software"), to deal in the Software without restriction, including 
     * without limitation the rights to use, copy, modify, merge, publish, 
     * distribute, sublicense, and/or sell copies of the Software, and to 
     * permit persons to whom the Software is furnished to do so, subject to 
     * the following conditions: 
     *  
     * The above copyright notice and this permission notice shall be 
     * included in all copies or substantial portions of the Software. 
     * 
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
     * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
     * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
     * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
     * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
     * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
     * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
     */

// checkSiteAccess
require_once __DIR__."/../inc/checkSiteAccess.inc.php";

// Projektpfade und Syste-Einstellungen
include_once __DIR__."/../inc/settings.php";

//Fehlerbehandlungsklasse
require_once PROJECT_DOC_ROOT."/inc/classes/ErrorHandling/class.ErrorHandling.php";

	
class CombineCode
{

	public $cache		= true;
	public $minify		= true;
	public $encode		= true;
	private $type		= "";

	public function combine($type = "css", $files = "")
	{

		$this->type		= $type;
		$this->minify	= MINIFY_CSS;
		$this->encode	= GZIP_CSS;
		$cssdir   		= PROJECT_DOC_ROOT; 
		$jsdir   	 	= PROJECT_DOC_ROOT; 
		$cachedir 		= PROJECT_DOC_ROOT . '/' . CACHE_DIR;

		// if admin page
		if(isset($_GET['page']) && $_GET['page'] == "admin")
			$cachedir	= PROJECT_DOC_ROOT . '/system/themes/' . ADMIN_THEME . '/cache/';

		// make cache dir if not exists
		if(!is_dir($cachedir))
			mkdir($cachedir, 0755);
		
		if(isset($_GET['type']) && isset($_GET['files'])) {
			$this->type	= $_GET['type'];
			$filesStr	= base64_decode($_GET['files']);
			$elements	= explode(',', $filesStr);
		}
		
		// return false if no files specified
		if($elements == null || count($elements) == 0)
			return false;
		
		
		// Determine the directory and type we should use 
		switch ($this->type) { 
			case 'css': 
				$base = realpath($cssdir); 
				$base = $cssdir; 
				break; 
			case 'javascript': 
				$base = realpath($jsdir); 
				$base = $jsdir; 
				break; 
			default: 
				header ("HTTP/1.0 503 Not Implemented"); 
				exit; 
		}; 

		 
		// Determine last modification date of the files 
		$lastmodified = 0; 
		while (list(,$element) = each($elements)) { 
			#$path = realpath($base . '/' . $element); 
			$path = $base . '/' . $element;
			#$path = $element;
		 
			if (($this->type == 'javascript' && substr($path, -3) != '.js') ||  
				($this->type == 'css' && substr($path, -4) != '.css')) { 
				header ("HTTP/1.0 403 Forbidden"); 
				exit;     
			} 
		 
			if (substr($path, 0, strlen($base)) != $base || !file_exists($path)) { 
				header ("HTTP/1.0 404 Not Found"); 
				exit; 
			} 
			 
			$lastmodified = max($lastmodified, filemtime($path)); 
		} 
		 
		// Send Etag hash 
		$hash = $lastmodified . '-' . md5($_GET['files']); 
		header ("Etag: \"" . $hash . "\""); 
		 
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&  
			stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"')  
		{ 
			// Return visit and no modifications, so do not send anything 
			header ("HTTP/1.0 304 Not Modified"); 
			header ('Content-Length: 0'); 
		}  
		else  
		{ 
			// First time visit or files were modified 
			if ($this->cache)  
			{ 
				// Determine supported compression method
				$gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
				$deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');
		
				// Determine used compression method
				$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');
		
				// Check for buggy versions of Internet Explorer
				if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') && 
					preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
					$version = floatval($matches[1]);
					
					if ($version < 6)
						$encoding = 'none';
						
					if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) 
						$encoding = 'none';
				}
				
				// If no encoding
				if(!$this->encode)
					$encoding = "none";
				
				// Try the cache first to see if the combined files were already generated 
				$cachefile = 'cache-' . $hash . '.' . $this->type . ($encoding != 'none' ? '.' . $encoding : '');
				
				if (file_exists($cachedir . $cachefile)) {
				
					if ($fp = fopen($cachedir . $cachefile, 'rb')) { 

						if ($encoding != 'none') {
							header ("Content-Encoding: $encoding");
							#header ("Content-Encoding: gzip");
						}
						
						#header("Cache-Control: no-cache, must-revalidate"); 
						#header("Pragma: no-cache"); //keeps ie happy
						
						header ("Content-Type: text/" . $this->type); 
						header ("Content-Length: " . filesize($cachedir . $cachefile));

						fpassthru($fp); 
						fclose($fp);
						exit; 
					} 
				} 
			} 
		 
			// Get contents of the files 
			$contents = ''; 
			reset($elements);
			
			$topArr		= array();
			
			while (list(,$element) = each($elements)) { 
				#$path = realpath($base . '/' . $element); 
				$path		= $base . '/' . $element;
				$url		= PROJECT_HTTP_ROOT . '/' . $element;
				#$path = $element;
				$fileCon	= file_get_contents($path);
				$fileCon	= self::removeComments($fileCon);
				$fileArr	= explode("\n", $fileCon);
				$lineArr	= array();
				
				foreach($fileArr as $line) {
					if(strpos($line, "@import") !== false)
						$topArr[] = $line . "\n";
					else
						$lineArr[] = $line . "\n";
				}

				$content	= implode("", $lineArr);
				$srcPath	= substr($url, 0, strrpos($url, "/"));
				$content	= preg_replace_callback("/(url\s?\(\s?[\"\']?)(?!(\s?[\"\']?\s?data\s?:?))/ism", function($m) use ($srcPath) { return $m[1] . $srcPath . "/"; }, $content) . "\n";
				
				$contents	.= $content;
			}
			
			if($this->minify)
				$contents	= self::minifyCode($contents);
			
			$contents = trim(str_ireplace("@charset \"utf-8\";", "", $contents));
			$contents = "@charset \"utf-8\";\n" . implode("", $topArr) . $contents;
			
			// Send Content-Type 
			header ("Content-Type: text/" . $this->type); 
			 
			if ($this->encode && isset($encoding) && $encoding != 'none') 
			{ 
				// Send compressed contents 
				$contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE); 
				$encoding = "gzip";
				header ("Content-Encoding: " . $encoding); 
				header ('Content-Length: ' . strlen($contents)); 
				echo $contents; 
			}  
			else  
			{ 
				// Send regular contents 
				header ('Content-Length: ' . strlen($contents)); 
				echo $contents; 
			} 

			// Store cache 
			if ($this->cache) { 
				if ($fp = fopen($cachedir . $cachefile, 'wb')) { 
					fwrite($fp, $contents); 
					fclose($fp); 
				} 
			} 
		}
	}

	public function removeComments($str)
	{

		// Remove comments
		$str = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
			
		return $str;
	}

	public function minifyCode($str)
	{
	
		// Falls Css
		if($this->type == "css")
			return $this->minifyCSS($str);
	
		// Falls JS
		if($this->type == "javascript")
			return $this->minifyJS($str);
	}

	public function minifyCSS($str)
	{

		// Remove comments
		$str = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
		 
		// Remove whitespace
		$str = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $str);
		$str = str_replace('  ', ' ', $str);
		 
		// Remove space after colons
		$str = str_replace(array(': '), ':', $str);
		$str = str_replace(array(', ', ' ,'), ',', $str);
		 
		// Remove space after semi-colons
		$str = str_replace('; ', ';', $str);
		 
		// Remove spaces
		$str = str_replace(array(' (', '( '), '(', $str);
		$str = str_replace(array(' )'), ')', $str);
		$str = str_replace(array(' {', '{ '), '{', $str);
		
		// Restore media "and"
		$str = str_replace('and(', 'and (', $str);
		#$str = str_replace(')and', ') and', $str);
		 
		// Remove trailing semi-colon
		$str = str_replace(array(' }', '} ', ';}'), '}', $str);
		
		return $str;
	}

	public function minifyJS($str)
	{
		
		return $str;
	}
    
    
    /**
     * Callback function für Pfadersetzungen (preg_replace_callback)
     *
     * @access    private
	 * @return    string
     */
	private function pathReplaceCallback($e)
	{
		$url = PROJECT_HTTP_ROOT;
		return $url.$e[1];
	
	}
}
$o_cc	= new CombineCode;
$o_cc->combine();
