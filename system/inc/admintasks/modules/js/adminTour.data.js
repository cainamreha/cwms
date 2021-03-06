/* Admin tour script */
(function($){

	$.data_AdminTour = function(){
			
		hopscotch.endTour(true);

		var lnTour	= {};

		if(cc.adminLang == "de"){
		   lnTour	= { nextBtn: "weiter",
						prevBtn: "zurück",
						doneBtn: "fertig",
						skipBtn: "überspringen",
						closeTooltip: "schließen",
						start: "Tour starten",
						taskBtn: "weitere Hilfe?",
						titleFst: $('.headerBox .adminHeader').text(),
						titleLst: "Tour Ende",
						textLst: 'Hinweis: Zum Anzeigen von Moduldaten auf der Website muss eine entsprechende Liste als Inhaltselement in eine der Webseiten eingefügt werden.</a>'
					  };
		}else{
		   lnTour	= {	start: "Start tour",
						taskBtn: "need further help?",
						titleFst: $('.headerBox .adminHeader').text(),
						titleLst: "End of tour",
						textLst: 'Hint: To show module data to your visitors you must include a data list type content element in one of your pages.'
					  };
		   
		}

		var steps	= new Array();
		var stepSrc	= $('.headerBox .description li');
		
		// Read steps from description
		stepSrc.each(function(i,e){
			var sTitle		= "";
			var sText		= $(e).html();
			var sTarget		= $(".headerBox")[0];
			//sTarget		=  document.querySelector(".adminTask-main .cc-section-heading");
			var sPlacecm	= "bottom";
			var xOffset		= 0;
			var aOffset		= 0;
			var onNext		= null;
			var onPrev		= null;
			var sDelay		= 0;
			var bubbleWidth	= 280;
			
			// targets
			switch(i){
				case 0:
					sTitle		= lnTour.titleFst;
					sTarget		= "logoDiv";
					break;
				case 1:
					sTarget		= "#right .controlBar";
					sPlacecm	= "left";
					bubbleWidth = 450;
					onNext		= function(){
						$(".adminTask-modules .editDataCategories").slideDown(500);
						$(".adminTask-modules .editDataEntrySection").slideUp(500);
					};
					break;
				case 2:
					sTarget		= ".adminTask-modules .editDataCategories";
					xOffset		= "center";
					aOffset		= "center";
					bubbleWidth = 450;
					onNext		= function(){
						$(".adminTask-modules .editDataEntrySection").slideDown(500);
						$(".adminTask-modules .editDataCategories").slideUp(500);
					};
					break;
				case 3:
					sTarget		= ".adminTask-modules .editDataEntrySection";
					sPlacecm	= "top";
					xOffset		= "center";
					aOffset		= "center";
					bubbleWidth = 450;
					onPrev		= function(){
						$(".adminTask-modules .editDataCategories").slideDown(500);
						$(".adminTask-modules .editDataEntrySection").slideUp(500);
					};
					break;
			}
			
			// Steps
			steps.push({
				title: sTitle,
				content: sText,
				target: sTarget,
				placement: sPlacecm,
				xOffset: xOffset,
				arrowOffset: aOffset,
				delay: sDelay,
				width: bubbleWidth,
				onNext: onNext,
				onPrev: onPrev
			});
		});
		
		// Last step
		steps.push({
			title: lnTour.titleLst,
			content: lnTour.textLst,
			target: document.querySelector(".headerBox"),
			placement: "bottom",
			xOffset: "center",
			arrowOffset: "-9999px"
		});
		
		// Define the tour!
		var optsTour = {
		  id: "cwms-admin-data-tour",
		  i18n: lnTour,
		  showPrevButton: true,
		  steps: steps
		};
		
		var startTourBtn	= $('<div id="adminTourNav" class="iconPanel-top"><button title="' + lnTour.start + '" class="cc-button button btn cc-button adminTourHint button-icon-only button-small"><span aria-hidden="true" class="cc-admin-icons cc-icons cc-icon-compass2">&nbsp;</span></button></div>');
		
		startTourBtn.css('transition','all 0.5s ease');
		
		startTourBtn.prependTo('#iconPanelTop');
		
		startTourBtn.bind("click", function(){
		
			// Start the tour!
			hopscotch.startTour(optsTour);
		
		});
	};
})(jQuery);
