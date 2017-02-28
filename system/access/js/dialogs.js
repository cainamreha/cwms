// jquery-ui dialog
$(document).ready(function(){
	
	// Aufrufen der Dialog-Box
	$('body').on("dblclick", ".fileName", function(e) {
		e.preventDefault();
		if($(this).siblings('.editButtons-panel').find('.rename').length){
			$(this).siblings('.editButtons-panel').find('.rename.dialog').click();
		}
		return false;
	});
	
	
	// Aufrufen der Dialog-Box
	$('body').on("click", ".dialog", function(e) {
		
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var editTarget;
		var editID;
		var editFolder;
		var dialogType;
		
		// Dialogdaten
		dialogType	= $(this).attr('data-dialog');
		editTarget	= $(this).attr('data-dialogname');
		editID		= $(this).attr('data-dialogid'); // Falls Dateinamenänderung, =Ordnername

		if(typeof($.fn.dialog) == "function"){

			// Dialog erstellen
			$.createDialog(dialogType);
			
			$("#dialog-form-" + dialogType).dialog("open")
				.siblings('.ui-dialog-buttonpane').find("button").addClass('cc-button button')
				.parent('div').children("button:first-child").addClass('change').prepend('<span class="cc-admin-icons cc-icons cc-icon-ok">&nbsp;</span>')
				.parent('div').children("button:last-child").addClass('cancel').prepend('<span class="cc-admin-icons cc-icons cc-icon-cancel">&nbsp;</span>')
				.closest(".ui-dialog").find(".dialogInput").val(editTarget).siblings('.copyID').val(editID)
				.closest(".ui-dialog").find('.cc-admin-icons:not(:first-child)').remove();
		}
	
		// Gallery tags
		if(dialogType == "gallery"){
			head.ready("ui", function(){
			head.ready("tagEditor", function(){
				$.getGalleryTagEditor(editTarget);
			});
			});
		}
		
		return false;
	
	});
	
	// Funktion zum Abrufen von Returntaste
	$("body").on("keypress", ".ui-dialog", function(e) {

		if (e.keyCode === $.ui.keyCode.ENTER) { // Falls Returntaste gedrückt
			//Close dialog and/or submit here...
			var focusElem = $("*:focus");
			// Falls ein Cancel- oder Closebutton den Focus hat, abrechen klicken. Sonst Ok-button klicken (Ajaxfunktion aufrufen)
			if(!(focusElem.hasClass("ui-dialog-titlebar-close") || focusElem.hasClass("cancel"))){
				$( this ).find('.ui-dialog-buttonpane').find('button:first-child').click();
			}else{
				$( this ).find('.ui-dialog-buttonpane').find('button:last-child').click();
			}
			return false;
		}
	});

});


	
// jquery-ui dialog
(function($){

	// Category-/file-Namen ändern (dialog popup), falls Dialog-Formular vorhanden
	$.createDialog = function(formExt) {
	
		if(!$("#dialog-form-" + formExt).length || !$(".dialog").length) {
			return false;
		}
		
		var newNameInput,
			allFields,
			tips,
			buttonLabels,
			okLabel,
			cancelLabel,
			dialogButtons;
		
		function getDialogVars() {
			newNameInput	= $( "#newname-" + formExt ),
			allFields		= $( [] ).add( newNameInput ),
			tips			= $( ".validateTips" );
		
			// Dialog Buttons
			buttonLabels	= $('#buttonLabels-' + formExt).val().split("<>"); // Sprachbausteine
			okLabel			= buttonLabels[0];
			cancelLabel		= buttonLabels[1];
			dialogButtons	= new Array();
		}
		getDialogVars();
		
		function updateTips( t ) {
			tips
				.text( t )
				/*.addClass( "ui-state-highlight" );
			setTimeout(function() {
				tips.removeClass( "ui-state-highlight", 1500 );
			}, 500 );
			*/
		}
	
		// Galerienamen überprüfen (Länge)
		function checkLength( o, n, min, max, notice ) {
			if ( o.val().length > max || o.val().length < min ) {
				//o.addClass( "ui-state-error" );
				if ( o.val().length > max ){
					tip = notice[1];
				}else{
					tip = notice[0];
				}
				updateTips( tip );
				return false;
			} else {
				return true;
			}
		}
	
		// Galerienamen überprüfen (Zeichen)
		function checkRegexp( o, regexp, n ) {
			if ( !( regexp.exec( o.val() ) ) ) {
				//o.addClass( "ui-state-error" );
				updateTips( n );
				return false;
			} else {
				return true;
			}
		}
		
		
		// Dialog initiieren
		$( "#dialog-form-" + formExt ).dialog({
			autoOpen: false,
			minHeight: Math.max(parseInt(($(window).width()) > 1200 ? 350 : 300), 300),
			minWidth: Math.max(parseInt(($(window).width()) > 1200 ? 450 : 350), 350),
			position: { my: "center", at: "center center-16%", of: window },
			modal: true,
			zIndex: 9500,
			open: function() {
					$( ".validateTips" ).html("");
				  },
			buttons: {
				Ok: function getOkButton() {
										
					getDialogVars();
					
					var phrases = $('#phrases-' + formExt).val().split("<>"); // Sprachbausteine
					var okButton = phrases[1];		
			
					var folderName = "";	
					var dbUpdate = 0;
					
					var bValid = true;
					allFields.removeClass( "ui-state-error" );
			
					bValid = bValid && checkLength( newNameInput, "newname-" + formExt, 1, 64, phrases );
					
					// Falls eine Datei umbenannt werden soll
					if($('#foldername-' + formExt).length){
						
						folderName = $('#foldername-' + formExt).val();
						bValid = bValid && checkRegexp( newNameInput, /^([0-9a-z._-])+$/i, phrases[2].split("<br />")[0]);				
						bValid = bValid && checkRegexp( newNameInput, /^[0-9a-z]([0-9a-z._-]+)$/i, phrases[2].split("<br />")[0]);
					
						if($('#dbUpdate-' + formExt).length && $('#dbUpdate-' + formExt).is(':checked')){
							dbUpdate = 1;							
						}
						
					// Andernfalls soll eine Galerie umbenannt werden
					}else{
						bValid = bValid && checkRegexp( newNameInput, /^([0-9a-z _-])+$/i, phrases[2].split("<br />")[0]);			
						bValid = bValid && checkRegexp( newNameInput, /^[0-9a-z]([0-9a-z _-]+)$/i, phrases[2].split("<br />")[0]);
					}
			
					if ( bValid ) {
					
							var editName		= $('#oldname-' + formExt).val();
							var newName			= newNameInput.val().replace(/ /g, "_");
							var dialogObject	= $(this);
							var notGallery		= dialogObject.hasClass('copy'); // Falls die Klasse copy vorhanden ist, handelt es sich nicht um den Dialog zum Ändern des Galerienamens
							var tags			= "";
							
							// Falls der Name geändert wurde
							if(newName == editName && notGallery)
							{
								$( this ).dialog( "close" );
								return false;
							}
								
							var targetURL = $('#scriptpath-' + formExt).val();
							targetURL += folderName + "&editname=" + editName + "&newname=" + newName + "&dbupdate=" + dbUpdate;
							
							// Falls gallery
							if(!notGallery){
								tags = $('input[name="gall_tags"]').val();
								targetURL += '&tags=' + tags;
							}
							
							$.ajax({
								url: targetURL,
								contentType: 'application/json; charset=utf-8',
								dataType: "json",
								success: function(ajax){
	
									if(typeof(ajax) != "object"){
										updateTips(phrases[4]); // Fehler
										return false;
									}

									var result = ajax.result;
									
									if(result == "1") {
									
										var newHrefEdit	= "";
										var newHrefDel	= "";
										var editListN	= $('#editlist-' + editName);
										var delListN	= $('#dellist-' + editName);
										var className	= editListN.attr('class').split("date-"); // Anfangsbuchstaben ermitteln
										var iniLetter	= newName.charAt(0).toUpperCase();
										var iconTag		= editListN.find('.openList').children('.cc-admin-icons').clone();
										
										newHrefEdit	= editListN.find('.showListBox').attr('data-url').replace("=gallery&gal=" + editName, "=gallery&gal=" + newName); // Neuen alten Namen setzen
										newHrefDel	= delListN.find('.showListBox').attr('data-url').replace("action=del&type=gallery&gal=" + editName, "action=del&type=gallery&gal=" + newName); // Neuen alten Namen setzen
										
										$('#oldname-' + formExt).val(newName); // Neuen alten Namen setzen
										editListN.find('.showListBox').attr('data-url', newHrefEdit); // Neuen alten Namen setzen
										editListN.find('.openList').val(newName).html(newName).prepend(iconTag.clone()); // Neuen alten Namen setzen
										editListN.attr('class', 'listItem gallListItem ' + iniLetter + ' date-' + className[1]); // Neuen Anfangs-Buchstaben setzen
										editListN.find('.changeGallName').attr('data-dialogname', newName); // Neuen alten Namen setzen
										editListN.attr('id', 'editlist-' + newName); // Neuen alten Namen setzen
										editListN.find('[data-content="tags"]').html(tags); // Neuen alten Namen setzen
										delListN.find('.showListBox').attr('data-url', newHrefDel); // Neuen alten Namen setzen
										delListN.find('.openList').val(newName).html(newName).prepend(iconTag.clone()); // Neuen alten Namen setzen
										delListN.attr('class', 'listItem gallListItem ' + iniLetter + ' date-' + className[1]); // Neuen Anfangs-Buchstaben setzen
										delListN.find('.changeGallName').attr('data-dialogname', newName); // Neuen alten Namen setzen
										delListN.attr('id', 'dellist-' + newName); // Neuen alten Namen setzen
										delListN.find('[data-content="tags"]').html(tags); // Neuen alten Namen setzen
										
										dialogObject.dialog( "close" );
										
										return false;
										
									}
									
									if(result == "-1") { // Galerie/Formular/Datei existiert bereits
										updateTips(phrases[3]);
										return false;
									}
									
									if(result == "2"){ // Datei wurde umbenannt
										var listBox			= $('.changeFileName').closest('.listBox');
										var mediaList		= $('*[data-id="' + listBox.attr('data-id') + '"]');
										
										dialogObject.dialog( "close" );
										$.removeListBox();
										$.removeWaitBar();
										mediaList.find('.showListBox[data-type="gallery"]:first').click();
										return false;
									}
									
									dialogObject.dialog( "close" );
									
									if(result != "1111111111111"){
										jAlert(result, conciseCMS.ln.alerttitle);
										return false;
									}
									
									$.doAjaxAction(cc.httpRoot + '/admin?task=forms&tab=1', true);											
									return false;
									
									// Sonst Fehler
									updateTips(phrases[4]);
									
									return false;
								}
							});
				
					}
				},
				abbrechen: function getCancelButton() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				allFields.removeClass( "ui-state-error" ).val( "" );
			}
		});

	}; // Ende createDialog
	
	
	// Gallery tags
	$.getGalleryTagEditor = function(gallName){

		var fetchTagsScript = cc.httpRoot + "/system/access/editGalleries.php?action=galltags&type=gallery&gal=" + gallName;
		var currTags		= [];
		
		$.ajax({
			url: fetchTagsScript,
			contentType: 'application/json; charset=utf-8',
			dataType: "json",
			success: function(ajax){
			
				currTags	= ajax.tags.split(",");

				$('#gall_tags').tagEditor("destroy").tagEditor({
					maxLength: 2048,
					initialTags: currTags,
					forceLowercase: false,
					delimiter: ",;\n",
					autocomplete: {
						position: { collision: "flip" }, // automatic menu position up/down
						source: cc.httpRoot + "/system/access/editGalleries.php?action=allgalltags&type=gallery&gal=" + gallName,
						delay: 0,
						minLength: 0,
						create: function( event, ui ) { }
					},
					onChange: function(field, editor, tags){
					}
				});
			}
		});
	};

})(jQuery);
