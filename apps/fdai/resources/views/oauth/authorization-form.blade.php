<!DOCTYPE html>
<html>
<head>
    <title>Authorize Request</title>
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
            position: absolute;
            max-width: 380px;
            top: 108px;
            left: 0;
            right: 0;
            margin-left: auto;
            margin-right: auto;
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
            border-bottom: 1px solid #E3E3E3;
            padding: 14px;
        }

        #button-approve {
            background-color: #229DA5;
            border: 1px solid #32ADB5;
            color: #fff;
        }

        #button-approve:hover {
            -webkit-box-shadow: rgba(160, 255, 160, 0.9) 0 4px 10px -1px;
            box-shadow: rgba(160, 255, 160, 0.9) 0 4px 10px -1px;
        }

        #button-deny {
            background-color: #F8F8F8;
            border: 1px solid #32ADB5;
            color: #666666;
        }

        #button-deny:hover {
            -webkit-box-shadow: rgba(255, 180, 180, 0.9) 0 4px 10px -1px;
            box-shadow: rgba(255, 180, 180, 0.9) 0 4px 10px -1px;
        }

        .logo-container {
            width: 380px;
            padding: 0;
            text-align: center;
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
        }
    </style>
    @include('loggers-js')
</head>
<body>{!! Analytics::render() !!}
<div class="dialog" id="authorize-dialog">
    @if(!empty($oauthApplication))
        <div class="logo-container">
            <img src="{{$user->avatar}}" alt="" style="width:100px;height:100px;text-align: center;border-radius: 50%;"> &nbsp; &nbsp;
            @if(!empty($oauthApplication) && !empty($oauthApplication->icon_url))
                {!! cl_image_tag($oauthApplication->icon_url, array( "width" => 200, "id" => "logo")) !!}
            @elseif(!empty($oauthApplication->user->avatar) )
                <img src="{{$oauthApplication->user->avatar}}" alt="" style="width:100px;height:100px;text-align: center;border-radius: 50%;">
            @endif
        </div>
        <br>
    @endif
    <div class="dialog-content">
        <div id="request-heading">
            <span id="client-name">
                @if(!empty($oauthApplication) && $oauthApplication->study)
                    {{ $oauthApplication->user->display_name }}
                @elseif(!empty($oauthApplication))
                    {{ $oauthApplication->app_display_name }}
                @else
                    {{ $client_id }}
                @endif
            </span>

            would like to
        </div>

        <ul id="permissions-list">
            <?php
            echo "<li>" . $scopeDescription . "</li>";
            ?>
            @if(!empty($loginName))
                <li>
                    <small>Not <strong><?php echo $loginName ?></strong>?
                        <a href="/auth/login?logout=1&redirectTo=<?php echo urlencode($requestPath)?>">Login</a> as 
	                    another user.
                    </small>
                </li>
            @endif
        </ul>

        {!! Form::open(['method' => 'POST','class'=>'form-horizontal', 'url'=> route($route.'oauth.authorize.post',$formParams)]) !!}
            <button class="dialog-button" id="button-approve" type="submit" name="approve" value="1">Approve</button>
            <button class="dialog-button" id="button-deny" type="submit" name="deny" value="1">Deny</button>
        {!! Form::hidden('client_id', $formParams['client_id']) !!}
        {!! Form::hidden('redirect_uri', $formParams['redirect_uri']) !!}
        {!! Form::hidden('response_type', $formParams['response_type']) !!}
        {!! Form::hidden('scope', $formParams['scope']) !!}
        {!! Form::hidden('state', $formParams['state']) !!}
        {!! Form::close() !!}
    </div>
</div>
</body>
</html>
