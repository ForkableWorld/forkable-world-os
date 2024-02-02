<form class="navbar-form">
    <div class="input-group no-border">
        <input id="searchInput" type="text" value="" class="form-control" placeholder="Search...">
        <button type="text" name="country" id="country" class="btn btn-white btn-round btn-just-icon">
            <i class="material-icons">search</i>
            <div class="ripple-container"></div>
        </button>
    </div>
    <div class="col-lg-3"></div>
</form>
@push('scripts')
    <script type="text/javascript">
        // jQuery wait till the page is fully loaded
        $(document).ready(function () {
            // keyup function looks at the keys typed on the search box
            $('#country').on('keyup',function() {
                // the text typed in the input field is assigned to a variable
                var query = $(this).val();
                // call to an ajax function
                $.ajax({
                    // assign a controller function to perform search action - route name is search
                    url:"{{ \App\Utils\UrlHelper::getApiUrlForPath('variables') }}",
                    // since we are getting data methos is assigned as GET
                    type:"GET",
                    // data are sent the server
                    data:{'searchPhrase':query},
                    // if search is succcessfully done, this callback function is called
                    success:function (data) {
                        // print the search results in the div called country_list(id)
                        $('#country_list').html(data);
                    }
                })
                // end of ajax call
            });
        });
    </script>
@endpush