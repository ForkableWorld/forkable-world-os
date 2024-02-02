<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @var \App\Buttons\QMButton $button */
?>
<div style="float:left">
    <a
        href="{{ $button->getUrl() }}"
        style="
            background: {{ $button->getBackgroundColor() }};
            border-radius: 100px;
            box-sizing: border-box;
            color: #fff;
            display: inline-block;
            font-family: 'AvenirNext-DemiBold', 'AvenirNext-Regular', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-weight: bold;
            font-size: 40px;
            margin: 20px auto;
            max-width: 100%;
            min-width: 300px;
            padding: 19px 40px 21px;
            text-align: center;
            text-decoration: none;
            white-space: nowrap
                "
        target="_blank"
        data-saferedirecturl="{{ $button->getUrl() }}">
        {!! $button->getFontAwesomeHtml() !!}
        {{ $button->getTitleAttribute() }}
    </a>
</div>
