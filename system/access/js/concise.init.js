// ***********************************************
// *****          Concise WMS init           *****
// ***********************************************


var conciseCMS			= conciseCMS || {regSess: false};
var ccPoolFunctions		= new Array();
var ccInitFunctions		= new Array();
var ccPluginFunctions	= new Array();
var ccLoadingImg		= new Image();
var ccWaitImg			= new Image();
var ccInitCount			= 0;
var ccInitSuccess		= false;
var ccEditorsSetup		= false;
var ccReloadEditors		= false;
var ccIsHandlingError	= false;


// graceful error handling
conciseCMS.ccHandleError = function(errmes, file, line) {
    // if successful: ccIsHandlingError = false;
	if(typeof(jAlert) == "function"
	&& errmes.indexOf("History") !== 0
	&& errmes.indexOf("sortable") < 0
	){
		jAlert(conciseCMS.ln.dberror, conciseCMS.ln.alerttitle);
	}
	console.log(errmes + " (script: " + file + ", line: " + line + ")");
};

window.onerror = function(errmes, file, line) {
    if (!ccIsHandlingError) {
        ccIsHandlingError = true;
        conciseCMS.ccHandleError(errmes, file, line);
    }
};

// Concise functions
(function(cc) {

	// Array unique
	cc.arrayUnique = function(array) {
		return $.grep(array, function(el, index) {
			return index === $.inArray(el, array);
		});
	};
	
	cc.idCounter	= 0;
	
	// Unique id
	cc.uniqueId = function(prefix) {
	  var id = ++cc.idCounter + '';
	  return prefix ? prefix + id : id;
	};

	// get resource url
	cc.getResourceUrl = function(url){
		var root = url.indexOf(cc.httpRoot.toString()) === 0 ? "" : cc.httpRoot + '/';
		return root + url;
	};

	// check for mobile phone
	cc.isPhone = function(){
		var vw = parseInt($(window).width());
		return vw <= 768;
	};

	// Loading images
	cc.setLoadingImages = function(fe){
		if(fe){
			ccLoadingImg.src	= cc.httpRoot + "/themes/" + cc.activeTheme + "/img/page-spinner.svg";
		}else{
			ccLoadingImg.src	= cc.httpRoot + "/system/themes/" + cc.adminTheme + "/img/page-spinner.svg";
		}
		ccWaitImg.src		= cc.httpRoot + "/system/themes/" + cc.adminTheme + "/img/page-spinner.svg";
	};

})(conciseCMS);


