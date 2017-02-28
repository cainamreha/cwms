/*
 * jQuery Pimg (Preview image) 
 * Written by Dave Earley ( http://dave-earley.com )
 * Modified by Alexander Hermani
 */

(function($){

	$.pimg = function(disabled){
	
		if(disabled === "disable"){
			return false;
		}
		
		$('body').on("bind, mouseenter", "img.preview", function() {
														   
				$(".pimg").remove();
				
				var elem				= $(this);
				var targetTag			= $('body');
				var thumbBox			= elem.parent('.previewBox');
				
				var img_title			= elem.attr('title');
				var img_alt				= elem.attr('alt');
				var img_src				= elem.attr('data-img-src') ? elem.attr('data-img-src') : elem.attr('src');
				var desc				= "<h2 class='cc-section-heading cc-h2 pimgH'>" + (img_src.replace(cc.httpRoot + "/", "").split("?")[0]) + "</h2>";
				var image				= new Image();
				var date				= new Date();
				image.src				= img_src + '?' + date.getTime();
				var actualWidth 		= image.width;
				var actualHeight		= image.height;
				var offsetTop			= "";
				var offsetLeft			= "";
				var thumbWidth			= parseInt(thumbBox.outerWidth());
				var thumbHeight			= parseInt(thumbBox.outerHeight());
				var thumbHalfH			= parseInt((thumbHeight) / 2);
				var thumbOffsetTop		= parseInt(thumbBox.offset().top);
				var thumbOffsetLeft		= parseInt(thumbBox.offset().left);
				var offsetLeftMargin	= 25;
				
				// Preview-Div anhängen
				targetTag.append("<div class='pimg adminArea' style='display:none;'><img src='" + image.src + "' alt='Image preview' class='previewImgBig' />" + desc + "</div>").parent().is(function(){
					
					// Maße für div.pimg holen, wenn Bild geladen
					$('img.previewImgBig').load(function(){
					
					var pimgObj			= $(this).parent('.pimg');
					var pimgWidth		= parseInt(pimgObj.outerWidth());
					var pimgHeight		= parseInt(pimgObj.outerHeight());
					var imgWidth		= pimgObj.children('.previewImgBig').innerWidth();
					$(this).children(".pimgH").css('width', parseInt(imgWidth - 10) + 'px');
					var pimgHalf		= parseInt(pimgHeight / 2);
					$(this).children('.pimgH').css('width', 'auto');
					
					// Bildmaße verzögert anzeigen
					$(pimgObj).hide(0, function(){
						
						offsetTop			= parseInt((thumbOffsetTop + thumbHalfH) - pimgHalf);
						offsetLeft			= parseInt(thumbOffsetLeft + thumbWidth + offsetLeftMargin);
						
						var os				= $(window).width() - offsetLeft;						
						
						if(os <= pimgObj.width()){							
							offsetLeft -= thumbWidth + (2 * offsetLeftMargin) + pimgWidth;
						}
						
						// Tatsächliche Bildmaße holen
						actualWidth 		= image.width;
						actualHeight		= image.height;
					
						$(this).css({'top' : offsetTop + 'px', 'left' : offsetLeft + 'px', 'position' : 'absolute'});
						$(this).delay(600).fadeIn(500, function(){
							$(this).append(" <div class='opacity90 imgSize'>" + actualWidth + " X " + actualHeight + "</div>").children(".imgSize").hide().delay(350).fadeIn(200);
						});
					});
					});
				});
		}).on("bind, mouseleave", "img.preview", function()
		{
			$(".pimg").stop().fadeOut(100, function(){
				$(this).remove();
			});
		});
	};
})(jQuery);
