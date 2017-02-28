// Editor toggeln
(function($){

	$.getTinyMCE_Comments = function(elemID){
				
		tinyMCE.init({
			entity_encoding : "raw",
			inline : true,
			hidden_input: false,
			selector : "#" + elemID,
			relative_urls: false,
			document_base_url : cc.httpRoot + "/",
			convert_urls : false,
			remove_script_host : false,
			language : typeof(cc.adminLang) != "undefined" ? cc.adminLang : cc.editLang,
			theme : "modern",
			skin : "concise",
			width : "calc(100% - 2px)",
			insertdatetime_formats: ["%d.%m.%Y", "%H:%M Uhr", "%H:%M:%S", "%m-%d-%Y", "%D", "%H:%M:%S %p"],
			visualblocks_default_state: false,
			indentation : '18px',
			schema: "html5",
			extended_valid_elements: "span[id|class|style|title|aria-hidden]",
			plugins : ["bbcode advlist autolink link lists charmap emoticons print preview hr anchor","searchreplace wordcount visualblocks visualchars codemagic insertdatetime nonbreaking","save contextmenu directionality paste textcolor"],
			menubar: "format edit view insert tools table",
			toolbar1: "undo redo | styleselect | bold italic underline strikethrough | emoticons | link | codemagic",
			style_formats: [
				{title: 'Blocks', items: [
					{title: "Blockquote", format: "blockquote"},
					{title: "Pre", format: "pre"}
				]}
			],
			/* bbcode used instead
			formats: {
				alignleft: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'left'},
				aligncenter: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'center'},
				alignright: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'right'},
				alignfull: {selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'full'},
				bold: {inline: 'span', 'classes': 'bold'},
				italic: {inline: 'span', 'classes': 'italic'},
				underline: {inline: 'span', 'classes': 'underline', exact: true},
				strikethrough: {inline: 'del'},
				customformat: {inline: 'span', styles: {color: '#00ff00', fontSize: '20px'}, attributes: {title: 'My custom format'}}
			},
			*/
			visualblocks_default_state: false,
			end_container_on_empty_block: true,
			image_list : cc.httpRoot + "/system/access/tmce4.imgList.php",
			image_class_list: [
					{title: 'None', value: ''},
					{title: 'Image with Frame', value: 'imgFrame'},
					{title: 'Image without Frame', value: 'imgNoFrame'}
			],
			link_list : cc.httpRoot + "/system/access/tmce4.linkList.php",
			link_class_list: [
					{title: 'None', value: ''},
					{title: 'Link', value: 'link'},
					{title: 'External link', value: 'extLink'},
					{title: 'Button link', value: 'formbutton button btn btn-default'}
			],
			init_instance_callback : function(edId){
									var inst = tinyMCE.get(edId.id);
									var repl = inst.getContent().replace(/{#root_img}/g, cc.imageDir).replace(/{#root}/g, cc.httpRoot);
									inst.execCommand("mceSetContent",true,repl);
									$.getCommentSubmitPanel(elemID);
									},	
			file_browser_callback : myFileBrowser
		});
	};
	
	
	// Init
	$('body').on("dblclick", ".editableComment:not(.current,.noTinyMCE)", function(){
		
		if($('.editableComment.current').length || $('div.buttonsQuickEdit').not($(this).siblings()).length){
			jAlert(ln.feeditopen, ln.alerttitle);
			$('#popup_ok').click(function(){
				$('.editableComment.current').focus().click();
			});
			return false;
		}
		
		var elem	= $(this);
		var elemID	= "comment-" + Math.floor( Math.random()*99999 );
		elem.addClass('current').attr('id', elemID);
		$.getTinyMCE_Comments(elemID);
	});
		
	$.refreshPanel = function(currEle, panel){
		panel.insertAfter(currEle);
		panel.fadeIn(400);
		if(currEle.siblings('.commentBox').length){
			currEle.siblings('.commentBox').insertAfter(panel);
		}
	};

	
	$.getCommentSubmitPanel = function(elemID){
		
		var editElem		= $("#" + elemID);
		var editDiv			= editElem.closest('.innerEditDiv');
		var editButtonDiv	= editDiv.children('div.editButtons');
		var resContent		= editElem.html();
		var elemWidth		= parseInt(editElem.innerWidth());
		var elemHeight		= parseInt(editElem.innerHeight());
		var elemMargin		= parseInt(editElem.css('margin-bottom')) * -1;
	
		// Warnen, wenn bereits ein anderes Element bearbeitet wird
		if($('div.buttonsQuickEdit').not(editElem.siblings()).length) {

			jAlert(ln.feeditopen, ln.alerttitle);
			return false;
		}
				
		editDiv.addClass('current');
		editButtonDiv.addClass('hide').hide();
		editButtonDiv.siblings('.conTypeDiv').addClass('hide').hide();
		
		// commentSubmitButtons
		var commentSubmitButtons	= '<button type="button" class="confirmCommentEdit feEditButton cc-button button submit apply" title="' + ln.feeditsubmit + ' [Strg+Enter]" value="' + ln.feeditsubmit + '"><span class="cc-admin-icons cc-icon-ok">&nbsp;</span>' + ln.feeditsubmit + ' [Strg+Enter]</button><button type="button" class="confirmCommentEdit feEditButton cc-button button cancel reset right" value="' + (elemWidth > 360 ? ln.feeditcancel : "") + '" title="' + ln.feeditcancel + '"><span class="cc-admin-icons cc-icon-cancel">&nbsp;</span>' + ln.feeditcancel + '</button>';
		
		// commentSubmitPanel
		var commentSubmitPanel	= $('<span class="buttonPanel-comments buttonPanel-change buttonPanel buttonsQuickEdit' + (elemWidth < 360 ? ' narrow' : '') + ' feButtonPanel" style="max-width:' + elemWidth + 'px; margin-top:' + elemMargin + 'px;">' + commentSubmitButtons + '<br class="clearfloat" /></span>');
		
		// commentSubmitButtons-Dom
		var submitButton	= commentSubmitPanel.children('.submit');
		var cancelButton	= commentSubmitPanel.children('.cancel');
				
		
		// Buttonpanel einfügen
		$.refreshPanel(editElem, commentSubmitPanel);

		
		// Speichern via Ctrl+Enter
		editElem.keydown(function(e) {
			if (e.ctrlKey && e.keyCode === 13) {
		
				e.preventDefault();
				
				editElem.closest('.listEntry').find('button.confirmCommentEdit.submit')[0].click(); // bug mit tinymce
				return false;
			}
		});
		

		// Speichern
		submitButton.bind("click", function(e) {
			
			var	term	= editElem.html(),
				url		= editElem.closest('.listEntry').find('.commentEditUrl').val();
			
			e.preventDefault();
			e.stopImmediatePropagation();
		
			$(this).unbind("click");
			
			$.getWaitBar();

			removeCommentEditor();				
			
			/* Send the data using post and put the results in a div */
			$.post(url, { commentEditText: term }, function(data) {				

				if(data == false || data == "0") {
					jAlert(ln.feediterror, ln.alerttitle);
				}else{
					var notice			= $(data);
					var noticeTarget	= $('div#container');
					if($('div#adminContent').length){
						noticeTarget	= $('div#adminContent > .adminArea:first');
					}
					noticeTarget.prepend(notice);
					if(typeof($.fixAdminNotice) == "function"){
						$.fixAdminNotice(true);
					}
					notice.delay(2000).fadeOut(400, function(){ notice.remove(); });
				}
				
			}).done(function(){
				$.removeWaitBar();				
				return false;
			});
			
			editDiv.removeClass('current');
			return false;

		});
		
	
		// Verwerfen
		cancelButton.click(function() {
		
			removeCommentEditor();
			editElem.html(resContent);

		});
		
		function removeCommentEditor(){
			tinymce.remove('#' + elemID);
			editDiv.removeClass('current');
			editElem.removeClass('current').removeAttr('id');
			
			commentSubmitPanel.fadeOut(300, function(){
				commentSubmitPanel.remove();
				editButtonDiv.removeClass('hide');
				editButtonDiv.siblings('.conTypeDiv').removeClass('hide').removeAttr('style');
			});
		}
	};
	
})(jQuery);