(function($){

	$.setUploadify = function(){
		
		// Uploadify-Skript
		if(!$(".fileUpload-uploadify").length){
			return false;
		}

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
		
		
		// Upload starten
		$('#uploadFiles').bind("click", function(){
			
			
			$('#imageFile, #docFile, #audioFile, #videoFile').uploadifySettings('sizeLimit', sizeLimit);
			
			// Bildgrößenberechnung
			if($('#imgWidth').is(':visible')) {
				var imgWidth	= $('#imgWidth').val();
				var imgHeight	= $('#imgHeight').val();
			} else {
				var imgWidth	= 0;
				var imgHeight	= 0;
			}
			
			// Falls files-Ordner gecheckt ist, aber kein Ordner angegeben, abbrechen
			if($('input.useFilesFolder').is(':checked') && $('input.filesFolder').val() == ""){
				jAlert(ln.choosefolder, ln.alerttitle);
				return false;
			}
			if($('input.useFilesFolder').is(':checked') && $('input.filesFolder').val() != ""){
				
				folder = 'media/files/' + $('input.filesFolder').val();
				$('#imageFile, #docFile, #videoFile, #audioFile').uploadifySettings('folder', folder);
				$('#imageFile').uploadifySettings('scriptData', {'type' : 'image','imgWidth' : imgWidth,'imgHeight' : imgHeight,'altFolder' : folder});
				$('#docFile').uploadifySettings('scriptData', {'type' : 'doc','altFolder' : folder});
				$('#videoFile').uploadifySettings('scriptData', {'type' : 'video','altFolder' : folder});
				$('#audioFile').uploadifySettings('scriptData', {'type' : 'audio','altFolder' : folder});
			}else{
				$('#imageFile').uploadifySettings('scriptData', {'type' : 'image','imgWidth' : imgWidth,'imgHeight' : imgHeight,'altFolder' : folder});
				$('#imageFile').uploadifySettings('folder', 'media/images');
				$('#docFile').uploadifySettings('folder', 'media/docs');
				$('#videoFile').uploadifySettings('folder', 'media/video');
				$('#audioFile').uploadifySettings('folder', 'media/audio');
			}
			
			// Loading-Gif anzeigen
			if($('div.uploadifyQueue').children('div.uploadifyQueueItem').length){
				$(this).before('<img src="' + loadingImg.src + '" class="loadingImg-submit loading" />');
				$(this).attr('disabled','disabled');
			}
			
			// Upload
			$('#imageFile').uploadifyUpload();
			$('#docFile').uploadifyUpload();
			$('#videoFile').uploadifyUpload();
			$('#audioFile').uploadifyUpload();
		});
		
		
		$('#docFile').uploadify({
			'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
			'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
			'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
			'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_doc_' + cc.adminLang + '.png',
			'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
			'scriptData'		: {'type' : 'doc'},
			'multi'				: true,
			'auto'				: false,
			'width'				: 170,
			'fileDesc'			: ln.uploaddocs + ' (*.doc, *.pdf, *.zip)',
			'fileExt'			: '*.doc;*.pdf;*.zip',
			'folder'			: 'media/docs',
			'onCancel'       	: function(event, queueID, fileObj, response, data) {
									if($('#docFileQueue').children('div.uploadifyQueueItem').length <= 1){
										$('img.loading').remove();
										$('#uploadFiles').removeAttr('disabled');
									}
								  },
			'onSelectOnce'      : function(event, queueID, fileObj) {
										
									$('a.gallery').hide();
									$('#uploadFiles').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000);
								  },
			'onAllComplete'     : function() {
									$('img.loading').remove();
									$('#uploadFiles').removeAttr('disabled');
									//$('#uploadfm').submit();
								  },
			'sizeLimit'			: sizeLimit,
			'simUploadLimit'	: 5
		});
		
		$('#imageFile').uploadify({
			'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
			'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
			'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
			'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_img_' + cc.adminLang + '.png',
			'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
			'scriptData'		: {'type' : 'image','imgWidth' : imgWidth,'imgHeight' : imgHeight},
			'multi'				: true,
			'auto'				: false,
			'width'				: 170,
			'fileDesc'			: ln.uploadimages + ' (*.jpg, *.png, *.gif, *.svg)',
			'fileExt'			: '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.JPG;*.JPEG;*.SVG',
			'folder'			: 'media/images',
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
										
									$('a.gallery').hide();
									$('#uploadFiles').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000);								
								  },
			'onCancel'       	: function(event, queueID, fileObj, response, data) {
									if($('#imageFileQueue').children('div.uploadifyQueueItem').length <= 1){
										$('img.loading').remove();
										$('#uploadFiles').removeAttr('disabled');
									}
								  },
			'onAllComplete'     : function() {
									$('img.loading').remove();
									$('#uploadFiles').removeAttr('disabled');
									//$('#uploadfm').submit();
								  },
			'sizeLimit'			: sizeLimit,
			'simUploadLimit'	: 5
		});
		
		$('#videoFile').uploadify({
			'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
			'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
			'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
			'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_video_' + cc.adminLang + '.png',
			'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
			'scriptData'		: {'type' : 'video'},
			'multi'				: true,
			'auto'				: false,
			'width'				: 170,
			'fileDesc'			: ln.uploadvideo + ' (*.m4v, *.mp4, *.wmf, *.mpeg, *.mpg, *.avi, *.ra, *.ram, *.mov, *.qt, *.ogv, *.webm, *.swf, *.flv, *.f4v)',
			'fileExt'			: '*.m4v;*.mp4;*.wmf;*.mpeg;*.mpg;*.avi;*.ra;*.ram;*.mov;*.qt;*.ogv;*.webm;*.swf;*.flv;*.f4v',
			'folder'			: 'media/video',
			'onSelectOnce'      : function(event, queueID, fileObj) {
									$('a.gallery').hide();
									$('#uploadFiles').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000);
									},
			'onCancel'       	: function(event, queueID, fileObj, response, data) {
									if($('#videoFileQueue').children('div.uploadifyQueueItem').length <= 1){
										$('img.loading').remove();
										$('#uploadFiles').removeAttr('disabled');
									}
								  },
			'onAllComplete'     : function() {
									$('img.loading').remove();
									$('#uploadFiles').removeAttr('disabled');
									//$('#uploadfm').submit();
								  },
			'sizeLimit'			: sizeLimit,
			'simUploadLimit'	: 5
		});
		
		$('#audioFile').uploadify({
			'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
			'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
			'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
			'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_audio_' + cc.adminLang + '.png',
			'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
			'scriptData'		: {'type' : 'audio'},
			'multi'				: true,
			'auto'				: false,
			'width'				: 170,
			'fileDesc'			: ln.uploadaudio + ' (*.mp3, *.ogg, *.oga)',
			'fileExt'			: '*.mp3;*.ogg;*.oga',
			'folder'			: 'media/audio',
			'onSelectOnce'      : function(event, queueID, fileObj) {
									$('a.gallery').hide();
									$('#uploadFiles').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000);
									},
			'onCancel'       	: function(event, queueID, fileObj, response, data) {
									if($('#audioFileQueue').children('div.uploadifyQueueItem').length <= 1){
										$('img.loading').remove();
										$('#uploadFiles').removeAttr('disabled');
									}
								  },
			'onAllComplete'     : function() {
									$('img.loading').remove();
									$('#uploadFiles').removeAttr('disabled');
									//$('#uploadfm').submit();
								  },
			'sizeLimit'			: sizeLimit,
			'simUploadLimit'	: 5
		});		
	};

})(jQuery);