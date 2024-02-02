@extends('layouts.material-app', ['activePage' => 'cleanup-update', 'titlePage' => __('Cleanup Update')])
@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-info">
            <h4 class="card-title ">Query</h4>
          </div>
          <div class="card-body">
              {!! $updateQuery  !!}
          </div>
        </div>
      </div>
    </div>
      <div class="row">
          <div class="col-md-12">
              <div class="card">
                  <div class="card-header card-header-info">
                      <h4 class="card-title ">Result</h4>
                  </div>
                  <div class="card-body">
                      {{ $result }}
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>
@endsection