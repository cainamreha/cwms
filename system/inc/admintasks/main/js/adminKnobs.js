/* adminKnobs */
(function($){

	$.createDashboardKnobs	= function(){
	
		// Dashboard knobs
		if(!$("#ccAdminDashboard #stats0").length){
			return false;
		}
		
		var tStats		= $("#stats0 table.stats tbody");
		var vToday		= tStats.find("tr:first-child td:last-child").text();
		var lYesterday	= tStats.find("tr:nth-child(2) td:first-child").text();
		var uYesterday	= tStats.find("tr:nth-child(2) td:last-child").text();
		var lOnline		= tStats.find("tr:nth-child(3) td:last-child").children(".visitorsOnline").attr('title');
		var vOnline		= tStats.find("tr:nth-child(3) td:last-child").children(".visitorsOnline").text();
		var uOnline		= tStats.find("tr:nth-child(3) td:last-child").children(".usersOnline").text();
		var rate		= uYesterday > 0 ? parseInt(vToday/uYesterday*100) : 0;
		var rateOnline	= vToday > 0 ? parseInt((vOnline - uOnline)/vToday*100) : 0;
		var bgCol		= "#F6F8FD";
		var fgCol		= rate > 100 ? "#93d835" : "#ff9a20";
		var fgColOnline	= rateOnline >= 50 ? "#93d835" : "#ff9a20";
		var knob		= '<table class="cc-table-centered cc-table-framed userStatKnobs">';
		knob			+=	'<tbody>';
		knob			+=	'<tr><td><input id="knob-visitors-today" class="knobInput cc-input-unstyled" type="text" value="' + rate + '" data-max="' + Math.ceil(rate/100)*100 + '" readonly="readonly" data-fgColor="' + fgCol + '" /><input type="hidden" class="knobInput-preval" value="0" /></td><td><input id="knob-visitors-online" class="knobInput cc-input-unstyled" type="text" value="' + rateOnline + '" data-max="' + Math.ceil(rateOnline/100)*100 + '" readonly="readonly" data-fgColor="' + fgColOnline + '" /><input type="hidden" class="knobInput-preval" value="0" /></td></tr>';
		knob			+=	'<tr><td><label>%&nbsp;' + lYesterday + '</label></td><td><label>%&nbsp;' + lOnline + '</label></td></tr>';
		knob			+=	'</tbody>';
		knob			+= '</table>';
		
		knob			= $(knob);
		
		$("#stats0").prepend(knob);
		
		// Init knobs
		$("#ccAdminDashboard .knobInput").knob({
			width:"50%",
			displayInput: true,
			readOnly: true,
			bgColor: bgCol,
			format: function(value){
				return value + '%';
			}
		});
		
		setTimeout(function(){
		
			// Animate knobs
			$({value: 0}).animate({value: rate}, {
				duration: 1000,
				easing:'swing',
				step: function(){
					$('#knob-visitors-today').val(this.value + '%').trigger('change');
					$('#knob-visitors-today').siblings('.knobInput-preval').val(rate);
				}
			});
			$({value: 0}).animate({value: rateOnline}, {
				duration: 1000,
				easing:'swing',
				step: function(){
					$('#knob-visitors-online').val(this.value + '%').trigger('change');
					$('#knob-visitors-online').siblings('.knobInput-preval').val(rateOnline);
				}
			});
			
			// Adjust panel size
			$("#stats30 .statDetails").css('min-height', $("#stats0").height() + 'px');
			//$(window).trigger('resize');
		
		},50);
	};

})(jQuery);
