<?php /** @var \App\Models\Card $card
 * @var \App\Models\User $user
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>
@component('mail::message')
# Your Studies

Here are some new discoveries from your data!

{!! $user->getWpPostPreviewCardListHtml() !!}

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ app_display_name() }}
@endcomponent
