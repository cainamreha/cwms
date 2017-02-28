// Medienauswahl
(function($){
	
	$.fn.listMedia = function() {
		
		var ln				= conciseCMS.ln;
		
		// Variable ob das Löschen einzelner Dateien bestätigt werden sollen
		var confirmFileDeletion = true;

		var mediaListLink	= $(this);
		var fromFileBrowser	= mediaListLink.closest('div.myFileBrowser').length;
		var targetUrl		= mediaListLink.attr('data-url');
		var mediaList		= mediaListLink.closest('div.mediaList');
		var mediaType		= mediaListLink.attr('data-type');
		var newlistBox		= "";
		var nestedListBox	= false;
		var multiSelect		= false;
		var feEditing		= mediaList.attr('data-fe') ? true : false;
		var mediaListID		= mediaList.attr('data-id');
		var listBox			= $('.listBox[data-id="' + mediaListID + '"]');
		
		
		// Falls multi select
		if(mediaListLink.hasClass('multiple')){
			multiSelect = true;
		}
		
		// Falls Filemanager
		if(mediaListLink.hasClass('keepListBox')) {
			mediaType = 'filemanager';
		}
		

		//alert(mediaListLink.attr('class'));
		//if(listBox.not('.gallery').length && !mediaListLink.hasClass('keepListBox')) {
		if(listBox.not('.gallery').length) {
			
			// Falls aus myFileBrowser geklickt
			listBox.not('.gallery').parent().remove();
			
			// Falls aus Listbox heraus ein (anderer) Default-Ordner geöffnet werden soll
			if(!nestedListBox && !mediaListLink.hasClass('keepListBox')){
				//$(".loadingDiv").remove();
				nestedListBox = true;
				return false;
			}else{
				nestedListBox = false;
			}
		}

		if($("div.dimDiv").not('.gallery').length){
			$("div.dimDiv").not('.gallery').remove();
		}
			
		// offene ListBox schließen (Außer .gallery, da diese den TinyMCE mit innerer ListBox enhalten können)
		//if($('div:not(.gallery) div.listBox').length && !mediaListLink.hasClass('keepListBox')) {
		if($('div.listBox:not(.gallery)').length) {
			$('div.listBox:not(.gallery)').parent().remove();
		}

		// Loading bei Button anzeigen
		var openListButton		= mediaListLink.children('.openList');

		openListButton.loading();
		
		$.getWaitBar();
		
		// Ajax listBox
		$.ajax({
			url: targetUrl,
			success: function(ajax){
				
				// Falls ein Json-Object zurückgegeben wurde, handelt es sich um eine Meldung (kein Zugriff auf ListBox)
				if(typeof(ajax) == "object"){
					$.getJSONResponse(ajax);
					$.removeWaitBar();
					mediaListLink.closest('.editButtons-panel').children('.newcon').click();
					return false;
				}
				
				var dateNow			= new Date();
				var uniqueID		= dateNow.getTime();
				var listBoxWrapper	= $('<div class="mediaList listBoxWrapper adminArea" data-id="lb-' + uniqueID + '"></div>');
				
				// ListBox Dom
				newlistBox = $('<div class="listBox ' + mediaType + (multiSelect ? ' multiple' : '') + (fromFileBrowser ? ' filebrowser' : '') + '" data-type="' + mediaType + '" data-id="lb-' + uniqueID + '" style="visibility:hidden;"><div class="innerListBox">' + ajax + '</div></div>');
				
				// ListBox einfügen
				listBoxWrapper.append(newlistBox);
				$('div#container').append(listBoxWrapper);
				
				// ListBox left margin
				newlistBox.css('margin-left', '-' + parseInt(newlistBox.outerWidth() /2) + 'px');
				
				// Unique ID für mediaList
				mediaList.attr('data-id', 'lb-' + uniqueID);
				

				// Auf Dialog zum Ändern von Dateinamen prüfen
				if($("#dialog-form-file").length){
					head.ready('ui', function(){
						head.load({ dialogs: cc.httpRoot + "/system/access/js/dialogs.min.js" }, function(){
							$.createDialog("file");
						});
					});
				}
				
				// Loading entfernen
				if($(".loadingDiv").length){
					$(".loadingDiv").remove();
				}

				
				// Filemanager
				if(typeof($.fn.elfinder) != "function"){
					$('.openFilemanager').hide();
				}
				
				// Ggf. Skriptdateien laden
				// Pimg
				//head.load({pimg:cc.httpRoot + "/extLibs/jquery/pimg/pimg.js"}, function(){ $.pimg(head.mobile ? "disable" : "enable"); });
				
				
				// Elfinder
				head.load(	{elfindercss: cc.httpRoot + "/extLibs/jquery/elfinder/css/elfinder.min.css"},
							{elfinderui: cc.httpRoot + "/extLibs/jquery/ui/jquery-ui-custom-elfinder.min.js"},
							{elfinder: cc.httpRoot + "/extLibs/jquery/elfinder/js/elfinder.min.js"},
							{elfinderln: cc.httpRoot + "/extLibs/jquery/elfinder/js/i18n/elfinder." + cc.adminLang + ".js"},
							function(){
								$('.openFilemanager').show();
							}
				);
				
				// Codemirror
				head.load(	cc.httpRoot + "/extLibs/codemirror/lib/codemirror.css",
							cc.httpRoot + "/extLibs/codemirror/theme/concise.css",
							cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.css",
							{codemirror: cc.httpRoot + "/extLibs/codemirror/lib/codemirror.js"},
							{codemirrorhtml: cc.httpRoot + "/extLibs/codemirror/mode/htmlmixed/htmlmixed.js"},
							{codemirrorxml: cc.httpRoot + "/extLibs/codemirror/mode/xml/xml.js"},
							{codemirrorcss: cc.httpRoot + "/extLibs/codemirror/mode/css/css.js"},
							{codemirrorjs: cc.httpRoot + "/extLibs/codemirror/mode/javascript/javascript.js"},
							//{codemirror: cc.httpRoot + "/extLibs/codemirror/mode/vbscript/vbscript.js"},
							{codemirrorfs: cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.js"}
							
				);
				/*
				Modernizr.load([{
					load: [	cc.httpRoot + "/extLibs/jquery/ui/jquery-ui-custom-elfinder.min.js",
							cc.httpRoot + "/extLibs/jquery/elfinder/js/elfinder.min.js",
							cc.httpRoot + "/extLibs/jquery/elfinder/js/i18n/elfinder." + cc.adminLang + ".js",
							cc.httpRoot + "/extLibs/jquery/elfinder/css/elfinder.min.css"
					],
					complete: function(){
						$('.openFilemanager').show();
					}
				}]);
				// Codemirror
				Modernizr.load([{
					load: [	cc.httpRoot + "/extLibs/codemirror/lib/codemirror.js",
							cc.httpRoot + "/extLibs/codemirror/mode/htmlmixed/htmlmixed.js",
							cc.httpRoot + "/extLibs/codemirror/mode/xml/xml.js",
							cc.httpRoot + "/extLibs/codemirror/mode/css/css.js",
							cc.httpRoot + "/extLibs/codemirror/mode/javascript/javascript.js",
							//cc.httpRoot + "/extLibs/codemirror/mode/vbscript/vbscript.js",
							cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.js",
							cc.httpRoot + "/extLibs/codemirror/lib/codemirror.css",
							cc.httpRoot + "/extLibs/codemirror/theme/concise.css",
							cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.css"
					]
				}]);
				
						// Ggf. Skriptdateien laden
						yepnope(cc.httpRoot + "/extLibs/jquery/ui/jquery-ui-custom-elfinder.min.js");
						yepnope(cc.httpRoot + "/extLibs/jquery/elfinder/js/elfinder.min.js");
						yepnope(cc.httpRoot + "/extLibs/jquery/elfinder/js/i18n/elfinder." + cc.adminLang + ".js");
						yepnope(cc.httpRoot + "/extLibs/codemirror/lib/codemirror.js");
						yepnope(cc.httpRoot + "/extLibs/codemirror/mode/htmlmixed/htmlmixed.js");
						yepnope(cc.httpRoot + "/extLibs/codemirror/mode/xml/xml.js");
						yepnope(cc.httpRoot + "/extLibs/codemirror/mode/css/css.js");
						yepnope(cc.httpRoot + "/extLibs/codemirror/mode/javascript/javascript.js");
						//yepnope(cc.httpRoot + "/extLibs/codemirror/mode/vbscript/vbscript.js");
						yepnope(cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.js");
						yepnope(cc.httpRoot + "/extLibs/jquery/elfinder/css/elfinder.min.css");
						yepnope(cc.httpRoot + "/extLibs/codemirror/lib/codemirror.css");
						yepnope(cc.httpRoot + "/extLibs/codemirror/theme/concise.css");
						yepnope(cc.httpRoot + "/extLibs/codemirror/addon/display/fullscreen.css");
				*/		
				
			
				// ListBox
				head.ready(function(){
				
					var windowWidth		= parseInt($.getWindowSize()[0]);
					var windowHeight	= parseInt($.getWindowSize()[1]);
					var listBoxHeight	= parseInt(newlistBox.height());
					var listBoxOWidth	= parseInt(newlistBox.outerWidth());
					var listBoxOHeight	= parseInt(newlistBox.outerHeight());
					var listBoxDiff		= parseInt(windowHeight - (listBoxOHeight + 100));
					var cbHeight		= 0;
					
					if(newlistBox.find('.controlBar:first').length){
						cbHeight		+= parseInt(newlistBox.find('.controlBar:first').outerHeight());
					}
					if(newlistBox.find('.listBoxHeader').length){
						cbHeight		+= parseInt(newlistBox.find('.listBoxHeader').outerHeight());
					}
					var minWidth		= head.mobile ? 320 : 525;
					var maxWidth		= parseInt($.getWindowSize()[0]);
					var maxHeight		= listBoxOHeight - (cbHeight);
					var ratio			= 3;
					
					if(listBoxDiff < 0){
						ratio			= 2;
						listBoxOHeight	= windowHeight - 125;
						maxHeight		= listBoxOHeight - (cbHeight + 75);
					}
					
					if(newlistBox.hasClass('filemanager')){
						var fileman		= newlistBox.find("#elfinder");
						ratio			= 2;
						listBoxOHeight	= windowHeight * 0.8;
						$("#elfinder").height(listBoxOHeight - 75).resize();
					}
				
					var listBoxOffsetT = parseInt((windowHeight - listBoxOHeight) / ratio);					
					
					newlistBox.hide().css({'visibility': 'visible', 'top' : listBoxOffsetT + 'px'}).children('div.innerListBox').find('div.listItemBox').css({'max-height' :  maxHeight + 'px'});
					
					// ListBox einblenden
					newlistBox.fadeIn(150, function(){
				
						// Loading bei Button entfernen
						openListButton.loadingRemove();
					
						$.removeWaitBar(false);

						// Resizable
						setTimeout(function(){
						
							// InnerListBox Resizable, falls nicht filemanager
							newlistBox.not('.filemanager').children('div.innerListBox').resizable({
								disabled: false,
								start: function( event, ui ) {
									$(this).css({'min-width': minWidth + 'px'});
									newlistBox.find('.listItemBox').css({'max-height': parseInt($.getWindowSize()[1]), 'max-width': '100%'});
								},
								resize: function( event, ui ) {
									newlistBox.css('height','auto');
									if(newlistBox.width() < 680){
										$('.openDefaultFolder').addClass('narrow');
									}else{
										$('.openDefaultFolder').removeClass('narrow');
									}
								},
								alsoResize: ".listBox .listItemBox, .listBox .listSearch, .listBox .newFolderName",
								width:"80%",
								minWidth: minWidth,
								minHeight: 250,
								maxWidth: maxWidth,
								maxHeight: parseInt($.getWindowSize()[1])
							});
							
							// InnerListBox Resizable, falls filemanager
							if(newlistBox.hasClass('filemanager')){
								fileman.resizable( "option", "alsoResize", "body .filemanager > .innerListBox, body .filemanager .CodeMirror, body .filemanager .CodeMirror-scroll");
								fileman.resizable( "option", "width", listBoxOWidth );
								fileman.resizable( "option", "minWidth", minWidth );
								fileman.resizable( "option", "maxWidth", maxWidth );
							}
							
							// Dragabble
							newlistBox.draggable({ delay: 100, handle: "h2, .listBoxHeader, .controlBar, .elfinder-toolbar" });
							
						}, 500);
					});


					// Falls Galerie-Edit, tinymce laden
					if(newlistBox.hasClass('gallery')){
							
						if(typeof($.sortableGallery) == "function"){
							$.sortableGallery($("#sortableGallery", newlistBox));
						}
				
						if(typeof($.myTinyMCEModules) == "function"){
						
							// Falls listBox erneut geöffnet wird, tinyMCE reinitialisieren
							$.mceAddEditors($("textarea.galleryEditor", newlistBox));
						}
					}
				});
				
				// Hintergrund abdunkeln
				var bodyWidth		= parseInt($(document).width());
				var bodyHeight		= parseInt($(document).height());

					
				// Falls die ListBox nicht über myFileBrowser geöffnet wurde
				if(!fromFileBrowser){
				
												
					if(!$('.dimDiv').length){
						$('div#container').append('<div class="dimDiv ' + mediaType + '" style="height:' + bodyHeight + 'px;"></div>');
					}
					

					// Mediendatei auswählen (Adminbereich) über ListBox
					$('div.listBox .listItemBox:not([data-action="del"]) img.preview:not(.noclick), div.listBox .mediaSelection.fetch').bind("click", function(e){

						if(cc.feMode && mediaList.hasClass('cc-contype-img')){
							return true;
						}
						
						e.preventDefault();
						e.stopPropagation();
					
						var thisElem		= $(this);
						var filePath 		= thisElem.attr('data-path');
						var file	 		= thisElem.attr('data-file');
						var fileName 		= file.split("/").pop();
						var imgPath 		= "";
						var title			= fileName;
						var altText			= title.split(".");
						var fileList;
						var fileTitles;
						var coverPics;
						var newImg			= "";
						var previewBoxDom	= mediaList.siblings('.previewBox');
						var previewDom		= previewBoxDom.children('.preview');
						
						// Falls files-Ordner, den Pfad ändern
						if(file.split("/").length > 1){
							title			= file;
						}
						
						
						if(!multiSelect){
						
							newImg		= mediaList.siblings('.fileUploadBox').children('.uploadMask').children('input.newUploadFile');
							
							// Funktion updatePreview
							function updatePreview() {
								
								previewBoxDom.siblings('input.existingFile').val(title).css('display', 'block');
								
								// Falls Bild
								if(previewBoxDom.hasClass('img')) {
									imgPath		= filePath.replace("/thumbs/" + fileName, "/" + fileName);
									previewDom.attr('src', filePath);
									previewDom.attr('data-img-src', imgPath);
								}
								// Falls Doc / Video
								if(previewBoxDom.hasClass('doc') || previewBoxDom.hasClass('video')) {
									
									previewBoxDom.children('img').remove();
									
									var docDom		= previewBoxDom.children('span');
									filePath		= thisElem.closest('.listItem').children('.fileName').attr('href');
									
									// Falls Doc, Typbild ermittlen
									if(previewBoxDom.hasClass('doc')) {
										var docIcon = thisElem.closest('.listItem').children('.fileName').children('div.iconBox').children('img').attr('src');
										docDom.children('img').attr('src', docIcon);
									}
									docDom.children('a').attr('href', filePath);
									docDom.children('a').html(fileName);
									previewBoxDom.children('span').show();
								}
								// Falls Audio
								if(previewBoxDom.hasClass('audio')) {
									previewBoxDom.children('*').remove();
									var audioFile	= thisElem.siblings('a').clone();
									var player		= thisElem.siblings('audio').clone();
									var myDate		= new Date();
									var player2		= thisElem.siblings('div').clone().children('object').attr('id', myDate.getTime()).attr('width', 250);
									previewBoxDom.append(audioFile);
									previewBoxDom.append(player);
									previewBoxDom.append(player2);
								}
								
								// Titel/Alt-Texte
								previewDom.attr('alt', title);
								previewDom.attr('title', title);
								previewBoxDom.parent('.existingFileBox').siblings('input.altText').val(altText[0]); // alt-Text von Bilddateiname übernehmen
							}
							
							// Falls bereits eine Datei für den Upload ausgewählt worden war...
							if(newImg.length && newImg.val() != "") {
		
								jConfirm(ln.cancelselfile, ln.confirmtitle, function(result){
								
									if(result === true) { // ...Verwerfen bestätigen
							   
										newImg.val('');
										updatePreview();
									}
									else {
										previewBoxDom.siblings('input.existingFile').val('');
										thisElem.closest('div.listBox').not('.gallery').removeListBox();
										$("div.dimDiv").not('.gallery').remove();
										$(".pimg").remove();
										return false;
									}
								});
								
							}
							else {
							
								// Falls die Vorschau aktualisiert werden soll
								updatePreview();
							}
						
							thisElem.closest('div.listBox').not('.gallery').removeListBox();
							$("div.dimDiv").not('.gallery').remove();
							$(".pimg").remove();

						
						} // Ende not multiple
						
						else{
						
							thisElem.fadeTo(100,.5).delay(100).fadeTo(200,1);
							
							// Media player
							fileList	= mediaList.siblings('.cc-filelist-box').find('textarea.fileList');
							fileTitles	= mediaList.siblings('.cc-filetitles-box').find('textarea.fileTitles');
							coverPics	= mediaList.siblings('.cc-imglist-box').find('textarea.coverPics');
							
							var fileListVal		= fileList.val();
							var fileTitlesVal	= fileTitles.val();
							var coverPicsVal	= coverPics.val();
							
							if(mediaList.hasClass('images') || (mediaList.hasClass('files') && mediaList.children('.showListBox.images').length)){
								coverPics.val(coverPics + title + "\r\n"); // Titel überbehmen
							}else{
								fileList.val(fileList + title + "\r\n"); // Dateiname übernehmen
								fileTitles.val(fileTitles + altText[0] + "\r\n"); // Titel überbehmen
							}
							
							// $.execMultiselectCallback
							if(typeof(cc.execMultiselectCallbacks) == "object"){
								$.each(cc.execMultiselectCallbacks, function(i, fcn){
									if(mediaList.hasClass('images') || (mediaList.hasClass('files') && mediaList.children('.showListBox.images').length)){
										fcn(coverPics, title);
									}else{
										fcn(fileList, title);
										fcn(fileTitles, altText[0]);
									}
								});
							}
						}
					
						return false;
				
					}); // Ende listBox Auswahl
					
				} // Ende falls nicht myFileBrowser
				
				
				// Falls Dateien hochgeladen wurden, diese highlighten
				if(mediaList.children('input.recentUploads').length) {
					var uppedFilesInp	= mediaList.children('input.recentUploads');
					newlistBox.find('.fileName').each(function(){
						var fileName		= $(this).attr('data-name');
						var parLi			= $(this).closest('li');
						var uppedFiles		= uppedFilesInp.val();
						var uppedFilesArr	= uppedFiles.split(",");
						if($.inArray(fileName, uppedFilesArr) > -1) {
							var fileList 	= parLi.siblings('li:first');
							parLi.addClass('highlight highlight-recent').insertBefore(fileList);
						}
						uppedFilesInp.remove();
					});
				}
				
				
				var timeoutImageRotate = false;
				
				// Drehen von Bildern
				newlistBox.on("click", '.rotateImage', function(e){
					
					if(timeoutImageRotate){
						return false;
					}
					
					timeoutImageRotate = true;
					
					var rotElem		= $(this);
					var previewObj	= rotElem.closest('li.listItem, .gallentry').children('.previewBox').children('img');
					var previewImg	= previewObj.attr('src').split("?")[0];
					var fullImg		= previewObj.attr('data-img-src').split("?")[0];
					var fileName	= previewObj.attr('data-file');
					var rotateUrl	= rotElem.attr('data-url');
					var rotElemSrc	= rotElem.attr('src');
					
					rotElem.loading();
							
					$.ajax({
						url: rotateUrl,
						success: function(ajax){
							
							if(ajax != 1 && ajax != "1"){
								jAlert(ln.dberror, ln.alerttitle);
							}
							d = new Date();
							previewObj.attr('src',previewImg + "?" + d.getTime());
							previewObj.attr('data-img-src',fullImg + "?" + d.getTime());
						}
					}).done(function(){
						rotElem.attr('src', rotElemSrc);
						setTimeout(function(){
							rotElem.loadingRemove();
							timeoutImageRotate = false;
						}, 200);
					});
					return false;
				});
				
				
				// Bild löschen, falls auf ein Bild in Bilderliste geklickt wurde und Löschen bestätigt wird
				newlistBox.on("click", '.deleteElement', function(){
					
					var fileName		= $(this).attr('data-file');
					var delUrl			= $(this).attr('data-url') + "&target=" + encodeURIComponent(fileName);
					var deleteFile		= false;
					var fileObj			= $(this).closest('li.listItem');
					var gallFiles		= false;
					var deldialog		= "";
					var fileCountObj	= "";
					var fileCountOld	= "";
					var fileCountNew	= "";
					var fileCountHtml	= "";
					
					// Falls Galerie
					if(fileObj.children('.gallentry').length){
						var editor		= fileObj.children('.gallentry').children('.textEditor').attr('id');
						if(typeof($.myTinyMCEModules) == "function"){
							tinymce.remove('#' + editor);
						}
					}else{
						fileCountObj	= fileObj.closest('.innerListBox').find('.listFilter').children('option:nth-child(1)');
					}
					
					if(fileObj.children('.gallentry').length){
						gallFiles	= true;
					}
					
					if(confirmFileDeletion == false) {
						deleteFile = true;
					}else{
						if($(this).hasClass('folder')){
							deldialog = ln.confirmdelfolder;
						}else{
							deldialog = ln.confirmdelfile;
						}

						jConfirm(deldialog + "<strong>" + fileName + "</strong>", ln.confirmtitle, function(result){
							
							if(result === true){
								$.ajax({
									url: delUrl,
									success: function(ajax){

										// Galerie sortIDs neu verteilen
										if(gallFiles){
											fileCountNew = $.updateGalleryList(fileObj);
										}else{
											// Listenelement entfernen
											fileObj.fadeOut(500, function(e){
												fileObj.remove();
											});
										}
										// Anzahl aktualisieren
										if(typeof(fileCountObj) == "object"){
											fileCountOld	= fileCountObj.html().split("(")[1].split(")")[0];
											fileCountNew	= parseInt(fileCountOld) -1;
											fileCountHtml	= fileCountObj.html();
											fileCountObj.html(fileCountHtml.replace(/[0-9]+/, fileCountNew));
										}
										
										// FileCount bei Galerien
										var gallListItem	= mediaListLink.closest('li.gallListItem').children('span.fileCount');
										var gallName		= gallListItem.attr('title');
										$('li#editlist-' + gallName).children('span.fileCount').children('strong').html(fileCountNew);
										$('li#dellist-' + gallName).children('span.fileCount').children('strong').html(fileCountNew);
									}
								});
								return false;
							}
						});
					}
					if(deleteFile){
						
						$.ajax({
							url: delUrl,
							cache: false,
							success: function(ajax){
								
								// Falls Galeriebilder
								if(gallFiles){
									// sortIDs neu verteilen
									fileCountNew = $.updateGalleryList(fileObj);
								
								}else{
									// Listenelement entfernen
									fileObj.fadeOut(500, function(e){
										fileObj.remove();
									});
									// Anzahl aktualisieren
									if(typeof(fileCountObj) == "object"){
										fileCountOld	= fileCountObj.html().split("(")[1].split(")")[0];
										fileCountNew	= parseInt(fileCountOld) -1;
										fileCountHtml	= fileCountObj.html();
										fileCountObj.html(fileCountHtml.replace(/[0-9]+/, fileCountNew));
									}
								}
								if($('span.fileCount').length) {
									// FileCount bei Galerien
									var gallListItem	= mediaListLink.closest('li.gallListItem').children('span.fileCount');
									var gallName		= gallListItem.attr('title');
									$('li#editlist-' + gallName).children('span.fileCount').children('strong').html(fileCountNew);
									$('li#dellist-' + gallName).children('span.fileCount').children('strong').html(fileCountNew);
								}
							}
						});
						return false;
					}else{
						return false;
					}
					
				});
							
			
				// Löschen von mehreren markierten Einträgen
				newlistBox.on("click", '*[data-action="delmultiplefiles"]', function(){
				
					var confMess	= false;
					var delElem		= $(this);
		
					// Falls keine Elemente ausgewählt
					if(!$('.markBox.highlight').length){
						jAlert(ln.nofilessel, ln.alerttitle);
						return false;
					}
					
					if(delElem.hasClass('delFiles')){
						confMess = ln.confirmdelfiles;
					}
					if(delElem.hasClass('delGall')){
						confMess = ln.confirmdelentry;
					}
					if(confMess) {
						jConfirm(confMess, ln.confirmtitle, function(result){
						   if(result === true){
								confirmFileDeletion = false;
								
								// Falls Galeriebild im Edit-Bereich
								if(delElem.hasClass('galleryFiles')){
									delElem.closest('div.listBox').children('div.innerListBox').children('form').children('.listItemBox').children('#sortableGallery').children('.listItem').children('.gallentry').children('.previewBox').children('.markBox').children('input:checked').each(function(){
										$(this).closest('.previewBox').siblings('.editButtons-panel').children('.deleteElement').click(); // Klick auf einzelne Bilder triggern, falls gecheckt und nicht ausgeblendet
									});
								// Falls Datei im Delete-Bereich
								}else{
									delElem.closest('div.listBox').children('div.innerListBox').children('div.listItemBox').find('li:visible input.addVal:checked').parent('.markBox').siblings('.editButtons-panel').children('.deleteElement').click(); // Klick auf einzelne Bilder triggern, falls gecheckt und nicht ausgeblendet
								}
								confirmFileDeletion = true;
						   }
						});
					}
					return false;
				});
			

				
				// Falls nicht aus TinyMCE-Filebrowser geöffnet
				if(!fromFileBrowser){
		
					// Link auswählen, falls auf Linkliste geklickt wurde
					// oder Zielseite auswählen, falls auf Linkliste (target) geklickt wurde
					$('div.listBox button.link').click(function(){
					
						var linkAlias		= $(this).val();
						var linkTitleArr	= linkAlias.split("/");
						var pathElem		= linkTitleArr.length;
						var linkTitle		= linkTitleArr[parseInt(pathElem -1)];
						var listBox			= $(this).closest('div.listBox');
						
						cc.conciseChanges		= true; // Änderungen merken
						
						// Falls nicht multiple (also keine generierung einer Linkliste "menu")
						if(!multiSelect){
						
							if(mediaList.children('.showListBox').attr('data-type') == "targetPage") { // Falls die Zielseite für News übernommen werden soll (nur Alias)
								var linkPageID = $(this).next('input').val();
								linkAlias = linkAlias.split('{#root}/'); // Sitelink-Präfix entfernen
								mediaList.siblings('input.targetPage').val(linkAlias[1]); // Linkname übernehmen in inputfeld
								mediaList.siblings('input.targetPageID').val(linkPageID); // Linkname übernehmen in inputfeld
							}
							else { // Falls die Zielseite für Links übernommen werden soll (Link bzw. Seitenname + Alias)
								mediaList.siblings('input').slice(0,1).val(linkAlias).focus().blur(); // Linkname übernehmen in inputfeld
								mediaList.siblings('input.linkText').val(linkTitle); // Linkid übernehmen in id-feld
							}
							
							// Nach Klick div ausblenden, falls nicht multiple
							$(this).closest('div.listBox').not('.gallery').removeListBox();
							$("div.dimDiv").not('.gallery').remove();
							
							// Falls Galeriename übernommen wurde, listBox entfernen							
							if($(this).closest('div.listBox').hasClass("gallery")){
								$(this).closest('div.listBox').removeListBox();
								$("div.dimDiv.gallery").remove();
							}
							
							// Falls canonical url, reset-Button einblenden							
							if(mediaList.siblings('.resetHiddenField').length){
								mediaList.siblings('.hide-on-empty').fadeIn(400);
								mediaList.siblings('.resetHiddenField').fadeIn(600);
							}
							
						}else{
						
							$(this).fadeTo(100,.5).delay(100).fadeTo(200,1);
							
							var linkList		= mediaList.siblings('div').find('textarea.linkList');
							var linkNames		= mediaList.siblings('div').find('textarea.linkNames');
							var linkListVal		= linkList.val();
							var linkNamesVal	= linkNames.val();
							linkTitle			= linkTitle.replace(/_/g, " ");

							linkList.val(linkListVal + linkAlias + "\r\n"); // Dateiname übernehmen
							linkNames.val(linkNamesVal + linkTitle + "\r\n"); // Titel überbehmen
							
							// $.execMultiselectCallback
							if(typeof(cc.execMultiselectCallbacks) == "object"){
								$.each(cc.execMultiselectCallbacks, function(i, fcn){
									fcn(linkList, linkAlias);
									fcn(linkNames, linkTitle);
								});
							}
						}
						return false;
					});
				}
				
				
				// Newskategorie auswählen, falls auf Kategorieliste geklickt wurde
				newlistBox.on("click", '.fetchCat', function(){
					var newsCat		= $(this).val();
					var newsCatName = $(this).attr('data-catname');
						
					cc.conciseChanges		= true; // Änderungen merken
					 
					mediaList.siblings('input').slice(0,1).val(newsCatName); // Newscat übernehmen in inputfeld
					mediaList.siblings('input').slice(1,2).val(newsCat); // Newscat übernehmen in inputfeld
					$(this).closest('div.listBox').removeListBox();
					$("div.dimDiv").remove();
					return false;
							
				});
				
				// Seite auswählen, falls auf Seitenliste geklickt wurde
				newlistBox.on("click", '.fetchcon[data-action="fetchcon"]', function(){
					
					var fetchId		= $(this).val();
					var fetchName	= $(this).restoreTitleTag();
						
					cc.conciseChanges		= true; // Änderungen merken
					
					// Falls die Inhaltselemente einer Seite übernommen werden sollen (edit), Bestätigung holen
					jConfirm(ln.confirmfetchcon + "<strong>" + fetchName + "</strong>", ln.confirmtitle, function(result){
												   if(result === true){ // ...Löschen bestätigen
														document.location.href = targetUrl + '&fetchid=' + fetchId;
												   }
					});
					return false;
							
				});
				
				// Ordner auswählen, falls auf Ordnerliste geklickt wurde
				newlistBox.on("click", '.mediaSelection.fetch.folder', function(){
					var fetchFolder = $(this).attr('data-file');
					
					mediaList.siblings('input.filesFolder').val(fetchFolder).focus().blur(); // Ordnernamen übernehmen in inputfeld
					$(this).closest('div.listBox').not('.gallery').removeListBox();
					$("div.dimDiv").not('.gallery').remove();
					return false;
							
				});
				
				// Ordner öffnen, falls auf einen Ordner geklickt wurde
				newlistBox.on("click", 'a.openFolder', function(){
					var folderName		= $(this).attr('href');
					var folderTarget	= mediaListLink.attr('data-url').split("&folder=")[0];
					
					// Falls die Inhaltselemente einer Seite übernommen werden sollen (edit), Bestätigung holen
					mediaListLink.attr('data-url', folderTarget + '&folder=' + folderName);
					
					$.cookie("recentFilesFolder", folderName, { expires: 7, path: '/' });
					
					mediaListLink.click().click();
					
					return false;
							
				});
				
				// Zum Elternordner wechseln, falls auf das OrdnerUp-Symbol geklickt wurde
				newlistBox.on("click", 'a.folderUp', function(){
					var folderName		= $(this).attr('href');
					var folderTarget	= mediaListLink.attr('data-url').split("&folder=")[0];
					
					$.removeAllToolTips();
					
					// Falls die Inhaltselemente einer Seite übernommen werden sollen (edit), Bestätigung holen
					mediaList.not('.gallery').children(mediaListLink).attr('data-url', folderTarget + '&folder=' + folderName);
					
					$.cookie("recentFilesFolder", folderName, { expires: 7, path: '/' });
					
					mediaListLink.click().click();
					
					return false;
							
				});
				
				// Default-Ordner wechseln, falls auf das Ordnerändern-Symbol geklickt wurde
				newlistBox.on("click", '.changeDefaultFolder', function(){
				
					nestedListBox		= true;
					var changeButton	= $(this);
					var targetHref		= changeButton.attr('href').split("type=")[0];
					var originalFolder	= mediaList.attr('data-type');
					var currentFolder	= mediaListLink.attr('data-type');
					var recentFolder	= currentFolder;
					var recentFilesFolder	= "";
					
					$.getWaitBar();
					
					if(!changeButton.hasClass('openDefaultFolder')){
					
						if(currentFolder == "files"){
							currentFolder = originalFolder;
						} else {
							if(typeof($.cookie("recentFilesFolder")) != "undefinded" && $.cookie("recentFilesFolder") != null){
								recentFilesFolder = "&folder=" + $.cookie("recentFilesFolder");
							}
							currentFolder = changeButton.attr('data-type');
							mediaListLink.attr('data-type', currentFolder);
						}
					}else{
						currentFolder = changeButton.attr('data-type');
					}
					targetHref += "type=" + currentFolder + recentFilesFolder;

					mediaListLink.removeClass(recentFolder).addClass(currentFolder);
					mediaListLink.attr('data-type', currentFolder);
					// Falls die Inhaltselemente einer Seite übernommen werden sollen (edit), Bestätigung holen
					mediaListLink.attr('data-path', 'media/' + currentFolder + '/').attr('data-url', targetHref).click().click();
				
					return false;
							
				});
				
				// Falls auf neuen Ordner anlegen geklickt wurde
				newlistBox.on("click", '.newFolder', function(){
					var nfInput			= $(this).closest('.newFolderBox').find('input[name="newFolderName"]:first');
					var folderName		= nfInput.val();
					var currTarget		= mediaListLink.attr('data-url');
					var currFolder		= currTarget.split("&folder=");
					var parentFolder	= typeof(currFolder[1]) !== "undefined" && currFolder.length > 1 && currFolder[1] != "#" ? currFolder[1] : '';
					var folderTarget	= $(this).attr('data-url') + "&parentfolder=" + parentFolder + "&foldername=" + folderName;
					
					// Ordnernamen überprüfen
					var regex = /^[A-Za-z0-9-_]+$/;
					var regexInit = /[A-Za-z0-9]/;
			
					if(folderName == "") {
						jAlert(ln.foldernamefirst, ln.alerttitle);
						nfInput.focus();
						return false;
					} else {
					if(folderName.length > 64 || !regex.exec(folderName) || !regexInit.exec(folderName[0])) {
						jAlert(ln.checkfoldername, ln.alerttitle);
						return false;
					}
					var folderExists = false;
					newlistBox.find('.openFolder').each(function(){
						if($(this).text().replace(/\n/g, "").replace(/\r/g, "") == folderName){
							folderExists = true;
						}
					});
					if(folderExists){
						jAlert(ln.folderexists.replace("$1", '<strong>' + folderName + '</strong>'), ln.alerttitle);
						return false;
					}}
					
					$.ajax({
						url: folderTarget,
						success: function(ajax){

							// ListBox neu laden
							mediaListLink.click().click();
						}
					});
					
					return false;
				
				});
	
	
				// Anlegen eines Inhaltelements (Ajax-ListBox)
				newlistBox.filter('.insertElement').on("click", '.contentType', function(){
				
					var conType		= $(this).val();
					var targetUrl	= mediaList.siblings('input.ajaxaction').val() + '&type=' + conType + (feEditing ? '&fe=1' : '');

					newlistBox.fadeOut(300, function(){ $(this).removeListBox(); });
					
					if(feEditing){
						$.doAjaxActionFE(targetUrl, mediaList, true);
					}else{
						$.doAjaxAction(targetUrl, true);
					}
					return false;
				
				});


				// Reparieren einer Bildergalerie
				newlistBox.on("click", '.repairGallery', function(){
					
					var elem		= $(this);
					var targetUrl	= $(this).attr('data-url');
					var promt		= ln.repairgall;

					$.ajax({
						url: targetUrl
					}).done(function(ajax){
						elem.closest('div.listBox').removeListBox();
						mediaListLink.click();
						if(ajax != 1 && ajax != "1"){
							promt = ln.dberror;
						}
						jAlert(promt, ln.alerttitle);
						return false;
					});
					return false;
				});
				
				
				return false;
			}
		});


		// Falls eine Bilddatei zum hochladen gewählt wurde
		$('input.newUploadFile').change(function(){
										 
			var title	= $(this).val();
			var altText	= title.split(".");
			
			if($(this).closest('div.fileSelBox').children('input.existingFile').length) {
				$(this).closest('div.fileSelBox').children('input.existingFile').val('').hide();
			}
			
			var previewBoxDom = $(this).closest('div.fileSelBox').children('div.previewBox');
			// Falls Bild
			if(previewBoxDom.hasClass('img')) {
				previewBoxDom.children('img').attr('data-img-src', cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/placeh_upload.png');
				previewBoxDom.children('img').attr('src', cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/placeh_upload.png');
			}else{
				previewBoxDom.children('*').hide();
				previewBoxDom.append('<img src="' + cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/placeh_upload.png" />');
			}
			previewBoxDom.children('img').attr('title', altText[0]); // title-Text von Bilddateiname überbehmen
			previewBoxDom.children('img').attr('alt', altText[0]); // title-Text von Bilddateiname überbehmen
			$(this).closest('div.fileSelBox').children('input.altText').val(altText[0]); // alt-Text von Bilddateiname überbehmen
		});


	// Galeriebild-Details
	$('body').on("click", '.gallViewImage .editgall', function() {
		
		if($('.cc-gallery-item-details').length){
			return false;
		}
	
		var modalBox		= $('<ul class="adminArea cc-gallery-item-details"></ul>');
		var gallForm		= $(this).closest('form');
		var itemBox			= $(this).closest('.gallentry').children('.galleryItemCaptionBox');
		var mediaList		= gallForm.closest('div.mediaList');
		var editForm		= gallForm.clone();
		var newItemBox		= itemBox.clone().show();
		var gallTextC		= newItemBox.find('.galleryEditor');
		var submitPanel		= gallForm.find('li.submit').clone().show();
		var dialogClass		= 'ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons';
		var closeBtn		= $('<button type="button" class="galleryItemCaptionBox-close ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-close" role="button"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span></button>');
		var gallName		= gallForm.find('input[name="gall_name"]');
		var now		= $.now();
		var edID	= 'gallImgText-' + now;
		
		if(typeof(gallName) != "object"){
			return false;
		}
		
		gallName			= gallName.clone();
		
		gallTextC.attr('id', edID);
		
		editForm.find('.mce-tinymce').remove();
		newItemBox.find('.mce-tinymce').remove();		
		//newItemBox.removeAttr('style');
		newItemBox.addClass(dialogClass);
		newItemBox.draggable({handle: '.move'});
		newItemBox.children('.boxHeader').prepend(closeBtn).show();
		newItemBox.children('.galleryItemCaptionBox-content').find('.previewBox').show();
		newItemBox.append(submitPanel);
		editForm.html("").append(newItemBox);
		editForm.append(gallName);
		
		modalBox.appendTo(mediaList);
		modalBox.append(editForm);
		
		newItemBox.show(function(){
	
			newItemBox.find('.toggleEditor').attr('data-target', edID);		
			
			//tinymce.EditorManager.execCommand('mceAddEditor',true, edID);
			$.mceAddEditors($('#' + edID));

			newItemBox.find('.cc-image-tags').tagEditor('destroy').tagEditor({
				maxLength: 2048,
				forceLowercase: false,
				delimiter: ", ;\n"
			});
			
			setTimeout(function(){

				var ed = tinymce.editors[edID];
				ed.theme.resizeBy('100%', 50);
				ed.show();		
				
				var boxW,
					boxH;
				
				modalBox.show();
				
				boxW	= parseInt(newItemBox.outerWidth());
				boxH	= parseInt(newItemBox.outerHeight());
				winH	= parseInt($.getWindowSize()[1]);
				
				modalBox.css({'max-height':boxH - 60 + 'px','margin-left':'-' + boxW /2 + 'px','margin-top':'-' + Math.min(winH /2, boxH /2) + 'px'}).hide().fadeIn();
			
			}, 300);
		
		}).fadeTo(500, 1);
	
		// Galeriebild-Details
		$('.galleryItemCaptionBox-close').click(function() {
			modalBox.remove();
		});

		return false;
	});


	// Galeriebild umbenennen
	$('body').on("dblclick", '.gallViewImage .gallentry .preview', function() {
		$(this).closest('.gallentry').find('.icons.rename .dialog').click();
		return false;
	});
		
		return this;
	
	}, // Ende function listMedia

	
	
	$.updateGalleryList = function(fileObj){
	
		// sortIDs neu verteilen
		var parentUL = fileObj.closest('ul#sortableGallery');
		fileObj.fadeOut(500, function(e){
			fileObj.remove();
			parentUL.children('li').each(function(index, domEle){
				var newID = parseInt(index +1);
				$(domEle).attr('data-sortid', newID);
				$(domEle).attr('data-newsortid', newID);
			});
		});
									
		// Anzahl aktualisieren
		var fileCountObj	= $('label.gallCount strong');
		var fileCountOld	= fileCountObj.html();
		var fileCountNew	= parseInt(fileCountOld) -1;
		fileCountObj.html(fileCountNew);

		return fileCountNew;
	
	}; // Ende function updateGalleryList

	
	
	$.valignListBox = function(listBox, vSize, dir){
	
		// sortIDs neu verteilen
		if(typeof(vSize) != "undefined"){
			var newPos	= Math.abs(vSize) / 2;
			listBox.animate({top : (dir ? '-' : '+') + '='+newPos+'px'}, 300);
		}
	
	};

	
	
	$.fn.removeListBox = function(callback){
	
		var listBox = $(this);
		
		listBox.fadeOut(200, function(){
		
			var lbWrapper	= listBox.parent('.listBoxWrapper');
			var dimDiv		= lbWrapper.siblings('div.dimDiv');
			
			// elFinder-Object entfernen
			if($("#elfinder").length){
				$("#elfinder").elfinder(null);
			}
			
			// DimDiv entfernen
			if(dimDiv.length){
				if(!listBox.hasClass('filemanager')
				|| !$('body').find('.listBox').not(listBox).length
				){
					dimDiv.not('.gallery').remove();
				}
				if(listBox.hasClass('gallery')){
					$('.dimDiv.gallery').remove();
				}
			}
			
			// TinyMCE-Modal entfernen
			if($('div.clearlooks2').length){
				$('div.clearlooks2').fadeIn(200);
			}
			
			// schließen
			$('div.editButtons').removeClass('forceShow'); // FE
			$('.pimg').remove();

			// listBox entfernen
			lbWrapper.fadeOut(200, function(){
				
				$(this).remove();
				
				// Ggf. callback
				if(typeof(callback) == "function"){
					callback();
				}
			});
		});
	
	};


	
	// Schließen-Button geklickt
	$('body').off("click", "div.listBox .closeListBox").on("click", "div.listBox .closeListBox", function(e){
	
		var listBox = $(this).closest("div.listBox");
		
		// Falls ein Dialogfenster offen ist, nicht schließen, sondern Meldung ausgeben
		if(listBox.find('.elfinder-file-edit').length){
			jAlert(ln.closefirst, ln.alerttitle);
			return false;
		}
	
		listBox.removeListBox();
		
		return false;
	
	});

})(jQuery);
