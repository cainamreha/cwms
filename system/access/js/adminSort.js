head.ready(function() {
	
	$(document).ready(function() {

		// Ggf.Sortable deaktivieren (Galerie-Edit)
		if($.fn.sortable
		&& $("#sortableGallery").closest("div.mediaList").length
		){
			$("#sortableGallery").sortable({disabled:true});
		}
	});
});


var itemH = "";

// Sortierung von Inhaltselementen
(function($) {

	$.sortableContents = function(){
	
		var cancelled	= false;
		
		$("#sortableContents").sortable({  
			placeholder: "ui-state-highlight",
			delay:100,
			items: "> .contentElement",
			handle: "span.type",
			containment: "parent",
			tolerance:"pointer",
			opacity: 0.85,
			create: function(event, ui){
				//$(this).height(parseInt($(this).height())); // fix scroll jumps bug(?)
			},
			helper: function(event, ui){
				var sHelper =  $(ui).clone();
				sHelper.addClass("ui-widget ui-sortable-helper").css("position","absolute");
				return sHelper.get(0);
			},
			start: function(event, ui){
				itemH		= parseInt(ui.item.height());
				cancelled	= false;
				if(cc.conciseChanges || $(this).children('li').children('.elements:visible').length){
					cancelled	= true;
				}
			},
			beforeStop: function(event, ui){
				var minSortNo	= parseInt($(this).attr('data-sort-min'));
				var newSortId	= parseInt(ui.placeholder.index()) + minSortNo-1 -1;
				if($('.showPreviousElements').length){
					newSortId--;
				}
				ui.item.attr("data-sortid", newSortId);
			},
			stop: function(event, ui){
				if(cancelled){
					$(this).sortable('cancel');
					ui.item.css('height',itemH + 'px');
					jAlert(ln.alertsort, ln.alerttitle);
				}
			},
			update: function(event, ui){
				if(cancelled){
					return false;
				}
				var oldSortId = parseInt(ui.item.attr("data-sortidold"));
				var newSortId = parseInt(ui.item.attr("data-sortid"));
				// Falls die alte sortID kleiner als die neue war, die neue um 1 erhöhen
				if(oldSortId < newSortId){
					newSortId++;				
				}				
				var sortObj		= $(this);
				var targetUrl	= sortObj.attr("data-url");
				targetUrl += "&con="+oldSortId+"&pastecon="+newSortId;
				
				var conElem = $('div#adminContent');
				
				$.getWaitBar();
				sortObj.sortable('destroy');
				
				$.ajax({
					url: targetUrl,
					cache: false,
					success: function(ajax){
					
						// Inhalte ersetzen
						$.ajaxReplace(ajax);
					}
				});
			}
		});

		$("#sortableContents").enableSelection();
		
		return this;
	};	
})(jQuery);



// Sortierung von Cards
(function($) {

	$.sortableCards = function(cardsID){
		
		var cardsDom	= cardsID ? cardsID : '.sortableCards';
		var cardEditors	= "";
		var cardEditor	= "";
		
		$(cardsDom).sortable({	placeholder: "ui-state-highlight",
										handle: ".listEntryHeader .sortCard",
										cancel: '',
										delay:100,
										items: "> li.cardEntry",
										containment: "parent",
										tolerance:"pointer",
										opacity: 0.85,
										helper: function(event, ui){
											var sHelper =  $(ui).clone();
											sHelper.addClass("ui-widget ui-sortable-helper").css("position","absolute");
											return sHelper.get(0);
										},
										start: function(event, ui){
											//$(this).css('height',$(this).height()); // fix scroll jumps bug(?)
											if(typeof(tinymce) != "undefined"){
												// save conent
												cardEditors		= $(this).find('textarea.cc-editor-add');
												cardEditor		= ui.item.find('textarea.cc-editor-add');
												var cardIDCur	= cardEditor.attr('id');
												
												cardEditors.each(function(i,e){
													var cardID		= $(e).attr('id');
													if(typeof(tinymce.editors[cardID]) == "undefined"){
														return false;
													}
													tinymce.editors[cardID].save();
													tinymce.remove('#' + cardID);
												});
											}
										},
										beforeStop: function(event, ui){
										},
										update: function(event, ui){

											var sortObj = $(this);

											sortObj.children('li').each(function(idx, ele){
												idx++;
												var regex1	= "[[0-9]+]";
												var regex2	= " [0-9]+";
													regex1	= new RegExp(regex1, "g");
													regex2	= new RegExp(regex2, "g");
												$(this).find('.listEntryHeader, label').each(function(index, domEle){
													$(domEle).html($(domEle).html().replace(regex2, (" "+idx)));
												});
												$(this).find('input, textarea, select').each(function(index, domEle){
													$(domEle).attr('data-index', idx).attr('name', $(domEle).attr('name').replace(regex1, "[" + idx + "]"));
												});
												
											});
											cardEditors.each(function(i,e){
												var cardID		= $(e).attr('id');
												tinymce.EditorManager.execCommand('mceAddEditor',true, cardID);
												var edi	= tinymce.editors[cardID];
												edi.hide();
												$.toggleEditor(edi);
											});
											sortObj.css('height','auto');
										},
										deactivate: function(event, ui){
											//$.myTinyMCE(); // for tinymce 3											
										}
									});
		
		$(".sortableCards").enableSelection();
		
		return this;
	};	
})(jQuery);


