<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>API Reference</title>

    <link rel="stylesheet" href="{{ qm_asset('css/api-docs.css') }}"/>
    <script
        src="https://code.jquery.com/jquery-2.2.4.js"
        integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI="
        crossorigin="anonymous"></script>
    <script
        src="https://code.jquery.com/ui/1.11.3/jquery-ui.js"
        integrity="sha256-0vBSIAi/8FxkNOSKyPEfdGQzFDak1dlqFKBYqBp1yC4="
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-highlight@3.5.0/jquery.highlight.min.js"></script>
    <script src="{{ qm_asset('js/jquery.tocify.js') }}"></script>
    <script src="{{ qm_asset('js/energize.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.1.0/highlight.min.js"
            integrity="sha256-fkOAs5tViC8MpG+5VCOqdlSpLL8htz4mdL2VZlWGoMA=" crossorigin="anonymous"></script>
    <script src="{{ qm_asset('js/imagesloaded.min.js')}}"></script>
    <!--    <script src="js/lib/lunr.js"></script>-->
    <script src="{{ qm_asset('js/api-docs.js')}}"></script>

    <script>
        $(function () {
            setupLanguages(["javascript", "bash", "php", "python"]);
        });
    </script>
</head>

<body class="">
<a href="#" id="nav-button">
      <span>
        NAV
        <img src="/img/navbar.png"/>
      </span>
</a>
<div class="tocify-wrapper">
    <img src="/img/logo.png"/>
    <div class="lang-selector">
        <a href="#" data-language-name="javascript">javascript</a>
        <a href="#" data-language-name="bash">bash</a>
        <a href="#" data-language-name="php">php</a>
        <a href="#" data-language-name="python">python</a>
    </div>
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>
    <ul class="search-results"></ul>
    <div id="toc">
    </div>
    <ul class="toc-footer">
        <li>
            <a href='https://help.quantimo.do'>Need Help?</a>
        </li>
    </ul>
</div>
<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <!-- START_INFO -->
        <h1>Info</h1>
        <p>
            Welcome to the QuantiModo API!
{{--            <a href="{{ route("apidoc.json") }}">Get Postman Collection</a>--}}
        </p>
        <!-- END_INFO -->
        <h1>general</h1>
        <!-- START_839bc5f69c5c0c773239478be58e3a05 -->
        @foreach(\App\Utils\QMRoute::getAPIRoutes() as $qmRoute)

            <h2>{{ $qmRoute->getTitleAttribute() }}</h2>
            @if( $qmRoute->requiresAuth() )
{{--                <p><small>Requires authentication</small></p>--}}
            @endif

            <div style="max-width: 40%; padding-left: 15px;">
                {!! $qmRoute->getDescriptionHtml() !!}
            </div>
            <blockquote>
                <p>Example request:</p>
            </blockquote>
            <pre>
    <code class="language-javascript">
const url = new URL(
    "{{ $qmRoute->getUrl() }}
");

let headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "Authorization": "Bearer test-token",
};

fetch(url, {method: "{{ strtoupper($qmRoute->getMethod()) }}", headers: headers})
    .then(response =&gt; response.json())
    .then(json =&gt; console.log(json));
    </code>
</pre>
            <pre>
    <code class="language-bash">curl -X {{ strtoupper($qmRoute->getActionMethod()) }} \
    -G "{{ $qmRoute->getUrl() }}" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -H "Authorization: Bearer test-token"
    </code>
</pre>
            <pre><code class="language-php">
$client = new \GuzzleHttp\Client();
$response = $client-&gt;get(
    '{{ $qmRoute->getUrl() }}',
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'Authorization' =&gt; 'Bearer test-token',
        ],
    ]
);
$body = $response-&gt;getBody();
\App\Logging\QMLog::print_r(json_decode((string) $body));</code></pre>
            <pre>
    <code class="language-python">import requests
import json

url = '{{ $qmRoute->getUrl() }}'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': 'Bearer test-token'
}
response = requests.request('{{ strtoupper($qmRoute->getActionMethod()) }}', url, headers=headers)
response.json()
    </code>
</pre>
            <blockquote>
                <p>
                    Example response (200):
                </p>
            </blockquote>
            <pre><code class="language-json">{{ $qmRoute->getExampleResponseJson() }}</code></pre>
            <h3>HTTP Request</h3>
            <p>
                <code>
                    {{ $qmRoute->getMethod() }} {{ $qmRoute->uri }}
                </code>
            </p>

    @endforeach
    <!-- END_839bc5f69c5c0c773239478be58e3a05 -->

    </div>
    <div class="dark-box">
        <div class="lang-selector">
            <a href="#" data-language-name="javascript">javascript</a>
            <a href="#" data-language-name="bash">bash</a>
            <a href="#" data-language-name="php">php</a>
            <a href="#" data-language-name="python">python</a>
        </div>
    </div>
</div>
</body>
</html>
