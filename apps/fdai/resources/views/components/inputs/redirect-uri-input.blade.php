<div class="form-group">
    <label for="redirect-uris" class="col-md-2 control-label">Redirect URIs</label>
    <div class="col-md-5">
        <small>The Redirect URI (A.K.A Callback URL) is used in the OAuth 2.0 authentication process. It is the uri that our systems post your an authorization code to, which is then
            exchanged for an access token which you can use to authenticate subsequent API calls.  HTTPS must be used.  If you have more than one redirect uri, you must specify
            which one to use by adding a redirect_uri parameter in your OAuth request.
            See <a target="_blank" href="https://developer.quantimo.do/docs">https://developer.quantimo.do/docs</a> for more information. Please enter one url per line</small>
        @if(isset($uris))
            <textarea id="redirect-uris" name="redirect_uris" class="textarea edi-css" placeholder="https://">{!! Request::old('redirect_uris', $uris) !!}</textarea>
        @else
            <textarea id="redirect-uris" name="redirect_uris" class="textarea edi-css" placeholder="Must include https:// or http://localhost">{!! Request::old('redirect_uris') !!}</textarea>
        @endif
    </div>
</div>