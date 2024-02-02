@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Create New User
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
                            <i class="fa fa-fw fa-users"></i>
                            Add New User
                        </h3>
                                    <span class="pull-right">
                                        <i class="fa fa-fw fa-times removepanel clickable"></i>
                                        <i class="fa fa-fw fa-chevron-up clickable"></i>
                                    </span>
                    </div>
                    <div class="panel-body">
                        <!--main content-->
                        <div class="position-center">
                            @include('errors.errors')
                            <div>
                                <h3 class="text-primary">Personal Information</h3>
                            </div>
                            <form role="form" id="tryitForm" class="form-horizontal" enctype="multipart/form-data" method="POST" action="{{ route('users.create') }}">
                                {!! Form::token() !!}
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Avatar</label>
                                    <div class="col-lg-6">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                                <img src="https://via.placeholder.com/200x150" alt="..."></div>
                                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>
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
                                                    <input type="text" placeholder="first name" name="first_name" id="first_name" class="form-control" value="{!! Request::old('first_name') !!}" required="" />
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
                                                    <input type="text" placeholder="last name" name="last_name" id="last_name" class="form-control" value="{!! Request::old('last_name') !!}" required="" />
                                                </div>
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
                                                    <input type="text" placeholder="" id="email" name="email" class="form-control" value="{!! Request::old('email') !!}"></div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="group" class="col-md-2 control-label">Group</label>
                                            <div class="col-md-6">
                                                <select class="form-control " title="Select group..." name="group" id="group" required="">
                                                    <option value="">Select</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" @if($role->id == Request::old('group')) selected="selected" @endif >{{ $role->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="activate" class="col-md-2 control-label"> Activate User</label>
                                            <div class="col-md-6">
                                                <input id="activate" name="activate" type="checkbox" class="pos-rel p-l-30" value="1" @if(Request::old('activate')) checked="checked" @endif  >
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-2 control-label">Gender</label>
                                            <div class="col-md-6">
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="gender" value="male" />
                                                        Male
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="gender" value="female" />
                                                        Female
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" name="gender" value="other" />
                                                        Other
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="prf-contacts sttng">
                                    <h3 class="text-primary">Contact</h3>
                                </div>
                                <div class="cd-block">
                                    <div class="cd-content">

                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Phone</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-phone text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="phone" name="phone" class="form-control" value="{!! Request::old('phone') !!}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label  col-md-2">Select Country</label>
                                            <div class="col-md-6">
                                                <select name="country" id="select2_sample4" class="form-control select2">
                                                    <option value="AF">Afghanistan</option>
                                                    <option value="AL">Albania</option>
                                                    <option value="DZ">Algeria</option>
                                                    <option value="AS">American Samoa</option>
                                                    <option value="AD">Andorra</option>
                                                    <option value="AO">Angola</option>
                                                    <option value="AI">Anguilla</option>
                                                    <option value="AR">Argentina</option>
                                                    <option value="AM">Armenia</option>
                                                    <option value="AW">Aruba</option>
                                                    <option value="AU">Australia</option>
                                                    <option value="AT">Austria</option>
                                                    <option value="AZ">Azerbaijan</option>
                                                    <option value="BS">Bahamas</option>
                                                    <option value="BH">Bahrain</option>
                                                    <option value="BD">Bangladesh</option>
                                                    <option value="BB">Barbados</option>
                                                    <option value="BY">Belarus</option>
                                                    <option value="BE">Belgium</option>
                                                    <option value="BZ">Belize</option>
                                                    <option value="BJ">Benin</option>
                                                    <option value="BM">Bermuda</option>
                                                    <option value="BT">Bhutan</option>
                                                    <option value="BO">Bolivia</option>
                                                    <option value="BA">Bosnia and Herzegowina</option>
                                                    <option value="BW">Botswana</option>
                                                    <option value="BV">Bouvet Island</option>
                                                    <option value="BR">Brazil</option>
                                                    <option value="IO">British Indian Ocean Territory</option>
                                                    <option value="BN">Brunei Darussalam</option>
                                                    <option value="BG">Bulgaria</option>
                                                    <option value="BF">Burkina Faso</option>
                                                    <option value="BI">Burundi</option>
                                                    <option value="KH">Cambodia</option>
                                                    <option value="CM">Cameroon</option>
                                                    <option value="CA">Canada</option>
                                                    <option value="CV">Cape Verde</option>
                                                    <option value="KY">Cayman Islands</option>
                                                    <option value="CF">Central African Republic</option>
                                                    <option value="TD">Chad</option>
                                                    <option value="CL">Chile</option>
                                                    <option value="CN">China</option>
                                                    <option value="CX">Christmas Island</option>
                                                    <option value="CC">Cocos (Keeling) Islands</option>
                                                    <option value="CO">Colombia</option>
                                                    <option value="KM">Comoros</option>
                                                    <option value="CG">Congo</option>
                                                    <option value="CD">Congo, the Democratic Republic of the</option>
                                                    <option value="CK">Cook Islands</option>
                                                    <option value="CR">Costa Rica</option>
                                                    <option value="CI">Cote d'Ivoire</option>
                                                    <option value="HR">Croatia (Hrvatska)</option>
                                                    <option value="CU">Cuba</option>
                                                    <option value="CY">Cyprus</option>
                                                    <option value="CZ">Czech Republic</option>
                                                    <option value="DK">Denmark</option>
                                                    <option value="DJ">Djibouti</option>
                                                    <option value="DM">Dominica</option>
                                                    <option value="DO">Dominican Republic</option>
                                                    <option value="EC">Ecuador</option>
                                                    <option value="EG">Egypt</option>
                                                    <option value="SV">El Salvador</option>
                                                    <option value="GQ">Equatorial Guinea</option>
                                                    <option value="ER">Eritrea</option>
                                                    <option value="EE">Estonia</option>
                                                    <option value="ET">Ethiopia</option>
                                                    <option value="FK">Falkland Islands (Malvinas)</option>
                                                    <option value="FO">Faroe Islands</option>
                                                    <option value="FJ">Fiji</option>
                                                    <option value="FI">Finland</option>
                                                    <option value="FR">France</option>
                                                    <option value="GF">French Guiana</option>
                                                    <option value="PF">French Polynesia</option>
                                                    <option value="TF">French Southern Territories</option>
                                                    <option value="GA">Gabon</option>
                                                    <option value="GM">Gambia</option>
                                                    <option value="GE">Georgia</option>
                                                    <option value="DE">Germany</option>
                                                    <option value="GH">Ghana</option>
                                                    <option value="GI">Gibraltar</option>
                                                    <option value="GR">Greece</option>
                                                    <option value="GL">Greenland</option>
                                                    <option value="GD">Grenada</option>
                                                    <option value="GP">Guadeloupe</option>
                                                    <option value="GU">Guam</option>
                                                    <option value="GT">Guatemala</option>
                                                    <option value="GN">Guinea</option>
                                                    <option value="GW">Guinea-Bissau</option>
                                                    <option value="GY">Guyana</option>
                                                    <option value="HT">Haiti</option>
                                                    <option value="HM">Heard and Mc Donald Islands</option>
                                                    <option value="VA">Holy See (Vatican City State)</option>
                                                    <option value="HN">Honduras</option>
                                                    <option value="HK">Hong Kong</option>
                                                    <option value="HU">Hungary</option>
                                                    <option value="IS">Iceland</option>
                                                    <option value="IN">India</option>
                                                    <option value="ID">Indonesia</option>
                                                    <option value="IR">Iran (Islamic Republic of)</option>
                                                    <option value="IQ">Iraq</option>
                                                    <option value="IE">Ireland</option>
                                                    <option value="IL">Israel</option>
                                                    <option value="IT">Italy</option>
                                                    <option value="JM">Jamaica</option>
                                                    <option value="JP">Japan</option>
                                                    <option value="JO">Jordan</option>
                                                    <option value="KZ">Kazakhstan</option>
                                                    <option value="KE">Kenya</option>
                                                    <option value="KI">Kiribati</option>
                                                    <option value="KP">Korea, Democratic People's Republic of</option>
                                                    <option value="KR">Korea, Republic of</option>
                                                    <option value="KW">Kuwait</option>
                                                    <option value="KG">Kyrgyzstan</option>
                                                    <option value="LA">Lao People's Democratic Republic</option>
                                                    <option value="LV">Latvia</option>
                                                    <option value="LB">Lebanon</option>
                                                    <option value="LS">Lesotho</option>
                                                    <option value="LR">Liberia</option>
                                                    <option value="LY">Libyan Arab Jamahiriya</option>
                                                    <option value="LI">Liechtenstein</option>
                                                    <option value="LT">Lithuania</option>
                                                    <option value="LU">Luxembourg</option>
                                                    <option value="MO">Macau</option>
                                                    <option value="MK">Macedonia, The Former Yugoslav Republic of</option>
                                                    <option value="MG">Madagascar</option>
                                                    <option value="MW">Malawi</option>
                                                    <option value="MY">Malaysia</option>
                                                    <option value="MV">Maldives</option>
                                                    <option value="ML">Mali</option>
                                                    <option value="MT">Malta</option>
                                                    <option value="MH">Marshall Islands</option>
                                                    <option value="MQ">Martinique</option>
                                                    <option value="MR">Mauritania</option>
                                                    <option value="MU">Mauritius</option>
                                                    <option value="YT">Mayotte</option>
                                                    <option value="MX">Mexico</option>
                                                    <option value="FM">Micronesia, Federated States of</option>
                                                    <option value="MD">Moldova, Republic of</option>
                                                    <option value="MC">Monaco</option>
                                                    <option value="MN">Mongolia</option>
                                                    <option value="MS">Montserrat</option>
                                                    <option value="MA">Morocco</option>
                                                    <option value="MZ">Mozambique</option>
                                                    <option value="MM">Myanmar</option>
                                                    <option value="NA">Namibia</option>
                                                    <option value="NR">Nauru</option>
                                                    <option value="NP">Nepal</option>
                                                    <option value="NL">Netherlands</option>
                                                    <option value="AN">Netherlands Antilles</option>
                                                    <option value="NC">New Caledonia</option>
                                                    <option value="NZ">New Zealand</option>
                                                    <option value="NI">Nicaragua</option>
                                                    <option value="NE">Niger</option>
                                                    <option value="NG">Nigeria</option>
                                                    <option value="NU">Niue</option>
                                                    <option value="NF">Norfolk Island</option>
                                                    <option value="MP">Northern Mariana Islands</option>
                                                    <option value="NO">Norway</option>
                                                    <option value="OM">Oman</option>
                                                    <option value="PK">Pakistan</option>
                                                    <option value="PW">Palau</option>
                                                    <option value="PA">Panama</option>
                                                    <option value="PG">Papua New Guinea</option>
                                                    <option value="PY">Paraguay</option>
                                                    <option value="PE">Peru</option>
                                                    <option value="PH">Philippines</option>
                                                    <option value="PN">Pitcairn</option>
                                                    <option value="PL">Poland</option>
                                                    <option value="PT">Portugal</option>
                                                    <option value="PR">Puerto Rico</option>
                                                    <option value="QA">Qatar</option>
                                                    <option value="RE">Reunion</option>
                                                    <option value="RO">Romania</option>
                                                    <option value="RU">Russian Federation</option>
                                                    <option value="RW">Rwanda</option>
                                                    <option value="KN">Saint Kitts and Nevis</option>
                                                    <option value="LC">Saint LUCIA</option>
                                                    <option value="VC">Saint Vincent and the Grenadines</option>
                                                    <option value="WS">Samoa</option>
                                                    <option value="SM">San Marino</option>
                                                    <option value="ST">Sao Tome and Principe</option>
                                                    <option value="SA">Saudi Arabia</option>
                                                    <option value="SN">Senegal</option>
                                                    <option value="SC">Seychelles</option>
                                                    <option value="SL">Sierra Leone</option>
                                                    <option value="SG">Singapore</option>
                                                    <option value="SK">Slovakia (Slovak Republic)</option>
                                                    <option value="SI">Slovenia</option>
                                                    <option value="SB">Solomon Islands</option>
                                                    <option value="SO">Somalia</option>
                                                    <option value="ZA">South Africa</option>
                                                    <option value="GS">South Georgia and the South Sandwich Islands</option>
                                                    <option value="ES">Spain</option>
                                                    <option value="LK">Sri Lanka</option>
                                                    <option value="SH">St. Helena</option>
                                                    <option value="PM">St. Pierre and Miquelon</option>
                                                    <option value="SD">Sudan</option>
                                                    <option value="SR">Suriname</option>
                                                    <option value="SJ">Svalbard and Jan Mayen Islands</option>
                                                    <option value="SZ">Swaziland</option>
                                                    <option value="SE">Sweden</option>
                                                    <option value="CH">Switzerland</option>
                                                    <option value="SY">Syrian Arab Republic</option>
                                                    <option value="TW">Taiwan, Province of China</option>
                                                    <option value="TJ">Tajikistan</option>
                                                    <option value="TZ">Tanzania, United Republic of</option>
                                                    <option value="TH">Thailand</option>
                                                    <option value="TG">Togo</option>
                                                    <option value="TK">Tokelau</option>
                                                    <option value="TO">Tonga</option>
                                                    <option value="TT">Trinidad and Tobago</option>
                                                    <option value="TN">Tunisia</option>
                                                    <option value="TR">Turkey</option>
                                                    <option value="TM">Turkmenistan</option>
                                                    <option value="TC">Turks and Caicos Islands</option>
                                                    <option value="TV">Tuvalu</option>
                                                    <option value="UG">Uganda</option>
                                                    <option value="UA">Ukraine</option>
                                                    <option value="AE">United Arab Emirates</option>
                                                    <option value="GB">United Kingdom</option>
                                                    <option value="US">United States</option>
                                                    <option value="UM">United States Minor Outlying Islands</option>
                                                    <option value="UY">Uruguay</option>
                                                    <option value="UZ">Uzbekistan</option>
                                                    <option value="VU">Vanuatu</option>
                                                    <option value="VE">Venezuela</option>
                                                    <option value="VN">Viet Nam</option>
                                                    <option value="VG">Virgin Islands (British)</option>
                                                    <option value="VI">Virgin Islands (U.S.)</option>
                                                    <option value="WF">Wallis and Futuna Islands</option>
                                                    <option value="EH">Western Sahara</option>
                                                    <option value="YE">Yemen</option>
                                                    <option value="ZM">Zambia</option>
                                                    <option value="ZW">Zimbabwe</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-2 control-label" for="address">Address</label>
                                            <div class="col-lg-6">
                                                <textarea rows="5" cols="30" class="form-control" id="address" name="address"> {!! Request::old('address') !!}</textarea>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-2 control-label" for="zip">Zip</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-dot-circle-o text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="zip" class="form-control" name="zip"  value="{!! Request::old('zip') !!}" />
                                                </div>
                                            </div>
                                        </div>




                                    </div>
                                    <div class="cd-block">
                                        <div class="cd-content">

                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-primary">social networks</h3>
                                </div>
                                <div class="cd-block">
                                    <div class="cd-content">
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Facebook</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-facebook text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="facebook" name="facebook" class="form-control" value="{!! Request::old('facebook') !!}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Twitter</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-twitter text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="twitter" name="twitter" class="form-control" value="{!! Request::old('twitter') !!}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Google plus</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-google-plus text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="google_plus" name="google_plus" class="form-control"  value="{!! Request::old('google_plus') !!}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Skype</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-skype text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="skype" name="skype" class="form-control" value="{!! Request::old('skype') !!}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Flickr</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-flickr text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="flickr" name="flickr" class="form-control" value="{!! Request::old('flickr') !!}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Youtube</label>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                                <span class="input-group-addon">
                                                                    <i class="fa fa-fw fa-youtube text-primary"></i>
                                                                </span>
                                                    <input type="text" placeholder=" " id="youtube" name="youtube" class="form-control" value="{!! Request::old('youtube') !!}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <input type="submit" class="btn btn-primary" name="save" value="Save" />
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
    <script src="{{ qm_asset('assets/js/custom_js/addnew_user.js') }}" type="text/javascript"></script>
@stop