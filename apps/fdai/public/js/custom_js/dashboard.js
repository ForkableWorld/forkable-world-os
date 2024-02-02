 $(function() {

     /*
     |------------------------
     | Site activity
     |------------------------
     |
     */

     /* stats - 1st tab */

     function showTooltipStats(x, y, contents) {
         $('<div id="tooltip" class="tooltipflot">' + contents + '</div>').css({
             position: 'absolute',
             display: 'none',
             top: y + 5,
             left: x + 5
         }).appendTo("body").fadeIn(200);
     }

     var sales = [
         [0, 5],
         [1, 30],
         [2, 10],
         [3, 15],
         [4, 30],
         [5, 5],
         [6, 12],
         [7, 10],
         [8, 55],
         [9, 13],
         [10, 25],
         [11, 10],
         [12, 12],
         [13, 6],
         [14, 40],
         [15, 5],
         [16, 5]
     ];
     var profit = [
         [0, 3],
         [1, 20],
         [2, 5],
         [3, 2],
         [4, 20],
         [5, 3],
         [6, 2],
         [7, 0],
         [8, 3],
         [9, 1],
         [10, 0],
         [11, 1],
         [12, 0],
         [13, 2],
         [14, 1],
         [15, 20],
         [16, 5]
     ];

     var plot = $.plot($("#basicflot"), [{
         data: sales,
         label: "Sales",
         color: "#48CFAD"
     }, {
         data: profit,
         label: "Profit",
         color: "#FD9883",
         opacity: "1"
     }], {
         series: {
             lines: {
                 show: false
             },
             splines: {
                 show: true,
                 tension: 0.4,
                 lineWidth: 1,
                 fill: 0.4
             },
             points: {
                 radius: 0,
                 show: true
             },
             shadowSize: 2
         },
         legend: {
             container: '#basicFlotLegend1',
             noColumns: 0
         },
         grid: {
             hoverable: true,
             clickable: true,
             borderColor: '#ddd',
             borderWidth: 0,
             labelMargin: 5,
             backgroundColor: '#fff'
         },
         colors: ["#48CFAD", "#FD9883"],
         xaxis: {},
         yaxis: {
             ticks: 4
         }
     });

     var previousPoint1 = null;
     $("#basicflot").bind("plothover", function(event, pos, item) {
         $("#x").text(pos.x.toFixed(2));
         $("#y").text(pos.y.toFixed(2));

         if (item) {
             if (previousPoint1 != item.dataIndex) {
                 previousPoint1 = item.dataIndex;

                 $("#tooltip").remove();
                 var x = item.datapoint[0].toFixed(2),
                     y = item.datapoint[1].toFixed(2);

                 showTooltipStats(item.pageX, item.pageY,
                     item.series.label + " on " + parseInt(x) + " = " + parseInt(y));
             }

         } else {
             $("#tooltip").remove();
             previousPoint1 = null;
         }

     });

     $("#basicflot").bind("plotclick", function(event, pos, item) {
         if (item) {
             plot.highlight(item.series, item.datapoint);
         }
     });

     /* server load - 2nd tab */

     var data = [],
         totalPoints = 300;

     function getRandomData() {
         if (data.length > 0)
             data = data.slice(1);

         // do a random walk
         while (data.length < totalPoints) {
             var prev = data.length > 0 ? data[data.length - 1] : 50;
             var y = prev + Math.random() * 10 - 5;
             if (y < 0)
                 y = 0;
             if (y > 100)
                 y = 100;
             data.push(y);
         }

         // zip the generated y values with the x values
         var res = [];
         for (var i = 0; i < data.length; ++i)
             res.push([i, data[i]])
         return res;
     }

     // setup control widget
     var updateInterval = 50;

     // setup plot
     var options = {
         colors: ["#DBDBDB"],
         series: {
             shadowSize: 0,
             lines: {
                 show: true,
                 fill: true,
                 fillColor: {
                     colors: [{
                         opacity: 0.5
                     }, {
                         opacity: 0.5
                     }]
                 }
             }
         },
         yaxis: {
             min: 0,
             max: 90
         },
         xaxis: {
             show: false
         },
         grid: {
             backgroundColor: '#fff',
             borderWidth: 1,
             borderColor: '#fff'
         }
     };

     var plot4 = $.plot($("#chart3"), [getRandomData()], options);

     function update() {
         plot4.setData([getRandomData()]);
         // since the axes don't change, we don't need to call plot.setupGrid()
         plot4.draw();
         setTimeout(update, updateInterval);
     }
     update();

     //donut
     var datax = [{
         label: "Profile",
         data: 50,
         color: '#4FC1E9'
     }, {
         label: "Facebook ",
         data: 30,
         color: '#A0D468'
     }, {
         label: "Twitter ",
         data: 90,
         color: '#48CFAD'
     }, {
         label: "Google+",
         data: 80,
         color: '#FD9883'
     }, {
         label: "Linkedin",
         data: 110,
         color: '#FFCE54'
     }];

     $.plot($("#donut"), datax, {
         series: {
             pie: {
                 innerRadius: 0.5,
                 show: true
             }
         },
         legend: {
             show: false
         },
         grid: {
             hoverable: true
         },
         tooltip: true,
         tooltipOpts: {
             content: "%p.0%, %s"
         }

     });

     /* sales section - 3rd tab */

     // Desktop
     var d1 = [
         [new Date('2011-01-01').getTime(), 33],
         [new Date('2011-02-01').getTime(), 34],
         [new Date('2011-03-01').getTime(), 23],
         [new Date('2011-04-01').getTime(), 39],
         [new Date('2011-05-01').getTime(), 47],
         [new Date('2011-06-01').getTime(), 26],
         [new Date('2011-07-01').getTime(), 11],
         [new Date('2011-08-01').getTime(), 12],
         [new Date('2011-09-01').getTime(), 24],
         [new Date('2011-10-01').getTime(), 39],
         [new Date('2011-11-01').getTime(), 48],
         [new Date('2011-12-01').getTime(), 40]
     ];

     // Tablet
     var d2 = [
         [new Date('2011-01-01').getTime(), 11],
         [new Date('2011-02-01').getTime(), 13],
         [new Date('2011-03-01').getTime(), 16],
         [new Date('2011-04-01').getTime(), 18],
         [new Date('2011-05-01').getTime(), 22],
         [new Date('2011-06-01').getTime(), 28],
         [new Date('2011-07-01').getTime(), 33],
         [new Date('2011-08-01').getTime(), 32],
         [new Date('2011-09-01').getTime(), 28],
         [new Date('2011-10-01').getTime(), 21],
         [new Date('2011-11-01').getTime(), 15],
         [new Date('2011-12-01').getTime(), 11]
     ];

     // Mobile
     var d3 = [
         [new Date('2011-01-01').getTime(), 0],
         [new Date('2011-02-01').getTime(), 2],
         [new Date('2011-03-01').getTime(), 3],
         [new Date('2011-04-01').getTime(), 5],
         [new Date('2011-05-01').getTime(), 9],
         [new Date('2011-06-01').getTime(), 13],
         [new Date('2011-07-01').getTime(), 16],
         [new Date('2011-08-01').getTime(), 16],
         [new Date('2011-09-01').getTime(), 13],
         [new Date('2011-10-01').getTime(), 8],
         [new Date('2011-11-01').getTime(), 4],
         [new Date('2011-12-01').getTime(), 2]
     ];

     var data3 = [{
         label: "Desktop",
         data: d1,
         bars: {
             show: true,
             align: "center",
             barWidth: 12 * 24 * 60 * 60 * 1000,
             fill: true,
             lineWidth: 1
         },
         color: "#5D9CEC"
     }, {
         label: "Tablet",
         data: d2,
         lines: {
             show: true,
             fill: false
         },
         points: {
             show: true,
             fillColor: '#48CFAD'
         },
         color: '#48CFAD',
         yaxis: 1
     }, {
         label: "Mobile",
         data: d3,
         lines: {
             show: true,
             fill: false
         },
         points: {
             show: true,
             fillColor: '#FFCE54'
         },
         color: '#FFCE54',
         yaxis: 1
     }];

     $.plot($("#placeholder"), data3, {
         xaxis: {
             min: (new Date(2010, 11, 15)).getTime(),
             max: (new Date(2011, 11, 18)).getTime(),
             mode: "time",
             timeformat: "%b",
             tickSize: [1, "month"],
             monthNames: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
             tickLength: 0, // hide gridlines
             axisLabel: "Months",
             axisLabelUseCanvas: true,
             axisLabelFontSizePixels: 12,

             axisLabelPadding: 5
         },
         yaxes: [{
             tickFormatter: function(val, axis) {
                 return val;
             },
             max: 65,
             axisLabel: "Desktop",
             axisLabelUseCanvas: true,
             axisLabelFontSizePixels: 12,

             axisLabelPadding: 5
         }],
         grid: {
             hoverable: true,
             borderWidth: 1,
             borderColor: '#fff'

         },
         legend: {
             container: '#basicFlotLegend',
             noColumns: 0
         },
     });

     function showTooltip(x, y, contents, z) {
         $('<div id="flot-tooltip">' + contents + '</div>').css({
             position: 'absolute',
             display: 'none',
             top: y - 30,
             left: x + 30,
             border: '2px solid',
             padding: '2px',
             'background-color': '#FFF',
             opacity: 0.80,
             'border-color': z,
             '-moz-border-radius': '5px',
             '-webkit-border-radius': '5px',
             '-khtml-border-radius': '5px',
             'border-radius': '5px'
         }).appendTo("body").fadeIn(200);
     }

     function getMonthName(numericMonth) {
         var monthArray = ["Jan", "Feb", "March", "April", "May", "June", "July", "Aug", "Sep", "Oct", "Nov", "Dec"];
         var alphaMonth = monthArray[numericMonth];

         return alphaMonth;
     }

     function convertToDate(timestamp) {
         var newDate = new Date(timestamp);
         var dateString = newDate.getMonth();
         var monthName = getMonthName(dateString);

         return monthName;
     }

     var previousPoint = null;
     var previousPointLabel = null;

     $("#placeholder").bind("plothover", function(event, pos, item) {
         if (item) {
             if ((previousPoint != item.dataIndex) || (previousLabel != item.series.label)) {
                 previousPoint = item.dataIndex;
                 previousLabel = item.series.label;

                 $("#flot-tooltip").remove();

                 if (item.series.label == "Tablet") {
                     var unitLabel = "&nbsp;" + "Sales";
                 } else if (item.series.label == "Mobile") {
                     var unitLabel = "&nbsp;" + "Sales";
                 } else if (item.series.label == "Desktop") {
                     var unitLabel = "&nbsp;" + "Sales";
                 }

                 var x = convertToDate(item.datapoint[0]);
                 y = item.datapoint[1];
                 z = item.series.color;

                 showTooltip(item.pageX, item.pageY,
                     "<b>" + item.series.label + "</b><br /> " + x + " = " + y + unitLabel,
                     z);
             }
         } else {
             $("#flot-tooltip").remove();
             previousPoint = null;
         }
     });


     // sparkline charts
     var myvalues = [10, 8, 5, 7, 4, 6, 7, 1, 3, 5, 9, 4, 4, 1];
     $('.mini-graph').sparkline(myvalues, {
         type: 'bar',
         barColor: '#48CFAD',
         lineColor: 'black',
         height: '40'
     });
     $('.inlinesparkline').sparkline();

     // sparkline charts    
     var myvalues = [10, 8, 5, 3, 5, 7, 4, 6, 7, 1, 9, 4, 4, 1];
     $('.mini-graphpie').sparkline(myvalues, {
         type: 'pie',
         height: '40'
     });

     // sparkline charts
     $(".mini-graph2").sparkline([9, 10, 9, 10, 10, 11, 12, 10, 10, 11, 11, 12, 11, 10, 12, 11, 10, 12], {
         type: 'line',
         width: '80',
         height: '40',
         lineColor: '#FFCC66',
         fillColor: '#F5F5F5'
     });

     // sparkline charts
     var myvalues = [10, 8, 5, 7, 4, 6, 7, 1, 3, 5, 9, 4, 4, 1];
     $('.mini-graph3').sparkline(myvalues, {
         type: 'bar',
         barColor: '#FD9883',
         height: '40'
     });

     $(".mini-graph5").sparkline([5, 6, 7, 4, 9, 5, 9, 6, 4, 6, 6, 7, 8, 6, 7, 4, 9, 5], {
         type: 'line',
         width: '80',
         height: '40',
         lineColor: '#FD9883',
         fillColor: '#F5F5F5'
     });

     /* End of site activity */


     /*
     |------------------------
     | World map & feeds
     |------------------------
     |
     */

     /* World map */

     $('#world-map-markers').vectorMap({
         map: 'world_mill_en',
         scaleColors: ["#C8EEFF", "#0071A4"],
         normalizeFunction: "polynomial",
         hoverOpacity: 0.7,
         hoverColor: false,
         zoomOnScroll: false,
         markerStyle: {
             initial: {
                 fill: "#4FC1E9",
                 stroke: "#4FC1E9"
             }
         },
         regionStyle: {
             initial: {
                 fill: "#6b737d",
                 "fill-opacity": 0.9,
                 stroke: "#fff",
             },
             hover: {
                 "fill-opacity": 0.7
             },
             selected: {
                 fill: "#1A94E0"
             }
         },
         markerStyle: {
             initial: {
                 fill: "#e04a1a",
                 stroke: "#FF604F",
                 "fill-opacity": 0.8,
                 "stroke-width": 1,
                 "stroke-opacity": 0.4,
                 "r": 3
             },
             hover: {
                 stroke: "#C54638",
                 "stroke-width": 2
             },
             selected: {
                 fill: "#C54638"
             },
         },

         backgroundColor: '#B8E0FF',
         markers: [{
             latLng: [60, -100],
             name: 'canada - 1022 views'
         }, {
             latLng: [43.93, 12.46],
             name: 'San Marino- 300 views'
         }, {
             latLng: [47.14, 9.52],
             name: 'Liechtenstein- 52 views'
         }, {
             latLng: [20, -99],
             name: 'Mexico- 599 views'
         }, {
             latLng: [41.90, 12.45],
             name: 'Vatican City- 154 views'
         }, {
             latLng: [50, 0],
             name: 'France - 254 views'
         }, {
             latLng: [40, -90],
             name: 'United States Of America - 925 views'
         }, {
             latLng: [-25, 130],
             name: 'Australia - 586 views'
         }, {
             latLng: [0, 20],
             name: 'Africa - 1 views'
         }, {
             latLng: [35, 100],
             name: 'China -29 views'
         }, {
             latLng: [46, 105],
             name: 'Mongolia - 123 views'
         }, {
             latLng: [40, 70],
             name: 'Kyrgiztan - 446 views'
         }, {
             latLng: [58, 50],
             name: 'Russia - 405 views'
         }, {
             latLng: [35, 135],
             name: 'Japan - 566 views'
         }]
     });

     /* End of world map */

     /* Feeds */

     $('#slim').slimscroll({
         height: '300px',
         size: '5px',
         color: '#bbb',
         opacity: 1
     });
     $('#slim1').slimscroll({
         height: '305px',
         size: '5px',
         color: '#bbb',
         opacity: 1
     });

     /* End of feeds */

     /*
      |------------------------
      | Calendar & users
      |------------------------
      |
      */

     /* Calendar */
      /* var cTime = new Date(),
         month = cTime.getMonth() + 1,
         year = cTime.getFullYear();

     theMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

     theDays = ["S", "M", "T", "W", "T", "F", "S"];
     events = [
         [
             "5/" + month + "/" + year,
             'Meet a friend',
             '#',
             '#418bca',
             'Contents here'
         ],
         [
             "8/" + month + "/" + year,
             'Meeting with CEO',
             '#',
             '#418bca',
             'Contents here'
         ],
         [
             "18/" + month + "/" + year,
             'Milestone release',
             '#',
             '#418bca',
             'Contents here'
         ],
         [
             "19/" + month + "/" + year,
             'A link',
             '/chandra_html',
             '#418bca',
             '#cccccc'
         ]
     ];

     $('#calendar').calendar({
         months: theMonths,
         days: theDays,
         events: events,
         popover_options: {
             placement: 'top',
             html: true
         }
     });

    End of Calendar */

     /* X-editable for users 
     $('#users a').editable({
         type: 'text',
         name: 'username',
         url: '/post',
         title: 'Edit username',
     });

     //ajax emulation
     $.mockjax({
         url: '/post',
         responseTime: 200
     });*/

     /* End of x-editable for users */


     /*
     |------------------------
     | Toastr notifications & quick notes
     |------------------------
     |
     */

     /* Toastr notifications */

     var i = -1;
     var toastCount = 0;
     var $toastlast;

     var shortCutFunction = "info";
     var msg = "Thanks for checking our theme!"
     var title = "Welcome"
     var $showDuration = 1000;
     var $hideDuration = 1000;
     var $timeOut = 5000;
     var $extendedTimeOut = 1000;
     var $showEasing = "swing";
     var $hideEasing = "linear";
     var $showMethod = "fadeIn";
     var $hideMethod = "fadeOut";
     var toastIndex = toastCount++;
     toastr.options = {
         closeButton: $('#closeButton').prop('checked'),
         debug: $('#debugInfo').prop('checked'),
         positionClass: 'toast-top-right',
         onclick: null
     };
     if ($('#addBehaviorOnToastClick').prop('checked')) {
         toastr.options.onclick = function() {
             alert('You can perform some custom action after a toast goes away');
         };
     }
     if ($showDuration.length) {
         toastr.options.showDuration = $showDuration;
     }
     if ($hideDuration.length) {
         toastr.options.hideDuration = $hideDuration;
     }
     if ($timeOut.length) {
         toastr.options.timeOut = $timeOut;
     }
     if ($extendedTimeOut.length) {
         toastr.options.extendedTimeOut = $extendedTimeOut;
     }
     if ($showEasing.length) {
         toastr.options.showEasing = $showEasing;
     }
     if ($hideEasing.length) {
         toastr.options.hideEasing = $hideEasing;
     }
     if ($showMethod.length) {
         toastr.options.showMethod = $showMethod;
     }
     if ($hideMethod.length) {
         toastr.options.hideMethod = $hideMethod;
     }
     //$("#toastrOptions").text("Command: toastr[" + shortCutFunction + "](\"" + msg + (title ? "\", \"" + title : '') + "\")\n\ntoastr.options = " + JSON.stringify(toastr.options, null, 2));
     //var $toast = toastr[shortCutFunction](msg, title); // Wire up an event handler to a button in the toast, if it exists
     //$toastlast = $toast;

     /* End of toastr notifications */

     /* Quick notes */

     //$('#qn').quicknote();

     /* End of quick notes */


 });
   