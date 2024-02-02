<div class="form-group">
    <h3>Icon</h3>
    <p>Ideally, a 512px by 512px  icon or square logo png image with a transparent background.  <a href="http://code.ionicframework.com/resources/icon.psd">Photoshop Icon Template</a></p><br>
    <div class="col-md-6">
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail">
                @if(isset($application) && $application->icon_url && strpos($application->icon_url, 'http') !== false)
                    <img style="max-width: 128px;"  src="{{$application->icon_url}}" alt="..." />
                @elseif(isset($application) && $application->icon_url)
                    {!! cl_image_tag($application->icon_url, array( "width" => 128, "height" => 128)) !!}
                @else
                    <img style="max-width: 128px;"  src="https://placehold.it/512x512" alt="..." />
                @endif
            </div>
            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 128px;"></div>
            <div>
                <span class="btn btn-primary btn-file">
                    <span class="fileinput-new">Select image</span>
                    <span class="fileinput-exists">Change</span>
                    <input type="file" name="icon_url" id="icon_url" />
                </span>
                <a href="#" class="btn btn-primary fileinput-exists" data-dismiss="fileinput">Remove</a>
                <button type="submit" class="btn btn-success">@lang('button.save')</button>
            </div>
        </div>
    </div>
</div>
