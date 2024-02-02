@extends('layouts/default')

{{-- Web site Title --}}
@section('title')
Data Sharing
@parent
@stop
{{-- Content --}}
@section('content')

<!-- Main content -->
<section class="content">
    <div class="row">

        <?php use App\AppSettings\BaseApplication;$invitationBody = rawurlencode("I'm using " . app_display_name() . " to track symptoms and treatments, and import data from devices like Fitbit. Could you create an account at https://" .  hostOriginWithProtocol() . "/physician and share the generated authorization URL with me?  Then you'll be able to see my data as well as analytics showing the strongest predictors of my symptoms.  Thanks!")?>
        <?php $invitationSubject = rawurlencode('View my data at ' . app_display_name())?>
        <div class="panel-body">
            <a href="mailto:?subject={{ $invitationSubject }}&body={{ $invitationBody }}" class="btn btn-primary btn-block" role="button">
            {{--<a href="mailto:?subject={{$invitationSubject}}&body={{$invitationBody}}" class="btn btn-primary btn-block" role="button" target="_blank">--}}
                <strong><i class="fa fa-envelope-o"></i> &nbsp Share your data with a physician, caregiver, or loved one</strong>
            </a>
<br>
        <div class="col-lg-12">
            <div class="panel panel-primary ">
                <div class="panel-heading clearfix">
                    <h4 class="panel-title pull-left"> <i class="fa fa-lock"></i>
                        &nbsp Authorized Applications, Studies, and {{physicianAlias()}}s
                    </h4>
                </div>
                <br />

                @if ( count($clients) >= 1)

                        <table class="table table-striped">
                            <tbody>
                            <?php  /** @var BaseApplication[] $clients */ ?>
                                @foreach ( $clients as $client )
                                    @if(!empty($client->application))
                                        <tr class="">
                                            <td class="app-logo">
                                                @if(!empty($client->applicationOwner))
                                                    <img src="{{ $client->applicationOwner->avatar }}" style="width:75px;height:75px; border-radius: 50%;"/>
                                                @elseif(!empty($client->iconUrl))
                                                    {!! cl_image_tag($client->iconUrl, array( "width" => 75)) !!}
                                                @else
                                                    <img src="{{ qm_asset('/img/default_app.jpg') }}"/>
                                                @endif
                                            </td>
                                            <td>
                                                <a target="_blank" href="{{ $client->homepageUrl }}">
                                                    <strong>{{ $client->displayName }}</strong>
                                                </a>
                                                <small><i>{{ $client->appDescription }}</i></small>
                                                <div>{{ $client->longDescription }}</div>
                                                <div>Can {{ $client->scopeDescription }}</div>
                                            </td>
                                            <td>
                                                <button data-client-id="{{ $client->clientId }}"
                                                        class="btn btn-qm-orange revoke-access">
                                                    Revoke Access
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <h5 class="text-center">You haven't authorized any applications yet.</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>    <!-- row-->
</section>

<div class="modal fade" id="confirm-delete" tabindex="-2" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Revoke Access</h4>
            </div>
            <div class="modal-body">
                Are you sure you want to revoke access?
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal">Cancel</button>
                <button class="btn btn-qm-orange" id="delete">Yes</button>
            </div>
        </div>
    </div>
</div>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="{{ qm_asset('js/quantimodo/revoke.access.js') }}" type="text/javascript"></script>
@stop