// Sortierung von Tabs
(function($) {

	$.sortableTabs = function(tabsID){
		
		var tabsDom		= tabsID ? tabsID : '.sortableTabs';
		var tabEditors	= "";
		var tabEditor	= "";
		
		$(tabsDom).sortable({	placeholder: "ui-state-highlight",
										handle: ".listEntryHeader",
										delay:100,
										items: "> li.tabEntry",
										tolerance:"pointer",
										containment: "parent",
										opacity: 0.85,
										helper: function(event, ui){
											var sHelper =  $(ui).clone();
											sHelper.addClass("ui-widget ui-sortable-helper").css("position","absolute");
											return sHelper.get(0);
										},
										start: function(event, ui){
											if(typeof(tinymce) != "undefined"){
												// save conent
												tabEditors		= $(this).find('textarea.tabContent');
												tabEditor		= ui.item.find('textarea.tabContent');
												var tabIDCur	= tabEditor.attr('id');
												
												tabEditors.each(function(i,e){
													var tabID		= $(e).attr('id');
													if(typeof(tinymce.editors[tabID]) == "undefined"){
														return false;
													}
													tinymce.editors[tabID].save();
													if(tabID == tabIDCur){
														tinymce.remove('#' + tabIDCur);
													}
												});
											}
										},
										beforeStop: function(event, ui){
										},
										update: function(event, ui){

											var sortObj = $(this);

											sortObj.children('li').each(function(idx, ele){
												idx++;
												var regex1	= "[[0-9]+]";
												var regex2	= " [0-9]+";
													regex1	= new RegExp(regex1, "g");
													regex2	= new RegExp(regex2, "g");
												$(this).children('.listEntryHeader, label').each(function(index, domEle){
													$(domEle).html($(domEle).html().replace(regex2, (" "+idx)));
												});
												$(this).children('input, textarea').each(function(index, domEle){
													$(domEle).attr('data-index', idx).attr('name', $(domEle).attr('name').replace(regex1, "[" + idx + "]"));
												});
												
											});
											
											tinymce.EditorManager.execCommand('mceAddEditor',true, tabEditor.attr('id'));
											var edi	= tinymce.editors[tabEditor.attr('id')];
											edi.hide();
											$.toggleEditor(edi);
										},
										deactivate: function(event, ui){
											//$.myTinyMCE(); // for tinymce 3											
										}
									});
		
		$(".sortableTabs").enableSelection();
		
		return this;
	};	
})(jQuery);
	
	
// Sortierung von Sprachen im Sprachmenu
(function($) {

	$.sortableLangs = function(){

		$("#sortableLangs").sortable({  placeholder: "ui-state-highlight",
									delay:100,
									tolerance:"pointer",
									opacity: 0.85,
									beforeStop: function(event, ui){
										var newSortId = ui.placeholder.index();
										ui.item.attr("data-sortid", newSortId);
									},
									update: function(event, ui){
										var sortLang	= ui.item.attr("id");
										var oldSortId	= ui.item.attr("data-sortidold");
										var newSortId	= ui.item.attr("data-sortid");
										var sortObj		= $(this);
										var sortItem	= ui.item;
										var targetUrl	= sortObj.attr("data-url");
										targetUrl 		+= "&sortlang="+sortLang+"&oldsortid="+oldSortId+"&newsortid="+newSortId;
										
										$.ajax({
											url: targetUrl,
											cache: false,
											success: function(ajax){
												if(ajax == "1") {
													// Sortierung vorübergehend abschalten
													$("#sortableLangs").sortable('disable');
													// Neunummerierung
													$('ul#sortableLangs li').each(function(index, domEle){
														var n = parseInt(index+1);
														$(domEle).attr('data-sortid',n).attr('data-sortidold',n);
														// Wenn letztes Element, Sortierung wieder anschalten
														if(n == $(this).parent('ul').children('li').length){
															sortItem.animate({'opacity':'0.5'}, 100).delay(50).animate({'opacity':'1'}, 250);
															$("#sortableLangs").sortable('enable');
														}
													});
												}else{
													// Seite neu laden mit url aus editLangs(ajax);
													if(ajax.match("http").index === 0){
														document.location.href = ajax;
													}else{
														document.location.reload();
													}
												}
											}
										});
									}
								  });
		
		$("#sortableLangs").enableSelection();

		return this;
		
	};	
})(jQuery);


