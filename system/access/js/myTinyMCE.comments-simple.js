// Simple Comment Editor
head.ready(function(){
	$(document).ready(function(){
		$.getTinyMCE_CommentsSimple("message", $("#commentEditorSkin").val(), $("#emoticonForms").length);
	});
});
	
(function($){

	$.getTinyMCE_CommentsSimple = function(elemID, skin, emoticons){
	
		tinyMCE.init({
			entity_encoding : "raw",
			inline : false,
			hidden_input: false,
			selector : "#" + elemID,
			relative_urls: false,
			document_base_url : conciseCMS.httpRoot + "/",
			convert_urls : true,
			remove_script_host : false,
			language : typeof(conciseCMS.lang) != "undefined" ? conciseCMS.lang : "de",
			theme : "modern",
			skin : skin,
			width : "calc(100% - 2px)",
			insertdatetime_formats: ["%d.%m.%Y", "%H:%M Uhr", "%H:%M:%S", "%m-%d-%Y", "%D", "%H:%M:%S %p"],
			visualblocks_default_state: false,
			indentation : '18px',
			schema: "html5",
			extended_valid_elements: "span[id|class|style|title|aria-hidden]",
			plugins : ["bbcode advlist lists charmap" + (emoticons ? " emoticons" : ''),"visualblocks visualchars insertdatetime nonbreaking"],
			menubar: false,
			toolbar1: "undo redo | styleselect | bold italic underline strikethrough" + (emoticons ? " | emoticons" : '') + " | link",
			style_formats: [
				{title: 'Blocks', items: [
					{title: "Blockquote", format: "blockquote"},
					{title: "Pre", format: "pre"}
				]}
			],
			visualblocks_default_state: false,
			end_container_on_empty_block: true
		});
	};

})(jQuery);
