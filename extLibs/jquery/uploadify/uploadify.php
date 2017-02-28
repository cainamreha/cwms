<?php
/*
Uploadify v2.1.0
Release Date: August 24, 2009

Copyright (c) 2009 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

require_once("../../../inc/common.php");
require_once(PROJECT_DOC_ROOT . "/inc/classes/Media/class.Files.php");

if (!empty($_FILES)) {
	$tempFile		= $_FILES['Filedata']['tmp_name'];
	$folder			= $_REQUEST['folder'];
	
	if(isset($_REQUEST['altFolder']) 
	&& trim($_REQUEST['altFolder']) != ""
	) {
		$altFolder	= trim($_REQUEST['altFolder']);		
	}
	else {
		$altFolder	= "";
	}
	
	$type		= $_REQUEST['type'];
	$imgWidth	= 0;
	$imgHeight	= 0;
	$response = "";
	
	if(isset($_REQUEST['imgWidth']))
		$imgWidth = $_REQUEST['imgWidth'];
	if(isset($_REQUEST['imgHeight']))
		$imgHeight = $_REQUEST['imgHeight'];
	
	if($imgWidth < MIN_IMG_SIZE || $imgWidth > MAX_IMG_SIZE)
		$imgWidth = 0;
	if($imgHeight < MIN_IMG_SIZE || $imgHeight > MAX_IMG_SIZE)
		$imgHeight = 0;
	
	
	if(isset($_REQUEST['response']))
		$response = $_REQUEST['response'];

	$targetFile =  Concise\Files::getValidFileName($_FILES['Filedata']['name']);
	$fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
	$fileTypes  = str_replace(';','|',$fileTypes);
	$typesArray = explode('|',$fileTypes);
	$fileParts  = pathinfo($_FILES['Filedata']['name']);

	
	switch($type) {
		
		case "image":
			$fileType = "image";
			break;
		
		case "gallery":
			$fileType = "image";
			break;
		
		case "doc":
			$fileType = "doc";
			break;
		
		case "video":
			$fileType = "video";
			break;
		
		case "audio":
			$fileType = "audio";
			break;
		
		default:
			$fileType = Concise\Files::getFileType($targetFile); // Falls Typ unbekannt, Typ ermitteln;
		
	}
	

	if (in_array($fileParts['extension'],$typesArray)) {
	
		if(Concise\Files::uploadFile($targetFile, $tempFile, $altFolder, $fileType, $imgWidth, $imgHeight, true, "")) { // Datei-Upload starten
			if($response == "filename")
				echo Concise\Files::getValidFileName($targetFile);
			else
				echo "1";
		}
		else
			echo "0"; //"An error occurred. File was not uploaded";
	
	} else {
	 	echo 'Invalid file type.';
	}
}
else {
 	echo 'no file.';
}
?>