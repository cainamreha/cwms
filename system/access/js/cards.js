// jquery-ui dialog
head.ready(function(){
$(document).ready(function(){

	
	// Cards hinzufügen
	cc.cardsAddCard = function(cardDiv, sourceTab, resetCard){
	
		var ed		= "";
		var cardNo	= parseInt(cardDiv.children().length);
		var now		= $.now();
		var newTab	= $(sourceTab.clone());
		var regex	= " " + cardNo;
			regex	= new RegExp(regex, "g");
		var edIDPfx	= 'cardCon-' + now + '-';
		
		var listEntryHeader	= newTab.find('.listEntryHeader');
		listEntryHeader.html(listEntryHeader.html().replace(regex, (" "+(cardNo+1))));
		
		newTab.find('.cc-groupitem-content-label').each(function(i,ele){
			$(ele).html($(this).html().replace(regex, (" "+(cardNo+1))));
			$(ele).attr('data-target', edIDPfx + (cardNo + i+1));
			if(resetCard){
				$(ele).removeClass('busy');
				$(ele).next('.cardImgBox').find('.elementsFileName').children('button').click();
			}
		});
		
		newTab.find('.mce-tinymce').remove();
		newTab.hide().appendTo(cardDiv).fadeIn(300);
		newTab.find('.cc-editor-add').each(function(i,ele){
			var edID	= edIDPfx + parseInt(cardNo + i+1);
			$(ele).attr('id', edID).attr('data-index', parseInt(cardNo + i+1));		
			if(resetCard){
				$(ele).val("");
			}
			tinymce.EditorManager.execCommand('mceAddEditor',true, edID);			
			ed = tinymce.editors[edID];
			if(resetCard){
				ed.setContent("");
			}
			//ed.show();
		});
		
		return false;
			
	};
		
	
	// Cards hinzufügen
	$('body').on("click", '.addCards', function(e){
	
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var cardDiv		= $(this).prev('.setupCards');
		var sourceTab	= cardDiv.children('.cardEntry:last');
		
		cc.cardsAddCard(cardDiv, sourceTab, true);
		
		return false;
	
	});

	
	// Karteninhalte (cards) im Editbereich löschen
	$('body').on("click", '.cardEntry .copyCard', function(e) {
		
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var sourceTab	= $(this).closest('.cardEntry');	
		var cardDiv		= sourceTab.closest('.setupCards');
		
		cc.cardsAddCard(cardDiv, sourceTab, false);
	
	});

	
	// Karteninhalte (cards) im Editbereich löschen
	$('body').on("click", '.cardEntry .removeCard', function(e) {
		
		e.preventDefault();
		e.stopImmediatePropagation();
		
		var cardEle		= $(this).parents('.sortableCards');
		var card		= $(this).closest('.cardEntry');
		var cardCon		= card.find('.cc-editor-add');
		
		cardCon.each(function(i,ele){
			tinymce.remove('#' + $(ele).attr('id'));
		});
		
		// if new first
		if(cardEle.children('.cardEntry').length < 2){
			var newTab	= card.clone();
			var now		= $.now();
			var edIDPfx	= 'cardCon-' + now + '-';
			
			newTab.find('.cc-groupitem-content-label').each(function(i,ele){
				$(ele).attr('data-target', edIDPfx + i+1).removeClass('busy');
				$(ele).next('.cardImgBox').find('.elementsFileName').children('button').click();
			});
			newTab.hide().appendTo(cardEle).delay(300).fadeIn(200);
			newTab.find('.cc-editor-add').each(function(i,ele){
				var edID	= edIDPfx + i+1;
				$(ele).attr('id', edID).attr('data-index', i+1).val("");
				tinymce.EditorManager.execCommand('mceAddEditor',true, edID);
				ed = tinymce.editors[edID];
				//ed.show();
			});
		}
		card.fadeOut(300, function(){
			
			var regex	= " [0-9]+";
				regex	= new RegExp(regex, "g");

			$(this).remove();
			
			cardEle.children('.cardEntry').each(function(i,ele){
				$(ele).find('.listEntryHeader, label').each(function(){
					$(this).html($(this).html().replace(regex, (" "+(i+1))));
				});
			});
		});
		return false;
	});
});
});
