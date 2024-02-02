<?php /** @var App\Models\User $loggedInUser */ ?>

@extends('layouts/default')
{{-- Web site Title --}}
@section('title')
    @lang('apps/title.edit')
    @parent
@stop
@section('header_styles')
    <link href="{{ qm_asset('vendors/twitter-bootstrap-wizard/prettify.css') }}" rel="stylesheet">
    <link href="{{ qm_asset('vendors/select2/select2.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('vendors/select2/select2-bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('css/quantimodo/plans.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('css/quantimodo/app.css') }}" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">
        function hideProgress() {
            document.getElementById('spinner').style.display = 'none';
        }
    </script>
@stop
{{-- Content --}}
@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-primary ">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            Application: {!! $application->app_display_name !!}
                        </h4>
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <div>
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#data" aria-controls="data" role="tab"
                                       data-toggle="tab">Application
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#settings" aria-controls="settings" role="tab"
                                       data-toggle="tab">Settings
                                    </a>
                                </li>
                                {{--<li role="presentation"><a href="#images" aria-controls="settings" role="tab" data-toggle="tab">Images</a></li>--}}
                                @if($application->user_id == Auth::user()->ID)
                                    <li role="presentation">
                                        <a href="#people" aria-controls="people" role="tab"
                                           data-toggle="tab">Collaborators
                                        </a>
                                    </li>
                                @endif
                                <li role="presentation">
                                    <a href="{{ route('integration/app', $application->id) }}"
                                       aria-controls="Integration">Integration
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#users" aria-controls="users" role="tab"
                                       data-toggle="tab">Users
                                    </a>
                                </li>
                                @if($application->user_id == Auth::user()->ID && $userCount > 10)
                                    <li role="presentation">
                                        <a href="#billing" aria-controls="billing" role="tab"
                                           data-toggle="tab">Billing
                                        </a>
                                    </li>
                                @endif
                                {{--<li role="presentation"><a href="#config-file" aria-controls="config-file" role="tab" data-toggle="tab">Config File</a></li>--}}
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div id="data" role="tabpanel" class="tab-pane fade in active">
                                    <form class="form-horizontal mt10" role="form" method="post"
                                          enctype="multipart/form-data" action="">
                                        <!-- CSRF Token -->
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                        <div id="app-preview" class="col-md-12">
                                            <div id="spinner" style="position: absolute; top: 25%; left: 50%">
                                                <img id="loader-image"
                                                     style="width: 25px"
                                                     src="https://static.quantimo.do/img/loaders/circular_loader.gif"
                                                     alt="loader"/>
                                                {{--@include('loaders.ring-loader')--}}
                                            </div>
                                            <iframe id="iframe" width="100%" height="700" onload="hideProgress();"
                                                    frameborder="0"
                                                    src="https://builder.quantimo.do/#/app/configuration?clientId={{
                                                    $application->client_id
                                                    }}&showPopOut=true&quantimodoAccessToken={{
                                                    $loggedInUser->getOrCreateAccessTokenString($application->client_id) }}&apiUrl={{
                                                    getAppHostNameWithoutProtocol() }}">
                                            </iframe>
                                        </div>
                                        <br>
                                        <a style="font-size: 30px; text-align: center;" target='_blank'
                                           href="{{'https://' . $application->client_id . \App\Properties\Base\BaseUrlProperty::WILDCARD_APEX_DOMAIN}}">{{'https://' . $application->client_id . \App\Properties\Base\BaseUrlProperty::WILDCARD_APEX_DOMAIN}}
                                        </a>
                                    </form>
                                </div>
                                <div id="settings" role="tabpanel" class="tab-pane fade in">
                                    <form class="form-horizontal mt10" role="form" method="post"
                                          enctype="multipart/form-data" action="">
                                        <!-- CSRF Token -->
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label"><strong>Client Id</strong></label>
                                            <div class="col-md-5"><input type="text" class="col-md-12"
                                                                         disabled="disabled"
                                                                         value="{!! $client->client_id !!}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-2 control-label"><strong>Client Secret</strong></label>
                                            <div class="col-md-5"><input type="text" class="col-md-12" disabled="true"
                                                                         value="{!! $client->client_secret !!}">
                                            </div>
                                        </div>
                                        @include('components.inputs.name-input')
                                        @include('components.inputs.description-input')
                                        @include('components.inputs.homepage-input')
                                        <div class="form-group status">
                                            <label class="col-md-2 control-label"><strong>Status</strong></label>
                                            <div class="col-md-5 {!! $application->status !!}">{!! $application->status !!}</div>
                                        </div>
                                        <div class="form-group status">
                                            <label class="col-md-2 control-label"><strong>Request Count</strong></label>
                                            <div class="col-md-5">{!! $requestCount !!} Monthly</div>
                                        </div>
                                        <div class="form-group status">
                                            <label class="col-md-2 control-label">
                                                <i class="fa fa-info-circle fa-lg" data-toggle="tooltip"
                                                   data-placement="top"
                                                   title="This access token gives you access to retrieve all the authorized users' data for this application"></i>
                                                <strong>App Token</strong>
                                            </label>
                                            <div class="col-md-5">
                                                <button id="get-token" data-loading-text="Loading..."
                                                        data-app-id="{{ $application->id }}" class="btn btn-sm btn-info">
                                                    Generate
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-group status">
                                            <label class="col-md-2 control-label">
                                                <strong>
                                                    <a target="_blank"
                                                       href="{{ getHostAppSettings()->additionalSettings->downloadLinks->webApp . '/account/api-explorer'.$client->client_id.'&client_secret='.$client->client_secret }}">
                                                        Api Explorer
                                                    </a>
                                                </strong>
                                            </label>
                                        </div>
                                        @if(Auth::user()->inRole('admin'))
                                            <div class="form-group status">
                                                <label class="col-md-2 control-label"> <strong>Enabled</strong> </label>
                                                <div class="col-md-5"><input type="checkbox" name="enabled"
                                                                             value="1" {{ $application->enabled ? 'checked="checked"' : '' }}>
                                                </div>
                                            </div>
                                            <div class="form-group status">
                                                <label class="col-md-2 control-label"> <strong>Billing Enabled</strong>
                                                </label>
                                                <div class="col-md-5"><input type="checkbox" name="billing_enabled"
                                                                             value="1" {{ $application->billing_enabled ? 'checked="checked"' : '' }}>
                                                </div>
                                            </div>
                                        @endif
                                        <br>
                                        <div class="form-group">
                                            <div class="col-md-offset-2 col-md-4">
                                                @if($application->status == 'Published')
                                                    <button type="submit" name="publish" class="btn btn-danger"> Do Not
                                                        Build Apps
                                                    </button>
                                                @else
                                                    <button type="submit" name="publish" class="btn btn-success"> Build
                                                        for Chrome, Android, and iOS
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <br>
                                        @include('components.inputs.redirect-uri-input')
                                        <div class="form-group">
                                            <div class="col-md-offset-2 col-md-4">
                                                {{--<a class="btn btn-danger" href="{{ route('apps') }}"> @lang('button.cancel') </a>--}}
                                                <button type="submit" class="btn btn-success"> @lang('button.save')
                                                    Setting Changes
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                {{--                                <div id="images" role="tabpanel" class="tab-pane fade in">
                                                                    <form class="form-horizontal mt10" role="form" method="post" enctype="multipart/form-data" action="">
                                                                        <!-- CSRF Token -->
                                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                                                        @include('components.icon-uploader')
                                                                        @include('components.text-logo-uploader')
                                                                        @include('components.splash-screen-uploader')
                                                                    </form>
                                                                </div>--}}
                                <div id="people" role="tabpanel" class="tab-pane fade">
                                    @if( $collaborators && $collaborators->count() )
                                        <table class="table table-striped collaborators mt10">
                                            @foreach( $collaborators as $collaborator )
                                                <tr>
                                                    <td class="avatar">
                                                        <img src="{!! $collaborator->user->avatar_image !!}"
                                                             alt="collaborator user avatar image"
                                                             style="display: block; max-height: 35px; max-width: 35px; width: auto; height: auto;"
                                                             class="img-circle img-responsive"
                                                             height="35" width="35"
                                                        />
                                                    </td>
                                                    <td>{{ $collaborator->user->email }}</td>
                                                    <td>{{ ucfirst($collaborator->type) }}</td>
                                                    <td class="delete-collaborator">
                                                        @if($collaborator->type != 'owner')
                                                            <i data-id="{{ $collaborator->id }}"
                                                               class="fa fa-lg fa-times"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    @endif
                                    <form class="form-horizontal" id="collaborator-form" role="form" method="post"
                                          action="{{ route('collaborator/app', $application->id) }}">
                                        <!-- CSRF Token -->
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                        <div class="form-group">
                                            <label for="email" class="col-md-3 control-label">Email</label>
                                            <div class="col-md-5"><input type="text" id="email" name="email"
                                                                         class="form-control"
                                                                         placeholder="user@example.com"></div>
                                        </div>
                                        <div class="form-group message hide">
                                            <div class="col-md-offset-3 col-md-5"></div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-offset-3 col-md-5">
                                                <button type="submit" class="btn btn-success">Add Collaborator</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                {{--<div id="integration" role="tabpanel" class="tab-pane fade"> @include('docs.integration-guide') </div>--}}
                                @if( $userCount > 0)
                                    <div id="users" role="tabpanel" class="tab-pane fade">
                                        <table class="table table-striped users mt10">
                                            <tr>
                                                <th id="user-info" colspan="3">
                                                    Total {{ $userCount }} user(s)
                                                    <div class="btn-group export-measurements pull-right"
                                                         data-app-id="{{ $application->id }}"
                                                         data-client-id="{{ $application->client_id }}">
                                                        <button type="button" class="btn btn-info dropdown-toggle"
                                                                data-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                            Export all measurements <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li data-output="csv">
                                                                <a href="#">CSV</a>
                                                            </li>
                                                            <li data-output="pdf">
                                                                <a href="#">PDF</a>
                                                            </li>
                                                            <li data-output="xls">
                                                                <a href="#">XLS</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </th>
                                            </tr>
                                            @include('components.lists.user-list')
                                        </table>
                                    </div>
                                @endif
                                <div id="billing" role="tabpanel" class="tab-pane fade">
                                    @include(
                                    'admin/payment/plans',
                                    ['plans' => $plans, 'currentPlan' => $currentPlan, 'appId' => $application->id])
                                </div>
                                <div id="config-file" role="tabpanel" class="tab-pane fade">
                                    <form class="form-horizontal mt10" role="form" method="post"
                                          enctype="multipart/form-data" action="">
                                        <!-- CSRF Token -->
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                                        <div class="col-md-8" style="padding-left: 0; padding-right: 0;">
                                            <div class="form-group">
                                                <div class="col-md-12" style=" width: 95%;">
                                                    App Configuration File
                                                    <br>
                                                    <small>(2000 character limit) This is the json file that will be
                                                        included with the application and can be used for advanced
                                                        customization. </small>
                                                    <textarea id=app_design" name="app_design" style="height:600px;"
                                                              class="textarea edi-css"
                                                              placeholder="App Design">
                                                        {!! Request::old('app_design', $application->app_design) !!}
                                                    </textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-offset-2 col-md-4">
                                                <button type="submit" class="btn btn-success">Update</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- row-->
    </section>
    <div class="modal fade" id="confirm-delete" tabindex="-2" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Delete Collaborator</h4>
                </div>
                <div class="modal-body">Are you sure you want to delete this collaborator?</div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" id="delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('footer_scripts')
    <script src="{{ qm_asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ qm_asset('js/quantimodo/form_editors.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('js/quantimodo/applications_dashboard.js') }}" type="text/javascript"></script>
    <script src="https://checkout.stripe.com/checkout.js"></script>
    <script src="{{ qm_asset('vendors/twitter-bootstrap-wizard/jquery.bootstrap.wizard.js') }}"></script>
    <script src="{{ qm_asset('vendors/twitter-bootstrap-wizard/prettify.js') }}"></script>
    <script src="{{ qm_asset('vendors/validation/jquery.validate.min.js') }}"></script>
    <script src="{{ qm_asset('vendors/select2/select2.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        var handler = StripeCheckout.configure({
            key: '{!! \App\Utils\Env::get('STRIPE_API_PUBLIC') !!}',
            image: '/img/checkout_logo.jpg',
            locale: 'auto',
            email: '{!! Auth::user()->email !!}',
            token: function (token) {
                if (typeof token.id != 'undefined') {
                    $('#card_token').val(token.id);
                    $('#subscribeForm').submit();
                }
            }
        });
    </script>
    <script src="{{ qm_asset('js/quantimodo/plans.js') }}"></script>
@stop
