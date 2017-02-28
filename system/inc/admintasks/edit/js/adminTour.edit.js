/* Admin tour script */
(function($){

	$.edit_AdminTour = function(){
			
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
						titleFst: "Seiteneinstellungen und Inhalte",
						textFst: "Concise-WMS-Tour zu Seiteneinstellungen und Inhaltsbearbeitung.",
						titleLst: "Tour Ende",
						textLst: "Hinweis: Inhalte können auch direkt auf der jeweiligen Seite (im Frontend) bearbeitet werden."
					  };
		}else{
		   lnTour	= {	start: "Start tour",
						taskBtn: "need further help?",
						titleFst: "Pages and contents",
						textFst: "Concise WMS tour on page settings and content administration.",
						titleLst: "End of tour",
						textLst: "Note: Alternatively, contents can be edited directly on front-end pages."
					  };
		   
		}

		var steps	= new Array();
		var stepSrc	= $('.headerBox.header-edit .description li');
		
		// First step
		steps.push({
			title: lnTour.titleFst,
			content: lnTour.textFst,
			target: "logoDiv",
			placement: "bottom"
		});
		
		// Read steps from description
		stepSrc.each(function(i,e){
			var sTitle		= "";
			var sText		= $(e).html();
			var sTarget		= "header";
			//sTarget		=  document.querySelector(".adminTask-main .cc-section-heading");
			var sPlacecm	= "bottom";
			var xOffset		= 0;
			var aOffset		= 0;
			
			// if edit single page
			if($('#editPageDetails-form').length
			|| $('.adminTask-tpl.adminType-edit').length
			){
				switch(i){
					case 0:
						sTarget		= "chooseLang";
						sPlacecm	= "left";
						break;
					case 1:
						sTarget		= ".editPageDetailsDiv .page";
						break;
					case 2:
						sTarget		= ".editPageContentsDiv .toggleCons";
						break;
					case 3:
						sTarget		= '.cc-edit-element-box .conNr';
						xOffset		= "center";
						aOffset		= "center";
						break;
					case 4:
						sTarget		= '.cc-edit-element-box .editButtons-panel';
						sPlacecm	= "left";
						break;
					case 5:
						sTarget		= '.cc-edit-element-box .editButtons-panel';
						xOffset		= "center";
						aOffset		= "center";
						break;
					case 6:
						sTarget		= '.controlBar';
						xOffset		= "center";
						aOffset		= "center";
						break;
				}
			}else{
			// else page list
				switch(i){
					case 0:
						sTarget		= ".adminTask-edit .cc-section-heading";
						break;
					case 1:
						sTarget		= "chooseLang";
						sPlacecm	= "left";
						break;
					case 2:
						sTarget		= ".pageListItem .pageID + .editButtons-panel";
						sPlacecm	= "left";
						break;
					case 3:
						sTarget		= ".pageListItem .editButtons-panel.panel-left";
						sPlacecm	= "right";
						break;
					case 4:
						sTarget		= '.pageListItem .editButtons-panel[data-id*="contextmenu-1-b"]';
						xOffset		= "center";
						aOffset		= "center";
						break;
					case 5:
						sTarget		= "mainMenuItem-tpl";
						sPlacecm	= "right";
						break;
				}
			}
			
			// Steps
			steps.push({
				title: sTitle,
				content: sText,
				target: sTarget,
				placement: sPlacecm,
				xOffset: xOffset,
				arrowOffset: aOffset
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
		  id: "cwms-admin-edit-tour",
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
