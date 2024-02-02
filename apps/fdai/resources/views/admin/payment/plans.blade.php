<div class="row mt10">
    <?php $downgrade = true ?>
    @foreach ($plans as $plan)
        <div class="col-md-4">
            <div class="panel plan-box {{ ($currentPlan == $plan['id'] ? 'panel-info' : 'panel-primary') }}">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {!! $plan['name'] !!}
                    </h3>
                    <span class="pull-right">
                        @if($plan['price'] === 0)
                            Free
                        @else
                            ${!! $plan['price']/100 !!}/Month
                        @endif
                    </span>
                </div>
                <div class="panel-body">
                    <div class="features">
                        @foreach (json_decode($plan['description']) as $feature)
                            <div>{!! $feature !!}</div>
                        @endforeach
                    </div>
                    @if($currentPlan == $plan['id'])
                        <?php $downgrade = false ?>
                        <a href="#" class="btn btn-info btn-block disabled" role="button">
                            Current Plan
                        </a>
                    @elseif($downgrade)
                        <a href="#"
                           class="btn btn-primary btn-block downgradePlan"
                           data-plan-id="{!! $plan['id'] !!}"
                           role="button">
                            <strong><i class="fa fa-chevron-down"></i> Downgrade</strong>
                        </a>
                    @else
                        <a href="#"
                           data-plan-id="{!! $plan['id'] !!}"
                           data-plan-name="{!! $plan['name'] !!}"
                           data-plan-price="{!! $plan['price']/100 !!}"
                           class="btn btn-primary btn-block purchase"
                           role="button">
                            <strong><i class="fa fa-credit-card"></i> Upgrade</strong>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-md-offset-4 col-md-4">
        <div class="panel plan-box {{ ($currentPlan == 7 ? 'panel-info' : 'panel-primary') }}">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Enterprise
                </h3>
            </div>
            <div class="panel-body">
                <div class="features">
                    <div><b>Unlimited</b> API Calls / Day</div>
                    <div>Forum / Email / Phone Support / Smoke Signals / Carrier Pigeon Support</div>
                </div>
                @if($currentPlan == 7)
                    <a href="#" class="btn btn-info btn-block" role="button">
                        Current Plan
                    </a>
                @else
                    <a href="mailto:help@curedao.org" class="btn btn-primary btn-block" role="button">
                        <strong><i class="fa fa-envelope-o"></i> Contact Us</strong>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="purchase_confirm" tabindex="-2" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Purchase</h4>
            </div>
            <div class="modal-body">
                <div class="bs-example">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#individual" data-toggle="tab">Individual</a>
                        </li>
                        <li>
                            <a href="#company" data-toggle="tab">Company</a>
                        </li>
                    </ul>
                    <form id="subscribeForm" class="form-horizontal" method="post" action="{{ route('account.subscribe-post') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="plan_id" id="plan_id"/>
                        <input type="hidden" name="app_id" value="{{ $appId }}"/>
                        <input type="hidden" name="card_token" id="card_token"/>
                        <input type="hidden" id="plan_name"/>
                        <input type="hidden" id="plan_price"/>
                        <input type="hidden" id="invoice_type" name="invoice_type"/>
                        <div id="tabContent" class="tab-content">
                            <div class="form-group">
                                <label class="control-label col-md-4">Country</label>
                                <div class="col-md-6">
                                    @include('countries', ['default' => 'US', 'id' => 'select2_sample4'])
                                </div>
                            </div>
                            <div class="tab-pane fade active in" id="individual">

                            </div>
                            <div class="tab-pane fade" id="company">
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="company_name">Company Name</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="company_name" name="company_name" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="address">Address</label>
                                    <div class="col-md-6">
                                        <textarea rows="2" class="form-control" id="address" name="address"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="city">City</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="city" name="city" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="state">State</label>
                                    <div class="col-md-3">
                                        <input type="text" placeholder="" id="state" class="form-control" name="state"  value="" />
                                    </div>
                                    <label class="col-md-1 control-label" for="zip">Zip</label>
                                    <div class="col-md-2">
                                        <input type="text" placeholder="" id="zip" class="form-control" name="zip"  value="" />
                                    </div>
                                </div>
                            </div>
                            <div id="order" class="form-group">
                                <label class="col-md-4">Your Plan</label>
                                <div class="col-md-6"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button id="continueButton" class="btn btn-success pull-right">Continue</button>
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
