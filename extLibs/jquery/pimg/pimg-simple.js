/*
 * jQuery Pimg (Preview image) 
 * Written by Dave Earley ( http://dave-earley.com )
 * Modified by Alexander Hermani
 */

$(document).ready(function(){
						   
	// Falls rechte Bar vorhanden und kein Mobile (> 768)
	if($(window).width() > Gumby.breakpoint){
		pimg();
	}
	
	// Bilder skalieren
	$('.imgObj').each(function(){ scaleImg($(this).children('*:not(.caption)').find('img'), 0); });

});

// Skalieren von Bildern
function scaleImg (img, adaptH){
	var maxW = Math.max(img.parents('.imgObj').width(), 720);
	var maxH = Math.max(img.parents('.imgObj').height(), 720);
	
	if(maxH > adaptH && adaptH > 0){
		maxH = adaptH;
	}
	var ratio = 0;

	if(img.width() > maxW && img.width() > img.height()){
		ratio = img.height() / img.width();
		img.animate({'width': maxW + 'px'}, 250);
		img.animate({'height': (maxW*ratio) + 'px'}, 250);
	}
	if(img.height() > maxH){
		ratio = img.width() / img.height();
		img.animate({'height': maxH + 'px'}, 250);
		img.animate({'width': (maxH*ratio) + 'px'}, 250);
	}
}

// Bildervorschau mit pimg
function pimg()
{

    $(".dataDetail .imgObj .caption").append('<span class="enlarge" title="vergrößerte Ansicht">&nbsp;</span>');
	
    $("#container").on("click touchstart", ".dataDetail .imgObj .enlarge", function() {
													   
			$(".pimg").remove();
			
			var parElem				= $(this).parents('.imgObj');
			var elem				= parElem.find('img');
			var targetTag			= $('#container');
						
			var img_title			= elem.attr('title');
			var img_alt				= elem.attr('alt');
			var img_src				= elem.attr('data-img-src') ? elem.attr('data-img-src') : elem.attr('src');
			var image				= new Image();
			image.src				= img_src ? img_src : elem.attr('src');
			var imgW				= image.width;
			var caption				= "<p class='caption' style='max-width:" + imgW + "px'>" + (parElem.find('.caption').html()) + "</p>";
			var pimgW				= "";
			var pimgH				= "";
			var offsetTop			= "";
			var offsetLeft			= "";
			var offsetClose			= "";
			var windowH				= parseInt($(window).height());
			var windowW				= parseInt($(window).width());
			
			// Preview-Div anhängen
			targetTag.append("<div class='pimg' style='display:none;'><div class='close' title='schließen'>&nbsp;</div><img src='" + image.src + "' alt='Image preview' class='previewImgBig' />" + caption + "</div>").parent().is(function(){
				
				// Maße für div.pimg holen, wenn Bild geladen
				$('img.previewImgBig').load(function(){
				
					var pimgObj			= $(this).parents('.pimg');
					
					// Bildmaße verzögert anzeigen
					$(pimgObj).hide(0, function(){
					
						pimgW				= parseInt(pimgObj.outerWidth());
						pimgH				= parseInt(pimgObj.outerHeight());
						
						offsetTop			= parseInt((windowH - pimgH) / 2);
						offsetLeft			= parseInt((windowW - pimgW) / 2);
						offsetClose			= parseInt(pimgW -24) + 'px';
						
						
						if(windowH > pimgH && windowW > pimgW && offsetTop > 0){
							$(this).css({'top' : offsetTop + 'px', 'left' : offsetLeft + 'px'});
						}else{
							$(this).css({'top' : '5%', 'left' : offsetLeft + 'px'});
						}
						
						
						//$(this).children('img').css({'max-width' : $(this).width() + 'px'});
						$(this).children('.close').css({'margin-left' : offsetClose});
						$(this).fadeIn(350, function(){ scaleImg ($('.pimg img.previewImgBig'), $(this).innerHeight() - $(this).find('.caption').outerHeight() -24); }).draggable({delay: 100});
						
						$('#container').append('<div class="dimDiv">&nbsp;</div>');
					});
				});
			});
		return false;
    });
	
	$('#container').on("click touchstart", '.pimg .close, div.dimDiv', function()
    {
        $(".pimg").fadeOut(250, function(){ $(this).remove(); $('.titleTagBox').remove(); $('#container div.dimDiv').remove(); });
		return false;
    });
};