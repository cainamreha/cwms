// Plupload config function $.ccPluploader
head.ready('ui', function(){
head.ready('plupload', function(){
	
(function($) {
	
	$.ccPluploader = function(plelem){
	
		ccIsHandlingError		= true;
		var ln					= conciseCMS.ln;
		
		var myPluploader		= plelem || "#myUploadBox";
		var pluploadForm		= $('body').find(myPluploader).closest('form');
		var maxFileSize			= pluploadForm.find('input[name="maxFileSize"]').val();
		var mimeTypes			= pluploadForm.find('input[name="allowedFileTypes"]').val();
		var multiSel			= !pluploadForm.find('input[name="singleFile"]').length;
		var overwrite			= pluploadForm.find('input[name="overwrite"]').is(":checked");
		var scaleimg			= pluploadForm.find('input[name="scaleimg"]').is(":checked");
		var imgWidth			= pluploadForm.find('input[name="imgWidth"]').val();
		var imgHeight			= pluploadForm.find('input[name="imgHeight"]').val();
		var uploader			= null;
		var duplicateFilesStr	= "";
		var filesFolder			= "";
		var useFilesFolder		= 0;
		var gallName			= "";

		var pluploadSettings	= {
		
			// General settings
			runtimes : 'html5,flash,silverlight,html4',
			url : cc.httpRoot + '/extLibs/jquery/plupload/upload.php',
			flash_swf_url : cc.httpRoot + '/extLibs/jquery/plupload/js/Moxie.swf', // Flash settings
			silverlight_xap_url : cc.httpRoot + '/extLibs/jquery/plupload/js/Moxie.xap', // Silverlight settings


			// User can upload no more then 20 files in one go (sets multiple_queues to false)
			max_file_count: multiSel ? 20 : 1,
			
			chunk_size: '1mb',

			max_retries: 0,

			// Allow selection of multiple files
			multi_selection: multiSel,

			// Autostart
			autostart: !multiSel,

			// Own params
			multipart_params: {'scaleimg': scaleimg, 'imgWidth': imgWidth, 'imgHeight': imgHeight},

			// Resize images on clientside if we can
			resize : {
				width : 2400, 
				height : 2400, 
				quality : 90,
				crop: false // crop to exact dimensions
			},			
			
			// Buttons
			buttons : {
				start: multiSel
			},			
			
			filters : {
				// Maximum file size
				max_file_size : maxFileSize,
				// Specify what files to browse for
				
				mime_types: [
					//{title : "Image files", extensions : "jpg,gif,png"},
					//{title : "Zip files", extensions : "zip"}
					{title : "Allowed files", extensions : mimeTypes}
				],
				
				prevent_duplicates : true,
			},

			// Rename files by clicking on their titles
			rename: true,
			
			// Sort files
			sortable: true,

			// Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
			dragdrop: true,

			// Views to activate
			views: {
				list: true,
				thumbs: true, // Show thumbs
				active: 'thumbs'
			}
		};

		// load uploader functions
		_loadUploaderFunctions = function(uploader, pluploadForm) {

			// Handle the case when form was submitted before uploading has finished
			$(pluploadForm).bind("submit", function(e) {
				
				e.preventDefault();
				
				var replace = false;
				
				// Files in queue upload them first
				if (!pluploadForm.hasClass('gallery-form')
				&& uploader.total.size == 0
				) {
					jAlert(ln.nofilessel, ln.alerttitle);
					return false; // Keep the form from submitting
				}
				duplicateFilesStr = "";
				
				// Falls NICHT fe
				if(pluploadForm.parents('body.admin').length){
					replace = true;
				}
				
				$.submitViaAjax($(this), false, undefined, replace, function(){
					
					$(document).find('input.listSearch').keyup().blur();
					uploader.refresh();
					
					// Falls fe
					if(!replace){
						var listBox			= pluploadForm.closest('.listBox');
						var listBoxID		= listBox.attr('data-id');					
						if($('div.mediaList[data-id="' + listBoxID + '"]').length) {
							listBox.removeListBox();
							mediaList		= $('div.mediaList[data-id="' + listBoxID + '"].gallery');
							mediaList.find('.showListBox').filter(":first").click();
						}
						$.removeWaitBar();
					}
				});
				return false;
			});
			
			// Uploader refresh
			$('body').on("click", '.showListBox', function(){
				// Falls fe
				if(uploader.settings.container.indexOf("-fe-") > -1){
					// Refresh bug, daher ausblenden
					uploader.destroy();
					pluploadForm.closest('.mediaList').addClass('single-col');
					pluploadForm.children('.uploadFeaturesBox').children('label:nth-child(1), label:nth-child(2)').hide();
					$(myPluploader).hide();
					if(!pluploadForm.prev().hasClass('clearfloat')){
						pluploadForm.before('<br class="clearfloat" />');
					}
					//uploader.refresh();
					//$(myPluploader).getUploader().refresh();
					//$.ccPluploader($('body').find('#' + myPluploader));
				}
			});
			
					/*
			// Uploader refresh
			$('body').on("click", '.close', function(){
				if(uploader.settings.container.indexOf("-fe-") > -1){
					// Refresh bug, daher ausblenden
					setTimeout(function(){$.ccPluploader(myPluploader);}, 1000);
				}
			});
					*/
			
			if(uploader.runtime === 'html5') {
				$('div.plupload_droptext').bind('dragenter', function() {
					$(this).addClass("draghover");
				});

				$('div.plupload_droptext').bind('dragleave', function() {
					$(this).removeClass("draghover");
				});
			}
			
			// When files are selected
			$(myPluploader).on('selected', function() {
				if (uploader.total.size > 0) {
					_checkStartUploadAllowed(uploader);
					$('.plupload_droptext').addClass('hide');
				}else{
					$('.plupload_droptext').removeClass('hide');
				}
			});
			
			// When files are removed
			$(myPluploader).on('removed', function() {
				if (uploader.total.size > 0) {
					_checkStartUploadAllowed(uploader);
					$('.plupload_droptext').addClass('hide');
				}else{
					$('.plupload_droptext').removeClass('hide');
				}
			});
			
			// When files are removed
			$(myPluploader).on('start', function() {
				var launch = false;
				launch = _checkStartUploadAllowed(uploader);
				if(!launch){					
					uploader.stop();
					$(myPluploader + '_start').button('disable');
					return launch;
				}
				
			});

			// Falls files folder
			_getForceFilesFolder = function(pluploadForm) {
				
				if(pluploadForm.closest('.listBox.files').length){
					useFilesFolder		= 1
					var folderPath		= pluploadForm.closest('div.listBox').find('.listBoxHeader').html().split("/");
					folderPath.shift(); // ...media entfernen
					folderPath.shift(); // files entfernen
					filesFolder			= folderPath.join("/");
				}else{
					useFilesFolder		= pluploadForm.find('input.useFilesFolder').is(":checked") ? 1 : 0;
					filesFolder			= pluploadForm.find('input.filesFolder').val();
				}
				return useFilesFolder;
			};
			
			// Falls Gallery
			_getGallName = function(myPluploader, pluploadForm) {
			
				if(myPluploader == "#myUploadBox"
				&& pluploadForm.find('input#gallName').length
				){
					gallName		= pluploadForm.find('input#gallName').val().replace(" ", "_");
					return gallName;
				}
				// Falls listBox
				if(myPluploader == "#myUploadBox-lb"
				&& pluploadForm.find('input[name="gall_name"]').length
				){
					gallName		= pluploadForm.find('input[name="gall_name"]').val().replace(" ", "_");
					return gallName;
				}
				return "";
			};

			_checkStartUploadAllowed = function(uploader) {
				
				if($('#gallName').length){
				
					var gallNameChk	= gallName;
					
					// Falls nicht listBox
					if(myPluploader == "#myUploadBox"){
						gallNameChk	= $('#gallName').val();
					}
					
					if(_checkGallName(gallNameChk) === false){
						
						if(multiSel && $(myPluploader + '_start').data("ui-button")){
							$(myPluploader + '_start').button('disable');
						}
						return false;
					}
				}
				// Falls files-Ordner gecheckt ist, aber kein Ordner angegeben, abbrechen
				if($('input.useFilesFolder').is(':checked') && $('input.filesFolder').val() == ""){
					if(multiSel && $(myPluploader + '_start').data("ui-button")){
						$(myPluploader + '_start').button('disable');
					}
					jAlert(ln.choosefolder, ln.alerttitle);
					return false;
				}
				if(uploader.total.size > 0){
					if(multiSel && $(myPluploader + '_start').data("ui-button")){
						$(myPluploader + '_start').button('enable');
					}
					return true;
				}
				return false;
			};
			
			
			// Überprüfung Galleriename
			_checkGallName = function(gallN) {
				
				var regex = /^[A-Za-z0-9 _-]+$/;
				var regexInit = /[A-Za-z0-9]/;

				if(gallN == "") {
					jAlert(ln.gallnamefirst, ln.alerttitle);
					return false;
				} else {
				if(gallN.length > 64 
				|| !regex.exec(gallN) 
				|| !regexInit.exec(gallN[0])
				) {
					jAlert(ln.checkgallname, ln.alerttitle);
					return false;
				}}
				return true;
			};
		
			// Überprüfung Filesfolder
			$("body").on("blur", '.filesFolder', function() {
				
				_checkStartUploadAllowed(uploader);				
			
			});
			$("body").on("click", '.useFilesFolder', function() {
				
				_checkStartUploadAllowed(uploader);				
			
			});
		
			// Überprüfung Galeriename nach Eingabe
			$("body").on("blur", '.plupload-uploader #gallName', function() {

				_checkStartUploadAllowed(uploader);				
				return false;
			});
			
			// Upload-Box Togglen
			$('.toggleUploadBox').unbind("click").bind('click', function(){
				
				var controlBar		= $(this).closest('.controlBar');
				var uploadBar		= $(this).closest('.actionBox');
				var uploadBox		= uploadBar.next('.uploadBox');
				var actionBoxes		= controlBar.find('.actionBox, .fullBox').not(uploadBox).not(uploadBar).not('.overwriteBox, .scaleImgBox');
				var listBox			= $(this).closest('.innerListBox');
				var lbH				= parseInt(controlBar.height());
				
				actionBoxes.slideToggle('fast');
				uploadBox.siblings('div.newFolderBox').slideToggle('fast');
				listBox.find('.listItemBox').slideToggle('fast');
				
				uploadBox.slideToggle('fast', function(){
					var lbHNew	= parseInt(controlBar.height()-lbH);
					//$.valignListBox($(this).closest('.listBox'), lbHNew, Number(lbHNew) > 0);
					//$.valignListBox($(this).closest('.listBox'), 5, Number(lbHNew) > 0);
					listBox.css('height','auto');
				});
			});
		};

		// Init settings
		pluploadSettings.init = {

			PostInit: function (up, files) {
				// destroy the uploader and init a new one
				uploader			= up;
				duplicateFilesStr	= "";
				pluploadForm		= $('body').find(myPluploader).closest('form');
				maxFileSize			= pluploadForm.find('input[name="maxFileSize"]').val();
				mimeTypes			= pluploadForm.find('input[name="allowedFileTypes"]').val();
				overwrite			= pluploadForm.find('input[name="overwrite"]').is(":checked");
				scaleimg			= pluploadForm.find('input[name="scaleimg"]').is(":checked");
				imgWidth			= pluploadForm.find('input[name="imgWidth"]').val();
				imgHeight			= pluploadForm.find('input[name="imgHeight"]').val();
				
				// _loadUploaderFunctions
				_loadUploaderFunctions(uploader, pluploadForm);
				
				useFilesFolder		= _getForceFilesFolder(pluploadForm);
				gallName			= _getGallName(myPluploader, pluploadForm);
			},
			UploadComplete: function(up, file) {
	
				var uppedFilesStr	= "";
				var newInput 		= "";
				var i				= file.length;
				var isListBox		= pluploadForm.attr('data-type') == "listbox";
				var mediaList		= pluploadForm.closest('div.mediaList');
				var listBox			= pluploadForm.closest('.listBox');
				var listBoxID		= listBox.attr('data-id');
				var redirect		= "";
				
				if($('div.mediaList[data-id="' + listBoxID + '"]').length) {
					mediaList		= $('div.mediaList[data-id="' + listBoxID + '"]');
				}
				if(isListBox) {
					mediaList		= $('div.mediaList[data-id="' + listBoxID + '"]');
				}
				if(mediaList.attr('data-redirect')){
					redirect		= mediaList.attr('data-redirect');
					pluploadForm.attr('data-ajax', 'false');
				}
				if(i > 0){
					$.each(file, function( index, value ) {
						uppedFilesStr += value.name + ",";
						if(!--i ){
			
							// Falls Galerie
							if(pluploadForm.hasClass('gallery-form')){
								if(!pluploadForm.find('input#gallName').length){
									newInput += '<input type="hidden" name="gallName" value="' + gallName + '" />';
								}
								newInput += '<input type="hidden" name="uppedFiles" id="uppedFiles" value="' + uppedFilesStr + '" />';
								newInput += '<input type="hidden" name="duplicateFiles" id="duplicateFiles" value="' + duplicateFilesStr + '" />';
								pluploadForm.attr('action', pluploadForm.attr('action') + '&edit_gall=' + gallName + '&redirect=' + redirect).append(newInput).submit();
								return false;
							}
							
							// Falls listBox
							if(isListBox){
								newInput += '<input type="hidden" name="recentUploads" class="recentUploads" value="' + uppedFilesStr + '" />';
								mediaList.prepend(newInput).find('.showListBox').not('.openFilemanager').filter(":first").click().click();
								return false;
							}
							
							// Falls FE
							if($(myPluploader).hasClass('feFileUploader')){
								
								pluploadForm.find('input[name="newfile"]').val(value.name);
								newInput += '<input type="hidden" name="recentUploads" class="recentUploads" value="' + uppedFilesStr + '" />';
								
								mediaList.prepend(newInput);
								
								imgWidth	 	= "";
								imgHeight	 	= ""; // leave height on auto for correct ratio
								imgWidth		= mediaList.find('.scaleImgDiv').children('.imgWidth').val();
								if(scaleimg){
									imgHeight	= mediaList.find('.scaleImgDiv').children('.imgHeight').val();
								}								
								
								mediaList.closest('.editButtons').removeClass('forceShow');
								
								mediaList.find('.scaleImgDiv').children('.imgWidth').val(imgWidth).siblings('.imgHeight').val(imgHeight).closest('.mediaList').find('.feEditButton.submit').click()[0];
								
								uploader.splice();
								uploader.refresh();
								
								return false;
							}
			
						}
					});
				}
				
				return false;
			},
			BeforeUpload: function(up, file) {
				
				overwrite			= pluploadForm.find('input[name="overwrite"]').is(":checked") ? 1 : 0;
				scaleimg			= pluploadForm.find('input[name="scaleimg"]').is(":checked") ? 1 : 0;
				imgWidth			= pluploadForm.find('input[name="imgWidth"]').val();
				imgHeight			= pluploadForm.find('input[name="imgHeight"]').val();
				useFilesFolder		= _getForceFilesFolder(pluploadForm);
				gallName			= _getGallName(myPluploader, pluploadForm);

				var launch = _checkStartUploadAllowed(up);
				if(!launch){					
					up.stop();
					$(myPluploader + '_start').button('disable');
					return launch;
				}
				
				uploader.settings.multipart_params["overwrite"]			= overwrite;
				uploader.settings.multipart_params["scaleimg"]			= scaleimg;
				uploader.settings.multipart_params["imgWidth"]			= imgWidth;
				uploader.settings.multipart_params["imgHeight"]			= imgHeight;
				uploader.settings.multipart_params["useFilesFolder"]	= useFilesFolder;
				uploader.settings.multipart_params["filesFolder"]		= filesFolder;
				uploader.settings.multipart_params["gallName"]			= gallName;
			},
			FileUploaded: function (up, file, info) {
			
				console.log(info);
				
				if (!info.response)
					return;
				
				var error = false; // parse response and check for error, if so, requeue file
				
				if(info.response.indexOf("{") === 0){
					
					var res = $.parseJSON(info.response);
				
					error = !res.OK;
			
					if (error) {
						up.trigger('Error', {
							code: plupload.FAILED,
							message: plupload.translate("HTTP Error."),
							file: file
						});
						return false;
					}

					if(file.name != res.truename){
						$('#' + file.id).find('.plupload_file_name').attr('title', res.truename);
						$('#' + file.id).find('.plupload_file_name_wrapper').html(res.truename);
						file.name	= res.truename;
					}
					// Falls Datei überschrieben wurde
					if(res.duplicate
					&& res.truename == res.originalname
					){
						duplicateFilesStr += file.name + ",";
					}
				}
			},
			Error: function(up, file) {
				console.log(uploader);
			}
		};
		
		// Plupload init
		$(myPluploader).plupload(pluploadSettings);
			
		return this;
	};
	/*
	head.ready('ui', function(){
		$(document).ready(function(){
			
			ccPoolFunctions.push({name: "$.ccPluploader", params: ""});
		
		});
	});
*/
})(jQuery);

});
});
