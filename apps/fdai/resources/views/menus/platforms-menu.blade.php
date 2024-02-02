<li>
    <a target="_blank"
       href="https://web.quantimo.do"><i class="menu-icon fa fa-fw fa-globe"></i>
        <span class="mm-text ">Web App</span></a>
</li>
@if(getHostAppSettings()->additionalSettings->downloadLinks->androidApp)
    <li>
        <a target="_blank"
           href="{{getHostAppSettings()->additionalSettings->downloadLinks->androidApp}}">
            <i class="menu-icon fa fa-fw fa-android"></i> <span class="mm-text ">Android App</span>
        </a>
    </li>
@endif
@if(getHostAppSettings()->additionalSettings->downloadLinks->chromeExtension)
    <li>
        <a target="_blank"
           href="{{getHostAppSettings()->additionalSettings->downloadLinks->chromeExtension}}">
            <i class="menu-icon fa fa-fw fa-chrome"></i>
            <span class="mm-text ">Chrome Extension</span></a>
    </li>
@endif
@if(getHostAppSettings()->additionalSettings->downloadLinks->iosApp)
    <li>
        <a target="_blank"
           href="{{getHostAppSettings()->additionalSettings->downloadLinks->iosApp}}">
            <i class="menu-icon fa fa-fw fa-apple"></i> <span class="mm-text ">iOS App</span></a>
    </li>
@endif