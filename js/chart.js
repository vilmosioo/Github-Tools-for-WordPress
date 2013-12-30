'use strict';
/*
* Github chart handler.
* Requires nvd3 library.
*/
var CHART = (function (chart, $, window) {

	var _create_chart = function(CHART_DATA){
		return nv.models.discreteBarChart()
			.x(function(d) { return d.date })
			.y(function(d) { return d.value })
			.staggerLabels(false)
			.tooltips(true)
			.showValues(false)
			.transitionDuration(250)
			.margin({top: 15, right: 10, bottom: 20, left: 20})
			.color(function(d){
				return CHART_DATA.color;
			})
			.tooltipContent(function(key, x, y, e, graph){
				return parseInt(y, 10) !== 1 ? '<h3>'+y+' commits</h3>' : '<h3>'+y+' commit</h3>';
			});
	};

	var _apply_styles = function(d3_chart, CHART_DATA){
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
				return d3.time.format('%e %b')(new Date(d));
			});
	  chart.yAxis
	  	.tickFormat(d3.format('d'));
	};

	chart.init = function(){
		nv.addGraph(function() {  
			d3.selectAll('.github-chart svg').each(function(d, index){
				var chart = d3.select(this);
				var data = window[chart.attr('id')];
				var nvchart = _create_chart(data);
			
				// format the axises
				_format_axis(nvchart);

				// apply any styles to the chart
				_apply_styles(chart, data);

				chart.datum([ 
					{
						key: "Github Repository",
						values: data.data
					}
				])
				.call(nvchart);

				nv.utils.windowResize(nvchart.update);
			});
		});
	}

	return chart;
}(CHART || {}, jQuery, window));

jQuery(document).ready(CHART.init);





