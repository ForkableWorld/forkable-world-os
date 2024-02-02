@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ ucfirst($type) }} Plan
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link href="{{ qm_asset('vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ qm_asset('vendors/validation/dist/css/bootstrapValidator.min.css') }}" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/blue.css" rel="stylesheet"/>
    <link href="{{ qm_asset('vendors/select2/select2.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('vendors/select2/select2-bootstrap.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('vendors/Buttons/css/buttons.css') }}" rel="stylesheet"/>
    @stop


    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="panel-heading3">{{ ucfirst($type) }} Plan </h4>
                @if($type == 'upgrade')
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <div class="box">
                                <p><strong>Upgrade to a <span class="strike">$72</span> $54 yearly plan.</strong></p>
                                <div class="alert alert-info">
                                    <p>Your card will be charged immediately.</p>
                                </div>
                                <table width="100%" class="table">
                                    <tbody>
                                        <tr>
                                            <td>
                                                Yearly plan, 1 year
                                            </td>
                                            <td align="right">USD $54.00</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <strong>Total:</strong>
                                            </td>
                                            <td align="right">
                                                <strong>USD $54.00</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <form method="post">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <p>
                                        <button type="submit" class="btn btn-lg button-flat-royal">Upgrade</button> or <a href="{{ route('account') }}">Cancel</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <div class="box">
                                <p><strong>Downgrade to a <span class="strike">$9</span> $6.75 monthly plan</strong></p>
                                <div class="alert alert-info">
                                    <p>Your card will be charged immediately.</p>
                                </div>
                                <table width="100%" class="table">
                                    <tbody><tr>
                                        <td>
                                            Monthly plan
                                        </td>
                                        <td align="right">USD $6.75</td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <strong>Total:</strong>
                                        </td>
                                        <td align="right">
                                            <strong>USD $6.75</strong>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <form method="post">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <p>
                                        <button type="submit" class="btn btn-lg button-flat-caution">Downgrade</button> or <a href="{{ route('account') }}">Cancel</a>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <!--row end-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.js" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('vendors/validation/dist/js/bootstrapValidator.min.js') }}" type="text/javascript" ></script>
    <script src="{{ qm_asset('vendors/select2/select2.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
    <script src="{{ qm_asset('js/custom_js/edit_user.js') }}" type="text/javascript"></script>
@stop