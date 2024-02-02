<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script type="text/javascript" async="" src="https://www.google-analytics.com/analytics.js"></script>
    <script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-129399191-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag("js", new Date());

        gtag("config", "UA-129399191-2");
    </script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Variable Search</title>
    <meta name="”description&quot;" content="”Simple" autocomplete="" pure="" vanilla="" javascript="" library="" that
    's="" designed="" for="" speed,="" high="" versatility="" and="" seamless="" integration="" with="" wide="" range=""
    of="" projects="" systems.”="">
    <meta name="keywords"
          content="autoComplete.js, autocomplete, easy-to-use, simple, pure, vanilla, javascript, js, library, speed, lightning, blazing, fast, search, suggestions, versatile, customizable, hackable, developer friendly, zero dependencies, lightweight, high integration, scalable, scalability, open source, github">
    <meta name="subject" content="autoComplete Javascript Library">
    <meta name="author" content="Tarek Raafat">
    <meta name="copyright" content="Tarek Raafat">
    <meta name="owner" content="Tarek Raafat">
    <meta name="google-site-verification" content="UphDCSqXKDBZF8Uyot7RJpQUxqsdrp2GDZXgiORHwgs">
    <meta property="og:site_name" content="autoComplete.js">
    <meta property="og:title" content="autoComplete.js">
    <meta property="og:description" content="Simple autocomplete pure vanilla Javascript library.">
    <meta property="og:type" content="product">
    <meta property="og:url" content="https://tarekraafat.github.io/autoComplete.js/demo/">
    <meta property="og:image" content="https://tarekraafat.github.io/autoComplete.js/img/autoComplete.js_Preview.png">
    <meta property="og:image"
          content="https://tarekraafat.github.io/autoComplete.js/img/autoComplete.js_WidePreview.png">
    <meta property="og:image" content="https://tarekraafat.github.io/autoComplete.js/img/autoComplete.init.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="1200">
    <meta property="fb:app_id" content="1482373115226718">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="manifest" href="https://tarekraafat.github.io/autoComplete.js/manifest.json">
    <link rel="apple-touch-icon" sizes="57x57"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16"
          href="https://tarekraafat.github.io/autoComplete.js/img/icons/favicon-16x16.png">
    <link rel="manifest" href="https://tarekraafat.github.io/autoComplete.js//manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" type="text/css" media="screen"
          href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/css/autoComplete.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/plugins/pace/pace.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="./css/main.css">
    <style>
        :root {
            --transition-1: all 0.3s ease-in-out;
            --transition-2: all 0.1s ease-in-out;
        }

        html {
            font-size: 1rem;
            font-family: "PT Sans", sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .contaier {
            width: 100vw;
            height: 100vh;
        }

        .header {
            text-align: center;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -webkit-touch-callout: none;
        }

        .no_result {
            margin: 0.15rem auto;
            padding: 0.6rem;
            max-width: 280px;
            border: 0.05rem solid #e3e3e3;
            list-style: none;
            text-align: left;
            font-size: 1.1rem;
            color: rgb(123, 123, 123);
            transition: all 0.1s ease-in-out;
            background-color: #fff;
            border-radius: 0 0 1rem 1rem;
            outline: none;
        }

        .no_result:hover {
            cursor: default;
            background-color: #fff;
            border: 0.05rem solid #e3e3e3;
        }

        .no_result:focus {
            cursor: default;
            background-color: #fff;
            border: 0.05rem solid #e3e3e3;
        }

        .in {
            padding: 0 2rem 0 3.5rem;
            color: rgba(255, 122, 122, 1);
            height: 3rem;
            width: 16.5rem;
            border: 0.05rem solid rgb(255, 122, 122);
            background: url("./images/magnifier.svg") no-repeat left/15% 1.5rem;
            box-shadow: rgba(255, 122, 122, 0.1) 0px 0px 20px 5px;
            position: relative;
            font-size: 1.2rem;
            outline: none;
            border-radius: 50rem;
            border: 0.05rem solid rgb(255, 122, 122);
            caret-color: rgb(255, 122, 122);
            transition: all 0.4s ease;
            -webkit-transition: all -webkit-transform 0.4s ease;
            text-overflow: ellipsis;
        }

        .out {
            position: relative;
            padding: 0 2rem 0 3.5rem;
            height: 2.1rem;
            width: 6rem;
            font-size: 1.2rem;
            outline: none;
            border-radius: 50rem;
            border: 0.05rem solid rgb(255, 122, 122);
            caret-color: rgb(255, 122, 122);
            color: rgba(255, 255, 255, 0);
            background: url("./images/magnifier.svg") no-repeat center/10% 1.5rem;
            transition: all 0.4s ease;
            -webkit-transition: all -webkit-transform 0.4s ease;
            text-overflow: ellipsis;
        }

        h1 {
            color: rgba(255, 122, 122, 1);
            transition: var(--transition-1);
        }

        h1 > a {
            text-decoration: none;
            color: rgba(255, 122, 122, 1);
        }

        h1 > a::selection {
            color: rgb(255, 122, 122);
        }

        h4 {
            margin-bottom: 5px;
            color: #ffc6c6;
        }

        h4::selection {
            color: #ffc6c6;
        }

        .github-corner {
            transition: var(--transition-1);
        }

        .mode {
            margin-top: 20px;
        }

        .toggele {
            display: flex;
            position: abolute;
            border: 1px solid #ffc6c6;
            height: 35px;
            width: 120px;
            border-radius: 50px;
            justify-content: flex-start;
            align-content: center;
            transition: var(--transition-1);
        }

        .toggeler {
            display: grid;
            cursor: pointer;
            background-color: rgba(255, 198, 198, 1);
            color: #fff;
            height: 25px;
            width: 60px;
            border-radius: 50px;
            margin: 5px;
            text-align: center;
            align-content: center;
            align-self: flex-start;
            transition: var(--transition-1);
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -webkit-touch-callout: none;
        }

        .toggeler:hover {
            width: 70px;
            background-color: rgba(255, 122, 122, 0.7);
        }

        .toggeler::selection {
            color: #fff;
        }

        .strict {
            display: inline;
        }

        .loose {
            display: inline;
        }

        .selection {
            margin-top: 25vh;
            font-size: 2rem;
            font-weight: bold;
            color: #ffc6c6;
            transition: var(--transition-1);
        }

        .selection::selection {
            color: #64ceaa;
        }

        .footer {
            display: flex;
            width: 100vw;
            position: absolute;
            bottom: 2rem;
            color: rgb(147, 147, 147);
            justify-content: center;
            transition: var(--transition-1);
        }

        .footer > div > a {
            text-decoration: none;
            color: rgb(147, 147, 147);
        }

        .footer > div > a::selection {
            color: rgba(255, 122, 122, 1);
        }

        .copyrights {
            font-size: 0.8rem;
        }

        .copyrights::selection {
            color: rgb(147, 147, 147);
        }

        .copyrights > a::selection {
            color: rgb(147, 147, 147);
        }

        @media only screen and (max-width: 600px) {
            .selection {
                margin-top: 15vh;
            }

            .footer {
                bottom: 1.5rem;
                transition: var(--transition-1);
            }
        }

        @media only screen and (max-height: 500px) {
            .footer {
                display: none;
            }
        }

        #autoComplete {
            position: relative;
            padding: 0 2rem 0 0.5rem;
            height: 2.1rem;
            width: 3rem;
            font-size: 1.2rem;
            outline: 0;
            border-radius: 50rem;
            border: .05rem solid #ff7a7a;
            caret-color: #ff7a7a;
            color: rgba(255, 255, 255, 0);
            background-image: url(https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/css/images/magnifier.svg);
            background-repeat: no-repeat;
            background-size: 1.2rem;
            background-origin: border-box;
            background-position: center;
            transition: all .4s ease;
            -webkit-transition: all -webkit-transform .4s ease;
            text-overflow: ellipsis;
        }
    </style>
    <link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
    <script src="https://cdn.themesinfo.com/detector.js"></script>
</head>
<div class="body" align="center">
    <input id="autoComplete" type="text" tabindex="1" placeholder="Food &amp; Drinks">
    <ul id="autoComplete_list"></ul>
    <div class="mode">
        <h4>mode</h4>
        <div class="toggele">
            <div class="toggeler">Strict</div>
        </div>
    </div>
    <div class="selection"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@7.2.0/dist/js/autoComplete.js"></script>

@include('pace')

<script>$(document).ajaxStart(function () {
        Pace.restart();
    });</script>

<script src="{{ qm_api_asset('/js/app.js') }}" type="text/javascript"></script>