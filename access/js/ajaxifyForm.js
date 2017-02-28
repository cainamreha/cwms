(function(cc){

	cc.ajaxifyForm	= function(formTag, validation, classErr){

		function _updateQueryString(key, value, url) {
			if (!url) url = window.location.href;
			var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
				hash;

			if (re.test(url)) {
				if (typeof value !== "undefined" && value !== null)
					return url.replace(re, "$1" + key + "=" + value + "$2$3");
				else {
					hash = url.split("#");
					url = hash[0].replace(re, "$1$3").replace(/(&|\?)$/, "");
					if (typeof hash[1] !== "undefined" && hash[1] !== null) 
						url += "#" + hash[1];
					return url;
				}
			}
			else {
				if (typeof value !== "undefined" && value !== null) {
					var separator = url.indexOf("?") !== -1 ? "&" : "?";
					hash = url.split("#");
					url = hash[0] + separator + key + "=" + value;
					if (typeof hash[1] !== "undefined" && hash[1] !== null) 
						url += "#" + hash[1];
					return url;
				}
				else
					return url;
			}
		};

		function _ajaxSubmitForm(formEle, formTag) {

			// Grab the information from the form needed for the Ajax request.
			var formAction	= formEle.attr("action"); // e.g. "/somethings"
			var formMethod	= formEle.attr("method"); // e.g. "post"
			var formData	= formEle.serializeArray(); // grabs the form data and makes your params nicely structured!
			var formBtn		= formEle.find('button[type="submit"]'); // submit button

			formAction		= _updateQueryString("ajax", "true", formAction);
			
			var ccGetFormErrorMsg	= function(){
				if(formEle.closest(".form-minimal").length){
					formEle.find(".formErrorBox").addClass(classErr).hide();
				}else{
					formEle.find(".formErrorBox").addClass(classErr).hide().fadeIn(800);
				}
			};

			// Prevent click on disabled button
			formBtn.filter('.disabled').click(function(e) {
				e.preventDefault();
				return false;
			});

			// Button load
			formBtn.not(".disabled").addClass("disabled").append('&nbsp;&nbsp;<span class="icons icon-refresh icon-spin"></span>');
			
			// Make the Ajax request, which will hit the "create" action in the "somethings" controller
			$.ajax({
			  url:  formAction,
			  type: formMethod,
			  data: formData,
			  cache: false,
			  success: function(ajax){

				// code for modern browsers
				var parser		= new DOMParser();
				var xmlDoc		= parser.parseFromString(ajax,"text/html");
				var newConDiv	= $(xmlDoc).find(formTag);

				if(!newConDiv.length){
					ccGetFormErrorMsg();
					return false;
				}
							
				var newDom		= $('<div id="ccNewDomFE"></div>').append(newConDiv);

				newConDiv		= newDom.children(formTag);					

				formEle.fadeTo(200, 0, function(){
					
					// replace formEle
					formEle.replaceWith(newConDiv).fadeTo(900,1);
				});				  
			  },
			  error: function(ajax){
				  ccGetFormErrorMsg();
			  }
			});
		};
		
		
		// Form submission
		if(validation == "true"){
		
			_ajaxSubmitForm($(formTag), formTag);
		
		}else{
			
			$("body").on("submit", formTag, function(e) {

				if(!window.DOMParser){
					return true;
				}

				// Prevent an entire page load (or reload).
				e.preventDefault();
			
				_ajaxSubmitForm($(this), formTag);
			
			});
		}
	};
})(conciseCMS);
