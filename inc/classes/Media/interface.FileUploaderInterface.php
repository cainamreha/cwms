<?php
namespace Concise;


/**
 * FileUploader Interface
 * 
 */

interface FileUploaderInterface
{

	/**
	 * Gibt Upload-Methode zurück
	 */
	public function getUploadMethod();

	/**
	 * Weist Upload Head Files zu
	 */
	public function assignHeadFiles($type = "");

	/**
	 * Gibt eine Upload Maske eines bestimmten Typs zurück
	 */
	public function getUploaderMask($uploadKind, $type = "", $index = "");

	/**
	 * Gibt Upload-Script zurück
	 */
	public function getUploadScript($targetElem, $type = "default", $noCache = false);

	/**
	 * Getter
	 */
	public function __get($property);

	/**
	 * Setter
	 */
	public function __set($property, $value);

}
