<?php
namespace Concise;


/**
 * Elements ConfigElements
 * 
 */

interface ConfigElements
{

	/**
	 * Gibt Inhaltselement-Konfigurations Html zurück
	 */
	public function getConfigElement($a_POST);

	/**
	 * POST-Array auslesen
	 */
	public function evalElementPost();

	/**
	 * DB-Updatestr generieren
	 */
	public function makeUpdateStr();

	/**
	 * Parameter (default) setzen
	 */
	public function setParams();

	/**
	 * Element-Formular generieren
	 */
	public function getCreateElementHtml();

}
