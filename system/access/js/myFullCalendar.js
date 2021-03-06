// jquery-ui dialog
$(document).ready(function(){
	

});

// changeDataFcEvent
cc.changeDataFcEvent = function(event, delta, revertFunc){

	var calendar = $("#calendar").fullCalendar("getCalendar");
	var dateFormat = "DD. MMMM YYYY HH:mm";
	var dateStart = event.start.format(dateFormat);
	var dateEnd = dateStart;
	var dateEndCorr = "";
	if(event.end){
		dateEndCorr = event.end;
		if(event.end.format("HH:mm:ss") == "00:00:00"){ dateEndCorr = calendar.moment(event.end-900*1000); }
		dateEnd = dateEndCorr.format(dateFormat);
	}
	jConfirm(ln.movedata.replace("%s", "#" + event.id) + "\n\n\n<strong>" + event.title + "</strong>\n\n\n" + (cc.dataType == "planner" ? "<strong>" + ln.datestart + "</strong> &#9654;" : "") + dateStart + (cc.dataType == "planner" ? "\n\n<strong>" + ln.dateend + "</strong> &#9654; " + dateEnd : ""), ln.confirmtitle, function(result){
		if(result === true){
			dateStart	= event.start ? encodeURIComponent(event.start.format("YYYY-MM-DD HH:mm:ss")) : "";
			dateEnd = dateStart;
			if(event.end){
				dateEnd		= dateEndCorr ? encodeURIComponent(dateEndCorr.format("YYYY-MM-DD HH:mm:ss")) : "";
			}
			var targetUrl = cc.httpRoot + "/system/access/editModules.php?page=admin&action=editdata&mod=" + cc.dataType + "&id=" + event.id + "&dates=" + dateStart + "&datee=" + dateEnd + "&ajax=1";
			$.ajax({
				url: targetUrl.toString(),
				success: function(ajax){
					if(ajax != "1"){
						revertFunc();
						jAlert(ln.dberror, ln.alerttitle);
					}
				}
			});
		}else{
			revertFunc();
		}
	});
};

// jquery-ui dialog
(function($){

	// Category-/file-Namen ändern (dialog popup), falls Dialog-Formular vorhanden
	$.createDataDialog = function(formExt, title, dateStart, dateEnd, timeStart, timeEnd, catID, catName) {
	
		if(!$("#dialog-form-" + formExt).length) {
			return false;
		}
		
		var dialog = $("#dialog-form-" + formExt),
			newNameInput,
			allFields,
			dateFormat = cc.adminLang != "en" ? "DD-MM-YYYY" : "MM-DD-YYYY";
		
		function _setDialogVars() {
		
			moment.locale(cc.editLang);
			
			if(catID && dialog.find( 'select[name="news_cat"]' ).length) {
				dialog.find( 'select[name="news_cat"]' ).val(catID);
			}
			
			dialog.attr("data-getcontent","fullpage");
			var timeS = timeStart.split(":");
			
			dialog.find( 'input[name="news_date"]' ).datepicker("setDate", dateStart);
			dialog.find( 'select[name="hourcombo_start"]').val(Number(timeS[0]).toString());
			dialog.find( 'select[name="mincombo_start"]' ).val(Number(timeS[1]).toString());
			
			if(dateEnd && $( 'input[name="news_date_end"]' ).length) {
				if(timeEnd == "23:59"){timeEnd = "23:45"}
				var timeE = timeEnd.split(":");
				dialog.find( 'input[name="news_date_end"]' ).datepicker("setDate", dateEnd);
				dialog.find( 'select[name="hourcombo_end"]' ).val(Number(timeE[0]).toString());
				dialog.find( 'select[name="mincombo_end"]' ).val(Number(parseInt(timeE[1])).toString());
			}
		}
		
		function _checkDialogInputs() {
			var valid = true;
			var fHeader	= dialog.find('input[name="news_header"]');
			var fCat	= dialog.find('select[name="news_cat"]');
			var fDateS	= dialog.find('input[name="news_date"]');
			var fDateE	= dialog.find('input[name="news_date_end"]');
			
			if(fHeader.val() == ""){
				valid = false;
				_markInvalid(fHeader);
			}else{
				_markValid(fHeader);						
			}
			if(!fCat.val()){
				valid = false;
				_markInvalid(fCat);
			}else{
				_markValid(fCat);						
			}
			if(!fDateS.val()
			|| !moment(fDateS.val(), dateFormat).isValid()
			){
				valid = false;
				_markInvalid(fDateS);
			}else{
				_markValid(fDateS);						
			}
			if(fDateE.length){
				var fHourS	= dialog.find('select[name="hourcombo_start"]');
				var fHourE	= dialog.find('select[name="hourcombo_end"]');
				var fMinS	= dialog.find('select[name="mincombo_start"]');
				var fMinE	= dialog.find('select[name="mincombo_end"]');
				if(!fDateE.val()
				|| !moment(fDateE.val(), dateFormat).isValid()
				|| moment(fDateE.val() + "T" + fHourE.val() + ":" + fMinE.val(), dateFormat + "THH:mm") <= moment(fDateS.val() + "T" + fHourS.val() + ":" + fMinS.val(), dateFormat + "T" + "HH:mm")
				){
					valid = false;
					if(fDateE.val() == fDateS.val()){
						_markInvalid(fHourE);
						_markInvalid(fMinE);
					}else{
						_markInvalid(fDateE);
					}
				}else{
					_markValid(fDateE);						
					_markValid(fHourE);
					_markValid(fMinE);
				}
			}
			return valid;
		}
		
		function _markValid(field) {
			field.removeClass("invalid");
		}
		
		function _markInvalid(field) {
			field.addClass("invalid");
		}
		
		// Dialog initiieren
		$( "#dialog-form-" + formExt ).dialog({
			autoOpen: true,
			minWidth: Math.max(parseInt(($(window).width()) *0.5), 720),
			width: 720,
			position: { my: "center", at: "center center", of: window },
			modal: true,
			show: {
				effect: "scale",
				duration: 300,
				easing: "swing"
			},
			hide: {
				effect: "fadeOut",
				duration: 200,
				easing: "easeInOutExpo"
			},
			zIndex: 9500,
			title: title,
			dialogClass: "adminArea",
			open: function() {
				
				_setDialogVars();
				
				var submitBtn	= $(this).find('button[type="submit"]');
				
				submitBtn.bind("click", function(){
					return _checkDialogInputs();
				});
			},
			close: function() {
				//allFields.removeClass( "ui-state-error" ).val( "" );
			}
		});

	}; // Ende createDataDialog

})(jQuery);


