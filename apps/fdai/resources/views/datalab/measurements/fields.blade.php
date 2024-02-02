<?php /** @var App\Models\Measurement $model */ ?>

<!-- Value Field -->
<div class="form-group col-sm-6">
    {!! Form::label('value', 'Value') !!}
    {!! Form::number('value', null, ['class' => 'form-control']) !!}
</div>

<!-- Unit Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('unit_id', 'Unit') !!}
    {!! $model->getUnitSelector() !!}
</div>

<!-- Duration Field -->
<div class="form-group col-sm-6">
    {!! Form::label('duration', 'Duration:') !!}
    {!! Form::number('duration', null, ['class' => 'form-control']) !!}
</div>

<!-- Start At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('start_at', 'Timestamp') !!}
    {!! Form::date('start_at', null, ['class' => 'form-control','id'=>'start_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#start_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.measurements.index') }}" class="btn btn-default">Cancel</a>
</div>