(function($) {

	// Container
	cc.container 	= $('div#container');

	cc.containerHeight	= cc.container.height();
	cc.windowScrollTop	= $(window).scrollTop();
	
	// If invisible container with scroll pos
	if(cc.container.attr('data-scroll')){
	
		cc.windowScrollTop	= cc.container.attr('data-scroll');
		
		cc.container.fadeTo(0,0,function(){
			$(window).scrollTop(cc.windowScrollTop);
			cc.container.fadeTo(0,0).delay(300).fadeTo(300,1);
		});
	}
	
	$(window).scroll(function(){
		cc.windowScrollTop	= $(window).scrollTop();
	});
	
	// Loading
	// Loadingbar einblenden
	$.getWaitBar = function(callback) {
	
		$.getDimDiv();
		
		var loadingDiv		= '<div class="loadingDiv loadingDiv-text tempHint"><p>' + ln.loading + '</p></div>';
		var loadingDivImg	= '<div class="loadingDiv loadingDiv-image tempHint"><img src="' + ccLoadingImg.src + '" alt="loading" class="loading" /></div>';
		
		var loadingStatus	= $('body').append(loadingDiv, loadingDivImg);
		
		// Callback
		if(loadingStatus && typeof(callback) == "function"){
			return callback();
		}
		return loadingStatus;
	},

	// Loadingbar ausblenden
	$.removeWaitBar = function(rmDD) {	
		$('.loadingDiv').remove();
		if(rmDD !== false){
			$.removeDimDiv();
		}
		return true;
	},

	// Dimdiv einblenden
	$.getDimDiv = function() {
		if(!$('.dimDiv').length){
			var bodyHeight = parseInt($(document).height());
			cc.container.append('<div class="dimDiv" style="min-height:' + bodyHeight + 'px;"></div>');
			return true;
		}
		return false;
	},

	// Dimdiv ausblenden
	$.removeDimDiv = function() {	
		$('.dimDiv').remove();
		return true;
	},

	// ListBox ausblenden
	$.removeListBox = function() {
		var listBoxDom	= cc.container.children('div.listBoxWrapper');
		if(listBoxDom.length){
			$(listBoxDom).remove();
		}
		return true;
	};	
	
	// FE-editing refresh page
	$('body:not(.admin)').off("click", '#cc-fePanel .feMode');
	$('body:not(.admin)').on("click", '#cc-fePanel .feMode', function(e){
		
		e.preventDefault();
		
		var newDoc,
			container,
			scrollPos	= cc.windowScrollTop,
			targetUrl	= window.location.origin + window.location.pathname + $(this).attr('href');
		
		$.getWaitBar();
		$('div#container').fadeOut(300);
		$.get(targetUrl, function(data){
			newDoc = document.open("text/html", "replace");
			newDoc.charset = "UTF-8";
			newDoc.write(data.replace('<div id="container">', '<div id="container" style="opacity:0;" data-scroll="' + scrollPos + '">'));
			newDoc.close();
		}).fail(function(){
			$.refreshPage();
		}).done(function(data){
		});
		return false;
	});

})(jQuery);


/* ajaxSetup */
(function($) {

	$.runAjaxSetup = function(){

		$.ajaxSetup({
			cache: false,
			statusCode: {
				// No access
				403: function() {
					$.regenerateSession();
					return false;
				},
				// Not found
				404: function() {
					$.regenerateSession();
					return false;
				},
				// Not Acceptable (unknown task)
				406: function() {
					jAlert(ln.dberror, ln.alerttitle);		
					setTimeout(function() {
						document.location.href = cc.httpRoot + "/admin";
					}, 3000);
					return false;
				}
			},
			error: function(jqXHR, exception) {
				if (jqXHR.status === 0) {
					console.log('Not connect.\n Verify Network.');
				} else if (jqXHR.status == 404) {
					console.log('Requested page not found. [404]');
				} else if (jqXHR.status == 500) {
					console.log('Internal Server Error [500].');
				} else if (exception === 'parsererror') {
					console.log('Requested JSON parse failed.');
				} else if (exception === 'timeout') {
					console.log('Time out error.');
				} else if (exception === 'abort') {
					console.log('Ajax request aborted.');
				} else {
					console.log('Uncaught Error.\n' + jqXHR.responseText);
				}
			}
		});
	},

	// getJSONResponse
	$.getJSONResponse = function(ajax){

		var resArr = ajax;
		if(typeof(resArr.alert) != "undefined"){
			jAlert(resArr.alert, ln.alerttitle);
		}else{
			jAlert(ln.dberror, ln.alerttitle);
		}
		return false;
	};
})(jQuery);


// Add Init function
(function($) {

	$.addInitFunction = function(fnObj, plugin){
	
		if(plugin === true){
			if(!$.arrayAssocFind(ccPluginFunctions, fnObj.name)){
				ccPluginFunctions.push(fnObj);
			}
		}else{
			if(!$.arrayAssocFind(ccInitFunctions, fnObj.name)){
				ccInitFunctions.push(fnObj);
			}
		}
	};	
})(jQuery);


// Init functions
(function($) {

	$.addInitFunctions = function(pool){
	
		$.each(pool, function(index, plugin){
			
			if(plugin.name){
				$.addInitFunction({name: plugin.name, params: plugin.params});	// add pooled custom init functions
			}
		
		});	
	};	
})(jQuery);



