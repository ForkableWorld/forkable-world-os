

<?php /** @var Illuminate\Support\Collection|App\Models\Card[] $cards
 * @var \App\Buttons\QMButton $button
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>
@extends('layouts.material-app', ['activePage' => 'datalab-dashboard', 'titlePage' => __('Dashboard')])

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
            @foreach( \App\Menus\DataLab\DataLabIndexRoutesMenu::instance()->getButtons() as $button )
                {!! $button->getMaterialStatCard() !!}
            @endforeach
            </div>
            @include('example-material-charts')
            @include('example-material-tables')
        </div>
    </div>
@endsection
