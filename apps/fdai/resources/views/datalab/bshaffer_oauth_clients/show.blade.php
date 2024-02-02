<?php /** @var App\Models\OAClient $bshafferOauthClient */ ?>
@extends('layouts.admin-lte-app')

@section('content')
    @include('model-header')
    <div class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.oa_clients.show_fields')
                    <a href="{{ route('datalab.oAuthClients.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