// Init functions ausführen
(function($) {

	$.executeInitFunctions = function(callback){		
	
		head.ready(function(){
		
			// Kurzen Timeout setzen, da sonst z.T. nicht gefired wird (tinyMCE bug(?))
			setTimeout(function(){

				// Zusätzliche Methoden/Plugins bestimmen
				// globale Funktionen hinzufügen
				$.addInitFunctions($.getInitFunctions());
				
				ccInitFunctions	= $.merge( $.merge( [], ccInitFunctions ), ccPluginFunctions );		
			
				ccInitFunctions	= $.arrayUnique(ccInitFunctions);
				
				var fncCnt		= ccInitFunctions.length;

				$.each(ccInitFunctions, function(index, fcn){
					
					functionName = eval(fcn.name);
					
					// Objekt initialisieren
					if($.isFunction(functionName)){
						if(Array.isArray(fcn.params)){
							functionName.apply(this, fcn.params);
						}else{
							functionName(fcn.params);
						}
					}
					// Wenn alle Funktion geladen sind
					if (!--fncCnt){
						ccInitSuccess = true;
						ccInitCount++;
						if(callback
						&& $.isFunction(callback)
						){
							callback();
						}else{
							$.postPageLoadActions();
						}
					}
				});
				
				return true;
		
			}, 0.05);
		});
	};
})(jQuery);


// Array (assoc) unique
(function($) {

	$.arrayUnique = function(array){		
	
		var result = [];
		$.each(array, function(i, e) {
			var dupl = $.arrayAssocFind(result, e.name);
			if(!dupl)result.push(array[i]);
		});
		return result;
	};
})(jQuery);


// Array find
// Array unique
(function($) {

	$.arrayAssocFind = function(array, name){	
	
		var found = false;
		$.each(array, function(i, e) {
			if(typeof(e.name) != "undefined" && e.name == name){
				found = true;
			}
		});
		return found;
	};
})(jQuery);


// Ermittlung der Fenstergröße
(function($){
	$.getWindowSize	= function() {
		
		var windowWidth = 0, windowHeight = 0;
	 
		if( typeof( window.innerWidth ) == 'number' ) {
			//Non-IE
			windowWidth = window.innerWidth;
			windowHeight = window.innerHeight;
		} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
			//IE 6+ in 'standards compliant mode'
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
			//IE 4 compatible
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}
		return [ windowWidth, windowHeight ];
	};
})(jQuery);
	

// Ermittlung der Elementbreite
(function($){
	$.fn.getElementWidth	= function() {
		var conElem	= $(this);
		if(typeof(conElem[0]) != "undefined"
		&& typeof(conElem[0].getBoundingClientRect) == "function"
		){
			return conElem[0].getBoundingClientRect().width;
		}		
		return conElem.width();	
	},	

	// Ermittlung der Elementhöhe
	$.fn.getElementHeight	= function(conElem) {
		var conElem	= $(this);
		if(typeof(conElem[0]) != "undefined"
		&& typeof(conElem[0].getBoundingClientRect) == "function"
		){
			return conElem[0].getBoundingClientRect().height;
		}		
		return conElem.height();	
	};
})(jQuery);


// Loading-Image
(function($) {

    $.fn.loading = function (callback) {
		if($(this).is('img')){
			$(this).prop('src', ccWaitImg.src).waitForImages(function(){
				if($.isFunction(callback)) {
					callback();
				}
			});
		}else{
			var elem	= $(this).hasClass('cc-icons') ? $(this) : $(this).find('.cc-icons');
			elem.addClass('cc-icon-loading');
			if($.isFunction(callback)) {
				callback();
			}
		}
    },
	
    $.fn.loadingRemove = function (src, callback) {
		if($(this).is('img')){
			$(this).prop('src', src).waitForImages(function(){
				if($.isFunction(callback)) {
					callback();
				}
			});
		}else{
			var elem	= $(this).hasClass('cc-icons') ? $(this) : $(this).find('.cc-icons');
			elem.removeClass('cc-icon-loading');
			if($.isFunction(callback)) {
				callback();
			}
		}
    },
	
    $.refreshPage = function (hash) {
		var url	= document.location.href.split('?')[0];
		if(!hash){
			url	= url.split('#')[0];
		}
		document.location.href = url;
    };
}(jQuery));


