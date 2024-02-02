<!DOCTYPE html>
<html>
    <head>
        <title>Be right back.</title>
        <!--     Fonts and icons     -->
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
        <!-- CSS Files -->
        @include('fontawesome')
        <link href="https://static.quantimo.do/material/css/material-dashboard.css?v=2.1.1" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <style>
            html, body {
                height: 100%;
            }
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }
            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }
            .content {
                text-align: center;
                display: inline-block;
            }
            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }
        </style>
    </head>
    <body>{!! Analytics::render() !!}
        <div class="container">
            <div class="content">
                <div class="title">Be right back.</div>
            </div>
        </div>
    <div>
	    <div class="robotpage-top-half">
		    <img src="/img/technical-difficulties-drunk-cameraman.jpg"
		         style="max-width: 90%"
		         href="http://help.quantimo.do"/>
	    </div>
	    <div class="robotpage-bottom-half">
		    <div>
			    <p id="robot-text" style="font-size: large; line-height: 1">Our server's overloaded!</p>
			    @include('error-buttons')
		    </div>
		    <div class="clear"></div>
	    </div>
    </div>
	@include('javascript-in-body')
    @include('components.buttons.chat-sidebar')
    </body>
</html>
