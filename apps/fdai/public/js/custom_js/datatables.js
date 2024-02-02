 jQuery(document).ready(function() {
        $('#sample_1').dataTable();

        var table = $('#example').DataTable({
            "scrollY": "200px",
            "paging": false,
            "bFilter": false
        });

        $('button.toggle-vis').on('click', function(e) {
            e.preventDefault();

            // Get the column API object
            var column = table.column($(this).attr('data-column'));

            // Toggle the visibility
            column.visible(!column.visible());
        });

    });