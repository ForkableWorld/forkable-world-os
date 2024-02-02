<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @var \App\Buttons\QMButton $button */
?>
<li class="service-list-item service_list_item__service-list-item__1I5Y6"
    style="background-color: {{ $button->getBackgroundColor() }};">
    <a href="{{ $button->getUrl() }}">
        <img src="{{ $button->getImage() }}"
            title="{{ $button->getTitleAttribute() }}"
            alt="{{ $button->getTitleAttribute() }}"
            class="service_list_item__service-icon__3gw6h">
        <h2 class="service_list_item__service-title__1NqPJ">
            {{ $button->getTitleAttribute() }}
        </h2>
        <div class="service_list_item__works-with-group__19wPJ">
            <ul class="service_list_item__works-with-list__114fC">
                <li class="service_list_item__works-with-list-item__3LJWd"><img
                        src="https://assets.ifttt.com/images/channels/799977804/icons/monochrome_large.png"
                        title="Google Sheets" alt="Google Sheets" class="service_list_item__works-with-img__3EofB">
                </li>
            </ul>
        </div>
    </a>
</li>
