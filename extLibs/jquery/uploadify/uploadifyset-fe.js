(function($){

	$.setUploadifyFE = function(plelem){
		
		var ln				= conciseCMS.ln;
		var myUploader		= plelem || ".imageFile";
		var selFiles		= 0;
		var selFiles2		= 0;
		var selFiles3		= 0;
		var selFiles4		= 0;
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
		var scaleImg		= false;
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
		
		
		// Bilderupload aus dem Front-End
		if(!$('.listBox').length){
		
		// Upload starten
		$('.uploadFiles').bind('click', function(){
			$(this).next(myUploader).uploadifyUpload();
		});
		
		
		$(myUploader).uploadify({
			'uploader'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.swf',
			'script'			: cc.httpRoot + '/extLibs/jquery/uploadify/uploadify.php',
			'checkScript'		: cc.httpRoot + '/extLibs/jquery/uploadify/check.php',
			'buttonImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/browse_img-fe_' + cc.adminLang + '.png',
			'cancelImg'			: cc.httpRoot + '/extLibs/jquery/uploadify/cancel.png',
			'scriptData'		: {'type' : 'image','imgWidth' : imgWidth,'imgHeight' : imgHeight,'response' : 'filename'},
			'multi'				: false,
			'auto'				: true,
			'width'				: 212,
			'height'			: 30,
			'fileDesc'			: ln.uploadimages + '(*.jpg, *.png, *.gif, *.svg)',
			'fileExt'			: '*.jpg;*.png;*.gif;*.jpeg;*.svg;*.JPG;*.JPEG;*.SVG',
			'folder'			: 'media/images',
			'onSelectOnce'      : function(event, queueID, fileObj) {
									
									parObj = $(this);
									
									parObj.closest('.innerEditDiv').children('.editButtons').addClass('forceShow');
				
									scaleImg	= parObj.closest('.mediaList').find('.scaleimg').is(':checked');
									
									imgWidth	= parObj.closest('.mediaList').find('.scaleImgDiv:visible').children('.imgWidth').val();
									imgHeight	= parObj.closest('.mediaList').find('.scaleImgDiv:visible').children('.imgHeight').val();
									
									if(!scaleImg || typeof(imgWidth) == "undefined" || imgWidth == "") {
										imgWidth = 0;
									}
									if(!scaleImg || typeof(imgHeight) == "undefined" || imgHeight == "") {
										imgHeight = 0;
									}
									parObj.uploadifySettings('scriptData', {'type' : 'image','imgWidth' : imgWidth,'imgHeight' : imgHeight,'response' : 'filename'}, true);
									
									parObj.find('.uploadSingleFile').css('display','inline').removeClass('hide').fadeOut(600).fadeIn(1000).removeAttr('disabled');
								
								  },
			'onComplete'        : function(event, queueID, fileObj, response, data) {
			
									if(response != 0) {
										if(!scaleImg){
											parObj.closest('.mediaList').find('.scaleImgDiv').children('.imgHeight').val('');
										}
										parObj.closest('.innerEditDiv').children('.editButtons').removeClass('forceShow');
										parObj.closest('.mediaList').find('.newfile').val(response).closest('.mediaList').find('.feEditButton.submit').click()[0];
									}
								  },
			'sizeLimit'			: sizeLimit,
			'simUploadLimit'	: 1
		});
		}
	};
})(jQuery);