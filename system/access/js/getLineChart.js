(function($){

	$.drawLine = function(cvs, data, linedata, labels, labelsingraph, tooltips, key){

		var canvas		= $('#' + cvs);
		
		if(!$('body').find(canvas).length){
			return false;
		}
		
		var lineCol		= $('.cc-section-heading').css('background-color');
		var dotCol		= $('.cc-section-heading').css('border-top-color');
		var text_size	= 12;
		var linewidth	= 1;
		var dataCount	= labels.length;
		var cWidth		= canvas[0].width;
		var cHeight		= canvas[0].height;
		
		if($('body.cc-admin-skin-blue').length){
			dotCol	= '#FF9A20';
		}

		// Adjust canvas
		_rGadjustCanvas(canvas);
		
		$('.cc-action-togglesidebar').click(function(){
			if($('body').find(canvas).length){
				//canvas[0].width	= 450;
			canvas[0].width	= 450;
			RGraph.Reset(canvas[0]);
				setTimeout(function(){
					_rGadjustCanvas(canvas);
				},350);
			}
		});
		
		$(window).resize(function(){
			if($('body').find(canvas).length){
				//canvas[0].width	= 450;
			canvas[0].width	= 450;
			RGraph.Reset(canvas[0]);
				setTimeout(function(){
					_rGadjustCanvas(canvas);
				},350);
			}
		});
		
		
		/**
		* This is the function that is called when the Pie chart is clicked on
		*/
		function _rGadjustCanvas(canvas)
		{
		
			RGraph.Reset(canvas[0]);
			
			canvas[0].width	= 450;
		
			var panel	= canvas.closest('.cc-admin-panel');
			var gTop		= 40;
			var gLeft		= 60;
			var gRight		= 60;
			var gBottom		= 40;
		
			panel.removeAttr('style');
			var pw	= parseInt(panel.width()) -2;
			var bw	= parseInt(panel.closest('.cc-admin-panel-box').width());

			canvas[0].width  = pw;
			canvas[0].height = Math.min($(window).height() * 0.85, pw * cHeight / cWidth);
		
			text_size = Math.min(11, (pw / 750) * 11 );
			linewidth = pw > 600 ? 2 : 1;
            linewidth = pw > 750 ? 3 : linewidth;
			
			if(pw < 400){
				gTop		= 25;
				gLeft		= 25;
				gRight		= 25;
				gBottom		= 25;
			}
			if(pw > 750){
				gTop		= 60;
				gLeft		= 80;
				gRight		= 80;
				gBottom		= 60;
			}

			// Reset the translation fix so that it gets applied again
			canvas[0].__rgraph_aa_translated__ = false;
			
			if(pw > bw/2){
				panel.css('min-width','100%');
			}
			
			
			var options = {
				textSize: text_size,
				lineWidth: linewidth,
				keyRounded: false,
				keyShadow: false,
				keyLineWidth: 0,
				backgroundGridColor: '#DCE8F1',
				backgroundGridVlines: false,
				clearto: 'white',
				//spline: true,
				colors: [(lineCol ? lineCol : '#F6F8FD')],
				filled: true,
				fillstyle: '#F6F8FD',
				tickmarksDotFill: dotCol,
				tickmarksDotStroke: '#ffffff',
				tickmarksDotLinewidth: 1,
				eventsClick: _rGgetStatsDatapoint
			};
			
			var line = new RGraph.Line({id: cvs, data: data, options: options})
				.set('tickmarks', 'dot')
				.set('shadow', false)
				.set('text.color', '#405B8E')
				.set('text.font', 'Verdana')
				.set('strokestyle', '#D6D8DD')
				.set('shadow', false)
				.set('shadow.color', '#333366')
				.set('shadow.offsety', -1)
				.set('shadow.offsetx', 2)
				.set('shadow.blur', 1)
				.set('axis.color', '#6F84AA')
				.set('noendxtick', true)
				.set('gutter.top', gTop)
				.set('gutter.left', gLeft)
				.set('gutter.right', gRight)
				.set('gutter.bottom', gBottom)
				.set('labels', labels)
				.set('labels.ingraph', true)
				.set('tooltips', tooltips)
				.set('labels.ingraph.size', 6)
				//.set('title', 'Titel')
				.set('key', key)
				.set('key.background', 'rgba(230,237,252,0.75)')
				.set('key.text.size', text_size)
				.set('key.text.color', '#405B8E')
				.trace2();

		}

		
		/**
		* This is the function that is called when the Pie chart is clicked on
		*/
		function _rGgetStatsDatapoint (e, shape)
		{
			// If you have multiple charts on your canvas the .__object__ is a reference to
			// the last one that you created
			var obj   = e.target.__object__
			
			var index = shape['index'];
			var value = linedata[index];
			var d	= new Date();
			var day = ("0" + d.getDate()).slice(-2);
			var mon = ("0" + (d.getMonth() + 1)).slice(-2);
			var de	= day + '.' + mon + '.' + d.getFullYear();

			$.doAjaxAction('admin?task=stats&statsince=' + value + '&statuntil=' + de);
		}
	};
})(jQuery);