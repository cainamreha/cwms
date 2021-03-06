/* Admin tour script */
(function($){

	$.file_AdminTour = function(){
			
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
						titleFst: "Dateien verwalten",
						textFst: "Concise-WMS-Tour zur Datei und Medienverwaltung.",
						titleLst: "Tour Ende",
						textLst: '<a href="admin?task=modules&type=gallery"><u>Zur Verwaltung von Bildergalerien.</u></a>'
					  };
		}else{
		   lnTour	= {	start: "Start tour",
						taskBtn: "need further help?",
						titleFst: "Manage files",
						textFst: "Concise WMS tour on file and media administration.",
						titleLst: "End of tour",
						textLst: '<a href="admin?task=modules&type=gallery"><u>Goto gallery administration.</u></a>'
					  };
		   
		}

		var steps	= new Array();
		var stepSrc	= $('.headerBox.header-file .description li');
		
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
			
			// targets
			switch(i){
				case 0:
					sTitle		= lnTour.titleFst;
					sTarget		= "logoDiv";
					onNext		= function(){
						$(".adminTask-file .cc-section-heading:first").next().slideDown(500);
					};
					break;
				case 1:
					sTarget		= ".adminTask-file .cc-section-heading:first";
					break;
				case 2:
					sTarget		= ".adminTask-file .useFilesFolder";
					onNext		= function(){
						$(".adminTask-file #adminContent .cc-section-heading:first").next().slideUp(500);
						$(".adminTask-file #adminContent .cc-section-heading:last").next().slideDown(500);
					};
					break;
				case 3:
					sTarget		= ".adminTask-file #adminContent .cc-section-heading:last";
					sDelay		= 550;
					onPrev		= function(){
						$(".adminTask-file #adminContent .cc-section-heading:first").next().slideDown(500);
						$(".adminTask-file #adminContent .cc-section-heading:last").next().slideUp(500);
					};
					break;
				case 4:
					sTarget		= $(".adminTask-file #adminContent .cc-section-heading:last").next().find(".folderList:first")[0];
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
		  id: "cwms-admin-file-tour",
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
