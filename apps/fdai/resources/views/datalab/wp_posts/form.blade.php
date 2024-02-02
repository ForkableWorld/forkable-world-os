
<div class="form-group {{ $errors->has('post_author') ? 'has-error' : '' }}">
    <label for="post_author" class="col-md-2 control-label">Post Author</label>
    <div class="col-md-10">
        <select class="form-control" id="post_author" name="post_author">
        	    <option value="" style="display: none;" {{ old('post_author', optional($wpPost)->post_author ?: '') == '' ? 'selected' : '' }} disabled selected>Enter post author here...</option>
        	@foreach ($WpUsers as $key => $WpUser)
			    <option value="{{ $key }}" {{ old('post_author', optional($wpPost)->post_author) == $key ? 'selected' : '' }}>
			    	{{ $WpUser }}
			    </option>
			@endforeach
        </select>

        {!! $errors->first('post_author', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_date') ? 'has-error' : '' }}">
    <label for="post_date" class="col-md-2 control-label">Post Date</label>
    <div class="col-md-10">
        <input class="form-control" name="post_date" type="text" id="post_date" value="{{ old('post_date', optional($wpPost)->post_date) }}" required="true" placeholder="Enter post date here...">
        {!! $errors->first('post_date', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_date_gmt') ? 'has-error' : '' }}">
    <label for="post_date_gmt" class="col-md-2 control-label">Post Date Gmt</label>
    <div class="col-md-10">
        <input class="form-control" name="post_date_gmt" type="text" id="post_date_gmt" value="{{ old('post_date_gmt', optional($wpPost)->post_date_gmt) }}" required="true" placeholder="Enter post date gmt here...">
        {!! $errors->first('post_date_gmt', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_content') ? 'has-error' : '' }}">
    <label for="post_content" class="col-md-2 control-label">Post Content</label>
    <div class="col-md-10">
        <input class="form-control" name="post_content" type="text" id="post_content" value="{{ old('post_content', optional($wpPost)->post_content) }}" maxlength="2147483647" placeholder="Enter post content here...">
        {!! $errors->first('post_content', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_title') ? 'has-error' : '' }}">
    <label for="post_title" class="col-md-2 control-label">Post Title</label>
    <div class="col-md-10">
        <textarea class="form-control" name="post_title" cols="50" rows="10" id="post_title" placeholder="Enter post title here...">{{ old('post_title', optional($wpPost)->post_title) }}</textarea>
        {!! $errors->first('post_title', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_excerpt') ? 'has-error' : '' }}">
    <label for="post_excerpt" class="col-md-2 control-label">Post Excerpt</label>
    <div class="col-md-10">
        <textarea class="form-control" name="post_excerpt" cols="50" rows="10" id="post_excerpt" placeholder="Enter post excerpt here...">{{ old('post_excerpt', optional($wpPost)->post_excerpt) }}</textarea>
        {!! $errors->first('post_excerpt', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_status') ? 'has-error' : '' }}">
    <label for="post_status" class="col-md-2 control-label">Post Status</label>
    <div class="col-md-10">
        <input class="form-control" name="post_status" type="text" id="post_status" value="{{ old('post_status', optional($wpPost)->post_status) }}" maxlength="20" placeholder="Enter post status here...">
        {!! $errors->first('post_status', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('comment_status') ? 'has-error' : '' }}">
    <label for="comment_status" class="col-md-2 control-label">Comment Status</label>
    <div class="col-md-10">
        <input class="form-control" name="comment_status" type="text" id="comment_status" value="{{ old('comment_status', optional($wpPost)->comment_status) }}" maxlength="20" placeholder="Enter comment status here...">
        {!! $errors->first('comment_status', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('ping_status') ? 'has-error' : '' }}">
    <label for="ping_status" class="col-md-2 control-label">Ping Status</label>
    <div class="col-md-10">
        <input class="form-control" name="ping_status" type="text" id="ping_status" value="{{ old('ping_status', optional($wpPost)->ping_status) }}" maxlength="20" placeholder="Enter ping status here...">
        {!! $errors->first('ping_status', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_password') ? 'has-error' : '' }}">
    <label for="post_password" class="col-md-2 control-label">Post Password</label>
    <div class="col-md-10">
        <input class="form-control" name="post_password" type="text" id="post_password" value="{{ old('post_password', optional($wpPost)->post_password) }}" maxlength="255" placeholder="Enter post password here...">
        {!! $errors->first('post_password', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_name') ? 'has-error' : '' }}">
    <label for="post_name" class="col-md-2 control-label">Post Name</label>
    <div class="col-md-10">
        <input class="form-control" name="post_name" type="text" id="post_name" value="{{ old('post_name', optional($wpPost)->post_name) }}" maxlength="200" placeholder="Enter post name here...">
        {!! $errors->first('post_name', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('to_ping') ? 'has-error' : '' }}">
    <label for="to_ping" class="col-md-2 control-label">To Ping</label>
    <div class="col-md-10">
        <textarea class="form-control" name="to_ping" cols="50" rows="10" id="to_ping" placeholder="Enter to ping here...">{{ old('to_ping', optional($wpPost)->to_ping) }}</textarea>
        {!! $errors->first('to_ping', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('pinged') ? 'has-error' : '' }}">
    <label for="pinged" class="col-md-2 control-label">Pinged</label>
    <div class="col-md-10">
        <textarea class="form-control" name="pinged" cols="50" rows="10" id="pinged" placeholder="Enter pinged here...">{{ old('pinged', optional($wpPost)->pinged) }}</textarea>
        {!! $errors->first('pinged', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_modified') ? 'has-error' : '' }}">
    <label for="post_modified" class="col-md-2 control-label">Post Modified</label>
    <div class="col-md-10">
        <input class="form-control" name="post_modified" type="text" id="post_modified" value="{{ old('post_modified', optional($wpPost)->post_modified) }}" required="true" placeholder="Enter post modified here...">
        {!! $errors->first('post_modified', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_modified_gmt') ? 'has-error' : '' }}">
    <label for="post_modified_gmt" class="col-md-2 control-label">Post Modified Gmt</label>
    <div class="col-md-10">
        <input class="form-control" name="post_modified_gmt" type="text" id="post_modified_gmt" value="{{ old('post_modified_gmt', optional($wpPost)->post_modified_gmt) }}" required="true" placeholder="Enter post modified gmt here...">
        {!! $errors->first('post_modified_gmt', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_content_filtered') ? 'has-error' : '' }}">
    <label for="post_content_filtered" class="col-md-2 control-label">Post Content Filtered</label>
    <div class="col-md-10">
        <input class="form-control" name="post_content_filtered" type="text" id="post_content_filtered" value="{{ old('post_content_filtered', optional($wpPost)->post_content_filtered) }}" maxlength="2147483647" placeholder="Enter post content filtered here...">
        {!! $errors->first('post_content_filtered', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_parent') ? 'has-error' : '' }}">
    <label for="post_parent" class="col-md-2 control-label">Post Parent</label>
    <div class="col-md-10">
        <input class="form-control" name="post_parent" type="text" id="post_parent" value="{{ old('post_parent', optional($wpPost)->post_parent) }}" min="0" placeholder="Enter post parent here...">
        {!! $errors->first('post_parent', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('guid') ? 'has-error' : '' }}">
    <label for="guid" class="col-md-2 control-label">Guid</label>
    <div class="col-md-10">
        <input class="form-control" name="guid" type="text" id="guid" value="{{ old('guid', optional($wpPost)->guid) }}" maxlength="255" placeholder="Enter guid here...">
        {!! $errors->first('guid', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('menu_order') ? 'has-error' : '' }}">
    <label for="menu_order" class="col-md-2 control-label">Menu Order</label>
    <div class="col-md-10">
        <input class="form-control" name="menu_order" type="number" id="menu_order" value="{{ old('menu_order', optional($wpPost)->menu_order) }}" min="-2147483648" max="2147483647" placeholder="Enter menu order here...">
        {!! $errors->first('menu_order', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_type') ? 'has-error' : '' }}">
    <label for="post_type" class="col-md-2 control-label">Post Type</label>
    <div class="col-md-10">
        <input class="form-control" name="post_type" type="text" id="post_type" value="{{ old('post_type', optional($wpPost)->post_type) }}" maxlength="20" placeholder="Enter post type here...">
        {!! $errors->first('post_type', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('post_mime_type') ? 'has-error' : '' }}">
    <label for="post_mime_type" class="col-md-2 control-label">Post Mime Type</label>
    <div class="col-md-10">
        <input class="form-control" name="post_mime_type" type="text" id="post_mime_type" value="{{ old('post_mime_type', optional($wpPost)->post_mime_type) }}" maxlength="100" placeholder="Enter post mime type here...">
        {!! $errors->first('post_mime_type', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('comment_count') ? 'has-error' : '' }}">
    <label for="comment_count" class="col-md-2 control-label">Comment Count</label>
    <div class="col-md-10">
        <input class="form-control" name="comment_count" type="text" id="comment_count" value="{{ old('comment_count', optional($wpPost)->comment_count) }}" min="0" placeholder="Enter comment count here...">
        {!! $errors->first('comment_count', '<p class="help-block">:message</p>') !!}
    </div>
</div>

<div class="form-group {{ $errors->has('client_id') ? 'has-error' : '' }}">
    <label for="client_id" class="col-md-2 control-label">Client</label>
    <div class="col-md-10">
        <select class="form-control" id="client_id" name="client_id">
        	    <option value="" style="display: none;" {{ old('client_id', optional($wpPost)->client_id ?: '') == '' ? 'selected' : '' }} disabled selected>Select client</option>
        	@foreach ($clients as $key => $client)
			    <option value="{{ $key }}" {{ old('client_id', optional($wpPost)->client_id) == $key ? 'selected' : '' }}>
			    	{{ $client }}
			    </option>
			@endforeach
        </select>

        {!! $errors->first('client_id', '<p class="help-block">:message</p>') !!}
    </div>
</div>