// grabPageColors
cc.grabPageColors = function() {  
	
	//my colors array
	var colors = new Array();
	
	//get all elements
	$("*[class]").each(function() {
		if($(this).css("background-color") && $(this).css("background-color") != "transparent") { colors.push($(this).css("background-color")); }
		//if($(this).css("color")) { colors.push($(this).css("color")); }
		//if($(this).css("border-color")) { colors.push($(this).css("border-color")); }
	});
	
	//remove dupes and sort
	colors	= cc.arrayUnique(colors);
	
	//colors.sort();

	cc.eventColors		= [];
	cc.eventBGColors	= [];
	
	$.each(colors, function(i,e){
		if(!e.match(/^rgb\((0|255),\s*(0|255),\s*(0|255)\)$/)){
			cc.getEventHexColors(e);
		}
	});
	
	return cc.eventBGColors;
	
};


// hexDigits
cc.hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 	//Function to convert hex format to a rgb color


// getHexDuplet
cc.getHexDuplet = function(x) {
	return isNaN(x) ? "00" : cc.hexDigits[(x - x % 16) / 16] + cc.hexDigits[x % 16];
};


// getEventHexColors
cc.getEventHexColors = function(rgb) {
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	if(!rgb){
		return false;
	}
	var colSum = parseInt(rgb[1]) + parseInt(rgb[2]) + parseInt(rgb[3]);
	if(colSum <= 45 || colSum >= 720){
		return false;
	}
	var brightness = cc.colourBrightness(rgb[0]);
	if(brightness <= 167){
		cc.eventColors.push("#ffffff");
	}else{
		cc.eventColors.push("#333366");
	}
	var bgCol = "#" + cc.getHexDuplet(rgb[1]) + cc.getHexDuplet(rgb[2]) + cc.getHexDuplet(rgb[3]);
	cc.eventBGColors.push($.trim(bgCol.toString()));

	return true;

};


// colourBrightness
cc.colourBrightness = function(colour){

    var r,g,b,brightness;
    if (colour.indexOf("rgb") === 0) {
      colour = colour.match(/rgba?\(([^)]+)\)/)[1];
      colour = colour.split(/ *, */).map(Number);
      r = colour[0];
      g = colour[1];
      b = colour[2];
    } else if ('#' == colour[0] && 7 == colour.length) {
      r = parseInt(colour.slice(1, 3), 16);
      g = parseInt(colour.slice(3, 5), 16);
      b = parseInt(colour.slice(5, 7), 16);
    } else if ('#' == colour[0] && 4 == colour.length) {
      r = parseInt(colour[1] + colour[1], 16);
      g = parseInt(colour[2] + colour[2], 16);
      b = parseInt(colour[3] + colour[3], 16);
    }

    //brightness = (r * 299 + g * 587 + b * 114) / 1000;
    brightness = (r * 0.2126 + g * 0.7152 + b * 0.0722);

	//console.log([brightness,colour.join(",")]);

	return brightness;

};
