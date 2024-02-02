@extends('layouts/default')
{{-- Web site Title --}}
@section('title')
	@lang('apps/title.management')
	@parent
@stop
@section('header_styles')
	<link href="{{ qm_asset('css/quantimodo/app.css') }}" rel="stylesheet" type="text/css"/>
@stop
{{-- Content --}}
@section('content')
	<!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-primary ">
					<div class="panel-heading clearfix">
						<h4 class="panel-title pull-left"><i class="fa fa-mobile"></i> Your Apps </h4>
						<div class="pull-right">
							<a id="create-app-button" 
							   href="{{ route('create/app') }}"
							   class="btn btn-sm btn-default"><span
									class="glyphicon glyphicon-plus"></span> @lang('button.create')
							</a>
						</div>
					</div>
					<br/>
					<div class="panel-body">
						@if (count($applications) >= 1)
							<table class="table table-bordered">
								<thead>
								<tr>
									<th>@lang('apps/table.name')</th>
									<th>@lang('apps/table.client_id')</th> {{--<th>@lang('apps/table.client_secret')</th>--}} {{--<th>@lang('apps/table.status')</th>--}} {{--<th>@lang('apps/table.created_at')</th>--}}
									<th>@lang('apps/table.actions')</th>
								</tr>
								</thead>
								<tbody>
								@foreach ($applications as $application)
									<tr class="status">
										<td>
											<a href="{{ route('update/app', $application->id) }}"> {!! $application->app_display_name !!} </a>
										</td>
										<td>{!! $application->client_id !!}</td> {{--<td>{!! $application->credentials->client_secret !!}</td>--}}
										{{--<td class="{!! $application->status !!}">{!! $application->status !!}</td>--}}
										{{--<td>{!! $application->created_at->diffForHumans() !!}</td>--}}
										<td>
											<a href="{{ route('update/app', $application->client_id) }}"> <i
													class="fa fa-fw fa-pencil text-warning" data-toggle="tooltip"
													data-placement="top" title="Edit application details"></i> Edit</a>&nbsp;
											&nbsp; &nbsp;
											<a href="{{ route('integration/app', $application->client_id) }}"> <i
													class="fa fa-fw fa-code" data-toggle="tooltip" data-placement="top"
													title="Integration Docs"></i> Integrate</a>&nbsp; &nbsp; &nbsp;
											<a href="https://{{$application->client_id}}.quantimo.do/physician?accessToken={{Auth::user()->access_token}}">
												<i class="fa fa-fw fa-user-md" data-toggle="tooltip"
												   data-placement="top"
												   title="Admin Page"></i> {{physicianAlias() . ' Portal'}}</a>&nbsp;
											&nbsp; &nbsp;
											<a href="https://{{$application->client_id}}.quantimo.do?accessToken={{Auth::user()->access_token}}">
												<i class="fa fa-fw fa-user" data-toggle="tooltip" data-placement="top"
												   title="End User Web App"></i> {{patientAlias() . ' Portal'}}</a>&nbsp;
											&nbsp; &nbsp;
											@if(Auth::user()->ID == $application->user_id && Auth::user()->inRole('admin'))
												<a href="{{ route('confirm-delete/app', $application->id) }}"
												   data-toggle="modal" data-target="#delete_confirm">
													<i class="fa fa-fw fa-times text-danger" data-toggle="tooltip"
													   data-placement="top" title="Delete this application"></i> Delete
												</a>
											@endif
										</td>
									</tr>
								@endforeach
								</tbody>
							</table>
						@else
							<h5 class="text-center">Please create your first application by clicking <a
									href="{{ route('create/app') }}" class="btn btn-sm btn-default"><span
										class="glyphicon glyphicon-plus"></span> @lang('button.create')</a></h5>
						@endif
						@if (isset($applications) && count($applications) >= 1)
							<table class="table table-bordered">
								<thead>
								<tr>
									<th>@lang('apps/table.name')</th>
									<th>@lang('apps/table.client_id')</th> {{--<th>@lang('apps/table.client_secret')</th>--}} {{--<th>@lang('apps/table.status')</th>--}} {{--<th>@lang('apps/table.created_at')</th>--}}
									<th>@lang('apps/table.actions')</th>
								</tr>
								</thead>
								<tbody>
								@foreach ($applications as $application)
									<tr class="status">
										<td>
											<a href="{{ route('update/app', $application->id) }}"> {!! $application->app_display_name !!} </a>
										</td>
										<td>{!! $application->client_id !!}</td> {{--<td>{!! $application->credentials->client_secret !!}</td>--}}
										{{--<td class="{!! $application->status !!}">{!! $application->status !!}</td>--}}
										{{--<td>{!! $application->created_at->diffForHumans() !!}</td>--}}
										<td>
											<a href="{{ route('update/app', $application->id) }}"> <i
													class="fa fa-fw fa-pencil text-warning" data-toggle="tooltip"
													data-placement="top" title="Edit application details"></i> </a>
											@if(Auth::user()->ID == $application->user_id)
												<a href="{{ route('confirm-delete/app', $application->id) }}"
												   data-toggle="modal" data-target="#delete_confirm">
													<i class="fa fa-fw fa-times text-danger" data-toggle="tooltip"
													   data-placement="top" title="Delete this application"></i>
												</a>
											@endif
										</td>
									</tr>
								@endforeach
								</tbody>
							</table>
						@endif
					</div>
				</div>
			</div>
		</div>    <!-- row-->
	</section>
@stop
