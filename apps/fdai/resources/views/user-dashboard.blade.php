<?php /** @var Illuminate\Support\Collection|App\Models\Card[] $cards
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */ ?>
@extends('layouts.material-app', ['activePage' => 'user-dashboard', 'titlePage' => __('Dashboard')])

@section('content')
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ \App\Utils\IonicHelper::getReminderManagementUrl() }}">
              <div id="reminders-card" class="card card-stats" title="Variables You Are Regularly Recording">
                <div class="card-header card-header-warning card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">notification_important</i>
                  </div>
                  <p class="card-category">Tracking Reminders</p>
                  <h3 class="card-title">{{ \App\Slim\Middleware\QMAuth::getQMUser()->numberOfTrackingReminders }}
    {{--                <small>Variables You Are Regularly Recording</small>--}}
                  </h3>
                </div>
                <div class="card-footer">
                  <div class="stats">
                        <i class="material-icons text-danger">notification_important</i>
                        Manage Reminders
                  </div>
                </div>
              </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ \App\Utils\IonicHelper::getHistoryUrl() }}">
              <div id="measurements-card" class="card card-stats" title="Number of Data Points Recorded">
                <div class="card-header card-header-success card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">edit</i>
                  </div>
                  <p class="card-category">Measurements</p>
                  <h3 class="card-title">{{ \App\Slim\Middleware\QMAuth::getQMUser()->numberOfMeasurements }}</h3>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">launch</i> View Measurements
                  </div>
                </div>
              </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ \App\Buttons\States\ImportStateButton::url() }}">
                <div id="connections-card" class="card card-stats" title="Number of Data Sources You're Importing From">
                    <div class="card-header card-header-danger card-header-icon">
                      <div class="card-icon">
                        <i class="material-icons">cloud_download</i>
                      </div>
                      <p class="card-category">Data Connections</p>
                        <h3 class="card-title">{{ \App\Slim\Middleware\QMAuth::getQMUser()->numberOfConnections }}</h3>
                    </div>
                    <div class="card-footer">
                      <div class="stats">
                        <i class="material-icons">launch</i> Connect More Data Sources
                      </div>
                    </div>
              </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <a href="{{ \App\Utils\IonicHelper::getStudyCreationUrl() }}">
              <div class="card card-stats">
                <div class="card-header card-header-info card-header-icon">
                  <div class="card-icon">
                    <i class="fa fa-line-chart"></i>
                  </div>
                  <p class="card-category">Studies</p>
                    <h3 class="card-title">{{ \App\Slim\Middleware\QMAuth::getQMUser()->numberOfCorrelations }}</h3>
                </div>
                <div class="card-footer">
                  <div class="stats">
                    <i class="material-icons">launch</i> Create a Study
                  </div>
                </div>
              </div>
            </a>
        </div>
      </div>
        @include('example-material-charts')
        @include('example-material-tables')
    </div>
  </div>
@endsection

@push('js')
  <script>
    $(document).ready(function() {
      // Javascript method's body can be found in assets/js/demos.js
      md.initDashboardPageCharts();
    });
  </script>
@endpush
