head.ready(function(){

	$(document).ready(function(){
		$.myDatepicker();
	});
});

	
if(typeof($.myDatepicker) != "function"){

	$.extend({

	myDatepicker: function() {

	$(function() {
		if($(".datepicker").length) {
			var dayNFull	= $("#daynames").attr('value').split(',');
			var dayNAbb		= $("#daynames").attr('alt').split(',');
			var monthNFull	= $("#monthnames").attr('value').split(',');
			var monthNAbb	= $("#monthnames").attr('alt').split(',');
			$(".datepicker").datepicker({
				showOn: "button",
				buttonImage: conciseCMS.httpRoot + "/system/themes/" + conciseCMS.adminTheme + "/img/calendar.png",
				buttonImageOnly: true,
				dateFormat: "dd.mm.yy",
				firstDay: 1,
				dayNames: dayNFull,
				dayNamesMin: dayNAbb,
				dayNamesShort: dayNAbb,
                monthNames: monthNFull,
                monthNamesShort: monthNAbb
			});
		}
	});
	
	
	/* Datepicker Daten-Archiv */
	$(function() {
		if($("#datepicker").length) {
			busyDaysFetch = new Array();
			busyDaysFetch = $("#busydates").attr('value').split(',');
			busyDays = new Array();
			for(var i=0; i<busyDaysFetch.length; i++) {
				busyDays.push(busyDaysFetch[i]);
			}
			var dayNFull	= $("#daynames").attr('value').split(',');
			var dayNAbb		= $("#daynames").attr('alt').split(',');
			var monthNFull	= $("#monthnames").attr('value').split(',');
			var monthNAbb	= $("#monthnames").attr('alt').split(',');
			var currMonStr	= $("#busydates").attr('alt');
			var selDate		= $("#currDate").attr('value').split(',');
			var date		= new Date();
			if($("#datepicker").hasClass("planner_calendar")){
				var minDate	= new Date(2011, parseInt(date.getMonth()+1)-1, 1);
			} else {
				var minDate = new Date(2011, 1-1, 1);
			}
			
			$("#datepicker").datepicker({
				//dateFormat: "dd.mm.yy",
				firstDay: 1,
				dayNames: dayNFull,
				dayNamesMin: dayNAbb,
				dayNamesShort: dayNAbb,
                monthNames: monthNFull,
                monthNamesShort: monthNAbb,
				changeMonth: false,
				changeYear: false,
                prevText: '&#x3c;&#x3c;',
                nextText: '&#x3e;&#x3e;',
                currentText: currMonStr,
                minDate: minDate,
				defaultDate: new Date(selDate[0], selDate[1]-1, selDate[2]),
				beforeShowDay: function(date){
									var yy = date.getFullYear();
									var mm = "0"+ parseInt(date.getMonth()+1);
									var dd = "0"+date.getDate();
									var currDate = yy+'-'+mm.slice(-2)+'-'+dd.slice(-2);
									var pickedDate = $("#datepicker").siblings('input[name="datepicker_pickedDate"]').attr('value')+1;
									var addClass = "";
									//alert(currDate);
									if(pickedDate == currDate) {
										addClass = " pickedDate";
										//pickedDate = pickedDate.replace("2011",",");
									}
									if($.inArray(currDate, busyDays) > -1) {
										return [true,"busy"+addClass];
									} else {
										return [false,""];
									}
									
				},
				onSelect: function(dateText, inst){
					//alert(dateText);
					$("#datepicker").siblings('input[name="datepicker_pickedDate"]').attr('value',dateText);
					$("#datepicker").parents('form').attr('data-ajax','false').submit();
				}

			});
		}
	});
	}
});
}
