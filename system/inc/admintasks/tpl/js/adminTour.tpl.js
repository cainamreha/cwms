/* Admin tour script */
(function($){

	$.tpl_AdminTour = function(){
			
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
						titleFst: "Themes & Templates",
						textFst: "Concise-WMS-Tour zu Themes & Templates.",
						titleLst: "Tour Ende",
						textLst: 'Tipp: Legen Sie vor einschneidenden Veränderungen an Themes eine Kopie des Themes an. So kann jederzeit darauf zurückgegriffen werden.</a>'
					  };
		}else{
		   lnTour	= {	start: "Start tour",
						taskBtn: "need further help?",
						titleFst: "Themes & templates",
						textFst: "Concise WMS tour on themes & templates.",
						titleLst: "End of tour",
						textLst: 'Hint: Make a copy of a theme you mean to edit. In case you can access the original state any time.'
					  };
		   
		}

		var steps	= new Array();
		var stepSrc	= $('.headerBox.header-tpl .description li');
		
		// Read steps from description
		stepSrc.each(function(i,e){
			var sTitle		= "";
			var sText		= $(e).html();
			var sTarget		= ".headerBox";
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
					sTarget		= ".adminTask-tpl .controlBar";
					xOffset		= "center";
					aOffset		= "center";
					onNext		= function(){
						$(".adminTask-tpl .controlBar ~ .adminBox:first").slideDown(500);
					};
					break;
				case 2:
					sTarget		= ".adminTask-tpl .controlBar ~ .adminBox:first";
					xOffset		= "center";
					aOffset		= "center";
					bubbleWidth = 450;
					onNext		= function(){
						$(".adminTask-tpl .controlBar ~ .adminBox:nth-of-type(3)").slideDown(500);
					};
					break;
				case 3:
					sTarget		= ".adminTask-tpl .controlBar ~ .adminBox:nth-of-type(3)";
					xOffset		= "center";
					aOffset		= "center";
					onPrev		= function(){
						$(".adminTask-tpl .controlBar ~ .adminBox:first").slideDown(500);
					};
					break;
				case 4:
					sTarget		= $(".adminTask-tpl .controlBar ~ .adminBox:nth-of-type(4)").prev()[0];
					xOffset		= "center";
					aOffset		= "center";
					onPrev		= function(){
						$(".adminTask-tpl .controlBar ~ .adminBox:nth-of-type(3)").slideDown(500);
					};
					break;
				case 5:
					sTarget		= $(".adminTask-tpl .controlBar ~ .adminBox:nth-of-type(5)").prev()[0];
					xOffset		= "center";
					aOffset		= "center";
					break;
				case 6:
					xOffset		= "center";
					aOffset		= "-9999px";
					break;
				case 7:
					xOffset		= "center";
					aOffset		= "-9999px";
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
		  id: "cwms-admin-tpl-tour",
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
