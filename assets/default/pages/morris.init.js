
/**
 * Theme: Minton Admin Template
 * Author: Coderthemes
 * Morris Chart
 */

!function ($) {
    "use strict";

    var MorrisCharts = function () {
    };

    MorrisCharts.prototype.shortDate = function (d, is_long) {
        if (typeof d !== 'object') {
            d = new Date(d);
        }
        var weekdays = new Array(7);
        weekdays[0] = "SUN";
        weekdays[1] = "MON";
        weekdays[2] = "TUE";
        weekdays[3] = "WED";
        weekdays[4] = "THU";
        weekdays[5] = "FRI";
        weekdays[6] = "SAT";
        var month = new Array();
        month[0] = "JAN";
        month[1] = "FEB";
        month[2] = "MAR";
        month[3] = "APR";
        month[4] = "MAY";
        month[5] = "JUN";
        month[6] = "JUL";
        month[7] = "AUG";
        month[8] = "SEP";
        month[9] = "OCT";
        month[10] = "NOV";
        month[11] = "DEC";
        if(typeof is_long !== 'undefined'){
            return ("0" + (d.getDate())).slice(-2) + ' ' + month[d.getMonth()] + ' ' + d.getFullYear() + ' (' + weekdays[d.getDay()] + ')';
        }else{
            return ("0" + (d.getDate())).slice(-2) + ' ' + month[d.getMonth()] + ' (' + weekdays[d.getDay()] + ')';
        }
    }

    //creates line chart
    MorrisCharts.prototype.createLineChart = function (element, data, xkey, ykeys, labels, opacity, Pfillcolor, Pstockcolor, lineColors) {
        var parent = this;
        var options = {
            element: element,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            fillOpacity: opacity,
            pointFillColors: Pfillcolor,
            pointStrokeColors: Pstockcolor,
            behaveLikeLine: true,
            gridLineColor: '#eef0f2',
            hideHover: 'auto',
            resize: true, //defaulted to true
            lineColors: lineColors,
            dateFormat: function (d) {
                return parent.shortDate(d,true);
            },
            xLabelFormat: function (d) {
                return parent.shortDate(d);
            },
            hoverCallback: function (index, options, content) {
                if (options.ykeys.length > 1) {
                    var total = 0;
                    for (var i in options.ykeys) {
                        total = (total + options.data[index][options.ykeys[i]]).toFixed(4) * 1;
                    }
                    content = content + '<div class="morris-hover-row-label" style="display:block;border-top:1px solid #ccc;">Total: ' + total + '</div>';
                }
                return content;
            }
        };
        Morris.Line(options);
    },
            //creates area chart
            MorrisCharts.prototype.createAreaChart = function (element, pointSize, lineWidth, data, xkey, ykeys, labels, lineColors) {
                Morris.Area({
                    element: element,
                    pointSize: 0,
                    lineWidth: 0,
                    data: data,
                    xkey: xkey,
                    ykeys: ykeys,
                    labels: labels,
                    hideHover: 'auto',
                    resize: true,
                    gridLineColor: '#eef0f2',
                    lineColors: lineColors,
                    hoverCallback: function (index, options, content) {
                        if (options.ykeys.length > 1) {
                            var total = 0;
                            for (var i in options.ykeys) {
                                total = (total + options.data[index][options.ykeys[i]]).toFixed(4) * 1;
                            }
                            content = content + '<div class="morris-hover-row-label" style="display:block;border-top:1px solid #ccc;">Total: ' + total + '</div>';
                        }
                        return content;
                    },
                });
            },
            //creates area chart with dotted
            MorrisCharts.prototype.createAreaChartDotted = function (element, pointSize, lineWidth, data, xkey, ykeys, labels, Pfillcolor, Pstockcolor, lineColors) {
                Morris.Area({
                    element: element,
                    pointSize: 3,
                    lineWidth: 1,
                    data: data,
                    xkey: xkey,
                    ykeys: ykeys,
                    labels: labels,
                    hideHover: 'auto',
                    pointFillColors: Pfillcolor,
                    pointStrokeColors: Pstockcolor,
                    resize: true,
                    gridLineColor: '#eef0f2',
                    lineColors: lineColors,
                    hoverCallback: function (index, options, content) {
                        if (options.ykeys.length > 1) {
                            var total = 0;
                            for (var i in options.ykeys) {
                                total = (total + options.data[index][options.ykeys[i]]).toFixed(4) * 1;
                            }
                            content = content + '<div class="morris-hover-row-label" style="display:block;border-top:1px solid #ccc;">Total: ' + total + '</div>';
                        }
                        return content;
                    },
                });
            },
            //creates Bar chart
            MorrisCharts.prototype.createBarChart = function (element, data, xkey, ykeys, labels, lineColors) {
                Morris.Bar({
                    element: element,
                    data: data,
                    xkey: xkey,
                    ykeys: ykeys,
                    labels: labels,
                    hideHover: 'auto',
                    resize: true, //defaulted to true
                    gridLineColor: '#eeeeee',
                    barColors: lineColors
                });
            },
            //creates Stacked chart
            MorrisCharts.prototype.createStackedChart = function (element, data, xkey, ykeys, labels, lineColors) {
                Morris.Bar({
                    element: element,
                    data: data,
                    xkey: xkey,
                    ykeys: ykeys,
                    stacked: true,
                    labels: labels,
                    hideHover: 'auto',
                    resize: true, //defaulted to true
                    gridLineColor: '#eeeeee',
                    barColors: lineColors
                });
            },
            //creates Donut chart
            MorrisCharts.prototype.createDonutChart = function (element, data, colors) {
                Morris.Donut({
                    element: element,
                    data: data,
                    resize: true, //defaulted to true
                    colors: colors
                });
            },
            MorrisCharts.prototype.init = function () {

                //create line chart
                var $data = [
                    {y: '2010', a: 30, b: 20, c: 10},
                    {y: '2011', a: 50, b: 40, c: 30},
                    {y: '2012', a: 75, b: 65, c: 50},
                    {y: '2013', a: 50, b: 40, c: 22},
                    {y: '2014', a: 75, b: 65, c: 50},
                    {y: '2015', a: 100, b: 90, c: 65}
                ];
                this.createLineChart('morris-line-example', $data, 'y', ['a', 'b', 'c'], ['Series A', 'Series B', 'Series C'], ['0.1'], ['#ffffff'], ['#999999'], ["#00b19d", "#ffaa00", "#f76397"]);

                //creating area chart
                var $areaData = [
                    {y: '2009', a: 10, b: 20, c: 30},
                    {y: '2010', a: 75, b: 65, c: 30},
                    {y: '2011', a: 50, b: 40, c: 30},
                    {y: '2012', a: 75, b: 65, c: 30},
                    {y: '2013', a: 50, b: 40, c: 30},
                    {y: '2014', a: 75, b: 65, c: 30},
                    {y: '2015', a: 90, b: 60, c: 30}
                ];
                this.createAreaChart('morris-area-example', 0, 0, $areaData, 'y', ['a', 'b', 'c'], ['Series A', 'Series B', 'Series C'], ["#00b19d", "#ffaa00", "#f76397"]);

                //creating area chart with dotted
                var $areaDotData = [
                    {y: '2009', a: 10, b: 20},
                    {y: '2010', a: 75, b: 65},
                    {y: '2011', a: 50, b: 40},
                    {y: '2012', a: 75, b: 65},
                    {y: '2013', a: 50, b: 40},
                    {y: '2014', a: 75, b: 65},
                    {y: '2015', a: 90, b: 60}
                ];
                this.createAreaChartDotted('morris-area-with-dotted', 0, 0, $areaDotData, 'y', ['a', 'b'], ['Series A', 'Series B'], ['#ffffff'], ['#999999'], ["#26c6da", "#228bdf"]);

                //creating bar chart
                var $barData = [
                    {y: '2009', a: 100, b: 90, c: 40},
                    {y: '2010', a: 75, b: 65, c: 20},
                    {y: '2011', a: 50, b: 40, c: 50},
                    {y: '2012', a: 75, b: 65, c: 95},
                    {y: '2013', a: 50, b: 40, c: 22},
                    {y: '2014', a: 75, b: 65, c: 56},
                    {y: '2015', a: 100, b: 90, c: 60}
                ];
                this.createBarChart('morris-bar-example', $barData, 'y', ['a', 'b', 'c'], ['Series A', 'Series B', 'Series C'], ["#00b19d", "#f76397", "#7266ba"]);

                //creating Stacked chart
                var $stckedData = [
                    {y: '2005', a: 45, b: 180},
                    {y: '2006', a: 75, b: 65},
                    {y: '2007', a: 100, b: 90},
                    {y: '2008', a: 75, b: 65},
                    {y: '2009', a: 100, b: 90},
                    {y: '2010', a: 75, b: 65},
                    {y: '2011', a: 50, b: 40},
                    {y: '2012', a: 75, b: 65},
                    {y: '2013', a: 50, b: 40},
                    {y: '2014', a: 75, b: 65},
                    {y: '2015', a: 100, b: 90}
                ];
                this.createStackedChart('morris-bar-stacked', $stckedData, 'y', ['a', 'b'], ['Series A', 'Series B'], ["#228bdf", "#ededed"]);

                //creating donut chart
                var $donutData = [
                    {label: "Download Sales", value: 12},
                    {label: "In-Store Sales", value: 30},
                    {label: "Mail-Order Sales", value: 20}
                ];
                this.createDonutChart('morris-donut-example', $donutData, ["#00b19d", "#f76397", "#7266ba"]);
            },
            //init
            $.MorrisCharts = new MorrisCharts, $.MorrisCharts.Constructor = MorrisCharts
}(window.jQuery),
//initializing 
        function ($) {
            "use strict";
            //$.MorrisCharts.init();
        }(window.jQuery);