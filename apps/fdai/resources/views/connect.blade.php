@extends('layouts.material-app', ['activePage' => 'table', 'titlePage' => __('Table List')])

@section('content')
    @foreach($connectors as $connector)
        {{ \App\Logging\QMLog::print_r($connector, true) }}
        @include('connector')
    @endforeach
@endsection

@push('js')
    <script>
        $(document).ready( function () {
            $('#table_id').DataTable();
        } );

    </script>
@endpush
