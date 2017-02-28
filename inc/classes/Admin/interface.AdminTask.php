<?php
namespace Concise;


/**
 * Elements Interface
 * 
 */

interface AdminTask
{

	/**
	 * Gibt eine Adminseite zurück
	 * 
	 * @access	public
     * @return  string
	 */
	public function getTaskContents($ajax = false);
	
}
