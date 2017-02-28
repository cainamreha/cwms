/* FE editing */

var conciseCMS			= conciseCMS || {regSess: false};
var cc					= conciseCMS;
var ln					= cc.ln || {};
cc.domIsLoaded			= false;
cc.skipEditDivsAdjust	= false;


// Define http root
cc.getHttpRoot	= function() {

	var root = document.location.protocol + '//' + document.location.host;
	if(document.location.host == "localhost"){
		root += '/' + document.location.pathname.split('/', 1)[1];
	}
	return root;
};
cc.httpRoot = cc.httpRoot || cc.getHttpRoot();

// Do not use History
cc.useHistory	= false;


// load init js
head.ready('ui', function(){
	head.load({ ccInitScript: cc.httpRoot + "/system/access/js/concise.init.min.js" }, function(){
		conciseCMS.fe	= true;
		head.ready('jquery', function(){
			if(!ccLoadingImg.src){
				cc.setLoadingImages(conciseCMS.fe);
			}
		});
	});
});

// headJS ready
head.ready(function () {


$(document).ready(function(){

	// Buttonbeschriftung für jConfirm
	$.alerts.okButton		= 'Ok';
	$.alerts.cancelButton	= ln.cancel;
	$.alerts.dialogClass	= 'ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable';

	
	// Contextmenu
	head.load(	cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.contextMenu.css",
				{contextmenu: cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.contextMenu.min.js"},
				{contextmenuui: cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.ui.position.js"},
				{contextmenujs: cc.httpRoot + "/system/access/js/contextMenu.min.js"}
	);
	/*
	Modernizr.load([{
		load: [
			cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.contextMenu.css",
			cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.contextMenu.min.js",
			cc.httpRoot + "/extLibs/jquery/contextMenu/jquery.ui.position.js",
			cc.httpRoot + "/system/access/js/contextMenu.min.js"
		]
	}]);
	*/

	cc.windowH	= $(window).height(),
	cc.windowW	= $(window).width();
	
	// Anpassen von editDiv-Elementen bei window resize (nur bei width Änderung)
	$(window).on('resize',function(){
		
		var newW	= $(window).width();
		
		if(newW <= cc.windowW +100
		&& newW >= cc.windowW -100
		){
			return false;
		}
		
		$.getWaitBar();
		setTimeout(function(){
			location.reload();
		}, 10);
	
	});
	
	
	// Linkaufruf bei vorliegenden Änderungen verhindern
	$('body').on("click", 'a[href]:not([href^="#"], [data-mce-href], .mediaList a, a.cc-editNewElement)', function(e){
	
		if(cc.conciseChanges == true
		|| $('.innerEditDiv.current').length
		){
			e.preventDefault();
			e.stopPropagation();
			
			if($(this).closest('.innerEditDiv.current').length){
				return false;
			}
			
			var href = $(this).attr('href');
			
			jConfirm(ln.savefirst, ln.confirmtitle, function(result){
							
				if(result === true){										
					$.getWaitBar();
					document.location.href = href;
				}
			});
			return false;
		}
	});

	
	// Einblenden von editDiv-Elementen
	$('body').on("mouseenter", 'div.innerEditDiv', function(e){
	
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var ele			= $(this);
		
		// Falls ein Element bearbeitet wird, event abbrechen
		if(!cc.domIsLoaded
		|| $('.innerEditDiv.current').length
		|| $('body').find('.ui-resizable-resizing').length
		|| $('body').find('.ui-sortable-helper').length
		|| $('body').find('div.listBox').length
		|| ele.hasClass('cc-element-init')
		){
			$('*').unbind("mouseenter, mouseleave", function(e){
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				return false;
			});
			return false;
		}
		
		var editDiv		= ele.parent('div.editDiv');
		var conType		= ele.attr('data-type');
		var conDiv		= ele.children('div.editContent');
		var conElem		= $.getConElem(conDiv);
		
		// Höhenanpassung Korrektur (ggf. bei Elementen mit Bildern erforderlich)
		ele.css('height', 'auto');
		conElem.css('height', 'auto');

		$.adjustEditElement(ele, conDiv, conElem);
	
		var ebp		= ele.not('.current').children('div.editButtons');
		var bpH		= ebp.outerHeight();
		var elTop	= ele.offset().top - cc.windowScrollTop;
		var bpPos	= 'top';
		var bpPosA	= 'bottom';
		
		if(elTop < bpH){
			bpPos	= 'bottom';
			bpPosA	= 'top';
		}
		
		ele.addClass('cc-element-highlight');
		ele.children('div.editDivFrame').stop(true, true).css('z-index','auto').fadeIn(200);
		ebp.not('.hide').hide().css(bpPos, '-1px').css(bpPosA, 'auto').css('margin-' + bpPos, '-' + bpH + 'px').delay(400).fadeIn(200);
		ele.not('.current').find('.quickEditPanel').hide().delay(400).fadeIn(300);

		// Resizable div
		ele.getResizableEditDiv();
		
		// Resizable bei anderen editDivs entfernen
		$('.innerEditDiv:not(.ui-resizable-resizing)').not(this).resizableDestroy();
	
	}).on("mouseleave", 'div.innerEditDiv', function(e){
		
		e.preventDefault();
		e.stopImmediatePropagation();

		var ele			= $(this);
		var conType		= ele.attr('data-type');
		var imgEle		= ele.find('.imageElement');
		
		// Resizable: falls gerade resized wird, nicht ausgehen
		if(ele.hasClass('ui-resizable-resizing')
		|| $('body').find('.ui-resizable-resizing').length
		|| $('body').find('.ui-sortable-helper').length
		|| ele.hasClass('current')
		|| ele.hasClass('forceShow')
		){
			return false;
		}
		
		ele.removeClass('cc-element-highlight');
		
		ele.not('.current').children('div.editDivFrame, div.editButtons').stop(true, true).fadeOut(200);
		ele.not('.current').children('div.editDivFrame').css('z-index','-1');
		ele.not('.current').find('.quickEditPanel').stop(true, true).fadeOut(200);
	
		ele.resizableDestroy();
		imgEle.resizableDestroy();

	});
	
	// Resizable images
	$('body').on("mouseenter", 'div.innerEditDiv[data-type="img"]:not(".ui-resizable-resizing") .imageWrapper .imageElement:not(.resizable-disabled)', function(e){
	
		e.preventDefault();
		
		// Falls ein Element bearbeitet wird, event abbrechen
		if($('.innerEditDiv.current').length
		|| $('body').find('.ui-resizable-resizing').length
		|| $('body').find('.mediaList div.listBox').length
		){
			return false;
		}
		
		var ele			= $(this);
		var editDiv		= ele.closest('div.innerEditDiv');
		
		// Resizable: falls gerade resized wird, nicht ausgehen
		if(editDiv.hasClass('ui-resizable-resizing')){
			ele.resizableDestroy();
			return false;
		}
		
		// Ggf. resizable img
		ele.getResizableImage();
	
	}).on("mouseleave", 'div.innerEditDiv .imageWrapper .imageElement', function(e){
		
		e.preventDefault();
		
		var ele			= $(this);
		var res			= $('body').find('.ui-resizable-resizing');
		
		// Resizable: falls gerade resized wird, nicht ausgehen
		if(ele.hasClass('ui-resizable-resizing')
		|| res.length
		){
			return false;
		}
		
		// Ggf. resizable img destroy
		//ele.resizableDisable();
	
	});
	
	
	// Neues Inhaltelement
	$('body').on("click", 'div.editDiv .newcon', function(){
	
		var currNewCon	= $(this).next('div.addCon');
		var innerDiv	= $(this).closest('.innerEditDiv');
		var editConDiv	= innerDiv.children('.editContent');
		var editButtons	= innerDiv.children('.editButtons');
		var separator	= $(this).siblings('span.feButtonSeparator');
		
		editButtons.addClass('forceShow');

		// Resizable entladen
		if(typeof(innerDiv.resizable) == "function"
			&& typeof(innerDiv.data("ui-resizable")) != "undefined"
		){
			//innerDiv.resizable("destroy");
			innerDiv.resizableDisable();
		}
		
		currNewCon.toggle("fast", function(){
			$('div.addCon').not(currNewCon).hide();
			if($("div.listBox").length){
				$("div.listBox").remove();
			}
			if($("div.dimDiv").length){
				$("div.dimDiv").remove();
			}
			if(currNewCon.is(":visible")){
				innerDiv.addClass('forceShow');
				editButtons.addClass('forceShow');
				$.zIndexAreas(separator, true);
				showListBox = true;
				currNewCon.find('.showListBox').click();
			}else{
				innerDiv.removeClass('forceShow');
				editButtons.removeClass('forceShow');
				$.zIndexAreas(separator, false);
			}
		});
		return false;
	});
	
	
	// Anlegen eines Inhaltelements (Select)
	$('div.editDiv select.new_con').change(function(){
		var conType		= $(this).children('optgroup').children('option:selected').val();
		var targetUrl	= $(this).siblings('input.ajaxaction').val() + '&type=' + conType;
		$.doAjaxActionFE(targetUrl, false, true);
		return false;
	});


	// Bearbeiten/veröffentlichen eines Inhaltelements
	$('body').on("click", 'div.editDiv .editButtons *[data-action="editcon"]', function(e){
	
		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();
		
		var targetUrl	= $(this).attr('data-url');
		var urlExt		= "";
	
	
		// Kopieren/Einfügen/Löschen eines Inhaltelements (Ajax)
		if($(this).hasClass('copycon') || $(this).hasClass('cutcon') || $(this).hasClass('pastecon')){
			var elem = $(this);
			targetUrl	+= '&fe=1';
			$.doAjaxActionFE(targetUrl, false, true, function(){
				if(elem.hasClass('pastecon')){
					$('div.editDiv .editButtons .pastecon').hide();
					$('div.editDiv .editButtons .cancelpaste').hide();
				}
				$.removeWaitBar(true);
			});
			return false;
		}
		// Einfügen eines Inhaltelements abbrechen (Ajax)
		if($(this).hasClass('cancelpaste')){
			$.doAjaxActionFE(targetUrl, false, false, function(){
				$('div.editDiv .editButtons .pastecon').hide();
				$('div.editDiv .editButtons .cancelpaste').hide();
				$.removeWaitBar(true);
			});
			return false;
		}
		// Löschen eines Inhaltselements (Ajax)
		if($(this).hasClass('delcon')){
			jConfirm(ln.confirmdelcon, ln.confirmtitle, function(result){
				if(result === true) {
					targetUrl	+= '&fe=1';
					$.doAjaxActionFE(targetUrl, false, true);
				}
			});
			return false;
		}
		// Bildergalerie
		if($(this).hasClass('editgall')){
			urlExt = $(this).closest('div.editButtons').siblings('div.editContent').find('div.cc-gallery').attr('data-gallname');
			targetUrl += urlExt.replace(" ","");
		}
		// Element veröffentlichen
		if($(this).hasClass('pubcon')){
     
			var pubElem			= $(this);
			var innerEditDiv	= pubElem.closest('.innerEditDiv');
			
			$('html').ajaxStart(function(){
				pubElem.on('click',function(){
					return false;
				});
			}).ajaxStop(function(){
				pubElem.off('click');
			});

			
			var pubStatus		= pubElem.attr('data-publish');
			
			pubElem.loading();
						
			$.ajax({
				url: targetUrl,
				cache: false,
				success: function(result){

					if(result == 1 || result == '1'){
					
						pubElem.siblings('[data-actiontype="pubcon"]:hidden').css('display','inline-block');
						pubElem.css('display','none').loadingRemove();
					
						if(pubStatus == 1){
							innerEditDiv.addClass('hiddenElement');
						}else{
							innerEditDiv.removeClass('hiddenElement');
						}
						pubElem.loadingRemove();
					}else{
						pubElem.loadingRemove();
						jAlert(ln.dberror, ln.alerttitle);
					}
				}
			});
			return false;
		}
		
		// Bearbeiten im backend
		document.location.href = targetUrl;
		
		return false;
	});


	// Verschieben eines Inhaltelements
	$('body').on("click", 'div.editDiv .sortcon', function(){
		var targetUrl = $(this).attr('data-url');
		$.doAjaxActionFE(targetUrl, false, true);
		return false;
	});


	// Bearbeiten/kopieren eines Daten-Eintrags
	$('body').on("click", '.dataEditButtons *[data-action="editcon"]', function(e){
	
		e.preventDefault();
		e.stopPropagation();
	
		var targetUrl	= $(this).attr('data-url');
		
		// Kopieren eines Daten-Modul-Eintrags (Ajax)
		if($(this).hasClass('copydata')){
			locate = false;
			jConfirm(ln.confirmcopydata, ln.confirmtitle,					
				function(result){
					if(result !== true) {
						return false;
					}else{
						document.location.href = targetUrl;
					}
				}
			);
		}else{
			// Bearbeiten
			document.location.href = targetUrl;		
		}
		return false;
	});


	// Veröffentlichen von mehreren Einträgen (gallery)
	$('body').on("click", '.pubAll', function(e){
	
		var pubElem		= $(this);
		var pub			= 1;
		var unpub		= pubElem.hasClass('unpublish');
		
		
		if(unpub){
			pub = 0;
		}

		e.preventDefault();
		e.stopPropagation();
	
		// Falls keine Elemente ausgewählt
		if(!$('.markBox.highlight').length){
			jAlert(ln.nodatasel, ln.alerttitle);
			return false;
		}
		
		// Galeriedateien
		if(pubElem.hasClass('galleryFiles')){
		
			pubElem.loading(function(){
				
				$.getWaitBar(function(){
					
					var status		= pub == 1 ? 1 : 0;
					var entryFilter	= status ? '.hiddenImage' : ':not(.hiddenImage)';
					var previewDom	= pubElem.closest('.listBox').children('.innerListBox').children('form').children('.listItemBox').children('.sortable-container').children('.listEntry' + entryFilter).children('.gallentry').children('.previewBox');
					var markedElems	= previewDom.children('.markBox').filter('.highlight');
					var i = markedElems.length;
					
					if(i == 0){
						pubElem.loadingRemove();
						$.removeWaitBar(false);
						return false;
					}
					markedElems.each(function(){
					
						var pubEntry = $(this).closest('.gallentry').children('.editButtons-panel').children('.switchIcons').children('*[data-publish="' + status + '"]');
						
						setTimeout(function(){
							pubEntry[0].click();
							if(!--i ){
								pubElem.loadingRemove();
								$.removeWaitBar(false);
							}
						}, 5);
					});
				});
			});
			return false;
		}
	
	});


	// FE-Editing	
	// FE-editing von Text-, Image-Typ oder anderen Inhalten
	$('body').on("click",'\
	div.innerEditDiv > div.editButtons > .directedit,\
	div.innerEditDiv > div.editButtons > .cc-fe-edit-img,\
	div.innerEditDiv > div.editButtons > .cc-fe-edit-text',
	function() {
	
		var directeditBtn	= $(this);
		var innerEditDiv	= directeditBtn.closest('.innerEditDiv');
		var conType			= innerEditDiv.children('div.editDivFrame').attr("data-type");
		var editButtons		= innerEditDiv.children('div.editButtons');

		
		// Resizable entladen
		if(typeof(innerEditDiv.resizable) == "function"
			&& typeof(innerEditDiv.data("ui-resizable")) != "undefined"
		){
			//innerEditDiv.resizable("destroy");
			innerEditDiv.resizableDisable();
		}

		cc.directEditElement(conType, directeditBtn, innerEditDiv, editButtons);
	
		return false;
	
	});
	
	
	// FE-editing direct edit element
	cc.getDirectEditElement	= function(directeditBtn) {
	
		var mediaList	= "";
		var feScript	= directeditBtn.attr('data-url');
		
		$.getWaitBar();

		$.ajax({
			url: feScript,
			dataType: "json",
			async: false,
			cache: false,
			success: function(ajax){
			
				if(!ajax.html){
					jAlert(ln.feediterror, ln.alerttitle);
					return false;
				}
				
				mediaList = $(ajax.html);				
				
				// load css
				if(ajax.css){
				
					$.each(ajax.css, function(i,e){
						var url	= cc.getResourceUrl(e);
						if(!$('head').find('*[href="' + url + '"]').length){
							head.load(cc.getResourceUrl(e));
						}
					});
				}
				
				// load scripts
				if(ajax.scripts){
				
					var cnt = Object.keys(ajax.scripts).length;
					$.each(ajax.scripts, function(i,e){
						var key	= i.toString();
						var url	= cc.getResourceUrl(e);

						if(!$('head').find('*[src="' + url + '"]').length){
							head.load({key: url}, function(){
								if(!--cnt){
									cc.skipEditDivsAdjust = true;
									$.executeInitFunctions();
								}
							});
						}else{
							if(!--cnt){
								cc.skipEditDivsAdjust = true;
								$.executeInitFunctions();
							}
						}
					});
				}
				return mediaList;
			}
		}).done(function(ajax){
			return mediaList;
		});
		
		return mediaList;
	
	};
	
	
	// getDirectEditFloatingButtons
	cc.getDirectEditFloatingButtons = function(mediaList) {

		// Sticky edit element buttons
		var closeBtn	= mediaList.find('.closeDetailsBox');
		var saveBtn		= $('<button class="saveElementDetails btnDetailsBox cc-button close button-icon-only" title="' + ln.feeditsubmit + '"><span class="cc-admin-icons cc-icons cc-icon-ok">&nbsp;</span></button>');
		
		if(!closeBtn.siblings('.saveElementDetails').length){
			closeBtn.after(saveBtn);
		}
		
		closeBtn.closest('.custombox-modal-wrapper').scroll(function(){
			closeBtn.stickyElementBtn();
			saveBtn.stickyElementBtn(closeBtn.height() + 20);
		});
		
		saveBtn.click(function(){
			mediaList.find('.feEditButton.submit')[0].click();
		});
		
		return mediaList;
	
	};
	
	
	// FE-editing direct element edit
	cc.directEditElement	= function(conType, directeditBtn, innerEditDiv, editButtons) {
	
		var editConDiv		= innerEditDiv.children('.editContent');
		var editDetails		= editButtons.siblings('.editDetailsBox');
		var mediaList		= "";
		var mediaFiles		= "";
		var isBasicEle		= false;

		// Element edit Inhalte holen
		// Falls Textelement
		if(conType == "text"
		&& directeditBtn.hasClass('cc-fe-edit-text')
		){
			innerEditDiv.setCurrent();
			innerEditDiv.children('div.editButtons').hide();
			cc.directEditText(innerEditDiv);
			return false;
		}
		
		// Falls Bild
		if(conType == "img"
		&& directeditBtn.hasClass('cc-fe-edit-img')
		){
			mediaList		= editDetails.children("div.mediaList");
		// Andere Inhaltelemente
		}else{
			isBasicEle		= true;
			mediaList		= cc.getDirectEditElement(directeditBtn);
			editDetails.append(mediaList);
			$.toggleSeparator(directeditBtn.prev('span.feButtonSeparator'));
		}
		
		if(!mediaList
		|| typeof(mediaList) != "object"){
			return false;
		}
		
		// Falls Bild, die Maße des aktuellen Bildes übernehmen
		if(conType == "img"){
			var imgWidth 	= Math.round(editConDiv.find('.imageElement').getElementWidth());
			var imgHeight 	= Math.round(editConDiv.find('.imageElement').getElementHeight());
			innerEditDiv.find('input.imgWidth').val(imgWidth);
			innerEditDiv.find('input.imgHeight').val(imgHeight);
		}
		
		var targetID		= mediaList.closest('.editDetailsBox').attr('id');
		var scrollPos		= $(window).scrollTop();
		cc.windowScrollTop	= scrollPos;
		
		// Element anzeigen
		Custombox.open({
			target: '#' + targetID,
			speed: 300,
			width: 1004,
			effect: 'push',
			position: ['left','top'],
			overlayColor: '#405b8e',
			overlayOpacity: 0.3,
			overlayClose: false,
			overlayEffect: '',
			escKey: false,
			zIndex: 9900,
			open: function(){
				
				mediaList.toggle();
				
				mediaList.draggable({"handle": mediaList.find('.elements').children('.cc-contype-heading')});
				
				// add tinyMCE
				setTimeout(function(){
					if(ccReloadEditors || !head.browser.ff){ // do not add in Firefox (bug: double init)
						$.mceAddEditors($("textarea.cc-editor-add", mediaList));
					}
					$.setTrueFalseIcons(false);
				}, 200);
				
			},
			close: function(){
				
				mediaList.toggle();
				
				innerEditDiv.unsetCurrent();
				editButtons.siblings('.quickEditPanel').hide();
				editButtons.hide();

				$(window).scrollTop(scrollPos);
				
				ccReloadEditors = true;
				
				$.resetChangesVars();
				
				if(isBasicEle){
					$.toggleSeparator(directeditBtn.prev('span.feButtonSeparator'));
				}
			},
			complete: function(){
	
				// Sticky edit element buttons
				cc.getDirectEditFloatingButtons(mediaList);

				if(mediaList.css("display") == "block"){
					editButtons.show();
					innerEditDiv.setCurrent();
				}

				$.removeWaitBar(true);
			}
		});

		// Custombox close event on click
		mediaList.children(".closeDetailsBox").bindCboxCloseEvent(isBasicEle);
		
		return false;
	
	};
	
	
	// FE-editing von Text-Typ Inhalten
	cc.directEditText	= function(innerEditDiv) {
		
		var editConDiv	= innerEditDiv.children('div.editContent');
		var textElem	= editConDiv.children('div.editableText');
		var htmlContent = "";
		
		if($('div.editableText.current').not(textElem).length
		|| $('.controlsQuickEdit').length
		){
			// Falls erster Funktionsaufruf, 1 Alert ausgeben (verhindert mehrmaliges Anzeigen des Alerts nach mehrfacher Bearbeitung)
			if(typeof(init) == "undefined" || init){
				jAlert(ln.feeditopen, ln.alerttitle);
			}
			return false;
		}
		if(textElem.hasClass('current')) {
			return false;
		}
		
			
		// Sortierung ausschalten
		$("div.contentArea").disableSortable();
		
		
		innerEditDiv.find('span.quickEditPanel').addClass('hide').removeAttr('style'); // Quick-Edit Button verstecken		
		innerEditDiv.children('div.editButtons').children('span.feButtonSeparator').html('▼');
		
		var hasWrapper		= false;
		var textWrapper		= textElem;
		var wrapperClass	= "";
		var wrapperID		= "";
		var wrapperStyle	= "";
		
		// Falls ein textWrapper-Div das Textelement umgiebt (z.B. bei Angabe von Styledefs), den Inhalt des betreffenden child-Divs nehmen
		if(textElem.children('div.textWrapper').length){
			hasWrapper		= true;
			textWrapper		= textWrapper.children('div.textWrapper');
			wrapperClass	= textWrapper.attr('class');
			wrapperID		= textWrapper.attr('id');
			wrapperStyle	= textWrapper.attr('style');
		}
		htmlContent			= textWrapper.html();
		var pageID			= editConDiv.attr('data-pageid');
		var conArea			= editConDiv.attr('data-pagearea');
		var conID			= editConDiv.attr('data-connum');
		var divWidth		= parseInt(innerEditDiv.getElementWidth());
		var divHeight		= parseInt(innerEditDiv.getElementHeight());
		var inlineTag		= (tinymce.majorVersion == 3 ? "textarea" : "div");
		
		var formTag	=	"<form><" + inlineTag + " name='feEditText' class='" + wrapperClass + " feTextEdit'>" + htmlContent + "</" + inlineTag + "><div class='feButtonPanel panel-bottom'><button type='button' class='feEditButton cc-button button " + (divWidth > 360 ? "" : "narrow ") + "submit text' name='editCon' value='" + ln.feeditsubmit + "' title='" + ln.feeditsubmit + "'><span class='cc-admin-icons cc-icons cc-icon-ok'>&nbsp;</span>" + (divWidth > 360 ? ln.feeditsubmit : "") + "</button><button type='button' class='feEditButton cc-button button " + (divWidth > 550 ? "" : "narrow ") + "reset' name='resetCon' value='" + ln.feeditcancel + "' title='" + ln.feeditcancel + "'><span class='cc-admin-icons cc-icons cc-icon-cancel'>&nbsp;</span>" + (divWidth > 550 ? ln.feeditcancel : "") + "</button></div></form>";
		var elemHeight	= textElem.height();
		elemHeight = Math.max(elemHeight+100,300); // Höhe des Textelements (mind. 200px)
		
		innerEditDiv.children('div.editButtons').addClass('hide').hide();
		textElem.addClass("current").hide().fadeIn(750);
		textElem.html(formTag);
		
		
		// Editor init
		$.getTinyMCE_FE(elemHeight);
		
		
		// HTML5
		var pluginExt = "";
		var buttonsExt = "";
		var definitionsExt = "";
	
	
		// Ajax für Content-Änderungen
		$('.feEditButton.submit.text').bind("click",function(){
			
			var thisElem	= $(this);
			
			tinyMCE.triggerSave();
			
			$form	= $(this).parent('form'),
			term	= tinyMCE.activeEditor.getContent(),
			url		= cc.httpRoot + "/system/access/feEdit.php";
			
			/* Send the data using post and put the results in a div */
			$.post(url, { feEditText: term, conArea: conArea, pageID: pageID, conID: conID, lang: cc.feLang },
				function(data) {
				
					// Falls ein Wrapper-Div vorhanden war, diesen wieder umschließend hinzufügen
					if(hasWrapper){
						var termExt = '<div ';
						if(wrapperID != "" && wrapperID != undefined){
							termExt += 'id="' + wrapperID + '"';
						}
						if(wrapperClass != "" && wrapperClass != undefined){
							termExt += ' class="' + wrapperClass + '"';
						}
						if(wrapperStyle != "" && wrapperStyle != undefined){
							termExt += ' style="' + wrapperStyle + '"';
						}
						term = termExt + '>' + term.replace(/{#root}/g, cc.httpRoot) + '</div>';
					}
					thisElem.closest('div.innerEditDiv').children('div.editButtons').children('span.feButtonSeparator').html('►');
					thisElem.closest('div.editableText').html(term).removeClass('current');
					//tinyMCE.execCommand("mceRemoveControl", true, tinyMCE.get(0));
					tinyMCE.activeEditor.remove();
					//feEditing(false);
					if(data == false) {
						jAlert(ln.feediterror, ln.alerttitle);
					}
					//alert("feEditText:" + term + " conArea:" + conArea +  " pageID:" + pageID + " conID:" +  conID + " lang:" + cc.feLang);
				}
			);
			
			$(this).closest('div.innerEditDiv').unsetCurrent().children('div.editButtons').removeClass('hide').children('.fe-changes').show();
			$(this).closest('div.contentArea').find('div.editButtons').children('.fe-changes').show();
			
			innerEditDiv.find('span.quickEditPanel').hide().removeClass('hide'); // Quick-Edit Button anzeigen

			// Sortierung einschalten
			$("div.contentArea").enableSortable();
			
		});
		
		
		// Content-Änderungen verwerfen
		$('.feEditButton.reset').bind("click",function(){
		
			$(this).closest('div.innerEditDiv').unsetCurrent().children('div.editButtons').removeClass('hide').children('span.feButtonSeparator').html('►');
			//tinymce.remove(tinyMCE.get(0));
			//tinyMCE.execCommand("mceRemoveControl", true, tinyMCE.get(0));
			tinyMCE.activeEditor.remove();
			
			// Falls ein Wrapper-Div vorhanden war, diesen wieder umschließend hinzufügen
			if(hasWrapper){
				var termExt = '<div ';
				if(wrapperID != "" && wrapperID != undefined){
					termExt += 'id="' + wrapperID + '"';
				}
				if(wrapperClass != "" && wrapperClass != undefined){
					termExt += ' class="' + wrapperClass + '"';
				}
				if(wrapperStyle != "" && wrapperStyle != undefined){
					termExt += ' style="' + wrapperStyle + '"';
				}
				htmlContent = termExt + '>' + htmlContent + '</div>';
			}
	
			$(this).closest('div.editableText').html(htmlContent).removeClass('current');
			innerEditDiv.removeClass('cc-element-highlight');

			innerEditDiv.find('span.quickEditPanel').hide().removeClass('hide'); // Quick-Edit Button anzeigen
			
			// Sortierung einschalten
			$("div.contentArea").enableSortable();
			
			return false;
		});
		
		
		// Warnen, wenn auf Edit-Buttons eines anderes Elements geklickt wird
		$('div.editButtons .cc-button').mousedown(function(){
			
			if($('div.editableText.current').length) {

				jAlert(ln.feeditopen, ln.alerttitle);
				return false;
			}
		});
		
		return false;
	};
	
	
	// FE-editing update element
	cc.updateElement	= function(ajax, container) {
	
		//console.log(ajax	);
		$.replaceFEContent(ajax, container);
		//$.refreshPage();
		
		return false;
	
	};
	
	
	// FE-editing quick edit text
	$('body').on("click", 'div.innerEditDiv > span.quickEditPanel > .quickEditBtn', function(){
	
		var quickEditButton		= $(this);
		var quickEditButtons	= quickEditButton.parent().children('.quickEditBtn');
		var innerEditDiv		= $(this).closest('div.innerEditDiv');
		var editConDiv			= innerEditDiv.children('.editContent');
		var editButtonDiv		= innerEditDiv.children('div.editButtons');
		
		// Warnen, wenn bereits ein anderes Element bearbeitet wird
		if($('div.buttonsQuickEdit').length || $('div.editableText.current').length) {

			jAlert(ln.feeditopen, ln.alerttitle);
			return false;
		}
		
		// Falls text Element (editor edit)
		if(quickEditButton.hasClass('editorTextEditBtn')){
			editButtonDiv.find('.cc-fe-edit-text').click();
			return false;
		}
		
		// Falls img Element (quick edit)
		if(quickEditButton.hasClass('quickImgEditBtn')){
			editButtonDiv.find('.cc-fe-edit-img').click();
			return false;
		}
		
		// Falls img Element (size reset)
		if(quickEditButton.hasClass('quickImgResetBtn')){
			$.resetImgSize(innerEditDiv);
			return false;
		}
		
		// Falls gallery Element (open gall)
		if(quickEditButton.hasClass('quickOpenGalleryBtn')){
			return false;
		}
		
		var editElem		= editConDiv.find('div.textWrapper');
		var baseDiv			= editElem.parent('.editableText');
		var resContent		= editElem.html();
		var buttonPanel		= editButtonDiv.html();
		var divWidth		= parseInt(innerEditDiv.outerWidth());
		var divHeight		= parseInt(innerEditDiv.height());
		var isFloat			= editElem.css('float') != "undefinded" && editElem.css('float') != "none" ? true : false;
		
		
		// Falls leeres Element, leeren Paragrafen hinzufügen
		if(resContent.trim().length == 0){
			editElem.html('<p><br /></p>');
		}
		
		// QuickEditButtons
		var quickEditButtonPanel	= '<button type="button" class="confirmQuikEdit feEditButton cc-button button' + (divWidth > 360 ? "" : " narrow") + ' submit" title="' + ln.feeditsubmit + ' [Strg+Enter]" value="' + ln.feeditsubmit + '"><span class="cc-admin-icons cc-icons cc-icon-ok">&nbsp;</span>{%submit%}</button><button type="button" class="confirmQuikEdit feEditButton cc-button button' + (divWidth > 550 ? "" : " narrow") + ' reset" title="' + ln.feeditcancel + '" value="' + ln.feeditcancel + '"><span class="cc-admin-icons cc-icons cc-icon-cancel">&nbsp;</span>{%cancel%}</button>';
		
		var quickEditButtonDiv	= '<div class="buttonsQuickEdit feButtonPanel panel-bottom" style="width:' + divWidth + 'px;' + (isFloat ? ' position:relative; clear:both;' : '') + ' display:none;">' + quickEditButtonPanel.replace("{%submit%}", (divWidth > 360 ? ln.feeditsubmit : "")).replace("{%cancel%}", (divWidth > 550 ? ln.feeditcancel : "")) + '</div>';

		// QuickEditControls
		var quickEditControls	= '<div class="controlsQuickEdit feButtonPanel panel-top" style="display:none;"><button type="button" class="undoQuikEdit feEditButton cc-button button button-icon-only narrow" title="' + ln.feeditsubmit + '" value=""><span class="cc-admin-icons cc-icons cc-icon-undo">&nbsp;</span></button><button type="button" class="redoQuikEdit feEditButton cc-button button button-icon-only narrow" value="" title="' + ln.feeditsubmit + '"><span class="cc-admin-icons cc-icons cc-icon-redo">&nbsp;</span></button><button type="button" class="showHtmlSource feEditButton cc-button button button-icon-only narrow" title="' + ln.feeditsubmit + '" value=""><span class="cc-admin-icons cc-icons cc-icon-code">&nbsp;</span></button>' + quickEditButtonPanel.replace("{%submit%}", "").replace("{%cancel%}", "") + '</div>';

		innerEditDiv.resizableDisable();
		innerEditDiv.setCurrent();
		baseDiv.addClass('current');
		
		editElem.not('[contenteditable]').css('height','auto').closest('div.innerEditDiv').css('height','auto');
		editElem.not('[contenteditable]').attr("contenteditable", "true").closest('div.innerEditDiv').append(quickEditButtonDiv).closest('div.editDiv').find('.buttonsQuickEdit').fadeIn(600);

		var controlsIcons	= editButtonDiv.after(quickEditControls).parent('div.innerEditDiv').children('.controlsQuickEdit').children('input, button');
		controlsIcons.addClass("narrow").parent().fadeIn(600);
		
		editButtonDiv.addClass('hide').hide();
		editButtonDiv.siblings('.conTypeDiv').addClass('hide').hide();
		quickEditButtons.hide();
		
		editElem.focus();
		document.getSelection().removeAllRanges();
		
		// HTML Source Code
		$('.controlsQuickEdit .showHtmlSource').click(function(){
			var sourceCode	= editElem.html();
			if(sourceCode.indexOf('<textarea ') == 0){
				sourceCode = editElem.children('textarea#sourceCode').val();
				editElem.html("").append(sourceCode);
			}else{
				editElem.html("").append('<textarea id="sourceCode" style="height:' + (divHeight -2) + 'px;">' + sourceCode + '</textarea>');
			}
		});

	
		// Speichern via Ctrl+Enter
		editElem.keydown(function(e) {
			if (e.ctrlKey && e.keyCode === 13) {
				editElem.closest('div.innerEditDiv').find('.confirmQuikEdit.submit').click();
				return false;
			}
		});
		

		// Speichern
		$("div.feButtonPanel .confirmQuikEdit.submit").click(function() {
		
			var elem	= $(this);
			
			var	term	= editElem.html(),
				pageID	= editConDiv.attr('data-pageid'),
				conArea	= editConDiv.attr('data-pagearea'),
				conID	= editConDiv.attr('data-connum'),
				url		= cc.httpRoot + "/system/access/feEdit.php";
			
			if(editElem.hasClass('dataHeader')){
				term	= term.split('<span class="icons')[0];
			}
			
			if(term.indexOf('<textarea ') == 0){
				term = editElem.children('textarea#sourceCode').val();
				editElem.html("").append($("<div/>").html(term).text());
			}

			if(term.split("<").length < 3){
				term	= '<p>' + term + '</p>';
			}
			
			$.getWaitBar();			
			
			/* Send the data using post and put the results in a div */
			$.post(url, { feEditText: term, conArea: conArea, conID: conID, pageID: pageID, lang: cc.feLang }, function(data) {					

				if(data == false) {
					jAlert(ln.feediterror, ln.alerttitle);
				}else{
					editElem.html(term);
				}
				//alert(data+"feEditText:" + term + " conArea:" + conArea +  " pageID:" + pageID + cc.feLang);
				editElem.removeAttr("contenteditable").blur();
				$("div.buttonsQuickEdit, .controlsQuickEdit").fadeOut(300, function(){ $(this).remove();
					editButtonDiv.removeClass('hide');
					editButtonDiv.siblings('.conTypeDiv').removeClass('hide').removeAttr('style');
					quickEditButtons.removeAttr('style');
					$.removeWaitBar(true);
					editButtonDiv.closest('div.contentArea').find('div.editButtons').children('.fe-changes').show()
				});
			});
			
			innerEditDiv.unsetCurrent();
			baseDiv.removeClass('current');

		});
		
		
		// Verwerfen
		$("div.feButtonPanel .confirmQuikEdit.reset").click(function() {
		
			var elem	= $(this);

			editElem.html(resContent);
			editElem.removeAttr("contenteditable").blur();
			innerEditDiv.unsetCurrent();
			baseDiv.removeClass('current');
			$("div.buttonsQuickEdit, .controlsQuickEdit").fadeOut(300, function(){
				$(this).remove();
				editButtonDiv.removeClass('hide');
				editButtonDiv.siblings('.conTypeDiv').removeClass('hide').removeAttr('style');
				quickEditButtons.removeAttr('style');
			});
		});
	});


	// undo
	$('body').on("click", '.controlsQuickEdit .undoQuikEdit', function(){
		document.execCommand("undo");
	});
	
	// redo
	$('body').on("click", '.controlsQuickEdit .redoQuikEdit', function(){
		document.execCommand("redo");
	});


	// Bei Klick auf Quick-Edit-Symbol (Data)
	$('body').on("click", "div.dataDetail button.quickEditBtn", function() {
		$(this).closest('.quickEditDataText').dblclick();
	});


	// Datenmodul direct editing
	// FE-editing von Daten Textinhalten
	$('body').on("dblclick", "div.dataDetail .dataHeader, div.dataDetail div.dataTeaser, div.dataDetail div.dataText", function(e) {
	
		
		e.preventDefault();
		e.stopPropagation();
		
		// Warnen, wenn bereits ein anderes Element bearbeitet wird
		if($('div.buttonsQuickEdit').not($(this).siblings()).length) {

			jAlert(ln.feeditopen, ln.alerttitle);
			return false;
		}
		
		$(this).closest('.dataDetail').find('.quickEditBtn').hide(); // QuickEditButton aus Inhalt entfernen
		$(this).find('.quickEditBtn').remove(); // QuickEditButton aus Inhalt entfernen
		
		var editElem		= $(this);
		var innerEditDiv	= editElem.closest('.innerEditDiv');
		var editButtonDiv	= innerEditDiv.children('div.editButtons');
		var resContent		= editElem.html();
		var elemWidth		= parseInt(editElem.getElementWidth());
		var elemHeight		= parseInt(editElem.getElementHeight());
		var elemMargin		= parseInt(editElem.css('margin-bottom')) * -1;
		
		// Falls leeres Element, leeren Paragrafen hinzufügen
		if(resContent.trim().length == 0){
			editElem.html('<p></p>');
		}
		
		innerEditDiv.setCurrent();
		editButtonDiv.addClass('hide').hide();
		editButtonDiv.siblings('.conTypeDiv').addClass('hide').hide();
		
		// QuickEditButtons
		var quickEditButtons	= '<button type="button" class="confirmDataEdit feEditButton cc-button button submit" title="' + ln.feeditsubmit + ' [Strg+Enter]" value="' + ln.feeditsubmit + '"><span class="cc-admin-icons cc-icons cc-icon-ok">&nbsp;</span>{%submit%}</button><button type="button" class="confirmDataEdit feEditButton cc-button button reset" value="' + ln.feeditcancel + '" title="' + ln.feeditcancel + '"><span class="cc-admin-icons cc-icons cc-icon-cancel">&nbsp;</span>{%cancel%}</button>';
		
		// QuickEditButtonsDiv
		var quickEditButtonsDiv	= '<div class="buttonsQuickEdit' + (elemWidth < 360 ? ' narrow' : '') + ' feButtonPanel panel-bottom" style="width:' + elemWidth + 'px; margin-top:' + elemMargin + 'px;">' + quickEditButtons.replace("{%submit%}", (elemWidth > 360 ? ln.feeditsubmit : "")).replace("{%cancel%}", (elemWidth > 500 ? ln.feeditcancel : "")) + '</div>';		

		// QuickEditControls
		var quickEditControls	= '<div class="controlsQuickEdit feButtonPanel panel-top" style="display:none;"><button type="button" class="undoQuikEdit feEditButton cc-button button button-icon-only narrow" title="' + ln.feeditsubmit + '" value=""><span class="cc-admin-icons cc-icons cc-icon-undo">&nbsp;</span></button><button type="button" class="redoQuikEdit feEditButton cc-button button button-icon-only narrow" value="" title="' + ln.feeditsubmit + '"><span class="cc-admin-icons cc-icons cc-icon-redo">&nbsp;</span></button><button type="button" class="showHtmlSource feEditButton cc-button button button-icon-only narrow" title="' + ln.feeditsubmit + '" value=""><span class="cc-admin-icons cc-icons cc-icon-code"></button>' + quickEditButtons.replace("{%submit%}", "").replace("{%cancel%}", "") + '</div>';
		
		
		editElem.not('[contenteditable]').attr("contenteditable", "true");
		editElem.after(quickEditButtonsDiv).before(quickEditControls).parent().find('.controlsQuickEdit').children('input, button').addClass("narrow").val("").parent().fadeIn(600);
		
		editElem.focus();
		document.getSelection().removeAllRanges();
				
		// HTML Source Code
		$('.controlsQuickEdit .showHtmlSource').click(function(){
			var sourceCode	= editElem.html();
			if(sourceCode.indexOf('<textarea ') == 0){
				sourceCode = editElem.children('textarea#sourceCode').val();
				editElem.html("").append(sourceCode);
			}else{
				editElem.html("").append('<textarea id="sourceCode" style="height:' + (elemHeight -2) + 'px;">' + sourceCode + '</textarea>');
			}
		});

		
		// Speichern via Ctrl+Enter
		editElem.bind("keydown", function(e) {
			if (e.ctrlKey && e.keyCode === 13) {
				editElem.closest('div.dataDetail').find('.confirmDataEdit.submit').click();
				return false;
			}
		});
		

		// Speichern
		$("div.dataDetail .confirmDataEdit.submit").click(function() {
		
			var elem		= $(this);
			
			var	term		= editElem.html(),
				modType		= elem.closest('.dataDetail').find('*[data-action="editcon"]').attr('data-type'),
				tagClass	= editElem.attr('class'),
				conArea		= "header",
				dataID		= elem.closest('.dataDetail').find('*[data-action="editcon"]').attr('data-url').split("id=")[1],
				url			= cc.httpRoot + "/system/access/feEdit.php";
			
			if(editElem.hasClass('dataTeaser')){
				conArea		= "teaser";
			}
			if(editElem.hasClass('dataText')){
				conArea		= "text";
			}
			
			if(term.indexOf('<textarea ') == 0){
				term = editElem.children('textarea#sourceCode').val();
				editElem.html("").append($("<div/>").html(term).text());
			}
			
			if(editElem.hasClass('dataHeader')){
				term	= term.split('<span class="icons')[0];
			}
			
			$.getWaitBar();
			
			/* Send the data using post and put the results in a div */
			$.post(url, { feEditDataText: term, modType: modType, conArea: conArea, dataID: dataID, lang: cc.feLang }, function(data) {					

				if(data == false) {
					jAlert(ln.feediterror, ln.alerttitle);
				}
				editElem.removeAttr("contenteditable").blur();
				innerEditDiv.unsetCurrent();
				$(".buttonsQuickEdit, .controlsQuickEdit").fadeOut(300, function(){
					$(this).remove();
					editButtonDiv.removeClass('hide');
					editButtonDiv.siblings('.conTypeDiv').removeClass('hide').removeAttr('style');
				});
				$.addDataQuickEditButtons();
				$.removeWaitBar(true);
			});
			
			innerEditDiv.unsetCurrent();

		});
		
	
		// Verwerfen
		$("div.dataDetail .confirmDataEdit.reset").click(function() {
		
			var elem	= $(this);

			editElem.html(resContent);
			editElem.removeAttr("contenteditable").blur();
			innerEditDiv.unsetCurrent();
			$(".buttonsQuickEdit, .controlsQuickEdit").fadeOut(300, function(){
				$(this).remove();
				editButtonDiv.removeClass('hide');
				editButtonDiv.siblings('.conTypeDiv').removeClass('hide').removeAttr('style');
			});
			$.addDataQuickEditButtons();

		});
	}); // Ende feEditing
	
	
		
	// FE-editing bei Doppelklick auf Textelemente
	$('body').on("dblclick", "div.innerEditDiv:not(.current) div.editableText", function(e) {
		
		e.preventDefault();
		e.stopPropagation();
		
		$(this).closest('div.innerEditDiv').children('span.quickEditPanel').children('.textEditBtn').click();
		
		return false;
		
	});


	// Falls ein neues Element (mit direct edit Option) eingefügt wurde, dieses zum editieren öffnen
	var pageID	= $.getUrlVar('pageid');
	var conID	= $.getUrlVar('conid');
	var area	= $.getUrlVar('area');
	var conDiv	= 'ee-area-' + area + '-conID-' + conID;
	$('div[id="' + conDiv + '"]').parent('div.innerEditDiv').setCurrent().children('div.editButtons').children('.directedit').click();

	
	
	// Falls Änderungen an einem HTML-Element gespeichert werden sollen
	$('body').on("click", '.feEditButton.cc-editelement-save', function(){
	
		var feForm		= $(this).closest('form');
		var allLangs 	= feForm.children('label.markBox').children('input.alllangs').is(':checked');
		var container	= feForm.closest('body').find('div#container:first');
	
		if(!feForm.length){
			return false;
		}
		
		feForm.attr('action', feForm.attr('action') + "&langs=" + allLangs + "&fe=1&fe-theme=true");
		
		
		$.submitViaAjax(feForm, true, "json", false, function(ajax){
		
			var editDetailsBox	= feForm.closest('.editDetailsBox');
			
			if(!ajax.success){

				var mediaList		= editDetailsBox.children('.cc-fe-medialist-default');
				var mediaListNew	= $(ajax.html);
				var mediaListCon	= mediaListNew.html();
				
				mediaList.html("").append(mediaListCon);
	
				// Sticky edit element buttons
				cc.getDirectEditFloatingButtons(mediaList);
				
				// add tinyMCE
				setTimeout(function(){
					$.mceAddEditors($("textarea.cc-editor-add", mediaList));
					$.setTrueFalseIcons(false);
				}, 200);					
				
				mediaList.children(".closeDetailsBox").bindCboxCloseEvent(true);

				$.removeWaitBar();

				return false;
			
			}
			
			
			// Custombox close event listener
			var cbClose		= function(){			
				
				ccReloadEditors = false;

				editDetailsBox.html("");
				
				// if element successfully edited, reload contents
				if(ajax.success){
					cc.updateElement(ajax, container);
				}
				
				document.removeEventListener('custombox.close', cbClose, false);
			
			};
			
			document.addEventListener('custombox.close', cbClose, false);

			// Custombox schließen
			$.closeCustomBox();		
			
			return false;
		});
	
		return false;
	});

	
	// Falls nur die Texteingaben bei einem Bildelement gespeichert werden sollen
	$('body').on("click", '.feEditButton.submit.image', function(e){
		
		e.preventDefault();

		$.getWaitBar();
		
		// Custombox schließen
		$.closeCustomBox();
		
		var mediaList		= $(this).closest('div.mediaList');
		var editDetailsBox	= mediaList.closest('div.editDetailsBox');
		var editDiv			= $('.editDiv[id="' + editDetailsBox.attr('data-eeid') + '"]');
		var innerEditDiv	= editDiv.children('.innerEditDiv');
		var editButtons		= innerEditDiv.children('.editButtons');
		var conDiv			= innerEditDiv.children('div.editContent');
		var editObj			= conDiv.find('.imageWrapper');
		var editImage 		= editObj.find('.imageElement');
		var altText			= mediaList.find('input.imgalt').val();
		var title			= mediaList.find('input.imgtitle').val();
		var caption			= mediaList.find('input.imgcap').val();
		var Link			= mediaList.find('input.imglink').val();
		var imgClass		= mediaList.find('select.imgclass option:selected').val();
		var imgClassOld		= editImage.attr('data-imgclass');
		var imgExtra		= mediaList.find('input.imgextra:checked').val();
		var allLangs 		= mediaList.find('label.markBox').children('input.alllangs').is(':checked');
		var newfile 		= mediaList.find('input.newfile').val();
		var imgWidth 		= mediaList.find('.scaleImgDiv').children('input.imgWidth').val();
		var imgHeight 		= mediaList.find('.scaleImgDiv').children('input.imgHeight').val();
		
		var feScript		= mediaList.find('.showListBox').children('input[name="script"]').val();
		feScript			= feScript + "&src=" + newfile + "&alt=" + altText + "&tit=" + title + "&caption=" + encodeURIComponent(caption) + "&link=" + Link + "&imgclass=" + imgClass + "&imgclassold=" + imgClassOld + "&imgextra=" + imgExtra + "&langs=" + allLangs + "&width=" + imgWidth + "&height=" + imgHeight + "&fe-theme=true";
		
		// resizable destroy
		editImage.addClass('resizable-disabled').resizableDestroy();

		
		$.ajax({
			url: feScript,
			dataType: "json",
			cache: false,
			success: function(ajax){

				if(ajax.result == "0"){
					jAlert(ln.feediterror, ln.alerttitle);
					return false;
				}
				
				editImage.fadeTo(300,0.25).removeClass(ajax.imgclassold);
				editImage.addClass(ajax.imgclass).fadeTo(600,1);
				editImage.attr('data-imgclass', ajax.imgclassshort);

				// Bildpfad ersetzen, falls neues Bild
				if(newfile != ""){
				
					// Bildpfad ermitteln
					var date		= new Date();
					var folder		= "/media/images/";
					if(newfile.split("/").length > 1){
						folder		= "/media/files/";
					}
					var forceSrc	= cc.httpRoot + folder + newfile + "?" + date.getTime();
					
					// Bildmaße ersetzen
					if(typeof(editImage.attr('style')) != "undefined"){
						editImage.attr('style', editImage.attr('style').replace(/(min-)?width:\s?[0-9]+px;/, "").replace(/(min-)?height:\s?[0-9]+px;/, ""));
					}
					
					// Bildpfad ersetzen
					editImage.attr('src', forceSrc);
					mediaList.children('.previewImgDiv').find('img').attr('src', forceSrc);
				}
				
				imgWidth	= imgWidth != "" && imgWidth != "auto" ? imgWidth + 'px' : 'auto';
				imgHeight	= imgHeight != "" && imgHeight != "auto" ? imgHeight + 'px' : 'auto';
				
				editImage.attr('title', title).attr('alt', altText);
				editImage.attr('style', 'width:' + imgWidth + '; height:' + imgHeight + ';');
				editImage.attr('width', imgWidth).attr('height', imgHeight);
				innerEditDiv.css('height', 'auto');
				
				if(mediaList.is(':visible')){
					//mediaList.animate({width: 'toggle', height: 'toggle'}, 400);
					mediaList.closest('div.contentArea').find('.fe-changes').show();
					//editButtons.hide().find('.cc-button').removeClass('hide');
					$.toggleSeparator(editButtons.children('span.feButtonSeparator'));
					//innerEditDiv.resizable("enable");
				}
				
				editImage.removeClass('resizable-disabled');
				
				$.adjustEditElement(editDiv, conDiv, editObj);

				$.removeWaitBar();

				return false;
			}
		});
		
		return false;
	});
	


	// FE-editing Änderungen übernehmen/verwerfen
	$('body').on("click", 'div.editDiv .fe-changes', function() {

		var feScript	= $(this).siblings('input[name="script"]').val();
		feScript		= feScript + $(this).attr('data-action') + '&fe=1';
		var confirmMes	= $(this).restoreTitleTag();
		
		if($(this).hasClass('apply')){
			confirmMes += "\r\n\r\n" + ln.confirmchanges;
		}
		confirmMes += "\r\n\r\n" + ln.isreversible;
		
		$(this).closest('.contentArea').addClass('currentContentArea');
		
		jConfirm(confirmMes, ln.confirmtitle,
		
			function(result){
				
				if(result === true) {
			
					$.getWaitBar();
				
					$.ajax({
						url: feScript,
						type: 'post',
						cache: false,
						success: function(ajax){
							if(ajax == "false"){
								jAlert(ln.feediterror, ln.alerttitle);
							}else{
								var tUrl	= window.location.href.split('?')[0];
								tUrl		= tUrl.split('#')[0];
								document.location.replace(tUrl);
							}
							return false;
						}
					});
				}
				
				$('.contentArea').removeClass('currentContentArea');
			}
		);
		return false;
	});

	
	
	// Falls Änderungen an einem HTML-Element gespeichert werden sollen
	$('body').on("click", '.feEditButton.submit.html', function(){
	
		var feForm		= $(this).closest('form');
		var allLangs 	= feForm.children('label.markBox').children('input.alllangs').is(':checked');
		
		// Custombox close event listener
		var cbClose		= function(){
		
			if(!feForm.length){
				return false;
			}
			
			feForm.attr('action', feForm.attr('action') + "&langs=" + allLangs + "&fe=1&fe-theme=true");
			
			$.submitViaAjax(feForm, false, "json");
			
			document.removeEventListener('custombox.close', cbClose, false);
		
		};
		
		document.addEventListener('custombox.close', cbClose, false);

		// Custombox schließen
		$.closeCustomBox();
		
		return false;
	});


	
	// Falls der feButtons-Div umbricht, die "Änderungen übernehmen"-Buttons verkleinern
	$('body').on("hover", 'div.innerEditDiv', function(){
		
		var buttonsDiv		= $(this).children('div.editButtons');
		var buttonsHeight	= buttonsDiv.height();
		
		if(buttonsHeight > 25){
			buttonsDiv.children('.fe-changes').addClass('narrow');
		}else{
			buttonsDiv.children('.fe-changes').removeClass('narrow');
		}
	});
	
	
	
	// Adjust Element upon change of gallery view
	$("body").on("mouseup", ".portfolioGallery .thumbnail, .portfolioGallery li", function(e) {
	
		var ele	= $(this);

		// if fe mode
		if(cc.feMode){
			setTimeout(function(){
				$.adjustEditElement(ele.closest(".editDiv"));
			}, 800);
		}
	});

}); // Ende ready function

// Ende headJS
});



/* adjustEditDivs */
(function($) {

	$.adjustEditDivs = function() {
	
		if(cc.skipEditDivsAdjust){
			cc.skipEditDivsAdjust	= false;
			return false;
		}
		
		// setTimeout to ensure elements are loaded (prevent size bug)
		setTimeout(function(){

		// editDiv-Positionierung anpassen bei gefloateten oder positionierten Inhalten
		var ele	= $("div.contentArea div.editDiv");
		var ec	= ele.length;
		
		cc.domIsLoaded	= false;
		
		ele.each(function(){
		
			var editDiv			= $(this);
			var innerEditDiv	= editDiv.children('div.innerEditDiv');
			var conDiv			= innerEditDiv.children('div.editContent');
			var conElem			= $.getConElem(conDiv);
		
			innerEditDiv.addClass('cc-element-init');
			
			// Textelem
			if(conDiv.children('div.editableText').length) {
				var imgElem		= conElem.find('img:last');
				if(typeof(imgElem) != "undefined" && imgElem.length) {
					conDiv.waitForImages(function(){
						if(parseInt(conElem.height()) < 23){
							conElem.css('height','auto');
						}
						$.adjustEditElement(editDiv, conDiv, conElem);
					});
				} else {
					$.adjustEditElement(editDiv, conDiv, conElem);
				}
			} else {
			// Bildelem
			if(conDiv.find('.imageWrapper').length) {
				var imgElem	= conElem.find('.imageElement');
				//imgElem.addClass('img-autowidth');
				conElem.waitForImages(function(){
					if(parseInt(imgElem.height()) < 23){
						conElem.css('height','auto');
					}
					setTimeout(function(){
						$.adjustEditElement(editDiv, conDiv, conElem);
						//imgElem.removeClass('img-autowidth');
					}, 1000);
				});
				// Preview image class entfernen
				editDiv.find('.mediaList').children('.previewImgDiv').children('img').addClass('previewImg');
			
			} else {
			// Bildergalerieelem
			if(conDiv.find('.cc-gallery').length) {
				var imgElem	= conElem.find('img:last').parent(':last').children('img');
				conDiv.waitForImages(function(){
					if(parseInt(conElem.height()) < 23){
						conElem.css('height','auto');
					}
					setTimeout(function(){
						$.adjustEditElement(editDiv, conDiv, conElem);
					}, 1000);
				});
			} else {
				// andere
				$.adjustEditElement(editDiv, conDiv, conElem);
			}}}
			
			if(!--ec ){
				cc.domIsLoaded			= true;
				$.resizableFeElements();
				cc.containerHeight		= $('div#container').height();
			}
		});
		
		},10); // end setTimeout
	
	},

	$.resizableFeElements = function() {
		
		// Resizable (disabled) intances (to prevent error upon later calls)
		$("div.innerEditDiv").each(function(i,e){
			$(e).resizable({handles: "e, se"});
			$(e).resizableDisable();
			
			// Falls resizable für images
			if($(e).attr('data-type') == "img"){
				var imgEle	= $(e).children('.editContent').find('.imageElement');
				imgEle.resizable({autoHide:true, handles: "e, se"});
				imgEle.resizableDisable();
			}
		});
	},
	
	// FE-Elementstyles anpassen
	$.adjustEditElement = function(editDiv, conDiv, conElem) {
	
		var innerEditDiv	= editDiv.children('div.innerEditDiv');
		
		if(typeof(conDiv) == "undefined"
		|| typeof(conElem) == "undefined"
		){
			conDiv			= innerEditDiv.children('div.editContent');
			conElem			= $.getConElem(conDiv);
			editDiv.css('height', 'auto');
			conElem.css('height', 'auto');
		}
		
		var isFloat 	= conElem.css('float');
		var isPosA  	= conElem.css('position');
		var hasClear  	= conElem.css('clear');
		var conMT		= conElem.css('margin-top');
		var conML		= conElem.css('margin-left');
		var conMR		= conElem.css('margin-right');
		var conMB		= conElem.css('margin-bottom');
			conMT		= conMT != "auto" ? parseInt(conElem.css('margin-top')) + 'px' : 'auto';
			conML		= conML != "auto" ? parseInt(conElem.css('margin-left')) + 'px' : 'auto';
			conMR		= conMR != "auto" ? parseInt(conElem.css('margin-right')) + 'px' : 'auto';
			conMB		= conMB != "auto" ? parseInt(conElem.css('margin-bottom')) + 'px' : 'auto';
		var conW		= "auto";
		var conH		= "auto";
		var frameW		= "";
		var frameH		= "";
		var display		= "block";
		var boxSizing	= conElem.css('box-sizing');
		var extraWidth	= 0;
		var isFixNav  	= false;
		var isFixed  	= false;

		
		// Clear setzen, bei (Gumby) Grid ggf. nicht erforderlich
		if(hasClear == "left" || hasClear == "right" || hasClear == "both"){
			editDiv.css('clear', hasClear);
		}
		
		/*
		if(boxSizing == "border-box"){
			extraWidth = parseInt(conElem.css('border-left')) + parseInt(conElem.css('border-right'));
		}
		*/
		
		if((conElem.hasClass("fixedNav")
		|| conElem.children().hasClass("fixedNav"))
		&& (!conElem.find('.navbar-affix').length
		|| conElem.find('.affix').length)
		){
			isPosA = "fixed";
			isFixNav	= true;
		}
		else{
			if(conElem.children(':first').length
			&& conElem.children(':first').css("position") == "fixed"
			){
				isPosA	= "fixed";
				isFixed	= true;
			}			
		}
		if(isFloat == "left" 
		|| isFloat == "right" 
		|| isPosA == "absolute" 
		|| isPosA == "fixed"
		){
		
			conW	= parseInt(conElem.getElementWidth()) + extraWidth;
			if(isFixNav){
				conH	= parseInt(conElem.children().closest(".fixedNav").find('[role="navigation"]').getElementHeight());
			}else{
				if(isFixed){
					conH	= parseInt(conElem.children(':first').getElementHeight());
				}else{
					conH	= parseInt(conElem.getElementHeight());
				}
			}
			frameW	= parseInt(conW +2) + 'px';
			frameH	= parseInt(conH +2) + 'px';
			conW	= conW + 'px';
			conH	= conH + 'px';
		
			conElem.css('min-width', 'inherit');	
		}
		
		// Position setzen
		if(isPosA == "relative" 
		|| isPosA == "absolute" 
		|| isPosA == "fixed"
		){

			var conPos		= "";
			var conPosX		= "";
			var conPosY		= "";
			var conPosX2	= "";
			var conPosY2	= "";
			
			if(isPosA == "relative"){
				conPosX		= conElem.css('left');
				conPosY		= conElem.css('top');
				editDiv.css({'top': conPosY, 'left': conPosX, 'position': isPosA});
			}else{
				if(isFixed){
					conPos		= conElem.children(':first').position();
					conPosX		= conPos.left;
					conPosY		= conPos.top;
					editDiv.css({'top': conPosY, 'left': conPosX, 'position': isPosA});
				}else{
					conPos		= conElem.position();
					conPosX		= conPos.left;
					conPosY		= conPos.top;
					editDiv.css({'top': conPosY, 'left': conPosX, 'position': isPosA});
				}
				if(isPosA == "fixed"){
					editDiv.css({'z-index': isFixNav ? 1000 : 1});
				}
			}			
			
			innerEditDiv.css({'width': conW, 'height': conH}).children('div.editDivFrame').css({'width': frameW, 'height': frameH});
			//conElem.css({'top':'0', 'left':'0'}).css({'width': conW, 'height': conH}).parent('div.editableText').css({'width': conW, 'height': conH});
			conElem.css({'top':'0', 'left':'0'}).css({'width': conW, 'height': conH});
		}
		
		// Float setzen
		if(isFloat == "left" 
		|| isFloat == "right"
		){
			editDiv.css('float', isFloat).addClass('floated');
			conElem.parent('div.editableText').css({'width': conW, 'height': conH}).css({'margin-right':conMR, 'margin-left':conML});
		}
		
		// Ränder setzen
		editDiv.css({'margin-right':conMR, 'margin-left':conML, 'margin-top':conMT, 'margin-bottom':conMB}).children('div.innerEditDiv').removeClass('cc-element-init');
		conElem.css({'margin-right':0, 'margin-left':0, 'margin-top':0, 'margin-bottom':0}).closest('div.innerEditDiv').find('div.editContent');

		innerEditDiv.css({'min-height': '25px'});
		
		// Frame
		editDiv.children('.editDivFrame').css({width: 'calc(100% + 2px)', height: 'calc(100% + 2px)'});
		
		return true;
		
	},
	
	// getConElem
	$.getConElem = function(conDiv) {
		
		// Textelem
		if(conDiv.children('div.editableText').length) {
			return conDiv.children('div.editableText').children('div:first');
		}
		
		// Bildelem
		if(conDiv.closest('.innerEditDiv').attr("data-type") == "img" && conDiv.find('.imageWrapper').length) {
			return conDiv.find('.imageWrapper');
		}
		// andere
		if(conDiv.children(':first').length) {
			return conDiv.children(':first');
		}
	
		return conDiv;
	
	};
})(jQuery);
	

// Sortierung von Inhaltselementen
(function($) {

	var indexArr = new Array();

	$.sortableContents = function() {
		
		var updatePage = true;
		
		$("div.contentArea").sortable({	placeholder: "ui-state-highlight",
										items:'div.editDiv',
										handle:'.editButtons .movecon',
										cancel: '',
										connectWith: ".contentArea",
										delay:100,
										tolerance:"pointer",
										zIndex:9999,
										revert:true,
										scrollSensitivity:100,
										scrollSpeed:30,
										opacity:0.95,
										containment: "body > #container",
										start: function(event, ui){
											$("div.contentArea").each(function(indx, elem){
												indexArr.push($(elem).css('z-index'));
												$(elem).css('z-index', 1);
											});
											$("div.contentArea .innerEditDiv").addClass('cc-element-highlight');

											ui.placeholder.closest("div.contentArea").css('z-index', 9100);
											ui.placeholder.html(ln.moveelement);
											$.toolTips(false);
										},
										sort: function(event, ui){
											ui.placeholder.css('width', ui.item.css('width'));
											ui.placeholder.css('height', ui.item.css('height'));
											if(ui.placeholder.prev().length && !ui.placeholder.prev().hasClass('editDiv')){
												updatePage = false;
												ui.placeholder.addClass('noSortTarget');
											}else{
												updatePage = true;
												ui.placeholder.removeClass('noSortTarget');
											}
										},
										beforeStop: function(event, ui){
											$("div.contentArea .innerEditDiv").removeClass('cc-element-highlight');
											if(!updatePage){
												$(this).sortable('cancel');
											}
											$("div.contentArea").each(function(indx, elem){
												$(elem).css('z-index', indexArr[indx]);
											});
											/*
											var targetUrl = ui.placeholder.next('div.editDiv').find('input[name="sorttarget"]').val();
											var sortTarget = ui.placeholder.next('div.editDiv').find('input[name="sorturl"]').val();
											*/
											var sortTarget = ui.placeholder.next('div.editDiv').find('input[name="sorttarget"]').val();
											if(typeof(sortTarget) == "undefined"){
											   sortTarget = ui.placeholder.prev('div.editDiv').prev('div.editDiv').find('input[name="sorttarget"]').val() + "&last=true";
											}
											var sortSource = ui.item.find('input[name="sortsource"]').val();
											ui.item.find('input[name="sortsource"]').val(sortSource + sortTarget);
											$.toolTips();
										},
										update: function(event, ui){
										
										   var sortObj		= ui;
										   
										   if(updatePage && sortObj.sender == null || sortObj.sender == "" || typeof(sortObj.sender) == null){ // Wichtig, da "update" bei Transsortierung zweimal getriggert
											   var targetUrl	= ui.item.find('input[name="sortsource"]').val() + '&fe=1';
											   $.doAjaxActionFE(targetUrl, false, true);
										   }
										}
		});
	};
})(jQuery);
	
		
// Textelement quick editing
(function($) {

	$.addQuickEditButtons = function() {
	
		var quickEditPanel	= $('<span class="quickEditPanel"></span>');
		var editBtnPanel	= "";
	
		// Element quickEditButtons
		$('div.innerEditDiv').each(function(i,elem){
		
			var innerEditDiv 		= $(elem);
				
			if(innerEditDiv.hasClass('empty')
			|| innerEditDiv.children('span.quickEditPanel').length
			){
				return true;
			}
			
			editBtnPanel	= quickEditPanel.clone();
			
			// Text
			if(innerEditDiv.filter('[data-type="text"]').length){
				
				var textWrapper	= innerEditDiv.find('div.editContent div.editableText div.textWrapper');
				
				textWrapper.addClass('quickEditText');
			
				// edit text (quick edit)
				editBtnPanel.append('<button class="quickEditBtn textEditBtn" title="' + ln.fequickedit +'"><span class="cc-admin-icons cc-icons cc-icon-quickedit quickEdit">&nbsp;</span></button>');
			
				// edit text (editor)
				editBtnPanel.append('<button class="quickEditBtn editorTextEditBtn" title="' + ln.feinlineedit + '"><span class="cc-admin-icons cc-icons cc-icon-edit quickEdit">&nbsp;</span></button>');
			}

			// Img element quick edit / size reset button
			if(innerEditDiv.filter('[data-type="img"]').length){
		
				// reset img
				editBtnPanel.append('<button class="quickEditBtn quickImgResetBtn" title="' + ln.feimgreset +'"><span class="cc-admin-icons cc-icons cc-icon-shrink quickEdit">&nbsp;</span></button>');
				
				// edit img
				editBtnPanel.append('<button class="quickEditBtn quickImgEditBtn" title="' + ln.feinlineedit + '"><span class="cc-admin-icons cc-icons cc-icon-image quickEdit">&nbsp;</span></button>');
			
			}
	
			// Gallery
			var gallTypeEle	= innerEditDiv.find('div.editContent .cc-gallery[data-gallname]');
			
			if(gallTypeEle.length){
				
				var gall		= gallTypeEle.attr('data-gallname');
				
				editBtnPanel.append('<div title="' + ln.fegalledit.replace("%s", gall) + '" data-type="gallery" class="mediaList gallery"><button data-type="gallery" data-url="' + cc.httpRoot + '/system/access/listMedia.php?page=admin&amp;action=edit&amp;type=gallery&amp;gal=' + gall + '" class="showListBox quickEditBtn quickOpenGalleryBtn"><span value="' + gall + '" class="quickEdit openList cc-admin-icons cc-icons cc-icon-gallery">&nbsp;</span></button></div>');
			}

			// Element
			var quickEditEleBtn		= $('<button class="quickEditBtn eleEditBtn" title="' + ln.feinlineedit +'"><span class="cc-admin-icons cc-icons cc-icon-equalizer quickEdit">&nbsp;</span></button>');
			var quickEditStyleBtn	= $('<button class="quickEditBtn eleEditStyleBtn" title="' + ln.feinlineedit + ' (Style)"><span class="cc-admin-icons cc-icons cc-icon-leaf quickEdit">&nbsp;</span></button>');
			
			// edit element
			if(!innerEditDiv.filter('[data-type="img"],[data-type="text"]').length){
				editBtnPanel.append(quickEditEleBtn);
			}
			editBtnPanel.append(quickEditStyleBtn);
			
			// edit styles
			innerEditDiv.append(editBtnPanel);
			
			quickEditEleBtn.click(function(e){
				e.preventDefault();
				var editBtn 	= innerEditDiv.children('.editButtons-panel').children('.directedit');
				if(editBtn.length){
					editBtn.click();
				}
				return false;
			});
			
			quickEditStyleBtn.click(function(e){
				e.preventDefault();
				var editBtn 	= innerEditDiv.children('.editButtons-panel').children('.directedit');
				if(editBtn.length){
					var deUrl	= editBtn.data('url');
					editBtn.attr('data-url', deUrl + '&tabactive=styles&fieldactive=ele');
					editBtn.click().attr('data-url', deUrl);
				return false;
				}
			});
				
		});
		
		
		// Section grid
		$('[data-cc-grid]').each(function(i,elem){
		
			var grid 			= $(elem);
			var gridType		= grid.data('cc-grid');
			var gridID			= grid.attr('id');
			var gridClass		= grid.attr('class');
			var editGridBtn		= $('<button class="quickEditBtn gridEditBtn" title="Layout - ' + ln.feinlineedit + (gridID && gridID != "" ? '<br /><b>' + gridID + '</b>' : '') + (gridClass && gridClass != "" ? '<br /><b>' + gridClass + '</b>' : '') + '"><span class="cc-admin-icons cc-icons cc-icon-grid-' + gridType + ' quickEdit">&nbsp;</span></button>');
			var rootGridEle		= grid.children(':first').parents('[data-cc-grid]').last();
			var editGridPanel	= rootGridEle.children('.gridEditPanel');
			
			if(rootGridEle[0] == grid[0]){
				if(editGridPanel.length){
					editGridPanel.remove();
				}
				editGridPanel	= $('<div class="gridEditPanel"></div>');
				rootGridEle.append(editGridPanel);
			}
			
			editGridPanel.append(editGridBtn);
			
			editGridBtn.click(function(e){
				e.preventDefault();
				var editBtn 	= $(elem).find('div.innerEditDiv:first').children('.editButtons-panel').children('.directedit');
				if(editBtn.length){
					var deUrl	= editBtn.data('url');
					editBtn.attr('data-url', deUrl + '&tabactive=styles&fieldactive=grid');
					editBtn.click().attr('data-url', deUrl);
				}
				return false;
			});
		
		});
	};

	// Dataelement quick editing
	$.addDataQuickEditButtons = function() {
	
		$('div.dataDetail .dataHeader:first, div.dataDetail .dataTeaser:first, div.dataDetail div.dataText:first').each(function(i,elem){
			
			var textElem = $(elem);
			
			// if not access
			if(!textElem.closest('div.dataDetail').hasClass('cc-edit-access')){
				return true;
			}
			
			textElem.addClass('quickEditDataText');
			
			if(!(textElem.children('.quickEditBtn').length)
			&& !textElem.hasClass('empty')
			){
				textElem.append('<button class="quickEditBtn quickEditDataBtn" title="' + ln.fequickedit +'"><span class="cc-admin-icons cc-icons cc-icon-quickedit quickEdit">&nbsp;</span></button>');
			}
		});
	};
	
	// Sticky edit element modal buttons
	$.fn.stickyElementBtn = function(offsetTop){

		var ele	= $(this);
		
		if(!offsetTop){ var offsetTop = 0; }

		var newTop = parseFloat(ele.closest('.custombox-modal-wrapper').scrollTop());
			newTop = newTop + offsetTop;

		if(newTop > 0){
			ele.css('top', newTop + "px");
		} else{
			ele.css('top', "0px");
		}
		return newTop;	
	};

	// Img element reset size
	$.resetImgSize = function(editDiv) {

		// neue Bildgröße speichern
		var mediaList	= editDiv.children('.editDetailsBox').children('.mediaList.images');
		var scaleDiv	= mediaList.find('.scaleImgDiv');
		var imgEle		= editDiv.find('.imageWrapper .imageElement');

		
		imgEle.resizableDestroy();
		
		scaleDiv.children('input.imgWidth').val("");
		scaleDiv.children('input.imgHeight').val("");
		
		mediaList.find('.feEditButton.submit.image').click()[0];
		
		editDiv.closest('div.contentArea').find('.fe-changes').show();
		
		return true;
	
	};
})(jQuery);




// Inhaltsbereiche neu stapeln (Vordergrund/Hintergrund)
(function($){

	// setCurrent editDiv
	$.fn.setCurrent = function() {	
		var ele = $(this);
		ele.addClass('current');
		ele.closest('.editDiv').addClass('current');
		return this;
	};

	// unsetCurrent editDiv
	$.fn.unsetCurrent = function() {	
		var ele = $(this);
		ele.closest('.editDiv').removeClass('current');
		ele.removeClass('current');
		ele.removeClass('cc-element-highlight');
		return this;
	};

	$.zIndexAreas = function(obj, index) {
		
		if(index){ // Falls in den Vordergrund
			
			indexArr = new Array();
			
			$("div.contentArea").disableSortable(); // Sortierung ausschalten
			
			$("div.contentArea").each(function(indx, elem){
				indexArr.push($(elem).css('z-index'));
				$(elem).css('z-index', 1);
			});
			
			obj.closest("div.contentArea").css('z-index', 9100);
			
		}else{ // z-Indices wieder herstellen
			$("div.contentArea").enableSortable(); // Sortierung einschalten
			
			obj.closest('div.innerEditDiv').unsetCurrent();
			obj.closest('div.editButtons').children('.cc-admin-icons, .switchIcons').removeClass('hide'); // Alle editButtons anzeigen
			
			$("div.contentArea").each(function(indx, elem){
				$(elem).css('z-index', indexArr[indx]);
			});
		}
	};
})(jQuery);


// Medienbox für direct editing ein-/ausblenden
(function($){

	$.toggleSeparator = function(obj) {						
		
		// Show
		if(obj.html() == "►"){
			obj.html('▼');
			
			$.zIndexAreas(obj, true);

		//hide	
		}else{
			obj.html('►');
			
			$.zIndexAreas(obj, false);
		}
		return false;
	};
})(jQuery);


// Medienbox für direct schließen
(function($){

	$.closeCustomBox = function() {						
		
		// Custombox schließen
		if($(".custombox-container").length){
			return Custombox.close();
		}
		return false;
	};
})(jQuery);
	

// Medienauswahl für Bilder
(function($){

	$.setImageData = function(mediaList) {
	
		if(typeof(mediaList) == "undefined"
		|| mediaList == ""
		){
			mediaList	= $('.editDiv').find('.editDetailsBox .mediaList.images');
		}
		
		// Alt-, Title- und Link-tags von Bildern auslesen
		mediaList.each(function(i,e){
			var conDiv		= $(e).closest('.innerEditDiv').children('.editContent');
			$(e).find('input.imgalt').val(function(){ return conDiv.find('.imageElement').attr('alt');});
			$(e).find('input.imgtitle').val(function(){ return conDiv.find('.imageElement').attr('title');});
			$(e).find('input.imgcap').val(function(){ return conDiv.find('.caption').html();});
			$(e).find('input.imglink').val(function(){ return conDiv.find('.imageElement').closest('a').attr('href');});
			$(e).find('select.imgclass').each(function(idx,ele){
				var imgclass	= conDiv.find('.imageElement').attr('data-imgclass');
				$(ele).children('option[value="' + imgclass + '"]').attr('selected','selected');
			});
			$(e).find('input.imgextra').each(function(idx,ele){
				var imgextra	= conDiv.find('.imageElement').attr('data-imgextra');
				$(ele).filter('[value="' + imgextra + '"]').prop('checked','checked');
			});
		});
	
	};
})(jQuery);


// Medien
// editMedia
(function($){

	$.editMedia = function(mediaListLink) {
	
		var fromFileBrowser	= mediaListLink.closest('div.myFileBrowser').length;
		
		// Falls die ListBox nicht über myFileBrowser geöffnet wurde
		if(fromFileBrowser
		|| mediaListLink.hasClass('openFilemanager')
		|| mediaListLink.closest('.mediaList.gallery').length
		){
			return false;
		}
		
		// Bild auswählen, falls auf ein Bild in Bilderliste geklickt
		$('body').off("click", 'div.listBox ul.editList img.preview, div.listBox ul.editList .mediaSelection');
		$('body').on("click", 'div.listBox ul.editList img.preview, div.listBox ul.editList .mediaSelection', function(e){
			
			e.preventDefault();
			e.stopPropagation();
	
			// Custombox schließen
			$.closeCustomBox();
			
			var fetchElem		= $(this);
			var listBoxID		= fetchElem.closest('div.mediaList').attr('data-id');
			
			var mediaList		= $('.mediaList[data-id="' + listBoxID  + '"]');
			var editDetailsBox	= mediaList.closest('div.editDetailsBox');
			var editDiv			= $('.editDiv[id="' + editDetailsBox.attr('data-eeid') + '"]');
			var innerED			= editDiv.children('div.innerEditDiv');
			var conSrc			= fetchElem.attr('data-file');
			var editButtons		= innerED.children('div.editButtons');
			var conDiv			= innerED.children('div.editContent');
			var editObj			= conDiv.find('.imageWrapper');
			var editImage		= editObj.find('.imageElement');
			var altText			= mediaList.find('input.imgalt').val();
			var title			= mediaList.find('input.imgtitle').val();
			var caption			= mediaList.find('input.imgcap').val();
			var Link			= mediaList.find('input.imglink').val();
			var imgClass		= mediaList.find('select.imgclass').children('option:selected').val();
			var imgClassOld		= editImage.attr('data-imgclass');
			var imgExtra		= mediaList.find('input.imgextra:checked').val();
			var allLangs 		= mediaList.find('input.alllangs').is(':checked');
			//var oldImgW			= editImage.getElementWidth();
			var oldImgW			= mediaList.find('input.imgWidth').val();

			
			if(altText == ""){
				altText	= conSrc.split(".")[0];
				mediaList.find('input.imgalt').val(altText);
			}
			
			var fileList;
			var fileTitles;
			var coverPics;
			var folder			= "media/images/";
			var nestedListBox	= false;
			

			// Falls files-Ordner, den Pfad ändern
			if(conSrc.split("/").length > 1){
				folder = "media/files/";
			}
			
			
			var feScript	= mediaListLink.children('input[name="script"]').val();
			feScript		= feScript + "&src=" + conSrc + "&alt=" + altText + "&tit=" + title + "&caption=" + encodeURIComponent(caption) + "&link=" + Link + "&imgclass=" + imgClass + "&imgclassold=" + imgClassOld + "&imgextra=" + imgExtra + "&langs=" + allLangs + "&width=" + oldImgW + "&fe-theme=true";
				
				
			// resizable destroy
			editImage.resizableDestroy();
			

			// Formular mit neuen Bilddaten abschicken
			$.ajax({
				url: feScript,
				type: 'post',
				dataType: "json",
				cache: false,
				success: function(ajax){
					if(ajax.result == "0"){
						jAlert(ln.feediterror, ln.alerttitle);
					}else{

						// Bildpfad ermitteln
						var date		= new Date();
						var forceSrc	= cc.httpRoot + "/" + folder + conSrc + "?" + date.getTime();
						
						
						// Bildpfad ersetzen
						editImage.stop().fadeOut(300, function(){								
							
							editImage.removeClass(ajax.imgclassold);
							editImage.addClass(ajax.imgclass);
							editImage.attr('data-imgclass', ajax.imgclassshort);
							
							editImage.removeAttr('width');
							editImage.removeAttr('height');
							
							// Bildmaße ersetzen
							if(typeof(editImage.attr('style')) != "undefined"){
								editImage.attr('style', editImage.attr('style').replace(/(min-)?height:\s?[0-9]+px;/, ""));
								editImage.closest('.imageWrapper, .innerEditDiv').css({height:'auto'});
							}
							if(typeof(editObj.attr('style')) != "undefined"){
								editObj.attr('style', editObj.attr('style').replace(/(min-)?height:\s?[0-9]+px;/, ""));
							}
							
							//editImage.attr('width', 'auto');
							//editImage.attr('height', 'auto');
							
							editImage.addClass('img-autowidth').attr('src', forceSrc).attr('title', title).attr('alt', altText).css({width: 'auto', height: 'auto', visibility: 'visible'}).waitForImages(function(){
								
								editImage.fadeIn(100, function(){

									editImage.parent('.ui-wrapper').css({width:'',height:''});
								
									setTimeout(function(){ editImage.closest('.imageWrapper, .innerEditDiv').css({height:'auto'}) }, 10);
									
									var newImgW		= editImage.outerWidth();
									var newImgH		= editImage.outerHeight();
									var useNewImgW	= newImgW;
									var useNewImgH	= newImgH;
									
									
									// Falls Bild größer als Vorgänger
									//if(newImgW > oldImgW){
										useNewImgW	= oldImgW;
										//useNewImgH	= newImgH * oldImgW / newImgW;
									//}
									useNewImgH	= 'auto';
									
									editImage.attr('width', useNewImgW);
									editImage.attr('height', useNewImgH);
									editImage.removeClass('img-autowidth');
									editImage.css({width: useNewImgW + 'px', height: useNewImgH + 'px'});
									
									editImage.parent('.ui-wrapper').css({width:'', height: ''});

									editImage.closest('.imageWrapper, .innerEditDiv').css({height: 'auto'});

									mediaList.find('input.imgWidth').val(useNewImgW);
									mediaList.find('input.imgHeight').val(useNewImgH);
									mediaList.find('input.newfile').val(conSrc);
									
									//editImage.parent('.ui-wrapper').css({width:useNewImgW + 'px',height:''});
									
									$.setImageData(mediaList);
									
									$.adjustEditElement(innerED, conDiv, editObj);
									
									// Falls Bild kleiner als Vorgänger
									if(newImgW < oldImgW){
									//	mediaList.find('.feEditButton.submit.image').click()[0];
									}
								});
							});
						});

						mediaList.children('div.previewImgDiv').find('img').attr('src', cc.httpRoot + "/" + folder+conSrc);
						
						// Bild einblenden
						$.toggleSeparator(editButtons.children('span.feButtonSeparator'));
						//mediaList.hide();
						//editButtons.removeClass('forceShow').hide();
						//editButtons.find('.cc-button').removeClass('hide');
						mediaListLink.closest('div.contentArea').find('.fe-changes').show();
						
						// ListBox close
						fetchElem.closest('div.listBox').removeListBox();
		
						innerED.unsetCurrent();
						
					}
				}
			});
		
			return false;
		});
	
	}; // Ende function $.editMedia
})(jQuery);

	
// FE-Elementstyles anpassen



// Resizable
(function($){


	$.fn.getResizableEditDiv = function() {
	
		_getParRowElement = function(ele) {

			if(rowClass != "" && ele.closest('.' + rowClass).length){
				if(ele.closest('.' + rowClass).find('.contentArea').length){
					return ele.closest('.contentArea');
				}else{
					return ele.closest('.' + rowClass);				
				}
			}else{
				return ele.parent('div.editDiv').parent('*');
			}
			
		};
		
		// ele = innerEditDiv
		var ele			= $(this);
		
		ele.not('.ui-resize-resizing').resizableDestroy();
		
		var btnEle		= ele.children('div.editButtons');
		var ecDiv		= ele.children('.editContent');
		var conElem		= $.getConElem(ecDiv);
		var edFrame		= ele.children('div.editDivFrame');
		var imgEle		= ele.find('.imageElement');
		var id			= ecDiv.attr('id');
		var maxCols		= ele.attr('data-maxcols');
		var rowClass	= ele.attr('data-row');
		var colCnt		= ele.attr('data-columns');
		var padL		= parseInt(conElem.css('padding-left'));
		var padR		= parseInt(conElem.css('padding-right'));
		var rowWidth	= Math.round(conElem.getElementWidth() -padL -padR);
		var eleWidth	= ele.getElementWidth();
		var eleHeight	= ele.getElementHeight();
		var stepSize	= rowWidth / colCnt;
		var colStr		= ele.attr('data-lang');
		var parRow		= _getParRowElement(ele);
		var maxRowWidth	= parRow.width();
		var maxHeight	= parseInt($.getWindowSize()[1]);
		var adjW		= 0;
		var colCntCur	= colCnt;
		var newWidth	= maxCols;
		
		//ele.not('.resize-disabled').resizable({
		ele.resizable({
			disabled: true,
			handles: "e, se",
			grid: stepSize,
			minWidth: stepSize,
			minHeight: eleHeight,
			maxWidth: maxRowWidth,
			maxHeight: maxHeight,
			create: function( event, ui ) {
				ele.resizable( "enable" );
				return true;
			},
			start: function( event, ui ) {
				imgEle.resizableDestroy();
				edFrame.css('background-position', padL + 'px top');
				edFrame.css('background-size',stepSize*2);
			},
			resize: function( event, ui ) {
				colCntCur	= Math.max(1, Math.min(maxCols, Math.round((ele.getElementWidth() -padL -padR) / stepSize)));
				newWidth	= colCntCur / maxCols * maxRowWidth;
				newStepSize	= (newWidth - padL - padR) / colCntCur;
				edFrame.css('background-size',newStepSize*2);
				ele.css('width', newWidth + 'px');
				edFrame.css('width', newWidth + 'px').css('height', ui.size.height + 'px');
				conElem.css('width', newWidth + 'px').css('height', ui.size.height + 'px');
				ecDiv.children('.titleTagBox').remove();
				ecDiv.append('<div class="titleTagBox permanent" style="position:absolute; left:0; top:' + (ui.originalSize.height +15) + 'px;">' + colStr + ':&nbsp;' + colCntCur + '</div>');
				
				ecDiv.find('.imageWrapper, .imageElement').removeAttr('height').css({'max-width': '100%', height: 'auto'});
			},
			stop: function( event, ui ) {
				// neue Column-Zahl speichern
				colCntCur	= Math.max(1, Math.min(maxCols, Math.round((ele.getElementWidth() -padL -padR) / stepSize)));
				
				$.safeColumnWidthChanges(ele, colCntCur, stepSize);
				
				ecDiv.children('.titleTagBox').delay(2000).fadeOut(600, function(){
					$(this).remove();
					edFrame.css('background-size','0%');
				});
				
				ecDiv.find('.imageWrapper, .imageElement').removeAttr('height').css({'max-width': '100%', height: 'auto'});
				
				ele.css({height: 'auto'});
				
			}
		});
	},
	
	// resizable image
	$.fn.getResizableImage = function() {
		
		// imgEle = image
		var imgEle		= $(this);
		
		imgEle.not('.ui-resize-resizing').resizableDestroy();
	
		var editDiv		= imgEle.closest('div.innerEditDiv');
		var conDiv		= editDiv.children('div.editContent');
		var conElem		= conDiv.find('.imageWrapper');
		var btnEle		= editDiv.children('div.editButtons');
		var elePadL		= parseInt(conElem.css('padding-left'));
		var elePadR		= parseInt(conElem.css('padding-right'));
		var maxWidth	= parseInt(editDiv.width()) - elePadL - elePadR;
		var containment	= "document";
		
		
		//imgEle.not('.resize-disabled').resizable({
		imgEle.resizable({
			disabled: true,
			autoHide: false,
			handles: "e, se",
			aspectRatio: true,
			containment: containment,
			minWidth: 10,
			minHeight: 10,
			maxWidth: maxWidth,
			start: function( event, ui ) {
				imgEle.removeAttr('width');
				imgEle.removeAttr('height');
				conElem.css({height: 'auto'});
				imgEle.closest('a').bind("click", function(e){
					e.stopImmediatePropagation();
					e.preventDefault();
					return false
				});
			},
			resize: function( event, ui ) {
				editDiv.css('height','auto').css('min-height', ui.size.height + 'px');
				editDiv.children('.editDivFrame').css('height', ui.size.height +2 + 'px');

				editDiv.children('.ui-resizable-handle').next('.titleTagBox').remove();
				editDiv.children('.ui-resizable-handle').after('<div class="titleTagBox permanent" style="position:absolute; left:' + ui.position.left + '; top:' + (Math.round(ui.size.height) +15) + 'px;">' + Math.round(ui.size.width) + '&nbsp;x&nbsp;' + Math.round(ui.size.height) + '&nbsp;px</div>');
			},
			stop: function( event, ui ) {
				// neue Bildgröße speichern
				var imgWidth 	= Math.round(ui.size.width);
				var imgHeight 	= Math.round(ui.size.height);
				var mediaList	= btnEle.siblings('.editDetailsBox').children('.mediaList.images');
				var scaleDiv	= mediaList.find('.scaleImgDiv');
				
				scaleDiv.children('input.imgWidth').val(imgWidth);
				scaleDiv.children('input.imgHeight').val(imgHeight);
				btnEle.hide();
				
				mediaList.find('.feEditButton.submit.image').click()[0];
				
				editDiv.closest('div.contentArea').find('.fe-changes').show();
				editDiv.children('.ui-resizable-handle').next('.titleTagBox').delay(2000).fadeOut(600, function(){ $(this).remove(); });
			}
		}).resizable("enable");
	};
})(jQuery);


// ui functions
(function($){
	
	// sortable enable
	$.fn.enableSortable = function() {
	
		var ele = $(this);
		
		if(typeof(ele.sortable) == "function"
		&& typeof(ele.data("ui-sortable")) != "undefined"
		){
			return ele.sortable("enable");
		}
		return false;
	},

	// sortable disable
	$.fn.disableSortable = function() {
	
		var ele = $(this);
		
		if(typeof(ele.sortable) == "function"
		&& typeof(ele.data("ui-sortable")) != "undefined"
		){
			return ele.sortable("disable");
		}
		return false;
	},

	// resizable disable
	$.fn.resizableDisable = function() {
	
		var ele = $(this);
		
		// Resizable: falls gerade resized wird, nicht ausgehen
		if(typeof(ele.resizable) == "function"
		&& typeof(ele.data("ui-resizable")) != "undefined"
		){
			return ele.resizable("disable");
		}
		return false;
	},

	// resizable destroy
	$.fn.resizableDestroy = function() {
	
		var ele = $(this);
		var res	= false;
		
		// Resizable: falls gerade resized wird, nicht ausgehen
		if(typeof(ele.resizable) == "function"
		&& typeof(ele.data("ui-resizable")) != "undefined"
		){
			res	= ele.resizable({diabled:true}).resizable("destroy");
			return res;			
		}
		return false;
	};
})(jQuery);


	
(function($){

	$.fn.bindCboxCloseEvent = function(clear){
	
		$(this).bind("click", function() {
	
			//$(this).unbind("click");
			
			// Custombox schließen
			$.closeCustomBox();
			
			if(clear){
				$(this).closest('.cc-fe-medialist-default').fadeOut(200, function(){ 				
					// remove tinyMCE
					$.mceRemoveEditors($("textarea.cc-editor-add", $(this)));
					
					$(this).remove();
				});
			}
		
		});
		
		return false;
	
	};
})(jQuery);


// Änderung der Spaltenbreite speichern
(function($){

	$.safeColumnWidthChanges = function(ele, colCnt, stepSize) {

		// ele = innerEditDiv
		var conDiv		= ele.children('div.editContent');
		var conElem		= $.getConElem(conDiv);
		var feScript	= ele.attr('data-script');
		
		feScript		+= colCnt + "&fe=1";
	
		$.ajax({
			url: feScript,
			contentType: 'application/json; charset=utf-8',
			dataType: 'json',
			cache: false,
			success: function(ajax){
				if(!ajax.result){
					jAlert(ln.feediterror, ln.alerttitle);
				}else{
					var edEl	= ele.find('.' + ajax.colsold);
					ele.attr('data-columns', ajax.colcnt);
					ele.removeAttr('style');
					edEl.removeClass(ajax.colsold).addClass(ajax.colsnew).css('height', 'auto');
					$.adjustEditElement(ele, conDiv, conElem);
					ele.closest('div.contentArea').find('.fe-changes').show();
				}
				return false;
			}
		});
		
		return false;
	};
})(jQuery);

	
// Neu/Kopieren/Sortierung via Ajax
(function($){

	$.doAjaxActionFE = function(targetUrl, prevElem, replaceDom, callback) {
	
		var targetElem	= "";
		$.getWaitBar();

		$.ajax({
			url: targetUrl,
			cache: false,
			success: function(ajax){

				// if replace dom
				if(replaceDom){
				
					var container	= $('div#container');
					var conSplit	= ajax.split('<div id="container"');
					
					if(!conSplit[1]){
						$.refreshPage();
						return false;			
					}
					
					var domSplit	= conSplit[1].split('<!-- Ende #container -->');
					
					if(!domSplit[0]){
						$.refreshPage();
						return false;			
					}
					
					var newDom		= $('<div id="ccNewDomFE"><div id="container"' + domSplit[0] + '</div>');
					
					targetElem		= domSplit[1].split('<target>')[1];
					if(typeof(targetElem) != "undefined" && targetElem != ""){
						targetElem	= targetElem.split('#')[1];
					}
		
					var newConDiv	= newDom.children('div#container');
					
					newConDiv.fadeTo(0,0);
					newConDiv.css('min-height', cc.containerHeight + 'px');
					
					container.fadeTo(200, 0.25, function(){
		
						container.replaceWith(newConDiv);
						
						newConDiv.fadeTo(0,0, function(){
						
							cc.skipEditDivsAdjust = false;
							$.executeInitFunctions(function(){							

								var directEditBtn	= $('*[id="' + targetElem + '"]').siblings('div.editButtons').children('.directedit');
								
								if(typeof(targetElem) != "undefined" && targetElem != ""){
									
									if(prevElem !== false){
										var elemID		= prevElem.closest('div.innerEditDiv').children('div.editContent').attr('id').split("-");
										var conID		= parseInt(parseInt(elemID.pop())+1);
										var conElem		= $('body').find('*[id="' + elemID.join("-") + '-' + conID + '"]').closest('div.editDiv').children('div.innerEditDiv');
										var editIcon	= conElem.setCurrent().children('div.editButtons').children('*[data-action="editcon"][data-actiontype="edit"]');
										var edBtn		= $('<button class="' + editIcon.attr('class') + '">' + ln.newelement + '<span class="cc-admin-icons cc-icons cc-icon-edit">&nbsp;</span></button>');
										var editNote	= $('<p class="newElementAdded adminArea col-md-12 col-12"></p>');
										
										conElem.setCurrent().children('div.editButtons').addClass('forceShow');
										editNote.append(edBtn);
										conElem.append(editNote);
										
										edBtn.bind("click", function(){
											directEditBtn.click();
										});
									}
								}
						
								// Show and scroll to new contents
								setTimeout(function(){
								
									$(window).scrollTop(cc.windowScrollTop);
									newConDiv.fadeTo(300,1, function(){
										$.removeWaitBar();
										return true;
									});
								}, 300);
							});					
						});
					});
				}
				return targetElem;
			}
		}).done(function(ajax){
			if(typeof callback == "function"){
				callback(ajax);
			}else{
				$.removeWaitBar(true);
			}
		});
		return targetElem;
	};
})(jQuery);



// replaceFEContent
(function($){

	// FE content via Ajax
	$.replaceFEContent = function(ajax, container) {
	
		container		= container && container.attr('id') == "container" ? container : $('body > div#container');
		
	//console.log(ajax);
		if(typeof(ajax) != "object"
		|| typeof(container) != "object"
		|| !ajax.content
		){
			$.refreshPage();
			return false;
		}
	
		var conSplit	= ajax.content.split('<div id="container"');
		
		if(!conSplit[1]){
			$.refreshPage();
			return false;			
		}
		
		var newDom		= $('<div id="ccNewDomFE"><div id="container"' + conSplit[1] + '</div>');
		
		$.removeListBox();

		if(typeof(newDom) == "undefinded"){
			$.refreshPage();
			return false;
		}
		
		var newConDiv	= newDom.children('div#container');
		
		// Remove script code
		if(ajax.scriptCode){
			container.siblings('script[data-scriptcon="init"]').remove();
		}
		
		newConDiv.fadeTo(0,0);
		newConDiv.css('min-height', cc.containerHeight + 'px');
		
		container.fadeTo(200, 0.25, function(){
		
			container.replaceWith(newConDiv);
		
			head.ready("ccInitScript", function(){
			
				// Load scriptFiles
				if(ajax.scriptFiles){
					var cnt = ajax.scriptFiles.length;
					$.each(ajax.scriptFiles, function(i, file) {
						var key	= i.toString();
						var url	= cc.getResourceUrl(file);
						
						if(!$('head').find('*[src="' + url + '"]').length){
							head.load({key: url}, function(){
								if(!--cnt){
								//	cc.skipEditDivsAdjust = true;
								//	$.executeInitFunctions();
								$(window).scrollTop(cc.windowScrollTop);
								}
							});
						}else{
							if(!--cnt){
								$(window).scrollTop(cc.windowScrollTop);
							}							
						}
                    });
				}
				// Load cssFiles
				if(ajax.cssFiles){
					$.each(ajax.cssFiles, function(i, file) {
						var url	= cc.getResourceUrl(file);
						if(!$('head').find('*[href="' + url + '"]').length){
							styleTagLast	= $('head').find('*[rel="stylesheet"]').last();
							styleTagAdd		= '<link href="' + url + '" type="text/css" rel="stylesheet" />';
							styleTagLast.after(styleTagAdd);
						}
                    });
				}
				// Append script code
				if(ajax.scriptCode){
					$('body').append(ajax.scriptCode);
				}
				
				head.ready(function(){
					$(document).ready(function(){
					
						newConDiv.fadeTo(0,0, function(){
						
							cc.skipEditDivsAdjust = false;
							$.executeInitFunctions(function(){
													
								// Show and scroll to new contents
								setTimeout(function(){
								
									$(window).scrollTop(cc.windowScrollTop);
									newConDiv.fadeTo(300,1, function(){
										$.removeWaitBar();
										return true;
									});
								}, 300);
							});
						});
					});
				});
			});
		});
	
		return true
	};
})(jQuery);



// Funktion zum auslesen von GET-Parametern
(function ($) {
	$.extend({
	  getUrlVars: function(){
		var vars = [], hash;
		var prehashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('#'); // Ankerziel entfernen
		var hashes = prehashes[0].split('&');
		for(var i = 0; i < hashes.length; i++)
		{
		  hash = hashes[i].split('=');
		  vars.push(hash[0]);
		  vars[hash[0]] = hash[1];
		}
		return vars;
	  },
	  getUrlVar: function(name){
		return $.getUrlVars()[name];
	  }
	});
}(jQuery));


// Zusätzliche Methoden/Plugins bestimmen
(function($) {

	$.getInitFunctions = function(){
	
		var dialog	= false;
		
		if(typeof(ccPoolFunctions) != "object"){
			ccPoolFunctions	= new Array();
		}
		
		ccPoolFunctions.push({name: "$.toolTips", params: ""});			// Tooltips
		ccPoolFunctions.push({name: "$.adjustEditDivs", params: ""});	// adjustEditDivs
		ccPoolFunctions.push({name: "$.addQuickEditButtons", params: ""});		// Text-Quickediting
		ccPoolFunctions.push({name: "$.addDataQuickEditButtons", params: ""});		// Data-Quickediting
		ccPoolFunctions.push({name: "$.sortableContents", params: ""});		// sortableContents
		ccPoolFunctions.push({name: "$.setImageData", params: ""});		// Medien
		ccPoolFunctions.push({name: "$.setTrueFalseIcons", params: false});	// True-False Icons

		
		// listMedia-Skript
		if($('.showListBox').length || typeof(tinyMCE) == "object"){
		
			head.load({ listmediacss: cc.httpRoot + "/system/themes/" + cc.adminTheme + "/css/listMedia.min.css" });
			head.load({ listmedia: cc.httpRoot + "/system/access/js/listMedia.min.js" },
				function(a){
			
					// Ajax popup für die Medienauswahl (ListBox)
					$('body').off("click", '.showListBox');
					$('body').on("click", '.showListBox', function(e){
					
						e.preventDefault();
						e.stopImmediatePropagation();

						var btnEle			= $(this);
						var mediaListType	= btnEle.prev('.showListBox[data-type]');
						
						// Falls openFilemanager
						if(btnEle.hasClass('openFilemanager')
						&& mediaListType.length
						){
							var url			= btnEle.attr('data-url');
							var splitStr	= "&root=";
							var urlBase		= url.split(splitStr)[0];
							var mediaType	= mediaListType.attr('data-type');
							btnEle.attr('data-url', urlBase + splitStr + mediaType);
						}

						$(this).listMedia();
						$.editMedia($(this));
						
						return false;
					});
				}
			);
		}
	
		// Dialog
		if($(".dialog[data-dialog]").length) {
			head.load({ dialogs: cc.httpRoot + "/system/access/js/dialogs.min.js" });
		}

		return ccPoolFunctions;
	
	};
})(jQuery);



// postPageLoadActions
(function($) {

	$.postPageLoadActions = function(){
		
		// Debug-Konsole
		if($("#debugDiv").length) {
			$("#debugContent").tabs();
		}

		$.resetChangesVars();

		return true;
	
	};

	$.resetChangesVars = function(){
		
		cc.openEditors			= 0;
		cc.conciseChanges		= false;

		return true;
	
	};

})(jQuery);


// Execute Init functions
head.ready("ccInitScript", function(){
	$(document).ready(function(){
		$.executeInitFunctions();
	});
});
