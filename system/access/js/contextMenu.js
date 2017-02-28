// Context menu
head.ready("contextmenu", function(){

$(document).ready(function(){
	
	$.contextMenu({
        selector: '*[data-menu="context"]:not(.innerEditDiv.current)',
		zIndex: 9999,
		reposition: true,
		animation: {duration: 300, show: "fadeIn", hide: "fadeOut"},
		events: {
			hide: function(menTrigger) {
				this.removeClass("activeListItem");
				//console.log("Selector: " + menTrigger.selector);
			}
		},
		build: function(menTrigger, e) {
            // this callback is executed every time the menu is to be shown
            // its results are destroyed every time the menu is hidden
            // e is the original contextmenu event, containing e.pageX and e.pageY (amongst other data)
			if(menTrigger.find(".current").length){
				return false;
			}
			
			function getItems(){
			
				var items 	= {};
				var sep		= "---";
				var target	= menTrigger.attr('data-target');
				
				if(typeof($.removeAllToolTips) == "function"){
					$.removeAllToolTips(); // Zunächst ggf. Titletags wieder herstellen
				}
				
				// Ggf. mehrere Targets
				targetPanels	= target.split(",");
			
				$.each(targetPanels, function(i, target){
				
					$('*[data-id="' + target + '"]').find('*[data-contextmenuitem="true"]:visible, *[data-menuitem="true"]:visible:not([data-contextmenuitem="false"])').each(function(idx, elem){
					
						var it			= $(elem);
						
						// Falls Element selbst die Item-Informationen enthält
						if(!it.attr('data-contextmenuitem')
						&& !it.attr('data-menuitem')
						){
							it			= it.find('*[data-menuitem]:visible');
						}
						
						// Sonst unter Kindelementen suchen
						if(!it.length){
							it	= $(elem).children('a') || $(elem).children('img');
						}
						
						if(it.hasClass('directedit') || it.hasClass('apply')){
							items['sep'+idx]	= sep;
						}
					
						var title		= it.attr('data-menutitle') || it.attr('data-title') || it.attr('title');
						var className	= it.attr('data-itemclass') || it.children().attr('data-itemclass') || it.children().attr('class') || it.attr('class');
						var iconPrefix	= className;
						var iconClass	= "";
						var classSplit	= [];
						var defs		= {};
						
						if(className.indexOf("icons cc-icon-") > -1){
							classSplit	= className.split('icons cc-icon-');
							className	= classSplit[1] || it.attr('class') || "undefined";
							iconPrefix	= className;
							iconClass	= iconPrefix + ' cc-icon-' + iconPrefix
						}else{
						if(className.indexOf("cc-icon-") > -1){
							classSplit	= className.split('cc-icon-');
							className	= classSplit[1] || it.attr('class') || "undefined";
							iconPrefix	= className;
							iconClass	= iconPrefix + ' cc-icon-' + iconPrefix
						}else{
						if(className.indexOf("icon-") > -1){
							iconPrefix	= className.replace(/icon-/g, "cc-icon-");
							iconClass	= className + ' ' + iconPrefix
						}}}
						
						defs			= {name: title, icon: iconClass, titleTag: title, callback: function(key, opt){ it[0].click(); }};
						
						items[className+idx]	= defs;
					});

					// Separator between target panel groups
					if(i < targetPanels.length -1){
						items['sepgroup'+i]	= sep;
					}
				
				});
				return items;
			}
			
			var myItems = getItems();
			
			
			$(".activeListItem").removeClass("activeListItem");
			menTrigger.addClass("activeListItem");
			
            return {
                callback: function(key, options) {

					var m = "clicked: " + options.icon;
					// window.console && (console.log(m)) || alert(m);
					return false;
				},
                items: myItems
            };
        }
    }); // Ende contextMenu

});

});
