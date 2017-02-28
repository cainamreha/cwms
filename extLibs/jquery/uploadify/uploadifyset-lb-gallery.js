(function($){

	$.setUploadifyLBGallery = function(){
						   
		var ln				= conciseCMS.ln;
		var selFiles 		= 0;
		var selFiles2 		= 0;
		var selFiles3 		= 0;
		var selFiles4 		= 0;
		var checkFile		= 0;
		var checkFile2		= 0;
		var checkFile3		= 0;
		var checkFile4		= 0;
		var fileExist		= "";
		var fileExist2		= "";
		var fileExist3		= "";
		var fileExist4		= "";
		var fileExistStr1	= ln.fileexists1;
		var fileExistStr2	= ln.fileexists2;
		var fileNames 		= [];
		var fileType		= "";
		var folder			= "";
		var sizeLimit		= 104857600;
		var maxUploadSize	= 104857600;
		var imgWidth		= "";
		var imgHeight		= "";
		var duplicateFiles	= [];
		var uppedFiles		= [];
		var uppedFiles2		= [];
		var uppedFiles3		= [];
		var uppedFiles4		= [];
		var uppedFilesStr	= "";
		var checkedFiles	= [];
		var checkedFiles2	= [];
		var checkedFiles3	= [];
		var checkedFiles4	= [];
		var parObj;

		// Loading-Platzhalterbild
		var loadingImg = new Image();
		loadingImg.src = cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/loading.gif';
		
		// Maximale Dateigröße festlegen
		maxUploadSize		= $('#maxUploadSize').val();
		sizeLimit 			= Math.min(104857600, maxUploadSize);
		



		// Dateiupload von ListBox aus
		if($('.uploadGalleryFiles').length){
			
			
			// Dateiupload von ListBox aus
			if(!$('#uploadifyGalleryUploader').length){
			
			
			// Upload-Box Togglen
			$('.listBox .toggleUploadBox').bind('click', function(){
				$(this).closest('.actionBox').next('div.uploadBox').slideToggle();
				$(this).closest('.innerListBox').css('height','auto');
			});
		
			// Upload starten
			$('.uploadGalleryFiles').bind('click', function(){
				
				// Bildgrößenberechnung
				if($(this).siblings('div.scaleImgBox').children('#scaleImgDiv').is(':visible')) {
					imgWidth	= $(this).siblings('div.scaleImgBox').find('#imgWidth').val();
					imgHeight	= $(this).siblings('div.scaleImgBox').find('#imgHeight').val();
				} else {
					imgWidth	= 0;
					imgHeight	= 0;
				}
				
				fileType		= 'gallery';
				
				folder			= $(this).closest('div.innerListBox').find('h2').html().split(" ")[2];
				folder			= folder.replace(/ /g, "_");

				
				function strpos (haystack, needle, offset) {
				  // http://kevin.vanzonneveld.net
				  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
				  // +   improved by: Onno Marsman
				  // +   bugfixed by: Daniel Esteban
				  // +   improved by: Brett Zamir (http://brett-zamir.me)
				  // *     example 1: strpos('Kevin van Zonneveld', 'e', 5);
				  // *     returns 1: 14
				  var i = (haystack + '').indexOf(needle, (offset || 0));
				  return i === -1 ? false : i;
				}
				
				if(strpos(folder, "media/galleries/", 0) === 0){
					folder	= folder.replace(/media\/galleries\//, "", folder);
				}

			
				// Loading-Gif anzeigen
				if($('div#uploadifyGalleryQueue').children('div.uploadifyQueueItem').length){
					$(this).before('<img src="' + loadingImg.src + '" class="loadingImg-submit loading" />');
					$(this).attr('disabled','disabled');
				}

				
				var gallName	= "media/galleries/" + folder;
		
				$('#gallName').val(folder);
				
				$('#uploadifyGallery').uploadifySettings('folder', gallName);
				$('#uploadifyGallery').uploadifySettings('scriptData', {'type' : fileType, 'imgWidth' : imgWidth, 'imgHeight' : imgHeight, 'altFolder' : gallName});
				
				$(this).siblings('#uploadifyGallery').uploadifyUpload();
			});
			
			
			$('#uploadifyGallery').uploadify({
				'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
				'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
				'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
				'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_listbox_' + cc.adminLang + '.png',
				'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
				'scriptData'		: {'type' : 'gallery','imgWidth' : imgWidth,'imgHeight' : imgHeight},
				'multi'				: true,
				'auto'				: false,
				'width'				: 250,
				'height'			: 30,
				'fileDesc'			: 'Dateitypen (*.jpg, *.png, *.gif, *.svg)',
				'fileExt'			: '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.JPG;*.JPEG;*.SVG',
				'folder'			: 'media/galleries',
				'onSelectOnce'      : function(event, queueID, fileObj) {
										
										parObj = $(this);
										
										imgWidth	= parObj.siblings('div.scaleImgBox').find('#imgWidth').val();
										imgHeight	= parObj.siblings('div.scaleImgBox').find('#imgHeight').val();
										
										if(typeof(imgWidth) == "undefined" || imgWidth == "") {
											imgWidth = 0;
										}
										if(typeof(imgHeight) == "undefined" || imgHeight == "") {
											imgHeight = 0;
										}
										parObj.uploadifySettings('scriptData', {'type' : 'gallery','imgWidth' : imgWidth,'imgHeight' : imgHeight}, true);
										$('#uploadifyGalleryUploader').addClass('opacity50');
										$('.uploadGalleryFiles').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000).removeAttr('disabled');
										$(this).closest('.innerListBox').css('height','auto');
									  },
				'onCancel'       	: function(event, queueID, fileObj, response, data) {
										$('img.loading').remove();
										$('.uploadGalleryFiles').hide();
										$('#uploadifyGalleryUploader').removeClass('opacity50');
									  },
				'onComplete'        : function(event, queueID, fileObj, response, data) {
										if(response == 1 || response == "1") {
											uppedFiles.push(fileObj["name"]);
										}
									  },
				'onAllComplete'     : function() {
				
										if(uppedFiles != "") {
											var formAction	= $('#gallFiles').closest('form').attr('action');
											var filesInput	= $('<input type="hidden" name="uppedFiles" id="uppedFiles" value="' + uppedFiles + '" />');
											$('#gallFiles').parent('form').attr('action', formAction + "&edit_gall=" + folder).append(filesInput);
											filesInput.closest('form').submit();
										}
										uppedFiles = [];
										$('img.loading').remove();
										//var formAction	= $('#gallFiles').closest('form').attr('action');
										//$('#gallFiles').closest('form').attr('action', formAction + "&edit_gall=" + folder).submit(false);
									  },
				'sizeLimit'			: sizeLimit,
				'simUploadLimit'	: 5
			});

		}
		
		}
	};
})(jQuery);
