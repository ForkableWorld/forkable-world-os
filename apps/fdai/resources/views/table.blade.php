<?php /** @var \Yajra\DataTables\Html\Builder $dataTable */ ?>

@section('css')
@include('layouts.datatables_css')
@endsection

{!! $dataTable->table(['width' => '100%', 'class' => 'table dt-responsive table-striped table-bordered'], false) !!}

@push('scripts')
@include('datatables_js')
{!! $dataTable->scripts() !!}
@endpush