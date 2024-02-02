<?php /** @var App\Models\Measurement $measurement */ ?>
@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
    @include('model-header')
   <div class="content">
       {!!  $measurement->getMaterialStatCard() !!}
	   <div id="material-cards-container"
	        class="content">
		   <div class="container-fluid">
			   @foreach(collect($measurement->getDataLabRelationshipMenu()->getButtons())->chunk(3) as $buttons)
				   <div class="row" >
					   @foreach($buttons as $button)
						   {!! $button->getMaterialStatCard() !!}
					   @endforeach
				   </div>
			   @endforeach

		   </div>
	   </div>
        <div class="box box-primary">
            <div class="box-body">
                <div class="row" style="padding-left: 20px">
                    @include('datalab.measurements.show_fields')
                    <a href="{{ route('datalab.measurements.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
