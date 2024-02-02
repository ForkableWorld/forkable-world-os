<div class="form-group">
    <label for="title" class="col-md-2 control-label">Homepage*</label>
    <div class="col-md-6">
        <small>Informational landing page</small>
        @if(isset($application))
            <input type="text" id="homepage_url" name="homepage_url" placeholder="https://yoursite.com" class="form-control" value="{!! Request::old('homepage_url', $application->homepage_url) !!}">
        @else
            <input type="text" id="homepage_url" name="homepage_url" placeholder="https://yoursite.com" class="form-control" value="{!! Request::old('homepage_url') !!}">
        @endif
    </div>
</div>
