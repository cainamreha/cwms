(function($){

	$.setUploadifyLBFlash = function(){
						   
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
			
				// Loading-Gif anzeigen
				if($('div#uploadFileQueue').children('div.uploadifyQueueItem').length){
					$(this).before('<img src="' + loadingImg.src + '" class="loadingImg-submit loading" />');
					$(this).attr('disabled','disabled');
				}
				
				var fileType = 'flash';
				
				$('#uploadFile').uploadifySettings('scriptData', {'type' : fileType});
		
				$(this).siblings('#uploadFile').uploadifyUpload();
			});
			
			
			$('#uploadFile').uploadify({
				'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
				'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
				'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
				'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_listbox_' + cc.adminLang + '.png',
				'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
				'scriptData'		: {'type' : 'flash','response' : 'filename'},
				'multi'				: true,
				'auto'				: false,
				'width'				: 250,
				'height'			: 30,
				'fileDesc'			: ln.uploadflash + ' (*.swf, *.flv, *.f4v)',
				'fileExt'			: '*.swf;*.flv;*.f4v',
				'folder'			: 'media/flash',
				'onSelectOnce'      : function(event, queueID, fileObj) {
										
										parObj = $(this);
										
										parObj.uploadifySettings('scriptData', {'type' : 'flash','response' : 'filename'}, true);
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
