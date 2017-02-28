(function($){

	$.drawBar = function(cvs, data, linedata, labels, labelsingraph, tooltips, key){

		var canvas		= $('#' + cvs);
		
		if(!$('body').find(canvas).length){
			return false;
		}

		var dataCount	= labels.length;
		var hMargin		= dataCount < 4 ? 30 : (dataCount > 6 ? 5 : 10);
		var hMarginG	= dataCount < 4 ? 20 : (dataCount > 6 ? 2 : 5);
		var labAngle	= dataCount < 4 ? 0 : 90;
		var lineCol		= $('.cc-section-heading').css('background-color');
		var dotCol		= $('.cc-section-heading').css('border-top-color');
		var altGradient	= 'Gradient(#6F84AA:#405B8E:#405B8E)';
		var text_size	= 12;
		var linewidth	= 1;
		var cWidth		= canvas[0].width;
		var cHeight		= canvas[0].height;
		
		if($('body.cc-admin-skin-blue').length){
			altGradient	= 'Gradient(#FEEDA9:#FF9A20:#FF9A20)';
		}

		// Adjust canvas
		_rGadjustCanvas(canvas);
		
		$('.cc-action-togglesidebar').click(function(){
			if($('body').find(canvas).length){
			canvas[0].width	= 450;
			RGraph.Reset(canvas[0]);
				setTimeout(function(){
					_rGadjustCanvas(canvas);
				},350);
			}
		});
		
		$(window).resize(function(){
			if($('body').find(canvas).length){
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
			var gBottom		= dataCount < 4 ? 60 : 180;
		
			panel.removeAttr('style');
			var pw	= parseInt(panel.width()) -2;
			var bw	= parseInt(panel.closest('.cc-admin-panel-box').width());

			canvas[0].width  = pw;
			canvas[0].height = Math.min($(window).height() * 0.65, pw * cHeight / cWidth);
		
			text_size = Math.min(11, (pw / 750) * 11 );
			linewidth = pw > 600 ? 2 : 1;
            linewidth = pw > 750 ? 3 : linewidth;
			
			if(pw < 400){
				gTop		= 25;
				gLeft		= dataCount < 4 ? 60 : (dataCount < 6 ? 40 : 25);
				gRight		= dataCount < 4 ? 60 : (dataCount < 6 ? 40 : 25);
				gBottom		= dataCount < 4 ? 25 : 75;
			}
			if(pw > 750){
				gTop		= 60;
				gLeft		= dataCount < 4 ? 180 : (dataCount < 6 ? 120 : 80);
				gRight		= dataCount < 4 ? 180 : (dataCount < 6 ? 120 : 80);
				gBottom		= dataCount < 4 ? 60 : 220;
			}

			// Reset the translation fix so that it gets applied again
			canvas[0].__rgraph_aa_translated__ = false;
			
			if(pw > bw/2){
				panel.css('min-width','100%');
			}

			
			var options = {
				textSize: text_size,
				lineWidth: linewidth,
				backgroundGridColor: '#DCE8F1',
				backgroundGridVlines: false,
				backgroundGridBorder: false,
				clearto: 'white',
				keyRounded: false,
				keyShadow: false,
				combinedchartEffect: 'wave'
			};
			
			var bar = new RGraph.Bar({id: cvs, data: data, options: options})
				.set('text.color', '#405B8E')
				.set('text.font', 'Verdana')
				.set('text.angle', labAngle)
				.set('colors', ['Gradient(' + lineCol + ':' + dotCol + ')', altGradient, '#FEF9D2', '#DCE8F1', '#E1FFE1', '#ABA6F4', 'pink', 'grey', '#EE6600', '#D6D8DD'])
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
				.set('hmargin', hMargin)
				.set('hmargin.grouped', hMarginG)
				.set('labels', labels)
				.set('labels.ingraph', true)
				.set('labels.ingraph.specific', labelsingraph)
				.set('labels.ingraph.size', 6)
				//.set('title', 'Titel')
				.set('key', key)
				.set('key.background', 'rgba(230,237,252,0.75)')
				.set('key.interactive', true)
				.set('key.interactive.highlight.chart.fill', 'rgba(220,232,241,0.25)')
				.set('key.interactive.highlight.chart.stroke', '#EE6600')
				.set('key.interactive.highlight.label', 'rgba(160,205,239,0.25)')
				.set('key.text.size', text_size)
				.set('key.text.color', '#405B8E')
				;
				//.draw();
			
			var line = new RGraph.Line(cvs, linedata)
				//.set('noxaxis', true)
				//.set('background.grid', false)
				.set('spline', true)
				.set('linewidth', linewidth)
				.set('tickmarks', 'filledcircle')
				.set('colors', ['#DBF5A6'])
				.set('shadow', false)
				.set('combinedchartEffect', 'trace2')
				//.draw();

			var combo = new RGraph.CombinedChart(bar, line);
			combo.draw();
		}

	};
})(jQuery);