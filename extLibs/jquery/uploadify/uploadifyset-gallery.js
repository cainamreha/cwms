(function($){

	$.setUploadifyGallery = function(){
		
		if(!$(".fileUpload-uploadifyGallery").length){
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
		
		// Gallery-/Bild-Upload
		var gallName = "media/galleries/";

		// Loading-Platzhalterbild
		var loadingImg = new Image();
		loadingImg.src = cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/loading.gif';
		
		// Maximale Dateigröße festlegen
		maxUploadSize		= $('#maxUploadSize').val();
		sizeLimit 			= Math.min(104857600, maxUploadSize);
		
		
		// Überprüfung Galleriename
		function checkGallName(gallName) {
			
			var regex = /^[A-Za-z0-9 _-]+$/;
			var regexInit = /[A-Za-z0-9]/;

			if(gallName == "") {
				jAlert(ln.gallnamefirst, ln.alerttitle);
				return false;
			} else {
			if(gallName.length > 64 
			|| !regex.exec(gallName) 
			|| !regexInit.exec(gallName[0])
			) {
				jAlert(ln.checkgallname, ln.alerttitle);
				return false;
			}}
			return true;
		};
	
		// Upload-Box Togglen
		$('.toggleUploadBox').bind('click', function(){
			$(this).closest('.actionBox').next('div.uploadBox').slideToggle();
			$(this).closest('.actionBox').siblings('div.newFolderBox').slideToggle();
			$(this).closest('.innerListBox').css('height','auto');
		});
		
		// Upload starten
		$('#uploadGall').bind("click", function() {
										
			var gallNameChk = $('#gallName').val();
			
			if(checkGallName(gallNameChk) == false) {
				return false;
			} else {	
			
				// Loading-Gif anzeigen
				if($('div#gallFilesQueue').children('div.uploadifyQueueItem').length){
					$(this).before('<img src="' + loadingImg.src + '" class="loadingImg-submit loading" />');
					$(this).attr('disabled','disabled');
				}
				
				// Bildgrößenberechnung
				if($('#scaleImgDiv:visible').length) {
					imgWidth	= $('#scaleImgDiv:visible').find('#imgWidth').val();
					imgHeight	= $('#scaleImgDiv:visible').find('#imgHeight').val();
				} else {
					imgWidth	= 0;
					imgHeight	= 0;
				}

				gallName		= gallNameChk.replace(/ /g, "_");
				folder			= "media/galleries/" + gallName;
				
				$('#gallFiles').uploadifySettings('folder', folder);
				$('#gallFiles').uploadifySettings('scriptData', {'type' : 'gallery', 'imgWidth' : imgWidth, 'imgHeight' : imgHeight, 'altFolder' : folder});
				
				$('#gallFiles').uploadifyUpload();
			}
		});


		$('#gallFiles').uploadify({
			'debug'				: true,
			'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
			'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
			'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
			'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_img_' + cc.adminLang + '.png',
			'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
			'scriptData'		: {'type' : 'gallery','imgWidth' : imgWidth,'imgHeight' : imgHeight},
			'auto'				: false,
			'multi'				: true,
			'width'				: 170,
			'folder'			: 'media/galleries',
			'altFolder'			: 'media/galleries',
			'fileDesc'			: ln.uploadimages + ' (*.jpg, *.png, *.gif, *.svg)',
			'fileExt'			: '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.JPG;*.JPEG;*.SVG',
			'onSelect'      	: function(event, queueID, fileObj) {
									selFiles++;
									},
			'onSelectOnce'      : function(event, queueID, fileObj) {
				
									parObj = $(this);
									
									imgWidth	= parObj.siblings('div.scaleImgBox').children('div.scaleImgDiv:visible').children('#imgWidth').val();
									imgHeight	= parObj.siblings('div.scaleImgBox').children('div.scaleImgDiv:visible').children('#imgHeight').val();
										
									if(typeof(imgWidth) == "undefined" || imgWidth == "") {
										imgWidth = 0;
									}
									if(typeof(imgHeight) == "undefined" || imgHeight == "") {
										imgHeight = 0;
									}
										
									parObj.uploadifySettings('scriptData', {'type' : 'gallery','imgWidth' : imgWidth,'imgHeight' : imgHeight}, true);
									
									$('#uploadGall').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000).removeAttr('disabled');
									
									if($('#gallName').val() != "") {
									
										var gallName = $('#gallName').val().replace(/ /g, "_");
										$('#gallFiles').uploadifySettings('folder','media/galleries/' + gallName);
									}else{
										jAlert(ln.entergallname, ln.alerttitle);
									}
								  },									
			'onCancel'       	: function(event, queueID, fileObj, response, data) {
									if($('#gallFilesQueue').children('div.uploadifyQueueItem').length <= 1){
										$('img.loading').remove();
										$('#uploadGall').removeAttr('disabled');
									}
								  },
			'onComplete'        : function(event, queueID, fileObj, response, data) {
									//console.log(response+data+$('#gallFiles').uploadifySettings('altFolder'));
									if(response == "1") {
										uppedFiles.push(fileObj["name"]);
									}
								  },
			'onAllComplete'     : function() {
			
									if(uppedFiles != "")
										$('#gallFiles').parent('form').append('<input type="hidden" name="uppedFiles" id="uppedFiles" value="' + uppedFiles + '" />');
									uppedFiles = [];
									$('img.loading').remove();
									
									//console.log($('#gallFiles').closest('form').submit());
									$('#gallFiles').closest('form').submit();
								  },
			'sizeLimit'			: sizeLimit,
			'simUploadLimit'	: 5

		});		
	};
	
})(jQuery);