// Editor
(function($){

	// Add editors
	$.mceAddEditors = function(textareas){
	
		// add tinyMCE
		if(typeof(textareas) != "object"
		|| typeof(tinymce) != "object"
		){
			return false;
		}
		
		textareas.each(function(i,e){
			var taID	= $(e).attr('id');
			if(typeof(edi) == "object"){
				//tinymce.remove('#' + taID);
				tinymce.execCommand('mceRemoveEditor',true, taID);
			}
			tinymce.execCommand('mceRemoveEditor',true, taID);
			tinymce.execCommand('mceAddEditor',true, taID);
		});
	
		return true;
	};

	// Remove editors
	$.mceRemoveEditors = function(textareas){
	
		// add tinyMCE
		if(typeof(textareas) != "object"
		|| typeof(tinymce) != "object"
		){
			return false;
		}

		textareas.each(function(i,e){
			var taID	= $(e).attr('id');
			var edi		= tinymce.editors[taID];
			if(typeof(edi) == "object"){
				//tinymce.remove('#' + taID);
				tinymce.execCommand('mceRemoveEditor',true, taID);
			}
		});
		return true;
	};

	// Editor toggeln
	$.toggleEditor = function(ed, show){
	
		var edID	= ed;
		
		if(typeof(ed) == "object"){
			edID	= ed.id;
		}

		var trigger		= '.toggleEditor[data-target="' + edID + '"]';
		var textarea	= $('textarea[id="' + edID + '"]').not('.cc-always-hide');
		
		$('body').off("click", trigger);
		$('body').on("click", trigger, function(){
			var edIn	= tinymce.EditorManager.editors[edID];

			if(typeof(edIn) == "undefined"){
				return false;
			}
			if(show
			|| edIn.isHidden()){
				edIn.show();
				textarea.hide();
				cc.openEditors++;
			}else{
				edIn.hide();
				textarea.show();
				cc.openEditors--;
			}
		});
		/*
		ed.on("change",function(e){
			cc.conciseChanges = true;
		});
		*/
	};

	// destroy editors
	$.destroyEditors = function(ed, show){
	
		if(typeof($.myTinyMCE) == "function"){
			$.myTinyMCE		= null;
		}
		if(typeof($.myCodeMirror) == "function"){
			$.myCodeMirror	= null;
		}		
		
	};
})(jQuery);


