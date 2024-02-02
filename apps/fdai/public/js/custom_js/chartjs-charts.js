$(function () {

    //start line chart
    var lineChartData = {
        labels : ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
        datasets : [
            {
                fillColor : "rgba(79,193,233,0.5)",
                strokeColor : "rgba(79,193,233,0.5)",
                pointColor : "rgba(79,193,233,0.5)",
                pointStrokeColor : "#fff",
                data : [130,63,103,51,93,55,80,140,100,92,108,110]
            },
            {
                fillColor : "rgba(253,152,131,0.5)",
                strokeColor : "rgba(253,152,131,0.5)",
                pointColor : "rgba(253,152,131,0.5)",
                pointStrokeColor : "#fff",
                data : [30,48,35,24,35,27,50,40,60,35,46,30]
            }
        ]

    }
            function draw(){

                var selector = '#line-chart';

                $(selector).attr( 'width', $(selector).parent().width() )
                var myLine = new Chart(document.getElementById("line-chart").getContext("2d")).Line(lineChartData);
            }
          $(window).resize( draw );
         draw()
    //endline chart

    //start bar chart
    var barChartData = {
        labels : ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
        datasets : [
            {
                fillColor : "#48CFAD",
                strokeColor : "#48CFAD",
                data : [65,59,90,81,56,55,40,30,50,20,80,99]
            },
            {
                fillColor : "#FFCE54",
                strokeColor : "#FFCE54",
                data : [28,48,40,19,96,27,40,60,30,90,50,87]
            }
            ,
            {
                fillColor : "#FD9883",
                strokeColor : "#FD9883",
                data:[30,20,100,10,80,27,50,30,60,40,80,66,90]
            }
        ]

    }  
      function draw1(){

                var selector = '#bar-chart';

                $(selector).attr( 'width', $(selector).parent().width() )
                  var myBar = new Chart(document.getElementById("bar-chart").getContext("2d")).Bar(barChartData);
            }
          $(window).resize( draw1 );
         draw1()

  
    //end bar chart

    //start radar chart
    var radarChartData = {
        labels : ["Eating","Drinking","Sleeping","Designing","Coding","Partying","Running"],
        datasets : [
            {
                fillColor : "rgba(160,212,104,0.5)",
                strokeColor : "rgba(160,212,104,0.5)",
                pointColor : "rgba(160,212,104,0.5)",
                pointStrokeColor : "#fff",
                data : [65,59,90,81,56,55,40]
            },
            {
                fillColor : "rgba(255,206,84,0.5)",
                strokeColor : "rgba(255,206,84,0.5)",
                pointColor : "rgba(255,206,84,0.5)",
                pointStrokeColor : "#fff",
                data : [28,48,40,19,96,27,100]
            }
        ]

    }
              function draw2(){

                var selector = '#radar-chart';

                $(selector).attr( 'width', $(selector).parent().width() )
                  var myRadar = new Chart(document.getElementById("radar-chart").getContext("2d")).Radar(radarChartData,{scaleShowLabels : false, pointLabelFontSize : 10});
            }
          $(window).resize( draw2 );
         draw2()
    
    //end  radar chart

    //start polar area chart
    var chartData = [
        {
            value : Math.random(),
            color: "#48CFAD"
        },
        {
            value : Math.random(),
            color: "#FFCE54"
        },
        {
            value : Math.random(),
            color: "#12A5F4"
        },
        {
            value : Math.random(),
            color: "#FD9883"
        },
        {
            value : Math.random(),
            color: "#A0D468"
        },
        {
            value : Math.random(),
            color: "#C1C1B7"
        }
    ];
     function draw3(){

                var selector = '#polar-area-chart';

                $(selector).attr( 'width', $(selector).parent().width() )
                   var myPolarArea = new Chart(document.getElementById("polar-area-chart").getContext("2d")).PolarArea(chartData);
            }
          $(window).resize( draw3 );
         draw3()
   
    //end polar area chart

    //start pie chart
    var pieData = [
        {
            value: 30,
            color:"#FD9883"
        },
        {
            value : 50,
            color : "#48CFAD"
        },
        {
            value : 100,
            color : "#12A5F4"
        }

    ];
     function draw4(){

                var selector = '#pie-chart';

                $(selector).attr( 'width', $(selector).parent().width() )
                   var myPie = new Chart(document.getElementById("pie-chart").getContext("2d")).Pie(pieData);
            }
          $(window).resize( draw4 );
         draw4()
    
    //end pie chart

    //start doughnut chart
    var doughnutData = [
        {
            value: 30,
            color:"#12A5F4"
        },
        {
            value : 50,
            color : "#48CFAD"
        },
        {
            value : 100,
            color : "#FD9883"
        },
        {
            value : 40,
            color : "#A0D468"
        },
        {
            value : 120,
            color : "#FFCE54"
        }

    ];
     function draw5(){

                var selector = '#doughnut-chart';

                $(selector).attr( 'width', $(selector).parent().width() )
                   var myDoughnut = new Chart(document.getElementById("doughnut-chart").getContext("2d")).Doughnut(doughnutData);
            }
          $(window).resize( draw5 );
         draw5()

    
    //end doughnut chart

});
