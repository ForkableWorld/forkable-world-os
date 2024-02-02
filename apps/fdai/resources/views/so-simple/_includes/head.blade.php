<head>
  @include('meta')
  <script>
    /* Cut the mustard */
    if ( 'querySelector' in document && 'addEventListener' in window ) {
      document.documentElement.className = document.documentElement.className.replace(/\bno-js\b/g, '') + 'js';
    }
  </script>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/plugins/pace/pace.min.css">
    <style>
        .pace .pace-progress {
            background: #2299dd;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" >
    <!-- Sortable tables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
    @include('fontawesome')
    <link rel="stylesheet" href="{{ qm_asset('css/so-simple.css') }}">
    <link rel="stylesheet" href="{{ qm_asset('css/jekyll-text-theme.css') }}">
    <link rel="stylesheet" href="{{ qm_asset('css/toc.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tocbot/4.12.3/tocbot.css">
{{--  <link rel="stylesheet" href="https://studies.quantimo.do/assets/css/skins/default.css">--}}
    @include('so-simple/_includes/head-feed')
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	@include('psychedelic-loader')
</head>
