<?php /** @var Illuminate\Support\Collection|App\Models\Card[] $cards
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>
@extends('layouts.material-app', ['activePage' => 'example-dashboard', 'titlePage' => __('Example Dashboard')])

@section('content')
  <div class="content">
    <div class="container-fluid">
        @include('example-material-stat-cards')
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
    });
  </script>
@endpush