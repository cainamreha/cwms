// ***********************************************
// ******  Funktionen für den Adminbereich  ******
// ******          Concise WMS              ******
// ***********************************************


// Globale Vars
var conciseCMS			= conciseCMS || {regSess: false};
var cc					= conciseCMS;
var ln					= cc.ln || {};


// Define http root
cc.getHttpRoot	= function() {

	var root = document.location.protocol + '//' + document.location.host;
	if(document.location.host == "localhost"){
		root += '/' + document.location.pathname.split('/')[1];
	}
	return root;
};
cc.httpRoot = cc.httpRoot || cc.getHttpRoot();



// load init js
if($('body').hasClass('admin')){
	head.load({ ccInitScript: cc.httpRoot + "/system/access/js/concise.init.min.js" }, function(){
		conciseCMS.fe	= false;
		head.ready('jquery', function(){
			cc.setLoadingImages(conciseCMS.fe);
		});
	});
}


head.ready('ccInitScript', function(){

$(document).ready(function(){

	// dom loaded
	$('body').addClass('cc-domloaded');
	cc.domIsLoaded	= true;
	
	// Buttonbeschriftung für jConfirm
	$.alerts.okButton = 'Ok';
	$.alerts.cancelButton = ln.cancel;
	$.alerts.dialogClass = 'adminArea ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-dialog-buttons ui-draggable ui-resizable';
	
	
	// hash auslesen und ggf. Elemente toggeln
	var ccHash = window.location.hash;
	
	if($(ccHash).length) {
		setTimeout(function(){ $(ccHash).click() }, 500);
	}
	
	
	// Zu Anker scrollen
	$('body.admin').on("click touchstart", "a[href^='#']:not([data-toggle],.ui-tabs-anchor)", function() {
		var ancor = $(this).attr("href").split("#")[1];
		if(typeof(ancor) != "undefined"
		&& ancor != ""
		&& $("#" + ancor).length
		){
			var ancorEle	= $("#" + ancor);
			$('html,body').stop().animate({ scrollTop: parseInt(ancorEle.offset().top) - $("#topBox").height() }, 400);
			ancorEle.focus().blur();
			return false;
		}
	});
	
	// Nach oben Link anzeigen/ausblenden
	$(window).scroll(function () {
		if ($(this).scrollTop() > 100) {
			$('p.up').show();
		} else {
			$('p.up').hide();
		}
	});
	
	// Zum Seitenanfang scrollen
	$('body.admin').on("click", 'p.up', function () {
		$('body,html').animate({scrollTop: 0}, 400);
		return false;
	});
	
	
	// Änderungen verfolgen
	$('body').on("keyup, change",  '#adminContent input[type="text"]:not(.searchPhrase), \
									#adminContent textarea, \
									#adminContent input[type="checkbox"]:not(.markAll, *[data-select="all"], .addVal, .overwrite, .scaleimg, .useFilesFolder, [data-toggle]), \
									#adminContent select:not(.autoSubmit)',
									function () {
		if($('.submit').length && 
		!$(this).closest('.controlBar').length
		){
			cc.conciseChanges = true;
		}
	});
	
	
	// Linkaufruf bei vorliegenden Änderungen verhindern
	$('body').on("click", 'a[href]:not([href^="#"]):not(.cache, .pageStatus, .searchPhraseLink)', function(e){
		if(cc.conciseChanges == true){
			e.preventDefault();
			e.stopPropagation();
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
	
	
	var prevSelectVal = "";
	
	// Select mit Autosubmit
	$('body').on("focus", 'select.autoSubmit', function(e){
		prevSelectVal = $(this).val();
	}).on("change", 'select.autoSubmit', function(e){
		var elem	= $(this);
		if(cc.conciseChanges == true){
			e.preventDefault();
			elem.blur();
			jConfirm(ln.savefirst, ln.confirmtitle, function(result){
							
				if(result === true){										
					$.submitViaAjax(elem);
				}else{
					elem.val(prevSelectVal);
				}
			});
			return false;
		}else{
			$.submitViaAjax(elem);
		}
	});


	// eigener Code (CodeMirror)
	// Vollbildschirm
	$('body').on("click", '.toggleFullScreen', function(e){
		e.preventDefault();
		var myTA = $('.CodeMirror').find('textarea');
		if(typeof(myTA) != "object" || typeof(htmlCodeMirror) != "object"){
			return false;
		}
		myTA.closest('.codeMirrorEditor').fadeTo(300, .5, function(){
			var cmLi	= $(this);
			if(cmLi.hasClass('codeMirrorEditor-window')){
				$.getDimDiv();
				$(window).keyup(function(e){
					var code = e.keyCode || e.which;
					if(code == 122){
						if(cmLi.find('.CodeMirror-fullscreen').length){
							$.setTopPanel("front");
						}else{
						$.setTopPanel("rear");
						}
					}
				});
			}else{
				$.removeDimDiv();
				$.setTopPanel("rear");
			}
		}).toggleClass('codeMirrorEditor-window inlineWindow').fadeTo(100, 1);
		$(this).toggleClass('expanded');
		htmlCodeMirror.refresh();
		return false;
	});
	// Buttons
	// undo
	$('body').on("click", '.codeMirrorEditor-history-undo', function(){
		htmlCodeMirror.undo();
	});
	// redo
	$('body').on("click", '.codeMirrorEditor-history-redo', function(){
		htmlCodeMirror.redo();
	});
	// Zurücksetzen
	$('body').on("click", '.codeMirrorEditor-reset', function(e){
		jConfirm(ln.confirmreset, ln.confirmtitle, function(result){
						
			if(result === true){										

				htmlCodeMirror.setValue(htmlCodeMirrorContent);
			}
		});
		return false;
	});

	
	// Adminmenü toggle
	$('body.admin').on("click", '.cc-action-togglesidebar', function(){
	
		var btnToggle	= $(this);
		var target		= btnToggle.attr('data-target');
		var bar			= $('#' + target);
		var mcon		= $('#mainContent');
		var state		= {};
		
		if($.cookie("cc_sidebars")){
			state		= JSON.parse($.cookie("cc_sidebars"));
		}
		
		if(bar.hasClass("collapsed")){
			bar.removeClass("collapsed");
			bar.addClass("expanded");
			mcon.removeClass("expanded-" + target);
			if(state[target]){
				delete state[target];
			}
			if(head.mobile
			&& cc.isPhone()
			){
				$("#adminMenu a, .cc-button", bar).click(function(){
					bar.removeClass("expanded");
					btnToggle.unbind("click");
					btnToggle.click();
				});
				
				mcon.addClass("overlay");
				if(target == "right"){
					bar.css({'display':'block'});
					$('#previewNav').show();
				}
			}
		}else{
			bar.addClass("collapsed");
			bar.removeClass("expanded");
			mcon.addClass("expanded-" + target);
			state[target] = "collapsed";
			
			if(head.mobile
			&& cc.isPhone()
			){
				mcon.removeClass("overlay");
				if(target == "right"){
					bar.hide();
					$('#previewNav').hide();
				}
			}
			if(target == "right"){
				setTimeout(function(){
					$.scaleAdminPanels(true);
				}, 0);
			}
		}
		$.cookie("cc_sidebars", JSON.stringify(state), { expires: 7, path: '/' });
	
	});

	
	// Vorschaumenüs einblenden
	$('body.admin').on("bind, mouseenter", 'div#menuBox', function(e){
			
		$('div.previewMenu').stop(true,true);
			
		var prevMenu	= $(this).children('div.previewMenu:hidden').has("li");
		
		prevMenu.delay(200).fadeIn(300);
				
	}).on("bind, mouseleave", 'div#menuBox', function(e){
		var pos		= $(this).offset().left;
		var mpos	= e.pageX;
		// Falls nicht mehr über rechter Spalte, zusätzliche Menüs ausblenden
		if(mpos < pos){
			$('div#menuBox').children('div.previewMenu:not(#mainMenu)').delay(800).fadeOut(500);
		}
	});

	

	// Funktion zum Zurücksetzen von Formularen
	$('body').on("click", "#reset", function fieldRes() {
	
		$("select").children('option:first-child').attr("selected","selected"); 
		$('input').not('input[type="button"]').not('input[type="submit"]').not('input[type="reset"]').val("");
		$('textarea').val("");
	});
	

						   
	var count = 0;
	
	// Erneuern des Captchas (ajax reload)
	$('body').on("click", '.caprel', function(){
								
			count++;
			
			var targetUrl = $('.caprel').attr('href');
			$('img.captcha').attr('src', targetUrl + '?rel=' + count);
			return false;
	});
	
	
	// Smileys ersetzen
	$('body').on("click", 'img.smiley', function setsmile(){
			
		var shortcut = $(this).attr('title');
		if(shortcut == ""){
			shortcut = $(this).restoreTitleTag();
		}
		var message = $('textarea[name="message"]').val();
		$('textarea[name="message"]').val(message + shortcut);
		
		return false;
	});



	// E-Mail-Validierung on the fly
	$("body").on("blur", ".email", function(){
							  
		var email = $(this).val();
		 
		if(email != '')
		{
			if(isValidEmailAddress(email))
			{
				$(this).removeClass('invalid');
			} else {
				$(this).addClass('invalid');
			}
		} else {
			$(this).removeClass('invalid');
		}
	});
	

	// Bildbreite-Validierung on the fly
	$("body").on("blur", ".pixelSize", function(){
							  
		var pSize = $(this).val();
		 
		if(pSize == '' || !(pSize.match('^(0|[1-9][0-9]*)$')) || pSize.length > 4 )
		{
			$(this).css({ "border-color": "#f93" });
			$(this).addClass('invalid');
		} else {
			$(this).css({ "border-color": "#D6D8DD" });
			$(this).removeClass('invalid');
		}
	});
	

	// E-Mail-Validierung on the fly
	$('body').on("click", ".change", function(){
							  
		if($(".email, .pixelSize, .ownUserGroups").hasClass("invalid"))
		{	
			var cancelSubmit = true;
			jAlert(ln.checkinvalidfield, ln.alerttitle);
			return false;
		}

	});
	
	
	
	var noWait = false;
	


	// erweiterte Info bei Hilfe, Fehlermeldung/Hinweis
	$('body.admin').on("click", 'p.notice, p.error', function(){ 
		$(this).children('div.description').toggle('fast');
	});
	
	
	// erweiterte Info bei Hilfe, Fehlermeldung/Hinweis
	$('body.admin').on("click", 'div.headerBox .adminHeader, div.headerBox div.description', function(){ 
		$(this).closest('.headerBox').children('div.description').toggle('fast');
	});
	
	
	// Abschicken von Formularen via Ajax
	$('body.admin').on("click", '.ajaxSubmit, *[data-ajaxform]', function(e){
		
		e.preventDefault();
		elem = $(this);
		
		// Falls Änderungen vorliegen, Bestätigung abwarten
		if( cc.conciseChanges && 
			typeof(elem.attr('data-check')) != "undefined" && 
			elem.attr('data-check') == "changes"
		){
			jConfirm(ln.savefirst, ln.confirmtitle, function(result){
							
				if(result === true){										
					$.submitViaAjax(elem);
				}else{
					elem.val(prevSelectVal);
				}
			});
			return false;
		}
		var form = elem.closest('form');
		$.submitViaAjax(form); // Formular abschicken
	
	});


	// Abschicken des Formulars zum Anlegen einer neuen Seite
	// neue erste Seite
	$('body').on("click", '*[data-action="newpage"]', function(){ // Den neuen Buttons die pageId des Menüpunkts mitgeben
	
		var targetId	= $(this).attr('data-value'); // targetMenu Id auslesen
		var targetKind	= $(this).attr('data-type'); // targetMenu Typ auslesen
	
		$('input#new_item').val(targetId); // menu_item mitgeben
		$('input#new_item').attr('name',targetKind); // Art des neuen Eintrags mitgeben

		$.submitViaAjax($('form#addNewItem')); // Formular abschicken
	
	});


	// Abschicken des Formulars zur Auswahl von Newskategorien/Kommentaren, Anlegen von Formfeldern etc.
	$('body').on("change", 'select[data-action="autosubmit"], input[data-action="filterlist"]', function(){
		
		var form		= $(this).closest('form');
		$.submitViaAjax(form); // Formular abschicken
	});

	
	// Warnung bei Foreig Key Änderungen
	$('body').on("change", 'select.formForeignKey', function(){
		
		jAlert(ln.foreignkeywarn, ln.alerttitle);
	});

	
	/* Accordeon Liste */
	/* Bei klick auf Toggle-Symbol Unterliste ausfahren/einklappen */
	$('body').on("click", '.pageList li .toggleSubList', function() {
		var listItem	= $(this).closest('li');
		if(listItem.children('ul').is(':visible')) {
			listItem.removeAttr('data-active').removeAttr('style').find('> ul').slideUp('fast');
			$(this).removeClass('expanded');
		} else {
			listItem.siblings('li:not(.sortFirstEntry)').removeAttr('data-active').removeAttr('style').find('ul:visible').slideUp('fast');
			listItem.attr('data-active','active').find('> ul').slideDown('fast').children('li').show();
			$(this).addClass('expanded');
		}
		return false;
	});
	

	
	// Buttonpanel unten fixieren
	$(window).on("scroll", function(i, e){
		var submElem	= $('body.admin li.submit.change:visible:not(.buttonpanel-nofix, .back)').first();
		if(submElem.length == 1){
			$.fixButtonPanel(submElem);
		}
		return true;
	});

	
	// Togglen von Kindelementen
	$('body').on("click", '.toggleChild', function(){
		$(this).toggleClass('active');
		$(this).children().slideToggle(200).toggleClass('active');
	});
	
	// Togglen von Folgeelementen
	$('body').on("click", '.toggleNext', function(){
		$(this).toggleClass('active');
		$(this).next().slideToggle(200).toggleClass('active');
	});
		
	
	// Reiterinhalte Toggeln
	$('body').on("click", 'h2.toggle, h3.toggle, h4.toggle:not([data-toggle="expand"])', function(e) {
		e.preventDefault();
		$(this).next().slideToggle('fast', function(){
			$.fixButtonPanel($(this), true);
			return false;
		});
		return false;
	});
	
	
	// Reiter ausschlussweise Toggeln (andere Reiter ausblenden)
	$('body').on("click", 'h2.switchToggle, h3.switchToggle', function() {
		var elem = $(this);
		$($(this).prop('tagName') + '.switchToggle').not($(this)).next('*:visible').slideToggle('fast');
		elem.next('ul,div').slideToggle('fast', function(e){
			if(elem.hasClass('codeMirrorToggle') && 
			typeof(htmlCodeMirror) == "object" && 
			typeof(htmlCodeMirror.refresh) == "function"
			){
				htmlCodeMirror.refresh();
			}
			$.fixButtonPanel(elem);
		});
		return false;
	});
	
	
	// Formularfelder-Ansicht Toggeln
	// Alle Formularfelder
	$('body').on("click", 'h4.formFields', function() {
		var listHeight = $('li.formField').hasClass('collapse');
		if(listHeight) {
			$('li.formField').removeClass('collapse');
		} else {
			$('li.formField').addClass('collapse');
		}
		$.fixButtonPanel($(this));
		return false;
	});
	
	// Einzelnes Formularfeld
	$('body').on("click", '.formFieldHeader', function() {
		var fieldLi = $(this).closest('li.formField');
		var listHeight = fieldLi.hasClass('collapse');
		if(listHeight) {
			fieldLi.removeClass('collapse');
			fieldLi.find('div.formFieldDetails').show();
		} else {
			fieldLi.find('div.formFieldDetails').toggle();
		}
	});
	
	// Formulardaten-Tabelle Ansicht umschalten
	$('body').on("click", '*[data-toggle="expand"]', function() {
		var dataDiv = $('*[id="' + $(this).attr('data-target') + '"]');
		var dataTab = dataDiv.find('table');

		var dataDivOv = dataDiv.css('overflow');
		var dataDivWidth = (dataDiv.width());
		var dataTabWidth = (dataTab.width());
		if(dataDivOv == 'auto') {
			dataDiv.css('overflow','inherit');
			dataTab.css('position','relative');
			dataTab.css('z-index','6000');
			dataDiv.css('margin','10px 0');
			dataDiv.css('margin-left',(Math.round(dataDivWidth-dataTabWidth) / 2) + 'px');
			dataTab.css('box-shadow','0 0 5px #333366');
		} else {
			dataDiv.css('overflow','auto');
			dataDiv.css('margin','10px 0');
			dataDiv.css('margin-left','0px');
			dataTab.css('box-shadow','none');
		}
		return false;
	});
	
	// Seiten veröffentlichen
	$('body').on("click", 'a.pageStatus', function(){
	
		var element			= $(this);
		var iconTag			= element.children('.cc-admin-icons');
		var targetUrl		= element.attr('href');
		var oldTitle		= element.restoreTitleTag();
		var newTitle		= element.attr('data-alttitle');
		var oldStatus		= element.attr('data-status');
		var newStatus		= oldStatus == "1" ? 0 : 1;
		var newStatusUrl	= targetUrl.split("&online=")[0];
		var oldStatusClass	= oldStatus == "1" ? "cc-icon-online" : "cc-icon-offline";
		var newStatusClass	= oldStatus == "0" ? "cc-icon-online" : "cc-icon-offline";
		
		iconTag.loading();
		
		$.ajax({
			url: targetUrl
		}).done(function(){
			element.attr('href',newStatusUrl + "&online=" + oldStatus);
			iconTag.addClass(newStatusClass).removeClass(oldStatusClass);
			element.attr('data-status', newStatus);
			element.attr('title', newTitle);
			element.attr('data-alttitle', oldTitle);
			iconTag.loadingRemove();
		});
		return false;
	});
	

	// Vorgeschlagene Keywords übernehmen
	$('body.admin').on("click", '.fetchKeywords', function() {
			var keywords = $(this).attr('data-keywords');
			$('input#keyw').val(keywords);
			return false;
	});

	
	// Zurücksetzen von canonical url
	$('body.admin').on("click", ".resetHiddenField", function (e) {
	
		e.preventDefault();
		
		var target	= $(this).attr('data-target');
		$('*[data-reset="' + target + '"]').val("");
		$(this).fadeOut(600);
		$(this).siblings('.hide-on-empty').fadeOut(600);
		
		return false;
	});

	
	// Bei klick auf Überschrift Unterliste (Inhaltselemente) toggeln
	$('body').on("click", 'h3.toggleCons', function() {
		if($(this).children('div.addCon:visible').length) {
			return false;
		}
		if($(this).next('ul').children('.contentElement:first').children('div.elements').css('display') == 'none'){
			$(this).next('ul').children('.contentElement').css('height','auto').css('margin-bottom','25px').children('div.elements:hidden').slideToggle(250, function(){ $.fixButtonPanel($(this)); });
		} else {
			$(this).next('ul').children('.contentElement').css('height','auto').css('margin-bottom','10px').children('div.elements:visible').slideToggle(1, function(){ $.fixButtonPanel($(this)); });
		}
		return false;
	});
			


	// Inhaltsliste (Inhalte) umschalten zwischen kurzer und ausführlicher Ansicht
	/* Bei klick auf Überschrift Elemente komplett ausfahren/anzeigen */
	$('body.admin').on("click", 'ul.elements li.contentElement div.conNr', function() {
		var parLi	= $(this).parent('.contentElement');
		if(parLi.children('div.elements').css('display') == 'none'){
			// ausklappen
			parLi.css('height','auto').css('margin-bottom','25px').children('div.elements:hidden').slideToggle(250, function(){
				$.fixButtonPanel($(this));
				parLi.addClass('active');
			});
		}else{
			// einklappen
			parLi.css('height','auto').css('margin-bottom','10px').children('div.elements:visible').slideToggle(1, function(){ $.fixButtonPanel($(this)); });
			parLi.removeClass('active');
			if($.fn.sortable
			&& !parLi.siblings('.contentElement.active').length
			){
				$('#sortableContents').sortable('enable');
			}
		}
		return false;
	});



	// Togglen z.B. erweiterte Angaben (z.B. Benutzerdetails) ein-/ausblenden
	$('body').on("click", '*[data-toggle]:not([data-toggle="dropdown"],[data-context])', function () {
		var tt	= $(this).attr('data-toggle');
		if(tt !== undefined){
			if($('*[id="' + tt + '"]').length){
				$('*[id="' + tt + '"]').slideToggle(200, function(){ $.fixButtonPanel($(this)); });
			}else{
			if($('.' + tt).length){
				$('.' + tt).slideToggle(200, function(){ $.fixButtonPanel($(this)); });
			}}
		}
	});


	
	// Benutzerbild löschen
	$('body.admin').on("click", '.deleteUserImage', function(e) {
	
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var elem		= $(this);
		var targetUrl	= elem.attr('data-url');
		
		jConfirm(ln.confirmdelfile, ln.confirmtitle, function(result){
						
			if(result === true){										

				$.ajax({
					url: targetUrl
				}).done(function(ajax){
						if(ajax == 0){
							jAlert(ln.cacheerror, ln.alerttitle);
						}else{
							var src = cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/empty_avatar.png';
							elem.closest('div.previewBox').children('img.userImage').fadeOut(300, function(){ $(this).attr('src', src).attr('data-img-src', src).fadeIn(600); });
							$('.pimg').remove();
							elem.parent('span').remove();
						}
				});
				return false;
			}
		});
		return false;
	});



	// Eigene Benutzergruppen ein-/ausblenden falls subscriber (hier nicht erlaubt, da kein Login)
	$('body').on("change", 'select#selGroup', function(){
		
		if($(this).val() == "subscriber"){
			$('select#selOwnGroups').parent('div').slideUp('fast');
		}else{
			$('select#selOwnGroups').parent('div').slideDown('fast');
		}
	});


	// Im db-Backupbereich löschen bzw. restore bestätigen
	// Falls gelöscht werden soll
	$('body.admin').on("click", 'button[name="del_bkp"]', function () {
		var elem = $(this);
		jConfirm(ln.delbackup, ln.confirmtitle, function(result){
												   if(result === true){
														$.submitViaAjax(elem.closest("form"));
												   }
										  });
		return false;
	});
		
	// Falls ein Backup restored werden soll
	$('body.admin').on("click", 'button[name="restore_bkp"]', function () {
		var elem = $(this);
		jConfirm(ln.restorebackup, ln.confirmtitle, function(result){
													if(result === true){
														$.getWaitBar();
														elem.closest("form")[0].submit();
													}
											  });
		return false;
	});


		
	// Dateiupload für multiple files
	$('body.admin').on("change", '#upload', function () {

		var input = document.getElementById("upload");
		var ul = $("#uploadFilesList");
			ul.children().remove();
		var filesArray = new Array();
		var allowedExt = $(this).attr('accept').split("|");
		var notAllowed = "";
		
		if($('ul#errorMes').length) { // Falls Fehlerliste vorhanden, diese entfernen
			$('ul#errorMes').remove();
		}
			
		if($('p.notice').length) { // Falls Meldung vorhanden, diese entfernen
			$('p.notice').remove();
		}
			
		if($('p.error').length) { // Falls Fehlermeldung vorhanden, diese entfernen
			$('p.error').remove();
		}			
		
		var fileArrayLoop = 20;
		
		// Falls zu viele Dateien ausgewählt wurden, das Array auf 20 reduzieren			
		if(input.files.length <= fileArrayLoop){
			fileArrayLoop = parseInt(input.files.length);
		} else {
			jAlert(ln.uploadmaxnr1 + fileArrayLoop + ln.uploadmaxnr2 + fileArrayLoop + ln.uploadmaxnr3, ln.alerttitle);
		}
		
		for (var i = 0; i < fileArrayLoop; i++) {
			var fileName = input.files[i].name;
			var fileSize = parseFloat(input.files[i].size / 1024).toFixed(2);

			filesArray.push(fileName);
			
			var ext = fileName.toLowerCase().split(".");
			
			if($.inArray(ext[ext.length-1], allowedExt) == -1) { // Falls die Dateinamenerweiterung nicht erlaubt ist
				filesArray.splice(i, 1, "#");
				notAllowed += fileName + "\r\n";
			} else {
				ul.append('<li class="file listItem"><strong>' + fileName + "</strong> (" + fileSize + " kb)" + '<span class="editButtons-panel"><span class="cc-admin-icons cc-icons cc-icon-delete">&nbsp;</span></span></li>');
			}
				
			$('li.listItem.file .cc-admin-icons').click(
				function removeFile () {
					
					var listItem	= $(this).closest('.file');
					var delName		= listItem.children('strong').html();
					
					if(listItem.remove()) {
					
						if(ul.children().length < 1) {
							$("#uploadFilesList").append('<li>' + ln.nofilessel + '</li>');
						}

						// var arrayFile = input.files;
						for (var j = 0; j < input.files.length; j++) {
							
							if(input.files[j].name == delName)
							{
								 //Value not found in the array.  Add to the end of the array with push();
								// jAlert(input.files[j].name, ln.alerttitle);

								filesArray.splice(j, 1, "#");
								$("#selFiles").val(filesArray);
								 
							}
						}
				
					}
				
			});
		
			$("#selFiles").val(filesArray);
		}
		
		if(ul.children().length < 1) { // Falls keine Dateien angezeigt werden, Meldung ausgeben
			$("#uploadFilesList").append('<li>'+ln.nofilessel+'</li>');
		}
			
		if(notAllowed != "") {
			jAlert(ln.notallowedfiles + notAllowed, ln.alerttitle);
		}
	});


	// Bei nicht angegebenem Galerienamen Fileauswahl verhindern
	$('body.admin').on("click", '#uploadGallFiles', function() {
						  
		if($('#gallName').val() == "") {
			jAlert(ln.gallnamefirst, ln.alerttitle);
			return false;
		} else {
		if($('#upload').val() == "") {
			jAlert(ln.choosegallfiles, ln.alerttitle);
			return false;
		}
		}
	});


	// Überprüfung Galeriename
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
	
	// Überprüfung Galeriename nach Eingabe
	$("body.admin").on("blur", '#gallName', function() {
		
		var gallName = $(this).val();
		checkGallName(gallName);
		return false;
	});
	

	
	// Wartesysmbol bei Fileupload einblenden
	$('body.admin').on("click", '#fileupload, #uploadGallFiles', function(e) {
		
		e.preventDefault();
		
		// Falls files-Ordner gecheckt ist, aber kein Ordner angegeben, abbrechen
		if($('input.useFilesFolder').is(':checked') && $('input.filesFolder').val() == ""){
			jAlert(ln.choosefolder, ln.alerttitle);
			return false;
		}
		
		if($('li.file').length) {
			
			if(!($('#gallName').length) || $('#gallName').val() != "") {
				var list = $('ul#uploadFilesList');
				list.prepend('<p class="hint">' + ln.uploadwait + '<img class="loading" src="' + ccLoadingImg.src + '" /></p>');
				list.children('li').children('strong').prepend('<img class="loading" src="' + ccLoadingImg.src + '" />');
				list.find('img[alt="delete"]').css('display','none');
				
				$('ul#uploadFilesList li:last-child img.loading').waitForImages(function(){
					$('#uploadfm').submit();
				});
			}
		}
		return false;
		
	});
	

	
	// Button mit loading icon (e.g. bei Update)
	$('body').on("click", ".button-wait", function(e) {
		var iconWait = $('<span class="cc-admin-icons cc-icons cc-icon-loading">&nbsp;</span>');
		$(this).append(iconWait);
	});

		
	// Live-Modus der Website ändern
	$('body.admin').on("click", ".siteStatusBox", function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).children('a.goLive:visible').click();
		return false;
	});

	
	// Live-Modus der Website ändern
	$('body.admin').on("click", 'a.goLive', function(e) {

		e.preventDefault();
		e.stopPropagation();
		
		var element = $(this);
		var confmes	= ln.confirmgostage;
		
		if(element.hasClass('live-off')){
			confmes	= ln.confirmgolive;
		}
		
		jConfirm(confmes, ln.confirmtitle, function(result){
		
			if(result === true){
			
				var targetUrl = element.attr('href');
				
				$.ajax({
					url: targetUrl + "&ajax=1"
				}).done(function(ajax){
					element.addClass('hide').hide();
					element.siblings('.goLive').removeClass('hide').show();
					if($('#goLiveLink-dashboard').length){	$('#goLiveLink-dashboard').fadeOut(250, function(){ $(this).remove(); }); }
					jAlert(ajax, ln.alerttitle);
					return ajax;
				});
				return false;
			}
		});
	});
	

	
	// HTML-Cache neu anlegen
	$('body').on("click", '*[data-action="refreshcache"], a.cache:not(.nocache)', function(e) {
	
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var elem			= $(this);
		var iconTag			= elem.parent('.cc-admin-icons').length ? elem.parent('.cc-admin-icons') : elem.find('.cc-admin-icons');
		var classCache		= "cc-admin-icons cc-icons cc-icon-cache";
		var classCacheOk	= "cc-admin-icons cc-icons cc-icon-cache-ok";
		var classCacheNo	= "cc-admin-icons cc-icons cc-icon-cache-no";
		var targetUrl		= elem.attr('href');
		var result;
		
		iconTag.loading();
		
		$.ajax({
			url: targetUrl,
			contentType: 'application/json; charset=utf-8',
			dataType: "json",
			//async: false, // set false in case of inconvienient callback with json
			success: function(ajax){
				if(ajax == 0 || ajax == '0'){
					iconTag.attr('class', classCache);
					jAlert(ln.cacheerror, ln.alerttitle);
				}else{
					var resArr = ajax;
					if(resArr.result == "nocache"){
						iconTag.attr('class', classCacheNo).attr('title', resArr.title);
						elem.addClass('nocache').css('cursor','default');
					}else{
						if(resArr.result !== true){
							iconTag.attr('class', classCache);
							jAlert(ln.cacheerror + ajax+resArr.title, ln.alerttitle);
						}else{
							iconTag.attr('class', classCacheOk).attr('title', resArr.title)
						}
					}
				}
			}
		});
		return false;
	});


	
	// Sortieren des Seitenbaums
	$('body').on("click",'*[data-action="cutpagebranch"]', function(){
	
		if(!$('.cancelSort:visible').length) { // Falls noch kein Element "ausgeschnitten" wurde
			
			var moveId		= $(this).attr('data-pageid'); // Id vom Button auslesen
			var targetUrl	= $(this).attr('data-url');
			var listTag		= $(this).closest('li');
			
			listTag.addClass('cutListEntry'); // ausgeschnittener Menüpunkt Style
			$('.cutPageBranch').hide(); // cut Button verstecken
			$('.pasteBelow').not($(this).siblings()).show(); // paste Button anzeigen
			$('.pasteChild').not($(this).siblings()).show(); // paste Button anzeigen
			listTag.children('ul').children('li').children('*').find('.pasteBelow, .pasteChild').hide(); // Paste-Buttons bei ausgeschnittenem Element verstecken
			$(this).before('<button class="cancelSort cc-button button button-icon-only" title="' + ln.cancel + '" data-menuitem="true" data-id="item-id-' + listTag.attr('data-itemcount') + '" data-menutitle="' + ln.cancel + '"><span class="cc-admin-icons cc-icons cc-icon-cancel">&nbsp;</span></button>'); // Cancel-Button einfügen
			$('li.sortFirstEntry').slideDown(300).children('.editButtons-panel').show(); // Erstes Element Button einblenden
			$('.oneSort, .delPage').hide(); // Sort-Button ausblenden
		}
	
		// Falls auf cancel geklickt wird, Ausschneiden rückgängig machen
		$('.cancelSort').click(function(){ // Den neuen Buttons die pageId des Menüpunkts mitgeben
			$(this).closest('li').removeClass('cutListEntry'); // Menüpunkt durchgehend umrahmen
			$('.pasteBelow').hide(); // paste Button verstecken
			$('.pasteChild').hide(); // paste Button verstecken
			$('.cutPageBranch:not(.newItem)').show(); // cut Button anzeigen
			$('li.sortFirstEntry').slideUp(200); // Cut-Button ausblenden
			$(this).remove(); // Cancel-Button entfernen
			$('.oneSort, .delPage').show(); // Sort-Button ausblenden
			return false;
		});
		
	
		// Falls auf paste below oder paste child geklickt wird
		$('.pasteBelow, .pasteChild').click(function(){
			
			var pageId		= $(this).attr('data-pageid'); // Id vom Button auslesen
			var menuitem	= "";
			
			targetUrl += '&move=' + moveId + '&targetid=' + pageId + '&sort=';
			
			if($(this).hasClass('pasteBelow')){
				targetUrl += 'below';
				if(pageId == "new") { // Falls in ein Menü ohne bisherigen Eintrag verschoben werden soll
					menuitem = $(this).attr('data-newpos'); // menu_item vom parent.span auslesen
					targetUrl += '&menuitem=' + menuitem;
				}
			}else{
				targetUrl += 'child';
			}
			
			
			if(targetUrl.match("=undefined") != null){
				return false;
			}
			
			$.doAjaxAction(targetUrl, true);			
			return false;
		});
		
		return false;
	});
	

	
	// Checkbox Overwrite highlighten
	$('body').on("click", '.overwriteLabel', function() {
			
		if($(this).find('input[type="checkbox"]').is(':checked')){
			$(this).children('.markBox').addClass('highlight');
		}else{
			$(this).children('.markBox').removeClass('highlight');
		}
	});
	
	
	// Checkboxen mit markBox highlighten (hover)
	$('body').on("mouseenter", 'label[for]', function() {
		var inputID = $(this).attr('for');
		$(this).siblings('label.markBox, label.markAll').has('input[id="'+inputID+'"]').addClass('state-hover');
	}).on("mouseleave", 'label[for]', function() {
		var inputID = $(this).attr('for');			
		$(this).siblings('label.markBox, label.markAll').has('input[id="'+inputID+'"]').removeClass('state-hover');
	});
	
	
	// Checkboxen mit markBox highlighten (checked)
	$('body').on("click", 'input[type="checkbox"]', function() {
			
			var elem		= $(this);

			if(elem.parent('.markBox').length){				
				if(elem.is(':checked')){
					elem.parent('.markBox').addClass('highlight');
				}else{
					elem.parent('.markBox').removeClass('highlight');
				}
			}
	});


	// Feld für Bilderskalierung einblenden
	$('body').on("click", 'input.scaleimg', function() {
		
		var elem		= $(this);
		
		elem.parent('.markBox').siblings('.scaleImgDiv').toggle('fast');
		
		if(elem.is(':checked')){
			elem.parent('.markBox').addClass('highlight');
		}else{
			elem.parent('.markBox').removeClass('highlight');
		}
	});
	
	
	// Feld für files-Ordnerauswahl einblenden
	$('body').on("click", 'input.useFilesFolder', function() {
			
		var elem		= $(this);
		var filesDiv	= elem.parent('label').siblings('div.filesDiv');
		
		filesDiv.toggle('fast');
		
		if(elem.is(':checked')){
			elem.closest('.markBox').addClass('highlight');
			filesDiv.children('div.mediaList').children('.showListBox').click();
		}else{
			elem.closest('.markBox').removeClass('highlight');
			$("div.dimDiv").remove();
		}
	});


	
	// Filtern von Listen
	// Falls eine Listenanzeige gefiltert werden soll (z.B. nach Buchstabe a-z)
	$('body').on("click", 'select.listFilter', function () {
		
		var filterVal = $(this).val();
		
		// Elternelement bestimmen
		if($(this).closest('div.listBox').length){
			var parListElem = $(this).closest('div.listBox').children('div.innerListBox').children('div.listItemBox').children('.editList');
		}else{
			if($(this).closest('.editList').length){
				var parListElem = $(this).closest('.editList');
			}else{
				var parListElem = $('.editList');
			}
		}
		
		if(filterVal == "all"){
			parListElem.find('li').show();
			// Falls Galerie, Button zum Wiedereinblenden ausgeblendeter Listeneinträge anzeigen
			if(parListElem.children('.showHiddenListEntries').length) {
				parListElem.children('.showHiddenListEntries').fadeOut(200);
			}
		}else{
			parListElem.find('li:not(.' + filterVal + ')').hide();
			parListElem.find('li.' + filterVal).show().parents('li, ul').show();
			// Falls Galerie, Button zum Wiedereinblenden ausgeblendeter Listeneinträge anzeigen
			if(parListElem.children('.showHiddenListEntries').length) {
				parListElem.children('.showHiddenListEntries').fadeIn(300);
			}
		}
		
		return false;
	});
	
	// Falls eine Listenanzeige durch einen Suchbegriff gefiltert werden soll
	$('body').on("keyup", 'input.listSearch', function (e) {
	
		// Auswahlbegrenzung nach Anfangsbuchstaben zurücksetzen auf "all"
		$('select.listFilter').children('option:first-child').attr('selected','selected');
		
		var filterVal 	= $(this).val().toLowerCase();
		var listBox		= false;
		var pageSort	= false;
		var langSearch	= false;
		var listItem	= -1;
		var parListElem = "";
		
		// Elternelement bestimmen
		if($(this).closest('div.listBox').length){ // Suche in der ListBox
			listBox = true;
			parListElem = $(this).closest('div.listBox').children('div.innerListBox').children('div.listItemBox').children('.editList');
		}else{
			if($(this).hasClass('langKeySearch')){ // Suche nach Sprachbausteinen (key)
				parListElem = $(this).closest('.editStatText').children('form');
				langSearch = "key";
			}else{
			if($(this).hasClass('langStringSearch')){ // Suche nach Sprachbausteinen (string)
				parListElem = $(this).closest('.editStatText').children('form');
				langSearch = "string";
			}else{
			if($(this).closest('.editList').length){ // Suche in der editList
				parListElem = $(this).closest('.editList');
			}else{
				parListElem = $('.editList');
				pageSort	= true;
			}}}
		}
		
		parListElem.find('li').each(function(index, domEle){
			
			if(filterVal == ""){
				$(domEle).show();
				return true;
			}			
		
			// domEle == this								
			// Falls erstes Element, alle verstecken
			if(index == 0){
				parListElem.find('li').not('.submit').not('.addLangKey').hide();
				parListElem.children('h4').next('ul').hide();
			}
			
			// Plugins
			if($(domEle).hasClass('pluginEntry')){
				listItem = $(domEle).children('.pluginName').text().toLowerCase().search(filterVal);
				// Falls der Suchtext vorhanden
				if(listItem > -1){
					$(domEle).show().parents('li, ul:not(.editList)').show();
				}
				return true;
			}

			// Falls Galerie und Galerieauflistung NICHT in der ListBox oder falls Seitenliste oder Sprachbausteine!
			if(listBox == false){
				
				if(pageSort){
					// Falls Pages
					listItem = $(domEle).children('.pageTitle').html().toLowerCase().search(filterVal);
				}else{
					if(!langSearch){
						listItem =	$(domEle).find('.openList').val().toLowerCase().search(filterVal);
						if(listItem < 0){
							listItem =	$(domEle).find('*[data-content="tags"]').text().toLowerCase().search(filterVal);
						}
						// Falls Galerie, Button zum Wiedereinblenden ausgeblendeter Listeneinträge anzeigen
						if(parListElem.children('.showHiddenListEntries').length) {
							parListElem.children('.showHiddenListEntries').fadeIn(300);
						}
				}else{
					// Falls langSearch
					if(langSearch
					&& !$(domEle).hasClass('headLabel')
					&& !$(domEle).children('div.addLangKey').length
					&& !$(domEle).hasClass('submit')
					){
						if(langSearch == "key"){ // Falls Schlüsselsuche
							listItem = $(domEle).children('input.langKey').val().toLowerCase().search(filterVal);
						}else{
							listItem = $(domEle).children('textarea.langString').val().toLowerCase().search(filterVal);
						}										
					}else{
						listItem = -1;
					}
				}}
			}else{
				var listBoxDom	= parListElem.closest('div.listBox');
				// Falls Bilder
				if(listBoxDom.hasClass('images')){
					listItem = $(domEle).children('div.previewBox').children('img').attr('title').toLowerCase().search(filterVal);
				}else{
					// Falls Galerie, Name aus html
					if(listBoxDom.hasClass('gallery')){
					listItem = $(domEle).children('.galleryName').text().toLowerCase().search(filterVal);
				}else{
					// Falls Link, Fetch oder TargetPage, Name aus span.pageTitle
					if(listBoxDom.hasClass('targetPage') || listBoxDom.hasClass('links') || listBoxDom.hasClass('fetch')){
					listItem = $(domEle).children('.pageTitle').html().toLowerCase().search(filterVal);
				}else{
					// Falls Dokumente
					if(listBoxDom.hasClass('docs')){
					listItem = $(domEle).children('a').html().split(">")[1].toLowerCase().search(filterVal);
				}else{
					// Falls Video
					if(listBoxDom.hasClass('video')){
					listItem = $(domEle).children('a').html().split(">")[1].toLowerCase().search(filterVal);
				}else{
					// Falls Audio
					if(listBoxDom.hasClass('audio')){
					listItem = $(domEle).children('img').attr('title').toLowerCase().search(filterVal);
				}else{
					// Falls Artikel/News/Planner
					if((listBoxDom.hasClass('category')
					|| listBoxDom.hasClass('articles')
					|| listBoxDom.hasClass('news')
					|| listBoxDom.hasClass('planner')
					|| listBoxDom.hasClass('feed'))
					&& index > 0
				){
					listItem = $(domEle).children('span.pageTitle').html().toLowerCase().search(filterVal);
				}else{
					// Falls Files
					if(listBoxDom.hasClass('files')){
					listItem = $(domEle).find('*[data-name]').attr('data-name').toLowerCase().search(filterVal);
				}
			}}}}}}}}
			
			// Falls der Suchtext vorhanden
			if(listItem > -1){
				$(domEle).show().parents('li, ul:not(.editList)').show();
			}
		});

		return false;
			
	});
	
	// Tabelle Suchbegriff filtern
	$('body').on("keyup", 'input.cc-input-table-search', function (e) {
		
		var filterVal 	= $(this).val().toLowerCase();
		var listItem	= -1;
		var searchTab	= $($(this).attr("data-target"));
		
		if(filterVal == ""){
			searchTab.find('tbody tr').each(function (index, domEle) { $(domEle).show(); } );
			return false;
		}
			
		searchTab.find('tbody tr').each(function (index, domEle) {
			// Falls erstes Element, alle verstecken
			if(index == 0){
				searchTab.find('tbody tr').hide();
			}
		
			listItem =	$(domEle).children('td').text().toLowerCase().search(filterVal);
			
			if(listItem < 0){
				listItem =	$(domEle).find('*[data-content="tags"]').text().toLowerCase().search(filterVal);
			}
			// Falls Galerie, Button zum Wiedereinblenden ausgeblendeter Listeneinträge anzeigen
			if(searchTab.children('.showHiddenListEntries').length) {
				searchTab.children('.showHiddenListEntries').fadeIn(300);
			}
			
			// Falls der Suchtext vorhanden
			if(listItem > -1){
				$(domEle).show().parents('li, ul:not(.editList)').show();
			}
		});

		return false;
			
	});
	
	// Falls eine Listenanzeige durch einen Suchbegriff, der über GET mitegegeben wurde, gefiltert werden soll, keyupevent zum Filtern auslösen 
	if(head.mobile){
		$('input.listSearch').keyup().blur();
	}
	
		
	// Sortieren von Listen
	// Falls eine Listenanzeige sortiert werden soll (z.B. nach Name)
	$('body').on("click", 'select.listOrder', function () {
		
		var filterVal = $(this).val();
		var listBox = false;
		var parListElem = "";
		var compA = "";
		var compB = "";
		
		// Elternelement bestimmen
		if($(this).closest('div.listBox').length){
			listBox = true;
			parListElem = $(this).closest('div.listBox').children('div.innerListBox').children('div.listItemBox').children('.editList');
		}else{
			parListElem = $(this).closest('.editList');
		}
		
		var listitems = parListElem.children('li').get();
		
		listitems.sort(function(a, b) {
			
			// Falls Galerie und Galerieauflistung NICHT in der ListBox!
			if(listBox == false){
				compA = $(a).find('.openList').val().toUpperCase();
				compB = $(b).find('.openList').val().toUpperCase();
			}else{
			var listBoxDom	= $(a).closest('div.listBox');
			// Falls Bilder
			if(listBoxDom.hasClass('images') && listBox){
				compA = $(a).children('div.previewBox').children('img').attr('title').toUpperCase();
				compB = $(b).children('div.previewBox').children('img').attr('title').toUpperCase();
			}else{
				// Falls Galerie, Name aus html
				if(listBoxDom.hasClass('gallery')){
					compA = $(a).text().toUpperCase();
					compB = $(b).text().toUpperCase();
			}else{
			// Falls Dokumente
				if(listBoxDom.hasClass('docs') || listBoxDom.hasClass('files')){
					compA = $(a).children('a').html().split(">")[1].toUpperCase();
					compB = $(b).children('a').html().split(">")[1].toUpperCase();
			}else{
			// Falls Video
				if(listBoxDom.hasClass('video')){
					compA = $(a).children('a').html().split(">")[1].toUpperCase();
					compB = $(b).children('a').html().split(">")[1].toUpperCase();
			}else{
			// Falls Audio
				if(listBoxDom.hasClass('audio')){
					compA = $(a).children('img').attr('title').toUpperCase();
					compB = $(b).children('img').attr('title').toUpperCase();
			}else{
			// Falls Artikel/News/Planner
				if(listBoxDom.hasClass('category')
				|| listBoxDom.hasClass('articles')
				|| listBoxDom.hasClass('news')
				|| listBoxDom.hasClass('planner')
				){
					compA = $(a).text().toUpperCase();
					compB = $(b).text().toUpperCase();
			}
			}}}}}}
			
			var compC = $(a).attr('class').split('date-')[1];
			var compD = $(b).attr('class').split('date-')[1];
		   

			if(filterVal == "namedsc"){
				return (compA > compB) ? -1 : (compA < compB) ? 1 : 0;
			}else{
				if(filterVal == "dateasc"){
					return (compC < compD) ? -1 : (compC > compD) ? 1 : 0;
			}else{
				if(filterVal == "datedsc"){
					return (compC > compD) ? -1 : (compC < compD) ? 1 : 0;
			}else{
				if(filterVal == "idasc"){
					return (compC < compD) ? -1 : (compC > compD) ? 1 : 0;
			}else{
				if(filterVal == "iddsc"){
					return (compC > compD) ? -1 : (compC < compD) ? 1 : 0;
			}else{
				return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
			}
			}}}}
		});
		
		$.each(listitems, function(idx, itm) { parListElem.append(itm); });					
		
		return false;
	});



	// Löschen eines Inhaltelements bzw. von Nachrichtenkategorien/Zurücksetzen von Bewertungen
	$('body.admin').on("click", 'img.delcon, *[data-action="delete"]', function(){
	
		var targetUrl = $(this).attr('data-url');
		var confMes = ln.confirmdelcon;
		
		if($(this).hasClass('delall')) {
			confMes = ln.confirmdelall + "<strong>" + $(this).closest('span.editButtons-panel').siblings('div.mediaList').find('.openList').val() + "</strong>" + "\r\n\r\n";
		}
		if($(this).hasClass('delgal')) {
			confMes = ln.confirmdelgall + "<strong>" + $(this).attr('data-url').split("&gal=")[1] + "</strong>" + "\r\n\r\n";
		}
		if($(this).hasClass('delcat')) {
			confMes = ln.confirmdelcat + "<strong>" + $(this).attr('data-title') + "</strong>" + "\r\n\r\n";
		}
		if($(this).hasClass('delform')) {
			confMes = ln.confirmdelform + "<strong>" + $(this).attr('data-title') + "</strong>" + "\r\n\r\n";
		}
		if($(this).hasClass('delIP')) {
			confMes = ln.confirmdelip + "<strong>" + $(this).closest('tr').find('span.botIP').html() + "</strong>" + "\r\n\r\n";
		}
		if($(this).hasClass('validIP')) {
			confMes = ln.confirmdelipbot + "<strong>" + $(this).closest('tr').find('span.botIP').html() + "</strong>" + "\r\n\r\n";
		}
		if($(this).hasClass('resetVotes')) {
			confMes = ln.confirmresvote + "<strong>" + $(this).attr('title') + "</strong>" + "\r\n\r\n";

			var rater = $(this).closest('.listEntry').find(".starrater");
			
			jConfirm(confMes, ln.confirmtitle, function(result){
														if(result === true){
															
															$.ajax({
																url: targetUrl,
																success: function(ajax){
																	rater.find(".cc-rating").rating('update', 0);
																	rater.find(".cc-rating").rating('refresh', {disabled: true});
																}
															});
														}
													 });
		} else {
			jConfirm(confMes, ln.confirmtitle, function(result){
														if(result === true){
															$.doAjaxAction(targetUrl, true);
															return false;
														}
													 });
		}
		return false;
	});


	// Fieldset toggle
	$('body').on("click", '.elements fieldset legend', function(e){
		e.preventDefault();
		e.stopPropagation();
		$(this).closest('fieldset').toggleClass('collapsed');
	});


	// Galerie-Optionen toggeln (im Editbereich)
	$('body').on("change", 'select.selectGalleryType', function(){
		if($(this).val().toLowerCase().indexOf('slide') > -1){
			$(this).closest('.elements').find('.sliderOptions').removeClass('hide');
		}else{
			$(this).closest('.elements').find('.sliderOptions').addClass('hide');
		}
	});


	// Erweiterte Formularfeld-Boxen ein-/ausblenden (im Editbereich)
	// Rating
	$('body').on("click", 'input.checkRating', function(){
		if($(this).is(':checked')){
			$(this).closest('div.elements').find('div.rating').fadeIn(300);
		}else{
			$(this).closest('div.elements').find('div.rating').fadeOut(300);
		}
	});
	// Kommentare
	$('body').on("click", 'input.checkComments', function(){
		if($(this).is(':checked')){
			$(this).closest('div.elements').find('div.comments').fadeIn(300);
		}else{
			$(this).closest('div.elements').find('div.comments').fadeOut(300);
		}
	});

 	
	// Link zu Module(kategorie) im Editbereich
	$('body').on("click", 'img.modLink:not(.gallery)', function(){
		var targetUrl = $(this).parent('a').attr('href');
		var catId = $(this).parent('a').siblings('input[type="hidden"]').val();
		if(catId == "<all>"){catId = "all";}
		targetUrl += catId;
		$(this).parent('a').attr('href',targetUrl);
	});
	
	
	// Artikel/Nachricht/Termin Listenansicht umschalten
	$('body.admin').on("click", '.toggleDataList', function(){
		// Auslösen von Button zur Anzeige einer Listenansicht
		var liObj = $(this).closest('.adminBox').find('.dataList');
		if(liObj.hasClass('collapsed')) {
			$.cookie("dataList", 1, { expires: 7, path: '/' });
			liObj.find('.listEntryContent').slideDown(200, function(){ liObj.removeClass('collapsed'); })
		}else{
			$.cookie("dataList", 2, { expires: 7, path: '/' });
			liObj.find('.listEntryContent').slideUp(100, function(){ liObj.addClass('collapsed'); })
		}
		liObj.children('.expanded').removeClass('expanded');
		return false;
	});
	
	

	// Artikel/Nachricht/Termin Tag-Liste generieren
	$('body').on("click", 'input.dataTags', function(){
		
		var element			= $(this);
		var elementTag		= this;
		var selectedTags	= $.trim(element.val());
		var mod				= element.attr('data-type');
		var separator		= "";

		
		if(!$('ol.tagList').length) {
				
			var targetUrl = cc.httpRoot + '/system/access/editModules.php?page=admin&action=tags&mod=' + mod + '&chosentags=' + selectedTags;
		
			$.ajax({
				url: targetUrl,
				success: function(ajax){
						
						var listTerm;
						var aktiv;
						var addTag;
						var childLi;
						
						element.after(ajax);
						
						listTerm = $('ol.tagList');
						
						listTerm.children('li').click(function(e){
						
							e.preventDefault();
							e.stopImmediatePropagation();
						
							cc.conciseChanges	= true;
							
							selectedTags = $.trim(element.val());
							if(!selectedTags){
								separator = "";
							}else{
								separator = ", ";
							}
							addTag = $(this).html().replace(/<\/?strong>/ig, "");
							element.val(selectedTags + separator + addTag);
							childLi = $(this).parent('ol.tagList').children('li').length;
							$(this).fadeOut(250, function(){ $(this).remove(); });
							if(childLi <= 1){
								$('ol.tagList').fadeOut(200, function(){ $(this).remove(); });
							}
						});
		
						// TagListe bei Click außerhalb entfernen
						element.closest('.tagSelection').parents('*').click(function(e){
							listTerm.fadeOut(200, function(){ $(this).remove(); });
						});
						
						element.keyup(function(e){
						
							aktiv = listTerm.children('li.highlight').index();
							
							switch (e.keyCode) {
								case 40: // "Pfeil nach unten"-Taste
									if (aktiv > -1) {
										
											listTerm.children("li:eq(" + aktiv + ")").removeClass('highlight');
											listTerm.children("li:eq(" + (aktiv + 1) + ")").addClass('highlight');
									} else {
											listTerm.children("li:eq(0)").addClass('highlight');
									}
								break;
								case 38: // "Pfeil nach oben"-Taste
									if (aktiv > -1) {
											listTerm.children("li:eq(" + aktiv + ")").removeClass('highlight');
											listTerm.children("li:eq(" + (aktiv - 1) + ")").addClass('highlight');
									} else {
											listTerm.children("li:last-child").addClass('highlight');
									}
								break;
								case 13: // "Return"-Taste
									if (aktiv > -1) {
										listTerm.children("li:eq(" + aktiv + ")").click();
										//return false;
									}
								break;
								case 39: // "Pfeil nach rechts"-Taste
									if (aktiv > -1) {
										listTerm.children("li:eq(" + aktiv + ")").click();
									}
								break;
								
							}
						});
						element.bind("keydown",function(e){
							if(e.keyCode == 38){
								return false;
							}
						});
						
						element.keydown(function(e){
							if(e.keyCode == 13){
								return false;
							}
						});
					
					return false;
				}
			});
		}else{
	
			$('ol.tagList').fadeOut(200, function(){ $(this).remove(); });
		}
		return false;
	});
	
		
	
	// Artikel/Nachricht/Termin neues Objekt hinzufügen
	$('body').on("click", 'button[data-action="adddataobject"]', function addDataObject(){

		var element		= $(this);
		var sortObj		= element.closest('#sortableObjects');
		var targetUrl	= element.attr('data-url');
		var newObject	= targetUrl.split("newobj=");
		var newObjectNr	= parseInt(newObject[1])+1;
		var	newTargetUrl	= newObject[0] + "newobj=" + newObjectNr;
		
		sortObj.sortable('disable');
		
		//jAlert(targetUrl, ln.alerttitle);
		$.ajax({
			url: targetUrl
		}).done(function(ajax){
			element.attr('data-url', newTargetUrl);
			element.parent('li').parent('ul').children('.newobj').before(ajax).prev().hide().fadeIn(300, function(){
				sortObj.sortable('enable');
			});
		});
		return false;
	});


	// Artikel/Nachricht/Termin Objekte anzeigen/verstecken
	$('body.admin').on("click", 'span.objectToggle', function(){
		$(this).next('div.objects').toggle();
	});


	// Listen-Objekte anzeigen/verstecken
	$('body.admin').on("click", '.toggleObjectType', function(e){
	
		var input	= $(this);
		var listObj = input.closest('li');
		
		if(!input.is(':checked')) {
			listObj.children('div.objects').children('div.attach').show();
			listObj.children('.objectToggle').removeClass('busy');
			input.parent('.markBox').siblings('label').next('div').show();
			input.parent('.markBox').next('label').next('div').hide();
			listObj.children('div.objects').children('div.attach').children('.toggleObjectType').removeAttr('checked');
		}
		else {
			var attach	= input.closest('div.attach');
			
			listObj.children('div.objects').children('div.attach').hide();
			listObj.children('.objectToggle').addClass('busy');
			attach.children('.toggleObjectType').next('label').next('div').hide();
			attach.children('.toggleObjectType').not(input).removeAttr('checked');
			attach.show(0, function(){
				input.prop('checked','checked').parent('.markBox').next('label').next('div').show();
			});
		}
	});


	// Datensatz veröffentlichen
	$('body').on("click", '*[data-publish]:not([data-action="editcon"])', function(e){
	
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var elem		= $(this);
		var targetUrl	= elem.attr('data-url');
		var listItem	= elem.closest('.listEntry, .listItem');
		var toggleClass	= listItem && listItem.attr('data-toggleclass') || false;
		
		elem.loading();
		
		$.ajax({
			url: targetUrl,
			success: function(ajax){
				if(ajax == "1"){
					elem.siblings('[data-publish]:hidden').css('display','inline-block');
					elem.css('display','none').loadingRemove();
					
					if(toggleClass){
						listItem.toggleClass(toggleClass);
					}
					
					// Falls contentElement
					if(listItem.hasClass('contentElement')){
						var pub	= elem.siblings('.elementStatus').val() == "1" ? 0 : 1;
						elem.siblings('.elementStatus').val(pub)
					}
				}else{
					elem.loadingRemove();
					jAlert(ln.dberror, ln.alerttitle);
				}
			},
			error: function (ajax) {
				console.log(ajax, targetUrl)
			}
		});
		return false;
	});


	// Artikel/Nachricht löschen
	$('body.admin').on("click", '*[data-action="deldata"]', function(){
		
		var delData = $(this);
		
		jConfirm(ln.confirmdeldata, ln.confirmtitle, function(result){
														if(result === true){ // ...Löschen bestätigen
		
															var targetUrl = delData.attr('data-url');
															var targetDiv;
															
															if(delData.closest('div.listEntry').length){
																targetDiv = delData.closest('div.listEntry');
															}
															
															$.ajax({
																url: targetUrl,
																success: function(ajax){
																	if(delData.closest('div.listEntry').length){
																		targetDiv.fadeOut(300, function(){ targetDiv.remove(); });
																	}else{
																		document.location.href = ajax;
																	}
																	return false;
																}
															});
															return false;
														} else {
															return false;
														}
													});
	});


	// Löschen des Installationsordners
	$('body.admin').on("click", 'a.delInstallDir', function(e){
		e.preventDefault();
		var url = cc.httpRoot + '/admin' + $(this).attr('href');
		jConfirm(ln.confirmdelfolder, ln.confirmtitle, function(result){
														if(result === true){
															$.doAjaxAction(url, true);
														}
		});
		return false;
	});


	// Löschen von Plug-ins
	$('body.admin').on("click", '*[data-action="del_plugin"]', function(){
		var delElem		= $(this);
		var confMes		= ln.confirmdelplugin + delElem.attr('data-delstr');
		var targetUrl	= delElem.attr('data-url');
		jConfirm(confMes, ln.confirmtitle, function(result){
														if(result === true){
															$.doAjaxAction(targetUrl, false, function(url){ document.location.href = url; });
														}
		});
		return false;
	});


	// Löschen von Templates
	$('body.admin').on("click", '*[data-action="deltpl"]', function(){
		var targetTpl = $('select[name="template"]').val();
		var confMes = ln.confirmdeltpl1 + "<strong>" + targetTpl + "</strong>" + ln.confirmdeltpl2;
		var delElem = $(this);
		jConfirm(confMes, ln.confirmtitle, function(result){
														if(result === true){
															delElem.siblings('input[name="del_tpl"]').val(targetTpl);
															delElem.closest('form').submit();
														}
		});
		return false;
	});


	// Löschen von Themes
	$('body.admin').on("click", '*[data-action="deltheme"]', function(){
		var delTheme = $('select[name="del_theme"]').val();
		var confMes = ln.confirmdeltheme1 + "<strong>" + delTheme + "</strong>" + ln.confirmdeltheme2;
		var delElem = $(this);
		jConfirm(confMes, ln.confirmtitle, function(result){
														if(result === true){
															delElem.closest('form').submit();
														}
		});
		return false;
	});


	// Theme Auswahl bestätigen
	$('body').on("change", 'select#currTheme', function(){
		var elem		= $(this);
		var theme		= elem.val();
		var currTheme	= elem.children('option.currentTheme');
		var confMes 	= ln.confirmtheme + "<strong>" + theme + "</strong>";
		jConfirm(confMes, ln.confirmtitle, function(result){
														if(result === true){
															elem.closest('form').submit();
														}else{
															elem.children('option[value="' + theme + '"]').removeAttr('selected');
															currTheme.attr('selected','selected');
															elem.val(currTheme.attr('value'));
															
															// reset Image picker
															if(typeof(elem.data('picker')) != "undefined"){
																elem.data('picker').sync_picker_with_select();
															}
														}
		});
		return false;
	});


	// Mehrere Einträge markieren
	$('body').on("click", '*[data-select="all"]', function(e){
		
		var elem			= $(this);
		var markDiv			= elem.closest('.markAll');
		var markTarget		= markDiv.attr('data-mark');
		var markTargetDom	= "";
		
		if(typeof(markTarget) != "undefined"){
			if(markTarget[0] == "#"){
				markTargetDom = $('*[id="' + markTarget.split("#")[1] + '"]').find('.markBox:not(.disabled)');
			}else{
			if(markTarget[0] == "."){
				markTargetDom = $(markTarget);
			}else{
				markTargetDom = $('.' + markTarget);
			}}
		}
	
		if(elem.is(':checked')){
			markDiv.addClass('highlight');
			
			// Falls eine Zielbox angegeben
			if(markTargetDom != ""){
				markTargetDom.addClass('highlight').children('input[type="checkbox"]').not('input[disabled]').prop('checked','checked');
				return true;
			}
			markDiv.closest('form:not(#editPageContents-form)').children('*:not(.actionBox):not(.controlBar):not(ul.pageDetails)').find('.markBox:visible:not(.disabled)').addClass('highlight').children('input[type="checkbox"]').prop('checked','checked');
			if(markDiv.closest('.pagesListDiv').length){
				markDiv.closest('.pagesListDiv').children('.pageList').find('.markBox:not(.disabled)').addClass('highlight').children('input[type="checkbox"]').prop('checked','checked');
				markDiv.closest('.pagesListDiv').find('.toggleSubList').children('.cc-admin-icons').addClass('cc-icon-toggle-marked');
			}
			markDiv.closest('form#editPageContents-form').children('.elements').children('li').children('.markBox').addClass('highlight').children('input[type="checkbox"]').prop('checked','checked');
			markDiv.closest('table').find('input[type="checkbox"]:visible').prop('checked','checked').parent('.markBox').addClass('highlight');
			markDiv.closest('div.listBox').children('div.innerListBox').children('div.listItemBox').children('ul.editList').find('input[type="checkbox"]:visible').prop('checked','checked').parent('.markBox').addClass('highlight');
			markDiv.closest('div.listBox').children('div.innerListBox').children('form').children('div.listItemBox').find('input[type="checkbox"]:visible').prop('checked','checked');
		}else{
			markDiv.removeClass('highlight');
			
			// Falls eine Zielbox angegeben
			if(markTargetDom != ""){
				markTargetDom.removeClass('highlight').children('input[type="checkbox"]').not('input[disabled]').removeAttr('checked');
				return true;
			}
			markDiv.closest('form:not(#editPageContents-form)').children('*:not(.actionBox):not(.controlBar):not(ul.pageDetails)').find('.markBox:not(.disabled)').removeClass('highlight').children('input[type="checkbox"]').removeAttr('checked');
			if(markDiv.closest('.pagesListDiv').length){
				markDiv.closest('.pagesListDiv').children('.pageList').find('.markBox:not(.disabled)').removeClass('highlight').children('input[type="checkbox"]').removeAttr('checked');
				markDiv.closest('.pagesListDiv').find('.toggleSubList').children('.cc-admin-icons').removeClass('cc-icon-toggle-marked');
			}
			markDiv.closest('form#editPageContents-form').children('.elements').children('li').children('.markBox').removeClass('highlight').children('input[type="checkbox"]').removeAttr('checked');
			markDiv.closest('table').find('input[type="checkbox"]').removeAttr('checked').parent('.markBox').removeClass('highlight');
			markDiv.closest('div.listBox').children('div.innerListBox').children('div.listItemBox').children('ul.editList').find('input[type="checkbox"]').removeAttr('checked').parent('.markBox').removeClass('highlight');
			markDiv.closest('div.listBox').children('div.innerListBox').children('form').children('div.listItemBox').find('input[type="checkbox"]').removeAttr('checked');
		}
	});


	// Checkbox "Einträge markieren" highlighten
	$('body').on("click", 'input.addVal:not(.disabled), .markImage input:not(.disabled)', function(){
	
		var checkBox		= $(this);
		var markBox			= checkBox.closest('.markBox');
		var toggleSubList	= markBox.parents('li').find('.toggleSubList');
		
		if(checkBox.is(':checked')){
			markBox.addClass('highlight');
			// Falls Unterliste mit markiert werden soll
			if(markBox.closest('.pagesListDiv').length){
				markBox.parent('li').children('ul').find('.markBox').addClass('highlight').children('input[type="checkbox"]').prop('checked','checked').attr('disabled','disabled');
				if(toggleSubList.length){
					toggleSubList.children('.cc-admin-icons').addClass('cc-icon-toggle-marked');
				}
			}
		}else{
			markBox.removeClass('highlight');
			if(markBox.closest('.pagesListDiv').length){
				markBox.parent('li').children('ul').find('.markBox').removeClass('highlight').children('input[type="checkbox"]').removeAttr('checked','checked').removeAttr('disabled');
				if(toggleSubList.length){
					toggleSubList.children('.cc-admin-icons').removeClass('cc-icon-toggle-marked');
				}
			}
		}
	});


	// Veröffentlichen von mehreren Einträgen
	$('body.admin').on("click", '.pubAll', function(e){
	
		var pubElem		= $(this);
		var pubItems	= new Array();
		var pubLabels	= "";
		var confMess	= false;
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
		
		// Seiten / Inhaltselemente
		if(pubElem.hasClass('pubPages')
		|| pubElem.hasClass('pubElements')
		){

			pubElem.loading();
			
			if(pubElem.hasClass('pubPages')){ // Seite
				pubLabels	= pubElem.closest('div.pagesListDiv').find('label.markBox').filter(function(){
					return ($(this).hasClass('.highlight') || $(this).children('input').is(':checked')) && $(this).siblings('.editButtons-panel').find('a.pageStatus').attr('data-status') == !pub;
				});
			}else{ // Inhaltselement
				pubLabels	= pubElem.closest('.elements').children('.contentElement').find('label.markBox').filter(function(){
					return ($(this).hasClass('.highlight') || $(this).children('input').is(':checked')) && $(this).siblings('.editButtons-panel').find('.elementStatus').val() == !pub;
				});
			}
			pubLabels.each(function(i, e){
			
				var pubItem			= $(e).parent('li');
				var pubItemNr		= "";
				
				if(pubElem.hasClass('pubPages')){ // Seite
					pubItemNr	= pubItem.children('.pageID').html().replace("#", "");
				}else{
					pubItemNr	= pubItem.attr('data-sortid');
				}
				var pubInput		= $(e).children('.addVal');
				if(!pubInput.attr('disabled') && typeof(pubItemNr) != "undefined"){
					pubItems.push(pubItemNr);
				}
			});
			
			if(pubItems.length == 0){
				pubElem.loadingRemove();
				return false;
			}
			
			var status	= pub == 0 ? 1 : 0;
			targetUrl	= pubElem.closest('.editButtons-panel').children('.multiAction').val();
			targetUrl	+= "&action=publish&status=" + status + "&online=" + pub + "&items=" + pubItems;
	
			confMess = unpub ? ln.confirmunpubdata : ln.confirmpubdata;
			confMess = confMess.replace("$1", pubItems.length);
			
			jConfirm(confMess, ln.confirmtitle, function(result){
													if(result === true){
														$.getWaitBar();
														$.ajax({
															url: targetUrl,
															success: function(ajax){
																$.ajaxReplace(ajax, $('div#adminContent'));
															}
														});
														return false;
													}
			});
			pubElem.loadingRemove();
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
							pubEntry.click();
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

		// Auswahl-Anzahl ersetzen
		pubItems	= pubElem.closest('form').find('.addVal:checked');

		// Datenmoduleeinträge
		if(pubElem.hasClass('pubData')){
			confMess = unpub ? ln.confirmunpubdata : ln.confirmpubdata;
		}
		
		// Plugins
		if(pubElem.hasClass('pubPlugins')){
			confMess = unpub ? ln.confirmunpubdata : ln.confirmpubdata;
		}
		
		// Forms u.a.
		if(pubElem.hasClass('pubMultiple')){
			var targetList	= $('.selectableItems');
			var status		= pub == 0 ? 1 : 0;
			confMess 		= unpub ? ln.confirmunpubdata : ln.confirmpubdata;
			pubItems 		= targetList.find('.addVal:checked');
		}
		
		// Gästebuch/Kommentare
		if(pubElem.hasClass('pubGBook') || pubElem.hasClass('pubComments')){
			confMess = unpub ? ln.confirmunpubcom : ln.confirmpubcom;
		}

		
		if(confMess){
			var parForm		= pubElem.closest('form');
			var targetUrl	= parForm.attr('action');
			var urlExt		= "";
			confMess		= confMess.replace("$1", pubItems.length);
			
			jConfirm(confMess, ln.confirmtitle, function(result){
														if(result === true){
															pubElem.loading();
															$.getWaitBar();
															if(pubElem.hasClass('pubPlugins')){
																targetUrl	+= "activate" + "&active=" + pub;
															}else{
															if(pubElem.hasClass('pubMultiple')){
																$.getMarkedListInputs(parForm, targetList);
																targetUrl	+= "publish" + "&status=" + status;
															}else{
																targetUrl	+= "publish" + "&pub=" + pub;
															}}
															
															parForm.attr('action', targetUrl);
															parForm.submit();
														}
			});
		}
		
		pubElem.loadingRemove();
		
		return false;
	});

	
	// Löschen von mehreren Einträgen bzw. Zurücksetzen von votes
	$('body').on("click", '*[data-action="delmultiple"]', function(){
	
		var delElem		= $(this);
		var confMess	= false;
		var urlExt		= "";
		var targetUrl	= "";
		var parForm		= "";
		
		// Falls keine Elemente ausgewählt
		if(!$('.markBox.highlight').length){
			var alertMes = delElem.hasClass('delPages') ? ln.nofilessel : ln.nodatasel;
			jAlert(alertMes, ln.alerttitle);
			return false;
		}

		// Seiten
		if(delElem.hasClass('delPages')){
			var parForm		= delElem.closest('form');
			var targetList	= parForm.closest('div.pagesListDiv');
			
			$.getMarkedListInputs(parForm, targetList);
			
			parForm.submit();
			
			return false;
		}
		// Benutzer
		if(delElem.hasClass('delUsers')){
			var parForm		= delElem.closest('form');
			var targetList	= parForm.closest('div.adminBox').find('#cc-userList');
			
			$.getMarkedListInputs(parForm, targetList);
		
			parForm.submit();
			
			return false;
		}
		// Inhaltselemente
		if(delElem.hasClass('delElements')){
			confMess	= ln.confirmdeldatas;
			targetUrl	= delElem.closest('.editButtons-panel').children('.multiAction').val();
			urlExt		= "&action=del";
		}
		// Daten-Kategorien
		if(delElem.hasClass('delDataCats')){
			var parForm		= delElem.closest('form');
			var targetList	= parForm.parents('.adminBox').children('.dataCatList');

			confMess 	= ln.confirmdelcat;
			targetUrl 	= parForm.attr('action');
			urlExt	 	= "delcats";
		}
		// Daten
		if(delElem.hasClass('delData')){
			confMess 	= ln.confirmdeldatas;
			targetUrl 	= delElem.closest('form').attr('action');
			urlExt	 	= "deldata";
		}
		// Formulare
		if(delElem.hasClass('delForms')){
			var parForm		= delElem.closest('form');
			var targetList	= $('.selectableForms');

			confMess 	= ln.confirmdelform;
			targetUrl 	= parForm.attr('action');
			urlExt	 	= "delforms";
		}
		// Formularfelder
		if(delElem.hasClass('delFormFields')){
			confMess 	= ln.confirmdelentry;
			targetUrl 	= delElem.attr('data-url');
		}
		// Formulardaten
		if(delElem.hasClass('delFormData')){
			confMess 	= ln.confirmdelentry;
		}
		// Newsl
		if(delElem.hasClass('delNewsl')){
			var targetList	= $('#newsletterList');
			confMess 	= ln.confirmdeldatas;
			targetUrl 	= delElem.closest('form').attr('action');
			urlExt	 	= "delnewsl";
		}
		// Backups
		if(delElem.hasClass('delBackups')){
			var parForm		= delElem.closest('form');
			var targetList	= $('#dbBackupList');

			confMess 	= ln.confirmdeldatas;
			targetUrl 	= parForm.attr('action');
			urlExt	 	= "delbackups";
		}
		// Votings
		if(delElem.hasClass('resetVotes')){
			confMess 	= ln.confirmresvotes;
			targetUrl 	= delElem.closest('form').attr('action');
			urlExt	 	= "resvotes";
		}
		// Reset
		if(delElem.hasClass('resetMultiple')){
			var targetList	= $('.selectableForms');
			confMess 		= ln.confirmresvotes;
			targetUrl 		= delElem.closest('form').attr('action');
			urlExt	 		= "resvotes";
		}
		// Kommentare
		if(delElem.hasClass('delGBook') || delElem.hasClass('delComments')){
			confMess	= ln.confirmdelcom;
			targetUrl 	= delElem.closest('form').attr('action');
			if(delElem.hasClass('delGBook')){
				urlExt	= "delgbook";
			}else{
				urlExt	= "delcomments";
			}
		}
		// Plugins
		if(delElem.hasClass('delPlugins')){
			confMess 	= ln.confirmdelplugin;
			targetUrl 	= delElem.closest('form').attr('action');
			urlExt	 	= "delete";
		}
		// IPs
		if(delElem.hasClass('delIP')){
			targetUrl 	= delElem.closest('form').attr('action');
			if(delElem.hasClass('validIP')){
				confMess	= ln.confirmdelipbot;
				urlExt		= '&valid=1';
			}else{
				confMess	= ln.confirmdelips;				
			}
		}
		// delmultiple
		if(delElem.attr('data-confirm')){
			confMess	= delElem.attr('data-confirm');
			targetUrl 	= delElem.closest('form').attr('action');
		}
		if(confMess){
			jConfirm(confMess, ln.confirmtitle, function(result){
														if(result === true){
														
															parForm	= delElem.closest('form');
															if(	delElem.hasClass('delElements') || 
																delElem.hasClass('delGBook') || 
																delElem.hasClass('delComments') || 
																delElem.hasClass('delDataCats') || 
																delElem.hasClass('delData') || 
																delElem.hasClass('delForms') || 
																delElem.hasClass('delFormFields') || 
																delElem.hasClass('delNewsl') || 
																delElem.hasClass('delIP') || 
																delElem.hasClass('delBackups') || 
																delElem.hasClass('delPlugins') || 
																delElem.hasClass('resetVotes') ||
																delElem.hasClass('resetMultiple')																
															){
																if(delElem.hasClass('delDataCats')
																|| delElem.hasClass('delForms')
																|| delElem.hasClass('delNewsl')
																|| delElem.hasClass('delBackups')
																|| delElem.hasClass('resetMultiple')
																){
																	$.getMarkedListInputs(parForm, targetList);
																}
																parForm.attr('action', targetUrl + urlExt).show(1,function(){
																	$.submitViaAjax(parForm, true);
																	return false;
																});
															}else{
																$.submitViaAjax(parForm);
															}
															return false;
														}
			});
		}
		return false;
	});


	// Theme-Vorschaubilder einblenden
	$('body.admin').on("bind, mouseenter", 'select.themes option', function(e){
			
		var theme = $(this).html();
		var themeImg = cc.httpRoot  + '/themes/' + theme + '/img/theme-preview.jpg';

		$(this).closest('div.selTheme').append('<div class="themePreview"><img src="' + themeImg + '" /></div>');
				
	}).on("bind, mouseleave", 'select.themes option', function(e){
		$('div.themePreview').remove();
	});

	
	// Auf doppelte Theme-Farbe prüfen
	$("body").on("blur", 'div.colorTab input.color', function(){
		var currCol = $(this).val().toUpperCase();
		var currEle = $(this).closest('.colorTab').attr('id');
		var duplCol = false;
		var i = 1;
		$('div.colorTab input.color').each(function(i, e){
			var checkCol = $(e).val().toUpperCase();
			var checkEle = $(e).closest('.colorTab').attr('id');
			if(checkCol == currCol && checkEle != currEle){
				jAlert("#" + (currCol || "none") + ln.duplColor1 + parseInt(i+1) + "." + ln.duplColor2, ln.alerttitle);
			}
		});
		return false;
	});
	
	// Einzelne Theme-Farbe zurücksetzen
	$('body').on("click", '.colorSample', function(){
		var oldCol		= rgb2hex($(this).css('background-color'), false);
		var colID		= $(this).closest('.colorTab').attr('id').split("-")[1];
		$.jPicker.List[colID-1].color.active.val('hex', oldCol);
		return false;
	});
	
	// Alle Theme-Farben zurücksetzen
	$('body').on("click", '#resetColors', function(){
		var confmes = $(this).val() + '?';
		jConfirm(confmes, ln.confirmtitle, function(result){					
			if(result) {
				var totColors	= $("#totColors").attr("class").split("-")[1];
				var oldCol		= 0;
				var colID		= 0;
				
				for(i = 1; i <= totColors; i++){
					oldCol		= rgb2hex($('div#col-'+i).find('.colorSample').css('background-color'), false);
					$.jPicker.List[i-1].color.active.val('hex', oldCol);
				}
			}
		});
		return false;
	});
	
	//Function zur Konvertierung von rgb nach hex
	function rgb2hex(rgb,hash) {
		var hexDigits = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];
		rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
		function hex(x) {
			return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
		}
		if(hash == true){
			return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
		}else{
			return hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
		}
	}
	
	// Neues Inhaltelement
	$('body.admin').on("click", '.newcon', function(e){
		
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var currNewCon = $(this).next('div.addCon');
		currNewCon.toggle("fast", function(){
			$('div.addCon').not(currNewCon).hide();
			if($("div.listBox").length){
				$("div.listBox").remove();
			}
			if($("div.dimDiv").length){
				$("div.dimDiv").remove();
			}
			if(currNewCon.is(":visible")){
				currNewCon.find('.showListBox').click();
			}
		});
		return false;
	});
	

	// Anlegen eines Inhaltelements (Select)
	$('body.admin').on("change", 'select.new_con', function(){
		var conType = $(this).children('optgroup').children('option:selected').val();
		var targetUrl = $(this).siblings('input.ajaxaction').val() + '&type=' + conType;
		$.doAjaxAction(targetUrl, true);
	});

	
	
	// Bearbeiten eines Inhaltelements
	$('body.admin').on("click", '*[data-action="editcon"]', function(){
	
		var elem		= $(this);
		
		// Falls Änderungen vorliegen, Bestätigung abwarten
		if( cc.conciseChanges && 
			!elem.hasClass('publishElement')
		){
			jConfirm(ln.savefirst, ln.confirmtitle, function(result){
							
				if(result === true){										
					$.editContents(elem);
				}
				return false;
			});
		}else{
			$.editContents(elem);
		}
		return false;
	});
	
	

	// Überprüfen von Benutzergruppen (Settings) on the fly
	$('body.admin').on("blur", "textarea.ownUserGroups", function(){
							  
		var groups = $(this).val().split("\n");
		var forbiddenGroups = new Array("admin","editor","author","guest","subscriber");
		var isValidGroups = true;
		
		$.each(groups, function(elem){
			if($.inArray(groups[elem], forbiddenGroups) > -1)
			{
				isValidGroups = false;
			}
			// Verhindern, dass Gruppenname 2x vorkommt
			forbiddenGroups.push(groups[elem]);
		});
	
		if(isValidGroups)
		{
			$(this).removeClass('invalid');
		} else {
			$(this).addClass('invalid');
		}
	});
	

	
	// Ausgeblendete List-Items einblenden
	$('body.admin').on("click", '*[data-reveal]', function() {
		var itemsId	= $(this).attr('data-reveal');
		$(this).fadeOut(300, function(){ $(this).remove(); }).parent().children(itemsId + ':hidden').fadeIn();
	});
	
	
	// Änderungen übernehmen
	$('body.admin').on("click",'*[data-action="applychanges"], \
								*[data-action="discardchanges"]',
								function(e) {

		e.preventDefault();
		e.stopImmediatePropagation();

		var url			= $(this).attr('href');
		var confirmMes	= $(this).attr('title');
		
		if(confirmMes == ""){
			confirmMes	= $(this).restoreTitleTag();
		}
		
		confirmMes	= "<strong>" + confirmMes + "</strong>" + "\r\n\r\n";
		
		if($(this).hasClass('change') && $(this).hasClass('right')){
			confirmMes += ln.confirmchanges + "\r\n\r\n";
		}
		confirmMes += ln.isreversible;
		
		if(cc.conciseChanges){
			confirmMes += "<br /><br />" + ln.savefirst;
		}
		
		jConfirm(confirmMes, ln.confirmtitle,
				
			function(result){
				
				if(result !== true) {
					$('.loadingDiv').remove();
					$('div.dimDiv').remove();
					return false;
				}else{
					$.doAjaxAction(url, true);
				}
			}
		);
		return false;
	});
	
	

	// Loading-Platzhalterbilder bei Änderungen
	$('body.admin').on("click", 'input.change:not(#popup_ok), input.publish:not(#send_newsl)', function(e)
	{	

		var submitElem	= $(this);
		var parForm		= submitElem.closest('form');
		
		// Falls im Edit-/Modulebereich FilesFolder gecheckt ist, aber kein Ordner ausgewählt ist
		if($('input.useFilesFolder:checked').length){
			
			var noFolder = false;
			
			// Falls files-Ordner gecheckt ist, aber kein Ordner angegeben, abbrechen
			$('input.useFilesFolder:checked').each(function(){
				if($(this).is(':visible') && $(this).siblings('div.filesDiv').children('input.filesFolder').val() == "" && $(this).parent('div.fileUploadBox').children('div.uploadMask').children('input.newUploadFile').val() != ""){
					noFolder = true;
				}
			});
			if(noFolder) {
				jAlert(ln.choosefolder, ln.alerttitle);
				return false;
			}
		}
		
		// Falls nicht falsch ausgefüllte Felder (settings)
		if(!$('.invalid').length && typeof(parForm) != "undefined") {
			if($('div.listBox').length){
				$('div.listBox').hide();
			}
			// Falls keine Redirect-Url angegeben oder Installationsseite
			if($(location).attr('href').match('&red=') != null || 
				submitElem.attr('id') == "submitInstall"
			){
				$.getWaitBar();
				return true;
			}
			
			if(parForm.find('input[type="file"]').filter(function(){ return !this.value == ""}).length > 0){
				return true;
			}else{
				e.preventDefault();
				e.stopImmediatePropagation();
				$.submitViaAjax(submitElem);
			}
		}else{
			return false;
		}
	});
	
	
	// Abschicken von Formularen ohne Ajax (bei Nutzung von Files)
	$('body').on("click", 'input[type="file"]', function(e){
	
		$(this).closest('form').attr('data-ajax', false);
	
	});
	
	
	// Abschicken von Formularen via Ajax (e.g., stats)
	$('body.admin').on("click", '.stats input[type="submit"]', function(e){
	
		e.preventDefault();
		var btn = $(this);
		$.submitViaAjax(btn, false); // Formular abschicken		
	
	});


	// Links / Seitennavigation via Ajax
	$('body.admin').on("click", '.ajaxLink, .pageNav a', function(e){
		
		e.preventDefault();
		e.stopPropagation();
		
		var url = $(this).attr('href');
		
		if(typeof(RGraph) == 'object'){
			RGraph.ObjectRegistry.Clear();
		}
		
		$.doAjaxAction(url, true);
		
		return false;
	});


	// Abschicken von Formularen via Auswahlliste (e.g., Statistiken)
	$('body.admin').on("change", 'select.submit', function(){
	
		$.getWaitBar();
		
		if(typeof(RGraph) == 'object'){
			RGraph.ObjectRegistry.Clear();
		}

		$(this).parent('form').submit();
		return false;
	});


	// Checken der Checkbox Newsletter bei Auswahl von group = subscriber (user Bereich)
	$('body.admin').on("change", 'select#selGroup', function(){
		if($(this).children('option:selected').val() == "subscriber"){
			$('input[name="newsl"]').prop('checked','checked').attr('disabled',true);
		} else {
			$('input[name="newsl"]').attr('disabled',false);
		}
	});

	
	// Default-Sprache ändern (Ajax)
	$('body.admin').on("click", '.changeDefLang input', function(){
		
		var checkBox	= $(this);
		var parElem		= $(this).closest('span');
		var defLangTag	= $(this).closest('ul').find('.defLang');
		var targetUrl	= parElem.attr('data-url');
		var newTitle	= "";
		
		$.ajax({
			url: targetUrl,
			success: function(ajax){
				checkBox.prop('checked','checked');
				defLangTag.appendTo(parElem);
				if(checkBox.restoreTitleTag()){
					newTitle	= checkBox.attr('title').replace(/\"[a-z]+\"/, '"' + checkBox.attr('data-lang') + '"');
					$('.changeDefLang input').attr('title', newTitle);
				}
			}
		});
	
	return false;
	});

	
	// Sprache installieren
	$('body.admin').on("click", '.installLang.installable', function(e){
		
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var elem	= $(this);
		var confmes	= elem.attr("data-lang");
		
		jConfirm(confmes, ln.confirmtitle,
				
			function(result){
				
				if(result === true) {
					$.submitViaAjax(elem, true);
				}
				return false;
			}
		);
		return false;
	});

	
	// Sprachbaustein-Schlüssel ändern
	$('body.admin').on("dblclick", 'input.langKey', function(){
		
		var oldVal = $(this).val();
		$(this).removeAttr('readonly');	
		
		$(this).blur(function(){
			if($(this).val() == oldVal){
				$(this).attr('readonly','readonly');
			}
		});
		return false;
	});

	
	// Sprachbaustein-Texteingabe
	$('body.admin').on("keyup", 'li.statText textarea', function(e){
		
		var textlen = $(this).val().length;
		var rows	= parseInt($(this).attr('rows'));
		var rowfactor	= 60;

		if(textlen > (rowfactor * rows) || e.keyCode===13){
			$(this).attr('rows', (rows + 1)).removeAttr('class');
		}
	});

	
	// Sprachbaustein hinzufügen
	$('body.admin').on("click", 'label.addLangKey', function(){
		
		$(this).next('div.addLangKey').toggle();	
		return false;
	});

	
	// Preview für Newsletter
	$('body.admin').on("click", '#preview_newsl', function()
	{
		var newsltext = "";
		if($(this).is(':checked')) {
			newsltext = tinymce.activeEditor.getContent({format : 'raw'});
		} else {
			newsltext = $('textarea#newsl_text').val().replace(/\n/g, "<br />", newsltext);
		}
		if(newsltext == "") {
			jAlert(ln.enternewsl, ln.alerttitle);
		} else {
		var fenster = window.open(' ','Newsletter preview','resizeable=no,width=960,height=720,toolbar=no,scrollbars=yes,dependent=yes');
		$(fenster.document).ready(function(){
			fenster.document.open();
			fenster.document.writeln(newsltext);
			fenster.document.close();
		});
		}
		return false;
	});


	// Token beim Newsletterformular entfernen bei Click auf Template, da sonst die Session verloren geht (Bug?!)
	$('body.admin').on("click", 'a#newsl_text_template', function(){
		$(this).closest('form').find('input[name="token"]').remove();
	});


	// Newsletter versenden bestätigen
	$('body.admin').on("click", '#send_newsl', function(e){
		
		e.preventDefault();
		
		var submitButton = $(this);
		var subject		= $('#newsl_subject').val();
		var newsltxtele	= $('#newsl_text');
		var newsltxt	= "";
		var newsltxtchk	= "";
		var attachkeys	= newsltxtele.attr('data-attachkeywords').split(",");
		var subLen		= subject.length;
		
		if($.trim(subject) == ""
		|| subLen >= 300
		){
			jAlert(ln.entersubject, ln.alerttitle);
			return false;
		}
		
		if(typeof(tinymce.editors['newsl_text']) != "undefined"){
			tinymce.editors['newsl_text'].save();
		}

		newsltxt	= newsltxtele.val();

		if($.trim(newsltxt) == ""){
			jAlert(ln.enternewsl, ln.alerttitle);
			return false;
		}

		newsltxtchk	= newsltxt.toLowerCase();
		
		var send	= true;
		var kcount	= attachkeys.length;
		
		// Auf Anhang keywords überprüfen
		$.each(attachkeys, function(i,e) {
			var keyw	= e.toLowerCase();
			var existF	= $('.existingFile:visible');
			var newF	= $('.newUploadFile:visible');
			
			if(newsltxtchk.indexOf(keyw) > -1
			&& (!$('.toggleObjectType:checked').length
			|| (typeof(existF) == "object" && typeof(existF.val()) != "undefined" && existF.val() == ""
			&&  typeof(newF) == "object" && typeof(newF.val()) != "undefined" && newF.val() == ""))
			){
				send	= false;
				jConfirm(ln.checkattachment, ln.confirmtitle, function(result){
					if(result === true){
						checkSendNewsl();
						return true;
					}
					return false;
				});
				return false;
			}
			
			if(!--kcount && send){
				checkSendNewsl();
			}
		});
		
		function checkSendNewsl(){
		
			var confmes		=  ln.confirmsendnewsl;
			var subExt		= $('#onlysubscribers:checked').closest('div.onlySubscribers').find('label[for="onlysubscribers"]').text();
			var extraM		= $('#newsl_extraemails').val();
			subExt		= subExt != "" ? " (" + subExt + ")" : "";
			extraM		= extraM != "" ? "\r\n\r\n" + $('label[for="newsl_extraemails"]').text() + ":\r\n\r\n<strong>" + extraM + "</strong>" : "";
			confmes		+= "<strong>" + $('#newsl_group').children('option:selected').map(function(){ return $(this).val().replace(/[<>]/g, "") }).get().join(", ") + "</strong>";
			confmes		+= subExt;
			confmes		+= extraM;
			
			jConfirm(confmes, ln.confirmtitle, function(result){
							
				if(result === true){										
					var finalSubmit = '<input type="hidden" name="send_newsl" value="true" />'; // verstecktes Inputfeld für den Versand einfügen
					submitButton.parent('.submit').append(finalSubmit);
					$.submitViaAjax(submitButton, false);
				}
			});
			return false;
		}
		return false;
	});
	

	// Platzhalter für Newsletterempfänger einfügen
	$('body.admin').on("click", '.namePlaceholder-button', function(){

		var placeh = "{%name%}";
		var htmlActive = false;
		
		// Falls HTML_Format (tinymce)
		if($('#newsl_format').is(':checked')) {
			if(tinymce.getInstanceById('newsl_text')){
				tinymce.activeEditor.selection.setContent(placeh);
				tinymce.execCommand('mceFocus', true, 'newsl_text');
			}
		}else{

			var textfield	= $('textarea#newsl_text');
			var range		= textfield.caret();
			var currValue	= textfield.val();
			
			if(range.start||range.start=="0"){
				var t_start=range.start;
				var t_end=range.end;
				var val_start=textfield.val().substring(0,t_start);
				var val_end=textfield.val().substring(t_end,textfield.val().length);
				textfield.val(val_start+placeh+val_end).focus();
			}else{
				textfield.val(currValue+placeh).focus();
			}
		}
	});


	// Select multiple background
	$('body').on("click", 'select[multiple] option', function(){
	
		var ct = "check";
		
		$(this).siblings().not(':selected').removeAttr('selected').removeClass(ct);
		$(this).map(function(){
			if($(this).is(':selected')){
				$(this).addClass(ct).attr('selected','selected');
			}else{
				$(this).removeClass(ct).removeAttr('selected');
			}
		});
	});
		
	
	// Ajax-Action (Links)
	$('body.admin').on("click", 'a[data-ajax]', function(e) {

		e.preventDefault();
		
		var elem		= $(this);
		var targetUrl	= elem.attr('href');
		var forceAssign	= false;
		
		if(cc.conciseChanges == true || typeof(targetUrl) == "undefined" || targetUrl == ""){
			return false;
		}
		
		if(elem.attr('data-ajaxload') && elem.attr('data-ajaxload') == "fullpage"){
			forceAssign	= true;
		}
		
		$.loadPageViaAjax(targetUrl, forceAssign);
		
		return false;
	
	});
	
	
	
	// Ajax-Action (e.g., veröffentlichen/verstecken, Galeriebilder)
	$("body").on("click", "span[data-ajax], img[data-ajax], input[data-ajax], button[data-ajax]", function(e) {

		var elem		= $(this);
		var targetUrl	= elem.attr('data-url');
		var confMes		= elem.attr('data-confirm');
	
		if(typeof(targetUrl) == "undefined"){
			return false;
		}
			
		// Falls confirm promt
		if(typeof(confMes) != "undefined"){
			jConfirm(confMes, ln.confirmtitle, function(result){
							
				if(result === true){										
					$.doAjaxAction(targetUrl, true);
				}
				return false;
			});
			return false;
		}
			
		// Falls Änderungen vorliegen, Bestätigung abwarten
		if( cc.conciseChanges && 
			!elem.hasClass('publishElement')
		){
			jConfirm(ln.savefirst, ln.confirmtitle, function(result){
							
				if(result === true){										
					$.doAjaxAction(targetUrl, true);
				}
				return false;
			});
		}else{
			$.doAjaxAction(targetUrl, true);
		}

	});
	
	
	
	// Ajax-Action (Select)
	$("body").on("change", "select[data-autosubmit]", function(e) {

		var elem		= $(this);
		var optVal		= elem.children('option:selected').val();
		var targetUrl	= elem.attr('data-url');
		targetUrl		+= optVal;
		
		$.doAjaxAction(targetUrl, true);
		
		return false;
	});

}); // Ende document ready function

}); // Ende concise.init ready function



	
(function($){

	$.toggleDashboard = function(){
		
		// Admin-Hauptbereichansicht
		if(!$('#adminMain').length)
			return false;
		
		var showMenu  = "short";
		var showStats = "-stats";
		var regexMenu = /full/;
		var regexStat = /stats/;
		
		if(typeof($.cookie("adminmain")) == 'undefined' || $.cookie("adminmain") == null || !regexMenu.exec($.cookie("adminmain"))){
			$('#adminMain').addClass('shortMenu').removeClass('hide');
			$('#adminMain li').hide();
			$('#adminMain li.shortMenu').css('display','inline-block').removeClass('hide');
		}else{
			showMenu = "full";
		}
		if(typeof($.cookie("adminmain")) !== 'undefined' && $.cookie("adminmain") != null && !regexStat.exec($.cookie("adminmain"))){
			$('div.stats').hide();
			showStats = "";
		}
		
		$.scaleAdminContentWrapper();
		
		// Umschlaten der Hauptbereichansicht
		$('body').on("click", '#toggleMenu', function(){
		
			// Falls fullMenu
			if(showMenu == "short"){
				if(showStats == ''){
					showStats = "-stats";
					$('div.stats').show();
				}else{
					showStats = "";
					$('div.stats').hide();
				}
				$.cookie("adminmain", showMenu + "Menu" + showStats, { expires: 999, path: '/' });
				showMenu = "full";
				$('#adminMain').addClass('shortMenu').removeClass('hide');
				$('#adminMain li').hide().addClass('hide');
				$('#adminMain li.shortMenu').css('display','inline-block').removeClass('hide');
			// Falls shortMenu
			} else {
				$.cookie("adminmain", showMenu + "Menu" + showStats, { expires: 999, path: '/' });
				showMenu = "short";
				$('#adminMain').removeClass('shortMenu');
				$('#adminMain li').css('display','block').removeClass('hide');
				$('#adminMain li.hide').hide();
			}
			
			$.scaleAdminContentWrapper();
		
		});
	};
})(jQuery);


	
// Inhalte via Ajax
(function($){

	$.loadPageViaAjax = function(targetUrl, forceAssign){
		
		var currUrl		= document.location.href.split("#")[0];
		
		// Falls neu laden erforderlich
		if(forceAssign
		|| currUrl.split("?")[0] != targetUrl.split("?")[0]
		|| (cc.useHistory && History.enabled == false)
		){
			$.getWaitBar(function(){
				location.assign(targetUrl);
			});		
			return false;
		}
		
		// Andernfalls ajaxReplace
		$.getWaitBar();
		
		$.ajax({
			url: targetUrl,
			success: function(ajax){
				
				var targetNoHash	= targetUrl.split("#")[0];
				
				// Ggf. History setzen
				if(cc.useHistory
				&& targetNoHash != currUrl
				){
					History.pushState({name: "Concise WMS Admin", id: "rndPageId-" + Math.random()}, $.getHtmlTitle(), targetNoHash);
				}
				
				// Inhalte ersetzen
				$.ajaxReplace(ajax);
			}
		});
		
		return false;
	
	}; // Ende doAjaxAction
	
})(jQuery);


	
// Inhalte via Ajax
(function($){

	$.doAjaxAction = function(targetUrl, replaceDom, callback){
	
		$.getWaitBar();

		var newDom	= "";
		var qss		= targetUrl.indexOf("?") >= 0 ? '&' : '?';
		targetUrl  += qss + "isajax=1";
		
		$.ajax({
			url: targetUrl,
			success: function(ajax){
				if(typeof(ajax) == "object"
				|| ajax.indexOf("{") === 0
				){
					if(typeof(ajax) == "string"){
						ajax = JSON.parse(ajax);
					}
					$.getJSONResponse(ajax);
					replaceDom	= true; // verhindert erneutes Ausführen von executeInitFunctions in done-Funktion
					//$('.listBox').remove();
					//$('.addCon').hide();
					$.removeWaitBar();
					return false;
				}
				
				if(replaceDom){
				
					// Inhalte ersetzen
					$.ajaxReplace(ajax);					
				}
			}
		}).done(function(ajax){
			if(typeof(callback) == "function"){
				callback(ajax);
			}else{
				if(!replaceDom){
					
					// Inhalte ersetzen
					$.ajaxReplace(ajax);
				}
			}
		}).fail(function(error) {
			console.log(error);
		});
		return false;
	
	}; // Ende doAjaxAction
	
})(jQuery);


// Submit button als hidden input an Formular anhängen
(function($) {
	$('body').on("mouseup", 'button[type="submit"]:not(#send_newsl), input[type="submit"]:not(#send_newsl)', function(e) {

		e.preventDefault();
		
		var elem	= $(this);
		var name	= elem.attr('name');
		var val		= elem.val();
		var form	= elem.closest('form');
		if(!form.find('input[type="hidden"][name="' + name + '"]').length) {
			var hidinp	= '<input type="hidden" name="' + name + '" value="' + (val || 1) + '" />';
			form.append(hidinp);
		}
		return this;
	});
})(jQuery);


// Submit function überschreiben mit submitViaAjax
/*
(function($) {
	$.extend($.fn, {

		submit: function(){
		
			if($(this).attr('id') == "uploadfm"
			&& $(this).attr('class') == "default-uploader"){
				document.getElementById("uploadfm").submit();
				return true;
			}
			$.submitViaAjax($(this));
			return this;
		}
	});
})(jQuery);
*/


// Submit via Return
(function($) {

	$('body').on("keypress", 'input[type="text"], input[type="password"]', function(e){

		if(e.keyCode===13){

			if($(this).closest('.contentElement').length){
				
				$(this).closest('#editPageContents-form').children('li.submit').children('button[type="submit"]')[0].click();
				return false;
			}
			
			var form 	= $(this).closest('form');
			var sbm		= form.find('button[type="submit"]:not(.delAll,.pubAll):first');
			var name	= sbm.attr('name');
			
			if(sbm.length
			&& !form.find('input[type="hidden"][name="' + name + '"]').length
			){
			
				var sbmH	= '<input type="hidden" name="' + name + '" value="' + sbm.val() + '" />';

				form.append(sbmH);
				sbm[0].click();
				
				return false;
			}
		}
	});
})(jQuery);


// Submit function abfangen => submitViaAjax
(function($) {
	$('body').on("submit", 'form:not([data-ajax="false"])', function(e){

		e.preventDefault();
		
		$.submitViaAjax($(this));
		return this;
	});
	
	$('body').on("submit", 'form[data-ajax="false"]', function(e){

		$.getWaitBar();
	
	});
})(jQuery);


// Formular via Ajax übermitteln
(function($) {

	$.submitViaAjax = function(formelem, addSubmitVal, dataType, replace, callback){
	
		var form			= "";
		var formData;
		var changeUrl		= false;
		var success			= false;
		var updateHistory	= cc.useHistory;
		
		if(formelem.is('form')){
			form			= formelem;
		}else{
			form			= formelem.closest('form');
			addSubmitVal	= true;
		}
		var postAction		= form.attr('action');

		// Falls kein Ajax submit
		if ((typeof(form.attr('data-ajax')) != "undefined"
		&& (   form.attr('data-ajax') == "false"
			|| form.attr('data-ajax') == "0"))
		|| (typeof(form.attr('data-history')) != "undefined"
		&& form.attr('data-history') == "false")
		){
			updateHistory	= false;
		}

		if(typeof(postAction) == "undefined" || postAction == ""){
			postAction		= document.location.href;
		}
		
		var targetUrl		= postAction;

		targetUrl += targetUrl.indexOf("?") > -1 ? '&' : '?';
		targetUrl += 'ajax=1';
		
		if(typeof(form.attr('data-getcontent')) != "undefined" 
		&& form.attr('data-getcontent') == "fullpage"
		){
			targetUrl 	   += '&fullpage=1';
			changeUrl		= true;
		}
		
		form.attr('action', targetUrl);
		
		// Änderungen aus Editor in  Textarea übernehmen
		if(typeof(tinymce) == "object"){
			for (i=0; i < tinymce.editors.length; i++){
				var edID	= tinymce.editors[i].id;
				if(typeof(tinymce.editors[edID]) != "undefined" 
				&& (!tinymce.editors[edID].isHidden()
				|| $('#' + edID).hasClass('forceSave'))
				){
					tinymce.editors[edID].save();
				}
			}
		}
		
		// Änderungen aus Codemirror in  Textarea übernehmen
		// Codemirror (Templatebereich)
		if(typeof(htmlCodeMirror) == "object"){
			htmlCodeMirror.save();
		}
		// Codemirrorinstanzen (Editbereich)
		if(typeof(codeMirrorInstances) == "object"){
			for(index = 0; index < codeMirrorInstances.length; ++index) {
				codeMirrorInstances[index].save();
			}
		}
		
		// Formulardaten
		//if(typeof(FormData) == "function"){
		//	formData	= new FormData(document.getElementById(form.attr('id')));
		//}else{
			formData	= form.serialize();
		//}
	
		// Falls Submitbutton hinzugefügt werden muss
		if(typeof(addSubmitVal) != "undefined" 
		&& typeof(addSubmitVal) != null 
		&& addSubmitVal
		){
			var sbmBtnName	= encodeURIComponent(formelem.attr('name'));
			var sbmBtnVal	= encodeURIComponent(formelem.val());
			
			// Falls Feld nicht breits vorhanden
			if(formData.match("&" + sbmBtnName + "=") == null){
				formData	+=	"&" + sbmBtnName + "=" + sbmBtnVal;
			}
		}

		// Data Type e.g. json or html
		if(typeof(dataType) == "undefined"){
			dataType = "";
		}
		
		$.getWaitBar();
		
		$.ajax({
			type: "POST",
			url: targetUrl,
			data: formData,
			dataType: dataType,
			success: function(ajax){
			
				success	= true;
				
				// Inhalte ersetzen
				if(replace !== false){
					$.ajaxReplace(ajax);
				}
				
				// Ggf. History url push
				if(updateHistory){
					History.pushState({name: "Concise WMS Admin", id: "rndPageId-" + Math.random()}, $.getHtmlTitle(), postAction);
				}
			}
		}).error(function(ajax){
			console.log(ajax);
		}).done(function(ajax){
			if(typeof(callback) == "function"){
				callback(ajax);
			}else{
				if(!success){
					$.executeInitFunctions();
				}
				$.removeWaitBar();
			}
		});
		return false;
	};
})(jQuery);

	
// regenerateSession
(function($) {

	$.regenerateSession = function(){
	
		var targetUrl	= cc.httpRoot + "/_checkLoginStatus.html?session=0";
		
		jAlert(ln.sessiontimeout, ln.alerttitle);
		
		setTimeout(function() {
		
			$.ajax({
				url: targetUrl,
				async: false,
				success: function(ajax){
					// Falls Session nicht wieder hergestellt werden kann, zur Loginseite gehen
					if(ajax == "0"){
						document.location.href = cc.httpRoot + "?page=login&timeout=1";
						return false;
					}
					else {
						$('input[name="token"]').val(ajax);
						conciseCMS.regSess	= false;
						$.removeWaitBar();
						$('#popup_container').remove();
						$('#popup_overlay').remove();
						return true;
					}
				}
			});
		}, 2000 );		
	};
})(jQuery);


// ajaxReplace
(function($) {

	$.ajaxReplace = function(ajax, container){

		// Falls ajax kein String
		if(typeof(ajax) != "string"
		&& typeof(ajax) != "object"
		){
			cc.ccHandleError(ajax);
			return false;
		}

		// Falls ajax die Fehlerseite wegen abgelaufener Session enthält, versuchen, Session wieder herzustellen oder zur Loginseite gehen
		if(typeof(ajax) == "string"
		&& (ajax.match('<body id="page--1003"') != null 
		|| (typeof(conciseCMS.regSess) != "undefined" && conciseCMS.regSess))
		){
			$.regenerateSession();
			return false;
		}
		
		// Container für Ajaxinhalte
		container	= container || $('div#container');
	
		// Object-Instanzen löschen
		_clearJSObjects();
	
	
		// Falls ajax = JSON object
		if(typeof(ajax) == "object"
		|| ajax.indexOf("{") === 0){
			if($.isFunction($.ccJsonResultHandler)){ // Steht noch aus... !!!
				$.ccJsonResultHandler(ajax);
			}
			if($.isFunction($.replaceFEContent)){ // Steht noch aus... !!!
				$.replaceFEContent(ajax);
			}
			return false;
		}
		
		// Falls eine komplette Seite Inhalt der Ajaxantwort ist
		if(ajax.match('<!-- begin #container -->') != null){	
		
			$.replaceAdminFullContent(ajax, container);
			return false;
		}	
		
		// Andernfalls enthält ajax nur den Inhalt des #adminContents
		$.replaceAdminMainContent(ajax, container);
		return false;
	
	},
	_clearJSObjects = function() {
	
		// Ggf. Editoren entfernen
		if(typeof(tinymce) == "object"){
			tinymce.remove();
			/*
			for (i=0; i < tinymce.editors.length; i++){
				var edID	= tinymce.editors[i].id;
				if(typeof(tinymce.editors[edID]) != "undefined"){
					tinymce.remove(edID);
				}
			}
			*/
		}
		
		// Ggf. plupload entfernen
		if(typeof(plupload) == "function"){
			$('#myUploadBox').plupload('getUploader').trigger('destroy');
		}
		
		// Ggf. RGraph entfernen
		if(typeof(RGraph) == "object"){
			RGraph.ObjectRegistry.Clear();
		}
		
		
		// Remove window resize events
		$(window).unbind("resize");

		
		codeMirrorInstances = []; // Array CodeMirror-Instanzen leeren
		
		return true;
	
	};
})(jQuery);


// replaceFullContainer
(function($){

	// Adminseite via Ajax
	$.replaceAdminFullContent = function(ajax, container) {
	
		// Head Content
		var newDom;
		var startHead		= (ajax.toString().indexOf("<head>")+6);
		var stopHead		= (ajax.toString().indexOf("</head>"));
		var headCode		= ajax.substring(startHead,stopHead);
		var newHeadDom		= $('<div id="myNewHeadDom">' + headCode + '</div>');
		var startBody		= (ajax.toString().indexOf("<body"));
		var stopBody		= (ajax.toString().indexOf("</body>")+7);
		var bodyCode		= ajax.substring(startBody,stopBody);
		var newBodyDom		= $('<div id="myNewBodyDom">' + bodyCode + '</div>');
		var headLoad		= headCode.split('head.load({ui:')[1];
		var headSrc			= '{ui:' + headLoad.split(');});</script>')[0];
		var headSrcArr		= headSrc.split(',');
		var headLoadArr		= [];
		var headFiles		= [];
		var oldHeadFiles	= [];
		var headSrcStr		= "";
		
		$.each(headSrcArr, function(i,e){
			var key		= i;
			var src		= e;
			if(e.indexOf("{") === 0){
				var fileArr	= e.split(':"');
				key		= fileArr[0].replace( /[\{]/g, "" );
				src		= '"' + fileArr[1].replace( /[\}]/g, "" );
			}
			headLoadArr.push({key: key, name: unescape(src).replace( /['"]/g, "" )});
		});
		
		// Content
		var domRgt		= ajax.split('<!-- begin #container -->')[1];
		var domLft		= domRgt.split('<!-- end #container -->')[0];
		newDom			= $(domLft);
		var headTags	= {};


		// Funktionen zurücksetzen (da Init-Skripte neu hinzugefügt werden, s.u.)
		$.destroyEditors();

		
		// Seitentitel aktualisieren
		if(newHeadDom.find('title')){
			var newHtmlTitle	= newHeadDom.find('title').html();
			var titleTag		= document.getElementsByTagName('title')[0];
			typeof(titleTag) != "undefined" ? titleTag.innerHTML	= newHtmlTitle : '';
		}

		// Ggf. Style-Attrbut von <html> löschen (entfernt overflow:hidden bei Fullscreen ansicht);
		document.documentElement.removeAttribute('style');
		
		// Body non-contents löschen
		container.siblings().not('.dimDiv').remove();
		
		// Inline-Scripts löschen
		var adminJS			= $('head').find('script[src*="admin.min.js"]');
		var adminSrc		= adminJS.attr('src');
		var file			= "";
		var label			= "";
		var styleTagLast	= "";
		var styleTagAdd		= "";
		var newJSVars		= "";
		
		// Neue Head-Tags (CSS)
		newHeadDom.find('link[href][rel="stylesheet"]').each(function(i,e){
			file	= $(e).attr('href');
			//label	= "label" + (i+1);
			//head.load({label: file});
			if(!$('head').find('*[href="' + file + '"]').length){
				styleTagLast	= $('head').find('*[rel="stylesheet"]').last();
				styleTagAdd		= '<link href="' + file + '" type="text/css" rel="stylesheet" />';
				styleTagLast.after(styleTagAdd);
			}
		});
		
		// JSVars neu auslesen
		newJSVars	= newHeadDom.find('script[data-scriptcon="jsvars"]').text();

		// JSVars einlesen
		if(newJSVars &&  $('head').find('script[data-scriptcon="jsvars"]').length){
			//$('head').find('script[data-scriptcon="jsvars"]').html(newJSVars);
			eval(newJSVars);
			ln	= conciseCMS.ln;
		}

		head.ready("ccInitScript", function () {
		
			container.fadeTo(200, 0.05, function(){
				
				container.replaceWith(newDom);
			
				$('document').ready(function(){
				
					// Doppelte Stylesheets entfernen
					if($('head').find('*[href*="/styles.css?type=css"]').length > 1){
						var styleTagNew	= $('head').find('*[href*="/styles.css?type=css"]').last();
						var styleTagOld	= $('head').find('*[href*="/styles.css?type=css"]').first();
						var styleSrc	= styleTagNew.attr('href');
						styleTagOld.after(styleTagNew).prev(styleTagOld).remove();
					}
					
					// Neue JS head files laden
					$.each(headLoadArr, function(i,e){
						headFile	= e.name;
						headLabel	= e.key;
						if(!$('head').find('script[src="' + headFile + '"]').length){
							headFiles.push(headFile);
							head.load({headLabel: headFile});
						}else{
							oldHeadFiles.push(headFile);
						}
					});

					/* not recommended
					// Nicht mehr benötigte JS head files entfernen
					$('head').find('script[src]').not('[data-script="headjs"]').each(function(i,e){
						var scSrc	= $(e).attr('src');
						if($.inArray(scSrc, headLoadArr) == -1){
							head.load(scSrc).state = null;
							$(e).remove();
						}
					});
					*/
				
					newBodyDom.find('#container').siblings('script:not([src])').each(function(i,e){
						var file	= $(e).clone();
						file.appendTo('body');
					});
			
					head.ready(function(){
						$.executeInitFunctions(); // Init-Objekte reinitialisieren
						container.fadeTo(300, 1, function(){
							$.removeWaitBar();
						});
					});
				});
			});
		});
	
		return true
	};
})(jQuery);


// replaceAdminMainContent
(function($){

	// Admin main content via Ajax
	$.replaceAdminMainContent = function(ajax, container) {
	
		var newDom		= $(ajax);
		container		= container && container.attr('id') == "adminContent" ? container : container.find('div#adminContent');
		
		$.removeListBox();
		
		container.fadeTo(200, 0.05, function(){
		
			container.replaceWith(newDom);
			
			head.ready("ccInitScript", function(){
				$(document).ready(function(){
					$.executeInitFunctions(); // Init-Objekte reinitialisieren
					container.fadeTo(300, 1, function(){
						$.removeWaitBar();
					});
				});
			});
		});
	
		return true
	};
})(jQuery);


// Markiere Listeneintrags-IDs als input-Felder an Formular anhängen
(function($){
	$.getMarkedListInputs = function(form, list){
		
		var listItems	= list.find('label.markBox').filter('.highlight');
		var cnt			= listItems.length;
		
		listItems.each(function(i, e){
			var delInput		= $(e).children('input.addVal');
			var delInputName	= delInput.attr('name');
			var delInputVal		= delInput.val();
			delInputVal			= delInputVal.replace(/(['"])/g, "\\$1");
			
			if(!delInput.attr('disabled') && typeof(delInputName) != "undefined"){
				form.append('<input type="hidden" name="' + delInputName + '" value="' + delInputVal + '" />');
			}
			if(!--cnt){
				return true;
			}
		});
		return false;
	};
})(jQuery);


// Listen toggeln
(function($){
	$.toggleLists = function(){

		// Seitenbaum im Editbereich verstecken
		$('.pageList ul').hide();
		
		// Inhaltsliste (Seite) z.B. bei Editbereich einklappen
		$('.hideNext').next('ul, div').hide();
		
		// Aufklappen des zuletzt sortierten Menuabschnitts
		if(typeof($.cookie("sort_id")) != 'undefined' && $('.pageList li ul').length) {
			var CookieVal = $.cookie("sort_id");
			$('#sortid' + CookieVal).parents('ul, li').not('ul.pageList').css('display', 'block').attr('data-active','active');
		}
	};
})(jQuery);


// Inhaltsliste (Inhalte) toggeln
(function($){
	$.toggleContentElements = function(){
		$('#sortableContents').children('li.contentElement').each(function(){
			if($(this).find('.notice').length || $(this).find('.editentry').length){
				$(this).addClass('active').css('height','auto').css('margin-bottom','20px').children('div.elements').slideDown(400, function(){ $(this).addClass('active'); });
				if($.fn.sortable){
					$('#sortableContents').sortable('disable');
				}
			}
		});
	};
})(jQuery);


// top panel to front
(function($){
	$.setTopPanel = function(zIndex) {
		if(zIndex == "front"){
			$('#container > #topBox').addClass('toFront');
			$('#contentWrapper > #header').addClass('toFront');
		}else{
			$('#container > #topBox').removeClass('toFront');
			$('#contentWrapper > #header').removeClass('toFront');
			//$('#iconPanelTop').css('margin-right', '-' + parseInt($('#contentWrapper').width() /2) + 'px');
		}
		return false;
	};
})(jQuery);


// scale admin panels
(function($){
	$.scaleAdminPanels = function(res) {
	
		var panels	= $('.cc-admin-panel');
		
		if(!panels.length){
			return false;
		}
		
		panels.each(function(i,e){
			$(e).fadeTo(.05, 1, function(){
				$(this).removeAttr('style').delay(200)
				.fadeTo(.05, 1, function(){
				var pw	= $(this).width();
				var bw	= $(this).closest('.cc-admin-panel-box').width();
					$(this).fadeTo(1, 1, function(){
				
				if(pw > bw/2){
					$(this).css('min-width','100%');
				}
			});
			});
			});
		});
		return false;
	};
})(jQuery);


// scale admin panels
(function($){
	$.scaleAdminContentWrapper = function() {
	
		var cw	= $('body.admin #mainContent');
		var lt	= $('body.admin #left');
		var rt	= $('body.admin #right');
		
		cw.css('min-height',0);
		
		return setTimeout(function(){
			var vph	= $('body').height() - parseInt(cw.css('margin-bottom')) - parseInt($('body.admin #footer').outerHeight());
			var mch	= cw.height();
			var lch	= lt.height();
			var rch	= rt.height();
			
			return cw.css('min-height',Math.max(mch,vph,lch,rch) + 'px');
		},1);
	
	};
})(jQuery);


// Haupt-Meldung fixieren
(function($){
	$.fixAdminNotice = function(remove) {
		var notice			= $('div.adminArea').children('p.notice, p.error, p.success');
		var fixNote			= "";
		var noteOffset		= 0;
		var windowOffset	= 0;
		
		if(notice.length){
			
			var panelWidth	= notice.outerWidth();
			
			//$('.main-notice').remove();
			
			fixNote			= notice.clone();
			fixNote.css({width: panelWidth + 'px', 'max-width': 'calc(100% - 30px)', 'margin-left': '-' + parseInt(panelWidth /2) + 'px'}).addClass('main-notice');
			noteOffset		= parseInt(notice.offset().top);
			windowOffset	= parseInt($(window).scrollTop() + $("#topBox").height());
			if (windowOffset >= noteOffset) {

				notice.parent('div.adminArea').prepend(fixNote);

				fixNote.hide().fadeIn(600).delay(4500).fadeOut(400, function(){
					//fixNote.remove();
					if(typeof(remove) != "undefined" && remove){
					//	notice.remove();
					}
				});
			} else {
				//fixNote.fadeOut(200, function(){ $(this).remove(); });
			}
		}
		return false;
	};
})(jQuery);


// Submitbuttonpanel fixieren
(function($){

	$.fixButtonPanel = function(tabEle) {
	
		if(typeof(tabEle) == "undefined"
		|| head.mobile
		){
			return false;
		}

		var btnPanel	= "";

		$('li.submit.change').removeClass('submit-activepanel').removeAttr('style');

		btnPanel	= tabEle.parent().find('li.submit.change:visible:not(.buttonpanel-nofix, .back)');
		
		if(typeof(btnPanel) == "object"){
			
			btnPanel.show(function(){
				
				var currentSumbitElemTop	= $(this).offset().top;					
				var windowHeight			= parseInt($(window).height());
				var padding					= parseInt($('#mainContent').offset().left) + parseInt($('#mainContent').css('padding-left'));
				
				if (parseInt($(window).scrollTop() -65) >= currentSumbitElemTop - windowHeight) {
					btnPanel.removeClass('submit-activepanel').removeAttr('style');
				} else {
					btnPanel.css({'padding-left': padding + 'px', 'padding-right': padding + 'px'}).addClass('submit-activepanel');
				}
				return true;
			});
		}
		return false;
	};
})(jQuery);



// Bearbeiten eines Inhaltelements
(function($) {

	$.editContents = function(elem){

		var targetUrl	= elem.attr('data-url');
		var urlExt		= "";
		var locate		= true;

		// Kopieren eines Inhaltelements (Ajax)
		if(elem.hasClass('copycon') || elem.hasClass('cutcon') || elem.hasClass('pastecon')){
			$.doAjaxAction(targetUrl, true);
			return false;
		}
		// Einfügen eines Inhaltelements abbrechen (Ajax)
		if(elem.hasClass('cancelpaste')){
			$.doAjaxAction(targetUrl, false, function(){
				elem.parents('.cutListEntry').removeClass('cutListEntry');
				elem.parents('.copiedListEntry').removeClass('copiedListEntry');
				elem.remove();
				$('.pastecon').remove();
				$.removeWaitBar();
			});
			return false;
		}
		// Kopieren eines Daten-Modul-Eintrags (Ajax)
		if(elem.hasClass('copydata')){

			locate = false;
			jConfirm(ln.confirmcopydata, ln.confirmtitle,					
				function(result){
					if(result !== true) {
						return false;
					}else{
						$.doAjaxAction(targetUrl, true);
					}
				}
			);
		}
		// Bildergalerie
		if(elem.hasClass('editgall')){
			urlExt = elem.parent('div.editButtons').siblings('div.cc-gallery').attr('class').split('cc-gallery')[0];
		}


		if(locate){
			$.doAjaxAction(targetUrl + urlExt, true);
		}
		return false;
	};
})(jQuery);



// Datepicker
if(typeof($.myAdminDatepicker) != "function"){

	$.extend({

	myAdminDatepicker: function (){

	// Datepicker
	if($('.adminArea .datepicker').length) {
		
		var dayNFull	= $("#daynames").val().split(',');
		var dayNAbb		= $("#daynames").attr('alt').split(',');
		var monthNFull	= $("#monthnames").val().split(',');
		var monthNAbb	= $("#monthnames").attr('alt').split(',');
		var minDate		= $("#mindate").val();
		var maxDate		= $("#maxdate").val();
		var currentText	= $("#currentText").val();
		var closeText	= $("#closeText").val();
		var dateFormat	= cc.adminLang != "en" ? "dd.mm.yy" : "mm/dd/yy";
		var altFormat	= "dd.mm.yy";
		var buttonImage	= cc.httpRoot + "/system/themes/" + cc.adminTheme + "/img/calendar.png";
		
		// Datepicker für Planner
		$('.datepicker:not(.statPeriod)').each(function(){
			$(this).datepicker({
				showOn: "button",
				buttonImage: buttonImage,
				buttonImageOnly: true,
				showButtonPanel: true,
				currentText: '<span class="cc-admin-icons cc-icon-calendar">&nbsp;</span>',
				closeText: '<span class="cc-admin-icons cc-icon-ok">&nbsp;</span>',
				dateFormat: dateFormat,
				altFormat: altFormat,
				altField: $(this).siblings('.altField[name="' + $(this).attr("name") + '"]')[0],
				firstDay: 1,
				dayNames: dayNFull,
				dayNamesMin: dayNAbb,
				dayNamesShort: dayNAbb,
				monthNames: monthNFull,
				monthNamesShort: monthNAbb
			});
		});
		
		// Datepicker für Statistiken
		$('input.statPeriod.datepicker').each(function(){
			$(this).datepicker({
				showOn: "button",
				buttonImage: buttonImage,
				buttonImageOnly: true,
				showButtonPanel: true,
				currentText: currentText,
				closeText: closeText,
				dateFormat: dateFormat,
				altFormat: altFormat,
				altField: $(this).siblings('.altField[name="' + $(this).attr("name") + '"]')[0],
				firstDay: 1,
				changeMonth: true,
				changeYear: true,
				dayNames: dayNFull,
				dayNamesMin: dayNAbb,
				dayNamesShort: dayNAbb,
				monthNames: monthNFull,
				monthNamesShort: monthNAbb,
				minDate: minDate,
				maxDate: maxDate,
				beforeShow: function() {
					setTimeout(function(){
						$('.ui-datepicker').css('z-index', 1000);
					}, 0);
				},
				onSelect:function(dateText, inst){
					$.getWaitBar();
					$(this).val(dateText).closest('form').submit();
				}
			});
		});
		
	}
	}
	});
}
	

// Timepicker
if(typeof($.myTimepicker) != "function"){

	$.extend({

	myTimepicker: function (){

	// Timepicker
	if($(".timepicker").length) {
		var hourVal		= $('.timepicker.start').prev('input[name="timePost"]').val();
		var minVal		= $('.timepicker.start').prev('input[name="timePost"]').attr('data-min');
		var hourValEnd	= $('.timepicker.end').prev('input[name="timePost_end"]').val();
		var minValEnd	= $('.timepicker.end').prev('input[name="timePost_end"]').attr('data-min');
		var minInterval	= parseInt($('.timepicker.start').siblings('input#minInterval').val());
	
		$('.timepicker.start').jtimepicker({
						
				clockIcon: "extLibs/jquery/ui/concise/images/icon_clock_2.gif",
				hourLabel: ln.hourlabel,
				minLabel: ln.minutelabel,
                hourCombo: 'hourcombo_start',
                minCombo: 'mincombo_start',
				hourDefaultValue: hourVal,
				minDefaultValue: minVal,
				minInterval: minInterval,
				secView: false
				
		});
		$('.timepicker.end').jtimepicker({
						
				clockIcon: "extLibs/jquery/ui/concise/images/icon_clock_2.gif",
				hourLabel: ln.hourlabel,
				minLabel: ln.minutelabel,
                hourCombo: 'hourcombo_end',
                minCombo: 'mincombo_end',
				hourDefaultValue: hourValEnd,
				minDefaultValue: minValEnd,
				minInterval: 15,
				secView: false
		});
	}
	}
	});
}	


	
// Ausblenden des nach unten verschieben Buttons beim letzten Element
(function($) {
	$.hideSortButtons = function(){
		if($('ul.contents li.submit').prev().children('.sortcon').slice(0,1).length) {
			$('ul.contents li.submit').prev().children('.sortcon').slice(0,1).css('display', 'none');
		}

		// Ausblenden des nach unten verschieben Buttons bei einzigem Element
		if($('ul.contents li:nth-child(1) .sortcon').slice(0,1).length) { 

			if($('ul.contents li:nth-child(2)').hasClass('submit'))	{ // Falls das zweite Listenelement der Button ist, nach unten-Button ausblenden
				$('ul.contents li:nth-child(1) .sortcon').slice(0,1).css('display', 'none');
			}
		}
	};
})(jQuery);


// Einblenden von Symbolen bei ja/nein Auswahl
(function($) {

	$.setTrueFalseIcons = function(obj){
	
		if($('div.adminArea ul').length) {
							
			if(obj === false){
				obj = $('option[value="false"], option[value="0"]').next('option[value="true"], option[value="1"]').parent('select:not(.noTrueFalse)');
			}else{
				obj.parent('*').children('.check').remove();	
			}

			obj.each(function(index, elem){
			
				if($(elem).hasClass('iconSelect')
				|| $(elem).children('option').length == 2
				){
					if(!$(elem).siblings('.check').length) {
						$(elem).children('option[value="false"]:selected, option[value="0"]:selected').parent('select').parent('*').append('<span class="cc-admin-icons cc-icons cc-icon-cancel check off">&nbsp;</span>');
						$(elem).children('option[value="true"]:selected, option:not([value="false"],[value="0"]):selected').parent('select').parent('*').append('<span class="cc-admin-icons cc-icons cc-icon-ok check on">&nbsp;</span>');
					}
					
					// Bei Auswahländerung
					$(elem).change(function(){				
						$.setTrueFalseIcons($(this));
					});
				}
			});
		}
	};
})(jQuery);


// E-Mail-validierung
function isValidEmailAddress(email) {
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	return pattern.test(email);
}



/*
 * jQuery Caret Range plugin
 * Copyright (c) 2009 Matt Zabriskie
 * Released under the MIT and GPL licenses.
 */
/*
 (function($) {
	$.extend($.fn, {
		caret: function (start, end) {
			var elem = this[0];

			if (elem) {							
				// get caret range
				if (typeof start == "undefined") {
					if (elem.selectionStart) {
						start = elem.selectionStart;
						end = elem.selectionEnd;
					} else {
						if (document.selection) {
							var val = this.attr('value');
							var range = document.selection.createRange().duplicate();
							range.moveEnd("character", val.length);
							start = (range.text == "" ? val.length : val.lastIndexOf(range.text));
	
							range = document.selection.createRange().duplicate();
							range.moveStart("character", -val.length);
							end = range.text.length;
						}
					}
				}
				// set caret range
				else {
					var val = this.attr('value');

					if (typeof start != "number"){ start = -1;}
					if (typeof end != "number"){ end = -1;}
					if (start < 0){ start = 0;}
					if (end > val.length){ end = val.length;}
					if (end < start){ end = start;}
					if (start > end){ start = end;}

					elem.focus();

					if (elem.selectionStart) {
						elem.selectionStart = start;
						elem.selectionEnd = end;
					} else {
						if (document.selection) {
							var range = elem.createTextRange();
							range.collapse(true);
							range.moveStart("character", start);
							range.moveEnd("character", end - start);
							range.select();
						}

					}
				}

				return {start:start, end:end};
			}
		}
	});
})(jQuery);
*/

// Html Title auslesen
(function($) {

	$.getHtmlTitle = function(){

		var title	= "Admin";
		if(document.getElementsByTagName('title').length){
			title	= document.getElementsByTagName('title')[0].text;
		}
		return title;
	};
})(jQuery);



// checkForUpdates
(function($) {

	$.checkForUpdates = function(){
	
		if(!$('.adminTask-main').length
		&& $.cookie('updateCheckDone')
		){
			return false;
		}		
		
		$.ajax({
			url: cc.httpRoot + "/system/access/editPages.php?page=admin&action=updatecheck",
			//async: false, // set false in case of inconvienient callback with json
			success: function(ajax){
				if(ajax == 1 || ajax == "1"){
					$('#updateNav').hide().removeClass('hide').fadeIn();
				}
				return false;
			},
			error: function(){
				console.log("Error during update check.");
				return false;
			}
		});
		return false;
	};
})(jQuery);




// Zusätzliche Methoden/Plugins bestimmen
(function($) {

	$.getInitFunctions = function(){
	
		if(typeof(ccPoolFunctions) != "object"){
			ccPoolFunctions	= new Array();
		}
		
		ccPoolFunctions.push({name: "$.scaleAdminPanels", params: ""});		// Admin panel Größe anpassen
		ccPoolFunctions.push({name: "$.scaleAdminContentWrapper", params: ""});		// Admin contentWrapper Höhe anpassen
		ccPoolFunctions.push({name: "$.fixAdminNotice", params: ""});		// AdminNotice fixieren
		ccPoolFunctions.push({name: "$.fixButtonPanel", params: $('li.submit.change:visible:not(.buttonpanel-nofix, .back)').first()});	// Submitbuttonpanel fixieren
		ccPoolFunctions.push({name: "$.toolTips", params: ""});				// ToolTips
		ccPoolFunctions.push({name: "$.myAdminDatepicker", params: ""});	// Datepicker
		ccPoolFunctions.push({name: "$.myTimepicker", params: ""});			// Timepicker
		ccPoolFunctions.push({name: "$.toggleLists", params: ""});			// Unterlisten ausklappen
		ccPoolFunctions.push({name: "$.setTrueFalseIcons", params: false});	// True-False Icons
		ccPoolFunctions.push({name: "$.runAjaxSetup", params: false});		// runAjaxSetup
		ccPoolFunctions.push({name: "$.pimg", params: head.mobile ? "disable" : "enable"});					// Img-Vorschaufunktion
		ccPoolFunctions.push({name: "$.checkForUpdates", params: ""});		// checkForUpdates
		
		// Mobile ui
		if(head.mobile){
			head.ready("ui", function(){
				head.load({ uimobile: cc.httpRoot + "/extLibs/jquery/ui/jquery.ui.touch-punch.min.js" });
			});
		}
		
		// listMedia-Skript
		if($('.showListBox').length || typeof(tinymce) == "object"){
			
			head.load({ listmediacss: cc.httpRoot + "/system/themes/" + cc.adminTheme + "/css/listMedia.min.css" });
			head.load({ listmedia: cc.httpRoot + "/system/access/js/listMedia.min.js" },
				function(){
					// Ajax popup für die Medienauswahl (ListBox)
					$('body').off("click", '.showListBox');
					$('body').on("click", '.showListBox', function(e){
						e.preventDefault();
						e.stopPropagation();
						$(this).listMedia();
						//return false; // verhindert Anzeige in neuem Fenster
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
	
		$.removeWaitBar();
		
		// Debug-Konsole
		if($('#debugDiv').length) {
			$("#debugContent").tabs();
		}

		// Inhaltsliste (Seite/Inhalte) bei Vorhandensein von Meldungen entsprechende ul ausklappen
		if($('.notice').parents('ul.contents:hidden').not('.elements').length) {
			$('.notice').parents('ul.contents').not('.elements').slideToggle(250);
		}
		
		// Benutzerdetails einblenden bei Fehler
		if($('div.userDetails:has(p.notice)').length){
			$('div.userDetails').show();
			$('input.showUserDetails').prop('checked','checked');
		}

		// Anzeigen einer Galerie-ListBox mit showOnLoad-Klasse
		if($('.showListBox.showOnLoad').length){
			setTimeout(function(){
				$('.showListBox.showOnLoad').click();
			}, 50);
		}
		
		cc.openEditors			= 0;
		cc.conciseChanges		= false;

		return true;
	
	};

})(jQuery);



// Falls head.js geladen
head.ready(function () {

	$(document).ready(function(){

		// Falls FE
		if(!$('body').hasClass('admin')){
			return false;
		}
		
		
		// Prepare History
		var History		= window.History; // Note: We are using a capital H instead of a lower h
		cc.useHistory	= true;

		if(History.enabled) {
			// History.js is disabled for this browser.
			// So remember first page call
			History.replaceState(null, $.getHtmlTitle(), document.location.href);
		}
		
		// Bind to StateChange Event (back, forward)
		History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
			
			var State		= History.getState(); // Note: We are using History.getState() instead of event.state
			var historyUrl	= State.url.split("#")[0];
			
			//History.log(State.data, State.title, State.url);
			if(typeof(State.origin) == "undefined" || (head.browser.ie && head.browser.version < 10)){
				location.assign(historyUrl);
			}else{
				if(State.origin == "bfw"){
					$.loadPageViaAjax(historyUrl);
				}
				return false;
			}
		});
	
		// Funktionsaufruf globaler Funktionen
		$.executeInitFunctions();
	
	});

}); // Ende head ready function