// Sortierung von Artikeln/Nachrichten/Terminen
(function($) {

	$.sortableData = function(){

		$("#sortableData, #sortableDataCat").sortable(
									{	items: '.listEntry' },
									{ 	placeholder: "ui-state-highlight",
									delay:100,
									tolerance:"pointer",
									containment: "parent",
									opacity: 0.85,
									helper: function(event, ui){
										var sHelper =  $(ui).clone();
										sHelper.addClass("ui-widget ui-sortable-helper").css("position","absolute");
										return sHelper.get(0);
									},
									beforeStop: function(event, ui){
										var newSortId = ui.placeholder.index();
										ui.item.attr("data-sortid", newSortId);
									},
									update: function(event, ui){
										
										var dataId		= "";
										var oldSortId	= parseInt(ui.item.attr("data-sortidold"));
										var newSortId	= parseInt(ui.item.attr("data-sortid"));										
										// Falls die alte sortID kleiner als die neue war, die neue um 1 erhöhen
										if(oldSortId < newSortId){
											newSortId=newSortId;
										}
										var sortObj = $(this);
										var sortItem = ui.item;
										var targetUrl = sortObj.attr("data-url");
										targetUrl += "&sortid="+oldSortId+"&newsortid="+newSortId;
										
										// Falls Daten und keine Kategorien sortiert werden, id hinzufügen
										if($(this).attr('id') == "sortableData"){
											dataId		= ui.item.attr("data-id");
											targetUrl += "&id="+dataId;
										}else{
											dataId		= ui.item.attr("data-catid");
											targetUrl += "&cat="+dataId;
										}
										
										$.ajax({
											url: targetUrl,
											cache: false,
											success: function(ajax){
												// Seite neu laden mit url aus editContents(ajax);
												//document.location.href = ajax;
												sortObj.sortable('disable');
												
												$.getWaitBar();
												
												sortObj.find('.listEntry').each(function (index, domEle) {
													
													var newItemId = parseInt(index+1);
													var sortClass	= $(domEle).attr('class').split("sortid-");
													
													$(domEle).attr('class',sortClass[0] + 'sortid-' + newItemId);
													$(domEle).attr('data-sortid', newItemId);
													$(domEle).attr('data-sortidold', newItemId);
													$(domEle).find('.sortcon').remove();
													
													if(newItemId == $(domEle).parent().children().length){
														$('.loadingDiv').remove();
														$('.dimDiv').remove();
														sortItem.animate({'opacity':'0.5'}, 100).delay(50).animate({'opacity':'1'}, 250);
														sortObj.sortable('enable');
													}
												});
											}
										});
									}
								  });
		
		$("#sortableData").enableSelection();
		
		return this;
	};	
})(jQuery);



