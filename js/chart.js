'use strict';
/*
* Github chart handler.
* Requires nvd3 library.
*/
var CHART = (function (chart, $) {

	chart.init = function(){
		nv.addGraph(function() {  
			var chart = nv.models.discreteBarChart()
				.x(function(d) { return d.date })
				.y(function(d) { return d.value })
				.staggerLabels(false)
				.tooltips(true)
				.showValues(false)
				.transitionDuration(250)
				.tooltipContent(function(k, d, e) {
					return '<p>' + d3.format('d')(e) + '</p>';
				});

			chart.xAxis
				.tickFormat(function(d) {
					return d3.time.format('%e/%m')(new Date(d));
				});		  
			
			var data = [ 
				{
					key: "Github Repository",
					values: CHART_DATA
				}
			];

			d3.select('.github-chart svg')
				.datum(data)
				.call(chart);
			
			nv.utils.windowResize(chart.update);
		});
	};

	return chart;
}(CHART || {}, jQuery));

jQuery(document).ready(CHART.init);





