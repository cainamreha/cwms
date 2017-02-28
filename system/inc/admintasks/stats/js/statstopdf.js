$(function() {

	jQuery.fn.cleanWhitespace = function() {
		textNodes = this.contents().filter(
			function() { return (this.nodeType == 3 && !/\S/.test(this.nodeValue)); })
			.remove();
		return this;
	}

	cc.statsToPDF = function(){
		
		// only jpeg is supported by jsPDF
		var statDDivs 	= $(".statDetails");
		
		if(!statDDivs.length){
			return false;
		}
		
		var date		= new Date();
		var year		= date.getFullYear();
		var month		= ('0' + (date.getMonth()+1)).slice(-2);
		var day			= ('0' + (date.getDate())).slice(-2);
		
		// To PDF buttons
		statDDivs.each(function(i,e){
		
			var statDDiv	= $(e);
			var statDiv		= statDDiv.closest(".stats");
			var statNo		= statDiv.attr("id");
			var pdfBtn		= $('<button class="convertToPdf btn cc-button button-small button-icon-only right"><span class="cc-admin-icons cc-icon-file-pdf">&nbsp;</span></button>');
			var upBtn		= statDDiv.next(".up");
			var dateTxt		= statDDiv.closest(".adminArea").children(".actionBox").children(".inlineParagraph").text().replace("►","-");
			if(dateTxt == ""){
				dateTxt	= day + "." + month + "." + year;
			}

			upBtn.children('a').addClass("cc-button cc-admin-button button-small");
			upBtn.after(pdfBtn);
			
			pdfBtn.bind("click", function(){
			
				var statData	= "";
				
				// Canvas
				var cvs 		= statDDiv.children("canvas");
				
				if(cvs.length){
					var statID		= cvs.attr("id");
					var canvas 		= document.getElementById(statID);
					var context 	= canvas.getContext("2d");
					destCanvas		= document.createElement("canvas");
					destCanvas.width	= canvas.width;
					destCanvas.height	= canvas.height;
					var destCtx		= destCanvas.getContext("2d");
					//create a rectangle with the desired color
					destCtx.fillStyle	= "#FFFFFF";
					destCtx.fillRect(0,0,canvas.width,canvas.height);
					//draw the original canvas onto the destination canvas
					destCtx.drawImage(canvas, 0, 0);
					statData		= destCanvas.toDataURL("image/jpeg", 1.0);
				}else{
					statData		= statDDiv.clone();
					statData.find("button,nav,br").remove();
					statData.find(".cc-icons").remove();
					statData.find("*[title]").removeAttr("title");
					statData.find("*[aria-tooltip]").removeAttr("aria-tooltip");
					statData.find("span").each(function(i,e){ $(e).parent().html($(e).text()); });
					statData.cleanWhitespace();
				}
				
				// PDF
				var pdf = new jsPDF('l', 'mm', 'a4', true, 10, 0.5, true, true);
				var fileName	= statNo + "-" + year + month + day + ".pdf";
				
				pdf.setTextColor(135);
				pdf.setDrawColor(135);
				pdf.setLineWidth(0.5);
				pdf.setFontSize(16);
				pdf.text(10, 10, statDiv.prev().text());
				pdf.line(10, 12, 210, 12);
				pdf.setTextColor(85);
				pdf.setFontSize(10);
				pdf.text(dateTxt, 10, 20);
				
				pdf.setProperties({
					title: fileName,
					subject: fileName,
					author: 'Concise CMS user',
					creator: 'Concise CMS'
				});

	
				if(cvs.length){
					pdf.addImage(statData, "JPEG", 0, 30);
					pdf.save(fileName);
				}else{
				
					pdf.setFontSize(8);

					// we support special element handlers. Register them with jQuery-style 
					// ID selector for either ID or node name. ("#iAmID", "div", "span" etc.)
					// There is no support for any other type of selectors 
					// (class, of compound) at this time.
					specialElementHandlers = {
						// element with id of "bypass" - jQuery style selector
						'#bypass': function (element, renderer) {
							// true = "handled elsewhere, bypass text extraction"
							return true
						}
					};
					margins = {
						top: 30,
						bottom: 10,
						left: 10,
						right: 10,
						width: 910
					};
					pdf.fromHTML(
						statData[0],
						margins.left,
						margins.top,
						{
							'width': margins.width, // max width of content on PDF
							'elementHandlers': specialElementHandlers
						},
						function (dispose) {
							pdf.save(statNo + "-" + year + month + day + ".pdf");
						},
						margins
					);
				}
				
			});
		});
	
	};

});
