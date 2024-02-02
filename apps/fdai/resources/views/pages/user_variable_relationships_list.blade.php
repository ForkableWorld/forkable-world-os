@extends('layouts.material-app', ['activePage' => 'global-variable-relationships-list', 'titlePage' => __('Global Variable Relationships List')])

@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-info">
            <h4 class="card-title ">Global Variable Relationships List</h4>
            <p class="card-category"> Discoveries from aggregated data</p>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="global-variable-relationships-list" class="table">
                <thead class=" text-primary">
                @foreach( $headers as $header )
                    <th>
                        {{ $header }}
                    </th>
                @endforeach
                </thead>
                <tbody>
                    @foreach( $rows as $row )
                        <tr>
                        @foreach( $row as $cell )
                            <td>
                                {{ $cell }}
                            </td>
                        @endforeach
                        </tr>
                    @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
    <script>
        $(document).ready( function () {
            $('#global-variable-relationships-list').DataTable();
        } );
    </script>
@endpush
