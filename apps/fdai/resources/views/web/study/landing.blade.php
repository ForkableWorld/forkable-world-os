<!DOCTYPE html>
<html>
<head>
    @include('meta')
    <style>
        body {
            font-family: arial regular, arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            background-color: #fbfbfb;
        }

        #logo {
            padding-bottom: 8px;
        }

        .dialog {
            -webkit-border-radius: 3px;
            border-radius: 3px;
            z-index: 1000001;
            position: relative;
            max-width: 500px;
            top: 108px;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
        }

        #study-frame {
            width: 600px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            height:2000px;
            display:block;
            margin-top:130px;
        }

        .logo-container {
            width: 500px;
            padding: 0;
            text-align: center;
        }

        .dialog-content {
            padding: 15px;
            background: #fff;
            border: 1px solid #e5e5e5;
            -webkit-box-shadow: rgba(200, 200, 200, .7) 0 4px 10px -1px;
            box-shadow: rgba(200, 200, 200, .7) 0 4px 10px -1px;
        }

        .dialog-button {
            height: 36px;
            padding-right: 8px;
            padding-left: 8px;
            margin-left: 2px;
            margin-right: 2px;
            float: right;

            -webkit-border-radius: 2px;
            -moz-border-radius: 2px;
            border-radius: 2px;

            -webkit-box-shadow: rgba(200, 200, 200, 0.7) 0 4px 10px -1px;
            box-shadow: rgba(200, 200, 200, 0.7) 0 4px 10px -1px;

            -moz-transition: all 0.2s;
            -webkit-transition: all 0.2s;
            -o-transition: all 0.4s;
            transition: all 0.4s;
        }

        #request-heading {
            font-size: 16px;
            padding-left: 4px;
        }

        #client-name {
            font-weight: bold;
        }

        #permissions-list {
            list-style: none;
            padding-left: 0;
        }

        #permissions-list li:first-child {
            border-top: 1px solid #E3E3E3;
        }

        #permissions-list li {
            padding: 5px 4px;
            line-height: 24px;
        }

        #button-participate {
            background-color: #229DA5;
            border: 1px solid #32ADB5;
            color: #fff;
            box-sizing: border-box;
            align-items: flex-start;
            text-align: center;
            text-decoration: none;
            font-size:14px;
            line-height:36px;
            border-radius: 4px;

        }

        #button-participate:hover {
            -webkit-box-shadow: rgba(160, 255, 160, 0.9) 0 4px 10px -1px;
            box-shadow: rgba(160, 255, 160, 0.9) 0 4px 10px -1px;
        }
        .fb-share-button > span {
            vertical-align: inherit !important;
        }

        /* Responsive stuffs */
        @media only screen and (max-width: 768px) {
            .dialog {
                top: 16px;
                max-width: 100%;
                padding-left: 20px;
                padding-right: 20px;
            }

            .logo-container {
                width: 100%;
            }

            #study-frame {
                width: 100%;
                margin-top:50px;
            }
        }
    </style>
    @include('loggers-js')
</head>
<body>{!! Analytics::render() !!}
<div class="dialog" id="authorize-dialog">
    @if(!empty($study) && !empty($study->icon_url))
        <div class="logo-container">
            {!! cl_image_tag($study->icon_url, array( "width" => 200, "id" => "logo")) !!}
        </div>
    @else
        <div class="logo">
            <a href="{{home_page()}}">
                <img src="https://static.quantimo.do/img/QM-LOGO-black-300x85.png" alt="logo" height="59px" />
            </a>
        </div>
    @endif

    <div class="dialog-content">
        <div id="request-heading">
            Would you like to donate your data to this study?<br><br>
            <span id="client-name">
                {{ $study->app_display_name }}
            </span>
        </div>

        <ul id="permissions-list">
            <li><b>Principal Investigator:</b> {{ $study->user->display_name }}</li>
            <li><b>Contact:</b> {{ $study->user->user_email }}</li>
            @if ($study->app_description)
                <li>
                    <b>Study Question:</b> {{ $study->app_description }}
                </li>
            @else
                <li>
                    <b>Study Question:</b> What is the relationship between {{ $study->predictor->name }} and {{ $study->outcome->name }}?
                </li>
            @endif
            @if ($study->long_description)
                <li>
                    <b>Participant Instructions:</b> {{ $study->long_description }}
                </li>
            @else
                <li>
                    <b>Participant Instructions:</b> Track {{ $study->predictor->name }} and {{ $study->outcome->name }}
                    with these
                    <a target="_blank" href="{{ ionic_url('#/app/import') }}">apps or devices</a>.
                </li>
            @endif
            @if ($study->homepage_url)
                <li>
                    Learn more at <a href="{{ $study->homepage_url }}">{{ $study->homepage_url }}</a>
                </li>
            @endif
        </ul>

        <?php $shareUrl = getHostAppSettings()->additionalSettings->downloadLinks->webApp . '/api/v2/study/'.$study->client_id; ?>
        <div class="fb-share-button pull-left" data-href="{{ $shareUrl }}" data-layout="button"></div>
        <a href="https://twitter.com/share" class="twitter-share-button pull-left" data-url="{{ $shareUrl }}"
           data-text="{{ $study->app_display_name }}"
           data-via="quantimodo">Tweet</a>
        <div class="g-plus" data-action="share" data-annotation="none" data-href="{{ $shareUrl }}"></div>
        <a href="/oauth/authorize?response_type=token&client_id={{ $study->client_id }}&scope=readmeasurements"
                class="dialog-button"
                id="button-participate">
            Participate in this study
        </a>
    </div>
</div>

<iframe id="study-frame" src="/embeddable/?plugin=search-relationships&commonOrUser=common&outcome={{ $study->outcome->name }}&predictor={{ $study->predictor->name }}&hideSearchBoxes=true" frameborder="0" scrolling="no"></iframe>

<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=225078261031461";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<script src="https://apis.google.com/js/platform.js" async defer></script>
</body>
</html>
