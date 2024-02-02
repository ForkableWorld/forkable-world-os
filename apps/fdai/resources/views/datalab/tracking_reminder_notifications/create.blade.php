@extends('layouts.admin-lte-app')

@section('content')
    <section class="content-header">
        <h1>
            Tracking Reminder Notification
        </h1>
    </section>
    <div class="content">
        @include('adminlte-templates::common.errors')
        <div class="box box-primary">
            <div class="box-body">
                <div class="row">
                    {!! Form::open(['route' => 'datalab.trackingReminderNotifications.store']) !!}

                        @include('datalab.tracking_reminder_notifications.fields')

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
