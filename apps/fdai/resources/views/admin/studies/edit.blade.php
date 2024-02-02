@extends('layouts/default')

{{-- Web site Title --}}
@section('title')
@lang('studies/title.edit')
@parent
@stop
@section('header_styles')
    <link href="{{ qm_asset('vendors/twitter-bootstrap-wizard/prettify.css') }}" rel="stylesheet">
    <link href="{{ qm_asset('vendors/select2/select2.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('vendors/select2/select2-bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ qm_asset('css/quantimodo/plans.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ qm_asset('vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ qm_asset('css/quantimodo/app.css') }}" rel="stylesheet" type="text/css" />
@stop
{{-- Content --}}
@section('content')

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-qm-blue ">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        Study: {!! $application->app_display_name !!}
                    </h4>
                </div>
                <div class="panel-body">
                    <div>
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#data" aria-controls="data" role="tab" data-toggle="tab">Study</a></li>
                            <li role="presentation"><a href="#people" aria-controls="people" role="tab" data-toggle="tab">Collaborators</a></li>
                            @if($userCount > 0)
                                <li role="presentation"><a href="#users" aria-controls="users" role="tab" data-toggle="tab">Users</a></li>
                            @endif
                        </ul>
                        <?php $participantUrl = getHostAppSettings()->additionalSettings->downloadLinks->webApp . '/api/v2/study/'.$client->client_id; ?>
                        <?php $invitationBody = 'Hi!%20%0A%0AI%27m%20running%20a%20study%20at%20' . app_display_name() . '%20(https%3A%2F%2Fquantimo.do).%20%20%0A%0AI%20was%20wondering%20if%20you%27d%20like%20to%20participate%3F%20%20If%20so%2C%20please%20click%20this%20link%20to%20learn%20more%20or%20donate%20your%20data%3A%0A' . urlencode($participantUrl) . '%0A%0AHave%20a%20great%20day!'?>
                        <?php $invitationSubject = urlencode('Would you like to participate in my study?')?>

                    <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="data">
                                <form class="form-horizontal mt10" role="form" method="post" enctype="multipart/form-data" action="">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="form-group status">
                                        <label class="col-md-2 control-label">
                                            <strong>Status</strong>
                                        </label>
                                        <div class="col-md-5 {!! $application->status !!}">
                                            {!! $application->status !!}
                                        </div>
                                    </div>
                                    <div class="form-group status">
                                        <label class="col-md-2 control-label">
                                            <i class="fa fa-info-circle fa-lg"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Share this url with potential study participants so that they can give you access to their data."></i>
                                            <strong>Participant Authorization URL</strong>
                                        </label>
                                        <div class="col-md-5">
                                            <a target="_blank" href="{{ $participantUrl }}">
                                                {{ $participantUrl }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-group status">
                                        <label class="col-md-2 control-label">
                                            <strong>Invite someone to participate in your study</strong>
                                        </label>
                                        <div class="col-md-5">
                                            <a href="mailto:?subject={{$invitationSubject}}&body={{$invitationBody}}" class="btn btn-primary btn-block" role="button" target="_blank">
                                                <strong><i class="fa fa-envelope-o"></i> Send Invitation</strong>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-group status">
                                        <label class="col-md-2 control-label">
                                            <strong>Share</strong>
                                        </label>
                                        <div class="col-md-5">
                                            <div class="fb-share-button pull-left" data-href="{{ $participantUrl }}"
                                                 data-layout="button">
                                            </div>
                                            <a href="https://twitter.com/share" class="twitter-share-button pull-left"
                                               data-url="{{ $participantUrl }}" data-text="{{ $application->app_display_name }}"
                                               data-via="quantimodo">
                                                Tweet
                                            </a>
                                        </div>
                                    </div>
                                    @if($userCount > 0)
                                        <div class="form-group status">
                                            <div class="col-md-5 col-md-offset-2">
                                                <div class="btn-group export-measurements"
                                                     data-app-id="{{ $application->id }}"
                                                     data-client-id="{{ $application->client_id }}">
                                                    <button type="button"
                                                            class="btn btn-qm-green dropdown-toggle"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false">
                                                        Export all participant data <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li data-output="csv"><a href="#">CSV</a></li>
                                                        <li data-output="pdf"><a href="#">PDF</a></li>
                                                        <li data-output="xls"><a href="#">XLS</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(Auth::user()->inRole('admin'))
                                        <div class="form-group status">
                                            <label class="col-md-2 control-label">
                                                <strong>Enabled</strong>
                                            </label>
                                            <div class="col-md-5">
                                                <input type="checkbox" name="enabled" value="1" {{ $application->enabled ? 'checked="checked"' : '' }}>
                                            </div>
                                        </div>
                                        <div class="form-group status">
                                            <label class="col-md-2 control-label">
                                                <strong>Billing Enabled</strong>
                                            </label>
                                            <div class="col-md-5">
                                                <input type="checkbox" name="billing_enabled" value="1" {{ $application->billing_enabled ? 'checked="checked"' : '' }}>
                                            </div>
                                        </div>
                                    @endif
                                    <br>
                                    <br>
                                    <div class="form-group status">
                                        <label for="app_display_name" class="col-md-2 control-label">
                                            @if(Auth::user()->ID != $application->id)
                                                <i class="fa fa-info-circle fa-lg"
                                                   data-toggle="tooltip"
                                                   data-placement="top"
                                                   title="Only the owner of the study can change its name"></i>
                                            @endif
                                            Study Name*
                                        </label>
                                        <div class="col-md-5">
                                            @if(Auth::user()->ID == $application->user_id)
                                                <input type="text" maxlength="100" id="app_display_name" name="app_display_name" class="form-control" value="{!! Request::old('app_display_name', $application->app_display_name) !!}">
                                            @else
                                                <div>{{ $application->app_display_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    {{--@include('components.icon-uploader')--}}
                                    <div class="form-group">
                                        <label for="app_description" class="col-md-2 control-label">
                                            Study Question (140 character limit)
                                        </label>
                                        <div class="col-md-7">
                                            <small>Short explanation of the goal of the study that will be shown on the participant authorization page. </small>
                                            <input type="text" maxlength="140" id="app_description" name="app_description" class="form-control" placeholder="Description" value="{!! Request::old('app_description', $application->app_description) !!}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="long-desc" class="col-md-2 control-label">
                                            Participant Instructions (2000 character limit)
                                        </label>
                                        <div class="col-md-10">
                                            <small>These instructions will be shown on the participant authorization page. Please describe in detail what data the study participants should be collecting and how they should collect it. Also, explain the purpose behind the study and provide any other relevant information.</small>
                                            <textarea id="long-desc" maxlength="2000" name="long_description" class="textarea edi-css" placeholder="Long Description">{!! Request::old('long_description', $application->long_description) !!}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group mt10">
                                        <label for="outcome" class="col-md-2 control-label">
                                            Outcome variable*
                                        </label>
                                        <div class="col-md-7">
                                            <input type="text" id="outcome" required="required" class="form-control variable-search" placeholder="Search for an outcome like Overall Mood, Inflammatory Pain, or Sleep Quality..." value="{{ $application->outcome->name or '' }}">
                                            <img class="spinner" src="https://static.quantimo.do/img/loading.gif"/>
                                        </div>
                                        <input type="hidden" id="outcome-id" required="required" name="outcome_variable_id" value="{{ $application->outcome_variable_id }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="predictor" class="col-md-2 control-label">
                                            Predictor variable*
                                        </label>
                                        <div class="col-md-7">
                                            <input type="text" id="predictor" required="required" class="form-control variable-search" placeholder="Search for a predictor like Steps, Omega 3 Fatty Acids, or Sleep Quality..." value="{{ $application->predictor->name or '' }}">
                                            <img class="spinner" src="{{ qm_asset("https://static.quantimo.do/img/loading.gif") }}"/>
                                        </div>
                                        <input type="hidden" id="predictor-id" required="required" name="predictor_variable_id" value="{{ $application->predictor_variable_id }}">
                                    </div>
{{--                                    <div class="form-group">
                                        <label for="title" class="col-md-2 control-label">
                                            Homepage Url
                                        </label>
                                        <div class="col-md-7">
                                            <input type="text" id="homepage_url" name="homepage_url" placeholder="http://" class="form-control" value="{!! Request::old('homepage_url', $application->homepage_url) !!}">
                                        </div>
                                    </div>--}}
                                    <div class="form-group">
                                        <label for="title" class="col-md-2 control-label">
                                            Learn More Url
                                        </label>
                                        <div class="col-md-7">
                                            <input type="text" id="homepage_url" name="homepage_url" placeholder="" class="form-control" value="{!! Request::old('homepage_url', $application->homepage_url) !!}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-offset-2 col-md-4">
                                            <a class="btn btn-qm-orange" href="{{ route('studies') }}">
                                                @lang('button.cancel')
                                            </a>
                                            @if($application->status == 'Published')
                                                <button type="submit" name="publish" class="btn btn-qm-orange">
                                                    Unpublish
                                                </button>
                                            @else
                                                <button type="submit" name="publish" class="btn btn-qm-green">
                                                    Publish
                                                </button>
                                            @endif
                                            <button type="submit" class="btn btn-qm-green">
                                                @lang('button.save')
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="people">
                                @if( $collaborators && $collaborators->count() )
                                    <table class="table table-striped collaborators mt10">
                                        @foreach( $collaborators as $collaborator )
                                        <tr>
                                            <td class="avatar">
                                                <img src="{!! $collaborator->user->avatar_image !!}"
                                                     alt="avatar image"
                                                     style="display: block; max-height: 35px; max-width: 35px; width: auto; height: auto;"
                                                     class="img-circle img-responsive"/>
                                            </td>
                                            <td>{{ $collaborator->user->email }}</td>
                                            <td>{{ ucfirst($collaborator->type) }}</td>
                                            <td class="delete-collaborator">
                                                @if($collaborator->type != 'owner')
                                                    <i data-id="{{ $collaborator->id }}" class="fa fa-lg fa-times"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                @endif
                                <form class="form-horizontal" id="collaborator-form" role="form" method="post" action="{{ route('collaborator/study', $application->id) }}">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="form-group">
                                        <label for="email" class="col-md-3 control-label">
                                            Email
                                        </label>
                                        <div class="col-md-5">
                                            <input type="text" id="email" name="email" class="form-control" placeholder="user@example.com">
                                        </div>
                                    </div>
                                    <div class="form-group message hide">
                                        <div class="col-md-offset-3 col-md-5">

                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-offset-3 col-md-5">
                                            <button type="submit" class="btn btn-qm-green">
                                                Add Collaborator
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @if($userCount > 0)
                                <div role="tabpanel" class="tab-pane fade" id="users">
                                    <table class="table table-striped users mt10">
                                        <tr>
                                            <th id="user-info" colspan="3">
                                                Total {{ $userCount }} user(s)
                                                <div class="btn-group export-measurements pull-right"
                                                     data-app-id="{{ $application->id }}"
                                                     data-client-id="{{ $application->client_id }}">
                                                    <button type="button"
                                                            class="btn btn-qm-green dropdown-toggle"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false">
                                                        Export all participant data <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li data-output="csv"><a href="#">CSV</a></li>
                                                        <li data-output="pdf"><a href="#">PDF</a></li>
                                                        <li data-output="xls"><a href="#">XLS</a></li>
                                                    </ul>
                                                </div>
                                            </th>
                                        </tr>
                                        {{--@foreach($users as $key => $user)--}}
                                            {{--<tr>--}}
                                                {{--<td> {{ $key + 1 }}</td>--}}
                                        {{--<td class="avatar"><img src="{!! $user->avatar_image !!}" alt="img" class="img-circle img-responsive" height="35" width="35"/></td>--}}
                                                {{--<td> {{ $user->email }} </td>--}}
                                            {{--</tr>--}}
                                        {{--@endforeach--}}
                                    </table>
                                </div>
                            @endif
                            <div role="tabpanel" class="tab-pane fade" id="billing">
                                @include(
                                'admin/payment/plans',
                                ['plans' => $plans, 'currentPlan' => $currentPlan, 'appId' => $application->id])
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Delete Colaborator</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this collaborator?
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal">Cancel</button>
                <button class="btn btn-qm-orange" id="delete">Delete</button>
            </div>
        </div>
    </div>
</div>
<div id="fb-root"></div>
@stop

@section('footer_scripts')
    <script src="{{ qm_asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}" type="text/javascript"></script>
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
            token: function(token) {
                if (typeof token.id != 'undefined') {
                    $("#card_token").val(token.id);
                    $("#subscribeForm").submit();
                }
            }
        });
    </script>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=225078261031461";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src="{{ qm_asset('js/quantimodo/plans.js') }}"></script>
@stop
