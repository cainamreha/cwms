// pfGallery a portfolio gallery
(function($) {
	
	// Bildergallerie / Portfolio
	if($('.pfGallery').length) {
		
		$('.pfGallery .galleryLink').attr('target','_blank');
		$('.pfGallery .thumbnail').children('img.thumb').fadeTo(200, 0.85);
		$('.pfGallery .thumbnail').children('.detailBox').hide();
		$('.pfGallery .thumbnail:first').addClass('active');
		$('.detailBox').css({'width': '412px', 'margin-left': '0', 'display': 'none'});
		$('.first').css('width',0).css({'display': 'block', 'margin-left': '450px', 'opacity': 0}).delay(200).animate({"margin-left": '-=450', 'width': '+=360', 'opacity': 100}, 400);
		
		$('.pfGallery .thumbnail').bind("click",function(){
			$(this).siblings('.thumbnail').removeClass('active').children('.detailBox').fadeOut(600);
			$(this).addClass('active').children('.detailBox').fadeIn(600).css('display','block');
		});
	
		$('img.thumb').bind("mouseenter",
			function(e){
				$(this).fadeTo(200, 1);				
		}).bind("mouseleave",
			function(e){
				$(this).fadeTo(200, 0.85);
				return false;
		});	
		
		// ThumbBoxen blättern
		$('.pfGallery .thumbNavi .showSubBox a').click(function(e){
			e.preventDefault();
			e.stopPropagation();

			var target			= $(this).attr('data-target');
			var parShowSubBox	= $(this).parent('.showSubBox');
			
			if(parShowSubBox.hasClass('active')){
				return false;
			}
			
			parShowSubBox.addClass('active').siblings('.showSubBox.active').removeClass('active');
			$(this).parents('.thumbBox').children('.thumbBox-sub.active').fadeOut(600, function(){
				$(this).removeClass('active');
				//$(this).siblings('#' + target).fadeIn(400).addClass('active');
				$(this).siblings('#' + target).addClass('active').fadeIn(400);
				$(this).siblings('#' + target).children('.thumbnail:first-child').click();
			});
			return false;
		});
		
	}
})(jQuery);
