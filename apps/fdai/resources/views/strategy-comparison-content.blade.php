<?php /** @var \App\Pages\StrategyComparisonPage $page */ ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header card-header-info">
                {{--                        <h4 class="card-title ">Strategy Comparison</h4>--}}
                <p class="card-category"> Click on a strategy for more details</p>
                {!! $page->getComparisonTabsHtml() !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {!! $page->getTableHtml() !!}
                </div>
            </div>
        </div>
    </div>
</div>