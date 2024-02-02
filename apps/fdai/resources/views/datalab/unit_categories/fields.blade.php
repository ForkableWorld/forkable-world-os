<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control']) !!}
</div>

<!-- Can Be Summed Field -->
<div class="form-group col-sm-6">
    {!! Form::label('can_be_summed', 'Can Be Summed:') !!}
    <label class="checkbox-inline">
        {!! Form::hidden('can_be_summed', 0) !!}
        {!! Form::checkbox('can_be_summed', '1', null) !!}
    </label>
</div>


<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.unitCategories.index') }}" class="btn btn-default">Cancel</a>
</div>