/*! waitForImages jQuery Plugin - v1.5.0 - 2013-07-20
* https://github.com/alexanderdickson/waitForImages
* Copyright (c) 2013 Alex Dickson; Licensed MIT */
(function ($) {
    // Namespace all events.
    var eventNamespace = 'waitForImages';

    // CSS properties which contain references to images.
    $.waitForImages = {
        hasImageProperties: ['backgroundImage', 'listStyleImage', 'borderImage', 'borderCornerImage', 'cursor']
    };

    // Custom selector to find `img` elements that have a valid `src` attribute and have not already loaded.
    $.expr[':'].uncached = function (obj) {
        // Ensure we are dealing with an `img` element with a valid `src` attribute.
        if (!$(obj).is('img[src!=""]')) {
            return false;
        }

        // Firefox's `complete` property will always be `true` even if the image has not been downloaded.
        // Doing it this way works in Firefox.
        var img = new Image();
        img.src = obj.src;
        return !img.complete;
    };

    $.fn.waitForImages = function (finishedCallback, eachCallback, waitForAll) {

        var allImgsLength = 0;
        var allImgsLoaded = 0;

        // Handle options object.
        if ($.isPlainObject(arguments[0])) {
            waitForAll = arguments[0].waitForAll;
            eachCallback = arguments[0].each;
			// This must be last as arguments[0]
			// is aliased with finishedCallback.
            finishedCallback = arguments[0].finished;
        }

        // Handle missing callbacks.
        finishedCallback = finishedCallback || $.noop;
        eachCallback = eachCallback || $.noop;

        // Convert waitForAll to Boolean
        waitForAll = !! waitForAll;

        // Ensure callbacks are functions.
        if (!$.isFunction(finishedCallback) || !$.isFunction(eachCallback)) {
            throw new TypeError('An invalid callback was supplied.');
        }

        return this.each(function () {
            // Build a list of all imgs, dependent on what images will be considered.
            var obj = $(this);
            var allImgs = [];
            // CSS properties which may contain an image.
            var hasImgProperties = $.waitForImages.hasImageProperties || [];
            // To match `url()` references.
            // Spec: http://www.w3.org/TR/CSS2/syndata.html#value-def-uri
            var matchUrl = /url\(\s*(['"]?)(.*?)\1\s*\)/g;

            if (waitForAll) {

                // Get all elements (including the original), as any one of them could have a background image.
                obj.find('*').addBack().each(function () {
                    var element = $(this);

                    // If an `img` element, add it. But keep iterating in case it has a background image too.
                    if (element.is('img:uncached')) {
                        allImgs.push({
                            src: element.attr('src'),
                            element: element[0]
                        });
                    }

                    $.each(hasImgProperties, function (i, property) {
                        var propertyValue = element.css(property);
                        var match;

                        // If it doesn't contain this property, skip.
                        if (!propertyValue) {
                            return true;
                        }

                        // Get all url() of this element.
                        while (match = matchUrl.exec(propertyValue)) {
                            allImgs.push({
                                src: match[2],
                                element: element[0]
                            });
                        }
                    });
                });
            } else {
                // For images only, the task is simpler.
                obj.find('img:uncached')
                    .each(function () {
                    allImgs.push({
                        src: this.src,
                        element: this
                    });
                });
            }

            allImgsLength = allImgs.length;
            allImgsLoaded = 0;

            // If no images found, don't bother.
            if (allImgsLength === 0) {
                finishedCallback.call(obj[0]);
            }

            $.each(allImgs, function (i, img) {

                var image = new Image();

                // Handle the image loading and error with the same callback.
                $(image).on('load.' + eventNamespace + ' error.' + eventNamespace, function (event) {
                    allImgsLoaded++;

                    // If an error occurred with loading the image, set the third argument accordingly.
                    eachCallback.call(img.element, allImgsLoaded, allImgsLength, event.type == 'load');

                    if (allImgsLoaded == allImgsLength) {
                        finishedCallback.call(obj[0]);
                        return false;
                    }

                });

                image.src = img.src;
            });
        });
    };
}(jQuery));


// Cookies

/**
 * jQuery Cookie Plugin
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */

/**
 * Create a cookie with the given name and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String name The name of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

(function($) {
	$.cookie = function(key, value, options) {

		// key and at least value given, set cookie...
		if (arguments.length > 1 && (!/Object/.test(Object.prototype.toString.call(value)) || value === null || value === undefined)) {
			options = $.extend({}, options);

			if (value === null || value === undefined) {
				options.expires = -1;
			}

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}

			value = String(value);

			return (document.cookie = [
				encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path ? '; path=' + options.path : '',
				options.domain ? '; domain=' + options.domain : '',
				options.secure ? '; secure' : ''
			].join(''));
		}

		// key and possibly options given, get cookie...
		options = value || {};
		var decode = options.raw ? function(s) { return s; } : decodeURIComponent;

		var pairs = document.cookie.split('; ');
		for (var i = 0, pair; pair = pairs[i] && pairs[i].split('='); i++) {
			if (decode(pair[0]) === key) return decode(pair[1] || ''); // IE saves cookies with empty string as "c; ", e.g. without "=" as opposed to EOMB, thus pair[1] may be undefined
		}
		return null;
	};
})(jQuery);
