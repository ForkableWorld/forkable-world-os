<!-- Client Secret Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_secret', 'Client Secret:') !!}
    {!! Form::text('client_secret', null, ['class' => 'form-control']) !!}
</div>

<!-- Redirect Uri Field -->
<div class="form-group col-sm-6">
    {!! Form::label('redirect_uri', 'Redirect Uri:') !!}
    {!! Form::text('redirect_uri', null, ['class' => 'form-control']) !!}
</div>

<!-- Grant Types Field -->
<div class="form-group col-sm-6">
    {!! Form::label('grant_types', 'Grant Types:') !!}
    {!! Form::text('grant_types', null, ['class' => 'form-control']) !!}
</div>

<!-- User Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Icon Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('icon_url', 'Icon Url:') !!}
    {!! Form::text('icon_url', null, ['class' => 'form-control']) !!}
</div>

<!-- App Identifier Field -->
<div class="form-group col-sm-6">
    {!! Form::label('app_identifier', 'App Identifier:') !!}
    {!! Form::text('app_identifier', null, ['class' => 'form-control']) !!}
</div>

<!-- Earliest Measurement Start At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('earliest_measurement_start_at', 'Earliest Measurement Start At:') !!}
    {!! Form::date('earliest_measurement_start_at', null, ['class' => 'form-control','id'=>'earliest_measurement_start_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#earliest_measurement_start_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Latest Measurement Start At Field -->
<div class="form-group col-sm-6">
    {!! Form::label('latest_measurement_start_at', 'Latest Measurement Start At:') !!}
    {!! Form::date('latest_measurement_start_at', null, ['class' => 'form-control','id'=>'latest_measurement_start_at']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#latest_measurement_start_at').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Number Of Global Variable Relationships Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_global_variable_relationships', 'Number Of Global Variable Relationships:') !!}
    {!! Form::number('number_of_global_variable_relationships', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Applications Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_applications', 'Number Of Applications:') !!}
    {!! Form::number('number_of_applications', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Oauth Access Tokens Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_oauth_access_tokens', 'Number Of Oauth Access Tokens:') !!}
    {!! Form::number('number_of_oauth_access_tokens', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Oauth Authorization Codes Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_oauth_authorization_codes', 'Number Of Oauth Authorization Codes:') !!}
    {!! Form::number('number_of_oauth_authorization_codes', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Oauth Refresh Tokens Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_oauth_refresh_tokens', 'Number Of Oauth Refresh Tokens:') !!}
    {!! Form::number('number_of_oauth_refresh_tokens', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Button Clicks Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_button_clicks', 'Number Of Button Clicks:') !!}
    {!! Form::number('number_of_button_clicks', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Collaborators Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_collaborators', 'Number Of Collaborators:') !!}
    {!! Form::number('number_of_collaborators', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Common Tags Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_common_tags', 'Number Of Common Tags:') !!}
    {!! Form::number('number_of_common_tags', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Connections Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_connections', 'Number Of Connections:') !!}
    {!! Form::number('number_of_connections', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Connector Imports Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_connector_imports', 'Number Of Connector Imports:') !!}
    {!! Form::number('number_of_connector_imports', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of Connectors Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_connectors', 'Number Of Connectors:') !!}
    {!! Form::number('number_of_connectors', null, ['class' => 'form-control']) !!}
</div>

<!-- Number Of VariableRelationships Field -->
<div class="form-group col-sm-6">
    {!! Form::label('number_of_correlations', 'Number Of VariableRelationships:') !!}
    {!! Form::number('number_of_correlations', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.oAuthClients.index') }}" class="btn btn-default">Cancel</a>
</div>
