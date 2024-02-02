<!-- Make sure to include the cards -> resources/views/hidden-search-cards.blade.php -->
<style>
    .card-stats-container {
        min-width: 30%;
        padding: 5px;
    }
    .card-title {
        font-size: 1rem;
    }
    .card [class*="card-header-"] .card-icon, .card [class*="card-header-"] .card-text {
        border-radius: 3px;
        background-color: #999999;
        padding: 5px;
        margin-top: -20px;
        margin-right: 5px;
        float: left;
    }
</style>
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
@push('js')
    <script>
        $(document).ready(function() {
            // Javascript method's body can be found in assets/js/demos.js
            $("#searchInput").on('keyup', function() {
                var searchValue = $(this).val();
                searchAndFilter(searchValue)
            });

            function searchAndFilter(searchTerm) {
                if(searchTerm && searchTerm.length){
                    $("#material-cards-container").show();
                } else {
                    $("#material-cards-container").hide();
                }
                searchTerm = searchTerm.toUpperCase();
                $(".card-stats-container").each(function() {
                    if (searchTerm === '') {
                        $(this).show();
                    } else {
                        var currentText = $(this).text();
                        currentText = currentText.toUpperCase();
                        if (currentText.indexOf(searchTerm) >= 0) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    }
                });
            }
        });
    </script>
@endpush