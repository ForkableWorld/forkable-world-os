<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    {!! Form::text('type', null, ['class' => 'form-control']) !!}
</div>

<!-- Cause Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('cause_variable_id', 'Cause Variable Id:') !!}
    {!! Form::number('cause_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Effect Variable Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('effect_variable_id', 'Effect Variable Id:') !!}
    {!! Form::number('effect_variable_id', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Analysis Parameters Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('analysis_parameters', 'Analysis Parameters:') !!}
    {!! Form::textarea('analysis_parameters', null, ['class' => 'form-control']) !!}
</div>

<!-- User Study Text Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('user_study_text', 'User Study Text:') !!}
    {!! Form::textarea('user_study_text', null, ['class' => 'form-control']) !!}
</div>

<!-- User Title Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('user_title', 'User Title:') !!}
    {!! Form::textarea('user_title', null, ['class' => 'form-control']) !!}
</div>

<!-- Study Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('study_status', 'Study Status:') !!}
    {!! Form::text('study_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Comment Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('comment_status', 'Comment Status:') !!}
    {!! Form::text('comment_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Study Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('study_password', 'Study Password:') !!}
    {!! Form::text('study_password', null, ['class' => 'form-control']) !!}
</div>

<!-- Study Images Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('study_images', 'Study Images:') !!}
    {!! Form::textarea('study_images', null, ['class' => 'form-control']) !!}
</div>

<!-- Errors Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('errors', 'Errors:') !!}
    {!! Form::textarea('errors', null, ['class' => 'form-control']) !!}
</div>

<!-- Statistics Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('statistics', 'Statistics:') !!}
    {!! Form::textarea('statistics', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Published At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('published_at', 'Published At:') !!}
    {!! Form::date('published_at', null, ['class' => 'form-control','id'=>'published_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#published_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Wp Post Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('wp_post_id', 'Wp Post Id:') !!}
    {!! Form::number('wp_post_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Newest Data At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('newest_data_at', 'Newest Data At:') !!}
    {!! Form::date('newest_data_at', null, ['class' => 'form-control','id'=>'newest_data_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#newest_data_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Analysis Requested At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_requested_at', 'Analysis Requested At:') !!}
    {!! Form::date('analysis_requested_at', null, ['class' => 'form-control','id'=>'analysis_requested_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_requested_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Reason For Analysis Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reason_for_analysis', 'Reason For Analysis:') !!}
    {!! Form::text('reason_for_analysis', null, ['class' => 'form-control']) !!}
</div>

<!-- Analysis Ended At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_ended_at', 'Analysis Ended At:') !!}
    {!! Form::date('analysis_ended_at', null, ['class' => 'form-control','id'=>'analysis_ended_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_ended_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Analysis Started At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_started_at', 'Analysis Started At:') !!}
    {!! Form::date('analysis_started_at', null, ['class' => 'form-control','id'=>'analysis_started_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_started_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Internal Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('internal_error_message', 'Internal Error Message:') !!}
    {!! Form::text('internal_error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- User Error Message Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_error_message', 'User Error Message:') !!}
    {!! Form::text('user_error_message', null, ['class' => 'form-control']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>

<!-- Analysis Settings Modified At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('analysis_settings_modified_at', 'Analysis Settings Modified At:') !!}
    {!! Form::date('analysis_settings_modified_at', null, ['class' => 'form-control','id'=>'analysis_settings_modified_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#analysis_settings_modified_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.studies.index') }}" class="btn btn-default">Cancel</a>
</div>
