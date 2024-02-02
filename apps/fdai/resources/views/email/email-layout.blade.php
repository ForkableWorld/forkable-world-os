<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
@include('meta')
<title>{{ $title ?? "Hello!" }}</title>
<style type="text/css">
    @import url(https://fonts.googleapis.com/css?family=Droid+Sans);
    /* Take care of image borders and formatting */
</style>
<style type="text/css" media="screen">
    @media screen {
        /*Thanks Outlook 2013! http://goo.gl/XLxpyl*/
        td, h1, h2, h3 {
            font-family: 'Droid Sans', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
        }
    }
</style>
<style type="text/css" media="only screen and (max-width: 480px)">
    /* Mobile styles */
    @media only screen and (max-width: 480px) {
        table[class="w320"] {
            width: 320px !important;
        }
        td[class="mobile-block"] {
            display: block !important;
        }
        td[class="pusher"] {
            width: 0 !important;
        }
        img[class="steps"] {
            width: 28px !important;
        }
    }
</style>
</head>
<body class="body">
{!! $content !!}
</body>
</html>