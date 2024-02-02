<!-- Start resources/views/javascript-in-head.blade.php -->
<script src="https://code.jquery.com/jquery-3.1.1.min.js" type="text/javascript"></script>
<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
<script src="https://unpkg.com/material-components-web@v4.0.0/dist/material-components-web.min.js"></script>
@include('psychedelic-loader')

<!-- This makes the current user id available in javascript -->
@if(!auth()->guest())
    <script>
        window.Laravel = <?php echo json_encode(['csrfToken' => qm_csrf_token(),]); ?>
    </script>
    <script>
        window.Laravel.userId = <?php echo auth()->user()->ID; ?>
    </script>
@endif
<!-- End resources/views/javascript-in-head.blade.php -->
