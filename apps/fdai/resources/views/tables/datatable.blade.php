<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>

<!-- DATATABLES -->
<link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
@include('datatables_js')

<!-- Bootstrap and Datatables Bootstrap theme (OPTIONAL) -->
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" rel="stylesheet">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script type="text/javascript">

    $(document).ready(function(){
        $('#data-table-id').DataTable();
    });

</script>

{!! $table  !!}