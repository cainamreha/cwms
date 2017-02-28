/* Admin tour script */
(function($){

	$.fe_AdminTour = function(){
			
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
						title1: "Concise WMS",
						title2: "FE-Menü",
						title3: "Admin-Menü",
						title4: "Inhaltselemente",
						title5: "Änderungen veröffentlichen",
						title6: "Inhaltsbereich History",
						title7: "Neue Inhalte",
						title8: "Tour Ende",
						text1: "Frontend-Tour starten." + (!cc.feMode ? "Für zusätzliche Erklärungen den Bearbeitungsmodus einschalten." : ""),
						text2: "Frontend-Menü: hier kann der <b>Bearbeitungsmodus</b> an- und ausgeschaltet werden. Die <b>Auswahl von Themes</b> kann im Vorschaumodus getestet werden.",
						text3: "Den Mauszeiger in die obere rechte Ecke bewegen, um das <b>Admin-Menü</b> einzublenden. Über Links im Menü werden verschiedene Inhaltsbereiche im Adminbereich aufrufen.",
						text4: "Beim überfahren von Inhaltselementen (oder bei Rechtsclick) erscheint eine Buttonleiste zur Bearbeitung der Inhalte. Hierzu muss der Bearbeitungsmodus eingeschaltet sein.",
						text5: "Nach dem Speichern von Änderungen müssen diese noch abschließend übernommen werden, um sie öffentlich zu machen. Dies kann ebenfalls über die Buttonleiste von Inhaltselementen oder im Adminbereich erfolgen.",
						text6: "Die Übernahme von Änderungen erfolgt für den jeweiligen Inhaltsbereich und kann über dessen History im Adminberich bei Bedarf wieder rückgängig gemacht werden.",
						text7: "Neue Inhaltselemente hinzufügen: auf das <b>Plus-Symbol</b> in der Buttonleiste des Elements clicken, nach welchem das neue Element eingefügt werden soll.",
						text8: !cc.feMode ? "Für zusätzliche Erklärungen den Bearbeitungsmodus einschalten und Tour neu starten." : "Jetzt mit dem Hizufügen von Inhaltselementen starten."
					  };
	   }else{
		   lnTour	= {	start: "Start tour",
						taskBtn: "need further help?",
						title1: "Concise WMS",
						title2: "FE menu",
						title3: "Admin menu",
						title4: "Content elements",
						title5: "Publish changes",
						title6: "Content area history",
						title7: "Add contents",
						title8: "End of tour",
						text1: "Start frontend tour." + (!cc.feMode ? "Turn on edit mode for additional info." : ""),
						text2: "Frontend menu: here you can switch <b>edit mode</b> on and off. Preview and confirm <b>theme selection</b>.",
						text3: "Move mouse to upper right corner to show <b>admin menu</b>. Use dropdown menu to find links for editing page's content areas in backend.",
						text4: "Hovering (or right click) on content elements reveals a button panel used to edit respective contents. Requires active frontend editing mode.",
						text5: "After changes have been made you must finally apply changes to make them public. This can be done via respective buttons in content elements' button panel or from within admin backend.",
						text6: "Changes are applied for the whole respective content area. When needed you can restore previous content states via a content area's history within admin backend.",
						text7: "Add content elements: click the <b>plus icon</b> within button panel of the element preceeding the new element.",
						text8: !cc.feMode ? "Turn on edit mode for additional info and restart tour." : "Start now adding new content elements."
					  };
		   
	   }
		
		// Define the tour!
		var optsTour = {
		  id: "cwms-fe-tour",
		  i18n: lnTour,
		  showPrevButton: true,
		  steps: [
			{
			  title: lnTour.title1,
			  content: lnTour.text1,
			  target: "cc-fePanel",
			  placement: "bottom",
			  fixedElement : true
			},
			{
			  title: lnTour.title2,
			  content: lnTour.text2,
			  target: "cc-fePanel",
			  placement: "bottom",
			  xOffset: "center",
			  fixedElement : true,
			  arrowOffset : "center",
			  onNext: function(){
				  $("#topHeader").trigger("mouseover");
			  }
			},
			{
			  title: lnTour.title3,
			  content: lnTour.text3,
			  target: "#container #accountMenu > *:first-child",
			  placement: "bottom",
			  xOffset: "center",
			  arrowOffset : "center",
			  fixedElement : true,
			  delay : 200,
			  onNext: function(){
				  $("#mainContent .editDiv:first-child").trigger("mouseover").find('.editButtons-panel').addClass('forceShow');
			  }
			},
			{
			  title: lnTour.title4,
			  content: lnTour.text4,
			  target: "#container #mainContent .editDiv:first-child",
			  placement: "bottom",
			  delay : 200,
			  onPrev: function(){
				  $("#topHeader").trigger("mouseover");
				  $("#mainContent .editDiv:first-child").find('.editButtons-panel').removeClass('forceShow');
			  }
			},
			{
			  title: lnTour.title5,
			  content: lnTour.text5,
			  target: "#container #mainContent .editDiv:first-child",
			  placement: "bottom",
			  xOffset: "center",
			  onNext: function(){
				  $("#mainContent .editDiv:first-child").find('.editButtons-panel').removeClass('forceShow');
			  }
			},
			{
			  title: lnTour.title6,
			  content: lnTour.text6,
			  target: "body.feMode #header",
			  placement: "bottom",
			  xOffset: "center",
			  arrowOffset : -9999,
			  onPrev: function(){
				  $("#mainContent .editDiv:first-child").trigger("mouseover").find('.editButtons-panel').addClass('forceShow');
			  }
			},
			{
			  title: lnTour.title7,
			  content: lnTour.text7,
			  target: "body.feMode #header",
			  placement: "bottom",
			  xOffset: "center",
			  arrowOffset : -9999
			},
			{
			  title: lnTour.title8,
			  content: lnTour.text8,
			  target: "header",
			  placement: "bottom",
			  xOffset: "center",
			  arrowOffset : -9999
			}
		  ]
		};
		
		var startTourBtn	= $('#cc-fePanel #cc-button-fetour');
		
		if(!startTourBtn.length){
			startTourBtn	= $('<button id="cc-button-fetour" class="btn btn-default adminTourHint" title="' + lnTour.start + '"><span aria-hidden="true" class="cc-admin-icons cc-icons cc-icons cc-icon-compass2">&nbsp;</span></button>');
			$('#cc-fePanel .feMode').after(startTourBtn);
		}
		
		startTourBtn.css('transition','all 0.5s ease');
		
		// Animate if first call
		if(!$.cookie('conciseLog')){
			startTourBtn.delay(1000).fadeTo(300, 0.25, function(){
				//startTourBtn.children('button').showTitleTag();
				startTourBtn.css('transform','scale(1.25)');
			}).delay(100)
			.fadeTo(300, 0.75)
			.delay(100)
			.fadeTo(100, 0.25)
			.delay(100)
			.fadeTo(300, 0.75)
			.delay(100)
			.fadeTo(100, 0.25)
			.delay(100)
			.fadeTo(300, 1, function(){
				startTourBtn.css('transform','scale(1)');
				//startTourBtn.children('button').restoreTitleTag();
			});
		}
		
		startTourBtn.click(function(){
		
			// Start the tour!
			hopscotch.startTour(optsTour);
		
		});
	};
})(jQuery);
