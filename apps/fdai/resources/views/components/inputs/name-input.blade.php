<div class="form-group status">
    <label for="app_display_name" class="col-md-2 control-label">
        @if(isset($application) && Auth::user()->ID != $application->id)
            <i class="fa fa-info-circle fa-lg" data-toggle="tooltip" data-placement="top" title="Only the owner of the app can change its name"></i>
        @endif
        App Name*
    </label>
    <div class="col-md-6">
        @if(isset($application) && Auth::user()->ID == $application->user_id)
            <input type="text" id="app_display_name" maxlength="100" name="app_display_name" class="form-control" value="{!! Request::old('app_display_name', $application->app_display_name) !!}">
        @elseif(isset($application))
            <div>{{ $application->app_display_name }}</div>
        @else
            <input type="text" maxlength="100" id="app_display_name" name="app_display_name" class="form-control" placeholder="Application Display Name" value="{!! Request::old('app_display_name') !!}">
        @endif
    </div>
</div>
