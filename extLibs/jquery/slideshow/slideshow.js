/* Simple fader gallery */
(function($){

	$.fn.simpleFaderSlideshow = function(settings){
	
		var config = {
			'timeOut': 3000,
			'speed': 'normal'
		};
	
		var slideshowIntervals	= [];
		var item	= "";
		var img		= "";
		var imgH	= "";
		
		_slideshowStep	= function(elem, init){
		
			item	= elem.children().eq(0);
			
			if(init){
				img		= item.children('img');
				imgH	= img.height();        
				item.closest('.slideshow').css('height',imgH + 'px');
				return false;
			}
			
			item.fadeOut(config['speed'], function(){
				item.next().addClass('active');
				item.removeClass('active').appendTo(elem).fadeTo(0, 0);
			});
			
			item.next().fadeTo(1, config['speed'], function(i,e){
				img		= $(e).children('img');
				imgH	= img.height();        
				item.closest('.slideshow').css('height',imgH + 'px');
			});
			
		};

		if(settings) $.extend(config, settings);
		
		$(this).each(function(i,e){

			if(slideshowIntervals[i]){
				clearInterval(slideshowIntervals[i]);
			}
		
			var sfElem 	= $(e);
			var ssItems	= sfElem.children('.cc-gallery-item');
			
			ssItems.each(function(i,item){
				img	= $(item).children('img');
				if(img.attr('data-src')){
					img.attr('src', img.attr('data-src'));
				}
			});

			ssItems.not(':first').fadeTo(0, 0);
			ssItems.filter(':first').show().addClass('active');

			_slideshowStep(sfElem, true);
			
			slideshowIntervals[i]	= setInterval(function(){
				_slideshowStep(sfElem);
			}, config['timeOut']);
		
		});
		
		return this;
	};

})(jQuery);
