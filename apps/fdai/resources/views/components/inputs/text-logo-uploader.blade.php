<div class="form-group">
    <h3>Wide Text Logo</h3>
    <p>Ideally, a 100px high by 500px wide png with a transparent background containing the name of your app that looks good on a dark background. </p><br>
    <div class="col-md-6">
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail" style="background-color: #23272D;">
                @if(isset($application) && $application->text_logo && strpos($application->text_logo,'http') !== false)
                    <img style="max-width: 128px;"  src="{{$application->text_logo}}" alt="..." />
                @elseif(isset($application) && $application->text_logo)
                    {!! cl_image_tag($application->text_logo, array( "width" => 500, "height" => 100)) !!}
                @else
                    <img src="https://placehold.it/500x100" alt="..." />
                @endif
            </div>
            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 500px;"></div>
            <div>
                <span class="btn btn-primary btn-file">
                    <span class="fileinput-new">Select image</span>
                    <span class="fileinput-exists">Change</span>
                    <input type="file" name="text_logo" id="text_logo" />
                </span>
                <a href="#" class="btn btn-primary fileinput-exists" data-dismiss="fileinput">Remove</a>
                <button type="submit" class="btn btn-success">@lang('button.save')</button>
            </div>
        </div>
    </div>
</div>
