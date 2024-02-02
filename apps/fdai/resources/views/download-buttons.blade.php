@if( $includeMobile ?? false )
    @include('components.buttons.play-android-download-button')
    @include('components.buttons.itunes-ios-app-download-button')
@endif
<div style="text-align: center; margin: auto;">
    @include('components.buttons.chrome-extension-button')
</div>
<div style="text-align: center; margin: auto;">
    @include('components.buttons.web-app-button')
</div>