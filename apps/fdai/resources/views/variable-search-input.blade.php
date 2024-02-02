<form class="navbar-form">
    <div class="input-group no-border">
        <input id="variable-search-input" type="text" value="" class="form-control" placeholder="Search...">
        <button type="text" name="variable-search" id="variable-search-button" class="btn btn-white btn-round btn-just-icon">
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
            // initiate a click function on each search result
            $(document).on('click', 'li', function(){
                // declare the value in the input field to a variable
                var value = $(this).text();
                // assign the value to the search box
                $('#country').val(value);
                // after click is done, search results segment is made empty
                $('#country_list').html("");
            });
        });
    </script>
@endpush