@extends('layouts.admin-lte-app', ['title' => null ])

@section('content')
    <section class="content-header">
        <h1>
            Measurement
        </h1>
         @include('single-model-menu-button')
   </section>
   <div class="content">
        @include('adminlte-templates::common.errors')
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    {!! Form::open(['route' => 'datalab.measurements.store']) !!}

                    <?php /** @var App\Models\Measurement $measurement */ ?>
                    <!-- User Id Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('user_id', 'User Id:') !!}
                            {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
                        </div>

                        <!-- Variable Id Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('variable_id', 'Variable Id:') !!}
                            {!! Form::number('variable_id', null, ['class' => 'form-control']) !!}
                        </div>

                        <!-- Start Time Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('start_time', 'Start Time:') !!}
                            {!! Form::number('start_time', null, ['class' => 'form-control']) !!}
                        </div>

                        <!-- Value Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('value', 'Value:') !!}
                            {!! Form::number('value', null, ['class' => 'form-control']) !!}
                        </div>

                        <!-- Unit Id Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('unit_id', 'Unit') !!}
                            {!! $measurement->getUnitSelector() !!}
                        </div>

                        <!-- Duration Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('duration', 'Duration:') !!}
                            {!! Form::number('duration', null, ['class' => 'form-control']) !!}
                        </div>



                        <!-- User Variable Id Fild -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('user_variable_id', 'User Variable Id:') !!}
                            {!! Form::number('user_variable_id', null, ['class' => 'form-control']) !!}
                        </div>

                        <!-- Start At Field -->
                        <div class="form-group col-sm-6">
                            {!! Form::label('start_at', 'Start At:') !!}
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


                        {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
