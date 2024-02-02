<!-- Post Author Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_author', 'Post Author:') !!}
    {!! Form::number('post_author', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_date', 'Post Date:') !!}
    {!! Form::date('post_date', null, ['class' => 'form-control','id'=>'post_date']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#post_date').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Post Date Gmt Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_date_gmt', 'Post Date Gmt:') !!}
    {!! Form::date('post_date_gmt', null, ['class' => 'form-control','id'=>'post_date_gmt']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#post_date_gmt').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Post Content Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('post_content', 'Post Content:') !!}
    {!! Form::textarea('post_content', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Title Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('post_title', 'Post Title:') !!}
    {!! Form::textarea('post_title', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Excerpt Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('post_excerpt', 'Post Excerpt:') !!}
    {!! Form::textarea('post_excerpt', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_status', 'Post Status:') !!}
    {!! Form::text('post_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Comment Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('comment_status', 'Comment Status:') !!}
    {!! Form::text('comment_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Ping Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ping_status', 'Ping Status:') !!}
    {!! Form::text('ping_status', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_password', 'Post Password:') !!}
    {!! Form::text('post_password', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_name', 'Post Name:') !!}
    {!! Form::text('post_name', null, ['class' => 'form-control']) !!}
</div>

<!-- To Ping Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('to_ping', 'To Ping:') !!}
    {!! Form::textarea('to_ping', null, ['class' => 'form-control']) !!}
</div>

<!-- Pinged Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('pinged', 'Pinged:') !!}
    {!! Form::textarea('pinged', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Modified Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_modified', 'Post Modified:') !!}
    {!! Form::date('post_modified', null, ['class' => 'form-control','id'=>'post_modified']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#post_modified').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Post Modified Gmt Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_modified_gmt', 'Post Modified Gmt:') !!}
    {!! Form::date('post_modified_gmt', null, ['class' => 'form-control','id'=>'post_modified_gmt']) !!}
</div>

@push('scripts')
    <script type="text/javascript">
        $('#post_modified_gmt').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            useCurrent: false
        })
    </script>
@endpush

<!-- Post Content Filtered Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('post_content_filtered', 'Post Content Filtered:') !!}
    {!! Form::textarea('post_content_filtered', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Parent Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_parent', 'Post Parent:') !!}
    {!! Form::number('post_parent', null, ['class' => 'form-control']) !!}
</div>

<!-- Guid Field -->
<div class="form-group col-sm-6">
    {!! Form::label('guid', 'Guid:') !!}
    {!! Form::text('guid', null, ['class' => 'form-control']) !!}
</div>

<!-- Menu Order Field -->
<div class="form-group col-sm-6">
    {!! Form::label('menu_order', 'Menu Order:') !!}
    {!! Form::number('menu_order', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_type', 'Post Type:') !!}
    {!! Form::text('post_type', null, ['class' => 'form-control']) !!}
</div>

<!-- Post Mime Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('post_mime_type', 'Post Mime Type:') !!}
    {!! Form::text('post_mime_type', null, ['class' => 'form-control']) !!}
</div>

<!-- Comment Count Field -->
<div class="form-group col-sm-6">
    {!! Form::label('comment_count', 'Comment Count:') !!}
    {!! Form::number('comment_count', null, ['class' => 'form-control']) !!}
</div>

<!-- Client Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('client_id', 'Client Id:') !!}
    {!! Form::text('client_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('datalab.posts.index') }}" class="btn btn-default">Cancel</a>
</div>
