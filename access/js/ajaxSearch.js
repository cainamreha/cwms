$(document).ready(function(){
	
	// Suche

	/*
	// Platzhalter ausblenden
	$("body").on("focus", "input.searchPhrase", function() {
		var ph	= $(this).attr('placeholder');
		$(this).attr('placeholder', '');
		$("input.searchPhrase").blur(function(){
			$(this).attr('placeholder', ph);
		});
	});

	var searchDivW		= $('#right').width();
	var headerW			= $('#header').width();
	var searchPhraseW	= $('#header input.searchPhrase').width();
	var deltaRange		= (searchDivW < 0 ? headerW -36 : searchDivW) - searchPhraseW -54;
	
	// Funktion zum Vergrößern der Suchmaske
	$("#header input.searchPhrase, .searchBox input.searchPhrase").focus(function() {
		if($(this).val() == ""){
			var searchDiv = $(this).parents("div.searchDiv");
			searchDiv.stop(true, false).animate({width:"+="+deltaRange+"px"}, 500);
			$(this).stop(true, false).animate({width:"+="+deltaRange+"px"}, 500);
		}
	});
	$("#header input.searchPhrase, .searchBox input.searchPhrase").blur(function(){
		if($(this).val() == ""){
			var searchDiv = $(this).parents("div.searchDiv");
			searchDiv.stop(true, false).animate({width:"-="+deltaRange+"px"}, 500);
			$(this).stop(true, false).animate({width:"-="+deltaRange+"px"}, 500);
			if($(this).parents('#header').length){
				$(this).fadeOut(500).siblings('ul#searchTerms').delay(2000).fadeOut(500);
			}
		}
	});
	*/

	// Verfeinerte Suche einblenden
	$("body").on("click", ".refineSearch", function(){
		$('.refSearch').val("true");
		$(this).next('div#refinedSearch:hidden').toggle(200);
		return false;
	});


	// Zieltabellen
	$("body").on("click", "#markAllSearch", function(){
		var searchTargets = $('input.searchTable');
		if($(this).is(':checked')){
			searchTargets.prop('checked','checked');
		}else{
			searchTargets.removeAttr('checked');
		}
	});


	// Falls keine Zieltabellen ausgewählt
	$("body").on("click", "#submitNewSearch", function(){
		var searchTargets = $('input.searchTable:checked');
		if(!searchTargets.length){
			$.doAlert($(this));
			return false;
		}
	});


	
	var request	 	= false;
	var startT		= 0;
	var elapsedT	= 0;
	
	// Ajax-Suche
	$('body').on("keyup", 'input[data-ajaxsearch="true"]', function(e){
		
		startT				= new Date().getTime();
		var element			= $(this);
	
		var searchPhrase	= encodeURIComponent($(this).val().toString());
		var searchType		= element.attr('class').split(' ')[1];
		var searchPhraseLen = searchPhrase.length;
		
		if(searchType == "big" && $('input[name="searchTables[true]"]').length){
			var t = $('input[name="searchTables[t]"]').is(":checked") ? 't' : '';
			var a = $('input[name="searchTables[a]"]').is(":checked") ? 'a' : '';
			var n = $('input[name="searchTables[n]"]').is(":checked") ? 'n' : '';
			var p = $('input[name="searchTables[p]"]').is(":checked") ? 'p' : '';
		} else {
			var t = $('input.searchTablesT').val().toString();
			var a = $('input.searchTablesA').val().toString();
			var n = $('input.searchTablesN').val().toString();
			var p = $('input.searchTablesP').val().toString();
		}
		var targetTabs = t + a + n + p;
		

		// Falls keine Navigationstaste gedrückt
		if(e.keyCode != 40
		&& e.keyCode != 38
		&& e.keyCode != 13
		&& e.keyCode != 39
		&& searchPhraseLen > 0
		&& targetTabs != ""
		) {
			
			$('ul#searchTerms').remove();
			element.parents('div.searchDiv, div#newSearch').children('#searchResults').remove();
			var targetUrl	= cc.httpRoot + '/access/ajaxSearch.php?search=' + searchPhrase + '&src=' + targetTabs + '&ajaxsearch=1&type=' + searchType + '&page=';
			
			// Falls Adminbereich
			if(element.parents('body').hasClass('admin')){
				targetUrl	+= 'admin';
			}
			targetUrl		+= '#newSearch';
			
			
			if(startT - elapsedT >= 200){
				elapsedT = new Date().getTime();
			}else{
				if(typeof(request) == "object"){
					request.abort();
				}
			}
			
			request = $.ajax({
			
				url: targetUrl,
				success: function(ajax){
				
					var data = ajax.split("<>");
					
					if(data.length < 2){
						data = new Array(ajax, "");
					}
					
					if(e.keyCode != 40
					&& e.keyCode != 38
					&& e.keyCode != 13
					&& e.keyCode != 39
					){
						$('ul#searchTerms').remove();
						if(data[0].length > 0){
							element.stop().after(data[0]);
						}
						
					}
						
					
					if(searchType == "big"){
						element.parents('div.searchDiv, div#newSearch').children('#searchResults').remove();
						element.parents('div.searchDiv, div#newSearch').append(data[1]);
					}else{
						$('div#mainContent').click(function(){
							$('ul#searchTerms').remove();
							element.val("");
							element.blur();
						});
					}
					//return false;
				}
			});
		
		} else {
																					  
			// Falls Navigationstaste (Pfeile) gedrückt
			var listTerm;
			var aktiv;
			var highlightClass	= "highlight bg-info";
			
			listTerm = $('ul#searchTerms');
			aktiv = listTerm.children('li.highlight').index();
			
			switch (e.keyCode) {
				case 40: // "Pfeil nach unten"-Taste
					if (aktiv > -1) {
							listTerm.children("li:eq(" + aktiv + ")").removeClass(highlightClass);
							listTerm.children("li:eq(" + (aktiv + 1) + ")").addClass(highlightClass);
					} else {
							listTerm.children("li:eq(0)").addClass(highlightClass);
					}
				break;
				case 38: // "Pfeil nach oben"-Taste
					if (aktiv > -1) {
							listTerm.children("li:eq(" + aktiv + ")").removeClass(highlightClass);
							listTerm.children("li:eq(" + (aktiv - 1) + ")").addClass(highlightClass);
					} else {
							listTerm.children("li:last-child").addClass(highlightClass);
					}
				break;
				case 13: // "Return"-Taste
					if (aktiv > -1) {
						var sp = listTerm.children("li:eq(" + aktiv + ")").children('a').text().replace(/<\/?strong>/ig, "");
						e.preventDefault();
						e.stopPropagation();
						listTerm.remove();
						element.val(sp).parents('form').find('*[type="submit"]').click();
						//listTerm.children("li:eq(" + aktiv + ")").children('a').click();
						//return false;
					} else {
						e.preventDefault();
							$('html').ajaxStop(function(){
							listTerm.remove();
							element.parents('form').submit();
						});
					}
					return false;
				break;
				case 39: // "Pfeil nach rechts"-Taste
					if (aktiv > -1) {
						var sp = listTerm.children("li:eq(" + aktiv + ")").children('a').text().replace(/<\/?strong>/ig, "");
						$('ul#searchTerms').remove();
						element.val(sp).focus();
					}
				break;
				
			}
			
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
		}
	});
});