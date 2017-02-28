head.ready(function(){
	
	$(document).ready(function(){

		$("#debugContent").tabs({
			beforeLoad: function( event, ui ) {
				ui.jqXHR.fail(function() {
				ui.panel.html(
					"Ajax error. Couldn't load this tab." );
			});
		  }
		});
		
		/* DEBUG Konsole ausklappen */
		$('body').on("click", "#debugOpener", function(){
			//Sliden
			if ($("#debugContent").is(":hidden")) 
			{
				$("#debugContent").slideDown(100);
				$("div#debugDiv").css({'width':'100%','margin':'0'});
				$(this).css('background','url("' + cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/opener.png") no-repeat');
			} else {
				$("#debugContent").slideUp(100);
				$("div#debugDiv").css({'width':'20%','margin':'0 40%'});
				$(this).removeAttr('style');
			}
		});
		
		$("body").on("hover","#debugContent, #debugOpener", function(){
			$(this).css('background','url("' + cc.httpRoot + '/system/themes/' + cc.adminTheme + '/img/opener.png") no-repeat');
		});
	
	});

});