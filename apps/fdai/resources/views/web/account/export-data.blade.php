@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Export Your Data
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
    @stop


    {{-- Page content --}}
    @section('content')
            <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="panel-heading3"><i class="fa fa-table"></i> Export Your Data</h4>
                <div id="status" class="alert hide centered">
                </div>
                <div class="alert alert-info centered">
                    You can schedule a CSV export containing all your measurements to be emailed within 24 hours.
                </div>
                <div class="col-md-12 centered">
                    Email will be sent to <strong>{{ Auth::user()->user_email }}</strong>
                    <br/>
                    <br/>
                    <div class="btn-group export-data">
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Send <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li data-output="csv"><a href="#">CSV</a></li>
                            <li data-output="pdf"><a href="#">PDF</a></li>
                            <li data-output="xls"><a href="#">XLS</a></li>
                        </ul>
                    </div>
                </div>
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
    <script src="{{ qm_asset('js/quantimodo/export.data.js') }}" type="text/javascript"></script>
@stop