@extends('layouts.admin-lte-app')

@section('content')

<div class="panel panel-default">
    <div class="panel-heading clearfix">

        <span class="pull-left">
            <h4 class="mt-5 mb-5">{{ isset($title) ? $title : 'Wp Post' }}</h4>
        </span>

        <div class="pull-right">

            <form method="POST" action="{!! route('wp_posts.wp_post.destroy', $wpPost->ID) !!}" accept-charset="UTF-8">
            <input name="_method" value="DELETE" type="hidden">
            {{ csrf_field() }}
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('wp_posts.wp_post.index') }}" class="btn btn-primary" title="Show All Wp Post">
                        <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span>
                    </a>

                    <a href="{{ route('wp_posts.wp_post.create') }}" class="btn btn-success" title="Create New Wp Post">
                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                    </a>

                    <a href="{{ route('wp_posts.wp_post.edit', $wpPost->ID ) }}" class="btn btn-primary" title="Edit Wp Post">
                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                    </a>

                    <button type="submit" class="btn btn-danger" title="Delete Wp Post" onclick="return confirm(&quot;Click Ok to delete Wp Post.?&quot;)">
                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                    </button>
                </div>
            </form>

        </div>

    </div>

    <div class="panel-body">
        <dl class="dl-horizontal">
            <dt>Post Author</dt>
            <dd>{{ optional($wpPost->WpUser)->id }}</dd>
            <dt>Post Date</dt>
            <dd>{{ $wpPost->post_date }}</dd>
            <dt>Post Date Gmt</dt>
            <dd>{{ $wpPost->post_date_gmt }}</dd>
            <dt>Post Content</dt>
            <dd>{!! $wpPost->post_content !!}</dd>
            <dt>Post Title</dt>
            <dd>{{ $wpPost->post_title }}</dd>
            <dt>Post Excerpt</dt>
            <dd>{{ $wpPost->post_excerpt }}</dd>
            <dt>Post Status</dt>
            <dd>{{ $wpPost->post_status }}</dd>
            <dt>Comment Status</dt>
            <dd>{{ $wpPost->comment_status }}</dd>
            <dt>Ping Status</dt>
            <dd>{{ $wpPost->ping_status }}</dd>
            <dt>Post Password</dt>
            <dd>{{ $wpPost->post_password }}</dd>
            <dt>Post Name</dt>
            <dd>{{ $wpPost->post_name }}</dd>
            <dt>To Ping</dt>
            <dd>{{ $wpPost->to_ping }}</dd>
            <dt>Pinged</dt>
            <dd>{{ $wpPost->pinged }}</dd>
            <dt>Post Modified</dt>
            <dd>{{ $wpPost->post_modified }}</dd>
            <dt>Post Modified Gmt</dt>
            <dd>{{ $wpPost->post_modified_gmt }}</dd>
            <dt>Post Content Filtered</dt>
            <dd>{!!  $wpPost->post_content_filtered !!}</dd>
            <dt>Post Parent</dt>
            <dd>{{ $wpPost->post_parent }}</dd>
            <dt>Guid</dt>
            <dd>{{ $wpPost->guid }}</dd>
            <dt>Menu Order</dt>
            <dd>{{ $wpPost->menu_order }}</dd>
            <dt>Post Type</dt>
            <dd>{{ $wpPost->post_type }}</dd>
            <dt>Post Mime Type</dt>
            <dd>{{ $wpPost->post_mime_type }}</dd>
            <dt>Comment Count</dt>
            <dd>{{ $wpPost->comment_count }}</dd>
            <dt>Updated At</dt>
            <dd>{{ $wpPost->updated_at }}</dd>
            <dt>Created At</dt>
            <dd>{{ $wpPost->created_at }}</dd>
            <dt>Deleted At</dt>
            <dd>{{ $wpPost->deleted_at }}</dd>
            <dt>Client</dt>
            <dd>{{ optional($wpPost->client)->id }}</dd>

        </dl>

    </div>
</div>

@endsection