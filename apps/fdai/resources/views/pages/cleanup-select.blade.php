@extends('layouts.material-app', ['activePage' => 'cleanup-select', 'titlePage' => __('Cleanup Select')])
@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-info">
            <h4 class="card-title ">Affected Records</h4>
            <div class="card-category"></div>
          </div>
          <div class="card-body">
              {!! $selectQuery  !!}
            <div class="table-responsive">
                {!! $results !!}
            </div>
          </div>
        </div>
      </div>
    </div>
      <a href="{{ $updateUrl }}">
          <div class="row">
              <div class="col-md-12">
                  <div class="card">
                      <div class="card-header card-header-info">
                          <h4 class="card-title ">Click to Execute</h4>
                      </div>
                      <div class="card-body">
                          {!! $updateQuery !!}
                      </div>
                  </div>
              </div>
          </div>
      </a>
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