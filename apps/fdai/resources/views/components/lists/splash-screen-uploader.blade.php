<div class="form-group">
    <h3>Splash Screen</h3>
    <p>Splash screen dimensions vary for each platform, device, and orientation, so a square source image is required to generate each of the various screen sizes. The source image’s minimum dimensions should be 2208×2208 px, and the artwork should be centered within the square, because each generated image will be center cropped into landscape and portrait images.
        The splash screen’s artwork should roughly fit within a center square (1200×1200 px). <a href="http://code.ionicframework.com/resources/splash.psd">This template</a> provides the recommended size and guidelines about artwork’s safe zone.
    </p>
    <br>
    <div class="col-md-6">
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail">
                @if(isset($application) && $application->splash_screen && strpos($application->splash_screen,'http') !== false)
                    <img style="max-width: 128px;"  src="{{$application->splash_screen}}" alt="..." />
                @elseif(isset($application) && $application->splash_screen)
                    {!! cl_image_tag($application->splash_screen, array( "width" => 128, "height" => 128)) !!}
                @else
                    <img style="max-width: 128px;" src="https://placehold.it/2208x2208" alt="..." />
                @endif
            </div>
            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 128px;"></div>
            <div>
                <span class="btn btn-primary btn-file">
                    <span class="fileinput-new">Select image</span>
                    <span class="fileinput-exists">Change</span>
                    <input type="file" name="splash_screen" id="splash_screen" />
                </span>
                <a href="#" class="btn btn-primary fileinput-exists" data-dismiss="fileinput">Remove</a>
                <button type="submit" class="btn btn-success">
                    @lang('button.save')
                </button>
            </div>
        </div>
    </div>
</div>
