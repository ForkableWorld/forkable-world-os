@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{app_display_name()}} Plus
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link href="{{ qm_asset('vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ qm_asset('vendors/validation/dist/css/bootstrapValidator.min.css') }}" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/blue.css" rel="stylesheet"/>
    <link href="{{ qm_asset('vendors/select2/select2.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('vendors/select2/select2-bootstrap.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('css/custom_css/addnew_user.css') }}" rel="stylesheet">
    <link href="{{ qm_asset('css/quantimodo/add-card.css') }}" rel="stylesheet" type="text/css" />
    @stop


    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">

                @include('web.account.premium-features-fragment')
                <h4 class="panel-heading3 qm-heading"><i class="fa fa-credit-card"></i> Update Card</h4>
                @include('errors.errors')
                <form class="form-horizontal" role="form" method="post" action="">
                    <!-- CSRF Token -->

                    <div class="form-group">
                        <label class="control-label col-sm-2">Card Number</label>
                        <div class="col-sm-5">
                            {!! Form::text('card_number', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Card Number',
                                'maxlength' => '16'
                            ]) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2">CVC</label>
                        <div class="col-sm-5">
                            {!! Form::text('card_cvc', null, [
                                'class' => 'form-control',
                                'placeholder' => 'CVC',
                                'maxlength' => '4'
                            ]) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2">Exp Month</label>
                        <div class="col-sm-5">
                            {!! Form::selectMonth('card_month', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">Exp Year</label>
                        <div class="col-sm-5">
                            {!! Form::selectRange('card_year', date("Y"), date("Y") + 10, null, ['class' => 'form-control']) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2">Coupon Code (optional)</label>

                        <div class="col-sm-5">
                            {!! Form::text('coupon', null, [
                                'class' => 'form-control',
                                'placeholder' => 'Coupon Code'
                            ]) !!}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-4">
                            <a class="btn btn-danger" href="{{ route('account') }}">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                Update
                            </button>
                        </div>
                    </div>
                </form>
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