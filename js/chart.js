'use strict';
/*
* Github chart handler.
* Requires nvd3 library.
*/
var CHART = (function (chart, $) {

	var _create_chart = function(){
		return nv.models.discreteBarChart()
			.x(function(d) { return d.date })
			.y(function(d) { return d.value })
			.staggerLabels(false)
			.tooltips(true)
			.showValues(false)
			.transitionDuration(250)
			.tooltipContent(function(k, d, e) {
				return '<p>' + d3.format('d')(e) + '</p>';
			})
			.color(function(d){
				return CHART_DATA.color;
			});
	};

	var _apply_styles = function(){
		var d3_chart = d3.select('.github-chart svg');

		if($.isNumeric(CHART_DATA.width) && CHART_DATA.width > 0){
			d3_chart.style('width', CHART_DATA.width);
		}
		if($.isNumeric(CHART_DATA.height) && CHART_DATA.height > 0){
			d3_chart.style('height', CHART_DATA.height);
		}

		d3_chart.style('background', CHART_DATA.background);
	};

	var _format_axis = function(chart){
		chart.xAxis
			.tickFormat(function(d) {
				return d3.time.format('%e/%m')(new Date(d));
			});
	};

	chart.init = function(){
		nv.addGraph(function() {  
			var chart = _create_chart();
			
			// format the axises
			_format_axis(chart);

			// apply any styles to the chart
			_apply_styles();

			d3.select('.github-chart svg').datum([ 
				{
					key: "Github Repository",
					values: CHART_DATA.data
				}
			])
			.call(chart);

			nv.utils.windowResize(chart.update);
		});
	};

	return chart;
}(CHART || {}, jQuery));

jQuery(document).ready(CHART.init);





