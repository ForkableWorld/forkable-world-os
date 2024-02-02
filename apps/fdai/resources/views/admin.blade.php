

<?php /** @var Illuminate\Support\Collection|App\Models\Card[] $cards
 * @var \App\Buttons\QMButton $button
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>
@extends('layouts.material-app', ['activePage' => 'admin-dashboard', 'titlePage' => __('Admin Dashboard')])

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

@push('js')
    <script>
        $(document).ready(function() {
            // Javascript method's body can be found in assets/js/demos.js
            md.initDashboardPageCharts();
            $("#searchInput").on('keyup', function() {
                var searchValue = $(this).val();
                searchAndFilter(searchValue)
            });

            function searchAndFilter(searchTerm) {
                debugger
                searchTerm = searchTerm.toUpperCase();
                $(".card-stats-container").each(function() {
                    if (searchTerm === '') {
                        $(this).show();
                    } else {
                        var currentText = $(this).text();
                        currentText = currentText.toUpperCase();
                        if (currentText.indexOf(searchTerm) >= 0) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    }
                });
            }
        });
    </script>
@endpush
