<div class="col-md-12">
    <h4 class="qm-heading"><i class="fa fa-info-circle"></i> Your Subscription</h4>
    <div class="qm-box">
        @include('web.account.premium-features-fragment')
        @if(!empty(Auth::user()->stripe_plan))
            <div class="col-md-4 col-sm-6 col-xs-6">Plan</div>
            <div class="col-md-8 col-sm-6 col-xs-6 text-left">{{ ucwords(Auth::user()->stripe_plan) }}
                @if(Auth::user()->onTrial())
                    (Trial)
                @endif
            </div>
        @endif
        @if(empty(Auth::user()->last_four))
            <div style="text-align: center">
                <br>
                <a href="{{ route('account.update.card') }}" id="edit-account" class="btn btn-large btn-primary">
                    SUBSCRIBE TO QUANTIMODO PLUS
                </a>
            </div>
        @else
            <div class="col-md-4 col-sm-6 col-xs-6">Credit Card</div>
            <div class="col-md-8 col-sm-6 col-xs-6 text-left add-card">Ends with {{ Auth::user()->last_four }} <a href="{{ route('account.update.card') }}">Update card or enter coupon</a></div>
        @endif
        @if(!empty(Auth::user()->subscription_ends_at))
            <div class="col-md-4 col-sm-6 col-xs-6">Next invoice:</div>
            <div class="col-md-8 col-sm-6 col-xs-6 text-right">
                {{
                    Auth::user()->subscription_ends_at ?
                    Auth::user()->subscription_ends_at->toFormattedDateString() :
                    Auth::user()->trial_ends_at->toFormattedDateString()
                }}
            </div>
        @endif
    </div>
    <div class="clearfix"></div>
    @if(!empty(Auth::user()->last_four))
        <div class="col-md-12 mt10">
            <div class="alert alert-success">
                <div>Monthly: <span class="strike">$9 per month</span> $6.75 per month</div>
                <div>Annual: <span class="strike">$72 per year</span> $54.00 per year</div>
                <br>
                <strong>Special offer:</strong> Upgrade to {{app_display_name()}} Plus by January 31 and get <strong>25%</strong> off the normal subscription price!
            </div>
            @if(Auth::user()->stripe_plan == 'monthly')
                <a href="{{ route('account.upgrade') }}" class="btn btn-success">Upgrade</a> <strong>Pay $27 less with yearly plan</strong>
            @else
                <a href="{{ route('account.downgrade') }}" class="btn btn-danger">Downgrade</a>
            @endif
        </div>
    @endif
</div>