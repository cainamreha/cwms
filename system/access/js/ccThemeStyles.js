// Theme styles
head.ready("ccInitScript", function(){

(function($){

	cc.getThemeStyles = function(){
	
		cc.cacheNoExt			= "?" + new Date().getTime();
		
		// Styles
		var ccStyles 			= $.cc_GetStyles() || (typeof(cc.feTheme.styles) != "undefined" ? Object.keys(cc.feTheme.styles).map(function (key) { return cc.feTheme.styles[key]; }) : "") || [];
		cc.defaultThemeStyles 	= [];
		var defaultStylesRow	= [];
		var index				= "";
		var indexOld			= "";
		var styleDef			= "";

		indexOld				= "";
		
		// Make styles strings
		$.each(ccStyles, function(k,v){
			
			index				= v.charAt(0);
			styleDef			= {title: v, classes: v.toString(), selector: 'div,p,span,a,img,table'};
			
			if(index == indexOld){
				defaultStylesRow.push(styleDef);
			}else{
				indexOld		= v.charAt(0);
				defaultStylesRow	= [];
				defaultStylesRow.push(styleDef);
				cc.defaultThemeStyles.push({title: indexOld + " (" + v.split("-")[0] + ")", items: defaultStylesRow});
			}
		});
		
		
		// Fonts
		var feFonts				= typeof(cc.feTheme.fonts) != "undefined" ? Object.keys(cc.feTheme.fonts).map(function (key) { return cc.feTheme.fonts[key]; }) : [] || [];
		cc.themeFonts 			= $.cc_GetFonts() || [];
		
		// Make fonts string
		if(feFonts.length){
			$.each(feFonts, function(i,f){
				if(f != ""){
					cc.themeFonts.push(f.toString() + '=' + f.toString() + ';');
				}
			});
		}
		cc.themeFonts.sort();
		cc.themeFonts	= cc.themeFonts.join("");

		
		// Icons
		var feIcons		= cc.feTheme.icons || {icons:"icons", icon:"icon-"};
		var iconClass	= feIcons["icons"] + " " + feIcons["icon"];
		feIcons			= Object.keys(feIcons).map(function (key) { return feIcons[key]; });
		cc.defaultIconpicker 		= [];
		var defaultIconpickerRow	= [];
		var icoLoop					= [];

		feIcons.shift();
		feIcons.shift();
		feIcons.shift();
		
		
		while(feIcons.length){
			icoLoop	= feIcons.splice(0,15);
			ic	= icoLoop.length;
			defaultIconpickerRow	= [];
			$.each(icoLoop, function(k,v){
				defaultIconpickerRow.push({iconclass: iconClass + v, title: iconClass + v});
				if(!--ic){
					cc.defaultIconpicker.push(defaultIconpickerRow);				
				}
			});
		}		
		
		// Icon effects
		cc.iconEffects	= [];
		if(cc.feTheme.grid
		&& cc.feTheme.grid.icoclass
		){
			var icoEff		= cc.feTheme.grid.icoclass.split(",");
			cc.iconEffects.push({title: 'None', value: ''});
			for(i=0; i < icoEff.length; i++){
				if(icoEff[i] !== undefined){
					cc.iconEffects.push({title: icoEff[i], value: 'ci-icon-wrap ci-icon-' + icoEff[i]});
				}
			}
		}
		
		
		// Colors
		var feColors		= typeof(cc.feTheme.colors) != "undefined" ? Object.keys(cc.feTheme.colors).map(function (key) { return cc.feTheme.colors[key]; }) : "" || [];
		cc.customPalette	= null;

		cc.constructColor = function(colorObj){
			//adapted from http://www.runtime-era.com/2011/11/grouping-html-hex-colors-by-hue-in.html
			var hex = colorObj.hex.substring(1);
			/* Get the RGB values to calculate the Hue. */
			var r = parseInt(hex.substring(0, 2), 16) / 255;
			var g = parseInt(hex.substring(2, 4), 16) / 255;
			var b = parseInt(hex.substring(4, 6), 16) / 255;
			
			/* Getting the Max and Min values for Chroma. */
			var max = Math.max.apply(Math, [r, g, b]);
			var min = Math.min.apply(Math, [r, g, b]);
			
			
			/* Variables for HSV value of hex color. */
			var chr = max - min;
			var hue = 0;
			var val = max;
			var sat = 0;

			
			if (val > 0) {
				/* Calculate Saturation only if Value isn't 0. */
				sat = chr / val;
				if (sat > 0) {
					if (r == max) {
						hue = 60 * (((g - min) - (b - min)) / chr);
						if (hue < 0) {
							hue += 360;
						}
					} else if (g == max) {
						hue = 120 + 60 * (((b - min) - (r - min)) / chr);
					} else if (b == max) {
						hue = 240 + 60 * (((r - min) - (g - min)) / chr);
					}
				}
			}
			colorObj.chroma = chr;
			colorObj.hue = hue;
			colorObj.sat = sat;
			colorObj.val = val;
			colorObj.luma = .3 * r + .59 * g + .11 * b;
			colorObj.red = r;
			colorObj.green = g;
			colorObj.blue = b;
			return colorObj;
		};
				
		cc.Color = function Color(hexVal) { //define a Color class for the color objects
			this.hex = hexVal;
		};

		if(feColors.length){
			feColors			= feColors.slice(0,39);
			cc.customPalette	= [];

			var colors = [];
			$.each(feColors, function (i, v) {
				var color = new cc.Color(v);
				cc.constructColor(color);
				colors.push(color);
			});
			//This will out put the colors as an array of color object, with HEX value, RGB value... etc.

			cc.sortColorsByHue = function (colors) {
				/* Sort by Hue. */
				return colors.sort(function (a, b) {
					return a.hue - b.hue;
				});
			};

			colors	= cc.sortColorsByHue(colors);
			
			//console.log(colors);
			
			$.each(colors, function(k,v){
				var hexVal	= v["hex"];
				cc.customPalette.push(hexVal.replace("#", ""), hexVal);
			});
		}
		
		return this;
	
	};

	
	$.cc_GetFonts	= function(){
	
		var fonts = [	"Andale Mono=andale mono,times;",
						"Arial=arial,helvetica,sans-serif;",
						"Arial Black=arial black,avant garde;",
						"Book Antiqua=book antiqua,palatino;",
						"Comic Sans MS=comic sans ms,sans-serif;",
						"Courier New=courier new,courier;",
						"Georgia=georgia,palatino;",
						"Helvetica=helvetica;",
						"Impact=impact,chicago;",
						"Symbol=symbol;",
						"Tahoma=tahoma,arial,helvetica,sans-serif;",
						"Terminal=terminal,monaco;",
						"Times New Roman=times new roman,times;",
						"Trebuchet MS=trebuchet ms,geneva;",
						"Verdana=verdana,geneva;",
						"Webdings=webdings;",
						"Wingdings=wingdings,zapf dingbats;"	
		];
		return fonts;
	};

	// Bootstrap 3.3.5 shortcodes
	$.cc_GetStyles	= function(){
	
		var styles = [
			"active","affix","alert","alert-danger","alert-dismissable","alert-dismissible","alert-info","alert-link","alert-success","alert-warning","arrow","badge","bg-danger","bg-info","bg-primary","bg-success","bg-warning","blockquote-reverse","bottom","bottom-left","bottom-right","breadcrumb","btn","btn-block","btn-danger","btn-default","btn-group","btn-group-justified","btn-group-lg","btn-group-sm","btn-group-vertical","btn-group-xs","btn-info","btn-lg","btn-link","btn-primary","btn-sm","btn-success","btn-toolbar","btn-warning","btn-xs","caption","caret","carousel","carousel-caption","carousel-control","carousel-indicators","carousel-inner","center-block","checkbox","checkbox-inline","clearfix","close","col-lg-1","col-lg-10","col-lg-11","col-lg-12","col-lg-2","col-lg-3","col-lg-4","col-lg-5","col-lg-6","col-lg-7","col-lg-8","col-lg-9","col-lg-offset-0","col-lg-offset-1","col-lg-offset-10","col-lg-offset-11","col-lg-offset-12","col-lg-offset-2","col-lg-offset-3","col-lg-offset-4","col-lg-offset-5","col-lg-offset-6","col-lg-offset-7","col-lg-offset-8","col-lg-offset-9","col-lg-pull-0","col-lg-pull-1","col-lg-pull-10","col-lg-pull-11","col-lg-pull-12","col-lg-pull-2","col-lg-pull-3","col-lg-pull-4","col-lg-pull-5","col-lg-pull-6","col-lg-pull-7","col-lg-pull-8","col-lg-pull-9","col-lg-push-0","col-lg-push-1","col-lg-push-10","col-lg-push-11","col-lg-push-12","col-lg-push-2","col-lg-push-3","col-lg-push-4","col-lg-push-5","col-lg-push-6","col-lg-push-7","col-lg-push-8","col-lg-push-9","col-md-1","col-md-10","col-md-11","col-md-12","col-md-2","col-md-3","col-md-4","col-md-5","col-md-6","col-md-7","col-md-8","col-md-9","col-md-offset-0","col-md-offset-1","col-md-offset-10","col-md-offset-11","col-md-offset-12","col-md-offset-2","col-md-offset-3","col-md-offset-4","col-md-offset-5","col-md-offset-6","col-md-offset-7","col-md-offset-8","col-md-offset-9","col-md-pull-0","col-md-pull-1","col-md-pull-10","col-md-pull-11","col-md-pull-12","col-md-pull-2","col-md-pull-3","col-md-pull-4","col-md-pull-5","col-md-pull-6","col-md-pull-7","col-md-pull-8","col-md-pull-9","col-md-push-0","col-md-push-1","col-md-push-10","col-md-push-11","col-md-push-12","col-md-push-2","col-md-push-3","col-md-push-4","col-md-push-5","col-md-push-6","col-md-push-7","col-md-push-8","col-md-push-9","col-sm-1","col-sm-10","col-sm-11","col-sm-12","col-sm-2","col-sm-3","col-sm-4","col-sm-5","col-sm-6","col-sm-7","col-sm-8","col-sm-9","col-sm-offset-0","col-sm-offset-1","col-sm-offset-10","col-sm-offset-11","col-sm-offset-12","col-sm-offset-2","col-sm-offset-3","col-sm-offset-4","col-sm-offset-5","col-sm-offset-6","col-sm-offset-7","col-sm-offset-8","col-sm-offset-9","col-sm-pull-0","col-sm-pull-1","col-sm-pull-10","col-sm-pull-11","col-sm-pull-12","col-sm-pull-2","col-sm-pull-3","col-sm-pull-4","col-sm-pull-5","col-sm-pull-6","col-sm-pull-7","col-sm-pull-8","col-sm-pull-9","col-sm-push-0","col-sm-push-1","col-sm-push-10","col-sm-push-11","col-sm-push-12","col-sm-push-2","col-sm-push-3","col-sm-push-4","col-sm-push-5","col-sm-push-6","col-sm-push-7","col-sm-push-8","col-sm-push-9","col-xs-1","col-xs-10","col-xs-11","col-xs-12","col-xs-2","col-xs-3","col-xs-4","col-xs-5","col-xs-6","col-xs-7","col-xs-8","col-xs-9","col-xs-offset-0","col-xs-offset-1","col-xs-offset-10","col-xs-offset-11","col-xs-offset-12","col-xs-offset-2","col-xs-offset-3","col-xs-offset-4","col-xs-offset-5","col-xs-offset-6","col-xs-offset-7","col-xs-offset-8","col-xs-offset-9","col-xs-pull-0","col-xs-pull-1","col-xs-pull-10","col-xs-pull-11","col-xs-pull-12","col-xs-pull-2","col-xs-pull-3","col-xs-pull-4","col-xs-pull-5","col-xs-pull-6","col-xs-pull-7","col-xs-pull-8","col-xs-pull-9","col-xs-push-0","col-xs-push-1","col-xs-push-10","col-xs-push-11","col-xs-push-12","col-xs-push-2","col-xs-push-3","col-xs-push-4","col-xs-push-5","col-xs-push-6","col-xs-push-7","col-xs-push-8","col-xs-push-9","collapse","collapsing","container","container-fluid","control-label","danger","disabled","divider","dl-horizontal","dropdown","dropdown-backdrop","dropdown-header","dropdown-menu","dropdown-menu-left","dropdown-menu-right","dropdown-toggle","dropup","embed-responsive","embed-responsive-16by9","embed-responsive-4by3","embed-responsive-item","fade","focus","form-control","form-control-feedback","form-control-static","form-group","form-group-lg","form-group-sm","form-horizontal","form-inline","h1","h2","h3","h4","h5","h6","has-error","has-feedback","has-success","has-warning","help-block","hidden","hidden-lg","hidden-md","hidden-print","hidden-sm","hidden-xs","hide","icon-bar","icon-next","icon-prev","img-circle","img-responsive","img-rounded","img-thumbnail","in","info","initialism","input-group","input-group-addon","input-group-btn","input-group-lg","input-group-sm","input-lg","input-sm","invisible","item","jumbotron","label","label-danger","label-default","label-info","label-primary","label-success","label-warning","lead","left","list-group","list-group-item","list-group-item-danger","list-group-item-heading","list-group-item-info","list-group-item-success","list-group-item-text","list-group-item-warning","list-inline","list-unstyled","mark","media","media-body","media-bottom","media-heading","media-left","media-list","media-middle","media-object","media-right","modal","modal-backdrop","modal-body","modal-content","modal-dialog","modal-footer","modal-header","modal-lg","modal-open","modal-scrollbar-measure","modal-sm","modal-title","nav","nav-divider","nav-justified","nav-pills","nav-stacked","nav-tabs","nav-tabs-justified","navbar","navbar-brand","navbar-btn","navbar-collapse","navbar-default","navbar-fixed-bottom","navbar-fixed-top","navbar-form","navbar-header","navbar-inverse","navbar-left","navbar-link","navbar-nav","navbar-right","navbar-static-top","navbar-text","navbar-toggle","next","open","page-header","pager","pagination","pagination-lg","pagination-sm","panel","panel-body","panel-collapse","panel-danger","panel-default","panel-footer","panel-group","panel-heading","panel-info","panel-primary","panel-success","panel-title","panel-warning","popover","popover-content","popover-title","pre-scrollable","prev","previous","progress","progress-bar","progress-bar-danger","progress-bar-info","progress-bar-striped","progress-bar-success","progress-bar-warning","progress-striped","pull-left","pull-right","radio","radio-inline","right","row","show","small","sr-only","sr-only-focusable","success","tab-content","tab-pane","table","table-bordered","table-condensed","table-hover","table-responsive","table-striped","text-capitalize","text-center","text-danger","text-hide","text-info","text-justify","text-left","text-lowercase","text-muted","text-nowrap","text-primary","text-right","text-success","text-uppercase","text-warning","thumbnail","tooltip","tooltip-arrow","tooltip-inner","top","top-left","top-right","visible-lg","visible-lg-block","visible-lg-inline","visible-lg-inline-block","visible-md","visible-md-block","visible-md-inline","visible-md-inline-block","visible-print","visible-print-block","visible-print-inline","visible-print-inline-block","visible-sm","visible-sm-block","visible-sm-inline","visible-sm-inline-block","visible-xs","visible-xs-block","visible-xs-inline","visible-xs-inline-block","warning","well","well-lg","well-sm",
		];
		return styles;
	};
	
})(jQuery);

});