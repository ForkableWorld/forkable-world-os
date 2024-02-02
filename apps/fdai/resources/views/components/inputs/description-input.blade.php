<div class="form-group">
    <label for="app_description" class="col-md-2 control-label">Description*</label>
    <div class="col-md-6">
        <small>This is the tag line of your app that will appear next to your logo on the {{app_display_name()}} App Gallery screen, if the app is featured or added by the user.  (140 character limit)</small>
        @if(isset($application))
            <input type="text" maxlength="140" id="app_description" name="app_description" class="form-control" placeholder="Description" value="{!! Request::old('app_description', $application->app_description) !!}">
        @else
            <input type="text" maxlength="140" id="app_description" name="app_description" class="form-control" placeholder="Description" value="{!! Request::old('app_description') !!}">
        @endif
    </div>
</div>
