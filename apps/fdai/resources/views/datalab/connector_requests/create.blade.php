@extends('layouts.admin-lte-app')

@section('content')
    <section class="content-header">
        <h1>
            Connector Request
        </h1>
    </section>
    <div class="content">
        @include('adminlte-templates::common.errors')
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    {!! Form::open(['route' => 'datalab.connectorRequests.store']) !!}

                        @include('datalab.connector_requests.fields')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
