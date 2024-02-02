<div class="form-group">
    <label for="title" class="col-md-2 control-label" style="padding-right: 0;">https://</label>
    <div class="col-md-3">
        @if(isset($application))
            <input type="text" id="client_id" name="client_id" placeholder="Your app will be available at https://yourlowercaseappname.quantimo.do" class="form-control" value="{!! Request::old('client_id', $application->client_id) !!}">
        @else
            <input type="text" id="client_id" name="client_id" placeholder="Enter subdomain" class="form-control" value="{!! Request::old('client_id') !!}">
        @endif
    </div>
    <label for="title" class="col-md-2 control-label" style="text-align: left; padding-left: 0;" >.quantimo.do *</label>
</div>
