(function($){

	$.setUploadifyLBFiles = function(){
						   
		var ln				= conciseCMS.ln;
		var selFiles		= 0;
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
		sizeLimit			= Math.min(104857600, maxUploadSize);
		

		// Dateiupload von ListBox aus
		if($('.uploadSingleFile').length){
		
			// Upload-Box Togglen
			$('.listBox .toggleUploadBox').bind('click', function(){
				$(this).closest('.actionBox').next('div.uploadBox').slideToggle();
				$(this).closest('.actionBox').siblings('div.newFolderBox').slideToggle();
				$(this).closest('.innerListBox').css('height','auto');
			});
		
			// Upload starten
			$('.uploadSingleFile').bind('click', function(){
				
				// Bildgrößenberechnung
				if($(this).siblings('div.scaleImgBox').children('#scaleImgDiv').is(':visible')) {
					var imgWidth	= $(this).siblings('div.scaleImgBox').find('#imgWidth').val();
					var imgHeight	= $(this).siblings('div.scaleImgBox').find('#imgHeight').val();
				} else {
					var imgWidth	= 0;
					var imgHeight	= 0;
				}
				
				var fileType = 'all';
				
				folder = $(this).closest('div.listBox').find('.listBoxHeader').html().split(" ")[2];
		
				var altFolder	= "";
				
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
			
				// Loading-Gif anzeigen
				if($('div#uploadFileQueue').children('div.uploadifyQueueItem').length){
					$(this).before('<img src="' + loadingImg.src + '" class="loadingImg-submit loading" />');
					$(this).attr('disabled','disabled');
				}
				
				if(strpos(folder, "media/files/", 0) === 0){
					altFolder	= folder;
				}
		
				$('#uploadFile').uploadifySettings('folder', folder);
				$('#uploadFile').uploadifySettings('scriptData', {'type' : fileType,'imgWidth' : imgWidth,'imgHeight' : imgHeight,'altFolder' : altFolder});
		
				$(this).siblings('#uploadFile').uploadifyUpload();
			});
			
			
			$('#uploadFile').uploadify({
				'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
				'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
				'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
				'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_listbox_' + cc.adminLang + '.png',
				'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
				'scriptData'		: {'type' : 'all','imgWidth' : imgWidth,'imgHeight' : imgHeight,'response' : 'filename'},
				'multi'				: true,
				'auto'				: false,
				'width'				: 250,
				'height'			: 30,
				'fileDesc'			: 'Dateitypen (*.jpg, *.png, *.gif, *.svg, *.doc, *.pdf, *.zip, *.m4v, *.mp4, *.wmf, *.mpeg, *.mpg, *.avi, *.ra, *.ram, *.mov, *.qt, *.ogv, *.webm, *.swf, *.flv, *.f4v, *.mp3)',
				'fileExt'			: '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.doc;*.pdf;*.zip;*.m4v;*.mp4;*.wmf;*.mpeg;*.mpg;*.avi;*.ra;*.ram;*.mov;*.qt;*.ogv;*.webm;*.swf;*.flv;*.f4v;*.mp3',
				'folder'			: 'media/files',
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
										parObj.uploadifySettings('scriptData', {'type' : 'all','imgWidth' : imgWidth,'imgHeight' : imgHeight,'response' : 'filename'}, true);
										$('#uploadFileUploader').addClass('opacity50');
										$('.uploadSingleFile').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000).removeAttr('disabled');
										$(this).closest('.innerListBox').css('height','auto');									
									  },
				'onCancel'       	: function(event, queueID, fileObj, response, data) {
										$('img.loading').remove();
										$('.uploadSingleFile').hide();
										$('#uploadFileUploader').removeClass('opacity50');
									  },
				'onComplete'        : function(event, queueID, fileObj, response, data) {
										if(response != "0") {
											uppedFiles.push(response);
										}
									  },
				'onAllComplete'     : function(event, data) {
										if(data.errors > 0) {
											jAlert(ln.uploaderror, ln.alerttitle);
										}
										parObj.closest('.mediaList').prepend('<input type="hidden" name="recentUploads" class="recentUploads" value="' + uppedFiles.join(",") + '" />').children('.showListBox').click().click();
									  },
				'sizeLimit'			: sizeLimit,
				'simUploadLimit'	: 5
			});
		}
	};
})(jQuery);
