<div class="col-lg-3 col-xs-6">
    <!-- small box -->
    <div class="small-box bg-{{ $color }}">
        <div class="inner">
            <h3 style='padding: 10px 0 0 10px;'>{{ $number }}</h3>
            <p style='padding: 0 0 0 10px;'>{{ $name }}</p>
        </div>
        <div class="icon">
            <i class="{{ $icon }}"></i>
        </div>
        <a href="{{ $url }}" class="small-box-footer">
            {{ $tooltip }} <i class="fa fa-arrow-circle-right"></i>
        </a>
    </div>
</div>