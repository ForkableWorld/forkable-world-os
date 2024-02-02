@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1>
            Bshaffer Oauth Access Token
        </h1>
    </section>
    <div class="content">
        @include('adminlte-templates::common.errors')
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    {!! Form::open(['route' => 'datalab.oAuthAccessTokens.store']) !!}

                        @include('datalab.oa_access_tokens.fields')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
