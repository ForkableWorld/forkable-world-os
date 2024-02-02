<?php
/** @link          https://github.com/mzm-dev
 * @demo          http://highcharts-mzm.rhcloud.com
 */
$cakeDescription = "Highcharts Pie Chart";
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $cakeDescription ?></title>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            var options = {
                chart: {
                    renderTo: 'container',
                    type: 'column'
                },
                title: {
                    text: 'Highcharts Chart PHP with MySQL Example',
                    x: -20 //center
                },
                subtitle: {
                    text: 'Sumber : Jabatan XYZ',
                    x: -20
                },
                xAxis: {
                    categories: []
                },
                yAxis: {
                    title: {
                        text: 'Jumlah Pelawat'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {
                    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>:<b>{point.y}</b> of total<br/>'
                },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y}'
                        }
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -40,
                    y: 100,
                    floating: true,
                    borderWidth: 1,
                    backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                    shadow: true
                },
                series: []
            };
            $.getJSON("/api/v1/highcharts?accessToken=demo&variableName=Overall Mood", function (charts) {
                var optionsFromApi = charts[0].chartConfig.options;
                options.xAxis.categories = optionsFromApi.xAxis.categories;
                options.series[0] = optionsFromApi.series.data;
                chart = new Highcharts.Chart(options);
            });
        });
    </script>
</head>
<body>
<a class="link_header" href="/highcharts/">&lt;&lt; Back to index</a>
<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
</body>
</html>
