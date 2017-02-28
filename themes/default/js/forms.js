$(document).ready(function(){


	// Formuare
	$('div#container').on("click", '*[data-select="all"]', function(){
		
		var markDiv = $(this).parents('div.markDiv');
		
		if($(this).is(':checked')){
			markDiv.parent('form').find('input[type="checkbox"]').attr('checked','checked');
			markDiv.parents('div.listBox').children('ul.editList').find('input[type="checkbox"]:visible').attr('checked','checked').parent('div.markBox').addClass('highlight');
			markDiv.parents('table').find('input[type="checkbox"]').attr('checked','checked');
		}else{
			markDiv.parent('form').find('input[type="checkbox"]').removeAttr('checked');
			markDiv.parents('div.listBox').children('ul.editList').find('input[type="checkbox"]').removeAttr('checked').parent('div.markBox').removeClass('highlight');
			markDiv.parents('table').find('input[type="checkbox"]').removeAttr('checked');
		}
	});


	// Löschen von mehreren Einträgen
	$('input[data-action="delmultiple"]').click(function(){
		var confMess = false;
		if($(this).hasClass('delFormData')){
			confMess = confirmdelentry;
			var delForm = $(this).parents('form');
		}
		if(confMess) {
			jConfirm(confMess, conciseCMS.ln.confirmtitle, function(result){
							
				if(result === true) {
					delForm.submit();
				}
			});
		}
		return false;
	});

});
