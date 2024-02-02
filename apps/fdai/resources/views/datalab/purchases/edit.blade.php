<?php /** @var App\Models\Purchase $purchase */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
   @include('model-header')
   <div class="content">
       @include('adminlte-templates::common.errors')
       <div class="box box-primary">
           <div class="box-body">
               <div class="row">
                   {!! Form::model($purchase, ['route' => ['datalab.purchases.update', $purchase->id], 'method' => 'patch']) !!}

                        @include('datalab.purchases.fields')

                   {!! Form::close() !!}
               </div>
           </div>
       </div>
   </div>
@endsection
