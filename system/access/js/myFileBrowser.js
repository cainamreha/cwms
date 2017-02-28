// Function myFileBrowser (tinyMCE 4)
function myFileBrowser(field_name, url, type, win) {

	//alert("Field_Name: " + field_name + ", nURL: " + url + ", nType: " + type + ", nWin: " + win); // debug/testing

    /* If you work with sessions in PHP and your client doesn't accept cookies you might need to carry
       the session name and session ID in the request string (can look like this: "?PHPSESSID=88p0n70s9dsknra96qhuk6etm5").
       These lines of code extract the necessary parameters and add them back to the filebrowser URL again. */
	
	$.getWaitBar();

	var srcInput	= $('#' + field_name);
	var inputID		= field_name.replace("mceu_", "").split("-")[0];
	var folder		= "";
	var myFileBrowser = null;
	var actEd 		= tinyMCE.activeEditor.id;
	
	
	if(!($('#myFileBrowser-' + actEd).length)){
		myFileBrowser = $('<div class="myFileBrowser"><div class="mediaList images" id="myFileBrowser-' + actEd + '" data-type="images"><span data-url="' + cc.httpRoot + '/system/access/listMedia.php?page=admin&action=list&type=" data-type="images" class="showListBox" style="display:none;" data-file="media/images/thumbs/"><input type="button" class="cc-button button" value="Bilderordner"></span><input type="hidden" name="myFileBrowserFile-' + actEd + '" id="myFileBrowserFile-' + actEd + '" value="" /></div></div>');
		
		$('body').append(myFileBrowser); // Filebrowser
		
	}else{
		myFileBrowser = $('#myFileBrowser-' + actEd).parent('.myFileBrowser');
	}
	
	var listBoxUrl		= cc.httpRoot + '/system/access/listMedia.php?page=admin&action=list&type=';
	
	// ListBox-Medientyp bestimmen
	if(typeof(type) != "undefined" && type != null){
	
		if(type == "image"){ // Bild
			mediaType	= "images";
			folder		= "images";
			$('#myFileBrowser-' + actEd).removeAttr('class').addClass('mediaList images').attr('data-type','images').children('.showListBox').attr('data-url',listBoxUrl + type).attr('data-file','media/' + folder + '/').attr('data-type','images');		
		}
		if(type == "media"){ // Media
			mediaType	= "video";
			folder		= "video";
			$('#myFileBrowser-' + actEd).removeAttr('class').addClass('mediaList ' + mediaType).attr('data-type','video').children('.showListBox').attr('data-url',listBoxUrl + mediaType).attr('data-file','media/' + folder + '/').attr('data-type','video');
		}
		if(type == "file"){ // Link
			mediaType	= "links";
			$('#myFileBrowser-' + actEd).removeAttr('class').addClass('mediaList links').attr('data-type','links').children('.showListBox').attr('data-url', cc.httpRoot + '/system/access/listPages.php?page=admin&type=link').attr('data-type','links');
		}		
	}
	
	
	// Medienauswahl
	//myFileBrowser.find('img.preview, .mediaSelection, input.link').click(function(e){
	//$('body').off("click", '.listBox img.preview, .listBox .mediaSelection, .listBox input.link');
	//$('body').on("click", '.listBox img.preview, .listBox .mediaSelection, .listBox input.link', function(e){
	myFileBrowser.parents('body').off("click", 'div.listBox.filebrowser img.preview, div.listBox.filebrowser .mediaSelection, div.listBox.filebrowser button.link');
	//myFileBrowser.unbind("click");
	myFileBrowser.parents('body').on("click", 'div.listBox.filebrowser img.preview, div.listBox.filebrowser .mediaSelection, div.listBox.filebrowser button.link', function(e){
		
		e.preventDefault();
		e.stopPropagation();
		
		var myFile		= "";
		
		// Falls Link
		if($(this).hasClass('link')){
			type	= "link";
			myFile	= $(this).val() + '.html';
		}else{
		// Andernfalls Image
		if(type == "image"){
			if($(this).hasClass('imageSelection')){
				myFile = $(this).closest('.listItem').children('div.previewBox').children('img').attr('data-img-src');
			}else{
			if($(this).hasClass('filesSelection')){
				myFile = $(this).attr('data-path');
			}else{
				myFile = $(this).attr('data-img-src');
			}}
		}else{
		// Falls Video/Audio
			myFile = $(this).closest('.listItem').children('a').attr('href');
		}}
		
		
		// Source-Input setzen
		if(srcInput != null){
		
			srcInput.val(myFile);
			
			// Alt-Input setzen, falls Bild
			if(type == "image"){
			
				var altInput	= parseInt(parseInt(inputID) + 2);
				altInput		= $('*[id="mceu_' + altInput + '"]');
			
				if(altInput.val() == "" && !altInput.is('*[aria-label="Width"]')){
					var splitInput		= myFile.split("/");
					var myAlt			= splitInput[parseInt(splitInput.length - 1)];
					altInput.val(myAlt);
				}
			}
			
			// Linktext setzen, falls Link
			if(type == "link"){
			
				var linkInputID		= parseInt(parseInt(inputID) + 1);
				var linkInput		= $('*[id="mceu_' + linkInputID + '"]');

				if(linkInput.val() == "" && !linkInput.is('*[aria-label="Width"]')){
					var splitInput		= myFile.split("/");
					var myLinkText		= splitInput[parseInt(splitInput.length - 1)];
					myLinkText			= myLinkText.split(".")[0];
					linkInput.val(myLinkText);
				}
			}
		}
		
		// ListBox schließen
		$('div.listBox').not('.gallery').remove();
		$('div.dimDiv').not('.gallery').remove();
		
		// Ggf. pimg entfernen
		if($(".pimg").length){
			$(".pimg").remove();
		}
		
		return false;
	});


	// ListBox öffnen
	myFileBrowser.children('#myFileBrowser-' + actEd).children('.showListBox').click();

	
    return false;

} // Ende myFileBrowser
