<?php
namespace Concise;


/**
 * UnZip Klasse
 * 
 *
 */

class UnZip
{  

	private $unzipEmptyFolders		= true;
	private $unzipEmptyFiles		= true;
	private $zipDir					= "";
	private $zipFile				= "";
	public $targetRootDir			= "";
	public $archive					= "";
	public $result					= array("success"	=> array(),
											"error"		=> array()
											);

	
	// Unzip Constructor
	public function __construct()
	{
	
		$this->zipDir			= TEMP_DIR;
		$this->targetRootDir	= PROJECT_DOC_ROOT. '/';

	}
	

	// Unzip
	public function unZip($zipFile, $zipDir = "", $deleteArchiveOnSuccess = true)
	{
	
		if($zipDir != "")
			$this->zipDir	= $zipDir;
			
		if(!is_dir($this->zipDir))
			$this->mkdirr($this->zipDir, 0755);
		
		
		$this->zipFile	= $zipFile;
		$this->archive	= $this->zipDir . $this->zipFile;

		
		// Unzip the archive
		$unzip = $this->runUnZip();
		
		
		// Ergebnissarray
		if(empty($unzip))
		{
			$this->result['success'][] = 'Alle Dateien wurden erfolgreich entpackt.';
		}
		else
		{
			$this->result['error'][] = 'Es sind Fehler beim Entpacken aufgetreten:<br /><br />'.$unzip;
			return $this->result;
		}

		// Set permission rights
		$permission_rights = $this->filelist($this->zipDir, $this->zipFile);

		if(empty($permission_rights))
		{
			$this->result['success'][] = 'Datei- und Verzeichnisrechte vollständig gesetzt.';
		}
		else
		{
			$this->result['error'][] = 'Es sind Fehler aufgetreten:<br />'.$permission_rights;
			return $this->result;
		}

		// Delete zip archive if no errors
		if($deleteArchiveOnSuccess)
			$this->deleteArchive();
				
		return $this->result;
	
	}
	

	// Unzip the archive - optimized version of http://www.php.net/manual/de/ref.zip.php#92941
	protected function runUnZip()
	{
	
		// Falls Datei nicht vorhanden
		if(!file_exists($this->archive))
			return('Es wurde keine Datei mit dem Namen '.$this->zipFile.' gefunden.<br />');

		$zip = zip_open($this->archive);

		if(!$zip)
			return('Datei konnte nicht verarbeitet werden: '.$this->zipFile.'<br />');
		

		$error = '';

		while($zip_entry = zip_read($zip))
		{
			$zdir	= $this->targetRootDir . dirname(zip_entry_name($zip_entry));
			$zname	= zip_entry_name($zip_entry);

			if(!zip_entry_open($zip, $zip_entry, 'r'))
			{
				$error .= 'Datei konnte nicht verarbeitet werden: '.$zname.'<br />';
				continue;
			}

			if(!is_dir($zdir))
			{
				$this->mkdirr($zdir, 0755);
			}

			$zip_filesize = zip_entry_filesize($zip_entry);

			if(empty($zip_filesize))
			{
				if(substr($zname, -1) == '/')
				{
					if($this->unzipEmptyFolders)
					{
						$this->mkdirr($zname, 0755);
					}

					unset($zdir);
					unset($zname);

					continue;
				}
				else
				{
					if(!$this->unzipEmptyFiles)
					{
						unset($zdir);
						unset($zname);

						continue;
					}
				}
			}

			$content = zip_entry_read($zip_entry, $zip_filesize);

			if(@file_put_contents($this->targetRootDir . $zname, $content) === false)
			{
				$error .= 'Datei konnte nicht verarbeitet werden: '.$zname.'<br />';
			}

			zip_entry_close($zip_entry);

			unset($zdir);
			unset($zname);
		}

		zip_close($zip);

		return $error;
	}

	// Create new folders for the archive files
	protected function mkdirr($pn, $mode = null)
	{
		if(is_dir($pn) OR empty($pn))
		{
			return true;
		}

		$pn = str_replace(array('/', ''), DIRECTORY_SEPARATOR, $pn);

		if(is_file($pn))
		{
			trigger_error('$this->mkdirr() File exists', E_USER_WARNING);
			return false;
		}

		$next_pathname = substr($pn, 0, strrpos($pn, DIRECTORY_SEPARATOR));

		if($this->mkdirr($next_pathname, $mode))
		{
			if(!file_exists($pn))
			{
				return mkdir($pn, $mode);
			}
		}

		return false;
	}

	// Set correct permission rights - folder 0755, files 0644
	protected function filelist($startdir, $archive = false, $error = false)
	{
		if(empty($error))
		{
			$error = '';
		}

		$ignoredDirectory = array('.', '..', 'unzip.php', 'maintenance.ini', 'pl_latest_version.txt');

		if(!empty($archive))
		{
			$ignoredDirectory[] = $archive;
		}

		if(@is_dir($startdir))
		{
			if($dh = opendir($startdir))
			{
				while(($archive = readdir($dh)) !== false)
				{
					if(!(array_search($archive, $ignoredDirectory) > -1))
					{
						if(@is_dir($startdir.$archive.'/'))
						{
							$error = $this->filelist($startdir.$archive.'/', 0, $error);
						}

						$filetype = filetype($startdir.$archive);

						if($filetype == 'dir')
						{
							if(chmod($startdir.$archive, 0755) == false)
							{
								$error .= 'Rechte konnten nicht gesetzt werden: '.$startdir.$archive.'<br />';
							}
						}
						elseif($filetype == 'file')
						{
							if(chmod($startdir.$archive, 0644) == false)
							{
								$error .= 'Rechte konnten nicht gesetzt werden: '.$startdir.$archive.'<br />';
							}
						}
					}
				}

				closedir($dh);
			}
		}

		return $error;
	}

	
	
	// Delete archive
	protected function deleteArchive()
	{

		if(unlink($this->archive) == true)
		{
			$this->result['success'][] = 'Installationsarchiv erfolgreich gelöscht.';
		}
		else
		{
			$this->result['error'][] = 'Archiv konnte nicht gelöscht werden.';
		}
	}
}
