<div class="col-md-12">
    <h4 class="qm-heading"><i class="fa fa-user"></i> Profile </h4>
    <div class="col-md-12 qm-box profile">
        <div class="col-md-2 col-sm-2 col-xs-2">
            <img src="{!! Auth::user()->avatar_image !!}?s=120" alt="img" class="pull-left img-circle"/>
        </div>
        <div class="qm-account-name col-md-8 col-sm-8 col-xs-8">
            <strong>{{ Auth::user()->display_name }}</strong> <br>
            <div class="qm-account-mail">{{ Auth::user()->user_login }}</div>

        </div>
        @if (!empty($latestMood))
            <div class="col-md-2 col-sm-2 col-xs-2 latest-mood">
                <img src="/embeddable/assets/img/{{ $latestMood }}" style="width:120px" alt="img" class="pull-right img-circle"/>
            </div>
        @endif
    </div>
</div>
