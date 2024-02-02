<!DOCTYPE html>
<html>
<head>
    <title> <?php echo '<li>'.$title.'</li>'; ?></title>
    <!--<link rel="stylesheet" type="text/css" href="../Templates/css/oauth2authorize.css"/>-->
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body {
            font-family: arial regular, arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            background-color: #fbfbfb;
        }

        .dialog {
            -webkit-border-radius: 3px;
            border-radius: 3px;
            z-index: 1000001;
            position: absolute;
            max-width: 320px;
            top: 108px;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
        }

        #permissions-list li:first-child {
            border-top: 1px solid #E3E3E3;
        }

        #permissions-list li {
            border-bottom: 1px solid #E3E3E3;
            padding: 14px;
        }

        @media only screen and (max-width: 768px) {
            .dialog {
                top: 16px;
                max-width: 100%;
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
</head>
<body>
<div class="dialog" id="authorize-dialog">
    <?php echo $studyHtml; ?>
</div>
</body>
</html>
