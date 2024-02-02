@extends('layouts/default')
{{-- Web site Title --}}
@section('title')
{{physicianAlias()}}
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
                <h4 class="panel-heading3"><i class="fa fa-user-md"></i> {{physicianAlias()}}s</h4>
                <div class="panel-body">
                    <div>
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#data" aria-controls="data" role="tab" data-toggle="tab">{{physicianAlias()}}</a></li>
                            {{--<li role="presentation"><a href="#people" aria-controls="people" role="tab" data-toggle="tab">Collaborators</a></li>--}}
                            @if($userCount > 0)
                                <li role="presentation"><a href="#users" aria-controls="users" role="tab" data-toggle="tab">{{patientAlias()}}s</a></li>
                            @endif
                        </ul>
                        <?php $participantUrl = getHostAppSettings()->additionalSettings->downloadLinks->webApp .
                            '/oauth/authorize?response_type=token&scope=writemeasurements&client_id='.$client->client_id; ?>
                        <?php $invitationBody = 'Hi!%20%0A%0ADid%20you%20know%20that%20you%20can%20use%20' . app_display_name() .
                            '%20(https%3A%2F%2Fquantimo.do)%20to%20easily%20track%20symptoms%20and%20treatments%2C%20import%20data%20from%20devices%20like%20Fitbit%2C%20and%20then%20see%20an%20analysis%20of%20your%20data%20showing%20the%20strongest%20predictors%20of%20your%20symptoms%3F%20%0A%0AYou%20can%20also%20opt-in%20share%20your%20data%20with%20me%20at%3A%0A' . urlencode($participantUrl) . '%0A%0AHave%20a%20great%20day!'?>
                        <?php $invitationSubject = rawurlencode('Invitation to Share Your Data')?>
                    <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="data">
                                <form class="form-horizontal mt10" role="form" method="post" enctype="multipart/form-data" action="">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="form-group status">
                                        <label class="col-md-2 control-label">
                                            <i class="fa fa-info-circle fa-lg" data-toggle="tooltip" data-placement="top"
                                               title="Share this url with your {{strtolower(patientAlias())}}s so that they can give you access to their data."></i>
                                            <strong>{{patientAlias()}} Authorization URL</strong>
                                        </label>
                                        <div class="col-md-5">
                                            <a target="_blank" href="{{ $participantUrl }}">{{ $participantUrl }}</a>
                                        </div>
                                    </div>
{{--                                    <div class="form-group status">
                                        <label class="col-md-2 control-label">
                                            <i class="fa fa-info-circle fa-lg"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Alternatively, give this provider code to your {{strtolower(patientAlias())}} to enter in the Settings page of their {{app_display_name()}}-supported mobile application."></i>
                                            <strong>Provider Code</strong>
                                        </label>
                                        <div class="col-md-5">
                                                {{ $client->client_id }}
                                        </div>
                                    </div>--}}
                                    <div class="form-group status">
                                        <label class="col-md-2 control-label"> <strong>Send Invitation</strong> </label>
                                        <div class="col-md-5">
                                            <a href="mailto:?subject={{$invitationSubject}}&body={{$invitationBody}}"
                                               class="btn btn-primary btn-block" role="button" target="_blank">
                                                <strong><i class="fa fa-envelope-o"></i>
                                                    Invite {{strtolower(patientAlias())}} to share data
                                                </strong>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-group status">
                                        <label class="col-md-2 control-label"> <strong>Share</strong> </label>
                                        <div class="col-md-5">
                                            <div class="fb-share-button pull-left"
                                                 data-href="{{ $participantUrl }}"
                                                 data-layout="button">
                                            </div>
                                            <a href="https://twitter.com/share" class="twitter-share-button pull-left"
                                               data-url="{{ $participantUrl }}"
                                               data-text="{{ $application->app_display_name }}"
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
                                                    <button type="button" class="btn btn-qm-green dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Export all {{strtolower(patientAlias())}}s data <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li data-output="csv"><a href="#">CSV</a></li>
                                                        <li data-output="pdf"><a href="#">PDF</a></li>
                                                        <li data-output="xls"><a href="#">XLS</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <h4 class="panel-heading3"> <i class="fa fa-users"></i> Your {{patientAlias()}}s </h4>
                                        <table class="table table-striped users mt10">
                                            Click name to switch to {{strtolower(patientAlias())}} account. Then you'll be able to
                                            see {{strtolower(patientAlias())}} analytics such as the strongest predictors of their symptoms,
                                            add treatment and symptom rating reminders reminders,
                                            add new symptom ratings, treatments, and other measurements for them,
                                            import their digital health data from other apps and devices,
                                            and review their past symptoms, treatments, vitals, and other measurements.
                                            Once you're done, click the "Back to my account" link at the top of the page.
                                            @include('components.lists.user-list')
                                        </table>
                                    @endif
                                </form>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="people">
                                @if( $collaborators && $collaborators->count() )
                                    <table class="table table-striped collaborators mt10">
                                        @foreach( $collaborators as $collaborator )
                                        <tr>
                                            <td class="avatar"><img src="{!! $collaborator->user->avatar !!}" alt="img" class="img-circle img-responsive" height="35" width="35"/></td>
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
                                <form class="form-horizontal" id="collaborator-form" role="form" method="post" action="{{ route('collaborator/physician', $application->id) }}">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <div class="form-group">
                                        <label for="email" class="col-md-3 control-label"> Email </label>
                                        <div class="col-md-5"> <input type="text" id="email" name="email" class="form-control" placeholder="user@example.com"> </div>
                                    </div>
                                    <div class="form-group message hide"> <div class="col-md-offset-3 col-md-5"> </div> </div>
                                    <div class="form-group"> <div class="col-md-offset-3 col-md-5"> <button type="submit" class="btn btn-qm-green"> Add Collaborator </button> </div> </div>
                                </form>
                            </div>
                            @if( $userCount > 0)
                                <div role="tabpanel" class="tab-pane fade" id="users">
                                    <table class="table table-striped users mt10">
                                        <tr>
                                            <th id="user-info" colspan="3">
                                                Click a {{strtolower(patientAlias())}}'s name to act as their user.  Then, using the menu on the left, you'll be able to:
                                                {!! getPhysicianFeatureBulletsHtml() !!}
                                                <div class="btn-group export-measurements pull-right"
                                                     data-app-id="{{ $application->id }}"
                                                     data-client-id="{{ $application->client_id }}">
                                                    <button type="button"
                                                            class="btn btn-qm-green dropdown-toggle"
                                                            data-toggle="dropdown"
                                                            aria-haspopup="true"
                                                            aria-expanded="false">
                                                        Export all {{strtolower(patientAlias())}}s data <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li data-output="csv"><a href="#">CSV</a></li>
                                                        <li data-output="pdf"><a href="#">PDF</a></li>
                                                        <li data-output="xls"><a href="#">XLS</a></li>
                                                    </ul>
                                                </div>
                                            </th>
                                        </tr>
                                        @foreach( $users as $applicationUser )
                                            <tr>
                                                <td class="avatar"><img src="{!! $applicationUser->avatar !!}" alt="img" class="img-circle img-responsive" height="35" width="35"/></td>
                                                <td><a target='_blank' href="https://patient.quantimo.do/#/app/history-all?accessToken={{$applicationUser->access_token}}">{{ $applicationUser->email }}</a></td>
                                                {{--<td><a href="{{ route('act-as/physician', ['clientId' => $application->id, 'userId' => $applicationUser->ID]) }}">{{ $applicationUser->email }}</a></td>--}}
                                            </tr>
                                        @endforeach
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
