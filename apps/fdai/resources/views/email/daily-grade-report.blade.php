@component('mail::message')
    {{--# $title--}}

    $body

    @component('mail::button', ['url' => $url])
        Button Text
    @endcomponent

    Love,<br>
    {{ app_display_name() }}
@endcomponent
