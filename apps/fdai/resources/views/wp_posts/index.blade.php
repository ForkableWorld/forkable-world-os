@extends('layouts.admin-lte-app')

@section('content')

    @if(Session::has('success_message'))
        <div class="alert alert-success">
            <span class="glyphicon glyphicon-ok"></span>
            {!! session('success_message') !!}

            <button type="button" class="close" data-dismiss="alert" aria-label="close">
                <span aria-hidden="true">&times;</span>
            </button>

        </div>
    @endif

    <div class="panel panel-default">

        <div class="panel-heading clearfix">

            <div class="pull-left">
                <h4 class="mt-5 mb-5">Wp Posts</h4>
            </div>

            <div class="btn-group btn-group-sm pull-right" role="group">
                <a href="{{ route('wp_posts.wp_post.create') }}" class="btn btn-success" title="Create New Wp Post">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </a>
            </div>

        </div>

        @if(count($wpPosts) == 0)
            <div class="panel-body text-center">
                <h4>No Wp Posts Available.</h4>
            </div>
        @else
        <div class="panel-body panel-body-with-table">
            <div class="table-responsive">

                <table class="table table-striped ">
                    <thead>
                        <tr>
                            <th>Post Author</th>
                            <th>Post Date</th>
                            <th>Post Date Gmt</th>

                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($wpPosts as $wpPost)
                        <tr>
                            <td>{{ optional($wpPost->WpUser)->id }}</td>
                            <td>{{ $wpPost->post_date }}</td>
                            <td>{{ $wpPost->post_date_gmt }}</td>

                            <td>

                                <form method="POST" action="{!! route('wp_posts.wp_post.destroy', $wpPost->ID) !!}" accept-charset="UTF-8">
                                <input name="_method" value="DELETE" type="hidden">
                                {{ csrf_field() }}

                                    <div class="btn-group btn-group-xs pull-right" role="group">
                                        <a href="{{ route('wp_posts.wp_post.show', $wpPost->ID ) }}" class="btn btn-info" title="Show Wp Post">
                                            <span class="glyphicon glyphicon-open" aria-hidden="true"></span>
                                        </a>
                                        <a href="{{ route('wp_posts.wp_post.edit', $wpPost->ID ) }}" class="btn btn-primary" title="Edit Wp Post">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </a>

                                        <button type="submit" class="btn btn-danger" title="Delete Wp Post" onclick="return confirm(&quot;Click Ok to delete Wp Post.&quot;)">
                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                        </button>
                                    </div>

                                </form>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <div class="panel-footer">
            {!! $wpPosts->render() !!}
        </div>

        @endif

    </div>
@endsection