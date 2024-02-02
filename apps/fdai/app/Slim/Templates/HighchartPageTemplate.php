<html>
<head>
    <title><?php echo $title ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php $highchart->printScripts(); ?>
</head>
<body>
<div id="container"></div>
<script type="text/javascript"><?php echo $highchart->render("chart1"); ?></script>
</body>
</html>
