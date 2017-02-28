$(document).ready(function(){

	// Zu Anker scrollen
	$("body").on("click", "div:not(.starrater) a[href^='#']:not(.scroll-no)", function() {
		var ancor = $(this).attr("href").split("#")[1];
		if(typeof(ancor) != "undefinded" && ancor != ""){
			$('html,body').stop().animate({ scrollTop: $("#" + ancor).offset().top }, 400);
			$("#" + ancor).focus().blur();
			return false;
		}
	});
	
	// Nach oben Link anzeigen/ausblenden
	$(window).scroll(function () {
		if ($(this).scrollTop() > 100) {
			$('.navbar-large').removeClass('navbar-large').addClass('navbar-large-condensed');
			$('p.up').fadeIn(600);
		} else {
			$('.navbar-large-condensed').addClass('navbar-large').removeClass('navbar-large-condensed');
			$('p.up').fadeOut(600);
		}
	});
	
	// Zum Seitenanfang scrollen
	$("body").on("click", 'p.up', function() {
		$('body,html').animate({scrollTop: 0}, 600);
		return false;
	});

	
	// Funktion zum Zurücksetzen von Formularen
	$("#reset").click(function fieldRes() {
	
		$("select").children('option:first-child').attr("selected","selected"); 
		$('input').not('input[type="button"]').not('input[type="submit"]').not('input[type="reset"]').attr("value","");
		$("textarea").attr("value","");
	});

	
	// Einblenden der Loginbox (hover)
	$("#accountMenu, .logFormSmall").mouseenter(function() {
		var logDiv = $(this).parent().children(".logFormSmall");
		logDiv.css('display','block');
	}).mouseleave(function() {
		var logDiv = $(this).siblings(".logFormSmall").not('.show');
		logDiv.delay(2000).fadeOut(200);
	});
	
	// Funktion zum dauerhaften Einblenden der Loginbox
	$("div.logFormSmall input").focus(function() {
		var logDiv = $(this).parents("div.logFormSmall");
		logDiv.addClass('show');
		$('div#contentBox *').click(function(){
			logDiv.removeClass('show');
		});
	});
	
	// Funktion zum Ausblenden der Loginbox
	$("body").on("click", "a.loginButton", function() {
		var logDiv = $(this).parents("#accountMenu").siblings("div.logFormSmall");
		if(logDiv.hasClass('show')){
			logDiv.fadeOut(300, function(){ $(this).removeClass('show').removeAttr('style'); });
			return false;
		}
	});

	
	// Untermenü ein-/ausblenden
	$('body').on("mouseenter", "ul#main_menu > li.hasChild", function(e){
		$(this).siblings('li').children('*:not(a)').slideUp(0);
		$(this).children('*:not(a)').stop(true,true).hide().fadeIn(300).slideDown(100);
	}).on("bind, mouseleave", "ul#main_menu > li.hasChild", function(e){
		$(this).children('*:not(a)').fadeOut(600).slideUp(100).parent('li').siblings('li').clearQueue().children('*:not(a)').stop(true,true).css('height','auto');
		$(this).siblings('li').children('*:not(a)').slideUp(0);
		return false;
	});

	
	// Verringern des z-index von Elementen bei Vollbild (jPlayer)
	$('a.jp-full-screen').click(function(){
		$('div#header').css('z-index',0);
		$('div#right').css('display',"none");
		$('div#footer').css('display',"none");
	});
	$('a.jp-restore-screen').click(function(){
		$('div#header').css('z-index',100);
		$('div#right').css('display',"block");
		$('div#footer').css('display',"block");
	});
	

	// Funktion zum Erneuern des Captchas
	var count = 0;	
	$('.caprel').click(function(){
								
		count++;
		
		var targetUrl = $('.caprel').attr('href');
		$('img.captcha').attr('src', targetUrl + '?rel=' + count);
		return false;
	});
	
	
	// Smileys ersetzen
	$('p.smileys img.smiley').click(
		function setsmile() {
			
			var shortcut = $(this).attr('title') == "" ? titleVal : $(this).attr('title');
			//alert(shortcut);
			var message = $('textarea[name="message"]').attr('value');
			$('textarea[name="message"]').attr('value', message + shortcut);
			
			return false;
	});


	// Theme-Vorschaubilder einblenden bei Auswahl über Select
	$('select.themes option').on("bind, mouseenter", function(e){
			
		var theme = $(this).html();
		var themeImg = cc.httpRoot  + '/themes/' + theme + '/img/theme-preview-big.jpg';

		$(this).parents('div.selTheme').append('<div class="themePreview" style="top:' + e.pageY + 'px;"><img src="' + themeImg + '" /></div>');
				
	}).on("bind, mouseleave",
        	function(e){
			$('div.themePreview').remove();
	});


	// Löschen eines Inhaltelements bzw. von Kommentaren
	$('body').on('click', '.dataEditButtons *[data-action="delete"], .commentEditButtons *[data-action="delete"]', function(){
		var targetUrl = $(this).attr('data-url');
		var confMes = "Möchten Sie dieses Element wirklich löschen?";
		if(typeof(jConfirm) == "function"){
			jConfirm(confMes, ln.confirmtitle, function(result){
				if(result === true) {
					document.location.href = targetUrl;
				}
			});
		}else{
			if(confirm(confMes)){
				document.location.href = targetUrl;
			}
		}
		return false;
	});


	// Nachricht veröffentlichen
	$('body').on('click', '*[data-action="pubdata"]:not(.icon-loading)', function(){
		
		var elem		= $(this);
		var targetUrl	= elem.attr('data-url');
		
		if($.isFunction($.fn.loading)) {
			elem.loading();
		}
		
		$.ajax({
			url: targetUrl,
			success: function(ajax){
				if(ajax == "1"){
					elem.siblings('[data-action="pubdata"]:hidden').css('display','inline-block');
					elem.css('display','none');
				}else{
					jAlert(ln.dberror, ln.alerttitle);
				}
				if($.isFunction($.fn.loadingRemove)) {
					elem.loadingRemove();
				}
				return false;
			}
		});
		return false;
	});


	// Kommentare einblenden
	$('p.comments span.toggle').bind("click tab", function(){
		$(this).parent('p').siblings('div.comments').slideToggle("fast");
		return false;
	});
	$('#commentSection').blur(function(){
		$(this).children('div.comments').slideDown("fast");
		return false;
	});

	// Kommentar-Formular einblenden
	$('span.newComment').bind("click tab",function(){
		if($('#commentForm').length){
			$('#commentForm').slideToggle("fast");
			return false;
		}
	});
	
	
	// Newsfeed-Liste einblenden
	$('img#feed, div.cc-newsfeed div.close').click(function(){
		$('div.cc-newsfeed').slideToggle("fast");
		$(this).blur();
		return false;
	});
	
	
	// Tabs ein-/ausblenden
	if($('div.tabsWrapper').length){
	
		$('div.tabsWrapper').children('div.tabContent:not(#tab-1)').hide().find('.timeBar').fadeTo(0,0);
		
		// Tabs ein-/ausblenden
		$("body").on("click touchstart", 'ul.tabs a', function(e) {
			
			e.preventDefault();
			e.stopImmediatePropagation();
			
			$(this).blur();
			
			var tabID = $(this).attr('href').split("#tab-")[1];
			$('div.tabContent:visible:not(#tab-' + tabID + ')').find('.timeBar').fadeTo(0,0);
			$(this).parents('ul.tabs').children('li.active').removeClass('active');
			$(this).parent('li.tab-' + tabID).addClass('active');
			$('div.tabContent:visible:not(#tab-' + tabID + ')').slideUp('200').siblings('div#tab-' + tabID).slideDown('400', function(){
			
				// do after tab change
			});
			return false;
		});
	}
	
	
	// Zusätzliche Bestellposten einblenden
	$('span.addOrderEntry').parent('p').click(function() {
		if($(this).siblings('li:visible').length)
			$(this).parent('ol').children('li:visible').next('li:hidden').show();
		if(!$(this).siblings('li:hidden').length) {
			$(this).hide();
		}
		return false;
	});


	// Wenn beim Bestellformular beim Schritt "überprüfen" ändern geklickt wird
	$('span.edit').click(function() {							 
		var submitName = $(this).attr('id');
		$(this).append('<input name="' + submitName + '" type="hidden" value="edit" />').parents('form').submit();	 	
		return false;
    });
	
	
	// Benutzermeldung ausblenden
	if($('.tempHint, div.cc-module > p.notice, div#mainContent > p.notice').length) {
		$('.tempHint, div.cc-module > p.notice, div#mainContent > p.notice').not('.empty').delay(3000).slideUp("medium", function(){ $(this).remove(); });
		return false;
	}
	
});