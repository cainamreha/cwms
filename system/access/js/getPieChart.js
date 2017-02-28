(function($){

	$.drawPie = function(cvs, data, labels, labelsingraph, tooltips, key){
		
		var options = {
			clearto: 'white',
			keyRounded: false,
			keyLineWidth: 0
		};
		
		var pie = new RGraph.Pie({id: cvs, data: data, options: options})
			.set('text.color', '#333366')
			.set('text.font', 'Verdana')
			.set('colors', ['Gradient(#FEEDA9:#FF9A20:#FF9A20)', 'Gradient(#6F84AA:#405B8E:#405B8E)', '#FEF9D2', '#DCE8F1', '#E1FFE1', '#ABA6F4', 'pink', 'grey', '#EE6600', '#D6D8DD'])
			.set('strokestyle', '#D6D8DD')
			.set('linewidth', 1)
			.set('gutter.top', 80)
			.set('shadow', true)
			.set('shadow.color', '#333366')
			.set('shadow.offsety', 5)
			.set('shadow.offsetx', 1)
			.set('shadow.blur', 5)
			.set('variant', 'donut')
			.set('variant.donut.width', 75)
			.set('exploded', 5)
			.set('radius', 110)
			.set('title', 'Browser Ihrer Website-Besucher')
			.set('title.size', 10)
			.set('title.y', 30)
			.set('title.color', '#D6D8DD')
			.set('tooltips', tooltips)
			.set('tooltips.coords.page', true)
			.set('tooltips.css.class', 'RGraph_tooltip titleTagBox adminArea')
			.set('labels', labels)
			.set('labels.ingraph', true)
			.set('labels.ingraph.specific', labelsingraph)
			.set('labels.ingraph.size', 6)
			.set('labels.sticks', true)
			.set('labels.sticks.length', 25)
			.set('key', key)
			.set('key.interactive', true)
            .set('key.interactive.highlight.chart.fill', 'rgba(255,255,255,0.5)')
            .set('key.interactive.highlight.chart.stroke', '#6F84AA')
            .set('key.interactive.highlight.label', 'rgba(220,232,241,0.5)')
			.set('key.position', 'graph')
			.set('key.position.x', 450)
			.set('key.position.y', 20)
			.set('key.text.size', 8)
			.set('key.position.graph.boxed', false)
			
		// This is the factor that the canvas is scaled by
		var factor = 1.25;
		// Set the transformation of the canvas - a scale up by the factor (which is 1.5 and a simultaneous translate
		// so that the Pie appears in the center of the canvas
		pie.context.setTransform(factor,0,0,1,((pie.canvas.width * factor) - pie.canvas.width) * -0.5,0);

		//pie.Draw();
		pie.roundRobin({frames: 30});
		
	};
})(jQuery);
