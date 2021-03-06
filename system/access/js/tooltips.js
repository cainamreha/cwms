// Tooltips
(function($){

if(typeof($.toolTips) != "function"){
	
	$.fn.extend({

		// Titletag wieder herstellen
		showTitleTag: function() {
		
			var titleEle	= $(this);
			var titleVal	= titleEle.attr('title');
			var eleWidth	= titleEle.width();
			var eleHeight	= titleEle.height();
			
			titleEle.mouseover(function(e){
				titleEle.parents().each(function(i,e){
					$(e).restoreTitleTag();
				});
			});
			
			titleEle.click(function(e){
				titleEle.restoreTitleTag();
				return true;
			});
			
			tooltipID		= Math.random();
			
			if(typeof(titleVal) !== 'undefined' && titleVal !== false && titleVal != "" && titleVal != "[object HTMLInputElement]"){
				
				var windowSize	= $.getWindowSize();
				var xOffset		= parseInt(titleEle.offset().left);
				//var yOffset		= parseInt(titleEle.offset().top - $(window).scrollTop()); // yOffset um Scroll-Position verringern (da fixed)
				var yOffset		= parseInt(titleEle.offset().top + eleHeight - $(window).scrollTop()); // yOffset um Scroll-Position verringern (da fixed)
				var showAbove	= false;
				var showLeft	= false;
				var leftMargin	= "-30px";
				var showDelay	= 200;
				var displayTime	= 3000;
				
				if(titleVal.length > 200){
					displayTime	= 5000;
				}
									
				// Browser-Tooltip unterbinden (leeren)
				titleEle.attr('title', function(i, val) {
					var restore = false;
					$(this).bind("mouseDown",function(){
						restore = true;
					});
					if(restore){
						return val;
					}else{
						return '';
					}
				});
			
				// Anzeige horizontal
				// Falls am rechten Rand, TitleBox nach links schieben
				if(xOffset > parseInt(windowSize[0]) - 100){
					showLeft = true;
				}
				
				// Anzeige Vertical
				// Falls Pos vorgegeben
				if(titleEle.attr('data-titlepos') 
				&& titleEle.attr('data-titlepos') == "top"
				&& yOffset > 100
				){
					showAbove	= true;
				}
				
				// Falls am unteren Rand, TitleBox oben anzeigen
				if(yOffset > parseInt(windowSize[1]) - 100){
					showAbove	= true;
				}
				
				// Falls Anzeige oben
				if(showAbove){
					yOffset -= 75;
				}else{
					yOffset += 45;				
				}
				
				titleEle.attr('aria-tooltip', tooltipID);
				

				$('div.titleTagBox:not(.permanent)').hide();
				
				var ttBox		= $('<div id="titleTagID-' + tooltipID + '" class="titleTagBox" style="position:fixed; top: 0; left: 0; visibility:hidden; z-index:99999;">' + titleVal + '</div>');
				
				$('body').append(ttBox);
				
				var ttWidth 	= parseInt($('div.titleTagBox').width());
				var ttHeight	= parseInt($('div.titleTagBox').height());
									
				ttBox.hide().css('visibility', 'visible');
				
				// Falls Anzeige zu weit rechts, um Breite nach links verschieben
				if(showLeft || xOffset + ttWidth > windowSize[0]){
					xOffset = windowSize[0] - ttWidth - 50;
				}
				
				xOffset -= 15; // left margin
				
				// Falls Anzeige oberhalb, um Höhe nach oben verschieben
				if(showAbove){
					yOffset -= ttHeight - 25;
					yOffset -= eleHeight;
					$('div.titleTagBox').addClass('showAbove');
				}
				
				
				titleEle.parents().each(function(i,e){ setTimeout(function(){ $(e).restoreTitleTag(); },250); });
				
				// TitleTagBox einblenden und nach x sec wieder ausblenden
				ttBox.css({'top':yOffset + 'px','left':xOffset + 'px','z-index':'99999'}).delay(showDelay).fadeIn(150, function(){
				
					//Bei Mausbewegung TitleTag (vorzeitig) ausblenden
					var startX = parseInt(titleEle.pageX);
					var startY = parseInt(titleEle.pageY);
					var currX = startX;
					var currY = startY;
					$('body').mousemove(function(i){
						currX = parseInt(i.pageX);
						currY = parseInt(i.pageY);
						var diffX = Math.abs(startX - currX);
						var diffY = Math.abs(startY - currY);
						
						if(	diffX >= 60 || 
							diffY >= 30
						){
							startX = currX;
							startY = currY;
							displayTime = 0;
							ttBox.stop(true).fadeOut(10, function(){ $(this).restoreTitleTag(); });
						}
					});
				}).delay(displayTime).fadeOut(100);					
			}
		},

		// Titletag wieder herstellen
		restoreTitleTag: function() {
		
			var elem		= $(this);
			var tooltipID	= elem.attr('aria-tooltip');
			var titleVal	= "";
			var ttBox		= $('body').find('div.titleTagBox[id="titleTagID-' + tooltipID + '"]');
			
			if(typeof(titleVal = ttBox.html()) != "undefined" && titleVal != ""){
				ttBox.stop(true).fadeOut(10, function(){ ttBox.remove(); });
			}else{
				titleVal = elem.attr('title');
			}
			
			elem.attr('title', titleVal);
			return titleVal;
		}
	});

	$.extend({

		toolTips: function(enable) {
		
			// Tooltips ersetzen
			var tooltipID	= 0;
			
			$('body').off("mouseenter", '*[title]:not(.QuickColor)');
			
			// Ggf. ToolTips abschalten
			if(typeof(enable) != "undefined" && enable === false){
				return false;
			}
			
			// Funktion zum Ersetzen von Titleattributen
			$('body').on("mouseenter", '*[title]:not(.QuickColor)', function(e){
			
				e.preventDefault();				
				$.removeAllToolTips();
				$(this).showTitleTag();
			
			}).on("mouseleave", '*[title]', function(e){
				$(this).restoreTitleTag();
			});
			
			// Hovering von Titleattributen ausschalten
			$('body').on("mouseenter, mouseleave, hover", 'div.titleTagBox:not(.permanent)', function(e){
				e.preventDefault();
				var telem = $('*[aria-tooltip="' + $(this).attr('id').split("titleTagID-")[1] + '"]');
				telem.restoreTitleTag();
				return false;
			});
		},
		
		// Titletag wieder herstellen
		removeAllToolTips: function() {
		
			$('div.titleTagBox').each(function(idx, elem){
				
				var tooltipIDa	= $(elem).attr('id');
				
				if(typeof(tooltipIDa) != "undefined"){
					var tooltipID	= tooltipIDa.split('titleTagID-')[1];
					var titleVal	= "";
					if(typeof(titleVal = $(elem).html()) != "undefined"){
						$(elem).fadeOut(10, function(){ $(elem).remove(); });
						$('*[aria-tooltip="' + tooltipID + '"]').attr('title', titleVal);
					}
					return titleVal;
				}
			});
		}
	});
}
})(jQuery);
