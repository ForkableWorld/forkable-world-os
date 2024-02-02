@extends('layouts/default')

{{-- Page title --}}
@section('title')
    View User
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ qm_asset('assets/css/custom_css/profile.css') }}" />
@stop


{{-- Page content --}}
@section('content')
    <!-- Main content -->
    <section class="content paddingleft_right15">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="text-center mbl">
                                    @if($user->pic)
                                        {!! cl_image_tag($user->pic, array( "width" => 200, "height" => 200)) !!}
                                    @else
                                        <img src="{!! $user->avatar_image !!}" alt="img" class="img-circle img-bor"/>
                                    @endif
                                </div>
                            </div>
                            <div align="center">
                                <h2>{!! $user->first_name !!} {!! $user->last_name !!}</h2>
                                <p>{!! $user->email !!}</p>
                                <img src="{{ qm_asset('assets/img/flaticons/social-media-09.png') }}" alt="Picture" width="30" height="30">
                                <img src="{{ qm_asset('assets/img/flaticons/social-media-16.png') }}" alt="Picture" width="30" height="30">
                                <img src="{{ qm_asset('assets/img/flaticons/social-media-07.png') }}" alt="Picture" width="30" height="30">
                            </div>
                            &nbsp;&nbsp;
                            <div align="center">
                                <button type="button" class="btn btn-success btn-sm">Follow</button>
                                <button type="button" class="btn btn-primary btn-sm">Message</button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <table class="table">
                                <tbody>
                                <tr>
                                    <td class="text-primary">User Name</td>
                                    <td>{!! $user->first_name !!} {!! $user->last_name !!}</td>
                                </tr>
                                <tr>
                                    <td class="text-primary">Email</td>
                                    <td>{!! $user->email !!}</td>
                                </tr>
                                @if($user->phone)
                                    <tr>
                                        <td class="text-primary">Phone Number</td>
                                        <td>{!! $user->phone !!}</td>
                                    </tr>
                                @endif
                                @if($user->address)
                                    <tr>
                                        <td class="text-primary">Address</td>
                                        <td>{!! $user->address !!} </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="text-primary">Status</td>
                                    <td>
                                    @if($user->deleted_at)
                                            <span class="label label-danger">Deleted</span>
                                    @elseif($activation = Activation::completed($user))
                                            <span class="label label-success">Active</span>
                                    @else
                                            <span class="label label-warning">Not Active</span>
                                    @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-primary">Facebook</td>
                                    <td>Nataliapery@example.com</td>
                                </tr>
                                <tr>
                                    <td class="text-primary">Skype</td>
                                    <td>Nataliapery1234</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-12">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs nav-custom">
                    <li class="active">
                        <a href="#tab-activity" data-toggle="tab">
                            <strong>Activities</strong>
                        </a>
                    </li>
                    <li>
                        <a href="#followers" data-toggle="tab">
                            <strong>Followers</strong>
                        </a>
                    </li>
                    <li>
                        <a href="#tab-events" data-toggle="tab">
                            <strong>My Events</strong>
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content nopadding noborder">
                    <div id="tab-activity" class="tab-pane fade in active">
                        <div class="activity">
                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Abbey</strong>
                                    started following
                                    <strong>Adele</strong>
                                    .
                                    <br>
                                    <small class="text-muted">2 days ago  at 1:30pm</small>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                                    </p>
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar1.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Clemens</strong>
                                    posted a new blog.
                                    <br>
                                    <small class="text-muted">Today at 1:18pm</small>

                                    <div class="media blog-media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="{{ qm_asset('assets/img/authors/avatar2.jpg') }}" alt=""></a>
                                        <div class="media-body col-md-8 col-lg-8 col-sm-8 col-xs-12">
                                            <h4 class="media-title">
                                                <a href="#">Lorem ipsum dolor</a>
                                            </h4>
                                            <p >
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat...
                                                <a href="#">Read more</a>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- media-body -->
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object" src="{{ qm_asset('assets/img/authors/avatar3.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Lottie</strong>
                                    started following
                                    <strong>Winifred</strong>
                                    .
                                    <br>
                                    <small class="text-muted">6 days ago at 8:30am</small>
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar4.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Marlee</strong>
                                    uploaded
                                    <a href="#">3 photos</a>
                                    .
                                    <br>
                                    <small class="text-muted">3 days ago at 12:30pm</small>

                                    <ul class="uploadphoto-list">
                                        <li>
                                            <a href="{{ qm_asset('assets/img/authors/avatar1.jpg') }}" data-rel="prettyPhoto">
                                                <img src="{{ qm_asset('assets/img/authors/avatar2.jpg') }}" class="thumbnail img-responsive" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="{{ qm_asset('assets/img/authors/avatar4.jpg') }}" data-rel="prettyPhoto">
                                                <img src="{{ qm_asset('assets/img/authors/avatar3.jpg') }}" class="thumbnail img-responsive" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="{{ qm_asset('assets/img/authors/avatar5.jpg') }}" data-rel="prettyPhoto">
                                                <img src="{{ qm_asset('assets/img/authors/avatar.jpg') }}" class="thumbnail img-responsive" alt=""></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar7.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Joseph</strong>
                                    started following
                                    <strong>Keegan</strong>
                                    .
                                    <br>
                                    <small class="text-muted">6 days ago at 8:15am</small>
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar2.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Lenny</strong>
                                    posted a new note.
                                    <br>
                                    <small class="text-muted">4 days ago at 11:00 am</small>
                                    <h4 class="media-title">
                                        <a href="#">Consectetur Adipisicing Elit</a>
                                    </h4>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat...
                                        <a href="#">Read more</a>
                                    </p>
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar3.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Danielle</strong>
                                    posted a new Image.
                                    <br>
                                    <small class="text-muted">sep 2 at 1:08pm</small>

                                    <div class="media blog-media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="{{ qm_asset('assets/img/authors/avatar4.jpg') }}" alt=""></a>
                                        <div class="media-body col-md-8 col-lg-8 col-sm-8 col-xs-12">
                                            <h4 class="media-title">
                                                <a href="#">Ut Enim Ad Minim Veniam</a>
                                            </h4>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat...
                                                <a href="#">Read more</a>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Frida</strong>
                                    added new photo.
                                    <br>
                                    <small class="text-muted">December 2 at 12:30pm</small>
                                    <div class="mb20"></div>
                                    <a href="{{ qm_asset('assets/img/authors/avatar1.jpg') }}" data-rel="prettyPhoto" class="img-single">
                                        <img src="{{ qm_asset('assets/img/authors/avatar.jpg') }}" class="thumbnail img-responsive" alt=""></a>
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar2.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Jensen</strong>
                                    started following
                                    <strong>Gordon</strong>
                                    .
                                    <br>
                                    <small class="text-muted">Jan 15 at 3:30pm</small>
                                </div>
                            </div>
                            <!-- media -->

                            <div class="imgs-profile">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="{{ qm_asset('assets/img/authors/avatar3.jpg') }}" alt=""></a>
                                <div class="media-body">
                                    <strong>Krista</strong>
                                    posted a new blog.
                                    <br>
                                    <small class="text-muted">Feb 15 at 3:18pm</small>

                                    <div class="media blog-media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="{{ qm_asset('assets/img/authors/avatar4.jpg') }}" alt=""></a>
                                        <div class="media-body col-md-8 col-lg-8 col-sm-8 col-xs-12">
                                            <h4 class="media-title">
                                                <a href="#">Ut Enim Ad Minim Veniam</a>
                                            </h4>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat...
                                                <a href="#">Read more</a>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                            </div>
                            <!-- media -->
                        </div>
                    </div>
                    <!-- tab-pane -->

                    <div class="tab-pane" id="followers">
                        <div class="follower-list">
                            <div class="media">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="https://via.placeholder.com/100x100" alt="image" />
                                </a>
                                <div class="media-body">
                                    <h3 class="follower-name">Nullam Vitae</h3>
                                    <div>
                                        <i class="fa fa-map-marker"></i>
                                        Alberta, Edmonton, Canada
                                    </div>
                                    <div>
                                        <i class="fa fa-briefcase"></i>
                                        Switchboard operator at
                                        <a href="#">SomeCompany, Inc.</a>
                                    </div>

                                    <div class="mb20"></div>

                                    <div class="btn-toolbar">
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-envelope-o"></i>
                                                Send Message
                                            </button>
                                        </div>
                                        <!-- btn-group -->
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Following
                                            </button>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Followers
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="media">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="https://via.placeholder.com/100x100" alt="image" />
                                </a>
                                <div class="media-body">
                                    <h3 class="follower-name">Nibh Un Odiosters</h3>
                                    <div>
                                        <i class="fa fa-map-marker"></i>
                                        Cebu City, Philippines
                                    </div>
                                    <div>
                                        <i class="fa fa-briefcase"></i>
                                        Switchboard operator
                                        <a href="#">ITCompany, Inc.</a>
                                    </div>
                                    <div class="mb20"></div>
                                    <div class="btn-toolbar">
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-envelope-o"></i>
                                                Send Message
                                            </button>
                                        </div>
                                        <!-- btn-group -->
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Following
                                            </button>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Followers
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="media">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="https://via.placeholder.com/100x100" alt="image" />
                                </a>
                                <div class="media-body">
                                    <h3 class="follower-name">Vitae Nibh</h3>
                                    <div class="profile-location">
                                        <i class="fa fa-map-marker"></i>
                                        Madrid, Spain
                                    </div>
                                    <div class="profile-position">
                                        <i class="fa fa-briefcase"></i>
                                        CEO at
                                        <a href="#">SomeCompany, Inc.</a>
                                    </div>

                                    <div class="mb20"></div>
                                    <div class="btn-toolbar">
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-envelope-o"></i>
                                                Send Message
                                            </button>
                                        </div>
                                        <!-- btn-group -->
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Following
                                            </button>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Followers
                                            </button>
                                        </div>
                                        <div class="mb20"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="media">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="https://via.placeholder.com/100x100" alt="image" />
                                </a>
                                <div class="media-body">
                                    <h3 class="follower-name">Odiosters</h3>
                                    <div>
                                        <i class="fa fa-map-marker"></i>
                                        Bangkok, Thailand
                                    </div>
                                    <div>
                                        <i class="fa fa-briefcase"></i>
                                        Java Developer at
                                        <a href="#">ITCompany, Inc.</a>
                                    </div>

                                    <div class="mb20"></div>
                                    <div class="btn-toolbar">
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-envelope-o"></i>
                                                Send Message
                                            </button>
                                        </div>
                                        <!-- btn-group -->
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Following
                                            </button>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Followers
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="media">
                                <a class="pull-left" href="#">
                                    <img class="media-object img-circle" src="https://via.placeholder.com/100x100" alt="image" />
                                </a>
                                <div class="media-body">
                                    <h3 class="follower-name">Odiosters Nullam Vitae</h3>
                                    <div class="profile-location">
                                        <i class="fa fa-map-marker"></i>
                                        Tokyo, Japan
                                    </div>
                                    <div class="profile-position">
                                        <i class="fa fa-briefcase"></i>
                                        QA Engineer at
                                        <a href="#">SomeCompany, Inc.</a>
                                    </div>

                                    <div class="mb20"></div>
                                    <div class="btn-toolbar">
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-envelope-o"></i>
                                                Send Message
                                            </button>
                                        </div>
                                        <!-- btn-group -->
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Following
                                            </button>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-info btn-xs">
                                                <i class="fa fa-check"></i>
                                                Followers
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb20"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab-events">
                        <div class="events">
                            <h5 class="lg-title mb20">Upcoming Events</h5>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">Lorem Ipsum is simply dummy</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                Silicon Valley, San Francisco, CA
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Sunday,Dec 18, 2014 at 11:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- col-sm-6 -->
                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">Lorem ipsum dolor text</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                Los Angeles, CA
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Friday,Dec 20, 2014 at 8:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">Lorem Ipsum is not simply random text</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                Los Angeles, CA
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Friday,Dec 22, 2014 at 8:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">The standard chunk of Lorem Ipsum</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                Bay Area, San Francisco
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Saturday,Dec 24, 2014 at 8:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- col-sm-6 -->
                            </div>
                            <!-- row -->

                            <br />

                            <h5 class="lg-title mb20">Past Events</h5>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">There are many variations of passages of</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                Silicon Valley, San Francisco, CA
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Sunday,Dec 15, 2014 at 11:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- col-sm-6 -->

                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">There are many variations of passages</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                New York City
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Monday,Dec 14, 2014 at 8:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- col-sm-6 -->

                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">English. Many desktop publishing</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                Los Angeles, CA
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Friday,Dec 12, 2014 at 8:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- col-sm-6 -->

                                <div class="col-sm-6">
                                    <div class="media">
                                        <a class="pull-left" href="#">
                                            <img class="media-object thumbnail" src="https://via.placeholder.com/100x100" alt="image" />
                                        </a>
                                        <div class="media-body">
                                            <h4 class="event-title">
                                                <a href="#">Lorem Ipsum comes from sections</a>
                                            </h4>
                                            <small class="text-muted">
                                                <i class="fa fa-map-marker"></i>
                                                Bay Area, San Francisco
                                            </small>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar"></i>
                                                Saturday,Dec 10, 2014 at 8:00am
                                            </small>
                                            <p>
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor...
                                            </p>
                                        </div>
                                    </div>
                                    <!-- media -->
                                </div>
                                <!-- col-sm-6 -->
                            </div>
                            <!-- row -->
                        </div>
                        <!-- events -->
                    </div>
                    <!-- tab-pane -->

                </div>
                <!-- tab-content -->

            </div>
            <!-- col-sm-9 -->
        </div>
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')

@stop
