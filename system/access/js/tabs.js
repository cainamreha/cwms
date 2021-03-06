// jquery-ui dialog
$(document).ready(function(){

	
	// Tabs hinzufügen
	$('body').on("click", '.addTabs', function(){
		
		var ed		= "";
		var tabDiv	= $(this).prev('.setupTabs');
		var tabNo	= parseInt(tabDiv.children().length);
		var lastTab	= tabDiv.children('.tabEntry:last');
		var now		= $.now();
		var newTab	= $(lastTab.clone());
		var regex	= " " + tabNo;
			regex	= new RegExp(regex, "g");
		var edID	= 'tabCon-' + now;
		
		newTab.find('.mce-tinymce').remove();
		newTab.find('.tabContent').attr('id', edID).attr('data-index', (tabNo+1)).html("");
		newTab.find('.tabContent-label').attr('data-target', edID);
		newTab.children('.listEntryHeader, label').each(function(){ $(this).html($(this).html().replace(regex, (" "+(tabNo+1)))); });
		newTab.children('input, textarea').each(function(){ $(this).removeClass('disableEditor'); });
		newTab.children('input').val("tab-"+(tabNo+1));
		newTab.children('textarea').val("");
		newTab.appendTo(tabDiv);
		
		tinymce.EditorManager.execCommand('mceAddEditor',true, edID);
		
		ed = tinymce.editors[edID];
		ed.setContent("");
		ed.show();
		
		return false;
	
	});
	
	/*
	// Reiterinhalte (tabs) im Editbereich Toggeln
	$('body').on("click", '.tabContent-label', function() {
		//$(this).next().slideToggle('fast', function(){
			if(typeof($.sortableTabs) == "function"){
				if($('.tabContent-label').next('.mce-tinymce:visible').length){
					$(this).parents('.sortableTabs').sortable('disable');
				}else{
					$(this).parents('.sortableTabs').sortable('enable');
				}
			}
		//});
		return false;
	});
	*/
	
	// Reiterinhalte (tabs) im Editbereich löschen
	$('body').on("click", '.tabEntry .removeTab', function() {
		
		var tabEle	= $(this).parents('.sortableTabs');
		var tab		= $(this).closest('.tabEntry');
		var tabCon	= tab.find('.tabContent');
		
		tinymce.remove('#' + tabCon.attr('id'));
		
		if(tabEle.children('.tabEntry').length < 2){
			var newTab	= tab.clone();
			var now		= $.now();
			var edID	= 'tabCon-' + now;
			
			newTab.find('.tabHeader').val("tab-1");
			newTab.find('.tabContent').attr('id', edID).attr('data-index', 1).html("");
			newTab.find('.tabContent-label').attr('data-target', edID).removeClass('busy');
			newTab.hide().appendTo(tabEle).delay(300).fadeIn(200);
			tinymce.EditorManager.execCommand('mceAddEditor',true, edID);
			ed = tinymce.editors[edID];
			ed.show();
		}
		tab.fadeOut(300, function(){
			
			var regex	= " [0-9]+";
				regex	= new RegExp(regex, "g");

			$(this).remove();
			
			tabEle.children('.tabEntry').each(function(i,e){
				$(e).children('.listEntryHeader, label').each(function(){
					$(this).html($(this).html().replace(regex, (" "+(i+1))));
				});
			});
		});
		return false;
	});
});
