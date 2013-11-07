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
			});
	};

	var _apply_styles = function(){
		var d3_chart = d3.select('.github-chart svg'),
			d3_rect = d3.selectAll('.github-chart rect');
		console.log(d3_rect);
		if($.isNumeric(CHART_DATA.width) && CHART_DATA.width > 0){
			d3_chart.style('width', CHART_DATA.width);
		}
		if($.isNumeric(CHART_DATA.height) && CHART_DATA.height > 0){
			d3_chart.style('height', CHART_DATA.height);
		}

		d3_chart.style('background', CHART_DATA.background);
		d3_rect.style({
			'fill': CHART_DATA.color,
			'stroke': CHART_DATA.color
		});
	};

	chart.init = function(){
		nv.addGraph(function() {  
			var chart = _create_chart();
			
			chart.xAxis
				.tickFormat(function(d) {
					return d3.time.format('%e/%m')(new Date(d));
				});

			d3.select('.github-chart svg').datum([ 
				{
					key: "Github Repository",
					values: CHART_DATA.data
				}
			])
			.call(chart);

			_apply_styles();

			nv.utils.windowResize(chart.update);
		});
	};

	return chart;
}(CHART || {}, jQuery));

jQuery(document).ready(CHART.init);