// Sortierung von Datenobjekten (Modulebereich)
(function($) {

	$.sortableObjects = function(){

		$("#sortableObjects").sortable({  placeholder: "ui-state-highlight",
									delay:100,
									handle: "span.type",
									tolerance:"pointer",
									items: "li:not(.newobj)", // schließt das Listenelement für neues Objekt aus
									opacity: 0.85,
									helper: function(event, ui){
										var sHelper =  $(ui).clone();
										sHelper.addClass("ui-widget ui-sortable-helper").css("position","absolute");
										return sHelper.get(0);
									},
									beforeStop: function(event, ui){
										var newSortId = ui.placeholder.index();
										ui.item.attr("data-sortid", newSortId);
									},
									update: function(event, ui){
										var oldSortId = ui.item.attr("data-sortidold");
										var newSortId = ui.item.attr("data-sortid");
										var sortObj = $(this);
										var sortItem = ui.item;
										var targetUrl = sortObj.attr("data-url");
										targetUrl += "&sortid="+oldSortId+"&newsortid="+newSortId;
										$.ajax({
											url: targetUrl,
											cache: false,
											success: function(ajax){
												
												sortObj.sortable('disable');
												//document.location.href = "admin?task=modules";

												/* Falls Fehler */
												if(ajax != "11111"){
													jAlert(ln.formtaberror+"\n\r\n\r"+ajax, ln.alerttitle);
												}else{
												
													sortObj.children('li').not('.newobj').each(function (index, domEle) {
														
														var item		= $(domEle);
														var newItemId	= parseInt(index+1);
														
														item.attr('id','object-' + newItemId);
														item.attr('data-sortid',newItemId);
														item.attr('data-sortidold',newItemId);
														
														// ObjektNr aktualisieren
														var headerEle	= item.children('span.objectToggle').html().replace(/ - [0-9]+/g, ' - ' + newItemId);
														item.children('span.objectToggle').html(headerEle);
																										
														// Feldnamen (Array[]) aktualisieren
														var objectFields = item.children('div.objects').html().replace(/[[0-9]+]/g, '[' + newItemId + ']');
														item.children('div.objects').html(objectFields);
													});
													
													sortItem.animate({'opacity':'0.5'}, 100).delay(50).animate({'opacity':'1'}, 250);
													sortObj.sortable('enable');
												}
											}
										});
									}
								  });
		
			
		$("#sortableObjects").enableSelection();
		
		return this;

	};	
})(jQuery);

	
// Sortierung von Galleriebildern
(function($) {

	$.sortableGallery = function(elem){

		var editors	= "";
		var editor	= "";

		elem.sortable({  placeholder: "ui-state-highlight",
									items: "> li",
									delay:100,
									tolerance:"pointer",
									opacity: 0.85,
									start: function(event, ui){
										if(typeof(tinymce) != "undefined"){
										
										editor	= ui.item.find('textarea.galleryEditor');
										
											var edIDCur	= editor.attr('id');
											
											// save conent
											editors	= $(this).find('textarea.galleryEditor');
											editors.each(function(i,e){
												var edID	= $(e).attr('id');
												if(typeof(tinymce.editors[edID]) == "undefined"){
													return false;
												}
												tinymce.editors[edID].save();
												if(edID == edIDCur){
													tinymce.remove('#' + edIDCur);
												}
											});
										}
										$('div#container').off("mouseenter", "img.preview");
										$('.pimg').fadeOut(200, function(){
											$(this).remove();
											$.pimg("disable");
										});
									},
									sort: function(event, ui){
										$('.pimg').remove();
										$.pimg("disable");
									},
									beforeStop: function(event, ui){
										var newSortId = ui.placeholder.index();
										ui.item.attr("data-newsortid", newSortId);
									},
									stop: function(event, ui){
										$.pimg("enable");
									},
									update: function(event, ui){
										var sortObj		= $(this);
										var itemId		= ui.item.attr("data-id");
										var oldSortId	= ui.item.attr("data-sortid");
										var newSortId	= ui.item.attr("data-newsortid");
										var targetUrl	= sortObj.attr("data-url");
										var openButton	= sortObj.closest("div.mediaList").children(".showListBox");
										targetUrl += "&sortIdOld="+oldSortId+"&sortIdNew="+newSortId+"&item="+itemId;
										
										$.getWaitBar();
										
										sortObj.sortable('disable');
										
										$.ajax({
											url: targetUrl,
											cache: false,
											success: function(ajax){
											
												if(ajax == "0"){
													sortObj.sortable( "cancel" );
													$.removeWaitBar(false);
													jAlert(ln.dberror, ln.alerttitle);
												}else{
													var sortItems	= sortObj.children('.sortItem');
													var elemCount	= sortItems.length;
													sortItems.each(function (index, domEle){
														// domEle == this
														var newSortId	= parseInt(index+1);
														var markDom		= $(domEle).children('.gallentry').children('.previewBox').children('markImage');
														markDom.attr('for','selImg-' + newSortId);
														markDom.children('input').attr('id','selImg-' + newSortId).attr('name', 'img_gall[' + newSortId + ']');
														$(domEle).attr('data-sortid', newSortId);
														$(domEle).attr('data-newsortid',newSortId);
														if (!--elemCount){
															sortObj.sortable('enable');
															$.removeWaitBar(false);
														}
													});
												}
										
												head.ready(function(){
													tinymce.EditorManager.execCommand('mceAddEditor',true, editor.attr('id'));
													var edi	= tinymce.editors[editor.attr('id')];
													if(typeof(edi) != "undefined"){
														edi.hide();
														$.toggleEditor(edi);
													}
												});
											
											}
										}).done(function(){
											/* ListBox updaten */
											if(elem.closest("div.mediaList").length){
												$.setListView(elem.closest("div.mediaList"));
												elem.enableSelection();
												return false;
											}	
										});
									}
		});
		
		elem.enableSelection();

		$.setListView(elem.closest("div.mediaList"));		
		
		return this;

	};	
})(jQuery);

	
// Sortierung von Formularelementen
(function($) {

	$.sortableForm = function(elem){

		var editors		= "";
		var editorsEle	= "";
	
		if(typeof(elem) != "object"){
			elem	= $('body').find(elem);
		}

		elem.sortable({  placeholder: "ui-state-highlight",
									items: "> li",
									delay:100,
									containment: "parent",
									tolerance:"pointer",
									opacity: 0.85,
									helper: function(event, ui){
										var sHelper =  $(ui).clone();
										sHelper.addClass("ui-widget ui-sortable-helper").css("position","absolute");
										return sHelper.get(0);
									},
									start: function(event, ui){
										if(typeof(tinymce) != "undefined"){
											// save conent
											editors	= $(this).find('textarea:not(.noTinyMCE)');
											editors.each(function(i,e){
												var edID	= $(e).attr('id');
												if(typeof(tinymce.editors[edID]) == "undefined"){
													return false;
												}
												tinymce.editors[edID].save();
											});
											editorsEle	= ui.item.find('textarea:not(.noTinyMCE)');

											editorsEle.each(function(i,e){
												tinymce.remove('#' + $(e).attr('id'));
											});
										}
										$('div#container').off("mouseenter", "img.preview");
										$('.pimg').fadeOut(200, function(){
											$(this).remove();
											$.pimg("disable");
										});
									},
									sort: function(event, ui){
										$('.pimg').remove();
										$.pimg("disable");
									},
									beforeStop: function(event, ui){
										var newSortId = ui.placeholder.index();
										ui.item.attr("data-newsortid", newSortId);
									},
									stop: function(event, ui){
										$.pimg("enable");
									},
									update: function(event, ui){
										var sortObj		= $(this);
										var itemId		= ui.item.attr("data-id");
										var oldSortId	= ui.item.attr("data-sortid");
										var newSortId	= ui.item.attr("data-newsortid");
										var targetUrl	= sortObj.attr("data-url");
										var openButton	= sortObj.closest("div.mediaList").children(".showListBox");
										targetUrl += "&sortIdOld="+oldSortId+"&sortIdNew="+newSortId+"&item="+itemId;
										
										$.getWaitBar();
										
										sortObj.sortable('disable');
										
										$.ajax({
											url: targetUrl,
											cache: false,
											success: function(ajax){
										
												/* Falls Fehler */
												if(ajax != "111"){
													jAlert(ln.formtaberror+ajax, ln.alerttitle);
												}
												sortObj.children('li').each(function (index, domEle) {
													
													var item		= $(domEle);
													
													var newItemId		= parseInt(index+1);
													var fieldHeader		= item.find('.formFieldHeader');
													item.attr('id','field-' + newItemId);
													item.attr('data-sortid', newItemId);
													item.attr('data-newsortid',newItemId);
													var headerEle		= fieldHeader.children('span.fieldNumber').html().replace(/#[0-9]+/g, '#' + newItemId);
													fieldHeader.children('span.fieldNumber').html(headerEle);
													var altTextDel		= fieldHeader.find('.delfield').attr('data-url').replace(/fieldid=[0-9]+&/g, 'fieldid=' + newItemId + '&');
													fieldHeader.find('.delfield').attr('data-url',altTextDel);
													
													// update fieldpos
													var newTypeSel		= item.find('select.insertFormField');
													var targetUrl		= newTypeSel.attr("data-url");
													newTypeSel.attr("data-url", targetUrl.replace(/fieldpos=[0-9]+/, "fieldpos=" + newItemId));
													
													// update name attr
													var formElems		= item.find('*[name]');
													var formElemCount	= formElems.length;
													
													formElems.each(function (idx, fieldEle) {
														var newName		= $(fieldEle).attr('name').replace(/\[[0-9]+\]/g, '[' + newItemId + ']');
														$(fieldEle).attr('name', newName);
														if (!--formElemCount){
										
															sortObj.sortable('enable');
															$.removeWaitBar();
														}
													});
													
													head.ready(function(){
														editorsEle		= item.find('textarea:not(.noTinyMCE)');
														editorsEle.each(function(i,e){
															var edID	= $(e).attr('id');
															tinymce.EditorManager.execCommand('mceAddEditor',true, edID);
															var edi		= tinymce.editors[edID];
															edi.hide();
															$.toggleEditor(edi);
														});
													});
													
												});
											}
										});
									}
		});
		
		elem.enableSelection();
		
		return this;

	};	
})(jQuery);


// Listenansicht für Galerie-ListBox
(function($) {

	$.setListView = function(liObj){
		
		var listType	= 2;
		var gallList	= liObj.find('ul#sortableGallery');
		var gallSubmit	= liObj.find('li.submit');
		
		if(typeof($.cookie("gallList")) != "undefinded"
		&& $.cookie("gallList") !== null
		&& $.cookie("gallList") != "null"
		){
			listType = $.cookie("gallList");
		}else{
			gallList.removeClass('gallViewImage gallViewSmall');
			gallSubmit.show();
		}
		
		if(listType == 1){
			gallList.addClass('gallViewSmall');
			gallList.removeClass('gallViewImage');
		}
		if(listType == 2){
			gallList.addClass('gallViewImage');
			gallList.removeClass('gallViewSmall');
			gallSubmit.hide();
		}
		
		
		// Verkleinerte Galerieansicht
		$('.gallViewSmall .sortItem').bind("click", function(){
			$('.gallViewSmall .expanded').not(this).removeClass('expanded');
			$(this).not('.expanded').hide().addClass('expanded').slideDown(300);
		});
		
		
		// Ansichtsmodus für Galleriebilder
		$('.toggleGallView').bind("click", function(){
		
			// Auslösen von Button zur Anzeige einer Listenansicht
			var liObj = $('ul#sortableGallery');
			
			// Editor ausblenden
			if(typeof(tinymce) == "object"){
				for(edId in tinymce.editors){
					tinymce.editors[edId].hide();
					cc.openEditors--;
					tinymce.editors[edId].isHidden(function(){
						if(cc.openEditors == 0){
							liObj.sortable({enabled:true});
						}
					});
				}
			}
			
			liObj.fadeOut(300,function(){

				if(liObj.hasClass('gallViewSmall')) {
					liObj.removeClass('gallViewSmall');
					liObj.addClass('gallViewImage');
					listType = 2;
					$.cookie("gallList", listType, { expires: 7, path: '/' });
					liObj.closest("div.mediaList").find('li.submit').hide();
				}else{
				if(liObj.hasClass('gallViewImage')) {
					liObj.removeClass('gallViewImage');
					listType = 0;
					$.cookie("gallList", listType, { expires: 7, path: '/' });
					liObj.closest("div.mediaList").find('li.submit').show();
				}
				else {
					liObj.addClass('gallViewSmall');
					listType = 1;
					$.cookie("gallList", listType, { expires: 7, path: '/' });
					liObj.closest("div.mediaList").find('li.submit').show();
				}
				}
				liObj.fadeIn(300);
			});
			return false;
		});
		
		return false;
	
	};	
})(jQuery);
