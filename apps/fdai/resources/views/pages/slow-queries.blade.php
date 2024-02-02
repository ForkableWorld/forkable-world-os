@extends('layouts.material-app', ['activePage' => 'slow-queries', 'titlePage' => __('Slow Queries')])
@php
$queries = \App\Storage\DB\Writable::getSlowQueries();
@endphp
@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-info">
            <h4 class="card-title ">Slow Queries</h4>
            <p class="card-category"> Copy and run explain in MySQL Workbench for more details</p>
              <div class="row">
                  <div class="col-12 text-right">
                      <a href="https://local.quantimo.do/user-management/create" class="btn btn-sm btn-primary">Add user<div class="ripple-container"></div></a>
                  </div>
              </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
                {!! \App\Storage\DB\Writable::renderSlowQueryTable(1, $queries) !!}
            </div>
          </div>
        </div>
      </div>
    </div>
      @foreach($queries as $query)
          <div class="row">
              <div class="col-md-12">
                  <div class="card" id="{{ $query->id }}">
                      <div class="card-header card-header-info">
                          <h4 class="card-title ">{{ \App\Types\QMStr::titleCaseSlow($query->id) }} </h4>
                          <p class="card-category"> Avg Duration: {{ $query->avg_duration }} seconds</p>
                          <p class="card-category"> Branch: {{ $query->branch }}</p>
                          <p class="card-category"> Test: {{ $query->test }}</p>
                          <p class="card-category"> {{ $query->minutes_ago }} minutes ago</p>
                      </div>
                      <div class="card-body">
                          {!! $query->html !!}
                      </div>
                  </div>
              </div>
          </div>
      @endforeach
  </div>
</div>
@include('scroll-to-top-button')
@endsection
@push('js')
    <script>
        $(document).ready( function () {
            $('#data-table-id').DataTable({
                "pageLength": 50,
                "order": [[ 0, "desc" ]] // Descending duration
            });
        } );
    </script>
@endpush