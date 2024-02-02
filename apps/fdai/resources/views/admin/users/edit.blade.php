@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Edit User
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link href="{{ qm_asset('assets/vendors/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ qm_asset('assets/vendors/validation/dist/css/bootstrapValidator.min.css') }}" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/minimal/blue.css" rel="stylesheet"/>
    <link href="{{ qm_asset('assets/vendors/select2/select2.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('assets/vendors/select2/select2-bootstrap.css') }}" rel="stylesheet"/>
    <link href="{{ qm_asset('assets/css/custom_css/addnew_user.css') }}" rel="stylesheet">
@stop


{{-- Page content --}}
@section('content')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="fa fa-fw fa-edit"></i>
                            Edit user
                        </h3>
                    </div>
                    <div class="panel-body">
                        <!--main content-->
                        <div class="position-center">
                            @include('errors.errors')
                            <div>
                                <h3 class="text-primary">Personal Information</h3>
                            </div>
                            <form role="form" id="tryitForm" class="form-horizontal" enctype="multipart/form-data" method="POST">
                                {!! Form::token() !!}
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Avatar</label>
                                    <div class="col-lg-6">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 200px;">
                                                @if($user->pic)
                                                    {!! cl_image_tag($user->pic, array( "width" => 200, "height" => 200)) !!}
                                                @else
                                                    <img src="https://via.placeholder.com/200x200" alt="..." />
                                                @endif
                                            </div>
                                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 200px;"></div>
                                            <div>
                                                            <span class="btn btn-primary btn-file">
                                                                <span class="fileinput-new">Select image</span>
                                                                <span class="fileinput-exists">Change</span>
                                                                <input type="file" name="pic" id="pic" />
                                                            </span>
                                                <a href="#" class="btn btn-primary fileinput-exists" data-dismiss="fileinput">Remove</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="cd-block">
                                    <div class="cd-content">
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label" for="first_name">First Name</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-user-md text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder="first name" name="first_name" id="first_name" class="form-control" value="{!! Request::old('first_name',$user->first_name) !!}" required="" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-2 control-label" for="last_name">Last Name</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-user-md text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder="last name" name="last_name" id="last_name" class="form-control" value="{!! Request::old('last_name',$user->last_name) !!}" required="" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-2"></div>
                                            <div class="col-lg-6">
                                                <h5 class="text-danger"><strong>If you don't want to change password, leave both fields empty</strong></h5>
                                            </div>
                                        </div>

                                        <div class="form-group">

                                            <label class="col-lg-2 control-label" for="password">Password</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-key text-primary"></i>
                                                                </span>
                                                    <input type="password" name="password" placeholder="" id="password" class="form-control" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-2 control-label" for="password_confirm">Confirm Password</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-key text-primary"></i>
                                                                </span>
                                                    <input type="password" name="password_confirm" placeholder=" " id="password_confirm" class="form-control" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-2 control-label" for="email">Email</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-envelope text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder="" id="email" name="email" class="form-control" value="{!! Request::old('email',$user->email) !!}"></div>
                                            </div>
                                        </div>
                                        @if(Auth::user()->inRole('admin'))
                                            <div class="form-group">
                                                <label for="group" class="col-md-2 control-label">Group</label>
                                                <div class="col-md-6">
                                                    <select class="form-control " title="Select group..." name="groups[]" id="groups" required="">
                                                        <option value="">Select</option>
                                                        @foreach($roles as $role)
                                                            <option value="{!! $role->id !!}"{{ (array_key_exists($role->id, $userRoles) ? ' selected="selected"' : '') }}>{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="activate" class="col-md-2 control-label"> Activate User</label>
                                                <div class="col-md-6">
                                                    <input id="activate" name="activate" type="checkbox" class="pos-rel p-l-30" value="1" @if(Activation::completed($user)) checked="checked" @endif {{ ($user->id === Sentinel::getUser()->id ? ' disabled="disabled"' : '') }} />
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="cd-block">
                                    <div class="cd-content">
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <input type="submit" class="btn btn-primary" name="save" value="Update" />
                                                <a href="{{ URL::previous() }}" class="btn btn-default" role="button">Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!--main content end--> </div>
                </div>
            </div>
        </div>
        <!--row end-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.js" type="text/javascript"></script>
    <script src="{{ qm_asset('assets/vendors/jasny-bootstrap/js/jasny-bootstrap.min.js') }}" type="text/javascript"></script>
    <script src="{{ qm_asset('assets/vendors/validation/dist/js/bootstrapValidator.min.js') }}" type="text/javascript" ></script>
    <script src="{{ qm_asset('assets/vendors/select2/select2.js') }}" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
    <script src="{{ qm_asset('assets/js/custom_js/edit_user.js') }}" type="text/javascript"></script>
@stop