@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Users List
    @parent
@stop

{{-- page level styles --}}
@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ qm_asset('assets/vendors/datatables/css/dataTables.bootstrap.css') }}" />
@stop


{{-- Page content --}}
@section('content')

    <!-- Main content -->
    <section class="content paddingleft_right15">
        <div class="row">
            <div class="col-lg-12">
                <div class="btn-group" role="group" aria-label="...">
                    <a class="btn btn-default btn-sm" href="{{ route('users') }}" role="button">Show Active users</a>
                    <a class="btn btn-success btn-sm" href="{{ route('users') }}?withTrashed=true" role="button">Include Deleted Users</a>
                    <a class="btn btn-danger btn-sm" href="{{ route('users') }}?onlyTrashed=true">Show Only Deleted Users</a>
                </div>
                <br />&nbsp;
                <div class="panel panel-primary ">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-fw fa-bell-o"></i>
                        Users list
                    </h3>
                                <span class="pull-right">
                                    <i class="fa fa-fw fa-times removepanel clickable"></i>
                                    <i class="fa fa-fw fa-chevron-up clickable"></i>
                                </span>
                </div>
                <div class="panel-body">
                    <table class="table" id="table">
                        <thead>
                        <tr class="filters">
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>User E-mail</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{!! $user->first_name !!}</td>
                                <td>{!! $user->last_name !!}</td>
                                <td>{!! $user->email !!}</td>
                                <td>
                                    @if($activation = Activation::completed($user))
                                        Activated
                                    @else
                                        Pending
                                    @endif
                                </td>
                                <td>{!! $user->created_at->diffForHumans() !!}</td>
                                <td>
                                    @if(!$user->trashed())
                                        <a href="{{ route('users.edit', $user->id) }}"><i class="fa fa-fw fa-pencil text-warning"></i></a>
                                        @if((Auth::user()->ID != $user->id) && ($user->id != 1))
                                            <a href="{{ route('confirm-delete/user', $user->id) }}" data-toggle="modal" data-target="#delete_confirm"><i class="fa fa-fw fa-times text-danger"></i></a>
                                        @endif
                                        <a href="{{ route('users.show', $user->id) }}"><i class="fa fa-fw fa-star text-primary"></i></a>
                                    @else
                                        <a href="{{ route('restore/user', $user->id) }}"><i class="fa fa-fw fa-undo text-danger"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>    <!-- row-->
    </section>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
    @include('datatables_js')
    <script type="text/javascript" src="{{ qm_asset('assets/vendors/datatables/js/dataTables.bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#table').DataTable();
        });
    </script>

    <div class="modal fade" id="delete_confirm" tabindex="-1" role="dialog" aria-labelledby="user_delete_confirm_title" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content"></div>
        </div>
    </div>
    <script>
        $(function () {
            $('body').on('hidden.bs.modal', '.modal', function () {
                $(this).removeData('bs.modal');
            });
        });
    </script>
@stop