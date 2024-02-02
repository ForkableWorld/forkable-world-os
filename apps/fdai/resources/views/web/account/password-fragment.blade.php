<div class="row">
    <div class="col-md-12">
        <h4 class="panel-heading3"><i class="fa fa-user"></i> Change Password</h4>
        @include('errors.errors')
        <form class="form-horizontal" role="form" method="post" action="">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

            <div class="form-group">
                <label for="old_password" class="col-sm-4 control-label">
                    Old Password
                </label>
                <div class="col-sm-4">
                    <input type="password" id="old_password" name="old_password" class="form-control" value="">
                </div>
            </div>

            <div class="form-group">
                <label for="new_password" class="col-sm-4 control-label">
                    New Password
                </label>
                <div class="col-sm-4">
                    <input type="password" id="new_password" name="new_password" class="form-control" value="">
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_new_password" class="col-sm-4 control-label">
                    Confirm New Password
                </label>
                <div class="col-sm-4">
                    <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" value="">
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-4">
                    <a class="btn btn-danger" href="{{ route('account') }}">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>