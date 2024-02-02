@if(sizeof($latestMeasurements) > 1)
    <div class="col-md-12">
        <h4 class="qm-heading"><i class="fa fa-calendar-check-o"></i> Today</h4>
        <div class="qm-box">
            <ul class="qm-list">
                @foreach($latestMeasurements as $measurement)
                    <li>{{ $measurement->value . ' ' . $measurement->unitAbbreviatedName . ' ' .  $measurement->variableName }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif