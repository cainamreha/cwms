/* FE-editing von Textelementen */

head.ready(function(){
	
(function($){
	
	var previewTheme	= "";
	var gotoTheme		= 0;
	
	if($.cookie("previewTheme") != null){
		previewTheme	= $.cookie("previewTheme");
		gotoTheme		= $('div#themeSelection .preview').index();
	}else{
		gotoTheme		= $('div#themeSelection .currentTheme').index();
	}
	

	// Theme-Auswahl-Box einblenden
	$(".selTheme > a").click(function(e){
		
		e.preventDefault();
		e.stopImmediatePropagation();
		
		$("#themeSelection").closest(".cc-fePanel").addClass("active");
		$("#themeSelection").slideDown(300);
	
	});
	
	// Theme-Auswahl-Galerie generieren
	$("#themeSelection #themeCarouselSlider").owlThemeCarousel({
		items: 6,
		margin: 5,
		lazyLoad: true,
		scrollPerPage: true,
		navigation: true,
		navigationText: ["&#xf0a8;","&#xf0a9;"],
		responsiveBaseWidth: $('#themeSelection #themeCarouselSlider'),
		afterInit: function(){}
	});
	
	//get carousel instance data and store it in variable owl
	cc.themeCarouselSlider = $("#themeSelection #themeCarouselSlider").data('owlThemeCarousel');

	cc.themeCarouselSlider.goTo(gotoTheme);
	
	// Theme-Auswahl-Box ausblenden
	$("body").on("click", ".themeSelection-close", function(e){
		e.preventDefault();
		e.stopPropagation();
		if($(this).closest("#themeSelection").length){
			$("#themeSelection").slideUp(300);
		}
	});
	
	// Theme-Auswahl-Box ausblenden
	$("body").on("mouseup", "#themeSelection .buttons", function(e){
		$(this).blur();
	});

	// Theme-Auswahl
	// Theme-Vorschau
	$('div#themeSelection .selectTheme').bind("click", function(){
		
		var selTheme	= $(this).attr('id').split("theme-")[1];
		
		$(this).siblings('.selectTheme').removeClass('preview');
		$(this).addClass('preview');
		
		previewTheme = selTheme;
		
		$.cookie("previewTheme", previewTheme, { expires: 7, path: '/' });
		
		$.getWaitBar();
		
		document.location.replace(window.location.href.split("#")[0]);

		if(!$('.themeSelection-panel button').length){
			$('.themeSelection-panel').append('<button type="submit" name="confirmTheme" class="confirmTheme btn-centered"><span class="icons icon-ok button ok">&nbsp;</span></button><button type="submit" name="cancelTheme" class="cancelTheme btn-centered"><span clas="icons icon-cancel button cancel">&nbsp;</span></button>');
		}
		return false;
	});
	
	// Theme festlegen/übernehmen
	$('body').on("click", '.themeSelection-panel .confirmTheme', function(){
		
		if(previewTheme != ""){
		
			var confMes 	= $(this).attr('title');
			
			if(typeof(jConfirm) == "function"){
				confMes 	= conciseCMS.ln.confirmtheme + "<strong>" + previewTheme + "</strong>";
				jConfirm(confMes, conciseCMS.ln.confirmtitle, function(result){
					if(result === true){
						$.getWaitBar();
						$.cookie("previewTheme", null, { expires: 7, path: '/' });
						$('input#currTheme').val(previewTheme).parent('form#themeSelectionForm').submit();
					}
				});
			}else{
				if(confirm(confMes)){
						$.cookie("previewTheme", null, { expires: 7, path: '/' });
						$('input#currTheme').val(previewTheme).parent('form#themeSelectionForm').submit();
				}
			}
		}
		return false;
	});

	// Theme verwerfen
	$('body').on("click", '.themeSelection-panel .cancelTheme', function(){
		$.getWaitBar();
		$.cookie("previewTheme", null, { expires: 7, path: '/' });
		document.location.replace(window.location.href);
		return false;
	});
	
})(jQuery);

});