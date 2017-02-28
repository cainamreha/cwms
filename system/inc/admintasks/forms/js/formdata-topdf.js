$(function() {

	jQuery.fn.cleanWhitespace = function() {
		textNodes = this.contents().filter(
			function() { return (this.nodeType == 3 && !/\S/.test(this.nodeValue)); })
			.remove();
		return this;
	}

	cc.formdataToPDF = function(){
		
		// only jpeg is supported by jsPDF
		var formDataDiv 	= $("#formDataList");
		
		if(!formDataDiv.length){
			return false;
		}
		
		var date		= new Date();
		var year		= date.getFullYear();
		var month		= ('0' + (date.getMonth()+1)).slice(-2);
		var day			= ('0' + (date.getDate())).slice(-2);
		
		// To PDF buttons		
		var pageNav		= formDataDiv.siblings(".pageNav");
		var pageNo		= pageNav.find(".actPage").text();
		var pdfBtn		= $('<button class="convertToPdf btn cc-button button-small button-icon-only right"><span class="cc-admin-icons cc-icon-file-pdf">&nbsp;</span></button>');
		var upBtn		= $("#right");
		var dateTxt		= day + "." + month + "." + year;
		

		upBtn.children('a').addClass("cc-button cc-admin-button button-small");
		
		var existBtn	= upBtn.find('.convertToPdf');
		
		if(!existBtn.length){
			upBtn.append(pdfBtn);
		}else{
			pdfBtn		= existBtn;
		}
		
		pdfBtn.bind("click", function(){

			var statData	= "";
			
			statData		= formDataDiv.find('table.formData').clone();
			
			var tabTags		= statData.find("th, td");
			var cnt			= tabTags.length;
			
			
			//statData.cleanWhitespace();
			statData.find("thead, tbody, tr").each(function(i,e){
				$(e).cleanWhitespace();
			});
			
			/*
			statData.find("th").each(function(i,e){
			});
			*/
			tabTags.each(function(i,e){
				//$(e).cleanWhitespace().html($(e).text());
				$(e).cleanWhitespace();
				
				if($(e).hasClass("mark")){
				//console.log($(e).html());
					$(e).remove();
				}
				if($(e).hasClass('editButtons-cell')){
					$(e).remove();
				}
				if($(e).is('th')){
					$(e).cleanWhitespace().html($(e).children('a').text());
				}
				
				if(!--cnt){
					
				statData.cleanWhitespace();
					statData.removeAttr("id").removeAttr("class");
					statData.find("button,nav,br,label,input,span").remove();
					statData.find(".cc-icons").remove();
					statData.find("*[title]").removeAttr("title");
					statData.find("*[class]").removeAttr("class");
					statData.find("*[aria-tooltip]").removeAttr("aria-tooltip");
					
					var tabData	= $('<div/>');
					tabData.append(statData);
					tabData.cleanWhitespace();
			statData.cleanWhitespace();
			
			// PDF
			var pdf = new jsPDF('l', 'mm', 'a4', true, 10, 0.5, true, true);
			var fileName	= "formdata-p" + pageNo + "-" + year + month + day + ".pdf";
			
			pdf.setTextColor(135);
			pdf.setDrawColor(135);
			pdf.setLineWidth(0.5);
			pdf.setFontSize(16);
			pdf.text(10, 10, pageNav.prev().text());
			pdf.line(10, 12, 285, 12);
			pdf.setTextColor(85);
			pdf.setFontSize(10);
			pdf.text(dateTxt, 10, 20);
			
			pdf.setProperties({
				title: fileName,
				subject: fileName,
				author: 'Concise CMS user',
				creator: 'Concise CMS'
			});

//console.log(tabData.html());
			
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
				top: 15,
				bottom: 10,
				left: 10,
				right: 10,
				width: 285
			};
			pdf.fromHTML(
				tabData[0],
				margins.left,
				margins.top,
				{
					'width': margins.width, // max width of content on PDF
					'elementHandlers': specialElementHandlers
				},
				function (dispose) {
					pdf.save(fileName);
				},
				margins
			);
				}
			});
		
		});
	};

});
