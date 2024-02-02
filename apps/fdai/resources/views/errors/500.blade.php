<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "https://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>:( I'm sorry... I made a mistake...</title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <!-- CSS Files -->
    @include('fontawesome')
    <link href="https://static.quantimo.do/material/css/material-dashboard.css?v=2.1.1" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://static.quantimo.do/css/robot_page.css">
    <link rel="stylesheet" type="text/css" href="https://static.quantimo.do/css/robot_styles.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:700,300' rel='stylesheet' type='text/css'>
    <script>
        var isHeadless = true;
    </script>
</head>
<body>
<div>
    <div class="robotpage-top-half">
	    <img src="/img/technical-difficulties-500.jpg"
	         style="max-width: 90%"
	         href="http://help.quantimo.do"/>
    </div>
    <div class="robotpage-bottom-half">
        <div>
            <p id="robot-text" style="font-size: large; line-height: 1">I made a mistake!</p>
            @include('error-buttons')
        </div>
        <div class="clear"></div>
    </div>
</div>
@include('javascript-in-body')
@include('components.buttons.chat-sidebar')
<script>
	console.log("{{\App\Utils\Env::get('APP_ENV')}}");
	console.log("{{\App\Utils\Env::get('DB_DATABASE')}}");
	console.log("{{\App\Utils\Env::get('DB_HOST')}}");
</script>
</body>
</html>
