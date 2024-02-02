<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="{{$subject ?? null}}">
    <meta name="author" content="{{app_display_name()}}">
    <title>Email</title>

    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Droid+Sans);

        /* Take care of image borders and formatting */

        img {
            max-width: 600px;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        @if(isset($noBackgroundColor))
            a {
                text-decoration: none;
                border: 0;
                outline: none;
            }
        @else
            a {
                text-decoration: none;
                border: 0;
                outline: none;
            }
        @endif


        a img {
            border: none;
        }

        /* General styling */

        td, h1, h2, h3  {
            font-family: Helvetica, Arial, sans-serif;
            font-weight: 400;
        }

        td {
            text-align: center;
        }

        @if(isset($noBackgroundColor))
            body {
                -webkit-font-smoothing:antialiased;
                -webkit-text-size-adjust:none;
                height: 100%;
                color: #37302d;
                background: #110001;
                font-size: 16px;
            }
        @else
            body {
                -webkit-font-smoothing:antialiased;
                -webkit-text-size-adjust:none;
                height: 100%;
                font-size: 16px;
            }
        @endif




        table {
            border-collapse: collapse !important;
        }



        @if(isset($noBackgroundColor))
            .headline {
                color: #1d0002;
                font-size: 24px;
            }
        @else
            .headline {
                color: #ffffff;
                font-size: 24px;
            }
        @endif



        .force-width-80 {
            width: 80% !important;
        }

        .pusher {
            width: 55px;
        }

        .steps {
            width: 43px;
        }


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