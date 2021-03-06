/* Admin tour script */
(function($){

	$.gallery_AdminTour = function(){
			
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
						titleFst: "Bildergalerien verwalten",
						textFst: "Concise-WMS-Tour zu Bildergalerien.",
						titleLst: "Tour Ende",
						textLst: "Hinweis: Bildergalerien können auch direkt im Frontend bearbeitet werden."
					  };
		}else{
		   lnTour	= {	start: "Start tour",
						taskBtn: "need further help?",
						titleFst: "Manage image galleries",
						textFst: "Concise WMS tour on image galleries.",
						titleLst: "End of tour",
						textLst: "Note: Alternatively, image galleries can be edited directly within front-end."
					  };
		   
		}

		var steps	= new Array();
		var stepSrc	= $('.headerBox.header-gallery .description li');
		
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
						$(".adminType-gallery .cc-section-heading:first").next().slideDown(500);
					};
					break;
				case 1:
					sTarget		= "gallName";
					break;
				case 2:
					sTarget		= "myUploadBox_dropbox";
					sPlacecm	= "top";
					xOffset		= "center";
					aOffset		= "center";
					onPrev		= function(){
						$(".adminType-gallery .cc-section-heading:first").next().slideDown(500);
						$(".adminType-gallery .cc-section-heading:last").next().slideUp(500);
					};
					onNext		= function(){
						$(".adminType-gallery .cc-section-heading:first").next().slideUp(500);
						$(".adminType-gallery .cc-section-heading:last").next().slideDown(500);
					};
					break;
				case 3:
					sTarget		= [	".adminType-gallery .gallListItem",
									".adminType-gallery .controlBar",
									".adminType-gallery .fileUpload + .cc-section-heading"
								  ];
					sDelay		= 550;
					onPrev		= function(){
						$(".adminType-gallery .cc-section-heading:first").next().slideDown(500);
						$(".adminType-gallery .cc-section-heading:last").next().slideUp(500);
					};
					break;
				case 4:
					sTarget		= $(".folderList.galleryList").prev()[0];
					break;
				case 5:
					sTarget		= "#right .mediaList.filemanager";
					sPlacecm	= "left";
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
		  id: "cwms-admin-gallery-tour",
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
